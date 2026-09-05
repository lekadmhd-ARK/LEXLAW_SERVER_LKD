<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEXLAW v2 — Legal Intelligence & SaaS Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#08090a;--panel:#0f1011;--surface:#151719;--text:#f7f8f8;--muted:#d0d6e0;--dim:#8a8f98;--line:rgba(255,255,255,.08);--line2:rgba(255,255,255,.05);--brand:#5e6ad2;--brand2:#7170ff;--ok:#10b981}
        *{box-sizing:border-box}html,body{margin:0;min-height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,sans-serif;font-feature-settings:"cv01","ss03";line-height:1.5;-webkit-font-smoothing:antialiased}
        body:before{content:"";position:fixed;inset:0;background:radial-gradient(circle at 50% -20%,rgba(113,112,255,.18),transparent 50%),radial-gradient(circle at 10% 80%,rgba(16,185,129,.06),transparent 40%);pointer-events:none;z-index:-1}
        a{color:inherit;text-decoration:none}
        .nav{position:sticky;top:0;height:68px;border-bottom:1px solid var(--line2);background:rgba(8,9,10,.75);backdrop-filter:blur(16px);display:flex;align-items:center;justify-content:space-between;padding:0 40px;z-index:100}
        .brand{display:flex;align-items:center;gap:12px}.logo{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--brand),#9b8cff);display:grid;place-items:center;font-weight:600}
        .nav-links{display:flex;gap:24px;font-size:13px;font-weight:500;color:var(--muted)}
        .nav-links a:hover{color:var(--text)}
        .nav-actions{display:flex;gap:12px;align-items:center}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:510;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--muted);cursor:pointer;transition:all .15s}
        .btn:hover{background:rgba(255,255,255,.06);color:var(--text)}
        .btn-primary{background:var(--brand);border-color:var(--brand);color:#fff}
        .btn-primary:hover{background:var(--brand2)}
        .hero{max-width:1080px;margin:80px auto 40px;padding:0 24px;text-align:center}
        .pill{display:inline-flex;align-items:center;gap:8px;padding:4px 12px;border-radius:999px;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--muted);font-size:12px;margin-bottom:24px}
        .hero h1{font-size:56px;line-height:1.05;font-weight:590;letter-spacing:-1.5px;margin:0 0 20px;background:linear-gradient(180deg,#f7f8f8 30%,#8a8f98 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero p{font-size:18px;color:var(--muted);max-width:680px;margin:0 auto 36px;line-height:1.6}
        .hero-cta{display:flex;justify-content:center;gap:16px}
        .features{max-width:1080px;margin:0 auto 80px;padding:0 24px}
        .section-title{text-align:center;margin-bottom:48px}
        .section-title h2{font-size:32px;font-weight:590;letter-spacing:-.8px;margin:0 0 12px}
        .section-title p{color:var(--dim);font-size:16px;margin:0}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .card{background:rgba(255,255,255,.025);border:1px solid var(--line);border-radius:14px;padding:28px;transition:transform .2s ease,box-shadow .2s ease}
        .card:hover{transform:translateY(-4px);box-shadow:0 20px 60px rgba(94,106,210,.15)}
        .card h3{margin:0 0 10px;font-size:18px;font-weight:590}
        .card p{margin:0;color:var(--dim);font-size:14px;line-height:1.6}
        .faq-item{border-bottom:1px solid var(--line2);padding:16px 0}
        .faq-question{font-weight:510;font-size:14px;cursor:pointer;display:flex;align-items:center;gap:8px;transition:color .15s}
        .faq-question:hover{color:var(--brand)}
        .faq-answer{color:var(--muted);font-size:13px;margin-top:8px;max-height:0;overflow:hidden;transition:max-height .3s ease}
        .faq-item.open .faq-answer{max-height:100px;padding-top:8px;border-top:1px solid var(--line2)}
        .cta-box{background:linear-gradient(135deg,rgba(94,106,210,.15),rgba(113,112,255,.1));border:1px solid var(--line);border-radius:16px;padding:32px;text-align:center;transition:transform .3s ease}
        .cta-box:hover{transform:translateY(-8px)}
        footer{border-top:1px solid var(--line2);padding:40px 0;text-align:center;color:var(--dim);font-size:13px}
        @media(max-width:768px){.hero h1{font-size:36px}.grid-3,.pricing-grid{grid-template-columns:1fr}.nav{padding:0 20px}.nav-links{display:none}}
    </style>
</head>
<body>
    <header class="nav">
        <div class="brand">
            <div class="logo">⚖</div>
            <span style="font-weight:600;font-size:16px;letter-spacing:-.3px">LEXLAW v2</span>
       </div>
        <div class="nav-links">
            <a href="#features">Fitur</a>
            <a href="#faq">FAQ</a>
            <a href="#terms">Syarat & Ketentuan</a>
            <a href="#refund">Refund Policy</a>
       </div>
        <div class="nav-actions">
            <a href="/login" class="btn">Login</a>
            <a href="/register" class="btn btn-primary">Mulai Sekarang</a>
       </div>
   </header>

    <section class="hero">
        <div class="pill">✦ Platform Legal Intelligence & SaaS Berbasis AI</div>
        <h1>Sistem Operasi Hukum Modern<br>untuk Perusahaan & Praktisi</h1>
        <p>Integrasi database regulasi nasional, AI Lex Q&A berstandar RAG, automated legal drafting DOCX, dan billing QRIS multi-tenant dalam satu platform presisi.</p>
        <div class="hero-cta">
            <a href="/register" class="btn btn-primary" style="padding:12px 24px;font-size:14px">Akses Dashboard →</a>
            <a href="/login" class="btn" style="padding:12px 24px;font-size:14px">Masuk Akun</a>
       </div>
   </section>

    <section class="features" id="features" style="opacity:0;transform:translateY(20px);transition:opacity .6s ease .2s,transform .6s ease .2s">
        <div class="section-title">
            <h2>Arsitektur Legal Tech Profesional</h2>
            <p>Dirancang khusus untuk efisiensi workflow hukum tingkat lanjut.</p>
       </div>
        <div class="grid-3">
            <div class="card">
                <h3>✦ AI Lex Q&A RAG</h3>
                <p>Pencarian regulasi nasional secara FULLTEXT dipadukan dengan AI untuk jawaban akurat bersumber pasal.</p>
           </div>
            <div class="card">
                <h3>✎ Automated Drafting</h3>
                <p>Buat NDA, Perjanjian Kerjasama, MoU, dan surat formal lainnya secara instan langsung unduh format DOCX.</p>
           </div>
            <div class="card">
                <h3>✓ Validity Checker</h3>
                <p>Deteksi otomatis sitasi UU, PP, Perpres, Permen, dan Perda di dalam dokumen serta verifikasi status hukum aktif.</p>
           </div>
       </div>
   </section>

    <section class="cta-box">
        <h3>Mulai Analisis Kontrak Sekarang</h3>
        <p>Unggah file kontrak atau masukkan teks — Dapatkan laporan profesional dalam detik.</p>
        <a href="/ai/contract-review" class="btn btn-primary" style="padding:12px 24px;font-size:14px;margin-top:16px">Buka Contract Reviewer</a>
   </section>

    <section class="faq" id="faq" style="opacity:0;transform:translateY(20px);transition:opacity .6s ease .4s,transform .6s ease .4s">
        <div class="section-title">
            <h2>❓ Frequently Asked Questions</h2>
            <p style="color:var(--dim);font-size:16px;margin:0 0 32px">Pertanyaan paling sering mengenai penggunaan platform LEXLAW</p>
       </div>
        <div class="faq-item" onclick="this.classList.toggle('open');this.querySelector('.faq-question').classList.toggle('open')">
            <div class="faq-question"><span>🔍 Apa itu LEXLAW?</span></div>
            <div class="faq-answer"><p>LEXLAW adalah platform Legal Intelligence & SaaS berbasis AI yang membantu Anda menganalisis kontrak, mencari regulasi, membuat draft dokumen hukum, dan memeriksa validitas sitasi hukum — semuanya dalam satu dashboard.</p></div>
       </div>
       <div class="faq-item" onclick="this.classList.toggle('open');this.querySelector('.faq-question').classList.toggle('open')">
            <div class="faq-question"><span>🛡️ Apakah data saya aman?</span></div>
            <div class="faq-answer"><p>Ya. Data kontrak Anda tidak disimpan di server kami — hanya diproses sekali saat analisis. Semua data dienkripsi dan sesuai Kebijakan Privasi kami.</p></div>
       </div>
       <div class="faq-item" onclick="this.classList.toggle('open');this.querySelector('.faq-question').classList.toggle('open')">
            <div class="faq-question"><span>⏱️ Berapa lama waktu analisis?</span></div>
            <div class="faq-answer"><p>Analisis kontrak membutuhkan waktu 10–30 detik tergantung panjang teks. Draft dokumen sekitar 15–45 detik.</p></div>
       </div>
       <div class="faq-item" onclick="this.classList.toggle('open');this.querySelector('.faq-question').classList.toggle('open')">
            <div class="faq-question"><span>📄 Format file apa yang didukung?</span></div>
            <div class="faq-answer"><p>Contract Reviewer mendukung PDF, DOCX, dan TXT (maks 10MB). Output bisa diunduh sebagai PDF atau Word.</p></div>
       </div>
       <div class="faq-item" onclick="this.classList.toggle('open');this.querySelector('.faq-question').classList.toggle('open')">
            <div class="faq-question"><span>👨‍⚖️ Apakah AI menggantikan pengacara?</span></div>
            <div class="faq-answer"><p>Tidak. LEXLAW adalah alat bantu referensi — bukan nasihat hukum profesional. Selalu verifikasi dengan pengacara berlisensi.</p></div>
       </div>
   </section>

    <section class="terms" id="terms" style="opacity:0;transform:translateY(20px);transition:opacity .6s ease .6s,transform .6s ease .6s">
        <div class="section-title">
            <h2>📜 Syarat & Ketentuan Layanan</h2>
       </div>
       <p style="color:var(--dim);font-size:16px;margin:0 0 24px">Dokumen ini mengatur hubungan antara Anda dan LexLaw terkait penggunaan layanan, fitur, dan konten yang disediakan melalui website dan aplikasi LexLaw.</p>
       <ul style="color:var(--text);font-size:14px;line-height:1.8;margin:0 0 24px;padding-left:20px">
           <li><strong>1. Penerimaan Syarat.</strong> Dengan mengakses, mendaftar, atau menggunakan layanan LexLaw, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh Syarat & Ketentuan ini.</li>
           <li><strong>2. Jangkauan Layanan.</strong> Layanan mencakup pencarian regulasi, analisis kontrak, pembuatan draft, dan validitas sitasi hukum sesuai paket layanan yang dipilih.</li>
           <li><strong>3. Kewajiban Pengguna.</strong> Anda bertanggung jawab atas keamanan akun Anda dan dilarang menggunakan Layanan untuk aktivitas ilegal.</li>
           <li><strong>4. Pelepasan Tanggung Jawab.</strong> LEXLAW tidak bertanggung jawab atas kerugian materiil/immateriil yang timbul akibat penggunaan atau ketergantungan penuh pada hasil AI.</li>
       </ul>
       <a href="/terms-of-service" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:16px">Baca Syarat Lengkap →</a>
   </section>

    <section class="refund" id="refund" style="opacity:0;transform:translateY(20px);transition:opacity .6s ease .8s,transform .6s ease .8s">
        <div class="section-title">
            <h2>💰 Kebijakan Refund</h2>
       </div>
       <p style="color:var(--dim);font-size:16px;margin:0 0 24px">Di LEXLAW, kami berkomitmen untuk kepuasan pelanggan.</p>
       <ul style="color:var(--text);font-size:14px;line-height:1.8;margin:0 0 24px;padding-left:20px">
           <li><strong>7 Hari Jaminan Keuangan.</strong> Setiap pelanggan baru berhak atas jembelian dana penuh (100% refund) dalam kurun waktu 7 (tujuh) hari kalender sejak tanggal transaksi pertama.</li>
           <li><strong>Proses Pengajuan.</strong> Pengajuan dikirimkan melalui email ke <strong>support@lexlaw.arktech.id</strong> dengan melampirkan bukti pembayaran dan alasan pembatalan.</li>
           <li><strong>Pengecualian.</strong> Permintaan refund setelah melewati batas 7 hari tidak dapat diproses. Langganan yang diperpanjang secara otomatis tidak termasuk dalam garansi 7 hari, kecuali terdapat kendala teknis dari pihak kami.</li>
           <li><strong>Waktu Proses.</strong> Dana yang disetujui untuk di-refund akan dikembalikan dalam 3–14 hari kerja tergantung kebijakan bank atau gateway pembayaran.</li>
       </ul>
       <a href="/refund-policy" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:16px">Lihat Detail Lengkap →</a>
   </section>

    <footer>
        <p>© 2026 LEXLAW v2 — Legal Intelligence SaaS. All rights reserved</p>
        <p style="margin-top:8px;color:var(--dim)">Data regulasi bersumber dari dokumen resmi pemerintah Indonesia (public domain). Informasi bersifat referensi.</p>
   </footer>

    <script>
    (function(){
        // Intersection Observer untuk animasi fade-in saat scroll
        const observerOptions = {threshold: 0.1};
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.features > div, .faq-item, .terms, .refund').forEach(el => {
            observer.observe(el);
        });

        // FAQ toggle
        document.querySelectorAll('.faq-item').forEach(function(item){
            item.addEventListener('click', function(){
                this.classList.toggle('open');
                var answer = this.querySelector('.faq-answer');
                if(this.classList.contains('open')){
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }else{
                    answer.style.maxHeight = '0';
                }
            });
        });
    })();
    </script>
</body>
</html>