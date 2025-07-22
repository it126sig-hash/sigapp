<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css"
  href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
</script>

<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3"
              onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
          </div>
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table">
              <thead>
                <tr>
                  <th>ID User</th>
                  <th>Username</th>
                  <th>NIK</th>
                  <th>Nama Karyawan</th>
                  <th>Divisi</th>
                  <th>Level</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal to add new record -->
    <div class="modal modal-slide-in fade" id="add-modal">
      <div class="modal-dialog sidebar-sm">
        <form id="add-form" class="add-new-record modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="form-group">
              <label for="nik">Nama Karyawan </label>
              <select id="nik" name="nik" class="custom-select" required>
                <option value="">Pilih Karyawan</option>

                <?php
                foreach ($karyawan as $p) {
                  $st = ($p->status == 1) ? 'Aktif' : 'Tidak Aktif';
                  echo "<option value='" . $p->nik . "'>$p->nama_karyawan ($st)</option>";
                }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label for="username"> Email: </label>
              <input type="email" id="email" name="email" class="form-control" placeholder="email" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label for="username"> Username: </label>
              <input type="text" id="username" name="username" class="form-control" placeholder="Username" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label for="password"> Password: </label>
              <input type="password" required id="password" name="password" class="form-control"
                placeholder="************" maxlength="255">
            </div>

            <div class="form-group">
              <label for="is_active"> Status </label>
              <select id="active" name="active" class="custom-select">
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Modal to add new record -->
    <div class="modal modal-slide-in fade" id="edit-modal">
      <div class="modal-dialog sidebar-sm">
        <form id="edit-form" class="add-new-record modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title" id="exampleModalLabel">Perbaharui Data</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <div class="form-group">
              <input type="hidden" id="id" name="id" class="form-control" placeholder="NIK" required maxlength="255">
              <label for="nik">NIK: </label>
              <input type="text" id="nik" name="nik" readonly class="form-control" placeholder="Username" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label for="nama_karyawan">Nama Karyawan: </label>
              <input type="text" id="nama_karyawan" name="nama_karyawan" readonly class="form-control"
                placeholder="Username" required maxlength="255">
            </div>

            <div class="form-group">
              <label for="username"> Email: </label>
              <input type="email" id="email" name="email" class="form-control" placeholder="email" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label for="username"> Username: </label>
              <input type="text" id="username" name="username" class="form-control" placeholder="Username" required
                maxlength="255">
            </div>
            <div class="form-group">
              <label for="password"> Password: </label>
              <input type="password" id="password" name="password" class="form-control" placeholder="************"
                maxlength="255">
            </div>

            <div class="form-group">
              <label for="is_active"> Status </label>
              <select id="active" name="active" class="custom-select">
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary data-submit mr-1" id="add-form-btn">Simpan</button>
            <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>
