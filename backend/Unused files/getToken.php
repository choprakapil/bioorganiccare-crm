<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$doctor = \App\Models\User::where('role', 'doctor')->first();
$token = $doctor->createToken('postman')->plainTextToken;
echo "{$doctor->id},{$doctor->plan_id},{$token}";
