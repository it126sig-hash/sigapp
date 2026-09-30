<?php

namespace App\Services\Bpb;

final class BpbNumberFormatter
{
    private const ROMAN_MONTHS = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public static function format(int $sequence, int $month, int $year): string
    {
        if ($sequence < 1 || ! isset(self::ROMAN_MONTHS[$month])) {
            throw new \InvalidArgumentException('Nomor urut atau bulan tidak valid.');
        }
        return str_pad((string) $sequence, 3, '0', STR_PAD_LEFT) . '/BPB/' . self::ROMAN_MONTHS[$month] . '/' . $year;
    }
}
