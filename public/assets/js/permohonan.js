(function ($) {
    'use strict';

    const config = window.PERMOHONAN_CONFIG || {};
    const baseUrl = String(config.baseUrl || (typeof base_url !== 'undefined' ? base_url : '')).replace(/\/+$/, '');
    const csrfName = config.csrfName || (typeof window.csrfName !== 'undefined' ? window.csrfName : 'csrf_test_name');
    let localCsrfHash = config.csrfHash || (typeof window.csrfHash !== 'undefined' ? window.csrfHash : '');
    const meta = config.meta || {};
    const perms = meta.permissions || {};
    const deepLink = config.deepLink || {};

    const hubState = {
        scope: deepLink.scope || 'assigned_to_me',
        module: deepLink.module || 'all',
        status: 'all',
        id_proyek: deepLink.id_proyek ? String(deepLink.id_proyek) : '',
        search: '',
        page: 1,
        per_page: 20,
        items: [],
        pagination: { page: 1, per_page: 20, total: 0, total_pages: 0 },
        summary: config.summary || {},
        loading: false,
    };

    const canvases = {};
    let activeItem = null;
    let currentBpb = null;
    let bpbActionMode = '';
    let currentFeeSales = null;
    let currentKavlingReq = null;
    let kavlingTipesCache = null;
    let currentTagihan = null;
    let tagihanHasProfileSig = false;
    let currentMgmContext = null;
    let currentMgmStages = [];
    let currentMgmSelectedStage = null;
    let currentMgmAction = '';
    let searchTimer = null;
    let deepLinkHandled = false;

    const moduleLabelMap = {
        all: 'Semua Modul',
        bpb: 'BPB',
        fee_sales: 'Fee Sales',
        kavling_request: 'Request Kavling',
        tagihan_direksi: 'Tagihan Direksi',
        cashout_subkon: 'Cashout Subkon',
        mgm: 'Member Get Member',
        tiket_masalah: 'Tiket Masalah',
    };

    const moduleIconMap = {
        bpb: 'fa-box-open',
        fee_sales: 'fa-hand-holding-usd',
        kavling_request: 'fa-map-marked-alt',
        tagihan_direksi: 'fa-file-signature',
        cashout_subkon: 'fa-hard-hat',
        mgm: 'fa-users',
        tiket_masalah: 'fa-exclamation-triangle',
    };

    function apiUrl(path) {
        return baseUrl + '/' + String(path || '').replace(/^\/+/, '');
    }

    function esc(val) {
        return $('<div>').text(val == null ? '' : String(val)).html();
    }

    function syncToken(payload) {
        if (payload && payload.token) {
            localCsrfHash = payload.token;
            if (typeof window.csrfHash !== 'undefined') {
                window.csrfHash = payload.token;
            }
            if (typeof csrfHash !== 'undefined') {
                csrfHash = payload.token;
            }
        }
    }

    function currentToken() {
        if (typeof csrfHash !== 'undefined' && csrfHash) {
            return csrfHash;
        }
        return localCsrfHash;
    }

    function formatRupiah(val) {
        if (val === null || val === undefined || val === '') return '-';
        const num = Number(val);
        if (!Number.isFinite(num)) return '-';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(num);
    }

    function formatDate(val) {
        if (!val) return '-';
        const normalized = String(val).replace(' ', 'T');
        const dt = new Date(normalized);
        if (Number.isNaN(dt.getTime())) return String(val);
        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(dt);
    }

    function formatDateTime(val) {
        if (!val) return '-';
        const normalized = String(val).replace(' ', 'T');
        const dt = new Date(normalized);
        if (Number.isNaN(dt.getTime())) return String(val);
        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(dt);
    }

    function sanitizeUrl(raw) {
        const str = String(raw == null ? '' : raw).trim();
        if (!str) return '';
        if (/^https?:\/\//i.test(str)) return str;
        if (/^\/\//.test(str) || /^(javascript|data|vbscript):/i.test(str)) return '';
        if (str.startsWith('/')) {
            let basePath = '';
            try {
                basePath = new URL(baseUrl || '/', window.location.origin).pathname.replace(/\/+$/, '');
            } catch (_) {
                basePath = '';
            }
            if (basePath && (str === basePath || str.startsWith(basePath + '/'))) {
                return str;
            }
            return apiUrl(str);
        }
        return apiUrl(str);
    }

    function extractApiError(json, xhr, fallback) {
        if (json) {
            syncToken(json);
            if (typeof json.messages === 'string' && json.messages) return json.messages;
            if (json.messages && typeof json.messages === 'object') {
                const vals = Object.values(json.messages).filter(Boolean);
                if (vals.length) return vals.join(' ');
            }
            if (typeof json.message === 'string' && json.message) return json.message;
            if (json.errors && typeof json.errors === 'object') {
                const errs = Object.values(json.errors).filter(Boolean);
                if (errs.length) return errs.join(' ');
            }
        }
        if (xhr && xhr.responseJSON) {
            return extractApiError(xhr.responseJSON, null, fallback);
        }
        return fallback || 'Terjadi kesalahan saat memproses permintaan.';
    }

    async function apiGet(path, params) {
        const u = new URL(apiUrl(path), window.location.origin);
        if (params && typeof params === 'object') {
            Object.entries(params).forEach(([k, v]) => {
                if (v !== undefined && v !== null && v !== '') {
                    u.searchParams.set(k, String(v));
                }
            });
        }
        const res = await fetch(u.toString(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const json = await res.json().catch(() => ({}));
        syncToken(json);
        if (!res.ok || json.success === false) {
            throw new Error(extractApiError(json, null, 'Gagal memuat data dari server.'));
        }
        return json;
    }

    async function apiPost(path, bodyOrFormData) {
        const token = currentToken();
        const isForm = bodyOrFormData instanceof FormData;
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
        };
        let body;
        if (isForm) {
            if (!bodyOrFormData.has(csrfName)) {
                bodyOrFormData.append(csrfName, token);
            }
            body = bodyOrFormData;
        } else {
            const fd = new URLSearchParams();
            fd.append(csrfName, token);
            if (bodyOrFormData && typeof bodyOrFormData === 'object') {
                Object.entries(bodyOrFormData).forEach(([k, v]) => {
                    if (v === undefined || v === null) return;
                    if (Array.isArray(v)) {
                        v.forEach(item => fd.append(`${k}[]`, String(item)));
                    } else {
                        fd.append(k, String(v));
                    }
                });
            }
            headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            body = fd.toString();
        }

        const res = await fetch(apiUrl(path), {
            method: 'POST',
            headers,
            body,
        });
        const json = await res.json().catch(() => ({}));
        syncToken(json);
        if (!res.ok || json.success === false) {
            throw new Error(extractApiError(json, null, 'Permintaan gagal diproses.'));
        }
        return json;
    }

    function showErrorAlert(msg) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak dapat diproses',
            text: msg || 'Terjadi kesalahan.',
        });
    }

    function afterActionSuccess(message, reloadCurrentModalFn) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: message || 'Perubahan berhasil disimpan.',
            timer: 1800,
            showConfirmButton: false,
        });
        loadList(hubState.page);
        if (typeof window.loadNotificationBadge === 'function') {
            window.loadNotificationBadge();
        }
        if (typeof reloadCurrentModalFn === 'function') {
            reloadCurrentModalFn();
        }
    }

    function refreshHubAndBadgeOnly() {
        loadList(hubState.page);
        if (typeof window.loadNotificationBadge === 'function') {
            window.loadNotificationBadge();
        }
    }

    function statusBadgeHtml(variant, label) {
        const safeVar = ['primary', 'warning', 'success', 'danger', 'secondary'].includes(variant)
            ? variant
            : 'secondary';
        return `<span class="permohonan-status-badge permohonan-status--${safeVar}">${esc(label || '-')}</span>`;
    }

    /* ======================================================================
       SIGNATURE CANVAS HELPER
       ====================================================================== */
    function initCanvas(key, canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let dirty = false;

        const resize = () => {
            const parent = canvas.parentElement;
            if (!parent) return;
            const width = Math.floor(parent.getBoundingClientRect().width);
            if (width < 20) return;
            const snapshot = dirty ? canvas.toDataURL('image/png') : null;
            const ratio = window.devicePixelRatio || 1;
            const height = 185;
            canvas.width = Math.max(1, Math.floor(width * ratio));
            canvas.height = Math.floor(height * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#172b4d';
            if (snapshot) {
                const img = new Image();
                img.onload = () => ctx.drawImage(img, 0, 0, width, height);
                img.src = snapshot;
            }
        };

        const getPoint = (ev) => {
            const rect = canvas.getBoundingClientRect();
            return { x: ev.clientX - rect.left, y: ev.clientY - rect.top };
        };

        canvas.addEventListener('pointerdown', (ev) => {
            if (ev.isPrimary === false) return;
            ev.preventDefault();
            drawing = true;
            dirty = true;
            if (canvas.setPointerCapture) canvas.setPointerCapture(ev.pointerId);
            const p = getPoint(ev);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });

        canvas.addEventListener('pointermove', (ev) => {
            if (!drawing) return;
            ev.preventDefault();
            const p = getPoint(ev);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });

        const stop = (ev) => {
            drawing = false;
            if (ev && canvas.hasPointerCapture && canvas.hasPointerCapture(ev.pointerId)) {
                canvas.releasePointerCapture(ev.pointerId);
            }
        };

        canvas.addEventListener('pointerup', stop);
        canvas.addEventListener('pointercancel', stop);

        canvases[key] = {
            resize,
            clear: () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                dirty = false;
            },
            isDirty: () => dirty,
            data: () => (dirty ? canvas.toDataURL('image/png') : ''),
        };

        resize();
        return canvases[key];
    }

    function openImageLightbox(url, caption) {
        if (!url) return;
        $('#permohonan-lightbox-img').attr({ src: url, alt: caption || 'Pratinjau lampiran' });
        $('#permohonan-image-lightbox').removeClass('d-none');
    }

    function closeImageLightbox() {
        $('#permohonan-image-lightbox').addClass('d-none');
        $('#permohonan-lightbox-img').attr('src', '');
    }

    /* ======================================================================
       MAIN HUB RENDERING: SUMMARY, FILTERS, LIST, PAGINATION
       ====================================================================== */
    function renderSummary(summary) {
        if (!summary || typeof summary !== 'object') return;
        hubState.summary = summary;
        $('#permohonan-count-assigned_to_me').text(Number(summary.assigned_to_me || 0));
        $('#permohonan-count-my_requests').text(Number(summary.my_requests || 0));
        $('#permohonan-count-department_monitoring').text(Number(summary.department_monitoring || 0));

        const byScope = summary.by_scope_and_module || {};
        const modCounts = byScope[hubState.scope] || summary.by_module || {};
        $('[data-module-count]').each(function () {
            const mod = $(this).data('module-count');
            $(this).text(Number(modCounts[mod] || 0));
        });
    }

    function renderActiveFilterChips() {
        const chips = [];
        if (hubState.module !== 'all') {
            chips.push({
                key: 'module',
                label: 'Modul: ' + (moduleLabelMap[hubState.module] || hubState.module),
            });
        }
        if (hubState.id_proyek) {
            const proyekLabel = $('#permohonan-filter-proyek option:selected').text().trim();
            chips.push({
                key: 'id_proyek',
                label: 'Proyek: ' + (proyekLabel || ('#' + hubState.id_proyek)),
            });
        }
        if (hubState.status && hubState.status !== 'all') {
            const statusLabel = $('#permohonan-filter-status option:selected').text().trim();
            chips.push({
                key: 'status',
                label: 'Status: ' + statusLabel,
            });
        }
        if (hubState.search) {
            chips.push({
                key: 'search',
                label: 'Cari: "' + hubState.search + '"',
            });
        }

        const $wrap = $('#permohonan-active-filters');
        if (!chips.length) {
            $wrap.empty().addClass('d-none');
            return;
        }

        const html = chips.map(c =>
            `<span class="permohonan-filter-chip">${esc(c.label)}<button type="button" data-clear-chip="${esc(c.key)}" aria-label="Hapus filter">&times;</button></span>`
        ).join('') + `<button type="button" class="btn btn-link btn-sm p-0 ml-50" id="permohonan-clear-all-filters">Bersihkan semua</button>`;
        $wrap.html(html).removeClass('d-none');
    }

    function renderListItems(items) {
        const $tbody = $('#permohonan-table-body');
        const $mobile = $('#permohonan-mobile-list');

        if (!items || !items.length) {
            $tbody.empty();
            $mobile.empty();
            $('#permohonan-table-wrap').addClass('d-none').removeClass('d-md-block');
            $('#permohonan-mobile-list').addClass('d-none');
            $('#permohonan-empty').removeClass('d-none');
            return;
        }

        $('#permohonan-empty').addClass('d-none');
        $('#permohonan-table-wrap').removeClass('d-none').addClass('d-md-block');
        $('#permohonan-mobile-list').removeClass('d-none');

        const rowsHtml = items.map((item, idx) => {
            const icon = moduleIconMap[item.module] || 'fa-file-alt';
            const ctaClass = item.can_respond ? 'btn-primary' : 'btn-outline-primary';
            const proyekLokasi = item.nama_proyek
                ? esc(item.nama_proyek) + (item.lokasi_kavling ? `<div class="permohonan-item-sub">${esc(item.lokasi_kavling)}</div>` : '')
                : '-';
            const pemohon = `${esc(item.pemohon_nama || '-')}<div class="permohonan-item-sub">${esc(item.pemohon_divisi || '-')}</div>`;
            const nominalStr = item.nominal !== null && item.nominal !== undefined ? formatRupiah(item.nominal) : '<span class="text-muted">-</span>';

            return `<tr data-item-index="${idx}">
                <td><span class="permohonan-doc-no">${esc(item.nomor_dokumen || '-')}</span></td>
                <td><span class="permohonan-module-tag"><i class="fas ${esc(icon)} text-primary"></i>${esc(item.module_label || item.module)}</span></td>
                <td>
                    <div class="permohonan-item-title">${esc(item.judul || '-')}</div>
                    <div class="permohonan-item-sub">${esc(item.subjudul || '')}</div>
                </td>
                <td>${proyekLokasi}</td>
                <td>${pemohon}</td>
                <td class="text-right font-weight-bold text-nowrap">${nominalStr}</td>
                <td>${statusBadgeHtml(item.status_variant, item.status_label)}</td>
                <td class="text-nowrap small">${esc(formatDate(item.created_at))}</td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn btn-sm ${ctaClass} permohonan-btn-open" data-item-index="${idx}">
                        ${esc(item.primary_action_label || (item.can_respond ? 'Respon' : 'Detail'))}
                    </button>
                </td>
            </tr>`;
        }).join('');

        const mobileHtml = items.map((item, idx) => {
            const icon = moduleIconMap[item.module] || 'fa-file-alt';
            const ctaClass = item.can_respond ? 'btn-primary' : 'btn-outline-primary';
            const nominalStr = item.nominal !== null && item.nominal !== undefined ? formatRupiah(item.nominal) : '-';
            return `<article class="permohonan-mobile-card" data-item-index="${idx}">
                <div class="permohonan-mobile-card-head">
                    <span class="permohonan-doc-no">${esc(item.nomor_dokumen || '-')}</span>
                    <span class="permohonan-module-tag"><i class="fas ${esc(icon)} text-primary"></i>${esc(item.module_label || item.module)}</span>
                </div>
                <div>
                    <div class="permohonan-item-title">${esc(item.judul || '-')}</div>
                    <div class="permohonan-item-sub">${esc(item.subjudul || '')}</div>
                </div>
                <div class="permohonan-mobile-card-meta">
                    <div><small>Proyek / Lokasi</small><span>${esc(item.nama_proyek || '-')}${item.lokasi_kavling ? ' · ' + esc(item.lokasi_kavling) : ''}</span></div>
                    <div><small>Pemohon</small><span>${esc(item.pemohon_nama || '-')} (${esc(item.pemohon_divisi || '-')})</span></div>
                    <div><small>Nominal</small><strong>${nominalStr}</strong></div>
                    <div><small>Tanggal</small><span>${esc(formatDate(item.created_at))}</span></div>
                </div>
                <div class="permohonan-mobile-card-foot">
                    ${statusBadgeHtml(item.status_variant, item.status_label)}
                    <button type="button" class="btn btn-sm ${ctaClass} permohonan-btn-open" data-item-index="${idx}">
                        ${esc(item.primary_action_label || (item.can_respond ? 'Respon' : 'Detail'))}
                    </button>
                </div>
            </article>`;
        }).join('');

        $tbody.html(rowsHtml);
        $mobile.html(mobileHtml);
    }

    function renderPagination(pag) {
        const $bar = $('#permohonan-pagination-bar');
        const $info = $('#permohonan-pagination-info');
        const $ctrl = $('#permohonan-pagination-controls');

        const total = Number(pag.total || 0);
        const page = Number(pag.page || 1);
        const perPage = Number(pag.per_page || 20);
        const totalPages = Number(pag.total_pages || 0);

        if (total === 0) {
            $bar.addClass('d-none');
            return;
        }

        const start = (page - 1) * perPage + 1;
        const end = Math.min(total, page * perPage);
        $info.text(`Menampilkan ${start}–${end} dari ${total} permohonan`);

        if (totalPages <= 1) {
            $ctrl.empty();
            $bar.removeClass('d-none');
            return;
        }

        const btns = [];
        btns.push(`<button type="button" class="btn btn-outline-secondary" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>&laquo; Prev</button>`);
        const fromPage = Math.max(1, page - 2);
        const toPage = Math.min(totalPages, page + 2);
        for (let p = fromPage; p <= toPage; p++) {
            btns.push(`<button type="button" class="btn ${p === page ? 'btn-primary' : 'btn-outline-secondary'}" data-page="${p}">${p}</button>`);
        }
        btns.push(`<button type="button" class="btn btn-outline-secondary" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>Next &raquo;</button>`);

        $ctrl.html(btns.join(''));
        $bar.removeClass('d-none');
    }

    function setActiveTabAndPillUI() {
        $('.permohonan-scope-card').each(function () {
            const isAct = $(this).data('scope') === hubState.scope;
            $(this).toggleClass('is-active', isAct).attr('aria-selected', isAct ? 'true' : 'false');
        });
        $('.permohonan-module-pill').each(function () {
            const isAct = $(this).data('module') === hubState.module;
            $(this).toggleClass('is-active', isAct);
        });
    }

    async function loadList(page = 1) {
        hubState.page = Math.max(1, Number(page) || 1);
        hubState.loading = true;
        setActiveTabAndPillUI();
        renderActiveFilterChips();

        $('#permohonan-error, #permohonan-empty').addClass('d-none');
        $('#permohonan-loading').removeClass('d-none');

        try {
            const res = await apiGet('api/permohonan/list', {
                scope: hubState.scope,
                module: hubState.module,
                status: hubState.status,
                id_proyek: hubState.id_proyek,
                search: hubState.search,
                page: hubState.page,
                per_page: hubState.per_page,
            });
            const data = res.data || {};
            hubState.items = Array.isArray(data.items) ? data.items : [];
            hubState.pagination = data.pagination || { page: hubState.page, per_page: hubState.per_page, total: 0, total_pages: 0 };
            if (data.summary) renderSummary(data.summary);

            $('#permohonan-loading').addClass('d-none');
            renderListItems(hubState.items);
            renderPagination(hubState.pagination);

            if (!deepLinkHandled && deepLink.id && deepLink.module && deepLink.module !== 'all') {
                deepLinkHandled = true;
                handleDeepLinkOpen(deepLink.module, deepLink.id);
            }
        } catch (err) {
            $('#permohonan-loading').addClass('d-none');
            $('#permohonan-table-wrap').addClass('d-none').removeClass('d-md-block');
            $('#permohonan-mobile-list').addClass('d-none');
            $('#permohonan-pagination-bar').addClass('d-none');
            $('#permohonan-error-message').text(err.message);
            $('#permohonan-error').removeClass('d-none');
        } finally {
            hubState.loading = false;
        }
    }

    /* ======================================================================
       DEEP-LINK AUTO OPEN & DISPATCHER
       ====================================================================== */
    function matchesDeepLink(item, targetModule, targetId) {
        if (!item || item.module !== targetModule) return false;
        const tid = String(targetId);
        if (String(item.id) === tid) return true;
        const mp = item.modal_payload || {};
        return [
            mp.id_bpb,
            mp.id_pengajuan,
            mp.id_request,
            mp.no_inv,
            mp.id_cashout_subkon_detail,
            mp.id_cashout_subkon,
            mp.id_bonus,
            mp.id_tiket,
        ].some(val => val !== undefined && val !== null && String(val) === tid);
    }

    async function handleDeepLinkOpen(targetModule, targetId) {
        const foundInCurrent = hubState.items.find(it => matchesDeepLink(it, targetModule, targetId));
        if (foundInCurrent) {
            openResponseModal(foundInCurrent);
            return;
        }

        const scopesToTry = ['assigned_to_me', 'my_requests', 'department_monitoring'].filter(s => s !== hubState.scope);
        for (const sc of [hubState.scope, ...scopesToTry]) {
            try {
                const res = await apiGet('api/permohonan/list', {
                    scope: sc,
                    module: targetModule,
                    status: 'all',
                    per_page: 100,
                });
                const list = (res.data && res.data.items) || [];
                const hit = list.find(it => matchesDeepLink(it, targetModule, targetId));
                if (hit) {
                    openResponseModal(hit);
                    return;
                }
            } catch (_) {}
        }

        // Fallback direct payload for ID-addressable modules
        if (targetModule === 'bpb') {
            openBpbModal({ id: targetId, modal_payload: { id_bpb: Number(targetId) } });
        } else if (targetModule === 'fee_sales') {
            openFeeSalesModal({ id: targetId, modal_payload: { id_pengajuan: Number(targetId) } });
        } else if (targetModule === 'tagihan_direksi') {
            openTagihanDireksiModal({ id: targetId, modal_payload: { no_inv: String(targetId) } });
        } else if (targetModule === 'cashout_subkon') {
            openCashoutSubkonModal({ id: targetId, modal_payload: { id_cashout_subkon: Number(targetId), id_proyek: deepLink.id_proyek || null } });
        }
    }

    function openResponseModal(item) {
        if (!item) return;
        activeItem = item;
        const payload = item.modal_payload || {};
        switch (item.module) {
            case 'bpb':
                openBpbModal(item);
                break;
            case 'fee_sales':
                openFeeSalesModal(item);
                break;
            case 'kavling_request':
                openKavlingRequestModal(item);
                break;
            case 'tagihan_direksi':
                openTagihanDireksiModal(item);
                break;
            case 'cashout_subkon':
                openCashoutSubkonModal(item);
                break;
            case 'mgm':
                openMgmModal(item);
                break;
            case 'tiket_masalah':
                openTiketMasalahModal(payload);
                break;
            default:
                showErrorAlert('Modul tidak dikenal.');
        }
    }

    /* ======================================================================
       MODAL 1: BPB
       ====================================================================== */
    function bpbStatusBadge(status, label) {
        const map = {
            waiting_cc: 'warning',
            waiting_approver: 'warning',
            pending: 'warning',
            approved: 'primary',
            processed: 'primary',
            disbursed: 'primary',
            purchased: 'success',
            rejected: 'danger',
            cancelled: 'secondary',
        };
        return { variant: map[status] || 'secondary', label: label || status || '-' };
    }

    function renderBpbFiles(files) {
        files = Array.isArray(files) ? files : [];
        if (!files.length) return '<span class="text-muted small">Tidak ada lampiran.</span>';

        const images = files.filter(f => String(f.mime_type || '').toLowerCase().startsWith('image/'));
        const docs = files.filter(f => !String(f.mime_type || '').toLowerCase().startsWith('image/'));

        const imgHtml = images.map(f => {
            const fullUrl = sanitizeUrl(f.access_url || f.url);
            const thumbUrl = sanitizeUrl(f.thumbnail_url || f.access_url || f.url);
            return `<button type="button" class="permohonan-image-thumb permohonan-lightbox-trigger" data-img-url="${esc(fullUrl)}" data-img-name="${esc(f.original_name)}">
                <img src="${esc(thumbUrl)}" alt="${esc(f.original_name)}">
                <span>${esc(f.original_name)}</span>
            </button>`;
        }).join('');

        const docHtml = docs.map(f => {
            const href = sanitizeUrl(f.download_url || f.access_url || f.url);
            return `<a class="permohonan-file-pill" href="${esc(href)}" target="_blank" rel="noopener">
                <i class="fas fa-file-pdf text-danger"></i>${esc(f.original_name || 'Lampiran')}
            </a>`;
        }).join('');

        return `<div class="permohonan-attachments-grid">${imgHtml}${docHtml}</div>`;
    }

    async function openBpbModal(item) {
        const idBpb = (item.modal_payload && item.modal_payload.id_bpb) || item.id;
        $('#permohonan-bpb-title').text(item.nomor_dokumen || 'Detail BPB');
        $('#permohonan-bpb-subtitle').text('Memuat detail dokumen BPB...');
        $('#permohonan-bpb-body').html('<div class="text-center py-3"><span class="spinner-border text-primary"></span></div>');
        $('#permohonan-bpb-actions').html('<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>');
        $('#permohonan-bpb-modal').modal('show');

        try {
            const res = await apiGet(`api/bpb/detail/${idBpb}`);
            const d = res.data || res;
            currentBpb = d;
            renderBpbDetail(d);
        } catch (err) {
            $('#permohonan-bpb-body').html(`<div class="alert alert-danger">${esc(err.message)}</div>`);
        }
    }

    function renderBpbDetail(d) {
        const badge = bpbStatusBadge(d.status, d.status_label);
        $('#permohonan-bpb-title').text(d.nomor || ('BPB #' + d.id));
        $('#permohonan-bpb-subtitle').text(`Pemohon: ${d.applicant_name || '-'} (${d.applicant_department || '-'})`);
        $('#permohonan-bpb-status').attr('class', `permohonan-status-badge permohonan-status--${badge.variant}`).text(badge.label);

        const reqFiles = (d.files || []).filter(f => f.category === 'request');
        const purFiles = (d.files || []).filter(f => f.category === 'purchase');
        const itemsRows = (d.items || []).map((it, i) => `<tr>
            <td>${i + 1}</td>
            <td><strong>${esc(it.nama_barang)}</strong></td>
            <td class="text-nowrap">${esc(it.jumlah)} ${esc(it.satuan || '')}</td>
            <td>${esc(it.keterangan || '-')}</td>
        </tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted">Tidak ada item.</td></tr>';

        const roleMap = { applicant: 'Pemohon', cc: 'CC / Paraf', approver: 'Mengetahui', expense_verifier: 'Verifikasi Pengeluaran' };
        const sigs = (d.signatures || []).filter(s => !s.revoked).map(s => `<div class="col-md-4 mb-1">
            <div class="permohonan-signature-card">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="small">${esc(roleMap[s.role] || s.role)}</strong>
                    <span class="badge badge-light-success">Valid</span>
                </div>
                <div class="permohonan-signature-preview">
                    <img src="${esc(sanitizeUrl(s.image_url))}" alt="TTD ${esc(s.signer_name)}">
                </div>
                <div class="font-weight-bold small">${esc(s.signer_name || '-')}</div>
                <small class="text-muted">${esc(formatDateTime(s.signed_at))}</small>
            </div>
        </div>`).join('') || '<div class="col-12 text-muted small">Belum ada tanda tangan.</div>';

        const hasFinance = d.actual_amount || d.purchase_date || purFiles.length || d.disbursement_note || d.purchase_note || d.pending_reason;
        const financeHtml = hasFinance ? `<div class="card"><div class="card-body">
            <div class="divider divider-left"><div class="divider-text">Pencairan &amp; Pembelian</div></div>
            <div class="row">
                <div class="col-md-3"><div class="info-row"><span class="info-label">Nominal Pencairan</span><span class="info-value">${d.actual_amount ? esc(formatRupiah(d.actual_amount)) : '-'}</span></div></div>
                <div class="col-md-3"><div class="info-row"><span class="info-label">Penerima Dana</span><span class="info-value">${esc(d.recipient_name || '-')}</span></div></div>
                <div class="col-md-3"><div class="info-row"><span class="info-label">No. Rekening</span><span class="info-value">${esc(d.account_number || '-')}</span></div></div>
                <div class="col-md-3"><div class="info-row"><span class="info-label">Tanggal Beli</span><span class="info-value">${esc(d.purchase_date || '-')}</span></div></div>
            </div>
            ${d.disbursement_note ? `<p class="small mb-50"><strong>Catatan Pencairan:</strong> ${esc(d.disbursement_note)}</p>` : ''}
            ${d.pending_reason ? `<p class="small text-warning mb-50"><strong>Alasan Pending:</strong> ${esc(d.pending_reason)}</p>` : ''}
            ${d.purchase_note ? `<p class="small mb-50"><strong>Keterangan Pembelian:</strong> ${esc(d.purchase_note)}</p>` : ''}
            <div class="mt-75">${renderBpbFiles(purFiles)}</div>
        </div></div>` : '';

        const timelineHtml = (d.history || []).map((h, i) => `<div class="permohonan-timeline-item ${i === d.history.length - 1 ? 'is-current' : ''}">
            <div>
                <div class="font-weight-bold small">${esc(h.summary || '-')}</div>
                <small class="text-muted">Oleh ${esc(h.actor_name || '-')}</small>
            </div>
            <div class="text-right small text-muted">${esc(formatDateTime(h.created_at))}</div>
        </div>`).join('') || '<div class="text-muted small">Belum ada riwayat aktivitas.</div>';

        $('#permohonan-bpb-body').html(`
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Nomor BPB</span><span class="info-value permohonan-mono">${esc(d.nomor || 'Draft')}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Pemohon</span><span class="info-value">${esc(d.applicant_name || '-')} · ${esc(d.applicant_department || '-')}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Hash Audit</span><span class="info-value permohonan-mono">${esc(d.current_document_hash || '-')}</span></div></div>
                </div>
            </div></div>
            <div class="card"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Barang Diminta &amp; Lampiran</div></div>
                <div class="table-responsive mb-1">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>No</th><th>Nama Barang</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
                        <tbody>${itemsRows}</tbody>
                    </table>
                </div>
                <div class="font-weight-bold small text-muted mb-50">Lampiran Pengajuan</div>
                ${renderBpbFiles(reqFiles)}
            </div></div>
            <div class="card"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Rantai Tanda Tangan</div></div>
                <div class="row">${sigs}</div>
            </div></div>
            ${financeHtml}
            <div class="card mb-0"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Audit Trail</div></div>
                ${timelineHtml}
            </div></div>
        `);

        const actions = d.actions || {};
        const pdfHref = sanitizeUrl(d.pdf_url || `bpb/${d.id}/pdf`);
        const btns = [
            `<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>`,
            `<a class="btn btn-outline-primary" href="${esc(pdfHref)}" target="_blank" rel="noopener"><i class="fas fa-file-pdf mr-50"></i>PDF</a>`,
        ];
        if (actions.sign || d.can_sign) btns.push('<button type="button" class="btn btn-primary" data-permohonan-bpb-action="sign"><i class="fas fa-signature mr-50"></i>Tanda Tangani</button>');
        if (actions.reject || d.can_reject) btns.push('<button type="button" class="btn btn-outline-danger" data-permohonan-bpb-action="reject"><i class="fas fa-times mr-50"></i>Tolak</button>');
        if (actions.cancel || d.can_cancel) btns.push('<button type="button" class="btn btn-outline-danger" data-permohonan-bpb-action="cancel">Batalkan</button>');
        if (actions.process) btns.push('<button type="button" class="btn btn-primary" data-permohonan-bpb-action="process">Tandai Diproses</button>');
        if (actions.disburse) btns.push('<button type="button" class="btn btn-primary" data-permohonan-bpb-action="disburse">Tandai Sudah Cair</button>');
        if (actions.finish) btns.push('<button type="button" class="btn btn-primary" data-permohonan-bpb-action="finish">Verifikasi Pengeluaran</button>');
        $('#permohonan-bpb-actions').html(btns.join(''));
    }

    function openBpbActionModal(mode) {
        if (!currentBpb) return;
        bpbActionMode = mode;
        const formEl = document.getElementById('permohonan-bpb-action-form');
        if (formEl) formEl.reset();
        if (canvases.bpb) canvases.bpb.clear();
        $('#permohonan-bpb-action-signature').addClass('d-none');
        $('#permohonan-bpb-canvas-wrap').removeClass('d-none');

        let title = 'Aksi BPB';
        let fields = '';
        const today = new Date().toISOString().slice(0, 10);

        if (mode === 'sign') {
            title = 'Tanda Tangani (ACC) BPB';
            $('#permohonan-bpb-action-signature').removeClass('d-none');
        } else if (mode === 'process') {
            title = 'Tandai Diproses (Verifikasi Pengeluaran)';
            $('#permohonan-bpb-action-signature').removeClass('d-none');
        } else if (mode === 'reject') {
            title = 'Tolak BPB';
            fields = `<div class="form-group mb-0"><label>Alasan Penolakan <span class="text-danger">*</span></label><textarea class="form-control" id="permohonan-bpb-reason" rows="3" required placeholder="Tuliskan alasan penolakan BPB"></textarea></div>`;
        } else if (mode === 'cancel') {
            title = 'Batalkan BPB';
            fields = `<div class="form-group mb-0"><label>Alasan Pembatalan</label><textarea class="form-control" id="permohonan-bpb-reason" rows="3" placeholder="Tuliskan alasan pembatalan (opsional)"></textarea></div>`;
        } else if (mode === 'disburse') {
            title = 'Tandai Sudah Cair';
            fields = `<div class="form-group"><label>Nominal Aktual <span class="text-danger">*</span></label><input type="number" min="1" step="any" class="form-control" id="permohonan-bpb-actual-amount" required></div>
                <div class="form-group"><label>Nama Penerima Dana <span class="text-danger">*</span></label><input type="text" class="form-control" id="permohonan-bpb-recipient" required></div>
                <div class="form-group"><label>Nomor Rekening Tujuan <span class="text-danger">*</span></label><input type="text" class="form-control" id="permohonan-bpb-account" required></div>
                <div class="form-group mb-0"><label>Catatan Pencairan</label><textarea class="form-control" id="permohonan-bpb-note" rows="2"></textarea></div>`;
        } else if (mode === 'finish') {
            title = 'Verifikasi Pengeluaran BPB';
            fields = `<div class="form-group">
                    <label>Status Pengeluaran</label>
                    <select class="form-control" id="permohonan-bpb-finish-status">
                        <option value="purchased">Sudah Dibeli (Verifikasi Pengeluaran)</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div id="permohonan-bpb-finish-purchased">
                    <div class="form-group"><label>Tanggal Pembelian <span class="text-danger">*</span></label><input type="date" class="form-control" id="permohonan-bpb-purchase-date" value="${today}"></div>
                    <div class="form-group">
                        <label>Bukti Nota / Kuitansi Pembelian (1–5 file) <span class="text-danger">*</span></label>
                        <input type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="d-none" id="permohonan-bpb-purchase-files">
                        <div class="bpb-dropzone" id="permohonan-bpb-purchase-dropzone" tabindex="0">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <strong>Pilih atau tarik nota pembelian ke sini</strong>
                            <small id="permohonan-bpb-purchase-files-label">JPG, PNG, WEBP, PDF (maks 5 file)</small>
                        </div>
                    </div>
                    <div class="form-group mb-0"><label>Keterangan Pembelian</label><textarea class="form-control" id="permohonan-bpb-note" rows="2"></textarea></div>
                </div>
                <div id="permohonan-bpb-finish-pending" class="d-none">
                    <div class="form-group mb-0"><label>Alasan Pending <span class="text-danger">*</span></label><textarea class="form-control" id="permohonan-bpb-reason" rows="3"></textarea></div>
                </div>`;
            $('#permohonan-bpb-action-signature').removeClass('d-none');
        }

        $('#permohonan-bpb-action-title').text(title);
        $('#permohonan-bpb-action-fields').html(fields);
        $('#permohonan-bpb-action-modal').modal('show');
    }

    /* ======================================================================
       MODAL 2: FEE SALES
       ====================================================================== */
    async function openFeeSalesModal(item) {
        const idPengajuan = (item.modal_payload && item.modal_payload.id_pengajuan) || item.id;
        $('#permohonan-fs-title').text(item.nomor_dokumen || 'Detail Pengajuan Fee Sales');
        $('#permohonan-fs-subtitle').text('Memuat detail pengajuan...');
        $('#permohonan-fs-body').html('<div class="text-center py-3"><span class="spinner-border text-primary"></span></div>');
        $('#permohonan-fs-actions').html('<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>');
        $('#permohonan-fee-sales-modal').modal('show');

        try {
            const res = await apiGet(`api/sales-external/group-detail/${idPengajuan}`);
            const g = res.data || res;
            currentFeeSales = g;
            renderFeeSalesDetail(g);
        } catch (err) {
            $('#permohonan-fs-body').html(`<div class="alert alert-danger">${esc(err.message)}</div>`);
        }
    }

    function feeSalesStatusMeta(status) {
        if (status === 'void') return { variant: 'secondary', label: 'Void / Dibatalkan' };
        if (status === 'ditolak') return { variant: 'danger', label: 'Ditolak' };
        if (status === 'selesai') return { variant: 'success', label: 'Selesai / Cair' };
        if (status === 'cair_sebagian') return { variant: 'primary', label: 'Cair Sebagian' };
        return { variant: 'warning', label: 'Diajukan' };
    }

    function renderFeeSalesDetail(g) {
        const st = feeSalesStatusMeta(g.status);
        $('#permohonan-fs-title').text(g.nomor_pengajuan || ('Pengajuan #' + g.id));
        $('#permohonan-fs-subtitle').text(`Tanggal Pengajuan: ${formatDate(g.tanggal_pengajuan)} · Total: ${formatRupiah(g.total_nominal)}`);
        $('#permohonan-fs-status').attr('class', `permohonan-status-badge permohonan-status--${st.variant}`).text(st.label);

        const items = Array.isArray(g.items) ? g.items : [];
        let uncairCount = 0;
        let cairCount = 0;
        items.forEach(it => {
            if (it.termin_status === 'sudah_cair') cairCount++;
            else uncairCount++;
        });

        const alertHtml = g.status === 'void'
            ? `<div class="alert alert-secondary mb-1"><strong>Dibatalkan (Void):</strong> ${esc(g.void_reason || '-')}</div>`
            : (g.status === 'ditolak'
                ? `<div class="alert alert-danger mb-1">
                    <strong>Ditolak (${esc((g.rejected_role || 'Reviewer').toUpperCase())}):</strong> ${esc(g.reject_reason || '-')}
                    <div class="mt-50"><button type="button" class="btn btn-sm btn-warning" id="permohonan-fs-btn-recalc"><i class="fas fa-sync-alt mr-25"></i>Terapkan Saran Harga &amp; Hitung Ulang</button></div>
                  </div>`
                : '');

        const sigCards = [
            {
                title: 'Pembuat Pengajuan',
                has: !!g.ttd_pembuat_path,
                img: `${baseUrl}/fee-sales/pengajuan-signature/${g.id}/pembuat`,
                name: g.ttd_pembuat_nama || g.creator_name || '-',
                at: g.ttd_pembuat_at || g.tanggal_pengajuan,
            },
            {
                title: 'TTD MKDT',
                has: !!(g.ttd_mkdt_by || g.ttd_mkdt_id),
                img: `${baseUrl}/fee-sales/pengajuan-signature/${g.id}/mkdt`,
                name: g.ttd_mkdt_nama || '-',
                at: g.ttd_mkdt_at,
            },
            {
                title: 'TTD Keuangan',
                has: !!(g.ttd_keuangan_by || g.ttd_keuangan_id),
                img: `${baseUrl}/fee-sales/pengajuan-signature/${g.id}/keuangan`,
                name: g.ttd_keuangan_nama || '-',
                at: g.ttd_keuangan_at,
            },
        ].map(card => `<div class="col-md-4 mb-1"><div class="permohonan-signature-card">
            <div class="d-flex justify-content-between align-items-center">
                <strong class="small">${esc(card.title)}</strong>
                <span class="badge ${card.has ? 'badge-light-success' : 'badge-light-secondary'}">${card.has ? 'Sudah TTD' : 'Belum TTD'}</span>
            </div>
            <div class="permohonan-signature-preview">
                ${card.has ? `<img src="${esc(card.img)}" alt="${esc(card.title)}" onerror="this.style.display='none'">` : '<span class="text-muted small">Belum ditandatangani</span>'}
            </div>
            <div class="font-weight-bold small">${esc(card.name)}</div>
            <small class="text-muted">${esc(card.at ? formatDateTime(card.at) : '-')}</small>
        </div></div>`).join('');

        const rowsHtml = items.map((it, idx) => {
            const sppUrl = sanitizeUrl(it.termin_lampiran_spp_access_url || it.lampiran_spp_access_url || it.termin_lampiran_spp);
            const cairUrl = sanitizeUrl(it.termin_lampiran_bukti_cair_access_url || it.lampiran_bukti_cair_access_url || it.termin_lampiran_bukti_cair);
            const links = [
                sppUrl ? `<a href="${esc(sppUrl)}" target="_blank" rel="noopener" class="badge badge-light-primary mr-25">SPP</a>` : '',
                cairUrl ? `<a href="${esc(cairUrl)}" target="_blank" rel="noopener" class="badge badge-light-success">Bukti Cair</a>` : '',
            ].filter(Boolean).join('') || '<span class="text-muted">-</span>';

            return `<tr>
                <td>${idx + 1}</td>
                <td><strong>${esc(it.sales_nama || '-')}</strong></td>
                <td><strong>${esc(it.nama_konsumen || '-')}</strong><div class="small text-muted">${esc(it.nama_jalan || '-')} No. ${esc(it.no_kavling || '-')}</div></td>
                <td class="text-right">${formatRupiah(it.harga_net_ajb || it.fee_harga_net_ajb || 0)}</td>
                <td class="text-center">Termin ${esc(it.termin_yang_diajukan || '-')} (${esc(it.termin_persen || 0)}%)</td>
                <td class="text-right font-weight-bold">${formatRupiah(it.nominal_diajukan || 0)}</td>
                <td>${it.termin_status === 'sudah_cair' ? '<span class="badge badge-light-success">Sudah Cair</span>' : '<span class="badge badge-light-warning">Diajukan</span>'}</td>
                <td>${links}</td>
            </tr>`;
        }).join('') || '<tr><td colspan="8" class="text-center text-muted">Tidak ada item.</td></tr>';

        $('#permohonan-fs-body').html(`
            ${alertHtml}
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Nomor Pengajuan</span><span class="info-value permohonan-mono">${esc(g.nomor_pengajuan || '-')}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Total Unit</span><span class="info-value">${esc(g.total_unit || 0)} Unit / ${items.length} Termin</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Total Nominal</span><span class="info-value text-primary">${esc(formatRupiah(g.total_nominal))}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Catatan</span><span class="info-value">${esc(g.catatan || '-')}</span></div></div>
                </div>
            </div></div>
            <div class="card"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Status Tanda Tangan</div></div>
                <div class="row">${sigCards}</div>
            </div></div>
            <div class="card mb-0"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Daftar Unit &amp; Termin Diajukan</div></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>No</th><th>Sales</th><th>Konsumen &amp; Kavling</th><th class="text-right">Harga Net AJB</th><th class="text-center">Termin</th><th class="text-right">Nominal Diajukan</th><th>Status</th><th>Lampiran</th></tr></thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            </div></div>
        `);

        const hasMkdt = !!(g.ttd_mkdt_by || g.ttd_mkdt_id);
        const hasKeu = !!(g.ttd_keuangan_by || g.ttd_keuangan_id);
        const hasAnySig = hasMkdt || hasKeu;
        const canRejectBase = g.status !== 'void' && g.status !== 'ditolak' && cairCount === 0 && !hasAnySig;

        const btns = [
            `<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>`,
            `<a class="btn btn-outline-primary" href="${esc(baseUrl + '/fee-sales/pengajuan-pdf/' + g.id)}" target="_blank" rel="noopener"><i class="fas fa-file-pdf mr-50"></i>PDF</a>`,
        ];
        if (g.status !== 'void' && g.status !== 'ditolak' && !hasMkdt && perms.can_sign_mkdt) {
            btns.push(`<button type="button" class="btn btn-primary permohonan-fs-btn-sign" data-role="mkdt"><i class="fas fa-signature mr-50"></i>TTD MKDT</button>`);
        }
        if (g.status !== 'void' && g.status !== 'ditolak' && !hasKeu && perms.can_sign_keuangan) {
            btns.push(`<button type="button" class="btn btn-primary permohonan-fs-btn-sign" data-role="keuangan"><i class="fas fa-signature mr-50"></i>TTD Keuangan</button>`);
        }
        if (canRejectBase && perms.can_sign_mkdt) {
            btns.push(`<button type="button" class="btn btn-outline-danger permohonan-fs-btn-reject" data-role="mkdt">Tolak (MKDT)</button>`);
        }
        if (canRejectBase && perms.can_sign_keuangan) {
            btns.push(`<button type="button" class="btn btn-outline-danger permohonan-fs-btn-reject" data-role="keuangan">Tolak (Keuangan)</button>`);
        }
        if (g.status !== 'void' && g.status !== 'ditolak' && uncairCount > 0 && perms.can_cairkan_fee_sales) {
            btns.push(`<button type="button" class="btn btn-success" id="permohonan-fs-btn-cair"><i class="fas fa-check-circle mr-50"></i>Konfirmasi Cair Massal</button>`);
        }
        if (g.status !== 'void' && cairCount === 0 && (perms.is_admin || perms.is_mkdt || Number(g.created_by) === Number(config.userId))) {
            btns.push(`<button type="button" class="btn btn-outline-danger" id="permohonan-fs-btn-void">Void Pengajuan</button>`);
        }
        $('#permohonan-fs-actions').html(btns.join(''));
    }

    /* ======================================================================
       MODAL 3: REQUEST KAVLING SITEPLAN
       ====================================================================== */
    async function loadActiveTipesList() {
        if (Array.isArray(kavlingTipesCache)) return kavlingTipesCache;
        const res = await apiGet('api/tipe/all', { only_active: 1, format: 'raw' });
        kavlingTipesCache = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []);
        return kavlingTipesCache;
    }

    async function openKavlingRequestModal(item) {
        const payload = item.modal_payload || {};
        const idRequest = payload.id_request || item.id;
        const idProyek = payload.id_proyek || item.id_proyek;

        $('#permohonan-kr-title').text(item.nomor_dokumen || ('REQ-KVL-' + idRequest));
        $('#permohonan-kr-subtitle').text('Memuat detail request kavling...');
        $('#permohonan-kr-body').html('<div class="text-center py-3"><span class="spinner-border text-primary"></span></div>');
        $('#permohonan-kr-actions').html('<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>');
        $('#permohonan-kavling-modal').modal('show');

        try {
            let found = null;
            if (idProyek) {
                const res = await apiPost('api/kavling-request/list', {
                    id_proyek: idProyek,
                    status: 'all',
                    per_page: 100,
                });
                const list = Array.isArray(res.data) ? res.data : [];
                found = list.find(r => Number(r.id_request) === Number(idRequest)) || null;
            }
            currentKavlingReq = found || {
                id_request: idRequest,
                id_proyek: idProyek,
                jenis_request: payload.jenis_request || 'tambah_baru',
                status: item.status || 'pending',
                keterangan: item.subjudul || '-',
                pengaju_name: item.pemohon_nama || '-',
                created_at: item.created_at || '',
                kavlings: [],
                can_cancel: false,
            };
            await renderKavlingRequestDetail(currentKavlingReq, item);
        } catch (err) {
            $('#permohonan-kr-body').html(`<div class="alert alert-danger">${esc(err.message)}</div>`);
        }
    }

    async function renderKavlingRequestDetail(req, dto) {
        const statusMap = {
            pending: { variant: 'warning', label: 'Menunggu Planning' },
            approved: { variant: 'success', label: 'Disetujui' },
            rejected: { variant: 'danger', label: 'Ditolak' },
            cancelled: { variant: 'secondary', label: 'Dibatalkan' },
        };
        const st = statusMap[req.status] || { variant: 'secondary', label: req.status || '-' };
        const jenisLabel = req.jenis_request === 'ubah_tipe' ? 'Ubah Tipe Kavling' : 'Tambah Kavling Baru';

        $('#permohonan-kr-title').text(`REQ-KVL-${String(req.id_request).padStart(4, '0')}`);
        $('#permohonan-kr-subtitle').text(`${jenisLabel} · ${(dto && dto.nama_proyek) || req.nama_proyek || '-'}`);
        $('#permohonan-kr-status').attr('class', `permohonan-status-badge permohonan-status--${st.variant}`).text(st.label);

        const kavlings = Array.isArray(req.kavlings) ? req.kavlings : [];
        const kavHtml = kavlings.length
            ? kavlings.map(k => {
                const label = `${k.nama_jalan ? k.nama_jalan + ' ' : ''}No. ${k.no_kavling || k.id_kavling}`;
                const tipeAwal = k.old_tipe_label || k.current_tipe_label || '-';
                const konWarn = k.has_active_konsumen
                    ? `<span class="badge badge-light-warning ml-50">Konsumen: ${esc(k.nama_konsumen || 'Aktif')} (${esc(k.status_mkdt || '-')})</span>`
                    : '';
                return `<div class="info-row d-flex flex-wrap justify-content-between align-items-center">
                    <div><strong>${esc(label)}</strong> ${konWarn}</div>
                    <span class="small text-muted">Tipe Saat Ini: <strong>${esc(tipeAwal)}</strong></span>
                </div>`;
            }).join('')
            : `<div class="info-row"><span class="info-value">${esc((dto && dto.lokasi_kavling) || req.lokasi_label || 'Area Proyek')}</span></div>`;

        const tipeTujuan = req.requested_tipe
            ? `${req.requested_tipe.no_tipe_rumah || ''} ${req.requested_tipe.tipe_rumah || ''}`.trim()
            : (req.tipe_display || req.tipe_manual || '-');

        const canProcess = (perms.is_planning || perms.is_admin) && req.status === 'pending';
        let processHtml = '';
        if (canProcess) {
            let tipeSelectHtml = '';
            if (req.jenis_request === 'ubah_tipe') {
                const tipes = await loadActiveTipesList();
                const requestedTipeId = req.requested_tipe && req.requested_tipe.id_tipe
                    ? String(req.requested_tipe.id_tipe)
                    : (req.id_tipe ? String(req.id_tipe) : '');
                const optionsHtml = ['<option value="">-- Pilih Tipe Tujuan --</option>'].concat(
                    tipes.map(t => {
                        const tid = String(t.id_tipe || t.id);
                        const lbl = `${t.no_tipe_rumah || ''} ${t.tipe_rumah || ''} (${t.lb || 0}/${t.lt || 0})`.trim();
                        return `<option value="${esc(tid)}" ${tid === requestedTipeId ? 'selected' : ''}>${esc(lbl)}</option>`;
                    })
                ).join('');
                tipeSelectHtml = `<div class="border rounded p-1 mb-1 bg-light">
                    <div class="font-weight-bold small mb-50 text-primary"><i class="fas fa-exchange-alt mr-25"></i>Terapkan Perubahan Tipe Kavling Langsung</div>
                    <div class="form-row align-items-end">
                        <div class="col-md-8 form-group mb-md-0">
                            <label for="permohonan-kr-id-tipe">Tipe Rumah Baru</label>
                            <select id="permohonan-kr-id-tipe" class="form-control">${optionsHtml}</select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary btn-block" id="permohonan-kr-btn-apply">
                                <i class="fas fa-check-double mr-50"></i>Terapkan Ubah Tipe
                            </button>
                        </div>
                    </div>
                </div>`;
            }

            processHtml = `<div class="card mb-0"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Respon Tim Planning</div></div>
                ${tipeSelectHtml}
                <div class="form-group mb-0">
                    <label for="permohonan-kr-catatan">Catatan Planning (Wajib diisi jika menolak)</label>
                    <textarea id="permohonan-kr-catatan" class="form-control" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan penolakan..."></textarea>
                </div>
            </div></div>`;
        }

        $('#permohonan-kr-body').html(`
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Jenis Request</span><span class="info-value">${esc(jenisLabel)}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Pemohon</span><span class="info-value">${esc(req.pengaju_name || req.pengaju_username || '-')}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Tipe Tujuan</span><span class="info-value">${esc(tipeTujuan)}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Tanggal Diajukan</span><span class="info-value">${esc(formatDateTime(req.created_at))}</span></div></div>
                </div>
                <div class="info-row"><span class="info-label">Keterangan Pengaju</span><span class="info-value">${esc(req.keterangan || '-')}</span></div>
                ${req.catatan_planning ? `<div class="info-row mb-0"><span class="info-label">Catatan Planning</span><span class="info-value">${esc(req.catatan_planning)}</span></div>` : ''}
            </div></div>
            <div class="card ${canProcess ? '' : 'mb-0'}"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Daftar Kavling / Lokasi</div></div>
                ${kavHtml}
            </div></div>
            ${processHtml}
        `);

        if ($('#permohonan-kr-id-tipe').length && $.fn.select2) {
            $('#permohonan-kr-id-tipe').select2({
                width: '100%',
                dropdownParent: $('#permohonan-kavling-modal'),
            });
        }

        const siteplanHref = sanitizeUrl((dto && dto.spa_route) || `siteplan/view?id_proyek=${req.id_proyek || ''}`);
        const btns = [
            `<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>`,
            `<a class="btn btn-outline-primary" href="${esc(siteplanHref)}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-50"></i>Buka Siteplan</a>`,
        ];
        if (canProcess) {
            btns.push(`<button type="button" class="btn btn-outline-danger" id="permohonan-kr-btn-reject"><i class="fas fa-times mr-50"></i>Tolak</button>`);
            btns.push(`<button type="button" class="btn btn-success" id="permohonan-kr-btn-approve"><i class="fas fa-check mr-50"></i>Setujui (Approved)</button>`);
        }
        if (req.can_cancel) {
            btns.push(`<button type="button" class="btn btn-outline-danger" id="permohonan-kr-btn-cancel">Batalkan Request</button>`);
        }
        $('#permohonan-kr-actions').html(btns.join(''));
    }

    /* ======================================================================
       MODAL 4: TAGIHAN DIREKSI
       ====================================================================== */
    async function openTagihanDireksiModal(item) {
        const payload = item.modal_payload || {};
        const noInv = payload.no_inv || item.id;

        $('#permohonan-td-title').text(item.nomor_dokumen || noInv);
        $('#permohonan-td-subtitle').text('Memuat detail surat tagihan...');
        $('#permohonan-td-body').html('<div class="text-center py-3"><span class="spinner-border text-primary"></span></div>');
        $('#permohonan-td-actions').html('<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>');
        $('#permohonan-tagihan-direksi-modal').modal('show');

        try {
            let row = null;
            const pendingRes = await apiGet('direksi/get_pending_tagihan');
            const pendingList = Array.isArray(pendingRes.data) ? pendingRes.data : [];
            row = pendingList.find(r => String(r.no_inv) === String(noInv)) || null;

            if (!row) {
                const histRes = await apiGet('direksi/get_history_tagihan');
                const histList = Array.isArray(histRes.data) ? histRes.data : [];
                row = histList.find(r => String(r.no_inv) === String(noInv)) || null;
            }

            if (!row) {
                row = {
                    no_inv: noInv,
                    nomor_surat: payload.nomor_surat || item.nomor_dokumen,
                    nama_konsumen: item.judul,
                    nama_proyek: item.nama_proyek,
                    total_nominal: item.nominal || 0,
                    pembuat: item.pemohon_nama || '-',
                    is_signed_direktur: item.status === 'approved' ? 1 : (item.status === 'rejected' ? 2 : 0),
                };
            }

            currentTagihan = row;
            await renderTagihanDireksiDetail(row);
        } catch (err) {
            $('#permohonan-td-body').html(`<div class="alert alert-danger">${esc(err.message)}</div>`);
        }
    }

    async function renderTagihanDireksiDetail(row) {
        const signed = Number(row.is_signed_direktur || 0);
        const st = signed === 1
            ? { variant: 'success', label: 'Disetujui Direksi' }
            : (signed === 2 ? { variant: 'danger', label: 'Ditolak Direksi' } : { variant: 'warning', label: 'Menunggu TTD Direksi' });

        $('#permohonan-td-title').text(row.nomor_surat || row.no_inv);
        $('#permohonan-td-subtitle').text(`Invoice ${row.no_inv} · ${row.nama_konsumen || '-'}`);
        $('#permohonan-td-status').attr('class', `permohonan-status-badge permohonan-status--${st.variant}`).text(st.label);

        let tagihanItems = [];
        if (row.tagihan) {
            try {
                const decoded = typeof row.tagihan === 'string'
                    ? JSON.parse(row.tagihan.replace(/&quot;/g, '"'))
                    : row.tagihan;
                if (Array.isArray(decoded)) tagihanItems = decoded;
            } catch (_) {}
        }

        const itemRows = tagihanItems.map((t, i) => `<tr>
            <td>${i + 1}</td>
            <td>${esc(t.keterangan || t.deskripsi || t.item || '-')}</td>
            <td class="text-right font-weight-bold">${formatRupiah(t.nominal || 0)}</td>
        </tr>`).join('') || '<tr><td colspan="3" class="text-center text-muted">Lihat rincian lengkap pada berkas PDF.</td></tr>';

        const canSign = signed === 0 && (perms.is_direksi || perms.is_admin);
        let signFormHtml = '';
        if (canSign) {
            signFormHtml = `<div class="card mb-0"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Tanda Tangan / Penolakan Direksi</div></div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="permohonan-td-sign-method">Metode Tanda Tangan</label>
                        <select id="permohonan-td-sign-method" class="form-control">
                            <option value="profile">Gunakan TTD Profil</option>
                            <option value="canvas">Gambar di Kanvas</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="permohonan-td-password">Password Akun Anda <span class="text-danger">*</span></label>
                        <input type="password" id="permohonan-td-password" class="form-control" autocomplete="current-password" placeholder="Masukkan password login">
                    </div>
                </div>
                <div id="permohonan-td-profile-wrap" class="border rounded p-1 text-center bg-light mb-1">
                    <span class="spinner-border spinner-border-sm text-primary"></span> Memeriksa tanda tangan profil...
                </div>
                <div id="permohonan-td-canvas-wrap" class="d-none mb-1">
                    <label>Area Tanda Tangan Kanvas</label>
                    <div class="permohonan-canvas-wrap">
                        <canvas id="permohonan-td-canvas"></canvas>
                        <button type="button" class="btn btn-sm btn-outline-secondary permohonan-canvas-clear" data-clear-canvas="td">Hapus</button>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="permohonan-td-reject-alasan">Alasan Penolakan (Isi jika ingin menolak surat)</label>
                    <textarea id="permohonan-td-reject-alasan" class="form-control" rows="2" placeholder="Wajib diisi jika menekan tombol Tolak Surat"></textarea>
                </div>
            </div></div>`;
        }

        const pdfUrl = apiUrl(`keuangan/download_penagihan?id=${encodeURIComponent(row.no_inv)}`);
        $('#permohonan-td-body').html(`
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Nomor Surat</span><span class="info-value permohonan-mono">${esc(row.nomor_surat || '-')}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">No. Invoice</span><span class="info-value permohonan-mono">${esc(row.no_inv || '-')}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Konsumen</span><span class="info-value">${esc(row.nama_konsumen || '-')}</span></div></div>
                    <div class="col-md-3"><div class="info-row"><span class="info-label">Total Tagihan</span><span class="info-value text-primary">${esc(formatRupiah(row.total_nominal || 0))}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Proyek &amp; Kavling</span><span class="info-value">${esc(row.nama_proyek || '-')}${row.no_kavling ? ` · ${esc(row.nama_jalan || '')} No. ${esc(row.no_kavling)}` : ''}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Pembuat</span><span class="info-value">${esc(row.pembuat || '-')}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Jatuh Tempo</span><span class="info-value">${esc(formatDate(row.tanggal_jatuh_tempo))}</span></div></div>
                </div>
                ${row.catatan ? `<div class="info-row mb-0"><span class="info-label">Catatan</span><span class="info-value">${esc(row.catatan)}</span></div>` : ''}
            </div></div>
            <div class="card ${canSign ? '' : 'mb-0'}"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Rincian Item Tagihan</div></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>No</th><th>Keterangan</th><th class="text-right">Nominal</th></tr></thead>
                        <tbody>${itemRows}</tbody>
                    </table>
                </div>
            </div></div>
            ${signFormHtml}
        `);

        const btns = [
            `<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>`,
            `<a class="btn btn-outline-primary" href="${esc(pdfUrl)}" target="_blank" rel="noopener"><i class="fas fa-file-pdf mr-50"></i>Preview / Download PDF</a>`,
        ];
        if (canSign) {
            btns.push(`<button type="button" class="btn btn-outline-danger" id="permohonan-td-btn-reject"><i class="fas fa-ban mr-50"></i>Tolak Surat</button>`);
            btns.push(`<button type="button" class="btn btn-primary" id="permohonan-td-btn-sign"><i class="fas fa-check-circle mr-50"></i>Setujui &amp; Tanda Tangani</button>`);
        }
        $('#permohonan-td-actions').html(btns.join(''));

        if (canSign) {
            initCanvas('td', 'permohonan-td-canvas');
            try {
                const sigRes = await apiGet('direksi/get_my_signature');
                const prof = sigRes.data || {};
                tagihanHasProfileSig = !!prof.has_signature;
                if (tagihanHasProfileSig) {
                    const sigImgUrl = sanitizeUrl(prof.image_url || 'api/profile/signature/image');
                    $('#permohonan-td-profile-wrap').html(
                        `<img src="${esc(sigImgUrl)}?v=${Date.now()}" alt="TTD Profil" style="max-height:90px;">` +
                        `<div class="small text-success font-weight-bold mt-25">Tanda tangan profil siap digunakan</div>`
                    );
                } else {
                    $('#permohonan-td-profile-wrap').html('<div class="text-warning small">Anda belum memiliki TTD di profil. Dialihkan ke mode Kanvas.</div>');
                    $('#permohonan-td-sign-method').val('canvas').trigger('change');
                }
            } catch (_) {
                tagihanHasProfileSig = false;
                $('#permohonan-td-profile-wrap').html('<div class="text-danger small">Gagal memeriksa TTD profil. Silakan gunakan Kanvas.</div>');
                $('#permohonan-td-sign-method').val('canvas').trigger('change');
            }
        }
    }

    /* ======================================================================
       MODAL 5: CASHOUT SUBKON (REUSE EXISTING PARTIAL)
       ====================================================================== */
    function openCashoutSubkonModal(item) {
        const payload = item.modal_payload || {};
        if (typeof dt_proyek === 'object') {
            dt_proyek.id_proyek = payload.id_proyek || item.id_proyek || '';
        }
        if (typeof window.openCOSubkon === 'function') {
            window.openCOSubkon(payload);
        } else {
            showErrorAlert('Komponen modal Cashout Subkon belum siap.');
        }
    }

    /* ======================================================================
       MODAL 6: MGM REFERRAL
       ====================================================================== */
    async function openMgmModal(item) {
        const payload = item.modal_payload || {};
        const idProyek = payload.id_proyek || item.id_proyek || (window.SIGAPP && window.SIGAPP.activeProyekId) || 0;
        const idReferral = payload.id_referral;
        const idBonus = payload.id_bonus || item.id;

        $('#permohonan-mgm-title').text(item.nomor_dokumen || 'Detail Bonus MGM');
        $('#permohonan-mgm-subtitle').text(item.judul || 'Memuat data referral...');
        $('#permohonan-mgm-body').html('<div class="text-center py-3"><span class="spinner-border text-primary"></span></div>');
        $('#permohonan-mgm-modal').modal('show');

        try {
            let matchedStages = [];
            let matchedReferrerId = null;

            if (idProyek) {
                const listRes = await apiPost('api/mgm/list', { id_proyek: idProyek });
                const referrers = Array.isArray(listRes.data) ? listRes.data : [];
                const titleParts = String(item.judul || '').split(' - ');
                const expectedReferrer = (titleParts.length > 1 ? titleParts.slice(1).join(' - ') : '').trim().toLowerCase();

                const sortedReferrers = [...referrers].sort((a, b) => {
                    const aMatch = String(a.referrer_nama || '').trim().toLowerCase() === expectedReferrer ? 0 : 1;
                    const bMatch = String(b.referrer_nama || '').trim().toLowerCase() === expectedReferrer ? 0 : 1;
                    return aMatch - bMatch;
                });

                for (const ref of sortedReferrers) {
                    const subRes = await apiPost('api/mgm/subrows', {
                        id_konsumen_referrer: ref.id_konsumen_referrer,
                        id_proyek: idProyek,
                    });
                    const subRows = Array.isArray(subRes.data) ? subRes.data : [];
                    const hitRows = subRows.filter(r =>
                        (idReferral && String(r.id_referral) === String(idReferral)) ||
                        (idBonus && String(r.id_bonus) === String(idBonus))
                    );
                    if (hitRows.length) {
                        matchedStages = hitRows;
                        matchedReferrerId = ref.id_konsumen_referrer;
                        break;
                    }
                }
            }

            currentMgmContext = {
                item,
                idProyek,
                idReferral,
                idBonus,
                idKonsumenReferrer: matchedReferrerId,
            };
            currentMgmStages = matchedStages;
            currentMgmSelectedStage = matchedStages.find(s => String(s.id_bonus) === String(idBonus)) || matchedStages[0] || null;
            renderMgmDetail();
        } catch (err) {
            $('#permohonan-mgm-body').html(`<div class="alert alert-danger">${esc(err.message)}</div>`);
        }
    }

    function mgmStatusMeta(status) {
        const map = {
            eligible: { variant: 'warning', label: 'Eligible (Menunggu Konfirmasi)' },
            dikonfirmasi: { variant: 'warning', label: 'Dikonfirmasi (Belum Diajukan)' },
            diajukan_keuangan: { variant: 'primary', label: 'Diajukan ke Keuangan' },
            dibayar_promosi: { variant: 'primary', label: 'Dibayar Promosi' },
            cair: { variant: 'primary', label: 'Cair Keuangan (Siap Bayar Member)' },
            selesai: { variant: 'success', label: 'Selesai' },
            batal: { variant: 'danger', label: 'Dibatalkan' },
        };
        return map[status] || { variant: 'secondary', label: status || '-' };
    }

    function renderMgmDetail() {
        const ctx = currentMgmContext || {};
        const item = ctx.item || {};
        const stage = currentMgmSelectedStage;
        const statusKey = (stage && stage.bonus_status) || item.status || 'eligible';
        const st = mgmStatusMeta(statusKey);

        $('#permohonan-mgm-title').text(item.nomor_dokumen || ('MGM-' + (ctx.idBonus || '')));
        $('#permohonan-mgm-subtitle').text(`${item.nama_proyek || '-'} · ${item.subjudul || ''}`);
        $('#permohonan-mgm-status').attr('class', `permohonan-status-badge permohonan-status--${st.variant}`).text(st.label);

        const canPromosi = !!(perms.is_promosi || perms.is_admin);
        const canKeuangan = !!(perms.is_keuangan || perms.is_admin);

        const stagesHtml = currentMgmStages.map((s, idx) => {
            const isFocus = String(s.id_bonus) === String(ctx.idBonus);
            const sMeta = mgmStatusMeta(s.bonus_status);
            const attachments = [
                s.bukti_pengajuan_keuangan_url ? `<a href="${esc(sanitizeUrl(s.bukti_pengajuan_keuangan_url))}" target="_blank" rel="noopener" class="badge badge-light-primary mr-25">Lampiran SPP</a>` : '',
                s.bukti_transfer_ke_promosi_url ? `<a href="${esc(sanitizeUrl(s.bukti_transfer_ke_promosi_url))}" target="_blank" rel="noopener" class="badge badge-light-info mr-25">Bukti Cair Keuangan</a>` : '',
                s.bukti_bayar_promosi_url ? `<a href="${esc(sanitizeUrl(s.bukti_bayar_promosi_url))}" target="_blank" rel="noopener" class="badge badge-light-success">Bukti Bayar Member</a>` : '',
            ].filter(Boolean).join('') || '<span class="text-muted small">-</span>';

            const btns = [];
            if (s.id_bonus) {
                if (canPromosi && s.bonus_status === 'eligible') {
                    btns.push(`<button type="button" class="btn btn-sm btn-outline-primary permohonan-mgm-btn-confirm" data-stage-index="${idx}">Konfirmasi Bonus</button>`);
                }
                if (canPromosi && ['eligible', 'dikonfirmasi', 'dibayar_promosi'].includes(s.bonus_status)) {
                    btns.push(`<button type="button" class="btn btn-sm btn-primary permohonan-mgm-btn-form" data-stage-index="${idx}" data-mgm-action="submit_keuangan">Ajukan SPP</button>`);
                }
                if (canKeuangan && s.bonus_status === 'diajukan_keuangan') {
                    btns.push(`<button type="button" class="btn btn-sm btn-primary permohonan-mgm-btn-form" data-stage-index="${idx}" data-mgm-action="mark_cair_keuangan">Cairkan Keuangan</button>`);
                }
                if (canPromosi && ['diajukan_keuangan', 'cair'].includes(s.bonus_status) && Number(s.paid_by_promosi || 0) !== 1) {
                    btns.push(`<button type="button" class="btn btn-sm btn-success permohonan-mgm-btn-form" data-stage-index="${idx}" data-mgm-action="pay_promosi">Bayar ke Member</button>`);
                }
            }

            return `<tr class="${isFocus ? 'table-active' : ''}">
                <td><strong>${esc(s.nama_tahapan || '-')}</strong></td>
                <td class="text-right font-weight-bold">${formatRupiah(s.nominal_bonus || 0)}</td>
                <td>${statusBadgeHtml(sMeta.variant, sMeta.label)}</td>
                <td>${attachments}</td>
                <td class="text-nowrap">${btns.join(' ') || '<span class="text-muted small">-</span>'}</td>
            </tr>`;
        }).join('') || `<tr><td colspan="5" class="text-center text-muted">Tahapan bonus untuk referral ini belum tersedia atau berada di filter lain.</td></tr>`;

        $('#permohonan-mgm-body').html(`
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Judul Bonus</span><span class="info-value">${esc(item.judul || '-')}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Member Referred &amp; Lokasi</span><span class="info-value">${esc((stage && stage.referred_nama) || item.subjudul || '-')}</span></div></div>
                    <div class="col-md-4"><div class="info-row"><span class="info-label">Nominal Fokus</span><span class="info-value text-primary">${esc(formatRupiah((stage && stage.nominal_bonus) || item.nominal || 0))}</span></div></div>
                </div>
            </div></div>
            <div class="card"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text">Tahapan Bonus Referral</div></div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Tahapan</th><th class="text-right">Nominal Bonus</th><th>Status</th><th>Lampiran</th><th>Aksi</th></tr></thead>
                        <tbody>${stagesHtml}</tbody>
                    </table>
                </div>
            </div></div>
            <div class="card mb-0 d-none" id="permohonan-mgm-form-card"><div class="card-body">
                <div class="divider divider-left"><div class="divider-text" id="permohonan-mgm-form-title">Form Respon MGM</div></div>
                <form id="permohonan-mgm-action-form" enctype="multipart/form-data">
                    <input type="hidden" id="permohonan-mgm-id-bonus" name="id_bonus">
                    <div id="permohonan-mgm-form-fields"></div>
                    <div class="d-flex justify-content-end mt-1" style="gap:.5rem;">
                        <button type="button" class="btn btn-outline-secondary" id="permohonan-mgm-form-cancel">Batal</button>
                        <button type="submit" class="btn btn-primary" id="permohonan-mgm-form-submit">Simpan</button>
                    </div>
                </form>
            </div></div>
        `);
    }

    function openMgmActionForm(stageIndex, action) {
        const stage = currentMgmStages[stageIndex];
        if (!stage) return;
        currentMgmSelectedStage = stage;
        currentMgmAction = action;
        const today = new Date().toISOString().slice(0, 10);
        const nominal = Math.round(Number(stage.nominal_pengajuan_keuangan || stage.nominal_bonus || 0));

        let title = 'Respon Bonus MGM';
        let fields = '';
        if (action === 'submit_keuangan') {
            title = `Ajukan SPP ke Keuangan — ${stage.nama_tahapan || ''}`;
            fields = `<div class="form-row">
                <div class="col-md-6 form-group"><label>Nominal Pengajuan (Rp) <span class="text-danger">*</span></label><input type="number" name="nominal_pengajuan" class="form-control" value="${nominal}" required></div>
                <div class="col-md-6 form-group"><label>Tanggal SPP <span class="text-danger">*</span></label><input type="date" name="tanggal_spp" class="form-control" value="${today}" required></div>
            </div>`;
        } else if (action === 'mark_cair_keuangan') {
            title = `Cairkan Keuangan — ${stage.nama_tahapan || ''}`;
            fields = `<div class="form-row">
                <div class="col-md-6 form-group"><label>Nominal Cair (Rp) <span class="text-danger">*</span></label><input type="number" name="nominal_cair_keuangan" class="form-control" value="${nominal}" required></div>
                <div class="col-md-6 form-group"><label>Tanggal Cair Keuangan <span class="text-danger">*</span></label><input type="date" name="tanggal_cair_keuangan" class="form-control" value="${today}" required></div>
                <div class="col-md-4 form-group"><label>Nama Penerima <span class="text-danger">*</span></label><input type="text" name="nama_penerima" class="form-control" required></div>
                <div class="col-md-4 form-group"><label>No. Rekening <span class="text-danger">*</span></label><input type="text" name="no_rekening" class="form-control" required></div>
                <div class="col-md-4 form-group"><label>Bank Pencairan <span class="text-danger">*</span></label><input type="text" name="bank_pencairan" class="form-control" required></div>
            </div>`;
        } else if (action === 'pay_promosi') {
            title = `Bayar Bonus ke Member — ${stage.nama_tahapan || ''}`;
            fields = `<div class="form-row">
                <div class="col-md-6 form-group"><label>Tanggal Pembayaran <span class="text-danger">*</span></label><input type="date" name="tanggal_pembayaran" class="form-control" value="${today}" required></div>
                <div class="col-md-6 form-group"><label>Nama Penerima <span class="text-danger">*</span></label><input type="text" name="nama_penerima" class="form-control" required></div>
                <div class="col-md-6 form-group"><label>No. Rekening Penerima <span class="text-danger">*</span></label><input type="text" name="no_rekening_penerima" class="form-control" required></div>
                <div class="col-md-6 form-group"><label>Bank Penerima <span class="text-danger">*</span></label><input type="text" name="bank_penerima" class="form-control" required></div>
            </div>`;
        }

        fields += `<div class="form-group">
                <label>Lampiran Bukti (Foto / PDF) <span class="text-danger">*</span></label>
                <input type="file" name="bukti_bayar" id="permohonan-mgm-file" class="d-none" accept=".pdf,image/*">
                <div class="bpb-dropzone" id="permohonan-mgm-dropzone" tabindex="0">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <strong>Pilih atau tarik lampiran ke sini</strong>
                    <small id="permohonan-mgm-filename">JPG, PNG, WEBP, atau PDF</small>
                </div>
            </div>
            <div class="form-group mb-0"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2"></textarea></div>`;

        $('#permohonan-mgm-id-bonus').val(stage.id_bonus);
        $('#permohonan-mgm-form-title').text(title);
        $('#permohonan-mgm-form-fields').html(fields);
        $('#permohonan-mgm-form-card').removeClass('d-none');
    }

    /* ======================================================================
       MODAL 7: TIKET MASALAH (REUSE EXISTING PARTIAL)
       ====================================================================== */
    function openTiketMasalahModal(payload) {
        const idTiket = payload.id_tiket;
        const refType = payload.ref_type || 'kavling';
        const refId = payload.ref_id || 0;

        if (idTiket && typeof window.tm_open_detail === 'function') {
            window.tm_open_detail(idTiket, refType, refId);
        } else if (typeof window.openTiketMasalah === 'function') {
            window.openTiketMasalah(refType, refId);
        } else {
            showErrorAlert('Komponen modal Tiket Masalah belum siap.');
        }
    }

    /* ======================================================================
       EVENT BINDINGS
       ====================================================================== */
    $(function () {
        initCanvas('bpb', 'permohonan-bpb-canvas');
        initCanvas('fs', 'permohonan-fs-canvas');

        if ($.fn.select2) {
            $('#permohonan-filter-proyek').select2({
                width: '100%',
                placeholder: 'Semua Proyek',
            });
        }

        // Scope tabs
        $(document).on('click', '.permohonan-scope-card', function () {
            const sc = $(this).data('scope');
            if (!sc || sc === hubState.scope) return;
            hubState.scope = sc;
            loadList(1);
        });

        // Module pills
        $(document).on('click', '.permohonan-module-pill', function () {
            const mod = $(this).data('module');
            if (!mod || mod === hubState.module) return;
            hubState.module = mod;
            loadList(1);
        });

        // Filters
        $('#permohonan-filter-proyek').on('change', function () {
            hubState.id_proyek = $(this).val() || '';
            loadList(1);
        });

        $('#permohonan-filter-status').on('change', function () {
            hubState.status = $(this).val() || 'all';
            loadList(1);
        });

        $('#permohonan-filter-search').on('input', function () {
            const val = $(this).val().trim();
            $('#permohonan-search-clear').toggleClass('d-none', !val);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                hubState.search = val;
                loadList(1);
            }, 380);
        }).on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                hubState.search = $(this).val().trim();
                loadList(1);
            }
        });

        $('#permohonan-search-clear').on('click', function () {
            $('#permohonan-filter-search').val('');
            $(this).addClass('d-none');
            hubState.search = '';
            loadList(1);
        });

        $(document).on('click', '[data-clear-chip]', function () {
            const key = $(this).data('clear-chip');
            if (key === 'module') hubState.module = 'all';
            if (key === 'id_proyek') {
                hubState.id_proyek = '';
                $('#permohonan-filter-proyek').val('').trigger('change.select2');
            }
            if (key === 'status') {
                hubState.status = 'all';
                $('#permohonan-filter-status').val('all');
            }
            if (key === 'search') {
                hubState.search = '';
                $('#permohonan-filter-search').val('');
                $('#permohonan-search-clear').addClass('d-none');
            }
            loadList(1);
        });

        $(document).on('click', '#permohonan-clear-all-filters', function () {
            hubState.module = 'all';
            hubState.id_proyek = '';
            hubState.status = 'all';
            hubState.search = '';
            $('#permohonan-filter-proyek').val('').trigger('change.select2');
            $('#permohonan-filter-status').val('all');
            $('#permohonan-filter-search').val('');
            $('#permohonan-search-clear').addClass('d-none');
            loadList(1);
        });

        $('#permohonan-btn-refresh, #permohonan-btn-retry').on('click', () => loadList(hubState.page));

        // Pagination
        $(document).on('click', '#permohonan-pagination-controls [data-page]', function () {
            const p = Number($(this).data('page'));
            if (p > 0) loadList(p);
        });

        // Open response modal from row button
        $(document).on('click', '.permohonan-btn-open', function (e) {
            e.stopPropagation();
            const idx = Number($(this).data('item-index'));
            openResponseModal(hubState.items[idx]);
        });

        // Lightbox & canvas clear
        $(document).on('click', '.permohonan-lightbox-trigger', function () {
            openImageLightbox($(this).data('img-url'), $(this).data('img-name'));
        });
        $('#permohonan-lightbox-close, #permohonan-image-lightbox').on('click', function (e) {
            if (e.target === this || $(e.target).closest('#permohonan-lightbox-close').length) {
                closeImageLightbox();
            }
        });
        $(document).on('click', '[data-clear-canvas]', function () {
            const key = $(this).data('clear-canvas');
            if (canvases[key]) canvases[key].clear();
        });

        // ── BPB Actions ────────────────────────────────────────────────────
        $(document).on('click', '[data-permohonan-bpb-action]', function () {
            openBpbActionModal($(this).data('permohonan-bpb-action'));
        });
        $('#permohonan-bpb-action-modal').on('shown.bs.modal', function () {
            if (canvases.bpb) { canvases.bpb.resize(); canvases.bpb.clear(); }
        });
        $('#permohonan-bpb-sign-method').on('change', function () {
            $('#permohonan-bpb-canvas-wrap').toggleClass('d-none', this.value === 'profile');
            if (this.value === 'canvas' && canvases.bpb) setTimeout(() => canvases.bpb.resize(), 60);
        });
        $(document).on('change', '#permohonan-bpb-finish-status', function () {
            const isPur = this.value === 'purchased';
            $('#permohonan-bpb-finish-purchased').toggleClass('d-none', !isPur);
            $('#permohonan-bpb-finish-pending').toggleClass('d-none', isPur);
            $('#permohonan-bpb-action-signature').toggleClass('d-none', !isPur);
        });
        $(document).on('click', '#permohonan-bpb-purchase-dropzone', () => $('#permohonan-bpb-purchase-files').trigger('click'));
        $(document).on('change', '#permohonan-bpb-purchase-files', function () {
            const count = (this.files && this.files.length) || 0;
            $('#permohonan-bpb-purchase-files-label').text(count ? `${count} file dipilih` : 'JPG, PNG, WEBP, PDF (maks 5 file)');
        });
        $('#permohonan-bpb-action-form').on('submit', async function (e) {
            e.preventDefault();
            if (!currentBpb) return;
            const $btn = $('#permohonan-bpb-action-submit').prop('disabled', true);
            try {
                const fd = new FormData();
                let path = `api/bpb/${currentBpb.id}/${bpbActionMode}`;
                if (bpbActionMode === 'sign' || bpbActionMode === 'process') {
                    const method = $('#permohonan-bpb-sign-method').val();
                    fd.append('signature_method', method);
                    fd.append('signature_data', method === 'canvas' && canvases.bpb ? canvases.bpb.data() : '');
                    fd.append('password', $('#permohonan-bpb-password').val() || '');
                }
                if (bpbActionMode === 'process') {
                    path = `api/bpb/${currentBpb.id}/status`;
                    fd.append('status', 'processed');
                }
                if (bpbActionMode === 'reject' || bpbActionMode === 'cancel') {
                    fd.append('reason', $('#permohonan-bpb-reason').val() || '');
                }
                if (bpbActionMode === 'disburse') {
                    path = `api/bpb/${currentBpb.id}/status`;
                    fd.append('status', 'disbursed');
                    fd.append('actual_amount', $('#permohonan-bpb-actual-amount').val() || '');
                    fd.append('recipient_name', $('#permohonan-bpb-recipient').val() || '');
                    fd.append('account_number', $('#permohonan-bpb-account').val() || '');
                    fd.append('note', $('#permohonan-bpb-note').val() || '');
                }
                if (bpbActionMode === 'finish') {
                    const st = $('#permohonan-bpb-finish-status').val();
                    path = `api/bpb/${currentBpb.id}/status`;
                    fd.append('status', st);
                    if (st === 'purchased') {
                        fd.append('purchase_date', $('#permohonan-bpb-purchase-date').val() || '');
                        fd.append('note', $('#permohonan-bpb-note').val() || '');
                        const files = ($('#permohonan-bpb-purchase-files')[0] && $('#permohonan-bpb-purchase-files')[0].files) || [];
                        Array.from(files).forEach(f => fd.append('purchase_proofs[]', f));
                        const method = $('#permohonan-bpb-sign-method').val();
                        fd.append('signature_method', method);
                        fd.append('signature_data', method === 'canvas' && canvases.bpb ? canvases.bpb.data() : '');
                        fd.append('password', $('#permohonan-bpb-password').val() || '');
                    } else {
                        fd.append('reason', $('#permohonan-bpb-reason').val() || '');
                    }
                }
                await apiPost(path, fd);
                $('#permohonan-bpb-action-modal').modal('hide');
                $('#permohonan-bpb-modal').modal('hide');
                afterActionSuccess('Aksi BPB berhasil disimpan.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        // ── Fee Sales Actions ──────────────────────────────────────────────
        $(document).on('click', '.permohonan-fs-btn-sign', function () {
            if (!currentFeeSales) return;
            const role = $(this).data('role');
            $('#permohonan-fs-sign-form')[0].reset();
            $('#permohonan-fs-sign-id').val(currentFeeSales.id);
            $('#permohonan-fs-sign-role').val(role);
            $('#permohonan-fs-sign-title').text(`Tanda Tangan Pengajuan Fee (${String(role).toUpperCase()})`);
            $('#permohonan-fs-canvas-wrap').removeClass('d-none');
            $('#permohonan-fs-sign-modal').modal('show');
        });
        $('#permohonan-fs-sign-modal').on('shown.bs.modal', function () {
            if (canvases.fs) { canvases.fs.resize(); canvases.fs.clear(); }
        });
        $('#permohonan-fs-sign-method').on('change', function () {
            $('#permohonan-fs-canvas-wrap').toggleClass('d-none', this.value === 'profile');
            if (this.value === 'canvas' && canvases.fs) setTimeout(() => canvases.fs.resize(), 60);
        });
        $('#permohonan-fs-sign-form').on('submit', async function (e) {
            e.preventDefault();
            const method = $('#permohonan-fs-sign-method').val();
            const fd = new FormData(this);
            if (method === 'canvas') {
                const dataUrl = canvases.fs ? canvases.fs.data() : '';
                if (!dataUrl) { showErrorAlert('Silakan gambar tanda tangan pada kanvas terlebih dahulu.'); return; }
                fd.append('signature_data', dataUrl);
            }
            const $btn = $('#permohonan-fs-sign-submit').prop('disabled', true);
            try {
                await apiPost('api/sales-external/sign-group', fd);
                $('#permohonan-fs-sign-modal').modal('hide');
                $('#permohonan-fee-sales-modal').modal('hide');
                afterActionSuccess('Tanda tangan pengajuan Fee Sales berhasil disimpan.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '.permohonan-fs-btn-reject', function () {
            if (!currentFeeSales) return;
            const role = $(this).data('role');
            $('#permohonan-fs-reject-form')[0].reset();
            $('#permohonan-fs-reject-id').val(currentFeeSales.id);
            $('#permohonan-fs-reject-role').val(role);
            $('#permohonan-fs-reject-title').text(`Tolak Pengajuan Fee (${String(role).toUpperCase()})`);

            const units = {};
            (currentFeeSales.items || []).forEach(it => {
                if (it.id_mkdt && !units[it.id_mkdt]) units[it.id_mkdt] = it;
            });
            const unitHtml = Object.values(units).map(it => {
                const cur = Number(it.suggested_harga_net_ajb || it.harga_net_ajb || 0);
                return `<div class="border rounded p-75 mb-50 bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-7"><strong>${esc(it.nama_konsumen || '-')}</strong> · <small>${esc(it.nama_jalan || '')} No. ${esc(it.no_kavling || '')}</small></div>
                        <div class="col-md-5"><input type="number" name="unit_prices[${esc(it.id_mkdt)}]" class="form-control form-control-sm" value="${cur > 0 ? Math.round(cur) : ''}" placeholder="Saran Harga Net AJB"></div>
                    </div>
                </div>`;
            }).join('');
            $('#permohonan-fs-reject-units').html(unitHtml);
            $('#permohonan-fs-reject-modal').modal('show');
        });
        $('#permohonan-fs-reject-form').on('submit', async function (e) {
            e.preventDefault();
            const $btn = $('#permohonan-fs-reject-submit').prop('disabled', true);
            try {
                await apiPost('api/sales-external/reject-group', new FormData(this));
                $('#permohonan-fs-reject-modal').modal('hide');
                $('#permohonan-fee-sales-modal').modal('hide');
                afterActionSuccess('Pengajuan Fee Sales telah ditolak.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-fs-btn-cair', function () {
            if (!currentFeeSales) return;
            $('#permohonan-fs-cair-form')[0].reset();
            $('#permohonan-fs-cair-id').val(currentFeeSales.id);
            $('#permohonan-fs-cair-tanggal').val(new Date().toISOString().slice(0, 10));
            $('#permohonan-fs-cair-filename').text('PDF, JPG, PNG');
            $('#permohonan-fs-cair-modal').modal('show');
        });
        $('#permohonan-fs-cair-dropzone').on('click', () => $('#permohonan-fs-cair-file').trigger('click'));
        $('#permohonan-fs-cair-file').on('change', function () {
            const f = this.files && this.files[0];
            $('#permohonan-fs-cair-filename').text(f ? f.name : 'PDF, JPG, PNG');
        });
        $('#permohonan-fs-cair-form').on('submit', async function (e) {
            e.preventDefault();
            const $btn = $('#permohonan-fs-cair-submit').prop('disabled', true);
            try {
                await apiPost('api/sales-external/konfirmasi-cair-group', new FormData(this));
                $('#permohonan-fs-cair-modal').modal('hide');
                $('#permohonan-fee-sales-modal').modal('hide');
                afterActionSuccess('Pencairan massal Fee Sales berhasil dikonfirmasi.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-fs-btn-void', function () {
            if (!currentFeeSales) return;
            $('#permohonan-fs-void-form')[0].reset();
            $('#permohonan-fs-void-id').val(currentFeeSales.id);
            $('#permohonan-fs-void-modal').modal('show');
        });
        $('#permohonan-fs-void-form').on('submit', async function (e) {
            e.preventDefault();
            const $btn = $('#permohonan-fs-void-submit').prop('disabled', true);
            try {
                await apiPost('api/sales-external/void-group', new FormData(this));
                $('#permohonan-fs-void-modal').modal('hide');
                $('#permohonan-fee-sales-modal').modal('hide');
                afterActionSuccess('Pengajuan Fee Sales berhasil di-void.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-fs-btn-recalc', async function () {
            if (!currentFeeSales) return;
            try {
                await apiPost('api/sales-external/recalculate-termin', { id_pengajuan: currentFeeSales.id });
                $('#permohonan-fee-sales-modal').modal('hide');
                afterActionSuccess('Nominal termin berhasil dihitung ulang.');
            } catch (err) {
                showErrorAlert(err.message);
            }
        });

        // ── Kavling Request Actions ────────────────────────────────────────
        $(document).on('click', '#permohonan-kr-btn-approve, #permohonan-kr-btn-reject', async function () {
            if (!currentKavlingReq) return;
            const newStatus = this.id === 'permohonan-kr-btn-approve' ? 'approved' : 'rejected';
            const catatan = ($('#permohonan-kr-catatan').val() || '').trim();
            if (newStatus === 'rejected' && !catatan) {
                showErrorAlert('Catatan Planning wajib diisi saat menolak request kavling.');
                $('#permohonan-kr-catatan').focus();
                return;
            }
            const $btn = $(this).prop('disabled', true);
            try {
                await apiPost('api/kavling-request/update-status', {
                    id_request: currentKavlingReq.id_request,
                    status: newStatus,
                    catatan_planning: catatan,
                });
                $('#permohonan-kavling-modal').modal('hide');
                afterActionSuccess(newStatus === 'approved' ? 'Request kavling disetujui.' : 'Request kavling ditolak.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-kr-btn-apply', async function () {
            if (!currentKavlingReq) return;
            const idTipe = $('#permohonan-kr-id-tipe').val();
            if (!idTipe) {
                showErrorAlert('Pilih tipe rumah tujuan terlebih dahulu.');
                return;
            }
            const $btn = $(this).prop('disabled', true);
            try {
                await apiPost('api/kavling-request/apply-ubah-kavling', {
                    id_request: currentKavlingReq.id_request,
                    id_tipe: idTipe,
                    catatan_planning: ($('#permohonan-kr-catatan').val() || '').trim(),
                });
                $('#permohonan-kavling-modal').modal('hide');
                afterActionSuccess('Perubahan tipe kavling berhasil diterapkan.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-kr-btn-cancel', async function () {
            if (!currentKavlingReq) return;
            const conf = await Swal.fire({
                icon: 'warning',
                title: 'Batalkan Request Kavling?',
                input: 'text',
                inputPlaceholder: 'Alasan pembatalan (opsional)',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tutup',
            });
            if (!conf.isConfirmed) return;
            try {
                await apiPost('api/kavling-request/cancel', {
                    id_proyek: currentKavlingReq.id_proyek,
                    id_request: currentKavlingReq.id_request,
                    alasan_batal: conf.value || '',
                });
                $('#permohonan-kavling-modal').modal('hide');
                afterActionSuccess('Request kavling dibatalkan.');
            } catch (err) {
                showErrorAlert(err.message);
            }
        });

        // ── Tagihan Direksi Actions ────────────────────────────────────────
        $(document).on('change', '#permohonan-td-sign-method', function () {
            const isCanvas = this.value === 'canvas';
            $('#permohonan-td-profile-wrap').toggleClass('d-none', isCanvas);
            $('#permohonan-td-canvas-wrap').toggleClass('d-none', !isCanvas);
            if (isCanvas && canvases.td) setTimeout(() => canvases.td.resize(), 60);
        });

        $(document).on('click', '#permohonan-td-btn-sign', async function () {
            if (!currentTagihan) return;
            const method = $('#permohonan-td-sign-method').val() || 'profile';
            const password = ($('#permohonan-td-password').val() || '').trim();
            if (!password) {
                showErrorAlert('Password akun wajib diisi untuk menandatangani surat tagihan.');
                $('#permohonan-td-password').focus();
                return;
            }
            let signatureData = '';
            if (method === 'canvas') {
                signatureData = canvases.td ? canvases.td.data() : '';
                if (!signatureData) {
                    showErrorAlert('Silakan buat tanda tangan di kanvas terlebih dahulu.');
                    return;
                }
            } else if (!tagihanHasProfileSig) {
                showErrorAlert('Profil belum memiliki TTD. Pilih metode Gambar di Kanvas.');
                return;
            }

            const $btn = $(this).prop('disabled', true);
            try {
                await apiPost('direksi/sign_tagihan', {
                    no_inv: currentTagihan.no_inv,
                    password,
                    signature_method: method,
                    signature_data: signatureData,
                });
                $('#permohonan-tagihan-direksi-modal').modal('hide');
                afterActionSuccess('Surat tagihan berhasil ditandatangani.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).on('click', '#permohonan-td-btn-reject', async function () {
            if (!currentTagihan) return;
            const alasan = ($('#permohonan-td-reject-alasan').val() || '').trim();
            const password = ($('#permohonan-td-password').val() || '').trim();
            if (!alasan) {
                showErrorAlert('Alasan penolakan wajib diisi.');
                $('#permohonan-td-reject-alasan').focus();
                return;
            }
            if (!password) {
                showErrorAlert('Password akun wajib diisi untuk menolak surat.');
                $('#permohonan-td-password').focus();
                return;
            }
            const $btn = $(this).prop('disabled', true);
            try {
                await apiPost('direksi/reject_tagihan', {
                    no_inv: currentTagihan.no_inv,
                    alasan,
                    password,
                });
                $('#permohonan-tagihan-direksi-modal').modal('hide');
                afterActionSuccess('Surat tagihan berhasil ditolak.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        // ── MGM Actions ────────────────────────────────────────────────────
        $(document).on('click', '.permohonan-mgm-btn-confirm', async function () {
            const idx = Number($(this).data('stage-index'));
            const stage = currentMgmStages[idx];
            if (!stage) return;
            try {
                await apiPost('api/mgm/confirm-bonus', {
                    id_bonus: stage.id_bonus,
                    nominal_bonus: stage.nominal_bonus || '',
                });
                $('#permohonan-mgm-modal').modal('hide');
                afterActionSuccess('Bonus MGM berhasil dikonfirmasi.');
            } catch (err) {
                showErrorAlert(err.message);
            }
        });

        $(document).on('click', '.permohonan-mgm-btn-form', function () {
            openMgmActionForm(Number($(this).data('stage-index')), $(this).data('mgm-action'));
        });

        $(document).on('click', '#permohonan-mgm-form-cancel', function () {
            $('#permohonan-mgm-form-card').addClass('d-none');
        });

        $(document).on('dragenter dragover', '#permohonan-bpb-purchase-dropzone, #permohonan-fs-cair-dropzone, #permohonan-mgm-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('is-dragging');
        });
        $(document).on('dragleave dragend', '#permohonan-bpb-purchase-dropzone, #permohonan-fs-cair-dropzone, #permohonan-mgm-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is-dragging');
        });
        $(document).on('drop', '#permohonan-bpb-purchase-dropzone, #permohonan-fs-cair-dropzone, #permohonan-mgm-dropzone', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is-dragging');
            const files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            if (!files || !files.length) return;
            const inputMap = {
                'permohonan-bpb-purchase-dropzone': '#permohonan-bpb-purchase-files',
                'permohonan-fs-cair-dropzone': '#permohonan-fs-cair-file',
                'permohonan-mgm-dropzone': '#permohonan-mgm-file',
            };
            const targetSel = inputMap[this.id];
            if (targetSel && $(targetSel).length) {
                $(targetSel)[0].files = files;
                $(targetSel).trigger('change');
            }
        });

        $(document).on('click', '#permohonan-mgm-dropzone', () => $('#permohonan-mgm-file').trigger('click'));
        $(document).on('change', '#permohonan-mgm-file', function () {
            const f = this.files && this.files[0];
            $('#permohonan-mgm-filename').text(f ? f.name : 'JPG, PNG, WEBP, atau PDF');
        });

        $(document).on('submit', '#permohonan-mgm-action-form', async function (e) {
            e.preventDefault();
            const pathMap = {
                submit_keuangan: 'api/mgm/submit-keuangan',
                mark_cair_keuangan: 'api/mgm/mark-cair-keuangan',
                pay_promosi: 'api/mgm/pay-promosi',
            };
            const path = pathMap[currentMgmAction];
            if (!path) return;

            const fileInput = $('#permohonan-mgm-file')[0];
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                showErrorAlert('Lampiran bukti wajib dipilih terlebih dahulu.');
                return;
            }

            const $btn = $('#permohonan-mgm-form-submit').prop('disabled', true);
            try {
                await apiPost(path, new FormData(this));
                $('#permohonan-mgm-modal').modal('hide');
                afterActionSuccess('Aksi bonus MGM berhasil disimpan.');
            } catch (err) {
                showErrorAlert(err.message);
            } finally {
                $btn.prop('disabled', false);
            }
        });

        // ── Listeners for Reused Modals (Cashout Subkon & Tiket Masalah) ───
        $(document).ajaxSuccess(function (_event, _xhr, settings) {
            const reqUrl = String((settings && settings.url) || '');
            if (reqUrl.includes('cashout/subkon/')) {
                if (!/\/(list|ambil|history|detail-list)/.test(reqUrl)) {
                    refreshHubAndBadgeOnly();
                }
            }
            if (reqUrl.includes('api/tiket-masalah/')) {
                if (/\/(store|update|add-progress|update-status|cancel|delete-progress|toggle-pin|publish)/.test(reqUrl)) {
                    refreshHubAndBadgeOnly();
                }
            }
        });

        // Initial load
        loadList(1);
    });
})(jQuery);
