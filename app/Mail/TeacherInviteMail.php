<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public User   $teacher;
    public string $inviteUrl;
    public string $expiresIn;

    public function __construct(User $teacher, string $token, string $expiresIn = '72 hours')
    {
        $this->teacher   = $teacher;
        $this->inviteUrl = route('invite.show', ['token' => $token]);
        $this->expiresIn = $expiresIn;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your DP-LMS Teacher Account — Activate Now',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher-invite',
            with: [
                'teacher'   => $this->teacher,
                'inviteUrl' => $this->inviteUrl,
                'expiresIn' => $this->expiresIn,
            ],
        );
    }
}