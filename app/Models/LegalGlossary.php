<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class LegalGlossary extends Model
{
    use Searchable;
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'term', 'singkatan', 'kategori_hukum', 'definisi_singkat',
        'penjelasan_lengkap', 'dasar_hukum_terkait', 'contoh_implementasi',
        'category', 'definition', 'cross_references',
    ];

    protected $casts = [
        'cross_references' => 'array',
        'dasar_hukum_terkait' => 'array',
    ];

    // Shared glossary: no tenant global scope — controller filters tenant_id = user tenant OR 'shared'
}
