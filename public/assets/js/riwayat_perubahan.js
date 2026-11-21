/**
 * Riwayat Perubahan (Audit Trail) Module JS
 * Standard: SIGAPP 3-Tier JS Architecture (Modular, Scoped, CSRF-safe)
 */
(function($) {
    'use strict';

    $(function() {
        const config = window.RIWAYAT_PERUBAHAN_CONFIG || {};
        const baseUrl = (typeof base_url !== 'undefined' ? base_url : '').replace(/\/+$/, '') + '/';
        const listUrl = config.listUrl || (baseUrl + 'api/riwayat-perubahan/list');
        const csrfTokenName = config.csrfName || (typeof csrfName !== 'undefined' ? csrfName : 'csrf_test_name');
        let csrfTokenHash = config.csrfHash || (typeof csrfHash !== 'undefined' ? csrfHash : '');

        let historyRows = [];
        let historyOffset = 0;
        const historyLimit = parseInt(config.limit || 20, 10);

        function updateCsrf(token) {
            if (!token) return;
            csrfTokenHash = token;
            if (typeof csrfHash !== 'undefined') {
                csrfHash = token;
            }
            $('input[name="' + csrfTokenName + '"]').val(token);
        }

        function escapeHtml(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            return $('<div>').text(String(value)).html();
        }

        function formatDateTime(value) {
            if (!value) return '-';
            if (typeof format_datetime === 'function') {
                try {
                    return format_datetime(value);
                } catch (e) {
                    // Fallback to raw string
                }
            }
            return String(value);
        }

        function prettyJson(value) {
            if (value === null || value === undefined || value === '') {
                return '{}';
            }
            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    return JSON.stringify(parsed, null, 2);
                } catch (e) {
                    return value;
                }
            }
            if (typeof value === 'object') {
                if (Object.keys(value).length === 0) {
                    return '{}';
                }
                return JSON.stringify(value, null, 2);
            }
            return String(value);
        }

        function getActionBadge(action) {
            const raw = (action || '').trim();
            const act = raw.toLowerCase();
            let badgeClass = 'badge-action-default';

            if (act === 'create' || act === 'insert' || act === 'tambah') {
                badgeClass = 'badge-action-create';
            } else if (act === 'update' || act === 'edit' || act === 'ubah') {
                badgeClass = 'badge-action-update';
            } else if (act === 'delete' || act === 'hapus' || act === 'remove') {
                badgeClass = 'badge-action-delete';
            }

            return '<span class="badge-action ' + badgeClass + '">' + escapeHtml(raw || '-') + '</span>';
        }

        function renderRows(response) {
            const rows = response.data || [];
            const total = parseInt(response.total || 0, 10);
            const offset = parseInt(response.offset || 0, 10);
            const limit = parseInt(response.limit || historyLimit, 10);

            if (!rows.length) {
                $('#history-log-body').html(
                    '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada riwayat perubahan ditemukan.</td></tr>'
                );
            } else {
                let html = '';
                rows.forEach(function(row, index) {
                    const createdAt = formatDateTime(row.created_at);
                    const moduleLabel = row.module_label || row.module || '-';
                    const actionBadge = getActionBadge(row.action);
                    const summary = row.summary || '-';
                    const user = row.username || '-';

                    html += '<tr>' +
                        '<td><span class="history-timestamp">' + escapeHtml(createdAt) + '</span></td>' +
                        '<td><span class="badge badge-light-primary">' + escapeHtml(moduleLabel) + '</span></td>' +
                        '<td>' + actionBadge + '</td>' +
                        '<td class="history-summary">' + escapeHtml(summary) + '</td>' +
                        '<td><span class="history-user">' + escapeHtml(user) + '</span></td>' +
                        '<td class="text-right">' +
                            '<button type="button" class="btn btn-outline-primary btn-sm history-detail-btn" data-index="' + index + '" title="Lihat detail">' +
                                '<i data-feather="eye"></i>' +
                            '</button>' +
                        '</td>' +
                    '</tr>';
                });
                $('#history-log-body').html(html);
            }

            const start = total === 0 ? 0 : offset + 1;
            const end = Math.min(offset + rows.length, total);
            $('#history-log-info').text(start + ' - ' + end + ' dari ' + total);
            $('#history-prev').prop('disabled', offset <= 0);
            $('#history-next').prop('disabled', offset + limit >= total);

            if (window.feather) {
                feather.replace();
            }
        }

        function loadHistory(offset) {
            historyOffset = Math.max(0, offset || 0);

            $('#history-log-body').html(
                '<tr><td colspan="6" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>Memuat data...</td></tr>'
            );
            $('#history-prev, #history-next, #history-filter').prop('disabled', true);

            const postData = {
                module: $('#history-module').val(),
                date_from: $('#history-date-from').val(),
                date_to: $('#history-date-to').val(),
                limit: historyLimit,
                offset: historyOffset
            };
            postData[csrfTokenName] = csrfTokenHash;

            $.ajax({
                url: listUrl,
                method: 'POST',
                dataType: 'json',
                data: postData,
                success: function(response) {
                    if (response.token) {
                        updateCsrf(response.token);
                    }

                    historyRows = response.data || [];
                    renderRows(response);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    if (response.token) {
                        updateCsrf(response.token);
                    }

                    const errorMsg = response.messages || 'Gagal memuat data riwayat perubahan.';
                    $('#history-log-body').html(
                        '<tr><td colspan="6" class="text-center text-danger py-3"><i data-feather="alert-circle" class="mr-50"></i>' + escapeHtml(errorMsg) + '</td></tr>'
                    );
                    $('#history-log-info').text('-');

                    if (window.feather) {
                        feather.replace();
                    }
                },
                complete: function() {
                    $('#history-prev, #history-next, #history-filter').prop('disabled', false);
                }
            });
        }

        function showRowDetail(row) {
            if (!row) return;

            const moduleName = row.module_label || row.module || 'Riwayat';
            const actionName = row.action || '-';
            $('#history-detail-title').text(moduleName + ' - ' + actionName);

            // Tampilkan atau sembunyikan kotak metadata debugging (_debug)
            let metadataObj = null;
            if (row.metadata && typeof row.metadata === 'object') {
                metadataObj = row.metadata;
            } else if (typeof row.metadata === 'string') {
                try {
                    metadataObj = JSON.parse(row.metadata);
                } catch (e) {
                    metadataObj = null;
                }
            }

            const debugInfo = (metadataObj && metadataObj._debug && typeof metadataObj._debug === 'object') ? metadataObj._debug : null;
            if (debugInfo && (debugInfo.ip_address || debugInfo.user_agent || debugInfo.uri)) {
                $('#history-debug-ip').text(debugInfo.ip_address || '-');
                $('#history-debug-uri').text(debugInfo.uri || '-');
                $('#history-debug-ua').text(debugInfo.user_agent || '-');
                $('#history-debug-box').removeClass('d-none');
            } else {
                $('#history-debug-box').addClass('d-none');
            }

            $('#history-old-data').text(prettyJson(row.old_data));
            $('#history-new-data').text(prettyJson(row.new_data));
            $('#history-metadata').text(prettyJson(row.metadata));

            $('#history-detail-modal').modal('show');

            if (window.feather) {
                feather.replace();
            }
        }

        // Event Handlers
        $(document).on('click', '.history-detail-btn', function() {
            const index = parseInt($(this).data('index'), 10);
            const row = historyRows[index] || null;
            showRowDetail(row);
        });

        $('#history-filter').on('click', function() {
            loadHistory(0);
        });

        $('#history-prev').on('click', function() {
            loadHistory(historyOffset - historyLimit);
        });

        $('#history-next').on('click', function() {
            loadHistory(historyOffset + historyLimit);
        });

        $('#history-module').on('change', function() {
            loadHistory(0);
        });

        $('#history-date-from, #history-date-to').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                loadHistory(0);
            }
        });

        // Initial Load
        loadHistory(0);
    });
})(jQuery);
