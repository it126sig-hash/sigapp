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
  $("#pa-pengajuan-form-wrap, #pa-pencairan-form-wrap").hide();
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
        swal("warning", "Nominal Plafon KPR Disetujui 0", "Hubungi MKDT untuk memastikan nominal Plafon KPR Disetujui sebelum membuat plan pencairan.");
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

function paRp(value) {
  return "Rp " + paMoney(value);
}

function paKpiPercentText(value, base) {
  const pct = base > 0 ? Math.max(0, Math.min(100, (value / base) * 100)) : 0;
  const rounded = Math.round(pct * 100) / 100;
  return { pct: rounded, text: (Number.isInteger(rounded) ? rounded : rounded.toFixed(2)) + "%" };
}

function paSetKpiProgress(prefix, value, base) {
  const info = paKpiPercentText(value, base);
  $("#" + prefix + "-pct").text(info.text);
  $("#" + prefix + "-bar").css("width", info.pct + "%");
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
  const sisa = accKpr - totalCair;

  $("#pa-acc-kpr-label").text(paRp(accKpr));
  $("#pa-total-retensi-label").text(paRp(totalRetensi));
  $("#pa-hasil-akad-label").text(paRp(hasilAkad));
  $("#pa-total-pengajuan-label").text(paRp(totalPengajuanOutstanding));
  $("#pa-total-cair-label").text(paRp(totalCair));
  $("#pa-sisa-hasil-akad-label").text(paRp(sisa));

  paSetKpiProgress("pa-total-cair", totalCair, accKpr);
  paSetKpiProgress("pa-total-pengajuan", totalPengajuanOutstanding, accKpr);
  paSetKpiProgress("pa-sisa-hasil-akad", sisa, accKpr);

  $("#pa-bank-box").toggle(!!m.bank);
  $("#pa-bank-label").text(m.bank || "-");
}

