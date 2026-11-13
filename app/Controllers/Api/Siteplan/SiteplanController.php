<?php

namespace App\Controllers\Api\Siteplan;

use App\Controllers\Api\BaseApiController;
use App\Exceptions\ServiceException;
use App\Services\Proyek\ProyekService;
use App\Services\Siteplan\SiteplanDataService;
use App\Services\Siteplan\SiteplanDetailService;
use App\Services\Siteplan\SiteplanService;
use App\Services\Siteplan\SiteplanUrgentService;
use CodeIgniter\HTTP\ResponseInterface;

class SiteplanController extends BaseApiController
{
    protected SiteplanService $siteplanService;
    protected SiteplanDetailService $siteplanDetailService;
    protected SiteplanDataService $siteplanDataService;
    protected SiteplanUrgentService $siteplanUrgentService;
    protected ProyekService $proyekService;

    public function __construct(
        ?SiteplanService $siteplanService = null,
        ?SiteplanDetailService $siteplanDetailService = null,
        ?SiteplanDataService $siteplanDataService = null,
        ?SiteplanUrgentService $siteplanUrgentService = null,
        ?ProyekService $proyekService = null
    ) {
        helper(['auth', 'active_proyek']);

        $this->siteplanService       = $siteplanService ?? new SiteplanService();
        $this->siteplanDetailService = $siteplanDetailService ?? new SiteplanDetailService();
        $this->siteplanDataService   = $siteplanDataService ?? new SiteplanDataService();
        $this->siteplanUrgentService = $siteplanUrgentService ?? new SiteplanUrgentService();
        $this->proyekService         = $proyekService ?? new ProyekService();
    }

    // ─── MODERN REST ENDPOINTS (/api/siteplan/*) ───

    /**
     * Metadata inisial halaman siteplan (proyek, master options, config_shape, permissions).
     */
    public function meta(): ResponseInterface
    {
        $rawQueryId = $this->request->getGet('id_proyek');
        if ($rawQueryId !== null && (int) $rawQueryId <= 0) {
            return $this->error('Parameter id_proyek tidak valid.', 422, [
                'id_proyek' => 'Parameter id_proyek harus berupa angka positif.',
            ]);
        }

        $requestData = $this->getRequestData();
        $rawIdProyek = $rawQueryId
            ?? ($requestData['id_proyek'] ?? null)
            ?? $this->request->header('X-Project-Id')?->getValue();

        $idProyek = ($rawIdProyek !== null && $rawIdProyek !== '') ? (int) $rawIdProyek : null;
        if ($idProyek !== null && $idProyek <= 0) {
            return $this->error('Parameter id_proyek tidak valid.', 422, [
                'id_proyek' => 'Parameter id_proyek harus berupa angka positif.',
            ]);
        }

        try {
            $meta = $this->siteplanService->getMeta($idProyek, $this->currentUserId());
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode(), $e->getErrors() ?: null);
        }

