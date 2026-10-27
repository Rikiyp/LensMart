<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __invoke(Request $request, ReportService $reportService): View
    {
        $store = $request->user()->store;

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $summary = $reportService->summary($from, $to, $store);

        $payouts = Payout::query()
            ->where('store_id', $store->id)
            ->with('order')
            ->latest()
            ->paginate(10);

        $chart = $reportService->monthlyRevenue(6, $store);

        return view('partner.finance.index', compact('store', 'summary', 'payouts', 'chart', 'from', 'to'));
    }
}
