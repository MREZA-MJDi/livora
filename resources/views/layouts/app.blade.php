<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    @php
        $siteSeo = \App\Models\SeoSetting::current();
        $pageSeo = \App\Models\SeoPageSetting::forRoute(\Illuminate\Support\Facades\Route::currentRouteName());
        // Admin-managed SEO values take precedence on fixed pages, while dynamic
        // product/category/article metadata continues to come from each view.
        $seoTitle = $pageSeo?->meta_title ?: (trim($__env->yieldContent('title')) ?: ($siteSeo->default_title ?: 'SillaGallery'));
        $seoDescription = $pageSeo?->meta_description ?: (trim($__env->yieldContent('description')) ?: ($siteSeo->default_description ?: 'فروشگاه SillaGallery'));
        $routeName = (string) \Illuminate\Support\Facades\Route::currentRouteName();
        $privateRoute = in_array($routeName, [
            'login', 'register', 'password.request', 'password.reset',
            'account.index', 'account.profile.edit', 'account.addresses.index',
            'account.orders.index', 'account.wishlist.index',
        ], true)
            || preg_match('/^(account|cart|checkout|login|register|password|logout)(\\.|$)/', $routeName) === 1
            || request()->is('account/*', 'cart/*', 'checkout/*', 'cart', 'login', 'register', 'forgot-password', 'reset-password/*');
        $seoRobots = $privateRoute
            ? 'noindex, nofollow'
            : ($pageSeo !== null
                ? $pageSeo->robots()
                : (trim($__env->yieldContent('robots')) ?: $siteSeo->robots()));
        // Canonical and social URLs must be bound to this application's configured origin.
        // Never let an inbound Host header from another virtual host poison SillaGallery metadata.
        $seoOrigin = rtrim((string) config('app.url', 'https://sillashop.com'), '/');
        $seoCanonicalSource = $pageSeo?->canonical_url ?: trim($__env->yieldContent('canonical'));
        $seoCanonicalPath = parse_url($seoCanonicalSource, PHP_URL_PATH);
        $seoCanonicalPath = is_string($seoCanonicalPath) && $seoCanonicalPath !== ''
            ? $seoCanonicalPath
            : request()->getPathInfo();
        $seoCanonicalPath = '/' . ltrim($seoCanonicalPath, '/');
        $seoCanonicalQuery = parse_url($seoCanonicalSource, PHP_URL_QUERY);
        $seoCanonical = $seoOrigin . $seoCanonicalPath;
        if (is_string($seoCanonicalQuery) && $seoCanonicalQuery !== '') {
            $seoCanonical .= '?' . $seoCanonicalQuery;
        }
        $seoBaseUrl = $seoOrigin . '/';
        $seoOgTitle = trim($__env->yieldContent('og_title')) ?: $seoTitle;
        $seoOgDescription = trim($__env->yieldContent('og_description')) ?: $seoDescription;
        $seoOgImage = $pageSeo?->og_image ?: (trim($__env->yieldContent('og_image')) ?: $siteSeo->default_og_image_url);
        $seoOgType = trim($__env->yieldContent('og_type')) ?: 'website';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="{{ $seoRobots }}">
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="theme-color" content="{{ request()->routeIs('shop.index') ? '#2c2d31' : '#f3f1ed' }}">
    <meta property="og:site_name" content="SillaGallery">
    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if($seoOgImage)<meta property="og:image" content="{{ $seoOgImage }}">@endif
    <meta name="twitter:card" content="{{ $seoOgImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    @if($seoOgImage)<meta name="twitter:image" content="{{ $seoOgImage }}">@endif
    @if($siteSeo->twitter_handle)<meta name="twitter:site" content="{{ str_starts_with($siteSeo->twitter_handle, '@') ? $siteSeo->twitter_handle : '@'.$siteSeo->twitter_handle }}">@endif
    @if($siteSeo->google_site_verification)<meta name="google-site-verification" content="{{ $siteSeo->google_site_verification }}">@endif
    @if($siteSeo->bing_site_verification)<meta name="msvalidate.01" content="{{ $siteSeo->bing_site_verification }}">@endif
    <link rel="canonical" href="{{ $seoCanonical }}">
    <title>{{ $seoTitle }}</title>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteSeo->organization_name ?: 'SillaGallery',
        'description' => $siteSeo->organization_description ?: $siteSeo->default_description,
        'url' => $seoBaseUrl,
        'logo' => $siteSeo->organization_logo_url,
        'email' => $siteSeo->contact_email,
        'telephone' => $siteSeo->contact_phone,
        'sameAs' => array_values($siteSeo->social_links ?: []),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'SillaGallery',
        'url' => $seoBaseUrl,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $seoBaseUrl . 'shop?search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('seo')
</head>
<body class="min-h-screen antialiased">
<div id="app" x-data="{
    mobileOpen: false,
    searchOpen: false,
    searchReturnFocus: null,
    openSearch(trigger) {
        const openedFromMenu = trigger?.closest?.('[data-sg-mobile-menu]');
        this.searchReturnFocus = openedFromMenu
            ? document.querySelector('[data-sg-search-trigger]')
            : (trigger || document.activeElement);
        this.mobileOpen = false;
        this.searchOpen = true;
        this.$nextTick(() => document.getElementById('global-search')?.focus({ preventScroll: true }));
    },
    closeSearch() {
        this.searchOpen = false;
        this.$nextTick(() => {
            const target = this.searchReturnFocus;
            if (target?.isConnected && target.offsetParent !== null) target.focus?.({ preventScroll: true });
            else document.querySelector('[data-sg-search-trigger]')?.focus({ preventScroll: true });
        });
    }
}" class="min-h-screen pb-24 lg:pb-0">
    <x-layout.navbar />
    <x-layout.mobile-menu />
    <x-layout.search-overlay />
    <main class="storefront-content">@yield('content')</main>
    <x-layout.footer />
    <x-layout.mobile-bottom-bar />
</div>
@stack('scripts')
</body>
</html>