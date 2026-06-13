<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">

<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
</script>

<style>
  .menu-access-tree {
    border: 1px solid #ebe9f1;
    border-radius: 4px;
    min-height: 220px;
    padding: 1rem;
  }

  .menu-access-row {
    align-items: center;
    border-bottom: 1px solid #f3f2f7;
    display: flex;
    gap: .75rem;
    min-height: 42px;
    padding: .4rem 0;
  }

  .menu-access-row:last-child {
    border-bottom: 0;
  }

  .menu-access-name {
    flex: 1 1 auto;
  }

  .menu-access-radio {
    display: flex;
    flex: 0 0 auto;
    gap: .5rem;
  }

  .menu-inherited {
    color: #6e6b7b;
    font-size: .85rem;
  }

  .siteplan-role-grid {
    border: 1px solid #ebe9f1;
    border-radius: 6px;
    display: grid;
    gap: .35rem .75rem;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    padding: .75rem;
  }

  .siteplan-role-grid .custom-control-label {
    font-weight: 500;
  }
</style>

<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="menu-setting">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <h4 class="card-title mb-0"><?= esc($title) ?></h4>
          </div>
          <div class="card-body pt-1">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="master-tab" data-toggle="tab" href="#master-menu" role="tab">Master Menu</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="siteplan-menu-tab" data-toggle="tab" href="#siteplan-menu" role="tab">Menu Siteplan</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="group-tab" data-toggle="tab" href="#group-access" role="tab">Akses Departemen</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="user-tab" data-toggle="tab" href="#user-access" role="tab">Akses User</a>
              </li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="master-menu" role="tabpanel" aria-labelledby="master-tab">
                <div class="d-flex justify-content-end mb-1">
                  <button type="button" class="btn btn-primary btn-sm" onclick="addMenu()"><i class="fa fa-plus"></i> Tambah Menu</button>
                </div>
                <div class="card-datatable">
                  <table id="menu_table" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Menu</th>
                        <th>Tipe</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th></th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>

              <div class="tab-pane" id="siteplan-menu" role="tabpanel" aria-labelledby="siteplan-menu-tab">
                <div class="d-flex justify-content-end mb-1">
                  <button type="button" class="btn btn-primary btn-sm" onclick="addSiteplanMenu()"><i class="fa fa-plus"></i> Tambah Menu Siteplan</button>
                </div>
                <div class="card-datatable">
                  <table id="siteplan_menu_table" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Departemen</th>
                        <th>Grup</th>
                        <th>Label</th>
                        <th>Icon</th>
                        <th>Class</th>
                        <th>Onclick</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th></th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>

              <div class="tab-pane" id="group-access" role="tabpanel" aria-labelledby="group-tab">
                <div class="row align-items-end">
                  <div class="col-md-5">
                    <div class="form-group">
                      <label for="group_id">Departemen</label>
                      <select id="group_id" class="form-control select2">
                        <option value="">Pilih Departemen</option>
                        <?php foreach ($groups as $group): ?>
                          <option value="<?= (int) $group->id ?>"><?= esc($group->name) ?><?= $group->description ? ' - ' . esc($group->description) : '' ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-primary mb-1" onclick="saveGroupAccess()">Simpan</button>
                  </div>
                </div>
                <div id="group-menu-tree" class="menu-access-tree"></div>
              </div>

              <div class="tab-pane" id="user-access" role="tabpanel" aria-labelledby="user-tab">
                <div class="row align-items-end">
                  <div class="col-md-5">
                    <div class="form-group">
                      <label for="user_id">User</label>
                      <select id="user_id" class="form-control select2">
                        <option value="">Pilih User</option>
                        <?php foreach ($users as $user): ?>
                          <?php $label = trim(($user->nama_karyawan ?: $user->username) . ' (' . $user->username . ')' . ($user->group_name ? ' - ' . $user->group_name : '')); ?>
                          <option value="<?= (int) $user->id ?>"><?= esc($label) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <button type="button" class="btn btn-primary mb-1" onclick="saveUserAccess()">Simpan</button>
                  </div>
                </div>
                <div id="user-menu-tree" class="menu-access-tree"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="modal modal-slide-in fade" id="menu-modal">
  <div class="modal-dialog sidebar-sm">
    <form id="menu-form" class="add-new-record modal-content pt-0">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
      <div class="modal-header mb-1">
        <h5 class="modal-title" id="menu-modal-title">Tambah Menu</h5>
      </div>
      <div class="modal-body flex-grow-1">
        <input type="hidden" id="menu_id" name="id">
        <div class="form-group">
          <label for="menu_name">Nama Menu <span class="text-danger">*</span></label>
          <input type="text" id="menu_name" name="name" class="form-control" maxlength="255" required>
        </div>
        <div class="form-group">
          <label for="menu_url">URL <span class="text-danger">*</span></label>
          <input type="text" id="menu_url" name="url" class="form-control" maxlength="255" required>
        </div>
        <div class="form-group">
          <label for="menu_icon">Icon Feather</label>
          <input type="text" id="menu_icon" name="icon" class="form-control" maxlength="255" placeholder="circle">
        </div>
        <div class="form-group">
          <label for="menu_slug">Slug</label>
          <input type="text" id="menu_slug" name="slug" class="form-control" maxlength="255">
        </div>
        <div class="form-group">
          <label for="menu_parent">Parent</label>
          <select id="menu_parent" name="parent_id" class="form-control">
            <?php foreach ($parent_options as $option): ?>
              <option value="<?= (int) $option->id ?>"><?= esc($option->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="menu_sort_order">Urutan</label>
          <input type="number" id="menu_sort_order" name="sort_order" class="form-control" value="0">
        </div>
        <div class="form-group">
          <label for="menu_is_active">Status</label>
          <select id="menu_is_active" name="is_active" class="form-control">
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary data-submit mr-1" id="menu-form-btn">Simpan</button>
        <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<div class="modal modal-slide-in fade" id="siteplan-menu-modal">
  <div class="modal-dialog sidebar-lg">
    <form id="siteplan-menu-form" class="add-new-record modal-content pt-0">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
      <div class="modal-header mb-1">
        <h5 class="modal-title" id="siteplan-menu-modal-title">Tambah Menu Siteplan</h5>
      </div>
      <div class="modal-body flex-grow-1">
        <input type="hidden" id="siteplan_menu_id" name="id">
        <div class="form-group">
          <label for="siteplan_item_key">Key <span class="text-danger">*</span></label>
          <input type="text" id="siteplan_item_key" name="item_key" class="form-control" maxlength="120" placeholder="mkdt_set_harga" required>
        </div>
        <div class="form-group">
          <label for="siteplan_id_group">Departemen Tampilan</label>
          <select id="siteplan_id_group" name="id_group" class="form-control">
            <?php foreach ($siteplan_groups as $group): ?>
              <option value="<?= (int) $group->id ?>"><?= esc($group->name) ?><?= $group->description ? ' - ' . esc($group->description) : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="siteplan_group_label">Grup Label</label>
          <input type="text" id="siteplan_group_label" name="group_label" class="form-control" maxlength="120" placeholder="Data Konsumen">
        </div>
        <div class="form-group">
          <label for="siteplan_label">Label <span class="text-danger">*</span></label>
          <input type="text" id="siteplan_label" name="label" class="form-control" maxlength="160" required>
        </div>
        <div class="form-group">
          <label for="siteplan_onclick">Handler Klik</label>
          <input type="text" id="siteplan_onclick" name="onclick" class="form-control" placeholder="open_set_harga()">
        </div>
        <div class="form-group">
          <label for="siteplan_icon">Icon Font Awesome</label>
          <input type="text" id="siteplan_icon" name="icon" class="form-control" maxlength="160" placeholder="fas fa-tag">
        </div>
        <div class="form-group">
          <label for="siteplan_btn_class">Class Warna <span class="text-danger">*</span></label>
          <input type="text" id="siteplan_btn_class" name="btn_class" class="form-control" maxlength="120" value="btn-primary" required>
          <small class="text-muted">Contoh: btn-primary, btn-info, btn-outline-warning, btn-outline-danger.</small>
        </div>
        <div class="form-group">
          <label for="siteplan_sort_order">Urutan</label>
          <input type="number" id="siteplan_sort_order" name="sort_order" class="form-control" value="0">
        </div>
        <div class="form-group">
          <label for="siteplan_extra_id">HTML ID</label>
          <input type="text" id="siteplan_extra_id" name="extra_id" class="form-control" maxlength="120" placeholder="bayar_tagihan-btn">
        </div>
        <div class="form-group">
          <label for="siteplan_extra_class">Class Tambahan</label>
          <input type="text" id="siteplan_extra_class" name="extra_class" class="form-control" maxlength="160" placeholder="btn-prod d-none">
        </div>
        <div class="form-group">
          <label for="siteplan_is_active">Status</label>
          <select id="siteplan_is_active" name="is_active" class="form-control">
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
          </select>
        </div>
        <div class="form-group">
          <label>Akses Departemen</label>
          <div class="siteplan-role-grid">
            <?php foreach ($siteplan_groups as $group): ?>
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input siteplan-role-checkbox" name="role_ids[]" id="siteplan_role_<?= (int) $group->id ?>" value="<?= (int) $group->id ?>">
                <label class="custom-control-label" for="siteplan_role_<?= (int) $group->id ?>"><?= esc($group->name) ?></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary data-submit mr-1" id="siteplan-menu-form-btn">Simpan</button>
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
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>

<script>
  const controllerUrl = base_url + '<?= $controller ?>';
  let menuTable = null;
  let siteplanMenuTable = null;

  $(function() {
    $('.select2').select2({ width: '100%' });
    menuTable = $('#menu_table').DataTable({
      paging: true,
      lengthChange: false,
      searching: true,
      ordering: false,
      info: true,
      autoWidth: false,
      responsive: true,
      ajax: {
        url: controllerUrl + '/menu/list',
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
    siteplanMenuTable = $('#siteplan_menu_table').DataTable({
      paging: true,
      lengthChange: false,
      searching: true,
      ordering: false,
      info: true,
      autoWidth: false,
      responsive: true,
      ajax: {
        url: controllerUrl + '/siteplan-menu/list',
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

    $('#group_id').on('change', loadGroupAccess);
    $('#user_id').on('change', loadUserAccess);
    bindMenuForm();
    bindSiteplanMenuForm();
  });

  function addMenu() {
    $('#menu-form')[0].reset();
    $('#menu_id').val('');
    $('#menu_parent').val('0');
    $('#menu_is_active').val('1');
    $('#menu-modal-title').text('Tambah Menu');
    $('#menu-modal').modal('show');
  }

  function editMenu(id) {
    $.post(controllerUrl + '/menu/get', { [csrfName]: csrfHash, id: id }, function(response) {
      csrfHash = response.token;
      if (!response.success) {
        showAlert('error', response.messages);
        return;
      }

      $('#menu_id').val(response.id);
      $('#menu_name').val(response.name);
      $('#menu_url').val(response.url);
      $('#menu_icon').val(response.icon);
      $('#menu_slug').val(response.slug);
      $('#menu_parent').val(response.parent_id);
      $('#menu_sort_order').val(response.sort_order);
      $('#menu_is_active').val(response.is_active);
      $('#menu-modal-title').text('Edit Menu');
      $('#menu-modal').modal('show');
    }, 'json');
  }

  function toggleMenu(id) {
    $.post(controllerUrl + '/menu/toggle', { [csrfName]: csrfHash, id: id }, function(response) {
      csrfHash = response.token;
      showAlert(response.success ? 'success' : 'error', response.messages);
      if (response.success) {
        menuTable.ajax.reload(null, false);
        reloadOpenAccessTabs();
      }
    }, 'json');
  }

  function addSiteplanMenu() {
    $('#siteplan-menu-form')[0].reset();
    $('#siteplan_menu_id').val('');
    $('#siteplan_id_group').val('0');
    $('#siteplan_btn_class').val('btn-primary');
    $('#siteplan_is_active').val('1');
    $('.siteplan-role-checkbox').prop('checked', false);
    $('#siteplan_role_0').prop('checked', true);
    $('#siteplan-menu-modal-title').text('Tambah Menu Siteplan');
    $('#siteplan-menu-modal').modal('show');
  }

  function editSiteplanMenu(id) {
    $.post(controllerUrl + '/siteplan-menu/get', { [csrfName]: csrfHash, id: id }, function(response) {
      csrfHash = response.token;
      if (!response.success) {
        showAlert('error', response.messages);
        return;
      }

      $('#siteplan_menu_id').val(response.id);
      $('#siteplan_item_key').val(response.item_key);
      $('#siteplan_id_group').val(response.id_group);
      $('#siteplan_group_label').val(response.group_label);
      $('#siteplan_label').val(response.label);
      $('#siteplan_onclick').val(response.onclick);
      $('#siteplan_icon').val(response.icon);
      $('#siteplan_btn_class').val(response.btn_class);
      $('#siteplan_sort_order').val(response.sort_order);
      $('#siteplan_extra_id').val(response.extra_id);
      $('#siteplan_extra_class').val(response.extra_class);
      $('#siteplan_is_active').val(response.is_active);
      $('.siteplan-role-checkbox').prop('checked', false);
      (response.role_ids || []).forEach(function(roleId) {
        $('#siteplan_role_' + roleId).prop('checked', true);
      });
      $('#siteplan-menu-modal-title').text('Edit Menu Siteplan');
      $('#siteplan-menu-modal').modal('show');
    }, 'json');
  }

  function toggleSiteplanMenu(id) {
    $.post(controllerUrl + '/siteplan-menu/toggle', { [csrfName]: csrfHash, id: id }, function(response) {
      csrfHash = response.token;
      showAlert(response.success ? 'success' : 'error', response.messages);
      if (response.success) {
        siteplanMenuTable.ajax.reload(null, false);
      }
    }, 'json');
  }

  function bindMenuForm() {
    $('#menu-form').validate({
      submitHandler: function(form) {
        const $btn = $('#menu-form-btn');
        $.ajax({
          url: controllerUrl + '/menu/save',
          type: 'POST',
          data: $(form).serialize() + '&' + csrfName + '=' + csrfHash,
          dataType: 'json',
          beforeSend: function() {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
          },
          success: function(response) {
            csrfHash = response.token;
            showAlert(response.success ? 'success' : 'error', response.messages);
            if (response.success) {
              $('#menu-modal').modal('hide');
              menuTable.ajax.reload(null, false);
              refreshParentOptions(response.parent_options || []);
              reloadOpenAccessTabs();
            }
          },
          complete: function() {
            $btn.prop('disabled', false).html('Simpan');
          }
        });

        return false;
      }
    });
  }

  function bindSiteplanMenuForm() {
    $('#siteplan-menu-form').validate({
      submitHandler: function(form) {
        const $btn = $('#siteplan-menu-form-btn');
        $.ajax({
          url: controllerUrl + '/siteplan-menu/save',
          type: 'POST',
          data: $(form).serialize() + '&' + csrfName + '=' + csrfHash,
          dataType: 'json',
          beforeSend: function() {
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
          },
          success: function(response) {
            csrfHash = response.token;
            showAlert(response.success ? 'success' : 'error', response.messages);
            if (response.success) {
              $('#siteplan-menu-modal').modal('hide');
              siteplanMenuTable.ajax.reload(null, false);
            }
          },
          complete: function() {
            $btn.prop('disabled', false).html('Simpan');
          }
        });

        return false;
      }
    });
  }

  function loadGroupAccess() {
    const groupId = $('#group_id').val();
    if (!groupId) {
      $('#group-menu-tree').html('');
      return;
    }

    $.post(controllerUrl + '/access/group', { [csrfName]: csrfHash, group_id: groupId }, function(response) {
      csrfHash = response.token;
      if (!response.success) {
        showAlert('error', response.messages);
        return;
      }
      renderGroupTree(response.menus, response.selected);
    }, 'json');
  }

  function saveGroupAccess() {
    const groupId = $('#group_id').val();
    if (!groupId) {
      showAlert('error', 'Pilih departemen dulu');
      return;
    }

    const menuIds = $('#group-menu-tree input[name="menu_ids[]"]:checked').map(function() {
      return this.value;
    }).get();

    $.post(controllerUrl + '/access/group/save', { [csrfName]: csrfHash, group_id: groupId, menu_ids: menuIds }, function(response) {
      csrfHash = response.token;
      showAlert(response.success ? 'success' : 'error', response.messages);
    }, 'json');
  }

  function loadUserAccess() {
    const userId = $('#user_id').val();
    if (!userId) {
      $('#user-menu-tree').html('');
      return;
    }

    $.post(controllerUrl + '/access/user', { [csrfName]: csrfHash, user_id: userId }, function(response) {
      csrfHash = response.token;
      if (!response.success) {
        showAlert('error', response.messages);
        return;
      }
      renderUserTree(response.menus, response.allow, response.deny, response.group_menu_ids);
    }, 'json');
  }

  function saveUserAccess() {
    const userId = $('#user_id').val();
    if (!userId) {
      showAlert('error', 'Pilih user dulu');
      return;
    }

    const allowIds = $('#user-menu-tree input[value="allow"]:checked').map(function() {
      return $(this).data('menu-id');
    }).get();
    const denyIds = $('#user-menu-tree input[value="deny"]:checked').map(function() {
      return $(this).data('menu-id');
    }).get();

    $.post(controllerUrl + '/access/user/save', { [csrfName]: csrfHash, user_id: userId, allow_ids: allowIds, deny_ids: denyIds }, function(response) {
      csrfHash = response.token;
      showAlert(response.success ? 'success' : 'error', response.messages);
      if (response.success) {
        loadUserAccess();
      }
    }, 'json');
  }

  function renderGroupTree(menus, selected) {
    const selectedMap = arrayMap(selected);
    const html = menus.map(function(menu) {
      const checked = selectedMap[menu.id] ? 'checked' : '';
      return `<label class="menu-access-row" style="padding-left:${menu.depth * 24}px">
        <input type="checkbox" name="menu_ids[]" value="${menu.id}" ${checked}>
        <span class="menu-access-name">${escapeHtml(menu.name)}</span>
        <span class="badge badge-secondary">${escapeHtml(menu.url || '#')}</span>
      </label>`;
    }).join('');
    $('#group-menu-tree').html(html || '<div class="text-muted">Tidak ada menu aktif.</div>');
  }

  function renderUserTree(menus, allow, deny, groupMenuIds) {
    const allowMap = arrayMap(allow);
    const denyMap = arrayMap(deny);
    const groupMap = arrayMap(groupMenuIds);
    const html = menus.map(function(menu) {
      const state = allowMap[menu.id] ? 'allow' : (denyMap[menu.id] ? 'deny' : 'inherit');
      const inherited = groupMap[menu.id] ? 'Ikut departemen: tampil' : 'Ikut departemen: tidak tampil';
      return `<div class="menu-access-row" style="padding-left:${menu.depth * 24}px">
        <span class="menu-access-name">${escapeHtml(menu.name)} <span class="menu-inherited">${inherited}</span></span>
        <span class="menu-access-radio">
          ${radio(menu.id, 'inherit', 'Ikut', state)}
          ${radio(menu.id, 'allow', 'Izinkan', state)}
          ${radio(menu.id, 'deny', 'Sembunyikan', state)}
        </span>
      </div>`;
    }).join('');
    $('#user-menu-tree').html(html || '<div class="text-muted">Tidak ada menu aktif.</div>');
  }

  function radio(menuId, value, label, state) {
    const checked = state === value ? 'checked' : '';
    return `<label class="mb-0"><input type="radio" name="user_menu_${menuId}" value="${value}" data-menu-id="${menuId}" ${checked}> ${label}</label>`;
  }

  function refreshParentOptions(options) {
    if (!options.length) {
      return;
    }
    const current = $('#menu_parent').val();
    $('#menu_parent').html(options.map(function(option) {
      return `<option value="${option.id}">${escapeHtml(option.name)}</option>`;
    }).join('')).val(current || '0');
  }

  function reloadOpenAccessTabs() {
    if ($('#group_id').val()) {
      loadGroupAccess();
    }
    if ($('#user_id').val()) {
      loadUserAccess();
    }
  }

  function arrayMap(values) {
    const map = {};
    (values || []).forEach(function(value) {
      map[value] = true;
    });
    return map;
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function(match) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[match];
    });
  }

  function showAlert(icon, message) {
    Swal.fire({
      position: 'bottom-end',
      icon: icon,
      title: message,
      showConfirmButton: false,
      timer: 1800
    });
  }
</script>
