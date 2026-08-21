$(document).ready(function() {
    window.tableTiketGlobal = $('#table-tiket-masalah-global').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + 'api/tiket-masalah/datatable',
            type: 'POST',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_prioritas = $('#filter_prioritas').val();
                d.filter_proyek = window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : ''; 
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'td-aksi',
                render: function(data, type, row) {
                    return `
                        <div class="m-aksi">
                            <button class="btn btn-sm btn-primary btn-icon rounded-circle btn-view-tiket" 
                                data-id="${row.id}" 
                                data-ref-type="${row.ref_type}" 
                                data-ref-id="${row.ref_id}" 
                                title="Detail Tiket">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'td-no',
                render: function(data, type, row, meta) {
                    let no = meta.row + meta.settings._iDisplayStart + 1;
                    return `<div class="m-no"><span class="badge">#${no}</span></div>`;
                }
            },
            {
                data: 'lokasi',
                name: 'lokasi',
                render: function(data, type, row) {
                    let html = `<div class="m-lokasi">${data || '-'}</div>`;
                    html += `<div class="m-foto">`;
                    if (row.foto_urls && row.foto_urls.length > 0) {
                        let allUrlsStr = encodeURIComponent(JSON.stringify(row.foto_urls));
                        let firstPhoto = row.foto_urls[0];
                        let moreIndicator = '';
                        if (row.foto_urls.length > 1) {
                            moreIndicator = `<div class="foto-overlay-count" onclick="window.openLightbox('${allUrlsStr}', 0)" style="cursor:pointer;">+${row.foto_urls.length - 1}</div>`;
                        }
                        
                        html += `
                            <a href="javascript:void(0)" onclick="window.openLightbox('${allUrlsStr}', 0)">
                                <img src="${firstPhoto}" class="img-thumb-grid">
                            </a>
                            ${moreIndicator}
                        `;
                    }
                    html += `</div>`;
                    return html;
                }
            },
            {
                data: 'keterangan',
                name: 'tm.keterangan',
                className: 'text-left',
                render: function(data, type, row) {
                    let ket = data ? data.replace(/^Laporan\s*/i, '') : '';
                    let html = `<div class="m-ket">
                        <span class="m-ket-label">Keterangan</span>
                        <div class="m-ket-text">${ket}</div>`;
                        
                    if (row.last_progress_keterangan) {
                        html += `<div class="m-ket-update">
                            <div class="m-ket-update-title">Update Terakhir:</div>
                            <div class="m-ket-update-text">${row.last_progress_keterangan}</div>`;
                        if (row.last_progress_date) {
                            let d = new Date(row.last_progress_date);
                            html += `<div class="m-ket-update-date"><i class="fas fa-clock"></i> ${d.toLocaleDateString('id-ID')}, ${d.toLocaleTimeString('id-ID').replace(/\./g,':')}</div>`;
                        }
                        html += `</div>`;
                    }
                    html += `</div>`;
                    html += `<div class="m-divider"></div>`;
                    return html;
                }
            },
            {
                data: 'tanggal_masalah',
                name: 'tm.tanggal_masalah',
                render: function(data, type, row) {
                    let d = data ? new Date(data).toLocaleDateString('id-ID') : '-';
                    return `<div class="m-tgl"><span class="m-lbl">Tgl Kunjungan</span><span class="m-val">${d}</span></div>`;
                }
            },
            {
                data: 'pic_username',
                name: 'u.username',
                render: function(data, type, row) {
                    let d = row.created_at ? new Date(row.created_at).toLocaleDateString('id-ID') : '-';
                    let pic = data ? data : '-';
                    return `<div class="m-pembuat">
                        <div>
                            <span class="m-lbl">Pembuat</span>
                            <span class="m-val">${pic}</span>
                        </div>
                        <span class="m-val-right">${d}</span>
                    </div>`;
                }
            },
            {
                data: 'assigned_users_list',
                name: 'assigned_users_list',
                orderable: false,
                render: function(data, type, row) {
                    let val = data ? data : '-';
                    return `<div class="m-pic"><span class="m-lbl">PIC Penanganan</span><span class="m-val">${val}</span></div>`;
                }
            },
            {
                data: 'status',
                name: 'tm.status',
                render: function(data, type, row) {
                    let cls = 'badge-status-dibuat';
                    let label = data ? data.replace('_', ' ').toUpperCase() : '';

                    if (data === 'selesai') cls = 'badge-status-selesai';
                    else if (data === 'dalam_proses') cls = 'badge-status-proses';
                    else if (data === 'hold') cls = 'badge-status-hold';
                    else if (data === 'batal') cls = 'badge-status-batal';

                    return `<div class="m-status"><span class="badge-status-pill ${cls}">${label}</span></div>`;
                }
            },
            {
                data: 'prioritas',
                name: 'tm.prioritas',
                render: function(data, type, row) {
                    let cls = 'badge-prio-normal';
                    let label = data ? data.toUpperCase() : '';
                    if (data === 'urgent') cls = 'badge-prio-urgent';
                    else if (data === 'medium') cls = 'badge-prio-medium';
                    else if (data === 'low') cls = 'badge-prio-low';
                    else if (data === 'laporan') cls = 'badge-prio-laporan';
                    
                    return `<div class="m-prio"><span class="badge-prio ${cls}">${label}</span></div>`;
                }
            }
        ],
        responsive: false,
        order: [[5, 'desc']], // Default urut berdasarkan tanggal pembuatan (index 5)
        language: {
            url: base_url + "assets/vendor/datatables/i18n/Indonesian.json" // Opsional jika ada
        }
    });

    // Event handler untuk tombol view tiket
    $('#table-tiket-masalah-global').on('click', '.btn-view-tiket', function() {
        let idTiket = $(this).data('id');
        let refType = $(this).data('ref-type');
        let refId = $(this).data('ref-id');
        
        // Memanfaatkan fungsi dari tiket-masalah.js untuk load detail
        if (typeof window.tm_open_detail === 'function') {
            window.tm_open_detail(idTiket, refType, refId);
        } else {
            console.error('Fungsi tm_open_detail tidak ditemukan. Pastikan tiket-masalah.js sudah dimuat.');
        }
    });

    // Event listener untuk filter Datatables
    $('#filter_status, #filter_prioritas').on('change', function() {
        window.tableTiketGlobal.ajax.reload();
    });
});
