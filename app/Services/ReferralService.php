<?php

namespace App\Services;

use App\Repositories\ReferralRepository;
use App\Models\ReferralModel;
use App\Models\ReferralBonusModel;
use App\Models\ReferralBonusHistoryModel;
use App\Models\KonsumenModel;

class ReferralService
{
    protected $repo;
    protected $model;
    protected $bonusModel;
    protected $historyModel;
    protected $konsumenModel;

    public function __construct()
    {
        $this->repo = new ReferralRepository();
        $this->model = new ReferralModel();
        $this->bonusModel = new ReferralBonusModel();
        $this->historyModel = new ReferralBonusHistoryModel();
        $this->konsumenModel = new KonsumenModel();
    }

    /**
     * Validasi kode referral untuk endpoint publik.
     * Tidak membutuhkan sesi login.
     *
     * @return array{valid: bool, message?: string, data?: array}
     */
    public function validateKodePublic(string $kode): array
    {
        $kode = strtoupper(trim($kode));

        if (empty($kode)) {
            return ['valid' => false, 'message' => 'Kode referral tidak boleh kosong'];
        }

        $data = $this->repo->findPublicByKode($kode);

        if (!$data) {
            return ['valid' => false, 'message' => 'Kode referral tidak valid atau tidak ditemukan'];
        }

        return [
            'valid' => true,
            'data'  => [
                'kode_referal'     => $data->kode_referal,
                'nama_konsumen'    => $data->nama_konsumen,
                'nama_proyek'      => $data->nama_proyek,
                'kavling_dimiliki' => $data->kavling_dimiliki,
            ],
        ];
    }

    public function getSubRowsByReferrer(int $idKonsumenReferrer, int $idProyek): array
    {
        $rows = $this->repo->getSubRowsByReferrer($idKonsumenReferrer, $idProyek);

        foreach ($rows as &$row) {
            foreach ([
                'bukti_bayar_promosi' => 'bukti_bayar_promosi_url',
                'bukti_pengajuan_keuangan' => 'bukti_pengajuan_keuangan_url',
                'bukti_transfer_ke_promosi' => 'bukti_transfer_ke_promosi_url',
            ] as $field => $urlField) {
                $row[$urlField] = !empty($row[$field])
                    ? (new FileAccessService())->pathUrl('mgm_bonus_file', $row[$field])
                    : null;
            }

            $row['bonus_status_label'] = $this->bonusStatusLabel($row);
            $row['promosi_status_label'] = ((int) ($row['paid_by_promosi'] ?? 0) === 1)
                ? 'Cair ke Member'
                : 'Belum cair ke Member';
            $row['keuangan_status_label'] = $this->keuanganStatusLabel($row);
        }

        return $rows;
    }

