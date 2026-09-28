<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                'in:unread,read',
            ],
        ]);

        $query = ContactMessage::query()
            ->latest();

        if (! empty($validated['status'])) {
            $query->where(
                'status',
                $validated['status']
            );
        }

        $messages = $query
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update([
                'status' => 'read',
            ]);
        }

        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage,
        ]);
    }

    public function markAsRead(
        ContactMessage $contactMessage
    ): RedirectResponse {
        $contactMessage->update([
            'status' => 'read',
        ]);

        return back()->with(
            'success',
            'پیام به عنوان خوانده‌شده علامت خورد.'
        );
    }

    public function destroy(
        ContactMessage $contactMessage
    ): RedirectResponse {
        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with(
                'success',
                'پیام با موفقیت حذف شد.'
            );
    }
}
