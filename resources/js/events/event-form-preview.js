/**
 * Event Form Preview & Enhanced UX
 * Real-time preview and interactive features
 */

let eventFormPreviewInitialized = false;

/**
 * 显示 poster 错误（全局函数）
 */
window.showPosterError = function (message) {
    const $poster = $('#poster');

    // 标红 input
    $poster.removeClass('is-valid').addClass('is-invalid');

    // 直接找 section-media 下的 .invalid-feedback
    const $section = $('#section-media');
    const $feedback = $section.find('.invalid-feedback').first();

    if ($feedback.length) {
        $feedback.text(message).show();
    } else {
        console.warn('⚠️ .invalid-feedback not found in #section-media');
    }
}

/**
 * 清除 poster 错误（全局函数）
 */
window.clearPosterError = function () {
    const $poster = $('#poster');

    $poster.removeClass('is-invalid').addClass('is-valid');

    // 直接找 section-media 下的 .invalid-feedback
    const $section = $('#section-media');
    const $feedback = $section.find('.invalid-feedback').first();

    if ($feedback.length) {
        $feedback.hide();
    }
}

/**
 * 更新右侧大图预览（全局函数）
 */
window.updateSidebarPreview = function (file) {
    const $previewPoster = $('#previewPoster');

    const reader = new FileReader();
    reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
            if (img.width < 640 || img.height < 360) {
                console.warn('Image dimensions should be at least 640x360 pixels');
            }

            // 更新右侧预览
            $previewPoster.html(`<img src="${e.target.result}" alt="Event Poster">`);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
};

/**
 * 更新表单内小图预览（全局函数）
 */
window.updateInlinePreview = function (file) {
    const $uploadPlaceholder = $('#uploadPlaceholder');
    const $posterPreview = $('#poster-preview');
    const $posterPreviewImg = $('#poster-preview-img');

    const reader = new FileReader();
    reader.onload = function (e) {
        $posterPreviewImg.attr('src', e.target.result);
        $posterPreview.show();

        // 隐藏 placeholder 文字
        $uploadPlaceholder.hide();
    };
    reader.readAsDataURL(file);
};

/**
 * 恢复原始海报（全局函数）
 */
window.restoreOriginalPoster = function () {
    const existingPoster = $('#existing-poster-path').val();
    const $posterInput = $('#poster');
    const $uploadPreview = $('#uploadPreview');
    const $uploadPlaceholder = $('#uploadPlaceholder');
    const $uploadArea = $('#uploadArea');
    const $previewPoster = $('#previewPoster');
    const $posterPreview = $('#poster-preview');

    if (!existingPoster) {
        window.clearAllPosterPreview();
        return;
    }

    // 清空 file input
    $posterInput.val('');
    const dt = new DataTransfer();
    $posterInput[0].files = dt.files;

    // 显示 placeholder，隐藏小预览
    $uploadPlaceholder.show();
    $posterPreview.hide();
    $uploadArea.css('border-style', 'dashed');

    // 清除验证状态
    clearPosterError();

    // 恢复右侧预览为原图
    const originalUrl = '/storage/event-posters/' + existingPoster;
    $previewPoster.html(`<img src="${originalUrl}" alt="Event Poster">`);

    // 隐藏表单内小预览
    $('#poster-preview-img').attr('src', '');
};

/**
 * 清空所有预览（全局函数）
 */
window.clearAllPosterPreview = function () {
    const $posterInput = $('#poster');
    const $uploadPlaceholder = $('#uploadPlaceholder');
    const $posterPreview = $('#poster-preview');
    const $uploadArea = $('#uploadArea');
    const $previewPoster = $('#previewPoster');

    // 清 file input
    $posterInput.val('');
    const dt = new DataTransfer();
    $posterInput[0].files = dt.files;

    // 显示 placeholder，隐藏小预览
    $uploadPlaceholder.show();
    $posterPreview.hide();
    $uploadArea.css('border-style', 'dashed');

    // 清除验证状态
    clearPosterError();

    // 右侧卡片预览
    $previewPoster.html(`
        <div class="preview-poster-placeholder">
            <i class="bi bi-image"></i>
            <span>No poster yet</span>
        </div>
    `);

    // 表单内小预览
    $('#poster-preview-img').attr('src', '');
};

