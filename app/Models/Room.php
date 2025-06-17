<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'max_occupancy',
        'room_type_id',
        'room_site_id',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function roomSite(): BelongsTo
    {
        return $this->belongsTo(RoomSite::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Verificar si la habitación está disponible en un rango de fechas específico
     */
    public function isAvailable($checkInDate, $checkOutDate): bool
    {
        return $this->bookings()
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                    // Verificar si hay alguna reserva que se superponga
                    $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                      ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate]);
                })->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                    // Verificar si las fechas solicitadas engloban alguna reserva existente
                    $q->where('check_in_date', '<=', $checkInDate)
                      ->where('check_out_date', '>=', $checkOutDate);
                });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->count() === 0;
    }

    /**
     * Obtener el precio para un rango de fechas específico basado en las temporadas
     */
    public function getPriceForDates($checkInDate, $checkOutDate): float
    {
        $totalPrice = 0;
        $currentDate = new \DateTime($checkInDate);
        $endDate = new \DateTime($checkOutDate);

        while ($currentDate < $endDate) {
            $dateString = $currentDate->format('Y-m-d');

            // Buscar la temporada para la fecha actual
            $season = Season::where('start_date', '<=', $dateString)
                ->where('end_date', '>=', $dateString)
                ->first();

            if ($season) {
                // Obtener el precio para esta temporada y tipo de habitación
                $price = RoomPrice::where('room_type_id', $this->room_type_id)
                    ->where('season_id', $season->id)
                    ->value('price');

                $totalPrice += $price ?? 0;
            }

            $currentDate->modify('+1 day');
        }

        return $totalPrice;
    }
}
