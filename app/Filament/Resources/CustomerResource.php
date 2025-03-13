<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Administración';
    
    protected static ?string $modelLabel = 'cliente';
    
    protected static ?string $pluralModelLabel = 'clientes';

    public static function form(Forms\Form $form): Forms\Form
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

                                Forms\Components\TextInput::make('email')
                                    ->label(__('filament-panels::fields.email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->label(__('filament-panels::fields.phone'))
                                    ->tel()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('tax_id')
                                    ->label(__('filament-panels::fields.tax_id'))
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('address')
                            ->label(__('filament-panels::fields.address'))
                            ->columnSpan('full')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('filament-panels::fields.notes'))
                            ->columnSpan('full')
                            ->maxLength(65535),
                    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-panels::fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament-panels::fields.email'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(__('filament-panels::fields.phone')),

                Tables\Columns\TextColumn::make('tax_id')
                    ->label(__('filament-panels::fields.tax_id')),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Total Ventas')
                    ->counts('sales')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_purchased')
                    ->label('Monto Total')
                    ->money('MXN')
                    ->getStateUsing(function (Customer $record): float {
                        return $record->sales()->sum('total_amount');
                    })
                    ->sortable(),
            ])
            ->filters([
                //
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
            RelationManagers\SalesRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
