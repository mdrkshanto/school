<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Setting::query()->pluck('value', 'key')->all();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public function set(string $key, mixed $value, string $group = 'general'): Setting
    {
        return Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_scalar($value) ? (string) $value : null, 'group' => $group]
        );
    }
}
