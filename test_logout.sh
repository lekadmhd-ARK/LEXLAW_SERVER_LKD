#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null

echo "=== TEST LOGOUT GET ==="
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/logout -o /tmp/logout.html -w 'HTTP:%{http_code}\n' 2>/dev/null
grep -oP '<title>\K[^<]+' /tmp/logout.html 2>/dev/null || echo "Redirect: $(grep -i 'location' /tmp/logout.html | head -1)"