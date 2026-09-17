<?php

namespace App\Mail;

use App\Models\TeamWorkspace;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public TeamWorkspace $workspace,
        public string $roleLabel,
        public string $companyName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Bergabung di LEXLAW',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.team-invite',
            with: [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'workspaceName' => $this->workspace->name,
                'roleLabel' => $this->roleLabel,
                'companyName' => $this->companyName,
            ],
        );
    }
}