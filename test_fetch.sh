#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html -w 'login:%{http_code}\n'
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null -w 'login2:%{http_code}\n'
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -X POST https://lexlaw.arktech.id/regulations/fetch-jdih -H 'Content-Type: application/json' -H 'Accept: application/json' -H "X-CSRF-TOKEN: $TOKEN" -d '{"url":"https://jdih.tangerangkab.go.id/dokumen/detail/111849"}' -w '\nfetch:%{http_code} %{size_download}B\n' 2>&1 | head -30