#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
rm -f /tmp/cookies

# 1. Ambil CSRF token dari login page
curl -skL -c /tmp/cookies https://lexlaw.arktech.id/login -o /tmp/login.html 2>/dev/null
TOKEN=$(grep -o 'name="_token" value="[^"]*"' /tmp/login.html | head -1 | grep -o 'value="[^"]*"' | sed 's/value="//;s/"//')
echo "CSRF TOKEN: $TOKEN"

# 2. Ambil session cookie
cat /tmp/cookies | grep laravel_session

# 3. POST contract review
curl -skL -b /tmp/cookies -c /tmp/cookies \
  -X POST https://lexlaw.arktech.id/ai/contract-review \
  -d "_token=${TOKEN}" \
  -d "contract_text=Surat Perjanjian Kerjasama Usaha Jakarta 14 November 2020 Surat perjanjian ini dibuat sebagai salah satu bentuk untuk membuat sebuah usaha yang dilakukan bersama Saya yang bertanda tangan di bawah ini Nama Arnold Widjaja Tempat Tanggal Lahir Surabaya 12 Mei 1978 Alamat Jl Jenderal Sudirman No 19 Jakarta Akan disebut sebagai pihak pertama Nama Christian Putranto Tempat Tanggal Lahir Jakarta 23 Juni 1990 Alamat Jl Pasar Baru 3 No 12 Jakarta Akan disebut sebagai pihak kedua Nama Islamiyah Citra Tempat Tanggal Lahir Purwakarta 18 Maret 1989 Alamat Jl Otto Iskandar Dinata No 39 Bandung Akan disebut sebagai pihak ketiga Ketiga pihak secara sadar dan tanpa adanya paksaan telah setuju untuk melakukan kerjasama dalam membangun usaha secara bersama dengan ketentuan yang ada seperti berikut ini Pasal 1 Pihak pertama akan menanamkan modal usaha senilai Rp150000000 Seratus lima puluh juta rupiah pihak kedua akan menanamkan modal usaha senilai Rp 100000000 Seratus juta rupiah dan pihak ketiga akan memberikan modal berupa peralatan usaha dengan nilai total sebesar Rp120000000 Seratus dua puluh juta rupiah Pasal 2 Hasil dari usaha akan dibagi secara merata dari keuntungan dengan nilai sebesar 3 persen berdasarkan keuntungan yang didapat setiap bulannya Pasal 3 Pihak pertama akan bertugas untuk melakukan pemasaran secara daring dan langsung Pihak kedua akan bertanggung jawab dalam melakukan pengelolaan usaha di lapangan dan pihak ketiga akan mengurus keuangan dari usaha yang dijalankan Pasal 4 Kerugian akan ditanggung secara bersama oleh seluruh pihak Apabila terjadi perselisihan maka masalah tersebut akan diselesaikan secara kekeluargaan terlebih dahulu Jika permasalahan masih berlanjut maka akan dilanjutkan secara hukum Jakarta 27 November 2020 Pihak Pertama Pihak Kedua Pihak Ketiga Arnold Widjaja Christian Putranto Islamiyah Citra" \
  2>&1 | head -c 3000
