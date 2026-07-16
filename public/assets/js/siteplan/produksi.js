	function updateState(elementId, variableName) {
  $(elementId).change(function () {
    window[variableName] = this.checked ? 1 : 0;
  });
}
/******************************** produksi ******************************************/
//cekbok produksi
var slo = 0,
  bp = 0,
  lpa = 0,
  tot = 0,
  st_0 = 0,
  st_25 = 0,
  st_50 = 0,
  st_75 = 0,
  st_100 = 0,
  st_saluran = 0,
  st_jalan = 0,
  st_air = 0;

updateState("#bp", "bp");
updateState("#slo", "slo");
updateState("#lpa", "lpa");
updateState("#st_0", "st_0");
updateState("#st_25", "st_25");
updateState("#st_50", "st_50");
updateState("#st_75", "st_75");
updateState("#st_100", "st_100");
updateState("#st_saluran", "st_saluran");
updateState("#st_jalan", "st_jalan"); // Field legacy: status listrik.
updateState("#st_air", "st_air");

function isProduksiManualSelectionActive() {
  return $("#produksi_tambah_jalan").prop("checked");
}

function setProduksiJalanSelectionMode(active, clearSelection) {
  $("#produksi_tambah_jalan").prop("checked", active);
  $("#produksi_menu").toggleClass("produksi-jalan-selecting", active);
  $("#produksi_menu > button, #produksi_menu > .btn-icon")
    .not("#produksi_add_jalan_ok, #produksi_add_jalan_undo, #produksi_add_jalan_batal")
    .toggleClass("d-none", active);
  $("#produksi_add_jalan_ok, #produksi_add_jalan_undo, #produksi_add_jalan_batal").toggleClass(
    "d-none",
    !active,
  );
  $("#produksi_add_jalan_hint").toggleClass("d-none", !active);

  if (clearSelection) {
    hapus_seleksi();
  }
}

function start_tambah_jalan_produksi() {
  setProduksiJalanSelectionMode(true, true);
}

function cancel_tambah_jalan_produksi() {
  $("#modal_produksi_add_jalan").modal("hide");
  setProduksiJalanSelectionMode(false, true);
}

function tambah_jalan_produksi() {
  if (!isProduksiManualSelectionActive()) {
    setProduksiJalanSelectionMode(true, false);
  }

  if (!dtt || dtt.length < 6) {
    return swal("error", "Seleksi manual minimal 3 titik");
  }

  $("#fm-produksi-add-jalan")[0].reset();
  $("#prod_jalan_id_cluster, #prod_jalan_id_jalan").val(null).trigger("change");
  $("#prod_jalan_id_jalan").prop("disabled", true);
  $("#prod_jalan_points").val(dtt.join(","));
  $(".prod_jalan_r_progres").html("0");
  setProduksiJalanSelectionMode(false, false);

  $("#modal_produksi_add_jalan").modal({
    backdrop: "static",
    keyboard: false,
  });
}

function save_jalan_produksi() {
  let points = $("#prod_jalan_points").val().split(",").filter(function (point) {
    return point !== "";
  });

  if (!$("#prod_jalan_id_jalan").val()) {
    return swal("error", "Blok/Jalan harus diisi");
  }

  if (points.length < 6) {
    return swal("error", "Seleksi manual minimal 3 titik");
  }

  $.ajax({
    url: base_url + "api/produksi/add_jalan",
    type: "POST",
    data: $("#fm-produksi-add-jalan").serialize() + "&" + csrfName + "=" + csrfHash,
    dataType: "json",
    beforeSend: function () {
      $("#save_produksi_add_jalan-btn").prop("disabled", true);
      $("#save_produksi_add_jalan-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modal_produksi_add_jalan").modal("hide");
          load_kavling();
          setProduksiJalanSelectionMode(false, true);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
        });
      }

      $("#save_produksi_add_jalan-btn").html("Simpan");
      $("#save_produksi_add_jalan-btn").prop("disabled", false);
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Terjadi kesalahan saat menambahkan jalan",
        showConfirmButton: false,
      });
      $("#save_produksi_add_jalan-btn").html("Simpan");
      $("#save_produksi_add_jalan-btn").prop("disabled", false);
    },
  });
}

