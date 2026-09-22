    function tambah_kavling(from_pilih_seleksi = false) {
        if (editdtt.length > 0) {
            return swal('error', "Terjadi Kesalahan.", "Lokasi sudah diisi oleh kavling lain")
        }

        if (dtt.length === 0 && !from_pilih_seleksi) {
            $("#btn_pilih_seleksi, #btn_batal_seleksi, #container_tambah_jalan, #planning_undo_manual_selection").removeClass('d-none').show();
            $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").hide();
            return;
        }

        if (dtt.length === 0 && from_pilih_seleksi) {
            return swal('warning', 'Peringatan', 'Harus ada seleksi di canvas!');
        }

        $("#fm-add_kavling")

        let shape
        if ($("#tambah_jalan").prop("checked")) {
            shape = dtt
            batchdtt[0] = dtt

            if (dtt.length < 6) {
                return swal('error', "Seleksi manual minimal 3 titik")
            }
        } else {
            shape = stage.find('#sel')[0]
            if (typeof shape === 'undefined') {
                return swal('error', 'Terjadi Kesalahan', 'Seleksi kavling kosong terlebih dahulu')
            }
        }
        $("#fm-add_kavling")[0].reset()
        $("#fm-add_kavling .select2").val(null).trigger('change')
        $("#rotation").val("");
        $("#ui-rotation").val("");
        $("#rotation-icon").css("transform", "rotate(0deg)");


        $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')
        $("#pindah_lokasi_btn").hide()
        act = "add";

        $(".t_luas_legal, .t_luas_produksi, .r_progres").html('-')
        $("#pindah_lokasi_btn").hide()
        act = "add";


        $('#status_tanah').val("Standar").trigger('change');

        if (batchdtt && batchdtt.length > 0 && typeof PolygonClip !== 'undefined' && PolygonClip.computeMABR) {
            interactiveFacadeArrow(batchdtt[0], function(selectedAngle) {
                if (selectedAngle !== null) {
                    $("#rotation").val(selectedAngle.toFixed(1));
                    $("#ui-rotation").val(selectedAngle.toFixed(1));
                    $("#rotation-icon").css("transform", "rotate(" + selectedAngle.toFixed(1) + "deg)");
                }
                $('#modals-slide-in').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#fm-add_kavling #planning_points").val(dtt);
            });
        } else {
            $('#modals-slide-in').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#fm-add_kavling #planning_points").val(dtt);
        }
    }

    function batal_tambah_kavling() {
        $("#btn_pilih_seleksi, #btn_batal_seleksi, #container_tambah_jalan, #planning_undo_manual_selection").hide();
        $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").show();
        
        if ($("#tambah_jalan").prop("checked")) {
            $("#tambah_jalan").prop("checked", false).trigger('change');
        }
    }

    /**************************** planning ***************************** */
var act;
//masking kavling on dbl click
if (typeof siteplan !== "undefined") {
  siteplan.on("dblclick dbltap", function (e) {
    if (typeof isManualSelectionActive === "function" ? isManualSelectionActive() : $("#tambah_jalan").prop("checked")) return;

    let va = $("#pilih-divisi option:selected").val();

    //planning only
    // if (va != 6 roleid) {
    //     Swal.fire({
    //         //position: 'bottom-end',
    //         icon: 'error',
    //         title: "Ubah list data ke pilihan planning",
    //         showConfirmButton: false,
    //         timer: 1500
    //     })
    //     return;
    // }
    dtt = [];

    e = e.evt;

    allowDraw = true;
    addMode = e.ctrlKey;

    downPoint = stage.getPointerPosition();

    if (!addMode) hapus_seleksi();

    let a = stage.getAbsoluteTransform().copy();
    a.invert();
    let l = a.point(downPoint);

    let xy = {
      x: parseInt(l.x, 10),
      y: parseInt(l.y, 10),
    };

    drawMask(xy.x, xy.y);
  });
}

//open modal untuk tambah kavling

// $("#add_kavling").click(function() {});
function planning_split_semicolon(value) {
  return String(value || "")
    .split(";")
    .map((item) => item.trim())
    .filter((item) => item !== "");
}

function planning_normalize_points(value) {
  if (Array.isArray(value)) return value.join(",");
  return String(value || "").trim();
}

