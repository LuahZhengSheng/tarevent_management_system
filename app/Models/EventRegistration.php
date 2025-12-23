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
        'refund_status', // null / pending / processed / rejected
        'refund_processed_at', // When refund was processed
        'notes', // Admin notes
    ];
    protected $casts = [
        'registration_data' => 'array',
        'attended' => 'boolean',
        'checked_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refund_processed_at' => 'datetime',
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
        // 2. Registration is cancelled
        // 3. Cancellation was before event start
        return $this->event->refund_available &&
                $this->is_cancelled &&
                $this->cancelled_at < $this->event->start_time;
    }

    // Methods
    public function cancel($reason = null) {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // If eligible for refund, mark for processing
        if ($this->is_refund_eligible && $this->payment) {
            $this->update(['refund_status' => 'pending']);
        }
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
