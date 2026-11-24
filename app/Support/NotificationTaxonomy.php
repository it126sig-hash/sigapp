<?php

namespace App\Support;

use App\Enums\NotificationEvent;

final class NotificationTaxonomy
{
    public const INTENT_ACTION_REQUIRED  = 'action_required';
    public const INTENT_URGENT_ALERT     = 'urgent_alert';
    public const INTENT_OPERATIONAL_INFO = 'operational_info';
    public const INTENT_SYSTEM_MASTER    = 'system_master';

    private const PERMOHONAN_MODULES = [
        'bpb',
        'fee_sales',
        'kavling_request',
        'tagihan_direksi',
        'cashout_subkon',
        'mgm',
        'tiket_masalah',
    ];

    private const CATEGORY_MAP = [
        // BPB
        NotificationEvent::BPB_SIGNATURE_REQUESTED => 'bpb',
        NotificationEvent::BPB_SIGNATURE_COMPLETED => 'bpb',
        NotificationEvent::BPB_REJECTED            => 'bpb',
        NotificationEvent::BPB_APPROVED            => 'bpb',
        NotificationEvent::BPB_STATUS_CHANGED      => 'bpb',
        NotificationEvent::BPB_EXECUTION_REQUESTED => 'bpb',

        // Fee Sales
        NotificationEvent::FEE_SALES_PENGAJUAN_CREATED  => 'Fee Sales',
        NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED => 'Fee Sales',
        NotificationEvent::FEE_SALES_TERMIN_GENERATED   => 'Fee Sales',
        NotificationEvent::FEE_SALES_HARGA_NET_UPDATED  => 'Fee Sales',
        NotificationEvent::FEE_SALES_TTD_SIGNED         => 'Fee Sales',
        NotificationEvent::FEE_SALES_PENCAIRAN          => 'Fee Sales',

        // Siteplan
        NotificationEvent::KAVLING_ADDED     => 'siteplan',
        NotificationEvent::KAVLING_UPDATED   => 'siteplan',
        NotificationEvent::OTHERS_ADDED      => 'siteplan',
        NotificationEvent::OTHERS_UPDATED    => 'siteplan',
        NotificationEvent::TURUN_PEMBANGUNAN => 'siteplan',
        NotificationEvent::REQUEST_KAVLING   => 'siteplan',

        // Konsumen
        NotificationEvent::BOOKING_BARU         => 'konsumen',
        NotificationEvent::DATA_KONSUMEN_UPDATE => 'konsumen',
        NotificationEvent::BATAL_BOOKING        => 'konsumen',
        NotificationEvent::KONSUMEN_BARU        => 'konsumen',
        NotificationEvent::WAWANCARA            => 'konsumen',
        NotificationEvent::PERINTAH_BANGUN      => 'konsumen',
        NotificationEvent::AKAD                 => 'konsumen',

        // Keuangan
        NotificationEvent::TAGIHAN_KPR               => 'keuangan',
        NotificationEvent::CASHOUT_SUBKON            => 'keuangan',
        NotificationEvent::PENCAIRAN_HASIL_AKAD      => 'keuangan',
        NotificationEvent::REKONSILIASI_KEUANGAN     => 'keuangan',
        NotificationEvent::DAJAM_PENGAJUAN_CREATED   => 'keuangan',
        NotificationEvent::DAJAM_PENCAIRAN_COMPLETED => 'keuangan',

        // Pajak
        NotificationEvent::PAJAK_PPH => 'pajak',
        NotificationEvent::PAJAK_PPN => 'pajak',

        // Legal
        NotificationEvent::LEGAL_DOKUMEN_UPDATED => 'legal',

        // Produksi
        NotificationEvent::PROGRESS_PRODUKSI => 'produksi',

        // Direksi
        NotificationEvent::DISKRESI_HARGA              => 'direksi',
        NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED => 'direksi',
        NotificationEvent::TAGIHAN_SIGNATURE_RESPONDED => 'direksi',

        // Tiket Masalah
        NotificationEvent::TIKET_MASALAH_BARU   => 'tiket_masalah',
        NotificationEvent::TIKET_MASALAH_ASSIGN => 'tiket_masalah',
        NotificationEvent::TIKET_MASALAH_UPDATE => 'tiket_masalah',

        // Referral / MGM
        NotificationEvent::MGM_REFERRAL_CREATED => 'referral',
        NotificationEvent::MGM_SPP_SUBMITTED    => 'referral',
        NotificationEvent::MGM_SPP_CAIR         => 'referral',

        // Master Data
        NotificationEvent::MASTER_PROYEK  => 'master_data',
        NotificationEvent::MASTER_CLUSTER => 'master_data',
        NotificationEvent::MASTER_JALAN   => 'master_data',
        NotificationEvent::MASTER_TIPE    => 'master_data',
    ];

