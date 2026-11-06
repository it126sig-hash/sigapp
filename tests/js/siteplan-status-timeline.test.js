/**
 * @jest-environment jsdom
 */
/**
 * Test Suite: Siteplan MKDT Status Resolution & Kavling Timeline Stepper
 * Tests resolveMkdtStatusInfo and renderKavlingTimeline from public/assets/js/siteplan-detail-modal.js
 *
 * Verifies:
 * - Kasus A: akad == 1 dan akad_indent == 1 (kavling 265 / MKDT 349) -> status 'Akad AJB', timeline 5 checkmarks (Opsi B).
 * - Kasus B: akad == 1 dan akad_indent == 0 -> status 'Akad AJB', timeline 5 checkmarks (Opsi B).
 * - Kasus C: akad == 0 dan akad_indent == 1 -> status 'Akad Indent', timeline step 4 aktif, step 5 nomor.
 * - Kasus D: akad == 0, akad_indent == 0, sp3k == 1 -> status 'SP3K', timeline step 3 aktif.
 * - Kasus E: wawancara == 1, sp3k == 0, akad == 0 -> status 'Wawancara', timeline step 2 aktif.
 * - Kasus F: Booking only -> status 'Booking', timeline step 1 aktif.
 * - Kasus G: is_batal == 1 -> status 'Batal', timeline batal aktif.
 */

const $ = require('jquery');

// Setup global DOM & jQuery environment before requiring the module
global.$ = $;
global.jQuery = $;
global.window = window;
global.document = document;
global.format_date = jest.fn((val) => val || '-');

const {
    isValidDate,
    format_date_clean,
    resolveMkdtStatusInfo,
    renderKavlingTimeline
} = require('../../public/assets/js/siteplan-detail-modal.js');

