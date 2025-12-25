<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - {{ $registration->registration_number }}</title>
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
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 28pt;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #666;
            font-size: 18pt;
            font-weight: normal;
        }
        .receipt-info {
            background-color: #f0f9ff;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #2563eb;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 8px 0;
        }
        .receipt-info .label {
            font-weight: bold;
            color: #1e40af;
            width: 180px;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #2563eb;
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
        .payment-summary {
            background-color: #f9fafb;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }
        .payment-summary table {
            width: 100%;
        }
        .payment-summary td {
            padding: 8px 0;
        }
        .payment-summary .total-row {
            border-top: 2px solid #2563eb;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14pt;
            font-weight: bold;
        }
        .payment-summary .total-label {
            color: #2563eb;
        }
        .payment-summary .total-amount {
            color: #2563eb;
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
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .important-notes {
            background-color: #fef3c7;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .important-notes h4 {
            color: #92400e;
            margin-bottom: 10px;
        }
        .important-notes ul {
            margin-left: 20px;
            color: #78350f;
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
            <h1>PAYMENT RECEIPT</h1>
            <h2>TAREvent Management System</h2>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <table>
                <tr>
                    <td class="label">Receipt Number:</td>
                    <td><strong>{{ $payment->transaction_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Registration Number:</td>
                    <td><strong>{{ $registration->registration_number }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Date Issued:</td>
                    <td>{{ $payment->paid_at->format('F j, Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Generated On:</td>
                    <td>{{ $generated_at->format('F j, Y h:i A') }}</td>
                </tr>
            </table>
        </div>

        <div class="success-badge">
            ✓ PAYMENT CONFIRMED - REGISTRATION COMPLETE
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
                <td class="label">Category:</td>
                <td class="value">{{ $event->category }}</td>
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
                <td class="label">Phone:</td>
                <td class="value">{{ $registration->phone }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td class="value">{{ $registration->student_id }}</td>
            </tr>
            <tr>
                <td class="label">Program:</td>
                <td class="value">{{ $registration->program }}</td>
            </tr>
            @if($event->require_emergency_contact && $registration->emergency_contact_name)
            <tr>
                <td class="label">Emergency Contact:</td>
                <td class="value">{{ $registration->emergency_contact_name }} ({{ $registration->emergency_contact_phone }})</td>
            </tr>
            @endif
        </table>

        <!-- Payment Summary -->
        <div class="section-title">PAYMENT SUMMARY</div>
        <div class="payment-summary">
            <table>
                <tr>
                    <td><strong>Event Registration Fee</strong></td>
                    <td style="text-align: right;">RM {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="total-label">TOTAL PAID</td>
                    <td class="total-amount">RM {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Details -->
        <div class="section-title">PAYMENT DETAILS</div>
        <table class="info-table">
            <tr>
                <td class="label">Payment Method:</td>
                <td class="value">{{ $payment->payment_method_name }}</td>
            </tr>
            <tr>
                <td class="label">Transaction ID:</td>
                <td class="value">{{ $payment->transaction_id }}</td>
            </tr>
            @if($payment->payment_intent_id)
            <tr>
                <td class="label">Payment Intent ID:</td>
                <td class="value">{{ $payment->payment_intent_id }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Payment Status:</td>
                <td class="value"><strong>SUCCESS</strong></td>
            </tr>
            <tr>
                <td class="label">Payment Date:</td>
                <td class="value">{{ $payment->paid_at->format('F j, Y h:i A') }}</td>
            </tr>
        </table>

        <!-- Important Notes -->
        <div class="important-notes">
            <h4>IMPORTANT NOTES:</h4>
            <ul>
                <li>Please bring this receipt (printed or digital) on the event day</li>
                <li>Arrive at least 15 minutes before the event starts</li>
                <li>Bring your student ID for verification</li>
                @if($event->refund_available)
                <li>Refunds are available if you cancel before {{ $event->start_time->format('F j, Y') }}</li>
                @else
                <li>This is a non-refundable registration</li>
                @endif
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
                This is a computer-generated receipt and does not require a signature.
            </p>
            <p style="color: #9ca3af;">Receipt generated on {{ $generated_at->format('F j, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>