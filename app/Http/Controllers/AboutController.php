<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $installmentSettings = Product::query()
            ->active()
            ->where('installment_enabled', true)
            ->get([
                'installment_cash_percent',
                'installment_cheque_count',
            ]);

        $cashPercents = $installmentSettings
            ->pluck('installment_cash_percent')
            ->filter(fn ($value) => (int) $value > 0)
            ->map(fn ($value) => (int) $value);

        $chequeCounts = $installmentSettings
            ->pluck('installment_cheque_count')
            ->filter(fn ($value) => (int) $value > 0)
            ->map(fn ($value) => (int) $value);

        return view('about.index', [
            'installmentProductCount' => $installmentSettings->count(),
            'minimumCashPercent' => $cashPercents->isNotEmpty()
                ? $cashPercents->min()
                : null,
            'maximumChequeCount' => $chequeCounts->isNotEmpty()
                ? $chequeCounts->max()
                : null,
        ]);
    }
}
