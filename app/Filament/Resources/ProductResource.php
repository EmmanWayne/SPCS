<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Inventario';
    
    protected static ?string $modelLabel = 'producto';
    
    protected static ?string $pluralModelLabel = 'productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('filament-panels::fields.name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('code')
                                    ->label(__('filament-panels::fields.code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Select::make('category')
                                    ->label(__('filament-panels::fields.category'))
                                    ->options([
                                        'organic' => 'Orgánico',
                                        'gourmet' => 'Gourmet',
                                        'decaf' => 'Descafeinado',
                                        'blend' => 'Mezcla',
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('roast_level')
                                    ->label(__('filament-panels::fields.roast_level'))
                                    ->options([
                                        'light' => 'Ligero',
                                        'medium' => 'Medio',
                                        'dark' => 'Oscuro',
                                        'medium-dark' => 'Medio-Oscuro',
                                    ]),

                                Forms\Components\TextInput::make('origin')
                                    ->label(__('filament-panels::fields.origin')),

                                Forms\Components\TextInput::make('purchase_price')
                                    ->label(__('filament-panels::fields.purchase_price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),

                                Forms\Components\TextInput::make('sale_price')
                                    ->label(__('filament-panels::fields.sale_price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label(__('filament-panels::fields.description'))
                            ->columnSpan('full'),

                        // Sección de Inventario
                        Forms\Components\Section::make('Inventario')
                            ->schema([
                                Forms\Components\TextInput::make('inventory.quantity')
                                    ->label(__('filament-panels::fields.quantity'))
                                    ->numeric()
                                    ->required()
                                    ->default(0),

                                Forms\Components\TextInput::make('inventory.minimum_stock')
                                    ->label(__('filament-panels::fields.minimum_stock'))
                                    ->numeric()
                                    ->required()
                                    ->default(0),
                            ])
                            ->columns(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament-panels::fields.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-panels::fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label(__('filament-panels::fields.category'))
                    ->badge(),

                Tables\Columns\TextColumn::make('inventory.quantity')
                    ->label(__('filament-panels::fields.quantity'))
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record): string => 
                        $record->inventory?->quantity <= $record->inventory?->minimum_stock 
                            ? 'danger' 
                            : 'success'
                    ),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label(__('filament-panels::fields.purchase_price'))
                    ->money('MXN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label(__('filament-panels::fields.sale_price'))
                    ->money('MXN')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('filament-panels::fields.category'))
                    ->options([
                        'organic' => 'Orgánico',
                        'gourmet' => 'Gourmet',
                        'decaf' => 'Descafeinado',
                        'blend' => 'Mezcla',
                    ]),

                SelectFilter::make('stock_status')
                    ->label('Estado de Stock')
                    ->options([
                        'low' => 'Stock Bajo',
                        'normal' => 'Stock Normal',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'low' => $query->whereHas('inventory', function ($query) {
                                $query->whereRaw('quantity <= minimum_stock');
                            }),
                            'normal' => $query->whereHas('inventory', function ($query) {
                                $query->whereRaw('quantity > minimum_stock');
                            }),
                            default => $query
                        };
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('inventory', function ($query) {
            $query->whereRaw('quantity <= minimum_stock');
        })->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() ? 'danger' : null;
    }
}
