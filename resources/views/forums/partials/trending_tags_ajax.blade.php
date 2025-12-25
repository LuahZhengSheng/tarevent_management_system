{{-- resources/views/forums/partials/trending_tags_ajax.blade.php --}}
@if(($popularTags ?? collect())->count() > 0)
    <div class="tag-cloud tag-cloud--roomy">
        @foreach($popularTags as $tag)
            @php
                $selected = in_array($tag->slug, (array)($selectedTags ?? []), true);
            @endphp

            <button
                type="button"
                class="tag-chip js-tag-chip {{ $selected ? 'is-active' : '' }}"
                data-tag="{{ $tag->slug }}"
                title="Toggle tag: {{ $tag->name }}"
            >
                #{{ $tag->name }}
            </button>
        @endforeach
    </div>
@else
    <div class="text-muted small">No trending tags.</div>
@endif