    public function generateKodeReferal(int $idKonsumen): string
    {
        $konsumen = $this->konsumenModel->find($idKonsumen);
        if ($konsumen && $konsumen->kode_referal) {
            return $konsumen->kode_referal; // already has one
        }

        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $db = \Config\Database::connect();
        
        do {
            $randomString = '';
            for ($i = 0; $i < 2; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            $kode = $randomString . str_pad($idKonsumen, 4, '0', STR_PAD_LEFT);
            $exists = $db->table('konsumen')->where('kode_referal', $kode)->countAllResults();
        } while ($exists > 0);

        $this->konsumenModel->update($idKonsumen, ['kode_referal' => $kode]);
        return $kode;
    }

    public function createReferral(int $idMkdtReferred, string $kodeReferal, int $idProyek): array
    {
        return $this->syncReferralForMkdt($idMkdtReferred, $kodeReferal, $idProyek, '');
    }

    public function syncReferralForMkdt(int $idMkdtReferred, string $kodeReferal, int $idProyek, string $statusMkdt): array
    {
        $kodeReferal = strtoupper(trim($kodeReferal));
        $existing = $this->repo->getReferralByMkdt($idMkdtReferred);

        if ($kodeReferal === '') {
            if (!$existing) {
                return ['success' => true, 'message' => 'Tidak ada kode referal'];
            }

            if ($this->isReferralLocked((int) $existing->id)) {
                return ['success' => false, 'message' => 'Kode referal tidak bisa dihapus karena bonus sudah dicairkan ke member'];
            }

            $this->deactivateReferral((int) $existing->id, 'Kode referal dikosongkan dari MKDT');
            return ['success' => true, 'id_referral' => (int) $existing->id];
        }

        // Cek referrer
        $referrer = $this->repo->findByKodeReferal($kodeReferal, $idProyek);
        if (!$referrer) {
            return ['success' => false, 'message' => 'Kode referal tidak valid atau beda proyek'];
        }

        $mkdt = $this->repo->getMkdtReferralContext($idMkdtReferred);
        if ($mkdt && (int) $mkdt->id_konsumen === (int) $referrer->id_konsumen) {
            return ['success' => false, 'message' => 'Konsumen tidak bisa menggunakan kode referal sendiri'];
        }

        if ($existing && $this->isReferralLocked((int) $existing->id) && (int) $existing->id_konsumen_referrer !== (int) $referrer->id_konsumen) {
            return ['success' => false, 'message' => 'Kode referal tidak bisa diubah karena bonus sudah dicairkan ke member'];
        }

        $data = [
            'id_konsumen_referrer' => $referrer->id_konsumen,
            'id_mkdt_referred' => $idMkdtReferred,
            'id_proyek' => $idProyek,
            'status' => 'active'
        ];

        if ($existing) {
            $idReferral = $existing->id;
            $this->model->update($idReferral, $data);
            if ((int) $existing->id_konsumen_referrer !== (int) $referrer->id_konsumen || ($existing->status ?? 'active') !== 'active') {
                $this->logHistory(null, (int) $idReferral, 'referral_sync', (array) $existing, (object) $data, [
                    'kode_referal' => $kodeReferal,
                    'old_referrer' => (int) $existing->id_konsumen_referrer,
                    'new_referrer' => (int) $referrer->id_konsumen,
                ], 'Referral disinkronkan dari form MKDT');
            }
        } else {
            $data['add_by'] = $this->actorId();
            $idReferral = $this->model->insert($data, true);
            $this->logHistory(null, (int) $idReferral, 'referral_created', null, (object) $data, [
                'kode_referal' => $kodeReferal,
            ], 'Referral dibuat dari form MKDT');
        }

        if ($statusMkdt !== '') {
            $this->checkAndActivateBonuses($idMkdtReferred, $statusMkdt);
        }

        return ['success' => true, 'id_referral' => $idReferral, 'referrer' => $referrer];
    }

    public function checkAndActivateBonuses(int $idMkdt, string $newStatusMkdt): void
    {
        $referral = $this->repo->getReferralByMkdt($idMkdt);
        if (!$referral || ($referral->status ?? 'active') !== 'active') return;

        $stages = $this->repo->getBonusStagesByProyek($referral->id_proyek);
        if (empty($stages)) return;

        foreach ($stages as $stage) {
            if ($this->isAkadStage($stage) && strcasecmp($newStatusMkdt, 'Akad') !== 0) {
                continue;
            }

            // Jika status cocok dengan trigger
            if (strcasecmp($stage['trigger_status_mkdt'], $newStatusMkdt) === 0) {
                
                // Cek apakah bonus stage ini sudah ada untuk referral ini
                $existingBonus = $this->bonusModel->where('id_referral', $referral->id)
                                                  ->where('id_stage', $stage['id'])
                                                  ->first();
                
                if (!$existingBonus) {
                    // Create new as eligible
                    $idBonus = $this->bonusModel->insert([
                        'id_referral' => $referral->id,
                        'id_stage' => $stage['id'],
                        'nominal_bonus' => $stage['nominal_default'],
                        'status' => 'eligible',
                        'eligible_at' => date('Y-m-d H:i:s'),
                        'add_by' => $this->actorId()
                    ], true);
                    $this->logHistory((int) $idBonus, (int) $referral->id, 'bonus_eligible', null, (object) [
                        'status' => 'eligible',
                        'nominal_bonus' => $stage['nominal_default'],
                    ], ['trigger_status_mkdt' => $newStatusMkdt, 'id_stage' => $stage['id']]);
                } else if ($existingBonus->status === 'batal') {
                    // Re-activate if it was canceled
                    $this->bonusModel->update($existingBonus->id, [
                        'status' => 'eligible',
                        'eligible_at' => date('Y-m-d H:i:s'),
                        'edit_by' => $this->actorId()
                    ]);
                    $after = clone $existingBonus;
                    $after->status = 'eligible';
                    $this->logHistory((int) $existingBonus->id, (int) $referral->id, 'bonus_reactivated', $existingBonus, $after, ['trigger_status_mkdt' => $newStatusMkdt]);
                }
            }
        }
    }

    public function confirmBonus(int $idReferralBonus, ?float $nominalOverride): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if ($bonus->status !== 'eligible') {
            return ['success' => false, 'message' => 'Status tidak valid untuk konfirmasi'];
        }

        $before = clone $bonus;
        $data = [
            'status' => 'dikonfirmasi',
            'confirmed_by' => $this->actorId(),
            'confirmed_at' => date('Y-m-d H:i:s'),
            'edit_by' => $this->actorId()
        ];
        if ($nominalOverride !== null) {
            $data['nominal_bonus'] = $nominalOverride;
        }

        $this->bonusModel->update($idReferralBonus, $data);
        $after = $this->bonusModel->find($idReferralBonus);
        $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'bonus_confirmed', $before, $after);
        return ['success' => true];
    }
    
    public function updateKeterangan(int $idReferralBonus, string $keterangan): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        $before = clone $bonus;
        $this->bonusModel->update($idReferralBonus, ['keterangan' => $keterangan, 'edit_by' => $this->actorId()]);
        $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'keterangan_updated', $before, $this->bonusModel->find($idReferralBonus), [], $keterangan);

        return ['success' => true];
    }

    public function payByPromosi(int $idReferralBonus, string $buktiPath): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if (!in_array($bonus->status, ['dikonfirmasi', 'dibayar_promosi', 'diajukan_keuangan', 'cair'], true)) {
            return ['success' => false, 'message' => 'Status tidak valid untuk pembayaran Promosi'];
        }

        $before = clone $bonus;
        $newStatus = $bonus->status === 'diajukan_keuangan' ? 'diajukan_keuangan' : 'dibayar_promosi';
        if ($bonus->status === 'cair') $newStatus = 'selesai';

        $this->bonusModel->update($idReferralBonus, [
            'status' => $newStatus,
            'paid_by_promosi' => 1,
            'paid_promosi_at' => date('Y-m-d H:i:s'),
            'paid_promosi_by' => $this->actorId(),
            'bukti_bayar_promosi' => $buktiPath,
            'edit_by' => $this->actorId()
        ]);
        $after = $this->bonusModel->find($idReferralBonus);
        $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'paid_by_promosi', $before, $after, [
            'bukti_bayar_promosi' => $buktiPath,
        ]);
        return ['success' => true];
    }

    public function submitToKeuangan(int $idReferralBonus, ?float $nominalPengajuan, string $tanggalSpp, ?string $buktiPath = null): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if (!in_array($bonus->status, ['dikonfirmasi', 'dibayar_promosi'])) {
            return ['success' => false, 'message' => 'Status tidak valid'];
        }

        $nominalBonus = (float) $bonus->nominal_bonus;
        $nominalPengajuan = $nominalPengajuan ?? $nominalBonus;
        if ($nominalPengajuan <= 0) {
            return ['success' => false, 'message' => 'Nominal pengajuan harus lebih dari 0'];
        }
        if ($nominalPengajuan > $nominalBonus) {
            return ['success' => false, 'message' => 'Nominal pengajuan tidak boleh melebihi nominal bonus'];
        }
        if (trim($tanggalSpp) === '') {
            return ['success' => false, 'message' => 'Tanggal SPP wajib diisi'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $before = clone $bonus;
        $updateData = [
            'status' => 'diajukan_keuangan',
            'nominal_pengajuan_keuangan' => $nominalPengajuan,
            'tanggal_spp' => $tanggalSpp,
            'submitted_keuangan_at' => date('Y-m-d H:i:s'),
            'submitted_keuangan_by' => $this->actorId(),
            'edit_by' => $this->actorId()
        ];
        
        if ($buktiPath) {
            $updateData['bukti_pengajuan_keuangan'] = $buktiPath;
        }

        $this->bonusModel->update($idReferralBonus, $updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Gagal submit ke keuangan'];
        }

        $after = $this->bonusModel->find($idReferralBonus);
        $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'submitted_to_keuangan', $before, $after, [
            'bukti_pengajuan_keuangan' => $buktiPath,
            'tanggal_spp' => $tanggalSpp,
            'nominal_pengajuan_keuangan' => $nominalPengajuan,
        ]);

        return ['success' => true];
    }

    public function markCairKeuangan(int $idReferralBonus, string $tanggalCair, ?float $nominalCair, string $buktiPath): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if ($bonus->status !== 'diajukan_keuangan') {
            return ['success' => false, 'message' => 'Status tidak valid untuk dicairkan Keuangan'];
        }

        if (trim($tanggalCair) === '') {
            return ['success' => false, 'message' => 'Tanggal cair wajib diisi'];
        }

        $nominalPengajuan = (float) ($bonus->nominal_pengajuan_keuangan ?: $bonus->nominal_bonus);
        $nominalCair = $nominalCair ?? $nominalPengajuan;
        if ($nominalCair <= 0) {
            return ['success' => false, 'message' => 'Nominal cair harus lebih dari 0'];
        }
        if ($nominalCair > $nominalPengajuan) {
            return ['success' => false, 'message' => 'Nominal cair tidak boleh melebihi nominal pengajuan'];
        }

        $before = clone $bonus;
        $newStatus = $bonus->paid_by_promosi ? 'selesai' : 'cair';

        $this->bonusModel->update($idReferralBonus, [
            'status' => $newStatus,
            'nominal_cair_keuangan' => $nominalCair,
            'tanggal_cair_keuangan' => $tanggalCair,
            'cair_keuangan_at' => date('Y-m-d H:i:s'),
            'cair_keuangan_by' => $this->actorId(),
            'bukti_transfer_ke_promosi' => $buktiPath,
            'edit_by' => $this->actorId(),
        ]);

        $after = $this->bonusModel->find($idReferralBonus);
        $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'cair_from_keuangan', $before, $after, [
            'tanggal_cair_keuangan' => $tanggalCair,
            'nominal_cair_keuangan' => $nominalCair,
            'bukti_transfer_ke_promosi' => $buktiPath,
        ]);

        return ['success' => true];
    }
    
    // Callback ketika dari keuangan mengupdate status
    public function syncFromKeuangan(int $idPengajuan, string $statusKeuangan, float $nominalCair)
    {
        $bonus = $this->bonusModel->where('id_pengajuan_pencairan', $idPengajuan)->first();
        if(!$bonus) return;
        
        if ($statusKeuangan === 'cair') {
            $newStatus = $bonus->paid_by_promosi ? 'selesai' : 'cair';
            $this->bonusModel->update($bonus->id, [
                'status' => $newStatus,
                'nominal_cair_keuangan' => $nominalCair,
                'tanggal_cair_keuangan' => date('Y-m-d'),
                'cair_keuangan_at' => date('Y-m-d H:i:s'),
                'edit_by' => $this->actorId()
            ]);
        } else if ($statusKeuangan === 'ditolak') {
            $newStatus = $bonus->paid_by_promosi ? 'dibayar_promosi' : 'dikonfirmasi';
            $this->bonusModel->update($bonus->id, [
                'status' => $newStatus,
                'id_pengajuan_pencairan' => null, // reset
                'edit_by' => $this->actorId()
            ]);
        }
    }

    public function markSelesai(int $idReferralBonus): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        $before = $bonus ? clone $bonus : null;
        $this->bonusModel->update($idReferralBonus, [
            'status' => 'selesai',
            'edit_by' => $this->actorId()
        ]);
        if ($bonus) {
            $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'marked_selesai', $before, $this->bonusModel->find($idReferralBonus));
        }
        return ['success' => true];
    }

    public function cancelBonus(int $idReferralBonus, string $keterangan): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        $before = $bonus ? clone $bonus : null;
        $this->bonusModel->update($idReferralBonus, [
            'status' => 'batal',
            'keterangan' => $keterangan,
            'edit_by' => $this->actorId()
        ]);
        if ($bonus) {
            $this->logHistory($idReferralBonus, (int) $bonus->id_referral, 'bonus_canceled', $before, $this->bonusModel->find($idReferralBonus), [], $keterangan);
        }
        return ['success' => true];
    }

    private function deactivateReferral(int $idReferral, string $note): void
    {
        $referral = $this->model->find($idReferral);
        $this->model->update($idReferral, ['status' => 'inactive']);

        $bonuses = $this->bonusModel->where('id_referral', $idReferral)->findAll();
        foreach ($bonuses as $bonus) {
            if ((int) ($bonus->paid_by_promosi ?? 0) === 1) {
                continue;
            }

            $before = clone $bonus;
            $this->bonusModel->update($bonus->id, [
                'status' => 'batal',
                'keterangan' => trim(($bonus->keterangan ? $bonus->keterangan . "\n" : '') . $note),
                'edit_by' => $this->actorId(),
            ]);
            $this->logHistory((int) $bonus->id, $idReferral, 'bonus_canceled_by_referral_sync', $before, $this->bonusModel->find($bonus->id), [], $note);
        }

        $this->logHistory(null, $idReferral, 'referral_deactivated', $referral, (object) ['status' => 'inactive'], [], $note);
    }

    private function isReferralLocked(int $idReferral): bool
    {
        return $this->bonusModel
            ->where('id_referral', $idReferral)
            ->groupStart()
                ->where('paid_by_promosi', 1)
                ->orWhereIn('status', ['dibayar_promosi', 'selesai'])
            ->groupEnd()
            ->countAllResults() > 0;
    }

    private function logHistory(?int $idBonus, ?int $idReferral, string $action, $before = null, $after = null, array $payload = [], ?string $note = null): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('referral_bonus_histories')) {
            return;
        }

        $beforeObj = is_array($before) ? (object) $before : $before;
        $afterObj = is_array($after) ? (object) $after : $after;

        $this->historyModel->insert([
            'id_bonus' => $idBonus,
            'id_referral' => $idReferral,
            'action' => $action,
            'old_status' => $beforeObj->status ?? null,
            'new_status' => $afterObj->status ?? null,
            'old_nominal_bonus' => isset($beforeObj->nominal_bonus) ? (float) $beforeObj->nominal_bonus : null,
            'new_nominal_bonus' => isset($afterObj->nominal_bonus) ? (float) $afterObj->nominal_bonus : null,
            'nominal_pengajuan_keuangan' => $payload['nominal_pengajuan_keuangan'] ?? ($afterObj->nominal_pengajuan_keuangan ?? null),
            'nominal_cair_keuangan' => $payload['nominal_cair_keuangan'] ?? ($afterObj->nominal_cair_keuangan ?? null),
            'payload_json' => $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
            'note' => $note,
            'add_by' => $this->actorId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function isAkadStage(array $stage): bool
    {
        return stripos((string) ($stage['nama_tahapan'] ?? ''), 'akad') !== false;
    }

    private function actorId(): ?int
    {
        return function_exists('user_id') && user_id() ? (int) user_id() : null;
    }

    private function bonusStatusLabel(array $row): string
    {
        return match ((string) ($row['bonus_status'] ?? '')) {
            'diajukan_keuangan' => 'Diajukan Keuangan',
            'dibayar_promosi' => 'Cair Dari Promosi',
            'cair', 'selesai' => 'Cair Dari Keuangan',
            'batal' => 'Batal',
            default => 'Belum diajukan',
        };
    }

    private function keuanganStatusLabel(array $row): string
    {
        $status = (string) ($row['bonus_status'] ?? '');
        if (in_array($status, ['cair', 'selesai'], true) || !empty($row['cair_keuangan_at'])) {
            return 'Sudah cair';
        }
        if ($status === 'diajukan_keuangan') {
            return 'Sedang diajukan';
        }

        return 'Belum cair';
    }
}
