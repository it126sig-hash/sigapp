    function ganti_kavling() {
        if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
            return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
        }
        Swal.fire({
            title: 'Apakah anda yakin akan memindahkan kavling',
            text: "Setelah menekan tombol 'Ya!', pilih salah satu kavling dipasarkan.",
            // type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya!',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: !1
        }).then(function(t) {
            if (t.isConfirmed) {
                $("#modal-isi_data_konsumen").modal('hide');
                $(".div_menu").hide();

                let div_menu = `
                <div id="menu-ganti_kavling" class="float div_menu">
                    <h5>Tekan tombol jika sudah selesai memilih kavling</h5>
                    <button id="btn-ganti_kavling_ok" type="button" onclick="ganti_kavling_selection(1)"
                        class="my-float btn-icon btn btn-primary btn-round "  style="float:left">
                        Selesai
                    </button>
                    <button id="btn-batal_ganti_kavling" type="button" onclick="ganti_kavling_selection(0)"
                        class="my-float btn-icon btn btn-danger btn-round" style="float:left">
                        Batal
                    </button>
                </div>
                `

                $("#menu_here").append(div_menu)
            }
        })
    }

    function ganti_kavling_selection(e) {
        if (e) {
            let sh = editdtt[0]

            id_mkdt_old = $("#idk-id_mkdt").val()
            id_konsumen_old = $("#idk-id_konsumen").val()
            is_ganti_nama = 'Pindah Kavling'

            $("#btn-print_spptb").prop("href", "#")
            $("#idk-id_konsumen, #idk-id_mkdt").val("")
            $(".id_kavling").val(sh.id.substr(3));

            $("#idk_data_konsumen-tab").click()

            $(".label_alamat").append(`
                <hr>
                <span style='color:red'>Pindah ke Kavling ${sh.data.nama_jalan} No. ${sh.data.no_kavling}</div>
            `)

        } else {

        }

        $("#menu-ganti_kavling").remove()
        $("#modal-isi_data_konsumen").modal('show');
        $("#keuangan_menu").show();
    }

   let id_mkdt_old = null,
  id_konsumen_old = null,
  is_ganti_nama = false;

$("#idk-rincian").richText({
  fonts: false,
  // uploads
  imageUpload: false,
  fileUpload: false,

  // media
  videoEmbed: false,

  // link
  urls: false,
});
$("#snk").richText({
  // text formatting
  bold: true,
  italic: true,
  underline: true,

  // text alignment
  leftAlign: true,
  centerAlign: true,
  rightAlign: true,
  justify: true,

  // lists
  ol: true,
  ul: true,

  // title
  heading: true,
});
$("#status_keterangan").richText({
  fonts: false,
  // uploads
  imageUpload: false,
  fileUpload: false,

  // media
  videoEmbed: false,

  // link
  urls: false,
});
$("#kopsurat").select2({
  placeholder: "Pilih Kop Surat",
  allowClear: true,
  ajax: {
    url: base_url + "/Home/getKop",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text: v.nama + " (" + v.ukuran + ")",
          lokasi: v.lokasi,
          ukuran: v.ukuran,
          mt: v.mt,
          mb: v.mb,
          ml: v.ml,
          mr: v.mr,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

function print_tagihan() {
  $("#pt_id_mkdt").html("");
  $("#pt_id_konsumen").html("");
  $("#pt_id_kavling").html("");
  $("#pt_detail_konsumen").html("");
  $("#pt_detail_kavling").html("");
  $("#list_inv-here").html("");

  $("#cp_telp").html("");
  $("#tb-print-data-tagihan").html("");

  $('.nav-tabs a[href="#form_list_inv"]').tab("show");

  document
    .querySelector("#tanggal_surat_tagihan")
    ._flatpickr.setDate(new Date().toDateInputValue());
  document
    .querySelector("#pt-tanggal_jatuh_tempo")
    ._flatpickr.setDate(new Date().fp_incr(7));

  let role,
    sh = editdtt[0];

  if (editdtt.length == 0) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Terjadi Kesalahan.",
      text: "Tidak ada kavling yang dipilih",
      showConfirmButton: false,
    });
    return;
  } else if (!sh.data.id_mkdt) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title:
        "Belum ada data konsumen di kavling" +
        sh.data.nama_jalan +
        ", No." +
        sh.data.no_kavling,
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  let id_kavling = sh.id.substr(3);

  $.ajax({
    url: base_url + "keuangan/get_tagihan/inv",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_keuangan: sh.data.id_keuangan,
      id_kavling: id_kavling,
      id_mkdt: sh.data.id_mkdt,
    },
    dataType: "json",
    success: function (r) {
      let kons = r.detail,
        lt = r.list_tagihan;
      csrfHash = r.token;

      if (!lt.length) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: "Tagihan tidak ditemukan",
          text: "Isi tagihan terlebih dahulu",
          showConfirmButton: false,
        });
        return;
      }

      if (r.invoice.length) {
        let tb = "";
        $.each(r.invoice, function (i, v) {
          tb += "<tr>";
          tb +=
            "<td>" +
            v.no_inv +
            "</td> " +
            "<td>" +
            format_date(v.tanggal_invoice) +
            "</td> " +
            "<td>" +
            format_date(v.tanggal_jatuh_tempo) +
            "</td> " +
            "<td>" +
            v.uadd_by +
            " <br>" +
            format_date(v.date_add) +
            "</td> " +
            `<td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="print_inv('` +
            v.no_inv +
            `')"><i class="fa fa-print"></i></button>
                                    </div>
                                </td> `;
          tb += "</tr>";
        });

        $("#list_inv-here").append(tb);
      } else {
        $("#list_inv-here").html("<tr><td colspan=5>Tidak ada data</td></tr>");
      }

      //load company profile detail
      // $("#cp_nama_perusahaan").html(r.compro.nama_perusahaan)
      // $("#cp_alamat_perusahaan").html(r.compro.alamat)
      // $("#cp_telp").html(r.compro.telp + " - " + r.compro.telp2)

      //load konsumen detail
      $("#pt_id_mkdt").html(kons.id_mkdt);
      $("#pt_id_konsumen").html(kons.id_konsumen);
      $("#pt_id_kavling").html(kons.id_kavling);
      $("#pt_detail_konsumen").html(
        kons.nama_konsumen +
          " (" +
          kons.hp_konsumen +
          ")" +
          "<br>" +
          kons.alamat_konsumen,
      );
      $("#pt_detail_kavling").html(
        dt_proyek.nama_proyek +
          "<br>" +
          sh.data.nama_jalan +
          " No. " +
          sh.data.no_kavling,
      );
      // $("#pt_hp_konsumen").html(kons.hp_konsumen)

      /************************ load table tagihan ***************************/
      let tr_tg = "",
        no = 1,
        tot_tg = 0,
        sb_button = "",
        chkd = "",
        tg = r.list_tagihan,
        sudah_bayar = r.sudah_bayar ? r.sudah_bayar : 0;

      $.each(tg, function (i, v) {
        chkd = v.sudah_dibayar == 1 ? "checked" : "";
        sb_button =
          `<div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" ` +
          chkd +
          ` onchange="save_sb(` +
          v.id_keuangan +
          `)" class="custom-control-input " disabled id="sb_btn` +
          v.id_keuangan +
          `" name="sb_btn[` +
          v.id_keuangan +
          `]" value="1" />
                                        <label class="custom-control-label" for="sb_btn` +
          v.id_keuangan +
          `"></label>
                                    </div>
                                </div>`;

        tot_tg += parseInt(v.nominal);
        tr_tg +=
          "<tr>" +
          "<td>" +
          no +
          "</td>" +
          "<td>" +
          v.berita_acara +
          "</td>" +
          "<td>" +
          format_date(v.jatuh_tempo_tgl) +
          "</td>" +
          // "<td>" + sb_button + "</td>" +
          "<td style='text-align:right'>" +
          num_format(v.nominal) +
          "</td>" +
          "<tr>";
        no++;
      });

      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Total Tagihan</th>" +
        "<th style='text-align:right'>" +
        num_format(tot_tg) +
        "</th>" +
        "<tr>";

      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Sudah Bayar</th>" +
        "<th style='text-align:right'>" +
        num_format(sudah_bayar) +
        "</th>" +
        "<tr>";
      tr_tg +=
        "<tr>" +
        "<th colspan='3' style='text-align:right'>Sisa</th>" +
        "<th style='text-align:right'>" +
        num_format(tot_tg - parseInt(sudah_bayar)) +
        "</th>" +
        "<tr>";

      $("#tb-print-data-tagihan").append(tr_tg);

      $("#print_tagihan_modal").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
      });
    },
  });
}

