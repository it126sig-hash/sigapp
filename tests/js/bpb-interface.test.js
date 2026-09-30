const fs = require('fs');
const path = require('path');

describe('BPB mobile interface contract', () => {
    const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/bpb.js'), 'utf8');
    const serviceSource = fs.readFileSync(path.join(__dirname, '../../app/Services/Bpb/BpbService.php'), 'utf8');
    const repositorySource = fs.readFileSync(path.join(__dirname, '../../app/Repositories/BpbRepository.php'), 'utf8');
    const viewSource = fs.readFileSync(path.join(__dirname, '../../app/Views/bpb/index.php'), 'utf8');
    const styleSource = fs.readFileSync(path.join(__dirname, '../../public/assets/css/bpb.css'), 'utf8');
    const migrationSource = fs.readFileSync(path.join(__dirname, '../../app/Database/Migrations/2026-09-30-000001_AddBpbItemUnitAndSubmissionKey.php'), 'utf8');

    test('uses pointer events so the signature canvas works with mouse and touch', () => {
        expect(source).toContain("canvas.addEventListener('pointerdown'");
        expect(source).toContain("canvas.addEventListener('pointermove'");
        expect(source).toContain("window.addEventListener('orientationchange'");
    });

    test('keeps the DataTable to the requested six columns including division', () => {
        const columns = source.match(/data: '(nomor|item_display|applicant_name|applicant_department|display_date|status_label)'/g) || [];
        expect(columns).toHaveLength(6);
    });

    test('renders the mobile card from the same DataTable page data', () => {
        expect(source).toContain('function renderMobileCards(api)');
        expect(source).toContain("api.rows({ page: 'current' }).data().toArray()");
        expect(source).toContain('bpb-mobile-card');
        expect(source).toContain('row.display_date');
        expect(source).toContain('row.status_changed_at');
        expect(source).toContain("mobileInfoRow('fa-building', 'Divisi', row.applicant_department)");
        expect(source).toContain('bpb-status-badge');
        expect(source).toContain('statusTone(row.status)');
        expect(source).not.toContain('<span class="bpb-mobile-card-top"><span class="badge');
    });

    test('resizes the action signature canvas only after its modal is visible', () => {
        expect(source).toContain("$('#bpb-action-modal').on('shown.bs.modal.bpb'");
        expect(source).not.toContain("setTimeout(() => canvases['bpb-action-canvas'].resize(), 100)");
    });

    test('locks draft and submit controls while an upload is in flight', () => {
        expect(source).toContain('function setFormBusy(busy, title)');
        expect(source).toContain("$('#bpb-save-draft, #bpb-submit, #bpb-form-back, #bpb-create').prop('disabled', busy)");
        expect(source).toContain('if (formBusy) return;');
    });

    test('locks every action modal submission while signing, rejecting, or changing status', () => {
        expect(source).toContain('let actionBusy = false;');
        expect(source).toContain('function setActionBusy(busy)');
        expect(source).toContain("$('#bpb-action-form :submit, #bpb-action-form [data-bpb-submit]')");
        expect(source).toContain('if (actionBusy) return;');
        expect(source).toContain('finally { setActionBusy(false); }');
        expect(source).toContain("sign: 'Konfirmasi Tanda Tangan', reject: 'Tolak BPB'");
    });

    test('uses the shared modal close confirmation helpers', () => {
        expect(source).toContain('initModalListener(selector)');
        expect(source).toContain('removeModalListener(selector)');
        expect(source).toContain('closeModalWithoutConfirmation');
    });

    test('renders image thumbnails through private thumbnail URLs and keeps PDF links', () => {
        expect(serviceSource).toContain("$access->thumbnailUrl('bpb_file'");
        expect(source).toContain('file.thumbnail_url || file.url');
        expect(source).toContain("file.mime_type || '').toLowerCase().startsWith('image/')");
        expect(source).toContain('class="bpb-file-pill" target="_blank"');
    });

    test('opens grouped image lightboxes with navigation, swipe, and keyboard controls', () => {
        expect(viewSource).toContain('id="bpb-image-lightbox"');
        expect(source).toContain('data-bpb-image-group');
        expect(source).toContain("event.key === 'ArrowLeft'");
        expect(source).toContain("event.key === 'ArrowRight'");
        expect(source).toContain('Math.abs(delta) > 55');
    });

    test('keeps the detail footer visible and colors the timeline by status', () => {
        expect(viewSource).toContain('modal-dialog-scrollable');
        expect(styleSource).toContain('#bpb-detail-modal .modal-footer { position: sticky');
        expect(source).toContain('statusTone(entry.to_status)');
        expect(source).toContain('bpb-timeline-item ${tone}');
    });

    test('sends stage-specific proof files only on purchase status', () => {
        expect(source).toContain("form.append('purchase_proofs[]', file)");
        expect(source).toContain("form.append('status', status)");
    });

    test('sends quantity and required free-text unit as part of each item', () => {
        expect(source).toContain('class="form-control bpb-item-unit" maxlength="50"');
        expect(source).toContain("satuan: $(this).find('.bpb-item-unit').val()");
        expect(serviceSource).toContain("'satuan' => $unit !== '' ? $unit : null");
        expect(serviceSource).toContain("($required && $unit === '')");
        expect(migrationSource).toContain("'satuan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true]");
    });

    test('uses a stable secure submit key and a unique database index for replay safety', () => {
        expect(source).toContain('window.crypto.getRandomValues(bytes)');
        expect(source).toContain('submissionKey = newSubmissionKey()');
        expect(source).toContain("form.append('submission_key', submissionKey)");
        expect(serviceSource).toContain('findBySubmissionKey($key)');
        expect(serviceSource).toContain('isSubmissionKeyConflict($e)');
        expect(migrationSource).toContain('uq_bpb_requests_submission_key');
        expect(source).toContain("$('#bpb-create').addClass('d-none')");
        expect(source).toContain("$('#bpb-save-draft, #bpb-submit, #bpb-form-back, #bpb-create').prop('disabled', busy)");
    });

    test('supports upload progress, drop/paste selection, local previews, and removing pending files', () => {
        expect(viewSource).toContain('id="bpb-dropzone"');
        expect(viewSource).toContain('id="bpb-submit-progress-bar"');
        expect(source).toContain('xhr.upload.addEventListener(\'progress\'');
        expect(source).toContain("addUploadFiles(event.originalEvent.dataTransfer.files)");
        expect(source).toContain("$(document).on('paste.bpb'");
        expect(source).toContain('URL.createObjectURL(upload)');
        expect(source).toContain('data-remove-selected-file');
        expect(styleSource).toContain('.bpb-dropzone.is-dragging');
    });

    test('shows a filter side modal with active chips and a refresh control', () => {
        expect(viewSource).toContain('id="bpb-filter-modal"');
        expect(viewSource).toContain('id="bpb-filter-history-status"');
        expect(viewSource).toContain('id="bpb-filter-date-from"');
        expect(viewSource).toContain('id="bpb-filter-date-to"');
        expect(viewSource).toContain('id="bpb-filter-department"');
        expect(viewSource).toContain('id="bpb-filter-applicant"');
        expect(source).toContain('function renderActiveFilters()');
        expect(source).toContain('data-remove-filter');
        expect(source).toContain("'#bpb-refresh'");
        expect(source).toContain("Object.entries(filters).forEach(([key, value]) => form.append(key, value))");
        expect(styleSource).toContain('#bpb-filter-modal.show .modal-dialog');
    });

    test('filters list by status-history dates, applicant division, and creator', () => {
        expect(repositorySource).toContain('hfilter.to_status');
        expect(repositorySource).toContain('hfilter.created_at >=');
        expect(repositorySource).toContain('hfilter.created_at <=');
        expect(repositorySource).toContain("$base->where('b.applicant_department', $department)");
        expect(repositorySource).toContain("$base->where('b.applicant_user_id', $applicantUserId)");
        expect(repositorySource).toContain('function listFilterOptions()');
        expect(serviceSource).toContain("'filters' => $this->repository->listFilterOptions()");
    });
});
