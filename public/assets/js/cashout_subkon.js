const status_spp = {
  0: "<span class='badge badge-primary'>Terbit SPK</span>",
  1: "<span class='badge badge-secondary'>Turun Jatuh Tempo</span>",
  2: "<span class='badge badge-info'>Pengajuan SPP</span>",
  3: "<span class='badge badge-warning'>Pengajuan Pencairan</span>",
  4: "<span class='badge badge-success'>Dibayar Oleh Keuangan</span>",
};
const status_cashout_subkon = {
  0: ["bg-primary", "fa fa-plus"],
  1: ["bg-secondary", "fa fa-calendar"],
  2: ["bg-info", "fa fa-file"],
  3: ["bg-warning", "fa fa-share"],
  4: ["bg-success", "fa fa-check"],
};

/******** Checkout Subkon **********/
function openCOSubkon() {
  let sh = editdtt;
  const id_kavlings = sh.map((item) => item.id.substr(3));

  if (id_kavlings.length == 0) {
    return swal("error", "Pilih kavling terlebih dahulu");
  }
  const select2_id_kavling = "#fm-cashout-subkon-id_kavling";
  load_cashout_subkon_detail([]);

  $("#fm-cashout-subkon")[0].reset();
  $(select2_id_kavling).val(null).trigger("change");
  $("#fm-cashout-subkon-id_subkon").val(null).trigger("change");

  const selected_kavlings = sh.map((item) => ({
    id_kavling: item.id.substr(3),
    nama_jalan: item.data.nama_jalan,
    no_kavling: item.data.no_kavling,
  }));

  const modal = "#modal-cashout-subkon";

  setImgOrPlaceholder($("#fm-cashout-subkon-file_surat-here"), "", not_found);
  load_dropzone("fm-cashout-subkon-file_surat");

  $.ajax({
    url: base_url + "cashout/subkon/ambil",
    type: "post",
    data: {
      id_kavlings: id_kavlings,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.status == "error") {
        return swal("error", r.message);
      }

      if (isNotEmpty(r.subkon)) {
        load_subkon(r.subkon);
      }

      let kavlings = selected_kavlings;
      if (isNotEmpty(r.detail_kavling)) {
        kavlings = r.detail_kavling;
      }

      load_selected_kavling(kavlings, select2_id_kavling);

      if (isNotEmpty(r.cashout_subkon)) {
        state.id_cashout_subkon = r.cashout_subkon.id_cashout_subkon;
        load_cashout_subkon(r.cashout_subkon);
        setImgOrPlaceholder(
          $("#fm-cashout-subkon-file_surat-here"),
          r.cashout_subkon.file_surat,
          not_found,
        );
      }

      let is_locked = false;
      if (isNotEmpty(r.cashout_subkon_detail)) {
        is_locked = r.cashout_subkon_detail.some((item) => item.status != 0);
        load_cashout_subkon_detail(r.cashout_subkon_detail);
      }
      load_list_kavling(kavlings, is_locked);

      initModalListener(modal);
      $(modal).modal("show");
    },
    error: function (r) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan saat memuat data");
    },
  });
}

function loadHistoryStatusCashoutSubkon() {
  const id_timeline = "#cashout-subkon-history-timeline";
  $.ajax({
    url: base_url + "cashout/subkon/history",
    type: "post",
    data: {
      id_cashout_subkon: state.id_cashout_subkon,
    },
    dataType: "json",
    beforeSend: function () {
      $(id_timeline).html(
        "<div class='text-center text-muted py-3'><i class='fas fa-spinner fa-spin mr-1'></i> Memuat riwayat...</div>",
      );
    },
    success: function (r) {
      $(id_timeline).html("");
      if (r.status == "error") {
        return swal("error", r.message);
      }
      if (r.data.length == 0) {
        $(id_timeline).html("<p class='text-center'>Belum ada aktivitas</p>");
        return;
      }
      r.data.forEach((item) => {
        $(id_timeline).append(
          `
         <div class="timeline-item pb-4">
          <div class="timeline-icon ${status_cashout_subkon[item.status][0]}">
            <i class="${status_cashout_subkon[item.status][1]}"></i>
          </div>
          <div class="timeline-content">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 font-weight-bold">${item.keterangan}</h6>
              <span class="badge badge-success-light text-success px-2"><i class="far fa-calendar-alt mr-1"></i> ${format_datetime(item.created_at)}</span>
            </div>
            <div class="text-muted small mt-1">
              <i class="far fa-user ml-3 mr-1"></i> ${item.username}
            </div>
          </div>
        </div>
          `,
        );
      });

      // if (isNotEmpty(r.cashout_subkon_detail)) {
      //   load_cashout_subkon_detail(r.cashout_subkon_detail);
      // }
    },
    error: function (r) {
      $(id_timeline).html(
        "<p class='text-center'>Terjadi kesalahan saat memuat data</p>",
      );
    },
  });
}

