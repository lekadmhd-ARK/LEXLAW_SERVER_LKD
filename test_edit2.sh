#!/bin/bash
cd /home/lekadmhd/project/LAWLEX_v2
echo "=== ISI EDIT PAGE (bagian error) ==="
grep -oP '(?<=<pre>)[^<]{0,200}' /tmp/edit.html | head -5
echo "=== CARI ERROR TEXT ==="
grep -oiP '(view \[[^\]]+\] not found|Undefined variable|Call to a member|Class [^ ]+ not found|syntax error)[^<]{0,120}' /tmp/edit.html | head -5
echo "=== FILE VIEW EDIT ==="
ls -la resources/views/regulations/edit.blade.php
echo "=== KONTEN AWAL ==="
head -30 resources/views/regulations/edit.blade.php