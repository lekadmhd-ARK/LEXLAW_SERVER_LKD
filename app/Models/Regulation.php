<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regulation extends Model
{
    use Searchable;
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'company_id', 'title', 'number', 'year', 'hierarchy_level',
        'category_sector', 'category', 'status', 'effective_date', 'description',
        'source_url', 'pdf_url', 'content_text', 'created_by', 'is_active', 'derogat_legi_id',
        'short_description', 'penetapan_date', 'pengundangan_date',
    ];

    protected $casts = [
        'year' => 'integer',
        'effective_date' => 'date',
        'penetapan_date' => 'date',
        'pengundangan_date' => 'date',
        'company_id' => 'integer',
        'created_by' => 'integer',
        'is_active' => 'boolean',
        'derogat_legi_id' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(RegulationContent::class);
    }

    // Relasi: peraturan yang mencabut/ini diubah (lex specialis)
    public function derogatLegi(): BelongsTo
    {
        return $this->belongsTo(Regulation::class, 'derogat_legi_id');
    }

    // Relasi balik: peraturan yang dicabut oleh ini
    public function revokedBy(): HasMany
    {
        return $this->hasMany(Regulation::class, 'derogat_legi_id');
    }

    public static function statusEnum(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'archived' => 'Archived',
            'revoked' => 'Revoked',
        ];
    }

    public function getHierarchyLabelAttribute(): string
    {
        return match($this->hierarchy_level) {
            '1' => 'Undang-Undang (UU)',
            '2' => 'Peraturan Pemerintah (PP)',
            '3' => 'Peraturan Presiden (Perpres)',
            '4' => 'Peraturan Menteri (PerMen)',
            '5' => 'Peraturan Daerah (Perda)',
            default => '—',
        };
    }

    public function getSectorLabelAttribute(): string
    {
        return match($this->category_sector) {
            'ketenagakerjaan' => 'Ketenagakerjaan',
            'perpajakan' => 'Perpajakan',
            'perusahaan' => 'Perusahaan',
            'agraria' => 'Agraria',
            'teknologi' => 'Teknologi Informasi',
            'lainnya' => 'Lainnya',
            default => '—',
        };
    }
}