<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use App\Models\ContentPage;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Seeder;

class InitialStoreContentSeeder extends Seeder
{
    public function run(): void
    {
        $settings = ContactSetting::query()->firstOrCreate([], [
            'brand_name' => 'سیلا گالری',
            'brand_description' => 'مجموعه‌ای منتخب از مبلمان و عناصر خانه برای انتخابی آگاهانه و ماندگار.',
            'footer_menu' => [
                'shop' => [['label' => 'همه محصولات', 'url' => '/shop'], ['label' => 'دسته‌بندی‌ها', 'url' => '/categories'], ['label' => 'تازه‌ها', 'url' => '/shop?sort=newest'], ['label' => 'سبد خرید', 'url' => '/cart']],
                'guide' => [['label' => 'قوانین و شرایط', 'url' => '/terms'], ['label' => 'راهنمای انتخاب', 'url' => '/buying-guide'], ['label' => 'پیگیری سفارش‌ها', 'url' => '/login']],
                'contact' => [['label' => 'درباره ما', 'url' => '/about'], ['label' => 'وبلاگ', 'url' => '/blog'], ['label' => 'تماس با ما', 'url' => '/contact']],
            ],
            'payment_methods' => [],
            'copyright_text' => 'تمام حقوق برای سیلا گالری محفوظ است.',
            'phone' => null,
            'email' => null,
            'address' => null,
            'hours' => null,
            'whatsapp_url' => null,
            'instagram_url' => null,
            'warranty_text' => null,
            'shipping_text' => null,
            'returns_text' => null,
            'is_active' => true,
            'floating_actions_enabled' => false,
            'page_content' => [
                'hero_kicker' => 'ارتباط با سیلا گالری',
                'hero_title' => 'برای انتخابی آگاهانه کنار شما هستیم',
                'hero_highlight' => 'پاسخ‌گویی به پرسش‌های شما',
                'hero_description' => 'برای دریافت اطلاعات تکمیلی درباره محصولات، شرایط سفارش و پیگیری درخواست خود پیام بفرستید.',
                'hero_cta' => 'ارسال پیام',
                'breadcrumb_label' => 'تماس با ما',
                'address_label' => 'نشانی فروشگاه',
                'hours_label' => 'ساعات پاسخ‌گویی',
                'installment_label' => 'خرید اقساطی',
                'info_kicker' => 'راه‌های ارتباط',
                'info_title' => 'از ما سؤال کنید',
                'info_description' => 'اطلاعات تماس و نشانی پس از ثبت توسط مدیریت در همین صفحه نمایش داده می‌شود.',
                'installment_note' => 'شرایط اقساط هر محصول در صفحه همان محصول درج می‌شود؛ جزئیات نهایی را پیش از سفارش تأیید کنید.',
                'form_kicker' => 'پیام شما',
                'form_title' => 'درخواست خود را ثبت کنید',
                'form_description' => 'موضوع و راه ارتباطی را وارد کنید تا درخواست شما در پنل مدیریت ثبت شود.',
                'faq_kicker' => 'پرسش‌های متداول',
                'faq_title' => 'پیش از ثبت سفارش',
                'faq_description' => 'پاسخ پرسش‌های پرتکرار درباره بررسی محصول و ثبت درخواست.',
                'final_kicker' => 'راهنمایی خرید',
                'final_title' => 'برای انتخاب بهتر، جزئیات را بررسی کنید',
                'final_description' => 'ابعاد، جنس، رنگ و شرایط خرید هر محصول را پیش از نهایی کردن سفارش با فروشگاه هماهنگ کنید.',
                'final_cta' => 'مشاهده محصولات',
                'success_message' => 'پیام شما ثبت شد. درخواست در پنل مدیریت قابل پیگیری است.',
            ],
            'faqs' => [
                ['question' => 'چطور از موجودی محصول مطلع شوم؟', 'answer' => 'وضعیت موجودی در صفحه محصول نمایش داده می‌شود. برای زمان تأمین کالای ناموجود، درخواست خود را از طریق فرم تماس ثبت کنید.'],
                ['question' => 'آیا امکان خرید اقساطی وجود دارد؟', 'answer' => 'در صورت فعال بودن شرایط اقساط برای یک محصول، پیش‌پرداخت و تعداد چک‌ها در صفحه همان محصول نمایش داده می‌شود. شرایط نهایی باید پیش از سفارش تأیید شود.'],
                ['question' => 'چطور ابعاد محصول را بررسی کنم؟', 'answer' => 'ابعاد درج‌شده در مشخصات محصول را با فضای نصب، مسیر عبور و محل قرارگیری کالا مقایسه کنید.'],
            ],
        ]);

        SeoSetting::query()->updateOrCreate(['id' => 1], [
            'site_name' => $settings->brand_name ?: config('app.name'),
            'organization_name' => $settings->brand_name ?: config('app.name'),
            'organization_description' => $settings->brand_description,
            'organization_logo' => $settings->logo_path,
        ]);
        Cache::forget('seo_settings.current');

        ContentPage::query()->firstOrCreate(
            ['slug' => 'buying-guide'],
            [
                'title' => 'راهنمای انتخاب مبلمان',
                'excerpt' => 'نکته‌های کاربردی برای اندازه‌گیری فضا، انتخاب متریال و بررسی مشخصات پیش از خرید.',
                'body' => '<h1>راهنمای انتخاب مبلمان</h1><p>پیش از انتخاب مبلمان، اندازه فضای موردنظر و مسیر ورود کالا را بررسی کنید. طول و عرض اتاق، محل باز شدن درها، پنجره‌ها و مسیر رفت‌وآمد روی انتخاب ابعاد اثر می‌گذارند.</p><h2>ابعاد و تناسب</h2><p>فقط ابعاد خود محصول را اندازه نگیرید؛ فضای لازم برای حرکت، باز شدن کشوها و استفاده از میزهای کناری را هم در نظر بگیرید. بهتر است محدوده قرارگیری مبلمان را با چسب کاغذی روی زمین مشخص کنید تا تناسب آن با اتاق روشن‌تر شود.</p><h2>متریال و نگهداری</h2><p>نوع پارچه، چوب، فلز و پوشش سطح را با سبک زندگی و میزان استفاده هماهنگ کنید. درباره روش تمیزکاری، مقاومت در برابر نور و رطوبت و امکان تعویض قطعات از فروشنده سؤال کنید.</p><h2>پیش از ثبت سفارش</h2><p>قیمت نهایی، رنگ و جنس انتخابی، ابعاد دقیق، زمان تحویل، هزینه حمل، شرایط نصب و ضمانت را به‌صورت مکتوب دریافت کنید. اگر کالا سفارشی است، جزئیات نهایی را در پیش‌فاکتور بررسی کنید.</p><p>مشخصات محصولات این فروشگاه به‌تدریج با اطلاعات تأییدشده تکمیل می‌شوند؛ هر موردی که نیاز به تأیید فروشنده داشته باشد باید پیش از خرید استعلام شود.</p>',
                'meta_title' => 'راهنمای انتخاب مبلمان | سیلا گالری',
                'meta_description' => 'راهنمای اندازه‌گیری فضا، انتخاب متریال و بررسی شرایط سفارش مبلمان پیش از خرید.',
                'status' => 'published',
                'seo_index' => true,
                'seo_follow' => true,
                'published_at' => now(),
            ]
        );

        
        // Seed the public About page without overwriting content already managed by an administrator.
        $about = ContentPage::query()->firstOrNew(['slug' => 'about']);
        $legacyMigrationBody = '<h1>درباره SillaGallery</h1><p>SillaGallery با تمرکز بر مبلمان و عناصر خانه، تلاش می‌کند انتخاب محصول را به تجربه‌ای روشن، دقیق و دلنشین تبدیل کند.</p><h2>نگاه ما</h2><p>ما باور داریم انتخاب خوب فقط به ظاهر محصول محدود نمی‌شود؛ کیفیت متریال، تناسب با فضا، کاربرد روزمره و دوام در طول زمان نیز اهمیت دارند.</p><h2>چیزهایی که برای ما مهم‌اند</h2><ul><li><strong>طراحی:</strong> فرم و ظاهر باید در خدمت فضای زندگی باشد.</li><li><strong>کیفیت:</strong> انتخاب متریال و ساخت خوب، ارزش محصول را در طول زمان حفظ می‌کند.</li><li><strong>شفافیت:</strong> مشخصات، قیمت، موجودی و شرایط خرید باید روشن باشند.</li><li><strong>تجربه:</strong> مسیر کشف، بررسی و سفارش باید ساده و قابل‌اعتماد باشد.</li></ul><h2>از کشف تا انتخاب</h2><p>در فروشگاه می‌توانید مجموعه‌ها را بررسی کنید، مشخصات محصولات را بخوانید و شرایط خرید را پیش از ثبت سفارش ببینید.</p><p>برای دریافت راهنمایی بیشتر از <a href="/contact">صفحه تماس با ما</a> با ما در ارتباط باشید.</p>';
        $isLegacyMigrationContent = $about->exists
            && $about->title === 'درباره SillaGallery'
            && $about->body === $legacyMigrationBody;
        $aboutWasMissingBody = ! filled($about->body) || $isLegacyMigrationContent;
        $aboutDefaults = [
            'title' => 'درباره سیلا گالری',
            'excerpt' => 'سیلا گالری برای انتخاب آگاهانه‌تر مبلمان و ساختن فضایی هماهنگ با زندگی روزمره شکل گرفته است.',
            'body' => <<<'HTML'
<h2>خانه، بازتابی از شیوه زندگی ما</h2>
<p>ما در سیلا گالری باور داریم انتخاب مبلمان فقط تهیه چند وسیله برای خانه نیست؛ هر انتخاب می‌تواند بر آسایش، نظم و حال‌وهوای فضایی که هر روز در آن زندگی می‌کنیم اثر بگذارد. به همین دلیل تلاش می‌کنیم محصولات و اطلاعات را روشن و قابل مقایسه ارائه کنیم تا تصمیم‌گیری با شناخت بیشتری انجام شود.</p>
<h2>انتخاب با توجه به نیاز واقعی</h2>
<p>ابعاد فضا، سبک استفاده، کیفیت ساخت، جنس متریال و امکان نگهداری، همگی در انتخاب یک محصول مناسب نقش دارند. ما مخاطبان را تشویق می‌کنیم پیش از خرید اندازه‌ها و مسیر ورود کالا را بررسی کنند و مشخصات هر محصول را با نیاز خانه خود بسنجند.</p>
<h2>شفافیت در مسیر خرید</h2>
<p>اطلاعات محصول، قیمت، وضعیت موجودی و شرایط سفارش باید تا حد امکان روشن باشد. اگر درباره ویژگی‌ها یا جزئیات خرید پرسشی وجود داشته باشد، راه ارتباط با مجموعه از طریق صفحه تماس در دسترس است. شرایط نهایی هر سفارش باید پیش از ثبت و پرداخت بررسی و تأیید شود.</p>
<h2>نگاهی ماندگار به خانه</h2>
<p>هدف ما کمک به انتخاب‌هایی است که در کنار زیبایی، برای استفاده روزمره نیز مناسب باشند؛ انتخاب‌هایی متناسب با فضا، سلیقه و اولویت‌های هر خانه.</p>
HTML,
            'featured_image' => 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1800&q=85',
            'featured_image_alt' => 'چیدمان نشیمن مدرن با مبلمان خنثی و نور طبیعی',
            'meta_title' => 'درباره سیلا گالری | مبلمان و سبک زندگی',
            'meta_description' => 'با نگاه سیلا گالری به انتخاب مبلمان، کیفیت، شفافیت اطلاعات و تجربه خرید آشنا شوید.',
            'status' => 'published',
            'seo_index' => true,
            'seo_follow' => true,
            'published_at' => now(),
        ];

        if ($isLegacyMigrationContent) {
            $about->body = $aboutDefaults['body'];
            $about->title = $aboutDefaults['title'];
        }

        foreach ($aboutDefaults as $field => $value) {
            if (! filled($about->getAttribute($field))) {
                $about->setAttribute($field, $value);
            }
        }

        if (! $about->exists) {
            $about->user_id = null;
        }

        if ($aboutWasMissingBody) {
            $about->status = 'published';
            $about->published_at = $about->published_at ?: now();
        }

        $about->save();
    }
}
