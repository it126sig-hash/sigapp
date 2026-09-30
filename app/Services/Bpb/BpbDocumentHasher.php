<?php

namespace App\Services\Bpb;

final class BpbDocumentHasher
{
    public static function hash(array $request, array $items, array $files): string
    {
        usort($items, static fn(array $a, array $b): int => ((int) ($a['item_order'] ?? 0)) <=> ((int) ($b['item_order'] ?? 0)));
        usort($files, static fn(array $a, array $b): int => strcmp((string) ($a['logical_path'] ?? ''), (string) ($b['logical_path'] ?? '')));

        $payload = [
            'nomor' => $request['nomor'] ?? null,
            'pemohon' => [
                'id' => (int) ($request['applicant_user_id'] ?? 0),
                'nama' => trim((string) ($request['applicant_name'] ?? '')),
                'departemen' => trim((string) ($request['applicant_department'] ?? '')),
            ],
            'cc_user_id' => isset($request['cc_user_id']) ? (int) $request['cc_user_id'] : null,
            'approver_user_id' => isset($request['approver_user_id']) ? (int) $request['approver_user_id'] : null,
            'items' => array_map(static function (array $item): array {
                $canonical = [
                    'nama_barang' => trim((string) ($item['nama_barang'] ?? '')),
                    'jumlah' => number_format((float) ($item['jumlah'] ?? 0), 2, '.', ''),
                ];
                $unit = trim((string) ($item['satuan'] ?? ''));
                if ($unit !== '') {
                    $canonical['satuan'] = $unit;
                }
                $canonical['keterangan'] = trim((string) ($item['keterangan'] ?? ''));
                return $canonical;
            }, $items),
            'files' => array_map(static fn(array $file): array => [
                'category' => (string) ($file['category'] ?? ''),
                'name' => (string) ($file['original_name'] ?? ''),
                'size' => (int) ($file['file_size'] ?? 0),
                'sha256' => (string) ($file['file_sha256'] ?? ''),
            ], $files),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION));
    }
}
