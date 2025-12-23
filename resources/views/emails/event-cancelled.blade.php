<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Cancelled</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #dc2626;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }
        .alert-box {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-icon {
            font-size: 24px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .event-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .reason-box {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .refund-box {
            background: #d1fae5;
            border: 1px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1f2937;
            }
            .container {
                background: #374151;
                color: #f9fafb;
            }
            .event-details {
                background: #4b5563;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TAREvent</div>
            <h1>Event Cancelled</h1>
        </div>

        <div class="alert-box">
            <span class="alert-icon">❌</span>
            <strong>Event Cancellation Notice:</strong> An event you registered for has been cancelled.
        </div>

        <p>Hi {{ $user->full_name ?? $user->name }},</p>

        <p>We regret to inform you that <strong>{{ $event->title }}</strong> has been cancelled.</p>

        <div class="event-details">
            <h3>{{ $event->title }}</h3>
            <p><strong>Original Date:</strong> {{ $event->start_time->format('l, F j, Y @ g:i A') }}</p>
            <p><strong>Venue:</strong> {{ $event->venue }}</p>
            <p><strong>Category:</strong> {{ $event->category }}</p>
        </div>

        @if($event->cancelled_reason)
        <div class="reason-box">
            <h4 style="margin-top: 0;">Reason for Cancellation:</h4>
            <p style="margin-bottom: 0;">{{ $event->cancelled_reason }}</p>
        </div>
        @endif

        @if($event->is_paid && $event->refund_available)
        <div class="refund-box">
            <h4 style="margin-top: 0;">💰 Refund Information</h4>
            <p style="margin-bottom: 0;">
                Your registration fee of <strong>RM {{ number_format($event->fee_amount, 2) }}</strong> will be refunded. 
                Please allow 5-7 business days for the refund to be processed and credited to your original payment method.
            </p>
        </div>
        @endif

        <p>We sincerely apologize for any inconvenience this may cause. We hope to see you at our future events!</p>

        <center>
            <a href="{{ route('events.index') }}" class="btn">Browse Other Events</a>
        </center>

        <div class="footer">
            <p>You're receiving this email because you registered for this event.</p>
            <p>&copy; {{ date('Y') }} TAREvent Management System. All rights reserved.</p>
            <p>
                <a href="{{ route('events.my') }}" style="color: #2563eb;">My Events</a> | 
                <a href="{{ route('notifications.index') }}" style="color: #2563eb;">Notifications</a>
            </p>
        </div>
    </div>
</body>
</html>