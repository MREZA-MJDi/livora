<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class CatalogueLookupController extends Controller
{
    private const RESULT_LIMIT = 20;

    public function products(Request $request): JsonResponse
    {
        $term = $this->searchTerm($request);
        $pattern = addcslashes($term, '%_\\\\') . '%';

        $matches = Product::query()
            ->when($request->boolean('active_only'), fn (Builder $query) => $query->active())
            ->when($request->integer('exclude_id') > 0, fn (Builder $query) => $query->where('id', '!=', $request->integer('exclude_id')))
            ->where(function (Builder $query) use ($pattern): void {
                $query->where('name', 'like', $pattern)
                    ->orWhere('sku', 'like', $pattern)
                    ->orWhere('slug', 'like', $pattern);
            })
            ->orderBy('name')
            ->orderBy('id')
            ->limit(self::RESULT_LIMIT + 1)
            ->get(['id', 'name', 'sku', 'price', 'status']);

        return $this->response($matches->map(fn (Product $product): array => [
            'id' => $product->id,
            'label' => $product->name,
            'meta' => trim(($product->sku ?: '') . ($product->status === 'active' ? '' : ' • غیرفعال')),
            'price' => (float) $product->price,
            'status' => $product->status,
        ])->values());
    }

    public function categories(Request $request): JsonResponse
    {
        $term = $this->searchTerm($request);
        $pattern = addcslashes($term, '%_\\\\') . '%';

        $matches = Category::query()
            ->when($request->boolean('active_only'), fn (Builder $query) => $query->where('is_active', true))
            ->when($request->boolean('parent_only'), fn (Builder $query) => $query->whereNull('parent_id'))
            ->when($request->integer('exclude_id') > 0, fn (Builder $query) => $query->whereKeyNot($request->integer('exclude_id')))
            ->where(function (Builder $query) use ($pattern): void {
                $query->where('name', 'like', $pattern)
                    ->orWhere('slug', 'like', $pattern);
            })
            ->orderBy('name')
            ->orderBy('id')
            ->limit(self::RESULT_LIMIT + 1)
            ->get(['id', 'name', 'is_active']);

        return $this->response($matches->map(fn (Category $category): array => [
            'id' => $category->id,
            'label' => $category->name,
            'meta' => $category->is_active ? null : 'غیرفعال',
        ])->values());
    }

    private function searchTerm(Request $request): string
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $term = trim($validated['q']);
        if (mb_strlen($term) < 2) {
            throw ValidationException::withMessages([
                'q' => 'برای جستجو حداقل دو حرف معتبر وارد کنید.',
            ]);
        }

        return $term;
    }

    private function response(Collection $matches): JsonResponse
    {
        $hasMore = $matches->count() > self::RESULT_LIMIT;

        return response()->json([
            'data' => $matches->take(self::RESULT_LIMIT)->values(),
            'has_more' => $hasMore,
            'limit' => self::RESULT_LIMIT,
        ])->header('Cache-Control', 'private, no-store');
    }
}
