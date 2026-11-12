<?php

namespace App\Services\Transaksi;

use App\Repositories\Transaksi\HargaJualRepository;
use App\Services\FileAccessService;
use App\Services\MkdtHistoryService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class HargaJualService
{
    protected BaseConnection $db;
    protected HargaJualRepository $hargaJualRepo;
    protected FileAccessService $fileAccessService;
    protected MkdtHistoryService $mkdtHistoryService;
    protected $validation;

    public function __construct(
        ?HargaJualRepository $hargaJualRepo = null,
        ?FileAccessService $fileAccessService = null,
        ?MkdtHistoryService $mkdtHistoryService = null,
        ?BaseConnection $db = null
    ) {
        $this->db                 = $db ?? \Config\Database::connect();
        $this->hargaJualRepo      = $hargaJualRepo ?? new HargaJualRepository($this->db);
        $this->fileAccessService  = $fileAccessService ?? new FileAccessService();
        $this->mkdtHistoryService = $mkdtHistoryService ?? new MkdtHistoryService();
        $this->validation         = \Config\Services::validation();
    }

    public function hasAccess(int $userId): bool
    {
        return $this->hargaJualRepo->hasModuleAccess($userId, 'Hargajual');
    }

    public function getProyekList(): array
    {
        return $this->hargaJualRepo->getProyekList();
    }

    public function getMeta(?int $idProyek, int $userId): array
    {
        $resolvedProyekId = $idProyek ?? (function_exists('resolve_active_proyek_id') ? resolve_active_proyek_id(null) : null);
        $hasAccess        = $this->hasAccess($userId);
        $isAdmin          = function_exists('in_groups') ? (bool) in_groups(['1']) : false;

        return [
            'id_proyek' => $resolvedProyekId ? (int) $resolvedProyekId : null,
            'user_id'   => $userId,
            'proyek_list' => $this->getProyekList(),
            'subsidi_options' => [
                ['value' => 1, 'label' => 'Subsidi'],
                ['value' => 0, 'label' => 'Non-Subsidi'],
            ],
            'active_options' => [
                ['value' => 1, 'label' => 'Aktif'],
                ['value' => 0, 'label' => 'Tidak Aktif'],
            ],
            'permissions' => [
                'has_access'            => $hasAccess,
                'is_admin'              => $isAdmin,
                'can_create'            => $hasAccess,
                'can_update'            => $hasAccess,
                'can_toggle_active'     => $hasAccess,
                'can_set_harga_kavling' => $hasAccess || (function_exists('in_groups') && in_groups(['1', '3', '4', '6'])),
            ],
        ];
    }

    public function getDataTable(array $var, ?int $idProyek, bool $rawFormat = false): array
    {
        $rows            = $this->hargaJualRepo->getDataTableRows($var, $idProyek);
        $recordsFiltered = $this->hargaJualRepo->getDataTableFilteredCount($var, $idProyek);
        $recordsTotal    = $this->hargaJualRepo->getDataTableTotalCount($idProyek);

        $dataRows = [];
        $no       = (int) ($var['start'] ?? 0);

        foreach ($rows as $key => $value) {
            $no++;
            $accessUrl = !empty($value->id_filehj)
                ? $this->fileAccessService->accessUrl('file_hargajual', (int) $value->id_filehj)
                : null;

            if ($rawFormat) {
                $dataRows[$key] = [
                    'no'                   => $no,
                    'id'                   => (int) $value->id,
                    'id_proyek'            => (int) ($value->id_proyek ?? 0),
                    'nama_proyek'          => $value->nama_proyek ?? null,
                    'tgl_harga'            => $value->tgl_harga ?? null,
                    'tgl_harga_formatted'  => $this->formatTgl($value->tgl_harga ?? null),
                    'row'                  => $value->row ?? null,
                    'id_tipe'              => $value->id_tipe ?? null,
                    'lb'                   => $value->lb ?? null,
                    'lt'                   => $value->lt ?? null,
                    'hargajual'            => (float) ($value->hargajual ?? 0),
                    'hargajual_net'        => (float) ($value->hargajual_net ?? 0),
                    'kpr'                  => (float) ($value->kpr ?? 0),
                    'uang_muka'            => (float) ($value->uang_muka ?? 0),
                    'biaya_adm'            => (float) ($value->biaya_adm ?? 0),
                    'bphtb'                => (float) ($value->bphtb ?? 0),
                    'ppn'                  => (float) ($value->ppn ?? 0),
                    'biaya_proses'         => (float) ($value->biaya_proses ?? 0),
                    'is_subsidi'           => (int) ($value->is_subsidi ?? 0),
                    'subsidi_label'        => ((int) ($value->is_subsidi ?? 0) === 1) ? 'Subsidi' : 'Non-Subsidi',
                    'keterangan'           => $value->keterangan ?? null,
                    'is_active'            => (int) ($value->is_active ?? 0),
                    'active_label'         => ((int) ($value->is_active ?? 0) === 1) ? 'Aktif' : 'Tidak Aktif',
                    'id_filehj'            => !empty($value->id_filehj) ? (int) $value->id_filehj : null,
                    'lokasi'               => $value->lokasi ?? null,
                    'file_name'            => $value->file_name ?? null,
                    'default_filename'     => $value->default_filename ?? null,
                    'access_url'           => $accessUrl,
                    'uadd_by'              => $value->uadd_by ?? null,
                    'created_at'           => $value->created_at ?? null,
                    'created_at_formatted' => $this->formatTgl($value->created_at ?? null),
                    'uedit_by'             => $value->uedit_by ?? null,
                    'updated_at'           => $value->updated_at ?? null,
                    'updated_at_formatted' => $this->formatTgl($value->updated_at ?? null),
                ];
                continue;
            }

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . $value->id . ')" title="Edit"><i class="fa fa-edit"></i></button>';
            if ($value->is_active) {
                $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . $value->id . ', ' . $value->is_active . ')" title="Nonaktifkan"><i class="fa fa-trash"></i></button>';
            } else {
                $ops .= '	<button type="button" class="btn btn-outline-success waves-effect btn-sm" onclick="remove(' . $value->id . ', ' . $value->is_active . ')" title="Aktifkan"><i class="fa fa-check"></i></button>';
            }
            if ($accessUrl !== null) {
                $ops .= '	<a target="_blank" href="' . $accessUrl . '" class="btn btn-outline-info waves-effect btn-sm" title="Lihat Softfile"><i class="fa fa-download"></i></a>';
            }
            $ops .= '</div>';

            $dt_tanggal = $this->formatTgl($value->tgl_harga) . "<br><div class='mt-1'>" . $this->renderActiveBadge($value->is_subsidi, "Subsidi", "Non-Subsidi") . "</div>";
            $dt_tipe    = "<strong>" . $value->id_tipe . "</strong> (" . $value->lb . "/" . $value->lt . ")";

            $dt_harga = "<div class='text-nowrap'>
				<small class='text-dark font-weight-bold d-block'>Jual: Rp " . number_format((float) $value->hargajual) . "</small>
				<small class='text-muted d-block'>Net: Rp " . number_format((float) $value->hargajual_net) . "</small>
				<small class='text-muted d-block'>KPR: Rp " . number_format((float) $value->kpr) . "</small>
				<small class='text-muted d-block'>UM: Rp " . number_format((float) $value->uang_muka) . "</small>
			</div>";

            $dt_biaya = "<div class='text-nowrap'>
				<small class='text-muted d-block'>Admin: Rp " . number_format((float) $value->biaya_adm) . "</small>
				<small class='text-muted d-block'>BPHTB: Rp " . number_format((float) $value->bphtb) . "</small>
				<small class='text-muted d-block'>PPn: Rp " . number_format((float) $value->ppn) . "</small>
				<small class='text-muted d-block'>Proses: Rp " . number_format((float) $value->biaya_proses) . "</small>
			</div>";

            $dt_ditambah = $value->uadd_by . "<br><small class='text-muted'>" . $this->formatTgl($value->created_at) . "</small>";
            $dt_diubah   = $value->uedit_by . "<br><small class='text-muted'>" . $this->formatTgl($value->updated_at) . "</small>";

            $dataRows[$key] = [
                $no,
                $ops,
                $dt_tanggal,
                $value->row,
                $dt_tipe,
                $dt_harga,
                $dt_biaya,
                $value->keterangan,
                $this->renderActiveBadge($value->is_active, "Aktif", "Tidak Aktif"),
                $dt_ditambah,
                $dt_diubah,
            ];
        }

        return [
            'token'           => csrf_hash(),
            'draw'            => $var['draw'] ?? 1,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $dataRows,
        ];
    }

    public function getAll(?int $idProyek, string $search = ''): array
    {
        $rows = $this->hargaJualRepo->getActiveByProyek($idProyek, $search);
        foreach ($rows as $row) {
            if (!empty($row->id_filehj)) {
                $row->access_url = $this->fileAccessService->accessUrl('file_hargajual', (int) $row->id_filehj);
            }
        }

        return $rows;
    }

    public function getOne(int $id): ?array
    {
        $row = $this->hargaJualRepo->getDetailById($id);
        if (!$row) {
            return null;
        }

        $data = (array) $row;
        $data['token'] = csrf_hash();
        if (!empty($row->id_filehj)) {
            $data['access_url'] = $this->fileAccessService->accessUrl('file_hargajual', (int) $row->id_filehj);
        }

        return $data;
    }

    public function create(array $fields, ?UploadedFile $file, int $userId): array
    {
        $hargajual = $this->cleanNumeric($fields['hargajual'] ?? 0);
        $rawHjNet  = $fields['hargajual_net'] ?? null;
        $isSubsidi = (int) $this->cleanNumeric($fields['is_subsidi'] ?? 0);

        if ($isSubsidi === 1) {
            $hargajualNet = $hargajual;
        } elseif ($rawHjNet !== null && trim((string) $rawHjNet) !== '') {
            $hargajualNet = $this->cleanNumeric($rawHjNet);
        } else {
            $hargajualNet = $hargajual;
        }

        $payload = [
            'id_proyek'     => $fields['id_proyek'] ?? ($fields['idProyek'] ?? null),
            'tgl_harga'     => $fields['tgl_harga'] ?? null,
            'row'           => $fields['row'] ?? null,
            'id_tipe'       => $fields['id_tipe'] ?? null,
            'lb'            => $fields['lb'] ?? null,
            'lt'            => $fields['lt'] ?? null,
            'hargajual'     => $hargajual,
            'hargajual_net' => $hargajualNet,
            'kpr'           => $this->cleanNumeric($fields['kpr'] ?? 0),
            'bphtb'         => $this->cleanNumeric($fields['bphtb'] ?? 0),
            'uang_muka'     => $this->cleanNumeric($fields['uang_muka'] ?? 0),
            'biaya_adm'     => $this->cleanNumeric($fields['biaya_adm'] ?? 0),
            'ppn'           => $this->cleanNumeric($fields['ppn'] ?? 0),
            'is_subsidi'    => $isSubsidi,
            'biaya_proses'  => $this->cleanNumeric($fields['biaya_proses'] ?? 0),
            'keterangan'    => $fields['keterangan'] ?? null,
            'add_by'        => $userId,
            'created_at'    => date('Y-m-d H:i:s'),
            'edit_by'       => $userId,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->validation->reset();
        $this->validation->setRules([
            'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'hargajual' => ['label' => 'Harga Jual', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if ($this->validation->run($payload) === false) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => $this->validation->listErrors(),
                'errors'   => $this->validation->getErrors(),
            ];
        }

        $this->db->transStart();
        try {
            if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
                $name         = $file->getRandomName();
                $originalName = $file->getClientName();
                $lok          = 'uploads/pricelist/' . date('Ymd') . '/';

                $this->fileAccessService->storeAs($file, $lok, $name);

                $fileId = $this->hargaJualRepo->insertFileHargajual([
                    'lokasi'           => $lok,
                    'default_filename' => $originalName,
                    'file_name'        => $name,
                    'upload_at'        => date('Y-m-d H:i:s'),
                    'upload_by'        => $userId,
                ]);
                $payload['id_filehj'] = $fileId;
            }

            if (!$this->hargaJualRepo->insertHargaJual($payload)) {
                throw new \RuntimeException('Insertion error!');
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Insertion error!');
            }

            return [
                'token'    => csrf_hash(),
                'success'  => true,
                'messages' => 'Data berhasil diinput',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Insertion error!',
            ];
        }
    }

    public function update(int $id, array $fields, ?UploadedFile $file, int $userId): array
    {
        $hargajual = $this->cleanNumeric($fields['hargajual'] ?? 0);
        $rawHjNet  = $fields['hargajual_net'] ?? null;
        $isSubsidi = (int) $this->cleanNumeric($fields['is_subsidi'] ?? 0);

        if ($isSubsidi === 1) {
            $hargajualNet = $hargajual;
        } elseif ($rawHjNet !== null && trim((string) $rawHjNet) !== '') {
            $hargajualNet = $this->cleanNumeric($rawHjNet);
        } else {
            $hargajualNet = $hargajual;
        }

        $payload = [
            'id_proyek'     => $fields['id_proyek'] ?? ($fields['idProyek'] ?? null),
            'tgl_harga'     => $fields['tgl_harga'] ?? null,
            'row'           => $fields['row'] ?? null,
            'id_tipe'       => $fields['id_tipe'] ?? null,
            'lb'            => $fields['lb'] ?? null,
            'lt'            => $fields['lt'] ?? null,
            'hargajual'     => $hargajual,
            'hargajual_net' => $hargajualNet,
            'kpr'           => $this->cleanNumeric($fields['kpr'] ?? 0),
            'ppn'           => $this->cleanNumeric($fields['ppn'] ?? 0),
            'bphtb'         => $this->cleanNumeric($fields['bphtb'] ?? 0),
            'uang_muka'     => $this->cleanNumeric($fields['uang_muka'] ?? 0),
            'biaya_adm'     => $this->cleanNumeric($fields['biaya_adm'] ?? 0),
            'biaya_proses'  => $this->cleanNumeric($fields['biaya_proses'] ?? 0),
            'is_subsidi'    => $isSubsidi,
            'keterangan'    => $fields['keterangan'] ?? null,
            'edit_by'       => $userId,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->validation->reset();
        $this->validation->setRules([
            'id_proyek' => ['label' => 'Id proyek', 'rules' => 'permit_empty|max_length[255]'],
            'hargajual' => ['label' => 'Harga Jual', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if ($this->validation->run($payload) === false) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => $this->validation->listErrors(),
                'errors'   => $this->validation->getErrors(),
            ];
        }

        $this->db->transStart();
        try {
            if ($file && $file->isValid() && !$file->hasMoved() && $file->getSize() > 0) {
                $name         = $file->getRandomName();
                $originalName = $file->getClientName();
                $lok          = 'uploads/pricelist/' . date('Ymd') . '/';

                $this->fileAccessService->storeAs($file, $lok, $name);

                $fileId = $this->hargaJualRepo->insertFileHargajual([
                    'lokasi'           => $lok,
                    'default_filename' => $originalName,
                    'file_name'        => $name,
                    'upload_at'        => date('Y-m-d H:i:s'),
                    'upload_by'        => $userId,
                ]);
                $payload['id_filehj'] = $fileId;
            } elseif (($fields['remove_filehj'] ?? '') === '1') {
                $payload['id_filehj'] = null;
            }

            if (!$this->hargaJualRepo->updateHargaJual($id, $payload)) {
                throw new \RuntimeException('Opps!! Terjadi kesalahan');
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Opps!! Terjadi kesalahan');
            }

            return [
                'token'    => csrf_hash(),
                'success'  => true,
                'messages' => 'Data berhasil diperbaharui',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Opps!! Terjadi kesalahan',
            ];
        }
    }

    public function toggleActive(int $id, int $currentIsActive, int $userId): array
    {
        $newIsActive = $currentIsActive == 1 ? 0 : 1;
        $fields      = [
            'is_active'  => $newIsActive,
            'edit_by'    => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->validation->reset();
        $this->validation->setRules([
            'id' => ['label' => 'Tidak ada Pricelist terpilih', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        if ($this->validation->run($fields) === false) {
            return [
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => $this->validation->listErrors(),
                'errors'   => $this->validation->getErrors(),
            ];
        }

        if ($this->hargaJualRepo->updateHargaJual($id, $fields)) {
            return [
                'token'    => csrf_hash(),
                'success'  => true,
                'messages' => 'Pricelist berhasil dinonaktifkan',
            ];
        }

        return [
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'Opps!! Terjadi kesalahan saat menonaktifkan pricelist',
        ];
    }

    public function setHargaKavling(string $rawIdKavling, $idHargaAkhir, int $userId): array
    {
        $response = [
            'token'    => csrf_hash(),
            'success'  => false,
            'messages' => 'Update error!',
        ];

        $baseFields = [
            'id_kavling'       => $rawIdKavling,
            'harga_akhir'      => $idHargaAkhir,
            'harga_akhir_tgl'  => date('Y-m-d'),
            'harga_akhir_oleh' => $userId,
            'edit_by'          => $userId,
        ];

        $ids = preg_split('/[;,]+/', $rawIdKavling, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($ids) && trim($rawIdKavling) !== '') {
            $ids = [trim($rawIdKavling)];
        }

        $this->validation->reset();
        $this->validation->setRules([
            'harga' => ['label' => 'Harga', 'rules' => 'permit_empty|max_length[255]'],
        ]);

        $this->db->transStart();
        try {
            if (!empty($ids)) {
                foreach ($ids as $singleId) {
                    $fields               = $baseFields;
                    $fields['id_kavling'] = $singleId;

                    if ($this->validation->run($fields) === false) {
                        $response['success']  = false;
                        $response['messages'] = $this->validation->listErrors();
                    } else {
                        $response = $this->applySetHarga($fields, $response, $userId);
                    }
                }
            } else {
                if ($this->validation->run($baseFields) === false) {
                    $response['success']  = false;
                    $response['messages'] = $this->validation->listErrors();
                } else {
                    $response = $this->applySetHarga($baseFields, $response, $userId);
                }
            }

            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                $response['success']  = false;
                $response['messages'] = 'Update error!';
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            $response['success']  = false;
            $response['messages'] = 'Update error!';
        }

        return $response;
    }

    protected function applySetHarga(array $fields, array $response, int $userId): array
    {
        $idKavling  = (int) ($fields['id_kavling'] ?? 0);
        $oldKavling = $this->hargaJualRepo->findKavlingById($idKavling);
        $pricelist  = !empty($fields['harga_akhir'])
            ? $this->hargaJualRepo->getPricelistRowById((int) $fields['harga_akhir'])
            : null;

        if ($this->hargaJualRepo->updateKavlingHarga($idKavling, $fields)) {
            $pricelistLabel = $pricelist
                ? ('Rp ' . number_format((float) $pricelist->hargajual, 0, ',', '.') . ' / ' . $pricelist->tgl_harga . ' ROW ' . $pricelist->row)
                : null;

            $this->mkdtHistoryService->log(
                $idKavling,
                $oldKavling ? (int) ($oldKavling->id_mkdt ?? 0) ?: null : null,
                MkdtHistoryService::ACTION_SET_HARGA_JUAL,
                $this->mkdtHistoryService->buildSetHargaSummary(
                    $oldKavling ? ['harga_akhir' => $oldKavling->harga_akhir ?? null] : null,
                    ['harga_akhir' => $fields['harga_akhir']],
                    $pricelistLabel
                ),
                $oldKavling ? ['harga_akhir' => $oldKavling->harga_akhir ?? null] : null,
                ['harga_akhir' => $fields['harga_akhir']],
                $userId
            );

            $response['success']  = true;
            $response['messages'] = 'Successfully updated';
        } else {
            $response['success']  = false;
            $response['messages'] = 'Update error!';
        }

        return $response;
    }

    public function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $clean = str_replace(',', '', trim((string) $value));
        $clean = preg_replace('/[^\d.\-]/', '', $clean);

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    protected function renderActiveBadge($id, string $texts, string $textf): string
    {
        $r = '<span class="btn btn-danger" style="padding:5px">' . $textf . '</span>';
        if ((string) $id === '1') {
            $r = '<span class="btn btn-primary" style="padding:5px">' . $texts . '</span>';
        }
        return $r;
    }

    protected function formatTgl($tgl): string
    {
        $v = date_parse((string) $tgl);

        if ($tgl == '' || $tgl == '0000-00-00' || $tgl == null) {
            return '-';
        }
        if ($v['hour']) {
            return date_format(date_create((string) $tgl), 'd-M-Y H:i:s');
        }
        return date_format(date_create((string) $tgl), 'd-M-Y');
    }
}
