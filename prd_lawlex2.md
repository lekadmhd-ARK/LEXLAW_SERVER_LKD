# Product Requirements Document (PRD): Lexlaw — Platform Intelijensi Hukum & Regulasi (Reg-Tech)

## 1. Overview & Core Vision
Lexlaw adalah platform enterprise reg-tech berbasis *Laravel 13 Monolith* yang dirancang khusus untuk manajemen regulasi, analisis validitas hukum, peninjauan kontrak cerdas (*smart contract review*), dan asisten AI legal. Platform ini membantu tim legal, compliance, dan bisnis dalam mencari, menganalisis, serta memahami perundang-undangan Indonesia secara efisien, aman, dan terisolasi dalam lingkungan shared cPanel (`LAWLEX_v2`).

### Target Users
1. **Tim Legal Perusahaan** — memantau perubahan regulasi, cek validitas dokumen, dan *legal drafting*.
2. **Compliance Officer** — memastikan kepatuhan regulasi terbaru dan mitigasi risiko klausul kontrak.
3. **Konsultan Hukum** — riset hukum cepat dengan AI assistant dan pencocokan yurisprudensi.
4. **Akademisi & Mahasiswa Hukum** — belajar dan riset regulasi perundang-undangan.

### Goals & Success Metrics
- Waktu rata-rata pencarian regulasi < 30 detik.
- Akurasi deteksi sitasi UU > 90%.
- User retention 30 hari > 70%.
- NPS score > 50.

---

## 2. Core Features & Functional Requirements

### F1. Manajemen Regulasi
**Deskripsi:** Database regulasi perundang-undangan Indonesia dengan metadata lengkap. Memuat seluruh undang-undang dan peraturan yang berlaku di semua lembaga hukum pemerintah pusat dan daerah yang terjamin validitasnya.
- Admin bisa menambah regulasi secara manual atau menggunakan koneksi AI yang terhubung ke `pasal.id` via token `pasal_mcp_82eef72b4f24`.
- **Fields:** Title, Doc Number, Year, Type (UU, PP, Perpres, Permen, dll), Status (berlaku, dicabut, diubah), PDF Path.
- **Acceptance Criteria:**
    - [ ] CRUD regulasi via Controller
    - [ ] Search fulltext (MySQL FULLTEXT + Scout)
    - [ ] Filter by status, type, year
    - [ ] Detail view dengan konten per pasal

### F2. Konten Pasal & Ekstraksi Granular (Regulation Contents)
**Deskripsi:** Pengelolaan teks per pasal dari setiap regulasi untuk pencarian granular. Mendukung proses *split* otomatis via Regex sekaligus fasilitas *manual entry/editing* bagi admin untuk merapikan hasil *parsing* dokumen.
- **Fields:** Regulation ID (FK), Article Number, Text Content.
- **Acceptance Criteria:**
    - [ ] CRUD konten pasal dengan form tambah/edit manual.
    - [ ] Mesin *regex* untuk *split* otomatis teks regulasi berdasarkan pola "Pasal X" atau "BAB X".
    - [ ] *Fulltext search* pada kolom `text_content` menggunakan MySQL FULLTEXT.
    - [ ] Sinkronisasi dan *indexing* otomatis ke Laravel Scout.

### F3. Glosarium Hukum Bilingual
**Deskripsi:** Kamus istilah hukum Indonesia-English dengan konteks penggunaan, dilengkapi validasi otomatis menggunakan AI.
- **Fields:** ID Term, EN Term, Definition & Context.
- **Acceptance Criteria:**
    - [ ] CRUD glosarium dengan validasi AI.
    - [ ] Search fulltext.
    - [ ] Tampilan bilingual side-by-side.

### F4. Konsolidasi Regulasi
**Deskripsi:** Relasi antar regulasi (parent-child) untuk *tracking* perubahan dan riwayat amandemen.
- **Fields:** Parent ID, Child ID, Note.
- **Acceptance Criteria:**
    - [ ] CRUD konsolidasi relasi regulasi.
    - [ ] Tampilan *tree/relationship* di detail regulasi.

### F5. Team Workspace
**Deskripsi:** Folder kolaboratif multi-tenant untuk tim menyimpan catatan dan dokumen bersama.
- **Fields:** Company ID, Folder Name, Shared Notes.
- **Acceptance Criteria:**
    - [ ] CRUD workspace terisolasi per perusahaan.
    - [ ] List view dengan card UI.

