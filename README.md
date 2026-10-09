# LIVORA

LIVORA is a furniture and home-living e-commerce project built with Laravel. Its documented product direction includes catalogue browsing, product details, categories, filtering, cart and wishlist experiences, customer accounts, orders, payment-provider integration, and installment purchasing. Features and payment providers must be verified against the current implementation before being treated as live or production-ready.

## Technology
- PHP `^8.2`, Laravel `^12.0`
- Blade, Tailwind CSS, Alpine.js, Vite
- Laravel database migrations and Eloquent
- Frontend build managed by npm/Vite

## Requirements
PHP 8.2+, Composer, Node.js/npm, and a configured Laravel-supported database.

## Local setup
```bash
git clone https://github.com/MREZA-MJDi/livora.git
cd livora
composer install
```

Copy `.env.example` to `.env` (`copy .env.example .env` in Windows CMD; `cp .env.example .env` on macOS/Linux), create a local database, and configure the `DB_*` values. Set payment credentials only in your local environment and only when the corresponding gateway is configured.

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. For Vite hot reload, run `npm run dev` in a separate terminal.

## Tests
```bash
php artisan test
```

## Payments and data safety
Use gateway sandbox credentials for development. Never store secret keys in source control, and never claim that a payment succeeded based only on a browser redirect; payment status must be confirmed by the application's verified gateway flow. Review migrations and seeders before using them with non-disposable data.

## Links
- Repository: https://github.com/MREZA-MJDi/livora
- Laravel documentation: https://laravel.com/docs/12.x
