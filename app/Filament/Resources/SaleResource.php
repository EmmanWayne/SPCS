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
use Closure;
use App\Helpers\NumberFormatter;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationGroup = 'Transacciones';
    
    protected static ?string $modelLabel = 'venta';
    
    protected static ?string $pluralModelLabel = 'ventas';

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
                                    ->default('VENT-' . random_int(1000, 9999))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('sale_date')
                                    ->label('Fecha de Venta')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Section::make('Detalles de la Venta')
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
                                                    $set('unit_price', $product->sale_price);
                                                    
                                                    // Obtener y mostrar el stock disponible
                                                    $inventory = Inventory::where('product_id', $state)->first();
                                                    $set('available_stock', $inventory ? $inventory->quantity : 0);
                                                }
                                            })
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('available_stock')
                                            ->label('Stock Disponible')
                                            ->disabled()
                                            ->numeric()
                                            ->columnSpan(1),

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
                                            ->rules([
                                                function (Get $get) {
                                                    return function (string $attribute, $value, $fail) use ($get) {
                                                        $productId = $get('product_id');
                                                        if ($productId) {
                                                            $inventory = Inventory::where('product_id', $productId)->first();
                                                            if ($inventory && $value > $inventory->quantity) {
                                                                $fail("La cantidad excede el stock disponible ({$inventory->quantity})");
                                                            }
                                                        }
                                                    };
                                                }
                                            ])
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
                                    ->columns(6)
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referencia')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => NumberFormatter::formatLempiras($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