function save_inv() {
  if (!$("#no_sruat").val()) {
    $("#no_sruat").addClass("is-invalid");
    return swal("warning", "Peringatan!", "No Invoice Harus Diisi!!");
  }

  $("#no_sruat").removeClass("is-invalid");
  $.ajax({
    url: base_url + "keuangan/save_inv",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      no_inv: $("#no_sruat").val(),
      id_konsumen: $("#pt_id_konsumen").html(),
      id_mkdt: $("#pt_id_mkdt").html(),
      id_kavling: $("#pt_id_kavling").html(),
      id_kopsurat: $("#kopsurat").val(),
      tanggal_invoice: $("#tanggal_surat_tagihan").val(),
      tanggal_jatuh_tempo: $("#pt-tanggal_jatuh_tempo").val(),
      tagihan: $("#tb-print-data-tagihan").html(),
      terms: $("#snk").val(),
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
      $("#form_add_inv-btn").html("Menyimpan");
      $("#form_add_inv-btn").prop("disabled", true);
    },
    success: function (r) {
      csrfHash = r.token;
      $("#loading").addClass("hidden");
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages || r.message || "Data berhasil disimpan",
          showConfirmButton: false,
        }).then(function () {
          print_tagihan();
          // $('.nav-tabs a[href="#form_list_inv"]').tab('show');
          $("#form_add_inv-btn").html("Simpan");
          $("#form_add_inv-btn").prop("disabled", false);
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
        }).then(function () {
          $("#form_add_inv-btn").html("Simpan");
          $("#form_add_inv-btn").prop("disabled", false);
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        $("#form_add_inv-btn").html("Simpan");
        $("#form_add_inv-btn").prop("disabled", false);
      });
    },
  });
}

function print_inv(e) {
  var myWindow = window.open(
    base_url + "/keuangan/print_tagihan/?id=" + e,
    "_blank",
    "top=100,left=300,width=700,height=600",
  );
  setTimeout(function () {
    myWindow.focus();
  }, 1000);
}

function doPrint() {
  (async () => {
    const rawResponse = await fetch(base_url + "keuangan/doPrint", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        [csrfName]: csrfHash,
        konsumen: $("#pt_nama_konsumen").html(),
        alamat: $("#pt_alamat_konsumen").html(),
        no_sruat: $("#no_sruat").val(),
        tanggal_surat_tagihan: $("#tanggal_surat_tagihan").val(),
        table: $("#tb-print-data-tagihan").html(),
      }),
    })
      .then((resp) => resp.blob())
      .then((blob) => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.style.display = "none";
        a.href = url;

        // the filename you want
        a.download =
          "Tagihan " +
          $("#pt_nama_konsumen").html() +
          " " +
          $("#tanggal_surat_tagihan").val() +
          ".pdf";
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
      })
      .catch(() => alert("oh no!"));
  })();
}

//if pelunasan lebih dari sisa tagihan pelunasan diisi sisa tagihan
// $("#bt-bayar_tagihan_um").change(function () {
//     if (parseFloat(removeComma(this.value)) > parseFloat(removeComma($("#bt-sisa_tagihan_um").val())))
//         $("#bt-bayar_tagihan_um").val($("#bt-sisa_tagihan_um").val())
// })

$("#mk-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  ajax: {
    url: base_url + "/Hargajual/getAll",
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
          text:
            "Rp. " +
            num_format(v.hargajual) +
            " (" +
            v.tipe_rumah +
            ")" +
            ": Per " +
            format_date(v.tgl_harga),
          row: v.row,
          tipe: v.tipe_rumah,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
          tgl_harga: format_date(v.tgl_harga),
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
$("#mk-id").on("select2:selecting", function (e) {
  // if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
  //     Swal.fire({
  //         title: 'Lakukan perubahan?',
  //         text: "data pada tabel tagihan akan terhapus!",
  //         type: 'warning',
  //         showCancelButton: true,
  //         confirmButtonColor: '#3085d6',
  //         cancelButtonColor: '#d33',
  //         confirmButtonText: 'Ya!',
  //         confirmButtonClass: 'btn btn-primary',
  //         cancelButtonClass: 'btn btn-danger ml-1',
  //         buttonsStyling: !1
  //     }).then(function (t) {
  //         if (t.value) {
  //             var i = e.params.args.data
  //             $.each(i, function (k, v) {
  //                 $("#mk-" + k).val(v).change().keyup()
  //             })
  //             sum_mktotal()
  //             data_um = {}
  //             data_bb = {}
  //             $("#list_cicilan_here").html("")
  //             $("#total_cicilan_um").val(0).change().keyup()
  //             $("#total_cicilan_bb").val(0).change().keyup()
  //             $("#id_list_keu").val('');
  //             $("#id_list_keu_bb").val('');
  //         } else
  //             return false
  //     })
  // } else {
  //     var i = e.params.args.data
  //     $.each(i, function (k, v) {
  //         $("#mk-" + k).val(v).change().keyup()
  //     })
  //     sum_mktotal()
  // }
});
$("#mk-id").change(function () {
  if (!this.value) $(".mk-fm").val("");
});
$(
  "#mk-harga_ppn, #mk-harga_penambahan, #mk-harga_penambahan_tanah, #mk-diskon",
).on("focusin", function () {
  $(this).data("val", $(this).val());
});
// $("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").change(function () {
//     var prev = $(this).data('val'),
//         current = $(this).val(),
//         th = $(this);

//     if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) {
//         Swal.fire({
//             title: 'Lakukan perubahan?',
//             text: "data pada tabel tagihan akan terhapus!",
//             type: 'warning',
//             showCancelButton: true,
//             confirmButtonColor: '#3085d6',
//             cancelButtonColor: '#d33',
//             confirmButtonText: 'Ya!',
//             confirmButtonClass: 'btn btn-primary',
//             cancelButtonClass: 'btn btn-danger ml-1',
//             buttonsStyling: !1
//         }).then(function (t) {
//             if (t.isConfirmed) {
//                 sum_mktotal()
//                 data_um = {}
//                 data_bb = {}
//                 $("#list_cicilan_here").html("")
//                 $("#total_cicilan_um").val(0).change().keyup()
//                 $("#total_cicilan_bb").val(0).change().keyup()
//                 $("#id_list_keu").val('');
//                 $("#id_list_keu_bb").val('');
//             } else
//                 th.val(prev)
//         })
//     } else
//         sum_mktotal()
// })

$("#isi_tagihan-modal").on("hidden.bs.modal", function () {
  data_um = {};
  data_bb = {};
});

// $("#mk-keterangan_harga_penambahan").change(function () {
//     // console.log(this.value)
//     if (this.value)
//         $("#berita_acara").append(`<option id='opt-keterangan_harga_penambahan'>${this.value}</option>`)
//     else
//         $("#berita_acara option[id='opt-keterangan_harga_penambahan']").remove()
// })

$("#berita_acara").change(function () {
  let nom = 0;
  switch (this.value) {
    case "Uang Muka":
      nom =
        parseFloat(removeComma($("#mk-uang_muka").val())) -
        parseFloat(removeComma($("#mk-diskon_uang_muka").val()));
      break;
    case "Biaya Administrasi":
      nom = parseFloat(removeComma($("#mk-biaya_adm").val()));
      break;
    case "Turun KPR":
      nom = parseFloat(removeComma($("#mk-harga_penambahan_um").val()));
      break;
    case "Biaya Kavling Strategis":
      nom = parseFloat(removeComma($("#mk-harga_penambahan").val()));
      break;
    case "Biaya Kelebihan Tanah":
      nom = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()));
      break;
    default:
      nom = 0;
      break;
  }
  changeVal("#nominal", nom);
});
$("#berita_acara_bb").change(function () {
  let nom = 0;
  switch (this.value) {
    case "PPN":
      nom = parseFloat(removeComma($("#mk-ppn").val()));
      break;
    case "BPHTB":
      nom = parseFloat(removeComma($("#mk-bphtb").val()));
      break;
    case "Biaya Proses":
      nom = parseFloat(removeComma($("#mk-biaya_proses").val()));
      break;
    default:
      nom = 0;
      break;
  }
  changeVal("#nominal_bb", nom);
});