    /**
     * Memetakan item notifikasi menjadi taksonomi 2-dimensi + routing SPA.
     *
     * @param array<string, mixed>|object $item
     *
     * @return array{
     *     event_type: ?string,
     *     category: string,
     *     intent: string,
     *     target_module: string,
     *     ref_id: int|string|null,
     *     spa_route: array{path: string, query: array<string, string>}
     * }
     */
    public static function resolve(array|object $item): array
    {
        $rawType    = self::extractString($item, 'event_type') ?? self::extractString($item, 'type');
        $actionUrl  = self::extractString($item, 'action_url') ?? '';
        $rawNotif   = self::extractString($item, 'notif_text') ?? self::extractString($item, 'notif') ?? '';
        $notifText  = NotificationTextFormatter::plain($rawNotif, '');
        $idKavling  = self::extractInt($item, 'id_kavling');
        $idKonsumen = self::extractInt($item, 'id_konsumen');
        $idProyek   = self::extractInt($item, 'id_proyek');

        $eventType    = self::resolveCanonicalEventType($rawType, $actionUrl, $notifText);
        $category     = self::resolveCategory($eventType, $rawType, $actionUrl);
        $intent       = self::resolveIntent($eventType, $notifText);
        $targetModule = self::resolveTargetModule($eventType, $rawType, $actionUrl);
        $refId        = self::resolveRefId($targetModule, $rawType, $actionUrl, $notifText, $idKavling, $idKonsumen, $idProyek);
        $spaRoute     = self::resolveSpaRoute($intent, $targetModule, $refId, $actionUrl, $idKavling, $idKonsumen);

        return [
            'event_type'    => $eventType,
            'category'      => $category,
            'intent'        => $intent,
            'target_module' => $targetModule,
            'ref_id'        => $refId,
            'spa_route'     => $spaRoute,
        ];
    }

    private static function resolveCanonicalEventType(?string $rawType, string $actionUrl, string $notifText): ?string
    {
        $rawLower = strtolower(trim((string) $rawType));
        $textLower = strtolower($notifText);
        $urlLower = strtolower($actionUrl);

        if ($rawLower === 'tiket_masalah' || str_starts_with($rawLower, 'tiket_masalah|')) {
            if (str_contains($textLower, 'ditugaskan') || str_contains($textLower, 'di-assign') || str_contains($textLower, 'assign')) {
                return NotificationEvent::TIKET_MASALAH_ASSIGN;
            }
            if (str_contains($textLower, 'tiket masalah baru') || str_contains($textLower, 'komplain baru')) {
                return NotificationEvent::TIKET_MASALAH_BARU;
            }

            return NotificationEvent::TIKET_MASALAH_UPDATE;
        }

        if ($rawLower === 'keuangan') {
            if (str_contains($urlLower, 'hasil-akad') || str_contains($textLower, 'hasil akad')) {
                return NotificationEvent::PENCAIRAN_HASIL_AKAD;
            }

            return 'keuangan';
        }

        if ($rawLower === 'mkdt_konsumen') {
            return NotificationEvent::DATA_KONSUMEN_UPDATE;
        }

        if ($rawLower === 'tagihan') {
            if (str_contains($textLower, 'kpr')) {
                return NotificationEvent::TAGIHAN_KPR;
            }

            return 'tagihan';
        }

        $canonical = NotificationEvent::fromLegacyType($rawType);
        if ($canonical !== null) {
            return $canonical;
        }

        // Legacy rows with NULL type: infer when unmistakable
        if ($urlLower !== '') {
            if (str_contains($urlLower, 'direksi/tagihan') || (str_contains($urlLower, 'tagihan/') && str_contains($textLower, 'surat tagihan'))) {
                if (str_contains($textLower, 'menunggu tanda tangan')) {
                    return NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED;
                }
                if (str_contains($textLower, 'ditolak') || str_contains($textLower, 'disetujui') || str_contains($textLower, 'ditandatangani')) {
                    return NotificationEvent::TAGIHAN_SIGNATURE_RESPONDED;
                }
            }
        }

        return null;
    }

