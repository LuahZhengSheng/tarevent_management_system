<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Services;

class ProgramOptionsProvider
{
    /**
     * Get all available program options
     */
    public function getOptions(): array
    {
        return config('user.programs', []);
    }

    /**
     * Get program name by code
     */
    public function getName(string $code): ?string
    {
        $options = $this->getOptions();
        return $options[$code] ?? null;
    }

    /**
     * Check if program code exists
     */
    public function exists(string $code): bool
    {
        return isset($this->getOptions()[$code]);
    }
}

