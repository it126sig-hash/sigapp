<?php

namespace App\Validation;

/**
 * TransaksiControllerValidation
 *
 * Validation rules for TransaksiControllerValidation
 *
 * @package App\Validation
 */
class TransaksiControllerValidation
{
    /**
     * Get validation rules
     *
     * @return array Validation rules array
     */
    public static function getRules(): array
    {
        return [
            'nama' => 'required|max_length[255]',
            'jumlah' => 'required|numeric',
            'tanggal' => 'required|valid_date',
        ];
    }

    /**
     * Get validation rules for nama field
     *
     * @return string Validation rule string
     */
    public static function getNamaRules(): string
    {
        return 'required|max_length[255]';
    }

    /**
     * Get validation rules for jumlah field
     *
     * @return string Validation rule string
     */
    public static function getJumlahRules(): string
    {
        return 'required|numeric';
    }

    /**
     * Get validation rules for tanggal field
     *
     * @return string Validation rule string
     */
    public static function getTanggalRules(): string
    {
        return 'required|valid_date';
    }

}
