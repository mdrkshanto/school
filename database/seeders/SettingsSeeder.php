<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // general
            'school.name' => ['Sunrise International School', 'general', true],
            'school.code' => ['SIS', 'general', true],
            'school.email' => ['info@sunriseschool.edu', 'general', true],
            'school.phone' => ['+880 2 5566 7788', 'general', true],
            'school.address' => ['House 12, Road 5, Dhanmondi, Dhaka', 'general', true],
            'school.motto' => ['Learn. Grow. Shine.', 'general', true],
            'school.website' => ['https://sunriseschool.edu', 'general', true],
            // academic
            'academic.current_session_id' => [null, 'academic', false],
            // terms & office
            'fees.currency' => ['$', 'fees', false],
            'notice.default_audience' => ['all', 'notices', false],
            'application.timezone' => [config('app.timezone'), 'general', false],
            'application.locale' => ['en', 'general', false],
        ];

        foreach ($settings as $key => [$value, $group, $public]) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'is_public' => $public]
            );
        }
    }
}