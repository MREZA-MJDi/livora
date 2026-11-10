<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ContactSetting;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\HomeSection;
use App\Services\Inventory\OrderInventoryService;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class StorefrontReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_without_catalogue_records(): void
    {
        foreach ([
            '/',
            '/shop',
            '/categories',
            '/about',
            '/contact',
            '/cart',
            '/login',
            '/register',
            '/forgot-password',
        ] as $uri) {
            $this->get($uri)
                ->assertOk();
        }
    }

    public function test_unknown_json_routes_keep_a_client_error_status(): void
    {
        $this->getJson('/route-that-does-not-exist')
            ->assertNotFound();
    }


    public function test_demo_cart_seeder_persists_a_valid_selection_for_each_required_variant_group(): void
    {
        $category = $this->category();
        $product = $this->product($category, [
            'name' => 'مبل دارای واریانت',
            'slug' => 'luna-sofa',
            'sku' => 'LIV-DEMO-SOFA-001',
            'price' => 50000000,
            'stock' => 10,
        ]);

        $color = ProductVariant::query()->create([
            'product_id' => $product->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'cream',
            'sku' => 'LIV-DEMO-SOFA-CREAM',
            'price_adjustment' => 0,
            'stock' => 5,
            'is_active' => true,
        ]);

        $size = ProductVariant::query()->create([
            'product_id' => $product->id,
            'type' => 'size',
            'name' => 'سایز',
            'value' => 'medium',
            'sku' => 'LIV-DEMO-SOFA-MEDIUM',
            'price_adjustment' => 2500000,
            'stock' => 4,
            'is_active' => true,
        ]);

        $demoCustomer = User::query()->create([
            'name' => 'Demo Customer',
            'email' => 'demo@livora.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $this->seed(\Database\Seeders\CartSeeder::class);

        $cart = Cart::query()
            ->where('user_id', $demoCustomer->id)
            ->where('status', 'active')
            ->firstOrFail();

        $item = $cart->items()->where('product_id', $product->id)->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$color->id, $size->id],
            $item->selectedVariantIds()
        );
        $this->assertCount(2, $item->variant_options);
        $this->assertSame('color', $item->variant_options[0]['type']);
        $this->assertSame('size', $item->variant_options[1]['type']);
        $this->assertSame('52500000.00', $item->unit_price);
        $this->assertSame(1, $item->quantity);
        $this->assertNotSame('base', $item->variant_key);
    }

    public function test_category_directory_uses_real_active_product_images_and_hides_inactive_categories(): void
    {
        $category = $this->category([
            'name' => 'مجموعه نشیمن واقعی',
            'slug' => 'real-living-room-collection',
            'image' => null,
            'is_active' => true,
        ]);

        $product = $this->product($category, [
            'name' => 'مبل واقعی برای تصویر مجموعه',
            'slug' => 'real-living-room-image-product',
            'status' => 'active',
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => 'https://images.example.test/sillagallery/living-room.webp',
            'alt' => 'مبل واقعی در مجموعه نشیمن',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $hiddenCategory = $this->category([
            'name' => 'مجموعه غیرفعال خصوصی',
            'slug' => 'hidden-private-collection',
            'is_active' => false,
        ]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('مجموعه نشیمن واقعی')
            ->assertSee('https://images.example.test/sillagallery/living-room.webp')
            ->assertDontSee('مجموعه غیرفعال خصوصی');

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('مجموعه نشیمن واقعی')
            ->assertSee('https://images.example.test/sillagallery/living-room.webp');

        $this->get(route('categories.show', $hiddenCategory->slug))
            ->assertNotFound();
    }

    public function test_inactive_category_products_are_hidden_and_cannot_be_added_to_cart(): void
    {
        $activeCategory = $this->category([
            'name' => 'دسته فعال',
            'slug' => 'active-category',
            'is_active' => true,
        ]);
        $hiddenCategory = $this->category([
            'name' => 'دسته غیرفعال',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $visibleProduct = $this->product($activeCategory, [
            'name' => 'محصول قابل نمایش',
            'slug' => 'visible-storefront-product',
        ]);
        $hiddenProduct = $this->product($hiddenCategory, [
            'name' => 'محصول پنهان',
            'slug' => 'hidden-storefront-product',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($visibleProduct->name)
            ->assertDontSee($hiddenProduct->name);

        $this->get('/shop')
            ->assertOk()
            ->assertSee($visibleProduct->name)
            ->assertDontSee($hiddenProduct->name);

        $this->get(route('product.show', $hiddenProduct->slug))
            ->assertNotFound();

        $this->postJson(route('cart.add', $hiddenProduct), [
            'quantity' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_wishlist_uses_the_view_contract_and_rejects_unavailable_products(): void
    {
        $customer = User::query()->create([
            'name' => 'مشتری علاقه‌مندی',
            'email' => 'wishlist-customer@example.test',
            'password' => Hash::make('TestPass123!'),
            'role' => 'customer',
        ]);

        $activeCategory = $this->category([
            'name' => 'دسته فعال علاقه‌مندی',
            'slug' => 'wishlist-active-category',
        ]);
        $inactiveCategory = $this->category([
            'name' => 'دسته غیرفعال علاقه‌مندی',
            'slug' => 'wishlist-inactive-category',
            'is_active' => false,
        ]);

        $visibleProduct = $this->product($activeCategory, [
            'name' => 'محصول ذخیره‌شده قابل نمایش',
            'slug' => 'wishlist-visible-product',
        ]);
        $hiddenProduct = $this->product($inactiveCategory, [
            'name' => 'محصول دسته غیرفعال',
            'slug' => 'wishlist-hidden-product',
        ]);
        $inactiveProduct = $this->product($activeCategory, [
            'name' => 'محصول غیرفعال',
            'slug' => 'wishlist-inactive-product',
            'status' => 'draft',
        ]);

        $customer->wishlists()->createMany([
            ['product_id' => $visibleProduct->id],
            ['product_id' => $hiddenProduct->id],
            ['product_id' => $inactiveProduct->id],
        ]);

        $this->actingAs($customer)
            ->get(route('account.wishlist.index'))
            ->assertOk()
            ->assertSee('محصول ذخیره‌شده قابل نمایش')
            ->assertDontSee('محصول دسته غیرفعال')
            ->assertDontSee('محصول غیرفعال');

        $this->actingAs($customer)
            ->from(route('account.wishlist.index'))
            ->post(route('account.wishlist.store', $hiddenProduct))
            ->assertRedirect(route('account.wishlist.index'))
            ->assertSessionHas('error');

        $this->assertSame(3, $customer->wishlists()->count());
    }

    public function test_cart_requires_an_active_selection_for_each_configured_variant_type(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول با گزینه غیرفعال',
            'slug' => 'product-with-disabled-option-type',
            'stock' => 20,
        ]);

        $color = ProductVariant::create([
            'product_id' => $product->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'cream',
            'sku' => 'LIV-TEST-COLOR-1',
            'price_adjustment' => 0,
            'stock' => 5,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'type' => 'size',
            'name' => 'سایز',
            'value' => 'large',
            'sku' => 'LIV-TEST-SIZE-1',
            'price_adjustment' => 0,
            'stock' => 5,
            'is_active' => false,
        ]);

        $this->postJson(route('cart.add', $product), [
            'quantity' => 1,
            'variants' => [
                'color' => $color->id,
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variants.size');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_paid_order_inventory_deduction_is_idempotent_for_products_and_variants(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول موجودی پایه',
            'slug' => 'base-stock-inventory-product',
            'stock' => 8,
        ]);

        $variantProduct = $this->product($this->category(), [
            'name' => 'محصول موجودی تنوع',
            'slug' => 'variant-stock-inventory-product',
            'stock' => 30,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'cream',
            'sku' => 'LIV-TEST-INVENTORY-VARIANT',
            'price_adjustment' => 0,
            'stock' => 6,
            'is_active' => true,
        ]);

        $order = $this->inventoryOrder();
        $order->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_options' => null,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 100,
            'total' => 200,
        ]);
        $order->items()->create([
            'product_id' => $variantProduct->id,
            'product_variant_id' => $variant->id,
            'variant_options' => [[
                'id' => $variant->id,
                'type' => 'color',
                'name' => 'رنگ',
                'value' => 'cream',
            ]],
            'product_name' => $variantProduct->name,
            'sku' => $variant->sku,
            'quantity' => 3,
            'unit_price' => 100,
            'total' => 300,
        ]);

        $service = app(OrderInventoryService::class);
        $service->deductForPaidOrder($order->fresh());
        $service->deductForPaidOrder($order->fresh());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 6,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $variantProduct->id,
            'stock' => 30,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock' => 3,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'inventory_status' => 'deducted',
        ]);
    }

    public function test_inventory_conflict_does_not_partially_decrement_an_order(): void
    {
        $availableProduct = $this->product($this->category(), [
            'name' => 'محصول دارای موجودی',
            'slug' => 'inventory-conflict-available',
            'stock' => 5,
        ]);
        $emptyProduct = $this->product($this->category(), [
            'name' => 'محصول بدون موجودی',
            'slug' => 'inventory-conflict-empty',
            'stock' => 0,
        ]);

        $order = $this->inventoryOrder();
        foreach ([$availableProduct, $emptyProduct] as $product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => null,
                'variant_options' => null,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => 100,
                'total' => 100,
            ]);
        }

        app(OrderInventoryService::class)->deductForPaidOrder($order->fresh());

        $this->assertDatabaseHas('products', [
            'id' => $availableProduct->id,
            'stock' => 5,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $emptyProduct->id,
            'stock' => 0,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'inventory_status' => 'stock_conflict',
        ]);
    }

    public function test_product_related_products_pivot_supports_sync_and_ordering(): void
    {
        $category = $this->category();

        $product = $this->product($category, [
            'name' => 'محصول اصلی ارتباطی',
            'slug' => 'related-products-owner',
        ]);
        $firstRelated = $this->product($category, [
            'name' => 'محصول مرتبط اول',
            'slug' => 'related-products-first',
        ]);
        $secondRelated = $this->product($category, [
            'name' => 'محصول مرتبط دوم',
            'slug' => 'related-products-second',
        ]);

        $product->relatedProducts()->sync([
            $secondRelated->id => ['sort_order' => 2],
            $firstRelated->id => ['sort_order' => 1],
        ]);

        $this->assertSame(
            [$firstRelated->id, $secondRelated->id],
            $product->relatedProducts()->pluck('products.id')->all()
        );
        $this->assertDatabaseHas('product_related_products', [
            'product_id' => $product->id,
            'related_product_id' => $firstRelated->id,
            'sort_order' => 1,
        ]);
    }

    public function test_product_primary_image_and_fallback_order_are_deterministic(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول تصویر اصلی',
            'slug' => 'primary-image-order-product',
        ]);

        $later = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/later.webp',
            'sort_order' => 2,
            'is_primary' => false,
        ]);
        $firstFallback = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/fallback-first.webp',
            'sort_order' => 1,
            'is_primary' => false,
        ]);
        $primary = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/primary.webp',
            'sort_order' => 9,
            'is_primary' => true,
        ]);

        $this->assertSame($primary->id, $product->fresh()->primaryImage()?->id);

        $primary->update(['is_primary' => false]);

        $this->assertSame($firstFallback->id, $product->fresh()->primaryImage()?->id);
        $this->assertNotSame($later->id, $product->fresh()->primaryImage()?->id);
    }

    public function test_category_page_links_back_to_the_shop_using_its_slug(): void
    {
        $category = $this->category([
            'name' => 'دسته لینک',
            'slug' => 'category-link-slug',
        ]);

        $product = $this->product($category, [
            'name' => 'محصول دسته لینک',
            'slug' => 'category-link-product',
        ]);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee(route('shop.index', ['category' => $category->slug]), false)
            ->assertSee($product->name);

        $this->get(route('shop.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_public_category_index_uses_bounded_pagination(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->category([
                'name' => 'دسته ' . $i,
                'slug' => 'category-' . $i,
                'sort_order' => $i,
            ]);
        }

        $this->get('/categories')
            ->assertOk()
            ->assertSee('sg-category-grid--index', false)
            ->assertSee('sg-category-index-tile__media', false)
            ->assertViewHas('categories', function ($categories) {
                return $categories instanceof LengthAwarePaginator
                    && $categories->perPage() === 12
                    && $categories->total() === 13;
            });
    }

    public function test_category_product_listing_uses_server_side_pagination_and_keeps_filters(): void
    {
        $category = $this->category([
            'name' => 'دسته صفحه‌بندی محصولات',
            'slug' => 'category-products-pagination',
        ]);
        $products = [];

        for ($i = 1; $i <= 13; $i++) {
            $products[] = $this->product($category, [
                'name' => 'محصول دسته‌بندی ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'slug' => 'category-page-product-' . $i,
                'installment_enabled' => true,
            ]);
        }

        $this->get(route('categories.show', [
            'category' => $category->slug,
            'installment' => '1',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('products', function ($paginator) {
                return $paginator instanceof LengthAwarePaginator
                    && $paginator->perPage() === 12
                    && $paginator->total() === 13
                    && $paginator->currentPage() === 2;
            })
            ->assertSee($products[12]->name)
            ->assertDontSee($products[0]->name)
            ->assertSee('installment=1', false);
    }

    public function test_homepage_sections_never_fall_back_to_products_that_do_not_match_their_title(): void
    {
        $category = $this->category();
        $installmentProduct = $this->product($category, [
            'name' => 'محصول واقعی اقساطی صفحه اصلی',
            'slug' => 'homepage-real-installment',
            'installment_enabled' => true,
        ]);
        $unrelatedProduct = $this->product($category, [
            'name' => 'محصول غیر اقساطی و غیر ویژه',
            'slug' => 'homepage-unrelated-product',
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $installmentProduct->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'کرم',
            'sku' => 'HOME-REAL-INSTALLMENT-CREAM',
            'price_adjustment' => 0,
            'stock' => 3,
            'is_active' => true,
        ]);
        $variantImage = ProductImage::query()->create([
            'product_id' => $installmentProduct->id,
            'path' => 'https://images.example.test/homepage/variant-cover.webp',
            'alt' => 'تصویر وریانت محصول اقساطی',
            'sort_order' => 0,
            'is_primary' => false,
            'is_variant_only' => true,
        ]);
        $variant->images()->attach($variantImage->id);

        HomeSection::query()->create([
            'key' => 'homepage-installments',
            'type' => 'products',
            'enabled' => true,
            'sort_order' => 10,
            'title' => 'محصولات اقساطی واقعی',
            'payload' => ['product_source' => 'installments', 'limit' => 8],
        ]);
        HomeSection::query()->create([
            'key' => 'featured',
            'type' => 'products',
            'enabled' => true,
            'sort_order' => 20,
            'title' => 'محصولات ویژه واقعی',
            'payload' => ['product_source' => 'latest', 'limit' => 8],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($installmentProduct->name)
            ->assertSee('https://images.example.test/homepage/variant-cover.webp', false)
            ->assertDontSee($unrelatedProduct->name)
            ->assertViewHas('homeSections', function ($sections) use ($installmentProduct) {
                $installments = $sections->firstWhere('key', 'homepage-installments');
                $featured = $sections->firstWhere('key', 'featured');
                $product = $installments?->products?->first();

                return $installments?->products?->modelKeys() === [$installmentProduct->id]
                    && $featured?->products?->isEmpty()
                    && $product?->relationLoaded('variants')
                    && $product->variants->first()?->relationLoaded('images')
                    && $product->variants->first()?->images->first()?->relationLoaded('media');
            });
    }

    public function test_shop_applies_each_visible_catalogue_filter(): void
    {
        $category = $this->category();

        $installmentProduct = $this->product($category, [
            'name' => 'محصول اقساطی آزمون',
            'slug' => 'filter-installment-product',
            'installment_enabled' => true,
            'stock' => 0,
        ]);
        $featuredProduct = $this->product($category, [
            'name' => 'محصول ویژه آزمون',
            'slug' => 'filter-featured-product',
            'is_featured' => true,
            'stock' => 0,
        ]);
        $newProduct = $this->product($category, [
            'name' => 'محصول جدید آزمون',
            'slug' => 'filter-new-product',
            'is_new' => true,
            'stock' => 0,
        ]);
        $inStockProduct = $this->product($category, [
            'name' => 'محصول موجود آزمون',
            'slug' => 'filter-in-stock-product',
            'stock' => 4,
        ]);
        $unmatchedProduct = $this->product($category, [
            'name' => 'محصول بدون ویژگی آزمون',
            'slug' => 'filter-unmatched-product',
            'stock' => 0,
        ]);

        foreach ([
            ['installment', $installmentProduct],
            ['featured', $featuredProduct],
            ['new', $newProduct],
            ['in_stock', $inStockProduct],
        ] as [$filter, $expectedProduct]) {
            $this->get(route('shop.index', [$filter => '1']))
                ->assertOk()
                ->assertSee($expectedProduct->name)
                ->assertDontSee($unmatchedProduct->name);
        }
    }

    public function test_shop_combines_category_search_and_price_filters(): void
    {
        $category = $this->category([
            'name' => 'دسته جستجو',
            'slug' => 'search-category',
        ]);
        $otherCategory = $this->category([
            'name' => 'دسته دیگر',
            'slug' => 'other-search-category',
        ]);

        $matching = $this->product($category, [
            'name' => 'مبل راحتی کرم',
            'slug' => 'search-price-matching',
            'price' => 50000000,
        ]);
        $this->product($category, [
            'name' => 'مبل راحتی گران',
            'slug' => 'search-price-too-high',
            'price' => 150000000,
        ]);
        $this->product($otherCategory, [
            'name' => 'مبل راحتی دسته دیگر',
            'slug' => 'search-other-category',
            'price' => 50000000,
        ]);

        $this->get(route('shop.index', [
            'category' => $category->slug,
            'search' => 'راحتی',
            'min_price' => 40000000,
            'max_price' => 80000000,
        ]))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee('مبل راحتی گران')
            ->assertDontSee('مبل راحتی دسته دیگر');
    }

    public function test_shop_sort_options_match_the_visible_sorting_menu(): void
    {
        $category = $this->category();

        $oldest = $this->product($category, [
            'name' => 'محصول قدیمی‌تر آزمون',
            'slug' => 'sorting-oldest-product',
        ]);
        $newest = $this->product($category, [
            'name' => 'محصول جدیدتر آزمون',
            'slug' => 'sorting-newest-product',
        ]);

        $oldest->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $newest->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        foreach (['latest', 'oldest', 'price_asc', 'price_desc', 'name_asc'] as $sort) {
            $this->get(route('shop.index', ['sort' => $sort]))
                ->assertOk();
        }

        $this->get(route('shop.index', ['sort' => 'oldest']))
            ->assertViewHas('products', fn ($products) => $products->first()?->is($oldest));

        $this->get(route('shop.index', ['sort' => 'latest']))
            ->assertViewHas('products', fn ($products) => $products->first()?->is($newest));
    }

    public function test_shop_pagination_keeps_active_filters_and_has_a_fixed_page_size(): void
    {
        $category = $this->category();
        $products = [];

        for ($i = 1; $i <= 13; $i++) {
            $products[] = $this->product($category, [
                'name' => 'محصول صفحه‌بندی ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'slug' => 'pagination-product-' . $i,
                'installment_enabled' => true,
                'is_featured' => true,
                'is_new' => true,
            ]);
        }

        $this->product($category, [
            'name' => 'محصول خارج از فیلتر صفحه‌بندی',
            'slug' => 'pagination-excluded-product',
        ]);

        $this->get(route('shop.index', [
            'installment' => '1',
            'featured' => '1',
            'new' => '1',
            'sort' => 'oldest',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('products', function ($paginator) {
                return $paginator instanceof LengthAwarePaginator
                    && $paginator->perPage() === 8
                    && $paginator->total() === 13
                    && $paginator->currentPage() === 2;
            })
            ->assertSee($products[12]->name)
            ->assertDontSee($products[0]->name)
            ->assertSee('installment=1', false)
            ->assertSee('featured=1', false)
            ->assertSee('new=1', false);
    }

    public function test_shop_limits_eager_loaded_card_images_to_one_per_product(): void
    {
        $category = $this->category();

        for ($i = 1; $i <= 3; $i++) {
            $product = $this->product($category, [
                'name' => 'محصول تصویر ' . $i,
                'slug' => 'product-card-image-' . $i,
            ]);

            for ($image = 1; $image <= 4; $image++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => "products/{$product->id}/image-{$image}.webp",
                    'alt' => $product->name . ' ' . $image,
                    'sort_order' => $image,
                    'is_primary' => $image === 1,
                ]);
            }
        }

        $this->get('/shop')
            ->assertOk()
            ->assertViewHas('products', function ($products) {
                return $products->count() === 3
                    && $products->every(fn ($product) => $product->images->count() === 1);
            });
    }

    public function test_homepage_popular_and_featured_sections_use_their_real_product_sources(): void
    {
        $category = $this->category();
        $bestSeller = $this->product($category, [
            'name' => 'محصول پرفروش واقعی',
            'slug' => 'homepage-best-seller-real',
        ]);
        $runnerUp = $this->product($category, [
            'name' => 'محصول رتبه دوم فروش',
            'slug' => 'homepage-runner-up',
        ]);
        $featured = $this->product($category, [
            'name' => 'محصول ویژه واقعی',
            'slug' => 'homepage-featured-real',
            'is_featured' => true,
        ]);

        foreach ([[$bestSeller, 8], [$runnerUp, 3]] as [$product, $quantity]) {
            $order = $this->inventoryOrder();
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => 1000,
                'total' => 1000 * $quantity,
            ]);
        }

        HomeSection::query()->create([
            'key' => 'best-selling',
            'type' => 'products',
            'enabled' => true,
            'sort_order' => 10,
            'title' => 'پرفروش‌ترین‌ها',
            'payload' => ['product_source' => 'popular', 'limit' => 8],
        ]);
        HomeSection::query()->create([
            'key' => 'featured-products',
            'type' => 'products',
            'enabled' => true,
            'sort_order' => 20,
            'title' => 'محصولات ویژه',
            'payload' => ['product_source' => 'featured', 'limit' => 8],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('homeSections', function ($sections) use ($bestSeller, $runnerUp, $featured) {
                return $sections->firstWhere('key', 'best-selling')?->products?->modelKeys()
                        === [$bestSeller->id, $runnerUp->id]
                    && $sections->firstWhere('key', 'featured-products')?->products?->modelKeys()
                        === [$featured->id];
            });
    }

    public function test_homepage_variant_media_queries_stay_bounded_as_product_count_grows(): void
    {
        $category = $this->category();
        $this->productWithVariantImage($category, 1);

        HomeSection::query()->create([
            'key' => 'query-count-products',
            'type' => 'products',
            'enabled' => true,
            'sort_order' => 10,
            'title' => 'محصولات تست تعداد کوئری',
            'payload' => ['product_source' => 'latest', 'limit' => 24],
        ]);

        $queryCount = 0;
        DB::listen(function (QueryExecuted $query) use (&$queryCount): void {
            if (preg_match('/^\s*select\b/i', $query->sql)) {
                $queryCount++;
            }
        });

        $this->get('/')->assertOk();
        $singleProductQueries = $queryCount;

        for ($i = 2; $i <= 12; $i++) {
            $this->productWithVariantImage($category, $i);
        }

        $queryCount = 0;
        $this->get('/')->assertOk();

        $this->assertLessThanOrEqual(
            $singleProductQueries + 3,
            $queryCount,
            'Homepage product and variant media queries must be eager-loaded, not executed once per card.'
        );
    }

    public function test_dashboard_top_product_media_queries_stay_bounded_as_sales_grow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $recordSale = function (int $index, int $quantity = 1) use ($category): void {
            $product = $this->productWithVariantImage($category, $index);
            $order = $this->inventoryOrder();

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => 1000,
                'total' => 1000 * $quantity,
            ]);
        };

        $recordSale(1, 5);

        $queryCount = 0;
        DB::listen(function (QueryExecuted $query) use (&$queryCount): void {
            if (preg_match('/^\s*select\b/i', $query->sql)) {
                $queryCount++;
            }
        });

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $singleProductQueries = $queryCount;

        for ($i = 2; $i <= 5; $i++) {
            $recordSale($i, 6 - $i);
        }

        $queryCount = 0;
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertLessThanOrEqual(
            $singleProductQueries + 3,
            $queryCount,
            'Dashboard top-product images and variant galleries must not add one query per sold product.'
        );
    }

    public function test_dashboard_uses_correct_paid_revenue_aggregates_for_current_and_previous_month(): void
    {
        $admin = User::create([
            'name' => 'Dashboard Metrics Admin',
            'email' => 'dashboard-metrics-admin@example.test',
            'password' => Hash::make('DashboardMetrics123'),
            'role' => 'admin',
        ]);

        $currentMonthOrder = $this->inventoryOrder();
        $currentMonthOrder->forceFill([
            'total' => 300,
            'status' => 'delivered',
            'created_at' => now()->startOfMonth()->addDay(),
            'updated_at' => now()->startOfMonth()->addDay(),
        ])->save();

        $previousMonthOrder = $this->inventoryOrder();
        $previousMonthOrder->forceFill([
            'total' => 150,
            'status' => 'pending',
            'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(),
            'updated_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(),
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalOrders', 2)
            ->assertViewHas('paidOrders', 2)
            ->assertViewHas('totalRevenue', 450.0)
            ->assertViewHas('currentMonthRevenue', 300.0)
            ->assertViewHas('previousMonthRevenue', 150.0)
            ->assertViewHas('currentMonthOrders', 1)
            ->assertViewHas('revenueGrowthPercent', 100.0)
            ->assertViewHas('orderStatus', function ($status) {
                return $status['delivered'] === 1
                    && $status['pending'] === 1;
            })
            ->assertViewHas('monthlyRevenue', function ($months) {
                return $months->count() === 6
                    && $months->last()['revenue'] === 300.0
                    && $months->last()['orders'] === 1;
            });
    }

    public function test_password_recovery_does_not_reveal_whether_an_email_exists(): void
    {
        Notification::fake();

        $customer = User::create([
            'name' => 'Recovery Test Customer',
            'email' => 'recovery-user@example.test',
            'password' => Hash::make('OldPass123A'),
            'role' => 'customer',
        ]);

        $genericStatus = 'اگر حسابی با این ایمیل ثبت شده باشد، لینک بازنشانی برای آن ارسال می‌شود.';

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => $customer->email,
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', $genericStatus);

        Notification::assertSentTo($customer, ResetPassword::class);

        $this->from(route('password.request'))
            ->post(route('password.email'), [
                'email' => 'missing-account@example.test',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', $genericStatus);
    }

    public function test_password_reset_consumes_its_token_and_updates_the_password(): void
    {
        $customer = User::create([
            'name' => 'Reset Test Customer',
            'email' => 'reset-user@example.test',
            'password' => Hash::make('OldPass123A'),
            'role' => 'customer',
        ]);

        $token = Password::createToken($customer);
        $replacementPassword = 'NewPass456B';

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $customer->email,
            'password' => $replacementPassword,
            'password_confirmation' => $replacementPassword,
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'رمز عبور تغییر کرد. حالا وارد حساب شوید.');

        $customer->refresh();

        $this->assertTrue(Hash::check($replacementPassword, $customer->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $customer->email,
        ]);
    }

    public function test_customer_login_normalizes_email_and_rejects_role_mismatched_intended_routes(): void
    {
        $customer = User::create([
            'name' => 'Login Redirect Customer',
            'email' => 'login-redirect@example.test',
            'password' => Hash::make('LivoraTest123'),
            'role' => 'customer',
        ]);

        $this->withSession([
            'url.intended' => route('admin.dashboard'),
        ])
            ->post(route('login.store'), [
                'email' => '  LOGIN-REDIRECT@EXAMPLE.TEST ',
                'password' => 'LivoraTest123',
            ])
            ->assertRedirect(route('account.index'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_admin_login_cannot_be_redirected_into_customer_pages(): void
    {
        $admin = User::create([
            'name' => 'Login Redirect Admin',
            'email' => 'login-redirect-admin@example.test',
            'password' => Hash::make('LivoraTest123'),
            'role' => 'admin',
        ]);

        $this->withSession([
            'url.intended' => route('account.index'),
        ])
            ->post(route('login.store'), [
                'email' => $admin->email,
                'password' => 'LivoraTest123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_rejects_external_intended_redirects(): void
    {
        $customer = User::create([
            'name' => 'Open Redirect Customer',
            'email' => 'open-redirect@example.test',
            'password' => Hash::make('LivoraTest123'),
            'role' => 'customer',
        ]);

        $this->withSession([
            'url.intended' => 'https://attacker.example/phishing',
        ])
            ->post(route('login.store'), [
                'email' => $customer->email,
                'password' => 'LivoraTest123',
            ])
            ->assertRedirect(route('account.index'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_guests_cannot_reach_customer_or_admin_pages(): void
    {
        foreach ([
            '/checkout',
            '/account',
            '/account/orders',
            '/admin',
            '/admin/orders',
        ] as $uri) {
            $this->get($uri)
                ->assertRedirect(route('login'));
        }
    }

    public function test_checkout_rejects_a_stale_cart_price_instead_of_charging_silently(): void
    {
        $category = $this->category();
        $product = $this->product($category, [
            'name' => 'محصول با قیمت تغییرکرده',
            'slug' => 'changed-price-product',
            'price' => 1200000,
            'stock' => 5,
        ]);

        $customer = User::create([
            'name' => 'Livora Test Customer',
            'email' => 'livora-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => 1000000,
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'first_name' => 'آزمون',
                'last_name' => 'لیورا',
                'phone' => '09123456789',
                'email' => 'buyer@example.test',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، پلاک ۱',
                'postal_code' => '1234567890',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'unit_price' => 1000000,
        ]);
    }

    public function test_guest_can_update_remove_and_clear_only_their_own_session_cart(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول سبد مهمان',
            'slug' => 'guest-cart-product',
            'stock' => 8,
        ]);

        // The test client encrypts raw cookie values before sending them,
        // matching the framework's real encrypted session-cookie contract.
        $this->get(route('cart.index'))->assertOk();

        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('status', 'active')
            ->latest('id')
            ->firstOrFail();

        $this->withCookie(
            config('session.cookie'),
            (string) $cart->session_id
        );
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        // Laravel's test client omits cookies on JSON requests unless
        // credentials are explicitly enabled. Preserve the same guest session
        // for this request and subsequent JSON mutations.
        $this->withCredentials()
            ->patchJson(route('cart.update', $item), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('cart_count', 3);

        $this->patchJson(route('cart.update', $item), ['quantity' => 4])
            ->assertOk()
            ->assertJsonPath('cart_count', 4);

        $this->deleteJson(route('cart.remove', $item))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);

        $secondItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->deleteJson(route('cart.clear'))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $secondItem->id]);
    }

    public function test_guest_cannot_modify_another_browser_session_cart(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'محصول نشست دیگر',
            'slug' => 'foreign-session-cart-product',
        ]);

        $cart = Cart::create([
            'user_id' => null,
            'session_id' => 'unrelated-browser-session',
            'status' => 'active',
        ]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => 'base',
            'variant_options' => null,
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);

        $this->patchJson(route('cart.update', $item), ['quantity' => 2])
            ->assertForbidden();
        $this->deleteJson(route('cart.remove', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    public function test_registration_persists_the_normalized_mobile_number(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'کاربر',
            'last_name' => 'آزمایشی',
            'email' => 'registration-phone@example.test',
            'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            'password' => 'Livora123Ab',
            'password_confirmation' => 'Livora123Ab',
            'terms' => '1',
        ])
            ->assertRedirect(route('account.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'registration-phone@example.test',
            'name' => 'کاربر آزمایشی',
            'phone' => '09123456789',
            'role' => 'customer',
        ]);
    }

    public function test_customer_account_profile_and_address_pages_are_real_and_work(): void
    {
        $customer = User::create([
            'name' => 'Livora Account Customer',
            'email' => 'account-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('account.profile.edit'))
            ->assertOk()
            ->assertSee('ویرایش پروفایل');

        $this->actingAs($customer)
            ->get(route('account.addresses.index'))
            ->assertOk()
            ->assertSee('افزودن آدرس جدید');

        $this->actingAs($customer)
            ->put(route('account.profile.update'), [
                'name' => 'Updated Account Customer',
                'email' => 'account-customer@example.test',
                'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Account Customer',
            'phone' => '09123456789',
        ]);

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), [
                'title' => 'خانه',
                'first_name' => 'آزمون',
                'last_name' => 'کاربر',
                'phone' => '۰۹۱۲۳۴۵۶۷۸۹',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، کوچه یک، پلاک ده',
                'postal_code' => '۱۲۳۴۵۶۷۸۹۰',
                'unit' => '۲',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $address = Address::query()
            ->where('user_id', $customer->id)
            ->firstOrFail();

        $this->assertSame('09123456789', $address->phone);
        $this->assertSame('1234567890', $address->postal_code);
        $this->assertTrue($address->is_default);

        $this->actingAs($customer)
            ->get(route('account.addresses.edit', $address))
            ->assertOk()
            ->assertSee('ویرایش آدرس ارسال');

        $this->actingAs($customer)
            ->put(route('account.addresses.update', $address), [
                'title' => 'خانه جدید',
                'first_name' => 'آزمون',
                'last_name' => 'کاربر',
                'phone' => '09123456789',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، کوچه دو، پلاک یازده',
                'postal_code' => '1234567890',
                'unit' => '۳',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'title' => 'خانه جدید',
            'is_default' => true,
        ]);
    }

    public function test_customer_cannot_view_or_modify_another_customers_address(): void
    {
        $owner = User::create([
            'name' => 'Address Owner',
            'email' => 'address-owner@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);
        $otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other-address-customer@example.test',
            'password' => Hash::make('test-password'),
            'role' => 'customer',
        ]);

        $address = Address::create([
            'user_id' => $owner->id,
            'title' => 'خصوصی',
            'first_name' => 'صاحب',
            'last_name' => 'آدرس',
            'phone' => '09123456789',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'نشانی خصوصی برای آزمون',
            'postal_code' => '1234567890',
            'is_default' => true,
        ]);

        $this->actingAs($otherCustomer)
            ->get(route('account.addresses.edit', $address))
            ->assertForbidden();

        $this->actingAs($otherCustomer)
            ->put(route('account.addresses.update', $address), [
                'title' => 'دستکاری',
                'first_name' => 'دستکاری',
                'last_name' => 'دستکاری',
                'phone' => '09111111111',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'نشانی دستکاری شده',
                'postal_code' => '1111111111',
                'is_default' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $owner->id,
            'title' => 'خصوصی',
        ]);
    }

    public function test_guest_quick_add_updates_the_shared_cart_badge_and_product_cards_offer_quick_add(): void
    {
        $product = $this->product($this->category(), [
            'name' => 'میز عسلی افزودن سریع',
            'slug' => 'quick-add-side-table',
            'stock' => 6,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-cart-add-form', false)
            ->assertSee('data-cart-count="0"', false);

        $this->withCredentials()
            ->postJson(route('cart.add', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('cart_count', 1)
            ->assertJsonPath('message', 'محصول به سبد اضافه شد.');

        $cart = Cart::query()
            ->whereNull('user_id')
            ->where('status', 'active')
            ->latest('id')
            ->firstOrFail();

        // Explicitly preserve the same session identifier when rendering the
        // next page, matching the guest-cookie contract used by cart tests.
        $this->withCookie(
            config('session.cookie'),
            (string) $cart->session_id
        )->get('/')
            ->assertOk()
            ->assertSee('data-cart-count="1"', false);
    }

    public function test_contact_page_and_footer_render_dashboard_social_links_as_icons_only(): void
    {
        ContactSetting::query()->create([
            'brand_name' => 'SillaGallery',
            'phone' => '09120000000',
            'email' => 'hello@sillashop.com',
            'whatsapp_url' => 'https://wa.me/989120000000',
            'telegram_url' => 'https://t.me/sillagallery',
            'instagram_url' => 'https://instagram.com/sillagallery',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Cache::forget('storefront.settings');

        foreach (['/', '/contact'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('aria-label="WhatsApp"', false)
                ->assertSee('aria-label="Telegram"', false)
                ->assertSee('aria-label="Instagram"', false)
                ->assertSee('https://wa.me/989120000000', false)
                ->assertSee('https://t.me/sillagallery', false)
                ->assertSee('https://instagram.com/sillagallery', false)
                ->assertDontSee('>WhatsApp</a>', false)
                ->assertDontSee('>Telegram</a>', false)
                ->assertDontSee('>Instagram</a>', false);
        }
    }

    private function productWithVariantImage(Category $category, int $index): Product
    {
        $product = $this->product($category, [
            'name' => 'محصول تصویر وریانت ' . $index,
            'slug' => 'variant-image-query-product-' . $index,
            'sku' => 'LIV-QUERY-VARIANT-' . $index,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'رنگ ' . $index,
            'sku' => 'LIV-QUERY-VARIANT-SKU-' . $index,
            'price_adjustment' => 0,
            'stock' => 4,
            'is_active' => true,
        ]);

        $image = ProductImage::query()->create([
            'product_id' => $product->id,
            'path' => "https://images.example.test/query/variant-{$index}.webp",
            'alt' => $product->name,
            'sort_order' => 0,
            'is_primary' => false,
            'is_variant_only' => true,
        ]);

        $variant->images()->attach($image->id);

        return $product;
    }

    private function inventoryOrder(): Order
    {
        static $sequence = 0;
        $sequence++;

        return Order::create([
            'user_id' => null,
            'order_number' => 'LV-INVENTORY-TEST-' . $sequence,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'online',
            'payment_provider' => 'test-gateway',
            'subtotal' => 500,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 500,
            'first_name' => 'آزمون',
            'last_name' => 'موجودی',
            'phone' => '09123456789',
            'email' => 'inventory@example.test',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'آدرس آزمایشی',
            'postal_code' => '1234567890',
            'unit' => null,
            'notes' => null,
            'installment_enabled' => false,
            'inventory_status' => 'pending',
        ]);
    }

    private function category(array $attributes = []): Category
    {
        static $sequence = 0;
        $sequence++;

        return Category::create(array_merge([
            'name' => 'دسته آزمایشی ' . $sequence,
            'slug' => 'test-category-' . $sequence,
            'description' => 'دسته آزمایشی برای بررسی فروشگاه',
            'image' => null,
            'is_active' => true,
            'sort_order' => $sequence,
        ], $attributes));
    }

    private function product(Category $category, array $attributes = []): Product
    {
        static $sequence = 0;
        $sequence++;

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'محصول آزمایشی ' . $sequence,
            'slug' => 'test-product-' . $sequence,
            'sku' => 'LIV-TEST-' . $sequence,
            'short_description' => 'توضیح کوتاه آزمایشی',
            'description' => 'توضیح کامل آزمایشی',
            'price' => 1000000,
            'compare_at_price' => null,
            'stock' => 5,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'meta_title' => null,
            'meta_description' => null,
            'installment_enabled' => false,
        ], $attributes));
    }
}