function planning_join_semicolon(values) {
  const filtered = values.map(planning_normalize_points).filter((item) => item !== "");
  return filtered.length ? filtered.join(";") + ";" : "";
}

function planning_collect_selection_points() {
  const points = [];

  for (let z = 0; z < batchdtt.length; z++) {
    const point = planning_normalize_points(batchdtt[z]);
    if (point !== "") points.push(point);
  }

  const singlePoint = planning_normalize_points(dtt);
  if (!points.length && singlePoint !== "") points.push(singlePoint);

  return points;
}

function planning_is_kavling_selection() {
  if (!editdtt.length) return false;

  return editdtt.every(function (item) {
    return item && item.data && item.data.tipe == "kavling";
  });
}

function edit_kavling_batch() {
  if (editdtt.length == 0) return;
  $("#pindah_lokasi_btn").show();

  $(".t_luas_legal, .t_luas_produksi, .r_progres").html("-");

  let data, tipe;

  let url = base_url + "/siteplan/get_others";
  tipe = editdtt[0].data.tipe;
  data = editdtt[0].id.substr(6);

  if (tipe == "kavling") {
    data = [];
    url = base_url + "/siteplan/get_kavling_by_multiple_id";
    for (let a = 0; a < editdtt.length; a++) {
      data.push(editdtt[a].id.substr(3));
      tipe = editdtt[a].data.tipe;
    }
  }

  $("#fm-add_kavling")[0].reset();
  $(".select2").not("#pilih-divisi").val(null).trigger("change");
  $("#rotation").val("");
  $("#ui-rotation").val("");
  $("#rotation-icon").css("transform", "rotate(0deg)");

  $.ajax({
    url: url,
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
      
      let form_jenis = tipe;
      if (tipe === "kavling" && editdtt[0].data && editdtt[0].data.jenis) {
          form_jenis = editdtt[0].data.jenis;
      }
      $("#fm-add_kavling #id_jenis").val(form_jenis).change();

      let r = res.data,
        id_kavling = "",
        id_cluster,
        id_jalan,
        id_tipe,
        no = "",
        points = "";

      $("#fm-add_kavling #id_cluster")
        .append(
          $("<option selected></option>")
            .attr("value", r[0].id_cluster)
            .text(r[0].nama_cluster),
        )
        .trigger("change");
      $("#fm-add_kavling #id_jalan")
        .append(
          $("<option selected></option>")
            .attr("value", r[0].id_jalan)
            .text(r[0].nama_jalan),
        )
        .trigger("change");
      $("#fm-add_kavling #id_tipe")
        .append(
          $("<option selected></option>")
            .attr("value", r[0].id_tipe)
            .text(r[0].no_tipe_rumah + " (" + r[0].tipe_rumah + ")"),
        )
        .trigger("change");

      if (tipe == "kavling") {
        if (r.length > 0) {
          for (let a = 0; a < r.length; a++) {
            id_kavling += r[a].id_kavling + ";";
            no += r[a].no_kavling + ";";
            id_cluster = r[a].id_cluster;
            id_jalan = r[a].id_jalan;
            id_tipe = r[a].id_tipe;
            points += r[a].points + ";";
          }
          $("#status_tanah").val(r[0].status_tanah).change();
          $(".id_kavling").val(id_kavling);
          $("#no_kavling").val(no);
          $("#fm-add_kavling #planning_points").val(points);
          $("#f_luas").val(r[0].luas_tanah);
          
          let rotVal = r[0].rotation;
          if (rotVal !== null && rotVal !== undefined && rotVal !== '') {
              $("#rotation").val(rotVal);
              $("#ui-rotation").val(rotVal);
              $("#rotation-icon").css("transform", "rotate(" + rotVal + "deg)");
          }
        }
      } else {
        if (r.length > 0) {
          $(".id_kavling").val(r[0].id);
          $("#fm-add_kavling #planning_points").val(r[0].points);
          $("#f_luas").val(r[0].planning_luas);
          $("#f_nama").val(r[0].nama);
          $("#f_planning_keterangan").val(r[0].planning_keterangan);

          let d = r[0];

          if (d.produksi_luas)
            $(".t_luas_produksi").html(
              d.produksi_luas +
                "  m&sup2  (" +
                d.produksi_edit +
                ": " +
                format_datetime(d.produksi_updated_at) +
                ")",
            );
          if (d.legal_luas)
            $(".t_luas_legal").html(
              d.legal_luas +
                "  m&sup2  (" +
                d.legal_edit +
                ": " +
                format_datetime(d.legal_updated_at) +
                ")",
            );
        }
      }

      $("#modals-slide-in").modal({
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
        timer: 1500,
      });
      return;
    },
  });

  $("#loading").addClass("hidden");
  act = "edit";
}

