<div class="mb-4">
  <h2 class="mb-1">Discussion</h2>

  <x-post-feed
    api-url="{{ route('api.v1.clubs.posts', ['club' => $club->id]) }}"
    :initial-posts="null"
    :show-filters="true"
  />
</div>
