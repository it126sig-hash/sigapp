<?php

namespace App\Controllers;

use App\Models\DivisiModel;
use App\Models\KaryawanModel;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Authorization\PermissionModel;
use Myth\Auth\Authorization\GroupModel;
use Myth\Auth\Password;
use Exception;

class Pengguna extends BaseController
{
    protected $divisiModel;
    protected $karyawanModel;
    protected $userModel;
    protected $groupModel;
    protected $permissionModel;

    protected $validation;
    function __construct()
    {
        $this->divisiModel = new DivisiModel();
        $this->karyawanModel = new KaryawanModel();
        $this->userModel = new UserModel();
        $this->groupModel = new GroupModel();
        $this->permissionModel = new PermissionModel();

        $this->validation =  \Config\Services::validation();
    }
    // public function index()
    // {
    //     // var_dump(session('username'));die();
    //     if (session('logged_in'))
    //         return redirect()->to(base_url('siteplan'));

    //     $data['content'] = '';
    //     return view('user/login', $data);
    // }





    ################ setuser karyawan ############
    function setuser()
    {

        $data['data']['karyawan'] = $this->karyawanModel
            ->where('id_user', null)
            ->orWhere('id_user', "")
            ->findAll();

        $data['content'] = 'user/setuser';

        $data['data']['controller'] = 'pengguna';
        $data['data']['title'] = 'Data Pengguna';

        return view('template', $data);
    }
    public function getAll()
    {
        $data['data'] = array();
        $data['token'] = csrf_hash();

        $result = $this->userModel
            ->select('users.*, karyawan.nik, karyawan.nama_karyawan, karyawan.status, auth_groups.name as divisi, auth_permissions.name as level')
            ->join('karyawan', 'karyawan.id_user = users.id')
            ->join('auth_groups', 'auth_groups.id = karyawan.id_divisi')
            ->join('auth_permissions', 'auth_permissions.id = karyawan.id_level')
            ->findAll();

        foreach ($result as $key => $value) {

            $st = ($value->active == true) ? 1 : 0;

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-sm btn-info" onclick="edit(' . $value->id . ')"><i class="fa fa-edit"></i></button>';

            if ($value->active == 1) {
                $status = '<span class="badge badge-pill badge-light-primary mr-1">Aktif</span>';
                $ops .= '	<button type="button" class="btn btn-sm btn-danger" onclick="remove(' . $value->id . ', ' . $st . ', \'' . $value->username . '\')"><i class="fa fa-times"></i></button>';
            } else {
                $status = '<span class="badge badge-pill badge-light-warning mr-1">Tidak Aktif</span>';
                $ops .= '	<button type="button" class="btn btn-sm btn-success" onclick="remove(' . $value->id . ', ' . $st . ', \'' . $value->username . '\')"><i class="fa fa-check"></i></button>';
            }

            $ops .= '</div>';

            $data['data'][$key] = array(
                $value->id_user,
                $value->username,
                $value->nik,
                $value->nama_karyawan,
                $value->divisi,
                $value->level,
                $status,
                $ops,
            );
        }

        return $this->response->setJSON($data);
    }

    function changeGroup($userId, $groupId)
    {
        $this->groupModel->removeUserFromAllGroups(intval($userId));
        $this->groupModel->addUserToGroup(intval($userId), intval($groupId));
    }
    function chnagePermission($userId, $permissionId)
    {
        $this->permissionModel->removePermissionFromUser(intval($permissionId), intval($userId));
        $this->permissionModel->addPermissionToUser(intval($permissionId), intval($userId));
    }
    public function getOne()
    {

        $id = $this->request->getPost('id_user');

        if ($this->validation->check($id, 'required|numeric')) {

            $data = $this->userModel
                ->select('users.*, karyawan.nik, karyawan.nama_karyawan, karyawan.status, auth_groups.name as divisi, auth_permissions.name as level')
                ->join('karyawan', 'karyawan.id_user = users.id')
                ->join('auth_groups', 'auth_groups.id = karyawan.id_divisi')
                ->join('auth_permissions', 'auth_permissions.id = karyawan.id_level')
                ->where('users.id', $id)
                ->first();

            $data->token = csrf_hash();

            return $this->response->setJSON($data);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
    public function edit_user()
    {
        $response['token'] = csrf_hash();

        $password = 'permit_empty|max_length[255]';
        if ($this->request->getPost('password') != '' || $this->request->getPost('password') != null) {
            $fields['password'] = $this->request->getPost('password');
            $password = 'min_length[4]|max_length[50]';
        }



        $id = $this->request->getPost('id');
        $username = $this->request->getPost('username');
        $fields['active'] = $this->request->getPost('active');

        $fields['updated_at'] = date('Y-m-d H:i:s');

        //bypass username validation if 
        $u = 'required|min_length[4]|max_length[20]|is_unique[users.username]';
        $c = $this->userModel->where('id', $id)->first();



        //get karyawan data
        $k = $this->karyawanModel->where('nik', $this->request->getPost('nik'))->first();


        $valid = [
            // 'id' => ['label' => 'ID', 'rules' => 'permit_empty|max_length[255]'],
            'password' => [
                'rules' => $password,
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 50 Karakter',
                ]
            ],
            'active' => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]']
        ];

        if ($c->username != $username) {
            $fields['username'] = $username;
            $valid['username'] = [

                'rules' => $u,
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 20 Karakter',
                    'is_unique' => 'Username sudah digunakan sebelumnya'
                ]

            ];
        };

