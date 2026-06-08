let loadmore = 0;
//load infinite scroll
var offset = 5; // Halaman awal
var start = 0;
var isLoading = false;

(function (window, undefined) {
  "use strict";
  /*
    NOTE:
    ------
    PLACE HERE YOUR OWN JAVASCRIPT CODE IF NEEDED
    WE WILL RELEASE FUTURE UPDATES SO IN ORDER TO NOT OVERWRITE YOUR JAVASCRIPT CODE PLEASE CONSIDER WRITING YOUR SCRIPT HERE.  */
  function num_cleave() {
    $(".num")
      .toArray()
      .forEach(function (field) {
        new Cleave(field, {
          numericOnly: true,
          numeral: true,
          numeralDecimalMark: ".",
          delimiter: ",",
          numeralThousandsGroupStyle: "thousand",
        });
      });
  }
  num_cleave();

  $(document).on("keyup", ".num", function () {
    num_cleave();
    // var n = parseInt($(this).val().replace(/\D/g, ''), 10);
    // if (!isNaN(n)) {
    //   $(this).val(n.toLocaleString());
    // } else {
    //   $(this).val(0);
    // }
  });
  // $(".num").keyup(function () {
  //   var n = parseInt($(this).val().replace(/\D/g, ''), 10);
  //   if (!isNaN(n)) {
  //     $(this).val(n.toLocaleString());
  //   } else {
  //     $(this).val("");
  //   }
  // });

  //disable selection after dblclick
  document.addEventListener(
    "mousedown",
    function (event) {
      if (event.detail > 1) {
        event.preventDefault();
        // of course, you still do not know what you prevent here...
        // You could also check event.ctrlKey/event.shiftKey/event.altKey
        // to not prevent something useful.
      }
    },
    false,
  );

  getNotif();
})(window);

function resolveFileHref(src, placeholder = null) {
  if (!isNotEmpty(src)) {
    return placeholder ? base_url + placeholder : null;
  }

  src = String(src);
  if (/^(https?:)?\/\//.test(src) || src.startsWith("/")) {
    return src;
  }

  return base_url + src;
}