// function ganti_kavling(){
//     if ($("#spptb_ttd_file").html() == 'Tidak ada data') {
//         return swal('error', 'Kamu harus mengunggah file SPPTB yang sudah ditandatangani')
//     }
//     Swal.fire({
//         title: 'Pindah kavling?',
//         text: "Apakah anda yakin akan memindahkan kavling?",
//         // type: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'Ya!',
//         confirmButtonClass: 'btn btn-primary',
//         cancelButtonClass: 'btn btn-danger ml-1',
//         buttonsStyling: !1
//     }).then(function (t) {
//         if (t.isConfirmed) {
//             id_mkdt_old = $("#idk-id_mkdt").val()
//             id_konsumen_old = $("#idk-id_konsumen").val()
//             is_ganti_nama = 'Pindah Kavling'

//             $("#btn-print_spptb").prop("href", "#")
//             $("#idk-id_konsumen, #idk-id_mkdt").val("")
//             $("#idk_data_konsumen-tab").click()
//         }
//     })
// }
function ganti_nama() {
  if ($("#spptb_ttd_file").html() == "Tidak ada data") {
    return swal(
      "error",
      "Kamu harus mengunggah file SPPTB yang sudah ditandatangani",
    );
  }
  Swal.fire({
    title: "Ganti nama konsumen?",
    text: "Apakah anda yakin akan mengganti nama konsumen?",
    // type: 'warning',
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya!",
    confirmButtonClass: "btn btn-primary",
    cancelButtonClass: "btn btn-danger ml-1",
    buttonsStyling: !1,
  }).then(function (t) {
    if (t.isConfirmed) {
      id_mkdt_old = $("#idk-id_mkdt").val();
      id_konsumen_old = $("#idk-id_konsumen").val();
      is_ganti_nama = "Ganti Nama";

      $("#btn-print_spptb").prop("href", "#");
      $(".gn, #idk-id_konsumen, #idk-id_mkdt").val("");
      $("#idk_data_konsumen-tab").click();
    }
  });
}

//sudah t  idak dipakai
// function isi_tagihan() {
//     var sh = editdtt[0],
//         id_kavling = sh.id.substr(3);

//     if (sh.data2.status_mkdt == 'Batal') {
//         Swal.fire({
//             //position: 'bottom-end',
//             icon: 'error',
//             title: "Status konsumen batal",
//             text: "Silahkan isi kavling dengan konsumen baru terlebih dahulu",
//             // showConfirmButton: false,
//             // timer: 1500
//         })
//         return;
//     }

//     if (!sh.data.id_mkdt) {
//         Swal.fire({
//             //position: 'bottom-end',
//             icon: 'error',
//             title: "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
//             showConfirmButton: false,
//             timer: 1500
//         })
//         return;
//     }

//     data_um = {}
//     data_bb = {}
//     $("#fm-isi_tagihan")[0].reset()
//     $("#list_cicilan_here").html("")
//     $("#total_cicilan_um").val(0).change().keyup()
//     $("#total_cicilan_bb").val(0).change().keyup()
//     $("#id_list_keu").val('');
//     $("#id_list_keu_bb").val('');

//     // $("#cicilan_belong_here").html("");
//     // $("#berita_acara0").val("Uang Muka 1");
//     // $("#nominal0").val(0).keyup();

//     $.ajax({
//         url: base_url + 'keuangan/get_data_by_id',
//         type: 'post',
//         data: {
//             [csrfName]: csrfHash,
//             id_keuangan: sh.data.id_keuangan,
//             id_kavling: id_kavling,
//             id_mkdt: sh.data.id_mkdt
//         },
//         dataType: 'json',
//         beforeSend: function() {
//             $("#loading").removeClass("hidden")
//         },
//         success: function(r) {
//             $("#loading").addClass("hidden")
//             let mkdt = r.mkdt,
//                 hj = r.hj,
//                 tg = r.tagihan

//             $("#mk-id_mkdt").val(sh.data.id_mkdt)

//             $('.select2').val(null).trigger('change');
//             if (hj.id) {
//                 // if (hj.id) {

//                 for (let i in hj) {
//                     $("#fm-isi_tagihan #mk-" + i).val(hj[i]).change().keyup();
//                 }
//                 $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(hj.tgl_harga));
//                 $("#fm-isi_tagihan #mk-tipe").val(hj.tipe_rumah);
//                 $("#mk-id").append(
//                     $("<option selected></option>")
//                     .attr("value", hj.id)
//                     .text("Rp. " + num_format(hj.hargajual) + " (" + hj.tipe_rumah + ")" + ": Per " + hj.tgl_harga)
//                 ).trigger('change');
//                 // } else {
//                 //     $(".mk-fm").val(0)
//                 // }
//             } else {
//                 $("#mk-id").append(
//                     $("<option selected></option>")
//                     .attr("value", mkdt.id_hargajual)
//                     .text("Rp. " + num_format(mkdt.harga_jual) + " (" + mkdt.tipe_rumah + ")" + ": " + mkdt.tgl_harga)
//                 ).trigger('change');

//                 $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(mkdt.tgl_harga));
//                 $("#mk-row").val(mkdt.row).change()
//                 $("#mk-tipe").val(mkdt.tipe_rumah).change()
//                 $("#mk-lb").val(mkdt.hj_lb).change()
//                 $("#mk-lt").val(mkdt.hj_lt).change()

//                 $("#mk-hargajual").val(mkdt.harga_jual).change()
//                 $("#mk-kpr").val(mkdt.harga_kpr).change()
//                 $("#mk-uang_muka").val(mkdt.harga_jual - mkdt.harga_kpr).change()
//                 $("#mk-bphtb").val(mkdt.harga_bphtb).change()
//                 $("#mk-biaya_adm").val(mkdt.harga_administrasi).change()
//                 $("#mk-biaya_proses").val(mkdt.harga_biaya_proses).change()
//             }
//             $("#mk-diskon").val(mkdt.harga_diskon).change().keyup()
//             $("#mk-harga_penambahan").val(mkdt.harga_penambahan).change().keyup()
//             $("#mk-harga_penambahan_tanah").val(mkdt.harga_penambahan_tanah).change().keyup()
//             $("#mk-keterangan_harga_penambahan").val(mkdt.keterangan_penambahan_biaya)

