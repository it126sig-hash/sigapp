<?php

use App\Services\ActiveProyekService;

if (!function_exists('active_proyek_id')) {
    function active_proyek_id(): ?int
    {
        return (new ActiveProyekService())->getActiveId();
    }
}

if (!function_exists('resolve_active_proyek_id')) {
    function resolve_active_proyek_id($requestValue = null): ?int
    {
        $id = (int) ($requestValue ?: 0);

        if ($id > 0) {
            return $id;
        }

        return active_proyek_id();
    }
}

if (!function_exists('active_proyek')) {
    function active_proyek(): ?object
    {
        return (new ActiveProyekService())->getActive();
    }
}
