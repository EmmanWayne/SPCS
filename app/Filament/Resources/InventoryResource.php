<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    protected static ?string $modelLabel = 'inventario';
    
    protected static ?string $pluralModelLabel = 'inventarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label(__('filament-panels::fields.product_id'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('quantity')
                            ->label(__('filament-panels::fields.quantity'))
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('minimum_stock')
                            ->label(__('filament-panels::fields.minimum_stock'))
                            ->numeric()
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament-panels::fields.product_id'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('filament-panels::fields.quantity'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label(__('filament-panels::fields.minimum_stock'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-panels::fields.status'))
                    ->badge()
                    ->color(fn ($record): string => 
                        $record->quantity <= $record->minimum_stock ? 'danger' : 'success'
                    )
                    ->formatStateUsing(fn ($record): string => 
                        $record->quantity <= $record->minimum_stock ? 'Stock Bajo' : 'Stock Normal'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament-panels::fields.status'))
                    ->options([
                        'low' => 'Stock Bajo',
                        'normal' => 'Stock Normal',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'low') {
                            $query->whereRaw('quantity <= minimum_stock');
                        } elseif ($data['value'] === 'normal') {
                            $query->whereRaw('quantity > minimum_stock');
                        }
                    }),
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