function setBtnHref(id, target, teks = null) {
  $(id).off("click"); // hapus semua event click sebelumnya
  if (
    target != "" &&
    target != null &&
    target != undefined &&
    target != "null"
  ) {
    teks = "Klik untuk melihat file";
    $(id).on("click", function (e) {
      e.preventDefault();
      window.open(resolveFileHref(target), "_blank");
    });
  } else {
    teks = "Berkas tidak tersedia";
    $(id).on("click", function (e) {
      e.preventDefault();
      showToast("Berkas tidak tersedia", "warning");
    });
  }
  $(id).html(teks);
}
function showToast(message, type = "primary") {
  const id = "toast-" + Date.now();
  $(".toast-container").css("z-index", 1080);

  const toastHtml = `
        <div id="${id}" class="toast" role="alert" data-delay="5000" data-autohide="true">
            <div class="toast-header bg-${type} text-white" data-bs-dismiss="toast">
                <strong class="me-auto">Info</strong>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;

  const container = $("#toast-container");
  container.append(toastHtml);

  const toastElement = $("#" + id);
  const toast = new bootstrap.Toast(toastElement[0], {
    delay: 5000,
  });

  toast.show();

  // Hapus dari DOM setelah selesai
  toastElement.on("hidden.bs.toast", function () {
    $(this).remove();
    $(".toast-container").css("z-index", 0);
  });
}

function num_format(e) {
  if (e == null) return e;
  if (!isNaN(e)) return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return e;
}

function removeComma(e) {
  if (e === null || e === undefined || e === "") return 0; // atau return e sesuai kebutuhan
  if (Number.isInteger(e)) return e;
  return parseFloat(e.replace(/\,/g, ""));
  //   if (typeof x === "number") return x;
  //   if (typeof x === "string")
  //     return Number(String(x).replace(/[^\d.-]/g, "")) || 0;
  //   return 0;
}
function setLabelAlamat(
  nama_proyek,
  nama_jalan,
  no_kavling,
  no_tipe_rumah,
  tipe_rumah,
  dark = false,
) {
  let text_dark = dark ? "text-dark" : "text-light";
  return `
   <h2 class="${text_dark}"><i class="fa-solid fa-location-pin"></i><b>${nama_proyek}</b></h2>
   <p class="mb-0"> <i class="fas fa-map-marker-alt"></i> ${nama_jalan}, No.${no_kavling}</p>
   <p class="mb-0"> <i class="fas fa-home"></i> Tipe ${tipe_rumah} (${no_tipe_rumah})</p>`;
}

function nama_bulan(e) {
  switch (e) {
    case "01":
      return "Jan";
    case "02":
      return "Feb";
    case "03":
      return "Mar";
    case "04":
      return "Apr";
    case "05":
      return "May";
    case "06":
      return "Jun";
    case "07":
      return "Jul";
    case "08":
      return "Aug";
    case "09":
      return "Sep";
    case "10":
      return "Oct";
    case "11":
      return "Nov";
    case "12":
      return "Dec";
    default:
      return "Jan";
  }
}

function format_datetime(e) {
  if (e) {
    e = e.split(" ");
    e[0] = e[0].split("-");
    e[0] = e[0][2] + " " + nama_bulan(e[0][1]) + " " + e[0][0];
    return e[0] + " " + e[1];
  } else {
    return e;
  }
}

function format_date(e) {
  if (e) {
    e = e.split("-");
    e = e[2] + " " + nama_bulan(e[1]) + " " + e[0];
    return e;
  } else if (e == null) {
    return "";
  } else {
    return e;
  }
}
function initModalListener(id) {
  $(id).on("hide.bs.modal", function (e) {
    // Panggil fungsi kamu di sini
    e.preventDefault();

    Swal.fire({
      title: "Konfirmasi",
      text: "Apakah Anda yakin ingin membatalkan dan menutup form?",
      showDenyButton: true,
      confirmButtonText: "Ya",
      denyButtonText: `Tidak`,
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        // lepas listener agar tidak loop
        removeModalListener(id);
        // tutup modal manual
        $(id).modal("hide");
        state.status.tab.isClosed = true;
        state.id_cashout_subkon = null;

        //
        state.mkdt = {};

        //unload data um & bb
        state.data_um = {};
        state.data_bb = {};
      }
    });
  });
}
function removeModalListener(id) {
  $(id).off("hide.bs.modal");
}
function applyLoadingEffect(selector) {
  $(selector).addClass("input-loading");
}

function removeLoadingEffect(selector) {
  $(selector).removeClass("input-loading");
}
let loaded = [];
let tabId;
$('a[data-toggle="tab"]').on("shown.bs.tab", function (e) {
  const targetId = $(e.target).attr("href"); // ex: #profile
  tabId = targetId;
  if (targetId === "#dtt-summary" && !loaded["sm"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadSummary(dr);
      loaded["sm"] = true;
      removeLoadingEffect(targetId);
    }, 200);
  }
  if (targetId === "#dtt-hj" && !loaded["pl"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadPL(dr.pricelist);
      loadMKDT(dr.mkdt);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["pl"] = true;
  }
  if (targetId === "#dt-stdetail" && !loaded["mkdt"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadMKDT(dr.mkdt);
      loadKavling(dr);
      loaded["mkdt"] = true;
      removeLoadingEffect(targetId);
    }, 200);
  }
  if (targetId === "#dt-cashout" && !loaded["co"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadCashOut(dr.cashout);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["co"] = true;
  }
  if (targetId === "#dt-tagihan" && !loaded["tg"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadTagihan(dr);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["tg"] = true;
  }
  if (targetId === "#dt-legal" && !loaded["lg"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadLegal(dr.legal);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["lg"] = true;
  }
  if (targetId === "#dt-produksi" && !loaded["pr"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadProduksi(dr.produksi, dr.files);
      loadBayarProduksi(dr.bayar_produksi);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["pr"] = true;
  }
  if (targetId === "#dt-pajak" && !loaded["pj"]) {
    loadBuktiBayarPajak(dr);
    loaded["pj"] = true;
  }
  if (targetId === "#log_pembayaran" && !loaded["keu_lp"]) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadLogPembayaran(keu_lp);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["keu_lp"] = true;
  }
  if (
    (targetId === "#tagihan" && !loaded["keu_tg"]) ||
    (targetId === "#bb" && !loaded["keu_tg"])
  ) {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadTableTagihan(keu_tg);
      removeLoadingEffect(targetId);
    }, 200);
    loaded["keu_tg"] = true;
  }
  if (targetId === "#fm-cashout-subkon-status") {
    applyLoadingEffect(targetId);
    setTimeout(() => {
      loadHistoryStatusCashoutSubkon();
      removeLoadingEffect(targetId);
    }, 200);
    loaded["fm_cs_st"] = true;
  }
  if (
    targetId === "#idk_data_konsumen" ||
    targetId === "#idk_biaya" ||
    targetId === "#idk_tagihan"
  ) {
    let btnN = "#add-form-btn-idk_keu";
    let btnPr = "#prev-form-btn-idk_keu";
    // updateButtons(btnN, btnPr)
  }
});
/******************************** modified magicwand js start *********************************/
//untuk set warna shape kavling
function set_fill(t, a, e, d) {
  ((fill = t), (stroke = a), (strokeWidth = e), (dashed = d));
}

function onRadiusChange(t) {
  blurRadius = t.target.value;
}

function showThreshold() {
  document.getElementById("threshold").innerHTML =
    "Threshold: " + currentThreshold;
}
let test, poly, tpoly;

function drawMask(t, a) {
  if (!imageInfo) return;
  showThreshold();
  let e = {
    data: imageInfo.data.data,
    width: imageInfo.width,
    height: imageInfo.height,
    bytes: 4,
  };
  let d = oldMask ? oldMask.data : null;
  ((mask = MagicWand.floodFill(
    e,
    Math.trunc(t),
    Math.trunc(a),
    currentThreshold,
    d,
    !1,
  )),
    mask && (mask = MagicWand.gaussBlurOnlyBorder(mask, blurRadius, d)),
    addMode && batchMask.push(mask),
    drawBorder());
}

function drawBorder(t) {
  mask &&
    (batchMask.length > 0
      ? ((dtt = []),
        drawBorderAct(batchMask[bml_old], t, bml_old),
        (bml_old += 1))
      : ((dtt_first = []), drawBorderAct(mask, t), (bml_old = 0)));
}

function drawBorderEdit(t) {
  let a = editdtt.length;
  poly = new Konva.Line({
    points: t.points,
    stroke: "red",
    strokeWidth: 2,
    dash: [5, 5],
    opacity: 1,
    closed: !0,
    id: "sel",
  });
  let box = poly.getClientRect();
  // console.log(box)
  // console.log(t.points)

  ((tpoly = new Konva.Text({
    // x: t.points[0],
    // y: t.points[t.points.length - 1] + 20,
    x: box.x - 20 + box.width / 2,
    y: box.y - 8 + box.height / 2,
    text: a,
    fontSize: 18,
    fontFamily: "Calibri",
    fill: "red",
    width: 40,
    padding: 0,
    align: "center",
    id: "tsel",
  })),
    maskedGroup.add(poly),
    maskedGroup.add(tpoly),
    masked.add(maskedGroup));
}

function drawBorderAct(t, a, e = null) {
  let d = MagicWand.traceContours(t);
  ((d = MagicWand.simplifyContours(d, simplifyTolerant, simplifyCount)),
    imageInfo.context.clearRect(0, 0, imageInfo.width, imageInfo.height));
  for (let o = 0; o < d.length; o++)
    if (!d[o].inner) {
      let n = d[o].points;
      (dtt.push(n[0].x), dtt.push(n[0].y));
      for (let i = 1; i < n.length; i++) (dtt.push(n[i].x), dtt.push(n[i].y));
    }
  let l = stage.find("#sel"),
    s = l ? l.length : 0,
    r = (poly = new Konva.Line({
      points: dtt,
      fill: "#eee",
      stroke: "red",
      strokeWidth: 2,
      dash: [5, 5],
      opacity: 0.6,
      closed: !0,
      id: "sel",
    })).getClientRect(),
    h = r.x + r.width / 5,
    u = r.y + r.height / 3;
  ((tpoly = new Konva.Text({
    x: h,
    y: u,
    text: s + 1,
    fontSize: 18,
    fontFamily: "Calibri",
    fill: "red",
    width: 40,
    padding: 5,
    align: "center",
    id: "tsel",
  })),
    maskedGroup.add(poly),
    maskedGroup.add(tpoly),
    masked.add(maskedGroup),
    null != e || 0 == s ? batchdtt.push(dtt) : (batchdtt = []));
}

function paint(t, a, e, d = null) {
  let o;
  if ((hapus_seleksi(), null == d)) {
    if (!dtt) return;
    o = new Konva.Line({
      points: dtt,
      fill: "#00D2FF",
      stroke: "black",
      strokeWidth: 1,
      opacity: 0.4,
      closed: !0,
      data: {
        nama_jalan: a,
        no_kavling: e,
      },
      id: "kav" + t,
    });
  } else
    o = new Konva.Line({
      points: JSON.parse("[" + d + "]"),
      fill: "#00D2FF",
      stroke: "black",
      strokeWidth: 1,
      opacity: 0.4,
      closed: !0,
      data: {
        nama_jalan: a,
        no_kavling: e,
      },
      id: "kav" + t,
    });
  (datal.add(o), datal.draw(), (dtt = []));
}

// function drawLine(point) {
//   if(stage.find("#manual_selection")[0])
//     stage.find("#manual_selection")[0].destroy();

//   return new Konva.Line({
//     points: point,
//     stroke: "red",
//     strokeWidth: 2,
//     dash: [5, 5],
//     opacity: 1,
//     closed: !0,
//     id: "manual_selection"
//   });
// }
/******************************** magicwand js end *********************************/

/******************************** getNotif *********************************/

function getNotif() {
  start = 0;
  let notif = "";
  $.ajax({
    type: "get",
    url: base_url + "/getnotif",
    data: {
      [csrfName]: csrfHash,
    },
    dataType: "json",
    beforeSend: function () {
      $("#load-more-notif").prop("disabled", true);
      $("#load-more-notif").html(
        'Memuat <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#notif-here").addClass("blur");
    },
    success: function (r) {
      $("#notif-here").html(" ");
      $("#load-more-notif").prop("disabled", false);
      $("#load-more-notif").html("Perbarui Aktivitas");
      $("#notif-here").removeClass("blur");
      
      // Update Unread Badge
      if (r.unread_count > 0) {
        $("#notif-badge").text(r.unread_count).show();
      } else {
        $("#notif-badge").hide();
      }

      $.each(r.notif, function (i, v) {
        let unread_class = v.is_read == 0 ? 'bg-light-warning' : '';
        notif +=
          `
          <a class="d-flex ${unread_class}" href="javascript:void(0)" onclick="handleNotificationClick(${v.id}, '${v.id_kavling}', '${v.type}')">
              <div class="media d-flex align-items-start">
                  <div class="media-body">
                      <p class="media-heading"><span class="font-weight-bolder">` +
          v.nama_jalan +
          ` No. ` +
          v.no_kavling +
          `</span></p>
                      <p class="media-heading"><b>` +
          v.username +
          `</b>:  ` +
          v.notif +
          `</p>
                      <small class="media-heading"><b>` +
          format_datetime(v.created_at) +
          `</b></small>
                  </div>
              </div>
          </a>`;
      });
      $("#notif-here").append(notif);
    },
  });
}

$("#load-more-notif").click(function () {
  getNotif();
});

// Fungsi untuk memuat data dari server
function loadData() {
  isLoading = true;
  $.ajax({
    url: base_url + "/loadnotif", 
    method: "GET",
    data: {
      [csrfName]: csrfHash,
      offset: start,
    },
    success: function (r) {
      let notif = "";
      $.each(r.notif, function (i, v) {
        let unread_class = v.is_read == 0 ? 'bg-light-warning' : '';
        notif +=
          `
                  <a class="d-flex ${unread_class}" href="javascript:void(0)" onclick="handleNotificationClick(${v.id}, '${v.id_kavling}', '${v.type}')">
                      <div class="media d-flex align-items-start">
                          <div class="media-body">
                              <p class="media-heading"><span class="font-weight-bolder">` +
          v.nama_jalan +
          ` No. ` +
          v.no_kavling +
          `</span></p>
                              <p class="media-heading"><b>` +
          v.username +
          `</b>:  ` +
          v.notif +
          `</p>
                              <small class="media-heading"><b>` +
          format_datetime(v.created_at) +
          `</b></small>
                          </div>
                      </div>
                  </a>`;
      });
      $("#notif-here").append(notif);
      if (r.notif.length > 0) start += offset;
      isLoading = false;
    },
    error: function () {
      isLoading = false;
      console.log("Error loading data");
    },
  });
}

function handleNotificationClick(id_notif, id_kavling, type) {
    // Tandai notifikasi sebagai dibaca
    $.ajax({
        url: base_url + "/notif/mark-as-read/" + id_notif,
        type: "POST",
        data: {
            [csrfName]: csrfHash
        },
        dataType: "json",
        success: function(response) {
            // Update csrf hash
            if(response.token) {
                csrfHash = response.token;
                $('input[name="' + csrfName + '"]').val(csrfHash);
            }
            
            // Refresh list notifikasi (agar badge & warna bg terupdate)
            getNotif();
            
            // Routing aksi berdasarkan tipe notifikasi
            // Pastikan fungsi-fungsi ini tersedia (contoh: di scope global)
            if (type === 'cashout_subkon') {
                if(typeof openCOSubkon === 'function') {
                    // Mungkin perlu fetch data kavling dulu atau langsung buka
                    // Tergantung logika existing di `openCOSubkon`
                    // Kita asumsikan openCOSubkon bisa menerima id_kavling langsung
                    // atau butuh state diset dulu
                    openCOSubkon(id_kavling); 
                } else {
                    console.log('openCOSubkon function is not available.');
                }
            } else if (type === 'tagihan') {
                if(typeof modal_tagihan === 'function') {
                    modal_tagihan(id_kavling); // Atau fungsi sejenis
                }
            } else if (type === 'progress') {
                // panggil form produksi
            } else {
                // Default action jika type null/tidak dikenali:
                // Misalnya buka modal_detail kavling
                if(typeof view_detail === 'function') {
                    view_detail(id_kavling);
                }
            }
        }
    });
}

// Deteksi scroll pada div
$("#notif-here").scroll(function () {
  if (
    $(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight &&
    !isLoading
  ) {
    loadData();
  }
});

function setDatePicker(val, id) {
  let el = null;

  if (typeof id === "string") {
    try {
      el = document.querySelector(id);
    } catch (error) {
      return false;
    }
  } else if (id && id.jquery) {
    el = id.get(0);
  } else {
    el = id;
  }

  const isValidDate = (dateStr) => {
    if (typeof dateStr !== "string" || !/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
      return false;
    }

    const [year, month, day] = dateStr.split("-").map(Number);
    const date = new Date(year, month - 1, day);

    return (
      date.getFullYear() === year &&
      date.getMonth() === month - 1 &&
      date.getDate() === day
    );
  };

  if (!el) {
    return false;
  }

  const fp = el._flatpickr;
  const validDate = val && val !== "0000-00-00" && isValidDate(val);

  if (validDate) {
    if (fp && typeof fp.setDate === "function") {
      fp.setDate(val);
    } else if ("value" in el) {
      el.value = val;
    }
    return true;
  }

  if (fp && typeof fp.clear === "function") {
    fp.clear(); // menghapus nilai flatpickr
  }
  if ("value" in el) {
    el.value = ""; // kosongkan input jika tidak valid
  }

  return false;
}

function swal(
  icon,
  title,
  text = null,
  showConfirmButton = false,
  callback = null,
) {
  Swal.fire({
    icon: icon,
    title: title,
    text: text,
    showConfirmButton: showConfirmButton,
  }).then(function () {
    if (callback) {
      callback();
    }
  });
}

function changeVal(id, val) {
  return $(id).val(val).change().keyup();
}

function simpanBtn(
  id,
  simpan = true,
  tekss = 'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
  tekse = "Simpan",
) {
  $(id).prop("disabled", simpan);
  if (simpan) {
    $(id).html(tekss);
  } else {
    $(id).html(tekse);
  }
}

function setText(id, val, isBadge = false, color = "") {
  val = !isBadge ? val : `<span class="badge badge-${color}">${val}</span>`;
  if (val) {
    $(id).html(val);
  } else {
    $(id).html("-");
  }
}

// script produksi
function tampilkanKoordinatGPS(img, callback) {
  EXIF.getData(img, function () {
    var lat = EXIF.getTag(this, "GPSLatitude");
    var lon = EXIF.getTag(this, "GPSLongitude");
    var latRef = EXIF.getTag(this, "GPSLatitudeRef") || "N";
    var lonRef = EXIF.getTag(this, "GPSLongitudeRef") || "W";

    if (!lat || !lon) {
      callback({
        lat: null,
        lon: null,
      });
      return;
    }
    lat = (lat[0] + lat[1] / 60 + lat[2] / 3600) * (latRef === "N" ? 1 : -1);
    lon = (lon[0] + lon[1] / 60 + lon[2] / 3600) * (lonRef === "W" ? -1 : 1);

    callback({
      lat: lat,
      lon: lon,
    });
  });
}

function createInputForm(
  name = null,
  el = "input",
  ty = "text",
  cl = [],
  dt = null,
) {
  const Input = document.createElement(el);
  Input.type = ty;
  Input.style.position = "relative";
  Input.style.borderRadius = "3px";
  // Input.style.top = "5px";

  Input.style.width = "150px";
  Input.id = name;
  if (el == "select") {
  } else {
    Input.value = dt;
  }
  Input.name = name;
  cl.forEach((v) => Input.classList.add(v));
  return Input;
}

function displayUploadedFiles(input, listId) {
  const listElement = document.getElementById(listId);

  // Hanya menghapus elemen yang belum terupload
  Array.from(listElement.children).forEach((child) => {
    if (child.id.startsWith("uploaded_foto_")) {
      child.remove();
    }
  });

  Array.from(input.files).forEach((file, index) => {
    const imgDiv = document.createElement("div");
    imgDiv.id = `uploaded_foto_${index}`;
    imgDiv.style.position = "relative";
    imgDiv.style.flexWrap = "wrap";
    imgDiv.classList.add("input-foto-container", "mt-1", "mr-1");

    //tampilkan gambar/ tombol link
    if (file.type.startsWith("image/")) {
      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);
      img.style.width = "150px";
      img.style.height = "150px";
      img.style.objectFit = "cover";
      imgDiv.appendChild(img);
    } else {
      const link = document.createElement("a");
      link.href = URL.createObjectURL(file);
      link.innerText = "Lihat File";
      link.target = "_blank";
      link.style.minWidth = "250px";
      link.style.minHeight = "60px";
      link.classList.add("btn", "btn-outline-primary");
      link.style.textAlign = "left";
      imgDiv.appendChild(link);
    }

    //tombol hapus
    const deleteButton = document.createElement("button");
    deleteButton.innerText = "Hapus";
    deleteButton.style.position = "absolute";
    deleteButton.style.top = "5px";
    deleteButton.style.left = "5px";
    deleteButton.style.backgroundColor = "red";
    deleteButton.style.color = "white";
    deleteButton.style.border = "none";
    deleteButton.style.cursor = "pointer";

    //event tombol hapus
    deleteButton.addEventListener("click", function (event) {
      event.preventDefault();
      imgDiv.remove();
    });

    //keterangan
    const keterangan = document.createElement("div");
    keterangan.innerText = "Belum terupload";
    keterangan.style.position = "absolute";
    keterangan.style.bottom = "5px";
    keterangan.style.left = "5px";
    keterangan.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
    keterangan.style.color = "white";
    keterangan.style.padding = "2px 5px";
    keterangan.style.borderRadius = "3px";

    //
    const imgContainer = document.createElement("div");
    imgContainer.classList.add("input-foto");

    //text input tanggal foto
    let te = document.createElement("div");
    te.innerHTML = "<strong>Tanggal Ambil Foto</strong>";
    te.classList.add("ml-1");
    imgContainer.appendChild(te);

    //input tanggal foto
    const tanggalInput = createInputForm(
      "tgl_" + input.name,
      "input",
      "date",
      ["form-control", "flatpickr-human-friendly", "ml-1"],
      new Date().toISOString().split("T")[0],
    );
    imgContainer.appendChild(tanggalInput);

    //text keterangan
    te = document.createElement("div");
    te.innerHTML = "<strong>Keterangan Foto</strong>";
    te.classList.add("ml-1");
    imgContainer.appendChild(te);

    // console.log(input, listId)
    if (listId == "list_prod_foto_konstruksi") {
      const keteranganInput = createInputForm(
        "kategoriPekerjaan_" + input.name,
        "select",
        "select",
        ["form-control", "ml-1", "kategoriPekerjaan"],
        list_pekerjaan,
      );
      imgContainer.appendChild(keteranganInput);

      const keteranganInput2 = createInputForm(
        "tugasPekerjaan_" + input.name,
        "select",
        "select",
        ["form-control", "ml-1"],
      );
      imgContainer.appendChild(keteranganInput2);

      imgDiv.appendChild(keterangan);
      imgDiv.appendChild(deleteButton);
      imgDiv.appendChild(imgContainer);

      listElement.appendChild(imgDiv);

      buatOpsiSelect("kategoriPekerjaan", list_pekerjaan);
      // Event listener untuk mengubah opsi tugas saat kategori berubah
      document
        .getElementById("kategoriPekerjaan_" + input.name)
        .addEventListener("change", (event) => {
          const kategoriTerpilih = event.target.value;
          updateTugasPekerjaan(kategoriTerpilih);
        });
    } else {
      const keteranganInput = createInputForm(
        "ket_" + input.name,
        "input",
        "text",
        ["form-control", "ml-1"],
      );
      imgContainer.appendChild(keteranganInput);

      imgDiv.appendChild(keterangan);
      imgDiv.appendChild(deleteButton);
      imgDiv.appendChild(imgContainer);

      listElement.appendChild(imgDiv);
    }
  });
}

function showFoto(data, imbuhan = "", del = true) {
  let containerElement = "",
    coor = {};

  data.forEach((item) => {
    containerElement = document.getElementById(
      `${imbuhan}list_${item.kategori}`,
    );
    if (containerElement) {
      const imgDiv = document.createElement("div");
      imgDiv.id = `${imbuhan}foto_produksi_${item.id}`;
      imgDiv.style.position = "relative";
      imgDiv.style.height = "auto";
      // imgDiv.style.margin = "5px";
      imgDiv.classList.add("input-foto-container", "mt-1", "mr-1");

      const imgContainer = document.createElement("div");
      imgContainer.classList.add("input-foto");

      if (item.file_name.endsWith(".pdf") || item.file_name.endsWith(".xlsx")) {
        const link = document.createElement("a");
        link.href = item.access_url || file_url('file_produksi', item.id);
        link.innerText = "Lihat File";
        link.target = "_blank";
        link.style.minWidth = "150px";
        link.classList.add("btn", "btn-primary");
        link.style.textAlign = "left";
        imgDiv.appendChild(link);
      } else {
        const img = document.createElement("img");
        img.src = item.thumbnail_url || file_thumbnail_url('file_produksi', item.id);
        img.style.width = "150px";
        img.style.height = "150px";
        img.style.objectFit = "cover";
        img.onload = function () {
          const coorDiv = document.createElement("div");
          coorDiv.position = "absolute";
          coorDiv.left = "160px";

          coorDiv.innerHTML = `
            <strong>Tanggal Pengambilan Foto:</strong><br/>
            ${format_date(item.tgl_capture)}<br/>
            <strong>Diunggah oleh:</strong><br/>
            ${item.username}<br/>
            <strong>Keterangan:</strong><br/>
            ${item.file_keterangan}<br/>
            <strong>Titik koordinat:</strong> <br>-, -
            `;

          imgDiv.appendChild(coorDiv);
          //load image 2 kali jika tanpa cache
          // tampilkanKoordinatGPS(img, function (koordinat) {
          //   coor = koordinat;
          //   coorDiv.innerHTML = `
          //   <strong>Tanggal Pengambilan Foto:</strong><br/>
          //   ${format_date(item.tgl_capture)}<br/>
          //   <strong>Diunggah oleh:</strong><br/>
          //   ${item.username}<br/>
          //   <strong>Keterangan:</strong><br/>
          //   ${item.file_keterangan}<br/>
          //   <strong>Titik koordinat:</strong> <br>${coor.lat}, ${coor.lon}
          //   `;

          //   imgDiv.appendChild(coorDiv);
          // });
        };
        imgContainer.appendChild(img);
      }

      const downloadButton = document.createElement("button");
      downloadButton.innerText = "Download";
      downloadButton.style.position = "absolute";
      downloadButton.style.top = "120px";
      downloadButton.style.left = "35px";
      downloadButton.classList.add("btn", "btn-primary", "btn-sm");
      // downloadButton.style.backgroundColor = "green";
      // downloadButton.style.color = "white";
      downloadButton.style.border = "none";
      downloadButton.style.cursor = "pointer";

      downloadButton.addEventListener("click", function (event) {
        event.preventDefault();
        const link = document.createElement("a");
        link.href = item.download_url || file_url('file_produksi', item.id, true);
        link.innerText = "Lihat File";
        link.target = "_blank";
        link.click();
      });

      // console.log(del)
      if (del == true) {
        const deleteButton = document.createElement("button");
        deleteButton.innerText = "Hapus";
        deleteButton.style.position = "absolute";
        deleteButton.style.top = "5px";
        deleteButton.style.left = "5px";
        deleteButton.style.backgroundColor = "red";
        deleteButton.style.color = "white";
        deleteButton.style.border = "none";
        deleteButton.style.cursor = "pointer";

        deleteButton.addEventListener("click", function (event) {
          event.preventDefault();
          hapusFoto(item.id);
        });

        imgDiv.appendChild(deleteButton);
      }

      imgDiv.appendChild(imgContainer);
      imgDiv.appendChild(downloadButton);

      containerElement.appendChild(imgDiv);
    }
  });
}

