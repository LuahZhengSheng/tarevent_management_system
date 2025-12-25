<form method="POST" action="/test/clubs/{{ $clubId ?? 1 }}/deactivate">
    @csrf
    @method('PUT')
    
    <div>
        <label for="reason">Reason (optional):</label>
        <textarea id="reason" name="reason"></textarea>
    </div>
    
    <button type="submit">Deactivate Club</button>
</form>

