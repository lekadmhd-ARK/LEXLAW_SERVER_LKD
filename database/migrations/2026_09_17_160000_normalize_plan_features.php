<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plans')) {
            return;
        }

        $rows = DB::table('plans')->get(['slug', 'max_users', 'max_regulations', 'max_ai_queries', 'features']);

        foreach ($rows as $plan) {
            $current = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
            $current = is_array($current) ? $current : [];

            $users = (int) $plan->max_users;
            $regs  = (int) $plan->max_regulations;
            $ai    = (int) $plan->max_ai_queries;

            $replace = [
                '/^\d+(\.\d+)?\s*user/i'              => "{$users} user",
                '/^\d+(\.\d+)?\s*regulasi/i'          => "{$regs} regulasi",
                '/^\d+(\.\d+)?\s*AI query\/bulan/i'   => "{$ai} AI query/bulan",
            ];

            $out = [];
            foreach ($current as $feat) {
                $matched = false;
                foreach ($replace as $pat => $rep) {
                    if (is_string($feat) && preg_match($pat, $feat)) {
                        $out[] = $rep;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $out[] = $feat;
                }
            }

            foreach (["{$users} user", "{$regs} regulasi", "{$ai} AI query/bulan"] as $want) {
                if (!in_array($want, $out, true)) {
                    $out[] = $want;
                }
            }

            DB::table('plans')->where('slug', $plan->slug)->update([
                'features' => json_encode(array_values(array_unique($out))),
            ]);
        }
    }

    public function down(): void
    {
        // Data normalisasi tidak bisa di-rollback secara otomatis.
    }
};