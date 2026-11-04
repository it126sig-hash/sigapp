(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanTiketOverlay = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';

    const TARGET_FPS = 30;
    const FRAME_INTERVAL_MS = 1000 / TARGET_FPS;
    const DEFAULT_CYCLE_DURATION_MS = 2800;

    const PRIORITY_COLORS = {
        urgent: '#ea5455',
        medium: '#ff9f43',
        normal: '#00cfe8',
        low: '#28c76f',
        fallback: '#ea5455'
    };

    const PRIORITY_CONF_KEYS = {
        urgent: 'Masalah Urgent',
        medium: 'Masalah Medium',
        normal: 'Masalah Normal',
        low: 'Masalah Low'
    };

    function normalizePriority(prioritas) {
        const raw = String(prioritas || '').toLowerCase().trim();
        if (raw === 'urgent' || raw === 'masalah urgent') return 'urgent';
        if (raw === 'medium' || raw === 'masalah medium') return 'medium';
        if (raw === 'normal' || raw === 'masalah normal') return 'normal';
        if (raw === 'low' || raw === 'masalah low') return 'low';
        return 'urgent';
    }

    function getPriorityColor(prioritas, conf) {
        const key = normalizePriority(prioritas);
        const confKey = PRIORITY_CONF_KEYS[key];
        if (conf && conf[confKey] && conf[confKey].fill) {
            return conf[confKey].fill;
        }
        return PRIORITY_COLORS[key] || PRIORITY_COLORS.fallback;
    }

    function calculateOpacity(timeMs, phaseOffset, cycleDurationMs) {
        const t = typeof timeMs === 'number' ? timeMs : 0;
        const offset = typeof phaseOffset === 'number' ? phaseOffset : 0;
        const duration = (typeof cycleDurationMs === 'number' && cycleDurationMs > 0)
            ? cycleDurationMs
            : DEFAULT_CYCLE_DURATION_MS;

        const raw = 0.5 + 0.5 * Math.sin(((t * 2 * Math.PI) / duration) - offset);
        return Math.max(0, Math.min(1, raw));
    }

    function parsePoints(points) {
        if (Array.isArray(points)) {
            if (points.length > 0 && Array.isArray(points[0])) {
                return points.flat ? points.flat() : Array.prototype.concat.apply([], points);
            }
            return points.map(function(val) {
                return Number(val) || 0;
            });
        }
        if (typeof points === 'string') {
            const trimmed = points.trim();
            if (!trimmed) return [];
            try {
                const jsonStr = trimmed.startsWith('[') ? trimmed : '[' + trimmed + ']';
                const parsed = JSON.parse(jsonStr);
                if (Array.isArray(parsed)) {
                    if (parsed.length > 0 && Array.isArray(parsed[0])) {
                        return parsed.flat ? parsed.flat() : Array.prototype.concat.apply([], parsed);
                    }
                    return parsed.map(function(val) {
                        return Number(val) || 0;
                    });
                }
            } catch (_) {
                return trimmed.replace(/[\[\]]/g, '').split(',').map(function(val) {
                    return Number(val) || 0;
                });
            }
        }
        return [];
    }

    function calculateCentroid(points) {
        const parsed = parsePoints(points);
        if (!parsed || parsed.length < 2) {
            return { x: 0, y: 0 };
        }
        let sumX = 0;
        let sumY = 0;
        let count = 0;
        for (let i = 0; i < parsed.length - 1; i += 2) {
            const x = Number(parsed[i]);
            const y = Number(parsed[i + 1]);
            if (!isNaN(x) && !isNaN(y)) {
                sumX += x;
                sumY += y;
                count++;
            }
        }
        if (count === 0) {
            return { x: 0, y: 0 };
        }
        return {
            x: sumX / count,
            y: sumY / count
        };
    }

    function createOverlayShape(Konva, options) {
        options = options || {};
        const refType = options.refType || (options.tipe && options.tipe !== 'kavling' ? 'others' : (options.id_kavling !== undefined ? 'kavling' : (options.id !== undefined ? 'others' : 'kavling')));
        const refId = options.refId !== undefined ? options.refId : (refType === 'others' ? (options.id !== undefined ? options.id : options.idKavling) : (options.idKavling !== undefined ? options.idKavling : options.id_kavling));
        const targetPrefix = (refType === 'others' ? 'others' : 'kav');
        const targetId = targetPrefix + refId;

        const prioritas = options.prioritas !== undefined ? options.prioritas : options.active_tiket_prioritas;
        const priorityColor = options.color || getPriorityColor(prioritas, options.conf);
        const phaseOffset = typeof options.phaseOffset === 'number' ? options.phaseOffset : 0;
        const points = parsePoints(options.points);

        const shape = new Konva.Line({
            id: 'tiket-overlay-' + targetPrefix + refId,
            name: 'siteplan-tiket-overlay-shape',
            points: points,
            closed: true,
            fill: priorityColor,
            stroke: '#ffffff',
            strokeWidth: 1.5,
            opacity: 0,
            listening: false,
            perfectDrawEnabled: false,
            refType: refType,
            refId: refId,
            targetId: targetId,
            idKavling: refId,
            prioritas: prioritas,
            phaseOffset: phaseOffset
        });

        shape.refType = refType;
        shape.refId = refId;
        shape.targetId = targetId;
        shape.idKavling = refId;
        shape.prioritas = prioritas;
        shape.phaseOffset = phaseOffset;

        return shape;
    }

    function createBadgeShape(Konva, options) {
        options = options || {};
        const refType = options.refType || (options.tipe && options.tipe !== 'kavling' ? 'others' : (options.id_kavling !== undefined ? 'kavling' : (options.id !== undefined ? 'others' : 'kavling')));
        const refId = options.refId !== undefined ? options.refId : (refType === 'others' ? (options.id !== undefined ? options.id : options.idKavling) : (options.idKavling !== undefined ? options.idKavling : options.id_kavling));
        const targetPrefix = (refType === 'others' ? 'others' : 'kav');
        const targetId = targetPrefix + refId;

        const prioritas = options.prioritas !== undefined ? options.prioritas : options.active_tiket_prioritas;
        const priorityColor = options.color || getPriorityColor(prioritas, options.conf);
        const phaseOffset = typeof options.phaseOffset === 'number' ? options.phaseOffset : 0;
        const centroid = options.centroid || calculateCentroid(options.points);

        const shape = new Konva.Shape({
            id: 'tiket-badge-' + targetPrefix + refId,
            name: 'siteplan-tiket-badge-shape',
            x: centroid.x,
            y: centroid.y,
            listening: false,
            perfectDrawEnabled: false,
            refType: refType,
            refId: refId,
            targetId: targetId,
            idKavling: refId,
            prioritas: prioritas,
            phaseOffset: phaseOffset,
            sceneFunc: function(context, shape) {
                const radius = 8;
                context.beginPath();
                context.arc(0, 0, radius, 0, Math.PI * 2, false);
                context.closePath();
                context.fillStyle = priorityColor;
                context.fill();
                context.lineWidth = 1.5;
                context.strokeStyle = '#ffffff';
                context.stroke();

                context.fillStyle = '#ffffff';
                context.font = 'bold 11px sans-serif';
                context.textAlign = 'center';
                context.textBaseline = 'middle';
                context.fillText('!', 0, 0.5);
            }
        });

        shape.refType = refType;
        shape.refId = refId;
        shape.targetId = targetId;
        shape.idKavling = refId;
        shape.prioritas = prioritas;
        shape.phaseOffset = phaseOffset;

        return shape;
    }

    function createAnimation(Konva, layer, isBaseVisibleCallback) {
        if (!Konva || typeof Konva.Animation !== 'function') {
            return {
                start: function() {},
                stop: function() {},
                isRunning: function() { return false; }
            };
        }

        let lastFrameTime = -Infinity;

        const anim = new Konva.Animation(function(frame) {
            const timeMs = (frame && typeof frame.time === 'number')
                ? frame.time
                : (typeof performance !== 'undefined' ? performance.now() : Date.now());

            if (timeMs - lastFrameTime < FRAME_INTERVAL_MS) {
                return false;
            }
            lastFrameTime = timeMs;

            const overlayShapes = typeof layer.find === 'function'
                ? layer.find('.siteplan-tiket-overlay-shape')
                : (layer.children ? layer.children.filter(function(c) {
                    return c.name === 'siteplan-tiket-overlay-shape' || (c.attrs && c.attrs.name === 'siteplan-tiket-overlay-shape');
                }) : []);

            for (let i = 0; i < overlayShapes.length; i++) {
                const shape = overlayShapes[i];
                const refType = shape.refType !== undefined
                    ? shape.refType
                    : (typeof shape.getAttr === 'function' ? shape.getAttr('refType') : (shape.attrs && shape.attrs.refType));
                const refId = shape.refId !== undefined
                    ? shape.refId
                    : (typeof shape.getAttr === 'function' ? shape.getAttr('refId') : (shape.attrs && shape.attrs.refId));
                const idKavling = shape.idKavling !== undefined
                    ? shape.idKavling
                    : (typeof shape.getAttr === 'function' ? shape.getAttr('idKavling') : (shape.attrs && shape.attrs.idKavling));

                const targetId = shape.targetId ||
                    (typeof shape.getAttr === 'function' ? shape.getAttr('targetId') : (shape.attrs && shape.attrs.targetId)) ||
                    (((refType === 'others') ? 'others' : 'kav') + (refId !== undefined ? refId : (idKavling !== undefined ? idKavling : '')));

                const isVisible = typeof isBaseVisibleCallback === 'function'
                    ? Boolean(isBaseVisibleCallback(targetId, refType, refId !== undefined ? refId : idKavling))
                    : true;

                if (!isVisible) {
                    if (typeof shape.visible === 'function') {
                        shape.visible(false);
                    } else if (shape.attrs) {
                        shape.attrs.visible = false;
                    }
                    continue;
                }

                if (typeof shape.visible === 'function') {
                    shape.visible(true);
                } else if (shape.attrs) {
                    shape.attrs.visible = true;
                }

                const phaseOffset = shape.phaseOffset !== undefined
                    ? shape.phaseOffset
                    : (typeof shape.getAttr === 'function' ? shape.getAttr('phaseOffset') : ((shape.attrs && shape.attrs.phaseOffset) || 0));

                const opacity = calculateOpacity(timeMs, phaseOffset);

                if (typeof shape.opacity === 'function') {
                    shape.opacity(opacity);
                } else if (shape.attrs) {
                    shape.attrs.opacity = opacity;
                }

                // Opsi 1: Pulsing high-contrast stroke
                const strokeWidth = 1.0 + 2.5 * opacity;
                if (typeof shape.strokeWidth === 'function') {
                    shape.strokeWidth(strokeWidth);
                } else if (shape.attrs) {
                    shape.attrs.strokeWidth = strokeWidth;
                }
            }

            const badgeShapes = typeof layer.find === 'function'
                ? layer.find('.siteplan-tiket-badge-shape')
                : (layer.children ? layer.children.filter(function(c) {
                    return c.name === 'siteplan-tiket-badge-shape' || (c.attrs && c.attrs.name === 'siteplan-tiket-badge-shape');
                }) : []);

            for (let j = 0; j < badgeShapes.length; j++) {
                const badge = badgeShapes[j];
                const refType = badge.refType !== undefined
                    ? badge.refType
                    : (typeof badge.getAttr === 'function' ? badge.getAttr('refType') : (badge.attrs && badge.attrs.refType));
                const refId = badge.refId !== undefined
                    ? badge.refId
                    : (typeof badge.getAttr === 'function' ? badge.getAttr('refId') : (badge.attrs && badge.attrs.refId));
                const idKavling = badge.idKavling !== undefined
                    ? badge.idKavling
                    : (typeof badge.getAttr === 'function' ? badge.getAttr('idKavling') : (badge.attrs && badge.attrs.idKavling));

                const targetId = badge.targetId ||
                    (typeof badge.getAttr === 'function' ? badge.getAttr('targetId') : (badge.attrs && badge.attrs.targetId)) ||
                    (((refType === 'others') ? 'others' : 'kav') + (refId !== undefined ? refId : (idKavling !== undefined ? idKavling : '')));

                const isVisible = typeof isBaseVisibleCallback === 'function'
                    ? Boolean(isBaseVisibleCallback(targetId, refType, refId !== undefined ? refId : idKavling))
                    : true;

                if (!isVisible) {
                    if (typeof badge.visible === 'function') {
                        badge.visible(false);
                    } else if (badge.attrs) {
                        badge.attrs.visible = false;
                    }
                    continue;
                }

                if (typeof badge.visible === 'function') {
                    badge.visible(true);
                } else if (badge.attrs) {
                    badge.attrs.visible = true;
                }

                const phaseOffset = badge.phaseOffset !== undefined
                    ? badge.phaseOffset
                    : (typeof badge.getAttr === 'function' ? badge.getAttr('phaseOffset') : ((badge.attrs && badge.attrs.phaseOffset) || 0));

                const opacity = calculateOpacity(timeMs, phaseOffset);

                // Opsi 2: Badge membesar-mengecil halus (scale 0.85 s.d. 1.15)
                const scale = 0.85 + 0.3 * opacity;
                if (typeof badge.scaleX === 'function' && typeof badge.scaleY === 'function') {
                    badge.scaleX(scale);
                    badge.scaleY(scale);
                } else if (typeof badge.scale === 'function') {
                    badge.scale({ x: scale, y: scale });
                } else if (badge.attrs) {
                    badge.attrs.scaleX = scale;
                    badge.attrs.scaleY = scale;
                }
            }
        }, layer);

        let running = false;

        return {
            start: function() {
                if (anim && typeof anim.start === 'function') {
                    anim.start();
                    running = true;
                }
            },
            stop: function() {
                if (anim && typeof anim.stop === 'function') {
                    anim.stop();
                    running = false;
                }
            },
            isRunning: function() {
                if (anim && typeof anim.isRunning === 'function') {
                    return anim.isRunning();
                }
                return running;
            },
            _anim: anim
        };
    }

    function stopAnimation(layer) {
        if (layer && layer._tiketAnimation) {
            if (typeof layer._tiketAnimation.stop === 'function') {
                layer._tiketAnimation.stop();
            }
            layer._tiketAnimation = null;
        }
    }

    function clearOverlay(layer) {
        stopAnimation(layer);
        if (layer && typeof layer.destroyChildren === 'function') {
            layer.destroyChildren();
        }
        if (layer && typeof layer.batchDraw === 'function') {
            layer.batchDraw();
        }
    }

    function buildOverlayShapes(Konva, layer, itemList, conf, isBaseVisibleCallback) {
        clearOverlay(layer);

        const problemItems = (itemList || []).filter(function(item) {
            return item && (item.active_tiket_id || item.active_tiket_prioritas);
        });

        if (problemItems.length === 0) {
            return [];
        }

        const total = problemItems.length;
        const createdShapes = [];

        for (let index = 0; index < total; index++) {
            const item = problemItems[index];
            const phaseOffset = index * ((2 * Math.PI) / Math.max(total, 1));

            const options = Object.assign({}, item, {
                conf: conf,
                phaseOffset: phaseOffset,
                points: item.points,
                prioritas: item.prioritas !== undefined ? item.prioritas : item.active_tiket_prioritas
            });

            const shape = createOverlayShape(Konva, options);
            const badge = createBadgeShape(Konva, options);

            if (typeof layer.add === 'function') {
                layer.add(shape);
                layer.add(badge);
            }
            createdShapes.push(shape);
            createdShapes.push(badge);
        }

        const anim = createAnimation(Konva, layer, isBaseVisibleCallback);
        if (layer) {
            layer._tiketAnimation = anim;
        }
        anim.start();

        return createdShapes;
    }

    return {
        TARGET_FPS: TARGET_FPS,
        FRAME_INTERVAL_MS: FRAME_INTERVAL_MS,
        DEFAULT_CYCLE_DURATION_MS: DEFAULT_CYCLE_DURATION_MS,
        PRIORITY_COLORS: PRIORITY_COLORS,
        getPriorityColor: getPriorityColor,
        calculateOpacity: calculateOpacity,
        calculateCentroid: calculateCentroid,
        createOverlayShape: createOverlayShape,
        createBadgeShape: createBadgeShape,
        createAnimation: createAnimation,
        stopAnimation: stopAnimation,
        clearOverlay: clearOverlay,
        buildOverlayShapes: buildOverlayShapes
    };
});