function hapusFoto(id) {
  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Anda tidak akan dapat mengembalikan ini!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya, hapus!",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: base_url + "/produksi/hapus_foto",
        type: "post",
        data: {
          [csrfName]: csrfHash,
          id: id,
        },

        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: "success",
              title: "Berhasil",
              text: "Foto berhasil dihapus",
            }).then(() => {
              $(`#foto_produksi_${id}`).remove();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal",
              text: "Foto gagal dihapus",
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "Gagal",
            text: "Terjadi kesalahan saat menghapus foto",
          });
        },
      });
    }
  });
}

$("#pph_jenis_validasi, #dt-pph_jenis_validasi").change(function () {
  if (this.value === "Online") {
    $(".select-pph-validasi-offline").hide();
    $(".select-pph-validasi-online").show();
  } else if (this.value === "Offline") {
    $(".select-pph-validasi-offline").show();
    $(".select-pph-validasi-online").hide();
  }
});

$("#sertifikat_is_balik_nama, #dt-sertifikat_is_balik_nama").change(
  function () {
    if (this.value === "Belum") {
      $(".select-sertifikat_is_balik_nama").hide();
    } else if (this.value === "Sudah") {
      $(".select-sertifikat_is_balik_nama").show();
    }
  },
);

$("#pbb_is_balik_nama, #dt-pbb_is_balik_nama").change(function () {
  if (this.value === "Belum") {
    $(".select-pbb_is_balik_nama").hide();
  } else if (this.value === "Sudah") {
    $(".select-pbb_is_balik_nama").show();
  }
});

