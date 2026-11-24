<?php

namespace App\Controllers\Api\Siteplan;

use App\Controllers\Api\BaseApiController;
use App\Services\Proyek\ActiveProyekService;
use App\Services\Siteplan\TargetSiteplanService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\ValidationInterface;

class TargetController extends BaseApiController
{
    protected TargetSiteplanService $targetService;
    protected ActiveProyekService $activeProyekService;
    protected ValidationInterface $validation;

    public function __construct(
        ?TargetSiteplanService $targetService = null,
        ?ActiveProyekService $activeProyekService = null
    ) {
        helper(['auth', 'active_proyek']);
        $this->targetService = $targetService ?? new TargetSiteplanService();
        $this->activeProyekService = $activeProyekService ?? new ActiveProyekService();
        $this->validation = \Config\Services::validation();
    }

    public function list(): ResponseInterface
    {
        $input = $this->getRequestData();
        $idProyek = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);

        if ($idProyek <= 0) {
            return $this->error('Proyek harus diisi', 422, ['id_proyek' => 'Proyek harus diisi']);
        }

        if ($denied = $this->authorizeProjectAccess($idProyek)) {
            return $denied;
        }

        return $this->success($this->targetService->listByProject($idProyek));
    }

    public function detail(): ResponseInterface
    {
        $input = $this->getRequestData();
        $idTarget = (int) ($input['id_target'] ?? 0);

        if ($idTarget > 0) {
            try {
                $detail = $this->targetService->get($idTarget);
                $idProyek = (int) ($detail['target']->id_proyek ?? 0);
                if ($idProyek > 0) {
                    if ($denied = $this->authorizeProjectAccess($idProyek)) {
                        return $denied;
                    }
                }

                return $this->success($detail);
            } catch (\Throwable $e) {
                $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 400;

                return $this->error($e->getMessage(), $statusCode);
            }
        }

        $idProyek = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);
        $tahunTarget = (int) ($input['tahun_target'] ?? 0);

        if ($idProyek <= 0 || $tahunTarget <= 0) {
            return $this->error(
                'Parameter id_target atau pasangan id_proyek dan tahun_target harus diisi',
                422,
                ['id_target' => 'Target atau proyek dan tahun target harus diisi']
            );
        }

        if ($tahunTarget < 2000 || $tahunTarget > 2100) {
            return $this->error('Tahun target tidak valid', 422, ['tahun_target' => 'Tahun target harus antara 2000 hingga 2100']);
        }

        if ($denied = $this->authorizeProjectAccess($idProyek)) {
            return $denied;
        }

        try {
            return $this->success($this->targetService->getByProjectAndYear($idProyek, $tahunTarget));
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function save(): ResponseInterface
    {
        if ($denied = $this->authorizeMutation()) {
            return $denied;
        }

        $input = $this->getRequestData();
        if (empty($input['id_proyek'])) {
            $headerProyek = (int) ($this->request->header('X-Project-Id')?->getValue() ?? 0);
            if ($headerProyek > 0) {
                $input['id_proyek'] = $headerProyek;
            }
        }

        $this->validation->setRules([
            'id_proyek' => [
                'label' => 'Proyek',
                'rules' => 'required|integer|greater_than[0]',
            ],
            'tahun_target' => [
                'label' => 'Tahun Target',
                'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            ],
            'deskripsi' => [
                'label' => 'Deskripsi',
                'rules' => 'permit_empty',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        if ($denied = $this->authorizeProjectAccess((int) $input['id_proyek'])) {
            return $denied;
        }

        try {
            $result = $this->targetService->save($input);

            return $this->success($result, 'Target berhasil disimpan');
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function addKavling(): ResponseInterface
    {
        if ($denied = $this->authorizeMutation()) {
            return $denied;
        }

        $input = $this->getRequestData();
        if (empty($input['id_proyek'])) {
            $headerProyek = (int) ($this->request->header('X-Project-Id')?->getValue() ?? 0);
            if ($headerProyek > 0) {
                $input['id_proyek'] = $headerProyek;
            }
        }

        $this->validation->setRules([
            'id_proyek' => [
                'label' => 'Proyek',
                'rules' => 'required|integer|greater_than[0]',
            ],
            'tahun_target' => [
                'label' => 'Tahun Target',
                'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            ],
            'id_tipe' => [
                'label' => 'Tipe Rumah',
                'rules' => 'permit_empty|integer|greater_than[0]',
            ],
            'tipe_manual' => [
                'label' => 'Tipe Manual',
                'rules' => 'permit_empty|max_length[255]',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $rawKavlingInput = ! empty($input['id_kavlings'])
            ? $input['id_kavlings']
            : ($input['id_kavling'] ?? null);
        $kavlingIds = $this->normalizeIdList($rawKavlingInput);
        if ($kavlingIds === []) {
            return $this->error(
                'Kavling harus diisi minimal 1',
                422,
                ['id_kavling' => 'Kavling harus diisi minimal 1']
            );
        }

        if ($denied = $this->authorizeProjectAccess((int) $input['id_proyek'])) {
            return $denied;
        }

        try {
            $result = $this->targetService->addKavlingToYear($input, $this->currentUserId());
            $addedCount = (int) ($result['batch_summary']['added_count'] ?? 1);
            $message = $addedCount > 1
                ? ($addedCount . ' kavling berhasil ditambahkan ke target siteplan')
                : 'Kavling berhasil ditambahkan ke target siteplan';

            return $this->success($result, $message);
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function updateKavlingTipe(): ResponseInterface
    {
        if ($denied = $this->authorizeMutation()) {
            return $denied;
        }

        $input = $this->getRequestData();

        $rawTargetKavlings = ! empty($input['id_target_kavlings'])
            ? $input['id_target_kavlings']
            : ($input['id_target_kavling'] ?? null);
        $targetKavlingIds = $this->normalizeIdList($rawTargetKavlings);
        $idTarget = (int) ($input['id_target'] ?? 0);
        $kavlingIds = $this->normalizeIdList(! empty($input['id_kavlings']) ? $input['id_kavlings'] : ($input['id_kavling'] ?? null));

        if ($targetKavlingIds === [] && ($idTarget <= 0 || $kavlingIds === [])) {
            return $this->error(
                'Parameter id_target_kavling(s) atau pasangan id_target dan id_kavling(s) wajib diisi',
                422,
                ['id_target_kavling' => 'Identitas target kavling wajib diisi']
            );
        }

        $this->validation->setRules([
            'id_tipe' => [
                'label' => 'Tipe Rumah',
                'rules' => 'permit_empty|integer|greater_than[0]',
            ],
            'tipe_manual' => [
                'label' => 'Tipe Manual',
                'rules' => 'permit_empty|max_length[255]',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $resolvedProjectIds = $this->targetService->resolveProjectIdsForTargetKavlings(
            $targetKavlingIds,
            $idTarget > 0 ? $idTarget : null
        );

        if ($resolvedProjectIds === []) {
            return $this->error('Kavling pada target tidak ditemukan', 404);
        }

        if (count($resolvedProjectIds) > 1) {
            return $this->error('Semua kavling harus berasal dari proyek yang sama', 422);
        }

        $resolvedProyekId = (int) $resolvedProjectIds[0];

        if ($denied = $this->authorizeProjectAccess($resolvedProyekId)) {
            return $denied;
        }

        try {
            $result = $this->targetService->updateKavlingTipe($input, $this->currentUserId());
            $updatedCount = (int) ($result['batch_summary']['updated_count'] ?? 1);
            $message = $updatedCount > 1
                ? ($updatedCount . ' tipe target kavling berhasil diperbarui')
                : 'Tipe target kavling berhasil diperbarui';

            return $this->success($result, $message);
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function removeKavling(): ResponseInterface
    {
        if ($denied = $this->authorizeMutation()) {
            return $denied;
        }

        $input = $this->getRequestData();

        $rawTargetKavlings = ! empty($input['id_target_kavlings'])
            ? $input['id_target_kavlings']
            : ($input['id_target_kavling'] ?? null);
        $targetKavlingIds = $this->normalizeIdList($rawTargetKavlings);
        $idTarget = (int) ($input['id_target'] ?? 0);
        $kavlingIds = $this->normalizeIdList(
            ! empty($input['id_kavlings'])
                ? $input['id_kavlings']
                : ($input['id_kavling'] ?? null)
        );

        if ($targetKavlingIds === [] && ($idTarget <= 0 || $kavlingIds === [])) {
            return $this->error(
                'Parameter id_target_kavling(s) atau pasangan id_target dan id_kavling(s) wajib diisi',
                422,
                ['id_target_kavling' => 'Identitas target kavling wajib diisi']
            );
        }

        $resolvedProjectIds = $this->targetService->resolveProjectIdsForTargetKavlings(
            $targetKavlingIds,
            $idTarget > 0 ? $idTarget : null
        );

        if ($resolvedProjectIds === []) {
            return $this->error('Kavling pada target tidak ditemukan', 404);
        }

        if (count($resolvedProjectIds) > 1) {
            return $this->error('Semua kavling harus berasal dari proyek yang sama', 422);
        }

        $resolvedProyekId = (int) $resolvedProjectIds[0];

        if ($denied = $this->authorizeProjectAccess($resolvedProyekId)) {
            return $denied;
        }

        try {
            $result = $this->targetService->removeKavling($input, $this->currentUserId());
            $removedCount = (int) ($result['batch_summary']['removed_count'] ?? 1);
            $message = $removedCount > 1
                ? ($removedCount . ' kavling berhasil dihapus dari target siteplan')
                : 'Kavling berhasil dihapus dari target siteplan';

            return $this->success($result, $message);
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function history(): ResponseInterface
    {
        $input = $this->getRequestData();
        $idTarget = (int) ($input['id_target'] ?? 0);

        if ($idTarget <= 0) {
            return $this->error('Target harus diisi', 422, ['id_target' => 'Target harus diisi']);
        }

        $resolvedProyekId = $this->targetService->resolveProjectIdForTargetKavling(null, $idTarget);
        if ($resolvedProyekId === null) {
            return $this->error('Target tidak ditemukan', 404);
        }

        if ($denied = $this->authorizeProjectAccess($resolvedProyekId)) {
            return $denied;
        }

        return $this->success($this->targetService->history($idTarget));
    }

    public function requestChange(): ResponseInterface
    {
        if ($denied = $this->authorizePlanningRequest()) {
            return $denied;
        }

        $input = $this->getRequestData();

        $idTargetKavling = (int) ($input['id_target_kavling'] ?? 0);
        $idTarget = (int) ($input['id_target'] ?? 0);
        $idKavling = (int) ($input['id_kavling'] ?? 0);

        if ($idTargetKavling <= 0 && ($idTarget <= 0 || $idKavling <= 0)) {
            return $this->error(
                'Parameter id_target_kavling atau pasangan id_target dan id_kavling wajib diisi',
                422,
                ['id_target_kavling' => 'Identitas target kavling wajib diisi']
            );
        }

        $this->validation->setRules([
            'id_tipe' => [
                'label' => 'Tipe Rumah',
                'rules' => 'permit_empty|integer|greater_than[0]',
            ],
            'new_id_tipe' => [
                'label' => 'Tipe Rumah Baru',
                'rules' => 'permit_empty|integer|greater_than[0]',
            ],
            'tipe_manual' => [
                'label' => 'Tipe Manual',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'new_tipe_manual' => [
                'label' => 'Tipe Manual Baru',
                'rules' => 'permit_empty|max_length[255]',
            ],
            'catatan_request' => [
                'label' => 'Catatan Request',
                'rules' => 'permit_empty',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $resolvedProyekId = $this->targetService->resolveProjectIdForTargetKavling(
            $idTargetKavling > 0 ? $idTargetKavling : null,
            $idTarget > 0 ? $idTarget : null
        );

        if ($resolvedProyekId === null) {
            return $this->error('Kavling pada target tidak ditemukan', 404);
        }

        if ($denied = $this->authorizeProjectAccess($resolvedProyekId)) {
            return $denied;
        }

        try {
            $result = $this->targetService->requestTipeChange($input, $this->currentUserId());

            return $this->success($result, 'Pengajuan ubah tipe target berhasil dikirim');
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function requests(): ResponseInterface
    {
        if ($denied = $this->authorizeRequestList()) {
            return $denied;
        }

        $input = $this->getRequestData();
        $idTarget = isset($input['id_target']) && (int) $input['id_target'] > 0 ? (int) $input['id_target'] : null;
        $tahunTarget = isset($input['tahun_target']) && (int) $input['tahun_target'] > 0 ? (int) $input['tahun_target'] : null;
        $status = isset($input['status']) && trim((string) $input['status']) !== '' ? trim((string) $input['status']) : null;

        $idProyek = (int) ($input['id_proyek'] ?? $this->request->header('X-Project-Id')?->getValue() ?? 0);
        if ($idProyek <= 0 && $idTarget !== null) {
            $idProyek = (int) ($this->targetService->resolveProjectIdForTargetKavling(null, $idTarget) ?? 0);
        }

        if ($idProyek <= 0) {
            return $this->error('Proyek harus diisi', 422, ['id_proyek' => 'Proyek harus diisi']);
        }

        if ($denied = $this->authorizeProjectAccess($idProyek)) {
            return $denied;
        }

        try {
            return $this->success($this->targetService->listRequests($idProyek, $idTarget, $tahunTarget, $status));
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    public function reviewRequest(): ResponseInterface
    {
        if ($denied = $this->authorizeMutation()) {
            return $denied;
        }

        $input = $this->getRequestData();
        if (isset($input['decision'])) {
            $input['decision'] = strtolower(trim((string) $input['decision']));
        }

        $this->validation->setRules([
            'id_request' => [
                'label' => 'ID Request',
                'rules' => 'required|integer|greater_than[0]',
            ],
            'decision' => [
                'label' => 'Keputusan Review',
                'rules' => 'required|in_list[approve,approved,reject,rejected]',
            ],
            'catatan_approval' => [
                'label' => 'Catatan Approval',
                'rules' => 'permit_empty',
            ],
        ]);

        if (! $this->validation->run($input)) {
            $errors = $this->validation->getErrors();
            $message = implode(' ', array_values($errors)) ?: 'Validasi gagal.';

            return $this->error($message, 422, $errors);
        }

        $idRequest = (int) $input['id_request'];
        $resolvedProyekId = $this->targetService->resolveProjectIdForTargetRequest($idRequest);
        if ($resolvedProyekId === null) {
            return $this->error('Request ubah tipe target tidak ditemukan', 404);
        }

        if ($denied = $this->authorizeProjectAccess($resolvedProyekId)) {
            return $denied;
        }

        try {
            $result = $this->targetService->reviewRequest(
                $idRequest,
                (string) $input['decision'],
                isset($input['catatan_approval']) ? (string) $input['catatan_approval'] : null,
                $this->currentUserId()
            );

            $isApproved = strtolower((string) ($result['request']->status ?? '')) === 'approved';
            $message = $isApproved
                ? 'Pengajuan ubah tipe target disetujui'
                : 'Pengajuan ubah tipe target ditolak';

            return $this->success($result, $message);
        } catch (\Throwable $e) {
            $statusCode = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422;

            return $this->error($e->getMessage(), $statusCode);
        }
    }

    private function authorizeMutation(): ?ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId <= 0 || ! function_exists('in_groups') || ! in_groups(['1', '4'])) {
            return $this->error('Anda tidak memiliki izin untuk mengelola target siteplan.', 403);
        }

        return null;
    }

    private function authorizePlanningRequest(): ?ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId <= 0 || ! function_exists('in_groups') || ! in_groups(['1', '6'])) {
            return $this->error('Anda tidak memiliki izin untuk mengajukan perubahan tipe target siteplan.', 403);
        }

        return null;
    }

    private function authorizeRequestList(): ?ResponseInterface
    {
        $userId = $this->currentUserId();
        if ($userId <= 0 || ! function_exists('in_groups') || ! in_groups(['1', '4', '6', '11'])) {
            return $this->error('Anda tidak memiliki izin untuk melihat daftar pengajuan target siteplan.', 403);
        }

        return null;
    }

    private function authorizeProjectAccess(int $idProyek): ?ResponseInterface
    {
        if (function_exists('in_groups') && in_groups(['1'])) {
            return null;
        }

        $userId = $this->currentUserId();
        if ($userId <= 0 || ! $this->activeProyekService->userCanAccess($idProyek, $userId)) {
            return $this->error('Anda tidak memiliki akses ke proyek ini.', 403);
        }

        return null;
    }

    private function currentUserId(): int
    {
        return function_exists('user_id') && user_id() ? (int) user_id() : 0;
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

    /**
     * @return int[]
     */
    private function normalizeIdList(mixed $raw): array
    {
        if (is_int($raw) || is_float($raw)) {
            $raw = [(int) $raw];
        } elseif (is_string($raw)) {
            $raw = preg_split('/[;,]+/', $raw) ?: [];
        }

        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $item) {
            if (is_array($item) || is_object($item)) {
                continue;
            }
            $id = (int) trim((string) $item);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}