        $this->validation->setRules($valid);
      
        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {
            //encrypt password
            $fields['password_hash'] = Password::hash($this->request->getVar('password'));

            if ($this->userModel->update($id, $fields)) {

                //cahnge group/divisi
                $this->changeGroup($id, $k->id_divisi);
                $this->chnagePermission($id, $k->id_level);

                $response['success'] = true;
                $response['messages'] = 'Successfully updated';
            } else {

                $response['success'] = false;
                $response['messages'] = 'Update error!';
            }
        }

        return $this->response->setJSON($response);
    }


    public function add()
    {

        $response = array();
        $response['token'] = csrf_hash();

        // $fields['id'] = $this->request->getPost('id');
        $fields['username'] = $this->request->getPost('username');
        $fields['email'] = $this->request->getPost('email');
        $fields['password_hash'] = $this->request->getPost('password');
        $fields['active'] = $this->request->getPost('active');

        $fields['created_at'] = date('Y-m-d H:i:s');

        $nik = $this->request->getPost('nik');
        //get karyawan data
        $k = $this->karyawanModel->where('nik', $this->request->getPost('nik'))->first();

        // var_dump($fields);die();


        $this->validation->setRules([
            'username' => [
                'rules' => 'required|min_length[4]|max_length[20]|is_unique[users.username]',
                'errors' => [
                    'required' => '{field} Harus diisi',
                    'min_length' => '{field} Minimal 4 Karakter',
                    'max_length' => '{field} Maksimal 20 Karakter',
                    'is_unique' => 'Username sudah digunakan sebelumnya'
                ]
            ],
            // 'password' => [
            //     'rules' => 'required|min_length[4]|max_length[50]',
            //     'errors' => [
            //         'required' => '{field} Harus diisi',
            //         'min_length' => '{field} Minimal 4 Karakter',
            //         'max_length' => '{field} Maksimal 50 Karakter',
            //     ]
            // ],
            'active' => ['label' => 'Status', 'rules' => 'permit_empty|max_length[255]']
        ]);

        if ($this->validation->run($fields) == FALSE) {
            $response['success'] = false;
            $response['messages'] = $this->validation->listErrors();
        } else {

            //encrypt password
            $fields['password_hash'] = Password::hash($this->request->getVar('password'));

            // var_dump($fields);
            if ($this->userModel->insert($fields)) {

                //update id_user di tb karyawan
                $id_user = $this->userModel->getInsertID();
                $this->karyawanModel->update($nik, ['id_user' => $id_user]);

                //cahnge/add group/divisi permission/level
                $this->changeGroup($id_user, $k->id_divisi);
                $this->chnagePermission($id_user, $k->id_level);

                $response['success'] = true;
                $response['messages'] = 'Data has been inserted successfully';
            } else {

                $response['success'] = false;
                $response['messages'] = 'Insertion error!';
            }
        }

        return $this->response->setJSON($response);
    }


    public function remove()
    {
        $response = array();
        $response['token'] = csrf_hash();

        $st = $this->request->getPost('active');
        $f['id'] = $this->request->getPost('id');
        $f['active'] = ($st == 1) ? 0 : 1;

        if ($this->userModel->update($f['id'], $f)) {

            $response['success'] = true;
            $response['messages'] = 'Berhasil diubah';
        } else {

            $response['success'] = false;
            $response['messages'] = 'Update error!';
        }

        return $this->response->setJSON($response);
    }
}
