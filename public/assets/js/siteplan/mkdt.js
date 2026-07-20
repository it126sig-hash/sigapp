    const MKDT_HISTORY_LIMIT = 10;

    function escapeMkdtHistoryValue(value) {
        return $("<div>").text(value === null || value === undefined ? "" : value).html();
    }

    function resetMkdtHistoryTimeline() {
        $("#mkdt-history-timeline")
            .data("id-kavling", "")
            .data("next-offset", 0)
            .data("history-limit", MKDT_HISTORY_LIMIT)
            .html('<div class="text-muted">Memuat history...</div>');
    }

    function renderMkdtHistoryTimeline(history, meta, append) {
        const $target = $("#mkdt-history-timeline");
        const nextOffset = meta ? (meta.history_next_offset || 0) : 0;
        const limit = meta ? (meta.history_limit || MKDT_HISTORY_LIMIT) : MKDT_HISTORY_LIMIT;
        const hasMore = !!(meta && meta.history_has_more);

        $target
            .data("next-offset", nextOffset)
            .data("history-limit", limit);

        if (!append) {
            $target.html('<div class="mkdt-history-list"></div><div class="mkdt-history-action mt-2"></div>');
        }

        const $list = $target.find(".mkdt-history-list");
        if (!history.length && !append) {
            $list.html('<div class="text-muted">Belum ada history perubahan.</div>');
        }

        $.each(history, function(index, item) {
            $list.append(
                '<div class="mkdt-history-item">' +
                    '<div class="d-flex justify-content-between align-items-start flex-wrap">' +
                        '<div class="mkdt-history-title">' + escapeMkdtHistoryValue(item.action_label || item.action) + '</div>' +
                        '<div class="mkdt-history-meta">' + format_datetime(item.created_at) + '</div>' +
                    '</div>' +
                    '<div class="mkdt-history-meta mb-1">Oleh: ' + escapeMkdtHistoryValue(item.username || '-') + '</div>' +
                    '<div class="mkdt-history-summary">' + escapeMkdtHistoryValue(item.summary || '-') + '</div>' +
                '</div>'
            );
        });

        const $action = $target.find(".mkdt-history-action");
        if (hasMore) {
            $action.html(
                '<button type="button" class="btn btn-outline-primary btn-sm mkdt-history-load-more">Muat lagi</button>'
            );
        } else {
            $action.empty();
        }
    }

    function loadMkdtHistory(idKavling, append) {
        if (!idKavling) {
            resetMkdtHistoryTimeline();
            return;
        }

        const $target = $("#mkdt-history-timeline");
        const offset = append ? ($target.data("next-offset") || 0) : 0;
        const limit = $target.data("history-limit") || MKDT_HISTORY_LIMIT;

        if (!append) {
            $target.data("id-kavling", idKavling).data("next-offset", 0);
            $target.html('<div class="text-muted">Memuat history...</div>');
        } else {
            $target.find(".mkdt-history-action .btn").prop("disabled", true).html('Memuat <i class="fa fa-spinner fa-spin"></i>');
        }

        $.ajax({
            url: base_url + "api/mkdt/history",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: idKavling,
                history_limit: limit,
                history_offset: offset,
            },
            dataType: "json",
            success: function(res) {
                csrfHash = res.token;
                renderMkdtHistoryTimeline(res.history || [], res, append);
            },
            error: function() {
                if (!append) {
                    $target.html('<div class="text-danger">Gagal memuat history.</div>');
                } else {
                    $target.find(".mkdt-history-action .btn").prop("disabled", false).text("Muat lagi");
                }
            },
        });
    }

    $(document).off("click", ".mkdt-history-load-more").on("click", ".mkdt-history-load-more", function() {
        const idKavling = $("#mkdt-history-timeline").data("id-kavling");
        loadMkdtHistory(idKavling, true);
    });

    $(document).off("shown.bs.tab", "#mkdt-tab-history-link").on("shown.bs.tab", "#mkdt-tab-history-link", function() {
        const idKavling = $(".id_kavling").val();
        loadMkdtHistory(idKavling, false);
    });

    document.addEventListener("DOMContentLoaded", function() {
        const mkdtModal = document.getElementById("modal_divisi4");
        if (!mkdtModal) return;

        const scrollArea = document.getElementById("mkdt-main-scroll-area");
        const sections = Array.from(mkdtModal.querySelectorAll(".scroll-section"));
        const navItems = mkdtModal.querySelectorAll(".mkdt-scroll-nav");

        if (!scrollArea || sections.length === 0) return;

        navItems.forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                const targetEl = document.getElementById(this.getAttribute("href").substring(1));
                if (!targetEl) return;
                scrollArea.scrollTo({
                    top: targetEl.offsetTop - scrollArea.offsetTop,
                    behavior: "smooth"
                });
            });
        });

        scrollArea.addEventListener("scroll", function() {
            let current = "";
            const currentPosition = scrollArea.scrollTop;

            sections.forEach(section => {
                const sectionTop = section.offsetTop - scrollArea.offsetTop - 50;
                if (currentPosition >= sectionTop) {
                    current = section.getAttribute("id");
                }
            });

            navItems.forEach(item => {
                item.classList.toggle("active", item.getAttribute("href") === "#" + current);
            });
        });

        $("#modal_divisi4").on("hidden.bs.modal", function() {
            $("#mkdt-tab-form-link").tab("show");
            resetMkdtHistoryTimeline();
        });
    });
    $("#id_bank").select2({
  placeholder: "Pilih Bank",
  allowClear: true,
  ajax: {
    url: base_url + "api/bank/ambil",
    dataType: "json",
    delay: 250,
    method: "get",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      // csrfHash = r.token;

      let results = [];
      $.each(r.data, function (i, v) {
        results.push({
          id: v.id,
          text: `${v.bank} ${v.keterangan ? "(" + v.keterangan + ")" : ""}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});


const containerIsiKonsumen = $("#tab-isi-konsumen");
let latestIsiDataKonsumenRequestId = 0;

function isValidKonsumen(i) {
  let isValid = true;

  if (i == "#idk_biaya-tab" || i == "#idk_data_konsumen-tab") {
    $("#fm-idk_keu")
      .find("input.tab1[required], select.tab1[required]")
      .each(function () {
        let id = $(this).attr("id");
        let value = $(this).val().trim();

        if (value === "") {
          let labelText = $('label[for="' + id + '"]').text();
          isValid = false;
          showToast(labelText + " harus diisi", "warning");
          $(this).focus();
          this.reportValidity();
          return false; // Stop the $.each loop immediately if an invalid field is found
        }
      });
    return isValid;
  } else if (i == "#idk_tagihan-tab") {
    if ($("#idk-booking_tgl").val() == "") {
      showToast("Tanggal Booking harus diisi", "warning");
      $("#idk-booking_tgl").get(0)._flatpickr.open();
      isValid = false;
    } else if ($("#idk-booking_fee").val() == "") {
      showToast("Booking Fee harus diisi", "warning");
      $("#idk-booking_fee").focus();
      isValid = false;
    }

    return isValid;
  } else if (i == "save") {
    if (parseFloat(removeComma($("#mk-total_tot").val() || 0)) > 0) {
      if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
        showToast(
          "Total tagihan tida sesuai dengan total harus dibayar",
          "danger",
        );
        isValid = false;
      }
    }
    return isValid;
  }
}
// Klik NEXT/SIMPAN
function btnNext(next) {
  let isValid = isValidKonsumen(next);
  if (next === "save" && isValid) {
    Swal.fire({
      title: "Konfirmasi",
      text: "Apakah data sudah benar dan akan disimpan?",
      showDenyButton: true,
      confirmButtonText: "Simpan",
      denyButtonText: `Kembali`,
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        simpan_dt_konsumen_keuangan();
      }
    });
  } else {
    if (isValid) {
      $(next).tab("show");
    } else {
      return;
    }
  }
}
$("a.locked").on("click", function (e) {
  e.preventDefault();
  e.stopPropagation();
});

// ====== Helpers ======
const ui = {
  form: {
    kons: $("#fm-idk_keu"),
  },
  loading: $("#loading"),
  tabs: {
    konsumen: $("#idk_data_konsumen-tab"),
  },
  btn: {
    printSPPTB: $("#btn-print_spptb"),
    addKons: $("#add-form-btn-idk_keu"),
    prevKons: $("#prev-form-btn-idk_keu"),
    delKons: $("#delete-btn-idk_keu"),
  },
  fields: {
    id_kavling: $(".id_kavling"),
    id_mkdt: $("#idk-id_mkdt"),
    hargaAkhirSelect: $("#idk-harga_akhir"),
    rincian: $("#idk-rincian"),
    richText: () => $("#idk-rincian").prev(".richText-editor"),
    // file previews
    ktpHere: $("#idk-file_ktp-here"),
    npwpHere: $("#idk-file_npwp-here"),
    ddHere: $("#idk-file_data_diri-here"),
  },
};

function withLoading(fn) {
  ui.loading.removeClass("hidden");
  return Promise.resolve(fn()).finally(() => ui.loading.addClass("hidden"));
}

function disableForm(disabled) {
  ui.form.kons.find("input:text, select, textarea").prop("disabled", disabled);
}

function setVal(sel, val) {
  $(sel)
    .val(val ?? "")
    .triggerHandler("input");
}
function setDate(dateStr, sel) {
  if (
    dateStr &&
    dateStr !== "0000-00-00" &&
    document.querySelector(sel)?._flatpickr
  ) {
    document.querySelector(sel)._flatpickr.setDate(dateStr);
  }
}
function setRichText(html) {
  ui.fields.richText().trigger("setContent", html ?? "");
  ui.fields.rincian.html(html ?? "");
}


function formatDateSafe(d) {
  return d ? format_date(d) : "-";
}

// ====== Data layer ======
function getTransaksiDetail({ id_mkdt, id_kavling, id_hargajual }) {
  return $.ajax({
    url: base_url + "api/transaksi/ambilsatu",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, id_mkdt, id_kavling, id_hargajual },
  });
}

// ====== Binders ======
function bindKavlingContext(sh) {
  // navigasi/tab & tombol
  ui.tabs.konsumen.tab("show");
  // updateButtons(ui.btn.addKons, ui.btn.prevKons);
  // reset form
  ui.form.kons[0].reset();
  ui.form.kons.find(".num").prop("disabled", false);

  $("#mk-total_bb, #mk-total_um").val(0);
  $("#list_cicilan_here").empty();
  $("#mk-total_cicilan_um, #total_cicilan_bb").val(0).triggerHandler("input");
  $("#id_list_keu, #id_list_keu_bb").val("");
  $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);
  $("#idk_data_baru").val(1);
  $("#idk-rincian").prev(".richText-editor").trigger("setContent", "");

  // set state dasar
  state.id_kavling = sh.data.id_kavling || sh.id.substr(3);
  state.id_mkdt = sh.data.id_mkdt || null;

  // isi hidden fields
  ui.fields.id_kavling.val(state.id_kavling);
  ui.fields.id_mkdt.val(state.id_mkdt);

  // tombol print
  if (state.id_mkdt == null) {
    ui.btn.printSPPTB
      .attr(
        "onclick",
        `return swal('error', 'Data konsumen harus disimpan terlebih dahulu');`,
      )
      .attr("target", "")
      .prop("href", "#");
  } else {
    ui.btn.printSPPTB
      .attr("onclick", "")
      .prop(
        "href",
        `${base_url}print/spptb?id_mkdt=${state.id_mkdt}&id_kavling=${state.id_kavling}&id_proyek=${dt_proyek.id_proyek}`,
      )
      .attr("target", "_blank");
  }
}

function fillPriceSection(h, dk) {
  if (!h?.hargajual) return;
  // masal: map kunci â†’ #mk-*
  const mkMap = [
    "hargajual",
    "hargajual_net",
    "kpr",
    "uang_muka",
    "biaya_adm",
    "bphtb",
    "ppn",
    "biaya_proses",
    "harga_penambahan",
    "harga_penambahan_tanah",
  ];
  // console.log(h, dk);
  setVal("#mk-diskon_uang_muka", h.diskon_uang_muka);
  mkMap.forEach((k) => setVal(`#mk-${k}`, h[k]));

  setDatePicker(h.tgl_harga, "#mk-tgl_harga");
  setVal("#idk-tgl_harga", formatDateSafe(h.tgl_harga));
  setVal("#idk-harga_kpr", h.kpr);

  setVal("#idk-mkdt_hargajual", h.hargajual);
  $("#idk-mkdt_hargajual_by").text(dk?.username_harga_akhir ?? "-");
  $("#idk-mkdt_hargajual_tgl").text(formatDateSafe(dk?.harga_akhir_tgl));
}

function fillDiskresi(dk) {
  if (dk?.username_diskresi) {
    $("#idk-diskresi_st").removeClass("hidden");
    setVal("#idk-diskresi_harga", dk.diskresi_harga);
    setVal("#idk-diskresi_memo", dk.diskresi_memo);
    $("#idk-diskresi_oleh").text(dk.username_diskresi);
    $("#idk-diskresi_tgl").text(formatDateSafe(dk.diskresi_at));
  } else {
    $("#idk-diskresi_st").addClass("hidden");
    setVal("#idk-diskresi_harga", "-");
    setVal("#idk-diskresi_memo", "-");
    $("#idk-diskresi_oleh").text("-");
    $("#idk-diskresi_tgl").text("-");
  }
}

function fillFiles(v) {
  const ktpUrl = v?.ktp_access_url || v?.ktp_lok;
  const npwpUrl = v?.npwp_access_url || v?.npwp_lok;
  const dataDiriUrl = v?.data_diri_access_url || v?.data_diri_lok;

  renderExistingUploadPreview("file_ktp", ktpUrl, "KTP");
  renderExistingUploadPreview("file_npwp", npwpUrl, "NPWP");
  renderExistingUploadPreview("file_data_diri", dataDiriUrl, "Data Diri", "pdf");

  setImgOrPlaceholder(ui.fields.ktpHere, ktpUrl, not_found);
  setImgOrPlaceholder(ui.fields.npwpHere, npwpUrl, not_found, "90%");
  ui.fields.ddHere
    .html(dataDiriUrl ? "Klik untuk melihat file" : "Tidak ada data")
    .prop("href", resolveFileHref(dataDiriUrl, not_found));
}

function renderExistingUploadPreview(inputId, src, label, type = "image") {
  const input = document.getElementById(inputId);
  if (!input) return;

  const dropzone = input.closest(".dropzone");
  if (!dropzone) return;

  const preview = dropzone.querySelector(".dz-preview");
  const placeholder = dropzone.querySelector(".dz-placeholder");
  if (!preview || !placeholder) return;

  if (!isNotEmpty(src)) {
    preview.innerHTML = "";
    preview.style.display = "none";
    placeholder.style.display = "block";
    return;
  }

  const href = resolveFileHref(src);
  preview.innerHTML =
    type === "pdf"
      ? `<div class="p-2 border rounded bg-light text-center">
          <i class="fa fa-file-pdf fa-3x text-danger"></i>
          <div class="text-truncate">${escapeHtml(label)} sudah diunggah</div>
        </div>`
      : `<img src="${escapeAttribute(href)}"
             class="preview-thumb"
             style="height:100%; object-fit:contain;"
             alt="${escapeAttribute(label)}">
        <div class="text-truncate mb-1">${escapeHtml(label)} sudah diunggah</div>`;
  preview.style.display = "block";
  placeholder.style.display = "none";
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function escapeAttribute(value) {
  return escapeHtml(value).replace(/`/g, "&#096;");
}

let mkdtClipboardBound = false;
let ktpOcrBound = false;
let tesseractLoadPromise = null;

function bindMkdtClipboardUpload() {
  if (mkdtClipboardBound) return;
  mkdtClipboardBound = true;

  document.addEventListener("paste", async function (e) {
    if (!$("#modal-isi_data_konsumen").hasClass("show")) return;

    const pastedFile = getImageFileFromClipboard(e);
    if (!pastedFile) return;

    e.preventDefault();
    const result = await Swal.fire({
      title: "Upload dari Clipboard",
      text: "Pilih tujuan file yang ditempel.",
      icon: "question",
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: "KTP",
      denyButtonText: "NPWP",
      cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
      assignFileToInput("file_ktp", pastedFile, "ktp-clipboard");
      runKtpOcrFromInput(true);
    } else if (result.isDenied) {
      assignFileToInput("file_npwp", pastedFile, "npwp-clipboard");
    }
  });
}

function bindKtpOcr() {
  if (ktpOcrBound) return;
  ktpOcrBound = true;

  $("#btn-ocr-ktp").on("click", function () {
    runKtpOcrFromInput(false);
  });
}

function getImageFileFromClipboard(e) {
  const clipboard = e.originalEvent?.clipboardData || e.clipboardData;
  if (!clipboard?.items) return null;

  for (const item of clipboard.items) {
    if (item.kind === "file" && item.type.startsWith("image/")) {
      return item.getAsFile();
    }
  }

  return null;
}

function assignFileToInput(inputId, file, prefix) {
  const input = document.getElementById(inputId);
  if (!input || !file) return;

  const extension = getImageExtension(file.type);
  const uploadFile = new File(
    [file],
    file.name || `${prefix}-${Date.now()}.${extension}`,
    { type: file.type || "image/png" },
  );
  const dataTransfer = new DataTransfer();
  dataTransfer.items.add(uploadFile);
  input.files = dataTransfer.files;
  input.dispatchEvent(new Event("change", { bubbles: true }));
}

function getImageExtension(type) {
  const map = {
    "image/jpeg": "jpg",
    "image/png": "png",
    "image/webp": "webp",
  };
  return map[type] || "png";
}

async function runKtpOcrFromInput(fromClipboard = false) {
  const input = document.getElementById("file_ktp");
  const file = input?.files?.[0];

  if (!file) {
    return Swal.fire("OCR KTP", "Pilih atau paste gambar KTP terlebih dahulu.", "warning");
  }

  if (!file.type.startsWith("image/")) {
    return Swal.fire("OCR KTP", "OCR hanya bisa membaca file gambar.", "warning");
  }

  if (fromClipboard) {
    const confirmOcr = await Swal.fire({
      title: "Jalankan OCR KTP?",
      text: "Sistem akan membaca gambar KTP dan menyiapkan isian form.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, baca KTP",
      cancelButtonText: "Lewati",
    });
    if (!confirmOcr.isConfirmed) return;
  }

  try {
    Swal.fire({
      title: "Membaca KTP",
      html: "Menyiapkan OCR...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const Tesseract = await loadTesseract();
    const result = await Tesseract.recognize(file, "ind+eng", {
      logger: updateOcrProgress,
    });

    Swal.close();
    const parsed = parseKtpOcrText(result?.data?.text || "");
    if (!parsed.nik && !parsed.nama && !parsed.alamat) {
      return Swal.fire(
        "OCR KTP",
        "Teks KTP belum bisa dikenali dengan cukup jelas.",
        "warning",
      );
    }

    await confirmApplyKtpOcr(parsed);
  } catch (err) {
    Swal.close();
    console.error(err);
    Swal.fire("OCR KTP", "Gagal menjalankan OCR KTP.", "error");
  }
}

function loadTesseract() {
  if (window.Tesseract) return Promise.resolve(window.Tesseract);
  if (tesseractLoadPromise) return tesseractLoadPromise;

  tesseractLoadPromise = new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js";
    script.async = true;
    script.onload = () => resolve(window.Tesseract);
    script.onerror = () => reject(new Error("Gagal memuat Tesseract.js"));
    document.head.appendChild(script);
  });

  return tesseractLoadPromise;
}

function updateOcrProgress(message) {
  if (!message?.status) return;

  const progress = Number.isFinite(message.progress)
    ? ` ${Math.round(message.progress * 100)}%`
    : "";
  const container = document.getElementById("swal2-html-container");
  if (container) {
    container.textContent = `${message.status}${progress}`;
  }
}

function parseKtpOcrText(text) {
  const lines = String(text || "")
    .split(/\r?\n/)
    .map((line) => cleanOcrLine(line))
    .filter(Boolean);
  const normalized = lines.join("\n");

  return {
    nik: extractKtpNik(normalized),
    nama: extractKtpLine(lines, /^NAMA\b/i, [/TEMPAT/i, /LAHIR/i, /JENIS/i, /ALAMAT/i]),
    alamat: extractKtpAddress(lines),
  };
}

function cleanOcrLine(line) {
  return String(line || "")
    .replace(/[|]/g, "I")
    .replace(/\s+/g, " ")
    .replace(/^[^\w]+|[^\w]+$/g, "")
    .trim();
}

function extractKtpNik(text) {
  const byLabel = text.match(/(?:NIK|N1K)\s*[:=\-]?\s*([0-9OILSB\s.\-]{12,24})/i);
  const fallback = text.match(/(?:[0-9OILSB][\s.\-]?){16,}/i);
  const raw = byLabel?.[1] || fallback?.[0] || "";
  const nik = raw
    .replace(/[Oo]/g, "0")
    .replace(/[IiLl]/g, "1")
    .replace(/[Ss]/g, "5")
    .replace(/[Bb]/g, "8")
    .replace(/\D/g, "");
  return nik.length >= 16 ? nik.substring(0, 16) : "";
}

function extractKtpLine(lines, labelRegex, stopRegexes = []) {
  for (let i = 0; i < lines.length; i++) {
    if (!labelRegex.test(lines[i])) continue;

    let value = lines[i]
      .replace(labelRegex, "")
      .replace(/^[:=\-\s]+/, "")
      .trim();

    if (!value && lines[i + 1] && !matchesAny(lines[i + 1], stopRegexes)) {
      value = lines[i + 1].trim();
    }

    return cleanKtpValue(value);
  }

  return "";
}

function extractKtpAddress(lines) {
  const stopRegexes = [/AGAMA/i, /STATUS/i, /PEKERJAAN/i, /KEWARGANEGARAAN/i, /BERLAKU/i];
  for (let i = 0; i < lines.length; i++) {
    if (!/^ALAMAT\b/i.test(lines[i])) continue;

    const parts = [];
    const first = lines[i]
      .replace(/^ALAMAT\b/i, "")
      .replace(/^[:=\-\s]+/, "")
      .trim();
    if (first) parts.push(first);

    for (let j = i + 1; j < Math.min(lines.length, i + 6); j++) {
      if (matchesAny(lines[j], stopRegexes)) break;
      if (/^(RT|RW|KEL|DESA|KEC|KAB|KOTA)\b/i.test(lines[j]) || parts.length === 0) {
        parts.push(lines[j]);
      }
    }

    return cleanKtpValue(parts.join(", "));
  }

  return "";
}

function matchesAny(value, regexes) {
  return regexes.some((regex) => regex.test(value));
}

function cleanKtpValue(value) {
  return String(value || "")
    .replace(/\s*[:=]\s*/g, " ")
    .replace(/\s+/g, " ")
    .replace(/^\W+|\W+$/g, "")
    .trim();
}

async function confirmApplyKtpOcr(parsed) {
  const rows = [
    ["NIK", parsed.nik],
    ["Nama", parsed.nama],
    ["Alamat", parsed.alamat],
  ]
    .filter(([, value]) => isNotEmpty(value))
    .map(
      ([label, value]) =>
        `<tr><th class="text-left pr-2">${escapeHtml(label)}</th><td>${escapeHtml(value)}</td></tr>`,
    )
    .join("");

  const result = await Swal.fire({
    title: "Gunakan hasil OCR?",
    html: `<table class="table table-sm mb-0">${rows}</table>`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Isi Form",
    cancelButtonText: "Batal",
  });

  if (!result.isConfirmed) return;

  if (parsed.nik) setVal("#idk-nik_konsumen", parsed.nik);
  if (parsed.nama) setVal("#idk-nama_konsumen", parsed.nama);
  if (parsed.alamat) setVal("#idk-alamat_konsumen", parsed.alamat);
}

$("#idk-is_allin").change(function () {
  is_allin(this);
});

$("#modal-isi_data_konsumen")
  .off("input.mkdtTotal change.mkdtTotal", ".mk-fm, #idk-is_subsidi")
  .on("input.mkdtTotal change.mkdtTotal", ".mk-fm, #idk-is_subsidi", function () {
    if (typeof sum_mktotal === "function") {
      sum_mktotal();
    }
  });

function is_allin(e) {
  if (e.value == "0") {
    harga_Total = $("#mk-tgt").val();
    $("#mk-harga_allin").hide();
  } else {
    harga_Total = $("#mk-harga_allin").val();
    $("#mk-harga_allin").show();
  }
  sum_mktotal();
}

function setIdkPerluRefund(value) {
  const normalized = String(value ?? "0") === "1" ? "1" : "0";
  $(`#modal-isi_data_konsumen input[name="dt-perlu_refund"][value="${normalized}"]`).prop(
    "checked",
    true,
  );
}

function updateIdkBatalSection() {
  const isBatal = $("#idk-status_mkdt").val() === "Batal";
  $("#idk-show_keterangan_batal").toggleClass("hidden", !isBatal);
  if (!isBatal) {
    $("#idk-keterangan_batal").val("");
    setIdkPerluRefund(0);
  }
}

function fillMkdt(v) {
  if (!v) return;

  if (v.status_mkdt === "Batal") {
    disableForm(true);
    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass("hidden");
    ui.form.kons.find("#idk-id_konsumen, #idk-id_keuangan0").val("");
    ui.btn.delKons.removeClass("hidden");
  }

  // console.log(v)

  if (v.id_konsumen) $("#idk_data_baru").val(0);

  // basic fields
  setVal("#idk-is_allin", v.is_allin);
  $("#idk-is_allin").change();

  setVal("#mk-harga_allin", v.harga_allin);

  setVal("#idk-status_mkdt", v.status_mkdt);
  setVal("#idk-keterangan_batal", v.keterangan_batal);
  setIdkPerluRefund(v.perlu_refund);
  setDate(v.booking_tgl, "#idk-booking_tgl");
  setVal("#idk-booking_fee", v.booking_fee);

  ui.form.kons.find("#idk-id_konsumen").val(v.id_konsumen ?? "");

  setVal("#st-mkdt-no_spptb", v.no_spptb);
  setVal("#idk-nama_konsumen", v.nama_konsumen);
  setVal("#idk-nik_konsumen", v.nik_konsumen);
  setVal("#idk-alamat_konsumen", v.alamat_konsumen);
  setVal("#idk-npwp_konsumen", v.npwp_konsumen);
  setVal("#idk-hp_konsumen", v.hp_konsumen);
  setVal("#idk-email_konsumen", v.email_konsumen);
  setVal("#idk-status_konsumen", v.status_konsumen);

  setVal("#idk-nama_instansi", v.nama_instansi);
  setVal("#idk-alamat_instansi", v.alamat_instansi);
  setVal("#idk-tel_instansi", v.tel_instansi);
  setVal("#idk-email_instansi", v.email_instansi);
  setVal("#idk-alamat_surat", v.alamat_surat);
  setVal("#idk-pekerjaan", v.pekerjaan);
  setVal("#idk-lama_bekerja", v.lama_bekerja);
  setVal("#idk-bidang_pekerjaan", v.bidang_pekerjaan);

  setVal("#idk-status_pernikahan", v.status_pernikahan);
  setVal("#idk-nama_pasangan", v.nama_pasangan);
  setVal("#idk-nik_pasangan", v.nik_pasangan);
  setVal("#idk-hp_pasangan", v.hp_pasangan);
  setVal("#idk-status_pekerjaan_pasangan", v.status_pekerjaan_pasangan);
  setVal("#idk-instansi_pasangan", v.instansi_pasangan);

  setVal("#idk-sales", v.sales);

  setVal("#idk-is_kpr", v.is_kpr);
  setVal("#idk-is_subsidi", v.is_subsidi);
  setVal("#idk-jenis_subsidi", v.jenis_subsidi);

  setRichText(v.rincian);

  // if (v.keuangan_saved_by) {
  setVal("#mk-hargajual", v.harga_jual);
  setVal("#mk-hargajual_net", v.harga_jual_net);
  setVal("#mk-kpr", v.harga_kpr);
  setVal("#mk-uang_muka", v.harga_uang_muka);
  setVal("#mk-biaya_adm", v.harga_administrasi);
  setVal("#mk-bphtb", v.harga_bphtb);
  setVal("#mk-ppn", v.harga_ppn);
  setVal("#mk-biaya_proses", v.harga_biaya_proses);
  setVal("#mk-harga_sbum", v.harga_sbum);
  setVal("#mk-harga_penambahan", v.harga_penambahan);
  setVal("#mk-harga_penambahan_tanah", v.harga_penambahan_tanah);
  setVal("#mk-diskon_uang_muka", v.harga_diskon_uang_muka);
  // }

  setVal("#idk-promo", v.promo);

  // KPR turun
  // setVal("#mk-harga_kpr_acc", v.harga_kpr_acc);
  const turun_kpr = v.harga_kpr_acc ? v.harga_kpr - v.harga_kpr_acc : 0;
  // setVal("#mk-harga_penambahan_um", turun_kpr);

  // SPPTB file
  const spptbLink = v.file_spptb
    ? `<a href="${
        file_url('mkdt_file_spptb', v.id_mkdt)
      }" target=_blank class="btn btn-outline-primary">Klik untuk melihat File SPPTB Yang Sudah ditandatangan</a>`
    : `Tidak ada data`;
  $("#spptb_ttd_file").html(spptbLink);
}

$("#idk-status_mkdt").change(updateIdkBatalSection);

function fillSpptbList(list) {
  const html =
    list && list.length
      ? list
          .map(
            (val, i) => `
      <tr>
        <td>${i + 1}</td>
        <td><a href="${val.access_url || file_url('file_spptb', val.id)}" target=_blank>Klik untuk melihat file</a></td>
        <td>${val.username}<br>${format_datetime(val.created_at)}</td>
      </tr>`,
          )
          .join("")
      : '<tr><td colspan="3">Tidak ada data</td></tr>';
  $("#spptb_ttd_file-here").html(html);
}

function fillTagihan(tg) {
  state.data_um = {};
  state.data_bb = {};
  if (tg.length == 0) return;

  let a = it; // mengikuti variabel lamamu
  tg.forEach((v) => {
    const id = "lk" + a;
    // if (v.status === "UM") {
    state.data_um[id] = {
      id_list_keu: id,
      id_keuangan: v.id_keuangan,
      berita_acara: v.berita_acara,
      nominal: num_format(v.nominal),
      jatuh_tempo_tgl: v.jatuh_tempo_tgl,
      is_void: Number(v.is_void) === 1,
      void_reason: v.void_reason,
    };
    // } else if (v.status === "BB") {
    //   state.data_bb[id] = {
    //     id_list_keu_bb: id,
    //     id_keuangan_bb: v.id_keuangan,
    //     berita_acara_bb: v.berita_acara,
    //     nominal_bb: num_format(v.nominal),
    //     jatuh_tempo_tgl_bb: v.jatuh_tempo_tgl,
    //   };
    // }
    a++;
  });

  // data_um = state.data_um
  // data_bb = state.data_bb
  // render list tagihan sekali saja
  tambah_ketagihan();
  it = a;
}

function lihat_total() {
    var harga_jual = removeComma(($("#detail_harga_jual").val() == '') ? 0 : $("#detail_harga_jual").val()),
        harga_diskon = removeComma(($("#detail_harga_diskon").val() == '') ? 0 : $("#detail_harga_diskon").val()),
        harga_penambahan = removeComma(($("#detail_harga_penambahan").val() == '') ? 0 : $("#detail_harga_penambahan").val()),
        harga_administrasi = removeComma(($("#detail_harga_administrasi").val() == '') ? 0 : $("#detail_harga_administrasi").val()),
        harga_ppn = removeComma(($("#detail_harga_ppn").val() == '') ? 0 : $("#detail_harga_ppn").val()),
        harga_bphtb = removeComma(($("#detail_harga_bphtb").val() == '') ? 0 : $("#detail_harga_bphtb").val()),
        harga_biaya_proses = removeComma(($("#detail_harga_biaya_proses").val() == '') ? 0 : $("#detail_harga_biaya_proses").val()),
        harga_kpr = removeComma(($("#detail_harga_kpr").val() == '') ? 0 : $("#detail_harga_kpr").val()),
        total_biaya = 0;

    total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

    $("#detail_total_biaya").val(total_biaya).keyup();

}
//sum tagihan

function sum_tg(e = 0, bb = '') {
    e = parseFloat(removeComma(e))

    let total_keu = parseFloat(removeComma($("#mk-total_tot").val()) || 0)
    let cicilan_keu = parseFloat(removeComma($("#mk-total_cicilan_um").val()) || 0)

    if (cicilan_keu + e > total_keu)
        $("#nominal").val(total_keu - cicilan_keu).keyup()
}

var it = 0;
/***************** list tagihan ****************/
function tambah_(e = '') {
    let a = (e == '_bb') ? e : '_um'
    if ($("#mk-total_cicilan_um").val() == $("#mk-total_tot").val()) {
        swal('error', "Tidak bisa menambahkan tagihan", "Total tagihan tidak bisa melebeihi total harus dibayar", false);
        return false;
    } else {
        if (!$("#berita_acara" + e).val() || !$("#nominal" + e).val() || !$("#jatuh_tempo_tgl" + e).val()) {
            swal('error', "Nominal dan jatuh tempo tidak boleh kosong", null, false);
            return false;
        }
        Swal.fire({
            title: 'Simpan data?',
            text: "Pastikan data sudah terisi dengan benar!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya!',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: !1
        }).then(function(t) {
            if (t.value) {
                tambah(e)
            }
        })
    }
}

function tambah(e = '') {
    let i = 'lk' + it

    if (state.data_um[$("#id_list_keu" + e).val()])
        i = $("#id_list_keu" + e).val()

    state.data_um[i] = ({
        id_list_keu: i,
        id_keuangan: $("#id_keuangan").val(),
        berita_acara: $("#berita_acara").val(),
        nominal: $("#nominal").val(),
        jatuh_tempo_tgl: $("#jatuh_tempo_tgl").val(),
    })

    tambah_ketagihan(e)

    fp = flatpickr("#jatuh_tempo_tgl", {
        altInput: true,
        altFormat: 'F j, Y',
        dateFormat: 'Y-m-d'
    })

    var d = new Date(
        $("#jatuh_tempo_tgl").val()
    ).fp_incr(30);

    fp.setDate(d);

    it += 1;
}

function removeFromTable(x, y = null) {
    const bucket = y == '_bb' ? 'data_bb' : 'data_um';
    const row = state[bucket] && state[bucket][x];
    const idKeuangan = row && row.id_keuangan;

    Swal.fire({
        title: 'Hapus Data?',
        text: "Data tidak bisa dipulihkan!",
        type: 'danger',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya!',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function(t) {
        if (!t.value) return;

        // Baris baru yang belum tersimpan (belum ada id_keuangan) cukup dihapus dari state.
        if (!idKeuangan) {
            delete state[bucket][x];
            tambah_ketagihan();
            return;
        }

        $.ajax({
            url: base_url + 'tagihan/hapus',
            type: 'post',
            dataType: 'json',
            data: {
                id_keuangan: idKeuangan,
                [csrfName]: csrfHash
            },
            success: function(r) {
                if (r.token) csrfHash = r.token;

                if (!r.success) {
                    return swal('error', r.message);
                }

                delete state[bucket][x];
                tambah_ketagihan();
            },
            error: function() {
                return swal('error', 'Terjadi kesalahan')
            }
        });
    })

}

function voidFromTable(x, y = null) {
    const bucket = y == '_bb' ? 'data_bb' : 'data_um';
    const row = state[bucket] && state[bucket][x];
    const idKeuangan = row && row.id_keuangan;
    if (!idKeuangan) return;

    Swal.fire({
        title: 'Void Tagihan?',
        text: "Tagihan tidak akan dihapus, tapi tidak akan dihitung lagi di total.",
        input: 'textarea',
        inputPlaceholder: 'Alasan void...',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Void',
        confirmButtonClass: 'btn btn-warning',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1,
        inputValidator: function(value) {
            if (!value) return 'Alasan void wajib diisi';
        }
    }).then(function(t) {
        if (!t.value) return;

        $.ajax({
            url: base_url + 'tagihan/void',
            type: 'post',
            dataType: 'json',
            data: {
                id_keuangan: idKeuangan,
                reason: t.value,
                [csrfName]: csrfHash
            },
            success: function(r) {
                if (r.token) csrfHash = r.token;

                if (!r.success) {
                    return swal('error', r.message);
                }

                state[bucket][x].is_void = 1;
                state[bucket][x].void_reason = t.value;
                tambah_ketagihan();
            },
            error: function() {
                return swal('error', 'Terjadi kesalahan')
            }
        });
    })
}

function unvoidFromTable(x, y = null) {
    const bucket = y == '_bb' ? 'data_bb' : 'data_um';
    const row = state[bucket] && state[bucket][x];
    const idKeuangan = row && row.id_keuangan;
    if (!idKeuangan) return;

    Swal.fire({
        title: 'Un-void Tagihan?',
        text: "Tagihan akan aktif kembali & dihitung lagi di total.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, aktifkan',
        confirmButtonClass: 'btn btn-primary',
        cancelButtonClass: 'btn btn-danger ml-1',
        buttonsStyling: !1
    }).then(function(t) {
        if (!t.value) return;

        $.ajax({
            url: base_url + 'tagihan/unvoid',
            type: 'post',
            dataType: 'json',
            data: {
                id_keuangan: idKeuangan,
                [csrfName]: csrfHash
            },
            success: function(r) {
                if (r.token) csrfHash = r.token;

                if (!r.success) {
                    return swal('error', r.message);
                }

                state[bucket][x].is_void = 0;
                state[bucket][x].void_reason = null;
                tambah_ketagihan();
            },
            error: function() {
                return swal('error', 'Terjadi kesalahan')
            }
        });
    })
}

function editFromTable(x) {
    var d = state.data_um[x]

    $("#id_list_keu").val(x);
    $("#berita_acara").val(d.berita_acara);
    $("#nominal").val(d.nominal).keyup();
    $("#jatuh_tempo_tgl").val(d.jatuh_tempo_tgl);
    $("#tambah_list").html("Simpan Perubahan")
}


function rowHTML({
    title,
    date,
    amount,
    key,
    suffix = '',
    id_keuangan = null,
    is_void = 0,
    void_reason = ''
}) {
    if (is_void) {
        return `
    <tr data-key="${key}" data-suffix="${suffix}" class="text-muted">
      <td>${title} <span class="badge badge-secondary" title="${escapeAttribute(void_reason || '')}">Void</span></td>
      <td>${format_date(date)}</td>
      <td>${num_format(amount)}</td>
      <td>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-secondary waves-effect btn-sm js-unvoid" title="Un-void tagihan">
            <i class="fa fa-undo"></i>
          </button>
        </div>
      </td>
    </tr>`;
    }

    const voidBtn = id_keuangan ? `
      <button type="button" class="btn btn-outline-warning waves-effect btn-sm js-void" title="Void tagihan">
        <i class="fa fa-ban"></i>
      </button>` : '';
    return `
    <tr data-key="${key}" data-suffix="${suffix}">
      <td>${title}</td>
      <td>${format_date(date)}</td>
      <td>${num_format(amount)}</td>
      <td>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-danger waves-effect btn-sm js-remove" title="Hapus tagihan">
            <i class="fa fa-trash"></i>
          </button>${voidBtn}
        </div>
      </td>
    </tr>`;
}

function sectionHTML({
    rows,
    label,
    suffix = ''
}) {
    let total = 0;
    const body = rows.map(r => {
        if (!r.is_void) total += Number(removeComma(r.amount));
        return rowHTML({
            ...r,
            suffix
        });
    }).join('');
    const foot = `
                <tr class="table-secondary">
                    <td colspan="2">Total Tagihan </td>
                    <td>${num_format(total)}</td>
                    <td></td>
                </tr>`;
    return {
        html: body + foot,
        total
    };
}

function tambah_ketagihan() {
    const umRows = Object.keys(state.data_um || {}).map(k => ({
        key: k,
        title: state.data_um[k].berita_acara,
        date: state.data_um[k].jatuh_tempo_tgl,
        amount: state.data_um[k].nominal,
        id_keuangan: state.data_um[k].id_keuangan,
        is_void: state.data_um[k].is_void,
        void_reason: state.data_um[k].void_reason
    }));

    // const bbRows = Object.keys(state.data_bb || {}).map(k => ({
    //     key: k,
    //     title: state.data_bb[k].berita_acara_bb,
    //     date: state.data_bb[k].jatuh_tempo_tgl_bb,
    //     amount: state.data_bb[k].nominal_bb
    // }));

    const um = sectionHTML({
        rows: umRows,
        label: 'Tagihan Uang Muka',
        suffix: ''
    });

    // const bb = sectionHTML({
    //     rows: bbRows,
    //     label: 'Tagihan Biaya Biaya',
    //     suffix: '_bb'
    // });

    // 1x write ke DOM
    $("#list_cicilan_here").html(um.html);

    // update total & UI state
    $("#mk-total_cicilan_um").val(um.total).trigger('change');
    // $("#total_cicilan_bb").val(bb.total).trigger('change');
    $("#id_list_keu").val('');
    $("#id_list_keu_bb").val('');
    $("#nominal, #nominal_bb").trigger('change');
    // $("#tambah_list").text("+ Cicilan UM");
    // $("#tambah_list_bb").text("+ Cicilan BB");
}

// Event delegation untuk void
$(document).on('click', '#list_cicilan_here .js-void', function() {
    const $tr = $(this).closest('tr');
    const key = $tr.data('key');
    const suffix = $tr.data('suffix');
    voidFromTable(String(key), String(suffix || ''));
});

// Event delegation untuk un-void
$(document).on('click', '#list_cicilan_here .js-unvoid', function() {
    const $tr = $(this).closest('tr');
    const key = $tr.data('key');
    const suffix = $tr.data('suffix');
    unvoidFromTable(String(key), String(suffix || ''));
});

// Event delegation untuk remove
$(document).on('click', '#list_cicilan_here .js-remove', function() {
    const $tr = $(this).closest('tr');
    const key = $tr.data('key');
    const suffix = $tr.data('suffix');
    removeFromTable(String(key), String(suffix || ''));
});

function hitung_total(isForm = false, mkdt = []) {
    let totalum = 0,
        totalbb = 0,
        pengurangan = 0,
        hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0), //
        diskon_hj = parseFloat(removeComma($("#mk-diskon_harga_jual").val()) || 0),
        hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
        kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
        um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
        diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
        badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
        ppn = parseFloat(removeComma($("#mk-ppn").val()) || 0),
        bphtb = parseFloat(removeComma($("#mk-bphtb").val()) || 0),
        bproses = parseFloat(removeComma($("#mk-biaya_proses").val()) || 0),
        sbum = parseFloat(removeComma($("#mk-harga_sbum").val()) || 0),

        hj_real = 0,
        persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1, //persentase kpr
        penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
        penambahan_biaya_tanah = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()) || 0),
        is_allin = $("#idk-is_allin").val(),
        harga_allin = parseFloat(removeComma($("#mk-harga_allin").val() || 0))
    if (isForm) {
        if (mkdt.length == 0)
            return showToast('tidak ada data tersedia', 'warning')

        um = parseFloat(mkdt.harga_uang_muka || 0)
        diskon_um = parseFloat(mkdt.harga_diskon_uang_muka || 0)
        badm = parseFloat(mkdt.harga_administrasi || 0)
        ppn = parseFloat(mkdt.harga_ppn || 0)
        bphtb = parseFloat(mkdt.harga_bphtb || 0)
        bproses = parseFloat(mkdt.harga_biaya_proses || 0)
        sbum = parseFloat(mkdt.harga_sbum || 0)
        penambahan_biaya = parseFloat(mkdt.harga_penambahan || 0)
        penambahan_biaya_tanah = parseFloat(mkdt.harga_penambahan_tanah || 0)
        is_allin = parseFloat(mkdt.is_allin || 0)
        harga_allin = parseFloat(mkdt.harga_allin || 0)
    }

    pengurangan = diskon_um + sbum

    totalum = um + badm + penambahan_biaya + penambahan_biaya_tanah
    totalbb = ppn + bphtb + bproses

    let tottot = totalum + totalbb - pengurangan;

    let grandtotal = tottot;
    if (is_allin == "1")
        grandtotal = harga_allin

    return {
        'total_keseluruhan': tottot,
        'harus_dibayar': grandtotal
    }
}

function sum_mktotal() {
    let hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0)

    let tot = hitung_total()

    $("#mk-hargajual_net").val(hj_net).keyup()

    $("#mk-tgt").val(tot.total_keseluruhan).keyup(); //grand total keseluruhan
    $("#mk-total_tot").val(tot.harus_dibayar).keyup(); //total yang harus dibayar konsumen
}

async function isi_data_konsumen() {
  const requestId = ++latestIsiDataKonsumenRequestId;
  mkdtUpload();
  // VALIDASI PILIHAN
  if (!editdtt?.[0]) return swal("error", "Tidak ada kavling yang dipilih");
  const sh = editdtt[0];
  if (sh.data.tipe !== "kavling")
    return swal("error", "Tidak ada kavling terpilih");
  if (sh.data2.harga_akhir === "-")
    return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");

  disableForm(false);
  ui.btn.delKons.addClass("hidden");
  $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass("hidden");
  setIdkPerluRefund(0);

  ui.form.kons.find("#idk-id_konsumen").val("");
  // Siapkan konteks UI & state
  bindKavlingContext(sh);
  state.id_hargajual = sh.data2.id_hargajual;
  setVal("#idk-harga_akhir", state.id_hargajual);

  $("#idk-is_allin").change();

  try {
    await withLoading(async () => {
      const res = await getTransaksiDetail({
        id_mkdt: sh.data.id_mkdt,
        id_kavling: state.id_kavling,
        id_hargajual: state.id_hargajual,
      });
      if (requestId !== latestIsiDataKonsumenRequestId) return;

      // CSRF update
      csrfHash = res.token;

      const v = res.data; // mkdt
      const h = res.hj; // pricelist
      const tg = res.tagihan;
      const dk = res.diskresi;

      state.mkdt = {
        harga_jual: res.hj,
        diskresi: res.diskresi,
      };

      // Diskresi & HJ
      fillDiskresi(dk);
      fillPriceSection(h, dk);

      // File preview
      fillFiles(v);

      // MKDT fields
      fillMkdt(v);

      // SPPTB list
      fillSpptbList(res.list_spptb || []);

      // Tagihan + render
      fillTagihan(tg);
      $("#idk-total_sudah_dibayar").val(res.total_sudah_bayar || 0).keyup();

      // Hitung total & label alamat sekali saja
      sum_mktotal();

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      // Buka modal
      $("#modal-isi_data_konsumen").modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal-isi_data_konsumen");
      state.status.tab.isClosed = false;
    });
  } catch (e) {
    console.log(e);
    // Error path konsisten
    return swal("error", e?.statusText || e?.message || "Terjadi kesalahan");
  }
}
function appendCollectionToFormData(fd, collection) {
  if (!collection || typeof collection !== "object") return;
  let i = 0;

  // Izinkan collection berupa Array atau Object keyed
  const items = Array.isArray(collection)
    ? collection
    : Object.values(collection);

  for (const item of items) {
    if (!item || typeof item !== "object") {
      i++;
      continue;
    }
    for (const [key, val] of Object.entries(item)) {
      // Nullish -> string kosong supaya backend nggak terima "undefined"
      fd.append(`${key}[${i}]`, val ?? "");
    }
    i++;
  }
}

function renderNikUsageWarning(rows) {
  const items = (rows || [])
    .map((item) => {
      const kavling = [item.nama_jalan, item.no_kavling ? `No. ${item.no_kavling}` : ""]
        .filter(Boolean)
        .join(" ");
      const lokasi = [
        item.nama_proyek || "Proyek tidak diketahui",
        item.nama_cluster || "",
        kavling || "",
      ]
        .filter(Boolean)
        .join(" - ");

      return `<li class="mb-1">
        <strong>${escapeHtml(item.nama_konsumen || "-")}</strong><br>
        <small>${escapeHtml(lokasi)}</small>
      </li>`;
    })
    .join("");

  return `
    <div class="text-left">
      <p class="mb-1">NIK tersebut sudah digunakan pada kavling/blok/proyek lain:</p>
      <ul class="pl-2 mb-1">${items}</ul>
      <p class="mb-0">Tetap lanjutkan dan simpan konsumen ini?</p>
    </div>
  `;
}

function simpan_dt_konsumen_keuangan(allowDuplicateNik = false) {
  const btnSave = "#add-form-btn-idk_keu";
  // updateButtons(btnSave, "#prev-form-btn-idk_keu");

  if (parseFloat(removeComma($("#mk-total_cicilan_um").val() || 0)) > 0) {
    if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
      return swal(
        "error",
        "Gagal Menyimpan Data",
        "Total tagihan dan total yang harus dibayar tidak sesuai",
      );
    }
  }

  let dt = {};
  dt[csrfName] = csrfHash;
  ui.form.kons.find(":input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  //cicilan um

  let form = ui.form.kons[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);
  if (allowDuplicateNik) {
    fd.append("allow_duplicate_nik", "1");
  }
  let is_ganti_nama = false;

  if (is_ganti_nama) {
    fd.append("id_mkdt_old", id_mkdt_old);
    fd.append("id_konsumen_old", id_konsumen_old);
    fd.append("is_ganti_nama", is_ganti_nama);
  }

  appendCollectionToFormData(fd, state.data_um);

  $.ajax({
    url: base_url + "api/transaksi/simpan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btnSave, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          removeModalListener("#modal-isi_data_konsumen");
          $(".modal").modal("hide");
          simpanBtn(btnSave, false);

          load_kavling();
          hapus_seleksi();
        });
      } else if (r.require_nik_confirmation === true) {
        simpanBtn(btnSave, false);
        Swal.fire({
          icon: "warning",
          title: "NIK sudah digunakan",
          html: renderNikUsageWarning(r.nik_usage),
          showDenyButton: true,
          confirmButtonText: "Ya, tetap simpan",
          denyButtonText: "Batal",
          allowOutsideClick: false,
        }).then(function (result) {
          if (result.isConfirmed) {
            simpan_dt_konsumen_keuangan(true);
          }
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn(btnSave, false);
        });
      }
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        simpanBtn(btnSave, false);
      });
    },
  });
}

