@extends('layouts.app')

@section('title', 'Test User Clubs API')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-bug me-2"></i>
                        Test User Clubs API
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>API Endpoint:</strong> <code>GET /api/users/{user}/clubs</code>
                        <br>
                        <strong>Description:</strong> Get all clubs that a user is a member of
                    </div>

                    <div class="mb-4">
                        <label for="userIdInput" class="form-label fw-bold">
                            User ID
                        </label>
                        <div class="input-group">
                            <input 
                                type="number" 
                                class="form-control" 
                                id="userIdInput" 
                                placeholder="Enter User ID (e.g., 1, 2, 3...)"
                                value="{{ auth()->id() }}"
                                min="1">
                            <button 
                                class="btn btn-primary" 
                                type="button"
                                id="testBtn">
                                <i class="bi bi-search me-2"></i>
                                Test API
                            </button>
                        </div>
                        <div class="form-text">
                            Enter the ID of the user to get their clubs. Defaults to current user.
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5>Quick Test Buttons:</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary" onclick="testApi({{ auth()->id() }})">
                                Current User (ID: {{ auth()->id() }})
                            </button>
                            <button class="btn btn-outline-primary" onclick="testApi(1)">User ID: 1</button>
                            <button class="btn btn-outline-primary" onclick="testApi(2)">User ID: 2</button>
                            <button class="btn btn-outline-primary" onclick="testApi(3)">User ID: 3</button>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5>Direct URL Links:</h5>
                        <div class="list-group">
                            <a href="/api/users/{{ auth()->id() }}/clubs" 
                               target="_blank" 
                               class="list-group-item list-group-item-action">
                                <i class="bi bi-link-45deg me-2"></i>
                                <strong>Current User:</strong> 
                                <code>/api/users/{{ auth()->id() }}/clubs</code>
                            </a>
                            <a href="/api/users/1/clubs" 
                               target="_blank" 
                               class="list-group-item list-group-item-action">
                                <i class="bi bi-link-45deg me-2"></i>
                                <strong>User ID 1:</strong> 
                                <code>/api/users/1/clubs</code>
                            </a>
                            <a href="/api/users/2/clubs" 
                               target="_blank" 
                               class="list-group-item list-group-item-action">
                                <i class="bi bi-link-45deg me-2"></i>
                                <strong>User ID 2:</strong> 
                                <code>/api/users/2/clubs</code>
                            </a>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> You must be logged in to access these URLs. The API requires authentication.
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>API Response:</h5>
                        <div id="apiResponse" class="alert alert-secondary" style="min-height: 200px;">
                            <em>Click "Test API" or use the direct links above to see the response.</em>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Current User Info:</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-1"><strong>ID:</strong> {{ auth()->id() ?? 'Not logged in' }}</p>
                                <p class="mb-1"><strong>Name:</strong> {{ auth()->user()->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ auth()->user()->email ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Role:</strong> 
                                    <span class="badge bg-{{ auth()->user()->role === 'student' ? 'success' : (auth()->user()->role === 'admin' ? 'danger' : 'primary') }}">
                                        {{ auth()->user()->role ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function testApi(userId) {
        if (!userId || userId <= 0) {
            alert('Please enter a valid User ID');
            return;
        }

        const responseDiv = document.getElementById('apiResponse');
        responseDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

        fetch(`/api/users/${userId}/clubs`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        })
        .then(async response => {
            const data = await response.json();
            
            if (!response.ok) {
                responseDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error (${response.status}):</strong> ${data.message || data.error || 'Unknown error'}
                        <pre class="mt-2 mb-0" style="background: rgba(0,0,0,0.1); padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 0.875rem;">${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
                return;
            }

            const clubs = data.data.clubs || [];
            let clubsHtml = '';

            if (clubs.length === 0) {
                clubsHtml = '<p class="text-muted mb-0">This user is not a member of any clubs.</p>';
            } else {
                clubs.forEach(club => {
                    clubsHtml += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">${club.name}</h6>
                                <p class="card-text text-muted small mb-2">${club.description || 'No description'}</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1 small"><strong>Status:</strong> <span class="badge bg-${club.status === 'active' ? 'success' : 'secondary'}">${club.status}</span></p>
                                        <p class="mb-1 small"><strong>Member Role:</strong> <span class="badge bg-info">${club.member_role || 'N/A'}</span></p>
                                        <p class="mb-1 small"><strong>Joined:</strong> ${club.joined_at ? new Date(club.joined_at).toLocaleString() : 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 small"><strong>Email:</strong> ${club.email || 'N/A'}</p>
                                        <p class="mb-1 small"><strong>Phone:</strong> ${club.phone || 'N/A'}</p>
                                        <p class="mb-0 small"><strong>Club ID:</strong> ${club.id}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            responseDiv.innerHTML = `
                <div class="alert alert-success mb-3">
                    <strong>Success!</strong> Found ${clubs.length} club(s) for user "${data.data.user_name}" (ID: ${data.data.user_id})
                </div>
                ${clubsHtml}
                <details class="mt-3">
                    <summary class="fw-bold" style="cursor: pointer;">View Raw JSON Response</summary>
                    <pre class="mt-2 mb-0" style="background: rgba(0,0,0,0.05); padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 0.875rem; max-height: 400px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
                </details>
            `;
        })
        .catch(error => {
            console.error('API Error:', error);
            responseDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Network Error:</strong> ${error.message}
                </div>
            `;
        });
    }

    // Test button handler
    document.getElementById('testBtn').addEventListener('click', function() {
        const userId = document.getElementById('userIdInput').value;
        testApi(parseInt(userId));
    });

    // Allow Enter key
    document.getElementById('userIdInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('testBtn').click();
        }
    });
</script>
@endpush
@endsection

