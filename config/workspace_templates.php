<?php

return [
    'pidana' => [
        'name' => 'Perkara Pidana',
        'type' => 'case',
        'description' => 'Template untuk penanganan perkara pidana',
        'tasks' => [
            ['title' => 'Analisis Berkas & Fakta', 'priority' => 'high'],
            ['title' => 'Konsultasi Klien', 'priority' => 'high'],
            ['title' => 'Draft Surat Gugatan/Pledoi', 'priority' => 'normal'],
            ['title' => 'Persidangan Putama', 'priority' => 'normal'],
            ['title' => 'Pembacaan Putusan', 'priority' => 'low'],
            ['title' => 'Upaya Hukum (PK/Banding/Kasasi)', 'priority' => 'low'],
        ],
    ],
    'perdata' => [
        'name' => 'Perkara Perdata',
        'type' => 'case',
        'description' => 'Template untuk penanganan perkara perdata',
        'tasks' => [
            ['title' => 'Analisis Dokumen Kontrak', 'priority' => 'high'],
            ['title' => 'Konsultasi Klien', 'priority' => 'high'],
            ['title' => 'Draft Gugatan/ Eksepsi', 'priority' => 'normal'],
            ['title' => 'Mediasi', 'priority' => 'normal'],
            ['title' => 'Persidangan', 'priority' => 'normal'],
            ['title' => 'Putusan & Eksekusi', 'priority' => 'low'],
        ],
    ],
    'arbitrase' => [
        'name' => 'Arbitrase',
        'type' => 'arbitration',
        'description' => 'Template untuk sengketa arbitrase',
        'tasks' => [
            ['title' => 'Review Perjanjian Arbitrase', 'priority' => 'high'],
            ['title' => 'Pilih Arbiter', 'priority' => 'high'],
            ['title' => 'Draft Pernyataan Klaim', 'priority' => 'normal'],
            ['title' => 'Proses Arbitrase', 'priority' => 'normal'],
            ['title' => 'Putusan & Enforcement', 'priority' => 'low'],
        ],
    ],
    'korporat' => [
        'name' => 'Korporat',
        'type' => 'corporate',
        'description' => 'Template untuk pekerjaan korporat/M&A',
        'tasks' => [
            ['title' => 'Due Diligence', 'priority' => 'high'],
            ['title' => 'Draft Perjanjian', 'priority' => 'high'],
            ['title' => 'Review Kontrak Existing', 'priority' => 'normal'],
            ['title' => 'Konsultasi dengan Klien', 'priority' => 'normal'],
            ['title' => 'Final Review & Signing', 'priority' => 'normal'],
            ['title' => 'Closing & Filing', 'priority' => 'low'],
        ],
    ],
    'konsultasi' => [
        'name' => 'Konsultasi Hukum',
        'type' => 'consultation',
        'description' => 'Template untuk konsultasi hukum umum',
        'tasks' => [
            ['title' => 'Identifikasi Masalah Hukum', 'priority' => 'high'],
            ['title' => 'Riset Regulasi & Yurisprudensi', 'priority' => 'normal'],
            ['title' => 'Draft Legal Opinion', 'priority' => 'normal'],
            ['title' => 'Presentasi ke Klien', 'priority' => 'low'],
        ],
    ],
];
