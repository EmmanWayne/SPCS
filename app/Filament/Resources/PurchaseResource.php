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

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    protected static ?string $modelLabel = 'compra';
    
    protected static ?string $pluralModelLabel = 'compras';

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
                                    ->default('COMP-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                                    ->maxLength(255),

                                Forms\Components\Select::make('supplier_id')
                                    ->label(__('filament-panels::fields.supplier_id'))
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DatePicker::make('purchase_date')
                                    ->label(__('filament-panels::fields.purchase_date'))
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
                                    ->default('pending'),
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
                                                    $set('unit_price', $product->purchase_price);
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
                                    ])
                                    ->columns(4),
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

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label(__('filament-panels::fields.supplier_id'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label(__('filament-panels::fields.purchase_date'))
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
}
