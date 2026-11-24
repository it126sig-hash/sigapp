<?php

namespace App\Services\Mkdt;

use App\Repositories\Mkdt\PromoRepository;

class PromoService
{
    protected PromoRepository $promoRepo;
    protected $validation;

    public function __construct(?PromoRepository $promoRepo = null)
    {
        $this->promoRepo  = $promoRepo ?? new PromoRepository();
        $this->validation = \Config\Services::validation();
    }

    /**
     * Data awal halaman Web Promo dan endpoint /api/mkdt/promo/meta.
     *
     * @return array<string, mixed>
     */
    public function getIndexData(): array
    {
        $proyek         = $this->promoRepo->getProyekList();
        $activeProyekId = $this->resolveProyekId(null);

        return [
            'proyek'           => $proyek,
            'active_proyek_id' => $activeProyekId,
            'meta'             => [
                'proyek'           => $proyek,
                'active_proyek_id' => $activeProyekId,
                'status_options'   => [
                    ['value' => 1, 'label' => 'Aktif'],
                    ['value' => 0, 'label' => 'Tidak Aktif'],
                ],
            ],
        ];
    }

    /**
     * Dual-mode DataTables response:
     * - $raw = true  -> RAW_JSON_READY associative items (tanpa HTML)
     * - $raw = false -> Legacy 13-column DataTables payload
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getDataTables(array $params, bool $raw = false): array
    {
        $idProyek = $this->resolveProyekId($params['id_proyek'] ?? null);

        $search = '';
        if (isset($params['search']) && is_array($params['search'])) {
            $search = trim((string) ($params['search']['value'] ?? ''));
        } elseif (isset($params['search'])) {
            $search = trim((string) $params['search']);
        }

        $start  = max(0, (int) ($params['start'] ?? 0));
        $length = isset($params['length']) ? (int) $params['length'] : 10;
        $draw   = (int) ($params['draw'] ?? 1);

        $repoResult = $this->promoRepo->getDataTables([
            'id_proyek' => $idProyek,
            'search'    => $search,
            'start'     => $start,
            'length'    => $length,
        ]);

        $dataRows = [];
        foreach ($repoResult['rows'] as $key => $value) {
            if ($raw) {
                $dataRows[$key] = $this->mapRawRow($value);
                continue;
            }

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . (int) $value->id . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . (int) $value->id . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $dataRows[$key] = [
                $value->id,
                $value->nama_promo,
                number_format((float) ($value->diskon ?? 0)),
                $value->insentif,
                $this->formatDate($value->tgl_berlaku_start ?? null) . ' s/d <br>' . $this->formatDate($value->tgl_berlaku_end ?? null),
                $this->formatDate($value->tgl_booking_start ?? null) . ' s/d <br>' . $this->formatDate($value->tgl_booking_end ?? null),
                $value->keterangan,
                $this->statusBadge($value->status ?? 0, 'Aktif', 'Tidak'),
                $value->uadd_by,
                $this->formatDate($value->created_at ?? null),
                $value->uedit_by,
                $this->formatDate($value->updated_at ?? null),
                $ops,
            ];
        }

        if ($raw) {
            return [
                'draw'            => $draw,
                'recordsTotal'    => $repoResult['recordsTotal'],
                'recordsFiltered' => $repoResult['recordsFiltered'],
                'items'           => $dataRows,
                'data'            => $dataRows,
            ];
        }

        return [
            'token'           => csrf_hash(),
            'draw'            => $draw,
            'recordsTotal'    => $repoResult['recordsTotal'],
            'recordsFiltered' => $repoResult['recordsFiltered'],
            'data'            => $dataRows,
        ];
    }

    /**
     * Ambil seluruh data promo (dual-mode: raw JSON vs legacy array).
     *
     * @return array<int, mixed>
     */
    public function getAll(?int $idProyek = null, string $search = '', bool $raw = false): array
    {
        $resolvedProyekId = $this->resolveProyekId($idProyek);
        $rows             = $this->promoRepo->getAll($resolvedProyekId, $search);

        $result = [];
        foreach ($rows as $key => $value) {
            if ($raw) {
                $result[$key] = $this->mapRawRow($value);
                continue;
            }

            $ops = '<div class="btn-group">';
            $ops .= '	<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="edit(' . (int) $value->id . ')"><i class="fa fa-edit"></i></button>';
            $ops .= '	<button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="remove(' . (int) $value->id . ')"><i class="fa fa-trash"></i></button>';
            $ops .= '</div>';

            $result[$key] = [
                $value->id,
                $value->id_proyek,
                $value->nama_proyek ?? '',
                $value->nama_promo,
                number_format((float) ($value->diskon ?? 0)),
                $value->insentif,
                $this->statusBadge($value->status ?? 0, 'Aktif', 'Tidak'),
                $ops,
            ];
        }

        return $result;
    }

    /**
     * Ambil satu promo berdasarkan ID.
     */
    public function getOne(int $id): ?object
    {
        if ($id <= 0) {
            return null;
        }

        return $this->promoRepo->getById($id);
    }

