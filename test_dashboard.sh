#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/dashboard -o /tmp/dashboard.html -w 'HTTP:%{http_code} size:%{size_download}B\n' 2>/dev/null
echo "=== DASHBOARD ==="
grep -oP '<h1 class="page-title">\K[^<]+' /tmp/dashboard.html
echo "=== STATS ==="
grep -c 'Total Regulasi Terindeks' /tmp/dashboard.html
echo "=== QUICK ACTIONS ==="
grep -c 'Tambah Regulasi\|Lex Q&A\|Draft Dokumen\|Validity Checker' /tmp/dashboard.html
echo "=== LATEST REGS ==="
grep -c 'Regulasi Terbaru' /tmp/dashboard.html