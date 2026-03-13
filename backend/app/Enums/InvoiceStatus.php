<?php

namespace App\Enums;

class InvoiceStatus
{
    public const UNPAID = 'Unpaid';
    public const PAID = 'Paid';
    public const PARTIAL = 'Partial';
    public const CANCELLED = 'Cancelled';
    public const REALLOCATION_REQUIRED = 'ReallocationRequired';

    public static function all(): array
    {
        return [
            self::UNPAID,
            self::PAID,
            self::PARTIAL,
            self::CANCELLED,
            self::REALLOCATION_REQUIRED,
        ];
    }
}
