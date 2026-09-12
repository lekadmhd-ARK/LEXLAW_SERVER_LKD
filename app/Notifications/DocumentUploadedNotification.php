<?php

namespace App\Notifications;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WorkspaceDocument $document,
        public TeamWorkspace $workspace,
        public string $uploaderName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Dokumen Baru: {$this->document->title}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Dokumen '{$this->document->title}' telah diunggah ke '{$this->workspace->name}' oleh {$this->uploaderName}.")
            ->action('Lihat Dokumen', url("/team-workspaces/{$this->workspace->id}?tab=documents"))
            ->line('Terima kasih menggunakan LEXLAW!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'document_uploaded',
            'document_id'   => $this->document->id,
            'document_title'=> $this->document->title,
            'workspace_id'  => $this->workspace->id,
            'workspace_name'=> $this->workspace->name,
            'uploader'      => $this->uploaderName,
            'message'       => "Dokumen '{$this->document->title}' diunggah ke '{$this->workspace->name}'",
            'url'           => "/team-workspaces/{$this->workspace->id}?tab=documents",
        ];
    }
}
