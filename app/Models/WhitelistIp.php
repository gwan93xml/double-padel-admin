<?php

namespace App\Models;

use App\Traits\AuditTrait;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WhitelistIp extends Model implements Auditable
{
    use AuditTrait, SoftDeletes, Sortable;

    protected $sortable = [
        'user.name',
        'ip_addresses',
    ];

    protected $fillable = [
        'user_id',
        'ip_addresses'
    ];

    protected $casts = [
        'ip_addresses' => 'array'
    ];

    public const RULES = [
        'user_id' => 'required|exists:users,id',
        'ip_addresses' => 'required|array|min:1',
    ];

    public const MESSAGES = [
        'user_id.required' => 'User wajib diisi',
        'user_id.exists' => 'User tidak ditemukan',
        'ip_addresses.required' => 'Alamat IP wajib diisi',
        'ip_addresses.array' => 'Alamat IP harus berupa array',
        'ip_addresses.min' => 'Alamat IP minimal 1',
        'ip_addresses.*.required' => 'Alamat IP wajib diisi',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
