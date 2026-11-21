<?php

namespace App\Providers;

use App\Models\SmtpSetting;

use App\Models\Category;
use App\Models\ContactSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Throwable;
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
        // Apply dashboard-managed SMTP settings to Laravel's mailer at runtime.
        // Keep .env as the fallback until SMTP is explicitly enabled in admin.
        try {
            if (Schema::hasTable('smtp_settings')) {
                $smtp = SmtpSetting::query()->first();

                if ($smtp?->enabled && filled($smtp->host) && filled($smtp->from_address)) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp' => [
                            'transport' => 'smtp',
                            'host' => $smtp->host,
                            'port' => $smtp->port,
                            'encryption' => $smtp->encryption === 'none' ? null : $smtp->encryption,
                            'username' => $smtp->username,
                            'password' => $smtp->password,
                            'timeout' => 15,
                            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
                        ],
                        'mail.from.address' => $smtp->from_address,
                        'mail.from.name' => $smtp->from_name ?: config('app.name'),
                    ]);
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }

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
