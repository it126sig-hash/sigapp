const SiteplanFilterState = require('../../public/assets/js/siteplan/filter-state.js');

describe('Siteplan multi-cluster filter state', () => {
    beforeEach(() => {
        window.localStorage.clear();
    });

    test('normalizes scalar and array IDs to unique positive strings', () => {
        expect(SiteplanFilterState.normalizeIds('4')).toEqual(['4']);
        expect(SiteplanFilterState.normalizeIds(['2', 2, '0', '-1', 'abc', 7])).toEqual(['2', '7']);
    });

    test('stores selection per user and project', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, ['3', '8']);

        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3', '8', '9'])).toEqual(['3', '8']);
        expect(SiteplanFilterState.restore(window.localStorage, 10, 22, ['3', '8', '9'])).toEqual([]);
        expect(SiteplanFilterState.restore(window.localStorage, 11, 21, ['3', '8', '9'])).toEqual([]);
    });

    test('drops saved clusters that are no longer available', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, ['3', '8']);

        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['8', '9'])).toEqual(['8']);
    });

    test('corrupt or unavailable storage fails safely', () => {
        window.localStorage.setItem(SiteplanFilterState.storageKey(10, 21), '{broken');
        const unavailableStorage = {
            getItem() {
                throw new Error('blocked');
            }
        };

        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3'])).toEqual([]);
        expect(SiteplanFilterState.restore(unavailableStorage, 10, 21, ['3'])).toEqual([]);
        expect(SiteplanFilterState.save(null, 10, 21, ['3'])).toBe(false);
    });
});
