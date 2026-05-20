<!-- BEGIN: Body-->

<body class="minimal-industrial-layout">
    <!-- Page Loader (instan muncul sebelum seluruh aset dimuat) -->
    <div id="loading" role="status"></div>

    <script>
        const base_url = "<?= base_url() ?>"
        var csrfName = '<?= csrf_token() ?>',
            csrfHash = '<?= csrf_hash() ?>';
        
        // Prevent layout flicker by checking sidebar collapse status early
        if (localStorage.getItem("sidebar-collapsed") === "true" && window.innerWidth > 991.98) {
            document.body.classList.add("sidebar-collapsed");
        }
    </script>

    <?= view_cell('\App\Libraries\Menu::get_menu') ?>