$("#status_mkdt").change(function () {
  if ($("#status_mkdt option:selected").val() == "Batal")
    $("#show_keterangan_batal").removeClass("hidden");
  else $("#show_keterangan_batal").addClass("hidden");
});

const mkdtKprState = {
  isLoading: false,
  isPromptOpen: false,
  previousAccValue: "0",
};

function mkdtAmount(selector) {
  return parseFloat(removeComma($(selector).val() || 0)) || 0;
}

function mkdtSetAmount(selector, value) {
  $(selector)
    .val(value || 0)
    .keyup();
}

function mkdtCalculateTurunKpr() {
  const hargaKpr = mkdtAmount("#fm-mkdt #harga_kpr");
  const accHargaKpr = mkdtAmount("#fm-mkdt #acc_harga_kpr");
  const turunKpr = accHargaKpr > 0 && accHargaKpr < hargaKpr ? hargaKpr - accHargaKpr : 0;

  mkdtSetAmount("#fm-mkdt #harga_turun_kpr", turunKpr);

  return { hargaKpr, accHargaKpr, turunKpr };
}

function mkdtRevertKprChange() {
  mkdtKprState.isLoading = true;
  mkdtSetAmount("#fm-mkdt #acc_harga_kpr", mkdtKprState.previousAccValue || 0);
  mkdtSetAmount("#fm-mkdt #harga_turun_kpr", 0);
  mkdtKprState.isLoading = false;
}

