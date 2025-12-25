@php
    $selectedCategory = null;
    if(request('category_id')) {
        $selectedCategory = $categories->firstWhere('id', (int)request('category_id'));
    }
@endphp

<div class="results-info">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="results-count">{{ $posts->total() }}</span>
    <span class="results-text">{{ Str::plural('Discussion', $posts->total()) }}</span>
</div>

@if(!empty($searchText) || request('category_id') || !empty($selectedTags))
    <div class="active-filters">
        @if(!empty($searchText))
            <span class="filter-tag">
                Search: {{ $searchText }}
                <a href="#" class="filter-tag-close js-clear-filter" data-clear="search" aria-label="Clear search">
                    <i class="bi bi-x"></i>
                </a>
            </span>
        @endif

        @if($selectedCategory)
            <span class="filter-tag">
                {{ $selectedCategory->name }}
                <a href="#" class="filter-tag-close js-clear-filter" data-clear="category_id" aria-label="Clear category">
                    <i class="bi bi-x"></i>
                </a>
            </span>
        @endif

        @if(!empty($selectedTags))
            @foreach($selectedTags as $slug)
                <span class="filter-tag">
                    #{{ $slug }}
                    <a href="#" class="filter-tag-close js-remove-tag" data-tag="{{ $slug }}" aria-label="Remove tag {{ $slug }}">
                        <i class="bi bi-x"></i>
                    </a>
                </span>
            @endforeach
        @endif
    </div>
@endif
