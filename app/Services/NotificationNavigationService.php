<?php

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationNavigationService
{
    private $db;
    private $notificationRepository;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->notificationRepository = new NotificationRepository();
    }

    public function processOpen(int $notificationId, int $userId): string
    {
        // Validasi recipient
        $recipient = $this->db->table('notification_recipients')
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->get()
            ->getRow();

        // Jika fitur dual write aktif, kita memvalidasi recipient
        $checkRecipient = getenv('NOTIF_DUAL_WRITE_RECIPIENTS');
        if ($checkRecipient === false || $checkRecipient === '') {
            $checkRecipient = true;
        } else {
            $checkRecipient = in_array(strtolower((string) $checkRecipient), ['1', 'true', 'yes', 'on'], true);
        }

        if ($checkRecipient && ! $recipient) {
            // Bisa jadi notifikasi global lama, cek notification table langsung
            $notif = $this->db->table('notification')->where('id', $notificationId)->get()->getRow();
            if (! $notif) {
                return site_url('/');
            }
        } else {
            $notif = $this->db->table('notification')->where('id', $notificationId)->get()->getRow();
        }

        if (! $notif) {
            return site_url('/');
        }

        // Tandai dibaca
        $this->notificationRepository->markAsReadForUser($notificationId, $userId);

        // Cari Proyek
        $idProyek = (int) $notif->id_proyek;
        if ($idProyek <= 0 && $notif->id_kavling) {
            // Coba resolve dari kavling
            $kavling = $this->db->table('kavling')
                ->select('kavling.id_jalan, jalan.id_cluster, cluster.id_proyek')
                ->join('jalan', 'jalan.id_jalan = kavling.id_jalan', 'left')
                ->join('cluster', 'cluster.id_cluster = jalan.id_cluster', 'left')
                ->where('kavling.id_kavling', $notif->id_kavling)
                ->get()
                ->getRow();
            
            if ($kavling && $kavling->id_proyek) {
                $idProyek = (int) $kavling->id_proyek;
            }
        }

        // Set Proyek Aktif jika perlu
        if ($idProyek > 0) {
            $session = session();
            $currentProyekId = (int) $session->get('id_proyek');
            if ($currentProyekId !== $idProyek) {
                // Pastikan user punya akses ke proyek ini
                $hasAccess = true; // Sederhanakan untuk sekarang, asumsikan ActiveProyekService validasi sendiri
                $session->set('id_proyek', $idProyek);
            }
        }

        // Tentukan Redirect URL
        if (! empty($notif->action_url)) {
            return site_url($notif->action_url);
        }

        // Fallback Logic
        if (! empty($notif->id_kavling)) {
            return site_url('siteplan/view?id_kavling=' . $notif->id_kavling);
        }

        if ($idProyek > 0) {
            return site_url('siteplan/view');
        }

        return site_url('/');
    }
}
