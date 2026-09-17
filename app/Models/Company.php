<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'plan_id',
        'address', 'phone', 'logo_url',
        'subscription_status', 'trial_ends_at', 'subscribed_until', 'settings',
        'quota_qna', 'quota_draft', 'quota_contract_review', 'quota_validity', 'quota_reset_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_until' => 'datetime',
        'plan_id' => 'integer',
        'quota_reset_at' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class);
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(TeamWorkspace::class);
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trialing'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }
}