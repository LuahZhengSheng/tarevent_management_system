<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Support\PhoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller {

    /**
     * Show registration form
     */
    public function create(Event $event) {
        // Authorization: Must be student/user
        if (!auth()->user()->hasRole('user')) {
            abort(403, 'Only students can register for events.');
        }

        // Check if event allows registration
        if (!$event->is_registration_open) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('error', 'Registration is not currently open for this event.');
        }

        // Check if user already registered
        if (auth()->check()) {
            $existingRegistration = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->whereIn('status', ['confirmed', 'pending_payment'])
                    ->first();

            if ($existingRegistration) {
                return redirect()
                                ->route('events.show', $event)
                                ->with('info', 'You are already registered for this event.');
            }
        }

        // Check if event is full
        if ($event->is_full) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('warning', 'This event is currently full. You may join the waitlist.');
        }

        // Check for private events (club-only)
        if (!$event->is_public) {
            if (!auth()->check()) {
                return redirect()
                                ->route('login')
                                ->with('error', 'You must be logged in to register for this event.');
            }
            // TODO: Check if user is member of the club
        }

        // Load custom registration fields
        $event->load('customRegistrationFields');

        // Prefill user data if authenticated
        $userData = [
            'full_name' => auth()->user()->full_name ?? '',
            'email' => auth()->user()->email ?? '',
            'student_id' => auth()->user()->student_id ?? '',
            'phone' => auth()->user()->phone ? PhoneHelper::formatForDisplay(auth()->user()->phone) : '',
            'program' => auth()->user()->program ?? '',
        ];

        $programOptions = [
            // Computing / IT
            'BCS' => 'Bachelor of Computer Science',
            'BIT' => 'Bachelor of Information Technology',
            'BSE' => 'Bachelor of Software Engineering',
            'BDS' => 'Bachelor of Data Science',
            'BCY' => 'Bachelor of Cyber Security',
            'BIS' => 'Bachelor of Information Systems',
            // Engineering
            'BEEE' => 'Bachelor of Electrical and Electronic Engineering',
            'BCHE' => 'Bachelor of Chemical Engineering',
            'BCIV' => 'Bachelor of Civil Engineering',
            'BME' => 'Bachelor of Mechanical Engineering',
            // Business / Finance
            'BBA' => 'Bachelor of Business Administration',
            'BACC' => 'Bachelor of Accounting',
            'BFIN' => 'Bachelor of Finance',
            'BMM' => 'Bachelor of Marketing Management',
            'BIBM' => 'Bachelor of International Business Management',
            // Science
            'BSCM' => 'Bachelor of Science (Mathematics)',
            'BSCP' => 'Bachelor of Science (Physics)',
            'BSCC' => 'Bachelor of Science (Chemistry)',
            'BSCB' => 'Bachelor of Science (Biology)',
            // Arts / Social Science
            'BENG' => 'Bachelor of Arts (English Language)',
            'BCOMM' => 'Bachelor of Communication',
            'BPSY' => 'Bachelor of Psychology',
            // Others / Generic
            'DIP' => 'Diploma Programme',
            'FOUND' => 'Foundation Programme',
            'OTH' => 'Other (please specify)',
        ];

        return view('events.register', compact('event', 'userData', 'programOptions'));
    }

    /**
     * Store registration
     */
    public function store(Request $request, Event $event) {
        try {
            // 统一 trim 文本字段
            $input = $request->all();
            foreach (['full_name', 'email', 'phone', 'student_id', 'program',
        'emergency_contact_name', 'emergency_contact_phone',
        'dietary_requirements', 'special_requirements'] as $key) {
                if (isset($input[$key])) {
                    $input[$key] = trim((string) $input[$key]);
                }
            }

            // Build validation rules dynamically
            $rules = [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string',
                'student_id' => 'required|string|max:50',
                'program' => 'required|string|max:255',
                'terms_accepted' => 'required|accepted',
            ];

            // Add emergency contact if required by event
            if ($event->require_emergency_contact) {
                $rules['emergency_contact_name'] = 'required|string|max:255';
                $rules['emergency_contact_phone'] = 'required|string';
            }

            // Add dietary info if required
            if ($event->require_dietary_info) {
                $rules['dietary_requirements'] = 'required|string|max:500';
            }

            // Add special requirements if required
            if ($event->require_special_requirements) {
                $rules['special_requirements'] = 'required|string|max:500';
            }

            // Validate custom fields dynamically
            $customFields = $event->customRegistrationFields;
            foreach ($customFields as $field) {
                $fieldName = "custom_fields.{$field->name}";
                $rules[$fieldName] = $field->getValidationRulesArray();
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                                'success' => false,
                                'errors' => $validator->errors(),
                                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            // Additional phone validation using PhoneHelper
            $phoneError = PhoneHelper::getValidationError($request->phone);
            if ($phoneError) {
                return back()->withErrors(['phone' => $phoneError])->withInput();
            }

            if ($event->require_emergency_contact) {
                $emergencyPhoneError = PhoneHelper::getValidationError($request->emergency_contact_phone);
                if ($emergencyPhoneError) {
                    return back()->withErrors(['emergency_contact_phone' => $emergencyPhoneError])->withInput();
                }
            }

            // Rate limiting check
            $recentRegistrations = EventRegistration::where('user_id', auth()->id())
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->count();

            if ($recentRegistrations >= 3) {
                Log::warning('Registration rate limit exceeded', [
                    'user_id' => auth()->id(),
                    'event_id' => $event->id,
                    'count' => $recentRegistrations,
                ]);

                return back()->with('error', 'Too many registration attempts. Please try again later.');
            }

            // Security: Check if event allows registration
            if (!$event->is_registration_open) {
                return back()->with('error', 'Registration is not currently open.');
            }

            // Check if already registered
            if (auth()->check()) {
                $existing = EventRegistration::where('event_id', $event->id)
                        ->where('user_id', auth()->id())
                        ->whereIn('status', ['confirmed', 'pending_payment'])
                        ->first();

                if ($existing) {
                    return back()->with('error', 'You are already registered for this event.');
                }
            }

            DB::beginTransaction();

            // Determine status based on payment requirement and seat availability
            $status = 'confirmed';
            if ($event->is_paid) {
                $status = 'pending_payment';
            } elseif ($event->is_full) {
                $status = 'waitlisted';
            }

            // Build registration_data JSON
            $registrationData = [];

            // Add dietary and special requirements if provided
            if ($request->filled('dietary_requirements')) {
                $registrationData['dietary_requirements'] = strip_tags($request->dietary_requirements);
            }
            if ($request->filled('special_requirements')) {
                $registrationData['special_requirements'] = strip_tags($request->special_requirements);
            }

            // Add custom fields data
            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $key => $value) {
                    if (is_array($value)) {
                        $registrationData[$key] = array_map(function ($v) {
                            return trim(strip_tags($v));
                        }, $value);
                    } else {
                        $registrationData[$key] = trim(strip_tags($value));
                    }
                }
            }

            // Create registration with SQL injection protection (using Eloquent)
            $registrationInput = [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'status' => $status,
                'full_name' => strip_tags($request->full_name),
                'email' => filter_var($request->email, FILTER_SANITIZE_EMAIL),
                'phone' => PhoneHelper::formatForStorage($request->phone),
                'student_id' => strip_tags($request->student_id),
                'program' => strip_tags($request->program),
                'registration_data' => $registrationData,
            ];

            // Add emergency contact only if required
            if ($event->require_emergency_contact) {
                $registrationInput['emergency_contact_name'] = strip_tags($request->emergency_contact_name);
                $registrationInput['emergency_contact_phone'] = PhoneHelper::formatForStorage($request->emergency_contact_phone);
            }

            $registration = EventRegistration::create($registrationInput);

            DB::commit();

            Log::info('Event registration successful', [
                'registration_id' => $registration->id,
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'status' => $status,
            ]);

            // Redirect based on status
            if ($status === 'pending_payment') {
                return redirect()
                                ->route('registrations.payment', $registration)
                                ->with('success', 'Registration received! Please complete payment to confirm.');
            } elseif ($status === 'waitlisted') {
                return redirect()
                                ->route('events.show', $event)
                                ->with('info', 'Event is full. You have been added to the waitlist.');
            } else {
                return redirect()
                                ->route('events.my')
                                ->with('success', 'Registration confirmed! 🎉');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event registration failed', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                            ->withInput()
                            ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Show payment page
     */
    public function payment(EventRegistration $registration) {
        if ($registration->status !== 'pending_payment') {
            return redirect()
                            ->route('events.my')
                            ->with('info', 'This registration does not require payment.');
        }

        $event = $registration->event;

        return view('events.payment', compact('event', 'registration'));
    }

    /**
     * Process payment
     */
    public function pay(EventRegistration $registration, Request $request) {
        try {
            DB::beginTransaction();

            $event = $registration->event;

            $payment = Payment::create([
                        'event_id' => $event->id,
                        'event_registration_id' => $registration->id,
                        'amount' => $event->fee_amount,
                        'method' => 'dummy',
                        'transaction_id' => Str::uuid()->toString(),
                        'status' => 'success',
                        'paid_at' => now(),
            ]);

            $registration->update([
                'status' => 'confirmed',
                'payment_id' => $payment->id,
            ]);

            DB::commit();

            Log::info('Payment processed', [
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
            ]);

            return redirect()
                            ->route('events.my')
                            ->with('success', 'Payment successful! Your registration is confirmed. 🎉');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment processing failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Payment failed. Please try again.');
        }
    }

    /**
     * My Events page
     */
    public function myEvents() {
        $registrations = EventRegistration::with('event', 'payment')
                ->orderByDesc('created_at')
                ->paginate(10);

        return view('events.my-events', compact('registrations'));
    }

    /**
     * Cancel registration
     */
    public function destroy(EventRegistration $registration) {
        // Check if event allows cancellation
        if (!$registration->event->allow_cancellation) {
            return back()->with('error', 'This event does not allow registration cancellation.');
        }

        // Check if cancellation is allowed
        if (!$registration->can_be_cancelled) {
            return back()->with('error', 'This registration cannot be cancelled.');
        }

        try {
            $registration->cancel('Cancelled by user');

            Log::info('Registration cancelled by user', [
                'registration_id' => $registration->id,
            ]);

            return back()->with('success', 'Registration cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Registration cancellation failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to cancel registration.');
        }
    }

    /**
     * AJAX field validation
     */
    public function validateField(Request $request) {
        $field = $request->input('field');
        $value = $request->input('value');
        $eventId = $request->input('event_id');

        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|regex:/^[0-9\+\-\(\)\s]+$/|max:20',
            'student_id' => 'required|string|max:50',
            'program' => 'required|string|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|regex:/^[0-9\+\-\(\)\s]+$/|max:20',
        ];

        // Check if it's a custom field
        if (strpos($field, 'custom_fields.') === 0) {
            $fieldName = str_replace('custom_fields.', '', $field);

            if ($eventId) {
                $event = Event::find($eventId);
                if ($event) {
                    $customField = $event->customRegistrationFields()
                            ->where('name', $fieldName)
                            ->first();

                    if ($customField) {
                        $rules[$field] = $customField->getValidationRulesArray();
                    }
                }
            }
        }

        if (!isset($rules[$field])) {
            return response()->json(['valid' => true]);
        }

        $validator = Validator::make(
                        [$field => $value],
                        [$field => $rules[$field]]
        );

        if ($validator->fails()) {
            return response()->json([
                        'valid' => false,
                        'message' => $validator->errors()->first($field),
            ]);
        }

        return response()->json(['valid' => true]);
    }
}
