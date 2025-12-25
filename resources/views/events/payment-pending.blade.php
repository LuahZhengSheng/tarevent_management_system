@extends('layouts.app')

@section('title', 'Payment Processing')

@section('content')
<div class="payment-pending-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Processing Card -->
                <div class="pending-card">
                    <div class="pending-icon">
                        <div class="spinner-container">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Processing...</span>
                            </div>
                        </div>
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <h1 class="pending-title">Payment Processing</h1>
                    <p class="pending-subtitle">Please wait while we confirm your payment...</p>

                    <div class="status-message" id="statusMessage">
                        <i class="bi bi-info-circle me-2"></i>
                        Checking payment status...
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                                 role="progressbar" 
                                 id="progressBar"
                                 style="width: 0%"></div>
                        </div>
                        <div class="progress-text" id="progressText">0/10 seconds</div>
                    </div>

                    <!-- Event Info -->
                    <div class="event-info-card">
                        <h6 class="event-info-title">Event Details</h6>
                        <div class="event-info-content">
                            <div class="info-row">
                                <span class="info-label">Event:</span>
                                <span class="info-value">{{ $registration->event->title }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date:</span>
                                <span class="info-value">{{ $registration->event->start_time->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Amount:</span>
                                <span class="info-value">RM {{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Registration #:</span>
                                <span class="info-value">{{ $registration->registration_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Important Note -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Please do not close this page or refresh your browser.</strong>
                        <br>We are verifying your payment with the payment gateway. This usually takes a few seconds.
                    </div>

                    <!-- Manual Actions (hidden initially) -->
                    <div class="manual-actions d-none" id="manualActions">
                        <p class="text-muted mb-3">Payment verification is taking longer than expected.</p>
                        <div class="btn-group-custom">
                            <a href="{{ route('events.my') }}" class="btn btn-primary">
                                <i class="bi bi-list-check me-2"></i>
                                Go to My Events
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Retry
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.payment-pending-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 60px 20px;
    display: flex;
    align-items: center;
}

.pending-card {
    background: var(--bg-primary);
    border-radius: 20px;
    padding: 50px 40px;
    box-shadow: var(--shadow-xl);
    text-align: center;
}

.pending-icon {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 30px;
}

.spinner-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.spinner-border {
    width: 120px;
    height: 120px;
    border-width: 8px;
}

.pending-icon i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 50px;
    color: #f59e0b;
}

.pending-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.pending-subtitle {
    font-size: 18px;
    color: var(--text-secondary);
    margin-bottom: 30px;
}

.status-message {
    background-color: var(--bg-secondary);
    padding: 15px 20px;
    border-radius: 10px;
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 30px;
}

.progress-container {
    margin-bottom: 30px;
}

.progress {
    height: 12px;
    border-radius: 10px;
    background-color: var(--bg-secondary);
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

.progress-text {
    margin-top: 10px;
    font-size: 14px;
    color: var(--text-secondary);
    font-weight: 500;
}

.event-info-card {
    background-color: var(--bg-secondary);
    padding: 25px;
    border-radius: 12px;
    margin: 30px 0;
    text-align: left;
}

.event-info-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-secondary);
}

.info-value {
    color: var(--text-primary);
    font-weight: 500;
}

.alert {
    border: none;
    border-radius: 10px;
    margin-bottom: 20px;
}

.manual-actions {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid var(--border-color);
}

.btn-group-custom {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .pending-card {
        padding: 40px 25px;
    }

    .pending-title {
        font-size: 24px;
    }

    .pending-subtitle {
        font-size: 16px;
    }

    .btn-group-custom {
        flex-direction: column;
    }

    .btn-group-custom .btn {
        width: 100%;
    }
}

/* Dark mode */
[data-theme="dark"] .pending-card {
    background-color: var(--bg-secondary);
}
</style>
@endpush

@push('scripts')
<script>
$(function() {
    const registrationId = {{ $registration->id }};
    const checkUrl = '{{ route('registrations.check-status', $registration) }}';
    const receiptUrl = '{{ route('registrations.receipt', $registration) }}';
    const myEventsUrl = '{{ route('events.my') }}';
    
    let pollCount = 0;
    const maxPolls = 10; // 10 seconds total
    const pollInterval = 1000; // Check every 1 second
    
    let intervalId;
    
    function updateProgress() {
        pollCount++;
        const percentage = (pollCount / maxPolls) * 100;
        
        $('#progressBar').css('width', percentage + '%');
        $('#progressText').text(pollCount + '/' + maxPolls + ' seconds');
        
        if (pollCount >= maxPolls) {
            clearInterval(intervalId);
            showManualActions();
        }
    }
    
    function checkPaymentStatus() {
        $.ajax({
            url: checkUrl,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    if (response.status === 'success' && response.registration_status === 'confirmed') {
                        // Payment confirmed!
                        clearInterval(intervalId);
                        showSuccess();
                        
                        // Redirect to receipt after 2 seconds
                        setTimeout(function() {
                            window.location.href = receiptUrl;
                        }, 2000);
                    } else if (response.status === 'failed') {
                        // Payment failed
                        clearInterval(intervalId);
                        showFailed();
                        
                        // Redirect to my events after 3 seconds
                        setTimeout(function() {
                            window.location.href = myEventsUrl;
                        }, 3000);
                    }
                    // If still pending, continue polling
                }
            },
            error: function(xhr) {
                console.error('Error checking payment status:', xhr);
            }
        });
    }
    
    function showSuccess() {
        $('.spinner-container').html('<i class="bi bi-check-circle-fill" style="font-size: 60px; color: #10b981;"></i>');
        $('.pending-icon > i').remove();
        $('.pending-title').text('Payment Confirmed! 🎉');
        $('.pending-subtitle').text('Redirecting to your receipt...');
        $('#statusMessage')
            .removeClass('alert-info')
            .addClass('alert-success')
            .html('<i class="bi bi-check-circle-fill me-2"></i>Payment verified successfully!');
        $('#progressBar')
            .removeClass('bg-warning')
            .addClass('bg-success')
            .css('width', '100%');
    }
    
    function showFailed() {
        $('.spinner-container').html('<i class="bi bi-x-circle-fill" style="font-size: 60px; color: #ef4444;"></i>');
        $('.pending-icon > i').remove();
        $('.pending-title').text('Payment Failed');
        $('.pending-subtitle').text('Please try again or contact support.');
        $('#statusMessage')
            .removeClass('alert-info')
            .addClass('alert-danger')
            .html('<i class="bi bi-x-circle-fill me-2"></i>Payment could not be verified.');
        $('#progressBar')
            .removeClass('bg-warning')
            .addClass('bg-danger')
            .css('width', '100%');
    }
    
    function showManualActions() {
        $('.pending-subtitle').text('Verification is taking longer than expected.');
        $('#statusMessage').html('<i class="bi bi-clock-history me-2"></i>Still processing... Please check "My Events" in a moment.');
        $('#manualActions').removeClass('d-none');
    }
    
    // Start polling immediately
    checkPaymentStatus();
    
    // Then poll every second
    intervalId = setInterval(function() {
        updateProgress();
        checkPaymentStatus();
    }, pollInterval);
    
    // Safety: Clear interval when user leaves page
    $(window).on('beforeunload', function() {
        clearInterval(intervalId);
    });
});
</script>
@endpush

@endsection