#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/create -o /tmp/create.html 2>/dev/null
TOK=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/create.html | head -1)
echo "TOK: $TOK"

echo "=== STORE (ikuti redirect, tampilkan body) ==="
curl -skL -b /tmp/cookies -c /tmp/cookies -X POST https://lexlaw.arktech.id/regulations \
  --data-urlencode "_token=$TOK" \
  --data-urlencode "title=UU Test Simpan" \
  --data-urlencode "number=99" \
  --data-urlencode "year=2025" \
  --data-urlencode "hierarchy_level=1" \
  --data-urlencode "category_sector=lainnya" \
  --data-urlencode "status=active" \
  --data-urlencode "is_active=1" \
  --data-urlencode "short_description=Test ringkas" \
  --data-urlencode "source_url=https://peraturan.bpk.go.id/Details/350096" \
  --data-urlencode "pdf_url=https://peraturan.bpk.go.id/Download/413801/test.pdf" \
  --data-urlencode "content_text=Konten test" \
  -o /tmp/store_result.html -w 'HTTP:%{http_code} FINAL_URL:%{url_effective}\n' 2>/dev/null

echo "=== HASIL (grep error/success) ==="
grep -oiP '(success|error|berhasil|gagal|Regulasi berhasil)[^<]{0,100}' /tmp/store_result.html | head -5

echo "=== CEK DB TERAKHIR ==="
php artisan tinker --execute="\$r=App\Models\Regulation::latest()->first(); echo 'ID:'.(\$r->id??'null').' title:'.(\$r->title??'null').' pdf:'.(\$r->pdf_url??'NULL');" 2>/dev/null