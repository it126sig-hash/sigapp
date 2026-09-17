(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanCompositeShape = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function toPoints(flatPoints) {
        const points = [];
        for (let index = 0; index < flatPoints.length; index += 2) {
            points.push({ x: Number(flatPoints[index]), y: Number(flatPoints[index + 1]) });
        }
        return points;
    }

    function centroid(points) {
        const total = points.reduce(function(sum, point) {
            return { x: sum.x + point.x, y: sum.y + point.y };
        }, { x: 0, y: 0 });

        return { x: total.x / points.length, y: total.y / points.length };
    }

    function rotatePoint(point, angle, center) {
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        const dx = point.x - center.x;
        const dy = point.y - center.y;

        return {
            x: center.x + dx * cos - dy * sin,
            y: center.y + dx * sin + dy * cos
        };
    }

    function bounds(points) {
        return points.reduce(function(result, point) {
            result.minX = Math.min(result.minX, point.x);
            result.maxX = Math.max(result.maxX, point.x);
            result.minY = Math.min(result.minY, point.y);
            result.maxY = Math.max(result.maxY, point.y);
            return result;
        }, { minX: Infinity, maxX: -Infinity, minY: Infinity, maxY: -Infinity });
    }

    function automaticAngle(points, center) {
        let xx = 0;
        let xy = 0;
        let yy = 0;

        points.forEach(function(point) {
            const dx = point.x - center.x;
            const dy = point.y - center.y;
            xx += dx * dx;
            xy += dx * dy;
            yy += dy * dy;
        });

        let angle = 0.5 * Math.atan2(2 * xy, xx - yy);
        const localBounds = bounds(points.map(function(point) {
            return rotatePoint(point, -angle, center);
        }));

        if ((localBounds.maxX - localBounds.minX) > (localBounds.maxY - localBounds.minY)) {
            angle += Math.PI / 2;
        }

        return angle;
    }

    function frame(flatPoints, rotationDeg) {
        const polygon = toPoints(flatPoints);
        const center = centroid(polygon);
        const numericRotation = Number(rotationDeg);
        const hasRotation = rotationDeg !== null && rotationDeg !== undefined && rotationDeg !== '' && Number.isFinite(numericRotation);
        const angle = hasRotation ? (numericRotation - 90) * Math.PI / 180 : automaticAngle(polygon, center);
        const localPolygon = polygon.map(function(point) {
            return rotatePoint(point, -angle, center);
        });

        return {
            polygon: polygon,
            center: center,
            angle: angle,
            bounds: bounds(localPolygon)
        };
    }

    function localRectToWorld(layoutFrame, minX, minY, maxX, maxY) {
        return [
            { x: minX, y: minY },
            { x: maxX, y: minY },
            { x: maxX, y: maxY },
            { x: minX, y: maxY }
        ].map(function(point) {
            return rotatePoint(point, layoutFrame.angle, layoutFrame.center);
        });
    }

    function uprightAngle(angle) {
        let normalized = angle;
        while (normalized > Math.PI / 2) normalized -= Math.PI;
        while (normalized < -Math.PI / 2) normalized += Math.PI;
        return normalized;
    }

    function buildLabelPlan(layoutFrame, layoutBounds, text) {
        if (text === null || text === undefined || String(text).trim() === '') {
            return null;
        }

        const width = layoutBounds.maxX - layoutBounds.minX;
        const height = layoutBounds.maxY - layoutBounds.minY;
        const fontSize = clamp(Math.min(width, height) * 0.32, 8, 32);
        const anchor = rotatePoint({
            x: (layoutBounds.minX + layoutBounds.maxX) / 2,
            y: (layoutBounds.minY + layoutBounds.maxY) / 2
        }, layoutFrame.angle, layoutFrame.center);

        return {
            text: String(text),
            x: anchor.x,
            y: anchor.y,
            angle: uprightAngle(layoutFrame.angle),
            fontSize: fontSize,
            haloWidth: clamp(fontSize * 0.12, 1, 3)
        };
    }

    function normalizeSegments(segments) {
        const valid = (Array.isArray(segments) ? segments : []).map(function(segment) {
            return {
                config_name: segment.config_name || 'Def',
                ratio: clamp(Number(segment.ratio) || 0, 0, 1)
            };
        }).filter(function(segment) {
            return segment.ratio > 0;
        });
        const total = valid.reduce(function(sum, segment) { return sum + segment.ratio; }, 0);

        if (total <= 0) {
            return [{ config_name: 'Def', ratio: 1 }];
        }

        return valid.map(function(segment) {
            return { config_name: segment.config_name, ratio: segment.ratio / total };
        });
    }

    function buildPaintPlan(flatPoints, visualRows, rotationDeg, colorResolver, strokeWidthResolver, labelText) {
        const layoutFrame = frame(flatPoints, rotationDeg);
        const layoutBounds = layoutFrame.bounds;
        const width = layoutBounds.maxX - layoutBounds.minX;
        const height = layoutBounds.maxY - layoutBounds.minY;
        const rows = Array.isArray(visualRows) && visualRows.length ? visualRows : [{ key: 'default', label: 'Status', segments: [{ config_name: 'Def', ratio: 1 }], markers: [] }];
        const rowHeight = height / rows.length;
        const resolveColor = typeof colorResolver === 'function' ? colorResolver : function() { return '#d1d5db'; };
        const resolveStrokeWidth = typeof strokeWidthResolver === 'function' ? strokeWidthResolver : function() { return 0; };

        const paintRows = rows.map(function(row, rowIndex) {
            const minY = layoutBounds.minY + rowIndex * rowHeight;
            const maxY = minY + rowHeight;
            let consumed = 0;
            const segments = normalizeSegments(row.segments).map(function(segment) {
                const start = consumed;
                consumed += segment.ratio;
                return {
                    config_name: segment.config_name,
                    ratio: segment.ratio,
                    color: resolveColor(segment.config_name),
                    points: localRectToWorld(
                        layoutFrame,
                        layoutBounds.minX + width * start,
                        minY,
                        layoutBounds.minX + width * consumed,
                        maxY
                    )
                };
            });
            const markers = (Array.isArray(row.markers) ? row.markers : []).map(function(marker) {
                const numericPosition = Number(marker.position);
                const position = Number.isFinite(numericPosition) ? clamp(numericPosition, 0, 1) : 0.5;
                const centerY = minY + rowHeight * position;
                const configuredThickness = Number(resolveStrokeWidth(marker.config_name));
                const markerThickness = Number.isFinite(configuredThickness) && configuredThickness > 0
                    ? clamp(configuredThickness, 0.5, rowHeight * 0.5)
                    : Math.min(rowHeight * 0.3, Math.max(1, rowHeight * 0.08));
                return {
                    config_name: marker.config_name,
                    position: position,
                    color: resolveColor(marker.config_name),
                    points: localRectToWorld(
                        layoutFrame,
                        layoutBounds.minX,
                        centerY - markerThickness / 2,
                        layoutBounds.maxX,
                        centerY + markerThickness / 2
                    )
                };
            });

            return {
                key: row.key || ('row-' + rowIndex),
                label: row.label || row.key || ('Baris ' + (rowIndex + 1)),
                segments: segments,
                markers: markers
            };
        });

        return {
            polygon: layoutFrame.polygon,
            bounds: bounds(layoutFrame.polygon),
            rows: paintRows,
            label: buildLabelPlan(layoutFrame, layoutBounds, labelText)
        };
    }

    function path(nativeContext, points) {
        nativeContext.beginPath();
        nativeContext.moveTo(points[0].x, points[0].y);
        for (let index = 1; index < points.length; index++) {
            nativeContext.lineTo(points[index].x, points[index].y);
        }
        nativeContext.closePath();
    }

    function drawLabel(nativeContext, label) {
        if (!label) {
            return;
        }

        nativeContext.save();
        nativeContext.globalCompositeOperation = 'source-over';
        nativeContext.translate(label.x, label.y);
        nativeContext.rotate(label.angle);
        nativeContext.font = label.fontSize + 'px Arial, sans-serif';
        nativeContext.textAlign = 'center';
        nativeContext.textBaseline = 'middle';
        nativeContext.lineJoin = 'round';
        nativeContext.lineWidth = label.haloWidth;
        nativeContext.strokeStyle = '#ffffff';
        nativeContext.strokeText(label.text, 0, 0);
        nativeContext.fillStyle = '#111827';
        nativeContext.fillText(label.text, 0, 0);
        nativeContext.restore();
    }

    function createKonvaShape(Konva, options, colorResolver) {
        const points = options.points || [];
        const paintPlan = buildPaintPlan(
            points,
            options.visualRows,
            options.facadeRotation,
            colorResolver,
            options.strokeWidthResolver,
            options.data && options.data.no_kavling
        );
        const shapeOptions = Object.assign({}, options);
        delete shapeOptions.facadeRotation;

        const shape = new Konva.Shape(Object.assign(shapeOptions, {
            name: 'siteplan-kavling siteplan-data-shape',
            fill: '#000000',
            stroke: options.stroke || '#000000',
            strokeWidth: options.strokeWidth || 0,
            sceneFunc: function(context, node) {
                const nativeContext = context._context;

                nativeContext.save();
                path(nativeContext, paintPlan.polygon);
                nativeContext.clip();

                // Draw base status colors first with the exact configured colors.
                paintPlan.rows.forEach(function(row) {
                    row.segments.forEach(function(segment) {
                        path(nativeContext, segment.points);
                        nativeContext.fillStyle = segment.color;
                        nativeContext.fill();
                    });
                });

                // Markers are annotations, not part of the blended base colors.
                // Force source-over and draw them in a second pass so they stay
                // consistent and visibly sit above every segment.
                nativeContext.globalCompositeOperation = 'source-over';
                paintPlan.rows.forEach(function(row) {
                    row.markers.forEach(function(marker) {
                        path(nativeContext, marker.points);
                        nativeContext.fillStyle = marker.color;
                        nativeContext.fill();
                    });
                });

                // Keep the number above both status segments and markers while
                // retaining the same single Konva.Shape node.
                drawLabel(nativeContext, paintPlan.label);

                nativeContext.restore();
                path(nativeContext, paintPlan.polygon);
                nativeContext.strokeStyle = node.stroke() || '#000000';
                nativeContext.lineWidth = Math.max(0.5, Number(node.strokeWidth()) || 0);
                nativeContext.stroke();
            },
            hitFunc: function(context, node) {
                context.beginPath();
                context.moveTo(paintPlan.polygon[0].x, paintPlan.polygon[0].y);
                for (let index = 1; index < paintPlan.polygon.length; index++) {
                    context.lineTo(paintPlan.polygon[index].x, paintPlan.polygon[index].y);
                }
                context.closePath();
                context.fillStrokeShape(node);
            }
        }));

        shape.getSelfRect = function() {
            return {
                x: paintPlan.bounds.minX,
                y: paintPlan.bounds.minY,
                width: paintPlan.bounds.maxX - paintPlan.bounds.minX,
                height: paintPlan.bounds.maxY - paintPlan.bounds.minY
            };
        };

        return shape;
    }

    return {
        buildPaintPlan: buildPaintPlan,
        createKonvaShape: createKonvaShape
    };
});
