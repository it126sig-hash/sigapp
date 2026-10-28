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
    { key: "booking", field: "jumlah_booking", label: "Jumlah Booking", shortLabel: "Booking", isDrilldown: true },
    { key: "booking_aktif", field: "booking_aktif", label: "Booking Aktif", shortLabel: "Aktif", isDrilldown: true },
    { key: "booking_batal", field: "booking_batal", label: "Booking Batal", shortLabel: "Batal", isDrilldown: true },
    { key: "akad", field: "jumlah_akad", label: "Jumlah Akad", shortLabel: "Akad", isDrilldown: true },
    { key: "rasio_akad", field: "rasio_akad", label: "Rasio Akad (%)", shortLabel: "Rasio (%)", isDrilldown: false }
  ];

  // Palet warna chart [Tahun A, Tahun B]
  var chartColors = [
    { booking: "#2057a3", aktif: "#2a9d8f", batal: "#e76f51", akad: "#5b21b6" },
    { booking: "#1f7a8c", aktif: "#48cae4", batal: "#f4a261", akad: "#8338ec" }
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

  function statusBadge(status, isBatal) {
    var s = String(status || "").trim();
    if (isBatal || s.toLowerCase() === "batal") {
      return '<span class="booking-akad-badge-status booking-akad-badge-batal">Batal</span>';
    }
    if (s.toLowerCase() === "akad") {
      return '<span class="booking-akad-badge-status booking-akad-badge-akad">Akad</span>';
    }
    if (s.toLowerCase() === "booking") {
      return '<span class="booking-akad-badge-status booking-akad-badge-booking">Booking</span>';
    }
    return '<span class="booking-akad-badge-status booking-akad-badge-aktif">' + escapeHtml(s || "Aktif") + "</span>";
  }

  function renderHeader(years) {
    var row1 = ['<th rowspan="2" class="booking-akad-month-column">Bulan</th>'];
    var row2 = [];

    years.forEach(function (year, index) {
      var suffix = index === 0 ? "a" : "b";
      row1.push(
        '<th colspan="5" class="text-center booking-akad-year-heading booking-akad-year-heading-' +
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
      var cells = ["<td>" + escapeHtml(month.month_label) + "</td>"];
      years.forEach(function (year) {
        var amounts = summaryValues(month, year);
        categories.forEach(function (cat) {
          if (cat.isDrilldown) {
            cells.push(
              '<td class="text-right">' +
                detailButton(amounts[cat.field], year, month.month, cat.key, cat.label) +
                "</td>"
            );
          } else {
            cells.push('<td class="text-center">' + rasioBadge(amounts[cat.field]) + "</td>");
          }
        });
      });
      return "<tr>" + cells.join("") + "</tr>";
    });

    var footerCells = ["<td>Total Tahun</td>"];
    years.forEach(function (year) {
      var totals = data.totals[String(year)] || data.totals[year] || {};
      categories.forEach(function (cat) {
        if (cat.isDrilldown) {
          var val = Number(totals[cat.field] || 0);
          footerCells.push(
            '<td class="text-right font-weight-bold ' +
              (val === 0 ? "text-muted" : "") +
              '">' +
              formatNumber(val) +
              "</td>"
          );
        } else {
          footerCells.push('<td class="text-center">' + rasioBadge(totals[cat.field]) + "</td>");
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
      { key: "booking", field: "jumlah_booking", label: "Jumlah Booking", colorProp: "booking" },
      { key: "booking_aktif", field: "booking_aktif", label: "Booking Aktif", colorProp: "aktif" },
      { key: "booking_batal", field: "booking_batal", label: "Booking Batal", colorProp: "batal" },
      { key: "akad", field: "jumlah_akad", label: "Jumlah Akad", colorProp: "akad" }
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
            return Number(summaryValues(month, year)[metric.field] || 0);
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
        render: function (data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      {
        data: "jenis_laporan",
        name: "jenis_laporan",
        render: function (data, type, row) {
          return statusBadge(data, row.is_batal);
        }
      },
      {
        data: "alamat_kavling",
        name: "alamat_kavling",
        render: function (data, type, row) {
          var cluster = row.nama_cluster ? "<small>Cluster: " + escapeHtml(row.nama_cluster) + "</small>" : "";
          return (
            '<div class="booking-akad-detail-meta"><span class="font-weight-bold">' +
            escapeHtml(data) +
            "</span>" +
            cluster +
            "</div>"
          );
        }
      },
      {
        data: "nama_konsumen",
        name: "nama_konsumen",
        render: function (data, type, row) {
          var tipe = row.tipe_rumah ? "<small>Tipe: " + escapeHtml(row.tipe_rumah) + "</small>" : "";
          return (
            '<div class="booking-akad-detail-meta"><span>' +
            escapeHtml(data || "-") +
            "</span>" +
            tipe +
            "</div>"
          );
        }
      },
      {
        data: "tanggal_transaksi",
        name: "tanggal_transaksi",
        render: function (data) {
          return formatDate(data);
        }
      },
      {
        data: "status_mkdt",
        name: "status_mkdt",
        render: function (data, type, row) {
          return statusBadge(data, row.is_batal);
        }
      },
      {
        data: "nominal",
        name: "nominal",
        className: "text-right font-weight-bold",
        render: function (data) {
          return money(data);
        }
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: "text-center",
        render: function (data, type, row) {
          if (!row.id_kavling) return "-";
          return (
            '<button type="button" class="btn btn-info btn-sm booking-akad-view-kavling" ' +
            'data-id-kavling="' +
            escapeHtml(row.id_kavling) +
            '" ' +
            'data-id-mkdt="' +
            escapeHtml(row.id_mkdt || "") +
            '" ' +
            'data-nama-jalan="' +
            escapeHtml(row.nama_jalan || "") +
            '" ' +
            'data-no-kavling="' +
            escapeHtml(row.no_kavling || "") +
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
      order: [[4, "asc"]], // default sort tanggal_transaksi ASC
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
      var button = $(this);
      var idKavling = button.data("id-kavling");
      if (!idKavling) return;

      if (typeof window.openSiteplanDetailModal === "function") {
        window.openSiteplanDetailModal(idKavling);
        return;
      }

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
          no_kavling: button.data("no-kavling") || ""
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
      }
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
