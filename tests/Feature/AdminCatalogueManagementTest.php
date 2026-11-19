<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCatalogueManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_edit_a_category_more_than_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'میزهای خانه',
                'slug' => 'home-tables',
                'description' => 'دسته‌بندی میزهای خانه',
                'is_active' => '1',
                'sort_order' => '2',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'home-tables')->firstOrFail();

        $this->put(route('admin.categories.update', $category), [
            'name' => 'میزهای پذیرایی',
            'slug' => 'living-room-tables',
            'description' => 'میزهای مناسب فضای پذیرایی',
            'is_active' => '1',
            'sort_order' => '3',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertSame('میزهای پذیرایی', $category->fresh()->name);

        $this->put(route('admin.categories.update', $category), [
            'name' => 'میزهای پذیرایی و جلو مبلی',
            'slug' => 'living-room-coffee-tables',
            'description' => 'مجموعه میزهای پذیرایی و جلو مبلی',
            'is_active' => '0',
            'sort_order' => '4',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'میزهای پذیرایی و جلو مبلی',
            'slug' => 'living-room-coffee-tables',
            'is_active' => false,
            'sort_order' => 4,
        ]);
    }

    public function test_admin_can_create_and_edit_a_product_with_persian_numeric_inputs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create([
            'name' => 'مبلمان',
            'slug' => 'furniture',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $payload = [
            'category_id' => $category->id,
            'name' => 'میز عسلی چوبی',
            'slug' => 'wooden-side-table',
            'sku' => 'SG-SIDE-001',
            'short_description' => 'میز عسلی برای فضای نشیمن',
            'description' => 'توضیحات محصول آزمایشی',
            'price' => '۱٬۲۳۴٬۵۰۰',
            'compare_at_price' => '',
            'stock' => '۲',
            'status' => 'draft',
            'is_featured' => '0',
            'is_new' => '0',
            'installment_enabled' => '0',
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect();

        $product = Product::query()->where('slug', 'wooden-side-table')->firstOrFail();

        $this->assertSame('1234500.00', $product->price);
        $this->assertSame(2, $product->stock);

        $this->put(route('admin.products.update', $product), array_merge($payload, [
            'name' => 'میز عسلی چوبی جدید',
            'slug' => 'wooden-side-table-updated',
            'sku' => 'SG-SIDE-002',
            'price' => '۱٬۵۰۰٬۰۰۰',
            'stock' => '۵',
        ]))->assertRedirect(route('admin.products.edit', $product));

        $this->put(route('admin.products.update', $product), array_merge($payload, [
            'name' => 'میز عسلی چوبی و فلزی',
            'slug' => 'wood-metal-side-table',
            'sku' => 'SG-SIDE-003',
            'price' => '۱٬۷۵۰٬۰۰۰',
            'stock' => '۷',
        ]))->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'میز عسلی چوبی و فلزی',
            'slug' => 'wood-metal-side-table',
            'sku' => 'SG-SIDE-003',
            'price' => '1750000.00',
            'stock' => 7,
        ]);
    }
}
