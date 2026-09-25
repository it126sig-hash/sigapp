const SiteplanShapeFilter = require('../../public/assets/js/siteplan/shape-filter.js');

describe('Siteplan shape visibility filter', () => {
    const rows = [{
        key: 'mkdt',
        label: 'MKDT',
        segments: [{ config_name: 'SP3K Subsidi', ratio: 1 }],
        markers: [{ config_name: 'Akad Indent', position: 0.5 }]
    }, {
        key: 'produksi',
        label: 'Produksi',
        segments: [{ config_name: 'Pembangunan', ratio: 1 }],
        markers: [{ config_name: 'Marker Baru', position: 0.5 }]
    }];

    test('pilihan kosong menampilkan semua shape', () => {
        expect(SiteplanShapeFilter.matches([], new Set())).toBe(true);
        expect(SiteplanShapeFilter.matches(['anything'], [])).toBe(true);
    });

    test('mengambil key segment dan semua marker tanpa daftar nama khusus', () => {
        expect(SiteplanShapeFilter.keysFromRows(rows)).toEqual([
            SiteplanShapeFilter.key('MKDT', 'SP3K Subsidi'),
            SiteplanShapeFilter.key('MKDT', 'Akad Indent'),
            SiteplanShapeFilter.key('Produksi', 'Pembangunan'),
            SiteplanShapeFilter.key('Produksi', 'Marker Baru')
        ]);
    });

    test('shape SP3K dengan marker Akad Indent cocok melalui segment maupun marker', () => {
        const nodeKeys = SiteplanShapeFilter.keysFromRows(rows);

        expect(SiteplanShapeFilter.matches(nodeKeys, [
            SiteplanShapeFilter.key('MKDT', 'SP3K Subsidi')
        ])).toBe(true);
        expect(SiteplanShapeFilter.matches(nodeKeys, [
            SiteplanShapeFilter.key('MKDT', 'Akad Indent')
        ])).toBe(true);
    });

    test('beberapa pilihan memakai logika OR', () => {
        const nodeKeys = SiteplanShapeFilter.keysFromRows(rows);

        expect(SiteplanShapeFilter.matches(nodeKeys, [
            SiteplanShapeFilter.key('Keuangan', 'Jatuh Tempo'),
            SiteplanShapeFilter.key('MKDT', 'Akad Indent')
        ])).toBe(true);
        expect(SiteplanShapeFilter.matches(nodeKeys, [
            SiteplanShapeFilter.key('Keuangan', 'Jatuh Tempo'),
            SiteplanShapeFilter.key('MKDT', 'Akad')
        ])).toBe(false);
    });

    test('status bernama sama pada kelompok berbeda tidak tercampur', () => {
        const nodeKeys = [SiteplanShapeFilter.key('MKDT', 'Def')];

        expect(SiteplanShapeFilter.matches(nodeKeys, [
            SiteplanShapeFilter.key('Produksi', 'Def')
        ])).toBe(false);
    });

    test('baris batal tanpa marker tidak menghasilkan key Akad Indent', () => {
        const cancelledKeys = SiteplanShapeFilter.keysFromRows([{
            key: 'mkdt',
            label: 'MKDT',
            segments: [{ config_name: 'Batal', ratio: 1 }],
            markers: []
        }]);

        expect(cancelledKeys).not.toContain(SiteplanShapeFilter.key('MKDT', 'Akad Indent'));
    });

    test('shape non-kavling selalu terlihat ketika filter aktif', () => {
        expect(SiteplanShapeFilter.shouldShow(false, [], [
            SiteplanShapeFilter.key('MKDT', 'Akad Indent')
        ])).toBe(true);
    });
});
