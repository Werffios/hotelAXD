<?php

namespace App\Filament\Resources\SeasonResource\RelationManagers;

use App\Models\RoomType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RoomPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'roomPrices';

    protected static ?string $recordTitleAttribute = 'price';

    protected static ?string $title = 'Precios por tipo de habitación';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_type_id')
                    ->label('Tipo de habitación')
                    ->options(RoomType::pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio por noche')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roomType.name')
                    ->label('Tipo de habitación')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio por noche')
                    ->money('COP'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
