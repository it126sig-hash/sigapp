<?php

namespace App\Repositories\Legal;

use App\Models\LegalModel;

class LegalRepository
{
    protected $db;
    protected $legalModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->legalModel = new LegalModel();
    }

    public function getProyekOptions(): array
    {
        return $this->db->table('proyek')
            ->select('id_proyek, nama_proyek, kode_keuangan as alias')
            ->orderBy('nama_proyek', 'ASC')
            ->get()
            ->getResult();
    }

    public function getClusterOptionsByProyek(?int $idProyek): array
    {
        if (!$idProyek || $idProyek <= 0) {
            return [];
        }

        return $this->db->table('cluster')
            ->select('id_cluster, nama_cluster, id_proyek')
            ->where('id_proyek', $idProyek)
            ->orderBy('nama_cluster', 'ASC')
            ->get()
            ->getResult();
    }

    public function getJalanOptionsByClusterIds(array $clusterIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $clusterIds), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            return [];
        }

        return $this->db->table('jalan')
            ->select('id_jalan, nama_jalan, id_cluster')
            ->whereIn('id_cluster', $ids)
            ->orderBy('nama_jalan', 'ASC')
            ->get()
            ->getResult();
    }

    public function getKavlingLegalId(int $idKavling): ?int
    {
        if ($idKavling <= 0) {
            return null;
        }

        $row = $this->db->table('kavling')
            ->select('id_legal')
            ->where('id_kavling', $idKavling)
            ->get()
            ->getRow();

        if (!$row || empty($row->id_legal)) {
            return null;
        }

        return (int) $row->id_legal;
    }

    public function getLegalDataById($id_legal)
    {
        if (!$id_legal) {
            return null;
        }

        return $this->legalModel->where('id_legal', $id_legal)->first();
    }

    public function insertLegal(array $data)
    {
        if ($this->legalModel->insert($data)) {
            return $this->legalModel->getInsertID();
        }

        return false;
    }

    public function updateLegal(int $idLegal, array $data): bool
    {
        return (bool) $this->legalModel->update($idLegal, $data);
    }

    public function updateKavlingLegal(int $idKavling, int $idLegal): bool
    {
        return (bool) $this->db->table('kavling')
            ->where('id_kavling', $idKavling)
            ->update(['id_legal' => $idLegal]);
    }

    public function getKavlingLegalTaxes($id_kavling)
    {
        return $this->db->table('kavling')
            ->select('
                konsumen.nama_konsumen,
                mkdt.harga_bphtb,
                pajak.pph42_id_billing,
                pajak.pph42_ntpn,
                pajak.pph42_nilai,
                pajak.pph42_tgl_bayar
            ')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('pajak', 'pajak.id_mkdt = mkdt.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->where('kavling.id_kavling', $id_kavling)
            ->get()->getRow();
    }

    public function getFileDocById($id)
    {
        return $this->db->table('file_upload')
            ->select('lokasi')
            ->where('id', $id)
            ->get()->getRow();
    }

    public function deleteFileDoc($id)
    {
        return $this->db->table('file_upload')->where('id', $id)->delete();
    }

    public function getFileDocsByKavling($id_kavling, $id_group = 5)
    {
        return $this->db->table('file_upload')
            ->select('
                file_upload.*,
                u.username as uadd_by
            ')
            ->join('users as u', 'u.id = file_upload.upload_by', 'left')
            ->where('id_kavling', $id_kavling)
            ->where('id_group', $id_group)
            ->get()->getResult();
    }

    public function insertFileDoc(array $data)
    {
        if ($this->db->table('file_upload')->insert($data)) {
            return $this->db->insertID();
        }

        return false;
    }

    public function updateOthers($id_kavling, array $data)
    {
        return $this->db->table('others')->where('id', $id_kavling)->update($data);
    }

    public function getDatatableLegalitasData($var)
    {
        $var   = (array) $var;
        $query = $this->buildFilteredLegalitasQuery($var);

        $query->orderBy('jalan.nama_jalan', 'ASC')
            ->orderBy('kavling.no_kavling', 'ASC')
            ->orderBy('kavling.id_kavling', 'ASC');

        if (isset($var['start']) && isset($var['length'])) {
            $query->offset((int) $var['start'])->limit((int) $var['length']);
        }

        return $query->get()->getResult();
    }

    public function getDatatableLegalitasFilteredCount($var)
    {
        $query = $this->buildFilteredLegalitasQuery((array) $var);

        return $query->countAllResults();
    }

    public function getDatatableLegalitasTotalCount(?int $idProyek = null, string $statusLegal = 'has_legal')
    {
        $statusLegal = $this->normalizeStatusLegal($statusLegal);

        $query = $this->db->table('kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if ($statusLegal === 'has_legal') {
            $query->join('legal', 'kavling.id_legal = legal.id_legal');
        } else {
            $query->join('legal', 'kavling.id_legal = legal.id_legal', 'left');
            if ($statusLegal === 'empty_legal') {
                $query->where('legal.id_legal IS NULL', null, false);
            }
        }

        if ($idProyek !== null && $idProyek > 0) {
            $query->where('proyek.id_proyek', $idProyek);
        }

        return $query->countAllResults();
    }

    protected function buildFilteredLegalitasQuery(array $var)
    {
        $statusLegal = $this->normalizeStatusLegal($var['status_legal'] ?? 'has_legal');
        $columns     = [
            'konsumen.nama_konsumen',
            'jalan.nama_jalan',
            'kavling.no_kavling',
            'legal.sertifikat_split_no_hgb_induk',
            'legal.sertifikat_split_no_hgb',
            'legal.sertifikat_split_nib',
            'legal.pbb_pecah_nop',
            'legal.pbg_no',
            'legal.ajb_no',
            'legal.ppjb_no',
        ];
        $condition = [];

        if (!empty($var['id_proyek'])) {
            $condition['proyek.id_proyek'] = (int) $var['id_proyek'];
        }
        if (!empty($var['id_cluster'])) {
            $condition['cluster.id_cluster'] = (int) $var['id_cluster'];
        }
        if (!empty($var['id_jalan'])) {
            $condition['jalan.id_jalan'] = (int) $var['id_jalan'];
        }

        $query = $this->baseDatatableQuery($statusLegal);

        return $this->applyIfWhere($var, $columns, $condition, $query);
    }

    protected function normalizeStatusLegal(mixed $statusLegal): string
    {
        $value = (string) $statusLegal;

        return in_array($value, ['has_legal', 'empty_legal', 'all'], true) ? $value : 'has_legal';
    }

    protected function baseDatatableQuery(string $statusLegal = 'has_legal')
    {
        $statusLegal = $this->normalizeStatusLegal($statusLegal);

        $query = $this->db->table('kavling')
            ->select("
                legal.*,
                kavling.id_kavling,
                kavling.id_legal,
                kavling.id_jalan,
                kavling.no_kavling,
                jalan.nama_jalan,
                jalan.id_cluster,
                cluster.nama_cluster,
                proyek.id_proyek,
                proyek.nama_proyek,
                tipe.tipe_rumah,
                hargajual.id_tipe as hj_tipe_rumah,
                COALESCE(NULLIF(TRIM(hargajual.id_tipe), ''), tipe.tipe_rumah) as tipe_rumah_aktif,
                konsumen.nama_konsumen,
                konsumen.hp_konsumen,
                mkdt.akad_tgl,
                c.username as uadd_by,
                d.username as uedit_by
            ");

        if ($statusLegal === 'has_legal') {
            $query->join('legal', 'kavling.id_legal = legal.id_legal');
        } else {
            $query->join('legal', 'kavling.id_legal = legal.id_legal', 'left');
        }

        $query->join('users c', 'c.id = legal.add_by', 'left')
            ->join('users d', 'd.id = legal.edit_by', 'left')
            ->join('produksi', 'kavling.id_produksi = produksi.id_produksi', 'left')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('hargajual', 'hargajual.id = kavling.harga_akhir', 'left')
            ->join('mkdt', 'mkdt.id_mkdt = kavling.id_mkdt', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_konsumen', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'jalan.id_cluster = cluster.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek');

        if ($statusLegal === 'empty_legal') {
            $query->where('legal.id_legal IS NULL', null, false);
        }

        return $query;
    }

    protected function applyIfWhere($var, $column, $condition, $query)
    {
        $searchValue = '';
        if (isset($var['search'])) {
            if (is_array($var['search'])) {
                $searchValue = trim((string) ($var['search']['value'] ?? ''));
            } elseif (is_string($var['search']) || is_numeric($var['search'])) {
                $searchValue = trim((string) $var['search']);
            }
        }

        if ($searchValue !== '') {
            $query->groupStart();
            foreach ($column as $index => $col) {
                if ($index === 0) {
                    $query->like($col, $searchValue);
                } else {
                    $query->orLike($col, $searchValue);
                }
            }
            $query->groupEnd();
        }
        if (!empty($condition)) {
            $query->where($condition);
        }

        return $query;
    }
}
