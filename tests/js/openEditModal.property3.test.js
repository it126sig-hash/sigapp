/**
 * Property 3: `id_kavling` selalu diteruskan ke modal
 *
 * **Validates: Requirements 4.10**
 *
 * For any positive integer `id_kavling`, after `openEditModal(id_kavling)` is
 * called, ALL `.id_kavling` inputs on the page SHALL have the same value as
 * the provided `id_kavling`.
 *
 * This property must hold regardless of which roleid is active (i.e., which
 * modal branch is taken inside openEditModal).
 */

const $ = require('jquery');

// ─── All valid roleids that have a modal ─────────────────────────────────────
const VALID_ROLEIDS = [1, 3, 4, 5, 6, 7, 8, 9, 10];

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Build the full DOM needed by openEditModal: one div per modal, one form per
 * modal that has a reset(), and multiple .id_kavling inputs scattered across
 * different modal containers — simulating multiple modals each having their
 * own hidden input.
 */
function buildDOM() {
  document.body.innerHTML = '';

  const modals = [
    { id: 'modal_divisi3',      form: 'fm-keuangan'    },
    { id: 'modal_divisi4',      form: 'fm-mkdt'         },
    { id: 'modal_flegal',       form: null              },
    { id: 'modals-slide-in',    form: 'fm-add_kavling'  },
    { id: 'modal_divisi7',      form: 'fm-produksi'     },
    { id: 'modal_serah_terima', form: 'fm-serah-terima' },
    { id: 'modal-diskresi',     form: 'fm-diskresi'     },
    { id: 'modal_divisi10',     form: 'fm-pajak'        },
  ];

  modals.forEach(({ id, form }) => {
    const div = document.createElement('div');
    div.id = id;

    if (form) {
      const f = document.createElement('form');
      f.id = form;
      div.appendChild(f);
    }

    // Each modal container has its own hidden .id_kavling input
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.className = 'id_kavling';
    div.appendChild(hiddenInput);

    document.body.appendChild(div);
  });

  // Also add a standalone .id_kavling input outside any modal
  // (simulates a shared input that may exist at page level)
  const standaloneInput = document.createElement('input');
  standaloneInput.type = 'hidden';
  standaloneInput.className = 'id_kavling';
  document.body.appendChild(standaloneInput);
}

/**
 * Build and return the openEditModal function under test.
 * Mirrors the implementation from design.md Step 7 / list-kavling.php.
 *
 * @param {object} jq - jQuery instance to use
 * @returns {function} openEditModal
 */
function buildOpenEditModal(jq) {
  return function openEditModal(id_kavling) {
    if (!id_kavling) return;

    // Set id_kavling on ALL .id_kavling inputs — this is the property under test
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

describe('Property 3: id_kavling selalu diteruskan ke modal', () => {
  let openEditModal;

  beforeEach(() => {
    buildDOM();

    // Stub $.fn.modal so it doesn't throw (Bootstrap not loaded in jsdom)
    $.fn.modal = jest.fn(function () { return this; });

    openEditModal = buildOpenEditModal($);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  // ── Core property: ALL .id_kavling inputs get the correct value ─────────────

  /**
   * **Validates: Requirements 4.10**
   *
   * For a representative set of positive integer id_kavling values, verify
   * that every .id_kavling input on the page receives that exact value after
   * openEditModal is called — regardless of which roleid is active.
   */
  const ID_KAVLING_SAMPLES = [1, 2, 10, 99, 100, 500, 999, 1000, 9999, 99999];

  test.each(ID_KAVLING_SAMPLES)(
    'id_kavling=%i → all .id_kavling inputs have that value (roleid=4)',
    (id_kavling) => {
      global.roleid = 4;
      openEditModal(id_kavling);

      const inputs = document.querySelectorAll('.id_kavling');
      expect(inputs.length).toBeGreaterThan(0);
      inputs.forEach((input) => {
        expect(input.value).toBe(String(id_kavling));
      });
    }
  );

  // ── Property holds regardless of roleid ────────────────────────────────────

  /**
   * **Validates: Requirements 4.10**
   *
   * The id_kavling propagation is the FIRST thing openEditModal does, before
   * the switch statement. Therefore the property must hold for every valid
   * roleid, not just one.
   */
  test.each(VALID_ROLEIDS)(
    'roleid=%i → all .id_kavling inputs receive id_kavling=42',
    (roleId) => {
      global.roleid = roleId;
      openEditModal(42);

      const inputs = document.querySelectorAll('.id_kavling');
      expect(inputs.length).toBeGreaterThan(0);
      inputs.forEach((input) => {
        expect(input.value).toBe('42');
      });
    }
  );

  // ── Multiple .id_kavling inputs are ALL updated ─────────────────────────────

  test('all .id_kavling inputs (across multiple modal containers) are updated', () => {
    global.roleid = 7;
    const id_kavling = 777;

    openEditModal(id_kavling);

    const inputs = document.querySelectorAll('.id_kavling');
    // DOM built by buildDOM() creates 8 modal inputs + 1 standalone = 9 total
    expect(inputs.length).toBe(9);
    inputs.forEach((input) => {
      expect(input.value).toBe(String(id_kavling));
    });
  });

  // ── Edge cases ──────────────────────────────────────────────────────────────

  test('large id_kavling (e.g. 999999) is propagated correctly', () => {
    global.roleid = 3;
    openEditModal(999999);

    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('999999');
    });
  });

  test('id_kavling=1 (minimum positive integer) is propagated correctly', () => {
    global.roleid = 6;
    openEditModal(1);

    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('1');
    });
  });

  test('calling openEditModal twice updates all inputs to the latest value', () => {
    global.roleid = 9;

    openEditModal(100);
    openEditModal(200);

    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('200');
    });
  });

  test('falsy id_kavling (0) is ignored — inputs retain previous value', () => {
    global.roleid = 4;

    // First call sets a valid value
    openEditModal(55);
    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('55');
    });

    // Second call with 0 (falsy) should be a no-op
    openEditModal(0);
    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('55');
    });
  });

  test('null id_kavling is ignored — inputs retain previous value', () => {
    global.roleid = 5;

    openEditModal(88);
    openEditModal(null);

    document.querySelectorAll('.id_kavling').forEach((input) => {
      expect(input.value).toBe('88');
    });
  });
});