    private static function resolveCategory(?string $eventType, ?string $rawType, string $actionUrl): string
    {
        if ($eventType !== null && isset(self::CATEGORY_MAP[$eventType])) {
            return self::CATEGORY_MAP[$eventType];
        }

        $rawLower = strtolower(trim((string) $rawType));
        if ($rawLower === 'keuangan' || $rawLower === 'tagihan') {
            return 'keuangan';
        }
        if ($rawLower === 'mkdt_konsumen') {
            return 'konsumen';
        }
        if (str_starts_with($rawLower, 'tiket_masalah')) {
            return 'tiket_masalah';
        }

        $urlLower = strtolower($actionUrl);
        if (str_contains($urlLower, 'bpb')) {
            return 'bpb';
        }
        if (str_contains($urlLower, 'fee-sales')) {
            return 'Fee Sales';
        }
        if (str_contains($urlLower, 'direksi/tagihan')) {
            return 'direksi';
        }
        if (str_contains($urlLower, 'cashout') || str_contains($urlLower, 'keuangan') || str_contains($urlLower, 'tab=keuangan')) {
            return 'keuangan';
        }
        if (str_contains($urlLower, 'member-get-member')) {
            return 'referral';
        }
        if (str_contains($urlLower, 'tiket-masalah') || str_contains($urlLower, 'filter=masalah')) {
            return 'tiket_masalah';
        }
        if (str_contains($urlLower, 'tab=legal')) {
            return 'legal';
        }
        if (str_contains($urlLower, 'tab=produksi')) {
            return 'produksi';
        }
        if (str_contains($urlLower, 'tab=konsumen')) {
            return 'konsumen';
        }

        return 'siteplan';
    }

    private static function resolveIntent(?string $eventType, string $notifText): string
    {
        if ($eventType === null) {
            return self::INTENT_OPERATIONAL_INFO;
        }

        if (in_array($eventType, [
            NotificationEvent::MASTER_PROYEK,
            NotificationEvent::MASTER_CLUSTER,
            NotificationEvent::MASTER_JALAN,
            NotificationEvent::MASTER_TIPE,
        ], true)) {
            return self::INTENT_SYSTEM_MASTER;
        }

        if (in_array($eventType, [
            NotificationEvent::BPB_SIGNATURE_REQUESTED,
            NotificationEvent::BPB_EXECUTION_REQUESTED,
            NotificationEvent::FEE_SALES_PENGAJUAN_CREATED,
            NotificationEvent::FEE_SALES_TTD_SIGNED,
            NotificationEvent::FEE_SALES_PENGAJUAN_REJECTED,
            NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED,
            NotificationEvent::MGM_SPP_SUBMITTED,
            NotificationEvent::TIKET_MASALAH_BARU,
            NotificationEvent::TIKET_MASALAH_ASSIGN,
            NotificationEvent::REKONSILIASI_KEUANGAN,
            NotificationEvent::DAJAM_PENGAJUAN_CREATED,
        ], true)) {
            return self::INTENT_ACTION_REQUIRED;
        }

        $textLower = strtolower($notifText);

        if ($eventType === NotificationEvent::REQUEST_KAVLING) {
            if (
                str_contains($textLower, 'telah disetujui')
                || str_contains($textLower, 'disetujui / selesai')
                || str_contains($textLower, 'telah ditolak')
                || str_contains($textLower, 'membatalkan')
                || str_contains($textLower, 'dibatalkan')
            ) {
                return self::INTENT_OPERATIONAL_INFO;
            }

            return self::INTENT_ACTION_REQUIRED;
        }

        if ($eventType === NotificationEvent::CASHOUT_SUBKON) {
            if (str_contains($textLower, 'pembayaran cek')) {
                return self::INTENT_OPERATIONAL_INFO;
            }

            if (
                str_contains($textLower, 'jatuh tempo')
                || str_contains($textLower, 'spp')
                || str_contains($textLower, 'pengajuan pencairan')
                || str_contains($textLower, 'pengajuan cair')
                || str_contains($textLower, 'mengajukan pencairan')
            ) {
                return self::INTENT_ACTION_REQUIRED;
            }

            return self::INTENT_OPERATIONAL_INFO;
        }

        return self::INTENT_OPERATIONAL_INFO;
    }

