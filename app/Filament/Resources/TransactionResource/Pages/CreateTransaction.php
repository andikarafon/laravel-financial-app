<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Get;
use Filament\Forms\Set;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                ->schema([
                    Forms\Components\Select::make('transaction_type')
                        ->label('Jenis Transaksi')
                        ->options([
                            'income' => 'Pemasukan',
                            'expense' => 'Pengeluaran',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            //reset category when transaction type changes
                            $set('category_id', null);
                        })
                        ->default('expense'),
                    
                    Forms\Components\Select::make('category_id')
                        ->label('Kategori')
                        ->relationship(
                            'category',
                            'name',
                            fn ($query, Get $get) => $query->when(
                                $get('transaction_type'),
                                fn ($q, $type) => $q->where('type', $type)
                            )
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->visible(fn (Get $get) => !empty($get('transaction_type')))
                        ->placeholder('Pilih Kategori'),
                    
                    Forms\Components\TextInput::make('name')
                        ->required(),
                    
                    Forms\Components\TextInput::make('amount')
                        ->label('Jumlah')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->step(0.01)
                        ->minValue(0),
                    
                    Forms\Components\DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                    
                    Forms\Components\TextArea::make('description')
                        ->label('Deskripsi')
                        ->maxLength(255)
                        ->rows(3)
                        ->columnSpanFull(),
                    
                ])->columns(2),


                Forms\Components\Section::make('Gambar Struk/Bukti Transaksi (opsional) ')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Gambar Struk')
                        ->image()
                        ->imageEditor()
                        ->imagePreviewHeight('250')
                        // ->directory('receipts')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ]),
            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array 
    {
        //store transaction type temporarily
        $transactionType = $data['transaction_type'] ?? null;

        //remove transaction_type from data as it's not a database field
        unset($data['transaction_type']);

        //clear merchant data and image of transaction type is income
        if ($transactionType == 'income') {
            $data['merchant_name'] = null;
            $data['merchant_address'] = null;
            $data['image'] = null;
        }

        //set user_id to current user
        $data['user_id'] = auth()->id();

        return $data;
    }

    /*
        calculate total amount from items
    */

    private function calculateTotalFromItems(Get $get, set $set): void
    {
        $items = $get('items') ?? [];
        $total = 0;

        foreach ($items as $item) {
            if (isset($item['quantity']) && isset($item['price'])) {
                $total += ($item['quantity'] * $item['price']);
            }
        }

        $set('amount', $total);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
}