$("#pbg_is_revisi, #dt-pbg_is_revisi").change(function () {
  if (this.value === "Tidak") {
    $(".select-pbg_is_revisi").hide();
  } else if (this.value === "Ya") {
    $(".select-pbg_is_revisi").show();
  }
});

$("#sertifikat_is_split, #dt-sertifikat_is_split").change(function () {
  if (this.value === "0") {
    $(".select-sertifikat_is_split").hide();
  } else if (this.value === "1") {
    $(".select-sertifikat_is_split").show();
  }
});

function buatOpsiSelect(selectId, data) {
  const selectElement = document.getElementsByClassName(selectId);

  for (let i = 0; i < selectElement.length; i++) {
    const select = selectElement[i];
    for (const kategori in data) {
      const option = document.createElement("option");
      option.value = kategori;
      option.text = kategori;

      select.appendChild(option);
    }
  }
}

function updateTugasPekerjaan(kategori) {
  const tugasSelect = document.getElementById("tugasPekerjaan");
  tugasSelect.innerHTML = "";

  if (kategori.value in data) {
    const tugasList = data[kategori];
    for (const tugas of tugasList) {
      const option = document.createElement("option");
      option.value = tugas;
      option.text = tugas;
      tugasSelect.appendChild(option);
    }
  }
}
function isNotEmpty(val) {
  if (val === null || val === undefined) return false;
  if (val === "") return false;
  if (Array.isArray(val)) return val.length > 0;
  if (typeof val === "object") return Object.keys(val).length > 0;
  return true;
}
function setImgOrPlaceholder(
  $a,
  src,
  placeholder,
  width = "100%",
  height = "150px",
) {
  if (!isNotEmpty(src)) {
    $a.html("Belum Unggah File");
    $a.prop("href", base_url + placeholder);
  } else {
    $a.html("Klik untuk melihat file");
    $a.prop("href", resolveFileHref(src));
  }
}
function load_dropzone(id) {
  const input = document.getElementById(id);
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
}
function createTable(cols, rows) {
  const table = document.createElement("table");
  table.style.borderCollapse = "collapse";
  table.style.width = "100%";

  const thead = document.createElement("thead");
  const headerRow = document.createElement("tr");

  cols.forEach((col) => {
    const th = document.createElement("th");
    th.textContent = col;
    th.style.border = "1px solid black";
    th.style.padding = "8px";
    headerRow.appendChild(th);
  });

  thead.appendChild(headerRow);
  table.appendChild(thead);

  const tbody = document.createElement("tbody");

  rows.forEach((row) => {
    const tr = document.createElement("tr");
    row.forEach((cell) => {
      const td = document.createElement("td");
      td.style.border = "1px solid black";
      td.style.padding = "8px";

      // Tambahkan logika untuk konten HTML
      if (typeof cell === "string" && cell.includes("<")) {
        td.innerHTML = cell;
      } else {
        td.textContent = cell;
      }

      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });

  table.appendChild(tbody);

  return table;
}

//highligth button
function hlButton(selector) {
  const btn = $(selector);
  btn.addClass("btn-highlight");

  setTimeout(() => {
    btn.removeClass("btn-highlight");
  }, 3000); // durasi harus sama dengan animasi CSS
}

// buatOpsiSelect('kategoriPekerjaan', data);
