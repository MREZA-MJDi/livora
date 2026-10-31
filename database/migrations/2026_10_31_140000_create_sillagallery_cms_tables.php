<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_settings', function (Blueprint $table): void {
            $table->string('default_og_image')->nullable();
            $table->string('twitter_handle', 80)->nullable();
            $table->string('google_site_verification', 255)->nullable();
            $table->string('bing_site_verification', 255)->nullable();
            $table->string('organization_name')->nullable();
            $table->text('organization_description')->nullable();
            $table->string('organization_logo')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->json('social_links')->nullable();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_image')->nullable();
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_image')->nullable();
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_image')->nullable();
        });

        Schema::create('content_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('seo_index')->default(false);
            $table->boolean('seo_follow')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index(['seo_index', 'status']);
        });

        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 100)->unique();
            $table->timestamps();
            $table->unique('name');
        });

        Schema::create('blog_post_tag', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_tag_id']);
        });

        $now = now();
        DB::table('content_pages')->insert([
            [
                'user_id' => null,
                'title' => 'درباره SillaGallery',
                'slug' => 'about',
                'excerpt' => 'با نگاه، ارزش‌ها و رویکرد SillaGallery در انتخاب مبلمان و عناصر خانه آشنا شوید.',
                'body' => '<h1>درباره SillaGallery</h1><p>SillaGallery با تمرکز بر مبلمان و عناصر خانه، تلاش می‌کند انتخاب محصول را به تجربه‌ای روشن، دقیق و دلنشین تبدیل کند.</p><h2>نگاه ما</h2><p>ما باور داریم انتخاب خوب فقط به ظاهر محصول محدود نمی‌شود؛ کیفیت متریال، تناسب با فضا، کاربرد روزمره و دوام در طول زمان نیز اهمیت دارند.</p><h2>چیزهایی که برای ما مهم‌اند</h2><ul><li><strong>طراحی:</strong> فرم و ظاهر باید در خدمت فضای زندگی باشد.</li><li><strong>کیفیت:</strong> انتخاب متریال و ساخت خوب، ارزش محصول را در طول زمان حفظ می‌کند.</li><li><strong>شفافیت:</strong> مشخصات، قیمت، موجودی و شرایط خرید باید روشن باشند.</li><li><strong>تجربه:</strong> مسیر کشف، بررسی و سفارش باید ساده و قابل‌اعتماد باشد.</li></ul><h2>از کشف تا انتخاب</h2><p>در فروشگاه می‌توانید مجموعه‌ها را بررسی کنید، مشخصات محصولات را بخوانید و شرایط خرید را پیش از ثبت سفارش ببینید.</p><p>برای دریافت راهنمایی بیشتر از <a href="/contact">صفحه تماس با ما</a> با ما در ارتباط باشید.</p>',
                'meta_title' => 'درباره SillaGallery | مبلمان و لوازم خانه',
                'meta_description' => 'با داستان، رویکرد و ارزش‌های SillaGallery در انتخاب مبلمان و عناصر خانه آشنا شوید.',
                'status' => 'published',
                'seo_index' => false,
                'seo_follow' => false,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => null,
                'title' => 'قوانین و شرایط استفاده',
                'slug' => 'terms',
                'excerpt' => 'شرایط عمومی استفاده از فروشگاه SillaGallery.',
                'body' => '<h1>قوانین و شرایط استفاده</h1><p>این صفحه چارچوب عمومی استفاده از فروشگاه SillaGallery را توضیح می‌دهد. شرایط نهایی فروش، ارسال، بازگشت کالا، ضمانت و حریم خصوصی باید توسط مالک فروشگاه بازبینی و تکمیل شوند.</p><h2>اطلاعات حساب کاربری</h2><p>کاربر مسئول صحت اطلاعاتی است که هنگام ثبت‌نام و ثبت سفارش وارد می‌کند و باید دسترسی به حساب خود را ایمن نگه دارد.</p><h2>کالا، قیمت و موجودی</h2><p>اطلاعات کالا، قیمت و موجودی در صفحه محصول نمایش داده می‌شود. اطلاعات نهایی سفارش باید پیش از تأیید پرداخت بررسی شوند.</p><h2>پرداخت و سفارش</h2><p>وضعیت سفارش و پرداخت در حساب کاربری نمایش داده می‌شود. پرداخت آنلاین فقط پس از تأیید معتبر سرویس پرداخت ثبت خواهد شد.</p><h2>ارسال، مرجوعی و ضمانت</h2><p>زمان و هزینه ارسال، ضوابط لغو یا مرجوعی و شرایط ضمانت باید در سیاست‌های نهایی فروشگاه مشخص شوند.</p><h2>حریم خصوصی</h2><p>اطلاعات حساب، سفارش و نشانی برای ارائه خدمات فروشگاه استفاده می‌شود. جزئیات نگهداری داده‌ها و حقوق کاربران باید در سیاست حریم خصوصی تکمیل شوند.</p><h2>پشتیبانی</h2><p>برای پرسش درباره کالا یا سفارش از <a href="/contact">صفحه تماس با ما</a> استفاده کنید.</p>',
                'meta_title' => 'قوانین و شرایط استفاده | SillaGallery',
                'meta_description' => 'شرایط عمومی استفاده، سفارش، پرداخت و ارتباط با فروشگاه SillaGallery.',
                'status' => 'published',
                'seo_index' => false,
                'seo_follow' => false,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('content_pages');

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn(['canonical_url', 'og_image']);
        });
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description', 'canonical_url', 'og_image']);
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['canonical_url', 'og_image']);
        });
        Schema::table('seo_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'default_og_image', 'twitter_handle', 'google_site_verification',
                'bing_site_verification', 'organization_name', 'organization_description',
                'organization_logo', 'contact_email', 'contact_phone', 'social_links',
            ]);
        });
    }
};