function initEventFormPreview() {
    if (eventFormPreviewInitialized)
        return;
    eventFormPreviewInitialized = true;

    const $form = $('#eventForm');
    const $uploadArea = $('#uploadArea');
    const $posterInput = $('#poster');
    const $removeImageBtn = $('#posterInlineRemove');

    // Preview elements
    const $previewTitle = $('#previewTitle');
    const $previewDate = $('#previewDate');
    const $previewVenue = $('#previewVenue');
    const $previewCategory = $('#previewCategory');
    const $description = $('#description');
    const $charCount = $('#char-count');

    // 移除所有现有的事件处理程序，防止重复绑定
    $uploadArea.off('click.upload drop dragover dragleave');
    $removeImageBtn.off('click');

    initializePreview();
    initializeUpload();
    initializeSectionScroll();
    
    $form.on('input change', 'input, textarea, select', function () {
        trackFormCompletion();
    });

    // 初始跑一次，渲染现有状态（edit 时已有值）
    trackFormCompletion();

    function initializePreview() {
        $('#title').on('input', function () {
            const value = $(this).val() || 'Event Title';
            $previewTitle.text(value);
        });

        $('#category').on('change', function () {
            const value = $(this).val() || 'Category';
            $previewCategory.text(value);
        });

        $('#venue').on('input', function () {
            const value = $(this).val() || 'Venue not set';
            $previewVenue.text(value);
        });

        $('#start_time').on('change', function () {
            const value = $(this).val();
            if (value) {
                const date = new Date(value);
                const formatted = date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                $previewDate.text(formatted);
            } else {
                $previewDate.text('Date not set');
            }
        });

        $description.on('input', function () {
            const length = $(this).val().length;
            $charCount.text(length);

            if (length < 20) {
                $charCount.css('color', 'var(--error)');
            } else if (length > 450) {
                $charCount.css('color', 'var(--warning)');
            } else {
                $charCount.css('color', 'var(--success)');
            }
        });
    }

    function initializeUpload() {
        // Drag & drop 逻辑
        $uploadArea
                .off('dragover.upload dragleave.upload drop.upload')
                .on('dragover.upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('dragover');
                })
                .on('dragleave.upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('dragover');
                })
                .on('drop.upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('dragover');

                    const files = e.originalEvent.dataTransfer.files;
                    if (!files.length)
                        return;

                    const file = files[0];

                    // 校验文件
                    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                    if (!validTypes.includes(file.type)) {
                        showPosterError('Please select a valid image file (JPEG, PNG, JPG, WEBP)');
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        showPosterError('Image size must not exceed 5MB');
                        return;
                    }

                    // 把 drop 的文件塞回 file input
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    $posterInput[0].files = dt.files;

                    // 清除错误 + 更新预览
                    clearPosterError();
                    window.updateSidebarPreview(file);
                    window.updateInlinePreview(file);
                });

        // 移除按钮逻辑
        $removeImageBtn.off('click.upload').on('click.upload', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const isEditMode = $('#eventForm').data('event-id') !== undefined;
            const existingPoster = $('#existing-poster-path').val();

            if (isEditMode && existingPoster) {
                window.restoreOriginalPoster();
            } else {
                window.clearAllPosterPreview();
            }
            
            trackFormCompletion();
        });
    }

    function initializeSectionScroll() {
        const sections = $('.form-section');
        const progressSteps = $('.progress-step');

        $(window).on('scroll', function () {
            let currentSection = '';

            sections.each(function () {
                const sectionTop = $(this).offset().top;
                const sectionHeight = $(this).outerHeight();
                const scrollPosition = $(window).scrollTop() + 200;

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    const id = $(this).attr('id');
                    if (id) {
                        currentSection = id.replace('section-', '');
                    }
                }
            });

            if (currentSection) {
                progressSteps.removeClass('active');
                $(`.progress-step[data-section="${currentSection}"]`).addClass('active');
            }
        });

        progressSteps.on('click', function () {
            const section = $(this).data('section');
            const $targetSection = $(`#section-${section}`);

            if ($targetSection.length) {
                $('html, body').animate({
                    scrollTop: $targetSection.offset().top - 100
                }, 600);
            }
        });
    }

    function trackFormCompletion() {
        const sections = {
            basic: ['title', 'category', 'description'],
            datetime: ['start_time', 'end_time', 'registration_start_time', 'registration_end_time'],
            location: ['venue'],
            registration: ['contact_email'],
            media: [] // 特殊处理
        };

        Object.keys(sections).forEach(section => {
            const fields = sections[section];
            let completed = true;

            // 特殊处理 media section
            if (section === 'media') {
                const hasPoster = $('#poster')[0].files.length > 0 || $('#existing-poster-path').val();
                completed = hasPoster;
            } else {
                fields.forEach(field => {
                    const $field = $(`#${field}`);
                    if ($field.prop('required') && !$field.val()) {
                        completed = false;
                    }
                });
            }

            const $step = $(`.progress-step[data-section="${section}"]`);
            if (completed) {
                $step.addClass('completed');
            } else {
                $step.removeClass('completed');
            }
        });
    }
}

// DOM 就绪时只调用一次
$(document).ready(function () {
    console.log('DOM ready, initializing event form preview');
    initEventFormPreview();
});