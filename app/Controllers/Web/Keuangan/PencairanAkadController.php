<?php

namespace App\Controllers\Web\Keuangan;

use App\Controllers\BaseController;
use App\Services\Keuangan\PencairanAkadService;

class PencairanAkadController extends BaseController
{
    protected PencairanAkadService $pencairanAkadService;

    public function __construct(?PencairanAkadService $pencairanAkadService = null)
    {
        $this->pencairanAkadService = $pencairanAkadService ?? new PencairanAkadService();
    }

    public function listHasilAkad()
    {
        $data['content'] = 'keuangan/list-hasil-akad';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'List Hasil Akad';

        return view('template', $data);
    }

    public function importForm()
    {
        if (function_exists('in_groups') && ! in_groups(['1', '3'])) {
            return redirect()->to(base_url('keuangan/hasil-akad/list'))->with('error', 'Akses ditolak: Hanya user Keuangan yang dapat mengakses import pencairan.');
        }

        $data['content'] = 'keuangan/pencairan-akad-import';
        $data['data']['controller'] = 'Keuangan';
        $data['data']['title'] = 'Import Tanggal Cair Pencairan Akad';

        return view('template', $data);
    }

    public function exportTemplate()
    {
        $rows = $this->pencairanAkadService->exportTemplate([
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
}
