<?php

namespace Database\Seeders;

use App\Models\GradeRule;
use Illuminate\Database\Seeder;

class GradeRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['A+', 5.0, 80, 100, 'Excellent'],
            ['A', 4.0, 70, 79, 'Very Good'],
            ['A-', 3.5, 60, 69, 'Good'],
            ['B', 3.0, 50, 59, 'Above Average'],
            ['C', 2.0, 40, 49, 'Average'],
            ['D', 1.0, 33, 39, 'Below Average'],
            ['F', 0.0, 0, 32, 'Fail'],
        ];

        foreach ($rules as [$name, $point, $min, $max, $remark]) {
            GradeRule::query()->updateOrCreate(
                ['name' => $name, 'min_mark' => $min, 'max_mark' => $max],
                ['grade_point' => $point, 'remark' => $remark]
            );
        }
    }
}