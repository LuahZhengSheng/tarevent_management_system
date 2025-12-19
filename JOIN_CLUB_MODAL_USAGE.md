# Join Club Modal - Developer Usage Guide

## Overview

The Join Club Modal is a reusable, Stripe-style modal component that allows students to request to join a club. It can be easily integrated into any part of the application, particularly useful for Event pages where club membership is required.

## Quick Start

### 1. Include the Modal

Add the modal to your Blade view using `@include`:

```blade
{{-- At the end of your view, before @endsection --}}
@include('clubs.join_modal')
```

**Important:** The modal should be included in views that extend `layouts.app` or have Bootstrap 5 and Bootstrap Icons loaded.

### 2. Call the Modal from JavaScript

Use the global function `window.openJoinClubModal()`:

```javascript
// Basic usage
window.openJoinClubModal(clubId);

// With callback (optional)
window.openJoinClubModal(clubId, function(clubId) {
    console.log('Join request submitted for club:', clubId);
    // Optional: Refresh page, update UI, etc.
});
```

## Usage Examples

### Example 1: Event Registration Button

When a user tries to register for a club-only event:

```blade
{{-- resources/views/events/show.blade.php --}}

@if($event->club_id && !auth()->user()->isClubMember($event->club_id))
    <button 
        type="button" 
        class="btn btn-primary"
        onclick="window.openJoinClubModal({{ $event->club_id }}, function(clubId) {
            // After successful join request, refresh the page or update UI
            location.reload();
        })">
        <i class="bi bi-people me-2"></i>
        Join Club to Register
    </button>
@endif

{{-- Include modal at the end --}}
@include('clubs.join_modal')
```

### Example 2: Event Registration Controller Logic

In your Event Registration Controller, check membership and show modal:

```php
// app/Http/Controllers/Event/EventRegistrationController.php

public function create(Event $event)
{
    // Check if event requires club membership
    if ($event->club_id) {
        $club = Club::find($event->club_id);
        $user = auth()->user();
        
        // Check if user is a member
        if (!$club->members()->where('users.id', $user->id)->exists()) {
            // Return view with flag to show join modal
            return view('events.register', [
                'event' => $event,
                'showJoinModal' => true,
                'clubId' => $event->club_id
            ]);
        }
    }
    
    return view('events.register', ['event' => $event]);
}
```

Then in the view:

```blade
{{-- resources/views/events/register.blade.php --}}

@if(isset($showJoinModal) && $showJoinModal)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.openJoinClubModal({{ $clubId }}, function(clubId) {
                // After join request, redirect to registration
                window.location.href = '{{ route("events.register", $event) }}';
            });
        });
    </script>
@endif

@include('clubs.join_modal')
```

### Example 3: AJAX Event Registration

When using AJAX for event registration:

```javascript
// In your event registration JavaScript

function registerForEvent(eventId, clubId) {
    // First check if user is club member
    fetch(`/api/events/${eventId}/check-membership`)
        .then(response => response.json())
        .then(data => {
            if (data.requires_club && !data.is_member) {
                // Show join club modal
                window.openJoinClubModal(clubId, function(clubId) {
                    // After join request submitted, show message
                    alert('Join request submitted. Please wait for approval before registering.');
                });
            } else {
                // Proceed with registration
                proceedWithRegistration(eventId);
            }
        });
}
```

### Example 4: Club List Page

On a page listing clubs:

```blade
{{-- resources/views/clubs/index.blade.php --}}

@foreach($clubs as $club)
    <div class="club-card">
        <h3>{{ $club->name }}</h3>
        <p>{{ $club->description }}</p>
        
        @if(auth()->check() && auth()->user()->role === 'student')
            @if(!$club->members->contains(auth()->id()))
                <button 
                    class="btn btn-primary"
                    onclick="window.openJoinClubModal({{ $club->id }})">
                    Request to Join
                </button>
            @else
                <span class="badge bg-success">Member</span>
            @endif
        @endif
    </div>
@endforeach

@include('clubs.join_modal')
```

### Example 5: Inline Button with Data Attributes

Using data attributes for cleaner HTML:

