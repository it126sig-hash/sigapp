<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/vendors.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<style>
    .canvas {
        border: 1px solid black;
        background-color: #eee;
    }

    .float {
        position: fixed;
        width: 200;
        height: 70px;
        bottom: 40px;
        left: 100px;
        background-color: #fff;
        border: 1px solid;
        /* color:#FFF; */
        border-radius: 5px;
        text-align: center;
        box-shadow: 2px 2px 3px #999;
        z-index: 9999;
        padding: 0 10px 10px 10px;
    }

    .my-float {
        margin-top: 22px;
    }
</style>
<!-- END: Vendor CSS-->

<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">
            <?php if (empty($data['proyek'])) : ?>
            <div class="row match-height">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-3">
                            <h4 class="mb-1">Belum ada proyek yang dapat diakses</h4>
                            <p class="text-muted mb-0">Hubungi administrator untuk mendapatkan akses proyek.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <div class="row match-height">
                <div class="col-12 mb-1">
                    <h4 class="mb-25">Pilih Proyek</h4>
                    <p class="text-muted mb-0">Pilih proyek yang ingin Anda kerjakan.</p>
                </div>
                <?php
                foreach ($data['proyek'] as $a) { ?>

                <div class="col-lg-4 col-md-6 col-12">
                    <a href="<?= base_url('siteplan/view_siteplan/' . $a->id_proyek) ?>" class="text-body">
                        <div class="card project-select-card-page h-100">
                            <div class="card-body d-flex flex-column align-items-center text-center py-2">
                                <img
                                    class="project-select-card-logo mb-1"
                                    src="<?= esc($a->logo_access_url ?? site_url('files/proyek_logo/' . $a->id_proyek)) ?>"
                                    alt="">
                                <h5 class="card-title mb-0"><?= esc($a->nama_proyek) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php } ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- END: Content-->




<!-- BEGIN: Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<!-- BEGIN Vendor JS-->
<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/assets/js/magic-wand.min.js"></script>
<script src="<?= base_url() ?>/assets/js/konva.min.js"></script>
<!-- END: Page Vendor JS-->
