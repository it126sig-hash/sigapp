<?php

namespace App\Libraries;

use \App\Models\MenuModel;

class Menu
{
    public function get_menu()
    {
        $db = db_connect();
        $get_menu = new MenuModel();
        $fullmenu = '<ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">';

        $mr = $db->table("menu_roles")
            ->select("id_menu")
            ->join('auth_groups_users', "auth_groups_users.group_id = menu_roles.id_groups")
            ->join('users', "users.id = auth_groups_users.user_id")
            ->where("users.id", user_id())
            ->get()
            ->getResult()[0]
            ->id_menu;
        $mr = explode(",", $mr);

        $m = $get_menu
            ->where("parent_id", 0)
            ->where("is_active", 1)
            ->findAll();
        foreach ($m as $m) {
            if (in_array($m->id, $mr)) {
                $fullmenu .= $this->generateMenuItem($m, $mr, $get_menu);
            }
        }

        $fullmenu .= "</ul>";

        $d['menu'] = $fullmenu;

        $d['notif'] = $this->getNotif();

        return view('template/generate_menu', $d);
    }

    private function generateMenuItem($menu, $mr, $get_menu)
    {
        $html = "
        <li class='nav-item'>
            <a class='d-flex align-items-center' href='" . base_url() . $menu->url . "'>
                <i data-feather='" . $menu->icon . "'></i>
                <span class='menu-title text-truncate' data-i18n='" . $menu->name . "'>" . $menu->name . "</span>
            </a>";
        $sm = $get_menu
            ->where('parent_id', $menu->id)
            ->where("is_active", 1)
            ->findAll();
        if ($sm) {
            $html .= '<ul class="menu-content">';
            foreach ($sm as $sm) {
                if (in_array($sm->id, $mr)) {
                    $html .= ' 
                    <li>
                        <a class="d-flex align-items-center" href="' . base_url() . $sm->url . '">
                            <i data-feather="circle"></i>
                            <span class="menu-item text-truncate" data-i18n="Collapsed Menu">' . $sm->name . '</span>
                        </a>
                    </li>';
                }
            }
            $html .= "</ul>";
        }
        $html .= "</li>";
        return $html;
    }

    function getNotif()
    {
        return  $notif = '';
    }
}
