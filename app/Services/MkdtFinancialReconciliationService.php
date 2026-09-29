<?php

namespace App\Services;

use App\Repositories\BookingPaymentRepository;
use CodeIgniter\Database\BaseConnection;

class MkdtFinancialReconciliationService
{
    private const DIRECT_FIELDS = [
        'Biaya Administrasi' => 'harga_administrasi',
        'BPHTB' => 'harga_bphtb',
        'Biaya Proses' => 'harga_biaya_proses',
        'PPN' => 'harga_ppn',
        'Kavling Strategis' => 'harga_penambahan',
        'Kelebihan Tanah' => 'harga_penambahan_tanah',
        'Turun KPR' => 'harga_penambahan_um',
    ];

    private const ALLOWED_FIELDS = [
        'harga_administrasi', 'harga_bphtb', 'harga_biaya_proses', 'harga_ppn',
        'harga_penambahan', 'harga_penambahan_tanah', 'harga_penambahan_um',
        'harga_uang_muka', 'harga_diskon_uang_muka', 'harga_sbum',
    ];

    private MkdtFinancialBreakdownService $financial;

    public function __construct(private readonly BaseConnection $db)
    {
        $this->financial = new MkdtFinancialBreakdownService($db);
    }

    public function audit(?int $idMkdt = null): array
    {
        $builder = $this->db->table('mkdt m')
            ->select('m.*, k.id_kavling')
            ->join('kavling k', 'k.id_mkdt = m.id_mkdt', 'left')
            ->where('m.status_mkdt !=', 'Batal');
        if ($idMkdt !== null) {
            $builder->where('m.id_mkdt', $idMkdt);
        }

        $report = [
            'generated_at' => date(DATE_ATOM),
            'mode' => 'audit',
            'filter' => ['id_mkdt' => $idMkdt],
            'direct_corrections' => [],
            'invalid_turun_kpr' => [],
            'unresolved_um' => [],
            'payment_integrity_issues' => [],
            'not_fully_paid' => [],
            'suggested_manifest' => ['rows' => []],
        ];

        foreach ($builder->orderBy('m.id_mkdt')->get()->getResultArray() as $mkdt) {
            $id = (int) $mkdt['id_mkdt'];
            $integrity = $this->paymentIntegrity($id);
            if ($integrity !== []) {
                $report['payment_integrity_issues'][] = ['id_mkdt' => $id, 'issues' => $integrity];
                continue;
            }

            $summary = (new BookingPaymentRepository($this->db))->installmentSummary($id);
            if ((float) $summary['total_tagihan'] <= 0 || (float) $summary['sisa_tagihan'] > 0.01) {
                $report['not_fully_paid'][] = [
                    'id_mkdt' => $id,
                    'total_tagihan' => (float) $summary['total_tagihan'],
                    'sudah_bayar' => (float) $summary['sudah_bayar'],
                    'sisa_tagihan' => (float) $summary['sisa_tagihan'],
                    'reason' => (float) $summary['total_tagihan'] <= 0 ? 'tagihan_tidak_tersedia' : 'belum_lunas',
                ];
                continue;
            }

            $paid = $this->paidComponents($id);
            $changes = [];
            $before = [];
            foreach (self::DIRECT_FIELDS as $item => $field) {
                $actual = (float) ($paid[$item] ?? 0);
                $current = (float) ($mkdt[$field] ?? 0);
                if ($item === 'Turun KPR' && (float) ($mkdt['harga_kpr_acc'] ?? 0) <= 0) {
                    $actual = 0;
                    if ($current > 0) {
                        $report['invalid_turun_kpr'][] = [
                            'id_mkdt' => $id, 'old' => $current, 'proposed' => 0,
                            'reason' => 'ACC KPR nol',
                        ];
                    }
                }
                if (abs($current - $actual) > 0.01) {
                    $before[$field] = $current;
                    $changes[$field] = $actual;
                    $report['direct_corrections'][] = [
                        'id_mkdt' => $id, 'item' => $item, 'field' => $field,
                        'old' => $current, 'paid' => $actual,
                    ];
                }
            }

            $umTarget = $this->financial->targets($mkdt)['um'];
            $umPaid = (float) ($paid['Uang Muka'] ?? 0);
            if (abs($umTarget - $umPaid) > 0.01) {
                $report['unresolved_um'][] = [
                    'id_mkdt' => $id,
                    'target_um' => $umTarget,
                    'paid_um' => $umPaid,
                    'difference' => $umTarget - $umPaid,
                    'decision_required' => 'Pilih diskon_uang_muka atau SBUM; tidak diisi otomatis.',
                ];
            }

            if ($changes !== []) {
                $report['suggested_manifest']['rows'][] = [
                    'id_mkdt' => $id,
                    'expected_updated_at' => $mkdt['updated_at'] ?? null,
                    'before' => $before,
                    'changes' => $changes,
                    'reason' => 'Rekonsiliasi target MKDT terhadap breakdown pembayaran Finance.',
                ];
            }
        }

        $report['counts'] = [
            'direct_corrections' => count($report['direct_corrections']),
            'invalid_turun_kpr' => count($report['invalid_turun_kpr']),
            'unresolved_um' => count($report['unresolved_um']),
            'payment_integrity_issues' => count($report['payment_integrity_issues']),
            'not_fully_paid' => count($report['not_fully_paid']),
            'manifest_rows' => count($report['suggested_manifest']['rows']),
        ];
        return $report;
    }