$("#prod_jalan_id_cluster").select2({
  placeholder: "Pilih Cluster",
  allowClear: true,
  dropdownParent: $("#modal_produksi_add_jalan"),
  ajax: {
    url: base_url + "/cluster/getAll",
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
      $.each(r.data, function (index, item) {
        results.push({
          id: item[0],
          text: item[3],
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

$("#prod_jalan_id_cluster").on("change", function () {
  $("#prod_jalan_id_jalan").val(null).trigger("change");
  $("#prod_jalan_id_jalan").prop("disabled", !this.value);
});

$("#prod_jalan_id_jalan").select2({
  placeholder: "Pilih Blok",
  allowClear: true,
  dropdownParent: $("#modal_produksi_add_jalan"),
  ajax: {
    url: base_url + "/jalan/getAll",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_cluster: $("#prod_jalan_id_cluster").val(),
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (index, item) {
        results.push({
          id: item[0],
          text: item[3],
        });
      });

      return {
        results: results,
      };
    },
    cache: true,
  },
});


$("#listrik_jenis").change(function () {
  if (this.value == "PLN") {
    $("#listrik-pln-input-form").removeClass("hidden");
    $("#listrik_disediakan").addClass("hidden");
  } else {
    $("#listrik-pln-input-form").addClass("hidden");
    $("#listrik_disediakan").removeClass("hidden");
  }
});
$("#air_jenis").change(function () {
  if (this.value == "Air Tanah") {
    $("#air_tanah-input_form").removeClass("hidden");
    $("#air_komunal-input_form").addClass("hidden");
    $("#air_pdam-input_form").addClass("hidden");
  } else if (this.value == "Komunal Warga") {
    $("#air_tanah-input_form").addClass("hidden");
    $("#air_komunal-input_form").removeClass("hidden");
    $("#air_pdam-input_form").addClass("hidden");
  } else {
    $("#air_tanah-input_form").addClass("hidden");
    $("#air_komunal-input_form").addClass("hidden");
    $("#air_pdam-input_form").removeClass("hidden");
  }
});

$("#progres_bangunan").on("input", function () {
  $("#t_progres_bangunan").html($(this).val());
});

function focusProduksiProgressForm() {
  const $progressTab = $("#fm-prod-progress-tab");
  if (typeof $progressTab.tab === "function") {
    $progressTab.tab("show");
  } else {
    $progressTab.click();
  }

  const $target = $("#produksi-progress-edit-area");
  const $body = $("#modal_divisi7 .modal-body");

  if ($target.length && $body.length) {
    const targetTop = $target.position().top + $body.scrollTop() - 12;
    $body.animate({ scrollTop: targetTop }, 220);
    $target.addClass("produksi-edit-highlight");
    window.setTimeout(function () {
      $target.removeClass("produksi-edit-highlight");
    }, 1600);
  }

  const $firstInput = $("#progres_bangunan");
  if ($firstInput.length) $firstInput.trigger("focus");
}

function save_produksi() {
  if ($("#tanggal_pembangunan").val() == "") {
    $(".tanggal_pembangunan").addClass("is-invalid");
    return swal("error", "Tanggal pembangunan harus diisi");
  }
  $(".tanggal_pembangunan").removeClass("is-invalid");

  if ($("#tanggal_rencana_selesai_pembangunan").val() == "") {
    $(".tanggal_rencana_selesai_pembangunan").addClass("is-invalid");
    return swal("error", "Tanggal rencana selesai pembangunan harus diisi");
  }
  $(".tanggal_rencana_selesai_pembangunan").removeClass("is-invalid");

  let form = $("#fm-produksi")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/save",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#add-form-btn-produksi", true);
    },
    success: function (r) {
      csrfHash = r.token;
      // $('#add-form-btn-produksi').prop('disabled', false);
      // return;
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $(".modal").modal("hide");
          simpanBtn("#add-form-btn-produksi", false);
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn("#add-form-btn-produksi", false);
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (xhr, st, err) {
      simpanBtn("#add-form-btn-produksi", false);
      return swal("error", err);
    },
  });
}

function resetProduksiHistoryTimeline() {
  $("#produksi-history-timeline")
    .data("id-kavling", "")
    .data("next-offset", 0)
    .data("history-limit", 10)
    .html('<div class="text-muted">Memuat riwayat...</div>');
}

function escapeProduksiHistoryValue(value) {
  return $("<div>").text(value === null || value === undefined ? "" : value).html();
}

const produksiHistoryFieldLabels = {
  progres_bangunan: "Progres Bangunan",
  st_0: "sd Sloof",
  st_25: "Dinding sd Ringbalok",
  st_50: "Dinding Full, Atap, Plester dan Aci",
  st_75: "Plafon, Keramik, Dapur, Kamar Mandi dan Cat",
  st_100: "Finishing",
  slo: "SLO / NIDI",
  bp: "BP",
  lpa: "LPA",
  lpa_tanggal: "Tanggal LPA",
  st_jalan: "Listrik",
  st_saluran: "Saluran Jalan",
  st_air: "Air",
  air_jenis: "Jenis Sumber Air",
  listrik_jenis: "Jenis Sumber Listrik",
  listrik_pln: "No Meteran Listrik PLN",
  listrik_disediakan_no: "No Pengajuan Listrik",
  listrik_disediakan_tanggal: "Tanggal Pengajuan Listrik",
  air_deskripsi_unit: "Deskripsi Unit",
  air_pdam_no: "No Meteran Air PDAM",
  keterangan: "Keterangan Pembangunan",
  tanggal_pembangunan: "Tanggal Pembangunan",
  tanggal_rencana_selesai_pembangunan: "Tanggal Rencana Selesai",
  tanggal_selesai_pembangunan: "Tanggal Selesai Pembangunan",
};

const produksiHistoryBooleanFields = [
  "st_0",
  "st_25",
  "st_50",
  "st_75",
  "st_100",
  "slo",
  "bp",
  "lpa",
  "st_jalan",
  "st_saluran",
  "st_air",
];

function formatProduksiHistoryValue(field, value) {
  if (value === null || value === undefined || value === "") return "-";
  if (produksiHistoryBooleanFields.indexOf(field) >= 0) {
    return String(value) === "1" ? "Sudah" : "Belum";
  }
  return value;
}

function renderProduksiHistoryChanges(item) {
  const oldData = item.old_data || {};
  const newData = item.new_data || {};
  let html = "";

  Object.keys(newData).forEach(function (field) {
    if (field === "checklist") return;

    const label = produksiHistoryFieldLabels[field] || field;
    const oldValue = formatProduksiHistoryValue(field, oldData[field]);
    const newValue = formatProduksiHistoryValue(field, newData[field]);

    html +=
      '<div class="produksi-history-change-row">' +
        '<div class="produksi-history-change-label">' +
          escapeProduksiHistoryValue(label) +
        "</div>" +
        '<div class="produksi-history-change-value">' +
          escapeProduksiHistoryValue(oldValue) +
          ' <i class="fas fa-arrow-right mx-50 text-muted"></i> ' +
          escapeProduksiHistoryValue(newValue) +
        "</div>" +
      "</div>";
  });

  if (item.files && item.files.length) {
    let files = "";
    $.each(item.files, function (index, file) {
      files +=
        "<li>" +
        escapeProduksiHistoryValue(file.kategori || "File") +
        (file.file_keterangan ? ": " + escapeProduksiHistoryValue(file.file_keterangan) : "") +
        "</li>";
    });

    html +=
      '<div class="produksi-history-change-row">' +
        '<div class="produksi-history-change-label">Upload</div>' +
        '<div class="produksi-history-change-value"><ul class="produksi-history-file-list">' +
          files +
        "</ul></div>" +
      "</div>";
  }

  return html ? '<div class="produksi-history-change-list">' + html + "</div>" : "";
}

function renderProduksiHistoryTimeline(history, meta, append) {
  const $target = $("#produksi-history-timeline");
  const nextOffset = meta ? (meta.history_next_offset || 0) : 0;
  const limit = meta ? (meta.history_limit || 10) : 10;
  const hasMore = !!(meta && meta.history_has_more);

  $target
    .data("next-offset", nextOffset)
    .data("history-limit", limit);

  if (!append) {
    $target.html('<div class="produksi-jalan-timeline"></div><div class="produksi-history-action mt-1"></div>');
  }

  const $timeline = $target.find(".produksi-jalan-timeline");
  if (!history.length && !append) {
    $timeline.html('<div class="text-muted">Belum ada riwayat produksi.</div>');
  }

  $.each(history, function (index, item) {
    let changeInfo = renderProduksiHistoryChanges(item);

    $timeline.append(
      '<div class="produksi-jalan-timeline-item">' +
        '<div class="d-flex justify-content-between align-items-start flex-wrap">' +
          '<div class="produksi-jalan-timeline-title">' +
            escapeProduksiHistoryValue(item.summary || "Data produksi diperbarui") +
          "</div>" +
          '<div class="produksi-jalan-timeline-meta">' +
            escapeProduksiHistoryValue(format_datetime(item.created_at) || "-") +
          "</div>" +
        "</div>" +
        '<div class="produksi-jalan-timeline-meta mb-1">Oleh: ' +
          escapeProduksiHistoryValue(item.username || "-") +
        "</div>" +
        changeInfo +
      "</div>",
    );
  });

  const $action = $target.find(".produksi-history-action");
  if (hasMore) {
    $action.html(
      '<button type="button" class="btn btn-outline-primary btn-sm" onclick="loadProduksiHistoryMore()">Muat lagi</button>',
    );
  } else {
    $action.empty();
  }
}

function loadProduksiHistory(idKavling, append = false) {
  const $target = $("#produksi-history-timeline");
  if (!idKavling) return;

  if (!append) {
    resetProduksiHistoryTimeline();
    $target.data("id-kavling", idKavling);
  }

  $.ajax({
    url: base_url + "api/produksi/history",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: idKavling,
      history_limit: $target.data("history-limit") || 10,
      history_offset: append ? ($target.data("next-offset") || 0) : 0,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      renderProduksiHistoryTimeline(r.history || [], r, append);
    },
    error: function () {
      $("#produksi-history-timeline").html('<div class="text-danger">Gagal memuat riwayat produksi.</div>');
    },
  });
}

function loadProduksiHistoryMore() {
  loadProduksiHistory($("#produksi-history-timeline").data("id-kavling"), true);
}
$("#terima_komplain").change(function () {
  if (this.checked) {
    $("#terima_komplain_div").removeClass("hidden");
  } else {
    $("#terima_komplain_div").addClass("hidden", true);
  }
});

function open_komplain_produksi() {
  if (!editdtt[0]) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Pilih salahsatu kavling",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  var sh = editdtt[0],
    id_kavling = sh.id.substr(3);

  if (!sh.data2.id_komplain) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Tidak ada komplain",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  $("#fm-komplain-produksi")[0].reset();

  $(
    "#fm-komplain-produksi #foto_komplain_sales, #fm-komplain-produksi #foto_komplain_produksi",
  ).html("");

  $(
    ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi",
  ).addClass("hidden", true);
  $("#keterangan_ditangani").prop("readonly", false);
  $("#komplain-produksi-form-btn").prop("disabled", false);

  $("#terima_komplain, #is_selesai_produksi").attr("onclick", "");
  $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", false);
  $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
    "disabled",
    false,
  );

  $("#komplain_selesai_sip").addClass("hidden");

  $("#last_update_komplain_produksi").html(
    "Terakhir diupdate oleh: -, pada: -",
  );

  $(".id_kavling").val(id_kavling);
  $("#fm-komplain-produksi #id_komplain").val(sh.data2.id_komplain);

  $.ajax({
    url: base_url + "api/produksi/get_data_komplain_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_komplain: sh.data2.id_komplain,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      let st = r.komplain,
        fotok,
        fotok_display = "",
        fotokp,
        fotokp_display = "";

      if (st) {
        //display foto komplain dari sales
        fotok = st.upload_komplain_sales_urls || [];

        if (Array.isArray(fotok)) {
          let is_active = "active";
          for (let e = 0; e < fotok.length; e++) {
            if (e > 0) is_active = "";

            fotok_display +=
              '<div class="carousel-item ' +
              is_active +
              '">' +
              '<img class="d-block w-100 ft_kom" src="' +
              fotok[e] +
              '" alt="First slide">' +
              "</div>";
          }
        }
        $("#fm-komplain-produksi #foto_komplain_sales").html(fotok_display);

        //display foto penyelsaian dari produksi
        fotokp = st.upload_komplain_produksi_urls || [];

        if (Array.isArray(fotokp)) {
          let is_active = "active";
          for (let e = 0; e < fotokp.length; e++) {
            if (e > 0) is_active = "";

            fotokp_display +=
              '<div class="carousel-item ' +
              is_active +
              '">' +
              '<img class="d-block w-100 ft_kom" src="' +
              fotokp[e] +
              '" alt="First slide">' +
              "</div>";
          }
        }
        $("#fm-komplain-produksi #foto_komplain_produksi").html(fotokp_display);

        //komplain
        $("#fm-komplain-produksi #keterangan_komplain").val(
          st.keterangan_komplain,
        );
        $("#fm-komplain-produksi #username_komplain_oleh").val(
          st.username_komplain_oleh,
        );

        if (st.komplain_tgl != "0000-00-00")
          document
            .querySelector("#fm-komplain-produksi #komplain_tgl")
            ._flatpickr.setDate(st.komplain_tgl);

        $("#last_update_komplain_produksi").html(
          "Terakhir diupdate oleh: " +
            st.username_last_update +
            ", pada: " +
            format_datetime(st.updated_at),
        );

        //ditangani
        if (st.status_komplain == 2) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);
        } else if (st.status_komplain == 3) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $("#is_selesai_produksi").attr("onclick", "return false;");
          $("#is_selesai_produksi").prop("checked", true);

          $("#keterangan_ditangani").prop("readonly", true);

          $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
            st.selesai_keterangan_produksi,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
            st.username_selesai_oleh_produksi,
          );

          if (st.selesai_tgl_produksi != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
              ._flatpickr.setDate(st.selesai_tgl_produksi);
        } else if (st.status_komplain == 4) {
          $("#terima_komplain").attr("onclick", "return false;");
          $("#terima_komplain").prop("checked", true);

          $("#is_selesai_produksi").attr("onclick", "return false;");
          $("#is_selesai_produksi").prop("checked", true);

          $("#komplain-produksi-form-btn").prop("disabled", true);

          $("#keterangan_ditangani").prop("readonly", true);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").prop(
            "disabled",
            true,
          );

          $("#fm-komplain-produksi #keterangan_ditangani").prop(
            "disabled",
            true,
          );

          $(
            ".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi",
          ).removeClass("hidden");

          $("#fm-komplain-produksi #keterangan_ditangani").val(
            st.keterangan_ditangani,
          );
          $("#fm-komplain-produksi #username_ditangani_oleh").val(
            st.username_ditangani_oleh,
          );

          if (st.ditangani_tgl != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #ditangani_tgl")
              ._flatpickr.setDate(st.ditangani_tgl);

          $("#fm-komplain-produksi #selesai_keterangan_produksi").val(
            st.selesai_keterangan_produksi,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(
            st.username_selesai_oleh_produksi,
          );

          if (st.selesai_tgl_produksi != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_produksi")
              ._flatpickr.setDate(st.selesai_tgl_produksi);

          $("#komplain_selesai_sip").removeClass("hidden");

          $("#fm-komplain-produksi #selesai_keterangan_sales").val(
            st.selesai_keterangan_sales,
          );
          $("#fm-komplain-produksi #username_selesai_oleh_sales").val(
            st.username_selesai_oleh_sales,
          );

          if (st.selesai_tgl_sales != "0000-00-00")
            document
              .querySelector("#fm-komplain-produksi #selesai_tgl_sales")
              ._flatpickr.setDate(st.selesai_tgl_sales);
        }
      }
      $(".label_alamat").html(
        dt_proyek.nama_proyek +
          "<br/>" +
          sh.data.nama_jalan +
          ", No." +
          sh.data.no_kavling +
          "<br/>" +
          sh.data2.no_tipe_rumah +
          " (" +
          sh.data2.tipe_rumah +
          ")<br/>",
      );
      $("#modal_komplain_produksi").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function () {},
  });
}

