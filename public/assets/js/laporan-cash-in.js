(function ($) {
  "use strict";

  var config = window.CASH_IN_REPORT || {};
  var detailTable = null;
  var categoryLabels = {
    booking_fee: "Booking Fee",
    uang_muka: "Uang Muka",
    hasil_akad: "Hasil Akad",
    all: "Total Cash In",
  };

  function updateToken(response) {
    if (response && response.token) {
      config.csrfHash = response.token;
    }
  }

  function csrfData() {
    var data = {};
    if (config.csrfName) {
      data[config.csrfName] = config.csrfHash;
    }
    return data;
  }

  function money(value) {
    var numeric = Number(value || 0);
    return "Rp " + new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(numeric);
  }

  function formatDate(value) {
    if (!value || value === "0000-00-00") {
      return "-";
    }

    var parts = String(value).split("-");
    return parts.length === 3 ? parts[2] + "-" + parts[1] + "-" + parts[0] : value;
  }

  function showError(message) {
    $("#cash_in_error").text(message || "Terjadi kesalahan saat memuat laporan.").prop("hidden", false);
  }

  function clearError() {
    $("#cash_in_error").prop("hidden", true).empty();
  }

  function showDetailError(message) {
    $("#cash_in_detail_error")
      .text(message || "Detail Cash In gagal dimuat.")
      .prop("hidden", false);
  }

  function clearDetailError() {
    $("#cash_in_detail_error").prop("hidden", true).empty();
  }

  function detailButton(value, year, month, category, label, totalClass) {
    var className = "cash-in-value-button" + (totalClass ? " cash-in-total-button" : "");
    return (
      '<button type="button" class="' + className + '"' +
      ' data-year="' + year + '"' +
      ' data-month="' + month + '"' +
      ' data-category="' + category + '"' +
      ' data-label="' + label + '"' +
      ' aria-label="Lihat detail ' + label + '">' +
      money(value) +
      "</button>"
    );
  }

  function renderSummary(data) {
    var years = data.years || [];
    if (years.length !== 2) {
      showError("Data tahun perbandingan tidak lengkap.");
      return;
    }

    $("#cash_in_year_a_heading").text(years[0]);
    $("#cash_in_year_b_heading").text(years[1]);

    var rows = (data.months || []).map(function (month) {
      var cells = ["<td>" + month.month_label + "</td>"];
      years.forEach(function (year) {
        var amounts = month.values[String(year)] || month.values[year] || {};
        cells.push("<td>" + detailButton(amounts.booking_fee, year, month.month, "booking_fee", "Booking Fee", false) + "</td>");
        cells.push("<td>" + detailButton(amounts.uang_muka, year, month.month, "uang_muka", "Uang Muka", false) + "</td>");
        cells.push("<td>" + detailButton(amounts.hasil_akad, year, month.month, "hasil_akad", "Hasil Akad", false) + "</td>");
        cells.push("<td>" + detailButton(amounts.total, year, month.month, "all", "Total Cash In", true) + "</td>");
      });

      return "<tr>" + cells.join("") + "</tr>";
    });

    var footer = ["<td>Total Tahun</td>"];
    years.forEach(function (year) {
      var totals = data.totals[String(year)] || data.totals[year] || {};
      footer.push("<td>" + money(totals.booking_fee) + "</td>");
      footer.push("<td>" + money(totals.uang_muka) + "</td>");
      footer.push("<td>" + money(totals.hasil_akad) + "</td>");
      footer.push("<td>" + money(totals.total) + "</td>");
    });

    $("#cash_in_matrix_body").html(rows.join(""));
    $("#cash_in_matrix_foot").html("<tr>" + footer.join("") + "</tr>");
  }

  function loadSummary() {
    if (!config.hasProject) {
      return;
    }

    clearError();
    var yearA = Number($("#cash_in_year_a").val());
    var yearB = Number($("#cash_in_year_b").val());

    if (yearA === yearB) {
      showError("Pilih dua tahun yang berbeda.");
      return;
    }

    $("#cash_in_loading").prop("hidden", false);
    $("#cash_in_apply").prop("disabled", true);

    var payload = csrfData();
    payload.year_a = yearA;
    payload.year_b = yearB;

    $.ajax({
      url: config.summaryUrl,
      type: "POST",
      dataType: "json",
      data: payload,
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
        showError(response.messages || "Laporan Cash In gagal dimuat.");
      })
      .always(function () {
        $("#cash_in_loading").prop("hidden", true);
        $("#cash_in_apply").prop("disabled", false);
      });
  }

  function openDetail(button) {
    var year = Number(button.data("year"));
    var month = Number(button.data("month"));
    var category = String(button.data("category"));
    var label = categoryLabels[category] || String(button.data("label"));
    var monthLabel = $("#cash_in_matrix_body tr").eq(month - 1).find("td:first").text();

    $("#cash_in_detail_title").text("Detail " + label);
    $("#cash_in_detail_subtitle").text(monthLabel + " " + year + " · " + config.projectName);
    clearDetailError();
    $("#cash_in_detail_modal").modal("show");

    if (detailTable) {
      detailTable.destroy();
      $("#cash_in_detail_table tbody").empty();
    }

    detailTable = $("#cash_in_detail_table").DataTable({
      processing: true,
      serverSide: true,
      searching: true,
      lengthChange: true,
      pageLength: 10,
      order: [[3, "asc"]],
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
          $("#cash_in_detail_table_processing").hide();
          showDetailError(response.messages || "Detail Cash In gagal dimuat.");
        },
      },
      columns: [
        { data: "jenis_pendapatan", name: "jenis_pendapatan", visible: category === "all" },
        { data: "alamat_kavling", name: "alamat_kavling" },
        { data: "nama_konsumen", name: "nama_konsumen" },
        {
          data: "tanggal_transaksi",
          name: "tanggal_transaksi",
          render: function (value) { return formatDate(value); },
        },
        {
          data: "nominal",
          name: "nominal",
          className: "text-right font-weight-bold",
          render: function (value) { return money(value); },
        },
      ],
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
    $("#cash_in_apply").on("click", loadSummary);
    $("#cash_in_matrix_body").on("click", ".cash-in-value-button", function () {
      openDetail($(this));
    });
    $("#cash_in_detail_modal").on("hidden.bs.modal", function () {
      if (detailTable) {
        detailTable.destroy();
        detailTable = null;
        $("#cash_in_detail_table tbody").empty();
      }
    });

    if (config.hasProject) {
      loadSummary();
    } else {
      $("#cash_in_year_a, #cash_in_year_b, #cash_in_apply").prop("disabled", true);
    }
  });
})(jQuery);
