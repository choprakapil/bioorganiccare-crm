<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Specialty;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\ClinicalCatalog;
use App\Models\ClinicalServiceCategory;
use App\Models\PharmacyCategory;
use App\Models\MasterMedicine;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Inventory;
use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\DeletionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DeleteManager
{
    private array $entityMap = [
        'specialty'         => Specialty::class,
        'service'           => ClinicalCatalog::class,
        'medicine'          => MasterMedicine::class,
        'doctor'            => User::class,
        'staff'             => User::class,
        'plan'              => SubscriptionPlan::class,
        'category'          => ClinicalServiceCategory::class,
        'pharmacy_category' => PharmacyCategory::class,
        'patient'           => Patient::class,
        'treatment'         => Treatment::class,
        'inventory'         => Inventory::class,
        'appointment'       => Appointment::class,
        'expense'           => Expense::class,
        'invoice'           => Invoice::class,
        'module'            => Module::class,
        'local_service'     => \App\Models\LocalService::class,
        'local_medicine'    => \App\Models\LocalMedicine::class,
    ];

    protected array $registry = [
        'specialty'         => Deletions\SpecialtyDeletionHandler::class,
        'service'           => Deletions\ServiceDeletionHandler::class,
        'medicine'          => Deletions\MedicineDeletionHandler::class,
        'doctor'            => Deletions\DoctorDeletionHandler::class,
        'staff'             => Deletions\StaffDeletionHandler::class,
        'plan'              => Deletions\PlanDeletionHandler::class,
        'category'          => Deletions\CategoryDeletionHandler::class,
        'pharmacy_category' => Deletions\PharmacyCategoryDeletionHandler::class,
        'patient'           => Deletions\PatientDeletionHandler::class,
        'treatment'         => Deletions\TreatmentDeletionHandler::class,
        'inventory'         => Deletions\InventoryDeletionHandler::class,
        'appointment'       => Deletions\AppointmentDeletionHandler::class,
        'expense'           => Deletions\ExpenseDeletionHandler::class,
        'invoice'           => Deletions\InvoiceDeletionHandler::class,
        'module'            => Deletions\ModuleDeletionHandler::class,
        'local_service'     => Deletions\LocalServiceDeletionHandler::class,
        'local_medicine'    => Deletions\LocalMedicineDeletionHandler::class,
    ];

    private function resolveModel(string $entity): string
    {
        if (!isset($this->entityMap[$entity])) {
            throw new \InvalidArgumentException("Unknown entity type: [{$entity}]");
        }
        return $this->entityMap[$entity];
    }

    public function isGoverned(string $entity): bool
    {
        return in_array($entity, ['specialty', 'doctor', 'plan']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEPENDENCY SUMMARIES
    // ─────────────────────────────────────────────────────────────────────────

    public function dependencySummary(string $entity, int $id): array
    {
        if (!isset($this->registry[$entity])) {
            throw new \InvalidArgumentException("No handler registered for entity: [{$entity}]");
        }

        $handlerClass = $this->registry[$entity];
        $handler = new $handlerClass();

        return $handler->summary($id);
    }

    // Handlers removed and moved to individual classes in App\Services\Deletions.
    // registry mapping above handles resolution.

    // ─────────────────────────────────────────────────────────────────────────
    // UNIFIED ARCHIVE / DELETE / RESTORE / FORCE
    // ─────────────────────────────────────────────────────────────────────────

    public function archive(string $entity, int $id): array
    {
        $model  = $this->resolveModel($entity);
        $record = $model::findOrFail($id);

        // Security check for role-specific users
        if (($entity === 'doctor' || $entity === 'staff') && $record->role !== $entity) {
             throw new \RuntimeException("Role mismatch: Record is not a [{$entity}]");
        }

        // Governance and safety blocks
        if ($entity === 'plan') {
            $summary = $this->planDependencySummary($id);
            if ($summary['active_doctors'] > 0) throw new \RuntimeException("Cannot delete plan assigned to active doctors.");
            if ($summary['specialty_plans'] <= 1) throw new \RuntimeException("Cannot delete the only plan of a specialty.");
        }

        if ($entity === 'invoice' && $record->is_finalized) {
            throw new \RuntimeException("Finalized invoices cannot be deleted.");
        }

        return DB::transaction(function() use ($entity, $id, $record) {
            app()->instance('deletion_context', true);
            try {
                // SPECIAL CLEANUP LOGIC BEFORE DELETING
                if ($entity === 'invoice') {
                    $this->cleanupInvoice($record);
                }

                if ($entity === 'staff') {
                     $record->tokens()->delete();
                }

                if ($entity === 'doctor') {
                     $record->tokens()->delete();
                }

                $record->delete();

                if ($entity === 'specialty') {
                    app(ModuleCacheService::class)->bumpSchemaVersion();
                }
                
                if ($entity === 'plan') {
                    app(ModuleCacheService::class)->invalidateByPlan($id);
                }

                $this->log('archive', $entity, $id);

                return ['status' => 'archived', 'entity' => $entity, 'id' => $id];
            } finally {
                app()->forgetInstance('deletion_context');
            }
        });
    }

    public function bulkDelete(string $entity, array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            try {
                $results[] = $this->delete($entity, $id);
            } catch (\Throwable $e) {
                $results[] = ['id' => $id, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }
        return ['status' => 'bulk_processed', 'results' => $results];
    }

    public function delete(string $entity, int $id): array
    {
        return $this->archive($entity, $id);
    }

    public function restore(string $entity, int $id): array
    {
        $model = $this->resolveModel($entity);
        $record = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model)) 
            ? $model::withTrashed()->findOrFail($id) 
            : $model::findOrFail($id);

        // Conflict check
        if ($entity === 'service') {
            $exists = ClinicalCatalog::where('specialty_id', $record->specialty_id)
                ->where('normalized_name', $record->normalized_name)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) throw new \RuntimeException("Cannot restore. Active item with same name exists.");
        }
        
        if ($entity === 'medicine') {
            $exists = MasterMedicine::where('specialty_id', $record->specialty_id)
                ->where('name', $record->name)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) throw new \RuntimeException("Cannot restore. Active medicine with same name exists.");
        }

        // DOCTOR SPECIAL RESTORE (Legacy handling)
        if ($entity === 'doctor') {
            if (strpos($record->email, '.deleted.') !== false) {
                $cleanedEmail = preg_replace('/\.deleted\.\d+$/', '', $record->email);
                if (!User::where('email', $cleanedEmail)->where('id', '!=', $record->id)->exists()) {
                    $record->email = $cleanedEmail;
                }
            }
            $record->is_active = true;
        }

        if (method_exists($record, 'restore')) {
            $record->restore();
            if ($entity === 'doctor') $record->save();
        }

        if ($entity === 'specialty') {
            app(ModuleCacheService::class)->bumpSchemaVersion();
        }

        $this->log('restore', $entity, $id);

        return ['status' => 'restored', 'entity' => $entity, 'id' => $id];
    }

    public function forceDelete(string $entity, int $id): array
    {
        if ($entity === 'service') {
            $local_services_count = \Illuminate\Support\Facades\DB::table('local_services')
                ->where('promoted_catalog_id', $id)
                ->count();
            
            if ($local_services_count > 0) {
                throw new \RuntimeException("Cannot delete global service. Local promoted services exist.");
            }
        }
        
        if ($entity === 'medicine') {
            $local_medicines_count = \Illuminate\Support\Facades\DB::table('local_medicines')
                ->where('promoted_master_id', $id)
                ->count();
            
            if ($local_medicines_count > 0) {
                throw new \RuntimeException("Cannot delete master medicine. Local promoted medicines exist.");
            }
        }

        $summary = $this->dependencySummary($entity, $id);

        if (!$summary['force_delete_safe']) {
            return [
                'status'  => 'blocked',
                'reason'  => 'Dependencies exist. Force delete blocked.',
                'summary' => $summary,
            ];
        }

        return DB::transaction(function () use ($entity, $id, $summary) {
            app()->instance('deletion_context', true); // SET CONTEXT
            try {
                $model  = $this->resolveModel($entity);
                $record = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model)) 
                    ? $model::withTrashed()->findOrFail($id) 
                    : $model::findOrFail($id);

                if ($entity === 'service') {
                    DB::table('catalog_versions')->where('entity_type', 'clinical')->where('entity_id', $id)->delete();
                    if (method_exists($record, 'catalogVersions')) $record->catalogVersions()->delete(); 
                }
                if ($entity === 'medicine') {
                    DB::table('catalog_versions')->where('entity_type', 'pharmacy')->where('entity_id', $id)->delete();
                    if (method_exists($record, 'catalogVersions')) $record->catalogVersions()->delete(); 
                }

                if ($entity === 'plan') {
                    DB::table('specialties')->where('default_plan_id', $id)->update(['default_plan_id' => null]);
                }

                // CLEANUP STAFF IF DOCTOR IS PURGED
                if ($entity === 'doctor') {
                    User::withTrashed()->where('doctor_id', $id)->each(fn($u) => $u->forceDelete());
                }

                $record->forceDelete();

                $this->log('force_delete', $entity, $id, ['name' => $summary['entity_name']]);

                return ['status' => 'deleted', 'entity' => $entity, 'id' => $id];
            } finally {
                app()->forgetInstance('deletion_context'); // CLEAR CONTEXT
            }
        });
}

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE CLEANUP HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function cleanupInvoice(Invoice $invoice): void
    {
        // 1. Stock Reversal (if not already reverted)
        $items = $invoice->items()->where('type', 'Medicine')->get();
        foreach ($items as $item) {
            $allocations = \App\Models\InvoiceItemBatchAllocation::where('invoice_item_id', $item->id)->get();
            foreach ($allocations as $alloc) {
                $batch = \App\Models\InventoryBatch::find($alloc->inventory_batch_id);
                if ($batch) {
                    $batch->increment('quantity_remaining', $alloc->quantity_taken);
                    $inv = Inventory::find($batch->inventory_id);
                    if ($inv) $inv->increment('stock', $alloc->quantity_taken);
                }
            }
        }

        // 2. Unlink treatments
        Treatment::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);

        // 3. Ledger Record
        \App\Models\LedgerEntry::record($invoice, 'cancellation', $invoice->total_amount, 'credit', ['detail' => 'Draft deleted']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CASCADE LOGIC
    // ─────────────────────────────────────────────────────────────────────────

    public function forceDeleteCascade(string $entity, int $id): array
    {
        if ($entity !== 'specialty') throw new \InvalidArgumentException("Cascade only for specialty.");
        
        $specialty = Specialty::withTrashed()->findOrFail($id);
        $counts = [];

        return DB::transaction(function () use ($specialty, $id, &$counts) {
            app()->instance('deletion_context', true); 
            try {
                $doctorIds = User::withTrashed()->where('specialty_id', $id)->pluck('id')->toArray();
                
                // 1. Data linked to Doctors
                $counts['invoices'] = DB::table('invoices')->whereIn('doctor_id', $doctorIds)->count();
                DB::table('invoices')->whereIn('doctor_id', $doctorIds)->delete();

                $counts['expenses'] = DB::table('expenses')->whereIn('doctor_id', $doctorIds)->count();
                DB::table('expenses')->whereIn('doctor_id', $doctorIds)->delete();

                $counts['appointments'] = DB::table('appointments')->whereIn('doctor_id', $doctorIds)->count();
                DB::table('appointments')->whereIn('doctor_id', $doctorIds)->delete();

                $counts['ledger_entries'] = DB::table('ledger_entries')->whereIn('doctor_id', $doctorIds)->count();
                DB::table('ledger_entries')->whereIn('doctor_id', $doctorIds)->delete();

                $counts['patients'] = Patient::withTrashed()->whereIn('doctor_id', $doctorIds)->count();
                Patient::withTrashed()->whereIn('doctor_id', $doctorIds)->each(fn($p) => $p->forceDelete());

                $counts['inventory'] = Inventory::withTrashed()->whereIn('doctor_id', $doctorIds)->count();
                Inventory::withTrashed()->whereIn('doctor_id', $doctorIds)->each(fn($i) => $i->forceDelete());

                DB::table('doctor_service_settings')->whereIn('user_id', $doctorIds)->delete();
                DB::table('local_services')->whereIn('doctor_id', $doctorIds)->delete();
                DB::table('local_medicines')->whereIn('doctor_id', $doctorIds)->delete();

                // 2. Specialty Master Data
                $counts['clinical_items'] = ClinicalCatalog::withTrashed()->where('specialty_id', $id)->count();
                ClinicalCatalog::withTrashed()->where('specialty_id', $id)->each(fn($c) => $c->forceDelete());

                $counts['clinical_categories'] = ClinicalServiceCategory::withTrashed()->where('specialty_id', $id)->count();
                ClinicalServiceCategory::withTrashed()->where('specialty_id', $id)->each(fn($c) => $c->forceDelete());

                $counts['master_medicines'] = MasterMedicine::withTrashed()->where('specialty_id', $id)->count();
                MasterMedicine::withTrashed()->where('specialty_id', $id)->each(fn($m) => $m->forceDelete());

                $counts['pharmacy_categories'] = PharmacyCategory::where('specialty_id', $id)->count();
                PharmacyCategory::where('specialty_id', $id)->each(fn($c) => $c->delete());

                $counts['plans'] = SubscriptionPlan::where('specialty_id', $id)->count();
                SubscriptionPlan::where('specialty_id', $id)->each(fn($p) => $p->delete()); 

                $counts['doctors'] = User::withTrashed()->where('specialty_id', $id)->count();
                User::withTrashed()->where('specialty_id', $id)->each(fn($u) => $u->forceDelete());

                $specialty->forceDelete();

                app(ModuleCacheService::class)->bumpSchemaVersion();
                $this->log('cascade_delete', 'specialty', $id);

                return [
                    'status' => 'cascade_deleted', 
                    'entity' => 'specialty', 
                    'id' => $id, 
                    'counts' => $counts
                ];
            } finally {
                app()->forgetInstance('deletion_context');
            }
        });
    }

    public function cascadePreview(string $entity, int $id): array
    {
        if ($entity === 'service') {
            $local_services_count = DB::table('local_services')->where('promoted_catalog_id', $id)->count();
            return [
                'entity' => 'service',
                'id' => $id,
                'will_delete' => ['local_services_count' => $local_services_count],
                'total_rows' => $local_services_count
            ];
        }

        if ($entity === 'medicine') {
            $local_medicines_count = DB::table('local_medicines')->where('promoted_master_id', $id)->count();
            return [
                'entity' => 'medicine',
                'id' => $id,
                'will_delete' => ['local_medicines_count' => $local_medicines_count],
                'total_rows' => $local_medicines_count
            ];
        }

        if ($entity !== 'specialty') return [];
        
        $doctorIds = User::withTrashed()->where('specialty_id', $id)->pluck('id');
        
        $counts = [
            'doctors'               => $doctorIds->count(),
            'patients'              => Patient::withTrashed()->whereIn('doctor_id', $doctorIds)->count(),
            'plans'                 => SubscriptionPlan::where('specialty_id', $id)->count(),
            'clinical_items'        => ClinicalCatalog::withTrashed()->where('specialty_id', $id)->count(),
            'clinical_categories'   => ClinicalServiceCategory::withTrashed()->where('specialty_id', $id)->count(),
            'master_medicines'      => MasterMedicine::withTrashed()->where('specialty_id', $id)->count(),
            'pharmacy_categories'   => PharmacyCategory::where('specialty_id', $id)->count(),
            'invoices'              => DB::table('invoices')->whereIn('doctor_id', $doctorIds)->count(),
            'expenses'              => DB::table('expenses')->whereIn('doctor_id', $doctorIds)->count(),
            'appointments'          => DB::table('appointments')->whereIn('doctor_id', $doctorIds)->count(),
            'inventory'             => DB::table('inventory')->whereIn('doctor_id', $doctorIds)->count(),
            'ledger_entries'        => DB::table('ledger_entries')->whereIn('doctor_id', $doctorIds)->count(),
        ];

        return [
            'entity'     => 'specialty',
            'id'         => $id,
            'will_delete' => $counts,
            'total_rows'  => array_sum($counts)
        ];
    }

    private function planDependencySummary(int $id): array
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return [
            'active_doctors'  => User::where('plan_id', $id)->count(),
            'specialty_plans' => SubscriptionPlan::where('specialty_id', $plan->specialty_id)->count()
        ];
    }

    private function log(string $action, string $entity, int $id, array $extra = []): void
    {
        $payload = array_merge(['action' => $action, 'entity' => $entity, 'entity_id' => $id, 'by' => Auth::id()], $extra);
        Log::info("[DeleteManager] {$action} {$entity} #{$id}", $payload);
        if (class_exists(AuditLog::class) && method_exists(AuditLog::class, 'log')) {
            AuditLog::log("delete_{$action}", ucfirst($action) . " {$entity} #{$id}", $payload);
        }
    }
}
