# SillaGallery / Livora — single-root VPS deployment

Production domain: `sillashop.com` (canonical HTTPS host).

This deployment keeps the complete Laravel application in one predictable directory:

```text
/var/www/livora/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          # Nginx document root
├── resources/
├── routes/
├── storage/         # persistent uploads, logs, sessions and cache
├── vendor/
├── .env             # server-only; never commit
├── artisan
├── composer.json
└── package.json
```

There are no `releases/`, `shared/`, or `current` directories. The public web root remains Laravel's `public/` directory; never point Nginx at the project root.

## 1. Existing VPS safety check

Before moving or replacing anything, inspect the actual server and take a database + uploaded-files backup. Do not delete the old `storage`, `share`, `release`, or `current` directories until their contents have been compared and the live site has passed smoke tests.

```bash
sudo nginx -T | grep -nE 'server_name|root |fastcgi_pass'
ls -la /var/www
find /var/www -maxdepth 3 -type d -print | sort
php -v
composer --version
node -v
npm -v
ls -l /run/php/*fpm.sock
```

Use the current Livora database and preserve its credentials. Never run `migrate:fresh`, destructive seeders, or regenerate `APP_KEY` on the existing production site.

## 2. Target directory and permissions

The target project path is `/var/www/livora`. If it already contains the current production application, update it in place after backing up. If the project is currently under `current` or another release directory, first copy/merge the application code into `/var/www/livora` while explicitly preserving the real production `.env` and `storage/` contents.

Use a dedicated account if one already exists; do not recreate it blindly:

```bash
id livora || sudo adduser --system --group --home /var/www/livora --shell /usr/sbin/nologin livora
sudo usermod -aG www-data livora
sudo chown -R livora:www-data /var/www/livora
sudo find /var/www/livora/storage -type d -exec chmod 2775 {} \;
sudo find /var/www/livora/storage -type f -exec chmod 0664 {} \;
sudo chown -R livora:www-data /var/www/livora/bootstrap/cache
sudo find /var/www/livora/bootstrap/cache -type d -exec chmod 2775 {} \;
sudo find /var/www/livora/bootstrap/cache -type f -exec chmod 0664 {} \;
```

The `.env` file should be owned by `livora:www-data` and mode `640`. Keep it in the project root:

```bash
sudo chown livora:www-data /var/www/livora/.env
sudo chmod 640 /var/www/livora/.env
```

## 3. Production environment

Create or update `/var/www/livora/.env` using the current production values. Do not replace the existing `APP_KEY`, database name, database password, mail credentials, payment settings, or other secrets with placeholders.

Required baseline:

```dotenv
APP_NAME="SillaGallery"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sillashop.com
APP_TIMEZONE=Asia/Tehran
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=120
FILESYSTEM_DISK=public

TURNSTILE_SITE_KEY=YOUR_CLOUDFLARE_PUBLIC_SITE_KEY
TURNSTILE_SECRET=YOUR_CLOUDFLARE_SECRET_KEY
TURNSTILE_HOSTNAMES=sillashop.com,www.sillashop.com

PAYMENT_GATEWAYS_PRODUCTION_READY=false
DIGIPAY_ENABLED=false
SNAPPAY_ENABLED=false
TOROBPAY_ENABLED=false
```

Keep the Turnstile secret only in this server-side `.env`. The configured Cloudflare widget must allow both production hostnames actually served by the site. The app validates the token with Cloudflare Siteverify and checks the returned hostname and action; do not remove the server-side middleware. If the secret is absent in production, protected form submissions intentionally fail closed.

Set the existing MySQL/MariaDB credentials and real SMTP/contact values in the same file. Keep `APP_DEBUG=false`. Never commit `.env` or paste secret values into chat or deployment logs.

## 4. Nginx and PHP-FPM

Install `deploy/nginx/sillashop.com.conf` as the site's Nginx server block. It uses:

- Document root: `/var/www/livora/public`
- PHP front controller: `/var/www/livora/public/index.php`
- Dedicated PHP-FPM socket: `/run/php/php8.5-fpm-livora.sock`

Confirm the installed PHP-FPM version and socket before enabling the block. If the VPS uses another version, update the socket in both the Nginx block and `deploy/php-fpm/livora.conf` to match. Do not alter the Farzin virtual host or pool. Install the dedicated Livora pool only after confirming the matching PHP-FPM version:

```bash
sudo install -m 644 deploy/php-fpm/livora.conf /etc/php/8.5/fpm/pool.d/livora.conf
sudo php-fpm8.5 -t
sudo systemctl reload php8.5-fpm
sudo nginx -t
sudo systemctl reload nginx
```

Keep the domain's valid origin certificate and CDN configuration. Use HTTPS Full (strict) at the CDN when the origin certificate is valid.

## 5. First deployment / updating the existing checkout

Run commands from the single project root. For an existing checkout, do not clone a second nested copy:

```bash
cd /var/www/livora
git status --short
git fetch origin
git pull --ff-only origin main
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan storage:link
php artisan migrate --force
php artisan optimize
```

Run `php artisan key:generate` only once when creating a genuinely new installation with no existing encrypted data. Never run it during an update. Back up the database before migrations. Do not run demo seeders against production.

If the database requires a separate migrator account, supply its credentials only to the migration command through a temporary shell environment; do not save the migrator password in `.env` or Git.

## 6. Persistent storage and uploads

All Laravel writable data stays under `/var/www/livora/storage`. The normal Laravel public-storage link is:

```text
/var/www/livora/public/storage -> /var/www/livora/storage/app/public
```

Check it with:

```bash
readlink -f public/storage
ls -lah storage/app/public
```

Do not create another storage directory outside the project. Do not replace an existing storage link until its target and uploaded files have been verified. Public uploads must remain in `storage/app/public`; private files must remain in `storage/app/private`.

## 7. Queue worker and scheduler

Install `deploy/supervisor/livora-worker.conf` into Supervisor. It now runs Artisan and writes logs inside `/var/www/livora`.

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status livora-worker:*
```

Install the scheduler once (skip if an equivalent Livora cron entry already exists):

```cron
* * * * * livora cd /var/www/livora && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

After code updates:

```bash
sudo -u livora php /var/www/livora/artisan queue:restart
sudo systemctl reload php8.5-fpm
```

## 8. Post-deploy verification

Verify all of the following before removing any old deployment folders:

1. `curl -I https://sillashop.com` and `curl -I https://www.sillashop.com`.
2. `https://sillashop.com/up` returns HTTP 200.
3. Login, registration, contact form and checkout show the Turnstile widget and reject invalid/missing tokens.
4. A real successful Turnstile verification works on the exact production hostnames.
5. Home, shop, categories, product details, search, filters and pagination load without console errors.
6. Admin login, product/category CRUD, image uploads and media library work.
7. Guest/customer cart and checkout flows work; payment gateways remain disabled until separately approved.
8. Uploaded product images remain available after a normal `git pull` and asset build.
9. Review Laravel, Nginx, PHP-FPM and queue logs.

Only after the site passes these checks and a backup is confirmed should obsolete `releases/`, `shared/`, `current`, or duplicate storage directories be archived and later removed. This is a one-root deployment, not an instruction to delete old folders before migration is verified.
