<?php
namespace App\Models;
use CodeIgniter\Model;
class BpbHistoryModel extends Model
{
    protected $table = 'bpb_history';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['bpb_id', 'from_status', 'to_status', 'action', 'summary', 'metadata', 'actor_user_id', 'actor_name', 'created_at'];
}
