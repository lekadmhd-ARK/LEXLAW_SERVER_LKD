<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Laravel\Scout\Searchable;

class LegalGlossary extends Model
{
    use Searchable;
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'term', 'definition', 'category', 'cross_references',
    ];

    protected $casts = [
        'cross_references' => 'array',
    ];
}