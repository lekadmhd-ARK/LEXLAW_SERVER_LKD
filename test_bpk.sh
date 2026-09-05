#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html -w 'login:%{http_code}\n'
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null -w 'login2:%{http_code}\n'
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/create -o /tmp/create.html -w 'create:%{http_code}\n'
TOK=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/create.html | head -1)
echo "CSRF: $TOK"
curl -skL -b /tmp/cookies -X POST https://lexlaw.arktech.id/regulations/fetch-jdih \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -H "X-CSRF-TOKEN: $TOK" -H "X-Requested-With: XMLHttpRequest" \
  -d '{"url":"https://peraturan.bpk.go.id/Details/350096/uu-no-5-tahun-2026"}' \
  -w '\nfetch:%{http_code} %{size_download}B\n' 2>&1 | head -40