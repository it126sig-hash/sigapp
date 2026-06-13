<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">

<script>
  // var csrfName = '<?= csrf_token() ?>';
  // var csrfHash = '<?= csrf_hash() ?>';
  // const base_url = '<?= base_url() ?>';
</script>
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <hr class="col-12" />
            <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>
            <button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
          </div>
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table">
              <thead>
                <tr>
                  <th>Id cluster</th>
                  <th>Id proyek</th>
                  <th>Nama Proyek</th>
                  <th>Nama cluster</th>
                  <th>Is active</th>
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
            <div class="row">
              <input type="hidden" id="idCluster" name="idCluster" class="form-control" placeholder="Id cluster" maxlength="255" required>
            </div>
            <div>
              <div class="form-group">
                <label for="idProyek"> Pilih Proyek: </label>
                <select id="idProyek" name="idProyek" class="custom-select select2">
                  <?php
                  foreach ($proyek as $p) {
                    echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="namaCluster"> Nama cluster: </label>
                <input type="text" id="namaCluster" name="namaCluster" class="form-control" placeholder="Nama cluster" maxlength="255">
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="isActive"> Status </label>
                <select id="isActive" name="isActive" class="custom-select">
                  <option value="1">Aktif</option>
                  <option value="0">Tidak Aktif</option>
                </select>
              </div>
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
            <div class="row">
              <input type="hidden" id="idCluster" name="idCluster" class="form-control" placeholder="Id cluster" maxlength="255" required>
            </div>
            <div>
              <div class="form-group">
                <label for="idProyek"> Id proyek: </label>
                <select id="idProyek" name="idProyek" class="custom-select">
                  <?php
                  foreach ($proyek as $p) {
                    echo "<option value='$p->id_proyek'>$p->nama_proyek</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="namaCluster"> Nama cluster: </label>
                <input type="text" id="namaCluster" name="namaCluster" class="form-control" placeholder="Nama cluster" maxlength="255">
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="isActive"> Status </label>
                <select id="isActive" name="isActive" class="custom-select">
                  <option value="1">Aktif</option>
                  <option value="0">Tidak Aktif</option>
                </select>
              </div>
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
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
  $(function() {
    var table = $('#data_table').DataTable({
      paging: true,
      columnDefs: [{
        targets: [1],
        visible: false
      }],
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: '<?php echo base_url($controller . '/getDataTable') ?>',
        type: "POST",
        dataType: "json",
        data: {
          [csrfName]: csrfHash
        },
        data: function(data) {
          data[csrfName] = csrfHash
          data.id_proyek = activeProyekId()
        },
        dataSrc: function(r) {
          csrfHash = r.token
          return r.data;
        },
        async: "true"
      }
    });
    $("#idProyek").select2({
      placeholder: "Pilih proyek",
      allowClear: true
    });
    //on chnage search
    $(".dataTables_filter input")
      .off()
      .on('change', function(e) {
        table.search(this.value).draw();
      });

    if (activeProyekId()) {
      table.draw();
    }

    //on click btn filter
    $("#btn_draw").on("click", function(e) {
      table.draw();
    })
    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")
  });




  function add() {
    // reset the form 
    $("#add-form")[0].reset();
    $(".form-control").removeClass('is-invalid').removeClass('is-valid');
    $('#add-modal').modal('show');
    if (activeProyekId()) {
      $("#add-form #idProyek").val(activeProyekId()).trigger('change');
    } else {
      $("#add-form #idProyek").val('').trigger('change');
    }
    // submit the add from 
    $.validator.setDefaults({
      highlight: function(element) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function(element) {
        $(element).removeClass('is-invalid').addClass('is-valid');
      },
      errorElement: 'div ',
      errorClass: 'invalid-feedback',
      errorPlacement: function(error, element) {
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

      submitHandler: function(form) {

        var form = $('#add-form');
        // remove the text-danger
        $(".text-danger").remove();

        $.ajax({
          url: '<?php echo base_url($controller . '/add') ?>',
          type: 'post',
          data: form.serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
          dataType: 'json',
          beforeSend: function() {
            $('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
          },
          success: function(response) {
            csrfHash = response.token;

            if (response.success === true) {

              Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: response.messages,
                showConfirmButton: false,
                timer: 1500
              }).then(function() {
                $('#data_table').DataTable().ajax.reload(null, false).draw(false);
                $('#add-modal').modal('hide');
              })

            } else {

              if (response.messages instanceof Object) {
                $.each(response.messages, function(index, value) {
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
            $('#add-form-btn').html('Add');
          }
        });

        return false;
      }
    });
    $('#add-form').validate();
  }

  function edit(id_cluster) {
    $.ajax({
      url: '<?php echo base_url($controller . '/getOne') ?>',
      type: 'post',
      data: {
        [csrfName]: csrfHash,
        id_cluster: id_cluster
      },
      dataType: 'json',
      success: function(response) {
        csrfHash = response.token;
        // reset the form 
        $("#edit-form")[0].reset();
        $(".form-control").removeClass('is-invalid').removeClass('is-valid');
        $('#edit-modal').modal('show');

        $("#edit-form #idCluster").val(response.id_cluster);
        $("#edit-form #idProyek").val(response.id_proyek);
        $("#edit-form #namaCluster").val(response.nama_cluster);
        $("#edit-form #isActive").val(response.is_active);

        // submit the edit from 
        $.validator.setDefaults({
          highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
          },
          unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
          },
          errorElement: 'div ',
          errorClass: 'invalid-feedback',
          errorPlacement: function(error, element) {
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

          submitHandler: function(form) {
            var form = $('#edit-form');
            $(".text-danger").remove();
            $.ajax({
              url: '<?php echo base_url($controller . '/edit') ?>',
              type: 'post',
              data: form.serialize() + "&" + csrfName + "=" + csrfHash,
              dataType: 'json',
              beforeSend: function() {
                $('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
              },
              success: function(response) {
                csrfHash = response.token;

                if (response.success === true) {

                  Swal.fire({
                    position: 'bottom-end',
                    icon: 'success',
                    title: response.messages,
                    showConfirmButton: false,
                    timer: 1500
                  }).then(function() {
                    $('#data_table').DataTable().ajax.reload(null, false).draw(false);
                    $('#edit-modal').modal('hide');
                  })

                } else {

                  if (response.messages instanceof Object) {
                    $.each(response.messages, function(index, value) {
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

  function remove(id_cluster) {
    Swal.fire({
      title: 'Are you sure of the deleting process?',
      text: "You cannot back after confirmation",
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
            id_cluster: id_cluster
          },
          dataType: 'json',
          success: function(response) {
            csrfHash = response.token;

            if (response.success === true) {
              Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: response.messages,
                showConfirmButton: false,
                timer: 1500
              }).then(function() {
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