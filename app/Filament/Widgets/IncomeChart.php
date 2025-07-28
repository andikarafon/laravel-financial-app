<?php

namespace App\Filament\Widgets;


use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;

class IncomeChart extends ChartWidget
{

    use InteractsWithPageFilters;

    protected static ?string $heading = 'Pendapatan Per Tanggal';
    protected static ?int $sort = 3;

    protected $listeners = ['refresh-widgets' => '$refresh'];

    protected function getData(): array
    {

        // Get filter values from dashboard filters
        $year = $this->filters['year'] ?? Carbon::now()->year;
        $month = $this->filters['month'] ?? Carbon::now()->month;
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $income = Transaction::income()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as transaction_date, SUM(amount) as total_amount')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->pluck('total_amount', 'transaction_date')
            ->toArray();
        
        $labels = array_map(function($date) {
            return Carbon::parse($date)->format('d/m');
        }, array_keys($income));
        
        return [
            //
            'datasets' => [
                    [
                        'label' => 'Pendapatan (Rp)',
                        'data' => array_values($income),
                        'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                        'borderColor' => '#22c55e',
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
