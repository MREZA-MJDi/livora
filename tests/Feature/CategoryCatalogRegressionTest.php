<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryCatalogRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_keeps_a_fixed_twelve_item_page_and_stable_order(): void
    {
        $this->createCategories(21);

        $firstPage = $this->get(route('categories.index'))->assertOk();
        $firstPage->assertSee('نمایش 1 تا 12', false);
        $firstPage->assertSee('از 21 مجموعه', false);

        $secondPage = $this->get(route('categories.index', ['page' => 2]))->assertOk();
        $secondPage->assertSee('نمایش 13 تا 21', false);
        $secondPage->assertSee('از 21 مجموعه', false);

        $dom = new \DOMDocument();
        $previousLibxmlState = libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($secondPage->getContent(), 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlState);

        $xpath = new \DOMXPath($dom);
        $grid = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " sg-category-index-grid ")]')->item(0);
        $this->assertNotNull($grid);

        $gridHtml = $dom->saveHTML($grid);
        $this->assertStringContainsString(route('categories.show', 'catalog-regression-13'), $gridHtml);
        $this->assertStringNotContainsString(route('categories.show', 'catalog-regression-01'), $gridHtml);
    }

    public function test_category_index_query_count_does_not_grow_with_category_count(): void
    {
        $this->createCategories(24);
        $selectSql = [];

        DB::listen(function (QueryExecuted $query) use (&$selectSql): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $selectSql[] = $query->sql;
            }
        });

        $this->get(route('categories.index'))->assertOk();

        // Eager-loaded category media/product relations must stay bounded;
        // rendering one more category must not issue per-card SQL queries.
        $this->assertLessThanOrEqual(14, count($selectSql), implode(PHP_EOL, $selectSql));
    }

    private function createCategories(int $count): void
    {
        for ($index = 1; $index <= $count; $index++) {
            Category::query()->create([
                'name' => sprintf('Catalog regression %02d', $index),
                'slug' => sprintf('catalog-regression-%02d', $index),
                'description' => 'Regression category description.',
                'is_active' => true,
                'sort_order' => 10,
                'seo_index' => true,
                'seo_follow' => true,
            ]);
        }
    }
}
