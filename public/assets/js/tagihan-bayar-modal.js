/* Shared bayar tagihan modal. Keep this in sync by editing this file only. */
var alokasi_items = [];
var keuSubmitInProgress = false;

$("#btn-add-item-alokasi").click(function () {
  let nominal = removeComma($("#bt-bayar_tagihan_um").val());
  let tanggal = $("#bt-tanggal_bayar_um").val();
  let metode = $("#bt-for").val();
  if (metode == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Angsuran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-for").focus();
    });
    return;
  }
  if (tanggal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Tanggal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-tanggal_bayar_um").focus();
    });
    return;
  }
  if (nominal == 0 || nominal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Nominal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  let options = {};
  li_keu.forEach((item) => {
    options[item.id_keuangan_item_list] = item.item;
  });

  Swal.fire({
    title: "Pilih Item",
    input: "select",
    inputOptions: options,
    inputPlaceholder: "Pilih item alokasi",
    showCancelButton: true,
  }).then((result) => {
    if (result.value) {
      let selectedItem = li_keu.find(
        (item) => String(item.id_keuangan_item_list) === String(result.value),
      );
      if (selectedItem) {
        // alokasi_items.push(selectedItem);
        const autoNominal = keuAllocationAutoNominal(selectedItem);
        if (autoNominal <= 0) {
          Swal.fire({
            icon: "warning",
            title: "Item pembyaran yang kamu pilih tidak memiliki nominal",
            text: "Item ini sudah lunas/nominal yang ditagihkan sudah terbayar.",
            confirmButtonText: "OK",
          }).then(() => {
            renderTableAlokasi(selectedItem, 0);
          });
          return;
        }

        renderTableAlokasi(selectedItem, autoNominal);
      }
    }
  });
});

function renderTableAlokasi(item, nominal = 0) {
  let html = "";
  const itemMax = keuItemRemaining(item);
  const maxInfo = itemMax === null
    ? "Maks. mengikuti sisa nominal pembayaran"
    : "Maks. Rp " + num_format(itemMax);

  if ($(`#fm-bayar_nominal-${item.id_keuangan_item_list}`).length) {
    Swal.fire({
      icon: "error",
      title: item.item + " sudah ditambahkan",
      showConfirmButton: false,
    });
    return;
  }
  alokasi_items.push(item);
  html = `
  <tr id="tr-li-${item.id_keuangan_item_list}">
    <td><a href="javascript:void(0)" onclick="deleteItemAlokasi(${item.id_keuangan_item_list})" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a></td>
    <td>${item.item}</td>
    <td class="text-right">
      <input type="text"
        onchange="setAlokasi(this)"
        name="nominal-${item.id_keuangan_item_list}"
        id="fm-bayar_nominal-${item.id_keuangan_item_list}"
        class="form-control num item-alokasi"
        data-item-max="${itemMax === null ? "" : itemMax}"
        value="${nominal}"
        placeholder="Nominal alokasi dari pembayaran">
      <small class="text-muted d-block mt-25">${maxInfo}</small>
    </td>
  </tr>`;
  $("#tb-alokasi-dana").append(html);
  $(`#fm-bayar_nominal-${item.id_keuangan_item_list}`).keyup();
  setAlokasi();
}

function deleteItemAlokasi(id) {
  Swal.fire({
    title: "Apakah anda yakin?",
    text: "Item alokasi akan dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
  }).then((result) => {
    if (result.isConfirmed) {
      let index = alokasi_items.findIndex(
        (item) => item.id_keuangan_item_list == id,
      );
      if (index !== -1) {
        alokasi_items.splice(index, 1);
        $(`#tr-li-${id}`).remove();
        setAlokasi();
        Swal.fire("Terhapus!", "Item alokasi telah dihapus.", "success");
      }
    }
  });
}

/********************************* keuangan *******************************************/
$("#bt-bayar_tagihan_um").change(function () {
  ubahMaksNominal("#bt-bayar_tagihan_um");
});

function ubahMaksNominal(id) {
  let s = state.total_cicilan - state.sudah_bayar,
    b = removeComma($(id).val());

  if (b > s) $(id).val(s).keyup();
  else $(id).val(b).keyup();

  setAlokasi();
}

