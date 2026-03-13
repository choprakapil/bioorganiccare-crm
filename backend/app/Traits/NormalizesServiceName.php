<?php

namespace App\Traits;

trait NormalizesServiceName
{
    /**
     * Normalize a service name for deduplication comparison.
     *
     * Rules applied (in order):
     *  1. Trim leading/trailing whitespace
     *  2. Collapse all internal whitespace sequences to a single space
     *  3. Convert to lowercase
     *
     * Examples:
     *  " Scaling "   → "scaling"
     *  "scaling"     → "scaling"
     *  "SCALING"     → "scaling"
     *  "Root  Canal" → "root canal"
     *  " ROOT  CANAL " → "root canal"
     */
    protected function normalizeServiceName(string $name): string
    {
        // Step 1: Trim outer whitespace
        $trimmed = trim($name);

        // Step 2: Collapse internal multiple spaces/tabs/newlines to single space
        $collapsed = preg_replace('/\s+/', ' ', $trimmed);

        // Step 3: Lowercase
        return strtolower($collapsed);
    }
}
