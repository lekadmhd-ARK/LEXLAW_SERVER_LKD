<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceDocument extends Model
{
    protected $fillable = [
        'workspace_id', 'user_id', 'title', 'description',
        'file_path', 'file_name', 'file_mime', 'file_size', 'category',
    ];

    public const CATEGORIES = [
        'perjanjian'    => 'Perjanjian',
        'gugatan'       => 'Gugatan',
        'putusan'       => 'Putusan',
        'surat_kuasa'   => 'Surat Kuasa',
        'berita_acara'  => 'Berita Acara',
        'korenspondensi'=> 'Korespondensi',
        'lainnya'       => 'Lainnya',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(TeamWorkspace::class, 'workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryNameAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Lainnya';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        return match (true) {
            str_contains($this->file_mime ?? '', 'pdf') => '📕',
            str_contains($this->file_mime ?? '', 'word'), str_contains($this->file_name ?? '', '.doc') => '📘',
            str_contains($this->file_mime ?? '', 'sheet'), str_contains($this->file_name ?? '', '.xls') => '📗',
            str_contains($this->file_mime ?? '', 'image') => '🖼️',
            str_contains($this->file_mime ?? '', 'zip'), str_contains($this->file_mime ?? '', 'rar') => '🗜️',
            default => '📄',
        };
    }
}
