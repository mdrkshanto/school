<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_at', 'ends_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'session_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function studentAttendance(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function classTeachers(): HasMany
    {
        return $this->hasMany(ClassTeacher::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}