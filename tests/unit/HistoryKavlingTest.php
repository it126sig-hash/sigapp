<?php

namespace Tests\Unit;

use App\Repositories\HistoryRepository;
use App\Services\HistoryService;
use CodeIgniter\Test\CIUnitTestCase;

final class HistoryKavlingTest extends CIUnitTestCase
{
    public function testFormatActionLabel(): void
    {
        $service = new HistoryService();

        $this->assertSame('Set Harga Jual', $service->formatActionLabel('set_harga_jual'));
        $this->assertSame('Ubah Status Kavling', $service->formatActionLabel('ubah_status_kavling'));
        $this->assertSame('Update Progres Bangunan', $service->formatActionLabel('update_progres'));
        $this->assertSame('Custom Action Name', $service->formatActionLabel('custom_action_name'));
    }

    public function testGetByKavlingReturnsExpectedShape(): void
    {
        $repo = $this->createMock(HistoryRepository::class);
        $repo->method('hasTable')->willReturn(true);
        $repo->method('getByKavling')->willReturn([
            [
                'id' => 101,
                'module' => 'mkdt',
                'action' => 'set_harga_jual',
                'summary' => 'Harga jual diubah ke 250jt',
                'old_data' => '{"harga":200000000}',
                'new_data' => '{"harga":250000000}',
                'metadata' => null,
                'created_at' => '2026-06-01 10:00:00',
                'username' => 'admin',
            ],
            [
                'id' => 102,
                'module' => 'produksi',
                'action' => 'update_progres',
                'summary' => 'Pondasi selesai 100%',
                'old_data' => null,
                'new_data' => null,
                'metadata' => null,
                'created_at' => '2026-06-02 11:00:00',
                'username' => 'alika',
            ],
        ]);
        $repo->method('countByKavling')->willReturn(2);

        $service = new HistoryService($repo);
        $result = $service->getByKavling(5, [], 10, 0);

        $this->assertCount(2, $result['history']);
        $this->assertSame(2, $result['history_total']);
        $this->assertSame(10, $result['history_limit']);
        $this->assertSame(0, $result['history_offset']);
        $this->assertSame(2, $result['history_next_offset']);
        $this->assertFalse($result['history_has_more']);

        $firstRow = $result['history'][0];
        $this->assertSame('MKDT / Kavling', $firstRow->module_label);
        $this->assertSame('Set Harga Jual', $firstRow->action_label);
        $this->assertIsArray($firstRow->old_data);
        $this->assertSame(200000000, $firstRow->old_data['harga']);
    }
}
