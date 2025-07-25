<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Services\GeminiService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Get;
use Filament\Forms\Set;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Edit Transaksi';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} (" . ucfirst($record->type) . ")")
                            ->required()
                            ->searchable()
                            ->preload(),

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
                            ->required(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(255)
                            ->rows(3)
                            ->columnSpanFull(),
                        
                    ])->columns(2),
                
                Forms\Components\Section::make('Gambar Struk & OCR')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Gambar Struk')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            // ->directory('receipts')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240) //10mb
                            ->columnSpanFull(),
                        
                        //ocr section
                        Forms\Components\View::make('filament.components.edit-ocr')
                            ->viewData(['record' => $this->record])
                            ->visible(fn () => $this->record->image)
                            ->columnSpanFull(),

                        
                        Forms\Components\Textarea::make('ocr_result')
                            ->label('Hasil OCR')
                            ->rows(8)
                            ->disabled()
                            ->placeholder('Hasil OCR akan muncul disini...')
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (get $get, Set $set, ?string $state) {

                            }),
                        
                        Forms\Components\Textarea::make('gemisi_json_data')
                            ->label('Data JSON')
                            ->rows(10)
                            ->disabled()
                            ->columnSpanFull(),
                        
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('applyGeminiData')
                                ->label('Terapkan data AI')
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->visible(fn () => !empty($this->geminiData) && isset($this->geminiData['parsed_data']))
                                ->action(function (Set $set) {

                                }),    
                        ])->columnSpanFull()
                    ]),

                    Forms\Components\Section::make('Data dari AI')
                        ->schema([
                            Forms\Components\TextInput::make('merchant_name')
                                ->label('Nama Merchant')
                                ->maxLength(255),
                            
                            Forms\Components\TextInput::make('merchant_address')
                                ->label('Alamat Merchant')
                                ->maxLength(255),
                            
                            Forms\Components\Repeater::make('items')
                                ->label('Items')
                                ->relationship('items')
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Item')
                                        ->required()
                                        ->maxLength(255),
                                    
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->step(1),
                                    
                                    Forms\Components\TextInput::make('price')
                                        ->label('Harga')
                                        ->numeric()
                                        ->required()
                                        ->prefix('Rp')
                                        ->step(0.01)
                                        ->minValue(0),
                                    
                                    Forms\Components\Placeholder::make('total')
                                        ->label('Total')
                                        ->content(function (Get $get) {
                                            $quantity = $get('quantity') ?? 0;
                                            $price = $get('price') ?? 0;
                                            $total = $quantity * $price;
                                            return 'Rp ' . number_format($total, 0, ',', '.');
                                        })
                                        ->columnSpan(1),
                                ])
                                ->columns(5)
                                ->addActionLabel('Tambah Item')
                                ->reorderableWithButtons()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                ->defaultItems(0)
                                ->columnSpanFull()
                                ->live() 
                        ]),
             ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
}
