<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory;

    public const TYPE_ADMISSION = 'admission';

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_ONE_TIME = 'one_time';

    public const TYPE_TRANSPORT = 'transport';

    protected $fillable = ['session_id', 'class_id', 'name', 'type', 'amount', 'is_optional', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_optional' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}