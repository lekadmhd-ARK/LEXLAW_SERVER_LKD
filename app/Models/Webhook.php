<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    protected $fillable = [
        'company_id', 'url', 'secret', 'events',
        'is_active', 'last_triggered_at', 'failure_count', 'last_error',
    ];

    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
