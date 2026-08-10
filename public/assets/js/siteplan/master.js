let sv_url,
    sv_fm,
    sv_btn,
    sv_par,
    wr_pembangunan = [],
    list_jatuhtempo = [],
    filter = {
        id_cluster: '',
        id_jalan: ''
    },
    filterwarna = {
        Status: null,
        Subsidi: null,
        Komersil: null,
        Legal: null,
        Pajak: null,
        Target: null,
        'Lain-lain': null
    };

const not_found = "/images/not_found.png"

let c_date = new Date();
let c_date_m = (c_date.getMonth() + 1 > 10) ? c_date.getMonth() + 1 : "0" + (c_date.getMonth() + 1);
let today_date = c_date.getFullYear() + '-' + c_date_m + '-' + c_date.getDate();

//convert date to num
function treatAsUTC(date) {
    let result = new Date(date);
    result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
    return result;
}

//cari selisih hari
function daysBetween(startDate, endDate) {
    let millisecondsPerDay = 24 * 60 * 60 * 1000;
    return (treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay;
}

Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0, 10);
});
    let data_um = [],
        data_bb = []


    //sewwtalert2 fix error cant type after open modal
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    const state = {
        id_kavling: null,
        id_hargajual: null,
        id_mkdt: null,
        data_um: {},
        data_bb: {},
        sisa_cicilan: 0,
        sudah_bayar: 0,
        total_cicilan: 0,
        status: {
            tab: {
                isClosed: false
            }
        }

    };


    //carousel
    $('.carousel').carousel('pause')

    //datepicker
    var fp = flatpickr(".flatpickr-human-friendly", {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
        }),
        sp,
        dtt = [], //data point from selection
        batchdtt = [], // multiple data point from selection
        kav, //kavling poly
        imageInfo,
        bml_old = 0, //batch mask old
        batchMask = [], //for multiple selection
        dtt_first = [], //for multiple selection
        sceneWidth = screen.width,
        sceneHeight = Math.min(window.innerHeight, window.innerHeight * 0.7);
    //declare stage
    var stage = new Konva.Stage({
        height: sceneHeight,
        width: sceneWidth,
        container: "konva-holder",
        draggable: true
    });

    //declare layer
    var siteplan = new Konva.Layer(), //siteplan layer
        masked = new Konva.Layer(), //selection layer
        // datal = new Konva.Layer(), //kavling shape layer
        group = new Konva.Group({ //group for tooltip
            visible: false,
        }),
        shape_ket = new Konva.Image({
            x: 10,
            y: 10,
            draggable: true,
            stroke: 'red',
            scaleX: 2,
            scaleY: 2,
        }),

        manual_selection = new Konva.Group(),

        maskedGroup = new Konva.Group(), //group for selection line and number
        tooltip = new Konva.Text({ //tooltip text on hovering at kavling
            text: '',
            fontFamily: 'Calibri',
            fontSize: 12,
            padding: 5,
            textFill: 'white',
            fill: 'black',
            text: 'vertical align',
            alpha: 0.75,
        }),
        tooltipbg = new Konva.Rect({ //tooltip bg on hovering at kavling
            width: 270,
            height: 57,
            stroke: "black",
            strokeWidth: 1,
            fill: "#f2ff7d",
        })

    Konva.hitOnDragEnabled = true; //for zoom on touchscreen

    group.add(tooltipbg, tooltip) //grouping tooltip bg & text
    siteplan.add(group);
    masked.add(shape_ket);
    masked.add(group);

    var siteplanImageReady = false,
        siteplanStageReady = false,
        siteplanCanvasInitialized = false,
        siteplanFitDone = false;
    var siteplanKavlingRequest = null,
        siteplanOthersRequest = null,
        siteplanLoadSequence = 0;

    function syncSiteplanMainHeight(konva_h) {
        const $card = $('.siteplan-main-card');
        const $cardBody = $card.children('.card-body').first();
        const fallbackHeight = Math.ceil(Number(konva_h) || sceneHeight || 0);

        if (!$card.length) {
            return fallbackHeight;
        }

        if ($(window).width() < 768) {
            $card.css({
                '--siteplan-main-card-height': 'auto',
                '--siteplan-main-content-height': 'auto'
            });
            $('#filter-side').css('height', 'auto');
            return fallbackHeight;
        }

        const paddingY = (parseFloat($cardBody.css('padding-top')) || 0) +
            (parseFloat($cardBody.css('padding-bottom')) || 0);
        const cardRect = $card[0].getBoundingClientRect();
        const viewportGap = 16;
        const availableCardHeight = Math.max(0, window.innerHeight - cardRect.top - viewportGap);
        const availableContentHeight = Math.max(0, availableCardHeight - paddingY);
        const contentHeight = Math.ceil(Math.max(fallbackHeight, availableContentHeight));

        $card.css({
            '--siteplan-main-card-height': Math.ceil(contentHeight + paddingY) + 'px',
            '--siteplan-main-content-height': contentHeight + 'px'
        });
        $('#filter-side').css('height', '');

        return contentHeight;
    }

    function tryInitSiteplanCanvas() {
        if (!siteplanImageReady || !siteplanStageReady || siteplanCanvasInitialized) {
            return;
        }

        siteplanCanvasInitialized = true;
        initSiteplanCanvas();
    }

    // siteplan img object :
    var imageObj = new Image();
    imageObj.onload = function() {

        sp = new Konva.Image({
            x: 0,
            y: 0,
            image: imageObj,
            width: imageObj.width,
            height: imageObj.height,
            globalCompositeOperation: 'overlay'
        });

        // add image to the layer
        siteplan.add(sp);
        siteplanImageReady = true;
        tryInitSiteplanCanvas();
    };


    //siteplan src
    imageObj.src = dt_proyek.siteplan_access_url || file_url('proyek_siteplan', dt_proyek.id_proyek);

    //deklarasi kanvas untuk kavling
    function initSiteplanCanvas() {
        colorThreshold = 15;
        blurRadius = 1;
        simplifyTolerant = 0;
        simplifyCount = 30;
        hatchLength = 4;
        hatchOffset = 0;

        imageInfo = null;
        cacheInd = null;
        mask = null;
        oldMask = null;
        downPoint = null;
        allowDraw = false;
        addMode = false;
        currentThreshold = colorThreshold;

        // showThreshold();

        //imginfo
        var img = imageObj;
        var cvs = masked;
        cvs.width = img.width;
        cvs.height = img.height;
        imageInfo = {
            width: img.width,
            height: img.height,
            context: cvs.getContext("2d", {
                willReadFrequently: true
            })._context
        };
        mask = null;

        var tempCtx = document.createElement("canvas").getContext("2d", {
            willReadFrequently: true
        });
        tempCtx.canvas.width = imageInfo.width;
        tempCtx.canvas.height = imageInfo.height;
        tempCtx.drawImage(img, 0, 0);
        imageInfo.data = tempCtx.getImageData(0, 0, imageInfo.width, imageInfo.height);

        //load kavling dari database
        load_kavling(roleid == 1 || roleid == 7);

        // $("#pilih-divisi").select2("val", roleid)
        // change_div();
        load_menu();


        // scaling layer to fit stage
        let konva_w = parseFloat($("#konva-holder").width())
        let konva_h = parseFloat($("#konva-holder").height())
        let l
        if (konva_w > konva_h)
            l = parseFloat($("#konva-holder").width()) / imageObj.width;
        else
            l = parseFloat($("#konva-holder").height()) / imageObj.height;

        new Konva.Tween({
            node: stage,
            duration: 0.5,
            scaleX: l,
            scaleY: l,
            x: 0,
            y: 0,
            easing: Konva.Easings.EaseInOut,
        }).play();

        // stage.scale({
        //     x: l,
        //     y: l
        // });

        group.scale({
            x: 1 / l,
            y: 1 / l
        })

        stage.height(syncSiteplanMainHeight(konva_h));

    }

    var line_ms = new Konva.Line({
        points: [0, 0],
        stroke: "red",
        strokeWidth: 2,
        dash: [5, 5],
        opacity: 1,
        closed: !0,
        id: "line_sel"
    });

    //refresh kavling setelah ganti divisi
    $("#pilih-divisi").change(function() {
        change_div()
    });

    function isManualSelectionActive() {
        return $("#tambah_jalan").prop("checked") || $("#produksi_tambah_jalan").prop("checked");
    }

    function change_div() {
        $("#tambah_jalan").prop("checked", 0)
        $("#produksi_tambah_jalan").prop("checked", 0)
        hapus_seleksi(); //hapus seleksi kavling

        //tampilkan menu sesuai divisi jika login sebagai admin
        if (roleid == 1) {
            let va = $("#pilih-divisi option:selected").val();
            $(".div_menu").addClass("hidden");

            if (va != 0) {
                const $selectedMenu = $('.div_menu[data-siteplan-role="' + va + '"]');
                if ($selectedMenu.length) {
                    $selectedMenu.removeClass("hidden");
                } else {
                    $('.div_menu[data-siteplan-role="0"]').removeClass("hidden");
                }
            }
        }

        //load ulang kavling
        load_kavling();
    }

    function load_menu() {
        // let va = $("#pilih-divisi option:selected").val();
        $.ajax({
            url: base_url + 'home/getMenuBtn',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                // id_role: roleid
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");
                const menu = r.menu;
                $("#menu_here").html(menu);
                if (roleid == 7 && has_akses.proyek == false) {
                    $(".btn-prod").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $(".btn-prod").show()
                }
                if (roleid == 5 && has_akses.legal == false) {
                    $("#edit_kavling_batch").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $("#edit_kavling_batch").show()
                }
            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        })
    }

    function registerMobileBottomNav(config) {
        window.SIGAPPMobileBottomNavQueue = window.SIGAPPMobileBottomNavQueue || [];
        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.register === 'function') {
            window.SIGAPPMobileBottomNav.register(config);
        } else {
            window.SIGAPPMobileBottomNavQueue.push(config);
        }
    }

    function registerSiteplanMobileBottomNav() {
        registerMobileBottomNav({
            filter: {
                sourceSelector: '#filter-side'
            },
            actions: [{
                sourceSelector: '#menu_here'
            }],
            showBack: true,
            showMenu: true
        });
    }

    registerSiteplanMobileBottomNav();

    function buat_nominatif() {

    }

    var stroke, fill, strokeWidth, dashed;
    let filterwarnahitung = {};

    function set_fill2(e) { //test set fill dengan config dari db
        // console.log(conf[e])
        if (!conf[e])
            e = "Warna Tidak Ditemukan"
        set_fill(conf[e].fill, conf[e].stroke, conf[e].strokeWidth, conf[e].dashed)
        return e;
    }

    const legalStatusOrder = [
        'Belum Isi Data',
        'Data Legal Masuk',
        'Sertipikat Induk Ada',
        'Split Sertipikat Diproses',
        'Sertipikat Split Terbit',
        'PBB Pecah / NOP Terbit',
        'PBG/IMB Terbit',
        'PPJB Dibuat',
        'PPH Dibayar / Validasi',
        'BPHTB Dibayar / Validasi',
        'AJB Dibuat',
        'Balik Nama Sertipikat',
        'Balik Nama PBB',
        'Selesai Legal'
    ];

    const pajakStatusOrder = [
        'Belum Input Pajak',
        'PPh4(2) Belum Bayar',
        'PPh4(2) Dibayar',
        'PPN Belum Bayar',
        'PPN Dibayar',
        'Faktur Pajak Terbit',
        'Selesai Pajak'
    ];

    function get_kategori_color(kategori) {
        if (conf[kategori]) return conf[kategori].fill;
        if (kategori == 'Belum Target') return '#f8fafc';
        if (String(kategori).indexOf('Target ') === 0) {
            const colors = ['#f59e0b', '#14b8a6', '#3b82f6', '#a855f7', '#ef4444', '#22c55e'];
            const year = parseInt(String(kategori).replace('Target ', ''), 10);
            return colors[Math.abs(year || 0) % colors.length];
        }
        return '#d1d5db';
    }

    function legalHasValue(value) {
        return value !== null &&
            value !== undefined &&
            value !== '' &&
            value !== 'null' &&
            value !== '0000-00-00';
    }

    function legalIsYes(value) {
        return value === 1 || value === '1' || value === true || value === 'Ya' || value === 'Iya' || value === 'Sudah';
    }

    function getLegalStatus(row) {
        if (!row.id_legal) {
            return {
                fill: 'Belum Isi Data',
                tipe: 'Legal'
            };
        }

        const sertifikatBalikNama = legalIsYes(row.sertifikat_is_balik_nama);
        const pbbBalikNama = legalIsYes(row.pbb_is_balik_nama);
        const sertifikatDikirim = legalHasValue(row.sertifikat_balik_nama_tgl_pengiriman) || legalHasValue(row.sertifikat_balik_nama_ke);
        const pbbDikirim = legalHasValue(row.pbb_balik_nama_tgl_pengiriman) || legalHasValue(row.pbb_balik_nama_ke);

        let status = 'Data Legal Masuk';

        if (legalHasValue(row.sertifikat_split_no_hgb_induk)) {
            status = 'Sertipikat Induk Ada';
        }

        if (legalIsYes(row.sertifikat_is_split) && !legalHasValue(row.sertifikat_split_no_hgb)) {
            status = 'Split Sertipikat Diproses';
        }

        if (legalHasValue(row.sertifikat_split_no_hgb) || legalHasValue(row.sertifikat_split_nib) || legalHasValue(row.sertifikat_split_tanggal_terbit)) {
            status = 'Sertipikat Split Terbit';
        }

        if (legalHasValue(row.pbb_pecah_nop)) {
            status = 'PBB Pecah / NOP Terbit';
        }

        if (row.pbg_status === 'Selesai' || legalHasValue(row.pbg_no) || legalHasValue(row.pbg_tanggal_terbit)) {
            status = 'PBG/IMB Terbit';
        }

        if (legalHasValue(row.ppjb_no) || legalHasValue(row.ppjb_tanggal)) {
            status = 'PPJB Dibuat';
        }

        if (legalHasValue(row.pph_tgl_bayar) || legalHasValue(row.pph_ntpn) || legalHasValue(row.pph_tanggal_validasi) || legalHasValue(row.pph_tgl_selesai) || legalHasValue(row.pph_no_sket)) {
            status = 'PPH Dibayar / Validasi';
        }

        if (legalHasValue(row.bphtb_tanggal_pembayaran) || legalHasValue(row.bphtb_tanggal_validasi)) {
            status = 'BPHTB Dibayar / Validasi';
        }

        if (legalHasValue(row.ajb_no) || legalHasValue(row.ajb_tanggal)) {
            status = 'AJB Dibuat';
        }

        if (sertifikatBalikNama) {
            status = 'Balik Nama Sertipikat';
        }

        if (pbbBalikNama) {
            status = 'Balik Nama PBB';
        }

        if (sertifikatBalikNama && pbbBalikNama && (sertifikatDikirim || pbbDikirim)) {
            status = 'Selesai Legal';
        }

        return {
            fill: status,
            tipe: 'Legal'
        };
    }

    function pajakNumber(value) {
        if (!legalHasValue(value)) return 0;

        const parsed = parseFloat(String(value).replace(/[^\d.-]/g, ''));
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function getPajakStatus(row) {
        if (row.status_mkdt == 'Batal' || row.is_batal == 1) {
            return {
                fill: 'Batal',
                tipe: 'Status'
            };
        }

        if (!row.id_pajak) {
            return {
                fill: 'Belum Input Pajak',
                tipe: 'Pajak'
            };
        }

        const pphComplete = legalHasValue(row.pph42_tgl_bayar) || legalHasValue(row.pph42_ntpn);
        if (!pphComplete) {
            return {
                fill: 'PPh4(2) Belum Bayar',
                tipe: 'Pajak'
            };
        }

        if (!legalHasValue(row.ppn_nilai)) {
            return {
                fill: 'PPh4(2) Dibayar',
                tipe: 'Pajak'
            };
        }

        const ppnRequired = pajakNumber(row.ppn_nilai) > 0;
        if (!ppnRequired) {
            return {
                fill: 'Selesai Pajak',
                tipe: 'Pajak'
            };
        }

        const ppnComplete = legalHasValue(row.ppn_tgl_bayar) || legalHasValue(row.ppn_ntpn);
        const fakturTerbit = legalHasValue(row.ppn_no_faktur);

        if (fakturTerbit && !ppnComplete) {
            return {
                fill: 'Faktur Pajak Terbit',
                tipe: 'Pajak'
            };
        }

        if (!ppnComplete) {
            return {
                fill: 'PPN Belum Bayar',
                tipe: 'Pajak'
            };
        }

        if (!fakturTerbit) {
            return {
                fill: 'PPN Dibayar',
                tipe: 'Pajak'
            };
        }

        return {
            fill: 'Selesai Pajak',
            tipe: 'Pajak'
        };
    }

    function hitung_kavling(fill) {
        let e = fill.fill
        let p = filterwarnahitung[e] ? filterwarnahitung[e] : 0;
        filterwarnahitung[e] = p + 1;

    }

    function set_keterangan_warna() {
        $("#keterangan-warna-here").html(" ")

        //filter
        $("#filter-kategori option").remove()
        $("#filter-kategori").append(`<option value="">Semua</option>`);
        let div = "",
            kv
        // console.log(filterwarna)
        $.each(filterwarna, function(i, v) {
            if (v) {
                div += `
                <div class="divider">
                    <div class="divider-text">${i} ${i == 'Subsidi' || i == 'Komersil' ? 'Dipasarkan' : ''}</div>
                </div>`;
                const statusOrder = i === 'Legal' ? legalStatusOrder : (i === 'Pajak' ? pajakStatusOrder : null);
                const sortedKeys = statusOrder ?
                    statusOrder.filter(key => Object.prototype.hasOwnProperty.call(v, key)).concat(Object.keys(v).filter(key => !statusOrder.includes(key)).sort()) :
                    Object.keys(v).sort();

                // Step 2: Create a new object with sorted keys
                const sortedObj = {};
                sortedKeys.forEach(key => {
                    sortedObj[key] = v[key];
                });

                $.each(sortedObj, function(x, y) {
                    //untuk tambah option di filter
                    $("#filter-kategori").append(`<option value="${x}">${x}</option>`);
                    kv = (x == "Def") ? "Data yang bisa diolah" : x;

                    div += `<div class="form-group row">
                                <div class="btn col-2 ml-1" style="background-color:${y}"></div>
                                <div class="col-9"> ${kv} (${filterwarnahitung[x]})</div>
                            </div>`;
                })
            }
        })
        $("#keterangan-warna-here").html(div)
    }
    function getHitForFilter(filterKey, row, subsidi) {
        if (filterKey === 'Masalah') {
            const statusMasalah = $('#filter-status-masalah').val();
            if (statusMasalah === 'dalam_proses') return { fill: 'Masalah Progress', tipe: 'Filter' };
            if (statusMasalah === 'selesai') return { fill: 'Masalah Selesai', tipe: 'Filter' };
            if (statusMasalah === 'batal') return { fill: 'Masalah Batal', tipe: 'Filter' };
            if (statusMasalah === 'hold') return { fill: 'Masalah Hold', tipe: 'Filter' };
            if (statusMasalah === 'dibuat') return { fill: 'Masalah Baru Dibuat', tipe: 'Filter' };
            return { fill: 'Status Masalah', tipe: 'Filter' };
        }

        const hitMap = {
            'Sudah Akad':       { fill: 'Akad ' + subsidi, tipe: 'Filter' },
            'Akad Komersil':    { fill: 'Akad Komersil', tipe: 'Filter' },
            'Akad Subsidi':     { fill: 'Akad Subsidi', tipe: 'Filter' },
            'Booking':          { fill: 'Booking', tipe: 'Filter' },
            'Batal':            { fill: 'Batal', tipe: 'Filter' },
            'SP3K':             { fill: 'SP3K', tipe: 'Filter' },
            'Masalah':          { fill: 'Status Masalah', tipe: 'Filter' },
            'Turun Pembangunan':{ fill: 'Perintah Bangun', tipe: 'Filter' },
            'Bangunan Selesai': { fill: 'Bangunan 100%', tipe: 'Filter' },
            'Jatuh Tempo':      { fill: 'Jatuh Tempo', tipe: 'Filter' },
            'Hasil Akad Belum Cair': { fill: 'Hasil Akad Belum Cair', tipe: 'Filter' },
            'Pengajuan Pencairan Hasil Akad': { fill: 'Pengajuan Pencairan Hasil Akad', tipe: 'Filter' },
            'Pencairan Hasil Akad': { fill: 'Lunas', tipe: 'Filter' }, // Note: assuming Lunas/Dajam Belum Cair, or let's use 'Lunas' as that exists
        };
        return hitMap[filterKey] || null;
    }

    function checkKavlingMatchesFilter(row, filterKey) {
        switch (filterKey) {
            case 'Sudah Akad':
                return row.status_mkdt === 'Akad';
            case 'Akad Komersil':
                return row.status_mkdt === 'Akad' && row.is_subsidi == 0;
            case 'Akad Subsidi':
                return row.status_mkdt === 'Akad' && row.is_subsidi == 1;
            case 'Booking':
                return row.status_mkdt === 'Booking';
            case 'Batal':
                return row.is_batal == 1 || row.status_mkdt === 'Batal';
            case 'SP3K':
                return row.sp3k_tgl != null && row.sp3k_tgl !== '0000-00-00';
            case 'Masalah':
                // Check if any complaint exists or other problem (matches backend logic)
                return row.status_komplain == 1 || row.status_komplain == 2 || row.status_komplain == 3;
            case 'Turun Pembangunan':
                return row.perintah_bangun == 1;
            case 'Bangunan Selesai':
                return parseInt(row.progres_bangunan) === 100;
            case 'Jatuh Tempo':
                if (!row.jatuh_tempo_tgl) return false;
                const today = new Date();
                const sevenDaysLater = new Date();
                sevenDaysLater.setDate(today.getDate() + 7);
                const jt = new Date(row.jatuh_tempo_tgl);
                return jt <= today && jt <= sevenDaysLater;
            case 'Hasil Akad Belum Cair':
                return row.status_mkdt == 'Akad' && row.is_kpr == 1 && row.is_lunas == 1 && (row.pa_plan_id == null || row.pa_pengajuan_count == null || row.pa_pengajuan_count == 0);
            case 'Pengajuan Pencairan Hasil Akad':
                if (row.status_mkdt != 'Akad' || row.is_kpr != 1 || row.is_lunas != 1) return false;
                const hasilAkad = parseFloat(row.pa_total_hasil_akad || 0);
                const totalCair = parseFloat(row.pa_total_cair_sum || 0);
                return row.pa_plan_id != null && (hasilAkad - totalCair > 0.01);
            case 'Pencairan Hasil Akad':
                if (row.status_mkdt != 'Akad' || row.is_kpr != 1 || row.is_lunas != 1) return false;
                const hAkad = parseFloat(row.pa_total_hasil_akad || 0);
                const tCair = parseFloat(row.pa_total_cair_sum || 0);
                return row.pa_plan_id != null && (hAkad - tCair <= 0.01);
            default:
                return false;
        }
    }

    function getFilterColorOverride(row, subsidi) {
        const serverData = getServerFilterData();
        const activeKategori = serverData.kategori;
        if (!activeKategori || activeKategori.length === 0) return null;

        // Jika hanya 1 filter, backend sudah pasti mengembalikan row yang match.
        if (activeKategori.length === 1) {
            return getHitForFilter(activeKategori[0], row, subsidi);
        }

        for (const kat of activeKategori) {
            if (checkKavlingMatchesFilter(row, kat)) {
                return getHitForFilter(kat, row, subsidi);
            }
        }
        
        // Fallback untuk multi-select (misal: Akad + Masalah)
        // Karena Masalah dicek dari tiket_masalah di backend (yang tidak ada datanya di frontend),
        // checkKavlingMatchesFilter untuk Masalah akan gagal.
        // Jika row ini dikembalikan backend tapi tidak match filter lain, kita asumsikan ini row Masalah.
        if (activeKategori.includes('Masalah')) {
            return getHitForFilter('Masalah', row, subsidi);
        }

        return null;
    }

    //load shape kavling
    function load_kavling(refresh = false) {
        const loadSequence = ++siteplanLoadSequence;

        if (siteplanKavlingRequest && siteplanKavlingRequest.readyState !== 4) {
            siteplanKavlingRequest.abort();
        }
        if (siteplanOthersRequest && siteplanOthersRequest.readyState !== 4) {
            siteplanOthersRequest.abort();
        }

        hapus_seleksi();
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            Legal: null,
            Pajak: null,
            Target: null,
            'Lain-lain': null
        };
        filterwarnahitung = {};

        const fields = [ //untuk pengecekan status kavling yang sudah done
            'nama_jalan',
            'no_kavling',
            'status_mkdt',
            'is_lunas',
            'progres_bangunan',
            'sertifikat_split_no_hgb_induk',
            'sertifikat_split_no_hgb',
            'sertifikat_is_balik_nama',
            'pbb_pecah_nop',
            'pbb_is_balik_nama',
            'pbg_no',
            'ajb_no',
            'pph_tgl_bayar',
            'bphtb_tanggal_pembayaran'
        ];
        const isValidDate = (val) => {
            return typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val) && !isNaN(new Date(val).getTime()) && val !== "0000-00-00";
        };

        siteplan.find('Line').forEach(line => line.destroy());

        let va = $("#pilih-divisi option:selected").val();
        wr_pembangunan = [];
        list_jatuhtempo = [];
        siteplanKavlingRequest = $.ajax({
            url: base_url + 'siteplan/get/all',
            type: 'post',
            data: Object.assign({
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_cluster: filter.id_cluster,
                id_jalan: filter.id_jalan,
                id_role: va
            }, getServerFilterData()),
            dataType: 'json',
            beforeSend: () => $("#loading").removeClass("hidden"),
            success: function(result) {
                if (loadSequence !== siteplanLoadSequence) {
                    return;
                }

                $("#loading").addClass("hidden");
                csrfHash = result.token;
                stroke = fill = strokeWidth = dashed = "";

                let r = result['data'],
                    targetKavling = result.target_kavling || {},
                    data2,
                    hit,
                    tp_rumah,
                    subsidi;



                for (var p = 0; p < r.length; p++) {
                    tp_rumah = r[p].tipe_rumah
                    no_tp_rumah = r[p].no_tipe_rumah

                    subsidi = (r[p].is_subsidi == "1") ? "Subsidi" : "Komersil"

                    //set default shape color
                    // set_fill("#fff67a", "#000000", 0, null)
                    hit = {
                        fill: set_fill2("Def"),
                        tipe: 'Lain-lain'
                    }

                    //add list to belum selesai bangun
                    if (r[p].tanggal_rencana_selesai_pembangunan != null) {
                        if (r[p].tanggal_selesai_pembangunan == null) {
                            if (daysBetween(today_date, r[p].tanggal_rencana_selesai_pembangunan) < 3) {
                                wr_pembangunan.push({
                                    id_kavling: r[p].id_kavling,
                                    id_produksi: r[p].id_produksi,
                                    id_mkdt: r[p].id_mkdt,
                                    id_keuangan: r[p].id_keuangan,
                                    id_tipe: r[p].id_tipe,
                                    id_gambar_kerja: r[p].id_gambar_kerja,
                                    progres: r[p].progres_bangunan ? r[p].progres_bangunan : 0,
                                    nama_jalan: r[p].nama_jalan,
                                    no_kavling: r[p].no_kavling,
                                    no_tipe_rumah: r[p].no_tipe_rumah,
                                    tipe: r[p].tipe_rumah,
                                    keterangan: r[p].keterangan_produksi,
                                    tanggal_pembangunan: r[p].tanggal_pembangunan,
                                    tanggal_rencana_selesai_pembangunan: r[p].tanggal_rencana_selesai_pembangunan,
                                    tanggal_selesai_pembangunan: r[p].tanggal_selesai_pembangunan
                                })
                            }
                        }
                    }

                    //add to list jatuh tempo
                    const today = new Date();
                    const sevenDaysLater = new Date();
                    sevenDaysLater.setDate(today.getDate() + 7);


                    if (r[p].jatuh_tempo_tgl) {
                        const jatuhTempo = new Date(r[p].jatuh_tempo_tgl);

                        if (jatuhTempo <= today && jatuhTempo <= sevenDaysLater) {
                            list_jatuhtempo.push(r[p].id_kavling)
                        }
                    }


                    // if (r[p].tanggal_selesai_pembangunan != '0000-00-00' || r[p].tanggal_selesai_pembangunan != '' || r[p].tanggal_selesai_pembangunan != null) {

                    // }

                    if (r[p].harga_akhir) {
                        tp_rumah = r[p].tipe_rumah
                        no_tp_rumah = r[p].no_tipe_rumah
                        hit = set_fill2(r[p].tipe_rumah)
                        hit = {
                            fill: hit,
                            tipe: subsidi
                        }
                        // if (r[p].is_subsidi == 1)
                        //     hit = set_fill2("Subsidi")
                        // else
                        //     hit = set_fill2("Non-Subsidi")
                    }


                    if (r[p].status_mkdt) {
                        hit = set_fill2(r[p].status_mkdt)
                        if (hit == "Akad" || hit == "Booking") {
                            hit = hit + " " + subsidi
                        }
                    } else {
                        if (r[p].progres_bangunan == "100") {
                            // jika sudah selesai
                            hit = set_fill2("Ready Stock")
                        }
                    }


                    if (va == 3) { //keuangan
                        if (r[p].is_lunas == 0 || r[p].is_lunas == null || r[p].is_lunas == "undefined") {
                            if (r[p].jatuh_tempo_tgl != null && r[p].jatuh_tempo_tgl != "0000-00-00") {
                                if (daysBetween(today_date, r[p].jatuh_tempo_tgl) < 7)
                                    hit = set_fill2('Jatuh Tempo')
                            }

                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        } else if (r[p].is_lunas == 1) {
                            hit = set_fill2('Lunas')
                            if (r[p].status_mkdt == 'Akad') {
                                if (r[p].is_kpr == 1) {
                                    if (r[p].pa_plan_id == null || r[p].pa_pengajuan_count == null || r[p].pa_pengajuan_count == 0) {
                                        hit = set_fill2('Hasil Akad Belum Cair')
                                    } else {
                                        const hasilAkad = parseFloat(r[p].pa_total_hasil_akad || 0);
                                        const totalCair = parseFloat(r[p].pa_total_cair_sum || 0);
                                        if (hasilAkad - totalCair > 0.01)
                                            hit = set_fill2('Pengajuan Pencairan Hasil Akad')
                                        else
                                            hit = set_fill2('Akad ' + subsidi)
                                    }
                                } else {
                                    hit = set_fill2('Akad ' + subsidi)
                                }
                            }
                        }

                        if (r[p].is_batal == 1)
                            hit = set_fill2("Batal")
                    } else if (va == 4) { //mkdt
                        if (r[p].perintah_bangun == 1)
                            hit = set_fill2("Perintah Bangun")

                        //status kavling
                        if (r[p].status_mkdt == "Batal") { //jika batal
                            if (r[p].is_batal == 1)
                                hit = set_fill2("Batal")
                            else
                                hit = set_fill2(r[p].status_mkdt)
                        } else if (r[p].status_mkdt == "Akad") { //jika akad
                            hit = set_fill2('Akad')
                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        } else if (r[p].status_mkdt == "Booking") {
                            //jika booking
                            if (r[p].booking_tgl != null && r[p].booking_tgl != "0000-00-00") {
                                // if (r[p].is_kpr == 1)
                                //     hit = set_fill2('KPR')
                                // else if (r[p].is_kpr == 0)
                                //     hit = set_fill2('Tunai')
                                // if (r[p].mkdt_is_subsidi == 1) {
                                //     hit = set_fill2('Subsidi')
                                // } else if (r[p].mkdt_is_subsidi == 0) {
                                //     hit = set_fill2('Non-Subsidi')
                                // }
                            }
                            //jika turun sp3k
                            if (r[p].sp3k_tgl != null && r[p].sp3k_tgl != "0000-00-00") {
                                hit = set_fill2('SP3K')
                            }
                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        }
                    } else if (va == 5) { //legal
                        hit = getLegalStatus(r[p]);

                        // if (
                        //     r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" &&
                        //     r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" &&
                        //     r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" &&
                        //     r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_no_hgb != null &&
                        //     r[p].sertifikat_no_split != null &&
                        //     r[p].imb_no_induk != null &&
                        //     r[p].imb_no_split != null &&
                        //     r[p].nop_pbb != null &&
                        //     r[p].pph != null
                        // )
                        //     hit = set_fill2("Sudah Diisi")
                        // else {
                        //     if (
                        //         r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" ||
                        //         r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" ||
                        //         r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" ||
                        //         r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_no_hgb != null ||
                        //         r[p].sertifikat_no_split != null ||
                        //         r[p].imb_no_induk != null ||
                        //         r[p].imb_no_split != null ||
                        //         r[p].nop_pbb != null ||
                        //         r[p].pph != null
                        //     )
                        //         hit = set_fill2("Sebagian Diisi")
                        // }
                        // if (r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                        // if (r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                    } else if (va == 7) { //produksi
                        // if (r[p].status_mkdt)
                        //     hit = set_fill2(r[p].status_mkdt)
                        if (r[p].status_mkdt == "Akad") {
                            hit = set_fill2("Akad " + subsidi)
                            if (r[p].progres_bangunan == null)
                                hit = set_fill2("Pembangunan") // warna merah

                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")
                            // if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                            //     hit = set_fill2("Pembangunan") // warna merah
                            // }else if (parseInt(r[p].progres_bangunan) == 100) {
                            //     // jika sudah selesai
                            //     hit = set_fill2("Akad "+ subsidi)
                            // }
                        } else if (r[p].status_mkdt == "Booking") {
                            hit = set_fill2("Booking " + subsidi)

                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")

                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")

                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Bangunan 100%")
                            }
                        } else {
                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")
                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Ready Stock") // warna merah
                            }
                        }

                        //jika ada komplain (dari sales)
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain")

                    } else if (va == 8) { //sales
                        //jika bangunan sudah 100%
                        if (r[p].progres_bangunan == "100") {
                            hit = set_fill2("Pembangunan Selesai") // warna biru
                            if (r[p].id_mkdt == null)
                                hit = set_fill2("Ready Stock") // warna biru
                        }

                        //jika sudah akad
                        if (r[p].status_mkdt == "Akad")
                            hit = set_fill2("Akad") //warna ungu
                        //jika ada komplain
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain") //warna merah
                        // jika sudah dicek
                        if (r[p].is_checked == 1)
                            hit = set_fill2("Sudah dicek") // warna orange
                        // jika sudah dicek
                        if (r[p].is_serah_terima == 1)
                            hit = set_fill2("Serah Terima") // warna hijau

                    } else if (va == 9) {
                        if (r[p].harga_akhir) {
                            tp_rumah = r[p].tipe_rumah
                            no_tp_rumah = r[p].no_tipe_rumah
                            hit = set_fill2(r[p].tipe_rumah)

                            //ubah var hit ke object
                            hit = {
                                fill: hit,
                                tipe: subsidi
                            }

                        }
                    } else if (va == 10) { //pajak
                        hit = getPajakStatus(r[p]);
                    }


                    // }



                    let targetInfo = null;
                    if (va == 11) {
                        targetInfo = targetKavling[r[p].id_kavling] || null;
                        if (targetInfo) {
                            hit = {
                                fill: 'Target ' + targetInfo.tahun_target,
                                tipe: 'Target'
                            }
                        } else {
                            hit = {
                                fill: 'Belum Target',
                                tipe: 'Target'
                            }
                        }
                    }

                    const fieldChecks = {
                        'sertifikat_is_balik_nama': (val) => val === 'Sudah',
                        'pbb_is_balik_nama':        (val) => val === 'Sudah',
                        'progres_bangunan':         (val) => parseInt(val) === 100,
                        'is_lunas':                 (val) => val == 1,
                    };
                    const isComplete = fields.every(field => {
                        const val = r[p][field];

                        if (fieldChecks[field]) {
                            return fieldChecks[field](val);
                        }

                        if (field.includes('tgl') || field.includes('tanggal')) {
                            return isValidDate(val);
                        }

                        return val !== null && val !== '';
                    });

                    // if (va != 11 && va != 5 && va != 10 && isComplete) {
                    //     hit = set_fill2("Selesai");
                    // }


                    //harga jual
                    let id_hargajual = r[p].harga_akhir;
                    r[p].harga_akhir = (r[p].hargajual) ? num_format(r[p].hargajual) + " (Per " + format_date(r[p].tgl_harga) + ")" : '-';



                    if (typeof hit !== 'object') {
                        hit = {
                            fill: hit,
                            tipe: 'Status'
                        }
                    }

                    // Override warna jika filter kategori aktif
                    const filterOverride = getFilterColorOverride(r[p], subsidi);
                    if (filterOverride) {
                        set_fill2(filterOverride.fill); // memastikan config warna diset
                        hit = filterOverride;
                    }

                    // return;

                    //set untuk filter warna
                    filterwarna[hit.tipe] = {
                        ...filterwarna[hit.tipe],
                        [hit.fill]: get_kategori_color(hit.fill)
                    }


                    // console.log(hit.fill, conf[hit.fill].fill);

                    hitung_kavling(hit)
                    //data di tiap kavling harus disesuaikan dengan divisi yang dipilih
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        // lineCap: 'round',
                        // lineJoin: 'round',
                        // stroke: stroke,
                        fill: get_kategori_color(hit.fill),
                        // strokeWidth: strokeWidth,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        kategori: hit.fill,
                        data: {
                            nama_jalan: r[p].nama_jalan,
                            no_kavling: r[p].no_kavling,
                            id_produksi: r[p].id_produksi,
                            id_legal: r[p].id_legal,
                            id_keuangan: r[p].id_keuangan,
                            id_sales: r[p].id_sales,
                            id_planning: r[p].id_planning,
                            id_mkdt: r[p].id_mkdt,
                            id_umum: r[p].id_umum,
                            id_direksi: r[p].id_direksi,
                            tipe: 'kavling',
                            status_tanah: r[p].status_tanah,
                            luas_tanah: r[p].luas_tanah,
                            is_batal: r[p].is_batal,
                            // total_biaya: ktotal_biaya,
                            // sudah_bayar: ksudah_bayar
                        },
                        data2: {
                            id_hargajual: id_hargajual,
                            status_mkdt: r[p].status_mkdt,
                            id_tipe: r[p].id_tipe,
                            tipe_rumah: tp_rumah,
                            no_tipe_rumah: no_tp_rumah,
                            id_gambar_kerja: r[p].id_gambar_kerja,
                            harga_akhir: r[p].harga_akhir,
                            harga_akhir_tgl: r[p].harga_akhir_tgl,
                            harga_akhir_oleh: r[p].harga_akhir_oleh_username,
                            id_serah_terima: r[p].id_serah_terima,
                            id_komplain: r[p].id_komplain,
                            target: targetInfo,
                        },
                        id: 'kav' + r[p].id_kavling
                    });
                    siteplan.add(kav);
                }
                set_keterangan_warna()
                cek_tanggal_pembangunan(refresh)
                handlePendingSiteplanUrgentAction()
                scheduleSiteplanUrgentPanelLoad();
            },
            error: function(xhr, st, err) {
                if (st === 'abort' || loadSequence !== siteplanLoadSequence) {
                    return;
                }

                $("#loading").addClass("hidden")
                return swal("error", err);
            },
            complete: function() {
                if (loadSequence === siteplanLoadSequence) {
                    siteplanKavlingRequest = null;
                }
            }
        });

        //load jalan fasos rth
        siteplanOthersRequest = $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_role: va
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(result) {
                if (loadSequence !== siteplanLoadSequence) {
                    return;
                }

                stroke = ""
                fill = ""
                strokeWidth = ""
                dashed = ""

                var r = result.data

                for (var p = 0; p < r.length; p++) {
                    if (r[p].tipe == "jalan")
                        set_fill("#ccc", "#000", "0", null) // warna abu
                    else if (r[p].tipe == "fasos")
                        set_fill("#9000ff", "#000", "0", null) // warna ungu
                    else if (r[p].tipe == "rth")
                        set_fill("#0f0", "#000", "0", null) // warna merah
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        fill: fill,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        data: {
                            tipe: r[p].tipe,
                            nama_jalan: r[p].nama_jalan,
                        },
                        data2: {},
                        id: 'others' + r[p].id
                    });
                    siteplan.add(kav);
                }
            },
            error: function(xhr, st) {
                if (st === 'abort' || loadSequence !== siteplanLoadSequence) {
                    return;
                }

                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            },
            complete: function() {
                if (loadSequence === siteplanLoadSequence) {
                    siteplanOthersRequest = null;
                }
            }
        });
        group.hide();
    }

    //zoom
    var scaleBy = 1.1;
    stage.on('wheel', (e) => {
        // stop default scrolling
        menuNode.style.display = 'none';
        e.evt.preventDefault();

        var oldScale = stage.scaleX();
        var pointer = stage.getPointerPosition();

        var mousePointTo = {
            x: (pointer.x - stage.x()) / oldScale,
            y: (pointer.y - stage.y()) / oldScale,
        };

        // how to scale? Zoom in? Or zoom out?
        let direction = e.evt.deltaY > 0 ? -1 : 1;

        // when we zoom on trackpad, e.evt.ctrlKey is true
        // in that case lets revert direction
        if (e.evt.ctrlKey) {
            direction = -direction;
        }

        var newScale = direction > 0 ? oldScale * scaleBy : oldScale / scaleBy;

        stage.scale({
            x: newScale,
            y: newScale
        });

        group.scale({
            x: 1 / newScale,
            y: 1 / newScale
        })
        var newPos = {
            x: pointer.x - mousePointTo.x * newScale,
            y: pointer.y - mousePointTo.y * newScale,
        };
        stage.position(newPos);
    });

    //clear selction
    var idss, idsb, idst, idstb, ajal, seljal;

    function hapus_seleksi() {
        line_ms.points([0, 0])

        bml_old = 0;
        dtt_first = [];

        idss = stage.find('#sel')[0]; //find selection line
        idst = stage.find('#tsel')[0]; //find selection text


        // ajal = stage.find('#ajal')[0];
        // if(ajal) ajal.destroy();

        //remove point select jalan
        seljal = stage.find('#seljal');
        for (let p = 0; p <= seljal.length; p++) {
            if (seljal[p])
                seljal[p].destroy();
        }
        dtt = [];



        if (idss)
            idss.destroy(); //destroy shape
        if (idst)
            idst.destroy(); //destroy shape

        hapus_seleksi_batch() //destroy multiple selection
    }
    //destroy multiple selection
    function hapus_seleksi_batch() {
        idsb = stage.find('#sel'); //find selection line
        idstb = stage.find('#tsel'); //find selection text

        for (let p = 0; p <= idsb.length; p++) {
            if (idsb[p])
                idsb[p].destroy();

            if (idstb[p])
                idstb[p].destroy();
        }

        batchMask = [];
        batchdtt = [];
        editdtt = [];
        siteplan.draw();
    }

    var editdtt = [];

    //event klik kavling
    masked.on('dblclick', function(e) {
        if (!addMode) {
            if (e.evt.button === 0 && e.target.attrs.id) {
                //open detail modal
                lihat_detail();
            }
        }
    })

    //hide tooltip on tap at siteplan
    siteplan.on('tap', function() {
        group.hide();
    })

    //panggil tooltip saat di tap di ponsel
    siteplan.on('tap', function(e) {
        var data = e.target.attrs;

        //posisi tooltip
        var mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });

        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir +
                ""
            );
            group.moveToTop();
            group.show(); //show tooltip
        }
    })

    siteplan.on('click tap', function(e) {
        var k = e.target, //get shape
            sh = k.attrs, //get attribut shape
            role = $('#pilih-divisi option:selected').val(),
            id_kavling = ''

        if (!sh.id) return false;
        id_kavling = sh.id.substr(3)

        if (!isManualSelectionActive()) {
            addMode = e.evt.ctrlKey;

            //jika hak akses = planning dan pilihan data yang ditampilkan = planning
            //admin, pro
            if ([1, 3, 4, 6, 7, 11].includes(roleid)) {
                if (addMode) {
                    if (sh.data.tipe != "kavling") {
                        return swal('error', 'Terjadi kesalahan', 'Multiple Selection hanya untuk data kavling ')
                    }

                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }
            } else {
                hapus_seleksi();
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
        }
    })

    function refresh_manual_selection_points() {
        dtt = [];
        var sj = stage.find("#seljal");

        for (let u = 0; u < sj.length; u++) {
            dtt.push(Math.trunc(sj[u].attrs.x), Math.trunc(sj[u].attrs.y))
        }

        line_ms.points(dtt)
        masked.batchDraw()
        return dtt;
    }

    function undo_manual_selection() {
        var sj = stage.find("#seljal");

        if (!sj.length) {
            return swal('error', 'Terjadi kesalahan', 'Belum ada titik seleksi manual')
        }

        sj[sj.length - 1].destroy();
        refresh_manual_selection_points();
    }

    $(document).on('keydown', function(e) {
        var tagName = e.target && e.target.tagName ? e.target.tagName.toLowerCase() : '';

        if (!isManualSelectionActive() || ['input', 'textarea', 'select'].includes(tagName)) {
            return;
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            undo_manual_selection();
        }
    });

    stage.on('click tap', function(e) {
        if (isManualSelectionActive()) {
            var pos = this.getRelativePointerPosition();

            var dot = new Konva.Circle({
                x: pos.x,
                y: pos.y,
                fill: 'red',
                radius: 5,
                id: "seljal",
                draggable: true
            })
            manual_selection.add(dot);
            refresh_manual_selection_points();
        }
    })
    masked.add(line_ms)
    masked.add(manual_selection)

    manual_selection.on('dragend', function(e) {
        refresh_manual_selection_points();
    })

    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })
    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })

    //highligh kavling
    siteplan.on('mouseover', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 4);
        sh.setAttr("stroke", "black");
    })

    //hide tooltip
    siteplan.on('mouseout', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 0);
        group.hide();
    })

    function getDistance(p1, p2) {
        return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
    }

    function getCenter(p1, p2) {
        return {
            x: (p1.x + p2.x) / 2,
            y: (p1.y + p2.y) / 2,
        };
    }

    var lastCenter = null;
    var lastDist = 0;
    stage.on('touchmove', function(e) {
        e.evt.preventDefault();
        var touch1 = e.evt.touches[0];
        var touch2 = e.evt.touches[1];

        if (touch1 && touch2) {
            // if the stage was under Konva's drag&drop
            // we need to stop it, and implement our own pan logic with two pointers
            if (stage.isDragging()) {
                stage.stopDrag();
            }

            var p1 = {
                x: touch1.clientX,
                y: touch1.clientY,
            };
            var p2 = {
                x: touch2.clientX,
                y: touch2.clientY,
            };

            if (!lastCenter) {
                lastCenter = getCenter(p1, p2);
                return;
            }
            var newCenter = getCenter(p1, p2);

            var dist = getDistance(p1, p2);

            if (!lastDist) {
                lastDist = dist;
            }

            // local coordinates of center point
            var pointTo = {
                x: (newCenter.x - stage.x()) / stage.scaleX(),
                y: (newCenter.y - stage.y()) / stage.scaleX(),
            };

            var scale = stage.scaleX() * (dist / lastDist);

            stage.scaleX(scale);
            stage.scaleY(scale);

            // calculate new position of the stage
            var dx = newCenter.x - lastCenter.x;
            var dy = newCenter.y - lastCenter.y;

            var newPos = {
                x: newCenter.x - pointTo.x * scale + dx,
                y: newCenter.y - pointTo.y * scale + dy,
            };

            group.scale({
                x: 1 / scale,
                y: 1 / scale
            })

            stage.position(newPos);

            lastDist = dist;
            lastCenter = newCenter;
        }
    });

    stage.on('touchend', function() {
        lastDist = 0;
        lastCenter = null;
    });

    function isi_data() {
        if (editdtt.length == 0)
            return swal('error', 'Terjad Kesalahan', 'Tidak ada kavling yang dipilih')

        //bug isi data with addmode
        if (editdtt.length > 1) {
            swal('error', 'Terjad Kesalahan', 'Tidak bisa merubah data lebih dari 1 kavling')
            hapus_seleksi();
            return;
        }

        let role,
            sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        //jika admin login
        if (roleid == 1)
            role = $('#pilih-divisi option:selected').val()
        else
            role = roleid

        if (role == 7) { //produksi
            open_produksi(sh, role, id_kavling)
        } else if (role == 5) { //legal
            open_legal(sh, role, id_kavling)
        } else if (role == 4) { //mkdt
            open_mkdt(sh, role, id_kavling)
        } else if (role == 3) { //keunagan
            if (!sh.data.id_mkdt) {
                return swal('error', 'Terjad Kesalahan', `Belum ada data konsumen di kavling ${sh.data.nama_jalan}, No. ${sh.data.no_kavling}`)
            }
            open_keuangan(sh, role, id_kavling)
        } else if (role == 6) { //planning
            if (!addMode) {
                hapus_seleksi();
                open_planning(sh, role, id_kavling)
            } else {
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 8) { //sales promotion
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 10) { //pajak
            open_pajak(sh, role, id_kavling);
        }
    }

    $("#fm-mkdt .num").change(function() {
        // total()
    })
    //tidak dipakai
    function total(id_form = "") {
        var harga_jual = removeComma(($(id_form + " #harga_jual").val() == '') ? 0 : $(id_form + " #harga_jual").val()),
            harga_diskon = removeComma(($(id_form + " #harga_diskon").val() == '') ? 0 : $(id_form + " #harga_diskon").val()),
            harga_penambahan = removeComma(($(id_form + " #harga_penambahan").val() == '') ? 0 : $(id_form + " #harga_penambahan").val()),
            harga_administrasi = removeComma(($(id_form + " #harga_administrasi").val() == '') ? 0 : $(id_form + " #harga_administrasi").val()),
            harga_ppn = removeComma(($(id_form + " #harga_ppn").val() == '') ? 0 : $(id_form + " #harga_ppn").val()),
            harga_bphtb = removeComma(($(id_form + " #harga_bphtb").val() == '') ? 0 : $(id_form + " #harga_bphtb").val()),
            harga_biaya_proses = removeComma(($(id_form + " #harga_biaya_proses").val() == '') ? 0 : $(id_form + " #harga_biaya_proses").val()),
            harga_kpr = removeComma(($(id_form + " #harga_kpr").val() == '') ? 0 : $(id_form + " #harga_kpr").val()),
            total_biaya = 0;

        total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

        $(id_form + " #total_biaya").val(total_biaya).keyup();

        // console.log(total_biaya)

        $("#total_biaya2").val(total_biaya).keyup();

    }

    $("#pilih-divisi").select2()
    function fetch_kategori_options() {
        if (!dt_proyek || !dt_proyek.id_proyek) return;
        $.ajax({
            url: base_url + 'siteplan/getKategoriOptions',
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: 'json',
            success: function(res) {
                if (res.token) csrfHash = res.token;
                if (res.success && res.data) {
                    let html = '';
                    let currentCat = '';
                    res.data.forEach(opt => {
                        if (opt.cat !== currentCat) {
                            if (currentCat !== '') {
                                html += `</optgroup>`;
                            }
                            currentCat = opt.cat;
                            html += `<optgroup label="${currentCat}">`;
                        }
                        let countText = opt.count !== undefined ? ` (${opt.count})` : '';
                        let hasPeriode = opt.has_periode ? 'true' : 'false';
                        html += `<option value="${opt.key}" data-has-periode="${hasPeriode}" data-is-kategori="true">${opt.label}${countText}</option>`;
                    });
                    if (currentCat !== '') {
                        html += `</optgroup>`;
                    }
                    
                    $('#pilih-divisi').append(html);
                    $('#pilih-divisi').trigger('change');
                    
                    $('#pilih-divisi').on('change', function() {
                        checkMasalahOptions();
                        checkPeriodeOptions();
                    });
                }
            }
        });
    }

    function checkPeriodeOptions() {
        let showPeriode = false;
        let selectedOption = $('#pilih-divisi option:selected');
        if (selectedOption.data('has-periode') === true || selectedOption.data('has-periode') === 'true') {
            showPeriode = true;
        }
        if (showPeriode) {
            $('#filter-periode-container').stop(true, true).slideDown(300);
        } else {
            $('#filter-periode-container').stop(true, true).slideUp(300);
        }
    }

    function checkMasalahOptions() {
        let hasMasalah = false;
        let selectedValue = $('#pilih-divisi').val();
        if (selectedValue === 'Masalah') {
            hasMasalah = true;
        }
        if (hasMasalah) {
            $('.filter-masalah-options').stop(true, true).slideDown(300);
        } else {
            $('.filter-masalah-options').stop(true, true).slideUp(300);
        }
    }

    window.apply_server_filter = function() {
        filter.id_cluster = $("#filter-id_cluster").val();
        filter.id_jalan = $("#filter-id_jalan").val();
        load_kavling();
        renderActiveFilterTags();
    }

    window.reset_server_filter = function() {
        $('#form-filter-kategori')[0].reset();
        $('#filter-id_cluster').val(null).trigger('change');
        $('#filter-id_jalan').val(null).trigger('change');
        $('#pilih-divisi').val('0').trigger('change');
        filter.id_cluster = '';
        filter.id_jalan = '';
        checkMasalahOptions();
        checkPeriodeOptions();
        load_kavling();
        renderActiveFilterTags();
    }

    $(document).ready(function() {
        fetch_kategori_options();
    });

    function getServerFilterData() {
        let data = {
            kategori: [],
            periode_mulai: $('#filter-periode-mulai').val() || '',
            periode_selesai: $('#filter-periode-selesai').val() || '',
            status_masalah: $('#filter-status-masalah').val() || '',
            periode_masalah_jenis: $('#filter-periode-masalah-jenis').val() || ''
        };
        let selectedOption = $('#pilih-divisi option:selected');
        if (selectedOption.data('is-kategori')) {
            data.kategori.push(selectedOption.val());
        }
        return data;
    }

    function renderActiveFilterTags() {
        let html = '';
        if (filter.id_cluster) {
            const clusterText = $("#filter-id_cluster option:selected").text();
            html += `<span class="badge badge-light-primary mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('cluster')">Cluster: ${clusterText} &times;</span>`;
        }
        
        if (filter.id_jalan) {
            const jalanText = $("#filter-id_jalan option:selected").text();
            html += `<span class="badge badge-light-primary mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('jalan')">Blok: ${jalanText} &times;</span>`;
        }
        
        let selectedOption = $('#pilih-divisi option:selected');
        if (selectedOption.data('is-kategori')) {
            const text = selectedOption.text().replace(/\s\(\d+\)$/, '');
            html += `<span class="badge badge-light-info mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('kategori')">${text} &times;</span>`;
        }

        if ($('#filter-periode-mulai').val() || $('#filter-periode-selesai').val()) {
            const start = $('#filter-periode-mulai').val() || '...';
            const end = $('#filter-periode-selesai').val() || '...';
            html += `<span class="badge badge-light-warning mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('periode')">${start} - ${end} &times;</span>`;
        }

        $('#active-filter-tags').html(html);
    }

    window.removeFilterTag = function(type, val = null) {
        if (type === 'cluster') {
            $('#filter-id_cluster').val(null).trigger('change');
            filter.id_cluster = '';
            $('#filter-id_jalan').val(null).trigger('change');
            filter.id_jalan = '';
        } else if (type === 'jalan') {
            $('#filter-id_jalan').val(null).trigger('change');
            filter.id_jalan = '';
        } else if (type === 'kategori') {
            $('#pilih-divisi').val('0').trigger('change');
        } else if (type === 'periode') {
            $('#filter-periode-mulai').val('');
            $('#filter-periode-selesai').val('');
        }
        checkMasalahOptions();
        checkPeriodeOptions();
        
        // Auto-apply immediately (user feedback)
        window.apply_server_filter();
    }
    // stage.add(siteplan, masked, datal);
    stage.add(siteplan, masked);
    stage.draw();
    siteplanStageReady = true;
    tryInitSiteplanCanvas();

    function downloadURI(uri, name, callback) {
        var link = document.createElement('a');
        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        delete link;

        callback()
    }

    function export_siteplan() {
        $('#btn-export-siteplan').prop("disabled", true);
        $('#btn-export-siteplan').html('Export<i class="fa fa-spinner fa-spin"></i>');

        const stageh = stage.height()
        const stagew = stage.width()
        const stages = stage.scale()
        const stagep = stage.position()

        stage.height(imageObj.height)
        stage.width(imageObj.width)

        stage.position({
            x: 0,
            y: 0
        })

        var dataURL = stage.scale({
            x: 1,
            y: 1
        }).toDataURL({
            pixelRatio: 1
        });

        const nama_file = "Siteplan " + dt_proyek.nama_proyek + " Per " + format_date(today_date) + ".png";

        downloadURI(dataURL, nama_file, function() {
            stage.height(stageh)
            stage.width(stagew)
            stage.scale(stages)
            stage.position(stagep)
            $('#btn-export-siteplan').prop("disabled", false);
            $('#btn-export-siteplan').html('Export');
        })
    }

    //context menu


    //autofit
    function fitStageIntoParentContainer() {
        var container = document.querySelector('#stage-parent');
        if (!container || !container.offsetWidth) {
            return;
        }

        // now we need to fit stage into parent container
        var containerWidth = container.offsetWidth;

        // but we also make the full scene visible
        // so we need to scale all objects on canvas
        var scale = containerWidth / sceneWidth;



        stage.width(sceneWidth * scale);
        stage.height(syncSiteplanMainHeight(sceneHeight));

        // ponytail: zoom-to-fit only on the very first fit; later calls (resize, etc.)
        // just resync canvas dimensions so a user's manual zoom/pan isn't wiped out.
        if (!siteplanFitDone) {
            siteplanFitDone = true;
            new Konva.Tween({
                node: stage,
                duration: 0.5,
                scaleX: scale,
                scaleY: scale,
                easing: Konva.Easings.EaseInOut,
            }).play();
        }
        // stage.scale({
        //     x: scale,
        //     y: scale
        // });
    }

    fitStageIntoParentContainer();

    let siteplanResizeTimer = null;
    $(window).on('resize', function() {
        clearTimeout(siteplanResizeTimer);
        siteplanResizeTimer = setTimeout(function() {
            fitStageIntoParentContainer();
        }, 150);
    });

    function open_setting() {
        // $("#modal-setting-filter").modal()
        $("#modalEwe").modal()
    }

    function filter_option() {
        filter.id_cluster = $("#filter-id_cluster").val()
        filter.id_jalan = $("#filter-id_jalan").val()
        load_kavling()
    }

    function hapus_filter_option() {
        $('#filter-id_cluster').val(null).trigger('change');
        filter_option()
    }

    //select2 cluster
    $("#filter-id_cluster").select2({
        placeholder: "Pilih Cluster",
        allowClear: true,
        ajax: {
            url: base_url + "/cluster/getAll",
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function(params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term,
                    id_proyek: dt_proyek.id_proyek
                };
            },
            processResults: function(r) {
                csrfHash = r.token

                let results = [];
                $.each(r.data, function(index, item) {
                    results.push({
                        id: item[0],
                        text: item[3]
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })
    // on select cluster
    $("#filter-id_cluster").on("change", function(e) {
        $('#filter-id_jalan').val(null).trigger('change');
        if (this.value)
            $("#filter-id_jalan").prop("disabled", false)
        else
            $("#filter-id_jalan").prop("disabled", true)
    });
    $("#filter-id_jalan").select2({
        placeholder: "Pilih Blok",
        allowClear: true,
        ajax: {
            url: base_url + "/jalan/getAll",
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function(params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term,
                    id_cluster: $("#filter-id_cluster").val(),
                    id_proyek: dt_proyek.id_proyek
                };
            },
            processResults: function(r) {
                csrfHash = r.token

                let results = [];
                $.each(r.data, function(index, item) {
                    results.push({
                        id: item[0],
                        text: item[3]
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")


    //context menu
    let currentShape;
    document.getElementById('menu-btn-lihat_detail').addEventListener('click', () => {
        if (currentShape.target.attrs.id) {
            //open detail modal
            lihat_detail();
        }
    });
    var menuNode = document.getElementById('menu');
    window.addEventListener('click', () => {
        // hide menu
        menuNode.style.display = 'none';
    });
    stage.on('contextmenu', function(e) {
        // prevent default behavior
        e.evt.preventDefault();

        if (e.target === stage) {
            // if we are on empty place of the stage we will do nothing
            return;
        }
        currentShape = e;
        // show menu
        menuNode.style.display = 'initial';
        var containerRect = stage.container().getBoundingClientRect();
        menuNode.style.top = stage.getPointerPosition().y + 4 + 'px';
        menuNode.style.left = stage.getPointerPosition().x + 20 + 'px';
    });

    function renderText() {

        $("#btn-renderText").prop("disabled", true);
        $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan <i class="fa fa-spinner fa-spin"></i>');
        // convert DOM into image
        html2canvas(document.querySelector("#keterangan-warna-here"))
            .then((canvas) => {
                // show it inside Konva.Image
                shape_ket.image(canvas);
                $("#btn-renderText").prop("disabled", false);
                $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan');
            });
    }
    $("#btn-simpan_batal_mkdt").click(function(e) {
        e.preventDefault()
    })

    function setBatalPerluRefund(value) {
        const normalized = String(value ?? "0") === "1" ? "1" : "0";
        $(`#modal-batal input[name="batal-perlu_refund"][value="${normalized}"]`).prop("checked", true);
    }

    function simpan_batal() {
        let btn = "#btn-simpan_batal_mkdt"

        if (!palid("batal-keterangan_batal", "", "Keterangan Batal harus diisi"))
            return;


        var form = $('#fm-batal_booking')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + 'mkdt/simpan_batal',
            type: 'post',
            contentType: false,
            processData: false,
            data: fd,
            dataType: 'json',
            beforeSend: function() {
                $(btn).prop("disabled", true);
                $(btn).html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                        $(btn).html('Simpan');
                        $(btn).prop("disabled", false);
                    })
                } else {
                    Swal.fire({
                        //
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $(btn).html('Simpan');
                        $(btn).prop("disabled", false);
                    })
                }
                load_kavling();
                hapus_seleksi();
            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                $(btn).html('Simpan');
                $(btn).prop("disabled", false);
                return;
            }
        });
    }

    function ajukan_batal() {
        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                //timer: 1500
            })
            return;
        }
        $("#fm-batal_booking")[0].reset();
        setBatalPerluRefund(0);
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })
                setBatalPerluRefund(mkdt.perlu_refund);

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal_access_url != null) {
                    src = mkdt.surat_batal_access_url
                }

                $("#list-file_surat_batal").prop("href", resolveFileHref(src))

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }

    function rumahBelumSelesaiEscape(value) {
        return String(value ?? '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function rumahBelumSelesaiDate(value) {
        if (!value || value === '0000-00-00') {
            return '-';
        }

        return format_date(value) || '-';
    }

    function rumahBelumSelesaiProgress(value) {
        const progress = parseFloat(value);
        if (!Number.isFinite(progress)) {
            return 0;
        }

        return Math.max(0, Math.min(100, Math.round(progress)));
    }

    function rumahBelumSelesaiShape(item) {
        return {
            id: 'kav' + item.id_kavling,
            data: {
                id_produksi: item.id_produksi || null,
                id_mkdt: item.id_mkdt || null,
                id_keuangan: item.id_keuangan || null,
                nama_proyek: dt_proyek.nama_proyek,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-',
                tipe: 'kavling'
            },
            data2: {
                id_tipe: item.id_tipe || '-',
                no_tipe_rumah: item.no_tipe_rumah || '-',
                tipe_rumah: item.tipe || '-',
                id_gambar_kerja: item.id_gambar_kerja || null
            }
        };
    }

    function openRumahBelumSelesaiProgress(idKavling) {
        const item = wr_pembangunan.find((row) => String(row.id_kavling) === String(idKavling));
        const sh = findSiteplanKavlingAttrs(idKavling) || (item ? rumahBelumSelesaiShape(item) : null);

        if (!sh || typeof open_fproduksi !== 'function') {
            return swal('error', 'Terjadi Kesalahan', 'Data kavling produksi tidak ditemukan');
        }

        if (typeof editdtt !== 'undefined') {
            editdtt = [sh];
        }

        const openProgressModal = function() {
            open_fproduksi(sh, 7, idKavling);
            if (typeof focusProduksiProgressForm === 'function') {
                setTimeout(focusProduksiProgressForm, 250);
            }
        };

        const $modal = $("#modal-list-rumah-belum-selesai");
        if ($modal.hasClass('show')) {
            $modal.one('hidden.bs.modal', openProgressModal);
            $modal.modal('hide');
        } else {
            openProgressModal();
        }
    }

    function cek_tanggal_pembangunan(x = false) {
        let arr = `<tr><td colspan="7" class="rumah-belum-empty">Tidak ada Data</td></tr>`;
        if (wr_pembangunan.length > 0) {
            let n = 1;
            arr = ''
            wr_pembangunan.forEach(i => {
                const progress = rumahBelumSelesaiProgress(i.progres);
                const daysLeft = Math.round(daysBetween(today_date, i.tanggal_rencana_selesai_pembangunan));
                const isOverdue = daysLeft < 0;
                const kavlingTitle = `${i.nama_jalan || '-'} No. ${i.no_kavling || '-'}`;
                const tipeLabel = `${i.no_tipe_rumah || '-'} / ${i.tipe || '-'}`;
                const keterangan = i.keterangan || '-';
                const progressClass = progress >= 75 ? 'is-high' : '';
                const badgeClass = progress >= 75 ? 'is-blue' : '';

                arr += `
                    <tr>
                        <td class="rumah-belum-no">${n++}</td>
                        <td class="rumah-belum-kavling">
                            <div class="rumah-belum-kavling-title">${rumahBelumSelesaiEscape(kavlingTitle)}</div>
                            <div class="rumah-belum-kavling-meta">Area: ${rumahBelumSelesaiEscape(tipeLabel)}</div>
                        </td>
                        <td class="rumah-belum-progress-cell">
                            <div class="rumah-belum-progress-wrap">
                                <div class="rumah-belum-progress-track">
                                    <div class="rumah-belum-progress-fill ${progressClass}" style="width:${progress}%"></div>
                                </div>
                                <span class="rumah-belum-progress-value">${progress}%</span>
                            </div>
                        </td>
                        <td><span class="rumah-belum-date">${rumahBelumSelesaiEscape(rumahBelumSelesaiDate(i.tanggal_pembangunan))}</span></td>
                        <td>
                            <span class="rumah-belum-date">${rumahBelumSelesaiEscape(rumahBelumSelesaiDate(i.tanggal_rencana_selesai_pembangunan))}</span>
                            <span class="rumah-belum-days ${isOverdue ? 'is-overdue' : ''}">${daysLeft} hari</span>
                        </td>
                        <td><span class="rumah-belum-badge ${badgeClass}">${rumahBelumSelesaiEscape(keterangan)}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary rumah-belum-action" onclick="openRumahBelumSelesaiProgress('${rumahBelumSelesaiEscape(i.id_kavling)}')" title="Ubah data progress">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </td>
                    </tr>
                `
            });
        }
        $("#list-rumah-belum-selesai-here").html(arr)
        if (x == true)
            $("#modal-list-rumah-belum-selesai").modal();
    }

    function formatNomorHP(nomor) {
        // Hapus semua karakter non-digit
        let cleaned = nomor.replace(/\D/g, '');

        // Jika sudah diawali dengan 62, tambahkan tanda +
        if (cleaned.startsWith('+')) {
            return '' + cleaned.slice(1);
        }

        // Jika diawali 0, ubah jadi +62
        if (cleaned.startsWith('0')) {
            return '62' + cleaned.slice(1);
        }

        // Jika sudah diawali dengan 8, asumsikan masih nomor lokal
        if (cleaned.startsWith('8')) {
            return '62' + cleaned;
        }

        // Jika sudah diawali +62 dan hanya simbol + yang dihapus
        return cleaned;
    }

    let siteplanUrgentItems = {};
    let siteplanUrgentLoaded = false;
    let siteplanUrgentLoadQueued = false;
    let siteplanUrgentRequest = null;
    let pendingSiteplanUrgentActionConsumed = false;
    const siteplanUrgentSectionOrder = [
        'tagihan_overdue',
        'tagihan_due',
        'cashout_subkon',
        'sp3k_expire',
        'rencana_akad',
        'pembangunan_telat',
        'perubahan_kavling'
    ];

    function siteplanUrgentEscape(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    function toggleSiteplanUrgentPanel(force) {
        const panel = $("#siteplan-urgent-panel");
        if (force !== false && !siteplanUrgentLoaded && !siteplanUrgentRequest) {
            loadSiteplanUrgentPanel({
                autoOpen: true
            });
        }

        if (force === true) {
            panel.removeClass("hidden");
            return;
        }
        if (force === false) {
            panel.addClass("hidden");
            return;
        }
        panel.toggleClass("hidden");
    }

    function scheduleSiteplanUrgentPanelLoad(options = {}) {
        if (siteplanUrgentLoaded || siteplanUrgentLoadQueued || siteplanUrgentRequest) {
            return;
        }

        siteplanUrgentLoadQueued = true;
        const run = function() {
            siteplanUrgentLoadQueued = false;
            if (!siteplanUrgentLoaded && !siteplanUrgentRequest) {
                loadSiteplanUrgentPanel(options);
            }
        };

        if (typeof window.requestIdleCallback === 'function') {
            window.requestIdleCallback(run, {
                timeout: 2000
            });
        } else {
            window.setTimeout(run, 700);
        }
    }

    function loadSiteplanUrgentPanel(options = {}) {
        if (!dt_proyek || !dt_proyek.id_proyek) {
            return;
        }

        if (siteplanUrgentRequest && siteplanUrgentRequest.readyState !== 4) {
            return;
        }

        siteplanUrgentRequest = $.ajax({
            type: "post",
            url: base_url + "siteplan/urgent/summary",
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: "json",
            beforeSend: function() {
                $("#siteplan-urgent-toggle").removeClass("hidden");
                $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Memuat hal urgent...</div>');
            },
            success: function(r) {
                if (r.token) {
                    csrfHash = r.token;
                    $('input[name="' + csrfName + '"]').val(csrfHash);
                }

                if (!r.success) {
                    $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Gagal memuat hal urgent.</div>');
                    return;
                }

                siteplanUrgentLoaded = true;
                renderSiteplanUrgentPanel(r.summary || {}, options);
            },
            error: function(xhr, st) {
                if (st === 'abort') {
                    return;
                }

                $("#siteplan-urgent-toggle").removeClass("hidden");
                $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Gagal memuat hal urgent.</div>');
            },
            complete: function() {
                siteplanUrgentRequest = null;
            }
        });
    }

    function renderSiteplanUrgentPanel(summary, options = {}) {
        const sections = summary.sections || {};
        const total = parseInt(summary.total || 0, 10);
        let html = '';
        siteplanUrgentItems = {};

        $("#siteplan-urgent-badge").text(total);
        $("#siteplan-urgent-toggle").removeClass("hidden");

        if (total <= 0) {
            $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Belum ada hal urgent untuk proyek ini.</div>');
            if (options.force) {
                toggleSiteplanUrgentPanel(true);
            }
            return;
        }

        $.each(siteplanUrgentSectionOrder, function(_, sectionKey) {
            const section = sections[sectionKey] || {};
            const items = section.items || [];
            if (!items.length) {
                return;
            }

            html += `
                <div class="siteplan-urgent-section">
                    <div class="siteplan-urgent-section-title">
                        <span>${siteplanUrgentEscape(section.label || sectionKey)}</span>
                        <span class="badge badge-light-primary">${items.length}</span>
                    </div>
            `;

            $.each(items, function(i, item) {
                const key = sectionKey + '-' + i;
                const severity = ['danger', 'warning', 'info', 'primary'].includes(item.severity) ? item.severity : 'primary';
                siteplanUrgentItems[key] = item;
                html += `
                    <div class="siteplan-urgent-item is-${severity}" onclick="openSiteplanUrgentItem('${key}')">
                        <div class="siteplan-urgent-item-title">${siteplanUrgentEscape(item.title)}</div>
                        <div class="siteplan-urgent-item-desc">${siteplanUrgentEscape(item.description)}</div>
                        <div class="siteplan-urgent-item-meta">${siteplanUrgentEscape(item.meta)}</div>
                    </div>
                `;
            });

            html += '</div>';
        });

        $("#siteplan-urgent-body").html(html);
        if (options.autoOpen) {
            toggleSiteplanUrgentPanel(true);
        }
    }

    function findSiteplanKavlingAttrs(id_kavling) {
        if (!id_kavling || typeof siteplan === 'undefined') {
            return null;
        }

        try {
            const node = typeof siteplan.findOne === 'function' ?
                siteplan.findOne('#kav' + id_kavling) :
                (siteplan.find('#kav' + id_kavling)[0] || null);
            return node && node.attrs ? node.attrs : null;
        } catch (error) {
            return null;
        }
    }

    function buildMinimalSiteplanShape(item) {
        return {
            id: 'kav' + item.id_kavling,
            data: {
                id_mkdt: item.id_mkdt || null,
                id_keuangan: item.id_keuangan || null,
                nama_proyek: item.nama_proyek || dt_proyek.nama_proyek,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-'
            },
            data2: {
                id_tipe: item.id_tipe || '-',
                no_tipe_rumah: item.id_tipe || '-',
                tipe_rumah: item.id_tipe || '-'
            }
        };
    }

    function openSiteplanUrgentItem(key) {
        const item = siteplanUrgentItems[key];
        if (!item) {
            return;
        }

        toggleSiteplanUrgentPanel(false);

        if (item.type === 'tagihan') {
            return openSiteplanKeuanganFromUrgent(item);
        }

        if (item.type === 'cashout_subkon') {
            return openSiteplanCashoutSubkonFromUrgent(item);
        }

        if (item.id_notif && typeof handleNotificationClick === 'function') {
            return handleNotificationClick(item.id_notif, item.id_kavling, item.type);
        }

        return openSiteplanKavlingFromNotification(item.id_kavling);
    }

    function openSiteplanKeuanganFromUrgent(item) {
        const sh = findSiteplanKavlingAttrs(item.id_kavling) || buildMinimalSiteplanShape(item);
        if (!sh.data || !sh.data.id_mkdt) {
            return openSiteplanKavlingFromNotification(item.id_kavling);
        }

        if (typeof open_keuangan === 'function') {
            return open_keuangan(sh, 3, item.id_kavling);
        }

        return openSiteplanKavlingFromNotification(item.id_kavling);
    }

    function openSiteplanCashoutSubkonFromUrgent(item) {
        if (typeof openCOSubkon !== 'function') {
            return openSiteplanKavlingFromNotification(item.id_kavling);
        }

        return openCOSubkon({
            id_proyek: dt_proyek.id_proyek,
            id_cashout_subkon: item.id_cashout_subkon,
            id_cashout_subkon_detail: item.id_cashout_subkon_detail,
            id_kavlings: [String(item.id_kavling)],
            selected_kavlings: [{
                id_kavling: item.id_kavling,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-'
            }]
        });
    }

    function openSiteplanKavlingFromNotification(id_kavling) {
        const sh = findSiteplanKavlingAttrs(id_kavling);
        if (sh && typeof detail_kavling === 'function') {
            hapus_seleksi();
            editdtt.push(sh);
            drawBorderEdit(sh);
            return detail_kavling(sh, id_kavling);
        }

        return swal('warning', 'Data kavling belum siap', 'Silakan buka detail kavling langsung dari siteplan.');
    }

    function getPendingSiteplanUrgentAction() {
        const params = new URLSearchParams(window.location.search || '');
        const target = params.get('urgent_action');
        if (!target) {
            return null;
        }

        return {
            type: target,
            action_target: target,
            id_proyek: parseInt(params.get('id_proyek') || dt_proyek.id_proyek || 0, 10),
            id_kavling: parseInt(params.get('id_kavling') || 0, 10),
            id_mkdt: parseInt(params.get('id_mkdt') || 0, 10),
            id_keuangan: parseInt(params.get('id_keuangan') || 0, 10),
            id_cashout_subkon: parseInt(params.get('id_cashout_subkon') || 0, 10),
            id_cashout_subkon_detail: parseInt(params.get('id_cashout_subkon_detail') || 0, 10),
            nama_proyek: dt_proyek.nama_proyek
        };
    }

    function handlePendingSiteplanUrgentAction() {
        if (pendingSiteplanUrgentActionConsumed) {
            return;
        }

        const item = getPendingSiteplanUrgentAction();
        if (!item) {
            return;
        }

        pendingSiteplanUrgentActionConsumed = true;
        setTimeout(function() {
            if (item.action_target === 'tagihan' || item.type === 'tagihan') {
                openSiteplanKeuanganFromUrgent(item);
                return;
            }

            if (item.action_target === 'cashout_subkon' || item.type === 'cashout_subkon') {
                openSiteplanCashoutSubkonFromUrgent(item);
                return;
            }

            openSiteplanKavlingFromNotification(item.id_kavling);
        }, 300);
    }

    function openSiteplanKeuanganFromNotification(id_kavling) {
        const sh = findSiteplanKavlingAttrs(id_kavling);
        if (sh && sh.data && sh.data.id_mkdt && typeof open_keuangan === 'function') {
            hapus_seleksi();
            editdtt.push(sh);
            drawBorderEdit(sh);
            return open_keuangan(sh, 3, id_kavling);
        }

        return openSiteplanKavlingFromNotification(id_kavling);
    }

    function cek_jatuh_tempo(x = false) {
        let arr = `<tr><td colspan='6'> Tidak ada Data</td></tr>`;

        $.ajax({
            type: "post",
            url: base_url + 'tagihan/jatuhtempo',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");
                if (r.length > 0) {
                    arr = ''
                    let n = 1
                    var groupedData = {};

                    $.each(r, function(i, v) {
                        if (!groupedData[v.id_mkdt]) {
                            // Jika id_mkdt belum ada, buat objek baru
                            groupedData[v.id_mkdt] = {
                                nama_konsumen: v.nama_konsumen,
                                no_kavling: v.no_kavling,
                                nama_cluster: v.nama_cluster,
                                nama_jalan: v.nama_jalan,
                                nama_proyek: v.nama_proyek,
                                id_tipe: v.id_tipe,
                                tagihan_list: [] // Tempat menampung banyak tagihan
                            };
                        }
                        // Masukkan tagihan ke dalam list
                        groupedData[v.id_mkdt].tagihan_list.push({
                            berita: v.berita_acara,
                            nominal: v.nominal,
                            tgl: v.jatuh_tempo_tgl
                        });
                    });
                    var html = "";
                    var no = 1;

                    $.each(groupedData, function(id, item) {
                        var tagihanHtml = "";

                        // Build tampilan list tagihan di dalam satu kolom
                        $.each(item.tagihan_list, function(idx, tg) {
                            // Format nominal ke rupiah sederhana
                            let formattedNominal = new Intl.NumberFormat('id-ID').format(tg.nominal);

                            tagihanHtml += `
                                <div style="border-bottom: 1px solid #eee; margin-bottom: 5px; padding-bottom: 5px;">
                                    <strong>${tg.berita}</strong>: Rp ${formattedNominal} <br>
                                    <small class="text-muted">Tempo: ${tg.tgl}</small>
                                </div>`;
                        });
                        let sh = {
                            data: {
                                id_mkdt: id,
                                nama_proyek: item.nama_proyek,
                                nama_jalan: item.nama_jalan,
                                no_kavling: item.no_kavling
                            },
                            data2: {
                                no_tipe_rumah: item.id_tipe,
                                tipe_rumah: item.id_tipe
                            }
                        };
                        let shString = JSON.stringify(sh).replace(/"/g, '&quot;');
                        var btn = `<button class="btn btn-outline-primary btn-sm"
                                    onclick="if(confirm('Apakah Anda yakin ingin melakukan pembayaran?')) { $('.modal').modal('hide'); open_keuangan(${shString}, 3, 0); }">
                                    <i class="fas fa-receipt"></i> Bayar
                                </button>`

                        html += `
                            <tr>
                                <td class="text-center">${no++}</td>
                                <td>
                                    <strong>${item.nama_konsumen}</strong><br>
                                    <small>${item.nama_jalan} No. ${item.no_kavling}: Tipe ${item.id_tipe}</small>
                                </td>
                                <td>${tagihanHtml}</td>
                                <td>${btn}</td>
                            </tr>`;
                    });

                    // 3. Masukkan ke dalam tbody
                    $("#list-jatuh-tempo-here").html(html);
                }
                // $("#list-jatuh-tempo-here").html(arr)
                $("#modal-list-jatuh-tempo").modal();

            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        });

    }

    function terima_batal() {

        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                // ////timer: 1500
            })
            return;
        }
        // if (sh.data.is_batal == '0') {
        //     Swal.fire({
        //         //
        //         icon: 'error',
        //         title: "Belum ada data surat batal dari MKDT",
        //         showConfirmButton: false,
        //         // //timer: 1500
        //     })
        //     return;
        // }
        $("#fm-batal_booking")[0].reset();
        setBatalPerluRefund(0);
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })
                setBatalPerluRefund(mkdt.perlu_refund);

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal_access_url != null) {
                    src = mkdt.surat_batal_access_url
                }

                $("#list-file_surat_batal").prop("href", resolveFileHref(src))

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }



    /*************************** cashout subkon ***************************/




    /*************************** End of cashout subkon ***************************/

    $(document).keydown(function(event) {
        if (event.key === 'Escape') {
            hapus_seleksi()
        }
    });


    $("#dt-listrik_jenis").change(function() {
        if (this.value == "PLN") {
            $("#dt-listrik-pln-input-form").removeClass("hidden");
            $("#dt-listrik_disediakan").addClass("hidden");
        } else {
            $("#dt-listrik-pln-input-form").addClass("hidden");
            $("#dt-listrik_disediakan").removeClass("hidden");
        }
    });
    $("#dt-air_jenis").change(function() {
        if (this.value == "Air Tanah") {
            $("#dt-air_tanah-input_form").removeClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else if (this.value == "Komunal Warga") {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").removeClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").removeClass("hidden");
        }
    });
