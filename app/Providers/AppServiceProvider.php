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
            $view->with('storeSettings', Cache::remember(
                'storefront.settings',
                now()->addMinutes(10),
                static fn () => ContactSetting::query()->first(),
            ));
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
