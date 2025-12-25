<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #2563eb, #10b981);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.95;
        }
        .content {
            padding: 40px 30px;
        }
        .success-badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 12px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;
            border-left: 4px solid #10b981;
        }
        .event-details {
            background-color: #f9fafb;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
        }
        .detail-value {
            color: #111827;
            font-weight: 500;
        }
        .payment-info {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2563eb;
        }
        .payment-info h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #1e40af;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            background-color: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎉 Registration Confirmed!</h1>
            <p>Your spot has been secured</p>
        </div>

        <div class="content">
            <div class="success-badge">
                Payment received successfully
            </div>

            <p>Hi <strong>{{ $registration->full_name }}</strong>,</p>
            
            <p>Congratulations! Your registration for <strong>{{ $registration->event->title }}</strong> has been confirmed. We're excited to see you there!</p>

            <div class="event-details">
                <h3 style="margin-top: 0; color: #111827;">Event Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Event:</span>
                    <span class="detail-value">{{ $registration->event->title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $registration->event->start_time->format('l, F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">{{ $registration->event->start_time->format('h:i A') }} - {{ $registration->event->end_time->format('h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Venue:</span>
                    <span class="detail-value">{{ $registration->event->venue }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registration Number:</span>
                    <span class="detail-value">{{ $registration->registration_number }}</span>
                </div>
            </div>

            @if($registration->payment)
            <div class="payment-info">
                <h3>💳 Payment Receipt</h3>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value">RM {{ number_format($registration->payment->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ ucfirst($registration->payment->method) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $registration->payment->transaction_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Date:</span>
                    <span class="detail-value">{{ $registration->payment->paid_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>
            @endif

            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Please arrive at least 15 minutes before the event starts</li>
                <li>Bring your student ID for verification</li>
                @if($registration->event->require_emergency_contact)
                <li>Emergency contact information has been recorded</li>
                @endif
                @if($registration->event->refund_available)
                <li>Refunds are available if you cancel before the event starts</li>
                @else
                <li>This is a non-refundable registration</li>
                @endif
            </ul>

            <center>
                <a href="{{ route('events.show', $registration->event) }}" class="cta-button">
                    View Event Details
                </a>
            </center>
        </div>

        <div class="footer">
            <p>If you have any questions, please contact us at <a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></p>
            <p style="margin-top: 15px;">
                <a href="{{ route('events.my') }}">View My Events</a> | 
                <a href="{{ route('events.index') }}">Browse More Events</a>
            </p>
            <p style="margin-top: 15px; color: #9ca3af; font-size: 12px;">
                © {{ date('Y') }} TAREvent Management System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>