function open_planning(sh, role, id_kavling) {
  $("#fm-add_kavling")[0].reset();
  $(".id_kavling").val(id_kavling);
  $(".t_luas_legal, .t_luas_produksi, .r_progres").html("-");

  $.ajax({
    url: base_url + "/siteplan/get_kavling_by_id",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      let r = res.data;
      if (r) {
        for (let i in r) {
          $("#fm-add_kavling #" + i).val(r[i]);
        }
        // $('.id_cluster').append($("<option selected></option>").attr("value",r['id_cluster']).text(r['nama_cluster']));
        // var id_cluster = new Option(r.nama_cluster, r.id_cluster, false, false);
        // $('.id_cluster').append(newOption).trigger('change');
        // $('.id_cluster').trigger({
        //     type: 'select2:select',
        //     params: {
        //         data: r
        //     }
        // });

        $("#modals-slide-in-edit").modal({
          backdrop: "static",
          keyboard: false,
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan saat memuat data",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    },
  });
  $("#loading").addClass("hidden");
}

function edit_kavling() {
  if (!editdtt.length || !editdtt[0] || !editdtt[0].data) {
    return Swal.fire({
      icon: "error",
      title: "Pilih data yang akan diubah terlebih dahulu",
      showConfirmButton: false,
      timer: 1500,
    });
  }

  let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
    no_kavlen =
      no_kav[no_kav.length - 1] == "" ? no_kav.length - 1 : no_kav.length,
    points_len = planning_split_semicolon($("#fm-add_kavling #planning_points").val()).length,
    tipe = editdtt[0].data.tipe,
    url = base_url + "/siteplan/edit_others";

  //jika no kavling dan selection tidak sesuai
  if (tipe == "kavling") {
    if (editdtt.length > 0) {
      if (editdtt.length != no_kavlen || editdtt.length != points_len) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: "Terjadi Kesalahan.",
          text:
            "Jumlah Kavling yang dipilih: " +
            editdtt.length +
            "\n" +
            "Jumlah No Kavling yang diisi: " +
            no_kavlen +
            "\n" +
            "Jumlah Lokasi yang dipilih: " +
            points_len,
          showConfirmButton: false,
        });
        return;
      }
    }
    url = base_url + "/siteplan/edit_kavling";
  }

  $.ajax({
    url: url,
    type: "post",
    data: $("#fm-add_kavling").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#add-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#add-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        $("#tambah_jalan").prop("checked", false).trigger('change');
        planning_forget_hidden_move_nodes();
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-slide-in").modal("hide");
          $("#add-form-btn").html("Simpan");
          $("#add-form-btn").removeClass("disabled");
        });
        load_kavling();
        hapus_seleksi();
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#add-form-btn").html("Simpan");
          $("#add-form-btn").removeClass("disabled");
        });
      }
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: false,
        timer: 1500,
      }).then(function () {
        $("#add-form-btn").html("Simpan");
        $("#add-form-btn").removeClass("disabled");
      });
    },
  });
}

