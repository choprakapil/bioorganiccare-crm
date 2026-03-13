echo "==================== ENV CHECK ===================="

php artisan tinker --execute="echo 'Broadcast Driver: '.config('broadcasting.default').PHP_EOL;"
php artisan tinker --execute="echo 'Queue Driver: '.config('queue.default').PHP_EOL;"
php artisan tinker --execute="echo 'Cache Driver: '.config('cache.default').PHP_EOL;"

grep -E "^(BROADCAST_DRIVER|QUEUE_CONNECTION|CACHE_DRIVER|REVERB_APP_KEY)=" .env

echo "==================== ROUTE CHECK ===================="

php artisan route:list | grep broadcasting

echo "==================== CHANNEL AUTH CHECK ===================="

php artisan tinker --execute="
\$doctor = \App\Models\User::where('role','doctor')->first();
\$token = \$doctor->createToken('audit')->plainTextToken;
echo \$token;
"

TOKEN=$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$d=\App\Models\User::where("role","doctor")->first();
echo $d->createToken("audit")->plainTextToken;
')

DOC_ID=$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$d=\App\Models\User::where("role","doctor")->first();
echo $d->id;
')

curl -s -X POST http://127.0.0.1:8000/api/broadcasting/auth \
-H "Authorization: Bearer $TOKEN" \
-H "Content-Type: application/json" \
-d "{\"channel_name\": \"private-doctor.$DOC_ID\", \"socket_id\": \"123.456\"}"
echo ""

echo "==================== QUEUE STATE ===================="

php artisan tinker --execute="echo 'Pending Jobs: '.DB::table('jobs')->count().PHP_EOL;"
php artisan tinker --execute="echo 'Failed Jobs: '.DB::table('failed_jobs')->count().PHP_EOL;"

echo "==================== CACHE TEST ===================="

php artisan tinker --execute="
\$d=\App\Models\User::where('role','doctor')->first();
echo 'Enabled Modules: ';
print_r(\$d->enabled_modules);
"

echo "==================== EVENT DISPATCH TEST ===================="

php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
event(new \App\Events\DoctorPlanUpdated(19));
echo "Event Fired\n";
'

sleep 2

php artisan tinker --execute="echo 'Pending Jobs After Event: '.DB::table('jobs')->count().PHP_EOL;"

echo "==================== REVERB PROCESS CHECK ===================="

ps aux | grep "reverb" | grep -v grep

echo "==================== QUEUE WORKER CHECK ===================="

ps aux | grep "queue:work" | grep -v grep

echo "==================== END AUDIT ===================="