### F6. AI Lex Q&A (RAG)
**Deskripsi:** Asisten AI yang menjawab pertanyaan hukum berdasarkan regulasi di database via 9Router (`localhost:8080`).
- **Acceptance Criteria:**
    - [ ] Endpoint `/ai/lex-qna` (Hybrid RAG: FULLTEXT + LLM).
    - [ ] Context sources ditampilkan ke user beserta sitasi.
    - [ ] History chat tersimpan di database.

### F7. AI Legal Drafting
**Deskripsi:** Generator draf dokumen hukum (NDA, perjanjian, MoU, dll) dengan ekspor ke Word (`.docx`).
- **Acceptance Criteria:**
    - [ ] Endpoint `POST /ai/draft` menerima `document_type` dan `instructions`.
    - [ ] Ekspor otomatis ke format file Word (`.docx`) atau Markdown siap pakai.

### F8. Validity Checker (Regex + DB)
**Deskripsi:** Deteksi sitasi UU dalam dokumen teks dan verifikasi status keaktifannya secara otomatis menggunakan Regex & database lookup.
- **Acceptance Criteria:**
    - [ ] Deteksi minimal 3 variasi penulisan sitasi UU Indonesia.
    - [ ] Status visual dengan *badge* warna: Hijau (Aktif), Merah (Dicabut), Kuning (Diubah), Abu-abu (Tidak Ditemukan).

### F9. Legal Compliance Citation Checker
**Deskripsi:** Analisis kontrak untuk mendeteksi sitasi undang-undang yang sudah dicabut guna mitigasi risiko hukum.
- **Acceptance Criteria:**
    - [ ] Endpoint `POST /ai/analyze` untuk memproses teks kontrak.
    - [ ] Flagging otomatis dengan status *Danger, Warning, Success, Neutral*.
    - [ ] UI merender teks dokumen dengan efek *highlight* berwarna.

### F10. Smart Contract Review & Risk Scoring
**Deskripsi:** Analisis isi kontrak menyeluruh menggunakan AI untuk mendeteksi klausul berisiko (*red flags*), ketidakseimbangan hak, dan kerugian komersial.
- **Acceptance Criteria:**
    - [ ] Endpoint `POST /ai/contract-review` menerima file/teks kontrak.
    - [ ] **AI Prompt Instruction:** *"Bertindaklah sebagai Senior Corporate Legal & Risk Assessor. Analisis teks kontrak secara mendalam. Identifikasi klausul merugikan, tidak seimbang, atau cacat hukum. Berikan: kutipan asli, kategori risiko, tingkat keparahan, alasan bahaya, dan rekomendasi alternatif dalam format JSON terstruktur."*
    - [ ] UI menampilkan ringkasan skor risiko (*Risk Meter*) dan daftar temuan klausul.

### F11. Automated Case Law & Court Precedent Matching
**Deskripsi:** Pencarian dan pencocokan yurisprudensi/putusan pengadilan berdasarkan fakta hukum atau isu sengketa.
- **Acceptance Criteria:**
    - [ ] Tabel database `court_decisions` (metadata putusan, nomor perkara, fakta hukum, amar putusan).
    - [ ] Endpoint `POST /ai/precedent-matching` dengan *Semantic Search / Hybrid RAG*.
    - [ ] **AI Prompt Instruction:** *"Analisis narasi kasus hukum pengguna. Bandingkan dengan database putusan pengadilan. Cari putusan dengan kemiripan prinsip hukum (ratione decidendi) tertinggi dan jelaskan implikasinya."*

### F12. Automated Legal Timeline & Statute of Limitations Tracker
**Deskripsi:** Ekstraksi otomatis tanggal penting, batas waktu, dan masa kedaluwarsa dari kontrak untuk disinkronkan ke kalender tim.
- **Acceptance Criteria:**
    - [ ] Tabel database `legal_deadlines` berelasi dengan `companies`.
    - [ ] **AI Prompt Instruction:** *"Pindai dokumen kontrak dan ekstrak seluruh kewajiban berbatas waktu/deadline. Keluarkan array JSON memuat: nama kewajiban, tanggal jatuh tempo estimasi, pihak bertanggung jawab, dan tingkat urgensi."*
    - [ ] Widget kalender/upcoming deadlines di dashboard.

### F13. Client Portal & Secure Collaboration
**Deskripsi:** Portal khusus berkeamanan tinggi bagi klien eksternal untuk memantau progres perkara (*case tracking*), meninjau dokumen, dan memberikan persetujuan (*approval*).
- **Acceptance Criteria:**
    - [ ] Route terisolasi khusus klien (*token-based guest link* / *middleware*).
    - [ ] Antarmuka bersih (*read-only* dokumen, interaktif untuk komentar & persetujuan).

