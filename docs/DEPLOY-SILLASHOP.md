# SilaGallery / Livora production deployment — sillashop.com

This deployment is intentionally isolated from farzindev and farajihome.com.
It uses its own release tree, environment file, database, writable storage, PHP-FPM pool/socket (where provisioned), and Nginx server block. The only shared resources are the VPS operating system and Nginx service.

## 1. DNS at Parspack

The domain is delegated to the Parspack nameservers supplied in the CDN panel. In the DNS/CDN zone for sillashop.com, set the origin:

- Apex @: A → 45.149.79.155
- www: CNAME → sillashop.com (or A → 45.149.79.155 if the panel does not support an apex CNAME/alias)
- Do not add an AAAA record unless IPv6 is actually configured on this VPS.
- If the CDN panel has a separate Origin field, set it to 45.149.79.155 and make sure the origin host header is sillashop.com.

Check that DNS has propagated before requesting the origin certificate. Do not alter the existing Farzin DNS zone or its Nginx block.

## 2. Verify the existing VPS before making changes

    php -v
    php-fpm8.5 -v 2>/dev/null || true
    ls -l /run/php/*fpm.sock
    nginx -v
    node -v
    npm -v
    composer --version
    sudo nginx -T | grep -nE 'server_name|listen 443|root '

Use the matching PHP-FPM version and give Livora its own pool/socket. The included pool config caps the Livora pool at four on-demand workers so it cannot consume unlimited PHP workers on a VPS shared with Farzin.

For PHP 8.5, install and validate the pool after cloning the repository:

    sudo install -m 644 deploy/php-fpm/livora.conf /etc/php/8.5/fpm/pool.d/livora.conf
    sudo php-fpm8.5 -t
    sudo systemctl reload php8.5-fpm
    sudo test -S /run/php/php8.5-fpm-livora.sock

If the installed FPM version is different, copy the pool file into that version's /etc/php/VERSION/fpm/pool.d/ directory and change its listen path and the Nginx fastcgi_pass socket to match. Do not alter the existing Farzin pool.

## 3. Create Livora's separate directory and database

Create a dedicated unprivileged deployment account. Do not clone Livora into /var/www/farzindev or reuse Farzin's .env, database, storage, or public directory.

    sudo adduser --system --group --home /var/www/livora --shell /usr/sbin/nologin livora
    sudo usermod -aG www-data livora

    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/releases
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/app/public
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/app/private
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/framework/cache/data
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/framework/sessions
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/framework/views
    sudo install -d -o livora -g www-data -m 2775 /var/www/livora/shared/storage/logs

Create a new MySQL/MariaDB database and least-privilege user for Livora only. Use a unique strong password and do not reuse Farzin's credentials:

    CREATE DATABASE livora_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER 'livora_app'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_APP_PASSWORD';
    GRANT SELECT, INSERT, UPDATE, DELETE
    ON livora_production.* TO 'livora_app'@'127.0.0.1';

    CREATE USER 'livora_migrator'@'127.0.0.1' IDENTIFIED BY 'REPLACE_WITH_MIGRATOR_PASSWORD';
    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
    ON livora_production.* TO 'livora_migrator'@'127.0.0.1';
    FLUSH PRIVILEGES;

The normal APP .env uses livora_app (data access only). Use livora_migrator only for the deployment-time migration command below; never store its password in the shared .env. If PHP connects through a Unix socket, create both users for the matching host and set DB_HOST to match the actual PDO connection mode. Do not grant global privileges.

## 4. Clone the production branch into a release

The current production baseline is main. Merge the reviewed production-readiness PR before this step; do not deploy the audit branch directly.

    RELEASE_ID=$(date +%Y%m%d%H%M%S)
    RELEASE=/var/www/livora/releases/$RELEASE_ID

    sudo -u livora git clone --depth 1 --single-branch --branch main \
      https://github.com/MREZA-MJDi/livora.git "$RELEASE"

    sudo -u livora ln -s /var/www/livora/shared/.env "$RELEASE/.env"
    sudo -u livora ln -s /var/www/livora/shared/storage "$RELEASE/storage"
    sudo -u livora ln -s /var/www/livora/shared/storage/app/public "$RELEASE/public/storage"

Create the shared environment file once:

    sudo -u livora cp "$RELEASE/.env.example" /var/www/livora/shared/.env
    sudo chown livora:www-data /var/www/livora/shared/.env
    sudo chmod 640 /var/www/livora/shared/.env

Set these values in /var/www/livora/shared/.env before generating the application key or running migrations:

    APP_NAME="SilaGallery"
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://sillashop.com
    APP_TIMEZONE=Asia/Tehran
    APP_LOCALE=fa
    APP_FALLBACK_LOCALE=fa

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=livora_production
    DB_USERNAME=livora_app
    DB_PASSWORD=REPLACE_WITH_THE_DATABASE_PASSWORD

    SESSION_DRIVER=database
    SESSION_SECURE_COOKIE=true
    SESSION_SAME_SITE=lax
    CACHE_STORE=database
    QUEUE_CONNECTION=database
    FILESYSTEM_DISK=public

    # Keep empty while CDN serves the same sillashop.com host transparently.
    ASSET_URL=
    PUBLIC_STORAGE_URL=

    LOG_CHANNEL=stack
    LOG_LEVEL=warning

    PAYMENT_GATEWAYS_PRODUCTION_READY=false
    DIGIPAY_ENABLED=false
    SNAPPAY_ENABLED=false
    TOROBPAY_ENABLED=false

Also configure real SMTP settings, sender identity, and the public business contact values. Keep all payment gateways disabled until each merchant account has passed its own current sandbox contract and callback/signature tests. Do not put secrets in GitHub.

## 5. Install dependencies, build, migrate and cache

    sudo -u livora bash -lc "cd '$RELEASE' && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader"
    sudo -u livora bash -lc "cd '$RELEASE' && npm ci && npm run build"
    sudo -u livora bash -lc "cd '$RELEASE' && php artisan key:generate"

    read -rsp "Livora migration DB password: " LIVORA_MIGRATOR_PASSWORD; echo
    sudo -u livora env DB_USERNAME=livora_migrator DB_PASSWORD="$LIVORA_MIGRATOR_PASSWORD" \
      bash -lc "cd '$RELEASE' && php artisan config:clear && php artisan migrate --force"
    unset LIVORA_MIGRATOR_PASSWORD

    sudo -u livora bash -lc "cd '$RELEASE' && php artisan optimize"

Run php artisan key:generate only when creating the first shared .env. Never regenerate APP_KEY during a routine release because that invalidates encrypted sessions and data. Do not run migrate:fresh, demo seeders, or destructive seed commands against production.

If this is a brand-new database, create the initial administrator with the documented ADMIN_NAME, ADMIN_EMAIL, and a one-time strong ADMIN_PASSWORD in the environment, then run only the guarded first-admin seeder documented by the repository. Remove the one-time password from the environment after provisioning.

Make sure Laravel's writable storage directories remain group-writable by www-data:

    sudo chown -R livora:www-data /var/www/livora/shared/storage
    sudo find /var/www/livora/shared/storage -type d -exec chmod 2775 {} \;
    sudo find /var/www/livora/shared/storage -type f -exec chmod 0664 {} \;
    sudo chown -R livora:www-data "$RELEASE/bootstrap/cache"
    sudo find "$RELEASE/bootstrap/cache" -type d -exec chmod 2775 {} \\
    sudo find "$RELEASE/bootstrap/cache" -type f -exec chmod 0664 {} \\

## 6. Atomically activate the release

    sudo ln -sfn "$RELEASE" /var/www/livora/current.new
    sudo mv -Tf /var/www/livora/current.new /var/www/livora/current

The Nginx root must be /var/www/livora/current/public. Keep at least the previous release for rollback.

Install and start the dedicated database queue worker after the current symlink is active:

    sudo apt-get install -y supervisor
    sudo cp deploy/supervisor/livora-worker.conf /etc/supervisor/conf.d/livora-worker.conf
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl status livora-worker:*

Enable Laravel's scheduler (safe even when no scheduled task is registered yet):

    echo '* * * * * cd /var/www/livora/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1' | sudo tee /etc/cron.d/livora-scheduler
    sudo chmod 644 /etc/cron.d/livora-scheduler

## 7. Nginx and origin SSL

Copy deploy/nginx/sillashop.com.conf to /etc/nginx/sites-available/sillashop.com. It points to the dedicated /run/php/php8.5-fpm-livora.sock pool, not the shared default PHP-FPM pool. Verify that socket exists, then enable only this new server block:

    sudo ln -s /etc/nginx/sites-available/sillashop.com /etc/nginx/sites-enabled/sillashop.com
    sudo nginx -t
    sudo systemctl reload nginx

Check that the existing farajihome.com server block remains enabled and unchanged. Ensure the VPS firewall/provider firewall allows inbound TCP 80 and 443.

After the A records resolve to 45.149.79.155, issue the origin certificate:

    sudo certbot --nginx -d sillashop.com -d www.sillashop.com
    sudo certbot renew --dry-run

Certbot will add the HTTPS server block and HTTP-to-HTTPS redirect. Test both hostnames. Use the CDN's HTTPS mode Full/Strict (never Flexible) once the origin certificate is valid. Enable the CDN's edge certificate for both sillashop.com and www.sillashop.com.

## 8. CDN cache policy

The domain uses the Parspack CDN nameservers. Start with conservative rules:

- Cache /build/* for one year; filenames are content-hashed by Vite.
- Cache /storage/* for seven days; uploads are stored under generated paths.
- Do not cache HTML, admin, account, cart, checkout, login, registration, logout, or payment callback responses.
- Bypass cache when Laravel session/XSRF cookies or authorization headers are present.
- Keep font CORS enabled for public fonts.
- Do not enable a second CSS/JS minifier: Vite already builds/minifies production assets.
- Enable image format conversion only after confirming WebP/AVIF variants, correct content-type, cache variation by Accept header, and fallbacks in a real browser. Dynamic resizing is not useful until image URLs are wired to the CDN's resize endpoint.

Because the CDN is attached to the same domain via nameservers, leave ASSET_URL and PUBLIC_STORAGE_URL empty unless Parspack supplies a distinct CDN hostname. In that case set ASSET_URL=https://THE_CDN_HOST for Vite/static assets and PUBLIC_STORAGE_URL=https://THE_CDN_HOST/storage for public uploads, then run php artisan config:cache again.

## 9. First-release verification

Check in this order:

1. curl -I http://sillashop.com then curl -I https://sillashop.com and the www hostname.
2. https://sillashop.com/up returns HTTP 200.
3. Home, shop, categories, product details, search, all shop filters, sorting, and pagination.
4. Admin login, product/category create-edit, image upload, media library, customer/order lists.
5. Cart as guest and customer, checkout validation, stock/variant checks, order ownership and payment callback route.
6. Browser mobile/desktop, console, failed assets, font CORS, image dimensions, cache headers, TTFB and PHP/database logs.
7. Upload a test image; confirm its public URL works through the CDN and the file remains present after a release switch.

Do not enable online payment gateways until the actual provider sandbox flow is tested and signed off. The default project configuration intentionally keeps them disabled.

## 10. Routine release / rollback

For each release, clone a new main checkout into a new releases/<timestamp>, link the same shared .env and storage, install/build, run additive migrations, cache, and atomically switch current. Do not overwrite the active release in place.

After each release, restart the worker on the new code with:

    sudo -u livora php /var/www/livora/current/artisan queue:restart
    sudo systemctl reload php8.5-fpm

Rollback the code by switching current to the previous release and reloading only the Livora PHP-FPM service where possible. Do not automatically roll back database migrations; use a tested forward fix when a schema migration has already been applied.
