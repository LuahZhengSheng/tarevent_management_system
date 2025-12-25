{{-- resources/views/forums/partials/post_feed.blade.php --}}

@push('styles')
@vite([
'resources/css/forums/forum.css',
'resources/css/forums/forum-media-gallery.css',
'resources/css/forums/media-lightbox.css'
])
@endpush

@section('content')
<div class="container py-4">
    <x-post-feed
        api-url="{{ route('api.v1.clubs.posts', ['club' => $club->id]) }}"
        :initial-posts="null"
        :show-filters="true"
        data-bearer-token="{{ $bearerToken ?? '' }}"
        />
</div>
@endsection


@push('scripts')
@vite([
'resources/js/forum.js',
'resources/js/post-feed.js',
'resources/js/media-lightbox.js'
])
@endpush
