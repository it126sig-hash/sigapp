<?php

namespace App\Libraries;

use \App\Models\MenuModel;

class Menu
{
    public function get_menu()
    {
        $db = db_connect();
        $get_menu = new MenuModel();
        $fullmenu = '<ul class="sidebar-menu" id="main-menu-navigation">';

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
        $sm = $get_menu
            ->where('parent_id', $menu->id)
            ->where("is_active", 1)
            ->findAll();

        $has_submenu = !empty($sm);
        $li_class = $has_submenu ? 'menu-item-parent has-submenu' : 'menu-item-parent';

        $html = "
        <li class='{$li_class}'>
            <a class='d-flex align-items-center' href='" . base_url() . $menu->url . "'>
                <i data-feather='" . $menu->icon . "' class='menu-icon'></i>
                <span class='menu-title'>" . $menu->name . "</span>";
        if ($has_submenu) {
            $html .= "<i class='fa-solid fa-chevron-right submenu-arrow ml-auto'></i>";
        }
        $html .= "</a>";

        if ($has_submenu) {
            $html .= '<ul class="menu-content" style="display: none;">';
            foreach ($sm as $sm_item) {
                if (in_array($sm_item->id, $mr)) {
                    $html .= ' 
                    <li class="menu-item-sub">
                        <a class="d-flex align-items-center" href="' . base_url() . $sm_item->url . '">
                            <span class="menu-dot"></span>
                            <span class="menu-title-sub">' . $sm_item->name . '</span>
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
