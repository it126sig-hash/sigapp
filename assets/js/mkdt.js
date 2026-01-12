$("#id_bank").select2({
  placeholder: "Pilih Bank",
  allowClear: true,
  ajax: {
    url: base_url + "api/bank",
    dataType: "json",
    delay: 250,
    method: "get",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      // csrfHash = r.token;

      let results = [];
      $.each(r, function (i, v) {
        results.push({
          id: v.id,
          text: `${v.bank}${v.keterangan ? ": (" + v.keterangan + ")" : ""}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

// $('a[data-toggle="tab"]').on("show.bs.tab", function (e) {
//   const targetId = $(e.target).attr("href"); // ex: #profile
//   if (
//     targetId === "#idk_data_konsumen" ||
//     targetId === "#idk_biaya" ||
//     targetId === "#idk_tagihan"
//   ) {
//     if (!state.status.tab.isClosed) {
//       let isValid = isValidKonsumen(getActiveIndex());

//       if (isValid != undefined && !isValid[getActiveIndex()]) {
//         e.preventDefault(); // mencegah tab berpindah
//         return;
//       }
//     }
//   }
// });

// tab button

const containerIsiKonsumen = $("#tab-isi-konsumen");

// Array urutan tab
// const tabOrder = ["#idk_data_konsumen", "#idk_biaya", "#idk_tagihan"];

// Ambil index tab aktif
// function getActiveIndex() {
//   const activeId = containerIsiKonsumen.find(".tab-pane.active").attr("id");
//   return tabOrder.findIndex((sel) => sel === "#" + activeId);
// }

// Pindah ke tab ke-i
// function goTo(i) {
//   if (i < 0 || i >= tabOrder.length) return;
//   containerIsiKonsumen.find('a[href="' + tabOrder[i] + '"]').tab("show");
// }

// Update tombol
// function updateButtons(next, prev) {
//   const i = getActiveIndex();
//   const bPrev = $(prev);
//   const bNext = $(next);

//   bPrev.prop("disabled", i === 0);

//   if (i === tabOrder.length - 1) {
//     bNext
//       .html('Simpan <i class="fa fa-save" aria-hidden="true"></i>')
//       .data("action", "save")
//       .removeClass("btn-primary")
//       .addClass("btn-success");
//     return true;
//   } else {
//     bNext
//       .html('Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i>')
//       .data("action", "next")
//       .removeClass("btn-success")
//       .addClass("btn-primary");
//     return false;
//   }
// }

function isValidKonsumen(i) {
  let isValid = true;

  if (i == "#idk_biaya-tab" || i == "#idk_data_konsumen-tab") {
    $("#fm-idk_keu")
      .find("input.tab1[required], select.tab1[required]")
      .each(function () {
        let id = $(this).attr("id");
        let value = $(this).val().trim();

        if (value === "") {
          let labelText = $('label[for="' + id + '"]').text();
          isValid = false;
          showToast(labelText + " harus diisi", "warning");
          $(this).focus();
          this.reportValidity();
          return false; // Stop the $.each loop immediately if an invalid field is found
        }
      });
    return isValid;
  } else if (i == "#idk_tagihan-tab") {
    if ($("#idk-booking_tgl").val() == "") {
      showToast("Tanggal Booking harus diisi", "warning");
      $("#idk-booking_tgl").get(0)._flatpickr.open();
      isValid = false;
    } else if ($("#idk-booking_fee").val() == "") {
      showToast("Booking Fee harus diisi", "warning");
      $("#idk-booking_fee").focus();
      isValid = false;
    }

    return isValid;
  } else if (i == "save") {
    if (parseFloat(removeComma($("#mk-total_tot").val() || 0)) > 0) {
      if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
        showToast(
          "Total tagihan tida sesuai dengan total harus dibayar",
          "danger"
        );
        isValid = false;
      }
    }
    return isValid;
  }
}
// Klik NEXT/SIMPAN
function btnNext(next) {
  let isValid = isValidKonsumen(next);
  if (next === "save" && isValid) {
    Swal.fire({
      title: "Konfirmasi",
      text: "Apakah data sudah benar dan akan disimpan?",
      showDenyButton: true,
      confirmButtonText: "Simpan",
      denyButtonText: `Kembali`,
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        simpan_dt_konsumen_keuangan();
      }
    });
  } else {
    if (isValid) {
      $(next).tab("show");
    } else {
      return;
    }
  }
}
$("a.locked").on("click", function (e) {
  e.preventDefault();
  e.stopPropagation();
});

// ====== Helpers ======
const ui = {
  form: {
    kons: $("#fm-idk_keu"),
  },
  loading: $("#loading"),
  tabs: {
    konsumen: $("#idk_data_konsumen-tab"),
  },
  btn: {
    printSPPTB: $("#btn-print_spptb"),
    addKons: $("#add-form-btn-idk_keu"),
    prevKons: $("#prev-form-btn-idk_keu"),
    delKons: $("#delete-btn-idk_keu"),
  },
  fields: {
    id_kavling: $(".id_kavling"),
    id_mkdt: $("#idk-id_mkdt"),
    hargaAkhirSelect: $("#idk-harga_akhir"),
    rincian: $("#idk-rincian"),
    richText: () => $("#idk-rincian").prev(".richText-editor"),
    // file previews
    ktpHere: $("#idk-file_ktp-here"),
    npwpHere: $("#idk-file_npwp-here"),
    ddHere: $("#idk-file_data_diri-here"),
  },
};

function withLoading(fn) {
  ui.loading.removeClass("hidden");
  return Promise.resolve(fn()).finally(() => ui.loading.addClass("hidden"));
}

function disableForm(disabled) {
  ui.form.kons.find("input:text, select, textarea").prop("disabled", disabled);
}

function setVal(sel, val) {
  $(sel)
    .val(val ?? "")
    .triggerHandler("input");
}
function setDate(dateStr, sel) {
  if (
    dateStr &&
    dateStr !== "0000-00-00" &&
    document.querySelector(sel)?._flatpickr
  ) {
    document.querySelector(sel)._flatpickr.setDate(dateStr);
  }
}
function setRichText(html) {
  ui.fields.richText().trigger("setContent", html ?? "");
  ui.fields.rincian.html(html ?? "");
}
function setImgOrPlaceholder(
  $a,
  src,
  placeholder,
  width = "100%",
  height = "150px"
) {
  if (src == null) {
  }
  $a.prop("href", base_url + (src || placeholder));
}

// function updateButtons() {
//   // ganti logika lamamu jika perlu
//   ui.btn.add.prop("disabled", false);
//   ui.btn.prev.prop("disabled", false);
// }

function formatDateSafe(d) {
  return d ? format_date(d) : "-";
}

// ====== Data layer ======
function getTransaksiDetail({ id_mkdt, id_kavling, id_hargajual }) {
  return $.ajax({
    url: base_url + "transaksi/ambilsatu",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, id_mkdt, id_kavling, id_hargajual },
  });
}

// ====== Binders ======
function bindKavlingContext(sh) {
  // navigasi/tab & tombol
  ui.tabs.konsumen.tab("show");
  // updateButtons(ui.btn.addKons, ui.btn.prevKons);
  // reset form
  ui.form.kons[0].reset();
  ui.form.kons.find(".num").prop("disabled", false);

  $("#mk-total_bb, #mk-total_um").val(0);
  $("#list_cicilan_here").empty();
  $("#mk-total_cicilan_um, #total_cicilan_bb").val(0).triggerHandler("input");
  $("#id_list_keu, #id_list_keu_bb").val("");
  $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);
  $("#idk_data_baru").val(1);
  $("#idk-rincian").prev(".richText-editor").trigger("setContent", "");

  // set state dasar
  state.id_kavling = sh.data.id_kavling || sh.id.substr(3);
  state.id_mkdt = sh.data.id_mkdt || null;

  // isi hidden fields
  ui.fields.id_kavling.val(state.id_kavling);
  ui.fields.id_mkdt.val(state.id_mkdt);

  // tombol print
  if (state.id_mkdt == null) {
    ui.btn.printSPPTB
      .attr(
        "onclick",
        `return swal('error', 'Data konsumen harus disimpan terlebih dahulu');`
      )
      .attr("target", "")
      .prop("href", "#");
  } else {
    ui.btn.printSPPTB
      .attr("onclick", "")
      .prop(
        "href",
        `${base_url}print/spptb?id_mkdt=${state.id_mkdt}&id_kavling=${state.id_kavling}&id_proyek=${dt_proyek.id_proyek}`
      )
      .attr("target", "_blank");
  }
}

function fillPriceSection(h, dk) {
  if (!h?.hargajual) return;
  // masal: map kunci → #mk-*
  const mkMap = [
    "hargajual",
    "hargajual_net",
    "kpr",
    "uang_muka",
    "biaya_adm",
    "bphtb",
    "ppn",
    "biaya_proses",
    "harga_penambahan",
    "harga_penambahan_tanah",
  ];
  mkMap.forEach((k) => setVal(`#mk-${k}`, h[k]));

  setDatePicker(h.tgl_harga, "#mk-tgl_harga");
  setVal("#idk-tgl_harga", formatDateSafe(h.tgl_harga));
  setVal("#idk-harga_kpr", h.kpr);

  setVal("#idk-mkdt_hargajual", h.hargajual);
  $("#idk-mkdt_hargajual_by").text(dk?.username_harga_akhir ?? "-");
  $("#idk-mkdt_hargajual_tgl").text(formatDateSafe(dk?.harga_akhir_tgl));
}

function fillDiskresi(dk) {
  if (dk?.username_diskresi) {
    $("#idk-diskresi_st").removeClass("hidden");
    setVal("#idk-diskresi_harga", dk.diskresi_harga);
    setVal("#idk-diskresi_memo", dk.diskresi_memo);
    $("#idk-diskresi_oleh").text(dk.username_diskresi);
    $("#idk-diskresi_tgl").text(formatDateSafe(dk.diskresi_at));
  } else {
    $("#idk-diskresi_st").addClass("hidden");
    setVal("#idk-diskresi_harga", "-");
    setVal("#idk-diskresi_memo", "-");
    $("#idk-diskresi_oleh").text("-");
    $("#idk-diskresi_tgl").text("-");
  }
}

function fillFiles(v) {
  setImgOrPlaceholder(ui.fields.ktpHere, v?.ktp_lok, not_found);
  setImgOrPlaceholder(ui.fields.npwpHere, v?.npwp_lok, not_found, "90%");
  ui.fields.ddHere
    .html(v?.data_diri_lok ? "Klik untuk melihat file" : "Tidak ada data")
    .prop("href", base_url + (v?.data_diri_lok || not_found));
}

$("#idk-is_allin").change(function () {
  is_allin(this);
});

function is_allin(e) {
  if (e.value == "0") {
    harga_Total = $("#mk-tgt").val();
    $("#mk-harga_allin").hide();
  } else {
    harga_Total = $("#mk-harga_allin").val();
    $("#mk-harga_allin").show();
  }
  sum_mktotal();
}

function fillMkdt(v) {
  if (!v) return;

  if (v.status_mkdt === "Batal") {
    disableForm(true);
    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass("hidden");
    $("#idk-id_konsumen, #idk-id_keuangan0").val("");
    ui.btn.delKons.removeClass("hidden");
  }

  // console.log(v)

  if (v.id_konsumen) $("#idk_data_baru").val(0);

  // basic fields
  setVal("#idk-is_allin", v.is_allin);
  $("#idk-is_allin").change();

  setVal("#mk-harga_allin", v.harga_allin);

  setVal("#idk-status_mkdt", v.status_mkdt);
  setVal("#idk-keterangan_batal", v.keterangan_batal);
  setDate(v.booking_tgl, "#idk-booking_tgl");
  setVal("#idk-booking_fee", v.booking_fee);

  setVal("#idk-id_konsumen", v.id_konsumen);

  setVal("#st-mkdt-no_spptb", v.no_spptb);
  setVal("#idk-nama_konsumen", v.nama_konsumen);
  setVal("#idk-nik_konsumen", v.nik_konsumen);
  setVal("#idk-alamat_konsumen", v.alamat_konsumen);
  setVal("#idk-npwp_konsumen", v.npwp_konsumen);
  setVal("#idk-hp_konsumen", v.hp_konsumen);
  setVal("#idk-email_konsumen", v.email_konsumen);
  setVal("#idk-status_konsumen", v.status_konsumen);

  setVal("#idk-nama_instansi", v.nama_instansi);
  setVal("#idk-alamat_instansi", v.alamat_instansi);
  setVal("#idk-tel_instansi", v.tel_instansi);
  setVal("#idk-email_instansi", v.email_instansi);
  setVal("#idk-alamat_surat", v.alamat_surat);
  setVal("#idk-pekerjaan", v.pekerjaan);
  setVal("#idk-lama_bekerja", v.lama_bekerja);
  setVal("#idk-bidang_pekerjaan", v.bidang_pekerjaan);

  setVal("#idk-status_pernikahan", v.status_pernikahan);
  setVal("#idk-nama_pasangan", v.nama_pasangan);
  setVal("#idk-nik_pasangan", v.nik_pasangan);
  setVal("#idk-hp_pasangan", v.hp_pasangan);
  setVal("#idk-status_pekerjaan_pasangan", v.status_pekerjaan_pasangan);
  setVal("#idk-instansi_pasangan", v.instansi_pasangan);

  setVal("#idk-sales", v.sales);

  setVal("#idk-is_kpr", v.is_kpr);
  setVal("#idk-is_subsidi", v.is_subsidi);
  setVal("#idk-jenis_subsidi", v.jenis_subsidi);

  setRichText(v.rincian);

  // if (v.keuangan_saved_by) {
  setVal("#mk-hargajual", v.harga_jual);
  setVal("#mk-hargajual_net", v.harga_jual_net);
  setVal("#mk-kpr", v.harga_kpr);
  setVal("#mk-uang_muka", v.harga_uang_muka);
  setVal("#mk-biaya_adm", v.harga_administrasi);
  setVal("#mk-bphtb", v.harga_bphtb);
  setVal("#mk-ppn", v.harga_ppn);
  setVal("#mk-biaya_proses", v.harga_biaya_proses);
  setVal("#mk-harga_sbum", v.harga_sbum);
  setVal("#mk-harga_penambahan", v.harga_penambahan);
  setVal("#mk-harga_penambahan_tanah", v.harga_penambahan_tanah);
  // }

  setVal("#idk-promo", v.promo);

  // KPR turun
  // setVal("#mk-harga_kpr_acc", v.harga_kpr_acc);
  const turun_kpr = v.harga_kpr_acc ? v.harga_kpr - v.harga_kpr_acc : 0;
  // setVal("#mk-harga_penambahan_um", turun_kpr);

  // SPPTB file
  const spptbLink = v.file_spptb
    ? `<a href="${
        base_url + v.file_spptb
      }" target=_blank class="btn btn-outline-primary">Klik untuk melihat File SPPTB Yang Sudah ditandatangan</a>`
    : `Tidak ada data`;
  $("#spptb_ttd_file").html(spptbLink);
}

function fillSpptbList(list) {
  const html =
    list && list.length
      ? list
          .map(
            (val, i) => `
      <tr>
        <td>${i + 1}</td>
        <td><a href="${
          base_url + val.lokasi
        }" target=_blank>Klik untuk melihat file</a></td>
        <td>${val.username}<br>${format_datetime(val.created_at)}</td>
      </tr>`
          )
          .join("")
      : '<tr><td colspan="3">Tidak ada data</td></tr>';
  $("#spptb_ttd_file-here").html(html);
}

function fillTagihan(tg) {
  state.data_um = {};
  state.data_bb = {};
  if (tg.length == 0) return;

  let a = it; // mengikuti variabel lamamu
  tg.forEach((v) => {
    const id = "lk" + a;
    // if (v.status === "UM") {
    state.data_um[id] = {
      id_list_keu: id,
      id_keuangan: v.id_keuangan,
      berita_acara: v.berita_acara,
      nominal: num_format(v.nominal),
      jatuh_tempo_tgl: v.jatuh_tempo_tgl,
    };
    // } else if (v.status === "BB") {
    //   state.data_bb[id] = {
    //     id_list_keu_bb: id,
    //     id_keuangan_bb: v.id_keuangan,
    //     berita_acara_bb: v.berita_acara,
    //     nominal_bb: num_format(v.nominal),
    //     jatuh_tempo_tgl_bb: v.jatuh_tempo_tgl,
    //   };
    // }
    a++;
  });

  // data_um = state.data_um
  // data_bb = state.data_bb
  // render list tagihan sekali saja
  tambah_ketagihan();
  it = a;
}

async function isi_data_konsumen() {
  mkdtUpload();
  // VALIDASI PILIHAN
  if (!editdtt?.[0]) return swal("error", "Tidak ada kavling yang dipilih");
  const sh = editdtt[0];
  if (sh.data.tipe !== "kavling")
    return swal("error", "Tidak ada kavling terpilih");
  if (sh.data2.harga_akhir === "-")
    return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");

  disableForm(false);
  ui.btn.delKons.addClass("hidden");
  $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass("hidden");

  // Siapkan konteks UI & state
  bindKavlingContext(sh);
  state.id_hargajual = sh.data2.id_hargajual;
  setVal("#idk-harga_akhir", state.id_hargajual);

  $("#idk-is_allin").change();

  try {
    await withLoading(async () => {
      const res = await getTransaksiDetail({
        id_mkdt: sh.data.id_mkdt,
        id_kavling: state.id_kavling,
        id_hargajual: state.id_hargajual,
      });

      // CSRF update
      csrfHash = res.token;

      const v = res.data; // mkdt
      const h = res.hj; // pricelist
      const tg = res.tagihan;
      const dk = res.diskresi;

      // Diskresi & HJ
      fillDiskresi(dk);
      fillPriceSection(h, dk);

      // File preview
      fillFiles(v);

      // MKDT fields
      fillMkdt(v);

      // SPPTB list
      fillSpptbList(res.list_spptb || []);

      // Tagihan + render
      fillTagihan(tg);

      // Hitung total & label alamat sekali saja
      sum_mktotal();

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah
      );
      $(".label_alamat").html(label_alamat);

      // Buka modal
      $("#modal-isi_data_konsumen").modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal-isi_data_konsumen");
      state.status.tab.isClosed = false;
    });
  } catch (e) {
    console.log(e);
    // Error path konsisten
    return swal("error", e?.statusText || e?.message || "Terjadi kesalahan");
  }
}
function appendCollectionToFormData(fd, collection) {
  if (!collection || typeof collection !== "object") return;
  let i = 0;

  // Izinkan collection berupa Array atau Object keyed
  const items = Array.isArray(collection)
    ? collection
    : Object.values(collection);

  for (const item of items) {
    if (!item || typeof item !== "object") {
      i++;
      continue;
    }
    for (const [key, val] of Object.entries(item)) {
      // Nullish -> string kosong supaya backend nggak terima "undefined"
      fd.append(`${key}[${i}]`, val ?? "");
    }
    i++;
  }
}
function simpan_dt_konsumen_keuangan(e) {
  const btnSave = "#add-form-btn-idk_keu";
  // updateButtons(btnSave, "#prev-form-btn-idk_keu");

  if (parseFloat(removeComma($("#mk-total_cicilan_um").val() || 0)) > 0) {
    if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
      return swal(
        "error",
        "Gagal Menyimpan Data",
        "Total tagihan dan total yang harus dibayar tidak sesuai"
      );
    }
  }

  let dt = {};
  dt[csrfName] = csrfHash;
  $("form#fm-idk_keu :input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  //cicilan um

  let form = $("#fm-idk_keu")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);
  let is_ganti_nama = false;

  if (is_ganti_nama) {
    fd.append("id_mkdt_old", id_mkdt_old);
    fd.append("id_konsumen_old", id_konsumen_old);
    fd.append("is_ganti_nama", is_ganti_nama);
  }

  appendCollectionToFormData(fd, state.data_um);

  // for (var k in state.data_um) {
  //   if (!data_um.hasOwnProperty(k)) continue;
  //   var obj = state.data_umk[k];

  //   for (var d in obj) {
  //     if (!obj.hasOwnProperty(d)) continue;
  //     let x = obj[d];

  //     dt[d + "[" + i + "]"] = is_ganti_nama == "Ganti Nama" ? "" : x;
  //     fd.append(`${d}[${i}]`, x);
  //   }
  //   i++;
  // }

  // console.log(dt)
  // fd.forEach((value, key) => {
  //   console.log(key, value);
  // });
  // return
  //cicilan bb
  // i = 0;
  // for (var k in data_bb) {
  //   if (!data_bb.hasOwnProperty(k)) continue;
  //   var obj = data_bb[k];

  //   for (var d in obj) {
  //     if (!obj.hasOwnProperty(d)) continue;
  //     var x = obj[d];
  //     dt[d + "[" + i + "]"] = is_ganti_nama == "Ganti Nama" ? "" : x;
  //     fd.append(`${d}[${i}]`, x);
  //   }
  //   i++;
  // }

  $.ajax({
    url: base_url + "transaksi/simpan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btnSave, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          removeModalListener("#modal-isi_data_konsumen");
          $(".modal").modal("hide");
          simpanBtn(btnSave, false);

          load_kavling();
          hapus_seleksi();
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn(btnSave, false);
        });
      }
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        simpanBtn(btnSave, false);
      });
    },
  });
}

