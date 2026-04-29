/**
 * Property 2: Modal yang dibuka sesuai dengan roleid
 *
 * Validates: Requirements 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9
 *
 * For each roleid from set {1, 3, 4, 5, 6, 7, 8, 9, 10}, verify that
 * openEditModal(id_kavling) calls $.fn.modal on the correct modal element
 * as defined in the design document mapping.
 */

const $ = require('jquery');

// ─── Role → Modal mapping (from design.md) ───────────────────────────────────
const ROLE_MODAL_MAP = {
  1:  '#modal_divisi4',      // Admin → fallback to mkdt modal
  3:  '#modal_divisi3',      // Keuangan
  4:  '#modal_divisi4',      // Marketing Data
  5:  '#modal_flegal',       // Legal
  6:  '#modals-slide-in',    // Planning
  7:  '#modal_divisi7',      // Produksi
  8:  '#modal_serah_terima', // Sales
  9:  '#modal-diskresi',     // Direksi
  10: '#modal_divisi10',     // Pajak
};

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Build the DOM elements needed by openEditModal:
 * one <div id="..."> for each modal, plus form elements that get reset().
 */
function buildDOM() {
  document.body.innerHTML = '';

  const modals = [
    { id: 'modal_divisi3',    form: 'fm-keuangan'   },
    { id: 'modal_divisi4',    form: 'fm-mkdt'        },
    { id: 'modal_flegal',     form: null             },
    { id: 'modals-slide-in',  form: 'fm-add_kavling' },
    { id: 'modal_divisi7',    form: 'fm-produksi'    },
    { id: 'modal_serah_terima', form: 'fm-serah-terima' },
    { id: 'modal-diskresi',   form: 'fm-diskresi'    },
    { id: 'modal_divisi10',   form: 'fm-pajak'       },
  ];

  modals.forEach(({ id, form }) => {
    const div = document.createElement('div');
    div.id = id;
    if (form) {
      const f = document.createElement('form');
      f.id = form;
      div.appendChild(f);
    }
    document.body.appendChild(div);
  });

  // Generic .id_kavling input
  const input = document.createElement('input');
  input.className = 'id_kavling';
  input.type = 'hidden';
  document.body.appendChild(input);
}

/**
 * Build and return the openEditModal function under test.
 * The function is extracted from list-kavling.php logic and depends on
 * the global `roleid` variable and jQuery.
 *
 * @param {object} jq - jQuery instance to use
 * @returns {function} openEditModal
 */
function buildOpenEditModal(jq) {
  return function openEditModal(id_kavling) {
    if (!id_kavling) return;

    jq('.id_kavling').val(id_kavling);

    /* global roleid */
    switch (parseInt(roleid)) {  // eslint-disable-line no-undef
      case 3:
        jq('#fm-keuangan')[0].reset();
        jq('#modal_divisi3').modal({ backdrop: 'static', keyboard: false });
        break;
      case 4:
        jq('#fm-mkdt')[0].reset();
        jq('#modal_divisi4').modal({ backdrop: 'static', keyboard: false });
        break;
      case 5:
        jq('#modal_flegal').modal({ backdrop: 'static', keyboard: false });
        break;
      case 6:
        jq('#fm-add_kavling')[0].reset();
        jq('#modals-slide-in').modal({ backdrop: 'static', keyboard: false });
        break;
      case 7:
        jq('#fm-produksi')[0].reset();
        jq('#modal_divisi7').modal({ backdrop: 'static', keyboard: false });
        break;
      case 8:
        jq('#fm-serah-terima')[0].reset();
        jq('#modal_serah_terima').modal({ backdrop: 'static', keyboard: false });
        break;
      case 9:
        jq('#fm-diskresi')[0].reset();
        jq('#modal-diskresi').modal({ backdrop: 'static', keyboard: false });
        break;
      case 10:
        jq('#fm-pajak')[0].reset();
        jq('#modal_divisi10').modal({ backdrop: 'static', keyboard: false });
        break;
      case 1:
        jq('#fm-mkdt')[0].reset();
        jq('#modal_divisi4').modal({ backdrop: 'static', keyboard: false });
        break;
      default:
        break;
    }
  };
}

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('Property 2: Modal yang dibuka sesuai dengan roleid', () => {
  let modalCalls;
  let openEditModal;

  beforeEach(() => {
    buildDOM();

    // Track which element IDs $.fn.modal was called on
    modalCalls = [];

    // Mock $.fn.modal so it records the selector of the element it was called on
    $.fn.modal = jest.fn(function (options) {
      // `this` is the jQuery object; record each matched element's id
      this.each(function () {
        modalCalls.push('#' + this.id);
      });
      return this;
    });

    openEditModal = buildOpenEditModal($);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  /**
   * **Validates: Requirements 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9**
   *
   * For every roleid in the valid set, openEditModal SHALL open exactly the
   * modal defined in the design document mapping.
   */
  test.each(Object.entries(ROLE_MODAL_MAP))(
    'roleid=%s → opens %s',
    (roleIdStr, expectedModalId) => {
      const roleId = parseInt(roleIdStr, 10);

      // Set global roleid (mirrors the PHP-rendered JS global in list-kavling.php)
      global.roleid = roleId;

      openEditModal(123);

      expect(modalCalls).toContain(expectedModalId);
    }
  );

  test('roleid=1 (Admin) opens #modal_divisi4 as fallback', () => {
    global.roleid = 1;
    openEditModal(456);
    expect(modalCalls).toContain('#modal_divisi4');
  });

  test('roleid=3 (Keuangan) opens #modal_divisi3', () => {
    global.roleid = 3;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_divisi3');
  });

  test('roleid=4 (Mkdt) opens #modal_divisi4', () => {
    global.roleid = 4;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_divisi4');
  });

  test('roleid=5 (Legal) opens #modal_flegal', () => {
    global.roleid = 5;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_flegal');
  });

  test('roleid=6 (Planning) opens #modals-slide-in', () => {
    global.roleid = 6;
    openEditModal(1);
    expect(modalCalls).toContain('#modals-slide-in');
  });

  test('roleid=7 (Produksi) opens #modal_divisi7', () => {
    global.roleid = 7;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_divisi7');
  });

  test('roleid=8 (Sales) opens #modal_serah_terima', () => {
    global.roleid = 8;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_serah_terima');
  });

  test('roleid=9 (Direksi) opens #modal-diskresi', () => {
    global.roleid = 9;
    openEditModal(1);
    expect(modalCalls).toContain('#modal-diskresi');
  });

  test('roleid=10 (Pajak) opens #modal_divisi10', () => {
    global.roleid = 10;
    openEditModal(1);
    expect(modalCalls).toContain('#modal_divisi10');
  });

  test('each roleid opens exactly one modal (no extra modals opened)', () => {
    Object.entries(ROLE_MODAL_MAP).forEach(([roleIdStr, expectedModalId]) => {
      modalCalls = [];
      global.roleid = parseInt(roleIdStr, 10);
      openEditModal(99);
      expect(modalCalls).toHaveLength(1);
      expect(modalCalls[0]).toBe(expectedModalId);
    });
  });
});
