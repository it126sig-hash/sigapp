<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuRoleModel extends Model
{
    protected $table = 'menu_roles';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id_groups', 'id_menu', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $skipValidation = true;
}
