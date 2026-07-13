    let pmSelectedKavling = null;
    let pmHistoryOffset = 0;

    function pmEscape(value) {
        return $("<div>").text(value == null || value === "" ? "-" : value).html();
    }

    function pmFormatDate(value) {
        if (!value || value === "0000-00-00") return "-";
        if (typeof format_date === "function") return format_date(value);
        return value;
    }

    function pmSetFlatpickrValue(selector, value) {
        const input = document.querySelector(selector);
        if (!input) return;
        if (input._flatpickr) {
            input._flatpickr.setDate(value || null, false);
        } else {
            input.value = value || "";
        }
    }

    function pmKavlingText(item) {
        const tipe = [item.no_tipe_rumah, item.tipe_rumah].filter(Boolean).join(" - ");
        const konsumen = item.nama_konsumen || "-";
        return `${item.nama_jalan || "-"} No ${item.no_kavling || "-"}: ${konsumen}${tipe ? " (" + tipe + ")" : ""}`;
    }

    function pmResetUploads() {
        const categories = $(".pm-upload-form").map(function() {
            return $(this).data("category");
        }).get();
        categories.forEach(function(category) {
            const input = document.getElementById(category);
            if (input) input.value = "";
            $("#list_" + category).html("");
            $("#label_" + category).html("Bisa lebih dari 1 foto");
        });
        window.produksiUploadFileStore = {};
    }

    function pmShowWorkspace(show) {
        $("#pm-empty-state").toggleClass("d-none", show);
        $("#pm-workspace").toggleClass("d-none", !show);
    }

    function pmPopulateProgress(data) {
        const r = data || {};
        const progress = r.progres_bangunan || 0;
        $("#pm-form-id-kavling").val(pmSelectedKavling.id_kavling);
        $("#pm-form-id-produksi").val(pmSelectedKavling.id_produksi || r.id_produksi || "");
        $("#pm-progres-bangunan").val(progress);
        $("#pm-progres-label, #pm-summary-progress").text(progress);
        $("#pm-keterangan").val(r.keterangan || "");

        pmSetFlatpickrValue("#pm-tanggal-pembangunan", r.tanggal_pembangunan || "");
        pmSetFlatpickrValue("#pm-tanggal-rencana", r.tanggal_rencana_selesai_pembangunan || "");
        pmSetFlatpickrValue("#pm-tanggal-selesai", r.tanggal_selesai_pembangunan || "");
        $("#pm-tanggal-pembangunan-old").val(r.tanggal_pembangunan || "");
        $("#pm-tanggal-rencana-old").val(r.tanggal_rencana_selesai_pembangunan || "");
        $("#pm-tanggal-selesai-old").val(r.tanggal_selesai_pembangunan || "");

        $("#pm-listrik-jenis").val(r.listrik_jenis || "PLN");
        $("#pm-listrik-pln").val(r.listrik_pln || "");
        $("#pm-air-jenis").val(r.air_jenis || "Air Tanah");
        $("#pm-air-pdam-no").val(r.air_pdam_no || "");
        $("#pm-preserve-bp").val(r.bp || "");
        $("#pm-preserve-st-jalan").val(r.st_jalan || "");
        $("#pm-preserve-st-saluran").val(r.st_saluran || "");
        $("#pm-preserve-st-air").val(r.st_air || "");
        $("#pm-preserve-lpa-tanggal").val(r.lpa_tanggal || "");
        $("#pm-preserve-listrik-disediakan-no").val(r.listrik_disediakan_no || "");
        $("#pm-preserve-listrik-disediakan-tanggal").val(r.listrik_disediakan_tanggal || "");
        $("#pm-preserve-air-deskripsi-unit").val(r.air_deskripsi_unit || "");
        $("#pm-preserve-sumurbor").val(r.sumurbor || "");
        $("#pm-preserve-sumurbor-keterangan").val(r.sumurbor_keterangan || "");
        $("#pm-preserve-sumurbor-tanggal").val(r.sumurbor_tanggal || "");

        ["st_0", "st_25", "st_50", "st_75", "st_100", "lpa", "slo"].forEach(function(field) {
            $("#pm-" + field.replace(/_/g, "-")).prop("checked", String(r[field] || "") === "1");
        });
    }

    function pmRenderFiles(files) {
        const $list = $("#pm-file-list");
        if (!files || files.length === 0) {
            $list.html('<div class="text-muted">Belum ada foto tersimpan.</div>');
            return;
        }

        const html = files.map(function(file) {
            const title = file.file_keterangan || file.kategori || file.file_name || "Foto";
            const coordinate = file.foto_lat && file.foto_lng ? `${Number(file.foto_lat).toFixed(6)}, ${Number(file.foto_lng).toFixed(6)}` : "-, -";
            const preview = file.thumbnail_url || file.access_url || file_url("file_produksi", file.id);
            const href = file.access_url || file_url("file_produksi", file.id);
            return `
                <div class="produksi-mobile-file-tile">
                    <a class="produksi-mobile-file-preview" href="${pmEscape(href)}" target="_blank">
                        <img src="${pmEscape(preview)}" alt="">
                    </a>
                    <div class="produksi-mobile-file-body">
                        <div class="produksi-mobile-file-title">${pmEscape(title)}</div>
                        <div class="produksi-mobile-file-meta">${pmEscape(file.kategori || "-")}</div>
                        <div class="produksi-mobile-file-meta">Tanggal foto: ${pmEscape(pmFormatDate(file.tgl_capture))}</div>
                        <div class="produksi-mobile-file-meta">Titik koordinat: ${pmEscape(coordinate)}</div>
                    </div>
                </div>
            `;
        }).join("");
        $list.html(html);
    }

    function pmRenderHistory(history, append, meta) {
        const $list = $("#pm-history-list");
        if (!append) $list.html("");
        if (!history || history.length === 0) {
            if (!append) $list.html('<div class="text-muted">Belum ada riwayat.</div>');
        } else {
            $list.append(history.map(function(item) {
                return `
                    <div class="produksi-mobile-history-item">
                        <div class="produksi-mobile-history-title">${pmEscape(item.summary || "Data produksi diperbarui")}</div>
                        <div class="produksi-mobile-history-meta">${pmEscape(pmFormatDate(item.created_at))} oleh ${pmEscape(item.username || "-")}</div>
                    </div>
                `;
            }).join(""));
        }

        pmHistoryOffset = meta ? (meta.history_next_offset || 0) : 0;
        $("#pm-history-more").toggleClass("d-none", !(meta && meta.history_has_more));
    }

    function pmLoadHistory(append) {
        if (!pmSelectedKavling) return;
        $.ajax({
            url: base_url + "api/produksi/history",
            type: "post",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_kavling: pmSelectedKavling.id_kavling,
                history_limit: pmConfig.historyLimit,
                history_offset: append ? pmHistoryOffset : 0
            },
            success: function(response) {
                csrfHash = response.token;
                pmRenderHistory(response.history || [], append, response);
            },
            error: function() {
                $("#pm-history-list").html('<div class="text-danger">Gagal memuat riwayat.</div>');
            }
        });
    }

    function pmLoadProduksi() {
        if (!pmSelectedKavling) return;
        pmShowWorkspace(true);
        pmResetUploads();
        $("#pm-summary-title").text(pmKavlingText(pmSelectedKavling));
        $("#pm-summary-meta").text([pmSelectedKavling.no_tipe_rumah, pmSelectedKavling.tipe_rumah].filter(Boolean).join(" - ") || "-");
        $("#pm-summary-konsumen").text(pmSelectedKavling.nama_konsumen || "-");

        $.ajax({
            url: base_url + "api/produksi/get_data_by_id",
            type: "post",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_kavling: pmSelectedKavling.id_kavling,
                id_produksi: pmSelectedKavling.id_produksi || ""
            },
            beforeSend: function() {
                loading(true);
            },
            success: function(response) {
                loading(false);
                csrfHash = response.token;
                pmPopulateProgress(response);
                pmRenderFiles(response.files || []);
                pmLoadHistory(false);
            },
            error: function() {
                loading(false);
                Swal.fire("Error", "Gagal memuat data produksi", "error");
            }
        });
    }

    function pmSelectInitialKavling(idKavling) {
        if (!idKavling) return;
        $.ajax({
            url: base_url + "api/produksi/getKavling",
            type: "post",
            dataType: "json",
            data: {
                [csrfName]: csrfHash,
                id_proyek: pmConfig.idProyek,
                id_kavling: idKavling
            },
            success: function(response) {
                csrfHash = response.token;
                const item = response.data && response.data[0] ? response.data[0] : null;
                if (!item) return;
                const option = new Option(pmKavlingText(item), item.id_kavling, true, true);
                $("#pm-id-kavling").append(option).trigger("change");
                pmSelectedKavling = item;
                pmLoadProduksi();
            }
        });
    }

    function pmSaveProgress() {
        if (!pmSelectedKavling) {
            Swal.fire("Error", "Pilih kavling terlebih dahulu", "error");
            return;
        }

        const formData = new FormData(document.getElementById("pm-progress-form"));
        formData.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + "api/produksi/save",
            type: "post",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                simpanBtn("#pm-save-progress", true);
            },
            success: function(response) {
                csrfHash = response.token;
                simpanBtn("#pm-save-progress", false);
                if (response.success === true) {
                    Swal.fire("Berhasil", response.messages || "Data produksi tersimpan", "success");
                    pmLoadProduksi();
                } else {
                    Swal.fire("Error", response.messages || "Gagal menyimpan data", "error");
                }
            },
            error: function() {
                simpanBtn("#pm-save-progress", false);
                Swal.fire("Error", "Gagal menyimpan data produksi", "error");
            }
        });
    }

    function pmUploadCategory(form) {
        if (!pmSelectedKavling) {
            Swal.fire("Error", "Pilih kavling terlebih dahulu", "error");
            return;
        }

        const category = $(form).data("category");
        const input = document.getElementById(category);
        if (!input || input.files.length === 0) {
            Swal.fire("Error", "Pilih minimal 1 foto", "error");
            return;
        }

        const formData = new FormData(form);
        formData.append(csrfName, csrfHash);
        formData.append("id_kavling", pmSelectedKavling.id_kavling);
        formData.append("id_produksi", $("#pm-form-id-produksi").val() || pmSelectedKavling.id_produksi || "");

        $.ajax({
            url: base_url + "api/produksi/upload-mobile",
            type: "post",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function() {
                $(form).find(".pm-upload-btn").prop("disabled", true).html('Mengunggah <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                csrfHash = response.token;
                $(form).find(".pm-upload-btn").prop("disabled", false).html('<i class="fas fa-cloud-upload-alt mr-50"></i>Upload');
                if (response.success === true) {
                    Swal.fire("Berhasil", response.messages || "Foto berhasil diunggah", "success");
                    pmResetUploads();
                    pmRenderFiles(response.files || []);
                    pmLoadHistory(false);
                } else {
                    Swal.fire("Error", response.messages || "Gagal upload foto", "error");
                }
            },
            error: function() {
                $(form).find(".pm-upload-btn").prop("disabled", false).html('<i class="fas fa-cloud-upload-alt mr-50"></i>Upload');
                Swal.fire("Error", "Gagal upload foto", "error");
            }
        });
    }

    $(function() {
        $("#pm-id-kavling").select2({
            placeholder: "Cari jalan, nomor kavling, atau konsumen",
            allowClear: true,
            width: "100%",
            ajax: {
                url: base_url + "api/produksi/getKavling",
                dataType: "json",
                delay: 250,
                method: "post",
                data: function(params) {
                    return {
                        [csrfName]: csrfHash,
                        search: params.term,
                        id_proyek: pmConfig.idProyek
                    };
                },
                processResults: function(response) {
                    csrfHash = response.token;
                    return {
                        results: (response.data || []).map(function(item) {
                            return {
                                id: item.id_kavling,
                                text: pmKavlingText(item),
                                raw: item
                            };
                        })
                    };
                },
                cache: true
            }
        }).on("select2:select", function(event) {
            pmSelectedKavling = event.params.data.raw;
            pmLoadProduksi();
        }).on("select2:clear", function() {
            pmSelectedKavling = null;
            pmShowWorkspace(false);
        });

        $("#pm-progres-bangunan").on("input", function() {
            $("#pm-progres-label, #pm-summary-progress").text(this.value || 0);
        });

        $("#pm-save-progress").on("click", pmSaveProgress);
        $(".pm-upload-form").on("submit", function(event) {
            event.preventDefault();
            pmUploadCategory(this);
        });
        $("#pm-history-more").on("click", function() {
            pmLoadHistory(true);
        });

        if (typeof initFlatpickrHumanFriendly === "function") {
            initFlatpickrHumanFriendly(document);
        }

        pmSelectInitialKavling(pmConfig.initialIdKavling);
    });
