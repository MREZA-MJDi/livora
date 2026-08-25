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
        $contactSetting = ContactSetting::query()
            ->where('is_active', true)
            ->first();

        $faqs = [
            // FAQ های static
        ];

        return view('contact.index', [
            'contactSetting' => $contactSetting,
            'faqs' => $faqs,
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

        return redirect()
            ->route('contact')
            ->with('success', 'پیام شما با موفقیت ارسال شد.');
    }
}
