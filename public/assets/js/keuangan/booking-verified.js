(function (window, $) {
  "use strict";

  let table = null;
  let initialized = false;
  let eventsBound = false;

  function escHtml(value) {
    if (typeof window.keuEscapeHtml === "function") {
      return window.keuEscapeHtml(value);
    }
    return $("<div>")
      .text(value === null || value === undefined ? "" : String(value))
      .html();
  }

  function escAttr(value) {
    if (typeof window.keuEscapeAttribute === "function") {
      return window.keuEscapeAttribute(value);
    }
    return escHtml(value)
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;")
      .replace(/`/g, "&#096;");
  }

  function formatMoney(value) {
    const num = Number(value) || 0;
    if (typeof window.num_format === "function") {
      return window.num_format(num);
    }
    return new Intl.NumberFormat("id-ID").format(num);
  }

  function formatDateSafe(value) {
    if (!value) return "-";
    const datePart = String(value).trim().split(" ")[0];
    if (!datePart) return "-";
    if (typeof window.format_date === "function") {
      return window.format_date(datePart) || datePart;
    }
    return datePart;
  }

  function formatDateTimeSafe(value) {
    if (!value) return "-";
    const raw = String(value).trim();
    if (!raw) return "-";
    if (raw.indexOf(" ") !== -1 && typeof window.format_datetime === "function") {
      return window.format_datetime(raw) || raw;
    }
    return formatDateSafe(raw);
  }

  function getActiveProyekId() {
    if (typeof window.activeProyekId === "function") {
      return window.activeProyekId() || "";
    }
    return "";
  }

  function renderStatusMkdtBadge(row) {
    const statusRaw = String(row.status_mkdt || "").trim();
    const statusLower = statusRaw.toLowerCase();

    if (row.is_batal === true || statusLower === "batal") {
      return '<span class="booking-verified-badge booking-verified-badge-danger"><i class="fas fa-ban"></i> Batal</span>';
    }
    if (!statusRaw) {
      return "";
    }

    let badgeClass = "booking-verified-badge-neutral";
    if (
      statusLower.indexOf("akad") !== -1 ||
      statusLower.indexOf("lunas") !== -1 ||
      statusLower.indexOf("selesai") !== -1
    ) {
      badgeClass = "booking-verified-badge-success";
    } else if (
      statusLower.indexOf("sp3k") !== -1 ||
      statusLower.indexOf("proses") !== -1 ||
      statusLower.indexOf("wawancara") !== -1 ||
      statusLower.indexOf("pemberkasan") !== -1
    ) {
      badgeClass = "booking-verified-badge-warning";
    } else if (
      statusLower.indexOf("booking") !== -1 ||
      statusLower.indexOf("aktif") !== -1
    ) {
      badgeClass = "booking-verified-badge-info";
    }

    return `<span class="booking-verified-badge ${badgeClass}">${escHtml(statusRaw)}</span>`;
  }

  function renderUnitCell(row) {
    const namaKonsumen = escHtml(row.nama_konsumen || "-");
    const isNup = row.is_nup === true;
    const tipeRumah = row.tipe_rumah ? ` &bull; ${escHtml(row.tipe_rumah)}` : "";

    let unitHtml = "";
    if (isNup) {
      const noNup = escHtml(row.no_nup || row.no_kavling || "-");
      const kavInfo =
        row.nama_jalan || row.no_kavling
          ? ` (${escHtml(row.nama_jalan || "-")} &bull; No. ${escHtml(row.no_kavling || "-")})`
          : "";
      unitHtml = `<span class="booking-verified-badge booking-verified-badge-info mr-25">NUP</span> ${noNup}${kavInfo}${tipeRumah}`;
    } else {
      unitHtml = `${escHtml(row.nama_jalan || "-")} &bull; No. ${escHtml(row.no_kavling || "-")}${tipeRumah}`;
    }

    const badges = [];
    const statusBadge = renderStatusMkdtBadge(row);
    if (statusBadge) badges.push(statusBadge);
    if (row.is_lunas === true) {
      badges.push(
        '<span class="booking-verified-badge booking-verified-badge-success"><i class="fas fa-check-circle"></i> Lunas</span>',
      );
    }

    const badgesHtml = badges.length
      ? `<div class="booking-verified-badges">${badges.join("")}</div>`
      : "";

    return `
      <div class="font-weight-bold text-dark">${namaKonsumen}</div>
      <div class="small text-muted mt-25">${unitHtml}</div>
      ${badgesHtml}
    `;
  }

  function renderBookingFeeCell(row) {
    const nominalHtml = `<div class="font-weight-bolder text-primary">Rp ${escHtml(formatMoney(row.booking_fee))}</div>`;
    const tglHtml = `<div class="small text-muted mt-25"><i class="far fa-calendar-alt mr-25"></i> ${escHtml(formatDateSafe(row.booking_tgl))}</div>`;

    const badges = [];
    if (row.is_promo === true) {
      badges.push(
        '<span class="booking-verified-badge booking-verified-badge-warning">Promo Rp 0</span>',
      );
    }
    if (Number(row.shared_owners_count || 1) > 1) {
      badges.push(
        '<span class="booking-verified-badge booking-verified-badge-info"><i class="fas fa-users"></i> Pembayaran Bersama</span>',
      );
    }

    const badgesHtml = badges.length
      ? `<div class="booking-verified-badges">${badges.join("")}</div>`
      : "";

    return `${nominalHtml}${tglHtml}${badgesHtml}`;
  }

  function renderVerifikasiCell(row) {
    const verifiedBadge =
      '<div><span class="booking-verified-badge booking-verified-badge-success"><i class="fas fa-check-double"></i> Terverifikasi</span></div>';
    const verifiedAt = `<div class="small text-muted mt-25"><i class="far fa-clock mr-25"></i> ${escHtml(formatDateTimeSafe(row.verified_at))}</div>`;
    const verifiedBy = `<div class="small text-muted font-weight-bold mt-25"><i class="fas fa-user-check mr-25"></i> ${escHtml(row.verified_by_name || "-")}</div>`;
    return `${verifiedBadge}${verifiedAt}${verifiedBy}`;
  }

  function renderAksiCell(row) {
    const buttons = [];

    if (row.can_unverify === true) {
      buttons.push(
        '<button type="button" class="btn btn-outline-danger btn-sm btn-unverify-booking" title="Batalkan Verifikasi Booking Fee"><i class="fas fa-undo-alt mr-25"></i> Batalkan</button>',
      );
    } else if (row.unverify_blocked_reason) {
      buttons.push(
        `<button type="button" class="btn btn-outline-secondary btn-sm" disabled title="${escAttr(row.unverify_blocked_reason)}"><i class="fas fa-lock mr-25"></i> Terkunci</button>`,
      );
    }

    if (row.id_pembayaran) {
      buttons.push(
        '<button type="button" class="btn btn-outline-primary btn-sm btn-print-booking-verified" title="Cetak Kuitansi Booking"><i class="fas fa-print"></i></button>',
      );
    }

    buttons.push(
      '<button type="button" class="btn btn-outline-secondary btn-sm btn-open-booking-modal" title="Buka Detail Pembayaran"><i class="fas fa-file-invoice-dollar"></i></button>',
    );

    return `<div class="booking-verified-actions">${buttons.join("")}</div>`;
  }

  function bindEvents() {
    if (eventsBound) return;
    eventsBound = true;

    $(document).on("click.bookingVerified", "#btn_refresh_booking_terverifikasi", function () {
      reload(false);
    });

    $("#table_booking_terverifikasi tbody")
      .off("click.bookingVerified")
      .on("click.bookingVerified", ".btn-unverify-booking", function () {
        const row = table ? table.row($(this).closest("tr")).data() : null;
        if (!row) return;
        if (typeof window.confirmUnverifyBookingFee === "function") {
          window.confirmUnverifyBookingFee({
            id_mkdt: row.id_mkdt,
            nama_konsumen: row.nama_konsumen || "-",
            nominal: row.booking_fee,
            tanggal: row.booking_tgl,
            is_promo: Boolean(row.is_promo),
          });
        }
      })
      .on("click.bookingVerified", ".btn-print-booking-verified", function () {
        const row = table ? table.row($(this).closest("tr")).data() : null;
        if (!row || !row.id_pembayaran) return;
        const proyId = row.id_proyek || getActiveProyekId();
        const url =
          base_url +
          "pembayaran/kuitansi/cetak?e=" +
          encodeURIComponent(row.id_pembayaran) +
          "&e2=" +
          encodeURIComponent(row.id_mkdt) +
          "&e3=" +
          encodeURIComponent(proyId);
        window.open(url, "_blank", "top=100,left=300,width=700,height=600");
      })
      .on("click.bookingVerified", ".btn-open-booking-modal", function () {
        const row = table ? table.row($(this).closest("tr")).data() : null;
        if (!row || typeof window.open_keuangan !== "function") return;
        window.open_keuangan(
          {
            data: {
              id_mkdt: row.id_mkdt,
              nama_proyek: row.nama_proyek || "",
              nama_jalan: row.nama_jalan || (row.is_nup ? "NUP" : "-"),
              no_kavling: row.no_kavling || row.no_nup || "-",
            },
            data2: {
              no_tipe_rumah: row.no_tipe_rumah || "-",
              tipe_rumah: row.tipe_rumah || "-",
            },
          },
          3,
          row.id_kavling || 0,
        );
      });
  }

  function init() {
    const $table = $("#table_booking_terverifikasi");
    if (!$table.length) return;
    if (initialized && table) return;

    bindEvents();

    table = $table.DataTable({
      scrollY: "55vh",
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      processing: true,
      serverSide: true,
      lengthChange: true,
      pageLength: 25,
      searching: true,
      searchDelay: 350,
      ordering: false,
      paging: true,
      language: {
        emptyTable: "Belum ada booking fee terverifikasi.",
        zeroRecords: "Data booking fee terverifikasi tidak ditemukan.",
        processing: "Memuat data...",
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        infoFiltered: "(disaring dari _MAX_ total data)",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Berikutnya",
          previous: "Sebelumnya",
        },
      },
      ajax: {
        url: base_url + "api/tagihan/booking/terverifikasi",
        type: "POST",
        dataType: "json",
        data: function (d) {
          d[csrfName] = csrfHash;
          d.id_proyek = getActiveProyekId();
          d.id_cluster = $("#id_cluster").val() || "";
          d.id_jalan = $("#id_jalan").val() || "";
        },
        dataSrc: function (r) {
          if (r && r.token) {
            csrfHash = r.token;
          }
          if (r && r.success === false) {
            if (typeof Swal !== "undefined") {
              Swal.fire({
                icon: "error",
                title: "Gagal Memuat Data",
                text: r.messages || "Gagal memuat daftar booking fee terverifikasi.",
              });
            }
            return [];
          }
          return Array.isArray(r && r.data) ? r.data : [];
        },
        error: function (xhr) {
          $("#table_booking_terverifikasi_processing").css("display", "none");
          const response = xhr && xhr.responseJSON ? xhr.responseJSON : {};
          if (response.token) {
            csrfHash = response.token;
          }
          if (typeof Swal !== "undefined") {
            Swal.fire({
              icon: "error",
              title: "Gagal Memuat Data",
              text:
                response.messages ||
                "Terjadi kesalahan saat memuat booking fee terverifikasi.",
            });
          }
        },
      },
      initComplete: function () {
        const $searchInput = $("#table_booking_terverifikasi_filter input");
        if ($searchInput.length) {
          $searchInput
            .attr("placeholder", "Cari konsumen, unit, NUP...")
            .off("keydown.bookingVerifiedSearch")
            .on("keydown.bookingVerifiedSearch", function (e) {
              if (e.key === "Enter") {
                e.preventDefault();
                if (table) {
                  table.search(this.value).draw();
                }
              }
            });
        }
      },
      columns: [
        {
          data: null,
          className: "text-center",
          orderable: false,
          searchable: false,
          render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          },
        },
        {
          data: "nama_konsumen",
          className: "text-left",
          render: function (data, type, row) {
            return renderUnitCell(row);
          },
        },
        {
          data: "booking_fee",
          className: "text-left",
          render: function (data, type, row) {
            return renderBookingFeeCell(row);
          },
        },
        {
          data: "verified_at",
          className: "text-left",
          render: function (data, type, row) {
            return renderVerifikasiCell(row);
          },
        },
        {
          data: null,
          className: "text-center",
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            return renderAksiCell(row);
          },
        },
      ],
    });

    initialized = true;
  }

  function reload(resetPaging) {
    if (!initialized || !table) return;
    table.ajax.reload(null, Boolean(resetPaging));
  }

  function adjust() {
    if (!table) return;
    table.columns.adjust();
    window.setTimeout(function () {
      if (table) table.columns.adjust();
    }, 20);
  }

  window.BookingVerifiedTab = {
    init: init,
    reload: reload,
    adjust: adjust,
  };
})(window, jQuery);
