<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as OwenItAudit;

class Audit extends OwenItAudit
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
        'delete_reason',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the delete reason attribute.
     */
    public function getDeleteReasonAttribute($value)
    {
        return $value;
    }

    /**
     * Set the delete reason attribute.
     */
    public function setDeleteReasonAttribute($value)
    {
        $this->attributes['delete_reason'] = $value;
    }
}
