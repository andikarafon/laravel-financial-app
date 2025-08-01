<?php


namespace App\Filament\Pages;

use App\Filament\Widgets\ExpenseChart;
use App\Filament\Widgets\IncomeChart;
use App\Filament\Widgets\StatsOverview;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;



class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public ?array $data = [];

    public function mount(): void 
    {
        //set default filter to current month/year
        $this->form->fill([
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month,
        ]);
    }

    public function filtersForm(Form $form): Form
    {
        $currentYear = Carbon::now()->year;
        
        $years = [];
        for ($year = $currentYear - 1; $year <= $currentYear + 1; $year++) {
            $years[$year] = $year;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari', 
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $form
            ->schema([
                Section::make('Filter Periode')
                    ->description('Pilih bulan dan tahun untuk melihat data keuangan')
                    ->schema([
                        Select::make('year')
                            ->label('Tahun')
                            ->options($years)
                            ->default($currentYear),
                        Select::make('month')
                            ->label('Bulan')
                            ->options($months)
                            ->default(Carbon::now()->month),
                    ])
                    ->columns(2),
            ]);
    }

    public function getHeaderWidgets(): array
    {
        return [
            //filter form will be here automatically
        ];
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            ExpenseChart::class,
            IncomeChart::class,
        ];
    }
}