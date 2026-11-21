(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanTargetOverlay = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';

    const DEFAULT_STROKE_COLOR = '#000000';
    const DEFAULT_BORDER_WIDTH = 2.5;
    const DEFAULT_GAP_BRIDGE = 2;
    const MIN_GAP_BRIDGE = 0;
    const MAX_GAP_BRIDGE = 12;

    /**
     * Parse input value into a finite number, or null when invalid/non-numeric.
     *
     * @param {*} value
     * @returns {number|null}
     */
    function toFiniteNumber(value) {
        if (value === null || value === undefined || typeof value === 'boolean') {
            return null;
        }
        if (typeof value === 'string' && value.trim() === '') {
            return null;
        }
        if (typeof value !== 'number' && typeof value !== 'string') {
            return null;
        }
        const num = Number(value);
        if (!Number.isFinite(num)) {
            return null;
        }
        return num === 0 ? 0 : num;
    }

    /**
     * Flatten a 2D coordinate pair array [[x, y], ...] into a 1D array.
     *
     * @param {Array} arr
     * @returns {Array}
     */
    function flattenPointsArray(arr) {
        if (arr.length > 0 && Array.isArray(arr[0])) {
            return arr.flat ? arr.flat() : Array.prototype.concat.apply([], arr);
        }
        return arr;
    }

    /**
     * Normalize gap-bridge radius in pixels: fallback to DEFAULT_GAP_BRIDGE if
     * non-finite, clamp to [MIN_GAP_BRIDGE, MAX_GAP_BRIDGE], and round to 1 decimal.
     *
     * @param {*} value
     * @returns {number}
     */
    function normalizeGapBridge(value) {
        const num = toFiniteNumber(value);
        if (num === null) {
            return DEFAULT_GAP_BRIDGE;
        }
        const clamped = Math.max(MIN_GAP_BRIDGE, Math.min(MAX_GAP_BRIDGE, num));
        const rounded = Math.round(clamped * 10) / 10;
        return rounded === 0 ? 0 : rounded;
    }

    /**
     * Normalize flat array, nested [[x, y], ...] array, or CSV/JSON string into
     * an even-length flat array of finite numbers [x1, y1, ...].
     *
     * @param {Array|string|null|undefined} points
     * @returns {number[]}
     */
    function parsePoints(points) {
        let raw = null;

        if (Array.isArray(points)) {
            raw = flattenPointsArray(points);
        } else if (typeof points === 'string') {
            const trimmed = points.trim();
            if (!trimmed) {
                return [];
            }
            try {
                const jsonStr = trimmed.startsWith('[') ? trimmed : '[' + trimmed + ']';
                const parsed = JSON.parse(jsonStr);
                if (Array.isArray(parsed)) {
                    raw = flattenPointsArray(parsed);
                }
            } catch (_) {
                raw = trimmed.replace(/[\[\]]/g, '').split(',');
            }
        }

        if (!Array.isArray(raw)) {
            return [];
        }

        const normalized = [];
        for (let i = 0; i < raw.length; i++) {
            const num = toFiniteNumber(raw[i]);
            if (num !== null) {
                normalized.push(num);
            }
        }

        if (normalized.length % 2 !== 0) {
            normalized.pop();
        }

        return normalized;
    }

    /**
     * Compute combined bounding box { x, y, width, height } across all valid polygons
     * (flat length >= 6), expanded on all sides by non-negative padding.
     *
     * @param {Array} polygons
     * @param {number} [padding=0]
     * @returns {{ x: number, y: number, width: number, height: number }}
     */
    function computeBounds(polygons, padding) {
        const parsedPad = toFiniteNumber(padding);
        const pad = parsedPad !== null && parsedPad > 0 ? parsedPad : 0;

        if (!Array.isArray(polygons) || polygons.length === 0) {
            return { x: 0, y: 0, width: 0, height: 0 };
        }

        let minX = Infinity;
        let minY = Infinity;
        let maxX = -Infinity;
        let maxY = -Infinity;
        let hasValid = false;

        for (let i = 0; i < polygons.length; i++) {
            const pts = parsePoints(polygons[i]);
            if (pts.length < 6) {
                continue;
            }
            hasValid = true;
            for (let j = 0; j < pts.length; j += 2) {
                const x = pts[j];
                const y = pts[j + 1];
                if (x < minX) minX = x;
                if (x > maxX) maxX = x;
                if (y < minY) minY = y;
                if (y > maxY) maxY = y;
            }
        }

        if (!hasValid) {
            return { x: 0, y: 0, width: 0, height: 0 };
        }

        return {
            x: minX - pad,
            y: minY - pad,
            width: (maxX - minX) + (pad * 2),
            height: (maxY - minY) + (pad * 2)
        };
    }

    /**
     * Trace a closed polygon path onto a 2D canvas context from flat coordinates.
     *
     * @param {CanvasRenderingContext2D} nativeContext
     * @param {number[]} flatPoints
     */
    function tracePolygonPath(nativeContext, flatPoints) {
        if (!nativeContext || !Array.isArray(flatPoints) || flatPoints.length < 6) {
            return;
        }
        nativeContext.beginPath();
        nativeContext.moveTo(flatPoints[0], flatPoints[1]);
        for (let i = 2; i + 1 < flatPoints.length; i += 2) {
            nativeContext.lineTo(flatPoints[i], flatPoints[i + 1]);
        }
        nativeContext.closePath();
    }

    /**
     * Create a composite Konva.Shape that renders only the outer perimeter silhouette
     * of unioned target polygons using a 2-pass morphological dilation/erosion technique.
     *
     * @param {Object} Konva
     * @param {Array} polygons
     * @param {Object} [options]
     * @returns {Object|null}
     */
    function createOverlayShape(Konva, polygons, options) {
        if (!Konva || typeof Konva.Shape !== 'function') {
            return null;
        }

        options = options || {};
        const strokeColor = (typeof options.strokeColor === 'string' && options.strokeColor.trim() !== '')
            ? options.strokeColor.trim()
            : DEFAULT_STROKE_COLOR;
        const parsedBorderWidth = toFiniteNumber(options.borderWidth);
        const borderWidth = (parsedBorderWidth !== null && parsedBorderWidth > 0)
            ? parsedBorderWidth
            : DEFAULT_BORDER_WIDTH;
        const gapBridge = normalizeGapBridge(options.gapBridge);
        const outerLineWidth = 2 * (borderWidth + gapBridge);
        const innerEraseLineWidth = 2 * gapBridge;

        const rawPolygons = Array.isArray(polygons) ? polygons : [];
        const validPolygons = [];
        for (let i = 0; i < rawPolygons.length; i++) {
            const pts = parsePoints(rawPolygons[i]);
            if (pts.length >= 6) {
                validPolygons.push(pts);
            }
        }

        const shape = new Konva.Shape({
            id: 'siteplan-target-border-overlay',
            name: 'siteplan-target-border-shape',
            listening: false,
            perfectDrawEnabled: false,
            sceneFunc: function(context) {
                const nativeContext = context && context._context ? context._context : context;
                if (!nativeContext || validPolygons.length === 0) {
                    return;
                }

                nativeContext.save();

                // Pass 1 (source-over): dilate combined silhouette by borderWidth + gapBridge
                nativeContext.globalCompositeOperation = 'source-over';
                nativeContext.fillStyle = strokeColor;
                nativeContext.strokeStyle = strokeColor;
                nativeContext.lineWidth = outerLineWidth;
                nativeContext.lineJoin = 'round';
                nativeContext.lineCap = 'round';

                for (let i = 0; i < validPolygons.length; i++) {
                    tracePolygonPath(nativeContext, validPolygons[i]);
                    nativeContext.fill();
                    nativeContext.stroke();
                }

                // Pass 2 (destination-out): erase interior and gap bridges by gapBridge
                nativeContext.globalCompositeOperation = 'destination-out';
                nativeContext.fillStyle = '#000000';
                nativeContext.strokeStyle = '#000000';
                nativeContext.lineJoin = 'round';
                nativeContext.lineCap = 'round';
                if (innerEraseLineWidth > 0) {
                    nativeContext.lineWidth = innerEraseLineWidth;
                }

                for (let i = 0; i < validPolygons.length; i++) {
                    tracePolygonPath(nativeContext, validPolygons[i]);
                    nativeContext.fill();
                    if (innerEraseLineWidth > 0) {
                        nativeContext.stroke();
                    }
                }

                nativeContext.restore();
            }
        });

        shape.getSelfRect = function() {
            return computeBounds(validPolygons, borderWidth + gapBridge);
        };

        return shape;
    }

    /**
     * Destroy all child shapes on the target overlay layer and trigger redraw.
     *
     * @param {Object|null|undefined} layer
     */
    function clearOverlay(layer) {
        if (layer && typeof layer.destroyChildren === 'function') {
            layer.destroyChildren();
        }
        if (layer && typeof layer.batchDraw === 'function') {
            layer.batchDraw();
        }
    }

    /**
     * Build and mount the outer-perimeter target overlay shape onto the given layer
     * for all items currently marked visible by isBaseVisibleCallback.
     *
     * @param {Object} Konva
     * @param {Object} layer
     * @param {Array} items
     * @param {Function} [isBaseVisibleCallback]
     * @param {Object} [options]
     * @returns {Object|null}
     */
    function buildOverlayShapes(Konva, layer, items, isBaseVisibleCallback, options) {
        if (!Konva || !layer) {
            return null;
        }

        clearOverlay(layer);

        const list = Array.isArray(items) ? items : [];
        const validPolygons = [];

        for (let i = 0; i < list.length; i++) {
            const item = list[i];
            if (!item) {
                continue;
            }
            const idKavling = item.id_kavling !== undefined ? item.id_kavling : item.idKavling;
            if (typeof isBaseVisibleCallback === 'function' && idKavling !== undefined && idKavling !== null) {
                if (!isBaseVisibleCallback('kav' + idKavling)) {
                    continue;
                }
            }
            const pts = parsePoints(item.points);
            if (pts.length >= 6) {
                validPolygons.push(pts);
            }
        }

        if (validPolygons.length === 0) {
            return null;
        }

        const shape = createOverlayShape(Konva, validPolygons, options);
        if (!shape) {
            return null;
        }

        if (typeof layer.add === 'function') {
            layer.add(shape);
        }
        if (typeof layer.batchDraw === 'function') {
            layer.batchDraw();
        }

        return shape;
    }

    return {
        DEFAULT_STROKE_COLOR: DEFAULT_STROKE_COLOR,
        DEFAULT_BORDER_WIDTH: DEFAULT_BORDER_WIDTH,
        DEFAULT_GAP_BRIDGE: DEFAULT_GAP_BRIDGE,
        MIN_GAP_BRIDGE: MIN_GAP_BRIDGE,
        MAX_GAP_BRIDGE: MAX_GAP_BRIDGE,
        normalizeGapBridge: normalizeGapBridge,
        parsePoints: parsePoints,
        computeBounds: computeBounds,
        tracePolygonPath: tracePolygonPath,
        createOverlayShape: createOverlayShape,
        clearOverlay: clearOverlay,
        buildOverlayShapes: buildOverlayShapes
    };
});