async function mkdtRequireTurunKprTagihan() {
  if (mkdtKprState.isLoading || mkdtKprState.isPromptOpen) return;
  if (!$("#modal_divisi4").hasClass("show")) return;

  const { turunKpr } = mkdtCalculateTurunKpr();
  if (turunKpr <= 0) {
    mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    return;
  }

  mkdtKprState.isPromptOpen = true;
  const { isConfirmed, isDismissed, value } = await loadFormTagihan(turunKpr);
  mkdtKprState.isPromptOpen = false;

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan Turun KPR ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    });
    return;
  }

  if (isDismissed) {
    mkdtRevertKprChange();
    Swal.fire(
      "Dibatalkan",
      "Nilai disetujui dikembalikan ke nilai awal dan Turun KPR direset 0.",
      "info",
    );
  }
}

$("#fm-mkdt #harga_kpr").change(function () {
  mkdtCalculateTurunKpr();
});

$("#fm-mkdt #acc_harga_kpr")
  .on("focusin", function () {
    if (!mkdtKprState.isLoading && !mkdtKprState.isPromptOpen) {
      mkdtKprState.previousAccValue = $(this).val() || "0";
    }
  })
  .on("change", function () {
    mkdtRequireTurunKprTagihan();
  });
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function () {
  if (!$("#wawancara").prop("checked")) {
    setDatePicker(null, "#wawancara_tgl");
  }
});

