<script>
// Best-effort device intel: fakta perangkat dibundel menjadi fingerprint hash,
// IP lokal via WebRTC, lalu dilaporkan ke server (sekali per tab).
// Real MAC tidak terekspos browser dari web publik — hanya terisi bila server
// di LAN sama (server resolve via ARP). Tidak memblokir apa pun.
(function () {
    try {
        var endpoint = @json(route('auth.device-info'));
        var token = document.querySelector('meta[name="csrf-token"]');
        if (!endpoint || !token) return;
        if (sessionStorage.getItem('ll_device_sent')) return;

        function fnv1a(str) {
            var h = 2166136261, i;
            for (i = 0; i < str.length; i++) {
                h ^= str.charCodeAt(i);
                h = Math.imul(h, 16777619);
            }
            return ('0000000' + (h >>> 0).toString(16)).slice(-8);
        }
        function canvasSig() {
            try {
                var c = document.createElement('canvas');
                c.width = 260; c.height = 70;
                var ctx = c.getContext('2d');
                ctx.textBaseline = 'top'; ctx.font = '14px Arial';
                ctx.fillStyle = '#f60'; ctx.fillRect(0, 0, 260, 70);
                ctx.fillStyle = '#069';
                ctx.fillText('LEXLAW\u00b7' + (navigator.vendor || '') + new Date().getTimezoneOffset(), 2, 15);
                ctx.fillStyle = 'rgba(102,204,0,.7)';
                ctx.beginPath(); ctx.arc(120, 38, 22, 0, 2 * Math.PI); ctx.fill();
                ctx.fillStyle = '#069'; ctx.font = '12px Arial';
                ctx.fillText('fp', 10, 44);
                return c.toDataURL();
            } catch (e) { return 'x'; }
        }
        function webglSig() {
            try {
                var cv = document.createElement('canvas');
                var gl = cv.getContext('webgl') || cv.getContext('experimental-webgl');
                if (!gl) return '';
                var ext = gl.getExtension('WEBGL_debug_renderer_info');
                return (gl.getParameter(gl.RENDERER) || '') + '|' +
                       ((ext && gl.getParameter(ext.UNMASKED_RENDERER_WEBGL)) || '');
            } catch (e) { return ''; }
        }

        var parts = [
            navigator.userAgent, navigator.platform || '', navigator.language || '',
            (navigator.languages || []).join(','),
            String(screen.width) + 'x' + String(screen.height) + 'x' + String(screen.colorDepth || 24),
            String(window.devicePixelRatio || 1),
            (Intl.DateTimeFormat && Intl.DateTimeFormat().resolvedOptions().timeZone) || '',
            String(navigator.hardwareConcurrency || 0),
            String(navigator.deviceMemory || 0),
            canvasSig(), webglSig()
        ];
        var joined = parts.join('|');
        var fingerprint = fnv1a(joined) + fnv1a(joined.split('').reverse().join(''));

        var ips = [];
        var sent = false;
        var send = function (payload) {
            if (sent) return;
            sent = true;
            try {
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                }).then(function (r) {
                    if (r.status === 200) sessionStorage.setItem('ll_device_sent', '1');
                }).catch(function () {});
            } catch (e) {}
        };
        var RTCPeerConnection = window.RTCPeerConnection || window.webkitRTCPeerConnection;
        if (RTCPeerConnection) {
            var pc = new RTCPeerConnection();
            pc.createDataChannel('');
            pc.createOffer().then(function (o) { return pc.setLocalDescription(o); }).catch(function () { send({ fingerprint: fingerprint, local_ips: ips }); });
            pc.onicecandidate = function (e) {
                if (!e.candidate) { send({ fingerprint: fingerprint, local_ips: ips }); return; }
                var m = /(\d{1,3}(?:\.\d{1,3}){3}|[0-9a-f]{1,4}(?::[0-9a-f:]+)+)/i.exec(e.candidate.candidate || '');
                if (m && ips.indexOf(m[1]) === -1) ips.push(m[1]);
            };
            setTimeout(function () { send({ fingerprint: fingerprint, local_ips: ips }); }, 2500);
        } else {
            send({ fingerprint: fingerprint, local_ips: ips });
        }
    } catch (e) {}
})();
</script>