    public function apply(array $manifest, ?int $actorId = null): array
    {
        $result = ['mode' => 'apply', 'generated_at' => date(DATE_ATOM), 'applied' => [], 'skipped' => []];
        foreach (($manifest['rows'] ?? []) as $entry) {
            $id = (int) ($entry['id_mkdt'] ?? 0);
            if ($id <= 0 || empty($entry['changes']) || !is_array($entry['changes'])) {
                throw new \DomainException('Manifest memuat baris yang tidak valid.');
            }
            $unknown = array_diff(array_keys($entry['changes']), self::ALLOWED_FIELDS);
            if ($unknown !== []) {
                throw new \DomainException('Field manifest tidak diizinkan: ' . implode(', ', $unknown));
            }

            $this->db->transException(true)->transBegin();
            try {
                $sql = 'SELECT * FROM ' . $this->db->prefixTable('mkdt') . ' WHERE id_mkdt = ?';
                if ($this->db->DBDriver !== 'SQLite3') {
                    $sql .= ' FOR UPDATE';
                }
                $current = $this->db->query($sql, [$id])->getRowArray();
                if (!$current) {
                    throw new \DomainException("MKDT {$id} tidak ditemukan.");
                }

                if ($this->alreadyApplied($current, $entry['changes'])) {
                    $this->db->transCommit();
                    $result['skipped'][] = ['id_mkdt' => $id, 'reason' => 'already_applied'];
                    continue;
                }
                if (($current['updated_at'] ?? null) !== ($entry['expected_updated_at'] ?? null)) {
                    throw new \DomainException("Manifest MKDT {$id} basi: updated_at sudah berubah.");
                }
                foreach (($entry['before'] ?? []) as $field => $value) {
                    if (round((float) ($current[$field] ?? 0), 2) !== round((float) $value, 2)) {
                        throw new \DomainException("Manifest MKDT {$id} basi: nilai {$field} sudah berubah.");
                    }
                }

                $changes = $entry['changes'];
                $changes['updated_at'] = date('Y-m-d H:i:s');
                if ($actorId !== null) {
                    $changes['edit_by'] = $actorId;
                }
                $this->db->table('mkdt')->where('id_mkdt', $id)->update($changes);

                $after = array_merge($current, $changes);
                if ($this->db->tableExists('history_log')) {
                    $this->db->table('history_log')->insert([
                        'module' => 'mkdt',
                        'reference_type' => 'mkdt',
                        'reference_id' => $id,
                        'id_kavling' => (int) ($current['id_kavling'] ?? 0) ?: null,
                        'action' => MkdtHistoryService::ACTION_RECONCILE_FINANCIAL,
                        'summary' => 'Rekonsiliasi komponen keuangan MKDT dari manifest yang disetujui.',
                        'old_data' => json_encode(array_intersect_key($current, $entry['changes']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'new_data' => json_encode(array_intersect_key($after, $entry['changes']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'metadata' => json_encode(['reason' => $entry['reason'] ?? null, 'source' => 'finance:reconcile-mkdt'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'add_by' => $actorId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->db->transCommit();
                $result['applied'][] = ['id_mkdt' => $id, 'changes' => $entry['changes']];
            } catch (\Throwable $e) {
                $this->db->transRollback();
                throw $e;
            }
        }
        return $result;
    }

    private function paymentIntegrity(int $idMkdt): array
    {
        $rows = $this->db->table('log_pembayaran lp')
            ->select('lp.id_pembayaran, lp.nominal, COUNT(lpd.id_pembayaran_detail) AS detail_count, COALESCE(SUM(lpd.nominal),0) AS detail_total', false)
            ->join('log_pembayaran_detail lpd', 'lpd.id_pembayaran = lp.id_pembayaran', 'left')
            ->where('lp.id_mkdt', $idMkdt)->where('lp.is_deleted', 0)
            ->where("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) != 'refund'", null, false)
            ->groupBy('lp.id_pembayaran, lp.nominal')->get()->getResultArray();
        $issues = [];
        foreach ($rows as $row) {
            if ((int) $row['detail_count'] === 0 || abs((float) $row['nominal'] - (float) $row['detail_total']) > 0.01) {
                $issues[] = [
                    'id_pembayaran' => (int) $row['id_pembayaran'],
                    'header' => (float) $row['nominal'],
                    'detail' => (float) $row['detail_total'],
                    'type' => (int) $row['detail_count'] === 0 ? 'missing_detail' : 'unbalanced',
                ];
            }
        }
        return $issues;
    }

    private function paidComponents(int $idMkdt): array
    {
        $rows = $this->db->table('log_pembayaran_detail lpd')
            ->select("CASE WHEN kl.kategori = 'BO' AND lpd.booking_is_installment = 1 THEN 'Uang Muka' ELSE kl.item END AS item, SUM(lpd.nominal) AS nominal", false)
            ->join('log_pembayaran lp', 'lp.id_pembayaran = lpd.id_pembayaran')
            ->join('keuangan_item_list kl', 'kl.id_keuangan_item_list = lpd.id_keuangan_item_list')
            ->where('lp.id_mkdt', $idMkdt)->where('lp.is_deleted', 0)
            ->where("LOWER(REPLACE(TRIM(COALESCE(lp.payment_type,'')), ';', '')) != 'refund'", null, false)
            ->where("NOT (kl.kategori = 'BO' AND COALESCE(lpd.booking_is_installment, 0) = 0)", null, false)
            ->groupBy('1', false)->get()->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            $item = $this->canonicalItem((string) $row['item']);
            $result[$item] = ($result[$item] ?? 0) + (float) $row['nominal'];
        }
        return $result;
    }

    private function canonicalItem(string $item): string
    {
        $normalized = strtolower($item);
        foreach ([
            'uang muka' => 'Uang Muka',
            'administrasi' => 'Biaya Administrasi',
            'bphtb' => 'BPHTB',
            'proses' => 'Biaya Proses',
            'ppn' => 'PPN',
            'strategis' => 'Kavling Strategis',
            'kelebihan' => 'Kelebihan Tanah',
            'turun kpr' => 'Turun KPR',
        ] as $needle => $canonical) {
            if (str_contains($normalized, $needle)) {
                return $canonical;
            }
        }
        return $item;
    }

    private function alreadyApplied(array $current, array $changes): bool
    {
        foreach ($changes as $field => $value) {
            if (round((float) ($current[$field] ?? 0), 2) !== round((float) $value, 2)) {
                return false;
            }
        }
        return true;
    }
}
