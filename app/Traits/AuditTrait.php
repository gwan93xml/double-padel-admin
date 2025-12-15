<?php

namespace App\Traits;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

trait AuditTrait
{
    use Auditable;

    /**
     * Transform the audit data before saving
     */
    public function transformAudit(array $data): array
    {
        // Add user information to audit data
        if (auth()->check()) {
            $data['user_id'] = auth()->id();
            $data['user_type'] = get_class(auth()->user());
        }

        // Add delete_reason if it exists
        if (isset($this->delete_reason)) {
            $data['delete_reason'] = $this->delete_reason;
        }

        return $data;
    }
}
