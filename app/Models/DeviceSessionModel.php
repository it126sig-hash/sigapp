<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceSessionModel extends Model
{
    protected $table = 'auth_device_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id', 'token_hash', 'remember_selector', 'user_agent', 'device_label', 'browser',
        'platform', 'ip_address', 'created_at', 'last_seen_at', 'expires_at', 'revoked_at',
        'revoked_by', 'revoke_reason',
    ];
}
