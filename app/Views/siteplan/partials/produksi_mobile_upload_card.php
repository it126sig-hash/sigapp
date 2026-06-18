<?php
$id = (string) ($id ?? '');
$title = (string) ($title ?? 'Upload Foto');
$help = (string) ($help ?? 'Bisa lebih dari 1 foto.');
?>
<form class="produksi-mobile-upload-card pm-upload-form" data-category="<?= esc($id) ?>" enctype="multipart/form-data">
    <strong class="produksi-mobile-upload-title"><?= esc($title) ?></strong>
    <small><?= esc($help) ?></small>
    <div class="custom-file">
        <input
            type="file"
            class="custom-file-input produksi-photo-input"
            accept="image/*"
            name="<?= esc($id) ?>[]"
            id="<?= esc($id) ?>"
            multiple
            onchange="displayUploadedFiles(this, 'list_<?= esc($id) ?>')" />
        <label class="custom-file-label" id="label_<?= esc($id) ?>" for="<?= esc($id) ?>">Bisa lebih dari 1 foto</label>
    </div>
    <div id="list_<?= esc($id) ?>"></div>
    <button type="submit" class="btn btn-primary btn-block btn-sm pm-upload-btn mt-75">
        <i class="fas fa-cloud-upload-alt mr-50"></i>Upload
    </button>
</form>
