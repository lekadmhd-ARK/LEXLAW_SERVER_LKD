<x-layouts.base title="Dashboard">
    <div class="page-head">
        <div><div class="eyebrow">📊 Dashboard</div><h1 class="page-title">Ringkasan Sistem</h1><p class="page-desc">Statistik dan aktivitas terbaru</p></div>
    </div>

    <div class="grid-4" style="margin-bottom:24px">
        <div class="card stat">
            <div class="num">{{ $stats['regulations'] ?? 0 }}</div>
            <div class="label">Regulations</div>
            <div class="hint">Database regulasi</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $stats['glossary'] ?? 0 }}</div>
            <div class="label">Glossary</div>
            <div class="hint">Terminologi hukum</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $stats['companies'] ?? 0 }}</div>
            <div class="label">Companies</div>
            <div class="hint">Tenant aktif</div>
        </div>
        <div class="card stat">
            <div class="num">{{ $stats['audit_logs'] ?? 0 }}</div>
            <div class="label">Audit Logs</div>
            <div class="hint">Aktivitas tercatat</div>
        </div>
    </div>

    <div class="card">
        <div class="eyebrow">📋 Endpoint Status</div>
        <table class="table" style="width:100%">
            <thead>
                <tr>
                    <th>Endpoint</th><th>Method</th><th>Status</th><th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>/dashboard</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/regulations</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/legal-glossary</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/ai/lex-qna</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/ai/draft</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/ai/validity</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/billing</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
                <tr><td>/password-change</td><td>GET</td><td><span style="padding:2px 8px;border-radius:99px;font-size:11px;background:#22c55e20;color:#22c55e">200</span></td><td>Live</td></tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;display:flex;gap:12px">
        <a href="/regulations" class="btn btn-primary">Lihat Semua Regulasi</a>
        <a href="/legal-glossary" class="btn btn-secondary">Lihat Glossary</a>
        <a href="/ai/draft" class="btn btn-secondary">AI Draft Generator</a>
    </div>
</x-layouts.base>