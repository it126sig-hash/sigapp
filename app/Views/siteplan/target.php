<div class="modal fade" id="modal-target-siteplan" tabindex="-1" role="dialog" aria-labelledby="modal-target-siteplan" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Target Siteplan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <form id="fm-target-siteplan">
                            <input type="hidden" id="target-id_target" name="id_target">
                            <input type="hidden" id="target-id_proyek" name="id_proyek">
                            <input type="hidden" id="target-id_kavling" name="id_kavling">
                            <div class="form-group">
                                <label>Proyek</label>
                                <input type="text" class="form-control" id="target-nama_proyek" readonly>
                            </div>
                            <div class="form-group">
                                <label>Tahun Target</label>
                                <input type="number" min="2000" max="2100" class="form-control" id="target-tahun_target" name="tahun_target">
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea class="form-control" id="target-deskripsi" name="deskripsi" rows="4"></textarea>
                            </div>
                            <div class="alert alert-info py-1" id="target-selected-count">0 kavling dipilih</div>
                            <button type="button" class="btn btn-primary btn-block" id="target-save-btn" onclick="save_target_siteplan()">Simpan Target</button>
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="reset_target_form()">Target Baru</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <h6>Daftar Target</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th>Kavling</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="target-list-here"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Histori Perubahan</h6>
                        <div id="target-history-here" class="small text-muted">Pilih target untuk melihat histori.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function target_escape_html(value) {
        return String(value || '').replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function get_selected_target_kavling_ids() {
        let ids = [];
        for (let i = 0; i < editdtt.length; i++) {
            if (editdtt[i].data && editdtt[i].data.tipe == 'kavling') {
                ids.push(editdtt[i].id.replace('kav', ''));
            }
        }
        return ids;
    }

    function target_selection_label(ids, fallbackCount) {
        if (ids.length > 0) return ids.length + ' kavling dari seleksi kanvas';
        if (fallbackCount > 0) return fallbackCount + ' kavling dari target tersimpan';
        return '0 kavling dipilih';
    }

    function reset_target_form() {
        const ids = get_selected_target_kavling_ids();
        const year = new Date().getFullYear();
        $("#fm-target-siteplan")[0].reset();
        $("#target-id_target").val('');
        $("#target-id_proyek").val(dt_proyek.id_proyek);
        $("#target-nama_proyek").val(dt_proyek.nama_proyek);
        $("#target-tahun_target").val(year);
        $("#target-id_kavling").val(ids.join(';'));
        $("#target-selected-count").text(target_selection_label(ids, 0));
        $("#target-history-here").html('<span class="text-muted">Pilih target untuk melihat histori.</span>');
    }

    function open_target_siteplan() {
        reset_target_form();
        load_target_siteplan_list();
        $('#modal-target-siteplan').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function open_target_history() {
        open_target_siteplan();
    }

    function load_target_siteplan_list() {
        $.ajax({
            url: base_url + 'api/target/list',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: 'json',
            success: function(res) {
                csrfHash = res.token;
                const rows = res.data || [];
                let html = '';
                if (rows.length == 0) {
                    html = '<tr><td colspan="3" class="text-muted">Belum ada target.</td></tr>';
                }
                rows.forEach(function(row) {
                    html += `
                        <tr>
                            <td>
                                <strong>${target_escape_html(row.tahun_target)}</strong><br>
                                <small>${target_escape_html(row.deskripsi)}</small>
                            </td>
                            <td>${target_escape_html(row.jumlah_kavling)}</td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="edit_target_siteplan(${row.id_target})">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="show_target_history(${row.id_target})">
                                        <i class="fa fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
                $("#target-list-here").html(html);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat target'
                });
            }
        });
    }

    function edit_target_siteplan(idTarget) {
        $.ajax({
            url: base_url + 'api/target/detail',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_target: idTarget
            },
            dataType: 'json',
            success: function(res) {
                csrfHash = res.token;
                if (!res.success) {
                    return Swal.fire({
                        icon: 'error',
                        title: res.messages
                    });
                }

                const data = res.data;
                const target = data.target;
                const savedIds = (data.kavlings || []).map(function(row) {
                    return row.id_kavling;
                });
                const selectedIds = get_selected_target_kavling_ids();

                $("#target-id_target").val(target.id_target);
                $("#target-id_proyek").val(target.id_proyek);
                $("#target-nama_proyek").val(dt_proyek.nama_proyek);
                $("#target-tahun_target").val(target.tahun_target);
                $("#target-deskripsi").val(target.deskripsi);
                $("#target-id_kavling").val((selectedIds.length > 0 ? selectedIds : savedIds).join(';'));
                $("#target-selected-count").text(target_selection_label(selectedIds, savedIds.length));
                render_target_history(data.history || []);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat detail target'
                });
            }
        });
    }

    function save_target_siteplan() {
        const selectedIds = get_selected_target_kavling_ids();
        if (selectedIds.length > 0) {
            $("#target-id_kavling").val(selectedIds.join(';'));
        }

        if (!$("#target-id_kavling").val()) {
            return Swal.fire({
                icon: 'error',
                title: 'Pilih minimal 1 kavling'
            });
        }

        $.ajax({
            url: base_url + 'api/target/save',
            type: 'post',
            data: $("#fm-target-siteplan").serialize() + '&' + csrfName + '=' + csrfHash,
            dataType: 'json',
            beforeSend: function() {
                $("#target-save-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>').addClass('disabled');
            },
            success: function(res) {
                csrfHash = res.token;
                if (res.success) {
                    $("#target-id_target").val(res.data.target.id_target);
                    render_target_history(res.data.history || []);
                    load_target_siteplan_list();
                    load_kavling();
                    hapus_seleksi();
                    Swal.fire({
                        icon: 'success',
                        title: res.messages,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: res.messages
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan target'
                });
            },
            complete: function() {
                $("#target-save-btn").html('Simpan Target').removeClass('disabled');
            }
        });
    }

    function show_target_history(idTarget) {
        $.ajax({
            url: base_url + 'api/target/history',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_target: idTarget
            },
            dataType: 'json',
            success: function(res) {
                csrfHash = res.token;
                render_target_history(res.data || []);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat histori'
                });
            }
        });
    }

    function render_target_history(rows) {
        if (!rows || rows.length == 0) {
            $("#target-history-here").html('<span class="text-muted">Belum ada histori.</span>');
            return;
        }

        let html = '';
        rows.forEach(function(row) {
            let count = '-';
            try {
                const snapshot = JSON.parse(row.snapshot || '{}');
                count = snapshot.after && snapshot.after.kavlings ? snapshot.after.kavlings.length : '-';
            } catch (e) {}

            html += `
                <div class="border-bottom pb-1 mb-1">
                    <div><strong>${target_escape_html(row.aksi)}</strong> - ${target_escape_html(row.deskripsi)}</div>
                    <div>${target_escape_html(row.add_by_username || '-')} / ${format_datetime(row.created_at)}</div>
                    <div>${count} kavling</div>
                </div>`;
        });
        $("#target-history-here").html(html);
    }
</script>
