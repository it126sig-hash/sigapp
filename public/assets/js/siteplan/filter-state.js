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

    function normalizeSelection(value) {
        if (value === 'all') return ['all'];
        if (Array.isArray(value) && value.includes('all')) return ['all'];
        return normalizeIds(value);
    }

    function storageKey(userId, projectId) {
        return ['sigapp', 'siteplan', 'clusters', STORAGE_VERSION, userId, projectId].join(':');
    }

    function hasSavedSetting(storage, userId, projectId) {
        if (!storage) return false;

        try {
            return storage.getItem(storageKey(userId, projectId)) !== null;
        } catch (error) {
            return false;
        }
    }

    function restore(storage, userId, projectId, availableIds) {
        if (!storage) return ['all'];

        try {
            const item = storage.getItem(storageKey(userId, projectId));
            if (item === null) {
                return ['all'];
            }

            const parsed = JSON.parse(item);
            if (parsed === 'all' || (Array.isArray(parsed) && parsed.includes('all'))) {
                return ['all'];
            }

            const available = new Set(normalizeIds(availableIds));
            const saved = normalizeIds(parsed);
            return saved.filter(function(id) {
                return available.has(id);
            });
        } catch (error) {
            return ['all'];
        }
    }

    function save(storage, userId, projectId, ids) {
        if (!storage) return false;

        try {
            storage.setItem(storageKey(userId, projectId), JSON.stringify(normalizeSelection(ids)));
            return true;
        } catch (error) {
            return false;
        }
    }

    return {
        normalizeIds: normalizeIds,
        normalizeSelection: normalizeSelection,
        hasSavedSetting: hasSavedSetting,
        restore: restore,
        save: save,
        storageKey: storageKey
    };
});
