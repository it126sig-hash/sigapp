/**
 * ==========================================================================
 * MODUL: LAPORAN BOOKING & AKAD
 * Script: laporan-booking-akad.js
 * Standar: SIGAPP UI Acuan
 * ==========================================================================
 */

(function ($) {
  "use strict";

  var config = window.BOOKING_AKAD_REPORT || {};
  var detailTable = null;
  var reportChart = null;
  var lastSummary = null;
  var comparisonEnabled = false;

  var categories = [
    { key: "booking", field: "booking", label: "Booking", shortLabel: "Booking", isDrilldown: true },
    { key: "wawancara", field: "wawancara", label: "Wawancara", shortLabel: "Wawancara", isDrilldown: true },
    { key: "sp3k", field: "sp3k", label: "SP3K", shortLabel: "SP3K", isDrilldown: true },
    { key: "batal", field: "batal", label: "Batal", shortLabel: "Batal", isDrilldown: true },
    { key: "akad_indent", field: "akad_indent", label: "Akad Indent", shortLabel: "Akad Indent", isDrilldown: true },
    { key: "akad_ajb", field: "akad_ajb", label: "Akad AJB", shortLabel: "Akad AJB", isDrilldown: true }
  ];

  // Palet warna chart [Tahun A, Tahun B]
  var chartColors = [
    { booking: "#2057a3", wawancara: "#e9c46a", sp3k: "#2a9d8f", batal: "#e76f51", akad_indent: "#8338ec", akad_ajb: "#15803d" },
    { booking: "#1f7a8c", wawancara: "#f4a261", sp3k: "#48cae4", batal: "#d62828", akad_indent: "#9b5de5", akad_ajb: "#2b9348" }
  ];

  function csrfData() {
    var data = {};
    if (config.csrfName) {
      data[config.csrfName] = config.csrfHash;
    }
    return data;
  }

  function updateToken(response) {
    if (response && response.token) {
      config.csrfHash = response.token;
    }
  }

  function escapeHtml(value) {
    return $("<div>").text(String(value == null ? "" : value)).html();
  }

  function formatNumber(value) {
    return new Intl.NumberFormat("id-ID").format(Number(value || 0));
  }

  function money(value) {
    return "Rp " + new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(value || 0));
  }

  function formatDate(value) {
    var raw = String(value || "").split(" ")[0];
    var parts = raw.split("-");
    return parts.length === 3 && parts[0] !== "0000" ? parts[2] + "-" + parts[1] + "-" + parts[0] : "-";
  }

  function showError(message) {
    $("#booking_akad_error").text(message || "Terjadi kesalahan saat memuat laporan.").prop("hidden", false);
  }

  function clearError() {
    $("#booking_akad_error").prop("hidden", true).empty();
  }

  function showDetailError(message) {
    $("#booking_akad_detail_error").text(message || "Detail data gagal dimuat.").prop("hidden", false);
  }

  function clearDetailError() {
    $("#booking_akad_detail_error").prop("hidden", true).empty();
  }

  function detailButton(value, year, month, category, label) {
    var val = Number(value || 0);
    var zeroClass = val === 0 ? " is-zero" : "";
    return (
      '<button type="button" class="booking-akad-value-button' +
      zeroClass +
      '" data-year="' +
      year +
      '" data-month="' +
      month +
      '" data-category="' +
      category +
      '" data-label="' +
      escapeHtml(label) +
      '">' +
      formatNumber(val) +
      "</button>"
    );
  }

  function rasioBadge(value) {
    var val = Number(value || 0);
    var positive = val > 0;
    return (
      '<span class="booking-akad-rasio-badge ' +
      (positive ? "is-positive" : "is-zero") +
      '">' +
      val.toFixed(1) +
      "%</span>"
    );
  }

  function statusBadge(status, badgeType) {
    var s = String(status || "Booking").trim();
    var lower = s.toLowerCase();
    var type = badgeType;

    if (!type) {
      if (lower === "batal") {
        type = "danger";
      } else if (lower === "akad ajb" || lower === "akad") {
        type = "success";
      } else if (lower === "akad indent" || lower === "indent") {
        type = "info";
      } else if (lower === "sp3k") {
        type = "primary";
      } else if (lower === "wawancara") {
        type = "warning";
      } else {
        type = "secondary";
      }
    }

    var slug = lower.replace(/\s+/g, "-");
    return '<span class="badge badge-light-' + escapeHtml(type) + ' booking-akad-badge-status booking-akad-badge-' + escapeHtml(slug) + '">' + escapeHtml(s) + "</span>";
  }

  function renderHeader(years) {
    var row1 = ['<th rowspan="2" class="text-left booking-akad-month-column">Bulan</th>'];
    var row2 = [];

    years.forEach(function (year, index) {
      var suffix = index === 0 ? "a" : "b";
      row1.push(
        '<th colspan="6" class="text-center booking-akad-year-heading booking-akad-year-heading-' +
          suffix +
          '">' +
          escapeHtml(year) +
          "</th>"
      );
      categories.forEach(function (cat) {
        row2.push(
          '<th class="text-center booking-akad-year-subheading-' +
            suffix +
            '" title="' +
            escapeHtml(cat.label) +
            '">' +
            escapeHtml(cat.shortLabel) +
            "</th>"
        );
      });
    });

    $("#booking_akad_matrix_head").html("<tr>" + row1.join("") + "</tr><tr>" + row2.join("") + "</tr>");
    $("#booking_akad_matrix").toggleClass("is-comparison", years.length > 1);
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
      var cells = ['<td class="text-left">' + escapeHtml(month.month_label) + "</td>"];
      years.forEach(function (year) {
        var amounts = summaryValues(month, year);
        categories.forEach(function (cat) {
          var val = amounts[cat.field] !== undefined ? amounts[cat.field] : (amounts["jumlah_" + cat.field] !== undefined ? amounts["jumlah_" + cat.field] : amounts["rasio_" + cat.field]);
          if (cat.key === "booking") {
            var konversiVal = amounts.konversi !== undefined ? amounts.konversi : (amounts.rasio_konversi !== undefined ? amounts.rasio_konversi : (amounts.rasio_akad !== undefined ? amounts.rasio_akad : 0));
            cells.push(
              '<td class="text-right">' +
                '<div class="booking-cell-wrapper">' +
                  detailButton(val, year, month.month, cat.key, cat.label) +
                  '<div class="booking-konversi-sub">' + rasioBadge(konversiVal) + '</div>' +
                '</div>' +
              "</td>"
            );
          } else if (cat.isDrilldown) {
            cells.push(
              '<td class="text-right">' +
                detailButton(val, year, month.month, cat.key, cat.label) +
                "</td>"
            );
          } else {
            cells.push('<td class="text-center">' + rasioBadge(val) + "</td>");
          }
        });
      });
      return "<tr>" + cells.join("") + "</tr>";
    });

    var footerCells = ['<td class="text-left font-weight-bold">Total Tahun</td>'];
    years.forEach(function (year) {
      var totals = data.totals[String(year)] || data.totals[year] || {};
      categories.forEach(function (cat) {
        var val = totals[cat.field] !== undefined ? totals[cat.field] : (totals["jumlah_" + cat.field] !== undefined ? totals["jumlah_" + cat.field] : totals["rasio_" + cat.field]);
        if (cat.key === "booking") {
          var num = Number(val || 0);
          var totalKonversi = totals.konversi !== undefined ? totals.konversi : (totals.rasio_konversi !== undefined ? totals.rasio_konversi : (totals.rasio_akad !== undefined ? totals.rasio_akad : 0));
          footerCells.push(
            '<td class="text-right font-weight-bold ' +
              (num === 0 ? "text-muted" : "") +
              '">' +
              '<div class="booking-cell-wrapper booking-cell-total">' +
                '<span class="font-weight-bold">' + formatNumber(num) + '</span>' +
                '<div class="booking-konversi-sub">' + rasioBadge(totalKonversi) + '</div>' +
              '</div>' +
            "</td>"
          );
        } else if (cat.isDrilldown) {
          var num = Number(val || 0);
          footerCells.push(
            '<td class="text-right font-weight-bold ' +
              (num === 0 ? "text-muted" : "") +
              '">' +
              formatNumber(num) +
              "</td>"
          );
        } else {
          footerCells.push('<td class="text-center font-weight-bold">' + rasioBadge(val) + "</td>");
        }
      });
    });

    $("#booking_akad_matrix_body").html(rows.join(""));
    $("#booking_akad_matrix_foot").html("<tr>" + footerCells.join("") + "</tr>");
    $("#booking_akad_toggle_chart").prop("disabled", false);

    if (!$("#booking_akad_chart_card").prop("hidden")) {
      renderChart(data);
    }
  }

  function chartDatasets(data) {
    var datasets = [];
    var metricKeys = [
      { key: "booking", field: "booking", label: "Booking", colorProp: "booking" },
      { key: "wawancara", field: "wawancara", label: "Wawancara", colorProp: "wawancara" },
      { key: "sp3k", field: "sp3k", label: "SP3K", colorProp: "sp3k" },
      { key: "batal", field: "batal", label: "Batal", colorProp: "batal" },
      { key: "akad_indent", field: "akad_indent", label: "Akad Indent", colorProp: "akad_indent" },
      { key: "akad_ajb", field: "akad_ajb", label: "Akad AJB", colorProp: "akad_ajb" }
    ];

    (data.years || []).forEach(function (year, yearIndex) {
      var palette = chartColors[yearIndex] || chartColors[0];
      metricKeys.forEach(function (metric) {
        var color = palette[metric.colorProp];
        datasets.push({
          label: metric.label + " " + year,
          backgroundColor: color,
          borderColor: color,
          borderWidth: 1,
          data: (data.months || []).map(function (month) {
            var amounts = summaryValues(month, year);
            var val = amounts[metric.field] !== undefined ? amounts[metric.field] : amounts["jumlah_" + metric.field];
            return Number(val || 0);
          })
        });
      });
    });

    return datasets;
  }

  function renderChart(data) {
    var canvas = document.getElementById("booking_akad_chart");
    if (!canvas || typeof Chart === "undefined") return;

    if (reportChart) {
      reportChart.destroy();
    }

    reportChart = new Chart(canvas.getContext("2d"), {
      type: "bar",
      data: {
        labels: (data.months || []).map(function (month) {
          return month.month_label;
        }),
        datasets: chartDatasets(data)
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          position: "bottom",
          labels: { boxWidth: 12, usePointStyle: true, fontSize: 11 }
        },
        tooltips: {
          mode: "index",
          intersect: false,
          callbacks: {
            label: function (item, chart) {
              return (chart.datasets[item.datasetIndex].label || "") + ": " + formatNumber(item.yLabel) + " unit";
            }
          }
        },
        scales: {
          xAxes: [{ stacked: false, gridLines: { display: false } }],
          yAxes: [
            {
              stacked: false,
              ticks: {
                beginAtZero: true,
                precision: 0,
                callback: function (value) {
                  return formatNumber(value);
                }
              }
            }
          ]
        }
      }
    });
  }

  function setComparison(enabled) {
    comparisonEnabled = enabled;
    $("#booking_akad_comparison_controls").prop("hidden", !enabled);
    $("#booking_akad_add_comparison").prop("hidden", enabled);

    if (enabled && $("#booking_akad_year_a").val() === $("#booking_akad_year_b").val()) {
      var other = $("#booking_akad_year_b option")
        .filter(function () {
          return this.value !== $("#booking_akad_year_a").val();
        })
        .first()
        .val();
      if (other) {
        $("#booking_akad_year_b").val(other);
      }
    }
  }

  function loadSummary() {
    if (!config.hasProject) return;

    clearError();
    var yearA = Number($("#booking_akad_year_a").val());
    var yearB = Number($("#booking_akad_year_b").val());

    if (comparisonEnabled && yearA === yearB) {
      showError("Pilih dua tahun yang berbeda.");
      return;
    }

    $("#booking_akad_loading").prop("hidden", false);
    $("#booking_akad_apply, #booking_akad_add_comparison, #booking_akad_remove_comparison").prop("disabled", true);

    var payload = csrfData();
    payload.year_a = yearA;
    if (comparisonEnabled) {
      payload.year_b = yearB;
    }

    $.ajax({
      url: config.summaryUrl,
      type: "POST",
      dataType: "json",
      data: payload
    })
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
        showError(response.messages || "Laporan Booking & Akad gagal dimuat.");
      })
      .always(function () {
        $("#booking_akad_loading").prop("hidden", true);
        $("#booking_akad_apply, #booking_akad_add_comparison, #booking_akad_remove_comparison").prop("disabled", false);
      });
  }

  function toggleChart() {
    if (!lastSummary) return;

    var card = $("#booking_akad_chart_card");
    var willShow = card.prop("hidden");
    card.prop("hidden", !willShow);
    $("#booking_akad_toggle_chart span").text(willShow ? "Sembunyikan Chart" : "Tampilkan Chart");

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
    var label = String(button.data("label") || "");

    var monthLabel = $("#booking_akad_matrix_body tr").eq(month - 1).find("td:first").text();
    $("#booking_akad_detail_title").text("Detail " + label);
    $("#booking_akad_detail_subtitle").text(monthLabel + " " + year + " · " + (config.projectName || ""));

    clearDetailError();
    $("#booking_akad_detail_modal").modal("show");

    if (detailTable) {
      detailTable.destroy();
      $("#booking_akad_detail_table tbody").empty();
    }

    var columns = [
      {
        data: null,
        orderable: false,
        searchable: false,
        className: "text-center",
        width: "40px",
        render: function (data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      {
        data: "kavling",
        name: "kavling",
        className: "text-left",
        render: function (data, type, row) {
          var jalan = (row.kavling && row.kavling.nama_jalan) || row.nama_jalan || "-";
          var no = (row.kavling && row.kavling.no_kavling) || row.no_kavling || "-";
          var sales = (row.kavling && row.kavling.sales) || row.sales || "-";
          var alamat = escapeHtml(jalan) + " No " + escapeHtml(no);
          return (
            '<div class="booking-akad-detail-meta">' +
            '<span class="font-weight-bold text-dark">' + alamat + "</span>" +
            '<small class="text-muted d-block">' + escapeHtml(sales) + "</small>" +
            "</div>"
          );
        }
      },
      {
        data: "konsumen",
        name: "konsumen",
        className: "text-left",
        render: function (data, type, row) {
          var nama = (row.konsumen && row.konsumen.nama_konsumen) || row.nama_konsumen || "-";
          var tipe = (row.konsumen && row.konsumen.tipe_rumah) || row.tipe_rumah || row.nama_tipe || "-";
          var isSubsidi = (row.konsumen && typeof row.konsumen.is_subsidi !== "undefined") ? row.konsumen.is_subsidi : row.is_subsidi;
          var badgeClass = isSubsidi ? "badge-light-success" : "badge-light-primary";
          var badgeLabel = isSubsidi ? "Subsidi" : "Komersil";
          return (
            '<div class="booking-akad-detail-meta">' +
            '<span class="font-weight-bold text-dark">' + escapeHtml(nama) + "</span>" +
            '<small class="text-muted d-block">' + escapeHtml(tipe) + "</small>" +
            '<span class="badge ' + badgeClass + ' mt-25" style="width: fit-content;">' + badgeLabel + "</span>" +
            "</div>"
          );
        }
      },
      {
        data: "tanggal_booking",
        name: "tanggal_booking",
        className: "text-center",
        render: function (data, type, row) {
          var val = data || row.booking_tgl || "-";
          return escapeHtml(val);
        }
      },
      {
        data: "status_terakhir",
        name: "status_terakhir",
        className: "text-left",
        render: function (data, type, row) {
          var status = data || row.status_mkdt || "Booking";
          var badgeType = row.status_terakhir_badge || "";
          var tglStatus = row.tanggal_status_terakhir || "-";
          return (
            '<div class="booking-akad-status-cell">' +
            '<span class="booking-akad-status-date">' + escapeHtml(tglStatus) + "</span>" +
            statusBadge(status, badgeType) +
            "</div>"
          );
        }
      },
      {
        data: "catatan_mkdt",
        name: "catatan_mkdt",
        className: "text-left",
        render: function (data, type, row) {
          var catatan = (data !== undefined && data !== null) ? String(data).trim() : (row.catatan_mkdt ? String(row.catatan_mkdt).trim() : "");
          if (!catatan || catatan === "-") {
            return '<span class="text-muted">-</span>';
          }
          return '<span class="booking-akad-catatan-text">' + escapeHtml(catatan) + "</span>";
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: "text-center",
        width: "70px",
        render: function (data, type, row) {
          var idKavling = row.id_kavling || (row.kavling && row.kavling.id_kavling);
          if (!idKavling) return "-";
          var idMkdt = row.id_mkdt || "";
          var namaJalan = (row.kavling && row.kavling.nama_jalan) || row.nama_jalan || "";
          var noKavling = (row.kavling && row.kavling.no_kavling) || row.no_kavling || "";
          return (
            '<button type="button" class="btn btn-info btn-sm booking-akad-view-kavling" ' +
            'data-id-kavling="' +
            escapeHtml(idKavling) +
            '" ' +
            'data-id-mkdt="' +
            escapeHtml(idMkdt) +
            '" ' +
            'data-nama-jalan="' +
            escapeHtml(namaJalan) +
            '" ' +
            'data-no-kavling="' +
            escapeHtml(noKavling) +
            '" ' +
            'title="Lihat Detail Kavling">' +
            '<i class="fa fa-eye"></i></button>'
          );
        }
      }
    ];

    detailTable = $("#booking_akad_detail_table").DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: [[3, "asc"]], // default sort tanggal_booking ASC
      scrollX: true,
      autoWidth: false,
      ajax: {
        url: config.detailUrl,
        type: "POST",
        data: function (request) {
          request.year = year;
          request.month = month;
          request.category = category;
          if (config.csrfName) {
            request[config.csrfName] = config.csrfHash;
          }
        },
        dataSrc: function (response) {
          updateToken(response);
          return response.data || [];
        },
        error: function (xhr) {
          var response = xhr.responseJSON || {};
          updateToken(response);
          $("#booking_akad_detail_table_processing").hide();
          showDetailError(response.messages || "Detail data gagal dimuat.");
        }
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
        paginate: { previous: "Sebelumnya", next: "Berikutnya" }
      }
    });
  }

  function openKavlingDetailModal(button) {
    var idKavling = button.data("id-kavling");
    if (!idKavling) return;

    if (typeof window.initSigappModalStacking === "function") {
      window.initSigappModalStacking();
    }

    window.loaded = window.loaded || [];

    if (typeof window.openSiteplanDetailModal === "function") {
      window.openSiteplanDetailModal(idKavling);
      return;
    }

    var namaJalan = String(button.data("nama-jalan") || "");
    var noKavling = String(button.data("no-kavling") || "");
    var idMkdt = button.data("id-mkdt") || null;

    var sh = {
      id: "kav" + idKavling,
      data: {
        tipe: "kavling",
        id_kavling: idKavling,
        id_mkdt: idMkdt,
        id_keuangan: null,
        id_legal: null,
        id_produksi: null,
        nama_jalan: namaJalan,
        no_kavling: noKavling
      },
      data2: {
        harga_akhir: "-",
        id_hargajual: "",
        id_komplain: null,
        no_tipe_rumah: "",
        tipe_rumah: "",
        harga_akhir_tgl: "",
        harga_akhir_oleh: ""
      }
    };

    window.editdtt = [sh];
    if (typeof detail_kavling === "function") {
      detail_kavling(sh, idKavling);
    } else {
      console.error("[BookingAkad] Fungsi detail_kavling tidak tersedia.");
    }
  }

  // Event Handlers
  $(function () {
    setComparison(false);

    $("#booking_akad_apply").on("click", loadSummary);

    $("#booking_akad_add_comparison").on("click", function () {
      setComparison(true);
    });

    $("#booking_akad_remove_comparison").on("click", function () {
      setComparison(false);
      loadSummary();
    });

    $("#booking_akad_toggle_chart").on("click", toggleChart);

    $("#booking_akad_matrix_body").on("click", ".booking-akad-value-button", function () {
      openDetail($(this));
    });

    $("#booking_akad_detail_modal").on("hidden.bs.modal", function () {
      if (detailTable) {
        detailTable.destroy();
        detailTable = null;
        $("#booking_akad_detail_table tbody").empty();
      }
    });

    // Handler tombol aksi Lihat Detail Kavling
    $(document).on("click", ".booking-akad-view-kavling", function () {
      openKavlingDetailModal($(this));
    });

    if (config.hasProject) {
      loadSummary();
    } else {
      $(
        "#booking_akad_year_a, #booking_akad_year_b, #booking_akad_apply, #booking_akad_add_comparison, #booking_akad_remove_comparison, #booking_akad_toggle_chart"
      ).prop("disabled", true);
    }
  });
})(jQuery);
