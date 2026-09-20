<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassTeacher extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'class_id', 'section_id', 'teacher_id', 'is_class_teacher'];

    protected function casts(): array
    {
        return [
            'is_class_teacher' => 'boolean',
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

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}