<?php

namespace App\Services\Bpb;

use App\Enums\NotificationEvent;
use App\Models\BpbFileModel;
use App\Models\BpbHistoryModel;
use App\Models\BpbItemModel;
use App\Models\BpbRequestModel;
use App\Models\BpbSignatureModel;
use App\Models\UserSignatureProfileModel;
use App\Repositories\BpbRepository;
use App\Services\FileAccessService;
use App\Services\NotifikasiService;
use Myth\Auth\Password;
use RuntimeException;

class BpbService
{
    private $db;
    private BpbRepository $repository;
    private BpbRequestModel $requests;
    private BpbItemModel $items;
    private BpbFileModel $files;
    private BpbSignatureModel $signatures;
    private BpbHistoryModel $history;
    private UserSignatureProfileModel $profileSignatures;
    private BpbFileService $fileService;

    public function __construct()
    {
        $this->db = db_connect();
        $this->repository = new BpbRepository($this->db);
        $this->requests = new BpbRequestModel();
        $this->items = new BpbItemModel();
        $this->files = new BpbFileModel();
        $this->signatures = new BpbSignatureModel();
        $this->history = new BpbHistoryModel();
        $this->profileSignatures = new UserSignatureProfileModel();
        $this->fileService = new BpbFileService();
    }

    public function list(array $input, int $userId): array
    {
        $statusValues = array_keys(BpbWorkflow::labels());
        foreach (['status', 'history_status'] as $key) {
            $value = trim((string) ($input[$key] ?? ''));
            $input[$key] = $value !== '' && in_array($value, $statusValues, true) ? $value : '';
        }
        foreach (['date_from', 'date_to'] as $key) {
            $value = trim((string) ($input[$key] ?? ''));
            $input[$key] = $value !== '' && $this->validDate($value) ? $value : '';
        }
        if ($input['date_from'] !== '' && $input['date_to'] !== '' && $input['date_from'] > $input['date_to']) {
            [$input['date_from'], $input['date_to']] = [$input['date_to'], $input['date_from']];
        }
        $input['department'] = trim((string) ($input['department'] ?? ''));
        $input['applicant_user_id'] = max(0, (int) ($input['applicant_user_id'] ?? 0));

        $result = $this->repository->list($input, $userId);
        foreach ($result['data'] as &$row) {
            $row['status_label'] = BpbWorkflow::label($row['status']);
            $extra = max(0, ((int) $row['item_count']) - 1);
            $row['item_display'] = (string) ($row['first_item'] ?: '-') . ($extra ? " +{$extra} item" : '');
        }
        return $result;
    }

    public function options(int $userId): array
    {
        return [
            'users' => $this->repository->options($userId),
            'statuses' => BpbWorkflow::labels(),
            'filters' => $this->repository->listFilterOptions(),
        ];
    }

    public function detail(int $id, int $userId): array
    {
        $detail = $this->repository->detail($id);
        if (! $detail || ! $this->canView($detail, $userId)) {
            throw new RuntimeException('BPB tidak ditemukan atau tidak dapat diakses.');
        }
        unset($detail['submission_key']);
        $access = new FileAccessService();
        foreach ($detail['files'] as &$file) {
            $file['url'] = $access->accessUrl('bpb_file', (int) $file['id']);
            if (str_starts_with(strtolower((string) ($file['mime_type'] ?? '')), 'image/')) {
                $file['thumbnail_url'] = $access->thumbnailUrl('bpb_file', (int) $file['id']);
            }
        }
        foreach ($detail['signatures'] as &$signature) {
            unset($signature['signature_path']);
            $signature['image_url'] = site_url('bpb/signature/' . $signature['id']);
            $signature['revoked'] = $signature['revoked_at'] !== null;
        }
        $detail['status_label'] = BpbWorkflow::label($detail['status']);
        $detail['actions'] = $this->actions($detail, $userId);
        $detail['pdf_url'] = site_url('bpb/' . $id . '/pdf');
        return $detail;
    }

