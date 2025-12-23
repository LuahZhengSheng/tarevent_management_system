<!DOCTYPE html>
<html>
<head>
    <title>Club Module Test UI</title>
</head>
<body>
    <h1>TEST UI – NOT PRODUCTION</h1>
    <p><strong>WARNING: This is a test interface. Do not use in production.</strong></p>

    @if(session('success'))
        <div style="background: green; color: white; padding: 10px; margin: 10px 0;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: red; color: white; padding: 10px; margin: 10px 0;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <hr>

    <h2>Club Lifecycle</h2>

    <p><strong>Note: Admin-created clubs are immediately active and approved.</strong></p>

    <h3>Create Club</h3>
    <form method="POST" action="{{ route('clubs.store') }}">
        @csrf
        <div>
            <label>Name: <input type="text" name="name" required></label>
        </div>
        <div>
            <label>Description: <textarea name="description"></textarea></label>
        </div>
        <div>
            <label>Email: <input type="email" name="email"></label>
        </div>
        <div>
            <label>Phone: <input type="text" name="phone"></label>
        </div>
        <div>
            <label>Logo: <input type="text" name="logo"></label>
        </div>
        <button type="submit">Create Club</button>
    </form>

    <h3>Activate Club</h3>
    <form method="POST" action="/clubs/{club}/activate" onsubmit="this.action = '/clubs/' + document.getElementById('activate_club_id').value + '/activate'; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="activate_club_id" name="club_id" required></label>
        </div>
        <button type="submit">Activate Club</button>
    </form>

    <h3>Deactivate Club</h3>
    <form method="POST" action="/clubs/{club}/deactivate" onsubmit="this.action = '/clubs/' + document.getElementById('deactivate_club_id').value + '/deactivate'; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="deactivate_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>Reason: <textarea name="reason"></textarea></label>
        </div>
        <button type="submit">Deactivate Club</button>
    </form>

    <h3>Transfer Club Ownership</h3>
    <form method="POST" action="/clubs/{club}/transfer-ownership" onsubmit="this.action = '/clubs/' + document.getElementById('transfer_club_id').value + '/transfer-ownership'; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="transfer_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>New Owner ID: <input type="number" name="new_owner_id" required></label>
        </div>
        <button type="submit">Transfer Ownership</button>
    </form>

    <h3>Bulk Update Club Status</h3>
    <form method="POST" action="/clubs/bulk-update-status">
        @csrf
        @method('PUT')
        <div>
            <label>Club IDs (comma-separated): <input type="text" name="club_ids" placeholder="1,2,3" required></label>
        </div>
        <div>
            <label>Status: 
                <select name="status" required>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </label>
        </div>
        <button type="submit">Bulk Update Status</button>
    </form>

    <hr>

    <h2>Join Requests (Student Join Requests)</h2>

    <h3>Request to Join Club (Student)</h3>
    <p><em>Student requests to join a club</em></p>
    <form method="POST" action="/clubs/{club}/join-request" onsubmit="this.action = '/clubs/' + document.getElementById('request_join_club_id').value + '/join-request'; return true;">
        @csrf
        <div>
            <label>Club ID: <input type="number" id="request_join_club_id" name="club_id" required></label>
        </div>
        <button type="submit">Request to Join</button>
    </form>

    <h3>Approve Student Join Request</h3>
    <p><em>Approve a student's request to join a club</em></p>
    <form method="POST" action="/clubs/{club}/join-requests/{user}/approve" onsubmit="this.action = '/clubs/' + document.getElementById('approve_join_club_id').value + '/join-requests/' + document.getElementById('approve_join_user_id').value + '/approve'; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="approve_join_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>User ID (Student): <input type="number" id="approve_join_user_id" name="user_id" required></label>
        </div>
        <button type="submit">Approve Join Request</button>
    </form>

    <h3>Reject Student Join Request</h3>
    <p><em>Reject a student's request to join a club</em></p>
    <form method="POST" action="/clubs/{club}/join-requests/{user}/reject" onsubmit="this.action = '/clubs/' + document.getElementById('reject_join_club_id').value + '/join-requests/' + document.getElementById('reject_join_user_id').value + '/reject'; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="reject_join_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>User ID (Student): <input type="number" id="reject_join_user_id" name="user_id" required></label>
        </div>
        <button type="submit">Reject Join Request</button>
    </form>

    <hr>

    <h2>Member Management</h2>

    <h3>Add Member to Club</h3>
    <form method="POST" action="/clubs/{club}/members/{user}" onsubmit="this.action = '/clubs/' + document.getElementById('add_member_club_id').value + '/members/' + document.getElementById('add_member_user_id').value; return true;">
        @csrf
        <div>
            <label>Club ID: <input type="number" id="add_member_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>User ID: <input type="number" id="add_member_user_id" name="user_id" required></label>
        </div>
        <div>
            <label>Role: <input type="text" name="role" placeholder="member"></label>
        </div>
        <button type="submit">Add Member</button>
    </form>

    <h3>Update Member Role</h3>
    <form method="POST" action="/clubs/{club}/members/{user}" onsubmit="this.action = '/clubs/' + document.getElementById('update_role_club_id').value + '/members/' + document.getElementById('update_role_user_id').value; return true;">
        @csrf
        @method('PUT')
        <div>
            <label>Club ID: <input type="number" id="update_role_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>User ID: <input type="number" id="update_role_user_id" name="user_id" required></label>
        </div>
        <div>
            <label>New Role: <input type="text" name="role" required></label>
        </div>
        <button type="submit">Update Member Role</button>
    </form>

    <h3>Remove Member from Club</h3>
    <form method="POST" action="/clubs/{club}/members/{user}" onsubmit="this.action = '/clubs/' + document.getElementById('remove_member_club_id').value + '/members/' + document.getElementById('remove_member_user_id').value; return true;">
        @csrf
        @method('DELETE')
        <div>
            <label>Club ID: <input type="number" id="remove_member_club_id" name="club_id" required></label>
        </div>
        <div>
            <label>User ID: <input type="number" id="remove_member_user_id" name="user_id" required></label>
        </div>
        <button type="submit">Remove Member</button>
    </form>

</body>
</html>

