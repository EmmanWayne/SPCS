<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Date;
use App\Helpers\NumberFormatter;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Supplier;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Transacciones';
    
    protected static ?string $modelLabel = 'compra';
    
    protected static ?string $pluralModelLabel = 'compras';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Número de Referencia')
                                    ->default('COMP-' . random_int(1000, 9999))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('supplier_id')
                                    ->label('Proveedor')
                                    ->options(function () {
                                        return Supplier::query()
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($supplier) => [
                                                $supplier->id => "{$supplier->name} - {$supplier->rtn}"
                                            ]);
                                    })
                                    ->searchable(['name', 'rtn'])
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('purchase_date')
                                    ->label('Fecha de Compra')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Section::make('Detalles de la Compra')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Producto')
                                            ->options(Product::query()->pluck('name', 'id'))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    $set('unit_price', $product->purchase_price);
                                                }
                                            })
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Precio Unitario')
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->prefix('L')
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state && $get('quantity')) {
                                                    $set('total_price', floatval($state) * floatval($get('quantity')));
                                                }
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state && $get('unit_price')) {
                                                    $set('total_price', floatval($state) * floatval($get('unit_price')));
                                                }
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('total_price')
                                            ->label('Total')
                                            ->disabled()
                                            ->numeric()
                                            ->prefix('L')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(5)
                                    ->defaultItems(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $totalAmount = collect($state ?? [])->sum('total_price');
                                        $set('total_amount', $totalAmount);
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total General')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('L'),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => NumberFormatter::formatLempiras($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'En Proceso',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'warning' : null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $totalAmount = 0;
        if (isset($data['items'])) {
            foreach ($data['items'] as &$item) {
                $item['total_price'] = floatval($item['quantity']) * floatval($item['unit_price']);
                $totalAmount += $item['total_price'];
            }
        }
        $data['total_amount'] = $totalAmount;
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $purchase = static::getModel()::create([
            'reference_number' => $data['reference_number'],
            'supplier_id' => $data['supplier_id'],
            'purchase_date' => $data['purchase_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'total_amount' => 0, // Inicialmente 0
        ]);

        // Crear los items y actualizar el total
        if (isset($data['items'])) {
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $purchaseItem = $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
                $totalAmount += $purchaseItem->total_price;
            }

            // Actualizar el total de la compra
            $purchase->update(['total_amount' => $totalAmount]);
        }

        return $purchase;
    }
}
