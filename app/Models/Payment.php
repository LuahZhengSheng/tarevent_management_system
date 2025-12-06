<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'event_registration_id',
        'amount',
        'method',           // paypal / stripe
        'transaction_id',  // PayPal/Stripe 的 payment id
        'status',          // success / failed / pending
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registration()
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
