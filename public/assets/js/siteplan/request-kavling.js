/**
 * Request Kavling & Ubah Tipe Handler for Non-Planning Users
 */
$(document).ready(function () {
    const $modal = $('#modal-request-kavling');
    const $form = $('#form-request-kavling');
    const $idProyek = $('#req-id_proyek');
    const $idCluster = $('#req-id_cluster');
    const $idJalan = $('#req-id_jalan');
    const $idTipe = $('#req-id_tipe');
    const $idKavling = $('#req-id_kavling');
    const $wrapKavling = $('#wrap-req-kavling');
    const $btnSubmit = $('#btn-submit-request-kavling');

    function getProyekId() {
        return $idProyek.val() || (typeof pl_id_proyek !== 'undefined' ? pl_id_proyek : '');
    }

    // Toggle jenis request
    $('input[name="jenis_request"]').on('change', function () {
        const val = $(this).val();
        if (val === 'ubah_tipe') {
            $wrapKavling.removeClass('d-none');
            $('#req-keterangan-req-star').text('');
        } else {
            $wrapKavling.addClass('d-none');
            $idKavling.val(null).trigger('change');
            $('#req-keterangan-req-star').text('*');
        }
    });

    // Inisialisasi Select2 Cluster
    $idCluster.select2({
        dropdownParent: $modal,
        placeholder: '-- Pilih Cluster (Jika Ada) --',
        allowClear: true,
        ajax: {
            url: base_url + '/cluster/getAll',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term || '',
                    id_proyek: getProyekId()
                };
            },
            processResults: function (r) {
                if (r.token) csrfHash = r.token;
                let results = [];
                if (r.data && Array.isArray(r.data)) {
                    $.each(r.data, function (index, item) {
                        results.push({
                            id: item[0],
                            text: item[2]
                        });
                    });
                }
                return { results: results };
            },
            cache: true
        }
    });

    // Event on select/change cluster -> refresh jalan
    $idCluster.on('change', function () {
        $idJalan.val(null).trigger('change');
        if (this.value) {
            $idJalan.prop('disabled', false);
        } else {
            $idJalan.prop('disabled', true);
        }
    });

    // Inisialisasi Select2 Jalan
    $idJalan.select2({
        dropdownParent: $modal,
        placeholder: '-- Pilih Jalan/Blok (Jika Ada) --',
        allowClear: true,
        ajax: {
            url: base_url + '/jalan/getAll',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term || '',
                    id_cluster: $idCluster.val(),
                    id_proyek: getProyekId()
                };
            },
            processResults: function (r) {
                if (r.token) csrfHash = r.token;
                let results = [];
                if (r.data && Array.isArray(r.data)) {
                    $.each(r.data, function (index, item) {
                        results.push({
                            id: item[0],
                            text: item[3]
                        });
                    });
                }
                return { results: results };
            },
            cache: true
        }
    });

    // Inisialisasi Select2 Tipe
    $idTipe.select2({
        dropdownParent: $modal,
        placeholder: '-- Pilih Tipe (Jika Ada) --',
        allowClear: true,
        ajax: {
            url: base_url + '/tipe/getAll',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term || '',
                    id_proyek: getProyekId()
                };
            },
            processResults: function (r) {
                if (r.token) csrfHash = r.token;
                let results = [];
                if (r.data && Array.isArray(r.data)) {
                    $.each(r.data, function (index, item) {
                        results.push({
                            id: item[0],
                            text: item[2] + ' (' + item[3] + ')'
                        });
                    });
                }
                return { results: results };
            },
            cache: true
        }
    });

    // Inisialisasi Select2 Kavling (untuk Ubah Tipe)
    $idKavling.select2({
        dropdownParent: $modal,
        placeholder: '-- Cari Kavling (Ketik Nomor/Jalan) --',
        allowClear: true,
        ajax: {
            url: base_url + '/api/kavling-request/kavling-list',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term || '',
                    id_proyek: getProyekId()
                };
            },
            processResults: function (r) {
                if (r.token) csrfHash = r.token;
                return {
                    results: r.results || []
                };
            },
            cache: true
        }
    });

    // Submit Handler
    $form.on('submit', function (e) {
        e.preventDefault();

        const jenis = $('input[name="jenis_request"]:checked').val();
        const idClusterVal = $idCluster.val();
        const idJalanVal = $idJalan.val();
        const idKavlingVal = $idKavling.val();
        const keteranganVal = $.trim($('#req-keterangan').val());

        // Validasi jenis ubah tipe
        if (jenis === 'ubah_tipe' && !idKavlingVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih kavling yang ingin diubah tipenya.'
            });
            return;
        }

        // Validasi jenis tambah baru jika cluster/jalan belum dipilih
        if (jenis === 'tambah_baru' && (!idClusterVal || !idJalanVal) && !keteranganVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Karena Cluster dan/atau Jalan belum dipilih, silakan isi kolom Keterangan dengan detail nama cluster dan jalan yang diminta.'
            });
            return;
        }

        // Siapkan data pengiriman
        const formData = $form.serialize() + '&' + csrfName + '=' + encodeURIComponent(csrfHash);

        $btnSubmit.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengirim Request...');

        $.ajax({
            url: base_url + '/api/kavling-request/submit',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.token) csrfHash = res.token;

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.messages || 'Request kavling berhasil diajukan ke tim Planning.'
                    }).then(function () {
                        $modal.modal('hide');
                        $form[0].reset();
                        $idCluster.val(null).trigger('change');
                        $idJalan.val(null).trigger('change');
                        $idTipe.val(null).trigger('change');
                        $idKavling.val(null).trigger('change');
                        $('input[name="jenis_request"][value="tambah_baru"]').prop('checked', true).trigger('change');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.messages || 'Terjadi kesalahan saat menyimpan data.'
                    });
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.token) {
                    csrfHash = xhr.responseJSON.token;
                }
                const msg = (xhr.responseJSON && xhr.responseJSON.messages)
                    ? xhr.responseJSON.messages
                    : 'Terjadi kesalahan pada server.';

                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: msg
                });
            },
            complete: function () {
                $btnSubmit.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Kirim Request');
            }
        });
    });

    // Helper function global untuk membuka modal request
    window.openModalRequestKavling = function (prefilledKavlingId) {
        $modal.modal('show');
        if (prefilledKavlingId) {
            $('input[name="jenis_request"][value="ubah_tipe"]').prop('checked', true).trigger('change');
        }
    };

    // ─────────────────────────────────────────────────────────────────────────────
    // MODAL LIST REQUEST KAVLING (ROLE PLANNING & ADMIN)
    // ─────────────────────────────────────────────────────────────────────────────
    const $modalList = $('#modal-list-request-kavling');
    const $tbodyList = $('#tbody-list-request');
    const $loadingList = $('#loading-list-request');
    const $emptyList = $('#empty-list-request');
    const $tableContainer = $('#table-list-request-container');
    let currentRequestStatusFilter = '';

    // Handle filter buttons
    $('#btn-group-filter-status button').on('click', function () {
        $('#btn-group-filter-status button').removeClass('btn-primary active')
            .addClass('btn-outline-primary');

        const status = $(this).data('status');
        currentRequestStatusFilter = status;

        $(this).removeClass('btn-outline-primary').addClass('btn-primary active');

        loadListRequestKavling();
    });

    window.openModalListRequestKavling = function () {
        $modalList.modal('show');
        loadListRequestKavling();
    };

    window.reloadListRequestKavling = function () {
        loadListRequestKavling();
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function loadListRequestKavling() {
        const idProyek = getProyekId();
        if (!idProyek) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'ID Proyek tidak ditemukan. Silakan refresh halaman.'
            });
            return;
        }

        $loadingList.removeClass('d-none');
        $tableContainer.addClass('d-none');
        $emptyList.addClass('d-none');

        $.ajax({
            url: base_url + '/api/kavling-request/list',
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                status: currentRequestStatusFilter
            },
            dataType: 'json',
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $loadingList.addClass('d-none');

                if (!res.success || !res.data) {
                    $emptyList.removeClass('d-none');
                    return;
                }

                const list = res.data;
                renderRequestListTable(list);
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.token) {
                    csrfHash = xhr.responseJSON.token;
                }
                $loadingList.addClass('d-none');
                $emptyList.removeClass('d-none');
                const msg = (xhr.responseJSON && xhr.responseJSON.messages)
                    ? xhr.responseJSON.messages
                    : 'Gagal memuat daftar request.';
                Swal.fire({ icon: 'error', title: 'Kesalahan', text: msg });
            }
        });
    }

    function renderRequestListTable(list) {
        $tbodyList.empty();

        if (!list || list.length === 0) {
            $tableContainer.addClass('d-none');
            $emptyList.removeClass('d-none');
            return;
        }

        $emptyList.addClass('d-none');
        $tableContainer.removeClass('d-none');

        // Hitung count status untuk badge filter
        let countAll = 0, countPending = 0, countApproved = 0, countRejected = 0;
        $.each(list, function (_, item) {
            countAll++;
            if (item.status === 'pending') countPending++;
            else if (item.status === 'approved') countApproved++;
            else if (item.status === 'rejected') countRejected++;
        });

        // Update badge count jika sedang filter 'Semua'
        if (!currentRequestStatusFilter) {
            $('#count-req-all').text(countAll);
            $('#count-req-pending').text(countPending);
            $('#count-req-approved').text(countApproved);
            $('#count-req-rejected').text(countRejected);
        }

        $.each(list, function (idx, item) {
            const no = idx + 1;
            const tanggal = item.created_at ? item.created_at.substring(0, 16) : '-';
            const pengaju = escapeHtml(item.pengaju_name || item.pengaju_username || 'User');

            // Badge jenis
            let badgeJenis = '';
            if (item.jenis_request === 'ubah_tipe') {
                badgeJenis = '<span class="badge badge-light-primary text-primary font-weight-bold"><i class="fa fa-exchange mr-1"></i> Ubah Tipe</span>';
            } else {
                badgeJenis = '<span class="badge badge-light-success text-success font-weight-bold"><i class="fa fa-plus-circle mr-1"></i> Tambah Baru</span>';
            }

            // Detail request
            let detailHtml = '';
            if (item.jenis_request === 'ubah_tipe') {
                const kavInfo = item.existing_nama_jalan
                    ? `${escapeHtml(item.existing_nama_jalan)} No. ${escapeHtml(item.existing_no_kavling || item.id_kavling)}`
                    : `ID Kavling #${item.id_kavling}`;
                const tipeTujuan = item.no_tipe_rumah
                    ? `${escapeHtml(item.no_tipe_rumah)}${item.tipe_rumah ? ' (' + escapeHtml(item.tipe_rumah) + ')' : ''}`
                    : '<span class="text-muted">-</span>';

                detailHtml = `<div><strong>Kavling:</strong> <span class="text-primary">${kavInfo}</span></div>
                              <div><strong>Tipe Dituju:</strong> ${tipeTujuan}</div>`;
            } else {
                const clusterText = item.nama_cluster ? escapeHtml(item.nama_cluster) : '<span class="text-muted font-italic">Manual / Belum ada</span>';
                const jalanText = item.nama_jalan ? escapeHtml(item.nama_jalan) : '<span class="text-muted font-italic">Manual / Belum ada</span>';
                const tipeText = item.no_tipe_rumah
                    ? `${escapeHtml(item.no_tipe_rumah)}${item.tipe_rumah ? ' (' + escapeHtml(item.tipe_rumah) + ')' : ''}`
                    : '<span class="text-muted">-</span>';

                detailHtml = `<div><strong>Cluster:</strong> ${clusterText}</div>
                              <div><strong>Jalan/Blok:</strong> ${jalanText}</div>
                              <div><strong>Tipe:</strong> ${tipeText}</div>`;
            }

            const keterangan = escapeHtml(item.keterangan || '-');

            // Badge status
            let badgeStatus = '';
            if (item.status === 'approved') {
                badgeStatus = '<span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i> Selesai</span>';
            } else if (item.status === 'rejected') {
                badgeStatus = '<span class="badge badge-danger px-2 py-1"><i class="fa fa-times mr-1"></i> Ditolak</span>';
            } else {
                badgeStatus = '<span class="badge badge-warning px-2 py-1"><i class="fa fa-clock-o mr-1"></i> Menunggu</span>';
            }

            // Tombol aksi tindak lanjut
            let actionButtons = '';
            if (item.status === 'pending') {
                actionButtons = `
                    <button type="button" class="btn btn-success btn-sm btn-block mb-1" onclick="updateRequestStatus(${item.id_request}, 'approved')" title="Tandai Selesai Dikerjakan">
                        <i class="fa fa-check mr-1"></i> Selesai
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-block" onclick="updateRequestStatus(${item.id_request}, 'rejected')" title="Tolak Pengajuan">
                        <i class="fa fa-times mr-1"></i> Tolak
                    </button>
                `;
            } else if (item.status === 'approved') {
                actionButtons = `
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="updateRequestStatus(${item.id_request}, 'pending')" title="Kembalikan Status ke Menunggu">
                        <i class="fa fa-undo mr-1"></i> Batal Selesai
                    </button>
                `;
            } else {
                actionButtons = `
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="updateRequestStatus(${item.id_request}, 'pending')" title="Kembalikan Status ke Menunggu">
                        <i class="fa fa-undo mr-1"></i> Buka Kembali
                    </button>
                `;
            }

            const row = `
                <tr>
                    <td class="text-center font-weight-bold">${no}</td>
                    <td>
                        <div class="font-weight-bold">${pengaju}</div>
                        <small class="text-muted"><i class="fa fa-calendar-o mr-1"></i>${tanggal}</small>
                    </td>
                    <td class="text-center">${badgeJenis}</td>
                    <td>${detailHtml}</td>
                    <td><div style="max-height: 80px; overflow-y: auto; font-size: 0.88rem;">${keterangan}</div></td>
                    <td class="text-center">${badgeStatus}</td>
                    <td class="text-center">${actionButtons}</td>
                </tr>
            `;
            $tbodyList.append(row);
        });
    }

    window.updateRequestStatus = function (idRequest, newStatus) {
        let confirmTitle = 'Konfirmasi';
        let confirmText = 'Apakah Anda yakin ingin mengubah status request ini?';
        let confirmColor = '#3085d6';

        if (newStatus === 'approved') {
            confirmTitle = 'Tandai Selesai?';
            confirmText = 'Pengajuan kavling ini akan ditandai telah selesai dikerjakan oleh tim Planning.';
            confirmColor = '#28a745';
        } else if (newStatus === 'rejected') {
            confirmTitle = 'Tolak Pengajuan?';
            confirmText = 'Pengajuan kavling ini akan ditandai sebagai Ditolak.';
            confirmColor = '#dc3545';
        } else if (newStatus === 'pending') {
            confirmTitle = 'Kembalikan ke Menunggu?';
            confirmText = 'Status pengajuan akan dikembalikan menjadi Menunggu (Pending).';
            confirmColor = '#ffc107';
        }

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + '/api/kavling-request/update-status',
                    type: 'POST',
                    data: {
                        [csrfName]: csrfHash,
                        id_request: idRequest,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.token) csrfHash = res.token;

                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.messages || 'Status berhasil diperbarui.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadListRequestKavling();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.messages || 'Gagal mengubah status request.'
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.token) {
                            csrfHash = xhr.responseJSON.token;
                        }
                        const msg = (xhr.responseJSON && xhr.responseJSON.messages)
                            ? xhr.responseJSON.messages
                            : 'Terjadi kesalahan pada server.';
                        Swal.fire({ icon: 'error', title: 'Kesalahan', text: msg });
                    }
                });
            }
        });
    };

    // Inject tombol ke floating toolbar Planning jika tersedia
    if (typeof roleid !== 'undefined' && (roleid == 6 || roleid == 1)) {
        function injectPlanningListRequestBtn() {
            const $planningMenu = $('#planning_menu');
            if ($planningMenu.length && !$('#btn-planning-list-request').length) {
                const btnHtml = '<button id="btn-planning-list-request" type="button" class="btn-icon btn btn-primary btn-round btn-sm my-float font-weight-bold ml-1" onclick="openModalListRequestKavling()" title="Lihat Daftar Pengajuan Kavling">' +
                    '<i class="fa fa-list-alt mr-1"></i> List Request' +
                    '</button>';
                $planningMenu.append(btnHtml);
            }
        }

        injectPlanningListRequestBtn();
        $(document).ajaxComplete(function (e, xhr, settings) {
            if (settings.url && settings.url.indexOf('getMenuBtn') !== -1) {
                setTimeout(injectPlanningListRequestBtn, 100);
            }
        });
    }
});