$("#status_mkdt").change(function () {
  if ($("#status_mkdt option:selected").val() == "Batal")
    $("#show_keterangan_batal").removeClass("hidden");
  else $("#show_keterangan_batal").addClass("hidden");
});
//
// hitung turun kpr
$("#fm-mkdt #harga_kpr, #fm-mkdt #acc_harga_kpr").change(function () {
  if ($("#fm-mkdt #acc_harga_kpr").val() != "0") {
    $("#fm-mkdt #harga_turun_kpr")
      .val(
        parseFloat(removeComma($("#fm-mkdt #harga_kpr").val())) -
          parseFloat(removeComma($("#fm-mkdt #acc_harga_kpr").val()))
      )
      .change();
  } else {
    $("#fm-mkdt #harga_turun_kpr").val(0);
  }
});
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function () {
  if (!$("#wawancara").prop("checked")) {
    setDatePicker(null, "#wawancara_tgl");
  }
});

$("#refresh_fmmkdt_btn").click(function () {
  refresh_fmmkdt(false);
  $("#mkdt_data_baru").val(1);
});

function refresh_fmmkdt($st = true) {
  $("#fm-mkdt")[0].reset();
  $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop(
    "disabled",
    $st
  );
  $("#id_konsumen").val("");
  $("#id_keuangan0").val("");
}

