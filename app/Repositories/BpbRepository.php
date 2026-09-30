<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class BpbRepository
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function list(array $input, int $userId): array
    {
        $draw = max(0, (int) ($input['draw'] ?? 0));
        $start = max(0, (int) ($input['start'] ?? 0));
        $length = min(100, max(10, (int) ($input['length'] ?? 10)));
        $scope = ($input['scope'] ?? 'related') === 'all' ? 'all' : 'related';
        $status = trim((string) ($input['status'] ?? ''));
        $historyStatus = trim((string) ($input['history_status'] ?? ''));
        $dateFrom = trim((string) ($input['date_from'] ?? ''));
        $dateTo = trim((string) ($input['date_to'] ?? ''));
        $department = trim((string) ($input['department'] ?? ''));
        $applicantUserId = max(0, (int) ($input['applicant_user_id'] ?? 0));
        $search = trim((string) ($input['search']['value'] ?? $input['search'] ?? ''));

        $base = $this->db->table('bpb_requests b')
            ->select("b.id,b.nomor,b.status,b.applicant_name,b.applicant_department,b.updated_at,COALESCE(b.submitted_at,b.created_at) AS display_date,(SELECT h.created_at FROM bpb_history h WHERE h.bpb_id=b.id AND h.to_status=b.status ORDER BY h.created_at DESC,h.id DESC LIMIT 1) AS status_changed_at,(SELECT i.nama_barang FROM bpb_items i WHERE i.bpb_id=b.id ORDER BY i.item_order ASC,i.id ASC LIMIT 1) AS first_item,(SELECT COUNT(*) FROM bpb_items ic WHERE ic.bpb_id=b.id) AS item_count", false);

        $this->applyVisibility($base, $scope, $userId);
        $totalBuilder = clone $base;
        $recordsTotal = $totalBuilder->countAllResults();

        if ($status !== '') {
            $base->where('b.status', $status);
        }
        if ($department !== '') {
            $base->where('b.applicant_department', $department);
        }
        if ($applicantUserId > 0) {
            $base->where('b.applicant_user_id', $applicantUserId);
        }
        if ($historyStatus !== '') {
            $historyFilter = 'hfilter.bpb_id = b.id AND hfilter.to_status = ' . $this->db->escape($historyStatus);
            if ($dateFrom !== '') {
                $historyFilter .= ' AND hfilter.created_at >= ' . $this->db->escape($dateFrom . ' 00:00:00');
            }
            if ($dateTo !== '') {
                $historyFilter .= ' AND hfilter.created_at <= ' . $this->db->escape($dateTo . ' 23:59:59');
            }
            $base->where("EXISTS (SELECT 1 FROM bpb_history hfilter WHERE {$historyFilter})", null, false);
        }
        if ($search !== '') {
            $base->groupStart()
                ->like('b.nomor', $search)
                ->orLike('b.applicant_name', $search)
                ->orLike('b.applicant_department', $search)
                ->orWhere("EXISTS (SELECT 1 FROM bpb_items si WHERE si.bpb_id=b.id AND si.nama_barang LIKE " . $this->db->escape('%' . $search . '%') . ")", null, false)
                ->groupEnd();
        }

        $filteredBuilder = clone $base;
        $recordsFiltered = $filteredBuilder->countAllResults();
        $rows = $base->orderBy('b.updated_at', 'DESC')->limit($length, $start)->get()->getResultArray();

        return ['draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $rows];
    }

    public function detail(int $id): ?array
    {
        $request = $this->db->table('bpb_requests')->where('id', $id)->get()->getRowArray();
        if (! $request) {
            return null;
        }
        $request['items'] = $this->db->table('bpb_items')->where('bpb_id', $id)->orderBy('item_order')->get()->getResultArray();
        $request['files'] = $this->db->table('bpb_files')->where('bpb_id', $id)->orderBy('id')->get()->getResultArray();
        $request['signatures'] = $this->db->table('bpb_signatures')->where('bpb_id', $id)->orderBy('signed_at')->get()->getResultArray();
        $request['history'] = $this->db->table('bpb_history')->where('bpb_id', $id)->orderBy('created_at', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray();
        return $request;
    }

    public function lock(int $id): array
    {
        $row = $this->db->query('SELECT * FROM bpb_requests WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        if (! $row) {
            throw new RuntimeException('BPB tidak ditemukan.');
        }
        return $row;
    }

    public function findBySubmissionKey(string $key): ?array
    {
        $row = $this->db->table('bpb_requests')->where('submission_key', $key)->get()->getRowArray();
        return $row ?: null;
    }

    public function options(int $excludeUserId): array
    {
        return $this->db->table('users u')
            ->select("u.id,COALESCE(NULLIF(k.nama_karyawan,''),NULLIF(u.name,''),u.username) AS name,COALESCE(d.divisi,'-') AS department,COALESCE(l.level,'-') AS level", false)
            ->join('karyawan k', 'k.id_user=u.id', 'left')
            ->join('divisi d', 'd.id_divisi=k.id_divisi', 'left')
            ->join('level l', 'l.id_level=k.id_level', 'left')
            ->where('u.active', 1)->where('u.id !=', $excludeUserId)
            ->orderBy('name', 'ASC')->get()->getResultArray();
    }

    public function listFilterOptions(): array
    {
        $departments = $this->db->table('bpb_requests')
            ->distinct()->select('applicant_department AS value')
            ->where('status !=', 'draft')->where('applicant_department !=', '')->orderBy('applicant_department', 'ASC')
            ->get()->getResultArray();
        $applicants = $this->db->table('bpb_requests')
            ->select('applicant_user_id AS id, MAX(applicant_name) AS name, MAX(applicant_department) AS department', false)
            ->where('status !=', 'draft')->where('applicant_user_id >', 0)->groupBy('applicant_user_id')->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return [
            'departments' => array_values(array_map(static fn (array $row): string => (string) $row['value'], $departments)),
            'applicants' => $applicants,
        ];
    }

    public function userSnapshot(int $userId): array
    {
        $row = $this->db->table('users u')
            ->select("u.id,u.password_hash,COALESCE(NULLIF(k.nama_karyawan,''),NULLIF(u.name,''),u.username) AS name,COALESCE(d.divisi,'-') AS department,COALESCE(l.level,'-') AS level", false)
            ->join('karyawan k', 'k.id_user=u.id', 'left')
            ->join('divisi d', 'd.id_divisi=k.id_divisi', 'left')
            ->join('level l', 'l.id_level=k.id_level', 'left')
            ->where('u.id', $userId)->get()->getRowArray();
        if (! $row) {
            throw new RuntimeException('Data pengguna tidak ditemukan.');
        }
        return $row;
    }

    public function hasGroup(int $userId, int $groupId): bool
    {
        return $this->db->table('auth_groups_users')->where(['user_id' => $userId, 'group_id' => $groupId])->countAllResults() > 0;
    }

    public function isActiveUser(int $userId): bool
    {
        return $userId > 0 && $this->db->table('users')->where(['id' => $userId, 'active' => 1])->countAllResults() > 0;
    }

    public function hasApproverSignature(int $bpbId): bool
    {
        return $this->db->table('bpb_signatures')->where('bpb_id', $bpbId)->whereIn('role', ['cc', 'approver'])->where('revoked_at', null)->countAllResults() > 0;
    }

    public function nextNumber(int $year, int $month): array
    {
        $this->db->query('INSERT IGNORE INTO bpb_number_counters (`year`,`last_number`,`updated_at`) VALUES (?,?,?)', [$year, 0, date('Y-m-d H:i:s')]);
        $row = $this->db->query('SELECT last_number FROM bpb_number_counters WHERE `year`=? FOR UPDATE', [$year])->getRowArray();
        $next = ((int) ($row['last_number'] ?? 0)) + 1;
        $this->db->table('bpb_number_counters')->where('year', $year)->update(['last_number' => $next, 'updated_at' => date('Y-m-d H:i:s')]);
        return ['sequence' => $next, 'year' => $year, 'month' => $month];
    }

    private function applyVisibility($builder, string $scope, int $userId): void
    {
        if ($scope === 'related') {
            $builder->groupStart()->where('b.applicant_user_id', $userId)->orWhere('b.cc_user_id', $userId)->orWhere('b.approver_user_id', $userId)->groupEnd();
        }
        $builder->groupStart()->where('b.status !=', 'draft')->orWhere('b.applicant_user_id', $userId)->groupEnd();
    }
}
