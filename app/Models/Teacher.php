<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_no', 'name', 'gender', 'dob', 'phone', 'email',
        'qualification', 'specialization', 'address', 'join_date', 'leave_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'join_date' => 'date',
            'leave_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classTeachers(): HasMany
    {
        return $this->hasMany(ClassTeacher::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }
}