$(document).ready(function() {
    const activeProyekId = window.SIGAPP?.activeProyekId || 1;
    const roles = window.SIGAPP?.mgmRoles || { canPromosi: false, canKeuangan: false };
    const canUseMgmSearch = roles.canPromosi || roles.canKeuangan;

    let currentStages = [];
    let currentDetailContext = null;
    let selectedStage = null;
    let selectedAction = '';
    let dtStages;
    let mgmFilters = {};
    let selectedFilterReferrerText = '';
    let filterTanggalMulaiPicker = null;
    let filterTanggalSelesaiPicker = null;

    const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(parseFloat(val) || 0);
    const filterStatusLabels = {
        booking: 'Booking',
        akad: 'Akad',
        cair_bonus_booking: 'Cair Bonus Booking',
        cair_bonus_akad: 'Cair Bonus Akad'
    };

    const escapeHtml = (value) => $('<div>').text(value == null ? '' : String(value)).html();
    const today = () => new Date().toISOString().slice(0, 10);
    const parseDate = (value) => value ? new Date(String(value).replace(' ', 'T')) : null;
    const showSection = (selector) => $(selector).removeClass('d-none').show();
    const hideSection = (selector) => $(selector).addClass('d-none').hide();
    const toDateInputValue = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

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

    function formatDate(value) {
        if (!value || value === '0000-00-00') return '-';
        const date = parseDate(value);
        if (!date || Number.isNaN(date.getTime())) return value || '-';

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
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

    function currentMonthRange() {
        const now = new Date();
        return {
            start: toDateInputValue(new Date(now.getFullYear(), now.getMonth(), 1)),
            end: toDateInputValue(new Date(now.getFullYear(), now.getMonth() + 1, 0))
        };
    }

    function setFilterDateValue(selector, picker, value) {
        if (picker) {
            picker.setDate(value, false);
            return;
        }

        $(selector).val(value);
    }

    function setDefaultFilterDates() {
        const range = currentMonthRange();
        setFilterDateValue('#filter_mgm_tanggal_mulai', filterTanggalMulaiPicker, range.start);
        setFilterDateValue('#filter_mgm_tanggal_selesai', filterTanggalSelesaiPicker, range.end);
    }

    function collectMgmFilters() {
        const kodeReferal = $('#filter_mgm_referrer').val() || '';
        const status = $('#filter_mgm_status').val() || '';
        const hasDateAnchor = !!kodeReferal || !!status;
        const selectedReferrer = $('#filter_mgm_referrer').select2('data')[0];
        selectedFilterReferrerText = selectedReferrer ? selectedReferrer.text : '';

        return {
            kode_referal: kodeReferal,
            filter_status: status,
            tanggal_mulai: hasDateAnchor ? ($('#filter_mgm_tanggal_mulai').val() || '') : '',
            tanggal_selesai: hasDateAnchor ? ($('#filter_mgm_tanggal_selesai').val() || '') : ''
        };
    }

    function appendMgmFilters(data) {
        return Object.assign(data, mgmFilters);
    }

    function countActiveFilters(filters) {
        let count = 0;
        if (filters.kode_referal) count += 1;
        if (filters.filter_status) count += 1;
        if (filters.tanggal_mulai || filters.tanggal_selesai) count += 1;
        return count;
    }

    function updateMgmFilterSummary() {
        const parts = [];
        if (mgmFilters.kode_referal) parts.push(selectedFilterReferrerText || mgmFilters.kode_referal);
        if (mgmFilters.filter_status) parts.push(filterStatusLabels[mgmFilters.filter_status] || mgmFilters.filter_status);
        if (mgmFilters.tanggal_mulai || mgmFilters.tanggal_selesai) {
            parts.push(`${formatDate(mgmFilters.tanggal_mulai) || '-'} s/d ${formatDate(mgmFilters.tanggal_selesai) || '-'}`);
        }

        const activeCount = countActiveFilters(mgmFilters);
        $('#mgm-filter-summary').text(parts.length ? parts.join(' | ') : 'Semua data');
        $('#mgm-filter-count').text(activeCount).toggleClass('d-none', activeCount === 0);
    }

    function closeOpenSubRows() {
        $('.datatables-mgm tbody tr.shown').each(function() {
            const row = dtMgm.row(this);
            row.child.hide();
            $(this).removeClass('shown');
            $(this).find('td.details-control i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });
    }

    function renderMkdtStatusCell(group, badgeMkdtClass) {
        return `
            <div class="subrow-status-list">
                <span class="badge badge-pill ${badgeMkdtClass}">${escapeHtml(group.status_mkdt || '-')}</span>
                <div class="subrow-date-line"><i class="far fa-calendar-check"></i> Tgl Booking: ${escapeHtml(formatDate(group.booking_tgl))}</div>
                <div class="subrow-date-line"><i class="fas fa-graduation-cap"></i> Tgl Akad: ${escapeHtml(formatDate(group.akad_tgl))}</div>
            </div>
        `;
    }

    function renderBonusStatusCell(stages) {
        return stages.map((stage) => {
            const label = stageLabel(stage);
            return `
                <div class="subrow-status-item">
                    <span class="badge badge-pill ${badgeClass(label)} mr-25">${escapeHtml(stage.nama_tahapan || 'Bonus')}: ${escapeHtml(label)}</span>
                    <div class="subrow-date-line"><i class="fas fa-university"></i> Cair Keuangan: ${escapeHtml(formatDate(stage.tanggal_cair_keuangan))}</div>
                    <div class="subrow-date-line"><i class="fas fa-user-check"></i> Cair Member: ${escapeHtml(formatDate(stage.paid_promosi_tanggal))}</div>
                </div>
            `;
        }).join('');
    }

    const dtMgm = $('.datatables-mgm').DataTable({
        ajax: {
            url: base_url + 'api/mgm/list',
            type: 'POST',
            data: function(d) {
                d.id_proyek = activeProyekId;
                appendMgmFilters(d);
            }
        },
        stateSave: false,
        destroy: true,
        searching: canUseMgmSearch,
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
            data: appendMgmFilters({ id_konsumen_referrer: d.id_konsumen_referrer, id_proyek: activeProyekId }),
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
                                booking_tgl: row.booking_tgl,
                                akad_tgl: row.akad_tgl,
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
                        const searchButton = canUseMgmSearch
                            ? `
                                <button class="btn btn-sm btn-icon btn-light btn-open-detail"
                                    data-referrer="${escapeHtml(d.referrer_nama)}"
                                    data-referrer-id="${escapeHtml(d.id_konsumen_referrer)}"
                                    data-id-mkdt="${escapeHtml(group.id_mkdt_referred)}"
                                    data-nama="${escapeHtml(group.referred_nama)}"
                                    data-kavling="${escapeHtml(group.referred_kavling)}"
                                    data-stages="${stagesJson}">
                                    <i class="fas fa-search"></i>
                                </button>
                            `
                            : `
                                <button class="btn btn-sm btn-icon btn-secondary disabled" type="button" disabled title="Pencarian hanya untuk Promosi dan Keuangan">
                                    <i class="fas fa-search"></i>
                                </button>
                            `;

                        html += `
                            <tr>
                                <td>${searchButton}</td>
                                <td>
                                    <span class="font-weight-bold">${escapeHtml(group.referred_nama || '-')}</span><br>
                                    <small class="text-muted">${escapeHtml(group.referred_kavling || '-')}</small>
                                </td>
                                <td>${renderMkdtStatusCell(group, badgeMkdtClass)}</td>
                                <td>${renderBonusStatusCell(group.stages)}</td>
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
        const form = $('#formActionDinamis')[0];
        if (form) form.reset();
        selectedAction = '';
        $('#form_action_type').val('');
        $('#form_bukti').prop('required', false).val('');
        $('#form_bukti_filename').text('Foto/PDF');
        $('#group_upload, #group_tanggal_spp, #group_tanggal_cair, #group_tanggal_pembayaran, #group_recipient, #group_keterangan').hide();
        $('#form_tanggal_spp, #form_tanggal_cair_keuangan, #form_tanggal_pembayaran, #form_nama_penerima, #form_no_rekening_penerima, #form_bank_penerima').prop('required', false);
        $('#form_nominal_pengajuan').prop('readonly', true).addClass('mgm-readonly-control');
        $('#btn_submit_dinamis').removeClass('btn-danger').addClass('btn-primary').html('<i class="fas fa-save mr-50"></i> Simpan');
    }

    function resetToggleButtons() {
        $('.btn-select-stage, .btn-stage-primary-action, .btn-edit-nominal').removeClass('is-active').attr('aria-expanded', 'false');
        $('.btn-select-stage .detail-icon').removeClass('fa-eye-slash').addClass('fa-eye');
        $('.btn-select-stage .detail-text').text('Lihat Detail');
        $('.btn-select-stage .toggle-icon, .btn-stage-primary-action .action-toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }

    function setActionType(action) {
        if (!selectedStage) return;

        $('#form_action_type').val(action);
        $('#group_upload, #group_tanggal_spp, #group_tanggal_cair, #group_tanggal_pembayaran, #group_recipient, #group_keterangan').hide();
        $('#form_tanggal_spp, #form_tanggal_cair_keuangan, #form_tanggal_pembayaran, #form_nama_penerima, #form_no_rekening_penerima, #form_bank_penerima').prop('required', false);
        $('#form_bukti').prop('required', false);
        $('#form_nominal_pengajuan').prop('readonly', true).addClass('mgm-readonly-control');
        $('#form_keterangan_input').val(selectedStage.bonus_keterangan || '');

        if (action === 'update_nominal') {
            $('#label_nominal_pengajuan').text('NOMINAL BONUS');
            $('#form_title_action').text('Edit Nominal Bonus');
            $('#form_nominal_pengajuan').val(formatCurrency(selectedStage.nominal_bonus || 0)).prop('readonly', false).removeClass('mgm-readonly-control');
            $('#group_keterangan').show();
            $('#btn_submit_dinamis').html('<i class="fas fa-save mr-50"></i> Simpan Nominal');
        } else if (action === 'submit_keuangan') {
            $('#label_nominal_pengajuan').text('NOMINAL PENGAJUAN');
            $('#label_upload_bukti').text('LAMPIRAN PENGAJUAN');
            $('#form_title_action').text('Pengajuan SPP Bonus');
            $('#form_nominal_pengajuan').val(formatCurrency(selectedStage.nominal_pengajuan_keuangan || selectedStage.nominal_bonus || 0)).prop('readonly', false).removeClass('mgm-readonly-control');
            $('#form_tanggal_spp').val(selectedStage.tanggal_spp || today()).prop('required', true);
            $('#group_tanggal_spp, #group_upload, #group_keterangan').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').html('<i class="fas fa-paper-plane mr-50"></i> Ajukan SPP Bonus');
        } else if (action === 'pay_promosi') {
            $('#label_nominal_pengajuan').text('NOMINAL BAYAR MEMBER');
            $('#label_upload_bukti').text('LAMPIRAN PEMBAYARAN');
            $('#label_nama_penerima').text('NAMA PENERIMA');
            $('#label_no_rekening').text('NO REKENING PENERIMA');
            $('#label_bank_penerima').text('BANK PENERIMA');
            $('#form_title_action').text('Cair/Bayar ke Member');
            $('#form_nominal_pengajuan').val(formatCurrency(selectedStage.nominal_bonus || 0));
            $('#form_tanggal_pembayaran').val(selectedStage.paid_promosi_tanggal || today()).prop('required', true);
            $('#form_nama_penerima').val(selectedStage.paid_promosi_penerima_nama || '').prop('required', true);
            $('#form_no_rekening_penerima').val(selectedStage.paid_promosi_no_rekening || '').prop('required', true);
            $('#form_bank_penerima').val(selectedStage.paid_promosi_bank || '').prop('required', true);
            $('#group_tanggal_pembayaran, #group_recipient, #group_upload, #group_keterangan').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').html('<i class="fas fa-hand-holding-usd mr-50"></i> Cairkan Bonus ke Member');
        } else if (action === 'mark_cair_keuangan') {
            $('#label_nominal_pengajuan').text('NOMINAL CAIR');
            $('#label_upload_bukti').text('LAMPIRAN CAIR KEUANGAN');
            $('#label_nama_penerima').text('NAMA PENERIMA');
            $('#label_no_rekening').text('NO REKENING');
            $('#label_bank_penerima').text('BANK PENCAIRAN');
            $('#form_title_action').text('Cair Dari Keuangan');
            $('#form_nominal_pengajuan').val(formatCurrency(selectedStage.nominal_cair_keuangan || selectedStage.nominal_pengajuan_keuangan || selectedStage.nominal_bonus || 0));
            $('#form_tanggal_cair_keuangan').val(selectedStage.tanggal_cair_keuangan || today()).prop('required', true);
            $('#form_nama_penerima').val(selectedStage.cair_keuangan_penerima_nama || '').prop('required', true);
            $('#form_no_rekening_penerima').val(selectedStage.cair_keuangan_no_rekening || '').prop('required', true);
            $('#form_bank_penerima').val(selectedStage.cair_keuangan_bank || '').prop('required', true);
            $('#group_tanggal_cair, #group_recipient, #group_upload, #group_keterangan').show();
            $('#form_bukti').prop('required', true);
            $('#btn_submit_dinamis').html('<i class="fas fa-university mr-50"></i> Cairkan dari Keuangan');
        }
    }

    function canEditNominal(stage) {
        const paidPromosi = parseInt(stage.paid_by_promosi || 0, 10) === 1;
        return roles.canPromosi && stage.id_bonus && (paidPromosi || ['eligible', 'dikonfirmasi', 'cair', 'dibayar_promosi', 'selesai'].includes(stage.bonus_status));
    }

    function primaryAction(stage) {
        const paidPromosi = parseInt(stage.paid_by_promosi || 0, 10) === 1;
        if (!stage.id_bonus) {
            return { value: '', text: roles.canKeuangan && !roles.canPromosi ? 'Cair Dari Keuangan' : 'Ajukan SPP Bonus', icon: 'fas fa-paper-plane', disabled: true };
        }
        if (roles.canKeuangan && !roles.canPromosi) {
            return { value: 'mark_cair_keuangan', text: 'Cair Dari Keuangan', icon: 'fas fa-university', disabled: stage.bonus_status !== 'diajukan_keuangan' };
        }
        if (roles.canPromosi && ['eligible', 'dikonfirmasi', 'dibayar_promosi'].includes(stage.bonus_status)) {
            return { value: 'submit_keuangan', text: 'Ajukan SPP Bonus', icon: 'fas fa-paper-plane', disabled: false };
        }
        if (roles.canPromosi && ['diajukan_keuangan', 'cair'].includes(stage.bonus_status) && !paidPromosi) {
            return { value: 'pay_promosi', text: 'Cairkan Bonus ke Member', icon: 'fas fa-hand-holding-usd', disabled: false };
        }
        return { value: '', text: roles.canPromosi ? 'Ajukan SPP Bonus' : 'Cair Dari Keuangan', icon: 'fas fa-paper-plane', disabled: true };
    }

    function fileLink(url, index, type) {
        if (!url) return '<span class="text-muted">Belum ada lampiran</span>';
        return `<button type="button" class="btn btn-link mgm-file-link btn-mgm-lampiran" data-index="${index}" data-type="${escapeHtml(type)}"><i class="far fa-file-alt"></i> Lihat lampiran</button>`;
    }

    function timelineDesc(lines) {
        return lines.map((line) => `<div class="mgm-timeline-line">${line}</div>`).join('');
    }

    function lampiranConfig(stage, type) {
        const configs = {
            spp: {
                title: 'Lampiran Pengajuan SPP',
                icon: 'fas fa-paper-plane',
                url: stage.bukti_pengajuan_keuangan_url,
                rawPath: stage.bukti_pengajuan_keuangan,
                rows: [
                    ['Tahapan Bonus', stage.nama_tahapan || '-'],
                    ['Tanggal Pengajuan', formatDate(stage.tanggal_spp)],
                    ['Dicatat Pada', formatDateTime(stage.submitted_keuangan_at)],
                    ['Diajukan Oleh', stage.submitted_keuangan_username || '-'],
                    ['Nominal Pengajuan', formatCurrency(stage.nominal_pengajuan_keuangan || stage.nominal_bonus || 0)],
                    ['Keterangan / Catatan', stage.bonus_keterangan || '-']
                ]
            },
            keuangan: {
                title: 'Lampiran Cair Dari Keuangan',
                icon: 'fas fa-university',
                url: stage.bukti_transfer_ke_promosi_url,
                rawPath: stage.bukti_transfer_ke_promosi,
                rows: [
                    ['Tahapan Bonus', stage.nama_tahapan || '-'],
                    ['Tanggal Cair', formatDate(stage.tanggal_cair_keuangan)],
                    ['Dicatat Pada', formatDateTime(stage.cair_keuangan_at)],
                    ['Dicairkan Oleh', stage.cair_keuangan_username || '-'],
                    ['Nominal Cair', formatCurrency(stage.nominal_cair_keuangan || stage.nominal_pengajuan_keuangan || stage.nominal_bonus || 0)],
                    ['Nama Penerima', stage.cair_keuangan_penerima_nama || '-'],
                    ['No Rekening', stage.cair_keuangan_no_rekening || '-'],
                    ['Bank Pencairan', stage.cair_keuangan_bank || '-'],
                    ['Keterangan / Catatan', stage.bonus_keterangan || '-']
                ]
            },
            member: {
                title: 'Lampiran Cair Ke Member',
                icon: 'fas fa-user-check',
                url: stage.bukti_bayar_promosi_url,
                rawPath: stage.bukti_bayar_promosi,
                rows: [
                    ['Tahapan Bonus', stage.nama_tahapan || '-'],
                    ['Tanggal Pembayaran', formatDate(stage.paid_promosi_tanggal || stage.paid_promosi_at)],
                    ['Dicatat Pada', formatDateTime(stage.paid_promosi_at)],
                    ['Dicatat Oleh', stage.paid_promosi_username || '-'],
                    ['Nominal Bayar', formatCurrency(stage.nominal_bonus || 0)],
                    ['Nama Penerima', stage.paid_promosi_penerima_nama || '-'],
                    ['No Rekening Penerima', stage.paid_promosi_no_rekening || '-'],
                    ['Bank Penerima', stage.paid_promosi_bank || '-'],
                    ['Keterangan / Catatan', stage.bonus_keterangan || '-']
                ]
            }
        };

        return configs[type] || null;
    }

    function isPdfLampiran(url, rawPath) {
        const value = String(rawPath || url || '').split('?')[0].toLowerCase();
        return value.endsWith('.pdf');
    }

    function renderLampiranPreview(config) {
        if (!config || !config.url) {
            return '<div class="text-muted small">Belum ada lampiran.</div>';
        }

        const url = escapeHtml(config.url);
        if (isPdfLampiran(config.url, config.rawPath)) {
            return `<iframe src="${url}" title="${escapeHtml(config.title)}"></iframe>`;
        }

        return `<img src="${url}" alt="${escapeHtml(config.title)}">`;
    }

    function renderLampiranInfo(config) {
        return config.rows.map(([label, value]) => `
            <div class="mgm-lampiran-info-row">
                <div class="mgm-lampiran-info-label">${escapeHtml(label)}</div>
                <div class="mgm-lampiran-info-value">${escapeHtml(value || '-')}</div>
            </div>
        `).join('');
    }

    function showLampiranModal(index, type) {
        const stage = currentStages[index];
        if (!stage) return;

        const config = lampiranConfig(stage, type);
        if (!config) return;

        $('#modalMgmLampiranTitle').html(`<i class="${config.icon} text-primary mr-50"></i>${escapeHtml(config.title)}`);
        $('#lampiran_progress_stage').text(`${stage.nama_tahapan || '-'} - ${stage.referred_nama || currentDetailContext?.namaReferred || '-'}`);
        $('#lampiran_progress_preview').html(renderLampiranPreview(config));
        $('#lampiran_progress_info').html(renderLampiranInfo(config));

        if (config.url) {
            $('#lampiran_open_link').attr('href', config.url).removeClass('d-none');
        } else {
            $('#lampiran_open_link').attr('href', '#').addClass('d-none');
        }

        $('#modalMgmLampiran').modal('show');
    }

    function renderTimeline(stage, index) {
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
        const submitted = !!stage.submitted_keuangan_at || ['diajukan_keuangan', 'cair', 'selesai'].includes(stage.bonus_status);
        const cairKeuangan = !!stage.cair_keuangan_at || ['cair', 'selesai'].includes(stage.bonus_status);

        const steps = [
            {
                icon: 'fas fa-gift',
                title: 'Bonus Tercatat',
                desc: timelineDesc([escapeHtml(formatDateTime(stage.eligible_at || stage.created_at))]),
                state: 'is-done'
            },
            {
                icon: 'fas fa-paper-plane',
                title: 'Pengajuan SPP',
                desc: submitted
                    ? timelineDesc([
                        `Oleh ${escapeHtml(stage.submitted_keuangan_username || '-')}`,
                        fileLink(stage.bukti_pengajuan_keuangan_url, index, 'spp')
                    ])
                    : timelineDesc(['Belum diajukan']),
                state: submitted ? 'is-done' : ''
            },
            {
                icon: 'fas fa-university',
                title: 'Cair Dari Keuangan',
                desc: cairKeuangan
                    ? timelineDesc([
                        `Oleh ${escapeHtml(stage.cair_keuangan_username || '-')}`,
                        fileLink(stage.bukti_transfer_ke_promosi_url, index, 'keuangan')
                    ])
                    : timelineDesc(['Belum cair']),
                state: cairKeuangan ? 'is-done' : ''
            },
            {
                icon: 'fas fa-user-check',
                title: 'Cair Ke Member',
                desc: paidPromosi
                    ? timelineDesc([
                        `Oleh ${escapeHtml(stage.paid_promosi_username || '-')}`,
                        fileLink(stage.bukti_bayar_promosi_url, index, 'member')
                    ])
                    : timelineDesc(['Belum cair ke member']),
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
                            <div class="mgm-timeline-desc">${step.desc}</div>
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
            bonus_nominal_updated: 'Nominal bonus diperbarui',
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

    function groupSubRows(rows) {
        const groups = {};
        (rows || []).forEach(function(row) {
            if (!groups[row.id_mkdt_referred]) {
                groups[row.id_mkdt_referred] = {
                    id_mkdt_referred: row.id_mkdt_referred,
                    referred_nama: row.referred_nama,
                    referred_kavling: row.referred_kavling,
                    status_mkdt: row.status_mkdt,
                    booking_tgl: row.booking_tgl,
                    akad_tgl: row.akad_tgl,
                    stages: []
                };
            }
            groups[row.id_mkdt_referred].stages.push(row);
        });

        return groups;
    }

    function renderModalStages() {
        let sumPotensi = 0;
        let stagesHtml = '';

        if (currentStages.length === 0) {
            stagesHtml = '<div class="text-center p-3 text-muted">Belum ada tahapan</div>';
        } else {
            currentStages.forEach((stage, idx) => {
                const nominal = parseFloat(stage.nominal_bonus) || 0;
                const label = stageLabel(stage);
                const action = primaryAction(stage);
                const actionClass = action.disabled ? 'btn-secondary' : 'btn-primary';
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
                                    <div class="stage-date-row mt-50">
                                        <span><i class="far fa-calendar-check"></i> Booking: ${escapeHtml(formatDate(stage.booking_tgl))}</span>
                                        <span><i class="fas fa-graduation-cap"></i> Akad: ${escapeHtml(formatDate(stage.akad_tgl))}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="stage-status">
                                <span class="stage-status-label">Status</span>
                                <span class="mgm-status-pill ${statusClass}"><span class="mgm-status-dot"></span>${escapeHtml(label)}</span>
                            </div>
                            <div class="stage-actions">
                                <button type="button" class="btn ${actionClass} btn-stage-primary-action" data-index="${idx}" data-action="${escapeHtml(action.value)}" aria-expanded="false" ${action.disabled ? 'disabled aria-disabled="true"' : ''}>
                                    <i class="${action.icon} mr-50 action-main-icon"></i>${escapeHtml(action.text)} <i class="fas fa-chevron-down ml-50 action-toggle-icon"></i>
                                </button>
                                ${canEditNominal(stage) ? `
                                    <button type="button" class="btn btn-outline-secondary btn-edit-nominal" data-index="${idx}" aria-expanded="false">
                                        <i class="fas fa-pen mr-50"></i>Edit Nominal
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-outline-primary btn-select-stage" data-index="${idx}" aria-expanded="false">
                                    <i class="fas fa-eye mr-50 detail-icon"></i><span class="detail-text">Lihat Detail</span> <i class="fas fa-chevron-down ml-50 toggle-icon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="stage-timeline-wrap d-none" id="stage_timeline_${idx}">
                            ${renderTimeline(stage, idx)}
                        </div>
                    </div>
                `;
            });
        }

        $('#stages_list_container').html(stagesHtml);
        $('#summary_potensi').text(formatCurrency(sumPotensi));
        $('#summary_cair').text(formatCurrency(sumKeuangan(currentStages)));
        $('#summary_sisa').text(formatCurrency(Math.max(sumPotensi - sumKeuangan(currentStages), 0)));
        renderAllHistory(currentStages);
    }

    function showStageTimeline(index) {
        const timeline = $(`#stage_timeline_${index}`);
        const button = $(`.btn-select-stage[data-index="${index}"]`);
        const isOpen = timeline.is(':visible') && !timeline.hasClass('d-none');

        $('.stage-timeline-wrap:visible').not(timeline).slideUp(180, function() {
            $(this).addClass('d-none');
        });
        $('.btn-select-stage').not(button).removeClass('is-active').attr('aria-expanded', 'false')
            .find('.detail-icon').removeClass('fa-eye-slash').addClass('fa-eye');
        $('.btn-select-stage').not(button).find('.detail-text').text('Lihat Detail');
        $('.btn-select-stage').not(button).find('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');

        if (!timeline.length) return;

        if (isOpen) {
            timeline.slideUp(180, function() {
                $(this).addClass('d-none');
            });
            button.removeClass('is-active').attr('aria-expanded', 'false');
            button.find('.detail-icon').removeClass('fa-eye-slash').addClass('fa-eye');
            button.find('.detail-text').text('Lihat Detail');
            button.find('.toggle-icon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            return;
        }

        timeline.removeClass('d-none').hide().slideDown(180);
        button.addClass('is-active').attr('aria-expanded', 'true');
        button.find('.detail-icon').removeClass('fa-eye').addClass('fa-eye-slash');
        button.find('.detail-text').text('Tutup Detail');
        button.find('.toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }

    window.selectStage = function(index, preferredAction, mode = 'detail') {
        if (!currentStages || !currentStages[index]) return;
        selectedStage = currentStages[index];
        $('#form_id_bonus').val(selectedStage.id_bonus || '');

        if (mode === 'detail') {
            showSummaryTab();
            showStageTimeline(index);
            return;
        }

        const action = preferredAction || primaryAction(selectedStage).value;
        if (!action) {
            hideSection('#form_section');
            return;
        }

        const sameActionVisible = selectedAction === action && $('#form_section').is(':visible') && $('.form-pengajuan-box').is(':visible');
        resetToggleButtons();
        if (sameActionVisible) {
            $('.form-pengajuan-box').slideUp(180, function() {
                hideSection('#form_section');
                resetActionForm();
            });
            return;
        }

        resetActionForm();
        selectedAction = action;
        $('#form_id_bonus').val(selectedStage.id_bonus || '');
        setActionType(action);
        showSummaryTab();
        showSection('#form_section');
        $('.form-pengajuan-box').hide().slideDown(180);

        const activeButton = action === 'update_nominal'
            ? $(`.btn-edit-nominal[data-index="${index}"]`)
            : $(`.btn-stage-primary-action[data-index="${index}"]`);
        activeButton.addClass('is-active').attr('aria-expanded', 'true');
        activeButton.find('.action-toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    };

    function refreshCurrentModal(idBonus = null) {
        if (!currentDetailContext) {
            dtMgm.ajax.reload(null, false);
            return;
        }

        $.ajax({
            url: base_url + 'api/mgm/subrows',
            type: 'POST',
            data: appendMgmFilters({ id_konsumen_referrer: currentDetailContext.idKonsumenReferrer, id_proyek: activeProyekId }),
            success: function(res) {
                if (!res.success) return;
                const groups = groupSubRows(res.data || []);
                const group = groups[currentDetailContext.idMkdt] || Object.values(groups)[0];
                currentStages = group ? group.stages : [];
                renderModalStages();
                resetActionForm();
                hideSection('#form_section');
                if (idBonus) {
                    const idx = currentStages.findIndex((stage) => String(stage.id_bonus) === String(idBonus));
                    if (idx >= 0) selectedStage = currentStages[idx];
                }
                dtMgm.ajax.reload(null, false);
            }
        });
    }

    async function compressImage(file) {
        if (!file || !String(file.type || '').startsWith('image/')) return file;

        return new Promise((resolve) => {
            const image = new Image();
            const reader = new FileReader();
            reader.onload = function(event) {
                image.onload = function() {
                    const maxSide = 1600;
                    const ratio = Math.min(1, maxSide / Math.max(image.width, image.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(image.width * ratio);
                    canvas.height = Math.round(image.height * ratio);
                    canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        const baseName = String(file.name || 'lampiran').replace(/\.[^.]+$/, '');
                        resolve(new File([blob], `${baseName}.jpg`, { type: 'image/jpeg' }));
                    }, 'image/jpeg', 0.78);
                };
                image.onerror = function() { resolve(file); };
                image.src = event.target.result;
            };
            reader.onerror = function() { resolve(file); };
            reader.readAsDataURL(file);
        });
    }

    function isAllowedUpload(file) {
        const name = String(file?.name || '').toLowerCase();
        const type = String(file?.type || '').toLowerCase();
        return type === 'application/pdf' || type.startsWith('image/') || /\.(pdf|jpe?g|png|webp)$/i.test(name);
    }

    async function setUploadFile(file) {
        if (!isAllowedUpload(file)) {
            Swal.fire('File tidak valid', 'Lampiran hanya boleh gambar atau PDF.', 'warning');
            return;
        }

        const finalFile = await compressImage(file);
        const data = new DataTransfer();
        data.items.add(finalFile);
        document.getElementById('form_bukti').files = data.files;
        $('#form_bukti_filename').text(`${finalFile.name} (${Math.ceil(finalFile.size / 1024)} KB)`);
    }

    window.submitFormActionDinamis = async function(e) {
        e.preventDefault();
        const actionType = $('#form_action_type').val();
        const idBonus = $('#form_id_bonus').val();
        if (!actionType || !idBonus) return;

        if (actionType === 'pay_promosi' && selectedStage && selectedStage.bonus_status === 'diajukan_keuangan' && !selectedStage.cair_keuangan_at) {
            const confirm = await Swal.fire({
                title: 'Dana belum cair',
                text: 'Dana belum cair, apakah kamu yakin akan mencairkan dana ke member?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            });
            if (!confirm.isConfirmed) return;
        }

        let url = base_url + 'api/mgm/';
        if (actionType === 'update_nominal') url += 'update-nominal';
        else if (actionType === 'pay_promosi') url += 'pay-promosi';
        else if (actionType === 'submit_keuangan') url += 'submit-keuangan';
        else if (actionType === 'mark_cair_keuangan') url += 'mark-cair-keuangan';
        else return;

        const formData = new FormData($('#formActionDinamis')[0]);
        formData.set('id_bonus', idBonus);

        const nominalStr = $('#form_nominal_pengajuan').val().replace(/[^0-9-]+/g, '');
        if (actionType === 'update_nominal') formData.set('nominal_bonus', nominalStr);
        if (actionType === 'submit_keuangan') formData.set('nominal_pengajuan', nominalStr);
        if (actionType === 'mark_cair_keuangan') {
            formData.set('nominal_cair_keuangan', nominalStr);
            formData.set('bank_pencairan', $('#form_bank_penerima').val());
            formData.set('no_rekening', $('#form_no_rekening_penerima').val());
        }
        if (formData.has('bukti') && formData.get('bukti') instanceof File && formData.get('bukti').size > 0) {
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
                Swal.fire('Berhasil', res.message || res.messages || 'Data tersimpan', 'success').then(() => {
                    refreshCurrentModal(idBonus);
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
        if (!$('#modalDetailPencairan').hasClass('show') || !$('#group_upload').is(':visible')) return;
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file') {
                const blob = item.getAsFile();
                const name = blob.type === 'application/pdf' ? 'lampiran.pdf' : 'lampiran-paste.png';
                setUploadFile(new File([blob], name, { type: blob.type }));
                break;
            }
        }
    });

    $(document).on('dragover dragenter', '#mgm_upload_dropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('is-dragover');
    });

    $(document).on('dragleave dragend drop', '#mgm_upload_dropzone', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('is-dragover');
    });

    $(document).on('drop', '#mgm_upload_dropzone', function(e) {
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) setUploadFile(file);
    });

    $(document).on('change', '#form_bukti', function() {
        if (this.files && this.files[0]) {
            setUploadFile(this.files[0]);
            return;
        }

        $('#form_bukti_filename').text('Foto/PDF');
    });

    $(document).on('click', '.btn-open-detail', function() {
        const nama = $(this).data('nama');
        const kavling = $(this).data('kavling');
        const referrer = $(this).data('referrer');
        currentDetailContext = {
            idKonsumenReferrer: $(this).data('referrer-id'),
            idMkdt: $(this).data('id-mkdt'),
            namaReferred: nama || '-'
        };
        currentStages = JSON.parse(decodeURIComponent($(this).data('stages')));

        $('#detail_nama_referrer').text(referrer || '-');
        $('#detail_nama_referred').text(nama || '-');
        $('#detail_kavling_referred').text(kavling ? `(${kavling})` : '');

        renderModalStages();
        resetActionForm();
        hideSection('#form_section');
        $('.stage-timeline-wrap').addClass('d-none').hide();
        showSummaryTab();

        if (typeof removeModalListener === 'function') {
            removeModalListener('#modalDetailPencairan');
        }

        $('#modalDetailPencairan').modal('show');

        if (typeof initModalListener === 'function') {
            initModalListener('#modalDetailPencairan');
        }
    });

    $(document).on('click', '.btn-select-stage', function() {
        selectStage(parseInt($(this).data('index'), 10), null, 'detail');
        this.blur();
    });

    $(document).on('click', '.btn-mgm-lampiran', function(e) {
        e.preventDefault();
        e.stopPropagation();
        showLampiranModal(parseInt($(this).data('index'), 10), $(this).data('type'));
    });

    $(document).on('click', '.btn-stage-primary-action', function() {
        if (this.disabled || $(this).hasClass('disabled')) {
            this.blur();
            return;
        }
        selectStage(parseInt($(this).data('index'), 10), $(this).data('action'), 'action');
        this.blur();
        const formTop = $('#form_section').offset()?.top;
        if (formTop && $('#form_section').is(':visible')) {
            $('#modalDetailPencairan .modal-body').animate({
                scrollTop: $('#modalDetailPencairan .modal-body').scrollTop() + formTop - $('#modalDetailPencairan .modal-body').offset().top - 16
            }, 180);
        }
    });

    $(document).on('click', '.btn-edit-nominal', function() {
        selectStage(parseInt($(this).data('index'), 10), 'update_nominal', 'action');
    });

    if ($.fn.select2) {
        $('#filter_mgm_referrer').select2({
            ajax: {
                url: base_url + 'api/mgm/search-options',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        id_proyek: activeProyekId
                    };
                },
                processResults: function(data) {
                    return data;
                }
            },
            allowClear: true,
            dropdownParent: $('#modalFilterMgm'),
            placeholder: $('#filter_mgm_referrer').data('placeholder') || 'Kode atau nama pemilik referal',
            width: '100%'
        });

        $('#filter_mgm_status').select2({
            allowClear: false,
            dropdownParent: $('#modalFilterMgm'),
            minimumResultsForSearch: Infinity,
            width: '100%'
        });
    }

    if (typeof flatpickr === 'function') {
        filterTanggalMulaiPicker = flatpickr(document.getElementById('filter_mgm_tanggal_mulai'), {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('mgm-filter-datepicker');
            }
        });
        filterTanggalSelesaiPicker = flatpickr(document.getElementById('filter_mgm_tanggal_selesai'), {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d',
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('mgm-filter-datepicker');
            }
        });
    }

    setDefaultFilterDates();

    $('#btn-filter-mgm').on('click', function() {
        $('#modalFilterMgm').modal('show');
    });

    $('#btn-apply-filter-mgm').on('click', function() {
        mgmFilters = collectMgmFilters();
        updateMgmFilterSummary();
        closeOpenSubRows();
        dtMgm.ajax.reload(null, false);
        $('#modalFilterMgm').modal('hide');
    });

    $('#btn-reset-filter-mgm').on('click', function() {
        $('#filter_mgm_referrer').val(null).trigger('change');
        $('#filter_mgm_status').val('').trigger('change');
        setDefaultFilterDates();
        mgmFilters = {};
        selectedFilterReferrerText = '';
        updateMgmFilterSummary();
        closeOpenSubRows();
        dtMgm.ajax.reload(null, false);
    });

    updateMgmFilterSummary();

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
