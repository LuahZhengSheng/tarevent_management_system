<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    // 报名（免费 / 付费）
    public function store(Event $event)
    {
        $status = $event->is_paid ? 'pending_payment' : 'confirmed';

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id'  => auth()->id(),
            'status'   => $status,
        ]);

        if ($event->is_paid) {
            return redirect()->route('registrations.payment', $registration);
        }

        return redirect()->route('events.my');
    }

    // Payment 页面
    public function payment(EventRegistration $registration)
    {
        $event = $registration->event;
        $user  = $registration->user;

        return view('events.payment', compact('event', 'registration', 'user'));
    }

    // 提交支付（这里先做假支付流程）
    public function pay(EventRegistration $registration, Request $request)
    {
        $event = $registration->event;
        $user  = $registration->user;

        // 创建支付记录
        $payment = Payment::create([
            'event_id'              => $event->id,
            'user_id'               => $user->id,
            'event_registration_id' => $registration->id,
            'amount'                => $event->fee_amount,
            'method'                => 'dummy',
            'transaction_id'        => Str::uuid()->toString(),
            'status'                => 'success',
            'paid_at'               => now(),
        ]);

        // 更新报名状态
        $registration->update([
            'status'     => 'confirmed',
            'payment_id' => $payment->id,
        ]);

        return redirect()->route('events.my');
    }

    // 我的活动（未来 + 历史）
    public function myEvents()
    {
        $userId = auth()->id();

        $registrations = EventRegistration::with('event')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('events.my-events', compact('registrations'));
    }
}
