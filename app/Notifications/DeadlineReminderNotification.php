<?php

namespace App\Notifications;

use App\Models\TeamWorkspace;
use App\Models\WorkspaceTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WorkspaceTask $task,
        public TeamWorkspace $workspace,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Deadline Tugas: {$this->task->title}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Tugas '{$this->task->title}' di workspace '{$this->workspace->name}' akan jatuh tempo besok ({$this->task->due_date->format('d M Y')}).")
            ->action('Lihat Tugas', url("/team-workspaces/{$this->workspace->id}?tab=tasks"))
            ->line('Terima kasih menggunakan LEXLAW!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'deadline_reminder',
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'workspace_id' => $this->workspace->id,
            'workspace_name' => $this->workspace->name,
            'due_date'     => $this->task->due_date?->format('Y-m-d'),
            'message'      => "Deadline besok: '{$this->task->title}'",
            'url'          => "/team-workspaces/{$this->workspace->id}?tab=tasks",
        ];
    }
}
