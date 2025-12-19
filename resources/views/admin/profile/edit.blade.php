<!-- Author: Tang Lit Xuan -->
@extends('layouts.admin')

@section('title', 'Admin Profile - TAREvent')

@push('styles')
<style>
    .admin-profile-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 968px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }
    }

    .profile-header {
        margin-bottom: 2.5rem;
    }

    .profile-title {
        font-size: 2rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }

    .profile-subtitle {
        font-size: 0.9375rem;
        color: var(--text-secondary);
    }

    .profile-section {
        background: var(--bg-primary);
        border-radius: 1rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        padding: 2.5rem;
        margin-bottom: 1.5rem;
        transition: box-shadow 0.2s ease;
    }

    .profile-section:hover {
        box-shadow: var(--shadow-md);
    }

    .profile-section-header {
        margin-bottom: 1.75rem;
    }

    .profile-section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.375rem;
    }

    .profile-section-subtitle {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }
</style>
@endpush

@section('content')
<div class="admin-profile-page">
    <div class="profile-header">
        <h1 class="profile-title">Admin Profile Settings</h1>
        <p class="profile-subtitle">Manage your administrator account information and preferences</p>
    </div>

    <div class="profile-layout">
        <!-- Left Column: Profile Information -->
        <div class="profile-section">
            @include('admin.profile.partials.update-profile-information-form')
        </div>

        <!-- Right Column: Password -->
        <div class="profile-section">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</div>
@endsection

