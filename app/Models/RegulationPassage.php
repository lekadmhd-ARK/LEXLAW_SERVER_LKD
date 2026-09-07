<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class RegulationPassage extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $table = 'regulation_passages';

    protected $fillable = [
        'tenant_id', 'regulation_id', 'passage_type', 'passage_number',
        'passage_title', 'content', 'parent_id', 'hierarchy_path',
    ];

    protected $casts = [
        'parent_id' => 'integer',
    ];

    public function regulation()
    {
        return $this->belongsTo(Regulation::class, 'regulation_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}