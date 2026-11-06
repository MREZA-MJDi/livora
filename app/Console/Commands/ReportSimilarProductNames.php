<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ReportSimilarProductNames extends Command
{
    protected $signature = 'products:report-similar-names {--min-similarity=0.88 : Minimum similarity from 0 to 1} {--limit=5000 : Maximum products to compare}';

    protected $description = 'Report products with similar names for manual review; this command never changes or deletes products.';

    public function handle(): int
    {
        $minimum = (float) $this->option('min-similarity');
        $limit = max(2, (int) $this->option('limit'));
        if ($minimum < 0.5 || $minimum > 1) {
            $this->error('Minimum similarity must be between 0.5 and 1.');
            return self::INVALID;
        }

        $products = Product::query()->orderBy('id')->limit($limit)->get(['id', 'name', 'slug']);
        $normalized = $products->mapWithKeys(fn (Product $product) => [
            $product->id => $this->normalize((string) $product->name),
        ]);
        $matches = [];

        for ($i = 0; $i < $products->count(); $i++) {
            $left = $products[$i];
            $leftName = $normalized[$left->id];
            if ($leftName['compact'] === '') continue;

            for ($j = $i + 1; $j < $products->count(); $j++) {
                $right = $products[$j];
                $rightName = $normalized[$right->id];
                if ($rightName['compact'] === '') continue;

                similar_text($leftName['compact'], $rightName['compact'], $directPercentage);
                similar_text($leftName['tokens'], $rightName['tokens'], $tokenPercentage);
                $similarity = max($directPercentage, $tokenPercentage) / 100;
                if ($similarity >= $minimum) {
                    $matches[] = [
                        $left->id, $left->name, $left->slug,
                        $right->id, $right->name, $right->slug,
                        number_format($similarity * 100, 1).'%',
                    ];
                }
            }
        }

        if ($matches === []) {
            $this->info('No similar product names met the selected threshold. No data was changed.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID A', 'نام A', 'Slug A', 'ID B', 'نام B', 'Slug B', 'شباهت'],
            $matches
        );
        $this->warn('این گزارش فقط برای بررسی دستی است؛ هیچ محصولی حذف یا ویرایش نشده است.');

        return self::SUCCESS;
    }

    private function normalize(string $value): array
    {
        $value = str_replace(['ي', 'ى', 'ك', '‌', 'ـ'], ['ی', 'ی', 'ک', ' ', ''], $value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[\p{P}\p{S}]+/u', ' ', $value) ?? '';
        $tokens = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sortedTokens = $tokens;
        sort($sortedTokens, SORT_STRING);

        return [
            'compact' => implode('', $tokens),
            'tokens' => implode(' ', $sortedTokens),
        ];
    }
}
