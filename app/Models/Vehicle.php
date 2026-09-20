<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['registration_no', 'model', 'capacity', 'driver_name', 'driver_phone', 'status'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(TransportRoute::class, 'route_vehicle', 'vehicle_id', 'route_id');
    }
}