//proses tambah kavling ke db
function add_kavling() {
  if (act == "edit") return edit_kavling();

  if ($("#fm-add_kavling #id_jenis").val() == "") {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Jenis harus diisi",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (!$("#fm-add_kavling #id_cluster").val()) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "Cluster harus diisi",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }
  if (!$("#fm-add_kavling #id_jalan").val()) {
    Swal.fire({
      //position: 'bottom-end',
      icon: "error",
      title: "jalan harus diisi",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  $(".form-control").removeClass("is-invalid").removeClass("is-valid");
  let par = "";

  for (let z = 0; z < batchdtt.length; z++) {
    par += "&bpoints[]=" + batchdtt[z];
  }

  //jika no kavling terakhir kosong
  let no_kav = $("#fm-add_kavling #no_kavling").val().split(";"),
    no_kavlen =
      no_kav[no_kav.length - 1] == "" ? no_kav.length - 1 : no_kav.length;

  if ($("#fm-add_kavling #id_jenis").val() == "kavling" || $("#fm-add_kavling #id_jenis").val() == "ruko") {
    //jika no kavling dan selection tidak sesuai
    if (batchdtt.length != no_kavlen) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi Kesalahan.",
        text:
          "Jumlah Kavling yang dipilih: " +
          batchdtt.length +
          "\n" +
          "Jumlah No Kavling yang diisi: " +
          no_kavlen,
        showConfirmButton: false,
      });
      return;
    }
  }
  par += `&nama_jalan=${$("#fm-add_kavling #id_jalan").text()}&nama_tipe=${$("#fm-add_kavling #id_tipe").text()}`;

  $.ajax({
    url: base_url + "/siteplan/add_kavling",
    type: "post",
    data:
      $("#fm-add_kavling").serialize() + par + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#add-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#add-form-btn").addClass("disabled");
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
          $("#modals-slide-in").modal("hide");
          $("#tambah_jalan").prop("checked", false).trigger('change');
          load_kavling();
          hapus_seleksi();
        });
      } else {
        // $('#modals-slide-in').modal('hide');
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        });
      }
      $("#add-form-btn").html("Simpan");
      $("#add-form-btn").removeClass("disabled");
    },
    error: function () {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title:
          "Terjadi Kesalahan saat melakukan penambahan data kaving, jalan atau fasos",
        showConfirmButton: false,
        timer: 1500,
      });
      $("#add-form-btn").html("Simpan");
      $("#add-form-btn").removeClass("disabled");
    },
  });
}

var editdtt_tmp = [];
var planningMoveState = {
  active: false,
  selected: [],
  previousPoints: "",
};
var planningHiddenMoveNodes = [];

function planning_redraw_move_nodes(nodes) {
  const layers = [];

  nodes.forEach(function (node) {
    const nodeLayer = node.getLayer();
    if (nodeLayer && layers.indexOf(nodeLayer) === -1) layers.push(nodeLayer);
  });

  layers.forEach(function (nodeLayer) {
    nodeLayer.batchDraw();
  });
}

function planning_find_move_node(item) {
  if (!item || !item.id || typeof siteplan === "undefined") return null;

  if (typeof siteplan.findOne === "function") {
    return siteplan.findOne("#" + item.id);
  }

  const nodes = siteplan.find("#" + item.id);
  return nodes.length ? nodes[0] : null;
}

function planning_hide_move_nodes(selectedItems) {
  planningHiddenMoveNodes = selectedItems
    .map(planning_find_move_node)
    .filter(function (node, index, nodes) {
      return node && nodes.indexOf(node) === index;
    });

  planningHiddenMoveNodes.forEach(function (node) {
    node.hide();
  });
  planning_redraw_move_nodes(planningHiddenMoveNodes);
}

function planning_restore_hidden_move_nodes() {
  planningHiddenMoveNodes.forEach(function (node) {
    node.show();
  });
  planning_redraw_move_nodes(planningHiddenMoveNodes);
  planningHiddenMoveNodes = [];
}

function planning_forget_hidden_move_nodes() {
  planningHiddenMoveNodes = [];
}

function planning_reset_move_state() {
  planningMoveState = {
    active: false,
    selected: [],
    previousPoints: "",
  };
}

function pindah_kavling() {
  if (!editdtt.length) {
    return Swal.fire({
      icon: "error",
      title: "Pilih objek terlebih dahulu",
      showConfirmButton: false,
      timer: 1500,
    });
  }

  editdtt_tmp = editdtt.slice();
  planningMoveState = {
    active: true,
    selected: editdtt.slice(),
    previousPoints: $("#fm-add_kavling #planning_points").val(),
  };
  planning_hide_move_nodes(planningMoveState.selected);

  $("#modals-slide-in").modal("hide");
  $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").hide();
  $("#selesai_pindah_btn, #batal_pindah_btn, #planning_undo_manual_selection, #container_tambah_jalan").removeClass('d-none').show();
  hapus_seleksi();
  $("#tambah_jalan").prop("checked", true).trigger('change');
}