    /**
     * Buat data promo baru.
     *
     * @param array<string, mixed> $input
     * @return array{success: bool, messages: string, data: ?object, errors: ?array<string, string>}
     */
    public function create(array $input, int $actorId): array
    {
        $now    = date('Y-m-d H:i:s');
        $fields = $this->normalizePayload($input);

        $fields['add_by']     = $actorId;
        $fields['created_at'] = $now;
        $fields['edit_by']    = $actorId;
        $fields['updated_at'] = $now;

        $errors = $this->validatePayload($fields);
        if ($errors !== []) {
            return [
                'success'  => false,
                'messages' => implode(' ', $errors),
                'data'     => null,
                'errors'   => $errors,
            ];
        }

        $insertedId = $this->promoRepo->insert($fields);
        if ($insertedId === false) {
            return [
                'success'  => false,
                'messages' => 'Insertion error!',
                'data'     => null,
                'errors'   => null,
            ];
        }

        return [
            'success'  => true,
            'messages' => 'Data berhasil diinput',
            'data'     => $this->promoRepo->getById($insertedId),
            'errors'   => null,
        ];
    }

    /**
     * Perbarui data promo berdasarkan ID.
     *
     * @param array<string, mixed> $input
     * @return array{success: bool, messages: string, data: ?object, errors: ?array<string, string>, not_found?: bool}
     */
    public function update(int $id, array $input, int $actorId): array
    {
        if ($id <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID promo tidak valid',
                'data'     => null,
                'errors'   => ['id' => 'ID promo tidak valid'],
            ];
        }

        $existing = $this->promoRepo->getById($id);
        if (!$existing) {
            return [
                'success'   => false,
                'messages'  => 'Data promo tidak ditemukan',
                'data'      => null,
                'errors'    => null,
                'not_found' => true,
            ];
        }

        $fields = $this->normalizePayload($input, $existing);
        $fields['edit_by']    = $actorId;
        $fields['updated_at'] = date('Y-m-d H:i:s');

        $errors = $this->validatePayload($fields);
        if ($errors !== []) {
            return [
                'success'  => false,
                'messages' => implode(' ', $errors),
                'data'     => null,
                'errors'   => $errors,
            ];
        }

        if (!$this->promoRepo->updateById($id, $fields)) {
            return [
                'success'  => false,
                'messages' => 'Opps!! Terjadi kesalahan',
                'data'     => null,
                'errors'   => null,
            ];
        }

