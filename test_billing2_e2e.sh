#!/bin/bash
set -euo pipefail
BASE="https://lexlaw.arktech.id"
JAR=$(mktemp)
LOGIN_TOKEN=""
GOT=( )
cleanup(){ rm -f "$JAR" "$LOGIN_TOKEN" "${GOT[@]}"; }
trap cleanup EXIT

echo "== 1. GET /login (ambil CSRF) =="
LOGIN_HTML=$(curl -sk -c "$JAR" -b "$JAR" "$BASE/login")
LOGIN_TOKEN=$(echo "$LOGIN_HTML" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
if [ -z "$LOGIN_TOKEN" ]; then
  TOKEN2=$(echo "$LOGIN_HTML" | grep -oP '_token"\s*content="\K[^"]+' | head -1)
  LOGIN_TOKEN=$TOKEN2
fi
echo "CSRF token: ${LOGIN_TOKEN:0:12}..."

EMAIL=admin@lexlaw.id
PASS=Admin123!

echo "== 2. POST /login =="
LOGIN_RESP=$(curl -sk -c "$JAR" -b "$JAR" -o /dev/null -w "%{http_code}" \
  -X POST "$BASE/login" \
  -d "_token=$LOGIN_TOKEN" -d "email=$EMAIL" -d "password=$PASS")
echo "login HTTP: $LOGIN_RESP"

echo "== 3. GET /billing2 (harus 200 setelah login) =="
B2=$(curl -sk -c "$JAR" -b "$JAR" -o /dev/null -w "%{http_code}" "$BASE/billing2")
echo "GET /billing2 HTTP: $B2"

echo "== 4. POST /billing2/make-dynamic (nominal=25000) =="
TOKEN=$(curl -sk -c "$JAR" -b "$JAR" "$BASE/billing2" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
POST_OUT=$(curl -sk -c "$JAR" -b "$JAR" -X POST "$BASE/billing2/make-dynamic" \
  -d "_token=$TOKEN" -d "nominal=25000")
echo "POST HTTP captured. Checking payload..."
echo "$POST_OUT" | grep -oP '000201[0-9A-F]+' | head -1 > /tmp/last_payload.txt
PAYLOAD=$(cat /tmp/last_payload.txt 2>/dev/null || true)
echo "PAYLOAD: ${PAYLOAD:0:80}..."
if echo "$PAYLOAD" | grep -q "540525000"; then
  echo "SUCCESS: Tag 54 Rp25.000 (540525000) ditemukan di respons!"
else
  echo "WARN: 540525000 tidak ditemukan di respons. Cek item berikut:"
  echo "$POST_OUT" | grep -oP '(HttpException|Error|exception|500|501|method|not support)' | sort -u | head
fi