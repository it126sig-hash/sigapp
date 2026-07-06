let pencairanAkadState = {
  idKavling: null,
  idMkdt: null,
  sh: null,
  mkdt: {},
  plan: null,
  items: [],
  listDajam: [],
  pengajuan: [],
};

function paMoney(value) {
  return num_format(parseFloat(value || 0));
}

function paEscape(value) {
  return $("<div>").text(value === null || value === undefined ? "" : value).html();
}

function paStatusBadge(status) {
  const map = {
    active: '<span class="badge badge-secondary">Aktif</span>',
    partial: '<span class="badge badge-warning">Partial</span>',
    paid: '<span class="badge badge-success">Paid</span>',
    void: '<span class="badge badge-light-danger">Void</span>',
  };
  return map[status] || status;
}

function openPencairanAkadModal(row) {
  dt_proyek.nama_proyek = row.nama_proyek || "";
  pencairanAkadState = {
    idKavling: row.id_kavling,
    idMkdt: row.id_mkdt,
    sh: { data: { nama_jalan: row.nama_jalan, no_kavling: row.no_kavling } },
    mkdt: {},
    plan: null,
    items: [],
    listDajam: [],
    pengajuan: [],
  };
  loadPencairanAkadData(true);
}

function loadPencairanAkadData(openModal) {
  const st = pencairanAkadState;
  if (!st.idKavling || !st.idMkdt) return;

  $.ajax({
    url: base_url + "keuangan/pencairan-akad/get",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: st.idKavling,
      id_mkdt: st.idMkdt,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.token) csrfHash = r.token;
      if (r.success === false) {
        return swal("error", "Terjadi kesalahan", r.messages || r.message || "Data tidak ditemukan");
      }

      st.mkdt = r.mkdt || {};
      st.plan = r.plan || null;
      st.items = r.items || [];
      st.listDajam = r.list_dajam || [];
      st.pengajuan = r.list_pengajuan || [];

      if (parseInt(st.mkdt.is_kpr) === 1 && parseFloat(st.mkdt.harga_kpr_acc || 0) <= 0) {
        swal("warning", "Nominal ACC KPR 0", "Hubungi MKDT untuk memastikan nominal ACC KPR sebelum membuat plan pencairan.");
      }

      if (openModal) {
        $("#pencairan_akad_modal").modal({ backdrop: "static", keyboard: false });
      }

      renderPencairanAkadHeader();
      renderPencairanAkadSummary();
      renderPencairanAkadRetensi();
      renderPencairanAkadTenor();
      renderPencairanAkadPengajuanItemPicker();
      renderPencairanAkadPengajuanTable();
      renderPencairanAkadCairSelect();
    },
    error: function (a, b, c) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan", c);
    },
  });
}

function renderPencairanAkadHeader() {
  const sh = pencairanAkadState.sh;
  $("#pa-pengajuan-id_plan").val(pencairanAkadState.plan ? pencairanAkadState.plan.id : "");
  $("#pa-label-alamat").html(
    dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
  );
  $("#pa-label-konsumen").text((sh.data2 && sh.data2.nama_konsumen) || sh.data.nama_konsumen || "-");
  $("#pa-status-mkdt").text(pencairanAkadState.mkdt.status_mkdt || "-");
}

function renderPencairanAkadSummary() {
  const m = pencairanAkadState.mkdt || {};
  const plan = pencairanAkadState.plan;
  const accKpr = parseFloat(m.harga_kpr_acc || 0);
  const totalRetensi = plan ? parseFloat(plan.total_retensi || 0) : 0;
  const hasilAkad = plan ? parseFloat(plan.total_hasil_akad || 0) : accKpr;

  const nonVoid = pencairanAkadState.pengajuan.filter(function (row) { return row.status !== "void"; });
  const totalCair = nonVoid.reduce(function (sum, row) { return sum + parseFloat(row.total_cair || 0); }, 0);
  const totalPengajuanOutstanding = nonVoid.reduce(function (sum, row) {
    return sum + (parseFloat(row.total_pengajuan || 0) - parseFloat(row.total_cair || 0));
  }, 0);

  $("#pa-acc-kpr-label").text(paMoney(accKpr));
  $("#pa-total-retensi-label").text(paMoney(totalRetensi));
  $("#pa-hasil-akad-label").text(paMoney(hasilAkad));
  $("#pa-total-pengajuan-label").text(paMoney(totalPengajuanOutstanding));
  $("#pa-total-cair-label").text(paMoney(totalCair));
  $("#pa-sisa-hasil-akad-label").text(paMoney(accKpr - totalCair));
}

