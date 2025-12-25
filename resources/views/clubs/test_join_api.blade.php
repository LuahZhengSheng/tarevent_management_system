@extends('layouts.app')

@section('title', 'Test Club Join API - TAREvent')

@push('styles')
<style>
    .test-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 2rem;
    }
    .test-section {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .test-section h2 {
        margin-bottom: 1.5rem;
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #555;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }
    .form-control:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .btn-test {
        background: #007bff;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-test:hover {
        background: #0056b3;
    }
    .btn-test:disabled {
        background: #ccc;
        cursor: not-allowed;
    }
    .response-section {
        margin-top: 2rem;
    }
    .response-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 1rem;
        margin-top: 1rem;
        max-height: 500px;
        overflow-y: auto;
    }
    .response-box pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
    .response-success {
        border-color: #28a745;
        background: #d4edda;
    }
    .response-error {
        border-color: #dc3545;
        background: #f8d7da;
    }
    .loading {
        display: none;
        text-align: center;
        padding: 1rem;
        color: #007bff;
    }
    .loading.show {
        display: block;
    }
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #007bff;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 4px;
    }
    .info-box p {
        margin: 0.5rem 0;
    }
    .info-box code {
        background: rgba(0,0,0,0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
</style>
@endpush

@section('content')
<div class="test-container">
    <div class="test-section">
        <h2>🧪 Test Club Join API</h2>
        
        <div class="info-box">
            <p><strong>API Endpoint:</strong> <code>POST /api/clubs/{club}/join</code></p>
            <p><strong>Required:</strong> Authentication (login required)</p>
            <p><strong>Required Parameters:</strong></p>
            <ul>
                <li><code>timestamp</code> (string, format: YYYY-MM-DD HH:MM:SS) <strong>OR</strong> <code>requestID</code> (string)</li>
                <li><code>agree</code> (boolean, required: true)</li>
            </ul>
            <p><strong>Optional Parameters:</strong></p>
            <ul>
                <li><code>reason</code> (string, max: 500 characters)</li>
            </ul>
        </div>

        <form id="joinApiForm">
            <div class="form-group">
                <label class="form-label" for="clubId">Club ID *</label>
                <input 
                    type="number" 
                    id="clubId" 
                    name="club_id" 
                    class="form-control" 
                    placeholder="Enter Club ID (e.g., 1, 2, 3...)" 
                    required
                    min="1"
                >
                <small class="text-muted">The ID of the club you want to join</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="reason">Reason (Optional)</label>
                <textarea 
                    id="reason" 
                    name="reason" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Why do you want to join this club? (max 500 characters)"
                    maxlength="500"
                ></textarea>
                <small class="text-muted">Maximum 500 characters</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="timestamp">Timestamp (Optional)</label>
                <input 
                    type="text" 
                    id="timestamp" 
                    name="timestamp" 
                    class="form-control" 
                    placeholder="YYYY-MM-DD HH:MM:SS (e.g., 2025-12-25 10:30:00)"
                    pattern="^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$"
                >
                <small class="text-muted">Format: YYYY-MM-DD HH:MM:SS (will auto-generate if left empty)</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="requestId">Request ID (Optional)</label>
                <input 
                    type="text" 
                    id="requestId" 
                    name="requestID" 
                    class="form-control" 
                    placeholder="Leave empty to auto-generate"
                >
                <small class="text-muted">UUID format (will auto-generate if both timestamp and requestID are empty)</small>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input 
                        type="checkbox" 
                        id="agree" 
                        name="agree" 
                        value="1"
                        required
                    >
                    <label class="form-label" for="agree" style="margin: 0; cursor: pointer;">
                        I agree to the club terms and conditions *
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-test" id="submitBtn">
                Send Request
            </button>
        </form>

        <div class="loading" id="loading">
            <p>⏳ Sending request...</p>
        </div>

        <div class="response-section" id="responseSection" style="display: none;">
            <h3>Response</h3>
            <div class="response-box" id="responseBox">
                <pre id="responseContent"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('joinApiForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const responseSection = document.getElementById('responseSection');
    const responseBox = document.getElementById('responseBox');
    const responseContent = document.getElementById('responseContent');

    // Auto-generate timestamp if not provided
    const timestampInput = document.getElementById('timestamp');
    if (!timestampInput.value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        timestampInput.value = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form data
        const clubId = document.getElementById('clubId').value;
        if (!clubId) {
            alert('Please enter a Club ID');
            return;
        }

        // Build request data
        const formData = new FormData(form);
        const requestData = {
            reason: formData.get('reason') || null,
            agree: formData.get('agree') === '1',
        };

        // Add timestamp or requestID
        const timestamp = formData.get('timestamp');
        const requestID = formData.get('requestID');
        
        if (timestamp) {
            requestData.timestamp = timestamp;
        } else if (requestID) {
            requestData.requestID = requestID;
        } else {
            // Auto-generate timestamp
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            requestData.timestamp = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        // Show loading
        submitBtn.disabled = true;
        loading.classList.add('show');
        responseSection.style.display = 'none';

        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Send request
            const response = await fetch(`/api/clubs/${clubId}/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(requestData),
            });

            // Get response data
            const data = await response.json();
            const status = response.status;

            // Display response
            responseSection.style.display = 'block';
            
            // Format response for display
            const formattedResponse = {
                statusCode: status,
                statusText: response.statusText,
                headers: {
                    'content-type': response.headers.get('content-type'),
                },
                body: data,
            };

            responseContent.textContent = JSON.stringify(formattedResponse, null, 2);

            // Style response box based on status
            responseBox.className = 'response-box';
            if (status >= 200 && status < 300) {
                responseBox.classList.add('response-success');
            } else {
                responseBox.classList.add('response-error');
            }

        } catch (error) {
            // Display error
            responseSection.style.display = 'block';
            responseBox.className = 'response-box response-error';
            responseContent.textContent = JSON.stringify({
                error: 'Network Error',
                message: error.message,
            }, null, 2);
        } finally {
            submitBtn.disabled = false;
            loading.classList.remove('show');
        }
    });
});
</script>
@endpush

