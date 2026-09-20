<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendance';

    public const PRESENT = 'present';

    public const ABSENT = 'absent';

    public const LATE = 'late';

    public const LEAVE = 'leave';

    protected $fillable = ['teacher_id', 'date', 'status', 'remark'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