function delete_kons() {
  $(
    "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen"
  ).val("");
  $("#id_konsumen, #id_mkdt").val("");
  $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
  if (sh.data.tipe != "kavling")
    return swal("error", "Tidak ada kavling terpilih", null, true);

  if (!sh.data.id_mkdt)
    return swal("error", "Belum ada data konsumen", null, true);

  if (sh.data2.harga_akhir == "-") {
    return swal(
      "error",
      "Kavling belum dipasarkan",
      "Kavling belum memiliki harga jual"
    );
  }
  $("#lb-st-no_spptb").html("-");
  $("#lb-st-nama_konsumen").html("-");

  // $("#label-file_ktp").html("Upload file KTP");
  // $("#label-file_npwp").html("Upload file KTP");

  // $("#refresh_fmmkdt_div").addClass("hidden");
  // $("#delete_kons_div").addClass("hidden");
  // $("#fm-mkdt .num").prop("disabled", false);

  // $("#cicilan_belong_here").html("");
  it = 0;
  // $("#data_konsumen").tab('show');

  $("#mkdt_data_baru").val(0);

  refresh_fmmkdt(false);

  $("#fm-mkdt .num").val(0);

  $(".id_kavling").val(id_kavling);
  $("#id_mkdt").val(sh.data.id_mkdt);

  $.ajax({
    url: base_url + "transaksi/status/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
      id_hargajual: sh.data2.id_hargajual,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (x) {
      $("#loading").addClass("hidden");
      csrfHash = x.token;
      let r = x.data, //data mkdt
        pb = x.perintah_bangun,
        tkpr = x.tagihan;

      //load hargajual
      // if (h.hargajual) {
      //   $.each(h, function (k, v) {
      //     $("#mkdt-" + k)
      //       .val(v)
      //       .change()
      //       .keyup();
      //   });
      //   $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
      //   $("#fm-mkdt #harga_kpr").val(h.kpr).change();
      // }

      //load num
      // if (!r.id_mkdt) {
      //     $(".num").val(0).keyup();
      //     $("#fm-mkdt #harga_jual").val(sh.data2.harga_akhir);
      // }

      //set harga jual dari data kavling
      if (r) {
        if (r.status_mkdt == "Batal") {
          refresh_fmmkdt(true);
          $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass(
            "hidden"
          );
          $("#delete_kons_div").addClass("hidden");
        }

        //autoload field ke input
        for (let i in r) {
          if (
            i != "perintah_bangun" &&
            i != "wawancara" &&
            i != "akad" &&
            i != "sp3k" &&
            i != "bast_file" &&
            i != "sp3k_file" &&
            i != "perintah_bangun_file"
          )
            $("#fm-mkdt #" + i).val(r[i]);
        }

        $("#lb-st-no_spptb").html(r.no_spptb);
        $("#lb-st-nama_konsumen").html(r.nama_konsumen);

        $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
        $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
        $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

        var newOption = new Option(r.nama_bank, r.id_bank, true, true);
        $("#id_bank").append(newOption).trigger("change");

        if (r.wawancara == 1) $("#wawancara").prop("checked", true);
        if (r.sp3k == 1) $("#sp3k").prop("checked", true);
        if (r.akad == 1) $("#akad").prop("checked", true);

        //set datepicker jika tanggal valid
        setDatePicker(pb.perintah_bangun_tgl, "#fm-mkdt #perintah_bangun_tgl");

        setDatePicker(r.booking_tgl, "#fm-mkdt #booking_tgl");
        setDatePicker(r.wawancara_tgl, "#fm-mkdt #wawancara_tgl");
        setDatePicker(r.sp3k_tgl, "#fm-mkdt #sp3k_tgl");
        setDatePicker(r.sp3k_tgl_exp, "#fm-mkdt #sp3k_tgl_exp");
        setDatePicker(r.rencana_akad_tgl, "#fm-mkdt #rencana_akad_tgl");
        setDatePicker(r.akad_tgl, "#fm-mkdt #akad_tgl");

        // if (r.refund_tgl != "0000-00-00")
        //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

        $("#fm-mkdt .num").keyup().change(); //fomrat form number
        $("#status_mkdt").change(); //show/hide keterangan batal

        $("#mkdt_keterangan").val(r.keterangan);

        // $("#file_ktp-here").html("Tidak ada data");
        // src = not_found;

        // src = not_found;
        //load bast
        // if (r.bast_file != null) {
        //   src = r.bast_file;
        // }
        // $("#list-upload_bast_file").prop("href", base_url + src);

        // src = not_found;
        //load sp3k
        // if (r.sp3k_file != null) {
        //   src = r.sp3k_file;
        // }
        // $("#list-upload_sp3k_file").prop("href", base_url + src);
        setBtnHref("#list-upload_sp3k_file", r.sp3k_file);
      }

      if (pb.perintah_bangun == 1) {
        $("#perintah_bangun").prop("checked", true);
        $("#fm-mkdt #perintah_bangun_oleh").val(pb.username);
        setBtnHref(
          "#list-upload_perintah_bangun_file",
          pb.perintah_bangun_file
        );
        setDatePicker(pb.perintah_bangun_tgl, "#perintah_bangun_tgl");
      }

      load_tagihankpr(tkpr);

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah
      );
      $(".label_alamat").html(label_alamat);

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal_divisi" + role);
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}
async function hapus_turunkpr(id_keuangan) {
  const { isConfirmed } = await Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data keuangan ini akan dihapus permanen!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });

  if (isConfirmed) {
    Swal.fire({
      title: "Menghapus...",
      text: "Tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      let response = await fetch(`${base_url}tagihan/hapusturunkpr`, {
        method: "POST", // atau 'DELETE' kalau API pakai method delete
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id_keuangan: id_keuangan }),
      });

      let result = await response.json();

      if (response.ok && result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: result.message,
        }).then(() => {
          // refresh table atau halaman
          load_tagihankpr(null);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: result.message || "Terjadi kesalahan saat menghapus",
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
      });
    }
  }
}
function load_tagihankpr(val) {
  $("#mkdt-tagihan_kpr").html("");

  // console.log(val);

  if (val == null) {
    return;
  }

  let div = `
  <div class="divider divider-left">
      <div class="divider-text font-weight-bold">Tagihan Turun KPR</div>
  </div>
  `;
  if (val.berita_acara == "Turun KPR") {
    div += `
      <div class="form-group">
          <label for="bank">Tanggal Jatuh Tempo Turun KPR</label>
          <input type="text" readonly class="form-control" value='${format_date(
            val.jatuh_tempo_tgl
          )}' />
              <a href="#" onclick="hapus_turunkpr(${
                val.id_keuangan
              })"class="text-danger"><i class="fa fa-trash"></i>Klik untuk hapus tagihan</a>
      </div>
      `;
  }

  $("#mkdt-tagihan_kpr").html(div);
}

