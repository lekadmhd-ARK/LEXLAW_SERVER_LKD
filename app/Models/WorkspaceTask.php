<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTask extends Model
{
    protected $fillable = [
        'workspace_id', 'user_id', 'assigned_to', 'title',
        'description', 'status', 'priority', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'todo'        => 'To Do',
        'in_progress' => 'In Progress',
        'done'        => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

    public const PRIORITIES = [
        'low'    => 'Rendah',
        'normal' => 'Normal',
        'high'   => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(TeamWorkspace::class, 'workspace_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function timeEntries()
    {
        return $this->hasMany(WorkspaceTimeEntry::class, 'task_id');
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPriorityNameAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => '#ef4444',
            'high'   => '#f59e0b',
            'normal' => '#5e6ad2',
            'low'    => '#94a3b8',
            default  => '#94a3b8',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'done'        => '#22c55e',
            'in_progress' => '#5e6ad2',
            'cancelled'   => '#94a3b8',
            default       => '#f59e0b',
        };
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['todo', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }
}
