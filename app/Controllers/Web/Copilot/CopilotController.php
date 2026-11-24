<?php

namespace App\Controllers\Web\Copilot;

use App\Controllers\BaseController;
use App\Services\Settings\MenuAccessService;

class CopilotController extends BaseController
{
    private const MENU_URL = 'copilot';

    protected string $activeMenu = 'copilot';

    private MenuAccessService $menuAccessService;

    public function __construct(?MenuAccessService $menuAccessService = null)
    {
        if (! function_exists('user_id')) {
            helper('auth');
        }

        $this->menuAccessService = $menuAccessService ?? new MenuAccessService();
    }

    public function index()
    {
        $userId = (int) (user_id() ?? 0);
        if ($userId <= 0 || ! $this->menuAccessService->canAccessUrl($userId, self::MENU_URL)) {
            return $this->response->setStatusCode(403)->setBody(view('errors/html/unauthorized'));
        }

        return view('template', [
            'content' => 'copilot/index',
            'data'    => [
                'title'      => 'AI Copilot',
                'activeMenu' => $this->activeMenu,
            ],
        ]);
    }
}
