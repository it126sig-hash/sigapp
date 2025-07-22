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
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Siteplan</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>proyek">Siteplan</a>
                                </li>
                                <li class="breadcrumb-item active">Index
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="content-body">
            <div class="row match-height">
                <!-- Timeline Card -->
                <?php 
                foreach($proyek as $a){?>
                
                <div class="col-lg-4 col-12">
                    <a href="<?=base_url("siteplan/view_siteplan/$a->id_proyek")?>">
                        <div class="card card-user-timeline">
                            <div class="card-header" style="text-align: center;">
                                <div class="d-flex align-items-center" >
                                    
                                </div>
                            </div>
                            <div class="card-body" style="text-align: center; height: 400px;">
                                <h4 class="card-title"><?=$a->nama_proyek?></h4>
                                <img style="width: 100%; max-height: 350px  ; object-fit: contain;" src="<?=base_url("/$a->logo")?>">
                            </div>
                        </div>
                    </a>
                </div>
                <?php }?>
                
                <!--/ Timeline Card -->
            </div>
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