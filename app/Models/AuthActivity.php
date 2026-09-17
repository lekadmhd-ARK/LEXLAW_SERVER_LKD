<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthActivity extends Model
{
    protected $fillable = [
        'user_id', 'event', 'email', 'ip_address', 'mac_address', 'local_ip', 'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mencatat aktivitas auth (register/login/login_failed/logout).
     */
    public static function record(
        string $event,
        ?User $user = null,
        ?string $email = null,
        ?string $ip = null,
        ?string $userAgent = null,
    ): self {
        return static::create([
            'user_id' => $user?->id,
            'event' => $event,
            'email' => $email ?? $user?->email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}