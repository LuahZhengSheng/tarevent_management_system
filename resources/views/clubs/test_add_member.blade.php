<p><strong>Note: Club ID and User ID are hardcoded for testing</strong></p>
<p>Club ID: 1, User ID: 2</p>

<form method="POST" action="/test/clubs/1/members/2/add">
    @csrf
    
    <button type="submit">Add Member</button>
</form>