function save_komplain_produksi() {
  var files = $("#upload_komplain_sales")[0].files;
  var form = $("#fm-komplain-produksi")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/save_komplain_produksi",
    type: "POST",
    contentType: false,
    processData: false,
    // data: $("#fm-komplain-sales").serialize() + "&" + csrfName + "=" + csrfHash,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#komplain-produksi-form-btn").prop("disabled", true);
      $("#komplain-produksi-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        swal("success", r.messages);
        // Swal.fire({
        //   //position: 'bottom-end',
        //   icon: "success",
        //   title: r.messages,
        //   showConfirmButton: false,
        //   timer: 1500,
        // });

        $(".modal").modal("hide");
        hapus_seleksi();
        load_kavling();
      } else {
        swal("error", r.messages);
      }
      $("#komplain-produksi-form-btn").html("Simpan");
      $("#komplain-produksi-form-btn").prop("disabled", false);
    },
  });
}
//open form add/edit
function open_produksi(sh, role, id_kavling) {
  if (editdtt.length > 1) {
    swal("error", "Tidak bisa mengisi data lebih dari 1 secara bersamaan");
  }
  if (sh.data.tipe == "kavling") {
    return open_fproduksi(sh, role, id_kavling);
  } else {
    return open_fotherproduksi(sh);
  }
}

