<?php

namespace App\Service;

use App\Entity\Module;

class ModuleManager
{
    /**
     * Rule 1: Title must have at least 2 characters.
     */
    public function isValidTitle(string $title): bool
    {
        return strlen(trim($title)) >= 2;
    }

    /**
     * Rule 3: Description must be between 10 and 500 characters.
     */
    public function isValidDescription(string $description): bool
    {
        $length = strlen(trim($description));
        return $length >= 10 && $length <= 500;
    }

    /**
     * Rule 2: Module is accessible ONLY IF its scheduledAt date is in the past or null.
     */
    public function isAccessible(?\DateTimeInterface $scheduledAt): bool
    {
        if ($scheduledAt === null) {
            return true;
        }

        $now = new \DateTime();
        return $scheduledAt <= $now;
    }
}
