<?php

use Illuminate\Support\Facades\Http;
use App\Models\User;

$user = User::where('role', 'doctor')->first();
$token = $user->createToken('test-script')->plainTextToken;

$baseUrl = 'http://127.0.0.1:8000/api';
$headers = [
    'Authorization' => "Bearer {$token}",
    'Accept' => 'application/json',
];

$response = Http::withHeaders($headers)->get($baseUrl . '/clinical-catalog?view=my-services');
echo "=== /clinical-catalog?view=my-services ===\n";
echo json_encode($response->json(), JSON_PRETTY_PRINT);