describe('Siteplan Status Resolution & Kavling Timeline Stepper', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="modal_detail">
                <span id="s-status_konsumen_badge" class="badge"></span>
                <span id="s-status_konsumen_tgl"></span>
                <div class="kavling-timeline-wrapper">
                    <div class="kavling-timeline" id="s-kavling-timeline"></div>
                </div>
            </div>
        `;
    });

    describe('1. resolveMkdtStatusInfo', () => {
        test('Kasus A: akad == 1 dan akad_indent == 1 (kavling 265 / MKDT 349) -> status "Akad AJB"', () => {
            const mkdt = {
                id_mkdt: 349,
                no_kavling: '265',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 1,
                akad_indent_tgl: '2023-02-20',
                akad: 1,
                akad_tgl: '2023-08-10',
                status_mkdt: 'Akad'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Akad AJB');
            expect(result.badgeCls).toBe('badge-success');
            expect(result.date).toBe('2023-08-10');
        });

        test('Kasus B: akad == 1 dan akad_indent == 0 -> status "Akad AJB"', () => {
            const mkdt = {
                id_mkdt: 120,
                no_kavling: '101',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 0,
                akad: 1,
                akad_tgl: '2023-03-01',
                status_mkdt: 'Akad'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Akad AJB');
            expect(result.badgeCls).toBe('badge-success');
            expect(result.date).toBe('2023-03-01');
        });

        test('Kasus C: akad == 0 dan akad_indent == 1 -> status "Akad Indent"', () => {
            const mkdt = {
                id_mkdt: 121,
                no_kavling: '102',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 1,
                akad_indent_tgl: '2023-02-20',
                akad: 0,
                status_mkdt: 'Akad Indent'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Akad Indent');
            expect(result.badgeCls).toBe('badge-primary');
            expect(result.date).toBe('2023-02-20');
        });

        test('Kasus D: akad == 0, akad_indent == 0, sp3k == 1 -> status "SP3K"', () => {
            const mkdt = {
                id_mkdt: 122,
                no_kavling: '103',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'SP3K'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('SP3K');
            expect(result.badgeCls).toBe('badge-info');
            expect(result.date).toBe('2023-02-01');
        });

        test('Kasus E: wawancara == 1, sp3k == 0, akad == 0 -> status "Wawancara"', () => {
            const mkdt = {
                id_mkdt: 123,
                no_kavling: '104',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 0,
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'WAWANCARA'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Wawancara');
            expect(result.badgeCls).toBe('badge-warning');
            expect(result.date).toBe('2023-01-15');
        });

        test('Kasus F: Booking only -> status "Booking"', () => {
            const mkdt = {
                id_mkdt: 124,
                no_kavling: '105',
                booking_tgl: '2023-01-10',
                wawancara: 0,
                sp3k: 0,
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'Booking'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Booking');
            expect(result.badgeCls).toBe('badge-primary');
            expect(result.date).toBe('2023-01-10');
        });

        test('Kasus G: is_batal == 1 -> status "Batal"', () => {
            const mkdt = {
                id_mkdt: 125,
                no_kavling: '106',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                is_batal: 1,
                mkdt_batal_tgl: '2023-01-20',
                status_mkdt: 'Batal'
            };

            const result = resolveMkdtStatusInfo(mkdt);
            expect(result.status).toBe('Batal');
            expect(result.badgeCls).toBe('badge-danger');
            expect(result.date).toBe('2023-01-20');
        });

        test('Null or undefined mkdt returns fallback status', () => {
            const result = resolveMkdtStatusInfo(null);
            expect(result.status).toBe('-');
            expect(result.badgeCls).toBe('badge-secondary');
            expect(result.date).toBeNull();
        });
    });

    describe('2. renderKavlingTimeline (Opsi B Implementation)', () => {
        test('Kasus A: akad == 1 dan akad_indent == 1 (MKDT 349) -> 5 checkmarks (Opsi B)', () => {
            const mkdt = {
                id_mkdt: 349,
                no_kavling: '265',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 1,
                akad_indent_tgl: '2023-02-20',
                akad: 1,
                akad_tgl: '2023-08-10',
                status_mkdt: 'Akad'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Sesuai Opsi B: semua 5 tahap (Booking, Wawancara, SP3K, Akad Indent, Akad AJB) berstatus is-completed
            expect($('#s-kavling-timeline .kavling-timeline-item.is-completed').length).toBe(5);
            expect($('#s-kavling-timeline .fa-check').length).toBe(5);
            expect($('#s-kavling-timeline .fa-dot-circle').length).toBe(0);

            // Verify titles
            expect(items.eq(0).find('.kavling-timeline-title').text()).toBe('Booking');
            expect(items.eq(1).find('.kavling-timeline-title').text()).toBe('Wawancara');
            expect(items.eq(2).find('.kavling-timeline-title').text()).toBe('SP3K');
            expect(items.eq(3).find('.kavling-timeline-title').text()).toBe('Akad Indent');
            expect(items.eq(4).find('.kavling-timeline-title').text()).toBe('Akad AJB');
        });

        test('Kasus B: akad == 1 dan akad_indent == 0 -> 5 checkmarks (Opsi B)', () => {
            const mkdt = {
                id_mkdt: 120,
                no_kavling: '101',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 0,
                akad: 1,
                akad_tgl: '2023-03-01',
                status_mkdt: 'Akad'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Sesuai Opsi B: semua 5 tahap berstatus is-completed
            expect($('#s-kavling-timeline .kavling-timeline-item.is-completed').length).toBe(5);
            expect($('#s-kavling-timeline .fa-check').length).toBe(5);
            expect($('#s-kavling-timeline .fa-dot-circle').length).toBe(0);
        });

        test('Kasus C: akad == 0 dan akad_indent == 1 -> step 4 aktif, step 5 nomor', () => {
            const mkdt = {
                id_mkdt: 121,
                no_kavling: '102',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 1,
                akad_indent_tgl: '2023-02-20',
                akad: 0,
                status_mkdt: 'Akad Indent'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Tahap 1, 2, 3: completed
            expect(items.eq(0).hasClass('is-completed')).toBe(true);
            expect(items.eq(0).find('.fa-check').length).toBe(1);
            expect(items.eq(1).hasClass('is-completed')).toBe(true);
            expect(items.eq(1).find('.fa-check').length).toBe(1);
            expect(items.eq(2).hasClass('is-completed')).toBe(true);
            expect(items.eq(2).find('.fa-check').length).toBe(1);

            // Tahap 4 (Akad Indent): aktif (dot-circle)
            expect(items.eq(3).hasClass('is-active')).toBe(true);
            expect(items.eq(3).find('.fa-dot-circle').length).toBe(1);

            // Tahap 5 (Akad AJB): nomor 5
            expect(items.eq(4).hasClass('is-completed')).toBe(false);
            expect(items.eq(4).hasClass('is-active')).toBe(false);
            expect(items.eq(4).find('.small').text()).toBe('5');
        });

        test('Kasus D: akad == 0, akad_indent == 0, sp3k == 1 -> step 3 aktif, step 4 & 5 nomor', () => {
            const mkdt = {
                id_mkdt: 122,
                no_kavling: '103',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 1,
                sp3k_tgl: '2023-02-01',
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'SP3K'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Tahap 1, 2: completed
            expect(items.eq(0).hasClass('is-completed')).toBe(true);
            expect(items.eq(1).hasClass('is-completed')).toBe(true);

            // Tahap 3 (SP3K): aktif
            expect(items.eq(2).hasClass('is-active')).toBe(true);
            expect(items.eq(2).find('.fa-dot-circle').length).toBe(1);

            // Tahap 4 & 5: nomor 4 & 5
            expect(items.eq(3).find('.small').text()).toBe('4');
            expect(items.eq(4).find('.small').text()).toBe('5');
        });

        test('Kasus E: wawancara == 1, sp3k == 0, akad == 0 -> step 2 aktif, step 3, 4, 5 nomor', () => {
            const mkdt = {
                id_mkdt: 123,
                no_kavling: '104',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                sp3k: 0,
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'WAWANCARA'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Tahap 1: completed
            expect(items.eq(0).hasClass('is-completed')).toBe(true);

            // Tahap 2 (Wawancara): aktif
            expect(items.eq(1).hasClass('is-active')).toBe(true);
            expect(items.eq(1).find('.fa-dot-circle').length).toBe(1);

            // Tahap 3, 4, 5: nomor 3, 4, 5
            expect(items.eq(2).find('.small').text()).toBe('3');
            expect(items.eq(3).find('.small').text()).toBe('4');
            expect(items.eq(4).find('.small').text()).toBe('5');
        });

        test('Kasus F: Booking only -> step 1 aktif, step 2..5 nomor', () => {
            const mkdt = {
                id_mkdt: 124,
                no_kavling: '105',
                booking_tgl: '2023-01-10',
                wawancara: 0,
                sp3k: 0,
                akad_indent: 0,
                akad: 0,
                status_mkdt: 'Booking'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(5);

            // Tahap 1 (Booking): aktif
            expect(items.eq(0).hasClass('is-active')).toBe(true);
            expect(items.eq(0).find('.fa-dot-circle').length).toBe(1);

            // Tahap 2, 3, 4, 5: nomor
            expect(items.eq(1).find('.small').text()).toBe('2');
            expect(items.eq(2).find('.small').text()).toBe('3');
            expect(items.eq(3).find('.small').text()).toBe('4');
            expect(items.eq(4).find('.small').text()).toBe('5');
        });

        test('Kasus G: is_batal == 1 -> timeline memiliki tahap batal aktif', () => {
            const mkdt = {
                id_mkdt: 125,
                no_kavling: '106',
                booking_tgl: '2023-01-10',
                wawancara: 1,
                wawancara_tgl: '2023-01-15',
                is_batal: 1,
                mkdt_batal_tgl: '2023-01-20',
                status_mkdt: 'Batal'
            };

            const statusInfo = resolveMkdtStatusInfo(mkdt);
            renderKavlingTimeline(mkdt, statusInfo.status);

            const items = $('#s-kavling-timeline .kavling-timeline-item');
            expect(items.length).toBe(6);

            const batalItem = items.filter('.is-batal');
            expect(batalItem.length).toBe(1);
            expect(batalItem.find('.fa-times').length).toBe(1);
            expect(batalItem.find('.kavling-timeline-title').text()).toBe('Batal');
        });

        test('Falsy mkdt displays empty placeholder', () => {
            renderKavlingTimeline(null, '-');
            expect($('#s-kavling-timeline').text()).toContain('Belum ada data aktivitas transaksi');
        });
    });
});
