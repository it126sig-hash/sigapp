<?php

use App\Services\PencairanAkadService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PencairanAkadImportValidationTest extends CIUnitTestCase
{
    private function item(): object
    {
        return (object) [
            'id_kavling' => 10,
            'jenis' => 'retensi',
            'nominal' => 6000000.0,
            'harga_kpr_acc' => 150000000.0,
            'total_retensi' => 18000000.0,
            'total_hasil_akad' => 132000000.0,
        ];
    }

    private function row(): array
    {
        return [
            'id_kavling' => '10',
            'jenis' => 'retensi',
            'nominal' => '6000000',
            'acc_kpr' => '150000000',
            'total_retensi' => '18000000',
            'rencana_hasil_akad' => '132000000',
        ];
    }

    public function testMatchingRowIsAccepted(): void
    {
        $this->assertNull(PencairanAkadService::findLockedMismatch($this->row(), $this->item()));
    }

    public function testTamperedNominalIsRejected(): void
    {
        $row = $this->row();
        $row['nominal'] = '9999999';

        $this->assertSame('nominal tidak cocok dengan data sistem', PencairanAkadService::findLockedMismatch($row, $this->item()));
    }

    public function testTamperedIdKavlingIsRejected(): void
    {
        $row = $this->row();
        $row['id_kavling'] = '99';

        $this->assertSame('id_kavling tidak cocok dengan data sistem', PencairanAkadService::findLockedMismatch($row, $this->item()));
    }

    public function testTamperedAccKprIsRejected(): void
    {
        $row = $this->row();
        $row['acc_kpr'] = '1';

        $this->assertSame('acc_kpr tidak cocok dengan data sistem', PencairanAkadService::findLockedMismatch($row, $this->item()));
    }

    public function testJenisMismatchIsRejected(): void
    {
        $row = $this->row();
        $row['jenis'] = 'tenor';

        $this->assertSame('jenis tidak cocok dengan data sistem', PencairanAkadService::findLockedMismatch($row, $this->item()));
    }
}
