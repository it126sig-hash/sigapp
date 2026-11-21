<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">

<script>
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
</script>

<style>
    .table-akses-fee th, .table-akses-fee td {
        vertical-align: middle !important;
    }
    .custom-switch .custom-control-label::before {
        cursor: pointer;
    }
    .custom-switch.disabled .custom-control-label::before,
    .custom-control-input:disabled ~ .custom-control-label::before {
        cursor: not-allowed;
        opacity: 0.7;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Pengaturan Hak Akses Fee Sales</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="#">Data Master</a></li>
                                <li class="breadcrumb-item active">Setting Akses Fee Sales</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header border-bottom">
                    <div>
                        <h4 class="card-title mb-25"><i class="fas fa-user-shield text-primary mr-50"></i> Matriks Hak Akses Peran (Role Permissions)</h4>
                        <p class="text-muted font-small-3 mb-0">Atur kewenangan setiap peran pengguna dalam melihat, mengisi/mengelola komisi, mengajukan pencairan, serta mencairkan fee sales.</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-refresh-matrix">
                            <i class="fas fa-sync-alt mr-25"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <div class="alert alert-primary p-1 mb-2 font-small-3">
                        <div class="d-flex align-items-center mb-50">
                            <i class="fas fa-info-circle mr-50 fa-lg"></i>
                            <span class="font-weight-bold">Panduan Kewenangan Fitur:</span>
                        </div>
                        <ul class="mb-0 pl-2 font-small-2">
                            <li><strong>Akses (Lihat Data):</strong> Mengizinkan pengguna dengan peran terkait membuka menu dan melihat halaman daftar fee sales kavling.</li>
                            <li><strong>Isi / Kelola Fee:</strong> Mengizinkan membuka modal kelola fee kavling, menambah sales, mengatur persentase komisi %, total fee override, dan menyimpan form fee.</li>
                            <li><strong>Buat Pengajuan:</strong> Mengizinkan memilih unit kavling dan mengirim pengajuan pencairan fee sales (satuan maupun kelompok batch).</li>
                            <li><strong>Cairkan Dana:</strong> Mengizinkan mengonfirmasi realisasi pencairan termin dana fee sales (divisi keuangan).</li>
                        </ul>
                    </div>

                    <form id="form-setting-akses">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered table-akses-fee" id="table-matrix-akses">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">No</th>
                                        <th style="min-width: 140px;">Peran / Departemen</th>
                                        <th>Deskripsi Peran</th>
                                        <th class="text-center" style="width: 150px;">Akses (Lihat Data)</th>
                                        <th class="text-center" style="width: 150px;">Isi / Kelola Fee</th>
                                        <th class="text-center" style="width: 160px;">Buat Pengajuan</th>
                                        <th class="text-center" style="width: 150px;">Cairkan Dana</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-matrix-akses">
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-muted">
                                            <span class="spinner-border spinner-border-sm mr-50"></span> Memuat matriks hak akses...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                            <small class="text-muted font-small-2">
                                <i class="fas fa-lock mr-25"></i> Peran <strong>Admin</strong> selalu memiliki seluruh hak akses secara permanen demi keamanan sistem.
                            </small>
                            <button type="button" class="btn btn-primary font-weight-bold" id="btn-save-matrix">
                                <i class="fas fa-save mr-50"></i> Simpan Pengaturan Hak Akses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script>
$(function () {
    const listUrl = "<?= base_url('api/setting-fee-sales/list') ?>";
    const saveUrl = "<?= base_url('api/setting-fee-sales/save') ?>";

    function loadMatrix() {
        $("#tbody-matrix-akses").html('<tr><td colspan="7" class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat matriks hak akses...</td></tr>');

        $.ajax({
            url: listUrl,
            type: "POST",
            data: { [csrfName]: csrfHash },
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                if (!res.success || !res.data) {
                    $("#tbody-matrix-akses").html('<tr><td colspan="7" class="text-center py-2 text-danger">Gagal memuat data hak akses.</td></tr>');
                    return;
                }

                let html = "";
                res.data.forEach(function (row, idx) {
                    let isAdmin = (parseInt(row.id_group) === 1 || String(row.group_name).toLowerCase() === "admin");
                    let disabledAttr = isAdmin ? "disabled" : "";

                    let chkView = (parseInt(row.can_view) === 1 || isAdmin) ? "checked" : "";
                    let chkManage = (parseInt(row.can_manage) === 1 || isAdmin) ? "checked" : "";
                    let chkSubmit = (parseInt(row.can_submit_spp) === 1 || isAdmin) ? "checked" : "";
                    let chkCair = (parseInt(row.can_cairkan) === 1 || isAdmin) ? "checked" : "";

                    let adminBadge = isAdmin ? '<span class="badge badge-light-primary font-small-1 ml-50">Full Akses</span>' : '';

                    html += `
                        <tr data-group-id="${row.id_group}">
                            <td class="text-center font-weight-bold">${idx + 1}</td>
                            <td>
                                <span class="font-weight-bold text-dark">${row.group_name || "-"}</span>
                                ${adminBadge}
                            </td>
                            <td><small class="text-secondary">${row.group_description || "-"}</small></td>
                            <td class="text-center">
                                <div class="custom-control custom-switch custom-switch-primary d-inline-block">
                                    <input type="checkbox" class="custom-control-input perm-switch switch-view" id="switch-view-${row.id_group}" ${chkView} ${disabledAttr}>
                                    <label class="custom-control-label" for="switch-view-${row.id_group}"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch custom-switch-primary d-inline-block">
                                    <input type="checkbox" class="custom-control-input perm-switch switch-manage" id="switch-manage-${row.id_group}" ${chkManage} ${disabledAttr}>
                                    <label class="custom-control-label" for="switch-manage-${row.id_group}"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch custom-switch-primary d-inline-block">
                                    <input type="checkbox" class="custom-control-input perm-switch switch-submit" id="switch-submit-${row.id_group}" ${chkSubmit} ${disabledAttr}>
                                    <label class="custom-control-label" for="switch-submit-${row.id_group}"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="custom-control custom-switch custom-switch-primary d-inline-block">
                                    <input type="checkbox" class="custom-control-input perm-switch switch-cair" id="switch-cair-${row.id_group}" ${chkCair} ${disabledAttr}>
                                    <label class="custom-control-label" for="switch-cair-${row.id_group}"></label>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                $("#tbody-matrix-akses").html(html);
            },
            error: function () {
                $("#tbody-matrix-akses").html('<tr><td colspan="7" class="text-center py-2 text-danger">Gagal menghubungi server untuk memuat hak akses.</td></tr>');
            }
        });
    }

    loadMatrix();
    $("#btn-refresh-matrix").on("click", loadMatrix);

    $("#btn-save-matrix").on("click", function (e) {
        e.preventDefault();

        let permissions = [];
        $("#tbody-matrix-akses tr[data-group-id]").each(function () {
            let $tr = $(this);
            let gid = parseInt($tr.attr("data-group-id"));
            if (gid > 0) {
                let isAdmin = (gid === 1);
                permissions.push({
                    id_group: gid,
                    can_view: isAdmin ? 1 : ($tr.find(".switch-view").is(":checked") ? 1 : 0),
                    can_manage: isAdmin ? 1 : ($tr.find(".switch-manage").is(":checked") ? 1 : 0),
                    can_submit_spp: isAdmin ? 1 : ($tr.find(".switch-submit").is(":checked") ? 1 : 0),
                    can_cairkan: isAdmin ? 1 : ($tr.find(".switch-cair").is(":checked") ? 1 : 0),
                });
            }
        });

        let $btn = $("#btn-save-matrix");
        $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: saveUrl,
            type: "POST",
            data: {
                [csrfName]: csrfHash,
                permissions: permissions
            },
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $btn.prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Pengaturan Hak Akses');

                if (res.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil Disimpan!",
                        text: res.message || "Pengaturan hak akses modul Fee Sales berhasil diperbarui.",
                        timer: 1800,
                        showConfirmButton: false
                    });
                    loadMatrix();
                } else {
                    Swal.fire("Gagal", res.message || "Terjadi kesalahan saat menyimpan hak akses.", "error");
                }
            },
            error: function (xhr) {
                $btn.prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Pengaturan Hak Akses');
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Terjadi kesalahan pada server.";
                Swal.fire("Error", msg, "error");
            }
        });
    });
});
</script>
