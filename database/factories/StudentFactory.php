<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admission_no' => 'S-'.$this->faker->unique()->numberBetween(10000, 99999),
            'class_id' => SchoolClass::factory(),
            'section_id' => fn (array $attrs) => Section::factory()->create(['class_id' => $attrs['class_id']]),
            'session_id' => AcademicSession::factory()->active(),
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'dob' => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->numerify('01#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => 'active',
        ];
    }
}