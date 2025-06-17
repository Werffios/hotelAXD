<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomSite extends Model
{
    /** @use HasFactory<\Database\Factories\RoomSiteFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];


    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