function save_fotherproduksi() {
  let form = $("#fm-fotherproduksi")[0],
    formData = new FormData(form);
  formData.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/produksi/edit_others",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    beforeSend: function () {
      $("#save_fotherproduksi-btn").prop("disabled", true);
      $("#save_fotherproduksi-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
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
        });

        $(".modal").modal("hide");
        hapus_seleksi();
        load_kavling();
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        });
      }
      $("#save_fotherproduksi-btn").html("Simpan");
      $("#save_fotherproduksi-btn").prop("disabled", false);
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat menginput data",
        showConfirmButton: false,
        timer: 1500,
      });
      $("#save_fotherproduksi-btn").html("Simpan");
      $("#save_fotherproduksi-btn").prop("disabled", false);
    },
  });
}
$("#save_fotherproduksi-btn").click(function (e) {
  e.preventDefault();
});

function open_fotherproduksi(sh) {
  $("#fm-fotherproduksi")[0].reset();
  $("#list_produksi_jalan_foto, #produksi_jalan_history").html("");
  resetProduksiJalanHistoryTimeline("#produksi_jalan_history");
  $("#label_produksi_jalan_foto").html("Bisa lebih dari 1 foto");
  $("#f_progres_jalan").val(0);
  $(".t_luas_legal, .t_luas_produksi, .r_progres").html(" ");
  $("#prod-jalan-progress-tab").tab("show");
  $.ajax({
    url: base_url + "siteplan/get_others",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: editdtt[0].id.substr(6),
      history_limit: PRODUKSI_JALAN_HISTORY_LIMIT,
      history_offset: 0,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;

      if (r.data) {
        let d = r.data[0],
          progres = d.progres ? d.progres : 0;
        $(".produksi-jalan-only").toggleClass("hidden", d.tipe !== "jalan");
        $(".id_kavling").val(d.id);
        $(".t_luas_legal, .t_luas_produksi").html("-");

        if (d.planning_luas)
          $(".t_luas_planning").html(
            d.planning_luas +
              "  m&sup2  (" +
              d.planning_edit +
              ": " +
              format_datetime(d.planning_updated_at) +
              ")",
          );
        if (d.legal_luas)
          $(".t_luas_legal").html(
            d.legal_luas +
              "  m&sup2  (" +
              d.legal_edit +
              ": " +
              format_datetime(d.legal_updated_at) +
              ")",
          );

        $("#f_produksi_luas").val(d.produksi_luas);
        $("#f_produksi_keterangan").val(d.produksi_keterangan);
        $("#f_progres_jalan").val(progres);
        $(".r_progres").html(progres);
        renderProduksiJalanHistoryTimeline("#produksi_jalan_history", r.history || [], r, false);
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    },
  });

  $(".label_alamat").html(
    dt_proyek.nama_proyek +
      "<br/> <span class='capitalize'>" +
      sh.data.tipe +
      "<span>: " +
      sh.data.nama_jalan +
      "",
  );
  $("#modal_fothersproduksi").modal({
    backdrop: "static",
    keyboard: false,
  });
}

