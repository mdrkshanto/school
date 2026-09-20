<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookIssue extends Model
{
    use HasFactory;

    public const STATUS_ISSUED = 'issued';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_OVERDUE = 'overdue';

    protected $fillable = ['book_id', 'user_id', 'issued_at', 'due_date', 'returned_at', 'status', 'remark'];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}