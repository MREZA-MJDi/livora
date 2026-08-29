# LIVORA

**LIVORA** is a modern furniture and home-living e-commerce platform built with Laravel. The project provides a complete shopping experience, including product discovery, filtering, cart management, customer accounts, orders, online payments, and installment purchasing.

## Features

* Product catalog with categories
* Product details and image galleries
* Advanced product filtering
* Shopping cart
* Wishlist
* Customer authentication and account area
* Checkout and order management
* Online payment integration
* Installment purchasing system
* Installment plan management
* Admin dashboard
* Product, category, customer, order, and media management
* Responsive RTL interface
* SEO-friendly page structure

## Technology Stack

* **Backend:** PHP, Laravel
* **Frontend:** Blade, Tailwind CSS, Alpine.js
* **Database:** MySQL
* **Build Tool:** Vite
* **Architecture:** MVC
* **Authentication:** Laravel Authentication
* **Payments:** Gateway-based payment architecture

## Payment Architecture

The payment system is designed around a driver-based architecture, making it possible to support multiple payment providers without coupling the order system to a specific gateway.

Supported integrations include:

* DigiPay
* SnapPay
* TorobPay

The project also includes a dedicated installment-payment flow for products that support installment sales.

## Project Structure

The application follows Laravel's standard MVC architecture with dedicated services, requests, models, controllers, and payment gateway drivers.

Key areas include:

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
└── Services/
    └── Payment/

resources/
├── views/
└── css/

database/
├── migrations/
└── seeders/

routes/
└── web.php
```

## Main Modules

### Storefront

Handles product browsing, categories, filtering, product details, cart, wishlist, and checkout.

### Customer Area

Provides authenticated customers with access to their account and order information.

### Admin Panel

Provides management interfaces for:

* Products
* Product images
* Product variants
* Categories
* Customers
* Orders
* Media

### Payment System

Payment processing is separated into gateway drivers and application services, allowing payment providers to be replaced or extended independently.

## Installation

Clone the repository:

```bash
git clone https://github.com/MREZA-MJDi/livora.git
cd livora
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database and required environment variables in `.env`.

Run migrations:

```bash
php artisan migrate
```

Optionally seed the database:

```bash
php artisan db:seed
```

Create the storage symlink:

```bash
php artisan storage:link
```

Start the development server:

```bash
php artisan serve
```

Run the frontend development server:

```bash
npm run dev
```

## Environment Configuration

Payment credentials and other sensitive configuration values should be stored in `.env` and must not be committed to the repository.

Example:

```env
PAYMENT_DEFAULT_GATEWAY=
DIGIPAY_MERCHANT_ID=
DIGIPAY_CLIENT_ID=
DIGIPAY_CLIENT_SECRET=
```

## Development Status

LIVORA is an actively developed e-commerce project focused on building a complete, maintainable, and scalable Laravel-based commerce platform.

## Author

**Mohammad Reza Majidi**

Full-Stack Web Developer

GitHub: [MREZA-MJDi](https://github.com/MREZA-MJDi)