$("#refresh_fmmkdt_btn").click(function () {
  refresh_fmmkdt(false);
  $("#mkdt_data_baru").val(1);
});

function refresh_fmmkdt($st = true) {
  $("#fm-mkdt")[0].reset();
  $("#status_keterangan").prev(".richText-editor").trigger("setContent", "");
  $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop(
    "disabled",
    $st,
  );
  mkdtKprState.isLoading = false;
  mkdtKprState.isPromptOpen = false;
  mkdtKprState.previousAccValue = "0";
  $("#id_konsumen").val("");
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#id_keuangan0").val("");
}

function delete_kons() {
  $(
    "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen",
  ).val("");
  $("#id_konsumen, #id_mkdt").val("");
  $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
  if (sh.data.tipe != "kavling")
    return swal("error", "Tidak ada kavling terpilih", null, true);

  if (!sh.data.id_mkdt)
    return swal("error", "Belum ada data konsumen", null, true);

  if (sh.data2.harga_akhir == "-") {
    return swal(
      "error",
      "Kavling belum dipasarkan",
      "Kavling belum memiliki harga jual",
    );
  }
  $("#lb-st-no_spptb").html("-");
  $("#lb-st-nama_konsumen").html("-");

  // $("#label-file_ktp").html("Upload file KTP");
  // $("#label-file_npwp").html("Upload file KTP");

  // $("#refresh_fmmkdt_div").addClass("hidden");
  // $("#delete_kons_div").addClass("hidden");
  // $("#fm-mkdt .num").prop("disabled", false);

  // $("#cicilan_belong_here").html("");
  it = 0;
  // $("#data_konsumen").tab('show');

  $("#mkdt_data_baru").val(0);

  refresh_fmmkdt(false);

  $("#fm-mkdt .num").val(0);

  $(".id_kavling").val(id_kavling);
  $("#id_mkdt").val(sh.data.id_mkdt);

  $.ajax({
    url: base_url + "api/transaksi/status/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
      id_hargajual: sh.data2.id_hargajual,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (x) {
      $("#loading").addClass("hidden");
      csrfHash = x.token;
      let r = x.data, //data mkdt
        pb = x.perintah_bangun,
        tkpr = x.tagihan;
      mkdtKprState.isLoading = true;

      //load hargajual
      // if (h.hargajual) {
      //   $.each(h, function (k, v) {
      //     $("#mkdt-" + k)
      //       .val(v)
      //       .change()
      //       .keyup();
      //   });
      //   $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
      //   $("#fm-mkdt #harga_kpr").val(h.kpr).change();
      // }

      //load num
      // if (!r.id_mkdt) {
      //     $(".num").val(0).keyup();
      //     $("#fm-mkdt #harga_jual").val(sh.data2.harga_akhir);
      // }

      //set harga jual dari data kavling
      if (r) {
        if (r.status_mkdt == "Batal") {
          refresh_fmmkdt(true);
          $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass(
            "hidden",
          );
          $("#delete_kons_div").addClass("hidden");
          $("#delete-btn-idk_keu").addClass("hidden");
        }

        //autoload field ke input
        for (let i in r) {
          if (
            i != "perintah_bangun" &&
            i != "wawancara" &&
            i != "akad" &&
            i != "akad_indent" &&
            i != "sp3k" &&
            i != "bast_file" &&
            i != "sp3k_file" &&
            i != "perintah_bangun_file" &&
            i != "id_bank"
          )
            $("#fm-mkdt #" + i).val(r[i]);
        }

        $("#lb-st-no_spptb").html(r.no_spptb);
        $("#lb-st-nama_konsumen").html(r.nama_konsumen);

        $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
        $("#status_keterangan").prev(".richText-editor").trigger("setContent", r.keterangan_status ?? "");
        $("#status_keterangan").html(r.keterangan_status ?? "");
        $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
        $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

        var newOption = new Option(r.nama_bank, r.id_bank, true, true);
        $("#id_bank").append(newOption).trigger("change");

        if (r.wawancara == 1) $("#wawancara").prop("checked", true);
        if (r.sp3k == 1) $("#sp3k").prop("checked", true);
        if (r.akad == 1) $("#akad").prop("checked", true);
        if (r.akad_indent == 1) $("#akad_indent").prop("checked", true);

        //set datepicker jika tanggal valid
        setDatePicker(pb.perintah_bangun_tgl, "#fm-mkdt #perintah_bangun_tgl");

        setDatePicker(r.booking_tgl, "#fm-mkdt #booking_tgl");
        setDatePicker(r.wawancara_tgl, "#fm-mkdt #wawancara_tgl");
        setDatePicker(r.sp3k_tgl, "#fm-mkdt #sp3k_tgl");
        setDatePicker(r.sp3k_tgl_exp, "#fm-mkdt #sp3k_tgl_exp");
        setDatePicker(r.rencana_akad_tgl, "#fm-mkdt #rencana_akad_tgl");
        setDatePicker(r.akad_tgl, "#fm-mkdt #akad_tgl");

        // if (r.refund_tgl != "0000-00-00")
        //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

        $("#fm-mkdt .num").keyup().change(); //fomrat form number
        $("#status_mkdt").change(); //show/hide keterangan batal

        $("#mkdt_keterangan").val(r.keterangan);

        // $("#file_ktp-here").html("Tidak ada data");
        // src = not_found;

        setBtnHref("#list-upload_sp3k_file", r.sp3k_access_url);
      }

      if (pb.perintah_bangun == 1) {
        $("#perintah_bangun").prop("checked", true);
        $("#fm-mkdt #perintah_bangun_oleh").val(pb.username);
        setBtnHref(
          "#list-upload_perintah_bangun_file",
          pb.perintah_bangun_access_url,
        );
        setDatePicker(pb.perintah_bangun_tgl, "#perintah_bangun_tgl");
      }

      load_tagihankpr(tkpr);
      mkdtKprState.isLoading = false;
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      $("#mkdt-tab-form-link").tab("show");
      resetMkdtHistoryTimeline();
      $("#mkdt-history-timeline").data("id-kavling", id_kavling);

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal_divisi" + role);
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}
async function hapus_turunkpr(id_keuangan) {
  const { isConfirmed } = await Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data keuangan ini akan dihapus permanen!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });

  if (isConfirmed) {
    Swal.fire({
      title: "Menghapus...",
      text: "Tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      let response = await fetch(`${base_url}tagihan/hapusturunkpr`, {
        method: "POST", // atau 'DELETE' kalau API pakai method delete
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id_keuangan: id_keuangan }),
      });

      let result = await response.json();

      if (response.ok && result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: result.message,
        }).then(() => {
          // refresh table atau halaman
          load_tagihankpr(null);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: result.message || "Terjadi kesalahan saat menghapus",
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
      });
    }
  }
}
function load_tagihankpr(val) {
  $("#mkdt-tagihan_kpr").html("");

  // console.log(val);

  if (val == null) {
    return;
  }

  let div = `
  <div class="divider divider-left">
      <div class="divider-text font-weight-bold">Tagihan Turun KPR</div>
  </div>
  `;
  if (val.berita_acara == "Turun KPR") {
    div += `
      <div class="form-group">
          <label for="bank">Tanggal Jatuh Tempo Turun KPR</label>
          <input type="text" readonly class="form-control" value='${format_date(
            val.jatuh_tempo_tgl,
          )}' />
              <a href="#" onclick="hapus_turunkpr(${
                val.id_keuangan
              })"class="text-danger"><i class="fa fa-trash"></i>Klik untuk hapus tagihan</a>
      </div>
      `;
  }

  $("#mkdt-tagihan_kpr").html(div);
}

