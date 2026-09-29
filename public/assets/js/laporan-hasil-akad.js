(function ($) {
  "use strict";

  var config = window.HASIL_AKAD_REPORT || {};
  var detailTable = null;
  var reportChart = null;
  var lastSummary = null;
  var comparisonEnabled = false;
  var categories = [
    { key: "penjualan", value: "total_penjualan", label: "Total Penjualan" },
    { key: "acc_kpr", value: "total_acc_kpr", label: "Total ACC KPR" },
    { key: "pengajuan", value: "total_pengajuan", label: "Total Pengajuan" },
    { key: "cair", value: "total_cair", label: "Total Cair" },
  ];
  var colors = [["#2057a3", "#3b82c4", "#6ca8d7", "#9cc5e6"], ["#1f7a8c", "#3595a8", "#68b4c0", "#9bced5"]];

  function csrfData() { var data = {}; if (config.csrfName) data[config.csrfName] = config.csrfHash; return data; }
  function updateToken(response) { if (response && response.token) config.csrfHash = response.token; }
  function escapeHtml(value) { return $("<div>").text(String(value == null ? "" : value)).html(); }
  function money(value) { return "Rp " + new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(value || 0)); }
  function formatDate(value) { var parts = String(value || "").split(" ")[0].split("-"); return parts.length === 3 && parts[0] !== "0000" ? parts[2] + "-" + parts[1] + "-" + parts[0] : "-"; }
  function shortMoney(value) { var n = Number(value || 0); return Math.abs(n) >= 1000000000 ? "Rp " + (n / 1000000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " M" : Math.abs(n) >= 1000000 ? "Rp " + (n / 1000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " Jt" : money(n); }
  function multiline(value) {
    return String(value || "-").split(" | ").map(function (line) {
      return escapeHtml(line).replace(/Rp ([0-9,.]+)/g, function (match, amount) { return money(Number(String(amount).replace(/[,.]/g, ""))); });
    }).join("<br>");
  }
  function actorDate(value, label, username) { return '<div class="hasil-akad-detail-date">' + escapeHtml(formatDate(value)) + '<small>' + escapeHtml(label) + ': ' + escapeHtml(username || "-") + "</small></div>"; }
  function paymentStatus(status, dates) {
    var labels = { active: "Belum cair", partial: "Cair sebagian", paid: "Sudah cair" };
    var label = labels[status] || "-";
    var formattedDates = String(dates || "").split(", ").filter(Boolean).map(formatDate);
    var dateText = formattedDates.length ? '<small>Tanggal cair: ' + escapeHtml(formattedDates.join(", ")) + "</small>" : "";
    return '<div class="hasil-akad-payment-status"><span class="badge badge-' + (status === "paid" ? "success" : status === "partial" ? "warning" : "secondary") + '">' + escapeHtml(label) + "</span>" + dateText + "</div>";
  }

  function showError(message) { $("#hasil_akad_error").text(message || "Terjadi kesalahan saat memuat laporan.").prop("hidden", false); }
  function clearError() { $("#hasil_akad_error").prop("hidden", true).empty(); }
  function showDetailError(message) { $("#hasil_akad_detail_error").text(message || "Detail Hasil Akad gagal dimuat.").prop("hidden", false); }
  function clearDetailError() { $("#hasil_akad_detail_error").prop("hidden", true).empty(); }

  function detailButton(value, year, month, category, label, saleCount) {
    var count = saleCount == null ? "" : '<small class="hasil-akad-kavling-count">' + Number(saleCount || 0) + " kavling</small>";
    return '<button type="button" class="hasil-akad-value-button" data-year="' + year + '" data-month="' + month + '" data-category="' + category + '" data-label="' + escapeHtml(label) + '">' + money(value) + count + "</button>";
  }

  function renderHeader(years) {
    var first = ['<th rowspan="2" class="hasil-akad-month-column">Bulan</th>'];
    var second = [];
    years.forEach(function (year, index) {
      var suffix = index === 0 ? "a" : "b";
      first.push('<th colspan="4" class="text-center hasil-akad-year-heading hasil-akad-year-heading-' + suffix + '">' + escapeHtml(year) + "</th>");
      categories.forEach(function (category) { second.push('<th class="hasil-akad-year-subheading-' + suffix + '">' + category.label + "</th>"); });
    });
    $("#hasil_akad_matrix_head").html("<tr>" + first.join("") + "</tr><tr>" + second.join("") + "</tr>");
    $("#hasil_akad_matrix").toggleClass("is-comparison", years.length > 1);
  }

  function summaryValues(month, year) { return month.values[String(year)] || month.values[year] || {}; }

  function renderSummary(data) {
    var years = data.years || [];
    if (years.length < 1 || years.length > 2) { showError("Data tahun laporan tidak lengkap."); return; }
    lastSummary = data;
    renderHeader(years);
    var rows = (data.months || []).map(function (month) {
      var cells = ["<td>" + escapeHtml(month.month_label) + "</td>"];
      years.forEach(function (year) {
        var amounts = summaryValues(month, year);
        cells.push("<td>" + detailButton(amounts.total_penjualan, year, month.month, "penjualan", "Total Penjualan", amounts.jumlah_kavling) + "</td>");
        cells.push("<td>" + detailButton(amounts.total_acc_kpr, year, month.month, "acc_kpr", "Total ACC KPR") + "</td>");
        cells.push("<td>" + detailButton(amounts.total_pengajuan, year, month.month, "pengajuan", "Total Pengajuan Pencairan") + "</td>");
        cells.push("<td>" + detailButton(amounts.total_cair, year, month.month, "cair", "Total Cair Hasil Akad") + "</td>");
      });
      return "<tr>" + cells.join("") + "</tr>";
    });
    var footer = ["<td>Total Tahun</td>"];
    years.forEach(function (year) {
      var totals = data.totals[String(year)] || data.totals[year] || {};
      footer.push("<td>" + money(totals.total_penjualan) + '<small class="hasil-akad-kavling-count">' + Number(totals.jumlah_kavling || 0) + " kavling</small></td>");
      footer.push("<td>" + money(totals.total_acc_kpr) + "</td>");
      footer.push("<td>" + money(totals.total_pengajuan) + "</td>");
      footer.push("<td>" + money(totals.total_cair) + "</td>");
    });
    $("#hasil_akad_matrix_body").html(rows.join(""));
    $("#hasil_akad_matrix_foot").html("<tr>" + footer.join("") + "</tr>");
    $("#hasil_akad_toggle_chart").prop("disabled", false);
    if (!$("#hasil_akad_chart_card").prop("hidden")) renderChart(data);
  }

  function chartDatasets(data) {
    var result = [];
    (data.years || []).forEach(function (year, yearIndex) {
      categories.forEach(function (category, categoryIndex) {
        result.push({ label: category.label + " " + year, backgroundColor: colors[yearIndex][categoryIndex], borderColor: colors[yearIndex][categoryIndex], borderWidth: 1, data: (data.months || []).map(function (month) { return Number(summaryValues(month, year)[category.value] || 0); }) });
      });
    });
    return result;
  }

  function renderChart(data) {
    var canvas = document.getElementById("hasil_akad_chart");
    if (!canvas || typeof Chart === "undefined") return;
    if (reportChart) reportChart.destroy();
    reportChart = new Chart(canvas.getContext("2d"), {
      type: "bar",
      data: { labels: (data.months || []).map(function (month) { return month.month_label; }), datasets: chartDatasets(data) },
      options: {
        responsive: true, maintainAspectRatio: false,
        legend: { position: "bottom", labels: { boxWidth: 12, usePointStyle: true } },
        tooltips: { mode: "index", intersect: false, callbacks: { label: function (item, chart) { return (chart.datasets[item.datasetIndex].label || "") + ": " + money(item.yLabel); } } },
        scales: { xAxes: [{ stacked: false, gridLines: { display: false } }], yAxes: [{ stacked: false, ticks: { beginAtZero: true, callback: function (value) { return shortMoney(value); } } }] },
      },
    });
  }

  function setComparison(enabled) {
    comparisonEnabled = enabled;
    $("#hasil_akad_comparison_controls").prop("hidden", !enabled);
    $("#hasil_akad_add_comparison").prop("hidden", enabled);
    if (enabled && $("#hasil_akad_year_a").val() === $("#hasil_akad_year_b").val()) {
      var other = $("#hasil_akad_year_b option").filter(function () { return this.value !== $("#hasil_akad_year_a").val(); }).first().val();
      if (other) $("#hasil_akad_year_b").val(other);
    }
  }

  function loadSummary() {
    if (!config.hasProject) return;
    clearError();
    var yearA = Number($("#hasil_akad_year_a").val());
    var yearB = Number($("#hasil_akad_year_b").val());
    if (comparisonEnabled && yearA === yearB) { showError("Pilih dua tahun yang berbeda."); return; }
    $("#hasil_akad_loading").prop("hidden", false);
    $("#hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison").prop("disabled", true);
    var payload = csrfData(); payload.year_a = yearA; if (comparisonEnabled) payload.year_b = yearB;
    $.ajax({ url: config.summaryUrl, type: "POST", dataType: "json", data: payload })
      .done(function (response) { updateToken(response); if (!response.success) { showError(response.messages); return; } renderSummary(response.data || {}); })
      .fail(function (xhr) { var response = xhr.responseJSON || {}; updateToken(response); showError(response.messages || "Laporan Hasil Akad gagal dimuat."); })
      .always(function () { $("#hasil_akad_loading").prop("hidden", true); $("#hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison").prop("disabled", false); });
  }

  function toggleChart() {
    if (!lastSummary) return;
    var card = $("#hasil_akad_chart_card"), willShow = card.prop("hidden");
    card.prop("hidden", !willShow);
    $("#hasil_akad_toggle_chart span").text(willShow ? "Sembunyikan Chart" : "Tampilkan Chart");
    if (willShow) { renderChart(lastSummary); card.get(0).scrollIntoView({ behavior: "smooth", block: "nearest" }); }
    else if (reportChart) { reportChart.destroy(); reportChart = null; }
  }

  function openDetail(button) {
    var year = Number(button.data("year")), month = Number(button.data("month")), category = String(button.data("category"));
    var monthLabel = $("#hasil_akad_matrix_body tr").eq(month - 1).find("td:first").text();
    $("#hasil_akad_detail_title").text("Detail " + String(button.data("label")));
    $("#hasil_akad_detail_subtitle").text(monthLabel + " " + year + " · " + config.projectName);
    clearDetailError(); $("#hasil_akad_detail_modal").modal("show");
    if (detailTable) { detailTable.destroy(); $("#hasil_akad_detail_table tbody").empty(); }
    var isPengajuan = category === "pengajuan", isCair = category === "cair";
    var headers = isPengajuan
      ? ["Rincian Pengajuan", "Jalan / No. Kavling", "Nama Konsumen", "Tanggal Pengajuan", "Status Cair", "Nominal", "Aksi"]
      : isCair
        ? ["Rincian Pencairan", "Jalan / No. Kavling", "Nama Konsumen", "Tanggal Pencairan", "Nominal", "Aksi"]
        : ["Jenis Laporan", "Jalan / No. Kavling", "Nama Konsumen", "Tanggal", "Nominal", "Aksi"];
    $("#hasil_akad_detail_table_head").html("<tr>" + headers.map(function (header) { return "<th>" + escapeHtml(header) + "</th>"; }).join("") + "</tr>");
    var columns = [
      { data: "jenis_laporan", name: "jenis_laporan", render: function (data) { return (isPengajuan || isCair) ? multiline(data) : escapeHtml(data); } },
      { data: "alamat_kavling", name: "alamat_kavling", render: escapeHtml },
      { data: "nama_konsumen", name: "nama_konsumen", render: escapeHtml },
      { data: "tanggal_transaksi", name: "tanggal_transaksi", render: function (data, type, row) { if (type !== "display") return data; if (isPengajuan) return actorDate(data, "Diajukan oleh", row.pengaju_username); if (isCair) return actorDate(data, "Dicairkan oleh", row.pencair_username); return formatDate(data); } },
    ];
    if (isPengajuan) columns.push({ data: "status_pengajuan", name: "status_pengajuan", orderable: false, render: function (data, type, row) { return type === "display" ? paymentStatus(data, row.tanggal_pencairan) : data; } });
    columns.push(
      { data: "nominal", name: "nominal", className: "text-right font-weight-bold", render: money },
      { data: null, orderable: false, searchable: false, className: "text-center", render: function (data, type, row) { if (!row.id_kavling) return "-"; return '<button type="button" class="btn btn-info btn-sm hasil-akad-view-kavling" data-id-kavling="' + escapeHtml(row.id_kavling) + '" data-id-mkdt="' + escapeHtml(row.id_mkdt || "") + '" data-nama-jalan="' + escapeHtml(row.nama_jalan || "") + '" data-no-kavling="' + escapeHtml(row.no_kavling || "") + '" title="Lihat Detail Kavling"><i class="fa fa-eye"></i></button>'; } }
    );
    detailTable = $("#hasil_akad_detail_table").DataTable({
      processing: true, serverSide: true, searching: true, lengthChange: true, pageLength: 10, order: [[3, "asc"]], scrollX: true, autoWidth: false,
      ajax: { url: config.detailUrl, type: "POST", data: function (request) { request.year = year; request.month = month; request.category = category; if (config.csrfName) request[config.csrfName] = config.csrfHash; }, dataSrc: function (response) { updateToken(response); return response.data || []; }, error: function (xhr) { var response = xhr.responseJSON || {}; updateToken(response); $("#hasil_akad_detail_table_processing").hide(); showDetailError(response.messages || "Detail Hasil Akad gagal dimuat."); } },
      columns: columns,
      language: { emptyTable: "Tidak ada transaksi pada periode ini.", info: "Menampilkan _START_–_END_ dari _TOTAL_ transaksi", infoEmpty: "Tidak ada transaksi", lengthMenu: "Tampilkan _MENU_", processing: "Memuat detail...", search: "Cari:", zeroRecords: "Transaksi tidak ditemukan.", paginate: { previous: "Sebelumnya", next: "Berikutnya" } },
    });
  }

  $(function () {
    setComparison(false);
    $("#hasil_akad_apply").on("click", loadSummary);
    $("#hasil_akad_add_comparison").on("click", function () { setComparison(true); });
    $("#hasil_akad_remove_comparison").on("click", function () { setComparison(false); loadSummary(); });
    $("#hasil_akad_toggle_chart").on("click", toggleChart);
    $("#hasil_akad_matrix_body").on("click", ".hasil-akad-value-button", function () { openDetail($(this)); });
    $("#hasil_akad_detail_modal").on("hidden.bs.modal", function () { if (detailTable) { detailTable.destroy(); detailTable = null; $("#hasil_akad_detail_table tbody").empty(); } });
    $(document).on("click", ".hasil-akad-view-kavling", function () {
      var button = $(this), idKavling = button.data("id-kavling"); if (!idKavling) return;
      var sh = { id: "kav" + idKavling, data: { tipe: "kavling", id_kavling: idKavling, id_mkdt: button.data("id-mkdt") || null, id_keuangan: null, id_legal: null, id_produksi: null, nama_jalan: button.data("nama-jalan") || "", no_kavling: button.data("no-kavling") || "" }, data2: { harga_akhir: "-", id_hargajual: "", id_komplain: null, no_tipe_rumah: "", tipe_rumah: "", harga_akhir_tgl: "", harga_akhir_oleh: "" } };
      window.editdtt = [sh]; if (typeof detail_kavling === "function") detail_kavling(sh, idKavling);
    });
    if (config.hasProject) loadSummary();
    else $("#hasil_akad_year_a, #hasil_akad_year_b, #hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison, #hasil_akad_toggle_chart").prop("disabled", true);
  });
})(jQuery);
