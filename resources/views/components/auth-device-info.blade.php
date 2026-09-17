<script>
// Best-effort: kumpulkan IP lokal via WebRTC lalu laporkan ke server.
// Real MAC tidak terekspos browser dari web publik; hanya terisi bila server
// berada di LAN yang sama (server resolve via ARP). Tidak memblokir apa pun.
(function () {
    try {
        var token = document.querySelector('input[name="_token"]');
        var endpoint = @json(route('auth.device-info'));
        if (!endpoint || !token) return;
        var ips = [];
        var sent = false;
        var send = function () {
            if (sent) return;
            sent = true;
            try {
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token.value
                    },
                    body: JSON.stringify({ local_ips: ips })
                }).catch(function () {});
            } catch (e) {}
        };
        var RTCPeerConnection = window.RTCPeerConnection || window.webkitRTCPeerConnection;
        if (!RTCPeerConnection) return;
        var pc = new RTCPeerConnection();
        pc.createDataChannel('');
        pc.createOffer().then(function (o) { return pc.setLocalDescription(o); }).catch(send);
        pc.onicecandidate = function (e) {
            if (!e.candidate) { send(); return; }
            var m = /(\d{1,3}(?:\.\d{1,3}){3}|[0-9a-f]{1,4}(?::[0-9a-f:]+)+)/i.exec(e.candidate.candidate || '');
            if (m && ips.indexOf(m[1]) === -1) ips.push(m[1]);
        };
        setTimeout(send, 2000);
    } catch (e) {}
})();
</script>