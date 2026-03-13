<?php

namespace App\Services\Deletions;

interface DeletionHandlerInterface
{
    /**
     * Return a dependency summary for the entity.
     */
    public function summary(int $id): array;
}
