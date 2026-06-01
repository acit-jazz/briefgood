<?php

namespace App\Mail;

use App\Models\Brief;
use App\Models\PitchAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PitchAssignmentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PitchAssignment $assignment,
        public Brief $brief,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Pitch Assignment: {$this->brief->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pitch-assignment-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
