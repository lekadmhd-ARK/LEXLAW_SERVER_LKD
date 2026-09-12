<?php

namespace App\Models;

use App\Models\Concerns\AuthorizesWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToTenant;

class TeamWorkspace extends Model
{
    use HasFactory;
    use BelongsToTenant;
    use AuthorizesWorkspace;

    public const TYPES = [
        'case'          => 'Perkara',
        'general'       => 'Umum',
        'arbitration'   => 'Arbitrase',
        'litigation'    => 'Litigasi',
        'corporate'     => 'Korporat',
        'consultation'  => 'Konsultasi',
    ];

    public const MEMBER_ROLES = [
        'owner'  => 'Owner',
        'admin'  => 'Admin',
        'member' => 'Member',
        'viewer' => 'Viewer',
    ];

    protected $fillable = [
        'tenant_id', 'company_id', 'name', 'type', 'description', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'company_id'  => 'integer',
        'created_by'  => 'integer',
    ];

    protected $appends = ['member_count'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_workspace_members', 'workspace_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WorkspaceDocument::class, 'workspace_id')->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(WorkspaceNote::class, 'workspace_id')->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkspaceTask::class, 'workspace_id')->latest();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(WorkspaceTimeEntry::class, 'workspace_id')->latest();
    }

    public function getMemberCountAttribute(): int
    {
        return $this->relationLoaded('members') ? $this->members->count() : $this->members()->count();
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? '—';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getTotalMinutesAttribute(): int
    {
        return $this->timeEntries()->sum('minutes');
    }

    public function getActiveTasksCountAttribute(): int
    {
        return $this->tasks()->active()->count();
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->completed()->count();
    }
}
