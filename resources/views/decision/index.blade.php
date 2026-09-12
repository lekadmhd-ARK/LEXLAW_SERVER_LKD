<x-layouts.base title="Feeds Putusan">
<div>
  <div class="page-head" style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
    <div>
      <div class="eyebrow">Direktori Putusan Mahkamah Agung</div>
      <h1 class="page-title">Feeds Putusan</h1>
      <p class="page-desc">Filter bertahap PN → Klasifikasi → Tahun. Data lokal, tanpa DB, tanpa AI. Klik Muat untuk buka Direktori MA terfilter.</p>
    </div>
  </div>

  <div class="card" style="background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);padding:16px;margin-bottom:16px">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end">
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--muted)">1. PN Daerah</label>
        <select id="pn-select" style="width:100%;margin-top:6px;padding:10px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text)">
          <option value="">-- Pilih PN --</option>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--muted)">2. Klasifikasi Perkara</label>
        <select id="kategori-select" disabled style="width:100%;margin-top:6px;padding:10px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text)">
          <option value="">-- Pilih PN dulu --</option>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--muted)">3. Tahun Putusan</label>
        <select id="tahun-select" style="width:100%;margin-top:6px;padding:10px;border:1px solid var(--line);border-radius:var(--radius);background:var(--bg);color:var(--text)">
          <option value="">Semua Tahun</option>
          @for($y=date('Y'); $y>=2015; $y--)
            <option value="{{ $y }}">{{ $y }}</option>
          @endfor
        </select>
      </div>
      <button id="btn-muat" disabled style="padding:10px 18px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius);font-weight:700;cursor:pointer;opacity:.5">Muat</button>
    </div>
    <div id="filter-info" style="margin-top:10px;font-size:11px;color:var(--muted)"></div>
  </div>

  <div id="putusan-list"></div>
  <div id="putusan-status" style="text-align:center;padding:16px;color:var(--muted);font-size:13px">Pilih PN untuk mulai.</div>
</div>

<script>
let curPN='',curKat='',curThn='';
const pnSel=document.getElementById('pn-select');
const katSel=document.getElementById('kategori-select');
const thnSel=document.getElementById('tahun-select');
const btnMuat=document.getElementById('btn-muat');
const listEl=document.getElementById('putusan-list');
const statusEl=document.getElementById('putusan-status');
const infoEl=document.getElementById('filter-info');

async function loadPN(){
  statusEl.textContent='Memuat daftar PN...';
  try{
    const r=await fetch('{{ route('decisions.courts') }}');
    const j=await r.json();
    if(j.error) throw new Error(j.error);
    pnSel.innerHTML='<option value="">-- Pilih PN ('+j.courts.length+')</option>';
    j.courts.forEach(c=>{
      const o=document.createElement('option');
      o.value=c.slug; o.textContent=c.nama+' ('+(c.jumlah||c.jumlah_putusan||'')+')';
      pnSel.appendChild(o);
    });
    statusEl.textContent='Pilih PN untuk mulai.';
  }catch(e){ statusEl.textContent='Gagal memuat PN: '+e.message; }
}
loadPN();

pnSel.addEventListener('change', async ()=>{
  curPN=pnSel.value;
  katSel.innerHTML='<option value="">Memuat...</option>'; katSel.disabled=true;
  btnMuat.disabled=true; btnMuat.style.opacity=.5;
  listEl.innerHTML='';
  if(!curPN){ katSel.innerHTML='<option value="">-- Pilih PN dulu --</option>'; statusEl.textContent='Pilih PN untuk mulai.'; infoEl.textContent=''; return; }
  try{
    const r=await fetch('{{ route('decisions.categories') }}?pn='+encodeURIComponent(curPN));
    const j=await r.json();
    if(j.error) throw new Error(j.error);
    katSel.innerHTML='<option value="">Semua Klasifikasi</option>';
    j.categories.forEach(c=>{
      const o=document.createElement('option'); o.value=c.slug; o.textContent=c.label; katSel.appendChild(o);
    });
    katSel.disabled=false;
    btnMuat.disabled=false; btnMuat.style.opacity=1;
    statusEl.textContent=j.categories.length+' klasifikasi tersedia. Pilih lalu klik Muat.';
    infoEl.textContent='PN: '+pnSel.options[pnSel.selectedIndex].text;
  }catch(e){ katSel.innerHTML='<option value="">Gagal memuat</option>'; statusEl.textContent=e.message; }
});

katSel.addEventListener('change', ()=>{ infoEl.textContent='PN: '+(pnSel.options[pnSel.selectedIndex]?.text||'')+' → '+(katSel.options[katSel.selectedIndex]?.text||''); });

btnMuat.addEventListener('click', async ()=>{
  curKat=katSel.value; curThn=thnSel.value;
  if(!curPN){ statusEl.textContent='Pilih PN dulu.'; return; }
  statusEl.textContent='Menyiapkan filter...';
  btnMuat.disabled=true;
  try{
    const q=new URLSearchParams({pn:curPN});
    if(curKat) q.set('kategori',curKat);
    if(curThn) q.set('tahun',curThn);
    const r=await fetch('{{ route('decisions.fetch') }}?'+q.toString());
    const j=await r.json();
    if(j.error) throw new Error(j.error);
    listEl.innerHTML='';
    const card=document.createElement('div');
    card.style.cssText='padding:20px;background:var(--bg2);border:1px solid var(--line);border-radius:var(--radius);text-align:center';
    card.innerHTML='<div style="font-size:13px;color:var(--muted);margin-bottom:8px">'+esc(j.filterInfo||'')+'</div>'
      +'<a href="'+esc(j.directori_url)+'" target="_blank" rel="noopener" style="display:inline-block;padding:12px 20px;background:var(--accent);color:#fff;border-radius:var(--radius);font-weight:700;text-decoration:none">Buka di Direktori MA ↗</a>'
      +'<div style="margin-top:10px;font-size:11px;color:var(--muted);word-break:break-all">'+esc(j.directori_url)+'</div>';
    listEl.appendChild(card);
    statusEl.textContent='Filter siap — klik tombol untuk buka Direktori MA.';
  }catch(e){ statusEl.textContent='Gagal: '+e.message; }
  finally{ btnMuat.disabled=false; }
});
function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
</x-layouts.base>