function paHasilAkadValue() {
  const plan = pencairanAkadState.plan;
  if (plan) return parseFloat(plan.total_hasil_akad || 0);
  return parseFloat((pencairanAkadState.mkdt || {}).harga_kpr_acc || 0);
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

function paItemStatusBadge(isLocked, sudahCair, sisa, idItem) {
  if (isLocked) {
    if (parseFloat(sudahCair || 0) > 0 && parseFloat(sisa || 0) <= 0.01) {
      return '<span class="badge badge-success font-weight-bold">Cair</span>';
    }
    return '<span class="badge badge-warning font-weight-bold">Dalam Pengajuan</span>';
  }
  if (idItem) {
    return '<span class="badge badge-light-secondary font-weight-bold">Belum Diajukan</span>';
  }
  return '<span class="badge badge-light-info font-weight-bold">Draft Baru</span>';
}

function paRetensiRowHtml(idItem, idListDajam, namaJaminan, nominal, catatan, isLocked, sudahCair, sisa) {
  const isSaved = !!idItem;
  const disabled = (isLocked || isSaved) ? "disabled" : "";
  const statusBadge = paItemStatusBadge(isLocked, sudahCair, sisa, idItem);

  let actionBtns = "";
  if (isLocked) {
    actionBtns = '<span class="badge badge-light-secondary">Terkunci</span>';
  } else {
    const editBtn = isSaved
      ? '<button type="button" class="btn btn-outline-primary btn-sm btn-edit-retensi mr-25" title="Edit Retensi"><i class="fas fa-edit"></i></button>'
      : '';
    const removeBtn = '<button type="button" class="btn btn-outline-danger btn-sm" onclick="removePencairanAkadRetensiRow(this)" title="Hapus"><i class="fas fa-trash"></i></button>';
    actionBtns = `<div class="d-inline-flex align-items-center">${editBtn}${removeBtn}</div>`;
  }

  return `
    <tr class="pa-retensi-row" data-id="${idItem || ""}" data-id-list-dajam="${idListDajam}">
      <td class="font-weight-bold text-dark align-middle">${paEscape(namaJaminan)}</td>
      <td>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
          <input type="text" class="form-control form-control-sm num pa-retensi-nominal text-right" value="${nominal || 0}" placeholder="Nominal retensi" ${disabled}>
        </div>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm pa-retensi-catatan" value="${paEscape(catatan || "")}" placeholder="Catatan" ${disabled}>
      </td>
      <td class="align-middle">${statusBadge}</td>
      <td class="text-center align-middle">${actionBtns}</td>
    </tr>`;
}

function paRetensiTotalInForm() {
  let total = 0;
  $("#pa-retensi_here .pa-retensi-row .pa-retensi-nominal").each(function () {
    total += parseFloat(String($(this).val() || "0").replace(/,/g, "")) || 0;
  });
  return total;
}

function paClampRetensiInput(el) {
  const $input = $(el);
  const accKpr = parseFloat((pencairanAkadState.mkdt || {}).harga_kpr_acc || 0);
  const totalTenor = paTenorTotalInForm();
  let otherRetensi = 0;
  $("#pa-retensi_here .pa-retensi-row .pa-retensi-nominal").not($input).each(function () {
    otherRetensi += parseFloat(String($(this).val() || "0").replace(/,/g, "")) || 0;
  });
  const maxAllowed = Math.max(0, accKpr - totalTenor - otherRetensi);
  const current = parseFloat(String($input.val() || "0").replace(/,/g, "")) || 0;
  if (current > maxAllowed + 0.01) {
    $input.val(maxAllowed);
    $input.keyup();
  }
}

function addPencairanAkadRetensiRow() {
  const idListDajam = parseInt($("#pa-retensi-picker").val());
  if (!idListDajam) {
    return swal("warning", "Pilih Item", "Silakan pilih item jaminan retensi terlebih dahulu.");
  }

  const d = pencairanAkadState.listDajam.find(function (x) { return parseInt(x.id) === idListDajam; });
  if (!d) return;

  const accKpr = parseFloat((pencairanAkadState.mkdt || {}).harga_kpr_acc || 0);
  const currentRetensi = paRetensiTotalInForm();
  const currentTenor = paTenorTotalInForm();

  if (accKpr > 0 && (currentRetensi + currentTenor >= accKpr - 0.01)) {
    return swal("warning", "Batas Maksimal Tercapai", "Total Retensi dan Pencairan Dana Akad Bersih sudah mencapai batas maksimal Plafon KPR (Rp " + paMoney(accKpr) + "). Tidak dapat menambahkan retensi baru.");
  }

  if ($("#pa-retensi_here tr.pa-retensi-row").length === 0) {
    $("#pa-retensi_here").empty();
  }

  $("#pa-retensi_here").append(paRetensiRowHtml(null, idListDajam, d.nama_jaminan, 0, "", false, 0, 0));
  $("#pa-retensi_here .num").keyup();
  renderPencairanAkadRetensiPicker();
}

function removePencairanAkadRetensiRow(btn) {
  $(btn).closest(".pa-retensi-row").remove();
  if ($("#pa-retensi_here tr.pa-retensi-row").length === 0) {
    $("#pa-retensi_here").html('<tr><td colspan="5" class="text-center text-muted py-2">Belum ada item retensi. Pilih item dari dropdown lalu klik Tambah.</td></tr>');
  }
  renderPencairanAkadRetensiPicker();
}

function serializeRetensiFormData() {
  const rows = [];
  $("#pa-retensi_here .pa-retensi-row").each(function () {
    rows.push({
      id: $(this).data("id") || "",
      id_list_dajam: $(this).data("id-list-dajam") || "",
      nominal: parseFloat(String($(this).find(".pa-retensi-nominal").val() || "0").replace(/,/g, "")),
      catatan: $(this).find(".pa-retensi-catatan").val() || ""
    });
  });
  return JSON.stringify(rows);
}

function isPencairanAkadRetensiDirty() {
  if (!$("#pa-retensi-pane").length) return false;
  const current = serializeRetensiFormData();
  return pencairanAkadState.savedRetensiSnapshot !== undefined && current !== pencairanAkadState.savedRetensiSnapshot;
}

function renderPencairanAkadRetensi() {
  const retensi = paItemsByJenis("retensi");
  let html = "";
  retensi.forEach(function (item) {
    html += paRetensiRowHtml(item.id, item.id_list_dajam, item.nama_jaminan, item.nominal, item.catatan, !!item.is_locked, item.sudah_cair, item.sisa);
  });
  $("#pa-retensi_here").html(html || '<tr><td colspan="5" class="text-center text-muted py-2">Belum ada item retensi. Pilih item dari dropdown lalu klik Tambah.</td></tr>');
  $("#pa-retensi_here .num").keyup();
  renderPencairanAkadRetensiPicker();
  pencairanAkadState.savedRetensiSnapshot = serializeRetensiFormData();
}

function savePencairanAkadRetensi(callback) {
  const accKpr = parseFloat((pencairanAkadState.mkdt || {}).harga_kpr_acc || 0);
  const totalRetensi = paRetensiTotalInForm();
  const totalTenor = paTenorTotalInForm();

  if (accKpr > 0 && totalRetensi + totalTenor > accKpr + 0.01) {
    swal("error", "Validasi Gagal", "Total Retensi dan Pencairan Dana Akad Bersih (Rp " + paMoney(totalRetensi + totalTenor) + ") tidak boleh melebihi Plafon KPR (Rp " + paMoney(accKpr) + ").");
    if (typeof callback === "function") callback(false);
    return;
  }

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
        if (typeof callback === "function") callback(true);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
        if (typeof callback === "function") callback(false);
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Retensi gagal disimpan");
      if (typeof callback === "function") callback(false);
    },
  });
}

