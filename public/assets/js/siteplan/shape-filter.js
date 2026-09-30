(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanShapeFilter = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';

    function key(groupName, statusName) {
        return JSON.stringify([
            String(groupName || 'Status'),
            String(statusName || 'Def')
        ]);
    }

    function keysFromRows(rows) {
        const keys = [];
        const seen = new Set();

        (Array.isArray(rows) ? rows : []).forEach(function(row) {
            const groupName = row.label || row.key || 'Status';
            const items = (Array.isArray(row.segments) ? row.segments : [])
                .concat(Array.isArray(row.markers) ? row.markers : []);

            items.forEach(function(item) {
                const itemKey = key(groupName, item && item.config_name);
                if (seen.has(itemKey)) return;

                seen.add(itemKey);
                keys.push(itemKey);
            });
        });

        return keys;
    }

    function normalizeSelection(selectedKeys) {
        if (selectedKeys instanceof Set) return selectedKeys;
        return new Set(Array.isArray(selectedKeys) ? selectedKeys : []);
    }

    function matches(nodeKeys, selectedKeys) {
        const selected = normalizeSelection(selectedKeys);
        if (selected.size === 0) return true;

        return (Array.isArray(nodeKeys) ? nodeKeys : []).some(function(nodeKey) {
            return selected.has(nodeKey);
        });
    }

    function shouldShow(filterable, nodeKeys, selectedKeys) {
        return !filterable || matches(nodeKeys, selectedKeys);
    }

    return {
        key: key,
        keysFromRows: keysFromRows,
        matches: matches,
        shouldShow: shouldShow
    };
});
