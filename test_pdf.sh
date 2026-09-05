#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/login.html | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=superadmin@lawlex.test&password=Admin123!" https://lexlaw.arktech.id/login -o /dev/null 2>/dev/null
curl -skL -b /tmp/cookies https://lexlaw.arktech.id/ai/contract-review -o /tmp/contract.html 2>/dev/null
TOK=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/contract.html | head -1)

echo "=== TEST UPLOAD PDF ==="
curl -skL -b /tmp/cookies -X POST https://lexlaw.arktech.id/ai/contract-review \
  -H "Content-Type: multipart/form-data" \
  -H "X-CSRF-TOKEN: $TOK" \
  -F "contract_file=@/home/lekadmhd/project/LAWLEX_v2/tests/sample-contract.pdf" \
  -F "_token=$TOK" \
  -o /tmp/result.html -w 'HTTP:%{http_code} size:%{size_download}B\n' 2>/dev/null
echo "=== CHECK RESULT ==="
grep -oP 'Risk Score|Risk Items|Compliance Checklist' /tmp/result.html | head -10