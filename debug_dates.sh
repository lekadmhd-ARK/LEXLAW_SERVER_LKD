#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
curl -skL --max-time 20 -A "Mozilla/5.0" \
  https://peraturan.bpk.go.id/Details/350096/uu-no-5-tahun-2026 \
  -o /tmp/bpk.html 2>/dev/null

echo "=== Section Penetapan (raw) ==="
grep -A2 -B1 "Tanggal Penetapan" /tmp/bpk.html | head -20
echo "==="
echo "=== Coba regex PHP ==="
php -r '
$html = file_get_contents("/tmp/bpk.html");
if (preg_match("/Tanggal\s+Penetapan\s*[\n\r]+([^\n\r<]+)/i", $html, $m)) {
    echo "MATCH: " . trim($m[1]) . "\n";
} else {
    echo "NO MATCH\n";
}
'