function open_fproduksi(sh, role, id_kavling) {
  let update_tanggal_pembangunan = has_akses.update_tanggal_pembangunan
    ? true
    : false;
  $(".tgl_bangun").prop("disabled", !update_tanggal_pembangunan);

  $("#fm-prod-progress-tab").click();
  resetProduksiHistoryTimeline();
  ((st_0 = 0),
    (st_25 = 0),
    (st_50 = 0),
    (st_75 = 0),
    (st_100 = 0),
    (st_saluran = 0),
    (st_air = 0),
    (st_jalan = 0),
    (bp = 0),
    (lpa = 0),
    (tot = 0));

  let categories = [
    "rab_dokumen",
    "prod_foto_konstruksi",
    "prod_foto_exterior",
    "prod_foto_interior",
    "jalan_foto",
    "jalan_foto_update",
    "listrik_pln_foto",
    "listrik_disediakan_dokumen",
    "air_komunal",
    "air_tanah",
    "air_pdam",
  ];

  categories.forEach((cat) => {
    $("#list_" + cat).html("");
    $("#label_" + cat).html("Upload file/Foto");
  });
  window.produksiUploadFileStore = {};
  $(".produksi-photo-input").val("");

  $(".af .cbp").prop("disabled", true);

  $("#t_progres_bangunan").html("0");
  $("#fm-produksi")[0].reset();
  $("#last_update_checklist_prod").html("Terakhir diupdate oleh: -, pada: -");

  $(".id_kavling").val(id_kavling);
  $("#id_produksi").val(sh.data.id_produksi);
  $("#produksi-mobile-link").attr(
    "href",
    base_url + "siteplan/produksi-mobile?id_kavling=" + encodeURIComponent(id_kavling),
  );
  loadProduksiHistory(id_kavling, false);

  $("#download_gambar_kerja").off("click").on("click", function () {
    simpanBtn(
      "#download_gambar_kerja",
      true,
      'Mengunduh <i class="fa fa-spinner fa-spin"></i>',
      "Unduh Gambar Kerja",
    );
    download(sh.data2.id_gambar_kerja, () => {
      simpanBtn(
        "#download_gambar_kerja",
        false,
        'Mengunduh <i class="fa fa-spinner fa-spin"></i>',
        "Unduh Gambar Kerja",
      );
    });
  });

  $.ajax({
    url: base_url + "api/produksi/get_data_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_produksi: sh.data.id_produksi,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      let cl = r.cl,
        pb = 0;
      if (r) {
        if (r.progres_bangunan) {
          $("#progres_bangunan").val(r.progres_bangunan);
          $("#t_progres_bangunan").html(r.progres_bangunan);
        }

        changeVal("#air_jenis", r.air_jenis);
        changeVal("#listrik_jenis", r.listrik_jenis);

        changeVal("#listrik_pln", r.listrik_pln);
        changeVal("#listrik_disediakan_no", r.listrik_disediakan_no);
        changeVal("#listrik_disediakan_tanggal", r.listrik_disediakan_tanggal);
        changeVal("#air_deskripsi_unit", r.air_deskripsi_unit);
        changeVal("#air_pdam_no", r.air_pdam_no);
        // changeVal("#lpa_tanggal", r.lpa_tanggal);

        setDatePicker(r.lpa_tanggal, "#lpa_tanggal");

        setDatePicker(r.tanggal_pembangunan, "#tanggal_pembangunan");
        setDatePicker(
          r.tanggal_rencana_selesai_pembangunan,
          "#tanggal_rencana_selesai_pembangunan",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan,
          "#tanggal_selesai_pembangunan",
        );

        if (!r.tanggal_pembangunan) {
          $(".tgl_bangun").prop("disabled", false);
        }

        $("#tanggal_pembangunan_old").val(r.tanggal_pembangunan);
        $("#tanggal_rencana_selesai_pembangunan_old").val(
          r.tanggal_rencana_selesai_pembangunan,
        );
        $("#tanggal_selesai_pembangunan_old").val(
          r.tanggal_selesai_pembangunan,
        );

        $("#lu-tanggal_pembangunan").html(
          `Diinput pada:
            ${r.tanggal_pembangunan_pada ? format_datetime(r.tanggal_pembangunan_pada) : "-"},
            oleh: ${r.tanggal_pembangunan_oleh ? r.tanggal_pembangunan_oleh_u : "-"}`,
        );
        $("#lu-tanggal_rencana_selesai_pembangunan").html(
          `Diubah pada:
            ${r.tanggal_pembangunan_diubah_pada ? format_datetime(r.tanggal_pembangunan_diubah_pada) : "-"},
            oleh: ${r.tanggal_pembangunan_diubah_oleh ? r.tanggal_pembangunan_diubah_oleh_u : "-"}`,
        );
        $("#lu-tanggal_selesai_pembangunan").html(
          `Diinput pada:
            ${r.tanggal_selesai_pembangunan_diubah_pada ? format_datetime(r.tanggal_selesai_pembangunan_diubah_pada) : "-"},
            oleh: ${r.tanggal_selesai_pembangunan_diubah_oleh ? r.tanggal_selesai_pembangunan_diubah_oleh_u : "-"}`,
        );

        changeVal("#tanggal_pembangunan_oleh", r.tanggal_pembangunan_oleh);
        changeVal(
          "#tanggal_pembangunan_diubah_oleh",
          r.tanggal_pembangunan_diubah_oleh,
        );
        changeVal(
          "#tanggal_selesai_pembangunan_oleh",
          r.tanggal_selesai_pembangunan_oleh,
        );
        changeVal(
          "#tanggal_selesai_pembangunan_diubah_oleh",
          r.tanggal_selesai_pembangunan_diubah_oleh,
        );

        setDatePicker(r.tanggal_pembangunan_pada, "#tanggal_pembangunan_pada");
        setDatePicker(
          r.tanggal_pembangunan_diubah_pada,
          "#tanggal_pembangunan_diubah_pada",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan_pada,
          "#tanggal_selesai_pembangunan_pada",
        );
        setDatePicker(
          r.tanggal_selesai_pembangunan_diubah_pada,
          "#tanggal_selesai_pembangunan_diubah_pada",
        );

        changeVal("#sumurbor_keterangan", r.sumurbor_keterangan);
        setDatePicker(r.sumurbor_tanggal, "#sumurbor_tanggal");
        $("#last_update-sumurbor").html(
          `Diubah pada: ${r.sumurbor_updated ? format_datetime(r.sumurbor_updated) : "-"},
                    oleh: ${r.sumurbor_oleh_u ? r.sumurbor_oleh_u : "-"}`,
        );

        const fields = [
          { id: "st_0", value: r.st_0 },
          { id: "st_25", value: r.st_25 },
          { id: "st_50", value: r.st_50 },
          { id: "st_75", value: r.st_75 },
          { id: "st_100", value: r.st_100 },
          { id: "st_saluran", value: r.st_saluran },
          { id: "st_air", value: r.st_air },
          { id: "st_jalan", value: r.st_jalan },
          { id: "bp", value: r.bp },
          { id: "lpa", value: r.lpa },
          { id: "slo", value: r.slo },
          { id: "sumurbor", value: r.sumurbor },
        ];

        fields.forEach((field) => {
          $("#" + field.id)
            .prop("checked", field.value == 1)
            .change();
        });

        if (cl && cl.length > 0) {
          let lates_date = cl[0].produksi_cek_tgl;
          cl.forEach((val) => {
            ["t", "f", "v"].forEach((type) => {
              if (val["hasil_cek_" + type] == 1) {
                $("#hasil_cek_" + type + "\\[" + val.id_subitem + "\\]").prop(
                  "checked",
                  true,
                );
              }
            });

            $("#keterangan_cek_produksi\\[" + val.id_subitem + "\\]").val(
              val.keterangan_cek_produksi,
            );

            if (lates_date < val.produksi_cek_tgl)
              lates_date = val.produksi_cek_tgl;
          });
          $("#last_update_checklist_prod").html(
            "Terakhir diupdate oleh: " +
              cl[0].username +
              ", pada: " +
              format_date(lates_date),
          );
        }

        $("#produksi_keterangan").val(r.keterangan);

        showFoto(r.files);
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    },
  });
  $(".label_alamat").html(
    dt_proyek.nama_proyek +
      "<br/>" +
      sh.data.nama_jalan +
      ", No." +
      sh.data.no_kavling +
      "<br/>" +
      sh.data2.no_tipe_rumah +
      " (" +
      sh.data2.tipe_rumah +
      ")<br/>",
  );
  $("#modal_divisi" + role).modal({
    backdrop: "static",
    keyboard: false,
  });
}