//             $("#mk-harga_ppn").val(mkdt.harga_ppn).change().keyup()
//             $("#mk-harga_kpr_acc").val(mkdt.harga_kpr_acc).change().keyup()

//             let turun_kpr = (mkdt.harga_kpr_acc == 0) ? 0 : mkdt.harga_kpr - mkdt.harga_kpr_acc;
//             $("#mk-harga_penambahan_um").val(turun_kpr).change().keyup()

//             sum_mktotal()

//             //load tagihan
//             if (tg) {
//                 let a = it;
//                 $.each(tg, function(i, v) {
//                     if (v.status == "UM") {
//                         data_um['lk' + a] = ({
//                             id_list_keu: 'lk' + a,
//                             id_keuangan: (v.id_keuangan),
//                             berita_acara: (v.berita_acara),
//                             nominal: num_format(v.nominal),
//                             jatuh_tempo_tgl: (v.jatuh_tempo_tgl),
//                         })
//                     }
//                     if (v.status == "BB") {
//                         data_bb['lk' + a] = ({
//                             id_list_keu_bb: 'lk' + a,
//                             id_keuangan_bb: (v.id_keuangan),
//                             berita_acara_bb: (v.berita_acara),
//                             nominal_bb: num_format(v.nominal),
//                             jatuh_tempo_tgl_bb: (v.jatuh_tempo_tgl),
//                         })
//                     }

//                     a++;
//                 })
//                 tambah_ketagihan()
//                 it = a;
//             }

//             $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
//             $('#isi_tagihan-modal').modal({
//                 backdrop: 'static',
//                 keyboard: false
//             });
//             //load data form
//             // for (let i in mkdt) {
//             //     $("#fm-isi_tagihan #" + i).val(mkdt[i]);
//             // }

//             // if (r.tagihan) {
//             //     it = 0;
//             //     let tg = r.tagihan
//             //     if (tg.length > 0) {
//             //         for (i = 0; i < tg.length; i++) {
//             //             if (i > 0)
//             //                 tambah();

//             //             fp = flatpickr("#fm-isi_tagihan #jatuh_tempo_tgl" + i, {
//             //                 altInput: true,
//             //                 altFormat: 'F j, Y',
//             //                 dateFormat: 'Y-m-d'
//             //             });
//             //             // console.log(tg[i]['id_keuangan']);
//             //             $("#fm-isi_tagihan #id_keuangan" + i).val(tg[i]['id_keuangan']);
//             //             $("#fm-isi_tagihan #nominal" + i).val(tg[i]['nominal']).keyup().change();
//             //             $("#fm-isi_tagihan #berita_acara" + i).val(tg[i]['berita_acara']);
//             //             document.querySelector("#fm-isi_tagihan #jatuh_tempo_tgl" + i)._flatpickr.setDate(tg[i]['jatuh_tempo_tgl']);
//             //         }
//             //     }
//             // }
//             // total('#fm-isi_tagihan');
//         },
//         error: function() {
//             $("#loading").addClass("hidden")
//             Swal.fire({
//                 //position: 'bottom-end',
//                 icon: 'error',
//                 title: "Terjadi kesalahan saat memuat data",
//                 showConfirmButton: false,
//                 timer: 1500
//             })
//             return;
//         }
//     });
// }

// $("#mk-jenis-diskon").change(function () {
//     if (this.value == "Harga Jual") {
//         $("#hjdis").removeClass("hidden")
//         // $("#umdis").addClass("hidden")
//     } else if (this.value == "Uang Muka") {
//         $("#hjdis").addClass("hidden")
//         // $("#umdis").removeClass("hidden")
//     }
//     sum_mktotal()
// })
$(
  "#add-form-isi-tagihan, #btn-ganti_nama, #add-form-btn-idk_keu, #btn-ganti_kavling",
).click(function (e) {
  e.preventDefault();
});

//sudah tidak di pakai
function save_isi_tagihan(e) {
  if (
    parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 ||
    parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0
  ) {
    if (
      $("#total_cicilan_um").val() != $("#mk-total_um").val() ||
      $("#total_cicilan_bb").val() != $("#mk-total_bb").val()
    ) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Total Cicilan tidak sesuai dengan total biaya",
        showConfirmButton: false,
        timer: 1500,
      });
      return false;
    }
  }
  let dt = {};
  dt[csrfName] = csrfHash;
  $("form#fm-idk_keu :input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  for (var k in data_um) {
    if (!data_um.hasOwnProperty(k)) continue;
    var obj = data_um[k];

    for (var d in obj) {
      if (!obj.hasOwnProperty(d)) continue;
      var x = obj[d];
      dt[d + "[" + i + "]"] = x;
    }
    i++;
  }
  i = 0;
  for (var k in data_bb) {
    if (!data_bb.hasOwnProperty(k)) continue;
    var obj = data_bb[k];

    for (var d in obj) {
      if (!obj.hasOwnProperty(d)) continue;
      var x = obj[d];
      dt[d + "[" + i + "]"] = x;
    }
    i++;
  }

  $.ajax({
    url: base_url + "Keuangan/isi_tagihan",
    type: "post",
    data: dt,
    dataType: "json",
    beforeSend: function () {
      $("#add-form-isi-tagihan").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#add-form-isi-tagihan").addClass("disabled");
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages || r.message || "Data berhasil disimpan",
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          // $('.modal').modal('hide');
          load_kavling();
          hapus_seleksi();
          $("#add-form-isi-tagihan").html("Simpan");
          $("#add-form-isi-tagihan").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages || r.message || "Terjadi kesalahan",
          showConfirmButton: false,
          // timer: 1500
        }).then(function () {
          $("#add-form-isi-tagihan").html("Simpan");
          $("#add-form-isi-tagihan").removeClass("disabled");
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
      }).then(function () {
        // $('.modal').modal('hide');
        $("#add-form-isi-tagihan").html("Simpan");
        $("#add-form-isi-tagihan").removeClass("disabled");
      });
      $("#add-form-isi-tagihan").html("Simpan");
      $("#add-form-isi-tagihan").removeClass("disabled");
    },
  });
}

function badgeStatus(s) {
  return s == 1
    ? '<span class="badge badge-success">Sudah Cair</span>'
    : '<span class="badge badge-secondary">Belum Cair</span>';
}

/* ************************ dana akad ************************ */
let dajamState = {
  idKavling: null,
  idMkdt: null,
  sh: null,
  list: [],
  historyLoaded: false,
};

function syncDanaJaminanToken(response) {
  if (response && response.token) {
    csrfHash = response.token;
    $(`input[name="${csrfName}"]`).val(csrfHash);
  }
}

function dajamMoney(value) {
  return num_format(parseFloat(value || 0));
}

function dajamEscape(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function dajamBadgeStatus(status) {
  return parseInt(status) === 1
    ? '<span class="badge badge-success">Sudah Cair</span>'
    : '<span class="badge badge-secondary">Pengajuan</span>';
}

function dana_akad() {
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  const sh = editdtt[0];
  const idKavling = sh.id.substr(3);

  if (!sh.data.id_mkdt) {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
    );
  }

  if (sh.data2.status_mkdt != "Akad") {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "Belum Akad!",
    );
  }

  dajamState = {
    idKavling: idKavling,
    idMkdt: sh.data.id_mkdt,
    sh: sh,
    list: [],
    historyLoaded: false,
  };

  $("#dana_akad_modal").modal({
    backdrop: "static",
    keyboard: false,
  });
  loadDanaJaminanData();
}