$("#fm-cashout-subkon-submit").click(function (e) {
  e.preventDefault();

  if (!isNotEmpty($("#fm-cashout-subkon-nama_subkon").val())) {
    return swal("error", "Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-hp1_subkon").val())) {
    return swal("error", "No. HP Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-alamat_subkon").val())) {
    return swal("error", "Alamat Subkon Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-id_kavling").val())) {
    return swal("error", "Kavling Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-tanggal_surat").val())) {
    return swal("error", "Tanggal Surat Harus diisi");
  }
  if (!isNotEmpty($("#fm-cashout-subkon-nomor_surat").val())) {
    return swal("error", "No. Surat Harus diisi");
  }
  if (
    !isNotEmpty($("#fm-cashout-subkon-total_nominal").val()) ||
    removeComma($("#fm-cashout-subkon-total_nominal").val()) == 0
  ) {
    return swal("error", "Total Nominal harus diisi");
  }

  let total_percentage = 0;
  $(".termin-percentage").each(function () {
    total_percentage += removeComma($(this).val());
  });
  if (total_percentage != 100) {
    return swal("error", "Total Persentase harus 100%");
  }
  let total_nominal = 0;
  $(".termin-nominal").each(function () {
    total_nominal += removeComma($(this).val());
  });
  if (
    total_nominal != removeComma($("#fm-cashout-subkon-total_nominal").val())
  ) {
    return swal("error", "Total Nominal harus sesuai dengan total persentase");
  }

  if (!isNotEmpty($("#fm-cashout-subkon-id_cashout_subkon").val())) {
    if (!isNotEmpty($("#fm-cashout-subkon-file_surat").val())) {
      return swal("error", "Soft file SPK harus diupload");
    }
  }

  const data = $("#fm-cashout-subkon").serializeArray();
  let formData = new FormData();
  data.forEach((item) => {
    formData.append(item.name, item.value);
  });
  const file = $("#fm-cashout-subkon-file_surat")[0].files[0];
  if (file) {
    formData.append("file_surat", file);
  }
  $.ajax({
    url: base_url + "cashout/subkon/save",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (r) {
      if (r.status == "error") {
        return swal("error", r.message);
      }
      removeModalListener("#modal-cashout-subkon");
      swal("success", r.message);
      $("#modal-cashout-subkon").modal("hide");
      // load_cashout_subkon();
    },
    error: function (r) {
      return swal("error", "Terjadi kesalahan saat menyimpan data");
    },
  });
});

function disabled_input(is_locked) {
  if (!is_locked) {
    $(".termin-percentage").prop("disabled", false);
    $(".fm-input").prop("disabled", false);
    $("#fm-cashout-subkon-submit").prop("disabled", false);
  } else {
    $(".termin-percentage").prop("disabled", true);
    $(".fm-input").prop("disabled", true);
    $("#fm-cashout-subkon-submit").prop("disabled", true);
  }
  if (roleid == 3) {
    $(".fm-input").prop("disabled", true);
    $(".termin-percentage").prop("disabled", true);
    $("#fm-cashout-subkon-submit").prop("disabled", true);
  }

  // if (is_locked) {
  //   $("#fm-cashout-subkon :input").prop("disabled", true);
  //   $("#modal-cashout-subkon .modal-footer .btn-primary").prop(
  //     "disabled",
  //     true,
  //   );
  //   $(".remove-termin").prop("disabled", true);
  // } else {
  //   $("#fm-cashout-subkon :input").prop("disabled", false);
  //   $("#modal-cashout-subkon .modal-footer .btn-primary").prop(
  //     "disabled",
  //     false,
  //   );
  // }
}

