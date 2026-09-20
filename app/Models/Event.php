<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'location', 'starts_at', 'ends_at', 'color', 'is_public'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}