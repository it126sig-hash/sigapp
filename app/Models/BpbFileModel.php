<?php
namespace App\Models;
use CodeIgniter\Model;
class BpbFileModel extends Model
{
    protected $table = 'bpb_files';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['bpb_id', 'category', 'logical_path', 'original_name', 'mime_type', 'file_size', 'file_sha256', 'uploaded_by', 'created_at'];
}
