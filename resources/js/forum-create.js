// resources/js/forum-create.js

document.addEventListener('DOMContentLoaded', function () {
    // ========================================
    // Global Variables
    // ========================================
    const form = document.getElementById('createPostForm') || document.getElementById('editPostForm');
    const postTitle = document.getElementById('postTitle');
    const postCategory = document.getElementById('postCategory');

    const visibilityPublic = document.getElementById('visibilityPublic');
    const visibilityClubOnly = document.getElementById('visibilityClubOnly');
    const clubSelectionContainer = document.getElementById('clubSelectionContainer');
    const clubListContainer = document.getElementById('clubListContainer');
    const joinClubButton = document.getElementById('joinClubButton');

    const postContent = document.getElementById('postContent');
    const contentInput = document.getElementById('contentInput');
    const mediaInput = document.getElementById('mediaInput');
    const mediaGrid = document.getElementById('mediaGrid');
    const uploadZone = document.getElementById('uploadZone');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const publishBtn = document.getElementById('publishBtn');
    const loadingOverlay = document.getElementById('formLoadingOverlay');

    let clubCheckboxes = []; // 动态填充
    let selectedFiles = [];
    let isSubmitting = false;
    let formInitialData = null;
    let pendingNavigation = null;

    const config = window.forumConfig || {};
    const MAX_MEDIA_FILES = config.maxMediaFiles || 10;
    const MAX_IMAGE_SIZE = config.maxImageSize || 10 * 1024 * 1024;
    const MAX_VIDEO_SIZE = config.maxVideoSize || 100 * 1024 * 1024;

    // ========================================
    // Helper Functions
    // ========================================

    function showLoading(message) {
        if (!loadingOverlay)
            return;
        isSubmitting = true;
        const textNode = loadingOverlay.querySelector('.loading-text');
        if (textNode && message) {
            textNode.textContent = message;
        }
        loadingOverlay.style.display = 'flex';
    }

    function hideLoading() {
        if (!loadingOverlay)
            return;
        isSubmitting = false;
        loadingOverlay.style.display = 'none';
    }

    function getTrimmedEditorText() {
        if (!postContent)
            return '';
        const text = postContent.innerText || postContent.textContent || '';
        return text.replace(/\s+/g, ' ').trim();
    }

    function captureInitialFormData() {
        if (!form)
            return;
        const fd = new FormData(form);
        formInitialData = {};
        fd.forEach((value, key) => {
            formInitialData[key] = value;
        });
        formInitialData.__contentText = getTrimmedEditorText();
        formInitialData.__selectedFiles = selectedFiles.map(f => f.name).join(',');
    }

    function isFormDirty() {
        if (!form || !formInitialData)
            return false;
        const current = new FormData(form);
        const currentData = {};
        current.forEach((value, key) => {
            currentData[key] = value;
        });
        currentData.__contentText = getTrimmedEditorText();
        currentData.__selectedFiles = selectedFiles.map(f => f.name).join(',');

        const keys = new Set([...Object.keys(formInitialData), ...Object.keys(currentData)]);
        for (const key of keys) {
            if ((formInitialData[key] || '') !== (currentData[key] || '')) {
                return true;
            }
        }
        return false;
    }

    // Clear all error messages
    function clearAllErrors() {
        const errorElements = ['titleError', 'categoryError', 'visibilityError', 'clubError', 'contentError', 'mediaError'];
        errorElements.forEach(id => {
            const el = document.getElementById(id);
            if (el)
                el.textContent = '';
        });

        // Remove invalid classes
        [postTitle, postCategory].forEach(el => {
            if (el)
                el.classList.remove('is-invalid', 'is-valid');
        });

        const editorWrapper = document.getElementById('editorContainer');
        if (editorWrapper)
            editorWrapper.classList.remove('is-invalid');
    }

    // Display backend validation errors
    function displayBackendErrors(errors) {
        clearAllErrors();
        if (!errors)
            return;

        const errorMap = {
            'title': ['titleError', postTitle],
            'category_id': ['categoryError', postCategory],
            'visibility': ['visibilityError', null],
            'club_ids': ['clubError', null],
            'content': ['contentError', document.getElementById('editorContainer')],
            'media': ['mediaError', null]
        };

        Object.keys(errors).forEach(field => {
            if (errorMap[field] && errors[field].length > 0) {
                const [errorId, inputElement] = errorMap[field];
                const errorSpan = document.getElementById(errorId);
                if (errorSpan) {
                    errorSpan.textContent = errors[field][0];
                }
                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                }
            }
        });
    }

    // ========================================
    // Button State Management
    // ========================================

    function updateButtonStates() {
        const titleValue = postTitle ? postTitle.value.trim() : '';
        const contentText = getTrimmedEditorText();
        const categoryValue = postCategory ? postCategory.value : '';
        const visibilityValue = getVisibilityValue();

        // Save Draft: only requires title
        if (saveDraftBtn) {
            saveDraftBtn.disabled = !titleValue || isSubmitting;
        }

        // Publish: requires all required fields
        if (publishBtn) {
            const allRequiredFilled = titleValue && contentText && categoryValue && visibilityValue;

            // If club_only, also check clubs
            let clubsValid = true;
            if (visibilityValue === 'club_only') {
                const checkedClubs = Array.from(clubCheckboxes).filter(cb => cb.checked);
                clubsValid = checkedClubs.length > 0;
            }

            publishBtn.disabled = !allRequiredFilled || !clubsValid || isSubmitting;
        }
    }

    function getVisibilityValue() {
        if (visibilityPublic && visibilityPublic.checked)
            return 'public';
        if (visibilityClubOnly && visibilityClubOnly.checked)
            return 'club_only';
        return '';
    }

    function refreshClubCheckboxes() {
        if (!clubListContainer) {
            clubCheckboxes = [];
            return;
        }
        clubCheckboxes = Array.from(clubListContainer.querySelectorAll('.club-checkbox'));
    }


    // ========================================
    // Validation Functions
    // ========================================

    function validateTitle(showError = true) {
        if (!postTitle)
            return true;

        const value = postTitle.value.trim();
        const length = value.length;
        const errorSpan = document.getElementById('titleError');

        postTitle.classList.remove('is-invalid', 'is-valid');

        if (!showError && length === 0) {
            return false;
        }

        if (length === 0) {
            if (errorSpan && showError)
                errorSpan.textContent = 'Title is required.';
            postTitle.classList.add('is-invalid');
            return false;
        }
        if (length < 5) {
            if (errorSpan && showError)
                errorSpan.textContent = `Title must be at least 5 characters. (${5 - length} more needed)`;
            postTitle.classList.add('is-invalid');
            return false;
        }
        if (length > 100) {
            if (errorSpan && showError)
                errorSpan.textContent = `Title exceeds 100 characters. (${length - 100} too many)`;
            postTitle.classList.add('is-invalid');
            return false;
        }

        if (errorSpan)
            errorSpan.textContent = '';
        postTitle.classList.add('is-valid');
        return true;
    }

    function validateCategory(showError = true) {
        if (!postCategory)
            return true;

        const value = postCategory.value;
        const errorSpan = document.getElementById('categoryError');

        postCategory.classList.remove('is-invalid', 'is-valid');

        if (!value) {
            if (errorSpan && showError)
                errorSpan.textContent = 'Please select a category.';
            postCategory.classList.add('is-invalid');
            return false;
        }

        if (errorSpan)
            errorSpan.textContent = '';
        postCategory.classList.add('is-valid');
        return true;
    }

    function validateVisibility(showError = true) {
        const value = getVisibilityValue();
        const errorSpan = document.getElementById('visibilityError');

        if (!value) {
            if (errorSpan && showError)
                errorSpan.textContent = 'Please select post visibility.';
            return false;
        }

        if (errorSpan)
            errorSpan.textContent = '';
        return true;
    }

    function validateClubSelection(showError = true) {
        const visibilityValue = getVisibilityValue();
        if (visibilityValue !== 'club_only')
            return true;

        const checkedClubs = Array.from(clubCheckboxes).filter(cb => cb.checked);
        const errorSpan = document.getElementById('clubError');

        if (checkedClubs.length === 0) {
            if (errorSpan && showError)
                errorSpan.textContent = 'Please select at least one club.';
            return false;
        }

        if (errorSpan)
            errorSpan.textContent = '';
        return true;
    }

    function validateContent(showError = true) {
        if (!postContent)
            return true;

        const text = getTrimmedEditorText();
        const length = text.length;
        const errorSpan = document.getElementById('contentError');
        const editorWrapper = document.getElementById('editorContainer');

        if (editorWrapper)
            editorWrapper.classList.remove('is-invalid');

        if (!showError && length === 0) {
            return false;
        }

        if (length === 0) {
            if (errorSpan && showError)
                errorSpan.textContent = 'Content is required.';
            if (editorWrapper)
                editorWrapper.classList.add('is-invalid');
            return false;
        }

        if (length > 500000) {
            if (errorSpan && showError)
                errorSpan.textContent = `Content exceeds 500,000 characters. (${length - 500000} too many)`;
            if (editorWrapper)
                editorWrapper.classList.add('is-invalid');
            return false;
        }

        if (errorSpan)
            errorSpan.textContent = '';
        return true;
    }

    // ========================================
    // Title Input
    // ========================================
    if (postTitle) {
        const charCount = document.getElementById('titleCharCount');

        postTitle.addEventListener('input', function () {
            const length = this.value.length;
            if (charCount)
                charCount.textContent = length;

            // Only validate if user has started typing
            if (length > 0) {
                validateTitle(true);
            } else {
                // Clear errors when empty
                const errorSpan = document.getElementById('titleError');
                if (errorSpan)
                    errorSpan.textContent = '';
                postTitle.classList.remove('is-invalid', 'is-valid');
            }

            updateButtonStates();
        });

        // Initialize char count
        if (charCount)
            charCount.textContent = postTitle.value.length;
    }

    // ========================================
    // Category Select
    // ========================================
    if (postCategory) {
        postCategory.addEventListener('change', function () {
            if (this.value) {
                validateCategory(true);
            } else {
                // Clear errors when empty
                const errorSpan = document.getElementById('categoryError');
                if (errorSpan)
                    errorSpan.textContent = '';
                postCategory.classList.remove('is-invalid', 'is-valid');
            }
            updateButtonStates();
        });
    }

    // ========================================
    // Visibility Toggle
    // ========================================
    function handleVisibilityChange() {
        const isClubOnly = getVisibilityValue() === 'club_only';

        if (clubSelectionContainer) {
            clubSelectionContainer.style.display = isClubOnly ? 'block' : 'none';
        }

        if (!isClubOnly) {
            const errorSpan = document.getElementById('clubError');
            if (errorSpan)
                errorSpan.textContent = '';
        }

        updateButtonStates();
    }

    if (visibilityPublic) {
        visibilityPublic.addEventListener('change', handleVisibilityChange);
    }

    if (visibilityClubOnly) {
        visibilityClubOnly.addEventListener('change', handleVisibilityChange);
    }

    // Initialize visibility
    handleVisibilityChange();

    // Club checkboxes
    clubCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            validateClubSelection(true);
            updateButtonStates();
        });
    });

    // ========================================
    // Load user clubs via API
    // ========================================
    async function loadUserClubs() {
        if (!clubListContainer || !config.clubsApiUrl)
            return;

        const loadingText = document.getElementById('clubListLoading');
        if (loadingText)
            loadingText.textContent = 'Loading your clubs...';

        try {
            const response = await fetch(config.clubsApiUrl, {
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to load clubs');
            }

            const json = await response.json();
            const clubs = (json.data && json.data.clubs) ? json.data.clubs : [];

            if (!clubs.length) {
                clubListContainer.innerHTML = `
                <p class="text-muted small">
                    You are not a member of any club yet.
                </p>
            `;
                refreshClubCheckboxes();
                updateButtonStates();
                return;
            }

            // 渲染 checkbox 列表
            clubListContainer.innerHTML = clubs.map(club => {
                const id = club.id;
                const name = club.name;
                const role = club.member_role || '';
                const membersText = role ? `Role: ${role}` : '';

                // 这里改用 config.oldClubIds
                const checked =
                        Array.isArray(config.oldClubIds) && config.oldClubIds.includes(id)
                        ? 'checked'
                        : '';

                return `
                <label class="club-item">
                    <input type="checkbox"
                           name="club_ids[]"
                           value="${id}"
                           class="club-checkbox"
                           ${checked}>
                    <span class="club-checkbox-custom"></span>
                    <div class="club-info">
                        <div class="club-name">${name}</div>
                        <div class="club-members">${membersText}</div>
                    </div>
                </label>
            `;
            }).join('');

            // 重新抓 checkbox 节点并绑定事件
            refreshClubCheckboxes();
            clubCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    validateClubSelection(true);
                    updateButtonStates();
                });
            });

            updateButtonStates();
        } catch (e) {
            console.error(e);
            clubListContainer.innerHTML = `
            <p class="text-danger small">
                Failed to load clubs. Please try again later.
            </p>
        `;
        }
    }


    // ========================================
    // Join Club Modal
    // ========================================
    if (joinClubButton && config.joinClubModalId) {
        const modalElement = document.getElementById(config.joinClubModalId);
        if (modalElement && window.bootstrap) {
            const joinClubModal = new bootstrap.Modal(modalElement);

            joinClubButton.addEventListener('click', function (e) {
                e.preventDefault();
                joinClubModal.show();
            });
        }
    }


    // ========================================
    // Content Editor
    // ========================================
    if (postContent) {
        const charCount = document.getElementById('contentCharCount');

        postContent.addEventListener('input', function () {
            const text = getTrimmedEditorText();
            const length = text.length;

            if (charCount)
                charCount.textContent = length;
            if (contentInput)
                contentInput.value = postContent.innerHTML;

            // Only validate if user has started typing
            if (length > 0) {
                validateContent(true);
            } else {
                // Clear errors when empty
                const errorSpan = document.getElementById('contentError');
                const editorWrapper = document.getElementById('editorContainer');
                if (errorSpan)
                    errorSpan.textContent = '';
                if (editorWrapper)
                    editorWrapper.classList.remove('is-invalid');
            }

            updateButtonStates();
        });

        // Initialize char count
        const initialLength = getTrimmedEditorText().length;
        if (charCount)
            charCount.textContent = initialLength;
    }

    // ========================================
    // Editor Toolbar
    // ========================================
    const toolbarButtons = document.querySelectorAll('.toolbar-btn[data-command]');
    toolbarButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const command = this.dataset.command;
            document.execCommand(command, false, null);
            if (postContent)
                postContent.focus();
        });
    });

    // Link button
    const linkBtn = document.getElementById('linkBtn');
    const linkModalElement = document.getElementById('linkModal');
    const insertLinkBtn = document.getElementById('insertLinkBtn');

    if (linkBtn && linkModalElement) {
        const linkModal = new bootstrap.Modal(linkModalElement);

        linkBtn.addEventListener('click', function (e) {
            e.preventDefault();
            linkModal.show();
        });

        if (insertLinkBtn) {
            insertLinkBtn.addEventListener('click', function () {
                const url = document.getElementById('linkUrl').value;
                const text = document.getElementById('linkText').value;

                if (url) {
                    const link = `<a href="${url}" target="_blank" rel="noopener">${text || url}</a>`;
                    document.execCommand('insertHTML', false, link);
                    linkModal.hide();
                    document.getElementById('linkUrl').value = '';
                    document.getElementById('linkText').value = '';
                }
            });
        }
    }

    // Emoji Picker
    const emojiBtn = document.getElementById('emojiBtn');
    const emojiPicker = document.getElementById('emojiPicker');
    const emojiItems = document.querySelectorAll('.emoji-item');

    if (emojiBtn && emojiPicker) {
        emojiBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            emojiPicker.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                emojiPicker.classList.remove('show');
            }
        });

        emojiItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                const emoji = this.dataset.emoji;
                if (postContent) {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(document.createTextNode(emoji));
                    } else {
                        postContent.innerHTML += emoji;
                    }
                    emojiPicker.classList.remove('show');
                    postContent.focus();
                    postContent.dispatchEvent(new Event('input'));
                }
            });
        });
    }

    // ========================================
    // Media Upload
    // ========================================
    if (mediaInput && uploadZone && mediaGrid) {
        mediaInput.addEventListener('change', handleFileSelect);

        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', function () {
            this.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        });
    }

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        handleFiles(files);
    }

    function handleFiles(files) {
        const errorSpan = document.getElementById('mediaError');
        if (errorSpan)
            errorSpan.textContent = '';

        const totalFiles = selectedFiles.length + files.length;
        if (totalFiles > MAX_MEDIA_FILES) {
            if (errorSpan) {
                errorSpan.textContent = `Maximum ${MAX_MEDIA_FILES} files allowed. You tried to add ${files.length} more files.`;
            }
            return;
        }

        for (const file of files) {
            const validation = validateMediaFile(file);
            if (!validation.valid) {
                if (errorSpan)
                    errorSpan.textContent = validation.error;
                return;
            }
        }

        files.forEach(file => {
            selectedFiles.push(file);
            addMediaPreview(file);
        });

        updateMediaInput();

        if (selectedFiles.length > 0 && mediaGrid) {
            mediaGrid.style.display = 'grid';
        }

        updateButtonStates();
    }

    function validateMediaFile(file) {
        const type = file.type;

        if (type.startsWith('image/')) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(type)) {
                return {valid: false, error: `Invalid image type (${file.name}). Allowed: JPEG, PNG, GIF, WebP.`};
            }

            if (file.size > MAX_IMAGE_SIZE) {
                return {valid: false, error: `Image (${file.name}) exceeds 10MB limit.`};
            }
        } else if (type.startsWith('video/')) {
            const allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
            if (!allowedTypes.includes(type)) {
                return {valid: false, error: `Invalid video type (${file.name}). Allowed: MP4, MOV, AVI.`};
            }

            if (file.size > MAX_VIDEO_SIZE) {
                return {valid: false, error: `Video (${file.name}) exceeds 100MB limit.`};
            }
        } else {
            return {valid: false, error: `Invalid file type (${file.name}). Only images and videos allowed.`};
        }

        return {valid: true};
    }

    function addMediaPreview(file) {
        const reader = new FileReader();
        const index = selectedFiles.length - 1;

        reader.onload = function (e) {
            const div = document.createElement('div');
            div.className = 'media-item';
            div.dataset.fileIndex = index;

            const isVideo = file.type.startsWith('video/');

            if (isVideo) {
                div.innerHTML = `
                    <video src="${e.target.result}" controls></video>
                    <span class="media-badge">VIDEO</span>
                    <button type="button" class="media-remove-btn" onclick="removeMedia(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;
            } else {
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" loading="lazy">
                    <span class="media-badge">IMAGE</span>
                    <button type="button" class="media-remove-btn" onclick="removeMedia(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;
            }

            // Add click to view in lightbox
            div.addEventListener('click', function (e) {
                if (!e.target.classList.contains('media-remove-btn') && !e.target.closest('.media-remove-btn')) {
                    if (typeof openLightbox === 'function') {
                        openLightbox(index);
                    }
                }
            });

            if (mediaGrid) {
                mediaGrid.appendChild(div);
            }
        };

        reader.readAsDataURL(file);
    }

    window.removeMedia = function (index) {
        selectedFiles.splice(index, 1);

        if (mediaGrid) {
            const item = mediaGrid.querySelector(`[data-file-index="${index}"]`);
            if (item)
                item.remove();

            const items = mediaGrid.querySelectorAll('.media-item');
            items.forEach((item, newIndex) => {
                item.dataset.fileIndex = newIndex;
                const removeBtn = item.querySelector('.media-remove-btn');
                if (removeBtn) {
                    removeBtn.setAttribute('onclick', `removeMedia(${newIndex})`);
                }
            });

            if (selectedFiles.length === 0) {
                mediaGrid.style.display = 'none';
            }
        }

        updateMediaInput();
        updateButtonStates();
    };

    function updateMediaInput() {
        if (!mediaInput)
            return;
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        mediaInput.files = dt.files;
    }


    // ========================================
    // Tags System
    // ========================================

    const tagInput = document.getElementById('tagInput');
    const tagsContainer = document.getElementById('tagsContainer');
    const tagSuggestions = document.getElementById('tagSuggestions');
    const createTagBtn = document.getElementById('createTagBtn');
    const createTagModal = document.getElementById('createTagModal');
    const submitNewTagBtn = document.getElementById('submitNewTagBtn');
    const newTagName = document.getElementById('newTagName');
    const newTagDescription = document.getElementById('newTagDescription');
    const newTagError = document.getElementById('newTagError');

    let selectedTags = [];
    let createTagModalInstance = null;

    const availableTags = config.availableTags || [];
    const MAX_TAGS = config.maxTags || 10;

    // Initialize tags from hidden input (for old values)
    (function initSelectedTags() {
        const tagsHidden = document.getElementById('tagsInput');
        if (!tagsHidden)
            return;
        let value = tagsHidden.value;
        if (!value)
            return;

        try {
            const parsed = JSON.parse(value);
            if (Array.isArray(parsed)) {
                selectedTags = parsed;
                renderTags();
            }
        } catch (e) {
            // ignore
        }
    })();

    // Tag Input - Search functionality
    if (tagInput) {
        tagInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            if (query.length >= 2 && tagSuggestions) {
                const matches = availableTags
                        .filter(tag =>
                            tag.name.toLowerCase().includes(query) &&
                                    !selectedTags.includes(tag.name) &&
                                    tag.status === 'active'
                        )
                        .slice(0, 5);

                if (matches.length > 0) {
                    showTagSuggestions(matches);
                } else {
                    // Show "Create new tag" option
                    tagSuggestions.innerHTML = `
                        <div class="tag-suggestion-item tag-no-result">
                            <div class="tag-no-result-content">
                                <i class="bi bi-search"></i>
                                <span class="text-muted">No matching tags found</span>
                            </div>
                        </div>
                        <div class="tag-suggestion-item tag-create-option" id="createTagOption">
                            <div class="tag-create-content">
                                <i class="bi bi-plus-circle"></i>
                                <div>
                                    <span>Create new tag: <strong>"${query}"</strong></span>
                                    <small class="d-block text-muted">Requires admin approval</small>
                                </div>
                            </div>
                        </div>
                    `;

                    // Add click handler for create tag option
                    const createTagOption = document.getElementById('createTagOption');
                    if (createTagOption) {
                        createTagOption.addEventListener('click', function () {
                            tagSuggestions.classList.remove('show');
                            if (createTagModalInstance && newTagName) {
                                newTagName.value = query;
                                if (newTagDescription)
                                    newTagDescription.value = '';
                                if (newTagError) {
                                    newTagError.textContent = '';
                                    newTagError.style.display = 'none';
                                }
                                newTagName.classList.remove('is-invalid');
                                createTagModalInstance.show();
                            }
                        });
                    }

                    tagSuggestions.classList.add('show');
                }
            } else if (tagSuggestions) {
                tagSuggestions.classList.remove('show');
            }
        });

        // Prevent Enter key from submitting form
        tagInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }

    function showTagSuggestions(tags) {
        if (!tagSuggestions)
            return;

        tagSuggestions.innerHTML = '';
        tags.forEach(tag => {
            const div = document.createElement('div');
            div.className = 'tag-suggestion-item';
            div.innerHTML = `
                <span>${tag.name}</span>
                <small class="text-muted">${tag.usage_count || 0} uses</small>
            `;
            div.addEventListener('click', function () {
                addTag(tag.name);
                if (tagInput)
                    tagInput.value = '';
                tagSuggestions.classList.remove('show');
            });
            tagSuggestions.appendChild(div);
        });

        tagSuggestions.classList.add('show');
    }

    function addTag(tagName) {
        if (selectedTags.length >= MAX_TAGS) {
            alert(`Maximum ${MAX_TAGS} tags allowed.`);
            return;
        }

        if (selectedTags.includes(tagName)) {
            alert('This tag is already selected.');
            return;
        }

        selectedTags.push(tagName);
        renderTags();
        updateTagsInput();
    }

    window.removeTag = function (tagName) {
        selectedTags = selectedTags.filter(t => t !== tagName);
        renderTags();
        updateTagsInput();
    };

    function renderTags() {
        if (!tagsContainer)
            return;

        tagsContainer.innerHTML = '';

        if (selectedTags.length === 0) {
            tagsContainer.innerHTML = '<span class="text-muted small">No tags selected yet</span>';
        } else {
            selectedTags.forEach(tag => {
                const span = document.createElement('span');
                span.className = 'tag-modern';
                span.dataset.tag = tag;
                span.innerHTML = `
                    <i class="bi bi-tag-fill"></i>${tag}
                    <i class="bi bi-x tag-remove-modern" onclick="removeTag('${tag}')"></i>
                `;
                tagsContainer.appendChild(span);
            });
        }
    }

    function updateTagsInput() {
        const tagsInputHidden = document.getElementById('tagsInput');
        if (tagsInputHidden) {
            tagsInputHidden.value = JSON.stringify(selectedTags);
        }
    }

    // Close tag suggestions when clicking outside
    document.addEventListener('click', function (e) {
        if (tagSuggestions && !tagSuggestions.contains(e.target) && e.target !== tagInput) {
            tagSuggestions.classList.remove('show');
        }
    });

    // Create Tag Modal
    if (createTagBtn && createTagModal) {
        createTagModalInstance = new bootstrap.Modal(createTagModal);

        createTagBtn.addEventListener('click', function () {
            if (newTagName) {
                newTagName.value = '';
                newTagName.classList.remove('is-invalid');
            }
            if (newTagDescription)
                newTagDescription.value = '';
            if (newTagError)
                newTagError.textContent = '';
            createTagModalInstance.show();
        });
    }

    if (submitNewTagBtn) {
        submitNewTagBtn.addEventListener('click', async function () {
            const tagName = newTagName ? newTagName.value.trim().toLowerCase() : '';
            const description = newTagDescription ? newTagDescription.value.trim() : '';

            if (!tagName) {
                if (newTagName && newTagError) {
                    newTagName.classList.add('is-invalid');
                    newTagError.textContent = 'Tag name is required.';
                    newTagError.style.display = 'block';
                }
                return;
            }

            if (tagName.length < 2 || tagName.length > 50) {
                if (newTagName && newTagError) {
                    newTagName.classList.add('is-invalid');
                    newTagError.textContent = 'Tag name must be between 2 and 50 characters.';
                    newTagError.style.display = 'block';
                }
                return;
            }

            const existingTag = availableTags.find(t => t.name === tagName);
            if (existingTag) {
                if (newTagName && newTagError) {
                    newTagName.classList.add('is-invalid');
                    newTagError.textContent = 'This tag already exists. Please select it from the suggestions.';
                    newTagError.style.display = 'block';
                }
                return;
            }

            if (selectedTags.includes(tagName)) {
                if (newTagName && newTagError) {
                    newTagName.classList.add('is-invalid');
                    newTagError.textContent = 'This tag is already selected.';
                    newTagError.style.display = 'block';
                }
                return;
            }

            submitNewTagBtn.disabled = true;
            submitNewTagBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

            try {
                const response = await fetch('/forums/tags/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({name: tagName, description: description}),
                });

                const data = await response.json();

                if (data.success) {
                    selectedTags.push(tagName);
                    renderTags();
                    updateTagsInput();

                    alert('Tag submitted for approval! You can use it in your post, but it will only appear publicly after admin approval.');

                    createTagModalInstance.hide();

                    if (newTagName)
                        newTagName.value = '';
                    if (newTagDescription)
                        newTagDescription.value = '';
                } else {
                    if (newTagName && newTagError) {
                        newTagName.classList.add('is-invalid');
                        newTagError.textContent = data.message || 'Failed to create tag.';
                        newTagError.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error creating tag:', error);
                alert('An error occurred while creating the tag. Please try again.');
            } finally {
                submitNewTagBtn.disabled = false;
                submitNewTagBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Submit for Approval';
            }
        });
    }


    // ========================================
    // Form Submission (AJAX)
    // ========================================

    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function (e) {
            e.preventDefault();
            submitFormAjax('draft');
        });
    }

    if (publishBtn) {
        publishBtn.addEventListener('click', function (e) {
            e.preventDefault();
            submitFormAjax('published');
        });
    }

    async function submitFormAjax(status) {
        if (isSubmitting)
            return;

        // Sync content
        if (contentInput && postContent) {
            contentInput.value = postContent.innerHTML;
        }

        // Validation
        let valid = true;

        if (status === 'published') {
            // Full validation for publish
            valid = validateTitle(true) && valid;
            valid = validateCategory(true) && valid;
            valid = validateVisibility(true) && valid;
            valid = validateClubSelection(true) && valid;
            valid = validateContent(true) && valid;
        } else {
            // Only title required for draft
            valid = validateTitle(true);
        }

        if (!valid) {
            updateButtonStates();
            return;
        }

        // Show loading
        showLoading(status === 'draft' ? 'Saving draft...' : 'Publishing post...');

        // Prepare FormData
        const formData = new FormData(form);
        formData.set('status', status);

        // Add media files
        formData.delete('media[]');
        selectedFiles.forEach(file => {
            formData.append('media[]', file);
        });

        // Determine URL and method
        let url = form.action;
        let method = form.method.toUpperCase();

        if (form.querySelector('input[name="_method"]')) {
            method = form.querySelector('input[name="_method"]').value.toUpperCase();
        }

        try {
            const response = await fetch(url, {
                method: method === 'PUT' || method === 'PATCH' ? 'POST' : method,
                headers: {
                    'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const contentType = response.headers.get('Content-Type') || '';
            const isJson = contentType.includes('application/json');
            const data = isJson ? await response.json() : null;

            hideLoading();

            // 422 验证失败
            if (response.status === 422) {
                if (data && data.errors) {
                    displayBackendErrors(data.errors);
                } else if (data && data.message) {
                    alert(data.message);
                } else {
                    alert('Validation failed. Please check your input.');
                }
                return;
            }

            // 其它非 2xx
            if (!response.ok) {
                console.error('Submit failed with status', response.status, data);
                alert(data && data.message ? data.message : 'Failed to submit post.');
                return;
            }

            // 2xx 且 success=true
            if (data && data.success) {
                isSubmitting = true; // Prevent beforeunload
                window.location.href = data.redirect;
            } else {
                if (data && data.errors) {
                    displayBackendErrors(data.errors);
                } else if (data && data.message) {
                    alert(data.message);
                }
            }
        } catch (error) {
            hideLoading();
            console.error('Error submitting form:', error);
            alert('An error occurred while submitting the post. Please try again.');
        }

    }

    // ========================================
    // Leave Page Confirmation (Custom Modal)
    // ========================================

    const confirmLeaveModalElement = document.getElementById('confirmLeaveModal');
    const confirmLeaveModal = confirmLeaveModalElement ? new bootstrap.Modal(confirmLeaveModalElement) : null;
    const discardBtn = document.getElementById('discardBtn');
    const saveDraftFromModal = document.getElementById('saveDraftFromModal');

    let isNavigatingAway = false;
    let blockedNavigation = null;

    // Override all navigation attempts
    function interceptNavigation(callback) {
        if (isSubmitting || isNavigatingAway) {
            return true; // Allow navigation
        }

        const isDirty = isFormDirty();
        const isEdit = !!document.getElementById('editPostForm');

        let needConfirm = false;
        if (isEdit) {
            needConfirm = isDirty;
        } else {
            needConfirm = postTitle && postTitle.value.trim().length > 0;
        }

        if (needConfirm) {
            blockedNavigation = callback;
            if (confirmLeaveModal) {
                confirmLeaveModal.show();
            }
            return false; // Block navigation
        }

        return true; // Allow navigation
    }

    // Handle discard button
    if (discardBtn) {
        discardBtn.addEventListener('click', function () {
            isNavigatingAway = true;
            if (confirmLeaveModal) {
                confirmLeaveModal.hide();
            }
            if (blockedNavigation) {
                blockedNavigation();
                blockedNavigation = null;
            }
        });
    }

    // Handle save draft from modal
    if (saveDraftFromModal) {
        saveDraftFromModal.addEventListener('click', async function () {
            if (confirmLeaveModal) {
                confirmLeaveModal.hide();
            }

            // Save as draft
            await submitFormAjax('draft');

            // After successful save, navigate
            if (blockedNavigation && !isSubmitting) {
                isNavigatingAway = true;
                blockedNavigation();
                blockedNavigation = null;
            }
        });
    }

    // Intercept all links on the page
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (link && link.href && !link.hasAttribute('data-bs-toggle')) {
            const url = new URL(link.href, window.location.origin);

            // Check if it's an external link or same page
            if (url.origin === window.location.origin) {
                const shouldBlock = !interceptNavigation(() => {
                    window.location.href = link.href;
                });

                if (shouldBlock) {
                    e.preventDefault();
                }
            }
        }
    });

    // Intercept back button
    let isFirstPage = window.history.length <= 1;

    window.addEventListener('popstate', function (e) {
        if (isSubmitting || isNavigatingAway)
            return;

        const isDirty = isFormDirty();
        const isEdit = !!document.getElementById('editPostForm');

        let needConfirm = false;
        if (isEdit) {
            needConfirm = isDirty;
        } else {
            needConfirm = postTitle && postTitle.value.trim().length > 0;
        }

        if (needConfirm) {
            // Push state back to stay on current page
            window.history.pushState(null, '', window.location.href);

            blockedNavigation = () => {
                window.history.back();
            };

            if (confirmLeaveModal) {
                confirmLeaveModal.show();
            }
        }
    });

    // Add initial history state
    window.history.pushState(null, '', window.location.href);

    // Browser close/refresh - Use native dialog
    window.addEventListener('beforeunload', function (e) {
        if (isSubmitting || isNavigatingAway)
            return;

        const isDirty = isFormDirty();
        const isEdit = !!document.getElementById('editPostForm');

        let needConfirm = false;
        if (isEdit) {
            needConfirm = isDirty;
        } else {
            needConfirm = postTitle && postTitle.value.trim().length > 0;
        }

        if (needConfirm) {
            e.preventDefault();
            e.returnValue = ''; // Required for Chrome
            return ''; // Required for some browsers
        }
    });

    // Handle form submission from other buttons (Cancel button)
    const cancelButtons = document.querySelectorAll('.btn-cancel-modern, .btn-modern.btn-cancel-modern');
    cancelButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const href = this.getAttribute('href');

            const shouldNavigate = interceptNavigation(() => {
                window.location.href = href;
            });

            if (shouldNavigate) {
                window.location.href = href;
            }
        });
    });


    // ========================================
    // Initialize
    // ========================================

    handleVisibilityChange();
    loadUserClubs();
    captureInitialFormData();
    updateButtonStates();

    // Don't show validation errors on page load
    clearAllErrors();
});
