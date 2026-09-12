<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Billing2Controller extends Controller
{
    public function __invoke(Request $request)
    {
        $company = $request->user()->company ?? null;
        return view('billing.qris2', [
            'company' => $company,
            'amount' => null,
            'payloadDynamic' => null,
            'qrCode' => null,
            'orderId' => null,
        ]);
    }

    public function makeDynamic(Request $request)
    {
        $validated = $request->validate([
            'nominal' => 'required|integer|min:1000',
        ]);

        $company = $request->user()->company ?? null;
        $amount = (int) $validated['nominal'];
        $orderId = 'LAWLEX-' . ($company->id ?? 'TEST') . '-' . time();

        // Payload static dari qris_ark.jpeg (decode zbarimg, CRC 1D31 valid)
        $payloadStatic = '00020101021126570011ID.DANA.WWW011893600915303464266502090346426650303UMI51440014ID.CO.QRIS.WWW0215ID10265821596320303UMI5204899953033605802ID5908Ark Mind6014Kab. Tangerang61051583363041D31';

        $payloadDynamic = $this->makeQrisDynamic($payloadStatic, $amount);

        return view('billing.qris2', [
            'company' => $company,
            'amount' => $amount,
            'payloadDynamic' => $payloadDynamic,
            'qrCode' => null,
            'orderId' => $orderId,
        ]);
    }

    private function makeQrisDynamic(string $payloadStatic, int $nominal): string
    {
        $payloadDyn = $payloadStatic;

        // 1. Ganti PIM tag 01 -> 02 (dinamis)
        $payloadDyn = str_replace('010211', '010212', $payloadDyn);

        // 2. Tag 54 (Transaction Amount) — format: 54 + len(2 digit) + nominal integer
        //    contoh: Rp 25.000 -> "540525000" (54, len=05, value=25000)
        $amountStr = (string) $nominal;
        $tag54 = '54' . str_pad(strlen($amountStr), 2, '0', STR_PAD_LEFT) . $amountStr;

        // 3. Cari tag 58 (5802ID) untuk sisipkan Tag 54 sebelum country code
        $idx58 = strpos($payloadDyn, '5802ID');
        if ($idx58 !== false) {
            $payloadDyn = substr($payloadDyn, 0, $idx58) . $tag54 . substr($payloadDyn, $idx58);
        } else {
            // fallback: sisipkan sebelum 6304
            $idx6304 = strpos($payloadDyn, '6304');
            if ($idx6304 !== false) {
                $payloadDyn = substr($payloadDyn, 0, $idx6304) . $tag54 . substr($payloadDyn, $idx6304);
            } else {
                $payloadDyn = $payloadDyn . $tag54;
            }
        }

        // 4. Hapus CRC lama (6304 + 4 hex digits di akhir)
        $payloadDyn = preg_replace('/6304[0-9A-Fa-f]{4}$/', '', $payloadDyn);

        // 5. Pastikan 6304 placeholder ada di akhir
        if (substr($payloadDyn, -4) !== '6304') {
            $payloadDyn .= '6304';
        }

        // 6. Hitung CRC16-CCITT atas seluruh payload (termasuk 6304)
        $crcInput = $payloadDyn;
        $crc = $this->crc16CCITT($crcInput);
        $crcHex = strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));

        // 7. Gabungkan: data + CRC + 6304
        return $crcInput . $crcHex;
    }

    private function crc16CCITT(string $data): int
    {
        $crc = 0xFFFF;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return $crc;
    }
}