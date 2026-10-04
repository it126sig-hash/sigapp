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

    let canvas = document.getElementById("tagihan-canvas");
    let ctx = canvas.getContext("2d");
    let isDrawing = false;
    let hasDrawn = false;
    let canvasRect;
    let hasProfileSignature = false;
    let isCheckingProfileSignature = false;
    
    function resizeCanvas() {
        if (!canvas) return;
        let parent = canvas.parentElement;
        canvas.width = parent.clientWidth - 2; // -2 for border
        canvas.height = 200;
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.lineJoin = "round";
        ctx.lineCap = "round";
        ctx.lineWidth = 2;
        ctx.strokeStyle = "#000000";
        hasDrawn = false;
    }
    
    // When modal shown, resize canvas properly
    $('#modal_penagihan').on('shown.bs.modal', function () {
        if ($('#tagihan-sign-method').val() === 'canvas') {
            resizeCanvas();
        }
    });
    
    // Drawing logic
    function getPointerPos(e) {
        canvasRect = canvas.getBoundingClientRect();
        if (e.touches) {
            return {
                x: e.touches[0].clientX - canvasRect.left,
                y: e.touches[0].clientY - canvasRect.top
            };
        }
        return {
            x: e.clientX - canvasRect.left,
            y: e.clientY - canvasRect.top
        };
    }
    
    function startDraw(e) {
        e.preventDefault();
        isDrawing = true;
        let pos = getPointerPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        hasDrawn = true;
        let pos = getPointerPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    
    function stopDraw() {
        if (!isDrawing) return;
        isDrawing = false;
        ctx.closePath();
        // save to hidden input only if something was drawn
        if (hasDrawn) {
            $('#tagihan_ttd_img').val(canvas.toDataURL("image/png"));
        } else {
            $('#tagihan_ttd_img').val('empty');
        }
    }
    
    if (canvas) {
        canvas.addEventListener("mousedown", startDraw);
        canvas.addEventListener("mousemove", draw);
        canvas.addEventListener("mouseup", stopDraw);
        canvas.addEventListener("mouseout", stopDraw);
        
        canvas.addEventListener("touchstart", startDraw, {passive: false});
        canvas.addEventListener("touchmove", draw, {passive: false});
        canvas.addEventListener("touchend", stopDraw);
    }
    
    $('#btn-clear-tagihan-canvas').on('click', function() {
        resizeCanvas();
        hasDrawn = false;
        $('#tagihan_ttd_img').val('empty');
    });

    function checkProfileSignature() {
        $('#tagihan-profile-preview-wrap').removeClass('d-none');
        $('#tagihan-profile-preview-content').html('<i class="fas fa-spinner fa-spin mr-50"></i> Memeriksa tanda tangan profil...');
        isCheckingProfileSignature = true;

        $.ajax({
            url: base_url + "api/profile/signature",
            type: "GET",
            dataType: "json",
            success: function(r) {
                isCheckingProfileSignature = false;
                if (r && r.token) csrfHash = r.token;

                // CI4 BaseApiController::success returns { success: true, messages: '...', data: { has_signature: true, ... } }
                let isSuccess = Boolean(r && (r.success === true || r.status === 'success' || r.status === 200));
                let sigData = r ? (r.data || r) : null;
                let hasSig = Boolean(sigData && (sigData.has_signature === true || sigData.has_signature === 1 || sigData.has_signature === '1'));

                if (isSuccess && hasSig) {
                    hasProfileSignature = true;
                    let imgUrl = base_url + "api/profile/signature/image?t=" + Date.now();
                    $('#tagihan-profile-preview-content').html(`
                        <div class="py-1">
                            <img src="${imgUrl}" style="max-height: 80px; max-width: 250px; border: 1px dashed #ced4da; padding: 4px; background: #fff;" alt="TTD Profil" class="mb-50" onerror="this.style.display='none'">
                            <div><span class="badge badge-light-success"><i class="fas fa-check-circle mr-25"></i> TTD Profil Aktif</span></div>
                            <small class="text-muted">Terakhir diperbarui: ${sigData.updated_at || '-'}</small>
                        </div>
                    `);
                } else {
                    hasProfileSignature = false;
                    $('#tagihan-profile-preview-content').html(`
                        <div class="alert alert-warning mb-0 py-1 text-left">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-2x mr-75 text-warning"></i>
                                <div>
                                    <div class="font-weight-bold">Tanda Tangan Profil Belum Diatur</div>
                                    <div class="small">Anda belum memiliki tanda tangan di profil akun Anda. Silakan pilih metode <strong>"Gambar Sekarang"</strong> untuk menandatangani langsung pada canvas.</div>
                                </div>
                            </div>
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                isCheckingProfileSignature = false;
                hasProfileSignature = false;
                let errMsg = 'Gagal memeriksa tanda tangan profil.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.messages) {
                    errMsg += ' (' + xhr.responseJSON.messages + ')';
                }
                $('#tagihan-profile-preview-content').html(`
                    <div class="alert alert-danger mb-0 py-1 text-left">
                        <i class="fas fa-exclamation-circle mr-50"></i> ${errMsg} Silakan gunakan metode "Gambar Sekarang".
                    </div>
                `);
            }
        });
    }
    
    $('#tagihan-sign-method').on('change', function() {
        if ($(this).val() === 'canvas') {
            $('#tagihan-canvas-wrap').show();
            $('#tagihan-profile-preview-wrap').addClass('d-none');
            setTimeout(resizeCanvas, 50);
        } else {
            $('#tagihan-canvas-wrap').hide();
            checkProfileSignature();
        }
    });

    window.openModalPenagihan = function(rowData, targetTab) {
        if (!rowData) return;
        let id_mkdt = rowData.id_mkdt;

        // Reset form FIRST before populating values
        $('#form-buat-tagihan')[0].reset();

        $('#tagihan_id_mkdt').val(id_mkdt);
        $('#tagihan_id_kavling').val(rowData.id_kavling || '');
        $('#tagihan_id_konsumen').val(rowData.id_konsumen || '');

        $('#tagihan_detail_konsumen').html(rowData.nama_konsumen || '-');
        $('#tagihan_detail_kavling').html((rowData.nama_jalan || '-') + ' - No. ' + (rowData.no_kavling || '-'));

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

        // Reset Signature State
        hasDrawn = false;
        hasProfileSignature = false;
        $('#tagihan-sign-method').val('canvas');
        $('#tagihan-password').val('');
        $('#tagihan_ttd_img').val('empty');
        $('#tagihan-canvas-wrap').show();
        $('#tagihan-profile-preview-wrap').addClass('d-none');
        resizeCanvas();

        // Load riwayat
        loadRiwayatTagihan(id_mkdt);

        // Load data untuk form buat tagihan
        loadDataFormTagihan(id_mkdt, rowData.id_kavling, rowData.id_keuangan);

        if (targetTab) {
            $(`#${targetTab}-tab`).tab('show');
        }

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
            window.openModalPenagihan(rowData, 'tab_buat_tagihan');
        }
    });
    
    function loadRiwayatTagihan(id_mkdt) {
        $('#list_riwayat_tagihan-here').html('<tr><td colspan="8" class="text-center">Memuat riwayat...</td></tr>');
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
                            if (v.status_tagihan === 'dibuat') statusBadge = '<span class="badge badge-secondary">Dibuat</span>';
                            else if (v.status_tagihan === 'dikirim') statusBadge = '<span class="badge badge-info">Dikirim</span>';
                            else if (v.status_tagihan === 'respon') statusBadge = '<span class="badge badge-success">Respon</span>';
                            else if (v.status_tagihan === 'tidak respon') statusBadge = '<span class="badge badge-danger">Tidak Respon</span>';
                            
                            html += `<tr>
                                <td>${v.no_inv}</td>
                                <td>${format_date(v.tanggal_invoice)}</td>
                                <td>${format_date(v.tanggal_jatuh_tempo)}</td>
                                <td>${statusBadge}</td>
                                <td>${v.tanggal_ubah_status ? format_date(v.tanggal_ubah_status) : '-'}</td>
                                <td>${v.keterangan_status || '-'}</td>
                                <td>${v.pembuat || '-'}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center" style="gap: .25rem;">
                                        <button type="button" class="btn btn-sm btn-outline-warning btn-ubah-status-tagihan" data-no="${v.no_inv}" data-status="${v.status_tagihan}" data-tgl="${v.tanggal_ubah_status || ''}" data-ket="${v.keterangan_status || ''}" title="Ubah Status"><i class="fas fa-edit"></i></button>
                                        <a href="${base_url}keuangan/download_penagihan?id=${encodeURIComponent(v.no_inv)}" target="_blank" class="btn btn-sm btn-outline-primary btn-download-tagihan" title="Download PDF"><i class="fas fa-download"></i></a>
                                    </div>
                                </td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="8" class="text-center">Belum ada riwayat tagihan.</td></tr>';
                    }
                    $('#list_riwayat_tagihan-here').html(html);
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
              
              // Load Items
              let itemsHtml = '';
              let total = 0;
              $.each(r.list_tagihan, function(i, a) {
                  let nominal = parseInt(a.nominal);
                  total += nominal;
                  // We add a hidden input to store the JSON string to be submitted
                  itemsHtml += `
                    <tr>
                        <td class="text-center">${i + 1}
                            <input type="hidden" name="item_ba[]" value="${a.berita_acara}">
                            <input type="hidden" name="item_jt[]" value="${a.jatuh_tempo_tgl}">
                            <input type="hidden" name="item_nominal[]" value="${a.nominal}">
                        </td>
                        <td>${a.berita_acara}</td>
                        <td>${format_date(a.jatuh_tempo_tgl)}</td>
                        <td class="text-right">Rp ${num_format(nominal)}</td>
                    </tr>
                  `;
              });
              itemsHtml += `
                <tr class="bg-light font-weight-bold">
                    <td colspan="3" class="text-right">Total Tagihan</td>
                    <td class="text-right">Rp ${num_format(total)}</td>
                </tr>
              `;
              $('#tb-tagihan-items-here').html(itemsHtml);
            }
        });
    }
    
    $('#form-buat-tagihan').on('submit', function(e) {
        e.preventDefault();
        
        if ($('.richText-editor').length) {
            $('#tagihan_snk').val($('.richText-editor').html());
        }
        
        let formData = $(this).serializeArray();
        
        // build json tagihan
        let tagihanArray = [];
        let items_ba = [];
        let items_jt = [];
        let items_nom = [];
        
        $.each(formData, function(i, field) {
            if (field.name === 'item_ba[]') items_ba.push(field.value);
            else if (field.name === 'item_jt[]') items_jt.push(field.value);
            else if (field.name === 'item_nominal[]') items_nom.push(field.value);
        });
        
        for (let i = 0; i < items_ba.length; i++) {
            tagihanArray.push({
                berita_acara: items_ba[i],
                jatuh_tempo_tgl: items_jt[i],
                nominal: items_nom[i]
            });
        }
        
        // 1. Validasi Item Tagihan
        if (tagihanArray.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Item Tagihan Kosong',
                text: 'Tidak ada item tagihan untuk dibuatkan invoice.'
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

        // 3. Validasi Password
        let password = $('#tagihan-password').val();
        if (!password || password.trim() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Password Belum Diisi',
                text: 'Silakan masukkan password akun Anda untuk verifikasi tanda tangan.'
            });
            $('#tagihan-password').focus();
            return;
        }

        // 4. Validasi Tanda Tangan
        let sign_method = $('#tagihan-sign-method').val();
        let ttd_img = $('#tagihan_ttd_img').val();

        if (sign_method === 'canvas') {
            if (!hasDrawn || !ttd_img || ttd_img === 'empty' || !ttd_img.startsWith('data:image')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Belum Ada',
                    text: 'Silakan bubuhkan tanda tangan pada canvas terlebih dahulu.'
                });
                return;
            }
        } else if (sign_method === 'profile') {
            if (isCheckingProfileSignature) {
                Swal.fire({
                    icon: 'info',
                    title: 'Memeriksa TTD...',
                    text: 'Sedang memeriksa tanda tangan profil, silakan tunggu sebentar.'
                });
                return;
            }
            if (!hasProfileSignature) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanda Tangan Profil Tidak Ada',
                    text: 'Anda belum memiliki tanda tangan profil. Silakan gunakan metode "Gambar Sekarang" atau atur tanda tangan profil Anda terlebih dahulu.'
                });
                return;
            }
        }
        
        let submitData = {
            [csrfName]: csrfHash,
            id_mkdt: $('#tagihan_id_mkdt').val(),
            id_kavling: $('#tagihan_id_kavling').val(),
            id_konsumen: $('#tagihan_id_konsumen').val(),
            id_kopsurat: id_kopsurat,
            tanggal_invoice: $('#tagihan_tanggal').val(),
            tanggal_jatuh_tempo: $('#tagihan_jatuh_tempo').val(),
            terms: $('#tagihan_snk').val(),
            sign_method: sign_method,
            password: password,
            ttd_img: ttd_img,
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
                    hasDrawn = false;
                    hasProfileSignature = false;
                    resizeCanvas();
                    $('#tagihan_ttd_img').val('empty');
                    $('#tagihan-password').val('');
                    $('#tab_riwayat_tagihan-tab').tab('show');
                    loadRiwayatTagihan(submitData.id_mkdt);
                } else {
                    Swal.fire('Gagal', r.messages, 'error');
                    if (r.messages && r.messages.toLowerCase().includes('password')) {
                        $('#tagihan-password').val('').focus();
                    }
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
                    loadRiwayatTagihan($('#tagihan_id_mkdt').val());
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
