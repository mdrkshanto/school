<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function index()
    {
        $presets = [
            'general' => [
                'school_name' => 'School Name',
                'school_tagline' => 'Tagline',
                'school_phone' => 'Phone',
                'school_email' => 'Email',
                'school_address' => 'Address',
            ],
            'academic' => [
                'current_session' => 'Current Session',
            ],
            'exam' => [
                'pass_percent' => 'Pass Percentage',
                'grade_scale' => 'Grade Scale',
            ],
        ];

        $current = $this->settings->all();
        $groups = collect($presets)->map(fn (array $fields, string $group) => [
            'key' => $group,
            'label' => ucfirst($group),
            'fields' => collect($fields)->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'value' => $current[$key] ?? '',
            ])->values(),
        ])->values();

        $extra = Setting::query()
            ->whereNotIn('key', collect($presets)->flatten()->keys()->all())
            ->orderBy('id')
            ->get(['key', 'value', 'group'])
            ->map(fn (Setting $setting) => [
                'key' => $setting->key,
                'value' => $setting->value,
                'group' => $setting->group,
            ]);

        return Inertia::render('admin/Settings/Index', [
            'groups' => $groups,
            'extra' => $extra,
        ]);
    }

    public function update(SettingRequest $request)
    {
        foreach ($request->input('settings', []) as $setting) {
            $this->settings->set($setting['key'], $setting['value'] ?? '');
        }

        $this->successFlash('Settings saved.');

        return back();
    }
}
