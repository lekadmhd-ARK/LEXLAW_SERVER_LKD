<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'company_id', 'role', 'tenant_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'company_id', 'role', 'tenant_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tenant_id' => 'string',
        ];
    }

    public static function roleEnum(): array
    {
        return ['owner' => 'Owner', 'admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer'];
    }

    public function getRoleDisplay(): ?string
    {
        return static::roleEnum()[$this->role] ?? $this->role;
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'editor']);
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
