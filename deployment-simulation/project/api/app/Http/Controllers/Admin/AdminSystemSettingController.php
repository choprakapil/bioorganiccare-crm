<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SystemSettingService;

class AdminSystemSettingController extends Controller
{
    public function __construct(private readonly SystemSettingService $settings) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required', // Int or string
        ]);

        $this->settings->set($validated['key'], $validated['value']);

        return response()->json(['status' => 'success', 'key' => $validated['key'], 'value' => $validated['value']]);
    }

    public function get(Request $request, string $key)
    {
        return response()->json([$key => $this->settings->get($key)]);
    }
}
