<?php

namespace App\Mail;

use App\Models\EventRegistration;
use App\Support\PdfHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class RegistrationConfirmedMail extends Mailable {

    use Queueable,
        SerializesModels;

    public $registration;

    /**
     * Create a new message instance.
     */
    public function __construct(EventRegistration $registration) {
        $this->registration = $registration->load('event', 'payment');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
                subject: 'Registration Confirmed - ' . $this->registration->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
                view: 'emails.registration-confirmed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array {
        try {
            // Generate and attach payment receipt PDF
            $pdf = PdfHelper::generateReceipt($this->registration->payment, false);
            $filename = 'Receipt_' . $this->registration->registration_number . '.pdf';

            return [
                        Attachment::fromData(fn() => $pdf->output(), $filename)
                        ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to attach receipt PDF', [
                'registration_id' => $this->registration->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
