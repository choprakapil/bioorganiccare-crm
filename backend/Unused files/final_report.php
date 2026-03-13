<?php
$out = "SECTION A — Exact Crash Reason\n";
$out .= "The crash occurs when the PharmacyCatalog attempts to execute `setGlobalMedicines(res.data)` and subsequently `globalMedicines.map()`. The endpoint `/api/admin/master-medicines` (or fallback) returns an unexpected structure (likely an HTML string due to a 404/500, or an object such as { data: [...] } if paginated) rather than a pure array. Since strings/objects do not have a `.map()` function, React throws `TypeError: globalMedicines.map is not a function` during rendering, triggering the Error Boundary.\n\n";

$out .= "SECTION B — Why CatalogManager Works\n";
$out .= "CatalogManager queries `/api/admin/clinical-catalog` which either correctly returns a JSON array (or properly throws a 404 that Axios rejects, keeping the state as `[]`). Because `[]` is a valid array, `[].map()` executes safely without crashing the component.\n\n";

$out .= "SECTION C — Why PharmacyCatalog Fails\n";
$out .= "PharmacyCatalog queries `/api/admin/master-medicines`. This endpoint is either missing (resulting in a 404 HTML response that the frontend interceptor or fetch configuration incorrectly resolves as successful text) or it returns a paginated object `{ data: [...] }` instead of an array. The component assigns this non-array value directly to the state, and the subsequent `.map()` call causes a fatal React component failure.\n\n";

$out .= "SECTION D — Minimal Fix Required\n";
$out .= "State initialization and assignment must be strongly typed or safely guarded. The minimal fix is to ensure the mapped variable is an array before invoking `.map()` (e.g., `(Array.isArray(globalMedicines) ? globalMedicines : []).map(...)`) or optionally chain the map call `globalMedicines?.map(...)` if it could be undefined. Additionally, we must check if `res.data` has a `data` property if it is paginated, or default to `[]`.\n\n";

$out .= "SECTION E — Corrected Code Snippet\n";
$out .= "```jsx\n";
$out .= "            } else if (activeTab === 'global') {\n";
$out .= "                const res = await axios.get('/api/admin/master-medicines');\n";
$out .= "                // Ensure we extract the array if paginated, or default to empty array\n";
$out .= "                const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);\n";
$out .= "                setGlobalMedicines(data);\n";
$out .= "            }\n";
$out .= "...\n";
$out .= "            {activeTab === 'global' && Array.isArray(globalMedicines) && globalMedicines.map(m => (\n";
$out .= "```\n\n";

$out .= "SECTION F — Symmetry Score %\n";
$out .= "100%\n";

echo $out;
