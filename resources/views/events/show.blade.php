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
                <!-- Event Status Alert -->
                @if($isRegistered)
                <div class="alert-custom alert-success">
                    <div class="alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>You're Registered!</h4>
                        <p>You have successfully registered for this event. Check your email for confirmation details.</p>
                        @if($userRegistration->status === 'pending_payment')
                        <a href="{{ route('registrations.payment', $userRegistration) }}" class="btn btn-sm btn-light mt-2">
                            <i class="bi bi-credit-card me-1"></i>
                            Complete Payment
                        </a>
                        @endif
                    </div>
                </div>
                @elseif(!$event->is_registration_open)
                <div class="alert-custom alert-warning">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <h4>Registration Closed</h4>
                        <p>
                            @if($event->is_full)
                            This event has reached maximum capacity.
                            @elseif($event->registration_end_time < now())
                            Registration period has ended.
                            @else
                            Registration is not yet open.
                            @endif
                        </p>
                    </div>
                </div>
                @endif

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
                                    <div class="capacity-fill" style="width: {{ ($event->registrations->where('status', 'confirmed')->count() / $event->max_participants) * 100 }}%"></div>
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
                <!-- Registration Card -->
                <div class="sidebar-card sticky-sidebar">
                    <div class="card-header-custom">
                        <h3>Event Registration</h3>
                    </div>
                    <div class="card-body-custom">
                        @auth
                        @if($isRegistered)
                        <!-- Already Registered -->
                        <div class="registration-status status-registered">
                            <div class="status-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="status-text">
                                <h4>You're Registered</h4>
                                <p>Registration Status: <strong>{{ ucfirst($userRegistration->status) }}</strong></p>
                            </div>
                        </div>

                        @if($event->allow_cancellation && $userRegistration->status === 'confirmed')
                        <button type="button" class="btn btn-outline-danger w-100 mt-3" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancel Registration
                        </button>
                        @endif
                        @elseif($event->is_registration_open && !$event->is_full)
                        <!-- Registration Available -->
                        <div class="price-display">
                            @if($event->is_paid)
                            <div class="price-label">Registration Fee</div>
                            <div class="price-amount">RM {{ number_format($event->fee_amount, 2) }}</div>
                            @if($event->refund_available)
                            <div class="price-note">
                                <i class="bi bi-info-circle me-1"></i>
                                Refundable before event date
                            </div>
                            @endif
                            @else
                            <div class="price-amount free">Free Event</div>
                            <div class="price-note">
                                <i class="bi bi-gift me-1"></i>
                                No registration fee required
                            </div>
                            @endif
                        </div>

                        <a href="{{ route('events.register.create', $event) }}" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-calendar-check me-2"></i>
                            Register Now
                        </a>

                        <div class="registration-info">
                            <div class="info-row">
                                <i class="bi bi-clock"></i>
                                <span>Registration closes {{ $event->registration_end_time->diffForHumans() }}</span>
                            </div>
                            @if($event->remaining_seats && $event->remaining_seats <= 10)
                            <div class="info-row urgent">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span>Only {{ $event->remaining_seats }} seats left!</span>
                            </div>
                            @endif
                        </div>
                        @elseif($event->is_full)
                        <!-- Event Full -->
                        <div class="registration-status status-full">
                            <div class="status-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="status-text">
                                <h4>Event Full</h4>
                                <p>This event has reached maximum capacity</p>
                            </div>
                        </div>
                        @else
                        <!-- Registration Closed -->
                        <div class="registration-status status-closed">
                            <div class="status-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="status-text">
                                <h4>Registration Closed</h4>
                                <p>
                                    @if($event->registration_end_time < now())
                                    Registration period has ended
                                    @else
                                    Opens {{ $event->registration_start_time->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        @else
                        <!-- Guest User - Login Required -->
                        <div class="registration-status status-login">
                            <div class="status-icon">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="status-text">
                                <h4>Login Required</h4>
                                <p>Please login to register for this event</p>
                            </div>
                        </div>
                        {{-- <a href="{{ route('login') }}" class="btn btn-primary w-100 mt-3"> --}}
                        <a href="{{ route('home') }}" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Login to Register
                        </a>
                        @endauth
                    </div>
                </div>

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
                        <a href="#" class="btn btn-outline-primary w-100 mt-3">
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

<!-- Cancel Registration Modal -->
@if($isRegistered && $event->allow_cancellation)
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Cancel Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel your registration for this event?</p>
                @if($event->is_paid && !$event->refund_available)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> This event is non-refundable. You will not receive a refund of your registration fee.
                </div>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Registration</button>
                <form action="{{ route('registrations.cancel', $userRegistration) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Cancel Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
@vite('resources/css/events/event-detail.css')
@endpush

@push('scripts')
<script>
    $(function () {
        // 分享
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

        // 复制链接
        window.copyEventLink = function () {
            const url = window.location.href;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(showCopyToast);
            } else {
                // 兼容旧浏览器
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

            setTimeout(() => {
                $toast.addClass('show');
            }, 100);

            setTimeout(() => {
                $toast.removeClass('show');
                setTimeout(() => $toast.remove(), 300);
            }, 3000);
        }

        // 移动端平滑滚动到注册区
        const $registerBtn = $('[href="#register"]');
        if ($registerBtn.length) {
            $registerBtn.on('click', function (e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    const $target = $('.sidebar-card');
                    if ($target.length) {
                        $('html, body').animate({
                            scrollTop: $target.offset().top
                        }, 500);
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection