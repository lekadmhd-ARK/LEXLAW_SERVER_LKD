<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class MonitorLexlaw extends Command
{
    protected $signature = 'lexlaw:monitor';

    protected $description = 'Cek kesehatan aplikasi & kedaluwarsa backup off-site, kirim alert jika terjadi masalah.';

    public function handle(): int
    {
        $alerts = [];
        $appUrl = rtrim(config('app.url'), '/');

        try {
            $resp = Http::timeout(15)->get($appUrl . '/up');
            if (!$resp->ok()) {
                $alerts[] = 'Endpoint /up merespons HTTP ' . $resp->status() . ' (diharapkan 200).';
            }
        } catch (\Throwable $e) {
            $alerts[] = 'Endpoint /up tidak dapat dijangkau: ' . $e->getMessage();
        }

        try {
            $resp = Http::timeout(15)->get($appUrl . '/login');
            if ($resp->status() !== 200) {
                $alerts[] = 'Endpoint /login merespons HTTP ' . $resp->status() . ' (diharapkan 200).';
            }
        } catch (\Throwable $e) {
            $alerts[] = 'Endpoint /login tidak dapat dijangkau: ' . $e->getMessage();
        }

        $backupDir = env('BACKUP_DIR', '/home/lekadmhd/backups/lawlex/db');
        if (is_dir($backupDir)) {
            $dumps = glob($backupDir . '/*.dump');
            if (empty($dumps)) {
                $alerts[] = 'Tidak ada dump database di ' . $backupDir . '.';
            } else {
                $latest = max(array_map('filemtime', $dumps));
                $age = Carbon::now()->diffInMinutes(Carbon::createFromTimestamp($latest));
                if ($age > 60 * 26) {
                    $alerts[] = 'Dump database terakhir berusia ' . round($age / 60) . ' jam (lebih dari 26 jam). Backup mungkin gagal.';
                }
            }
        } else {
            $alerts[] = 'Direktori backup ' . $backupDir . ' tidak ditemukan.';
        }

        $stateFile = storage_path('app/monitor-state.json');
        $now = Carbon::now();
        $lastAlertAt = null;

        if (file_exists($stateFile)) {
            $state = json_decode((string) file_get_contents($stateFile), true);
            $lastAlertAt = $state['last_alert_at'] ?? null;
        }

        if ($alerts) {
            $deadline = $lastAlertAt
                ? Carbon::parse($lastAlertAt)->addHours(4)
                : null;

            if (!$deadline || $now->gt($deadline)) {
                try {
                    $recipient = env('MONITOR_ALERT_TO', 'support@lexlaw.arktech.id');
                    Mail::raw(
                        "LEXLAW MONITORING ALERT — " . $now->format('d M Y H:i') . "\n\n" . implode("\n", $alerts),
                        function ($m) use ($recipient) {
                            $m->to($recipient)->subject('[LEXLAW] Monitoring Alert');
                        }
                    );
                } catch (\Throwable $e) {
                    $alerts[] = 'Gagal mengirim email alert: ' . $e->getMessage();
                }

                $this->error('Alert dikirim: ' . implode(' | ', $alerts));
                file_put_contents($stateFile, json_encode(['last_alert_at' => $now->toDateTimeString(), 'alerts' => $alerts]));
            } else {
                $this->warn('Masalah terdeteksi (alert pending dalam jendela 4 jam): ' . implode(' | ', $alerts));
            }
        } else {
            if (file_exists($stateFile)) {
                file_put_contents($stateFile, json_encode(['last_alert_at' => null, 'alerts' => []]));
            }
            $this->info('Semua pengecekan OK.');
        }

        return self::SUCCESS;
    }
}