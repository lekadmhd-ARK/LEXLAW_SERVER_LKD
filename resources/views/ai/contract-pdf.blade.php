<!DOCTYPE html>
<html lang="id" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Kontrak - LEXLAW v2</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 25mm 20mm 25mm;
        }
        body {
            font-family: "DejaVu Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 16pt;
            font-weight: bold;
            color: #4f46e5;
            display: block;
        }
        .title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 6px;
            display: block;
        }
        .meta {
            font-size: 8pt;
            color: #64748b;
            margin-top: 8px;
        }
        .badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: "DejaVu Sans Mono", monospace;
            font-weight: bold;
            font-size: 8.5pt;
        }
        .footer {
            margin-top: 36px;
            border-top: 1px solid #cbd5e1;
            padding-top: 14px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
        }
        .content {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 9.5pt;
            text-align: justify;
        }
        h1, h2, h3 { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">⚖ LAWLEX v2</div>
        <div class="title">Laporan Analisis Kontrak</div>
        <div class="meta">UID: <span class="badge">{{ $uid }}</span> | Tanggal: {{ $generatedAt }}</div>
    </div>
    <div class="content">{{ $content }}</div>
    <div class="footer">
        <hr style="margin: 10px 0; border: 0;">
        <p style="font-size: 7pt; color: #94a3b8; text-align: center;">
            Hasil analisis ini dihasilkan AI sebagai referensi awal dan bukan pendapat hukum final. 
            Konsultasikan dengan advokat untuk putusan mengikat. <br>
            © 2026 LEXLAW v2 • Semua hak cipta tertua.
        </p>
    </div>
</body>
</html>