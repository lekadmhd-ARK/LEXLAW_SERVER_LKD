<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationContent extends Model
{
    use Searchable;
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'regulation_id', 'article_number', 'article_title', 'content', 'sub_articles',
    ];

    protected $casts = [
        'sub_articles' => 'array',
        'regulation_id' => 'integer',
    ];

    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }
}