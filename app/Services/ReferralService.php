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

    public function submitToKeuangan(int $idReferralBonus): array
    {
        $bonus = $this->bonusModel->find($idReferralBonus);
        if (!$bonus) return ['success' => false, 'message' => 'Bonus not found'];

        if (!in_array($bonus->status, ['dikonfirmasi', 'dibayar_promosi'])) {
            return ['success' => false, 'message' => 'Status tidak valid'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Ambil info referral, mkdt, konsumen untuk keperluan pengajuan
        $sql = "SELECT r.id_mkdt_referred, kv.id_kavling, k.nama_konsumen
                FROM referrals r
                JOIN mkdt mk ON mk.id_mkdt = r.id_mkdt_referred
                JOIN kavling kv ON kv.id_mkdt = mk.id_mkdt
                JOIN konsumen k ON k.id_konsumen = r.id_konsumen_referrer
                WHERE r.id = ?";
        $info = $db->query($sql, [$bonus->id_referral])->getRow();

        // Create record di pengajuan_pencairan
        // Note: Asumsi tabel pengajuan_pencairan sudah ada dan bisa insert
        // Kita perlu generate kode pengajuan
        $kode = 'PRM-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $dataPengajuan = [
            'kode_pengajuan' => $kode,
            'departemen_asal' => 'promosi',
            'id_user_pengaju' => user_id(),
            'id_kavling' => $info->id_kavling,
            'jenis_biaya' => 'referral_bonus',
            'id_referensi' => $bonus->id,
            'keperluan' => "Pencairan Bonus Referral a/n " . $info->nama_konsumen,
            'nominal_diajukan' => $bonus->nominal_bonus,
            'tanggal_pengajuan' => date('Y-m-d'),
            'status' => 'diajukan'
        ];
        
        $db->table('pengajuan_pencairan')->insert($dataPengajuan);
        $idPengajuan = $db->insertID();

        // Update bonus status
        $this->bonusModel->update($idReferralBonus, [
            'status' => 'diajukan_keuangan',
            'id_pengajuan_pencairan' => $idPengajuan,
            'edit_by' => user_id()
        ]);

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
