/**
 * Event Form Validation with AJAX
 * Real-time validation and secure form submission
 */

$(function () {
    const $form = $('#eventForm');
    const $posterInput = $('#poster');
    const $posterPreview = $('#poster-preview');
    const $posterPreviewImg = $('#poster-preview-img');
    const $tagsInput = $('#tags-input');
    const $tagsContainer = $('#tags-container');
    const $isPaidRadios = $('input[name="is_paid"]');
    const $feeAmountContainer = $('#fee_amount_container');

    let tags = [];
    let validationTimeouts = {};

    init();

    function init() {
        // 设置 start_time min = now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        $('#start_time').attr('min', now.toISOString().slice(0, 16));
        $('#registration_start_time').val(now.toISOString().slice(0, 16));

        setupEventListeners();
        setupPosterPreview();
        setupTagsInput();
        setupFeeToggle();
        setupDateValidation();
    }

    function setupEventListeners() {
        const $textInputs = $form.find('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea');
        $textInputs.on('blur', function () {
            validateField($(this));
        });
        $textInputs.on('input', function () {
            const name = this.name;
            if (validationTimeouts[name]) clearTimeout(validationTimeouts[name]);
            validationTimeouts[name] = setTimeout(() => {
                validateField($(this));
            }, 500);
        });

        const $dateInputs = $form.find('input[type="datetime-local"]');
        $dateInputs.on('change', function () {
            validateField($(this));
            validateDateLogic();
        });

        const $numberInputs = $form.find('input[type="number"]');
        $numberInputs.on('blur', function () {
            validateField($(this));
        });

        $form.on('submit', handleFormSubmit);
    }

    function setupPosterPreview() {
        $posterInput.on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showFieldError($posterInput, 'Please select a valid image file (JPEG, PNG, JPG, WEBP)');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showFieldError($posterInput, 'Image size must not exceed 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (ev) {
                $posterPreviewImg.attr('src', ev.target.result);
                $posterPreview.show();
                showFieldSuccess($posterInput);
            };
            reader.readAsDataURL(file);
        });
    }

    function setupTagsInput() {
        $tagsInput.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tag = $.trim($(this).val());
                if (tag && tags.length < 10 && !tags.includes(tag)) {
                    tags.push(tag);
                    renderTags();
                }
                $(this).val('');
            }
        });

        // 删除 tag（事件委托）
        $tagsContainer.on('click', '.tag-remove', function () {
            const index = $(this).data('index');
            tags.splice(index, 1);
            renderTags();
        });
    }

    function renderTags() {
        $tagsContainer.empty();
        tags.forEach((tag, index) => {
            $tagsContainer.append(
                `<span class="tag-badge">
                    ${tag}
                    <i class="bi bi-x-circle tag-remove" data-index="${index}"></i>
                 </span>`
            );
        });
    }

    function setupFeeToggle() {
        $isPaidRadios.on('change', function () {
            if ($(this).val() === '1') {
                $feeAmountContainer.show();
                $('#fee_amount').prop('required', true);
            } else {
                $feeAmountContainer.hide();
                $('#fee_amount').prop('required', false).val('');
            }
        });
    }

    function setupDateValidation() {
        const $startTime = $('#start_time');
        const $endTime = $('#end_time');
        const $regStart = $('#registration_start_time');
        const $regEnd = $('#registration_end_time');

        $startTime.on('change', function () {
            $endTime.attr('min', $(this).val());
            $regEnd.attr('max', $(this).val());
        });

        $regStart.on('change', function () {
            $regEnd.attr('min', $(this).val());
        });
    }

    function validateDateLogic() {
        const startTime = new Date($('#start_time').val());
        const endTime = new Date($('#end_time').val());
        const regStart = new Date($('#registration_start_time').val());
        const regEnd = new Date($('#registration_end_time').val());
        const now = new Date();

        let isValid = true;

        if (startTime <= now) {
            showFieldError($('#start_time'), 'Event must start in the future');
            isValid = false;
        }
        if (endTime <= startTime) {
            showFieldError($('#end_time'), 'End time must be after start time');
            isValid = false;
        }
        if (regEnd >= startTime) {
            showFieldError($('#registration_end_time'), 'Registration must close before event starts');
            isValid = false;
        }
        if (regEnd <= regStart) {
            showFieldError($('#registration_end_time'), 'Registration close time must be after open time');
            isValid = false;
        }

        return isValid;
    }

    async function validateField($field) {
        const value = $.trim($field.val());
        const fieldName = $field.attr('name');

        if (!$field.prop('required') && !value) {
            clearFieldValidation($field);
            return true;
        }

        if (!$field[0].checkValidity()) {
            showFieldError($field, $field[0].validationMessage);
            return false;
        }

        const ajaxFields = ['title', 'venue', 'start_time', 'end_time', 'registration_start_time', 'registration_end_time'];
        if (ajaxFields.includes(fieldName) && value) {
            try {
                const data = await $.ajax({
                    url: '/events/validate-field',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({ field: fieldName, value: value }),
                    dataType: 'json'
                });

                if (data.valid) {
                    showFieldSuccess($field);
                    return true;
                } else {
                    showFieldError($field, data.message);
                    return false;
                }
            } catch (e) {
                console.error('Validation error:', e);
                return true;
            }
        } else {
            showFieldSuccess($field);
            return true;
        }
    }

    function showFieldError($field, message) {
        $field.removeClass('is-valid').addClass('is-invalid');
        const $feedback = $field.next('.invalid-feedback');
        if ($feedback.length) {
            $feedback.text(message).show();
        }
    }

    function showFieldSuccess($field) {
        $field.removeClass('is-invalid').addClass('is-valid');
        const $feedback = $field.next('.invalid-feedback');
        if ($feedback.length) {
            $feedback.hide();
        }
    }

    function clearFieldValidation($field) {
        $field.removeClass('is-valid is-invalid');
        const $feedback = $field.next('.invalid-feedback, .valid-feedback');
        if ($feedback.length) {
            $feedback.hide();
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        const $requiredFields = $form.find('[required]');
        let isValid = true;

        for (const el of $requiredFields) {
            const ok = await validateField($(el));
            if (!ok) isValid = false;
        }

        if (!validateDateLogic()) {
            isValid = false;
        }

        if (!isValid) {
            showAlert('Please fix the errors in the form before submitting.', 'danger');
            const $firstError = $form.find('.is-invalid').first();
            if ($firstError.length) {
                $firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        const submitter = e.originalEvent.submitter;
        const status = submitter ? submitter.value : 'draft';

        const formData = new FormData($form[0]);
        tags.forEach(tag => formData.append('tags[]', tag));
        formData.set('status', status);

        const $submitButtons = $form.find('button[type="submit"]');
        $submitButtons.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

        try {
            const response = await fetch($form.attr('action') || '/events', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                showAlert(data.message || 'An error occurred. Please try again.', 'danger');
                resetButtons($submitButtons);
            }
        } catch (error) {
            console.error('Submit error:', error);
            showAlert('An unexpected error occurred. Please try again.', 'danger');
            resetButtons($submitButtons);
        }
    }

    function resetButtons($btns) {
        $btns.prop('disabled', false).each(function () {
            if (this.id === 'saveDraftBtn') {
                $(this).html('<i class="bi bi-save me-2"></i>Save as Draft');
            } else {
                $(this).html('<i class="bi bi-send me-2"></i>Publish Event');
            }
        });
    }

    function showAlert(message, type) {
        const $alertContainer = $('#alert-container');
        const $alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $alertContainer.append($alert);

        setTimeout(() => {
            $alert.alert('close');
        }, 5000);

        $alertContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
