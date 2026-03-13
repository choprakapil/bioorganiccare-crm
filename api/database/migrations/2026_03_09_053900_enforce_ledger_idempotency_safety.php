<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ONE-TIME DATA MIGRATION
 *
 * 1. Removes duplicate ledger_entries (keeps earliest row per invoice+type+direction)
 * 2. Backfills deterministic idempotency_key for single-occurrence events
 *    so the existing UNIQUE(invoice_id, idempotency_key) constraint prevents future duplicates
 * 3. Makes idempotency_key NOT NULL to close the NULL-bypass loophole permanently
 */
return new class extends Migration
{
    private array $singleOccurrenceEvents = [
        'invoice_created',
        'cancellation',
        'resurrection',
        'reallocation',
    ];

    public function up(): void
    {
        // ── Step 1: Identify and remove duplicates ──────────────────────────────
        $duplicates = DB::select("
            SELECT invoice_id, type, direction, COUNT(*) as cnt
            FROM ledger_entries
            GROUP BY invoice_id, type, direction
            HAVING COUNT(*) > 1
        ");

        if (count($duplicates) > 0) {
            echo "  Found " . count($duplicates) . " duplicate group(s). Cleaning...\n";

            // Delete all but the earliest entry per (invoice_id, type, direction)
            $deleted = DB::affectingStatement("
                DELETE le1 FROM ledger_entries le1
                INNER JOIN ledger_entries le2
                    ON le1.invoice_id = le2.invoice_id
                    AND le1.type = le2.type
                    AND le1.direction = le2.direction
                    AND le1.id > le2.id
            ");

            echo "  → Removed {$deleted} duplicate ledger row(s).\n";

            // Fix the ledger totals on affected invoices
            foreach ($duplicates as $dup) {
                $invoice = DB::table('invoices')->where('id', $dup->invoice_id)->first();
                if (!$invoice) continue;

                $totals = DB::selectOne("
                    SELECT
                        COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) as debit_total,
                        COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) as credit_total
                    FROM ledger_entries
                    WHERE invoice_id = ?
                ", [$dup->invoice_id]);

                DB::table('invoices')->where('id', $dup->invoice_id)->update([
                    'ledger_debit_total' => $totals->debit_total,
                    'ledger_credit_total' => $totals->credit_total,
                ]);

                echo "  → Recalculated ledger totals for invoice #{$dup->invoice_id}"
                   . " (debit: {$totals->debit_total}, credit: {$totals->credit_total})\n";
            }
        } else {
            echo "  ✓ No duplicate ledger entries found.\n";
        }

        // ── Step 2: Backfill idempotency keys for single-occurrence events ──────
        $backfilled = 0;
        foreach ($this->singleOccurrenceEvents as $type) {
            $affected = DB::table('ledger_entries')
                ->where('type', $type)
                ->whereNull('idempotency_key')
                ->get();

            foreach ($affected as $entry) {
                DB::table('ledger_entries')
                    ->where('id', $entry->id)
                    ->update([
                        'idempotency_key' => "evt_{$type}_{$entry->direction}",
                    ]);
                $backfilled++;
            }
        }

        echo "  → Backfilled idempotency keys on {$backfilled} ledger row(s).\n";

        // ── Step 3: Make idempotency_key NOT NULL ──────────────────────────────
        // First, give any remaining NULL rows a UUID fallback
        $remaining = DB::table('ledger_entries')->whereNull('idempotency_key')->count();
        if ($remaining > 0) {
            $rows = DB::table('ledger_entries')->whereNull('idempotency_key')->get();
            foreach ($rows as $row) {
                DB::table('ledger_entries')
                    ->where('id', $row->id)
                    ->update(['idempotency_key' => \Illuminate\Support\Str::uuid()->toString()]);
            }
            echo "  → Assigned UUID keys to {$remaining} remaining row(s).\n";
        }

        // Now alter column to NOT NULL
        DB::statement("ALTER TABLE ledger_entries MODIFY idempotency_key VARCHAR(255) NOT NULL");
        echo "  → idempotency_key is now NOT NULL.\n";

        echo "  ✓ Ledger safety constraint migration complete.\n";
    }

    public function down(): void
    {
        // Revert NOT NULL constraint
        DB::statement("ALTER TABLE ledger_entries MODIFY idempotency_key VARCHAR(255) NULL");
        echo "  ✓ Reverted idempotency_key to nullable.\n";
    }
};
