<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_ALUMNI = 'alumni';

    protected $fillable = [
        'user_id', 'admission_no', 'class_id', 'section_id', 'session_id',
        'name', 'gender', 'dob', 'blood_group', 'address', 'phone', 'email',
        'avatar', 'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relation');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function transport(): HasMany
    {
        return $this->hasMany(StudentTransport::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}