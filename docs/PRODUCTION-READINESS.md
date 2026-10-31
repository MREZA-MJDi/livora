# Livora production readiness and deployment checklist

This checklist is deliberately conservative. A successful asset build or a green automated test suite does not replace a real merchant-gateway sandbox run, production-database rehearsal, or a browser pass against the customer's final environment.

## Deployment gate

**Do not enable online payment gateways for real customers until the provider-specific requests, signatures/authentication, callbacks, amount/currency semantics, refund flow, and sandbox results have been verified against each provider's current merchant API contract.** The current generic gateway adapters are intentionally blocked in production while `PAYMENT_GATEWAYS_PRODUCTION_READY=false`; provider flags are also false in `.env.example`.

## Before the first release

- Create a dedicated MySQL/MariaDB database and least-privilege application user. Back it up and rehearse a restore.
- Configure production values outside source control: `APP_ENV=production`, `APP_DEBUG=false`, a real HTTPS `APP_URL`, a unique `APP_KEY`, database credentials, and the actual mail/queue settings.
- Set `SESSION_SECURE_COOKIE=true` behind HTTPS. Use `SESSION_DRIVER=database` or Redis with working persistence; do not use the local `array` driver in production.
- Configure writable `storage/` and `bootstrap/cache/` directories; ensure uploaded product/category images live on the intended persistent disk.
- Set `ADMIN_NAME`, `ADMIN_EMAIL`, and a unique `ADMIN_PASSWORD` (at least 16 characters) only when bootstrapping a new administrator. Production seeding refuses weak defaults and does not reset an existing administrator's password.
- Confirm shipment, refund, privacy, contact, product, price, stock, and installment terms with the customer. Remove demo/sample catalogue rows and images; production seeders no longer seed demo catalogue, customers, carts, orders, or payments.

## Release commands (Linux example)

Run from the checked-out release directory with the production environment already configured:

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run `php artisan db:seed --class=UserSeeder --force` only when you intentionally need to create the first administrator and have verified the required environment values. Do not run demo seeders against a live customer catalogue. The new inventory-tracking migration marks historical paid orders as `needs_reconciliation`; review those orders against the actual inventory ledger before fulfilling them. Do not blindly decrement historic stock based on current catalogue quantities.

After deployment, verify `/up`, login/logout, category/product listing, image URLs, search and filters, pagination, guest and signed-in cart mutation, price/stock changes between cart and checkout, order creation under a double-click/retry, customer order ownership, admin order-status changes, and application logs. Verify in a browser at mobile, tablet, and desktop widths and inspect console errors and failed assets.

## Payment and stock behaviour

- A gateway callback does not count as success by itself; the server must verify the transaction with the provider.
- The order's payment and stock changes are recorded under transaction locks. Inventory deduction is idempotent and validates all required stock before decrementing any of it.
- If a confirmed payment cannot be matched to available inventory, or a duplicate/late payment is detected, the order is flagged for administrator review. This is safer than marking a confirmed payment as failed or silently shipping without stock.
- Existing paid orders are marked `needs_reconciliation` because their past stock deduction history cannot be inferred safely. Review them explicitly.
- The payment adapters' generic field names are not proof of compatibility with DigiPay, SnapPay, or TorobPay. Keep `PAYMENT_GATEWAYS_PRODUCTION_READY=false` until each provider passes a documented sandbox checklist.

## CDN and public media

- Keep uploaded product/category/media files on the persistent public disk or an explicitly configured object-storage disk; do not place uploads inside a release directory.
- For a CDN serving the Vite build and fonts, set `ASSET_URL` to the CDN origin (for example, `https://cdn.example.com`).
- For uploaded files, set `PUBLIC_STORAGE_URL` to the exact public storage base URL (for example, `https://cdn.example.com/storage`). Leave it empty to derive the storage URL from `ASSET_URL` or `APP_URL`.
- Configure the CDN origin to serve only public `/build` and `/storage` paths. Add the correct CORS response headers for web fonts, and do not cache dynamic HTML, account, admin, cart, checkout, or payment callback responses.
- Use versioned/unique upload paths and invalidate the CDN when an existing object is replaced. Verify a newly uploaded image from a separate browser/network after enabling the CDN.

## Rollback and observability

Take a database backup before migrations; record the release SHA and keep the previous release available. Watch application logs, failed jobs, payment exceptions, duplicate-payment alerts, and inventory conflicts during the first orders. Do not roll back by dropping the new inventory columns after they have been used: reconcile order state first and prepare a tested forward migration if schema changes need to be reversed.
