<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'class_id', 'section_id', 'student_id', 'subject_id',
        'obtained_marks', 'grade', 'grade_point', 'is_absent',
    ];

    protected function casts(): array
    {
        return [
            'obtained_marks' => 'decimal:2',
            'grade_point' => 'decimal:1',
            'is_absent' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}