function paItemsByJenis(jenis) {
  return pencairanAkadState.items.filter(function (item) {
    return item.jenis === jenis;
  });
}

function paRetensiUsedListDajamIds() {
  const ids = [];
  $("#pa-retensi_here .pa-retensi-row").each(function () {
    ids.push(parseInt($(this).data("id-list-dajam")));
  });
  return ids;
}

function renderPencairanAkadRetensiPicker() {
  const used = paRetensiUsedListDajamIds();
  let options = '<option value="">Pilih item...</option>';
  pencairanAkadState.listDajam.forEach(function (d) {
    if (used.includes(parseInt(d.id))) return;
    options += `<option value="${d.id}">${paEscape(d.nama_jaminan)}</option>`;
  });
  $("#pa-retensi-picker").html(options);
}

function paRetensiRowHtml(idItem, idListDajam, namaJaminan, nominal, catatan, isLocked) {
  const disabled = isLocked ? "disabled" : "";
  const removeBtn = isLocked
    ? '<span class="badge badge-light-secondary">Terpakai di pengajuan</span>'
    : `<button type="button" class="btn btn-outline-danger btn-sm" onclick="removePencairanAkadRetensiRow(this)"><i class="fas fa-trash"></i></button>`;

  return `
    <div class="form-row align-items-end pa-retensi-row mb-1" data-id="${idItem || ""}" data-id-list-dajam="${idListDajam}">
      <div class="col-md-3">${paEscape(namaJaminan)}</div>
      <div class="col-md-3">
        <input type="text" class="form-control form-control-sm num pa-retensi-nominal" value="${nominal || 0}" placeholder="Nominal retensi" ${disabled}>
      </div>
      <div class="col-md-4">
        <input type="text" class="form-control form-control-sm pa-retensi-catatan" value="${paEscape(catatan || "")}" placeholder="Catatan" ${disabled}>
      </div>
      <div class="col-md-2">${removeBtn}</div>
    </div>`;
}

function addPencairanAkadRetensiRow() {
  const idListDajam = parseInt($("#pa-retensi-picker").val());
  if (!idListDajam) return;

  const d = pencairanAkadState.listDajam.find(function (x) { return parseInt(x.id) === idListDajam; });
  if (!d) return;

  $("#pa-retensi_here").append(paRetensiRowHtml(null, idListDajam, d.nama_jaminan, 0, "", false));
  $("#pa-retensi_here .num").keyup();
  renderPencairanAkadRetensiPicker();
}

function removePencairanAkadRetensiRow(btn) {
  $(btn).closest(".pa-retensi-row").remove();
  renderPencairanAkadRetensiPicker();
}

function renderPencairanAkadRetensi() {
  const retensi = paItemsByJenis("retensi");
  let html = "";
  retensi.forEach(function (item) {
    html += paRetensiRowHtml(item.id, item.id_list_dajam, item.nama_jaminan, item.nominal, item.catatan, !!item.is_locked);
  });
  $("#pa-retensi_here").html(html || '<p class="text-muted">Belum ada item retensi. Pilih item dari dropdown lalu klik Tambah.</p>');
  $("#pa-retensi_here .num").keyup();
  renderPencairanAkadRetensiPicker();
}

function savePencairanAkadRetensi() {
  const retensi = [];
  $("#pa-retensi_here .pa-retensi-row").each(function () {
    retensi.push({
      id: $(this).data("id") || "",
      id_list_dajam: $(this).data("id-list-dajam"),
      nominal: parseFloat(String($(this).find(".pa-retensi-nominal").val() || "0").replace(/,/g, "")),
      catatan: $(this).find(".pa-retensi-catatan").val(),
    });
  });

  $.ajax({
    url: base_url + "keuangan/pencairan-akad/plan/save-retensi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: pencairanAkadState.idMkdt,
      id_kavling: pencairanAkadState.idKavling,
      retensi: retensi,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.token) csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages || r.message || "Retensi berhasil disimpan");
        loadPencairanAkadData(false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Retensi gagal disimpan");
    },
  });
}

function paTenorRowHtml(idItem, nominal, catatan, isLocked) {
  const disabled = isLocked ? "disabled" : "";
  const removeBtn = isLocked
    ? '<span class="badge badge-light-secondary">Terpakai di pengajuan</span>'
    : `<button type="button" class="btn btn-outline-danger btn-sm" onclick="removePencairanAkadTenorRow(this)"><i class="fas fa-trash"></i></button>`;

  return `
    <div class="form-row align-items-end pa-tenor-row mb-1" data-id="${idItem || ""}">
      <div class="col-md-4">
        <input type="text" class="form-control form-control-sm num pa-tenor-nominal" value="${nominal || 0}" placeholder="Nominal tenor" ${disabled}>
      </div>
      <div class="col-md-5">
        <input type="text" class="form-control form-control-sm pa-tenor-catatan" value="${paEscape(catatan || "")}" placeholder="Catatan tenor" ${disabled}>
      </div>
      <div class="col-md-3">${removeBtn}</div>
    </div>`;
}

