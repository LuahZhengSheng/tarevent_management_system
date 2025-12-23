<form method="POST" action="{{ route('clubs.store') }}">
    @csrf
    
    <div>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div>
        <label for="description">Description:</label>
        <textarea id="description" name="description"></textarea>
    </div>
    
    <div>
        <button type="submit">Create Club</button>
    </div>
</form>

