$(document).ready(function() {
    // Add initModalListener from scripts.js for confirmation
    if (typeof initModalListener === 'function') {
        initModalListener('#modal_penagihan');
    }

    // State tracking for editing draft
    window.isEditingDraftTagihan = false;
    window.currentTagihanFormData = null;

    // Toggle footer buttons based on active tab
    function syncModalFooter(targetHref) {
        if (targetHref === '#tab_buat_tagihan') {
            $('#footer-penagihan').removeClass('d-none').show();
            $('#footer-action-buat-tagihan').removeClass('d-none').show();
        } else {
            $('#footer-penagihan').addClass('d-none').hide();
            $('#footer-action-buat-tagihan').addClass('d-none').hide();
        }
    }

    // Fully clear and reset modal penagihan content
    window.clearModalPenagihan = function() {
        window.isEditingDraftTagihan = false;
        window.currentTagihanFormData = null;

        // Reset form controls
        if ($('#form-buat-tagihan').length) {
            $('#form-buat-tagihan')[0].reset();
        }

        // Reset hidden fields
        $('#tagihan_id_mkdt').val('');
        $('#tagihan_id_kavling').val('');
        $('#tagihan_id_konsumen').val('');
        $('#tagihan_no_inv').val('');
        $('#form_submit_status').val('draft');

        // Reset input fields
        $('#tagihan_kopsurat').html('<option value="">-- Pilih Kop Surat --</option>');
        $('#tagihan_nomor_surat').val('');
        $('#tagihan_nominal_ditagihkan').val('');

        const defaultSnk = '<ol><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda&nbsp;</span></li><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Pembayaran yang sah hanya melalui transfer ke rekening atas nama <br><b>PT. Sanggarindah Karya Sentosa</b> <b>Raya</b> BCA KC Setiabudi - Bandung, Nomor Rekening :<b>2337 887 887</b>&nbsp;</span></li><li>Konfirmasi pembayaran ke bagian keuangan kami dan lampirkan bukti transfer.</li></ol>';
        $('#tagihan_snk').val(defaultSnk);

        // Reset dates
        let today = new Date();
        let h7 = new Date();
        h7.setDate(h7.getDate() + 7);
        $('#tagihan_tanggal').val(today.toISOString().split('T')[0]);
        $('#tagihan_jatuh_tempo').val(h7.toISOString().split('T')[0]);

        // Reset items table & totals
        $('#tb-tagihan-items-here').html('<tr><td colspan="3" class="text-center py-2 text-muted"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat daftar tagihan...</td></tr>');
        $('#tagihan-total-nominal').text('Rp 0');
        $('#tagihan-total-bayar').text('Rp 0');
        $('#tagihan-total-sisa').text('Rp 0');

        // Reset top header card
        $('#tagihan_avatar_initial').text('-');
        $('#tagihan_header_konsumen').text('Memuat...');
        $('#tagihan_header_kavling').text('-');
        $('#tagihan_header_sisa').text('Rp 0');

        // Reset Daftar Surat (riwayat table)
        $('#list_riwayat_tagihan-here').html('<tr><td colspan="4" class="text-center py-2"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat riwayat...</td></tr>');

        // Reset right pane detail card
        $('#riwayat_detail_empty').show().removeClass('d-none');
        $('#riwayat_detail_content').hide().addClass('d-none');
        $('#dtl_no_inv').text('');
        $('#dtl_status_badge').empty();
        $('#dtl_nominal_tagihan').text('Rp 0');
        $('#dtl_tgl_terbit').text('-');
        $('#dtl_jatuh_tempo').text('-');
        $('#dtl_ttd_direksi').html('-');
        $('#dtl_dibuat_oleh').text('-');
        $('#dtl_actions').empty();
        $('#dtl_riwayat_surat').empty();
    };

    // Clear modal on hidden
    $('#modal_penagihan').on('hidden.bs.modal', function() {
        clearModalPenagihan();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let href = $(e.target).attr('href');
        syncModalFooter(href);
        if (href === '#tab_buat_tagihan' && !window.isEditingDraftTagihan) {
            $('#tagihan_no_inv').val('');
        }
    });

    // Button "+ Buat Surat" inside Daftar Surat tab
    $(document).on('click', '#btn-pindah-buat-surat', function(e) {
        e.preventDefault();
        window.isEditingDraftTagihan = false;
        $('#tagihan_no_inv').val('');
        if (window.currentTagihanFormData) {
            renderFormTagihanData(window.currentTagihanFormData);
        }
        $('#tab_buat_tagihan-tab').tab('show');
    });

    window.openModalPenagihan = function(rowData, targetTab = 'tab_riwayat_tagihan') {
        if (!rowData) return;
        let id_mkdt = rowData.id_mkdt;

        // 1. Kosongkan seluruh konten sisa sebelumnya
        clearModalPenagihan();

        // 2. Set ID konteks konsumen / kavling baru
        $('#tagihan_id_mkdt').val(id_mkdt);
        $('#tagihan_id_kavling').val(rowData.id_kavling || '');
        $('#tagihan_id_konsumen').val(rowData.id_konsumen || '');

        $('#tagihan_detail_konsumen').html(rowData.nama_konsumen || '-');
        $('#tagihan_detail_kavling').html((rowData.nama_jalan || '-') + ' - No. ' + (rowData.no_kavling || '-'));

        // 3. Populate header info
        let nama = rowData.nama_konsumen || '-';
        let initial = nama.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        $('#tagihan_avatar_initial').text(initial || '-');
        $('#tagihan_header_konsumen').text(nama);

        let tipe = rowData.tipe_pricelist || '';
        let kpr = rowData.is_kpr == 1 || rowData.is_kpr == '1' || rowData.is_kpr === 'KPR' || rowData.is_kpr === true ? 'KPR' : 'TUNAI';
        $('#tagihan_header_kavling').text(`${rowData.nama_jalan || '-'} - NO. ${rowData.no_kavling || '-'} - TYPE ${tipe} - ${kpr}`);

        let sisa = rowData.sisa_tagihan_raw || rowData.sisa_tagihan || 0;
        if (typeof sisa === 'string' && sisa.includes('<')) {
            let stripped = sisa.replace(/(<([^>]+)>)/gi, "");
            sisa = stripped.replace(/[^\d]/g, '');
        }
        $('#tagihan_header_sisa').text('Rp ' + num_format(sisa));

        // 4. Load data riwayat & data form
        loadRiwayatTagihan(id_mkdt);
        loadDataFormTagihan(id_mkdt, rowData.id_kavling, rowData.id_keuangan);

        let activeTab = targetTab || 'tab_riwayat_tagihan';
        $(`#${activeTab}-tab`).tab('show');
        syncModalFooter('#' + activeTab);

        $('#modal_penagihan').modal('show');
    };

    // Populate data when button clicked
    $(document).on('click', '.tagihan-penagihan-btn', function() {
        let id_mkdt = $(this).data('id');
        let tr = $(this).closest('tr');
        let rowData = tr.length ? listTagihanTable.row(tr).data() : null;
        if (!rowData) {
            let rowAttr = $(this).data('row');
            if (typeof rowAttr === 'string') {
                try { rowData = JSON.parse(rowAttr); } catch (e) {}
            } else if (typeof rowAttr === 'object') {
                rowData = rowAttr;
            }
        }
        if (!rowData && window.currentTagihanRow) {
            rowData = window.currentTagihanRow;
        }
        if (rowData) {
            window.openModalPenagihan(rowData, 'tab_riwayat_tagihan');
        }
    });
    
    window.selectRiwayatRow = function(tr) {
        $('.riwayat-row').removeClass('selected').css('background-color', '');
        $(tr).addClass('selected').css('background-color', '#e8f0fe');
        
        let raw = $(tr).data('row');
        if (!raw) return;
        
        let v = typeof raw === 'object' ? raw : JSON.parse(raw);
        
        $('#riwayat_detail_empty').hide();
        $('#riwayat_detail_content').show().removeClass('d-none');
        
        let st = (v.status_tagihan || '').toLowerCase();
        let nomorTampilDtl = (st === 'draft') ? v.no_inv : (v.nomor_surat || v.no_inv);
        $('#dtl_no_inv').text(nomorTampilDtl);

        // Hitung nominal tagihan surat
        let totalNominalSurat = 0;
        try {
            let parsedTagihan = typeof v.tagihan === 'string' ? JSON.parse(v.tagihan) : v.tagihan;
            if (Array.isArray(parsedTagihan)) {
                parsedTagihan.forEach(item => {
                    totalNominalSurat += parseFloat(item.nominal || 0);
                });
            }
        } catch(e) {}
        $('#dtl_nominal_tagihan').text('Rp ' + num_format(totalNominalSurat));

        $('#dtl_tgl_terbit').text(format_date((v.tanggal_invoice || '').split(' ')[0]));
        $('#dtl_jatuh_tempo').text(format_date((v.tanggal_jatuh_tempo || '').split(' ')[0]));

        // Status TTD Direksi
        let isSignedDtl = parseInt(v.is_signed_direktur || 0) === 1;
        let ttdDtlText = isSignedDtl
            ? `<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-25"></i> Sudah ${v.signed_at ? '(' + format_date(v.signed_at.split(' ')[0]) + ')' : ''}</span>`
            : `<span class="text-secondary font-weight-bold"><i class="fas fa-clock mr-25"></i> Belum</span>`;
        $('#dtl_ttd_direksi').html(ttdDtlText);

        $('#dtl_dibuat_oleh').text(v.pembuat || '-');
        
        let statusBadge = '';
        if (st === 'draft') statusBadge = '<span class="badge badge-secondary font-weight-bold">DRAFT</span>';
        else if (st === 'publish') statusBadge = '<span class="badge badge-primary font-weight-bold">PUBLISH</span>';
        else if (st === 'dibuat') statusBadge = '<span class="badge badge-secondary font-weight-bold">DIBUAT</span>';
        else if (st === 'dikirim') statusBadge = '<span class="badge badge-info font-weight-bold">DIKIRIM</span>';
        else if (st === 'respon') statusBadge = '<span class="badge badge-success font-weight-bold">RESPON</span>';
        else if (st === 'tidak respon') statusBadge = '<span class="badge badge-danger font-weight-bold">TIDAK RESPON</span>';
        else if (st === 'batal') statusBadge = '<span class="badge badge-dark font-weight-bold">BATAL</span>';
        else statusBadge = `<span class="badge badge-light-primary font-weight-bold text-uppercase">${v.status_tagihan}</span>`;
        $('#dtl_status_badge').html(statusBadge);
        
        let actionsHtml = '';
        if (st === 'draft') {
            actionsHtml += `<button type="button" class="btn btn-sm btn-outline-warning mr-50 btn-edit-tagihan" data-inv='${JSON.stringify(v).replace(/'/g, "&#39;")}'>Edit Surat</button>`;
        }
        
        actionsHtml += `
            <a href="${base_url}keuangan/download_penagihan?id=${encodeURIComponent(v.no_inv)}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download mr-50"></i> Download</a>
            <button type="button" class="btn btn-sm btn-outline-info ml-50 btn-ubah-status-tagihan" data-no="${v.no_inv}" data-status="${v.status_tagihan}" data-tgl="${v.tanggal_ubah_status || ''}" data-ket="${v.keterangan_status || ''}" data-nomorsurat="${v.nomor_surat || ''}">Ubah Status</button>
        `;
        $('#dtl_actions').html(actionsHtml);
        
        // Build all history entries
        let timelineList = [];
        if (Array.isArray(v.lifecycle) && v.lifecycle.length > 0) {
            timelineList = [...v.lifecycle];
        } else {
            timelineList.push({
                status: 'dibuat',
                tanggal: v.tanggal_invoice,
                date_add: v.date_add,
                pembuat: v.pembuat,
                keterangan: 'Surat penagihan berhasil dibuat.'
            });
            if (st && st !== 'dibuat') {
                timelineList.push({
                    status: v.status_tagihan,
                    tanggal: v.tanggal_ubah_status || v.date_edit || v.tanggal_invoice,
                    date_add: v.date_edit || v.date_add,
                    pembuat: v.pembuat,
                    keterangan: v.keterangan_status || 'Status surat diperbarui.'
                });
            }
        }
        
        // Urutkan aktivitas terbaru paling atas
        timelineList.sort(function(a, b) {
            let timeA = new Date(a.date_add || a.tanggal || 0).getTime();
            let timeB = new Date(b.date_add || b.tanggal || 0).getTime();
            if (timeB !== timeA) return timeB - timeA;
            return (parseInt(b.id) || 0) - (parseInt(a.id) || 0);
        });
        
        let timelineHtml = '';
        $.each(timelineList, function(idx, log) {
            let logStatus = (log.status || '').toLowerCase();
            let pointColor = 'timeline-point-primary';
            let titleText = 'Surat dibuat';
            
            if (logStatus === 'dibuat') {
                pointColor = 'timeline-point-primary';
                titleText = 'Surat dibuat';
            } else if (logStatus === 'dikirim') {
                pointColor = 'timeline-point-info';
                titleText = 'Surat dikirim';
            } else if (logStatus === 'respon') {
                pointColor = 'timeline-point-success';
                titleText = 'Surat direspon';
            } else if (logStatus === 'tidak respon') {
                pointColor = 'timeline-point-danger';
                titleText = 'Tidak ada respon';
            } else {
                pointColor = 'timeline-point-secondary';
                titleText = 'Status: ' + (log.status || '-');
            }
            
            let dateOnly = (log.tanggal || log.date_add || '').split(' ')[0];
            let timeStr = format_date(dateOnly);
            if (log.date_add && log.date_add.indexOf(' ') !== -1) {
                let timePart = log.date_add.split(' ')[1];
                if (timePart) {
                    timeStr += ' - ' + timePart.substring(0, 5);
                }
            }
            if (log.pembuat) {
                timeStr += ' - ' + log.pembuat;
            }
            
            let ketText = log.keterangan || (logStatus === 'dibuat' ? 'Surat penagihan berhasil dibuat.' : '-');
            let safeKet = $('<div>').text(ketText).html();
            
            timelineHtml += `
                <li class="timeline-item">
                    <span class="timeline-point timeline-point-indicator ${pointColor}"></span>
                    <div class="timeline-event">
                        <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-25">
                            <h6 class="font-weight-bolder text-dark mb-0">${titleText}</h6>
                        </div>
                        <span class="timeline-event-time small text-muted d-block mb-50">${timeStr}</span>
                        <div class="card shadow-none border bg-white mb-0">
                            <div class="card-body p-75 small text-dark">
                                ${safeKet}
                            </div>
                        </div>
                    </div>
                </li>
            `;
        });
        
        $('#dtl_riwayat_surat').html(timelineHtml);
    };

    function loadRiwayatTagihan(id_mkdt, autoSelectNoInv = null) {
        let selectedInv = autoSelectNoInv || $('.riwayat-row.selected').data('no-inv');
        $('#list_riwayat_tagihan-here').html('<tr><td colspan="4" class="text-center">Memuat riwayat...</td></tr>');
        $.ajax({
            url: base_url + "keuangan/get_riwayat_tagihan",
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_mkdt: id_mkdt
            },
            success: function(r) {
                csrfHash = r.token;
                if (r.success) {
                    let html = '';
                    if (r.data.length > 0) {
                        $.each(r.data, function(i, v) {
                            let statusBadge = '';
                            let stLower = (v.status_tagihan || '').toLowerCase();
                            if (stLower === 'draft') statusBadge = '<span class="badge badge-light-secondary font-weight-bold">DRAFT</span>';
                            else if (stLower === 'publish') statusBadge = '<span class="badge badge-light-primary font-weight-bold">PUBLISH</span>';
                            else if (stLower === 'dibuat') statusBadge = '<span class="badge badge-light-secondary font-weight-bold">DIBUAT</span>';
                            else if (stLower === 'dikirim') statusBadge = '<span class="badge badge-light-info font-weight-bold">DIKIRIM</span>';
                            else if (stLower === 'respon') statusBadge = '<span class="badge badge-light-success font-weight-bold">RESPON</span>';
                            else if (stLower === 'tidak respon') statusBadge = '<span class="badge badge-light-danger font-weight-bold">TIDAK RESPON</span>';
                            else if (stLower === 'batal') statusBadge = '<span class="badge badge-light-dark font-weight-bold">BATAL</span>';
                            else statusBadge = `<span class="badge badge-light-primary font-weight-bold text-uppercase">${v.status_tagihan}</span>`;
                            
                            let rowDataJson = JSON.stringify(v).replace(/"/g, '&quot;');
                            let nomorTampil = (stLower === 'draft') ? v.no_inv : (v.nomor_surat || v.no_inv);
                            let noSuratHtml = `<div class="font-weight-bolder text-dark">${nomorTampil}</div><div class="small text-muted mt-25 text-uppercase">${v.pembuat || '-'}</div>`;
                            let tglTerbitHtml = `<div class="text-dark font-weight-bold">${format_date((v.tanggal_invoice || '').split(' ')[0])}</div>`;

                            let isSigned = parseInt(v.is_signed_direktur || 0) === 1;
                            let ttdIcon = isSigned 
                                ? `<i class="fas fa-check-circle text-success" style="font-size: 1.15rem;" data-toggle="tooltip" data-placement="top" title="${v.signed_at ? 'Sudah TTD Direksi (' + format_date(v.signed_at.split(' ')[0]) + ')' : 'Sudah TTD Direksi'}"></i>`
                                : `<i class="fas fa-clock text-muted" style="font-size: 1.15rem;" data-toggle="tooltip" data-placement="top" title="Belum TTD Direksi"></i>`;

                            let updateTgl = v.tanggal_ubah_status ? format_date(v.tanggal_ubah_status.split(' ')[0]) : (v.date_add ? format_date(v.date_add.split(' ')[0]) : '-');
                            let rawKet = (v.keterangan_status || '').trim();
                            let ketSnippet = '';
                            if (rawKet) {
                                let truncated = rawKet.length > 35 ? rawKet.substring(0, 35) + '...' : rawKet;
                                ketSnippet = `<div class="small text-muted mt-25" data-toggle="tooltip" data-placement="top" title="${rawKet.replace(/"/g, '&quot;')}">${truncated}</div>`;
                            }
                            let updateHtml = `<div>${statusBadge} <small class="text-muted font-weight-bold ml-25">${updateTgl}</small></div>${ketSnippet}`;
                            
                            html += `<tr data-row="${rowDataJson}" data-no-inv="${v.no_inv}" class="riwayat-row" onclick="selectRiwayatRow(this)">
                                <td>${noSuratHtml}</td>
                                <td>${tglTerbitHtml}</td>
                                <td class="text-center align-middle">${ttdIcon}</td>
                                <td>${updateHtml}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">Belum ada riwayat tagihan.</td></tr>';
                    }
                    $('#list_riwayat_tagihan-here').html(html);
                    $('#list_riwayat_tagihan-here [data-toggle="tooltip"]').tooltip();
                    
                    // Re-select row if previously selected or explicitly requested
                    let reselected = false;
                    if (selectedInv) {
                        let $targetRow = $(`#list_riwayat_tagihan-here tr[data-no-inv="${selectedInv}"]`);
                        if ($targetRow.length) {
                            selectRiwayatRow($targetRow[0]);
                            reselected = true;
                        }
                    }
                    if (! reselected) {
                        $('#riwayat_detail_empty').show();
                        $('#riwayat_detail_content').hide().addClass('d-none');
                    }
                }
            }
        });
    }
    
    function renderFormTagihanData(r) {
        if (!r) return;

        // Load Kop Surat
        let kopHtml = '<option value="">-- Pilih Kop Surat --</option>';
        if (r.kop_surat && r.kop_surat.length > 0) {
            $.each(r.kop_surat, function(i, v) {
                kopHtml += `<option value="${v.id}">${v.nama} (${v.ukuran})</option>`;
            });
        }
        $('#tagihan_kopsurat').html(kopHtml);

        // Suggest Nomor Surat
        let kode_keu = r.kode_keuangan ? r.kode_keuangan : 'XXX';
        let romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        let today = new Date();
        let month = romanMonths[today.getMonth()];
        let year = today.getFullYear();
        let suggestedNomor = `/KEU-${kode_keu}/EX/PRS/DIR/${month}/${year}`;
        $('#tagihan_nomor_surat').val(suggestedNomor);
        
        // Load Items
        let itemsHtml = '';
        let totalTagihan = 0;
        let totalSudahBayar = 0;
        let closestDate = null;
        
        if (r.list_tagihan && r.list_tagihan.length > 0) {
            $.each(r.list_tagihan, function(i, a) {
                let nominal = parseInt(a.nominal) || 0;
                let isPaid = parseInt(a.sudah_dibayar || 0) === 1;
                let isVoid = parseInt(a.is_void || 0) === 1;
                
                if (!isVoid) {
                    totalTagihan += nominal;
                    if (isPaid) {
                        totalSudahBayar += nominal;
                    } else {
                        // Find closest unpaid due date
                        if (a.jatuh_tempo_tgl) {
                            let jtDate = new Date(a.jatuh_tempo_tgl);
                            if (!closestDate || jtDate < closestDate) {
                                closestDate = jtDate;
                            }
                        }
                    }
                }
                
                let statusIcon = isVoid 
                    ? `<i class="fas fa-ban text-secondary mr-50" data-toggle="tooltip" data-placement="top" title="Void"></i>`
                    : (isPaid 
                        ? `<i class="fas fa-check-circle text-success mr-50" data-toggle="tooltip" data-placement="top" title="Lunas"></i>` 
                        : `<i class="fas fa-clock text-warning mr-50" data-toggle="tooltip" data-placement="top" title="Belum Lunas"></i>`
                    );
                
                itemsHtml += `
                  <tr${isVoid ? ' class="text-muted"' : ""}>
                      <td class="align-middle">${a.berita_acara}</td>
                      <td class="text-center align-middle">${format_date(a.jatuh_tempo_tgl)}</td>
                      <td class="text-right align-middle text-nowrap">${statusIcon}Rp ${num_format(nominal)}</td>
                  </tr>
                `;
            });
        } else {
            itemsHtml = '<tr><td colspan="3" class="text-center py-2 text-muted">Belum ada daftar tagihan.</td></tr>';
        }
        
        let sisaTagihan = totalTagihan - totalSudahBayar;
        if (sisaTagihan < 0) sisaTagihan = 0;
        
        $('#tb-tagihan-items-here').html(itemsHtml);
        $('#tb-tagihan-items-here [data-toggle="tooltip"]').tooltip();
        $('#tagihan-total-nominal').text('Rp ' + num_format(totalTagihan));
        $('#tagihan-total-bayar').text('Rp ' + num_format(totalSudahBayar));
        $('#tagihan-total-sisa').text('Rp ' + num_format(sisaTagihan));

        // Set default Nominal Ditagihkan and Tanggal Jatuh Tempo
        let formatSisa = num_format(sisaTagihan);
        $('#tagihan_nominal_ditagihkan').val(formatSisa);
        
        // Formatting input for nominal
        $('#tagihan_nominal_ditagihkan').off('input').on('input', function() {
            let val = $(this).val().replace(/[^0-9]/g, '');
            if (val === '') val = '0';
            $(this).val(num_format(parseInt(val, 10)));
        });

        if (closestDate) {
            let yy = closestDate.getFullYear();
            let mm = String(closestDate.getMonth() + 1).padStart(2, '0');
            let dd = String(closestDate.getDate()).padStart(2, '0');
            $('#tagihan_jatuh_tempo').val(`${yy}-${mm}-${dd}`);
        } else {
            // Default to today if no unpaid items found
            let todayStr = new Date().toISOString().split('T')[0];
            $('#tagihan_jatuh_tempo').val(todayStr);
        }
        
        // Default tanggal surat is today
        $('#tagihan_tanggal').val(new Date().toISOString().split('T')[0]);
    }

    function loadDataFormTagihan(id_mkdt, id_kavling, id_keuangan) {
        $.ajax({
            url: base_url + "keuangan/get_tagihan/inv",
            type: "post",
            data: {
              [csrfName]: csrfHash,
              id_keuangan: id_keuangan,
              id_kavling: id_kavling,
              id_mkdt: id_mkdt,
            },
            dataType: "json",
            success: function (r) {
              csrfHash = r.token;
              window.currentTagihanFormData = r;
              if (!window.isEditingDraftTagihan) {
                  renderFormTagihanData(r);
              }
            },
            error: function() {
              $('#tb-tagihan-items-here').html('<tr><td colspan="3" class="text-center py-2 text-danger">Gagal memuat data tagihan.</td></tr>');
            }
        });
    }
    
    $('#form-buat-tagihan').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serializeArray();
        
        // build json tagihan (single item for the summary invoice)
        let nominalRaw = $('#tagihan_nominal_ditagihkan').val().replace(/[^0-9]/g, '');
        let nominalVal = parseInt(nominalRaw, 10) || 0;

        let tagihanArray = [];
        if (nominalVal > 0) {
            tagihanArray.push({
                berita_acara: "Pembayaran Tagihan",
                jatuh_tempo_tgl: $('#tagihan_jatuh_tempo').val(),
                nominal: nominalVal
            });
        }
        
        // 1. Validasi Nominal Tagihan
        if (tagihanArray.length === 0 || nominalVal <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Nominal Kosong',
                text: 'Silakan isi nominal yang akan ditagihkan (lebih dari 0).'
            });
            $('#tagihan_nominal_ditagihkan').focus();
            return;
        }

        // 2. Validasi Kop Surat
        let id_kopsurat = $('#tagihan_kopsurat').val();
        if (!id_kopsurat || id_kopsurat === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Kop Surat Belum Dipilih',
                text: 'Silakan pilih Kop Surat terlebih dahulu.'
            });
            $('#tagihan_kopsurat').focus();
            return;
        }

        // 3. Validasi Nomor Surat
        let nomor_surat = $('#tagihan_nomor_surat').val();
        if (!nomor_surat || nomor_surat.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Nomor Surat Kosong',
                text: 'Silakan masukkan nomor surat.'
            });
            $('#tagihan_nomor_surat').focus();
            return;
        }
        
        let submitData = {
            [csrfName]: csrfHash,
            no_inv: $('#tagihan_no_inv').val() || '',
            status_tagihan: $('#form_submit_status').val(),
            id_mkdt: $('#tagihan_id_mkdt').val(),
            id_kavling: $('#tagihan_id_kavling').val(),
            id_konsumen: $('#tagihan_id_konsumen').val(),
            id_kopsurat: id_kopsurat,
            nomor_surat: nomor_surat,
            tanggal_invoice: $('#tagihan_tanggal').val(),
            tanggal_jatuh_tempo: $('#tagihan_jatuh_tempo').val(),
            terms: $('#tagihan_snk').val(),
            tagihan: JSON.stringify(tagihanArray)
        };
        
        let draftBtn = $('#btn-simpan-draft');
        let publishBtn = $('#btn-simpan-publish');
        draftBtn.prop('disabled', true);
        publishBtn.prop('disabled', true);
        
        $.ajax({
            url: base_url + "keuangan/simpan_penagihan",
            type: "POST",
            dataType: "json",
            data: submitData,
            success: function(r) {
                draftBtn.prop('disabled', false);
                publishBtn.prop('disabled', false);
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    Swal.fire('Berhasil', r.messages, 'success');
                    window.isEditingDraftTagihan = false;
                    $('#form-buat-tagihan')[0].reset();
                    $('#tagihan_no_inv').val('');
        
                    // Setup Date
                    let today = new Date();
                    let h7 = new Date();
                    h7.setDate(h7.getDate() + 7);
                    $('#tagihan_tanggal').val(today.toISOString().split('T')[0]);
                    $('#tagihan_jatuh_tempo').val(h7.toISOString().split('T')[0]);
                    
                    if (window.currentTagihanFormData) {
                        renderFormTagihanData(window.currentTagihanFormData);
                    }

                    $('#tab_riwayat_tagihan-tab').tab('show');
                    loadRiwayatTagihan(submitData.id_mkdt);
                } else {
                    Swal.fire('Gagal', r.messages, 'error');
                }
            },
            error: function() {
                draftBtn.prop('disabled', false);
                publishBtn.prop('disabled', false);
                Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
            }
        });
    });
    
    // Edit Tagihan (Draft)
    $(document).on('click', '.btn-edit-tagihan', function() {
        let v = $(this).data('inv');
        window.isEditingDraftTagihan = true;
        // populate form
        $('#tagihan_no_inv').val(v.no_inv);
        
        $('#tagihan_kopsurat').val(v.id_kopsurat);
        $('#tagihan_nomor_surat').val(v.nomor_surat);
        $('#tagihan_tanggal').val((v.tanggal_invoice || '').split(' ')[0]);
        $('#tagihan_jatuh_tempo').val((v.tanggal_jatuh_tempo || '').split(' ')[0]);
        
        if (v.terms) {
            $('#tagihan_snk').val(v.terms);
        }
        
        try {
            let parsedTagihan = typeof v.tagihan === 'string' ? JSON.parse(v.tagihan) : v.tagihan;
            if (Array.isArray(parsedTagihan) && parsedTagihan.length > 0) {
                let nominal = parsedTagihan[0].nominal;
                $('#tagihan_nominal_ditagihkan').val(num_format(nominal));
            }
        } catch(e) {}
        
        // switch tab back to buat tagihan
        $('#tab_buat_tagihan-tab').tab('show');
    });

    $(document).on('click', '.btn-ubah-status-tagihan', function() {
        let no_inv = $(this).data('no');
        let status = ($(this).data('status') || '').toLowerCase();
        let tgl = $(this).data('tgl');
        let ket = $(this).data('ket');
        let nomorsurat = $(this).data('nomorsurat');
        
        $('#us_no_inv').val(no_inv);
        $('#us_current_status').val(status);
        
        let selectHtml = '';
        if (status === 'draft') {
            selectHtml = `
                <option value="publish">Publish</option>
                <option value="batal">Batal</option>
            `;
            $('#us_status_tagihan').html(selectHtml);
            $('#us_status_tagihan').val('publish').trigger('change');
        } else {
            selectHtml = `
                <option value="dikirim">Dikirim</option>
                <option value="respon">Respon</option>
                <option value="tidak respon">Tidak Respon</option>
            `;
            $('#us_status_tagihan').html(selectHtml);
            $('#us_status_tagihan').val(status);
        }

        if (tgl) {
            // keep only date part for input type=date
            $('#us_tanggal_ubah_status').val(tgl.split(' ')[0]);
        } else {
            // default to today
            let today = new Date();
            let dd = String(today.getDate()).padStart(2, '0');
            let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
            let yyyy = today.getFullYear();
            $('#us_tanggal_ubah_status').val(yyyy + '-' + mm + '-' + dd);
        }
        $('#us_keterangan_status').val(ket);
        $('#us_nomor_surat').val(nomorsurat || '');
        
        $('#modal_ubah_status_tagihan').modal('show');
    });

    $('#us_status_tagihan').on('change', function() {
        let val = $(this).val();
        let cur = $('#us_current_status').val();
        if (cur === 'draft' && val === 'publish') {
            $('#us_group_nomor_surat').removeClass('d-none');
            $('#us_nomor_surat').attr('required', true);
        } else {
            $('#us_group_nomor_surat').addClass('d-none');
            $('#us_nomor_surat').removeAttr('required');
        }
    });
    
    $('#form-ubah-status-tagihan').on('submit', function(e) {
        e.preventDefault();
        
        let submitData = {
            [csrfName]: csrfHash,
            no_inv: $('#us_no_inv').val(),
            status_tagihan: $('#us_status_tagihan').val(),
            tanggal_ubah_status: $('#us_tanggal_ubah_status').val(),
            keterangan_status: $('#us_keterangan_status').val(),
            nomor_surat: $('#us_nomor_surat').val()
        };
        
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: base_url + "keuangan/update_status_penagihan",
            type: "POST",
            dataType: "json",
            data: submitData,
            success: function(r) {
                btn.prop('disabled', false).html('Simpan Status');
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    $('#modal_ubah_status_tagihan').modal('hide');
                    loadRiwayatTagihan($('#tagihan_id_mkdt').val(), submitData.no_inv);
                } else {
                    Swal.fire('Gagal', r.messages, 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('Simpan Status');
                Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
            }
        });
    });
    
    // Download Tagihan
    $(document).on('click', '.btn-download-tagihan', function() {
        // reload riwayat after 2 seconds so the "dikirim" date might show up if we want to reflect it
        let id_mkdt = $('#tagihan_id_mkdt').val();
        setTimeout(function() {
            loadRiwayatTagihan(id_mkdt);
        }, 3000);
    });

    function refreshKopSuratSelect(selectedId = null) {
        $.ajax({
            url: base_url + "keuangan/get_kopsurat_list",
            type: "GET",
            dataType: "json",
            success: function(r) {
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    let curVal = selectedId || $('#tagihan_kopsurat').val();
                    let kopHtml = '<option value="">-- Pilih Kop Surat --</option>';
                    $.each(r.data, function(i, v) {
                        let isSel = (curVal && curVal == v.id) ? 'selected' : '';
                        kopHtml += `<option value="${v.id}" ${isSel}>${v.nama} (${v.ukuran})</option>`;
                    });
                    $('#tagihan_kopsurat').html(kopHtml);
                }
            }
        });
    }

    // ==========================================
    // CRUD KOP SURAT
    // ==========================================
    
    // Open Kelola Kop Surat Modal
    $(document).on('click', '#btn-modal-kelola-kopsurat', function() {
        $('#card-form-kopsurat').addClass('d-none');
        $('#modal_kelola_kopsurat').modal('show');
        loadKopSuratTable();
    });

    // Auto set dimensions when size changes
    $('#kop_ukuran').on('change', function() {
        let uk = $(this).val();
        if (uk === 'F4') {
            $('#kop_w').val('21.5cm');
            $('#kop_h').val('33cm');
        } else {
            $('#kop_w').val('21cm');
            $('#kop_h').val('29.7cm');
        }
    });

    // File input preview and label update
    $('#kop_file').on('change', function() {
        let file = this.files[0];
        if (file) {
            $('#kop_file_label').text(file.name);
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#kop_preview_img').attr('src', e.target.result);
                $('#kop_preview_wrap').removeClass('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            $('#kop_file_label').text('Pilih gambar background (PNG / JPG max 2MB)...');
            $('#kop_preview_wrap').addClass('d-none');
        }
    });

    // Show form tambah
    $('#btn-tambah-kopsurat').on('click', function() {
        $('#card-form-kopsurat-title').text('Tambah Kop Surat');
        $('#form-kopsurat')[0].reset();
        $('#kop_id').val('');
        $('#kop_w').val('21cm');
        $('#kop_h').val('29.7cm');
        $('#kop_file_label').text('Pilih gambar background (PNG / JPG max 2MB)...');
        $('#kop_preview_wrap').addClass('d-none');
        $('#kop-file-req').text('*');
        $('#kop_file').prop('required', true);
        $('#card-form-kopsurat').removeClass('d-none');
        $('#kop_nama').focus();
    });

    // Hide form
    $('#btn-batal-form-kopsurat').on('click', function() {
        $('#card-form-kopsurat').addClass('d-none');
    });

    // Load Kop Surat Table
    function loadKopSuratTable() {
        $('#tbody-kopsurat-list').html('<tr><td colspan="6" class="text-center py-2"><i class="fas fa-spinner fa-spin mr-50"></i> Memuat daftar kop surat...</td></tr>');
        $.ajax({
            url: base_url + "keuangan/get_kopsurat_list",
            type: "GET",
            dataType: "json",
            success: function(r) {
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    let html = '';
                    if (r.data.length > 0) {
                        $.each(r.data, function(i, v) {
                            let imgUrl = v.preview_url || (base_url + v.lokasi);
                            let preview = v.lokasi ? `<a href="${imgUrl}" target="_blank"><img src="${imgUrl}" alt="Kop" style="max-height: 40px; max-width: 70px; object-fit: contain; border: 1px solid #ddd; border-radius: 3px;"></a>` : '-';
                            html += `
                                <tr>
                                    <td class="text-center align-middle">${i + 1}</td>
                                    <td class="text-center align-middle">${preview}</td>
                                    <td class="align-middle font-weight-bold">${v.nama}</td>
                                    <td class="text-center align-middle"><span class="badge badge-light-primary">${v.ukuran}</span></td>
                                    <td class="text-center align-middle">${v.w} x ${v.h}</td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning btn-edit-kopsurat" data-id="${v.id}" title="Edit"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-outline-danger btn-hapus-kopsurat" data-id="${v.id}" data-nama="${v.nama}" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center py-2 text-muted">Belum ada data kop surat. Klik "+ Tambah Kop Surat" untuk menambahkan.</td></tr>';
                    }
                    $('#tbody-kopsurat-list').html(html);
                }
            },
            error: function() {
                $('#tbody-kopsurat-list').html('<tr><td colspan="6" class="text-center py-2 text-danger">Gagal memuat data kop surat.</td></tr>');
            }
        });
    }

    // Submit Kop Surat Form
    $('#form-kopsurat').on('submit', function(e) {
        e.preventDefault();
        let form = this;
        let formData = new FormData(form);
        formData.append(csrfName, csrfHash);

        let btn = $('#btn-simpan-form-kopsurat');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-25"></i> Menyimpan...');

        $.ajax({
            url: base_url + "keuangan/simpan_kopsurat",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(r) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Kop Surat');
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: r.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#card-form-kopsurat').addClass('d-none');
                    loadKopSuratTable();
                    refreshKopSuratSelect();
                } else {
                    Swal.fire('Gagal', r.message, 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-25"></i> Simpan Kop Surat');
                Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data.', 'error');
            }
        });
    });

    // Edit Kop Surat
    $(document).on('click', '.btn-edit-kopsurat', function() {
        let id = $(this).data('id');
        $.ajax({
            url: base_url + "keuangan/get_kopsurat_detail",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function(r) {
                if (r.token) csrfHash = r.token;
                if (r.success && r.data) {
                    let d = r.data;
                    $('#card-form-kopsurat-title').text('Edit Kop Surat');
                    $('#kop_id').val(d.id);
                    $('#kop_nama').val(d.nama);
                    $('#kop_ukuran').val(d.ukuran).trigger('change');
                    $('#kop_w').val(d.w);
                    $('#kop_h').val(d.h);

                    // Margins
                    $('#kop_mt').val(d.mt || 0);
                    $('#kop_mb').val(d.mb || 0);
                    $('#kop_ml').val(d.ml || 0);
                    $('#kop_mr').val(d.mr || 0);
                    $('#kop_pmt').val(d.pmt || 30);
                    $('#kop_pmb').val(d.pmb || 25);
                    $('#kop_pml').val(d.pml || 15);
                    $('#kop_pmr').val(d.pmr || 15);

                    // File upload optional on edit
                    $('#kop-file-req').text('(Kosongkan jika tidak diubah)');
                    $('#kop_file').prop('required', false);
                    $('#kop_file_label').text('Ganti gambar background (opsional)...');

                    if (d.lokasi) {
                        $('#kop_preview_img').attr('src', d.preview_url || (base_url + d.lokasi));
                        $('#kop_preview_wrap').removeClass('d-none');
                    } else {
                        $('#kop_preview_wrap').addClass('d-none');
                    }

                    $('#card-form-kopsurat').removeClass('d-none');
                    $('#kop_nama').focus();
                }
            }
        });
    });

    // Delete Kop Surat
    $(document).on('click', '.btn-hapus-kopsurat', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');

        Swal.fire({
            title: 'Hapus Kop Surat?',
            text: `Apakah Anda yakin ingin menghapus kop surat "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + "keuangan/hapus_kopsurat",
                    type: "POST",
                    data: {
                        [csrfName]: csrfHash,
                        id: id
                    },
                    dataType: "json",
                    success: function(r) {
                        if (r.token) csrfHash = r.token;
                        if (r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: r.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadKopSuratTable();
                            refreshKopSuratSelect();
                        } else {
                            Swal.fire('Gagal', r.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
    });

    // When modal kelola kop surat is closed, ensure select in modal penagihan is refreshed
    $('#modal_kelola_kopsurat').on('hidden.bs.modal', function() {
        refreshKopSuratSelect();
    });
});