function addPencairanAkadTenorRow() {
  $("#pa-tenor_here").append(paTenorRowHtml(null, 0, "", false));
  $("#pa-tenor_here .num").keyup();
  updatePencairanAkadTenorSisa();
}

function removePencairanAkadTenorRow(btn) {
  $(btn).closest(".pa-tenor-row").remove();
  updatePencairanAkadTenorSisa();
}

function renderPencairanAkadTenor() {
  const tenor = paItemsByJenis("tenor");
  let html = "";
  tenor.forEach(function (item) {
    html += paTenorRowHtml(item.id, item.nominal, item.catatan, !!item.is_locked);
  });
  $("#pa-tenor_here").html(html || '<p class="text-muted">Belum ada tenor. Klik Tambah Tenor.</p>');
  $("#pa-tenor_here .num").keyup();
  updatePencairanAkadTenorSisa();
}

function paTenorTotalInForm() {
  let total = 0;
  $("#pa-tenor_here .pa-tenor-row .pa-tenor-nominal").each(function () {
    total += parseFloat(String($(this).val() || "0").replace(/,/g, "")) || 0;
  });
  return total;
}

function updatePencairanAkadTenorSisa() {
  const hasilAkad = pencairanAkadState.plan ? parseFloat(pencairanAkadState.plan.total_hasil_akad || 0) : 0;
  const sisa = hasilAkad - paTenorTotalInForm();
  $("#pa-tenor-sisa-label").text("Rp " + paMoney(sisa));
  $("#pa-tenor-sisa-box").toggleClass("alert-light-danger", sisa < 0).toggleClass("alert-light-primary", sisa >= 0);
}

function paParsePercentInput(rawValue, hasilAkad) {
  const match = String(rawValue || "").trim().match(/^(\d+(?:\.\d+)?)\s*%$/);
  if (!match) return null;
  const pct = parseFloat(match[1]);
  if (isNaN(pct) || pct < 0) return null;
  return Math.round((hasilAkad * pct) / 100);
}

function paClampTenorInput(el) {
  const $input = $(el);
  const hasilAkad = pencairanAkadState.plan ? parseFloat(pencairanAkadState.plan.total_hasil_akad || 0) : 0;
  let othersTotal = 0;
  $("#pa-tenor_here .pa-tenor-row .pa-tenor-nominal").not($input).each(function () {
    othersTotal += parseFloat(String($(this).val() || "0").replace(/,/g, "")) || 0;
  });
  const maxAllowed = Math.max(0, hasilAkad - othersTotal);
  const current = parseFloat(String($input.val() || "0").replace(/,/g, "")) || 0;
  if (current > maxAllowed + 0.01) {
    $input.val(maxAllowed);
    $input.keyup();
  }
}

$(document).on("keydown", "#pa-tenor_here .pa-tenor-nominal", function (e) {
  if (e.key !== "%") return;
  e.preventDefault();
  const hasilAkad = pencairanAkadState.plan ? parseFloat(pencairanAkadState.plan.total_hasil_akad || 0) : 0;
  const rawDigits = String($(this).val() || "0").replace(/,/g, "");
  const amount = paParsePercentInput(rawDigits + "%", hasilAkad);
  if (amount === null) return;
  $(this).val(amount);
  $(this).keyup();
});

$(document).on("keyup change", "#pa-tenor_here .pa-tenor-nominal", function () {
  paClampTenorInput(this);
  updatePencairanAkadTenorSisa();
});

function savePencairanAkadTenor() {
  if (paTenorTotalInForm() > parseFloat(pencairanAkadState.plan ? pencairanAkadState.plan.total_hasil_akad || 0 : 0) + 0.01) {
    return swal("error", "Terjadi kesalahan", "Total tenor tidak boleh melebihi hasil akad");
  }

  const tenor = [];
  $("#pa-tenor_here .pa-tenor-row").each(function () {
    tenor.push({
      id: $(this).data("id") || "",
      nominal: parseFloat(String($(this).find(".pa-tenor-nominal").val() || "0").replace(/,/g, "")),
      catatan: $(this).find(".pa-tenor-catatan").val(),
    });
  });

  $.ajax({
    url: base_url + "keuangan/pencairan-akad/plan/save-tenor",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: pencairanAkadState.idMkdt,
      id_kavling: pencairanAkadState.idKavling,
      tenor: tenor,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.token) csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages || r.message || "Tenor berhasil disimpan");
        loadPencairanAkadData(false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Tenor gagal disimpan");
    },
  });
}

