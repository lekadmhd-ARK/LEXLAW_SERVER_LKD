#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
curl -skL --max-time 20 -A "Mozilla/5.0" \
  https://peraturan.bpk.go.id/Details/350096/uu-no-5-tahun-2026 \
  -o /tmp/bpk.html 2>/dev/null

php -r '
$html = file_get_contents("/tmp/bpk.html");
echo "=== ALL DATES FOUND ===\n";
preg_match_all("/\b(\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/", $html, $matches);
echo implode(", ", $matches[0]) . "\n";

echo "=== FIRST REGEX TEST ===\n";
if (preg_match("/Tanggal\s+Penetapan\b[^<]*<\/div>\s*<div[^>]*>.*?<[^>]*>\s*([^<\r\n]+(?:\s+[^<\r\n]+)*)/is", $html, $m)) {
    echo "MATCH: " . trim($m[1]) . "\n";
} else {
    echo "NO MATCH\n";
    // Try simpler: just grab date after "Tanggal Penetapan"
    $after = strstr($html, "Tanggal Penetapan");
    if ($after) {
        $snippet = substr($after, 0, 500);
        echo "Snippet after label:\n" . html_entity_decode(strip_tags($snippet)) . "\n";
    }
}
'