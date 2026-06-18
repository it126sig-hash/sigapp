<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">
<script>
  // var csrfName = '<?= csrf_token() ?>',
  //     csrfHash = '<?= csrf_hash() ?>',
  //     base_url = '<?= base_url() ?>'
</script>
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section id="basic-datatable">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header border-bottom">
            <div class="col-md-4 mb-1">
              <label>Cluster</label>
              <select disabled id="id_cluster" name="id_cluster" class="select2  form-control"></select>
            </div>
            <div class="col-md-4 mb-1">
</div>
            <hr class="col-12" />
            <button type="button" id="btn_draw" class="btn btn-outline-primary waves-effect btn-sm">Filter Data</button>

            <button type="button" class="btn btn-primary data-submit  btn-sm mr-1 col-sm-12 col-md-3 col-lg-3" onclick="add()" title="Add"> <i class="fa fa-plus"></i> Tambah Data</button>
          </div>
          <div class="card-datatable">
            <table id="data_table" class="datatables-basic table">
              <thead>
                <tr>
                  <th>Id jalan</th>
                  <th>Id cluster</th>
                  <th>Nama Cluster</th>
                  <th>Nama jalan</th>

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
              <input type="hidden" id="idJalan" name="idJalan" class="form-control" placeholder="Id jalan" maxlength="255" required>
            </div>
            <div>
              <div class="form-group">
                <label for="idCluster"> Cluster: </label>
                <select id="idCluster" name="idCluster" class="custom-select select2">
                  <?php
                  $x = 0;
                  $group = "";
                  foreach ($cluster as $p) {
                    if($group != $p->nama_proyek){
                      $x = 0;
                      $group = $p->nama_proyek;
                    }

                    if($x == 0)
                      echo "<optgroup label='".$group."'>";
                    
                    echo "<option  value='$p->id_cluster'>$p->nama_cluster</option>";

                    if($x == 0)
                      echo "</optgroup>";

                    $x++;
                  }
                  ?>
                </select>
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="namaJalan"> Nama jalan: </label>
                <input type="text" id="namaJalan" name="namaJalan" class="form-control" placeholder="Nama jalan" maxlength="255">
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
              <input type="hidden" id="idJalan" name="idJalan" class="form-control" placeholder="Id jalan" maxlength="255" required>
            </div>
            <div>
              <div class="form-group">
                <label for="idCluster"> Cluster: </label>
                <select id="idCluster" name="idCluster" class="custom-select">
                  <?php
                  foreach ($cluster as $p) {
                    echo "<option  value='$p->id_cluster'>($p->nama_proyek) $p->nama_cluster</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div>
              <div class="form-group">
                <label for="namaJalan"> Nama jalan: </label>
                <input type="text" id="namaJalan" name="namaJalan" class="form-control" placeholder="Nama jalan" maxlength="255">
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
          lengthChange: false,
          searching: true,
          ordering: true,
          info: true,
          autoWidth: false,
          responsive: true,
          processing: true,
          serverSide: true,
          ajax: {
            url: '<?php echo base_url($controller . '/getDatatable') ?>',
            type: "POST",
            dataType: "json",
            data: {
              [csrfName]: csrfHash
            },
            data: function(data) {
              data[csrfName] = csrfHash
              data.id_proyek = activeProyekId()
              data.id_cluster = $("#id_cluster").val()  
              
            },
            dataSrc: function(r) {
              csrfHash = r.token
              return r.data;
            },
            async: "true"
          },

        });
        //on chnage search
        $(".dataTables_filter input")
          .off()
          .on('change', function(e) {
            table.search(this.value).draw();
          });

        if (activeProyekId()) {
          $("#id_cluster").prop("disabled", false);
          table.draw();
        }

        //select2 cluster
        $("#id_cluster").select2({
          placeholder: "Pilih Cluster",
          allowClear: true,
          ajax: {
            url: base_url + "/cluster/getAll",
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function(params) {
              return {
                [csrfName]: csrfHash,
                search: params.term,
                id_proyek: activeProyekId()
              };
            },
            processResults: function(r) {
              csrfHash = r.token

              let results = [];
              $.each(r.data, function(index, item) {
                results.push({
                  id: item[0],
                  text: item[3]
                });
              });

              return {
                results: results
              };
            },
            cache: true
          },
        })

        $("#idCluster").select2();

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
                $('#add-form-btn').prop("disabled", true);
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
                    $('#add-form-btn').prop("disabled", false);
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
                $('#add-form-btn').prop("disabled", false);
              }
            });

            return false;
          }
        });
        $('#add-form').validate();
      }

      function edit(id_jalan) {
        $.ajax({
          url: '<?php echo base_url($controller . '/getOne') ?>',
          type: 'post',
          data: {
            [csrfName]: csrfHash,
            id_jalan: id_jalan
          },
          dataType: 'json',
          success: function(response) {
            csrfHash = response.token;
            // reset the form 
            $("#edit-form")[0].reset();
            $(".form-control").removeClass('is-invalid').removeClass('is-valid');
            $('#edit-modal').modal('show');

            $("#edit-form #idJalan").val(response.id_jalan);
            $("#edit-form #idCluster").val(response.id_cluster);
            $("#edit-form #namaJalan").val(response.nama_jalan);

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

      function remove(id_jalan) {
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
                id_jalan: id_jalan
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