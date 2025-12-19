<!DOCTYPE html>
<html>
<head>
    <title>Club User API Test Page</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Club User API Test Page</h1>
    <p><strong>NOTE: This is a temporary test page. Remove before production.</strong></p>
    
    <!-- Current User Info -->
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">
        <h3>Current User Information</h3>
        @auth
            <p><strong>User ID:</strong> {{ auth()->user()->id }}</p>
            <p><strong>Name:</strong> {{ auth()->user()->name ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email ?? 'N/A' }}</p>
            <p><strong>Role:</strong> {{ auth()->user()->role ?? 'N/A' }}</p>
            @if(auth()->user()->role === 'admin')
                <p style="color: green;"><strong>Status: Authenticated as Admin ✓</strong></p>
            @else
                <p style="color: orange;"><strong>Status: Authenticated but NOT Admin (requires admin role)</strong></p>
            @endif
        @else
            <p style="color: red;"><strong>Status: Not Authenticated ✗</strong></p>
            <p>Please <a href="/login">login</a> first to use the API.</p>
        @endauth
    </div>
    
    <hr>

    <!-- Create Club User Section -->
    <section>
        <h2>Create Club User</h2>
        <p><em>POST /api/v1/club-users</em></p>
        <form id="createClubUserForm">
            <div>
                <label>Name: <input type="text" id="clubUserName" name="name" value="Test Club Account" required></label>
            </div>
            <div>
                <label>Email: <input type="email" id="clubUserEmail" name="email" value="club_test@example.com" required></label>
            </div>
            <div>
                <label>Password: <input type="password" id="clubUserPassword" name="password" value="password123" required></label>
            </div>
            <div>
                <label>Password Confirmation: <input type="password" id="clubUserPasswordConfirmation" name="password_confirmation" value="password123" required></label>
            </div>
            <div>
                <label>Student ID: <input type="text" id="clubUserStudentId" name="student_id" value="CLUB001" required></label>
            </div>
            <div>
                <label>Phone: <input type="text" id="clubUserPhone" name="phone" value="0123456789" required></label>
            </div>
            <div>
                <label>Program: <input type="text" id="clubUserProgram" name="program" value="N/A"></label>
            </div>
            <button type="submit">Create Club User</button>
        </form>
        <div id="createClubUserResponse" style="margin-top: 20px;"></div>
    </section>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Create Club User Form Handler
        document.getElementById('createClubUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('clubUserName').value,
                email: document.getElementById('clubUserEmail').value,
                password: document.getElementById('clubUserPassword').value,
                password_confirmation: document.getElementById('clubUserPasswordConfirmation').value,
                student_id: document.getElementById('clubUserStudentId').value,
                phone: document.getElementById('clubUserPhone').value,
                program: document.getElementById('clubUserProgram').value,
            };

            try {
                const response = await fetch('/api/v1/club-users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData),
                });

                const data = await response.json();
                
                console.log('Response Status:', response.status);
                console.log('Response Data:', data);
                
                // Display response
                const responseDiv = document.getElementById('createClubUserResponse');
                responseDiv.innerHTML = `
                    <h3>Response (Status: ${response.status})</h3>
                    <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto;">${JSON.stringify(data, null, 2)}</pre>
                `;
                
                // Verification
                if (response.ok && data.success) {
                    const userData = data.data;
                    let verification = '<h4>Verification Results:</h4><ul>';
                    
                    // Check role
                    if (userData.role === 'club') {
                        verification += '<li style="color: green;">✓ Role verification: PASSED (role = "club")</li>';
                    } else {
                        verification += `<li style="color: red;">✗ Role verification: FAILED (expected "club", got "${userData.role}")</li>`;
                    }
                    
                    // Check response structure
                    const expectedFields = ['id', 'name', 'email', 'student_id', 'phone', 'program', 'role', 'status', 'club_id', 'created_at'];
                    const responseFields = Object.keys(userData);
                    const missingFields = expectedFields.filter(field => !responseFields.includes(field));
                    
                    if (missingFields.length === 0) {
                        verification += '<li style="color: green;">✓ Response structure: PASSED (all expected fields present)</li>';
                    } else {
                        verification += `<li style="color: red;">✗ Response structure: FAILED (missing fields: ${missingFields.join(', ')})</li>`;
                    }
                    
                    verification += '</ul>';
                    responseDiv.innerHTML += verification;
                    
                    alert('Success! Check the response below for verification results.');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('API Error:', error);
                alert('Error: ' + error.message);
                document.getElementById('createClubUserResponse').innerHTML = 
                    '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        });
    </script>
</body>
</html>