$("#add-form-btn-mkdt").click(function (e) {
  e.preventDefault();
});

function save_mkdt(e) {
  const btn = "#add-form-btn-mkdt";
  if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
  if (!palid("fm-mkdt #id_bank", "", "Bank harus diisi")) return;

  if (
    removeComma($("#harga_turun_kpr").val()) > 0 &&
    $("#mkdt-tagihan_kpr").html() == ""
  ) {
    swal(
      "warning",
      "Tagihan untuk turun KPR harus dibuat terlebih dahulu",
      "Karena ada nilai di turun KPR, jadi harus buat tagihannya dulu ya!",
      false,
      hlButton("#btn-add-tagihan-turunkpr")
    );
    return;
  }

  // if ($("#total_cicilan").val() != $("#total_biaya2").val()) {
  //     Swal.fire({
  //         //position: 'bottom-end',
  //         icon: 'error',
  //         title: "Total Cicilan tidak sesuai dengan total biaya",
  //         showConfirmButton: false,
  //         timer: 1500
  //     });
  //     return false;
  // }

  // var files = $('#file_ktp')[0].files;
  var form = $("#fm-mkdt")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "transaksi/status/simpan",
    type: "post",
    // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btn, true);
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        removeModalListener("#modal_divisi4");
        load_kavling();
        hapus_seleksi();

        swal("success", r.messages);
        $(".modal").modal("hide");
      } else {
        swal("error", r.messages);
      }
      simpanBtn(btn, false);
    },
    error: function (xhr, st, err) {
      simpanBtn(btn, false);
      return swal("error", err);
    },
  });
}

