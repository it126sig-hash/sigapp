$(function () {
    "use strict";

    $(".flatpickr-basic").flatpickr({
        dateFormat: "Y-m-d",
        allowInput: true,
    });

    // Custom file input label update
    $(".custom-file-input").on("change", function () {
        let fileName = $(this).val().split("\\").pop();
        $(this).next(".custom-file-label").addClass("selected").html(fileName || "Pilih file");
    });

    const dtSales = $("#table-master-sales").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + "api/sales-external/list-master",
            type: "POST",
            data: function (d) {
                d[csrfName] = csrfHash;
                d.status = $("#filter-sales-status").val();
            },
            dataSrc: function (json) {
                if (json.token) {
                    csrfHash = json.token;
                }
                return json.data;
            },
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            {
                data: "nama",
                render: function (data, type, row) {
                    return `<span class="font-weight-bold text-dark">${data}</span>`;
                },
            },
            {
                data: "kontak",
                render: function (data) {
                    return data || '<span class="text-muted">-</span>';
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.bank || row.no_rek) {
                        return `<div><span class="badge badge-light-secondary font-weight-bold">${row.bank || "-"}</span> <span class="font-small-3">${row.no_rek || "-"}</span></div>`;
                    }
                    return '<span class="text-muted">-</span>';
                },
            },
            {
                data: "fee_subsidi",
                render: function (data) {
                    return `<span class="badge badge-light-info font-weight-bold">${parseFloat(data || 0).toFixed(2)}%</span>`;
                },
            },
            {
                data: "fee_komersil",
                render: function (data) {
                    return `<span class="badge badge-light-primary font-weight-bold">${parseFloat(data || 0).toFixed(2)}%</span>`;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (!row.tanggal_pks) return '<span class="text-muted">-</span>';
                    let exp = row.tanggal_exp_pks ? ` s/d ${row.tanggal_exp_pks}` : " (Berjalan)";
                    return `<span class="font-small-3 text-secondary">${row.tanggal_pks}${exp}</span>`;
                },
            },
            {
                data: "lampiran_pks",
                render: function (data) {
                    if (!data) return '<span class="text-muted">Tidak ada</span>';
                    return `<a href="${base_url + data}" target="_blank" class="btn btn-outline-info btn-sm py-25 px-50"><i class="fas fa-file-pdf mr-25"></i> Lihat PKS</a>`;
                },
            },
            {
                data: "status",
                render: function (data) {
                    return data === "active"
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-secondary">Non-Aktif</span>';
                },
            },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <div class="d-inline-flex">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary mr-50 btn-edit-sales" data-id="${row.id}" title="Edit Data">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary btn-history-sales" data-id="${row.id}" data-nama="${row.nama}" title="Riwayat PKS & Fee">
                                <i class="fas fa-history"></i>
                            </button>
                        </div>
                    `;
                },
            },
        ],
        order: [[1, "asc"]],
        language: {
            processing: "Sedang memuat data...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            emptyTable: "Belum ada data master sales",
            zeroRecords: "Data sales tidak ditemukan",
            paginate: {
                previous: "&nbsp;",
                next: "&nbsp;",
            },
        },
    });

    $("#filter-sales-status").on("change", function () {
        dtSales.ajax.reload();
    });

    // Tambah sales baru
    $("#btn-add-sales").on("click", function () {
        $("#form-sales")[0].reset();
        $("#sales-id").val("");
        $("#sales-lampiran_pks-preview").empty();
        $(".custom-file-label").html("Pilih file");
        $("#modal-sales-title").text("Tambah Sales External");
        $("#sales-status").val("active");
        $("#modal-sales").modal("show");
    });

    // Edit sales
    $(document).on("click", ".btn-edit-sales", function () {
        let id = $(this).data("id");
        $.get(base_url + "api/sales-external/get/" + id, function (res) {
            if (res.success && res.data) {
                let d = res.data;
                $("#sales-id").val(d.id);
                $("#sales-nama").val(d.nama);
                $("#sales-kontak").val(d.kontak);
                $("#sales-bank").val(d.bank);
                $("#sales-no_rek").val(d.no_rek);
                $("#sales-fee_subsidi").val(d.fee_subsidi);
                $("#sales-fee_komersil").val(d.fee_komersil);
                $("#sales-tanggal_pks").val(d.tanggal_pks);
                $("#sales-tanggal_exp_pks").val(d.tanggal_exp_pks);
                $("#sales-status").val(d.status);
                $("#sales-catatan").val(d.catatan);

                if (d.lampiran_pks) {
                    $("#sales-lampiran_pks-preview").html(
                        `<a href="${base_url + d.lampiran_pks}" target="_blank" class="text-primary font-small-3"><i class="fas fa-link mr-25"></i> Lihat berkas PKS saat ini</a>`
                    );
                } else {
                    $("#sales-lampiran_pks-preview").empty();
                }

                $("#modal-sales-title").text("Edit Sales External: " + d.nama);
                $("#modal-sales").modal("show");
            }
        });
    });

    // Submit form simpan sales
    $("#form-sales").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-save-sales").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/save",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-save-sales").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Data Sales');

                if (res.success) {
                    $("#modal-sales").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: res.messages || "Data master sales berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    dtSales.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: res.messages || "Terjadi kesalahan saat menyimpan data.",
                    });
                }
            },
            error: function (xhr) {
                $("#btn-save-sales").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Data Sales');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: err,
                });
            },
        });
    });

    // Tampilkan riwayat PKS & Fee
    $(document).on("click", ".btn-history-sales", function () {
        let id = $(this).data("id");
        let nama = $(this).data("nama");
        $("#history-sales-nama").text(nama);
        $("#history-sales-tbody").html('<tr><td colspan="6" class="text-center py-2 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat data riwayat...</td></tr>');
        $("#modal-sales-history").modal("show");

        $.get(base_url + "api/sales-external/history/" + id, function (res) {
            if (res.token) csrfHash = res.token;
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    $("#history-sales-tbody").html('<tr><td colspan="6" class="text-center py-2 text-muted">Belum ada riwayat perubahan data.</td></tr>');
                    return;
                }
                let rows = "";
                res.data.forEach(function (h) {
                    let sub = h.fee_subsidi_baru !== null ? `${parseFloat(h.fee_subsidi_baru).toFixed(2)}%` : "-";
                    let kom = h.fee_komersil_baru !== null ? `${parseFloat(h.fee_komersil_baru).toFixed(2)}%` : "-";
                    let pks = h.tanggal_pks_baru ? h.tanggal_pks_baru : "-";
                    rows += `
                        <tr>
                            <td class="font-small-3">${h.created_at || "-"}</td>
                            <td><span class="badge badge-light-info">${sub}</span></td>
                            <td><span class="badge badge-light-primary">${kom}</span></td>
                            <td class="font-small-3">${pks}</td>
                            <td class="font-small-3">${h.catatan || "-"}</td>
                            <td class="font-small-3 text-secondary">${h.created_by_name || "Sistem"}</td>
                        </tr>
                    `;
                });
                $("#history-sales-tbody").html(rows);
            }
        });
    });
});
