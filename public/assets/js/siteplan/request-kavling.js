/**
 * Modul Siteplan: Request Kavling & Perubahan Tipe (Unified Modal + Canvas Picker)
 */
(function ($) {
    'use strict';

    const config = window.SITEPLAN_REQUEST_KAVLING_CONFIG || {};
    const currentRole = typeof roleid !== 'undefined' ? Number(roleid) : 0;
    const canCreate = typeof config.canCreate === 'boolean' ? config.canCreate : (currentRole === 1 || currentRole === 4);
    const canProcess = typeof config.canProcess === 'boolean' ? config.canProcess : (currentRole === 1 || currentRole === 6);

    const $modal = $('#modal-request-kavling');
    if (!$modal.length) {
        return;
    }

    const $modalApply = $('#modal-apply-ubah-kavling');
    const $modalPreview = $('#modal-rk-tipe-preview');
    const $modalLightbox = $('#modal-rk-lightbox');
    const $pickerBar = $('#rk-canvas-picker-bar');

    const rkState = {
        jalanList: [],
        jalansLoadedForProyek: 0,
        tipeList: [],
        tipeMap: {},
        tipesLoadedForProyek: 0,
        kavlingMap: {},
        requestMap: {},
        counts: {
            all: 0,
            pending: 0,
            tambah_baru_pending: 0,
            ubah_tipe_pending: 0,
            approved: 0,
            rejected: 0,
            cancelled: 0,
            history: 0
        },
        tambahFilterStatus: 'pending',
        ubahFilterStatus: 'pending',
        riwayatFilterStatus: 'all',
        riwayatFilterJenis: 'all',
        selectedCancelIds: {
            tambah: new Set(),
            ubah: new Set(),
            riwayat: new Set()
        },
        visibleCancellableIds: {
            tambah: [],
            ubah: [],
            riwayat: []
        },
        canvasPickActive: false,
        canvasPickedItems: [],
        canvasPickRestoreItems: [],
        highlightGroup: null,
        isSubmittingTambah: false,
        isSubmittingUbah: false,
        isSubmittingApply: false,
        isCancelling: false,
        isRefreshingTipes: false
    };

    function rkApiUrl(path) {
        const base = typeof base_url !== 'undefined' ? String(base_url || '') : '';
        const cleanBase = base.replace(/\/+$/, '');
        const cleanPath = String(path || '').replace(/^\/+/, '');
        return cleanBase + '/' + cleanPath;
    }

    const rkEndpoints = {
        jalans: rkApiUrl('api/jalan/all'),
        tipes: rkApiUrl('api/tipe/all?only_active=1&format=raw'),
        kavlingList: rkApiUrl('api/kavling-request/kavling-list'),
        list: rkApiUrl('api/kavling-request/list'),
        submit: rkApiUrl('api/kavling-request/submit'),
        updateStatus: rkApiUrl('api/kavling-request/update-status'),
        applyUbahKavling: rkApiUrl('api/kavling-request/apply-ubah-kavling'),
        cancel: rkApiUrl('api/kavling-request/cancel')
    };

    function getProyekId() {
        const fromInput = parseInt($('#req-id_proyek').val() || '0', 10);
        if (fromInput > 0) return fromInput;
        if (config.idProyek && Number(config.idProyek) > 0) return Number(config.idProyek);
        if (typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.id_proyek) {
            return Number(dt_proyek.id_proyek) || 0;
        }
        return 0;
    }

    function updateCsrf(token) {
        if (token && typeof csrfHash !== 'undefined') {
            csrfHash = token;
        }
    }

    function extractErrorMessage(xhr, fallback) {
        const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
        if (json) {
            updateCsrf(json.token);
            if (json.messages && typeof json.messages === 'string') {
                return json.messages;
            }
            if (json.errors && typeof json.errors === 'object') {
                const errs = Object.values(json.errors).filter(Boolean);
                if (errs.length > 0) {
                    return errs.join(' ');
                }
            }
        }
        return fallback || 'Terjadi kesalahan saat memproses permintaan.';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[ch];
        });
    }

    function sanitizeAccessUrl(url) {
        const raw = String(url == null ? '' : url).trim();
        if (!raw) return '';
        if (/^(https?:\/\/|\/(?!\/))/i.test(raw)) {
            return raw;
        }
        return '';
    }

    function formatDateTime(value) {
        if (!value) return '-';
        if (typeof format_datetime === 'function') {
            try {
                return format_datetime(value) || value;
            } catch (e) {
                return String(value).substring(0, 16);
            }
        }
        return String(value).substring(0, 16);
    }

    function isValidDateStr(value) {
        if (typeof value !== 'string') return false;
        const trimmed = value.trim();
        return /^\d{4}-\d{2}-\d{2}/.test(trimmed) && trimmed.indexOf('0000-00-00') !== 0;
    }

    function showToast(icon, title, text) {
        if (typeof Swal === 'undefined') return;
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: icon === 'success' ? 2400 : 3500,
            timerProgressBar: true
        });
        Toast.fire({
            icon: icon || 'info',
            title: title || '',
            text: text || ''
        });
    }

    function formatTipeLabel(tipe) {
        if (!tipe) return '';
        const noTipe = String(tipe.no_tipe_rumah || '').trim();
        const namaTipe = String(tipe.tipe_rumah || '').trim();
        let label = '';
        if (noTipe && namaTipe) {
            label = noTipe + ' (' + namaTipe + ')';
        } else {
            label = noTipe || namaTipe || ('Tipe #' + (tipe.id_tipe || ''));
        }
        const lb = Number(tipe.lb || 0);
        const lt = Number(tipe.lt || 0);
        if (lb > 0 || lt > 0) {
            label += ' · ' + (lb > 0 ? lb : '-') + '/' + (lt > 0 ? lt : '-') + ' m²';
        }
        return label;
    }

    function formatDimensionPair(first, second, unit) {
        const a = String(first == null ? '' : first).trim();
        const b = String(second == null ? '' : second).trim();
        const hasA = a !== '' && a !== '0';
        const hasB = b !== '' && b !== '0';
        if (!hasA && !hasB) return '-';
        return (hasA ? a : '-') + ' / ' + (hasB ? b : '-') + (unit ? (' ' + unit) : '');
    }

    function buildSpecVal(val) {
        const text = String(val == null ? '' : val).trim();
        return text || '-';
    }

    function checkAkadBlockFromShapeData(data, data2) {
        const d = data || {};
        const d2 = data2 || {};
        const idMkdt = Number(d.id_mkdt || 0);
        const isBatal = Number(d.is_batal || 0) === 1;
        const statusMkdt = String(d2.status_mkdt || d.status_mkdt || '').trim();
        const statusLower = statusMkdt.toLowerCase();

        if (!idMkdt || isBatal || statusLower === 'batal') {
            return { blocked: false, label: '' };
        }

        const isAkad = statusLower === 'akad'
            || Number(d.akad || 0) === 1
            || isValidDateStr(d.akad_tgl);
        if (isAkad) {
            return { blocked: true, label: 'Akad' };
        }

        const isAkadIndent = Number(d.akad_indent || 0) === 1
            || isValidDateStr(d.akad_indent_tgl);
        if (isAkadIndent) {
            return { blocked: true, label: 'Akad Indent' };
        }

        return { blocked: false, label: '' };
    }

    function buildShapeKavlingItem(attrs, fallbackId) {
        const d = (attrs && attrs.data) ? attrs.data : {};
        const d2 = (attrs && attrs.data2) ? attrs.data2 : {};
        const rawId = attrs && attrs.id ? String(attrs.id) : '';
        const kavId = Number(d.id_kavling || fallbackId || (rawId.indexOf('kav') === 0 ? rawId.substring(3) : 0));
        const namaJalan = String(d.nama_jalan || '').trim();
        const noKavling = String(d.no_kavling || kavId).trim();
        const noTipe = String(d2.no_tipe_rumah || '').trim();
        const namaTipe = String(d2.tipe_rumah || '').trim();
        let currentTipeLabel = null;
        if (noTipe && namaTipe) {
            currentTipeLabel = noTipe + ' (' + namaTipe + ')';
        } else if (noTipe || namaTipe) {
            currentTipeLabel = noTipe || namaTipe;
        }

        const idMkdt = Number(d.id_mkdt || 0);
        const isBatal = Number(d.is_batal || 0) === 1;
        const statusMkdt = String(d2.status_mkdt || d.status_mkdt || '').trim();
        const hasActiveKonsumen = idMkdt > 0 && !isBatal && statusMkdt.toLowerCase() !== 'batal';
        const namaKonsumen = hasActiveKonsumen ? (d.nama_konsumen ? String(d.nama_konsumen).trim() : null) : null;

        let text = (namaJalan ? (namaJalan + ' ') : '') + 'No. ' + noKavling;
        if (currentTipeLabel) {
            text += ' - ' + currentTipeLabel;
        }
        if (hasActiveKonsumen) {
            if (namaKonsumen) {
                text += ' [Konsumen: ' + namaKonsumen + (statusMkdt ? ' - ' + statusMkdt : '') + ']';
            } else {
                text += ' [Konsumen Aktif' + (statusMkdt ? ' - ' + statusMkdt : '') + ']';
            }
        }

        return {
            id: kavId,
            id_kavling: kavId,
            no_kavling: noKavling,
            nama_jalan: namaJalan,
            current_id_tipe: d2.id_tipe ? Number(d2.id_tipe) : null,
            current_tipe_label: currentTipeLabel,
            has_active_konsumen: hasActiveKonsumen,
            nama_konsumen: namaKonsumen,
            status_mkdt: hasActiveKonsumen ? (statusMkdt || null) : null,
            text: text,
            points: (attrs && Array.isArray(attrs.points) && attrs.points.length >= 6) ? attrs.points : null
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DATA LOADERS: JALAN, TIPE AKTIF, & REQUEST LIST / COUNTS
    // ─────────────────────────────────────────────────────────────────────────

    function populateJalanSelect() {
        const $sel = $('#rk-tambah-jalans');
        if (!$sel.length) return;

        const prevVals = ($sel.val() || []).map(String);
        $sel.empty();

        rkState.jalanList.forEach(function (j) {
            const label = j.nama_cluster
                ? (String(j.nama_jalan || '') + ' — ' + String(j.nama_cluster))
                : String(j.nama_jalan || ('Jalan #' + j.id_jalan));
            const opt = new Option(label, String(j.id_jalan), false, prevVals.includes(String(j.id_jalan)));
            $sel.append(opt);
        });

        $sel.trigger('change');
    }

    function loadJalans(force) {
        if (!canCreate) return $.Deferred().resolve([]).promise();
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve([]).promise();

        if (!force && rkState.jalansLoadedForProyek === idProyek && rkState.jalanList.length > 0) {
            populateJalanSelect();
            return $.Deferred().resolve(rkState.jalanList).promise();
        }

        return $.ajax({
            url: rkApiUrl('api/jalan/all'),
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                format: 'raw'
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            const rows = Array.isArray(res && res.data) ? res.data : [];
            rkState.jalanList = rows;
            rkState.jalansLoadedForProyek = idProyek;
            populateJalanSelect();
        }).fail(function (xhr) {
            extractErrorMessage(xhr, 'Gagal memuat daftar jalan.');
        });
    }

    function populateSingleTipeSelect($sel, preserveVal) {
        if (!$sel.length) return;

        const currentVal = preserveVal !== undefined ? String(preserveVal || '') : String($sel.val() || '');
        $sel.empty();
        $sel.append(new Option('', '', false, false));

        let foundCurrent = false;
        rkState.tipeList.forEach(function (t) {
            const idStr = String(t.id_tipe);
            const isSelected = currentVal !== '' && currentVal === idStr;
            if (isSelected) foundCurrent = true;
            $sel.append(new Option(formatTipeLabel(t), idStr, false, isSelected));
        });

        $sel.val(foundCurrent ? currentVal : '').trigger('change');
    }

    function syncAllTipeSelects(preferredApplyVal) {
        populateSingleTipeSelect($('#rk-tambah-id_tipe'));
        populateSingleTipeSelect($('#rk-ubah-id_tipe'));
        populateSingleTipeSelect($('#rk-apply-id_tipe'), preferredApplyVal);
    }

    function loadActiveTipes(force, preferredApplyVal) {
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve([]).promise();

        if (!force && rkState.tipesLoadedForProyek === idProyek && rkState.tipeList.length > 0) {
            syncAllTipeSelects(preferredApplyVal);
            return $.Deferred().resolve(rkState.tipeList).promise();
        }

        return $.ajax({
            url: rkApiUrl('api/tipe/all?only_active=1&format=raw'),
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            const rows = Array.isArray(res && res.data) ? res.data : [];
            const activeRows = rows.filter(function (item) {
                return item && Number(item.is_active != null ? item.is_active : 1) === 1;
            });
            rkState.tipeList = activeRows;
            rkState.tipeMap = {};
            activeRows.forEach(function (item) {
                if (item && item.id_tipe) {
                    rkState.tipeMap[String(item.id_tipe)] = item;
                }
            });
            rkState.tipesLoadedForProyek = idProyek;
            syncAllTipeSelects(preferredApplyVal);
        }).fail(function (xhr) {
            extractErrorMessage(xhr, 'Gagal memuat daftar tipe aktif.');
        });
    }

    function updateBadgesAndCounters(counts) {
        if (counts && typeof counts === 'object') {
            rkState.counts = {
                all: Number(counts.all || 0),
                pending: Number(counts.pending || 0),
                tambah_baru_pending: Number(counts.tambah_baru_pending || 0),
                ubah_tipe_pending: Number(counts.ubah_tipe_pending || 0),
                approved: Number(counts.approved || 0),
                rejected: Number(counts.rejected || 0),
                cancelled: Number(counts.cancelled || 0),
                history: Number(counts.history || 0)
            };
        }

        const c = rkState.counts;
        const $sidebarBadge = $('#badge-request-kavling-pending');
        if ($sidebarBadge.length) {
            $sidebarBadge.text(c.pending);
            $sidebarBadge.prop('hidden', c.pending <= 0);
        }

        const $tabTambahBadge = $('#badge-rk-tab-tambah');
        if ($tabTambahBadge.length) {
            $tabTambahBadge.text(c.tambah_baru_pending);
            $tabTambahBadge.prop('hidden', c.tambah_baru_pending <= 0);
        }

        const $tabUbahBadge = $('#badge-rk-tab-ubah');
        if ($tabUbahBadge.length) {
            $tabUbahBadge.text(c.ubah_tipe_pending);
            $tabUbahBadge.prop('hidden', c.ubah_tipe_pending <= 0);
        }

        const $tabRiwayatBadge = $('#badge-rk-tab-riwayat');
        if ($tabRiwayatBadge.length) {
            $tabRiwayatBadge.text(c.all);
        }

        $('#rk-tambah-count-pending').text(c.tambah_baru_pending);
        $('#rk-ubah-count-pending').text(c.ubah_tipe_pending);
        $('#rk-mkdt-tambah-pending-count').text(c.tambah_baru_pending);
        $('#rk-mkdt-ubah-pending-count').text(c.ubah_tipe_pending);
        $('#rk-riwayat-count-all').text(c.all);
        $('#rk-riwayat-count-pending').text(c.pending);
        $('#rk-riwayat-count-approved').text(c.approved);
        $('#rk-riwayat-count-rejected').text(c.rejected);
        $('#rk-riwayat-count-cancelled').text(c.cancelled);
    }

    function cacheRequests(rows) {
        (Array.isArray(rows) ? rows : []).forEach(function (item) {
            if (item && item.id_request) {
                rkState.requestMap[String(item.id_request)] = item;
            }
        });
    }

    function loadCountsSummary() {
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve(null).promise();

        return $.ajax({
            url: rkApiUrl('api/kavling-request/list'),
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                page: 1,
                per_page: 1
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            if (res && res.success && res.counts) {
                updateBadgesAndCounters(res.counts);
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PREVIEW CARD & LIGHTBOX TIPE
    // ─────────────────────────────────────────────────────────────────────────

    function buildTipePreviewInnerHtml(tipe) {
        const title = formatTipeLabel(tipe);
        const isSubsidi = Number(tipe.is_subsidi || 0) === 1;
        const badgeHtml = '<span class="rk-preview-category-badge ' + (isSubsidi ? 'is-subsidi' : 'is-komersil') + '">'
            + (isSubsidi ? 'Subsidi' : 'Komersil')
            + '</span>';

        const url3d = sanitizeAccessUrl(tipe.gambar_tipe_access_url);
        const urlDenah = sanitizeAccessUrl(tipe.gambar_denah_access_url);
        const urlKerja = sanitizeAccessUrl(tipe.gambar_kerja_access_url);

        const buildThumb = function (url, caption, emptyLabel) {
            if (url) {
                return '<button type="button" class="rk-thumb-card rk-thumb-zoom-trigger"'
                    + ' data-img-url="' + escapeHtml(url) + '"'
                    + ' data-img-title="' + escapeHtml(caption + ' — ' + title) + '"'
                    + ' title="Klik untuk perbesar ' + escapeHtml(caption) + '">'
                    + '<div class="rk-thumb-img-wrap">'
                    + '<img class="rk-thumb-img" src="' + escapeHtml(url) + '" alt="' + escapeHtml(caption) + '" loading="lazy">'
                    + '<span class="rk-thumb-zoom"><i class="fas fa-search-plus"></i></span>'
                    + '</div>'
                    + '<span class="rk-thumb-caption"><span>' + escapeHtml(caption) + '</span><i class="fas fa-expand-alt text-muted"></i></span>'
                    + '</button>';
            }
            return '<div class="rk-thumb-card">'
                + '<div class="rk-thumb-img-wrap"><span class="rk-thumb-empty">' + escapeHtml(emptyLabel) + '</span></div>'
                + '<span class="rk-thumb-caption"><span>' + escapeHtml(caption) + '</span></span>'
                + '</div>';
        };

        const specs = [
            { label: 'LT / LB', value: formatDimensionPair(tipe.lt, tipe.lb, 'm²') },
            { label: 'KT / KM', value: formatDimensionPair(tipe.jumlah_kamar_tidur, tipe.jumlah_kamar_mandi, '') },
            { label: 'Atap', value: buildSpecVal(tipe.spesifikasi_teknis_atap) },
            { label: 'Dinding', value: buildSpecVal(tipe.spesifikasi_teknis_dinding) },
            { label: 'Lantai', value: buildSpecVal(tipe.spesifikasi_teknis_lantai) },
            { label: 'Pondasi', value: buildSpecVal(tipe.spesifikasi_teknis_pondasi) }
        ];

        const specsHtml = specs.map(function (sp) {
            return '<div class="rk-spec-item">'
                + '<span class="rk-spec-label">' + escapeHtml(sp.label) + '</span>'
                + '<span class="rk-spec-val">' + escapeHtml(sp.value) + '</span>'
                + '</div>';
        }).join('');

        const kerjaHtml = urlKerja
            ? '<div class="rk-preview-footer-actions">'
            + '<a href="' + escapeHtml(urlKerja) + '" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">'
            + '<i class="fas fa-file-pdf mr-25"></i>Lihat Gambar Kerja (PDF)'
            + '</a></div>'
            : '';

        return '<div class="rk-preview-head">'
            + '<span class="rk-preview-title"><i class="fas fa-home text-primary mr-25"></i>' + escapeHtml(title) + '</span>'
            + badgeHtml
            + '</div>'
            + '<div class="rk-preview-grid">'
            + '<div class="rk-preview-thumbs">'
            + buildThumb(url3d, 'Gambar 3D', 'Belum ada gambar 3D')
            + buildThumb(urlDenah, 'Gambar Denah', 'Belum ada denah')
            + '</div>'
            + '<div>'
            + '<div class="rk-preview-specs">' + specsHtml + '</div>'
            + kerjaHtml
            + '</div>'
            + '</div>';
    }

    function renderTipePreviewCard($container, tipeId) {
        if (!$container || !$container.length) return;
        const idStr = String(tipeId || '').trim();
        const tipe = idStr ? (rkState.tipeMap[idStr] || null) : null;

        if (!idStr || !tipe) {
            $container.attr('hidden', true).empty();
            return;
        }

        $container.html(buildTipePreviewInnerHtml(tipe)).removeAttr('hidden');
    }

    function openLightboxModal(imgUrl, titleText) {
        const cleanUrl = sanitizeAccessUrl(imgUrl);
        if (!cleanUrl || !$modalLightbox.length) return;

        $('#rk-lightbox-title').text(titleText || 'Preview Gambar Tipe');
        $('#rk-lightbox-img').attr('src', cleanUrl).attr('alt', titleText || 'Preview Gambar Tipe');
        $('#rk-lightbox-newtab').attr('href', cleanUrl);
        $modalLightbox.modal('show');
    }

    function openTipePreviewModal(tipe, roleLabel) {
        if (!tipe || !$modalPreview.length) return;

        const title = formatTipeLabel(tipe);
        const isSubsidi = Number(tipe.is_subsidi || 0) === 1;
        const url3d = sanitizeAccessUrl(tipe.gambar_tipe_access_url);
        const urlDenah = sanitizeAccessUrl(tipe.gambar_denah_access_url);
        const urlKerja = sanitizeAccessUrl(tipe.gambar_kerja_access_url);

        $('#rk-tipe-preview-modal-title').text((roleLabel ? roleLabel + ': ' : '') + title);

        const buildLargeImgCard = function (url, label, emptyMsg) {
            if (url) {
                return '<button type="button" class="rk-thumb-card rk-thumb-zoom-trigger"'
                    + ' data-img-url="' + escapeHtml(url) + '"'
                    + ' data-img-title="' + escapeHtml(label + ' — ' + title) + '"'
                    + ' title="Klik untuk perbesar">'
                    + '<div class="rk-thumb-img-wrap">'
                    + '<img class="rk-thumb-img" src="' + escapeHtml(url) + '" alt="' + escapeHtml(label) + '">'
                    + '<span class="rk-thumb-zoom"><i class="fas fa-search-plus"></i></span>'
                    + '</div>'
                    + '<span class="rk-thumb-caption"><span>' + escapeHtml(label) + '</span><span class="text-primary small">Perbesar</span></span>'
                    + '</button>';
            }
            return '<div class="rk-thumb-card">'
                + '<div class="rk-thumb-img-wrap"><span class="rk-thumb-empty">' + escapeHtml(emptyMsg) + '</span></div>'
                + '<span class="rk-thumb-caption"><span>' + escapeHtml(label) + '</span></span>'
                + '</div>';
        };

        const infoRows = [
            { label: 'Nama Tipe', value: buildSpecVal(tipe.tipe_rumah) },
            { label: 'Nomor Tipe', value: buildSpecVal(tipe.no_tipe_rumah) },
            { label: 'Luas Tanah / Bangunan', value: formatDimensionPair(tipe.lt, tipe.lb, 'm²') },
            { label: 'Kamar Tidur / Mandi', value: formatDimensionPair(tipe.jumlah_kamar_tidur, tipe.jumlah_kamar_mandi, '') },
            { label: 'Atap', value: buildSpecVal(tipe.spesifikasi_teknis_atap) },
            { label: 'Dinding', value: buildSpecVal(tipe.spesifikasi_teknis_dinding) },
            { label: 'Lantai', value: buildSpecVal(tipe.spesifikasi_teknis_lantai) },
            { label: 'Pondasi', value: buildSpecVal(tipe.spesifikasi_teknis_pondasi) }
        ];

        const infoHtml = infoRows.map(function (row) {
            return '<div class="col-md-6 col-12">'
                + '<div class="info-row">'
                + '<div class="info-label">' + escapeHtml(row.label) + '</div>'
                + '<div class="info-value">' + escapeHtml(row.value) + '</div>'
                + '</div>'
                + '</div>';
        }).join('');

        const kerjaBtnHtml = urlKerja
            ? '<div class="mt-75 text-right">'
            + '<a href="' + escapeHtml(urlKerja) + '" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">'
            + '<i class="fas fa-file-pdf mr-25"></i>Buka Gambar Kerja (PDF)'
            + '</a></div>'
            : '';

        const bodyHtml = '<div class="d-flex align-items-center justify-content-between flex-wrap mb-75" style="gap:.5rem;">'
            + '<div class="font-weight-bold text-dark">' + escapeHtml(title) + '</div>'
            + '<span class="rk-preview-category-badge ' + (isSubsidi ? 'is-subsidi' : 'is-komersil') + '">' + (isSubsidi ? 'Subsidi' : 'Komersil') + '</span>'
            + '</div>'
            + '<div class="rk-spec-modal-images mb-1">'
            + buildLargeImgCard(url3d, 'Gambar 3D / Ilustrasi', 'Belum ada gambar 3D')
            + buildLargeImgCard(urlDenah, 'Gambar Denah', 'Belum ada gambar denah')
            + '</div>'
            + '<div class="divider divider-left"><div class="divider-text">Spesifikasi Teknis</div></div>'
            + '<div class="card rk-card mb-0"><div class="card-body">'
            + '<div class="row">' + infoHtml + '</div>'
            + kerjaBtnHtml
            + '</div></div>';

        $('#rk-tipe-preview-modal-body').html(bodyHtml);
        $modalPreview.modal('show');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SELECT2 INITIALIZATION
    // ─────────────────────────────────────────────────────────────────────────

    function initCreateSelect2Controls() {
        if (!canCreate) return;

        const $jalans = $('#rk-tambah-jalans');
        if ($jalans.length && !$jalans.hasClass('select2-hidden-accessible')) {
            $jalans.select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: '-- Pilih Nama Jalan (Opsional, Multi-Pilih) --',
                allowClear: true
            });
        }

        const $tambahTipe = $('#rk-tambah-id_tipe');
        if ($tambahTipe.length && !$tambahTipe.hasClass('select2-hidden-accessible')) {
            $tambahTipe.select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: '-- Pilih Tipe Rumah Available (Opsional) --',
                allowClear: true
            }).on('change.rkTambahTipe', function () {
                renderTipePreviewCard($('#rk-tambah-tipe-preview'), $(this).val());
            });
        }

        const $ubahTipe = $('#rk-ubah-id_tipe');
        if ($ubahTipe.length && !$ubahTipe.hasClass('select2-hidden-accessible')) {
            $ubahTipe.select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: '-- Pilih Tipe Rumah Available (Opsional) --',
                allowClear: true
            }).on('change.rkUbahTipe', function () {
                renderTipePreviewCard($('#rk-ubah-tipe-preview'), $(this).val());
            });
        }

        const $kavlings = $('#rk-ubah-kavlings');
        if ($kavlings.length && !$kavlings.hasClass('select2-hidden-accessible')) {
            $kavlings.select2({
                dropdownParent: $modal,
                width: '100%',
                placeholder: '-- Cari & Pilih Kavling (Multi-Pilih) --',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: rkApiUrl('api/kavling-request/kavling-list'),
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            [csrfName]: csrfHash,
                            id_proyek: getProyekId(),
                            search: (params && params.term) ? params.term : '',
                            limit: 50
                        };
                    },
                    processResults: function (res) {
                        updateCsrf(res && res.token);
                        const items = Array.isArray(res && res.results)
                            ? res.results
                            : (Array.isArray(res && res.data) ? res.data : []);
                        items.forEach(function (it) {
                            if (it && it.id) {
                                rkState.kavlingMap[String(it.id)] = it;
                            }
                        });
                        return {
                            results: items.map(function (it) {
                                return {
                                    id: String(it.id),
                                    text: it.text || ((it.nama_jalan || '') + ' No. ' + (it.no_kavling || it.id)),
                                    kavData: it
                                };
                            })
                        };
                    },
                    cache: false
                },
                templateResult: function (opt) {
                    if (!opt || opt.loading || !opt.id) {
                        return opt ? opt.text : '';
                    }
                    const k = opt.kavData || rkState.kavlingMap[String(opt.id)] || null;
                    if (!k) {
                        return escapeHtml(opt.text || '');
                    }
                    const mainLabel = (k.nama_jalan ? (k.nama_jalan + ' ') : '') + 'No. ' + (k.no_kavling || k.id);
                    const tipeBadge = k.current_tipe_label
                        ? '<span class="rk-s2-pill">Tipe: ' + escapeHtml(k.current_tipe_label) + '</span>'
                        : '<span class="rk-s2-pill">Belum ada tipe</span>';
                    let konsumenBadge = '';
                    if (k.has_active_konsumen) {
                        const konText = k.nama_konsumen
                            ? ('Konsumen: ' + k.nama_konsumen + (k.status_mkdt ? ' (' + k.status_mkdt + ')' : ''))
                            : ('Konsumen Aktif' + (k.status_mkdt ? ' (' + k.status_mkdt + ')' : ''));
                        konsumenBadge = '<span class="rk-s2-pill is-konsumen"><i class="fas fa-user mr-25"></i>' + escapeHtml(konText) + '</span>';
                    }
                    return $('<div class="rk-s2-kav-item">'
                        + '<div class="rk-s2-kav-main">' + escapeHtml(mainLabel) + '</div>'
                        + '<div class="rk-s2-kav-meta">' + tipeBadge + konsumenBadge + '</div>'
                        + '</div>');
                },
                templateSelection: function (opt) {
                    if (!opt || !opt.id) return opt ? opt.text : '';
                    const k = opt.kavData || rkState.kavlingMap[String(opt.id)] || null;
                    if (!k) return opt.text || '';
                    let label = (k.nama_jalan ? (k.nama_jalan + ' ') : '') + 'No. ' + (k.no_kavling || k.id);
                    if (k.current_tipe_label) {
                        label += ' (' + k.current_tipe_label + ')';
                    }
                    if (k.has_active_konsumen) {
                        label += ' [' + (k.nama_konsumen || 'Konsumen Aktif') + (k.status_mkdt ? ' - ' + k.status_mkdt : '') + ']';
                    }
                    return label;
                }
            }).on('select2:select.rkKav', function (e) {
                const data = e && e.params ? e.params.data : null;
                if (data && data.kavData && data.kavData.id) {
                    rkState.kavlingMap[String(data.kavData.id)] = data.kavData;
                }
                renderUbahSelectedKonsumenWarning();
            }).on('change.rkKav', function () {
                renderUbahSelectedKonsumenWarning();
            });
        }
    }

    function initApplySelect2Control() {
        if (!canProcess) return;
        const $applyTipe = $('#rk-apply-id_tipe');
        if ($applyTipe.length && !$applyTipe.hasClass('select2-hidden-accessible')) {
            $applyTipe.select2({
                dropdownParent: $modalApply,
                width: '100%',
                placeholder: '-- Pilih Tipe Rumah Baru --',
                allowClear: true
            }).on('change.rkApplyTipe', function () {
                renderTipePreviewCard($('#rk-apply-tipe-preview'), $(this).val());
            });
        }
    }

    function renderUbahSelectedKonsumenWarning() {
        const $box = $('#rk-ubah-selected-info');
        if (!$box.length) return;

        const ids = ($('#rk-ubah-kavlings').val() || []).map(String);
        const withKonsumen = [];
        ids.forEach(function (id) {
            const k = rkState.kavlingMap[id];
            if (k && k.has_active_konsumen) {
                const kavTitle = (k.nama_jalan ? (k.nama_jalan + ' ') : '') + 'No. ' + (k.no_kavling || id);
                const konDetail = (k.nama_konsumen || 'Konsumen Aktif') + (k.status_mkdt ? ' — ' + k.status_mkdt : '');
                withKonsumen.push(kavTitle + ' (' + konDetail + ')');
            }
        });

        if (withKonsumen.length === 0) {
            $box.attr('hidden', true).empty();
            return;
        }

        const listHtml = withKonsumen.map(function (txt) {
            return '<li>' + escapeHtml(txt) + '</li>';
        }).join('');

        $box.html(
            '<div class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-25"></i>Info Konsumen Aktif pada Kavling Terpilih</div>'
            + '<div class="mt-25">Kavling berikut sudah memiliki konsumen (non-Akad) dan tetap dapat diajukan perubahan tipe:</div>'
            + '<ul class="rk-konsumen-alert-list">' + listHtml + '</ul>'
        ).removeAttr('hidden');
    }

    function setUbahSelectedKavlings(items) {
        const $kavlings = $('#rk-ubah-kavlings');
        if (!$kavlings.length) return;

        const normalized = Array.isArray(items) ? items : [];
        const selectedIds = [];
        normalized.forEach(function (it) {
            if (!it) return;
            const idNum = Number(it.id || it.id_kavling || 0);
            if (!idNum) return;
            const idStr = String(idNum);
            const merged = Object.assign({}, rkState.kavlingMap[idStr] || {}, it, { id: idNum });
            rkState.kavlingMap[idStr] = merged;

            let $opt = $kavlings.find('option[value="' + idStr + '"]');
            if (!$opt.length) {
                $opt = $(new Option(merged.text || ('No. ' + (merged.no_kavling || idStr)), idStr, true, true));
                $kavlings.append($opt);
            } else {
                $opt.text(merged.text || $opt.text()).prop('selected', true);
            }
            selectedIds.push(idStr);
        });

        $kavlings.val(selectedIds).trigger('change');
        renderUbahSelectedKonsumenWarning();
    }

    function hydrateMissingConsumerNames(items) {
        const list = (Array.isArray(items) ? items : []).filter(function (it) {
            return it && it.has_active_konsumen && !it.nama_konsumen && it.no_kavling;
        });
        if (list.length === 0) return;

        const idProyek = getProyekId();
        if (!idProyek) return;

        const targetIds = new Set(list.map(function (it) { return Number(it.id || it.id_kavling); }));
        const queryTerm = list.length === 1 ? String(list[0].no_kavling || '') : '';

        $.ajax({
            url: rkApiUrl('api/kavling-request/kavling-list'),
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                search: queryTerm,
                limit: 100
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            const rows = Array.isArray(res && res.results) ? res.results : [];
            let updatedAny = false;
            rows.forEach(function (row) {
                const rowId = Number(row && row.id ? row.id : 0);
                if (rowId > 0 && targetIds.has(rowId)) {
                    rkState.kavlingMap[String(rowId)] = Object.assign({}, rkState.kavlingMap[String(rowId)] || {}, row);
                    updatedAny = true;
                }
            });
            if (updatedAny) {
                const currentVals = ($('#rk-ubah-kavlings').val() || []).map(String);
                const updatedItems = currentVals.map(function (idStr) {
                    return rkState.kavlingMap[idStr];
                }).filter(Boolean);
                if (updatedItems.length > 0) {
                    setUbahSelectedKavlings(updatedItems);
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDERING TABEL PLANNING, MKDT PENDING & RIWAYAT + PEMBATALAN
    // ─────────────────────────────────────────────────────────────────────────

    const cancelContextUiMap = {
        tambah: {
            batchBar: '#rk-mkdt-tambah-batch-bar',
            selectedCount: '#rk-mkdt-tambah-selected-count',
            checkAll: '#rk-mkdt-tambah-check-all',
            tbody: '#tbody-rk-mkdt-tambah'
        },
        ubah: {
            batchBar: '#rk-mkdt-ubah-batch-bar',
            selectedCount: '#rk-mkdt-ubah-selected-count',
            checkAll: '#rk-mkdt-ubah-check-all',
            tbody: '#tbody-rk-mkdt-ubah'
        },
        riwayat: {
            batchBar: '#rk-riwayat-batch-bar',
            selectedCount: '#rk-riwayat-selected-count',
            checkAll: '#rk-riwayat-check-all',
            tbody: '#tbody-rk-riwayat'
        }
    };

    function isItemCancellable(item) {
        if (!item || !canCreate) return false;
        const status = String(item.status || '').toLowerCase();
        if (status !== 'pending') return false;
        return Boolean(item.can_cancel);
    }

    function syncCancelBatchUI(context) {
        const ui = cancelContextUiMap[context];
        const selectedSet = rkState.selectedCancelIds[context];
        const visibleIds = rkState.visibleCancellableIds[context] || [];
        if (!ui || !selectedSet) return;

        const selectedCount = selectedSet.size;
        const totalCancellable = visibleIds.length;

        $(ui.selectedCount).text(selectedCount);
        const $batchBar = $(ui.batchBar);
        if ($batchBar.length) {
            if (selectedCount > 0) {
                $batchBar.removeAttr('hidden');
            } else {
                $batchBar.attr('hidden', true);
            }
        }

        const $checkAll = $(ui.checkAll);
        if ($checkAll.length) {
            const allSelected = totalCancellable > 0 && selectedCount === totalCancellable;
            const partialSelected = selectedCount > 0 && selectedCount < totalCancellable;
            $checkAll.prop('disabled', totalCancellable === 0);
            $checkAll.prop('checked', allSelected);
            $checkAll.prop('indeterminate', partialSelected);
        }
    }

    function pruneStaleSelections(context, rows) {
        const selectedSet = rkState.selectedCancelIds[context];
        if (!selectedSet) return;

        const cancellableIds = [];
        const cancellableLookup = new Set();
        (Array.isArray(rows) ? rows : []).forEach(function (item) {
            if (isItemCancellable(item)) {
                const idNum = Number(item.id_request);
                if (idNum > 0 && !cancellableLookup.has(idNum)) {
                    cancellableLookup.add(idNum);
                    cancellableIds.push(idNum);
                }
            }
        });

        rkState.visibleCancellableIds[context] = cancellableIds;
        Array.from(selectedSet).forEach(function (idNum) {
            if (!cancellableLookup.has(Number(idNum))) {
                selectedSet.delete(idNum);
            }
        });

        syncCancelBatchUI(context);
    }

    function clearCancelSelection(context) {
        const selectedSet = rkState.selectedCancelIds[context];
        const ui = cancelContextUiMap[context];
        if (!selectedSet) return;
        selectedSet.clear();
        if (ui && ui.tbody) {
            $(ui.tbody).find('.rk-cancel-checkbox').prop('checked', false);
        }
        syncCancelBatchUI(context);
    }

    function clearCancelledIdsFromAllContexts(ids) {
        const idLookup = new Set((Array.isArray(ids) ? ids : []).map(Number));
        Object.keys(rkState.selectedCancelIds).forEach(function (ctx) {
            const set = rkState.selectedCancelIds[ctx];
            idLookup.forEach(function (idNum) {
                set.delete(idNum);
            });
            syncCancelBatchUI(ctx);
        });
    }

    function buildCancelSummaryText(ids) {
        const list = Array.isArray(ids) ? ids : [];
        if (list.length === 0) return '';
        if (list.length === 1) {
            const item = rkState.requestMap[String(list[0])];
            if (!item) {
                return 'Request #' + list[0] + ' akan dibatalkan.';
            }
            const jenisLabel = item.jenis_request === 'ubah_tipe' ? 'Ubah Kavling' : 'Tambah Kavling';
            const pengaju = item.pengaju_name || item.pengaju_username || 'Pengaju';
            return 'Req #' + item.id_request + ' (' + jenisLabel + ' · ' + pengaju + ') akan dibatalkan.';
        }
        const previewIds = list.slice(0, 5).map(function (id) { return '#' + id; }).join(', ');
        const suffix = list.length > 5 ? ', +' + (list.length - 5) + ' lainnya' : '';
        return list.length + ' request menunggu (' + previewIds + suffix + ') akan dibatalkan.';
    }

    function setCancelControlsDisabled(disabled) {
        $modal.find('.btn-rk-action-cancel, #btn-rk-mkdt-batch-cancel-tambah, #btn-rk-mkdt-batch-cancel-ubah, #btn-rk-riwayat-batch-cancel')
            .prop('disabled', Boolean(disabled));
    }

    function confirmAndCancelRequests(ids, summaryText) {
        if (!canCreate || rkState.isCancelling) return;

        const cleanIds = Array.from(new Set(
            (Array.isArray(ids) ? ids : [ids]).map(Number).filter(function (id) { return id > 0; })
        ));
        if (cleanIds.length === 0) {
            showToast('warning', 'Belum ada pilihan', 'Pilih minimal 1 request menunggu untuk dibatalkan.');
            return;
        }

        const isSingle = cleanIds.length === 1;
        const resolvedSummary = summaryText || buildCancelSummaryText(cleanIds);

        Swal.fire({
            title: isSingle ? 'Batalkan 1 Request?' : ('Batalkan ' + cleanIds.length + ' Request Terpilih?'),
            html: '<div class="small text-muted mb-50">' + escapeHtml(resolvedSummary) + '</div>'
                + '<div class="small text-danger font-weight-bold">Pengajuan yang dibatalkan tidak dapat diproses lagi oleh Planning.</div>',
            input: 'textarea',
            inputLabel: 'Alasan Pembatalan (Opsional)',
            inputPlaceholder: 'Tulis alasan pembatalan jika ada (opsional)...',
            inputAttributes: {
                'aria-label': 'Alasan Pembatalan (Opsional)',
                maxlength: '1000'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isSingle ? 'Ya, Batalkan Request' : ('Ya, Batalkan ' + cleanIds.length + ' Request'),
            cancelButtonText: 'Kembali'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            if (rkState.isCancelling) return;

            const idProyek = getProyekId();
            if (!idProyek) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Proyek aktif tidak ditemukan.' });
                return;
            }

            rkState.isCancelling = true;
            setCancelControlsDisabled(true);

            $.ajax({
                url: rkEndpoints.cancel,
                type: 'POST',
                data: {
                    [csrfName]: csrfHash,
                    id_proyek: idProyek,
                    id_requests: cleanIds,
                    alasan_batal: $.trim(result.value || '')
                },
                dataType: 'json'
            }).done(function (res) {
                updateCsrf(res && res.token);
                if (res && res.success) {
                    const resData = res.data && typeof res.data === 'object' ? res.data : {};
                    const cancelledIds = Array.isArray(resData.cancelled_ids) && resData.cancelled_ids.length > 0
                        ? resData.cancelled_ids
                        : cleanIds;
                    const skippedCount = Number(resData.skipped_count || 0);
                    const skippedItems = Array.isArray(resData.skipped) ? resData.skipped : [];
                    const successMsg = resData.message || res.messages || 'Request kavling berhasil dibatalkan.';

                    clearCancelledIdsFromAllContexts(cleanIds);
                    clearCancelledIdsFromAllContexts(cancelledIds);

                    if (resData.counts) {
                        updateBadgesAndCounters(resData.counts);
                    }

                    if (skippedCount > 0) {
                        const skipReasons = skippedItems.slice(0, 3).map(function (sk) {
                            return 'Req #' + Number(sk.id_request || 0) + ': ' + String(sk.reason || 'Dilewati');
                        }).join(' | ');
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembatalan Diproses',
                            text: successMsg + (skipReasons ? (' (' + skipReasons + ')') : '')
                        });
                    } else {
                        showToast('success', 'Request Dibatalkan', successMsg);
                    }

                    refreshAllVisibleLists();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Membatalkan',
                        text: (res && res.messages) || 'Gagal membatalkan request kavling.'
                    });
                }
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membatalkan',
                    text: extractErrorMessage(xhr, 'Terjadi kesalahan saat membatalkan request kavling.')
                });
            }).always(function () {
                rkState.isCancelling = false;
                setCancelControlsDisabled(false);
            });
        });
    }

    function renderStatusBadge(status) {
        const s = String(status || 'pending').toLowerCase();
        if (s === 'approved') {
            return '<span class="rk-status-pill is-approved"><i class="fas fa-check-circle"></i> Selesai</span>';
        }
        if (s === 'rejected') {
            return '<span class="rk-status-pill is-rejected"><i class="fas fa-times-circle"></i> Ditolak</span>';
        }
        if (s === 'cancelled') {
            return '<span class="rk-status-pill rk-status-badge is-cancelled"><i class="fas fa-ban"></i> Dibatalkan</span>';
        }
        return '<span class="rk-status-pill is-pending"><i class="fas fa-clock"></i> Menunggu</span>';
    }

    function renderJenisBadge(jenis) {
        if (jenis === 'ubah_tipe') {
            return '<span class="rk-jenis-pill is-ubah"><i class="fas fa-exchange-alt"></i> Ubah Kavling</span>';
        }
        return '<span class="rk-jenis-pill is-tambah"><i class="fas fa-plus-circle"></i> Tambah Kavling</span>';
    }

    function renderJalansCell(item) {
        const jalans = Array.isArray(item.jalans) ? item.jalans : [];
        if (jalans.length === 0) {
            const parts = [];
            if (item.nama_cluster) parts.push('Cluster: ' + escapeHtml(item.nama_cluster));
            if (item.nama_jalan) parts.push('Jalan: ' + escapeHtml(item.nama_jalan));
            if (parts.length > 0) {
                return '<div class="rk-item-chip"><div class="rk-item-chip-title">' + parts.join(' · ') + '</div></div>';
            }
            return '<span class="text-muted font-italic small">Manual / Ditulis di keterangan</span>';
        }

        return '<div class="rk-chip-stack">' + jalans.map(function (j) {
            return '<div class="rk-item-chip">'
                + '<div class="rk-item-chip-title">' + escapeHtml(j.nama_jalan || ('Jalan #' + j.id_jalan)) + '</div>'
                + (j.nama_cluster ? '<div class="rk-item-chip-sub">Cluster: ' + escapeHtml(j.nama_cluster) + '</div>' : '')
                + '</div>';
        }).join('') + '</div>';
    }

    function renderKavlingsCell(item) {
        const kavlings = Array.isArray(item.kavlings) ? item.kavlings : [];
        if (kavlings.length === 0) {
            if (item.existing_nama_jalan || item.existing_no_kavling) {
                return '<div class="rk-item-chip"><div class="rk-item-chip-title">'
                    + escapeHtml((item.existing_nama_jalan ? item.existing_nama_jalan + ' ' : '') + 'No. ' + (item.existing_no_kavling || item.id_kavling || '-'))
                    + '</div></div>';
            }
            return '<span class="text-muted small">-</span>';
        }

        const countBadgeHtml = kavlings.length > 1
            ? '<div class="rk-kavling-count-badge">' + kavlings.length + ' kavling</div>'
            : '';

        return countBadgeHtml + '<div class="rk-chip-stack">' + kavlings.map(function (k) {
            const kavTitle = (k.nama_jalan ? (k.nama_jalan + ' ') : '') + 'No. ' + (k.no_kavling || k.id_kavling);
            const tipeAwal = k.old_tipe_label || k.current_tipe_label || '-';
            const currentBerbeda = k.current_tipe_label && k.old_tipe_label && k.current_tipe_label !== k.old_tipe_label;

            let subHtml = '<div class="rk-item-chip-sub">Tipe Awal: <strong>' + escapeHtml(tipeAwal) + '</strong>';
            if (currentBerbeda) {
                subHtml += ' &rarr; Saat Ini: <strong>' + escapeHtml(k.current_tipe_label) + '</strong>';
            }
            subHtml += '</div>';

            let konHtml = '';
            if (k.has_active_konsumen) {
                const konLabel = (k.nama_konsumen || 'Konsumen Aktif') + (k.status_mkdt ? ' (' + k.status_mkdt + ')' : '');
                konHtml = '<span class="rk-konsumen-tag"><i class="fas fa-user"></i> ' + escapeHtml(konLabel) + '</span>';
            }

            return '<div class="rk-item-chip">'
                + '<div class="rk-item-chip-title">' + escapeHtml(kavTitle) + '</div>'
                + subHtml
                + konHtml
                + '</div>';
        }).join('') + '</div>';
    }

    function renderTipeSummaryCell(item, includeApplied) {
        const reqTipe = item.requested_tipe || null;
        const appTipe = item.applied_tipe || null;
        const reqLabel = reqTipe ? formatTipeLabel(reqTipe) : (item.tipe_display || item.tipe_manual || '');

        let html = '';
        if (reqLabel) {
            html += '<div class="mb-25"><span class="small text-muted d-block">Usulan:</span>'
                + '<span class="font-weight-bold text-dark">' + escapeHtml(reqLabel) + '</span></div>';
            if (reqTipe && reqTipe.id_tipe) {
                html += '<button type="button" class="btn btn-outline-primary btn-sm btn-rk-view-tipe-spec mb-25" data-req-id="' + Number(item.id_request) + '" data-tipe-role="requested">'
                    + '<i class="fas fa-images mr-25"></i>Lihat 3D/Denah/Spek'
                    + '</button>';
            }
        } else {
            html += '<span class="text-muted font-italic small">Belum ditentukan / Tipe Baru</span>';
        }

        if (includeApplied && appTipe && appTipe.id_tipe) {
            html += '<div class="mt-50 pt-50 border-top">'
                + '<span class="small text-success font-weight-bold d-block">Diterapkan:</span>'
                + '<span class="font-weight-bold text-success">' + escapeHtml(formatTipeLabel(appTipe)) + '</span>'
                + '<div class="mt-25"><button type="button" class="btn btn-outline-success btn-sm btn-rk-view-tipe-spec" data-req-id="' + Number(item.id_request) + '" data-tipe-role="applied">'
                + '<i class="fas fa-images mr-25"></i>Lihat Tipe Diterapkan'
                + '</button></div>'
                + '</div>';
        }

        return html;
    }

    function renderPlanningRowActions(item, jenis) {
        const idReq = Number(item.id_request);
        const statusLower = String(item.status || 'pending').toLowerCase();
        if (statusLower === 'pending') {
            let btns = '<div class="rk-action-stack">';
            if (jenis === 'ubah_tipe') {
                btns += '<button type="button" class="btn btn-primary btn-sm btn-rk-action-apply" data-id="' + idReq + '">'
                    + '<i class="fas fa-edit mr-25"></i>Ubah Kavling</button>';
            }
            btns += '<button type="button" class="btn btn-success btn-sm btn-rk-action-approve" data-id="' + idReq + '">'
                + '<i class="fas fa-check mr-25"></i>Selesai</button>';
            btns += '<button type="button" class="btn btn-danger btn-sm btn-rk-action-reject" data-id="' + idReq + '">'
                + '<i class="fas fa-times mr-25"></i>Tolak</button>';
            if (isItemCancellable(item)) {
                btns += '<button type="button" class="btn btn-outline-danger btn-sm btn-rk-action-cancel" data-id="' + idReq + '">'
                    + '<i class="fas fa-ban mr-25"></i>Batalkan</button>';
            }
            btns += '</div>';
            return btns;
        }

        if (statusLower === 'cancelled') {
            const canceller = item.processed_by_name ? escapeHtml(item.processed_by_name) : '-';
            const cancelledAt = item.processed_at ? escapeHtml(formatDateTime(item.processed_at)) : '';
            const cancelReason = item.alasan_batal && String(item.alasan_batal).trim()
                ? escapeHtml(String(item.alasan_batal).trim())
                : '<span class="text-muted font-italic">Tanpa catatan pembatalan</span>';

            return '<div class="small text-left">'
                + '<div class="text-muted">Dibatalkan oleh: <strong>' + canceller + '</strong></div>'
                + (cancelledAt ? '<div class="rk-meta-time">' + cancelledAt + '</div>' : '')
                + '<div class="rk-note-box is-cancelled mt-25">' + cancelReason + '</div>'
                + '</div>';
        }

        const processor = item.processed_by_name ? escapeHtml(item.processed_by_name) : '-';
        const processedAt = item.processed_at ? escapeHtml(formatDateTime(item.processed_at)) : '';
        const noteText = item.catatan_planning || item.alasan_tolak || '';

        return '<div class="small text-left">'
            + '<div class="text-muted">Diproses: <strong>' + processor + '</strong></div>'
            + (processedAt ? '<div class="rk-meta-time">' + processedAt + '</div>' : '')
            + (noteText ? '<div class="rk-note-box mt-25">' + escapeHtml(noteText) + '</div>' : '')
            + '</div>';
    }

    function loadMkdtPendingList(jenis) {
        if (!canCreate || canProcess) return $.Deferred().resolve([]).promise();
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve([]).promise();

        const isUbah = jenis === 'ubah_tipe';
        const ctx = isUbah ? 'ubah' : 'tambah';
        const $loading = isUbah ? $('#rk-mkdt-ubah-loading') : $('#rk-mkdt-tambah-loading');
        const $wrap = isUbah ? $('#rk-mkdt-ubah-table-wrap') : $('#rk-mkdt-tambah-table-wrap');
        const $empty = isUbah ? $('#rk-mkdt-ubah-empty') : $('#rk-mkdt-tambah-empty');
        const $tbody = isUbah ? $('#tbody-rk-mkdt-ubah') : $('#tbody-rk-mkdt-tambah');

        if (!$tbody.length) return $.Deferred().resolve([]).promise();

        $loading.removeAttr('hidden');
        $wrap.attr('hidden', true);
        $empty.attr('hidden', true);

        return $.ajax({
            url: rkEndpoints.list,
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                jenis_request: jenis,
                status: 'pending'
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            $loading.attr('hidden', true);

            if (res && res.counts) {
                updateBadgesAndCounters(res.counts);
            }

            const rows = (res && res.success && Array.isArray(res.data)) ? res.data : [];
            cacheRequests(rows);
            pruneStaleSelections(ctx, rows);

            if (!res || !res.counts) {
                $(isUbah ? '#rk-mkdt-ubah-pending-count' : '#rk-mkdt-tambah-pending-count').text(rows.length);
            }

            if (rows.length === 0) {
                $tbody.empty();
                $wrap.attr('hidden', true);
                $empty.removeAttr('hidden');
                return;
            }

            const selectedSet = rkState.selectedCancelIds[ctx];
            const html = rows.map(function (item, idx) {
                const idReq = Number(item.id_request);
                const tanggal = escapeHtml(formatDateTime(item.created_at));
                const pengaju = escapeHtml(item.pengaju_name || item.pengaju_username || 'User');
                const detailCol = isUbah ? renderKavlingsCell(item) : renderJalansCell(item);
                const cancellable = isItemCancellable(item);
                const isChecked = cancellable && selectedSet.has(idReq);

                const checkCell = cancellable
                    ? '<input type="checkbox" class="rk-row-checkbox rk-cancel-checkbox"'
                        + ' data-context="' + ctx + '" value="' + idReq + '"'
                        + (isChecked ? ' checked' : '')
                        + ' aria-label="Pilih request #' + idReq + '">'
                    : '<span class="text-muted small" title="Hanya pengaju atau Admin yang dapat membatalkan">&mdash;</span>';

                const actionCell = cancellable
                    ? '<button type="button" class="btn btn-outline-danger btn-sm btn-rk-action-cancel"'
                        + ' data-id="' + idReq + '" data-context="' + ctx + '">'
                        + '<i class="fas fa-ban mr-25"></i>Batal</button>'
                    : '<span class="text-muted small">Pengaju lain</span>';

                return '<tr>'
                    + '<td class="text-center rk-check-col">' + checkCell + '</td>'
                    + '<td class="text-center font-weight-bold">' + (idx + 1) + '</td>'
                    + '<td>'
                    + '<div class="font-weight-bold">' + pengaju + '</div>'
                    + '<div class="rk-meta-time mt-25"><i class="far fa-calendar-alt mr-25"></i>' + tanggal + '</div>'
                    + '<div class="small text-muted">Req #' + idReq + '</div>'
                    + '</td>'
                    + '<td>' + detailCol + '</td>'
                    + '<td>' + renderTipeSummaryCell(item, false) + '</td>'
                    + '<td><div class="rk-note-box">' + escapeHtml(item.keterangan || '-') + '</div></td>'
                    + '<td class="text-center">' + actionCell + '</td>'
                    + '</tr>';
            }).join('');

            $tbody.html(html);
            $empty.attr('hidden', true);
            $wrap.removeAttr('hidden');
            syncCancelBatchUI(ctx);
        }).fail(function (xhr) {
            $loading.attr('hidden', true);
            $wrap.attr('hidden', true);
            $empty.removeAttr('hidden');
            pruneStaleSelections(ctx, []);
            showToast('error', 'Gagal memuat data', extractErrorMessage(xhr, 'Gagal memuat pengajuan menunggu.'));
        });
    }

    function loadPlanningList(jenis) {
        if (!canProcess) return $.Deferred().resolve([]).promise();
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve([]).promise();

        const isUbah = jenis === 'ubah_tipe';
        const statusFilter = isUbah ? rkState.ubahFilterStatus : rkState.tambahFilterStatus;
        const $loading = isUbah ? $('#rk-ubah-loading') : $('#rk-tambah-loading');
        const $wrap = isUbah ? $('#rk-ubah-table-wrap') : $('#rk-tambah-table-wrap');
        const $empty = isUbah ? $('#rk-ubah-empty') : $('#rk-tambah-empty');
        const $tbody = isUbah ? $('#tbody-rk-ubah') : $('#tbody-rk-tambah');

        $loading.removeAttr('hidden');
        $wrap.attr('hidden', true);
        $empty.attr('hidden', true);

        return $.ajax({
            url: rkEndpoints.list,
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                jenis_request: jenis,
                status: statusFilter
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            $loading.attr('hidden', true);

            if (res && res.counts) {
                updateBadgesAndCounters(res.counts);
            }

            const rows = (res && res.success && Array.isArray(res.data)) ? res.data : [];
            cacheRequests(rows);

            if (rows.length === 0) {
                $tbody.empty();
                $wrap.attr('hidden', true);
                $empty.removeAttr('hidden');
                return;
            }

            const html = rows.map(function (item, idx) {
                const tanggal = escapeHtml(formatDateTime(item.created_at));
                const pengaju = escapeHtml(item.pengaju_name || item.pengaju_username || 'User');
                const detailCol = isUbah ? renderKavlingsCell(item) : renderJalansCell(item);

                return '<tr>'
                    + '<td class="text-center font-weight-bold">' + (idx + 1) + '</td>'
                    + '<td>'
                    + '<div class="font-weight-bold">' + pengaju + '</div>'
                    + '<div class="rk-meta-time mt-25"><i class="far fa-calendar-alt mr-25"></i>' + tanggal + '</div>'
                    + '<div class="small text-muted">Req #' + Number(item.id_request) + '</div>'
                    + '</td>'
                    + '<td>' + detailCol + '</td>'
                    + '<td>' + renderTipeSummaryCell(item, false) + '</td>'
                    + '<td><div class="rk-note-box">' + escapeHtml(item.keterangan || '-') + '</div></td>'
                    + '<td class="text-center">' + renderStatusBadge(item.status) + '</td>'
                    + '<td class="text-center">' + renderPlanningRowActions(item, jenis) + '</td>'
                    + '</tr>';
            }).join('');

            $tbody.html(html);
            $empty.attr('hidden', true);
            $wrap.removeAttr('hidden');
        }).fail(function (xhr) {
            $loading.attr('hidden', true);
            $wrap.attr('hidden', true);
            $empty.removeAttr('hidden');
            showToast('error', 'Gagal memuat data', extractErrorMessage(xhr, 'Gagal memuat daftar request.'));
        });
    }

    function loadRiwayatList() {
        const idProyek = getProyekId();
        if (!idProyek) return $.Deferred().resolve([]).promise();

        const $loading = $('#rk-riwayat-loading');
        const $wrap = $('#rk-riwayat-table-wrap');
        const $empty = $('#rk-riwayat-empty');
        const $tbody = $('#tbody-rk-riwayat');

        $loading.removeAttr('hidden');
        $wrap.attr('hidden', true);
        $empty.attr('hidden', true);

        return $.ajax({
            url: rkEndpoints.list,
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                jenis_request: rkState.riwayatFilterJenis,
                status: rkState.riwayatFilterStatus
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            $loading.attr('hidden', true);

            if (res && res.counts) {
                updateBadgesAndCounters(res.counts);
            }

            const rows = (res && res.success && Array.isArray(res.data)) ? res.data : [];
            cacheRequests(rows);
            if (canCreate) {
                pruneStaleSelections('riwayat', rows);
            }

            if (rows.length === 0) {
                $tbody.empty();
                $wrap.attr('hidden', true);
                $empty.removeAttr('hidden');
                return;
            }

            const selectedSet = rkState.selectedCancelIds.riwayat;
            const html = rows.map(function (item, idx) {
                const idReq = Number(item.id_request);
                const tanggal = escapeHtml(formatDateTime(item.created_at));
                const pengaju = escapeHtml(item.pengaju_name || item.pengaju_username || 'User');
                const detailCol = item.jenis_request === 'ubah_tipe'
                    ? renderKavlingsCell(item)
                    : renderJalansCell(item);

                const statusLower = String(item.status || 'pending').toLowerCase();
                let processInfoHtml = '<span class="text-muted small">-</span>';

                if (statusLower === 'cancelled') {
                    const canceller = escapeHtml(item.processed_by_name || item.pengaju_name || item.pengaju_username || '-');
                    const cancelledAt = item.processed_at
                        ? ' &bull; <span class="rk-meta-time">' + escapeHtml(formatDateTime(item.processed_at)) + '</span>'
                        : '';
                    const cancelMeta = '<div class="small text-muted mb-25">Dibatalkan oleh <strong>' + canceller + '</strong>' + cancelledAt + '</div>';
                    const alasanBatal = item.alasan_batal && String(item.alasan_batal).trim()
                        ? escapeHtml(String(item.alasan_batal).trim())
                        : '<span class="text-muted font-italic">Tanpa catatan pembatalan</span>';
                    processInfoHtml = cancelMeta + '<div class="rk-note-box is-cancelled">' + alasanBatal + '</div>';
                } else {
                    const notes = item.catatan_planning || item.alasan_tolak || '';
                    const noteClass = statusLower === 'rejected'
                        ? ' is-planning-rejected'
                        : (statusLower === 'approved' ? ' is-planning-approved' : '');
                    const processedMeta = item.processed_by_name
                        ? '<div class="small text-muted mt-25">Oleh: <strong>' + escapeHtml(item.processed_by_name) + '</strong>'
                            + (item.processed_at ? ' · <span class="rk-meta-time">' + escapeHtml(formatDateTime(item.processed_at)) + '</span>' : '')
                            + '</div>'
                        : '';

                    if (notes) {
                        processInfoHtml = '<div class="rk-note-box' + noteClass + '">' + escapeHtml(notes) + '</div>' + processedMeta;
                    } else if (processedMeta) {
                        processInfoHtml = processedMeta;
                    }
                }

                const cancellable = isItemCancellable(item);
                const isChecked = cancellable && selectedSet.has(idReq);

                const checkColHtml = canCreate
                    ? ('<td class="text-center rk-check-col">'
                        + (cancellable
                            ? '<input type="checkbox" class="rk-row-checkbox rk-cancel-checkbox"'
                                + ' data-context="riwayat" value="' + idReq + '"'
                                + (isChecked ? ' checked' : '')
                                + ' aria-label="Pilih request #' + idReq + '">'
                            : '')
                        + '</td>')
                    : '';

                const actionColHtml = canCreate
                    ? ('<td class="text-center">'
                        + (cancellable
                            ? '<button type="button" class="btn btn-outline-danger btn-sm btn-rk-action-cancel"'
                                + ' data-id="' + idReq + '" data-context="riwayat">'
                                + '<i class="fas fa-ban mr-25"></i>Batal</button>'
                            : '<span class="text-muted small">-</span>')
                        + '</td>')
                    : '';

                return '<tr>'
                    + checkColHtml
                    + '<td class="text-center font-weight-bold">' + (idx + 1) + '</td>'
                    + '<td>'
                    + '<div class="font-weight-bold">' + pengaju + '</div>'
                    + '<div class="rk-meta-time mt-25"><i class="far fa-calendar-alt mr-25"></i>' + tanggal + '</div>'
                    + '<div class="small text-muted">Req #' + idReq + '</div>'
                    + '</td>'
                    + '<td class="text-center">' + renderJenisBadge(item.jenis_request) + '</td>'
                    + '<td>' + detailCol + '</td>'
                    + '<td>' + renderTipeSummaryCell(item, true) + '</td>'
                    + '<td><div class="rk-note-box">' + escapeHtml(item.keterangan || '-') + '</div></td>'
                    + '<td class="text-center">' + renderStatusBadge(item.status) + '</td>'
                    + '<td>' + processInfoHtml + '</td>'
                    + actionColHtml
                    + '</tr>';
            }).join('');

            $tbody.html(html);
            $empty.attr('hidden', true);
            $wrap.removeAttr('hidden');
            if (canCreate) {
                syncCancelBatchUI('riwayat');
            }
        }).fail(function (xhr) {
            $loading.attr('hidden', true);
            $wrap.attr('hidden', true);
            $empty.removeAttr('hidden');
            if (canCreate) {
                pruneStaleSelections('riwayat', []);
            }
            showToast('error', 'Gagal memuat riwayat', extractErrorMessage(xhr, 'Gagal memuat riwayat request.'));
        });
    }

    function loadTambahTab() {
        if (canProcess) {
            return loadPlanningList('tambah_baru');
        }
        if (canCreate) {
            return loadMkdtPendingList('tambah_baru');
        }
        return $.Deferred().resolve([]).promise();
    }

    function loadUbahTab() {
        if (canProcess) {
            return loadPlanningList('ubah_tipe');
        }
        if (canCreate) {
            return loadMkdtPendingList('ubah_tipe');
        }
        return $.Deferred().resolve([]).promise();
    }

    function loadRiwayatTab() {
        return loadRiwayatList();
    }

    function refreshAllVisibleLists() {
        loadTambahTab();
        loadUbahTab();
        loadRiwayatTab();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM SUBMISSIONS (MKDT / ADMIN)
    // ─────────────────────────────────────────────────────────────────────────

    function resetFormTambah() {
        const $form = $('#form-rk-tambah');
        if (!$form.length) return;
        $form[0].reset();
        $('#rk-tambah-jalans').val(null).trigger('change');
        $('#rk-tambah-id_tipe').val(null).trigger('change');
        $('#rk-tambah-tipe-preview').attr('hidden', true).empty();
    }

    function resetFormUbah() {
        const $form = $('#form-rk-ubah');
        if (!$form.length) return;
        $form[0].reset();
        $('#rk-ubah-kavlings').val(null).trigger('change');
        $('#rk-ubah-id_tipe').val(null).trigger('change');
        $('#rk-ubah-tipe-preview').attr('hidden', true).empty();
        $('#rk-ubah-selected-info').attr('hidden', true).empty();
    }

    if (canCreate) {
        $('#btn-rk-reset-tambah').on('click', resetFormTambah);
        $('#btn-rk-reset-ubah').on('click', resetFormUbah);

        $('#form-rk-tambah').on('submit', function (e) {
            e.preventDefault();
            if (rkState.isSubmittingTambah) return;

            const idProyek = getProyekId();
            const jalanIds = ($('#rk-tambah-jalans').val() || []).map(Number).filter(function (id) { return id > 0; });
            const idTipe = parseInt($('#rk-tambah-id_tipe').val() || '0', 10);
            const keterangan = $.trim($('#rk-tambah-keterangan').val() || '');

            if (!idProyek) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Proyek aktif tidak ditemukan.' });
                return;
            }
            if (!keterangan) {
                Swal.fire({ icon: 'warning', title: 'Keterangan Wajib Diisi', text: 'Silakan isi keterangan detail pengajuan tambah kavling.' })
                    .then(function () { $('#rk-tambah-keterangan').focus(); });
                return;
            }

            const payload = {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                jenis_request: 'tambah_baru',
                keterangan: keterangan
            };
            if (jalanIds.length > 0) {
                payload.id_jalans = jalanIds;
            }
            if (idTipe > 0) {
                payload.id_tipe = idTipe;
            }

            const $btn = $('#btn-rk-submit-tambah');
            rkState.isSubmittingTambah = true;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Mengirim...');

            $.ajax({
                url: rkApiUrl('api/kavling-request/submit'),
                type: 'POST',
                data: payload,
                dataType: 'json'
            }).done(function (res) {
                updateCsrf(res && res.token);
                if (res && res.success) {
                    resetFormTambah();
                    refreshAllVisibleLists();
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Terkirim',
                        text: (res.data && res.data.message) || res.messages || 'Request tambah kavling berhasil dikirim ke tim Planning.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: (res && res.messages) || 'Gagal mengirim request.'
                    });
                }
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengirim Request',
                    text: extractErrorMessage(xhr, 'Terjadi kesalahan pada server.')
                });
            }).always(function () {
                rkState.isSubmittingTambah = false;
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-25"></i>Kirim Request Tambah Kavling');
            });
        });

        $('#form-rk-ubah').on('submit', function (e) {
            e.preventDefault();
            if (rkState.isSubmittingUbah) return;

            const idProyek = getProyekId();
            const kavlingIds = ($('#rk-ubah-kavlings').val() || []).map(Number).filter(function (id) { return id > 0; });
            const idTipe = parseInt($('#rk-ubah-id_tipe').val() || '0', 10);
            const keterangan = $.trim($('#rk-ubah-keterangan').val() || '');

            if (!idProyek) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Proyek aktif tidak ditemukan.' });
                return;
            }
            if (kavlingIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Kavling Belum Dipilih', text: 'Pilih minimal 1 kavling melalui dropdown atau kanvas siteplan.' });
                return;
            }
            if (!keterangan) {
                Swal.fire({ icon: 'warning', title: 'Keterangan Wajib Diisi', text: 'Silakan isi keterangan alasan perubahan tipe kavling.' })
                    .then(function () { $('#rk-ubah-keterangan').focus(); });
                return;
            }

            const payload = {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                jenis_request: 'ubah_tipe',
                id_kavlings: kavlingIds,
                keterangan: keterangan
            };
            if (idTipe > 0) {
                payload.id_tipe = idTipe;
            }

            const $btn = $('#btn-rk-submit-ubah');
            rkState.isSubmittingUbah = true;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Mengirim...');

            $.ajax({
                url: rkApiUrl('api/kavling-request/submit'),
                type: 'POST',
                data: payload,
                dataType: 'json'
            }).done(function (res) {
                updateCsrf(res && res.token);
                if (res && res.success) {
                    resetFormUbah();
                    refreshAllVisibleLists();
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Terkirim',
                        text: (res.data && res.data.message) || res.messages || 'Request ubah tipe kavling berhasil dikirim ke tim Planning.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: (res && res.messages) || 'Gagal mengirim request.'
                    });
                }
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengirim Request',
                    text: extractErrorMessage(xhr, 'Terjadi kesalahan pada server.')
                });
            }).always(function () {
                rkState.isSubmittingUbah = false;
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-25"></i>Kirim Request Ubah Kavling');
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PLANNING ACTIONS (SELESAI, TOLAK, & SUB-MODAL UBAH KAVLING)
    // ─────────────────────────────────────────────────────────────────────────

    function submitStatusUpdate(idRequest, newStatus, catatan) {
        return $.ajax({
            url: rkApiUrl('api/kavling-request/update-status'),
            type: 'POST',
            data: {
                [csrfName]: csrfHash,
                id_request: idRequest,
                status: newStatus,
                catatan_planning: catatan || ''
            },
            dataType: 'json'
        }).done(function (res) {
            updateCsrf(res && res.token);
            if (res && res.success) {
                showToast('success', 'Berhasil', (res.data && res.data.message) || res.messages || 'Status request diperbarui.');
                refreshAllVisibleLists();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.messages) || 'Gagal mengubah status.' });
            }
        }).fail(function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: extractErrorMessage(xhr, 'Gagal memperbarui status request.')
            });
        });
    }

    function openApplyUbahKavlingModal(idRequest) {
        if (!canProcess || !$modalApply.length) return;
        const item = rkState.requestMap[String(idRequest)];
        if (!item) {
            showToast('warning', 'Data tidak ditemukan', 'Muat ulang tabel terlebih dahulu.');
            return;
        }

        $('#rk-apply-id_request').val(String(item.id_request));
        $('#rk-apply-catatan').val('');

        const metaText = 'Pengaju: <strong>' + escapeHtml(item.pengaju_name || item.pengaju_username || 'User') + '</strong>'
            + ' · Tanggal: <span class="rk-meta-time">' + escapeHtml(formatDateTime(item.created_at)) + '</span>'
            + (item.keterangan ? '<div class="mt-25 text-dark">Keterangan MKDT: <em>"' + escapeHtml(item.keterangan) + '"</em></div>' : '');
        $('#rk-apply-request-meta').html(metaText);

        const kavlings = Array.isArray(item.kavlings) ? item.kavlings : [];
        const activeKonsumens = [];
        const kavHtml = kavlings.map(function (k) {
            const title = (k.nama_jalan ? (k.nama_jalan + ' ') : '') + 'No. ' + (k.no_kavling || k.id_kavling);
            const tipeAwal = k.old_tipe_label || k.current_tipe_label || '-';
            let konTag = '';
            if (k.has_active_konsumen) {
                const konDetail = (k.nama_konsumen || 'Konsumen Aktif') + (k.status_mkdt ? ' (' + k.status_mkdt + ')' : '');
                activeKonsumens.push(title + ' — ' + konDetail);
                konTag = '<span class="rk-konsumen-tag"><i class="fas fa-user"></i> ' + escapeHtml(konDetail) + '</span>';
            }
            return '<div class="rk-item-chip">'
                + '<div class="rk-item-chip-title">' + escapeHtml(title) + '</div>'
                + '<div class="rk-item-chip-sub">Tipe Awal: <strong>' + escapeHtml(tipeAwal) + '</strong></div>'
                + konTag
                + '</div>';
        }).join('');
        const $kavList = $('#rk-apply-kavling-list');
        $kavList.prev('.rk-kavling-count-badge').remove();
        if (kavlings.length > 1) {
            $('<div class="rk-kavling-count-badge"></div>')
                .text(kavlings.length + ' kavling')
                .insertBefore($kavList);
        }
        $kavList.html(kavHtml || '<div class="text-muted small">Tidak ada data kavling.</div>');

        const $warn = $('#rk-apply-konsumen-warning');
        if (activeKonsumens.length > 0) {
            $warn.html(
                '<div class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-25"></i>Peringatan Kavling Berkonsumen Aktif</div>'
                + '<div class="mt-25">Mengubah tipe hanya memperbarui master <code>kavling.id_tipe</code> dan <strong>tidak</strong> mengubah Harga Akhir atau transaksi konsumen yang berjalan:</div>'
                + '<ul class="rk-konsumen-alert-list">' + activeKonsumens.map(function (t) { return '<li>' + escapeHtml(t) + '</li>'; }).join('') + '</ul>'
            ).removeAttr('hidden');
        } else {
            $warn.attr('hidden', true).empty();
        }

        initApplySelect2Control();

        const reqTipeId = (item.requested_tipe && item.requested_tipe.id_tipe)
            ? String(item.requested_tipe.id_tipe)
            : (item.id_tipe ? String(item.id_tipe) : '');

        const $hint = $('#rk-apply-requested-hint');
        const reqText = item.requested_tipe
            ? formatTipeLabel(item.requested_tipe)
            : (item.tipe_display || item.tipe_manual || '');
        if (reqText) {
            $hint.text('Tipe usulan MKDT: ' + reqText).removeAttr('hidden');
        } else {
            $hint.attr('hidden', true).text('');
        }

        loadActiveTipes(false, reqTipeId).always(function () {
            renderTipePreviewCard($('#rk-apply-tipe-preview'), $('#rk-apply-id_tipe').val());
        });

        $modalApply.modal('show');
    }

    if (canProcess) {
        $('#rk-tambah-filter-pills').on('click', '.rk-filter-pill', function () {
            const status = String($(this).data('status') || 'pending');
            rkState.tambahFilterStatus = status;
            $('#rk-tambah-filter-pills .rk-filter-pill').removeClass('is-active');
            $(this).addClass('is-active');
            loadPlanningList('tambah_baru');
        });

        $('#rk-ubah-filter-pills').on('click', '.rk-filter-pill', function () {
            const status = String($(this).data('status') || 'pending');
            rkState.ubahFilterStatus = status;
            $('#rk-ubah-filter-pills .rk-filter-pill').removeClass('is-active');
            $(this).addClass('is-active');
            loadPlanningList('ubah_tipe');
        });

        $('#btn-rk-refresh-tambah').on('click', function () {
            loadPlanningList('tambah_baru');
        });

        $('#btn-rk-refresh-ubah').on('click', function () {
            loadPlanningList('ubah_tipe');
        });

        $modal.on('click', '.btn-rk-action-approve', function () {
            const idReq = Number($(this).data('id') || 0);
            if (!idReq) return;

            Swal.fire({
                title: 'Tandai Request #' + idReq + ' Selesai?',
                text: 'Pengajuan ini akan ditandai sebagai Selesai (Disetujui).',
                input: 'textarea',
                inputLabel: 'Catatan Planning (Opsional)',
                inputPlaceholder: 'Tulis catatan tindak lanjut untuk pengaju jika ada...',
                inputAttributes: { 'aria-label': 'Catatan Planning (Opsional)' },
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2a9d8f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Selesai',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    submitStatusUpdate(idReq, 'approved', result.value || '');
                }
            });
        });

        $modal.on('click', '.btn-rk-action-reject', function () {
            const idReq = Number($(this).data('id') || 0);
            if (!idReq) return;

            Swal.fire({
                title: 'Tolak Request #' + idReq + '?',
                text: 'Pengajuan ini akan ditandai sebagai Ditolak.',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan (Wajib Diisi)',
                inputPlaceholder: 'Jelaskan alasan penolakan agar pengaju mengetahuinya...',
                inputAttributes: { 'aria-label': 'Alasan Penolakan (Wajib Diisi)' },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e76f51',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
                inputValidator: function (value) {
                    if (!value || !String(value).trim()) {
                        return 'Catatan / alasan penolakan wajib diisi!';
                    }
                    return null;
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    submitStatusUpdate(idReq, 'rejected', String(result.value || '').trim());
                }
            });
        });

        $modal.on('click', '.btn-rk-action-apply', function () {
            const idReq = Number($(this).data('id') || 0);
            if (!idReq) return;
            openApplyUbahKavlingModal(idReq);
        });

        $('#btn-rk-refresh-tipes').on('click', function () {
            if (rkState.isRefreshingTipes) return;
            const $btn = $(this);
            const currentVal = String($('#rk-apply-id_tipe').val() || '');
            rkState.isRefreshingTipes = true;
            $btn.prop('disabled', true).html('<i class="fas fa-sync-alt fa-spin mr-25"></i> Memuat...');

            loadActiveTipes(true, currentVal).done(function () {
                showToast('success', 'Daftar Tipe Diperbarui', 'Daftar tipe aktif terbaru sudah dimuat.');
            }).always(function () {
                rkState.isRefreshingTipes = false;
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-25"></i> Refresh Tipe');
            });
        });

        $('#form-rk-apply-ubah').on('submit', function (e) {
            e.preventDefault();
            if (rkState.isSubmittingApply) return;

            const idRequest = parseInt($('#rk-apply-id_request').val() || '0', 10);
            const idTipe = parseInt($('#rk-apply-id_tipe').val() || '0', 10);
            const catatan = $.trim($('#rk-apply-catatan').val() || '');

            if (!idRequest) return;
            if (!idTipe || idTipe <= 0) {
                Swal.fire({ icon: 'warning', title: 'Tipe Belum Dipilih', text: 'Silakan pilih tipe rumah baru yang akan diterapkan.' });
                return;
            }

            const $btn = $('#btn-rk-submit-apply');
            rkState.isSubmittingApply = true;
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Menyimpan...');

            $.ajax({
                url: rkApiUrl('api/kavling-request/apply-ubah-kavling'),
                type: 'POST',
                data: {
                    [csrfName]: csrfHash,
                    id_request: idRequest,
                    id_tipe: idTipe,
                    catatan_planning: catatan
                },
                dataType: 'json'
            }).done(function (res) {
                updateCsrf(res && res.token);
                if (res && res.success) {
                    $modalApply.modal('hide');
                    refreshAllVisibleLists();
                    if (typeof load_kavling === 'function') {
                        load_kavling();
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipe Kavling Diperbarui',
                        text: (res.data && res.data.message) || res.messages || 'Tipe kavling berhasil diperbarui.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: (res && res.messages) || 'Gagal menerapkan perubahan tipe kavling.'
                    });
                }
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menerapkan Perubahan',
                    text: extractErrorMessage(xhr, 'Terjadi kesalahan saat memperbarui tipe kavling.')
                });
            }).always(function () {
                rkState.isSubmittingApply = false;
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-25"></i>Simpan &amp; Ubah Tipe Kavling');
            });
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TAB RIWAYAT, PEMBATALAN (SATUAN & BATCH) & PREVIEW TRIGGERS
    // ─────────────────────────────────────────────────────────────────────────

    if (canCreate) {
        $('#btn-rk-mkdt-refresh-tambah').on('click', function () {
            loadMkdtPendingList('tambah_baru');
        });

        $('#btn-rk-mkdt-refresh-ubah').on('click', function () {
            loadMkdtPendingList('ubah_tipe');
        });

        $modal.on('change', '.rk-cancel-checkbox', function () {
            const ctx = String($(this).attr('data-context') || '');
            const idReq = Number($(this).val() || 0);
            const selectedSet = rkState.selectedCancelIds[ctx];
            if (!selectedSet || idReq <= 0) return;

            if (this.checked) {
                selectedSet.add(idReq);
            } else {
                selectedSet.delete(idReq);
            }
            syncCancelBatchUI(ctx);
        });

        $('#rk-mkdt-tambah-check-all, #rk-mkdt-ubah-check-all, #rk-riwayat-check-all').on('change', function () {
            const elemId = String(this.id || '');
            let ctx = 'riwayat';
            if (elemId === 'rk-mkdt-tambah-check-all') {
                ctx = 'tambah';
            } else if (elemId === 'rk-mkdt-ubah-check-all') {
                ctx = 'ubah';
            }

            const selectedSet = rkState.selectedCancelIds[ctx];
            const visibleIds = rkState.visibleCancellableIds[ctx] || [];
            const ui = cancelContextUiMap[ctx];
            if (!selectedSet || !ui) return;

            const shouldCheck = Boolean(this.checked);
            selectedSet.clear();
            if (shouldCheck) {
                visibleIds.forEach(function (idNum) {
                    selectedSet.add(Number(idNum));
                });
            }

            $(ui.tbody).find('.rk-cancel-checkbox').prop('checked', shouldCheck);
            syncCancelBatchUI(ctx);
        });

        $('#btn-rk-mkdt-clear-tambah').on('click', function () {
            clearCancelSelection('tambah');
        });

        $('#btn-rk-mkdt-clear-ubah').on('click', function () {
            clearCancelSelection('ubah');
        });

        $('#btn-rk-riwayat-clear-select').on('click', function () {
            clearCancelSelection('riwayat');
        });

        $('#btn-rk-mkdt-batch-cancel-tambah').on('click', function () {
            const ids = Array.from(rkState.selectedCancelIds.tambah);
            confirmAndCancelRequests(ids, buildCancelSummaryText(ids));
        });

        $('#btn-rk-mkdt-batch-cancel-ubah').on('click', function () {
            const ids = Array.from(rkState.selectedCancelIds.ubah);
            confirmAndCancelRequests(ids, buildCancelSummaryText(ids));
        });

        $('#btn-rk-riwayat-batch-cancel').on('click', function () {
            const ids = Array.from(rkState.selectedCancelIds.riwayat);
            confirmAndCancelRequests(ids, buildCancelSummaryText(ids));
        });

        $modal.on('click', '.btn-rk-action-cancel', function () {
            const idReq = Number($(this).attr('data-id') || $(this).data('id') || 0);
            if (!idReq) return;
            confirmAndCancelRequests([idReq], buildCancelSummaryText([idReq]));
        });
    }

    $('#rk-riwayat-status-pills').on('click', '.rk-filter-pill', function () {
        const status = String($(this).data('status') || 'all');
        rkState.riwayatFilterStatus = status;
        $('#rk-riwayat-status-pills .rk-filter-pill').removeClass('is-active');
        $(this).addClass('is-active');
        loadRiwayatList();
    });

    $('#rk-riwayat-jenis-filter').on('change', function () {
        rkState.riwayatFilterJenis = String($(this).val() || 'all');
        loadRiwayatList();
    });

    $('#btn-rk-refresh-riwayat').on('click', function () {
        loadRiwayatList();
    });

    $(document).on('click', '#modal-request-kavling .rk-thumb-zoom-trigger, #modal-apply-ubah-kavling .rk-thumb-zoom-trigger, #modal-rk-tipe-preview .rk-thumb-zoom-trigger', function () {
        const url = $(this).attr('data-img-url') || '';
        const title = $(this).attr('data-img-title') || 'Preview Gambar Tipe';
        openLightboxModal(url, title);
    });

    $(document).on('click', '#modal-request-kavling .btn-rk-view-tipe-spec', function () {
        const reqId = String($(this).attr('data-req-id') || '');
        const role = String($(this).attr('data-tipe-role') || 'requested');
        const item = rkState.requestMap[reqId];
        if (!item) return;
        const tipe = role === 'applied' ? item.applied_tipe : item.requested_tipe;
        const label = role === 'applied' ? 'Tipe Diterapkan' : 'Tipe Rumah Usulan';
        if (tipe) {
            openTipePreviewModal(tipe, label);
        }
    });

    // Nested Bootstrap 4 modal backdrop & body scroll fix
    $(document).on('show.bs.modal', '#modal-apply-ubah-kavling, #modal-rk-tipe-preview, #modal-rk-lightbox', function () {
        const zIndex = parseInt($(this).css('z-index'), 10) || 1060;
        setTimeout(function () {
            $('.modal-backdrop').not('.rk-stacked-backdrop').last()
                .css('z-index', zIndex - 1)
                .addClass('rk-stacked-backdrop');
        }, 0);
    });

    $(document).on('hidden.bs.modal', '.rk-nested-modal', function () {
        if ($('.modal.show').length > 0) {
            $('body').addClass('modal-open');
        }
    });

    // ─────────────────────────────────────────────────────────────────────────
    // CANVAS PICK MODE (PILIH DARI KANVAS SITEPLAN)
    // ─────────────────────────────────────────────────────────────────────────

    function mountPickerBar() {
        if (!$pickerBar.length) return;
        const $canvasCol = $('.siteplan-canvas-col').first();
        if ($canvasCol.length) {
            if (!$pickerBar.parent().is($canvasCol)) {
                $canvasCol.prepend($pickerBar);
            }
            $pickerBar.removeClass('is-fixed-fallback');
        } else {
            $pickerBar.addClass('is-fixed-fallback');
        }
    }

    function clearCanvasHighlights() {
        if (rkState.highlightGroup) {
            try {
                rkState.highlightGroup.destroy();
            } catch (e) {}
            rkState.highlightGroup = null;
            if (typeof masked !== 'undefined' && masked && typeof masked.batchDraw === 'function') {
                masked.batchDraw();
            }
        }
    }

    window.renderRequestKavlingCanvasHighlights = function () {
        clearCanvasHighlights();

        if (!rkState.canvasPickActive || !Array.isArray(rkState.canvasPickedItems) || rkState.canvasPickedItems.length === 0) {
            return;
        }
        if (typeof Konva === 'undefined' || typeof masked === 'undefined' || !masked) {
            return;
        }

        const group = new Konva.Group({
            name: 'rk-canvas-pick-highlights',
            listening: false
        });

        let hasAny = false;
        rkState.canvasPickedItems.forEach(function (item) {
            let points = (item && Array.isArray(item.points) && item.points.length >= 6) ? item.points : null;
            if (!points && typeof findSiteplanLocationNode === 'function' && item && item.id_kavling) {
                const node = findSiteplanLocationNode('kavling', item.id_kavling);
                if (node) {
                    points = typeof node.points === 'function'
                        ? node.points()
                        : (typeof node.getAttr === 'function' ? node.getAttr('points') : null);
                }
            }
            if (!points || points.length < 6) return;

            const outer = new Konva.Line({
                points: points,
                stroke: '#ffffff',
                strokeWidth: 6,
                closed: true,
                listening: false,
                strokeScaleEnabled: false,
                perfectDrawEnabled: false
            });
            const inner = new Konva.Line({
                points: points,
                stroke: '#2057a3',
                strokeWidth: 3,
                fill: 'rgba(32, 87, 163, 0.28)',
                dash: [6, 3],
                closed: true,
                listening: false,
                strokeScaleEnabled: false,
                perfectDrawEnabled: false
            });
            group.add(outer, inner);
            hasAny = true;
        });

        if (!hasAny) {
            group.destroy();
            return;
        }

        masked.add(group);
        group.moveToTop();
        masked.batchDraw();
        rkState.highlightGroup = group;
    };

    function renderPickerBar() {
        const list = Array.isArray(rkState.canvasPickedItems) ? rkState.canvasPickedItems : [];
        const count = list.length;

        $('#rk-picker-count').text(count + ' dipilih');
        $('#rk-picker-clear-btn').prop('disabled', count === 0);

        const $list = $('#rk-picker-chip-list');
        if (!$list.length) return;

        if (count === 0) {
            $list.html('<div class="rk-picker-empty">Belum ada kavling dipilih. Klik kavling pada kanvas.</div>');
            return;
        }

        const html = list.map(function (item) {
            const label = (item.nama_jalan ? (item.nama_jalan + ' ') : '') + 'No. ' + (item.no_kavling || item.id_kavling);
            const subParts = [];
            if (item.current_tipe_label) subParts.push('Tipe: ' + item.current_tipe_label);
            if (item.has_active_konsumen) {
                subParts.push(item.nama_konsumen
                    ? ('Konsumen: ' + item.nama_konsumen + (item.status_mkdt ? ' - ' + item.status_mkdt : ''))
                    : ('Konsumen Aktif' + (item.status_mkdt ? ' - ' + item.status_mkdt : '')));
            }

            return '<div class="rk-picker-chip" data-kavling-id="' + Number(item.id_kavling) + '">'
                + '<div class="rk-picker-chip-info">'
                + '<div class="rk-picker-chip-label">' + escapeHtml(label) + '</div>'
                + (subParts.length ? '<div class="rk-picker-chip-sub">' + escapeHtml(subParts.join(' · ')) + '</div>' : '')
                + '</div>'
                + '<button type="button" class="rk-picker-chip-remove" data-id="' + Number(item.id_kavling) + '" title="Hapus dari pilihan">&times;</button>'
                + '</div>';
        }).join('');

        $list.html(html);
    }

    function enterCanvasPickMode() {
        if (!canCreate) return;

        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.close === 'function') {
            window.SIGAPPMobileBottomNav.close();
        }
        if (typeof window.targetInteractiveMode !== 'undefined' && window.targetInteractiveMode && typeof window.isTargetSetModeActive === 'function' && window.isTargetSetModeActive()) {
            $('#target-bar-exit-btn').trigger('click');
        }
        if (typeof hapus_seleksi === 'function') {
            hapus_seleksi();
        }

        const currentIds = ($('#rk-ubah-kavlings').val() || []).map(String);
        const currentItems = currentIds.map(function (idStr) {
            const cached = rkState.kavlingMap[idStr];
            if (cached) return Object.assign({}, cached);
            return {
                id: Number(idStr),
                id_kavling: Number(idStr),
                no_kavling: idStr,
                nama_jalan: '',
                text: 'Kavling #' + idStr
            };
        });

        rkState.canvasPickRestoreItems = currentItems.map(function (it) { return Object.assign({}, it); });
        rkState.canvasPickedItems = currentItems;
        rkState.canvasPickActive = true;

        $modal.modal('hide');
        mountPickerBar();
        $('.siteplan-canvas-col').addClass('is-rk-pick-mode-host');
        $pickerBar.removeAttr('hidden');
        renderPickerBar();
        window.renderRequestKavlingCanvasHighlights();
    }

    function exitCanvasPickMode(applySelection) {
        if (!rkState.canvasPickActive) return;

        rkState.canvasPickActive = false;
        $pickerBar.attr('hidden', true);
        $('.siteplan-canvas-col').removeClass('is-rk-pick-mode-host');
        clearCanvasHighlights();

        const finalItems = applySelection ? rkState.canvasPickedItems : rkState.canvasPickRestoreItems;
        openModalRequestKavling({
            tab: 'ubah',
            kavlings: finalItems,
            replaceKavlings: true
        });
    }

    window.isRequestKavlingCanvasPickActive = function () {
        return Boolean(rkState.canvasPickActive);
    };

    window.cancelRequestKavlingCanvasPick = function () {
        exitCanvasPickMode(false);
    };

    window.handleRequestKavlingCanvasClick = function (sh, idKavlingRaw) {
        if (!rkState.canvasPickActive) return;
        if (!sh || !sh.id || String(sh.id).indexOf('kav') !== 0 || !sh.data || sh.data.tipe !== 'kavling') {
            return;
        }

        const kavId = parseInt(idKavlingRaw || String(sh.id).substring(3), 10);
        if (!kavId || kavId <= 0) return;

        const blockInfo = checkAkadBlockFromShapeData(sh.data, sh.data2);
        if (blockInfo.blocked) {
            const label = (sh.data.nama_jalan ? sh.data.nama_jalan + ' ' : '') + 'No. ' + (sh.data.no_kavling || kavId);
            Swal.fire({
                icon: 'warning',
                title: 'Kavling Tidak Dapat Dipilih',
                text: label + ' berstatus ' + blockInfo.label + ' sehingga tidak dapat diajukan perubahan tipe.'
            });
            return;
        }

        const existingIdx = rkState.canvasPickedItems.findIndex(function (it) {
            return Number(it.id_kavling || it.id) === kavId;
        });

        if (existingIdx >= 0) {
            rkState.canvasPickedItems.splice(existingIdx, 1);
            renderPickerBar();
            window.renderRequestKavlingCanvasHighlights();
            return;
        }

        const item = buildShapeKavlingItem(sh, kavId);
        rkState.kavlingMap[String(kavId)] = Object.assign({}, rkState.kavlingMap[String(kavId)] || {}, item);
        const enriched = rkState.kavlingMap[String(kavId)];
        enriched.points = item.points;

        rkState.canvasPickedItems.push(enriched);
        renderPickerBar();
        window.renderRequestKavlingCanvasHighlights();
    };

    if (canCreate) {
        $('#btn-rk-pick-canvas').on('click', enterCanvasPickMode);
        $('#rk-picker-done-btn').on('click', function () {
            exitCanvasPickMode(true);
        });
        $('#rk-picker-cancel-btn').on('click', function () {
            exitCanvasPickMode(false);
        });
        $('#rk-picker-clear-btn').on('click', function () {
            rkState.canvasPickedItems = [];
            renderPickerBar();
            window.renderRequestKavlingCanvasHighlights();
        });
        $pickerBar.on('click', '.rk-picker-chip-remove', function () {
            const kavId = Number($(this).attr('data-id') || 0);
            rkState.canvasPickedItems = rkState.canvasPickedItems.filter(function (it) {
                return Number(it.id_kavling || it.id) !== kavId;
            });
            renderPickerBar();
            window.renderRequestKavlingCanvasHighlights();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GLOBAL ENTRYPOINTS (OPEN MODAL & CONTEXT MENU)
    // ─────────────────────────────────────────────────────────────────────────

    window.openModalRequestKavling = function (opts) {
        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.close === 'function') {
            window.SIGAPPMobileBottomNav.close();
        }

        let options = {};
        if (typeof opts === 'string') {
            options = { tab: opts };
        } else if (typeof opts === 'number' && opts > 0) {
            options = { tab: 'ubah', prefillKavlingId: opts };
        } else if (opts && typeof opts === 'object') {
            options = opts;
        }

        initCreateSelect2Controls();

        if (canCreate) {
            clearCancelSelection('tambah');
            clearCancelSelection('ubah');
            clearCancelSelection('riwayat');
        }

        let targetTab = '#tab-rk-tambah';
        if (options.tab === 'ubah') {
            targetTab = '#tab-rk-ubah';
        } else if (options.tab === 'riwayat') {
            targetTab = '#tab-rk-riwayat';
        }
        $(targetTab).tab('show');

        $modal.modal('show');

        if (canCreate) {
            loadJalans(false);
        }
        loadActiveTipes(false);
        refreshAllVisibleLists();

        if (canCreate && Array.isArray(options.kavlings)) {
            setUbahSelectedKavlings(options.kavlings);
            hydrateMissingConsumerNames(options.kavlings);
        } else if (canCreate && options.prefillKavling) {
            setUbahSelectedKavlings([options.prefillKavling]);
            hydrateMissingConsumerNames([options.prefillKavling]);
        } else if (canCreate && options.prefillKavlingId) {
            const attrs = typeof findSiteplanKavlingAttrs === 'function'
                ? findSiteplanKavlingAttrs(options.prefillKavlingId)
                : null;
            const item = attrs
                ? buildShapeKavlingItem(attrs, options.prefillKavlingId)
                : {
                    id: Number(options.prefillKavlingId),
                    id_kavling: Number(options.prefillKavlingId),
                    no_kavling: String(options.prefillKavlingId),
                    text: 'Kavling #' + options.prefillKavlingId
                };
            setUbahSelectedKavlings([item]);
            hydrateMissingConsumerNames([item]);
        }
    };

    window.openModalListRequestKavling = function () {
        window.openModalRequestKavling({ tab: 'tambah' });
    };

    window.refreshRequestKavlingCounts = loadCountsSummary;

    if (canCreate) {
        window.handleContextRequestKavling = function () {
            let targetAttrs = null;
            if (typeof currentShape !== 'undefined' && currentShape && currentShape.target && currentShape.target.attrs) {
                targetAttrs = currentShape.target.attrs;
            }
            if ((!targetAttrs || !targetAttrs.id || String(targetAttrs.id).indexOf('kav') !== 0)
                && typeof editdtt !== 'undefined' && Array.isArray(editdtt) && editdtt.length > 0 && editdtt[0]) {
                targetAttrs = editdtt[0];
            }

            if (targetAttrs && targetAttrs.id && String(targetAttrs.id).indexOf('kav') === 0) {
                const kavId = parseInt(String(targetAttrs.id).substring(3), 10);
                const blockInfo = checkAkadBlockFromShapeData(targetAttrs.data, targetAttrs.data2);

                if (blockInfo.blocked) {
                    const data = targetAttrs.data || {};
                    const label = (data.nama_jalan ? (data.nama_jalan + ' ') : '') + 'No. ' + (data.no_kavling || kavId);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Dapat Diajukan Ubah Tipe',
                        text: label + ' berstatus ' + blockInfo.label + ' dan tidak dapat diajukan perubahan tipe.'
                    });
                    return;
                }

                const prefillItem = buildShapeKavlingItem(targetAttrs, kavId);
                window.openModalRequestKavling({
                    tab: 'ubah',
                    prefillKavling: prefillItem
                });
                return;
            }

            window.openModalRequestKavling({ tab: 'ubah' });
        };
    }

    // Load initial badge count & re-render on tab switch
    $(document).ready(function () {
        if (getProyekId() > 0) {
            loadCountsSummary();
        }

        $('#rk-modal-tablist a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            const href = $(e.target).attr('href');
            if (href === '#panel-rk-tambah') {
                loadTambahTab();
            } else if (href === '#panel-rk-ubah') {
                loadUbahTab();
            } else if (href === '#panel-rk-riwayat') {
                loadRiwayatTab();
            }
        });
    });
})(jQuery);
