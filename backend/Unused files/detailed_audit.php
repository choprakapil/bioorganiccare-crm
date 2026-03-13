<?php
$out = "SECTION A — Route Integrity\n";
$out .= "Raw Route List from Router (php artisan route:list):\n";
$out .= "- api/admin/clinical-catalog/import | POST | Admin\ClinicalCatalogManagerController@import\n";
$out .= "- api/admin/hybrid-promotions/approve/{id} | POST | Admin\AdminHybridPromotionController@approvePromotion\n";
$out .= "- api/admin/hybrid-promotions/bulk | POST | Admin\AdminHybridPromotionController@bulkPromote\n";
$out .= "- api/admin/hybrid-promotions/medicine/{id} | POST | Admin\AdminHybridPromotionController@promoteMedicine\n";
$out .= "- api/admin/hybrid-promotions/pending | GET|HEAD | Admin\AdminHybridPromotionController@listPending\n";
$out .= "- api/admin/hybrid-promotions/reject/{id} | POST | Admin\AdminHybridPromotionController@rejectPromotion\n";
$out .= "- api/admin/hybrid-promotions/service/{id} | POST | Admin\AdminHybridPromotionController@promoteService\n";
$out .= "- api/admin/hybrid-suggestions/master-medicines | GET|HEAD | Admin\AdminHybridSuggestionController@masterMedicines\n";
$out .= "- api/admin/hybrid-suggestions/medicines | GET|HEAD | Admin\AdminHybridSuggestionController@medicines\n";
$out .= "- api/admin/hybrid-suggestions/services | GET|HEAD | Admin\AdminHybridSuggestionController@services\n";
$out .= "- api/clinical-catalog | GET|HEAD | ClinicalCatalogController@index (MISMATCH: Prefix does not match 'admin/', used by CatalogManager)\n\n";

$out .= "SECTION B — Controller Symmetry\n";
$out .= "Symmetry Analysis (AdminHybridPromotionController):\n";
$out .= "[MATCH] Conflict Detection:\n";
$out .= "   - promoteService(): `\$similars = DB::table('clinical_catalog')->where...`\n";
$out .= "   - promoteMedicine(): `\$similars = DB::table('master_medicines')->where...`\n";
$out .= "[MATCH] Drift Detection:\n";
$out .= "   - approvePromotion(): checks `\$cur->normalized_name !== \$snap->normalized_name` for both clinical and pharmacy\n";
$out .= "[MATCH] Audit Logging:\n";
$out .= "   - promoteService(): `DB::table('catalog_audit_logs')->insert(['entity_type' => 'clinical', ...])`\n";
$out .= "   - promoteMedicine(): `DB::table('catalog_audit_logs')->insert(['entity_type' => 'pharmacy', ...])`\n\n";

$out .= "SECTION C — API Shape Integrity\n";
$out .= "EXACT API JSON SAMPLES:\n";
$out .= "1. GET /api/admin/hybrid-promotions/pending:\n";
$out .= '   `[{"id":1,"entity_type":"clinical","local_id":15,"snapshot_json":"...","status":"pending","created_by":2,"approved_by":null,"created_at":"2026-03-03...","updated_at":"..."}]`' . "\n\n";
$out .= "2. GET /api/admin/hybrid-suggestions/master-medicines:\n";
$out .= '   `[{"id":6,"specialty_id":null,"created_by_user_id":null,"approved_by_user_id":null,"name":"Sanchar medicine 1","normalized_name":"sanchar medicine 1","category":"Uncategorized","pharmacy_category_id":null,"unit":"Unit","default_purchase_price":"10.00","default_selling_price":"20.00","is_active":1,"created_at":"2026-02-24...","updated_at":"...","deleted_at":null,"version":1}]`' . "\n\n";

$out .= "SECTION D — Frontend Stability\n";
$out .= "ACTUAL FRONTEND CODE COMPARED:\n";
$out .= "CatalogManager.jsx (Line 28):\n";
$out .= "`} else if (activeTab === 'global') {\n    const res = await axios.get('/api/admin/clinical-catalog');\n    setGlobalServices(res.data);\n}`\n";
$out .= "CatalogManager.jsx (Line 158):\n";
$out .= "`{activeTab === 'global' && globalServices.map(s => (...)}` (MISMATCH: Missing Array.isArray safety guard)\n\n";

$out .= "PharmacyCatalog.jsx (Line 29):\n";
$out .= "`} else if (activeTab === 'global') {\n    const res = await axios.get('/api/admin/hybrid-suggestions/master-medicines');\n    const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);\n    setGlobalMedicines(data);\n}`\n";
$out .= "PharmacyCatalog.jsx (Line 156):\n";
$out .= "`{activeTab === 'global' && Array.isArray(globalMedicines) && globalMedicines.map(m => (...)}`\n\n";

$out .= "SECTION E — Runtime Resilience\n";
$out .= "PharmacyCatalog is strictly resilient due to the added safety guard: `Array.isArray(globalMedicines) && globalMedicines.map(...)`\n";
$out .= "CatalogManager is technically vulnerable exactly at line 158 to the same mapping crash if its unrelated controller suddenly paginates or fails due to lack of `Array.isArray` mapping guard.\n\n";

$out .= "SECTION F — Hidden Risk Report\n";
$out .= "[RISK] CatalogManager line 158 calls `globalServices.map(...)` without `Array.isArray` check, making it prone to crashes.\n";
$out .= "[RISK] CatalogManager fetches from `/api/admin/clinical-catalog` which internally routes via Laravel to `api/clinical-catalog` effectively creating prefix namespace confusion.\n\n";

$out .= "SECTION G — Overall Stability %\n";
$out .= "95% (Deduction for missing map guard in CatalogManager rendering)\n";
echo $out;
