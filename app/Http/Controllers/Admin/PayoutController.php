<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutStatus;
use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $payouts = Payout::query()
            ->with(['store', 'order'])
            ->when(in_array($status, array_column(PayoutStatus::cases(), 'value'), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'pending' => (int) Payout::where('status', PayoutStatus::Pending->value)->sum('amount'),
            'paid' => (int) Payout::where('status', PayoutStatus::Paid->value)->sum('amount'),
        ];

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'status' => $status,
            'statuses' => PayoutStatus::cases(),
            'totals' => $totals,
        ]);
    }

    public function markPaid(Payout $payout): RedirectResponse
    {
        if ($payout->status !== PayoutStatus::Paid) {
            $payout->update([
                'status' => PayoutStatus::Paid,
                'paid_at' => now(),
            ]);
        }

        return back()->with('success', 'Pencairan ditandai sudah dibayar.');
    }
}
