#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
echo "=== TABLE DETAIL / METADATA BPK ==="
grep -oiP '<td[^>]*>\K[^<]+' /tmp/bpk.html | head -60
echo "=== NOMOR & TAHUN ==="
grep -oiP '(Nomor|Tahun|Tanggal Penetapan|Tanggal Pengundangan|Status)[^<]{0,80}' /tmp/bpk.html | head -20
echo "=== METADATA META TAG ==="
grep -oiP '<meta[^>]*(og:title|og:description|description)[^>]*>' /tmp/bpk.html | head -10