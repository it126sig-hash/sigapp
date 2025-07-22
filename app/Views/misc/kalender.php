<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/calendars/fullcalendar.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/responsive.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/tables/datatable/buttons.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/pages/app-calendar.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/forms/select/select2.min.css">

<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
  var base_url = '<?= base_url() ?>';
</script>
<div class="app-content content ">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <div class="content-wrapper">
    <div class="content-header row">
    </div>
    <div class="content-body">
      <!-- Full calendar start -->
      <section>
        <div class="app-calendar overflow-hidden border">
          <div class="row no-gutters">
            <!-- Sidebar -->
            <div class="col-md-2 col-lg-2 col-xl-2" id="app-calendar-sidebar">
              <div class="sidebar-wrapper">
                <!-- <div class="card-body d-flex justify-content-center">
                  <button class="btn btn-primary btn-toggle-sidebar btn-block" data-toggle="modal" data-target="#add-new-sidebar">
                    <span class="align-middle">Add Event</span>
                  </button>
                </div> -->
                <div class="card-body pb-0">
                  <div>
                    <h5 class="section-label mb-1" style="width:200px">
                      <span class="align-middle">Pilih Proyek</span>
                    </h5>
                    <select id="id_proyek" name="id_proyek" class="select2 form-control"></select>
                  </div>

                  <!-- <h5 class="section-label mb-1">
                    <span class="align-middle">Filter</span>
                  </h5>

                  <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input select-all" id="select-all" checked />
                    <label class="custom-control-label" for="select-all">View All</label>
                  </div> -->
                  <div class="calendar-events-filter">
                    <!-- <?php
                          // foreach ($divisi as $d) {
                          //   echo '<div class="custom-control custom-control-danger custom-checkbox mb-1">
                          //           <input type="checkbox" class="custom-control-input input-filter" id="id' . $d->id_divisi . '" data-value="' . $d->id_divisi . '" checked />
                          //           <label class="custom-control-label" for="personal">' . $d->divisi . '</label>
                          //         </div>';
                          // }
                          ?> -->

                    <!-- <div class="custom-control custom-control-primary custom-checkbox mb-1">
                      <input type="checkbox" class="custom-control-input input-filter" id="akad" data-value="akad" checked />
                      <label class="custom-control-label" for="akad">Akad</label>
                    </div>
                    <div class="custom-control custom-control-primary custom-checkbox mb-1">
                      <input type="checkbox" class="custom-control-input input-filter" id="rencana_akad" data-value="rencana_akad" checked />
                      <label class="custom-control-label" for="rencana_akad">Rencana Akad</label>
                    </div> -->

                    <!-- <div class="custom-control custom-control-primary custom-checkbox mb-1">
                      <input type="checkbox" class="custom-control-input input-filter" id="business" data-value="business" checked />
                      <label class="custom-control-label" for="business">Business</label>
                    </div>
                    <div class="custom-control custom-control-warning custom-checkbox mb-1">
                      <input type="checkbox" class="custom-control-input input-filter" id="family" data-value="family" checked />
                      <label class="custom-control-label" for="family">Family</label>
                    </div>
                    <div class="custom-control custom-control-success custom-checkbox mb-1">
                      <input type="checkbox" class="custom-control-input input-filter" id="holiday" data-value="holiday" checked />
                      <label class="custom-control-label" for="holiday">Holiday</label>
                    </div>
                    <div class="custom-control custom-control-info custom-checkbox">
                      <input type="checkbox" class="custom-control-input input-filter" id="etc" data-value="etc" checked />
                      <label class="custom-control-label" for="etc">ETC</label>
                    </div> -->
                  </div>
                </div>
              </div>
              <!-- <div class="mt-auto">
                <img src="<?= base_url() ?>/app-assets/images/pages/calendar-illustration.png" alt="Calendar illustration" class="img-fluid" />
              </div> -->
            </div>
            <!-- /Sidebar -->

            <!-- Calendar -->
            <div class="col-md-10 col-lg-10 col-xl-10 position-relative">
              <div class="card shadow-none border-0 mb-0 rounded-0">
                <div class="card-body pb-0">
                  <div id="calendar" style="height: 50vh;"></div>
                </div>
              </div>
            </div>
            <!-- /Calendar -->

          </div>
        </div>
        <!--/ Calendar Add/Update/Delete event modal-->
      </section>
      <!-- Full calendar end -->

    </div>
  </div>
</div>
<!-- END: Content-->
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

<script src="<?= base_url() ?>/app-assets/vendors/js/calendar/fullcalendar.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/moment.min.js"></script>
<script src="<?= base_url() ?>/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<!-- <script src="https://adminlte.io/themes/v3/plugins/jquery-validation/additional-methods.min.js"></script> -->
<!-- END: Page Vendor JS-->
<script>
  $(document).ready(function() {
    var calendarEl = $('#calendar')[0];
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: "90vh",
      eventSources: [
        // your event source
        {
          url: '<?= base_url() ?>/kalendar/getEvents',
          method: 'POST',
          extraParams: function() {
            return {
              [csrfName]: csrfHash,
              id_proyek: $("#id_proyek").val(),
              id_role: 3
            }
          },
          success: function(r) {
            // csrfName = r.token
          },
          dateClick: function() {
            alert("date")
          },
          failure: function() {
            alert('there was an error while fetching events!');
          }
        }
      ],
      eventClick: function(info) {
        // console.log(info)
        Swal.fire({
          position: 'center',
          // icon: 'Succes',
          title: info.event.extendedProps.calendar,
          text: info.event.title,
          showConfirmButton: true,
          // timer: 1500
        })
        // alert(info.event.title);
        // // change the border color just for fun
        // info.el.style.borderColor = 'red';
      },
    });
    calendar.render();

    $("#id_proyek").select2({
      placeholder: "Pilih Proyek",
      allowClear: true,
      ajax: {
        url: base_url + "/proyek/getAll",
        dataType: 'json',
        delay: 250,
        method: 'post',
        data: function(params) {
          return {
            [csrfName]: csrfHash,
            search: params.term
          };
        },
        processResults: function(r) {
          csrfHash = r.token

          let results = [];
          $.each(r.data, function(index, item) {
            results.push({
              id: item['id_proyek'],
              text: item[1] + ' (' + item[2] + ')'
            });
          });

          return {
            results: results
          };
        },
        cache: true
      },
    })

    $("#id_proyek").on("change", function() {
      calendar.refetchEvents();
    });

    function eventClick(info) {
      alert()
    }

    //remove bug arrow select2
    $(".select2-selection__arrow").removeClass("select2-selection__arrow")
  });
</script>