function set_harga() {
  $.ajax({
    url: base_url + "Hargajual/set_harga",
    type: "post",
    data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#set-harga-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>'
      );
      $("#set-harga-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      swal(
        response.success ? "success" : "error",
        response.messages,
        null,
        true,
        () => {
          if (response.success) {
            $("#modals-set_harga").modal("hide");
          }
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        }
      );
      load_kavling();
      hapus_seleksi();
    },
  });
}

function formatDesign(item) {
  var selectionText = item.text.split(";");
  var $returnString = $(
    "<span> <b>" +
      selectionText[0] +
      "</b></br >" +
      selectionText[1] +
      "</br>" +
      selectionText[2] +
      "</span>"
  );
  return $returnString;
}
$("#sh-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  templateResult: formatDesign,
  ajax: {
    url: base_url + "hargajual/get",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text: `Rp. ${num_format(v.hargajual)} Per ${format_date(
            v.tgl_harga
          )} (ROW ${v.row}); <b>Tipe:</b> ${v.id_tipe}; <b>Ket:</b> ${
            v.keterangan
          };`,
          row: v.row,
          tipe: v.id_tipe,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          hargajual_net: v.hargajual_net,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          ppn: v.ppn,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
          lok: `${v.lokasi}/${v.file_name}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
// on select cluster
$("#sh-id").on("select2:selecting", function (e) {
  var i = e.params.args.data;
  $.each(i, function (k, v) {
    changeVal("#sh-" + k, v);
  });

  let src = i.lok != "null/null" ? i.lok : not_found;
  setFileHref("#sh-pricelist_file", false, src);
});

$("#sh-id").change(function () {
  if (!this.value) $(".sh-fm").val("");
});

function open_set_turun_pembangunan() {
  $("#list-tp-upload_perintah_bangun_file").prop("href", base_url + not_found);
  $("#label-perintah_bangun_file").html("File Turun Perintah Bangun");
  if (editdtt.length == 0) {
    return swal("error", "Tidak ada kavling terpilih");
  }
  $("#fm-turun_pembangunan")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }
  $.ajax({
    url: base_url + "siteplan/get_turun_pembangunan",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        no = "";

      $(".select2").not("#pilih-divisi").val(null).trigger("change");

      if (r.length > 0) {
        r.forEach((v) => {
          id_kavling += v.id_kavling + ";";
          no += `${v.nama_jalan} No. ${v.no_kavling} \n`;
        });

        $(".id_kavling").val(id_kavling);
        $("#tp-kavling").val(no);

        $("#tp-perintah_bangun_oleh").val(r[0].username);

        $("#list-tp-upload_perintah_bangun_file").prop(
          "href",
          base_url + r[0].perintah_bangun_file
        );

        setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl");
      }

      $("#loading").addClass("hidden");
      $("#modals-turun_pembangunan").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function set_tp() {
  if ($("#tp-perintah_bangun_tgl").val() == "") {
    return Swal.fire({
      icon: "error",
      title: "Tanggal Perintah Bangun harus diisi",
      showConfirmButton: false,
    });
  }
  let form = $("#fm-turun_pembangunan")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "siteplan/set_turun_pembangunan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#set-tp-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
      $("#set-tp-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-turun_pembangunan").modal("hide");
          $("#set-tp-btn").html("Simpan");
          $("#set-tp-btn").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#set-tp-btn").html("Simpan");
          $("#set-tp-btn").removeClass("disabled");
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (err) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan saat menginput data",
        showConfirmButton: false,
      }).then(function () {
        $("#set-tp-btn").html("Simpan");
        $("#set-tp-btn").removeClass("disabled");
      });
    },
  });
}

function setFileHref(id, remove = true, url = null) {
  if (remove) {
    $(id).removeAttr("target");
    $(id).prop("href", "javascript:void(0)");
  } else {
    $(id).prop("href", `${base_url}${url}`);
    $(id).prop("target", "_blank");
  }
}

function open_set_harga() {
  if (editdtt.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  setFileHref("#sh-pricelist_file");

  $("#fm-set_harga")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }

  $.ajax({
    url: base_url + "siteplan/get_harga_kavling",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        src,
        no = "";

      $("#sh-id").val(null).trigger("change");

      if (r.length > 0) {
        for (let a = 0; a < r.length; a++) {
          id_kavling += r[a].id_kavling + ";";
          no += `${r[a].nama_jalan} No. ${r[a].no_kavling} - ${r[a].tipe_rumah}\n`;

          if (r[a].harga_akhir) {
            $("#sh-id")
              .append(
                $("<option selected></option>")
                  .attr("value", r[a].harga_akhir)
                  .text(
                    `Rp. ${num_format(r[a].hargajual)} (${
                      r[a].tipe_rumah
                    }) ROW ${r[a].row}: per ${format_date(r[a].tgl_harga)}`
                  )
              )
              .trigger("change");

            changeVal("#sh-row", r[a].row);
            changeVal("#sh-tipe", r[a].tipe_rumah);
            changeVal("#sh-lb", r[a].hj_lb);
            changeVal("#sh-lt", r[a].hj_lt);
            changeVal("#sh-hargajual", r[a].hargajual);
            changeVal("#sh-hargajual_net", r[a].hargajual_net);
            changeVal("#sh-kpr", r[a].kpr);
            changeVal("#sh-uang_muka", r[a].uang_muka);
            changeVal("#sh-ppn", r[a].ppn);
            changeVal("#sh-bphtb", r[a].bphtb);
            changeVal("#sh-biaya_adm", r[a].biaya_adm);
            changeVal("#sh-biaya_proses", r[a].biaya_proses);

            src = not_found;
            if (r[a].file_name) {
              src = `${r[a].lokasi}/${r[a].file_name}`;
            }
            setFileHref("#sh-pricelist_file", false, src);
          } else {
            setFileHref("#sh-pricelist_file");
          }
        }

        $(".id_kavling").val(id_kavling);
        $("#sh-kavling").val(no);
        // $("#fm-set_harga #id_tipe").val(id_tipe);
        // $("#fm-set_harga #harga").val(harga).keyup();
      }

      $("#modals-set_harga").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function isi_si() {
  let sh = editdtt;

  if (sh.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  sh = sh[0];

  let id_kavling = sh.id.substr(3);

  $(".id_kavling").val(id_kavling);

  co = [];

  $("#fm-si")[0].reset();

  $.ajax({
    url: base_url + "mkdt/getsi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      let d = res.data,
        id_si,
        div = "";

      $.each(d, function (i, v) {
        co.push(v.id_list_si_ori);

        id_si = !v.id ? "n" + v.id_list_si_ori : v.id;
        div += `
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>${v.nama}</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Tanggal SI</label>
                                                <input type="text" class="form-control fp-si flatpickr-human-friendly tsi${
                                                  v.id_list_si_ori
                                                }"
                                                    id="id-si[${id_si}][tanggal_si]" value="${
          v.tanggal_si ? v.tanggal_si : ""
        }" name="id-si[${id_si}][tanggal_si]">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Soft File</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input " accept="application/pdf"
                                                        name="id-si-file-${id_si}" id="id-si-file-${id_si}" />
                                                    <label class="custom-file-label" id="label-si-file-${id_si}"
                                                        for="id-si-file-${id_si}">Upload Soft File</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                        
                                         <a href="${
                                           base_url + v.file
                                         }" target=_blank id="list-si-file-${id_si}"
                                                class="btn btn-outline-primary col-12">Klik untuk lihat file</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <textarea class="form-control" id="id-si[${id_si}][keterangan]"
                                                name="id-si[${id_si}][keterangan]" rows="4" placyeholder="Keterangan">${
          v.keterangan ? v.keterangan : ""
        }</textarea>
                                            <small id="last_update-si${id_si}" class=""></small>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 `;
      });

      $("#dv-si-here").html(div);

      flatpickr(".fp-si", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
      });
      $(".num").change();

      $("#modals-si").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function save_si() {
  // $.each(co, function(i, v) {
  //     if ($(".tsi" + v)[0].value == "") {
  //         $(".tsi" + v).addClass('is-invalid')
  //         return swal('error', "Nominal pembayaran harus diisi")
  //     } else
  //         $(".tsi" + v).removeClass('is-invalid');

  // });

  var form = $("#fm-si")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  let sbtn = "#btn-si-simpan";

  $.ajax({
    url: base_url + "mkdt/saveSI",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(sbtn, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        swal("success", r.messages);
        $(".modal").modal("hide");
        simpanBtn(sbtn, false);
      } else {
        swal("error", "Terjadi kesalahan", r.messages);
        simpanBtn(sbtn, false);
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (r) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
      simpanBtn(sbtn, false);
    },
  });
}

$("#fm-mkdt #sp3k_tgl").change(function () {
  if (!this.value) return;
  document
    .querySelector("#fm-mkdt #sp3k_tgl_exp")
    ._flatpickr.setDate(new Date(this.value).fp_incr(88));
});

$("#refresh-btn-idk_keu").click(function () {
  $("#fm-idk_keu .num").prop("disabled", false);

  $("#idk_data_baru").val(1);
  // $("#fm-idk_keu")[0].reset();

  // refresh_fmmkdt(false);
  $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop(
    "disabled",
    false
  );
  $("#fm-idk_keu #idk-id_konsumen").val("");

  $("#idk-show_keterangan_batal").addClass("hidden");
});

function mkdtUpload() {
  const inputs = [
    { id: "file_ktp" },
    { id: "file_npwp" },
    { id: "file_data_diri" },
  ];

  inputs.forEach((item) => {
    const input = document.getElementById(item.id);
    const dropzone = input.closest(".dropzone");
    const preview = dropzone.querySelector(".dz-preview");
    const placeholder = dropzone.querySelector(".dz-placeholder");

    preview.style.display = "none";
    placeholder.style.display = "block";

    input.addEventListener("change", function () {
      preview.innerHTML = ""; // reset dulu

      if (this.files && this.files[0]) {
        const file = this.files[0];

        // Kalau gambar → tampilkan thumbnail
        if (file.type.startsWith("image/")) {
          const reader = new FileReader();
          reader.onload = function (e) {
            preview.innerHTML = `
                        <img src="${e.target.result}" 
                             class="preview-thumb" 
                             style="height:100%;"/>
                        <div class="text-truncate mb-1">${file.name}</div>
                    `;
          };
          reader.readAsDataURL(file);
        }
        // Kalau PDF → tampilkan icon + nama file
        else if (file.type === "application/pdf") {
          preview.innerHTML = `
                    <div class="p-2 border rounded bg-light text-center">
                        <i class="fa fa-file-pdf fa-3x text-danger"></i>
                        <div class="text-truncate">${file.name}</div>
                    </div>
                `;
        }
        // toggle tampil
        preview.style.display = "block";
        placeholder.style.display = "none";
      }
    });
  });
}

function postTurunKPR(val) {
  // console.log(val)
  const { berita_acara, nominal, tgl, id_mkdt } = val;
  $.ajax({
    url: base_url + "tagihan/turunkpr",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, berita_acara, nominal, tgl, id_mkdt },
    beforeSend: () => {},
    success: () => {},
    error: () => {},
  });
}
async function loadFormTagihan(nominal_kpr) {
  const { isConfirmed, isDenied, value } = await Swal.fire({
    title: "Tambah Ke Tagihan",
    html: `
      <div class="swal2-content mt-1" style="text-align:left">
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="Turun KPR" readonly id="fkpr-berita_acara" placeholder=" " required>
          <label for="fkpr-berita_acara">Untuk Tagihan</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="${nominal_kpr}" readonly id="fkpr-nominal" placeholder=" " required>
          <label for="fkpr-nominal">Nominal</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control fp-jatuhtempo" id="fkpr-jatuh_tempo_tgl" placeholder=" " required>
          <label for="fkpr-jatuh_tempo_tgl">Jatuh Tempo</label>
        </div>
      </div>
    `,
    focusConfirm: false,
    showDenyButton: true,
    confirmButtonText: "Ya",
    denyButtonText: "Tidak",
    allowOutsideClick: false,
    showLoaderOnConfirm: true,

    didOpen: () => {
      // const popup = Swal.getPopup();
      const el = document.querySelector(".fp-jatuhtempo");
      if (el && el._flatpickr) el._flatpickr.destroy();
      flatpickr(el, {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
        allowInput: true,
        // appendTo: popup, // penting biar z-index aman
      });
    },

    preConfirm: async () => {
      const p = Swal.getPopup();
      const berita_acara = p.querySelector("#fkpr-berita_acara").value.trim();
      const nominalStr = p.querySelector("#fkpr-nominal").value.trim();
      const tgl = p.querySelector("#fkpr-jatuh_tempo_tgl").value.trim();
      const id_mkdt = document.querySelector("#id_mkdt").value.trim();
      const id_kavling = document.querySelector(".id_kavling").value.trim();
      const id_konsumen = document.querySelector("#id_konsumen").value.trim();
      const harga_kpr = document.querySelector("#harga_kpr").value.trim();
      const acc_harga_kpr = document
        .querySelector("#acc_harga_kpr")
        .value.trim();

      const nominal = Number(nominalStr.replace(/[^\d.-]/g, "")) || 0;

      if (!berita_acara)
        return Swal.showValidationMessage("Untuk Tagihan wajib diisi");
      if (nominal <= 0)
        return Swal.showValidationMessage("Nominal tidak boleh 0");
      if (!tgl)
        return Swal.showValidationMessage(
          "Tanggal jatuh tempo tidak boleh kosong"
        );

      // ---- POST ke server ----
      try {
        // (opsional) Abort kalau kelamaan
        const ac = new AbortController();
        const timeout = setTimeout(() => ac.abort(), 20000); // 20 detik

        const res = await fetch(`${base_url}tagihan/turunkpr`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            // sertakan CSRF kalau CI4 mengaktifkan:
            // "X-CSRF-TOKEN": window.CSRF_TOKEN
          },
          body: JSON.stringify({
            berita_acara,
            nominal,
            jatuh_tempo: tgl,
            id_mkdt,
            id_kavling,
            id_konsumen,
            harga_kpr,
            acc_harga_kpr,
          }),
          signal: ac.signal,
        });
        clearTimeout(timeout);

        // Tangani error HTTP
        if (!res.ok) {
          const text = await res.text().catch(() => "");
          throw new Error(text || `Gagal menyimpan (HTTP ${res.status})`);
        }

        const data = await res.json().catch(() => ({}));
        // Jika API kamu kirim {success:false, message:"..."}
        if (data && data.success === false) {
          throw new Error(data.messages || "Gagal menyimpan");
        }
        // Return untuk diteruskan ke .then(...) sebagai `value`
        return data;
      } catch (err) {
        // Tetap di popup + tampilkan pesan error di bawah tombol
        return Swal.showValidationMessage(err.message || "Gagal menyimpan");
      }
    },
  });

  return { isConfirmed, isDenied, value };
}
let btnTunruKpr = "#btn-add-tagihan-turunkpr";
$(btnTunruKpr).click(async function (e) {
  e.preventDefault();
  let nominal_kpr = removeComma($("#harga_turun_kpr").val());

  if (nominal_kpr == 0) {
    return swal(
      "error",
      "Terjadi Kesalahan",
      "Tidak bisa menambahkan ke tagihan jika nominal Turun KPR 0!"
    );
  }
  const { isConfirmed, isDenied, value } = await loadFormTagihan(nominal_kpr);

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
    });
  } else if (isDenied) {
    Swal.fire("Dibatalkan", "Aksi dibatalkan.", "info");
  }
});
