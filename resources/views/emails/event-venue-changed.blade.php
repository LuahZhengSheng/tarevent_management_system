<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Venue Changed</title>
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
            border-bottom: 3px solid #8b5cf6;
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
        .venue-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .old-venue {
            color: #dc2626;
            text-decoration: line-through;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .new-venue {
            color: #16a34a;
            font-size: 24px;
            font-weight: 600;
            margin-top: 10px;
        }
        .map-button {
            display: inline-block;
            padding: 10px 20px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 14px;
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
        .event-info {
            background: #eff6ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
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
            .venue-box, .event-info {
                background: #4b5563;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TAREvent</div>
            <h1>Event Venue Changed</h1>
        </div>

        <div class="alert-box">
            <span class="alert-icon">📍</span>
            <strong>Location Update:</strong> The venue for your registered event has been changed.
        </div>

        <p>Hi {{ $user->full_name ?? $user->name }},</p>

        <p>This is an important update regarding <strong>{{ $event->title }}</strong>.</p>

        <p>The event location has been changed. Please make note of the new venue to ensure you arrive at the correct location.</p>

        <div class="venue-box">
            <h3>{{ $event->title }}</h3>
            
            <div style="text-align: center; padding: 20px;">
                <div class="old-venue">
                    <strong>Previous Venue:</strong><br>
                    {{ $changes['venue']['old'] }}
                </div>
                
                <div style="font-size: 30px; margin: 10px 0;">⬇️</div>
                
                <div class="new-venue">
                    <strong>New Venue:</strong><br>
                    {{ $changes['venue']['new'] }}
                </div>

                @if($event->location_map_url)
                <div style="margin-top: 15px;">
                    <a href="{{ $event->location_map_url }}" class="map-button">📍 View on Map</a>
                </div>
                @endif
            </div>
        </div>

        <div class="event-info">
            <p style="margin: 5px 0;"><strong>Date & Time:</strong> {{ $event->start_time->format('l, F j, Y @ g:i A') }}</p>
            <p style="margin: 5px 0;"><strong>Category:</strong> {{ $event->category }}</p>
            @if($event->contact_phone)
            <p style="margin: 5px 0;"><strong>Contact:</strong> {{ $event->contact_phone }}</p>
            @endif
        </div>

        <p>If you have any questions about the new location or need directions, please don't hesitate to contact the event organizers.</p>

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