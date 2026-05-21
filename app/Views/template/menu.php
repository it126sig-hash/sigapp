<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static   menu-collapsed" data-open="click" data-menu="vertical-menu-modern" data-col="">
    <script>
        const base_url = "<?= base_url() ?>"
        var csrfName = '<?= csrf_token() ?>',
            csrfHash = '<?= csrf_hash() ?>';
    </script>

    <?= view_cell('\App\Libraries\Menu::get_menu') ?>