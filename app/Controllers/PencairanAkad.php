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
        return $this->response->setJSON(
            $this->service->saveRetensi($this->request->getPost(), (int) user_id())
        );
    }

    public function saveTenor()
    {
        return $this->response->setJSON(
            $this->service->saveTenor($this->request->getPost(), (int) user_id())
        );
    }

    public function storePengajuan()
    {
        return $this->response->setJSON(
            $this->service->storePengajuan($this->request, (int) user_id())
        );
    }

    public function cairkan()
    {
        return $this->response->setJSON(
            $this->service->cairkan($this->request, (int) user_id())
        );
    }

    public function void()
    {
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
        $data['content'] = 'keuangan/pencairan-akad-import';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'Import Tanggal Cair Pencairan Akad';

        return view('template', $data);
    }

    public function import()
    {
        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'message' => 'File CSV tidak valid']);
        }

        return $this->response->setJSON(
            $this->service->importTanggalCair($file->getTempName(), $this->request->getFile('lampiran_surat'), (int) user_id())
        );
    }
}
