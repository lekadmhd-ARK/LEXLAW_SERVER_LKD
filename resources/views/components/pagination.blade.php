@php
  $total   = $paginator->total();
  $current = $paginator->currentPage();
  $last    = $paginator->lastPage();
  $from    = $paginator->firstItem();
  $to      = $paginator->lastItem();

  $pages = [1];
  if ($last > 1) {
    $start = max(2, $current - 2);
    $end   = min($last - 1, $current + 2);
    if ($start > 2) $pages[] = '…';
    for ($i = $start; $i <= $end; $i++) $pages[] = $i;
    if ($end < $last - 1) $pages[] = '…';
    $pages[] = $last;
    $pages = array_values(array_unique($pages));
  }
@endphp

@if ($total > 0)
<style>
.pg { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:18px; padding-top:16px; border-top:1px solid var(--line); }
.pg-info { font-size:13px; color:var(--muted); }
.pg-info b { color:var(--text); font-weight:600; }
.pg-nav { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.pg-btn, .pg-gap {
  min-width:32px; height:32px; padding:0 10px; display:inline-flex; align-items:center; justify-content:center;
  border:1px solid var(--line); border-radius:8px; background:var(--bg2); color:var(--text);
  font-size:13px; text-decoration:none; transition:background .12s, border-color .12s;
}
a.pg-btn:hover { border-color:var(--accent); color:var(--accent); }
.pg-btn.on { background:var(--accent); border-color:var(--accent); color:#fff; font-weight:600; }
.pg-btn.on:hover { color:#fff; }
.pg-btn.dis, .pg-gap { opacity:.45; cursor:default; pointer-events:none; }
.pg-gap { border:none; background:transparent; }
</style>
<div class="pg">
  <div class="pg-info">
    Menampilkan <b>{{ number_format($from ?? 0, 0, ',', '.') }}–{{ number_format($to ?? 0, 0, ',', '.') }}</b>
    dari <b>{{ number_format($total, 0, ',', '.') }}</b> entri
  </div>

  @if ($paginator->hasPages())
  <nav class="pg-nav" aria-label="Navigasi halaman">
    @if ($paginator->onFirstPage())
      <span class="pg-btn dis">‹</span>
    @else
      <a class="pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
    @endif

    @foreach ($pages as $p)
      @if ($p === '…')
        <span class="pg-gap">…</span>
      @elseif ($p == $current)
        <span class="pg-btn on">{{ $p }}</span>
      @else
        <a class="pg-btn" href="{{ $paginator->url($p) }}">{{ $p }}</a>
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a class="pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
    @else
      <span class="pg-btn dis">›</span>
    @endif
  </nav>
  @endif
</div>
@endif