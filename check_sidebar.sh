#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null

echo "=== SIDEBAR LINKS ==="
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/dashboard -o /tmp/dash.html 2>/dev/null
grep -oP 'class="nav-link[^"]*">\K[^<]+' /tmp/dash.html

echo ""
echo "=== CHECK EACH MENU ==="
for url in "/dashboard" "/regulations" "/ai/lex-qna" "/ai/draft" "/ai/validity" "/ai/contract-review" "/billing"; do
    code=$(curl -skL -b /tmp/cookies -o /dev/null -w '%{http_code}' "https://lexlaw.arktech.id$url" 2>/dev/null)
    size=$(curl -skL -b /tmp/cookies -o /dev/null -w '%{size_download}' "https://lexlaw.arktech.id$url" 2>/dev/null)
    echo "$url → HTTP:$code size:${size}B"
done