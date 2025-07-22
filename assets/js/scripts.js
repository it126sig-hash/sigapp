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
    false
  );

  getNotif();

  
})(window);

function num_format(e) {
  if (e == null) return e;
  if (!isNaN(e)) return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return e;
}
function removeComma(e) {
  if (Number.isInteger(e)) return e;
  return parseInt(e.replace(/\,/g, ""));
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
  } else {
    return e;
  }
}

/******************************** modified magicwand js start *********************************/
//untuk set warna shape kavling
function set_fill(t, a, e, d) {
  (fill = t), (stroke = a), (strokeWidth = e), (dashed = d);
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
  (mask = MagicWand.floodFill(
    e,
    Math.trunc(t),
    Math.trunc(a),
    currentThreshold,
    d,
    !1
  )),
    mask && (mask = MagicWand.gaussBlurOnlyBorder(mask, blurRadius, d)),
    addMode && batchMask.push(mask),
    drawBorder();
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

  (tpoly = new Konva.Text({
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
    masked.add(maskedGroup);
}

function drawBorderAct(t, a, e = null) {
  let d = MagicWand.traceContours(t);
  (d = MagicWand.simplifyContours(d, simplifyTolerant, simplifyCount)),
    imageInfo.context.clearRect(0, 0, imageInfo.width, imageInfo.height);
  for (let o = 0; o < d.length; o++)
    if (!d[o].inner) {
      let n = d[o].points;
      dtt.push(n[0].x), dtt.push(n[0].y);
      for (let i = 1; i < n.length; i++) dtt.push(n[i].x), dtt.push(n[i].y);
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
  (tpoly = new Konva.Text({
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
    null != e || 0 == s ? batchdtt.push(dtt) : (batchdtt = []);
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
  datal.add(o), datal.draw(), (dtt = []);
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
      // offset: loadmore,
    },
    dataType: "json",
    beforeSend: function () {
      $("#load-more-notif").prop("disabled", true);
      $("#load-more-notif").html(
        'Memuat <i class="fa fa-spinner fa-spin"></i>'
      );
      $("#notif-here").addClass("blur");
    },
    success: function (r) {
      $("#notif-here").html(" ");
      // if(r.notif.length > 0 <5)
      //   loadmore += parseInt(r.notif.length)
      // else
      //   loadmore += 5
      // console.log(r)
      $("#load-more-notif").prop("disabled", false);
      $("#load-more-notif").html("Perbarui Aktivitas");
      $("#notif-here").removeClass("blur");
      $.each(r.notif, function (i, v) {
        notif +=
          `
          <a class="d-flex" href="javascript:void(0)">
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
    url: base_url + "/loadnotif", // Gantilah dengan path menuju file yang menyediakan data
    method: "GET",
    data: {
      [csrfName]: csrfHash,
      offset: start,
    },
    success: function (r) {
      let notif = "";
      $.each(r.notif, function (i, v) {
        notif +=
          `
                  <a class="d-flex" href="javascript:void(0)">
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
  if (val != "0000-00-00")
    return document.querySelector(id)._flatpickr.setDate(val);
}

function swal(
  icon,
  title,
  text = null,
  showConfirmButton = false,
  callback = null
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



function simpanBtn(id, simpan = true, tekss = 'Menyimpan <i class="fa fa-spinner fa-spin"></i>', tekse = 'Simpan') {
  $(id).prop("disabled", simpan);
  if (simpan) {
    $(id).html(tekss);
  }else{
    $(id).html(tekse);
  }
}

function setText(id, val){
  if(val){
    $(id).text(val)
  }else{
    $(id).text("-")
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

function createInputForm(name = null, el = 'input', ty = 'text', cl = []) {
  const Input = document.createElement(el);
  Input.type = ty;
  Input.style.position = "relative";
  Input.style.borderRadius = "3px";
  // Input.style.top = "5px";

  Input.style.width = "150px";
  Input.id = name;
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
    imgDiv.classList.add('input-foto-container', 'mt-1', 'mr-1');


    if (file.type.startsWith('image/')) {
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
      imgDiv.remove();
    });

    const keterangan = document.createElement("div")
    keterangan.innerText = "Belum terupload";
    keterangan.style.position = "absolute";
    keterangan.style.bottom = "5px";
    keterangan.style.left = "5px";
    keterangan.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
    keterangan.style.color = "white";
    keterangan.style.padding = "2px 5px";
    keterangan.style.borderRadius = "3px";


    const imgContainer = document.createElement('div')
    imgContainer.classList.add('input-foto')

    let te = document.createElement("div")
    te.innerHTML = "<strong>Tanggal Ambil Foto</strong>";
    te.classList.add('ml-1')
    imgContainer.appendChild(te);

    const tanggalInput = createInputForm('tgl_' + input.name, 'input', 'date', ['form-control', 'flatpickr-human-friendly', 'ml-1'])
    imgContainer.appendChild(tanggalInput);

    te = document.createElement("div")
    te.innerHTML = "<strong>Keterangan Foto</strong>";
    te.classList.add('ml-1')
    imgContainer.appendChild(te);

    const keteranganInput = createInputForm('ket_' + input.name, 'input', 'text', ['form-control', 'ml-1'])
    imgContainer.appendChild(keteranganInput);


    imgDiv.appendChild(keterangan);
    imgDiv.appendChild(deleteButton);
    imgDiv.appendChild(imgContainer);

    listElement.appendChild(imgDiv);
  });
}

function showFoto(data, imbuhan = '', del = true) {
  let containerElement = "",
    coor = {};

  data.forEach((item) => {
    containerElement = document.getElementById(`${imbuhan}list_${item.kategori}`);
    if (containerElement) {
      const imgDiv = document.createElement("div");
      imgDiv.id = `${imbuhan}foto_produksi_${item.id}`;
      imgDiv.style.position = "relative";
      imgDiv.style.height = "auto";
      // imgDiv.style.margin = "5px";
      imgDiv.classList.add('input-foto-container', 'mt-1', 'mr-1');

      const imgContainer = document.createElement('div')
      imgContainer.classList.add('input-foto')

      if (item.file_name.endsWith(".pdf") || item.file_name.endsWith(".xlsx")) {
        const link = document.createElement("a");
        link.href = `${base_url}/${item.lokasi}/${item.file_name}`;
        link.innerText = "Lihat File";
        link.target = "_blank";
        link.style.minWidth = "150px";
        link.classList.add("btn", "btn-primary");
        link.style.textAlign = "left";
        imgDiv.appendChild(link);
      } else {
        const img = document.createElement("img");
        img.src = `${base_url}${item.lokasi}thumbnails/${item.file_name}`;
        img.style.width = "150px";
        img.style.height = "150px";
        img.style.objectFit = "cover";
        img.onload = function () {
          const coorDiv = document.createElement("div");
          coorDiv.position = 'absolute';
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
      downloadButton.classList.add('btn', 'btn-primary', 'btn-sm');
      // downloadButton.style.backgroundColor = "green";
      // downloadButton.style.color = "white";
      downloadButton.style.border = "none";
      downloadButton.style.cursor = "pointer";

      downloadButton.addEventListener("click", function (event) {
        event.preventDefault();
        const link = document.createElement("a");
        link.href = `${base_url}${item.lokasi}${item.file_name}`;
        link.innerText = "Lihat File";
        link.target = "_blank";
        link.click()
      });

      console.log(del)
      if(del == true){
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