### F14. PDF Upload & Watermark
**Deskripsi:** Upload PDF regulasi dengan *forensic watermark* otomatis via mPDF dan penyimpanan ke Cloudflare R2.
- **Acceptance Criteria:**
    - [ ] Route `POST /pdf/upload` (mPDF dynamic watermark: `user_id`, `timestamp`, `reg_id`).
    - [ ] Storage driver ke Cloudflare R2 (S3-compatible).
    - [ ] Presigned URL (15 menit) untuk pengunduhan aman.

### F15. PDF Text Parser
**Deskripsi:** Parse teks regulasi dan *split* otomatis per pasal ke tabel `regulation_contents`.
- **Acceptance Criteria:**
    - [ ] Route `POST /pdf/parse-text`.
    - [ ] Regex engine untuk split Pasal/BAB & auto-index ke Scout.

### F16. Multi-Tenant RBAC
**Deskripsi:** Sistem autentikasi dan otorisasi dengan role-based access control per perusahaan.
- **Roles:** Admin (Full), Senior (Upload/Edit/AI), Staff (Read-only).
- **Acceptance Criteria:**
    - [ ] Laravel Session-based Auth.
    - [ ] Role middleware & global scope data per `company_id`.

### F17. Dark/Light Mode + ID/EN Toggle
**Deskripsi:** Pengaturan tema tampilan dan bahasa antarmuka.
- **Acceptance Criteria:**
    - [ ] Toggle theme di sidebar layout.
    - [ ] Toggle language (placeholder i18n) & Tailwind dark mode support.

### F18. SaaS Subscription & Billing Management & Super Admin Pricing
**Deskripsi:** Pengelolaan paket berlangganan (*Free, Pro, Enterprise*), *feature gating*, integrasi *payment gateway* lokal, serta panel manajemen harga dinamis oleh Super Admin.
- **Acceptance Criteria:**
    - [ ] Tabel `subscriptions` & `plans` berelasi ke `companies`.
    - [ ] *Feature Gating Middleware* berdasarkan paket aktif.
    - [ ] Integrasi *payment gateway* (Midtrans/Xendit) untuk pembayaran otomatis.
    - [ ] Halaman Super Admin Pricing CRUD di route `/super-admin/plans` untuk ubah harga & kuota tanpa *hardcode*.

### F19. Tenant Onboarding & Company Registration
**Deskripsi:** Pendaftaran mandiri (*Self-service registration*) untuk perusahaan baru.
- **Acceptance Criteria:**
    - [ ] Form registrasi publik (Nama Perusahaan, Domain/Slug, Email Admin, Password).
    - [ ] Otomatis buat entri `companies` dan set *role* awal sebagai `Owner`.

### F20. Audit Trail & Security Logging
**Deskripsi:** Pencatatan aktivitas otomatis untuk melacak tindakan sensitif (unduhan dokumen ber-watermark, perubahan langganan, persetujuan kontrak).
- **Acceptance Criteria:**
    - [ ] Pencatatan otomatis ke tabel `audit_logs` pada event krusial.
    - [ ] Halaman laporan audit log untuk admin perusahaan.

---

## 3. Technical Architecture & Directory Structure (`LAWLEX_v2`)
Seluruh source code aplikasi, penyimpanan database, binari Redis, cache, dan skrip pendukung diisolasi mandiri di dalam direktori server:

