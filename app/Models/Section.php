<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'name', 'capacity', 'status'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }
}