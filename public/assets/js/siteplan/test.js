const file_url = (source, id, download = false) => ${base_url}files//;

var c_date = new Date();
var c_date_m = (c_date.getMonth() + 1 > 10) ? c_date.getMonth() + 1 : "0" + (c_date.getMonth() + 1);
var today_date = c_date.getFullYear() + '-' + c_date_m + '-' + c_date.getDate();

//convert date to num
function treatAsUTC(date) {
    var result = new Date(date);
    result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
    return result;
}

//cari selisih hari
function daysBetween(startDate, endDate) {
    var millisecondsPerDay = 24 * 60 * 60 * 1000;
    return (treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay;
}

// palidasi manual
function palid(id, val, msg) {
    if ($(#" + id).val() == val) {
        Swal.fire({
            position: 'bottom-end',
            icon: 'error',
            title: msg,
            showConfirmButton: false,
            timer: 1500
        });
        return false;
    }
    return true;

}
Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0, 10);
});
    let data_um = [],
        data_bb = [];
    $.fn.modal.Constructor.prototype._enforceFocus = function() {}, $(".carousel").carousel("pause");
    var sp, kav, imageInfo, fp = flatpickr(".flatpickr-human-friendly", {
            altInput: !0,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d"
        }),
        dtt = [],
        batchdtt = [],
        bml_old = 0,
        batchMask = [],
        dtt_first = [],
        sceneWidth = screen.width,
        sceneHeight = .7 * screen.height,
        stage = new Konva.Stage({
            height: sceneWidth,
            width: sceneHeight,
            container: "konva-holder",
            draggable: !0
        }),
        siteplan = new Konva.Layer,
        masked = new Konva.Layer,
        datal = new Konva.Layer,
        group = new Konva.Group({
            visible: !1
        }),
        manual_selection = new Konva.Group,
        maskedGroup = new Konva.Group,
        tooltip = new Konva.Text({
            text: "",
            fontFamily: "Calibri",
            fontSize: 12,
            padding: 5,
            textFill: "white",
            fill: "black",
            text: "vertical align",
            alpha: .75
        }),
        tooltipbg = new Konva.Rect({
            width: 180,
            height: 57,
            stroke: "black",
            strokeWidth: 1,
            fill: "#f2ff7d"
        });
    Konva.hitOnDragEnabled = !0, group.add(tooltipbg, tooltip), datal.add(group);
    var imageObj = new Image;
    imageObj.onload = function() {
        sp = new Konva.Image({
            x: 0,
            y: 0,
            image: imageObj,
            width: imageObj.width,
            height: imageObj.height
        }), siteplan.add(sp);
        let a = parseFloat($("#konva-holder").width()) / imageObj.width;
        stage.scale({
            x: a,
            y: a
        }), group.scale({
            x: 1 / a,
            y: 1 / a
        })
    }, imageObj.src = dt_proyek.siteplan_access_url || file_url('proyek_siteplan', dt_proyek.id_proyek), window.onload = function() {
        colorThreshold = 15, blurRadius = 1, simplifyTolerant = 0, simplifyCount = 30, hatchLength = 4, hatchOffset = 0, imageInfo = null, cacheInd = null, mask = null, oldMask = null, downPoint = null, allowDraw = !1, addMode = !1, currentThreshold = colorThreshold, showThreshold();
        var a = imageObj,
            t = masked;
        t.width = a.width, t.height = a.height, imageInfo = {
            width: a.width,
            height: a.height,
            context: t.getContext("2d")._context
        }, mask = null;
        var e = document.createElement("canvas").getContext("2d");
        e.canvas.width = imageInfo.width, e.canvas.height = imageInfo.height, e.drawImage(a, 0, 0), imageInfo.data = e.getImageData(0, 0, imageInfo.width, imageInfo.height), load_kavling(), load_menu(), $("#tambah_jalan").change((function(a) {
            hapus_seleksi()
        }))
    };
    var stroke, fill, strokeWidth, dashed, line_ms = new Konva.Line({
        points: [0, 0],
        stroke: "red",
        strokeWidth: 2,
        dash: [5, 5],
        opacity: 1,
        closed: !0,
        id: "line_sel"
    });

    function load_menu() {
        $("#menu_here").html("")
    }

    function load_kavling() {
        for (var a = datal.find("Line"), t = 0; t < a.length; t++) a[t].destroy();
        let e = $("#pilih-divisi option:selected").val();
        $.ajax({
            url: base_url + "/siteplan/get_kavling_all",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_role: e
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(a) {
                $("#loading").addClass("hidden"), csrfHash = a.token, stroke = "", fill = "", strokeWidth = "", dashed = "";
                let t = a.data;
                for (var n = 0; n < t.length; n++) set_fill("#fff67a", "#000000", 0, null), 3 == e ? 0 == t[n].is_lunas || null == t[n].is_lunas || "undefined" == t[n].is_lunas ? ("Booking" == t[n].status_mkdt && set_fill("#8a0085", "#000", "0", null), "Batal" == t[n].status_mkdt && set_fill("#ff0000", "#000", "0", null), null != t[n].jatuh_tempo_tgl && "0000-00-00" != t[n].jatuh_tempo_tgl && (daysBetween(today_date, t[n].jatuh_tempo_tgl) < 7 ? set_fill("#ff0000", "#000", "0", null) : daysBetween(today_date, t[n].jatuh_tempo_tgl) < 14 && set_fill("#fc7b03", "#000", "0", null))) : 1 == t[n].is_lunas && set_fill("#00ff15", "#000", "0", null) : 4 == e ? "Batal" == t[n].status_mkdt ? set_fill("#ff0000", "#000", "0", null) : "Akad" == t[n].status_mkdt ? set_fill("#00ff15", "#000", "0", null) : "Booking" == t[n].status_mkdt && (null != t[n].booking_tgl && "0000-00-00" != t[n].booking_tgl && set_fill("#8a0085", "#000", "0", null), null != t[n].sp3k_tgl && "0000-00-00" != t[n].sp3k_tgl && set_fill("#4dbbff", "#000", "0", null)) : 7 == e ? (null != t[n].id_produksi ? 1 == t[n].pondasi && 1 == t[n].naik_dinding && 1 == t[n].topping_off && 1 == t[n].finishing && 1 == t[n].slo && 1 == t[n].bp && 1 == t[n].jalan && 1 == t[n].lpa && 1 == t[n].saluran ? set_fill("#00ff15", "#000", "0", null) : t[n].pondasi || t[n].naik_dinding || t[n].topping_off || t[n].finishing || t[n].slo || t[n].bp || t[n].jalan || t[n].lpa || t[n].saluran ? t[n].pondasi && t[n].naik_dinding && t[n].topping_off && t[n].finishing && t[n].slo && t[n].bp && t[n].jalan && t[n].lpa && 1 != !t[n].saluran || (1 == t[n].perintah_bangun ? set_fill("#fc7b03", "#000", "0", null) : set_fill("#8a0085", "#000", "0", null)) : 1 == t[n].perintah_bangun ? set_fill("#fc7b03", "#000", "0", null) : set_fill("#fff67a", "#000000", 0, null) : 1 == t[n].perintah_bangun && set_fill("#fc7b03", "#000", "0", null), 1 != t[n].status_komplain && 2 != t[n].status_komplain && 3 != t[n].status_komplain || set_fill("#ff0000", "#000", "0", null)) : 8 == e ? (1 == t[n].slo && 1 == t[n].bp && 1 == t[n].jalan && 1 == t[n].lpa && 1 == t[n].saluran && set_fill("#4dbbff", "#000", "0", null), "Akad" == t[n].status_mkdt && set_fill("#8a0085", "#000", "0", null), 1 != t[n].status_komplain && 2 != t[n].status_komplain && 3 != t[n].status_komplain || set_fill("#ff0000", "#000", "0", null), 1 == t[n].is_checked && set_fill("#fc7b03", "#000", "0", null), 1 == t[n].is_serah_terima && set_fill("#00ff15", "#000", "0", null)) : 5 == e && (null != t[n].sertifikat_tgl && "0000-00-00" != t[n].sertifikat_tgl && null != t[n].sertifikat_masa_berlaku && "0000-00-00" != t[n].sertifikat_masa_berlaku && null != t[n].bphtb_masa_berlaku && "0000-00-00" != t[n].bphtb_masa_berlaku && null != t[n].imb_tgl && "0000-00-00" != t[n].imb_tgl && null != t[n].bphtb_tgl && "0000-00-00" != t[n].bphtb_tgl && null != t[n].sertifikat_no_hgb && null != t[n].sertifikat_no_split && null != t[n].imb_no_induk && null != t[n].imb_no_split && null != t[n].nop_pbb && null != t[n].pph ? set_fill("#00ff15", "#000", "0", null) : (null != t[n].sertifikat_tgl && "0000-00-00" != t[n].sertifikat_tgl || null != t[n].sertifikat_masa_berlaku && "0000-00-00" != t[n].sertifikat_masa_berlaku || null != t[n].bphtb_masa_berlaku && "0000-00-00" != t[n].bphtb_masa_berlaku || null != t[n].imb_tgl && "0000-00-00" != t[n].imb_tgl || null != t[n].bphtb_tgl && "0000-00-00" != t[n].bphtb_tgl || null != t[n].sertifikat_no_hgb || null != t[n].sertifikat_no_split || null != t[n].imb_no_induk || null != t[n].imb_no_split || null != t[n].nop_pbb || null != t[n].pph) && set_fill("#8a0085", "#000", "0", null), null != t[n].sertifikat_masa_berlaku && "0000-00-00" != t[n].sertifikat_masa_berlaku && (daysBetween(today_date, t[n].sertifikat_masa_berlaku) < 30 ? set_fill("#ff0000", "#000", "0", null) : daysBetween(today_date, t[n].sertifikat_masa_berlaku) < 60 && set_fill("#fc7b03", "#000", "0", null)), null != t[n].bphtb_masa_berlaku && "0000-00-00" != t[n].bphtb_masa_berlaku && (daysBetween(today_date, t[n].bphtb_masa_berlaku) < 30 ? set_fill("#ff0000", "#000", "0", null) : daysBetween(today_date, t[n].bphtb_masa_berlaku) < 60 && set_fill("#fc7b03", "#000", "0", null))), t[n].harga_akhir = t[n].hargajual ? num_format(t[n].hargajual) + "(" + format_date(t[n].tgl_harga) + ")" : "-", kav = new Konva.Line({
                    points: JSON.parse("[" + t[n].points + "]"),
                    fill: fill,
                    dash: dashed,
                    opacity: .5,
                    closed: !0,
                    data: {
                        nama_jalan: t[n].nama_jalan,
                        no_kavling: t[n].no_kavling,
                        id_produksi: t[n].id_produksi,
                        id_legal: t[n].id_legal,
                        id_keuangan: t[n].id_keuangan,
                        id_sales: t[n].id_sales,
                        id_planning: t[n].id_planning,
                        id_mkdt: t[n].id_mkdt,
                        id_umum: t[n].id_umum,
                        id_direksi: t[n].id_direksi,
                        tipe: "kavling",
                        status_tanah: t[n].status_tanah
                    },
                    data2: {
                        status_mkdt: t[n].status_mkdt,
                        id_tipe: t[n].id_tipe,
                        tipe_rumah: t[n].tipe_rumah,
                        no_tipe_rumah: t[n].no_tipe_rumah,
                        id_gambar_kerja: t[n].id_gambar_kerja,
                        harga_akhir: t[n].harga_akhir,
                        harga_akhir_tgl: t[n].harga_akhir_tgl,
                        harga_akhir_oleh: t[n].harga_akhir_oleh_username,
                        id_serah_terima: t[n].id_serah_terima,
                        id_komplain: t[n].id_komplain
                    },
                    id: "kav" + t[n].id_kavling
                }), datal.add(kav)
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $.ajax({
            url: base_url + "/siteplan/get_others",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_role: e
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(a) {
                stroke = "", fill = "", strokeWidth = "", dashed = "";
                for (var t = a.data, e = 0; e < t.length; e++) "jalan" == t[e].tipe ? set_fill("#ccc", "#000", "0", null) : "fasos" == t[e].tipe ? set_fill("#9000ff", "#000", "0", null) : "rth" == t[e].tipe && set_fill("#0f0", "#000", "0", null), kav = new Konva.Line({
                    points: JSON.parse("[" + t[e].points + "]"),
                    fill: fill,
                    dash: dashed,
                    opacity: .5,
                    closed: !0,
                    data: {
                        tipe: t[e].tipe,
                        nama_jalan: t[e].nama_jalan
                    },
                    data2: {},
                    id: "others" + t[e].id
                }), datal.add(kav)
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        })
    }
    $("#pilih-divisi").change((function() {
        if ($("#tambah_jalan").prop("checked", 0), hapus_seleksi(), 1 == roleid) {
            let a = $("#pilih-divisi option:selected").val();
            $(".div_menu").addClass("hidden"), 6 == a ? $("#planning_menu").removeClass("hidden") : 4 == a ? $("#mkdt_menu").removeClass("hidden") : 7 == a ? $("#produksi_menu").removeClass("hidden") : 8 == a ? $("#sales_menu").removeClass("hidden") : 3 == a ? $("#keuangan_menu").removeClass("hidden") : 9 == a ? $("#direksi_menu").removeClass("hidden") : 0 == a ? $("#others_menu").addClass("hidden") : $("#others_menu").removeClass("hidden")
        }
        load_kavling()
    }));
    var idss, idsb, idst, idstb, ajal, seljal, scaleBy = 1.1;

    function hapus_seleksi() {
        line_ms.points([0, 0]), bml_old = 0, dtt_first = [], idss = stage.find("#sel")[0], idst = stage.find("#tsel")[0], seljal = stage.find("#seljal");
        for (let a = 0; a <= seljal.length; a++) seljal[a] && seljal[a].destroy();
        dtt = [], idss && idss.destroy(), idst && idst.destroy(), hapus_seleksi_batch()
    }

    function hapus_seleksi_batch() {
        idsb = stage.find("#sel"), idstb = stage.find("#tsel");
        for (let a = 0; a <= idsb.length; a++) idsb[a] && idsb[a].destroy(), idstb[a] && idstb[a].destroy();
        batchMask = [], batchdtt = [], editdtt = [], datal.draw()
    }
    stage.on("wheel", (a => {
        a.evt.preventDefault();
        var t = stage.scaleX(),
            e = stage.getPointerPosition(),
            n = (e.x - stage.x()) / t,
            i = (e.y - stage.y()) / t;
        let l = a.evt.deltaY > 0 ? -1 : 1;
        a.evt.ctrlKey && (l = -l);
        var s = l > 0 ? t * scaleBy : t / scaleBy;
        stage.scale({
            x: s,
            y: s
        }), group.scale({
            x: 1 / s,
            y: 1 / s
        });
        var o = {
            x: e.x - n * s,
            y: e.y - i * s
        };
        stage.position(o)
    }));
    var data, mousePos, persentase, editdtt = [];

    function getDistance(a, t) {
        return Math.sqrt(Math.pow(t.x - a.x, 2) + Math.pow(t.y - a.y, 2))
    }

    function getCenter(a, t) {
        return {
            x: (a.x + t.x) / 2,
            y: (a.y + t.y) / 2
        }
    }
    datal.on("dblclick dbltap", (function(a) {
        lihat_detail()
    })), siteplan.on("tap", (function() {
        group.hide()
    })), datal.on("tap", (function(a) {
        var t = a.target.attrs,
            e = stage.getRelativePointerPosition();
        if (group.position({
                x: e.x + 20,
                y: e.y + 5
            }), t.data) {
            if (!t.data.nama_jalan || !t.data.no_kavling) return;
            tooltip.text(t.data.nama_jalan + " No. " + t.data.no_kavling + "\n" + t.data2.no_tipe_rumah + "\n" + t.data2.tipe_rumah + " ( " + t.data.status_tanah + ") \nHJ: Rp. " + t.data2.harga_akhir)
        }
        group.moveToTop(), group.show()
    })), datal.on("click tap", (function(a) {
        if (!$("#tambah_jalan").prop("checked")) {
            var t = a.target.attrs,
                e = $("#pilih-divisi option:selected").val();
            t.id.substr(3);
            if (addMode = a.evt.ctrlKey, 1 == roleid)
                if (1 == addMode && 6 == e || 1 == addMode && 9 == e) {
                    if ("kavling" != t.data.tipe) return void Swal.fire({
                        position: "bottom-end",
                        icon: "error",
                        title: "Terjadi Kesalahan.",
                        text: "Multiple Selection hanya untuk data kavling ",
                        showConfirmButton: !1
                    });
                    if ("kavling" != editdtt[0].data.tipe) return void Swal.fire({
                        position: "bottom-end",
                        icon: "error",
                        title: "Terjadi Kesalahan.",
                        text: "Multiple Selection hanya untuk data kavling ",
                        showConfirmButton: !1
                    });
                    editdtt.push(t), drawBorderEdit(t)
                } else hapus_seleksi(), editdtt.push(t), drawBorderEdit(t);
            else 1 == addMode && 6 == roleid || 1 == addMode && 9 == roleid ? (editdtt.push(t), drawBorderEdit(t)) : (hapus_seleksi(), editdtt.push(t), drawBorderEdit(t))
        }
    })), stage.on("click tap", (function(a) {
        if ($("#tambah_jalan").prop("checked")) {
            dtt = [];
            var t = this.getRelativePointerPosition(),
                e = new Konva.Circle({
                    x: t.x,
                    y: t.y,
                    fill: "red",
                    radius: 5,
                    id: "seljal",
                    draggable: !0
                });
            manual_selection.add(e);
            var n = stage.find("#seljal");
            for (let a = 0; a < n.length; a++) dtt.push(Math.trunc(n[a].attrs.x), Math.trunc(n[a].attrs.y));
            line_ms.points(dtt)
        }
    })), masked.add(line_ms), masked.add(manual_selection), manual_selection.on("dragend", (function(a) {
        dtt = [];
        var t = stage.find("#seljal");
        for (let a = 0; a < t.length; a++) dtt.push(Math.trunc(t[a].attrs.x), Math.trunc(t[a].attrs.y));
        line_ms.points(dtt)
    })), datal.on("mousemove", (function(a) {
        if (data = a.target.attrs, mousePos = stage.getRelativePointerPosition(), group.position({
                x: mousePos.x + 20,
                y: mousePos.y + 5
            }), data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling) return;
            tooltip.text(data.data.nama_jalan + " No. " + data.data.no_kavling + "\n" + data.data2.no_tipe_rumah + "\n" + data.data2.tipe_rumah + " ( " + data.data.status_tanah + ")\nHJ: Rp. " + data.data2.harga_akhir)
        }
        group.moveToTop(), group.show()
    })), datal.on("mouseover", (function(a) {
        var t = a.target;
        t.setAttr("strokeWidth", 4), t.setAttr("stroke", "black")
    })), datal.on("mouseout", (function(a) {
        a.target.setAttr("strokeWidth", 0), group.hide()
    }));
    var act, editdtt_tmp, lastCenter = null,
        lastDist = 0;

    function lihat_detail() {
        if (0 != editdtt.length) {
            $("#last_update_legal, #last_update_mkdt, #last_update_keuangan, #last_update_prod").html("Terakhir dipudate oleh: -, pada: - ");
            var a = editdtt[0],
                t = a.id.substr(3);
            return $("#fm-detail")[0].reset(), $("#tb-data-tagihan-detail").html(""), "kavling" == a.data.tipe ? detail_kavling(a, t) : detail_others(a)
        }
        Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Terjadi Kesalahan.",
            text: "Tidak ada kavling yang dipilih",
            showConfirmButton: !1
        })
    }

    function detail_others(a) {
        $("#f_detail_progres_jalan").val(0), $(".t_luas_planning, .t_keterangan_planning, .t_luas_legal, .t_keterangan_legal, .t_luas_produksi, .t_keterangan_produksi, .r_progres").html("-"), $.ajax({
            url: base_url + "/siteplan/get_others",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: editdtt[0].id.substr(6)
            },
            dataType: "json",
            success: function(a) {
                if (csrfHash = a.token, a.data) {
                    let t = a.data[0],
                        e = t.progres ? t.progres : 0;
                    t.planning_luas && ($(".t_luas_planning").html(t.planning_luas + "  m&sup2  (" + t.planning_edit + ": " + format_datetime(t.planning_updated_at) + ")"), $(".t_keterangan_planning").html(t.planning_keterangan)), t.legal_luas && ($(".t_luas_legal").html(t.legal_luas + "  m&sup2  (" + t.legal_edit + ": " + format_datetime(t.legal_updated_at) + ")"), $(".t_keterangan_legal").html(t.legal_keterangan)), t.produksi_luas && ($(".t_luas_produksi").html(t.produksi_luas + "  m&sup2  (" + t.produksi_edit + ": " + format_datetime(t.produksi_updated_at) + ")"), $(".t_keterangan_produksi").html(t.produksi_keterangan)), $("#f_detail_progres_jalan").val(e), $(".r_progres").html(e)
                }
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + a.data.tipe + "<span>: " + a.data.nama_jalan), $("#modal_othersdetail").modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function detail_kavling(a, t) {
        $.ajax({
            url: base_url + "/siteplan/get_detail",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: t,
                id_legal: a.data.id_legal,
                id_produksi: a.data.id_produksi,
                id_keuangan: a.data.id_keuangan,
                id_mkdt: a.data.id_mkdt
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(t) {
                $("#loading").addClass("hidden"), csrfHash = t.token;
                let e = t.mkdt;
                if (e) {
                    e.username && $("#last_update_mkdt").html("Terakhir diupdate oleh: " + e.username + " pada: " + format_datetime(e.updated_at));
                    for (let a in e) $("#detail_" + a).val(e[a]);
                    "0000-00-00" != e.booking_tgl && document.querySelector("#detail_booking_tgl")._flatpickr.setDate(e.booking_tgl), "0000-00-00" != e.wawancara_tgl && document.querySelector("#detail_wawancara_tgl")._flatpickr.setDate(e.wawancara_tgl), "0000-00-00" != e.sp3k_tgl && document.querySelector("#detail_sp3k_tgl")._flatpickr.setDate(e.sp3k_tgl), "0000-00-00" != e.rencana_akad_tgl && document.querySelector("#detail_rencana_akad_tgl")._flatpickr.setDate(e.rencana_akad_tgl), "0000-00-00" != e.akad_tgl && document.querySelector("#detail_akad_tgl")._flatpickr.setDate(e.akad_tgl), $(".num").keyup().change(), lihat_total(), $("#detail_mkdt_keterangan").val(e.keterangan)
                }
                if (t.tagihan) {
                    let a = parseInt(t.keuangan[0].sudah_bayar) ? parseInt(t.keuangan[0].sudah_bayar) - 1e6 : 0,
                        e = parseInt(t.tagihan[0].total_biaya) ? parseInt(t.tagihan[0].total_biaya) : 0,
                        n = 0 == a ? 0 : a / e * 100;
                    n = ~~n + "%", $("#lihat_detail_total_biaya").val(e).keyup(), $("#lihat_detail_sudah_bayar").val(a).keyup(), $("#lihat_detail_persentase").val(n), $("#lihat_detail_sisa").val(e - a).keyup()
                }
                t.keuangan;
                $("#tb-detail-log_pembayaran").html("");
                let n = t.legal;
                if (n) {
                    n.username && $("#last_update_legal").html("Terakhir diupdate oleh: " + n.username + " pada: " + format_datetime(n.updated_at));
                    for (let a in n) $("#detail_" + a).val(n[a]);
                    "0000-00-00" != n.sertifikat_tgl && document.querySelector("#detail_sertifikat_tgl")._flatpickr.setDate(n.sertifikat_tgl), "0000-00-00" != n.sertifikat_masa_berlaku && document.querySelector("#detail_sertifikat_masa_berlaku")._flatpickr.setDate(n.sertifikat_masa_berlaku), "0000-00-00" != n.imb_tgl && document.querySelector("#detail_imb_tgl")._flatpickr.setDate(n.imb_tgl), "0000-00-00" != n.bphtb_tgl && document.querySelector("#detail_bphtb_tgl")._flatpickr.setDate(n.bphtb_tgl), "0000-00-00" != n.bphtb_masa_berlaku && document.querySelector("#detail_bphtb_masa_berlaku")._flatpickr.setDate(n.bphtb_masa_berlaku), "0000-00-00" != n.bphtb_validasi && document.querySelector("#detail_bphtb_validasi")._flatpickr.setDate(n.bphtb_validasi), "0000-00-00" != n.akad_tgl && document.querySelector("#detail_legal_akad_tgl")._flatpickr.setDate(n.akad_tgl), $("#detail_legal_keterangan").val(n.keterangan)
                }
                let i = t.produksi;
                i && (pondasi = 0, topping_off = 0, naik_dinding = 0, finishing = 0, slo = 0, bp = 0, jalan = 0, lpa = 0, tot = 0, saluran = 0, i.username && $("#last_update_produksi").html("Terakhir diupdate oleh: " + i.username + " pada: " + format_datetime(i.updated_at)), pondasi = 1 == i.pondasi ? 1 : 0, $("#detail_pondasi").prop("checked", pondasi).change(), topping_off = 1 == i.topping_off ? 1 : 0, $("#detail_topping_off").prop("checked", topping_off).change(), naik_dinding = 1 == i.naik_dinding ? 1 : 0, $("#detail_naik_dinding").prop("checked", naik_dinding).change(), finishing = 1 == i.finishing ? 1 : 0, $("#detail_finishing").prop("checked", finishing).change(), jalan = 1 == i.jalan ? 1 : 0, $("#detail_jalan").prop("checked", jalan).change(), slo = 1 == i.slo ? 1 : 0, $("#detail_slo").prop("checked", slo).change(), bp = 1 == i.bp ? 1 : 0, $("#detail_bp").prop("checked", bp).change(), lpa = 1 == i.lpa ? 1 : 0, $("#detail_lpa").prop("checked", lpa).change(), saluran = 1 == i.saluran ? 1 : 0, $("#detail_saluran").prop("checked", saluran).change(), $("#detail_progres_bangunan").val(i.progres_bangunan), $("#detail_t_progres_bangunan").html(i.progres_bangunan), $("#detail_produksi_keterangan").val(i.keterangan)), $(".label_alamat").html("<?= $data['proyek']->nama_proyek ?><br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/> Harga Jual: Rp. " + a.data2.harga_akhir + "<br/>(" + format_date(a.data2.harga_akhir_tgl) + " - " + a.data2.harga_akhir_oleh + ")"), $("#modal_detail").modal("show")
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        })
    }

    function isi_data() {
        if (0 != editdtt.length) {
            if (editdtt.length > 1) return Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Terjadi Kesalahan.",
                text: "Jangan pilih kavling lebih dari 1",
                showConfirmButton: !1
            }), void hapus_seleksi();
            var a, t = editdtt[0],
                e = t.id.substr(3);
            if (7 == (a = 1 == roleid ? $("#pilih-divisi option:selected").val() : roleid)) open_produksi(t, a, e);
            else if (5 == a) open_legal(t, a, e);
            else if (4 == a) open_mkdt(t, a, e);
            else if (3 == a) {
                if (!t.data.id_mkdt) return void Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Belum ada data konsumen di kavling" + t.data.nama_jalan + ", No." + t.data.no_kavling,
                    showConfirmButton: !1,
                    timer: 1500
                });
                open_keuangan(t, a, e), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + t.data.nama_jalan + ", No." + t.data.no_kavling + "<br/>" + t.data2.no_tipe_rumah + " (" + t.data2.tipe_rumah + ")<br/>"), $("#modal_divisi" + a).modal({
                    backdrop: "static",
                    keyboard: !1
                })
            } else 6 == a ? (addMode ? (editdtt.push(t), drawBorderEdit(t)) : (hapus_seleksi(), open_planning(t, a, e)), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + t.data.nama_jalan + ", No." + t.data.no_kavling + "<br/>" + t.data2.no_tipe_rumah + " (" + t.data2.tipe_rumah + ")<br/>"), $("#modal_divisi" + a).modal({
                backdrop: "static",
                keyboard: !1
            })) : 8 == a && ($(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + t.data.nama_jalan + ", No." + t.data.no_kavling + "<br/>" + t.data2.no_tipe_rumah + " (" + t.data2.tipe_rumah + ")<br/>"), $("#modal_divisi" + a).modal({
                backdrop: "static",
                keyboard: !1
            }))
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Terjadi Kesalahan.",
            text: "Tidak ada kavling yang dipilih",
            showConfirmButton: !1
        })
    }

    function tambah_kavling() {
        if ($(".t_luas_legal, .t_luas_produksi, .r_progres").html("-"), $("#pindah_lokasi_btn").hide(), act = "add", $("#fm-add_kavling")[0].reset(), $(".select2").val(null).trigger("change"), editdtt.length > 0) Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Terjadi Kesalahan.",
            text: "Lokasi sudah diisi oleh kavling lain",
            showConfirmButton: !1
        });
        else {
            if ($("#tambah_jalan").prop("checked")) {
                if (dtt.length < 6) return void Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Seleksi manual minimal 3 titik",
                    showConfirmButton: !1,
                    timer: 1500
                })
            } else if (!stage.find("#sel")[0]) return void Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Pilih kavling terlebih dahulu",
                showConfirmButton: !1,
                timer: 1500
            });
            $("#status_tanah").val("Standar").trigger("change"), $("#modals-slide-in").modal({
                backdrop: "static",
                keyboard: !1
            }), $("#points").val(dtt)
        }
    }

    function edit_kavling_batch() {
        if (0 == editdtt.length) return;
        let a, t;
        $("#pindah_lokasi_btn").hide(), 1 == editdtt.length && $("#pindah_lokasi_btn").show(), $(".t_luas_legal, .t_luas_produksi, .r_progres").html("-");
        let e = base_url + "/siteplan/get_others";
        if (t = editdtt[0].data.tipe, a = editdtt[0].id.substr(6), "kavling" == t) {
            a = [], e = base_url + "/siteplan/get_kavling_by_multiple_id";
            for (let e = 0; e < editdtt.length; e++) a.push(editdtt[e].id.substr(3)), t = editdtt[e].data.tipe
        }
        $("#fm-add_kavling")[0].reset(), $(".select2").val(null).trigger("change"), $.ajax({
            url: e,
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: a
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(a) {
                csrfHash = a.token, $("#id_jenis").val(t).change();
                let e, n, i, l = a.data,
                    s = "",
                    o = "",
                    r = "";
                if ($("#id_cluster").append($("<option selected></option>").attr("value", l[0].id_cluster).text(l[0].nama_cluster)).trigger("change"), $("#id_jalan").append($("<option selected></option>").attr("value", l[0].id_jalan).text(l[0].nama_jalan)).trigger("change"), $("#id_tipe").append($("<option selected></option>").attr("value", l[0].id_tipe).text(l[0].no_tipe_rumah + " (" + l[0].tipe_rumah + ")")).trigger("change"), "kavling" == t) {
                    if (l.length > 0) {
                        for (let a = 0; a < l.length; a++) s += l[a].id_kavling + ";", o += l[a].no_kavling + ";", e = l[a].id_cluster, n = l[a].id_jalan, i = l[a].id_tipe, r += l[a].points + ";";
                        $("#status_tanah").val(l[0].status_tanah).change(), $(".id_kavling").val(s), $("#no_kavling").val(o), $("#points").val(r), $("#f_luas").val(l[0].luas_tanah)
                    }
                } else if (l.length > 0) {
                    $(".id_kavling").val(l[0].id), $("#f_luas").val(l[0].planning_luas), $("#f_nama").val(l[0].nama), $("#f_planning_keterangan").val(l[0].planning_keterangan);
                    let a = l[0];
                    a.produksi_luas && $(".t_luas_produksi").html(a.produksi_luas + "  m&sup2  (" + a.produksi_edit + ": " + format_datetime(a.produksi_updated_at) + ")"), a.legal_luas && $(".t_luas_legal").html(a.legal_luas + "  m&sup2  (" + a.legal_edit + ": " + format_datetime(a.legal_updated_at) + ")")
                }
                $("#modals-slide-in").modal({
                    backdrop: "static",
                    keyboard: !1
                })
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $("#loading").addClass("hidden"), act = "edit"
    }

    function open_planning(a, t, e) {
        $("#fm-add_kavling")[0].reset(), $(".id_kavling").val(e), $(".t_luas_legal, .t_luas_produksi, .r_progres").html("-"), $.ajax({
            url: base_url + "/siteplan/get_kavling_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: e
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(a) {
                csrfHash = a.token;
                let t = a.data;
                if (t) {
                    for (let a in t) $("#fm-add_kavling #" + a).val(t[a]);
                    $("#modals-slide-in-edit").modal({
                        backdrop: "static",
                        keyboard: !1
                    })
                }
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $("#loading").addClass("hidden")
    }

    function edit_kavling() {
        let a = $("#fm-add_kavling #no_kavling").val().split(";"),
            t = "" == a[a.length - 1] ? a.length - 1 : a.length,
            e = editdtt[0].data.tipe,
            n = base_url + "/siteplan/edit_others";
        if ("kavling" == e) {
            if (editdtt.length > 0 && editdtt.length != t) return void Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Terjadi Kesalahan.",
                text: "Jumlah Kavling yang dipilih: " + editdtt.length + "\nJumlah No Kavling yang diisi: " + t,
                showConfirmButton: !1
            });
            n = base_url + "/siteplan/edit_kavling"
        }
        $.ajax({
            url: n,
            type: "post",
            data: $("#fm-add_kavling").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>'), $("#add-form-btn").addClass("disabled")
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#modals-slide-in").modal("hide"), $("#add-form-btn").html("Simpan"), $("#add-form-btn").removeClass("disabled")
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn").html("Simpan"), $("#add-form-btn").removeClass("disabled")
                })), load_kavling(), hapus_seleksi()
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan",
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn").html("Simpan"), $("#add-form-btn").removeClass("disabled")
                }))
            }
        })
    }

    function add_kavling() {
        if ("edit" == act) return edit_kavling();
        if ("" == $("#id_jenis").val()) return void Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Jenis harus diisi",
            showConfirmButton: !1,
            timer: 1500
        });
        if (!$("#id_cluster").val()) return void Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Cluster harus diisi",
            showConfirmButton: !1,
            timer: 1500
        });
        if (!$("#id_jalan").val()) return void Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "jalan harus diisi",
            showConfirmButton: !1,
            timer: 1500
        });
        $(".form-control").removeClass("is-invalid").removeClass("is-valid");
        let a = "";
        for (let t = 0; t < batchdtt.length; t++) a += "&bpoints[]=" + batchdtt[t];
        let t = $("#fm-add_kavling #no_kavling").val().split(";"),
            e = "" == t[t.length - 1] ? t.length - 1 : t.length;
        "kavling" != $("#id_jenis").val() || batchdtt.length == e ? $.ajax({
            url: base_url + "/siteplan/add_kavling",
            type: "post",
            data: $("#fm-add_kavling").serialize() + a + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>'), $("#add-form-btn").addClass("disabled")
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#modals-slide-in").modal("hide"), load_kavling(), hapus_seleksi()
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#add-form-btn").html("Simpan"), $("#add-form-btn").removeClass("disabled")
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi Kesalahan saat melakukan penambahan data kaving, jalan atau fasos",
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#add-form-btn").html("Simpan"), $("#add-form-btn").removeClass("disabled")
            }
        }) : Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Terjadi Kesalahan.",
            text: "Jumlah Kavling yang dipilih: " + batchdtt.length + "\nJumlah No Kavling yang diisi: " + e,
            showConfirmButton: !1
        })
    }

    function pindah_kavling() {
        editdtt_tmp = editdtt, $("#modals-slide-in").modal("hide"), $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").hide(), $("#selesai_pindah_btn, #batal_pindah_btn").show(), hapus_seleksi()
    }

    function selesai_selection(a) {
        if (1 == a) {
            if ("" == dtt) return void Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Tidak ada lokasi yang dipilih",
                showConfirmButton: !1,
                timer: 1500
            });
            $("#points").val(dtt)
        }
        editdtt = editdtt_tmp, $("#modals-slide-in").modal("show"), $("#add_kavling, #edit_kavling_batch, #planning_toggle_btn").show(), $("#selesai_pindah_btn, #batal_pindah_btn").hide()
    }

    function print_tagihan() {
        $("#cp_nama_perusahaan").html(""), $("#cp_alamat_perusahaan").html(""), $("#cp_telp").html(""), $("#tb-print-data-tagihan").html(""), document.querySelector("#tanggal_surat_tagihan")._flatpickr.setDate((new Date).toDateInputValue());
        let a = editdtt[0],
            t = a.id.substr(3);
        0 != editdtt.length ? a.data.id_mkdt ? $.ajax({
            url: base_url + "/keuangan/get_tagihan",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_keuangan: a.data.id_keuangan,
                id_kavling: t,
                id_mkdt: a.data.id_mkdt
            },
            dataType: "json",
            success: function(a) {
                let t = a.detail;
                csrfHash = a.token, $("#cp_nama_perusahaan").html(a.compro.nama_perusahaan), $("#cp_alamat_perusahaan").html(a.compro.alamat), $("#cp_telp").html(a.compro.telp + " - " + a.compro.telp2), $("#pt_nama_konsumen").html(t.nama_konsumen + " (" + t.hp_konsumen + ")"), $("#pt_alamat_konsumen").html(t.alamat_konsumen);
                let e = "",
                    n = 1,
                    i = 0,
                    l = "",
                    s = "",
                    o = a.list_tagihan;
                $.each(o, (function(a, t) {
                    s = 1 == t.sudah_dibayar ? "checked" : "", l = '<div class="form-group">\n                                    <div class="custom-control custom-switch custom-control-inline">\n                                        <input type="checkbox" ' + s + ' onchange="save_sb(' + t.id_keuangan + ')" class="custom-control-input " disabled id="sb_btn' + t.id_keuangan + '" name="sb_btn[' + t.id_keuangan + ']" value="1" />\n                                        <label class="custom-control-label" for="sb_btn' + t.id_keuangan + '"></label>\n                                    </div>\n                                </div>', i += parseInt(t.nominal), e += "<tr><td>" + n + "</td><td>" + t.berita_acara + "</td><td>" + format_date(t.jatuh_tempo_tgl) + "</td><td style='text-align:right'>" + num_format(t.nominal) + "</td><tr>", n++
                })), e += "<tr><th colspan='3' style='text-align:right'>Total Tagihan</th><th style='text-align:right'>" + num_format(i) + "</th><tr>", e += "<tr><th colspan='3' style='text-align:right'>Sudah Bayar</th><th style='text-align:right'>" + num_format(a.sudah_bayar) + "</th><tr>", e += "<tr><th colspan='3' style='text-align:right'>Sisa</th><th style='text-align:right'>" + num_format(i - parseInt(a.sudah_bayar)) + "</th><tr>", $("#tb-print-data-tagihan").append(e), $("#print_tagihan_modal").modal({
                    backdrop: "static",
                    keyboard: !1
                })
            },
            error: function() {}
        }) : Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Belum ada data konsumen di kavling" + a.data.nama_jalan + ", No." + a.data.no_kavling,
            showConfirmButton: !1,
            timer: 1500
        }) : Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Terjadi Kesalahan.",
            text: "Tidak ada kavling yang dipilih",
            showConfirmButton: !1
        })
    }

    function doPrint() {
        (async () => {
            await fetch(base_url + "/keuangan/doPrint", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    [csrfName]: csrfHash,
                    konsumen: $("#pt_nama_konsumen").html(),
                    alamat: $("#pt_alamat_konsumen").html(),
                    no_sruat: $("#no_sruat").val(),
                    tanggal_surat_tagihan: $("#tanggal_surat_tagihan").val(),
                    table: $("#tb-print-data-tagihan").html()
                })
            }).then((a => a.blob())).then((a => {
                const t = window.URL.createObjectURL(a),
                    e = document.createElement("a");
                e.style.display = "none", e.href = t, e.download = "Tagihan " + $("#pt_nama_konsumen").html() + " " + $("#tanggal_surat_tagihan").val() + ".pdf", document.body.appendChild(e), e.click(), window.URL.revokeObjectURL(t)
            })).catch((() => alert("oh no!")))
        })()
    }

    function save_sb(a) {
        let t = $("#sb_btn" + a).prop("checked") ? 1 : 0;
        $.ajax({
            url: base_url + "/keuangan/save_sb",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_keuangan: a,
                sb: t
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(a) {
                csrfHash = a.token, $("#loading").addClass("hidden")
            }
        })
    }

    function sum_mktotal() {
        let a = parseFloat(removeComma($("#mk-hargajual").val()) || 0) - parseFloat(removeComma($("#mk-harga_kpr_acc").val() || 0)) - parseFloat(removeComma($("#mk-diskon").val()) || 0),
            t = 0;
        $(".totalbb").toArray().forEach((function(a) {
            t += parseFloat(removeComma(a.value) || 0)
        })), $("#mk-total_um").val(a).keyup(), $("#mk-total_bb").val(t).keyup()
    }

    function isi_tagihan() {
        var a = editdtt[0],
            t = a.id.substr(3);
        a.data.id_mkdt ? (data_um = {}, data_bb = {}, $("#fm-isi_tagihan")[0].reset(), $("#list_cicilan_here").html(""), $("#total_cicilan_um").val(0).change().keyup(), $("#total_cicilan_bb").val(0).change().keyup(), $("#id_list_keu").val(""), $("#id_list_keu_bb").val(""), $.ajax({
            url: base_url + "/keuangan/get_data_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_keuangan: a.data.id_keuangan,
                id_kavling: t,
                id_mkdt: a.data.id_mkdt
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(t) {
                $("#loading").addClass("hidden");
                let e = t.mkdt,
                    n = t.hj,
                    i = t.tagihan;
                if ($("#mk-id_mkdt").val(a.data.id_mkdt), $(".select2").val(null).trigger("change"), e.id_hargajual) $("#mk-id").append($("<option selected></option>").attr("value", e.id_hargajual).text("Rp. " + num_format(e.harga_jual) + " (" + e.tipe_rumah + "): " + e.tgl_harga)).trigger("change"), $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(e.tgl_harga)), $("#mk-row").val(e.row).change(), $("#mk-tipe").val(e.tipe_rumah).change(), $("#mk-lb").val(e.hj_lb).change(), $("#mk-lt").val(e.hj_lt).change(), $("#mk-hargajual").val(e.harga_jual).change(), $("#mk-kpr").val(e.harga_kpr).change(), $("#mk-uang_muka").val(e.harga_jual - e.harga_kpr).change(), $("#mk-bphtb").val(e.harga_bphtb).change(), $("#mk-biaya_adm").val(e.harga_administrasi).change(), $("#mk-biaya_proses").val(e.harga_biaya_proses).change();
                else if (n.id) {
                    for (let a in n) $("#fm-isi_tagihan #mk-" + a).val(n[a]).change().keyup();
                    $("#fm-isi_tagihan #mk-tgl_harga").val(format_date(n.tgl_harga)), $("#fm-isi_tagihan #mk-tipe").val(n.tipe_rumah), $("#mk-id").append($("<option selected></option>").attr("value", n.id).text("Rp. " + num_format(n.hargajual) + " (" + n.tipe_rumah + "): " + n.tgl_harga)).trigger("change")
                } else $(".mk-fm").val(0);
                $("#mk-diskon").val(e.harga_diskon).change().keyup(), $("#mk-harga_penambahan").val(e.harga_penambahan).change().keyup(), $("#mk-keterangan_harga_penambahan").val(e.keterangan_penambahan_biaya), $("#mk-harga_ppn").val(e.harga_ppn).change().keyup(), $("#mk-harga_kpr_acc").val(e.harga_kpr_acc).change().keyup();
                let l = 0 == e.harga_kpr_acc ? 0 : e.harga_kpr - e.harga_kpr_acc;
                if ($("#mk-harga_penambahan_um").val(l).change().keyup(), sum_mktotal(), i) {
                    let a = it;
                    $.each(i, (function(t, e) {
                        "UM" == e.status && (data_um["lk" + a] = {
                            id_list_keu: "lk" + a,
                            id_keuangan: num_format(e.id_keuangan),
                            berita_acara: num_format(e.berita_acara),
                            nominal: num_format(e.nominal),
                            jatuh_tempo_tgl: num_format(e.jatuh_tempo_tgl)
                        }), "BB" == e.status && (data_bb["lk" + a] = {
                            id_list_keu_bb: "lk" + a,
                            id_keuangan_bb: num_format(e.id_keuangan),
                            berita_acara_bb: num_format(e.berita_acara),
                            nominal_bb: num_format(e.nominal),
                            jatuh_tempo_tgl_bb: num_format(e.jatuh_tempo_tgl)
                        }), a++
                    })), tambah_ketagihan(), it = a
                }
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#isi_tagihan-modal").modal({
                    backdrop: "static",
                    keyboard: !1
                })
            },
            error: function() {
                $("#loading").addClass("hidden"), Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        })) : Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Belum ada data konsumen di kavling" + a.data.nama_jalan + ", No." + a.data.no_kavling,
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_isi_tagihan(a) {
        if ((parseFloat(removeComma($("#total_cicilan_um").val() || 0)) > 0 || parseFloat(removeComma($("#total_cicilan_bb").val() || 0)) > 0) && ($("#total_cicilan_um").val() != $("#mk-total_um").val() || $("#total_cicilan_bb").val() != $("#mk-total_bb").val())) return Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Total Cicilan tidak sesuai dengan total biaya",
            showConfirmButton: !1,
            timer: 1500
        }), !1;
        let t = {};
        t[csrfName] = csrfHash, $("form#fm-isi_tagihan :input").each((function() {
            t[this.name] = this.value
        }));
        let e = 0;
        for (var n in data_um)
            if (data_um.hasOwnProperty(n)) {
                var i = data_um[n];
                for (var l in i)
                    if (i.hasOwnProperty(l)) {
                        var s = i[l];
                        t[l + "[" + e + "]"] = s
                    } e++
            } for (var n in e = 0, data_bb)
            if (data_bb.hasOwnProperty(n)) {
                i = data_bb[n];
                for (var l in i)
                    if (i.hasOwnProperty(l)) {
                        s = i[l];
                        t[l + "[" + e + "]"] = s
                    } e++
            } $.ajax({
            url: base_url + "/Keuangan/isi_tagihan",
            type: "post",
            data: t,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-isi-tagihan").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>'), $("#add-form-isi-tagihan").addClass("disabled")
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-isi-tagihan").html("Simpan"), $("#add-form-isi-tagihan").removeClass("disabled")
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-isi-tagihan").html("Simpan"), $("#add-form-isi-tagihan").removeClass("disabled")
                })), load_kavling(), hapus_seleksi()
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "terjadi kesalahan",
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-isi-tagihan").html("Simpan"), $("#add-form-isi-tagihan").removeClass("disabled")
                })), $("#add-form-isi-tagihan").html("Simpan"), $("#add-form-isi-tagihan").removeClass("disabled")
            }
        })
    }

    function open_keuangan(a, t, e) {
        $("#fm-keuangan")[0].reset(), $("#label_konsumen").html(""), $("#tb-data-log_pembayaran, #tb-data-tagihan").empty(), $("#booking_fee_paid, #keu_booking_fee").prop("disabled", !1), document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = !1, $(".id_kavling").val(e), $("#id_keuangan").val(a.data.id_keuangan), $("#id_mkdt").val(a.data.id_mkdt), $("#hide_lunas").removeClass("hidden"), $("#hide_refund").addClass("hidden"), $("#add-form-btn-keuangan").prop("disabled", !1), $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 0), document.querySelector("#tanggal_refund")._flatpickr._input.disabled = !1, $.ajax({
            url: base_url + "/keuangan/get_data_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_keuangan: a.data.id_keuangan,
                id_kavling: e,
                id_mkdt: a.data.id_mkdt
            },
            dataType: "json",
            success: function(a) {
                let t = a.mkdt,
                    e = a.sudah_bayar,
                    n = a.log_pembayaran,
                    i = "";
                if (tg = a.tagihan, csrfHash = a.token, $("#label_konsumen").html(t.nama_konsumen), t) {
                    $("#fm-keuangan #status_mkdt").val(t.status_mkdt), "Batal" == t.status_mkdt && ($("#hide_lunas").addClass("hidden"), $("#hide_refund").removeClass("hidden")), 1 == t.refund_paid && ($("#add-form-btn-keuangan").prop("disabled", !0), $("#hide_lunas").addClass("hidden"), $("#keterangan_refund, #nominal_refund, #tanggal_refund, #refund_paid").prop("disabled", 1), $("#fm-keuangan #refund_paid").prop("checked", 1), $("#keterangan_refund").val(t.refund_keterangan).change(), $("#nominal_refund").val(t.refund).change(), "0000-00-00" != t.refund_tgl && document.querySelector("#tanggal_refund")._flatpickr.setDate(t.refund_tgl), document.querySelector("#tanggal_refund")._flatpickr._input.disabled = !0, i = "disabled"), 1 == t.is_lunas && ($("#add-form-btn-keuangan").prop("disabled", !0), $("#hide_lunas").addClass("hidden"), i = "disabled");
                    for (let a in t) $("#fm-keuangan #" + a).val(t[a]);
                    $("#fm-keuangan #is_lunas").val(1), "0000-00-00" != t.booking_tgl && document.querySelector("#fm-keuangan #booking_tgl")._flatpickr.setDate(t.booking_tgl), $(".num").keyup().change(), $("#booking_fee_paid").val(t.booking_paid), 1 == t.booking_paid && ($("#booking_fee_paid, #keu_booking_fee").prop("disabled", !0), document.querySelector("#keu_booking_tgl")._flatpickr._input.disabled = !0)
                }
                let l = e.nominal ? e.nominal : 0,
                    s = removeComma($("#fm-keuangan #total_biaya").val()),
                    o = s - l,
                    r = 0;
                r = 0 == l ? 0 : l / s * 100, $("#sudah_bayar").val(l).keyup(), $("#sisa_tagihan").val(o).keyup(), $("#persentase_bayar_tagihan").val(r.toFixed(2) + "%");
                let d = "",
                    _ = 1,
                    m = 0,
                    p = "",
                    u = "";
                $.each(tg, (function(a, t) {
                    "UM" == t.status && (u = 1 == t.sudah_dibayar ? "checked" : "", p = '<div class="form-group">\n                                    <div class="custom-control custom-switch custom-control-inline">\n                                        <input type="checkbox" ' + u + ' onchange="save_sb(' + t.id_keuangan + ')" class="custom-control-input " ' + i + ' id="sb_btn' + t.id_keuangan + '" name="sb_btn[' + t.id_keuangan + ']" value="1" />\n                                        <label class="custom-control-label" for="sb_btn' + t.id_keuangan + '"></label>\n                                    </div>\n                                </div>', m += parseInt(t.nominal), d += "<tr><td>" + _ + "</td><td>" + t.berita_acara + "</td><td style='text-align:right'>" + num_format(t.nominal) + "</td><td>" + format_date(t.jatuh_tempo_tgl) + "</td><td>" + t.username + "<br/>" + format_datetime(t.created_at) + " </td><td>" + p + "</td><tr>", _++)
                })), d += "<tr><th colspan='2'>Total</th><th style='text-align:right'>" + num_format(m) + "</th><th colspan='3'></th><tr>", $("#tb-data-tagihan").append(d);
                let c = "",
                    k = 0;
                _ = 1, $.each(n, (function(a, t) {
                    "Booking" == t.payment_type && ($("#keu_booking_fee").val(t.nominal).keyup(), document.querySelector("#keu_booking_tgl")._flatpickr.setDate(t.tanggal_bayar)), "Refund" == t.payment_type && (k -= parseInt(t.nominal)), "Pembayaran" == t.payment_type && (k += parseInt(t.nominal)), c += "<tr><td>" + _ + "</td><td>" + t.keterangan + "</td><td style='text-align:right'>" + num_format(t.nominal) + "</td><td>" + format_date(t.tanggal_bayar) + "</td><td>" + t.username + "<br/>" + format_datetime(t.created_at) + " </td><tr>", _++
                })), c += "<tr><th colspan='2'>Total</th><th style='text-align:right'>" + num_format(k) + "</th><th colspan='3'></th><tr>", $("#tb-data-log_pembayaran").append(c)
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        })
    }

    function save_keuangan() {
        if ("Batal" == $("#fm-keuangan #status_mkdt").val()) {
            if (!palid("keterangan_refund", "", "Keterangan harus diisi")) return;
            if (!palid("nominal_refund", "", "Nominal harus diisi")) return
        } else if (1 == $("#booking_fee_paid").prop("disabled") && 0 == $("#fm-keuangan #is_lunas").prop("checked")) {
            if (!palid("berita_acara", "", "Berita acara harus diisi")) return;
            if (!palid("bayar_tagihan", "0", "Nominal Tidak boleh 0")) return;
            if (!palid("bayar_tagihan", null, "Nominal Tidak boleh kosong")) return;
            if (!palid("bayar_tagihan", "", "Nominal Tidak boleh kosong")) return;
            if (!palid("tanggal_bayar", "", "Tanggal bayar Tidak boleh kosong")) return;
            if ($("#bayar_tagihan").val() != $("#sisa_tagihan").val()) {
                if (!palid("berita_acara_jatuh_tempo", "", "Berita acara selanjutnya harus diisi")) return;
                if (!palid("jatuh_tempo_tgl_next", "", "Tanggal Jatuh tempo harus diisi")) return
            }
        }
        $.ajax({
            url: base_url + "/keuangan/save",
            type: "post",
            data: $("#fm-keuangan").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn-keuangan").prop("disabled", !0), $("#add-form-btn-keuangan").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $(".modal").modal("hide"), $("#add-form-btn-keuangan").html("Simpan"), $("#add-form-btn-keuangan").prop("disabled", !1)
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn-keuangan").html("Simpan"), $("#add-form-btn-keuangan").prop("disabled", !1)
                })), load_kavling(), hapus_seleksi()
            },
            error: function(a) {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "terjadi kesalahan",
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn-keuangan").html("Simpan"), $("#add-form-btn-keuangan").prop("disabled", !1)
                }))
            }
        })
    }

    function dana_akad() {
        if ($("#fm-dana_akad")[0].reset(), editdtt[0]) {
            var a = editdtt[0],
                t = a.id.substr(3);
            a.data.id_mkdt ? "Akad" == a.data2.status_mkdt ? ($.ajax({
                url: base_url + "/keuangan/getDanaAkad",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    id_kavling: t,
                    id_mkdt: a.data.id_mkdt
                },
                dataType: "json",
                beforeSend: function() {
                    $("#loading").removeClass("hidden")
                },
                success: function(t) {
                    $("#loading").addClass("hidden"), csrfHash = t.token;
                    var e = t.data;
                    $("#fm-dana_akad #id_mkdt").val(a.data.id_mkdt), $("#fm-dana_akad #id_dana_cair").val(e.id), $("#fm-dana_akad #nominal_dana_akad").val(e.nominal).change().keyup(), $("#fm-dana_akad #keterangan_dana_jaminan").val(e.keterangan_dana_jaminan), "0000-00-00" != e.tgl_rencana_cair && document.querySelector("#fm-dana_akad #tgl_rencana_cair")._flatpickr.setDate(e.tgl_rencana_cair), "0000-00-00" != e.tgl_cair && document.querySelector("#fm-dana_akad #tgl_cair")._flatpickr.setDate(e.tgl_cair), 1 == e.sudah_cair && $("#dana_akad_cair").prop("checked", !0)
                },
                error: function(a) {
                    Swal.fire({
                        position: "bottom-end",
                        icon: "error",
                        title: "terjadi kesalahan",
                        showConfirmButton: !1
                    })
                }
            }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#dana_akad_modal").modal({
                backdrop: "static",
                keyboard: !1
            })) : Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Kavling" + a.data.nama_jalan + ", No." + a.data.no_kavling + "Belum Akad!",
                showConfirmButton: !1,
                timer: 1500
            }) : Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Belum ada data konsumen di kavling" + a.data.nama_jalan + ", No." + a.data.no_kavling,
                showConfirmButton: !1,
                timer: 1500
            })
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Tidak ada kavling yang dipilih",
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_dana_akad() {
        $.ajax({
            url: base_url + "/keuangan/saveDanaAkad",
            type: "post",
            data: $("#fm-dana_akad").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn-dana_akad").prop("disabled", !0), $("#add-form-btn-dana_akad").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $(".modal").modal("hide"), $("#add-form-btn-dana_akad").prop("disabled", !1), $("#add-form-btn-dana_akad").html("Simpan")
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn-dana_akad").prop("disabled", !1), $("#add-form-btn-dana_akad").html("Simpan")
                })), load_kavling(), hapus_seleksi()
            },
            error: function(a) {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "terjadi kesalahan",
                    showConfirmButton: !1
                }), $("#add-form-btn-dana_akad").prop("disabled", !1), $("#add-form-btn-dana_akad").html("Simpan")
            }
        })
    }

    function total(a = "") {
        var t, e = removeComma("" == $(a + " #harga_jual").val() ? 0 : $(a + " #harga_jual").val()),
            n = removeComma("" == $(a + " #harga_diskon").val() ? 0 : $(a + " #harga_diskon").val()),
            i = removeComma("" == $(a + " #harga_penambahan").val() ? 0 : $(a + " #harga_penambahan").val()),
            l = (removeComma("" == $(a + " #harga_administrasi").val() ? 0 : $(a + " #harga_administrasi").val()), removeComma("" == $(a + " #harga_ppn").val() ? 0 : $(a + " #harga_ppn").val())),
            s = removeComma("" == $(a + " #harga_bphtb").val() ? 0 : $(a + " #harga_bphtb").val()),
            o = removeComma("" == $(a + " #harga_biaya_proses").val() ? 0 : $(a + " #harga_biaya_proses").val());
        t = e - removeComma("" == $(a + " #harga_kpr").val() ? 0 : $(a + " #harga_kpr").val()) - n + i + l + s + o, $(a + " #total_biaya").val(t).keyup(), $("#total_biaya2").val(t).keyup()
    }

    function lihat_total() {
        var a, t = removeComma("" == $("#detail_harga_jual").val() ? 0 : $("#detail_harga_jual").val()),
            e = removeComma("" == $("#detail_harga_diskon").val() ? 0 : $("#detail_harga_diskon").val()),
            n = removeComma("" == $("#detail_harga_penambahan").val() ? 0 : $("#detail_harga_penambahan").val()),
            i = (removeComma("" == $("#detail_harga_administrasi").val() ? 0 : $("#detail_harga_administrasi").val()), removeComma("" == $("#detail_harga_ppn").val() ? 0 : $("#detail_harga_ppn").val())),
            l = removeComma("" == $("#detail_harga_bphtb").val() ? 0 : $("#detail_harga_bphtb").val()),
            s = removeComma("" == $("#detail_harga_biaya_proses").val() ? 0 : $("#detail_harga_biaya_proses").val());
        a = t - removeComma("" == $("#detail_harga_kpr").val() ? 0 : $("#detail_harga_kpr").val()) - e + n + i + l + s, $("#detail_total_biaya").val(a).keyup()
    }
    let total_keu, cicilan_keu;

    function sum_tg(a = 0, t = "") {
        a = parseFloat(removeComma(a)), "" == t ? (total_keu = parseFloat(removeComma($("#mk-total_um").val()) || 0), cicilan_keu = parseFloat(removeComma($("#total_cicilan_um").val()) || 0)) : (total_keu = parseFloat(removeComma($("#mk-total_bb").val()) || 0), cicilan_keu = parseFloat(removeComma($("#total_cicilan_bb").val()) || 0)), cicilan_keu + a > total_keu && $("#nominal" + t).val(total_keu - cicilan_keu).keyup()
    }
    stage.on("touchmove", (function(a) {
        a.evt.preventDefault();
        var t = a.evt.touches[0],
            e = a.evt.touches[1];
        if (t && e) {
            stage.isDragging() && stage.stopDrag();
            var n = {
                    x: t.clientX,
                    y: t.clientY
                },
                i = {
                    x: e.clientX,
                    y: e.clientY
                };
            if (!lastCenter) return void(lastCenter = getCenter(n, i));
            var l = getCenter(n, i),
                s = getDistance(n, i);
            lastDist || (lastDist = s);
            var o = {
                    x: (l.x - stage.x()) / stage.scaleX(),
                    y: (l.y - stage.y()) / stage.scaleX()
                },
                r = stage.scaleX() * (s / lastDist);
            stage.scaleX(r), stage.scaleY(r);
            var d = l.x - lastCenter.x,
                _ = l.y - lastCenter.y,
                m = {
                    x: l.x - o.x * r + d,
                    y: l.y - o.y * r + _
                };
            group.scale({
                x: 1 / r,
                y: 1 / r
            }), stage.position(m), lastDist = s, lastCenter = l
        }
    })), stage.on("touchend", (function() {
        lastDist = 0, lastCenter = null
    })), siteplan.on("dblclick dbltap", (function(a) {
        if ($("#tambah_jalan").prop("checked")) return;
        $("#pilih-divisi option:selected").val();
        dtt = [], a = a.evt, allowDraw = !0, addMode = a.ctrlKey, downPoint = stage.getPointerPosition(), addMode || hapus_seleksi();
        let t = stage.getAbsoluteTransform().copy();
        t.invert();
        let e = t.point(downPoint),
            n = {
                x: parseInt(e.x, 10),
                y: parseInt(e.y, 10)
            };
        drawMask(n.x, n.y)
    })), $("#id_cluster").select2({
        placeholder: "Pilih Cluster",
        allowClear: !0,
        ajax: {
            url: base_url + "/cluster/getAll",
            dataType: "json",
            delay: 250,
            method: "post",
            data: function(a) {
                return {
                    [csrfName]: csrfHash,
                    search: a.term,
                    id_proyek: $("#id_proyek").val()
                }
            },
            processResults: function(a) {
                csrfHash = a.token;
                let t = [];
                return $.each(a.data, (function(a, e) {
                    t.push({
                        id: e[0],
                        text: e[3]
                    })
                })), {
                    results: t
                }
            },
            cache: !1
        }
    }), $("#id_cluster").on("change", (function(a) {
        $("#id_jalan").val(null).trigger("change"), this.value ? $("#id_jalan").prop("disabled", !1) : $("#id_jalan").prop("disabled", !0)
    })), $("#id_jalan").select2({
        placeholder: "Pilih Blok",
        allowClear: !0,
        ajax: {
            url: base_url + "/jalan/getAll",
            dataType: "json",
            delay: 250,
            method: "post",
            data: function(a) {
                return {
                    [csrfName]: csrfHash,
                    search: a.term,
                    id_cluster: $("#id_cluster").val(),
                    id_proyek: $("#id_proyek").val()
                }
            },
            processResults: function(a) {
                csrfHash = a.token;
                let t = [];
                return $.each(a.data, (function(a, e) {
                    t.push({
                        id: e[0],
                        text: e[3]
                    })
                })), {
                    results: t
                }
            },
            cache: !0
        }
    }), $("#id_tipe").select2({
        placeholder: "Pilih Tipe",
        allowClear: !0,
        ajax: {
            url: base_url + "/tipe/getAll",
            dataType: "json",
            delay: 250,
            method: "post",
            data: function(a) {
                return {
                    [csrfName]: csrfHash,
                    search: a.term,
                    id_proyek: $("#id_proyek").val()
                }
            },
            processResults: function(a) {
                csrfHash = a.token;
                let t = [];
                return $.each(a.data, (function(a, e) {
                    t.push({
                        id: e[0],
                        text: e[2] + "(" + e[3] + ")"
                    })
                })), {
                    results: t
                }
            },
            cache: !0
        }
    }), $("#status_tanah").select2(), $("#id_jenis").select2(), $("#id_jenis").change((function() {
        "" == this.value ? $(".h").hide() : "kavling" == this.value ? ($(".h").hide(), $("#div_kavling").show()) : "jalan" == this.value ? ($(".h").hide(), $("#div_jalan, #div_luas").show()) : "fasos" == this.value || "rth" == this.value ? ($(".h").hide(), $("#div_jalan, #div_fasos").show()) : $(".h").hide()
    })), $("#bayar_tagihan").change((function() {
        let a = removeComma($("#sisa_tagihan").val()),
            t = removeComma(this.value);
        t > a ? $("#bayar_tagihan").val(a).keyup() : $("#bayar_tagihan").val(t).keyup()
    })), $("#fm-keuangan #bayar_tagihan").change((function() {
        parseFloat(removeComma(this.value)) > parseFloat(removeComma($("#sisa_tagihan").val())) && $("#fm-keuangan #bayar_tagihan").val($("#sisa_tagihan").val())
    })), $("#mk-id").select2({
        placeholder: "Pilih Pricelist",
        allowClear: !0,
        ajax: {
            url: base_url + "/Hargajual/getAll",
            dataType: "json",
            delay: 250,
            method: "post",
            data: function(a) {
                return {
                    [csrfName]: csrfHash,
                    search: a.term,
                    id_proyek: $("#id_proyek").val()
                }
            },
            processResults: function(a) {
                csrfHash = a.token;
                let t = [];
                return $.each(a.data, (function(a, e) {
                    t.push({
                        id: e.id,
                        text: "Rp. " + num_format(e.hargajual) + " (" + e.tipe_rumah + "): " + e.tgl_harga,
                        row: e.row,
                        tipe: e.tipe_rumah,
                        lb: e.lb,
                        lt: e.lt,
                        hargajual: e.hargajual,
                        kpr: e.kpr,
                        uang_muka: e.uang_muka,
                        bphtb: e.bphtb,
                        biaya_adm: e.biaya_adm,
                        biaya_proses: e.biaya_proses,
                        id_tipe: e.id_tipe,
                        tgl_harga: format_date(e.tgl_harga)
                    })
                })), {
                    results: t
                }
            },
            cache: !1
        }
    }), $("#mk-id").on("select2:selecting", (function(a) {
        if (Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0) Swal.fire({
            title: "Lakukan perubahan?",
            text: "data pada tabel tagihan akan ter hapus!",
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1
        }).then((function(t) {
            if (!t.value) return !1;
            var e = a.params.args.data;
            $.each(e, (function(a, t) {
                $("#mk-" + a).val(t).change().keyup()
            })), sum_mktotal(), data_um = {}, data_bb = {}, $("#list_cicilan_here").html(""), $("#total_cicilan_um").val(0).change().keyup(), $("#total_cicilan_bb").val(0).change().keyup(), $("#id_list_keu").val(""), $("#id_list_keu_bb").val("")
        }));
        else {
            var t = a.params.args.data;
            $.each(t, (function(a, t) {
                $("#mk-" + a).val(t).change().keyup()
            })), sum_mktotal()
        }
    })), $("#mk-id").change((function() {
        this.value || $(".mk-fm").val("")
    })), $("#mk-harga_ppn, #mk-harga_penambahan, #mk-diskon").change((function(a) {
        Object.keys(data_um).length > 0 || Object.keys(data_bb).length > 0 ? Swal.fire({
            title: "Lakukan perubahan?",
            text: "data pada tabel tagihan akan ter hapus!",
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1
        }).then((function(a) {
            a.value && (sum_mktotal(), data_um = {}, data_bb = {}, $("#list_cicilan_here").html(""), $("#total_cicilan_um").val(0).change().keyup(), $("#total_cicilan_bb").val(0).change().keyup(), $("#id_list_keu").val(""), $("#id_list_keu_bb").val(""))
        })) : sum_mktotal()
    })), $("#isi_tagihan-modal").on("hidden.bs.modal", (function() {
        data_um = {}, data_bb = {}
    })), $("#add-form-isi-tagihan").click((function(a) {
        a.preventDefault()
    })), $("#fm-mkdt .num").change((function() {
        total()
    }));
    var it = 0;

    function tambah_(a = "") {
        let t = "_bb" == a ? a : "_um";
        return $("#total_cicilan" + t).val() == $("#mk-total" + t).val() ? (Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Tidak bisa menambahkan lagi form ",
            showConfirmButton: !1,
            timer: 1500
        }), !1) : $("#berita_acara" + a).val() && $("#nominal" + a).val() && $("#jatuh_tempo_tgl" + a).val() ? void Swal.fire({
            title: "Simpan data?",
            text: "Pastikan data sudah terisi dengan benar!",
            type: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1
        }).then((function(t) {
            t.value && tambah(a)
        })) : (Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Berita acara, nominal dan jatuh tempo tidak boleh kosong",
            showConfirmButton: !1,
            timer: 1500
        }), !1)
    }

    function tambah(a = "") {
        let t = "lk" + it;
        data_um[$("#id_list_keu" + a).val()] && (t = $("#id_list_keu" + a).val()), "" == a ? data_um[t] = {
            id_list_keu: t,
            id_keuangan: $("#id_keuangan").val(),
            berita_acara: $("#berita_acara").val(),
            nominal: $("#nominal").val(),
            jatuh_tempo_tgl: $("#jatuh_tempo_tgl").val()
        } : data_bb[t] = {
            id_list_keu_bb: t,
            id_keuangan_bb: $("#id_keuangan_bb").val(),
            berita_acara_bb: $("#berita_acara_bb").val(),
            nominal_bb: $("#nominal_bb").val(),
            jatuh_tempo_tgl_bb: $("#jatuh_tempo_tgl_bb").val()
        }, tambah_ketagihan(a), fp = flatpickr("#jatuh_tempo_tgl" + a, {
            altInput: !0,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d"
        });
        var e = new Date($("#jatuh_tempo_tgl" + a).val()).fp_incr(30);
        fp.setDate(e), it += 1
    }

    function removeFromTable(a, t = null) {
        Swal.fire({
            title: "Hapus Data?",
            text: "Data tidak bisa dipulihkan!",
            type: "danger",
            showCancelButton: !0,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya!",
            confirmButtonClass: "btn btn-primary",
            cancelButtonClass: "btn btn-danger ml-1",
            buttonsStyling: !1
        }).then((function(e) {
            e.value && ("_bb" == t ? delete data_bb[a] : delete data_um[a], tambah_ketagihan())
        }))
    }

    function editFromTable(a) {
        var t = data_um[a];
        $("#id_list_keu").val(a), $("#berita_acara").val(t.berita_acara), $("#nominal").val(t.nominal).keyup(), $("#jatuh_tempo_tgl").val(t.jatuh_tempo_tgl), $("#tambah_list").html("Simpan Perubahan")
    }

    function tambah_ketagihan(a = "") {
        $("#list_cicilan_here").html("");
        let t = "",
            e = 0,
            n = 0;
        for (var i in data_um) {
            if (data_um.hasOwnProperty(i)) t += "<tr>\n                        <td>" + (l = data_um[i]).berita_acara + "</td>\n                        <td>" + format_date(l.jatuh_tempo_tgl) + "</td>\n                        <td>" + l.nominal + '</td>\n                        <td>\n                            <div class="btn-group">\n                                \x3c!--<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="editFromTable(\'' + i + '\')"><i class="fa fa-edit"></i></button>--\x3e\n                                <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeFromTable(\'' + i + '\')"><i class="fa fa-trash"></i></button>\n                            </div>\n                        </td>\n                    </tr>', e += parseFloat(removeComma(l.nominal))
        }
        for (var i in t += "\n            <tr class='table-secondary'>\n                <td colspan='2'>Total Tagihan Uang Muka</td><td>" + num_format(e) + "</td><td></td>\n            </tr>", data_bb) {
            var l;
            if (data_bb.hasOwnProperty(i)) t += "<tr>\n                        <td>" + (l = data_bb[i]).berita_acara_bb + "</td>\n                        <td>" + format_date(l.jatuh_tempo_tgl_bb) + "</td>\n                        <td>" + l.nominal_bb + '</td>\n                        <td>\n                            <div class="btn-group">\n                                \x3c!--<button type="button" class="btn btn-outline-primary waves-effect btn-sm" onclick="editFromTable(\'' + i + '\')"><i class="fa fa-edit"></i></button>--\x3e\n                                <button type="button" class="btn btn-outline-danger waves-effect btn-sm" onclick="removeFromTable(\'' + i + "', '_bb')\"><i class=\"fa fa-trash\"></i></button>\n                            </div>\n                        </td>\n                    </tr>", n += parseFloat(removeComma(l.nominal_bb))
        }
        t += "\n            <tr class='table-secondary'>\n                <td colspan='2'>Total Tagihan Biaya biaya</td><td>" + num_format(n) + "</td><td></td>\n            </tr>", $("#list_cicilan_here").html(t), $("#total_cicilan_um").val(e).change().keyup(), $("#total_cicilan_bb").val(n).change().keyup(), $("#id_list_keu").val(""), $("#id_list_keu_bb").val(""), $("#nominal, #nominal_bb").change(), $("#tambah_list").html("+ Cicilan UM"), $("#tambah_list_bb").html("+ Cicilan BB")
    }

    function refresh_fmmkdt(a = !0) {
        $("#fm-mkdt")[0].reset(), $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop("disabled", a), $("#id_konsumen").val(""), $("#id_keuangan0").val("")
    }

    function delete_kons() {
        $("#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen").val(""), $("#id_konsumen, #id_mkdt").val(""), $("#mkdt_data_baru").val(1)
    }

    function open_mkdt(a, t, e) {
        "kavling" == a.data.tipe ? ($("#refresh_fmmkdt_div").addClass("hidden"), $("#delete_kons_div").addClass("hidden"), $("#fm-mkdt .num").prop("disabled", !1), $("#cicilan_belong_here").html(""), it = 0, $("#mkdt_data_baru").val(0), refresh_fmmkdt(!1), $("#fm-mkdt .num").val(0), $(".id_kavling").val(e), $("#id_mkdt").val(a.data.id_mkdt), $.ajax({
            url: base_url + "/mkdt/get_data_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_mkdt: a.data.id_mkdt,
                id_kavling: e
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden")
            },
            success: function(e) {
                $("#loading").addClass("hidden"), csrfHash = e.token;
                let n = e.data,
                    i = e.hj;
                if (i.hargajual && ($.each(i, (function(a, t) {
                        $("#mkdt-" + a).val(t).change().keyup()
                    })), $("#mkdt-tgl_harga").val(format_date(i.tgl_harga)), $("#fm-mkdt #harga_kpr").val(i.kpr).change()), n) {
                    "Batal" == n.status_mkdt && (refresh_fmmkdt(!0), $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass("hidden"), $("#delete_kons_div").addClass("hidden"));
                    for (let a in n) "perintah_bangun" != a && "wawancara" != a && "akad" != a && "sp3k" != a && $("#fm-mkdt #" + a).val(n[a]);
                    $("#fm-mkdt #mkdt_keterangan").val(n.keterangan), $("#fm-mkdt #acc_harga_kpr").val(n.harga_kpr_acc).change(), $("#fm-mkdt #harga_turun_kpr").val(n.harga_penambahan_um).change(), 1 == n.perintah_bangun && $("#perintah_bangun").prop("checked", !0), 1 == n.wawancara && $("#wawancara").prop("checked", !0), 1 == n.sp3k && $("#sp3k").prop("checked", !0), 1 == n.akad && $("#akad").prop("checked", !0), $("#fm-mkdt #perintah_bangun_oleh").val(n.perintah_bangun_user), "0000-00-00" != n.perintah_bangun_tgl && document.querySelector("#perintah_bangun_tgl")._flatpickr.setDate(n.perintah_bangun_tgl), "0000-00-00" != n.booking_tgl && document.querySelector("#booking_tgl")._flatpickr.setDate(n.booking_tgl), "0000-00-00" != n.wawancara_tgl && document.querySelector("#wawancara_tgl")._flatpickr.setDate(n.wawancara_tgl), "0000-00-00" != n.sp3k_tgl && document.querySelector("#sp3k_tgl")._flatpickr.setDate(n.sp3k_tgl), "0000-00-00" != n.sp3k_tgl_exp && document.querySelector("#sp3k_tgl_exp")._flatpickr.setDate(n.sp3k_tgl_exp), "0000-00-00" != n.rencana_akad_tgl && document.querySelector("#rencana_akad_tgl")._flatpickr.setDate(n.rencana_akad_tgl), "0000-00-00" != n.akad_tgl && document.querySelector("#akad_tgl")._flatpickr.setDate(n.akad_tgl), $("#fm-mkdt .num").keyup().change(), $("#status_mkdt").change(), $("#mkdt_keterangan").val(n.keterangan)
                }
                $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#modal_divisi" + t).modal({
                    backdrop: "static",
                    keyboard: !1
                })
            },
            error: function(a) {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: !0
                }).then((function() {
                    location.reload()
                }))
            }
        })) : Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Tidak ada kavling terpilih ",
            showConfirmButton: !0
        })
    }

    function save_mkdt(a) {
        if (palid("fm-mkdt #nama_konsumen", "", "nama konsumen harus diisi") && palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) {
            for (let a = 0; a <= it; a++) {
                if (!palid("fm-mkdt #jatuh_tempo_tgl" + a, "", "Tanggal jatuh tempo harus diisi")) return !1;
                if (!palid("fm-mkdt #berita_acara1" + a, "", "Keterangan harus diisi")) return !1
            }
            $.ajax({
                url: base_url + "/mkdt/save",
                type: "post",
                data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
                dataType: "json",
                beforeSend: function() {
                    $("#add-form-btn-mkdt").prop("disabled", !0), $("#add-form-btn-mkdt").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
                },
                success: function(a) {
                    csrfHash = a.token, !0 === a.success ? Swal.fire({
                        position: "bottom-end",
                        icon: "success",
                        title: a.messages,
                        showConfirmButton: !1,
                        timer: 1500
                    }).then((function() {
                        $(".modal").modal("hide"), $("#add-form-btn-mkdt").html("Simpan"), $("#add-form-btn-mkdt").prop("disabled", !1)
                    })) : Swal.fire({
                        position: "bottom-end",
                        icon: "error",
                        title: a.messages,
                        showConfirmButton: !1,
                        timer: 1500
                    }).then((function() {
                        $("#add-form-btn-mkdt").html("Simpan"), $("#add-form-btn-mkdt").prop("disabled", !1)
                    })), load_kavling(), hapus_seleksi()
                }
            })
        }
    }

    function open_legal(a, t, e) {
        return "kavling" == a.data.tipe ? open_flegal(a, t, e) : open_fotherlegal(a)
    }

    function open_flegal(a, t, e) {
        $("#fm-legal")[0].reset(), $(".id_kavling").val(e), $("#id_legal").val(a.data.id_legal), $.ajax({
            url: base_url + "/legal/get_data_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_legal: a.data.id_legal
            },
            dataType: "json",
            success: function(a) {
                if (csrfHash = a.token, a) {
                    for (let t in a) $("#" + t).val(a[t]);
                    "0000-00-00" != a.sertifikat_tgl && document.querySelector("#sertifikat_tgl")._flatpickr.setDate(a.sertifikat_tgl), "0000-00-00" != a.sertifikat_masa_berlaku && document.querySelector("#sertifikat_masa_berlaku")._flatpickr.setDate(a.sertifikat_masa_berlaku), "0000-00-00" != a.imb_tgl && document.querySelector("#imb_tgl")._flatpickr.setDate(a.imb_tgl), "0000-00-00" != a.bphtb_tgl && document.querySelector("#bphtb_tgl")._flatpickr.setDate(a.bphtb_tgl), "0000-00-00" != a.bphtb_masa_berlaku && document.querySelector("#bphtb_masa_berlaku")._flatpickr.setDate(a.bphtb_masa_berlaku), "0000-00-00" != a.bphtb_validasi && document.querySelector("#bphtb_validasi")._flatpickr.setDate(a.bphtb_validasi), "0000-00-00" != a.akad_tgl && document.querySelector("#legal_akad_tgl")._flatpickr.setDate(a.akad_tgl), $("#legal_keterangan").val(a.keterangan)
                }
            }
        }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + a.data.tipe + "<span>: " + a.data.nama_jalan), $("#modal_flegal").modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function open_fotherlegal(a) {
        $("#fm-fotherlegal")[0].reset(), $("#fl_progres_jalan").val(0), $(".t_luas_planning .t_luas_produksi, .r_progres").html(" "), $.ajax({
            url: base_url + "/siteplan/get_others",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: editdtt[0].id.substr(6)
            },
            dataType: "json",
            success: function(a) {
                if (csrfHash = a.token, a.data) {
                    let t = a.data[0],
                        e = t.progres ? t.progres : 0;
                    $(".id_kavling").val(t.id), $(".t_luas_planning, .t_luas_produksi").html("-"), t.planning_luas && $(".t_luas_planning").html(t.planning_luas + "  m&sup2  (" + t.planning_edit + ": " + format_datetime(t.planning_updated_at) + ")"), t.produksi_luas && $(".t_luas_produksi").html(t.produksi_luas + "  m&sup2  (" + t.produksi_edit + ": " + format_datetime(t.produksi_updated_at) + ")"), $("#f_legal_luas").val(t.legal_luas), $("#f_legal_keterangan").val(t.legal_keterangan), $("#fl_progres_jalan").val(e), $(".r_progres").html(e)
                }
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + a.data.tipe + "<span>: " + a.data.nama_jalan), $("#modal_fotherlegal").modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function save_legal() {
        $.ajax({
            url: base_url + "/legal/save",
            type: "post",
            data: $("#fm-legal").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn-legal").prop("disabled", !0), $("#add-form-btn-legal").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $(".modal").modal("hide"), $("#add-form-btn-legal").html("Simpan"), $("#add-form-btn-legal").prop("disabled", !1)
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn-legal").html("Simpan"), $("#add-form-btn-legal").prop("disabled", !0)
                })), load_kavling(), hapus_seleksi()
            }
        })
    }

    function save_fotherlegal() {
        $.ajax({
            url: base_url + "/legal/edit_others",
            type: "POST",
            data: $("#fm-fotherlegal").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#save-fother-btn-legal").prop("disabled", !0), $("#save-fother-btn-legal").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? (Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $(".modal").modal("hide"), hapus_seleksi(), load_kavling()) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#save-fother-btn-legal").html("Simpan"), $("#save-fother-btn-legal").prop("disabled", !1)
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat menginput data",
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#save-fother-btn-legal").html("Simpan"), $("#save-fother-btn-legal").prop("disabled", !1)
            }
        })
    }
    $("#status_mkdt").change((function() {
        "Batal" == $("#status_mkdt option:selected").val() ? $("#show_keterangan_batal").removeClass("hidden") : $("#show_keterangan_batal").addClass("hidden")
    })), $("#fm-mkdt #harga_kpr, #fm-mkdt #acc_harga_kpr").change((function() {
        "0" != $("#fm-mkdt #acc_harga_kpr").val() ? $("#fm-mkdt #harga_turun_kpr").val(parseFloat(removeComma($("#fm-mkdt #harga_kpr").val())) - parseFloat(removeComma($("#fm-mkdt #acc_harga_kpr").val()))).change() : $("#fm-mkdt #harga_turun_kpr").val(0)
    })), $("#wawancara").change((function() {
        $("#wawancara").prop("checked") || document.querySelector("#wawancara_tgl")._flatpickr.setDate(null)
    })), $("#refresh_fmmkdt_btn").click((function() {
        refresh_fmmkdt(!1), $("#mkdt_data_baru").val(1)
    })), $("#add-form-btn-mkdt").click((function(a) {
        a.preventDefault()
    }));
    var slo = 0,
        bp = 0,
        jalan = 0,
        lpa = 0,
        tot = 0,
        saluran = 0,
        pondasi = 0,
        topping_off = 0,
        naik_dinding = 0,
        finishing = 0;

    function ftot() {
        return tot = slo + bp + jalan + lpa + saluran + pondasi + topping_off + naik_dinding + finishing
    }

    function cekstprod() {
        $("#pondasi").prop("checked") && $("#naik_dinding").prop("checked") && $("#topping_off").prop("checked") && $("#finishing").prop("checked") && $("#saluran").prop("checked") && $("#jalan").prop("checked") ? $(".af .cbp").prop("disabled", !1) : $(".af .cbp").prop("disabled", !0)
    }

    function save_produksi() {
        $.ajax({
            url: base_url + "/produksi/save",
            type: "post",
            data: $("#fm-produksi").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#add-form-btn-produksi").prop("disabled", !0), $("#add-form-btn-produksi").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $(".modal").modal("hide"), $("#add-form-btn-produksi").html("Simpan"), $("#add-form-btn-produksi").prop("disabled", !1)
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#add-form-btn-produksi").html("Simpan"), $("#add-form-btn-produksi").prop("disabled", !1)
                })), load_kavling(), hapus_seleksi()
            }
        })
    }

    function open_komplain_produksi() {
        if (editdtt[0]) {
            var a = editdtt[0],
                t = a.id.substr(3);
            a.data2.id_komplain ? ($("#fm-komplain-produksi")[0].reset(), $("#fm-komplain-produksi #foto_komplain_sales, #fm-komplain-produksi #foto_komplain_produksi").html(""), $(".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi").addClass("hidden", !0), $("#keterangan_ditangani").prop("readonly", !1), $("#komplain-produksi-form-btn").prop("disabled", !1), $("#terima_komplain, #is_selesai_produksi").attr("onclick", ""), $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", !1), $("#fm-komplain-produksi #selesai_keterangan_produksi").prop("disabled", !1), $("#komplain_selesai_sip").addClass("hidden"), $("#last_update_komplain_produksi").html("Terakhir diupdate oleh: -, pada: -"), $(".id_kavling").val(t), $("#fm-komplain-produksi #id_komplain").val(a.data2.id_komplain), $.ajax({
                url: base_url + "/produksi/get_data_komplain_by_id",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    id_komplain: a.data2.id_komplain,
                    id_kavling: t
                },
                dataType: "json",
                success: function(t) {
                    csrfHash = t.token;
                    let e, n, i = t.komplain,
                        l = "",
                        s = "";
                    if (i) {
                        if (e = i.upload_komplain_sales_urls || [], Array.isArray(e)) {
                            let a = "active";
                            for (let t = 0; t < e.length; t++) t > 0 && (a = ""), l += '<div class="carousel-item ' + a + '"><img class="d-block w-100 ft_kom" src="' + e[t] + '" alt="First slide"></div>'
                        }
                        if ($("#fm-komplain-produksi #foto_komplain_sales").html(l), n = i.upload_komplain_produksi_urls || [], Array.isArray(n)) {
                            let a = "active";
                            for (let t = 0; t < n.length; t++) t > 0 && (a = ""), s += '<div class="carousel-item ' + a + '"><img class="d-block w-100 ft_kom" src="' + n[t] + '" alt="First slide"></div>'
                        }
                        $("#fm-komplain-produksi #foto_komplain_produksi").html(s), $("#fm-komplain-produksi #keterangan_komplain").val(i.keterangan_komplain), $("#fm-komplain-produksi #username_komplain_oleh").val(i.username_komplain_oleh), "0000-00-00" != i.komplain_tgl && document.querySelector("#fm-komplain-produksi #komplain_tgl")._flatpickr.setDate(i.komplain_tgl), $("#last_update_komplain_produksi").html("Terakhir diupdate oleh: " + i.username_last_update + ", pada: " + format_datetime(i.updated_at)), 2 == i.status_komplain ? ($("#terima_komplain").attr("onclick", "return false;"), $("#terima_komplain").prop("checked", !0), $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden"), $("#fm-komplain-produksi #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-produksi #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl)) : 3 == i.status_komplain ? ($("#terima_komplain").attr("onclick", "return false;"), $("#terima_komplain").prop("checked", !0), $("#is_selesai_produksi").attr("onclick", "return false;"), $("#is_selesai_produksi").prop("checked", !0), $("#keterangan_ditangani").prop("readonly", !0), $(".ditangani_form, #selesaikan_komplain_div").removeClass("hidden"), $("#fm-komplain-produksi #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-produksi #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl), $("#fm-komplain-produksi #selesai_keterangan_produksi").val(i.selesai_keterangan_produksi), $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(i.username_selesai_oleh_produksi), "0000-00-00" != i.selesai_tgl_produksi && document.querySelector("#fm-komplain-produksi #selesai_tgl_produksi")._flatpickr.setDate(i.selesai_tgl_produksi)) : 4 == i.status_komplain && ($("#terima_komplain").attr("onclick", "return false;"), $("#terima_komplain").prop("checked", !0), $("#is_selesai_produksi").attr("onclick", "return false;"), $("#is_selesai_produksi").prop("checked", !0), $("#komplain-produksi-form-btn").prop("disabled", !0), $("#keterangan_ditangani").prop("readonly", !0), $("#fm-komplain-produksi #selesai_keterangan_produksi").prop("disabled", !0), $("#fm-komplain-produksi #keterangan_ditangani").prop("disabled", !0), $(".ditangani_form, #selesaikan_komplain_div, #komplain_selesai_btn_produksi").removeClass("hidden"), $("#fm-komplain-produksi #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-produksi #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-produksi #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl), $("#fm-komplain-produksi #selesai_keterangan_produksi").val(i.selesai_keterangan_produksi), $("#fm-komplain-produksi #username_selesai_oleh_produksi").val(i.username_selesai_oleh_produksi), "0000-00-00" != i.selesai_tgl_produksi && document.querySelector("#fm-komplain-produksi #selesai_tgl_produksi")._flatpickr.setDate(i.selesai_tgl_produksi), $("#komplain_selesai_sip").removeClass("hidden"), $("#fm-komplain-produksi #selesai_keterangan_sales").val(i.selesai_keterangan_sales), $("#fm-komplain-produksi #username_selesai_oleh_sales").val(i.username_selesai_oleh_sales), "0000-00-00" != i.selesai_tgl_sales && document.querySelector("#fm-komplain-produksi #selesai_tgl_sales")._flatpickr.setDate(i.selesai_tgl_sales))
                    }
                    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#modal_komplain_produksi").modal({
                        backdrop: "static",
                        keyboard: !1
                    })
                },
                error: function() {}
            })) : Swal.fire({
                position: "bottom-end",
                icon: "error",
                title: "Tidak ada komplain",
                showConfirmButton: !1,
                timer: 1500
            })
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Pilih salahsatu kavling",
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_komplain_produksi() {
        $("#upload_komplain_sales")[0].files;
        var a = $("#fm-komplain-produksi")[0],
            t = new FormData(a);
        t.append(csrfName, csrfHash), $.ajax({
            url: base_url + "/produksi/save_komplain_produksi",
            type: "POST",
            contentType: !1,
            processData: !1,
            data: t,
            dataType: "json",
            beforeSend: function() {
                $("#komplain-produksi-form-btn").prop("disabled", !0), $("#komplain-produksi-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? (Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $(".modal").modal("hide"), hapus_seleksi(), load_kavling()) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#komplain-produksi-form-btn").html("Simpan"), $("#komplain-produksi-form-btn").prop("disabled", !1)
            }
        })
    }

    function open_produksi(a, t, e) {
        return "kavling" == a.data.tipe ? open_fproduksi(a, t, e) : open_fotherproduksi(a)
    }

    function save_fotherproduksi() {
        $.ajax({
            url: base_url + "/produksi/edit_others",
            type: "POST",
            data: $("#fm-fotherproduksi").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#save_fotherproduksi-btn").prop("disabled", !0), $("#save_fotherproduksi-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? (Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $(".modal").modal("hide"), hapus_seleksi(), load_kavling()) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#save_fotherproduksi-btn").html("Simpan"), $("#save_fotherproduksi-btn").prop("disabled", !1)
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat menginput data",
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#save_fotherproduksi-btn").html("Simpan"), $("#save_fotherproduksi-btn").prop("disabled", !1)
            }
        })
    }

    function open_fotherproduksi(a) {
        $("#fm-fotherproduksi")[0].reset(), $("#f_progres_jalan").val(0), $(".t_luas_legal, .t_luas_produksi, .r_progres").html(" "), $.ajax({
            url: base_url + "/siteplan/get_others",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: editdtt[0].id.substr(6)
            },
            dataType: "json",
            success: function(a) {
                if (csrfHash = a.token, a.data) {
                    let t = a.data[0],
                        e = t.progres ? t.progres : 0;
                    $(".id_kavling").val(t.id), $(".t_luas_legal, .t_luas_produksi").html("-"), t.planning_luas && $(".t_luas_planning").html(t.planning_luas + "  m&sup2  (" + t.planning_edit + ": " + format_datetime(t.planning_updated_at) + ")"), t.legal_luas && $(".t_luas_legal").html(t.legal_luas + "  m&sup2  (" + t.legal_edit + ": " + format_datetime(t.legal_updated_at) + ")"), $("#f_produksi_luas").val(t.produksi_luas), $("#f_produksi_keterangan").val(t.produksi_keterangan), $("#f_progres_jalan").val(e), $(".r_progres").html(e)
                }
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/> <span class='capitalize'>" + a.data.tipe + "<span>: " + a.data.nama_jalan), $("#modal_fothersproduksi").modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function open_fproduksi(a, t, e) {
        pondasi = 0, topping_off = 0, naik_dinding = 0, finishing = 0, slo = 0, bp = 0, jalan = 0, lpa = 0, tot = 0, saluran = 0, $(".af .cbp").prop("disabled", !0), $("#t_progres_bangunan").html("0"), $("#fm-produksi")[0].reset(), $("#last_update_checklist_prod").html("Terakhir diupdate oleh: -, pada: -"), $(".id_kavling").val(e), $("#id_produksi").val(a.data.id_produksi), $("#download_gambar_kerja").attr("onclick", "download(" + a.data2.id_gambar_kerja + ")"), $.ajax({
            url: base_url + "/produksi/get_data_by_id",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_produksi: a.data.id_produksi,
                id_kavling: e
            },
            dataType: "json",
            success: function(a) {
                csrfHash = a.token;
                let t = a.cl;
                if (a) {
                    if (a.progres_bangunan && $("#progres_bangunan").val(a.progres_bangunan), pondasi = 1 == a.pondasi ? 1 : 0, $("#pondasi").prop("checked", pondasi).change(), topping_off = 1 == a.topping_off ? 1 : 0, $("#topping_off").prop("checked", topping_off).change(), naik_dinding = 1 == a.naik_dinding ? 1 : 0, $("#naik_dinding").prop("checked", naik_dinding).change(), finishing = 1 == a.finishing ? 1 : 0, $("#finishing").prop("checked", finishing).change(), jalan = 1 == a.jalan ? 1 : 0, $("#jalan").prop("checked", jalan).change(), slo = 1 == a.slo ? 1 : 0, $("#slo").prop("checked", slo).change(), bp = 1 == a.bp ? 1 : 0, $("#bp").prop("checked", bp).change(), lpa = 1 == a.lpa ? 1 : 0, $("#lpa").prop("checked", lpa).change(), saluran = 1 == a.saluran ? 1 : 0, $("#saluran").prop("checked", saluran).change(), t) {
                        if (0 == t.length) return;
                        let a = t[0].produksi_cek_tgl;
                        $.each(t, (function(t, e) {
                            1 == e.hasil_cek_t && $("#hasil_cek_t\\[" + e.id_subitem + "\\]").prop("checked", !0), 1 == e.hasil_cek_f && $("#hasil_cek_f\\[" + e.id_subitem + "\\]").prop("checked", !0), 1 == e.hasil_cek_v && $("#hasil_cek_v\\[" + e.id_subitem + "\\]").prop("checked", !0), $("#keterangan_cek_produksi\\[" + e.id_subitem + "\\]").val(e.keterangan_cek_produksi), a < e.produksi_cek_tgl && (a = e.produksi_cek_tgl)
                        })), $("#last_update_checklist_prod").html("Terakhir diupdate oleh: " + t[0].username + ", pada: " + format_date(a))
                    }
                    $("#produksi_keterangan").val(a.keterangan)
                }
            },
            error: function() {
                Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: "Terjadi kesalahan saat memuat data",
                    showConfirmButton: !1,
                    timer: 1500
                })
            }
        }), $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#modal_divisi" + t).modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function download(a) {
        (async () => {
            await fetch(base_url + "/produksi/get_gambarkerja", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    [csrfName]: csrfHash,
                    id_gambar_kerja: a,
                    pass: "password"
                })
            }).then((a => a.blob())).then((a => {
                const t = window.URL.createObjectURL(a),
                    e = document.createElement("a");
                e.style.display = "none", e.href = t, e.download = "gambar kerja.pdf", document.body.appendChild(e), e.click(), window.URL.revokeObjectURL(t)
            })).catch((() => alert("oh no!")))
        })()
    }

    function open_checklist_sales() {
        if (editdtt[0]) {
            var a = editdtt[0],
                t = a.id.substr(3);
            $("#fm-checklist-sales")[0].reset(), $("#last_update_checklist_prod2").html("Terakhir diupdate oleh: -, pada: -"), $("#last_update_checklist_sales").html("Terakhir diupdate oleh: -, pada: -"), $(".id_kavling").val(t), $("#id_sales").val(a.data.id_sales), $.ajax({
                url: base_url + "/sales/get_data_by_id",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    id_sales: a.data.id_sales,
                    id_kavling: t
                },
                dataType: "json",
                success: function(t) {
                    csrfHash = t.token;
                    let e = t.cl;
                    if (1 == t.kav.is_checked && $("#is_checked").prop("checked", !0), e.length > 0) {
                        let a = e[0].produksi_cek_tgl,
                            t = e[0].sales_cek_tgl;
                        $.each(e, (function(e, n) {
                            1 == n.hasil_cek_t && $("#hasil_cek_t\\[" + n.id_subitem + "\\]").prop("checked", !0), 1 == n.hasil_cek_f && $("#hasil_cek_f\\[" + n.id_subitem + "\\]").prop("checked", !0), 1 == n.hasil_cek_v && $("#hasil_cek_v\\[" + n.id_subitem + "\\]").prop("checked", !0), $("#keterangan_cek_produksi\\[" + n.id_subitem + "\\]").val(n.keterangan_cek_produksi), 1 == n.hasil_cek_t_s && $("#hasil_cek_t_s\\[" + n.id_subitem + "\\]").prop("checked", !0), 1 == n.hasil_cek_f_s && $("#hasil_cek_f_s\\[" + n.id_subitem + "\\]").prop("checked", !0), 1 == n.hasil_cek_v_s && $("#hasil_cek_v_s\\[" + n.id_subitem + "\\]").prop("checked", !0), $("#keterangan_cek_sales\\[" + n.id_subitem + "\\]").val(n.keterangan_cek_sales), a < n.produksi_cek_tgl && (a = n.produksi_cek_tgl), t < n.sales_cek_tgl && (t = n.sales_cek_tgl)
                        })), $("#last_update_checklist_prod2").html("Terakhir diupdate (produksi) oleh: " + e[0].username_prod + ", pada: " + format_date(a)), $("#last_update_checklist_sales").html("Terakhir diupdate (sales) oleh: " + e[0].username_sales + ", pada: " + format_date(t))
                    }
                    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#checklist_modal_sales").modal({
                        backdrop: "static",
                        keyboard: !1
                    })
                },
                error: function() {}
            })
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Pilih salahsatu kavling",
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_checklist_sales() {
        $.ajax({
            url: base_url + "/sales/save_checklist",
            type: "post",
            data: $("#fm-checklist-sales").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#checklist-form-btn-sales").prop("disabled", !0), $("#checklist-form-btn-sales").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $(".modal").modal("hide"), $("#checklist-form-btn-sales").html("Simpan"), $("#checklist-form-btn-sales").prop("disabled", !1)
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#checklist-form-btn-sales").html("Simpan"), $("#checklist-form-btn-sales").prop("disabled", !1)
                })), load_kavling(), hapus_seleksi()
            }
        })
    }
    $("#pondasi").change((function() {
        pondasi = this.checked ? 1 : 0
    })), $("#naik_dinding").change((function() {
        naik_dinding = this.checked ? 1 : 0
    })), $("#topping_off").change((function() {
        topping_off = this.checked ? 1 : 0
    })), $("#finishing").change((function() {
        finishing = this.checked ? 1 : 0
    })), $("#slo").change((function() {
        slo = this.checked ? 1 : 0
    })), $("#saluran").change((function() {
        saluran = this.checked ? 1 : 0
    })), $("#bp").change((function() {
        bp = this.checked ? 1 : 0
    })), $("#jalan").change((function() {
        jalan = this.checked ? 1 : 0
    })), $("#lpa").change((function() {
        lpa = this.checked ? 1 : 0
    })), $(".cbp").change((function() {
        ftot(), cekstprod(), tot = tot / 9 * 100, $("#progres_bangunan").val(tot.toFixed(2)), $("#t_progres_bangunan").html(tot.toFixed(2))
    })), $("#terima_komplain").change((function() {
        this.checked ? $("#terima_komplain_div").removeClass("hidden") : $("#terima_komplain_div").addClass("hidden", !0)
    })), $("#save_fotherproduksi-btn").click((function(a) {
        a.preventDefault()
    }));
    let fuks = document.getElementById("upload_komplain_sales"),
        fluks = [],
        flduks = "";

    function open_komplain_sales() {
        if (editdtt[0]) {
            $("#fm-komplain-sales #foto_komplain_sales").html(""), $("#list_upload_komplain_sales").html(""), $("#label_upload_komplain_sales").html("Bisa Upload lebih dari 1 foto");
            var a = editdtt[0],
                t = a.id.substr(3);
            $("#fm-komplain-sales")[0].reset(), $("#last_update_komplain_sales").html("Terakhir diupdate oleh: -, pada: -"), $("#batal_komplain_btn").addClass("btn-outline-danger"), $("#batal_komplain_btn").removeClass("btn-outline-primary"), $("#batal_komplain_btn").html("Batalkan Komplain"), $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").addClass("hidden", !0), $("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop("disabled", !1), $(".id_kavling").val(t), $("#fm-komplain-sales #id_komplain").val(a.data2.id_komplain), $.ajax({
                url: base_url + "/sales/get_data_komplain_by_id",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    id_komplain: a.data2.id_komplain,
                    id_kavling: t
                },
                dataType: "json",
                success: function(t) {
                    csrfHash = t.token;
                    let e, n, i = t.komplain,
                        l = "",
                        s = "";
                    if (i) {
                        if (e = i.upload_komplain_sales_urls || [], Array.isArray(e)) {
                            let a = "active";
                            for (let t = 0; t < e.length; t++) t > 0 && (a = ""), l += '<div class="carousel-item ' + a + '"><img class="d-block w-100 ft_kom" src="' + e[t] + '" alt="First slide"></div>'
                        }
                        if ($("#fm-komplain-sales #foto_komplain_sales").html(l), n = i.upload_komplain_produksi_urls || [], Array.isArray(n)) {
                            let a = "active";
                            for (let t = 0; t < n.length; t++) t > 0 && (a = ""), s += '<div class="carousel-item ' + a + '"><img class="d-block w-100 ft_kom" src="' + n[t] + '" alt="First slide"></div>'
                        }
                        $("#fm-komplain-sales #foto_komplain_produksi").html(s), 1 == i.status_komplain ? $("#batal_komplain").removeClass("hidden", !0) : 2 == i.status_komplain ? ($("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain").prop("disabled", !0), $("#batal_komplain").removeClass("hidden", !0), $("#komplain_ditangani_sales").removeClass("hidden", !0), $("#fm-komplain-sales #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-sales #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl)) : 3 == i.status_komplain ? ($("#batal_komplain_btn, #fm-komplain-sales #keterangan_komplain").prop("disabled", !0), $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass("hidden", !0), $("#fm-komplain-sales #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-sales #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl), $("#fm-komplain-sales #selesai_keterangan_produksi").val(i.selesai_keterangan_produksi), $("#fm-komplain-sales #username_selesai_oleh_produksi").val(i.username_selesai_oleh_produksi), "0000-00-00" != i.selesai_tgl_produksi && document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(i.selesai_tgl_produksi)) : 4 == i.status_komplain && ($("#batal_komplain_btn, #komplain-sales-form-btn, #fm-komplain-sales #keterangan_komplain, #fm-komplain-sales #selesai_keterangan_sales").prop("disabled", !0), $("#batal_komplain, #selesaikan_komplain_div_sales, #komplain_ditangani_sales").removeClass("hidden", !0), $("#batal_komplain_btn").html("Komplain Selesai"), $("#batal_komplain_btn").removeClass("btn-outline-danger"), $("#batal_komplain_btn").addClass("btn-outline-primary"), $("#fm-komplain-sales #keterangan_ditangani").val(i.keterangan_ditangani), $("#fm-komplain-sales #username_ditangani_oleh").val(i.username_ditangani_oleh), "0000-00-00" != i.ditangani_tgl && document.querySelector("#fm-komplain-sales #ditangani_tgl")._flatpickr.setDate(i.ditangani_tgl), $("#fm-komplain-sales #selesai_keterangan_produksi").val(i.selesai_keterangan_produksi), $("#fm-komplain-sales #username_selesai_oleh_produksi").val(i.username_selesai_oleh_produksi), "0000-00-00" != i.selesai_tgl_produksi && document.querySelector("#fm-komplain-sales #selesai_tgl_produksi")._flatpickr.setDate(i.selesai_tgl_produksi), $("#fm-komplain-sales #selesai_keterangan_sales").val(i.selesai_keterangan_sales), $("#fm-komplain-sales #username_selesai_oleh_sales").val(i.username_selesai_oleh_sales), "0000-00-00" != i.selesai_tgl_produksi && document.querySelector("#fm-komplain-sales #selesai_tgl_sales")._flatpickr.setDate(i.selesai_tgl_sales)), $("#fm-komplain-sales #keterangan_komplain").val(i.keterangan_komplain), $("#fm-komplain-sales #username_komplain_oleh").val(i.username_komplain_oleh), "0000-00-00" != i.komplain_tgl && document.querySelector("#fm-komplain-sales #komplain_tgl")._flatpickr.setDate(i.komplain_tgl), $("#last_update_komplain_sales").html("Terakhir diupdate oleh: " + i.username_last_update + ", pada: " + format_datetime(i.updated_at))
                    }
                    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#modal_komplain_sales").modal({
                        backdrop: "static",
                        keyboard: !1
                    })
                },
                error: function() {}
            })
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Pilih salahsatu kavling",
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_komplain_sales() {
        $("#upload_komplain_sales")[0].files;
        var a = $("#fm-komplain-sales")[0],
            t = new FormData(a);
        t.append(csrfName, csrfHash), $.ajax({
            url: base_url + "/sales/save_komplain_sales",
            type: "POST",
            contentType: !1,
            processData: !1,
            data: t,
            dataType: "json",
            beforeSend: function() {
                $("#komplain-sales-form-btn").prop("disabled", !0), $("#komplain-sales-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? (Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $(".modal").modal("hide"), hapus_seleksi(), load_kavling()) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#komplain-sales-form-btn").html("Simpan"), $("#komplain-sales-form-btn").prop("disabled", !1)
            }
        })
    }

    function batal_komplain() {
        Swal.fire({
            title: "Apakah anda yakin akan membatalkan komplain?",
            text: "Data komplain akan terhapus dan tidak bisa dikembalikan",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel"
        }).then((a => {
            a.value && $.ajax({
                url: base_url + "/sales/batalkan_komplain",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    id_kavling: $(".id_kavling").val(),
                    id_komplain: $("#id_komplain").val()
                },
                dataType: "json",
                success: function(a) {
                    csrfHash = a.token, !0 === a.success ? Swal.fire({
                        position: "bottom-end",
                        icon: "success",
                        title: a.messages,
                        showConfirmButton: !1,
                        timer: 1500
                    }).then((function() {
                        load_kavling(), hapus_seleksi(), $(".modal").modal("hide")
                    })) : Swal.fire({
                        position: "bottom-end",
                        icon: "error",
                        title: a.messages,
                        showConfirmButton: !1,
                        timer: 1500
                    })
                }
            })
        }))
    }

    function open_serah_terima() {
        if (editdtt[0]) {
            var a = editdtt[0],
                t = a.id.substr(3);
            $("#fm-serah-terima")[0].reset(), $("#last_update_serah_terima").html("Terakhir diupdate oleh: -, pada: -"), $(".id_kavling").val(t), $("#is_serah_terima").val(a.data2.id_serah_terima), $.ajax({
                url: base_url + "/sales/get_data_serah_terima_by_id",
                type: "post",
                data: {
                    [csrfName]: csrfHash,
                    is_serah_terima: a.data2.is_serah_terima,
                    id_kavling: t
                },
                dataType: "json",
                success: function(t) {
                    csrfHash = t.token;
                    let e = t.serah_terima;
                    if (e) {
                        1 == e.is_serah_terima && $("#is_serah_terima").prop("checked", !0);
                        for (let a in e) "is_serah_terima" != a && $("#fm-serah-terima #" + a).val(e[a]);
                        "0000-00-00" != e.serah_terima_tgl && document.querySelector("#serah_terima_tgl")._flatpickr.setDate(e.serah_terima_tgl), $("#fm-serah-terima #is_serah_terima").val(1), $("#last_update_serah_terima").html("Terakhir diupdate oleh: " + e.username + ", pada: " + format_date(e.updated_at))
                    }
                    $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + a.data.nama_jalan + ", No." + a.data.no_kavling + "<br/>" + a.data2.no_tipe_rumah + " (" + a.data2.tipe_rumah + ")<br/>"), $("#modal_serah_terima").modal({
                        backdrop: "static",
                        keyboard: !1
                    })
                },
                error: function() {}
            })
        } else Swal.fire({
            position: "bottom-end",
            icon: "error",
            title: "Pilih salahsatu kavling",
            showConfirmButton: !1,
            timer: 1500
        })
    }

    function save_serah_terima() {
        $.ajax({
            url: base_url + "/sales/save_serah_terima",
            type: "post",
            data: $("#fm-serah-terima").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#serah-terima-form-btn").prop("disabled", !0), $("#serah-terima-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>')
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? (Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $(".modal").modal("hide"), hapus_seleksi(), load_kavling()) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }), $("#serah-terima-form-btn").html("Simpan"), $("#serah-terima-form-btn").prop("disabled", !1)
            }
        })
    }

    function open_set_harga() {
        if (0 == editdtt.length) return;
        $("#fm-set_harga")[0].reset();
        let a = [];
        for (let t = 0; t < editdtt.length; t++) a.push(editdtt[t].id.substr(3));
        $.ajax({
            url: base_url + "/siteplan/get_harga_kavling",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: a
            },
            dataType: "json",
            success: function(a) {
                csrfHash = a.token;
                let t, e, n, i, l = a.data,
                    s = "",
                    o = "";
                if ($(".select2").val(null).trigger("change"), l.length > 0) {
                    for (let a = 0; a < l.length; a++) s += l[a].id_kavling + ";", o += l[a].no_kavling + ";", t = l[a].id_cluster, e = l[a].id_jalan, n = l[a].id_tipe, i = l[a].harga_akhir, l[a].harga_akhir && ($("#sh-id").append($("<option selected></option>").attr("value", l[a].harga_akhir).text("Rp. " + num_format(l[a].hargajual) + " (" + l[a].tipe_rumah + "): " + l[a].tgl_harga)).trigger("change"), $("#sh-row").val(l[a].row).change(), $("#sh-tipe").val(l[a].tipe_rumah).change(), $("#sh-lb").val(l[a].hj_lb).change(), $("#sh-lt").val(l[a].hj_lt).change(), $("#sh-hargajual").val(l[a].hargajual).change(), $("#sh-kpr").val(l[a].kpr).change(), $("#sh-uang_muka").val(l[a].uang_muka).change(), $("#sh-bphtb").val(l[a].bphtb).change(), $("#sh-biaya_adm").val(l[a].biaya_adm).change(), $("#sh-biaya_proses").val(l[a].biaya_proses).change());
                    $(".id_kavling").val(s), $("#fm-set_harga #id_cluster").val(t), $("#fm-set_harga #id_jalan").val(e), $("#fm-set_harga #no_kavling").val(o), $("#fm-set_harga #id_tipe").val(n), $("#fm-set_harga #harga").val(i).keyup()
                }
            }
        }), $("#modals-set_harga").modal({
            backdrop: "static",
            keyboard: !1
        })
    }

    function set_harga() {
        $.ajax({
            url: base_url + "/Hargajual/set_harga",
            type: "post",
            data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash,
            dataType: "json",
            beforeSend: function() {
                $("#set-harga-form-btn").html('Menyimpan <i class="fa fa-spinner fa-spin"></i>'), $("#set-harga-form-btn").addClass("disabled")
            },
            success: function(a) {
                csrfHash = a.token, !0 === a.success ? Swal.fire({
                    position: "bottom-end",
                    icon: "success",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#modals-set_harga").modal("hide"), $("#set-harga-form-btn").html("Simpan"), $("#set-harga-form-btn").removeClass("disabled")
                })) : Swal.fire({
                    position: "bottom-end",
                    icon: "error",
                    title: a.messages,
                    showConfirmButton: !1,
                    timer: 1500
                }).then((function() {
                    $("#set-harga-form-btn").html("Simpan"), $("#set-harga-form-btn").removeClass("disabled")
                })), load_kavling(), hapus_seleksi()
            }
        })
    }

    function fitStageIntoParentContainer() {
        var a = document.querySelector("#stage-parent").offsetWidth / sceneWidth;
        stage.width(sceneWidth * a), stage.height(sceneHeight * a), stage.scale({
            x: a,
            y: a
        })
    }
    fuks.addEventListener("change", (function(a) {
        fluks = [], flduks = "", $("#list_upload_komplain_sales").html();
        for (let a = 0; a < fuks.files.length; a++) fluks.push(fuks.files[a]), flduks += "<p>" + (a + 1) + ": " + fuks.files[a].name + " </p>", $("#list_upload_komplain_sales").html(flduks)
    })), $("#sh-id").select2({
        placeholder: "Pilih Pricelist",
        allowClear: !0,
        ajax: {
            url: base_url + "/Hargajual/getAll",
            dataType: "json",
            delay: 250,
            method: "post",
            data: function(a) {
                return {
                    [csrfName]: csrfHash,
                    search: a.term,
                    id_proyek: $("#id_proyek").val()
                }
            },
            processResults: function(a) {
                csrfHash = a.token;
                let t = [];
                return $.each(a.data, (function(a, e) {
                    t.push({
                        id: e.id,
                        text: "Rp. " + num_format(e.hargajual) + " (" + e.tipe_rumah + "): " + e.tgl_harga,
                        row: e.row,
                        tipe: e.tipe_rumah,
                        lb: e.lb,
                        lt: e.lt,
                        hargajual: e.hargajual,
                        kpr: e.kpr,
                        uang_muka: e.uang_muka,
                        bphtb: e.bphtb,
                        biaya_adm: e.biaya_adm,
                        biaya_proses: e.biaya_proses,
                        id_tipe: e.id_tipe
                    })
                })), {
                    results: t
                }
            },
            cache: !1
        }
    }), $("#sh-id").on("select2:selecting", (function(a) {
        var t = a.params.args.data;
        $.each(t, (function(a, t) {
            $("#sh-" + a).val(t).change().keyup()
        }))
    })), $("#sh-id").change((function() {
        this.value || $(".sh-fm").val("")
    })), stage.add(siteplan, masked, datal), stage.draw(), fitStageIntoParentContainer(), $(".select2-selection__arrow").css("pointer-events", "none");
</script>
