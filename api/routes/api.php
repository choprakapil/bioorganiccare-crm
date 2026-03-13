<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\AuthController;

Broadcast::routes([
    'middleware' => ['auth:sanctum'],
]);

Route::get('/health', [\App\Http\Controllers\SystemHealthController::class, 'health']);

Route::post('/login', [AuthController::class, 'login']);

// Landing Page Enquiry (Public)
Route::post('/landing-enquiry', [\App\Http\Controllers\LandingEnquiryController::class, 'store']);

// Public Invoice Access
Route::get('/public/invoices/{uuid}', [\App\Http\Controllers\InvoiceController::class, 'showPublic']);
Route::get('/public/invoices/{uuid}/pdf', [\App\Http\Controllers\InvoiceController::class, 'generatePublicPdf']);

Route::middleware(['auth:sanctum', 'tenant', 'require.specialty.admin'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile']);

    // Alias route for symmetry with Pharmacy
    Route::get('admin/clinical-catalog', [\App\Http\Controllers\ClinicalCatalogController::class, 'index']);

    // ── Hybrid Suggestion Visibility ────
    Route::prefix('admin/hybrid-suggestions')->group(function () {
        Route::get('/services', [\App\Http\Controllers\Admin\AdminHybridSuggestionController::class, 'services']);
        Route::get('/medicines', [\App\Http\Controllers\Admin\AdminHybridSuggestionController::class, 'medicines']);
        Route::get('/master-medicines', [\App\Http\Controllers\Admin\AdminHybridSuggestionController::class, 'masterMedicines']);
    });

    Route::prefix('admin/hybrid-promotions')->group(function () {
        Route::post('/service/{id}', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'promoteService']);
        Route::post('/medicine/{id}', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'promoteMedicine']);
        Route::post('/bulk', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'bulkPromote']);
        Route::post('/approve/{id}', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'approvePromotion']);
        Route::get('/pending', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'listPending']);
        Route::post('/reject/{id}', [\App\Http\Controllers\Admin\AdminHybridPromotionController::class, 'rejectPromotion']);
    });

    // ── Delete Manager Governance (Shared Access, Prefixed /admin/delete) ────
    Route::prefix('admin/delete')->group(function () {
        // ── Deletion Workflow (B-Mode: Request → Approve → Execute) ────────
        Route::get('/requests',                         [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'listRequests']);
        Route::get('/requests/{id}/drift-check',        [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'driftCheck']);
        Route::post('/requests/{id}/approve',           [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'approve']);
        Route::post('/requests/{id}/reject',            [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'reject']);
        Route::post('/requests/{id}/execute',           [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'execute']);
        Route::post('/{entity}/{id}/request-cascade',   [\App\Http\Controllers\Admin\DeletionWorkflowController::class, 'requestCascade']);

        // ── Delete Manager Direct (Standardized) ───────────────────────────
        Route::get('/{entity}/{id}/summary',        [\App\Http\Controllers\Admin\DeleteManagerController::class, 'summary']);
        Route::delete('/{entity}/{id}/archive',     [\App\Http\Controllers\Admin\DeleteManagerController::class, 'archive']);
        Route::post('/{entity}/{id}/restore',       [\App\Http\Controllers\Admin\DeleteManagerController::class, 'restore']);
        Route::delete('/{entity}/{id}/force',       [\App\Http\Controllers\Admin\DeleteManagerController::class, 'forceDelete']);
        Route::delete('/{entity}/{id}/force-cascade', [\App\Http\Controllers\Admin\DeleteManagerController::class, 'forceDeleteCascade']);
        Route::get('/{entity}/{id}/cascade-preview', [\App\Http\Controllers\Admin\DeleteManagerController::class, 'cascadePreview']);
        Route::delete('/{entity}/bulk',             [\App\Http\Controllers\Admin\DeleteManagerController::class, 'bulk']);
    });

    // ── System Governance (Dashboard & Repairs) ────
    Route::prefix('admin/governance')->middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
        Route::get('/health', [\App\Http\Controllers\Admin\GovernanceController::class, 'health']);
        Route::post('/repair/duplicate-services', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairDuplicateServices']);
        Route::post('/repair/duplicate-medicines', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairDuplicateMedicines']);
        Route::post('/repair/inventory-batches', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairInventoryBatches']);
        Route::post('/repair/orphan-treatments', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairOrphanTreatments']);
        Route::post('/repair/negative-inventory', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairNegativeInventory']);
        Route::post('/repair/medicine-drift', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairMedicineDrift']);
        Route::post('/repair/floating-ledger', [\App\Http\Controllers\Admin\GovernanceController::class, 'repairFloatingLedger']);
    });
    
    Route::middleware(['role:doctor,staff', 'subscription'])->group(function () {
        // Universal Access (Patients, Appointments)
        Route::get('/patients/{patient}/full', [\App\Http\Controllers\PatientController::class, 'full'])->name('patients.full')->middleware(['check.permission:patients', 'module:auto']);
        


        Route::apiResource('patients', \App\Http\Controllers\PatientController::class)->middleware(['check.permission:patients', 'module:auto'])->except(['destroy']);
        Route::apiResource('appointments', \App\Http\Controllers\AppointmentController::class)->middleware(['check.permission:appointments', 'module:auto']);
        
        // Billing (Read Only - Module Protected)
        Route::middleware(['role:doctor', 'module:auto'])->group(function () {
             Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('billing.index');
             Route::get('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('billing.show');
             Route::get('/invoices/{id}/pdf', [\App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('billing.pdf');
        });

        // Billing Write Access (Restricted for Receptionist)
        Route::middleware('check.permission:billing_write')->group(function () {
             Route::middleware('module:auto')->as('billing.')->group(function() {
                Route::post('/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])
                    ->name('invoices.store')
                    ->middleware('block.receptionist');
                
                Route::put('/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
                

                
                Route::post('/invoices/{invoice}/finalize', [\App\Http\Controllers\InvoiceController::class, 'finalize'])
                    ->name('invoices.finalize')
                    ->middleware('block.receptionist');
                
                Route::patch('/invoices/{invoice}/status', [\App\Http\Controllers\InvoiceController::class, 'updateStatus'])
                    ->name('invoices.status')
                    ->middleware('block.receptionist');
                
                Route::post('/invoices/{invoice}/apply-payment', [\App\Http\Controllers\InvoiceController::class, 'applyPayment'])
                    ->name('invoices.apply_payment')
                    ->middleware('block.receptionist');
                
                Route::post('/invoices/{invoice}/approve-reallocation', [\App\Http\Controllers\InvoiceController::class, 'approveReallocation']);
             });
             
             // Expenses also considered billing/finance
             Route::middleware('module:auto')->as('expenses.')->group(function() {
                 Route::get('/expenses/summary', [\App\Http\Controllers\ExpenseController::class, 'summary'])->name('summary');
                 Route::apiResource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['destroy']);
             });
        });

        // Clinical Access (Restricted for Receptionist)
        Route::middleware(['check.permission:clinical', 'module:auto'])->as('clinical-catalog.')->group(function () {
            Route::get('/clinical-catalog', [\App\Http\Controllers\ClinicalCatalogController::class, 'index'])->name('index');
            Route::post('/clinical-catalog', [\App\Http\Controllers\ClinicalCatalogController::class, 'store'])
                ->name('store')
                ->middleware('require.doctor.role');
            Route::post('/clinical-catalog/settings', [\App\Http\Controllers\ClinicalCatalogController::class, 'updateSettings'])
                ->name('settings')
                ->middleware('require.doctor.role');

            Route::get('/patients/{patient}/treatments', [\App\Http\Controllers\TreatmentController::class, 'getByPatient'])
                ->name('treatments.by_patient')
                ->middleware('block.receptionist');

            Route::post('/treatments', [\App\Http\Controllers\TreatmentController::class, 'store'])
                ->name('treatments.store')
                ->middleware('block.receptionist');

            Route::put('/treatments/{treatment}', [\App\Http\Controllers\TreatmentController::class, 'update'])
                ->name('treatments.update')
                ->middleware('block.receptionist');



            Route::apiResource('treatments', \App\Http\Controllers\TreatmentController::class)->except(['store', 'update', 'destroy']);
        });

        // Pharmacy Access (Restricted for Receptionist)
        Route::middleware(['check.permission:pharmacy', 'module:auto'])->as('inventory.')->group(function () {
             Route::get('/pharmacy/catalog', [\App\Http\Controllers\Doctor\PharmacyController::class, 'catalog'])->name('catalog');


             Route::get('/inventory/search-medicines', [\App\Http\Controllers\InventoryController::class, 'searchMedicines']);
             Route::get('/inventory/analytics', [\App\Http\Controllers\InventoryController::class, 'analytics'])->name('analytics');
              Route::post('/inventory/{inventory}/replenish', [\App\Http\Controllers\InventoryController::class, 'replenish'])
                  ->name('replenish')
                  ->middleware('block.receptionist');
              
              Route::post('/inventory/{inventory}/adjust', [\App\Http\Controllers\InventoryController::class, 'adjust'])
                  ->name('adjust')
                  ->middleware('block.receptionist');

              Route::post('/inventory', [\App\Http\Controllers\InventoryController::class, 'store'])->middleware('block.receptionist');
              Route::match(['put', 'patch'], '/inventory/{inventory}', [\App\Http\Controllers\InventoryController::class, 'update'])->middleware('block.receptionist');


              Route::apiResource('inventory', \App\Http\Controllers\InventoryController::class)->except(['store', 'update', 'destroy']);

        });

        // Settings / Admin Level (Doctors Only)
        Route::middleware('check.permission:settings')->group(function () {
             Route::get('staff/{id}/activity', [\App\Http\Controllers\StaffController::class, 'activity']);
             Route::post('staff', [\App\Http\Controllers\StaffController::class, 'store'])
                 ->name('staff.store')
                 ->middleware('require.doctor.role');
             
             // Define granular staff routes BEFORE the resource to avoid 404/ID collision
             Route::get('/staff/me', [\App\Http\Controllers\StaffController::class, 'me'])
                 ->withoutMiddleware('check.permission:settings')
                 ->middleware('role:doctor,staff');

             Route::match(['put', 'patch'], 'staff/{staff}', [\App\Http\Controllers\StaffController::class, 'update'])->middleware('require.doctor.role');

             Route::apiResource('staff', \App\Http\Controllers\StaffController::class)->except(['store', 'update', 'destroy']);
             Route::patch('/settings/branding', [\App\Http\Controllers\SettingsController::class, 'updateBranding']);
             Route::post('/settings/logo', [\App\Http\Controllers\SettingsController::class, 'updateLogo']);
             // Route::get('/settings', ...) is READ, maybe allow?
             // Prompt says "Settings: Doctor Only (Staff blocked)". So block GET too?
             // But Staff might need to know clinic name/logo.
             // `CheckStaffPermissions` blocks `settings`.
             // I'll keep GET `settings` outside or inside?
             // "Settings (Edit)" was blocked. "Cannot modify...".
             // But `CheckStaffPermissions` implementation blocks ALL settings module.
             // Let's assume GET settings is needed for frontend to show Logo.
        });
        
        // Read Settings (Available to all for UI display)
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'getSettings']);

        // Reports (Restricted)
        Route::middleware(['check.permission:reports', 'module:auto'])->prefix('growth')->as('insights.')->group(function () {
             Route::get('/insights', [\App\Http\Controllers\GrowthInsightController::class, 'index'])->name('index');
        });

        // 🆕 Finance (Read-Only)
        Route::middleware(['role:doctor', 'module:auto'])->group(function () {
             Route::get('/finance/summary', [\App\Http\Controllers\FinanceController::class, 'summary'])->name('expenses.finance_summary');
             Route::get('/finance/revenue-trend', [\App\Http\Controllers\FinanceController::class, 'revenueTrend'])->name('expenses.revenue_trend');
             Route::get('/system/integrity', [\App\Http\Controllers\SystemController::class, 'integrity'])->name('expenses.integrity');
             Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit_logs.index');
        });

        // Doctor Subscription Details (No Module restriction)
        Route::get('/subscription/usage', [\App\Http\Controllers\Doctor\SubscriptionUsageController::class, 'usage'])->middleware('role:doctor');
        Route::get('/subscription/me', [\App\Http\Controllers\Doctor\DoctorSubscriptionController::class, 'me'])->middleware('role:doctor');

        // Notifications (Personal/Shared)
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead']);
    });

    // Stop impersonation (must be reachable by the impersonated doctor token)
    Route::post('/admin/stop-impersonation', [\App\Http\Controllers\AdminImpersonationController::class, 'stop']);

    // Admin Routes
    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::post('/impersonate/{doctor}', [\App\Http\Controllers\AdminImpersonationController::class, 'impersonate']);




        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);

        Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'index']);
        Route::get('/doctors/reference-data', [\App\Http\Controllers\Admin\DoctorController::class, 'getReferenceData']);
        Route::post('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'store']);
        Route::patch('/doctors/{user}/toggle-active', [\App\Http\Controllers\Admin\DoctorController::class, 'toggleActive']);
        Route::patch('/doctors/{user}/reset-password', [\App\Http\Controllers\Admin\DoctorController::class, 'resetPassword']);
        Route::get('/doctors/{user}/logs', [\App\Http\Controllers\Admin\DoctorController::class, 'getLogs']);
        Route::get('/doctors/{user}', [\App\Http\Controllers\Admin\DoctorController::class, 'show']);

        Route::get('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'index']);
        Route::post('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'store']);
        Route::patch('/plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update']);


        Route::get('specialties/archived', [\App\Http\Controllers\Admin\SpecialtyController::class, 'archived']);
        Route::get('services/archived', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'archived']);
        Route::get('medicines/archived', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'archived']);

        Route::apiResource('specialties', \App\Http\Controllers\Admin\SpecialtyController::class);
        
        // Catalog Manager Routes
        Route::get('specialties/{specialty}/catalog', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'index']);
        Route::post('specialties/{specialty}/categories', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'storeCategory']);
        Route::put('categories/{category}', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'updateCategory']);

        
        Route::post('specialties/{specialty}/services', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'storeService']);
        Route::put('services/{item}', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'updateService']);

        


        // Pharmacy Governance (Structured)
        Route::get('specialties/{specialty}/pharmacy-catalog', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'index']);
        Route::post('specialties/{specialty}/pharmacy-categories', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'storeCategory']);
        Route::put('pharmacy-categories/{category}', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'updateCategory']);

        
        Route::post('specialties/{specialty}/medicines', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'storeMedicine']);
        Route::put('medicines/{medicine}', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'updateMedicine']);




        // Import Routes
        Route::post('/clinical-catalog/import', [\App\Http\Controllers\Admin\ClinicalCatalogManagerController::class, 'import']);
        Route::post('/pharmacy-catalog/import', [\App\Http\Controllers\Admin\PharmacyCatalogManagerController::class, 'import']);

        Route::post('/system-settings', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'store']);
        Route::get('/system-settings/{key}', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'get']);

        // Service Moderation
        Route::get('/service-submissions', [\App\Http\Controllers\Admin\AdminServiceModerationController::class, 'index']);
        Route::post('/service-submissions/{id}/approve', [\App\Http\Controllers\Admin\AdminServiceModerationController::class, 'approve']);
        Route::post('/service-submissions/{id}/reject', [\App\Http\Controllers\Admin\AdminServiceModerationController::class, 'reject']);

        // Recycle Bin
        Route::get('/recycle-bin/doctors', [\App\Http\Controllers\Admin\RecycleBinController::class, 'index']);


        // Landing Enquiries
        Route::get('/enquiries', [\App\Http\Controllers\LandingEnquiryController::class, 'index']);

        // Module List (for Plan Configuration)
        Route::get('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index']);

        // Subscription Management
        Route::controller(\App\Http\Controllers\Admin\AdminSubscriptionController::class)
            ->prefix('subscriptions')
            ->group(function () {
                Route::get('/', 'index');
                Route::get('/{doctor}', 'show');
                Route::post('/{doctor}/renew', 'renew');
                Route::post('/{doctor}/restart', 'restart');
                Route::post('/{doctor}/cancel', 'cancel');
                Route::post('/{doctor}/lifetime', 'lifetime');
                Route::patch('/{doctor}/plan', 'updatePlan');
            });
    });
});

Route::post('/admin/system-reset', [\App\Http\Controllers\Admin\SystemResetController::class, 'resetSystemData'])
    ->middleware(['auth:sanctum','role:super_admin']);
