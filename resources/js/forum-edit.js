// resources/js/forum-edit.js

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill Editor
    const quill = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Write your post content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'code-block'],
                ['clean']
            ]
        }
    });

    // Set initial content
    const initialContent = document.getElementById('content').value;
    quill.root.innerHTML = initialContent;

    // State management
    let selectedTags = [...EXISTING_TAGS];
    let selectedFiles = [];
    let existingMedia = [...EXISTING_MEDIA];
    let originalFormData = null;
    let isSubmitting = false;
    let hasUnsavedChanges = false;

    // DOM elements
    const form = document.getElementById('postForm');
    const titleInput = document.getElementById('title');
    const categorySelect = document.getElementById('category_id');
    const contentTextarea = document.getElementById('content');
    const statusSelect = document.getElementById('status');
    const tagsInput = document.getElementById('tagsInput');
    const tagsContainer = document.getElementById('tagsContainer');
    const tagsHidden = document.getElementById('tagsHidden');
    const mediaInput = document.getElementById('mediaInput');
    const mediaUploadArea = document.getElementById('mediaUploadArea');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const mediaPreviewGrid = document.getElementById('mediaPreviewGrid');
    const existingMediaGrid = document.getElementById('existingMediaGrid');
    const existingMediaInput = document.getElementById('existingMediaInput');
    const updateBtn = document.getElementById('updateBtn');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const visibilityRadios = document.querySelectorAll('input[name="visibility"]');
    const clubSelectionGroup = document.getElementById('clubSelectionGroup');
    const backButton = document.getElementById('backButton');
    const unsavedModal = new bootstrap.Modal(document.getElementById('unsavedChangesModal'));
    const discardBtn = document.getElementById('discardBtn');
    const saveChangesFromModalBtn = document.getElementById('saveChangesFromModalBtn');

    // Character counters
    const titleCount = document.getElementById('titleCount');
    const contentCount = document.getElementById('contentCount');

    // Error display elements
    const titleError = document.getElementById('titleError');
    const categoryError = document.getElementById('categoryError');
    const contentError = document.getElementById('contentError');
    const visibilityError = document.getElementById('visibilityError');
    const tagsError = document.getElementById('tagsError');
    const mediaError = document.getElementById('mediaError');
    const clubsError = document.getElementById('clubsError');

    // Initialize
    renderTags();
    initializeFormState();
    updateCharCount();

    // Save initial form state
    function initializeFormState() {
        setTimeout(() => {
            originalFormData = getFormData();
        }, 500);
    }

    // Get current form data
    function getFormData() {
        return {
            title: titleInput.value.trim(),
            category: categorySelect.value,
            content: quill.root.innerHTML.trim(),
            tags: [...selectedTags],
            files: selectedFiles.length,
            existingMedia: [...existingMedia],
            visibility: document.querySelector('input[name="visibility"]:checked')?.value || '',
            status: statusSelect.value
        };
    }

    // Check if form data changed
    function hasFormChanged() {
        if (!originalFormData) return false;
        
        const currentData = getFormData();
        return JSON.stringify(originalFormData) !== JSON.stringify(currentData);
    }

    // Update character count
    function updateCharCount() {
        const titleLength = titleInput.value.length;
        const contentLength = quill.getText().trim().length;
        
        titleCount.textContent = titleLength;
        contentCount.textContent = contentLength;

        titleCount.style.color = titleLength > 100 ? '#dc3545' : '#6c757d';
        contentCount.style.color = contentLength > 500000 ? '#dc3545' : '#6c757d';

        // Track changes
        if (originalFormData) {
            hasUnsavedChanges = hasFormChanged();
        }
    }

    // Validate required fields
    function validateRequiredFields() {
        let isValid = true;
        clearAllErrors();

        const title = titleInput.value.trim();
        if (title.length === 0) {
            showError(titleInput, titleError, 'Post title is required.');
            isValid = false;
        } else if (title.length < 5) {
            showError(titleInput, titleError, 'Post title must be at least 5 characters.');
            isValid = false;
        } else if (title.length > 100) {
            showError(titleInput, titleError, 'Post title must not exceed 100 characters.');
            isValid = false;
        }

        const content = quill.getText().trim();
        if (content.length === 0) {
            showError(contentTextarea, contentError, 'Post content is required.');
            isValid = false;
        } else if (content.length > 500000) {
            showError(contentTextarea, contentError, 'Content must not exceed 500,000 characters.');
            isValid = false;
        }

        if (!categorySelect.value) {
            showError(categorySelect, categoryError, 'Please select a category.');
            isValid = false;
        }

        const visibility = document.querySelector('input[name="visibility"]:checked');
        if (!visibility) {
            showError(null, visibilityError, 'Please select post visibility.');
            isValid = false;
        }

        if (visibility?.value === 'club_only') {
            const selectedClubs = document.querySelectorAll('input[name="club_ids[]"]:checked');
            if (selectedClubs.length === 0) {
                showError(null, clubsError, 'Please select at least one club.');
                isValid = false;
            }
        }

        return isValid;
    }

    // Show/clear error functions
    function showError(input, errorElement, message) {
        if (input) input.classList.add('is-invalid');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function clearAllErrors() {
        [titleInput, categorySelect].forEach(el => el.classList.remove('is-invalid'));
        [titleError, categoryError, contentError, visibilityError, tagsError, mediaError, clubsError]
            .forEach(el => {
                if (el) {
                    el.textContent = '';
                    el.style.display = 'none';
                }
            });
    }

    function displayBackendErrors(errors) {
        clearAllErrors();
        if (errors.title) showError(titleInput, titleError, errors.title[0]);
        if (errors.content) showError(contentTextarea, contentError, errors.content[0]);
        if (errors.category_id) showError(categorySelect, categoryError, errors.category_id[0]);
        if (errors.visibility) showError(null, visibilityError, errors.visibility[0]);
        if (errors.tags) showError(null, tagsError, errors.tags[0]);
        if (errors.media) showError(null, mediaError, errors.media[0]);
        if (errors.club_ids) showError(null, clubsError, errors.club_ids[0]);
    }

    // Event listeners
    titleInput.addEventListener('input', () => {
        updateCharCount();
        if (titleError.style.display === 'block') {
            titleInput.classList.remove('is-invalid');
            titleError.style.display = 'none';
        }
    });

    categorySelect.addEventListener('change', () => {
        if (categoryError.style.display === 'block') {
            categorySelect.classList.remove('is-invalid');
            categoryError.style.display = 'none';
        }
    });

    quill.on('text-change', () => {
        contentTextarea.value = quill.root.innerHTML;
        updateCharCount();
        if (contentError.style.display === 'block') {
            contentError.style.display = 'none';
        }
    });

    visibilityRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            clubSelectionGroup.style.display = this.value === 'club_only' ? 'block' : 'none';
            if (visibilityError.style.display === 'block') {
                visibilityError.style.display = 'none';
            }
        });
    });

    // Tags handling (same as create)
    tagsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(this.value.trim());
            this.value = '';
        }
    });

    tagsInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            addTag(this.value.trim());
            this.value = '';
        }
    });

    function addTag(tagName) {
        if (!tagName || tagName.length > 50) return;
        if (selectedTags.length >= 10) {
            showError(null, tagsError, 'Maximum 10 tags allowed.');
            return;
        }
        if (selectedTags.includes(tagName)) return;

        selectedTags.push(tagName);
        renderTags();
        if (tagsError.style.display === 'block') tagsError.style.display = 'none';
    }

    function removeTag(tagName) {
        selectedTags = selectedTags.filter(t => t !== tagName);
        renderTags();
    }

    function renderTags() {
        tagsContainer.innerHTML = '';
        selectedTags.forEach(tag => {
            const tagEl = document.createElement('span');
            tagEl.className = 'tag-badge';
            tagEl.innerHTML = `
                ${tag}
                <button type="button" class="tag-remove" onclick="window.removeTag('${tag}')">
                    <i class="bi bi-x"></i>
                </button>
            `;
            tagsContainer.appendChild(tagEl);
        });
        tagsHidden.value = JSON.stringify(selectedTags);
    }

    window.removeTag = removeTag;

    // Remove existing media
    window.removeExistingMedia = function(mediaPath) {
        existingMedia = existingMedia.filter(m => m !== mediaPath);
        existingMediaInput.value = JSON.stringify(existingMedia);
        
        const mediaItem = existingMediaGrid.querySelector(`[data-media-path="${mediaPath}"]`);
        if (mediaItem) mediaItem.remove();
        
        if (existingMedia.length === 0 && existingMediaGrid) {
            existingMediaGrid.closest('.form-group').style.display = 'none';
        }
    };

    // New media upload (same as create)
    mediaUploadArea.addEventListener('click', () => mediaInput.click());

    mediaInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const totalMedia = existingMedia.length + selectedFiles.length + files.length;
        
        if (totalMedia > 10) {
            showError(null, mediaError, 'Maximum 10 media files allowed in total.');
            return;
        }

        files.forEach(file => {
            if (selectedFiles.length < 10) selectedFiles.push(file);
        });

        renderMediaPreviews();
        if (mediaError.style.display === 'block') mediaError.style.display = 'none';
    });

    function renderMediaPreviews() {
        if (selectedFiles.length === 0) {
            uploadPlaceholder.style.display = 'flex';
            mediaPreviewGrid.style.display = 'none';
            return;
        }

        uploadPlaceholder.style.display = 'none';
        mediaPreviewGrid.style.display = 'grid';
        mediaPreviewGrid.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const preview = document.createElement('div');
            preview.className = 'media-preview-item';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.controls = true;
                preview.appendChild(video);
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-media-btn';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.onclick = () => removeMedia(index);
            preview.appendChild(removeBtn);

            mediaPreviewGrid.appendChild(preview);
        });
    }

    function removeMedia(index) {
        selectedFiles.splice(index, 1);
        renderMediaPreviews();
    }

    window.removeMedia = removeMedia;

    // Loading overlay
    function showLoading() {
        loadingOverlay.style.display = 'flex';
    }

    function hideLoading() {
        loadingOverlay.style.display = 'none';
    }

    // Submit form
    async function submitForm(isDraft = false) {
        if (isSubmitting) return;

        if (!isDraft && !validateRequiredFields()) return;

        isSubmitting = true;
        showLoading();
        clearAllErrors();

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('title', titleInput.value.trim());
        formData.append('content', quill.root.innerHTML);
        formData.append('category_id', categorySelect.value);
        formData.append('status', isDraft ? 'draft' : statusSelect.value);
        
        const visibility = document.querySelector('input[name="visibility"]:checked');
        formData.append('visibility', visibility ? visibility.value : 'public');

        if (visibility?.value === 'club_only') {
            const selectedClubs = document.querySelectorAll('input[name="club_ids[]"]:checked');
            selectedClubs.forEach(checkbox => {
                formData.append('club_ids[]', checkbox.value);
            });
        }

        formData.append('tags', JSON.stringify(selectedTags));
        formData.append('existing_media', JSON.stringify(existingMedia));

        if (document.getElementById('replaceMedia')?.checked) {
            formData.append('replace_media', '1');
        }

        selectedFiles.forEach(file => {
            formData.append('media[]', file);
        });

        try {
            const response = await fetch(UPDATE_ROUTE, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                hasUnsavedChanges = false;
                setTimeout(() => {
                    window.location.href = data.redirect || SHOW_ROUTE;
                }, 500);
            } else {
                hideLoading();
                isSubmitting = false;
                
                if (data.errors) displayBackendErrors(data.errors);
                alert(data.message || 'Failed to update post. Please try again.');
            }
        } catch (error) {
            hideLoading();
            isSubmitting = false;
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }

    // Button events
    updateBtn.addEventListener('click', () => submitForm(false));
    saveDraftBtn.addEventListener('click', () => submitForm(true));
    saveChangesFromModalBtn.addEventListener('click', () => {
        unsavedModal.hide();
        submitForm(false);
    });

    // Navigation handling
    let pendingNavigation = null;

    function handleNavigation(e) {
        if (!hasUnsavedChanges || isSubmitting) return true;

        e.preventDefault();
        pendingNavigation = e.target.href;
        unsavedModal.show();
        return false;
    }

    if (backButton) {
        backButton.addEventListener('click', handleNavigation);
    }

    discardBtn.addEventListener('click', () => {
        hasUnsavedChanges = false;
        unsavedModal.hide();
        
        if (pendingNavigation) {
            window.location.href = pendingNavigation;
        } else {
            window.location.href = SHOW_ROUTE;
        }
    });

    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges && !isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    document.querySelectorAll('a:not(#backButton)').forEach(link => {
        link.addEventListener('click', (e) => {
            if (hasUnsavedChanges && !isSubmitting) {
                handleNavigation(e);
            }
        });
    });
});
