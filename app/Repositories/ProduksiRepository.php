<?php

namespace App\Repositories;

class ProduksiRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getFilesByKavling(int $idKavling): array
    {
        return $this->db->table('file_produksi')
            ->select('file_produksi.*, username')
            ->join('users', 'file_produksi.upload_by = users.id')
            ->where('id_kavling', $idKavling)
            ->get()->getResult();
    }

    public function insertFiles(array $files): bool
    {
        return (bool) $this->db->table('file_produksi')->insertBatch($files);
    }

    public function hasProduksiChangeHistoryTable(): bool
    {
        return $this->db->tableExists('produksi_change_history');
    }

    public function insertProduksiChangeHistory(array $fields): bool
    {
        return (bool) $this->db->table('produksi_change_history')->insert($fields);
    }

    public function countProduksiChangeHistory(int $idKavling): int
    {
        if (!$this->hasProduksiChangeHistoryTable()) {
            return 0;
        }

        return $this->db->table('produksi_change_history')
            ->where('id_kavling', $idKavling)
            ->countAllResults();
    }

    public function getProduksiChangeHistory(int $idKavling, int $limit, int $offset): array
    {
        if (!$this->hasProduksiChangeHistoryTable()) {
            return [];
        }

        return $this->db->table('produksi_change_history h')
            ->select('h.*, users.username')
            ->join('users', 'users.id = h.add_by', 'left')
            ->where('h.id_kavling', $idKavling)
            ->orderBy('h.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()->getResult();
    }

    public function getFileById(int $id): ?object
    {
        return $this->db->table('file_produksi')->where('id', $id)->get()->getRow() ?: null;
    }

    public function deleteFileById(int $id): bool
    {
        return (bool) $this->db->table('file_produksi')->delete(['id' => $id]);
    }

    public function getBayarList(int $idKavling): array
    {
        return $this->db->table('list_bayar_produksi lc')
            ->select('lc.id as id_bayar_produksi, lc.item, lc.sort, c.*, u.username as add_by_u, e.username as edit_by_u')
            ->join('bayar_produksi c', 'c.id_item_produksi = lc.id and id_kavling = ' . $this->db->escape($idKavling), 'left')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC')
            ->get()->getResult();
    }

    public function getBayarItemList(string $search): array
    {
        $builder = $this->db->table('list_bayar_produksi lc')
            ->select('lc.id, lc.item')
            ->where('lc.deleted_at', null)
            ->orderBy('lc.sort', 'ASC')
            ->orderBy('lc.id', 'ASC');

        if ($search !== '') {
            $builder->like('lc.item', $search);
        }

        return $builder->get()->getResult();
    }

    public function getRiwayatBayarByKavling(int $idKavling): array
    {
        return $this->db->table('bayar_produksi c')
            ->select('c.*, lc.item, u.username as uadd_by, e.username as uedit_by')
            ->join('list_bayar_produksi lc', 'lc.id = c.id_item_produksi')
            ->join('users u', 'u.id = c.add_by', 'left')
            ->join('users e', 'e.id = c.edit_by', 'left')
            ->where('c.id_kavling', $idKavling)
            ->orderBy('c.tanggal_bayar', 'DESC')
            ->get()->getResult();
    }

    public function insertBayarSingle(array $data): ?int
    {
        $inserted = (bool) $this->db->table('bayar_produksi')->insert([
            'id_kavling'       => $data['id_kavling'],
            'id_item_produksi' => $data['id_item_produksi'],
            'nominal'          => $data['nominal'],
            'keterangan'       => $data['keterangan'] ?? '',
            'tanggal_bayar'    => $data['tanggal_bayar'],
            'add_by'           => $data['add_by'],
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            return null;
        }

        return (int) $this->db->insertID();
    }

    public function deleteBayar(int $id): ?int
    {
        $row = $this->db->table('bayar_produksi')->where('id', $id)->get()->getRow();
        if (!$row) {
            return null;
        }

        $idKavling = (int) $row->id_kavling;
        $this->db->table('bayar_produksi')->where('id', $id)->delete();

        return $idKavling;
    }

    public function upsertBayarItems(string $idKavling, array $items, int $userId): void
    {
        $this->db->transStart();

        foreach ($items as $key => $v) {
            if ($v['nominal'] === '' || $v['tanggal_bayar'] === '') {
                continue;
            }

            $nominal = str_replace(',', '', $v['nominal']);

            if ((float) $nominal <= 0) {
                continue;
            }

            if (strpos($key, 'n') === false) {
                $this->db->table('bayar_produksi')->where(['id' => $key])->update([
                    'id_kavling'    => $idKavling,
                    'nominal'       => $nominal,
                    'keterangan'    => $v['keterangan'],
                    'tanggal_bayar' => $v['tanggal_bayar'],
                    'edit_by'       => $userId,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->table('bayar_produksi')->insert([
                    'id_kavling'       => $idKavling,
                    'id_item_produksi' => substr($v['id_item_produksi'], 1),
                    'nominal'          => $nominal,
                    'keterangan'       => $v['keterangan'],
                    'tanggal_bayar'    => $v['tanggal_bayar'],
                    'id'               => null,
                    'add_by'           => $userId,
                    'created_at'       => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->db->transComplete();
    }

    public function getByIdWithUsers(int $idProduksi): ?object
    {
        return $this->db->table('produksi')
            ->select('
                produksi.*,
                a.username as tanggal_pembangunan_oleh_u,
                b.username as tanggal_pembangunan_diubah_oleh_u,
                c.username as tanggal_selesai_pembangunan_diubah_oleh_u,
                sumurbor, sumurbor_tanggal, sumurbor_keterangan,
                d.username as sumurbor_oleh_u
            ')
            ->where('produksi.id_produksi', $idProduksi)
            ->join('users as a', 'a.id = produksi.tanggal_pembangunan_oleh', 'left')
            ->join('users as b', 'b.id = produksi.tanggal_pembangunan_diubah_oleh', 'left')
            ->join('users as c', 'c.id = produksi.tanggal_selesai_pembangunan_diubah_oleh', 'left')
            ->join('kavling', 'kavling.id_produksi = produksi.id_produksi', 'left')
            ->join('users as d', 'd.id = kavling.sumurbor_oleh', 'left')
            ->get()->getRow() ?: null;
    }

    public function getSlfList(): array
    {
        $subQuery = $this->db->table('kavling')
            ->select('GROUP_CONCAT(concat(jalan.nama_jalan," No. ", kavling.no_kavling ) ORDER BY jalan.nama_jalan, kavling.no_kavling SEPARATOR \', \')', false)
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->where('FIND_IN_SET(kavling.id_kavling, list_slf.id_kavling)', null, false)
            ->getCompiledSelect();

        return $this->db->table('list_slf')
            ->select('list_slf.id, list_slf.no_slf, username, list_slf.created_at')
            ->select("($subQuery) AS kavling", false)
            ->join('users', 'users.id = list_slf.add_by')
            ->groupBy('list_slf.id')
            ->get()->getResult();
    }

    public function getSlfById(int $id): ?object
    {
        $result = $this->db->table('list_slf')->where('id', $id)->get()->getResult();
        return $result[0] ?? null;
    }

    public function getSlfKavlingData(array $idKavlingList): array
    {
        return $this->db->table('kavling')
            ->select('
                `proyek`.`nama_proyek`,
                `proyek`.`alamat_proyek`,
                `proyek`.`kelurahan`,
                `proyek`.`kecamatan`,
                `proyek`.`kota`,
                `proyek`.`provinsi`,
                `proyek`.`nama_pt`,
                `cluster`.`nama_cluster`,
                `jalan`.`nama_jalan`,
                `tipe`.`no_tipe_rumah`,
                `tipe`.`tipe_rumah`,
                `kavling`.`no_kavling`,
                konsumen.nama_konsumen
            ')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'cluster.id_proyek = proyek.id_proyek')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe')
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt', 'left')
            ->whereIn('kavling.id_kavling', $idKavlingList)
            ->orderBy('jalan.nama_jalan', 'asc')
            ->orderBy('kavling.no_kavling', 'asc')
            ->get()->getResult();
    }

    public function insertSlf(array $data): bool
    {
        return (bool) $this->db->table('list_slf')->insert($data);
    }

    public function deleteSlfById(int $id): bool
    {
        return (bool) $this->db->table('list_slf')->delete(['id' => $id]);
    }

    public function getKavlingByProyek(int $idProyek, string $search): array
    {
        return $this->db->table('kavling')
            ->select('kavling.id_kavling, kavling.id_mkdt, nama_jalan, no_kavling, nama_konsumen')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('cluster', 'cluster.id_cluster = jalan.id_cluster')
            ->join('proyek', 'proyek.id_proyek = cluster.id_proyek')
            ->join('tipe', 'kavling.id_tipe = tipe.id_tipe')
            ->join('mkdt', 'mkdt.id_kavling = kavling.id_kavling', 'left')
            ->join('konsumen', 'konsumen.id_konsumen = mkdt.id_mkdt', 'left')
            ->where('proyek.id_proyek', $idProyek)
            ->groupStart()
            ->like('nama_jalan', $search)
            ->orLike('nama_konsumen', $search)
            ->orLike('no_kavling', $search)
            ->groupEnd()
            ->get()->getResult();
    }

    public function createOther(array $fields): bool
    {
        return (bool) $this->db->table('others')->insert($fields);
    }

    public function getOtherById(int $id): ?object
    {
        return $this->db->table('others')->where('id', $id)->get()->getRow() ?: null;
    }

    public function updateOthers(int $id, array $fields): bool
    {
        return (bool) $this->db->table('others')->where('id', $id)->update($fields);
    }

    public function hasJalanProgressHistoryTable(): bool
    {
        return $this->db->tableExists('produksi_jalan_progress_history');
    }

    public function insertJalanProgressHistory(array $fields): bool
    {
        return (bool) $this->db->table('produksi_jalan_progress_history')->insert($fields);
    }
}
