<?php

namespace Tests\Unit;

use App\Services\Bpb\BpbDocumentHasher;
use CodeIgniter\Test\CIUnitTestCase;

final class BpbDocumentHasherTest extends CIUnitTestCase
{
    public function testHashIsStableAcrossInputOrdering(): void
    {
        $request = ['nomor' => '001/BPB/IX/2026', 'applicant_user_id' => 7, 'applicant_name' => 'Ayu', 'applicant_department' => 'Umum', 'cc_user_id' => 3, 'approver_user_id' => 4];
        $items = [
            ['item_order' => 2, 'nama_barang' => 'Kertas', 'jumlah' => 2, 'keterangan' => 'A4'],
            ['item_order' => 1, 'nama_barang' => 'Tinta', 'jumlah' => 1, 'keterangan' => 'Hitam'],
        ];
        $files = [
            ['logical_path' => 'z.pdf', 'category' => 'request', 'original_name' => 'z.pdf', 'file_size' => 20, 'file_sha256' => str_repeat('a', 64)],
            ['logical_path' => 'a.png', 'category' => 'request', 'original_name' => 'a.png', 'file_size' => 10, 'file_sha256' => str_repeat('b', 64)],
        ];

        $first = BpbDocumentHasher::hash($request, $items, $files);
        $second = BpbDocumentHasher::hash($request, array_reverse($items), array_reverse($files));

        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);
    }

    public function testHashChangesWhenSignedContentChanges(): void
    {
        $request = ['nomor' => '001/BPB/IX/2026', 'applicant_user_id' => 7, 'applicant_name' => 'Ayu', 'applicant_department' => 'Umum', 'approver_user_id' => 4];
        $items = [['item_order' => 1, 'nama_barang' => 'Tinta', 'jumlah' => 1, 'keterangan' => 'Hitam']];
        $changed = $items;
        $changed[0]['jumlah'] = 2;

        $this->assertNotSame(BpbDocumentHasher::hash($request, $items, []), BpbDocumentHasher::hash($request, $changed, []));
    }

    public function testHashChangesWhenItemUnitChanges(): void
    {
        $request = ['nomor' => '001/BPB/IX/2026', 'applicant_user_id' => 7, 'applicant_name' => 'Ayu', 'applicant_department' => 'Umum'];
        $items = [['item_order' => 1, 'nama_barang' => 'Kertas', 'jumlah' => 2, 'satuan' => 'rim', 'keterangan' => 'A4']];
        $changed = $items;
        $changed[0]['satuan'] = 'box';

        $this->assertNotSame(BpbDocumentHasher::hash($request, $items, []), BpbDocumentHasher::hash($request, $changed, []));
    }
}