function sum_percentage(el) {
  let total = 0;
  $(".termin-percentage").each(function () {
    total += removeComma($(this).val());
  });
  if (total > 100) {
    showToast("Total persentase melebihi 100%", "warning");
  }
  let tr = $(el).closest("tr");
  let p = removeComma($(el).val());
  let totals = removeComma($("#fm-cashout-subkon-total_nominal").val());
  let nominal = (totals * p) / 100;
  tr.find(".termin-nominal").val(num_format(nominal));
}
function load_cashout_subkon_detail(data) {
  let is_locked = data.some((item) => item.status != 0);
  let disabled = is_locked ? "disabled" : "";

  const tbody = $("#fm-cashout-subkon-termin");
  if (tbody.find("tr").length > 0 && tbody.find("tr").length == data.length) {
    data.forEach((item, i) => {
      // $(`#fm-cashout-subkon-id_cashout_subkon_detail-${i}`).val(
      //   item.id_cashout_subkon_detail ?? "",
      // );
      $(`#fm-cashout-subkon-berita_acara-${i}`).val(item.berita_acara ?? "");
      $(`#fm-cashout-subkon-persentase-${i}`).val(num_format(item.persentase));
      $(`#fm-cashout-subkon-nominal-${i}`).val(num_format(item.nominal));
      if (item.tanggal_jatuh_tempo) {
        $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${i}`).val(
          item.tanggal_jatuh_tempo,
        );
      }
      if (item.keterangan) {
        $(`#fm-cashout-subkon-keterangan-${i}`).val(item.keterangan);
      }
    });
  } else {
    tbody.empty();
    let i = 0;
    data.forEach((item) => {
      let btn = "";
      let status = "";
      if (roleid == 3 && item.status == 0) {
        btn = `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Terbit Jatuh Tempo</button>`;
      } else if (roleid == 3 && item.status == 1) {
        btn = `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah Jatuh Tempo</button>`;
      } else if (roleid == 7 && item.status == 1) {
        btn = `<button type="button" class="btn btn-sm btn-info ajukan-spp w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Ajukan SPP</button>`;
      } else if (roleid == 7 && item.status == 2) {
        status =
          "No SPP: " + item.spp_no + "(" + format_date(item.spp_tgl) + ")";
        btn = `<button type="button" class="btn btn-sm btn-info ajukan-spp w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah SPP</button>`;
      } else if (roleid == 3 && item.status == 2) {
        status =
          "No SPP: " + item.spp_no + "(" + format_date(item.spp_tgl) + ")";
        btn = `<button type="button" class="btn btn-sm btn-warning ajukan-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Ajukan Pencairan</button>`;
      } else if (roleid == 3 && item.status == 3) {
        btn = `<button type="button" class="btn btn-sm btn-warning ajukan-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-edit"></i> Ubah Tanggal</button>`;
        btn += `<button type="button" class="btn btn-sm btn-success pembayaran-pencairan w-100" data-i="${i}" data-id="${item.id_cashout_subkon_detail}" data-status="${item.status}"><i class="fa fa-plus"></i> Pembayaran</button>`;
      } else if (item.status == 4) {
        status =
          "No Cek: " + item.cek_no + "(" + format_date(item.cek_tgl) + ")";
      }

      const tr = $("<tr>");
      tr.append(`<td style="min-width:100px; nowrap">
      <input type="hidden" id="fm-cashout-subkon-berita_acara-${i}" name="berita_acara[]" value="${
        item.berita_acara ?? ""
      }">
      ${item.berita_acara ?? "-"} 
      </td>`);
      tr.append(
        `<td style="min-width:100px">
        <input type="hidden" id="fm-cashout-subkon-id_cashout_subkon_detail-${i}" name="id_cashout_subkon_detail[]" value="${item.id_cashout_subkon_detail ?? ""}">
        <input type="text" class="form-control form-control-sm termin-percentage num" id="fm-cashout-subkon-persentase-${i}" name="persentase[]" value="${num_format(item.persentase)}">
        </td>`,
      );
      tr.append(
        `<td><input type="text" class="form-control form-control-sm termin-nominal num" id="fm-cashout-subkon-nominal-${i}" name="nominal[]" value="${num_format(
          item.nominal,
        )}" readonly></td>`,
      );
      tr.append(
        `<td style="min-width:150px"><input type="text" disabled class="form-control form-control-sm flatpickr-human-friendly termin-jatuh-tempo" id="fm-cashout-subkon-tanggal_jatuh_tempo-${i}" name="tanggal_jatuh_tempo[]" value="${
          item.tanggal_jatuh_tempo || ""
        }"></td>`,
      );
      tr.append(`
          <td>
            ${status_spp[item.status] || "-"}<br>
            ${status}
            </td>`);
      tr.append(
        `<td><input type="text" class="form-control form-control-sm termin-keterangan" id="fm-cashout-subkon-keterangan-${i}" name="keterangan[]" value="${
          item.keterangan || ""
        }"></td>`,
      );
      tr.append(`<td>${btn}</td>`);
      tbody.append(tr);
      i++;
    });

    disabled_input(is_locked);

    $(".termin-percentage").change(function () {
      let tr = $(this).closest("tr");
      if (tr.find(".termin-nominal").prop("readonly")) {
        sum_percentage(this);
      }
    });

    $(".remove-termin").click(function () {
      $(this).closest("tr").remove();
    });

    $(".flatpickr-human-friendly").flatpickr({
      altInput: true,
      altFormat: "j F Y",
      dateFormat: "Y-m-d",
    });
  }
}

