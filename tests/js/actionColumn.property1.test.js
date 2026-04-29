/**
 * Property 1: Action column render selalu menghasilkan tombol yang benar
 *
 * Validates: Requirements 3.2, 3.5, 3.6
 *
 * For any positive integer id_kavling and a roleid that has a modal,
 * verify the HTML output contains:
 *   - onclick="openEditModal(<id_kavling>)"
 *   - onclick="openDetailModal(<id_kavling>)"
 *
 * For roleid values that do NOT have a modal, verify the Edit button
 * is NOT in the output.
 */

// ─── Function under test (extracted from list-kavling.php columnDefs render) ─

/**
 * Renders the action column HTML for a given row and roleid.
 * Mirrors the render function in DataTable columnDefs (Design Step 6).
 *
 * @param {*} data - cell data (unused, DataTables passes it)
 * @param {string} type - render type (unused)
 * @param {object|Array} row - row data from server
 * @param {number} roleid - global roleid variable
 * @returns {string} HTML string
 */
function renderActionColumn(data, type, row, roleid) {
    var id_kavling = row.id_kavling || row[0];
    var btnEdit = '';
    var rolesWithModal = [1, 3, 4, 5, 6, 7, 8, 9, 10];
    if (rolesWithModal.indexOf(roleid) !== -1) {
        btnEdit = '<button class="btn btn-primary btn-action btn-sm mr-1" ' +
                  'onclick="openEditModal(' + id_kavling + ')" ' +
                  'title="Edit Data">' +
                  '<i class="fa fa-edit"></i> Edit' +
                  '</button>';
    }
    var btnDetail = '<button class="btn btn-info btn-action btn-sm" ' +
                    'onclick="openDetailModal(' + id_kavling + ')" ' +
                    'title="Lihat Detail">' +
                    '<i class="fa fa-eye"></i> Lihat' +
                    '</button>';
    return '<div style="white-space:nowrap">' + btnEdit + btnDetail + '</div>';
}

// ─── Constants ────────────────────────────────────────────────────────────────

const ROLES_WITH_MODAL = [1, 3, 4, 5, 6, 7, 8, 9, 10];
const ROLES_WITHOUT_MODAL = [2, 11, 99];

// Property-based style: representative id_kavling values
const ID_KAVLING_SAMPLES = [1, 5, 42, 100, 999, 9999];

// ─── Tests ────────────────────────────────────────────────────────────────────

describe('Property 1: Action column render selalu menghasilkan tombol yang benar', () => {

    /**
     * **Validates: Requirements 3.2, 3.5**
     *
     * For every roleid in ROLES_WITH_MODAL and every sample id_kavling,
     * the rendered HTML SHALL contain onclick="openEditModal(<id_kavling>)".
     */
    describe('Edit button muncul untuk roleid yang memiliki modal', () => {
        test.each(ROLES_WITH_MODAL)(
            'roleid=%i → Edit button ada di output',
            (roleid) => {
                ID_KAVLING_SAMPLES.forEach((id_kavling) => {
                    const row = { id_kavling };
                    const html = renderActionColumn(null, 'display', row, roleid);
                    expect(html).toContain(`onclick="openEditModal(${id_kavling})"`);
                });
            }
        );
    });

    /**
     * **Validates: Requirements 3.6**
     *
     * For every roleid (with or without modal) and every sample id_kavling,
     * the rendered HTML SHALL always contain onclick="openDetailModal(<id_kavling>)".
     */
    describe('Detail button selalu muncul untuk semua roleid', () => {
        const ALL_ROLES = [...ROLES_WITH_MODAL, ...ROLES_WITHOUT_MODAL];

        test.each(ALL_ROLES)(
            'roleid=%i → Detail button selalu ada di output',
            (roleid) => {
                ID_KAVLING_SAMPLES.forEach((id_kavling) => {
                    const row = { id_kavling };
                    const html = renderActionColumn(null, 'display', row, roleid);
                    expect(html).toContain(`onclick="openDetailModal(${id_kavling})"`);
                });
            }
        );
    });

    /**
     * **Validates: Requirements 3.5**
     *
     * For roleid values NOT in ROLES_WITH_MODAL, the Edit button
     * SHALL NOT appear in the rendered HTML.
     */
    describe('Edit button tidak muncul untuk roleid tanpa modal', () => {
        test.each(ROLES_WITHOUT_MODAL)(
            'roleid=%i → Edit button tidak ada di output',
            (roleid) => {
                ID_KAVLING_SAMPLES.forEach((id_kavling) => {
                    const row = { id_kavling };
                    const html = renderActionColumn(null, 'display', row, roleid);
                    expect(html).not.toContain('openEditModal');
                });
            }
        );
    });

    /**
     * **Validates: Requirements 3.4**
     *
     * The wrapper div SHALL always have style="white-space:nowrap".
     */
    test('Wrapper div selalu memiliki white-space:nowrap', () => {
        const ALL_ROLES = [...ROLES_WITH_MODAL, ...ROLES_WITHOUT_MODAL];
        ALL_ROLES.forEach((roleid) => {
            ID_KAVLING_SAMPLES.forEach((id_kavling) => {
                const row = { id_kavling };
                const html = renderActionColumn(null, 'display', row, roleid);
                expect(html).toContain('style="white-space:nowrap"');
            });
        });
    });

    /**
     * id_kavling dari array numerik (row[0]) juga harus berfungsi.
     */
    test('id_kavling dari array numerik (row[0]) digunakan jika row.id_kavling tidak ada', () => {
        const roleid = 4;
        ID_KAVLING_SAMPLES.forEach((id_kavling) => {
            const row = [id_kavling]; // array numerik, tidak ada property id_kavling
            const html = renderActionColumn(null, 'display', row, roleid);
            expect(html).toContain(`onclick="openEditModal(${id_kavling})"`);
            expect(html).toContain(`onclick="openDetailModal(${id_kavling})"`);
        });
    });
});
