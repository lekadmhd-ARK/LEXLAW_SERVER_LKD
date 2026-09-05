#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
KEY=$(grep '^AI_API_KEY' .env | cut -d= -f2)
curl -sk --max-time 60 -X POST http://127.0.0.1:20128/v1/chat/completions \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $KEY" \
  -d '{"model":"gemini-3.5-flash","messages":[{"role":"system","content":"Anda adalah asisten hukum Indonesia. Kembalikan jawaban dalam format JSON array."},{"role":"user","content":"Cari 3 undang-undang atau peraturan Indonesia yang aktif dan masih berlaku terkait ketenagakerjaan. Berikan output dalam format JSON array dengan field: title, number, year, hierarchy_level, category_sector, source_url."}],"stream":false,"max_tokens":2000}' \
  -w '\nHTTP:%{http_code}\n' 2>&1 | tail -10
