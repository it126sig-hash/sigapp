<?php

namespace App\Services;

use App\Repositories\KalendarRepository;

class KalendarService
{
    public const EVENT_META = [
        'pembayaran'      => ['label' => 'Pembayaran',      'color' => '#ff40d2', 'textColor' => 'white'],
        'jatuh_tempo'     => ['label' => 'Jatuh Tempo',     'color' => 'red',     'textColor' => 'white'],
        'akad'            => ['label' => 'Akad',            'color' => 'green',   'textColor' => 'white'],
        'rencana_akad'    => ['label' => 'Rencana Akad',    'color' => '#17ffff', 'textColor' => '#000'],
        'perintah_bangun' => ['label' => 'Perintah Bangun', 'color' => '#fcba03', 'textColor' => '#000'],
    ];

    public const DIVISI_EVENT_MAP = [
        'keuangan'         => ['pembayaran', 'jatuh_tempo'],
        'legal_pertanahan' => ['akad', 'rencana_akad'],
        'produksi'         => ['perintah_bangun'],
    ];

    protected KalendarRepository $repo;

    public function __construct(?KalendarRepository $repo = null)
    {
        $this->repo = $repo ?? new KalendarRepository();
    }

    public static function divisiKeyFromName(string $name): string
    {
        $key = strtolower($name);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);

        return trim($key, '_');
    }

    public function getDivisiForView(array $groups): array
    {
        $result = [];

        foreach ($groups as $group) {
            $divisiKey = self::divisiKeyFromName($group->divisi);
            $eventKeys = self::DIVISI_EVENT_MAP[$divisiKey] ?? [];

            if ($eventKeys === []) {
                continue;
            }

            $result[] = (object) [
                'id_divisi'  => $group->id_divisi,
                'divisi'     => $group->divisi,
                'divisi_key' => $divisiKey,
                'event_keys' => $eventKeys,
            ];
        }

        return $result;
    }

    public function getEventKeysForDivisiKeys(array $divisiKeys): array
    {
        $keys = [];

        foreach ($divisiKeys as $divisiKey) {
            $mapped = self::DIVISI_EVENT_MAP[$divisiKey] ?? [];
            foreach ($mapped as $eventKey) {
                $keys[$eventKey] = true;
            }
        }

        return array_keys($keys);
    }

    public function getEventsForCalendar(int $idProyek, string $start, string $end, array $types = []): array
    {
        if ($idProyek <= 0) {
            return [];
        }

        $allTypes = array_keys(self::EVENT_META);
        $types    = $types === [] ? $allTypes : array_values(array_intersect($types, $allTypes));

        if ($types === []) {
            return [];
        }

        $events = [];
        $seq    = 0;

        if (in_array('pembayaran', $types, true)) {
            foreach ($this->repo->getPembayaranEvents($idProyek, $start, $end) as $row) {
                $events[] = $this->formatEvent(
                    ++$seq,
                    'pembayaran',
                    $row->tanggal_bayar,
                    $row->keterangan . ' : (Rp. ' . number_format((float) $row->nominal) . ')',
                );
            }
        }

        if (in_array('jatuh_tempo', $types, true)) {
            foreach ($this->repo->getJatuhTempoEvents($idProyek, $start, $end) as $row) {
                $events[] = $this->formatEvent(
                    ++$seq,
                    'jatuh_tempo',
                    $row->jatuh_tempo_tgl,
                    "Jatuh Tempo \n {$row->nama_konsumen} \n {$row->nama_jalan} No. {$row->no_kavling} ({$row->tipe_rumah})",
                );
            }
        }

        if (array_intersect(['akad', 'rencana_akad', 'perintah_bangun'], $types) !== []) {
            foreach ($this->repo->getMkdtEvents($idProyek, $start, $end) as $row) {
                if (in_array('akad', $types, true) && (int) $row->akad === 1 && $this->dateInRange($row->akad_tgl, $start, $end)) {
                    $events[] = $this->formatEvent(
                        ++$seq,
                        'akad',
                        $row->akad_tgl,
                        "Akad \n {$row->nama_konsumen} \n {$row->nama_jalan} No. {$row->no_kavling} ({$row->tipe_rumah})",
                    );
                }

                if (in_array('rencana_akad', $types, true) && ! empty($row->rencana_akad_tgl) && $this->dateInRange($row->rencana_akad_tgl, $start, $end)) {
                    $events[] = $this->formatEvent(
                        ++$seq,
                        'rencana_akad',
                        $row->rencana_akad_tgl,
                        "Rencana Akad \n {$row->nama_konsumen} \n {$row->nama_jalan} No. {$row->no_kavling} ({$row->tipe_rumah})",
                    );
                }

                if (in_array('perintah_bangun', $types, true) && (int) $row->perintah_bangun === 1 && $this->dateInRange($row->perintah_bangun_tgl, $start, $end)) {
                    $events[] = $this->formatEvent(
                        ++$seq,
                        'perintah_bangun',
                        $row->perintah_bangun_tgl,
                        "Perintah Bangun \n {$row->nama_jalan} No. {$row->no_kavling} ({$row->tipe_rumah})",
                    );
                }
            }
        }

        return $events;
    }

    protected function formatEvent(int $id, string $eventKey, string $date, string $title): array
    {
        $meta      = self::EVENT_META[$eventKey];
        $divisiKey = $this->eventKeyToDivisiKey($eventKey);

        return [
            'id'            => $id,
            'url'           => '',
            'color'         => $meta['color'],
            'textColor'     => $meta['textColor'],
            'title'         => $title,
            'start'         => $date,
            'end'           => $date,
            'allDay'        => true,
            'extendedProps' => [
                'calendar'  => $meta['label'],
                'eventKey'  => $eventKey,
                'divisiKey' => $divisiKey,
            ],
        ];
    }

    protected function eventKeyToDivisiKey(string $eventKey): string
    {
        foreach (self::DIVISI_EVENT_MAP as $divisiKey => $eventKeys) {
            if (in_array($eventKey, $eventKeys, true)) {
                return $divisiKey;
            }
        }

        return '';
    }

    protected function dateInRange(?string $date, string $start, string $end): bool
    {
        if (empty($date) || $date === '0000-00-00') {
            return false;
        }

        return $date >= $start && $date <= $end;
    }
}
