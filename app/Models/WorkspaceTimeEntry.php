<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceTimeEntry extends Model
{
    protected $fillable = [
        'workspace_id', 'user_id', 'task_id', 'description',
        'minutes', 'billable', 'entry_date',
    ];

    protected $casts = [
        'billable'   => 'boolean',
        'entry_date' => 'date',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(TeamWorkspace::class, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTask::class, 'task_id');
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours   = intdiv($this->minutes, 60);
        $minutes = $this->minutes % 60;
        if ($hours > 0) return "{$hours}j {$minutes}m";
        return "{$minutes}m";
    }

    public function getDecimalHoursAttribute(): float
    {
        return round($this->minutes / 60, 2);
    }
}