function loadDanaJaminanData() {
  if (!dajamState.idKavling || !dajamState.idMkdt) return;

  $("#fm-dana_akad")[0].reset();
  $("#form-pencairan")[0].reset();
  $("#da-jaminan_here").html("");
  $("#da-pengajuan-item_here").html("");
  $("#tbl-riwayat tbody").html("");

  $.ajax({
    url: base_url + "keuangan/getDanaAkad",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: dajamState.idKavling,
      id_mkdt: dajamState.idMkdt,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      syncDanaJaminanToken(r);
      if (r.success === false) {
        return swal("error", "Terjadi kesalahan", r.messages || r.message || "Data tidak ditemukan");
      }

      dajamState.list = r.list_dajam || [];
      $("#da-id_mkdt, #dajam-pengajuan-id_mkdt").val(dajamState.idMkdt);
      $("#da-id_kavling, #dajam-pengajuan-id_kavling").val(dajamState.idKavling);
      $("#da-status_mkdt").text(r.mkdt?.status_mkdt || "-");
      $("#da-dajam_selesai").prop("checked", parseInt(r.mkdt?.dajam_selesai || 0) === 1);

      const sh = dajamState.sh;
      $(".label_alamat").html(
        dt_proyek.nama_proyek +
          "<br/>" +
          sh.data.nama_jalan +
          ", No." +
          sh.data.no_kavling +
          "<br/>" +
          sh.data2.no_tipe_rumah +
          " (" +
          sh.data2.tipe_rumah +
          ")<br/>",
      );
      $(".label_konsumen").text(sh.data2.nama_konsumen || sh.data.nama_konsumen || "-");

      $("#da-kpr_acc").val(r.mkdt?.harga_kpr_acc || 0);
      renderDanaJaminanItems(dajamState.list);
      renderPengajuanItems(dajamState.list);
      renderPengajuanTable(r.list_pengajuan || []);
      hitung_dana_akad();
    },
    error: function (a, b, c) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan", c);
    },
  });
}

function renderDanaJaminanItems(list) {
  if (!list.length) {
    $("#da-jaminan_here").html('<div class="keu-dj-empty">Belum ada master item dana jaminan.</div>');
    return;
  }

  let rows = "";
  $.each(list, function (i, v) {
    const idListDajam = v.id_list_dajam ? v.id_list_dajam : v.id_list_dajam_ori;
    const idDajam = v.id == null ? "n" + i : v.id;
    const isCair = parseInt(v.sudah_cair || 0) === 1;
    const nominalReadonly = isCair ? "readonly" : "";
    const cairDisabled = isCair ? "disabled" : "disabled";
    rows += `
      <tr>
        <td>
          <strong>${dajamEscape(v.nama_jaminan)}</strong>
          <input type="hidden" value="${idListDajam}" name="id_dajam[${idDajam}][id_list_dajam]" />
        </td>
        <td>
          <input type="text" value="${v.nominal ? v.nominal : 0}"
            name="id_dajam[${idDajam}][nominal]"
            class="form-control num daf"
            ${nominalReadonly}
            onchange="hitung_dana_akad()" />
        </td>
        <td class="text-center">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" value="1"
              id="da-cair-${idDajam}"
              name="id_dajam[${idDajam}][sudah_cair]"
              onclick="is_cair(this, '${idDajam}')"
              ${isCair ? "checked disabled" : ""} />
            <label class="custom-control-label" for="da-cair-${idDajam}">${isCair ? "Cair" : "Belum"}</label>
          </div>
        </td>
        <td>
          <input type="text" value="${v.nominal_cair ? v.nominal_cair : 0}"
            name="id_dajam[${idDajam}][nominal_cair]"
            class="form-control num cl${idDajam}"
            ${isCair ? "disabled" : cairDisabled} />
        </td>
        <td>
          <input type="text" value="${v.tgl_cair ? v.tgl_cair : ""}"
            name="id_dajam[${idDajam}][tgl_cair]"
            class="form-control flatpickr-human-friendly fp-dajam cl${idDajam}"
            ${isCair ? "disabled" : cairDisabled} />
        </td>
        <td>
          <textarea rows="2" class="form-control cl${idDajam}"
            name="id_dajam[${idDajam}][keterangan]"
            ${isCair ? "disabled" : cairDisabled}>${dajamEscape(v.keterangan)}</textarea>
        </td>
      </tr>
    `;
  });

  $("#da-jaminan_here").html(`
    <div class="table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead>
          <tr>
            <th>Nama Jaminan</th>
            <th width="18%">Nominal</th>
            <th width="12%">Status</th>
            <th width="18%">Nominal Cair</th>
            <th width="15%">Tanggal Cair</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `);

  flatpickr(".fp-dajam", {
    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
  });
  $("#fm-dana_akad .num").keyup();
}

function renderPengajuanItems(list) {
  const available = list.filter(function (v) {
    return v.id && parseInt(v.sudah_cair || 0) !== 1 && parseFloat(v.nominal || 0) > 0;
  });

  if (!available.length) {
    $("#da-pengajuan-item_here").html('<div class="keu-dj-empty">Tidak ada item yang bisa diajukan. Simpan nominal dulu atau semua item sudah cair.</div>');
    return;
  }

  let rows = "";
  available.forEach(function (v) {
    rows += `
      <label class="d-flex align-items-center justify-content-between border rounded px-1 py-50 mb-50">
        <span>
          <input type="checkbox" name="items[]" value="${v.id}" class="mr-50">
          <strong>${dajamEscape(v.nama_jaminan)}</strong>
        </span>
        <span>Rp ${dajamMoney(v.nominal)}</span>
      </label>
    `;
  });
  $("#da-pengajuan-item_here").html(rows);
}

