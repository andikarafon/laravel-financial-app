<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ExpenseChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Pengeluaran per Tanggal';
    protected static ?int $sort = 2;

    protected $listeners = ['refresh-widgets' => '$refresh'];

    protected function getData(): array
    {
        // Get filter values from dashboard filters
        $year = $this->filters['year'] ?? Carbon::now()->year;
        $month = $this->filters['month'] ?? Carbon::now()->month;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $expenses = Transaction::expense()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as transaction_date, SUM(amount) as total_amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->pluck('total_amount', 'transaction_date')
            ->toArray();

        $labels = array_map(function($date) {
            return Carbon::parse($date)->format('d/m');
        }, array_keys($expenses));

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran (Rp)',
                    'data' => array_values($expenses),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => '#ef4444',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

}