function paTenorRowHtml(idItem, nominal, catatan, isLocked, sudahCair, sisa) {
  const isSaved = !!idItem;
  const disabled = (isLocked || isSaved) ? "disabled" : "";
  const statusBadge = paItemStatusBadge(isLocked, sudahCair, sisa, idItem);

  let actionBtns = "";
  if (isLocked) {
    actionBtns = '<span class="badge badge-light-secondary">Terkunci</span>';
  } else {
    const editBtn = isSaved
      ? '<button type="button" class="btn btn-outline-primary btn-sm btn-edit-tenor mr-25" title="Edit Termin"><i class="fas fa-edit"></i></button>'
      : '';
    const removeBtn = '<button type="button" class="btn btn-outline-danger btn-sm" onclick="removePencairanAkadTenorRow(this)" title="Hapus"><i class="fas fa-trash"></i></button>';
    actionBtns = `<div class="d-inline-flex align-items-center">${editBtn}${removeBtn}</div>`;
  }

  return `
    <tr class="pa-tenor-row" data-id="${idItem || ""}">
      <td>
        <div class="input-group input-group-sm">
          <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
          <input type="text" class="form-control form-control-sm num pa-tenor-nominal text-right" value="${nominal || 0}" placeholder="Nominal termin" ${disabled}>
        </div>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm pa-tenor-catatan" value="${paEscape(catatan || "")}" placeholder="Catatan termin" ${disabled}>
      </td>
      <td class="align-middle">${statusBadge}</td>
      <td class="text-center align-middle">${actionBtns}</td>
    </tr>`;
}

function serializeTenorFormData() {
  const rows = [];
  $("#pa-tenor_here .pa-tenor-row").each(function () {
    rows.push({
      id: $(this).data("id") || "",
      nominal: parseFloat(String($(this).find(".pa-tenor-nominal").val() || "0").replace(/,/g, "")),
      catatan: $(this).find(".pa-tenor-catatan").val() || ""
    });
  });
  return JSON.stringify(rows);
}

function isPencairanAkadTenorDirty() {
  if (!$("#pa-tenor-pane").length) return false;
  const current = serializeTenorFormData();
  return pencairanAkadState.savedTenorSnapshot !== undefined && current !== pencairanAkadState.savedTenorSnapshot;
}

function getPencairanAkadTenorSisa() {
  const hasilAkad = paHasilAkadValue();
  return hasilAkad - paTenorTotalInForm();
}

function addPencairanAkadTenorRow() {
  const hasilAkad = paHasilAkadValue();
  if (hasilAkad <= 0) {
    return swal("warning", "Peringatan", "Dana Akad Bersih belum tersedia atau Rp 0.");
  }
  const sisa = getPencairanAkadTenorSisa();
  if (sisa <= 0.01) {
    return swal("warning", "Sisa Tagihan Habis", "Sisa Dana Akad Bersih sudah Rp 0. Tidak dapat menambahkan termin baru.");
  }

  showPencairanAkadTenorBreakdownModal();
}

