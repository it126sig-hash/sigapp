<?php

namespace App\Services;

use App\Models\ProduksiModel;
use App\Models\KavlingModel;
use App\Models\ChecklistWorkModel;
use App\Models\ChecklistSubItemModel;
use App\Repositories\ProduksiRepository;

class ProduksiService
{
    protected ProduksiRepository   $repo;
    protected ProduksiFileService  $fileService;
    protected NotifikasiService    $notifService;
    protected ProduksiModel        $produksiModel;
    protected KavlingModel         $kavlingModel;
    protected ChecklistWorkModel   $cwModel;
    protected ChecklistSubItemModel $siModel;

    public function __construct()
    {
        $this->repo          = new ProduksiRepository();
        $this->fileService   = new ProduksiFileService();
        $this->notifService  = new NotifikasiService();
        $this->produksiModel = new ProduksiModel();
        $this->kavlingModel  = new KavlingModel();
        $this->cwModel       = new ChecklistWorkModel();
        $this->siModel       = new ChecklistSubItemModel();
    }

    public function save(array $var, array $uploadedFiles, int $userId): array
    {
        $idKavling = (int) ($var['id_kavling'] ?? 0);
        $idProduksi = (int) ($var['id_produksi'] ?? 0);
        $oldProduksi = $idProduksi > 0 ? (array) ($this->produksiModel->find($idProduksi) ?? []) : [];

        // Physical file upload happens before transaction — cannot be rolled back
        $fotoRows = $this->fileService->uploadFotoGroups($uploadedFiles, $var, $idKavling);

        $f = $this->buildProduksiData($var, $userId);
        $historyPayload = $this->buildHistoryPayload($oldProduksi, $f, $var, $fotoRows);

        $notif    = 'Melakukan perubahan pada progres pembangunan = ' . ($var['progres_bangunan'] ?? '') . '%';
        $sumurbor = [
            'sumurbor'            => $var['sumurbor'] ?? null,
            'sumurbor_keterangan' => $var['sumurbor_keterangan'] ?? null,
            'sumurbor_tanggal'    => $var['sumurbor_tanggal'] ?? null,
            'sumurbor_oleh'       => $userId,
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        if (!empty($fotoRows)) {
            $this->repo->insertFiles($fotoRows);
        }

        $this->processChecklists($idKavling, $var, $userId);

        if (empty($f['id_produksi'])) {
            $f['add_by']     = $userId;
            $f['edit_by']    = $userId;
            $f['created_at'] = date('Y-m-d H:i:s');

            $this->produksiModel->insert($f);
            $idProduksi = (int) $this->produksiModel->getInsertID();
            $this->kavlingModel->update($idKavling, array_merge($sumurbor, [
                'id_produksi'      => $idProduksi,
                'sumurbor_updated' => date('Y-m-d H:i:s'),
            ]));
        } else {
            $f['edit_by']    = $userId;
            $f['updated_at'] = date('Y-m-d H:i:s');

            $this->produksiModel->update($f['id_produksi'], $f);
            $this->kavlingModel->update($idKavling, $sumurbor);
        }

        if ($this->repo->hasProduksiChangeHistoryTable() && $this->shouldWriteHistory($historyPayload)) {
            $this->repo->insertProduksiChangeHistory([
                'id_kavling'  => $idKavling,
                'id_produksi' => $idProduksi ?: null,
                'action'      => empty($oldProduksi) ? 'create' : 'update',
                'summary'     => $historyPayload['summary'],
                'old_data'    => json_encode($historyPayload['old_data']),
                'new_data'    => json_encode($historyPayload['new_data']),
                'files'       => json_encode($historyPayload['files']),
                'add_by'      => $userId,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'messages' => 'Terjadi Kesalahan'];
        }

        $this->notifService->tambah_notif('7;4;9', $notif, $userId, $idKavling, '');
        return ['success' => true, 'messages' => 'Successfully updated'];
    }

    protected function buildProduksiData(array $var, int $userId): array
    {
        $f = [];

        foreach ([
            'id_produksi', 'st_0', 'st_25', 'st_50', 'st_75', 'st_100',
            'bp', 'slo', 'lpa', 'lpa_tanggal', 'st_jalan', 'st_saluran', 'st_air',
            'air_jenis', 'listrik_jenis', 'progres_bangunan',
            'listrik_pln', 'listrik_disediakan_no', 'listrik_disediakan_tanggal',
            'air_deskripsi_unit', 'air_pdam_no',
        ] as $field) {
            $f[$field] = $var[$field] ?? null;
        }

        $f['keterangan'] = $var['produksi_keterangan'] ?? null;

        $tglBangun     = $var['tanggal_pembangunan'] ?? '';
        $tglBangunOld  = $var['tanggal_pembangunan_old'] ?? '';
        $tglRencana    = $var['tanggal_rencana_selesai_pembangunan'] ?? '';
        $tglRencanaOld = $var['tanggal_rencana_selesai_pembangunan_old'] ?? '';
        $tglSelesai    = $var['tanggal_selesai_pembangunan'] ?? '';
        $tglSelesaiOld = $var['tanggal_selesai_pembangunan_old'] ?? '';

        if (($tglBangun && $tglBangunOld === '') || ($tglRencana && $tglRencanaOld === '')) {
            $f['tanggal_pembangunan_oleh'] = $userId;
            $f['tanggal_pembangunan_pada'] = date('Y-m-d H:i:s');
        } elseif (($tglBangunOld !== '' && $tglBangun !== $tglBangunOld) || ($tglRencanaOld !== '' && $tglRencana !== $tglRencanaOld)) {
            $f['tanggal_pembangunan_diubah_oleh'] = $userId;
            $f['tanggal_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');
        }

        if ($tglSelesai && $tglSelesaiOld === '') {
            $f['tanggal_selesai_pembangunan_oleh'] = $userId;
            $f['tanggal_selesai_pembangunan_pada'] = date('Y-m-d H:i:s');
        } elseif ($tglSelesaiOld !== '' && $tglSelesai !== $tglSelesaiOld) {
            $f['tanggal_selesai_pembangunan_diubah_oleh'] = $userId;
            $f['tanggal_selesai_pembangunan_diubah_pada'] = date('Y-m-d H:i:s');
        }

        if ($tglBangun)  $f['tanggal_pembangunan'] = $tglBangun;
        if ($tglSelesai) $f['tanggal_selesai_pembangunan'] = $tglSelesai ?: null;
        if ($tglRencana) $f['tanggal_rencana_selesai_pembangunan'] = $tglRencana;

        return $f;
    }

    protected function processChecklists(int $idKavling, array $var, int $userId): void
    {
        $existingCw = $this->cwModel->select('id_subitem')->where('id_kavling', $idKavling)->findAll();
        $subitems   = $this->siModel->select('id_subitem')->findAll();

        foreach ($subitems as $s) {
            $hct = isset($var['hasil_cek_t'][$s->id_subitem]) ? 1 : 0;
            $hcf = isset($var['hasil_cek_f'][$s->id_subitem]) ? 1 : 0;
            $hcv = isset($var['hasil_cek_v'][$s->id_subitem]) ? 1 : 0;

            if ($hct === 0 && $hcf === 0 && $hcv === 0) {
                continue;
            }

            $row = [
                'produksi_cek'            => $userId,
                'produksi_cek_tgl'        => date('Y-m-d'),
                'keterangan_cek_produksi' => $var['keterangan_cek_produksi'][$s->id_subitem] ?? null,
                'hasil_cek_t'             => $hct,
                'hasil_cek_f'             => $hcf,
                'hasil_cek_v'             => $hcv,
            ];

            $exists = false;
            foreach ($existingCw as $cw) {
                if ($cw->id_subitem == $s->id_subitem) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $this->cwModel->set($row)->where('id_kavling', $idKavling)->where('id_subitem', $s->id_subitem)->update();
            } else {
                $row['id_kavling'] = $idKavling;
                $row['id_subitem'] = $s->id_subitem;
                $this->cwModel->insert($row);
            }
        }
    }

    protected function buildHistoryPayload(array $oldProduksi, array $newProduksi, array $var, array $fotoRows): array
    {
        $watchedFields = [
            'progres_bangunan',
            'st_0',
            'st_25',
            'st_50',
            'st_75',
            'st_100',
            'slo',
            'bp',
            'lpa',
            'lpa_tanggal',
            'st_jalan',
            'st_saluran',
            'st_air',
            'air_jenis',
            'listrik_jenis',
            'listrik_pln',
            'listrik_disediakan_no',
            'listrik_disediakan_tanggal',
            'air_deskripsi_unit',
            'air_pdam_no',
            'keterangan',
            'tanggal_pembangunan',
            'tanggal_rencana_selesai_pembangunan',
            'tanggal_selesai_pembangunan',
        ];

        $oldData = [];
        $newData = [];
        foreach ($watchedFields as $field) {
            $oldValue = $oldProduksi[$field] ?? null;
            $newValue = $newProduksi[$field] ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $oldData[$field] = $oldValue;
                $newData[$field] = $newValue;
            }
        }

        $fileSummary = array_map(static function (array $row) {
            return [
                'kategori'        => $row['kategori'] ?? null,
                'file_name'       => $row['file_name'] ?? null,
                'tgl_capture'     => $row['tgl_capture'] ?? null,
                'file_keterangan' => $row['file_keterangan'] ?? null,
                'foto_lat'        => $row['foto_lat'] ?? null,
                'foto_lng'        => $row['foto_lng'] ?? null,
            ];
        }, $fotoRows);

        $summaryParts = [];
        if (!empty($newData)) {
            $summaryParts[] = count($newData) . ' field diperbarui';
        }
        if (!empty($fotoRows)) {
            $summaryParts[] = count($fotoRows) . ' file/foto diunggah';
        }

        return [
            'summary'  => !empty($summaryParts) ? implode(', ', $summaryParts) : 'Data produksi disimpan',
            'old_data' => $oldData,
            'new_data' => $newData,
            'files'    => $fileSummary,
        ];
    }

    protected function shouldWriteHistory(array $payload): bool
    {
        return !empty($payload['old_data']) || !empty($payload['new_data']) || !empty($payload['files']);
    }

}
