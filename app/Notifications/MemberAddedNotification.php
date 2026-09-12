<?php

namespace App\Notifications;

use App\Models\TeamWorkspace;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TeamWorkspace $workspace,
        public User $addedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Anda ditambahkan ke Workspace: {$this->workspace->name}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Anda telah ditambahkan ke workspace '{$this->workspace->name}' oleh {$this->addedBy->name}.")
            ->action('Lihat Workspace', url("/team-workspaces/{$this->workspace->id}?tab=members"))
            ->line('Terima kasih menggunakan LEXLAW!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'member_added',
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'added_by'     => $this->addedBy->name,
            'message'      => "Anda ditambahkan ke workspace '{$this->workspace->name}'",
            'url'          => "/team-workspaces/{$this->workspace->id}?tab=members",
        ];
    }
}
