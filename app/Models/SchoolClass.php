<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['name', 'numeric_value', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'integer',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classTeachers(): HasMany
    {
        return $this->hasMany(ClassTeacher::class);
    }

    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }
}