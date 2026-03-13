<?php
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\Request;

echo "1. DATABASE STATE\n";
echo "local_services:\n";
echo json_encode(DB::table('local_services')->select('id', 'item_name', 'is_promoted', 'deleted_at')->get(), JSON_PRETTY_PRINT) . "\n\n";

echo "clinical_catalog:\n";
echo json_encode(DB::table('clinical_catalog')->select('id', 'item_name', 'specialty_id', 'deleted_at')->get(), JSON_PRETTY_PRINT) . "\n\n";

echo "doctor_service_settings:\n";
echo json_encode(DB::table('doctor_service_settings')->get(), JSON_PRETTY_PRINT) . "\n\n";

echo "inventory:\n";
echo json_encode(DB::table('inventory')->select('id', 'item_name', 'doctor_id', 'stock', 'is_selling', 'deleted_at')->get(), JSON_PRETTY_PRINT) . "\n\n";

echo "master_medicines:\n";
echo json_encode(DB::table('master_medicines')->get(), JSON_PRETTY_PRINT) . "\n\n";

echo "2. API RESPONSES\n";

// Assuming doctor has ID 30 based on settings table
$user = User::find(30) ?? User::first();
if ($user) {
    Auth::login($user);
    echo "USER LOGGED IN AS ID: " . $user->id . "\n\n";
    
    $requestMyServices = Request::create('/api/clinical-catalog', 'GET', ['view' => 'my-services']);
    $requestMyServices->headers->set('Accept', 'application/json');
    $responseMyServices = app()->handle($requestMyServices);
    echo "GET /api/clinical-catalog?view=my-services:\n";
    $myServicesPayload = $responseMyServices->getContent();
    echo $myServicesPayload . "\n\n";
    
    $requestCatalog = Request::create('/api/clinical-catalog', 'GET', ['view' => 'catalog']);
    $requestCatalog->headers->set('Accept', 'application/json');
    $responseCatalog = app()->handle($requestCatalog);
    echo "GET /api/clinical-catalog?view=catalog:\n";
    echo $responseCatalog->getContent() . "\n\n";
    
    $requestInventory = Request::create('/api/inventory', 'GET');
    $requestInventory->headers->set('Accept', 'application/json');
    $responseInventory = app()->handle($requestInventory);
    echo "GET /api/inventory:\n";
    $inventoryPayload = $responseInventory->getContent();
    echo $inventoryPayload . "\n\n";
    
    echo "3. FRONTEND MAPPING SIMULATION\n";
    $servicesData = json_decode($myServicesPayload, true);
    if (!is_array($servicesData)) $servicesData = [];
    $mappedServices = array_map(function($s) {
        $s['react_key'] = ($s['is_local'] ?? true ? 'local' : 'global') . '_' . $s['id'];
        return $s;
    }, $servicesData);
    
    echo "FINAL SERVICES ARRAY:\n";
    echo json_encode($mappedServices, JSON_PRETTY_PRINT) . "\n\n";
    
    $invData = json_decode($inventoryPayload, true);
    $finalInventory = isset($invData['data']) ? $invData['data'] : (is_array($invData) ? $invData : []);
    echo "FINAL INVENTORY ARRAY:\n";
    echo json_encode($finalInventory, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "4. DOM SIMULATION\n";
    $domServices = array_map(function($c) {
        return [
            'key' => $c['react_key'],
            'value' => ltrim($c['react_key'], 'localglobal_'),
            'label' => $c['item_name'] ?? 'Unknown'
        ];
    }, $mappedServices);
    echo "DOM SERVICES:\n";
    echo json_encode($domServices, JSON_PRETTY_PRINT) . "\n\n";
    
    $domInventory = array_map(function($i) {
        return [
            'key' => $i['id'],
            'value' => $i['id'],
            'label' => $i['item_name'] ?? 'Unknown'
        ];
    }, array_filter($finalInventory, function($i) {
        return ($i['deleted_at'] ?? null) === null;
    }));
    echo "DOM INVENTORY:\n";
    echo json_encode(array_values($domInventory), JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No user found to authenticate.\n";
}