function selesai_selection(e) {
  let destinationPoints = [];
  const wasMoveActive = planningMoveState.active;
  const selectedForEdit = wasMoveActive ? planningMoveState.selected.slice() : editdtt_tmp.slice();
  if (e == 1) {
    destinationPoints = planning_collect_selection_points();
    const expectedCount = planningMoveState.active ? planningMoveState.selected.length : 1;

    if (!destinationPoints.length) {
      Swal.fire({
        icon: "error",
        title: "Tidak ada lokasi yang dipilih",
        showConfirmButton: false,
        timer: 1500,
      });
      return;
    }

    if (destinationPoints.length != expectedCount) {
      Swal.fire({
        icon: "error",
        title: "Jumlah lokasi tidak sesuai",
        text:
          "Jumlah Kavling yang dipindah: " +
          expectedCount +
          "\n" +
          "Jumlah Lokasi yang dipilih: " +
          destinationPoints.length,
        showConfirmButton: false,
      });
      return;
    }

    $("#fm-add_kavling #planning_points").val(planning_join_semicolon(destinationPoints));
  } else if (wasMoveActive) {
    $("#fm-add_kavling #planning_points").val(planningMoveState.previousPoints);
    planning_restore_hidden_move_nodes();
  }

  let openModal = function() {
    $("#modals-slide-in").modal("show");
    $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").show();
    $("#selesai_pindah_btn, #batal_pindah_btn, #planning_undo_manual_selection, #container_tambah_jalan").hide();
  };

  let completeSelection = function(keepPreview) {
    if (!keepPreview) {
      $("#tambah_jalan").prop("checked", false).trigger('change');
    }
    editdtt = selectedForEdit;
    planning_reset_move_state();
    openModal();
  };

  if (!wasMoveActive && selectedForEdit.length === 0) {
    $("#fm-add_kavling")[0].reset();
    $(".select2").not("#pilih-divisi").val(null).trigger("change");
    $("#rotation").val("");
    $("#ui-rotation").val("");
    $("#rotation-icon").css("transform", "rotate(0deg)");
  }
  if (e == 1 && typeof interactiveFacadeArrow !== 'undefined') {
    let ptsArr = typeof destinationPoints !== 'undefined' && destinationPoints.length > 0 ? destinationPoints[0] : (typeof dtt !== 'undefined' ? dtt : []);
    interactiveFacadeArrow(ptsArr, function(selectedAngle) {
      if (selectedAngle !== null) {
          $("#rotation").val(selectedAngle.toFixed(1));
          $("#ui-rotation").val(selectedAngle.toFixed(1));
          $("#rotation-icon").css("transform", "rotate(" + selectedAngle.toFixed(1) + "deg)");
      }
      completeSelection(true);
    });
  } else {
    completeSelection(false);
  }
}

$(document).on("click", "#modals-slide-in [data-planning-cancel-edit]", function () {
  if (planningMoveState.active) {
    $("#fm-add_kavling #planning_points").val(planningMoveState.previousPoints);
  }
  $("#tambah_jalan").prop("checked", false).trigger('change');
  planning_restore_hidden_move_nodes();
  planning_reset_move_state();
});

