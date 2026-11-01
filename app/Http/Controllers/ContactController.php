<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $storedSettings = ContactSetting::query()->first();
        $contactSetting = $storedSettings?->is_active ? $storedSettings : null;

        $faqs = collect($storedSettings?->faqs ?? [])
            ->filter(fn ($faq): bool => is_array($faq)
                && filled($faq['question'] ?? null)
                && filled($faq['answer'] ?? null))
            ->values();

        return view('contact.index', [
            'contactSetting' => $contactSetting,
            'faqs' => $faqs,
            'pageContent' => $storedSettings?->page_content ?? [],
        ]);
    }

    public function store(
        StoreContactMessageRequest $request
    ): RedirectResponse {
        ContactMessage::query()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'status' => 'unread',
        ]);

        $contactSetting = ContactSetting::query()->first();
        $successMessage = data_get(
            $contactSetting?->page_content ?? [],
            'success_message',
            'پیام شما با موفقیت ارسال شد.'
        );

        return redirect()
            ->route('contact')
            ->with('success', $successMessage);
    }
}
