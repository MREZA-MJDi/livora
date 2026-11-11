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
            'sections' => HomeSection::query()->orderBy('sort_order')->orderBy('id')->limit(40)->get(),
        ]);
    }

    public function create(): View
    {
        $section = new HomeSection([
            'enabled' => true,
            'type' => 'products',
            'payload' => ['product_source' => 'latest', 'limit' => 8],
        ]);

        return view('admin.home-sections.form', [
            'section' => $section,
            ...$this->selectedLookupOptions($section),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (HomeSection::query()->count() >= 40) {
            return back()->withErrors(['section' => 'حداکثر ۴۰ سکشن برای صفحه اصلی قابل تعریف است.'])->withInput();
        }

        $data = $this->validated($request);
        $data['key'] = Str::slug($data['key']);
        $data['sort_order'] = (HomeSection::max('sort_order') ?? 0) + 10;
        $data['enabled'] = $request->boolean('enabled');
        $data['payload'] = $this->normalizePayload($data['payload'] ?? []);
        $this->assertProductsAreUnique($data['payload']['product_ids'] ?? []);
        $data['image_path'] = $this->storeImage($request, null);
        $data['payload'] = $this->storeHeroSlides($request, $data['payload'], []);
        if (($data['type'] ?? null) === 'hero' && ! empty($data['payload']['slides'][0]['image_path'])) {
            $data['image_path'] = $data['payload']['slides'][0]['image_path'];
        }
        HomeSection::create($data);

        return redirect()->route('admin.home-sections.index')->with('success', 'سکشن صفحه اصلی ایجاد شد.');
    }

    public function edit(HomeSection $homeSection): View
    {
        return view('admin.home-sections.form', [
            'section' => $homeSection,
            ...$this->selectedLookupOptions($homeSection),
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
        $data['payload'] = $this->storeHeroSlides($request, $data['payload'], $homeSection->payload['slides'] ?? []);
        if (($data['type'] ?? null) === 'hero' && ! empty($data['payload']['slides'][0]['image_path'])) {
            $data['image_path'] = $data['payload']['slides'][0]['image_path'];
        }
        $homeSection->update($data);

        return redirect()->route('admin.home-sections.index')->with('success', 'سکشن صفحه اصلی بروزرسانی شد.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1', 'max:40'],
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
        $rawKey = trim((string) $request->input('key'));
        $fallbackKey = $section?->key ?: (($request->input('type') ?: 'section') . '-' . Str::lower(Str::random(6)));
        $request->merge([
            'key' => Str::slug($rawKey !== '' ? $rawKey : $fallbackKey),
        ]);

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
            'slide_images' => ['nullable', 'array', 'max:4'],
            'slide_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'payload' => ['nullable', 'array'],
            'payload.product_source' => ['nullable', Rule::in(['latest', 'new', 'featured', 'popular', 'installments', 'selected', 'category'])],
            'payload.product_ids' => ['nullable', 'array', 'max:30'],
            'payload.product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'payload.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'payload.limit' => ['nullable', 'integer', 'between:1,24'],
            'payload.items' => ['nullable', 'array', 'max:8'],
            'payload.items.*.title' => ['required_with:payload.items', 'string', 'max:120'],
            'payload.items.*.body' => ['required_with:payload.items', 'string', 'max:500'],
            'payload.slides' => ['nullable', 'array', 'max:4'],
            'payload.slides.*.eyebrow' => ['nullable', 'string', 'max:160'],
            'payload.slides.*.title' => ['nullable', 'string', 'max:255'],
            'payload.slides.*.subtitle' => ['nullable', 'string', 'max:1500'],
            'payload.slides.*.price_label' => ['nullable', 'string', 'max:120'],
            'payload.slides.*.cta_label' => ['nullable', 'string', 'max:120'],
            'payload.slides.*.cta_url' => ['nullable', 'string', 'max:2048'],
            'payload.slides.*.image_path' => ['nullable', 'string', 'max:2048'],
            'payload.slides.*.object_position' => ['nullable', 'string', 'regex:/^(?:[0-9]{1,3})%\\s+(?:[0-9]{1,3})%$/'],
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

        if (isset($payload['slides'])) {
            $payload['slides'] = collect($payload['slides'])
                ->filter(fn ($slide) => is_array($slide))
                ->map(fn ($slide) => [
                    'eyebrow' => trim((string) ($slide['eyebrow'] ?? '')),
                    'title' => trim((string) ($slide['title'] ?? '')),
                    'subtitle' => trim((string) ($slide['subtitle'] ?? '')),
                    'price_label' => trim((string) ($slide['price_label'] ?? '')),
                    'cta_label' => trim((string) ($slide['cta_label'] ?? '')),
                    'cta_url' => trim((string) ($slide['cta_url'] ?? '')),
                    'image_path' => $slide['image_path'] ?? null,
                    'object_position' => $slide['object_position'] ?? '50% 50%',
                ])
                ->filter(fn ($slide) => filled($slide['title']) || filled($slide['image_path']))
                ->take(4)
                ->values()
                ->all();
        }

        $payload['limit'] = max(1, min(24, (int) ($payload['limit'] ?? 8)));
        $payload['product_source'] = $payload['product_source'] ?? 'latest';
        $payload['product_ids'] = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));

        return $payload;
    }

    private function storeHeroSlides(Request $request, array $payload, array $existingSlides): array
    {
        if (! isset($payload['slides'])) {
            return $payload;
        }

        $slides = $payload['slides'];
        foreach ($slides as $index => &$slide) {
            $upload = $request->file("slide_images.{$index}");
            if ($upload) {
                $media = MediaLibrary::store($upload, (int) $request->user()->id, 'home-sections');
                $slide['image_path'] = $media->path;
            } elseif (blank($slide['image_path'] ?? null)) {
                $slide['image_path'] = $existingSlides[$index]['image_path'] ?? null;
            }
        }
        unset($slide);

        $payload['slides'] = array_values(array_filter($slides, fn ($slide) => filled($slide['image_path']) || filled($slide['title'])));
        return $payload;
    }

    private function assertProductsAreUnique(array $productIds, ?int $ignoreId = null): void
    {
        if ($productIds === []) {
            return;
        }

        $hasDuplicate = HomeSection::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($productIds): void {
                foreach (array_unique(array_map('intval', $productIds)) as $productId) {
                    $query->orWhereJsonContains('payload->product_ids', $productId);
                }
            })
            ->exists();

        if ($hasDuplicate) {
            throw ValidationException::withMessages([
                'payload.product_ids' => 'هر محصول فقط می‌تواند در یک سکشن دستی صفحه اصلی انتخاب شود.',
            ]);
        }
    }

    /**
     * Load only the values already selected in this form. Search results are fetched
     * on demand through HomeSectionLookupController.
     *
     * @return array{selectedCategory: ?Category, selectedProducts: \\Illuminate\\Support\\Collection}
     */
    private function selectedLookupOptions(HomeSection $section): array
    {
        $oldPayload = session()->get('_old_input.payload', []);
        $categoryId = data_get($oldPayload, 'category_id', data_get($section->payload, 'category_id'));
        $productIds = data_get($oldPayload, 'product_ids', data_get($section->payload, 'product_ids', []));
        $productIds = collect(is_array($productIds) ? $productIds : [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(30)
            ->values();

        $selectedCategory = filled($categoryId)
            ? Category::query()->whereKey((int) $categoryId)->first(['id', 'name'])
            : null;

        $productsById = $productIds->isEmpty()
            ? collect()
            : Product::query()->whereIn('id', $productIds)->get(['id', 'name', 'sku'])->keyBy('id');

        $selectedProducts = $productIds
            ->map(fn ($id) => $productsById->get($id))
            ->filter()
            ->values();

        return compact('selectedCategory', 'selectedProducts');
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
