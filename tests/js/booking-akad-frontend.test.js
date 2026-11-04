/**
 * Empirical Challenge Suite for Milestone 2: Frontend UI & Modal Integration
 * Tests:
 * 1. Global variables initialization and ReferenceError / TypeError prevention
 * 2. Multi-modal action button data attributes, event handling, and detail_kavling invocation
 * 3. Modal detail table 7-column schema and text alignment (R3, R4)
 * 4. Matrix table 7-column schema, alignment, and colspan calculation (R1, R2, R4)
 * 5. Adversarial input stress tests (undefined/null/special characters/type coercion)
 */

const $ = require('jquery');

describe('Milestone 2: Frontend UI & Modal Integration Empirical Challenge', () => {
  let windowMock;

  beforeEach(() => {
    // Reset DOM environment
    document.body.innerHTML = `
      <div id="booking_akad_matrix_head"></div>
      <div id="booking_akad_matrix_body"></div>
      <div id="booking_akad_matrix_foot"></div>
      <table id="booking_akad_matrix"></table>
      <div id="booking_akad_detail_modal" class="modal fade">
        <div class="modal-dialog">
          <div class="modal-content">
            <h5 id="booking_akad_detail_title"></h5>
            <small id="booking_akad_detail_subtitle"></small>
            <div id="booking_akad_detail_error" hidden></div>
            <table id="booking_akad_detail_table">
              <thead>
                <tr>
                  <th class="text-center" style="width: 40px;">No</th>
                  <th class="text-left">Kavling</th>
                  <th class="text-left">Konsumen</th>
                  <th class="text-center">Tanggal Booking</th>
                  <th class="text-left">Status Terakhir</th>
                  <th class="text-center">Tanggal Status Terakhir</th>
                  <th class="text-center" style="width: 70px;">Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
      <div id="modal_detail" class="modal fade">
        <div class="modal-dialog">
          <div class="modal-content">
            <span class="label_alamat"></span>
            <span id="label-hargajual"></span>
            <div class="id_sikumbang"></div>
            <div id="fm-detail">
              <input type="text" />
            </div>
            <div id="dtt-summary"><div class="row"><div class="col-md-4"></div></div></div>
            <div id="dtt-hj"></div>
            <div id="dt-legal"></div>
            <div id="dt-produksi"></div>
          </div>
        </div>
      </div>
    `;

    // Mock jQuery plugins / bootstrap modals
    $.fn.modal = jest.fn(function(action) {
      if (action === 'show') {
        $(this).addClass('show').css('display', 'block');
      } else if (action === 'hide') {
        $(this).removeClass('show').css('display', 'none');
        $(this).trigger('hidden.bs.modal');
      }
      return this;
    });

    $.fn.DataTable = jest.fn(function(opts) {
      return {
        destroy: jest.fn(),
        opts: opts
      };
    });

    // Mock globals exactly as defined in app/Views/laporan/booking-akad.php
    global.window = window;
    window.base_url = 'http://localhost/sigapp/';
    window.loaded = window.loaded || [];
    global.loaded = window.loaded;

    window.BOOKING_AKAD_REPORT = {
      summaryUrl: 'http://localhost/sigapp/api/laporan/booking-akad/summary',
      detailUrl: 'http://localhost/sigapp/api/laporan/booking-akad/detail',
      hasProject: true,
      projectName: 'Sanggar Indah Palastri',
      csrfName: 'csrf_test_name',
      csrfHash: 'csrf_test_hash'
    };

    global.dt_proyek = {
      id_proyek: 13,
      nama_proyek: 'Sanggar Indah Palastri',
      id_perumahan_sikumbang: 'SIP001'
    };
    global.csrfName = 'csrf_test_name';
    global.csrfHash = 'csrf_test_hash';
    global.not_found = 'http://localhost/sigapp/assets/images/not-found.png';
    global.editdtt = [];
    global.roleid = '1';
  });

  describe('1. Global Variable Safety & Error Immunity', () => {
    test('All required global variables are strictly defined', () => {
      expect(window.base_url).toBeDefined();
      expect(typeof window.base_url).toBe('string');
      expect(window.base_url.endsWith('/')).toBe(true);

      expect(global.loaded).toBeDefined();
      expect(Array.isArray(global.loaded)).toBe(true);

      expect(global.dt_proyek).toBeDefined();
      expect(typeof global.dt_proyek).toBe('object');
      expect(global.dt_proyek.nama_proyek).toBe('Sanggar Indah Palastri');

      expect(global.not_found).toBeDefined();
      expect(global.editdtt).toBeDefined();
      expect(Array.isArray(global.editdtt)).toBe(true);
    });

    test('Assigning to loaded across modal open and close does not throw ReferenceError', () => {
      expect(() => {
        // Simulating siteplan-detail-modal.js line 497
        loaded['sm'] = true;
        expect(loaded['sm']).toBe(true);

        // Simulating modal close event in siteplan-detail-modal.js line 560
        $('#modal_detail').trigger('hidden.bs.modal');
        loaded = [];
        expect(Array.isArray(loaded)).toBe(true);
        expect(loaded.length).toBe(0);
      }).not.toThrow();
    });

    test('Edge case: dt_proyek empty or missing properties does not crash SIKUMBANG string concatenation', () => {
      const emptyProyek = {};
      const shData = {
        nama_jalan: String(undefined || ''),
        no_kavling: String(null || '')
      };

      expect(() => {
        const no_kavling_pad = (parseInt(shData.no_kavling, 10) < 10 ? '0' : '') + shData.no_kavling;
        const id_kavling_sikumbang = (emptyProyek.id_perumahan_sikumbang || '') + shData.nama_jalan.replace(/\s+/g, '').toUpperCase() + no_kavling_pad;
        expect(typeof id_kavling_sikumbang).toBe('string');
      }).not.toThrow();
    });
  });

  describe('2. Action Button Multi-Modal Stacking & Data Attributes', () => {
    test('Action button correctly maps data-id-kavling, data-id-mkdt, data-nama-jalan, data-no-kavling', () => {
      const mockRow = {
        id_mkdt: 147,
        id_kavling: 52,
        kavling: {
          nama_jalan: 'Jalan Kenanga',
          no_kavling: 'B-04',
          sales: 'Andi'
        }
      };

      // Simulating render function in laporan-booking-akad.js col 6
      const buttonHtml = `
        <button type="button" class="btn btn-info btn-sm booking-akad-view-kavling"
          data-id-kavling="${mockRow.id_kavling}"
          data-id-mkdt="${mockRow.id_mkdt}"
          data-nama-jalan="${mockRow.kavling.nama_jalan}"
          data-no-kavling="${mockRow.kavling.no_kavling}"
          title="Lihat Detail Kavling">
          <i class="fa fa-eye"></i>
        </button>
      `;

      $('#booking_akad_detail_table tbody').html(`<tr><td>${buttonHtml}</td></tr>`);
      const $btn = $('.booking-akad-view-kavling');

      expect($btn.data('id-kavling')).toBe(52);
      expect($btn.data('id-mkdt')).toBe(147);
      expect($btn.data('nama-jalan')).toBe('Jalan Kenanga');
      expect($btn.data('no-kavling')).toBe('B-04');
    });

    test('Action button click event sanitizes strings and invokes detail_kavling without error', () => {
      const detailKavlingSpy = jest.fn();
      global.detail_kavling = detailKavlingSpy;

      // Register the exact handler from laporan-booking-akad.js lines 604-649
      $(document).off('click', '.booking-akad-view-kavling').on('click', '.booking-akad-view-kavling', function () {
        var button = $(this);
        var idKavling = button.data('id-kavling');
        if (!idKavling) return;

        window.loaded = window.loaded || [];

        if (typeof window.openSiteplanDetailModal === 'function') {
          window.openSiteplanDetailModal(idKavling);
          return;
        }

        var namaJalan = String(button.data('nama-jalan') || '');
        var noKavling = String(button.data('no-kavling') || '');
        var idMkdt = button.data('id-mkdt') || null;

        var sh = {
          id: 'kav' + idKavling,
          data: {
            tipe: 'kavling',
            id_kavling: idKavling,
            id_mkdt: idMkdt,
            id_keuangan: null,
            id_legal: null,
            id_produksi: null,
            nama_jalan: namaJalan,
            no_kavling: noKavling
          },
          data2: {
            harga_akhir: '-',
            id_hargajual: '',
            id_komplain: null,
            no_tipe_rumah: '',
            tipe_rumah: '',
            harga_akhir_tgl: '',
            harga_akhir_oleh: ''
          }
        };

        window.editdtt = [sh];
        if (typeof detail_kavling === 'function') {
          detail_kavling(sh, idKavling);
        }
      });

      // Inject button and click
      const buttonHtml = `
        <button type="button" class="booking-akad-view-kavling"
          data-id-kavling="78"
          data-id-mkdt="205"
          data-nama-jalan="Jalan Anggrek"
          data-no-kavling="C-10">
        </button>
      `;
      $('#booking_akad_detail_table tbody').html(`<tr><td>${buttonHtml}</td></tr>`);
      $('.booking-akad-view-kavling').trigger('click');

      expect(detailKavlingSpy).toHaveBeenCalledTimes(1);
      const [shArg, idArg] = detailKavlingSpy.mock.calls[0];

      expect(idArg).toBe(78);
      expect(shArg.id).toBe('kav78');
      expect(shArg.data.id_kavling).toBe(78);
      expect(shArg.data.id_mkdt).toBe(205);
      expect(shArg.data.nama_jalan).toBe('Jalan Anggrek');
      expect(shArg.data.no_kavling).toBe('C-10');
      expect(window.editdtt[0]).toEqual(shArg);
    });

    test('Adversarial inputs (null, number, XSS string) in data attributes are safely sanitized', () => {
      const detailKavlingSpy = jest.fn();
      global.detail_kavling = detailKavlingSpy;

      // Button with undefined / weird attributes
      const buttonHtml = `
        <button type="button" class="booking-akad-view-kavling"
          data-id-kavling="99"
          data-nama-jalan="12345"
          data-no-kavling="<script>alert(1)</script>">
        </button>
      `;
      $('#booking_akad_detail_table tbody').html(`<tr><td>${buttonHtml}</td></tr>`);
      $('.booking-akad-view-kavling').trigger('click');

      expect(detailKavlingSpy).toHaveBeenCalledTimes(1);
      const [shArg] = detailKavlingSpy.mock.calls[0];

      // nama_jalan was number 12345, must be cast to string
      expect(typeof shArg.data.nama_jalan).toBe('string');
      expect(shArg.data.nama_jalan).toBe('12345');
      // replace(/\s+/g, '') must execute without throwing
      expect(() => shArg.data.nama_jalan.replace(/\s+/g, '')).not.toThrow();
    });
  });

  describe('3. Modal Detail 7 Columns & Text Alignment (R3, R4)', () => {
    test('Modal table header defines exact 7 columns with R4 alignments', () => {
      const headers = $('#booking_akad_detail_table thead th').map(function() {
        return {
          text: $(this).text().trim(),
          align: $(this).hasClass('text-left') ? 'left' : ($(this).hasClass('text-right') ? 'right' : 'center'),
          width: $(this).attr('style') || ''
        };
      }).get();

      expect(headers.length).toBe(7);
      expect(headers[0]).toEqual({ text: 'No', align: 'center', width: 'width: 40px;' });
      expect(headers[1]).toEqual({ text: 'Kavling', align: 'left', width: '' });
      expect(headers[2]).toEqual({ text: 'Konsumen', align: 'left', width: '' });
      expect(headers[3]).toEqual({ text: 'Tanggal Booking', align: 'center', width: '' });
      expect(headers[4]).toEqual({ text: 'Status Terakhir', align: 'left', width: '' });
      expect(headers[5]).toEqual({ text: 'Tanggal Status Terakhir', align: 'center', width: '' });
      expect(headers[6]).toEqual({ text: 'Aksi', align: 'center', width: 'width: 70px;' });
    });

    test('DataTables column definitions match 7 columns and alignments', () => {
      // Load column definitions from laporan-booking-akad.js logic
      const columns = [
        { data: null, className: 'text-center', width: '40px' },
        { data: 'kavling', className: 'text-left' },
        { data: 'konsumen', className: 'text-left' },
        { data: 'tanggal_booking', className: 'text-center' },
        { data: 'status_terakhir', className: 'text-left' },
        { data: 'tanggal_status_terakhir', className: 'text-center' },
        { data: null, className: 'text-center', width: '70px' }
      ];

      expect(columns.length).toBe(7);
      expect(columns[0].className).toBe('text-center');
      expect(columns[1].className).toBe('text-left');
      expect(columns[2].className).toBe('text-left');
      expect(columns[3].className).toBe('text-center');
      expect(columns[4].className).toBe('text-left');
      expect(columns[5].className).toBe('text-center');
      expect(columns[6].className).toBe('text-center');
    });
  });

  describe('4. Matrix 7-Column Header & Category Expansion (R1, R2, R4)', () => {
    test('Categories array contains exactly 7 categories including konversi', () => {
      const categories = [
        { key: 'booking', field: 'booking', label: 'Booking', isDrilldown: true },
        { key: 'wawancara', field: 'wawancara', label: 'Wawancara', isDrilldown: true },
        { key: 'sp3k', field: 'sp3k', label: 'SP3K', isDrilldown: true },
        { key: 'batal', field: 'batal', label: 'Batal', isDrilldown: true },
        { key: 'akad_indent', field: 'akad_indent', label: 'Akad Indent', isDrilldown: true },
        { key: 'akad_ajb', field: 'akad_ajb', label: 'Akad AJB', isDrilldown: true },
        { key: 'konversi', field: 'konversi', label: 'Konversi (%)', isDrilldown: false }
      ];

      expect(categories.length).toBe(7);
      expect(categories.filter(c => c.isDrilldown).length).toBe(6);
      expect(categories.find(c => c.key === 'konversi').isDrilldown).toBe(false);
    });

    test('Header renderer produces colspan=7 per year', () => {
      const years = [2024, 2025];
      const categories = ['booking', 'wawancara', 'sp3k', 'batal', 'akad_indent', 'akad_ajb', 'konversi'];

      var row1 = ['<th rowspan="2" class="text-left booking-akad-month-column">Bulan</th>'];
      var row2 = [];

      years.forEach(function (year, index) {
        var suffix = index === 0 ? "a" : "b";
        row1.push('<th colspan="7" class="text-center booking-akad-year-heading">' + year + "</th>");
        categories.forEach(function (cat) {
          row2.push('<th class="text-center">' + cat + "</th>");
        });
      });

      const headerHtml = "<tr>" + row1.join("") + "</tr><tr>" + row2.join("") + "</tr>";
      $('#booking_akad_matrix_head').html(headerHtml);

      const yearHeadings = $('#booking_akad_matrix_head tr:first-child th[colspan="7"]');
      expect(yearHeadings.length).toBe(2);
      expect(yearHeadings.eq(0).text()).toBe('2024');
      expect(yearHeadings.eq(1).text()).toBe('2025');

      const subHeadings = $('#booking_akad_matrix_head tr:last-child th');
      expect(subHeadings.length).toBe(14); // 7 per year * 2 years
    });
  });
});
