<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Transaction;


class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(Transaction::count()),

            'income' => Tab::make('Income')
                ->modifyQueryUsing(fn (Builder $query) => $query->income())
                ->badge(Transaction::income()->count())
                ->badgeColor('success'),
            
            'expense' => Tab::make('Expense')
                ->modifyQueryUsing(fn (Builder $query) => $query->expense())
                ->badge(Transaction::expense()->count())
                ->badgeColor('danger'),
                
        ];
    }
}
