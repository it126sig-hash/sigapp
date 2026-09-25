<?php

use App\Services\PrintService;
use CodeIgniter\Test\CIUnitTestCase;

final class PrintServiceSpptbHistoryTest extends CIUnitTestCase
{
    public function testActiveSpptbPrecedesChronologicalHistoryAndUsesPerConsumerFooter(): void
    {
        $service = new PrintService();
        $active = $this->spptbData(30, 'Konsumen Aktif', 'SPPTB-001', 'active-ktp');
        $legacy = $this->spptbData(10, 'Konsumen Legacy', 'SPPTB-LEGACY', 'legacy-ktp');
        $legacy->id_mkdt = 19;
        $legacy->created_at = '2025-01-01 00:00:00';
        $snapshot = $this->spptbData(20, 'Konsumen Lama', 'SPPTB-001', 'history-ktp');

        $pdf = new class {
            public array $pages = [];
            public string $filename = '';
            public function generate($html, $filename): void
            {
                $this->pages = $html;
                $this->filename = $filename;
            }
        };

        $this->setProperty($service, 'proyek', new class {
            public function find(): object
            {
                return (object) [
                    'id_proyek' => 7,
                    'nama_proyek' => 'Proyek Test',
                    'nama_pt' => 'PT Test',
                    'logo' => '',
                    'bank' => 'Bank Test',
                    'no_rek' => '123',
                    'atas_nama' => 'PT Test',
                ];
            }
        });
        $this->setProperty($service, 'transaksi', new class($active, $legacy) {
            public function __construct(private object $active, private object $legacy) {}
            public function getSpptbData(): object { return $this->active; }
            public function getLegacyReplacementSpptbData(): array { return [$this->legacy]; }
        });
        $this->setProperty($service, 'keuanganModel', new class {
            private int $idMkdt = 20;
            public function where(string $field, int $value): self { $this->idMkdt = $value; return $this; }
            public function orderBy(): self { return $this; }
            public function find(): array
            {
                $nominal = $this->idMkdt === 19 ? 111 : 333;
                return [(object) ['status' => 'UM', 'jatuh_tempo_tgl' => '2025-01-01', 'nominal' => $nominal]];
            }
        });
        $this->setProperty($service, 'mkdtHistoryService', new class($snapshot) {
            public function __construct(private object $snapshot) {}
            public function getConsumerReplacementSnapshots(): array
            {
                return [(object) [
                    'created_at' => '2025-02-01 00:00:00',
                    'old_data' => [
                        'spptb_data' => (array) $this->snapshot,
                        'list_tagihan' => [['status' => 'UM', 'jatuh_tempo_tgl' => '2025-02-01', 'nominal' => 222]],
                    ],
                ]];
            }
        });
        $this->setProperty($service, 'fileAccessService', new class {
            public function existingPath(?string $path): ?string
            {
                return $path ? 'resolved/' . $path : null;
            }
        });
        $this->setProperty($service, 'mpdf', $pdf);

        $service->printSpptb(50, 20, 7);

        $this->assertCount(9, $pdf->pages);
        $this->assertStringContainsString('Konsumen Aktif', $pdf->pages[0]['html']);
        $this->assertStringContainsString('SPPTB-001', $pdf->pages[0]['html']);
        $this->assertStringContainsString('resolved/active-ktp', $pdf->pages[0]['footer']);

        $this->assertStringContainsString('Konsumen Legacy', $pdf->pages[3]['html']);
        $this->assertStringContainsString('SPPTB-LEGACY', $pdf->pages[3]['html']);
        $this->assertStringContainsString('resolved/legacy-ktp', $pdf->pages[3]['footer']);

        $this->assertStringContainsString('Konsumen Lama', $pdf->pages[6]['html']);
        $this->assertStringContainsString('SPPTB-001', $pdf->pages[6]['html']);
        $this->assertStringContainsString('resolved/history-ktp', $pdf->pages[6]['footer']);
        $this->assertStringContainsString('222', $pdf->pages[7]['html']);
        $this->assertStringContainsString('Konsumen Aktif', $pdf->filename);
    }

    private function spptbData(int $idKonsumen, string $name, string $number, string $ktp): object
    {
        return (object) [
            'id_proyek' => 7,
            'id_mkdt' => 20,
            'id_konsumen' => $idKonsumen,
            'nama_konsumen' => $name,
            'no_spptb' => $number,
            'file_ktp' => $ktp,
            'file_npwp' => null,
            'kode_referal' => '',
            'referred_by_nama' => '',
            'referred_by_kode' => '',
            'nik' => '',
            'npwp' => '',
            'alamat_konsumen' => '',
            'email_konsumen' => '',
            'hp_konsumen' => '',
            'nama_instansi' => '',
            'alamat_instansi' => '',
            'email_instansi' => '',
            'alamat_surat' => '',
            'pekerjaan' => '',
            'bidang_pekerjaan' => '',
            'lama_bekerja' => '',
            'status_pekerjaan_pasangan' => '',
            'hp_pasangan' => '',
            'nik_pasangan' => '',
            'nama_pasangan' => '',
            'instansi_pasangan' => '',
            'sales' => '',
            'nama_jalan' => 'A',
            'no_kavling' => '1',
            'tipe_pricelist' => 'T1',
            'lb' => 36,
            'luas_tanah' => 72,
            'is_kpr' => 0,
            'is_allin' => 0,
            'harga_jual' => 1000,
            'harga_kpr' => 0,
            'harga_uang_muka' => 1000,
            'harga_bphtb' => 0,
            'harga_biaya_proses' => 0,
            'harga_administrasi' => 0,
            'harga_penambahan_tanah' => 0,
            'harga_penambahan' => 0,
            'harga_ppn' => 0,
            'harga_sbum' => 0,
            'harga_diskon_uang_muka' => 0,
            'harga_allin' => 0,
            'harga_jual_net' => 1000,
            'booking_fee' => 0,
            'booking_tgl' => '2025-01-01',
        ];
    }

    private function setProperty(object $target, string $name, mixed $value): void
    {
        $property = new ReflectionProperty($target, $name);
        $property->setValue($target, $value);
    }
}