$("#add-form-btn-mkdt").click(function (e) {
  e.preventDefault();
});

function save_mkdt(e) {
  const btn = "#add-form-btn-mkdt";
  if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
  if (!palid("fm-mkdt #id_bank", "", "Bank harus diisi")) return;

  if (
    removeComma($("#harga_turun_kpr").val()) > 0 &&
    $("#mkdt-tagihan_kpr").html() == ""
  ) {
    swal(
      "warning",
      "Tagihan untuk turun KPR harus dibuat terlebih dahulu",
      "Karena ada nilai di turun KPR, jadi harus buat tagihannya dulu ya!",
      false,
      hlButton("#btn-add-tagihan-turunkpr"),
    );
    return;
  }

  // if ($("#total_cicilan").val() != $("#total_biaya2").val()) {
  //     Swal.fire({
  //         //position: 'bottom-end',
  //         icon: 'error',
  //         title: "Total Cicilan tidak sesuai dengan total biaya",
  //         showConfirmButton: false,
  //         timer: 1500
  //     });
  //     return false;
  // }

  // var files = $('#file_ktp')[0].files;
  var form = $("#fm-mkdt")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/transaksi/status/simpan",
    type: "post",
    // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btn, true);
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        removeModalListener("#modal_divisi4");
        load_kavling();
        hapus_seleksi();

        swal("success", r.messages);
        $(".modal").modal("hide");
      } else {
        swal("error", r.messages);
      }
      simpanBtn(btn, false);
    },
    error: function (xhr, st, err) {
      simpanBtn(btn, false);
      return swal("error", err);
    },
  });
}

