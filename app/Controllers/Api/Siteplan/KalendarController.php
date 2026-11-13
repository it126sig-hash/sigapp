<?php

namespace App\Controllers\Api\Siteplan;

use App\Controllers\Api\BaseApiController;
use App\Services\Siteplan\KalendarService;
use CodeIgniter\HTTP\ResponseInterface;

class KalendarController extends BaseApiController
{
    protected KalendarService $kalendarService;

    public function __construct(?KalendarService $kalendarService = null)
    {
        helper(['auth', 'active_proyek']);
        $this->kalendarService = $kalendarService ?? new KalendarService();
    }

    public function meta(): ResponseInterface
    {
        $userId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;

        return $this->success(
            $this->kalendarService->getCalendarViewData($userId),
            'Metadata kalender berhasil dimuat'
        );
    }

    public function getEvents(): ResponseInterface
    {
        $input         = $this->getRequestData();
        $wantsEnvelope = $this->wantsEnvelope();

        $startRaw = (string) ($input['start'] ?? '');
        $endRaw   = (string) ($input['end'] ?? '');
        $start    = explode('T', $startRaw)[0];
        $end      = explode('T', $endRaw)[0];

        $requestedProyek = $input['id_proyek']
            ?? $this->request->header('X-Project-Id')?->getValue();
        $idProyek = (int) (resolve_active_proyek_id($requestedProyek) ?? 0);

        $hasTypesParam = array_key_exists('types', $input);
        $rawTypes      = $input['types'] ?? null;
        $types         = $this->normalizeTypes($rawTypes);

        if (! $wantsEnvelope) {
            if ($types === []) {
                return $this->response->setJSON([]);
            }

            $events = $this->kalendarService->getEventsForCalendar($idProyek, $start, $end, $types);

            return $this->response->setJSON($events);
        }

        if ($idProyek <= 0) {
            return $this->error('ID Proyek tidak valid', 400);
        }

        if ($start === '') {
            $start = date('Y-m-01');
        }
        if ($end === '') {
            $end = date('Y-m-t');
        }

        if ($hasTypesParam && $types === []) {
            return $this->success([], 'Data event kalender berhasil dimuat');
        }

        $events = $this->kalendarService->getEventsForCalendar($idProyek, $start, $end, $types);

        return $this->success($events, 'Data event kalender berhasil dimuat');
    }

    private function normalizeTypes(mixed $rawTypes): array
    {
        if (is_array($rawTypes)) {
            return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $rawTypes)));
        }

        if (is_string($rawTypes) && trim($rawTypes) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $rawTypes))));
        }

        return [];
    }

    private function wantsEnvelope(): bool
    {
        $uriPath = ltrim($this->request->getUri()->getPath(), '/');
        if (str_starts_with($uriPath, 'index.php/')) {
            $uriPath = substr($uriPath, strlen('index.php/'));
        }

        if (str_starts_with($uriPath, 'api/') || str_contains($uriPath, '/api/')) {
            return true;
        }

        $format = strtolower((string) ($this->request->getGet('format') ?? ($this->getRequestData()['format'] ?? '')));
        if ($format === 'raw') {
            return true;
        }

        return trim((string) $this->request->getHeaderLine('X-Client-Platform')) !== '';
    }

    private function getRequestData(): array
    {
        $query = $this->request->getGet();
        $query = is_array($query) ? $query : [];

        $post = $this->request->getPost();
        $post = is_array($post) ? $post : [];

        $json = [];
        if (str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'application/json')) {
            try {
                $decoded = $this->request->getJSON(true);
                if (is_array($decoded)) {
                    $json = $decoded;
                }
            } catch (\Throwable $e) {
                $json = [];
            }
        }

        return array_merge($query, $post, $json);
    }
}
