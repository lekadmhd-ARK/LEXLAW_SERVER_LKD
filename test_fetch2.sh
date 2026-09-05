#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html -w 'login:%{http_code}\n'
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null -w 'login2:%{http_code}\n'
# Ambil token dari halaman /regulations/create
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/regulations/create -o /tmp/create.html -w 'create:%{http_code}\n'
TOK=$(grep -oP 'csrf-token.*content="\K[^"]+' /tmp/create.html | head -1)
echo "CSRF Token: $TOK"
if [ -z "$TOK" ]; then
  TOK=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/create.html | head -1)
  echo "Fallback Token: $TOK"
fi
curl -skL -b /tmp/cookies -X POST https://lexlaw.arktech.id/regulations/fetch-jdih -H 'Content-Type: application/json' -H 'Accept: application/json' -H "X-CSRF-TOKEN: $TOK" -H "X-Requested-With: XMLHttpRequest" -d '{"url":"https://jdih.tangerangkab.go.id/dokumen/detail/111849"}' -w '\nfetch:%{http_code} %{size_download}B\n' 2>&1 | head -50