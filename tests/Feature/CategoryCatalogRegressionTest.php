<?php

namespace Tests\\Feature;

use App\\Models\\Category;
use Illuminate\\Database\\Events\\QueryExecuted;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

final class CategoryCatalogRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_keeps_a_fixed_twelve_item_page_and_stable_order(): void
    {
        $this->createCategories(21);

        $firstPage = $this->get(route('categories.index'))->assertOk();
        $firstPage->assertSee('نمایش ۱ تا ۱۲', false);
        $firstPage->assertSee('از ۲۱ مجموعه', false);

        $secondPage = $this->get(route('categories.index', ['page' => 2]))->assertOk();
        $secondPage->assertSee('نمایش ۱۳ تا ۲۱', false);
        $secondPage->assertSee('از ۲۱ مجموعه', false);

        $this->assertStringContainsString(
            route('categories.show', 'catalog-regression-13'),
            $secondPage->getContent()
        );
        $this->assertStringNotContainsString(
            route('categories.show', 'catalog-regression-01'),
            $secondPage->getContent()
        );
    }

    public function test_category_index_query_count_does_not_grow_with_category_count(): void
    {
        $this->createCategories(24);
        $selectQueries = 0;

        DB::listen(function (QueryExecuted $query) use (&$selectQueries): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $selectQueries++;
            }
        });

        $this->get(route('categories.index'))->assertOk();

        // Eager-loaded category media/product relations must stay bounded;
        // rendering one more category must not issue per-card SQL queries.
        $this->assertLessThanOrEqual(14, $selectQueries);
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