function setAlokasi(e = null) {
  function recomputeAlokasiTotals() {
    const alokasi = $("#fm-keu-total_dialokasi");
    const sisa_alokasi = $("#fm-keu-sisa_belum_dialokasi");
    const nominal = removeComma($("#bt-bayar_tagihan_um").val());

    let total = 0;
    $(".item-alokasi").each(function () {
      total += removeComma($(this).val());
    });

    let sisa = nominal - total;

    if (total > nominal) {
      Swal.fire({
        icon: "warning",
        title: "Total alokasi melebihi nominal",
        text: "Nominal akan disesuaikan dengan total alokasi",
        showConfirmButton: false,
      });
      sisa = 0;
      if (e) {
        e.value = 0;
      }
    }
    alokasi.html(num_format(nominal));
    sisa_alokasi.html(num_format(sisa));
  }

  if (e) {
    const input = $(e);
    const currentValue = removeComma(input.val());
    const rawItemMax = input.attr("data-item-max");
    const itemMax = rawItemMax === "" || rawItemMax === undefined
      ? null
      : keuToNumber(rawItemMax);
    const nominal = removeComma($("#bt-bayar_tagihan_um").val());
    // Hard cap: total alokasi tidak boleh melebihi nominal pembayaran (yang sendiri
    // sudah dibatasi oleh sisa tagihan lewat ubahMaksNominal).
    const paymentRemaining = Math.max(0, nominal - keuAllocatedTotal(e));

    if (currentValue > paymentRemaining) {
      input.val(paymentRemaining).keyup();
      Swal.fire({
        icon: "warning",
        title: "Nominal alokasi melebihi batas",
        text: "Nominal disesuaikan dengan sisa nominal pembayaran",
        showConfirmButton: false,
        timer: 1800,
      });
      recomputeAlokasiTotals();
      return;
    }

    // Soft cap: item boleh dibayar lebih dari nominal yang ditagihkan untuk
    // kategori ini, asal dikonfirmasi (mis. booking fee 0 tapi ingin bayar 1jt).
    if (itemMax !== null && currentValue > itemMax) {
      Swal.fire({
        icon: "warning",
        title: "Nominal melebihi tagihan kategori ini",
        text: "Nominal alokasi lebih besar dari nominal yang ditagihkan untuk item ini. Tetap gunakan nominal ini?",
        showCancelButton: true,
        confirmButtonText: "Ya, tetap gunakan",
        cancelButtonText: "Tidak, sesuaikan",
      }).then((result) => {
        if (!result.isConfirmed) {
          input.val(itemMax).keyup();
        }
        recomputeAlokasiTotals();
      });
      return;
    }
  }

  recomputeAlokasiTotals();
}


function save_sb(id) {
  let i = $("#sb_btn" + id).prop("checked") ? 1 : 0;
  $.ajax({
    url: base_url + "keuangan/save_sb",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_keuangan: id,
      sb: i,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      csrfHash = r.token;
      $("#loading").addClass("hidden");
      if (r.success === false) {
        Swal.fire({
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
        });
      }
    },
  });
}

$("#bt-for, #bt-for_bb").select2();
var keu_tg,
  keu_lp,
  keu_nom_ll,
  keu_nom_bb,
  keu_sb,
  keu_item_sudah_bayar,
  keu_biaya_mkdt = {},
  keu_current_id_mkdt,
  keu_total_item_sudah_bayar = 0,
  keu_total_sudah_bayar = 0,
  keu_riwayat_loaded = false,
  keu_riwayat_loading = false;

function keuToNumber(value) {
  if (value === null || value === undefined || value === "") return 0;
  return parseFloat(String(value).replace(/,/g, "")) || 0;
}

function keuEscapeHtml(value) {
  return $("<div>").text(value === null || value === undefined ? "" : value).html();
}

