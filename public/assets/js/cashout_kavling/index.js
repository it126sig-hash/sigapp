function buildKavlingShapeFromPayload(payload) {
  return {
    id: "kav" + payload.id_kavling,
    data: {
      tipe: "kavling",
      id_kavling: payload.id_kavling,
      id_mkdt: payload.id_mkdt || null,
      nama_jalan: payload.nama_jalan || "",
      no_kavling: payload.no_kavling || "",
      status_mkdt: payload.status_mkdt || "",
      nama_proyek: dt_proyek.nama_proyek || "",
    },
    data2: {
      no_tipe_rumah: payload.no_tipe_rumah || "",
      tipe_rumah: payload.tipe_rumah || "",
      status_mkdt: payload.status_mkdt || "",
    },
  };
}

function decodeCkPayload(el) {
  try {
    return JSON.parse($(el).attr("data-payload"));
  } catch (error) {
    return null;
  }
}

$(document).ready(function () {
  const table = $("#cashout-kavling-table").DataTable({
    processing: true,
    serverSide: true,
    searching: true,
    ajax: {
      url: base_url + "cashout/kavling/list",
      type: "POST",
      data: function (d) {
        d[csrfName] = csrfHash;
        d.id_proyek = dt_proyek.id_proyek;
      },
      dataSrc: function (json) {
        csrfHash = json.token;
        return json.data;
      },
    },
    columns: [
      { orderable: false, searchable: false },
      { orderable: false, searchable: false },
      { orderable: false },
      { orderable: false },
      { orderable: false, searchable: false, className: "text-right" },
      { orderable: false, searchable: false, className: "text-right" },
      { orderable: false, searchable: false, className: "text-right" },
      { orderable: false, searchable: false, className: "text-right" },
      { orderable: false, searchable: false, className: "text-right" },
    ],
  });

  $("#cashout-kavling-table tbody").on("click", ".btn-ck-detail", function () {
    const tr = $(this).closest("tr");
    const row = table.row(tr);
    const idKavling = $(this).data("id-kavling");

    if (row.child.isShown()) {
      row.child.hide();
      $(this).find("i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
      return;
    }

    row.child('<div class="ck-detail-wrap"><div class="text-center text-muted py-2"><i class="fa fa-spinner fa-spin mr-1"></i> Memuat riwayat...</div></div>').show();
    $(this).find("i").removeClass("fa-chevron-down").addClass("fa-chevron-up");

    $.ajax({
      url: base_url + "cashout/kavling/detail-list",
      type: "post",
      data: {
        [csrfName]: csrfHash,
        id_kavling: idKavling,
      },
      dataType: "json",
      success: function (r) {
        csrfHash = r.token;
        const items = (r.data || []);
        if (items.length === 0) {
          row.child('<div class="ck-detail-wrap"><div class="ck-detail-empty">Belum ada transaksi cashout untuk kavling ini</div></div>').show();
          return;
        }

        let html = '<div class="ck-detail-wrap"><table class="table table-sm ck-detail-table"><thead><tr><th>Tanggal</th><th>Departemen</th><th>Item</th><th class="text-right">Nominal</th><th>Keterangan</th></tr></thead><tbody>';
        items.forEach(function (item) {
          html += "<tr><td>" + (item.tanggal || "-") + "</td><td><span class='badge badge-light-primary'>" + item.departemen + "</span></td><td>" + (item.item || "-") + "</td><td class='text-right'>" + Number(item.nominal || 0).toLocaleString("id-ID") + "</td><td>" + (item.keterangan || "-") + "</td></tr>";
        });
        html += "</tbody></table></div>";
        row.child(html).show();
      },
    });
  });

  $("#cashout-kavling-table tbody").on("click", ".ck-open-cashout", function () {
    const payload = decodeCkPayload(this);
    if (!payload) return;
    window.editdtt = [buildKavlingShapeFromPayload(payload)];
    isi_cashout(payload.id_kavling);
  });

  $("#cashout-kavling-table tbody").on("click", ".ck-open-produksi", function () {
    const payload = decodeCkPayload(this);
    if (!payload) return;
    window.editdtt = [buildKavlingShapeFromPayload(payload)];
    isi_pembayaran(payload.id_kavling);
  });

  $("#cashout-kavling-table tbody").on("click", ".ck-open-pajak", function () {
    const payload = decodeCkPayload(this);
    if (!payload) return;
    const sh = buildKavlingShapeFromPayload(payload);
    window.editdtt = [sh];
    open_pajak(sh, 10, payload.id_kavling);
  });
});
