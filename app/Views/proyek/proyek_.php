<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/vendors.min.css">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/tables/datatable/rowGroup.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
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
                            <h2 class="content-header-title float-left mb-0">Proyek</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>proyek">Proyek</a>
                                    </li>
                                    <li class="breadcrumb-item active">Index
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <div class="form-group breadcrumb-right">
                        <div class="dropdown">
                            <button class="btn-icon btn btn-primary btn-round btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i data-feather="grid"></i></button>
                            <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="check-square"></i><span class="align-middle">Todo</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="message-square"></i><span class="align-middle">Chat</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="mail"></i><span class="align-middle">Email</span></a><a class="dropdown-item" href="javascript:void(0);"><i class="mr-1" data-feather="calendar"></i><span class="align-middle">Calendar</span></a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- Kick start -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">List Proyek</h4>
                    </div>
                    <div class="card-body">
                        <div class="card-text">
                        
                        <!-- Basic table -->
                        <section id="ajax-datatable">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header border-bottom">
                                            <button type="button" onclick='openForm()'
                                                class="btn btn-primary data-submit mr-1 col-sm-12 col-md-3 col-lg-3">+Tambah Data</button>
                                            <!-- <button type="button" onclick='reload_table()' class="btn btn-primary">Refresh</button> -->
                                        </div>
                                        <div class="card-datatable">
                                            <table id="data-merk" class="datatable-master table">
                                                <thead>
                                                    <tr>
                                                        <th></th>
                                                        <th>ID</th>
                                                        <th>Nama Proyek</th>
                                                        <th>Alamat Proyek</th>
                                                        <th>Siteplan</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <!--/ Basic table -->

                        </div>
                    </div>
                </div>
                <!--/ Kick start -->
            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade" id="modals-slide-in">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record needs-validation modal-content pt-0" readonly novalidate id="form">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Tambah Data Proyek</h5>

                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group">
                        <label class="form-label" for="nama">Nama Proyek</label>
                        <input type="text" class="form-control form-control-sm dt-full-name hidden" id="id_proyek"
                            name="id_proyek" />
                        <input type="text" class="form-control form-control-sm dt-full-name" id="nama_proyek"
                            name="nama_proyek" placeholder="Alam Sanggar Idah" aria-label="Toyota" required />
                        <div class="valid-feedback">Sudah sesuai!</div>
                        <div class="invalid-feedback">Mohon diisi atau perhatikan format penulisan</div>
                    </div>

                    <div class="form-group">
                        <label for="title" class="form-label">Alamat Proyek</label>
                        <textarea rows="2" class="form-control form-control-sm" id="alamat_proyek" name="alamat_proyek"
                            placeholder="Jl Terusan Gegerkalong No 8" required></textarea>
                        <div class="valid-feedback">Sudah sesuai!</div>
                        <div class="invalid-feedback">Mohon diisi atau perhatikan format penulisan</div>
                    </div>
                    <div class="form-group">
                        <label for="title" class="form-label">Siteplan</label>
                        <input type="text" class="form-control form-control-sm" id="siteplan" name="siteplan"
                            placeholder="Siteplan" />
                        <div class="valid-feedback">Sudah sesuai!</div>
                        <div class="invalid-feedback">Mohon diisi atau perhatikan format penulisan</div>
                    </div>
                    <button type="button" id="simpanbtn" class="btn btn-primary data-submit mr-1"><span
                            class="spinner-border spinner-border-sm hidden" role="status" aria-hidden="true"></span> Simpan
                        Data</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Basic toast -->
    <div class="toast toast-basic hide position-fixed" role="alert" aria-live="assertive" aria-atomic="true"
        data-delay="3000" style="bottom: 1rem; right: 1rem;">
        <div class="toast-header">
            <img src="{{ asset('images/logo/logo.png') }}" class="mr-1" alt="Toast image" height="18" width="25" />
            <strong class="mr-auto">Drent System</strong>
            <small class="text-muted">0 mins ago</small>
            <button type="button" class="ml-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body"></div>
    </div>
    <!-- Basic toast ends -->
    
    <!-- BEGIN: Page Vendor JS-->
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <script src="<?=base_url()?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    <!-- END: Page Vendor JS-->