function showPencairanAkadTenorBreakdownModal() {
  const hasilAkad = paHasilAkadValue();
  const n30 = Math.round(hasilAkad * 0.3);
  const n60 = Math.round(hasilAkad * 0.6);
  const n10 = Math.max(0, hasilAkad - n30 - n60);

  const html = `
    <div class="text-left py-50">
      <div class="alert alert-light-primary p-1 mb-2">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted font-weight-bold" style="font-size: 0.8rem;">Dana Akad Bersih:</span>
          <strong class="text-primary font-weight-bolder" style="font-size: 1rem;">${paRp(hasilAkad)}</strong>
        </div>
      </div>
      <p class="text-muted mb-1" style="font-size: 0.84rem;">Pilih metode pembagian termin pencairan dana akad bersih:</p>
      <div class="list-group">
        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center p-1 mb-75 border rounded btn-choose-breakdown" data-type="30-60-10">
          <div class="mr-1 text-primary" style="font-size: 1.4rem;"><i class="fas fa-layer-group"></i></div>
          <div class="flex-grow-1">
            <strong class="text-dark d-block">30%, 60%, 10%</strong>
            <small class="text-muted d-block">Otomatis 3 termin (${paRp(n30)}, ${paRp(n60)}, ${paRp(n10)})</small>
          </div>
          <span class="badge badge-light-primary badge-pill">3 Termin</span>
        </button>
        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center p-1 mb-75 border rounded btn-choose-breakdown" data-type="100">
          <div class="mr-1 text-success" style="font-size: 1.4rem;"><i class="fas fa-check-circle"></i></div>
          <div class="flex-grow-1">
            <strong class="text-dark d-block">Pencairan Sekaligus 100%</strong>
            <small class="text-muted d-block">Otomatis 1 termin penuh (${paRp(hasilAkad)})</small>
          </div>
          <span class="badge badge-light-success badge-pill">1 Termin</span>
        </button>
        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center p-1 border rounded btn-choose-breakdown" data-type="manual">
          <div class="mr-1 text-secondary" style="font-size: 1.4rem;"><i class="fas fa-edit"></i></div>
          <div class="flex-grow-1">
            <strong class="text-dark d-block">Manual</strong>
            <small class="text-muted d-block">Tambah baris termin kosong untuk diisi manual</small>
          </div>
          <span class="badge badge-light-secondary badge-pill">Manual</span>
        </button>
      </div>
    </div>
  `;

  Swal.fire({
    title: '<strong>Pilih Breakdown Termin</strong>',
    html: html,
    showConfirmButton: false,
    showCancelButton: true,
    cancelButtonText: 'Batal',
    cancelButtonColor: '#82868b',
    didOpen: function () {
      $('.btn-choose-breakdown').on('click', function () {
        const type = String($(this).data('type'));
        Swal.close();
        applyPencairanAkadTenorBreakdown(type);
      });
    }
  });
}

function applyPencairanAkadTenorBreakdown(type) {
  const typeStr = String(type);
  const hasilAkad = paHasilAkadValue();

  if (typeStr === 'manual') {
    if (getPencairanAkadTenorSisa() <= 0.01) {
      return swal("warning", "Sisa Tagihan Habis", "Sisa Dana Akad Bersih sudah Rp 0.");
    }
    if ($("#pa-tenor_here tr.pa-tenor-row").length === 0) {
      $("#pa-tenor_here").empty();
    }
    $("#pa-tenor_here").append(paTenorRowHtml(null, 0, "", false, 0, 0));
    $("#pa-tenor_here .num").keyup();
    updatePencairanAkadTenorSisa();
    return;
  }

  const hasLocked = $("#pa-tenor_here .pa-tenor-row").filter(function () {
    return $(this).find(".pa-tenor-nominal").prop("disabled") && $(this).data("id");
  }).length > 0 && pencairanAkadState.items.some(function (i) { return i.jenis === "tenor" && i.is_locked; });

  if (hasLocked) {
    return swal("warning", "Termin Terkunci", "Sebagian termin sudah diajukan ke bank dan tidak dapat diubah otomatis dengan template. Silakan gunakan opsi Manual.");
  }

  const existingRowsCount = $("#pa-tenor_here tr.pa-tenor-row").length;

  function doApply() {
    $("#pa-tenor_here").empty();
    if (typeStr === '30-60-10') {
      const n30 = Math.round(hasilAkad * 0.3);
      const n60 = Math.round(hasilAkad * 0.6);
      const n10 = Math.max(0, hasilAkad - n30 - n60);

      $("#pa-tenor_here").append(paTenorRowHtml(null, n30, "30%", false, 0, n30));
      $("#pa-tenor_here").append(paTenorRowHtml(null, n60, "60%", false, 0, n60));
      $("#pa-tenor_here").append(paTenorRowHtml(null, n10, "10%", false, 0, n10));
    } else if (typeStr === '100') {
      $("#pa-tenor_here").append(paTenorRowHtml(null, hasilAkad, "100%", false, 0, hasilAkad));
    }
    $("#pa-tenor_here .num").keyup();
    updatePencairanAkadTenorSisa();
  }

  if (existingRowsCount > 0) {
    Swal.fire({
      title: 'Konfirmasi Template',
      text: 'Daftar termin saat ini akan digantikan dengan template baru. Lanjutkan?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Terapkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#2057a3',
      cancelButtonColor: '#82868b'
    }).then(function (result) {
      if (result.isConfirmed) {
        doApply();
      }
    });
  } else {
    doApply();
  }
}

