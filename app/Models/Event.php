<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\EventObserver;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    // Automatically register observer
    protected static function boot()
    {
        parent::boot();
        static::observe(EventObserver::class);
    }

    protected $fillable = [
        'title',
        'description',
        'organizer_id',
        'organizer_type',
        'start_time',
        'end_time',
        'registration_start_time',
        'registration_end_time',
        'venue',
        'category',
        'is_public',
        'is_paid',
        'fee_amount',
        'refund_available',
        'max_participants',
        'poster_path',
        'status',                    // draft / published / cancelled / completed
        'created_by',                // User ID who created
        'updated_by',                // User ID who last updated
        'cancelled_reason',          // If status = cancelled
        'tags',                      // JSON array of tags
        'contact_email',             // Contact for inquiries
        'contact_phone',             // Contact phone
        'requirements',              // Special requirements (JSON)
        'location_map_url',          // Google Maps link
        'registration_fields',       // Custom registration fields (JSON)
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_start_time' => 'datetime',
        'registration_end_time' => 'datetime',
        'is_public' => 'boolean',
        'is_paid' => 'boolean',
        'refund_available' => 'boolean',
        'tags' => 'array',
        'requirements' => 'array',
        'registration_fields' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'is_public' => true,
        'is_paid' => false,
        'refund_available' => false,
    ];

    // Relationships
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function organizer()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())
                     ->where('status', 'published');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getIsRegistrationOpenAttribute()
    {
        $now = now();
        return $this->status === 'published' &&
               $this->registration_start_time <= $now &&
               $this->registration_end_time >= $now &&
               ($this->max_participants === null || 
                $this->registrations()->where('status', 'confirmed')->count() < $this->max_participants);
    }

    public function getRemainingSeatsAttribute()
    {
        if ($this->max_participants === null) {
            return null;
        }
        return $this->max_participants - $this->registrations()->where('status', 'confirmed')->count();
    }

    public function getIsFullAttribute()
    {
        return $this->remaining_seats === 0;
    }

    public function getFormattedFeeAttribute()
    {
        return $this->is_paid ? 'RM ' . number_format($this->fee_amount, 2) : 'Free';
    }

    // Methods
    public function canBeEditedBy(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($this->organizer_type === 'club' && $user->isClubAdmin($this->organizer_id)) {
            return true;
        }

        return false;
    }

    public function publish()
    {
        $this->update(['status' => 'published']);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_reason' => $reason,
        ]);
    }
}