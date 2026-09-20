<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportStop extends Model
{
    use HasFactory;

    protected $fillable = ['route_id', 'name', 'fare', 'order_index'];

    protected function casts(): array
    {
        return [
            'fare' => 'decimal:2',
            'order_index' => 'integer',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }
}