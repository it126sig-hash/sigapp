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
        stateSave: false,
        destroy: true,
        columns: [
            {
                className: 'details-control text-center align-middle',
                orderable: false,
                data: null,
                defaultContent: '<div class="btn btn-sm btn-light btn-icon rounded"><i class="fas fa-chevron-down"></i></div>'
            },
            {
                data: null,
                render: function(data, type, row) {
                    let initials = (row.referrer_nama || 'UN').match(/\b\w/g) || [];
                    initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
                    return `
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-light-primary mr-1">
                                <div class="avatar-content">${initials}</div>
                            </div>
                            <div>
                                <span class="font-weight-bold">${row.referrer_nama}</span><br>
                                <small class="text-muted">${row.kavling_dimiliki || '-'}</small>
                            </div>
                        </div>
                    `;
                }
            },
            { 
                data: "kode_referal",
                render: function(data) {
                    return `<span class="badge border text-dark font-weight-bold" style="padding: 0.5rem 1rem;">${data}</span>`;
                }
            },
            { 
                data: "jumlah_referal",
                render: function(data) {
                    return `<span class="badge badge-light-primary badge-pill" style="font-size: 0.9rem;">${data}</span>`;
                }
            },
            { data: "total_penghasilan", render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
            { 
                data: "total_sudah_cair_promosi", 
                render: function(data) {
                    let formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(data);
                    return `<span class="text-primary">${formatted}</span>`;
                }
            },
            { 
                data: "total_sudah_cair_keuangan", 
                render: function(data) {
                    let formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(data);
                    return `<span class="text-success">${formatted}</span>`;
                }
            },
            { 
                data: "sisa_belum_cair", 
                render: function(data) {
                    let formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(data);
                    return `<span class="text-danger">${formatted}</span>`;
                }
            }
        ],
        order: [[4, 'desc']]
    });

    // Formatting function for row details
    function format(d) {
        let div = $('<div/>').addClass('subrow-wrapper m-0').text('Loading...');
        
        $.ajax({
            url: base_url + 'api/mgm/subrows',
            type: 'POST',
            data: { id_konsumen_referrer: d.id_konsumen_referrer, id_proyek: activeProyekId },
            success: function(res) {
                if (res.success) {
                    let html = `
                        <div class="mb-1 font-weight-bold text-left" style="margin-top: -0.5rem;"><i class="fas fa-level-up-alt fa-rotate-90"></i> Daftar Member yang Diajak</div>
                        <table class="table table-sm subrow-table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-uppercase">AKSI</th>
                                    <th class="text-uppercase">NAMA REFERRED & KAVLING</th>
                                    <th class="text-uppercase">STATUS MKDT</th>
                                    <th class="text-uppercase">STATUS BONUS</th>
                                    <th class="text-uppercase">NOMINAL BONUS</th>
                                    <th class="text-uppercase">DIBAYAR PROMOSI</th>
                                    <th class="text-uppercase">CAIR KEUANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    if (res.data.length === 0) {
                        html += '<tr><td colspan="7" class="text-center">Belum ada member yang diajak / mencapai tahapan bonus</td></tr>';
                    } else {
                        // Group by id_mkdt_referred
                        let groups = {};
                        res.data.forEach(function(row) {
                            if (!groups[row.id_mkdt_referred]) {
                                groups[row.id_mkdt_referred] = {
                                    id_mkdt_referred: row.id_mkdt_referred,
                                    referred_nama: row.referred_nama,
                                    referred_kavling: row.referred_kavling,
                                    status_mkdt: row.status_mkdt,
                                    stages: []
                                };
                            }
                            groups[row.id_mkdt_referred].stages.push(row);
                        });

                        Object.values(groups).forEach(function(g) {
                            let totalNominal = 0;
                            let totalDibayarPromosi = 0;
                            let totalCairKeuangan = 0;
                            
                            let hasMenunggu = false;
                            let hasDiajukan = false;
                            let hasBelum = false;
                            let hasCair = false;

                            g.stages.forEach(function(s) {
                                let nominal = parseFloat(s.nominal_bonus) || 0;
                                totalNominal += nominal;
                                
                                if (s.paid_by_promosi == 1 || s.bonus_status === 'dibayar_promosi' || s.bonus_status === 'selesai' || s.bonus_status === 'cair' || s.bonus_status === 'diajukan_keuangan') {
                                    totalDibayarPromosi += nominal;
                                }
                                if (s.cair_keuangan_at || s.bonus_status === 'cair' || s.bonus_status === 'selesai') {
                                    totalCairKeuangan += nominal;
                                }

                                if (s.bonus_status === 'diajukan_keuangan') hasMenunggu = true;
                                else if (s.bonus_status === 'dikonfirmasi' || s.bonus_status === 'dibayar_promosi') hasDiajukan = true;
                                else if (s.bonus_status === 'eligible' || !s.bonus_status) hasBelum = true;
                                else if (s.bonus_status === 'cair' || s.bonus_status === 'selesai') hasCair = true;
                            });

                            let badgeText = 'Belum diajukan';
                            let badgeClass = 'badge-light-secondary';

                            if (hasMenunggu) { badgeText = 'Menunggu Pencairan'; badgeClass = 'badge-light-info'; }
                            else if (hasDiajukan) { badgeText = 'Diajukan Promosi'; badgeClass = 'badge-light-warning'; }
                            else if (hasBelum) { badgeText = 'Belum diajukan'; badgeClass = 'badge-light-secondary'; }
                            else if (hasCair) { badgeText = 'Cair Dari Keuangan'; badgeClass = 'badge-light-success'; }

                            let badgeMkdtClass = 'badge-light-primary';
                            if (g.status_mkdt.toLowerCase() === 'akad') badgeMkdtClass = 'badge-light-success';
                            if (g.status_mkdt.toLowerCase() === 'batal') badgeMkdtClass = 'badge-light-danger';

                            // Save grouped data stringified to pass to modal
                            let stagesJson = encodeURIComponent(JSON.stringify(g.stages));

                            html += `
                                <tr>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-light btn-open-detail" data-referrer="${d.referrer_nama}" data-nama="${g.referred_nama}" data-kavling="${g.referred_kavling}" data-stages="${stagesJson}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">${g.referred_nama || '-'}</span><br>
                                        <small class="text-muted">${g.referred_kavling || '-'}</small>
                                    </td>
                                    <td><span class="badge badge-pill ${badgeMkdtClass}">${g.status_mkdt || '-'}</span></td>
                                    <td><span class="badge badge-pill ${badgeClass}">${badgeText}</span></td>
                                    <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(totalNominal)}</td>
                                    <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(totalDibayarPromosi)}</td>
                                    <td>${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(totalCairKeuangan)}</td>
                                </tr>
                            `;
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
            $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
        else {
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
            $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    let currentStages = [];
    let formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits:0 }).format(val);

    window.changeFormAction = function(val) {
        $('#form_action_type').val(val);
        if (val === 'pay_promosi' || val === 'submit_keuangan') {
            $('#group_upload').show();
            $('#form_bukti').prop('required', true);
            $('#group_metode').hide(); 
            $('#btn_submit_dinamis').text(val === 'submit_keuangan' ? 'Ajukan ke Keuangan' : 'Bayar Promosi');
        } else {
            $('#group_upload').hide();
            $('#form_bukti').prop('required', false);
        }
    };

    window.selectStage = function(index) {
        if (!currentStages || !currentStages[index]) return;
        let stage = currentStages[index];
    
        $('#timeline_section').show();
        $('#timeline_stage_name').text('(' + (stage.nama_tahapan || '') + ')');
    
        // Build timeline
        let timelineHtml = '';
    
        // Step 1: Pengajuan Dibuat
        let isCreated = stage.bonus_status !== 'batal';
        let createdDate = stage.created_at || stage.eligible_at || '-';
        let createdStatus = (stage.bonus_status === 'eligible') ? 'ACTIVE' : (isCreated ? 'DONE' : '');
        timelineHtml += `
        <div class="timeline-item ${createdStatus === 'DONE' ? 'done' : (createdStatus === 'ACTIVE' ? 'active' : '')}">
            <div class="timeline-title">Pengajuan Dibuat</div>
            <div class="timeline-desc">Oleh Sistem &bull; ${createdDate}</div>
            <div class="timeline-status">${createdStatus}</div>
        </div>`;
    
        // Step 2: Verifikasi Dept. Promosi
        let promosiActive = ['dikonfirmasi', 'dibayar_promosi'].includes(stage.bonus_status);
        let promosiDone = ['diajukan_keuangan', 'cair', 'selesai'].includes(stage.bonus_status);
        let promosiStatus = promosiDone ? 'DONE' : (promosiActive ? 'ACTIVE' : 'PENDING');
        let promosiDesc = 'Menunggu verifikasi';
        if (stage.confirmed_at) promosiDesc = 'Dikonfirmasi pada ' + stage.confirmed_at;
        if (stage.paid_promosi_at) promosiDesc += '<br>Dana talangan dicairkan pada ' + stage.paid_promosi_at;
        
        timelineHtml += `
        <div class="timeline-item ${promosiStatus === 'DONE' ? 'done' : (promosiStatus === 'ACTIVE' ? 'active' : '')}">
            <div class="timeline-title">Verifikasi Dept. Promosi</div>
            <div class="timeline-desc">${promosiDesc}</div>
            <div class="timeline-status">${promosiStatus}</div>
        </div>`;
    
        // Step 3: Menunggu Keuangan
        let keuanganActive = stage.bonus_status === 'diajukan_keuangan';
        let keuanganDone = ['cair', 'selesai'].includes(stage.bonus_status);
        let keuanganStatus = keuanganDone ? 'DONE' : (keuanganActive ? 'ACTIVE' : 'PENDING');
        let keuanganDesc = keuanganDone ? ('Pencairan selesai pada ' + (stage.cair_keuangan_at||'-')) : 'Sedang dalam proses review pencairan dana';
    
        timelineHtml += `
        <div class="timeline-item ${keuanganStatus === 'DONE' ? 'done' : (keuanganStatus === 'ACTIVE' ? 'active' : '')}">
            <div class="timeline-title">Menunggu Keuangan</div>
            <div class="timeline-desc">${keuanganDesc}</div>
            <div class="timeline-status">${keuanganStatus}</div>
        </div>`;
    
        $('#timeline_container').html(timelineHtml);
    
        // Form logic
        $('#form_id_bonus').val(stage.id_bonus || '');
        $('#form_nominal_pengajuan').val(formatCurrency(stage.nominal_bonus || 0));
        
        // Reset form displays
        $('#dynamic_action_container').remove(); 
        $('#form_action_type').val('');
        $('#group_upload').hide();
        $('#group_metode').hide();
        $('#group_keterangan').hide();
        $('#btn_submit_dinamis').show().removeClass('btn-danger').addClass('btn-primary').text('Kirim Pengajuan Sekarang');
        $('#form_nominal_pengajuan').prop('readonly', true);
        $('#form_bukti').prop('required', false);
    
        if (stage.bonus_status === 'eligible') {
            $('#form_section').show();
            $('#form_title_action').text('Konfirmasi Kelayakan');
            $('#form_action_type').val('konfirmasi');
            $('#btn_submit_dinamis').text('Konfirmasi Sekarang');
            $('#form_nominal_pengajuan').prop('readonly', false); // Allow edit for konfirmasi
        } else if (stage.bonus_status === 'dikonfirmasi' || stage.bonus_status === 'dibayar_promosi') {
            $('#form_section').show();
            $('#form_title_action').text('Tindakan Lanjutan');
            
            let options = '';
            if (stage.bonus_status === 'dikonfirmasi') {
                options += '<option value="pay_promosi">Bayar (Promosi)</option>';
            }
            options += '<option value="submit_keuangan">Ajukan Keuangan</option>';
            
            let actionSelectHtml = `
                <div class="col-md-12 mb-3" id="dynamic_action_container">
                    <label class="text-muted font-weight-bold" style="font-size: 0.75rem;">TINDAKAN</label>
                    <select class="form-control form-control-lg font-weight-bold" id="dynamic_action_type" onchange="changeFormAction(this.value)">
                        ${options}
                    </select>
                </div>
            `;
            $('#formActionDinamis .row').first().before(actionSelectHtml);
            
            // Trigger change for the first option
            changeFormAction($('#dynamic_action_type').val());
        } else {
            // cair, selesai, batal -> no form actions
            $('#form_section').hide();
        }
    };

    window.submitFormActionDinamis = function(e) {
        e.preventDefault();
        let actionType = $('#form_action_type').val();
        let idBonus = $('#form_id_bonus').val();
        
        if(!actionType || !idBonus) return;
        
        let url = base_url + 'api/mgm/';
        if (actionType === 'konfirmasi') url += 'confirm-bonus';
        else if (actionType === 'pay_promosi') url += 'pay-promosi';
        else if (actionType === 'submit_keuangan') url += 'submit-keuangan';
        
        let formData = new FormData($('#formActionDinamis')[0]);
        formData.append('id_bonus', idBonus);
        
        if (actionType === 'konfirmasi') {
            let nominalStr = $('#form_nominal_pengajuan').val().replace(/[^0-9,-]+/g,"");
            formData.set('nominal_bonus', nominalStr);
        }
        
        // Backend expects 'bukti_bayar'
        if (formData.has('bukti') && formData.get('bukti').size > 0) {
            formData.set('bukti_bayar', formData.get('bukti'));
        }
        
        Swal.fire({
            title: 'Proses data...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    $('#modalDetailPencairan').modal('hide');
                    dtMgm.ajax.reload(null, false);
                });
            },
            error: function(err) {
                let msg = err.responseJSON ? err.responseJSON.messages.error : 'Terjadi kesalahan';
                if(typeof msg === 'object') msg = Object.values(msg).join(', ');
                Swal.fire('Gagal', msg, 'error');
            }
        });
    };

    // Paste image from clipboard
    document.addEventListener('paste', function(e) {
        let isModalOpen = $('#modalDetailPencairan').hasClass('show');
        if (!isModalOpen) return;
        let fileInput = document.getElementById('form_bukti');
        if (!fileInput) return;

        let items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            let item = items[index];
            if (item.kind === 'file') {
                let blob = item.getAsFile();
                let data = new DataTransfer();
                data.items.add(new File([blob], "pasted_image.png", {type: blob.type}));
                fileInput.files = data.files;
                $(fileInput).next('.custom-file-label').html("pasted_image.png");
            }
        }
    });

    // Handle Open Detail Pencairan Modal
    $(document).on('click', '.btn-open-detail', function() {
        let nama = $(this).data('nama');
        let kavling = $(this).data('kavling');
        let referrer = $(this).data('referrer');
        let stagesStr = decodeURIComponent($(this).data('stages'));
        currentStages = JSON.parse(stagesStr);

        $('#detail_nama_referrer').text(referrer || '-');
        $('#detail_nama_referred').text(nama || '-');
        $('#detail_kavling_referred').text(kavling || '-');

        let sumPotensi = 0;
        let sumCair = 0;
        let stagesHtml = '';

        if (currentStages.length === 0) {
            stagesHtml = '<div class="text-center p-3 text-muted">Belum ada tahapan</div>';
        } else {
            currentStages.forEach((stage, idx) => {
                let nominal = parseFloat(stage.nominal_bonus) || 0;
                sumPotensi += nominal;
                if (['cair', 'selesai'].includes(stage.bonus_status)) {
                    sumCair += nominal;
                }

                let badgeClass = 'badge-light-secondary';
                let badgeText = stage.bonus_status_badge || 'Belum diajukan';
                let btnClass = 'btn-outline-primary';
                let btnText = 'Ajukan Pencairan';
                
                if (badgeText === 'Menunggu Pencairan') { badgeClass = 'badge-light-info'; }
                else if (badgeText === 'Diajukan Promosi') { badgeClass = 'badge-light-warning'; }
                else if (badgeText === 'Cair Dari Keuangan') { badgeClass = 'badge-light-success'; }
                else if (badgeText === 'Batal') { badgeClass = 'badge-light-danger'; }

                // Customize states based on status
                if (stage.bonus_status === 'eligible') { 
                    badgeClass = 'badge-light-warning'; badgeText = 'Pending'; btnClass = 'btn-primary';
                } else if (['cair', 'selesai'].includes(stage.bonus_status)) { 
                    badgeClass = 'badge-light-success'; badgeText = 'Completed'; btnText = 'Lihat Detail';
                } else if (['dikonfirmasi', 'dibayar_promosi'].includes(stage.bonus_status)) {
                    btnClass = 'btn-primary';
                }

                stagesHtml += `
                <div class="stage-card">
                    <div>
                        <div class="font-weight-bold" style="font-size: 0.85rem; color: #4b5563;">${stage.nama_tahapan || '-'}</div>
                        <div class="font-weight-bold text-dark" style="font-size: 1.1rem;">${formatCurrency(nominal)}</div>
                    </div>
                    <div class="text-right d-flex flex-column align-items-end justify-content-center">
                        <span class="badge badge-pill ${badgeClass} px-3 py-2 mb-2 w-100 text-center" style="min-width: 130px;">${badgeText}</span>
                        <button class="btn btn-sm ${btnClass} px-3 w-100" style="min-width: 130px; border-radius: 20px;" onclick="selectStage(${idx})">${btnText}</button>
                    </div>
                </div>`;
            });
        }

        $('#stages_list_container').html(stagesHtml);
        
        $('#summary_potensi').text(formatCurrency(sumPotensi));
        $('#summary_cair').text(formatCurrency(sumCair));
        $('#summary_sisa').text(formatCurrency(sumPotensi - sumCair));

        $('#timeline_section').hide();
        $('#form_section').hide();

        if (currentStages.length > 0) {
            let activeIdx = 0;
            for (let i=0; i<currentStages.length; i++) {
                if (!['cair','selesai','batal'].includes(currentStages[i].bonus_status)) {
                    activeIdx = i;
                    break;
                }
            }
            selectStage(activeIdx);
        }

        $('#modalDetailPencairan').modal('show');

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
