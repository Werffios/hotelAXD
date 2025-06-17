<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use App\Models\Season;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Habitaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_site_id')
                    ->label('Sitio de Habitación')
                    ->relationship('roomSite', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('room_type_id')
                    ->label('Tipo de Habitación')
                    ->relationship('roomType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('max_occupancy')
                    ->label('Ocupación Máxima')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->placeholder('Ingrese la ocupación máxima'),
                // Sección de precios por temporada
                Forms\Components\Section::make('Precios por temporada')
                    ->schema([
                        Forms\Components\Placeholder::make('price_info')
                            ->label('Información')
                            ->content('Los precios por temporada se establecen en la sección de precios')
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('roomSite.name')
                    ->label('Sitio de Habitación')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_occupancy')
                    ->label('Ocupación Máxima')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roomType.name')
                    ->label('Tipo de Habitación')
                    ->searchable(),
                // Columna para ver disponibilidad
                Tables\Columns\TextColumn::make('availability')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (Room $record): string =>
                        $record->bookings()
                            ->whereIn('status', ['pending', 'confirmed'])
                            ->where('check_out_date', '>=', now())
                            ->exists() ? 'danger' : 'success'
                    )
                    ->formatStateUsing(fn (Room $record): string =>
                        $record->bookings()
                            ->whereIn('status', ['pending', 'confirmed'])
                            ->where('check_out_date', '>=', now())
                            ->exists() ? 'Ocupada' : 'Disponible'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roomSite')
                    ->relationship('roomSite', 'name')
                    ->label('Sitio de Habitación')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('roomType')
                    ->relationship('roomType', 'name')
                    ->label('Tipo de Habitación')
                    ->searchable()
                    ->preload(),
                // Nuevo filtro para fechas de disponibilidad
                Tables\Filters\Filter::make('available_dates')
                    ->form([
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Fecha de entrada')
                            ->default(now()),
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('Fecha de salida')
                            ->default(now()->addDays(1)),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['check_in_date']) || !isset($data['check_out_date'])) {
                            return $query;
                        }

                        $checkInDate = $data['check_in_date'];
                        $checkOutDate = $data['check_out_date'];

                        return $query->whereDoesntHave('bookings', function (Builder $query) use ($checkInDate, $checkOutDate) {
                            $query->where(function (Builder $q) use ($checkInDate, $checkOutDate) {
                                $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                  ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                  ->orWhere(function (Builder $q) use ($checkInDate, $checkOutDate) {
                                      $q->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                  });
                            })->whereIn('status', ['pending', 'confirmed']);
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['check_in_date'] ?? null) {
                            $indicators['check_in_date'] = 'Entrada: ' . $data['check_in_date'];
                        }

                        if ($data['check_out_date'] ?? null) {
                            $indicators['check_out_date'] = 'Salida: ' . $data['check_out_date'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Nueva acción para crear reserva
                Tables\Actions\Action::make('reserve')
                    ->label('Reservar')
                    ->icon('heroicon-o-calendar')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Fecha de entrada')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('check_out_date')
                            ->label('Fecha de salida')
                            ->required()
                            ->default(now()->addDays(1))
                            ->afterOrEqual('check_in_date'),
                    ])
                    ->action(function (Room $record, array $data): void {
                        // Verificar disponibilidad
                        if (!$record->isAvailable($data['check_in_date'], $data['check_out_date'])) {
                            // Mostrar mensaje de error si no está disponible
                            Forms\Notification::make()
                                ->title('La habitación no está disponible en estas fechas')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Calcular precio total
                        $totalPrice = $record->getPriceForDates($data['check_in_date'], $data['check_out_date']);

                        // Si no hay precio configurado para estas fechas
                        if ($totalPrice <= 0) {
                            Forms\Notification::make()
                                ->title('No hay precios configurados para estas fechas')
                                ->warning()
                                ->send();

                            return;
                        }

                        // Crear la reserva
                        $record->bookings()->create([
                            'user_id' => auth()->id(),
                            'check_in_date' => $data['check_in_date'],
                            'check_out_date' => $data['check_out_date'],
                            'total_price' => $totalPrice,
                            'status' => 'confirmed',
                        ]);

                        Forms\Notification::make()
                            ->title('Reserva creada correctamente')
                            ->success()
                            ->send();
                    })->modalHeading('Crear reserva'),
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
            RelationManagers\BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
