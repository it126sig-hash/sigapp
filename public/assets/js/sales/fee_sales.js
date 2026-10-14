$(function () {
    "use strict";

    function formatRupiah(number) {
        if (number === null || number === "" || isNaN(number)) return "Rp 0";
        return "Rp " + parseFloat(number).toLocaleString("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function parseMoney(val) {
        if (val === null || val === undefined || val === "") return 0;
        if (typeof val === "number") return Math.round(val);
        let s = String(val).trim();
        if (!s) return 0;
        if (s.indexOf(".") !== -1 && s.indexOf(",") !== -1) {
            if (s.lastIndexOf(",") > s.lastIndexOf(".")) {
                s = s.replace(/\./g, "").replace(/,/g, ".");
            } else {
                s = s.replace(/,/g, "");
            }
            return Math.round(parseFloat(s)) || 0;
        }
        if ((s.match(/\./g) || []).length > 1) {
            return Math.round(parseFloat(s.replace(/\./g, ""))) || 0;
        }
        if ((s.match(/,/g) || []).length > 1) {
            return Math.round(parseFloat(s.replace(/,/g, ""))) || 0;
        }
        if (s.indexOf(".") !== -1) {
            let parts = s.split(".");
            if (parts[1].length === 3) {
                return Math.round(parseFloat(s.replace(/\./g, ""))) || 0;
            }
            return Math.round(parseFloat(s)) || 0;
        }
        if (s.indexOf(",") !== -1) {
            let parts = s.split(",");
            if (parts[1].length === 3) {
                return Math.round(parseFloat(s.replace(/,/g, ""))) || 0;
            }
            return Math.round(parseFloat(s.replace(/,/g, "."))) || 0;
        }
        return Math.round(parseFloat(s.replace(/[^\d.-]/g, ""))) || 0;
    }

    function cleanPercent(val) {
        if (val === null || val === undefined || val === "") return 0;
        if (typeof val === "number") return val;
        let s = String(val).trim().replace(/,/g, ".");
        return parseFloat(s.replace(/[^\d.-]/g, "")) || 0;
    }

    const cleanNum = parseMoney;
    const cleanCurrency = parseMoney;

    $(".flatpickr-basic").flatpickr({
        dateFormat: "Y-m-d",
        allowInput: true,
    });

    $(".custom-file-input").on("change", function () {
        let fileName = $(this).val().split("\\").pop();
        $(this).next(".custom-file-label").addClass("selected").html(fileName || "Pilih file");
    });

    // Formatting on keyup for num-input
    $(document).on("input", ".num-input", function () {
        let val = cleanCurrency($(this).val());
        if (val === 0 && $(this).val().trim() === "") return;
        let formatted = val.toLocaleString("id-ID");
        $(this).val(formatted);
    });

    let currentDetailData = null;

    const dtFee = $("#table-fee-sales").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + "api/sales-external/list-fee",
            type: "POST",
            data: function (d) {
                d[csrfName] = csrfHash;
                d.id_proyek = $("#filter-fee-proyek").val();
                d.status_fee = $("#filter-fee-status").val();
                d.is_ajb = $("#filter-fee-ajb").val();
            },
            dataSrc: function (json) {
                if (json.token) csrfHash = json.token;
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
                data: null,
                render: function (data, type, row) {
                    let jalan = row.nama_jalan || "-";
                    let kav = row.no_kavling || "-";
                    let tipe = row.tipe_rumah ? `(${row.tipe_rumah})` : "";
                    return `
                        <div>
                            <span class="font-weight-bold text-dark">${row.nama_konsumen || "-"}</span>
                            <small class="text-muted d-block font-small-2">${jalan} • No. ${kav} ${tipe}</small>
                        </div>
                    `;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let ajb = cleanNum(row.harga_net_ajb);
                    let net = cleanNum(row.harga_jual_net);
                    if (ajb > 0) {
                        return `<span class="font-weight-bold text-primary">${formatRupiah(ajb)}</span>`;
                    }
                    return `
                        <div>
                            <span class="font-weight-bold text-secondary">${formatRupiah(net)}</span>
                            <span class="badge badge-light-warning font-small-1 d-block mt-25">Estimasi</span>
                        </div>
                    `;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let salesName = row.master_sales_nama || row.sales_konsumen || "-";
                    let ket = row.keterangan_sales
                        ? `<span class="badge badge-light-info font-small-1 ml-50">${row.keterangan_sales}</span>`
                        : "";
                    return `<div><span class="font-weight-bold">${salesName}</span>${ket}</div>`;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let tgl = row.akad_tgl || "-";
                    let badge = (parseInt(row.is_ajb) === 1)
                        ? '<span class="badge badge-light-success font-small-1">AJB</span>'
                        : '<span class="badge badge-light-warning font-small-1">PPJB</span>';
                    return `<div><small class="d-block text-secondary">${tgl}</small>${badge}</div>`;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let totalHasil = cleanNum(row.total_hasil_akad);
                    let totalCairKeu = cleanNum(row.total_cair_keuangan);
                    let pct = totalHasil > 0 ? Math.min(100, Math.round((totalCairKeu / totalHasil) * 100)) : 0;
                    let barClass = pct >= 100 ? "bg-success" : (pct > 0 ? "bg-primary" : "bg-secondary");

                    return `
                        <div style="min-width: 110px;">
                            <div class="d-flex justify-content-between font-small-2 mb-25">
                                <span class="font-weight-bold">${pct}%</span>
                                <span class="text-muted">${formatRupiah(totalCairKeu)}</span>
                            </div>
                            <div class="progress termin-progress-mini">
                                <div class="progress-bar ${barClass}" role="progressbar" style="width: ${pct}%;"></div>
                            </div>
                        </div>
                    `;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.id_fee_sales && cleanNum(row.fee_total) > 0) {
                        return `
                            <div>
                                <span class="font-weight-bold text-success">${formatRupiah(row.fee_total)}</span>
                                <small class="text-muted d-block font-small-2">${parseFloat(row.fee_persentase || 0).toFixed(2)}%</small>
                            </div>
                        `;
                    }
                    return '<span class="badge badge-light-secondary">Belum Dihitung</span>';
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let st = row.fee_status;
                    let totalT = parseInt(row.total_termin || 0);
                    let cairT = parseInt(row.termin_cair || 0);
                    let sppT = parseInt(row.termin_spp || 0);

                    if (st === "selesai" || (totalT > 0 && totalT === cairT)) {
                        return '<span class="badge badge-success">Lunas (100%)</span>';
                    }
                    if (cairT > 0) {
                        return `<span class="badge badge-primary">Cair ${cairT}/${totalT} Termin</span>`;
                    }
                    if (sppT > 0) {
                        return `<span class="badge badge-warning">Proses SPP (${sppT})</span>`;
                    }
                    if (row.id_fee_sales) {
                        return '<span class="badge badge-info">Termin Siap</span>';
                    }
                    return '<span class="badge badge-secondary">Belum Diproses</span>';
                },
            },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button type="button" class="btn btn-sm btn-primary btn-manage-fee" data-id="${row.id_mkdt}">
                            <i class="fas fa-sliders-h mr-25"></i> Kelola
                        </button>
                    `;
                },
            },
        ],
        order: [[4, "desc"]],
        language: {
            processing: "Sedang memuat data...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 data",
            emptyTable: "Belum ada transaksi akad",
            zeroRecords: "Data transaksi tidak ditemukan",
            paginate: {
                previous: "&nbsp;",
                next: "&nbsp;",
            },
        },
    });

    $("#filter-fee-proyek, #filter-fee-status, #filter-fee-ajb").on("change", function () {
        dtFee.ajax.reload();
    });

    $("#btn-refresh-fee").on("click", function () {
        dtFee.ajax.reload();
    });

    // Buka Modal Kelola Fee & Pencairan
    $(document).on("click", ".btn-manage-fee", function () {
        let idMkdt = $(this).data("id");
        openFeeModal(idMkdt);
    });

    function openFeeModal(idMkdt) {
        $("#modal-info-konsumen").text("Memuat...");
        $("#tbody-termin").html('<tr><td colspan="8" class="text-center py-2 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat data...</td></tr>');
        $("#modal-fee-sales").modal("show");

        $.get(base_url + "api/sales-external/fee-detail/" + idMkdt, function (res) {
            if (res.token) csrfHash = res.token;
            if (res.success && res.data) {
                let d = res.data;
                currentDetailData = d;

                // Set Info Konsumen & Kavling
                $("#modal-info-konsumen").text(d.nama_konsumen || "-");
                $("#modal-info-hp").text("HP: " + (d.hp_konsumen || "-"));
                $("#modal-info-unit").text(`${d.nama_jalan || "-"} • Kavling ${d.no_kavling || "-"} (${d.tipe_rumah || "-"})`);
                let subsidiText = parseInt(d.is_subsidi) === 1 ? "Kavling Subsidi" : "Kavling Komersil";
                $("#modal-info-proyek").text(`${d.nama_proyek || "-"} • ${subsidiText}`);

                // Realisasi Pencairan Akad Keuangan
                let totHasil = cleanNum(d.total_hasil_akad);
                let totCairKeu = cleanNum(d.total_cair_keuangan);
                let pctKeu = totHasil > 0 ? Math.min(100, Math.round((totCairKeu / totHasil) * 100)) : 0;
                $("#modal-keu-progress").css("width", pctKeu + "%").attr("class", pctKeu >= 100 ? "progress-bar bg-success" : "progress-bar bg-primary");
                $("#modal-keu-percent").text(pctKeu + "%");
                $("#modal-keu-nominal").text(`${formatRupiah(totCairKeu)} / ${formatRupiah(totHasil)}`);

                // Form Kalkulasi
                $("#calc-id_mkdt").val(d.id_mkdt);
                let hargaAjb = cleanCurrency(d.harga_net_ajb) > 0 ? cleanCurrency(d.harga_net_ajb) : cleanCurrency(d.harga_jual_net);
                $("#calc-harga_net_ajb").val(hargaAjb.toLocaleString("id-ID"));

                // Hitung persen default
                let defaultPersen = parseInt(d.is_subsidi) === 1 ? cleanPercent(d.sales_fee_subsidi || 1.5) : cleanPercent(d.sales_fee_komersil || 2.0);
                if (cleanPercent(d.fee_persentase) > 0) {
                    defaultPersen = cleanPercent(d.fee_persentase);
                }
                $("#calc-persentase_fee").val(defaultPersen.toFixed(2));
                $("#calc-tarif-hint").text(parseInt(d.is_subsidi) === 1 ? "Tarif Subsidi PKS: " + defaultPersen + "%" : "Tarif Komersil PKS: " + defaultPersen + "%");

                if (cleanCurrency(d.fee_total) > 0) {
                    $("#calc-total_fee").val(cleanCurrency(d.fee_total).toLocaleString("id-ID"));
                } else {
                    let autoTotal = Math.round((defaultPersen / 100) * hargaAjb);
                    $("#calc-total_fee").val(autoTotal.toLocaleString("id-ID"));
                }

                if (d.fee_skema_termin) {
                    $("#calc-skema_termin").val(d.fee_skema_termin);
                } else {
                    $("#calc-skema_termin").val("100");
                }
                $("#calc-catatan").val(d.fee_catatan || "");
                $("#calc-override_fee").prop("checked", false);
                $("#calc-total_fee").prop("readonly", true);

                // Render Termin Table
                renderTerminTable(d.termin, d.fee_total);
            }
        });
    }

    function recalculateFee() {
        if ($("#calc-override_fee").is(":checked")) return;
        let harga = cleanCurrency($("#calc-harga_net_ajb").val());
        let persen = cleanPercent($("#calc-persentase_fee").val());
        let total = Math.round((persen / 100) * harga);
        $("#calc-total_fee").val(total.toLocaleString("id-ID"));
    }

    $("#calc-harga_net_ajb, #calc-persentase_fee").on("input change", function () {
        recalculateFee();
    });

    $("#calc-override_fee").on("change", function () {
        if ($(this).is(":checked")) {
            $("#calc-total_fee").prop("readonly", false).focus();
        } else {
            $("#calc-total_fee").prop("readonly", true);
            recalculateFee();
        }
    });

    function renderTerminTable(terminList, totalFee) {
        if (!terminList || terminList.length === 0) {
            $("#tbody-termin").html('<tr><td colspan="8" class="text-center py-2 text-muted">Belum ada termin dibangkitkan. Klik "Simpan & Generate Termin" untuk membuat jadwal pencairan.</td></tr>');
            $("#termin-summary-badge").text("Total Fee: " + formatRupiah(totalFee));
            return;
        }

        $("#termin-summary-badge").text("Total Fee: " + formatRupiah(totalFee));
        let html = "";
        terminList.forEach(function (t) {
            let statusBadge = "";
            let actionBtn = "";

            if (t.status === "sudah_cair") {
                statusBadge = '<span class="badge badge-success"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>';
                actionBtn = '<span class="text-success font-small-2"><i class="fas fa-check mr-25"></i> Tuntas</span>';
            } else if (t.status === "pengajuan_spp") {
                statusBadge = '<span class="badge badge-warning"><i class="fas fa-clock mr-25"></i> Pengajuan SPP</span>';
                actionBtn = `
                    <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50" data-id="${t.id}" data-termin="${t.termin_ke}" data-nominal="${t.nominal}">
                        <i class="fas fa-check mr-25"></i> Konfirmasi Cair
                    </button>
                `;
            } else {
                statusBadge = '<span class="badge badge-secondary">Belum Diajukan</span>';
                actionBtn = `
                    <button type="button" class="btn btn-warning btn-sm btn-open-spp py-25 px-50" data-id="${t.id}" data-termin="${t.termin_ke}" data-nominal="${t.nominal}">
                        <i class="fas fa-paper-plane mr-25"></i> Ajukan SPP
                    </button>
                `;
            }

            let berkasHtml = "";
            if (t.lampiran_spp) {
                berkasHtml += `<a href="${base_url + t.lampiran_spp}" target="_blank" class="badge badge-light-warning mr-25"><i class="fas fa-file-pdf mr-25"></i> SPP</a>`;
            }
            if (t.lampiran_bukti_cair) {
                berkasHtml += `<a href="${base_url + t.lampiran_bukti_cair}" target="_blank" class="badge badge-light-success"><i class="fas fa-receipt mr-25"></i> Bukti</a>`;
            }
            if (!berkasHtml) berkasHtml = '<span class="text-muted">-</span>';

            let sppInfo = t.nomor_spp ? `<div><span class="font-weight-bold font-small-2">${t.nomor_spp}</span><small class="d-block text-muted">${t.tanggal_pengajuan_spp || ""}</small></div>` : '<span class="text-muted">-</span>';
            let cairInfo = t.tanggal_cair ? `<span class="font-small-3 font-weight-bold text-success">${t.tanggal_cair}</span>` : '<span class="text-muted">-</span>';

            html += `
                <tr>
                    <td class="text-center font-weight-bold">Termin ${t.termin_ke}</td>
                    <td class="text-center"><span class="badge badge-light-info">${parseFloat(t.persen).toFixed(1)}%</span></td>
                    <td class="font-weight-bold text-dark">${formatRupiah(t.nominal)}</td>
                    <td>${statusBadge}</td>
                    <td>${sppInfo}</td>
                    <td>${cairInfo}</td>
                    <td>${berkasHtml}</td>
                    <td>${actionBtn}</td>
                </tr>
            `;
        });

        $("#tbody-termin").html(html);
    }

    // Submit form kalkulasi fee & skema termin
    $("#form-fee-calc").on("submit", function (e) {
        e.preventDefault();
        let postData = {
            [csrfName]: csrfHash,
            id_mkdt: $("#calc-id_mkdt").val(),
            harga_net_ajb: cleanCurrency($("#calc-harga_net_ajb").val()),
            persentase_fee: cleanPercent($("#calc-persentase_fee").val()),
            total_fee: cleanCurrency($("#calc-total_fee").val()),
            total_fee_manual: $("#calc-override_fee").is(":checked") ? 1 : 0,
            skema_termin: $("#calc-skema_termin").val(),
            catatan: $("#calc-catatan").val(),
        };

        $("#btn-save-calc").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.post(base_url + "api/sales-external/save-fee-header", postData, function (res) {
            if (res.token) csrfHash = res.token;
            $("#btn-save-calc").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan & Generate Termin');

            if (res.success) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.messages || "Kalkulasi fee berhasil disimpan.",
                    timer: 1500,
                    showConfirmButton: false,
                });
                openFeeModal(postData.id_mkdt);
                dtFee.ajax.reload(null, false);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: res.messages || "Gagal menyimpan kalkulasi.",
                });
            }
        }).fail(function (xhr) {
            $("#btn-save-calc").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan & Generate Termin');
            let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: err,
            });
        });
    });

    // Buka Modal Pengajuan SPP
    $(document).on("click", ".btn-open-spp", function () {
        let idTermin = $(this).data("id");
        let terminKe = $(this).data("termin");
        let nom = $(this).data("nominal");

        $("#form-spp")[0].reset();
        $("#spp-id_termin").val(idTermin);
        $("#spp-termin-label").text("Termin " + terminKe);
        $("#spp-nominal-display").val(formatRupiah(nom));
        $("#spp-tanggal").val(new Date().toISOString().slice(0, 10));
        $(".custom-file-label").html("Pilih file");
        $("#modal-spp").modal("show");
    });

    // Submit Pengajuan SPP
    $("#form-spp").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-spp").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Mengirim...');

        $.ajax({
            url: base_url + "api/sales-external/submit-spp",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-spp").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan SPP');

                if (res.success) {
                    $("#modal-spp").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "SPP Diajukan!",
                        text: res.messages || "Pengajuan SPP berhasil dicatat.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    if (currentDetailData && currentDetailData.id_mkdt) {
                        openFeeModal(currentDetailData.id_mkdt);
                    }
                    dtFee.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: res.messages || "Gagal mengajukan SPP.",
                    });
                }
            },
            error: function (xhr) {
                $("#btn-submit-spp").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan SPP');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: err,
                });
            },
        });
    });

    // Buka Modal Konfirmasi Cair
    $(document).on("click", ".btn-open-cair", function () {
        let idTermin = $(this).data("id");
        let terminKe = $(this).data("termin");
        let nom = $(this).data("nominal");

        $("#form-cair")[0].reset();
        $("#cair-id_termin").val(idTermin);
        $("#cair-termin-label").text("Termin " + terminKe);
        $("#cair-nominal-display").val(formatRupiah(nom));
        $("#cair-tanggal").val(new Date().toISOString().slice(0, 10));
        $(".custom-file-label").html("Pilih file");
        $("#modal-cair").modal("show");
    });

    // Submit Konfirmasi Cair
    $("#form-cair").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-cair").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/konfirmasi-cair",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair');

                if (res.success) {
                    $("#modal-cair").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pencairan Dikonfirmasi!",
                        text: res.messages || "Pencairan fee berhasil dikonfirmasi.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    if (currentDetailData && currentDetailData.id_mkdt) {
                        openFeeModal(currentDetailData.id_mkdt);
                    }
                    dtFee.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: res.messages || "Gagal mengonfirmasi pencairan.",
                    });
                }
            },
            error: function (xhr) {
                $("#btn-submit-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Dana Cair');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: err,
                });
            },
        });
    });
});