    private static function resolveTargetModule(?string $eventType, ?string $rawType, string $actionUrl): string
    {
        $urlLower = strtolower($actionUrl);
        $rawLower = strtolower(trim((string) $rawType));

        if ($eventType !== null) {
            if (str_starts_with($eventType, 'bpb_')) {
                return 'bpb';
            }
            if (str_starts_with($eventType, 'fee_sales_')) {
                return 'fee_sales';
            }
            if ($eventType === NotificationEvent::REQUEST_KAVLING) {
                return 'kavling_request';
            }
            if (in_array($eventType, [
                NotificationEvent::TAGIHAN_SIGNATURE_REQUESTED,
                NotificationEvent::TAGIHAN_SIGNATURE_RESPONDED,
            ], true)) {
                return 'tagihan_direksi';
            }
            if ($eventType === NotificationEvent::CASHOUT_SUBKON) {
                return 'cashout_subkon';
            }
            if (str_starts_with($eventType, 'mgm_')) {
                return 'mgm';
            }
            if (str_starts_with($eventType, 'tiket_masalah')) {
                return 'tiket_masalah';
            }
            if ($eventType === NotificationEvent::LEGAL_DOKUMEN_UPDATED) {
                return 'legal';
            }
            if (in_array($eventType, [
                NotificationEvent::TAGIHAN_KPR,
                NotificationEvent::PENCAIRAN_HASIL_AKAD,
                NotificationEvent::REKONSILIASI_KEUANGAN,
                NotificationEvent::DAJAM_PENGAJUAN_CREATED,
                NotificationEvent::DAJAM_PENCAIRAN_COMPLETED,
                NotificationEvent::PAJAK_PPH,
                NotificationEvent::PAJAK_PPN,
                'keuangan',
                'tagihan',
            ], true)) {
                return 'keuangan';
            }
        }

        if (str_starts_with($rawLower, 'tiket_masalah') || str_contains($urlLower, 'filter=masalah') || str_contains($urlLower, 'tiket-masalah')) {
            return 'tiket_masalah';
        }
        if (str_contains($urlLower, 'bpb')) {
            return 'bpb';
        }
        if (str_contains($urlLower, 'fee-sales')) {
            return 'fee_sales';
        }
        if (str_contains($urlLower, 'direksi/tagihan')) {
            return 'tagihan_direksi';
        }
        if (str_contains($urlLower, 'cashout/subkon')) {
            return 'cashout_subkon';
        }
        if (str_contains($urlLower, 'member-get-member')) {
            return 'mgm';
        }
        if (str_contains($urlLower, 'tab=legal')) {
            return 'legal';
        }
        if (str_contains($urlLower, 'keuangan') || str_contains($urlLower, 'tab=keuangan')) {
            return 'keuangan';
        }

        return 'siteplan';
    }

