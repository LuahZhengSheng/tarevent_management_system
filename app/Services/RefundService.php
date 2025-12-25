<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Refund as StripeRefund;

class RefundService
{
    /**
     * Request refund (user-initiated)
     */
    public function requestRefund(EventRegistration $registration, string $reason)
    {
        // Validation
        if (!$registration->is_refund_eligible) {
            throw new \Exception('This registration is not eligible for refund.');
        }
        
        $payment = $registration->payment;
        
        if (!$payment) {
            throw new \Exception('No payment found for this registration.');
        }
        
        // Check if already requested
        if (in_array($payment->refund_status, ['pending', 'processing', 'completed'])) {
            throw new \Exception('Refund has already been requested or processed.');
        }
        
        // Rate limiting: only 1 request per registration
        $existingRequest = Payment::where('event_registration_id', $registration->id)
            ->whereNotNull('refund_requested_at')
            ->where('refund_requested_at', '>=', now()->subDay())
            ->count();
        
        if ($existingRequest > 0) {
            throw new \Exception('You can only request refund once per day.');
        }
        
        DB::beginTransaction();
        
        try {
            // Request refund
            $payment->requestRefund($reason, auth()->id());
            
            Log::info('Refund requested by user', [
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'reason' => $reason,
            ]);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Approve and process refund (admin/organizer-initiated)
     */
    public function approveRefund(Payment $payment, $processedBy = null)
    {
        // Validation
        if ($payment->refund_status !== 'pending') {
            throw new \Exception('Only pending refunds can be approved.');
        }
        
        if (!$payment->can_be_refunded) {
            throw new \Exception('This payment is not eligible for refund.');
        }
        
        DB::beginTransaction();
        
        try {
            // Mark as processing
            $payment->processRefund($processedBy ?? auth()->id());
            
            // Call gateway API based on method
            if ($payment->method === 'stripe') {
                $this->processStripeRefund($payment);
            } elseif ($payment->method === 'paypal') {
                $this->processPayPalRefund($payment);
            } else {
                throw new \Exception('Unsupported payment method: ' . $payment->method);
            }
            
            Log::info('Refund approved and processing', [
                'payment_id' => $payment->id,
                'method' => $payment->method,
                'processed_by' => $processedBy ?? auth()->id(),
            ]);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Mark refund as failed
            $payment->update([
                'refund_status' => 'failed',
                'refund_rejection_reason' => $e->getMessage(),
            ]);
            
            Log::error('Refund processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process Stripe refund
     */
    protected function processStripeRefund(Payment $payment)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        
        $idempotencyKey = $payment->generateRefundIdempotencyKey();
        
        try {
            // Use payment_intent_id for refund
            $refundParams = [
                'payment_intent' => $payment->payment_intent_id,
                'amount' => $payment->refund_amount * 100, // Convert to cents
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'payment_id' => $payment->id,
                    'registration_id' => $payment->event_registration_id,
                    'refund_reason' => substr($payment->refund_reason, 0, 500),
                ],
            ];
            
            $refund = StripeRefund::create($refundParams, [
                'idempotency_key' => $idempotencyKey,
            ]);
            
            // Store refund ID temporarily (webhook will confirm)
            $metadata = $payment->refund_metadata ?? [];
            $metadata['stripe_refund_id'] = $refund->id;
            $metadata['stripe_status'] = $refund->status;
            
            $payment->update([
                'refund_metadata' => $metadata,
            ]);
            
            Log::info('Stripe refund created', [
                'payment_id' => $payment->id,
                'refund_id' => $refund->id,
                'status' => $refund->status,
            ]);
            
            return $refund;
        } catch (\Stripe\Exception\CardException $e) {
            throw new \Exception('Stripe card error: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Stripe refund failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Process PayPal refund
     */
    protected function processPayPalRefund(Payment $payment)
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'sandbox');
        
        $baseUrl = $mode === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
        
        try {
            // Get access token
            $ch = curl_init("{$baseUrl}/v1/oauth2/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "{$clientId}:{$secret}");
            curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
            
            $response = curl_exec($ch);
            $tokenData = json_decode($response, true);
            curl_close($ch);
            
            $accessToken = $tokenData['access_token'];
            
            // Get capture ID from metadata or order
            $metadata = $payment->metadata ?? [];
            $captureId = $metadata['capture_id'] ?? null;
            
            if (!$captureId) {
                // Try to get from order
                $orderId = $payment->transaction_id;
                $orderDetails = $this->getPayPalOrderDetails($orderId, $accessToken, $baseUrl);
                
                if (isset($orderDetails['purchase_units'][0]['payments']['captures'][0]['id'])) {
                    $captureId = $orderDetails['purchase_units'][0]['payments']['captures'][0]['id'];
                }
            }
            
            if (!$captureId) {
                throw new \Exception('Cannot find PayPal capture ID for refund.');
            }
            
            // Create refund
            $refundData = [
                'amount' => [
                    'currency_code' => 'MYR',
                    'value' => number_format($payment->refund_amount, 2, '.', ''),
                ],
                'note_to_payer' => substr($payment->refund_reason, 0, 255),
            ];
            
            $ch = curl_init("{$baseUrl}/v2/payments/captures/{$captureId}/refund");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Authorization: Bearer {$accessToken}",
                'PayPal-Request-Id: ' . $payment->generateRefundIdempotencyKey(),
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refundData));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $refundResult = json_decode($response, true);
            
            if ($httpCode !== 201 || !isset($refundResult['id'])) {
                $errorMsg = $refundResult['message'] ?? 'Unknown error';
                throw new \Exception("PayPal refund failed: {$errorMsg}");
            }
            
            // Store refund ID temporarily (webhook will confirm)
            $refundMetadata = $payment->refund_metadata ?? [];
            $refundMetadata['paypal_refund_id'] = $refundResult['id'];
            $refundMetadata['paypal_status'] = $refundResult['status'];
            $refundMetadata['capture_id'] = $captureId;
            
            $payment->update([
                'refund_metadata' => $refundMetadata,
            ]);
            
            Log::info('PayPal refund created', [
                'payment_id' => $payment->id,
                'refund_id' => $refundResult['id'],
                'status' => $refundResult['status'],
            ]);
            
            return $refundResult;
        } catch (\Exception $e) {
            throw new \Exception('PayPal refund failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get PayPal order details
     */
    protected function getPayPalOrderDetails($orderId, $accessToken, $baseUrl)
    {
        $ch = curl_init("{$baseUrl}/v2/checkout/orders/{$orderId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$accessToken}",
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Reject refund request
     */
    public function rejectRefund(Payment $payment, string $reason, $rejectedBy = null)
    {
        if (!in_array($payment->refund_status, ['pending', 'processing'])) {
            throw new \Exception('Only pending or processing refunds can be rejected.');
        }
        
        DB::beginTransaction();
        
        try {
            $payment->rejectRefund($reason, $rejectedBy ?? auth()->id());
            
            Log::info('Refund rejected', [
                'payment_id' => $payment->id,
                'rejected_by' => $rejectedBy ?? auth()->id(),
                'reason' => $reason,
            ]);
            
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Auto-reject expired refund requests
     */
    public function autoRejectExpiredRefunds()
    {
        $expiredRegistrations = EventRegistration::where('refund_status', 'pending')
            ->whereNotNull('refund_auto_reject_at')
            ->where('refund_auto_reject_at', '<=', now())
            ->with('payment')
            ->get();
        
        $rejected = 0;
        
        foreach ($expiredRegistrations as $registration) {
            if ($registration->payment && $registration->payment->refund_status === 'pending') {
                try {
                    $this->rejectRefund(
                        $registration->payment,
                        'Refund request expired - no action taken within the allowed time period.',
                        null // System auto-reject
                    );
                    
                    $rejected++;
                } catch (\Exception $e) {
                    Log::error('Failed to auto-reject expired refund', [
                        'registration_id' => $registration->id,
                        'payment_id' => $registration->payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        Log::info('Auto-rejected expired refund requests', [
            'count' => $rejected,
        ]);
        
        return $rejected;
    }
}