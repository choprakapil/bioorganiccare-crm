<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$doctor = \App\Models\User::find(19);
$doctor->plan_id = 11;
$doctor->save();
echo "Plan changed to 11";
