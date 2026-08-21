$(document).ready(function() {
    let activeProyekId = window.SIGAPP?.activeProyekId || 1; // Sesuaikan dengan cara project ambil active proyek

    let dtMgm = $('.datatables-mgm').DataTable({
        ajax: {
            url: base_url + "api/mgm/list",
            type: "POST",
            data: function (d) {
                d.id_proyek = activeProyekId;
                // d.id_cluster = $("#filter_cluster").val(); // jika ada filter
            }
        },
        columns: [
            {
                className: 'details-control',
                orderable: false,
                data: null,
                defaultContent: ''
            },
            { data: "referrer_nama" },
            { data: "kode_referal" },
            { data: "kavling_dimiliki" },
            { data: "jumlah_referal" },
            { data: "total_penghasilan", render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
            { data: "total_sudah_cair_promosi", render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
            { data: "total_sudah_cair_keuangan", render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
            { data: "sisa_belum_cair", render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') }
        ],
        order: [[4, 'desc']]
    });

    // Formatting function for row details
    function format(d) {
        let div = $('<div/>').addClass('p-2').text('Loading...');
        
        $.ajax({
            url: base_url + 'api/mgm/subrows',
            type: 'POST',
            data: { id_konsumen_referrer: d.id_konsumen_referrer, id_proyek: activeProyekId },
            success: function(res) {
                if (res.success) {
                    let html = '<table class="table table-sm subrow-table">';
                    html += '<thead><tr>' +
                            '<th>Nama Referred</th>' +
                            '<th>Kavling Referred</th>' +
                            '<th>Status MKDT</th>' +
                            '<th>Tahapan Bonus</th>' +
                            '<th>Status Bonus</th>' +
                            '<th>Nominal</th>' +
                            '<th>Dibayar Promosi</th>' +
                            '<th>Cair Keuangan</th>' +
                            '<th>Keterangan</th>' +
                            '<th>Aksi</th>' +
                            '</tr></thead><tbody>';
                    
                    if (res.data.length === 0) {
                        html += '<tr><td colspan="10" class="text-center">Belum ada tahapan bonus yang tercapai</td></tr>';
                    } else {
                        res.data.forEach(function(row) {
                            let actionBtn = '';
                            if (row.bonus_status === 'eligible') {
                                actionBtn = `<button class="btn btn-sm btn-primary btn-action" data-action="confirm" data-id="${row.id_bonus}" data-nominal="${row.nominal_bonus}">Konfirmasi</button> ` +
                                            `<button class="btn btn-sm btn-danger btn-action" data-action="cancel" data-id="${row.id_bonus}">Batal</button>`;
                            } else if (row.bonus_status === 'dikonfirmasi') {
                                actionBtn = `<button class="btn btn-sm btn-success btn-action" data-action="pay_promosi" data-id="${row.id_bonus}">Bayar (Promosi)</button> ` +
                                            `<button class="btn btn-sm btn-warning btn-action" data-action="submit_keuangan" data-id="${row.id_bonus}">Ajukan Keuangan</button>`;
                            } else if (row.bonus_status === 'dibayar_promosi') {
                                actionBtn = `<button class="btn btn-sm btn-warning btn-action" data-action="submit_keuangan" data-id="${row.id_bonus}">Ajukan Keuangan</button>`;
                            }

                            if(row.id_bonus) {
                                actionBtn += ` <button class="btn btn-sm btn-info btn-action" data-action="keterangan" data-id="${row.id_bonus}" data-ket="${row.bonus_keterangan || ''}">Keterangan</button>`;
                            }

                            html += '<tr>' +
                                '<td>' + (row.referred_nama || '-') + '</td>' +
                                '<td>' + (row.referred_kavling || '-') + '</td>' +
                                '<td><span class="badge badge-light-primary">' + (row.status_mkdt || '-') + '</span></td>' +
                                '<td>' + (row.nama_tahapan || '-') + '</td>' +
                                '<td><span class="badge badge-light-secondary">' + (row.bonus_status || '-') + '</span></td>' +
                                '<td>' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(row.nominal_bonus) + '</td>' +
                                '<td>' + (row.paid_by_promosi == 1 ? '<i class="fas fa-check text-success"></i>' : '-') + 
                                         (row.bukti_bayar_promosi ? ` <a href="${base_url}${row.bukti_bayar_promosi}" target="_blank"><i class="fas fa-image"></i></a>` : '') + '</td>' +
                                '<td>' + (row.cair_keuangan_at ? row.cair_keuangan_at : '-') + '</td>' +
                                '<td>' + (row.bonus_keterangan || '-') + '</td>' +
                                '<td>' + actionBtn + '</td>' +
                                '</tr>';
                        });
                    }
                    html += '</tbody></table>';
                    div.html(html);
                }
            }
        });
        
        return div;
    }

    $('.datatables-mgm tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dtMgm.row( tr );
 
        if ( row.child.isShown() ) {
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });

    // Handle Action Buttons
    $(document).on('click', '.btn-action', function() {
        let action = $(this).data('action');
        let id = $(this).data('id');
        
        $('#action_id_bonus').val(id);
        $('#action_type').val(action);
        
        $('#form-konfirmasi, #form-bayar-promosi, #form-keterangan, #form-submit-keuangan').hide();

        if (action === 'confirm') {
            $('#modalBonusActionTitle').text('Konfirmasi Bonus');
            $('#action_nominal_bonus').val($(this).data('nominal'));
            $('#form-konfirmasi').show();
        } else if (action === 'pay_promosi') {
            $('#modalBonusActionTitle').text('Bayar via Promosi');
            $('#form-bayar-promosi').show();
        } else if (action === 'submit_keuangan') {
            $('#modalBonusActionTitle').text('Ajukan ke Keuangan');
            $('#form-submit-keuangan').show();
        } else if (action === 'cancel' || action === 'keterangan') {
            $('#modalBonusActionTitle').text(action === 'cancel' ? 'Batalkan Bonus' : 'Update Keterangan');
            $('#action_keterangan').val($(this).data('ket') || '');
            $('#form-keterangan').show();
        }

        $('#modalBonusAction').modal('show');
    });

    $('#btn-save-action').click(function() {
        let action = $('#action_type').val();
        let id = $('#action_id_bonus').val();
        
        let url = '';
        let formData = new FormData();
        formData.append('id_bonus', id);

        if (action === 'confirm') {
            url = 'api/mgm/confirm-bonus';
            formData.append('nominal_bonus', $('#action_nominal_bonus').val());
        } else if (action === 'pay_promosi') {
            url = 'api/mgm/pay-promosi';
            let file = $('#action_bukti_bayar')[0].files[0];
            if (!file) return Swal.fire('Error', 'File bukti bayar wajib diupload', 'error');
            formData.append('bukti_bayar', file);
        } else if (action === 'submit_keuangan') {
            url = 'api/mgm/submit-keuangan';
        } else if (action === 'cancel') {
            url = 'api/mgm/cancel-bonus';
            formData.append('keterangan', $('#action_keterangan').val());
        } else if (action === 'keterangan') {
            url = 'api/mgm/update-keterangan';
            formData.append('keterangan', $('#action_keterangan').val());
        }

        $.ajax({
            url: base_url + url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire('Sukses', res.data.message, 'success');
                    $('#modalBonusAction').modal('hide');
                    dtMgm.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', res.messages, 'error');
                }
            }
        });
    });

    // SETTINGS STAGES
    let dtStages;
    $('#btn-setting-stages').click(function() {
        $('#modalSettingStages').modal('show');
        if (!dtStages) {
            dtStages = $('#table-stages').DataTable({
                ajax: {
                    url: base_url + "api/mgm/stages/list",
                    type: "POST",
                    data: function(d) { d.id_proyek = activeProyekId; }
                },
                columns: [
                    { data: "nama_tahapan" },
                    { data: "trigger_status_mkdt" },
                    { data: "nominal_default", render: $.fn.dataTable.render.number(',', '.', 0) },
                    { data: "urutan" },
                    { data: "is_active", render: function(d) { return d == 1 ? 'Ya' : 'Tidak'; } },
                    { 
                        data: "id", 
                        render: function(data, type, row) {
                            return `<button class="btn btn-sm btn-info btn-edit-stage" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button> `+
                                   `<button class="btn btn-sm btn-danger btn-delete-stage" data-id="${data}"><i class="fas fa-trash"></i></button>`;
                        }
                    }
                ],
                searching: false,
                paging: false,
                info: false
            });
        } else {
            dtStages.ajax.reload();
        }
    });

    $('#btn-save-stage').click(function() {
        $.ajax({
            url: base_url + 'api/mgm/stages/save',
            type: 'POST',
            data: {
                id: $('#stage_id').val(),
                id_proyek: activeProyekId,
                nama_tahapan: $('#stage_nama').val(),
                trigger_status_mkdt: $('#stage_trigger').val(),
                nominal_default: $('#stage_nominal').val(),
                urutan: $('#stage_urutan').val(),
                is_active: $('#stage_aktif').val()
            },
            success: function(res) {
                if (res.success) {
                    Swal.fire('Sukses', res.data.message, 'success');
                    $('#form-stage')[0].reset();
                    $('#stage_id').val('');
                    dtStages.ajax.reload();
                } else {
                    Swal.fire('Error', res.messages, 'error');
                }
            }
        });
    });

    $('#btn-reset-stage').click(function() {
        $('#form-stage')[0].reset();
        $('#stage_id').val('');
    });

    $(document).on('click', '.btn-edit-stage', function() {
        let row = $(this).data('row');
        $('#stage_id').val(row.id);
        $('#stage_nama').val(row.nama_tahapan);
        $('#stage_trigger').val(row.trigger_status_mkdt);
        $('#stage_nominal').val(row.nominal_default);
        $('#stage_urutan').val(row.urutan);
        $('#stage_aktif').val(row.is_active);
    });

    $(document).on('click', '.btn-delete-stage', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Tahapan?',
            text: "Anda yakin ingin menghapus tahapan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'api/mgm/stages/delete',
                    type: 'POST',
                    data: { id: id },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Terhapus!', res.data.message, 'success');
                            dtStages.ajax.reload();
                        }
                    }
                });
            }
        });
    });
});
