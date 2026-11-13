<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            // Cache::remember treats a cached null as a cache miss. On a fresh
            // database with no ContactSetting row, every nested view composer
            // would otherwise issue the same SELECT again.
            $settings = Cache::remember(
                'storefront.settings',
                now()->addMinutes(10),
                static fn () => ContactSetting::query()->first() ?? false,
            );

            $view->with('storeSettings', $settings === false ? null : $settings);
        });
        View::composer('components.layout.search-overlay', function ($view): void {
            $searchCategories = Cache::remember(
                'layout.search_categories',
                now()->addMinutes(5),
                static fn () => Category::query()
                    ->active()
                    ->limit(4)
                    ->get(['name', 'slug'])
            );

            $view->with('searchCategories', $searchCategories);
        });
    }
}
