<?php

namespace App\Repositories;

use App\Models\FinanceLedgerModel;

class FinanceLedgerRepository extends FinanceLedgerModel
{
    public function findBySource(string $sourceType, int $sourceId): ?array
    {
        return $this->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->first();
    }

    public function upsertBySource(array $payload): int
    {
        $sourceType = (string) $payload['source_type'];
        $sourceId = (int) $payload['source_id'];
        $existing = $this->findBySource($sourceType, $sourceId);

        if ($existing) {
            $id = (int) $existing[$this->primaryKey];
            unset($payload['created_at'], $payload['add_by']);
            $this->update($id, $payload);
            return $id;
        }

        $this->insert($payload);
        return (int) $this->getInsertID();
    }

    public function voidBySource(string $sourceType, int $sourceId, ?int $actorId = null): bool
    {
        $existing = $this->findBySource($sourceType, $sourceId);
        if (!$existing) {
            return true;
        }

        return (bool) $this->update((int) $existing[$this->primaryKey], [
            'status' => 'void',
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by' => $actorId,
            'edit_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function sumIncome(array $filters = []): float
    {
        $builder = $this->selectSum('nominal', 'total')
            ->where('direction', 'income')
            ->where('status', 'active')
            ->where('is_deleted', 0);

        if (!empty($filters['id_mkdt'])) {
            $builder->where('id_mkdt', (int) $filters['id_mkdt']);
        }

        if (!empty($filters['id_kavling'])) {
            $builder->where('id_kavling', (int) $filters['id_kavling']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('tanggal_transaksi >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('tanggal_transaksi <=', $filters['date_to']);
        }

        $row = $builder->first();
        return (float) ($row['total'] ?? 0);
    }
}
