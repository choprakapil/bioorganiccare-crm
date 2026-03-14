<?php

namespace App\Enums;

class TreatmentStatus
{
    public const PROPOSED = 'Proposed';
    public const COMPLETED = 'Completed';

    public static function all(): array
    {
        return [
            self::PROPOSED,
            self::COMPLETED,
        ];
    }
}
