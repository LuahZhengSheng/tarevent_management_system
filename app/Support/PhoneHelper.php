<?php

namespace App\Support;

class PhoneHelper {

    /**
     * Validate and format phone number to storage format (digits only)
     * 
     * @param string $phone
     * @param string $defaultCountryCode Default country code (e.g., '60' for Malaysia)
     * @return string|null Returns formatted phone or null if invalid
     */
    public static function formatForStorage(string $phone, string $defaultCountryCode = '60'): ?string {
        // Remove all non-digit characters
        $digitsOnly = preg_replace('/\D/', '', $phone);

        // If empty after cleaning, return null
        if (empty($digitsOnly)) {
            return null;
        }

        // Handle different formats
        // Case 1: Starts with country code (e.g., 60123456789)
        if (strlen($digitsOnly) >= 10 && str_starts_with($digitsOnly, $defaultCountryCode)) {
            return $digitsOnly;
        }

        // Case 2: Starts with 0 (local format, e.g., 0123456789)
        if (str_starts_with($digitsOnly, '0') && strlen($digitsOnly) >= 9) {
            // Remove leading 0 and add country code
            return $defaultCountryCode . substr($digitsOnly, 1);
        }

        // Case 3: No country code, no leading 0 (e.g., 123456789)
        if (strlen($digitsOnly) >= 8) {
            return $defaultCountryCode . $digitsOnly;
        }

        // Invalid format
        return null;
    }

    /**
     * Format phone number for display (with country code and spacing)
     * 
     * @param string $phone Storage format phone number
     * @param string $countryCode Country code to format (default '60' for Malaysia)
     * @return string Formatted phone for display
     */
    public static function formatForDisplay(string $phone, string $countryCode = '60'): string {
        // 只保留数字
        $digitsOnly = preg_replace('/\D/', '', $phone);

        // 以国家码开头：6017... -> 转成本地 017...
        if (str_starts_with($digitsOnly, $countryCode)) {
            $localNumber = substr($digitsOnly, strlen($countryCode)); // 例如 174529262
            return '0' . $localNumber; // 0174529262
        }

        // 已经是本地 01 开头，直接返回
        if (str_starts_with($digitsOnly, '01')) {
            return $digitsOnly;
        }

        // 其它情况，简单加 0 前缀
        return '0' . $digitsOnly;
    }

    /**
     * Validate phone number format
     * 
     * @param string $phone
     * @param int $minLength Minimum digits required (default 8)
     * @param int $maxLength Maximum digits allowed (default 15)
     * @return bool
     */
    public static function validate(string $phone, int $minLength = 8, int $maxLength = 15): bool {
        // Remove all non-digit characters
        $digitsOnly = preg_replace('/\D/', '', $phone);

        // Check length
        $length = strlen($digitsOnly);
        if ($length < $minLength || $length > $maxLength) {
            return false;
        }

        // Check if it's all digits
        return ctype_digit($digitsOnly);
    }

    /**
     * Get validation error message
     * 
     * @param string $phone
     * @return string|null Error message or null if valid
     */
    public static function getValidationError(string $phone): ?string {
        $raw = trim($phone);

        // 1) required
        $digitsOnly = preg_replace('/\D/', '', $raw);
        if (strlen($digitsOnly) === 0) {
            return 'Phone number is required';
        }

        // 2) 只允许数字、空格、-、()、+
        if (!preg_match('/^[\d\s\-()+]*$/', $raw)) {
            return 'Invalid phone number format';
        }

        // 3) 不允许连续分隔符
        if (preg_match('/[\s\-()]{2,}/', $raw)) {
            return 'Invalid phone number format';
        }

        // 4) 兜底：digitsOnly 必须全为数字
        if (!ctype_digit($digitsOnly)) {
            return 'Invalid phone number format';
        }

        // 5) 统一格式后按马来手机规则验
        if (!self::isMalaysianMobile($phone)) {
            return 'Invalid phone number format';
        }

        return null;
    }

    /**
     * Check if phone is Malaysian mobile number
     * 
     * @param string $phone
     * @return bool
     */
    public static function isMalaysianMobile(string $phone): bool {
        $digitsOnly = preg_replace('/\D/', '', $phone);

        // 用 formatForStorage 统一成 60 开头
        $stored = self::formatForStorage($digitsOnly, '60');
        if (!$stored || !str_starts_with($stored, '60')) {
            return false;
        }

        // 去掉 6 -> 本地号段
        $local = substr($stored, 1); // 例如 0174529262 / 01123456789
        if (!str_starts_with($local, '01')) {
            return false;
        }

        $len = strlen($local);

        // 011: 011 + 8 位 -> 11
        if (str_starts_with($local, '011')) {
            return $len === 11;
        }

        // 其它 01X: 01X + 7 位 -> 10
        return $len === 10;
    }

    /**
     * Get country code from phone number
     * 
     * @param string $phone
     * @return string|null
     */
    public static function extractCountryCode(string $phone): ?string {
        $digitsOnly = preg_replace('/\D/', '', $phone);

        // Common country codes (1-3 digits)
        $commonCodes = ['60', '65', '62', '1', '44', '86', '91'];

        foreach ($commonCodes as $code) {
            if (str_starts_with($digitsOnly, $code)) {
                return $code;
            }
        }

        return null;
    }
}
