let sv_url,
    sv_fm,
    sv_btn,
    sv_par,
    wr_pembangunan = [],
    list_jatuhtempo = [],
    filter = {
        id_cluster: [],
        id_jalan: ''
    },
    filterwarna = {
        Filter: null,
        Status: null,
        Subsidi: null,
        Komersil: null,
        Legal: null,
        Pajak: null,
        Target: null,
        MKDT: null,
        Produksi: null,
        Keuangan: null,
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
        targetOverlayLayer = new Konva.Layer({ listening: false }), // overlay border luar target layer
        tiketOverlayLayer = new Konva.Layer({ listening: false }), // overlay tiket masalah layer
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

    var hoverHighlight = SiteplanInteractionHighlight.createHoverHighlight(Konva);
    masked.add(hoverHighlight);

    var siteplanImageReady = false,
        siteplanStageReady = false,
        siteplanCanvasInitialized = false,
        siteplanFitDone = false,
        siteplanFilterReady = false,
        siteplanInitialDataRequested = false;
    var siteplanKavlingRequest = null,
        siteplanOthersRequest = null,
        siteplanLoadSequence = 0;
    let siteplanProblemItems = { kavling: [], others: [] };
    let siteplanTargetItems = [];

    function targetGapBridgeStorageKey() {
        const proyekId = (typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.id_proyek)
            ? dt_proyek.id_proyek
            : 'default';
        return 'sigapp_siteplan_target_gap_bridge_' + proyekId;
    }

    function getSavedTargetGapBridge() {
        const fallback = (typeof SiteplanTargetOverlay !== 'undefined' && typeof SiteplanTargetOverlay.DEFAULT_GAP_BRIDGE === 'number')
            ? SiteplanTargetOverlay.DEFAULT_GAP_BRIDGE
            : 2;
        try {
            const storage = getSiteplanStorage();
            if (!storage) return fallback;
            const raw = storage.getItem(targetGapBridgeStorageKey());
            if (raw === null || raw === '') return fallback;
            if (typeof SiteplanTargetOverlay !== 'undefined' && typeof SiteplanTargetOverlay.normalizeGapBridge === 'function') {
                return SiteplanTargetOverlay.normalizeGapBridge(raw);
            }
            const num = Number(raw);
            return Number.isFinite(num) ? Math.max(0, Math.min(12, Math.round(num * 10) / 10)) : fallback;
        } catch (error) {
            return fallback;
        }
    }

    function getTargetGapBridge() {
        const $input = $('#input-target-gap-bridge');
        const rawVal = $input.length ? $input.val() : undefined;
        if (typeof SiteplanTargetOverlay !== 'undefined' && typeof SiteplanTargetOverlay.normalizeGapBridge === 'function') {
            return SiteplanTargetOverlay.normalizeGapBridge(rawVal);
        }
        const num = Number(rawVal);
        if (rawVal === undefined || rawVal === null || rawVal === '' || !Number.isFinite(num)) {
            return 2;
        }
        return Math.max(0, Math.min(12, Math.round(num * 10) / 10));
    }

    function applyTargetGapBridgeUI(value, persist) {
        const normalized = (typeof SiteplanTargetOverlay !== 'undefined' && typeof SiteplanTargetOverlay.normalizeGapBridge === 'function')
            ? SiteplanTargetOverlay.normalizeGapBridge(value)
            : (Number.isFinite(Number(value)) && String(value).trim() !== '' ? Math.max(0, Math.min(12, Math.round(Number(value) * 10) / 10)) : 2);
        $('#input-target-gap-bridge-range').val(normalized);
        $('#input-target-gap-bridge').val(normalized);
        $('#target-gap-bridge-value').text(normalized + ' px');
        if (persist === true) {
            try {
                const storage = getSiteplanStorage();
                if (storage) {
                    storage.setItem(targetGapBridgeStorageKey(), String(normalized));
                }
            } catch (error) {
                // ignore storage errors
            }
        }
        return normalized;
    }

    function clearSiteplanTargetOverlay() {
        if (typeof SiteplanTargetOverlay !== 'undefined' && typeof SiteplanTargetOverlay.clearOverlay === 'function') {
            SiteplanTargetOverlay.clearOverlay(targetOverlayLayer);
        } else if (typeof targetOverlayLayer !== 'undefined' && targetOverlayLayer) {
            targetOverlayLayer.destroyChildren();
            targetOverlayLayer.batchDraw();
        }
    }

    function syncSiteplanTargetOverlay() {
        if (typeof SiteplanTargetOverlay === 'undefined' || !targetOverlayLayer) return;
        if (!isTargetFilterActive() || siteplanTargetItems.length === 0) {
            clearSiteplanTargetOverlay();
            return;
        }
        const isBaseVisible = function(targetId) {
            const baseNode = siteplan.findOne('#' + targetId);
            if (!baseNode) return false;
            return typeof baseNode.visible === 'function' ? baseNode.visible() : true;
        };
        SiteplanTargetOverlay.buildOverlayShapes(Konva, targetOverlayLayer, siteplanTargetItems, isBaseVisible, {
            strokeColor: '#000000',
            borderWidth: 2.5,
            gapBridge: getTargetGapBridge()
        });
    }

    function syncSiteplanTiketOverlay() {
        if (typeof SiteplanTiketOverlay === 'undefined' || !tiketOverlayLayer) return;
        const allProblemItems = [].concat(siteplanProblemItems.kavling || [], siteplanProblemItems.others || []);
        const isBaseVisible = function(targetId) {
            const baseNode = siteplan.findOne('#' + targetId);
            if (!baseNode) return false;
            return typeof baseNode.visible === 'function' ? baseNode.visible() : true;
        };
        SiteplanTiketOverlay.buildOverlayShapes(Konva, tiketOverlayLayer, allProblemItems, conf, isBaseVisible);
    }

    function updateSiteplanRenderStats() {
        const kavlingNodes = Array.from(siteplan.find('.siteplan-kavling'));
        const stats = {
            kavlingNodes: kavlingNodes.length,
            compositeNodes: kavlingNodes.filter((node) => node.getClassName() === 'Shape').length,
            legacyLineNodes: kavlingNodes.filter((node) => node.getClassName() === 'Line').length,
            subShapeNodes: siteplan.find('.subShape').length,
            dataShapeNodes: siteplan.find('.siteplan-data-shape').length
        };

        const holder = document.getElementById('konva-holder');
        if (holder) {
            holder.dataset.renderStats = JSON.stringify(stats);
        }

        return stats;
    }

    window.getSiteplanRenderStats = function() {
        return updateSiteplanRenderStats();
    };

    function syncSiteplanMainHeight(konva_h) {
        const $card = $('.siteplan-main-card');
        const $cardBody = $card.children('.card-body').first();
        const fallbackHeight = Math.ceil(Number(konva_h) || sceneHeight || 0);

        if (!$card.length) {
            return fallbackHeight;
        }

        const paddingY = (parseFloat($cardBody.css('padding-top')) || 0) +
            (parseFloat($cardBody.css('padding-bottom')) || 0);

        if ($(window).width() < 768) {
            const mobileNav = document.getElementById('sigapp-mobile-bottom-nav');
            const mobileNavRect = mobileNav ? mobileNav.getBoundingClientRect() : null;
            const mobileNavStyle = mobileNav ? getComputedStyle(mobileNav) : null;
            const mobileNavVisible = mobileNavRect && mobileNavStyle &&
                mobileNavStyle.display !== 'none' && mobileNavRect.height > 0;
            const bottomGap = mobileNavVisible
                ? Math.max(12, window.innerHeight - mobileNavRect.top + 12)
                : 88;
            const cardRect = $card[0].getBoundingClientRect();
            const availableCardHeight = Math.max(0, window.innerHeight - cardRect.top - bottomGap);
            const availableContentHeight = Math.max(0, availableCardHeight - paddingY);
            const contentHeight = Math.ceil(Math.max(fallbackHeight, availableContentHeight));

            $card.css({
                '--siteplan-main-card-height': Math.ceil(contentHeight + paddingY) + 'px',
                '--siteplan-main-content-height': contentHeight + 'px'
            });
            $('#filter-side').css('height', 'auto');
            return contentHeight;
        }

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

    function allAvailableClusterIds() {
        return (siteplanClusterOptions || []).map(function(item) {
            return String(item.id_cluster);
        });
    }

    function selectedClusterIds() {
        const rawVal = $('#filter-id_cluster').val();
        const rawArray = Array.isArray(rawVal) ? rawVal : (rawVal == null || rawVal === '' ? [] : [rawVal]);
        if (rawArray.includes('all')) {
            return allAvailableClusterIds();
        }

        if (typeof SiteplanFilterState === 'undefined') {
            return Array.isArray(filter.id_cluster) ? filter.id_cluster : [];
        }

        return SiteplanFilterState.normalizeIds(rawVal);
    }

    function setSiteplanClusterHint(show, message) {
        const $hint = $('#siteplan-cluster-hint');
        if (!$hint.length) return;

        if (message) $hint.text(message);
        $hint.toggle(Boolean(show));
        $hint.toggleClass('text-primary', Boolean(show));
    }

    function showSiteplanClusterRequiredAlert() {
        if (typeof Swal === 'undefined') return;
        if (typeof Swal.isVisible === 'function' && Swal.isVisible()) return;

        Swal.fire({
            icon: 'warning',
            title: 'Cluster belum dipilih',
            text: 'Silakan pilih minimal satu cluster terlebih dahulu.',
            confirmButtonText: 'Pilih Cluster'
        }).then(function(result) {
            if (!result || !result.isConfirmed) return;

            const focusClusterSelect = function() {
                const select = document.getElementById('filter-id_cluster');
                if (!select) return;

                const selection = select.nextElementSibling
                    ? select.nextElementSibling.querySelector('.select2-selection')
                    : null;
                const target = selection
                    ? (selection.querySelector('.select2-search__field') || selection)
                    : select;

                target.focus({ preventScroll: true });
                if (window.matchMedia('(max-width: 767.98px)').matches) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            };

            if (window.matchMedia('(max-width: 767.98px)').matches) {
                if (window.SIGAPPMobileBottomNav &&
                    typeof window.SIGAPPMobileBottomNav.open === 'function') {
                    window.SIGAPPMobileBottomNav.open('filter');
                } else {
                    $('#sigapp-mobile-bottom-nav [data-sigapp-mobile-nav="filter"]').trigger('click');
                }

                window.setTimeout(focusClusterSelect, 100);
                return;
            }

            focusClusterSelect();
        });
    }

    function clearSiteplanDataShapes() {
        clearShapeFilterSelection();
        siteplan.find('.siteplan-data-shape').forEach(shape => shape.destroy());
        siteplanProblemItems = { kavling: [], others: [] };
        siteplanTargetItems = [];
        clearSiteplanTargetOverlay();
        if (typeof SiteplanTiketOverlay !== 'undefined' && typeof SiteplanTiketOverlay.clearOverlay === 'function') {
            SiteplanTiketOverlay.clearOverlay(tiketOverlayLayer);
        } else if (typeof tiketOverlayLayer !== 'undefined' && tiketOverlayLayer) {
            tiketOverlayLayer.destroyChildren();
            if (tiketOverlayLayer._tiketAnimation && typeof tiketOverlayLayer._tiketAnimation.stop === 'function') {
                tiketOverlayLayer._tiketAnimation.stop();
                tiketOverlayLayer._tiketAnimation = null;
            }
            tiketOverlayLayer.batchDraw();
        }
        if (typeof hoverHighlight !== 'undefined' && hoverHighlight) {
            hoverHighlight.hide();
        }
        filterwarnahitung = {};
        $('#keterangan-warna-here').empty();
        updateSiteplanRenderStats();
        siteplan.batchDraw();
        masked.batchDraw();
    }

    function tryLoadInitialSiteplanData() {
        if (!siteplanCanvasInitialized || !siteplanFilterReady || siteplanInitialDataRequested) {
            return;
        }

        siteplanInitialDataRequested = true;
        filter.id_cluster = selectedClusterIds();
        if (filter.id_cluster.length === 0) {
            clearSiteplanDataShapes();
            setSiteplanClusterHint(true, 'Pilih minimal satu cluster untuk menampilkan data siteplan.');
            showSiteplanClusterRequiredAlert();
            return;
        }

        setSiteplanClusterHint(false);
        load_kavling(roleid == 1 || roleid == 7);
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

        // Data baru dimuat setelah riwayat cluster selesai dipulihkan.
        tryLoadInitialSiteplanData();

        // $("#pilih-divisi").select2("val", roleid)
        // change_div();
        load_menu();
        loadSiteplanMenuItems(); // load dynamic context menu items


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

    window.targetInteractiveMode = Boolean(window.targetInteractiveMode);

    window.isTargetSetModeActive = function() {
        return Boolean(window.targetInteractiveMode);
    };

    let targetCanvasRefreshTimer = null;
    window.scheduleTargetCanvasRefresh = function(delayMs) {
        const wait = typeof delayMs === 'number' && delayMs >= 0 ? delayMs : 450;
        if (targetCanvasRefreshTimer) {
            clearTimeout(targetCanvasRefreshTimer);
        }
        targetCanvasRefreshTimer = setTimeout(function() {
            targetCanvasRefreshTimer = null;
            if (typeof load_kavling === 'function') {
                load_kavling();
            }
        }, wait);
    };

    function isTargetFilterActive() {
        return $('#toggle-tampilkan-target').prop('checked') === true;
    }

    function selectedTargetYear() {
        return String($('#filter-tahun-target').val() || '').trim();
    }

    function selectedTargetOtherMode() {
        return String($('#filter-target-other-mode').val() || 'default').trim() || 'default';
    }

    function selectedTargetKavlingMode() {
        return String($('#filter-target-kavling-mode').val() || 'default').trim() || 'default';
    }

    let targetTipeColorMap = {};
    const targetTipePalette = [
        '#2563eb', '#059669', '#d97706', '#9333ea',
        '#e11d48', '#0891b2', '#65a30d', '#c026d3',
        '#ea580c', '#4f46e5', '#0d9488', '#db2777'
    ];

    function resolveTargetTipeColor(tipeName) {
        const key = String(tipeName || '').trim() || 'Tanpa Tipe';
        if (conf[key] && conf[key].fill) {
            return conf[key].fill;
        }
        if (key === 'Tanpa Tipe') {
            return '#64748b';
        }
        if (targetTipeColorMap[key]) {
            return targetTipeColorMap[key];
        }

        let hash = 0;
        for (let i = 0; i < key.length; i++) {
            hash = ((hash << 5) - hash) + key.charCodeAt(i);
            hash |= 0;
        }
        const startIdx = Math.abs(hash) % targetTipePalette.length;
        const usedColors = new Set(Object.values(targetTipeColorMap));
        let chosen = targetTipePalette[startIdx];
        for (let offset = 0; offset < targetTipePalette.length; offset++) {
            const candidate = targetTipePalette[(startIdx + offset) % targetTipePalette.length];
            if (!usedColors.has(candidate)) {
                chosen = candidate;
                break;
            }
        }
        targetTipeColorMap[key] = chosen;
        return chosen;
    }

    function hasValidTargetDate(val) {
        if (typeof val !== 'string') return false;
        const trimmed = val.trim();
        return /^\d{4}-\d{2}-\d{2}$/.test(trimmed)
            && trimmed !== '0000-00-00'
            && !isNaN(new Date(trimmed).getTime());
    }

    function syncTargetMenuVisibility() {
        if (typeof roleid === 'undefined' || Number(roleid) !== 1) return;
        const va = String($('#pilih-divisi option:selected').val() || '0');
        if (va === '0') {
            $('#target_menu').toggleClass('hidden', !isTargetFilterActive());
        }
    }
    window.syncTargetMenuVisibility = syncTargetMenuVisibility;

    function syncTargetYearDropdown(targetYears, preferredYear) {
        const $select = $('#filter-tahun-target');
        if (!$select.length) return;

        const nowYear = new Date().getFullYear();
        const minPresetYear = nowYear - 1;
        const maxPresetYear = nowYear + 3;
        const previousVal = selectedTargetYear();
        const prefVal = preferredYear ? String(preferredYear).trim() : '';
        const barYearVal = String($('#target-bar-year').val() || '').trim();
        const isTargetInteractive = window.isTargetSetModeActive() || isTargetFilterActive();

        const yearSet = new Set();
        (Array.isArray(targetYears) ? targetYears : []).forEach(function(y) {
            const num = parseInt(String(y || '').trim(), 10);
            if (num >= 2000 && num <= 2100) {
                yearSet.add(String(num));
            }
        });

        [prefVal, barYearVal, previousVal].forEach(function(candidate) {
            const num = parseInt(candidate || '', 10);
            if (num >= 2000 && num <= 2100) {
                const inPresetRange = num >= minPresetYear && num <= maxPresetYear;
                if (inPresetRange || isTargetInteractive || candidate === prefVal) {
                    yearSet.add(String(num));
                }
            }
        });

        const years = Array.from(yearSet).sort(function(a, b) {
            return Number(b) - Number(a);
        });

        $select.empty();

        if (years.length === 0) {
            $select.append('<option value="">Belum ada target</option>');
            $select.prop('disabled', true);
            return;
        }

        $select.prop('disabled', false);
        years.forEach(function(year) {
            $select.append($('<option>', { value: year, text: year }));
        });

        const currentYear = String(nowYear);
        if (prefVal && years.includes(prefVal)) {
            $select.val(prefVal);
        } else if (window.isTargetSetModeActive() && barYearVal && years.includes(barYearVal)) {
            $select.val(barYearVal);
        } else if (previousVal && years.includes(previousVal)) {
            $select.val(previousVal);
        } else if (years.includes(currentYear)) {
            $select.val(currentYear);
        } else {
            $select.val(years[0]);
        }
    }
    window.syncTargetYearDropdown = syncTargetYearDropdown;

    function resolveTargetCanvasHit(row, rawHargaAkhir, targetInfo, defaultHit) {
        const activeYear = selectedTargetYear();
        const isTargetKavling = Boolean(targetInfo && (!activeYear || String(targetInfo.tahun_target) === activeYear));
        const isBatal = (row.is_batal == 1 || row.status_mkdt === 'Batal');
        const hasSubValue = function(val) {
            return val !== null && val !== undefined && val !== '';
        };
        const rawSub = hasSubValue(row.is_subsidi)
            ? row.is_subsidi
            : (hasSubValue(row.mkdt_is_subsidi)
                ? row.mkdt_is_subsidi
                : (targetInfo && hasSubValue(targetInfo.target_is_subsidi) ? targetInfo.target_is_subsidi : null));
        const targetSubsidi = (String(rawSub) === '1') ? 'Subsidi' : 'Komersil';

        const isAkad = !isBatal && (
            row.akad == 1
            || hasValidTargetDate(row.akad_tgl)
        );
        const isBooking = !isBatal && !isAkad && (
            row.status_mkdt === 'Booking'
            || row.status_mkdt === 'Akad'
            || row.sp3k == 1
            || hasValidTargetDate(row.sp3k_tgl)
            || row.wawancara == 1
            || hasValidTargetDate(row.wawancara_tgl)
            || hasValidTargetDate(row.booking_tgl)
            || row.akad_indent == 1
            || hasValidTargetDate(row.akad_indent_tgl)
        );
        const hasConsumer = isAkad || isBooking;
        const otherMode = selectedTargetOtherMode();
        const targetMode = selectedTargetKavlingMode();

        // 1. Kavling dengan konsumen aktif (Akad / Booking) — berlaku untuk target maupun selain target
        if (hasConsumer) {
            if (otherMode === 'default') {
                return {
                    hit: defaultHit,
                    preserveDefaultVisual: true,
                    isTargetKavling: isTargetKavling,
                    targetStroke: undefined,
                    targetStrokeWidth: 0
                };
            }

            if (otherMode === 'split_4') {
                const statusLabel = (isAkad ? 'Akad ' : 'Booking ') + targetSubsidi;
                if (conf[statusLabel]) {
                    set_fill2(statusLabel);
                }
                return {
                    hit: {
                        fill: statusLabel,
                        tipe: 'Target'
                    },
                    preserveDefaultVisual: false,
                    isTargetKavling: isTargetKavling,
                    targetStroke: undefined,
                    targetStrokeWidth: 0
                };
            }

            if (otherMode === 'merge_2') {
                return {
                    hit: {
                        fill: isAkad ? 'Sudah Akad' : 'Booking',
                        tipe: 'Target'
                    },
                    preserveDefaultVisual: false,
                    isTargetKavling: isTargetKavling,
                    targetStroke: undefined,
                    targetStrokeWidth: 0
                };
            }

            return {
                hit: {
                    fill: 'Sudah Memiliki Konsumen',
                    tipe: 'Target'
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        // 2. Kavling Selain Target & belum berkonsumen (atau batal)
        if (!isTargetKavling) {
            if (otherMode === 'default') {
                return {
                    hit: defaultHit,
                    preserveDefaultVisual: true,
                    isTargetKavling: isTargetKavling,
                    targetStroke: undefined,
                    targetStrokeWidth: 0
                };
            }

            set_fill('#e2e8f0', '#000000', '0', null);
            return {
                hit: {
                    fill: 'Bukan Target',
                    tipe: 'Target'
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        // 3. Kavling Target & belum berkonsumen (atau target yang batal)
        if (targetMode === 'single_color') {
            return {
                hit: {
                    fill: 'Kavling Target',
                    tipe: 'Target'
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        if (targetMode === 'by_tipe') {
            const rawTipe = (targetInfo && (targetInfo.target_tipe_rumah || targetInfo.tipe_manual || targetInfo.tipe_label))
                || row.tipe_rumah
                || 'Tanpa Tipe';
            const tipeName = String(rawTipe).trim() || 'Tanpa Tipe';
            return {
                hit: {
                    fill: tipeName,
                    color_fill: 'Tipe Target:' + tipeName,
                    tipe: 'Tipe Target'
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        // targetMode === 'default'
        if (otherMode === 'default') {
            return {
                hit: defaultHit,
                preserveDefaultVisual: true,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        if ((rawHargaAkhir && String(rawHargaAkhir) !== '0') || (parseFloat(row.hargajual || 0) > 0)) {
            return {
                hit: {
                    fill: set_fill2(row.tipe_rumah),
                    tipe: targetSubsidi
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        if (!conf['Def']) {
            set_fill('#fcffd9', '#000000', '0', null);
            return {
                hit: {
                    fill: 'Def',
                    tipe: 'Target'
                },
                preserveDefaultVisual: false,
                isTargetKavling: isTargetKavling,
                targetStroke: undefined,
                targetStrokeWidth: 0
            };
        }

        return {
            hit: {
                fill: set_fill2('Def'),
                tipe: 'Target'
            },
            preserveDefaultVisual: false,
            isTargetKavling: isTargetKavling,
            targetStroke: undefined,
            targetStrokeWidth: 0
        };
    }

    //refresh kavling setelah ganti divisi
    $("#pilih-divisi").change(function() {
        change_div()
    });

    $("#toggle-tampilkan-target").on("change", function() {
        if (this.checked) {
            $("#filter-target-tahun-container").stop(true, true).slideDown(200);
        } else {
            $("#filter-target-tahun-container").stop(true, true).slideUp(200);
        }
        syncTargetMenuVisibility();
        if (typeof window.apply_server_filter === "function") {
            window.apply_server_filter();
        }
    });

    $("#filter-tahun-target").on("change", function() {
        if (isTargetFilterActive() && typeof window.apply_server_filter === "function") {
            window.apply_server_filter();
        }
    });

    $("#filter-target-other-mode, #filter-target-kavling-mode").on("change", function() {
        if (isTargetFilterActive() && typeof window.apply_server_filter === "function") {
            window.apply_server_filter();
        }
    });

    $(document).on('click', '#btn-toggle-target-border-settings', function() {
        const $btn = $(this);
        const $panel = $('#target-border-settings-panel');
        if (!$panel.length) return;
        const willOpen = !$panel.is(':visible');
        $btn.toggleClass('is-active', willOpen);
        $btn.attr('aria-expanded', willOpen ? 'true' : 'false');
        $panel.stop(true, true).slideToggle(180, function() {
            const isOpen = $panel.is(':visible');
            $btn.toggleClass('is-active', isOpen);
            $btn.attr('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    $(document).on('input change', '#input-target-gap-bridge-range', function() {
        applyTargetGapBridgeUI(this.value, true);
        if (isTargetFilterActive()) {
            syncSiteplanTargetOverlay();
        }
    });

    $(document).on('input change', '#input-target-gap-bridge', function() {
        if (this.value === '') return;
        applyTargetGapBridgeUI(this.value, true);
        if (isTargetFilterActive()) {
            syncSiteplanTargetOverlay();
        }
    });

    $(document).on('blur', '#input-target-gap-bridge', function() {
        applyTargetGapBridgeUI(this.value, true);
        if (isTargetFilterActive()) {
            syncSiteplanTargetOverlay();
        }
    });

    function isManualSelectionActive() {
        return $("#tambah_jalan").prop("checked") || $("#produksi_tambah_jalan").prop("checked");
    }

    function change_div() {
        $("#tambah_jalan").prop("checked", 0)
        $("#produksi_tambah_jalan").prop("checked", 0)
        if (typeof window.exitTargetSetMode === 'function') {
            window.exitTargetSetMode();
        }
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

        syncTargetMenuVisibility();

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
                syncTargetMenuVisibility();
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
    const selectedShapeLegendKeys = new Set();

    function set_fill2(e) { //test set fill dengan config dari db
        if (!conf[e]) {
            e = "Warna Tidak Ditemukan";
        }
        
        if (conf[e]) {
            set_fill(conf[e].fill, conf[e].stroke, conf[e].strokeWidth, conf[e].dashed);
        } else {
            set_fill('#d1d5db', '#000000', '0', null); // default
        }
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
        if (kategori === 'Bukan Target') return '#e2e8f0';
        if (kategori === 'Def' && !conf['Def']) return '#fcffd9';
        if (kategori === 'Sudah Akad') return (conf['Akad'] && conf['Akad'].fill) || '#16a34a';
        if (kategori === 'Booking') return '#ff00b7';
        if (kategori === 'Sudah Memiliki Konsumen') return '#0ea5e9';
        if (kategori === 'Kavling Target') return '#f59e0b';
        if (kategori === 'Akad Subsidi') return '#16a34a';
        if (kategori === 'Akad Komersil') return '#15803d';
        if (kategori === 'Booking Subsidi') return '#ec4899';
        if (kategori === 'Booking Komersil') return '#a855f7';
        if (String(kategori).indexOf('Tipe Target:') === 0) {
            return resolveTargetTipeColor(String(kategori).slice('Tipe Target:'.length).trim());
        }
        if (kategori == 'Belum Target') return '#f8fafc';

        if (String(kategori).indexOf('Target ') === 0) {
            const colors = ['#f59e0b', '#14b8a6', '#3b82f6', '#a855f7', '#ef4444', '#22c55e'];
            const year = parseInt(String(kategori).replace('Target ', ''), 10);
            return colors[Math.abs(year || 0) % colors.length];
        }
        return '#d1d5db';
    }

    function get_kategori_stroke_width(kategori) {
        const configured = conf[kategori] ? Number(conf[kategori].strokeWidth) : 0;
        return Number.isFinite(configured) ? Math.max(0, configured) : 0;
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
        const groupName = fill.tipe || 'Status';
        const countKey = groupName + ':' + fill.fill;
        const count = filterwarnahitung[countKey] || 0;
        filterwarnahitung[countKey] = count + 1;

    }

    function createLegendEntry(groupName, configName, item, kind) {
        const colorConfigName = item.color_config_name || configName;
        const background = typeof SiteplanCompositeShape !== 'undefined'
            && typeof SiteplanCompositeShape.legendBackground === 'function'
            ? SiteplanCompositeShape.legendBackground(item, get_kategori_color)
            : get_kategori_color(colorConfigName);

        return {
            key: SiteplanShapeFilter.key(groupName, configName),
            background: background,
            kind: kind,
            strokeWidth: kind === 'marker' ? get_kategori_stroke_width(colorConfigName) : 0
        };
    }

    function registerLegendItem(groupName, item, kind, counted) {
        const configName = item.config_name || 'Def';
        filterwarna[groupName][configName] = createLegendEntry(groupName, configName, item, kind);

        const countKey = groupName + ':' + configName;
        if (!counted[countKey]) {
            filterwarnahitung[countKey] = (filterwarnahitung[countKey] || 0) + 1;
            counted[countKey] = true;
        }
    }

    function registerCompositeLegend(rows) {
        const counted = {};

        (rows || []).forEach(function(row) {
            const groupName = row.label || row.key || 'Status';
            filterwarna[groupName] = filterwarna[groupName] || {};

            (row.segments || []).forEach(function(item) {
                registerLegendItem(groupName, item, 'fill', counted);
            });
            (row.markers || []).forEach(function(item) {
                registerLegendItem(groupName, item, 'marker', counted);
            });
        });
    }

    function compositeRowSummary(row) {
        const segments = row.segments || [];
        const showRatio = segments.length > 1;
        const labels = segments.map(function(segment) {
            const ratio = Math.round((Number(segment.ratio) || 0) * 100);
            return segment.config_name + (showRatio ? ' ' + ratio + '%' : '');
        });
        const markers = (row.markers || []).map(function(marker) {
            return marker.config_name;
        });

        return labels.concat(markers.map(function(marker) { return 'Penanda ' + marker; })).join(' / ');
    }

    function compositeTooltipLines(row) {
        if (typeof SiteplanCompositeShape !== 'undefined' && typeof SiteplanCompositeShape.tooltipLines === 'function') {
            return SiteplanCompositeShape.tooltipLines(row);
        }

        return [(row.label || row.key) + ': ' + compositeRowSummary(row)];
    }

    function setSiteplanTooltip(attrs) {
        if (!attrs.data || !attrs.data.nama_jalan || !attrs.data.no_kavling) {
            return false;
        }

        const lines = [
            attrs.data.nama_jalan + ' No. ' + attrs.data.no_kavling,
            attrs.data2.no_tipe_rumah,
            attrs.data2.tipe_rumah + ' ( ' + attrs.data.luas_tanah + ' / ' + attrs.data.status_tanah + ')',
            'HJ: Rp. ' + attrs.data2.harga_akhir
        ];

        if (isTargetFilterActive()) {
            const targetInfo = attrs.data2 ? attrs.data2.target : null;
            const activeYear = selectedTargetYear();
            const matchesTarget = targetInfo && (!activeYear || String(targetInfo.tahun_target) === activeYear);
            let targetLine = matchesTarget ? ('Target: Tahun ' + targetInfo.tahun_target) : 'Target: Bukan Target';
            if (matchesTarget && targetInfo) {
                const targetTipe = String(targetInfo.target_tipe_rumah || targetInfo.tipe_manual || targetInfo.tipe_label || '').trim();
                if (targetTipe) {
                    targetLine += ' (' + targetTipe + ')';
                }
            }
            lines.push(targetLine);
        }

        (attrs.tooltipRows || attrs.visualRows || []).forEach(function(row) {
            compositeTooltipLines(row).forEach(function(line) {
                lines.push(line);
            });
        });

        tooltip.text(lines.join('\n'));
        tooltipbg.height(Math.max(57, tooltip.height() + 10));
        return true;
    }

    function set_keterangan_warna() {
        const $legend = $("#keterangan-warna-here").empty();

        //filter
        $("#filter-kategori option").remove()
        $("#filter-kategori").append(`<option value="">Semua</option>`);
        $.each(filterwarna, function(i, v) {
            if (v) {
                const groupLabel = i + (i == 'Subsidi' || i == 'Komersil' ? ' Dipasarkan' : '');
                const $divider = $('<div>', { class: 'divider divider-left' })
                    .append($('<div>', { class: 'divider-text' }).text(groupLabel));
                $legend.append($divider);

                const statusOrder = i === 'Legal' ? legalStatusOrder : (i === 'Pajak' ? pajakStatusOrder : null);
                const sortedKeys = statusOrder ?
                    statusOrder.filter(key => Object.prototype.hasOwnProperty.call(v, key)).concat(Object.keys(v).filter(key => !statusOrder.includes(key)).sort()) :
                    Object.keys(v).sort();

                // Step 2: Create a new object with sorted keys
                const sortedObj = {};
                sortedKeys.forEach(key => {
                    sortedObj[key] = v[key];
                });

                $.each(sortedObj, function(x, rawEntry) {
                    //untuk tambah option di filter
                    $("#filter-kategori").append($('<option>').val(x).text(x));

                    const entry = typeof rawEntry === 'string' ? {
                        key: SiteplanShapeFilter.key(i, x),
                        background: rawEntry,
                        kind: 'fill',
                        strokeWidth: 0
                    } : rawEntry;
                    const label = (x == "Def") ? "Data yang bisa diolah" : x;
                    const count = filterwarnahitung[i + ':' + x] ?? filterwarnahitung[x] ?? 0;
                    const $item = $('<label>', { class: 'siteplan-legend-item' });
                    const $checkbox = $('<input>', {
                        type: 'checkbox',
                        class: 'siteplan-shape-filter-checkbox',
                        'aria-label': 'Filter shape ' + groupLabel + ' ' + label
                    }).prop('checked', selectedShapeLegendKeys.has(entry.key));
                    $checkbox.data('shapeKey', entry.key);

                    const $swatch = $('<span>', { class: 'siteplan-legend-swatch' });
                    if (entry.kind === 'marker') {
                        const markerWidth = Math.min(6, Math.max(2, Number(entry.strokeWidth) || 3));
                        $swatch.addClass('siteplan-legend-swatch-marker').append(
                            $('<span>', { class: 'siteplan-legend-marker-line' }).css({
                                background: entry.background,
                                height: markerWidth + 'px'
                            })
                        );
                    } else {
                        $swatch.css('background', entry.background);
                    }

                    $item.append(
                        $checkbox,
                        $swatch,
                        $('<span>', { class: 'siteplan-legend-label' }).text(label + ' (' + count + ')')
                    );
                    $legend.append($item);
                })
            }
        })
    }

    function clearShapeFilterSelection() {
        selectedShapeLegendKeys.clear();
        $('#keterangan-warna-here .siteplan-shape-filter-checkbox').prop('checked', false);
    }

    function applyShapeVisibilityFilter() {
        hapus_seleksi();
        hideKavlingHover();
        group.hide();

        siteplan.find('.siteplan-data-shape').forEach(function(node) {
            const filterable = typeof node.hasName === 'function' && node.hasName('siteplan-kavling');
            const legendKeys = typeof node.getAttr === 'function' ? node.getAttr('legendKeys') : [];
            node.visible(SiteplanShapeFilter.shouldShow(filterable, legendKeys, selectedShapeLegendKeys));
        });

        syncSiteplanTargetOverlay();
        siteplan.batchDraw();
        masked.batchDraw();
    }

    $(document).on('change', '#keterangan-warna-here .siteplan-shape-filter-checkbox', function() {
        const shapeKey = $(this).data('shapeKey');
        if (this.checked) {
            selectedShapeLegendKeys.add(shapeKey);
        } else {
            selectedShapeLegendKeys.delete(shapeKey);
        }

        applyShapeVisibilityFilter();
    });
    function getHitForFilter(filterKey, row, subsidi) {
        if (filterKey === 'Masalah') {
            const prio = row.prioritas_masalah ? row.prioritas_masalah.toLowerCase() : 'normal';
            if (prio === 'urgent') return { fill: 'Masalah Urgent', tipe: 'Filter' };
            if (prio === 'medium') return { fill: 'Masalah Medium', tipe: 'Filter' };
            if (prio === 'low') return { fill: 'Masalah Low', tipe: 'Filter' };
            return { fill: 'Masalah Normal', tipe: 'Filter' };
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
        clearShapeFilterSelection();
        filter.id_cluster = selectedClusterIds();

        if (siteplanKavlingRequest && siteplanKavlingRequest.readyState !== 4) {
            siteplanKavlingRequest.abort();
        }
        if (siteplanOthersRequest && siteplanOthersRequest.readyState !== 4) {
            siteplanOthersRequest.abort();
        }

        if (filter.id_cluster.length === 0) {
            filter.id_jalan = '';
            $('#filter-id_jalan').val(null).trigger('change.select2').prop('disabled', true);
            clearSiteplanDataShapes();
            setSiteplanClusterHint(true, 'Pilih minimal satu cluster untuk menampilkan data siteplan.');
            showSiteplanClusterRequiredAlert();
            $('#loading').addClass('hidden');
            return;
        }

        setSiteplanClusterHint(false);

        hapus_seleksi();
        filterwarna = {
            Filter: null,
            Status: null,
            Subsidi: null,
            Komersil: null,
            Legal: null,
            Pajak: null,
            Target: null,
            MKDT: null,
            Produksi: null,
            Keuangan: null,
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

        siteplan.find('.siteplan-data-shape').forEach(shape => shape.destroy());
        siteplanProblemItems = { kavling: [], others: [] };
        siteplanTargetItems = [];
        clearSiteplanTargetOverlay();
        if (typeof SiteplanTiketOverlay !== 'undefined' && typeof SiteplanTiketOverlay.clearOverlay === 'function') {
            SiteplanTiketOverlay.clearOverlay(tiketOverlayLayer);
        } else if (typeof tiketOverlayLayer !== 'undefined' && tiketOverlayLayer) {
            tiketOverlayLayer.destroyChildren();
            if (tiketOverlayLayer._tiketAnimation && typeof tiketOverlayLayer._tiketAnimation.stop === 'function') {
                tiketOverlayLayer._tiketAnimation.stop();
                tiketOverlayLayer._tiketAnimation = null;
            }
            tiketOverlayLayer.batchDraw();
        }

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
                targetYears = result.target_years || [],
                    data2,
                    hit,
                    tp_rumah,
                    subsidi;

                siteplanTargetItems = [];
                targetTipeColorMap = {};
                filterwarna['Tipe Target'] = null;
                syncTargetYearDropdown(targetYears);
                if (isTargetFilterActive()) {
                    renderActiveFilterTags();
                }



                for (var p = 0; p < r.length; p++) {
                    const rawHargaAkhir = r[p].harga_akhir;
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
                            if (r[p].is_kpr != 1) {
                                hit = set_fill2('Akad ' + subsidi);
                            } else {
                                hit = set_fill2('Lunas')
                                if (r[p].status_mkdt == 'Akad') {
                                    if (r[p].pa_plan_id == null || r[p].pa_pengajuan_count == null || r[p].pa_pengajuan_count == 0) {
                                        hit = set_fill2('Hasil Akad Belum Cair')
                                    } else {
                                        const hasilAkad = parseFloat(r[p].pa_total_hasil_akad || 0);
                                        const totalCair = parseFloat(r[p].pa_total_cair_sum || 0);
                                        if (hasilAkad - totalCair > 0.01)
                                            hit = set_fill2('Pengajuan Pencairan Hasil Akad')
                                        else if (hasilAkad > 0) {
                                            const marketColor = 'Akad ' + subsidi;
                                            set_fill2(marketColor);
                                            hit = {
                                                fill: 'Pencairan Hasil Akad 100%',
                                                color_fill: marketColor,
                                                tipe: 'Status'
                                            };
                                        } else
                                            hit = set_fill2('Akad ' + subsidi)
                                    }
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

                        if (
                            parseFloat(r[p].progres_bangunan || 0) >= 100
                            && (r[p].is_subsidi == 0 || r[p].is_subsidi == 1)
                        ) {
                            const marketColor = 'Akad ' + subsidi;
                            set_fill2(marketColor);
                            hit = {
                                fill: 'Bangunan 100%',
                                color_fill: marketColor,
                                tipe: 'Status'
                            };
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



                    const isTargetMode = isTargetFilterActive();
                    const targetInfo = targetKavling[r[p].id_kavling] || null;
                    const targetPlan = isTargetMode
                        ? resolveTargetCanvasHit(r[p], rawHargaAkhir, targetInfo, hit)
                        : null;
                    if (targetPlan) {
                        hit = targetPlan.hit;
                    }
                    const preserveTargetDefault = Boolean(targetPlan && targetPlan.preserveDefaultVisual);
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
                    let id_hargajual = rawHargaAkhir;
                    r[p].harga_akhir = (r[p].hargajual) ? num_format(r[p].hargajual) + " (Per " + format_date(r[p].tgl_harga) + ")" : '-';



                    if (typeof hit !== 'object') {
                        hit = {
                            fill: hit,
                            tipe: 'Status'
                        }
                    }

                    const visualRows = Array.isArray(r[p].visual_rows) ? r[p].visual_rows : [];
                    const visualFill = r[p].visual_fill && r[p].visual_fill.config_name ? r[p].visual_fill : null;
                    const useUnifiedFill = (!isTargetMode || preserveTargetDefault) && String(va) === '0' && visualFill !== null;

                    if (useUnifiedFill) {
                        hit = {
                            fill: visualFill.config_name,
                            tipe: visualFill.tipe || 'Status'
                        };
                    }

                    // Override warna jika filter kategori aktif
                    const filterOverride = isTargetMode ? null : getFilterColorOverride(r[p], subsidi);
                    if (filterOverride) {
                        set_fill2(filterOverride.fill); // memastikan config warna diset
                        hit = filterOverride;
                    }

                    const useCompositeShape = (!isTargetMode || preserveTargetDefault) && String(va) === '0'
                        && !filterOverride
                        && visualRows.length > 0
                        && typeof SiteplanCompositeShape !== 'undefined';
                    const hitColorConfig = hit.color_fill || hit.fill;

                    const paintRows = useUnifiedFill ? [{
                        key: hit.tipe ? hit.tipe.toLowerCase() : 'status',
                        label: hit.tipe || 'Status',
                        segments: [{
                            config_name: hit.fill,
                            color_config_name: hitColorConfig,
                            ratio: 1
                        }],
                        markers: []
                    }] : visualRows;

                    const legendRows = (useUnifiedFill && hit.tipe === 'Tipe') ? paintRows : visualRows;
                    const legendKeys = useCompositeShape
                        ? (useUnifiedFill
                            ? (hit.tipe === 'Tipe'
                                ? SiteplanShapeFilter.keysFromRows(paintRows)
                                : SiteplanShapeFilter.keysFromRows(visualRows).concat(SiteplanShapeFilter.keysFromRows(paintRows)))
                            : SiteplanShapeFilter.keysFromRows(visualRows))
                        : [SiteplanShapeFilter.key(hit.tipe, hit.fill)];

                    const tooltipRows = (useUnifiedFill && hit.tipe === 'Tipe') ? paintRows : visualRows;

                    if (useCompositeShape) {
                        registerCompositeLegend(legendRows);
                    } else {
                        //set untuk filter warna
                        filterwarna[hit.tipe] = {
                            ...filterwarna[hit.tipe],
                            [hit.fill]: createLegendEntry(hit.tipe, hit.fill, {
                                config_name: hit.fill,
                                color_config_name: hitColorConfig
                            }, 'fill')
                        }

                        hitung_kavling(hit)
                    }
                    
                    const dataObj = {
                        id_kavling: r[p].id_kavling,
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
                        rotation: r[p].rotation,
                        luas_tanah: r[p].luas_tanah,
                        is_batal: r[p].is_batal,
                        akad: r[p].akad,
                        akad_tgl: r[p].akad_tgl,
                        akad_indent: r[p].akad_indent,
                        akad_indent_tgl: r[p].akad_indent_tgl,
                        nama_konsumen: r[p].nama_konsumen || null,
                    };
                    const data2Obj = {
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
                    };

                    const pointsArr = JSON.parse("[" + r[p].points + "]");
                    if (isTargetMode && targetPlan && targetPlan.isTargetKavling) {
                        siteplanTargetItems.push({
                            id_kavling: r[p].id_kavling,
                            points: pointsArr
                        });
                    }

                    if (useCompositeShape) {
                        kav = SiteplanCompositeShape.createKonvaShape(Konva, {
                            id: 'kav' + r[p].id_kavling,
                            kategori: hit.fill,
                            data: dataObj,
                            data2: data2Obj,
                            points: pointsArr,
                            facadeRotation: r[p].rotation,
                            visualRows: paintRows,
                            tooltipRows: tooltipRows,
                            legendKeys: legendKeys,
                            strokeWidthResolver: get_kategori_stroke_width,
                            stroke: undefined,
                            strokeWidth: 0,
                            opacity: 1,
                            globalCompositeOperation: 'source-over'
                        }, get_kategori_color);
                    } else {
                        // Rendering standar
                        kav = new Konva.Line({
                            points: pointsArr,
                            fill: get_kategori_color(hitColorConfig),
                            stroke: undefined,
                            strokeWidth: 0,
                            lineJoin: 'round',
                            lineCap: 'round',
                            strokeScaleEnabled: true,
                            dash: dashed,
                            opacity: 1,
                            closed: true,
                            globalCompositeOperation: 'multiply',
                            kategori: hit.fill,
                            data: dataObj,
                            data2: data2Obj,
                            visualRows: visualRows,
                            legendKeys: legendKeys,
                            id: 'kav' + r[p].id_kavling,
                            name: 'siteplan-kavling siteplan-data-shape'
                        });
                    }
                    
                    siteplan.add(kav);
                }
                updateSiteplanRenderStats();
                const problemKavlings = [];
                for (let p = 0; p < r.length; p++) {
                    if (r[p] && r[p].active_tiket_id) {
                        r[p].refType = 'kavling';
                        problemKavlings.push(r[p]);
                    }
                }
                siteplanProblemItems.kavling = problemKavlings;
                syncSiteplanTargetOverlay();
                syncSiteplanTiketOverlay();
                if (window.isTargetSetModeActive() && typeof window.renderTargetStagedCanvasHighlights === 'function') {
                    window.renderTargetStagedCanvasHighlights();
                }
                if (typeof window.isRequestKavlingCanvasPickActive === 'function' && window.isRequestKavlingCanvasPickActive()
                    && typeof window.renderRequestKavlingCanvasHighlights === 'function') {
                    window.renderRequestKavlingCanvasHighlights();
                }
                set_keterangan_warna()
                cek_tanggal_pembangunan(refresh)
                handlePendingSiteplanUrgentAction()
                handlePendingSiteplanTiketAction()
                if (typeof handlePendingNotificationDeepLink === 'function') handlePendingNotificationDeepLink();
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

        siteplanOthersRequest = $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: Object.assign({
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_cluster: filter.id_cluster,
                id_role: va
            }, getServerFilterData()),
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
                let activeKategori = $('#pilih-divisi').val() || [];

                for (var p = 0; p < r.length; p++) {
                    if (r[p].tipe == "jalan")
                        set_fill("#ccc", "#000", "0", null) // warna abu
                    else if (r[p].tipe == "fasos")
                        set_fill("#9000ff", "#000", "0", null) // warna ungu
                    else if (r[p].tipe == "rth")
                        set_fill("#0f0", "#000", "0", null) // warna merah
                    else if (r[p].tipe == "ruko")
                        set_fill("#2057a3", "#000", "0", null) // warna biru utama SIGAPP

                    if (activeKategori.includes('Masalah')) {
                        const prio = r[p].prioritas_masalah ? r[p].prioritas_masalah.toLowerCase() : 'normal';
                        let hitFill = 'Masalah Normal';
                        if (prio === 'urgent') hitFill = 'Masalah Urgent';
                        else if (prio === 'medium') hitFill = 'Masalah Medium';
                        else if (prio === 'low') hitFill = 'Masalah Low';

                        set_fill2(hitFill);
                        filterwarna['Filter'] = filterwarna['Filter'] || {};
                        filterwarna['Filter'][hitFill] = createLegendEntry('Filter', hitFill, {
                            config_name: hitFill
                        }, 'fill');
                        hitung_kavling({fill: hitFill, tipe: 'Filter'});
                    }

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
                        id: 'others' + r[p].id,
                        name: 'siteplan-other siteplan-data-shape'
                    });
                    siteplan.add(kav);
                }

                updateSiteplanRenderStats();

                const problemOthers = [];
                for (let p = 0; p < r.length; p++) {
                    if (r[p] && r[p].active_tiket_id) {
                        r[p].refType = 'others';
                        problemOthers.push(r[p]);
                    }
                }
                siteplanProblemItems.others = problemOthers;
                syncSiteplanTiketOverlay();
                if (window.isTargetSetModeActive() && typeof window.renderTargetStagedCanvasHighlights === 'function') {
                    window.renderTargetStagedCanvasHighlights();
                }
                if (typeof window.isRequestKavlingCanvasPickActive === 'function' && window.isRequestKavlingCanvasPickActive()
                    && typeof window.renderRequestKavlingCanvasHighlights === 'function') {
                    window.renderRequestKavlingCanvasHighlights();
                }
                
                if (activeKategori.includes('Masalah')) {
                    set_keterangan_warna();
                }
                handlePendingSiteplanTiketAction();
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
        if (!pointer) return;

        var mousePointTo = {
            x: (pointer.x - stage.x()) / oldScale,
            y: (pointer.y - stage.y()) / oldScale,
        };

        var newScale = e.evt.ctrlKey
            ? oldScale * Math.pow(0.99, e.evt.deltaY)
            : (e.evt.deltaY > 0 ? oldScale / scaleBy : oldScale * scaleBy);

        stage.scale({
            x: newScale,
            y: newScale
        });

        group.scale({
            x: 1 / newScale,
            y: 1 / newScale
        });
        stage.position({
            x: pointer.x - mousePointTo.x * newScale,
            y: pointer.y - mousePointTo.y * newScale,
        });
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
        stage.find('#siteplan-selection').forEach(function(selectionHighlight) {
            selectionHighlight.destroy();
        });

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

    function getSiteplanKavlingNode(target) {
        if (!target || typeof target.hasName !== 'function') return null;
        if (target.hasName('siteplan-kavling')) return target;
        if (target.hasName('subShape') && target.parent && target.parent.hasName('siteplan-kavling')) {
            return target.parent;
        }

        return null;
    }

    function isSelectedKavling(node) {
        const nodeId = node && typeof node.id === 'function' ? node.id() : '';
        return editdtt.some(function(selected) {
            return selected && selected.id === nodeId;
        });
    }

    function getSiteplanKavlingPoints(node) {
        if (node && typeof node.points === 'function') return node.points();
        return node && typeof node.getAttr === 'function' ? (node.getAttr('points') || []) : [];
    }

    function hideKavlingHover() {
        if (!hoverHighlight.isVisible()) return;
        hoverHighlight.hide();
        masked.batchDraw();
    }

    //event klik kavling (dblclick dihapus agar ditangani stage)

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
        if (setSiteplanTooltip(data)) {
            group.moveToTop();
            group.show(); //show tooltip
        }
    })

    siteplan.on('click tap', function(e) {
        if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) return;
        if (typeof isFacadeArrowActive !== 'undefined' && isFacadeArrowActive) return;

        var k = e.target; //get shape
        // Jika shape adalah bagian dari Konva.Group (multi-color mode), gunakan group-nya
        if (k.hasName('subShape') && k.parent) {
            k = k.parent;
        }

        var sh = k.attrs, //get attribut shape
            role = $('#pilih-divisi option:selected').val(),
            id_kavling = ''

        if (!sh.id) return false;
        id_kavling = sh.id.substr(3)

        if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) {
            return;
        }
        if (typeof window.isTargetSetModeActive === 'function' && window.isTargetSetModeActive()) {
            if (typeof window.handleTargetCanvasClick === 'function') {
                window.handleTargetCanvasClick(sh, id_kavling);
            }
            return;
        }
        if (typeof window.isRequestKavlingCanvasPickActive === 'function' && window.isRequestKavlingCanvasPickActive()) {
            if (typeof window.handleRequestKavlingCanvasClick === 'function') {
                window.handleRequestKavlingCanvasClick(sh, id_kavling);
            }
            return;
        }

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

            hideKavlingHover();
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
        if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) return;
        if (typeof isFacadeArrowActive !== 'undefined' && isFacadeArrowActive) return;
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
        if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) {
            group.hide();
            return;
        }
        var k = e.target;
        if (k.hasName('subShape') && k.parent) k = k.parent;
        data = k.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (setSiteplanTooltip(data)) {
            group.moveToTop();
            group.show(); //show tooltip

        }

    })
    //highlight kavling tanpa menggambar ulang seluruh layer siteplan
    siteplan.on('mouseover', function(e) {
        var node = getSiteplanKavlingNode(e.target);
        if ((typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) || !node || isManualSelectionActive() || isSelectedKavling(node)) {
            hideKavlingHover();
            return;
        }

        hoverHighlight.setHighlightPoints(getSiteplanKavlingPoints(node));
        hoverHighlight.show();
        hoverHighlight.moveToTop();
        stage.container().style.cursor = 'pointer';
        masked.batchDraw();
    })

    //hide tooltip
    siteplan.on('mouseout', function(e) {
        if (getSiteplanKavlingNode(e.target)) {
            hideKavlingHover();
            stage.container().style.cursor = 'default';
        }
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
    var dragStopped = false;
    stage.on('touchmove', function(e) {
        e.evt.preventDefault();
        var touch1 = e.evt.touches[0];
        var touch2 = e.evt.touches[1];

        // restore single-finger drag when second finger is lifted
        if (touch1 && !touch2 && !stage.isDragging() && dragStopped) {
            stage.startDrag();
            dragStopped = false;
        }

        if (touch1 && touch2) {
            // Konva registers a "ready" drag on touchstart before dragDistance (3px) is crossed;
            // stage.isDragging() is still false then, so always stopDrag() to clear _dragElements
            stage.stopDrag();
            dragStopped = true;

            var rect = stage.container().getBoundingClientRect();
            var p1 = { x: touch1.clientX - rect.left, y: touch1.clientY - rect.top };
            var p2 = { x: touch2.clientX - rect.left, y: touch2.clientY - rect.top };

            var newCenter = getCenter(p1, p2);
            var dist = getDistance(p1, p2);

            if (!lastCenter || !lastDist) {
                lastCenter = newCenter;
                lastDist = dist;
                return;
            }

            // local coordinates of previous pinch center point
            var pointTo = {
                x: (lastCenter.x - stage.x()) / stage.scaleX(),
                y: (lastCenter.y - stage.y()) / stage.scaleX(),
            };

            var scale = stage.scaleX() * (dist / lastDist);

            stage.scale({ x: scale, y: scale });
            group.scale({ x: 1 / scale, y: 1 / scale });

            stage.position({
                x: newCenter.x - pointTo.x * scale,
                y: newCenter.y - pointTo.y * scale,
            });

            lastDist = dist;
            lastCenter = newCenter;
        }
    });

    stage.on('touchend touchcancel', function(e) {
        lastDist = 0;
        lastCenter = null;
        if (!e.evt.touches || e.evt.touches.length === 0) {
            dragStopped = false;
        }
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
                    const pendingTiketAction = getPendingSiteplanTiketAction();
                    if (pendingTiketAction && $('#pilih-divisi option[value="Masalah"]').length) {
                        $('#pilih-divisi').val('Masalah');
                    }
                    $('#pilih-divisi').trigger('change.select2');
                    checkMasalahOptions();
                    checkPeriodeOptions();

                    // Jika siteplan telanjur memuat sebelum opsi dinamis selesai,
                    // muat ulang agar deep-link tiket memakai filter Masalah.
                    if (pendingTiketAction && siteplanInitialDataRequested && siteplanCanvasInitialized) {
                        load_kavling();
                    }
                    
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
        const rawClusterVal = $('#filter-id_cluster').val();
        const rawClusterArray = Array.isArray(rawClusterVal) ? rawClusterVal : (rawClusterVal == null || rawClusterVal === '' ? [] : [rawClusterVal]);
        filter.id_cluster = selectedClusterIds();
        filter.id_jalan = $("#filter-id_jalan").val();
        const saveSelection = rawClusterArray.includes('all') ? ['all'] : filter.id_cluster;
        SiteplanFilterState.save(getSiteplanStorage(), siteplanCurrentUserId, dt_proyek.id_proyek, saveSelection);
        load_kavling();
        renderActiveFilterTags();
    }

    window.reset_server_filter = function() {
        const rawClusterVal = $('#filter-id_cluster').val();
        const preservedClusters = selectedClusterIds();
        $('#form-filter-kategori')[0].reset();
        applyTargetGapBridgeUI(getSavedTargetGapBridge(), false);
        $('#filter-id_cluster').val(rawClusterVal).trigger('change.select2');
        $('#filter-id_jalan').val(null).trigger('change.select2');
        $('#pilih-divisi').val('0').trigger('change.select2');
        $('#filter-id_jalan').prop('disabled', preservedClusters.length === 0);
        filter.id_cluster = preservedClusters;
        filter.id_jalan = '';
        $('#toggle-tampilkan-target').prop('checked', false);
        $('#filter-target-other-mode').val('default');
        $('#filter-target-kavling-mode').val('default');
        $('#filter-target-tahun-container').stop(true, true).slideUp(200);
        syncTargetMenuVisibility();
        checkMasalahOptions();
        checkPeriodeOptions();
        load_kavling();
        renderActiveFilterTags();
    }

    $(document).ready(function() {
        applyTargetGapBridgeUI(getSavedTargetGapBridge(), false);
        fetch_kategori_options();
    });

    function getServerFilterData() {
        let data = {
            kategori: [],
            periode_mulai: $('#filter-periode-mulai').val() || '',
            periode_selesai: $('#filter-periode-selesai').val() || '',
            status_masalah: $('#filter-status-masalah').val() || '',
            periode_masalah_jenis: $('#filter-periode-masalah-jenis').val() || '',
            tahun_target: isTargetFilterActive() ? selectedTargetYear() : ''
        };
        let selectedOption = $('#pilih-divisi option:selected');
        if (selectedOption.data('is-kategori')) {
            data.kategori.push(selectedOption.val());
        }
        return data;
    }

    function getSiteplanStorage() {
        try {
            return window.localStorage;
        } catch (error) {
            return null;
        }
    }

    function escapeSiteplanHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function renderActiveFilterTags() {
        let html = '';
        const rawClusterVal = $('#filter-id_cluster').val();
        const rawClusterArray = Array.isArray(rawClusterVal) ? rawClusterVal : (rawClusterVal == null || rawClusterVal === '' ? [] : [rawClusterVal]);
        if (rawClusterArray.includes('all')) {
            html += `<span class="badge badge-light-primary mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('cluster', 'all')">Cluster: Semua Cluster &times;</span>`;
        } else {
            const selectedClusters = new Set(selectedClusterIds());
            $('#filter-id_cluster option:selected').each(function() {
                const clusterId = String($(this).val());
                if (!selectedClusters.has(clusterId)) return;

                const clusterText = escapeSiteplanHtml($(this).text());
                html += `<span class="badge badge-light-primary mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('cluster', '${clusterId}')">Cluster: ${clusterText} &times;</span>`;
            });
        }
        
        if (filter.id_jalan) {
            const jalanText = escapeSiteplanHtml($("#filter-id_jalan option:selected").text());
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

        if (isTargetFilterActive()) {
            const targetYear = selectedTargetYear();
            const otherMode = selectedTargetOtherMode();
            const targetMode = selectedTargetKavlingMode();
            const otherModeLabels = {
                split_4: 'Selain: Akad/Booking (4)',
                merge_2: 'Selain: Akad/Booking (2)',
                single_1: 'Selain: Konsumen (1)'
            };
            const targetModeLabels = {
                single_color: 'Target: 1 Warna',
                by_tipe: 'Target: Per Tipe'
            };
            const modeNotes = [];
            if (otherModeLabels[otherMode]) {
                modeNotes.push(otherModeLabels[otherMode]);
            }
            if (targetModeLabels[targetMode]) {
                modeNotes.push(targetModeLabels[targetMode]);
            }
            const baseLabel = targetYear ? ('Target: ' + escapeSiteplanHtml(targetYear)) : 'Target: Aktif';
            const targetLabel = modeNotes.length > 0 ? (baseLabel + ' (' + modeNotes.join(', ') + ')') : baseLabel;
            html += `<span class="badge badge-light-primary siteplan-target-active-badge mr-50 mb-50" style="cursor:pointer;" onclick="removeFilterTag('target')">${targetLabel} &times;</span>`;
        }

        $('#active-filter-tags').html(html);
    }

    window.removeFilterTag = function(type, val = null) {
        if (type === 'cluster') {
            if (val === 'all') {
                $('#filter-id_cluster').val([]).trigger('change.select2');
                filter.id_cluster = [];
                $('#filter-id_jalan').val(null).trigger('change.select2');
                filter.id_jalan = '';
                $('#filter-id_jalan').prop('disabled', true);
            } else {
                const remaining = selectedClusterIds().filter(function(id) {
                    return id !== String(val);
                });
                $('#filter-id_cluster').val(remaining).trigger('change.select2');
                filter.id_cluster = remaining;
                $('#filter-id_jalan').val(null).trigger('change.select2');
                filter.id_jalan = '';
                $('#filter-id_jalan').prop('disabled', remaining.length === 0);
            }
        } else if (type === 'jalan') {
            $('#filter-id_jalan').val(null).trigger('change');
            filter.id_jalan = '';
        } else if (type === 'kategori') {
            $('#pilih-divisi').val('0').trigger('change.select2');
        } else if (type === 'periode') {
            $('#filter-periode-mulai').val('');
            $('#filter-periode-selesai').val('');
        } else if (type === 'target') {
            $('#toggle-tampilkan-target').prop('checked', false);
            $('#filter-target-other-mode').val('default');
            $('#filter-target-kavling-mode').val('default');
            $('#filter-target-tahun-container').stop(true, true).slideUp(200);
            syncTargetMenuVisibility();
        }
        checkMasalahOptions();
        checkPeriodeOptions();
        
        // Auto-apply immediately (user feedback)
        window.apply_server_filter();
    }
    // stage.add(siteplan, masked, datal);
    stage.add(siteplan, targetOverlayLayer, tiketOverlayLayer, masked);
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

    const siteplanExportState = {
        area: 'full',
        format: 'png',
        quality: 'hd',
        paper: 'a3',
        orientation: 'auto',
        customCrop: null,
        draftCrop: null,
        cropModeActive: false,
        cropGroup: null,
        cropSyncScale: null
    };

    window.isExportCropModeActive = function() {
        return Boolean(siteplanExportState.cropModeActive);
    };

    function getSiteplanImageSize() {
        const w = (imageObj && imageObj.width) ? Number(imageObj.width) : 0;
        const h = (imageObj && imageObj.height) ? Number(imageObj.height) : 0;
        return { width: w, height: h };
    }

    function computeFilteredKavlingCropRect(imgW, imgH) {
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        let found = 0;

        siteplan.find('.siteplan-kavling').forEach(function(node) {
            if (typeof node.isVisible === 'function' ? !node.isVisible() : (typeof node.visible === 'function' && !node.visible())) {
                return;
            }
            const rect = node.getClientRect({ relativeTo: siteplan, skipShadow: true });
            if (!rect || rect.width <= 0 || rect.height <= 0) return;
            minX = Math.min(minX, rect.x);
            minY = Math.min(minY, rect.y);
            maxX = Math.max(maxX, rect.x + rect.width);
            maxY = Math.max(maxY, rect.y + rect.height);
            found++;
        });

        if (!found || !Number.isFinite(minX)) {
            return { rect: { x: 0, y: 0, width: imgW, height: imgH }, fallbackFull: true };
        }

        const spanW = maxX - minX;
        const spanH = maxY - minY;
        const pad = Math.max(60, Math.round(Math.max(spanW, spanH) * 0.06));
        return {
            rect: SiteplanExportHelper.clampCropRect({
                x: minX - pad,
                y: minY - pad,
                width: spanW + pad * 2,
                height: spanH + pad * 2
            }, imgW, imgH, 60),
            fallbackFull: false
        };
    }

    function computeViewportCropRect(imgW, imgH) {
        const scale = stage.scaleX() || 1;
        const viewW = stage.width() || 0;
        const viewH = stage.height() || 0;
        if (!viewW || !viewH) {
            return { x: 0, y: 0, width: imgW, height: imgH };
        }

        return SiteplanExportHelper.clampCropRect({
            x: (-stage.x()) / scale,
            y: (-stage.y()) / scale,
            width: viewW / scale,
            height: viewH / scale
        }, imgW, imgH, 60);
    }

    function resolveExportCropInfo(areaMode) {
        const size = getSiteplanImageSize();
        const imgW = size.width;
        const imgH = size.height;
        if (!imgW || !imgH) {
            return { rect: { x: 0, y: 0, width: 1, height: 1 }, fallbackFull: false };
        }

        if (areaMode === 'viewport') {
            return { rect: computeViewportCropRect(imgW, imgH), fallbackFull: false };
        }
        if (areaMode === 'filtered') {
            return computeFilteredKavlingCropRect(imgW, imgH);
        }
        if (areaMode === 'custom') {
            if (siteplanExportState.customCrop) {
                return {
                    rect: SiteplanExportHelper.clampCropRect(siteplanExportState.customCrop, imgW, imgH, 60),
                    fallbackFull: false
                };
            }
            const vp = computeViewportCropRect(imgW, imgH);
            const insetX = Math.round(vp.width * 0.1);
            const insetY = Math.round(vp.height * 0.1);
            return {
                rect: SiteplanExportHelper.clampCropRect({
                    x: vp.x + insetX,
                    y: vp.y + insetY,
                    width: Math.max(80, vp.width - insetX * 2),
                    height: Math.max(80, vp.height - insetY * 2)
                }, imgW, imgH, 60),
                fallbackFull: false
            };
        }

        return { rect: { x: 0, y: 0, width: imgW, height: imgH }, fallbackFull: false };
    }

    function renderSiteplanExportCanvas(cropRect, pixelRatio) {
        const savedScale = { x: stage.scaleX(), y: stage.scaleY() };
        const savedPos = { x: stage.x(), y: stage.y() };
        const wasGroupVisible = group.visible();
        const wasHoverVisible = hoverHighlight.visible();
        const wasMaskedGroupVisible = maskedGroup.visible();
        const wasManualVisible = manual_selection.visible();
        const wasLineMsVisible = line_ms.visible();
        const wasCropVisible = siteplanExportState.cropGroup ? siteplanExportState.cropGroup.visible() : false;

        try {
            group.hide();
            hoverHighlight.hide();
            maskedGroup.hide();
            manual_selection.hide();
            line_ms.hide();
            if (siteplanExportState.cropGroup) {
                siteplanExportState.cropGroup.hide();
            }

            stage.scale({ x: 1, y: 1 });
            stage.position({ x: 0, y: 0 });

            const rawCanvas = stage.toCanvas({
                x: cropRect.x,
                y: cropRect.y,
                width: cropRect.width,
                height: cropRect.height,
                pixelRatio: pixelRatio
            });

            const outCanvas = document.createElement('canvas');
            outCanvas.width = Math.max(1, rawCanvas.width);
            outCanvas.height = Math.max(1, rawCanvas.height);
            const outCtx = outCanvas.getContext('2d');
            outCtx.fillStyle = '#ffffff';
            outCtx.fillRect(0, 0, outCanvas.width, outCanvas.height);
            outCtx.imageSmoothingEnabled = true;
            outCtx.imageSmoothingQuality = 'high';
            outCtx.drawImage(rawCanvas, 0, 0);
            return outCanvas;
        } finally {
            stage.scale(savedScale);
            stage.position(savedPos);
            if (wasGroupVisible) group.show();
            if (wasHoverVisible) hoverHighlight.show();
            if (wasMaskedGroupVisible) maskedGroup.show();
            if (wasManualVisible) manual_selection.show();
            if (wasLineMsVisible) line_ms.show();
            if (siteplanExportState.cropGroup && wasCropVisible) {
                siteplanExportState.cropGroup.show();
            }
            stage.batchDraw();
        }
    }

    function syncExportModalUI() {
        const $modal = $('#modal-export-siteplan');
        if (!$modal.length) return;

        $('#export-area-options .export-option-card').each(function() {
            const val = $(this).data('export-area');
            const active = val === siteplanExportState.area;
            $(this).toggleClass('is-selected', active);
            $(this).find('input[type="radio"]').prop('checked', active);
        });

        $('#export-format-options .export-option-card').each(function() {
            const val = $(this).data('export-format');
            const active = val === siteplanExportState.format;
            $(this).toggleClass('is-selected', active);
            $(this).find('input[type="radio"]').prop('checked', active);
        });

        $('#export-quality-select').val(siteplanExportState.quality);
        $('#export-pdf-paper').val(siteplanExportState.paper);
        $('#export-pdf-orientation').val(siteplanExportState.orientation);

        const isCustom = siteplanExportState.area === 'custom';
        $('#export-custom-crop-box').prop('hidden', !isCustom);
        if (isCustom) {
            if (siteplanExportState.customCrop) {
                const c = siteplanExportState.customCrop;
                $('#export-custom-crop-status').text('Area manual: ' + c.width + ' × ' + c.height + ' px (posisi ' + c.x + ', ' + c.y + ')');
            } else {
                $('#export-custom-crop-status').text('Belum memilih area khusus — gunakan tombol di kanan untuk tarik kotak di kanvas');
            }
        }

        const isPdf = siteplanExportState.format === 'pdf';
        $('#export-pdf-options').prop('hidden', !isPdf);

        refreshExportPreview();
    }

    function refreshExportPreview() {
        const size = getSiteplanImageSize();
        if (!size.width || !size.height) return;

        const cropInfo = resolveExportCropInfo(siteplanExportState.area);
        const cropRect = cropInfo.rect;
        const metrics = SiteplanExportHelper.computeEffectivePixelRatio(
            cropRect.width,
            cropRect.height,
            siteplanExportState.quality
        );

        $('#export-meta-source').text(cropRect.width + ' × ' + cropRect.height + ' px');
        $('#export-meta-output').text(metrics.outputWidth + ' × ' + metrics.outputHeight + ' px (' + metrics.pixelRatio + 'x)');

        if (siteplanExportState.format === 'pdf') {
            const pdfLayout = SiteplanExportHelper.computePdfPageLayout(
                metrics.outputWidth,
                metrics.outputHeight,
                siteplanExportState.paper,
                siteplanExportState.orientation
            );
            const paperLabel = pdfLayout.paperSize === 'fit' ? 'Sesuai Area' : pdfLayout.paperSize.toUpperCase();
            const orientLabel = pdfLayout.orientation === 'landscape' ? 'Landscape' : 'Portrait';
            $('#export-meta-note').text(
                'Output PDF ' + paperLabel + ' (' + orientLabel + '). ' +
                (cropInfo.fallbackFull ? 'Tidak ada kavling terfilter aktif, menggunakan seluruh siteplan.' : 'Shape & nomor kavling dirender pada resolusi vektor tinggi.')
            );
        } else if (cropInfo.fallbackFull) {
            $('#export-meta-note').text('Tidak ada kavling terfilter yang tampil di kanvas, area otomatis menggunakan seluruh siteplan.');
        } else if (metrics.capped) {
            $('#export-meta-note').text('Skala disesuaikan otomatis ke batas aman memori browser (' + metrics.pixelRatio + 'x) agar hasil tajam dan bebas gagal render.');
        } else {
            $('#export-meta-note').text('Shape kavling & nomor kavling dirender ulang sebagai vektor ' + metrics.pixelRatio + 'x agar tetap tajam saat diperbesar.');
        }

        const previewScale = Math.min(1, 560 / Math.max(cropRect.width, cropRect.height));
        try {
            const previewCanvas = renderSiteplanExportCanvas(cropRect, previewScale);
            const previewUrl = previewCanvas.toDataURL('image/jpeg', 0.85);
            $('#export-preview-empty').prop('hidden', true);
            $('#export-preview-img').attr('src', previewUrl).prop('hidden', false);
        } catch (err) {
            $('#export-preview-img').prop('hidden', true);
            $('#export-preview-empty').text('Pratinjau tidak tersedia').prop('hidden', false);
        }
    }

    function destroyExportCropOverlay() {
        stage.off('.exportCrop');
        stage.draggable(true);
        stage.container().style.cursor = 'default';
        if (siteplanExportState.cropGroup) {
            siteplanExportState.cropGroup.destroy();
            siteplanExportState.cropGroup = null;
        }
        siteplanExportState.cropSyncScale = null;
        siteplanExportState.cropModeActive = false;
        $('#siteplan-export-crop-bar').prop('hidden', true);
        masked.batchDraw();
    }

    function startExportCropSelection() {
        const size = getSiteplanImageSize();
        if (!size.width || !size.height) return;

        destroyExportCropOverlay();
        hideKavlingHover();
        group.hide();

        const initialInfo = resolveExportCropInfo('custom');
        const rect = Object.assign({}, initialInfo.rect);
        siteplanExportState.draftCrop = rect;
        siteplanExportState.cropModeActive = true;

        const cropGroup = new Konva.Group({ id: 'siteplan-export-crop-group' });
        const maskFill = 'rgba(15, 23, 42, 0.45)';
        const maskTop = new Konva.Rect({ fill: maskFill, listening: false });
        const maskBottom = new Konva.Rect({ fill: maskFill, listening: false });
        const maskLeft = new Konva.Rect({ fill: maskFill, listening: false });
        const maskRight = new Konva.Rect({ fill: maskFill, listening: false });

        const cropBox = new Konva.Rect({
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height,
            stroke: '#2057a3',
            strokeWidth: 3,
            strokeScaleEnabled: false,
            dash: [10, 5],
            fill: 'rgba(32, 87, 163, 0.06)',
            draggable: true
        });

        cropGroup.add(maskTop, maskBottom, maskLeft, maskRight, cropBox);

        const MIN_CROP_SIZE = 60;
        const handleDefs = [
            { id: 'nw', cursor: 'nwse-resize', getPos: (r) => ({ x: r.x, y: r.y }), resize: (r, p) => {
                const right = r.x + r.width, bottom = r.y + r.height;
                const nx = Math.max(0, Math.min(p.x, right - MIN_CROP_SIZE));
                const ny = Math.max(0, Math.min(p.y, bottom - MIN_CROP_SIZE));
                return { x: nx, y: ny, width: right - nx, height: bottom - ny };
            }},
            { id: 'n', cursor: 'ns-resize', getPos: (r) => ({ x: r.x + r.width / 2, y: r.y }), resize: (r, p) => {
                const bottom = r.y + r.height;
                const ny = Math.max(0, Math.min(p.y, bottom - MIN_CROP_SIZE));
                return { x: r.x, y: ny, width: r.width, height: bottom - ny };
            }},
            { id: 'ne', cursor: 'nesw-resize', getPos: (r) => ({ x: r.x + r.width, y: r.y }), resize: (r, p) => {
                const bottom = r.y + r.height;
                const nw = Math.max(MIN_CROP_SIZE, Math.min(p.x - r.x, size.width - r.x));
                const ny = Math.max(0, Math.min(p.y, bottom - MIN_CROP_SIZE));
                return { x: r.x, y: ny, width: nw, height: bottom - ny };
            }},
            { id: 'e', cursor: 'ew-resize', getPos: (r) => ({ x: r.x + r.width, y: r.y + r.height / 2 }), resize: (r, p) => {
                const nw = Math.max(MIN_CROP_SIZE, Math.min(p.x - r.x, size.width - r.x));
                return { x: r.x, y: r.y, width: nw, height: r.height };
            }},
            { id: 'se', cursor: 'nwse-resize', getPos: (r) => ({ x: r.x + r.width, y: r.y + r.height }), resize: (r, p) => {
                const nw = Math.max(MIN_CROP_SIZE, Math.min(p.x - r.x, size.width - r.x));
                const nh = Math.max(MIN_CROP_SIZE, Math.min(p.y - r.y, size.height - r.y));
                return { x: r.x, y: r.y, width: nw, height: nh };
            }},
            { id: 's', cursor: 'ns-resize', getPos: (r) => ({ x: r.x + r.width / 2, y: r.y + r.height }), resize: (r, p) => {
                const nh = Math.max(MIN_CROP_SIZE, Math.min(p.y - r.y, size.height - r.y));
                return { x: r.x, y: r.y, width: r.width, height: nh };
            }},
            { id: 'sw', cursor: 'nesw-resize', getPos: (r) => ({ x: r.x, y: r.y + r.height }), resize: (r, p) => {
                const right = r.x + r.width;
                const nx = Math.max(0, Math.min(p.x, right - MIN_CROP_SIZE));
                const nh = Math.max(MIN_CROP_SIZE, Math.min(p.y - r.y, size.height - r.y));
                return { x: nx, y: r.y, width: right - nx, height: nh };
            }},
            { id: 'w', cursor: 'ew-resize', getPos: (r) => ({ x: r.x, y: r.y + r.height / 2 }), resize: (r, p) => {
                const right = r.x + r.width;
                const nx = Math.max(0, Math.min(p.x, right - MIN_CROP_SIZE));
                return { x: nx, y: r.y, width: right - nx, height: r.height };
            }}
        ];

        const handles = [];
        function syncCropVisuals() {
            const r = siteplanExportState.draftCrop;
            cropBox.setAttrs({ x: r.x, y: r.y, width: r.width, height: r.height });

            maskTop.setAttrs({ x: 0, y: 0, width: size.width, height: r.y });
            maskBottom.setAttrs({ x: 0, y: r.y + r.height, width: size.width, height: Math.max(0, size.height - (r.y + r.height)) });
            maskLeft.setAttrs({ x: 0, y: r.y, width: r.x, height: r.height });
            maskRight.setAttrs({ x: r.x + r.width, y: r.y, width: Math.max(0, size.width - (r.x + r.width)), height: r.height });

            const invScale = 1 / (stage.scaleX() || 1);
            handles.forEach(function(item) {
                const pt = item.def.getPos(r);
                item.node.setAttrs({ x: pt.x, y: pt.y, scaleX: invScale, scaleY: invScale });
            });

            $('#export-crop-bar-size').text(Math.round(r.width) + ' × ' + Math.round(r.height) + ' px');
            masked.batchDraw();
        }

        cropBox.on('mouseenter', function() {
            stage.container().style.cursor = 'move';
        });
        cropBox.on('mouseleave', function() {
            stage.container().style.cursor = 'default';
        });
        cropBox.on('mousedown touchstart', function() {
            stage.draggable(false);
        });
        cropBox.on('dragmove', function() {
            const r = siteplanExportState.draftCrop;
            const nx = Math.max(0, Math.min(cropBox.x(), size.width - r.width));
            const ny = Math.max(0, Math.min(cropBox.y(), size.height - r.height));
            siteplanExportState.draftCrop = {
                x: Math.round(nx),
                y: Math.round(ny),
                width: Math.round(r.width),
                height: Math.round(r.height)
            };
            syncCropVisuals();
        });
        cropBox.on('dragend mouseup touchend', function() {
            stage.draggable(true);
        });

        handleDefs.forEach(function(def) {
            const handle = new Konva.Circle({
                radius: 7,
                fill: '#ffffff',
                stroke: '#2057a3',
                strokeWidth: 2.5,
                draggable: true
            });
            handle.on('mouseenter', function() {
                stage.container().style.cursor = def.cursor;
            });
            handle.on('mouseleave', function() {
                stage.container().style.cursor = 'default';
            });
            handle.on('mousedown touchstart', function(ev) {
                ev.cancelBubble = true;
                stage.draggable(false);
            });
            handle.on('dragmove', function(ev) {
                ev.cancelBubble = true;
                const next = def.resize(siteplanExportState.draftCrop, { x: handle.x(), y: handle.y() });
                siteplanExportState.draftCrop = SiteplanExportHelper.clampCropRect(next, size.width, size.height, MIN_CROP_SIZE);
                syncCropVisuals();
            });
            handle.on('dragend mouseup touchend', function(ev) {
                ev.cancelBubble = true;
                stage.draggable(true);
            });
            cropGroup.add(handle);
            handles.push({ def: def, node: handle });
        });

        siteplanExportState.cropGroup = cropGroup;
        siteplanExportState.cropSyncScale = syncCropVisuals;
        masked.add(cropGroup);
        cropGroup.moveToTop();
        syncCropVisuals();

        stage.on('wheel.exportCrop touchmove.exportCrop', function() {
            if (siteplanExportState.cropSyncScale) {
                siteplanExportState.cropSyncScale();
            }
        });

        $('#modal-export-siteplan').modal('hide');
        $('#siteplan-export-crop-bar').prop('hidden', false);
    }

    function applyExportCropSelection() {
        if (!siteplanExportState.cropModeActive) return;
        const size = getSiteplanImageSize();
        siteplanExportState.customCrop = SiteplanExportHelper.clampCropRect(
            siteplanExportState.draftCrop,
            size.width,
            size.height,
            60
        );
        siteplanExportState.area = 'custom';
        destroyExportCropOverlay();
        syncExportModalUI();
        $('#modal-export-siteplan').modal('show');
    }

    function cancelExportCropSelection() {
        if (!siteplanExportState.cropModeActive) return;
        destroyExportCropOverlay();
        syncExportModalUI();
        $('#modal-export-siteplan').modal('show');
    }

    function triggerBlobDownload(blob, filename, done) {
        const url = URL.createObjectURL(blob);
        downloadURI(url, filename, function() {
            setTimeout(function() {
                URL.revokeObjectURL(url);
            }, 4000);
            if (typeof done === 'function') done();
        });
    }

    function canvasToBlobPromise(canvas, mimeType, quality) {
        return new Promise(function(resolve, reject) {
            if (typeof canvas.toBlob === 'function') {
                canvas.toBlob(function(blob) {
                    if (blob) {
                        resolve(blob);
                    } else {
                        reject(new Error('Gagal mengonversi kanvas menjadi file.'));
                    }
                }, mimeType, quality);
                return;
            }
            const dataUrl = canvas.toDataURL(mimeType, quality);
            const bytes = SiteplanExportHelper.dataUrlToUint8Array(dataUrl);
            resolve(new Blob([bytes], { type: mimeType }));
        });
    }

    function export_siteplan() {
        const size = getSiteplanImageSize();
        if (!siteplanImageReady || !size.width || !size.height) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Siteplan belum siap',
                    text: 'Tunggu hingga gambar siteplan selesai dimuat sebelum melakukan export.'
                });
            }
            return;
        }

        if (siteplanExportState.cropModeActive) {
            destroyExportCropOverlay();
        }

        syncExportModalUI();
        $('#modal-export-siteplan').modal('show');
    }

    function executeSiteplanExport() {
        const size = getSiteplanImageSize();
        if (!siteplanImageReady || !size.width || !size.height) return;

        const $confirmBtn = $('#btn-confirm-export-siteplan');
        const originalHtml = $confirmBtn.html();
        $confirmBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Memproses...');

        const cropInfo = resolveExportCropInfo(siteplanExportState.area);
        const cropRect = cropInfo.rect;
        const metrics = SiteplanExportHelper.computeEffectivePixelRatio(
            cropRect.width,
            cropRect.height,
            siteplanExportState.quality
        );

        const areaSuffixMap = {
            full: '',
            viewport: ' (Area Layar)',
            filtered: ' (Area Filter)',
            custom: ' (Area Pilihan)'
        };
        const projectName = SiteplanExportHelper.sanitizeFilenamePart(dt_proyek && dt_proyek.nama_proyek);
        const datePart = SiteplanExportHelper.sanitizeFilenamePart(format_date(today_date));
        const areaSuffix = areaSuffixMap[siteplanExportState.area] || '';
        const ext = siteplanExportState.format === 'jpeg' ? 'jpg' : siteplanExportState.format;
        const filename = 'Siteplan ' + projectName + areaSuffix + ' Per ' + datePart + '.' + ext;

        setTimeout(function() {
            Promise.resolve().then(function() {
                const exportCanvas = renderSiteplanExportCanvas(cropRect, metrics.pixelRatio);

                if (siteplanExportState.format === 'png') {
                    return canvasToBlobPromise(exportCanvas, 'image/png').then(function(blob) {
                        return new Promise(function(resolve) {
                            triggerBlobDownload(blob, filename, resolve);
                        });
                    });
                }

                if (siteplanExportState.format === 'jpeg') {
                    return canvasToBlobPromise(exportCanvas, 'image/jpeg', metrics.jpegQuality).then(function(blob) {
                        return new Promise(function(resolve) {
                            triggerBlobDownload(blob, filename, resolve);
                        });
                    });
                }

                // PDF export via high-res JPEG stream embedded into PDF 1.4
                const jpegDataUrl = exportCanvas.toDataURL('image/jpeg', metrics.jpegQuality);
                const jpegBytes = SiteplanExportHelper.dataUrlToUint8Array(jpegDataUrl);
                const pdfLayout = SiteplanExportHelper.computePdfPageLayout(
                    exportCanvas.width,
                    exportCanvas.height,
                    siteplanExportState.paper,
                    siteplanExportState.orientation
                );
                const pdfBytes = SiteplanExportHelper.buildPdfFromJpegBytes(
                    jpegBytes,
                    exportCanvas.width,
                    exportCanvas.height,
                    pdfLayout
                );
                const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
                return new Promise(function(resolve) {
                    triggerBlobDownload(pdfBlob, filename, resolve);
                });
            }).then(function() {
                $('#modal-export-siteplan').modal('hide');
            }).catch(function(err) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Export gagal',
                        text: (err && err.message) ? err.message : 'Terjadi kesalahan saat merender file export.'
                    });
                }
            }).finally(function() {
                $confirmBtn.prop('disabled', false).html(originalHtml);
            });
        }, 30);
    }

    $(document).on('change', '#export-area-options input[name="export_area"]', function() {
        siteplanExportState.area = String($(this).val() || 'full');
        syncExportModalUI();
    });

    $(document).on('change', '#export-format-options input[name="export_format"]', function() {
        siteplanExportState.format = String($(this).val() || 'png');
        syncExportModalUI();
    });

    $(document).on('change', '#export-quality-select', function() {
        siteplanExportState.quality = String($(this).val() || 'hd');
        syncExportModalUI();
    });

    $(document).on('change', '#export-pdf-paper', function() {
        siteplanExportState.paper = String($(this).val() || 'a3');
        syncExportModalUI();
    });

    $(document).on('change', '#export-pdf-orientation', function() {
        siteplanExportState.orientation = String($(this).val() || 'auto');
        syncExportModalUI();
    });

    $(document).on('click', '#btn-open-export-crop', function() {
        startExportCropSelection();
    });

    $(document).on('click', '#btn-export-crop-apply', function() {
        applyExportCropSelection();
    });

    $(document).on('click', '#btn-export-crop-cancel', function() {
        cancelExportCropSelection();
    });

    $(document).on('click', '#btn-export-crop-reset', function() {
        siteplanExportState.customCrop = null;
        startExportCropSelection();
    });

    $(document).on('click', '#btn-confirm-export-siteplan', function() {
        executeSiteplanExport();
    });

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
        filter.id_cluster = selectedClusterIds()
        filter.id_jalan = $("#filter-id_jalan").val()
        load_kavling()
    }

    function hapus_filter_option() {
        $('#filter-id_jalan').val(null).trigger('change.select2');
        filter.id_jalan = '';
        load_kavling()
    }

    //select2 cluster
    const siteplanClusterSelectData = (siteplanClusterOptions || []).map(function(item) {
        return {
            id: String(item.id_cluster),
            text: item.nama_cluster
        };
    });

    $("#filter-id_cluster").select2({
        placeholder: "Pilih Cluster",
        allowClear: true,
        closeOnSelect: false,
        data: siteplanClusterSelectData,
        width: '100%'
    })
    let previousClusterValues = [];
    // on select cluster
    $("#filter-id_cluster").on("change", function(e) {
        const rawVal = $(this).val();
        let currentVal = Array.isArray(rawVal) ? rawVal : (rawVal == null || rawVal === '' ? [] : [rawVal]);

        const hadAll = previousClusterValues.includes('all');
        const hasAll = currentVal.includes('all');

        if (!hadAll && hasAll) {
            // User baru saja klik "Semua Cluster" -> deselect cluster individual
            currentVal = ['all'];
            previousClusterValues = currentVal.slice();
            $(this).val(currentVal).trigger('change');
            return;
        } else if (hadAll && hasAll && currentVal.length > 1) {
            // User sebelumnya memilih "Semua Cluster" lalu memilih cluster tertentu -> lepas "Semua Cluster"
            currentVal = currentVal.filter(function(v) { return v !== 'all'; });
            previousClusterValues = currentVal.slice();
            $(this).val(currentVal).trigger('change');
            return;
        }

        previousClusterValues = currentVal.slice();
        const clusterIds = selectedClusterIds();
        $('#filter-id_jalan').val(null).trigger('change.select2');
        filter.id_jalan = '';
        $("#filter-id_jalan").prop("disabled", clusterIds.length === 0);
        setSiteplanClusterHint(true, clusterIds.length === 0
            ? 'Pilih minimal satu cluster untuk menampilkan data siteplan.'
            : 'Klik Terapkan untuk memuat cluster terpilih.');
        if (clusterIds.length === 0) {
            showSiteplanClusterRequiredAlert();
        }
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
                    id_cluster: selectedClusterIds(),
                    id_proyek: dt_proyek.id_proyek
                };
            },
            processResults: function(r) {
                csrfHash = r.token

                let results = [];
                $.each(r.data, function(index, item) {
                    results.push({
                        id: item[0],
                        text: item[2] + ' â€” ' + item[3]
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })

    const availableClusterIds = siteplanClusterSelectData.map(function(item) { return item.id; });
    const restoredClusterIds = SiteplanFilterState.restore(
        getSiteplanStorage(),
        siteplanCurrentUserId,
        dt_proyek.id_proyek,
        availableClusterIds
    );
    const pendingTiketAction = getPendingSiteplanTiketAction();
    const targetClusterIds = SiteplanFilterState.normalizeIds(
        pendingTiketAction ? pendingTiketAction.targetClusterId : null
    ).filter(function(id) {
        return availableClusterIds.includes(id);
    });

    let initialClusterSelection = [];
    if (targetClusterIds.length > 0) {
        initialClusterSelection = targetClusterIds;
    } else if (restoredClusterIds.includes('all')) {
        initialClusterSelection = ['all'];
    } else {
        initialClusterSelection = SiteplanFilterState.normalizeIds(restoredClusterIds);
    }

    previousClusterValues = initialClusterSelection.slice();
    $('#filter-id_cluster').val(initialClusterSelection).trigger('change.select2');
    const initialClusterIds = selectedClusterIds();
    $('#filter-id_jalan').prop('disabled', initialClusterIds.length === 0);
    filter.id_cluster = initialClusterIds;
    filter.id_jalan = '';
    siteplanFilterReady = true;
    renderActiveFilterTags();
    tryLoadInitialSiteplanData();

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")


    //context menu
    let currentShape;

    // â”€â”€ Dynamic context menu dari home/getMenuItemsJson â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function loadSiteplanMenuItems() {
        return $.ajax({
            url: base_url + 'home/getMenuItemsJson',
            type: 'post',
            data: { [csrfName]: csrfHash },
            dataType: 'json'
        }).done(function(response) {
            csrfHash = response.token;
            window.siteplanMenuItems = response.items || [];
        }).fail(function() {
            window.siteplanMenuItems = [];
        });
    }

    function buildContextMenuItems(sh) {
        const container = document.getElementById('menu-dynamic-items');
        if (!container) return;
        container.innerHTML = '';

        const items = window.siteplanMenuItems || [];
        if (items.length === 0) return;

        // Divider antara tombol "Detail" dan dynamic items
        const divider = document.createElement('div');
        divider.className = 'ctx-menu-divider';
        container.appendChild(divider);

        let lastGroup = null;

        items.forEach(function(item) {
            // Group header hanya untuk admin (role 1)
            if (parseInt(roleid, 10) === 1 && item.group_label && item.group_label !== lastGroup) {
                lastGroup = item.group_label;
                const header = document.createElement('div');
                header.className = 'ctx-menu-header';
                header.textContent = item.group_label;
                container.appendChild(header);
            }

            const btn = document.createElement('button');
            if (item.icon) {
                const icon = document.createElement('i');
                icon.className = item.icon;
                icon.style.marginRight = '6px';
                btn.appendChild(icon);
            }
            btn.appendChild(document.createTextNode(item.label || ''));

            btn.addEventListener('click', function() {
                menuNode.style.display = 'none';
                runSiteplanMenuActionFromCanvas(item.onclick, sh, item);
            });

            container.appendChild(btn);
        });
    }

    function runSiteplanMenuActionFromCanvas(onclick, sh, menuItem) {
        if (!onclick || !sh) return;

        // Set editdtt agar isi_data() dan open_* functions tahu kavling mana yang dipilih
        editdtt = [sh];

        const targetOnclick = String(onclick).trim();

        if (targetOnclick === 'isi_data()') {
            // Jika admin, paksa role sesuai id_group dari menu item
            const originalPilihDivisi = $('#pilih-divisi').val();
            if (parseInt(roleid, 10) === 1 && menuItem && menuItem.id_group) {
                $('#pilih-divisi').val(String(menuItem.id_group));
            }
            isi_data();
            // Kembalikan filter setelah dipanggil (jika perlu)
            if (parseInt(roleid, 10) === 1 && menuItem && menuItem.id_group) {
                $('#pilih-divisi').val(originalPilihDivisi);
            }
            return;
        }

        try {
            const fn = new Function(targetOnclick);
            fn.call(window);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Aksi gagal',
                text: error.message || 'Fungsi tidak tersedia di halaman ini.'
            });
        }
    }
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    document.getElementById('menu-btn-lihat_detail').addEventListener('click', () => {
        if (currentShape.target.attrs.id) {
            //open detail modal
            lihat_detail();
        }
    });
    var menuNode = document.getElementById('menu');
    window.addEventListener('click', (e) => {
        // hide menu
        menuNode.style.display = 'none';
    });
    window.addEventListener('touchstart', (e) => {
        // hide menu on mobile tap outside
        if (!menuNode.contains(e.target)) {
            menuNode.style.display = 'none';
        }
    });
    stage.on('contextmenu dblclick dbltap', function(e) {
        // prevent default behavior
        e.evt.preventDefault();

        if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) {
            return;
        }
        if (typeof window.isTargetSetModeActive === 'function' && window.isTargetSetModeActive()) {
            return;
        }
        if (typeof window.isRequestKavlingCanvasPickActive === 'function' && window.isRequestKavlingCanvasPickActive()) {
            return;
        }

        if (e.target === stage) {
            // if we are on empty place of the stage we will do nothing
            return;
        }

        // Resolve target attributes. If clicking on selection border/text, use editdtt[0]
        let targetAttrs = e.target.attrs;
        const shapeId = targetAttrs ? targetAttrs.id : '';
        
        if (shapeId === 'sel' || shapeId === 'tsel' || shapeId === 'line_ms' || (shapeId && shapeId.startsWith('border'))) {
            if (typeof editdtt !== 'undefined' && editdtt.length > 0) {
                targetAttrs = editdtt[0];
            } else {
                return;
            }
        } else if (!shapeId || !shapeId.startsWith('kav')) {
            // Hanya tampilkan menu untuk kavling (bukan jalan/fasos/dll)
            return;
        }

        currentShape = e;

        // Set editdtt supaya tombol Detail (lihat_detail) bisa digunakan
        editdtt = [targetAttrs];

        // Render dynamic menu items dengan data shape saat ini
        buildContextMenuItems(targetAttrs);

        // show menu
        menuNode.style.display = 'initial';
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
        const node = findSiteplanLocationNode('kavling', id_kavling);
        return node && node.attrs ? node.attrs : null;
    }

    function findSiteplanLocationNode(refType, refId) {
        if (!refId || typeof siteplan === 'undefined') {
            return null;
        }

        const nodeId = refType === 'others' ? '#others' + refId : '#kav' + refId;
        try {
            return typeof siteplan.findOne === 'function' ?
                siteplan.findOne(nodeId) :
                (siteplan.find(nodeId)[0] || null);
        } catch (error) {
            return null;
        }
    }

    function focusSiteplanLocation(node) {
        if (!node || typeof node.getClientRect !== 'function') {
            return false;
        }

        const bounds = node.getClientRect({
            relativeTo: siteplan,
            skipShadow: true
        });
        if (!bounds || bounds.width <= 0 || bounds.height <= 0) {
            return false;
        }

        const viewportWidth = stage.width();
        const viewportHeight = stage.height();
        const currentScale = Math.min(stage.scaleX() || 1, 1.5);
        const targetScale = Math.max(currentScale, Math.min(
            viewportWidth / Math.max(bounds.width * 6, 1),
            viewportHeight / Math.max(bounds.height * 6, 1),
            1.5
        ));
        const centerX = bounds.x + (bounds.width / 2);
        const centerY = bounds.y + (bounds.height / 2);

        group.scale({
            x: 1 / targetScale,
            y: 1 / targetScale
        });

        new Konva.Tween({
            node: stage,
            duration: 0.45,
            scaleX: targetScale,
            scaleY: targetScale,
            x: (viewportWidth / 2) - (centerX * targetScale),
            y: (viewportHeight / 2) - (centerY * targetScale),
            easing: Konva.Easings.EaseInOut
        }).play();

        return true;
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

    function getPendingSiteplanTiketAction() {
        const params = new URLSearchParams(window.location.search || '');
        const idTiket = params.get('show_tiket');
        const refType = params.get('ref_type');
        const refId = params.get('ref_id');

        if (!idTiket || !['kavling', 'others'].includes(refType) || !refId) {
            return null;
        }

        return {
            idTiket: idTiket,
            refType: refType,
            refId: refId,
            targetClusterId: params.get('target_cluster_id')
        };
    }

    let pendingSiteplanTiketActionConsumed = false;
    function handlePendingSiteplanTiketAction() {
        if (pendingSiteplanTiketActionConsumed) {
            return;
        }

        const action = getPendingSiteplanTiketAction();
        if (!action) {
            return;
        }

        setTimeout(function() {
            if (pendingSiteplanTiketActionConsumed) {
                return;
            }

            const node = findSiteplanLocationNode(action.refType, action.refId);
            const sh = node && node.attrs ? node.attrs : null;
            if (!sh) {
                return;
            }

            pendingSiteplanTiketActionConsumed = true;
            if (sh) {
                if (typeof hapus_seleksi === 'function') hapus_seleksi();
                editdtt.push(sh);
                if (typeof drawBorderEdit === 'function') drawBorderEdit(sh);
                focusSiteplanLocation(node);
            }

        }, 600);
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
                                status_mkdt: v.status_mkdt,
                                is_lunas: v.is_lunas,
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
                                    <strong>${item.nama_konsumen}</strong>
                                    ${item.status_mkdt == 'Batal' ? '<span class="badge badge-danger">Batal</span>' : ''}
                                    ${item.is_lunas == '1' ? '<span class="badge badge-success">Lunas</span>' : ''}
                                    <br>
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
            if (typeof window.isExportCropModeActive === 'function' && window.isExportCropModeActive()) {
                cancelExportCropSelection();
                return;
            }
            if (typeof window.isRequestKavlingCanvasPickActive === 'function' && window.isRequestKavlingCanvasPickActive()
                && typeof window.cancelRequestKavlingCanvasPick === 'function') {
                window.cancelRequestKavlingCanvasPick();
                return;
            }
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

    let pendingNotificationDeepLinkConsumed = false;
    window.handlePendingNotificationDeepLink = function() {
        if (pendingNotificationDeepLinkConsumed) return;
        pendingNotificationDeepLinkConsumed = true;
        
        const params = new URLSearchParams(window.location.search);
        const id_kavling = params.get('id_kavling');
        const tab = params.get('tab');
        
        if (id_kavling) {
            setTimeout(function() {
                const sh = findSiteplanKavlingAttrs(id_kavling);
                if (sh && typeof detail_kavling === 'function') {
                    hapus_seleksi();
                    editdtt.push(sh);
                    drawBorderEdit(sh);
                    detail_kavling(sh, id_kavling);
                    
                    if (tab) {
                        setTimeout(function() {
                            const tabTarget = $('#modal_detail .nav-tabs a[href="#detail-panel-' + tab + '"]');
                            if (tabTarget.length) {
                                tabTarget.tab('show');
                            }
                        }, 500); // Wait for modal and content to render
                    }
                }
            }, 300);
        }
    };
