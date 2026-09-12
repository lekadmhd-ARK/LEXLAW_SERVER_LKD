cd /home/lekadmhd/project/LAWLEX_v2
sed -i "s/\[.index., .create., .store., .update.\]/['index', 'create', 'store', 'update']/g" routes/web.php
sed -i "s/\[.index., .store., .update.\]/['index', 'store', 'update']/g" routes/web.php
sed -i 's/\.index\./index/g; s/\.create\./create/g; s/\.store\./store/g; s/\.update\./update/g' routes/web.php
