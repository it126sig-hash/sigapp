<?php
$roleId = '';
$roleName = '';
foreach (user()->getRoles() as $key => $val) {
    $roleId = $key;
    $roleName = $val;
    break;
}
?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">

<style>
    #cashout-subkon-table td {
        vertical-align: top;
    }

    .cashout-subkon-filter {
        gap: 1rem;
    }
</style>

<script>
    const state = {
        status: {
            tab: {
                isClosed: false
            }
        },
        id_cashout_subkon: null
    };
    const roleid = <?= (int) $roleId ?>;
    const rolename = '<?= esc($roleName) ?>';
    const not_found = 'images/not_found.png';
    let dt_proyek = {};
    window.editdtt = [];
</script>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <section id="cashout-subkon-list">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex flex-wrap align-items-end cashout-subkon-filter">
                        <div>
                            <h4 class="card-title mb-0"><?= esc($title ?? 'Cashout Subkon') ?></h4>
                            <small class="text-muted">List SPK dan termin cashout subkon</small>
                        </div>
                        <div class="ml-auto d-flex flex-wrap align-items-end cashout-subkon-filter">
                            <div style="min-width: 260px;">
                                <label>Proyek</label>
                                <select id="filter-id-proyek" class="form-control select2"></select>
                            </div>
                            <div style="min-width: 180px;">
                                <label>Status</label>
                                <select id="filter-status" class="form-control select2">
                                    <option value="">Semua Status</option>
                                    <option value="0">Berjalan</option>
                                    <option value="1">Selesai / Dibayar</option>
                                </select>
                            </div>
                            <button type="button" id="btn-filter-cashout-subkon" class="btn btn-outline-primary">
                                Filter
                            </button>
                            <button type="button" id="btn-add-cashout-subkon" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Tambah Cashout Subkon
                            </button>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="cashout-subkon-table" class="datatables-basic table table-hover">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>No</th>
                                    <th>No SPK</th>
                                    <th>Tgl SPK</th>
                                    <th>Subkon</th>
                                    <th>Proyek</th>
                                    <th>Kavling</th>
                                    <th>Total Kontrak</th>
                                    <th>Tenggat Waktu</th>
                                    <th>Waktu Cair</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>

<?= view('siteplan/cashout_subkon') ?>

<script>
    $(function() {
        const table = $('#cashout-subkon-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            lengthChange: true,
            searching: true,
            ordering: false,
            paging: true,
            ajax: {
                url: base_url + 'cashout/subkon/list',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data[csrfName] = csrfHash;
                    data.id_proyek = $('#filter-id-proyek').val();
                    data.status = $('#filter-status').val();
                },
                dataSrc: function(r) {
                    csrfHash = r.token;
                    return r.data;
                }
            }
        });

        $('.dataTables_filter input')
            .off()
            .on('change', function() {
                table.search(this.value).draw();
            });

        $('#filter-status').select2({
            minimumResultsForSearch: Infinity
        });

        $('#filter-id-proyek').select2({
            placeholder: 'Pilih Proyek',
            allowClear: true,
            ajax: {
                url: base_url + 'proyek/get/all',
                dataType: 'json',
                delay: 250,
                method: 'post',
                data: function(params) {
                    return {
                        [csrfName]: csrfHash,
                        search: params.term
                    };
                },
                processResults: function(r) {
                    csrfHash = r.token;
                    return {
                        results: (r.data || []).map(function(item) {
                            return {
                                id: item.id_proyek,
                                text: item[1] + ' (' + item[2] + ')',
                                nama_proyek: item[1]
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('change', function() {
            const selected = $(this).select2('data')[0] || {};
            dt_proyek = {
                id_proyek: selected.id || '',
                nama_proyek: selected.nama_proyek || selected.text || ''
            };
        });

        $('#btn-filter-cashout-subkon').on('click', function() {
            table.draw();
        });

        $('#btn-add-cashout-subkon').on('click', function() {
            if (!$('#filter-id-proyek').val()) {
                return swal('error', 'Pilih proyek terlebih dahulu');
            }

            openCashoutSubkonCreate();
        });

        $(document).on('click', '.btn-edit-cashout-subkon', function() {
            const payload = $(this).data('payload');
            dt_proyek.id_proyek = payload.id_proyek || $('#filter-id-proyek').val() || '';
            openCOSubkon(payload);
        });

        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (!settings.url.includes('cashout/subkon/')) {
                return;
            }

            if (
                settings.url.includes('/list') ||
                settings.url.includes('/ambil') ||
                settings.url.includes('/history')
            ) {
                return;
            }

            table.ajax.reload(null, false);
        });
    });
</script>
