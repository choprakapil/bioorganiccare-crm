<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemResetController extends Controller
{
    public function resetSystemData(Request $request)
    {
        if (app()->environment('production')) {
            return response()->json([
                'error' => 'System reset disabled in production'
            ], 403);
        }

        // Wait, the user has 'admin' or 'superadmin' middleware typically, but validation in method is good.
        if ($request->input('confirm') !== 'RESET') {
            return response()->json([
                'message' => 'Invalid confirmation token.'
            ], 400);
        }

        try {
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $tables = [
                'patients',
                'appointments',
                'treatments',
                'invoices',
                'invoice_items',
                'ledger_entries',
                'inventory',
                'inventory_batches',
                'clinical_catalog',
                'local_services',
                'master_medicines',
                'local_medicines',
                'promotion_requests',
                'doctor_service_settings',
                'personal_access_tokens'
            ];

            foreach ($tables as $table) {
                DB::statement("TRUNCATE TABLE {$table}");
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            try { DB::commit(); } catch (\Throwable $e) {}

            \Illuminate\Support\Facades\Artisan::call('optimize:clear');

            if ($request->input('seed_demo') == true || $request->input('seed_demo') === 'true') {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Factory reset completed successfully.'
            ]);

        } catch (\Throwable $e) {
            try { DB::rollBack(); } catch (\Throwable $err) {}
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return response()->json([
                'message' => 'Failed to reset system data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
