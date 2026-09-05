<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $regulation->hierarchy_label }} No. {{ $regulation->number }} Tahun {{ $regulation->year }}</title>
    <style>
        @page { margin: 2.5cm 2cm 2cm 2cm; size: A4; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 24pt; }
        .header h1 { font-size: 14pt; font-weight: bold; margin: 0 0 6pt 0; text-transform: uppercase; }
        .header h2 { font-size: 12pt; font-weight: bold; margin: 0 0 12pt 0; }
        .header .nomor { font-size: 11pt; margin: 0 0 6pt 0; }
        .tentang { text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 24pt; }
        .metadata { margin-bottom: 24pt; font-size: 10pt; }
        .metadata p { margin: 2pt 0; }
        .content { text-align: justify; }
        .pasal { margin-bottom: 12pt; }
        .pasal-title { font-weight: bold; margin-bottom: 6pt; }
        .pasal-text { margin-left: 0; }
        .footer { margin-top: 36pt; text-align: center; font-size: 10pt; }
        .badge { display: inline-block; padding: 2pt 8pt; border-radius: 4pt; font-size: 9pt; margin-right: 6pt; }
        .badge-uu { background: #3b82f6; color: #fff; }
        .badge-pp { background: #8b5cf6; color: #fff; }
        .badge-perpres { background: #ec4899; color: #fff; }
        .badge-permen { background: #f59e0b; color: #fff; }
        .badge-perda { background: #22c55e; color: #fff; }
        .source-url { font-size: 9pt; color: #666; margin-top: 24pt; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $regulation->hierarchy_label }}</h1>
        <p class="nomor">Nomor {{ $regulation->number ?? '-' }} Tahun {{ $regulation->year ?? '-' }}</p>
        <h2>{{ $regulation->title }}</h2>
    </div>

    @if($regulation->short_description)
    <div class="tentang">
        <p>{{ $regulation->short_description }}</p>
    </div>
    @endif

    <div class="metadata">
        <p><strong>Tanggal Penetapan:</strong> {{ $regulation->penetapan_date ? $regulation->penetapan_date->format('d F Y') : '-' }}</p>
        <p><strong>Tanggal Pengundangan:</strong> {{ $regulation->pengundangan_date ? $regulation->pengundangan_date->format('d F Y') : '-' }}</p>
        <p><strong>Status:</strong> {{ $regulation->is_active ? 'Berlaku' : 'Tidak Berlaku' }}</p>
        <p><strong>Kategori Sektor:</strong> {{ $regulation->sector_label ?? '-' }}</p>
    </div>

    @if($regulation->description)
    <div class="content">
        <h3>Deskripsi</h3>
        <p>{{ $regulation->description }}</p>
    </div>
    @endif

    @if($regulation->content_text)
    <div class="content">
        <h3>Isi Peraturan</h3>
        <div style="white-space: pre-wrap; font-family: 'Times New Roman', Times, serif;">{{ $regulation->content_text }}</div>
    </div>
    @endif

    @if($regulation->source_url)
    <div class="source-url">
        <strong>Sumber Resmi:</strong> {{ $regulation->source_url }}
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh sistem LEXLAW v2.</p>
        <p>Sumber data: Dokumen resmi milik pemerintah Indonesia (public domain).</p>
        <p>Tanggal cetak: {{ now()->format('d F Y H:i') }}</p>
    </div>
</body>
</html>