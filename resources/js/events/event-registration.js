/**
 * Event Registration Form with Real-time Validation
 */

$(document).ready(function () {
    const form = $('#registrationForm');
    const submitBtn = $('#submitBtn');
    const eventId = $('#event_id').val();
    let isValidating = {};
    let fieldValidationStatus = {};

    // Initialize validation for readonly fields as valid
    $('[readonly], [disabled]').each(function () {
        const fieldName = $(this).attr('name');
        if ($(this).val()) {
            fieldValidationStatus[fieldName] = true;
        }
    });

    // AJAX field validation with debounce
    let validationTimeout;

    $('[data-validate="true"]').on('input', function () {
        const field = $(this);
        const fieldName = field.attr('name');
        const raw = field.val();
        let value = raw.trim();

        // 把去空格后的值写回 input
        if (raw !== value) {
            field.val(value);
        }

        // Skip validation for readonly/disabled fields
        if (field.prop('readonly') || field.prop('disabled')) {
            return;
        }

        clearTimeout(validationTimeout);

        // Reset validation state
        field.removeClass('is-invalid is-valid');
        field.closest('.form-group').removeClass('field-validating');

        if (value === '') {
            fieldValidationStatus[fieldName] = false;
            updateSubmitButton();
            return;
        }

        // Special handling for phone fields
        if (field.attr('type') === 'tel') {
            // Client-side phone validation first
            if (typeof PhoneValidator !== 'undefined') {
                const phoneError = PhoneValidator.getValidationError(value);
                if (phoneError) {
                    console.log('phone error', phoneError);
                    field.addClass('is-invalid');
                    field.closest('.form-group').find('.invalid-feedback span').text(phoneError);
                    fieldValidationStatus[fieldName] = false;
                    updateSubmitButton();
                    return;
                }

                // Format for server validation
                const formatted = PhoneValidator.formatForStorage(value);
                if (formatted) {
                    value = formatted;
                }
            }
        }

        // Show validating state
        field.closest('.form-group').addClass('field-validating');
        isValidating[fieldName] = true;

        validationTimeout = setTimeout(function () {
            $.ajax({
                url: '/events/register/validate-field',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    field: fieldName,
                    value: value,
                    event_id: eventId
                },
                success: function (response) {
                    field.closest('.form-group').removeClass('field-validating');
                    isValidating[fieldName] = false;

                    if (response.valid) {
                        field.removeClass('is-invalid').addClass('is-valid');
                        fieldValidationStatus[fieldName] = true;
                    } else {
                        field.removeClass('is-valid').addClass('is-invalid');
                        field.closest('.form-group').find('.invalid-feedback span').text(response.message || 'Invalid input');
                        fieldValidationStatus[fieldName] = false;
                    }
                    updateSubmitButton();
                },
                error: function () {
                    field.closest('.form-group').removeClass('field-validating');
                    isValidating[fieldName] = false;
                    field.removeClass('is-valid is-invalid');
                    fieldValidationStatus[fieldName] = false;
                    updateSubmitButton();
                }
            });
        }, 500);
    });

    // Phone number formatting as user types
    $('input[type="tel"]').on('input', function () {
        const field = $(this);
        let value = field.val();

        // Only allow digits, +, -, (, ), and spaces
        value = value.replace(/[^\d\+\-\(\)\s]/g, '');
        field.val(value);
    });

    // Client-side validation for textareas and selects
    $('textarea, select').not('[data-validate="true"]').on('input change', function () {
        const field = $(this);
        const fieldName = field.attr('name');
        const isRequired = field.prop('required');

        field.removeClass('is-invalid is-valid');

        if (isRequired && field.val().trim() === '') {
            fieldValidationStatus[fieldName] = false;
            field.addClass('is-invalid');
            field.siblings('.invalid-feedback').find('span').text('This field is required');
        } else if (field.val().trim() !== '') {
            fieldValidationStatus[fieldName] = true;
            field.addClass('is-valid');
        } else {
            fieldValidationStatus[fieldName] = !isRequired;
        }

        updateSubmitButton();
    });

    // Radio button validation
    $('input[type="radio"]').on('change', function () {
        const name = $(this).attr('name');
        const isChecked = $(`input[name="${name}"]:checked`).length > 0;
        fieldValidationStatus[name] = isChecked;
        updateSubmitButton();
    });

    // Checkbox validation
    $('input[type="checkbox"]').on('change', function () {
        const name = $(this).attr('name');

        if (name.endsWith('[]')) {
            const baseName = name.replace('[]', '');
            const checked = $(`input[name="${name}"]:checked`).length;
            fieldValidationStatus[baseName] = checked > 0;
        } else {
            fieldValidationStatus[name] = $(this).is(':checked');
        }

        updateSubmitButton();
    });

    // Terms checkbox
    $('#terms_accepted').on('change', function () {
        fieldValidationStatus['terms_accepted'] = $(this).is(':checked');
        updateSubmitButton();
    });

    // Form submission
    form.on('submit', function (e) {
        // 先统一 trim 所有文本/电话字段
        form.find('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea')
                .each(function () {
                    const $f = $(this);
                    $f.val($f.val().trim());
                });

        // Check if validating
        if (Object.values(isValidating).some(v => v === true)) {
            e.preventDefault();
            alert('Please wait for field validation to complete.');
            return false;
        }

        // Check all required fields
        if (!checkAllRequiredFields()) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
            scrollToFirstInvalid();
            return false;
        }

        // Format phone numbers before submission
        if (typeof PhoneValidator !== 'undefined') {
            $('input[type="tel"]').each(function () {
                const formatted = PhoneValidator.formatForStorage($(this).val());
                if (formatted) {
                    $(this).val(formatted);
                }
            });
        }

        // Show loading
        submitBtn.addClass('loading').prop('disabled', true);
        return true;
    });

    // Update submit button state
    function updateSubmitButton() {
        const hasValidatingFields = Object.values(isValidating).some(v => v === true);
        const allRequiredFieldsValid = checkAllRequiredFields();

        submitBtn.prop('disabled', hasValidatingFields || !allRequiredFieldsValid);
    }

    // Check all required fields
    function checkAllRequiredFields() {
        let allValid = true;

        $('[required]').each(function () {
            const fieldName = $(this).attr('name');
            const value = $(this).val();

            // Readonly/disabled fields with values are valid
            if (($(this).prop('readonly') || $(this).prop('disabled')) && value) {
                fieldValidationStatus[fieldName] = true;
                return true;
            }

            if ($(this).attr('type') === 'checkbox') {
                if (!$(this).is(':checked')) {
                    allValid = false;
                    fieldValidationStatus[fieldName] = false;
                }
            } else if ($(this).attr('type') === 'radio') {
                const name = $(this).attr('name');
                if ($(`input[name="${name}"]:checked`).length === 0) {
                    allValid = false;
                    fieldValidationStatus[name] = false;
                }
            } else {
                if (!value || value.trim() === '') {
                    allValid = false;
                    fieldValidationStatus[fieldName] = false;
                } else if (fieldValidationStatus[fieldName] === false) {
                    allValid = false;
                }
            }
        });

        return allValid;
    }

    // Scroll to first invalid field
    function scrollToFirstInvalid() {
        const firstInvalid = $('.is-invalid, [required]').filter(function () {
            const val = $(this).val();
            return !val || val.trim() === '';
        }).first();

        if (firstInvalid.length) {
            $('html, body').animate({
                scrollTop: firstInvalid.offset().top - 100
            }, 500);
            firstInvalid.focus();
        }
    }

    // Character counter for textareas
    $('textarea[maxlength]').each(function () {
        const maxLength = $(this).attr('maxlength');
        const counter = $('<small class="form-text text-end d-block mt-2"></small>');
        $(this).after(counter);

        const updateCounter = () => {
            const remaining = maxLength - $(this).val().length;
            counter.text(`${remaining} characters remaining`);
            counter.toggleClass('text-warning', remaining < 20);
        };

        updateCounter();
        $(this).on('input', updateCounter);
    });

    // Scroll to first error on page load
    if ($('.is-invalid').length > 0) {
        setTimeout(scrollToFirstInvalid, 300);
    }

    // Prevent form resubmission
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Initialize validation for prefilled fields
    $('[data-validate="true"]').each(function () {
        const field = $(this);
        const value = field.val().trim();

        if (value !== '' && !field.prop('readonly') && !field.prop('disabled')) {
            field.trigger('input');
        }
    });

    // Handle dependent fields
    $('[data-depends-on]').each(function () {
        const $dependentField = $(this);
        const dependsOn = $dependentField.data('depends-on');
        const dependsValue = $dependentField.data('depends-value');
        const $parentField = $(`[name="${dependsOn}"]`);

        const checkDependency = () => {
            const parentValue = $parentField.val();
            if (parentValue === dependsValue) {
                $dependentField.closest('.form-group').show();
            } else {
                $dependentField.closest('.form-group').hide();
                $dependentField.val('').removeClass('is-valid is-invalid');
            }
        };

        $parentField.on('change', checkDependency);
        checkDependency();
    });

    // Initial button state
    updateSubmitButton();

    console.log('Event Registration Form initialized');
});