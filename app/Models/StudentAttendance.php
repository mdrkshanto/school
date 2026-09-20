<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'student_attendance';

    public const PRESENT = 'present';

    public const ABSENT = 'absent';

    public const LATE = 'late';

    public const LEAVE = 'leave';

    public const STATUSES = [self::PRESENT, self::ABSENT, self::LATE, self::LEAVE];

    protected $fillable = ['session_id', 'class_id', 'section_id', 'student_id', 'date', 'status', 'remark'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
