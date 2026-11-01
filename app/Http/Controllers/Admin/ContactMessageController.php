<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(15);

        return view('admin.contact-messages.index', [
            'messages' => $messages,
        ]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', [
            'contactMessage' => $contactMessage,
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'read']);

        return back()->with('success', 'پیام به عنوان خوانده‌شده علامت خورد.');
    }

    public function reply(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        if (! filled($contactMessage->email)) {
            return back()->with('error', 'برای این پیام ایمیلی ثبت نشده است؛ امکان ارسال پاسخ ایمیلی وجود ندارد.');
        }

        $reply = trim($validated['admin_reply']);
        $body = "سلام {$contactMessage->name} عزیز،\\n\\n"
            . "در پاسخ به پیام شما در SillaGallery:\\n\\n"
            . $reply
            . "\\n\\nبا احترام،\\nتیم پشتیبانی SillaGallery";

        try {
            Mail::raw($body, function ($message) use ($contactMessage): void {
                $message
                    ->to($contactMessage->email, $contactMessage->name)
                    ->subject('پاسخ SillaGallery به پیام شما');
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'ارسال ایمیل ناموفق بود. تنظیمات SMTP و گزارش خطا را بررسی کنید.');
        }

        $contactMessage->update([
            'admin_reply' => $reply,
            'replied_at' => now(),
            'status' => 'replied',
        ]);

        return redirect()
            ->route('admin.contact-messages.show', $contactMessage)
            ->with('success', 'پاسخ ایمیلی ارسال و در سابقه پیام ثبت شد.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'پیام با موفقیت حذف شد.');
    }
}
