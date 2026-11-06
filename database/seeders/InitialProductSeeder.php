<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InitialProductSeeder extends Seeder
{
    public function run(): void
    {
        $categorySlugs = [
            'sofa' => 'مبل-راحتی',
            'chester' => 'مبل-چستر',
            'modern' => 'مبل-مدرن',
            'dining' => 'میز-ناهارخوری',
            'bedroom' => 'تخت-و-اتاق-خواب',
            'table' => 'میز-عسلی-و-جلو-مبلی',
            'decor' => 'آینه-و-دکور',
            'lighting' => 'روشنایی',
        ];

        $categories = collect($categorySlugs)
            ->mapWithKeys(fn (string $slug, string $key): array => [
                $key => Category::query()->where('slug', $slug)->firstOrFail(),
            ]);

        $products = [
            ['category' => 'sofa', 'name' => 'مبل راحتی سه‌نفره آرتا', 'slug' => 'مبل-راحتی-سه-نفره-آرتا', 'sku' => 'SG-SOF-101', 'price' => 78000000, 'stock' => 5, 'featured' => true, 'new' => true, 'installment' => [40, 3, 1], 'dimensions' => 'طول ۲۲۰، عمق ۹۰، ارتفاع ۸۵ سانتی‌متر', 'material' => 'پارچه مبلی و اسکلت چوبی؛ نوع دقیق متریال باید با تولیدکننده تأیید شود', 'colors' => 'طوسی، کرم، سبز زیتونی', 'delivery' => 'زمان تحویل پس از تأیید سفارش اعلام می‌شود', 'description' => 'مبل سه‌نفره با فرم متعادل برای نشیمن‌های خانوادگی. پیش از ثبت سفارش، ابعاد نهایی، پارچه و رنگ قابل سفارش را با فروشگاه هماهنگ کنید.'],
            ['category' => 'sofa', 'name' => 'مبل راحتی ال‌شکل رستا', 'slug' => 'مبل-راحتی-ال-شکل-رستا', 'sku' => 'SG-SOF-102', 'price' => 145000000, 'stock' => 2, 'featured' => true, 'new' => false, 'installment' => [50, 4, 1], 'dimensions' => 'ابعاد کلی وابسته به جهت و اندازه سفارشی', 'material' => 'پارچه مبلی؛ مشخصات فوم و اسکلت نیازمند تأیید فروشنده', 'colors' => 'رنگ‌بندی قابل انتخاب پس از استعلام', 'delivery' => 'زمان ساخت و ارسال هنگام استعلام مشخص می‌شود', 'description' => 'چیدمان ال‌شکل برای استفاده بهتر از گوشه نشیمن و ایجاد فضای نشستن بیشتر. جهت قرارگیری ضلع بلند و ابعاد مسیر ورود را پیش از سفارش بررسی کنید.'],
            ['category' => 'sofa', 'name' => 'مبل راحتی دونفره مانا', 'slug' => 'مبل-راحتی-دو-نفره-مانا', 'sku' => 'SG-SOF-103', 'price' => 52000000, 'stock' => 7, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'طول ۱۶۰، عمق ۸۸، ارتفاع ۸۲ سانتی‌متر', 'material' => 'پارچه مبلی و سازه داخلی؛ نیازمند تطبیق با محصول نهایی', 'colors' => 'کرم، خاکستری، قهوه‌ای روشن', 'delivery' => 'زمان تحویل پس از استعلام', 'description' => 'گزینه‌ای جمع‌وجور برای نشیمن‌های کوچک، اتاق کار یا تکمیل چیدمان کنار یک مبل بزرگ‌تر. اندازه نهایی و نمونه پارچه را پیش از خرید بررسی کنید.'],
            ['category' => 'chester', 'name' => 'مبل چستر چهارنفره کلاسیک', 'slug' => 'مبل-چستر-چهار-نفره-کلاسیک', 'sku' => 'SG-CHS-201', 'price' => 168000000, 'stock' => 1, 'featured' => true, 'new' => false, 'installment' => [50, 4, 2], 'dimensions' => 'طول تقریبی ۲۶۰، عمق ۹۵ سانتی‌متر', 'material' => 'رویه پارچه‌ای یا چرمی بر اساس سفارش؛ نیازمند تأیید', 'colors' => 'قهوه‌ای، زغالی، سبز تیره', 'delivery' => 'زمان ساخت سفارشی پس از استعلام', 'description' => 'فرم چستر با لمسه‌دوزی شاخص برای فضاهایی با حال‌وهوای کلاسیک یا تلفیقی. نوع رویه، جزئیات دوخت و ابعاد نهایی باید در پیش‌فاکتور ثبت شوند.'],
            ['category' => 'chester', 'name' => 'مبل چستر دونفره آوید', 'slug' => 'مبل-چستر-دو-نفره-آوید', 'sku' => 'SG-CHS-202', 'price' => 92000000, 'stock' => 3, 'featured' => false, 'new' => true, 'installment' => [40, 3, 1], 'dimensions' => 'طول ۱۸۰، عمق ۹۰ سانتی‌متر', 'material' => 'رویه قابل انتخاب؛ ساختار داخلی نیازمند تأیید', 'colors' => 'طوسی، سبز یشمی، عسلی', 'delivery' => 'زمان تحویل پس از تأیید سفارش', 'description' => 'نسخه دونفره با جزئیات چستر برای فضاهای محدودتر. تناسب رنگ و جنس رویه با نور محیط و سایر مبلمان را پیش از سفارش بسنجید.'],
            ['category' => 'modern', 'name' => 'مبل مدرن مینیمال نیلا', 'slug' => 'مبل-مدرن-مینیمال-نیلا', 'sku' => 'SG-MOD-301', 'price' => 115000000, 'stock' => 4, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'طول ۲۴۰، عمق ۹۰ سانتی‌متر', 'material' => 'پارچه مبلی با پایه چوبی؛ جزئیات نیازمند تأیید', 'colors' => 'شیری، خاکستری، بژ', 'delivery' => 'زمان ارسال هنگام سفارش اعلام می‌شود', 'description' => 'طراحی ساده و کم‌جزئیات برای خانه‌هایی با چیدمان امروزی. پیش از خرید، ارتفاع نشیمن و ابعاد کلی را با نیاز فضای خود مقایسه کنید.'],
            ['category' => 'modern', 'name' => 'مبل مدرن چهارنفره دنا', 'slug' => 'مبل-مدرن-چهار-نفره-دنا', 'sku' => 'SG-MOD-302', 'price' => 132000000, 'stock' => 0, 'featured' => false, 'new' => false, 'installment' => [50, 3, 1], 'dimensions' => 'طول تقریبی ۲۸۰، عمق ۹۲ سانتی‌متر', 'material' => 'پارچه مبلی؛ مشخصات ساخت باید تأیید شود', 'colors' => 'خاکستری، کرم، آبی نفتی', 'delivery' => 'در حال حاضر ناموجود؛ زمان تأمین نیازمند استعلام', 'description' => 'مبل چهار نفره با ظاهر یکپارچه برای نشیمن‌های بزرگ. امکان سفارش مجدد و زمان تأمین را پیش از ثبت درخواست بررسی کنید.'],
            ['category' => 'dining', 'name' => 'میز ناهارخوری شش‌نفره سپهر', 'slug' => 'میز-ناهارخوری-شش-نفره-سپهر', 'sku' => 'SG-DIN-401', 'price' => 68000000, 'stock' => 3, 'featured' => true, 'new' => false, 'installment' => [40, 3, 1], 'dimensions' => 'طول ۱۸۰، عرض ۹۰، ارتفاع ۷۵ سانتی‌متر', 'material' => 'صفحه چوبی یا روکش‌دار؛ جنس قطعی نیازمند تأیید', 'colors' => 'گردویی، طبیعی، مشکی', 'delivery' => 'زمان ارسال پس از تأیید موجودی', 'description' => 'میز شش‌نفره برای وعده‌های روزمره و دورهمی. فضای لازم برای عقب کشیدن صندلی‌ها و مسیر رفت‌وآمد را هنگام اندازه‌گیری در نظر بگیرید.'],
            ['category' => 'dining', 'name' => 'میز ناهارخوری چهارنفره باران', 'slug' => 'میز-ناهارخوری-چهار-نفره-باران', 'sku' => 'SG-DIN-402', 'price' => 42000000, 'stock' => 6, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'طول ۱۲۰، عرض ۸۰، ارتفاع ۷۵ سانتی‌متر', 'material' => 'چوب یا فرآورده چوبی؛ نیازمند تأیید', 'colors' => 'طبیعی، سفید، گردویی', 'delivery' => 'زمان تحویل پس از استعلام', 'description' => 'ابعاد جمع‌وجور برای آشپزخانه یا فضای غذاخوری کوچک. ظرفیت واقعی به ابعاد صندلی‌ها و فاصله موردنیاز اطراف میز بستگی دارد.'],
            ['category' => 'bedroom', 'name' => 'تخت دونفره چوبی آریا', 'slug' => 'تخت-دو-نفره-چوبی-آریا', 'sku' => 'SG-BED-501', 'price' => 89000000, 'stock' => 2, 'featured' => false, 'new' => false, 'installment' => [50, 4, 1], 'dimensions' => 'مناسب تشک ۱۶۰×۲۰۰ سانتی‌متر', 'material' => 'چوب یا روکش چوب؛ نوع و ضخامت باید تأیید شود', 'colors' => 'گردویی، بلوطی، طبیعی', 'delivery' => 'زمان ساخت و ارسال پس از استعلام', 'description' => 'تخت دونفره با ظاهر گرم چوبی. ابعاد تشک، فضای بازشو و مسیر انتقال قطعات به اتاق را پیش از سفارش اندازه‌گیری کنید.'],
            ['category' => 'bedroom', 'name' => 'تخت دونفره پارچه‌ای مهتاب', 'slug' => 'تخت-دو-نفره-پارچه-ای-مهتاب', 'sku' => 'SG-BED-502', 'price' => 74000000, 'stock' => 0, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'مناسب تشک ۱۶۰×۲۰۰ سانتی‌متر', 'material' => 'پارچه مبلی و سازه داخلی؛ نیازمند تأیید', 'colors' => 'کرم، طوسی روشن، بژ', 'delivery' => 'در حال حاضر ناموجود؛ زمان تأمین نیازمند استعلام', 'description' => 'تخت با تاج پارچه‌ای برای ایجاد نقطه کانونی در اتاق خواب. رنگ پارچه و ارتفاع تاج را با ابعاد اتاق و نور محیط هماهنگ کنید.'],
            ['category' => 'table', 'name' => 'میز جلو مبلی گرد هیراد', 'slug' => 'میز-جلو-مبلی-گرد-هیراد', 'sku' => 'SG-TBL-601', 'price' => 18500000, 'stock' => 8, 'featured' => false, 'new' => false, 'installment' => null, 'dimensions' => 'قطر ۸۰، ارتفاع ۴۰ سانتی‌متر', 'material' => 'صفحه چوبی یا سنگ مصنوعی؛ نیازمند تأیید', 'colors' => 'گردویی، مشکی، کرم', 'delivery' => 'زمان ارسال پس از تأیید موجودی', 'description' => 'میز گرد برای مرکز نشیمن و دسترسی آسان از اطراف. فاصله مناسب تا مبل و ارتفاع متناسب با نشیمن را در انتخاب در نظر بگیرید.'],
            ['category' => 'table', 'name' => 'میز عسلی سه‌تکه آوین', 'slug' => 'میز-عسلی-سه-تکه-آوین', 'sku' => 'SG-TBL-602', 'price' => 12500000, 'stock' => 9, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'سه اندازه مکمل؛ ابعاد نهایی نیازمند تأیید', 'material' => 'چوب و فلز؛ جزئیات ساخت نیازمند تأیید', 'colors' => 'مشکی، گردویی، طلایی مات', 'delivery' => 'زمان ارسال پس از تأیید موجودی', 'description' => 'سه میز مکمل برای چیدمان منعطف کنار مبل. امکان قرارگیری تو در تو و ابعاد هر قطعه را پیش از خرید بررسی کنید.'],
            ['category' => 'decor', 'name' => 'آینه دکوراتیو قدی سپیدار', 'slug' => 'آینه-دکوراتیو-قدی-سپیدار', 'sku' => 'SG-DEC-701', 'price' => 24500000, 'stock' => 4, 'featured' => false, 'new' => false, 'installment' => null, 'dimensions' => 'ارتفاع ۱۶۰، عرض ۶۰ سانتی‌متر', 'material' => 'آینه و قاب؛ جنس قاب نیازمند تأیید', 'colors' => 'مشکی، طلایی، چوب طبیعی', 'delivery' => 'بسته‌بندی و ارسال نیازمند هماهنگی', 'description' => 'آینه قدی برای ورودی یا اتاق خواب. نوع نصب، وزن و فضای امن اطراف آینه را پیش از انتخاب بررسی کنید.'],
            ['category' => 'decor', 'name' => 'آینه دیواری بیضی ماهور', 'slug' => 'آینه-دیواری-بیضی-ماهور', 'sku' => 'SG-DEC-702', 'price' => 14500000, 'stock' => 5, 'featured' => false, 'new' => false, 'installment' => null, 'dimensions' => 'عرض ۶۰، ارتفاع ۹۰ سانتی‌متر', 'material' => 'آینه و قاب؛ جنس قاب نیازمند تأیید', 'colors' => 'مشکی، برنزی، طبیعی', 'delivery' => 'زمان ارسال پس از تأیید موجودی', 'description' => 'فرم بیضی برای ایجاد تنوع بصری در دیوارهای ساده. محل نصب و نوع اتصالات متناسب با جنس دیوار را بررسی کنید.'],
            ['category' => 'lighting', 'name' => 'چراغ ایستاده آرکا', 'slug' => 'چراغ-ایستاده-آرکا', 'sku' => 'SG-LGT-801', 'price' => 19500000, 'stock' => 3, 'featured' => false, 'new' => true, 'installment' => null, 'dimensions' => 'ارتفاع تقریبی ۱۵۰ سانتی‌متر', 'material' => 'فلز و قطعات روشنایی؛ مشخصات فنی نیازمند تأیید', 'colors' => 'مشکی، برنجی، سفید', 'delivery' => 'زمان تحویل پس از استعلام', 'description' => 'چراغ ایستاده برای نور مکمل کنار مبل یا صندلی مطالعه. نوع سرپیچ، توان مجاز و استاندارد برق را پیش از استفاده بررسی کنید.'],
            ['category' => 'lighting', 'name' => 'چراغ رومیزی نیایش', 'slug' => 'چراغ-رومیزی-نیایش', 'sku' => 'SG-LGT-802', 'price' => 8500000, 'stock' => 6, 'featured' => false, 'new' => false, 'installment' => null, 'dimensions' => 'ارتفاع تقریبی ۴۵ سانتی‌متر', 'material' => 'فلز، شیشه یا سرامیک؛ نیازمند تأیید', 'colors' => 'سفید، مشکی، کرم', 'delivery' => 'زمان تحویل پس از تأیید موجودی', 'description' => 'چراغ رومیزی برای نورپردازی موضعی روی میز کنار تخت یا کنسول. شدت و رنگ نور را متناسب با کاربرد فضا انتخاب کنید.'],
        ];

        foreach ($products as $product) {
            $installment = $product['installment'];
            $specifications = [
                'ابعاد' => $product['dimensions'],
                'جنس' => $product['material'],
                'رنگ‌بندی' => $product['colors'],
                'زمان تحویل' => $product['delivery'],
                'یادداشت' => 'اطلاعات این رکورد برای آماده‌سازی کاتالوگ اولیه است و باید پیش از انتشار با مشخصات واقعی محصول تطبیق داده شود.',
            ];
            unset(
                $product['category'],
                $product['featured'],
                $product['new'],
                $product['installment'],
                $product['dimensions'],
                $product['material'],
                $product['colors'],
                $product['delivery']
            );

            $categoryKey = $product['sku'];
            $categoryKey = match (substr($categoryKey, 3, 3)) {
                'SOF' => 'sofa',
                'CHS' => 'chester',
                'MOD' => 'modern',
                'DIN' => 'dining',
                'BED' => 'bedroom',
                'TBL' => 'table',
                'DEC' => 'decor',
                'LGT' => 'lighting',
                default => throw new \LogicException('Unknown product category.'),
            };

            $slug = $product['slug'];
            $defaults = [
                ...$product,
                'category_id' => $categories[$categoryKey]->id,
                'short_description' => $product['description'],
                'description' => $product['description'],
                'status' => 'draft',
                'is_featured' => str_starts_with($product['sku'], 'SG-SOF-101') || in_array($product['sku'], ['SG-SOF-102', 'SG-CHS-201', 'SG-DIN-401'], true),
                'is_new' => in_array($product['sku'], ['SG-SOF-101', 'SG-SOF-103', 'SG-CHS-202', 'SG-MOD-301', 'SG-DIN-402'], true),
                'meta_title' => $product['name'] . ' | سیلا گالری',
                'meta_description' => 'مشاهده مشخصات و دریافت استعلام قیمت ' . $product['name'] . ' از سیلا گالری.',
                'specifications' => $specifications,
                'key_features' => ['امکان بررسی تناسب با فضای خانه', 'دریافت جزئیات نهایی پیش از ثبت سفارش'],
                'installment_enabled' => $installment !== null,
                'installment_cash_percent' => $installment[0] ?? null,
                'installment_remainder_method' => $installment !== null ? 'cheque' : null,
                'installment_cheque_count' => $installment[1] ?? null,
                'installment_interval_months' => $installment[2] ?? null,
            ];

            Product::query()->firstOrCreate(['slug' => $slug], $defaults);
        }
    }
}
