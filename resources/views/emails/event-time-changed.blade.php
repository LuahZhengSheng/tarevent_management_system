<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Time Changed</title>
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
            border-bottom: 3px solid #f59e0b;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
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
        .change-box {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        .old-value {
            color: #dc2626;
            text-decoration: line-through;
        }
        .new-value {
            color: #16a34a;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white !important;
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
            .change-box {
                background: #374151;
                border-color: #6b7280;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TAREvent</div>
            <h1>Event Time Changed</h1>
        </div>

        <div class="alert-box">
            <span class="alert-icon">⏰</span>
            <strong>Important Update:</strong> The schedule for your registered event has been changed.
        </div>

        <p>Hi {{ $user->full_name ?? $user->name }},</p>

        <p>We wanted to inform you that the timing for <strong>{{ $event->title }}</strong> has been updated.</p>

        <div class="event-details">
            <h3>{{ $event->title }}</h3>
            
            @if(isset($changes['start_time']))
            <div class="change-box">
                <div>
                    <strong>Start Time:</strong><br>
                    <span class="old-value">{{ \Carbon\Carbon::parse($changes['start_time']['old'])->format('l, F j, Y @ g:i A') }}</span>
                </div>
                <div style="text-align: right;">
                    <strong>New Time:</strong><br>
                    <span class="new-value">{{ \Carbon\Carbon::parse($changes['start_time']['new'])->format('l, F j, Y @ g:i A') }}</span>
                </div>
            </div>
            @endif

            @if(isset($changes['end_time']))
            <div class="change-box">
                <div>
                    <strong>End Time:</strong><br>
                    <span class="old-value">{{ \Carbon\Carbon::parse($changes['end_time']['old'])->format('l, F j, Y @ g:i A') }}</span>
                </div>
                <div style="text-align: right;">
                    <strong>New Time:</strong><br>
                    <span class="new-value">{{ \Carbon\Carbon::parse($changes['end_time']['new'])->format('l, F j, Y @ g:i A') }}</span>
                </div>
            </div>
            @endif

            <p><strong>Venue:</strong> {{ $event->venue }}</p>
            <p><strong>Category:</strong> {{ $event->category }}</p>
        </div>

        <p>Please update your calendar accordingly. If you can no longer attend due to this change, you may cancel your registration from your dashboard.</p>

        <center>
            <a href="{{ route('events.show', $event) }}" class="btn">View Event Details</a>
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