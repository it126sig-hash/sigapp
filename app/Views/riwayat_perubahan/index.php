<style>
    .riwayat-perubahan-page .page-toolbar {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        padding: 1rem;
    }

    .riwayat-perubahan-page .table th {
        white-space: nowrap;
    }

    .riwayat-perubahan-page .history-summary {
        max-width: 520px;
        white-space: normal;
    }

    .riwayat-perubahan-page .btn-primary,
    .riwayat-perubahan-page .badge-light-primary {
        border-color: #2057a3;
    }

    .riwayat-json-view {
        min-height: 180px;
        max-height: 58vh;
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f8fafc;
        color: #111827;
        padding: .875rem;
        font-size: .8125rem;
    }

    @media (max-width: 767.98px) {
        .riwayat-perubahan-page .page-toolbar .form-group {
            margin-bottom: .75rem;
        }
    }
</style>

<div class="app-content content riwayat-perubahan-page">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-body">
            <div class="divider divider-left">
                <div class="divider-text">Riwayat Perubahan</div>
            </div>

            <div class="page-toolbar mb-1">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="history-module">Modul</label>
                            <select id="history-module" class="form-control">
                                <option value="">Semua Modul</option>
                                <?php foreach ($modules as $key => $label) : ?>
                                    <option value="<?= esc($key) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="history-date-from">Dari Tanggal</label>
                            <input type="date" id="history-date-from" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-md-0">
                            <label for="history-date-to">Sampai Tanggal</label>
                            <input type="date" id="history-date-to" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="history-filter" class="btn btn-primary btn-block">
                            <i data-feather="filter" class="mr-50"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Modul</th>
                                <th>Aksi</th>
                                <th>Ringkasan</th>
                                <th>User</th>
                                <th class="text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody id="history-log-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-2">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small id="history-log-info" class="text-muted">-</small>
                    <div>
                        <button type="button" id="history-prev" class="btn btn-outline-secondary btn-sm">Sebelumnya</button>
                        <button type="button" id="history-next" class="btn btn-outline-primary btn-sm">Berikutnya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="history-detail-modal" tabindex="-1" role="dialog" aria-labelledby="history-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="history-detail-title">Detail Riwayat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="divider divider-left">
                    <div class="divider-text">Data Lama</div>
                </div>
                <pre id="history-old-data" class="riwayat-json-view"></pre>
                <div class="divider divider-left">
                    <div class="divider-text">Data Baru</div>
                </div>
                <pre id="history-new-data" class="riwayat-json-view"></pre>
                <div class="divider divider-left">
                    <div class="divider-text">Metadata</div>
                </div>
                <pre id="history-metadata" class="riwayat-json-view"></pre>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script>
    (function() {
        let historyRows = [];
        let historyOffset = 0;
        const historyLimit = 20;

        function escapeHtml(value) {
            return $("<div>").text(value === null || value === undefined || value === "" ? "-" : value).html();
        }

        function prettyJson(value) {
            if (!value || (typeof value === "object" && Object.keys(value).length === 0)) {
                return "{}";
            }
            return JSON.stringify(value, null, 2);
        }

        function loadHistory(offset) {
            historyOffset = Math.max(0, offset || 0);
            $("#history-log-body").html('<tr><td colspan="6" class="text-center text-muted py-2">Memuat data...</td></tr>');
            $("#history-prev, #history-next, #history-filter").prop("disabled", true);

            $.ajax({
                url: base_url + "api/riwayat-perubahan/list",
                method: "POST",
                dataType: "json",
                data: {
                    [csrfName]: csrfHash,
                    module: $("#history-module").val(),
                    date_from: $("#history-date-from").val(),
                    date_to: $("#history-date-to").val(),
                    limit: historyLimit,
                    offset: historyOffset,
                },
                success: function(response) {
                    if (response.token) {
                        csrfHash = response.token;
                        $('input[name="' + csrfName + '"]').val(csrfHash);
                    }

                    historyRows = response.data || [];
                    renderRows(response);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    $("#history-log-body").html('<tr><td colspan="6" class="text-center text-danger py-2">' + escapeHtml(response.messages || "Gagal memuat data") + '</td></tr>');
                    $("#history-log-info").text("-");
                },
                complete: function() {
                    $("#history-prev, #history-next, #history-filter").prop("disabled", false);
                }
            });
        }

        function renderRows(response) {
            const rows = response.data || [];
            const total = parseInt(response.total || 0, 10);
            const offset = parseInt(response.offset || 0, 10);
            const limit = parseInt(response.limit || historyLimit, 10);

            if (!rows.length) {
                $("#history-log-body").html('<tr><td colspan="6" class="text-center text-muted py-2">Belum ada data.</td></tr>');
            } else {
                let html = "";
                rows.forEach(function(row, index) {
                    html += `
                        <tr>
                            <td>${escapeHtml(format_datetime(row.created_at))}</td>
                            <td><span class="badge badge-light-primary">${escapeHtml(row.module_label)}</span></td>
                            <td>${escapeHtml(row.action)}</td>
                            <td class="history-summary">${escapeHtml(row.summary)}</td>
                            <td>${escapeHtml(row.username)}</td>
                            <td class="text-right">
                                <button type="button" class="btn btn-outline-primary btn-sm history-detail-btn" data-index="${index}">
                                    <i data-feather="eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $("#history-log-body").html(html);
            }

            const start = total === 0 ? 0 : offset + 1;
            const end = Math.min(offset + rows.length, total);
            $("#history-log-info").text(start + "-" + end + " dari " + total);
            $("#history-prev").prop("disabled", offset <= 0);
            $("#history-next").prop("disabled", offset + limit >= total);

            if (window.feather) {
                feather.replace();
            }
        }

        $(document).on("click", ".history-detail-btn", function() {
            const row = historyRows[parseInt($(this).data("index"), 10)] || {};
            $("#history-detail-title").text((row.module_label || "Riwayat") + " - " + (row.action || "-"));
            $("#history-old-data").text(prettyJson(row.old_data));
            $("#history-new-data").text(prettyJson(row.new_data));
            $("#history-metadata").text(prettyJson(row.metadata));
            $("#history-detail-modal").modal("show");
        });

        $("#history-filter").on("click", function() {
            loadHistory(0);
        });

        $("#history-prev").on("click", function() {
            loadHistory(historyOffset - historyLimit);
        });

        $("#history-next").on("click", function() {
            loadHistory(historyOffset + historyLimit);
        });

        loadHistory(0);
    })();
</script>
