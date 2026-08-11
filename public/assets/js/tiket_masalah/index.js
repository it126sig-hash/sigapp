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
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let mapBtn = '';
                    if ((row.ref_type === 'kavling' || row.ref_type === 'others') && row.ref_id) {
                        let url = base_url + 'siteplan/master/' + row.id_proyek + '?focus_type=' + row.ref_type + '&focus_id=' + row.ref_id;
                        // mapBtn = `
                        //     <a href="${url}" class="btn btn-sm btn-info btn-icon rounded-circle ml-1" title="Lihat di Peta">
                        //         <i class="fas fa-map-marker-alt"></i>
                        //     </a>
                        // `;
                    }
                    return `
                        <button class="btn btn-sm btn-primary btn-icon rounded-circle btn-view-tiket" 
                            data-id="${row.id}" 
                            data-ref-type="${row.ref_type}" 
                            data-ref-id="${row.ref_id}" 
                            title="Detail Tiket">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${mapBtn}
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: 'lokasi',
                name: 'lokasi'
            },
            {
                data: 'keterangan',
                name: 'tm.keterangan',
                render: function(data, type, row) {
                    let html = `<div><strong>Laporan:</strong> ${data}</div>`;
                    if (row.last_progress_keterangan) {
                        html += `<div class="mt-1 text-muted small"><strong>Update:</strong> ${row.last_progress_keterangan}</div>`;
                        if (row.last_progress_date) {
                            // formatting date
                            let d = new Date(row.last_progress_date);
                            html += `<div class="text-muted small"><i class="fas fa-clock"></i> ${d.toLocaleString('id-ID')}</div>`;
                        }
                    }
                    return html;
                }
            },
            {
                data: 'status',
                name: 'tm.status',
                render: function(data, type, row) {
                    let cls = 'badge-status-dibuat';
                    let label = data.replace('_', ' ').toUpperCase();

                    if (data === 'selesai') cls = 'badge-status-selesai';
                    else if (data === 'dalam_proses') cls = 'badge-status-proses';
                    else if (data === 'hold') cls = 'badge-status-hold';
                    else if (data === 'batal') cls = 'badge-status-batal';

                    return `<span class="badge-status-pill ${cls}"><span class="dot"></span> ${label}</span>`;
                }
            },
            {
                data: 'prioritas',
                name: 'tm.prioritas',
                render: function(data, type, row) {
                    let cls = 'badge-prio-normal';
                    if (data === 'urgent') cls = 'badge-prio-urgent';
                    else if (data === 'medium') cls = 'badge-prio-medium';
                    else if (data === 'low') cls = 'badge-prio-low';
                    else if (data === 'laporan') cls = 'badge-prio-laporan';
                    
                    return `<span class="badge-prio ${cls}">${data.toUpperCase()}</span>`;
                }
            },
            {
                data: 'pic_username',
                name: 'u.username'
            },
            {
                data: 'assigned_users_list',
                name: 'assigned_users_list',
                orderable: false
            },
            {
                data: 'tanggal_masalah',
                name: 'tm.tanggal_masalah'
            }
        ],
        order: [[7, 'desc']], // Default urut berdasarkan tanggal terbaru
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
