    public function exportExcelJatuhTempo($request)
    {
        $builder = $this->keuRepo->getBelumLunasGroupedQuery();
        
        $today = date('Y-m-d');
        $builder->where('keu_agg.jatuh_tempo_tgl <=', $today);

        $id_proyek = resolve_active_proyek_id($request->getVar('id_proyek'));
        if ($id_proyek)
            $builder->where('p.id_proyek', $id_proyek);
        if ($request->getVar('id_cluster'))
            $builder->where('cl.id_cluster', $request->getVar('id_cluster'));
        if ($request->getVar('id_jalan'))
            $builder->where('j.id_jalan', $request->getVar('id_jalan'));

        $isKpr = $request->getVar('is_kpr');
        if ($isKpr !== null && $isKpr !== '') {
            $builder->where('m.is_kpr', $isKpr);
        }

        $bookingRange = $request->getVar('booking_tgl_range');
        if (!empty($bookingRange)) {
            $dates = explode(' to ', $bookingRange);
            if (count($dates) === 2) {
                $builder->where('m.booking_tgl >=', $dates[0]);
                $builder->where('m.booking_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('m.booking_tgl', $dates[0]);
            }
        }

        $jatuhTempoRange = $request->getVar('jatuh_tempo_tgl_range');
        if (!empty($jatuhTempoRange)) {
            $dates = explode(' to ', $jatuhTempoRange);
            if (count($dates) === 2) {
                $builder->where('keu_agg.jatuh_tempo_tgl >=', $dates[0]);
                $builder->where('keu_agg.jatuh_tempo_tgl <=', $dates[1]);
            } else if (count($dates) === 1) {
                $builder->where('keu_agg.jatuh_tempo_tgl', $dates[0]);
            }
        }

        $searchValue = $request->getVar('search_value');
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('c.nama_konsumen', $searchValue)
                ->orLike('k.no_kavling', $searchValue)
                ->orLike('j.nama_jalan', $searchValue)
                ->groupEnd();
        }

        $builder->orderBy('keu_agg.jatuh_tempo_tgl', 'ASC');
        $rekapData = $builder->get()->getResult();

        $spreadsheet = new Spreadsheet();
        
        // =====================================
        // SHEET 1: REKAP
        // =====================================
        $sheetRekap = $spreadsheet->getActiveSheet();
        $sheetRekap->setTitle('REKAP');

        $headersRekap = ['NO', 'KONSUMEN', 'BLOK / UNIT', 'NO KAVLING', 'TYPE', 'TUNAI/KPR', 'JATUH TEMPO TERDEKAT', 'KETERLAMBATAN (HARI)', 'TOTAL TAGIHAN', 'SUDAH BAYAR', 'SISA TAGIHAN', 'JUMLAH ITEM'];
        
        // Add headers
        $col = 'A';
        foreach ($headersRekap as $h) {
            $sheetRekap->setCellValue($col . '1', $h);
            $col++;
        }

        // Add Data
        $rowNum = 2;
        $no = 1;
        $mkdtIds = [];
        foreach ($rekapData as $row) {
            $mkdtIds[] = $row->id_mkdt;

            $diffTime = strtotime($today) - strtotime($row->jatuh_tempo_tgl);
            $diffDays = floor($diffTime / (60 * 60 * 24));
            
            $sheetRekap->setCellValue('A' . $rowNum, $no++);
            $sheetRekap->setCellValue('B' . $rowNum, $row->nama_konsumen);
            $sheetRekap->setCellValue('C' . $rowNum, $row->nama_jalan);
            $sheetRekap->setCellValueExplicit('D' . $rowNum, $row->no_kavling, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetRekap->setCellValueExplicit('E' . $rowNum, $row->tipe_pricelist, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetRekap->setCellValue('F' . $rowNum, $row->is_kpr === '1' || $row->is_kpr === 'KPR' ? 'KPR' : 'TUNAI');
            
            // Excel date formatting
            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($row->jatuh_tempo_tgl));
            $sheetRekap->setCellValue('G' . $rowNum, $excelDate);
            $sheetRekap->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('dd-mmm-yyyy');

            $sheetRekap->setCellValue('H' . $rowNum, $diffDays);
            
            $sheetRekap->setCellValue('I' . $rowNum, (float)$row->total_tagihan);
            $sheetRekap->setCellValue('J' . $rowNum, (float)$row->sudah_bayar);
            $sheetRekap->setCellValue('K' . $rowNum, (float)$row->sisa_tagihan);
            
            $sheetRekap->getStyle('I'.$rowNum.':K'.$rowNum)->getNumberFormat()->setFormatCode('#,##0');
            
            $sheetRekap->setCellValue('L' . $rowNum, (int)$row->jumlah_tagihan);
            
            $rowNum++;
        }
        
        // Header styling
        $lastColRekap = chr(ord('A') + count($headersRekap) - 1);
        $sheetRekap->getStyle('A1:' . $lastColRekap . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2057a3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        
        foreach (range('A', $lastColRekap) as $c) {
            $sheetRekap->getColumnDimension($c)->setAutoSize(true);
        }
        $sheetRekap->freezePane('A2');

        // =====================================
        // SHEET 2: DETAIL
        // =====================================
        $sheetDetail = $spreadsheet->createSheet();
        $sheetDetail->setTitle('DETAIL');

        $headersDetail = ['NO', 'KONSUMEN', 'BLOK / UNIT', 'NO KAVLING', 'TYPE', 'TUNAI/KPR', 'BERITA ACARA', 'JATUH TEMPO', 'KETERLAMBATAN (HARI)', 'STATUS', 'NOMINAL', 'PEMBAYARAN', 'STATUS PEMBAYARAN'];
        
        $col = 'A';
        foreach ($headersDetail as $h) {
            $sheetDetail->setCellValue($col . '1', $h);
            $col++;
        }

        if (!empty($mkdtIds)) {
            $detailData = $this->keuRepo->select([
                'keuangan.id_mkdt', 'keuangan.berita_acara', 'keuangan.jatuh_tempo_tgl',
                'keuangan.nominal', 'keuangan.sudah_dibayar', 'keuangan.status', 'keuangan.is_void'
            ])
            ->whereIn('keuangan.id_mkdt', $mkdtIds)
            ->where('keuangan.sudah_dibayar', 0)
            ->where('keuangan.jatuh_tempo_tgl <=', $today)
            ->orderBy('keuangan.jatuh_tempo_tgl', 'ASC')
            ->findAll();

            // Group detail by mkdt
            $groupedDetail = [];
            foreach ($detailData as $d) {
                $groupedDetail[$d['id_mkdt']][] = $d;
            }

            $rowNumD = 2;
            $noD = 1;
            foreach ($rekapData as $parent) {
                if (isset($groupedDetail[$parent->id_mkdt])) {
                    foreach ($groupedDetail[$parent->id_mkdt] as $child) {
                        $diffTimeD = strtotime($today) - strtotime($child['jatuh_tempo_tgl']);
                        $diffDaysD = floor($diffTimeD / (60 * 60 * 24));
                        
                        $statusPembayaran = 'BELUM LUNAS';
                        if ($child['is_void'] == 1) $statusPembayaran = 'VOID';
                        else if ($child['sudah_dibayar'] == 1) $statusPembayaran = 'LUNAS';

                        $sheetDetail->setCellValue('A' . $rowNumD, $noD++);
                        $sheetDetail->setCellValue('B' . $rowNumD, $parent->nama_konsumen);
                        $sheetDetail->setCellValue('C' . $rowNumD, $parent->nama_jalan);
                        $sheetDetail->setCellValueExplicit('D' . $rowNumD, $parent->no_kavling, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheetDetail->setCellValueExplicit('E' . $rowNumD, $parent->tipe_pricelist, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheetDetail->setCellValue('F' . $rowNumD, $parent->is_kpr === '1' || $parent->is_kpr === 'KPR' ? 'KPR' : 'TUNAI');
                        
                        $sheetDetail->setCellValue('G' . $rowNumD, $child['berita_acara']);
                        
                        $excelDateD = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($child['jatuh_tempo_tgl']));
                        $sheetDetail->setCellValue('H' . $rowNumD, $excelDateD);
                        $sheetDetail->getStyle('H' . $rowNumD)->getNumberFormat()->setFormatCode('dd-mmm-yyyy');
                        
                        $sheetDetail->setCellValue('I' . $rowNumD, $diffDaysD);
                        $sheetDetail->setCellValue('J' . $rowNumD, $child['status']);
                        
                        $sheetDetail->setCellValue('K' . $rowNumD, (float)$child['nominal']);
                        // Note: The detail query doesn't have partial payment amount historically on child row unless mapped. We use 0 as specified in example if not available
                        $sheetDetail->setCellValue('L' . $rowNumD, (float)($child['sudah_dibayar'] == 1 ? $child['nominal'] : 0));
                        
                        $sheetDetail->getStyle('K'.$rowNumD.':L'.$rowNumD)->getNumberFormat()->setFormatCode('#,##0');
                        
                        $sheetDetail->setCellValue('M' . $rowNumD, $statusPembayaran);
                        
                        $rowNumD++;
                    }
                }
            }
        }

        $lastColDetail = chr(ord('A') + count($headersDetail) - 1);
        $sheetDetail->getStyle('A1:' . $lastColDetail . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2057a3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        foreach (range('A', $lastColDetail) as $c) {
            $sheetDetail->getColumnDimension($c)->setAutoSize(true);
        }
        $sheetDetail->freezePane('A2');
        
        $spreadsheet->setActiveSheetIndex(0);

        ob_start();
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        return [
            'status' => true,
            'file'   => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData)
        ];
    }
