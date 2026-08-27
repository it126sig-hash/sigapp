<?php

namespace App\Services;

use App\Repositories\ReferralRepository;
use App\Models\ReferralModel;
use App\Models\ReferralBonusModel;
use App\Models\KonsumenModel;

class ReferralService
{
    protected $repo;
    protected $model;
    protected $bonusModel;
    protected $konsumenModel;

    public function __construct()
    {
        $this->repo = new ReferralRepository();
        $this->model = new ReferralModel();
        $this->bonusModel = new ReferralBonusModel();
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
        $kodeReferal = strtoupper(trim($kodeReferal));
        if (empty($kodeReferal)) return ['success' => false, 'message' => 'Kode kosong'];

        // Cek referrer
        $referrer = $this->repo->findByKodeReferal($kodeReferal, $idProyek);
        if (!$referrer) {
            return ['success' => false, 'message' => 'Kode referal tidak valid atau beda proyek'];
        }

        // Cek self-referral
        // (Assuming you have logic here to check if the new MKDT belongs to the same consumer. Usually handled in controller)
        
        // Simpan referral
        $data = [
            'id_konsumen_referrer' => $referrer->id_konsumen,
            'id_mkdt_referred' => $idMkdtReferred,
            'id_proyek' => $idProyek,
            'add_by' => user_id()
        ];
        
        // Cek apakah sudah ada (karena UNIQUE id_mkdt_referred)
        $existing = $this->repo->getReferralByMkdt($idMkdtReferred);
        if ($existing) {
            $idReferral = $existing->id;
            // Update
            $this->model->update($idReferral, $data);
        } else {
            $idReferral = $this->model->insert($data, true);
        }

        return ['success' => true, 'id_referral' => $idReferral, 'referrer' => $referrer];
    }

    public function checkAndActivateBonuses(int $idMkdt, string $newStatusMkdt): void
    {
        $referral = $this->repo->getReferralByMkdt($idMkdt);
        if (!$referral) return;

        $stages = $this->repo->getBonusStagesByProyek($referral->id_proyek);
        if (empty($stages)) return;

        foreach ($stages as $stage) {
            // Jika status cocok dengan trigger
            if (strcasecmp($stage['trigger_status_mkdt'], $newStatusMkdt) === 0) {
                
                // Cek apakah bonus stage ini sudah ada untuk referral ini
                $existingBonus = $this->bonusModel->where('id_referral', $referral->id)
                                                  ->where('id_stage', $stage['id'])
                                                  ->first();
                
                if (!$existingBonus) {
                    // Create new as eligible
                    $this->bonusModel->insert([
                        'id_referral' => $referral->id,
                        'id_stage' => $stage['id'],
                        'nominal_bonus' => $stage['nominal_default'],
                        'status' => 'eligible',
                        'eligible_at' => date('Y-m-d H:i:s'),
                        'add_by' => user_id()
                    ]);
                } else if ($existingBonus->status === 'batal') {
                    // Re-activate if it was canceled
                    $this->bonusModel->update($existingBonus->id, [
                        'status' => 'eligible',
                        'eligible_at' => date('Y-m-d H:i:s'),
                        'edit_by' => user_id()
                    ]);
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

        $data = [
            'status' => 'dikonfirmasi',
            'confirmed_by' => user_id(),
            'confirmed_at' => date('Y-m-d H:i:s'),
            'edit_by' => user_id()
        ];
        if ($nominalOverride !== null) {
            $data['nominal_bonus'] = $nominalOverride;
        }

        $this->bonusModel->update($idReferralBonus, $data);
        return ['success' => true];
    }
    
    public function updateKeterangan(int $idReferralBonus, string $keterangan): array
    {
         $this->bonusModel->update($idReferralBonus, ['keterangan' => $keterangan, 'edit_by' => user_id()]);
         return ['success' => true];
    }

    public function payByPromosi(int $idReferralBonus, string $buktiPath): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        $newStatus = $bonus->status === 'diajukan_keuangan' ? 'diajukan_keuangan' : 'dibayar_promosi';
        if ($bonus->status === 'cair') $newStatus = 'selesai';

        $this->bonusModel->update($idReferralBonus, [
            'status' => $newStatus,
            'paid_by_promosi' => 1,
            'paid_promosi_at' => date('Y-m-d H:i:s'),
            'paid_promosi_by' => user_id(),
            'bukti_bayar_promosi' => $buktiPath,
            'edit_by' => user_id()
        ]);
        return ['success' => true];
    }

    public function submitToKeuangan(int $idReferralBonus, ?string $buktiPath = null): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if (!in_array($bonus->status, ['dikonfirmasi', 'dibayar_promosi'])) {
            return ['success' => false, 'message' => 'Status tidak valid'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Update bonus status
        $updateData = [
            'status' => 'diajukan_keuangan',
            'edit_by' => user_id()
        ];
        
        if ($buktiPath) {
            $updateData['bukti_pengajuan_keuangan'] = $buktiPath;
        }

        $this->bonusModel->update($idReferralBonus, $updateData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Gagal submit ke keuangan'];
        }

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
                'cair_keuangan_at' => date('Y-m-d H:i:s'),
                'edit_by' => user_id()
            ]);
        } else if ($statusKeuangan === 'ditolak') {
            $newStatus = $bonus->paid_by_promosi ? 'dibayar_promosi' : 'dikonfirmasi';
            $this->bonusModel->update($bonus->id, [
                'status' => $newStatus,
                'id_pengajuan_pencairan' => null, // reset
                'edit_by' => user_id()
            ]);
        }
    }

    public function markSelesai(int $idReferralBonus): array
    {
        $this->bonusModel->update($idReferralBonus, [
            'status' => 'selesai',
            'edit_by' => user_id()
        ]);
        return ['success' => true];
    }

    public function cancelBonus(int $idReferralBonus, string $keterangan): array
    {
        $this->bonusModel->update($idReferralBonus, [
            'status' => 'batal',
            'keterangan' => $keterangan,
            'edit_by' => user_id()
        ]);
        return ['success' => true];
    }
}
