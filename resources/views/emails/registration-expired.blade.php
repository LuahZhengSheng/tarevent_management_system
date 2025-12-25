<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Expired</title>
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
            background: linear-gradient(135deg, #f59e0b, #dc2626);
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
            background-color: #fef3c7;
            color: #92400e;
            padding: 12px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;
            border-left: 4px solid #f59e0b;
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
        .info-box {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2563eb;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #1e40af;
        }
        .info-box p {
            margin: 0;
            color: #1e3a8a;
            line-height: 1.6;
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
            <h1>⏰ Registration Expired</h1>
            <p>Payment time limit reached</p>
        </div>

        <div class="content">
            <div class="warning-badge">
                ⚠️ Your registration has expired due to incomplete payment
            </div>

            <p>Hi <strong>{{ $registration->full_name }}</strong>,</p>
            
            <p>We're sorry to inform you that your registration for <strong>{{ $registration->event->title }}</strong> has expired because payment was not completed within the 30-minute time limit.</p>

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
                    <span class="detail-label">Registration Number:</span>
                    <span class="detail-value">{{ $registration->registration_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expired At:</span>
                    <span class="detail-value">{{ $registration->expires_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>ℹ️ What Happened?</h3>
                <p>To ensure fair access to limited event spots, we require payment to be completed within 30 minutes of registration. Your registration slot has been released back to the available pool.</p>
            </div>

            @if($registration->event->remaining_seats > 0 && $registration->event->is_registration_open)
            <div class="info-box" style="background-color: #d1fae5; border-left-color: #10b981;">
                <h3 style="color: #065f46;">✅ Good News!</h3>
                <p style="color: #047857;">There are still <strong>{{ $registration->event->remaining_seats }} spot(s)</strong> available for this event. You can register again if you're still interested.</p>
            </div>

            <center>
                <a href="{{ route('events.show', $registration->event) }}" class="cta-button">
                    Register Again
                </a>
            </center>
            @else
            <div class="info-box" style="background-color: #fee2e2; border-left-color: #dc2626;">
                <h3 style="color: #991b1b;">❌ Event Status</h3>
                <p style="color: #7f1d1d;">
                    @if($registration->event->is_full)
                    Unfortunately, this event is now fully booked.
                    @else
                    Registration for this event is currently closed.
                    @endif
                </p>
            </div>
            @endif

            <p><strong>Need Help?</strong></p>
            <ul>
                <li>If you experienced technical difficulties during payment, please contact us</li>
                <li>Make sure to complete payment promptly in future registrations</li>
                <li>Check your internet connection before starting the payment process</li>
            </ul>
        </div>

        <div class="footer">
            <p>If you have any questions or concerns, please contact us at <a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></p>
            <p style="margin-top: 15px;">
                <a href="{{ route('events.index') }}">Browse More Events</a> | 
                <a href="{{ route('events.my') }}">My Events</a>
            </p>
            <p style="margin-top: 15px; color: #9ca3af; font-size: 12px;">
                © {{ date('Y') }} TAREvent Management System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>