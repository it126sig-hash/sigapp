(function($) {
    'use strict';

    const targetState = {
        activeYear: new Date().getFullYear(),
        target: null,
        kavlings: [],
        history: [],
        targetList: [],
        activeTipes: [],
        tipeMap: {},
        tipesLoadedForProyek: null,
        selectedBarTipeId: '',
        stagedKavlings: [],
        stagedGroup: null,
        isSavingStaged: false,
        pendingKavlingLocks: {},
        editingTargetKavlingId: null,
        requestingTargetKavlingId: null,
        focusTargetKavlingId: null,
        selectedTargetKavlingIds: [],
        editModalTargetKavlingIds: [],
        isSubmittingEditTipe: false,
        isSubmittingPlanningRequest: false,
        specLoadingId: null,
        requests: [],
        requestCounts: { all: 0, pending: 0, approved: 0, rejected: 0 },
        pendingRequestsMap: {},
        requestsStatusFilter: 'all',
        requestsYearFilter: '',
        searchQuery: '',
        flashGroup: null,
        flashTimer: null
    };

    function currentRoleId() {
        return typeof roleid !== 'undefined' ? Number(roleid) : 0;
    }

    function isAdmin() {
        return currentRoleId() === 1;
    }

    function canManageTarget() {
        const r = currentRoleId();
        return r === 1 || r === 4;
    }

    function canRequestTarget() {
        const r = currentRoleId();
        return r === 1 || r === 6;
    }

    function applyTargetModalRolePermissions() {
        const manageAllowed = canManageTarget();
        $('#target-btn-add-from-canvas').prop('hidden', !manageAllowed);
        $('#target-notes-divider, #target-notes-card').prop('hidden', !manageAllowed);
        $('#target-btn-batch-edit-tipe').prop('hidden', !manageAllowed);
        $('#target-btn-batch-req-planning').prop('hidden', !manageAllowed);
    }

    function makePendingRequestKey(idTarget, idKavling) {
        return String(Number(idTarget || 0)) + ':' + String(Number(idKavling || 0));
    }

    function syncPendingRequestsMap(rows, scopeTargetId, scopeYear) {
        const list = Array.isArray(rows) ? rows : [];
        if (scopeTargetId && Number(scopeTargetId) > 0) {
            const prefix = String(Number(scopeTargetId)) + ':';
            Object.keys(targetState.pendingRequestsMap).forEach(function(k) {
                if (k.indexOf(prefix) === 0) {
                    delete targetState.pendingRequestsMap[k];
                }
            });
        } else if (scopeYear && Number(scopeYear) > 0) {
            Object.keys(targetState.pendingRequestsMap).forEach(function(k) {
                const existing = targetState.pendingRequestsMap[k];
                if (existing && Number(existing.tahun_target) === Number(scopeYear)) {
                    delete targetState.pendingRequestsMap[k];
                }
            });
        } else {
            targetState.pendingRequestsMap = {};
        }

        list.forEach(function(req) {
            if (req && String(req.status || '').toLowerCase() === 'pending') {
                const key = makePendingRequestKey(req.id_target, req.id_kavling);
                targetState.pendingRequestsMap[key] = req;
            }
        });
    }

    function findPendingRequestForRow(row) {
        if (!row) return null;
        const key = makePendingRequestKey(row.id_target, row.id_kavling);
        return targetState.pendingRequestsMap[key] || null;
    }

    function updateTargetPendingBadge(counts) {
        if (counts && typeof counts === 'object') {
            targetState.requestCounts = {
                all: Number(counts.all || 0),
                pending: Number(counts.pending || 0),
                approved: Number(counts.approved || 0),
                rejected: Number(counts.rejected || 0)
            };
        }
        const pendingCount = Number((targetState.requestCounts && targetState.requestCounts.pending) || 0);
        const $badge = $('#target-request-pending-badge');
        if ($badge.length) {
            $badge.text(pendingCount);
            $badge.prop('hidden', pendingCount <= 0);
        }
    }

    function currentProyekId() {
        return typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.id_proyek
            ? Number(dt_proyek.id_proyek)
            : 0;
    }

    function currentProyekNama() {
        return typeof dt_proyek !== 'undefined' && dt_proyek && dt_proyek.nama_proyek
            ? String(dt_proyek.nama_proyek)
            : '-';
    }

    function updateTargetCsrf(token) {
        if (token && typeof csrfHash !== 'undefined') {
            csrfHash = token;
        }
    }

    function extractTargetErrorMessage(xhr, fallback) {
        const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
        if (json) {
            updateTargetCsrf(json.token);
            if (json.messages && typeof json.messages === 'string') {
                return json.messages;
            }
            if (json.errors && typeof json.errors === 'object') {
                const msgs = Object.values(json.errors).filter(Boolean);
                if (msgs.length > 0) {
                    return msgs.join(' ');
                }
            }
        }
        return fallback || 'Terjadi kesalahan saat memproses permintaan.';
    }

    function target_escape_html(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function sanitizeTargetAccessUrl(url) {
        const raw = String(url == null ? '' : url).trim();
        if (!raw) return '';
        if (/^(https?:\/\/|\/(?!\/))/i.test(raw)) {
            return raw;
        }
        return '';
    }

    function formatTargetDateTime(value) {
        if (!value) return '-';
        if (typeof format_datetime === 'function') {
            try {
                return format_datetime(value) || value;
            } catch (e) {
                return value;
            }
        }
        return value;
    }

    function showTargetToast(icon, title, text) {
        if (typeof Swal === 'undefined') return;
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: icon === 'success' ? 2200 : 3400,
            timerProgressBar: true
        });
        Toast.fire({
            icon: icon || 'info',
            title: title || '',
            text: text || ''
        });
    }

    function formatTipeOptionLabel(tipe) {
        if (!tipe) return '-';
        const noTipe = String(tipe.no_tipe_rumah || '').trim();
        const namaTipe = String(tipe.tipe_rumah || '').trim();
        let label = '';
        if (noTipe && namaTipe) {
            label = noTipe + ' / ' + namaTipe;
        } else {
            label = namaTipe || noTipe || ('Tipe #' + (tipe.id_tipe || ''));
        }
        const lb = Number(tipe.lb || 0);
        const lt = Number(tipe.lt || 0);
        if (lb > 0 || lt > 0) {
            label += ' (' + lb + '/' + lt + ')';
        }
        return label;
    }

    function formatKavlingMasterTipe(row) {
        if (!row) return '-';
        if (row.master_tipe) return String(row.master_tipe);
        const noTipe = String(row.kavling_no_tipe_rumah || row.no_tipe_rumah || '').trim();
        const namaTipe = String(row.kavling_tipe_rumah || row.tipe_rumah || '').trim();
        if (noTipe && namaTipe) return noTipe + ' / ' + namaTipe;
        return namaTipe || noTipe || '-';
    }

    function formatKavlingLocation(row) {
        const jalan = String(row.nama_jalan || row.cluster || '').trim();
        const noKav = String(row.no_kavling || row.kavling || '').trim();
        if (jalan && noKav) return jalan + ' · No. ' + noKav;
        if (noKav) return 'No. ' + noKav;
        return '#' + (row.id_kavling || '-');
    }

    function formatSnapshotKavlingLabel(item) {
        if (!item) return '-';
        const jalan = String(item.nama_jalan || item.cluster || '').trim();
        const noKav = String(item.no_kavling || item.kavling || '').trim();
        if (jalan && noKav) return jalan + ' No. ' + noKav;
        if (noKav) return 'Kav. ' + noKav;
        return 'Kavling #' + (item.id_kavling || '-');
    }

    function resolveRowHasActiveConsumer(row) {
        if (!row) return false;
        if (row.has_active_consumer === true || row.has_active_consumer === 1 || row.has_active_consumer === '1') {
            return true;
        }
        const idMkdt = Number(row.id_mkdt || 0);
        const isBatal = Number(row.is_batal || 0) === 1;
        const status = String(row.status_mkdt || '').trim().toLowerCase();
        return idMkdt > 0 && !isBatal && !['', 'batal', 'nup'].includes(status);
    }

    function resolveShapeHasActiveConsumer(sh, idKavling) {
        const fallbackAttrs = (typeof findSiteplanKavlingAttrs === 'function' && idKavling)
            ? findSiteplanKavlingAttrs(idKavling)
            : null;
        const data = (sh && sh.data) ? sh.data : (fallbackAttrs && fallbackAttrs.data ? fallbackAttrs.data : {});
        const data2 = (sh && sh.data2) ? sh.data2 : (fallbackAttrs && fallbackAttrs.data2 ? fallbackAttrs.data2 : {});

        if (data.has_active_consumer === true || data.has_active_consumer === 1) {
            return true;
        }

        const idMkdt = Number(data.id_mkdt || 0);
        const isBatal = Number(data.is_batal || 0) === 1;
        const statusMkdt = String(data2.status_mkdt || '').trim().toLowerCase();

        return idMkdt > 0 && !isBatal && !['', 'batal', 'nup'].includes(statusMkdt);
    }

    function resetTargetInteractiveCardScroll() {
        const card = document.getElementById('target-interactive-card');
        if (card) {
            card.scrollTop = 0;
        }
    }

    function mountTargetInteractiveBar() {
        const $bar = $('#target-interactive-bar');
        if (!$bar.length) return;

        const $canvasCol = $('.siteplan-canvas-col').first();
        if ($canvasCol.length) {
            if (!$bar.parent().is($canvasCol)) {
                $canvasCol.prepend($bar);
            }
            $bar.removeClass('is-fixed-fallback');
        } else {
            $bar.addClass('is-fixed-fallback');
        }
    }

    function clearTargetCanvasFlash() {
        if (targetState.flashTimer) {
            clearTimeout(targetState.flashTimer);
            targetState.flashTimer = null;
        }
        if (targetState.flashGroup) {
            try {
                targetState.flashGroup.destroy();
            } catch (e) {}
            targetState.flashGroup = null;
            if (typeof masked !== 'undefined' && masked && typeof masked.batchDraw === 'function') {
                masked.batchDraw();
            }
        }
    }

    function flashTargetShapeHighlight(sh, idKavling) {
        if (typeof Konva === 'undefined' || typeof masked === 'undefined' || !masked) return;

        let points = (sh && Array.isArray(sh.points) && sh.points.length >= 6) ? sh.points : null;
        if (!points && typeof findSiteplanLocationNode === 'function' && idKavling) {
            const node = findSiteplanLocationNode('kavling', idKavling);
            if (node) {
                points = typeof node.points === 'function'
                    ? node.points()
                    : (typeof node.getAttr === 'function' ? node.getAttr('points') : null);
            }
        }
        if (!points || points.length < 6) return;

        clearTargetCanvasFlash();

        const flashGroup = new Konva.Group({
            name: 'target-added-flash',
            listening: false
        });

        const outer = new Konva.Line({
            points: points,
            stroke: '#ffffff',
            strokeWidth: 7,
            closed: true,
            listening: false,
            strokeScaleEnabled: false,
            perfectDrawEnabled: false
        });
        const inner = new Konva.Line({
            points: points,
            stroke: '#2a9d8f',
            strokeWidth: 3.5,
            fill: 'rgba(42, 157, 143, 0.22)',
            closed: true,
            listening: false,
            strokeScaleEnabled: false,
            perfectDrawEnabled: false
        });

        flashGroup.add(outer, inner);
        masked.add(flashGroup);
        flashGroup.moveToTop();
        masked.batchDraw();

        targetState.flashGroup = flashGroup;
        targetState.flashTimer = setTimeout(clearTargetCanvasFlash, 1100);
    }

    function collectKnownYears() {
        const yearSet = new Set();
        const nowYear = new Date().getFullYear();

        for (let y = nowYear - 1; y <= nowYear + 3; y++) {
            yearSet.add(y);
        }

        if (targetState.activeYear >= 2000 && targetState.activeYear <= 2100) {
            yearSet.add(Number(targetState.activeYear));
        }

        (targetState.targetList || []).forEach(function(item) {
            const y = Number(item && item.tahun_target ? item.tahun_target : 0);
            if (y >= 2000 && y <= 2100) {
                yearSet.add(y);
            }
        });

        $('#filter-tahun-target option').each(function() {
            const y = Number($(this).val() || 0);
            if (y >= 2000 && y <= 2100) {
                yearSet.add(y);
            }
        });

        return Array.from(yearSet).sort(function(a, b) { return a - b; });
    }

    function syncAllYearControls() {
        const yearsAsc = collectKnownYears();
        const yearsDesc = yearsAsc.slice().sort(function(a, b) { return b - a; });
        const activeStr = String(targetState.activeYear || new Date().getFullYear());

        const $barYear = $('#target-bar-year');
        if ($barYear.length) {
            $barYear.empty();
            yearsAsc.forEach(function(y) {
                const match = (targetState.targetList || []).find(function(item) {
                    return Number(item.tahun_target) === y;
                });
                const label = match
                    ? y + ' (' + Number(match.jumlah_kavling || 0) + ' kav)'
                    : String(y);
                $barYear.append($('<option>', { value: String(y), text: label }));
            });
            $barYear.val(activeStr);
        }

        const populateSelect = function($sel) {
            if (!$sel.length) return;
            $sel.empty();
            yearsDesc.forEach(function(y) {
                const match = (targetState.targetList || []).find(function(item) {
                    return Number(item.tahun_target) === y;
                });
                const label = match
                    ? y + ' (' + Number(match.jumlah_kavling || 0) + ' kavling)'
                    : y + ' (Baru)';
                $sel.append($('<option>', { value: String(y), text: label }));
            });
            $sel.val(activeStr);
        };

        populateSelect($('#target-modal-year-select'));
        populateSelect($('#target-history-year-select'));

        const $reqYear = $('#target-requests-year-select');
        if ($reqYear.length) {
            const currentVal = String(targetState.requestsYearFilter || '');
            $reqYear.empty();
            $reqYear.append($('<option>', { value: '', text: 'Semua Tahun' }));
            yearsDesc.forEach(function(y) {
                const match = (targetState.targetList || []).find(function(item) {
                    return Number(item.tahun_target) === y;
                });
                const label = match
                    ? y + ' (' + Number(match.jumlah_kavling || 0) + ' kavling)'
                    : String(y);
                $reqYear.append($('<option>', { value: String(y), text: label }));
            });
            if (currentVal && $reqYear.find('option[value="' + currentVal + '"]').length) {
                $reqYear.val(currentVal);
            } else {
                $reqYear.val('');
                targetState.requestsYearFilter = '';
            }
        }
    }

    function syncSidebarTargetYears() {
        if (typeof window.syncTargetYearDropdown !== 'function') return;
        const savedYears = [];
        (targetState.targetList || []).forEach(function(item) {
            const y = Number(item && item.tahun_target ? item.tahun_target : 0);
            if (y >= 2000 && y <= 2100) {
                savedYears.push(String(y));
            }
        });
        if (targetState.target && targetState.target.tahun_target) {
            savedYears.push(String(targetState.target.tahun_target));
        }
        const preferred = window.targetInteractiveMode ? String(targetState.activeYear || '') : '';
        if (savedYears.length > 0 || preferred) {
            const sorted = Array.from(new Set(savedYears)).sort(function(a, b) {
                return Number(b) - Number(a);
            });
            window.syncTargetYearDropdown(sorted, preferred || undefined);
        }
    }

    function syncSidebarTargetFilter(year, shouldApplyFilter) {
        const tahunStr = String(year || targetState.activeYear || new Date().getFullYear());
        const $toggle = $('#toggle-tampilkan-target');
        const $container = $('#filter-target-tahun-container');
        const $select = $('#filter-tahun-target');

        if ($toggle.length) {
            $toggle.prop('checked', true);
        }
        if ($container.length) {
            $container.removeAttr('hidden').stop(true, true).show();
        }
        if ($select.length) {
            $select.find('option[value=""]').remove();
            if (!$select.find('option[value="' + tahunStr + '"]').length) {
                $select.append($('<option>', { value: tahunStr, text: tahunStr }));
            }
            $select.prop('disabled', false).val(tahunStr);
        }

        if (typeof window.syncTargetMenuVisibility === 'function') {
            window.syncTargetMenuVisibility();
        }
        if (shouldApplyFilter && typeof window.apply_server_filter === 'function') {
            window.apply_server_filter();
        }
    }

    function updateTargetCountersAndHeader() {
        const count = (targetState.kavlings || []).length;
        const yearStr = String(targetState.activeYear || new Date().getFullYear());
        const target = targetState.target;

        $('#target-bar-count').text(count + ' kavling');
        $('#target-tab-kavling-count').text(count);
        $('#target-tab-history-count').text((targetState.history || []).length);
        $('#target-selected-count').text(count + ' kavling target');
        $('#target-modal-year-label').text(yearStr);
        $('#target-modal-project-badge').text(currentProyekNama());

        $('#target-id_target').val(target && target.id_target ? target.id_target : '');
        $('#target-id_proyek').val(currentProyekId() || '');
        $('#target-nama_proyek').val(currentProyekNama());
        $('#target-tahun_target').val(yearStr);
        $('#target-deskripsi').val(target && target.deskripsi ? target.deskripsi : '');

        const ids = (targetState.kavlings || []).map(function(row) {
            return row.id_kavling;
        });
        $('#target-id_kavling').val(ids.join(';'));
        $('#target-save-btn').prop('disabled', ids.length === 0);
    }

    function cacheTipeListToMap(rows) {
        (Array.isArray(rows) ? rows : []).forEach(function(item) {
            if (item && item.id_tipe) {
                targetState.tipeMap[String(item.id_tipe)] = item;
            }
        });
    }

    function formatTipeSelectText(tipe) {
        if (!tipe) return '';
        const tipeRumah = String(tipe.tipe_rumah || '').trim();
        const noTipe = String(tipe.no_tipe_rumah || '').trim();
        if (tipeRumah && noTipe) {
            return tipeRumah + ' (' + noTipe + ')';
        }
        return tipeRumah || noTipe || ('Tipe #' + (tipe.id_tipe || ''));
    }

    function formatTipeSelect2Markup(item) {
        if (!item) return '';
        if (item.loading || !item.id) {
            return target_escape_html(item.text || '');
        }
        const data = item.tipeData || targetState.tipeMap[String(item.id)] || null;
        if (!data) {
            return target_escape_html(item.text || '');
        }
        const tipeRumah = String(data.tipe_rumah || '').trim();
        const noTipe = String(data.no_tipe_rumah || '').trim();
        if (tipeRumah && noTipe) {
            return '<strong>' + target_escape_html(tipeRumah) + '</strong> (' + target_escape_html(noTipe) + ')';
        }
        return '<strong>' + target_escape_html(tipeRumah || noTipe || ('Tipe #' + (data.id_tipe || ''))) + '</strong>';
    }

    function buildTipeSpecValue(val, suffix) {
        const text = String(val == null ? '' : val).trim();
        if (!text) return '-';
        return suffix ? (text + suffix) : text;
    }

    function formatDimensionPair(first, second, unit) {
        const a = String(first == null ? '' : first).trim();
        const b = String(second == null ? '' : second).trim();
        if ((!a || a === '0') && (!b || b === '0')) return '-';
        const aStr = (a && a !== '0') ? a : '-';
        const bStr = (b && b !== '0') ? b : '-';
        return aStr + ' / ' + bStr + (unit ? (' ' + unit) : '');
    }

    function renderTargetBarTipePreview() {
        const $preview = $('#target-bar-tipe-preview');
        if (!$preview.length) return;

        const mode = $('input[name="target_bar_tipe_source"]:checked').val() || 'master';
        const selectedId = String($('#target-bar-tipe-select').val() || targetState.selectedBarTipeId || '').trim();
        const tipe = selectedId ? (targetState.tipeMap[selectedId] || null) : null;

        if (mode !== 'master' || !selectedId || !tipe) {
            $preview.attr('hidden', true).empty();
            resetTargetInteractiveCardScroll();
            return;
        }

        const tipeTitle = formatTipeSelectText(tipe);
        const isSubsidi = String(tipe.is_subsidi || '0') === '1';
        const badgeHtml = '<span class="target-preview-badge ' + (isSubsidi ? 'is-subsidi' : 'is-komersil') + '">'
            + (isSubsidi ? 'Subsidi' : 'Komersil')
            + '</span>';

        const url3d = sanitizeTargetAccessUrl(tipe.gambar_tipe_access_url);
        const urlDenah = sanitizeTargetAccessUrl(tipe.gambar_denah_access_url);

        const renderThumbCard = function(url, caption, focusKey) {
            if (url) {
                return '<button type="button" class="target-tipe-thumb-card target-preview-thumb-trigger"'
                    + ' data-tipe-id="' + target_escape_html(selectedId) + '"'
                    + ' data-focus="' + target_escape_html(focusKey) + '"'
                    + ' title="Klik untuk perbesar ' + target_escape_html(caption) + '">'
                    + '<div class="target-tipe-thumb-img-wrap">'
                    + '<img class="target-tipe-thumb-img" src="' + target_escape_html(url) + '" alt="' + target_escape_html(caption) + '" loading="lazy">'
                    + '<span class="target-tipe-thumb-zoom"><i class="fas fa-search-plus"></i></span>'
                    + '</div>'
                    + '<span class="target-tipe-thumb-caption">' + target_escape_html(caption) + '</span>'
                    + '</button>';
            }
            return '<div class="target-tipe-thumb-card">'
                + '<div class="target-tipe-thumb-img-wrap"><span class="target-tipe-thumb-empty">Belum ada gambar</span></div>'
                + '<span class="target-tipe-thumb-caption">' + target_escape_html(caption) + '</span>'
                + '</div>';
        };

        const lbLt = formatDimensionPair(tipe.lb, tipe.lt, 'm²');
        const ktKm = formatDimensionPair(tipe.jumlah_kamar_tidur, tipe.jumlah_kamar_mandi, '');
        const specs = [
            { label: 'LB / LT', value: lbLt },
            { label: 'KT / KM', value: ktKm },
            { label: 'Atap', value: buildTipeSpecValue(tipe.spesifikasi_teknis_atap) },
            { label: 'Dinding', value: buildTipeSpecValue(tipe.spesifikasi_teknis_dinding) },
            { label: 'Lantai', value: buildTipeSpecValue(tipe.spesifikasi_teknis_lantai) },
            { label: 'Pondasi', value: buildTipeSpecValue(tipe.spesifikasi_teknis_pondasi) }
        ];

        const specsHtml = specs.map(function(sp) {
            return '<div class="target-preview-spec-item">'
                + '<span class="target-preview-spec-label">' + target_escape_html(sp.label) + '</span>'
                + '<span class="target-preview-spec-val">' + target_escape_html(sp.value) + '</span>'
                + '</div>';
        }).join('');

        const html = '<div class="target-preview-head">'
            + '<span class="target-preview-title">' + target_escape_html(tipeTitle) + '</span>'
            + badgeHtml
            + '</div>'
            + '<div class="target-preview-thumbs">'
            + renderThumbCard(url3d, 'Gambar 3D', '3d')
            + renderThumbCard(urlDenah, 'Denah', 'denah')
            + '</div>'
            + '<div class="target-preview-specs">' + specsHtml + '</div>';

        $preview.html(html).removeAttr('hidden');
        resetTargetInteractiveCardScroll();
        if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(resetTargetInteractiveCardScroll);
        }
    }

    function hasCompleteTipeSpec(tipe) {
        if (!tipe || typeof tipe !== 'object') return false;
        if (tipe._specLoaded === true) return true;
        return Object.prototype.hasOwnProperty.call(tipe, 'spesifikasi_teknis_atap')
            && Object.prototype.hasOwnProperty.call(tipe, 'gambar_tipe_access_url');
    }

    function renderTargetTipeSpecModalBody(tipe, focusKey) {
        const title = formatTipeSelectText(tipe);
        $('#target-spec-modal-heading').text('Spesifikasi Tipe: ' + title);

        const url3d = sanitizeTargetAccessUrl(tipe.gambar_tipe_access_url);
        const urlDenah = sanitizeTargetAccessUrl(tipe.gambar_denah_access_url);
        const urlKerja = sanitizeTargetAccessUrl(tipe.gambar_kerja_access_url);

        const buildImgCard = function(url, label, isFocus) {
            const highlightClass = isFocus ? ' is-highlighted' : '';
            const linkBtn = url
                ? '<a href="' + target_escape_html(url) + '" target="_blank" rel="noopener noreferrer" class="btn btn-link btn-sm p-0">Buka Tab Baru <i class="fas fa-external-link-alt ml-25"></i></a>'
                : '';
            const imgBody = url
                ? '<img src="' + target_escape_html(url) + '" alt="' + target_escape_html(label) + '">'
                : '<div class="text-muted small">Belum ada ' + target_escape_html(label) + '</div>';
            return '<div class="target-spec-modal-img-card' + highlightClass + '">'
                + '<div class="target-spec-modal-img-head"><span>' + target_escape_html(label) + '</span>' + linkBtn + '</div>'
                + '<div class="target-spec-modal-img-box">' + imgBody + '</div>'
                + '</div>';
        };

        const infoItems = [
            { label: 'Nama Tipe', value: buildTipeSpecValue(tipe.tipe_rumah) },
            { label: 'Nomor Tipe', value: buildTipeSpecValue(tipe.no_tipe_rumah) },
            { label: 'LB / LT', value: formatDimensionPair(tipe.lb, tipe.lt, 'm²') },
            { label: 'KT / KM', value: formatDimensionPair(tipe.jumlah_kamar_tidur, tipe.jumlah_kamar_mandi, '') },
            { label: 'Atap', value: buildTipeSpecValue(tipe.spesifikasi_teknis_atap) },
            { label: 'Dinding', value: buildTipeSpecValue(tipe.spesifikasi_teknis_dinding) },
            { label: 'Lantai', value: buildTipeSpecValue(tipe.spesifikasi_teknis_lantai) },
            { label: 'Pondasi', value: buildTipeSpecValue(tipe.spesifikasi_teknis_pondasi) }
        ];

        const infoHtml = infoItems.map(function(item) {
            return '<div class="col-md-6 col-12 mb-50">'
                + '<div class="info-row">'
                + '<div class="info-label">' + target_escape_html(item.label) + '</div>'
                + '<div class="info-value">' + target_escape_html(item.value) + '</div>'
                + '</div>'
                + '</div>';
        }).join('');

        const kerjaHtml = urlKerja
            ? '<div class="mt-75 text-right"><a href="' + target_escape_html(urlKerja) + '" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm"><i class="fas fa-drafting-compass mr-25"></i>Lihat Gambar Kerja</a></div>'
            : '';

        const bodyHtml = '<div class="target-spec-modal-images">'
            + buildImgCard(url3d, 'Gambar 3D / Ilustrasi', focusKey === '3d')
            + buildImgCard(urlDenah, 'Gambar Denah', focusKey === 'denah')
            + '</div>'
            + '<div class="divider divider-left"><div class="divider-text">Spesifikasi Teknis</div></div>'
            + '<div class="target-spec-modal-card">'
            + '<div class="row">' + infoHtml + '</div>'
            + kerjaHtml
            + '</div>';

        $('#target-spec-modal-body').html(bodyHtml);
    }

    function openTargetTipeSpecModal(idTipe, focusKey) {
        const numericId = Number(idTipe || 0);
        if (!numericId || numericId <= 0) return;

        const $modal = $('#modal-target-tipe-spec');
        if (!$modal.length) return;

        const cached = targetState.tipeMap[String(numericId)] || null;
        if (hasCompleteTipeSpec(cached)) {
            targetState.specLoadingId = null;
            renderTargetTipeSpecModalBody(cached, focusKey);
            $modal.modal('show');
            return;
        }

        targetState.specLoadingId = numericId;
        $('#target-spec-modal-heading').text('Detail Spesifikasi Tipe');
        $('#target-spec-modal-body').html(
            '<div class="target-spec-modal-card text-center py-3">'
            + '<i class="fa fa-spinner fa-spin mr-50 text-primary"></i>'
            + '<span class="text-muted">Memuat gambar &amp; spesifikasi teknis tipe...</span>'
            + '</div>'
        );
        $modal.modal('show');

        $.ajax({
            url: base_url + 'api/tipe/getone',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_tipe: numericId
            },
            dataType: 'json',
            success: function(res) {
                if (res && res.token) {
                    updateTargetCsrf(res.token);
                }
                const fetched = (res && res.data && typeof res.data === 'object')
                    ? res.data
                    : ((res && res.id_tipe) ? res : null);

                if (!fetched || !fetched.id_tipe) {
                    if (targetState.specLoadingId === numericId) {
                        $('#target-spec-modal-body').html(
                            '<div class="alert alert-warning mb-0">Data spesifikasi tipe tidak ditemukan.</div>'
                        );
                    }
                    return;
                }

                const normalized = Object.assign({}, fetched, { _specLoaded: true });
                targetState.tipeMap[String(fetched.id_tipe)] = normalized;

                if (targetState.specLoadingId === numericId) {
                    renderTargetTipeSpecModalBody(normalized, focusKey);
                }
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat spesifikasi teknis tipe.');
                if (targetState.specLoadingId === numericId) {
                    $('#target-spec-modal-body').html(
                        '<div class="alert alert-danger mb-0">' + target_escape_html(msg) + '</div>'
                    );
                }
            }
        });
    }

    function ensureActiveTipeSelect2($sel, $dropdownParent, eventNs, onReset, onValueChange) {
        if (!$sel.length || typeof $.fn.select2 !== 'function') return;

        const idProyek = currentProyekId();
        const prevProyek = Number($sel.data('proyekId') || 0);

        if ($sel.hasClass('select2-hidden-accessible')) {
            if (prevProyek === idProyek) {
                return;
            }
            $sel.select2('destroy');
            $sel.empty();
            if (typeof onReset === 'function') {
                onReset();
            }
        }

        $sel.data('proyekId', idProyek);
        $sel.select2({
            dropdownParent: $dropdownParent,
            width: '100%',
            placeholder: 'Cari atau pilih Master Tipe Aktif...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: base_url + 'api/tipe/all?only_active=1&format=raw',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        [csrfName]: csrfHash,
                        id_proyek: currentProyekId(),
                        only_active: 1,
                        format: 'raw',
                        search: (params && params.term) ? params.term : ''
                    };
                },
                processResults: function(res) {
                    updateTargetCsrf(res && res.token);
                    const rows = Array.isArray(res && res.data) ? res.data : [];
                    const activeRows = rows.filter(function(item) {
                        return item && Number(item.is_active != null ? item.is_active : 1) === 1;
                    });
                    cacheTipeListToMap(activeRows);
                    return {
                        results: activeRows.map(function(item) {
                            return {
                                id: String(item.id_tipe),
                                text: formatTipeSelectText(item),
                                tipeData: item
                            };
                        })
                    };
                },
                cache: false
            },
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: formatTipeSelect2Markup,
            templateSelection: formatTipeSelect2Markup
        });

        const selectEv = 'select2:select.' + eventNs;
        const clearEv = 'select2:clear.' + eventNs;
        const changeEv = 'change.' + eventNs;

        $sel.off(selectEv + ' ' + clearEv + ' ' + changeEv)
            .on(selectEv, function(e) {
                const data = e && e.params ? e.params.data : null;
                if (data && data.tipeData) {
                    cacheTipeListToMap([data.tipeData]);
                }
                if (typeof onValueChange === 'function') {
                    onValueChange(String($(this).val() || ''));
                }
            })
            .on(clearEv + ' ' + changeEv, function() {
                if (typeof onValueChange === 'function') {
                    onValueChange(String($(this).val() || ''));
                }
            });
    }

    function initTargetBarTipeSelect2() {
        ensureActiveTipeSelect2(
            $('#target-bar-tipe-select'),
            $('#target-interactive-card'),
            'targetBar',
            function() {
                targetState.selectedBarTipeId = '';
            },
            function(val) {
                targetState.selectedBarTipeId = val;
                renderTargetBarTipePreview();
            }
        );
    }

    function initTargetEditTipeSelect2() {
        ensureActiveTipeSelect2(
            $('#target-edit-tipe-select'),
            $('#modal-target-edit-tipe'),
            'targetEditModal',
            null,
            null
        );
    }

    function loadActiveTipes(force, callback) {
        const idProyek = currentProyekId();
        if (!idProyek) {
            if (typeof callback === 'function') callback([]);
            return;
        }

        if (!force && targetState.tipesLoadedForProyek === idProyek && targetState.activeTipes.length > 0) {
            cacheTipeListToMap(targetState.activeTipes);
            renderTargetBarTipePreview();
            if (typeof callback === 'function') callback(targetState.activeTipes);
            return;
        }

        $.ajax({
            url: base_url + 'api/tipe/all?only_active=1&format=raw',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                only_active: 1,
                format: 'raw'
            },
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                const rows = Array.isArray(res && res.data) ? res.data : [];
                targetState.activeTipes = rows.filter(function(item) {
                    return item && Number(item.is_active != null ? item.is_active : 1) === 1;
                });
                cacheTipeListToMap(targetState.activeTipes);
                targetState.tipesLoadedForProyek = idProyek;
                renderTargetBarTipePreview();
                if (targetState.editingTargetKavlingId || targetState.requestingTargetKavlingId) {
                    renderTargetKavlingTable();
                }
                if (typeof callback === 'function') callback(targetState.activeTipes);
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat daftar tipe aktif.');
                showTargetToast('error', 'Gagal memuat tipe', msg);
                if (typeof callback === 'function') callback([]);
            }
        });
    }

    function loadTargetList(callback) {
        const idProyek = currentProyekId();
        if (!idProyek) {
            if (typeof callback === 'function') callback([]);
            return;
        }

        $.ajax({
            url: base_url + 'api/target/list',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek
            },
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                targetState.targetList = Array.isArray(res && res.data) ? res.data : [];
                syncAllYearControls();
                syncSidebarTargetYears();
                if (typeof callback === 'function') callback(targetState.targetList);
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat daftar target proyek.');
                showTargetToast('error', 'Gagal memuat target', msg);
                if (typeof callback === 'function') callback([]);
            }
        });
    }

    function applyTargetDetailData(data, fallbackYear) {
        const payload = data || {};
        targetState.target = payload.target || null;
        targetState.kavlings = Array.isArray(payload.kavlings) ? payload.kavlings : [];
        targetState.history = Array.isArray(payload.history) ? payload.history : [];

        if (targetState.target && targetState.target.tahun_target) {
            targetState.activeYear = Number(targetState.target.tahun_target);
        } else if (fallbackYear) {
            targetState.activeYear = Number(fallbackYear);
        }

        if (targetState.target) {
            const idx = targetState.targetList.findIndex(function(item) {
                return Number(item.id_target) === Number(targetState.target.id_target);
            });
            const summaryRow = Object.assign({}, targetState.target, {
                jumlah_kavling: targetState.kavlings.length
            });
            if (idx >= 0) {
                targetState.targetList[idx] = Object.assign({}, targetState.targetList[idx], summaryRow);
            } else {
                targetState.targetList.unshift(summaryRow);
            }
        }

        targetState.editingTargetKavlingId = null;
        targetState.requestingTargetKavlingId = null;
        pruneSelectedTargetKavlings();
        applyTargetModalRolePermissions();
        syncAllYearControls();
        syncSidebarTargetYears();
        updateTargetCountersAndHeader();
        revalidateStagedKavlings();
        renderTargetKavlingTable();
        render_target_history(targetState.history);
    }

    function loadTargetDetailByYear(year, callback) {
        const idProyek = currentProyekId();
        const tahunTarget = Number(year || targetState.activeYear || new Date().getFullYear());
        if (!idProyek || tahunTarget < 2000 || tahunTarget > 2100) {
            if (typeof callback === 'function') callback(null);
            return;
        }

        if (Number(targetState.activeYear) !== tahunTarget) {
            targetState.selectedTargetKavlingIds = [];
        }
        targetState.activeYear = tahunTarget;
        syncAllYearControls();

        $.ajax({
            url: base_url + 'api/target/detail',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                tahun_target: tahunTarget
            },
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (!res || !res.success) {
                    showTargetToast('error', 'Gagal memuat detail target', (res && res.messages) || '');
                    if (typeof callback === 'function') callback(null);
                    return;
                }
                applyTargetDetailData(res.data, tahunTarget);
                if (typeof callback === 'function') callback(res.data);
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat detail target tahun ' + tahunTarget);
                showTargetToast('error', 'Gagal memuat detail target', msg);
                if (typeof callback === 'function') callback(null);
            }
        });
    }

    function renderStatusBadge(row) {
        if (resolveRowHasActiveConsumer(row)) {
            const label = String(row.status_mkdt || 'Konsumen Aktif');
            return '<span class="target-pill target-pill-danger" title="Kavling memiliki konsumen aktif">'
                + '<i class="fas fa-user-check"></i> ' + target_escape_html(label)
                + '</span>';
        }
        const status = String(row.status_mkdt || '').trim();
        if (Number(row.is_batal || 0) === 1 || status.toLowerCase() === 'batal') {
            return '<span class="target-pill target-pill-warning">Batal (Kosong)</span>';
        }
        if (status.toLowerCase() === 'nup') {
            return '<span class="target-pill target-pill-primary">NUP</span>';
        }
        return '<span class="target-pill target-pill-success">Tersedia</span>';
    }

    function canEditTargetRow(row) {
        if (!row || !Number(row.id_target_kavling)) return false;
        if (!canManageTarget()) return false;
        return !resolveRowHasActiveConsumer(row);
    }

    function isTargetRowTipeMismatch(row) {
        if (!row) return false;
        const targetIdTipe = Number(row.id_tipe || 0);
        if (targetIdTipe > 0) {
            return Number(row.kavling_id_tipe || 0) !== targetIdTipe;
        }
        const manualTipe = String(row.tipe_manual || '').trim().toLowerCase();
        if (!manualTipe) return false;
        const kavlingTipe = String(row.kavling_tipe_rumah || '').trim().toLowerCase();
        return manualTipe !== kavlingTipe;
    }

    function getPlanningRequestStatus(row) {
        if (!row) return '';
        return String(row.planning_request_status || '').trim().toLowerCase();
    }

    function isPlanningRequestPending(row) {
        if (!row) return false;
        return getPlanningRequestStatus(row) === 'pending' || Number(row.pending_planning_request_id || 0) > 0;
    }

    function formatTargetRowTargetTipeLabel(row) {
        if (!row) return '-';
        const targetIdTipe = Number(row.id_tipe || 0);
        if (targetIdTipe > 0) {
            return String(row.tipe_label || row.target_tipe_rumah || ('Tipe #' + targetIdTipe)).trim();
        }
        return String(row.tipe_manual || row.tipe_label || '-').trim() || '-';
    }

    function pruneSelectedTargetKavlings() {
        const editableSet = new Set();
        (targetState.kavlings || []).forEach(function(row) {
            if (canEditTargetRow(row)) {
                editableSet.add(Number(row.id_target_kavling));
            }
        });
        targetState.selectedTargetKavlingIds = (targetState.selectedTargetKavlingIds || []).filter(function(id) {
            return editableSet.has(Number(id));
        });
    }

    function syncTargetBatchToolbarAndCheckAll(visibleRows) {
        const rows = Array.isArray(visibleRows) ? visibleRows : (targetState.kavlings || []);
        const selectedSet = new Set((targetState.selectedTargetKavlingIds || []).map(Number));
        const editableVisibleIds = rows
            .filter(canEditTargetRow)
            .map(function(row) { return Number(row.id_target_kavling); });

        const checkedVisibleCount = editableVisibleIds.filter(function(id) {
            return selectedSet.has(id);
        }).length;

        const $checkAll = $('#target-check-all');
        if ($checkAll.length) {
            const hasEditable = editableVisibleIds.length > 0;
            $checkAll.prop('disabled', !hasEditable);
            $checkAll.prop('checked', hasEditable && checkedVisibleCount === editableVisibleIds.length);
            $checkAll.prop('indeterminate', hasEditable && checkedVisibleCount > 0 && checkedVisibleCount < editableVisibleIds.length);
        }

        const totalSelected = selectedSet.size;
        const $bar = $('#target-batch-action-bar');
        if ($bar.length) {
            if (totalSelected > 0) {
                const manageAllowed = canManageTarget();
                $bar.removeAttr('hidden');
                $('#target-batch-selected-count').text(totalSelected + ' kavling dipilih');
                $('#target-btn-batch-edit-tipe').prop('hidden', !manageAllowed);
                $('#target-btn-batch-req-planning').prop('hidden', !manageAllowed);
            } else {
                $bar.attr('hidden', true);
                $('#target-batch-selected-count').text('0 kavling dipilih');
            }
        }
    }

    function renderCurrentKavlingTipeCell(row, isMismatch) {
        const kavlingIdTipe = Number(row.kavling_id_tipe || 0);
        const namaTipe = String(row.kavling_tipe_rumah || '').trim();
        const noTipe = String(row.kavling_no_tipe_rumah || row.no_tipe_rumah || '').trim();

        let mainHtml = '';
        if (kavlingIdTipe > 0) {
            const displayTitle = namaTipe || noTipe || ('Tipe #' + kavlingIdTipe);
            mainHtml = '<button type="button" class="target-tipe-trigger target-btn-preview-tipe"'
                + ' data-id-tipe="' + kavlingIdTipe + '"'
                + ' title="Lihat spek teknis">'
                + '<strong>' + target_escape_html(displayTitle) + '</strong>'
                + (noTipe && namaTipe ? '<div class="target-kavling-sub">' + target_escape_html(noTipe) + '</div>' : '')
                + '</button>';
        } else if (namaTipe || noTipe) {
            mainHtml = '<div>'
                + '<strong>' + target_escape_html(namaTipe || '-') + '</strong>'
                + (noTipe ? '<div class="target-kavling-sub">' + target_escape_html(noTipe) + '</div>' : '')
                + '</div>';
        } else {
            mainHtml = '<div class="text-muted">-</div>';
        }

        const mismatchHtml = isMismatch
            ? '<div class="target-mismatch-note"><i class="fas fa-exclamation-triangle mr-25"></i>Tipe kavling belum sama</div>'
            : '';

        return mainHtml + mismatchHtml;
    }

    function renderTipeTargetCell(row) {
        const rowId = Number(row.id_target_kavling);
        const requestAllowed = canRequestTarget();
        const isRequesting = requestAllowed && Number(targetState.requestingTargetKavlingId) === rowId;
        const pendingReq = findPendingRequestForRow(row);

        if (isRequesting) {
            const masterOptions = (targetState.activeTipes || []).map(function(t) {
                const selected = Number(row.id_tipe) === Number(t.id_tipe) ? ' selected' : '';
                return '<option value="' + t.id_tipe + '"' + selected + '>' + target_escape_html(formatTipeOptionLabel(t)) + '</option>';
            }).join('');

            return '<div class="target-inline-editor target-inline-req-box" data-row-id="' + rowId + '">'
                + '<div class="target-inline-req-title"><i class="fas fa-paper-plane mr-25"></i>Ajukan Perubahan Tipe Target</div>'
                + '<div class="target-inline-mode-row">'
                + '<select class="form-control form-control-sm target-req-mode" style="width:auto;">'
                + '<option value="master" selected>Master Aktif</option>'
                + '<option value="manual">Manual</option>'
                + '</select>'
                + '<select class="form-control form-control-sm target-req-master-select">'
                + '<option value="">-- Pilih Tipe Aktif --</option>' + masterOptions
                + '</select>'
                + '<input type="text" class="form-control form-control-sm target-req-manual-input" maxlength="255" placeholder="Contoh: 36/72" hidden>'
                + '</div>'
                + '<input type="text" class="form-control form-control-sm target-req-catatan-input" maxlength="500" placeholder="Catatan pengajuan (opsional)">'
                + '<div class="target-inline-action-row">'
                + '<button type="button" class="btn btn-primary btn-sm target-btn-submit-request" data-id="' + rowId + '">'
                + '<i class="fas fa-paper-plane mr-25"></i>Kirim Request</button>'
                + '<button type="button" class="btn btn-outline-secondary btn-sm target-btn-cancel-request" data-id="' + rowId + '">Batal</button>'
                + '</div>'
                + '</div>';
        }

        const targetIdTipe = Number(row.id_tipe || 0);
        const isManual = !(targetIdTipe > 0);
        const isInactiveMaster = !isManual && row.target_tipe_is_active !== null && Number(row.target_tipe_is_active) !== 1;
        const inactiveBadge = isInactiveMaster
            ? '<span class="target-pill target-pill-danger ml-auto">Nonaktif</span>'
            : '';

        let mainHtml = '';
        if (!isManual) {
            const targetNamaTipe = String(row.target_tipe_rumah || row.tipe_label || '').trim() || ('Tipe #' + targetIdTipe);
            const targetNoTipe = String(row.target_no_tipe_rumah || '').trim();
            mainHtml = '<div class="d-flex align-items-center justify-content-between" style="gap:.4rem;">'
                + '<button type="button" class="target-tipe-trigger target-btn-preview-tipe"'
                + ' data-id-tipe="' + targetIdTipe + '"'
                + ' title="Lihat spek teknis">'
                + '<strong>' + target_escape_html(targetNamaTipe) + '</strong>'
                + (targetNoTipe ? '<div class="target-kavling-sub">' + target_escape_html(targetNoTipe) + '</div>' : '')
                + '</button>'
                + inactiveBadge
                + '</div>';
        } else {
            const manualLabel = String(row.tipe_manual || row.tipe_label || '-').trim() || '-';
            mainHtml = '<div class="d-flex align-items-center justify-content-between" style="gap:.4rem;">'
                + '<strong class="target-tipe-manual-text">' + target_escape_html(manualLabel) + '</strong>'
                + '<span class="target-pill target-pill-warning ml-auto">Manual</span>'
                + '</div>';
        }

        let pendingHtml = '';
        if (pendingReq) {
            const proposedLabel = pendingReq.new_tipe_label || pendingReq.new_tipe_manual || '';
            pendingHtml = '<div class="target-req-pending-meta">'
                + '<span class="target-pill target-pill-warning"><i class="fas fa-clock"></i> Menunggu ACC MKDT</span>'
                + (proposedLabel ? ' <span class="target-kavling-sub">Usulan: <strong>' + target_escape_html(proposedLabel) + '</strong></span>' : '')
                + '</div>';
        }

        return mainHtml + pendingHtml;
    }

    function renderTargetRowActions(row) {
        const rowId = Number(row.id_target_kavling);
        const manageAllowed = canManageTarget();
        const requestAllowed = canRequestTarget();
        const hasConsumer = resolveRowHasActiveConsumer(row);
        const isMismatch = isTargetRowTipeMismatch(row);
        const pendingReq = findPendingRequestForRow(row);
        const planningStatus = getPlanningRequestStatus(row);
        const isPlanningPending = isPlanningRequestPending(row);
        const buttons = [];

        if (manageAllowed) {
            if (hasConsumer) {
                buttons.push(
                    '<button type="button" class="btn btn-outline-secondary btn-sm target-btn-edit-tipe" data-id="' + rowId + '" disabled title="Kavling memiliki konsumen aktif">'
                    + '<i class="fas fa-lock mr-25"></i>Ubah Tipe</button>'
                );
            } else {
                buttons.push(
                    '<button type="button" class="btn btn-outline-primary btn-sm target-btn-edit-tipe" data-id="' + rowId + '" title="Ubah tipe target">'
                    + '<i class="fas fa-edit mr-25"></i>Ubah Tipe</button>'
                );
            }
            buttons.push(
                '<button type="button" class="btn btn-outline-danger btn-sm target-btn-remove-kavling" data-id="' + rowId + '" data-label="' + target_escape_html(formatKavlingLocation(row)) + '" title="Hapus dari target">'
                + '<i class="fas fa-trash-alt"></i></button>'
            );

            if (isPlanningPending) {
                buttons.push(
                    '<span class="target-planning-req-badge is-pending" title="Pengajuan ubah kavling ke tim Planning sedang diproses">'
                    + '<i class="fas fa-clock"></i>Req Planning: Pending</span>'
                );
            } else {
                if (planningStatus === 'approved') {
                    buttons.push(
                        '<span class="target-planning-req-badge is-approved" title="Pengajuan ubah kavling ke tim Planning sudah selesai">'
                        + '<i class="fas fa-check-circle"></i>Req Planning: Selesai</span>'
                    );
                } else if (planningStatus === 'rejected') {
                    buttons.push(
                        '<span class="target-planning-req-badge is-rejected" title="Pengajuan ubah kavling ke tim Planning sebelumnya ditolak">'
                        + '<i class="fas fa-times-circle"></i>Req Planning: Ditolak</span>'
                    );
                }

                if (isMismatch && !hasConsumer) {
                    buttons.push(
                        '<button type="button" class="btn btn-outline-warning btn-sm target-btn-req-planning" data-id-target-kavling="' + rowId + '" data-id-kavling="' + Number(row.id_kavling || 0) + '" title="Ajukan perubahan tipe kavling ke tim Planning">'
                        + '<i class="fas fa-paper-plane mr-25"></i>Request ke Planning</button>'
                    );
                }
            }
        }

        if (requestAllowed) {
            if (pendingReq) {
                buttons.push(
                    '<button type="button" class="btn btn-outline-warning btn-sm target-btn-request-change" data-id="' + rowId + '" disabled title="Sudah ada pengajuan menunggu ACC MKDT">'
                    + '<i class="fas fa-clock mr-25"></i>Request Ubah Tipe</button>'
                );
            } else if (hasConsumer) {
                buttons.push(
                    '<button type="button" class="btn btn-outline-secondary btn-sm target-btn-request-change" data-id="' + rowId + '" disabled title="Kavling memiliki konsumen aktif">'
                    + '<i class="fas fa-lock mr-25"></i>Request Ubah Tipe</button>'
                );
            } else {
                buttons.push(
                    '<button type="button" class="btn btn-outline-warning btn-sm target-btn-request-change" data-id="' + rowId + '">'
                    + '<i class="fas fa-paper-plane mr-25"></i>Request Ubah Tipe</button>'
                );
            }
        }

        if (buttons.length === 0) {
            return '<span class="text-muted small">-</span>';
        }

        return '<div class="target-row-actions-wrap">' + buttons.join('') + '</div>';
    }

    function renderTargetKavlingTable() {
        const $tbody = $('#target-kavling-tbody');
        if (!$tbody.length) return;

        const manageAllowed = canManageTarget();
        const allRows = targetState.kavlings || [];
        pruneSelectedTargetKavlings();

        const q = String(targetState.searchQuery || '').trim().toLowerCase();
        const rows = !q ? allRows : allRows.filter(function(row) {
            const haystack = [
                row.nama_jalan,
                row.cluster,
                row.no_kavling,
                row.kavling,
                row.tipe_label,
                row.tipe_manual,
                row.target_tipe_rumah,
                row.target_no_tipe_rumah,
                row.kavling_no_tipe_rumah,
                row.kavling_tipe_rumah,
                row.status_mkdt
            ].join(' ').toLowerCase();
            return haystack.indexOf(q) !== -1;
        });

        $('#target-kavling-summary').text(
            'Menampilkan ' + rows.length + ' dari ' + allRows.length + ' kavling target tahun ' + targetState.activeYear
        );

        if (allRows.length === 0) {
            const emptyHint = manageAllowed
                ? ' Klik <strong>Tambah dari Kanvas</strong> untuk memilih kavling langsung di siteplan.'
                : '';
            $tbody.html(
                '<tr><td colspan="7" class="text-center text-muted py-3">'
                + 'Belum ada kavling pada target tahun ' + target_escape_html(targetState.activeYear) + '.' + emptyHint
                + '</td></tr>'
            );
            syncTargetBatchToolbarAndCheckAll([]);
            return;
        }

        if (rows.length === 0) {
            $tbody.html(
                '<tr><td colspan="7" class="text-center text-muted py-2">Tidak ada kavling yang cocok dengan pencarian "' + target_escape_html(targetState.searchQuery) + '".</td></tr>'
            );
            syncTargetBatchToolbarAndCheckAll([]);
            return;
        }

        const selectedSet = new Set((targetState.selectedTargetKavlingIds || []).map(Number));
        let html = '';
        rows.forEach(function(row, idx) {
            const rowId = Number(row.id_target_kavling);
            const rowEditable = canEditTargetRow(row);
            const isChecked = rowEditable && selectedSet.has(rowId);
            const isMismatch = isTargetRowTipeMismatch(row);
            const rowClasses = [];
            if (Number(targetState.focusTargetKavlingId) === rowId) {
                rowClasses.push('target-row-focused');
            }
            if (isMismatch) {
                rowClasses.push('target-row-tipe-mismatch');
            }
            const classAttr = rowClasses.length ? (' class="' + rowClasses.join(' ') + '"') : '';
            const disabledTitle = !canManageTarget()
                ? 'Hanya MKDT atau Admin yang dapat memilih kavling'
                : (!rowEditable ? 'Kavling memiliki konsumen aktif' : 'Pilih kavling');

            html += '<tr' + classAttr + ' data-id-target-kavling="' + rowId + '">'
                + '<td class="text-center">'
                + '<input type="checkbox" class="target-row-check" value="' + rowId + '" data-id="' + rowId + '"'
                + (isChecked ? ' checked' : '')
                + (!rowEditable ? ' disabled' : '')
                + ' title="' + target_escape_html(disabledTitle) + '"'
                + ' aria-label="Pilih kavling ' + target_escape_html(formatKavlingLocation(row)) + '">'
                + '</td>'
                + '<td class="text-center">' + (idx + 1) + '</td>'
                + '<td><strong>' + target_escape_html(formatKavlingLocation(row)) + '</strong>'
                + '<div class="target-kavling-sub">ID Kavling: #' + target_escape_html(row.id_kavling) + '</div></td>'
                + '<td>' + renderCurrentKavlingTipeCell(row, isMismatch) + '</td>'
                + '<td>' + renderTipeTargetCell(row) + '</td>'
                + '<td>' + renderStatusBadge(row) + '</td>'
                + '<td class="text-center">' + renderTargetRowActions(row) + '</td>'
                + '</tr>';
        });

        $tbody.html(html);
        syncTargetBatchToolbarAndCheckAll(rows);
    }

    function parseHistorySnapshots(row) {
        let before = row.before_snapshot || null;
        let after = row.after_snapshot || null;
        if ((!before || !after) && row.snapshot) {
            try {
                const parsed = typeof row.snapshot === 'string' ? JSON.parse(row.snapshot) : row.snapshot;
                if (parsed && typeof parsed === 'object') {
                    if (!before && parsed.before) before = parsed.before;
                    if (!after && parsed.after) after = parsed.after;
                }
            } catch (e) {}
        }
        return { before: before, after: after };
    }

    function renderSnapshotKavlingList(snapshot, diffSummary, side) {
        const kavlings = snapshot && Array.isArray(snapshot.kavlings) ? snapshot.kavlings : [];
        if (kavlings.length === 0) {
            return '<div class="text-muted small py-50">'
                + (side === 'before' ? 'Belum ada data kavling sebelumnya.' : '0 kavling pada target.')
                + '</div>';
        }

        const addedIds = new Set(((diffSummary && diffSummary.added) || []).map(function(i) { return Number(i.id_kavling); }));
        const updatedIds = new Set(((diffSummary && diffSummary.updated) || []).map(function(i) { return Number(i.id_kavling); }));
        const removedIds = new Set(((diffSummary && diffSummary.removed) || []).map(function(i) { return Number(i.id_kavling); }));

        let html = '<ul class="target-diff-items">';
        kavlings.forEach(function(item) {
            const kavId = Number(item.id_kavling || 0);
            let itemClass = '';
            if (side === 'after' && addedIds.has(kavId)) itemClass = ' is-added';
            else if (updatedIds.has(kavId)) itemClass = ' is-updated';
            else if (side === 'before' && removedIds.has(kavId)) itemClass = ' is-removed';

            const tipeText = item.tipe_label || item.tipe_manual || item.target_tipe_rumah || '-';
            html += '<li class="target-diff-item' + itemClass + '">'
                + '<span>' + target_escape_html(formatSnapshotKavlingLabel(item)) + '</span>'
                + '<span class="font-weight-bold">' + target_escape_html(tipeText) + '</span>'
                + '</li>';
        });
        html += '</ul>';
        return html;
    }

    function render_target_history(rows) {
        const $container = $('#target-history-here');
        if (!$container.length) return;

        const list = Array.isArray(rows) ? rows.slice().sort(function(a, b) {
            return Number(b.version || 0) - Number(a.version || 0);
        }) : [];

        $('#target-tab-history-count').text(list.length);

        if (list.length === 0) {
            $container.html(
                '<div class="card target-modal-card mb-0">'
                + '<div class="card-body text-center text-muted py-3">Belum ada histori perubahan untuk target tahun ' + target_escape_html(targetState.activeYear) + '.</div>'
                + '</div>'
            );
            return;
        }

        let html = '';
        list.forEach(function(row, idx) {
            const versionLabel = row.version_label || (row.version ? 'v' + row.version : 'v' + (list.length - idx));
            const actionLabel = row.action_label || row.aksi || row.action || 'Perubahan Target';
            const actor = row.add_by_username || row.username || row.actor || '-';
            const diff = row.diff_summary || null;
            const snaps = parseHistorySnapshots(row);
            const collapseId = 'target-history-diff-' + idx + '-' + Number(row.id_target_history || idx);
            const expanded = idx === 0;

            let diffBadges = '';
            if (diff && typeof diff === 'object') {
                if (Number(diff.added_count || 0) > 0) {
                    diffBadges += '<span class="target-pill target-pill-success">+' + Number(diff.added_count) + ' ditambah</span>';
                }
                if (Number(diff.updated_count || 0) > 0) {
                    diffBadges += '<span class="target-pill target-pill-primary">~' + Number(diff.updated_count) + ' tipe diubah</span>';
                }
                if (Number(diff.removed_count || 0) > 0) {
                    diffBadges += '<span class="target-pill target-pill-danger">-' + Number(diff.removed_count) + ' dihapus</span>';
                }
                diffBadges += '<span class="target-pill target-pill-neutral">Total: '
                    + Number(diff.total_before || 0) + ' &rarr; ' + Number(diff.total_after || 0) + ' kavling</span>';
            }

            let approvalMeta = '';
            if (row.requested_by_username) {
                approvalMeta += '<span class="target-pill target-pill-neutral">Diminta oleh: ' + target_escape_html(row.requested_by_username) + '</span>';
            }
            if (row.approved_by_username) {
                approvalMeta += '<span class="target-pill target-pill-success">Disetujui oleh: ' + target_escape_html(row.approved_by_username) + '</span>';
            }

            const meta = row.metadata && typeof row.metadata === 'object' ? row.metadata : {};
            const catatanReq = row.catatan_request || meta.catatan_request || '';
            const catatanApp = row.catatan_approval || meta.catatan_approval || '';
            let approvalNotesHtml = '';
            if (catatanReq || catatanApp) {
                approvalNotesHtml += '<div class="target-history-notes">';
                if (catatanReq) {
                    approvalNotesHtml += '<div class="target-history-note-item"><span class="text-muted">Catatan Request:</span> ' + target_escape_html(catatanReq) + '</div>';
                }
                if (catatanApp) {
                    approvalNotesHtml += '<div class="target-history-note-item"><span class="text-muted">Catatan Approval:</span> ' + target_escape_html(catatanApp) + '</div>';
                }
                approvalNotesHtml += '</div>';
            }

            html += '<div class="target-history-card">'
                + '<div class="target-history-head">'
                + '<div class="target-history-head-left">'
                + '<span class="target-version-badge">' + target_escape_html(versionLabel) + '</span>'
                + '<span class="target-history-action">' + target_escape_html(actionLabel) + '</span>'
                + '</div>'
                + '<span class="target-history-time">' + target_escape_html(formatTargetDateTime(row.created_at)) + '</span>'
                + '</div>'
                + '<div class="target-history-meta">'
                + '<span><i class="fas fa-user mr-25"></i>Oleh: <strong>' + target_escape_html(actor) + '</strong></span>'
                + approvalMeta
                + '</div>'
                + '<div class="target-history-summary">' + target_escape_html(row.deskripsi || '-') + '</div>'
                + approvalNotesHtml
                + (diffBadges ? '<div class="target-diff-pills">' + diffBadges + '</div>' : '')
                + '<button type="button" class="btn btn-outline-primary btn-sm" data-toggle="collapse" data-target="#' + collapseId + '" aria-expanded="' + (expanded ? 'true' : 'false') + '">'
                + '<i class="fas fa-exchange-alt mr-25"></i>Before vs After</button>'
                + '<div class="collapse' + (expanded ? ' show' : '') + '" id="' + collapseId + '">'
                + '<div class="target-diff-grid">'
                + '<div class="target-diff-pane">'
                + '<div class="target-diff-pane-title">Sebelum (Before)</div>'
                + renderSnapshotKavlingList(snaps.before, diff, 'before')
                + '</div>'
                + '<div class="target-diff-pane">'
                + '<div class="target-diff-pane-title">Sesudah (After)</div>'
                + renderSnapshotKavlingList(snaps.after, diff, 'after')
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        });

        $container.html(html);
    }

    function clearTargetStagedCanvasHighlights() {
        if (targetState.stagedGroup) {
            try {
                targetState.stagedGroup.destroy();
            } catch (e) {}
            targetState.stagedGroup = null;
        }
        if (typeof masked !== 'undefined' && masked && typeof masked.batchDraw === 'function') {
            masked.batchDraw();
        }
    }

    function renderTargetStagedCanvasHighlights() {
        clearTargetStagedCanvasHighlights();

        if (!window.targetInteractiveMode || !Array.isArray(targetState.stagedKavlings) || targetState.stagedKavlings.length === 0) {
            return;
        }
        if (typeof Konva === 'undefined' || typeof masked === 'undefined' || !masked) {
            return;
        }

        const stagedGroup = new Konva.Group({
            name: 'target-staged-highlights',
            listening: false
        });

        let hasAnyShape = false;
        targetState.stagedKavlings.forEach(function(item) {
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
            stagedGroup.add(outer, inner);
            hasAnyShape = true;
        });

        if (!hasAnyShape) {
            stagedGroup.destroy();
            return;
        }

        masked.add(stagedGroup);
        stagedGroup.moveToTop();
        masked.batchDraw();
        targetState.stagedGroup = stagedGroup;
    }

    function renderTargetStagedList() {
        const list = Array.isArray(targetState.stagedKavlings) ? targetState.stagedKavlings : [];
        const count = list.length;

        $('#target-bar-staged-count').text(count + ' terpilih');
        $('#target-bar-staged-reset-btn').prop('disabled', count === 0 || targetState.isSavingStaged);
        $('#target-bar-save-staged-btn').prop('disabled', count === 0 || targetState.isSavingStaged);

        const $list = $('#target-bar-staged-list');
        if (!$list.length) return;

        if (count === 0) {
            $list.html('<div class="target-staged-empty">Klik kavling kosong di kanvas untuk memilih.</div>');
            return;
        }

        const html = list.map(function(item, idx) {
            const label = item.kavling_label || ('Kavling #' + item.id_kavling);
            return '<div class="target-staged-item" data-kavling-id="' + Number(item.id_kavling) + '">'
                + '<span class="target-staged-item-label">'
                + '<span class="target-staged-item-idx">' + (idx + 1) + '.</span>'
                + target_escape_html(label)
                + '</span>'
                + '<button type="button" class="target-staged-remove-btn" data-id="' + Number(item.id_kavling) + '" title="Hapus dari pilihan">&times;</button>'
                + '</div>';
        }).join('');

        $list.html(html);
    }

    function revalidateStagedKavlings() {
        const list = Array.isArray(targetState.stagedKavlings) ? targetState.stagedKavlings : [];
        if (list.length === 0) {
            renderTargetStagedList();
            renderTargetStagedCanvasHighlights();
            return;
        }

        const existingTargetIds = new Set((targetState.kavlings || []).map(function(row) {
            return Number(row.id_kavling);
        }));

        const kept = [];
        let removedCount = 0;
        list.forEach(function(item) {
            const idKav = Number(item.id_kavling);
            if (existingTargetIds.has(idKav)) {
                removedCount += 1;
                return;
            }
            kept.push(item);
        });

        targetState.stagedKavlings = kept;
        renderTargetStagedList();
        renderTargetStagedCanvasHighlights();

        if (removedCount > 0 && window.targetInteractiveMode) {
            showTargetToast(
                'info',
                removedCount + ' kavling disesuaikan',
                'Kavling yang sudah terdaftar di Target ' + targetState.activeYear + ' dikeluarkan dari daftar terpilih.'
            );
        }
    }

    function enterTargetSetMode(preferredYear) {
        if (!canManageTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Mode Set Target hanya dapat digunakan oleh MKDT atau Admin.');
            return;
        }
        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.close === 'function') {
            window.SIGAPPMobileBottomNav.close();
        }
        if (typeof hapus_seleksi === 'function') {
            hapus_seleksi();
        }
        $('#modal-target-siteplan').modal('hide');

        mountTargetInteractiveBar();
        $('.siteplan-canvas-col').addClass('is-target-mode-host');
        $('#mkdt-set-target-btn').addClass('is-target-mode-active');

        window.targetInteractiveMode = true;
        $('#target-interactive-bar').removeAttr('hidden');

        if (preferredYear && Number(preferredYear) >= 2000 && Number(preferredYear) <= 2100) {
            targetState.activeYear = Number(preferredYear);
        } else {
            const sidebarYear = parseInt($('#filter-tahun-target').val() || '0', 10);
            if ($('#toggle-tampilkan-target').prop('checked') === true && sidebarYear >= 2000 && sidebarYear <= 2100) {
                targetState.activeYear = sidebarYear;
            } else if (!targetState.activeYear || targetState.activeYear < 2000 || targetState.activeYear > 2100) {
                targetState.activeYear = new Date().getFullYear();
            }
        }

        syncAllYearControls();
        $('#target-bar-year').val(String(targetState.activeYear));

        initTargetBarTipeSelect2();
        renderTargetBarTipePreview();
        renderTargetStagedList();
        renderTargetStagedCanvasHighlights();
        resetTargetInteractiveCardScroll();
        if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(resetTargetInteractiveCardScroll);
        }

        syncSidebarTargetFilter(targetState.activeYear, true);

        loadActiveTipes(false);
        loadTargetList(function() {
            loadTargetDetailByYear(targetState.activeYear);
        });
    }

    function exitTargetSetMode() {
        window.targetInteractiveMode = false;
        $('#target-interactive-bar').attr('hidden', true);
        $('.siteplan-canvas-col').removeClass('is-target-mode-host');
        $('#mkdt-set-target-btn').removeClass('is-target-mode-active');
        const $sel = $('#target-bar-tipe-select');
        if ($sel.length && $sel.hasClass('select2-hidden-accessible')) {
            try {
                $sel.select2('close');
            } catch (e) {}
        }
        targetState.stagedKavlings = [];
        clearTargetStagedCanvasHighlights();
        renderTargetStagedList();
        clearTargetCanvasFlash();
    }

    function resolveInitialTargetYearFromFilterOrList(list) {
        if ($('#toggle-tampilkan-target').prop('checked') === true) {
            const filterYear = parseInt($('#filter-tahun-target').val() || '0', 10);
            if (filterYear >= 2000 && filterYear <= 2100) {
                return filterYear;
            }
        }

        const years = (Array.isArray(list) ? list : [])
            .map(function(item) { return Number(item && item.tahun_target ? item.tahun_target : 0); })
            .filter(function(y) { return y >= 2000 && y <= 2100; });
        if (years.length > 0) {
            return Math.max.apply(null, years);
        }

        return (targetState.activeYear >= 2000 && targetState.activeYear <= 2100)
            ? Number(targetState.activeYear)
            : new Date().getFullYear();
    }

    function scrollTargetKavlingRowIntoView(idTargetKavling) {
        if (!idTargetKavling) return;
        const $row = $('#target-kavling-tbody tr[data-id-target-kavling="' + Number(idTargetKavling) + '"]');
        if ($row.length && $row[0] && typeof $row[0].scrollIntoView === 'function') {
            $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function openTargetManageModal(initialTab) {
        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.close === 'function') {
            window.SIGAPPMobileBottomNav.close();
        }

        targetState.editingTargetKavlingId = null;
        targetState.requestingTargetKavlingId = null;
        targetState.focusTargetKavlingId = null;
        targetState.selectedTargetKavlingIds = [];

        let tabSelector = '#tab-target-kavling';
        if (initialTab === 'history') {
            tabSelector = '#tab-target-history';
        } else if (initialTab === 'requests') {
            tabSelector = '#tab-target-requests';
        }
        $(tabSelector).tab('show');

        applyTargetModalRolePermissions();
        updateTargetCountersAndHeader();
        syncAllYearControls();

        $('#modal-target-siteplan').modal({
            backdrop: 'static',
            keyboard: false
        });

        loadActiveTipes(false);
        loadTargetList(function() {
            loadTargetDetailByYear(targetState.activeYear);
        });
        loadTargetRequests();
    }

    function open_target_siteplan() {
        if (!canManageTarget()) {
            if (canRequestTarget()) {
                open_target_request();
                return;
            }
            showTargetToast('warning', 'Akses terbatas', 'Mode Set Target hanya dapat digunakan oleh MKDT atau Admin.');
            return;
        }
        enterTargetSetMode();
    }

    function open_target_history() {
        openTargetManageModal('history');
    }

    function isInvokedFromCanvasContextMenu() {
        const evt = window.event;
        if (!evt || !evt.target) return false;
        const $target = $(evt.target);
        if ($target.closest('#planning-target-request-btn').length > 0) {
            return false;
        }
        return $target.closest('#menu, #menu-dynamic-items').length > 0;
    }

    function resolveContextKavlingId(preferredKavlingId) {
        if (preferredKavlingId && Number(preferredKavlingId) > 0) {
            return Number(preferredKavlingId);
        }
        if (!isInvokedFromCanvasContextMenu()) {
            return 0;
        }
        if (typeof editdtt !== 'undefined' && Array.isArray(editdtt) && editdtt.length > 0 && editdtt[0]) {
            const rawId = String(editdtt[0].id || '');
            if (rawId.indexOf('kav') === 0) {
                const parsed = parseInt(rawId.replace('kav', ''), 10);
                return parsed > 0 ? parsed : 0;
            }
        }
        return 0;
    }

    function open_target_request(preferredKavlingId) {
        if (!canRequestTarget() && !canManageTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Anda tidak memiliki izin untuk membuka target siteplan.');
            return;
        }

        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.close === 'function') {
            window.SIGAPPMobileBottomNav.close();
        }

        const contextKavlingId = resolveContextKavlingId(preferredKavlingId);
        targetState.editingTargetKavlingId = null;
        targetState.requestingTargetKavlingId = null;
        targetState.focusTargetKavlingId = null;
        targetState.selectedTargetKavlingIds = [];
        targetState.requestsStatusFilter = 'all';
        targetState.requestsYearFilter = '';
        $('#target-requests-status-filter').val('all');
        $('#target-requests-year-select').val('');

        $('#tab-target-kavling').tab('show');
        applyTargetModalRolePermissions();
        updateTargetCountersAndHeader();
        syncAllYearControls();

        $('#modal-target-siteplan').modal({
            backdrop: 'static',
            keyboard: false
        });

        loadActiveTipes(false);
        loadTargetList(function(list) {
            const resolvedYear = resolveInitialTargetYearFromFilterOrList(list);
            let doneCount = 0;
            const onBothLoaded = function() {
                doneCount += 1;
                if (doneCount < 2 || contextKavlingId <= 0) {
                    return;
                }

                const matchedRow = (targetState.kavlings || []).find(function(k) {
                    return Number(k.id_kavling) === contextKavlingId;
                });

                if (!matchedRow) {
                    showTargetToast(
                        'info',
                        'Kavling belum ada di Target ' + resolvedYear,
                        'Pilih tahun target lain atau hubungi MKDT untuk mendaftarkan kavling ini ke target.'
                    );
                    return;
                }

                const rowId = Number(matchedRow.id_target_kavling);
                targetState.focusTargetKavlingId = rowId;

                const pendingReq = findPendingRequestForRow(matchedRow);
                if (pendingReq) {
                    renderTargetKavlingTable();
                    scrollTargetKavlingRowIntoView(rowId);
                    showTargetToast('info', 'Menunggu ACC MKDT', 'Kavling ini sudah memiliki pengajuan ubah tipe yang masih pending.');
                    return;
                }

                if (resolveRowHasActiveConsumer(matchedRow)) {
                    renderTargetKavlingTable();
                    scrollTargetKavlingRowIntoView(rowId);
                    showTargetToast('warning', 'Kavling memiliki konsumen aktif', 'Kavling dengan konsumen aktif tidak dapat diajukan perubahan tipe.');
                    return;
                }

                if (canRequestTarget()) {
                    targetState.requestingTargetKavlingId = rowId;
                }
                renderTargetKavlingTable();
                scrollTargetKavlingRowIntoView(rowId);
            };

            loadTargetDetailByYear(resolvedYear, onBothLoaded);
            loadTargetRequests(onBothLoaded);
        });
    }

    function getBarSelectedTipePayload() {
        const mode = $('input[name="target_bar_tipe_source"]:checked').val() || 'master';
        if (mode === 'manual') {
            const tipeManual = String($('#target-bar-tipe-manual').val() || '').trim();
            if (!tipeManual) {
                return { error: 'Isi tipe target manual terlebih dahulu (contoh: 36/72).', focus: '#target-bar-tipe-manual' };
            }
            return { tipe_manual: tipeManual };
        }

        const idTipe = parseInt($('#target-bar-tipe-select').val() || '0', 10);
        if (!idTipe || idTipe <= 0) {
            return { error: 'Pilih Master Tipe Aktif terlebih dahulu atau gunakan Ketik Manual.', focus: '#target-bar-tipe-select' };
        }
        return { id_tipe: idTipe };
    }

    function handleTargetCanvasClick(sh, idKavlingRaw) {
        if (!window.targetInteractiveMode) return;
        if (!sh || !sh.id || !String(sh.id).startsWith('kav') || !sh.data || sh.data.tipe !== 'kavling') {
            return;
        }

        const kavlingId = parseInt(idKavlingRaw || String(sh.id).replace('kav', ''), 10);
        if (!kavlingId || kavlingId <= 0) return;

        if (targetState.isSavingStaged) {
            return;
        }

        const namaJalan = String(sh.data.nama_jalan || '').trim();
        const noKavling = String(sh.data.no_kavling || kavlingId).trim();
        const kavlingLabel = (namaJalan ? namaJalan + ' ' : '') + 'No. ' + noKavling;

        if (resolveShapeHasActiveConsumer(sh, kavlingId)) {
            showTargetToast(
                'warning',
                'Kavling memiliki konsumen aktif',
                kavlingLabel + ' sudah memiliki konsumen dan tidak dapat dimasukkan ke target.'
            );
            return;
        }

        const tahunTarget = parseInt($('#target-bar-year').val() || targetState.activeYear, 10);
        const alreadyInTarget = (targetState.kavlings || []).some(function(row) {
            return Number(row.id_kavling) === kavlingId;
        });
        if (alreadyInTarget) {
            showTargetToast(
                'info',
                'Sudah ada di Target ' + tahunTarget,
                kavlingLabel + ' sudah terdaftar pada target tahun aktif.'
            );
            return;
        }

        const existingIdx = targetState.stagedKavlings.findIndex(function(item) {
            return Number(item.id_kavling) === kavlingId;
        });

        if (existingIdx >= 0) {
            targetState.stagedKavlings.splice(existingIdx, 1);
            renderTargetStagedList();
            renderTargetStagedCanvasHighlights();
            return;
        }

        let points = (sh && Array.isArray(sh.points) && sh.points.length >= 6) ? sh.points : null;
        if (!points && typeof findSiteplanLocationNode === 'function') {
            const node = findSiteplanLocationNode('kavling', kavlingId);
            if (node) {
                points = typeof node.points === 'function'
                    ? node.points()
                    : (typeof node.getAttr === 'function' ? node.getAttr('points') : null);
            }
        }

        targetState.stagedKavlings.push({
            id_kavling: kavlingId,
            kavling_label: kavlingLabel,
            nama_jalan: namaJalan,
            no_kavling: noKavling,
            points: points,
            shape: sh
        });

        renderTargetStagedList();
        renderTargetStagedCanvasHighlights();
    }

    function submitStagedKavlings() {
        if (targetState.isSavingStaged) return;

        const staged = Array.isArray(targetState.stagedKavlings) ? targetState.stagedKavlings : [];
        if (staged.length === 0) {
            showTargetToast('warning', 'Belum ada kavling terpilih', 'Klik minimal 1 kavling kosong di kanvas terlebih dahulu.');
            return;
        }

        const tahunTarget = parseInt($('#target-bar-year').val() || targetState.activeYear, 10);
        if (!tahunTarget || tahunTarget < 2000 || tahunTarget > 2100) {
            showTargetToast('warning', 'Tahun target tidak valid', 'Pilih tahun target antara 2000 hingga 2100.');
            $('#target-bar-year').focus();
            return;
        }

        const tipePayload = getBarSelectedTipePayload();
        if (tipePayload.error) {
            showTargetToast('warning', 'Tipe target belum dipilih', tipePayload.error);
            if (tipePayload.focus) $(tipePayload.focus).focus();
            return;
        }

        const idKavlings = staged.map(function(item) {
            return Number(item.id_kavling);
        }).filter(function(id) {
            return id > 0;
        });

        const payload = Object.assign({
            [csrfName]: csrfHash,
            id_proyek: currentProyekId(),
            tahun_target: tahunTarget,
            id_kavlings: idKavlings
        }, tipePayload);

        const $btn = $('#target-bar-save-staged-btn');
        targetState.isSavingStaged = true;
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Menyimpan...');
        $('#target-bar-staged-reset-btn').prop('disabled', true);

        $.ajax({
            url: base_url + 'api/target/add-kavling',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (!res || !res.success) {
                    showTargetToast('error', 'Gagal menyimpan target', (res && res.messages) || '');
                    return;
                }

                const savedItems = staged.slice();
                targetState.stagedKavlings = [];
                clearTargetStagedCanvasHighlights();
                renderTargetStagedList();

                applyTargetDetailData(res.data, tahunTarget);
                syncSidebarTargetFilter(tahunTarget, false);

                if (savedItems.length === 1) {
                    flashTargetShapeHighlight(savedItems[0].shape, savedItems[0].id_kavling);
                }

                const summary = res.data && res.data.batch_summary ? res.data.batch_summary : null;
                const addedCount = summary && typeof summary.added_count !== 'undefined'
                    ? Number(summary.added_count)
                    : savedItems.length;
                const toastTitle = addedCount + ' kavling ditambahkan ke Target ' + tahunTarget;
                const toastSub = (res && res.messages) ? String(res.messages) : '';

                showTargetToast('success', toastTitle, toastSub);

                if (typeof window.scheduleTargetCanvasRefresh === 'function') {
                    window.scheduleTargetCanvasRefresh(250);
                }
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal menyimpan kavling terpilih ke target.');
                showTargetToast('warning', 'Tidak dapat menyimpan target', msg);
            },
            complete: function() {
                targetState.isSavingStaged = false;
                $btn.html('<i class="fas fa-check-circle mr-25"></i>Simpan ke Target');
                renderTargetStagedList();
            }
        });
    }

    function save_target_siteplan() {
        const ids = (targetState.kavlings || []).map(function(row) {
            return row.id_kavling;
        });

        if (ids.length === 0) {
            showTargetToast('warning', 'Belum ada kavling target', 'Tambahkan minimal 1 kavling target sebelum menyimpan catatan.');
            return;
        }

        const payload = {
            [csrfName]: csrfHash,
            id_target: $('#target-id_target').val() || '',
            id_proyek: currentProyekId(),
            tahun_target: targetState.activeYear,
            deskripsi: String($('#target-deskripsi').val() || '').trim(),
            id_kavling: ids.join(';')
        };

        $.ajax({
            url: base_url + 'api/target/save',
            type: 'post',
            data: payload,
            dataType: 'json',
            beforeSend: function() {
                $('#target-save-btn').html('Menyimpan <i class="fa fa-spinner fa-spin"></i>').addClass('disabled');
            },
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (res && res.success) {
                    applyTargetDetailData(res.data, targetState.activeYear);
                    loadTargetList();
                    if (typeof window.scheduleTargetCanvasRefresh === 'function') {
                        window.scheduleTargetCanvasRefresh(250);
                    }
                    showTargetToast('success', res.messages || 'Target berhasil disimpan');
                } else {
                    showTargetToast('error', (res && res.messages) || 'Gagal menyimpan catatan target.');
                }
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal menyimpan catatan target.');
                showTargetToast('error', 'Gagal menyimpan target', msg);
            },
            complete: function() {
                $('#target-save-btn').html('<i class="fas fa-save mr-25"></i>Simpan Catatan').removeClass('disabled');
            }
        });
    }

    function show_target_history(idTarget) {
        if (!idTarget) return;
        $.ajax({
            url: base_url + 'api/target/history',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_target: idTarget
            },
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                targetState.history = Array.isArray(res && res.data) ? res.data : [];
                render_target_history(targetState.history);
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat histori.');
                showTargetToast('error', 'Gagal memuat histori', msg);
            }
        });
    }

    function setEditModalTipeSource(mode) {
        const resolved = mode === 'manual' ? 'manual' : 'master';
        $('input[name="target_edit_tipe_source"][value="' + resolved + '"]').prop('checked', true);
        $('.target-edit-source-option').removeClass('is-active');
        $('input[name="target_edit_tipe_source"]:checked').closest('.target-edit-source-option').addClass('is-active');

        if (resolved === 'manual') {
            $('#target-edit-master-wrap').attr('hidden', true);
            $('#target-edit-manual-wrap').removeAttr('hidden');
        } else {
            $('#target-edit-manual-wrap').attr('hidden', true);
            $('#target-edit-master-wrap').removeAttr('hidden');
            initTargetEditTipeSelect2();
        }
    }

    function openTargetEditTipeModal(idTargetKavlings) {
        if (!canManageTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Hanya MKDT atau Admin yang dapat mengubah tipe target.');
            return;
        }

        const requestedSet = new Set((Array.isArray(idTargetKavlings) ? idTargetKavlings : [idTargetKavlings]).map(Number).filter(function(id) {
            return id > 0;
        }));
        const rows = (targetState.kavlings || []).filter(function(row) {
            return requestedSet.has(Number(row.id_target_kavling)) && canEditTargetRow(row);
        });

        if (rows.length === 0) {
            showTargetToast('warning', 'Tidak ada kavling valid', 'Pilih minimal 1 kavling tanpa konsumen aktif untuk diubah tipenya.');
            return;
        }

        targetState.editModalTargetKavlingIds = rows.map(function(row) {
            return Number(row.id_target_kavling);
        });

        const isBatch = rows.length > 1;
        $('#target-edit-tipe-modal-heading').text(
            isBatch
                ? ('Ubah Tipe Target (' + rows.length + ' Kavling)')
                : 'Ubah Tipe Target Kavling'
        );

        const $summary = $('#target-edit-tipe-kavling-summary');
        if (!isBatch) {
            const row = rows[0];
            const currentKavTipe = formatKavlingMasterTipe(row);
            const currentTargetTipe = row.tipe_label || row.tipe_manual || '-';
            $summary.html(
                '<div class="target-edit-single-box">'
                + '<div class="target-edit-single-title">' + target_escape_html(formatKavlingLocation(row)) + '</div>'
                + '<div class="target-edit-single-meta">Tipe Rumah Saat Ini: <strong>' + target_escape_html(currentKavTipe) + '</strong></div>'
                + '<div class="target-edit-single-meta">Tipe Target Sekarang: <strong>' + target_escape_html(currentTargetTipe) + '</strong></div>'
                + '</div>'
            );
        } else {
            const chipsHtml = rows.map(function(row) {
                return '<span class="target-edit-batch-chip">' + target_escape_html(formatKavlingLocation(row)) + '</span>';
            }).join('');
            $summary.html(
                '<div class="target-edit-batch-head">'
                + '<span class="target-pill target-pill-primary">' + rows.length + ' kavling dipilih</span>'
                + '<span class="target-kavling-sub">Tahun Target ' + target_escape_html(targetState.activeYear) + '</span>'
                + '</div>'
                + '<div class="target-edit-batch-list">' + chipsHtml + '</div>'
            );
        }

        initTargetEditTipeSelect2();
        const $sel = $('#target-edit-tipe-select');
        const $manualInput = $('#target-edit-tipe-manual');

        if (!isBatch) {
            const row = rows[0];
            const rowIdTipe = Number(row.id_tipe || 0);
            const isActiveTipe = rowIdTipe > 0 && (row.target_tipe_is_active === null || Number(row.target_tipe_is_active) === 1);

            if (rowIdTipe > 0) {
                setEditModalTipeSource('master');
                $manualInput.val('');
                if (isActiveTipe) {
                    const cached = targetState.tipeMap[String(rowIdTipe)] || {
                        id_tipe: rowIdTipe,
                        tipe_rumah: row.target_tipe_rumah || row.tipe_label || ('Tipe #' + rowIdTipe),
                        no_tipe_rumah: row.target_no_tipe_rumah || ''
                    };
                    cacheTipeListToMap([cached]);
                    const optText = formatTipeSelectText(cached);
                    $sel.empty().append(new Option(optText, String(rowIdTipe), true, true)).trigger('change');
                } else {
                    $sel.val(null).trigger('change');
                }
            } else {
                $sel.val(null).trigger('change');
                setEditModalTipeSource('manual');
                $manualInput.val(row.tipe_manual || row.tipe_label || '');
            }
        } else {
            $sel.val(null).trigger('change');
            $manualInput.val('');
            setEditModalTipeSource('master');
        }

        $('#modal-target-edit-tipe').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function submitEditTipeModal() {
        if (targetState.isSubmittingEditTipe) return;

        const ids = Array.isArray(targetState.editModalTargetKavlingIds)
            ? targetState.editModalTargetKavlingIds.map(Number).filter(function(id) { return id > 0; })
            : [];
        if (ids.length === 0) {
            showTargetToast('warning', 'Tidak ada kavling dipilih', 'Pilih minimal 1 kavling terlebih dahulu.');
            return;
        }

        const mode = $('input[name="target_edit_tipe_source"]:checked').val() || 'master';
        const payload = {
            [csrfName]: csrfHash,
            id_target_kavlings: ids
        };
        if (ids.length === 1) {
            payload.id_target_kavling = ids[0];
        }

        if (mode === 'manual') {
            const val = String($('#target-edit-tipe-manual').val() || '').trim();
            if (!val) {
                showTargetToast('warning', 'Tipe manual kosong', 'Masukkan nama/kode tipe target.');
                $('#target-edit-tipe-manual').focus();
                return;
            }
            payload.tipe_manual = val;
        } else {
            const idTipe = parseInt($('#target-edit-tipe-select').val() || '0', 10);
            if (!idTipe || idTipe <= 0) {
                showTargetToast('warning', 'Tipe master belum dipilih', 'Pilih tipe rumah aktif terlebih dahulu.');
                $('#target-edit-tipe-select').select2('open');
                return;
            }
            payload.id_tipe = idTipe;
        }

        const $btn = $('#target-edit-tipe-submit-btn');
        targetState.isSubmittingEditTipe = true;
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Menyimpan...');

        $.ajax({
            url: base_url + 'api/target/update-kavling-tipe',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (!res || !res.success) {
                    showTargetToast('error', 'Gagal memperbarui tipe', (res && res.messages) || '');
                    return;
                }
                targetState.selectedTargetKavlingIds = [];
                targetState.editModalTargetKavlingIds = [];
                $('#modal-target-edit-tipe').modal('hide');
                applyTargetDetailData(res.data, targetState.activeYear);
                if (typeof window.scheduleTargetCanvasRefresh === 'function') {
                    window.scheduleTargetCanvasRefresh(250);
                }
                showTargetToast('success', res.messages || 'Tipe target diperbarui');
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memperbarui tipe target.');
                showTargetToast('error', 'Gagal memperbarui tipe', msg);
            },
            complete: function() {
                targetState.isSubmittingEditTipe = false;
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-25"></i>Simpan Perubahan Tipe');
            }
        });
    }

    function confirmRemoveTargetKavling(idTargetKavling, label) {
        const executeDelete = function() {
            $.ajax({
                url: base_url + 'api/target/remove-kavling',
                type: 'post',
                data: {
                    [csrfName]: csrfHash,
                    id_target_kavling: idTargetKavling
                },
                dataType: 'json',
                success: function(res) {
                    updateTargetCsrf(res && res.token);
                    if (!res || !res.success) {
                        showTargetToast('error', 'Gagal menghapus kavling', (res && res.messages) || '');
                        return;
                    }
                    applyTargetDetailData(res.data, targetState.activeYear);
                    if (typeof window.scheduleTargetCanvasRefresh === 'function') {
                        window.scheduleTargetCanvasRefresh(250);
                    }
                    showTargetToast('success', res.messages || 'Kavling dihapus dari target');
                },
                error: function(xhr) {
                    const msg = extractTargetErrorMessage(xhr, 'Gagal menghapus kavling dari target.');
                    showTargetToast('error', 'Gagal menghapus kavling', msg);
                }
            });
        };

        if (typeof Swal === 'undefined') {
            if (window.confirm('Hapus ' + label + ' dari target tahun ' + targetState.activeYear + '?')) {
                executeDelete();
            }
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Hapus kavling dari target?',
            text: (label || 'Kavling') + ' akan dihapus dari target tahun ' + targetState.activeYear + '.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626'
        }).then(function(result) {
            if (result && result.isConfirmed) {
                executeDelete();
            }
        });
    }

    function submitPlanningKavlingChangeRequest(rows, options) {
        if (!canManageTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Hanya MKDT atau Admin yang dapat mengajukan ubah kavling ke Tim Planning.');
            return;
        }
        if (targetState.isSubmittingPlanningRequest) {
            return;
        }

        const idProyek = currentProyekId();
        if (!idProyek) {
            showTargetToast('warning', 'Proyek tidak ditemukan', 'Pilih proyek aktif terlebih dahulu.');
            return;
        }

        const inputRows = Array.isArray(rows) ? rows : (rows ? [rows] : []);
        const seenKavlingIds = new Set();
        const validRows = [];

        inputRows.forEach(function(row) {
            if (!row) return;
            const idKavling = Number(row.id_kavling || 0);
            if (idKavling <= 0 || seenKavlingIds.has(idKavling)) return;
            if (resolveRowHasActiveConsumer(row)) return;
            if (!isTargetRowTipeMismatch(row) || isPlanningRequestPending(row)) return;

            const targetIdTipe = Number(row.id_tipe || 0);
            const targetTipeManual = String(row.tipe_manual || '').trim();
            if (targetIdTipe <= 0 && !targetTipeManual) return;

            seenKavlingIds.add(idKavling);
            validRows.push(row);
        });

        if (validRows.length === 0) {
            showTargetToast(
                'warning',
                'Tidak ada kavling memenuhi syarat',
                'Pastikan kavling yang dipilih tidak memiliki konsumen aktif, memiliki perbedaan tipe dengan target, dan belum berstatus pending di Planning.'
            );
            return;
        }

        const executeSubmit = function(keterangan) {
            const items = validRows.map(function(row) {
                const targetIdTipe = Number(row.id_tipe || 0);
                return {
                    id_kavling: Number(row.id_kavling),
                    id_tipe: targetIdTipe > 0 ? targetIdTipe : null,
                    tipe_manual: targetIdTipe > 0 ? '' : String(row.tipe_manual || '').trim(),
                    keterangan: keterangan
                };
            });

            targetState.isSubmittingPlanningRequest = true;
            const $batchBtn = $('#target-btn-batch-req-planning');
            const prevBatchHtml = $batchBtn.html();
            $batchBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-25"></i>Mengirim...');
            $('.target-btn-req-planning').prop('disabled', true);

            $.ajax({
                url: base_url + 'api/kavling-request/submit',
                type: 'post',
                contentType: 'application/json; charset=utf-8',
                headers: {
                    'X-CSRF-TOKEN': csrfHash
                },
                data: JSON.stringify({
                    [csrfName]: csrfHash,
                    id_proyek: idProyek,
                    jenis_request: 'ubah_tipe',
                    items: items
                }),
                dataType: 'json',
                success: function(res) {
                    updateTargetCsrf(res && res.token);
                    if (!res || !res.success) {
                        showTargetToast('error', 'Gagal mengirim request ke Planning', (res && res.messages) || '');
                        return;
                    }

                    const resData = res.data || {};
                    const submittedCount = Number(resData.submitted_count || items.length);
                    const skippedCount = Number(resData.skipped_count || 0);
                    let toastMsg = res.messages || (submittedCount + ' pengajuan ubah kavling berhasil dikirim ke Tim Planning.');
                    if (skippedCount > 0 && toastMsg.toLowerCase().indexOf('dilewati') === -1) {
                        toastMsg += ' (' + skippedCount + ' kavling dilewati karena sudah pending.)';
                    }

                    targetState.selectedTargetKavlingIds = [];
                    loadTargetDetailByYear(targetState.activeYear);
                    showTargetToast('success', 'Request dikirim ke Planning', toastMsg);
                },
                error: function(xhr) {
                    const msg = extractTargetErrorMessage(xhr, 'Gagal mengirim pengajuan ubah kavling ke Tim Planning.');
                    showTargetToast('error', 'Gagal mengirim request', msg);
                },
                complete: function() {
                    targetState.isSubmittingPlanningRequest = false;
                    $batchBtn.prop('disabled', false).html(prevBatchHtml || '<i class="fas fa-paper-plane mr-25"></i>Request Ubah ke Planning');
                    $('.target-btn-req-planning').prop('disabled', false);
                }
            });
        };

        if (typeof Swal === 'undefined') {
            const plainSummary = validRows.map(function(row) {
                return formatKavlingLocation(row) + ': ' + formatKavlingMasterTipe(row) + ' -> ' + formatTargetRowTargetTipeLabel(row);
            }).join('\n');
            const note = window.prompt(
                'Ajukan perubahan tipe ' + validRows.length + ' kavling ke Tim Planning?\n\n'
                + plainSummary
                + '\n\nCatatan untuk Tim Planning (opsional):',
                ''
            );
            if (note !== null) {
                executeSubmit(String(note || '').trim());
            }
            return;
        }

        const opts = options || {};
        const skippedParts = [];
        if (Array.isArray(opts.skippedPending) && opts.skippedPending.length > 0) {
            skippedParts.push(opts.skippedPending.length + ' sudah pending');
        }
        if (Array.isArray(opts.skippedMatched) && opts.skippedMatched.length > 0) {
            skippedParts.push(opts.skippedMatched.length + ' tipe sudah sama');
        }
        const skippedNoticeHtml = skippedParts.length > 0
            ? '<div class="alert alert-warning py-50 px-75 mb-75 small text-left">'
                + '<i class="fas fa-info-circle mr-25"></i>'
                + target_escape_html('Sebagian kavling terpilih dilewati (' + skippedParts.join(', ') + ').')
                + '</div>'
            : '';

        const listItemsHtml = validRows.map(function(row) {
            const locLabel = formatKavlingLocation(row);
            const currentTipe = formatKavlingMasterTipe(row);
            const targetTipe = formatTargetRowTargetTipeLabel(row);
            return '<div class="target-planning-swal-item">'
                + '<span class="target-planning-swal-kav">' + target_escape_html(locLabel) + '</span>'
                + '<span class="target-planning-swal-diff">'
                + '<span class="target-planning-swal-old">' + target_escape_html(currentTipe) + '</span>'
                + '<i class="fas fa-long-arrow-alt-right mx-25"></i>'
                + '<span class="target-planning-swal-new">' + target_escape_html(targetTipe) + '</span>'
                + '</span>'
                + '</div>';
        }).join('');

        const titleText = validRows.length === 1
            ? 'Request Ubah Kavling ke Planning?'
            : ('Request Ubah ' + validRows.length + ' Kavling ke Planning?');

        Swal.fire({
            icon: 'question',
            title: titleText,
            html: '<div class="text-left">'
                + '<p class="small text-muted mb-75">Tim Planning akan menerima pengajuan perubahan master tipe kavling sesuai Target Siteplan berikut:</p>'
                + skippedNoticeHtml
                + '<div class="target-planning-swal-list">' + listItemsHtml + '</div>'
                + '</div>',
            input: 'textarea',
            inputLabel: 'Catatan untuk Tim Planning (opsional)',
            inputPlaceholder: 'Catatan untuk Tim Planning (opsional)',
            inputAttributes: {
                maxlength: 500,
                'aria-label': 'Catatan untuk Tim Planning (opsional)'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane mr-25"></i>Kirim ke Planning',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2057a3'
        }).then(function(result) {
            if (result && result.isConfirmed) {
                executeSubmit(String(result.value || '').trim());
            }
        });
    }

    function submitInlineRequestChange(idTargetKavling, $editor) {
        if (!canRequestTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Anda tidak memiliki izin mengajukan perubahan tipe.');
            return;
        }

        const mode = $editor.find('.target-req-mode').val();
        const catatanRequest = String($editor.find('.target-req-catatan-input').val() || '').trim();
        const payload = {
            [csrfName]: csrfHash,
            id_target_kavling: idTargetKavling
        };

        if (catatanRequest) {
            payload.catatan_request = catatanRequest;
        }

        const currentRow = (targetState.kavlings || []).find(function(k) {
            return Number(k.id_target_kavling) === Number(idTargetKavling);
        });

        if (mode === 'manual') {
            const val = String($editor.find('.target-req-manual-input').val() || '').trim();
            if (!val) {
                showTargetToast('warning', 'Tipe manual kosong', 'Masukkan tipe target usulan.');
                $editor.find('.target-req-manual-input').focus();
                return;
            }
            if (currentRow && !(currentRow.id_tipe && Number(currentRow.id_tipe) > 0)
                && String(currentRow.tipe_manual || '').trim() === val) {
                showTargetToast('warning', 'Tipe target sama', 'Tipe target usulan harus berbeda dari tipe target saat ini.');
                $editor.find('.target-req-manual-input').focus();
                return;
            }
            payload.tipe_manual = val;
        } else {
            const idTipe = parseInt($editor.find('.target-req-master-select').val() || '0', 10);
            if (!idTipe || idTipe <= 0) {
                showTargetToast('warning', 'Tipe master belum dipilih', 'Pilih tipe rumah aktif usulan terlebih dahulu.');
                $editor.find('.target-req-master-select').focus();
                return;
            }
            if (currentRow && Number(currentRow.id_tipe || 0) === idTipe && !currentRow.tipe_manual) {
                showTargetToast('warning', 'Tipe target sama', 'Tipe target usulan harus berbeda dari tipe target saat ini.');
                $editor.find('.target-req-master-select').focus();
                return;
            }
            payload.id_tipe = idTipe;
        }

        const $btn = $editor.find('.target-btn-submit-request');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: base_url + 'api/target/request-change',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (!res || !res.success) {
                    showTargetToast('error', 'Gagal mengirim pengajuan', (res && res.messages) || '');
                    return;
                }

                targetState.requestingTargetKavlingId = null;
                const data = res.data || {};
                const scopeTargetId = currentRow ? Number(currentRow.id_target) : (targetState.target ? Number(targetState.target.id_target) : null);
                if (Array.isArray(data.requests)) {
                    syncPendingRequestsMap(data.requests, scopeTargetId, null);
                }
                updateTargetPendingBadge(data.counts || null);
                renderTargetKavlingTable();
                loadTargetRequests();
                showTargetToast('success', res.messages || 'Pengajuan ubah tipe target berhasil dikirim');
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal mengirim pengajuan perubahan tipe.');
                showTargetToast('warning', 'Pengajuan ditolak', msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-25"></i>Kirim Request');
            }
        });
    }

    function formatRequestKavlingLocation(row) {
        if (!row) return '-';
        const cluster = String(row.nama_cluster || row.cluster || '').trim();
        const jalan = String(row.nama_jalan || row.jalan || '').trim();
        const noKav = String(row.no_kavling || row.kavling || '').trim();
        const parts = [];
        if (cluster) parts.push(cluster);
        if (jalan) parts.push(jalan);
        const prefix = parts.join(' · ');
        if (prefix && noKav) return prefix + ' · No. ' + noKav;
        if (noKav) return 'No. ' + noKav;
        return prefix || ('Kavling #' + (row.id_kavling || '-'));
    }

    function renderRequestStatusBadge(status) {
        const normalized = String(status || '').toLowerCase();
        if (normalized === 'approved') {
            return '<span class="target-pill target-req-badge-approved"><i class="fas fa-check-circle"></i> Disetujui</span>';
        }
        if (normalized === 'rejected') {
            return '<span class="target-pill target-req-badge-rejected"><i class="fas fa-times-circle"></i> Ditolak</span>';
        }
        return '<span class="target-pill target-req-badge-pending"><i class="fas fa-clock"></i> Pending</span>';
    }

    function renderTargetRequests() {
        const $tbody = $('#target-requests-tbody');
        if (!$tbody.length) return;

        const rows = Array.isArray(targetState.requests) ? targetState.requests : [];
        const counts = targetState.requestCounts || {};
        const pendingTotal = Number(counts.pending || 0);
        const manageAllowed = canManageTarget();

        $('#target-requests-summary').text(
            rows.length + ' pengajuan ditampilkan · ' + pendingTotal + ' pending'
        );

        if (rows.length === 0) {
            $tbody.html(
                '<tr><td colspan="5" class="text-center text-muted py-3">'
                + 'Belum ada pengajuan perubahan tipe target untuk filter yang dipilih.'
                + '</td></tr>'
            );
            return;
        }

        let html = '';
        rows.forEach(function(req) {
            const idRequest = Number(req.id_request || 0);
            const status = String(req.status || 'pending').toLowerCase();
            const oldLabel = req.old_tipe_label || req.old_tipe_manual || '-';
            const newLabel = req.new_tipe_label || req.new_tipe_manual || '-';
            const isInactiveNewTipe = req.new_id_tipe && req.new_tipe_is_active !== null && Number(req.new_tipe_is_active) !== 1;
            const inactiveBadge = isInactiveNewTipe
                ? ' <span class="target-pill target-pill-danger ml-25">Nonaktif</span>'
                : '';

            const masterTipeHtml = req.master_tipe_label
                ? '<div class="target-kavling-sub">Master Kavling: ' + target_escape_html(req.master_tipe_label) + '</div>'
                : '';

            const reqNoteHtml = req.catatan_request
                ? '<div class="target-req-note">"' + target_escape_html(req.catatan_request) + '"</div>'
                : '<div class="target-kavling-sub">Tanpa catatan</div>';

            let reviewerHtml = '';
            if (status !== 'pending') {
                const reviewer = req.approve_by_username || '-';
                const approveAt = req.approve_at ? formatTargetDateTime(req.approve_at) : '-';
                reviewerHtml += '<div class="target-kavling-sub mt-25">Oleh: <strong>' + target_escape_html(reviewer) + '</strong></div>'
                    + '<div class="target-kavling-sub">' + target_escape_html(approveAt) + '</div>';
                if (req.catatan_approval) {
                    reviewerHtml += '<div class="target-req-note">Catatan: ' + target_escape_html(req.catatan_approval) + '</div>';
                }
            }

            let actionHtml = '<span class="text-muted small">-</span>';
            if (manageAllowed && status === 'pending') {
                actionHtml = '<div class="target-req-action-group">'
                    + '<button type="button" class="btn btn-success btn-sm target-btn-approve-request" data-id="' + idRequest + '">'
                    + '<i class="fas fa-check mr-25"></i>ACC</button>'
                    + '<button type="button" class="btn btn-outline-danger btn-sm target-btn-reject-request" data-id="' + idRequest + '">'
                    + '<i class="fas fa-times mr-25"></i>Tolak</button>'
                    + '</div>';
            }

            html += '<tr data-id-request="' + idRequest + '">'
                + '<td>'
                + '<div><span class="target-pill target-pill-primary">Tahun ' + target_escape_html(req.tahun_target || '-') + '</span></div>'
                + '<div class="mt-25"><strong>' + target_escape_html(formatRequestKavlingLocation(req)) + '</strong></div>'
                + masterTipeHtml
                + '</td>'
                + '<td>'
                + '<div class="target-req-tipe-diff">'
                + '<span class="target-req-old">' + target_escape_html(oldLabel) + '</span>'
                + '<span class="target-req-arrow"><i class="fas fa-long-arrow-alt-right"></i></span>'
                + '<span class="target-req-new">' + target_escape_html(newLabel) + '</span>'
                + inactiveBadge
                + '</div>'
                + '</td>'
                + '<td>'
                + '<div><strong>' + target_escape_html(req.request_by_username || '-') + '</strong></div>'
                + '<div class="target-kavling-sub">' + target_escape_html(formatTargetDateTime(req.request_at)) + '</div>'
                + reqNoteHtml
                + '</td>'
                + '<td>'
                + renderRequestStatusBadge(status)
                + reviewerHtml
                + '</td>'
                + '<td class="text-center">' + actionHtml + '</td>'
                + '</tr>';
        });

        $tbody.html(html);
    }

    function loadTargetRequests(callback) {
        const idProyek = currentProyekId();
        if (!idProyek) {
            if (typeof callback === 'function') callback([]);
            return;
        }

        const statusFilter = String(targetState.requestsStatusFilter || 'all');
        const yearFilter = String(targetState.requestsYearFilter || '').trim();
        const payload = {
            [csrfName]: csrfHash,
            id_proyek: idProyek,
            status: statusFilter
        };
        if (yearFilter && Number(yearFilter) >= 2000 && Number(yearFilter) <= 2100) {
            payload.tahun_target = Number(yearFilter);
        }

        $.ajax({
            url: base_url + 'api/target/requests',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function(res) {
                updateTargetCsrf(res && res.token);
                if (!res || !res.success) {
                    showTargetToast('error', 'Gagal memuat request', (res && res.messages) || '');
                    if (typeof callback === 'function') callback([]);
                    return;
                }

                const data = res.data || {};
                const rows = Array.isArray(data.requests) ? data.requests : [];
                targetState.requests = rows;
                if (statusFilter === 'all' || statusFilter === 'pending') {
                    syncPendingRequestsMap(rows, null, yearFilter ? Number(yearFilter) : null);
                }
                updateTargetPendingBadge(data.counts || null);
                renderTargetRequests();
                renderTargetKavlingTable();
                if (typeof callback === 'function') callback(rows);
            },
            error: function(xhr) {
                const msg = extractTargetErrorMessage(xhr, 'Gagal memuat daftar pengajuan perubahan tipe target.');
                showTargetToast('error', 'Gagal memuat request', msg);
                if (typeof callback === 'function') callback([]);
            }
        });
    }

    function reviewTargetRequest(idRequest, decision) {
        if (!canManageTarget()) {
            showTargetToast('warning', 'Akses terbatas', 'Hanya MKDT atau Admin yang dapat memproses persetujuan request.');
            return;
        }

        const req = (targetState.requests || []).find(function(item) {
            return Number(item.id_request) === Number(idRequest);
        });
        const isApprove = decision === 'approved';
        const kavLabel = req ? formatRequestKavlingLocation(req) : ('Request #' + idRequest);
        const tipeSummary = req
            ? (req.old_tipe_label || '-') + ' → ' + (req.new_tipe_label || '-')
            : '';

        const executeReview = function(catatanApproval) {
            const payload = {
                [csrfName]: csrfHash,
                id_request: idRequest,
                decision: decision
            };
            if (catatanApproval) {
                payload.catatan_approval = catatanApproval;
            }

            $.ajax({
                url: base_url + 'api/target/review-request',
                type: 'post',
                data: payload,
                dataType: 'json',
                success: function(res) {
                    updateTargetCsrf(res && res.token);
                    if (!res || !res.success) {
                        showTargetToast('error', 'Gagal memproses request', (res && res.messages) || '');
                        return;
                    }

                    const data = res.data || {};
                    const reviewedTarget = data.target || null;
                    if (Array.isArray(data.requests) && reviewedTarget && reviewedTarget.id_target) {
                        syncPendingRequestsMap(data.requests, Number(reviewedTarget.id_target), null);
                    }

                    if (reviewedTarget && Number(reviewedTarget.tahun_target) === Number(targetState.activeYear)) {
                        applyTargetDetailData(data, targetState.activeYear);
                    } else {
                        loadTargetDetailByYear(targetState.activeYear);
                    }

                    updateTargetPendingBadge(data.counts || null);
                    loadTargetRequests();

                    if (isApprove && typeof window.scheduleTargetCanvasRefresh === 'function') {
                        window.scheduleTargetCanvasRefresh(150);
                    }

                    showTargetToast(
                        'success',
                        res.messages || (isApprove ? 'Pengajuan perubahan tipe disetujui' : 'Pengajuan perubahan tipe ditolak')
                    );
                },
                error: function(xhr) {
                    const msg = extractTargetErrorMessage(xhr, 'Gagal memproses review pengajuan.');
                    showTargetToast('error', 'Gagal memproses request', msg);
                }
            });
        };

        if (typeof Swal === 'undefined') {
            const promptMsg = (isApprove ? 'ACC perubahan tipe ' : 'Tolak pengajuan perubahan tipe ')
                + kavLabel + (tipeSummary ? ' (' + tipeSummary + ')' : '')
                + '?\nCatatan approval (opsional):';
            const note = window.prompt(promptMsg, '');
            if (note !== null) {
                executeReview(String(note || '').trim());
            }
            return;
        }

        Swal.fire({
            icon: isApprove ? 'question' : 'warning',
            title: isApprove ? 'ACC Perubahan Tipe Target?' : 'Tolak Pengajuan Perubahan?',
            html: '<div class="text-left small">'
                + '<div><strong>Kavling:</strong> ' + target_escape_html(kavLabel) + '</div>'
                + (tipeSummary ? '<div class="mt-25"><strong>Perubahan:</strong> ' + target_escape_html(tipeSummary) + '</div>' : '')
                + (isApprove ? '<div class="mt-50 text-muted">Catatan: ACC hanya memperbarui tipe pada target siteplan, tidak mengubah tipe master kavling.</div>' : '')
                + '</div>',
            input: 'textarea',
            inputLabel: 'Catatan Approval (opsional)',
            inputPlaceholder: isApprove ? 'Contoh: Disetujui sesuai revisi siteplan' : 'Alasan penolakan (opsional)',
            inputAttributes: {
                maxlength: 500
            },
            showCancelButton: true,
            confirmButtonText: isApprove ? 'Ya, ACC' : 'Ya, Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: isApprove ? '#15803d' : '#dc2626'
        }).then(function(result) {
            if (result && result.isConfirmed) {
                executeReview(String(result.value || '').trim());
            }
        });
    }

    function bindTargetEvents() {
        mountTargetInteractiveBar();

        $(document).on('change', 'input[name="target_bar_tipe_source"]', function() {
            const val = $(this).val();
            $('.target-source-option').removeClass('is-active');
            $(this).closest('.target-source-option').addClass('is-active');

            if (val === 'manual') {
                $('#target-bar-master-wrap').attr('hidden', true);
                $('#target-bar-manual-wrap').removeAttr('hidden');
                $('#target-bar-tipe-label').text('Ketik Tipe Target Manual');
                renderTargetBarTipePreview();
                $('#target-bar-tipe-manual').focus();
            } else {
                $('#target-bar-manual-wrap').attr('hidden', true);
                $('#target-bar-master-wrap').removeAttr('hidden');
                $('#target-bar-tipe-label').text('Pilih Master Tipe');
                initTargetBarTipeSelect2();
                renderTargetBarTipePreview();
            }
        });

        $(document).on('change', '#target-bar-year', function() {
            const year = parseInt($(this).val() || '0', 10);
            if (year >= 2000 && year <= 2100) {
                syncSidebarTargetFilter(year, true);
                loadTargetDetailByYear(year);
            }
        });

        $(document).on('click', '.target-preview-thumb-trigger', function() {
            const tipeId = $(this).data('tipeId');
            const focusKey = $(this).data('focus') || '';
            if (tipeId) {
                openTargetTipeSpecModal(tipeId, focusKey);
            }
        });

        $(document).on('click', '.target-staged-remove-btn', function() {
            if (targetState.isSavingStaged) return;
            const idKav = Number($(this).data('id'));
            targetState.stagedKavlings = (targetState.stagedKavlings || []).filter(function(item) {
                return Number(item.id_kavling) !== idKav;
            });
            renderTargetStagedList();
            renderTargetStagedCanvasHighlights();
        });

        $(document).on('click', '#target-bar-staged-reset-btn', function() {
            if (targetState.isSavingStaged) return;
            targetState.stagedKavlings = [];
            renderTargetStagedList();
            renderTargetStagedCanvasHighlights();
        });

        $(document).on('click', '#target-bar-save-staged-btn', function() {
            submitStagedKavlings();
        });

        $(document).on('click', '#target-bar-manage-btn', function() {
            openTargetManageModal('kavling');
        });

        $(document).on('click', '#target-bar-exit-btn', function() {
            exitTargetSetMode();
        });

        $(document).on('click', '#target-btn-add-from-canvas', function() {
            enterTargetSetMode(targetState.activeYear);
        });

        $(document).on('change', '#target-modal-year-select, #target-history-year-select', function() {
            const year = parseInt($(this).val() || '0', 10);
            if (year >= 2000 && year <= 2100) {
                loadTargetDetailByYear(year);
            }
        });

        $(document).on('click', '#target-history-refresh-btn', function() {
            if (targetState.target && targetState.target.id_target) {
                show_target_history(targetState.target.id_target);
            } else {
                loadTargetDetailByYear(targetState.activeYear);
            }
        });

        $(document).on('input', '#target-kavling-search', function() {
            targetState.searchQuery = $(this).val() || '';
            renderTargetKavlingTable();
        });

        $(document).on('click', '.target-btn-preview-tipe', function() {
            const tipeId = Number($(this).data('idTipe') || $(this).attr('data-id-tipe') || 0);
            const focusKey = $(this).data('focus') || '';
            if (tipeId > 0) {
                openTargetTipeSpecModal(tipeId, focusKey);
            }
        });

        const getVisibleTargetRowsFromDom = function() {
            const visibleRows = [];
            $('#target-kavling-tbody tr[data-id-target-kavling]').each(function() {
                const rowId = Number($(this).data('idTargetKavling') || $(this).attr('data-id-target-kavling') || 0);
                const match = (targetState.kavlings || []).find(function(r) {
                    return Number(r.id_target_kavling) === rowId;
                });
                if (match) visibleRows.push(match);
            });
            return visibleRows;
        };

        $(document).on('change', '#target-check-all', function() {
            const checked = $(this).prop('checked') === true;
            const selectedSet = new Set((targetState.selectedTargetKavlingIds || []).map(Number));

            $('#target-kavling-tbody .target-row-check:not(:disabled)').each(function() {
                const rowId = Number($(this).data('id') || $(this).val() || 0);
                if (rowId <= 0) return;
                $(this).prop('checked', checked);
                if (checked) {
                    selectedSet.add(rowId);
                } else {
                    selectedSet.delete(rowId);
                }
            });

            targetState.selectedTargetKavlingIds = Array.from(selectedSet);
            syncTargetBatchToolbarAndCheckAll(getVisibleTargetRowsFromDom());
        });

        $(document).on('change', '.target-row-check', function() {
            const rowId = Number($(this).data('id') || $(this).val() || 0);
            if (rowId <= 0) return;
            const selectedSet = new Set((targetState.selectedTargetKavlingIds || []).map(Number));
            if ($(this).prop('checked') === true) {
                selectedSet.add(rowId);
            } else {
                selectedSet.delete(rowId);
            }
            targetState.selectedTargetKavlingIds = Array.from(selectedSet);
            syncTargetBatchToolbarAndCheckAll(getVisibleTargetRowsFromDom());
        });

        $(document).on('click', '#target-btn-batch-clear', function() {
            targetState.selectedTargetKavlingIds = [];
            $('#target-kavling-tbody .target-row-check').prop('checked', false);
            renderTargetKavlingTable();
        });

        $(document).on('click', '#target-btn-batch-edit-tipe', function() {
            openTargetEditTipeModal(targetState.selectedTargetKavlingIds);
        });

        $(document).on('click', '#target-btn-batch-req-planning', function() {
            if ($(this).prop('disabled') || targetState.isSubmittingPlanningRequest) return;
            const selectedSet = new Set((targetState.selectedTargetKavlingIds || []).map(Number));
            const selectedRows = (targetState.kavlings || []).filter(function(row) {
                return selectedSet.has(Number(row.id_target_kavling));
            });

            if (selectedRows.length === 0) {
                showTargetToast('warning', 'Belum ada kavling dipilih', 'Centang minimal 1 kavling terlebih dahulu.');
                return;
            }

            const eligible = [];
            const skippedPending = [];
            const skippedMatched = [];

            selectedRows.forEach(function(row) {
                if (resolveRowHasActiveConsumer(row)) {
                    return;
                }
                if (!isTargetRowTipeMismatch(row)) {
                    skippedMatched.push(row);
                    return;
                }
                if (isPlanningRequestPending(row)) {
                    skippedPending.push(row);
                    return;
                }
                eligible.push(row);
            });

            if (eligible.length === 0) {
                if (skippedPending.length > 0 && skippedMatched.length === 0) {
                    showTargetToast(
                        'info',
                        'Sudah berstatus Pending',
                        'Semua kavling berbeda tipe yang dipilih sudah memiliki pengajuan ke Planning yang berstatus pending.'
                    );
                } else if (skippedPending.length > 0) {
                    showTargetToast(
                        'warning',
                        'Tidak ada kavling siap diajukan',
                        skippedPending.length + ' kavling masih berstatus pending di Planning dan ' + skippedMatched.length + ' kavling tipenya sudah sama.'
                    );
                } else {
                    showTargetToast(
                        'warning',
                        'Tidak ada kavling berbeda tipe',
                        'Pilih kavling yang tipe targetnya berbeda dari tipe kavling saat ini dan belum berstatus pending di Planning.'
                    );
                }
                return;
            }

            submitPlanningKavlingChangeRequest(eligible, {
                skippedPending: skippedPending,
                skippedMatched: skippedMatched
            });
        });

        $(document).on('click', '.target-btn-req-planning', function() {
            if ($(this).prop('disabled') || targetState.isSubmittingPlanningRequest) return;
            const rowId = Number($(this).attr('data-id-target-kavling') || $(this).data('idTargetKavling') || 0);
            if (rowId <= 0) return;
            const row = (targetState.kavlings || []).find(function(item) {
                return Number(item.id_target_kavling) === rowId;
            });
            if (row) {
                submitPlanningKavlingChangeRequest([row]);
            }
        });

        $(document).on('click', '.target-btn-edit-tipe', function() {
            if ($(this).prop('disabled')) return;
            const rowId = Number($(this).data('id') || 0);
            if (rowId > 0) {
                openTargetEditTipeModal([rowId]);
            }
        });

        $(document).on('change', 'input[name="target_edit_tipe_source"]', function() {
            const mode = $(this).val();
            setEditModalTipeSource(mode);
            if (mode === 'manual') {
                $('#target-edit-tipe-manual').focus();
            }
        });

        $(document).on('click', '#target-edit-tipe-submit-btn', function() {
            submitEditTipeModal();
        });

        $(document).on('keydown', '#target-edit-tipe-manual', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitEditTipeModal();
            }
        });

        $(document).on('show.bs.modal', '#modal-target-tipe-spec, #modal-target-edit-tipe', function() {
            const openCount = $('.modal.show').length;
            if (openCount > 0) {
                const zIndex = 1055 + (10 * openCount);
                $(this).css('z-index', zIndex);
                setTimeout(function() {
                    $('.modal-backdrop').not('.target-stacked-backdrop').last()
                        .css('z-index', zIndex - 1)
                        .addClass('target-stacked-backdrop');
                }, 0);
            }
        });

        $(document).on('hide.bs.modal', '#modal-target-edit-tipe', function() {
            const $sel = $('#target-edit-tipe-select');
            if ($sel.length && $sel.hasClass('select2-hidden-accessible')) {
                try {
                    $sel.select2('close');
                } catch (e) {}
            }
        });

        $(document).on('hidden.bs.modal', '#modal-target-tipe-spec, #modal-target-edit-tipe', function() {
            $(this).css('z-index', '');
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });

        $(document).on('click', '.target-btn-request-change', function() {
            if ($(this).prop('disabled')) return;
            targetState.editingTargetKavlingId = null;
            targetState.requestingTargetKavlingId = Number($(this).data('id'));
            if (targetState.activeTipes.length === 0) {
                loadActiveTipes(false, function() {
                    renderTargetKavlingTable();
                });
            } else {
                renderTargetKavlingTable();
            }
        });

        $(document).on('click', '.target-btn-cancel-request', function() {
            targetState.requestingTargetKavlingId = null;
            renderTargetKavlingTable();
        });

        $(document).on('change', '.target-req-mode', function() {
            const mode = $(this).val();
            const $editor = $(this).closest('.target-inline-req-box');
            if (mode === 'manual') {
                $editor.find('.target-req-master-select').attr('hidden', true);
                $editor.find('.target-req-manual-input').removeAttr('hidden').focus();
            } else {
                $editor.find('.target-req-manual-input').attr('hidden', true);
                $editor.find('.target-req-master-select').removeAttr('hidden').focus();
            }
        });

        $(document).on('click', '.target-btn-submit-request', function() {
            const rowId = Number($(this).data('id'));
            const $editor = $(this).closest('.target-inline-req-box');
            submitInlineRequestChange(rowId, $editor);
        });

        $(document).on('click', '.target-btn-remove-kavling', function() {
            const rowId = Number($(this).data('id'));
            const label = String($(this).data('label') || '');
            confirmRemoveTargetKavling(rowId, label);
        });

        $(document).on('change', '#target-requests-status-filter', function() {
            targetState.requestsStatusFilter = String($(this).val() || 'all');
            loadTargetRequests();
        });

        $(document).on('change', '#target-requests-year-select', function() {
            targetState.requestsYearFilter = String($(this).val() || '');
            loadTargetRequests();
        });

        $(document).on('click', '#target-requests-refresh-btn', function() {
            loadTargetRequests();
        });

        $(document).on('shown.bs.tab', '#tab-target-requests', function() {
            loadTargetRequests();
        });

        $(document).on('click', '.target-btn-approve-request', function() {
            const idRequest = Number($(this).data('id'));
            if (idRequest > 0) {
                reviewTargetRequest(idRequest, 'approved');
            }
        });

        $(document).on('click', '.target-btn-reject-request', function() {
            const idRequest = Number($(this).data('id'));
            if (idRequest > 0) {
                reviewTargetRequest(idRequest, 'rejected');
            }
        });
    }

    $(document).ready(bindTargetEvents);

    // Global exports for menu onclicks, master.js canvas hooks, and backward compatibility
    window.open_target_siteplan = open_target_siteplan;
    window.open_target_history = open_target_history;
    window.open_target_request = open_target_request;
    window.exitTargetSetMode = exitTargetSetMode;
    window.handleTargetCanvasClick = handleTargetCanvasClick;
    window.renderTargetStagedCanvasHighlights = renderTargetStagedCanvasHighlights;
    window.save_target_siteplan = save_target_siteplan;
    window.show_target_history = show_target_history;
    window.render_target_history = render_target_history;
    window.target_escape_html = target_escape_html;
})(jQuery);
