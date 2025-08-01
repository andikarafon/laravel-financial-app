<?php

namespace App\Filament\Widgets;


use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected $listeners = ['refresh-widgets' => '$refresh'];


    protected function getStats(): array
    {
        
            //get filter values from dashboard filters
            $year = $this->filters['year'] ?? Carbon::now()->year;
            $month = $this->filters['month'] ?? Carbon::now()->month;

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            //total income for selected month/year
            $totalIncome = Transaction::income()
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            //total expense for selected month/year
            $totalExpense = Transaction::expense()
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');
            
            //balance
            $balance = $totalIncome - $totalExpense;

            //get month name for display (Indonesian)
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $monthName = $monthNames[$month] . ' ' . $year;

            return [
                Stat::make('Total Pendapatan', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                    ->description('Periode: ' . $monthName)
                    ->color('success')
                    ->icon('heroicon-o-arrow-trending-up'),
    
                Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalExpense, 0, ',', '.'))
                    ->description('Periode: ' . $monthName)
                    ->color('danger')
                    ->icon('heroicon-o-arrow-trending-down'),
    
                Stat::make('Selisih', 'Rp ' . number_format($balance, 0, ',', '.'))
                    ->description($balance >= 0 ? 'Surplus - ' . $monthName : 'Defisit - ' . $monthName)
                    ->color($balance >= 0 ? 'success' : 'danger')
                    ->icon($balance >= 0 ? 'heroicon-o-arrow-up' : 'heroicon-o-arrow-down'),
            ];

            return [

            ];
        
    }
}
