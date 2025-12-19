<div class="profile-section-header">
    <h2 class="profile-section-title">Profile Information</h2>
    <p class="profile-section-subtitle">Update your account's profile information and preferences.</p>
</div>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ request()->routeIs('admin.profile.*') ? route('admin.profile.update') : route('profile.update') }}" novalidate enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="mb-4">
        <label class="auth-label">Profile Avatar</label>
        <div class="profile-avatar-container">
            <div class="profile-avatar-preview">
                <label for="avatar" class="avatar-wrapper">
                    <img
                        src="{{ $user->profile_photo_url }}"
                        alt="Avatar"
                        class="avatar-image"
                        id="avatarPreview"
                        onerror="this.onerror=null; this.src='{{ asset('images/avatar/default-student-avatar.png') }}';"
                    >
                    <div class="avatar-overlay">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                </label>
                <label for="avatar" class="avatar-upload-btn">
                    <i class="bi bi-upload me-2"></i>
                    <span>Change Photo</span>
                </label>
            </div>
            <input
                id="avatar"
                name="avatar"
                type="file"
                accept="image/*"
                class="avatar-input @error('avatar') is-invalid @enderror"
            >
            <p class="avatar-hint">
                <i class="bi bi-info-circle me-1"></i>
                Recommended: Square image, JPG or PNG, max 2MB
            </p>
            @error('avatar')
                <div class="invalid-feedback" style="margin-top: 0.5rem;">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="name" class="auth-label">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $user->name) }}"
            required
            autocomplete="name"
            class="auth-input @error('name') is-invalid @enderror"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="auth-label">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $user->email) }}"
            readonly
            class="auth-input auth-input-readonly"
            style="background-color: var(--bg-secondary); cursor: not-allowed;"
        >

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div style="margin-top: 0.75rem; padding: 1rem 1.25rem; background: var(--warning-light); border-radius: 0.5rem; font-size: 0.875rem; color: var(--warning); display: flex; align-items: flex-start; gap: 0.75rem;">
                <i class="bi bi-exclamation-triangle" style="font-size: 1.125rem; margin-top: 0.125rem; flex-shrink: 0;"></i>
                <div>
                    <p style="margin: 0 0 0.5rem 0;">
                        Your email address is unverified.
                    </p>
                    <button form="send-verification" type="submit" style="background: none; border: none; color: var(--warning); text-decoration: underline; cursor: pointer; padding: 0; font-size: 0.875rem;">
                        Click here to re-send the verification email.
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p style="margin: 0.5rem 0 0 0; font-weight: 500;">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="mb-3">
        <label for="student_id" class="auth-label">Student ID</label>
        <input
            id="student_id"
            name="student_id"
            type="text"
            value="{{ old('student_id', $user->student_id) }}"
            readonly
            class="auth-input auth-input-readonly"
            style="background-color: var(--bg-secondary); cursor: not-allowed;"
        >
    </div>

    <div class="mb-3">
        <label for="phone" class="auth-label">Phone</label>
        <input
            id="phone"
            name="phone"
            type="tel"
            value="{{ old('phone', $user->phone) }}"
            autocomplete="tel"
            class="auth-input @error('phone') is-invalid @enderror"
            placeholder="e.g., +60 12-345 6789"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="program" class="auth-label">Program</label>
        <select
            id="program"
            name="program"
            disabled
            class="auth-input auth-select auth-input-readonly"
            style="background-color: var(--bg-secondary); cursor: not-allowed;"
        >
            <option value="">Select a program</option>
            @php
                $programOptions = [
                    // Computing / IT
                    'BCS' => 'Bachelor of Computer Science',
                    'BIT' => 'Bachelor of Information Technology',
                    'BSE' => 'Bachelor of Software Engineering',
                    'BDS' => 'Bachelor of Data Science',
                    'BCY' => 'Bachelor of Cyber Security',
                    'BIS' => 'Bachelor of Information Systems',
                    // Engineering
                    'BEEE' => 'Bachelor of Electrical and Electronic Engineering',
                    'BCHE' => 'Bachelor of Chemical Engineering',
                    'BCIV' => 'Bachelor of Civil Engineering',
                    'BME' => 'Bachelor of Mechanical Engineering',
                    // Business / Finance
                    'BBA' => 'Bachelor of Business Administration',
                    'BACC' => 'Bachelor of Accounting',
                    'BFIN' => 'Bachelor of Finance',
                    'BMM' => 'Bachelor of Marketing Management',
                    'BIBM' => 'Bachelor of International Business Management',
                    // Science
                    'BSCM' => 'Bachelor of Science (Mathematics)',
                    'BSCP' => 'Bachelor of Science (Physics)',
                    'BSCC' => 'Bachelor of Science (Chemistry)',
                    'BSCB' => 'Bachelor of Science (Biology)',
                    // Arts / Social Science
                    'BENG' => 'Bachelor of Arts (English Language)',
                    'BCOMM' => 'Bachelor of Communication',
                    'BPSY' => 'Bachelor of Psychology',
                    // Others / Generic
                    'DIP' => 'Diploma Programme',
                    'FOUND' => 'Foundation Programme',
                    'OTH' => 'Other',
                ];
            @endphp
            @foreach($programOptions as $value => $label)
                <option value="{{ $value }}" {{ old('program', $user->program) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <button type="submit" class="auth-button" style="width: auto; padding: 0.75rem 1.75rem;">
            Save Changes
        </button>

        @if (session('status') === 'profile-updated')
            <p style="margin: 0; font-size: 0.875rem; color: var(--success); font-weight: 500;">
                Saved.
            </p>
        @endif
    </div>
</form>

<style>
    .auth-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .auth-input {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9375rem;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .auth-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .auth-button {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .auth-button:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        padding: 0.75rem 1rem;
        background: var(--error-light);
        color: var(--error);
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .invalid-feedback ul {
        margin: 0.25rem 0 0 0;
        padding-left: 1.25rem;
    }

    .invalid-feedback li {
        margin-bottom: 0.25rem;
    }

    .invalid-feedback li:last-child {
        margin-bottom: 0;
    }

    .is-invalid {
        /* 仅用文字提示错误，不再用红色高亮边框，保持简约风格 */
        border-color: var(--border-color);
        box-shadow: none;
    }

    .is-invalid:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    .auth-input-readonly:focus {
        outline: none;
        border-color: var(--border-color);
        box-shadow: none;
    }

    .profile-avatar-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .profile-avatar-preview {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .avatar-wrapper {
        position: relative;
        width: 96px;
        height: 96px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--border-color);
        background: var(--bg-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
    }

    .avatar-wrapper:hover {
        border-color: var(--primary);
        transform: scale(1.05);
        box-shadow: 0 0 0 4px var(--primary-light);
    }

    .avatar-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .avatar-wrapper:hover .avatar-overlay {
        opacity: 1;
    }

    .avatar-upload-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border-color);
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--text-primary);
        background: var(--bg-primary);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .avatar-upload-btn:hover {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .avatar-input {
        display: none;
    }

    .avatar-hint {
        margin: 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
    }

    .auth-input-readonly {
        opacity: 0.7;
    }

    .field-hint {
        margin: 0.5rem 0 0 0;
        font-size: 0.8125rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
    }

    .auth-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .auth-select:focus {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%232563eb' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const defaultAvatar = avatarPreview.src;

        if (avatarInput) {
            avatarInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        avatarInput.value = '';
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        avatarInput.value = '';
                        return;
                    }

                    // Preview image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Reset to default if no file selected
                    avatarPreview.src = defaultAvatar;
                }
            });
        }
    });
</script>
@endpush