function removePencairanAkadTenorRow(btn) {
  $(btn).closest(".pa-tenor-row").remove();
  if ($("#pa-tenor_here tr.pa-tenor-row").length === 0) {
    $("#pa-tenor_here").html('<tr><td colspan="4" class="text-center text-muted py-2">Belum ada termin. Klik Tambah Termin.</td></tr>');
  }
  updatePencairanAkadTenorSisa();
}

function renderPencairanAkadTenor() {
  const tenor = paItemsByJenis("tenor");
  let html = "";
  tenor.forEach(function (item) {
    html += paTenorRowHtml(item.id, item.nominal, item.catatan, !!item.is_locked, item.sudah_cair, item.sisa);
  });
  $("#pa-tenor_here").html(html || '<tr><td colspan="4" class="text-center text-muted py-2">Belum ada termin. Klik Tambah Termin.</td></tr>');
  $("#pa-tenor_here .num").keyup();
  updatePencairanAkadTenorSisa();
  pencairanAkadState.savedTenorSnapshot = serializeTenorFormData();
}

function paTenorTotalInForm() {
  let total = 0;
  $("#pa-tenor_here .pa-tenor-row .pa-tenor-nominal").each(function () {
    total += parseFloat(String($(this).val() || "0").replace(/,/g, "")) || 0;
  });
  return total;
}

function updatePencairanAkadTenorSisa() {
  const hasilAkad = paHasilAkadValue();
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
  const hasilAkad = paHasilAkadValue();
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
  const hasilAkad = paHasilAkadValue();
  const rawDigits = String($(this).val() || "0").replace(/,/g, "");
  const pctString = rawDigits + "%";
  const amount = paParsePercentInput(pctString, hasilAkad);
  if (amount === null) return;
  $(this).val(amount);
  $(this).keyup();
  
  const $row = $(this).closest(".pa-tenor-row");
  const $catatan = $row.find(".pa-tenor-catatan");
  if ($catatan.length) {
    $catatan.val(pctString);
  }
});

$(document).on("keyup change", "#pa-tenor_here .pa-tenor-nominal", function () {
  paClampTenorInput(this);
  updatePencairanAkadTenorSisa();
});

$(document).on("keyup change", "#pa-retensi_here .pa-retensi-nominal", function () {
  paClampRetensiInput(this);
});

$(document).on("click", ".btn-edit-tenor", function () {
  const $row = $(this).closest(".pa-tenor-row");
  $row.find(".pa-tenor-nominal, .pa-tenor-catatan").prop("disabled", false);
  $row.find(".pa-tenor-nominal").focus();
  $(this).removeClass("btn-outline-primary").addClass("btn-primary").prop("disabled", true);
});

$(document).on("click", ".btn-edit-retensi", function () {
  const $row = $(this).closest(".pa-retensi-row");
  $row.find(".pa-retensi-nominal, .pa-retensi-catatan").prop("disabled", false);
  $row.find(".pa-retensi-nominal").focus();
  $(this).removeClass("btn-outline-primary").addClass("btn-primary").prop("disabled", true);
});

let paTabSwitchingConfirmed = false;
$(document).on("show.bs.tab", "#pencairan_akad_modal .nav-tabs a[data-toggle='tab']", function (e) {
  if (paTabSwitchingConfirmed) {
    paTabSwitchingConfirmed = false;
    return;
  }

  const activeTabHref = $("#pencairan_akad_modal .nav-tabs .nav-link.active").attr("href");
  const targetTab = $(this);
  const targetHref = targetTab.attr("href");

  if (activeTabHref === "#pa-tenor-pane" && targetHref !== "#pa-tenor-pane" && isPencairanAkadTenorDirty()) {
    e.preventDefault();

    Swal.fire({
      title: 'Data Belum Disimpan',
      text: 'Anda memiliki perubahan breakdown termin yang belum disimpan. Ingin menyimpan data ini?',
      icon: 'warning',
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-save mr-25"></i> Simpan',
      denyButtonText: '<i class="fas fa-undo mr-25"></i> Batalkan',
      cancelButtonText: 'Tetap di Sini',
      confirmButtonColor: '#2057a3',
      denyButtonColor: '#ea5455',
      cancelButtonColor: '#82868b'
    }).then(function (result) {
      if (result.isConfirmed) {
        savePencairanAkadTenor(function (success) {
          if (success) {
            paTabSwitchingConfirmed = true;
            targetTab.tab("show");
          }
        });
      } else if (result.isDenied) {
        renderPencairanAkadTenor();
        paTabSwitchingConfirmed = true;
        targetTab.tab("show");
      }
    });
  } else if (activeTabHref === "#pa-retensi-pane" && targetHref !== "#pa-retensi-pane" && isPencairanAkadRetensiDirty()) {
    e.preventDefault();

    Swal.fire({
      title: 'Data Belum Disimpan',
      text: 'Anda memiliki perubahan item retensi yang belum disimpan. Ingin menyimpan data ini?',
      icon: 'warning',
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-save mr-25"></i> Simpan',
      denyButtonText: '<i class="fas fa-undo mr-25"></i> Batalkan',
      cancelButtonText: 'Tetap di Sini',
      confirmButtonColor: '#2057a3',
      denyButtonColor: '#ea5455',
      cancelButtonColor: '#82868b'
    }).then(function (result) {
      if (result.isConfirmed) {
        savePencairanAkadRetensi(function (success) {
          if (success) {
            paTabSwitchingConfirmed = true;
            targetTab.tab("show");
          }
        });
      } else if (result.isDenied) {
        renderPencairanAkadRetensi();
        paTabSwitchingConfirmed = true;
        targetTab.tab("show");
      }
    });
  }
});