```blade
<button 
    class="btn btn-primary join-club-btn"
    data-club-id="{{ $club->id }}"
    data-redirect-url="{{ route('events.show', $event) }}">
    Join Club to Continue
</button>

<script>
document.querySelectorAll('.join-club-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const clubId = this.dataset.clubId;
        const redirectUrl = this.dataset.redirectUrl;
        
        window.openJoinClubModal(parseInt(clubId), function(clubId) {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    });
});
</script>

@include('clubs.join_modal')
```

## API Integration

The modal automatically handles the API call to `/api/clubs/{club_id}/join`:

**Request:**
```json
POST /api/clubs/{club_id}/join
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
    "reason": "Optional reason text",
    "agree": true
}
```

**Success Response:**
```json
{
    "success": true,
    "message": "Join request submitted successfully."
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Error details"]
    }
}
```

## Modal Behavior

### On Success:
1. Shows success message with checkmark icon
2. Automatically closes modal after 2 seconds
3. Executes callback function if provided
4. Does NOT reload the page

### On Error:
1. Shows error message in red alert box
2. Modal stays open
3. User can fix errors and resubmit
4. Does NOT reload the page

## Requirements

### Backend Requirements:
- User must be authenticated
- User must have role `'student'` (enforced by `ClubFacade`)
- Club must exist
- User must not already be a member
- User must not have a pending join request

### Frontend Requirements:
- Bootstrap 5.3+ must be loaded
- Bootstrap Icons must be loaded
- CSRF token must be available in `<meta name="csrf-token">`
- Modal must be included in the view

## Common Error Scenarios

### 1. "Only students are allowed to request to join clubs"
- **Cause:** User role is not 'student'
- **Solution:** Check user role before showing the modal

### 2. "User is already a member of this club"
- **Cause:** User is already a club member
- **Solution:** Check membership before showing join button

### 3. "A pending join request already exists"
- **Cause:** User already has a pending request
- **Solution:** Check for existing requests and show appropriate message

### 4. "Club ID is required"
- **Cause:** `openJoinClubModal()` called without club ID
- **Solution:** Always pass a valid club ID

## Best Practices

### 1. Check Membership Before Showing Modal
```php
// In Controller
$isMember = $club->members()->where('users.id', auth()->id())->exists();
$hasPendingRequest = ClubJoinRequest::where('club_id', $club->id)
    ->where('user_id', auth()->id())
    ->where('status', 'pending')
    ->exists();
```

### 2. Provide User Feedback
```javascript
window.openJoinClubModal(clubId, function(clubId) {
    // Show toast notification
    showToast('Join request submitted. Please wait for approval.', 'success');
    
    // Update UI
    updateJoinButtonState(clubId);
});
```

### 3. Handle Loading States
```javascript
// Disable button while modal is open
const btn = document.getElementById('joinBtn');
btn.disabled = true;

window.openJoinClubModal(clubId, function(clubId) {
    btn.disabled = false;
    btn.textContent = 'Request Pending';
});
```

### 4. Include Modal Once Per Page
```blade
{{-- Include at the end of your main layout or once per page --}}
@include('clubs.join_modal')
```

## Testing

### Manual Testing:
1. Navigate to `/test/join-club-modal`
2. Enter a Club ID
3. Click "Open Join Club Modal"
4. Fill in the form and submit
5. Verify success/error handling

### Integration Testing:
```javascript
// Test in browser console
window.openJoinClubModal(1, function(clubId) {
    console.log('Test successful for club:', clubId);
});
```

## Troubleshooting

### Modal doesn't open:
1. Check browser console for errors
2. Verify Bootstrap is loaded: `typeof bootstrap`
3. Verify function exists: `typeof window.openJoinClubModal`
4. Verify modal element exists: `document.getElementById('joinClubModal')`

### Form doesn't submit:
1. Check CSRF token is present
2. Verify user is authenticated
3. Check user role is 'student'
4. Verify club ID is valid

### Styling issues:
1. Ensure Bootstrap CSS is loaded
2. Check for CSS conflicts
3. Verify modal is included in correct location

## Support

For issues or questions:
1. Check browser console for errors
2. Verify all requirements are met
3. Test with the provided test page: `/test/join-club-modal`