function renderPencairanAkadPengajuanItemPicker() {
  const visibleItems = pencairanAkadState.items.filter(function (item) {
    return parseFloat(item.sisa) > 0.01;
  });
  let cols = "";
  visibleItems.forEach(function (item) {
    const label = item.jenis === "retensi"
      ? "Retensi - " + paEscape(item.nama_jaminan || "")
      : "Tenor #" + item.urutan_tenor;
    cols += `
      <div class="col-md-6">
        <div class="custom-control custom-checkbox mb-50">
          <input type="checkbox" class="custom-control-input" id="pa-pengajuan-item-${item.id}" name="items[]" value="${item.id}">
          <label class="custom-control-label" for="pa-pengajuan-item-${item.id}">
            ${label} - Rp ${paMoney(item.sisa)} ${item.catatan ? "(" + paEscape(item.catatan) + ")" : ""}
          </label>
        </div>
      </div>`;
  });
  const html = cols ? `<div class="row">${cols}</div>` : "";
  $("#pa-pengajuan-item_here").html(html || '<p class="text-muted">Belum ada item yang bisa diajukan. Simpan plan retensi/tenor dulu.</p>');
}

$(document).on("submit", "#form-pencairan-akad-pengajuan", function (e) {
  e.preventDefault();
  const fd = new FormData(this);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "keuangan/pencairan-akad/pengajuan/store",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.token) csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages || r.message || "Pengajuan berhasil disimpan");
        $("#form-pencairan-akad-pengajuan")[0].reset();
        loadPencairanAkadData(false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Pengajuan gagal disimpan");
    },
  });
});

function renderPencairanAkadPengajuanTable() {
  const tb = document.querySelector("#tbl-pencairan-akad-pengajuan tbody");
  tb.innerHTML = "";

  if (!pencairanAkadState.pengajuan.length) {
    tb.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Belum ada pengajuan.</td></tr>';
    return;
  }

  pencairanAkadState.pengajuan.forEach(function (row, i) {
    const lampiran = row.access_url
      ? `<a href="${row.access_url}" target="_blank" class="btn btn-link btn-sm">Lihat</a>`
      : "-";
    const action = row.status === "void"
      ? '<span class="text-muted">-</span>'
      : `<button type="button" class="btn btn-outline-danger btn-sm" onclick="voidPencairanAkad(${row.id})" ${parseFloat(row.total_cair) > 0 ? "disabled" : ""}><i class="fas fa-ban"></i></button>`;
    const itemList = (row.details || [])
      .map(function (d) {
        const label = d.jenis === "retensi" ? "Retensi - " + paEscape(d.nama_jaminan || "") : "Tenor #" + d.urutan_tenor;
        const catatan = d.item_catatan ? " (" + paEscape(d.item_catatan) + ")" : "";
        return `${label}: Rp ${paMoney(d.nominal_pengajuan)}${catatan}`;
      })
      .join("<br>") || "-";

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${row.tanggal_pengajuan ? format_date(row.tanggal_pengajuan) : "-"}</td>
      <td>${row.tanggal_rencana_cair ? format_date(row.tanggal_rencana_cair) : "-"}</td>
      <td>${itemList}</td>
      <td class="text-right">Rp ${paMoney(row.total_pengajuan)}</td>
      <td class="text-right">Rp ${paMoney(row.total_cair)}</td>
      <td>${paStatusBadge(row.status)}</td>
      <td>${paEscape(row.add_by_name || "-")}</td>
      <td>${lampiran}</td>
      <td>${action}</td>
    `;
    tb.appendChild(tr);
  });
}

function voidPencairanAkad(id) {
  Swal.fire({
    title: "Void pengajuan ini?",
    type: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, void",
    cancelButtonText: "Batal",
    confirmButtonClass: "btn btn-danger",
    cancelButtonClass: "btn btn-outline-secondary ml-1",
    buttonsStyling: false,
  }).then(function (result) {
    if (!result.value) return;

    $.ajax({
      url: base_url + "keuangan/pencairan-akad/void",
      type: "post",
      data: {
        [csrfName]: csrfHash,
        id_pengajuan: id,
        reason: "Dibatalkan oleh user",
      },
      dataType: "json",
      success: function (r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          swal("success", r.messages || r.message || "Pengajuan berhasil di-void");
          loadPencairanAkadData(false);
        } else {
          swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
        }
      },
    });
  });
}

function renderPencairanAkadCairSelect() {
  let options = '<option value="">Pilih pengajuan</option>';
  pencairanAkadState.pengajuan
    .filter(function (row) { return row.status === "active" || row.status === "partial"; })
    .forEach(function (row) {
      options += `<option value="${row.id}">#${row.id} - ${row.tanggal_pengajuan} - Rp ${paMoney(row.total_pengajuan)} (${row.status})</option>`;
    });
  $("#pa-cair-select_pengajuan").html(options);
  $("#pa-cair-detail_here").html("");
}

