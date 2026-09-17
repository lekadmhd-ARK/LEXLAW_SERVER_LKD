<x-layouts.base title="Analitik Bisnis — LEXLAW v2">

<div class="page-head">
  <div>
    <div class="eyebrow">Super Admin</div>
    <h1 class="page-title">Analitik Bisnis</h1>
    <p class="page-desc">Metrik SaaS: status langganan, MRR, distribusi paket, dan konversi trial.</p>
  </div>
  <div style="display:flex;gap:8px">
    <a href="/super-admin/companies" class="btn btn-secondary">Companies</a>
    <a href="/super-admin/plans" class="btn btn-secondary">Plans</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px">
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Total Perusahaan</div>
    <div style="font-size:26px;font-weight:700;color:var(--text);margin-top:4px">{{ number_format($totalCompanies) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Aktif</div>
    <div style="font-size:26px;font-weight:700;color:#22c55e;margin-top:4px">{{ number_format($active) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Trial</div>
    <div style="font-size:26px;font-weight:700;color:#f59e0b;margin-top:4px">{{ number_format($trialing) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Suspend</div>
    <div style="font-size:26px;font-weight:700;color:#ef4444;margin-top:4px">{{ number_format($suspended) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Nonaktif</div>
    <div style="font-size:26px;font-weight:700;color:var(--muted);margin-top:4px">{{ number_format($inactive) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Ditolak</div>
    <div style="font-size:26px;font-weight:700;color:#6b7280;margin-top:4px">{{ number_format($rejected) }}</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:18px">
    <div style="font-size:12px;color:var(--muted)">Total User</div>
    <div style="font-size:26px;font-weight:700;color:var(--text);margin-top:4px">{{ number_format($totalUsers) }}</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:22px">
    <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px">MRR (Monthly Recurring Revenue)</div>
    <div style="font-size:30px;font-weight:800;color:var(--accent)">Rp {{ number_format($mrr, 0, ',', '.') }}</div>
    <div style="font-size:12px;color:var(--muted);margin-top:6px">Berdasarkan harga bulanan paket perusahaan aktif &amp; trial (tanpa PPN).</div>
  </div>
  <div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:22px">
    <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px">Trial → Paid Conversion</div>
    <div style="font-size:30px;font-weight:800;color:var(--accent)">{{ $conversionRate }}%</div>
    <div style="font-size:12px;color:var(--muted);margin-top:6px">Aktif / (Trial + Aktif). Target awal &gt; 20%.</div>
  </div>
</div>

<div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:22px;margin-bottom:16px">
  <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:14px">Distribusi Perusahaan per Paket (aktif &amp; trial)</div>
  @if($planDist->count())
  <table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead>
      <tr style="color:var(--muted);text-align:left">
        <th style="padding:6px 8px">Paket</th>
        <th style="padding:6px 8px">Perusahaan</th>
        <th style="padding:6px 8px;text-align:right">MRR (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($planDist as $row)
      <tr style="border-top:1px solid var(--line)">
        <td style="padding:10px 8px;font-weight:600;color:var(--text)">{{ $row->plan }}</td>
        <td style="padding:10px 8px">{{ number_format($row->companies) }}</td>
        <td style="padding:10px 8px;text-align:right">{{ number_format($row->mrr, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div style="color:var(--muted);font-size:13px">Belum ada perusahaan berlangganan.</div>
  @endif
</div>

<div style="background:var(--bg2);border:1px solid var(--line);border-radius:12px;padding:22px">
  <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:14px">Signup Perusahaan — 8 Pekan Terakhir</div>
  @if($signups->count())
  <div style="display:flex;align-items:flex-end;gap:10px;min-height:150px">
    @foreach($signups as $row)
    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
      <div style="font-size:12px;font-weight:700;color:var(--text)">{{ $row->total }}</div>
      <div style="width:100%;max-width:52px;background:linear-gradient(180deg,var(--accent),#8b5cf6);border-radius:6px 6px 0 0;min-height:8px;height:{{ max(8, $row->total * 24) }}px"></div>
      <div style="font-size:10px;color:var(--muted)">{{ \Illuminate\Support\Carbon::parse($row->week)->format('d/m') }}</div>
    </div>
    @endforeach
  </div>
  @else
  <div style="color:var(--muted);font-size:13px">Belum ada data dalam 8 pekan terakhir.</div>
  @endif
</div>

</x-layouts.base>