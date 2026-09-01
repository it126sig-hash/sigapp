<?php

namespace App\Controllers;

use App\Services\GoogleCalendarService;
use RuntimeException;

class GoogleCalendar extends BaseController
{
    protected GoogleCalendarService $googleCalendarService;

    public function __construct()
    {
        $this->googleCalendarService = new GoogleCalendarService();
    }

    public function connect()
    {
        try {
            return redirect()->to($this->googleCalendarService->buildAuthUrl((int) user_id()));
        } catch (RuntimeException $e) {
            return redirect()->to(base_url('profil'))->with('error', $e->getMessage());
        }
    }

    public function callback()
    {
        $error = (string) $this->request->getGet('error');
        if ($error !== '') {
            return redirect()->to(base_url('profil'))->with('error', 'Google Calendar tidak terhubung: ' . $error);
        }

        $state = (string) $this->request->getGet('state');
        $code = (string) $this->request->getGet('code');
        if ($state === '' || $code === '') {
            return redirect()->to(base_url('profil'))->with('error', 'Callback Google Calendar tidak lengkap.');
        }

        try {
            $this->googleCalendarService->connectFromCallback((int) user_id(), $state, $code);
            return redirect()->to(base_url('profil'))->with('message', 'Google Calendar berhasil terhubung.');
        } catch (RuntimeException $e) {
            return redirect()->to(base_url('profil'))->with('error', $e->getMessage());
        }
    }

    public function disconnect()
    {
        $this->googleCalendarService->disconnect((int) user_id());

        return redirect()->to(base_url('profil'))->with('message', 'Google Calendar berhasil diputus.');
    }
}
