<?php

namespace App\Support;

use App\Models\Payment;
use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfHelper
{
    /**
     * Generate payment receipt PDF
     */
    public static function generateReceipt(Payment $payment, $download = true)
    {
        $registration = $payment->registration;
        $event = $registration->event;
        $user = $registration->user;
        
        $data = [
            'payment' => $payment,
            'registration' => $registration,
            'event' => $event,
            'user' => $user,
            'generated_at' => now(),
        ];
        
        $pdf = Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
        
        $filename = self::generateReceiptFilename($payment);
        
        if ($download) {
            return $pdf->download($filename);
        }
        
        return $pdf;
    }
    
    /**
     * Generate refund receipt PDF
     */
    public static function generateRefundReceipt(Payment $payment, $download = true)
    {
        if ($payment->refund_status !== 'completed') {
            throw new \Exception('Cannot generate refund receipt for incomplete refund.');
        }
        
        $registration = $payment->registration;
        $event = $registration->event;
        $user = $registration->user;
        
        $data = [
            'payment' => $payment,
            'registration' => $registration,
            'event' => $event,
            'user' => $user,
            'generated_at' => now(),
        ];
        
        $pdf = Pdf::loadView('pdf.refund-receipt', $data)
            ->setPaper('a4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
        
        $filename = self::generateRefundReceiptFilename($payment);
        
        if ($download) {
            return $pdf->download($filename);
        }
        
        return $pdf;
    }
    
    /**
     * Generate receipt filename
     */
    protected static function generateReceiptFilename(Payment $payment)
    {
        $registration = $payment->registration;
        $regNumber = $registration->registration_number ?? $registration->id;
        $date = $payment->paid_at ? $payment->paid_at->format('Ymd') : now()->format('Ymd');
        
        return "Receipt_{$regNumber}_{$date}.pdf";
    }
    
    /**
     * Generate refund receipt filename
     */
    protected static function generateRefundReceiptFilename(Payment $payment)
    {
        $registration = $payment->registration;
        $regNumber = $registration->registration_number ?? $registration->id;
        $date = $payment->refund_processed_at ? $payment->refund_processed_at->format('Ymd') : now()->format('Ymd');
        
        return "Refund_Receipt_{$regNumber}_{$date}.pdf";
    }
    
    /**
     * Save receipt to storage
     */
    public static function saveReceipt(Payment $payment)
    {
        $pdf = self::generateReceipt($payment, false);
        $filename = self::generateReceiptFilename($payment);
        
        $path = "receipts/{$payment->user_id}/{$filename}";
        
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }
    
    /**
     * Save refund receipt to storage
     */
    public static function saveRefundReceipt(Payment $payment)
    {
        $pdf = self::generateRefundReceipt($payment, false);
        $filename = self::generateRefundReceiptFilename($payment);
        
        $path = "refund-receipts/{$payment->user_id}/{$filename}";
        
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }
}