function keuEscapeAttribute(value) {
  return keuEscapeHtml(value)
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;")
    .replace(/`/g, "&#096;");
}

function renderBiayaMkdt(biaya = {}) {
  keu_biaya_mkdt = biaya || {};
  $("#fm-keu-biaya-mkdt [data-biaya-mkdt]").each(function () {
    const key = $(this).data("biaya-mkdt");
    $(this).html("Rp. " + num_format(keuToNumber(biaya[key])));
  });
}

function keuNormalizeText(value) {
  return String(value || "").toLowerCase();
}

function keuFindPaidItem(item) {
  const id = String(item?.id_keuangan_item_list || "");
  return (Array.isArray(keu_item_sudah_bayar) ? keu_item_sudah_bayar : []).find(
    (paid) => String(paid.id_keuangan_item_list || "") === id,
  );
}

function keuPaidByItem(item) {
  const paid = keuFindPaidItem(item);
  return keuToNumber(paid?.total_nominal);
}

function keuItemLimit(item) {
  const id = String(item?.id_keuangan_item_list || "");
  const name = keuNormalizeText(item?.item);
  const category = keuNormalizeText(item?.kategori);
  const biaya = keu_biaya_mkdt || {};

  if (id === "1" || name.includes("booking")) return keuToNumber(biaya.booking_fee);
  if (id === "2" || category === "um" || name.includes("uang muka")) return keuToNumber(biaya.total_um);
  if (id === "3" || category === "adm" || name.includes("administrasi")) return keuToNumber(biaya.harga_administrasi);
  if (id === "6" || name.includes("bphtb")) return keuToNumber(biaya.harga_bphtb);
  if (id === "7" || name.includes("proses")) return keuToNumber(biaya.harga_biaya_proses);
  if (id === "8" || name.includes("ppn")) return keuToNumber(biaya.harga_ppn);
  if (id === "9" || name.includes("turun")) return keuToNumber(biaya.harga_penambahan_um);
  if (name.includes("kelebihan") || name.includes("tanah")) return keuToNumber(biaya.harga_penambahan_tanah);
  if (name.includes("kavling") || name.includes("siap")) return keuToNumber(biaya.harga_penambahan);

  return null;
}

function keuItemRemaining(item) {
  const limit = keuItemLimit(item);
  if (limit === null) return null;
  return Math.max(0, limit - keuPaidByItem(item));
}

function keuAllocatedTotal(exceptEl = null) {
  let total = 0;
  $(".item-alokasi").each(function () {
    if (exceptEl && this === exceptEl) return;
    total += removeComma($(this).val());
  });
  return total;
}

function keuAllocationAutoNominal(item, exceptEl = null) {
  const paymentNominal = removeComma($("#bt-bayar_tagihan_um").val());
  const paymentRemaining = Math.max(0, paymentNominal - keuAllocatedTotal(exceptEl));
  const itemRemaining = keuItemRemaining(item);

  if (itemRemaining === null) {
    return paymentRemaining;
  }

  return Math.min(paymentRemaining, itemRemaining);
}

function loadKeuanganRiwayatLazy(done) {
  if (keu_riwayat_loaded) {
    loadLogPembayaran(keu_lp);
    if (typeof done === "function") done();
    return;
  }

  if (keu_riwayat_loading || !keu_current_id_mkdt) {
    if (typeof done === "function") done();
    return;
  }

  keu_riwayat_loading = true;
  $.ajax({
    url: base_url + "tagihan/riwayat/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: keu_current_id_mkdt,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;
      if (r.success === false) {
        loaded["keu_lp"] = false;
        swal("error", r.messages || "Riwayat pembayaran tidak ditemukan");
        return;
      }
      keu_lp = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [];
      keu_sb = keu_lp;
      keu_riwayat_loaded = true;
      loadLogPembayaran(keu_lp);
    },
    error: function (xhr, st, err) {
      loaded["keu_lp"] = false;
      swal("error", "Terjadi kesalahan saat memuat riwayat pembayaran", err);
    },
    complete: function () {
      keu_riwayat_loading = false;
      if (typeof done === "function") done();
    },
  });
}

function open_keuangan(sh, role, id_kavling) {
  loading(true);
  $("#tb-alokasi-dana").html("");
  alokasi_items = [];

  loaded = [];
  keu_lp = [];
  keu_tg = [];
  keu_current_id_mkdt = sh.data.id_mkdt;
  keu_item_sudah_bayar = [];
  keu_total_item_sudah_bayar = 0;
  keu_total_sudah_bayar = 0;
  keu_riwayat_loaded = false;
  keu_riwayat_loading = false;
  renderBiayaMkdt({});

  keu_sb = [];
  keu_nom_bb = 0;
  keu_nom_ll = 0;

  state.sisa_cicilan = 0;
  state.sudah_bayar = 0;
  state.total_cicilan = 0;

  $("#tagihan-tab").tab("show");
  $("#fm-keuangan")[0].reset(); //reset form
  $("#label_konsumen").html(""); //reset label nama
  $(
    "#tb-data-log_pembayaran, #tb-data-log_pembayaran_bb, #tb-data-tagihan, #tb-data-tagihan_bb",
  ).empty(); //reset table log
  $("#booking_fee_paid, #keu_booking_fee").prop("disabled", false); //set disabled false untuk input booking

  // document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = false; //set disabled false untuk input tanggal booking

  $("#hide_lunas").removeClass("hidden");
  $("#hide_refund").addClass("hidden");

  $("#is_lunas").prop("checked", false).change();

  $(".id_kavling").val(id_kavling);
  $("#fm-keuangan #id_mkdt").val(sh.data.id_mkdt);

  $("#add-form-btn-keuangan").prop("disabled", false);
  $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop(
    "disabled",
    0,
  );
  document.querySelector("#tanggal_refund")._flatpickr._input.disabled = false;

  $.ajax({
    url: base_url + "tagihan/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      // id_keuangan: sh.data.id_keuangan,
      // id_kavling: id_kavling,
      id_mkdt: sh.data.id_mkdt,
      include_log: 0,
      // id_hargajual: sh.data2.id_hargajual,
    },
    dataType: "json",
    beforeSend: function () {
      loading(true);
    },
    success: function (r) {
      loading(false);
      let mkdt = r.mkdt,
        sb = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [],
        lp = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [],
        disabled = "";
      tg = r.tagihan;
      csrfHash = r.token;
      keu_total_sudah_bayar = keuToNumber(r.total_sudah_bayar);
      keu_item_sudah_bayar = Array.isArray(r.item_sudah_bayar)
        ? r.item_sudah_bayar
        : [];
      keu_total_item_sudah_bayar = keuToNumber(r.total_item_sudah_bayar);
      renderBiayaMkdt(Object.assign({}, mkdt || {}, r.biaya_mkdt || {}));

      if (!Array.isArray(tg) || tg.length === 0) {
        Swal.fire({
          icon: "error",
          title: "Oops!",
          text: "Belum ada konsumen dan tagihannya",
          showConfirmButton: false,
        });
        return;
      }

      let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;

      //load label alamat
      let label_alamat = setLabelAlamat(
        nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      //load label konsumen
      $("#fm-bayar-label_konsumen").html(mkdt.nama_konsumen);
      $("#fm-bayar-label_tgl").html(format_date(mkdt.booking_tgl));
      $("#fm-bayar-label_bookingfee").html(num_format(mkdt.booking_fee));

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });

      //load detail biaya dari mkdt
      $("#label_konsumen").html(mkdt.nama_konsumen);

      if (mkdt) {
        $("#fm-keuangan #status_mkdt").val(mkdt.status_mkdt);

        //jika status batal
        if (mkdt.status_mkdt == "Batal") {
          $("#hide_lunas").addClass("hidden");
          $("#hide_refund").removeClass("hidden");
        }

        // console.log(hitung_total(true, mkdt));

        //matikan tombol simpan jika sudah refund
        if (mkdt.refund_paid == 1) {
          $("#add-form-btn-keuangan").prop("disabled", true);
          $("#hide_lunas").addClass("hidden");
          $(
            "#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid",
          ).prop("disabled", 1);
          $("#fm-keuangan #refund_paid").prop("checked", 1);

          $("#keterangan_refund").val(mkdt.refund_keterangan).change();
          $("#nominal_refund").val(mkdt.refund).change();

          setDatePicker(mkdt.refund_tgl, "#tanggal_refund");
          document.querySelector("#tanggal_refund")._flatpickr._input.disabled =
            true;

          disabled = "disabled";
        }

        //matikan tombol simpan jika sudah lunas
        if (mkdt.is_lunas == 1) {
          $("#is_lunas").prop("checked", true);
          // $(".hide_lunas").addClass("hidden");
          // disabled = "disabled"
        }

        $("#fm-keuangan #nama_konsumen").val(mkdt.nama_konsumen);

        $("#bt-total_biaya_um")
          .val(
            parseFloat(mkdt.harga_uang_muka) -
              parseFloat(mkdt.harga_diskon_uang_muka),
          )
          .change();
        // $("#bt-total_biaya_um_ll")
        //   .val(
        //     parseFloat(mkdt.harga_penambahan_um) +
        //       parseFloat(mkdt.harga_penambahan) +
        //       parseFloat(mkdt.harga_penambahan_tanah) +
        //       parseFloat(mkdt.harga_administrasi)
        //   )
        //   .change();

        // $("#bt-total_biaya_bb")
        //   .val(
        //     parseFloat(mkdt.harga_bphtb) +
        //       parseFloat(mkdt.harga_biaya_proses) +
        //       parseFloat(mkdt.harga_ppn)
        //   )
        //   .change();

        //set checnkbox value to prevent being 0 by automatic load data
        $("#fm-keuangan #is_lunas").val(1);

        // setDatePicker(mkdt.booking_tgl, "#fm-keuangan #booking_tgl");
        // setDatePicker(mkdt.booking_tgl, "#keu_booking_tgl");

        $(".num").keyup().change();
        // total('#fm-keuangan');

        $("#fm-keuangan #booking_fee").val(mkdt.booking_fee).change();
        $("#keu_booking_fee").val(mkdt.booking_fee).change();

        //set booking paid/not
        // $("#booking_fee_paid").val(1);
        // if (mkdt.booking_paid == 1) {
        //   $("#booking_fee_paid, #keu_booking_fee").prop("disabled", true);
        //   document.querySelector(
        //     "#keu_booking_tgl"
        //   )._flatpickr._input.disabled = true;
        // }
      }
      //untuk load data sudah bayar
      keu_sb = sb;

      //load_table tagihan
      keu_tg = tg;
      state.total_cicilan = tg
        .filter((item) => Number(item.is_void) !== 1)
        .reduce((sum, item) => sum + parseInt(item.nominal, 10), 0);

      // /************************ load table log pembayaran ***************************/
      //   load table riwayat bayar
      keu_lp = lp;

      /************************ end of load table log pembayaran ***************************/

      loadTableTagihan(tg);
      loaded["keu_tg"] = true;

      removeModalListener("#modal_divisi3");
      initModalListener("#modal_divisi3");
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan saat memuat data", err);
    },
  });
}

// Refresh modal content in place after a successful save/delete, without
// closing the modal (unlike open_keuangan, which also opens it from scratch).
function refreshKeuanganModal(clearEntryForm = false) {
  if (!keu_current_id_mkdt) return;

  $.ajax({
    url: base_url + "tagihan/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: keu_current_id_mkdt,
      include_log: 0,
    },
    dataType: "json",
    success: function (r) {
      csrfHash = r.token;

      let mkdt = r.mkdt;
      let tg = r.tagihan;
      let sb = Array.isArray(r.log_pembayaran) ? r.log_pembayaran : [];

      if (!Array.isArray(tg) || tg.length === 0) {
        swal("error", "Belum ada konsumen dan tagihannya");
        return;
      }

      keu_total_sudah_bayar = keuToNumber(r.total_sudah_bayar);
      keu_item_sudah_bayar = Array.isArray(r.item_sudah_bayar)
        ? r.item_sudah_bayar
        : [];
      keu_total_item_sudah_bayar = keuToNumber(r.total_item_sudah_bayar);
      renderBiayaMkdt(Object.assign({}, mkdt || {}, r.biaya_mkdt || {}));

      if (mkdt) {
        $("#fm-keuangan #status_mkdt").val(mkdt.status_mkdt);

        $("#hide_lunas").removeClass("hidden");
        $("#hide_refund").addClass("hidden");
        if (mkdt.status_mkdt == "Batal") {
          $("#hide_lunas").addClass("hidden");
          $("#hide_refund").removeClass("hidden");
        }

        if (mkdt.refund_paid == 1) {
          $("#add-form-btn-keuangan").prop("disabled", true);
          $("#hide_lunas").addClass("hidden");
          $(
            "#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid",
          ).prop("disabled", 1);
          $("#fm-keuangan #refund_paid").prop("checked", 1);
          $("#keterangan_refund").val(mkdt.refund_keterangan).change();
          $("#nominal_refund").val(mkdt.refund).change();
          setDatePicker(mkdt.refund_tgl, "#tanggal_refund");
          document.querySelector("#tanggal_refund")._flatpickr._input.disabled =
            true;
        } else {
          $("#add-form-btn-keuangan").prop("disabled", false);
        }

        $("#is_lunas").prop("checked", mkdt.is_lunas == 1);
        $("#fm-bayar-label_tgl").html(format_date(mkdt.booking_tgl));
        $("#fm-bayar-label_bookingfee").html(num_format(mkdt.booking_fee));
      }

      keu_sb = sb;
      keu_lp = sb;
      keu_tg = tg;
      state.total_cicilan = tg
        .filter((item) => Number(item.is_void) !== 1)
        .reduce((sum, item) => sum + parseInt(item.nominal, 10), 0);

      loadTableTagihan(tg);

      // Paksa tab Riwayat Pembayaran mengambil data terbaru, apapun tab yang aktif.
      keu_riwayat_loaded = false;
      loadKeuanganRiwayatLazy();

      if (clearEntryForm) {
        $("#bt-for").val(null).trigger("change");
        // Kosongkan tabel alokasi dulu sebelum reset nominal, supaya .change()
        // di bawah ini tidak menghitung ulang total alokasi lama vs nominal 0
        // (yang selalu memicu warning "Total alokasi melebihi nominal" palsu).
        alokasi_items = [];
        $("#tb-alokasi-dana").html("");
        $("#bt-bayar_tagihan_um").val("").keyup().change();
        $("#bt-berita_acara_um").val("");
        if (document.querySelector("#bt-tanggal_bayar_um")._flatpickr) {
          document.querySelector("#bt-tanggal_bayar_um")._flatpickr.clear();
        }
        setAlokasi();
      }
    },
    error: function (xhr, st, err) {
      swal("error", "Terjadi kesalahan saat memuat data terbaru", err);
    },
  });
}

function loadKeuSB(sb) {
  let nom = 0,
    tot = state.total_cicilan,
    sisa = 0,
    prs = 0;
  // nom_bb = 0,
  // tot_bb = removeComma($("#bt-total_biaya_bb").val()) || 0,
  // sisa_bb = 0,
  // prs_bb = 0,
  // nom_ll = 0,
  // // tot_ll = removeComma($("#bt-total_biaya_um_ll").val()) || 0,
  // sisa_ll = 0,
  // prs_ll = 0;

  const fallbackSudahBayar = keu_total_item_sudah_bayar > 0
    ? keu_total_item_sudah_bayar
    : keu_total_sudah_bayar;

  if ((!Array.isArray(sb) || sb.length === 0) && fallbackSudahBayar > 0) {
    nom = fallbackSudahBayar;
    nom = nom > tot ? tot : nom;
    prs = nom == 0 || tot == 0 ? 0 : (nom / tot) * 100;

    return {
      total_sudah_bayar: nom,
      sisa_tagihan: sisa,
      persentase: prs.toFixed(2) + "%",
    };
  }

  $.each(sb, function (i, v) {
    if (v.payment_type != "Booking") {
      nom += parseFloat(v.nominal) || 0;

      // let sp = v.payment_type.split(";");
      // if (sp.includes("Uang Muka")) nom += parseFloat(v.nominal) || 0;
      // if (v.status == "UM") nom_ll += parseFloat(v.nominal) || 0;
      // else if (v.status == "BB") nom_bb += parseFloat(v.nominal) || 0;
    }
  });
  nom = nom > tot ? tot : nom;
  // sisa = tot - nom;
  // sisa_bb = tot_bb - nom_bb;

  prs = nom == 0 || tot == 0 ? 0 : (nom / tot) * 100;

  return {
    total_sudah_bayar: nom,
    sisa_tagihan: sisa,
    persentase: prs.toFixed(2) + "%",
  };

  // prs_bb = nom_bb == 0 ? 0 : (nom_bb / tot_bb) * 100;

  // $("#bt-sudah_bayar_um").val(nom).keyup();
  // $("#bt-sisa_tagihan_um").val(sisa).keyup();

  // $("#bt-persentase_bayar_tagihan_um").val(prs.toFixed(2) + "%");

  // $("#bt-sudah_bayar_bb").val(nom_bb).keyup();
  // $("#bt-sisa_tagihan_bb").val(sisa_bb).keyup();

  // $("#bt-persentase_bayar_tagihan_bb").val(prs_bb.toFixed(2) + "%");

  // keu_nom_bb = nom_bb;
  // keu_nom_ll = nom_ll;
}

function renderKeuSubItemSudahBayar() {
  const items = Array.isArray(keu_item_sudah_bayar)
    ? keu_item_sudah_bayar.filter((item) => keuToNumber(item.total_nominal) > 0)
    : [];

  if (items.length === 0) {
    return '<div class="keu-payment-empty">Belum ada detail alokasi pembayaran tercatat.</div>';
  }

  return items
    .map((item) => {
      const label = keuEscapeHtml(item.item || "-");
      const kategori = item.kategori || item.item || "-";
      const badge = kategori
        ? `<span class="keu-payment-allocation-badge">${keuEscapeHtml(kategori)}</span>`
        : "";

      return `
        <div class="keu-payment-allocation-row">
          <div class="keu-payment-allocation-label">
            ${badge}
            <span class="keu-payment-allocation-name">${label}</span>
          </div>
          <div class="keu-payment-allocation-value">Rp ${num_format(keuToNumber(item.total_nominal))}</div>
        </div>`;
    })
    .join("");
}

function renderKeuPaymentSummary(totalTagihan, sudahBayar) {
  const totalAlokasiItem = Array.isArray(keu_item_sudah_bayar)
    ? keu_item_sudah_bayar.reduce(
        (sum, item) => sum + keuToNumber(item.total_nominal),
        0,
      )
    : 0;
  const sisaTagihan = Math.max(totalTagihan - sudahBayar, 0);
  const paidPercent = totalTagihan > 0
    ? Math.max(0, Math.min(100, (sudahBayar / totalTagihan) * 100))
    : 0;
  const progressClass = paidPercent <= 0
    ? "is-empty"
    : paidPercent >= 100
      ? ""
      : "is-partial";

  return `
    <div class="keu-payment-summary">
      <div class="keu-payment-summary-header">
        <span>Ringkasan Pembayaran</span>
      </div>
      <div class="keu-payment-percent">${paidPercent.toFixed(0)}%</div>

      <div class="keu-payment-primary-label">Sisa Tagihan</div>
      <div class="keu-payment-primary-value">Rp ${num_format(sisaTagihan)}</div>

      <div class="keu-payment-metric-row">
        <span class="keu-payment-metric-label">Total Tagihan</span>
        <span class="keu-payment-metric-value">Rp ${num_format(totalTagihan)}</span>
      </div>
      <div class="keu-payment-metric-row">
        <span class="keu-payment-metric-label">Sudah Bayar</span>
        <span class="keu-payment-metric-value is-paid">Rp ${num_format(sudahBayar)}</span>
      </div>
      <div class="keu-payment-progress-track">
        <div class="keu-payment-progress-fill ${progressClass}" style="width:${paidPercent}%"></div>
      </div>

      <div class="keu-payment-detail-title">Breakdown Pembayaran</div>
      ${renderKeuSubItemSudahBayar()}

      <div class="keu-payment-allocation-total">
        <span>Total Breakdown Pembayaran</span>
        <span class="keu-payment-allocation-value">Rp ${num_format(totalAlokasiItem)}</span>
      </div>
    </div>`;
}

function loadTableTagihan(tg) {
  let sudah_bayar = loadKeuSB(keu_sb);
  state.sudah_bayar = sudah_bayar.total_sudah_bayar;
  // if (!loaded["keu_sb"]) {
  //   sudah_bayar =  loadKeuSB(keu_sb);
  // }
  $("#tb-data-tagihan").html("");

  let tr_tg = "",
    no = 1,
    tot_tg = 0,
    sb_button = "",
    chkd = "",
    opt = "",
    dsb = "",
    disabled = "";
  $("#bt-for").html("");
  $.each(tg, function (i, v) {
    chkd = "";
    dsb = "";

    if (Number(v.is_void) === 1) {
      tr_tg += `
        <div class="p-1 mb-1 rounded border text-muted">
          <div class="row">
            <div class="col-12">
                <h5><strong>${keuEscapeHtml(v.berita_acara)}</strong> <span class="badge badge-secondary" title="${keuEscapeAttribute(v.void_reason || "")}">Void</span></h5>
                <h5><strong>Rp. ${num_format(v.nominal)}</strong></h5>
                <small class="muted">Jatuh Tempo: ${format_date(
                  v.jatuh_tempo_tgl,
                )}</small>
                ${v.void_reason ? `<small class="text-danger d-block">Alasan void: ${keuEscapeHtml(v.void_reason)}</small>` : ""}
            </div>
          </div>
        </div>
    `;
      no++;
      return; // is_void: excluded from tot_tg / #bt-for, still shown for visibility
    }

    if (v.sudah_dibayar == 1) {
      chkd = "checked";
      // dsb = "disabled"
    }
    sb_button = `
        <div class="form-group">
            <div class="custom-control custom-switch custom-control-inline">
                <input type="checkbox" ${chkd} onchange="save_sb(${v.id_keuangan})" class="custom-control-input " ${disabled} id="sb_btn${v.id_keuangan}" name="sb_btn[${v.id_keuangan}]" value="1" />
                <label class="custom-control-label" for="sb_btn${v.id_keuangan}"></label>
            </div>
        </div>`;

    tot_tg += keuToNumber(v.nominal);
    tr_tg += `
        <div class="p-1 mb-1 rounded border" style="">
          <div class="row">
            <div class="col-9">
                <h5 class="text-primary"><strong>${v.berita_acara}</strong></h5>
                <h5 class="text-success"><strong>Rp. ${num_format(
                  v.nominal,
                )}</strong></h5>
                <small class="muted">Jatuh Tempo: ${format_date(
                  v.jatuh_tempo_tgl,
                )}</small>
            </div>
            <div class="col-3 text-right">
              ${sb_button}
            </div>
          </div>
        </div>
    `;
    no++;

    opt += `<option ${dsb} value='${v.id_keuangan}'>${v.berita_acara}</option>`;
  });

  tr_tg += renderKeuPaymentSummary(tot_tg, sudah_bayar.total_sudah_bayar);

  $("#bt-for").append(opt);
  //   $("#bt-for_bb").append(opt);

  $("#tb-data-tagihan").append(tr_tg);
  //   $("#tb-data-tagihan_bb").append(tr_tg_bb);
}

function loadLogPembayaran(lp) {
  if (!loaded["keu_sb"]) {
    loadKeuSB(keu_sb);
  }
  $("#tb-data-log_pembayaran").html("");
  let t = "",
    tot_lp = 0,
    no = 1;

  $.each(lp, function (k, v) {
    let detail = v.detail;
    let item = "";
    $.each(detail, function (k2, v2) {
      item += `<strong>${v2.item}</strong>: Rp. ${num_format(v2.nominal)}<br>`;
    });

    tot_lp += parseInt(v.nominal);
    t += `
      <tr>
        <td>${no}</td>
        <td>${format_date(v.tanggal_bayar)}</td>
        <td style="text-align:right">${num_format(v.nominal)}</td>
        <td class="text-left">Untuk Pembayaran: ${
          v.payment_type
        }<br>Dengan Detail: <br>${item}</td>
        <td>
          ${v.username}<br/>
          ${format_datetime(v.created_at)}
        </td>
        <td>
          <div class="btn-group">
            <button
              type="button"
              class="btn btn-outline-primary waves-effect btn-sm"
              onclick="printRiwayatBayar('${v.id_pembayaran}', '${
                v.id_mkdt
              }', '${dt_proyek["id_proyek"]}')"
            >
              <i class="fa fa-print"></i>
            </button>

            <button
              type="button"
              class="btn btn-outline-danger waves-effect btn-sm"
              onclick="removeRiwayatBayar('${v.id_pembayaran}')"
            >
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    no++;
  });

  t +=
    "<tr>" +
    "<th colspan='2'>Total</th>" +
    "<th style='text-align:right'>" +
    num_format(tot_lp) +
    "</th>" +
    "<th colspan='3'></th>" +
    "<tr>";

  $("#tb-data-log_pembayaran").append($(t).hide().slideDown(2000));
}

