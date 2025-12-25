@extends('layouts.app')

@section('title', $event->title . ' - Event Details')

@section('content')
<div class="event-detail-page">
    <!-- Hero Section with Event Poster -->
    <div class="event-hero">
        <div class="hero-overlay"></div>
        @if($event->poster_path)
        <img src="{{ Storage::url('event-posters/'.$event->poster_path) }}" 
             alt="{{ $event->title }}" 
             class="hero-background">
        @else
        <div class="hero-background-placeholder">
            <i class="bi bi-calendar-event"></i>
        </div>
        @endif

        <div class="container">
            <div class="hero-content">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb-custom">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('events.index') }}">Events</a></li>
                        <li>{{ Str::limit($event->title, 30) }}</li>
                    </ol>
                </nav>

                <!-- Event Category Badge -->
                <div class="event-meta-tags">
                    <span class="meta-tag category-tag">
                        <i class="bi bi-bookmark-fill me-1"></i>
                        {{ $event->category }}
                    </span>
                    @if($event->is_paid)
                    <span class="meta-tag price-tag">
                        <i class="bi bi-cash-coin me-1"></i>
                        RM {{ number_format($event->fee_amount, 2) }}
                    </span>
                    @else
                    <span class="meta-tag free-tag">
                        <i class="bi bi-gift-fill me-1"></i>
                        Free Event
                    </span>
                    @endif

                    {{-- Status Badge for Organizers/Admins --}}
                    @auth
                    @if($canManageEvent)
                    <span class="meta-tag status-tag status-{{ $event->status }}">
                        <i class="bi bi-circle-fill me-1"></i>
                        {{ ucfirst($event->status) }}
                    </span>
                    @endif
                    @endauth
                </div>

                <!-- Event Title -->
                <h1 class="event-title-hero">{{ $event->title }}</h1>

                <!-- Event Quick Info -->
                <div class="event-quick-info">
                    <div class="quick-info-item">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ $event->start_time->format('d M Y') }}</span>
                    </div>
                    <div class="quick-info-divider"></div>
                    <div class="quick-info-item">
                        <i class="bi bi-clock"></i>
                        <span>{{ $event->start_time->format('h:i A') }}</span>
                    </div>
                    <div class="quick-info-divider"></div>
                    <div class="quick-info-item">
                        <i class="bi bi-geo-alt"></i>
                        <span>{{ $event->venue }}</span>
                    </div>
                    @if($event->max_participants)
                    <div class="quick-info-divider"></div>
                    <div class="quick-info-item">
                        <i class="bi bi-people"></i>
                        <span>{{ $event->remaining_seats }} / {{ $event->max_participants }} slots</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container event-content-container">
        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                {{-- Event Stage Status for Managers --}}
                @auth
                @if($canManageEvent)
                {{-- Draft Status --}}
                @if($stage === 'draft')
                <div class="alert-custom alert-info">
                    <div class="alert-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Draft Event</h4>
                        <p>This event is in draft status and not visible to students.</p>
                        @if($timeInfo['registration_starts_in'])
                        <p class="mb-0"><strong>Registration opens:</strong> {{ $timeInfo['registration_starts_in'] }}</p>
                        @endif
                        @if($timeInfo['event_starts_in'])
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Pending Approval Status --}}
                @if($stage === 'pending')
                <div class="alert-custom alert-warning">
                    <div class="alert-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Pending Admin Approval</h4>
                        <p>This event is awaiting admin approval. Students cannot see this event until it's approved and published.</p>
                        @if($timeInfo['registration_starts_in'])
                        <p class="mb-0"><strong>Registration opens:</strong> {{ $timeInfo['registration_starts_in'] }}</p>
                        @endif
                        @if($timeInfo['event_starts_in'])
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Pre-Registration Stage --}}
                @if($stage === 'pre_registration')
                <div class="alert-custom alert-info">
                    <div class="alert-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Not Yet Open</h4>
                        <p><strong>Registration opens:</strong> {{ $timeInfo['registration_starts_in'] }}</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                    </div>
                </div>
                @endif

                {{-- Registration Open Stage --}}
                @if($stage === 'registration_open')
                <div class="alert-custom alert-success">
                    <div class="alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Open</h4>
                        <p><strong>Registration closes:</strong> {{ $timeInfo['registration_ends_in'] }}</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        <p class="mb-0"><strong>Current registrations:</strong> {{ $event->registrations()->where('status', ['confirmed', 'pending_payment'])->count() }}
                            @if($event->max_participants)
                            / {{ $event->max_participants }}
                            @endif
                        </p>
                    </div>
                </div>
                @endif

                {{-- Registration Closed but Event Not Started --}}
                @if($stage === 'registration_closed')
                <div class="alert-custom alert-warning">
                    <div class="alert-icon">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Closed</h4>
                        <p>Registration period has ended.</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        <p class="mb-0"><strong>Total registered:</strong> {{ $event->registrations()->where('status', 'confirmed')->count() }}</p>
                    </div>
                </div>
                @endif

                {{-- Event Ongoing --}}
                @if($stage === 'ongoing')
                <div class="alert-custom alert-primary">
                    <div class="alert-icon">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event In Progress</h4>
                        <p>This event is currently ongoing.</p>
                        @if($timeInfo['event_ends_in'])
                        <p class="mb-0"><strong>Event ends:</strong> {{ $timeInfo['event_ends_in'] }}</p>
                        @endif
                        <p class="mb-0"><strong>Total attendees:</strong> {{ $event->registrations()->where('status', 'confirmed')->count() }}</p>
                    </div>
                </div>
                @endif

                {{-- Event Past --}}
                @if($stage === 'past')
                <div class="alert-custom alert-secondary">
                    <div class="alert-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event Completed</h4>
                        <p>This event has ended {{ $timeInfo['event_ended'] }}.</p>
                        <p class="mb-0"><strong>Total attendees:</strong> {{ $event->registrations()->where('status', 'confirmed')->count() }}</p>
                    </div>
                </div>
                @endif

                {{-- Cancelled Event --}}
                @if($stage === 'cancelled')
                <div class="alert-custom alert-danger">
                    <div class="alert-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event Cancelled</h4>
                        <p>{{ $event->cancellation_reason ?? 'This event has been cancelled.' }}</p>
                    </div>
                </div>
                @endif
                @endif
                @endauth

                {{-- Event Status for Regular Users --}}
                @auth
                @if(!$canManageEvent)
                {{-- User Already Registered --}}
                @if($isRegistered)
                <div class="alert-custom alert-success">
                    <div class="alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>You're Registered!</h4>
                        <p>You have successfully registered for this event.</p>
                        @if($stage === 'registration_open' && $timeInfo['event_starts_in'])
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        @elseif($stage === 'ongoing' && $timeInfo['event_ends_in'])
                        <p class="mb-0"><strong>Event is ongoing and ends:</strong> {{ $timeInfo['event_ends_in'] }}</p>
                        @elseif($stage === 'past')
                        <p class="mb-0">This event has ended. Thank you for participating!</p>
                        @endif

                        @if($userRegistration->status === 'pending_payment')
                        <a href="{{ route('registrations.payment', $userRegistration) }}" class="btn btn-sm btn-light mt-2">
                            <i class="bi bi-credit-card me-1"></i>
                            Complete Payment
                        </a>
                        @endif
                    </div>
                </div>
                @else
                {{-- Registration Not Open Yet --}}
                @if($stage === 'pre_registration')
                <div class="alert-custom alert-info">
                    <div class="alert-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Opening Soon</h4>
                        <p><strong>Registration opens:</strong> {{ $timeInfo['registration_starts_in'] }}</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                    </div>
                </div>
                @endif

                {{-- Registration Closed --}}
                @if(in_array($stage, ['registration_closed', 'ongoing']))
                <div class="alert-custom alert-warning">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Closed</h4>
                        @if($stage === 'ongoing')
                        <p>This event is currently in progress. Registration is no longer available.</p>
                        <p class="mb-0"><strong>Event ends:</strong> {{ $timeInfo['event_ends_in'] }}</p>
                        @else
                        <p>Registration period has ended.</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Event Full --}}
                @if($event->is_full && $stage === 'registration_open')
                <div class="alert-custom alert-warning">
                    <div class="alert-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event Full</h4>
                        <p>This event has reached maximum capacity.</p>
                        <p class="mb-0"><strong>Event starts:</strong> {{ $timeInfo['event_starts_in'] }}</p>
                    </div>
                </div>
                @endif

                {{-- Event Past --}}
                @if($stage === 'past')
                <div class="alert-custom alert-secondary">
                    <div class="alert-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event Ended</h4>
                        <p class="mb-0">This event has ended {{ $timeInfo['event_ended'] }}.</p>
                    </div>
                </div>
                @endif

                {{-- Cancelled Event --}}
                @if($stage === 'cancelled')
                <div class="alert-custom alert-danger">
                    <div class="alert-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Event Cancelled</h4>
                        <p class="mb-0">{{ $event->cancellation_reason ?? 'This event has been cancelled.' }}</p>
                    </div>
                </div>
                @endif
                @endif
                @endif
                @endauth

                <!-- Event Description -->
                <div class="content-card">
                    <h2 class="section-title">
                        <i class="bi bi-file-text me-2"></i>
                        About This Event
                    </h2>
                    <div class="event-description">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>

                <!-- Event Details -->
                <div class="content-card">
                    <h2 class="section-title">
                        <i class="bi bi-info-circle me-2"></i>
                        Event Details
                    </h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-calendar-range"></i>
                                Event Date & Time
                            </div>
                            <div class="detail-value event-time">
                                <div class="date"><strong>{{ $event->start_time->format('l, d F Y') }}</strong></div>
                                <div class="time">{{ $event->start_time->format('h:i A') }} - {{ $event->end_time->format('h:i A') }}</div>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-geo-alt-fill"></i>
                                Venue
                            </div>
                            <div class="detail-value">
                                {{ $event->venue }}
                                @if($event->location_map_url)
                                <br>
                                <a href="{{ $event->location_map_url }}" target="_blank" class="link-with-icon">
                                    <i class="bi bi-map me-1"></i>View on Map
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-bookmark"></i>
                                Category
                            </div>
                            <div class="detail-value">
                                {{ $event->category }}
                            </div>
                        </div>

                        @if($event->max_participants)
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-people"></i>
                                Capacity
                            </div>
                            <div class="detail-value">
                                <strong>{{ $event->remaining_seats }}</strong> seats remaining out of {{ $event->max_participants }}
                                <div class="capacity-bar">
                                    @php
                                    $occupied = $event->max_participants - $event->remaining_seats;
                                    $percentage = ($occupied / $event->max_participants) * 100;
                                    @endphp
                                    <div class="capacity-fill" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($event->is_paid)
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-cash-coin"></i>
                                Registration Fee
                            </div>
                            <div class="detail-value">
                                <strong class="text-primary">RM {{ number_format($event->fee_amount, 2) }}</strong>
                                @if($event->refund_available)
                                <br><small class="text-success"><i class="bi bi-check-circle me-1"></i>Refundable</small>
                                @else
                                <br><small class="text-muted"><i class="bi bi-x-circle me-1"></i>Non-refundable</small>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-clock-history"></i>
                                Registration Period
                            </div>
                            <div class="detail-value reg-chip">
                                <div class="reg-item">
                                    <span class="reg-label">From</span>
                                    <span class="reg-time">
                                        {{ $event->registration_start_time->format('d M Y, h:i A') }}
                                    </span>
                                </div>
                                <div class="reg-sep">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                                <div class="reg-item">
                                    <span class="reg-label">To</span>
                                    <span class="reg-time">
                                        {{ $event->registration_end_time->format('d M Y, h:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requirements -->
                @if($event->requirements)
                <div class="content-card">
                    <h2 class="section-title">
                        <i class="bi bi-list-check me-2"></i>
                        Requirements
                    </h2>
                    <ul class="requirements-list">
                        @foreach($event->requirements as $requirement)
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $requirement }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Contact Information -->
                <div class="content-card">
                    <h2 class="section-title">
                        <i class="bi bi-telephone me-2"></i>
                        Contact Information
                    </h2>
                    <div class="contact-grid">
                        @if($event->contact_email)
                        <a href="mailto:{{ $event->contact_email }}" class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">{{ $event->contact_email }}</div>
                            </div>
                        </a>
                        @endif

                        @if($event->contact_phone)
                        <a href="tel:{{ $event->contact_phone }}" class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-phone-fill"></i>
                            </div>
                            <div class="contact-details">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">{{ $event->contact_phone }}</div>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Tags -->
                @if($event->tags)
                <div class="content-card">
                    <h2 class="section-title">
                        <i class="bi bi-tags me-2"></i>
                        Tags
                    </h2>
                    <div class="tags-container">
                        @foreach($event->tags as $tag)
                        <span class="tag-badge">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                @auth
                {{-- Management Actions for Organizers/Admins --}}
                @if($canManageEvent)
                <div class="sidebar-card sticky-sidebar">
                    <div class="card-header-custom">
                        <h3>Event Management</h3>
                    </div>
                    <div class="card-body-custom">
                        {{-- Stage Timeline --}}
                        <div class="stage-timeline mb-3">
                            <div class="timeline-item {{ in_array($stage, ['draft', 'pending']) ? 'active' : 'completed' }}">
                                <div class="timeline-marker">
                                    <i class="bi {{ in_array($stage, ['draft', 'pending']) ? 'bi-circle' : 'bi-check-circle-fill' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Draft/Approval</div>
                                </div>
                            </div>
                            <div class="timeline-item {{ $stage === 'pre_registration' ? 'active' : ($stage === 'draft' || $stage === 'pending' ? '' : 'completed') }}">
                                <div class="timeline-marker">
                                    <i class="bi {{ $stage === 'pre_registration' ? 'bi-circle' : (in_array($stage, ['draft', 'pending']) ? 'bi-circle' : 'bi-check-circle-fill') }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Pre-Registration</div>
                                    @if($timeInfo['registration_starts_in'] && $stage === 'pre_registration')
                                    <div class="timeline-time">{{ $timeInfo['registration_starts_in'] }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-item {{ $stage === 'registration_open' ? 'active' : (in_array($stage, ['registration_closed', 'ongoing', 'past']) ? 'completed' : '') }}">
                                <div class="timeline-marker">
                                    <i class="bi {{ $stage === 'registration_open' ? 'bi-circle' : (in_array($stage, ['registration_closed', 'ongoing', 'past']) ? 'bi-check-circle-fill' : 'bi-circle') }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Registration Open</div>
                                    @if($timeInfo['registration_ends_in'] && $stage === 'registration_open')
                                    <div class="timeline-time">Closes {{ $timeInfo['registration_ends_in'] }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-item {{ $stage === 'ongoing' ? 'active' : ($stage === 'past' ? 'completed' : '') }}">
                                <div class="timeline-marker">
                                    <i class="bi {{ $stage === 'ongoing' ? 'bi-circle' : ($stage === 'past' ? 'bi-check-circle-fill' : 'bi-circle') }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Event Ongoing</div>
                                    @if($timeInfo['event_ends_in'] && $stage === 'ongoing')
                                    <div class="timeline-time">Ends {{ $timeInfo['event_ends_in'] }}</div>
                                    @elseif($timeInfo['event_starts_in'] && in_array($stage, ['pre_registration', 'registration_open', 'registration_closed']))
                                    <div class="timeline-time">Starts {{ $timeInfo['event_starts_in'] }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="timeline-item {{ $stage === 'past' ? 'completed' : '' }}">
                                <div class="timeline-marker">
                                    <i class="bi {{ $stage === 'past' ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Completed</div>
                                    @if($stage === 'past' && $timeInfo['event_ended'])
                                    <div class="timeline-time">{{ $timeInfo['event_ended'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="management-actions">
                            {{-- Edit Event --}}
                            @if(in_array($stage, ['draft', 'pending', 'pre_registration', 'registration_open', 'registration_closed']))
                            <a href="{{ route('events.edit', $event) }}" class="action-btn action-edit w-100 mb-3">
                                <i class="bi bi-pencil-square me-2"></i>
                                <span>Edit Event</span>
                            </a>
                            @endif

                            {{-- Publish Event (for draft) --}}
                            @if($stage === 'draft')
                            <button type="button"
                                    class="action-btn action-publish w-100 mb-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#publishModal">
                                <i class="bi bi-send-check me-2"></i>
                                <span>Publish Event</span>
                            </button>
                            @endif

                            {{-- Cancel Event Button --}}
                            @if(in_array($stage, ['pre_registration', 'registration_open', 'registration_closed']))
                            <button type="button"
                                    class="action-btn action-cancel w-100 mb-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#cancelEventModal">
                                <i class="bi bi-x-circle me-2"></i>
                                <span>Cancel Event</span>
                            </button>
                            @endif

                            {{-- Delete Button --}}
                            @if(in_array($stage, ['draft', 'pending']) && $event->registrations()->where('status', 'confirmed')->count() === 0)
                            <button type="button"
                                    class="action-btn action-delete w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                <i class="bi bi-trash3 me-2"></i>
                                <span>Delete Event</span>
                            </button>
                            @endif

                            {{-- View Registrations --}}
                            @if(!in_array($stage, ['draft', 'pending']))
                            <a href="{{ route('events.registrations.index', $event) }}" class="action-btn action-view w-100 mt-3">
                                <i class="bi bi-list-check me-2"></i>
                                <span>
                                    View Registrations ({{ $event->registrations->where('status', 'confirmed')->count() }})
                                </span>
                            </a>
                            @endif
                        </div>

                        {{-- Event Statistics --}}
                        <div class="event-stats">
                            <h4>Quick Stats</h4>
                            <div class="stat-row">
                                <span class="stat-label">Total Registered</span>
                                <span class="stat-value">
                                    {{ $event->registrations->where('status', ['confirmed', 'pending_payment'])->count() }}
                                </span>
                            </div>
                            @if($event->is_paid)
                            <div class="stat-row">
                                <span class="stat-label">Revenue</span>
                                <span class="stat-value">
                                    RM {{ number_format($event->registrations->where('status', 'confirmed')->count() * $event->fee_amount, 2) }}
                                </span>
                            </div>
                            @endif
                            @if($event->max_participants)
                            <div class="stat-row">
                                <span class="stat-label">Capacity</span>
                                <span class="stat-value">
                                    {{ round(($event->registrations->where('status', 'confirmed')->count() / $event->max_participants) * 100) }}%
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                {{-- Registration Card for Regular Users --}}
                <div class="card-body-custom">

                    {{-- 1. 全局提示：活动进行中 (Global Alert for Ongoing) --}}
                    @if($stage === 'ongoing')
                    <div class="alert alert-primary mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-play-circle-fill fs-4 me-2"></i>
                            <div>
                                <strong>Event In Progress</strong>
                                <small class="d-block">This event is currently ongoing</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 2. 已报名用户界面 (Registered User View) --}}
                    @if($isRegistered)

                    <!-- Registration Status Badge -->
                    <div class="registration-status status-registered">
                        <div class="status-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="status-text">
                            <h4>You're Registered</h4>
                            <p>Registration Status: <strong>{{ ucfirst($userRegistration->status) }}</strong></p>
                            <small class="text-muted d-block mt-2 status-info">
                                <i class="bi bi-calendar-check me-1"></i>
                                Registered on {{ $userRegistration->created_at->format('d M Y, h:i A') }}
                            </small>
                        </div>
                    </div>

                    {{-- View Registration History Button --}}
                    {{-- <a href="{{ route('registrations.history', ['event' => $event->id]) }}" --}}
                       class="btn btn-outline-primary w-100 mt-3">
                        <i class="bi bi-clock-history me-2"></i>
                        View Registration History
                    </a>

                    {{-- 根据状态互斥显示 --}}

                    {{-- Case A: Pending Payment (未付款) --}}
                    @if($userRegistration->status === 'pending_payment')
                    @php
                    $minutesLeft = $userRegistration->remaining_minutes ?? \Carbon\Carbon::now()->diffInMinutes($userRegistration->expires_at, false);
                    $isExpired = $minutesLeft < 0;
                    @endphp

                    @if(!$isExpired)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Payment pending. You have <strong>{{ (int)$minutesLeft }} minutes</strong> left to complete payment.</small>

                        @if(now() > $event->registration_end_time->subHours(1))
                        <br><small class="text-danger mt-1 d-block">
                            <i class="bi bi-clock-history me-1"></i>
                            Registration closes soon.
                        </small>
                        @endif
                    </div>

                    <a href="{{ route('registrations.payment', $userRegistration) }}" class="btn btn-warning w-100 mt-3">
                        <i class="bi bi-credit-card me-2"></i>
                        Continue Payment
                    </a>

                    {{-- 只有 Pending 状态才显示 "Cancel Order" --}}
                    <button type="button"
                            class="btn btn-outline-danger w-100 mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelOrderModal">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancel Order
                    </button>
                    @else
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-x-circle me-2"></i>
                        <small>Payment time expired. Please register again if spots are available.</small>
                    </div>
                    @endif

                    {{-- Case B: Confirmed (已确认报名) --}}
                    @elseif($userRegistration->status === 'confirmed')

                    @if($userRegistration->can_be_cancelled)
                    <div class="cancellation-info mt-3">
                        <div class="info-row">
                            <i class="bi bi-info-circle"></i>
                            <span>You can cancel until {{ $userRegistration->cancellation_deadline->format('d M Y, h:i A') }}</span>
                        </div>
                        @if($event->is_paid && !$event->refund_available)
                        <div class="info-row text-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span>Non-refundable event</span>
                        </div>
                        @endif
                    </div>

                    <button type="button" 
                            class="btn btn-outline-danger w-100 mt-3" 
                            data-bs-toggle="modal" 
                            data-bs-target="#cancelRegistrationModal">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancel Registration
                    </button>
                    @elseif(!$event->allow_cancellation)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>This event does not allow registration cancellation.</small>
                    </div>
                    @else
                    <div class="alert alert-secondary mt-3 mb-0">
                        <i class="bi bi-lock me-2"></i>
                        <small>Cancellation is not available at this time.</small>
                    </div>
                    @endif

                    {{-- Case C: Already Cancelled (已取消) --}}
                    @elseif($userRegistration->status === 'cancelled')
                    <div class="alert alert-secondary mt-3 mb-0">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-x-circle me-2 mt-1"></i>
                            <div>
                                <small><strong>Registration Cancelled</strong></small>
                                <br>
                                <small class="text-muted">Cancelled on {{ $userRegistration->cancelled_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- 【核心修改区域结束】 --}}

                    {{-- 3. 未报名用户界面 (Non-Registered User View) --}}
                    @else 
                    @if($stage === 'registration_open')
                    {{-- Open for Registration --}}
                    @if($canRegister && !$event->is_full)
                    <div class="price-display">
                        @if($event->is_paid)
                        <div class="price-label">Registration Fee</div>
                        <div class="price-amount">RM {{ number_format($event->fee_amount, 2) }}</div>
                        @if($event->refund_available)
                        <div class="price-note"><i class="bi bi-info-circle me-1"></i> Refundable before event date</div>
                        @endif
                        @else
                        <div class="price-amount free">Free Event</div>
                        @endif
                    </div>

                    <a href="{{ route('events.register.create', $event) }}" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-calendar-check me-2"></i> Register Now
                    </a>

                    <div class="registration-info">
                        <div class="info-row"><i class="bi bi-clock"></i><span>Closes {{ $timeInfo['registration_ends_in'] }}</span></div>
                        @if($event->remaining_seats && $event->remaining_seats <= 10)
                        <div class="info-row urgent"><i class="bi bi-exclamation-triangle"></i><span>Only {{ $event->remaining_seats }} seats left!</span></div>
                        @endif
                    </div>

                    @elseif($registrationBlockReason === 'not_club_member')
                    <div class="registration-status status-private">
                        <div class="status-icon"><i class="bi bi-lock-fill"></i></div>
                        <div class="status-text"><h4>Club Members Only</h4><p>Restricted to members.</p></div>
                    </div>
                    <a href="{{ route('home') }}" class="btn btn-outline-primary w-100 mt-3">Join This Club</a>

                    @elseif($event->is_full)
                    <div class="registration-status status-full">
                        <div class="status-icon"><i class="bi bi-x-circle"></i></div>
                        <div class="status-text"><h4>Event Full</h4><p>Max capacity reached</p></div>
                    </div>
                    @endif

                    @elseif($stage === 'pre_registration')
                    <div class="registration-status status-closed">
                        <div class="status-icon"><i class="bi bi-clock"></i></div>
                        <div class="status-text"><h4>Opens Soon</h4><p>Opens {{ $timeInfo['registration_starts_in'] }}</p></div>
                    </div>

                    @elseif($stage === 'ongoing')
                    <div class="registration-status status-closed">
                        <div class="status-icon"><i class="bi bi-play-circle"></i></div>
                        <div class="status-text"><h4>Event In Progress</h4><p>Registration is closed.</p></div>
                    </div>

                    @elseif($stage === 'past')
                    <div class="registration-status status-closed">
                        <div class="status-icon"><i class="bi bi-calendar-check"></i></div>
                        <div class="status-text"><h4>Event Ended</h4><p>This event has concluded</p></div>
                    </div>

                    @else
                    <div class="registration-status status-closed">
                        <div class="status-icon"><i class="bi bi-lock"></i></div>
                        <div class="status-text"><h4>Registration Closed</h4><p>Period has ended</p></div>
                    </div>
                    @endif

                    @if(!$event->is_public)
                    <div class="registration-info mt-3">
                        <div class="info-row"><i class="bi bi-lock-fill"></i><span>Private Event - Club Members Only</span></div>
                    </div>
                    @endif

                    @endif {{-- End of isRegistered check --}}

                </div>

                @endif
                @else
                <!-- Guest User - Login Required -->
                <div class="sidebar-card sticky-sidebar">
                    <div class="card-header-custom">
                        <h3>Event Registration</h3>
                    </div>
                    <div class="card-body-custom">
                        @if(!$event->is_public)
                        <!-- Private Event -->
                        <div class="registration-status status-private">
                            <div class="status-icon">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <div class="status-text">
                                <h4>Club Members Only</h4>
                                <p>This private event is only open to club members</p>
                            </div>
                        </div>
                        @else
                        <!-- Public Event -->
                        <div class="registration-status status-login">
                            <div class="status-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="status-text">
                                <h4>Login Required</h4>
                                <p>Please login to register for this event</p>
                            </div>
                        </div>
                        @endif

                        <button type="button" 
                                class="btn btn-primary w-100 mt-3" 
                                onclick="showLoginRequired('{{ !$event->is_public ? 'This is a private club event. Please login to check your membership status.' : 'Please login to register for this event.' }}')">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Login to Register
                        </button>
                    </div>
                </div>
                @endauth

                <!-- Organizer Card -->
                <div class="sidebar-card">
                    <div class="card-header-custom">
                        <h3>Organized By</h3>
                    </div>
                    <div class="card-body-custom">
                        <div class="organizer-info">
                            <div class="organizer-avatar">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="organizer-details">
                                <h4>{{ $event->organizer->name ?? 'TARCampus' }}</h4>
                                <p class="organizer-type">{{ ucfirst($event->organizer_type) }}</p>
                            </div>
                        </div>
                        @if($event->organizer)
                        <a href="{{ route('home', $event->organizer_id) }}" class="btn btn-outline-primary w-100 mt-3">
                            <i class="bi bi-building me-2"></i>
                            View Profile
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Share Event -->
                <div class="sidebar-card">
                    <div class="card-header-custom">
                        <h3>Share Event</h3>
                    </div>
                    <div class="card-body-custom">
                        <div class="share-buttons">
                            <button class="share-btn share-facebook" onclick="shareEvent('facebook')">
                                <i class="bi bi-facebook"></i>
                            </button>
                            <button class="share-btn share-twitter" onclick="shareEvent('twitter')">
                                <i class="bi bi-twitter"></i>
                            </button>
                            <button class="share-btn share-whatsapp" onclick="shareEvent('whatsapp')">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                            <button class="share-btn share-copy" onclick="copyEventLink()">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@auth
@if($canManageEvent)
<!-- Publish Event Modal -->
<div class="modal fade" id="publishModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Publish Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to publish this event? Once published, students will be able to view and register for this event.</p>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Event will be visible to all students and registration will open according to the scheduled time.</small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('events.publish', $event) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary">Yes, Publish Event</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Event Modal -->
<div class="modal fade" id="cancelEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Cancel Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('events.cancel', $event) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <p>Are you sure you want to cancel this event?</p>
                    @if($event->registrations->where('status', 'confirmed')->count() > 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ $event->registrations->where('status', 'confirmed')->count() }}</strong> students have registered for this event. They will be notified about the cancellation.
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required placeholder="Please provide a reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Event</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Event Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Delete Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete this event?</p>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All event data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete Event</button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<!-- Cancel Registration Modal -->
@if($isRegistered && $userRegistration && $userRegistration->can_be_cancelled)
<div class="modal fade" id="cancelRegistrationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Cancel Registration
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Are you sure you want to cancel your registration?</strong>
                </div>

                <div class="cancellation-details">
                    <h6 class="mb-3">Event Details:</h6>
                    <div class="detail-row">
                        <span class="detail-label">Event:</span>
                        <span class="detail-value">{{ $event->title }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">{{ $event->start_time->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($event->is_paid)
                    <div class="detail-row">
                        <span class="detail-label">Fee Paid:</span>
                        <span class="detail-value">
                            <strong class="text-primary">RM {{ number_format($event->fee_amount, 2) }}</strong>
                        </span>
                    </div>
                    @endif
                </div>

                @if($event->is_paid)
                <div class="alert {{ $event->refund_available ? 'alert-info' : 'alert-danger' }} mt-3 mb-0">
                    <i class="bi bi-{{ $event->refund_available ? 'info-circle' : 'exclamation-octagon' }} me-2"></i>
                    @if($event->refund_available)
                    <strong>Refund Available:</strong> You will be asked to provide a reason for refund in the next step.
                    @else
                    <strong>No Refund:</strong> This event is non-refundable.
                    @endif
                </div>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Registration</button>
                <button type="button" class="btn btn-danger" id="proceedCancelBtn">
                    <i class="bi bi-arrow-right me-2"></i>
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Refund Reason Modal --}}
<div class="modal fade" id="refundReasonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-chat-square-text me-2 text-primary"></i>
                    Refund Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('registrations.cancel', $userRegistration) }}" method="POST" id="refundReasonForm">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p class="mb-3">Please provide a reason for your refund request:</p>

                    <div class="mb-3">
                        <label for="refundReason" class="form-label">
                            Refund Reason <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="refundReason" 
                            name="refund_reason"
                            rows="4" 
                            required
                            minlength="10"
                            maxlength="500"
                            placeholder="Please explain why you need to cancel and request a refund..."></textarea>
                        <div class="form-text">
                            Minimum 10 characters, maximum 500 characters.
                        </div>
                        <div class="invalid-feedback">
                            Please provide a reason (at least 10 characters).
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            <strong>What happens next:</strong><br>
                            • Your registration will be cancelled<br>
                            • Refund request will be submitted to organizer<br>
                            • You'll be notified once the refund is processed<br>
                            • Refund may take 5-7 business days
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmRefundCancelBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Submit & Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Cancel Order Modal --}}
@if($isRegistered && $userRegistration && $userRegistration->status === 'pending_payment' && !$userRegistration->is_expired)
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Cancel Pending Order
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('registrations.cancel', $userRegistration) }}" method="POST" id="cancelOrderForm">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Are you sure you want to cancel this pending order?</strong>
                    </div>

                    <p>You are about to cancel your pending registration for:</p>

                    <div class="event-info-box">
                        <h6><strong>{{ $event->title }}</strong></h6>
                        <p class="mb-1">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ $event->start_time->format('d M Y, h:i A') }}
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $event->venue }}
                        </p>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>
                            <strong>What happens next:</strong><br>
                            • Your registration will be cancelled immediately<br>
                            • No payment has been charged<br>
                            • You can register again if spots are available<br>
                            • Your spot will be released for other students
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-arrow-left me-2"></i>
                        Keep Registration
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmCancelOrderBtn">
                        <i class="bi bi-x-circle me-2"></i>
                        Yes, Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endif
