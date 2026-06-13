<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/calendars/fullcalendar.min.css">

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/vendors/css/extensions/sweetalert2.min.css">

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>/app-assets/css/pages/app-calendar.css">



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

      <section>

        <div class="app-calendar overflow-hidden border">

          <div class="row no-gutters">

            <!-- Sidebar -->

            <div class="col-md-2 col-lg-2 col-xl-2" id="app-calendar-sidebar">

              <div class="sidebar-wrapper">

                <div class="card-body pb-0">

                  <h6 class="section-label mb-1">Filter Divisi</h6>

                  <div class="calendar-events-filter">

                    <div class="custom-control custom-control-primary custom-checkbox mb-1">

                      <input type="checkbox" class="custom-control-input" id="select-all" checked />

                      <label class="custom-control-label" for="select-all">Tampilkan Semua</label>

                    </div>

                    <?php foreach (($data['divisi'] ?? []) as $d) : ?>

                      <div class="custom-control custom-control-primary custom-checkbox mb-1">

                        <input

                          type="checkbox"

                          class="custom-control-input input-filter"

                          id="divisi-<?= esc($d->id_divisi) ?>"

                          data-value="<?= esc($d->divisi_key) ?>"

                          checked

                        />

                        <label class="custom-control-label" for="divisi-<?= esc($d->id_divisi) ?>">

                          <?= esc($d->divisi) ?>

                        </label>

                      </div>

                    <?php endforeach; ?>

                  </div>



                  <div class="mt-1">

                    <h6 class="section-label mb-1">Event Periode Ini</h6>

                    <div id="calendar-event-list" class="calendar-event-list">

                      <p class="text-muted small mb-0" id="calendar-event-list-empty">Memuat event...</p>

                    </div>

                  </div>

                </div>

              </div>

            </div>

            <!-- /Sidebar -->



            <!-- Calendar -->

            <div class="col-md-10 col-lg-10 col-xl-10 position-relative">

              <div class="card shadow-none border-0 mb-0 rounded-0">

                <div class="card-body pb-0">

                  <div id="calendar-loading" class="text-muted small mb-50" style="display:none;">Memuat event...</div>

                  <div id="calendar" style="height: 50vh;"></div>

                </div>

              </div>

            </div>

            <!-- /Calendar -->

          </div>

        </div>

      </section>

    </div>

  </div>

</div>



<script src="<?= base_url() ?>/app-assets/vendors/js/vendors.min.js"></script>

<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>

<script src="<?= base_url() ?>/app-assets/vendors/js/calendar/fullcalendar.min.js"></script>

<script src="<?= base_url() ?>/app-assets/vendors/js/extensions/moment.min.js"></script>

