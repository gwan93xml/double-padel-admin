<?php

namespace App\Models;

use App\Traits\AuditTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements AuditableContract
{

    public const USER_TYPE_ADMIN = 'admin';
    public const USER_TYPE_MEMBER = 'member';
    
    public const USER_TYPES = [
        self::USER_TYPE_ADMIN,
        self::USER_TYPE_MEMBER,
    ];
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, AuditTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'entry_by',
        'entry_at',
        'last_edit_by',
        'last_edit_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function whitelistIp()
    {
        return $this->hasOne(WhitelistIp::class);
    }

    public function scopeAdmin()
    {
        return $this->where('user_type', 'admin');
    }

    public function scopeMember()
    {
        return $this->where('user_type', 'member');
    }
}
