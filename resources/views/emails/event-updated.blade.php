<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Updated</title>
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
            border-bottom: 3px solid #3b82f6;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
        }
        .alert-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TAREvent</div>
            <h1>Event Updated</h1>
        </div>

        <div class="alert-box">
            <span class="alert-icon">📝</span>
            <strong>Update:</strong> Details for your registered event have been updated.
        </div>

        <p>Hi {{ $user->full_name ?? $user->name }},</p>

        <p>Some details for <strong>{{ $event->title }}</strong> have been updated.</p>

        <div class="event-details">
            <h3>{{ $event->title }}</h3>
            <p><strong>Date:</strong> {{ $event->start_time->format('l, F j, Y @ g:i A') }}</p>
            <p><strong>Venue:</strong> {{ $event->venue }}</p>
            <p><strong>Category:</strong> {{ $event->category }}</p>
        </div>

        <p>Please check the event page for full details about what has changed.</p>

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