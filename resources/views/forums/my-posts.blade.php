@extends('layouts.app')

@section('title', 'My Posts - Forum')

@section('content')
<div class="my-posts-page">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title mb-3">My Posts</h1>
                    <p class="hero-subtitle">Manage all your forum posts in one place</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('forum.posts.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Create New Post
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['total_posts'] }}</h3>
                        <p class="stat-label">Total Posts</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['published_posts'] }}</h3>
                        <p class="stat-label">Published</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ $stats['draft_posts'] }}</h3>
                        <p class="stat-label">Drafts</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-value">{{ number_format($stats['total_views']) }}</h3>
                        <p class="stat-label">Total Views</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow-sm mb-4 filter-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="filter-buttons">
                        <a href="{{ route('forum.my-posts', ['filter' => 'all']) }}" 
                           class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="bi bi-grid me-1"></i>All Posts
                        </a>
                        <a href="{{ route('forum.my-posts', ['filter' => 'published']) }}" 
                           class="btn btn-sm {{ $filter === 'published' ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-check-circle me-1"></i>Published
                        </a>
                        <a href="{{ route('forum.my-posts', ['filter' => 'draft']) }}" 
                           class="btn btn-sm {{ $filter === 'draft' ? 'btn-warning' : 'btn-outline-secondary' }}">
                            <i class="bi bi-pencil me-1"></i>Drafts
                        </a>
                    </div>

                    <div class="post-count">
                        <span class="text-muted">
                            <i class="bi bi-list-ul me-1"></i>
                            {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Grid -->
        <div class="row g-4" id="postsContainer">
            @forelse($posts as $post)
            <div class="col-lg-4 col-md-6 post-item" data-post-id="{{ $post->id }}">
                <div class="card post-card h-100">
                    <!-- Post Media Preview -->
                    @if($post->media_paths && count($post->media_paths) > 0)
                    <div class="post-media-preview">
                        @php
                            $firstMedia = $post->media_paths[0];
                            $extension = pathinfo($firstMedia, PATHINFO_EXTENSION);
                            $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);
                        @endphp
                        
                        @if($isVideo)
                        <video class="card-img-top" muted>
                            <source src="{{ Storage::url($firstMedia) }}" type="video/{{ $extension }}">
                        </video>
                        @else
                        <img src="{{ Storage::url($firstMedia) }}" 
                             alt="{{ $post->title }}" 
                             class="card-img-top">
                        @endif
                        
                        @if(count($post->media_paths) > 1)
                        <div class="media-count-badge">
                            <i class="bi bi-images"></i> {{ count($post->media_paths) }}
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="post-placeholder">
                        <i class="bi bi-file-text"></i>
                    </div>
                    @endif

                    <!-- Status Badge -->
                    <div class="post-status-badge">
                        @if($post->status === 'draft')
                            <span class="badge bg-warning">
                                <i class="bi bi-pencil me-1"></i>Draft
                            </span>
                        @else
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Published
                            </span>
                        @endif
                    </div>

                    <div class="card-body">
                        <!-- Post Title -->
                        <h5 class="post-title">
                            <a href="{{ route('forum.posts.show', $post) }}" 
                               class="text-decoration-none">
                                {{ $post->title }}
                            </a>
                        </h5>

                        <!-- Category -->
                        <div class="post-category mb-2">
                            <span class="badge bg-primary-light text-primary">
                                {{ $post->category }}
                            </span>
                            @if($post->visibility === 'club_only')
                            <span class="badge bg-secondary">
                                <i class="bi bi-lock me-1"></i>Club Only
                            </span>
                            @endif
                        </div>

                        <!-- Post Excerpt -->
                        <p class="post-excerpt text-muted">
                            {{ $post->excerpt }}
                        </p>

                        <!-- Tags -->
                        @if($post->tags && count($post->tags) > 0)
                        <div class="post-tags mb-3">
                            @foreach(array_slice($post->tags, 0, 3) as $tag)
                            <span class="tag-badge">#{{ $tag }}</span>
                            @endforeach
                            @if(count($post->tags) > 3)
                            <span class="tag-badge">+{{ count($post->tags) - 3 }}</span>
                            @endif
                        </div>
                        @endif

                        <!-- Post Meta -->
                        <div class="post-meta">
                            <div class="meta-item">
                                <i class="bi bi-calendar3 text-primary"></i>
                                <span>{{ $post->formatted_date }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-eye text-primary"></i>
                                <span>{{ number_format($post->views_count) }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-heart text-primary"></i>
                                <span>{{ number_format($post->likes_count) }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-chat text-primary"></i>
                                <span>{{ number_format($post->comments_count) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('forum.posts.show', $post) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Post">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('forum.posts.edit', $post) }}" 
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Edit Post">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-post-btn"
                                        data-post-id="{{ $post->id }}"
                                        data-post-title="{{ $post->title }}"
                                        title="Delete Post">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <button type="button" 
                                    class="btn btn-sm {{ $post->status === 'draft' ? 'btn-success' : 'btn-warning' }} toggle-status-btn"
                                    data-post-id="{{ $post->id }}">
                                @if($post->status === 'draft')
                                    <i class="bi bi-upload me-1"></i>Publish
                                @else
                                    <i class="bi bi-archive me-1"></i>Unpublish
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h4>No Posts Yet</h4>
                    <p class="text-muted mb-4">
                        You haven't created any posts yet. Start sharing your thoughts with the community!
                    </p>
                    <a href="{{ route('forum.posts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Your First Post
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
        <div class="mt-5">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the post:</p>
                <p class="fw-bold" id="deletePostTitle"></p>
                <p class="text-danger">
                    <i class="bi bi-info-circle me-1"></i>
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-2"></i>Delete Post
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    padding: 4rem 0;
    margin-bottom: 2rem;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.95;
}

.stat-card {
    background: var(--bg-primary);
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0.5rem 0 0;
}

.filter-card {
    border: none;
    border-radius: 1rem;
}

.filter-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-buttons .btn {
    transition: all 0.3s ease;
}

.post-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-md);
}

.post-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-xl);
}