function set_harga() {
  $.ajax({
    url: base_url + "Hargajual/set_harga",
    type: "post",
    data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#set-harga-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#set-harga-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      swal(
        response.success ? "success" : "error",
        response.messages,
        null,
        true,
        () => {
          if (response.success) {
            $("#modals-set_harga").modal("hide");
          }
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        },
      );
      load_kavling();
      hapus_seleksi();
    },
  });
}

function formatDesign(item) {
  var selectionText = item.text.split(";");
  var $returnString = $(
    "<span> <b>" +
      selectionText[0] +
      "</b></br >" +
      selectionText[1] +
      "</br>" +
      selectionText[2] +
      "</span>",
  );
  return $returnString;
}
$("#sh-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  templateResult: formatDesign,
  ajax: {
    url: base_url + "hargajual/get",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text: `Rp. ${num_format(v.hargajual)} Per ${format_date(
            v.tgl_harga,
          )} (ROW ${v.row}); <b>Tipe:</b> ${v.id_tipe}; <b>Ket:</b> ${
            v.keterangan
          };`,
          row: v.row,
          tipe: v.id_tipe,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          hargajual_net: v.hargajual_net,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          ppn: v.ppn,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
          lok: file_url('file_hargajual', v.id_filehj),
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
// on select cluster
$("#sh-id").on("select2:selecting", function (e) {
  var i = e.params.args.data;
  $.each(i, function (k, v) {
    changeVal("#sh-" + k, v);
  });

  let src = i.lok != "null/null" ? i.lok : not_found;
  setFileHref("#sh-pricelist_file", false, src);
});

$("#sh-id").change(function () {
  if (!this.value) $(".sh-fm").val("");
});

function open_set_turun_pembangunan() {
  $("#list-tp-upload_perintah_bangun_file").prop("href", resolveFileHref(not_found));
  $("#label-perintah_bangun_file").html("Upload File Perintah Bangun");
  if (editdtt.length == 0) {
    return swal("error", "Tidak ada kavling terpilih");
  }
  $("#fm-turun_pembangunan")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }
  $.ajax({
    url: base_url + "siteplan/get_turun_pembangunan",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        no = "";

      $(".select2").not("#pilih-divisi").val(null).trigger("change");

      if (r.length > 0) {
        r.forEach((v) => {
          id_kavling += v.id_kavling + ";";
          no += `${v.nama_jalan} No. ${v.no_kavling} \n`;
        });

        $(".id_kavling").val(id_kavling);
        $("#tp-kavling").val(no);

        $("#tp-perintah_bangun_oleh").val(r[0].username);

        $("#list-tp-upload_perintah_bangun_file").prop(
          "href",
          resolveFileHref(r[0].perintah_bangun_access_url, not_found),
        );

        setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl");
      }

      $("#loading").addClass("hidden");
      $("#modals-turun_pembangunan").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function set_tp() {
  if ($("#tp-perintah_bangun_tgl").val() == "") {
    return Swal.fire({
      icon: "error",
      title: "Tanggal Perintah Bangun harus diisi",
      showConfirmButton: false,
    });
  }
  let form = $("#fm-turun_pembangunan")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "siteplan/set_turun_pembangunan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#set-tp-btn").html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan');
      $("#set-tp-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-turun_pembangunan").modal("hide");
          $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
          $("#set-tp-btn").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
          $("#set-tp-btn").removeClass("disabled");
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (err) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan saat menginput data",
        showConfirmButton: false,
      }).then(function () {
        $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
        $("#set-tp-btn").removeClass("disabled");
      });
    },
  });
}

