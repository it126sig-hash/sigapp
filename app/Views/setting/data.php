<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">

<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
</script>

<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="setting-data">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <h4 class="card-title mb-0"><?= esc($title) ?></h4>
          </div>
          <div class="card-body pt-1">
            <ul class="nav nav-tabs" role="tablist">
              <?php $first = true; ?>
              <?php foreach ($tables as $key => $table): ?>
                <li class="nav-item">
                  <a class="nav-link <?= $first ? 'active' : '' ?>" id="<?= esc($key) ?>-tab" data-toggle="tab" href="#<?= esc($key) ?>-pane" role="tab">
                    <?= esc($table['label']) ?>
                  </a>
                </li>
                <?php $first = false; ?>
              <?php endforeach; ?>
            </ul>

            <div class="tab-content">
              <?php $first = true; ?>
              <?php foreach ($tables as $key => $table): ?>
                <div class="tab-pane <?= $first ? 'active' : '' ?>" id="<?= esc($key) ?>-pane" role="tabpanel" aria-labelledby="<?= esc($key) ?>-tab">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="text-muted small"><?= esc($table['table_label']) ?></div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addSettingData('<?= esc($key) ?>')">
                      <i class="fa fa-plus"></i> Tambah Data
                    </button>
                  </div>
                  <div class="card-datatable">
                    <table id="setting-table-<?= esc($key) ?>" class="table table-bordered table-striped setting-data-table" data-key="<?= esc($key) ?>">
                      <thead>
                        <tr>
                          <th>No</th>
                          <?php foreach ($table['fields'] as $field): ?>
                            <th><?= esc($field['label']) ?></th>
                          <?php endforeach; ?>
                          <th></th>
                        </tr>
                      </thead>
                    </table>
                  </div>
                </div>
                <?php $first = false; ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="modal modal-slide-in fade" id="setting-data-modal">
  <div class="modal-dialog sidebar-sm">
    <form id="setting-data-form" class="add-new-record modal-content pt-0">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
      <div class="modal-header mb-1">
        <h5 class="modal-title" id="setting-data-modal-title">Tambah Data</h5>
      </div>
      <div class="modal-body flex-grow-1">
        <input type="hidden" id="setting_key" name="setting_key">
        <input type="hidden" id="setting_id" name="setting_id">
        <div id="setting-data-fields"></div>
        <button type="submit" class="btn btn-primary data-submit mr-1" id="setting-data-form-btn">Simpan</button>
        <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script>
  const settingControllerUrl = base_url + '<?= $controller ?>';
  const settingConfigs = <?= json_encode($tables) ?>;
  const settingTables = {};

  $(function() {
    $('.setting-data-table').each(function() {
      const key = $(this).data('key');
      settingTables[key] = $(this).DataTable({
        paging: true,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        ajax: {
          url: settingControllerUrl + '/list/' + key,
          type: 'POST',
          dataType: 'json',
          data: function(data) {
            data[csrfName] = csrfHash;
          },
          dataSrc: function(response) {
            csrfHash = response.token;
            return response.data;
          }
        }
      });
    });

    bindSettingDataForm();
  });

  function addSettingData(key) {
    const config = settingConfigs[key];
    $('#setting-data-form')[0].reset();
    $('#setting_key').val(key);
    $('#setting_id').val('');
    $('#setting-data-modal-title').text('Tambah ' + config.label);
    renderSettingFields(key, {});
    $('#setting-data-modal').modal('show');
  }

  function editSettingData(key, id) {
    $.post(settingControllerUrl + '/get/' + key, { [csrfName]: csrfHash, id: id }, function(response) {
      csrfHash = response.token;
      if (!response.success) {
        showSettingAlert('error', response.messages);
        return;
      }

      const config = settingConfigs[key];
      $('#setting_key').val(key);
      $('#setting_id').val(response[config.primary_key]);
      $('#setting-data-modal-title').text('Edit ' + config.label);
      renderSettingFields(key, response);
      $('#setting-data-modal').modal('show');
    }, 'json');
  }

  function deleteSettingData(key, id) {
    Swal.fire({
      title: 'Hapus data ini?',
      text: 'Data akan disembunyikan dengan soft delete.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Hapus',
      cancelButtonText: 'Batal',
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-outline-secondary ml-1'
      },
      buttonsStyling: false
    }).then(function(result) {
      if (!result.isConfirmed) {
        return;
      }

      $.post(settingControllerUrl + '/delete/' + key, { [csrfName]: csrfHash, id: id }, function(response) {
        csrfHash = response.token;
        showSettingAlert(response.success ? 'success' : 'error', response.messages);
        if (response.success) {
          settingTables[key].ajax.reload(null, false);
        }
      }, 'json');
    });
  }

  function bindSettingDataForm() {
    $('#setting-data-form').validate({
      submitHandler: function(form) {
        const key = $('#setting_key').val();
        const config = settingConfigs[key];
        const id = $('#setting_id').val();
        let payload = $(form).serializeArray();
        payload.push({ name: csrfName, value: csrfHash });
        payload.push({ name: config.primary_key, value: id });

        $.ajax({
          url: settingControllerUrl + '/save/' + key,
          type: 'POST',
          data: $.param(payload),
          dataType: 'json',
          beforeSend: function() {
            $('#setting-data-form-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
          },
          success: function(response) {
            csrfHash = response.token;
            showSettingAlert(response.success ? 'success' : 'error', response.messages);
            if (response.success) {
              $('#setting-data-modal').modal('hide');
              settingTables[key].ajax.reload(null, false);
            }
          },
          complete: function() {
            $('#setting-data-form-btn').prop('disabled', false).html('Simpan');
          }
        });

        return false;
      }
    });
  }

  function renderSettingFields(key, row) {
    const config = settingConfigs[key];
    const html = Object.keys(config.fields).map(function(field) {
      const meta = config.fields[field];
      const type = (meta.type === 'number' || meta.type === 'decimal') ? 'number' : 'text';
      const step = meta.type === 'decimal' ? 'step="0.01"' : '';
      const required = meta.required ? 'required' : '';
      const maxLength = meta.max_length ? `maxlength="${meta.max_length}"` : '';
      const value = escapeHtml(row[field] ?? '');
      return `<div class="form-group">
        <label for="setting_field_${field}">${escapeHtml(meta.label)} ${meta.required ? '<span class="text-danger">*</span>' : ''}</label>
        <input type="${type}" id="setting_field_${field}" name="${field}" class="form-control" value="${value}" ${required} ${maxLength} ${step}>
      </div>`;
    }).join('');

    $('#setting-data-fields').html(html);
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(match) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[match];
    });
  }

  function showSettingAlert(icon, message) {
    Swal.fire({
      position: 'bottom-end',
      icon: icon,
      title: message,
      showConfirmButton: false,
      timer: 1800
    });
  }
</script>
