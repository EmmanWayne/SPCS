<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    protected static ?string $modelLabel = 'venta';
    
    protected static ?string $pluralModelLabel = 'ventas';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_number')
                                    ->label(__('filament-panels::fields.reference_number'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default('VENT-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                                    ->maxLength(255),

                                Forms\Components\Select::make('customer_id')
                                    ->label(__('filament-panels::fields.customer_id'))
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DatePicker::make('sale_date')
                                    ->label(__('filament-panels::fields.sale_date'))
                                    ->required()
                                    ->default(now()),

                                Forms\Components\Select::make('status')
                                    ->label(__('filament-panels::fields.status'))
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'processing' => 'En Proceso',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado',
                                    ])
                                    ->required()
                                    ->default('pending')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $record) {
                                        if ($record && $state === 'completed') {
                                            // Actualizar inventario al completar la venta
                                            foreach ($record->items as $item) {
                                                $inventory = Inventory::where('product_id', $item->product_id)->first();
                                                if ($inventory) {
                                                    $inventory->decrement('quantity', $item->quantity);
                                                }
                                            }
                                        }
                                    }),
                            ]),

                        Forms\Components\Section::make('Productos')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label(__('filament-panels::fields.product_id'))
                                            ->options(Product::query()->pluck('name', 'id'))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    $set('unit_price', $product->sale_price);
                                                }
                                            })
                                            ->searchable(),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label(__('filament-panels::fields.quantity'))
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                if ($state && $get('product_id')) {
                                                    $inventory = Inventory::where('product_id', $get('product_id'))->first();
                                                    if ($inventory && $state > $inventory->quantity) {
                                                        Notification::make()
                                                            ->warning()
                                                            ->title('Stock insuficiente')
                                                            ->body("Solo hay {$inventory->quantity} unidades disponibles.")
                                                            ->send();
                                                        $set('quantity', $inventory->quantity);
                                                        $state = $inventory->quantity;
                                                    }
                                                }
                                                $set('total_price', $state * $get('unit_price'));
                                            }),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label(__('filament-panels::fields.unit_price'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->prefix('$')
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $set('total_price', $state * $get('quantity'));
                                            }),

                                        Forms\Components\TextInput::make('total_price')
                                            ->label(__('filament-panels::fields.total_price'))
                                            ->numeric()
                                            ->disabled()
                                            ->prefix('$')
                                            ->dehydrated(),

                                        Forms\Components\TextInput::make('stock_available')
                                            ->label('Stock Disponible')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->reactive()
                                            ->afterStateHydrated(function ($component, $state, $get) {
                                                $productId = $get('product_id');
                                                if ($productId) {
                                                    $inventory = Inventory::where('product_id', $productId)->first();
                                                    $component->state($inventory ? $inventory->quantity : 0);
                                                }
                                            }),
                                    ])
                                    ->columns(5),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('filament-panels::fields.notes'))
                            ->columnSpan('full'),
                    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(__('filament-panels::fields.reference_number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('filament-panels::fields.customer_id'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label(__('filament-panels::fields.sale_date'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-panels::fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(__('filament-panels::fields.total_amount'))
                    ->money('MXN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Productos')
                    ->counts('items'),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
            'view' => Pages\ViewSale::route('/{record}'),
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
}
