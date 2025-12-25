<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Refund Receipt - {{ $registration->registration_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #10b981;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #10b981;
            font-size: 28pt;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #666;
            font-size: 18pt;
            font-weight: normal;
        }
        .receipt-info {
            background-color: #d1fae5;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #10b981;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 8px 0;
        }
        .receipt-info .label {
            font-weight: bold;
            color: #065f46;
            width: 180px;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #10b981;
            margin: 30px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-table .label {
            font-weight: bold;
            color: #6b7280;
            width: 180px;
        }
        .info-table .value {
            color: #111827;
        }
        .refund-summary {
            background-color: #d1fae5;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            border: 2px solid #10b981;
        }
        .refund-summary table {
            width: 100%;
        }
        .refund-summary td {
            padding: 8px 0;
        }
        .refund-summary .total-row {
            border-top: 2px solid #10b981;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14pt;
            font-weight: bold;
        }
        .refund-summary .total-label {
            color: #10b981;
        }
        .refund-summary .total-amount {
            color: #10b981;
            text-align: right;
        }
        .success-badge {
            background-color: #d1fae5;
            color: #065f46;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 12pt;
            border: 2px solid #10b981;
        }
        .refund-reason-box {
            background-color: #fef3c7;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .refund-reason-box h4 {
            color: #92400e;
            margin-bottom: 10px;
        }
        .refund-reason-box p {
            color: #78350f;
            font-style: italic;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 9pt;
        }
        .footer p {
            margin: 5px 0;
        }
        .important-notes {
            background-color: #dbeafe;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .important-notes h4 {
            color: #1e40af;
            margin-bottom: 10px;
        }
        .important-notes ul {
            margin-left: 20px;
            color: #1e3a8a;
        }
        .important-notes li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>REFUND RECEIPT</h1>
            <h2>TAREvent Management System</h2>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <table>
                <tr>
                    <td class="label">Refund Receipt Number:</td>
                    <td><strong>{{ $payment->refund_transaction_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Original Receipt Number:</td>
                    <td><strong>{{ $payment->transaction_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Registration Number:</td>
                    <td><strong>{{ $registration->registration_number }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Refund Date:</td>
                    <td>{{ $payment->refund_processed_at->format('F j, Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Generated On:</td>
                    <td>{{ $generated_at->format('F j, Y h:i A') }}</td>
                </tr>
            </table>
        </div>

        <div class="success-badge">
            ✓ REFUND PROCESSED SUCCESSFULLY
        </div>

        <!-- Event Details -->
        <div class="section-title">EVENT DETAILS</div>
        <table class="info-table">
            <tr>
                <td class="label">Event Name:</td>
                <td class="value"><strong>{{ $event->title }}</strong></td>
            </tr>
            <tr>
                <td class="label">Event Date:</td>
                <td class="value">{{ $event->start_time->format('l, F j, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Event Time:</td>
                <td class="value">{{ $event->start_time->format('h:i A') }} - {{ $event->end_time->format('h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Venue:</td>
                <td class="value">{{ $event->venue }}</td>
            </tr>
            <tr>
                <td class="label">Organized By:</td>
                <td class="value">{{ $event->organizer->name ?? 'TARCampus' }}</td>
            </tr>
        </table>

        <!-- Participant Details -->
        <div class="section-title">PARTICIPANT DETAILS</div>
        <table class="info-table">
            <tr>
                <td class="label">Full Name:</td>
                <td class="value">{{ $registration->full_name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td class="value">{{ $registration->email }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td class="value">{{ $registration->student_id }}</td>
            </tr>
        </table>

        <!-- Refund Reason -->
        @if($payment->refund_reason)
        <div class="refund-reason-box">
            <h4>REFUND REASON:</h4>
            <p>{{ $payment->refund_reason }}</p>
        </div>
        @endif

        <!-- Refund Summary -->
        <div class="section-title">REFUND SUMMARY</div>
        <div class="refund-summary">
            <table>
                <tr>
                    <td><strong>Original Payment Amount</strong></td>
                    <td style="text-align: right;">RM {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Refund Amount</strong></td>
                    <td style="text-align: right;">RM {{ number_format($payment->refund_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="total-label">TOTAL REFUNDED</td>
                    <td class="total-amount">RM {{ number_format($payment->refund_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Refund Details -->
        <div class="section-title">REFUND TRANSACTION DETAILS</div>
        <table class="info-table">
            <tr>
                <td class="label">Refund Method:</td>
                <td class="value">{{ $payment->payment_method_name }}</td>
            </tr>
            <tr>
                <td class="label">Refund Transaction ID:</td>
                <td class="value">{{ $payment->refund_transaction_id }}</td>
            </tr>
            <tr>
                <td class="label">Original Transaction ID:</td>
                <td class="value">{{ $payment->transaction_id }}</td>
            </tr>
            <tr>
                <td class="label">Refund Status:</td>
                <td class="value"><strong>COMPLETED</strong></td>
            </tr>
            <tr>
                <td class="label">Refund Requested On:</td>
                <td class="value">{{ $payment->refund_requested_at->format('F j, Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Refund Processed On:</td>
                <td class="value">{{ $payment->refund_processed_at->format('F j, Y h:i A') }}</td>
            </tr>
        </table>

        <!-- Original Payment Details -->
        <div class="section-title">ORIGINAL PAYMENT DETAILS</div>
        <table class="info-table">
            <tr>
                <td class="label">Original Payment Date:</td>
                <td class="value">{{ $payment->paid_at->format('F j, Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Registration Cancelled On:</td>
                <td class="value">{{ $registration->cancelled_at ? $registration->cancelled_at->format('F j, Y h:i A') : 'N/A' }}</td>
            </tr>
        </table>

        <!-- Important Notes -->
        <div class="important-notes">
            <h4>REFUND PROCESSING INFORMATION:</h4>
            <ul>
                @if($payment->method === 'stripe')
                <li>Refunds typically appear in your account within 5-10 business days</li>
                <li>The exact timing depends on your card issuer's processing time</li>
                @elseif($payment->method === 'paypal')
                <li>Refunds typically appear in your PayPal account within 3-5 business days</li>
                @else
                <li>Refunds typically appear in your account within 5-10 business days</li>
                @endif
                <li>The refund will be credited to the same payment method used for the original purchase</li>
                <li>If you don't see the refund after the expected timeframe, contact your bank or payment provider</li>
                <li>Keep this receipt for your records</li>
                <li>For any inquiries, contact: {{ $event->contact_email ?? 'events@tarc.edu.my' }}</li>
            </ul>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>TAREvent Management System</strong></p>
            <p>Tunku Abdul Rahman University College</p>
            <p>Kuala Lumpur, Malaysia</p>
            <p style="margin-top: 15px;">Email: events@tarc.edu.my | Phone: +60 3-1234 5678</p>
            <p style="margin-top: 10px; color: #9ca3af;">
                This is a computer-generated refund receipt and does not require a signature.
            </p>
            <p style="color: #9ca3af;">Receipt generated on {{ $generated_at->format('F j, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>