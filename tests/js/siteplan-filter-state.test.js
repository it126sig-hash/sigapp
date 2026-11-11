const SiteplanFilterState = require('../../public/assets/js/siteplan/filter-state.js');

describe('Siteplan multi-cluster filter state', () => {
    beforeEach(() => {
        window.localStorage.clear();
    });

    test('normalizes scalar and array IDs to unique positive strings', () => {
        expect(SiteplanFilterState.normalizeIds('4')).toEqual(['4']);
        expect(SiteplanFilterState.normalizeIds(['2', 2, '0', '-1', 'abc', 7])).toEqual(['2', '7']);
    });

    test('normalizes selection to all or list of IDs', () => {
        expect(SiteplanFilterState.normalizeSelection('all')).toEqual(['all']);
        expect(SiteplanFilterState.normalizeSelection(['all', '1', '2'])).toEqual(['all']);
        expect(SiteplanFilterState.normalizeSelection(['2', '3'])).toEqual(['2', '3']);
    });

    test('defaults to all when no setting has been saved for project/user', () => {
        expect(SiteplanFilterState.hasSavedSetting(window.localStorage, 10, 21)).toBe(false);
        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3', '8', '9'])).toEqual(['all']);
    });

    test('stores and restores specific selection per user and project', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, ['3', '8']);

        expect(SiteplanFilterState.hasSavedSetting(window.localStorage, 10, 21)).toBe(true);
        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3', '8', '9'])).toEqual(['3', '8']);
        // Other projects/users default to all:
        expect(SiteplanFilterState.restore(window.localStorage, 10, 22, ['3', '8', '9'])).toEqual(['all']);
        expect(SiteplanFilterState.restore(window.localStorage, 11, 21, ['3', '8', '9'])).toEqual(['all']);
    });

    test('stores and restores all cluster selection', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, ['all']);

        expect(SiteplanFilterState.hasSavedSetting(window.localStorage, 10, 21)).toBe(true);
        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3', '8', '9'])).toEqual(['all']);
    });

    test('drops saved clusters that are no longer available', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, ['3', '8']);

        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['8', '9'])).toEqual(['8']);
    });

    test('allows restoring an explicitly saved empty selection', () => {
        SiteplanFilterState.save(window.localStorage, 10, 21, []);

        expect(SiteplanFilterState.hasSavedSetting(window.localStorage, 10, 21)).toBe(true);
        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3', '8'])).toEqual([]);
    });

    test('corrupt or unavailable storage fails safely to all', () => {
        window.localStorage.setItem(SiteplanFilterState.storageKey(10, 21), '{broken');
        const unavailableStorage = {
            getItem() {
                throw new Error('blocked');
            }
        };

        expect(SiteplanFilterState.restore(window.localStorage, 10, 21, ['3'])).toEqual(['all']);
        expect(SiteplanFilterState.restore(unavailableStorage, 10, 21, ['3'])).toEqual(['all']);
        expect(SiteplanFilterState.save(null, 10, 21, ['3'])).toBe(false);
    });
});
