<?php

namespace App\Repositories\Keuangan;

use CodeIgniter\Database\BaseConnection;

class PenagihanRepository
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getDb(): BaseConnection
    {
        return $this->db;
    }

    public function getKavlingForPayment(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('
                kavling.*,
                konsumen.nama_konsumen,
                jalan.nama_jalan,
                cluster.id_cluster,
                cluster.nama_cluster,
                tipe.id_tipe,
                tipe.tipe_rumah,
                tipe.no_tipe_rumah,
                tipe.id_gambar_kerja
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('mkdt', 'kavling.id_mkdt = mkdt.id_mkdt')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    public function getMkdtRow(int $idMkdt): ?object
    {
        return $this->db->table('mkdt')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();
    }

    public function isMkdtBookingPaid(int $idMkdt): bool
    {
        $row = $this->db->table('mkdt')
            ->select('booking_paid')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getRow();

        return $row ? ! empty($row->booking_paid) : false;
    }

    public function updateMkdt(int $idMkdt, array $data): bool
    {
        $payload = $data;
        if (! isset($payload['updated_at'])) {
            $payload['updated_at'] = date('Y-m-d H:i:s');
        }

        return (bool) $this->db->table('mkdt')
            ->where('id_mkdt', $idMkdt)
            ->update($payload);
    }

    public function insertLogPembayaran(array $data): bool
    {
        $now = date('Y-m-d H:i:s');
        $payload = $data;
        $payload['created_at'] = $payload['created_at'] ?? $now;
        $payload['updated_at'] = $payload['updated_at'] ?? $now;

        return (bool) $this->db->table('log_pembayaran')->insert($payload);
    }

    public function getProfilePerusahaan(): ?object
    {
        return $this->db->table('profile_perusahaan')->get()->getRow();
    }

    public function getMkdtWithKonsumen(int $idMkdt): ?object
    {
        return $this->db->table('mkdt')
            ->select('
                mkdt.*,
                konsumen.nama_konsumen,
                konsumen.nik as nik_konsumen,
                konsumen.hp_konsumen,
                konsumen.alamat_konsumen,
                konsumen.status_konsumen
            ')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->get()
            ->getRow();
    }

    public function getKeuanganByMkdt(int $idMkdt): array
    {
        return $this->db->table('keuangan')
            ->where('id_mkdt', $idMkdt)
            ->get()
            ->getResult();
    }

    public function getLogPembayaranWithUserByMkdt(int $idMkdt): array
    {
        return $this->db->table('log_pembayaran')
            ->select('
                log_pembayaran.*,
                users.username
            ')
            ->join('users', 'users.id = log_pembayaran.add_by')
            ->where('log_pembayaran.id_mkdt', $idMkdt)
            ->get()
            ->getResult();
    }

    public function getInvoicesByMkdt(int $idMkdt): array
    {
        return $this->db->table('invoice_log')
            ->select('invoice_log.*, users.username as uadd_by')
            ->join('users', 'users.id = invoice_log.add_by')
            ->where('invoice_log.id_mkdt', $idMkdt)
            ->get()
            ->getResult();
    }

    public function getKodeKeuanganByMkdt(int $idMkdt): string
    {
        $proyek = $this->db->table('kavling')
            ->select('proyek.kode_keuangan')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling')
            ->where('mkdt.id_mkdt', $idMkdt)
            ->get()
            ->getRow();

        return $proyek && ! empty($proyek->kode_keuangan) ? (string) $proyek->kode_keuangan : 'XXX';
    }

    public function getRiwayatTagihan(?int $idMkdt = null, ?string $noInv = null): array
    {
        $builder = $this->db->table('invoice_log i')
            ->select('i.*, u.nama_karyawan as pembuat')
            ->join('karyawan u', 'u.id_user = i.add_by', 'left');

        if (! empty($noInv)) {
            $builder->where('i.no_inv', $noInv);
        } elseif (! empty($idMkdt)) {
            $builder->where('i.id_mkdt', $idMkdt);
        }

        $riwayat = $builder->orderBy('i.date_add', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($riwayat as &$row) {
            $logs = $this->db->table('invoice_status_log l')
                ->select('l.*, k.nama_karyawan as pembuat')
                ->join('karyawan k', 'k.id_user = l.add_by', 'left')
                ->where('l.no_inv', $row['no_inv'])
                ->orderBy('l.date_add', 'ASC')
                ->orderBy('l.id', 'ASC')
                ->get()
                ->getResultArray();

            $hasDibuat = false;
            foreach ($logs as $lg) {
                if (strtolower((string) ($lg['status'] ?? '')) === 'dibuat') {
                    $hasDibuat = true;
                    break;
                }
            }

            if (! $hasDibuat && ! empty($row['no_inv'])) {
                array_unshift($logs, [
                    'id'         => 0,
                    'no_inv'     => $row['no_inv'],
                    'status'     => 'dibuat',
                    'tanggal'    => ! empty($row['tanggal_invoice'])
                        ? $row['tanggal_invoice']
                        : date('Y-m-d', strtotime((string) ($row['date_add'] ?? 'now'))),
                    'keterangan' => 'Surat penagihan berhasil dibuat.',
                    'add_by'     => $row['add_by'] ?? null,
                    'date_add'   => $row['date_add'] ?? null,
                    'pembuat'    => ! empty($row['pembuat']) ? $row['pembuat'] : '-',
                ]);
            }

            $row['lifecycle'] = $logs;
        }
        unset($row);

        return $riwayat;
    }

    public function findDuplicateNomorSurat(string $nomorSurat, ?string $excludeNoInv = null): ?object
    {
        $builder = $this->db->table('invoice_log i')
            ->select('i.no_inv, i.nomor_surat, COALESCE(ks.nama_konsumen, mks.nama_konsumen) as nama_konsumen, COALESCE(kv.no_kavling, mkv.no_kavling) as no_kavling, COALESCE(j.nama_jalan, mj.nama_jalan) as nama_jalan, COALESCE(p.nama_proyek, mp.nama_proyek) as nama_proyek')
            ->join('konsumen ks', 'ks.id_konsumen = i.id_konsumen', 'left')
            ->join('kavling kv', 'kv.id_kavling = i.id_kavling', 'left')
            ->join('jalan j', 'j.id_jalan = kv.id_jalan', 'left')
            ->join('cluster c', 'c.id_cluster = j.id_cluster', 'left')
            ->join('proyek p', 'p.id_proyek = c.id_proyek', 'left')
            ->join('mkdt m', 'm.id_mkdt = i.id_mkdt', 'left')
            ->join('konsumen mks', 'mks.id_konsumen = m.id_konsumen', 'left')
            ->join('kavling mkv', 'mkv.id_kavling = m.id_kavling', 'left')
            ->join('jalan mj', 'mj.id_jalan = mkv.id_jalan', 'left')
            ->join('cluster mc', 'mc.id_cluster = mj.id_cluster', 'left')
            ->join('proyek mp', 'mp.id_proyek = mc.id_proyek', 'left')
            ->where('i.nomor_surat', trim($nomorSurat))
            ->where('i.status_tagihan !=', 'draft')
            ->where('i.status_tagihan !=', 'batal');

        if (! empty($excludeNoInv)) {
            $builder->where('i.no_inv !=', $excludeNoInv);
        }

        return $builder->get()->getRow();
    }

    public function findInvoiceByNoInv(string $noInv): ?object
    {
        return $this->db->table('invoice_log')
            ->where('no_inv', $noInv)
            ->get()
            ->getRow();
    }

    public function findLastInvoiceByPrefix(string $prefix): ?object
    {
        return $this->db->table('invoice_log')
            ->like('no_inv', $prefix, 'after')
            ->orderBy('no_inv', 'DESC')
            ->get()
            ->getRow();
    }

    public function insertInvoice(array $data): bool
    {
        return (bool) $this->db->table('invoice_log')->insert($data);
    }

    public function updateInvoice(string $noInv, array $data): bool
    {
        return (bool) $this->db->table('invoice_log')
            ->where('no_inv', $noInv)
            ->update($data);
    }

    public function insertInvoiceStatusLog(array $data): bool
    {
        return (bool) $this->db->table('invoice_status_log')->insert($data);
    }

    public function getProyekInfoForInvoice(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('proyek.no_rek, proyek.bank, proyek.atas_nama, proyek.nama_proyek, proyek.no_telepon, proyek.direktur_id, proyek.nama_pt, users.name as nama_direktur, user_signature_profiles.signature_path')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->join('users', 'users.id = proyek.direktur_id', 'left')
            ->join('user_signature_profiles', 'user_signature_profiles.user_id = users.id', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    public function getKonsumenSummary(int $idKonsumen): ?object
    {
        return $this->db->table('konsumen')
            ->select('konsumen.nama_konsumen, konsumen.alamat_konsumen, konsumen.hp_konsumen')
            ->where('id_konsumen', $idKonsumen)
            ->get()
            ->getRow();
    }

    public function getKavlingLocationSummary(int $idKavling): ?object
    {
        return $this->db->table('kavling')
            ->select('proyek.nama_proyek, jalan.nama_jalan, kavling.no_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow();
    }

    public function getKaryawanNameByUserId(int $userId): ?object
    {
        return $this->db->table('karyawan b')
            ->select('nama_karyawan')
            ->where('b.id_user', $userId)
            ->get()
            ->getRow();
    }

    public function getFirstKopsuratLokasi(): ?string
    {
        $row = $this->db->table('kopsurat')
            ->select('lokasi')
            ->get()
            ->getRow();

        return $row->lokasi ?? null;
    }

    public function getKopsuratListObjects(): array
    {
        return $this->db->table('kopsurat')
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();
    }

    public function getKopsuratListArrays(): array
    {
        return $this->db->table('kopsurat')
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findKopsuratObject(int $id): ?object
    {
        return $this->db->table('kopsurat')
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    public function findKopsuratArray(int $id): ?array
    {
        return $this->db->table('kopsurat')
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    public function insertKopsurat(array $data): bool
    {
        return (bool) $this->db->table('kopsurat')->insert($data);
    }

    public function updateKopsurat(int $id, array $data): bool
    {
        return (bool) $this->db->table('kopsurat')
            ->where('id', $id)
            ->update($data);
    }

    public function deleteKopsurat(int $id): bool
    {
        return (bool) $this->db->table('kopsurat')
            ->where('id', $id)
            ->delete();
    }

    public function countInvoicesUsingKopsurat(int $idKopsurat): int
    {
        return (int) $this->db->table('invoice_log')
            ->where('id_kopsurat', $idKopsurat)
            ->countAllResults();
    }
}
