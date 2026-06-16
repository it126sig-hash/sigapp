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

    .cashout-subkon-child-wrap {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin: .5rem 0;
        padding: .75rem;
    }

    .cashout-subkon-child-table {
        background: #fff;
        margin-bottom: 0;
    }

    .cashout-subkon-child-table th {
        white-space: nowrap;
    }

    .cashout-subkon-child-empty {
        color: #6b7280;
        font-size: .86rem;
        padding: .35rem;
    }

    .btn-edit-cashout-subkon,
    .btn-delete-cashout-subkon {
        border-radius: 6px;
        font-weight: 800;
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
    let dt_proyek = {
        id_proyek: window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : '',
        nama_proyek: window.SIGAPP && window.SIGAPP.activeProyekName ? window.SIGAPP.activeProyekName : ''
    };
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
                                    <th>No / Tgl SPK</th>
                                    <th>Subkon</th>
                                    <th>Kavling</th>
                                    <th>Total Kontrak</th>
                                    <th>Total Sudah Cair</th>
                                    <th>Tenggat Waktu</th>
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
        function resolveActiveProyekId() {
            if (typeof activeProyekId === 'function') {
                return activeProyekId();
            }

            return window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : null;
        }

        function syncActiveProyekContext() {
            dt_proyek = {
                id_proyek: resolveActiveProyekId() || '',
                nama_proyek: window.SIGAPP && window.SIGAPP.activeProyekName ? window.SIGAPP.activeProyekName : ''
            };
        }

        function cashoutSubkonEscapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : value).html();
        }

        function cashoutSubkonFormatDate(value) {
            if (!value || value === '0000-00-00') {
                return '-';
            }

            if (typeof format_date === 'function') {
                return format_date(value) || '-';
            }

            return cashoutSubkonEscapeHtml(value);
        }

        function cashoutSubkonFormatMoney(value) {
            const amount = parseFloat(value || 0);
            const safeAmount = Number.isFinite(amount) ? amount : 0;

            if (typeof num_format === 'function') {
                return 'Rp ' + num_format(safeAmount);
            }

            return 'Rp ' + safeAmount.toLocaleString('id-ID');
        }

        function cashoutSubkonStatusBadge(status, isPaid) {
            if (parseInt(status || 0) === 4 || parseInt(isPaid || 0) === 1) {
                return '<span class="badge badge-success">Dibayar Oleh Keuangan</span>';
            }

            const labels = {
                0: ['badge-primary', 'Terbit SPK'],
                1: ['badge-secondary', 'Turun Jatuh Tempo'],
                2: ['badge-info', 'Pengajuan SPP'],
                3: ['badge-warning', 'Pengajuan Pencairan']
            };
            const item = labels[parseInt(status || 0)] || ['badge-light', '-'];

            return '<span class="badge ' + item[0] + '">' + item[1] + '</span>';
        }

        function cashoutSubkonReadTermin($button) {
            const data = $button.data('termin');
            if (Array.isArray(data)) {
                return data;
            }

            if (typeof data === 'string' && data.length > 0) {
                try {
                    return JSON.parse(data);
                } catch (e) {
                    return [];
                }
            }

            return [];
        }

        function cashoutSubkonFormatTerminChild(data) {
            if (!Array.isArray(data) || data.length === 0) {
                return '<div class="cashout-subkon-child-wrap"><div class="cashout-subkon-child-empty">Tidak ada termin pembayaran.</div></div>';
            }

            const rows = data.map(function(item, index) {
                const spp = item.spp_no
                    ? cashoutSubkonEscapeHtml(item.spp_no) + '<br><small class="text-muted">' + cashoutSubkonFormatDate(item.spp_tgl) + '</small>'
                    : '-';
                const pembayaran = item.cek_no
                    ? cashoutSubkonEscapeHtml(item.cek_no) + '<br><small class="text-muted">' + cashoutSubkonFormatDate(item.cek_tgl) + '</small>'
                    : '-';

                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${cashoutSubkonEscapeHtml(item.berita_acara || '-')}</td>
                        <td class="text-right">${cashoutSubkonEscapeHtml(item.persentase || 0)}%</td>
                        <td class="text-right">${cashoutSubkonFormatMoney(item.nominal)}</td>
                        <td>${cashoutSubkonFormatDate(item.tanggal_jatuh_tempo)}</td>
                        <td>${spp}</td>
                        <td>${cashoutSubkonFormatDate(item.pengajuan_cair_tgl)}</td>
                        <td>${pembayaran}</td>
                        <td>${cashoutSubkonStatusBadge(item.status, item.is_paid)}</td>
                    </tr>`;
            }).join('');

            return `
                <div class="cashout-subkon-child-wrap">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered cashout-subkon-child-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Termin / Berita Acara</th>
                                    <th class="text-right">Persentase</th>
                                    <th class="text-right">Nominal</th>
                                    <th>Jatuh Tempo</th>
                                    <th>SPP</th>
                                    <th>Pengajuan Cair</th>
                                    <th>Pembayaran / Cek</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>`;
        }

        syncActiveProyekContext();

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
                    data.id_proyek = resolveActiveProyekId();
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

        $('#btn-filter-cashout-subkon').on('click', function() {
            table.draw();
        });

        $('#btn-add-cashout-subkon').on('click', function() {
            syncActiveProyekContext();
            if (!dt_proyek.id_proyek) {
                return swal('error', 'Pilih proyek terlebih dahulu');
            }

            openCashoutSubkonCreate();
        });

        $(document).on('click', '.btn-edit-cashout-subkon', function() {
            const payload = $(this).data('payload');
            syncActiveProyekContext();
            dt_proyek.id_proyek = payload.id_proyek || dt_proyek.id_proyek || '';
            openCOSubkon(payload);
        });

        $('#cashout-subkon-table tbody').on('click', '.btn-cashout-subkon-detail', function() {
            const button = $(this);
            const tr = button.closest('tr');
            const row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                button.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                return;
            }

            tr.addClass('shown');
            button.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            row.child(cashoutSubkonFormatTerminChild(cashoutSubkonReadTermin(button))).show();
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
