/**
 * Phone Number Validation and Formatting Utility
 * Mirrors server-side PhoneHelper functionality
 * 
 * Usage:
 * - PhoneValidator.validate(phone) - returns true/false
 * - PhoneValidator.getValidationError(phone) - returns error message or null
 * - PhoneValidator.formatForStorage(phone) - returns digits only with country code
 * - PhoneValidator.formatForDisplay(phone) - returns formatted display string
 */

const PhoneValidator = {
    /**
     * Format phone number for storage (digits only with country code)
     */
    formatForStorage: function (phone, defaultCountryCode = '60') {
        // Remove all non-digit characters
        const digitsOnly = phone.replace(/\D/g, '');

        if (!digitsOnly)
            return null;

        // Already has country code
        if (digitsOnly.length >= 10 && digitsOnly.startsWith(defaultCountryCode)) {
            return digitsOnly;
        }

        // Starts with 0 (local format)
        if (digitsOnly.startsWith('0') && digitsOnly.length >= 9) {
            return defaultCountryCode + digitsOnly.substring(1);
        }

        // No country code, no leading 0
        if (digitsOnly.length >= 8) {
            return defaultCountryCode + digitsOnly;
        }

        return null;
    },

    /**
     * Format phone number for display
     */
    formatForDisplay: function (phone, countryCode = '60') {
        const digitsOnly = phone.replace(/\D/g, '');

        // 60 开头：6017... -> 017...
        if (digitsOnly.startsWith(countryCode)) {
            const local = digitsOnly.substring(countryCode.length); // 例如 174529262
            return '0' + local; // 0174529262
        }

        // 已经是本地 01 开头
        if (digitsOnly.startsWith('01')) {
            return digitsOnly;
        }

        // 其它情况，简单加 0 前缀
        return '0' + digitsOnly;
    },

    /**
     * Validate phone number
     */
    validate: function (phone, minLength = 8, maxLength = 15) {
        const digitsOnly = phone.replace(/\D/g, '');
        const length = digitsOnly.length;

        if (length < minLength || length > maxLength) {
            return false;
        }

        return /^\d+$/.test(digitsOnly);
    },

    /**
     * Get validation error message
     */
    getValidationError: function (phone) {
        const raw = phone.trim();

        if (!raw) {
            return 'Phone number is required';
        }

        if (!/^[\d\s\-()+]*$/.test(raw)) {
            return 'Invalid phone number format';
        }

        if (/[\s\-()]{2,}/.test(raw)) {
            return 'Invalid phone number format';
        }

        if (/^[-)]/.test(raw) || /[-(]$/.test(raw)) {
            return 'Invalid phone number format';
        }

        const digitsOnly = raw.replace(/\D/g, '');
        if (!/^\d+$/.test(digitsOnly)) {
            return 'Invalid phone number format';
        }

        // 统一规则：用 isMalaysianMobile 判断
        if (!PhoneValidator.isMalaysianMobile(raw)) {
            return 'Invalid phone number format';
        }

        return null;
    },

    /**
     * Check if Malaysian mobile number
     */
    isMalaysianMobile: function (phone) {
        const digitsOnly = phone.replace(/\D/g, '');

        // 统一成 60 开头
        const stored = PhoneValidator.formatForStorage(digitsOnly, '60');
        if (!stored || !stored.startsWith('60')) {
            return false;
        }

        const local = stored.substring(1); // 去掉 6
        if (!local.startsWith('01')) {
            return false;
        }

        const len = local.length;

        // 011 号段：011 + 8 位 -> 11
        if (local.startsWith('011')) {
            return len === 11;
        }

        // 其它 01X：01X + 7 位 -> 10
        return len === 10;
    },

    /**
     * Format as user types (for input field)
     */
    formatAsTyping: function (value) {
        // Remove all non-digit characters except +
        let cleaned = value.replace(/[^\d+]/g, '');

        // If starts with +, keep it
        if (cleaned.startsWith('+')) {
            const digits = cleaned.substring(1);

            // Format: +60 12-345 6789
            if (digits.startsWith('60') && digits.length > 2) {
                const localPart = digits.substring(2);
                if (localPart.length <= 2) {
                    return `+60 ${localPart}`;
                } else if (localPart.length <= 5) {
                    return `+60 ${localPart.substring(0, 2)}-${localPart.substring(2)}`;
                } else {
                    return `+60 ${localPart.substring(0, 2)}-${localPart.substring(2, 5)} ${localPart.substring(5, 9)}`;
                }
            }

            return cleaned;
        }

        // Format local: 012-345 6789
        if (cleaned.startsWith('0') && cleaned.length > 1) {
            if (cleaned.length <= 3) {
                return cleaned;
            } else if (cleaned.length <= 6) {
                return `${cleaned.substring(0, 3)}-${cleaned.substring(3)}`;
            } else {
                return `${cleaned.substring(0, 3)}-${cleaned.substring(3, 6)} ${cleaned.substring(6, 10)}`;
            }
        }

        return cleaned;
    }
};

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PhoneValidator;
}

// 让浏览器里也有全局 PhoneValidator 可用
if (typeof window !== 'undefined') {
    window.PhoneValidator = PhoneValidator;
}