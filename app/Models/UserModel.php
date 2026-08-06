<?php

namespace App\Models;

use Myth\Auth\Models\UserModel as MythUserModel;

class UserModel extends MythUserModel
{
    public function __construct()
    {
        parent::__construct();
        if (! in_array('name', $this->allowedFields, true)) {
            $this->allowedFields[] = 'name';
        }
        if (! in_array('profile_photo', $this->allowedFields, true)) {
            $this->allowedFields[] = 'profile_photo';
        }
    }
}
