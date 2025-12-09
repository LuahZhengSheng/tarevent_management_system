/**
 * Event Form Validation with AJAX
 * Real-time validation and secure form submission
 */

// 在文件开头添加
const isEditMode = $('#eventForm').data('event-id') !== undefined;
const eventStage = $('#eventForm').data('event-stage');

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
        // 设置 start_time min = now（仅在 create mode 或允许修改时间的 stage）
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

        if (!isEditMode) {
            // Create mode: 设置默认值
            $('#start_time').attr('min', now.toISOString().slice(0, 16));
            $('#registration_start_time').val(now.toISOString().slice(0, 16));
        } else {
            // Edit mode: 只设置 min 属性，不改变现有值
            if (eventStage === 'draft' || eventStage === 'before-registration') {
                $('#start_time').attr('min', now.toISOString().slice(0, 16));
            }
            // 不要设置 registration_start_time 的值，保持 Blade 模板中的值
        }

        setupEventListeners();
        setupPosterPreview();
        setupTagsInput();
        setupFeeToggle();
        setupDateValidation();
        setupCustomFieldsBuilder();
    }

    function setupEventListeners() {
        const $textInputs = $form.find(
                'input[type="text"]:not([readonly]), ' +
                'input[type="email"]:not([readonly]), ' +
                'input[type="tel"]:not([readonly]), ' +
                'input[type="url"]:not([readonly]), ' +
                'textarea:not([readonly])'
                );

        $textInputs.on('blur', function () {
            validateField($(this));
        });

        $textInputs.on('input', function () {
            const name = this.name;
            if (validationTimeouts[name])
                clearTimeout(validationTimeouts[name]);
            validationTimeouts[name] = setTimeout(() => {
                validateField($(this));
            }, 500);
        });

        const $dateInputs = $form.find('input[type="datetime-local"]:not([readonly])');
        $dateInputs.on('change', function () {
            validateField($(this));
            validateDateLogic();
        });

        const $numberInputs = $form.find('input[type="number"]:not([readonly])');
        $numberInputs.on('blur', function () {
            const $field = $(this);
            if ($field.attr('id') === 'max_participants') {
                validateMaxParticipants();
            } else {
                validateField($field);
            }
        });

        // Phone 输入时格式限制
        $('input[type="tel"]:not([readonly])').on('input', function () {
            const field = $(this);
            let value = field.val();
            value = value.replace(/[^\d\+\-\(\)\s]/g, '');
            field.val(value);
        });

        $form.on('submit', handleFormSubmit);
    }

    function setupPosterPreview() {
        $posterInput.off('change.upload').on('change.upload', function (e) {
            const file = e.target.files[0];

            // 如果没有文件（用户取消选择），不做任何处理
            if (!file) {
                return;
            }

            // 校验文件
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                if (typeof showPosterError === 'function') {
                    showPosterError('Please select a valid image file (JPEG, PNG, JPG, WEBP)');
                }
                // 清空 input
                $posterInput.val('');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                if (typeof showPosterError === 'function') {
                    showPosterError('Image size must not exceed 5MB');
                }
                // 清空 input
                $posterInput.val('');
                return;
            }

            // 文件合法：清除错误 + 更新预览
            if (typeof clearPosterError === 'function') {
                clearPosterError();
            }

            // 调用全局函数更新预览
            if (typeof window.updateSidebarPreview === 'function') {
                window.updateSidebarPreview(file);
            }
            if (typeof window.updateInlinePreview === 'function') {
                window.updateInlinePreview(file);
            }
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

        // ====== EDIT MODE 特殊处理 ======
        if (isEditMode) {
            // 如果是 registration stage，registration_end_time 只能延长
            if (eventStage === 'registration') {
                const currentRegEnd = $regEnd.val();
                if (currentRegEnd) {
                    // 设置最小值为当前的 registration_end_time
                    $regEnd.attr('min', currentRegEnd);

                    // 同时设置最大值为 start_time
                    const startVal = $startTime.val();
                    if (startVal) {
                        $regEnd.attr('max', startVal);
                    }
                }
            }

            // 如果字段是 readonly，不需要设置监听器
            if ($startTime.prop('readonly')) {
                return; // 直接返回，不设置任何监听
            }
        }

        // ====== 当 event start 改变时 ======
        $startTime.on('change', function () {
            const startVal = $(this).val();
            if (!startVal)
                return;

            // 1. 结束时间不得早于开始
            $endTime.attr('min', startVal);

            // 2. 报名开始/截止不得晚于 event start
            $regStart.attr('max', startVal);
            $regEnd.attr('max', startVal);

            // 3. 报名截止不得早于报名开始（如果已有 regStart）
            const regStartVal = $regStart.val();
            if (regStartVal) {
                $regEnd.attr('min', regStartVal);
            }

            // 4. 如果现有 regEnd 超过了新的 startTime，清空并清除错误
            if ($regEnd.val() && $regEnd.val() > startVal) {
                $regEnd.val('');
                clearFieldValidation($regEnd);
            }

            // 5. 如果 regStart 比 startTime 还晚，也清空并清除错误
            if ($regStart.val() && $regStart.val() > startVal) {
                $regStart.val('');
                clearFieldValidation($regStart);
            }
        });

        // ====== 当 registration start 改变时 ======
        $regStart.on('change', function () {
            const regStartVal = $(this).val();
            if (!regStartVal)
                return;

            // 报名截止不得早于报名开始
            $regEnd.attr('min', regStartVal);

            // 如果在 edit mode 且是 registration stage
            if (isEditMode && eventStage === 'registration') {
                const currentRegEnd = $('#registration_end_time').data('original-value');
                if (currentRegEnd && regStartVal > currentRegEnd) {
                    // 报名开始时间不能晚于当前的报名结束时间
                    showFieldError($regStart, 'Cannot change registration start time during registration period');
                    return;
                }
            }

            if ($regEnd.val() && $regEnd.val() < regStartVal) {
                $regEnd.val('');
                clearFieldValidation($regEnd);
            }
        });

        // ====== 当 registration end 改变时 ======
        $regEnd.on('change', function () {
            const regEndVal = $(this).val();
            if (!regEndVal)
                return;

            const regStartVal = $regStart.val();
            const startVal = $startTime.val();

            // 验证：不能早于 registration start
            if (regStartVal && regEndVal < regStartVal) {
                showFieldError($regEnd, 'Registration close time must be after registration open time');
                $(this).val('');
                return;
            }

            // 验证：不能晚于 event start
            if (startVal && regEndVal >= startVal) {
                showFieldError($regEnd, 'Registration must close before event starts');
                $(this).val('');
                return;
            }

            // 如果是 edit mode 且在 registration stage，只能延长
            if (isEditMode && eventStage === 'registration') {
                const originalRegEnd = $(this).data('original-value');
                if (originalRegEnd && regEndVal < originalRegEnd) {
                    showFieldError($regEnd, 'Registration end time can only be extended, not shortened');
                    $(this).val(originalRegEnd);
                    return;
                }
            }

            clearFieldValidation($regEnd);
        });
    }

    function validateMaxParticipants() {
        const $field = $('#max_participants');
        if (!$field.length)
            return true;

        const value = $field.val();
        const min = parseInt($field.attr('min'), 10) || 0;

        // 空值：视为 unlimited，直接通过
        if (!value) {
            clearFieldValidation($field);
            return true;
        }

        const num = parseInt(value, 10);
        if (isNaN(num) || num < min) {
            showFieldError(
                    $field,
                    `Maximum participants cannot be less than ${min}.`
                    );
            return false;
        }

        showFieldSuccess($field);
        return true;
    }

    function setupCustomFieldsBuilder() {
        let customFieldIndex = 0;
        const $customFieldsList = $('#customFieldsList');
        const $addBtn = $('#addCustomFieldBtn');

        $addBtn.on('click', function () {
            const fieldHtml = `
            <div class="custom-field-item" data-index="${customFieldIndex}">
                <div class="custom-field-drag-handle">
                    <i class="bi bi-grip-vertical"></i>
                </div>
                <div class="custom-field-info">
                    <input type="text" class="form-control-modern mb-2" 
                           name="custom_fields[${customFieldIndex}][label]" 
                           placeholder="Field Label (e.g., T-Shirt Size)" required>
                    <input type="text" class="form-control-modern mb-2" 
                           name="custom_fields[${customFieldIndex}][name]" 
                           placeholder="Field Name (e.g., tshirt_size)" required>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select class="form-select-modern" name="custom_fields[${customFieldIndex}][type]" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="select">Select</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control-modern" 
                                   name="custom_fields[${customFieldIndex}][options]" 
                                   placeholder='Options (JSON array, e.g., ["S","M","L","XL"])'>
                        </div>
                    </div>
                    <input type="text" class="form-control-modern mt-2" 
                           name="custom_fields[${customFieldIndex}][placeholder]" 
                           placeholder="Placeholder text">
                    <input type="text" class="form-control-modern mt-2" 
                           name="custom_fields[${customFieldIndex}][help_text]" 
                           placeholder="Help text">
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" 
                               id="custom_required_${customFieldIndex}" 
                               name="custom_fields[${customFieldIndex}][required]" value="1">
                        <label class="form-check-label" for="custom_required_${customFieldIndex}">
                            Required field
                        </label>
                    </div>
                </div>
                <div class="custom-field-actions">
                    <button type="button" class="btn-icon btn-danger remove-field">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;

            $customFieldsList.append(fieldHtml);
            customFieldIndex++;
        });

        $customFieldsList.on('click', '.remove-field', function () {
            $(this).closest('.custom-field-item').remove();
        });
    }

    function validateDateLogic() {
        const startVal = $('#start_time').val();
        const endVal = $('#end_time').val();
        const regStartVal = $('#registration_start_time').val();
        const regEndVal = $('#registration_end_time').val();

        console.log('RAW VALUES:', {startVal, endVal, regStartVal, regEndVal});

        if (!startVal || !endVal || !regStartVal || !regEndVal) {
            console.log('Some values empty -> skip date logic, return true');
            return true; // 这里先返回 true，避免直接把表单判错
        }

        const startTime = new Date(startVal);
        const endTime = new Date(endVal);
        const regStart = new Date(regStartVal);
        const regEnd = new Date(regEndVal);
        const now = new Date();

        console.log('PARSED DATES:', {startTime, endTime, regStart, regEnd, now});

        // 如果有任何 Invalid Date，先不报错，返回 true
        if ([startTime, endTime, regStart, regEnd].some(d => isNaN(d.getTime()))) {
            console.log('At least one Invalid Date -> skip date logic, return true');
            return true;
        }

        let isValid = true;

        if (!(startTime > now)) {
            console.log('FAIL: startTime > now');
            showFieldError($('#start_time'), 'Event must start in the future');
            isValid = false;
        }
        if (!(endTime > startTime)) {
            console.log('FAIL: endTime > startTime');
            showFieldError($('#end_time'), 'End time must be after start time');
            isValid = false;
        }
        if (!(regEnd < startTime)) {
            console.log('FAIL: regEnd < startTime');
            showFieldError($('#registration_end_time'), 'Registration must close before event starts');
            isValid = false;
        }
        if (!(regEnd > regStart)) {
            console.log('FAIL: regEnd > regStart');
            showFieldError($('#registration_end_time'), 'Registration close time must be after open time');
            isValid = false;
        }

        console.log('validateDateLogic result:', isValid);
        return isValid;
    }

    async function validateField($field) {
        const raw = $field.val();
        const value = $.trim(raw);
        const fieldName = $field.attr('name');
        const isRequired = $field.prop('required');

        // 先把 trim 后的值写回 input，这样 HTML5 validity 也用去空格后的值
        if (raw !== value) {
            $field.val(value);
        }

        if (!$field.prop('required') && !value) {
            clearFieldValidation($field);
            return true;
        }

        // Phone: 用 PhoneValidator
        if (fieldName === 'contact_phone') {
            if (typeof PhoneValidator !== 'undefined') {
                const phoneError = PhoneValidator.getValidationError(value);
                if (phoneError) {
                    showFieldError($field, phoneError);
                    return false;
                }

                const formatted = PhoneValidator.formatForStorage(value);
                if (formatted) {
                    // 暂存格式化后的值，提交时用
                    $field.data('formatted-phone', formatted);
                }
            }
            showFieldSuccess($field);
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
                    data: JSON.stringify({field: fieldName, value: value, all: {
                            start_time: $('#start_time').val(),
                            registration_start_time: $('#registration_start_time').val(),
                            registration_end_time: $('#registration_end_time').val(),
                        }}),
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
        const $feedback = $field.siblings('.invalid-feedback');
        if ($feedback.length) {
            $feedback.text(message).show();
        }
    }

    function showFieldSuccess($field) {
        $field.removeClass('is-invalid').addClass('is-valid');
        const $feedback = $field.siblings('.invalid-feedback');
        if ($feedback.length) {
            $feedback.hide();
        }
    }

    function clearFieldValidation($field) {
        $field.removeClass('is-valid is-invalid');
        const $feedback = $field.siblings('.invalid-feedback, .valid-feedback');
        if ($feedback.length) {
            $feedback.hide();
        }
    }

    async function handleFormSubmit(e) {
        e.preventDefault();

        // 1) 提交前先统一 trim 所有文本类字段
        $form.find('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], textarea')
                .each(function () {
                    const $f = $(this);
                    $f.val($.trim($f.val()));
                });

        const $requiredFields = $form.find('[required]');
        let isValid = true;

        for (const el of $requiredFields) {
            const ok = await validateField($(el));
            if (!ok)
                isValid = false;
        }

        if (!validateDateLogic()) {
            isValid = false;
        }

        if (!validateMaxParticipants()) {
            isValid = false;
        }

        if (!isValid) {
            showAlert('Please fix the errors in the form before submitting.', 'danger');
            const $firstError = $form.find('.is-invalid').first();
            if ($firstError.length) {
                $firstError[0].scrollIntoView({behavior: 'smooth', block: 'center'});
            }
            return;
        }

        const submitter = e.originalEvent.submitter;
        const status = submitter ? submitter.value : 'draft';

        if (typeof PhoneValidator !== 'undefined') {
            const $phone = $('#contact_phone');
            const raw = $phone.data('formatted-phone') || $phone.val();
            const formatted = PhoneValidator.formatForStorage(raw);
            if (formatted) {
                $phone.val(formatted);
            }
        }

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

            if (response.ok && data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                // 422 或自定义失败
                if (data.errors) {
                    // 先清掉旧状态
                    $form.find('.is-invalid').each(function () {
                        clearFieldValidation($(this));
                    });

                    // 遍历每个错误字段
                    Object.keys(data.errors).forEach(name => {
                        const messages = data.errors[name];
                        const msg = Array.isArray(messages) ? messages[0] : messages;
                        const $field = $form.find(`[name="${name}"]`);

                        if ($field.length) {
                            showFieldError($field, msg);
                        }
                    });
                }

                showAlert(data.message || 'An error occurred. Please fix the errors and try again.', 'danger');
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
                $(this).html('<i class="bi bi-save me-2"></i>Save Changes');
            } else if (this.id === 'updateBtn') {
                $(this).html('<i class="bi bi-save me-2"></i>Update Event');
            } else {
                // 其它 submit 按钮（如果以后有真正的 publish submit）
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

        $alertContainer[0].scrollIntoView({behavior: 'smooth', block: 'nearest'});
    }
});
