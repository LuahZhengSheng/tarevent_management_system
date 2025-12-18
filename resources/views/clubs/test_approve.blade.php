<form method="POST" action="/test/clubs/{{ $clubId ?? 1 }}/approve">
    @csrf
    @method('PUT')
    
    <button type="submit">Approve Club</button>
</form>