function renderPengajuanTable(rows) {
  const tb = document.querySelector("#tbl-riwayat tbody");
  tb.innerHTML = "";

  if (!rows.length) {
    tb.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada pengajuan.</td></tr>';
    return;
  }

  rows.forEach(function (row, i) {
    const details = row.details || [];
    const itemText = details.length
      ? details.map((d) => `${dajamEscape(d.nama_jaminan)} (Rp ${dajamMoney(d.nominal_pengajuan)})`).join("<br>")
      : "-";
    const lampiran = row.access_url
      ? `<a href="${row.access_url}" target="_blank" class="btn btn-link btn-sm">Lihat</a>`
      : "-";
    const action = parseInt(row.status_cair || 0) === 1
      ? '<span class="text-muted">Selesai</span>'
      : `<button type="button" class="btn btn-success btn-sm" onclick="toggleCairPengajuan(${row.id})">Cairkan</button>`;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${row.tanggal_pengajuan ?? ""}</td>
      <td>${itemText}</td>
      <td>${dajamBadgeStatus(row.status_cair)}</td>
      <td>${lampiran}</td>
      <td>${action}</td>
    `;
    tb.appendChild(tr);

    if (parseInt(row.status_cair || 0) !== 1) {
      const formRow = document.createElement("tr");
      formRow.id = `dajam-cair-row-${row.id}`;
      formRow.className = "d-none";
      formRow.innerHTML = `<td colspan="6">${renderCairForm(row)}</td>`;
      tb.appendChild(formRow);
    }
  });
}

function renderCairForm(row) {
  const today = new Date().toISOString().slice(0, 10);
  const detailRows = (row.details || []).map(function (d) {
    if (parseInt(d.status_cair || 0) === 1) {
      return `
        <tr>
          <td><strong>${dajamEscape(d.nama_jaminan)}</strong></td>
          <td>Rp ${dajamMoney(d.nominal_cair || d.nominal_pengajuan)}</td>
          <td>${d.tanggal_cair || "-"}</td>
          <td>${dajamEscape(d.keterangan_cair || "-")}</td>
        </tr>
      `;
    }

    return `
      <tr>
        <td><strong>${dajamEscape(d.nama_jaminan)}</strong></td>
        <td>
          <input type="text" class="form-control num" name="items[${d.id}][nominal_cair]" value="${d.nominal_pengajuan || 0}">
        </td>
        <td>
          <input type="date" class="form-control" name="items[${d.id}][tanggal_cair]" value="${today}">
        </td>
        <td>
          <textarea class="form-control" rows="2" name="items[${d.id}][keterangan_cair]" placeholder="Keterangan cair"></textarea>
        </td>
      </tr>
    `;
  }).join("");

  return `
    <form id="dajam-cair-form-${row.id}" onsubmit="submitCairPengajuan(${row.id}); return false;">
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-1">
          <thead>
            <tr>
              <th>Item</th>
              <th width="22%">Nominal Cair</th>
              <th width="18%">Tanggal Cair</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>${detailRows}</tbody>
        </table>
      </div>
      <button type="submit" class="btn btn-primary btn-sm">Simpan Pencairan</button>
    </form>
  `;
}

function toggleCairPengajuan(id) {
  $(`#dajam-cair-row-${id}`).toggleClass("d-none");
  $(`#dajam-cair-form-${id} .num`).keyup();
}

function hitung_dana_akad() {
  let total = parseFloat(removeComma($("#da-kpr_acc").val())) || 0;
  let totalDajam = 0;
  $(".daf").each(function () {
    totalDajam += parseFloat(removeComma($(this).val())) || 0;
  });
  const hasilAkad = total - totalDajam;
  $("#da-total_dajam").val(totalDajam);
  $("#da-hasil_akad").val(hasilAkad);
  $("#da-kpr_acc-label").text(dajamMoney(total));
  $("#da-total_dajam-label").text(dajamMoney(totalDajam));
  $("#da-hasil_akad-label").text(dajamMoney(hasilAkad));
}

function is_cair(e, id) {
  const isTrue = $(e).prop("checked");
  $(`.cl${id}`).prop("disabled", !isTrue);
  if (isTrue) {
    const nominal = $(`[name="id_dajam[${id}][nominal]"]`).val();
    const nominalCair = $(`[name="id_dajam[${id}][nominal_cair]"]`);
    if (parseFloat(removeComma(nominalCair.val())) <= 0) {
      nominalCair.val(nominal).keyup();
    }
  }
}

function save_dana_akad() {
  $.ajax({
    url: base_url + "keuangan/saveDanaAkad",
    type: "post",
    data: $("#fm-dana_akad").serialize() + "&" + csrfName + "=" + csrfHash,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#add-form-btn-dana_akad", true);
    },
    success: function (r) {
      syncDanaJaminanToken(r);
      simpanBtn("#add-form-btn-dana_akad", false);
      if (r.success === true) {
        swal("success", r.messages || r.message || "Data berhasil disimpan");
        loadDanaJaminanData();
        loadDajamHistory(true);
        load_kavling();
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: false,
      });
      simpanBtn("#add-form-btn-dana_akad", false);
    },
  });
}

function savePengajuanDajam() {
  if (!$("#form-pencairan input[name='items[]']:checked").length) {
    return swal("error", "Gagal Menyimpan Data", "Pilih minimal satu item yang diajukan");
  }

  const fd = new FormData($("#form-pencairan")[0]);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "pencairan/store",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#btn-saveDanaJaminan", true);
    },
    success: function (r) {
      syncDanaJaminanToken(r);
      simpanBtn("#btn-saveDanaJaminan", false);
      if (r.success === true) {
        swal("success", r.messages || r.message || "Pengajuan berhasil disimpan");
        loadDanaJaminanData();
        loadDajamHistory(true);
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      simpanBtn("#btn-saveDanaJaminan", false);
      swal("error", "Terjadi kesalahan", "Pengajuan gagal disimpan");
    },
  });
}

function submitCairPengajuan(id) {
  const fd = new FormData($(`#dajam-cair-form-${id}`)[0]);
  fd.append("id_mkdt", dajamState.idMkdt);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "pencairan/cairkan/" + id,
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
      syncDanaJaminanToken(r);
      if (r.success === true) {
        swal("success", r.messages || r.message || "Pencairan berhasil disimpan");
        loadDanaJaminanData();
        loadDajamHistory(true);
        load_kavling();
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      $("#loading").addClass("hidden");
      swal("error", "Terjadi kesalahan", "Pencairan gagal disimpan");
    },
  });
}

function loadDajamHistory(force = false) {
  if (!dajamState.idKavling) return;
  if (dajamState.historyLoaded && !force) return;

  $.ajax({
    url: base_url + "pencairan/history/" + dajamState.idKavling,
    type: "get",
    dataType: "json",
    success: function (r) {
      syncDanaJaminanToken(r);
      dajamState.historyLoaded = true;
      const rows = r.data || [];
      if (!rows.length) {
        $("#da-history_here").html('<div class="keu-dj-empty">Belum ada history dana jaminan.</div>');
        return;
      }

      let html = `
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Aksi</th>
                <th>Deskripsi</th>
                <th>User</th>
              </tr>
            </thead>
            <tbody>
      `;
      rows.forEach(function (row) {
        html += `
          <tr>
            <td>${row.created_at || ""}</td>
            <td><span class="badge badge-light-primary">${dajamEscape(row.aksi)}</span></td>
            <td>${dajamEscape(row.deskripsi)}</td>
            <td>${dajamEscape(row.username || "-")}</td>
          </tr>
        `;
      });
      html += "</tbody></table></div>";
      $("#da-history_here").html(html);
    },
  });
}

/* ************************ pencairan bank kpr ************************ */
let bankKprState = {
  idKavling: null,
  idMkdt: null,
  sh: null,
  rows: [],
  banks: [],
  summary: {},
  mkdt: {},
};

function bankKprMoney(value) {
  return num_format(parseFloat(value || 0));
}

function bankKprEscape(value) {
  return dajamEscape(value);
}

function bankKprBadge(status) {
  if (status === "cair") {
    return '<span class="badge badge-success">Cair</span>';
  }
  if (status === "void") {
    return '<span class="badge badge-light-danger">Void</span>';
  }
  return '<span class="badge badge-secondary">Draft</span>';
}

function pencairan_bank() {
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  const sh = editdtt[0];
  const idKavling = sh.id.substr(3);

  if (!sh.data.id_mkdt) {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
    );
  }

  if (sh.data2.status_mkdt != "Akad") {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "Belum Akad!",
    );
  }

  bankKprState = {
    idKavling: idKavling,
    idMkdt: sh.data.id_mkdt,
    sh: sh,
    rows: [],
    banks: [],
    summary: {},
    mkdt: {},
  };

  loadBankKprData({
    openModal: true,
    warnMissingDajam: true,
  });
}

function loadBankKprData(options = {}) {
  if (!bankKprState.idKavling || !bankKprState.idMkdt) return;

  $("#form-bank-kpr")[0].reset();
  $("#tbl-bank-kpr tbody").html("");
  $("#bank-kpr-current-file").html("");

  $.ajax({
    url: base_url + "keuangan/pencairan-bank/get",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: bankKprState.idKavling,
      id_mkdt: bankKprState.idMkdt,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      syncDanaJaminanToken(r);
      if (r.success === false) {
        return swal("error", "Terjadi kesalahan", r.messages || r.message || "Data tidak ditemukan");
      }

      bankKprState.rows = r.rows || [];
      bankKprState.banks = r.banks || [];
      bankKprState.summary = r.summary || {};
      bankKprState.mkdt = r.mkdt || {};

      if (options.warnMissingDajam && !bankKprState.summary.has_dana_jaminan_nominal) {
        return swal(
          "warning",
          "Dana Jaminan belum diisi",
          "Isi nominal Dana Jaminan dulu sebelum mencatat pencairan bank KPR.",
        );
      }

      if (options.openModal) {
        $("#bank_kpr_modal").modal({
          backdrop: "static",
          keyboard: false,
        });
      }

      renderBankKprHeader();
      renderBankKprOptions();
      renderBankKprSummary();
      renderBankKprRows();
      resetBankKprForm();
    },
    error: function (a, b, c) {
      $("#loading").addClass("hidden");
      return swal("error", "Terjadi kesalahan", c);
    },
  });
}

