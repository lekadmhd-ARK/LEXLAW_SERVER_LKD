#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
KEY=$(grep '^AI_API_KEY' .env | cut -d= -f2)
echo "API_KEY length: ${#KEY}"
curl -sk --max-time 30 -X POST http://127.0.0.1:20128/v1/chat/completions \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $KEY" \
  -d '{"model":"ARK","messages":[{"role":"user","content":"Coba respon dengan singkat"}],"stream":false,"max_tokens":50}' \
  -w '\nHTTP:%{http_code}\n' 2>&1 | tail -5
