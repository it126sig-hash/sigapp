<?php

namespace App\Controllers;

use App\Services\KalendarService;
use Myth\Auth\Authorization\GroupModel;

class Kalendar extends BaseController
{
    protected GroupModel $authGroup;
    protected KalendarService $kalendarService;

    public function __construct()
    {
        $this->authGroup       = new GroupModel();
        $this->kalendarService = new KalendarService();
    }

    public function index()
    {
        $groups = $this->authGroup
            ->select('id as id_divisi, name as divisi')
            ->findAll();

        $data['content']              = 'misc/kalender';
        $data['data']['title']        = 'Kalender';
        $data['data']['divisi']       = $this->kalendarService->getDivisiForView($groups);
        $data['data']['eventMeta']    = KalendarService::EVENT_META;
        $data['data']['divisiEventMap'] = KalendarService::DIVISI_EVENT_MAP;

        return view('template', $data);
    }

    public function getEvents()
    {
        $startRaw = (string) $this->request->getVar('start');
        $endRaw   = (string) $this->request->getVar('end');
        $start    = explode('T', $startRaw)[0];
        $end      = explode('T', $endRaw)[0];

        $idProyek = resolve_active_proyek_id($this->request->getVar('id_proyek'));

        $types = $this->request->getVar('types');
        if (! is_array($types)) {
            $types = $types ? [(string) $types] : [];
        }
        $types = array_values(array_filter(array_map('strval', $types)));

        if ($types === []) {
            return $this->response->setJSON([]);
        }

        $events = $this->kalendarService->getEventsForCalendar($idProyek, $start, $end, $types);

        return $this->response->setJSON($events);
    }
}
