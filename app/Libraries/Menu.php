<?php

namespace App\Libraries;

use App\Services\ActiveProyekService;
use App\Services\MenuAccessService;

class Menu
{
    public function get_menu()
    {
        $menuAccess = new MenuAccessService();
        $tree = $menuAccess->getMenuTreeForUser((int) user_id());
        $fullmenu = '<ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">';

        foreach ($tree as $menu) {
            $fullmenu .= $this->generateMenuItem($menu);
        }

        $fullmenu .= "</ul>";

        $activeProyekService = new ActiveProyekService();
        $userId = (int) user_id();

        $d['menu'] = $fullmenu;
        $d['notif'] = $this->getNotif();
        $d['activeProyek'] = $activeProyekService->getActive();
        $d['accessibleProyek'] = $activeProyekService->getAccessibleList($userId);
        $d['needsProjectSelection'] = $activeProyekService->needsSelection();

        return view('template/generate_menu', $d);
    }

    private function generateMenuItem($menu)
    {
        $name = esc($menu->name ?? '');
        $icon = esc($menu->icon ?: 'circle');
        $href = $this->buildHref($menu->url ?? '#');
        $hasChildren = !empty($menu->children);

        $html = "
        <li class='nav-item'>
            <a class='d-flex align-items-center' href='" . $href . "'>
                <i data-feather='" . $icon . "'></i>
                <span class='menu-title text-truncate' data-i18n='" . $name . "'>" . $name . "</span>
            </a>";

        if ($hasChildren) {
            $html .= '<ul class="menu-content">';
            foreach ($menu->children as $child) {
                $html .= $this->generateSubMenuItem($child);
            }
            $html .= "</ul>";
        }

        $html .= "</li>";
        return $html;
    }

    private function generateSubMenuItem($menu)
    {
        $name = esc($menu->name ?? '');
        $href = $this->buildHref($menu->url ?? '#');
        $hasChildren = !empty($menu->children);

        $html = '
            <li>
                <a class="d-flex align-items-center" href="' . $href . '">
                    <i data-feather="circle"></i>
                    <span class="menu-item text-truncate" data-i18n="' . $name . '">' . $name . '</span>
                </a>';

        if ($hasChildren) {
            $html .= '<ul class="menu-content">';
            foreach ($menu->children as $child) {
                $html .= $this->generateSubMenuItem($child);
            }
            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }

    private function buildHref(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return 'javascript:void(0);';
        }

        if (preg_match('/^https?:\/\//', $url)) {
            return esc($url);
        }

        return base_url(ltrim($url, '/'));
    }

    function getNotif()
    {
        return  $notif = '';
    }
}