let paModalCloseConfirmed = false;
$(document).on("hide.bs.modal", "#pencairan_akad_modal", function (e) {
  if (paModalCloseConfirmed) {
    paModalCloseConfirmed = false;
    return;
  }

  const activeTabHref = $("#pencairan_akad_modal .nav-tabs .nav-link.active").attr("href");
  if (activeTabHref === "#pa-tenor-pane" && isPencairanAkadTenorDirty()) {
    e.preventDefault();

    Swal.fire({
      title: 'Data Belum Disimpan',
      text: 'Anda memiliki perubahan breakdown termin yang belum disimpan. Ingin menyimpan data sebelum menutup?',
      icon: 'warning',
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-save mr-25"></i> Simpan',
      denyButtonText: '<i class="fas fa-trash-alt mr-25"></i> Batalkan & Tutup',
      cancelButtonText: 'Tetap di Sini',
      confirmButtonColor: '#2057a3',
      denyButtonColor: '#ea5455',
      cancelButtonColor: '#82868b'
    }).then(function (result) {
      if (result.isConfirmed) {
        savePencairanAkadTenor(function (success) {
          if (success) {
            paModalCloseConfirmed = true;
            $("#pencairan_akad_modal").modal("hide");
          }
        });
      } else if (result.isDenied) {
        renderPencairanAkadTenor();
        paModalCloseConfirmed = true;
        $("#pencairan_akad_modal").modal("hide");
      }
    });
  } else if (activeTabHref === "#pa-retensi-pane" && isPencairanAkadRetensiDirty()) {
    e.preventDefault();

    Swal.fire({
      title: 'Data Belum Disimpan',
      text: 'Anda memiliki perubahan item retensi yang belum disimpan. Ingin menyimpan data sebelum menutup?',
      icon: 'warning',
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-save mr-25"></i> Simpan',
      denyButtonText: '<i class="fas fa-trash-alt mr-25"></i> Batalkan & Tutup',
      cancelButtonText: 'Tetap di Sini',
      confirmButtonColor: '#2057a3',
      denyButtonColor: '#ea5455',
      cancelButtonColor: '#82868b'
    }).then(function (result) {
      if (result.isConfirmed) {
        savePencairanAkadRetensi(function (success) {
          if (success) {
            paModalCloseConfirmed = true;
            $("#pencairan_akad_modal").modal("hide");
          }
        });
      } else if (result.isDenied) {
        renderPencairanAkadRetensi();
        paModalCloseConfirmed = true;
        $("#pencairan_akad_modal").modal("hide");
      }
    });
  }
});

function savePencairanAkadTenor(callback) {
  if (paTenorTotalInForm() > paHasilAkadValue() + 0.01) {
    swal("error", "Terjadi kesalahan", "Total termin tidak boleh melebihi dana akad bersih");
    if (typeof callback === "function") callback(false);
    return;
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
        swal("success", r.messages || r.message || "Termin berhasil disimpan");
        loadPencairanAkadData(false);
        if (typeof callback === "function") callback(true);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
        if (typeof callback === "function") callback(false);
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Termin gagal disimpan");
      if (typeof callback === "function") callback(false);
    },
  });
}

