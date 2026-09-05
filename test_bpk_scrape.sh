#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
echo "=== TEST 1: curl langsung ke BPK ==="
curl -skL --max-time 25 -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64)" \
  https://peraturan.bpk.go.id/Details/350096/uu-no-5-tahun-2026 \
  -o /tmp/bpk.html -w 'HTTP:%{http_code} size:%{size_download}B\n'
echo "=== TITLE ==="
grep -oP '<title>\K[^<]+' /tmp/bpk.html | head -1
echo "=== JUDUL PERATURAN (h1/h2) ==="
grep -oiP '<h[12][^>]*>\K[^<]+' /tmp/bpk.html | head -10
echo "=== TENTANG ==="
grep -oiP 'tentang[^<]{0,200}' /tmp/bpk.html | head -5
echo "=== TANGGAL ==="
grep -oiP '(ditetapkan|diundangkan|tanggal)[^<]{0,120}' /tmp/bpk.html | head -10