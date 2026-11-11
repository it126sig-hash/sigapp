/**
 * ============================================================================
 * MODUL: LEGALITAS & PERIZINAN (LEGAL)
 * Script Halaman List Legalitas (Tabel & Filter Data)
 * ============================================================================
 */

(function($) {
    'use strict';

    // Global Action Handlers for DataTables inline onclick buttons
    window.edit = function(id) {
        if (typeof open_flegal === 'function') {
            open_flegal({ data: { id_legal: id } }, null, id);
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Detail Legalitas',
                text: 'Perubahan dan pengelolaan detail data legalitas (ID #' + id + ') dilakukan melalui menu Siteplan atau modul Kavling.',
                confirmButtonText: 'Tutup',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
        }
    };

    window.remove = function(id) {
        Swal.fire({
            title: 'Hapus Data Legalitas?',
            text: 'Data legalitas ID #' + id + ' terkait dengan data kavling dan perizinan. Hubungi Administrator jika ingin membatalkan atau menghapus record ini.',
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonText: 'Tutup',
            cancelButtonClass: 'btn btn-outline-secondary',
            buttonsStyling: false
        });
    };

    $(function() {
        const tableEl = $('#data_table');
        if (!tableEl.length) {
            return;
        }

        const config = window.LEGAL_CONFIG || {};
        const baseUrl = config.baseUrl || (typeof base_url !== 'undefined' ? base_url : '');
        let csrfTokenName = config.csrfName || (typeof csrfName !== 'undefined' ? csrfName : 'csrf_test_name');
        let csrfTokenHash = config.csrfHash || (typeof csrfHash !== 'undefined' ? csrfHash : '');

        function getActiveProjectId() {
            if (typeof window.activeProyekId === 'function') {
                return window.activeProyekId();
            }
            if (window.SIGAPP && window.SIGAPP.activeProyekId) {
                return window.SIGAPP.activeProyekId;
            }
            return '';
        }

        function updateCsrf(token) {
            if (token) {
                csrfTokenHash = token;
                if (typeof csrfHash !== 'undefined') {
                    csrfHash = token;
                }
            }
        }

        // Initialize DataTable
        const table = tableEl.DataTable({
            scrollY: '70vh',
            scrollX: true,
            scrollCollapse: true,
            fixedColumns: true,
            processing: true,
            serverSide: true,
            lengthChange: true,
            searching: true,
            ordering: false,
            paging: true,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                zeroRecords: 'Tidak ditemukan data yang sesuai',
                processing: '<i class="fa fa-spinner fa-spin mr-50"></i>Memuat data...'
            },
            ajax: {
                url: baseUrl + '/api/legal/getListLegalitas',
                type: 'POST',
                dataType: 'json',
                data: function(d) {
                    d[csrfTokenName] = csrfTokenHash;
                    d.id_proyek = getActiveProjectId();
                    d.id_cluster = $('#id_cluster').val();
                    d.id_jalan = $('#id_jalan').val();
                },
                dataSrc: function(res) {
                    updateCsrf(res.token);
                    return res.data || [];
                }
            }
        });

        // Search on change / enter
        $('.dataTables_filter input')
            .off()
            .on('change', function() {
                table.search(this.value).draw();
            });

        // Initial state for project
        const initialProjectId = getActiveProjectId();
        if (initialProjectId) {
            $('#id_cluster').prop('disabled', false);
            table.draw();
        }

        // Select2: Cluster Filter
        $('#id_cluster').select2({
            placeholder: 'Pilih Cluster',
            allowClear: true,
            ajax: {
                url: baseUrl + '/cluster/getAll',
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: function(params) {
                    return {
                        [csrfTokenName]: csrfTokenHash,
                        search: params.term,
                        id_proyek: getActiveProjectId()
                    };
                },
                processResults: function(r) {
                    updateCsrf(r.token);
                    const results = [];
                    if (r && r.data) {
                        $.each(r.data, function(index, item) {
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

        // Event when cluster changes
        $('#id_cluster').on('change', function() {
            $('#id_jalan').val(null).trigger('change');
            if (this.value) {
                $('#id_jalan').prop('disabled', false);
            } else {
                $('#id_jalan').prop('disabled', true);
            }
        });

        // Select2: Blok / Jalan Filter
        $('#id_jalan').select2({
            placeholder: 'Pilih Blok',
            allowClear: true,
            ajax: {
                url: baseUrl + '/jalan/getAll',
                dataType: 'json',
                delay: 250,
                method: 'POST',
                data: function(params) {
                    return {
                        [csrfTokenName]: csrfTokenHash,
                        search: params.term,
                        id_cluster: $('#id_cluster').val(),
                        id_proyek: getActiveProjectId()
                    };
                },
                processResults: function(r) {
                    updateCsrf(r.token);
                    const results = [];
                    if (r && r.data) {
                        $.each(r.data, function(index, item) {
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

        // Apply filter button
        $('#btn_draw').on('click', function() {
            table.draw();
        });

        // Reset filter button
        $('#btn_reset_filter').on('click', function() {
            $('#id_cluster').val(null).trigger('change');
            $('#id_jalan').val(null).trigger('change');
            $('#id_jalan').prop('disabled', true);
            table.search('').draw();
        });

        // Prevent pointer-events glitch on Select2 arrow
        $('.select2-selection__arrow').css('pointer-events', 'none');
    });
})(jQuery);