function paPengajuanItemCheckboxHtml(item, label) {
  return `
    <div class="custom-control custom-checkbox mb-50">
      <input type="checkbox" class="custom-control-input" id="pa-pengajuan-item-${item.id}" name="items[]" value="${item.id}">
      <label class="custom-control-label" for="pa-pengajuan-item-${item.id}">
        ${label} - Rp ${paMoney(item.sisa)} ${item.catatan ? "(" + paEscape(item.catatan) + ")" : ""}
      </label>
    </div>`;
}

function renderPencairanAkadPengajuanItemPicker() {
  const visibleItems = pencairanAkadState.items.filter(function (item) {
    return parseFloat(item.sisa) > 0.01;
  });

  if (!visibleItems.length) {
    $("#pa-pengajuan-item_here").html('<p class="text-muted">Belum ada item yang bisa diajukan. Simpan plan retensi/termin dulu.</p>');
    return;
  }

  const retensiItems = visibleItems.filter(function (item) { return item.jenis === "retensi"; });
  const terminItems = visibleItems.filter(function (item) { return item.jenis !== "retensi"; });

  const retensiHtml = retensiItems
    .map(function (item) { return paPengajuanItemCheckboxHtml(item, "Retensi - " + paEscape(item.nama_jaminan || "")); })
    .join("") || '<p class="text-muted small mb-0">Tidak ada item retensi.</p>';

  const terminHtml = terminItems
    .map(function (item) { return paPengajuanItemCheckboxHtml(item, "Termin #" + item.urutan_tenor); })
    .join("") || '<p class="text-muted small mb-0">Tidak ada item termin.</p>';

  $("#pa-pengajuan-item_here").html(`
    <div class="divider divider-left mb-50">
      <div class="divider-text">Retensi</div>
    </div>
    ${retensiHtml}
    <div class="divider divider-left mb-50 mt-1">
      <div class="divider-text">Termin Pembayaran</div>
    </div>
    ${terminHtml}
  `);
}

$("#pa-btn-show-pengajuan").on("click", function () {
  $("#pa-pencairan-form-wrap").hide();
  $("#pa-pengajuan-form-wrap").show();
});

$("#pa-btn-show-pencairan").on("click", function () {
  $("#pa-pengajuan-form-wrap").hide();
  $("#pa-pencairan-form-wrap").show();
});

$(document).on("click", ".pa-form-close", function () {
  $($(this).data("target")).hide();
});

$(document).on("change", "#form-pencairan-akad-pengajuan [name=tanggal_pengajuan]", function () {
  const val = $(this).val();
  if (!val) return;
  const d = new Date(val + "T00:00:00");
  d.setDate(d.getDate() + 7);
  $("#form-pencairan-akad-pengajuan [name=tanggal_rencana_cair]").val(d.toISOString().slice(0, 10));
});

function paLampiranPreview(file) {
  const $preview = $("#pa-lampiran-preview");
  const $placeholder = $("#pa-dz-lampiran .dz-placeholder");
  if (!file) {
    $preview.html("").hide();
    $placeholder.show();
    return;
  }
  $preview.html(`
    <div class="p-2 border rounded bg-light text-center">
      <i class="fas fa-file-${file.type.startsWith("image/") ? "image" : "pdf"} fa-2x text-primary"></i>
      <div class="text-truncate small">${paEscape(file.name)}</div>
    </div>`).show();
  $placeholder.hide();
}

$(document).on("change", "#pa-lampiran-input", function () {
  paLampiranPreview(this.files && this.files[0]);
});

