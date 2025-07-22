<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/coloris/coloris.css">


<script>
  // var csrfName = '<?= csrf_token() ?>',
  //     csrfHash = '<?= csrf_hash() ?>',
  //     base_url = '<?= base_url() ?>'
</script>
<style>
  #clr-picker {
    z-index: 3000 !important;
  }
</style>
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">

            <!-- <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button> -->

            <!-- <button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button> -->
          </div>
          <div class="card-datatable">
            <table id="data_table" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Fill</th>
                  <th>Dashed</th>
                  <th>Keterangan</th>
                  <th>Add by</th>
                  <th>Date add</th>
                  <th>Edit by</th>
                  <th>Date edit</th>

                  <th></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>


    <div id="add-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="text-center bg-info p-3">
            <h4 class="modal-title text-white" id="info-header-modalLabel">Add</h4>
          </div>
          <div class="modal-body">
            <form id="add-form" class="pl-3 pr-3">
              <div class="row">
                <input type="hidden" id="configName" name="configName" class="form-control" placeholder="Item" maxlength="255" required>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="fill"> Fill: <span class="text-danger">*</span> </label>
                    <input type="text" id="fill" name="fill" class="form-control" placeholder="Fill" maxlength="20" required>
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="dashed"> Dashed: </label>
                    <input type="text" id="dashed" name="dashed" class="form-control" placeholder="Dashed" maxlength="255">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="keterangan"> Keterangan: </label>
                    <input type="text" id="keterangan" name="keterangan" class="form-control" placeholder="Keterangan" maxlength="255">
                  </div>
                </div>
              </div>
              <!-- <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="addBy"> Add by: </label>
                    <input type="text" id="addBy" name="addBy" class="form-control" placeholder="Add by" maxlength="255">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="dateAdd"> Date add: </label>
                    <input type="date" id="dateAdd" name="dateAdd" class="form-control" dateISO="true">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="editBy"> Edit by: </label>
                    <input type="text" id="editBy" name="editBy" class="form-control" placeholder="Edit by" maxlength="255">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="dateEdit"> Date edit: </label>
                    <input type="date" id="dateEdit" name="dateEdit" class="form-control" dateISO="true">
                  </div>
                </div>
              </div> -->

              <div class="form-group text-center">
                <div class="btn-group">
                  <button type="submit" class="btn btn-success" id="add-form-btn">Add</button>
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </form>
          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Add modal content -->
    <div id="edit-modal" class="modal modal-slide-in fade" role="dialog" aria-hidden="true">
      <div class="modal-dialog sidebar-sm">
        <div class="modal-content pt-0">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
          <div class="modal-header mb-1">
            <h5 class="modal-title" id="exampleModalLabel">Perbaharui Data</h5>
          </div>
          <div class="modal-body flex-grow-1">
            <form id="edit-form" class="">

              <div class="form-group">
                <label for="fill"> Item <span class="text-danger">*</span> </label>
                <input type="text" readonly id="configName" name="configName" class="form-control" placeholder="Item" maxlength="255" required>
              </div>
              <div class="form-group">
                <label for="fill"> Fill: <span class="text-danger">*</span> </label> <Br>
                <input type="text" id="fill" name="fill" class="form-control" placeholder="Fill" maxlength="20" required data-coloris>
              </div>

              <div class="col-12 hidden">
                <div class="form-group">
                  <label for="dashed"> Dashed: </label>
                  <input type="" id="dashed" name="dashed" class="form-control" placeholder="Dashed" maxlength="255">
                </div>
              </div>

              <div class="form-group">
                <label for="keterangan"> Keterangan: </label>
                <input type="text" id="keterangan" name="keterangan" class="form-control" placeholder="Keterangan" maxlength="255">
              </div>

              <!-- <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="addBy"> Add by: </label>
                    <input type="text" id="addBy" name="addBy" class="form-control" placeholder="Add by" maxlength="255">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="dateAdd"> Date add: </label>
                    <input type="date" id="dateAdd" name="dateAdd" class="form-control" dateISO="true">
                  </div>
                </div>
                <div class="col-12">
                  <div class="form-group">
                    <label for="editBy"> Edit by: </label>
                    <input type="text" id="editBy" name="editBy" class="form-control" placeholder="Edit by" maxlength="255">
                  </div>
                </div>
              </div> 
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="dateEdit"> Date edit: </label>
                    <input type="date" id="dateEdit" name="dateEdit" class="form-control" dateISO="true">
                  </div>
                </div>
              </div>-->

              <div class="form-group text-center">
                <div class="btn-group">
                  <button type="submit" class="btn btn-success" id="edit-form-btn">Update</button>
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
              </div>
            </form>

          </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- /.content -->
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
<script src="<?= base_url() ?>/app-assets/vendors/js/coloris/coloris.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->

<!-- END: Page Vendor JS-->
<script>
  $(function() {
    $('#data_table').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "ajax": {
        "url": '<?php echo base_url($controller . '/getAll') ?>',
        "type": "POST",
        "dataType": "json",
        data: {
          [csrfName]: csrfHash
        },
        data: function(data) {
          data[csrfName] = csrfHash

        },
        dataSrc: function(r) {
          csrfHash = r.token
          return r.data;
        },
        async: "true"
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
          data: form.serialize(), // /converting the form data into array and sending it to server
          dataType: 'json',
          beforeSend: function() {
            $('#add-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
          },
          success: function(response) {

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

  function edit(config_name) {
    $.ajax({
      url: '<?php echo base_url($controller . '/getOne') ?>',
      type: 'post',
      data: {
        [csrfName]: csrfHash,
        config_name: config_name
      },
      dataType: 'json',
      success: function(response) {
        csrfHash = response.token;
        // reset the form 
        $("#edit-form")[0].reset();
        $(".form-control").removeClass('is-invalid').removeClass('is-valid');
        $('#edit-modal').modal('show');

        $("#edit-form #configName").val(response.config_name);
        $("#edit-form #fill").val(response.fill);
        $("#edit-form #dashed").val(response.dashed);
        $("#edit-form #keterangan").val(response.keterangan);
        $("#edit-form #addBy").val(response.add_by);
        $("#edit-form #dateAdd").val(response.date_add);
        $("#edit-form #editBy").val(response.edit_by);
        $("#edit-form #dateEdit").val(response.date_edit);

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
              data: form.serialize()  + "&" + csrfName + "=" + csrfHash ,
              dataType: 'json',
              beforeSend: function() {
                $('#edit-form-btn').html('<i class="fa fa-spinner fa-spin"></i>');
              },
              success: function(response) {

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

  function remove(config_name) {
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
            config_name: config_name,
            [csrfName]: csrfHash
          },
          data: function(data) {
            data[csrfName] = csrfHash
          },
          dataSrc: function(r) {
            csrfHash = r.token
            return r.data;
          },
          async: "true",
          dataType: 'json',
          success: function(response) {

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

  //######################################

  //on chnage search
  $(".dataTables_filter input")
    .off()
    .on('change', function(e) {
      table.search(this.value).draw();
    });



  //on click btn filter
  $("#btn_draw").on("click", function(e) {
    table.draw();
  })

  //remove bug arrow select2
  $(".select2-selection__arrow").removeClass("select2-selection__arrow")
</script>