<?php
namespace App\Models;
use CodeIgniter\Model;
class BpbSignatureModel extends Model
{
    protected $table = 'bpb_signatures';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['bpb_id', 'role', 'signer_user_id', 'signer_name', 'signer_department', 'signer_level', 'method', 'signature_path', 'document_hash', 'ip_address', 'user_agent', 'signed_at', 'revoked_at', 'revoked_by', 'revoked_reason'];
}