    private static function resolveRefId(
        string $targetModule,
        ?string $rawType,
        string $actionUrl,
        string $notifText,
        ?int $idKavling,
        ?int $idKonsumen,
        ?int $idProyek
    ): int|string|null {
        $queryParams = self::parseQueryParams($actionUrl);
        $path = self::extractUrlPath($actionUrl);

        switch ($targetModule) {
            case 'bpb':
                $val = $queryParams['open'] ?? $queryParams['id'] ?? null;
                return $val !== null && is_numeric($val) ? (int) $val : $val;

            case 'fee_sales':
                if (preg_match('#fee-sales/pengajuan/([^/?\#]+)#i', $path, $matches)) {
                    $token = trim($matches[1]);
                    return is_numeric($token) ? (int) $token : $token;
                }
                if (isset($queryParams['open_mkdt'])) {
                    return is_numeric($queryParams['open_mkdt']) ? (int) $queryParams['open_mkdt'] : $queryParams['open_mkdt'];
                }
                return $queryParams['id'] ?? $idKavling;

            case 'kavling_request':
                if (isset($queryParams['id_request']) && is_numeric($queryParams['id_request'])) {
                    return (int) $queryParams['id_request'];
                }
                if (isset($queryParams['open']) && is_numeric($queryParams['open'])) {
                    return (int) $queryParams['open'];
                }
                if (preg_match('/\(#(\d+)\)/', $notifText, $matches)) {
                    return (int) $matches[1];
                }
                return $idKavling;

            case 'tagihan_direksi':
                foreach (['no_inv', 'open', 'id'] as $key) {
                    if (isset($queryParams[$key]) && $queryParams[$key] !== '') {
                        return is_numeric($queryParams[$key]) ? (int) $queryParams[$key] : $queryParams[$key];
                    }
                }
                if (preg_match('#direksi/tagihan/([^/?\#]+)#i', $path, $matches)) {
                    $segment = trim($matches[1]);
                    return is_numeric($segment) ? (int) $segment : $segment;
                }
                return $idKavling;

            case 'cashout_subkon':
                foreach (['open_kavling', 'id_kavling', 'open', 'id'] as $key) {
                    if (isset($queryParams[$key]) && $queryParams[$key] !== '') {
                        return is_numeric($queryParams[$key]) ? (int) $queryParams[$key] : $queryParams[$key];
                    }
                }
                return $idKavling;

            case 'mgm':
                foreach (['id_bonus', 'id_referral', 'open', 'id'] as $key) {
                    if (isset($queryParams[$key]) && $queryParams[$key] !== '') {
                        return is_numeric($queryParams[$key]) ? (int) $queryParams[$key] : $queryParams[$key];
                    }
                }
                return $idKonsumen ?? $idKavling;

            case 'tiket_masalah':
                if ($rawType !== null && str_starts_with(strtolower($rawType), 'tiket_masalah|')) {
                    $parts = explode('|', $rawType);
                    if (isset($parts[3]) && is_numeric($parts[3])) {
                        return (int) $parts[3];
                    }
                }
                foreach (['id_tiket', 'open', 'id', 'id_kavling'] as $key) {
                    if (isset($queryParams[$key]) && $queryParams[$key] !== '') {
                        return is_numeric($queryParams[$key]) ? (int) $queryParams[$key] : $queryParams[$key];
                    }
                }
                return $idKavling;

            case 'keuangan':
            case 'legal':
            case 'siteplan':
            default:
                foreach (['id_kavling', 'id_dajam', 'open', 'id'] as $key) {
                    if (isset($queryParams[$key]) && $queryParams[$key] !== '') {
                        return is_numeric($queryParams[$key]) ? (int) $queryParams[$key] : $queryParams[$key];
                    }
                }
                return $idKavling ?? $idKonsumen ?? $idProyek;
        }
    }

