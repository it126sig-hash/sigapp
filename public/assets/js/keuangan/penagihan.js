$(document).ready(function() {
    // Initialize rich text
    if ($('#tagihan_snk').length) {
        $('#tagihan_snk').richText();
    }
    
    // Add initModalListener from scripts.js for confirmation
    if (typeof initModalListener === 'function') {
        initModalListener('#modal_penagihan');
    }

    // Toggle footer buttons based on active tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#tab_buat_tagihan') {
            $('#footer-action-riwayat-tagihan').addClass('d-none');
            $('#footer-action-buat-tagihan').removeClass('d-none');
        } else {
            $('#footer-action-buat-tagihan').addClass('d-none');
            $('#footer-action-riwayat-tagihan').removeClass('d-none');
        }
    });

    window.openModalPenagihan = function(rowData, targetTab = 'tab_riwayat_tagihan') {
        if (!rowData) return;
        let id_mkdt = rowData.id_mkdt;

        // Reset form FIRST before populating values
        $('#form-buat-tagihan')[0].reset();

        $('#tagihan_id_mkdt').val(id_mkdt);
        $('#tagihan_id_kavling').val(rowData.id_kavling || '');
        $('#tagihan_id_konsumen').val(rowData.id_konsumen || '');

        $('#tagihan_detail_konsumen').html(rowData.nama_konsumen || '-');
        $('#tagihan_detail_kavling').html((rowData.nama_jalan || '-') + ' - No. ' + (rowData.no_kavling || '-'));

        // Populate new top header
        let nama = rowData.nama_konsumen || '-';
        let initial = nama.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
        $('#tagihan_avatar_initial').text(initial);
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

        // Setup Date
        let today = new Date();
        let h7 = new Date();
        h7.setDate(h7.getDate() + 7);
        $('#tagihan_tanggal').val(today.toISOString().split('T')[0]);
        $('#tagihan_jatuh_tempo').val(h7.toISOString().split('T')[0]);

        // Reset rich text editor if needed
        if ($('.richText-editor').length) {
            $('.richText-editor').html($('#tagihan_snk').val());
        }
        $('#tagihan_kopsurat').empty();
        $('#tb-tagihan-items-here').empty();


        // Load riwayat
        loadRiwayatTagihan(id_mkdt);

        // Load data untuk form buat tagihan
        loadDataFormTagihan(id_mkdt, rowData.id_kavling, rowData.id_keuangan);

        let activeTab = targetTab || 'tab_riwayat_tagihan';
        $(`#${activeTab}-tab`).tab('show');

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
        
        $('#dtl_no_inv').text(v.no_inv);
        $('#dtl_tgl_terbit').text(format_date((v.tanggal_invoice || '').split(' ')[0]));
        $('#dtl_jatuh_tempo').text(format_date((v.tanggal_jatuh_tempo || '').split(' ')[0]));
        $('#dtl_dibuat_oleh').text(v.pembuat || '-');
        
        let statusBadge = '';
        let st = (v.status_tagihan || '').toLowerCase();
        if (st === 'dibuat') statusBadge = '<span class="badge badge-secondary font-weight-bold">DIBUAT</span>';
        else if (st === 'dikirim') statusBadge = '<span class="badge badge-info font-weight-bold">DIKIRIM</span>';
        else if (st === 'respon') statusBadge = '<span class="badge badge-success font-weight-bold">RESPON</span>';
        else if (st === 'tidak respon') statusBadge = '<span class="badge badge-danger font-weight-bold">TIDAK RESPON</span>';
        else statusBadge = `<span class="badge badge-light-primary font-weight-bold text-uppercase">${v.status_tagihan}</span>`;
        $('#dtl_status_badge').html(statusBadge);
        
        let actionsHtml = `
            <a href="${base_url}keuangan/download_penagihan?id=${encodeURIComponent(v.no_inv)}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download mr-50"></i> Download</a>
            <button type="button" class="btn btn-sm btn-outline-info btn-ubah-status-tagihan" data-no="${v.no_inv}" data-status="${v.status_tagihan}" data-tgl="${v.tanggal_ubah_status || ''}" data-ket="${v.keterangan_status || ''}">Ubah Status</button>
        `;
        $('#dtl_actions').html(actionsHtml);
        
        // Build all history entries
        let timelineList = [];
        if (Array.isArray(v.lifecycle) && v.lifecycle.length > 0) {
            timelineList = v.lifecycle;
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
        $('#list_riwayat_tagihan-here').html('<tr><td colspan="5" class="text-center">Memuat riwayat...</td></tr>');
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
                            if (stLower === 'dibuat') statusBadge = '<span class="badge badge-light-secondary font-weight-bold">DIBUAT</span>';
                            else if (stLower === 'dikirim') statusBadge = '<span class="badge badge-light-info font-weight-bold">DIKIRIM</span>';
                            else if (stLower === 'respon') statusBadge = '<span class="badge badge-light-success font-weight-bold">RESPON</span>';
                            else if (stLower === 'tidak respon') statusBadge = '<span class="badge badge-light-danger font-weight-bold">TIDAK RESPON</span>';
                            else statusBadge = `<span class="badge badge-light-primary font-weight-bold text-uppercase">${v.status_tagihan}</span>`;
                            
                            let rowDataJson = JSON.stringify(v).replace(/"/g, '&quot;');
                            let noSuratHtml = `<div class="font-weight-bolder text-dark">${v.no_inv}</div><div class="small text-muted mt-25 text-uppercase">${v.pembuat || '-'}</div>`;
                            let tglTerbitHtml = `<div class="text-dark">${format_date((v.tanggal_invoice || '').split(' ')[0])}</div>
                                                 <div class="small text-danger mt-25 font-weight-bold"><i class="fas fa-calendar-times mr-25"></i>${format_date((v.tanggal_jatuh_tempo || '').split(' ')[0])}</div>`;
                            let updateTgl = v.tanggal_ubah_status ? format_date(v.tanggal_ubah_status.split(' ')[0]) : '-';
                            let updateHtml = `<div class="text-dark">${updateTgl}</div><div class="small text-muted mt-25">${v.keterangan_status || '-'}</div>`;
                            
                            html += `<tr data-row="${rowDataJson}" data-no-inv="${v.no_inv}" class="riwayat-row" onclick="selectRiwayatRow(this)">
                                <td>${noSuratHtml}</td>
                                <td>${tglTerbitHtml}</td>
                                <td>${statusBadge}</td>
                                <td>${updateHtml}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-block px-1 tagihan-detail-btn" onclick="event.stopPropagation(); selectRiwayatRow(this.closest('tr'))">Detail</button>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center">Belum ada riwayat tagihan.</td></tr>';
                    }
                    $('#list_riwayat_tagihan-here').html(html);
                    
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
    
    function loadDataFormTagihan(id_mkdt, id_kavling, id_keuangan) {
        // We reuse the existing /keuangan/get_tagihan/inv endpoint to get kop surat and items
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
              let total = 0;
              $.each(r.list_tagihan, function(i, a) {
                  let nominal = parseInt(a.nominal);
                  total += nominal;
                  
                  itemsHtml += `
                    <tr>
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input tagihan-item-check" id="checkTagihan_${i}" data-ba="${a.berita_acara}" data-jt="${a.jatuh_tempo_tgl}" data-nom="${a.nominal}" checked>
                                <label class="custom-control-label" for="checkTagihan_${i}"></label>
                            </div>
                        </td>
                        <td class="text-center">${i + 1}</td>
                        <td>${a.berita_acara}</td>
                        <td>${format_date(a.jatuh_tempo_tgl)}</td>
                        <td class="text-right">Rp <span class="tagihan-item-nominal-text">${num_format(nominal)}</span></td>
                    </tr>
                  `;
              });
              itemsHtml += `
                <tr class="bg-light font-weight-bold">
                    <td colspan="4" class="text-right">Total Tagihan</td>
                    <td class="text-right" id="tagihan-total-nominal">Rp ${num_format(total)}</td>
                </tr>
              `;
              $('#tb-tagihan-items-here').html(itemsHtml);

              // Check all behavior
              $('#checkAllTagihan').prop('checked', true);
              $('#checkAllTagihan').off('change').on('change', function() {
                  $('.tagihan-item-check').prop('checked', $(this).is(':checked'));
                  updateTotalTagihan();
              });

              $('.tagihan-item-check').off('change').on('change', function() {
                  if ($('.tagihan-item-check:not(:checked)').length > 0) {
                      $('#checkAllTagihan').prop('checked', false);
                  } else {
                      $('#checkAllTagihan').prop('checked', true);
                  }
                  updateTotalTagihan();
              });

              function updateTotalTagihan() {
                  let tempTotal = 0;
                  $('.tagihan-item-check:checked').each(function() {
                      tempTotal += parseInt($(this).data('nom'));
                  });
                  $('#tagihan-total-nominal').text('Rp ' + num_format(tempTotal));
              }
            }
        });
    }
    
    $('#form-buat-tagihan').on('submit', function(e) {
        e.preventDefault();
        
        if ($('.richText-editor').length) {
            $('#tagihan_snk').val($('.richText-editor').html());
        }
        
        let formData = $(this).serializeArray();
        
        // build json tagihan only from checked items
        let tagihanArray = [];
        $('.tagihan-item-check:checked').each(function() {
            tagihanArray.push({
                berita_acara: $(this).data('ba'),
                jatuh_tempo_tgl: $(this).data('jt'),
                nominal: $(this).data('nom')
            });
        });
        
        // 1. Validasi Item Tagihan
        if (tagihanArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Item Tagihan Kosong',
                text: 'Silakan pilih (ceklis) minimal 1 item tagihan.'
            });
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
        
        $('#btn-simpan-tagihan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
        
        $.ajax({
            url: base_url + "keuangan/simpan_penagihan",
            type: "POST",
            dataType: "json",
            data: submitData,
            success: function(r) {
                $('#btn-simpan-tagihan').prop('disabled', false).html('Buat Invoice Tagihan');
                if (r.token) csrfHash = r.token;
                if (r.success) {
                    Swal.fire('Berhasil', r.messages, 'success');
                    $('#form-buat-tagihan')[0].reset();
        
                    // Setup Date
                    let today = new Date();
                    let h7 = new Date();
                    h7.setDate(h7.getDate() + 7);
                    $('#tagihan_tanggal').val(today.toISOString().split('T')[0]);
                    $('#tagihan_jatuh_tempo').val(h7.toISOString().split('T')[0]);
                    
                    // Reset rich text editor if needed
                    if ($('.richText-editor').length) {
                        $('.richText-editor').html($('#tagihan_snk').val());
                    }
                    
                    $('#tab_riwayat_tagihan-tab').tab('show');
                    loadRiwayatTagihan(submitData.id_mkdt);
                } else {
                    Swal.fire('Gagal', r.messages, 'error');
                }
            },
            error: function() {
                $('#btn-simpan-tagihan').prop('disabled', false).html('Buat Invoice Tagihan');
                Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
            }
        });
    });
    
    // Ubah Status
    $(document).on('click', '.btn-ubah-status-tagihan', function() {
        let no_inv = $(this).data('no');
        let status = $(this).data('status');
        let tgl = $(this).data('tgl');
        let ket = $(this).data('ket');
        
        $('#us_no_inv').val(no_inv);
        $('#us_status_tagihan').val(status);
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
        
        $('#modal_ubah_status_tagihan').modal('show');
    });
    
    $('#form-ubah-status-tagihan').on('submit', function(e) {
        e.preventDefault();
        
        let submitData = {
            [csrfName]: csrfHash,
            no_inv: $('#us_no_inv').val(),
            status_tagihan: $('#us_status_tagihan').val(),
            tanggal_ubah_status: $('#us_tanggal_ubah_status').val(),
            keterangan_status: $('#us_keterangan_status').val()
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
