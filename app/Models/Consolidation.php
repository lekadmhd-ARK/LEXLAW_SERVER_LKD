<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consolidation extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'title', 'regulation_ids', 'consolidated_text', 'version', 'created_by',
        'source_regulations', 'status', 'ai_metadata',
    ];

    protected $casts = [
        'regulation_ids' => 'array',
        'source_regulations' => 'array',
        'ai_metadata' => 'array',
        'version' => 'integer',
        'created_by' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function chunks()
    {
        return $this->hasMany(\App\Models\ConsolidationChunk::class);
    }
}