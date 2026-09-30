(function ($) {
    'use strict';
    const config = window.SIGAPP.bpb;
    let csrfHash = config.csrfHash;
    let table;
    let options = { users: [], statuses: {} };
    let current = null;
    let actionMode = '';
    let formBusy = false;
    let actionBusy = false;
    const filters = { scope: 'related', status: '', history_status: '', date_from: '', date_to: '', department: '', applicant_user_id: '' };
    let selectedAttachments = [];
    let submissionKey = null;
    const canvases = {};
    let imageCollections = {};
    let activeImageCollection = [];
    let activeImageIndex = 0;

    const esc = value => $('<div>').text(value == null ? '' : String(value)).html();
    const url = path => config.baseUrl + '/' + path.replace(/^\//, '');
    const alertError = message => Swal.fire({ icon: 'error', title: 'Tidak dapat diproses', text: message || 'Terjadi kesalahan.' });
    const updateToken = payload => { if (payload && payload.token) csrfHash = payload.token; };
    const formatMoney = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value) || 0);
    const formatDate = value => value ? new Date(String(value).replace(' ', 'T')).toLocaleString('id-ID') : '-';
    const formatListDate = value => value ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(String(value).replace(' ', 'T'))) : '-';
    const statusTone = value => `bpb-status--${String(value || 'draft').toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;

    async function request(path, opts) {
        opts = opts || {};
        opts.headers = opts.headers || {};
        opts.headers['X-CSRF-TOKEN'] = csrfHash;
        const response = await fetch(url(path), opts);
        const json = await response.json().catch(() => ({}));
        updateToken(json);
        if (!response.ok || json.success === false) throw new Error(json.messages || 'Permintaan gagal.');
        return json.data !== undefined ? json.data : json;
    }

    function postForm(path, formData) {
        formData.append(config.csrfName, csrfHash);
        return request(path, { method: 'POST', body: formData });
    }

    function postFormWithProgress(path, formData) {
        formData.append(config.csrfName, csrfHash);
        const xhr = new XMLHttpRequest();
        return new Promise((resolve, reject) => {
            xhr.open('POST', url(path), true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfHash);
            xhr.upload.addEventListener('progress', event => {
                if (!event.lengthComputable || event.total <= 0) {
                    updateSubmitProgress(null);
                    return;
                }
                updateSubmitProgress(Math.min(100, Math.round((event.loaded / event.total) * 100)));
            });
            xhr.addEventListener('load', () => {
                let json = {};
                try { json = JSON.parse(xhr.responseText || '{}'); } catch (_) { /* handled as a generic request error */ }
                updateToken(json);
                if (xhr.status < 200 || xhr.status >= 300 || json.success === false || json.status === false) {
                    reject(new Error(json.messages || json.message || 'Permintaan gagal.'));
                    return;
                }
                resolve(json.data !== undefined ? json.data : json);
            });
            xhr.addEventListener('error', () => reject(new Error('Koneksi terputus saat menyimpan BPB. Periksa daftar BPB sebelum mencoba kembali.')));
            xhr.addEventListener('abort', () => reject(new Error('Pengiriman BPB dibatalkan.')));
            xhr.send(formData);
        });
    }

    function initCanvas(id) {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let drawing = false;
        let dirty = false;
        const resize = () => {
            const snapshot = dirty ? canvas.toDataURL('image/png') : null;
            const ratio = window.devicePixelRatio || 1;
            const width = Math.floor(canvas.parentElement.getBoundingClientRect().width);
            if (width < 2) return;
            canvas.width = Math.max(1, Math.floor(width * ratio));
            canvas.height = Math.floor(210 * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.strokeStyle = '#172b4d';
            if (snapshot) { const image = new Image(); image.onload = () => ctx.drawImage(image, 0, 0, width, 210); image.src = snapshot; }
        };
        const point = event => { const rect = canvas.getBoundingClientRect(); return { x: event.clientX - rect.left, y: event.clientY - rect.top }; };
        const stopDrawing = event => {
            drawing = false;
            if (event && canvas.hasPointerCapture && canvas.hasPointerCapture(event.pointerId)) canvas.releasePointerCapture(event.pointerId);
        };
        canvas.addEventListener('pointerdown', event => {
            if (event.isPrimary === false) return;
            event.preventDefault(); drawing = true; dirty = true;
            if (canvas.setPointerCapture) canvas.setPointerCapture(event.pointerId);
            const p = point(event); ctx.beginPath(); ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener('pointermove', event => {
            if (!drawing) return;
            event.preventDefault(); const p = point(event); ctx.lineTo(p.x, p.y); ctx.stroke();
        });
        ['pointerup', 'pointercancel'].forEach(name => canvas.addEventListener(name, stopDrawing));
        canvases[id] = { resize, clear: () => { ctx.clearRect(0, 0, canvas.width, canvas.height); dirty = false; }, data: () => dirty ? canvas.toDataURL('image/png') : '' };
        resize();
    }

    function addItem(item) {
        item = item || {};
        $('#bpb-items').append(`<div class="bpb-item-row row">
            <div class="col-md-4 form-group mb-md-0"><label>Nama barang</label><input class="form-control bpb-item-name" maxlength="255" value="${esc(item.nama_barang || '')}"></div>
            <div class="col-md-2 form-group mb-md-0"><label>Jumlah</label><input type="number" min="0.01" step="0.01" class="form-control bpb-item-qty" value="${esc(item.jumlah || 1)}"></div>
            <div class="col-md-2 form-group mb-md-0"><label>Satuan <span class="text-danger">*</span></label><input class="form-control bpb-item-unit" maxlength="50" placeholder="pcs, rim, box" required value="${esc(item.satuan || '')}"></div>
            <div class="col-md-3 form-group mb-md-0"><label>Keterangan</label><input class="form-control bpb-item-note" value="${esc(item.keterangan || '')}"></div>
            <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger bpb-remove-item"><i class="fa fa-trash"></i></button></div>
        </div>`);
    }

    function readItems() {
        return $('#bpb-items .bpb-item-row').map(function () {
            return { nama_barang: $(this).find('.bpb-item-name').val(), jumlah: $(this).find('.bpb-item-qty').val(), satuan: $(this).find('.bpb-item-unit').val(), keterangan: $(this).find('.bpb-item-note').val() };
        }).get();
    }

    const uploadTypes = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    const uploadExtensions = new Set(['jpg', 'jpeg', 'png', 'webp', 'pdf']);
    const maxUploadBytes = 5 * 1024 * 1024;

    function formatBytes(size) {
        return size >= 1024 * 1024 ? `${(size / (1024 * 1024)).toFixed(1)} MB` : `${Math.max(1, Math.round(size / 1024))} KB`;
    }

    function revokeSelectedPreview(file) {
        if (file.previewUrl) URL.revokeObjectURL(file.previewUrl);
    }

    function keptRequestFileCount() {
        return $('#bpb-existing-files .bpb-keep-file:checked').length;
    }

    function renderSelectedAttachments() {
        const cards = selectedAttachments.map((entry, index) => {
            const image = String(entry.file.type || '').startsWith('image/');
            const preview = image
                ? `<img src="${esc(entry.previewUrl)}" alt="Pratinjau ${esc(entry.file.name)}">`
                : '<i class="fa fa-file-pdf" aria-hidden="true"></i>';
            return `<div class="bpb-selected-file"><div class="bpb-selected-file-preview">${preview}</div><span class="bpb-selected-file-name" title="${esc(entry.file.name)}">${esc(entry.file.name)} · ${formatBytes(entry.file.size)}</span><button type="button" class="bpb-selected-file-remove" data-remove-selected-file="${index}" aria-label="Hapus ${esc(entry.file.name)}"><i class="fa fa-times"></i></button></div>`;
        }).join('');
        $('#bpb-selected-files').html(cards);
    }

    function addUploadFiles(fileList) {
        if (formBusy) return;
        const files = Array.from(fileList || []);
        const errors = [];
        files.forEach(file => {
            const extension = String(file.name || '').split('.').pop().toLowerCase();
            const accepted = uploadTypes.has(String(file.type || '').toLowerCase()) || (!file.type && uploadExtensions.has(extension));
            if (!accepted) { errors.push(`${file.name || 'File'}: format tidak didukung.`); return; }
            if (file.size > maxUploadBytes) { errors.push(`${file.name}: ukuran melebihi 5 MB.`); return; }
            if (selectedAttachments.length + keptRequestFileCount() >= 5) { errors.push('Maksimal 5 lampiran pengajuan. Hapus atau lepaskan lampiran lama terlebih dahulu.'); return; }
            let upload = file;
            if (!upload.type && extension) {
                const inferredMime = { jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', webp: 'image/webp', pdf: 'application/pdf' }[extension];
                if (inferredMime) upload = new File([upload], upload.name, { type: inferredMime });
            }
            if (!upload.name) {
                const mimeExtension = { 'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp' }[upload.type] || 'png';
                upload = new File([upload], `clipboard-image-${Date.now()}-${selectedAttachments.length + 1}.${mimeExtension}`, { type: upload.type || 'image/png' });
            }
            selectedAttachments.push({ file: upload, previewUrl: upload.type.startsWith('image/') ? URL.createObjectURL(upload) : '' });
        });
        $('#bpb-upload-message').text(errors.join(' '));
        renderSelectedAttachments();
    }

    function newSubmissionKey() {
        if (!window.crypto || typeof window.crypto.getRandomValues !== 'function') {
            throw new Error('Browser tidak mendukung pengamanan pengajuan BPB. Gunakan browser versi terbaru.');
        }
        const bytes = new Uint8Array(32);
        window.crypto.getRandomValues(bytes);
        return Array.from(bytes, value => value.toString(16).padStart(2, '0')).join('');
    }

    function populateUsers() {
        $('.bpb-user-select option:not(:first)').remove();
        options.users.forEach(user => {
            const label = `${user.name} — ${user.department} / ${user.level}`;
            $('#bpb-cc, #bpb-approver').append(new Option(label, user.id));
        });
        $('.bpb-user-select').select2({ width: '100%' });
        Object.entries(options.statuses).forEach(([value, label]) => {
            $('#bpb-filter-current-status, #bpb-filter-history-status').append(new Option(label, value));
        });
        (options.filters && options.filters.departments || []).forEach(department => $('#bpb-filter-department').append(new Option(department, department)));
        (options.filters && options.filters.applicants || []).forEach(applicant => {
            const label = `${applicant.name} — ${applicant.department || '-'}`;
            $('#bpb-filter-applicant').append(new Option(label, applicant.id));
        });
        $('#bpb-filter-department, #bpb-filter-applicant').select2({ width: '100%', dropdownParent: $('#bpb-filter-modal') });
    }

    function filterLabels() {
        const applicantOption = $('#bpb-filter-applicant option').filter((_, option) => option.value === String(filters.applicant_user_id)).text();
        return {
            scope: filters.scope === 'all' ? 'Cakupan: Semua' : '',
            status: filters.status ? `Status saat ini: ${options.statuses[filters.status] || filters.status}` : '',
            history_status: filters.history_status ? `Perubahan ${options.statuses[filters.history_status] || filters.history_status}${filters.date_from ? ` · ${filters.date_from}` : ''}${filters.date_to ? ` – ${filters.date_to}` : ''}` : '',
            department: filters.department ? `Divisi: ${filters.department}` : '',
            applicant_user_id: filters.applicant_user_id ? `Pembuat: ${applicantOption || filters.applicant_user_id}` : '',
        };
    }

    function renderActiveFilters() {
        const active = Object.entries(filterLabels()).filter(([, label]) => label);
        const chips = active.map(([key, label]) => `<span class="bpb-filter-chip"><span class="bpb-filter-chip-label">${esc(label)}</span><button type="button" class="bpb-filter-chip-remove" data-remove-filter="${esc(key)}" aria-label="Hapus filter ${esc(label)}"><i class="fa fa-times"></i></button></span>`).join('');
        const clear = active.length ? '<button type="button" class="bpb-filter-clear-all" data-clear-filters>Hapus semua</button>' : '';
        $('#bpb-active-filters').html(chips + clear).toggleClass('d-none', active.length === 0);
        $('#bpb-filter-count').text(active.length).toggleClass('d-none', active.length === 0);
    }

    function syncFilterPanel() {
        $('#bpb-filter-scope').val(filters.scope);
        $('#bpb-filter-current-status').val(filters.status);
        $('#bpb-filter-history-status').val(filters.history_status);
        $('#bpb-filter-date-from').val(filters.date_from).prop('disabled', !filters.history_status);
        $('#bpb-filter-date-to').val(filters.date_to).prop('disabled', !filters.history_status);
        $('#bpb-filter-department').val(filters.department).trigger('change');
        $('#bpb-filter-applicant').val(filters.applicant_user_id).trigger('change');
    }

    function applyFilterPanel() {
        filters.scope = $('#bpb-filter-scope').val() || 'related';
        filters.status = $('#bpb-filter-current-status').val() || '';
        filters.history_status = $('#bpb-filter-history-status').val() || '';
        filters.date_from = filters.history_status ? ($('#bpb-filter-date-from').val() || '') : '';
        filters.date_to = filters.history_status ? ($('#bpb-filter-date-to').val() || '') : '';
        filters.department = $('#bpb-filter-department').val() || '';
        filters.applicant_user_id = $('#bpb-filter-applicant').val() || '';
        renderActiveFilters();
        $('#bpb-filter-modal').modal('hide');
        if (table) table.ajax.reload();
    }

    function clearFilters() {
        Object.assign(filters, { scope: 'related', status: '', history_status: '', date_from: '', date_to: '', department: '', applicant_user_id: '' });
        syncFilterPanel();
        renderActiveFilters();
        if (table) table.ajax.reload();
    }

    function mobileInfoRow(icon, label, value, extraClass, meta) {
        return `<div class="bpb-mobile-card-row">
            <span class="bpb-mobile-card-icon"><i class="fa ${icon}"></i></span>
            <span class="bpb-mobile-card-copy"><span class="bpb-mobile-card-label">${esc(label)}</span><strong class="${extraClass || ''}">${esc(value || '-')}</strong>${meta ? `<small class="bpb-mobile-card-meta">${esc(meta)}</small>` : ''}</span>
        </div>`;
    }

    function renderMobileCards(api) {
        const rows = api.rows({ page: 'current' }).data().toArray();
        const cards = rows.map(row => {
            const tone = statusTone(row.status);
            return `<button type="button" class="bpb-mobile-card ${tone}" data-bpb-id="${Number(row.id)}">
            <span class="bpb-mobile-card-top"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
            ${mobileInfoRow('fa-file-alt', 'Nomor', row.nomor || 'Draft')}
            ${mobileInfoRow('fa-cube', 'Nama Item', row.item_display)}
            ${mobileInfoRow('fa-user', 'Pembuat BPB', row.applicant_name)}
            ${mobileInfoRow('fa-building', 'Divisi', row.applicant_department)}
            ${mobileInfoRow('fa-calendar-alt', 'Tanggal Pengajuan', formatListDate(row.display_date))}
            ${mobileInfoRow('fa-dot-circle', 'Status Terakhir', row.status_label, `bpb-mobile-card-status bpb-status-badge ${tone}`, `Diubah ${formatListDate(row.status_changed_at)}`)}
        </button>`;
        }).join('') || '<div class="bpb-mobile-empty">Belum ada BPB.</div>';
        const $mobileList = $('#bpb-mobile-list').html(cards);
        const $tableRow = $(api.table().node()).closest('.row');
        if ($tableRow.length) $mobileList.detach().insertBefore($tableRow);
    }

    function initTable() {
        table = $('#bpb-table').DataTable({
            processing: true, serverSide: true, pageLength: 10, order: [],
            ajax: function (data, callback) {
                const form = new FormData();
                form.append(config.csrfName, csrfHash); form.append('draw', data.draw); form.append('start', data.start); form.append('length', data.length);
                form.append('search[value]', data.search.value || '');
                Object.entries(filters).forEach(([key, value]) => form.append(key, value));
                fetch(url('api/bpb/list'), { method: 'POST', body: form, headers: { 'X-CSRF-TOKEN': csrfHash } }).then(r => r.json()).then(json => { updateToken(json); callback(json); }).catch(() => callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] }));
            },
            columns: [
                { data: 'nomor', defaultContent: 'Draft', render: value => esc(value || 'Draft') },
                { data: 'item_display', defaultContent: '-', render: esc },
                { data: 'applicant_name', defaultContent: '-', render: esc },
                { data: 'applicant_department', defaultContent: '-', render: value => esc(value || '-') },
                { data: 'display_date', defaultContent: '-', render: value => esc(formatListDate(value)) },
                { data: 'status_label', render: (value, type, row) => `<span class="badge bpb-status-badge ${statusTone(row.status)}">${esc(value)}</span><small class="d-block text-muted mt-25">${esc(formatDate(row.status_changed_at))}</small>` }
            ],
            createdRow: function (row, data) { $(row).addClass(statusTone(data.status)); $('td', row).each(function (index) { $(this).attr('data-label', ['Nomor', 'Nama Item', 'Pembuat BPB', 'Divisi', 'Tanggal Pengajuan', 'Status Terakhir'][index]); }); },
            drawCallback: function () { renderMobileCards(this.api()); },
            language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_', info: '_START_–_END_ dari _TOTAL_', emptyTable: 'Belum ada BPB', processing: 'Memuat...' }
        });
        $('#bpb-table tbody').on('click', 'tr', function () { const data = table.row(this).data(); if (data) openDetail(data.id); });
        $(document).on('click.bpb', '.bpb-mobile-card', function () { openDetail(Number($(this).data('bpb-id'))); });
    }

    function resetForm() {
        selectedAttachments.forEach(revokeSelectedPreview);
        selectedAttachments = [];
        submissionKey = null;
        current = null; $('#bpb-form')[0].reset(); $('#bpb-id').val(''); $('#bpb-items, #bpb-existing-files').empty(); addItem();
        $('#bpb-attachments').val(''); $('#bpb-selected-files, #bpb-upload-message').empty();
        $('#bpb-form-title').text('Buat BPB'); $('#bpb-save-draft').removeClass('d-none'); $('#bpb-submit').text('Ajukan BPB');
        $('#bpb-form-canvas-wrap').removeClass('d-none'); canvases['bpb-form-canvas'].clear(); $('#bpb-cc, #bpb-approver').val('').trigger('change');
    }

    function showForm(detail) {
        resetForm();
        if (!detail) submissionKey = newSubmissionKey();
        if (detail) {
            submissionKey = null;
            current = detail; $('#bpb-id').val(detail.id); $('#bpb-form-title').text(detail.status === 'draft' ? 'Edit Draft BPB' : 'Revisi BPB');
            $('#bpb-items').empty(); detail.items.forEach(addItem); $('#bpb-cc').val(detail.cc_user_id || '').trigger('change'); $('#bpb-approver').val(detail.approver_user_id || '').trigger('change');
            detail.files.filter(file => file.category === 'request').forEach(file => $('#bpb-existing-files').append(`<label class="bpb-file-pill"><input type="checkbox" class="bpb-keep-file mr-50" value="${file.id}" checked><a href="${esc(file.url)}" target="_blank" rel="noopener">${esc(file.original_name)}</a></label>`));
            if (detail.status !== 'draft') { $('#bpb-save-draft').addClass('d-none'); $('#bpb-submit').text('Simpan Revisi & Tanda Tangani Ulang'); }
        }
        $('#bpb-list-page').addClass('d-none'); $('#bpb-form-page').removeClass('d-none'); $('#bpb-create').addClass('d-none'); setTimeout(() => canvases['bpb-form-canvas'].resize(), 80);
    }

    function collectForm(withSignature) {
        const form = new FormData();
        const id = $('#bpb-id').val(); if (id) form.append('id', id);
        form.append('items', JSON.stringify(readItems())); form.append('cc_user_id', $('#bpb-cc').val() || ''); form.append('approver_user_id', $('#bpb-approver').val() || '');
        form.append('keep_file_ids', JSON.stringify($('.bpb-keep-file:checked').map((_, el) => Number(el.value)).get()));
        selectedAttachments.forEach(entry => form.append('attachments[]', entry.file, entry.file.name));
        if (withSignature) {
            if (!id && submissionKey) form.append('submission_key', submissionKey);
            const method = $('#bpb-sign-method').val(); form.append('signature_method', method); form.append('signature_data', method === 'canvas' ? canvases['bpb-form-canvas'].data() : '');
            form.append('password', $('#bpb-password').val()); form.append('consent', $('#bpb-consent').is(':checked') ? '1' : '0');
        }
        return form;
    }

    function updateSubmitProgress(percent) {
        const $wrapper = $('.bpb-submit-progress');
        const $bar = $('#bpb-submit-progress-bar');
        if (percent === null) {
            $wrapper.addClass('is-indeterminate');
            $('#bpb-submit-status').text('Mengunggah lampiran...');
            $('#bpb-submit-percentage').text('Memuat...');
            return;
        }
        if (percent >= 100) {
            $wrapper.addClass('is-indeterminate');
            $('#bpb-submit-status').text('Menyimpan ke server...');
            $('#bpb-submit-percentage').text('Memproses...');
            return;
        }
        $wrapper.removeClass('is-indeterminate');
        $bar.css('width', `${percent}%`).attr('aria-valuenow', percent);
        $('#bpb-submit-status').text('Mengunggah lampiran...');
        $('#bpb-submit-percentage').text(`${percent}%`);
    }

    function setFormBusy(busy, title) {
        formBusy = busy;
        $('#bpb-save-draft, #bpb-submit, #bpb-form-back, #bpb-create').prop('disabled', busy);
        const $overlay = $('#bpb-submit-overlay');
        if (busy) {
            $('#bpb-submit-title').text(title || 'Menyimpan BPB');
            $('#bpb-submit-status').text('Sedang menyiapkan data dan lampiran...');
            $('#bpb-submit-file-info').text(selectedAttachments.length ? `${selectedAttachments.length} file · ${selectedAttachments.map(entry => formatBytes(entry.file.size)).join(', ')}` : '');
            $('#bpb-submit-percentage').text('0%');
            $('#bpb-submit-progress-bar').css('width', '0%').attr('aria-valuenow', 0);
            $('.bpb-submit-progress').removeClass('is-indeterminate');
            $overlay.removeClass('d-none').attr('aria-hidden', 'false');
        } else {
            $overlay.addClass('d-none').attr('aria-hidden', 'true');
            $('.bpb-submit-progress').removeClass('is-indeterminate');
            $('#bpb-submit-progress-bar').css('width', '0%').attr('aria-valuenow', 0);
        }
    }

    function actionSubmitLabel(mode) {
        return ({
            sign: 'Konfirmasi Tanda Tangan', reject: 'Tolak BPB', cancel: 'Batalkan BPB',
            process: 'Konfirmasi Diproses', disburse: 'Simpan Pencairan', finish: 'Simpan Status',
        })[mode] || 'Simpan';
    }

    function setActionBusy(busy) {
        actionBusy = busy;
        const label = actionSubmitLabel(actionMode);
        $('#bpb-action-form :submit, #bpb-action-form [data-bpb-submit]')
            .prop('disabled', busy)
            .attr('aria-busy', busy ? 'true' : 'false')
            .html(busy ? `<span class="spinner-border spinner-border-sm mr-50" role="status" aria-hidden="true"></span>${esc(label)}…` : esc(label));
    }

    function armModalCloseConfirmation(selector) {
        if (typeof removeModalListener !== 'function' || typeof initModalListener !== 'function') return;
        removeModalListener(selector);
        initModalListener(selector);
    }

    function closeModalWithoutConfirmation(selector) {
        if (typeof removeModalListener === 'function') removeModalListener(selector);
        $(selector).modal('hide');
    }

    async function openDetail(id) {
        try {
            current = await request(`api/bpb/detail/${id}`);
            renderDetail(current); armModalCloseConfirmation('#bpb-detail-modal'); $('#bpb-detail-modal').modal('show');
        } catch (error) { alertError(error.message); }
    }

    function renderAttachmentGroup(files, group, emptyLabel) {
        files = files || [];
        const images = files.filter(file => String(file.mime_type || '').toLowerCase().startsWith('image/'));
        const documents = files.filter(file => !String(file.mime_type || '').toLowerCase().startsWith('image/'));
        imageCollections[group] = images.map(file => ({ url: file.url, name: file.original_name }));
        const thumbnails = images.map((file, index) => `<button type="button" class="bpb-image-thumb" data-bpb-image-group="${esc(group)}" data-bpb-image-index="${index}" aria-label="Lihat ${esc(file.original_name)}"><img src="${esc(file.thumbnail_url || file.url)}" alt=""><span>${esc(file.original_name)}</span></button>`).join('');
        const links = documents.map(file => `<a class="bpb-file-pill" target="_blank" rel="noopener" href="${esc(file.url)}"><i class="fa fa-file-pdf"></i>${esc(file.original_name)}</a>`).join('');
        return thumbnails || links ? `<div class="bpb-attachments">${thumbnails}${links}</div>` : `<span class="text-muted">${esc(emptyLabel)}</span>`;
    }

    function renderDetail(d) {
        const requestFiles = d.files.filter(file => file.category === 'request');
        const purchaseFiles = d.files.filter(file => file.category === 'purchase');
        imageCollections = {};
        const applicantInitials = String(d.applicant_name || '?').trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join('').toUpperCase();
        const items = d.items.map((item, i) => `<tr><td>${i + 1}</td><td><span class="bpb-item-dot"></span><strong>${esc(item.nama_barang)}</strong></td><td class="text-nowrap">${esc(item.jumlah)} ${esc(item.satuan || '')}</td><td>${esc(item.keterangan || '-')}</td></tr>`).join('');
        const activeSignatures = d.signatures.filter(signature => !signature.revoked);
        const signatures = activeSignatures.map(signature => `<div class="col-lg-4 col-md-6 mb-2"><article class="bpb-signature-card"><div class="bpb-signature-card-head"><strong>${esc(roleLabel(signature.role))}</strong><span class="badge bpb-status-badge bpb-status--approved">Disetujui</span></div><div class="bpb-signature-image"><img src="${esc(signature.image_url)}" alt="Tanda tangan ${esc(signature.signer_name)}"><small>Digital Signature</small></div><strong class="bpb-signature-name">${esc(signature.signer_name)}</strong><small class="bpb-signature-date">${esc(formatDate(signature.signed_at))}</small></article></div>`).join('') || '<div class="col-12 text-muted">Belum ada tanda tangan.</div>';
        const timeline = d.history.map((entry, index) => {
            const tone = statusTone(entry.to_status);
            const statusLabel = options.statuses[entry.to_status] || entry.to_status;
            return `<article class="bpb-timeline-item ${tone}${index === d.history.length - 1 ? ' is-current' : ''}"><span class="bpb-timeline-dot"></span><div class="bpb-timeline-copy"><strong>${esc(entry.summary)}</strong><small>Oleh ${esc(entry.actor_name)}</small></div><div class="bpb-timeline-meta"><span class="badge bpb-status-badge ${tone}">${esc(statusLabel)}</span><time>${esc(formatDate(entry.created_at))}</time></div></article>`;
        }).join('') || '<div class="text-muted">Belum ada riwayat.</div>';
        const hasFinance = d.actual_amount || d.purchase_date || purchaseFiles.length || d.disbursement_note || d.purchase_note || d.pending_reason;
        const finance = hasFinance ? `<section class="bpb-section"><div class="bpb-section-heading"><div><div class="divider divider-left"><div class="divider-text">Pencairan & Pembelian</div></div></div><span class="badge badge-light-primary">Kas Operasional</span></div><div class="bpb-finance-grid">
            <div class="bpb-finance-card"><small>Nominal Pencairan</small><strong>${d.actual_amount ? esc(formatMoney(d.actual_amount)) : '-'}</strong><span class="text-success">${d.disbursed_at ? 'Selesai Dicairkan' : 'Belum dicairkan'}</span></div>
            <div class="bpb-finance-card"><small>Penerima Dana</small><strong>${esc(d.recipient_name || '-')}</strong><span>Penerima dana BPB</span></div>
            <div class="bpb-finance-card"><small>No. Rekening Tujuan</small><strong>${esc(d.account_number || '-')}</strong><span>Nomor rekening tujuan</span></div>
            <div class="bpb-finance-card"><small>Tanggal Beli</small><strong>${esc(d.purchase_date || '-')}</strong><span>${esc(d.purchased_at ? formatListDate(d.purchased_at) : '')}</span></div>
        </div>${d.disbursement_note ? `<p class="bpb-finance-note"><strong>Catatan pencairan:</strong> ${esc(d.disbursement_note)}</p>` : ''}${d.pending_reason ? `<p class="bpb-finance-note"><strong>Alasan pending:</strong> ${esc(d.pending_reason)}</p>` : ''}${d.purchase_note ? `<p class="bpb-finance-note"><strong>Keterangan pembelian:</strong> ${esc(d.purchase_note)}</p>` : ''}${renderAttachmentGroup(purchaseFiles, 'purchase', 'Belum ada bukti pembelian.')}</section>` : '';
        const requestAttachments = renderAttachmentGroup(requestFiles, 'request', 'Tidak ada lampiran.');
        $('#bpb-detail-title').html(`<span class="bpb-detail-title-icon"><i class="fa fa-file-alt"></i></span><span><small>DOKUMEN PENGADAAN <i>• Diperbarui ${esc(formatDate(d.updated_at || d.created_at))}</i></small><strong>Detail BPB (Bon Permintaan Barang)</strong></span>`);
        $('#bpb-detail-status').attr('class', `badge bpb-status-badge ${statusTone(d.status)}`).text(d.status_label);
        $('#bpb-detail-body').html(`<section class="bpb-section bpb-summary-card"><div class="bpb-summary-main"><div><div class="bpb-label-row"><small>Nomor BPB</small><span class="badge ${d.current_document_hash ? 'badge-light-success' : 'badge-light-secondary'}">${d.current_document_hash ? 'Terverifikasi' : 'Belum Diajukan'}</span></div><h3 class="bpb-number">${esc(d.nomor || 'Draft')}</h3></div><div class="bpb-applicant"><span class="bpb-applicant-avatar">${esc(applicantInitials)}</span><span><small>Pemohon Pengajuan</small><strong>${esc(d.applicant_name || '-')}</strong><em>${esc(d.applicant_department || '-')}</em></span></div></div><div class="bpb-hash-row"><small><i class="fa fa-lock"></i> HASH AUDIT</small><code>${esc(d.current_document_hash || '-')}</code></div></section>
            <section class="bpb-section"><div class="bpb-section-heading"><div class="divider divider-left"><div class="divider-text">Item & Lampiran</div></div><span class="badge badge-light-secondary">${d.items.length} Barang</span></div><div class="table-responsive"><table class="table table-sm bpb-detail-items"><thead><tr><th>No</th><th>Deskripsi Barang</th><th>Jumlah</th><th>Keterangan / Spesifikasi</th></tr></thead><tbody>${items}</tbody></table></div><div class="bpb-attachment-heading"><i class="fa fa-paperclip"></i> Berkas Terlampir</div>${requestAttachments}</section>
            <section class="bpb-section"><div class="bpb-section-heading"><div class="divider divider-left"><div class="divider-text">Rantai Persetujuan & Tanda Tangan</div></div><span class="badge badge-light-success">${activeSignatures.length} Tanda Tangan</span></div><div class="row">${signatures}</div></section>${finance}
            <section class="bpb-section"><div class="bpb-section-heading"><div class="divider divider-left"><div class="divider-text">Riwayat Status & Audit Trail</div></div><span class="badge badge-light-secondary">${d.history.length} Aktivitas</span></div><div class="bpb-timeline">${timeline}</div></section>`);
        $('#bpb-image-lightbox').addClass('d-none').attr('aria-hidden', 'true');
        const buttons = [`<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>`, `<a class="btn btn-outline-primary" target="_blank" href="${esc(d.pdf_url)}"><i class="fa fa-file-pdf mr-50"></i>PDF</a>`];
        if (d.actions.edit) buttons.push('<button class="btn btn-outline-primary" data-bpb-action="edit">Edit</button>');
        if (d.actions.sign) buttons.push('<button class="btn btn-primary" data-bpb-action="sign">Tanda Tangani</button>');
        if (d.actions.reject) buttons.push('<button class="btn btn-outline-danger" data-bpb-action="reject">Tolak</button>');
        if (d.actions.cancel) buttons.push('<button class="btn btn-outline-danger" data-bpb-action="cancel">Batalkan</button>');
        if (d.actions.process) buttons.push('<button class="btn btn-primary" data-bpb-action="process">Tandai Diproses</button>');
        if (d.actions.disburse) buttons.push('<button class="btn btn-primary" data-bpb-action="disburse">Tandai Sudah Cair</button>');
        if (d.actions.finish) buttons.push('<button class="btn btn-primary" data-bpb-action="finish">Status Pembelian</button>');
        $('#bpb-detail-actions').html(buttons.join(''));
    }

    function showLightboxImage(index) {
        if (!activeImageCollection.length) return;
        activeImageIndex = (index + activeImageCollection.length) % activeImageCollection.length;
        const image = activeImageCollection[activeImageIndex];
        $('#bpb-lightbox-image').attr({ src: image.url, alt: image.name });
        $('#bpb-lightbox-caption').text(image.name);
        $('#bpb-lightbox-counter').text(`${activeImageIndex + 1} / ${activeImageCollection.length}`);
        $('#bpb-lightbox-prev, #bpb-lightbox-next').toggleClass('d-none', activeImageCollection.length < 2);
    }

    function openLightbox(group, index, trigger) {
        activeImageCollection = imageCollections[group] || [];
        if (!activeImageCollection.length) return;
        showLightboxImage(Number(index) || 0);
        $('#bpb-image-lightbox').removeClass('d-none').attr('aria-hidden', 'false');
        $('#bpb-lightbox-close').trigger('focus');
        $('#bpb-image-lightbox').data('trigger', trigger || null);
    }

    function closeLightbox() {
        const trigger = $('#bpb-image-lightbox').data('trigger');
        $('#bpb-image-lightbox').addClass('d-none').attr('aria-hidden', 'true');
        $('#bpb-lightbox-image').attr({ src: '', alt: '' });
        activeImageCollection = [];
        if (trigger && typeof trigger.focus === 'function') trigger.focus();
    }

    function moveLightbox(direction) {
        if (activeImageCollection.length > 1) showLightboxImage(activeImageIndex + direction);
    }

    function roleLabel(role) { return ({ applicant: 'Pemohon', cc: 'CC / Paraf', approver: 'Mengetahui', expense_verifier: 'Verifikasi Pengeluaran' })[role] || role; }

    function openAction(mode) {
        if (actionBusy) return;
        actionMode = mode; $('#bpb-action-form')[0].reset(); canvases['bpb-action-canvas'].clear(); $('#bpb-action-signature').addClass('d-none');
        let title = 'Aksi BPB', fields = '';
        if (mode === 'sign') { title = 'Tanda Tangani BPB'; $('#bpb-action-signature').removeClass('d-none'); }
        if (mode === 'process') { title = 'Verifikasi Pengeluaran'; $('#bpb-action-signature').removeClass('d-none'); }
        if (mode === 'reject') { title = 'Tolak BPB'; fields = '<div class="form-group"><label>Alasan penolakan</label><textarea class="form-control" id="bpb-action-reason" required></textarea></div>'; }
        if (mode === 'cancel') { title = 'Batalkan BPB'; fields = '<div class="form-group"><label>Alasan pembatalan</label><textarea class="form-control" id="bpb-action-reason"></textarea></div>'; }
        if (mode === 'disburse') { title = 'Data Pencairan'; fields = '<div class="form-group"><label>Nominal aktual</label><input type="number" min="1" class="form-control" id="bpb-actual-amount"></div><div class="form-group"><label>Nama penerima</label><input class="form-control" id="bpb-recipient"></div><div class="form-group"><label>Nomor rekening</label><input class="form-control" inputmode="numeric" id="bpb-account"></div><div class="form-group"><label>Catatan</label><textarea class="form-control" id="bpb-action-note"></textarea></div>'; }
        if (mode === 'finish') { title = 'Status Pembelian'; fields = '<div class="form-group"><label>Status</label><select class="form-control" id="bpb-finish-status"><option value="purchased">Sudah Dibeli</option><option value="pending">Pending</option></select></div><div class="form-group"><label>Alasan pending</label><textarea class="form-control" id="bpb-action-reason"></textarea></div><div class="form-group"><label>Tanggal pembelian</label><input type="date" class="form-control" id="bpb-purchase-date"></div><div class="form-group"><label>Bukti pembelian (1–5 file)</label><input type="file" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="form-control" id="bpb-purchase-files"></div><div class="form-group"><label>Keterangan</label><textarea class="form-control" id="bpb-action-note"></textarea></div>'; }
        $('#bpb-action-title').text(title); $('#bpb-action-fields').html(fields); setActionBusy(false); armModalCloseConfirmation('#bpb-action-modal'); $('#bpb-action-modal').modal('show');
    }

    async function submitAction() {
        const form = new FormData(); let path = `api/bpb/${current.id}/${actionMode}`;
        if (actionMode === 'sign' || actionMode === 'process') {
            const method = $('#bpb-action-sign-method').val(); form.append('signature_method', method); form.append('signature_data', method === 'canvas' ? canvases['bpb-action-canvas'].data() : ''); form.append('password', $('#bpb-action-password').val());
        }
        if (actionMode === 'reject' || actionMode === 'cancel') form.append('reason', $('#bpb-action-reason').val() || '');
        if (actionMode === 'process') { path = `api/bpb/${current.id}/status`; form.append('status', 'processed'); }
        if (actionMode === 'disburse') { path = `api/bpb/${current.id}/status`; form.append('status', 'disbursed'); form.append('actual_amount', $('#bpb-actual-amount').val()); form.append('recipient_name', $('#bpb-recipient').val()); form.append('account_number', $('#bpb-account').val()); form.append('note', $('#bpb-action-note').val()); }
        if (actionMode === 'finish') { const status = $('#bpb-finish-status').val(); path = `api/bpb/${current.id}/status`; form.append('status', status); form.append('reason', $('#bpb-action-reason').val()); form.append('purchase_date', $('#bpb-purchase-date').val()); form.append('note', $('#bpb-action-note').val()); Array.from($('#bpb-purchase-files')[0].files || []).forEach(file => form.append('purchase_proofs[]', file)); }
        const detail = await postForm(path, form); closeModalWithoutConfirmation('#bpb-action-modal'); current = detail; renderDetail(detail); table.ajax.reload(null, false);
    }

    $(function () {
        initCanvas('bpb-form-canvas'); initCanvas('bpb-action-canvas');
        $(document).on('click.bpb', '#bpb-create', () => { try { showForm(null); } catch (error) { alertError(error.message); } });
        $(document).on('click.bpb', '#bpb-form-back', () => { if (formBusy) return; $('#bpb-form-page').addClass('d-none'); $('#bpb-list-page').removeClass('d-none'); $('#bpb-create').removeClass('d-none').prop('disabled', false); });
        $(document).on('click.bpb', '#bpb-add-item', () => addItem());
        $(document).on('click.bpb', '.bpb-remove-item', function () { if ($('#bpb-items .bpb-item-row').length > 1) $(this).closest('.bpb-item-row').remove(); });
        $(document).on('click.bpb', '#bpb-dropzone', function (event) { if (!$(event.target).closest('.bpb-select-files').length) $('#bpb-attachments').trigger('click'); });
        $(document).on('click.bpb', '.bpb-select-files', function (event) { event.stopPropagation(); $('#bpb-attachments').trigger('click'); });
        $(document).on('keydown.bpb', '#bpb-dropzone', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); $('#bpb-attachments').trigger('click'); } });
        $(document).on('change.bpb', '#bpb-attachments', function () { addUploadFiles(this.files); this.value = ''; });
        $(document).on('click.bpb', '[data-remove-selected-file]', function () {
            const index = Number($(this).attr('data-remove-selected-file'));
            if (!Number.isInteger(index) || !selectedAttachments[index]) return;
            revokeSelectedPreview(selectedAttachments[index]);
            selectedAttachments.splice(index, 1);
            $('#bpb-upload-message').empty();
            renderSelectedAttachments();
        });
        $(document).on('change.bpb', '.bpb-keep-file', function () { $('#bpb-upload-message').empty(); });
        $('#bpb-dropzone').on('dragenter.bpb dragover.bpb', function (event) { event.preventDefault(); event.originalEvent.dataTransfer.dropEffect = 'copy'; $(this).addClass('is-dragging'); });
        $('#bpb-dropzone').on('dragleave.bpb', function (event) { if (!this.contains(event.relatedTarget)) $(this).removeClass('is-dragging'); });
        $('#bpb-dropzone').on('drop.bpb', function (event) { event.preventDefault(); $(this).removeClass('is-dragging'); addUploadFiles(event.originalEvent.dataTransfer.files); });
        $(document).on('paste.bpb', function (event) {
            if ($('#bpb-form-page').hasClass('d-none') || formBusy) return;
            const clipboard = (event.originalEvent || event).clipboardData;
            const items = Array.from(clipboard && clipboard.items || []);
            const images = items.filter(item => item.kind === 'file' && String(item.type || '').startsWith('image/'))
                .map(item => item.getAsFile()).filter(Boolean);
            if (images.length) { event.preventDefault(); addUploadFiles(images); }
        });
        $(document).on('click.bpb', '.bpb-clear-canvas', function () { canvases[$(this).data('canvas')].clear(); });
        $(document).on('change.bpb', '#bpb-sign-method', function () { $('#bpb-form-canvas-wrap').toggleClass('d-none', this.value === 'profile'); });
        $(document).on('change.bpb', '#bpb-action-sign-method', function () { $('#bpb-action-canvas-wrap').toggleClass('d-none', this.value === 'profile'); });
        $(document).on('click.bpb', '#bpb-open-filter', function () { syncFilterPanel(); $('#bpb-filter-modal').modal('show'); });
        $(document).on('change.bpb', '#bpb-filter-history-status', function () {
            const enabled = Boolean(this.value);
            $('#bpb-filter-date-from, #bpb-filter-date-to').prop('disabled', !enabled);
        });
        $(document).on('click.bpb', '#bpb-filter-apply', applyFilterPanel);
        $(document).on('click.bpb', '#bpb-filter-clear', function () { clearFilters(); $('#bpb-filter-modal').modal('hide'); });
        $(document).on('click.bpb', '#bpb-refresh', function () { if (table) table.ajax.reload(null, false); });
        $(document).on('click.bpb', '[data-clear-filters]', clearFilters);
        $(document).on('click.bpb', '[data-remove-filter]', function () {
            const key = $(this).attr('data-remove-filter');
            if (key === 'history_status') Object.assign(filters, { history_status: '', date_from: '', date_to: '' });
            else if (Object.prototype.hasOwnProperty.call(filters, key)) filters[key] = key === 'scope' ? 'related' : '';
            renderActiveFilters();
            if (table) table.ajax.reload();
        });
        $(document).on('click.bpb', '#bpb-save-draft', async function () {
            if (formBusy) return;
            setFormBusy(true, 'Menyimpan draft BPB');
            try { const detail = await postFormWithProgress('api/bpb/draft', collectForm(false)); Swal.fire('Tersimpan', 'Draft BPB berhasil disimpan.', 'success'); showForm(detail); if (table) table.ajax.reload(); }
            catch (error) { alertError(error.message); }
            finally { setFormBusy(false); }
        });
        $(document).on('submit.bpb', '#bpb-form', async function (event) {
            event.preventDefault();
            if (formBusy) return;
            setFormBusy(true, 'Mengajukan BPB');
            try { const id = $('#bpb-id').val(); const path = id && current && current.status !== 'draft' ? `api/bpb/${id}/update` : 'api/bpb/submit'; await postFormWithProgress(path, collectForm(true)); Swal.fire('Berhasil', 'BPB berhasil disimpan.', 'success'); selectedAttachments.forEach(revokeSelectedPreview); selectedAttachments = []; submissionKey = null; $('#bpb-selected-files').empty(); $('#bpb-form-page').addClass('d-none'); $('#bpb-list-page').removeClass('d-none'); $('#bpb-create').removeClass('d-none').prop('disabled', false); if (table) table.ajax.reload(); }
            catch (error) { alertError(error.message); }
            finally { setFormBusy(false); }
        });
        $(document).on('click.bpb', '#bpb-detail-actions [data-bpb-action]', function () { const mode = $(this).data('bpb-action'); if (mode === 'edit') { closeModalWithoutConfirmation('#bpb-detail-modal'); showForm(current); } else openAction(mode); });
        $(document).on('submit.bpb', '#bpb-action-form', async function (event) {
            event.preventDefault();
            if (actionBusy) return;
            setActionBusy(true);
            try { await submitAction(); Swal.fire('Berhasil', 'Aksi BPB disimpan.', 'success'); }
            catch (error) { alertError(error.message); }
            finally { setActionBusy(false); }
        });
        $(document).on('click.bpb', '.bpb-image-thumb', function () { openLightbox($(this).data('bpb-image-group'), $(this).data('bpb-image-index'), this); });
        $(document).on('click.bpb', '#bpb-lightbox-close', closeLightbox);
        $(document).on('click.bpb', '#bpb-lightbox-prev', () => moveLightbox(-1));
        $(document).on('click.bpb', '#bpb-lightbox-next', () => moveLightbox(1));
        $(document).on('click.bpb', '#bpb-image-lightbox', function (event) { if (event.target === this) closeLightbox(); });
        $(document).on('keydown.bpb', function (event) {
            if ($('#bpb-image-lightbox').hasClass('d-none')) return;
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); closeLightbox(); }
            if (event.key === 'ArrowLeft') { event.preventDefault(); moveLightbox(-1); }
            if (event.key === 'ArrowRight') { event.preventDefault(); moveLightbox(1); }
        });
        let lightboxSwipeStart = null;
        $('#bpb-lightbox-stage').on('pointerdown.bpb', event => { lightboxSwipeStart = event.clientX; });
        $('#bpb-lightbox-stage').on('pointerup.bpb pointercancel.bpb', event => {
            if (lightboxSwipeStart === null) return;
            const delta = event.clientX - lightboxSwipeStart;
            lightboxSwipeStart = null;
            if (Math.abs(delta) > 55) moveLightbox(delta < 0 ? 1 : -1);
        });
        $('#bpb-action-modal').on('shown.bs.modal.bpb', function () { const canvas = canvases['bpb-action-canvas']; if (canvas) { canvas.resize(); canvas.clear(); } });
        window.addEventListener('orientationchange', () => setTimeout(() => Object.values(canvases).forEach(canvas => canvas.resize()), 250));
        window.addEventListener('resize', () => Object.values(canvases).forEach(canvas => canvas.resize()));
        (async function () {
            try { options = await request('api/bpb/options'); populateUsers(); } catch (error) { alertError(error.message); }
            initTable();
            renderActiveFilters();
            if (config.openId) openDetail(config.openId);
        })();
    });
})(jQuery);