function renderBankKprHeader() {
  const sh = bankKprState.sh;
  $("#bank-kpr-id-mkdt").val(bankKprState.idMkdt);
  $("#bank-kpr-id-kavling").val(bankKprState.idKavling);
  $("#bank-kpr-label-alamat").html(
    dt_proyek.nama_proyek +
      "<br/>" +
      sh.data.nama_jalan +
      ", No." +
      sh.data.no_kavling +
      "<br/>" +
      sh.data2.no_tipe_rumah +
      " (" +
      sh.data2.tipe_rumah +
      ")<br/>",
  );
  $("#bank-kpr-label-konsumen").text(sh.data2.nama_konsumen || sh.data.nama_konsumen || "-");
  $("#bank-kpr-status-mkdt").text(bankKprState.mkdt.status_mkdt || "-");
  $("#bank-kpr-bank-akad").text(bankKprState.mkdt.bank || bankKprState.sh.data2.bank || "-");
}

function renderBankKprOptions() {
  let options = '<option value="">Pilih Bank</option>';
  bankKprState.banks.forEach(function (bank) {
    options += `<option value="${bank.id}">${bankKprEscape(bank.bank)}${bank.keterangan ? " (" + bankKprEscape(bank.keterangan) + ")" : ""}</option>`;
  });
  $("#bank-kpr-id-bank").html(options);
}

function renderBankKprSummary() {
  const s = bankKprState.summary || {};
  $("#bank-kpr-plafon-label").text(bankKprMoney(s.nominal_plafon));
  $("#bank-kpr-total-cair-label").text(bankKprMoney(s.total_cair));
  $("#bank-kpr-retensi-label").text(bankKprMoney(s.total_retensi));
  $("#bank-kpr-sisa-label").text(bankKprMoney(s.sisa_plafon));
}

function renderBankKprRows() {
  const tb = document.querySelector("#tbl-bank-kpr tbody");
  tb.innerHTML = "";

  if (!bankKprState.rows.length) {
    tb.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Belum ada pencairan bank.</td></tr>';
    return;
  }

  bankKprState.rows.forEach(function (row, i) {
    const lampiran = row.access_url
      ? `<a href="${row.access_url}" target="_blank" class="btn btn-link btn-sm">Lihat</a>`
      : "-";
    const isVoid = row.status === "void";
    const action = isVoid
      ? '<span class="text-muted">Void</span>'
      : `
        <button type="button" class="btn btn-outline-primary btn-sm" title="Edit pencairan" onclick="editBankKprDisbursement(${row.id})">
          <i class="fas fa-edit"></i>
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm ml-25" title="Void pencairan" onclick="voidBankKprDisbursement(${row.id})">
          <i class="fas fa-ban"></i>
        </button>
      `;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${i + 1}</td>
      <td>${bankKprBadge(row.status)}</td>
      <td>${bankKprEscape(row.bank || "-")}</td>
      <td>${row.tanggal_cair ? format_date(row.tanggal_cair) : "-"}</td>
      <td class="text-right">Rp ${bankKprMoney(row.nominal_cair)}</td>
      <td class="text-right">Rp ${bankKprMoney(row.nominal_retensi)}</td>
      <td>${lampiran}</td>
      <td>${action}</td>
    `;
    tb.appendChild(tr);
  });
}

function resetBankKprForm() {
  $("#form-bank-kpr")[0].reset();
  $("#bank-kpr-id").val("");
  $("#bank-kpr-id-mkdt").val(bankKprState.idMkdt);
  $("#bank-kpr-id-kavling").val(bankKprState.idKavling);
  $("#bank-kpr-status").val("draft");
  $("#bank-kpr-id-bank").val(bankKprState.mkdt.id_bank || "");
  $("#bank-kpr-nominal-plafon").val(bankKprState.summary.nominal_plafon || bankKprState.mkdt.harga_kpr_acc || 0);
  $("#bank-kpr-nominal-cair").val(0);
  $("#bank-kpr-nominal-retensi").val(bankKprState.summary.total_dana_jaminan || 0);
  $("#bank-kpr-current-file").html("");
  $("#bank_kpr_modal .num").keyup();
}

function findBankKprRow(id) {
  return bankKprState.rows.find(function (row) {
    return parseInt(row.id) === parseInt(id);
  });
}

function editBankKprDisbursement(id) {
  const row = findBankKprRow(id);
  if (!row) return;

  $("#bank-kpr-id").val(row.id);
  $("#bank-kpr-id-mkdt").val(row.id_mkdt);
  $("#bank-kpr-id-kavling").val(row.id_kavling);
  $("#bank-kpr-id-bank").val(row.id_bank || "");
  $("#bank-kpr-nominal-plafon").val(row.nominal_plafon || 0);
  $("#bank-kpr-nominal-cair").val(row.nominal_cair || 0);
  $("#bank-kpr-nominal-retensi").val(row.nominal_retensi || 0);
  $("#bank-kpr-tanggal-cair").val(row.tanggal_cair || "");
  $("#bank-kpr-rekening").val(row.rekening_tujuan || "");
  $("#bank-kpr-referensi").val(row.no_referensi || "");
  $("#bank-kpr-keterangan").val(row.keterangan || "");
  $("#bank-kpr-status").val(row.status === "cair" ? "cair" : "draft");
  $("#bank-kpr-current-file").html(
    row.access_url
      ? `<a href="${row.access_url}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat bukti tersimpan</a>`
      : "",
  );
  $("#bank_kpr_modal .num").keyup();
  $("#bank-kpr-form-tab").tab("show");
}

function saveBankKprDisbursement() {
  const fd = new FormData($("#form-bank-kpr")[0]);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "keuangan/pencairan-bank/save",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn("#btn-save-bank-kpr", true);
    },
    success: function (r) {
      syncDanaJaminanToken(r);
      simpanBtn("#btn-save-bank-kpr", false);
      if (r.success === true) {
        swal("success", r.messages || r.message || "Pencairan bank berhasil disimpan");
        loadBankKprData();
        load_kavling();
        $("#bank-kpr-history-tab").tab("show");
      } else {
        swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
      }
    },
    error: function () {
      simpanBtn("#btn-save-bank-kpr", false);
      swal("error", "Terjadi kesalahan", "Pencairan bank gagal disimpan");
    },
  });
}

function voidBankKprDisbursement(id) {
  Swal.fire({
    title: "Void pencairan bank?",
    text: "Ledger income terkait akan ikut di-void.",
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
      url: base_url + "keuangan/pencairan-bank/void/" + id,
      type: "post",
      data: {
        [csrfName]: csrfHash,
      },
      dataType: "json",
      beforeSend: function () {
        $("#loading").removeClass("hidden");
      },
      success: function (r) {
        $("#loading").addClass("hidden");
        syncDanaJaminanToken(r);
        if (r.success === true) {
          swal("success", r.messages || r.message || "Pencairan bank berhasil di-void");
          loadBankKprData();
          load_kavling();
        } else {
          swal("error", "Terjadi kesalahan", r.messages || r.message || "Terjadi kesalahan");
        }
      },
      error: function () {
        $("#loading").addClass("hidden");
        swal("error", "Terjadi kesalahan", "Void pencairan bank gagal");
      },
    });
  });
}

