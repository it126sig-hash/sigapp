<?php

namespace App\Controllers\Api\Siteplan;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ProyekService;
use App\Services\SiteplanDataService;
use Config\Database;

class SiteplanController extends BaseApiController
{
    protected SiteplanDataService $siteplanDataService;
    protected ProyekService $proyekService;

    public function __construct()
    {
        $this->siteplanDataService = new SiteplanDataService();
        $this->proyekService       = new ProyekService();
    }

    /**
     * Mendapatkan daftar kavling beserta visual_rows dan visual_fill untuk canvas siteplan.
     */
    public function kavling()
    {
        $params = (array) $this->request->getVar();

        // Fallback id_proyek dari header X-Project-Id jika tidak ada di query/body
        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (!empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        if (empty($params['id_proyek'])) {
            return $this->error('Parameter id_proyek wajib diisi', 400);
        }

        // Default id_role = 0 agar SiteplanDataService memicu SiteplanVisualStatusService::appendVisualRows
        if (!isset($params['id_role'])) {
            $params['id_role'] = 0;
        }

        $result = $this->siteplanDataService->getKavlings($params);

        return $this->success($result);
    }

    /**
     * Mendapatkan detail proyek dan siteplan_access_url.
     */
    public function proyek($id = null)
    {
        $idProyek = (int) ($id ?: $this->request->getVar('id_proyek') ?: $this->request->header('X-Project-Id')?->getValue());

        if ($idProyek <= 0) {
            return $this->error('ID Proyek tidak valid', 400);
        }

        $proyek = $this->proyekService->getOne($idProyek);

        if (!$proyek) {
            return $this->error('Data proyek tidak ditemukan', 404);
        }

        return $this->success($proyek);
    }

    /**
     * Mendapatkan map konfigurasi shape dari database config_shape.
     */
    public function config()
    {
        $db = Database::connect();
        $rows = $db->table('config_shape')->get()->getResult();
        $map = [];

        foreach ($rows as $row) {
            if (!isset($row->config_name)) {
                continue;
            }

            $map[$row->config_name] = [
                'fill'        => $row->fill ?? null,
                'stroke'      => $row->stroke ?? null,
                'strokeWidth' => $row->strokeWidth ?? null,
                'dashed'      => $row->dashed ?? null,
                'keterangan'  => $row->keterangan ?? null,
            ];
        }

        return $this->success($map);
    }

    /**
     * Mendapatkan data fasilitas/fasum siteplan.
     */
    public function others()
    {
        $params = (array) $this->request->getVar();

        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (!empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        $result = $this->siteplanDataService->getOthers($params);

        return $this->success($result);
    }
}
