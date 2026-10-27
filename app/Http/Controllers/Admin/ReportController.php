<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reportService): View
    {
        [$from, $to] = $this->period($request);

        $summary = $reportService->summary($from, $to);

        return view('admin.reports.index', [
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'chart' => $reportService->monthlyRevenue(6),
        ]);
    }

    public function exportPdf(Request $request, ReportService $reportService)
    {
        [$from, $to] = $this->period($request);
        $summary = $reportService->summary($from, $to);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi-'.$from->format('Ymd').'-'.$to->format('Ymd').'.pdf');
    }

    public function exportExcel(Request $request, ReportService $reportService): BinaryFileResponse
    {
        [$from, $to] = $this->period($request);
        $summary = $reportService->summary($from, $to);

        return Excel::download(
            new TransactionsExport($summary['orders'], $from, $to),
            'laporan-transaksi-'.$from->format('Ymd').'-'.$to->format('Ymd').'.xlsx'
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        // Validasi input terlebih dahulu agar Carbon::parse() tidak diumpan string sembarang.
        $request->validate([
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to'   => ['nullable', 'date', 'before_or_equal:today'],
        ], [
            'from.date'             => 'Format tanggal awal tidak valid.',
            'from.before_or_equal'  => 'Tanggal awal tidak boleh melebihi hari ini.',
            'to.date'               => 'Format tanggal akhir tidak valid.',
            'to.before_or_equal'    => 'Tanggal akhir tidak boleh melebihi hari ini.',
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