// ################################## Pencairan Akad ##################################
function pencairan_akad() {
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  const sh = editdtt[0];
  const idKavling = sh.id.substr(3);

  if (!sh.data.id_mkdt) {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Belum ada data konsumen di kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
    );
  }

  if (sh.data2.status_mkdt != "Akad") {
    return swal(
      "error",
      "Terjadi kesalahan",
      "Kavling" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "Belum Akad!",
    );
  }

  pencairanAkadState = {
    idKavling: idKavling,
    idMkdt: sh.data.id_mkdt,
    sh: sh,
    mkdt: {},
    plan: null,
    items: [],
    listDajam: [],
    pengajuan: [],
  };

  loadPencairanAkadData(true);
}

function getRiwayatGantinama() {
  if (!editdtt[0]) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }
  let sh = editdtt[0];

  $.ajax({
    url: base_url + "keuangan/get_riwayat_gantinama",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
    },
    dataType: "json",
    beforeSend: function () {
      simpanBtn(
        "#btn-refresh-ganti_nama",
        true,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
    },
    success: function (x) {
      csrfHash = x.token;

      let tb = "<tr><td colspan = 2> Tidak Ada Data</td></tr>";
      if (x.riwayat) {
        tb = "";
        let n = 1;
        x.riwayat.forEach((e) => {
          tb += `
                    <tr>
                            <td>${n}</td>
                            <td>
                                ${
                                  e.file_spptb_access_url
                                    ? `<a href="${e.file_spptb_access_url}" target=_blank class="btn btn-outline-primary">Klik untuk melihat file SPPTB Seblumnya</a>`
                                    : "-"
                                }
                            </td>
                            <td>
                                -
                            </td>
                        </tr>"`;
          n++;
        });
      }

      $("#riwayat_ganti_nama-here").html(tb);

      simpanBtn(
        "#btn-refresh-ganti_nama",
        false,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
    },
    error: function (xhr, st, err) {
      simpanBtn(
        "#btn-refresh-ganti_nama",
        false,
        'Sedang Memuat <i class="fa fa-spinner fa-spin"></i>',
        "Muat ulang riwayat",
      );
      return swal("error", err);
    },
  });
}

$(".modal").on("hidden.bs.modal", function () {
  data_um = {};
  data_bb = {};
});

$("#idk_riwayat-tab").click(function () {
  getRiwayatGantinama();
});

$("#form-pencairan").on("submit", function (e) {
  e.preventDefault();
  savePengajuanDajam();
});

$("#form-bank-kpr").on("submit", function (e) {
  e.preventDefault();
  saveBankKprDisbursement();
});

$("#keu-history-dajam-tab").on("shown.bs.tab", function () {
  loadDajamHistory();
});

/****************************** end of dana akad ****************************************/
/****************************** end of keunagan ****************************************/
/****************************** Cash Out ****************************************/
var co = [];
$("#co-untuk_pembayaran").select2({
  placeholder: "Pilih Item Pembayaran",
  allowClear: true,
  ajax: {
    url: base_url + "keuangan/cashout/listitem/ambil",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.list_item, function (k, v) {
        results.push({
          id: v.id,
          text: v.item,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

function cashoutBiayaToNumber(value) {
  if (value === null || value === undefined || value === "") return 0;
  return parseFloat(String(value).replace(/,/g, "")) || 0;
}

function renderCashoutBiayaMkdt(biaya = {}) {
  $("#fm-co-biaya-mkdt [data-cashout-biaya-mkdt]").each(function () {
    const key = $(this).attr("data-cashout-biaya-mkdt");
    $(this).html("Rp. " + num_format(cashoutBiayaToNumber(biaya[key])));
  });
}

function hapus_cashout(id) {
  Swal.fire({
    title: "Hapus Data?",
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
      let sbtn = ".co-del-btn";
      $.ajax({
        url: base_url + "keuangan/cashout/delete",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id: id,
        },
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true, '<i class="fa fa-spinner fa-spin"></i>');
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
            isi_cashout(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
          }
        },
        error: function (r) {
          Swal.fire({
            //position: 'bottom-end',
            icon: "error",
            title: "terjadi kesalahan",
            showConfirmButton: false,
            // timer: 1500
          });
          simpanBtn(sbtn, false, "", '<i class="fa fa-trash"></i>');
        },
      });
    }
  });
}
function isi_cashout(id_kav = null) {
  if (!editdtt[0] && !id_kav) {
    return swal("error", "Tidak ada kavling yang dipilih");
  }

  var sh = editdtt[0],
    id_kavling = id_kav ?? sh.id.substr(3);

  co = [];

  $("#fm-cashout-keu")[0].reset();
  $("#cashout-table tbody").html("");
  renderCashoutBiayaMkdt({});
  $.ajax({
    url: base_url + "keuangan/cashout/ambil",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (r) {
      $("#loading").addClass("hidden");
      csrfHash = r.token;
      let d = Array.isArray(r.riwayat_bayar) ? r.riwayat_bayar : [];
      let konsumen = r.konsumen || {};
      let hasKonsumen = Object.keys(konsumen).length > 0;
      renderCashoutBiayaMkdt(r.biaya_mkdt || {});

      $("#co-untuk_pembayaran, #co-tanggal_bayar, #co-nominal, #co-keterangan, #add-form-btn-cashout").prop(
        "disabled",
        !hasKonsumen,
      );

      if (d.length == 0) {
        $("#cashout-table tbody").html(
          "<tr><td colspan='5' class='text-center'>Data tidak ditemukan</td></tr>",
        );
      } else {
        $.each(d, function (index, val) {
          let btn = `<button type="button" class="btn btn-danger btn-sm co-del-btn" onclick="hapus_cashout(${val.id})"><i class="fa fa-trash"></i></button>`;
          let row = `
        <tr>
            <td>${btn}</td>
            <td>${val.item}</td>
            <td>${format_date(val.tanggal_bayar) ?? "-"}</td>
            <td>${num_format(val.nominal) ?? "0"}</td>
            <td>${val.keterangan ?? "-"}</td>
        </tr>`;
          $("#cashout-table tbody").append(row);
        });
      }

      $("#cashout-id_kavling").val(id_kavling);

      let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;
      let label_alamat = setLabelAlamat(
        nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $("#modal-cashout-keu .label_alamat").html(label_alamat);

      // load label konsumen
      $("#fm-co-label_konsumen").html(konsumen.nama_konsumen || "-");
      $("#fm-co-label_tgl").html(konsumen.booking_tgl ? format_date(konsumen.booking_tgl) : "-");
      $("#fm-co-label_bookingfee").html(num_format(cashoutBiayaToNumber(konsumen.harga_jual)));
      initModalListener("#modal-cashout-keu");
      $("#modal-cashout-keu").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (r) {
      $("#loading").addClass("hidden");
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
    },
  });
}

function save_cashout() {
  if ($("#co-untuk_pembayaran").val() == null) {
    return swal("error", "Item pembayaran harus diisi");
  }
  if ($("#co-tanggal_bayar").val() == "") {
    return swal("error", "Tanggal pembayaran harus diisi");
  }
  if ($("#co-nominal").val() == "" || $("#co-nominal").val() <= 0) {
    return swal("error", "Nominal pembayaran harus diisi");
  }

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
      let sbtn = "#add-form-btn-cashout";
      $.ajax({
        url: base_url + "keuangan/cashout/save",
        type: "post",
        data:
          $("#fm-cashout-keu").serialize() + "&" + csrfName + "=" + csrfHash,
        dataType: "json",
        beforeSend: function () {
          simpanBtn(sbtn, true);
        },
        success: function (r) {
          csrfHash = r.token;
          if (r.success === true) {
            swal("success", r.messages);
            simpanBtn(sbtn, false);
            isi_cashout(r.id_kavling);
          } else {
            swal("error", "Terjadi kesalahan", r.messages);
            simpanBtn(sbtn, false);
          }
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
  });
}

/****************************** End Of Cash Out ****************************************/
