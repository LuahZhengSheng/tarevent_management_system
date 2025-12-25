<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model {

    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'event_registration_id',
        'amount',
        'method', // stripe / paypal
        'transaction_id', // Main transaction ID
        'payment_intent_id', // Stripe Payment Intent ID
        'payer_email',
        'payer_name',
        'metadata', // JSON field for additional data
        'status', // pending / success / failed
        'paid_at',
        // Refund fields
        'refund_status', // null / pending / processing / completed / rejected
        'refund_amount',
        'refund_transaction_id',
        'refund_requested_at',
        'refund_processed_at',
        'refund_reason',
        'refund_requested_by',
        'refund_processed_by',
        'refund_rejection_reason',
        'refund_idempotency_key',
        'refund_metadata',
        // Error tracking
        'error_message',
        'error_code',
    ];
    protected $casts = [
        'paid_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refund_processed_at' => 'datetime',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refund_metadata' => 'array',
    ];
    protected $attributes = [
        'status' => 'pending',
    ];

    // Relationships
    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function registration() {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function refundRequestedBy() {
        return $this->belongsTo(User::class, 'refund_requested_by');
    }

    public function refundProcessedBy() {
        return $this->belongsTo(User::class, 'refund_processed_by');
    }

    // Scopes
    public function scopeSuccessful($query) {
        return $query->where('status', 'success');
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query) {
        return $query->where('status', 'failed');
    }

    public function scopeByMethod($query, $method) {
        return $query->where('method', $method);
    }

    public function scopeStripe($query) {
        return $query->where('method', 'stripe');
    }

    public function scopePaypal($query) {
        return $query->where('method', 'paypal');
    }

    public function scopeRefundable($query) {
        return $query->where('status', 'success')
                        ->whereNull('refund_status');
    }

    public function scopeRefundPending($query) {
        return $query->where('refund_status', 'pending');
    }

    public function scopeRefundProcessing($query) {
        return $query->where('refund_status', 'processing');
    }

    public function scopeRefundCompleted($query) {
        return $query->where('refund_status', 'completed');
    }

    public function scopeRefundRejected($query) {
        return $query->where('refund_status', 'rejected');
    }

    // Accessors
    public function getIsSuccessfulAttribute() {
        return $this->status === 'success';
    }

    public function getIsPendingAttribute() {
        return $this->status === 'pending';
    }

    public function getIsFailedAttribute() {
        return $this->status === 'failed';
    }

    public function getIsRefundedAttribute() {
        return $this->refund_status === 'completed';
    }

    public function getCanBeRefundedAttribute() {
        // Cannot refund if:
        // 1. Payment was not successful
        if ($this->status !== 'success') {
            return false;
        }

        // 2. Already refunded or refund in progress
        if (in_array($this->refund_status, ['completed', 'processing'])) {
            return false;
        }

        // 3. No associated registration
        if (!$this->registration) {
            return false;
        }

        // 4. Registration not cancelled
        if ($this->registration->status !== 'cancelled') {
            return false;
        }

        // 5. Event doesn't allow refunds
        if (!$this->registration->event || !$this->registration->event->refund_available) {
            return false;
        }

        // 6. Cancellation was after event started (not eligible)
        if (!$this->registration->is_refund_eligible) {
            return false;
        }

        return true;
    }

    public function getFormattedAmountAttribute() {
        return 'RM ' . number_format($this->amount, 2);
    }

    public function getFormattedRefundAmountAttribute() {
        return $this->refund_amount ? 'RM ' . number_format($this->refund_amount, 2) : null;
    }

    public function getPaymentMethodNameAttribute() {
        return match ($this->method) {
            'stripe' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
            default => ucfirst($this->method),
        };
    }

    public function getIsRefundPendingAttribute() {
        return $this->refund_status === 'pending';
    }

    public function getIsRefundProcessingAttribute() {
        return $this->refund_status === 'processing';
    }

    public function getIsRefundCompletedAttribute() {
        return $this->refund_status === 'completed';
    }

    public function getIsRefundRejectedAttribute() {
        return $this->refund_status === 'rejected';
    }

    // Methods

    /**
     * Mark payment as successful
     */
    public function markAsSuccessful($transactionData = []) {
        $updateData = [
            'status' => 'success',
            'paid_at' => now(),
        ];

        // Store additional transaction data in metadata
        if (!empty($transactionData)) {
            $existingMetadata = $this->metadata ?? [];
            $updateData['metadata'] = array_merge($existingMetadata, $transactionData);
        }

        return $this->update($updateData);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason) {
        $meta = $this->metadata ?? [];
        $meta['failed_reason'] = $reason;

        $this->update([
            'status' => 'failed',
            'error_message' => $reason,
            'metadata' => $meta,
        ]);
    }

    /**
     * Request refund (by user or admin)
     */
    public function requestRefund($reason, $requestedBy = null) {
        if (!$this->can_be_refunded) {
            throw new \Exception('This payment cannot be refunded.');
        }

        // Check if already requested
        if (in_array($this->refund_status, ['pending', 'processing', 'completed'])) {
            throw new \Exception('Refund has already been requested or processed.');
        }

        $requestedBy = $requestedBy ?? auth()->id();

        // Calculate auto-reject deadline (e.g., 7 days)
        $autoRejectAt = now()->addDays(7);

        $this->update([
            'refund_status' => 'pending',
            'refund_amount' => $this->amount,
            'refund_requested_at' => now(),
            'refund_reason' => $reason,
            'refund_requested_by' => $requestedBy,
        ]);

        // Update registration
        if ($this->registration) {
            $this->registration->update([
                'refund_status' => 'pending',
                'refund_requested_at' => now(),
                'refund_auto_reject_at' => $autoRejectAt,
            ]);
        }

        return true;
    }

    /**
     * Process refund (call gateway API)
     */
    public function processRefund($processedBy = null) {
        if ($this->refund_status !== 'pending') {
            throw new \Exception('Only pending refunds can be processed.');
        }

        // Mark as processing
        $this->update([
            'refund_status' => 'processing',
            'refund_processed_by' => $processedBy ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Mark refund as completed (called by webhook)
     */
    public function completeRefund($refundTransactionId, $metadata = []) {
        $updateData = [
            'refund_status' => 'completed',
            'refund_transaction_id' => $refundTransactionId,
            'refund_processed_at' => now(),
        ];

        if (!empty($metadata)) {
            $existingMetadata = $this->refund_metadata ?? [];
            $updateData['refund_metadata'] = array_merge($existingMetadata, $metadata);
        }

        $this->update($updateData);

        // Update registration
        if ($this->registration) {
            $this->registration->update([
                'refund_status' => 'completed',
                'refund_completed_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Reject refund request
     */
    public function rejectRefund($reason, $rejectedBy = null) {
        if (!in_array($this->refund_status, ['pending', 'processing'])) {
            throw new \Exception('Only pending or processing refunds can be rejected.');
        }

        $this->update([
            'refund_status' => 'rejected',
            'refund_rejection_reason' => $reason,
            'refund_processed_by' => $rejectedBy ?? auth()->id(),
            'refund_processed_at' => now(),
        ]);

        // Update registration
        if ($this->registration) {
            $this->registration->update([
                'refund_status' => 'rejected',
            ]);
        }

        return true;
    }

    /**
     * Generate idempotency key for refund
     */
    public function generateRefundIdempotencyKey() {
        if ($this->refund_idempotency_key) {
            return $this->refund_idempotency_key;
        }

        $key = 'refund_payment_' . $this->id . '_' . now()->timestamp;

        $this->update([
            'refund_idempotency_key' => $key,
        ]);

        return $key;
    }

    /**
     * Get payment gateway icon
     */
    public function getGatewayIcon() {
        return match ($this->method) {
            'stripe' => '<i class="bi bi-credit-card-2-front"></i>',
            'paypal' => '<i class="bi bi-paypal"></i>',
            default => '<i class="bi bi-cash"></i>',
        };
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge() {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'success' => '<span class="badge bg-success">Successful</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get refund status badge HTML
     */
    public function getRefundStatusBadge() {
        if (!$this->refund_status) {
            return null;
        }

        $badges = [
            'pending' => '<span class="badge bg-warning">Refund Pending</span>',
            'processing' => '<span class="badge bg-info">Refund Processing</span>',
            'completed' => '<span class="badge bg-success">Refunded</span>',
            'rejected' => '<span class="badge bg-danger">Refund Rejected</span>',
        ];

        return $badges[$this->refund_status] ?? null;
    }

    /**
     * Boot method - Observer for automatic actions
     */
    protected static function boot() {
        parent::boot();

        // When payment is marked as successful, update registration status
        static::updated(function ($payment) {
            if ($payment->isDirty('status') && $payment->status === 'success') {
                // Update registration to confirmed
                if ($payment->registration && $payment->registration->status === 'pending_payment') {
                    $payment->registration->update([
                        'status' => 'confirmed',
                        'payment_id' => $payment->id,
                    ]);
                }
            }
        });
    }
}
