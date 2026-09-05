#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies

# 1. Login
TOKEN=$(curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login | grep -oP 'name="_token" value="\K[^"]+' | head -1)
curl -skL -b /tmp/cookies -c /tmp/cookies -d "_token=$TOKEN&email=admin@test.com&password=password" https://lexlaw.arktech.id/login -o /dev/null

# 2. Ambil token baru dari form contract-review
TOKEN2=$(curl -skL -b /tmp/cookies -c /tmp/cookies https://lexlaw.arktech.id/ai/contract-review | grep -oP 'name="_token" value="\K[^"]+' | head -1)

# 3. POST contract review
curl -skL -b /tmp/cookies -c /tmp/cookies -X POST https://lexlaw.arktech.id/ai/contract-review \
  -H "Accept: application/json" \
  -d "_token=${TOKEN2}" \
  -d "contract_text=Surat Perjanjian Kerjasama Usaha. Pihak Pertama Arnold Widjaja. Pihak Kedua Christian Putranto. Kerjasama membangun usaha bersama dengan modal total Rp370.000.000. Pembagian keuntungan 3 persen bulanan. Pasal 4: Kerugian ditanggung bersama. Perselisihan diselesaikan kekeluargaan, lalu hukum. Jakarta, 27 November 2020."
