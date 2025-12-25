<!-- Author: Auto-generated -->
@if($clubs->hasPages())
<nav aria-label="Clubs pagination">
    <ul class="pagination justify-content-center">
        <!-- Previous Page Link -->
        @if($clubs->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link">
                <i class="bi bi-chevron-left"></i>
            </span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $clubs->previousPageUrl() }}" data-page="{{ $clubs->currentPage() - 1 }}">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        @endif

        <!-- Page Numbers -->
        @foreach($clubs->getUrlRange(1, $clubs->lastPage()) as $page => $url)
            @if($page == $clubs->currentPage())
            <li class="page-item active">
                <span class="page-link" data-page="{{ $page }}">{{ $page }}</span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            </li>
            @endif
        @endforeach

        <!-- Next Page Link -->
        @if($clubs->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $clubs->nextPageUrl() }}" data-page="{{ $clubs->currentPage() + 1 }}">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        @else
        <li class="page-item disabled">
            <span class="page-link">
                <i class="bi bi-chevron-right"></i>
            </span>
        </li>
        @endif
    </ul>
</nav>
@endif

<style>
.pagination {
    margin-top: 2rem;
}

.pagination .page-link {
    color: var(--text-primary);
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    padding: 0.5rem 0.75rem;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.pagination .page-link:hover {
    background: var(--bg-secondary);
    border-color: var(--primary);
    color: var(--primary);
}

.pagination .page-item.active .page-link {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