// === Turun Jatuh Tempo: inline edit flow ===
$(document).on("click", ".turun-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);
  const fp = dateInput[0]._flatpickr;

  // Enable the date input (both original + flatpickr alt input)
  dateInput.prop("disabled", false);
  if (fp && fp.altInput) {
    fp.altInput.disabled = false;
  }

  // Replace button with confirm (✓) and cancel (✗) buttons
  const td = btn.closest("td");
  td.html(
    `<div class="btn-group w-100">
      <button type="button" class="btn btn-sm btn-success confirm-jatuh-tempo w-50" data-i="${idx}" data-id="${id}"><i class="fa fa-check"></i></button>
      <button type="button" class="btn btn-sm btn-danger cancel-jatuh-tempo w-50" data-i="${idx}" data-id="${id}"><i class="fa fa-times"></i></button>
    </div>`,
  );
});

// Confirm: validate & send to server
$(document).on("click", ".confirm-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);
  const tanggal = dateInput.val();

  if (!tanggal) {
    return swal("error", "Tanggal Jatuh Tempo harus diisi");
  }

  const berita_acara = $(`#fm-cashout-subkon-berita_acara-${idx}`).val();

  $.ajax({
    url: base_url + "cashout/subkon/turun-jatuh-tempo",
    type: "POST",
    data: {
      id_cashout_subkon_detail: id,
      tanggal_jatuh_tempo: tanggal,
      berita_acara: berita_acara,
    },
    dataType: "json",
    beforeSend: function () {
      btn.prop("disabled", true);
    },
    success: function (r) {
      btn.prop("disabled", false);
      if (r.status == "error") {
        return swal("error", r.message);
      }
      swal("success", r.message);
      // Disable input again and remove action buttons
      dateInput.prop("disabled", true);
      if (dateInput[0]._flatpickr && dateInput[0]._flatpickr.altInput) {
        dateInput[0]._flatpickr.altInput.disabled = true;
      }
      const td = btn.closest("td");
      td.html("");

      // Update status badge
      const tr = dateInput.closest("tr");
      const statusTd = tr.find("td").eq(4);
      statusTd.html(status_spp[1] || "-");
    },
    error: function () {
      btn.prop("disabled", false);
      return swal("error", "Terjadi kesalahan saat menyimpan data");
    },
  });
});

// Cancel: revert to original button
$(document).on("click", ".cancel-jatuh-tempo", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");
  const dateInput = $(`#fm-cashout-subkon-tanggal_jatuh_tempo-${idx}`);

  // Disable input again (both original + flatpickr alt input)
  dateInput.prop("disabled", true);
  if (dateInput[0]._flatpickr && dateInput[0]._flatpickr.altInput) {
    dateInput[0]._flatpickr.altInput.disabled = true;
  }

  // Restore original button
  const td = btn.closest("td");
  td.html(
    `<button type="button" class="btn btn-sm btn-secondary turun-jatuh-tempo" data-i="${idx}" data-id="${id}" data-status="0"><i class="fa fa-edit"></i> Terbit Jatuh Tempo</button>`,
  );
});

// === Ajukan SPP: SweetAlert popup with form ===
$(document).on("click", ".ajukan-spp", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Ajukan SPP",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-spp_no">No. SPP</label>
          <input type="text" id="swal-spp_no" class="form-control" placeholder="Masukkan No. SPP">
        </div>
        <div class="form-group">
          <label for="swal-spp_tgl">Tanggal SPP</label>
          <input type="text" id="swal-spp_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal SPP">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-spp_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const spp_no = Swal.getPopup().querySelector("#swal-spp_no").value;
      const spp_tgl = Swal.getPopup().querySelector("#swal-spp_tgl").value;
      if (!spp_no || !spp_tgl) {
        Swal.showValidationMessage(`No SPP dan Tanggal SPP harus diisi`);
      }
      return { spp_no: spp_no, spp_tgl: spp_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/ajukan-spp",
        type: "POST",
        data: {
          id_cashout_subkon_detail: id,
          spp_no: result.value.spp_no,
          spp_tgl: result.value.spp_tgl,
        },
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);

          // Update status badge
          const tr = btn.closest("tr");
          const statusTd = tr.find("td").eq(4);
          statusTd.html(status_spp[2] || "-");

          // Remove the button or update it
          btn.closest("td").html("");
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});