function download(e, callback = null) {
  (async () => {
    const response = await fetch(base_url + "api/produksi/get_gambarkerja", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        [csrfName]: csrfHash,
        id_gambar_kerja: e,
        pass: "password",
      }),
    });

    if (!response.ok) {
      throw new Error("Gagal mengunduh file");
    }

    const data = await response.json();
    const url = data.lokasi;
    const a = document.createElement("a");
    let sh = editdtt[0].data2;

    a.href = url;
    const date = new Date();
    const filename = `${sh.no_tipe_rumah}: diunduh pada: ${date.toISOString().split("T")[0]} - ${date.getHours()}:${date.getMinutes()}}.pdf`;
    a.download = filename;
    a.click();
    callback();
  })().catch((error) => {
    swal("error", "Gagal mengunduh file");
    callback();
  });
}

$("#fm-slf-id_kavling").select2({
  placeholder: "Pilih Kavling",
  allowClear: true,
  ajax: {
    url: base_url + "api/produksi/getKavling",
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
      $.each(r.data, function (i, v) {
        results.push({
          id: v.id_kavling,
          text: `${v.nama_jalan} No ${v.no_kavling}: ${v.nama_konsumen ? v.nama_konsumen : "-"}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: true,
  },
});

function simpan_slf() {
  // Validate required fields
  let requiredFields = $("#fm-pr_slf").find("[required]");
  let isValid = true;

  requiredFields.each(function () {
    if ($(this).val() === "") {
      isValid = false;
      $(this).addClass("is-invalid");
    } else {
      $(this).removeClass("is-invalid");
    }
  });

  if ($("#fm-slf-id_kavling").val().length == 0) {
    isValid = false;
    $(this).addClass("is-invalid");
  } else {
    isValid = true;
    $(this).removeClass("is-invalid");
  }

  if (!isValid) {
    swal("Error", "Mohon lengkapi semua field yang wajib diisi", "error");
    return;
  }

  // If all required fields are filled, proceed with form submission
  let formData = new FormData($("#fm-pr_slf")[0]);

  formData.append(csrfName, csrfHash);
  formData.append("id_proyek", dt_proyek.id_proyek);

  $.ajax({
    url: base_url + "api/produksi/saveSLF",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.success === true) {
        swal("success", "Berhasil", "Data SLF berhasil disimpan");
        // Optionally, refresh the SLF list or perform other actions
        form_slf_reset();
        // Refresh the SLF list
        getlistSLF();

        // Reset and focus on the SLF tab
        $("#fm-slf-id_kavling").val(null).trigger("change");
        $('a[href="#fm-pr_list_slf"]').tab("show");
      } else {
        swal("error", "terjadi kesalahan", "Gagal menyimpan data SLF");
      }
    },
    error: function () {
      swal(
        "error",
        "Terjadi kesalahan",
        "Terjadi kesalahan saat menyimpan data",
      );
    },
  });
}

function getlistSLF() {
  $.ajax({
    url: base_url + "api/produksi/getSlf",
    type: "GET",
    data: { id_proyek: dt_proyek.id_proyek },
    success: function (response) {
      if (response.data && response.data.length > 0) {
        let tableContent = "";
        $.each(response.data, function (i, v) {
          tableContent += `
                                  <tr>
                                      <td>${i + 1}</td>
                                      <td>${v.no_slf}</td>
                                      <td>${v.kavling}</td>
                                      <td>
                                          <div class="form-group">
                                               <a href="${base_url}api/produksi/getSLFPDF/${v.id}" class="btn btn-outline-primary waves-effect btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                                              <a href=javascript:void(0) onclick="editSLF(${v.id})" class="btn btn-outline-warning waves-effect btn-sm"><i class="fa fa-edit"></i></a>
                                              <a href=javascript:void(0) onclick="hapusSLF(${v.id})" class="btn btn-outline-danger waves-effect btn-sm"><i class="fa fa-trash"></i></a>
                                          </div>
                                         </td>
                                      <td>${v.username + "<br>" + format_datetime(v.created_at)}</td>
                                  </tr>
                              `;
        });
        $("#tb-pr_lsit_slf-here").html(tableContent);
      } else {
        $("#tb-pr_lsit_slf-here").html(
          "<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>",
        );
      }
    },
    error: function () {
      swal("Error", "Gagal memuat data SLF", "error");
    },
  });
}

function hapusSLF(id) {
  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Data SLF akan dihapus secara permanen!",
    icon: "warning",
    showCancelButton: true,
    onfirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete.isConfirmed) {
      $.ajax({
        url: base_url + "api/produksi/hapusSLF",
        type: "POST",
        data: { id: id, [csrfName]: csrfHash },
        success: function (response) {
          if (response.success === true) {
            swal("Berhasil!", "Data SLF telah dihapus.", "success");
            getlistSLF(); // Refresh the SLF list
          } else {
            swal("Error", "Gagal menghapus data SLF", "error");
          }
        },
        error: function () {
          swal("Error", "Terjadi kesalahan saat menghapus data SLF", "error");
        },
      });
    }
  });
}

function form_slf_reset() {
  $("#fm-pr_slf")[0].reset();
  $("#tb-pr_lsit_slf-here").html(
    "<tr><td colspan='5' style='text-align: center'>Tidak ada data</td></tr>",
  );

  $("#fm-slf-id_kavling").val(null).trigger("change");

  $("#fm-slf-kelurahan").val(dt_proyek.kelurahan);
  $("#fm-slf-kecamatan").val(dt_proyek.kecamatan);
  $("#fm-slf-kota").val(dt_proyek.kota);
  $("#fm-slf-provinsi").val(dt_proyek.provinsi);
  $("#fm-slf-alamat_proyek").val(dt_proyek.alamat_proyek);
  $("#fm-slf-nama_perusahaan").val(dt_proyek.nama_pt);
  $("#fm-slf-nama_bangunan").val(dt_proyek.nama_proyek);
}

function buat_slf() {
  let sh = editdtt;
  let dvl = "";
  form_slf_reset();
  getlistSLF();

  $(".label_alamat").html(dt_proyek.nama_proyek);
  $("#modal-pr_slf").modal({
    backdrop: "static",
    keyboard: false,
  });
}
$("#bp-untuk_pembayaran").select2({
  placeholder: "Pilih Item Pembayaran",
  allowClear: true,
  dropdownParent: $("#modal-bayar_produksi-prod"),
  ajax: {
    url: base_url + "api/produksi/getBayarProduksiListItem",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.list_item, function (_k, v) {
        results.push({
          id: v.id,
          text: v.item,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

function hapus_bayar_produksi(id) {
  Swal.fire({
    title: "Hapus Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let sbtn = ".bp-del-btn";
      $.ajax({
        url: base_url + "api/produksi/deleteBayarProduksi",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id: id,
        },
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true, '<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
            load_kavling();
            isi_pembayaran(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "terjadi kesalahan",
            showConfirmButton: false,
          });
          simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
        },
      });
    }
  });
}

function isi_pembayaran(id_kav = null) {
  if (!editdtt[0] && !id_kav) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  var sh = editdtt[0],
    id_kavling = id_kav ?? sh.id.substr(3);

  $("#fm-bayar_produksi-prod")[0].reset();
  $("#bp-untuk_pembayaran").val(null).trigger("change");
  $("#bayar-produksi-table tbody").html("");

  $.ajax({
    url: base_url + "api/produksi/getBayarProduksi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      csrfHash = r.token;
      let d = r.riwayat_bayar || [];

      if (d.length === 0) {
        $("#bayar-produksi-table tbody").html(
          "<tr><td colspan='5' class='text-center'>Data tidak ditemukan</td></tr>",
        );
      } else {
        $.each(d, function (_index, val) {
          let btn = `<button type="button" class="btn btn-danger btn-sm bp-del-btn" onclick="hapus_bayar_produksi(${val.id})"><i class="fa fa-trash"></i></button>`;
          let row = `
        <tr>
            <td>${btn}</td>
            <td>${val.item}</td>
            <td>${format_date(val.tanggal_bayar) ?? "-"}</td>
            <td>${num_format(val.nominal) ?? "0"}</td>
            <td>${val.keterangan ?? "-"}</td>
        </tr>`;
          $("#bayar-produksi-table tbody").append(row);
        });
      }

      $("#bayar_produksi-id_kavling").val(id_kavling);

      let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;
      let label_alamat = setLabelAlamat(
        nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $("#modal-bayar_produksi-prod .label_alamat").html(label_alamat);

      if (r.konsumen) {
        $("#fm-bp-label_konsumen").html(r.konsumen.nama_konsumen ?? "-");
        $("#fm-bp-label_tgl").html(format_date(r.konsumen.booking_tgl) ?? "-");
        $("#fm-bp-label_bookingfee").html(num_format(r.konsumen.harga_jual) ?? "0");
      }

      initModalListener("#modal-bayar_produksi-prod");
      $("#modal-bayar_produksi-prod").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function () {
      $("#loading").addClass("hidden");
      Swal.fire({
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
      });
    },
  });
}

function save_bayar_produksi() {
  if ($("#bp-untuk_pembayaran").val() == null) {
    return swal("error", "Item pembayaran harus diisi");
  }
  if ($("#bp-tanggal_bayar").val() == "") {
    return swal("error", "Tanggal pembayaran harus diisi");
  }
  if ($("#bp-nominal").val() == "" || $("#bp-nominal").val() <= 0) {
    return swal("error", "Nominal pembayaran harus diisi");
  }

  Swal.fire({
    title: "Simpan Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let sbtn = "#add-form-btn-bayar_produksi";
      $.ajax({
        url: base_url + "api/produksi/saveBayarProduksi",
        type: "post",
        data:
          $("#fm-bayar_produksi-prod").serialize() +
          "&" +
          csrfName +
          "=" +
          csrfHash,
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true);
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false);
            load_kavling();
            isi_pembayaran(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false);
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "terjadi kesalahan",
            showConfirmButton: false,
          });
          simpanBtn(sbtn, false);
        },
      });
    }
  });
}