    public function saveDraft(array $data, array $uploads, int $userId): array
    {
        $createdPaths = [];
        $committed = false;
        $this->db->transBegin();
        try {
            $snapshot = $this->repository->userSnapshot($userId);
            $id = (int) ($data['id'] ?? 0);
            if ($id > 0) {
                $request = $this->repository->lock($id);
                $this->assertOwner($request, $userId);
                if ($request['status'] !== BpbWorkflow::DRAFT) {
                    throw new RuntimeException('Hanya draft yang dapat disimpan melalui aksi ini.');
                }
            } else {
                $this->requests->insert([
                    'applicant_user_id' => $userId, 'status' => BpbWorkflow::DRAFT,
                    'applicant_name' => $snapshot['name'], 'applicant_department' => $snapshot['department'],
                    'created_by' => $userId, 'updated_by' => $userId,
                ]);
                $id = (int) $this->requests->getInsertID();
                $request = $this->repository->lock($id);
                $this->addHistory($id, null, BpbWorkflow::DRAFT, 'draft_created', 'Draft BPB dibuat.', $snapshot);
            }
            $this->validateApprovers($data, $userId, false);
            $this->requests->update($id, [
                'cc_user_id' => $this->nullableInt($data['cc_user_id'] ?? null),
                'approver_user_id' => $this->nullableInt($data['approver_user_id'] ?? null),
                'updated_by' => $userId,
            ]);
            $items = $this->normalizeItems($data['items'] ?? [], false);
            $this->replaceItems($id, $items);
            $createdPaths = $this->syncFiles($id, 'request', $data['keep_file_ids'] ?? [], $uploads, $userId);
            $this->db->transCommit();
            $committed = true;
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            if (! $committed) {
                $this->db->transRollback();
                $this->fileService->cleanup($createdPaths);
            }
            throw $e;
        }
    }

