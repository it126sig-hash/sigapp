<?php

namespace App\Repositories\Keuangan;

use CodeIgniter\Model;

class PajakRepository extends Model
{
    protected $table = 'pajak';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_mkdt',
        'pph42_tarif',
        'pph42_nilai',
        'pph42_id_billing',
        'pph42_ntpn',
        'pph42_tgl_bayar',
        'pph42_keterangan',
        'ppn_tarif',
        'ppn_nilai',
        'ppn_id_billing',
        'ppn_ntpn',
        'ppn_tgl_bayar',
        'ppn_keterangan',
        'ppn_no_faktur',
        'add_by',
        'edit_by',
    ];
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function linkKavling(int $idKavling, int $idPajak): bool
    {
        return $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->update(['id_pajak' => $idPajak]);
    }

    public function getPajakBundleByKavling(int $idKavling): array
    {
        $row = $this->db->table('kavling')
            ->select('
                konsumen.nama_konsumen,
                konsumen.npwp,
                konsumen.alamat_konsumen,
                konsumen.file_npwp,
                konsumen.hp_konsumen,
                legal.pbb_pecah_nop,
                mkdt.harga_ppn,
                mkdt.harga_jual_net,
                mkdt.akad_tgl,
                mkdt.is_subsidi,
                pajak.*,
                c.username as uadd_by,
                d.username as uedit_by
            ')
            ->join('mkdt', 'kavling.id_mkdt = mkdt.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('legal', 'legal.id_legal = kavling.id_legal', 'left')
            ->join('pajak', 'pajak.id = kavling.id_pajak', 'left')
            ->join('users as c', 'c.id = pajak.add_by', 'left')
            ->join('users as d', 'd.id = pajak.edit_by', 'left')
            ->where('kavling.id_kavling', $idKavling)
            ->get()
            ->getRow(0);

        return [
            'pajak'    => $row,
            'file_ajb' => $this->getLatestFilesByCategories($idKavling, [2, 6]),
            'file'     => $this->getLatestFilesByCategories($idKavling, [9, 10], 10),
            'file_ppn' => $this->getLatestFilesByCategories($idKavling, [11, 12, 13], 10),
        ];
    }

    public function getLatestFilesByCategories(int $idKavling, array $categories, ?int $idGroup = null): array
    {
        if (empty($categories)) {
            return [];
        }

        $parts = [];
        $bindings = [];

        foreach ($categories as $kategori) {
            if ($idGroup !== null) {
                $parts[] = '(SELECT
                    file_upload.*,
                    c.username as uupload_by
                    FROM `file_upload`
                    LEFT JOIN `users` AS c ON file_upload.upload_by = c.id
                    WHERE `id_kavling` = ?
                    AND `id_group` = ?
                    AND `kategori` = ?
                    ORDER BY `upload_at` DESC
                    LIMIT 1)';
                $bindings[] = $idKavling;
                $bindings[] = $idGroup;
                $bindings[] = (int) $kategori;
            } else {
                $parts[] = '(SELECT
                    file_upload.*,
                    c.username as uupload_by
                    FROM `file_upload`
                    LEFT JOIN `users` AS c ON file_upload.upload_by = c.id
                    WHERE `id_kavling` = ?
                    AND `kategori` = ?
                    ORDER BY `upload_at` DESC
                    LIMIT 1)';
                $bindings[] = $idKavling;
                $bindings[] = (int) $kategori;
            }
        }

        $sql = implode("\nUNION ALL\n", $parts);

        return $this->db->query($sql, $bindings)->getResult();
    }

    public function insertFileUpload(array $data): int
    {
        $this->db->table('file_upload')->insert($data);

        return (int) $this->db->insertID();
    }

    public function getTarifPph(): array
    {
        if (! $this->db->tableExists('pph')) {
            return [];
        }

        return $this->db->table('pph')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getTarifPpn(): array
    {
        if (! $this->db->tableExists('ppn')) {
            return [];
        }

        return $this->db->table('ppn')
            ->where('deleted_at', null)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
