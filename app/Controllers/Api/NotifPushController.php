<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\WebPushService;

class NotifPushController extends BaseController
{
    protected $webPushService;

    public function __construct()
    {
        $this->webPushService = new WebPushService();
    }

    public function subscribe()
    {
        if (!function_exists('user_id') || !user_id()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['endpoint'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid subscription data']);
        }

        $userAgent = $this->request->getUserAgent()->getAgentString();
        $this->webPushService->subscribe(user_id(), $json, $userAgent);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function unsubscribe()
    {
        if (!function_exists('user_id') || !user_id()) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $json = $this->request->getJSON(true);
        if (!$json || !isset($json['endpoint'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid endpoint data']);
        }

        $this->webPushService->unsubscribe(user_id(), $json['endpoint']);

        return $this->response->setJSON(['status' => 'success']);
    }
}
