<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Ledger Integrity Service
 *
 * Provides validation methods to ensure accounting integrity
 * across ledger entries. Works with transaction_group_uuid to
 * validate that paired financial operations are balanced.
 *
 * DESIGN NOTE: This system uses event-based single-entry ledger recording.
 * - invoice_created → solo debit (receivable established)
 * - payment_applied → solo credit (cash received)
 * - cancellation → solo credit (receivable written off)
 *
 * Balance is achieved over an invoice's LIFETIME, not per individual event.
 * The transaction_group_uuid groups entries that are part of the SAME
 * atomic operation (e.g., paid-at-creation = debit + credit together).
 *
 * validateTransactionGroup() should only be called for groups that are
 * EXPECTED to be balanced (i.e., contain both debits and credits).
 */
class LedgerIntegrityService
{
    /**
     * Validate that a transaction group has balanced debits and credits.
     *
     * Call this for paired operations where debit and credit entries
     * are created together (e.g., paid-at-creation invoices).
     *
     * @throws \RuntimeException if the group is imbalanced
     */
    public static function validateTransactionGroup(string $transactionGroupUuid): void
    {
        $result = DB::selectOne("
            SELECT
                transaction_group_uuid,
                COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) AS debits,
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) AS credits
            FROM ledger_entries
            WHERE transaction_group_uuid = ?
            GROUP BY transaction_group_uuid
        ", [$transactionGroupUuid]);

        if (!$result) {
            return; // No entries found — nothing to validate
        }

        $debits = (float) $result->debits;
        $credits = (float) $result->credits;

        // Float-safe comparison (tolerance of 0.01 for rounding)
        if (abs($debits - $credits) > 0.01) {
            throw new \RuntimeException(
                "Ledger imbalance detected in transaction group [{$transactionGroupUuid}]: "
                . "debits ({$debits}) != credits ({$credits}). "
                . "Difference: " . abs($debits - $credits)
            );
        }
    }

    /**
     * Validate that an invoice's lifetime ledger is balanced.
     *
     * An invoice is considered balanced when:
     *   SUM(debits) = SUM(credits)
     *
     * This is expected only for fully settled invoices (Paid/Cancelled).
     * Unpaid/Partial invoices will naturally have debit > credit.
     *
     * @return array{balanced: bool, debits: float, credits: float, difference: float}
     */
    public static function validateInvoiceBalance(int $invoiceId): array
    {
        $result = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END), 0) AS debits,
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END), 0) AS credits
            FROM ledger_entries
            WHERE invoice_id = ?
        ", [$invoiceId]);

        $debits = (float) ($result->debits ?? 0);
        $credits = (float) ($result->credits ?? 0);
        $diff = abs($debits - $credits);

        return [
            'balanced' => $diff < 0.01,
            'debits' => $debits,
            'credits' => $credits,
            'difference' => round($diff, 2),
        ];
    }

    /**
     * Global audit: Find all transaction groups that have BOTH debits AND credits
     * but are NOT balanced. Single-sided groups are excluded since they represent
     * standalone events (e.g., invoice creation or standalone payment).
     *
     * @return \Illuminate\Support\Collection of imbalanced groups
     */
    public static function findImbalancedGroups(): \Illuminate\Support\Collection
    {
        return collect(DB::select("
            SELECT
                transaction_group_uuid,
                SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END) AS debits,
                SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END) AS credits,
                ABS(SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END) -
                    SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END)) AS difference,
                COUNT(*) AS entry_count
            FROM ledger_entries
            GROUP BY transaction_group_uuid
            HAVING entry_count > 1
               AND ABS(debits - credits) > 0.01
        "));
    }

    /**
     * Global audit: Find all invoices where the ledger totals
     * don't match the cached totals on the invoices table.
     *
     * @return \Illuminate\Support\Collection of mismatched invoices
     */
    public static function findLedgerMismatches(): \Illuminate\Support\Collection
    {
        return collect(DB::select("
            SELECT
                i.id AS invoice_id,
                i.total_amount,
                i.ledger_debit_total AS cached_debit,
                i.ledger_credit_total AS cached_credit,
                COALESCE(SUM(CASE WHEN le.direction = 'debit' THEN le.amount ELSE 0 END), 0) AS actual_debit,
                COALESCE(SUM(CASE WHEN le.direction = 'credit' THEN le.amount ELSE 0 END), 0) AS actual_credit
            FROM invoices i
            LEFT JOIN ledger_entries le ON le.invoice_id = i.id
            GROUP BY i.id, i.total_amount, i.ledger_debit_total, i.ledger_credit_total
            HAVING ABS(cached_debit - actual_debit) > 0.01
                OR ABS(cached_credit - actual_credit) > 0.01
        "));
    }
}