</div>
</div>

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/responsive.bootstrap4.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/validation/jquery.validate.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/polyfill.min.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
  $(function () {
    $('#data_table').DataTable({
      paging: true,
      columnDefs: [{
        targets: [0],
        visible: false
      }],
      lengthChange: false,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      ajax: {
        url: '<?php echo base_url($controller . '/getAll') ?>',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash
        },
        data: function (data) {
          data[csrfName] = csrfHash
        },
        dataSrc: function (r) {
          csrfHash = r.token
          return r.data;
        },
        async: 'true'
      }
    });
  });

  function add() {
    // reset the form 
    $("#add-form")[0].reset();
    $(".form-control").removeClass('is-invalid').removeClass('is-valid');
    $('#add-modal').modal('show');

    // submit the add from 
    $.validator.setDefaults({
      highlight: function (element) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function (element) {
        $(element).removeClass('is-invalid').addClass('is-valid');
      },
      errorElement: 'div ',
      errorClass: 'invalid-feedback',
      errorPlacement: function (error, element) {
        if (element.parent('.input-group').length) {
          error.insertAfter(element.parent());
        } else if ($(element).is('.select')) {
          element.next().after(error);
        } else if (element.hasClass('select2')) {
          //error.insertAfter(element);
          error.insertAfter(element.next());
        } else if (element.hasClass('selectpicker')) {
          error.insertAfter(element.next());
        } else {
          error.insertAfter(element);
        }
      },

      submitHandler: function (form) {

        var form = $('#add-form');
        // remove the text-danger
        $(".text-danger").remove();

        $.ajax({
          url: '<?php echo base_url($controller . '/add') ?>',
          type: 'post',
          data: form.serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
          dataType: 'json',
          beforeSend: function () {
            simpanBtn("#add-form-btn", true)
          },
          success: function (response) {
            csrfHash = response.token;

            if (response.success === true) {

              Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: response.messages,
                showConfirmButton: false,
                timer: 1500
              }).then(function () {
                $('#data_table').DataTable().ajax.reload(null, false).draw(false);
                $('#add-modal').modal('hide');

              })

            } else {

              if (response.messages instanceof Object) {
                $.each(response.messages, function (index, value) {
                  var id = $("#" + index);

                  id.closest('.form-control')
                    .removeClass('is-invalid')
                    .removeClass('is-valid')
                    .addClass(value.length > 0 ? 'is-invalid' : 'is-valid');

                  id.after(value);

                });
              } else {
                Swal.fire({
                  position: 'bottom-end',
                  icon: 'error',
                  title: response.messages,
                  showConfirmButton: false,
                  timer: 1500
                })

              }
            }
            simpanBtn("#add-form-btn", false)
          },
          error: function (xhr, st, err) {            
            simpanBtn("#add-form-btn", false)
            return swal('error', 'Terjadi kesalahan saat memuat data', err)
          }
        });

        return false;
      }
    });
    $('#add-form').validate();
  }

  function edit(id_user) {
    $.ajax({
      url: '<?php echo base_url($controller . '/getOne') ?>',
      type: 'post',
      data: {
        [csrfName]: csrfHash,
        id_user: id_user
      },
      dataType: 'json',
      success: function (response) {
        csrfHash = response.token;

        // reset the form 
        $("#edit-form")[0].reset();
        $(".form-control").removeClass('is-invalid').removeClass('is-valid');
        $('#edit-modal').modal('show');

        for (var i in response) {
          $("#edit-form #" + i).val(response[i]);
        }
        $("#edit-form #active").val((response.active == true) ? 1 : 0);
        $("#edit-form #password").val("");

        // submit the edit from 
        $.validator.setDefaults({
          highlight: function (element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
          },
          unhighlight: function (element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
          },
          errorElement: 'div ',
          errorClass: 'invalid-feedback',
          errorPlacement: function (error, element) {
            if (element.parent('.input-group').length) {
              error.insertAfter(element.parent());
            } else if ($(element).is('.select')) {
              element.next().after(error);
            } else if (element.hasClass('select2')) {
              //error.insertAfter(element);
              error.insertAfter(element.next());
            } else if (element.hasClass('selectpicker')) {
              error.insertAfter(element.next());
            } else {
              error.insertAfter(element);
            }
          },

          submitHandler: function (form) {
            var form = $('#edit-form');
            $(".text-danger").remove();
            $.ajax({
              url: '<?php echo base_url($controller . '/edit_user') ?>',
              type: 'post',
              data: form.serialize() + "&" + csrfName + "=" + csrfHash,
              dataType: 'json',
              beforeSend: function () {
                $('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
              },
              success: function (response) {
                csrfHash = response.token;
                if (response.success === true) {

                  Swal.fire({
                    position: 'bottom-end',
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                  }).then(function () {
                    $('#data_table').DataTable().ajax.reload(null, false).draw(false);
                    $('#edit-modal').modal('hide');
                  })

                } else {

                  if (response.messages instanceof Object) {
                    $.each(response.messages, function (index, value) {
                      var id = $("#" + index);

                      id.closest('.form-control')
                        .removeClass('is-invalid')
                        .removeClass('is-valid')
                        .addClass(value.length > 0 ? 'is-invalid' : 'is-valid');

                      id.after(value);

                    });
                  } else {
                    Swal.fire({
                      position: 'bottom-end',
                      icon: 'error',
                      title: response.messages,
                      showConfirmButton: false,
                      timer: 1500
                    })

                  }
                }
                $('#edit-form-btn').html('Update');
              }
            });

            return false;
          }
        });
        $('#edit-form').validate();

      }
    });
  }

  function remove(id_user, stat, u) {
    let st = (stat == 1) ? "Nonaktifkan" : "Aktifkan";
    Swal.fire({
      title: st + ' user dengan username: ' + u + '?',
      text: "Aktif/nonaktifkan user",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel'
    }).then((result) => {

      if (result.value) {
        $.ajax({
          url: '<?php echo base_url($controller . '/remove') ?>',
          type: 'post',
          data: {
            [csrfName]: csrfHash,
            id: id_user,
            active: stat
          },
          dataType: 'json',
          success: function (response) {
            csrfHash = response.token;

            if (response.success === true) {
              Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: response.messages,
                showConfirmButton: false,
                timer: 1500
              }).then(function () {
                $('#data_table').DataTable().ajax.reload(null, false).draw(false);
              })
            } else {
              Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: response.messages,
                showConfirmButton: false,
                timer: 1500
              })


            }
          }
        });
      }
    })
  }
</script>