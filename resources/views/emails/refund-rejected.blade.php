<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Request Rejected</title>
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
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
        .warning-badge {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;
            border-left: 4px solid #ef4444;
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
        .rejection-reason {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #f59e0b;
        }
        .rejection-reason h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #92400e;
        }
        .rejection-reason p {
            margin: 0;
            color: #78350f;
            line-height: 1.6;
        }
        .info-box {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2563eb;
        }
        .info-box h3 {
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
            <h1>Refund Request Rejected</h1>
            <p>Your refund request could not be approved</p>
        </div>

        <div class="content">
            <div class="warning-badge">
                🚫 Refund request has been declined by the organizer
            </div>

            <p>Hi <strong>{{ $payment->registration->full_name }}</strong>,</p>
            
            <p>We regret to inform you that your refund request for <strong>{{ $payment->registration->event->title }}</strong> has been rejected by the event organizer.</p>

            <div class="event-details">
                <h3 style="margin-top: 0; color: #111827;">Event Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Event:</span>
                    <span class="detail-value">{{ $payment->registration->event->title }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registration Number:</span>
                    <span class="detail-value">{{ $payment->registration->registration_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Amount:</span>
                    <span class="detail-value">RM {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Refund Requested:</span>
                    <span class="detail-value">{{ $payment->refund_requested_at->format('M d, Y h:i A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Rejection Date:</span>
                    <span class="detail-value">{{ $payment->refund_processed_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="rejection-reason">
                <h3>📋 Reason for Rejection</h3>
                <p>{{ $payment->refund_rejection_reason }}</p>
            </div>

            <div class="info-box">
                <h3>💡 What This Means</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li style="margin-bottom: 8px; color: #1e40af;">Your registration remains active and valid</li>
                    <li style="margin-bottom: 8px; color: #1e40af;">No refund will be processed for this registration</li>
                    <li style="margin-bottom: 8px; color: #1e40af;">You are still expected to attend the event</li>
                    <li style="margin-bottom: 0; color: #1e40af;">The payment amount remains with the organizer</li>
                </ul>
            </div>

            <div class="info-box">
                <h3>🤔 Have Questions or Concerns?</h3>
                <p style="margin: 0; color: #1e40af;">
                    If you believe this rejection was made in error or if you have questions about the decision, 
                    please contact the event organizer directly or reach out to our support team.
                </p>
            </p>
            </div>

            <p><strong>Contact Information:</strong></p>
            <ul>
                <li><strong>Event Organizer:</strong> {{ $payment->registration->event->organizer->name ?? 'TAREvent' }}</li>
                @if($payment->registration->event->contact_email)
                <li><strong>Organizer Email:</strong> <a href="mailto:{{ $payment->registration->event->contact_email }}">{{ $payment->registration->event->contact_email }}</a></li>
                @endif
                <li><strong>Support Email:</strong> <a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></li>
            </ul>

            <center>
                <a href="{{ route('events.show', $payment->registration->event) }}" class="cta-button">
                    View Event Details
                </a>
            </center>
        </div>

        <div class="footer">
            <p>For assistance, please contact us at <a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></p>
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