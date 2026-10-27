$(function () {
    "use strict";

    const userPerms = window.FEE_SALES_PERMISSIONS || { can_manage: 1, can_submit_spp: 1, can_cairkan: 1, can_sign_mkdt: 0, can_sign_keuangan: 0 };
    const canManage = parseInt(userPerms.can_manage) === 1;
    const canSubmitSpp = parseInt(userPerms.can_submit_spp) === 1;
    const canCairkan = parseInt(userPerms.can_cairkan) === 1;
    const canSignMkdt = parseInt(userPerms.can_sign_mkdt) === 1;
    const canSignKeuangan = parseInt(userPerms.can_sign_keuangan) === 1;
    const currentUserId = parseInt(userPerms.current_user_id) || 0;

    function closeModalWithoutConfirmation(selector) {
        const $m = $(selector);
        $m.data("isConfirmedClose", true);
        $m.modal("hide");
    }

    const canvases = {};
    function initCanvas(id, clearBtnId) {
        const canvas = document.getElementById(id);
        if (!canvas) return null;
        const ctx = canvas.getContext("2d");
        let drawing = false;
        let dirty = false;
        const resize = () => {
            const snapshot = dirty ? canvas.toDataURL("image/png") : null;
            const ratio = window.devicePixelRatio || 1;
            const width = Math.floor(canvas.parentElement.getBoundingClientRect().width);
            if (width < 2) return;
            canvas.width = Math.max(1, Math.floor(width * ratio));
            canvas.height = Math.floor(200 * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.2;
            ctx.lineCap = "round";
            ctx.lineJoin = "round";
            ctx.strokeStyle = "#172b4d";
            if (snapshot) {
                const image = new Image();
                image.onload = () => ctx.drawImage(image, 0, 0, width, 200);
                image.src = snapshot;
            }
        };
        const point = (event) => {
            const rect = canvas.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        };
        const stopDrawing = (event) => {
            drawing = false;
            if (event && canvas.hasPointerCapture && canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
        };
        canvas.addEventListener("pointerdown", (event) => {
            if (event.isPrimary === false) return;
            event.preventDefault();
            drawing = true;
            dirty = true;
            if (canvas.setPointerCapture) canvas.setPointerCapture(event.pointerId);
            const p = point(event);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener("pointermove", (event) => {
            if (!drawing) return;
            event.preventDefault();
            const p = point(event);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });
        ["pointerup", "pointercancel"].forEach((name) => canvas.addEventListener(name, stopDrawing));

        if (clearBtnId) {
            $(clearBtnId).off("click").on("click", function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                dirty = false;
            });
        }

        const obj = {
            resize,
            clear: () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                dirty = false;
            },
            data: () => (dirty ? canvas.toDataURL("image/png") : "")
        };
        canvases[id] = obj;
        return obj;
    }

    const batchCanvas = initCanvas("batch-sign-canvas", "#btn-clear-batch-canvas");
    const signCanvas = initCanvas("sign-pengajuan-canvas", "#btn-clear-sign-canvas");

    $("#batch-sign-method").on("change", function () {
        if ($(this).val() === "canvas") {
            $("#batch-sign-canvas-section").show();
            if (batchCanvas) batchCanvas.resize();
        } else {
            $("#batch-sign-canvas-section").hide();
        }
    });

    $("#sign-pengajuan-method").on("change", function () {
        if ($(this).val() === "canvas") {
            $("#sign-pengajuan-canvas-section").show();
            if (signCanvas) signCanvas.resize();
        } else {
            $("#sign-pengajuan-canvas-section").hide();
        }
    });

    $("#modal-create-group").on("shown.bs.modal", function () {
        if (batchCanvas) batchCanvas.resize();
    });

    $("#modal-sign-pengajuan").on("shown.bs.modal", function () {
        if (signCanvas) signCanvas.resize();
    });

    function formatRupiah(number) {
        if (number === null || number === "" || isNaN(number)) return "Rp 0";
        return "Rp " + parseFloat(number).toLocaleString("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function parseMoney(val) {
        if (val === null || val === undefined || val === "") return 0;
        if (typeof val === "number") return Math.round(val);
        let s = String(val).trim();
        if (!s) return 0;

        // DB decimal format tanpa separator ribuan: e.g. '205650000.00' atau '0.00'
        if (/^\d+\.\d{1,2}$/.test(s)) {
            return Math.round(parseFloat(s)) || 0;
        }

        // Format angka murni tanpa simbol
        if (/^-?\d+$/.test(s)) {
            return parseInt(s, 10) || 0;
        }

        // Jika ada titik dan koma sekaligus (format campuran ribuan + desimal)
        if (s.indexOf(".") !== -1 && s.indexOf(",") !== -1) {
            if (s.lastIndexOf(",") > s.lastIndexOf(".")) {
                // Format Indonesia: 1.000.000,50
                s = s.replace(/\./g, "").replace(/,/g, ".");
            } else {
                // Format US: 1,000,000.50
                s = s.replace(/,/g, "");
            }
            return Math.round(parseFloat(s)) || 0;
        }

        // Format ribuan Indonesia dengan titik (e.g. 200.000.000 atau 1.500 atau 2.0000 saat mengetik)
        let cleanDigits = s.replace(/[^\d-]/g, "");
        if (cleanDigits && cleanDigits !== "-") {
            return parseInt(cleanDigits, 10) || 0;
        }

        return 0;
    }

    function cleanPercent(val) {
        if (val === null || val === undefined || val === "") return 0;
        if (typeof val === "number") return val;
        let s = String(val).trim().replace(/,/g, ".");
        return parseFloat(s.replace(/[^\d.-]/g, "")) || 0;
    }

    const cleanNum = parseMoney;
    const cleanCurrency = parseMoney;

    function resolveHargaJualNetAjb(row) {
        if (!row) return { nominal: 0, isEstimasi: true, source: "none" };
        let ajb = cleanNum(row.harga_net_ajb);
        let net = cleanNum(row.harga_jual_net);
        let hj  = cleanNum(row.harga_jual);

        // Prioritas utama: harga_net_ajb dari mkdt
        if (ajb > 0) {
            return { nominal: ajb, isEstimasi: false, source: "ajb" };
        }
        // Fallback jika mkdt.harga_net_ajb belum terisi
        let nominalEstimasi = net > 0 ? net : hj;
        return { nominal: nominalEstimasi, isEstimasi: true, source: "net" };
    }

    function formatShortDate(d) {
        if (!d || d === "0000-00-00" || d === "0000-00-00 00:00:00") return "";
        let dateOnly = String(d).trim().split(" ")[0];
        let parts = dateOnly.split("-");
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return d;
    }

    $(".flatpickr-basic").flatpickr({
        dateFormat: "Y-m-d",
        allowInput: true,
    });

    $(".custom-file-input").on("change", function () {
        let fileName = $(this).val().split("\\").pop();
        $(this).next(".custom-file-label").addClass("selected").html(fileName || "Pilih file");
    });

    // Formatting on input for num-input dengan cursor preservation & digit extraction stabil
    $(document).on("input", ".num-input", function () {
        let input = this;
        let selStart = input.selectionStart || 0;
        let oldVal = input.value || "";
        let digitsBeforeCursor = (oldVal.slice(0, selStart).match(/\d/g) || []).length;

        let raw = oldVal.replace(/[^\d]/g, "");
        if (!raw) {
            input.value = "";
            $(input).trigger("change");
            return;
        }

        let num = parseInt(raw, 10) || 0;
        let formatted = num.toLocaleString("id-ID");
        input.value = formatted;

        let newPos = 0;
        let digitsCounted = 0;
        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) {
                digitsCounted++;
            }
            if (digitsCounted === digitsBeforeCursor) {
                newPos = i + 1;
                break;
            }
        }
        if (digitsBeforeCursor === 0) newPos = 0;
        if (digitsCounted < digitsBeforeCursor) newPos = formatted.length;

        if (input.setSelectionRange) {
            input.setSelectionRange(newPos, newPos);
        }
        $(input).trigger("change");
    });

    // Helper untuk Drag & Drop dan Clipboard Paste pada Berkas Lampiran
    function setupDropzoneAndPaste(zoneSelector, inputSelector, previewSelector) {
        let $zone = $(zoneSelector);
        let $input = $(inputSelector);
        let $preview = $(previewSelector);

        function updatePreview(file) {
            if (!file) {
                $preview.empty().addClass("d-none");
                return;
            }
            let sizeKb = Math.round(file.size / 1024);
            let isImage = file.type && file.type.startsWith("image/");
            let iconHtml = isImage ? '<i class="fas fa-file-image text-primary mr-50 fa-lg"></i>' : '<i class="fas fa-file-pdf text-danger mr-50 fa-lg"></i>';

            let previewHtml = `
                <div class="d-flex align-items-center justify-content-between p-50">
                    <div class="d-flex align-items-center overflow-hidden">
                        ${iconHtml}
                        <div class="text-left ml-50">
                            <span class="font-weight-bold font-small-2 d-block text-truncate" style="max-width: 230px;">${file.name}</span>
                            <small class="text-muted font-small-1">${sizeKb} KB</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm p-25 btn-remove-preview" title="Hapus file">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $preview.html(previewHtml).removeClass("d-none");
        }

        $zone.on("click", function (e) {
            if ($(e.target).closest(".btn-remove-preview").length) return;
            $input.trigger("click");
        });

        $input.on("change", function () {
            if (this.files && this.files[0]) {
                updatePreview(this.files[0]);
            }
        });

        $preview.on("click", ".btn-remove-preview", function (e) {
            e.stopPropagation();
            $input.val("");
            updatePreview(null);
        });

        $zone.on("dragover dragenter", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $zone.addClass("shadow-sm").css("border-color", "#7367f0");
        });

        $zone.on("dragleave dragend", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $zone.removeClass("shadow-sm").css("border-color", "");
        });

        $zone.on("drop", function (e) {
            e.preventDefault();
            e.stopPropagation();
            $zone.removeClass("shadow-sm").css("border-color", "");

            let dt = e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                $input[0].files = dt.files;
                updatePreview(dt.files[0]);
            }
        });

        // Tangkap clipboard paste pada modal
        let $modalParent = $zone.closest(".modal");
        if ($modalParent.length) {
            $modalParent.on("paste", function (e) {
                let clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                if (!clipboardData || !clipboardData.items) return;

                for (let i = 0; i < clipboardData.items.length; i++) {
                    let item = clipboardData.items[i];
                    if (item.kind === "file") {
                        let blob = item.getAsFile();
                        if (blob) {
                            let ext = blob.type.indexOf("png") !== -1 ? ".png" : (blob.type.indexOf("pdf") !== -1 ? ".pdf" : ".jpg");
                            let filename = "screenshot_" + Date.now() + ext;
                            let file = new File([blob], filename, { type: blob.type });

                            let dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            $input[0].files = dataTransfer.files;
                            updatePreview(file);
                            e.preventDefault();
                            break;
                        }
                    }
                }
            });
        }
    }

    setupDropzoneAndPaste("#spp-dropzone", "#spp-file", "#spp-file-preview");
    setupDropzoneAndPaste("#cair-dropzone", "#cair-file", "#cair-file-preview");
    setupDropzoneAndPaste("#edit-cair-dropzone", "#edit-cair-file", "#edit-cair-file-preview");

    let currentDetailData = null;
    let selectedMkdtMap = {}; // { id_mkdt: true }
    let dtGroup = null;

    function updateBatchButton() {
        let count = Object.keys(selectedMkdtMap).length;
        $("#batch-count-badge").text(count);
        if (count > 0 && canSubmitSpp) {
            $("#btn-batch-pengajuan").show();
        } else {
            $("#btn-batch-pengajuan").hide();
        }
    }

    if (!canSubmitSpp) {
        $("#btn-batch-pengajuan").remove();
        $("#check-all-fee").closest("th").hide();
    }
    if (!canManage) {
        $("#btn-add-fee-sales-card").hide();
    }
    if (!canManage && !canSignMkdt && !canSignKeuangan) {
        $("#modal-shared-harga-ajb").prop("readonly", true);
    }
    if (!canCairkan) {
        $("#btn-batch-cair-modal").hide();
    }

    // Check all checkbox
    $("#check-all-fee").on("change", function () {
        let isChecked = $(this).is(":checked");
        $(".check-mkdt-item:not(:disabled)").each(function () {
            let id = $(this).val();
            let $tr = $(this).closest("tr");
            if (isChecked) {
                selectedMkdtMap[id] = true;
                $(this).prop("checked", true);
                $tr.addClass("table-active");
            } else {
                delete selectedMkdtMap[id];
                $(this).prop("checked", false);
                $tr.removeClass("table-active");
            }
        });
        updateBatchButton();
    });

    // Individual item checkbox
    $(document).on("change", ".check-mkdt-item", function () {
        let id = $(this).val();
        let $tr = $(this).closest("tr");
        if ($(this).is(":checked")) {
            selectedMkdtMap[id] = true;
            $tr.addClass("table-active");
        } else {
            delete selectedMkdtMap[id];
            $tr.removeClass("table-active");
            $("#check-all-fee").prop("checked", false);
        }
        updateBatchButton();
    });

    // Row click selection
    $("#table-fee-sales tbody").on("click", "tr", function (e) {
        if ($(e.target).closest("button, a, input, label, select, .btn").length) {
            return;
        }
        if ($(this).hasClass("row-restricted")) {
            return;
        }
        let $chk = $(this).find(".check-mkdt-item:not(:disabled)");
        if ($chk.length) {
            $chk.prop("checked", !$chk.is(":checked")).trigger("change");
        }
    });

    let activeSalesList = [];

    // Load active sales for filter dropdown and modal picker
    $.get(base_url + "api/sales-external/list-active", function (res) {
        if (res && res.data) {
            activeSalesList = res.data;
            let $select = $("#filter-fee-sales");
            res.data.forEach(function (s) {
                $select.append(new Option(s.nama, s.id));
            });
        }
    });

    const dtFee = $("#table-fee-sales").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + "api/sales-external/list-fee",
            type: "POST",
            data: function (d) {
                d[csrfName] = csrfHash;
                d.id_proyek = (typeof activeProyekId === "function" ? activeProyekId() : (window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : "")) || "";
                d.id_sales = $("#filter-fee-sales").val();
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
                orderable: false,
                className: "text-center",
                visible: canSubmitSpp,
                render: function (data, type, row) {
                    let isSelesai = (row.fee_status === "selesai" || (parseInt(row.total_termin || 0) > 0 && parseInt(row.total_termin || 0) === parseInt(row.termin_cair || 0)));
                    let isBelumProses = (!row.id_fee_sales || parseInt(row.total_termin || 0) === 0);
                    let isRestricted = isSelesai || isBelumProses;
                    let restrictReason = "";
                    if (isSelesai) {
                        restrictReason = "Fee sales untuk unit ini sudah lunas 100% dicairkan.";
                    } else if (isBelumProses) {
                        restrictReason = "Jadwal termin fee belum dibuat. Silakan kelola fee sales terlebih dahulu.";
                    }

                    let disabledAttr = isRestricted ? "disabled" : "";
                    let titleAttr = isRestricted ? `title="${restrictReason}"` : "";
                    let checked = (!isRestricted && selectedMkdtMap[row.id_mkdt]) ? "checked" : "";

                    return `
                        <div class="custom-control custom-checkbox" ${titleAttr}>
                            <input type="checkbox" class="custom-control-input check-mkdt-item" id="chk-mkdt-${row.id_mkdt}" value="${row.id_mkdt}" ${checked} ${disabledAttr}>
                            <label class="custom-control-label" for="chk-mkdt-${row.id_mkdt}"></label>
                        </div>
                    `;
                },
            },
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
            },
            {
                data: null,
                className: "text-left",
                render: function (data, type, row) {
                    let jalan = row.nama_jalan || "-";
                    let kav = row.no_kavling || "-";
                    let tipe = row.tipe_rumah ? `(${row.tipe_rumah})` : "";
                    return `
                        <div class="text-left">
                            <span class="font-weight-bold text-dark d-block">${row.nama_konsumen || "-"}</span>
                            <small class="text-muted d-block font-small-2">${jalan} • No. ${kav} ${tipe}</small>
                        </div>
                    `;
                },
            },
            {
                data: "harga_jual",
                render: function (data, type, row) {
                    let hj = cleanNum(row.harga_jual);
                    if (hj > 0) {
                        return `<span class="font-weight-bold text-dark">${formatRupiah(hj)}</span>`;
                    }
                    return '<span class="text-muted font-small-2">-</span>';
                },
            },
            {
                data: "harga_jual_net",
                render: function (data, type, row) {
                    let hjNet = cleanNum(row.harga_jual_net);
                    if (hjNet > 0) {
                        return `<span class="font-weight-bold text-dark">${formatRupiah(hjNet)}</span>`;
                    }
                    return '<span class="text-muted font-small-2">-</span>';
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    let res = resolveHargaJualNetAjb(row);
                    if (!res.isEstimasi) {
                        return `<span class="font-weight-bold text-primary">${formatRupiah(res.nominal)}</span>`;
                    }
                    return `
                        <div>
                            <span class="font-weight-bold text-secondary">${formatRupiah(res.nominal)}</span>
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
                    let tgl = row.akad_tgl ? formatShortDate(row.akad_tgl) : "-";
                    let badgeKpr = (parseInt(row.is_kpr) === 1)
                        ? '<span class="badge badge-light-primary font-small-1 mr-25">KPR</span>'
                        : '<span class="badge badge-light-secondary font-small-1 mr-25">TUNAI</span>';
                    let isAjbVal = String(row.is_ajb || "").trim().toUpperCase();
                    let isAjb = (isAjbVal === "AJB" || isAjbVal === "1");
                    let badgeAjb = isAjb
                        ? '<span class="badge badge-light-success font-small-1">AJB</span>'
                        : '<span class="badge badge-light-warning font-small-1">PPJB</span>';
                    return `<div><small class="d-block text-secondary mb-25">${tgl}</small><div>${badgeKpr}${badgeAjb}</div></div>`;
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
                        <div style="min-width: 70px;">
                            <div class="font-small-2 mb-25">
                                <span class="font-weight-bold">${pct}%</span>
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
                    let tglPengajuan = formatShortDate(row.tgl_pengajuan_spp);
                    let tglCair = formatShortDate(row.tgl_cair_terakhir);

                    let badgeHtml = "";
                    let dateHtml = "";

                    if (st === "selesai" || (totalT > 0 && totalT === cairT)) {
                        badgeHtml = '<span class="badge badge-success">Lunas (100%)</span>';
                        if (tglCair) {
                            dateHtml = `<small class="d-block text-muted font-small-1 mt-25"><i class="fas fa-check-circle mr-25"></i> ${tglCair}</small>`;
                        }
                    } else if (sppT > 0) {
                        if (cairT > 0) {
                            badgeHtml = `<span class="badge badge-primary">Cair ${cairT}/${totalT}</span> <span class="badge badge-warning">SPP (${sppT})</span>`;
                        } else {
                            badgeHtml = `<span class="badge badge-warning">Proses SPP (${sppT})</span>`;
                        }
                        if (tglPengajuan) {
                            dateHtml = `<small class="d-block text-warning font-small-1 mt-25"><i class="fas fa-clock mr-25"></i> Pengajuan: ${tglPengajuan}</small>`;
                        }
                    } else if (cairT > 0) {
                        badgeHtml = `<span class="badge badge-primary">Cair ${cairT}/${totalT} Termin</span>`;
                        if (tglCair) {
                            dateHtml = `<small class="d-block text-muted font-small-1 mt-25"><i class="fas fa-calendar-check mr-25"></i> Cair: ${tglCair}</small>`;
                        }
                    } else if (row.id_fee_sales) {
                        badgeHtml = '<span class="badge badge-info">Termin Siap</span>';
                    } else {
                        badgeHtml = '<span class="badge badge-secondary">Belum Diproses</span>';
                    }

                    return `<div>${badgeHtml}${dateHtml}</div>`;
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
        order: [[7, "desc"]],
        drawCallback: function (settings) {
            let api = this.api();
            let rows = api.rows({ page: "current" }).nodes();
            let data = api.rows({ page: "current" }).data();

            data.each(function (row, index) {
                let $tr = $(rows[index]);
                let isSelesai = (row.fee_status === "selesai" || (parseInt(row.total_termin || 0) > 0 && parseInt(row.total_termin || 0) === parseInt(row.termin_cair || 0)));
                let isBelumProses = (!row.id_fee_sales || parseInt(row.total_termin || 0) === 0);
                if (isSelesai || isBelumProses) {
                    $tr.addClass("row-restricted text-muted");
                } else {
                    $tr.removeClass("row-restricted text-muted");
                }

                if (selectedMkdtMap[row.id_mkdt]) {
                    $tr.addClass("table-active");
                } else {
                    $tr.removeClass("table-active");
                }
            });

            let availableCheckboxes = $(".check-mkdt-item:not(:disabled)");
            if (availableCheckboxes.length > 0 && availableCheckboxes.length === availableCheckboxes.filter(":checked").length) {
                $("#check-all-fee").prop("checked", true);
            } else {
                $("#check-all-fee").prop("checked", false);
            }
        },
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

    $("#filter-fee-sales, #filter-fee-status, #filter-fee-ajb").on("change", function () {
        dtFee.ajax.reload();
    });

    $("#btn-refresh-fee").on("click", function () {
        dtFee.ajax.reload();
    });

    // Buka Modal Kelola Fee & Pencairan
    $(document).on("click", ".btn-manage-fee", function () {
        let idMkdt = $(this).data("id");
        let rowData = dtFee.row($(this).closest("tr")).data();
        openFeeModal(idMkdt, rowData);
    });

    function updateSalesCardsCount() {
        let count = $("#modal-sales-cards-container .sales-fee-card").length;
        $("#modal-sales-count-badge").text(count);
        if (count === 0) {
            $("#modal-sales-cards-container").html(`
                <div class="text-center py-4 bg-light rounded border border-dashed text-muted mb-2">
                    <i class="fas fa-users fa-2x mb-50 text-secondary"></i>
                    <p class="mb-50 font-weight-bold">Belum ada fee sales untuk kavling ini.</p>
                    <button type="button" class="btn btn-primary btn-sm btn-trigger-add-sales">
                        <i class="fas fa-user-plus mr-25"></i> Tambah Sales Pertama
                    </button>
                </div>
            `);
        }
    }

    function buildTerminRowsHtml(terminList) {
        if (!terminList || terminList.length === 0) {
            return '<tr><td colspan="7" class="text-center py-2 text-muted font-small-2">Belum ada termin dibangkitkan. Klik "Simpan & Generate Termin" untuk membuat jadwal pencairan.</td></tr>';
        }

        let html = "";
        terminList.forEach(function (t) {
            let statusBadge = "";
            let actionBtn = "";

            if (t.status === "sudah_cair") {
                statusBadge = '<span class="badge badge-success font-small-2"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>';
                let editCairBtn = canCairkan ? `
                    <button type="button" class="btn btn-outline-success btn-sm btn-open-edit-cair py-25 px-50 d-block w-100 font-small-2" data-id="${t.id}" data-termin="${t.termin_ke}" data-nominal="${t.nominal}" data-tanggal="${t.tanggal_cair || ''}" data-nocek="${t.no_cek || ''}" data-ket="${t.keterangan || ''}" data-file="${t.lampiran_bukti_cair || ''}">
                        <i class="fas fa-edit mr-25"></i> Edit Cair
                    </button>
                ` : '';
                actionBtn = `
                    <span class="badge badge-light-success font-small-2 mb-25 d-block"><i class="fas fa-check mr-25"></i> Tuntas</span>
                    ${editCairBtn}
                `;
            } else if (t.status === "pengajuan_spp") {
                statusBadge = '<span class="badge badge-warning font-small-2"><i class="fas fa-clock mr-25"></i> Diajukan</span>';
                let konfirmasiCairBtn = canCairkan ? `
                    <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50 mb-25 d-block w-100 font-small-2" data-id="${t.id}" data-termin="${t.termin_ke}" data-nominal="${t.nominal}">
                        <i class="fas fa-check mr-25"></i> Konfirmasi Cair
                    </button>
                ` : '';
                actionBtn = konfirmasiCairBtn || '<span class="text-muted font-small-2">-</span>';
            } else {
                statusBadge = '<span class="badge badge-secondary font-small-2">Belum Diajukan</span>';
                actionBtn = '<span class="text-muted font-small-2">-</span>';
            }

            // Kolom Pengajuan Pencairan
            let sppInfo = '<span class="text-muted font-small-2">-</span>';
            if (t.nomor_spp || t.status === "pengajuan_spp" || t.status === "sudah_cair") {
                let regHtml = t.nomor_spp ? `<span class="font-weight-bold font-small-2 d-block text-dark">${t.nomor_spp}</span>` : '<span class="text-muted d-block font-small-2">No Reg: -</span>';
                let tglHtml = t.tanggal_pengajuan_spp ? `<small class="text-muted d-block font-small-1"><i class="far fa-calendar-alt mr-25"></i> ${formatShortDate(t.tanggal_pengajuan_spp)}</small>` : '';
                let ketHtml = t.keterangan ? `<small class="text-secondary d-block font-small-1 font-italic mt-25 text-truncate" style="max-width: 170px;" title="${t.keterangan}">${t.keterangan}</small>` : '';
                let linkHtml = t.lampiran_spp ? `<a href="${base_url + t.lampiran_spp}" target="_blank" class="badge badge-light-warning font-small-1 mt-25"><i class="fas fa-file-pdf mr-25"></i> Lampiran</a>` : '';
                sppInfo = `<div>${regHtml}${tglHtml}${ketHtml}${linkHtml}</div>`;
            }

            // Kolom Pencairan Dana
            let cairInfo = '<span class="text-muted font-small-2">-</span>';
            if (t.status === "sudah_cair") {
                let tglCairHtml = t.tanggal_cair ? `<span class="font-small-2 font-weight-bold text-success d-block"><i class="fas fa-check-circle mr-25"></i> ${formatShortDate(t.tanggal_cair)}</span>` : '';
                let cekHtml = t.no_cek ? `<span class="badge badge-light-primary font-small-1 mt-25">Cek: ${t.no_cek}</span>` : '';
                let linkBukti = t.lampiran_bukti_cair ? `<a href="${base_url + t.lampiran_bukti_cair}" target="_blank" class="badge badge-light-success font-small-1 mt-25 ml-25"><i class="fas fa-receipt mr-25"></i> Bukti</a>` : '';
                cairInfo = `<div>${tglCairHtml}${cekHtml}${linkBukti}</div>`;
            }

            html += `
                <tr>
                    <td class="text-center font-weight-bold font-small-2">Termin ${t.termin_ke}</td>
                    <td class="text-center"><span class="badge badge-light-info font-small-2">${parseFloat(t.persen).toFixed(1)}%</span></td>
                    <td class="font-weight-bold text-dark text-right font-small-2">${formatRupiah(t.nominal)}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td>${sppInfo}</td>
                    <td>${cairInfo}</td>
                    <td>${actionBtn}</td>
                </tr>
            `;
        });

        return html;
    }

    function buildSalesCardHtml(fee, isDraft, cardUid) {
        let feeId = fee.id || 0;
        let idSales = fee.id_sales || "";
        let salesName = fee.sales_nama || "";
        let salesKet = fee.sales_keterangan || "";
        let feePersen = cleanPercent(fee.persentase_fee || 0);
        let totalFee = cleanCurrency(fee.total_fee || 0);
        let isManual = parseInt(fee.total_fee_manual || 0) === 1;
        let defaultSkema = (currentDetailData && parseInt(currentDetailData.akad_indent || 0) === 1) ? "100" : "30_60_10";
        let skema = fee.skema_termin || defaultSkema;
        let catatan = fee.catatan || "";
        let isPks = parseInt(fee.has_pks || 0) === 1;

        let isLocked = false;
        if (fee.termin && fee.termin.length > 0) {
            isLocked = fee.termin.some(function (t) {
                return t.status === "pengajuan_spp" || t.status === "sudah_cair";
            });
        }

        let pksBadgeHtml = isPks
            ? '<span class="badge badge-light-success font-small-1 ml-50 card-pks-badge"><i class="fas fa-file-contract mr-25"></i> PKS</span>'
            : '<span class="badge badge-light-secondary font-small-1 ml-50 card-pks-badge">Non-PKS</span>';

        let ketBadgeHtml = salesKet
            ? `<span class="badge badge-light-info font-small-1 ml-50">${salesKet}</span>`
            : '';

        let statusBadgeHtml = "";
        if (fee.status === "selesai") {
            statusBadgeHtml = '<span class="badge badge-success font-small-1 ml-50">Lunas (100%)</span>';
        } else if (fee.status === "proses_termin") {
            statusBadgeHtml = '<span class="badge badge-warning font-small-1 ml-50">Proses Termin</span>';
        } else if (isDraft || !feeId) {
            statusBadgeHtml = '<span class="badge badge-light-secondary font-small-1 ml-50">Draft Baru</span>';
        }

        let deleteBtnHtml = "";
        if (!canManage) {
            deleteBtnHtml = "";
        } else if (isLocked) {
            deleteBtnHtml = '<span class="badge badge-light-warning font-small-1" title="Terkunci karena termin sudah diajukan/cair"><i class="fas fa-lock mr-25"></i> Terkunci</span>';
        } else {
            deleteBtnHtml = `
                <button type="button" class="btn btn-outline-danger btn-sm p-25 px-50 btn-delete-fee-entry" data-id="${feeId}" data-name="${salesName || 'Sales'}">
                    <i class="fas fa-trash-alt mr-25"></i> Hapus Fee Sales
                </button>
            `;
        }

        let lockBannerHtml = isLocked
            ? '<div class="alert alert-warning py-50 font-small-2 mb-1"><i class="fas fa-lock mr-25"></i> Konfigurasi komisi & termin untuk sales ini dikunci karena termin sedang dalam proses pengajuan atau sudah dicairkan.</div>'
            : '';

        let saveBtnHtml = (isLocked || !canManage)
            ? ''
            : `<button type="submit" class="btn btn-primary btn-sm btn-block font-small-2 font-weight-bold" style="height: 34px;">
                    <i class="fas fa-save mr-25"></i> Simpan
               </button>`;

        let tariffHint = isPks ? `Tarif PKS: ${feePersen.toFixed(2)}%` : `Tarif Non-PKS: ${feePersen.toFixed(2)}%`;

        let selectSalesPickerHtml = "";
        if (isDraft) {
            selectSalesPickerHtml = `
                <div class="row mb-1 bg-white p-50 rounded border">
                    <div class="col-md-12">
                        <label class="font-small-2 font-weight-bold text-primary mb-25">
                            <i class="fas fa-search mr-25"></i> Pilih Sales atau Ketik Nama Sales Baru (Non-PKS)
                        </label>
                        <select class="form-control form-control-sm select2-sales-picker" style="width: 100%;">
                            <option value="">-- Pilih Sales atau Ketik Nama Baru --</option>
                        </select>
                        <small class="text-muted font-small-1 d-block mt-25">
                            Ketik nama langsung lalu tekan Enter untuk menambahkan sales baru (otomatis Non-PKS default 1.00%).
                        </small>
                    </div>
                </div>
            `;
        }

        let terminRows = buildTerminRowsHtml(fee.termin || []);

        return `
            <div class="card border mb-2 shadow-none sales-fee-card sales-fee-card-accent" data-fee-id="${feeId}" data-locked="${isLocked ? 1 : 0}" id="${cardUid}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-75 px-1 border-bottom">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="font-weight-bold font-medium-1 text-dark mr-50">
                            <i class="fas fa-user-tie text-primary mr-25"></i> <span class="card-sales-title">${salesName || "Sales Baru"}</span>
                        </span>
                        ${pksBadgeHtml}
                        ${ketBadgeHtml}
                        ${statusBadgeHtml}
                    </div>
                    <div>
                        ${deleteBtnHtml}
                    </div>
                </div>
                <div class="card-body p-1 bg-white">
                    ${lockBannerHtml}
                    <form class="form-fee-entry mb-1 border-bottom pb-1" data-fee-id="${feeId}">
                        <input type="hidden" name="id_mkdt" value="${currentDetailData ? currentDetailData.id_mkdt : ''}">
                        <input type="hidden" name="id_fee_sales" value="${feeId}">
                        <input type="hidden" name="id_sales" class="input-id-sales" value="${idSales}">
                        <input type="hidden" name="sales_nama" class="input-sales-nama" value="${salesName}">
                        <input type="hidden" name="sales_keterangan" value="${salesKet}">

                        ${selectSalesPickerHtml}

                        <div class="row align-items-start form-fee-row-aligned">
                            <div class="col-md-2 form-group mb-50">
                                <div class="form-header-slot">
                                    <label class="font-small-2 font-weight-bold mb-0 text-dark">Komisi (%)</label>
                                    <span class="badge badge-light-primary font-small-1 ml-50 py-0 px-25 text-truncate" style="max-width: 95px;" title="${tariffHint}">${tariffHint}</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="persentase_fee" class="form-control text-right input-fee-persen" value="${feePersen.toFixed(2)}" placeholder="0.00" ${isLocked || !canManage ? 'disabled' : ''}>
                                    <div class="input-group-append"><span class="input-group-text font-small-1">%</span></div>
                                </div>
                            </div>
                            <div class="col-md-3 form-group mb-50">
                                <div class="form-header-slot d-flex justify-content-between align-items-center flex-nowrap">
                                    <label class="font-small-2 font-weight-bold mb-0 text-nowrap mr-25 text-dark">Total Fee (Rp)</label>
                                    <div class="custom-control custom-checkbox font-small-1 ml-auto text-nowrap override-checkbox-wrap">
                                        <input type="checkbox" class="custom-control-input chk-fee-override" id="override-${cardUid}" ${isManual ? 'checked' : ''} ${isLocked || !canManage ? 'disabled' : ''}>
                                        <label class="custom-control-label cursor-pointer font-weight-bold text-primary font-small-1" for="override-${cardUid}">Override</label>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text font-small-1">Rp</span></div>
                                    <input type="text" name="total_fee" class="form-control text-right font-weight-bold num-input input-fee-total" value="${cleanCurrency(totalFee).toLocaleString('id-ID')}" ${isManual && !isLocked && canManage ? '' : 'readonly'} ${isLocked || !canManage ? 'disabled' : ''}>
                                </div>
                            </div>
                            <div class="col-md-2 form-group mb-50">
                                <div class="form-header-slot">
                                    <label class="font-small-2 font-weight-bold mb-0 text-dark">Skema Termin</label>
                                </div>
                                <select name="skema_termin" class="form-control form-control-sm select-fee-skema" ${isLocked || !canManage ? 'disabled' : ''}>
                                    <option value="100" ${skema === '100' ? 'selected' : ''}>100% Sekaligus</option>
                                    <option value="30_60_10" ${skema === '30_60_10' ? 'selected' : ''}>Bertahap (30-60-10)</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-50">
                                <div class="form-header-slot">
                                    <label class="font-small-2 font-weight-bold mb-0 text-dark">Catatan</label>
                                </div>
                                <input type="text" name="catatan" class="form-control form-control-sm input-fee-catatan" value="${catatan}" placeholder="Catatan opsional..." ${isLocked || !canManage ? 'disabled' : ''}>
                            </div>
                            <div class="col-md-2 form-group mb-50 text-right">
                                <div class="form-header-slot">
                                    <label class="mb-0 d-block">&nbsp;</label>
                                </div>
                                ${saveBtnHtml}
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0 table-termin-accent">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 80px;" class="text-center font-small-2">Termin</th>
                                    <th style="width: 60px;" class="text-center font-small-2">%</th>
                                    <th style="width: 120px;" class="text-right font-small-2">Nominal</th>
                                    <th style="width: 110px;" class="text-center font-small-2">Status</th>
                                    <th class="font-small-2">Pengajuan Pencairan</th>
                                    <th class="font-small-2">Pencairan Dana</th>
                                    <th style="width: 140px;" class="text-center font-small-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="tbody-sales-termin">
                                ${terminRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    function renderSalesCards(d) {
        let hargaAjb = cleanCurrency(d.harga_net_ajb) > 0
            ? cleanCurrency(d.harga_net_ajb)
            : (cleanCurrency(d.fee_harga_net_ajb) > 0
                ? cleanCurrency(d.fee_harga_net_ajb)
                : (cleanCurrency(d.harga_jual_net) > 0 ? cleanCurrency(d.harga_jual_net) : cleanCurrency(d.harga_jual)));
        $("#modal-shared-harga-ajb").val(hargaAjb > 0 ? hargaAjb.toLocaleString("id-ID") : "");

        let hasCairTermin = Boolean(d.has_cair_termin) || (d.fee_list || []).some(function (f) {
            return Array.isArray(f.termin) && f.termin.some(function (t) { return t.status === "sudah_cair"; });
        });

        let hasGeneratedTermin = (d.fee_list || []).some(function (f) {
            return Array.isArray(f.termin) && f.termin.length > 0;
        });

        let canEditHarga = Boolean(canManage || canSignMkdt || canSignKeuangan);
        if (!canEditHarga || hasCairTermin) {
            $("#modal-shared-harga-ajb").prop("readonly", true).addClass("bg-light");
        } else {
            $("#modal-shared-harga-ajb").prop("readonly", false).removeClass("bg-light");
        }

        if (canEditHarga && hasGeneratedTermin && !hasCairTermin) {
            $("#btn-recalc-modal-termin").show();
        } else {
            $("#btn-recalc-modal-termin").hide();
        }

        if (!canManage) {
            $("#btn-add-fee-sales-card").hide();
        } else {
            $("#btn-add-fee-sales-card").show();
        }

        // Active Rejection Hint
        if (d.active_rejection && cleanCurrency(d.active_rejection.suggested_harga_net_ajb) > 0) {
            let sugNom = cleanCurrency(d.active_rejection.suggested_harga_net_ajb);
            let rejName = d.active_rejection.rejected_by_name || "Reviewer";
            let rejReason = d.active_rejection.reject_reason || "-";
            $("#modal-unit-rejection-hint").html(`
                <strong><i class="fas fa-exclamation-triangle mr-25"></i> Rekomendasi Revisi Harga:</strong>
                <div>Saran Harga Net AJB: <strong class="text-danger">${formatRupiah(sugNom)}</strong> oleh ${rejName}</div>
                <div class="font-italic mt-25">"${rejReason}"</div>
            `).show();
        } else {
            $("#modal-unit-rejection-hint").empty().hide();
        }

        let resAjbModal = resolveHargaJualNetAjb(d);
        if (hasCairTermin) {
            $("#modal-shared-harga-ajb-hint").html('<span class="badge badge-light-danger font-small-1 mr-25"><i class="fas fa-lock mr-25"></i> Terkunci</span> Tidak dapat mengubah Harga Jual Net AJB karena sudah ada pengajuan/termin yang cair.');
        } else if (hasGeneratedTermin && !hasCairTermin) {
            $("#modal-shared-harga-ajb-hint").html('<span class="badge badge-light-success font-small-1 mr-25"><i class="fas fa-edit mr-25"></i> Dapat Diedit</span> Ubah harga net lalu klik "Perbaharui Nominal Tiap Termin".');
        } else if (resAjbModal.isEstimasi) {
            $("#modal-shared-harga-ajb-hint").html('<span class="badge badge-light-warning font-small-1 mr-25">Estimasi Harga Jual Net</span> Berlaku seragam untuk seluruh sales.');
        } else {
            $("#modal-shared-harga-ajb-hint").html('Berlaku seragam untuk seluruh sales di unit ini.');
        }

        let container = $("#modal-sales-cards-container");
        container.empty();

        let feeList = d.fee_list || [];

        if (feeList.length === 0) {
            // Jika belum ada fee_sales tersimpan sama sekali di DB
            let defSalesName = d.master_sales_nama || d.sales_konsumen || "";
            if (defSalesName) {
                let defaultPersen = parseInt(d.is_subsidi) === 1 ? cleanPercent(d.sales_fee_subsidi || 1.5) : cleanPercent(d.sales_fee_komersil || 2.0);
                let defaultTotal = Math.round((defaultPersen / 100) * hargaAjb);
                let defaultSkema = parseInt(d.akad_indent || 0) === 1 ? "100" : "30_60_10";
                let draftFee = {
                    id: 0,
                    id_sales: d.id_sales || "",
                    sales_nama: defSalesName,
                    sales_keterangan: d.keterangan_sales || "",
                    has_pks: d.sales_has_pks || 0,
                    persentase_fee: defaultPersen,
                    total_fee: defaultTotal,
                    total_fee_manual: 0,
                    skema_termin: defaultSkema,
                    status: "draft",
                    catatan: "",
                    termin: []
                };
                let cardHtml = buildSalesCardHtml(draftFee, false, "card-sales-init");
                container.append(cardHtml);
            } else {
                container.html(`
                    <div class="text-center py-4 bg-light rounded border border-dashed text-muted mb-2">
                        <i class="fas fa-users fa-2x mb-50 text-secondary"></i>
                        <p class="mb-50 font-weight-bold">Belum ada fee sales untuk kavling ini.</p>
                        <button type="button" class="btn btn-primary btn-sm btn-trigger-add-sales">
                            <i class="fas fa-user-plus mr-25"></i> Tambah Sales Pertama
                        </button>
                    </div>
                `);
            }
        } else {
            feeList.forEach(function (fee, idx) {
                let cardHtml = buildSalesCardHtml(fee, false, `card-sales-${fee.id || idx}`);
                container.append(cardHtml);
            });
        }

        updateSalesCardsCount();
    }

    function appendNewDraftSalesCard() {
        let hargaAjb = cleanCurrency($("#modal-shared-harga-ajb").val());
        let defaultPersen = 1.00; // Default Non-PKS untuk sales tambahan
        let defaultTotal = Math.round((defaultPersen / 100) * hargaAjb);
        let defaultSkema = (currentDetailData && parseInt(currentDetailData.akad_indent || 0) === 1) ? "100" : "30_60_10";

        let draftFee = {
            id: 0,
            id_sales: "",
            sales_nama: "",
            sales_keterangan: "",
            has_pks: 0,
            persentase_fee: defaultPersen,
            total_fee: defaultTotal,
            total_fee_manual: 0,
            skema_termin: defaultSkema,
            status: "draft",
            catatan: "",
            termin: []
        };

        let cardUid = "card-draft-" + Date.now();
        let cardHtml = buildSalesCardHtml(draftFee, true, cardUid);

        let container = $("#modal-sales-cards-container");
        if (container.find(".sales-fee-card").length === 0) {
            container.empty();
        }
        container.prepend(cardHtml);
        $("#modal-fee-sales .modal-body").scrollTop(0);

        let $card = $(`#${cardUid}`);
        let $select = $card.find(".select2-sales-picker");

        // Populate options dari activeSalesList
        activeSalesList.forEach(function (s) {
            let opt = new Option(`${s.nama} (${parseInt(s.has_pks) === 1 ? 'PKS' : 'Non-PKS'})`, s.id, false, false);
            $(opt).attr("data-has-pks", s.has_pks || 0);
            $(opt).attr("data-subsidi", s.fee_subsidi || 1.5);
            $(opt).attr("data-komersil", s.fee_komersil || 2.0);
            $select.append(opt);
        });

        $select.select2({
            dropdownParent: $("#modal-fee-sales"),
            tags: true,
            width: "100%",
            placeholder: "Pilih sales atau ketik nama sales baru...",
            createTag: function (params) {
                let term = $.trim(params.term);
                if (term === "") return null;
                return {
                    id: term,
                    text: term + " (Sales Baru - Non-PKS)",
                    newTag: true
                };
            }
        });

        $select.on("change", function () {
            let val = $(this).val();
            let dataObj = $(this).select2("data")[0];
            let currentHarga = cleanCurrency($("#modal-shared-harga-ajb").val());

            if (dataObj && dataObj.newTag) {
                // Sales Baru (diketik manual)
                $card.find(".input-id-sales").val("");
                $card.find(".input-sales-nama").val(val);
                $card.find(".card-sales-title").text(val);
                $card.find(".card-pks-badge").html('<span class="badge badge-light-secondary font-small-1 ml-50">Non-PKS</span>');
                $card.find(".input-fee-persen").val("1.00");
                $card.find(".tarif-hint-label").text("Tarif Default Non-PKS: 1.00%");
                if (!$card.find(".chk-fee-override").is(":checked")) {
                    let tot = Math.round(0.01 * currentHarga);
                    $card.find(".input-fee-total").val(tot.toLocaleString("id-ID"));
                    $card.find(".card-total-fee-badge").text(formatRupiah(tot));
                }
            } else {
                // Pilih sales dari master
                let matched = activeSalesList.find(s => String(s.id) === String(val));
                if (matched) {
                    $card.find(".input-id-sales").val(matched.id);
                    $card.find(".input-sales-nama").val(matched.nama);
                    $card.find(".card-sales-title").text(matched.nama);
                    let isPks = parseInt(matched.has_pks) === 1;
                    $card.find(".card-pks-badge").html(isPks ? '<span class="badge badge-light-success font-small-1 ml-50"><i class="fas fa-file-contract mr-25"></i> PKS</span>' : '<span class="badge badge-light-secondary font-small-1 ml-50">Non-PKS</span>');
                    let defaultP = currentDetailData && parseInt(currentDetailData.is_subsidi) === 1
                        ? cleanPercent(matched.fee_subsidi || 1.5)
                        : cleanPercent(matched.fee_komersil || 2.0);
                    $card.find(".input-fee-persen").val(defaultP.toFixed(2));
                    $card.find(".tarif-hint-label").text(isPks ? `Tarif PKS: ${defaultP.toFixed(2)}%` : `Tarif Non-PKS: ${defaultP.toFixed(2)}%`);
                    if (!$card.find(".chk-fee-override").is(":checked")) {
                        let tot = Math.round((defaultP / 100) * currentHarga);
                        $card.find(".input-fee-total").val(tot.toLocaleString("id-ID"));
                        $card.find(".card-total-fee-badge").text(formatRupiah(tot));
                    }
                }
            }
        });

        updateSalesCardsCount();
        $select.select2("open");
    }

    $("#btn-add-fee-sales-card").on("click", function () {
        appendNewDraftSalesCard();
    });

    $(document).on("click", ".btn-trigger-add-sales", function () {
        appendNewDraftSalesCard();
    });

    // Perubahan Harga Net AJB Kavling (Berlaku Seragam ke Seluruh Card Sales yang Tidak Terkunci)
    $("#modal-shared-harga-ajb").on("input change", function () {
        let sharedHarga = cleanCurrency($(this).val());
        $(".sales-fee-card").each(function () {
            let $card = $(this);
            let isLocked = parseInt($card.data("locked") || 0) === 1;
            let isOverride = $card.find(".chk-fee-override").is(":checked");
            if (!isLocked && !isOverride) {
                let persen = cleanPercent($card.find(".input-fee-persen").val());
                let tot = Math.round((persen / 100) * sharedHarga);
                $card.find(".input-fee-total").val(tot.toLocaleString("id-ID"));
                $card.find(".card-total-fee-badge").text(formatRupiah(tot));
            }
        });
    });

    // Recalculate Termin dari Modal Detail Fee Kavling
    $("#btn-recalc-modal-termin").on("click", function () {
        if (!currentDetailData || !currentDetailData.id_mkdt) return;
        let idMkdt = currentDetailData.id_mkdt;
        let hargaAjb = cleanCurrency($("#modal-shared-harga-ajb").val());

        if (hargaAjb <= 0) {
            Swal.fire("Perhatian", "Harga Jual Net AJB harus bernilai lebih dari Rp 0.", "warning");
            return;
        }

        Swal.fire({
            title: "Perbaharui Nominal Tiap Termin?",
            html: `Nominal pencairan tiap termin untuk unit ini akan dihitung ulang berdasarkan Harga Jual Net AJB <strong>${formatRupiah(hargaAjb)}</strong>.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2057a3",
            cancelButtonColor: "#82868b",
            confirmButtonText: "Ya, Perbaharui",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                let data = {
                    id_mkdt: idMkdt,
                    harga_net_ajb: hargaAjb
                };
                data[csrfName] = csrfHash;

                $("#btn-recalc-modal-termin").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-25"></span> Menghitung...');

                $.ajax({
                    url: base_url + "api/sales-external/recalculate-termin",
                    type: "POST",
                    data: data,
                    dataType: "json",
                    success: function (res) {
                        if (res.token) csrfHash = res.token;
                        $("#btn-recalc-modal-termin").prop("disabled", false).html('<i class="fas fa-sync-alt mr-25"></i> Perbaharui Nominal Tiap Termin');

                        if (res.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Nominal Berhasil Diperbarui!",
                                text: res.messages || "Nominal termin berhasil dihitung ulang.",
                                timer: 1500,
                                showConfirmButton: false,
                            });
                            openFeeModal(idMkdt);
                            dtFee.ajax.reload(null, false);
                            if (dtGroup) dtGroup.ajax.reload(null, false);
                        } else {
                            Swal.fire("Gagal", res.messages || "Gagal memperbarui nominal termin.", "error");
                        }
                    },
                    error: function (xhr) {
                        $("#btn-recalc-modal-termin").prop("disabled", false).html('<i class="fas fa-sync-alt mr-25"></i> Perbaharui Nominal Tiap Termin');
                        let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                        Swal.fire("Error", err, "error");
                    }
                });
            }
        });
    });

    // Perubahan Persentase Komisi per Card
    $(document).on("input change", ".input-fee-persen", function () {
        let $card = $(this).closest(".sales-fee-card");
        let isOverride = $card.find(".chk-fee-override").is(":checked");
        if (!isOverride) {
            let sharedHarga = cleanCurrency($("#modal-shared-harga-ajb").val());
            let persen = cleanPercent($(this).val());
            let tot = Math.round((persen / 100) * sharedHarga);
            $card.find(".input-fee-total").val(tot.toLocaleString("id-ID"));
            $card.find(".card-total-fee-badge").text(formatRupiah(tot));
        }
    });

    // Checkbox Override Manual Total Fee per Card
    $(document).on("change", ".chk-fee-override", function () {
        let $card = $(this).closest(".sales-fee-card");
        let $totalInput = $card.find(".input-fee-total");
        if ($(this).is(":checked")) {
            $totalInput.prop("readonly", false).focus();
        } else {
            $totalInput.prop("readonly", true);
            let sharedHarga = cleanCurrency($("#modal-shared-harga-ajb").val());
            let persen = cleanPercent($card.find(".input-fee-persen").val());
            let tot = Math.round((persen / 100) * sharedHarga);
            $totalInput.val(tot.toLocaleString("id-ID"));
            $card.find(".card-total-fee-badge").text(formatRupiah(tot));
        }
    });

    // Input manual Total Fee per Card
    $(document).on("input change", ".input-fee-total", function () {
        let $card = $(this).closest(".sales-fee-card");
        let tot = cleanCurrency($(this).val());
        $card.find(".card-total-fee-badge").text(formatRupiah(tot));
    });

    // Submit Form Fee Entry per Card (Simpan & Generate Termin)
    $(document).on("submit", ".form-fee-entry", function (e) {
        e.preventDefault();
        let $form = $(this);
        let $card = $form.closest(".sales-fee-card");
        let $btn = $form.find("button[type='submit']");

        let idMkdt = $form.find("input[name='id_mkdt']").val();
        let idFeeSales = $form.find("input[name='id_fee_sales']").val();
        let idSales = $form.find(".input-id-sales").val();
        let salesNama = $form.find(".input-sales-nama").val();
        let salesKet = $form.find("input[name='sales_keterangan']").val();

        let $select2 = $form.find(".select2-sales-picker");
        if ($select2.length > 0) {
            let selVal = $select2.val();
            if (!selVal) {
                Swal.fire("Perhatian", "Pilih atau ketik nama sales terlebih dahulu.", "warning");
                return;
            }
            let matched = activeSalesList.find(s => String(s.id) === String(selVal));
            if (matched) {
                idSales = matched.id;
                salesNama = matched.nama;
            } else {
                idSales = "";
                salesNama = selVal;
            }
        }

        if (!salesNama) {
            Swal.fire("Perhatian", "Nama sales wajib diisi.", "warning");
            return;
        }

        let sharedHargaAjb = cleanCurrency($("#modal-shared-harga-ajb").val());
        let persenFee = cleanPercent($form.find(".input-fee-persen").val());
        let totalFee = cleanCurrency($form.find(".input-fee-total").val());
        let isManual = $form.find(".chk-fee-override").is(":checked") ? 1 : 0;
        let skemaTermin = $form.find(".select-fee-skema").val();
        let catatan = $form.find(".input-fee-catatan").val();

        let postData = {
            [csrfName]: csrfHash,
            id_mkdt: idMkdt,
            id_fee_sales: idFeeSales || 0,
            id_sales: idSales || "",
            sales_nama: salesNama,
            sales_keterangan: salesKet || "",
            harga_net_ajb: sharedHargaAjb,
            persentase_fee: persenFee,
            total_fee: totalFee,
            total_fee_manual: isManual,
            skema_termin: skemaTermin,
            catatan: catatan
        };

        $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-25"></span> Menyimpan...');

        $.post(base_url + "api/sales-external/save-fee-header", postData, function (res) {
            if (res.token) csrfHash = res.token;
            $btn.prop("disabled", false).html('<i class="fas fa-save mr-25"></i> Simpan & Generate');

            if (res.success) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.messages || "Konfigurasi fee sales berhasil disimpan.",
                    timer: 1500,
                    showConfirmButton: false,
                });
                openFeeModal(idMkdt);
                dtFee.ajax.reload(null, false);
            } else {
                Swal.fire("Gagal", res.messages || "Gagal menyimpan fee sales.", "error");
            }
        }).fail(function (xhr) {
            $btn.prop("disabled", false).html('<i class="fas fa-save mr-25"></i> Simpan & Generate');
            let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
            Swal.fire("Error", err, "error");
        });
    });

    // Hapus Fee Sales Entry
    $(document).on("click", ".btn-delete-fee-entry", function () {
        let feeId = $(this).data("id");
        let salesName = $(this).data("name") || "Sales ini";
        let $card = $(this).closest(".sales-fee-card");

        if (!feeId || feeId === "0" || feeId === 0) {
            $card.remove();
            updateSalesCardsCount();
            return;
        }

        Swal.fire({
            title: "Hapus Fee Sales?",
            text: `Apakah Anda yakin ingin menghapus fee sales untuk ${salesName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ea5455",
            cancelButtonColor: "#82868b",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + "api/sales-external/delete-fee-entry/" + feeId,
                    type: "POST",
                    data: { [csrfName]: csrfHash },
                    dataType: "json",
                    success: function (res) {
                        if (res.token) csrfHash = res.token;
                        if (res.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Terhapus!",
                                text: res.message || "Fee sales berhasil dihapus.",
                                timer: 1500,
                                showConfirmButton: false,
                            });
                            if (currentDetailData && currentDetailData.id_mkdt) {
                                openFeeModal(currentDetailData.id_mkdt);
                            }
                            dtFee.ajax.reload(null, false);
                        } else {
                            Swal.fire("Gagal", res.message || "Gagal menghapus fee sales.", "error");
                        }
                    },
                    error: function (xhr) {
                        let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                        Swal.fire("Error", err, "error");
                    }
                });
            }
        });
    });

    function openFeeModal(idMkdt, rowData) {
        if (typeof modalInitListener === "function") {
            modalInitListener("#modal-fee-sales");
        } else if (typeof initModalListener === "function") {
            initModalListener("#modal-fee-sales");
        }

        if (rowData) {
            let initAjb = cleanNum(rowData.harga_net_ajb) > 0
                ? cleanNum(rowData.harga_net_ajb)
                : (cleanNum(rowData.harga_jual_net) > 0 ? cleanNum(rowData.harga_jual_net) : cleanNum(rowData.harga_jual));
            $("#modal-shared-harga-ajb").val(initAjb > 0 ? initAjb.toLocaleString("id-ID") : "");
            $("#modal-info-konsumen").text(rowData.nama_konsumen || "Memuat...");
            $("#modal-info-hp").text("HP: " + (rowData.hp_konsumen || "-"));
            $("#modal-info-unit").text(`${rowData.nama_jalan || "-"} • Kavling ${rowData.no_kavling || "-"} (${rowData.tipe_rumah || "-"})`);
        } else {
            $("#modal-shared-harga-ajb").val("");
            $("#modal-info-konsumen").text("Memuat...");
        }

        $("#modal-sales-cards-container").html('<div class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat data fee sales...</div>');
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
                let isAjbModal = String(d.is_ajb || "").trim().toUpperCase();
                let statusAkadBadge = (isAjbModal === "AJB" || isAjbModal === "1") ? "AJB" : "PPJB";
                $("#modal-info-proyek").text(`${d.nama_proyek || "-"} • ${subsidiText} • ${statusAkadBadge}`);

                // Realisasi Pencairan Akad Keuangan
                let totHasil = cleanNum(d.total_hasil_akad);
                let totCairKeu = cleanNum(d.total_cair_keuangan);
                let pctKeu = totHasil > 0 ? Math.min(100, Math.round((totCairKeu / totHasil) * 100)) : 0;
                $("#modal-keu-progress").css("width", pctKeu + "%").attr("class", pctKeu >= 100 ? "progress-bar bg-success" : "progress-bar bg-primary");
                $("#modal-keu-percent").text(pctKeu + "%");
                $("#modal-keu-nominal").text(`${formatRupiah(totCairKeu)} / ${formatRupiah(totHasil)}`);

                // Render Cards Multi Sales
                renderSalesCards(d);
            }
        });
    }

    // Buka Modal Pengajuan Pencairan
    $(document).on("click", ".btn-open-spp", function () {
        let idTermin = $(this).data("id");
        let terminKe = $(this).data("termin");
        let nom = $(this).data("nominal");

        $("#form-spp")[0].reset();
        $("#spp-id_termin").val(idTermin);
        $("#spp-termin-label").text("Termin " + terminKe);
        $("#spp-nominal-display").val(formatRupiah(nom));
        $("#spp-tanggal").val(new Date().toISOString().slice(0, 10));
        $("#spp-file-preview").empty().addClass("d-none");
        $("#modal-spp").modal("show");
    });

    // Submit Pengajuan Pencairan
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
                $("#btn-submit-spp").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan Pencairan');

                if (res.success) {
                    $("#modal-spp").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pengajuan Terkirim!",
                        text: res.messages || "Pengajuan pencairan berhasil dicatat.",
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
                        text: res.messages || "Gagal mengajukan pencairan.",
                    });
                }
            },
            error: function (xhr) {
                $("#btn-submit-spp").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Kirim Pengajuan Pencairan');
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
        $("#cair-file-preview").empty().addClass("d-none");
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
                    if ($("#modal-group-detail").is(":visible") && currentGroupDetail && currentGroupDetail.id) {
                        openGroupDetailModal(currentGroupDetail.id);
                    }
                    dtFee.ajax.reload(null, false);
                    if (dtGroup) dtGroup.ajax.reload(null, false);
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


    // Buka Modal Edit Pencairan Dana
    $(document).on("click", ".btn-open-edit-cair", function () {
        let idTermin = $(this).data("id");
        let terminKe = $(this).data("termin");
        let nom = $(this).data("nominal");
        let tgl = $(this).data("tanggal");
        let nocek = $(this).data("nocek");
        let ket = $(this).data("ket");
        let file = $(this).data("file");

        $("#form-edit-cair")[0].reset();
        $("#edit-cair-id_termin").val(idTermin);
        $("#edit-cair-termin-label").text("Termin " + terminKe + " (" + formatRupiah(nom) + ")");
        $("#edit-cair-tanggal").val(tgl);
        $("#edit-cair-no_cek").val(nocek);
        $("#edit-cair-keterangan").val(ket);
        $("#edit-cair-alasan").val("");
        $("#edit-cair-file-preview").empty().addClass("d-none");

        if (file) {
            $("#edit-cair-current-file").html(`<span class="text-success"><i class="fas fa-paperclip mr-25"></i> File saat ini: <a href="${base_url + file}" target="_blank">${file.split("/").pop()}</a></span>`);
        } else {
            $("#edit-cair-current-file").html('<span class="text-muted">Belum ada file bukti transfer.</span>');
        }

        $("#modal-edit-cair").modal("show");
    });

    // Submit Edit Pencairan Dana
    $("#form-edit-cair").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-edit-cair").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/edit-cair",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-edit-cair").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Perubahan Pencairan');

                if (res.success) {
                    $("#modal-edit-cair").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pencairan Diperbarui!",
                        text: res.messages || "Data pencairan berhasil diperbarui.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    if (currentDetailData && currentDetailData.id_mkdt) {
                        openFeeModal(currentDetailData.id_mkdt);
                    }
                    if ($("#modal-group-detail").is(":visible") && currentGroupDetail && currentGroupDetail.id) {
                        openGroupDetailModal(currentGroupDetail.id);
                    }
                    dtFee.ajax.reload(null, false);
                    if (dtGroup) dtGroup.ajax.reload(null, false);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text: res.messages || "Gagal memperbarui pencairan.",
                    });
                }
            },
            error: function (xhr) {
                $("#btn-submit-edit-cair").prop("disabled", false).html('<i class="fas fa-save mr-50"></i> Simpan Perubahan Pencairan');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: err,
                });
            },
        });
    });

    // ─────────────────────────────────────────────────────────────
    // PENGURUSAN BATCH / GRUP PENGAJUAN PENCAIRAN
    // ─────────────────────────────────────────────────────────────

    // Buka Modal Buat Pengajuan Batch
    $("#btn-batch-pengajuan").on("click", function () {
        let mkdtIds = Object.keys(selectedMkdtMap);
        if (mkdtIds.length === 0) {
            Swal.fire("Perhatian", "Pilih setidaknya 1 kavling terlebih dahulu.", "warning");
            return;
        }

        if (typeof window.initModalListener === "function") {
            window.initModalListener("#modal-create-group");
        }

        $("#form-create-group")[0].reset();
        if ($("#batch-tgl_pengajuan_sales")[0] && $("#batch-tgl_pengajuan_sales")[0]._flatpickr) {
            $("#batch-tgl_pengajuan_sales")[0]._flatpickr.clear();
        } else {
            $("#batch-tgl_pengajuan_sales").val("");
        }
        if ($("#batch-tgl_pengajuan")[0] && $("#batch-tgl_pengajuan")[0]._flatpickr) {
            $("#batch-tgl_pengajuan")[0]._flatpickr.clear();
        } else {
            $("#batch-tgl_pengajuan").val("");
        }
        $("#batch-sign-method").val("canvas").trigger("change");
        if (batchCanvas) batchCanvas.clear();
        $("#batch-lampiran_spp").val("").next(".custom-file-label").text("Pilih file");

        $("#tbody-batch-prepare").html('<tr><td colspan="8" class="text-center py-2 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat kavling terpilih...</td></tr>');
        $("#modal-create-group").modal("show");

        $.post(base_url + "api/sales-external/prepare-group", {
            [csrfName]: csrfHash,
            mkdt_ids: mkdtIds
        }, function (res) {
            if (res.token) csrfHash = res.token;
            if (res.success && res.data) {
                renderBatchPrepareTable(res.data);
            }
        });
    });

    function renderBatchPrepareTable(list) {
        if (!list || list.length === 0) {
            $("#tbody-batch-prepare").html('<tr><td colspan="8" class="text-center py-2 text-muted">Tidak ada data unit terpilih.</td></tr>');
            $("#batch-summary-badge").text("Total: Rp 0");
            return;
        }

        let html = "";
        let runningTotal = 0;
        let totalTerminCount = 0;

        list.forEach(function (row, idx) {
            let ajbRes = resolveHargaJualNetAjb(row);
            let hargaAjb = ajbRes.nominal;
            let persen = cleanPercent(row.fee_persentase) > 0 ? cleanPercent(row.fee_persentase) : (parseInt(row.is_subsidi) === 1 ? cleanPercent(row.sales_fee_subsidi || 1.5) : cleanPercent(row.sales_fee_komersil || 2.0));
            let totalFee = cleanNum(row.fee_total) > 0 ? cleanNum(row.fee_total) : Math.round((persen / 100) * hargaAjb);
            let skema = row.fee_skema_termin || "30_60_10";

            let checkboxesHtml = "";
            let rowSelectedNominal = 0;
            let rowCheckedCount = 0;

            if (row.termin && row.termin.length > 0) {
                let firstUnpaidChecked = false;
                row.termin.forEach(function (t) {
                    let isCair = (t.status === "sudah_cair");
                    let isSpp = (t.status === "pengajuan_spp");
                    let disabled = (isCair || isSpp) ? "disabled" : "";
                    let badgeStatus = "";
                    if (isCair) {
                        badgeStatus = '<span class="badge badge-light-success font-small-1 py-0 px-25 ml-25"><i class="fas fa-check-circle"></i> Cair</span>';
                    } else if (isSpp) {
                        badgeStatus = '<span class="badge badge-light-warning font-small-1 py-0 px-25 ml-25"><i class="fas fa-clock"></i> Diajukan</span>';
                    }

                    // Default: centang termin yang belum diproses pertama kali
                    let checked = "";
                    if (!isCair && !isSpp && !firstUnpaidChecked) {
                        checked = "checked";
                        rowSelectedNominal += cleanNum(t.nominal);
                        rowCheckedCount++;
                        firstUnpaidChecked = true;
                    }

                    checkboxesHtml += `
                        <div class="custom-control custom-checkbox custom-control-primary mb-25">
                            <input type="checkbox" class="custom-control-input chk-batch-termin"
                                id="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-${t.termin_ke}"
                                data-mkdt="${row.id_mkdt}"
                                data-fee-sales="${row.id_fee_sales || 0}"
                                data-id-termin="${t.id || 0}"
                                data-termin="${t.termin_ke}"
                                data-nominal="${t.nominal}"
                                ${disabled} ${checked}>
                            <label class="custom-control-label font-small-2 d-flex justify-content-between align-items-center" for="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-${t.termin_ke}">
                                <span><strong>Termin ${t.termin_ke}</strong> (${parseFloat(t.persen)}%) - ${formatRupiah(t.nominal)}</span>
                                ${badgeStatus}
                            </label>
                        </div>
                    `;
                });

                if (!firstUnpaidChecked && rowCheckedCount === 0) {
                    checkboxesHtml = '<div class="text-muted font-small-2 font-italic mb-25"><i class="fas fa-info-circle mr-25"></i> Semua termin sudah diproses/cair</div>' + checkboxesHtml;
                }
            } else {
                // Belum ada termin dibangkitkan (generate on-the-fly)
                let nom1 = Math.round(0.30 * totalFee);
                let nom2 = Math.round(0.60 * totalFee);
                let nom3 = totalFee - nom1 - nom2;

                rowSelectedNominal = nom1;
                rowCheckedCount = 1;

                checkboxesHtml = `
                    <div class="custom-control custom-checkbox custom-control-primary mb-25">
                        <input type="checkbox" class="custom-control-input chk-batch-termin"
                            id="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-1"
                            data-mkdt="${row.id_mkdt}"
                            data-fee-sales="${row.id_fee_sales || 0}"
                            data-id-termin="0"
                            data-termin="1"
                            data-nominal="${nom1}"
                            checked>
                        <label class="custom-control-label font-small-2" for="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-1">
                            <strong>Termin 1</strong> (30%) - ${formatRupiah(nom1)}
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-primary mb-25">
                        <input type="checkbox" class="custom-control-input chk-batch-termin"
                            id="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-2"
                            data-mkdt="${row.id_mkdt}"
                            data-fee-sales="${row.id_fee_sales || 0}"
                            data-id-termin="0"
                            data-termin="2"
                            data-nominal="${nom2}">
                        <label class="custom-control-label font-small-2" for="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-2">
                            <strong>Termin 2</strong> (60%) - ${formatRupiah(nom2)}
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-primary mb-25">
                        <input type="checkbox" class="custom-control-input chk-batch-termin"
                            id="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-3"
                            data-mkdt="${row.id_mkdt}"
                            data-fee-sales="${row.id_fee_sales || 0}"
                            data-id-termin="0"
                            data-termin="3"
                            data-nominal="${nom3}">
                        <label class="custom-control-label font-small-2" for="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-3">
                            <strong>Termin 3</strong> (10%) - ${formatRupiah(nom3)}
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-info border-top pt-25 mt-25">
                        <input type="checkbox" class="custom-control-input chk-batch-termin chk-skema-100"
                            id="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-100"
                            data-mkdt="${row.id_mkdt}"
                            data-fee-sales="${row.id_fee_sales || 0}"
                            data-id-termin="0"
                            data-termin="1"
                            data-nominal="${totalFee}"
                            data-skema100="1">
                        <label class="custom-control-label font-small-2 text-info font-weight-bold" for="chk-term-${row.id_mkdt}-${row.id_fee_sales || 0}-100">
                            100% Sekaligus - ${formatRupiah(totalFee)}
                        </label>
                    </div>
                `;
            }

            runningTotal += rowSelectedNominal;
            totalTerminCount += rowCheckedCount;

            let sName = row.fee_sales_nama || row.sales_nama || row.master_sales_nama || row.sales_konsumen || "-";
            let pksBadge = parseInt(row.sales_has_pks || 0) === 1
                ? '<span class="badge badge-light-success font-small-1 ml-25">PKS</span>'
                : '<span class="badge badge-light-secondary font-small-1 ml-25">Non-PKS</span>';

            let ajbCellHtml = ajbRes.isEstimasi
                ? `<div><span class="font-weight-bold text-secondary">${formatRupiah(hargaAjb)}</span><span class="badge badge-light-warning font-small-1 d-block mt-25">Estimasi</span></div>`
                : `<span class="font-weight-bold text-primary">${formatRupiah(hargaAjb)}</span>`;

            html += `
                <tr class="batch-prepare-row" data-mkdt="${row.id_mkdt}">
                    <td class="text-center">${idx + 1}</td>
                    <td>
                        <span class="font-weight-bold d-block text-dark">${row.nama_konsumen || "-"}</span>
                        <small class="text-muted font-small-2">${row.nama_jalan || "-"} • No. ${row.no_kavling || "-"}</small>
                    </td>
                    <td><div><span class="font-weight-bold">${sName}</span>${pksBadge}</div></td>
                    <td class="text-right">${ajbCellHtml}</td>
                    <td class="text-right font-weight-bold text-success">${formatRupiah(totalFee)}</td>
                    <td>
                        <div class="batch-termin-container p-50 bg-light rounded border text-left">
                            ${checkboxesHtml}
                        </div>
                    </td>
                    <td class="text-right">
                        <span class="font-weight-bold text-primary d-block batch-row-nominal">${formatRupiah(rowSelectedNominal)}</span>
                        <small class="text-muted font-small-1 batch-row-count">${rowCheckedCount} termin dipilih</small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm p-25 btn-remove-batch-item" data-id="${row.id_mkdt}" title="Hapus kavling dari pengajuan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $("#tbody-batch-prepare").html(html);
        $("#batch-summary-badge").text("Total Pengajuan: " + formatRupiah(runningTotal) + " (" + totalTerminCount + " Termin)");
    }

    // Ubah pilihan checkbox termin di modal batch
    $(document).on("change", ".chk-batch-termin", function () {
        let $row = $(this).closest("tr");
        let isSkema100 = $(this).hasClass("chk-skema-100");

        if (isSkema100 && $(this).is(":checked")) {
            // Jika pilih 100% sekaligus, uncheck termin bertahap di baris ini
            $row.find(".chk-batch-termin:not(.chk-skema-100)").prop("checked", false);
        } else if (!isSkema100 && $(this).is(":checked")) {
            // Jika pilih termin bertahap, uncheck 100% sekaligus di baris ini
            $row.find(".chk-skema-100").prop("checked", false);
        }

        // Hitung ulang nominal terpilih pada baris ini
        let rowNominal = 0;
        let rowChecked = 0;
        $row.find(".chk-batch-termin:checked").each(function () {
            rowNominal += cleanNum($(this).data("nominal"));
            rowChecked++;
        });

        $row.find(".batch-row-nominal").text(formatRupiah(rowNominal));
        $row.find(".batch-row-count").text(rowChecked + " termin dipilih");

        // Hitung ulang grand total
        recalculateBatchGrandTotal();
    });

    function recalculateBatchGrandTotal() {
        let grand = 0;
        let count = 0;
        $("#tbody-batch-prepare .chk-batch-termin:checked").each(function () {
            grand += cleanNum($(this).data("nominal"));
            count++;
        });
        $("#batch-summary-badge").text("Total Pengajuan: " + formatRupiah(grand) + " (" + count + " Termin)");
    }

    // Hapus item dari daftar batch
    $(document).on("click", ".btn-remove-batch-item", function () {
        let id = $(this).data("id");
        delete selectedMkdtMap[id];
        $(`#chk-mkdt-${id}`).prop("checked", false);
        updateBatchButton();
        $(this).closest("tr").remove();

        // Hitung ulang
        recalculateBatchGrandTotal();

        let remainingRows = $("#tbody-batch-prepare tr.batch-prepare-row").length;
        if (remainingRows === 0) {
            closeModalWithoutConfirmation("#modal-create-group");
        }
    });

    // Submit Buat Pengajuan Batch
    $("#form-create-group").on("submit", function (e) {
        e.preventDefault();

        let tglSales = $("#batch-tgl_pengajuan_sales").val().trim();
        if (!tglSales) {
            Swal.fire("Perhatian", "Tanggal Pengajuan dari Sales wajib diisi.", "warning");
            return;
        }

        let tgl = $("#batch-tgl_pengajuan").val().trim();
        if (!tgl) {
            Swal.fire("Perhatian", "Tanggal Pengajuan Pencairan wajib diisi.", "warning");
            return;
        }

        let signMethod = $("#batch-sign-method").val();
        let signData = "";
        if (signMethod === "canvas") {
            signData = batchCanvas ? batchCanvas.data() : "";
            if (!signData) {
                Swal.fire("Perhatian", "Silakan bubuhkan tanda tangan pembuat pengajuan pada canvas.", "warning");
                return;
            }
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);
        fd.append("id_proyek", (typeof activeProyekId === "function" ? activeProyekId() : (window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : "")) || "");
        if (signMethod === "canvas" && signData) {
            fd.append("signature_data", signData);
        }

        // Kumpulkan data items dari setiap checkbox termin yang dicentang (bisa lebih dari 1 termin per kavling & per sales)
        let itemIndex = 0;
        $("#tbody-batch-prepare .chk-batch-termin:checked").each(function () {
            let idMkdt = $(this).data("mkdt");
            let idFeeSales = $(this).data("fee-sales") || 0;
            let idTermin = $(this).data("id-termin") || 0;
            let terminKe = $(this).data("termin");
            let skema100 = $(this).data("skema100") ? 1 : 0;

            fd.append(`items[${itemIndex}][id_mkdt]`, idMkdt);
            fd.append(`items[${itemIndex}][id_fee_sales]`, idFeeSales);
            fd.append(`items[${itemIndex}][id_termin]`, idTermin);
            fd.append(`items[${itemIndex}][termin_ke]`, terminKe);
            if (skema100) {
                fd.append(`items[${itemIndex}][skema_100]`, 1);
            }
            itemIndex++;
        });

        if (itemIndex === 0) {
            Swal.fire("Perhatian", "Pilih setidaknya 1 termin untuk diajukan.", "warning");
            return;
        }

        $("#btn-submit-batch").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/create-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-batch").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan & Kirim Pengajuan');

                if (res.success) {
                    closeModalWithoutConfirmation("#modal-create-group");
                    selectedMkdtMap = {};
                    $("#check-all-fee").prop("checked", false);
                    updateBatchButton();

                    Swal.fire({
                        icon: "success",
                        title: "Pengajuan Dibuat!",
                        text: res.messages || "Pengajuan pencairan berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    dtFee.ajax.reload(null, false);
                    if (dtGroup) dtGroup.ajax.reload(null, false);

                    // Pindah ke Tab 2
                    $("#tab-fee-group").tab("show");
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal membuat pengajuan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-batch").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan & Kirim Pengajuan');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // TAB 2: DATATABLES GRUP PENGAJUAN
    // ─────────────────────────────────────────────────────────────

    function initGroupDataTable() {
        if (dtGroup) return;

        dtGroup = $("#table-pengajuan-group").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: base_url + "api/sales-external/list-group",
                type: "POST",
                data: function (d) {
                    d[csrfName] = csrfHash;
                    d.id_proyek = (typeof activeProyekId === "function" ? activeProyekId() : (window.SIGAPP && window.SIGAPP.activeProyekId ? window.SIGAPP.activeProyekId : "")) || "";
                    d.status = $("#filter-group-status").val();
                },
                dataSrc: function (json) {
                    if (json.token) csrfHash = json.token;
                    let list = json.data || [];
                    let rejectedCount = list.filter(function (r) {
                        return r.status === "ditolak" && parseInt(r.created_by) === currentUserId;
                    }).length;

                    if (rejectedCount > 0) {
                        $("#creator-rejected-count").text(rejectedCount);
                        $("#creator-rejected-notif-banner").slideDown(200);
                    } else {
                        $("#creator-rejected-notif-banner").slideUp(200);
                    }
                    return list;
                },
            },
            createdRow: function (row, data) {
                if (data.status === "ditolak" && parseInt(data.created_by) === currentUserId) {
                    $(row).addClass("row-rejected-creator");
                }
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
                    data: "tanggal_pengajuan",
                    render: function (data, type, row) {
                        let tgl = data ? formatShortDate(data) : "-";
                        let pembuat = row.ttd_pembuat_nama || row.creator_name || "-";
                        return `<div><strong class="text-dark d-block">${tgl}</strong><small class="text-muted font-small-2"><i class="far fa-user mr-25"></i>${pembuat}</small></div>`;
                    },
                },
                {
                    data: "nomor_pengajuan",
                    render: function (data, type, row) {
                        let lampiran = row.lampiran_spp
                            ? `<a href="${base_url + row.lampiran_spp}" target="_blank" class="badge badge-light-warning ml-25 font-small-1"><i class="fas fa-file-pdf"></i> SPP</a>`
                            : "";
                        return `<div><span class="font-weight-bold text-dark">${data || "-"}</span>${lampiran}</div>`;
                    },
                },
                {
                    data: "sales_names",
                    render: function (data, type, row) {
                        let sales = data || "-";
                        let tglSales = row.tanggal_pengajuan_sales ? formatShortDate(row.tanggal_pengajuan_sales) : "-";
                        return `<div><span class="font-weight-bold font-small-3 text-dark d-block">${sales}</span><small class="text-muted font-small-1"><i class="far fa-calendar-alt mr-25"></i>Sales: ${tglSales}</small></div>`;
                    },
                },
                {
                    data: "total_unit",
                    className: "text-center",
                    render: function (data) {
                        return `<span class="badge badge-light-primary">${data} Kavling</span>`;
                    },
                },
                {
                    data: "total_nominal",
                    className: "text-right font-weight-bold text-primary",
                    render: function (data) {
                        return formatRupiah(data);
                    },
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (row.ttd_mkdt_by || row.ttd_mkdt_id) {
                            let namaMkdt = row.ttd_mkdt_nama ? row.ttd_mkdt_nama.split(" ")[0] : "MKDT";
                            return `<div><span class="badge badge-light-success py-25 px-50 font-small-1" title="${row.ttd_mkdt_nama || 'MKDT'}"><i class="fas fa-check mr-25"></i>${namaMkdt}</span><small class="text-muted d-block" style="font-size:10px;">${formatShortDate(row.ttd_mkdt_at)}</small></div>`;
                        }
                        if (row.status === "void") {
                            return '<span class="badge badge-light-secondary py-25 px-50 font-small-1">Void</span>';
                        }
                        if (row.status === "ditolak") {
                            return '<span class="badge badge-light-danger py-25 px-50 font-small-1">Ditolak</span>';
                        }
                        if (canSignMkdt) {
                            return `<button type="button" class="btn btn-xs btn-outline-primary py-0 px-50 font-small-1 btn-quick-sign" data-id="${row.id}" data-role="mkdt"><i class="fas fa-pen-nib mr-25"></i>TTD</button>`;
                        }
                        return '<span class="badge badge-light-warning py-25 px-50 font-small-1">Menunggu</span>';
                    },
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (row.ttd_keuangan_by || row.ttd_keuangan_id) {
                            let namaKeu = row.ttd_keuangan_nama ? row.ttd_keuangan_nama.split(" ")[0] : "Keuangan";
                            return `<div><span class="badge badge-light-success py-25 px-50 font-small-1" title="${row.ttd_keuangan_nama || 'Keuangan'}"><i class="fas fa-check mr-25"></i>${namaKeu}</span><small class="text-muted d-block" style="font-size:10px;">${formatShortDate(row.ttd_keuangan_at)}</small></div>`;
                        }
                        if (row.status === "void") {
                            return '<span class="badge badge-light-secondary py-25 px-50 font-small-1">Void</span>';
                        }
                        if (row.status === "ditolak") {
                            return '<span class="badge badge-light-danger py-25 px-50 font-small-1">Ditolak</span>';
                        }
                        if (canSignKeuangan) {
                            return `<button type="button" class="btn btn-xs btn-outline-info py-0 px-50 font-small-1 btn-quick-sign" data-id="${row.id}" data-role="keuangan"><i class="fas fa-pen-nib mr-25"></i>TTD</button>`;
                        }
                        return '<span class="badge badge-light-warning py-25 px-50 font-small-1">Menunggu</span>';
                    },
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        if (row.status === "void") {
                            return '<span class="badge badge-light-danger">Dibatalkan</span>';
                        }
                        let tot = parseInt(row.total_item || 0);
                        let cair = parseInt(row.total_cair || 0);
                        let pct = tot > 0 ? Math.round((cair / tot) * 100) : 0;
                        let barClass = pct >= 100 ? "bg-success" : (pct > 0 ? "bg-primary" : "bg-warning");

                        return `
                            <div style="min-width: 90px;">
                                <div class="d-flex justify-content-between font-small-2 mb-25">
                                    <span>${cair}/${tot} Cair</span>
                                    <span class="font-weight-bold">${pct}%</span>
                                </div>
                                <div class="progress termin-progress-mini">
                                    <div class="progress-bar ${barClass}" role="progressbar" style="width: ${pct}%;"></div>
                                </div>
                            </div>
                        `;
                    },
                },
                {
                    data: "status",
                    render: function (data, type, row) {
                        if (data === "void") {
                            return '<span class="badge badge-danger">Void / Dibatalkan</span>';
                        } else if (data === "ditolak") {
                            let role = (row.rejected_role || "Reviewer").toUpperCase();
                            let reason = row.reject_reason || "";
                            let titleAttr = reason ? ` title="${reason}"` : "";
                            return `
                                <div>
                                    <span class="badge badge-danger font-small-1"${titleAttr}><i class="fas fa-times-circle mr-25"></i> Ditolak (${role})</span>
                                    ${reason ? `<small class="text-danger font-small-1 d-block mt-25 text-truncate" style="max-width: 130px;" title="${reason}"><i class="fas fa-comment mr-25"></i>${reason}</small>` : ''}
                                </div>
                            `;
                        } else if (data === "selesai") {
                            return '<span class="badge badge-success">Selesai (Lunas)</span>';
                        } else if (data === "cair_sebagian") {
                            return '<span class="badge badge-primary">Cair Sebagian</span>';
                        }
                        return '<span class="badge badge-warning">Diajukan</span>';
                    },
                },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        let shareBtn = `<button type="button" class="btn btn-sm btn-outline-info btn-share-group mr-25 py-25 px-50" data-token="${row.share_token || row.id}" title="Salin Link Pengajuan"><i class="fas fa-share-alt"></i></button>`;
                        let pdfBtn = `<a href="${base_url}fee-sales/pengajuan-pdf/${row.id}" target="_blank" class="btn btn-sm btn-outline-danger mr-25 py-25 px-50" title="PDF"><i class="fas fa-file-pdf"></i></a>`;
                        let detailBtn = `<button type="button" class="btn btn-sm btn-primary btn-detail-group mr-25 py-25 px-50" data-id="${row.id}" title="Detail Pengajuan"><i class="fas fa-eye"></i></button>`;
                        let voidBtn = "";
                        if (row.status !== "void" && parseInt(row.total_cair || 0) === 0 && canManage) {
                            voidBtn = `<button type="button" class="btn btn-sm btn-outline-danger btn-void-group py-25 px-50" data-id="${row.id}" title="Batalkan (Void)"><i class="fas fa-ban"></i></button>`;
                        }
                        return detailBtn + pdfBtn + shareBtn + voidBtn;
                    },
                },
            ],
            order: [[1, "desc"]],
            language: {
                processing: "Sedang memuat data...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                emptyTable: "Belum ada data pengajuan pencairan",
                zeroRecords: "Data pengajuan tidak ditemukan",
                paginate: { previous: "&nbsp;", next: "&nbsp;" },
            },
        });

        $("#table-pengajuan-group tbody").on("click", "tr", function (e) {
            if ($(e.target).closest("button, a, input, select, textarea, .btn").length) {
                return;
            }
            let rowData = dtGroup.row(this).data();
            if (rowData && rowData.id) {
                openGroupDetailModal(rowData.id);
            }
        });
    }

    $("#tab-fee-group").on("shown.bs.tab", function () {
        initGroupDataTable();
    });

    $("#filter-group-status").on("change", function () {
        if (dtGroup) dtGroup.ajax.reload();
    });

    $("#btn-refresh-group").on("click", function () {
        if (dtGroup) dtGroup.ajax.reload();
    });

    $(document).on("click", "#btn-filter-rejected-group", function () {
        $("#filter-group-status").val("ditolak").trigger("change");
    });

    // ─────────────────────────────────────────────────────────────
    // MODAL DETAIL & KELOLA GRUP PENGAJUAN
    // ─────────────────────────────────────────────────────────────

    let currentGroupDetail = null;

    $(document).on("click", ".btn-detail-group", function () {
        let idGroup = $(this).data("id");
        openGroupDetailModal(idGroup);
    });

    function openGroupDetailModal(idGroup) {
        $("#detail-group-title").text("Memuat...");
        $("#tbody-group-items").html('<tr><td colspan="12" class="text-center py-2 text-muted"><span class="spinner-border spinner-border-sm mr-50"></span> Memuat data pengajuan...</td></tr>');
        $("#modal-group-detail").modal("show");

        $.get(base_url + "api/sales-external/group-detail/" + idGroup, function (res) {
            if (res.token) csrfHash = res.token;
            if (res.success && res.data) {
                let g = res.data;
                currentGroupDetail = g;

                $("#detail-group-title").text(g.nomor_pengajuan || `Pengajuan #${g.id}`);
                $("#detail-group-nomor").text(g.nomor_pengajuan || "-");
                $("#detail-group-tanggal").text(g.tanggal_pengajuan ? formatShortDate(g.tanggal_pengajuan) : "-");
                $("#detail-group-tanggal-sales").text(g.tanggal_pengajuan_sales ? formatShortDate(g.tanggal_pengajuan_sales) : "-");

                let badgeSt = g.status === "void"
                    ? '<span class="badge badge-danger">Void / Dibatalkan</span>'
                    : (g.status === "ditolak"
                        ? `<span class="badge badge-danger">Ditolak (${(g.rejected_role || 'Reviewer').toUpperCase()})</span>`
                        : (g.status === "selesai"
                            ? '<span class="badge badge-success">Selesai (Lunas)</span>'
                            : (g.status === "cair_sebagian" ? '<span class="badge badge-primary">Cair Sebagian</span>' : '<span class="badge badge-warning">Diajukan</span>')));
                $("#detail-group-status-badge").html(badgeSt);

                $("#btn-export-group-pdf").attr("href", `${base_url}fee-sales/pengajuan-pdf/${g.id}`);
                $("#btn-share-group-detail").data("token", g.share_token || g.id);

                let uncairCount = 0;
                let cairCount = 0;
                if (g.items && g.items.length > 0) {
                    g.items.forEach(function (it) {
                        if (it.termin_status === "sudah_cair") cairCount++;
                        else uncairCount++;
                    });
                }

                // Void alert & Tombol Void
                if (g.status === "void") {
                    let voidMeta = "";
                    if (g.voided_by_name) {
                        voidMeta += `Dibatalkan oleh: <strong>${g.voided_by_name}</strong>`;
                    }
                    if (g.voided_at) {
                        voidMeta += (voidMeta ? " • " : "") + `Waktu: <strong>${formatShortDate(g.voided_at)}</strong>`;
                    }
                    $("#detail-group-void-reason").text(g.void_reason || "-");
                    $("#detail-group-void-meta").html(voidMeta || "-");
                    $("#detail-group-void-alert").show();
                    $("#btn-void-group-detail").hide();
                } else {
                    $("#detail-group-void-alert").hide();
                    if (cairCount === 0 && canManage) {
                        $("#btn-void-group-detail").data("id", g.id).show();
                    } else {
                        $("#btn-void-group-detail").hide();
                    }
                }

                // Reject Alert & Tombol Apply Recalculate
                if (g.status === "ditolak") {
                    $("#detail-group-reject-by").text(g.rejected_by_name || "Reviewer");
                    let roleStr = (g.rejected_role || "Reviewer").toUpperCase();
                    $("#detail-group-reject-role").text(roleStr);
                    $("#detail-group-reject-role-badge").text("Ditolak (" + roleStr + ")");
                    $("#detail-group-reject-at").text(g.rejected_at ? formatShortDate(g.rejected_at) : "-");
                    $("#detail-group-reject-reason").text(g.reject_reason || "-");

                    // Rincian saran harga kavling jika ada
                    let priceHints = [];
                    if (Array.isArray(g.items)) {
                        let seenUnits = {};
                        g.items.forEach(function (it) {
                            if (it.id_mkdt && !seenUnits[it.id_mkdt] && cleanCurrency(it.suggested_harga_net_ajb) > 0) {
                                seenUnits[it.id_mkdt] = true;
                                let unitLabel = (it.no_kavling ? 'Kav. ' + it.no_kavling : '') + (it.nama_konsumen ? ' (' + it.nama_konsumen + ')' : '');
                                priceHints.push(`<div>• <strong>${unitLabel}</strong>: Saran Harga Net AJB <strong>${formatRupiah(it.suggested_harga_net_ajb)}</strong></div>`);
                            }
                        });
                    }
                    if (priceHints.length > 0) {
                        $("#detail-group-reject-prices-list").html(priceHints.join(""));
                        $("#detail-group-reject-prices-section").show();
                    } else {
                        $("#detail-group-reject-prices-section").hide();
                    }

                    if (canManage || canSubmitSpp || canSignMkdt || canSignKeuangan || parseInt(g.created_by) === currentUserId) {
                        $("#btn-apply-reject-recalc").show();
                    } else {
                        $("#btn-apply-reject-recalc").hide();
                    }

                    $("#detail-group-reject-alert").show();
                } else {
                    $("#detail-group-reject-alert").hide();
                    $("#btn-apply-reject-recalc").hide();
                }

                // Tombol Cairkan Sekaligus
                if (g.status !== "void" && g.status !== "ditolak" && uncairCount > 0 && canCairkan) {
                    $("#btn-batch-cair-modal").show();
                } else {
                    $("#btn-batch-cair-modal").hide();
                }

                // Tombol Tolak & TTD MKDT
                if (g.status !== "void" && g.status !== "ditolak" && cairCount === 0 && canSignMkdt) {
                    $("#btn-reject-mkdt-detail").data("id", g.id).data("role", "mkdt").show();
                } else {
                    $("#btn-reject-mkdt-detail").hide();
                }
                if (g.status !== "void" && g.status !== "ditolak" && !(g.ttd_mkdt_by || g.ttd_mkdt_id) && canSignMkdt) {
                    $("#btn-sign-mkdt-detail").data("id", g.id).data("role", "mkdt").show();
                } else {
                    $("#btn-sign-mkdt-detail").hide();
                }

                // Tombol Tolak & TTD Keuangan
                if (g.status !== "void" && g.status !== "ditolak" && cairCount === 0 && canSignKeuangan) {
                    $("#btn-reject-keuangan-detail").data("id", g.id).data("role", "keuangan").show();
                } else {
                    $("#btn-reject-keuangan-detail").hide();
                }
                if (g.status !== "void" && g.status !== "ditolak" && !(g.ttd_keuangan_by || g.ttd_keuangan_id) && canSignKeuangan) {
                    $("#btn-sign-keuangan-detail").data("id", g.id).data("role", "keuangan").show();
                } else {
                    $("#btn-sign-keuangan-detail").hide();
                }

                // Kartu TTD Pembuat
                if (g.ttd_pembuat_path) {
                    $("#img-ttd-pembuat").html(`<img src="${base_url}fee-sales/pengajuan-signature/${g.id}/pembuat" alt="TTD Pembuat">`);
                } else {
                    $("#img-ttd-pembuat").html('<span class="text-muted font-small-2">Belum ada tanda tangan</span>');
                }
                $("#name-ttd-pembuat").text(g.ttd_pembuat_nama || g.creator_name || "-");
                $("#date-ttd-pembuat").text(g.ttd_pembuat_at ? formatShortDate(g.ttd_pembuat_at) : (g.tanggal_pengajuan ? formatShortDate(g.tanggal_pengajuan) : "-"));

                // Kartu TTD MKDT
                if ((g.ttd_mkdt_by || g.ttd_mkdt_id) && g.ttd_mkdt_path) {
                    $("#img-ttd-mkdt").html(`<img src="${base_url}fee-sales/pengajuan-signature/${g.id}/mkdt" alt="TTD MKDT">`);
                    $("#badge-ttd-mkdt").removeClass("badge-secondary").addClass("badge-success").text("Disetujui");
                } else {
                    $("#img-ttd-mkdt").html('<span class="text-muted font-small-2">Belum ditandatangani</span>');
                    $("#badge-ttd-mkdt").removeClass("badge-success").addClass("badge-secondary").text("Belum TTD");
                }
                $("#name-ttd-mkdt").text(g.ttd_mkdt_nama || "-");
                $("#date-ttd-mkdt").text(g.ttd_mkdt_at ? formatShortDate(g.ttd_mkdt_at) : "-");

                // Kartu TTD Keuangan
                if ((g.ttd_keuangan_by || g.ttd_keuangan_id) && g.ttd_keuangan_path) {
                    $("#img-ttd-keuangan").html(`<img src="${base_url}fee-sales/pengajuan-signature/${g.id}/keuangan" alt="TTD Keuangan">`);
                    $("#badge-ttd-keuangan").removeClass("badge-secondary").addClass("badge-success").text("Disetujui");
                } else {
                    $("#img-ttd-keuangan").html('<span class="text-muted font-small-2">Belum ditandatangani</span>');
                    $("#badge-ttd-keuangan").removeClass("badge-success").addClass("badge-secondary").text("Belum TTD");
                }
                $("#name-ttd-keuangan").text(g.ttd_keuangan_nama || "-");
                $("#date-ttd-keuangan").text(g.ttd_keuangan_at ? formatShortDate(g.ttd_keuangan_at) : "-");

                renderGroupItemsTable(g.items);
            }
        });
    }

    function renderGroupItemsTable(items) {
        if (!items || items.length === 0) {
            $("#tbody-group-items").html('<tr><td colspan="12" class="text-center py-2 text-muted">Tidak ada item pada pengajuan ini.</td></tr>');
            return;
        }

        let isVoidGroup = (currentGroupDetail && currentGroupDetail.status === "void");
        let isDitolakGroup = (currentGroupDetail && currentGroupDetail.status === "ditolak");

        // 1. Grouping unit kavling agar 1 baris per unit kavling
        let groupedUnitMap = new Map();
        items.forEach(function (it) {
            let unitKey = it.id_fee_sales || (it.id_mkdt + "_" + it.id_sales);
            if (!groupedUnitMap.has(unitKey)) {
                let clone = Object.assign({}, it);
                clone.termin_targets = [parseInt(it.termin_yang_diajukan)];
                clone.submitted_items = [it];
                clone.nominal_diajukan_total = parseFloat(it.nominal_diajukan || 0);
                groupedUnitMap.set(unitKey, clone);
            } else {
                let existing = groupedUnitMap.get(unitKey);
                existing.termin_targets.push(parseInt(it.termin_yang_diajukan));
                existing.submitted_items.push(it);
                existing.nominal_diajukan_total += parseFloat(it.nominal_diajukan || 0);
            }
        });
        let groupedItems = Array.from(groupedUnitMap.values());

        // Helper untuk mengambil objek termin secara aman dari array/object all_termin
        function getTerminObj(allTermin, k) {
            if (!allTermin) return null;
            if (Array.isArray(allTermin)) {
                for (let i = 0; i < allTermin.length; i++) {
                    if (allTermin[i] && parseInt(allTermin[i].termin_ke) === k) {
                        return allTermin[i];
                    }
                }
                return null;
            }
            return allTermin[k] || allTermin[String(k)] || null;
        }

        // Helper untuk render sel termin matriks
        function renderTerminCell(k, it, isSkemaMatch) {
            if (!isSkemaMatch) {
                return { html: "-", cellClass: "" };
            }

            let t = getTerminObj(it.all_termin, k);
            let isTarget = it.termin_targets.includes(k);
            let cellClass = isTarget ? "tenor-highlight-yellow" : "";

            let nominalVal = 0;
            if (t && cleanCurrency(t.nominal) > 0) {
                nominalVal = t.nominal;
            } else if (isTarget) {
                let subItem = (it.submitted_items || []).find(function (sub) {
                    return parseInt(sub.termin_yang_diajukan) === k;
                });
                if (subItem) nominalVal = subItem.nominal_diajukan || 0;
            }

            if (!nominalVal || cleanCurrency(nominalVal) <= 0) {
                return { html: "-", cellClass: cellClass };
            }

            let cellHtml = `<div class="font-weight-bold">${formatRupiah(nominalVal)}</div>`;

            if (isTarget) {
                let isCair = (t && t.status === "sudah_cair");
                if (isCair) {
                    cellHtml += `
                        <div class="tenor-status-box">
                            <span class="tenor-cair-badge"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>
                            ${t.tanggal_cair ? `<small class="text-success font-weight-bold d-block mt-25">${formatShortDate(t.tanggal_cair)}</small>` : ""}
                            ${t.no_cek ? `<small class="text-muted d-block font-small-1">Cek: ${t.no_cek}</small>` : ""}
                        </div>
                    `;
                } else {
                    cellHtml += `
                        <div class="tenor-status-box">
                            <span class="badge badge-dark font-small-1 mt-25"><i class="fas fa-arrow-down mr-25"></i> Diajukan</span>
                        </div>
                    `;
                }
            } else {
                if (t && t.status === "sudah_cair") {
                    cellHtml += `
                        <div class="tenor-status-box">
                            <span class="tenor-cair-badge"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>
                            ${t.tanggal_cair ? `<small class="text-success font-weight-bold d-block mt-25">${formatShortDate(t.tanggal_cair)}</small>` : ""}
                            ${t.no_cek ? `<small class="text-muted d-block font-small-1">Cek: ${t.no_cek}</small>` : ""}
                            ${t.pengajuan_nomor ? `<small class="text-muted d-block font-small-1" title="Nomor Pengajuan">No: ${t.pengajuan_nomor}</small>` : ""}
                        </div>
                    `;
                } else if (t && t.status === "pengajuan_spp") {
                    let tglPengajuan = t.tanggal_pengajuan_spp || t.pengajuan_tanggal;
                    cellHtml += `
                        <div class="tenor-status-box">
                            <span class="tenor-diajukan-other-badge"><i class="fas fa-clock mr-25"></i> Sudah Diajukan</span>
                            ${tglPengajuan ? `<small class="text-warning font-weight-bold d-block mt-25">${formatShortDate(tglPengajuan)}</small>` : ""}
                            ${t.pengajuan_nomor ? `<small class="text-muted d-block font-small-1" title="Nomor Pengajuan">No: ${t.pengajuan_nomor}</small>` : ""}
                        </div>
                    `;
                } else {
                    cellHtml += `
                        <div class="tenor-status-box">
                            <small class="text-muted font-small-1 d-block">Belum Diajukan</small>
                        </div>
                    `;
                }
            }

            return { html: cellHtml, cellClass: cellClass };
        }

        let html = "";
        groupedItems.forEach(function (it, idx) {
            // Kolom Status & No Cek
            let statusHtml = "";
            if (it.submitted_items.length === 1) {
                let sub = it.submitted_items[0];
                let isCair = (sub.termin_status === "sudah_cair");
                let statusBadge = isCair
                    ? '<span class="badge badge-success"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>'
                    : (isVoidGroup
                        ? '<span class="badge badge-secondary"><i class="fas fa-ban mr-25"></i> Batal / Void</span>'
                        : (isDitolakGroup
                            ? '<span class="badge badge-danger"><i class="fas fa-times-circle mr-25"></i> Ditolak</span>'
                            : '<span class="badge badge-warning"><i class="fas fa-clock mr-25"></i> Pengajuan SPP</span>'));

                let noCekInfo = "";
                if (isCair) {
                    let cek = sub.termin_no_cek ? `<div class="mt-25"><span class="badge badge-light-primary font-small-1">Cek: ${sub.termin_no_cek}</span></div>` : "";
                    let tgl = sub.termin_tgl_cair ? `<small class="text-success font-weight-bold d-block mt-25">${formatShortDate(sub.termin_tgl_cair)}</small>` : "";
                    noCekInfo = cek + tgl;
                }
                statusHtml = statusBadge + noCekInfo;
            } else {
                statusHtml = it.submitted_items.map(function (sub, sIdx) {
                    let isCair = (sub.termin_status === "sudah_cair");
                    let tLabel = `T${sub.termin_yang_diajukan}`;
                    let badge = isCair
                        ? '<span class="badge badge-success font-small-1"><i class="fas fa-check-circle mr-25"></i> Sudah Cair</span>'
                        : (isVoidGroup
                            ? '<span class="badge badge-secondary font-small-1"><i class="fas fa-ban mr-25"></i> Batal / Void</span>'
                            : (isDitolakGroup
                                ? '<span class="badge badge-danger font-small-1"><i class="fas fa-times-circle mr-25"></i> Ditolak</span>'
                                : '<span class="badge badge-warning font-small-1"><i class="fas fa-clock mr-25"></i> Pengajuan SPP</span>'));

                    let extra = "";
                    if (isCair) {
                        if (sub.termin_no_cek) {
                            extra += `<div class="mt-25"><span class="badge badge-light-primary font-small-1">Cek: ${sub.termin_no_cek}</span></div>`;
                        }
                        if (sub.termin_tgl_cair) {
                            extra += `<small class="text-success font-weight-bold d-block mt-25">${formatShortDate(sub.termin_tgl_cair)}</small>`;
                        }
                    }

                    return `
                        <div class="${sIdx > 0 ? "mt-50 pt-25 border-top" : ""}">
                            <div class="d-flex align-items-center justify-content-center flex-wrap">
                                <span class="badge badge-primary font-small-1 mr-25">${tLabel}</span>
                                ${badge}
                            </div>
                            ${extra}
                        </div>
                    `;
                }).join("");
            }

            // Kolom Aksi
            let actionHtml = "";
            if (it.submitted_items.length === 1) {
                let sub = it.submitted_items[0];
                let isCair = (sub.termin_status === "sudah_cair");
                if (!isCair && !isVoidGroup && !isDitolakGroup && canCairkan) {
                    actionHtml = `
                        <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50" data-id="${sub.id_termin}" data-termin="${sub.termin_yang_diajukan}" data-nominal="${sub.nominal_diajukan}">
                            <i class="fas fa-check mr-25"></i> Cairkan
                        </button>
                    `;
                } else {
                    actionHtml = '<span class="text-muted font-small-2">-</span>';
                }
            } else {
                let actionBtns = [];
                it.submitted_items.forEach(function (sub) {
                    let isCair = (sub.termin_status === "sudah_cair");
                    if (!isCair && !isVoidGroup && !isDitolakGroup && canCairkan) {
                        actionBtns.push(`
                            <button type="button" class="btn btn-success btn-sm btn-open-cair py-25 px-50 mb-25" data-id="${sub.id_termin}" data-termin="${sub.termin_yang_diajukan}" data-nominal="${sub.nominal_diajukan}">
                                <i class="fas fa-check mr-25"></i> Cairkan T${sub.termin_yang_diajukan}
                            </button>
                        `);
                    }
                });
                actionHtml = actionBtns.length > 0 ? actionBtns.join("") : '<span class="text-muted font-small-2">-</span>';
            }

            let pksBadge = parseInt(it.sales_has_pks || 0) === 1
                ? '<span class="badge badge-light-success font-small-1 ml-25">PKS</span>'
                : '<span class="badge badge-light-secondary font-small-1 ml-25">Non-PKS</span>';

            let itAjbRes = resolveHargaJualNetAjb(it);
            let itAjbCellHtml = itAjbRes.isEstimasi
                ? `<div><span class="font-weight-bold text-secondary">${formatRupiah(itAjbRes.nominal)}</span><span class="badge badge-light-warning font-small-1 d-block mt-25">Estimasi</span></div>`
                : `<span class="font-weight-bold text-primary">${formatRupiah(itAjbRes.nominal)}</span>`;

            if (cleanCurrency(it.suggested_harga_net_ajb) > 0) {
                itAjbCellHtml += `
                    <div class="badge-suggested-price mt-25" title="Harga Rekomendasi Reviewer">
                        <i class="fas fa-arrow-alt-circle-right mr-25"></i> Saran: ${formatRupiah(it.suggested_harga_net_ajb)}
                    </div>
                `;
            }

            let skema = it.skema_termin || "30_60_10";
            let c30 = renderTerminCell(1, it, skema !== "100");
            let c60 = renderTerminCell(2, it, skema !== "100");
            let c10 = renderTerminCell(3, it, skema !== "100");
            let c100 = renderTerminCell(1, it, skema === "100");

            let tglBooking = (it.booking_tgl && formatShortDate(it.booking_tgl)) ? formatShortDate(it.booking_tgl) : "-";
            let tglAkad = (it.akad_tgl && formatShortDate(it.akad_tgl)) ? formatShortDate(it.akad_tgl) : "-";
            let tipeRumahLabel = it.tipe_rumah ? ` (${it.tipe_rumah})` : "";

            html += `
                <tr class="group-header-row">
                    <td colspan="12">
                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <span class="mr-1 font-weight-bold text-primary">
                                    <i class="fas fa-user-tie mr-25"></i> Sales: <strong>${it.sales_nama || "-"}</strong> ${pksBadge}
                                </span>
                                <span class="mr-1 font-weight-bold text-dark">
                                    <i class="fas fa-user mr-25"></i> Konsumen: <strong>${it.nama_konsumen || "-"}</strong>
                                </span>
                                <span class="text-secondary font-weight-bold">
                                    <i class="fas fa-home mr-25"></i> Kavling: <strong>${it.nama_jalan || "-"} No. ${it.no_kavling || "-"}</strong>${tipeRumahLabel}
                                </span>
                            </div>
                            <div>
                                <span class="badge badge-light-secondary font-small-1">
                                    <i class="far fa-calendar-alt mr-25"></i> Booking: ${tglBooking} | Akad: ${tglAkad}
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">${idx + 1}</td>
                    <td class="text-right">${formatRupiah(it.harga_jual || 0)}</td>
                    <td class="text-right">${formatRupiah(it.harga_jual_net || 0)}</td>
                    <td class="text-right">${itAjbCellHtml}</td>
                    <td class="text-center">${parseFloat(it.persentase_fee || 0).toLocaleString("id-ID")}%</td>
                    <td class="text-right"><span class="font-weight-bold text-success">${formatRupiah(it.total_fee || 0)}</span></td>
                    <td class="text-right ${c30.cellClass}">${c30.html}</td>
                    <td class="text-right ${c60.cellClass}">${c60.html}</td>
                    <td class="text-right ${c10.cellClass}">${c10.html}</td>
                    <td class="text-right ${c100.cellClass}">${c100.html}</td>
                    <td class="text-center">${statusHtml}</td>
                    <td class="text-center">${actionHtml}</td>
                </tr>
            `;
        });

        $("#tbody-group-items").html(html);
    }

    // Buka Modal Cairkan Sekaligus dari Modal Detail Grup
    $("#btn-batch-cair-modal").on("click", function () {
        if (!currentGroupDetail) return;
        $("#form-batch-cair")[0].reset();
        $("#batch-cair-id_pengajuan").val(currentGroupDetail.id);
        $("#batch-cair-tanggal").val(new Date().toISOString().slice(0, 10));
        $("#batch-cair-file").next(".custom-file-label").html("Pilih file");
        $("#modal-batch-cair").modal("show");
    });

    // Submit Cairkan Sekaligus Grup
    $("#form-batch-cair").on("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-batch-cair").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Memproses...');

        $.ajax({
            url: base_url + "api/sales-external/konfirmasi-cair-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-batch-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Cair Semua');

                if (res.success) {
                    $("#modal-batch-cair").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pencairan Berhasil!",
                        text: res.messages || "Seluruh termin pengajuan berhasil dicairkan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    if (currentGroupDetail) {
                        openGroupDetailModal(currentGroupDetail.id);
                    }
                    dtFee.ajax.reload(null, false);
                    if (dtGroup) dtGroup.ajax.reload(null, false);
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal mencairkan pengajuan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-batch-cair").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Konfirmasi Cair Semua');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // TTD PENGAJUAN (MKDT & KEUANGAN)
    // ─────────────────────────────────────────────────────────────

    $(document).on("click", ".btn-quick-sign, #btn-sign-mkdt-detail, #btn-sign-keuangan-detail", function () {
        let idPengajuan = $(this).data("id");
        let role = $(this).data("role");
        $("#form-sign-pengajuan")[0].reset();
        $("#sign-pengajuan-id").val(idPengajuan);
        $("#sign-pengajuan-role").val(role);
        $("#sign-pengajuan-method").val("canvas").trigger("change");
        if (signCanvas) signCanvas.clear();
        let roleLabel = role === "mkdt" ? "MKDT" : "Keuangan";
        $("#modal-sign-pengajuan .modal-title").html(`<i class="fas fa-pen-nib mr-50 text-primary"></i> Tanda Tangan Pengajuan Fee (${roleLabel})`);
        $("#modal-sign-pengajuan").modal("show");
    });

    $("#form-sign-pengajuan").on("submit", function (e) {
        e.preventDefault();
        let method = $("#sign-pengajuan-method").val();
        let signData = "";
        if (method === "canvas") {
            signData = signCanvas ? signCanvas.data() : "";
            if (!signData) {
                Swal.fire("Perhatian", "Silakan gambar tanda tangan pada canvas terlebih dahulu.", "warning");
                return;
            }
        }
        let pwd = $("#sign-pengajuan-password").val();
        if (!pwd) {
            Swal.fire("Perhatian", "Password akun wajib diisi untuk verifikasi keabsahan tanda tangan.", "warning");
            return;
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);
        if (method === "canvas") {
            fd.append("signature_data", signData);
        }

        $("#btn-submit-sign-pengajuan").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/sign-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-sign-pengajuan").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Simpan Tanda Tangan');

                if (res.success) {
                    $("#modal-sign-pengajuan").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: res.messages || "Tanda tangan berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    let idPengajuan = $("#sign-pengajuan-id").val();
                    if (currentGroupDetail && currentGroupDetail.id == idPengajuan) {
                        openGroupDetailModal(idPengajuan);
                    }
                    if (dtGroup) dtGroup.ajax.reload(null, false);
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal menyimpan tanda tangan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-sign-pengajuan").prop("disabled", false).html('<i class="fas fa-check mr-50"></i> Simpan Tanda Tangan');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // VOID PENGAJUAN
    // ─────────────────────────────────────────────────────────────

    $(document).on("click", ".btn-void-group, #btn-void-group-detail", function () {
        let id = $(this).data("id");
        $("#form-void-pengajuan")[0].reset();
        $("#void-pengajuan-id").val(id);
        $("#modal-void-pengajuan").modal("show");
    });

    $("#form-void-pengajuan").on("submit", function (e) {
        e.preventDefault();
        let alasan = $("#void-pengajuan-alasan").val().trim();
        if (!alasan) {
            Swal.fire("Perhatian", "Alasan pembatalan (void) wajib diisi.", "warning");
            return;
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-void-pengajuan").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Memproses...');

        $.ajax({
            url: base_url + "api/sales-external/void-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-void-pengajuan").prop("disabled", false).html('<i class="fas fa-ban mr-50"></i> Konfirmasi Void Pengajuan');

                if (res.success) {
                    $("#modal-void-pengajuan").modal("hide");
                    $("#modal-group-detail").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pengajuan Dibatalkan!",
                        text: res.messages || "Pengajuan fee berhasil divoid.",
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    if (dtGroup) dtGroup.ajax.reload(null, false);
                    dtFee.ajax.reload(null, false);
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal membatalkan pengajuan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-void-pengajuan").prop("disabled", false).html('<i class="fas fa-ban mr-50"></i> Konfirmasi Void Pengajuan');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // TOLAK PENGAJUAN (MKDT & KEUANGAN) & REVISI HARGA NET AJB
    // ─────────────────────────────────────────────────────────────

    // Buka Modal Tolak Pengajuan
    $(document).on("click", "#btn-reject-mkdt-detail, #btn-reject-keuangan-detail", function () {
        if (!currentGroupDetail) return;
        let role = $(this).attr("id") === "btn-reject-mkdt-detail" ? "mkdt" : "keuangan";
        let roleLabel = role === "mkdt" ? "MKDT" : "Keuangan";

        $("#form-reject-pengajuan")[0].reset();
        $("#reject-pengajuan-id").val(currentGroupDetail.id);
        $("#reject-pengajuan-role").val(role);
        $("#modal-reject-title").text(`Tolak Pengajuan & Saran Revisi Harga (${roleLabel})`);

        let container = $("#reject-unit-prices-container");
        container.empty();

        let uniqueUnits = {};
        (currentGroupDetail.items || []).forEach(function (it) {
            if (it.id_mkdt && !uniqueUnits[it.id_mkdt]) {
                uniqueUnits[it.id_mkdt] = it;
            }
        });

        let keys = Object.keys(uniqueUnits);
        if (keys.length === 0) {
            container.html('<div class="text-center text-muted py-1 font-small-2">Tidak ada kavling dalam pengajuan ini.</div>');
        } else {
            let html = "";
            keys.forEach(function (k) {
                let itm = uniqueUnits[k];
                let ajbRes = resolveHargaJualNetAjb(itm);
                let curAjb = ajbRes.nominal;
                let sugAjb = cleanCurrency(itm.suggested_harga_net_ajb);
                let initVal = sugAjb > 0 ? sugAjb : curAjb;

                html += `
                    <div class="reject-unit-row mb-1 p-75 border rounded bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-7 col-12 mb-50 mb-md-0">
                                <strong class="text-dark d-block font-small-3">${itm.nama_konsumen || "-"}</strong>
                                <small class="text-muted font-small-2">${itm.nama_jalan || "-"} • No. ${itm.no_kavling || "-"}</small>
                                <div class="font-small-1 text-secondary mt-25">
                                    Harga AJB Saat Ini: <strong>${formatRupiah(curAjb)}</strong>
                                </div>
                            </div>
                            <div class="col-md-5 col-12">
                                <label class="font-small-2 font-weight-bold text-dark mb-25">Rekomendasi Harga Net AJB (Rp):</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text font-small-1">Rp</span></div>
                                    <input type="text" name="unit_prices[${itm.id_mkdt}]" class="form-control form-control-sm text-right font-weight-bold num-input" value="${initVal > 0 ? initVal.toLocaleString("id-ID") : ""}" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.html(html);
        }

        $("#modal-reject-pengajuan").modal("show");
    });

    // Submit Tolak Pengajuan
    $("#form-reject-pengajuan").on("submit", function (e) {
        e.preventDefault();
        let alasan = $("#reject-pengajuan-alasan").val().trim();
        if (!alasan) {
            Swal.fire("Perhatian", "Keterangan / alasan penolakan wajib diisi.", "warning");
            return;
        }

        let fd = new FormData(this);
        fd.append(csrfName, csrfHash);

        $("#btn-submit-reject-pengajuan").prop("disabled", true).html('<span class="spinner-border spinner-border-sm mr-50"></span> Menyimpan...');

        $.ajax({
            url: base_url + "api/sales-external/reject-group",
            type: "POST",
            data: fd,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                if (res.token) csrfHash = res.token;
                $("#btn-submit-reject-pengajuan").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi');

                if (res.success) {
                    $("#modal-reject-pengajuan").modal("hide");
                    Swal.fire({
                        icon: "success",
                        title: "Pengajuan Ditolak!",
                        text: res.messages || "Penolakan dan saran revisi harga berhasil disimpan.",
                        timer: 1500,
                        showConfirmButton: false,
                    });

                    let idPengajuan = $("#reject-pengajuan-id").val();
                    if (currentGroupDetail && currentGroupDetail.id == idPengajuan) {
                        openGroupDetailModal(idPengajuan);
                    }
                    if (dtGroup) dtGroup.ajax.reload(null, false);
                    dtFee.ajax.reload(null, false);
                } else {
                    Swal.fire("Gagal", res.messages || "Gagal menyimpan penolakan.", "error");
                }
            },
            error: function (xhr) {
                $("#btn-submit-reject-pengajuan").prop("disabled", false).html('<i class="fas fa-paper-plane mr-50"></i> Simpan Penolakan & Kirim Revisi');
                let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                Swal.fire("Error", err, "error");
            }
        });
    });

    // Perbaharui Nominal Tiap Termin dari Modal Detail Pengajuan
    $(document).on("click", "#btn-apply-reject-recalc", function () {
        if (!currentGroupDetail) return;
        let idPengajuan = currentGroupDetail.id;

        Swal.fire({
            title: "Perbaharui Nominal Tiap Termin?",
            text: "Sistem akan menerapkan saran harga rekomendasi, menghitung ulang nominal tiap termin, dan mengembalikan status pengajuan menjadi Diajukan.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2057a3",
            cancelButtonColor: "#82868b",
            confirmButtonText: "Ya, Perbaharui Nominal",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                let data = { id_pengajuan: idPengajuan };
                data[csrfName] = csrfHash;

                Swal.fire({
                    title: "Memproses Perhitungan Ulang...",
                    text: "Mohon tunggu sebentar.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: base_url + "api/sales-external/recalculate-termin",
                    type: "POST",
                    data: data,
                    dataType: "json",
                    success: function (res) {
                        if (res.token) csrfHash = res.token;
                        if (res.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Nominal Berhasil Diperbarui!",
                                text: res.messages || "Nominal termin berhasil dihitung ulang.",
                                timer: 1500,
                                showConfirmButton: false,
                            });
                            openGroupDetailModal(idPengajuan);
                            if (dtGroup) dtGroup.ajax.reload(null, false);
                            dtFee.ajax.reload(null, false);
                        } else {
                            Swal.fire("Gagal", res.messages || "Gagal memperbarui nominal termin.", "error");
                        }
                    },
                    error: function (xhr) {
                        let err = xhr.responseJSON ? xhr.responseJSON.messages : "Terjadi kesalahan sistem.";
                        Swal.fire("Error", err, "error");
                    }
                });
            }
        });
    });

    // ─────────────────────────────────────────────────────────────
    // BAGIKAN LINK PENGAJUAN
    // ─────────────────────────────────────────────────────────────

    $(document).on("click", ".btn-share-group, #btn-share-group-detail", function () {
        let token = $(this).data("token");
        if (!token) return;
        let shareUrl = base_url + "fee-sales/pengajuan/" + token;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shareUrl).then(function () {
                Swal.fire({
                    icon: "success",
                    title: "Link Tersalin!",
                    html: `Link pengajuan telah disalin ke clipboard:<br><input type="text" class="form-control mt-1 text-center font-small-2" value="${shareUrl}" readonly onclick="this.select()">`,
                    showConfirmButton: true,
                    confirmButtonText: "Tutup"
                });
            }).catch(function () {
                fallbackCopyUrl(shareUrl);
            });
        } else {
            fallbackCopyUrl(shareUrl);
        }
    });

    function fallbackCopyUrl(url) {
        let $temp = $("<input>");
        $("body").append($temp);
        $temp.val(url).select();
        document.execCommand("copy");
        $temp.remove();
        Swal.fire({
            icon: "success",
            title: "Link Tersalin!",
            html: `Link pengajuan telah disalin:<br><input type="text" class="form-control mt-1 text-center font-small-2" value="${url}" readonly onclick="this.select()">`,
            showConfirmButton: true,
            confirmButtonText: "Tutup"
        });
    }

    function openDetailModal(idMkdt) {
        return openFeeModal(idMkdt);
    }

    const urlParams = new URLSearchParams(window.location.search);
    const openMkdt = urlParams.get("open_mkdt");
    if (openMkdt) {
        openDetailModal(openMkdt);
    }
});
