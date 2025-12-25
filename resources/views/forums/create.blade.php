{{-- resources/views/forums/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create New Post - Forum')

@push('styles')
@vite(['resources/css/forums/forum-create.css', 'resources/css/forums/media-lightbox.css'])
@endpush

{{-- Join Club Modal --}}
@include('clubs.join_modal')

@section('content')
<div class="forum-create-wrapper">
    {{-- Hero Section --}}
    <section class="forum-hero">
        <div class="container">
            <div class="forum-hero-content">
                <a href="{{ route('forums.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="forum-hero-text">
                    <h1>Create New Post</h1>
                    <p>Share your ideas with the community</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container forum-create-container">
        <form id="createPostForm" method="POST" action="{{ route('forums.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="forum-layout">
                {{-- Left Column: Post Form --}}
                <div class="forum-main-content">
                    {{-- Title --}}
                    <div class="form-group">
                        <label class="form-label">
                            Post Title <span class="required-mark">*</span>
                        </label>
                        <input
                            type="text"
                            name="title"
                            id="postTitle"
                            class="form-input"
                            placeholder="Enter an engaging title (5-100 characters)"
                            value="{{ old('title') }}"
                            minlength="5"
                            maxlength="100"
                            >
                        <div class="form-footer">
                            <div class="form-error" id="titleError"></div>
                            <div class="char-count">
                                <span id="titleCharCount">0</span>/100 characters
                            </div>
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="form-group">
                        <label class="form-label">
                            Category <span class="required-mark">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select name="category_id" id="postCategory" class="form-select">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            <i class="bi bi-chevron-down select-icon"></i>
                        </div>
                        <div class="form-error" id="categoryError"></div>
                    </div>

                    {{-- Content Editor --}}
                    <div class="form-group">
                        <label class="form-label">
                            Content <span class="required-mark">*</span>
                        </label>

                        <div class="editor-wrapper" id="editorContainer">
                            {{-- Toolbar --}}
                            <div class="editor-toolbar">
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                                        <i class="bi bi-type-bold"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                                        <i class="bi bi-type-italic"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                                        <i class="bi bi-type-underline"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strikethrough">
                                        <i class="bi bi-type-strikethrough"></i>
                                    </button>
                                </div>

                                <div class="toolbar-divider"></div>

                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                                        <i class="bi bi-list-ul"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                                        <i class="bi bi-list-ol"></i>
                                    </button>
                                </div>

                                <div class="toolbar-divider"></div>

                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" id="linkBtn" title="Insert Link">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="insertImage" title="Insert Image">
                                        <i class="bi bi-image"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-command="code" title="Code">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                </div>

                                <div class="toolbar-divider"></div>

                                <div class="toolbar-group">
                                    <div class="emoji-dropdown">
                                        <button type="button" class="toolbar-btn" id="emojiBtn" title="Insert Emoji">
                                            <i class="bi bi-emoji-smile"></i>
                                        </button>
                                        <div class="emoji-picker" id="emojiPicker">
                                            <div class="emoji-grid">
                                                @php
                                                $emojis = ['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🤭','🤫','🤥','😶','😐','😑','😬','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','👍','👎','👏','🙌','🤝','🙏','💪','❤️','🔥','⭐'];
                                                @endphp
                                                @foreach($emojis as $emoji)
                                                <button type="button" class="emoji-item" data-emoji="{{ $emoji }}">{{ $emoji }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Editor Content --}}
                            <div
                                class="editor-content"
                                id="postContent"
                                contenteditable="true"
                                data-placeholder="Write your post content here..."
                                >{!! old('content') !!}</div>

                            <textarea name="content" id="contentInput" style="display:none;">{{ old('content') }}</textarea>
                        </div>

                        <div class="form-footer">
                            <div class="form-error" id="contentError"></div>
                            <div class="char-count">
                                <span id="contentCharCount">0</span>/500,000 characters
                            </div>
                        </div>
                    </div>

                    {{-- Media Upload --}}
                    <div class="form-group">
                        <label class="form-label">Media (Optional)</label>

                        <div class="upload-zone" id="uploadZone">
                            <label for="mediaInput" class="upload-label">
                                <div class="upload-icon">
                                    <i class="bi bi-cloud-upload"></i>
                                </div>
                                <div class="upload-text">
                                    <p class="upload-title">Drop files here or click to browse</p>
                                    <p class="upload-desc">Upload images or videos</p>
                                    <p class="upload-limits">Max 10 files • Images: 10MB • Videos: 100MB</p>
                                </div>
                            </label>
                            <input
                                type="file"
                                name="media[]"
                                id="mediaInput"
                                class="d-none"
                                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,video/mp4,video/quicktime,video/x-msvideo"
                                multiple
                                >
                        </div>

                        <div class="media-grid" id="mediaGrid" style="display:none;"></div>
                        <div class="form-error" id="mediaError"></div>
                    </div>

                    {{-- Tags Card --}}
                    <div class="form-group">
                        <div class="tags-card">
                            <div class="tags-card-header">
                                <label class="form-label" style="margin-bottom: 0;">
                                    <i class="bi bi-tags-fill"></i> Tags (Optional)
                                </label>
                                <button type="button" class="btn-create-tag" id="createTagBtn">
                                    <i class="bi bi-plus-circle"></i> Create Tag
                                </button>
                            </div>

                            <p class="tags-hint">
                                <i class="bi bi-info-circle"></i>
                                Select existing tags or create new ones (up to {{ config('forum.max_tags', 10) }} tags). 
                                New tags require admin approval.
                            </p>

                            {{-- Tag Search --}}
                            <div class="tag-search-wrapper">
                                <input
                                    type="text"
                                    class="form-input"
                                    id="tagInput"
                                    placeholder="Type to search existing tags..."
                                    autocomplete="off"
                                    >
                                <div id="tagSuggestions" class="tag-suggestions"></div>
                            </div>

                            {{-- Selected Tags --}}
                            <div class="selected-tags-wrapper">
                                <label class="form-label">Selected Tags</label>
                                <div id="tagsContainer" class="selected-tags">
                                    <span class="text-muted small">No tags selected yet</span>
                                </div>
                            </div>

                            {{-- Hidden input --}}
                            <input type="hidden" name="tags" id="tagsInput" value="{{ old('tags') ? json_encode(old('tags')) : '' }}">
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar: Settings & Actions --}}
                <div class="forum-sidebar">
                    {{-- Post Settings Card --}}
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="bi bi-gear"></i>
                            <h3>Post Settings</h3>
                        </div>
                        <div class="sidebar-card-body">
                            {{-- Visibility --}}
                            <div class="form-group">
                                <label class="form-label">
                                    Visibility <span class="required-mark">*</span>
                                </label>

                                <div class="visibility-options">
                                    <label class="visibility-option" data-value="public">
                                        <input type="radio" name="visibility" value="public" id="visibilityPublic" {{ old('visibility', 'public') === 'public' ? 'checked' : '' }}>
                                        <div class="visibility-card">
                                            <div class="visibility-icon">
                                                <i class="bi bi-globe"></i>
                                            </div>
                                            <div class="visibility-content">
                                                <div class="visibility-title">Public</div>
                                                <div class="visibility-desc">Anyone can see this post</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="visibility-option" data-value="club_only">
                                        <input type="radio" name="visibility" value="club_only" id="visibilityClubOnly" {{ old('visibility') === 'club_only' ? 'checked' : '' }}>
                                        <div class="visibility-card">
                                            <div class="visibility-icon">
                                                <i class="bi bi-lock"></i>
                                            </div>
                                            <div class="visibility-content">
                                                <div class="visibility-title">Club Only</div>
                                                <div class="visibility-desc">Only club members can see</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-error" id="visibilityError"></div>
                            </div>

                            {{-- Club Selection --}}
                            <div class="form-group" id="clubSelectionContainer" style="display: none">
                                <label class="form-label">
                                    Select Clubs
                                    <span class="required-mark">*</span>
                                </label>

                                {{-- 由 JS 通过 /api/users/{userId}/clubs 渲染 --}}
                                <div class="club-list" id="clubListContainer">
                                    <p class="text-muted small" id="clubListLoading">Loading your clubs...</p>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="form-error" id="clubError"></div>

                                    {{-- Join Club 按钮 --}}
                                    <button type="button"
                                            class="btn btn-link btn-sm"
                                            id="joinClubButton">
                                        Join a club
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="sidebar-card">
                        <div class="sidebar-card-body">
                            <button
                                type="button"
                                data-action="publish"
                                class="btn btn-primary btn-block"
                                id="publishBtn"
                                disabled
                                >
                                <i class="bi bi-send"></i> Publish Post
                            </button>

                            <button
                                type="button"
                                data-action="draft"
                                class="btn btn-secondary btn-block"
                                id="saveDraftBtn"
                                disabled
                                >
                                <i class="bi bi-file-earmark"></i> Save as Draft
                            </button>

                            <div class="btn-hint">
                                <i class="bi bi-info-circle"></i>
                                Fill in required fields to enable buttons
                            </div>
                        </div>
                    </div>

                    {{-- Posting Guidelines --}}
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <i class="bi bi-lightbulb"></i>
                            <h3>Posting Guidelines</h3>
                        </div>
                        <div class="sidebar-card-body">
                            <ul class="guidelines-list">
                                <li><i class="bi bi-check-circle"></i> Be respectful and constructive</li>
                                <li><i class="bi bi-check-circle"></i> Stay on topic</li>
                                <li><i class="bi bi-check-circle"></i> Use clear and concise language</li>
                                <li><i class="bi bi-check-circle"></i> Add relevant tags for better visibility</li>
                                <li><i class="bi bi-check-circle"></i> Check spelling and grammar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Loading Overlay --}}
    <div id="formLoadingOverlay" class="loading-overlay" style="display:none;">
        <div class="loading-backdrop"></div>
        <div class="loading-content">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="loading-text">Processing your post...</div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal fade" id="confirmLeaveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content confirm-leave-modal">
                <div class="modal-header confirm-leave-header">
                    <div class="confirm-leave-icon-wrapper">
                        <div class="confirm-leave-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-body confirm-leave-body">
                    <h4 class="confirm-leave-title">Unsaved Changes</h4>
                    <p class="confirm-leave-message">
                        You have unsaved changes that will be lost if you leave this page. 
                        What would you like to do?
                    </p>
                </div>
                <div class="modal-footer confirm-leave-footer">
                    <button type="button" class="btn-confirm-leave btn-discard" id="discardBtn">
                        <i class="bi bi-trash3"></i>
                        <span>Discard Changes</span>
                    </button>
                    <button type="button" class="btn-confirm-leave btn-continue" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left"></i>
                        <span>Continue Editing</span>
                    </button>
                    <button type="button" class="btn-confirm-leave btn-save" id="saveDraftFromModal">
                        <i class="bi bi-check-circle"></i>
                        <span>Save as Draft</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Link Modal --}}
    <div class="modal fade" id="linkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Insert Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">URL</label>
                        <input type="url" class="form-control" id="linkUrl" placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Text</label>
                        <input type="text" class="form-control" id="linkText" placeholder="Click here">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="insertLinkBtn">Insert Link</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Tag Modal --}}
    <div class="modal fade" id="createTagModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Create New Tag
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> New tags require admin approval before they appear publicly.
                    </div>

                    <div class="mb-3">
                        <label for="newTagName" class="form-label">
                            Tag Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            id="newTagName"
                            placeholder="e.g., javascript, photography"
                            maxlength="50"
                            >
                        <div class="form-text">
                            Maximum 50 characters. Use lowercase letters, numbers, hyphens, and spaces.
                        </div>
                        <div id="newTagError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="newTagDescription" class="form-label">Description (Optional)</label>
                        <textarea
                            class="form-control"
                            id="newTagDescription"
                            rows="3"
                            placeholder="Brief description of what this tag represents..."
                            maxlength="200"
                            ></textarea>
                        <div class="form-text">Help admins understand the purpose of this tag.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitNewTagBtn">
                        <i class="bi bi-check-circle me-1"></i> Submit for Approval
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.forumConfig = {
        availableTags: @json($activeTags ?? []),
        maxTags: 10,
        maxMediaFiles: 10,
        maxImageSize: 10 * 1024 * 1024,
        maxVideoSize: 100 * 1024 * 1024,

        // club 相关配置
        currentUserId: {{ auth()->id() }},
        clubsApiUrl: '{{ url('/api/users/' . auth()->id() . '/clubs') }}', // 对应 api.php
        joinClubModalId: 'joinClubModal', // JS 用这个 id 打开 modal
    };
</script>
@vite(['resources/js/forum-create.js', 'resources/js/media-lightbox.js'])
@endpush

