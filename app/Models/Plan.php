<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'price_monthly', 'price_yearly',
        'max_users', 'max_regulations', 'max_ai_queries',
        'features', 'ai_enabled', 'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_users' => 'integer',
        'max_regulations' => 'integer',
        'max_ai_queries' => 'integer',
        'features' => 'array',
        'ai_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}