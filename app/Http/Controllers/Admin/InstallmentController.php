<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderInstallment;
use App\Services\Admin\InstallmentReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class InstallmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $query = OrderInstallment::query()
            ->with([
                'order.user',
                'paidBy',
            ])
            ->orderByRaw(
                "CASE
                    WHEN status = 'pending' AND due_date < CURRENT_DATE THEN 0
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'paid' THEN 2
                    ELSE 3
                 END"
            )
            ->orderBy('due_date')
            ->orderBy('id');

        if ($status === 'overdue') {
            $query
                ->where('status', 'pending')
                ->whereDate('due_date', '<', today());
        }

        if ($status === 'pending') {
            $query
                ->where('status', 'pending')
                ->where(function ($query) {
                    $query
                        ->whereNull('due_date')
                        ->orWhereDate('due_date', '>=', today());
                });
        }

        if ($status === 'paid') {
            $query->where('status', 'paid');
        }

        $installments = $query
            ->paginate(25)
            ->withQueryString();

        return view('admin.installments.index', [
            'installments' => $installments,
            'status' => $status,
        ]);
    }

    public function markPaid(
        Request $request,
        OrderInstallment $installment,
        InstallmentReceiptService $receiptService
    ): RedirectResponse {
        $validated = $request->validate([
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {
            $receiptService->markAsPaid(
                $installment,
                (int) $request->user()->id,
                $validated['notes'] ?? null
            );

            return back()->with(
                'success',
                'وصول قسط با موفقیت در حساب مالی ثبت شد.'
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'ثبت وصول قسط انجام نشد. دوباره تلاش کنید.'
            );
        }
    }
}
