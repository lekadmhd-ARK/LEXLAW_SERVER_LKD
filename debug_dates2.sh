#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
curl -skL --max-time 20 -A "Mozilla/5.0" \
  https://peraturan.bpk.go.id/Details/350096/uu-no-5-tahun-2026 \
  -o /tmp/bpk.html 2>/dev/null

echo "=== More context around Tanggal Penetapan ==="
grep -A10 "Tanggal Penetapan" /tmp/bpk.html | head -20
echo "==="
echo "=== More context around Tanggal Pengundangan ==="
grep -A10 "Tanggal Pengundangan" /tmp/bpk.html | head -20