// === Ajukan Pencairan: SweetAlert popup with form ===
$(document).on("click", ".ajukan-pencairan", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Ajukan Pencairan",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-pencairan_tgl">Tanggal Pengajuan Cair</label>
          <input type="text" id="swal-pencairan_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal Pengajuan">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-pencairan_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const pencairan_tgl = Swal.getPopup().querySelector(
        "#swal-pencairan_tgl",
      ).value;
      if (!pencairan_tgl) {
        Swal.showValidationMessage(`Tanggal Pengajuan Cair harus diisi`);
      }
      return { pencairan_tgl: pencairan_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/ajukan-pencairan",
        type: "POST",
        data: {
          id_cashout_subkon_detail: id,
          pencairan_tgl: result.value.pencairan_tgl,
        },
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);

          // Update status badge
          const tr = btn.closest("tr");
          const statusTd = tr.find("td").eq(4);
          statusTd.html(status_spp[3] || "-");

          // Remove the button or update it
          btn.closest("td").html("");
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});

// === Pembayaran Pencairan: SweetAlert popup with form ===
$(document).on("click", ".pembayaran-pencairan", function () {
  const btn = $(this);
  const idx = btn.data("i");
  const id = btn.data("id");

  Swal.fire({
    title: "Pembayaran Pencairan",
    html: `
      <div class="text-left">
        <div class="form-group">
          <label for="swal-cek_no">No. Cek</label>
          <input type="text" id="swal-cek_no" class="form-control" placeholder="Masukkan No. Cek">
        </div>
        <div class="form-group">
          <label for="swal-cek_tgl">Tanggal Cek</label>
          <input type="text" id="swal-cek_tgl" class="form-control flatpickr-basic" placeholder="Pilih Tanggal Cek">
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: "Simpan",
    cancelButtonText: "Batal",
    focusConfirm: false,
    didOpen: () => {
      $("#swal-cek_tgl").flatpickr({
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
      });
    },
    preConfirm: () => {
      const cek_no = Swal.getPopup().querySelector("#swal-cek_no").value;
      const cek_tgl = Swal.getPopup().querySelector("#swal-cek_tgl").value;
      if (!cek_no || !cek_tgl) {
        Swal.showValidationMessage(`No Cek dan Tanggal Cek harus diisi`);
      }
      return { cek_no: cek_no, cek_tgl: cek_tgl };
    },
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "cashout/subkon/pembayaran",
        type: "POST",
        data: {
          id_cashout_subkon_detail: id,
          cek_no: result.value.cek_no,
          cek_tgl: result.value.cek_tgl,
        },
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Mohon Tunggu",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (r) {
          if (r.status == "error") {
            return swal("error", r.message);
          }
          swal("success", r.message);

          // Update status badge
          const tr = btn.closest("tr");
          const statusTd = tr.find("td").eq(4);
          statusTd.html(status_spp[4] || "-");

          // Remove the button or update it
          btn.closest("td").html("");
        },
        error: function () {
          return swal("error", "Terjadi kesalahan saat menyimpan data");
        },
      });
    }
  });
});
function load_list_kavling(data, is_locked = false) {
  const ul = $("#fm-cashout-subkon-list_kavlings");
  ul.empty();
  data.forEach((item) => {
    let text = item.text || `${item.nama_jalan} No ${item.no_kavling}`;
    let id = item.id || item.id_kavling;
    const li = $("<li>").addClass(
      "list-group-item d-flex justify-content-between align-items-center",
    );
    // console.log(roleid, is_locked);
    if (roleid == 7 && !is_locked) {
      li.html(
        `${text} <button type="button" class="btn btn-sm btn-danger remove-kavling" data-id="${id}"><i class="fa fa-trash"></i></button>`,
      );
    } else {
      li.html(`${text}`);
    }
    ul.append(li);
  });
  $(".remove-kavling")
    .off("click")
    .click(function () {
      const id = $(this).data("id");
      const select = $("#fm-cashout-subkon-id_kavling");
      let values = select.val() || [];
      values = values.filter((v) => v != id);
      select.val(values).trigger("change");
      $(this).closest("li").remove();
    });
}
function load_cashout_subkon(data) {
  if (data == null) {
    $("#fm-cashout-subkon-id_cashout_subkon").val("");
    $("#fm-cashout-subkon-nomor_surat").val("");
    setDatePicker("", "#fm-cashout-subkon-tanggal_surat");
    $("#fm-cashout-subkon-total_nominal").val("");
    $("#fm-cashout-subkon-keterangan_cashout_subkon").val("");
  } else {
    $("#fm-cashout-subkon-id_cashout_subkon").val(data.id_cashout_subkon);
    $("#fm-cashout-subkon-nomor_surat").val(data.nomor_surat);
    setDatePicker(data.tanggal_surat, "#fm-cashout-subkon-tanggal_surat");
    $("#fm-cashout-subkon-total_nominal").val(num_format(data.total_nominal));
    $("#fm-cashout-subkon-keterangan_cashout_subkon").val(data.keterangan);
  }
}
function load_selected_kavling(data, id_select2) {
  $(id_select2).empty();
  data.forEach((item) => {
    const option = new Option(
      item.nama_jalan + " - " + item.no_kavling,
      item.id_kavling,
      true,
      true,
    );
    $(id_select2).append(option);
  });
  $(id_select2).trigger("change");
}
function load_subkon(data) {
  if (data == []) {
    $("#fm-cashout-subkon-id_subkon").val("");
    $("#fm-cashout-subkon-nama_subkon").val("");
    $("#fm-cashout-subkon-hp1_subkon").val("");
    $("#fm-cashout-subkon-alamat_subkon").val("");
  } else {
    const option = new Option(data.nama_subkon, data.id, true, true);
    $("#fm-cashout-subkon-id_subkon").append(option);

    $("#fm-cashout-subkon-id_subkon").val(data.id);
    $("#fm-cashout-subkon-nama_subkon").val(data.nama_subkon);
    $("#fm-cashout-subkon-hp1_subkon").val(data.hp1_subkon);
    $("#fm-cashout-subkon-alamat_subkon").val(data.alamat_subkon);
  }
}

$("#fm-cashout-subkon-total_nominal").change(function () {
  let val = removeComma($(this).val());
  let termin = $("#fm-cashout-subkon-termin");
  const data_subkon = [];
  if (val > 0) {
    let percentages = [20, 20, 20, 20, 15, 5];
    percentages.forEach((p, i) => {
      let nominal = (val * p) / 100;
      data_subkon.push({
        berita_acara: "Termin " + (i + 1),
        persentase: p,
        nominal: nominal,
        tanggal_jatuh_tempo: "",
        status: 0,
        keterangan: "",
      });
    });
    load_cashout_subkon_detail(data_subkon);
  } else {
    termin.empty();
  }
});

$("#fm-cashout-subkon-id_kavling")
  .select2({
    placeholder: "Pilih Kavling",
    allowClear: true,
    multiple: true,
    ajax: {
      url: base_url + "kavling/list/ambil",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          is_cashout_subkon: 1,
          id_proyek: dt_proyek.id_proyek,
          limit: 20,
        };
      },
      processResults: function (r) {
        let results = [];
        $.each(r, function (i, v) {
          let disabled = false;
          let text = `${v.nama_jalan} No ${v.no_kavling}`;
          if (v.id_cashout_subkon) {
            disabled = true;
            text += " (Sudah dibuat tagihan)";
          }
          results.push({
            id: v.id_kavling,
            text: text,
            // disabled: disabled,
          });
        });
        return {
          results: results,
        };
      },
      cache: true,
    },
  })
  .on("change", function () {
    load_list_kavling($(this).select2("data"));
  });

$("#fm-cashout-subkon-id_subkon")
  .select2({
    placeholder: "Pilih Subkon",
    allowClear: true,
    ajax: {
      url: base_url + "subkon/list/ambil",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          limit: 20,
        };
      },
      processResults: function (r) {
        let results = [];
        $.each(r, function (i, v) {
          results.push({
            id: v.id,
            text: `${v.nama_subkon}`,
            nama_subkon: v.nama_subkon,
            hp1_subkon: v.hp1_subkon,
            alamat_subkon: v.alamat_subkon,
          });
        });
        return {
          results: results,
        };
      },
      cache: true,
    },
  })
  .on("change", function () {
    let data = $(this).select2("data");
    if (data.length > 0) {
      load_subkon(data[0]);
    } else {
      load_subkon([]);
    }
  });
