<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddRukoToOthersType extends Migration
{
    private const TABLE = 'others';
    private const COLUMN = 'tipe';
    private const RUKO_TYPE = 'ruko';

    public function up()
    {
        $enumValues = $this->getEnumValues();

        if (in_array(self::RUKO_TYPE, $enumValues, true)) {
            return;
        }

        $enumValues[] = self::RUKO_TYPE;
        $this->alterEnum($enumValues);
    }

    public function down()
    {
        $enumValues = $this->getEnumValues();

        if (!in_array(self::RUKO_TYPE, $enumValues, true)) {
            return;
        }

        $rukoCount = $this->db->table(self::TABLE)
            ->where(self::COLUMN, self::RUKO_TYPE)
            ->countAllResults();

        if ($rukoCount > 0) {
            throw new RuntimeException(
                'Rollback dibatalkan karena masih ada data others dengan tipe ruko.'
            );
        }

        $enumValues = array_values(array_filter(
            $enumValues,
            static fn (string $value): bool => $value !== self::RUKO_TYPE
        ));

        $this->alterEnum($enumValues);
    }

    /**
     * @return list<string>
     */
    private function getEnumValues(): array
    {
        if (!$this->db->tableExists(self::TABLE) || !$this->db->fieldExists(self::COLUMN, self::TABLE)) {
            throw new RuntimeException('Kolom others.tipe tidak ditemukan.');
        }

        $row = $this->db
            ->query('SHOW COLUMNS FROM `' . self::TABLE . '` LIKE ' . $this->db->escape(self::COLUMN))
            ->getRowArray();
        $columnType = (string) ($row['Type'] ?? '');

        if (!preg_match('/^enum\((.*)\)$/i', $columnType, $matches)) {
            throw new RuntimeException('Kolom others.tipe bukan ENUM.');
        }

        $enumValues = str_getcsv($matches[1], ',', "'", '\\');
        $enumValues = array_values(array_filter(
            array_map('trim', $enumValues),
            static fn (string $value): bool => $value !== ''
        ));

        if ($enumValues === []) {
            throw new RuntimeException('Daftar ENUM pada kolom others.tipe kosong.');
        }

        return $enumValues;
    }

    /**
     * @param list<string> $enumValues
     */
    private function alterEnum(array $enumValues): void
    {
        $escapedValues = array_map(
            fn (string $value): string => $this->db->escape($value),
            $enumValues
        );

        $this->db->query(
            'ALTER TABLE `' . self::TABLE . '` MODIFY COLUMN `' . self::COLUMN . '` ENUM('
            . implode(', ', $escapedValues)
            . ') NULL DEFAULT NULL'
        );
    }
}
