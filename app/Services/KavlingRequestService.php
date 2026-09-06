<?php

namespace App\Services;

use App\Data\NotificationAudience;
use App\Data\NotificationData;
use App\Models\KavlingRequestModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class KavlingRequestService
{
    protected BaseConnection $db;
    protected KavlingRequestModel $requestModel;
    protected NotifikasiService $notifikasiService;

    public function __construct()
    {
        $this->db = db_connect();
        $this->requestModel = new KavlingRequestModel();
        $this->notifikasiService = new NotifikasiService();
    }

    /**
     * Submit request tambah atau ubah kavling dari user non-planning
     */
    public function submitRequest(array $data, int $userId): array
    {
        $idProyek = (int) ($data['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            throw new RuntimeException('Proyek tidak valid.');
        }

        $jenisRequest = $data['jenis_request'] ?? 'tambah_baru';
        if (!in_array($jenisRequest, ['tambah_baru', 'ubah_tipe'])) {
            throw new RuntimeException('Jenis request tidak valid.');
        }

        $idKavling = !empty($data['id_kavling']) ? (int) $data['id_kavling'] : null;
        $idCluster = !empty($data['id_cluster']) ? (int) $data['id_cluster'] : null;
        $idJalan   = !empty($data['id_jalan']) ? (int) $data['id_jalan'] : null;
        $idTipe    = !empty($data['id_tipe']) ? (int) $data['id_tipe'] : null;
        $keterangan = trim($data['keterangan'] ?? '');

        if ($jenisRequest === 'ubah_tipe' && empty($idKavling)) {
            throw new RuntimeException('Kavling existing harus dipilih untuk request perubahan tipe.');
        }

        // Jika cluster dan/atau jalan belum ada/tidak dipilih, keterangan wajib diisi untuk catatan manual
        if ($jenisRequest === 'tambah_baru' && (empty($idCluster) || empty($idJalan)) && empty($keterangan)) {
            throw new RuntimeException('Keterangan wajib diisi apabila Cluster dan/atau Jalan belum dipilih.');
        }

        $this->db->transStart();

        $insertData = [
            'id_proyek'     => $idProyek,
            'jenis_request' => $jenisRequest,
            'id_kavling'    => $idKavling,
            'id_cluster'    => $idCluster,
            'id_jalan'      => $idJalan,
            'id_tipe'       => $idTipe,
            'keterangan'    => $keterangan,
            'status'        => 'pending',
            'created_by'    => $userId,
        ];

        $requestId = $this->requestModel->insert($insertData);
        if (!$requestId) {
            $this->db->transRollback();
            throw new RuntimeException('Gagal menyimpan pengajuan request kavling.');
        }

        // Ambil info nama proyek
        $proyek = $this->db->table('proyek')->select('nama_proyek')->where('id_proyek', $idProyek)->get()->getRow();
        $namaProyek = $proyek->nama_proyek ?? 'Proyek #' . $idProyek;

        // Ambil nama user pengaju
        $userRow = $this->db->table('users')->select('name, username')->where('id', $userId)->get()->getRow();
        $pengaju = $userRow->name ?? ($userRow->username ?? 'User #' . $userId);

        // Siapkan pesan notifikasi
        $pesanNotif = '';
        $actionUrl = 'siteplan/view';

        if ($jenisRequest === 'ubah_tipe') {
            $kavlingRow = $this->db->table('kavling')
                ->select('kavling.no_kavling, jalan.nama_jalan')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
                ->where('kavling.id_kavling', $idKavling)
                ->get()
                ->getRow();

            $noKav = $kavlingRow ? ($kavlingRow->nama_jalan . ' No. ' . $kavlingRow->no_kavling) : ('ID #' . $idKavling);

            $tipeRow = $idTipe ? $this->db->table('tipe')->select('no_tipe_rumah, tipe_rumah')->where('id_tipe', $idTipe)->get()->getRow() : null;
            $namaTipe = $tipeRow ? ($tipeRow->no_tipe_rumah . ' (' . $tipeRow->tipe_rumah . ')') : 'Tipe Baru';

            $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan perubahan tipe kavling {$noKav} menjadi {$namaTipe} di {$namaProyek}." .
                ($keterangan !== '' ? " Catatan: {$keterangan}" : '');
            $actionUrl = "siteplan/view?id_kavling={$idKavling}";
        } else {
            $clusterRow = $idCluster ? $this->db->table('cluster')->select('nama_cluster')->where('id_cluster', $idCluster)->get()->getRow() : null;
            $jalanRow   = $idJalan ? $this->db->table('jalan')->select('nama_jalan')->where('id_jalan', $idJalan)->get()->getRow() : null;

            $lokasiParts = [];
            if ($clusterRow) $lokasiParts[] = "Cluster: " . $clusterRow->nama_cluster;
            if ($jalanRow)   $lokasiParts[] = "Jalan: " . $jalanRow->nama_jalan;

            $lokasiText = !empty($lokasiParts) ? implode(', ', $lokasiParts) : 'Cluster/Jalan Manual';

            $tipeRow = $idTipe ? $this->db->table('tipe')->select('no_tipe_rumah, tipe_rumah')->where('id_tipe', $idTipe)->get()->getRow() : null;
            $tipeText = $tipeRow ? " Tipe: {$tipeRow->no_tipe_rumah} ({$tipeRow->tipe_rumah})." : "";

            $pesanNotif = "[Request Siteplan] {$pengaju} mengajukan penambahan kavling baru ({$lokasiText}).{$tipeText} di {$namaProyek}." .
                ($keterangan !== '' ? " Catatan: {$keterangan}" : '');
        }

        // Kirim notifikasi in-app dan email ke Tim Planning (Group 6)
        $notifData = new NotificationData(
            message: $pesanNotif,
            actorUserId: $userId,
            idKavling: $idKavling,
            idKonsumen: null,
            type: 'request_kavling',
            idProyek: $idProyek,
            actionUrl: $actionUrl
        );

        $audience = NotificationAudience::forGroups([6]); // Group 6 = Planning
        $this->notifikasiService->create($notifData, $audience);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Gagal memproses transaksi request kavling.');
        }

        return [
            'id_request' => $requestId,
            'message'    => 'Pengajuan request berhasil dikirim ke tim Planning.',
        ];
    }

    /**
     * Mengambil daftar request kavling berdasarkan proyek aktif dan filter status
     */
    public function getList(int $idProyek, ?string $status = null): array
    {
        $builder = $this->db->table('kavling_request')
            ->select('
                kavling_request.*,
                users.name as pengaju_name,
                users.username as pengaju_username,
                cluster.nama_cluster,
                jalan.nama_jalan,
                tipe.no_tipe_rumah,
                tipe.tipe_rumah,
                kavling.no_kavling as existing_no_kavling,
                kavling_jalan.nama_jalan as existing_nama_jalan
            ')
            ->join('users', 'users.id = kavling_request.created_by', 'left')
            ->join('cluster', 'cluster.id_cluster = kavling_request.id_cluster', 'left')
            ->join('jalan', 'jalan.id_jalan = kavling_request.id_jalan', 'left')
            ->join('tipe', 'tipe.id_tipe = kavling_request.id_tipe', 'left')
            ->join('kavling', 'kavling.id_kavling = kavling_request.id_kavling', 'left')
            ->join('jalan as kavling_jalan', 'kavling_jalan.id_jalan = kavling.id_jalan', 'left')
            ->where('kavling_request.id_proyek', $idProyek);

        if (!empty($status) && in_array($status, ['pending', 'approved', 'rejected'])) {
            $builder->where('kavling_request.status', $status);
        }

        $builder->orderBy('kavling_request.created_at', 'DESC');
        return $builder->get()->getResult();
    }

    /**
     * Update status request kavling (contoh: approved/selesai atau dikembalikan ke pending/rejected)
     */
    public function updateStatus(int $idRequest, string $newStatus, int $userId): array
    {
        if (!in_array($newStatus, ['pending', 'approved', 'rejected'])) {
            throw new RuntimeException('Status tidak valid.');
        }

        $request = $this->requestModel->find($idRequest);
        if (!$request) {
            throw new RuntimeException('Data request tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $updated = $this->requestModel->update($idRequest, [
            'status'     => $newStatus,
            'updated_at' => $now,
        ]);

        if (!$updated) {
            throw new RuntimeException('Gagal mengubah status request.');
        }

        $statusLabels = [
            'pending'  => 'Menunggu (Pending)',
            'approved' => 'Selesai (Disetujui)',
            'rejected' => 'Ditolak',
        ];

        return [
            'id_request' => $idRequest,
            'status'     => $newStatus,
            'label'      => $statusLabels[$newStatus] ?? $newStatus,
            'message'    => "Status request berhasil diubah menjadi: " . ($statusLabels[$newStatus] ?? $newStatus),
        ];
    }
}