    public function submit(array $data, array $uploads, int $userId, string $ip, string $agent): array
    {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            $key = trim((string) ($data['submission_key'] ?? ''));
            if (! preg_match('/^[a-f0-9]{64}$/i', $key)) {
                throw new RuntimeException('Kunci pengajuan tidak valid. Muat ulang form BPB lalu coba kembali.');
            }

            $existing = $this->repository->findBySubmissionKey($key);
            if ($existing) {
                if ((int) $existing['applicant_user_id'] !== $userId || $existing['status'] === BpbWorkflow::DRAFT) {
                    throw new RuntimeException('Kunci pengajuan sudah digunakan dan tidak dapat dipakai kembali.');
                }
                return $this->detail((int) $existing['id'], $userId);
            }
            $data['submission_key'] = $key;
        }
        return $this->saveSubmitted($id, $data, $uploads, $userId, $ip, $agent, false);
    }

    public function update(int $id, array $data, array $uploads, int $userId, string $ip, string $agent): array
    {
        return $this->saveSubmitted($id, $data, $uploads, $userId, $ip, $agent, true);
    }

    public function sign(int $id, array $data, int $userId, string $ip, string $agent): array
    {
        $notify = [];
        $createdPaths = [];
        $committed = false;
        $this->db->transBegin();
        try {
            $request = $this->repository->lock($id);
            $role = match ($request['status']) {
                BpbWorkflow::WAITING_CC => 'cc',
                BpbWorkflow::WAITING_APPROVER => 'approver',
                default => throw new RuntimeException('BPB tidak sedang menunggu tanda tangan.'),
            };
            $expected = $role === 'cc' ? (int) $request['cc_user_id'] : (int) $request['approver_user_id'];
            if ($expected !== $userId) {
                throw new RuntimeException('Anda bukan penanda tangan aktif BPB ini.');
            }
            $user = $this->verifyPassword($userId, (string) ($data['password'] ?? ''));
            $currentHash = $this->calculateHash($id, $request);
            if (! hash_equals((string) $request['current_document_hash'], $currentHash)) {
                throw new RuntimeException('Dokumen berubah. Muat ulang BPB sebelum menandatangani.');
            }
            $createdPaths[] = $this->createSignature($request, $role, $user, $data, $currentHash, $ip, $agent);
            $next = BpbWorkflow::afterSignature($request['status']);
            $update = ['status' => $next, 'updated_by' => $userId];
            if ($next === BpbWorkflow::APPROVED) {
                $update['approved_at'] = date('Y-m-d H:i:s');
            }
            $this->requests->update($id, $update);
            $this->addHistory($id, $request['status'], $next, 'signed', $role === 'cc' ? 'CC memberi paraf.' : 'Mengetahui menandatangani BPB.', $user);
            $this->db->transCommit();
            $committed = true;
            $notify[] = [(int) $request['applicant_user_id'], NotificationEvent::BPB_SIGNATURE_COMPLETED, "Tahap tanda tangan BPB {$request['nomor']} telah selesai oleh {$user['name']}."];
            if ($next === BpbWorkflow::WAITING_APPROVER) {
                $notify[] = [(int) $request['approver_user_id'], NotificationEvent::BPB_SIGNATURE_REQUESTED, "BPB {$request['nomor']} menunggu tanda tangan Anda."];
            } else {
                $notify[] = [(int) $request['applicant_user_id'], NotificationEvent::BPB_APPROVED, "BPB {$request['nomor']} telah disetujui."];
            }
            $this->sendNotifications($notify, $userId, $id);
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            if (! $committed) {
                $this->db->transRollback();
                $this->fileService->cleanup($createdPaths);
            }
            throw $e;
        }
    }

    public function reject(int $id, string $reason, int $userId): array
    {
        if (trim($reason) === '') {
            throw new RuntimeException('Alasan penolakan wajib diisi.');
        }
        $this->db->transBegin();
        try {
            $request = $this->repository->lock($id);
            $allowed = ($request['status'] === BpbWorkflow::WAITING_CC && (int) $request['cc_user_id'] === $userId)
                || ($request['status'] === BpbWorkflow::WAITING_APPROVER && (int) $request['approver_user_id'] === $userId);
            if (! $allowed) {
                throw new RuntimeException('Anda tidak dapat menolak BPB ini.');
            }
            $user = $this->repository->userSnapshot($userId);
            $this->requests->update($id, ['status' => BpbWorkflow::REJECTED, 'rejection_reason' => trim($reason), 'updated_by' => $userId]);
            $this->addHistory($id, $request['status'], BpbWorkflow::REJECTED, 'rejected', 'BPB ditolak: ' . trim($reason), $user);
            $this->db->transCommit();
            $this->sendNotifications([[(int) $request['applicant_user_id'], NotificationEvent::BPB_REJECTED, "BPB {$request['nomor']} ditolak: " . trim($reason)]], $userId, $id);
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function cancel(int $id, string $reason, int $userId): array
    {
        $this->db->transBegin();
        try {
            $request = $this->repository->lock($id);
            $this->assertOwner($request, $userId);
            if (! in_array($request['status'], [BpbWorkflow::WAITING_CC, BpbWorkflow::WAITING_APPROVER], true) || $this->repository->hasApproverSignature($id)) {
                throw new RuntimeException('BPB tidak dapat dibatalkan setelah approver menandatangani.');
            }
            $user = $this->repository->userSnapshot($userId);
            $this->requests->update($id, ['status' => BpbWorkflow::CANCELLED, 'cancellation_reason' => trim($reason), 'updated_by' => $userId]);
            $this->addHistory($id, $request['status'], BpbWorkflow::CANCELLED, 'cancelled', 'BPB dibatalkan.' . ($reason ? ' ' . trim($reason) : ''), $user);
            $this->db->transCommit();
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function changeStatus(int $id, array $data, array $uploads, int $userId, string $ip, string $agent): array
    {
        $createdPaths = [];
        $committed = false;
        $this->db->transBegin();
        try {
            $request = $this->repository->lock($id);
            if ((int) $request['applicant_user_id'] !== $userId && ! $this->repository->hasGroup($userId, 2)) {
                throw new RuntimeException('Hanya pembuat BPB atau Divisi Umum yang dapat mengubah status.');
            }
            $target = (string) ($data['status'] ?? '');
            if (! BpbWorkflow::canOperationalTransition($request['status'], $target)) {
                throw new RuntimeException('Transisi status tidak diizinkan.');
            }
            $user = $this->repository->userSnapshot($userId);
            $update = ['status' => $target, 'updated_by' => $userId];
            if ($target === BpbWorkflow::PROCESSED) {
                $this->verifyPassword($userId, (string) ($data['password'] ?? ''));
                $createdPaths[] = $this->createSignature($request, 'expense_verifier', $user, $data, (string) $request['current_document_hash'], $ip, $agent);
                $update['processed_at'] = date('Y-m-d H:i:s');
            } elseif ($target === BpbWorkflow::DISBURSED) {
                $amount = (float) ($data['actual_amount'] ?? 0);
                if ($amount <= 0 || trim((string) ($data['recipient_name'] ?? '')) === '' || trim((string) ($data['account_number'] ?? '')) === '') {
                    throw new RuntimeException('Nominal, nama penerima, dan nomor rekening wajib diisi.');
                }
                $update += ['actual_amount' => $amount, 'recipient_name' => trim($data['recipient_name']), 'account_number' => trim((string) $data['account_number']), 'disbursement_note' => trim((string) ($data['note'] ?? '')), 'disbursed_at' => date('Y-m-d H:i:s')];
            } elseif ($target === BpbWorkflow::PENDING) {
                if (trim((string) ($data['reason'] ?? '')) === '') {
                    throw new RuntimeException('Alasan pending wajib diisi.');
                }
                $update['pending_reason'] = trim($data['reason']);
            } elseif ($target === BpbWorkflow::PURCHASED) {
                if (! $this->validDate((string) ($data['purchase_date'] ?? ''))) {
                    throw new RuntimeException('Tanggal pembelian wajib diisi dengan benar.');
                }
                $createdPaths = $this->syncFiles($id, 'purchase', [], $uploads, $userId, false);
                if ($this->files->where(['bpb_id' => $id, 'category' => 'purchase'])->countAllResults() < 1) {
                    throw new RuntimeException('Minimal satu bukti pembelian wajib diunggah.');
                }
                $update += ['purchase_date' => $data['purchase_date'], 'purchase_note' => trim((string) ($data['note'] ?? '')), 'purchased_at' => date('Y-m-d H:i:s')];
            }
            $this->requests->update($id, $update);
            $this->addHistory($id, $request['status'], $target, 'status_changed', 'Status diubah menjadi ' . BpbWorkflow::label($target) . '.', $user, $data);
            $this->db->transCommit();
            $committed = true;
            $this->sendNotifications([[(int) $request['applicant_user_id'], NotificationEvent::BPB_STATUS_CHANGED, "Status BPB {$request['nomor']} menjadi " . BpbWorkflow::label($target) . '.']], $userId, $id);
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            if (! $committed) {
                $this->db->transRollback();
                $this->fileService->cleanup($createdPaths);
            }
            throw $e;
        }
    }

    public function signaturePath(int $signatureId, int $userId): string
    {
        $signature = $this->signatures->find($signatureId);
        if (! $signature) {
            throw new RuntimeException('Tanda tangan tidak ditemukan.');
        }
        $request = $this->repository->detail((int) $signature['bpb_id']);
        if (! $request || ! $this->canView($request, $userId)) {
            throw new RuntimeException('Tanda tangan tidak dapat diakses.');
        }
        return (new FileAccessService())->privatePath($signature['signature_path']);
    }

    private function saveSubmitted(int $id, array $data, array $uploads, int $userId, string $ip, string $agent, bool $editing): array
    {
        $isNewSubmission = ! $editing && $id <= 0;
        $submissionKey = trim((string) ($data['submission_key'] ?? ''));
        $createdPaths = [];
        $removedPaths = [];
        $notify = [];
        $committed = false;
        $this->db->transBegin();
        try {
            if ($id <= 0) {
                $snapshot = $this->repository->userSnapshot($userId);
                $inserted = $this->requests->insert([
                    'applicant_user_id' => $userId, 'status' => BpbWorkflow::DRAFT,
                    'applicant_name' => $snapshot['name'], 'applicant_department' => $snapshot['department'],
                    'submission_key' => $submissionKey !== '' ? $submissionKey : null,
                    'created_by' => $userId, 'updated_by' => $userId,
                ]);
                if ($inserted === false) {
                    $error = $this->db->error();
                    if ($isNewSubmission && (int) ($error['code'] ?? 0) === 1062) {
                        throw new RuntimeException('Duplicate entry for uq_bpb_requests_submission_key');
                    }
                    throw new RuntimeException('Gagal membuat data BPB.');
                }
                $id = (int) $this->requests->getInsertID();
                if ($id <= 0) {
                    throw new RuntimeException('Gagal membuat data BPB.');
                }
                $request = $this->repository->lock($id);
                $this->addHistory($id, null, BpbWorkflow::DRAFT, 'draft_created', 'Draft BPB dibuat.', $snapshot);
            } else {
                $request = $this->repository->lock($id);
            }
            $previousStatus = $request['status'];
            $this->assertOwner($request, $userId);
            if ($editing) {
                if (! in_array($request['status'], [BpbWorkflow::WAITING_CC, BpbWorkflow::WAITING_APPROVER], true) || $this->repository->hasApproverSignature($id)) {
                    throw new RuntimeException('BPB sudah terkunci dan tidak dapat diubah.');
                }
            } elseif ($request['status'] !== BpbWorkflow::DRAFT) {
                throw new RuntimeException('Hanya draft yang dapat diajukan.');
            }
            if (! filter_var($data['consent'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                throw new RuntimeException('Persetujuan tanda tangan wajib dicentang.');
            }
            $user = $this->verifyPassword($userId, (string) ($data['password'] ?? ''));
            $this->validateApprovers($data, $userId, true);
            $items = $this->normalizeItems($data['items'] ?? [], true);
            $this->replaceItems($id, $items);
            $sync = $this->syncFilesDetailed($id, 'request', $data['keep_file_ids'] ?? [], $uploads, $userId);
            $createdPaths = $sync['created'];
            $removedPaths = $sync['removed'];
            if ($this->files->where(['bpb_id' => $id, 'category' => 'request'])->countAllResults() < 1) {
                throw new RuntimeException('Minimal satu lampiran pengajuan wajib tersedia.');
            }
            $now = date('Y-m-d H:i:s');
            $update = [
                'applicant_name' => $user['name'], 'applicant_department' => $user['department'],
                'cc_user_id' => $this->nullableInt($data['cc_user_id'] ?? null),
                'approver_user_id' => (int) $data['approver_user_id'], 'updated_by' => $userId,
            ];
            if (! $editing) {
                $number = $this->repository->nextNumber((int) date('Y'), (int) date('n'));
                $update += [
                    'sequence_no' => $number['sequence'], 'sequence_year' => $number['year'],
                    'nomor' => BpbNumberFormatter::format($number['sequence'], $number['month'], $number['year']),
                    'verification_token' => bin2hex(random_bytes(32)), 'submitted_at' => $now,
                ];
            }
            $update['status'] = BpbWorkflow::approvalStatus($update['cc_user_id'] !== null);
            $this->requests->update($id, $update);
            $request = array_merge($request, $update);
            $hash = $this->calculateHash($id, $request);
            $this->requests->update($id, ['current_document_hash' => $hash]);
            if ($editing) {
                $this->signatures->where(['bpb_id' => $id, 'role' => 'applicant', 'revoked_at' => null])->set([
                    'revoked_at' => $now, 'revoked_by' => $userId, 'revoked_reason' => 'Dokumen direvisi oleh pemohon.',
                ])->update();
            }
            $createdPaths[] = $this->createSignature($request, 'applicant', $user, $data, $hash, $ip, $agent);
            $this->addHistory($id, $previousStatus, $update['status'], $editing ? 'revised' : 'submitted', $editing ? 'BPB direvisi dan ditandatangani ulang oleh pemohon.' : 'BPB diajukan.', $user);
            $this->db->transCommit();
            $committed = true;
            $this->fileService->cleanup($removedPaths);
            $recipient = $update['cc_user_id'] ?: $update['approver_user_id'];
            $notify[] = [(int) $recipient, NotificationEvent::BPB_SIGNATURE_REQUESTED, "BPB {$request['nomor']} menunggu tanda tangan Anda."];
            $this->sendNotifications($notify, $userId, $id);
            return $this->detail($id, $userId);
        } catch (\Throwable $e) {
            if (! $committed) {
                $this->db->transRollback();
                $this->fileService->cleanup($createdPaths);
            }
            if ($isNewSubmission && $this->isSubmissionKeyConflict($e)) {
                $existing = $this->repository->findBySubmissionKey($submissionKey);
                if ($existing && (int) $existing['applicant_user_id'] === $userId && $existing['status'] !== BpbWorkflow::DRAFT) {
                    return $this->detail((int) $existing['id'], $userId);
                }
                if ($existing) {
                    throw new RuntimeException('Kunci pengajuan sudah digunakan dan tidak dapat dipakai kembali.', 0, $e);
                }
            }
            throw $e;
        }
    }

    private function isSubmissionKeyConflict(\Throwable $error): bool
    {
        do {
            $message = strtolower($error->getMessage());
            if (str_contains($message, 'uq_bpb_requests_submission_key')
                || (str_contains($message, 'duplicate entry') && str_contains($message, 'submission_key'))
                || (int) $error->getCode() === 1062) {
                return true;
            }
            $error = $error->getPrevious();
        } while ($error !== null);

        return false;
    }

    private function createSignature(array $request, string $role, array $user, array $data, string $hash, string $ip, string $agent): string
    {
        $method = ($data['signature_method'] ?? '') === 'profile' ? 'profile' : 'canvas';
        if ($method === 'profile') {
            $profile = $this->profileSignatures->find((int) $user['id']);
            if (! $profile) {
                throw new RuntimeException('Tanda tangan profil belum tersedia.');
            }
            $path = $this->fileService->copyPrivate($profile['signature_path'], 'bpb/' . $request['id'] . '/signatures');
        } else {
            $path = $this->fileService->storeCanvas((string) ($data['signature_data'] ?? ''), 'bpb/' . $request['id'] . '/signatures');
        }
        $this->signatures->insert([
            'bpb_id' => $request['id'], 'role' => $role, 'signer_user_id' => $user['id'],
            'signer_name' => $user['name'], 'signer_department' => $user['department'], 'signer_level' => $user['level'],
            'method' => $method, 'signature_path' => $path, 'document_hash' => $hash,
            'ip_address' => substr($ip, 0, 45), 'user_agent' => substr($agent, 0, 500), 'signed_at' => date('Y-m-d H:i:s'),
        ]);
        return $path;
    }

    private function normalizeItems(mixed $items, bool $required): array
    {
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        if (! is_array($items)) {
            $items = [];
        }
        $normalized = [];
        foreach ($items as $item) {
            $name = trim((string) ($item['nama_barang'] ?? ''));
            $qty = (float) ($item['jumlah'] ?? 0);
            $unit = trim((string) ($item['satuan'] ?? ''));
            if ($name === '' && ! $required) {
                continue;
            }
            if ($name === '' || $qty <= 0 || ($required && $unit === '')) {
                throw new RuntimeException('Setiap item wajib memiliki nama barang, jumlah positif, dan satuan.');
            }
            if (mb_strlen($unit) > 50) {
                throw new RuntimeException('Satuan maksimal 50 karakter.');
            }
            $normalized[] = ['nama_barang' => $name, 'jumlah' => $qty, 'satuan' => $unit !== '' ? $unit : null, 'keterangan' => trim((string) ($item['keterangan'] ?? ''))];
        }
        if ($required && $normalized === []) {
            throw new RuntimeException('Minimal satu item wajib diisi.');
        }
        return $normalized;
    }

    private function replaceItems(int $id, array $items): void
    {
        $this->items->where('bpb_id', $id)->delete();
        foreach ($items as $index => $item) {
            $this->items->insert(['bpb_id' => $id, 'item_order' => $index + 1] + $item);
        }
    }

    private function syncFiles(int $id, string $category, mixed $keepIds, array $uploads, int $userId, bool $removeMissing = true): array
    {
        return $this->syncFilesDetailed($id, $category, $keepIds, $uploads, $userId, $removeMissing)['created'];
    }

    private function syncFilesDetailed(int $id, string $category, mixed $keepIds, array $uploads, int $userId, bool $removeMissing = true): array
    {
        if (is_string($keepIds)) {
            $keepIds = array_filter(array_map('intval', explode(',', $keepIds)));
        }
        $keepIds = array_map('intval', is_array($keepIds) ? $keepIds : []);
        $existing = $this->files->where(['bpb_id' => $id, 'category' => $category])->findAll();
        $removed = [];
        if ($removeMissing) {
            foreach ($existing as $file) {
                if (! in_array((int) $file['id'], $keepIds, true)) {
                    $this->files->delete($file['id']);
                    $removed[] = $file['logical_path'];
                }
            }
        }
        $count = $this->files->where(['bpb_id' => $id, 'category' => $category])->countAllResults();
        $rows = $this->fileService->storeUploads($uploads, $id, $category, $userId, $count);
        foreach ($rows as $row) {
            $this->files->insert($row);
        }
        return ['created' => array_column($rows, 'logical_path'), 'removed' => $removed];
    }

    private function validateApprovers(array $data, int $userId, bool $required): void
    {
        $cc = $this->nullableInt($data['cc_user_id'] ?? null);
        $approver = $this->nullableInt($data['approver_user_id'] ?? null);
        if ($required && ! $approver) {
            throw new RuntimeException('Penanda tangan Mengetahui wajib dipilih.');
        }
        if ($cc === $userId || $approver === $userId || ($cc && $cc === $approver)) {
            throw new RuntimeException('Pemohon, CC, dan Mengetahui harus pengguna yang berbeda.');
        }
        if (($cc && ! $this->repository->isActiveUser($cc)) || ($approver && ! $this->repository->isActiveUser($approver))) {
            throw new RuntimeException('CC atau Mengetahui harus merupakan pengguna aktif.');
        }
    }

    private function verifyPassword(int $userId, string $password): array
    {
        $user = $this->repository->userSnapshot($userId);
        if ($password === '' || ! Password::verify($password, (string) $user['password_hash'])) {
            throw new RuntimeException('Password saat ini tidak sesuai.');
        }
        return $user;
    }

    private function calculateHash(int $id, array $request): string
    {
        $items = $this->items->where('bpb_id', $id)->orderBy('item_order')->findAll();
        $files = $this->files->where(['bpb_id' => $id, 'category' => 'request'])->findAll();
        $access = new FileAccessService();
        foreach ($files as $file) {
            $absolute = $access->privatePath($file['logical_path']);
            $actual = is_file($absolute) ? hash_file('sha256', $absolute) : false;
            if ($actual === false || ! hash_equals((string) $file['file_sha256'], $actual)) {
                throw new RuntimeException('Integritas lampiran BPB tidak dapat diverifikasi.');
            }
        }
        return BpbDocumentHasher::hash($request, $items, $files);
    }

    private function addHistory(int $id, ?string $from, string $to, string $action, string $summary, array $user, array $metadata = []): void
    {
        unset($metadata['password'], $metadata['signature_data']);
        $this->history->insert([
            'bpb_id' => $id, 'from_status' => $from, 'to_status' => $to, 'action' => $action,
            'summary' => substr($summary, 0, 500), 'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'actor_user_id' => $user['id'], 'actor_name' => $user['name'], 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function actions(array $request, int $userId): array
    {
        $owner = (int) $request['applicant_user_id'] === $userId;
        $approverSigned = $this->repository->hasApproverSignature((int) $request['id']);
        $operational = $owner || $this->repository->hasGroup($userId, 2);
        return [
            'edit' => $owner && ($request['status'] === BpbWorkflow::DRAFT || (in_array($request['status'], [BpbWorkflow::WAITING_CC, BpbWorkflow::WAITING_APPROVER], true) && ! $approverSigned)),
            'cancel' => $owner && in_array($request['status'], [BpbWorkflow::WAITING_CC, BpbWorkflow::WAITING_APPROVER], true) && ! $approverSigned,
            'sign' => ($request['status'] === BpbWorkflow::WAITING_CC && (int) $request['cc_user_id'] === $userId) || ($request['status'] === BpbWorkflow::WAITING_APPROVER && (int) $request['approver_user_id'] === $userId),
            'reject' => ($request['status'] === BpbWorkflow::WAITING_CC && (int) $request['cc_user_id'] === $userId) || ($request['status'] === BpbWorkflow::WAITING_APPROVER && (int) $request['approver_user_id'] === $userId),
            'process' => $operational && $request['status'] === BpbWorkflow::APPROVED,
            'disburse' => $operational && $request['status'] === BpbWorkflow::PROCESSED,
            'finish' => $operational && in_array($request['status'], [BpbWorkflow::DISBURSED, BpbWorkflow::PENDING], true),
        ];
    }

    private function canView(array $request, int $userId): bool
    {
        if ($request['status'] === BpbWorkflow::DRAFT) {
            return (int) $request['applicant_user_id'] === $userId;
        }
        return true;
    }

    private function assertOwner(array $request, int $userId): void
    {
        if ((int) $request['applicant_user_id'] !== $userId) {
            throw new RuntimeException('Hanya pembuat BPB yang dapat melakukan aksi ini.');
        }
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = (int) $value;
        return $value > 0 ? $value : null;
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }

    private function sendNotifications(array $notifications, int $actorUserId, int $bpbId): void
    {
        foreach ($notifications as [$recipientId, $event, $message]) {
            if ($recipientId <= 0) {
                continue;
            }
            try {
                (new NotifikasiService())->tambah_notif_user($recipientId, $message, $actorUserId, null, null, $event, null, '/bpb?open=' . $bpbId, $event);
            } catch (\Throwable $e) {
                log_message('error', 'BPB notification failed: {message}', ['message' => $e->getMessage()]);
            }
        }
    }
}
