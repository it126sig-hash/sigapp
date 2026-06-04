<!-- BEGIN: Body-->

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
    </script>

    <?= view_cell('\App\Libraries\Menu::get_menu') ?>
