<?php

namespace App\Controllers\Api\Proyek;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Proyek\ProyekService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProyekController extends BaseApiController
{
    protected ProyekService $proyekService;
    protected ActiveProyekService $activeProyekService;

    public function __construct()
    {
        $this->proyekService       = new ProyekService();
        $this->activeProyekService = new ActiveProyekService();
    }

    public function getAll()
    {
        return $this->respond([
            'data'  => $this->proyekService->getAll((array) $this->request->getVar()),
            'token' => csrf_hash(),
        ]);
    }

    public function getOne()
    {
        $id = (int) $this->request->getPost('id_proyek');

        if ($id <= 0) {
            throw new PageNotFoundException();
        }

        $data = $this->proyekService->getOne($id);
        if (!$data) {
            throw new PageNotFoundException();
        }

        $data->token = csrf_hash();

        return $this->respond($data);
    }

    public function add()
    {
        $result = $this->proyekService->add($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function edit($id = null)
    {
        $result = $this->proyekService->edit($this->request);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getSiteplanList($id = null)
    {
        $idProyek = (int) ($id ?: $this->request->getVar('idProyek'));

        return $this->proyekService->getSiteplanList($idProyek);
    }

    public function remove()
    {
        $id = (int) $this->request->getPost('id_proyek');
        $result = $this->proyekService->remove($id);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }

    public function getAccessible()
    {
        $list     = $this->activeProyekService->getAccessibleList((int) user_id());
        $activeId = $this->activeProyekService->getActiveId();

        return $this->respond([
            'success'   => true,
            'active_id' => $activeId,
            'data'      => array_map(static function ($row) {
                return [
                    'id_proyek'       => (int) $row->id_proyek,
                    'nama_proyek'     => $row->nama_proyek,
                    'logo_access_url' => $row->logo_access_url ?? '',
                ];
            }, $list),
            'token'     => csrf_hash(),
        ]);
    }

    public function setActive()
    {
        $idProyek = (int) $this->request->getPost('id_proyek');
        $result   = $this->activeProyekService->setActive($idProyek);
        $result['token'] = csrf_hash();

        return $this->respond($result);
    }
}
