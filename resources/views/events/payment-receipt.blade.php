@extends('layouts.app')

@section('title', 'Payment Receipt')

@section('content')
<div class="receipt-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Success Header -->
                <div class="success-header">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h1>Payment Successful!</h1>
                    <p>Your registration has been confirmed</p>
                </div>

                <!-- Receipt Card -->
                <div class="receipt-card">
                    <!-- Receipt Header -->
                    <div class="receipt-header">
                        <div class="receipt-logo">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="receipt-info">
                            <h2>Payment Receipt</h2>
                            <p class="receipt-number">Receipt #{{ $payment->transaction_id }}</p>
                            <p class="receipt-date">{{ $payment->paid_at->format('F j, Y h:i A') }}</p>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="status-badge status-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Payment Confirmed - Registration Complete
                    </div>

                    <!-- Event Details -->
                    <div class="receipt-section">
                        <h3 class="section-title">Event Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Event Name</span>
                                <span class="detail-value">{{ $registration->event->title }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date</span>
                                <span class="detail-value">{{ $registration->event->start_time->format('l, F j, Y') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Time</span>
                                <span class="detail-value">{{ $registration->event->start_time->format('h:i A') }} - {{ $registration->event->end_time->format('h:i A') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Venue</span>
                                <span class="detail-value">{{ $registration->event->venue }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Category</span>
                                <span class="detail-value">{{ $registration->event->category }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Organized By</span>
                                <span class="detail-value">{{ $registration->event->organizer->name ?? 'TARCampus' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Participant Details -->
                    <div class="receipt-section">
                        <h3 class="section-title">Participant Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Full Name</span>
                                <span class="detail-value">{{ $registration->full_name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Email</span>
                                <span class="detail-value">{{ $registration->email }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Phone</span>
                                <span class="detail-value">{{ $registration->phone }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Student ID</span>
                                <span class="detail-value">{{ $registration->student_id }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Program</span>
                                <span class="detail-value">{{ $registration->program }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Registration Number</span>
                                <span class="detail-value"><strong>{{ $registration->registration_number }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="receipt-section">
                        <h3 class="section-title">Payment Summary</h3>
                        <div class="payment-summary">
                            <div class="summary-row">
                                <span>Event Registration Fee</span>
                                <span>RM {{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="summary-row total-row">
                                <span>Total Paid</span>
                                <span>RM {{ number_format($payment->amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="receipt-section">
                        <h3 class="section-title">Payment Details</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Payment Method</span>
                                <span class="detail-value">{{ $payment->payment_method_name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Transaction ID</span>
                                <span class="detail-value">{{ $payment->transaction_id }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Status</span>
                                <span class="detail-value"><span class="badge bg-success">Success</span></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Payment Date</span>
                                <span class="detail-value">{{ $payment->paid_at->format('F j, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle me-2"></i>Important Reminders</h5>
                        <ul class="mb-0">
                            <li>Please bring this receipt (printed or digital) on the event day</li>
                            <li>Arrive at least 15 minutes before the event starts</li>
                            <li>Bring your student ID for verification</li>
                            @if($registration->event->refund_available)
                            <li>Refunds are available if you cancel before the event starts</li>
                            @else
                            <li>This is a non-refundable registration</li>
                            @endif
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="receipt-actions">
                        <a href="{{ route('payments.download-receipt', $payment) }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-download me-2"></i>
                            Download Receipt (PDF)
                        </a>
                        <a href="{{ route('events.my') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-list-check me-2"></i>
                            View My Events
                        </a>
                        <a href="{{ route('events.show', $registration->event) }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-calendar-event me-2"></i>
                            View Event Details
                        </a>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="additional-info">
                    <p class="text-center">
                        <i class="bi bi-envelope me-2"></i>
                        A confirmation email with your receipt has been sent to <strong>{{ $registration->email }}</strong>
                    </p>
                    <p class="text-center text-muted">
                        For any questions, contact: {{ $registration->event->contact_email ?? 'events@tarc.edu.my' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.receipt-page {
    background-color: var(--bg-secondary);
    min-height: 100vh;
    padding: 40px 20px;
}

.success-header {
    text-align: center;
    margin-bottom: 40px;
}

.success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease;
}

.success-icon i {
    font-size: 60px;
    color: white;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.success-header h1 {
    font-size: 36px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.success-header p {
    font-size: 18px;
    color: var(--text-secondary);
}

.receipt-card {
    background: var(--bg-primary);
    border-radius: 20px;
    padding: 40px;
    box-shadow: var(--shadow-xl);
    margin-bottom: 30px;
}

.receipt-header {
    display: flex;
    align-items: center;
    gap: 25px;
    padding-bottom: 30px;
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 30px;
}

.receipt-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.receipt-logo i {
    font-size: 40px;
    color: white;
}

.receipt-info h2 {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.receipt-number {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 2px;
}

.receipt-date {
    font-size: 14px;
    color: var(--text-tertiary);
    margin: 0;
}

.status-badge {
    padding: 15px 20px;
    border-radius: 12px;
    text-align: center;
    font-weight: 600;
    margin-bottom: 30px;
}

.status-success {
    background-color: var(--success-light);
    color: var(--success);
    border-left: 4px solid var(--success);
}

.receipt-section {
    margin-bottom: 35px;
}

.section-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border-color);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 16px;
    color: var(--text-primary);
    font-weight: 500;
}

.payment-summary {
    background-color: var(--bg-secondary);
    padding: 25px;
    border-radius: 12px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 16px;
}

.summary-row:last-child {
    border-bottom: none;
}

.total-row {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 10px;
    padding-top: 20px;
    border-top: 2px solid var(--primary);
}

.receipt-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 30px;
}

.receipt-actions .btn {
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
}

.additional-info {
    text-align: center;
    margin-top: 30px;
}

.additional-info p {
    margin: 10px 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .receipt-card {
        padding: 25px;
    }

    .receipt-header {
        flex-direction: column;
        text-align: center;
    }

    .success-header h1 {
        font-size: 28px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .receipt-actions {
        flex-direction: column;
    }

    .receipt-actions .btn {
        width: 100%;
    }
}

/* Print Styles */
@media print {
    .receipt-page {
        background: white;
    }

    .receipt-actions,
    nav,
    footer,
    .dark-mode-toggle {
        display: none !important;
    }
}
</style>
@endpush

@endsection