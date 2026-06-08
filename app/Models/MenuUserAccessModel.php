<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuUserAccessModel extends Model
{
    protected $table = 'menu_user_access';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['id_user', 'id_menu', 'access_type', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $skipValidation = true;
}