function setFileHref(id, remove = true, url = null) {
  if (remove) {
    $(id).removeAttr("target");
    $(id).prop("href", "javascript:void(0)");
  } else {
    $(id).prop("href", resolveFileHref(url));
    $(id).prop("target", "_blank");
  }
}

function open_set_harga() {
  if (editdtt.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  setFileHref("#sh-pricelist_file");

  $("#fm-set_harga")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }

  $.ajax({
    url: base_url + "siteplan/get_harga_kavling",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      $("#loading").addClass("hidden");
      let r = res.data,
        id_kavling = "",
        src,
        no = "";

      $("#sh-id").val(null).trigger("change");

      if (r.length > 0) {
        for (let a = 0; a < r.length; a++) {
          id_kavling += r[a].id_kavling + ";";
          no += `${r[a].nama_jalan} No. ${r[a].no_kavling} - ${r[a].tipe_rumah}\n`;

          if (r[a].harga_akhir) {
            $("#sh-id")
              .append(
                $("<option selected></option>")
                  .attr("value", r[a].harga_akhir)
                  .text(
                    `Rp. ${num_format(r[a].hargajual)} (${
                      r[a].tipe_rumah
                    }) ROW ${r[a].row}: per ${format_date(r[a].tgl_harga)}`,
                  ),
              )
              .trigger("change");

            changeVal("#sh-row", r[a].row);
            changeVal("#sh-tipe", r[a].tipe_rumah);
            changeVal("#sh-lb", r[a].hj_lb);
            changeVal("#sh-lt", r[a].hj_lt);
            changeVal("#sh-hargajual", r[a].hargajual);
            changeVal("#sh-hargajual_net", r[a].hargajual_net);
            changeVal("#sh-kpr", r[a].kpr);
            changeVal("#sh-uang_muka", r[a].uang_muka);
            changeVal("#sh-ppn", r[a].ppn);
            changeVal("#sh-bphtb", r[a].bphtb);
            changeVal("#sh-biaya_adm", r[a].biaya_adm);
            changeVal("#sh-biaya_proses", r[a].biaya_proses);

            src = not_found;
            if (r[a].file_name) {
              src = file_url('file_hargajual', r[a].id_filehj);
            }
            setFileHref("#sh-pricelist_file", false, src);
          } else {
            setFileHref("#sh-pricelist_file");
          }
        }

        $(".id_kavling").val(id_kavling);
        $("#sh-kavling").val(no);
        // $("#fm-set_harga #id_tipe").val(id_tipe);
        // $("#fm-set_harga #harga").val(harga).keyup();
      }

      $("#modals-set_harga").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}

let siDataCache = [];

function getSiRecordKey(item) {
  return item && item.id ? item.id : "n" + item.id_list_si_ori;
}

function isSiSaved(item) {
  return !!(
    item &&
    (item.id || item.tanggal_si || item.keterangan || item.file || item.access_url)
  );
}

function getSelectedSiItem() {
  const selectedId = $("#si-id_list_si").val();
  return siDataCache.find((item) => String(item.id_list_si_ori) === String(selectedId));
}

function initSiDatePicker(value = "") {
  const input = document.querySelector("#si-tanggal_si");
  if (!input) return;

  if (input._flatpickr) {
    input._flatpickr.setDate(value || null, false);
    return;
  }

  flatpickr(input, {
    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
    defaultDate: value || null,
  });
}

function setSiDynamicFields(item) {
  const key = item ? getSiRecordKey(item) : "";
  $("#si-current-key").val(key);
  if (key) {
    $("#si-tanggal_si").attr("name", `id-si[${key}][tanggal_si]`);
    $("#si-keterangan").attr("name", `id-si[${key}][keterangan]`);
    $("#si-file").attr("name", `id-si-file-${key}`);
  } else {
    $("#si-tanggal_si, #si-keterangan, #si-file").removeAttr("name");
  }
}

function resetSiInput() {
  $("#si-id_list_si").val("");
  $("#si-keterangan").val("");
  $("#si-file").val("");
  $("#si-file-label").text("Upload Soft File");
  $("#si-current-file").addClass("hidden").attr("href", "#");
  $("#si-selected-label").text("-");
  $("#si-selected-date").text("-");
  setSiDynamicFields(null);
  initSiDatePicker("");
}

function fillSiInput(item) {
  if (!item) {
    resetSiInput();
    return;
  }

  setSiDynamicFields(item);
  $("#si-id_list_si").val(item.id_list_si_ori);
  $("#si-keterangan").val(item.keterangan || "");
  $("#si-file").val("");
  $("#si-file-label").text("Upload Soft File");
  initSiDatePicker(item.tanggal_si || "");
  $("#si-selected-label").text(item.nama || "-");
  $("#si-selected-date").text(item.tanggal_si ? format_date(item.tanggal_si) : "-");

  if (item.access_url) {
    $("#si-current-file")
      .removeClass("hidden")
      .attr("href", item.access_url);
  } else {
    $("#si-current-file").addClass("hidden").attr("href", "#");
  }
}

function renderSiOptions(data) {
  let options = '<option value="">Pilih Standing Instruction</option>';
  $.each(data, function (_, item) {
    const savedLabel = isSiSaved(item) ? " (sudah tersimpan)" : "";
    options += `<option value="${escapeAttribute(item.id_list_si_ori)}">${escapeHtml(item.nama || "-")}${savedLabel}</option>`;
  });
  $("#si-id_list_si").html(options);
}

function renderSiTable(data) {
  const rows = data.filter(isSiSaved);
  const tbody = $("#si-table tbody");
  tbody.empty();

  if (!rows.length) {
    tbody.html("<tr><td colspan='5' class='text-center text-muted'>Data tidak ditemukan</td></tr>");
    return;
  }

  $.each(rows, function (_, item) {
    const fileButton = item.access_url
      ? `<a href="${escapeAttribute(item.access_url)}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye mr-1"></i> Lihat</a>`
      : '<span class="text-muted">-</span>';
    const row = `
      <tr>
        <td>${escapeHtml(item.nama || "-")}</td>
        <td>${item.tanggal_si ? format_date(item.tanggal_si) : "-"}</td>
        <td>${fileButton}</td>
        <td>${escapeHtml(item.keterangan || "-")}</td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-primary si-edit-btn" data-id="${escapeAttribute(item.id_list_si_ori)}">
            <i class="fas fa-edit mr-1"></i> Pilih
          </button>
        </td>
      </tr>`;
    tbody.append(row);
  });
}

function load_si_data(id_kavling, keepSelection = false) {
  const selectedId = keepSelection ? $("#si-id_list_si").val() : "";

  $.ajax({
    url: base_url + "mkdt/getsi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      siDataCache = res.data || [];
      renderSiOptions(siDataCache);
      renderSiTable(siDataCache);
      if (selectedId) {
        $("#si-id_list_si").val(selectedId);
      }

      const selected = keepSelection ? getSelectedSiItem() : null;
      if (selected) {
        fillSiInput(selected);
      } else {
        resetSiInput();
      }

      $("#modals-si").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function isi_si() {
  let sh = editdtt;

  if (sh.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  sh = sh[0];

  let id_kavling = sh.id.substr(3);
  let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;
  let label_alamat = setLabelAlamat(
    nama_proyek,
    sh.data.nama_jalan,
    sh.data.no_kavling,
    sh.data2.no_tipe_rumah,
    sh.data2.tipe_rumah,
  );

  $(".id_kavling").val(id_kavling);
  $("#modals-si .label_alamat").html(label_alamat);
  $("#fm-si")[0].reset();
  $("#si-table tbody").html("<tr><td colspan='5' class='text-center text-muted'>Memuat data...</td></tr>");
  load_si_data(id_kavling);
}

$("#si-id_list_si").on("change", function () {
  fillSiInput(getSelectedSiItem());
});

$("#si-file").on("change", function () {
  const fileName = this.files && this.files[0] ? this.files[0].name : "Upload Soft File";
  $("#si-file-label").text(fileName);
});

$(document).on("click", ".si-edit-btn", function () {
  const itemId = $(this).data("id");
  $("#si-id_list_si").val(itemId);
  fillSiInput(getSelectedSiItem());
});

function save_si() {
  const selected = getSelectedSiItem();

  if (!selected) {
    return swal("error", "Pilih Standing Instruction terlebih dahulu");
  }

  if ($("#si-tanggal_si").val() == "") {
    return swal("error", "Tanggal SI harus diisi");
  }

  if (!selected.access_url && !selected.file && $("#si-file").val() == "") {
    return swal("error", "Soft file Standing Instruction harus diupload");
  }

  var form = $("#fm-si")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  let sbtn = "#btn-si-simpan";

  $.ajax({
    url: base_url + "mkdt/saveSI",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(sbtn, true);
    },
    success: function (r) {
      csrfHash = r.token;
      simpanBtn(sbtn, false);
      if (r.success === true) {
        swal("success", r.messages);
        load_si_data($("#fm-si .id_kavling").val(), true);
        load_kavling();
      } else {
        swal("error", "Terjadi kesalahan", r.messages);
      }
    },
    error: function (r) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
      simpanBtn(sbtn, false);
    },
  });
}

$("#fm-mkdt #sp3k_tgl").change(function () {
  if (!this.value) return;
  document
    .querySelector("#fm-mkdt #sp3k_tgl_exp")
    ._flatpickr.setDate(new Date(this.value).fp_incr(88));
});

//untuk tambah konsumen baru ketika batal
$("#refresh-btn-idk_keu").click(function () {
  $("#fm-idk_keu .num").prop("disabled", false);
  $("#fm-idk_keu")[0].reset();

  // refresh_fmmkdt(false);
  $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop(
    "disabled",
    false,
  );
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#idk_data_baru").val(1);
  $("#idk-show_keterangan_batal").addClass("hidden");
  setIdkPerluRefund(0);

  if (state.mkdt.harga_jual && state.mkdt.diskresi) {
    fillPriceSection(state.mkdt.harga_jual, state.mkdt.diskresi);
  }
  state.data_um = {};
  tambah_ketagihan();
  sum_mktotal();
});

function mkdtUpload() {
  const inputs = [
    { id: "file_ktp" },
    { id: "file_npwp" },
    { id: "file_data_diri" },
    { id: "perintah_bangun_file" },
    { id: "sp3k_file" },
    { id: "bast_file" },
  ];

  inputs.forEach((item) => {
    const input = document.getElementById(item.id);
    if (!input || input.dataset.dropzoneLoaded === "1") return;

    load_dropzone(item.id);
    input.dataset.dropzoneLoaded = "1";
  });

  bindMkdtClipboardUpload();
  bindKtpOcr();
}

function postTurunKPR(val) {
  // console.log(val)
  const { berita_acara, nominal, tgl, id_mkdt } = val;
  $.ajax({
    url: base_url + "tagihan/turunkpr",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, berita_acara, nominal, tgl, id_mkdt },
    beforeSend: () => {},
    success: () => {},
    error: () => {},
  });
}
async function loadFormTagihan(nominal_kpr) {
  const { isConfirmed, isDismissed, value } = await Swal.fire({
    title: "Tambah Ke Tagihan",
    html: `
      <div class="swal2-content mt-1" style="text-align:left">
        <div class="alert alert-warning py-1 px-2 mb-1" style="font-size:.82rem; line-height:1.4">
          Turun KPR wajib dibuat tagihan sebelum perubahan nilai disetujui bisa disimpan.
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="Turun KPR" readonly id="fkpr-berita_acara" placeholder=" " required>
          <label for="fkpr-berita_acara">Untuk Tagihan</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="${nominal_kpr}" readonly id="fkpr-nominal" placeholder=" " required>
          <label for="fkpr-nominal">Nominal</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control fp-jatuhtempo" id="fkpr-jatuh_tempo_tgl" placeholder=" " required>
          <label for="fkpr-jatuh_tempo_tgl">Jatuh Tempo</label>
        </div>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: "Simpan Tagihan",
    cancelButtonText: "Batal",
    allowOutsideClick: false,
    allowEscapeKey: false,
    showLoaderOnConfirm: true,

    didOpen: () => {
      // const popup = Swal.getPopup();
      const el = document.querySelector(".fp-jatuhtempo");
      if (el && el._flatpickr) el._flatpickr.destroy();
      flatpickr(el, {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
        allowInput: true,
        // appendTo: popup, // penting biar z-index aman
      });
    },

    preConfirm: async () => {
      const p = Swal.getPopup();
      const berita_acara = p.querySelector("#fkpr-berita_acara").value.trim();
      const nominalStr = p.querySelector("#fkpr-nominal").value.trim();
      const tgl = p.querySelector("#fkpr-jatuh_tempo_tgl").value.trim();
      const id_mkdt = document.querySelector("#id_mkdt").value.trim();
      const id_kavling = document.querySelector(".id_kavling").value.trim();
      const id_konsumen = document.querySelector("#id_konsumen").value.trim();
      const harga_kpr = document.querySelector("#harga_kpr").value.trim();
      const acc_harga_kpr = document
        .querySelector("#acc_harga_kpr")
        .value.trim();

      const nominal = Number(nominalStr.replace(/[^\d.-]/g, "")) || 0;

      if (!berita_acara)
        return Swal.showValidationMessage("Untuk Tagihan wajib diisi");
      if (nominal <= 0)
        return Swal.showValidationMessage("Nominal tidak boleh 0");
      if (!tgl)
        return Swal.showValidationMessage(
          "Tanggal jatuh tempo tidak boleh kosong",
        );

      // ---- POST ke server ----
      try {
        // (opsional) Abort kalau kelamaan
        const ac = new AbortController();
        const timeout = setTimeout(() => ac.abort(), 20000); // 20 detik

        const res = await fetch(`${base_url}tagihan/turunkpr`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            // sertakan CSRF kalau CI4 mengaktifkan:
            // "X-CSRF-TOKEN": window.CSRF_TOKEN
          },
          body: JSON.stringify({
            berita_acara,
            nominal,
            jatuh_tempo: tgl,
            id_mkdt,
            id_kavling,
            id_konsumen,
            harga_kpr,
            acc_harga_kpr,
          }),
          signal: ac.signal,
        });
        clearTimeout(timeout);

        // Tangani error HTTP
        if (!res.ok) {
          const text = await res.text().catch(() => "");
          throw new Error(text || `Gagal menyimpan (HTTP ${res.status})`);
        }

        const data = await res.json().catch(() => ({}));
        // Jika API kamu kirim {success:false, message:"..."}
        if (data && data.success === false) {
          throw new Error(data.messages || "Gagal menyimpan");
        }
        // Return untuk diteruskan ke .then(...) sebagai `value`
        return data;
      } catch (err) {
        // Tetap di popup + tampilkan pesan error di bawah tombol
        return Swal.showValidationMessage(err.message || "Gagal menyimpan");
      }
    },
  });

  return { isConfirmed, isDismissed, value };
}
let btnTunruKpr = "#btn-add-tagihan-turunkpr";
$(btnTunruKpr).click(async function (e) {
  e.preventDefault();
  let nominal_kpr = removeComma($("#harga_turun_kpr").val());

  if (nominal_kpr == 0) {
    return swal(
      "error",
      "Terjadi Kesalahan",
      "Tidak bisa menambahkan ke tagihan jika nominal Turun KPR 0!",
    );
  }
  const { isConfirmed, isDismissed, value } = await loadFormTagihan(nominal_kpr);

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    });
  } else if (isDismissed) {
    Swal.fire("Dibatalkan", "Aksi dibatalkan.", "info");
  }
});