(function () {
  const $planningForm = $("#fm-add_kavling");
  if (!$planningForm.length) return;

  const $planningModal = $("#modals-slide-in");
  const $planningCluster = $planningForm.find("#id_cluster");
  const $planningJalan = $planningForm.find("#id_jalan");
  const $planningTipe = $planningForm.find("#id_tipe");
  const $planningJenis = $planningForm.find("#id_jenis");
  const getPlanningProyekId = () => $planningForm.find("[name=id_proyek]").val() || "";

  //select2 cluster
  $planningCluster.select2({
    dropdownParent: $planningModal,
    placeholder: "Pilih Cluster",
    allowClear: true,
    ajax: {
      url: base_url + "/cluster/getAll",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          id_proyek: getPlanningProyekId(),
        };
      },
      processResults: function (r) {
        csrfHash = r.token;

        let results = [];
        $.each(r.data, function (index, item) {
          results.push({
            id: item[0],
            text: item[3],
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
  $planningCluster.on("change", function (e) {
    $planningJalan.val(null).trigger("change");
    if (this.value) $planningJalan.prop("disabled", false);
    else $planningJalan.prop("disabled", true);
  });

  //select jalan
  $planningJalan.select2({
    dropdownParent: $planningModal,
    placeholder: "Pilih Blok",
    allowClear: true,
    ajax: {
      url: base_url + "/jalan/getAll",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          id_cluster: $planningCluster.val(),
          id_proyek: getPlanningProyekId(),
        };
      },
      processResults: function (r) {
        csrfHash = r.token;

        let results = [];
        $.each(r.data, function (index, item) {
          results.push({
            id: item[0],
            text: item[3],
          });
        });

        return {
          results: results,
        };
      },
      cache: true,
    },
  });

  $planningTipe.select2({
    dropdownParent: $planningModal,
    placeholder: "Pilih Tipe",
    allowClear: true,
    ajax: {
      url: base_url + "/tipe/getAll",
      dataType: "json",
      delay: 250,
      method: "post",
      data: function (params) {
        return {
          [csrfName]: csrfHash,
          search: params.term,
          id_proyek: getPlanningProyekId(),
        };
      },
      processResults: function (r) {
        csrfHash = r.token;

        let results = [];
        $.each(r.data, function (index, item) {
          results.push({
            id: item[0],
            text: item[2] + "(" + item[3] + ")",
          });
        });

        return {
          results: results,
        };
      },
      cache: true,
    },
  });

  $planningForm.find("#status_tanah").select2({
    dropdownParent: $planningModal,
  });

  $planningJenis.select2({
    dropdownParent: $planningModal,
  });
  $planningJenis.change(function () {
  if (this.value == "") {
    $(".h").hide();
  } else if (this.value == "kavling" || this.value == "ruko") {
    $(".h").hide();
    $("#div_kavling").show();
  } else if (this.value == "jalan") {
    $(".h").hide();
    $("#div_jalan, #div_luas").show();
  } else if (this.value == "fasos" || this.value == "rth") {
    $(".h").hide();
    $("#div_jalan, #div_fasos").show();
  } else {
    $(".h").hide();
  }
  });
})();

/**************************** planning ***************************** */

document.addEventListener("DOMContentLoaded", function() {
    const planningForm = document.getElementById("fm-add_kavling");
    if (!planningForm) return;

    const namaProyek = planningForm.querySelector("#nama_proyek");
    const idProyek = planningForm.querySelector("[name=id_proyek]");
    if (namaProyek) namaProyek.value = pl_nama_proyek;
    if (idProyek) idProyek.value = pl_id_proyek;
});


var isFacadeArrowActive = false;

$(document).ready(function() {

    // 4. Klik luar shape otomatis deselect
    if (typeof stage !== 'undefined') {
        stage.on('click.planningDeselect tap.planningDeselect', function(e) {
            if (typeof isFacadeArrowActive !== 'undefined' && isFacadeArrowActive) return;
            if (typeof isManualSelectionActive === 'function' && isManualSelectionActive()) return;
            if ($("#tambah_jalan").prop("checked")) return; 
            
            let targetData = e.target.attrs && e.target.attrs.data;
            let isShape = targetData && targetData.tipe;
            let isSubShape = e.target.hasName && e.target.hasName('subShape');
            
            if (!isShape && !isSubShape && typeof editdtt !== 'undefined' && editdtt.length > 0) {
                if (typeof hapus_seleksi === 'function') {
                    hapus_seleksi();
                    $("#add_kavling, #edit_kavling_batch").show();
                    if (typeof layer !== 'undefined') layer.batchDraw();
                }
            }
        });
    }
    let originalBatchDtt = [];
    let originalEditDtt = [];
    
        $('#modals-slide-in').on('shown.bs.modal', function () {
        originalBatchDtt = JSON.parse(JSON.stringify(typeof batchdtt !== 'undefined' ? batchdtt : []));
        originalEditDtt = JSON.parse(JSON.stringify(typeof editdtt !== 'undefined' ? editdtt : []));
        
        let editPoints = null;
        if (originalEditDtt.length > 0 && originalEditDtt[0].points) {
            editPoints = originalEditDtt[0].points;
        }

        let firstPts = originalBatchDtt.length > 0 ? originalBatchDtt[0] : editPoints;
        
        let isEditMode = (originalEditDtt && originalEditDtt.length > 0);
        let hasManyPoints = false;
        if (firstPts) {
            let len = typeof firstPts === 'string' ? firstPts.split(',').length : firstPts.length;
            if (len >= 6) hasManyPoints = true;
        }

        // Tampilkan container jika banyak titik (magic wand) ATAU sedang mode edit
        if (hasManyPoints || isEditMode) {
            $("#simplify-rect-container").show();
            $("#btn-simplify-rect").text("Sederhanakan ke Rect");
            $("#btn-simplify-rect").removeClass("btn-warning").addClass("btn-outline-primary");
            
            // Cek apakah ada value rotation dari database (Edit Mode)
            if (isEditMode && originalEditDtt[0] && originalEditDtt[0].data && originalEditDtt[0].data.rotation !== null && originalEditDtt[0].data.rotation !== undefined) {
                // Jika belum diset oleh arrow, set dari DB
                if (!$("#rotation").val()) {
                    $("#rotation").val(originalEditDtt[0].data.rotation);
                }
            }
            
            let existingRot = $("#rotation").val();
            if (existingRot !== "" && existingRot !== null && existingRot !== undefined) {
                $("#ui-rotation").val(existingRot);
                $("#rotation-icon").css("transform", "rotate(" + existingRot + "deg)");
            } else {
                $("#ui-rotation").val("");
                $("#rotation-icon").css("transform", "rotate(0deg)");
            }
        } else {
            $("#simplify-rect-container").hide();
        }
    });

    $("#ui-rotation").on("input", function() {
        let val = $(this).val();
        $("#rotation").val(val);
        if (val !== "") {
            $("#rotation-icon").css("transform", "rotate(" + val + "deg)");
        } else {
            $("#rotation-icon").css("transform", "rotate(0deg)");
        }
    });

    $("#btn-ubah-fasad").on("click", function() {
        $('#modals-slide-in').modal('hide');
        let targetPoints = (typeof batchdtt !== 'undefined' && batchdtt.length > 0) ? batchdtt[0] : (typeof editdtt !== 'undefined' && editdtt.length > 0 ? editdtt[0].points : (typeof dtt !== 'undefined' ? dtt : []));
        
        interactiveFacadeArrow(targetPoints, function(selectedAngle) {
            if (selectedAngle !== null) {
                $("#rotation").val(selectedAngle.toFixed(1));
                $("#ui-rotation").val(selectedAngle.toFixed(1));
                $("#rotation-icon").css("transform", "rotate(" + selectedAngle.toFixed(1) + "deg)");
            }
            $('#modals-slide-in').modal('show');
        });
    });

    $("#btn-simplify-rect").on("click", function() {
        let isEditMode = (typeof batchdtt !== 'undefined' && batchdtt.length === 0 && typeof editdtt !== 'undefined' && editdtt.length > 0);
        
        if ($(this).text() === "Sederhanakan ke Rect") {
            if (typeof PolygonClip !== 'undefined' && PolygonClip.computeMABR) {
                let targetArray = isEditMode ? editdtt : batchdtt;
                let allPointsStr = [];
                
                for (let i = 0; i < targetArray.length; i++) {
                    const currentItem = targetArray[i];
                    const currentPoints = isEditMode ? currentItem.points : (typeof currentItem === 'string' ? currentItem.split(',').map(Number) : currentItem);
                    if (currentPoints.length < 6) {
                        allPointsStr.push(isEditMode ? currentItem.points : currentItem);
                        continue;
                    }

                    const mabr = PolygonClip.computeMABR(currentPoints);
                    let newPointsStr = mabr.points.join(',');
                    allPointsStr.push(newPointsStr);
                    
                    if (isEditMode) {
                        targetArray[i].points = newPointsStr;
                    } else {
                        targetArray[i] = newPointsStr;
                    }
                    
                    if (i === 0) {
                        if (!$("#rotation").val()) {
                            let angle = mabr.angle;
                            $("#ui-rotation").val(angle.toFixed(1));
                            $("#rotation-icon").css("transform", "rotate(" + angle.toFixed(1) + "deg)");
                            $("#rotation").val(angle.toFixed(1));
                        }
                    }
                }
                
                if (isEditMode) {
                    $("#fm-add_kavling #planning_points").val(allPointsStr.join(';'));
                } else {
                    if (allPointsStr.length > 0) $("#fm-add_kavling #planning_points").val(allPointsStr[0]);
                }
                
                $(this).text("Batal Menyederhanakan");
                $(this).removeClass("btn-outline-primary").addClass("btn-warning");
                
                updateSelectionPreview(targetArray, isEditMode);
            }
        } else {
            if (isEditMode) {
                editdtt = JSON.parse(JSON.stringify(originalEditDtt));
                let allOriginalPts = editdtt.map(e => e.points);
                $("#fm-add_kavling #planning_points").val(allOriginalPts.join(';'));
                updateSelectionPreview(editdtt, true);
            } else {
                batchdtt = JSON.parse(JSON.stringify(originalBatchDtt));
                if (batchdtt.length > 0) $("#fm-add_kavling #planning_points").val(typeof batchdtt[0] === 'string' ? batchdtt[0] : batchdtt[0].join(','));
                updateSelectionPreview(batchdtt, false);
            }
            
            $("#rotation").val("");
            $("#ui-rotation").val("");
            $("#rotation-icon").css("transform", "rotate(0deg)");
            $(this).text("Sederhanakan ke Rect");
            $(this).removeClass("btn-warning").addClass("btn-outline-primary");
        }
    });

    function updateSelectionPreview(targetArray, isEditMode) {
        if (typeof stage === 'undefined') return;
        let lines = stage.find("#sel");
        for (let i = 0; i < lines.length; i++) {
            let item = targetArray[i];
            if (item) {
                let pts = isEditMode ? item.points : item;
                pts = typeof pts === 'string' ? pts.split(',').map(Number) : pts;
                if (lines[i]) lines[i].points(pts);
            }
        }
        stage.batchDraw();
    }
});

function interactiveFacadeArrow(pointsArr, callback) {
    isFacadeArrowActive = true;
    if (typeof pointsArr === 'string') pointsArr = pointsArr.split(',').map(Number);
    if (pointsArr.length < 6) {
        isFacadeArrowActive = false;
        callback(null); return;
    }

    let cx = 0, cy = 0;
    for(let i=0; i<pointsArr.length; i+=2) {
        cx += pointsArr[i];
        cy += pointsArr[i+1];
    }
    cx /= (pointsArr.length/2);
    cy /= (pointsArr.length/2);

    let selLine = stage.find('#sel')[0];
    let layer = selLine ? selLine.getLayer() : stage.getLayers()[0];
    
    let arrow = new Konva.Arrow({
        points: [cx, cy, cx, cy - 50],
        pointerLength: 10,
        pointerWidth: 10,
        fill: 'black',
        stroke: 'black',
        strokeWidth: 4,
        id: 'facade_arrow'
    });
    
    layer.add(arrow);
    layer.batchDraw();

    let arrowAngle = 0;

    let moveHandler = function() {
        let pos = stage.getPointerPosition();
        if (!pos) return;
        
        let t = stage.getAbsoluteTransform().copy();
        t.invert();
        let localPos = t.point(pos);
        
        let dx = localPos.x - cx;
        let dy = localPos.y - cy;
        arrowAngle = Math.atan2(dy, dx) * 180 / Math.PI;
        
        let len = Math.sqrt(dx*dx + dy*dy);
        if (len < 20) len = 20; 
        
        arrow.points([cx, cy, cx + Math.cos(arrowAngle * Math.PI/180)*len, cy + Math.sin(arrowAngle * Math.PI/180)*len]);
        layer.batchDraw();
    };

    stage.on('mousemove.facade', moveHandler);
    stage.on('touchmove.facade', moveHandler);

    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom',
        showConfirmButton: false,
        timer: 10000,
        timerProgressBar: true,
    });
    Toast.fire({
        icon: 'warning',
        title: 'Arahkan panah ke arah jalan, lalu KLIK KIRI pada peta.'
    });

    let clickHandler = function(e) {
        isFacadeArrowActive = false;
        if (e.evt) e.evt.preventDefault();
        
        stage.off('mousemove.facade');
        stage.off('touchmove.facade');
        stage.off('click.facade');
        stage.off('tap.facade');
        
        arrow.destroy();
        layer.batchDraw();
        Swal.close();
        
        callback(arrowAngle);
    };

    setTimeout(() => {
        stage.on('click.facade', clickHandler);
        stage.on('tap.facade', clickHandler);
    }, 100);
}
