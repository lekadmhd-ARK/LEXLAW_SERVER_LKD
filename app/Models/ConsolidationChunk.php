<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class ConsolidationChunk extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'consolidation_id', 'chunk_index', 'source_regulation_id',
        'source_passage', 'ai_processed_text', 'change_flags',
    ];

    protected $casts = [
        'change_flags' => 'array',
    ];

    public function consolidation()
    {
        return $this->belongsTo(Consolidation::class);
    }
}