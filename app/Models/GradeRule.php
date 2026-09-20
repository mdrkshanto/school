<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeRule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'grade_point', 'min_mark', 'max_mark', 'remark'];

    protected function casts(): array
    {
        return [
            'grade_point' => 'decimal:1',
            'min_mark' => 'decimal:2',
            'max_mark' => 'decimal:2',
        ];
    }
}