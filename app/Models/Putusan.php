<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Putusan extends Model
{
    protected $fillable = [
        'nomor_putusan',
        'panitera',
        'jenis_pengadilan',
        'nama_pengadilan',
        'tingkat_pengadilan',
        'golongan_perkara',
        'klasifikasi_perkara',
        'tanggal_putusan',
        'tanggal_register',
        'para_pihak',
        'ringkasan_putusan',
        'isi_putusan',
        'pasal_dikutip',
        'putusan_terkait',
        'status_putusan',
        'sumber_url',
        'hash_content',
        'is_published',
    ];

    protected $casts = [
        'pasal_dikutip' => 'array',
        'putusan_terkait' => 'array',
        'tanggal_putusan' => 'date',
        'tanggal_register' => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_pengadilan', $jenis);
    }

    public function scopeByGolongan($query, $golongan)
    {
        return $query->where('golongan_perkara', $golongan);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nomor_putusan', 'ILIKE', "%{$term}%")
              ->orWhere('ringkasan_putusan', 'ILIKE', "%{$term}%")
              ->orWhere('isi_putusan', 'ILIKE', "%{$term}%")
              ->orWhere('para_pihak', 'ILIKE', "%{$term}%")
              ->orWhere('nama_pengadilan', 'ILIKE', "%{$term}%");
        });
    }
}