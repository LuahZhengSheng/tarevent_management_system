<!DOCTYPE html>
<html>
<head>
    <title>Club API Test Page</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Club API Test Page</h1>
    <p><strong>NOTE: This is a temporary test page. Remove before production.</strong></p>
    
    <!-- Current User Info -->
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">
        <h3>Current User Information</h3>
        @auth
            <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
            <p><strong>Name:</strong> {{ auth()->user()->name ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email ?? 'N/A' }}</p>
            <p><strong>Role:</strong> {{ auth()->user()->role ?? 'N/A' }}</p>
            <p style="color: green;"><strong>Status: Authenticated ✓</strong></p>
        @else
            <p style="color: red;"><strong>Status: Not Authenticated ✗</strong></p>
            <p>Please <a href="/login">login</a> first to use the API.</p>
        @endauth
    </div>
    
    <hr>

    <!-- Create Club Section -->
    <section>
        <h2>Create Club</h2>
        <form id="createClubForm">
            <div>
                <label>Club Name: <input type="text" id="clubName" name="name" required></label>
            </div>
            <div>
                <label>Description: <textarea id="clubDescription" name="description"></textarea></label>
            </div>
            <div>
                <label>Email: <input type="email" id="clubEmail" name="email"></label>
            </div>
            <div>
                <label>Phone: <input type="text" id="clubPhone" name="phone"></label>
            </div>
            <button type="submit">Create Club</button>
        </form>
        <div id="createClubResponse"></div>
    </section>

    <hr>

    <!-- Request Join Club Section -->
    <section>
        <h2>Request Join Club</h2>
        <form id="requestJoinForm">
            <div>
                <label>Club ID: <input type="number" id="requestJoinClubId" name="club_id" required></label>
            </div>
            <button type="submit">Request Join</button>
        </form>
        <div id="requestJoinResponse"></div>
    </section>

    <hr>

    <!-- Approve Join Request Section -->
    <section>
        <h2>Approve Join Request</h2>
        <form id="approveJoinForm">
            <div>
                <label>Club ID: <input type="number" id="approveJoinClubId" name="club_id" required></label>
            </div>
            <div>
                <label>User ID: <input type="number" id="approveJoinUserId" name="user_id" required></label>
            </div>
            <button type="submit">Approve Join</button>
        </form>
        <div id="approveJoinResponse"></div>
    </section>

    <hr>

    <!-- Reject Join Request Section -->
    <section>
        <h2>Reject Join Request</h2>
        <form id="rejectJoinForm">
            <div>
                <label>Club ID: <input type="number" id="rejectJoinClubId" name="club_id" required></label>
            </div>
            <div>
                <label>User ID: <input type="number" id="rejectJoinUserId" name="user_id" required></label>
            </div>
            <button type="submit">Reject Join</button>
        </form>
        <div id="rejectJoinResponse"></div>
    </section>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Helper function to make API calls
        async function makeApiCall(url, method = 'POST', body = null) {
            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin', // Include cookies/session for Sanctum
                };

                if (body) {
                    options.body = JSON.stringify(body);
                }

                const response = await fetch(url, options);
                const data = await response.json();

                return { response, data };
            } catch (error) {
                console.error('API Error:', error);
                return { error: error.message };
            }
        }

        // Create Club Form Handler
        document.getElementById('createClubForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('clubName').value,
                description: document.getElementById('clubDescription').value,
                email: document.getElementById('clubEmail').value,
                phone: document.getElementById('clubPhone').value,
            };

            const { response, data, error } = await makeApiCall('/api/clubs', 'POST', formData);
            
            if (error) {
                alert('Error: ' + error);
                console.error('Create Club Error:', error);
            } else {
                console.log('Create Club Response:', data);
                alert('Success: ' + JSON.stringify(data, null, 2));
                document.getElementById('createClubResponse').innerHTML = 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            }
        });

        // Request Join Form Handler
        document.getElementById('requestJoinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const clubId = document.getElementById('requestJoinClubId').value;
            const url = `/api/clubs/${clubId}/join`;

            const { response, data, error } = await makeApiCall(url, 'POST');
            
            if (error) {
                alert('Error: ' + error);
                console.error('Request Join Error:', error);
            } else {
                console.log('Request Join Response:', data);
                alert('Success: ' + JSON.stringify(data, null, 2));
                document.getElementById('requestJoinResponse').innerHTML = 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            }
        });

        // Approve Join Form Handler
        document.getElementById('approveJoinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const clubId = document.getElementById('approveJoinClubId').value;
            const userId = document.getElementById('approveJoinUserId').value;
            const url = `/api/clubs/${clubId}/join/${userId}/approve`;

            const { response, data, error } = await makeApiCall(url, 'POST');
            
            if (error) {
                alert('Error: ' + error);
                console.error('Approve Join Error:', error);
            } else {
                console.log('Approve Join Response:', data);
                alert('Success: ' + JSON.stringify(data, null, 2));
                document.getElementById('approveJoinResponse').innerHTML = 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            }
        });

        // Reject Join Form Handler
        document.getElementById('rejectJoinForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const clubId = document.getElementById('rejectJoinClubId').value;
            const userId = document.getElementById('rejectJoinUserId').value;
            const url = `/api/clubs/${clubId}/join/${userId}/reject`;

            const { response, data, error } = await makeApiCall(url, 'POST');
            
            if (error) {
                alert('Error: ' + error);
                console.error('Reject Join Error:', error);
            } else {
                console.log('Reject Join Response:', data);
                alert('Success: ' + JSON.stringify(data, null, 2));
                document.getElementById('rejectJoinResponse').innerHTML = 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            }
        });
    </script>
</body>
</html>

