#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/26/edit -w '\nHTTP:%{http_code} size:%{size_download}B\n' -o /tmp/edit.html 2>/dev/null
echo "=== EDIT PAGE SIZE ==="
wc -c /tmp/edit.html
echo "=== ERROR CHECK ==="
grep -ci 'error\|exception\|500' /tmp/edit.html | head -3
echo "=== PREVIEW ==="
grep -oP '<title>\K[^<]+' /tmp/edit.html
