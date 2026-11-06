<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminManagementParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_content_and_commerce_resources_have_real_admin_management_routes(): void
    {
        $requiredRoutes = [
            'admin.home-sections.index',
            'admin.shop-banner.edit',
            'admin.categories.index',
            'admin.products.index',
            'admin.product-images.index',
            'admin.product-variants.index',
            'admin.orders.index',
            'admin.orders.show',
            'admin.customers.index',
            'admin.customers.edit',
            'admin.pages.index',
            'admin.blog.index',
            'admin.contact-settings.edit',
            'admin.seo.edit',
            'admin.seo.pages',
            'admin.media.index',
            'admin.contact-messages.index',
        ];

        foreach ($requiredRoutes as $routeName) {
            $this->assertNotNull(
                Route::getRoutes()->getByName($routeName),
                sprintf('Missing admin management route [%s].', $routeName)
            );
        }
    }

    public function test_brand_and_footer_configuration_is_managed_from_the_canonical_settings_screen(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('admin.contact-settings.edit'));
        $this->assertNotNull(Route::getRoutes()->getByName('admin.contact-settings.update'));
    }
}
