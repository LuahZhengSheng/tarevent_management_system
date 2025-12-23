@extends('layouts.app')

@section('title', $notification->title)

@push('styles')
<style>
    .notification-detail-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        text-decoration: none;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .back-button:hover {
        color: var(--primary);
        transform: translateX(-3px);
    }

    .notification-detail-card {
        background: var(--bg-primary);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: var(--shadow-md);
    }

    .notification-detail-header {
        display: flex;
        align-items: start;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .notification-detail-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }

    .notification-detail-icon.event_updated {
        background: var(--info-light);
        color: var(--info);
    }

    .notification-detail-icon.event_cancelled {
        background: var(--error-light);
        color: var(--error);
    }

    .notification-detail-icon.event_time_changed,
    .notification-detail-icon.event_venue_changed {
        background: var(--warning-light);
        color: var(--warning);
    }

    .notification-detail-icon.registration_confirmed,
    .notification-detail-icon.payment_confirmed {
        background: var(--success-light);
        color: var(--success);
    }

    .notification-detail-info {
        flex: 1;
    }

    .notification-detail-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .notification-detail-meta {
        display: flex;
        gap: 1.5rem;
        font-size: 0.9rem;
        color: var(--text-tertiary);
        flex-wrap: wrap;
    }

    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .priority-urgent {
        background: var(--error-light);
        color: var(--error);
    }

    .priority-high {
        background: var(--warning-light);
        color: var(--warning);
    }

    .priority-normal {
        background: var(--info-light);
        color: var(--info);
    }

    .notification-detail-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-primary);
        margin-bottom: 2rem;
    }

    .event-info-card {
        background: var(--bg-secondary);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .event-info-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .event-info-grid {
        display: grid;
        gap: 1rem;
    }

    .event-info-item {
        display: flex;
        align-items: start;
        gap: 0.75rem;
    }

    .event-info-item i {
        color: var(--primary);
        font-size: 1.25rem;
        margin-top: 0.125rem;
    }

    .event-info-label {
        font-weight: 600;
        color: var(--text-secondary);
        min-width: 100px;
    }

    .event-info-value {
        color: var(--text-primary);
        flex: 1;
    }

    .changes-card {
        background: var(--warning-light);
        border-left: 4px solid var(--warning);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .changes-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .change-item {
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--bg-primary);
        border-radius: 0.5rem;
    }

    .change-item:last-child {
        margin-bottom: 0;
    }

    .change-label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        text-transform: capitalize;
    }

    .change-values {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .old-value {
        color: var(--error);
        text-decoration: line-through;
    }

    .new-value {
        color: var(--success);
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-action-detail {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary-detail {
        background: var(--primary);
        color: white;
    }

    .btn-primary-detail:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        color: white;
    }

    .btn-secondary-detail {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary-detail:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    @media (max-width: 768px) {
        .notification-detail-card {
            padding: 1.5rem;
        }

        .notification-detail-header {
            flex-direction: column;
            text-align: center;
        }

        .notification-detail-icon {
            margin: 0 auto;
        }

        .notification-detail-meta {
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action-detail {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="notification-detail-container">
    <a href="{{ route('notifications.index') }}" class="back-button">
        <i class="bi bi-arrow-left"></i> Back to Notifications
    </a>

    <div class="notification-detail-card">
        <div class="notification-detail-header">
            <div class="notification-detail-icon {{ $notification->type }}">
                <i class="bi {{ $notification->icon }}"></i>
            </div>
            <div class="notification-detail-info">
                <h1 class="notification-detail-title">{{ $notification->title }}</h1>
                <div class="notification-detail-meta">
                    <span><i class="bi bi-clock"></i> {{ $notification->created_at->format('F j, Y @ g:i A') }}</span>
                    <span><i class="bi bi-arrow-clockwise"></i> {{ $notification->time_ago }}</span>
                    @if($notification->priority !== 'normal')
                    <span class="priority-badge priority-{{ $notification->priority }}">
                        {{ ucfirst($notification->priority) }} Priority
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="notification-detail-content">
            {{ $notification->message }}
        </div>

        @if($notification->data && isset($notification->data['event_id']))
        @php
            $eventId = $notification->data['event_id'];
            $event = \App\Models\Event::find($eventId);
        @endphp

        @if($event)
        <div class="event-info-card">
            <div class="event-info-title">
                <i class="bi bi-calendar-event"></i>
                Event Details
            </div>
            <div class="event-info-grid">
                <div class="event-info-item">
                    <i class="bi bi-bookmark"></i>
                    <div>
                        <div class="event-info-label">Event</div>
                        <div class="event-info-value">{{ $event->title }}</div>
                    </div>
                </div>
                <div class="event-info-item">
                    <i class="bi bi-clock"></i>
                    <div>
                        <div class="event-info-label">Date & Time</div>
                        <div class="event-info-value">{{ $event->start_time->format('l, F j, Y @ g:i A') }}</div>
                    </div>
                </div>
                <div class="event-info-item">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <div class="event-info-label">Venue</div>
                        <div class="event-info-value">{{ $event->venue }}</div>
                    </div>
                </div>
                <div class="event-info-item">
                    <i class="bi bi-tag"></i>
                    <div>
                        <div class="event-info-label">Category</div>
                        <div class="event-info-value">{{ $event->category }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($notification->data['changes']))
        <div class="changes-card">
            <div class="changes-title">
                <i class="bi bi-arrow-repeat"></i>
                What Changed
            </div>
            @foreach($notification->data['changes'] as $field => $change)
            <div class="change-item">
                <div class="change-label">{{ str_replace('_', ' ', $field) }}</div>
                <div class="change-values">
                    <div class="old-value">
                        @if(strpos($field, 'time') !== false)
                            {{ \Carbon\Carbon::parse($change['old'])->format('M j, Y @ g:i A') }}
                        @else
                            {{ $change['old'] }}
                        @endif
                    </div>
                    <i class="bi bi-arrow-right"></i>
                    <div class="new-value">
                        @if(strpos($field, 'time') !== false)
                            {{ \Carbon\Carbon::parse($change['new'])->format('M j, Y @ g:i A') }}
                        @else
                            {{ $change['new'] }}
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="action-buttons">
            @if($event && $event->status !== 'cancelled')
            <a href="{{ route('events.show', $event) }}" class="btn-action-detail btn-primary-detail">
                <i class="bi bi-calendar-event"></i>
                View Event Details
            </a>
            @endif
            <a href="{{ route('events.my') }}" class="btn-action-detail btn-secondary-detail">
                <i class="bi bi-bookmark"></i>
                My Events
            </a>
        </div>
        @endif
    </div>
</div>
@endsection