document.addEventListener("paste", function (e) {
  if (!$("#pencairan_akad_modal").hasClass("show")) return;

  const clipboard = e.clipboardData || (e.originalEvent && e.originalEvent.clipboardData);
  const item = clipboard && clipboard.items
    && Array.from(clipboard.items).find(function (i) { return i.kind === "file" && i.type.startsWith("image/"); });
  if (!item) return;

  e.preventDefault();
  const file = item.getAsFile();
  const input = document.getElementById("pa-lampiran-input");
  const dt = new DataTransfer();
  dt.items.add(new File([file], `lampiran-${Date.now()}.png`, { type: file.type }));
  input.files = dt.files;
  input.dispatchEvent(new Event("change", { bubbles: true }));
});

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
        paLampiranPreview(null);
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
    const action = row.status === "void"
      ? '<span class="text-muted">-</span>'
      : `<button type="button" class="btn btn-outline-info btn-sm mr-25 mb-25" title="Riwayat Pencairan" onclick="showPencairanList(${row.id})"><i class="fas fa-list"></i></button>`
        + `<button type="button" class="btn btn-outline-danger btn-sm mb-25" title="Void Pengajuan" onclick="voidPencairanAkad(${row.id})" ${parseFloat(row.total_cair) > 0 ? "disabled" : ""}><i class="fas fa-ban"></i></button>`;
    const itemList = (row.details || [])
      .map(function (d) {
        const label = d.jenis === "retensi" ? "Retensi - " + paEscape(d.nama_jaminan || "") : "Termin #" + d.urutan_tenor;
        const catatan = d.item_catatan ? " (" + paEscape(d.item_catatan) + ")" : "";
        return `${label}: Rp ${paMoney(d.nominal_pengajuan)}${catatan}`;
      })
      .join("<br>") || "-";

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${row.tanggal_pengajuan ? format_date(row.tanggal_pengajuan) : "-"}</td>
      <td>${itemList}</td>
      <td class="text-right">Rp ${paMoney(row.total_pengajuan)}</td>
      <td class="text-right">Rp ${paMoney(row.total_cair)}</td>
      <td>${paStatusBadge(row.status)}</td>
      <td>${paEscape(row.add_by_name || "-")}</td>
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

function showPencairanList(idPengajuan) {
  $.ajax({
    url: base_url + "keuangan/pencairan-akad/payment/list",
    type: "post",
    data: { [csrfName]: csrfHash, id_pengajuan: idPengajuan },
    dataType: "json",
    success: function (r) {
      if (r.token) csrfHash = r.token;
      if (r.success) {
        if (!r.payments || !r.payments.length) {
          swal("info", "Tidak ada", "Belum ada pencairan untuk pengajuan ini.");
          return;
        }
        let html = '<table class="table table-sm table-bordered text-left" style="font-size:12px;">';
        html += '<thead><tr><th>Tgl Cair</th><th>Nominal</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>';
        r.payments.forEach(p => {
          html += `<tr>
            <td>${format_date(p.tanggal_cair)}</td>
            <td class="text-right">Rp ${paMoney(p.total_cair)}</td>
            <td>${paEscape(p.catatan)}</td>
            <td><button class="btn btn-sm btn-danger py-0 px-1" onclick="voidPayment(${p.id})"><i class="fas fa-ban"></i> Void</button></td>
          </tr>`;
        });
        html += '</tbody></table>';

        Swal.fire({
          title: 'Riwayat Pencairan',
          html: html,
          width: '600px',
          showCloseButton: true,
          showConfirmButton: false
        });
      }
    }
  });
}

function voidPayment(idPayment) {
  Swal.fire({
    title: "Batalkan pencairan ini?",
    text: "Jurnal keuangan akan di-rollback. Masukkan alasan:",
    input: 'text',
    type: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Void",
    cancelButtonText: "Batal",
    inputValidator: (value) => {
      if (!value) {
        return 'Alasan harus diisi!'
      }
    }
  }).then(function (result) {
    if (!result.value) return;

    $.ajax({
      url: base_url + "keuangan/pencairan-akad/payment/void",
      type: "post",
      data: {
        [csrfName]: csrfHash,
        id_payment: idPayment,
        reason: result.value,
      },
      dataType: "json",
      success: function (r) {
        if (r.token) csrfHash = r.token;
        if (r.success === true) {
          swal("success", "Sukses", "Pencairan berhasil dibatalkan");
          Swal.close();
          loadPencairanAkadData(false);
        } else {
          swal("error", "Gagal", r.messages || r.message || "Terjadi kesalahan");
        }
      }
    });
  });
}

function paPengajuanItemSummaryText(row) {
  return (row.details || [])
    .map(function (d) {
      return d.jenis === "retensi" ? "Retensi - " + (d.nama_jaminan || "") : "Termin #" + d.urutan_tenor;
    })
    .join(", ");
}

function renderPencairanAkadCairSelect() {
  let options = '<option value="">Pilih pengajuan</option>';
  pencairanAkadState.pengajuan
    .filter(function (row) { return row.status === "active" || row.status === "partial"; })
    .forEach(function (row) {
      const itemSummary = paPengajuanItemSummaryText(row);
      const itemText = itemSummary ? " — " + itemSummary : "";
      options += `<option value="${row.id}">#${row.id} - ${row.tanggal_pengajuan} - Rp ${paMoney(row.total_pengajuan)} (${row.status})${itemText}</option>`;
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
    const label = d.jenis === "retensi" ? "Retensi - " + paEscape(d.nama_jaminan || "") : "Termin #" + d.urutan_tenor;
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
