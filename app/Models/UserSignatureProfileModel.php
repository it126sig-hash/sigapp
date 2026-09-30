<?php
namespace App\Models;
use CodeIgniter\Model;
class UserSignatureProfileModel extends Model
{
    protected $table = 'user_signature_profiles';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['user_id', 'signature_path', 'updated_at'];
}
