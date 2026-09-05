#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/create -o /tmp/create.html -w 'create:%{http_code} size:%{size_download}B\n' 2>/dev/null
echo "=== CHECK fetchJdih present ==="
grep -c 'function fetchJdih' /tmp/create.html
echo "=== CHECK jdih-url input ==="
grep -c 'id="jdih-url"' /tmp/create.html
echo "=== CHECK fetch-btn ==="
grep -c 'onclick="fetchJdih()"' /tmp/create.html
echo "=== CHECK csrf token meta ==="
grep -c 'csrf-token' /tmp/create.html