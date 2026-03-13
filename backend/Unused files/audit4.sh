echo "========== TABLE EXISTENCE =========="

php artisan tinker --execute="
echo 'modules: '.(\Schema::hasTable('modules') ? 'YES' : 'NO').PHP_EOL;
echo 'plan_module: '.(\Schema::hasTable('plan_module') ? 'YES' : 'NO').PHP_EOL;
echo 'specialty_module: '.(\Schema::hasTable('specialty_module') ? 'YES' : 'NO').PHP_EOL;
"

echo "========== TOTAL MODULES =========="

php artisan tinker --execute="
echo 'Total Modules: '.\App\Models\Module::count().PHP_EOL;
"

echo "========== MODULE USAGE COUNT =========="

php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$modules = \App\Models\Module::all();

foreach (\$modules as \$module) {
    \$planCount = DB::table('plan_module')
        ->where('module_id', \$module->id)
        ->count();

    \$specialtyCount = DB::table('specialty_module')
        ->where('module_id', \$module->id)
        ->count();

    echo \"Module: {\$module->key} | PlanLinks: {\$planCount} | SpecialtyLinks: {\$specialtyCount}\".PHP_EOL;
}
"

echo "========== EMPTY PLANS =========="

php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$plans = \App\Models\SubscriptionPlan::all();

foreach (\$plans as \$plan) {
    \$count = DB::table('plan_module')
        ->where('plan_id', \$plan->id)
        ->count();

    echo \"Plan {\$plan->id} ({\$plan->name}) Modules: {\$count}\".PHP_EOL;
}
"

echo "========== EMPTY SPECIALTIES =========="

php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$specialties = \App\Models\Specialty::all();

foreach (\$specialties as \$specialty) {
    \$count = DB::table('specialty_module')
        ->where('specialty_id', \$specialty->id)
        ->count();

    echo \"Specialty {\$specialty->id} ({\$specialty->name}) Modules: {\$count}\".PHP_EOL;
}
"
