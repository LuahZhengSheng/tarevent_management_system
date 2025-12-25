@extends('layouts.app')

@section('title', 'Payment - ' . $event->title)

@push('styles')
@vite('resources/css/events/payment.css')
@endpush

@section('content')
{{-- 倒计时提示 --}}
<div class="expiry-timer" id="expiryTimer" data-expires-at="{{ $registration->expires_at->toIso8601String() }}">
    <div class="timer-icon">
        <i class="bi bi-clock-history"></i>
    </div>
    <div class="timer-content">
        <div class="timer-label">Payment Time Remaining:</div>
        <div class="timer-display" id="timerDisplay">
            <span class="minutes">--</span>:<span class="seconds">--</span>
        </div>
    </div>
</div>

<div class="payment-container">
    <div class="payment-card">
        <!-- Payment Header -->
        <div class="payment-header">
            <div class="header-icon">
                <i class="bi bi-credit-card"></i>
            </div>
            <h1>Complete Payment</h1>
            <p>Secure your spot for this event</p>
        </div>

        <!-- Event Summary -->
        <div class="event-summary">
            <div class="event-summary-header">
                <i class="bi bi-calendar-check"></i>
                <h3>Event Details</h3>
            </div>

            <div class="event-info">
                <div class="event-poster">
                    @if($event->poster_path)
                    <img src="{{ asset('storage/event-posters/' . $event->poster_path) }}" 
                         alt="{{ $event->title }}">
                    @else
                    <div class="poster-placeholder">
                        <i class="bi bi-image"></i>
                    </div>
                    @endif
                </div>

                <div class="event-details">
                    <h4>{{ $event->title }}</h4>
                    <div class="detail-row">
                        <i class="bi bi-calendar3"></i>
                        <span>{{ $event->start_time->format('l, F j, Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <i class="bi bi-clock"></i>
                        <span>{{ $event->start_time->format('h:i A') }} - {{ $event->end_time->format('h:i A') }}</span>
                    </div>
                    <div class="detail-row">
                        <i class="bi bi-geo-alt"></i>
                        <span>{{ $event->venue }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Summary -->
        <div class="registration-summary">
            <div class="summary-header">
                <i class="bi bi-receipt"></i>
                <h3>Registration Summary</h3>
            </div>

            <div class="summary-content">
                <div class="summary-row">
                    <span class="label">Registration Number:</span>
                    <span class="value">{{ $registration->registration_number }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Registrant Name:</span>
                    <span class="value">{{ $registration->full_name }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Student ID:</span>
                    <span class="value">{{ $registration->student_id }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Registration Date:</span>
                    <span class="value">{{ $registration->created_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="payment-amount-box">
            <div class="amount-label">Total Amount</div>
            <div class="amount-value">RM {{ number_format($event->fee_amount, 2) }}</div>
            @if($event->refund_available)
            <div class="refund-notice">
                <i class="bi bi-info-circle"></i>
                <span>Refundable if cancelled before event starts</span>
            </div>
            @else
            <div class="refund-notice no-refund">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Non-refundable payment</span>
            </div>
            @endif
        </div>

        <!-- Payment Method Selection -->
        <div class="payment-method-section">
            <h3 class="section-title">
                <i class="bi bi-wallet2"></i>
                Select Payment Method
            </h3>

            <div class="payment-methods">
                <!-- Stripe Payment -->
                <div class="payment-method-card" data-method="stripe">
                    <input type="radio" 
                           name="payment_method" 
                           id="method_stripe" 
                           value="stripe" 
                           checked>
                    <label for="method_stripe">
                        <div class="method-icon stripe-icon">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <div class="method-info">
                            <div class="method-name">Credit/Debit Card</div>
                            <div class="method-description">Pay securely with Stripe</div>
                        </div>
                        <div class="method-logos">
                            <img src="https://js.stripe.com/v3/fingerprinted/img/visa-729c05c240c4bdb47b03ac81d9945bfe.svg" alt="Visa">
                            <img src="https://js.stripe.com/v3/fingerprinted/img/mastercard-4d8844094130711885b5e41b28c9848f.svg" alt="Mastercard">
                        </div>
                    </label>
                </div>

                <!-- PayPal Payment -->
                <div class="payment-method-card" data-method="paypal">
                    <input type="radio" 
                           name="payment_method" 
                           id="method_paypal" 
                           value="paypal">
                    <label for="method_paypal">
                        <div class="method-icon paypal-icon">
                            <i class="bi bi-paypal"></i>
                        </div>
                        <div class="method-info">
                            <div class="method-name">PayPal</div>
                            <div class="method-description">Pay with PayPal account</div>
                        </div>
                        <div class="method-logos">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="24" viewBox="0 0 100 32">
                            <path fill="#003087" d="M12 4.917h7.333c5.523 0 8.667 2.667 8.667 7.333 0 5.334-4 9.334-9.333 9.334H15l-2 10h-4L12 4.917z"/>
                            <path fill="#009cde" d="M35 4.917h7.333C47.856 4.917 51 7.583 51 12.25c0 5.334-4 9.334-9.333 9.334H38l-2 10h-4L35 4.917z"/>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Stripe Payment Form -->
        <div id="stripe-payment-section" class="payment-section active">
            <form id="stripe-payment-form">
                @csrf
                <input type="hidden" name="registration_id" value="{{ $registration->id }}">
                <input type="hidden" name="payment_method" value="stripe">

                <div class="form-group">
                    <label for="card-holder-name">Cardholder Name</label>
                    <input type="text" 
                           id="card-holder-name" 
                           class="form-control" 
                           placeholder="John Doe"
                           value="{{ $registration->full_name }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="card-element">Card Information</label>
                    <div id="card-element" class="stripe-element"></div>
                    <div id="card-errors" class="error-message"></div>
                </div>

                <button type="submit" class="btn-pay" id="stripe-submit-btn">
                    <span class="btn-spinner"></span>
                    <span class="btn-text">
                        <i class="bi bi-lock-fill"></i>
                        Pay RM {{ number_format($event->fee_amount, 2) }}
                    </span>
                </button>
            </form>
        </div>

        <!-- PayPal Payment Section -->
        <div id="paypal-payment-section" class="payment-section">
            <form id="paypal-payment-form">
                @csrf
                <input type="hidden" name="registration_id" value="{{ $registration->id }}">
                <input type="hidden" name="payment_method" value="paypal">

                <div class="paypal-info">
                    <i class="bi bi-info-circle"></i>
                    <p>You will be redirected to PayPal to complete your payment securely.</p>
                </div>

                <!-- 修改这里：加一个 wrapper -->
                <div class="paypal-button-wrapper" style="position: relative;">
                    <!-- 遮罩层：默认盖住 -->
                    <div id="paypal-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; cursor: pointer;"></div>

                    <div id="paypal-button-container"></div>
                </div>
            </form>
        </div>

        <!-- Security Notice -->
        <div class="security-notice">
            <i class="bi bi-shield-check"></i>
            <div>
                <strong>Secure Payment</strong>
                <p>Your payment information is encrypted and secure. We never store your card details.</p>
            </div>
        </div>

        <!-- Terms -->
        <div class="payment-terms">
            <label class="terms-checkbox">
                <input type="checkbox" id="payment-terms" required>
                <span>I agree to the <a href="#" target="_blank">payment terms</a> and <a href="#" target="_blank">refund policy</a></span>
            </label>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="payment-loading" class="payment-loading" style="display: none;">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <p>Processing your payment...</p>
        <small>Please do not close this window</small>
    </div>
</div>
@endsection

@push('scripts')
<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=MYR"></script>

@vite('resources/js/events/payment.js')
@endpush