function printRiwayatBayar(ee, e2, e3) {
  $("#btnDetail").off("click");
  $("#btnUangMuka").off("click");
  Swal.fire({
    title: "Pilih Jenis Kuitansi",
    text: "Silakan pilih jenis cetakan yang ingin dibuat:",
    showCancelButton: true,
    showConfirmButton: false,
    cancelButtonText: "Batal",
    html: `
    <div class="d-grid gap-2">
      <button id="btnDetail" class="swal2-confirm swal2-styled" style="background:#0d6efd">
        Kuitansi Detail
      </button>
      <button id="btnUangMuka" class="swal2-confirm swal2-styled" style="background:#198754">
        Kuitansi Uang Muka
      </button>
    </div>
  `,
  });
  $("#btnDetail").on("click", function () {
    Swal.close();
    var myWindow = window.open(
      base_url + `pembayaran/kuitansi/cetak?e=${ee}&e2=${e2}&e3=${e3}`,
      "_blank",
      "top=100,left=300,width=700,height=600",
    );
    setTimeout(function () {
      myWindow.focus();
    }, 1000);
  });
  $("#btnUangMuka").on("click", function () {
    Swal.close();
    var myWindow = window.open(
      base_url + `pembayaran/kuitansi-um/cetak?e=${ee}&e2=${e2}&e3=${e3}`,
      "_blank",
      "top=100,left=300,width=700,height=600",
    );
    setTimeout(function () {
      myWindow.focus();
    }, 1000);
  });
}

