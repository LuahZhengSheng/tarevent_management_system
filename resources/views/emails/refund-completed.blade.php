<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Completed</title>
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
            background: linear-gradient(135deg, #10b981, #059669);
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
        .refund-amount {
            text-align: center;
            margin: 30px 0;
        }
        .refund-amount .amount {
            font-size: 48px;
            font-weight: 700;
            color: #10b981;
            margin: 0;
        }
        .refund-amount .label {
            font-size: 16px;
            color: #6b7280;
            margin-top: 5px;
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
        .refund-info {
            background-color: #dbeafe;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2563eb;
        }
        .refund-info h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #1e40af;
        }
        .attachment-notice {
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #10b981;
            font-size: 14px;
            color: #065f46;
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
            <h1>Refund Completed</h1>
            <p>Your refund has been processed successfully</p>
        </div>

        <div class="content">
            <div class="success-badge">
                💰 Refund processed and sent to your payment method
            </div>

            <p>Hi <strong>{{ $payment->registration->full_name }}</strong>,</p>
            
            <p>Great news! Your refund request for <strong>{{ $payment->registration->event->title }}</strong> has been processed successfully.</p>

            <div class="refund-amount">
                <div class="amount">RM {{ number_format($payment->refund_amount, 2) }}</div>
                <div class="label">Refunded Amount</div>
            </div>

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
                    <span class="detail-label">Original Payment:</span>
                    <span class="detail-value">RM {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Refund Amount:</span>
                    <span class="detail-value">RM {{ number_format($payment->refund_amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ ucfirst($payment->method) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Refund Transaction ID:</span>
                    <span class="detail-value">{{ $payment->refund_transaction_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Refund Date:</span>
                    <span class="detail-value">{{ $payment->refund_processed_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="refund-info">
                <h3>📋 Refund Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Refund Reason:</span>
                    <span class="detail-value">{{ $payment->refund_reason }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Requested On:</span>
                    <span class="detail-value">{{ $payment->refund_requested_at->format('M d, Y h:i A') }}</span>
                </div>
            </div>

            <div class="attachment-notice">
                <strong>📎 Refund Receipt Attached</strong><br>
                A detailed refund receipt has been attached to this email for your records.
            </div>

            <div class="refund-info">
                <h3>⏰ When Will I Receive My Refund?</h3>
                <p style="margin: 0; color: #1e40af;">
                    @if($payment->method === 'stripe')
                    Refunds typically appear in your account within <strong>5-10 business days</strong>, depending on your card issuer.
                    @elseif($payment->method === 'paypal')
                    Refunds typically appear in your PayPal account within <strong>3-5 business days</strong>.
                    @else
                    Refunds typically appear in your account within <strong>5-10 business days</strong>.
                    @endif
                </p>
            </div>

            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>The refund has been sent to the same payment method used for the original transaction</li>
                <li>The exact timing depends on your financial institution's processing time</li>
                <li>If you don't see the refund after the expected timeframe, please contact your bank or payment provider</li>
                <li>Keep this email and the attached receipt for your records</li>
            </ul>
        </div>

        <div class="footer">
            <p>If you have any questions about your refund, please contact us at <a href="mailto:events@tarc.edu.my">events@tarc.edu.my</a></p>
            <p style="margin-top: 15px;">
                <a href="{{ route('events.index') }}">Browse More Events</a> | 
                <a href="{{ route('events.my') }}">View My Events</a>
            </p>
            <p style="margin-top: 15px; color: #9ca3af; font-size: 12px;">
                © {{ date('Y') }} TAREvent Management System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>