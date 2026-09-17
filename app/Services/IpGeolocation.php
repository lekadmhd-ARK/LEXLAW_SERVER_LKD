<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Geolokasi IP server-side (silent, tanpa prompt browser) via ipwho.is.
 * Free & tanpa API key; HTTPS; response: country/region/city/lat/lon/isp.
 * Selalu dikembalikan array kosong saat gagal — tidak pernah melempar.
 */
class IpGeolocation
{
    public static function lookup(?string $ip): array
    {
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return [];
        }

        if (in_array($ip, ['127.0.0.1', '::1'], true)) {
            return [];
        }

        try {
            $r = Http::timeout(4)->withHeaders(['User-Agent' => 'LEXLAW/1.0'])
                ->get('https://ipwho.is/' . rawurlencode($ip));

            if (!$r->ok() || $r->json('success') !== true) {
                return [];
            }

            return [
                'geo_country' => mb_substr(trim((string) ($r->json('country') ?? '')), 0, 64),
                'geo_region' => mb_substr(trim((string) ($r->json('region') ?? '')), 0, 96),
                'geo_city' => mb_substr(trim((string) ($r->json('city') ?? '')), 0, 96),
                'geo_lat' => isset($r['latitude']) ? round((float) $r['latitude'], 6) : null,
                'geo_lon' => isset($r['longitude']) ? round((float) $r['longitude'], 6) : null,
                'geo_isp' => mb_substr(trim((string) ($r->json('connection.isp') ?? '')), 0, 128),
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}