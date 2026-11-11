(function ($) {
  "use strict";

  var config = window.HASIL_AKAD_REPORT || {};
  var detailTable = null;
  var reportChart = null;
  var lastSummary = null;
  var comparisonEnabled = false;
  var categories = [
    { key: "akad", value: "total_hasil_akad", label: "Total Hasil Akad" },
    { key: "pengajuan", value: "total_pengajuan", label: "Total Pengajuan" },
    { key: "cair", value: "total_cair", label: "Total Cair" },
  ];
  var colors = [
    ["#2057a3", "#3b82c4", "#6ca8d7"],
    ["#1f7a8c", "#3595a8", "#68b4c0"],
  ];

  function csrfData() {
    var data = {};
    if (config.csrfName) data[config.csrfName] = config.csrfHash;
    return data;
  }

  function updateToken(response) {
    if (response && response.token) config.csrfHash = response.token;
  }

  function escapeHtml(value) {
    return $("<div>").text(String(value == null ? "" : value)).html();
  }

  function money(value) {
    return "Rp " + new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(value || 0));
  }

  function formatDate(value) {
    var parts = String(value || "").split(" ")[0].split("-");
    return parts.length === 3 && parts[0] !== "0000" ? parts[2] + "-" + parts[1] + "-" + parts[0] : "-";
  }

  function shortMoney(value) {
    var n = Number(value || 0);
    return Math.abs(n) >= 1000000000
      ? "Rp " + (n / 1000000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " M"
      : Math.abs(n) >= 1000000
        ? "Rp " + (n / 1000000).toLocaleString("id-ID", { maximumFractionDigits: 1 }) + " Jt"
        : money(n);
  }

  function multiline(value) {
    return String(value || "-")
      .split(" | ")
      .map(function (line) {
        return escapeHtml(line).replace(/Rp ([0-9,.]+)/g, function (match, amount) {
          return money(Number(String(amount).replace(/[,.]/g, "")));
        });
      })
      .join("<br>");
  }

  function renderSudahCair(sudahCair, totalAkad) {
    var cair = Number(sudahCair || 0);
    var akad = Number(totalAkad || 0);
    var pct = akad > 0 ? (cair / akad) * 100 : 0;
    var pctText = new Intl.NumberFormat("id-ID", { maximumFractionDigits: 1 }).format(pct) + "%";
    var badgeClass = pct >= 100 ? "badge-light-success" : pct > 0 ? "badge-light-primary" : "badge-light-secondary";
    return (
      '<div class="hasil-akad-cair-cell text-right">' +
      '<span class="text-success font-weight-bold d-block">' + money(cair) + "</span>" +
      '<span class="badge badge-pill ' + badgeClass + ' hasil-akad-cair-pct mt-25">' + pctText + "</span>" +
      "</div>"
    );
  }

  function renderKonsumenKavling(row) {
    var nama = escapeHtml(row.nama_konsumen || "-");
    var jalan = String(row.nama_jalan || "").trim();
    var no = String(row.no_kavling || "").trim();
    var alamat = "";
    if (jalan || (no && no !== "-")) {
      alamat = (jalan ? jalan + " " : "") + (no && no !== "-" ? "No. " + no : "");
    } else if (row.alamat_kavling) {
      alamat = row.alamat_kavling;
    } else {
      alamat = "-";
    }

    var tipe = escapeHtml(row.tipe_rumah || "-");
    var isSubsidi = Number(row.is_subsidi) === 1;
    var badgeClass = isSubsidi ? "badge-light-success" : "badge-light-primary";
    var badgeText = isSubsidi ? "Subsidi" : "Komersil";

    return (
      '<div class="hasil-akad-consumer-cell text-left">' +
      '<span class="font-weight-bold d-block text-dark">' + nama + "</span>" +
      '<small class="text-muted"><i class="fas fa-map-marker-alt mr-25"></i>' + escapeHtml(alamat) + "</small>" +
      '<div class="hasil-akad-tipe-inline d-flex align-items-center mt-25">' +
      '<small class="font-weight-600 text-secondary mr-50">' + tipe + "</small>" +
      '<span class="badge badge-pill ' + badgeClass + '">' + badgeText + "</span>" +
      "</div>" +
      "</div>"
    );
  }

  function renderStatusRincian(category, row) {
    var rincianHtml = multiline(row.jenis_laporan);
    var badgeHtml = "";
    var dateHtml = "";

    if (category === "pengajuan") {
      var status = String(row.status_pengajuan || "").toLowerCase();
      var statusConfig = {
        paid: { label: "Sudah cair", badgeClass: "badge-light-success" },
        partial: { label: "Cair sebagian", badgeClass: "badge-light-warning" },
        active: { label: "Belum cair", badgeClass: "badge-light-secondary" },
      };
      var cfg = statusConfig[status] || { label: status || "Belum cair", badgeClass: "badge-light-secondary" };
      badgeHtml = '<span class="badge badge-pill ' + cfg.badgeClass + '">' + escapeHtml(cfg.label) + "</span>";

      var dates = String(row.tanggal_cair || row.tanggal_pencairan || "")
        .split(", ")
        .filter(Boolean)
        .map(formatDate);
      if (dates.length > 0) {
        dateHtml = '<small class="text-muted"><i class="far fa-calendar-check mr-25"></i>' + escapeHtml(dates.join(", ")) + "</small>";
      }
    } else if (category === "cair") {
      badgeHtml = '<span class="badge badge-pill badge-light-success">Sudah cair</span>';
      var tglCair = row.tanggal_cair || row.tanggal_pencairan || row.tanggal_transaksi;
      var dateParts = [];
      if (tglCair) {
        dateParts.push('<i class="far fa-calendar-check mr-25"></i>' + escapeHtml(formatDate(tglCair)));
      }
      if (row.pencair_username && row.pencair_username !== "-") {
        dateParts.push('<i class="far fa-user mr-25 ml-25"></i>' + escapeHtml(row.pencair_username));
      }
      if (dateParts.length > 0) {
        dateHtml = '<small class="text-muted">' + dateParts.join(" ") + "</small>";
      }
    }

    return (
      '<div class="hasil-akad-rincian-status text-left">' +
      '<div class="hasil-akad-rincian-text mb-25">' + rincianHtml + "</div>" +
      '<div class="hasil-akad-status-row d-flex justify-content-between align-items-center mt-25">' +
      '<div class="hasil-akad-status-badge">' + badgeHtml + "</div>" +
      '<div class="hasil-akad-status-date text-right">' + dateHtml + "</div>" +
      "</div>" +
      "</div>"
    );
  }

  function renderActionButtons(row) {
    if (!row.id_kavling) return "-";
    var btnDetail =
      '<button type="button" class="btn btn-info btn-sm hasil-akad-view-kavling" data-id-kavling="' +
      escapeHtml(row.id_kavling) +
      '" data-id-mkdt="' +
      escapeHtml(row.id_mkdt || "") +
      '" data-nama-jalan="' +
      escapeHtml(row.nama_jalan || "") +
      '" data-no-kavling="' +
      escapeHtml(row.no_kavling || "") +
      '" title="Lihat Detail Kavling"><i class="fa fa-eye"></i></button>';

    var btnKelola = "";
    if (config.canManagePencairan && row.id_mkdt) {
      btnKelola =
        '<button type="button" class="btn btn-primary btn-sm ml-50 hasil-akad-manage-pencairan" data-id-kavling="' +
        escapeHtml(row.id_kavling) +
        '" data-id-mkdt="' +
        escapeHtml(row.id_mkdt || "") +
        '" data-nama-jalan="' +
        escapeHtml(row.nama_jalan || "") +
        '" data-no-kavling="' +
        escapeHtml(row.no_kavling || "") +
        '" title="Kelola Pencairan Akad"><i class="fas fa-hand-holding-usd mr-25"></i> Kelola</button>';
    }

    return '<div class="d-inline-flex align-items-center justify-content-center">' + btnDetail + btnKelola + "</div>";
  }

  function showError(message) {
    $("#hasil_akad_error").text(message || "Terjadi kesalahan saat memuat laporan.").prop("hidden", false);
  }

  function clearError() {
    $("#hasil_akad_error").prop("hidden", true).empty();
  }

  function showDetailError(message) {
    $("#hasil_akad_detail_error").text(message || "Detail Hasil Akad gagal dimuat.").prop("hidden", false);
  }

  function clearDetailError() {
    $("#hasil_akad_detail_error").prop("hidden", true).empty();
  }

  function detailButton(value, year, month, category, label, saleCount) {
    var count = saleCount == null ? "" : '<small class="hasil-akad-kavling-count">' + Number(saleCount || 0) + " kavling</small>";
    return (
      '<button type="button" class="hasil-akad-value-button" data-year="' +
      year +
      '" data-month="' +
      month +
      '" data-category="' +
      category +
      '" data-label="' +
      escapeHtml(label) +
      '">' +
      money(value) +
      count +
      "</button>"
    );
  }

  function renderHeader(years) {
    var first = ['<th rowspan="2" class="hasil-akad-month-column">Bulan</th>'];
    var second = [];
    years.forEach(function (year, index) {
      var suffix = index === 0 ? "a" : "b";
      first.push(
        '<th colspan="3" class="text-center hasil-akad-year-heading hasil-akad-year-heading-' +
          suffix +
          '">' +
          escapeHtml(year) +
          "</th>"
      );
      categories.forEach(function (category) {
        second.push('<th class="hasil-akad-year-subheading-' + suffix + '">' + category.label + "</th>");
      });
    });
    $("#hasil_akad_matrix_head").html("<tr>" + first.join("") + "</tr><tr>" + second.join("") + "</tr>");
    $("#hasil_akad_matrix").toggleClass("is-comparison", years.length > 1);
  }

  function summaryValues(month, year) {
    return month.values[String(year)] || month.values[year] || {};
  }

  function renderSummary(data) {
    var years = data.years || [];
    if (years.length < 1 || years.length > 2) {
      showError("Data tahun laporan tidak lengkap.");
      return;
    }
    lastSummary = data;
    renderHeader(years);
    var rows = (data.months || []).map(function (month) {
      var cells = ["<td>" + escapeHtml(month.month_label) + "</td>"];
      years.forEach(function (year) {
        var amounts = summaryValues(month, year);
        var totalHasilAkad = amounts.total_hasil_akad != null ? amounts.total_hasil_akad : (amounts.total_penjualan || 0);
        cells.push("<td>" + detailButton(totalHasilAkad, year, month.month, "akad", "Total Hasil Akad", amounts.jumlah_kavling) + "</td>");
        cells.push("<td>" + detailButton(amounts.total_pengajuan, year, month.month, "pengajuan", "Total Pengajuan") + "</td>");
        cells.push("<td>" + detailButton(amounts.total_cair, year, month.month, "cair", "Total Cair") + "</td>");
      });
      return "<tr>" + cells.join("") + "</tr>";
    });
    var footer = ["<td>Total Tahun</td>"];
    years.forEach(function (year) {
      var totals = data.totals[String(year)] || data.totals[year] || {};
      var totalHasilAkad = totals.total_hasil_akad != null ? totals.total_hasil_akad : (totals.total_penjualan || 0);
      footer.push("<td>" + money(totalHasilAkad) + '<small class="hasil-akad-kavling-count">' + Number(totals.jumlah_kavling || 0) + " kavling</small></td>");
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
        result.push({
          label: category.label + " " + year,
          backgroundColor: colors[yearIndex][categoryIndex],
          borderColor: colors[yearIndex][categoryIndex],
          borderWidth: 1,
          data: (data.months || []).map(function (month) {
            return Number(summaryValues(month, year)[category.value] || 0);
          }),
        });
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
      data: {
        labels: (data.months || []).map(function (month) {
          return month.month_label;
        }),
        datasets: chartDatasets(data),
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { position: "bottom", labels: { boxWidth: 12, usePointStyle: true } },
        tooltips: {
          mode: "index",
          intersect: false,
          callbacks: {
            label: function (item, chart) {
              return (chart.datasets[item.datasetIndex].label || "") + ": " + money(item.yLabel);
            },
          },
        },
        scales: {
          xAxes: [{ stacked: false, gridLines: { display: false } }],
          yAxes: [
            {
              stacked: false,
              ticks: {
                beginAtZero: true,
                callback: function (value) {
                  return shortMoney(value);
                },
              },
            },
          ],
        },
      },
    });
  }

  function setComparison(enabled) {
    comparisonEnabled = enabled;
    $("#hasil_akad_comparison_controls").prop("hidden", !enabled);
    $("#hasil_akad_add_comparison").prop("hidden", enabled);
    if (enabled && $("#hasil_akad_year_a").val() === $("#hasil_akad_year_b").val()) {
      var other = $("#hasil_akad_year_b option")
        .filter(function () {
          return this.value !== $("#hasil_akad_year_a").val();
        })
        .first()
        .val();
      if (other) $("#hasil_akad_year_b").val(other);
    }
  }

  function loadSummary() {
    if (!config.hasProject) return;
    clearError();
    var yearA = Number($("#hasil_akad_year_a").val());
    var yearB = Number($("#hasil_akad_year_b").val());
    if (comparisonEnabled && yearA === yearB) {
      showError("Pilih dua tahun yang berbeda.");
      return;
    }
    $("#hasil_akad_loading").prop("hidden", false);
    $("#hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison").prop("disabled", true);
    var payload = csrfData();
    payload.year_a = yearA;
    if (comparisonEnabled) payload.year_b = yearB;
    $.ajax({ url: config.summaryUrl, type: "POST", dataType: "json", data: payload })
      .done(function (response) {
        updateToken(response);
        if (!response.success) {
          showError(response.messages);
          return;
        }
        renderSummary(response.data || {});
      })
      .fail(function (xhr) {
        var response = xhr.responseJSON || {};
        updateToken(response);
        showError(response.messages || "Laporan Hasil Akad gagal dimuat.");
      })
      .always(function () {
        $("#hasil_akad_loading").prop("hidden", true);
        $("#hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison").prop("disabled", false);
      });
  }

  function toggleChart() {
    if (!lastSummary) return;
    var card = $("#hasil_akad_chart_card"),
      willShow = card.prop("hidden");
    card.prop("hidden", !willShow);
    $("#hasil_akad_toggle_chart span").text(willShow ? "Sembunyikan Chart" : "Tampilkan Chart");
    if (willShow) {
      renderChart(lastSummary);
      card.get(0).scrollIntoView({ behavior: "smooth", block: "nearest" });
    } else if (reportChart) {
      reportChart.destroy();
      reportChart = null;
    }
  }

  function openDetail(button) {
    var year = Number(button.data("year"));
    var month = Number(button.data("month"));
    var category = String(button.data("category"));
    var monthLabel = $("#hasil_akad_matrix_body tr").eq(month - 1).find("td:first").text();

    $("#hasil_akad_detail_title").text("Detail " + String(button.data("label")));
    $("#hasil_akad_detail_subtitle").text(monthLabel + " " + year + " · " + config.projectName);
    clearDetailError();
    $("#hasil_akad_detail_modal").modal("show");

    if (detailTable) {
      detailTable.destroy();
      detailTable = null;
    }
    $("#hasil_akad_detail_table").empty().html('<thead id="hasil_akad_detail_table_head"></thead><tbody></tbody>');

    var headers = [];
    var columns = [];
    var defaultOrder = [[2, "desc"]];

    if (category === "akad" || category === "penjualan") {
      headers = [
        "Nama Konsumen / Kavling",
        "Tgl Booking",
        "Tgl Akad",
        "Total Hasil Akad",
        "Total Sudah Cair",
        "Aksi",
      ];
      columns = [
        {
          data: "nama_konsumen",
          name: "nama_konsumen",
          className: "text-left",
          render: function (data, type, row) {
            return type === "display" ? renderKonsumenKavling(row) : data;
          },
        },
        {
          data: "booking_tgl",
          name: "booking_tgl",
          className: "text-center",
          render: function (data, type) {
            return type === "display" ? formatDate(data) : data;
          },
        },
        {
          data: "akad_tgl",
          name: "akad_tgl",
          className: "text-center",
          render: function (data, type) {
            return type === "display" ? formatDate(data) : data;
          },
        },
        {
          data: "total_hasil_akad",
          name: "total_hasil_akad",
          className: "text-right font-weight-bold",
          render: function (data, type, row) {
            var val = data != null ? data : (row.nominal != null ? row.nominal : row.harga_jual);
            return type === "display" ? money(val) : Number(val || 0);
          },
        },
        {
          data: "total_sudah_cair",
          name: "total_sudah_cair",
          className: "text-right",
          render: function (data, type, row) {
            if (type !== "display") return Number(data || 0);
            var totalAkad = row.total_hasil_akad != null ? row.total_hasil_akad : (row.nominal != null ? row.nominal : row.harga_jual);
            return renderSudahCair(data, totalAkad);
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center hasil-akad-actions-column",
          render: function (data, type, row) {
            return renderActionButtons(row);
          },
        },
      ];
      defaultOrder = [[2, "desc"]];
    } else if (category === "pengajuan") {
      headers = [
        "Nama Konsumen / Kavling",
        "Tgl Pengajuan",
        "Status & Rincian",
        "Nominal",
        "Aksi",
      ];
      columns = [
        {
          data: "nama_konsumen",
          name: "nama_konsumen",
          className: "text-left",
          render: function (data, type, row) {
            return type === "display" ? renderKonsumenKavling(row) : data;
          },
        },
        {
          data: "tanggal_pengajuan",
          name: "tanggal_pengajuan",
          className: "text-center",
          render: function (data, type, row) {
            if (type !== "display") return data;
            var text = formatDate(data);
            if (row.pengaju_username && row.pengaju_username !== "-") {
              return '<div class="hasil-akad-detail-date">' + escapeHtml(text) + '<small class="text-muted">Oleh: ' + escapeHtml(row.pengaju_username) + "</small></div>";
            }
            return text;
          },
        },
        {
          data: "jenis_laporan",
          name: "jenis_laporan",
          className: "text-left",
          orderable: false,
          render: function (data, type, row) {
            return type === "display" ? renderStatusRincian("pengajuan", row) : data;
          },
        },
        {
          data: "nominal",
          name: "nominal",
          className: "text-right font-weight-bold",
          render: function (data, type) {
            return type === "display" ? money(data) : data;
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center hasil-akad-actions-column",
          render: function (data, type, row) {
            return renderActionButtons(row);
          },
        },
      ];
      defaultOrder = [[1, "desc"]];
    } else if (category === "cair") {
      headers = [
        "Nama Konsumen / Kavling",
        "Tgl Pengajuan",
        "Status & Rincian",
        "Nominal",
        "Aksi",
      ];
      columns = [
        {
          data: "nama_konsumen",
          name: "nama_konsumen",
          className: "text-left",
          render: function (data, type, row) {
            return type === "display" ? renderKonsumenKavling(row) : data;
          },
        },
        {
          data: "tanggal_pengajuan",
          name: "tanggal_pengajuan",
          className: "text-center",
          render: function (data, type) {
            return type === "display" ? formatDate(data) : data;
          },
        },
        {
          data: "jenis_laporan",
          name: "jenis_laporan",
          className: "text-left",
          orderable: false,
          render: function (data, type, row) {
            return type === "display" ? renderStatusRincian("cair", row) : data;
          },
        },
        {
          data: "nominal",
          name: "nominal",
          className: "text-right font-weight-bold",
          render: function (data, type) {
            return type === "display" ? money(data) : data;
          },
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          className: "text-center hasil-akad-actions-column",
          render: function (data, type, row) {
            return renderActionButtons(row);
          },
        },
      ];
      defaultOrder = [[1, "desc"]];
    }

    $("#hasil_akad_detail_table_head").html(
      "<tr>" +
        headers
          .map(function (header) {
            return "<th>" + escapeHtml(header) + "</th>";
          })
          .join("") +
        "</tr>"
    );

    detailTable = $("#hasil_akad_detail_table").DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: defaultOrder,
      scrollX: true,
      autoWidth: false,
      ajax: {
        url: config.detailUrl,
        type: "POST",
        data: function (request) {
          request.year = year;
          request.month = month;
          request.category = category;
          if (config.csrfName) request[config.csrfName] = config.csrfHash;
        },
        dataSrc: function (response) {
          updateToken(response);
          return response.data || [];
        },
        error: function (xhr) {
          var response = xhr.responseJSON || {};
          updateToken(response);
          $("#hasil_akad_detail_table_processing").hide();
          showDetailError(response.messages || "Detail Hasil Akad gagal dimuat.");
        },
      },
      columns: columns,
      language: {
        emptyTable: "Tidak ada transaksi pada periode ini.",
        info: "Menampilkan _START_–_END_ dari _TOTAL_ transaksi",
        infoEmpty: "Tidak ada transaksi",
        lengthMenu: "Tampilkan _MENU_",
        processing: "Memuat detail...",
        search: "Cari:",
        zeroRecords: "Transaksi tidak ditemukan.",
        paginate: { previous: "Sebelumnya", next: "Berikutnya" },
      },
    });
  }

  $(function () {
    setComparison(false);
    $("#hasil_akad_apply").on("click", loadSummary);
    $("#hasil_akad_add_comparison").on("click", function () {
      setComparison(true);
    });
    $("#hasil_akad_remove_comparison").on("click", function () {
      setComparison(false);
      loadSummary();
    });
    $("#hasil_akad_toggle_chart").on("click", toggleChart);
    $("#hasil_akad_matrix_body").on("click", ".hasil-akad-value-button", function () {
      openDetail($(this));
    });
    $("#hasil_akad_detail_modal").on("hidden.bs.modal", function () {
      if (detailTable) {
        detailTable.destroy();
        detailTable = null;
        $("#hasil_akad_detail_table").empty().html('<thead id="hasil_akad_detail_table_head"></thead><tbody></tbody>');
      }
    });
    $(document).on("click", ".hasil-akad-view-kavling", function () {
      var button = $(this),
        idKavling = button.data("id-kavling");
      if (!idKavling) return;
      var sh = {
        id: "kav" + idKavling,
        data: {
          tipe: "kavling",
          id_kavling: idKavling,
          id_mkdt: button.data("id-mkdt") || null,
          id_keuangan: null,
          id_legal: null,
          id_produksi: null,
          nama_jalan: button.data("nama-jalan") || "",
          no_kavling: button.data("no-kavling") || "",
        },
        data2: {
          harga_akhir: "-",
          id_hargajual: "",
          id_komplain: null,
          no_tipe_rumah: "",
          tipe_rumah: "",
          harga_akhir_tgl: "",
          harga_akhir_oleh: "",
        },
      };
      window.editdtt = [sh];
      if (typeof detail_kavling === "function") detail_kavling(sh, idKavling);
    });
    $(document).on("click", ".hasil-akad-manage-pencairan", function () {
      var button = $(this);
      var sh = {
        id_kavling: button.data("id-kavling"),
        id_mkdt: button.data("id-mkdt"),
        nama_jalan: button.data("nama-jalan"),
        no_kavling: button.data("no-kavling"),
        nama_proyek: config.projectName || "",
      };
      if (typeof openPencairanAkadModal === "function") {
        openPencairanAkadModal(sh);
      }
    });
    if (config.hasProject) loadSummary();
    else
      $(
        "#hasil_akad_year_a, #hasil_akad_year_b, #hasil_akad_apply, #hasil_akad_add_comparison, #hasil_akad_remove_comparison, #hasil_akad_toggle_chart"
      ).prop("disabled", true);
  });
})(jQuery);
