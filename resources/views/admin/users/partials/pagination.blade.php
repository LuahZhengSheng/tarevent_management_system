@if($users->hasPages())
<nav aria-label="User pagination">
    <ul class="pagination justify-content-center">
        {{-- Previous Page Link --}}
        @if($users->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link">
                <i class="bi bi-chevron-left"></i>
            </span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $users->previousPageUrl() }}" data-page="{{ $users->currentPage() - 1 }}">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        @endif

        {{-- Pagination Elements --}}
        @php
            $currentPage = $users->currentPage();
            $lastPage = $users->lastPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($lastPage, $currentPage + 2);
        @endphp

        @if($startPage > 1)
        <li class="page-item">
            <a class="page-link" href="{{ $users->url(1) }}" data-page="1">1</a>
        </li>
        @if($startPage > 2)
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>
        @endif
        @endif

        @for($page = $startPage; $page <= $endPage; $page++)
        @if($page == $currentPage)
        <li class="page-item active">
            <span class="page-link" data-page="{{ $page }}">{{ $page }}</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $users->url($page) }}" data-page="{{ $page }}">{{ $page }}</a>
        </li>
        @endif
        @endfor

        @if($endPage < $lastPage)
        @if($endPage < $lastPage - 1)
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>
        @endif
        <li class="page-item">
            <a class="page-link" href="{{ $users->url($lastPage) }}" data-page="{{ $lastPage }}">{{ $lastPage }}</a>
        </li>
        @endif

        {{-- Next Page Link --}}
        @if($users->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $users->nextPageUrl() }}" data-page="{{ $users->currentPage() + 1 }}">
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

<style>
.pagination {
    margin-top: 2rem;
}

.page-link {
    padding: 0.5rem 0.75rem;
    color: var(--text-primary);
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.page-link:hover {
    background: var(--bg-secondary);
    border-color: var(--primary);
    color: var(--primary);
}

.page-item.active .page-link {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endif

