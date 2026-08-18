<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/shop', [ShopController::class, 'index'])
    ->name('shop.index');

Route::get('/product/{slug}', [ProductController::class, 'show'])
    ->name('product.show');


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])
    ->name('categories.show');


/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/

Route::view('/about', 'about.index')
    ->name('about');

Route::view('/contact', 'contact.index')
    ->name('contact');


/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
|
| Cart is available to guests and authenticated customers.
|
*/

Route::prefix('cart')
    ->name('cart.')
    ->group(function () {

        Route::get('/', [CartController::class, 'index'])
            ->name('index');

        Route::post('/{product}/add', [CartController::class, 'add'])
            ->name('add');

        Route::patch('/item/{item}', [CartController::class, 'update'])
            ->middleware('auth')
            ->name('update');

        Route::delete('/item/{item}', [CartController::class, 'remove'])
            ->middleware('auth')
            ->name('remove');

        Route::delete('/', [CartController::class, 'clear'])
            ->middleware('auth')
            ->name('clear');
    });


/*
|--------------------------------------------------------------------------
| Guest Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')
    ->group(function () {

        Route::get('/login', [AuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.store');

        Route::get('/register', [AuthController::class, 'showRegister'])
            ->name('register');

        Route::post('/register', [AuthController::class, 'register'])
            ->name('register.store');
    });


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Customer Area
|--------------------------------------------------------------------------
|
| Every route in this block requires:
| 1. Authentication
| 2. Customer role
|
*/

Route::middleware(['auth', 'customer'])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::prefix('checkout')
            ->name('checkout.')
            ->group(function () {

                Route::get('/', [CheckoutController::class, 'index'])
                    ->name('index');

                Route::post('/', [CheckoutController::class, 'placeOrder'])
                    ->name('place');

                Route::get('/payment/{order}', [CheckoutController::class, 'payment'])
                    ->name('payment');
            });


        /*
        |--------------------------------------------------------------------------
        | Customer Account
        |--------------------------------------------------------------------------
        */

        Route::prefix('account')
            ->name('account.')
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                Route::get('/', [AccountController::class, 'index'])
                    ->name('index');


                /*
                |--------------------------------------------------------------------------
                | Profile
                |--------------------------------------------------------------------------
                */

                Route::put('/profile', [AccountController::class, 'updateProfile'])
                    ->name('profile.update');


                /*
                |--------------------------------------------------------------------------
                | Addresses
                |--------------------------------------------------------------------------
                */

                Route::get('/addresses', [AccountController::class, 'addresses'])
                    ->name('addresses.index');

                Route::post('/addresses', [AccountController::class, 'storeAddress'])
                    ->name('addresses.store');

                Route::put('/addresses/{address}', [AccountController::class, 'updateAddress'])
                    ->name('addresses.update');

                Route::delete('/addresses/{address}', [AccountController::class, 'deleteAddress'])
                    ->name('addresses.destroy');

                Route::patch('/addresses/{address}/default', [AccountController::class, 'setDefaultAddress'])
                    ->name('addresses.default');


                /*
                |--------------------------------------------------------------------------
                | Orders
                |--------------------------------------------------------------------------
                */

                Route::get('/orders', [OrderController::class, 'index'])
                    ->name('orders.index');

                Route::get('/orders/{order}', [OrderController::class, 'show'])
                    ->name('orders.show');


                /*
                |--------------------------------------------------------------------------
                | Wishlist
                |--------------------------------------------------------------------------
                */

                Route::get('/wishlist', [WishlistController::class, 'index'])
                    ->name('wishlist.index');

                Route::post('/wishlist/{product}', [WishlistController::class, 'store'])
                    ->name('wishlist.store');

                Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])
                    ->name('wishlist.destroy');
            });
    });


/*
|--------------------------------------------------------------------------
| Admin Area
|--------------------------------------------------------------------------
|
| Every route in this block requires:
| 1. Authentication
| 2. Admin role
|
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::resource('categories', AdminCategoryController::class);


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::resource('products', AdminProductController::class);


        /*
        |--------------------------------------------------------------------------
        | Product Images
        |--------------------------------------------------------------------------
        */

        Route::resource('product-images', AdminProductImageController::class);


        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        Route::resource('product-variants', AdminProductVariantController::class);


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get('orders', [AdminOrderController::class, 'index'])
            ->name('orders.index');

        Route::get('orders/{order}', [AdminOrderController::class, 'show'])
            ->name('orders.show');

        Route::patch(
            'orders/{order}/status',
            [AdminOrderController::class, 'updateStatus']
        )->name('orders.status');


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get('customers', [AdminCustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])
            ->name('customers.show');

        Route::get(
            'customers/{customer}/edit',
            [AdminCustomerController::class, 'edit']
        )->name('customers.edit');

        Route::put(
            'customers/{customer}',
            [AdminCustomerController::class, 'update']
        )->name('customers.update');
    });
