<?php

namespace App\Repositories;

class TargetSiteplanRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getByProject(int $idProyek): array
    {
        return $this->db->table('target_siteplan t')
            ->select('
                t.*,
                add_user.username as add_by_username,
                edit_user.username as edit_by_username,
                COUNT(tk.id_kavling) as jumlah_kavling
            ')
            ->join('target_siteplan_kavling tk', 'tk.id_target = t.id_target', 'left')
            ->join('users add_user', 'add_user.id = t.add_by', 'left')
            ->join('users edit_user', 'edit_user.id = t.edit_by', 'left')
            ->where('t.id_proyek', $idProyek)
            ->where('t.deleted_at', null)
            ->groupBy('t.id_target')
            ->orderBy('t.tahun_target', 'desc')
            ->orderBy('t.updated_at', 'desc')
            ->get()
            ->getResult();
    }

    public function getById(int $idTarget): ?object
    {
        return $this->db->table('target_siteplan t')
            ->select('t.*, add_user.username as add_by_username, edit_user.username as edit_by_username')
            ->join('users add_user', 'add_user.id = t.add_by', 'left')
            ->join('users edit_user', 'edit_user.id = t.edit_by', 'left')
            ->where('t.id_target', $idTarget)
            ->where('t.deleted_at', null)
            ->get()
            ->getRow();
    }

    public function getKavlings(int $idTarget): array
    {
        return $this->db->table('target_siteplan_kavling tk')
            ->select('
                tk.id_kavling,
                kavling.no_kavling,
                jalan.nama_jalan,
                tipe.no_tipe_rumah,
                tipe.tipe_rumah
            ')
            ->join('kavling', 'kavling.id_kavling = tk.id_kavling')
            ->join('jalan', 'jalan.id_jalan = kavling.id_jalan')
            ->join('tipe', 'tipe.id_tipe = kavling.id_tipe', 'left')
            ->where('tk.id_target', $idTarget)
            ->orderBy('jalan.nama_jalan', 'asc')
            ->orderBy('ABS(kavling.no_kavling)', 'asc')
            ->get()
            ->getResult();
    }

    public function getHistory(int $idTarget): array
    {
        return $this->db->table('target_siteplan_history h')
            ->select('h.*, users.username as add_by_username')
            ->join('users', 'users.id = h.add_by', 'left')
            ->where('h.id_target', $idTarget)
            ->orderBy('h.created_at', 'desc')
            ->get()
            ->getResult();
    }

    public function getKavlingTargetMap(int $idProyek): array
    {
        $rows = $this->db->table('target_siteplan_kavling tk')
            ->select('
                tk.id_kavling,
                t.id_target,
                t.tahun_target,
                t.deskripsi
            ')
            ->join('target_siteplan t', 't.id_target = tk.id_target')
            ->where('t.id_proyek', $idProyek)
            ->where('t.status', 1)
            ->where('t.deleted_at', null)
            ->orderBy('t.tahun_target', 'asc')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $idKavling = (int) $row->id_kavling;
            if (!isset($map[$idKavling])) {
                $map[$idKavling] = [
                    'id_target' => [],
                    'tahun_target' => [],
                    'deskripsi' => [],
                ];
            }

            $map[$idKavling]['id_target'][] = (int) $row->id_target;
            $map[$idKavling]['tahun_target'][] = (int) $row->tahun_target;
            if ((string) $row->deskripsi !== '') {
                $map[$idKavling]['deskripsi'][] = $row->deskripsi;
            }
        }

        foreach ($map as $idKavling => $item) {
            $map[$idKavling] = [
                'id_target' => implode(',', array_unique($item['id_target'])),
                'tahun_target' => implode(', ', array_unique($item['tahun_target'])),
                'deskripsi' => implode(' | ', array_unique($item['deskripsi'])),
            ];
        }

        return $map;
    }
}