@endauth

@push('styles')
@vite('resources/css/events/event-detail.css')
@endpush

@push('scripts')
<script>
    $(function () {
    // ==========================================
    // Share Functions
    // ==========================================
    window.shareEvent = function (platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(@json($event -> title));
    let shareUrl;
    if (platform === 'facebook') {
    shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
    } else if (platform === 'twitter') {
    shareUrl = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title;
    } else if (platform === 'whatsapp') {
    shareUrl = 'https://wa.me/?text=' + title + '%20' + url;
    }

    if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400');
    }
    };
    window.copyEventLink = function () {
    const url = window.location.href;
    if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(showCopyToast);
    } else {
    const $temp = $('<input>');
    $('body').append($temp);
    $temp.val(url).select();
    document.execCommand('copy');
    $temp.remove();
    showCopyToast();
    }
    };
    function showCopyToast() {
    const $toast = $(`
                <div class="copy-toast">
                    <i class="bi bi-check-circle me-2"></i>Link copied to clipboard!
                </div>
            `);
    $('body').append($toast);
    setTimeout(() => $toast.addClass('show'), 100);
    setTimeout(() => {
    $toast.removeClass('show');
    setTimeout(() => $toast.remove(), 300);
    }, 3000);
    }

    // ==========================================
    // Cancel Registration Form Handler
    // ==========================================
    const cancelForm = document.getElementById('cancelRegistrationForm');
    const confirmBtn = document.getElementById('confirmCancelBtn');
    if (cancelForm && confirmBtn) {
    cancelForm.addEventListener('submit', function (e) {
    confirmBtn.disabled = true;
    confirmBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';
    });
    }

    // ==========================================
    // Cancel Order Form Handler
    // ==========================================
    const cancelOrderForm = document.getElementById('cancelOrderForm');
    const confirmCancelOrderBtn = document.getElementById('confirmCancelOrderBtn');
    if (cancelOrderForm && confirmCancelOrderBtn) {
    cancelOrderForm.addEventListener('submit', function (e) {
    confirmCancelOrderBtn.disabled = true;
    confirmCancelOrderBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';
    });
    }

    // Cancel Registration Flow with Refund Reason
    const isRefundAvailable = @json($event -> is_paid && $event -> refund_available);
    const isPaid = @json($event -> is_paid);
    
    // 先定义 route 变量，如果有 userRegistration 才赋值
    let cancelRoute = '';
    @if($userRegistration)
        cancelRoute = '{{ route("registrations.cancel", $userRegistration) }}';
    @endif
    
    $('#proceedCancelBtn').on('click', function() {
    $('#cancelRegistrationModal').modal('hide');
    
    // 安全检查：如果没有 route (比如 organizer 视角)，直接停止
    if (!cancelRoute) return; 
        
    // 如果是付费且可退款，显示退款原因表单
    if (isPaid && isRefundAvailable) {
    $('#refundReasonModal').modal('show');
    } else {
    // 否则直接提交取消（免费活动或不可退款）
    const form = $('<form>', {
    'method': 'POST',
            'action': cancelRoute
    });
    form.append('@csrf');
    form.append($('<input>', {
    'type': 'hidden',
            'name': '_method',
            'value': 'DELETE'
    }));
    $('body').append(form);
    form.submit();
    }
    });
// Refund Reason Form Validation
    $('#refundReasonForm').on('submit', function(e) {
    const reason = $('#refundReason').val().trim();
    const btn = $('#confirmRefundCancelBtn');
    if (reason.length < 10) {
    e.preventDefault();
    $('#refundReason').addClass('is-invalid');
    return false;
    }

    btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Processing...'
            );
    });
    $('#refundReason').on('input', function() {
    $(this).removeClass('is-invalid');
    });
    });
</script>
@endpush

@endsection