        return [
            'success'  => true,
            'messages' => 'Data berhasil diperbaharui',
            'data'     => $this->promoRepo->getById($id),
            'errors'   => null,
        ];
    }

    /**
     * Nonaktifkan promo (soft-deactivate status = 0).
     *
     * @return array{success: bool, messages: string, data: ?object, errors: ?array<string, string>, not_found?: bool}
     */
    public function deactivate(int $id, int $actorId): array
    {
        if ($id <= 0) {
            return [
                'success'  => false,
                'messages' => 'ID promo tidak valid',
                'data'     => null,
                'errors'   => ['id' => 'ID promo tidak valid'],
            ];
        }

        $existing = $this->promoRepo->getById($id);
        if (!$existing) {
            return [
                'success'   => false,
                'messages'  => 'Data promo tidak ditemukan',
                'data'      => null,
                'errors'    => null,
                'not_found' => true,
            ];
        }

        $timestamp = date('Y-m-d H:i:s');
        if (!$this->promoRepo->deactivate($id, $actorId, $timestamp)) {
            return [
                'success'  => false,
                'messages' => 'Opps!! Terjadi kesalahan',
                'data'     => null,
                'errors'   => null,
            ];
        }

        return [
            'success'  => true,
            'messages' => 'Promo berhasil dinonaktifkan',
            'data'     => $this->promoRepo->getById($id),
            'errors'   => null,
        ];
    }

    /**
     * Format tanggal dengan guard aman untuk null dan zero-date.
     */
    public function formatDate(?string $tgl): string
    {
        if ($tgl === null) {
            return '-';
        }

        $tgl = trim($tgl);
        if (
            $tgl === ''
            || $tgl === '0000-00-00'
            || $tgl === '0000-00-00 00:00:00'
            || str_starts_with($tgl, '0000-00-00')
        ) {
            return '-';
        }

        $date = date_create($tgl);
        if ($date === false) {
            return '-';
        }

        $parsed = date_parse($tgl);
        if (!empty($parsed['hour']) || !empty($parsed['minute']) || !empty($parsed['second'])) {
            return date_format($date, 'd-M-Y H:i:s');
        }

        return date_format($date, 'd-M-Y');
    }

    /**
     * Render badge status aktif/nonaktif untuk DataTables legacy.
     */
    public function statusBadge($id, string $activeText, string $inactiveText): string
    {
        if ((string) $id === '1') {
            return '<span class="badge badge-pill badge-light-success" text-capitalized="">' . $activeText . '</span>';
        }

        return '<span class="badge badge-pill badge-light-danger" text-capitalized="">' . $inactiveText . '</span>';
    }

    /**
     * Sanitasi input numerik (mendukung format Cleave koma ribuan maupun format titik ribuan).
     */
    public function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $clean = str_replace(',', '', trim((string) $value));
        $clean = preg_replace('/[^\d.\-]/', '', $clean) ?? '';
        if ($clean === '') {
            return 0.0;
        }

        if (substr_count($clean, '.') > 1) {
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $input, ?object $existing = null): array
    {
        $rawProyek = $input['id_proyek'] ?? ($input['idProyek'] ?? null);
        $idProyek  = $this->resolveProyekId($rawProyek);
        if ($idProyek === null && $existing !== null && isset($existing->id_proyek)) {
            $idProyek = (int) $existing->id_proyek;
        }

        $rawStatus = $input['isActive'] ?? ($input['is_active'] ?? ($input['status'] ?? ($existing->status ?? 1)));
        $status    = ((string) $rawStatus === '0' || $rawStatus === 0 || $rawStatus === false) ? 0 : 1;

        return [
            'id_proyek'         => $idProyek,
            'nama_promo'        => trim((string) ($input['nama_promo'] ?? '')),
            'insentif'          => isset($input['insentif']) ? trim((string) $input['insentif']) : null,
            'diskon'            => $this->cleanNumeric($input['diskon'] ?? 0),
            'tgl_berlaku_start' => $this->normalizeDateInput($input['tgl_berlaku_start'] ?? null),
            'tgl_berlaku_end'   => $this->normalizeDateInput($input['tgl_berlaku_end'] ?? null),
            'tgl_booking_start' => $this->normalizeDateInput($input['tgl_booking_start'] ?? null),
            'tgl_booking_end'   => $this->normalizeDateInput($input['tgl_booking_end'] ?? null),
            'keterangan'        => isset($input['keterangan']) ? trim((string) $input['keterangan']) : null,
            'status'            => $status,
        ];
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, string>
     */
    protected function validatePayload(array $fields): array
    {
        $this->validation->reset();
        $this->validation->setRules([
            'id_proyek'  => ['label' => 'Proyek', 'rules' => 'permit_empty|is_natural_no_zero'],
            'nama_promo' => ['label' => 'Nama Promo', 'rules' => 'required|max_length[255]'],
        ]);

        if ($this->validation->run($fields) === false) {
            return $this->validation->getErrors();
        }

        return [];
    }

    protected function normalizeDateInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function resolveProyekId(mixed $requestValue): ?int
    {
        if (function_exists('resolve_active_proyek_id')) {
            return resolve_active_proyek_id($requestValue);
        }

        $id = (int) ($requestValue ?: 0);

        return $id > 0 ? $id : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapRawRow(object $value): array
    {
        return [
            'id'                          => (int) $value->id,
            'id_proyek'                   => isset($value->id_proyek) ? (int) $value->id_proyek : null,
            'nama_proyek'                 => $value->nama_proyek ?? null,
            'nama_promo'                  => (string) ($value->nama_promo ?? ''),
            'diskon'                      => (float) ($value->diskon ?? 0),
            'diskon_formatted'            => number_format((float) ($value->diskon ?? 0)),
            'insentif'                    => $value->insentif ?? null,
            'tgl_berlaku_start'           => $value->tgl_berlaku_start ?? null,
            'tgl_berlaku_end'             => $value->tgl_berlaku_end ?? null,
            'tgl_berlaku_start_formatted' => $this->formatDate($value->tgl_berlaku_start ?? null),
            'tgl_berlaku_end_formatted'   => $this->formatDate($value->tgl_berlaku_end ?? null),
            'tgl_booking_start'           => $value->tgl_booking_start ?? null,
            'tgl_booking_end'             => $value->tgl_booking_end ?? null,
            'tgl_booking_start_formatted' => $this->formatDate($value->tgl_booking_start ?? null),
            'tgl_booking_end_formatted'   => $this->formatDate($value->tgl_booking_end ?? null),
            'keterangan'                  => $value->keterangan ?? null,
            'status'                      => (int) ($value->status ?? 0),
            'status_label'                => ((int) ($value->status ?? 0) === 1) ? 'Aktif' : 'Tidak',
            'add_by'                      => isset($value->add_by) ? (int) $value->add_by : null,
            'uadd_by'                     => $value->uadd_by ?? null,
            'created_at'                  => $value->created_at ?? null,
            'created_at_formatted'        => $this->formatDate($value->created_at ?? null),
            'edit_by'                     => isset($value->edit_by) ? (int) $value->edit_by : null,
            'uedit_by'                    => $value->uedit_by ?? null,
            'updated_at'                  => $value->updated_at ?? null,
            'updated_at_formatted'        => $this->formatDate($value->updated_at ?? null),
        ];
    }
}
