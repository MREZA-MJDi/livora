<?php

namespace App\Providers;

use App\Models\Category;
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
        View::composer('components.layout.search-overlay', function ($view): void {
            $searchCategories = Category::query()
                ->active()
                ->limit(4)
                ->get(['name', 'slug']);

            $view->with('searchCategories', $searchCategories);
        });
    }
}
