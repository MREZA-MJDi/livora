<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    public function index(): View
    {
        return view('admin.home-sections.index', [
            'sections' => HomeSection::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.home-sections.form', [
            'section' => new HomeSection(['enabled' => true, 'type' => 'products', 'payload' => ['product_source' => 'latest', 'limit' => 8]]),
            'categories' => Category::query()->active()->get(['id', 'name']),
            'products' => Product::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['key'] = Str::slug($data['key']);
        $data['sort_order'] = (HomeSection::max('sort_order') ?? 0) + 10;
        $data['enabled'] = $request->boolean('enabled');
        $data['payload'] = $this->normalizePayload($data['payload'] ?? []);
        $this->assertProductsAreUnique($data['payload']['product_ids'] ?? []);
        $data['image_path'] = $this->storeImage($request, null);
        HomeSection::create($data);

        return redirect()->route('admin.home-sections.index')->with('success', 'سکشن صفحه اصلی ایجاد شد.');
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home-sections.form', [
            'section' => $homeSection,
            'categories' => Category::query()->active()->get(['id', 'name']),
            'products' => Product::query()->active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, HomeSection $homeSection): RedirectResponse
    {
        $data = $this->validated($request, $homeSection);
        $data['key'] = Str::slug($data['key']);
        $data['enabled'] = $request->boolean('enabled');
        $payload = $data['payload'] ?? [];
        if (! array_key_exists('items', $payload) && isset($homeSection->payload['items'])) {
            $payload['items'] = $homeSection->payload['items'];
        }
        $data['payload'] = $this->normalizePayload($payload);
        $this->assertProductsAreUnique($data['payload']['product_ids'] ?? [], $homeSection->id);
        $data['image_path'] = $this->storeImage($request, $homeSection->image_path);
        $homeSection->update($data);

        return redirect()->route('admin.home-sections.index')->with('success', 'سکشن صفحه اصلی بروزرسانی شد.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'exists:home_sections,id'],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['order'] as $position => $id) {
                HomeSection::query()->whereKey($id)->update(['sort_order' => ($position + 1) * 10]);
            }
        });

        return back()->with('success', 'ترتیب سکشن‌ها ذخیره شد.');
    }

    public function destroy(HomeSection $homeSection): RedirectResponse
    {
        $homeSection->delete();

        return redirect()->route('admin.home-sections.index')->with('success', 'سکشن حذف شد.');
    }

    private function validated(Request $request, ?HomeSection $section = null): array
    {
        $request->merge(['key' => Str::slug((string) $request->input('key'))]);

        return $request->validate([
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('home_sections', 'key')->ignore($section?->id)],
            'type' => ['required', Rule::in(['hero', 'products', 'categories', 'editorial', 'trust', 'cta'])],
            'enabled' => ['nullable', 'boolean'],
            'eyebrow' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:3000'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'payload' => ['nullable', 'array'],
            'payload.product_source' => ['nullable', Rule::in(['latest', 'new', 'installments', 'selected', 'category'])],
            'payload.product_ids' => ['nullable', 'array', 'max:30'],
            'payload.product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'payload.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'payload.limit' => ['nullable', 'integer', 'between:1,24'],
            'payload.items' => ['nullable', 'array', 'max:8'],
            'payload.items.*.title' => ['required_with:payload.items', 'string', 'max:120'],
            'payload.items.*.body' => ['required_with:payload.items', 'string', 'max:500'],
        ]);
    }

    private function normalizePayload(array $payload): array
    {
        if (isset($payload['items'])) {
            $payload['items'] = collect($payload['items'])
                ->filter(fn ($item) => is_array($item) && filled($item['title'] ?? null) && filled($item['body'] ?? null))
                ->map(fn ($item) => ['title' => trim($item['title']), 'body' => trim($item['body'])])
                ->take(8)
                ->values()
                ->all();
        }

        $payload['limit'] = max(1, min(24, (int) ($payload['limit'] ?? 8)));
        $payload['product_source'] = $payload['product_source'] ?? 'latest';
        $payload['product_ids'] = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));

        return $payload;
    }

    private function assertProductsAreUnique(array $productIds, ?int $ignoreId = null): void
    {
        if ($productIds === []) {
            return;
        }

        $used = HomeSection::query()->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->get(['id', 'payload'])
            ->flatMap(fn (HomeSection $item) => $item->payload['product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)->unique();

        $duplicates = $used->intersect(array_map('intval', $productIds));
        if ($duplicates->isNotEmpty()) {
                throw ValidationException::withMessages([
                'payload.product_ids' => 'هر محصول فقط می‌تواند در یک سکشن دستی صفحه اصلی انتخاب شود.',
            ]);
        }
    }

    private function storeImage(Request $request, ?string $existing): ?string
    {
        if (! $request->hasFile('image')) {
            return $existing;
        }

        $media = MediaLibrary::store($request->file('image'), (int) $request->user()->id, 'home-sections');
        return $media->path;
    }
}
