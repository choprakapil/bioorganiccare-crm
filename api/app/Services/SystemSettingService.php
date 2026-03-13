<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingService
{
    public function get(string $key, $default = null)
    {
        return Cache::rememberForever("sys_setting_{$key}", function () use ($key, $default) {
            $setting = SystemSetting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public function set(string $key, $value): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
        Cache::forget("sys_setting_{$key}");
    }
}