function renderPencairanAkadCairForm() {
  const id = $("#pa-cair-select_pengajuan").val();
  $("#pa-cair-id_pengajuan").val(id || "");
  if (!id) {
    $("#pa-cair-detail_here").html("");
    return;
  }

  const row = pencairanAkadState.pengajuan.find(function (r) { return parseInt(r.id) === parseInt(id); });
  if (!row) return;

  let html = "";
  (row.details || []).forEach(function (d) {
    const sisa = parseFloat(d.nominal_pengajuan) - parseFloat(d.nominal_cair || 0);
    if (sisa <= 0) return;
    const label = d.jenis === "retensi" ? "Retensi - " + paEscape(d.nama_jaminan || "") : "Tenor #" + d.urutan_tenor;
    html += `
      <div class="form-row align-items-end mb-1">
        <div class="col-md-6">${label} (sisa Rp ${paMoney(sisa)})</div>
        <div class="col-md-6">
          <input type="text" class="form-control form-control-sm num" name="details[${d.id}][nominal_cair]" value="${sisa}" placeholder="Nominal cair">
        </div>
      </div>`;
  });
  $("#pa-cair-detail_here").html(html || '<p class="text-muted">Tidak ada sisa item untuk dicairkan.</p>');
  $("#pa-cair-detail_here .num").keyup();
}

$(document).on("submit", "#form-pencairan-akad-cair", function (e) {
  e.preventDefault();

  $.ajax({
    url: base_url + "keuangan/pencairan-akad/pencairan/store",
    type: "post",
    data: $(this).serialize() + "&" + csrfName + "=" + csrfHash,
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      if (r.token) csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages || r.message || "Pencairan berhasil disimpan");
        $("#form-pencairan-akad-cair")[0].reset();
        loadPencairanAkadData(false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Pencairan gagal disimpan");
    },
  });
});

const paHistoryActionMap = {
  simpan_retensi: ["bg-primary", "fa fa-list"],
  simpan_tenor: ["bg-primary", "fa fa-list"],
  pengajuan: ["bg-warning", "fa fa-paper-plane"],
  pencairan: ["bg-success", "fa fa-money-bill"],
  void: ["bg-danger", "fa fa-ban"],
};

function loadPencairanAkadHistory() {
  $.ajax({
    url: base_url + "keuangan/pencairan-akad/history/" + pencairanAkadState.idKavling,
    type: "get",
    dataType: "json",
    beforeSend: function () {
      $("#pa-history_here").html(
        "<div class='text-center text-muted py-3'><i class='fas fa-spinner fa-spin mr-1'></i> Memuat riwayat...</div>",
      );
    },
    success: function (r) {
      const rows = r.data || [];
      $("#pa-history_here").html("");
      if (!rows.length) {
        $("#pa-history_here").html('<p class="text-center text-muted">Belum ada history.</p>');
        return;
      }
      rows.forEach(function (row) {
        const conf = paHistoryActionMap[row.action] || ["bg-secondary", "fa fa-info"];
        $("#pa-history_here").append(`
          <div class="timeline-item pb-4">
            <div class="timeline-icon ${conf[0]}">
              <i class="${conf[1]}"></i>
            </div>
            <div class="timeline-content">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold">${paEscape(row.summary || row.action)}</h6>
                <span class="badge badge-light-secondary text-secondary px-2"><i class="far fa-calendar-alt mr-1"></i> ${format_datetime(row.created_at)}</span>
              </div>
              <div class="text-muted small mt-1">
                <i class="far fa-user ml-3 mr-1"></i> ${paEscape(row.username || "-")}
              </div>
            </div>
          </div>`);
      });
    },
    error: function () {
      $("#pa-history_here").html('<p class="text-center text-muted">Terjadi kesalahan saat memuat data.</p>');
    },
  });
}
