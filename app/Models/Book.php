<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'isbn', 'publisher', 'category',
        'description', 'quantity', 'available_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'available_count' => 'integer',
        ];
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BookIssue::class);
    }
}