<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "# ================= MODULE TABLE STRUCTURE =\n";
print_r(Schema::getColumnListing('modules'));

print_r(Schema::getColumnListing('subscription_plans'));

print_r(Schema::getColumnListing('users'));

echo "Modules Count: ".\App\Models\Module::count().PHP_EOL;
echo "Plans Count: ".\App\Models\SubscriptionPlan::count().PHP_EOL;

print_r(\App\Models\Module::take(3)->get()->toArray());
print_r(\App\Models\SubscriptionPlan::take(3)->get()->toArray());

