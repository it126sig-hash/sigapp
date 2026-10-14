<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\PencairanAkadService;

class PencairanAkad extends BaseController
{
    protected PencairanAkadService $service;

    public function __construct()
    {
        $this->service = new PencairanAkadService();
    }

    protected function checkKeuanganAccess(): ?\CodeIgniter\HTTP\ResponseInterface
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return $this->response->setStatusCode(403)->setJSON([
                'token' => csrf_hash(),
                'success' => false,
                'message' => 'Akses ditolak: Hanya user Keuangan atau Administrator yang dapat memproses atau memperbarui pencairan dana.'
            ]);
        }
        return null;
    }

    public function get()
    {
        return $this->response->setJSON(
            $this->service->getData(
                (int) $this->request->getVar('id_mkdt'),
                (int) $this->request->getVar('id_kavling')
            )
        );
    }

    public function saveRetensi()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->saveRetensi($this->request->getPost(), (int) user_id())
        );
    }

    public function saveTenor()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->saveTenor($this->request->getPost(), (int) user_id())
        );
    }

    public function storePengajuan()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->storePengajuan($this->request, (int) user_id())
        );
    }

    public function cairkan()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->cairkan($this->request, (int) user_id())
        );
    }

    public function listPayment()
    {
        return $this->response->setJSON(
            $this->service->listPayment((int) $this->request->getPost('id_pengajuan'))
        );
    }

    public function voidPayment()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->voidPayment(
                (int) $this->request->getPost('id_payment'),
                (string) $this->request->getPost('reason'),
                (int) user_id()
            )
        );
    }

    public function void()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->void(
                (int) $this->request->getPost('id_pengajuan'),
                (string) $this->request->getPost('reason'),
                (int) user_id()
            )
        );
    }

    public function history($id_kavling)
    {
        return $this->response->setJSON(
            $this->service->getHistory((int) $id_kavling)
        );
    }

    public function listHasilAkad()
    {
        $data['content'] = 'keuangan/list-hasil-akad';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'List Hasil Akad';

        return view('template', $data);
    }

    public function getListGrouped()
    {
        return $this->service->getListGrouped($this->request);
    }

    public function getListDetail()
    {
        $idMkdt = (int) $this->request->getPost('id_mkdt');

        return $this->response->setJSON($this->service->getListDetail($idMkdt));
    }

    public function exportTemplate()
    {
        $rows = $this->service->exportTemplate([
            'id_kavling' => $this->request->getVar('id_kavling'),
            'id_proyek' => $this->request->getVar('id_proyek'),
        ]);

        $columns = PencairanAkadService::templateColumns();

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $columns);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($c) => $row[$c] ?? '', $columns));
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response->download('pencairan_akad_template_' . date('Ymd_His') . '.csv', $csv);
    }

    public function importForm()
    {
        if (function_exists('in_groups') && !in_groups(['1', '3'])) {
            return redirect()->to(base_url('keuangan/hasil-akad/list'))->with('error', 'Akses ditolak: Hanya user Keuangan yang dapat mengakses import pencairan.');
        }

        $data['content'] = 'keuangan/pencairan-akad-import';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'Import Tanggal Cair Pencairan Akad';

        return view('template', $data);
    }

    public function import()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'message' => 'File CSV tidak valid']);
        }

        return $this->response->setJSON(
            $this->service->importTanggalCair($file->getTempName(), $this->request->getFile('lampiran_surat'), (int) user_id())
        );
    }

    public function postBatchList()
    {
        return $this->service->getBatchList($this->request);
    }

    public function postEligibleKavling()
    {
        $idKavlings = (array) $this->request->getPost('id_kavlings');
        $idProyek = (int) $this->request->getPost('id_proyek');
        return $this->response->setJSON(
            $this->service->getEligibleBatchKavling($idKavlings, $idProyek)
        );
    }

    public function postStoreBatchPengajuan()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->storeBatchPengajuan(
                $this->request->getPost(),
                $this->request->getFile('lampiran_surat'),
                (int) user_id()
            )
        );
    }

    public function postBatchDetail()
    {
        $idBatch = (int) $this->request->getPost('id_batch');
        return $this->response->setJSON(
            $this->service->getBatchDetail($idBatch)
        );
    }

    public function postCairkanBatch()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        return $this->response->setJSON(
            $this->service->cairkanBatch($this->request->getPost(), (int) user_id())
        );
    }

    public function postVoidBatch()
    {
        if ($denied = $this->checkKeuanganAccess()) return $denied;

        $idBatch = (int) $this->request->getPost('id_batch');
        $reason = (string) $this->request->getPost('reason');
        return $this->response->setJSON(
            $this->service->voidBatch($idBatch, $reason, (int) user_id())
        );
    }

    public function getBatchHistory($id)
    {
        return $this->response->setJSON(
            $this->service->getBatchHistory((int) $id)
        );
    }
}