        return $this->success($meta, 'Metadata siteplan berhasil dimuat');
    }

    /**
     * Mendapatkan daftar kavling beserta visual_rows dan visual_fill untuk canvas siteplan.
     */
    public function kavling(): ResponseInterface
    {
        $params = $this->getRequestData();

        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (! empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        if (empty($params['id_proyek']) || (int) $params['id_proyek'] <= 0) {
            return $this->error('Parameter id_proyek wajib diisi', 400);
        }

        // Default id_role = 0 agar SiteplanDataService memicu SiteplanVisualStatusService::appendVisualRows
        if (! isset($params['id_role'])) {
            $params['id_role'] = 0;
        }

        $result = $this->siteplanDataService->getKavlings($params);

        return $this->success($result);
    }

    /**
     * Mendapatkan detail proyek dan siteplan_access_url.
     */
    public function proyek($id = null): ResponseInterface
    {
        $requestData = $this->getRequestData();
        $idProyek    = (int) ($id ?: ($requestData['id_proyek'] ?? null) ?: $this->request->header('X-Project-Id')?->getValue());

        if ($idProyek <= 0) {
            return $this->error('ID Proyek tidak valid', 400);
        }

        $proyek = $this->proyekService->getOne($idProyek);

        if (! $proyek) {
            return $this->error('Data proyek tidak ditemukan', 404);
        }

        return $this->success($proyek);
    }

    /**
     * Mendapatkan map konfigurasi shape dari database config_shape.
     */
    public function config(): ResponseInterface
    {
        $rows = $this->siteplanService->getConfigShapeList();
        $map  = [];

        foreach ($rows as $row) {
            if (! isset($row->config_name)) {
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
    public function others(): ResponseInterface
    {
        $params = $this->getRequestData();

        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (! empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        $result = $this->siteplanDataService->getOthers($params);

        return $this->success($result);
    }

    /**
     * Mendapatkan detail lengkap kavling (MKDT, tagihan, pembayaran, legal, produksi, pajak, cashout, finance flow).
     */
    public function detail(?int $idKavling = null): ResponseInterface
    {
        $input             = $this->getRequestData();
        $resolvedIdKavling = ($idKavling !== null && $idKavling > 0)
            ? $idKavling
            : (int) ($input['id_kavling'] ?? 0);

        $idMkdt      = $this->toNullableInt($input['id_mkdt'] ?? null);
        $idHargaJual = $this->toNullableInt($input['id_hargajual'] ?? null);
        $idLegal     = $this->toNullableInt($input['id_legal'] ?? null);

        if ($resolvedIdKavling <= 0 && ($idMkdt === null || $idMkdt <= 0)) {
            return $this->error('Parameter id_kavling wajib diisi.', 422, [
                'id_kavling' => 'Parameter id_kavling harus berupa angka positif.',
            ]);
        }

        $detail = $this->siteplanDetailService->getDetail(
            $resolvedIdKavling,
            $idMkdt,
            $idHargaJual,
            $idLegal,
            $this->currentUserId()
        );

        return $this->success($detail, 'Detail kavling berhasil dimuat');
    }

    // ─── ZERO-DOWNTIME LEGACY AJAX ENDPOINTS (+ ?format=raw FULL JSON) ───

    public function urgentSummary(): ResponseInterface
    {
        $input         = $this->getRequestData();
        $wantsEnvelope = $this->wantsEnvelope();
        $idProyek      = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);

        if ($idProyek <= 0) {
            if ($wantsEnvelope) {
                return $this->error('id_proyek wajib diisi', 422, [
                    'id_proyek' => 'Parameter id_proyek wajib diisi.',
                ]);
            }

            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'id_proyek wajib diisi',
                'summary'  => $this->siteplanUrgentService->emptySummary(),
            ]);
        }

        $userId         = $this->currentUserId();
        $sessionGroupId = (int) (session()->group_id ?? 0);
        $groupId        = $this->siteplanUrgentService->resolveGroupId(
            $sessionGroupId > 0 ? $sessionGroupId : null,
            $userId
        );

        if ($sessionGroupId <= 0 && $groupId > 0) {
            session()->set('group_id', $groupId);
        }

        $summary = $this->siteplanUrgentService->getUrgentSummary($idProyek, $groupId, $userId);

        if ($wantsEnvelope) {
            return $this->success(['summary' => $summary], 'Ringkasan urgent siteplan berhasil dimuat');
        }

        return $this->response->setJSON([
            'token'   => csrf_hash(),
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function getAllKavling(): ResponseInterface
    {
        $params = $this->getRequestData();

        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (! empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        $result = $this->siteplanDataService->getKavlings($params);

        if ($this->wantsEnvelope()) {
            return $this->success($result, 'Data kavling siteplan berhasil dimuat');
        }

        $result['token']  = csrf_hash();
        $result['config'] = [];

        return $this->response->setJSON($result);
    }

    public function getKategoriOptions(): ResponseInterface
    {
        $input         = $this->getRequestData();
        $wantsEnvelope = $this->wantsEnvelope();
        $idProyek      = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);

        if ($idProyek <= 0) {
            if ($wantsEnvelope) {
                return $this->error('Invalid ID Proyek', 422, [
                    'id_proyek' => 'Parameter id_proyek wajib diisi.',
                ]);
            }

            return $this->response->setJSON([
                'token'   => csrf_hash(),
                'success' => false,
                'message' => 'Invalid ID Proyek',
            ]);
        }

        $options = $this->siteplanService->getKategoriOptions($idProyek);

        if ($wantsEnvelope) {
            return $this->success($options, 'Opsi kategori siteplan berhasil dimuat');
        }

        return $this->response->setJSON([
            'token'   => csrf_hash(),
            'success' => true,
            'data'    => $options,
        ]);
    }

    public function get_others(): ResponseInterface
    {
        $params = $this->getRequestData();

        if (empty($params['id_proyek'])) {
            $headerProjectId = $this->request->header('X-Project-Id')?->getValue();
            if (! empty($headerProjectId)) {
                $params['id_proyek'] = (int) $headerProjectId;
            }
        }

        $result = $this->siteplanDataService->getOthers($params);

        if ($this->wantsEnvelope()) {
            return $this->success($result, 'Data fasilitas/others siteplan berhasil dimuat');
        }

        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function add_kavling(): ResponseInterface
    {
        $post   = $this->getRequestData();
        $result = $this->siteplanService->addKavlingOrOthers($post, $this->currentUserId());

        if ($this->wantsEnvelope()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'Gagal menambahkan data siteplan'), 422);
            }

            return $this->successCreated($result, (string) ($result['messages'] ?? 'Data berhasil diinput'));
        }

        return $this->response->setJSON($result);
    }

    public function edit_kavling(): ResponseInterface
    {
        $post   = $this->getRequestData();
        $result = $this->siteplanService->editKavling($post, $this->currentUserId());

        if ($this->wantsEnvelope()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'Gagal memperbarui data kavling'), 422);
            }

            return $this->success($result, (string) ($result['messages'] ?? 'Successfully updated'));
        }

        return $this->response->setJSON($result);
    }

    public function edit_others(): ResponseInterface
    {
        $post   = $this->getRequestData();
        $result = $this->siteplanService->editOthers($post, $this->currentUserId());

        if ($this->wantsEnvelope()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'Data gagal diperbaharui!'), 422);
            }

            return $this->success($result, (string) ($result['messages'] ?? 'Data berhasil diperbaharui'));
        }

        return $this->response->setJSON($result);
    }

    public function get_kavling_by_multiple_id(): ResponseInterface
    {
        $input  = $this->getRequestData();
        $ids    = $this->normalizeIdArray($input['id_kavling'] ?? null);
        $result = $this->siteplanService->getKavlingByMultipleId($ids);

        if ($this->wantsEnvelope()) {
            return $this->success($result['data'] ?? [], 'Data kavling berhasil dimuat');
        }

        return $this->response->setJSON($result);
    }

    public function get_harga_kavling(): ResponseInterface
    {
        $input  = $this->getRequestData();
        $ids    = $this->normalizeIdArray($input['id_kavling'] ?? null);
        $result = $this->siteplanService->getHargaKavling($ids);

        if ($this->wantsEnvelope()) {
            return $this->success($result['data'] ?? [], 'Data harga kavling berhasil dimuat');
        }

        return $this->response->setJSON($result);
    }

    public function get_turun_pembangunan(): ResponseInterface
    {
        $input  = $this->getRequestData();
        $ids    = $this->normalizeIdArray($input['id_kavling'] ?? null);
        $result = $this->siteplanService->getTurunPembangunan($ids);

        if ($this->wantsEnvelope()) {
            return $this->success($result['data'] ?? [], 'Data turun pembangunan berhasil dimuat');
        }

        return $this->response->setJSON($result);
    }

    public function set_turun_pembangunan(): ResponseInterface
    {
        $post   = $this->getRequestData();
        $file   = $this->request->getFile('perintah_bangun_file');
        $result = $this->siteplanService->setTurunPembangunan($post, $file, $this->currentUserId());

        if ($this->wantsEnvelope()) {
            if (! ($result['success'] ?? false)) {
                return $this->error((string) ($result['messages'] ?? 'Gagal melakukan perubahan data'), 422);
            }

            return $this->success($result, (string) ($result['messages'] ?? 'Berhasil melakukan perubahan data'));
        }

        return $this->response->setJSON($result);
    }

    public function get_kavling_by_id(): ResponseInterface
    {
        $input  = $this->getRequestData();
        $id     = (int) ($input['id_kavling'] ?? 0);
        $result = $this->siteplanService->getKavlingById($id);

        if ($this->wantsEnvelope()) {
            if ($result === null) {
                return $this->error('Data kavling tidak ditemukan', 404);
            }

            return $this->success($result['data'] ?? null, 'Detail kavling berhasil dimuat');
        }

        if ($result === null) {
            return $this->response->setJSON([
                'token' => csrf_hash(),
                'data'  => null,
            ]);
        }

        return $this->response->setJSON($result);
    }

    public function get_detail(): ResponseInterface
    {
        $input       = $this->getRequestData();
        $idKavling   = (int) ($input['id_kavling'] ?? 0);
        $idMkdt      = $this->toNullableInt($input['id_mkdt'] ?? null);
        $idHargaJual = $this->toNullableInt($input['id_hargajual'] ?? null);
        $idLegal     = $this->toNullableInt($input['id_legal'] ?? null);

        $detail = $this->siteplanDetailService->getDetail(
            $idKavling,
            $idMkdt,
            $idHargaJual,
            $idLegal,
            $this->currentUserId()
        );

        if ($this->wantsEnvelope()) {
            return $this->success($detail, 'Detail kavling berhasil dimuat');
        }

        return $this->response->setJSON($detail);
    }

    // ─── INTERNAL HELPERS ───

    private function currentUserId(): int
    {
        return function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
    }

    private function wantsEnvelope(): bool
    {
        $uriPath = ltrim($this->request->getUri()->getPath(), '/');
        if (str_starts_with($uriPath, 'index.php/')) {
            $uriPath = substr($uriPath, strlen('index.php/'));
        }

        if (str_starts_with($uriPath, 'api/') || str_contains($uriPath, '/api/')) {
            return true;
        }

        $format = strtolower((string) ($this->request->getGet('format') ?? ($this->getRequestData()['format'] ?? '')));
        if ($format === 'raw') {
            return true;
        }

        return trim((string) $this->request->getHeaderLine('X-Client-Platform')) !== '';
    }

    private function getRequestData(): array
    {
        $query = $this->request->getGet();
        $query = is_array($query) ? $query : [];

        $post = $this->request->getPost();
        $post = is_array($post) ? $post : [];

        $json = [];
        if (str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'application/json')) {
            try {
                $decoded = $this->request->getJSON(true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            } catch (\Throwable $e) {
                $json = [];
            }
        }

        return array_merge($query, $post, $json);
    }

    private function normalizeIdArray(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $items = is_array($raw)
            ? $raw
            : preg_split('/[;,]+/', (string) $raw);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $items), static fn (int $id): bool => $id > 0));
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