<script>
  $(document).ready(function() {
    var divisiEventMap = <?= json_encode($data['divisiEventMap'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var eventMeta = <?= json_encode($data['eventMeta'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    var calendarEl = document.getElementById('calendar');

    var calEventFilter = $('.calendar-events-filter');

    var filterInput = $('.input-filter');

    var selectAll = $('#select-all');

    var calendarEvents = [];

    var visibleRange = { start: null, end: null };



    function selectedDivisiKeys() {

      var selected = [];

      filterInput.filter(':checked').each(function() {

        selected.push($(this).attr('data-value'));

      });

      return selected;

    }



    function eventKeysFromDivisi(divisiKeys) {

      var keys = {};

      divisiKeys.forEach(function(divisiKey) {

        var mapped = divisiEventMap[divisiKey] || [];

        mapped.forEach(function(eventKey) {

          keys[eventKey] = true;

        });

      });

      return Object.keys(keys);

    }



    function showEventDetail(info) {

      Swal.fire({

        position: 'center',

        title: info.event.extendedProps.calendar,

        text: info.event.title,

        showConfirmButton: true,

      });

    }



    function renderSidebarEventList() {

      var listEl = $('#calendar-event-list');

      var divisiKeys = selectedDivisiKeys();

      var allowedEventKeys = eventKeysFromDivisi(divisiKeys);



      var filtered = calendarEvents.filter(function(event) {

        var eventKey = event.extendedProps && event.extendedProps.eventKey;

        if (!eventKey || allowedEventKeys.indexOf(eventKey) === -1) {

          return false;

        }

        if (!visibleRange.start || !visibleRange.end) {

          return true;

        }

        var eventDate = (event.start || '').substring(0, 10);

        return eventDate >= visibleRange.start && eventDate < visibleRange.end;

      });



      filtered.sort(function(a, b) {

        return (a.start || '').localeCompare(b.start || '');

      });



      if (filtered.length === 0) {

        listEl.html('<p class="text-muted small mb-0">Tidak ada event pada periode ini.</p>');

        return;

      }



      var html = '';

      filtered.forEach(function(event, index) {

        var eventKey = event.extendedProps.eventKey;

        var meta = eventMeta[eventKey] || {};

        var color = meta.color || '#7367f0';

        var dateLabel = moment(event.start).format('DD MMM YYYY');

        var title = (event.title || '').replace(/\n/g, ' — ');



        html += '<div class="calendar-event-list-item" data-event-index="' + index + '">';

        html += '<span class="calendar-event-dot" style="background-color:' + color + ';"></span>';

        html += '<div class="calendar-event-list-body">';

        html += '<div class="calendar-event-list-date">' + dateLabel + '</div>';

        html += '<div class="calendar-event-list-title">' + $('<div>').text(title).html() + '</div>';

        html += '</div></div>';

      });



      listEl.html(html);

      listEl.data('filtered-events', filtered);

    }



    function fetchEvents(info, successCallback, failureCallback) {

      var types = eventKeysFromDivisi(selectedDivisiKeys());

      var idProyek = activeProyekId();



      if (!idProyek || types.length === 0) {

        calendarEvents = [];

        renderSidebarEventList();

        successCallback([]);

        return;

      }



      $('#calendar-loading').show();



      $.ajax({

        url: base_url + '/kalendar/getEvents',

        method: 'POST',

        dataType: 'json',

        data: {

          [csrfName]: csrfHash,

          id_proyek: idProyek,

          start: info.startStr,

          end: info.endStr,

          types: types,

        },

        success: function(response) {

          $('#calendar-loading').hide();

          calendarEvents = Array.isArray(response) ? response : [];

          renderSidebarEventList();

          successCallback(calendarEvents);

        },

        error: function() {

          $('#calendar-loading').hide();

          if (typeof failureCallback === 'function') {

            failureCallback();

          } else {

            Swal.fire('Error', 'Gagal memuat event kalender.', 'error');

          }

        },

      });

    }



    var calendar = new FullCalendar.Calendar(calendarEl, {

      initialView: 'dayGridMonth',

      height: '90vh',

      events: fetchEvents,

      dayMaxEvents: 3,

      moreLinkClick: 'popover',

      headerToolbar: {

        start: 'prev,next today',

        center: 'title',

        end: 'dayGridMonth,timeGridWeek,listMonth',

      },

      loading: function(isLoading) {

        $('#calendar-loading').toggle(isLoading);

      },

      datesSet: function(info) {

        visibleRange = {

          start: moment(info.start).format('YYYY-MM-DD'),

          end: moment(info.end).format('YYYY-MM-DD'),

        };

        renderSidebarEventList();

      },

      eventContent: function(arg) {

        var title = (arg.event.title || '').replace(/\n/g, '<br>');

        return { html: '<div class="fc-event-title">' + title + '</div>' };

      },

      eventClick: function(info) {

        showEventDetail(info);

      },

    });



    calendar.render();



    if (activeProyekId()) {

      calendar.refetchEvents();

    }



    selectAll.on('change', function() {

      var checked = $(this).prop('checked');

      filterInput.prop('checked', checked);

      calendar.refetchEvents();

    });



    filterInput.on('change', function() {

      var total = filterInput.length;

      var checked = filterInput.filter(':checked').length;

      selectAll.prop('checked', total > 0 && checked === total);

      calendar.refetchEvents();

    });



    $(document).on('click', '.calendar-event-list-item', function() {

      var filtered = $('#calendar-event-list').data('filtered-events') || [];

      var index = parseInt($(this).attr('data-event-index'), 10);

      var eventData = filtered[index];

      if (!eventData) {

        return;

      }

      showEventDetail({

        event: {

          title: eventData.title,

          extendedProps: eventData.extendedProps,

        },

      });

    });

  });

</script>