.post-media-preview {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: var(--bg-secondary);
}

.post-media-preview img,
.post-media-preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.post-card:hover .post-media-preview img,
.post-card:hover .post-media-preview video {
    transform: scale(1.1);
}

.post-placeholder {
    height: 200px;
    background: linear-gradient(135deg, var(--primary-light), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: var(--primary);
    opacity: 0.3;
}

.media-count-badge {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    background: rgba(0, 0, 0, 0.75);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.post-status-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    z-index: 10;
}

.post-status-badge .badge {
    padding: 0.5rem 0.75rem;
    font-weight: 600;
}

.post-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.post-title a {
    color: var(--text-primary);
    transition: color 0.3s ease;
}

.post-title a:hover {
    color: var(--primary);
}

.post-category {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.bg-primary-light {
    background-color: var(--primary-light) !important;
}

.post-excerpt {
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.post-tags {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background-color: var(--bg-secondary);
    color: var(--text-secondary);
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.post-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.meta-item i {
    font-size: 1rem;
}

.card-footer {
    border-top: 1px solid var(--border-color);
    padding: 1rem;
}

.empty-state {
    padding: 4rem 2rem;
}

.empty-state i {
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .filter-buttons {
        width: 100%;
    }

    .filter-buttons .btn {
        flex: 1;
    }
}

/* Dark Mode */
[data-theme="dark"] .post-placeholder {
    background: linear-gradient(135deg, var(--bg-tertiary), var(--primary-light));
}

[data-theme="dark"] .tag-badge {
    background-color: var(--bg-tertiary);
}
</style>

@push('scripts')
<script>
$(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let deletePostId = null;

    // Delete Post Button Click
    $('.delete-post-btn').on('click', function() {
        deletePostId = $(this).data('post-id');
        const postTitle = $(this).data('post-title');
        
        $('#deletePostTitle').text(postTitle);
        new bootstrap.Modal('#deleteModal').show();
    });

    // Confirm Delete
    $('#confirmDeleteBtn').on('click', function() {
        if (!deletePostId) return;

        const $btn = $(this);
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        $.ajax({
            url: '{{ route("forum.my-posts.quick-delete") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            data: { post_id: deletePostId },
            success: function(response) {
                if (response.success) {
                    // Remove post card with animation
                    $(`.post-item[data-post-id="${deletePostId}"]`)
                        .fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if no posts left
                            if ($('.post-item').length === 0) {
                                location.reload();
                            }
                        });
                    
                    bootstrap.Modal.getInstance('#deleteModal').hide();
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to delete post. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false)
                    .html('<i class="bi bi-trash me-2"></i>Delete Post');
                deletePostId = null;
            }
        });
    });

    // Toggle Post Status
    $('.toggle-status-btn').on('click', function() {
        const $btn = $(this);
        const postId = $btn.data('post-id');
        const $card = $(`.post-item[data-post-id="${postId}"]`);

        $btn.prop('disabled', true);

        $.ajax({
            url: `/forum/posts/${postId}/toggle-status`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    
                    // Update button and badge
                    if (response.status === 'published') {
                        $btn.removeClass('btn-success').addClass('btn-warning')
                            .html('<i class="bi bi-archive me-1"></i>Unpublish');
                        $card.find('.post-status-badge .badge')
                            .removeClass('bg-warning')
                            .addClass('bg-success')
                            .html('<i class="bi bi-check-circle me-1"></i>Published');
                    } else {
                        $btn.removeClass('btn-warning').addClass('btn-success')
                            .html('<i class="bi bi-upload me-1"></i>Publish');
                        $card.find('.post-status-badge .badge')
                            .removeClass('bg-success')
                            .addClass('bg-warning')
                            .html('<i class="bi bi-pencil me-1"></i>Draft');
                    }
                }
            },
            error: function() {
                showToast('error', 'Failed to update post status.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    function showToast(type, message) {
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'check-circle' : 'x-circle';
        
        const toast = $(`
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast ${bgColor} text-white" role="alert">
                    <div class="toast-body">
                        <i class="bi bi-${icon} me-2"></i>${message}
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast.find('.toast')[0], { delay: 3000 });
        bsToast.show();
        
        toast.find('.toast').on('hidden.bs.toast', function() {
            toast.remove();
        });
    }
});
</script>
@endpush
@endsection