function removeRiwayatBayar(e) {
  Swal.fire({
    title: "Hapus Data?",
    text: "Apakah anda yakin akan menghapus data?",
    // type: 'warning',
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      $.ajax({
        url: base_url + "pembayaran/hapus",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id_pembayaran: e,
        },
        dataType: "json",
        beforeSend: function () {
          $("#loading").removeClass("hidden");
        },
        success: function (r) {
          $("#loading").addClass("hidden");
          csrfHash = r.token;
          if (r.success) {
            Swal.fire({
              //position: 'bottom-end',
              icon: "success",
              title: r.messages,
              showConfirmButton: false,
              timer: 1500,
            }).then(function () {
              refreshKeuanganModal(false);
              if (typeof isi_data === "function") {
                isi_data(true); // refresh list di background, modal tetap terbuka
              }
            });
          } else {
            Swal.fire({
              //position: 'bottom-end',
              icon: "error",
              title: r.messages,
              showConfirmButton: false,
              timer: 1500,
            });
          }
        },
        error: function (e) {
          $("#loading").addClass("hidden");
          Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "Terjadi Kesalahan",
            showConfirmButton: true,
            // timer: 1500
          });
        },
      });
    }
  });
}

function save_keuangan(e = "") {
  if (keuSubmitInProgress) return;

  let nominal = removeComma($("#bt-bayar_tagihan_um").val());
  let tanggal = $("#bt-tanggal_bayar_um").val();
  let metode = $("#bt-for").val();
  if (metode == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Angsuran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-for").focus();
    });
    return;
  }
  if (tanggal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Tanggal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-tanggal_bayar_um").focus();
    });
    return;
  }
  if (nominal == 0 || nominal == "") {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Nominal pembayaran belum diisi",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  let total = 0;
  $(".item-alokasi").each(function () {
    total += removeComma($(this).val());
  });

  if (total != nominal) {
    Swal.fire({
      icon: "warning",
      title: "Peringatan",
      text: "Total alokasi tidak sesuai dengan nominal pembayaran",
      showConfirmButton: false,
      timer: 1500,
    }).then(() => {
      $("#bt-bayar_tagihan_um").focus();
    });
    return;
  }

  keuSubmitInProgress = true;
  simpanBtn(".add-form-btn-keuangan", true);

  Swal.fire({
    title: "Simpan Data?",
    text: "",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.value) {
      let text_um = [];

      $("#bt-for option:selected").each(function () {
        text_um.push($(this).text()); // Get the text of the selected option
      });

      // Join the texts with semicolon
      text_um = text_um.join(";");
      text_um = text_um != "" ? text_um + ";" : text_um;

      let text_bb = [];

      $("#bt-for_bb option:selected").each(function () {
        text_bb.push($(this).text()); // Get the text of the selected option
      });

      // Join the texts with semicolon
      text_bb = text_bb.join(";");

      text_bb = text_bb != "" ? text_bb + ";" : text_bb;

      $.ajax({
        url: base_url + "pembayaran/simpan",
        type: "post",
        data:
          $("#fm-keuangan").serialize() +
          "&" +
          csrfName +
          "=" +
          csrfHash +
          "&text_um=" +
          text_um +
          "&text_bb=" +
          text_bb +
          "&e=" +
          e +
          "&cis_lunas=" +
          $("#is_lunas").prop("checked"),
        dataType: "json",
        success: function (r) {
          csrfHash = r.token;
          if (r.status === true) {
            swal("success", r.message);

            // Berhasil disimpan: refresh konten modal di tempat, jangan tutup modal.
            refreshKeuanganModal(true);

            if (typeof isi_data === "function") {
              isi_data(true); // refresh list di background, modal tetap terbuka
            }
          } else {
            swal("error", r.message || r.messages || "Terjadi kesalahan");
          }
          keuSubmitInProgress = false;
          simpanBtn(".add-form-btn-keuangan", false);

          // load_kavling();
          // hapus_seleksi();
        },
        error: function (e, f, g) {
          keuSubmitInProgress = false;
          simpanBtn(".add-form-btn-keuangan", false);
          swal("error", g);
        },
      });
    } else {
      keuSubmitInProgress = false;
      simpanBtn(".add-form-btn-keuangan", false);
      return false;
    }
  });
}

$(function () {
  $("#log_pembayaran-tab")
    .off("shown.bs.tab.tagihanBayarModal")
    .on("shown.bs.tab.tagihanBayarModal", function () {
      loadKeuanganRiwayatLazy();
    });
});