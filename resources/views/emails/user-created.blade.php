<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to TAREvent</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.8;
            color: #1a1a1a;
            max-width: 560px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #fafafa;
        }
        .container {
            background: white;
            padding: 50px 45px;
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .header {
            text-align: center;
            padding-bottom: 40px;
            margin-bottom: 40px;
            border-bottom: 1px solid #e8e8e8;
        }
        .logo {
            font-size: 28px;
            font-weight: 300;
            color: #1a1a1a;
            letter-spacing: 3px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .tagline {
            font-size: 13px;
            color: #888;
            letter-spacing: 1px;
            margin: 0;
        }
        .welcome-section {
            text-align: center;
            margin: 40px 0;
        }
        .welcome-title {
            font-size: 26px;
            font-weight: 300;
            color: #1a1a1a;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }
        .welcome-subtitle {
            font-size: 15px;
            color: #666;
            margin: 0;
        }
        .content-text {
            font-size: 15px;
            color: #4a4a4a;
            margin: 25px 0;
        }
        .account-section {
            background: #fafafa;
            padding: 35px;
            margin: 35px 0;
            border-radius: 2px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 25px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .info-grid {
            display: grid;
            gap: 18px;
        }
        .info-item {
            display: flex;
            align-items: baseline;
        }
        .info-label {
            font-size: 13px;
            color: #888;
            min-width: 110px;
            font-weight: 500;
        }
        .info-value {
            font-size: 15px;
            color: #1a1a1a;
        }
        .divider {
            height: 1px;
            background: #e8e8e8;
            margin: 35px 0;
        }
        .password-section {
            text-align: center;
            margin: 40px 0;
        }
        .password-label {
            font-size: 13px;
            color: #888;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .password-display {
            background: #fafafa;
            padding: 20px;
            border-radius: 2px;
            margin: 15px 0;
            border: 1px solid #e8e8e8;
        }
        .password-text {
            font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
            font-size: 20px;
            font-weight: 500;
            color: #1a1a1a;
            letter-spacing: 3px;
            margin: 0;
        }
        .password-note {
            font-size: 13px;
            color: #666;
            margin-top: 15px;
            line-height: 1.6;
        }
        .notice-box {
            background: white;
            border: 1px solid #e8e8e8;
            padding: 25px;
            margin: 35px 0;
            border-radius: 2px;
        }
        .notice-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 15px 0;
            letter-spacing: 0.5px;
        }
        .notice-text {
            font-size: 14px;
            color: #666;
            margin: 0;
            line-height: 1.7;
        }
        .tips-section {
            margin: 35px 0;
        }
        .tips-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 18px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .tips-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .tips-list li {
            font-size: 14px;
            color: #666;
            padding: 10px 0;
            padding-left: 20px;
            position: relative;
            line-height: 1.6;
        }
        .tips-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #1a1a1a;
            font-weight: bold;
        }
        .features-list {
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }
        .features-list li {
            font-size: 15px;
            color: #4a4a4a;
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        .features-list li:before {
            content: "—";
            position: absolute;
            left: 0;
            color: #1a1a1a;
        }
        .btn {
            display: inline-block;
            padding: 16px 45px;
            background: #1a1a1a;
            color: white;
            text-decoration: none;
            border-radius: 2px;
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 1px;
            margin: 35px 0;
            text-align: center;
            text-transform: uppercase;
            transition: background 0.2s ease;
        }
        .btn:hover {
            background: #333;
        }
        .signature {
            margin-top: 40px;
            font-size: 15px;
            color: #4a4a4a;
            line-height: 1.8;
        }
        .signature-name {
            font-weight: 500;
            color: #1a1a1a;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 35px;
            border-top: 1px solid #e8e8e8;
        }
        .footer-text {
            font-size: 12px;
            color: #999;
            margin: 10px 0;
            line-height: 1.6;
        }
        .footer-links {
            margin-top: 20px;
        }
        .footer-links a {
            color: #666;
            text-decoration: none;
            font-size: 12px;
            margin: 0 12px;
            letter-spacing: 0.5px;
        }
        .footer-links a:hover {
            color: #1a1a1a;
        }
        @media (max-width: 600px) {
            .container {
                padding: 35px 25px;
            }
            .account-section {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TAREvent</div>
            <p class="tagline">Event Management System</p>
        </div>

        <div class="welcome-section">
            <h1 class="welcome-title">Welcome, {{ $user->name }}</h1>
            <p class="welcome-subtitle">Your account has been successfully created</p>
        </div>

        <p class="content-text">
            We are pleased to inform you that your account has been created in the TAREvent Management System. You can now access the platform and start exploring events, forums, and more.
        </p>

        <div class="account-section">
            <h3 class="section-title">Account Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                @if($user->student_id)
                <div class="info-item">
                    <span class="info-label">Student ID</span>
                    <span class="info-value">{{ $user->student_id }}</span>
                </div>
                @endif
                @if($user->program)
                <div class="info-item">
                    <span class="info-label">Program</span>
                    <span class="info-value">{{ $user->program }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="info-value">{{ $user->role === 'club' ? 'Club Organizer' : 'Student' }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="password-section">
            <div class="password-label">Temporary Password</div>
            <div class="password-display">
                <code class="password-text">{{ $password }}</code>
            </div>
            <p class="password-note">
                Please use this password to log in for the first time.<br>
                We strongly recommend changing it after your initial login.
            </p>
        </div>

        <div class="notice-box">
            <div class="notice-title">Security Notice</div>
            <p class="notice-text">
                This password is temporary and should be changed immediately after your first login. 
                Do not share this password with anyone.
            </p>
        </div>

        <div class="tips-section">
            <div class="tips-title">Security Best Practices</div>
            <ul class="tips-list">
                <li>Change your password immediately after first login</li>
                <li>Use a strong, unique password with at least 8 characters</li>
                <li>Never share your password with anyone</li>
                <li>Log out when using shared or public computers</li>
                <li>Contact support immediately if you suspect unauthorized access</li>
            </ul>
        </div>

        <div class="divider"></div>

        <p class="content-text">
            To get started, please log in to your account using the credentials provided above. Once logged in, you can:
        </p>

        <ul class="features-list">
            <li>Browse and register for events</li>
            <li>Participate in forum discussions</li>
            <li>Manage your profile and preferences</li>
            <li>Receive notifications about events and updates</li>
        </ul>

        <center>
            <a href="{{ route('login') }}" class="btn">Log In</a>
        </center>

        <p class="content-text">
            If you have any questions or need assistance, please don't hesitate to contact our support team.
        </p>

        <div class="signature">
            Best regards,<br>
            <span class="signature-name">TAREvent Management System</span><br>
            Administration Team
        </div>

        <div class="footer">
            <p class="footer-text">
                You're receiving this email because an account was created for you<br>
                in the TAREvent Management System.
            </p>
            <p class="footer-text">
                &copy; {{ date('Y') }} TAREvent Management System. All rights reserved.
            </p>
            <div class="footer-links">
                <a href="{{ route('home') }}">Website</a>
                <a href="{{ route('login') }}">Log In</a>
                <a href="{{ route('events.index') }}">Events</a>
            </div>
        </div>
    </div>
</body>
</html>