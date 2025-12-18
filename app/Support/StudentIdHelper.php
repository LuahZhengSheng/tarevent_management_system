<?php

namespace App\Support;

class StudentIdHelper
{
    /**
     * Validate student ID format
     * Format: 2 digits + 3 letters + 5 digits (e.g., 24PMD10293)
     * 
     * @param string $studentId
     * @return bool
     */
    public static function validate(string $studentId): bool
    {
        // Remove any whitespace
        $studentId = trim($studentId);
        
        // Check format: 2 digits + 3 letters + 5 digits
        return preg_match('/^\d{2}[A-Z]{3}\d{5}$/i', $studentId) === 1;
    }

    /**
     * Get validation error message
     * 
     * @param string $studentId
     * @return string|null Error message or null if valid
     */
    public static function getValidationError(string $studentId): ?string
    {
        $studentId = trim($studentId);

        // 1) Required check
        if (empty($studentId)) {
            return 'Student ID is required';
        }

        // 2) Check format: 2 digits + 3 letters + 5 digits
        if (!preg_match('/^\d{2}[A-Z]{3}\d{5}$/i', $studentId)) {
            return 'Student ID must be in the format: 2 digits, 3 letters, 5 digits (e.g., 24PMD10293)';
        }

        return null;
    }

    /**
     * Format student ID to uppercase
     * 
     * @param string $studentId
     * @return string
     */
    public static function format(string $studentId): string
    {
        return strtoupper(trim($studentId));
    }

    /**
     * Check if student ID matches the pattern
     * 
     * @param string $studentId
     * @return bool
     */
    public static function matchesPattern(string $studentId): bool
    {
        return self::validate($studentId);
    }
}