    /**
     * @return array{path: string, query: array<string, string>}
     */
    private static function resolveSpaRoute(
        string $intent,
        string $targetModule,
        int|string|null $refId,
        string $actionUrl,
        ?int $idKavling,
        ?int $idKonsumen
    ): array {
        if ($intent === self::INTENT_ACTION_REQUIRED && in_array($targetModule, self::PERMOHONAN_MODULES, true)) {
            $query = ['module' => $targetModule];
            if ($refId !== null && $refId !== '') {
                $query['id'] = (string) $refId;
            }

            return [
                'path'  => '/permohonan',
                'query' => $query,
            ];
        }

        $urlParams = self::parseQueryParams($actionUrl);
        $urlLower  = strtolower($actionUrl);

        switch ($targetModule) {
            case 'bpb':
                return [
                    'path'  => '/bpb',
                    'query' => $refId !== null && $refId !== '' ? ['open' => (string) $refId] : [],
                ];

            case 'fee_sales':
                if (isset($urlParams['open_mkdt'])) {
                    return [
                        'path'  => '/fee-sales',
                        'query' => ['open_mkdt' => (string) $urlParams['open_mkdt']],
                    ];
                }
                return [
                    'path'  => '/fee-sales',
                    'query' => $refId !== null && $refId !== '' ? ['pengajuan' => (string) $refId] : [],
                ];

            case 'tagihan_direksi':
                return [
                    'path'  => '/direksi/tagihan',
                    'query' => $refId !== null && $refId !== '' ? ['open' => (string) $refId] : [],
                ];

            case 'cashout_subkon':
                $kavling = $urlParams['open_kavling'] ?? ($idKavling !== null ? (string) $idKavling : ($refId !== null ? (string) $refId : null));
                return [
                    'path'  => '/cashout/subkon',
                    'query' => $kavling !== null && $kavling !== '' ? ['open_kavling' => (string) $kavling] : [],
                ];

            case 'mgm':
                return [
                    'path'  => '/member-get-member',
                    'query' => $refId !== null && $refId !== '' ? ['id_referral' => (string) $refId] : [],
                ];

            case 'tiket_masalah':
                $query = [];
                if ($refId !== null && $refId !== '') {
                    $query['open'] = (string) $refId;
                }
                if ($idKavling !== null) {
                    $query['id_kavling'] = (string) $idKavling;
                }
                return [
                    'path'  => '/tiket-masalah',
                    'query' => $query,
                ];

            case 'legal':
                $query = ['tab' => 'legal'];
                if ($idKavling !== null) {
                    $query['id_kavling'] = (string) $idKavling;
                } elseif ($refId !== null && $refId !== '') {
                    $query['id_kavling'] = (string) $refId;
                }
                return [
                    'path'  => '/siteplan',
                    'query' => $query,
                ];

            case 'keuangan':
                if (str_contains($urlLower, 'hasil-akad')) {
                    return ['path' => '/keuangan/hasil-akad', 'query' => []];
                }
                if (str_contains($urlLower, 'list-kavling')) {
                    return [
                        'path'  => '/list-kavling',
                        'query' => isset($urlParams['search']) ? ['search' => (string) $urlParams['search']] : [],
                    ];
                }
                if (str_contains($urlLower, 'dana-jaminan') || str_contains($urlLower, 'dajam')) {
                    return [
                        'path'  => '/keuangan/dana-jaminan',
                        'query' => $refId !== null && $refId !== '' ? ['open' => (string) $refId] : [],
                    ];
                }
                $query = ['tab' => 'keuangan'];
                if ($idKavling !== null) {
                    $query['id_kavling'] = (string) $idKavling;
                }
                return [
                    'path'  => '/siteplan',
                    'query' => $query,
                ];

            case 'kavling_request':
            case 'siteplan':
            default:
                $query = [];
                $kavling = $urlParams['id_kavling'] ?? ($idKavling !== null ? (string) $idKavling : null);
                if ($kavling !== null && $kavling !== '') {
                    $query['id_kavling'] = (string) $kavling;
                }
                if (! empty($urlParams['tab'])) {
                    $query['tab'] = (string) $urlParams['tab'];
                } elseif ($idKonsumen !== null && $idKavling !== null) {
                    $query['tab'] = 'konsumen';
                }
                return [
                    'path'  => '/siteplan',
                    'query' => $query,
                ];
        }
    }

    /**
     * @return array<string, string>
     */
    private static function parseQueryParams(string $actionUrl): array
    {
        if ($actionUrl === '' || ! str_contains($actionUrl, '?')) {
            return [];
        }

        $queryStr = parse_url($actionUrl, PHP_URL_QUERY);
        if (! is_string($queryStr) || $queryStr === '') {
            return [];
        }

        $parsed = [];
        parse_str($queryStr, $parsed);

        $result = [];
        foreach ($parsed as $k => $v) {
            if (is_scalar($v)) {
                $result[(string) $k] = trim((string) $v);
            }
        }

        return $result;
    }

    private static function extractUrlPath(string $actionUrl): string
    {
        $path = parse_url($actionUrl, PHP_URL_PATH);

        return is_string($path) ? ltrim(trim($path), '/') : '';
    }

    private static function extractString(array|object $item, string $key): ?string
    {
        $val = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
        if ($val === null) {
            return null;
        }

        $str = trim((string) $val);

        return $str === '' ? null : $str;
    }

    private static function extractInt(array|object $item, string $key): ?int
    {
        $val = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
        if ($val === null || $val === '' || ! is_numeric($val)) {
            return null;
        }

        $intVal = (int) $val;

        return $intVal > 0 ? $intVal : null;
    }
}