```text
/home/nebsale7/LAWLEX_v2/
├── app/                  # Source code utama Laravel 13 (Monolith Architecture)
├── redis/                # Klaster/binari Redis versi user-space
│   ├── bin/              # File binary 'redis-server' dan 'redis-cli'
│   ├── etc/              # File konfigurasi 'redis.conf'
│   ├── run/              # PID file & socket file
│   └── data/             # Direktori penyimpanan dump.rdb (persistence)
├── storage/              # Storage Laravel (logs, framework cache)
└── scripts/              # Skrip pendukung (cron job launcher, queue worker)

Konfigurasi Redis Mandiri (LAWLEX_v2/redis/etc/redis.conf)
Ini, TOML

port 6379
bind 127.0.0.1
dir /home/nebsale7/LAWLEX_v2/redis/data/
pidfile /home/nebsale7/LAWLEX_v2/redis/run/redis.pid
daemonize yes

Eksekusi via SSH:
Bash

/home/nebsale7/LAWLEX_v2/redis/bin/redis-server /home/nebsale7/LAWLEX_v2/redis/etc/redis.conf

Stack Detail

    Framework: Laravel 13 (PHP 8.3) - Monolith

    Frontend Engine: Laravel Blade + Livewire v3 / Tailwind CSS

    Database: MySQL (nebsale7_lexlaw)

    Auth: Laravel Session-based Auth & Multi-Tenant Global Scope

    Search: MySQL FULLTEXT + Laravel Scout (database driver)

    Queue: Redis (user-space binary di LAWLEX_v2/redis)

    AI Gateway: 9Router (localhost:8080, model gas)

    Storage: Cloudflare R2 (S3-compatible)

4. Routes Reference
Auth & Onboarding

    GET /login, POST /login

    POST /logout

    GET /register, POST /register (Tenant & Company Signup)

SaaS Billing & Subscriptions

    GET /billing — Halaman kelola langganan & riwayat tagihan

    POST /billing/subscribe — Proses pembayaran/upgrade paket

    POST /webhook/payment — Endpoint webhook payment gateway

    GET/POST /super-admin/plans — CRUD paket harga & kuota oleh Super Admin

Core CRUD & Modules

    Resource routes untuk /regulations, /regulation-contents, /legal-glossary, /consolidations, /team-workspaces, /companies, /users, /audit-logs

AI Modules

    POST /ai/lex-qna — RAG Q&A

    POST /ai/draft — Legal drafting & Word export

    POST /ai/analyze — Validity checker & compliance highlight

    POST /ai/contract-review — Smart contract risk scoring & review

    POST /ai/precedent-matching — Case law & court precedent matching

PDF & Document Processing

    POST /pdf/upload — Upload + mPDF watermark + R2 storage

    POST /pdf/parse-text — Parse text & split pasal

5. UI/UX Design System ("Clean Enterprise / Neo-Editorial")

Prinsip antarmuka mengusung gaya minimalis modern kelas enterprise (estetika ala Linear, Stripe, atau Notion) untuk menghindari kesan "AI slop" yang berlebihan:
A. Karakteristik Visual

    Skala Warna Netral: Menggunakan basis palet monokromatik slate/zinc (slate-50 untuk background utama, slate-900 untuk teks, aksen Deep Indigo).

    Tipografi Presisi: Memakai sans-serif bersih seperti Inter atau Plus Jakarta Sans dengan pengaturan font-weight yang presisi.

    Border Tipis & Clean Cards: Menggunakan garis tepi halus (border border-slate-200 dark:border-slate-800) tanpa shadow tebal mencolok.

B. Komponen Utama

    Sidebar Navigasi: Responsif dengan transisi transition-all duration-300 ease-in-out dan dukungan dark mode otomatis (dark:bg-slate-950).

    Data Tables: Baris lapang (spacious padding), garis horizontal tipis, serta hover effect halus (hover:bg-slate-50/80 dark:hover:bg-slate-900/50).

    AI Chat/Q&A Flow: Format berbasis dokumen bersih (document-like flow) dengan penanda sitasi regulasi berbentuk pill-tags interaktif.

C. Contoh Snippet Komponen Kartu Dashboard (Tailwind + Blade)
HTML

<div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-xl p-5 shadow-2xs transition-all duration-200 hover:border-slate-300 dark:hover:border-slate-700">
    <div class="flex items-center justify-between">
        <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Regulasi Aktif</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
            Valid
        </span>
    </div>
    <div class="mt-3 flex items-baseline justify-between">
        <h3 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">1,482</h3>
        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">+12 minggu ini</span>
    </div>
</div>

6. Security Requirements & Deployment Checklist
Security

    [ ] CSRF protection (Laravel Session)

    [ ] Input validation (FormRequest)

    [ ] RBAC middleware & Tenant Data Isolation

    [ ] Rate limiting (Redis throttle) — TODO

    [ ] Audit log table — DONE (F20)

    [ ] Dynamic watermark (mPDF) — DONE

    [ ] Signed URLs (R2 presigned) — DONE

Deployment Checklist

    [ ] Direktori isolasi terstruktur (/home/nebsale7/LAWLEX_v2/)

    [ ] DB provisioned (nebsale7_lexlaw)

    [ ] Migrations run

    [ ] Redis binary running via LAWLEX_v2/redis

    [ ] Monolith backend & frontend tested

    [ ] Production .env (APP_DEBUG=false)

    [ ] Queue worker supervisor / cPanel Cron Job

    [ ] SSL certificate

    [ ] Domain/subdomain setup

Version History

    v1.0 (2026-08-28): Initial PRD

    v1.5 (2026-09-02): Shifted to Laravel Monolith, added SaaS multi-tenant billing, Super Admin pricing, enterprise AI modules, audit logging, and LAWLEX_v2 directory isolation.

    v1.6 (2026-09-02): Integrated Clean Enterprise / Neo-Editorial UI/UX guidelines into a unified Markdown specification.