(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanFilterState = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';

    const STORAGE_VERSION = 'v1';

    function normalizeIds(value) {
        const values = Array.isArray(value) ? value : (value == null || value === '' ? [] : [value]);
        const ids = [];
        const seen = new Set();

        values.forEach(function(valueItem) {
            const text = String(valueItem).trim();
            if (!/^\d+$/.test(text)) return;

            const id = String(parseInt(text, 10));
            if (id === '0' || seen.has(id)) return;

            seen.add(id);
            ids.push(id);
        });

        return ids;
    }

    function storageKey(userId, projectId) {
        return ['sigapp', 'siteplan', 'clusters', STORAGE_VERSION, userId, projectId].join(':');
    }

    function restore(storage, userId, projectId, availableIds) {
        if (!storage) return [];

        try {
            const available = new Set(normalizeIds(availableIds));
            const saved = normalizeIds(JSON.parse(storage.getItem(storageKey(userId, projectId)) || '[]'));
            return saved.filter(function(id) {
                return available.has(id);
            });
        } catch (error) {
            return [];
        }
    }

    function save(storage, userId, projectId, ids) {
        if (!storage) return false;

        try {
            storage.setItem(storageKey(userId, projectId), JSON.stringify(normalizeIds(ids)));
            return true;
        } catch (error) {
            return false;
        }
    }

    return {
        normalizeIds: normalizeIds,
        restore: restore,
        save: save,
        storageKey: storageKey
    };
});
