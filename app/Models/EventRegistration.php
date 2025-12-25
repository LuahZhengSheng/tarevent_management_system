<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model {

    use HasFactory,
        SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'status', // confirmed / pending_payment / cancelled / waitlisted
//        'payment_id',
        'registration_number', // Unique registration number (e.g., REG-2024-001234)
        'full_name', // Registrant full name
        'email', // Registrant email
        'phone', // Registrant phone
        'student_id', // Student ID number
        'program', // Study program/course
        'emergency_contact_name', // Emergency contact person
        'emergency_contact_phone', // Emergency contact number
        'registration_data', // JSON - Custom registration fields data
        'attended', // Boolean - Did they attend?
        'checked_in_at', // Datetime of check-in
        'cancelled_at', // Datetime when cancelled
        'cancellation_reason', // Why wemaias it cancelled
        'refund_status', // null / pending / completed / failed
        'refund_requested_at', // When user/system requested refund
        'refund_completed_at', // When refund actually completedF
        'refund_auto_reject_at',
        'expires_at',
        'payment_gateway',
        'gateway_session_id',
        'expiry_notified',
        'notes', // Admin notes
        'ip_address',
        'user_agent',
    ];
    protected $casts = [
        'registration_data' => 'array',
        'attended' => 'boolean',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refund_completed_at' => 'datetime',
        'refund_auto_reject_at' => 'datetime',
        'expires_at' => 'datetime',
        'expiry_notified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    protected $attributes = [
        'status' => 'pending_payment',
        'attended' => false,
    ];

    // Relationships
    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

    // Scopes
    public function scopeConfirmed($query) {
        return $query->where('status', 'confirmed');
    }

    public function scopePendingPayment($query) {
        return $query->where('status', 'pending_payment');
    }

    public function scopeCancelled($query) {
        return $query->where('status', 'cancelled');
    }

    public function scopeAttended($query) {
        return $query->where('attended', true);
    }

    public function scopeExpired($query) {
        return $query->where('status', 'pending_payment')
                        ->where('expires_at', '<', now());
    }

    public function scopePendingNotExpired($query) {
        return $query->where('status', 'pending_payment')
                        ->where('expires_at', '>', now());
    }

    // Accessors
    public function getIsConfirmedAttribute() {
        return $this->status === 'confirmed';
    }

    public function getIsPendingPaymentAttribute() {
        return $this->status === 'pending_payment';
    }

    public function getIsCancelledAttribute() {
        return $this->status === 'cancelled';
    }

    public function getIsRefundEligibleAttribute() {
        // Refund eligible if:
        // 1. Event allows refunds
        if (!$this->event || !$this->event->refund_available) {
            return false;
        }

        // 2. Registration is cancelled
        if ($this->status !== 'cancelled') {
            return false;
        }

        // 3. Has payment
        if (!$this->payment) {
            return false;
        }

        // 4. Payment was successful
        if ($this->payment->status !== 'success') {
            return false;
        }

        // 5. Cancellation was before event start
        if (!$this->cancelled_at || $this->cancelled_at >= $this->event->start_time) {
            return false;
        }

        // 6. Not already refunded
        if ($this->payment->refund_status === 'completed') {
            return false;
        }

        return true;
    }

    public function getIsExpiredAttribute() {
        return $this->expires_at && $this->expires_at < now();
    }

    public function getRemainingTimeAttribute() {
        if (!$this->expires_at || $this->is_expired) {
            return null;
        }

        return $this->expires_at->diff(now());
    }

    public function getRemainingMinutesAttribute() {
        if (!$this->expires_at || $this->is_expired) {
            return 0;
        }

        return now()->diffInMinutes($this->expires_at, false);
    }

    // Methods
    public function cancel($reason = null) {
        $data = [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ];

        // 如果符合退款条件，标记为 refund pending，并记录请求时间
        if ($this->is_refund_eligible && $this->payment) {
            $data['refund_status'] = 'pending';
            $data['refund_requested_at'] = now();
        }

        $this->update($data);
    }

    /**
     * Check if this registration can be cancelled
     */
    public function getCanBeCancelledAttribute() {
        // Cannot cancel if already cancelled
        if ($this->status === 'cancelled') {
            return false;
        }

        // Must be confirmed or pending_payment
        if (!in_array($this->status, ['confirmed', 'pending_payment'])) {
            return false;
        }

        // Event must allow cancellation
        if (!$this->event->allow_cancellation) {
            return false;
        }

        $now = now();

        // Can only cancel during registration period
        if ($now < $this->event->registration_start_time ||
                $now > $this->event->registration_end_time) {
            return false;
        }

        // Cannot cancel after event has started
        if ($now >= $this->event->start_time) {
            return false;
        }

        return true;
    }

    /**
     * Get cancellation deadline info
     */
    public function getCancellationDeadlineAttribute() {
        if (!$this->event->allow_cancellation) {
            return null;
        }

        // Registration end time is the deadline
        return $this->event->registration_end_time;
    }

    /**
     * Check if belongs to user
     */
    public function belongsToUser($userId) {
        return $this->user_id == $userId;
    }

    /**
     * Check if refund request is pending approval
     */
    public function getIsRefundPendingAttribute() {
        return $this->refund_status === 'pending' &&
                $this->payment &&
                $this->payment->refund_status === 'pending';
    }

    /**
     * Check if refund should be auto-rejected
     */
    public function shouldAutoRejectRefund() {
        return $this->refund_status === 'pending' &&
                $this->refund_auto_reject_at &&
                now()->gte($this->refund_auto_reject_at);
    }

    public function confirm() {
        $this->update([
            'status' => 'confirmed',
        ]);
    }

    public function checkIn() {
        $this->update([
            'attended' => true,
            'checked_in_at' => now(),
        ]);
    }

    public static function generateRegistrationNumber() {
        $year = now()->year;
        $lastNumber = static::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->value('registration_number');

        if ($lastNumber) {
            $lastNum = (int) substr($lastNumber, -6);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return 'REG-' . $year . '-' . str_pad($newNum, 6, '0', STR_PAD_LEFT);
    }

    // Boot method to auto-generate registration number
    protected static function boot() {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->registration_number)) {
                $registration->registration_number = static::generateRegistrationNumber();
            }
        });
    }
}
