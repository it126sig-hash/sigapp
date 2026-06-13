<!-- BEGIN: Body-->

<?php
$__activeProyekService = new \App\Services\ActiveProyekService();
$__activeProyek = $__activeProyekService->getActive();
$__accessibleProyek = $__activeProyekService->getAccessibleList((int) user_id());
$__needsProjectSelection = $__activeProyekService->needsSelection();
$__defaultProjectLogo = base_url('app-assets/images/ico/apple-icon-120.png');
?>

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static   menu-collapsed" data-open="click" data-menu="vertical-menu-modern" data-col="">
    <script>
        const base_url = "<?= base_url() ?>"
        var csrfName = '<?= csrf_token() ?>',
            csrfHash = '<?= csrf_hash() ?>';
        const file_url = (source, id, download = false) => {
            const suffix = download ? '?download=1' : '';
            return `${base_url}files/${source}/${id}${suffix}`;
        };
        const file_thumbnail_url = (source, id) => `${base_url}files/${source}/${id}/thumbnail`;
        window.SIGAPP = {
            activeProyekId: <?= json_encode($__activeProyek ? (int) $__activeProyek->id_proyek : null) ?>,
            activeProyekName: <?= json_encode($__activeProyek->nama_proyek ?? '') ?>,
            activeProyekLogoUrl: <?= json_encode(
                ($__activeProyek && ! empty($__activeProyek->logo_access_url))
                    ? $__activeProyek->logo_access_url
                    : $__defaultProjectLogo
            ) ?>,
            needsProjectSelection: <?= json_encode($__needsProjectSelection) ?>,
            accessibleProyek: <?= json_encode(array_map(static function ($row) {
                return [
                    'id_proyek'       => (int) $row->id_proyek,
                    'nama_proyek'     => $row->nama_proyek,
                    'logo_access_url' => $row->logo_access_url ?? '',
                ];
            }, $__accessibleProyek)) ?>,
        };
    </script>

    <?= view_cell('\App\Libraries\Menu::get_menu') ?>
