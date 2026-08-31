$(document).ready(function() {
    const activeProyekId = window.SIGAPP?.activeProyekId || 1;
    const roles = window.SIGAPP?.mgmRoles || { canPromosi: false, canKeuangan: false };

    let currentStages = [];
    let selectedStage = null;
    let dtStages;

    const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(parseFloat(val) || 0);

    const escapeHtml = (value) => $('<div>').text(value == null ? '' : String(value)).html();
    const today = () => new Date().toISOString().slice(0, 10);
    const parseDate = (value) => value ? new Date(String(value).replace(' ', 'T')) : null;
    const showSection = (selector) => $(selector).removeClass('d-none').show();
    const hideSection = (selector) => $(selector).addClass('d-none').hide();

    function formatDateTime(value) {
        const date = parseDate(value);
        if (!date || Number.isNaN(date.getTime())) return value || '-';

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).format(date).replace(/\./g, '');
    }

    function stageLabel(stage) {
        if (!stage.id_bonus) return 'Belum Aktif';
        if (stage.bonus_status_label) return stage.bonus_status_label;
        if (stage.bonus_status === 'diajukan_keuangan') return 'Diajukan Keuangan';
        if (stage.bonus_status === 'dibayar_promosi') return 'Cair Dari Promosi';
        if (['cair', 'selesai'].includes(stage.bonus_status)) return 'Cair Dari Keuangan';
        if (stage.bonus_status === 'batal') return 'Batal';
        return 'Belum diajukan';
    }

    function badgeClass(label) {
        if (label === 'Diajukan Keuangan') return 'badge-light-info';
        if (label === 'Cair Dari Promosi') return 'badge-light-primary';
        if (label === 'Cair Dari Keuangan') return 'badge-light-success';
        if (label === 'Batal') return 'badge-light-danger';
        return 'badge-light-secondary';
    }

    function statusPillClass(label) {
        if (label === 'Diajukan Keuangan') return 'is-info';
        if (['Cair Dari Promosi', 'Cair Dari Keuangan'].includes(label)) return 'is-success';
        if (label === 'Batal') return 'is-danger';
        if (label === 'Belum Aktif') return 'is-muted';
        return '';
    }

    function stageIconClass(stageName) {
        const name = String(stageName || '').toLowerCase();
        if (name.includes('akad')) return 'fas fa-graduation-cap';
        return 'far fa-calendar-check';
    }

    function uniqueStatusBadges(stages) {
        const labels = [];
        stages.forEach((stage) => {
            const label = stageLabel(stage);
            if (!labels.includes(label)) labels.push(label);
        });
        return labels.map((label) => `<span class="badge badge-pill ${badgeClass(label)} mr-25 mb-25">${label}</span>`).join('');
    }

    function sumPromosi(stages) {
        return stages.reduce((total, stage) => {
            const nominal = parseFloat(stage.nominal_bonus) || 0;
            const paid = parseInt(stage.paid_by_promosi || 0, 10) === 1 || ['dibayar_promosi', 'selesai'].includes(stage.bonus_status);
            return total + (paid ? nominal : 0);
        }, 0);
    }

    function sumKeuangan(stages) {
        return stages.reduce((total, stage) => {
            const nominal = parseFloat(stage.nominal_cair_keuangan || stage.nominal_bonus) || 0;
            const paid = stage.cair_keuangan_at || ['cair', 'selesai'].includes(stage.bonus_status);
            return total + (paid ? nominal : 0);
        }, 0);
    }

    const dtMgm = $('.datatables-mgm').DataTable({
        ajax: {
            url: base_url + 'api/mgm/list',
            type: 'POST',
            data: function(d) {
                d.id_proyek = activeProyekId;
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
                                <div class="avatar-content">${escapeHtml(initials)}</div>
                            </div>
                            <div>
                                <span class="font-weight-bold">${escapeHtml(row.referrer_nama)}</span><br>
                                <small class="text-muted">${escapeHtml(row.kavling_dimiliki || '-')}</small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'kode_referal',
                render: function(data) {
                    return `<span class="badge border text-dark font-weight-bold px-1 py-50">${escapeHtml(data || '-')}</span>`;
                }
            },
            {
                data: 'jumlah_referal',
                render: function(data) {
                    return `<span class="badge badge-light-primary badge-pill">${escapeHtml(data || 0)}</span>`;
                }
            },
            { data: 'total_penghasilan', render: $.fn.dataTable.render.number(',', '.', 0, 'Rp ') },
            {
                data: 'total_sudah_cair_promosi',
                render: function(data) {
                    return `<span class="text-primary font-weight-bold">${formatCurrency(data)}</span>`;
                }
            },
            {
                data: null,
                className: 'text-right',
                render: function(data, type, row) {
                    return `
                        <div class="small text-right">
                            <div><span class="text-muted">Sudah cair:</span> <span class="text-success font-weight-bold">${formatCurrency(row.total_sudah_cair_keuangan)}</span></div>
                            <div><span class="text-muted">Sedang diajukan:</span> <span class="text-info font-weight-bold">${formatCurrency(row.total_sedang_diajukan_keuangan)}</span></div>
                            <div><span class="text-muted">Sisa:</span> <span class="text-danger font-weight-bold">${formatCurrency(row.sisa_belum_cair)}</span></div>
                        </div>
                    `;
                }
            }
        ],
        order: [[4, 'desc']]
    });

    function formatSubRows(d) {
        const div = $('<div/>').addClass('subrow-wrapper m-0').text('Loading...');

        $.ajax({
            url: base_url + 'api/mgm/subrows',
            type: 'POST',
            data: { id_konsumen_referrer: d.id_konsumen_referrer, id_proyek: activeProyekId },
            success: function(res) {
                if (!res.success) return;

                let html = `
                    <div class="mb-1 font-weight-bold text-left"><i class="fas fa-level-up-alt fa-rotate-90"></i> Daftar Member yang Diajak</div>
                    <table class="table table-sm subrow-table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-uppercase">Aksi</th>
                                <th class="text-uppercase">Nama Referred & Kavling</th>
                                <th class="text-uppercase">Status MKDT</th>
                                <th class="text-uppercase">Status Bonus</th>
                                <th class="text-uppercase">Nominal Bonus</th>
                                <th class="text-uppercase">Dibayar Promosi</th>
                                <th class="text-uppercase">Cair Keuangan</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                if (res.data.length === 0) {
                    html += '<tr><td colspan="7" class="text-center">Belum ada member yang diajak / mencapai tahapan bonus</td></tr>';
                } else {
                    const groups = {};
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

                    Object.values(groups).forEach(function(group) {
                        const totalNominal = group.stages.reduce((sum, stage) => sum + (parseFloat(stage.nominal_bonus) || 0), 0);
                        const status = String(group.status_mkdt || '').toLowerCase();
                        const badgeMkdtClass = status === 'akad' ? 'badge-light-success' : (status === 'batal' ? 'badge-light-danger' : 'badge-light-primary');
                        const stagesJson = encodeURIComponent(JSON.stringify(group.stages));

                        html += `
                            <tr>
                                <td>
                                    <button class="btn btn-sm btn-icon btn-light btn-open-detail"
                                        data-referrer="${escapeHtml(d.referrer_nama)}"
                                        data-nama="${escapeHtml(group.referred_nama)}"
                                        data-kavling="${escapeHtml(group.referred_kavling)}"
                                        data-stages="${stagesJson}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                                <td>
                                    <span class="font-weight-bold">${escapeHtml(group.referred_nama || '-')}</span><br>
                                    <small class="text-muted">${escapeHtml(group.referred_kavling || '-')}</small>
                                </td>
                                <td><span class="badge badge-pill ${badgeMkdtClass}">${escapeHtml(group.status_mkdt || '-')}</span></td>
                                <td>${uniqueStatusBadges(group.stages)}</td>
                                <td>${formatCurrency(totalNominal)}</td>
                                <td>${formatCurrency(sumPromosi(group.stages))}</td>
                                <td>${formatCurrency(sumKeuangan(group.stages))}</td>
                            </tr>
                        `;
                    });
                }

                html += '</tbody></table>';
                div.html(html);
            }
        });

        return div;
    }

    $('.datatables-mgm tbody').on('click', 'td.details-control', function() {
        const tr = $(this).closest('tr');
        const row = dtMgm.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            row.child(formatSubRows(row.data())).show();
            tr.addClass('shown');
            $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    function resetActionForm() {
        $('#dynamic_action_container').remove();
        $('#formActionDinamis')[0].reset();
        $('#form_action_type').val('');
        $('#form_bukti').prop('required', false);
        $('#form_bukti').next('.custom-file-label').html('<i class="far fa-file mr-1"></i> Pilih File');
        $('#group_upload, #group_tanggal_spp, #group_tanggal_cair, #group_keterangan').hide();
        $('#form_tanggal_spp, #form_tanggal_cair_keuangan').prop('required', false);
        $('#form_nominal_pengajuan').prop('readonly', true).addClass('mgm-readonly-control');
        $('#btn_submit_dinamis').removeClass('btn-danger').addClass('btn-primary').text('Simpan');
    }

    function setActionType(action) {
        if (!selectedStage) return;

        $('#form_action_type').val(action);
        $('#group_upload, #group_tanggal_spp, #group_tanggal_cair').hide();
        $('#form_tanggal_spp, #form_tanggal_cair_keuangan').prop('required', false);
        $('#form_bukti').prop('required', false);
        $('#form_nominal_pengajuan').prop('readonly', true).addClass('mgm-readonly-control');

        if (action === 'konfirmasi') {
            $('#label_nominal_pengajuan').text('NOMINAL BONUS');
            $('#form_title_action').text('Konfirmasi Kelayakan');
            $('#form_nominal_pengajuan').prop('readonly', false).removeClass('mgm-readonly-control');
            $('#btn_submit_dinamis').text('Konfirmasi Sekarang');
        } else if (action === 'pay_promosi') {
            $('#label_nominal_pengajuan').text('NOMINAL BAYAR MEMBER');
            $('#label_upload_bukti').text('BUKTI BAYAR KE MEMBER');
            $('#form_title_action').text('Pembayaran ke Member');
            $('#group_upload').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').text('Catat Pembayaran Promosi');
        } else if (action === 'submit_keuangan') {
            $('#label_nominal_pengajuan').text('NOMINAL PENGAJUAN');
            $('#label_upload_bukti').text('LAMPIRAN SPP');
            $('#form_title_action').text('Pengajuan ke Keuangan');
            $('#form_nominal_pengajuan').prop('readonly', false).removeClass('mgm-readonly-control');
            $('#form_tanggal_spp').val(selectedStage.tanggal_spp || today()).prop('required', true);
            $('#group_tanggal_spp, #group_upload').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').text('Ajukan ke Keuangan');
        } else if (action === 'mark_cair_keuangan') {
            $('#label_nominal_pengajuan').text('NOMINAL CAIR');
            $('#label_upload_bukti').text('BUKTI TRANSFER KE PROMOSI');
            $('#form_title_action').text('Pencairan dari Keuangan');
            $('#form_tanggal_cair_keuangan').val(selectedStage.tanggal_cair_keuangan || today()).prop('required', true);
            $('#group_tanggal_cair, #group_upload').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').text('Cairkan dari Keuangan');
        }
    }

    function actionOptions(stage) {
        const options = [];
        const paidPromosi = parseInt(stage.paid_by_promosi || 0, 10) === 1;

        if (!stage.id_bonus) return options;
        if (roles.canPromosi && stage.bonus_status === 'eligible') options.push({ value: 'konfirmasi', text: 'Ajukan SPP Bonus' });
        if (roles.canPromosi && stage.bonus_status === 'dikonfirmasi') {
            options.push({ value: 'submit_keuangan', text: 'Ajukan SPP Bonus' });
            options.push({ value: 'pay_promosi', text: 'Bayar ke Member' });
        }
        if (roles.canPromosi && stage.bonus_status === 'dibayar_promosi') options.push({ value: 'submit_keuangan', text: 'Ajukan SPP Bonus' });
        if (roles.canPromosi && ['diajukan_keuangan', 'cair'].includes(stage.bonus_status) && !paidPromosi) options.push({ value: 'pay_promosi', text: 'Bayar ke Member' });
        if (roles.canKeuangan && stage.bonus_status === 'diajukan_keuangan') options.push({ value: 'mark_cair_keuangan', text: 'Cair Keuangan' });

        return options;
    }

    function primaryAction(stage) {
        const options = actionOptions(stage);
        if (!options.length) {
            return {
                value: '',
                text: 'Ajukan SPP Bonus',
                disabled: true
            };
        }

        return {
            value: options[0].value,
            text: options[0].text,
            disabled: false
        };
    }

    function renderTimeline(stage) {
        const stageName = stage.nama_tahapan || 'Bonus';

        if (!stage.id_bonus) {
            return `
                <div class="mgm-timeline-card is-muted">
                    <div class="mgm-timeline-heading">Alur ${escapeHtml(stageName)}</div>
                    <div class="text-muted small">Bonus tahap ini belum terbentuk karena status MKDT belum mencapai trigger tahapan.</div>
                </div>
            `;
        }

        const paidPromosi = parseInt(stage.paid_by_promosi || 0, 10) === 1;
        const submitted = stage.bonus_status === 'diajukan_keuangan' || ['cair', 'selesai'].includes(stage.bonus_status);
        const cairKeuangan = stage.cair_keuangan_at || ['cair', 'selesai'].includes(stage.bonus_status);

        const steps = [
            {
                icon: 'fas fa-gift',
                title: '1. Bonus Dibentuk',
                desc: formatDateTime(stage.eligible_at || stage.created_at),
                state: 'is-done'
            },
            {
                icon: 'fas fa-paper-plane',
                title: '2. Pengajuan Promosi',
                desc: submitted ? formatDateTime(stage.submitted_keuangan_at) : 'Belum diajukan',
                state: submitted ? 'is-done' : ''
            },
            {
                icon: 'fas fa-university',
                title: '3. Cair dari Keuangan',
                desc: cairKeuangan ? formatDateTime(stage.tanggal_cair_keuangan || stage.cair_keuangan_at) : 'Belum cair',
                state: cairKeuangan ? 'is-done' : ''
            },
            {
                icon: 'fas fa-user-friends',
                title: '4. Cair ke Member',
                desc: paidPromosi ? formatDateTime(stage.paid_promosi_at) : 'Belum dibayar',
                state: paidPromosi ? 'is-done' : ''
            }
        ];

        return `
            <div class="mgm-timeline-card">
                <div class="mgm-timeline-heading">Alur ${escapeHtml(stageName)}</div>
                <div class="mgm-bonus-timeline">
                    ${steps.map((step) => `
                        <div class="mgm-timeline-step ${step.state}">
                            <div class="mgm-timeline-icon"><i class="${step.icon}"></i></div>
                            <div class="mgm-timeline-title">${escapeHtml(step.title)}</div>
                            <div class="mgm-timeline-desc">${escapeHtml(step.desc)}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    function actionLabel(action) {
        const labels = {
            referral_created: 'Referral dibuat',
            referral_sync: 'Referral disinkronkan',
            referral_deactivated: 'Referral dinonaktifkan',
            bonus_eligible: 'Bonus terbentuk',
            bonus_reactivated: 'Bonus diaktifkan ulang',
            bonus_confirmed: 'Bonus dikonfirmasi',
            paid_by_promosi: 'Dibayar Promosi',
            submitted_to_keuangan: 'Diajukan ke Keuangan',
            cair_from_keuangan: 'Cair dari Keuangan',
            bonus_canceled: 'Bonus dibatalkan',
            bonus_canceled_by_referral_sync: 'Bonus dibatalkan otomatis',
            marked_selesai: 'Ditandai selesai',
            keterangan_updated: 'Catatan diperbarui'
        };
        return labels[action] || action || '-';
    }

    function renderAllHistory(stages) {
        const histories = [];

        (stages || []).forEach((stage) => {
            (stage.histories || []).forEach((history) => {
                histories.push({
                    ...history,
                    stage_name: stage.nama_tahapan || '-'
                });
            });
        });

        histories.sort((left, right) => {
            const leftTime = new Date(String(left.created_at || '').replace(' ', 'T')).getTime() || 0;
            const rightTime = new Date(String(right.created_at || '').replace(' ', 'T')).getTime() || 0;
            if (leftTime === rightTime) return (parseInt(right.id || 0, 10) || 0) - (parseInt(left.id || 0, 10) || 0);
            return rightTime - leftTime;
        });

        if (!histories.length) {
            $('#history_container').html('<div class="text-muted small">Belum ada riwayat perubahan bonus.</div>');
            return;
        }

        const html = histories.map((history) => {
            const changedNominal = history.new_nominal_bonus && history.old_nominal_bonus !== history.new_nominal_bonus;
            const nominalInfo = changedNominal ? `<div class="small text-muted">Nominal bonus: ${formatCurrency(history.old_nominal_bonus || 0)} ke ${formatCurrency(history.new_nominal_bonus)}</div>` : '';
            const note = history.note ? `<div class="small">${escapeHtml(history.note)}</div>` : '';
            return `
                <div class="history-item">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div class="font-weight-bold">${escapeHtml(actionLabel(history.action))}</div>
                        <span class="badge badge-light-primary">${escapeHtml(history.stage_name)}</span>
                    </div>
                    <div class="small text-muted">${escapeHtml(history.created_at || '-')} oleh ${escapeHtml(history.username || '-')}</div>
                    ${nominalInfo}
                    ${note}
                </div>
            `;
        }).join('');

        $('#history_container').html(html);
    }

    function showSummaryTab() {
        $('#mgm-summary-tab').tab('show');
    }

    function showStageTimeline(index) {
        $('.stage-timeline-wrap').addClass('d-none');
        $('.btn-select-stage').attr('aria-expanded', 'false');

        const timeline = $(`#stage_timeline_${index}`);
        if (!timeline.length) return;

        timeline.removeClass('d-none');
        $(`.btn-select-stage[data-index="${index}"]`).attr('aria-expanded', 'true');
    }

    window.selectStage = function(index, preferredAction, mode = 'detail') {
        if (!currentStages || !currentStages[index]) return;
        selectedStage = currentStages[index];

        resetActionForm();
        $('#form_id_bonus').val(selectedStage.id_bonus || '');
        $('#form_nominal_pengajuan').val(formatCurrency(selectedStage.nominal_pengajuan_keuangan || selectedStage.nominal_bonus || 0));

        if (mode === 'detail') {
            hideSection('#form_section');
            showSummaryTab();
            showStageTimeline(index);
            return;
        }

        showSummaryTab();
        $('.stage-timeline-wrap').addClass('d-none');
        $('.btn-select-stage').attr('aria-expanded', 'false');

        const options = actionOptions(selectedStage);
        if (!options.length) {
            hideSection('#form_section');
            return;
        }

        $('#formActionDinamis .row').first().before(`
            <div class="col-md-12 mb-2" id="dynamic_action_container">
                <label class="text-muted font-weight-bold mgm-form-label">TINDAKAN</label>
                <select class="form-control form-control-lg font-weight-bold" id="dynamic_action_type">
                    ${options.map((opt) => `<option value="${opt.value}">${escapeHtml(opt.text)}</option>`).join('')}
                </select>
            </div>
        `);
        showSection('#form_section');
        const action = preferredAction && options.some((opt) => opt.value === preferredAction)
            ? preferredAction
            : options[0].value;
        $('#dynamic_action_type').val(action);
        setActionType(action);
    };

    $(document).on('change', '#dynamic_action_type', function() {
        setActionType($(this).val());
    });

    window.submitFormActionDinamis = function(e) {
        e.preventDefault();
        const actionType = $('#form_action_type').val();
        const idBonus = $('#form_id_bonus').val();
        if (!actionType || !idBonus) return;

        let url = base_url + 'api/mgm/';
        if (actionType === 'konfirmasi') url += 'confirm-bonus';
        else if (actionType === 'pay_promosi') url += 'pay-promosi';
        else if (actionType === 'submit_keuangan') url += 'submit-keuangan';
        else if (actionType === 'mark_cair_keuangan') url += 'mark-cair-keuangan';

        const formData = new FormData($('#formActionDinamis')[0]);
        formData.append('id_bonus', idBonus);

        const nominalStr = $('#form_nominal_pengajuan').val().replace(/[^0-9-]+/g, '');
        if (actionType === 'konfirmasi') formData.set('nominal_bonus', nominalStr);
        if (actionType === 'submit_keuangan') formData.set('nominal_pengajuan', nominalStr);
        if (actionType === 'mark_cair_keuangan') formData.set('nominal_cair_keuangan', nominalStr);
        if (formData.has('bukti') && formData.get('bukti').size > 0) formData.set('bukti_bayar', formData.get('bukti'));

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
                Swal.fire('Berhasil', res.message || res.messages || 'Data tersimpan', 'success').then(() => {
                    $('#modalDetailPencairan').modal('hide');
                    dtMgm.ajax.reload(null, false);
                });
            },
            error: function(err) {
                let msg = err.responseJSON ? (err.responseJSON.messages?.error || err.responseJSON.messages || err.responseJSON.message) : 'Terjadi kesalahan';
                if (typeof msg === 'object') msg = Object.values(msg).join(', ');
                Swal.fire('Gagal', msg, 'error');
            }
        });
    };

    document.addEventListener('paste', function(e) {
        if (!$('#modalDetailPencairan').hasClass('show')) return;
        const fileInput = document.getElementById('form_bukti');
        if (!fileInput) return;

        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file') {
                const blob = item.getAsFile();
                const data = new DataTransfer();
                data.items.add(new File([blob], 'pasted_image.png', { type: blob.type }));
                fileInput.files = data.files;
                $(fileInput).next('.custom-file-label').html('pasted_image.png');
            }
        }
    });

    $(document).on('change', '#form_bukti', function() {
        if (this.files && this.files[0]) {
            $(this).next('.custom-file-label').text(this.files[0].name);
            return;
        }

        $(this).next('.custom-file-label').html('<i class="far fa-file mr-1"></i> Pilih File');
    });

    $(document).on('click', '.btn-open-detail', function() {
        const nama = $(this).data('nama');
        const kavling = $(this).data('kavling');
        const referrer = $(this).data('referrer');
        currentStages = JSON.parse(decodeURIComponent($(this).data('stages')));

        $('#detail_nama_referrer').text(referrer || '-');
        $('#detail_nama_referred').text(nama || '-');
        $('#detail_kavling_referred').text(kavling ? `(${kavling})` : '');

        let sumPotensi = 0;
        let stagesHtml = '';

        if (currentStages.length === 0) {
            stagesHtml = '<div class="text-center p-3 text-muted">Belum ada tahapan</div>';
        } else {
            currentStages.forEach((stage, idx) => {
                const nominal = parseFloat(stage.nominal_bonus) || 0;
                const label = stageLabel(stage);
                const action = primaryAction(stage);
                const statusClass = statusPillClass(label);
                sumPotensi += label === 'Batal' ? 0 : nominal;
                stagesHtml += `
                    <div class="stage-flow-card">
                        <div class="stage-card">
                            <div class="stage-main">
                                <div class="stage-icon"><i class="${stageIconClass(stage.nama_tahapan)}"></i></div>
                                <div class="min-w-0">
                                    <div class="stage-title">${escapeHtml(stage.nama_tahapan || '-')}</div>
                                    <div class="stage-amount">${formatCurrency(nominal)}</div>
                                    <div class="stage-meta">Promosi: ${escapeHtml(stage.promosi_status_label || '-')} | Keuangan: ${escapeHtml(stage.keuangan_status_label || '-')}</div>
                                </div>
                            </div>
                            <div class="stage-status">
                                <span class="stage-status-label">Status</span>
                                <span class="mgm-status-pill ${statusClass}"><span class="mgm-status-dot"></span>${escapeHtml(label)}</span>
                            </div>
                            <div class="stage-actions">
                                <button type="button" class="btn btn-primary btn-stage-primary-action" data-index="${idx}" data-action="${escapeHtml(action.value)}" ${action.disabled ? 'disabled' : ''}>${escapeHtml(action.text)}</button>
                                <button type="button" class="btn btn-outline-primary btn-select-stage" data-index="${idx}" aria-expanded="false">Lihat Detail</button>
                            </div>
                        </div>
                        <div class="stage-timeline-wrap d-none" id="stage_timeline_${idx}">
                            ${renderTimeline(stage)}
                        </div>
                    </div>
                `;
            });
        }

        $('#stages_list_container').html(stagesHtml);
        $('#summary_potensi').text(formatCurrency(sumPotensi));
        $('#summary_cair').text(formatCurrency(sumKeuangan(currentStages)));
        $('#summary_sisa').text(formatCurrency(Math.max(sumPotensi - sumKeuangan(currentStages), 0)));
        hideSection('#form_section');
        $('.stage-timeline-wrap').addClass('d-none');
        renderAllHistory(currentStages);
        showSummaryTab();

        $('#modalDetailPencairan').modal('show');
    });

    $(document).on('click', '.btn-select-stage', function() {
        selectStage(parseInt($(this).data('index'), 10), null, 'detail');
    });

    $(document).on('click', '.btn-stage-primary-action', function() {
        selectStage(parseInt($(this).data('index'), 10), $(this).data('action'), 'action');
        const formTop = $('#form_section').offset()?.top;
        if (formTop) {
            $('#modalDetailPencairan .modal-body').animate({
                scrollTop: $('#modalDetailPencairan .modal-body').scrollTop() + formTop - $('#modalDetailPencairan .modal-body').offset().top - 16
            }, 180);
        }
    });

    $('#btn-setting-stages').click(function() {
        $('#modalSettingStages').modal('show');
        if (!dtStages) {
            dtStages = $('#table-stages').DataTable({
                ajax: {
                    url: base_url + 'api/mgm/stages/list',
                    type: 'POST',
                    data: function(d) { d.id_proyek = activeProyekId; }
                },
                columns: [
                    { data: 'nama_tahapan' },
                    { data: 'trigger_status_mkdt' },
                    { data: 'nominal_default', render: $.fn.dataTable.render.number(',', '.', 0) },
                    { data: 'urutan' },
                    { data: 'is_active', render: function(d) { return d == 1 ? 'Ya' : 'Tidak'; } },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `<button class="btn btn-sm btn-info btn-edit-stage" data-row='${escapeHtml(JSON.stringify(row))}'><i class="fas fa-edit"></i></button> ` +
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
        const row = $(this).data('row');
        $('#stage_id').val(row.id);
        $('#stage_nama').val(row.nama_tahapan);
        $('#stage_trigger').val(row.trigger_status_mkdt);
        $('#stage_nominal').val(row.nominal_default);
        $('#stage_urutan').val(row.urutan);
        $('#stage_aktif').val(row.is_active);
    });

    $(document).on('click', '.btn-delete-stage', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Tahapan?',
            text: 'Anda yakin ingin menghapus tahapan ini?',
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
