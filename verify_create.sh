#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html -w 'login:%{http_code}\n'
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null -w 'login2:%{http_code}\n'
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/create -o /tmp/create.html -w 'create:%{http_code} %{size_download}B\n'
echo "=== CHECK fetchJdih ==="
grep -c 'function fetchJdih' /tmp/create.html
echo "=== CHECK fetch-btn onclick ==="
grep -c 'onclick="fetchJdih()"' /tmp/create.html
echo "=== CHECK stack rendered ==="
grep -c 'async function fetchJdih' /tmp/create.html