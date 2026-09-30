(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanInteractionHighlight = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    const PRIMARY_COLOR = '#2057a3';

    function outlineLine(Konva, options) {
        return new Konva.Line({
            points: options.points || [],
            stroke: options.stroke,
            strokeWidth: options.strokeWidth,
            dash: options.dash || [],
            closed: true,
            listening: false,
            strokeScaleEnabled: false,
            perfectDrawEnabled: false,
            id: options.id || ''
        });
    }

    function createOutlineGroup(Konva, options) {
        const group = new Konva.Group({
            id: options.id || '',
            name: options.name || '',
            visible: options.visible !== false,
            listening: false
        });
        const outerLine = outlineLine(Konva, {
            points: options.points,
            stroke: options.outerStroke || '#ffffff',
            strokeWidth: options.outerWidth || 6,
            id: options.outerId
        });
        const innerLine = outlineLine(Konva, {
            points: options.points,
            stroke: options.innerStroke || '#111827',
            strokeWidth: options.innerWidth || 2,
            dash: options.dash,
            id: options.innerId
        });

        group.add(outerLine, innerLine);
        group.outerLine = outerLine;
        group.innerLine = innerLine;
        group.setHighlightPoints = function(points) {
            outerLine.points(points || []);
            innerLine.points(points || []);
            return group;
        };

        return group;
    }

    function pointBounds(points) {
        const xs = [];
        const ys = [];

        for (let index = 0; index < points.length; index += 2) {
            xs.push(Number(points[index]) || 0);
            ys.push(Number(points[index + 1]) || 0);
        }

        return {
            centerX: (Math.min.apply(null, xs) + Math.max.apply(null, xs)) / 2,
            centerY: (Math.min.apply(null, ys) + Math.max.apply(null, ys)) / 2
        };
    }

    function createHoverHighlight(Konva) {
        return createOutlineGroup(Konva, {
            name: 'siteplan-hover-highlight',
            visible: false,
            outerStroke: '#ffffff',
            outerWidth: 6,
            innerStroke: '#111827',
            innerWidth: 2
        });
    }

    function createSelectionHighlight(Konva, points, selectionNumber) {
        const group = createOutlineGroup(Konva, {
            id: 'siteplan-selection',
            name: 'siteplan-selection-highlight',
            points: points,
            outerStroke: '#ffffff',
            outerWidth: 7,
            outerId: 'selhalo',
            innerStroke: PRIMARY_COLOR,
            innerWidth: 3,
            innerId: 'sel',
            dash: [8, 4]
        });
        const bounds = pointBounds(points || []);
        const label = String(selectionNumber);
        const badgeWidth = Math.max(24, (label.length * 9) + 12);
        const badgeHeight = 24;
        const badge = new Konva.Rect({
            x: bounds.centerX - (badgeWidth / 2),
            y: bounds.centerY - (badgeHeight / 2),
            width: badgeWidth,
            height: badgeHeight,
            fill: PRIMARY_COLOR,
            stroke: '#ffffff',
            strokeWidth: 2,
            cornerRadius: badgeHeight / 2,
            listening: false,
            strokeScaleEnabled: false,
            perfectDrawEnabled: false,
            id: 'tselbg'
        });
        const badgeText = new Konva.Text({
            x: bounds.centerX - (badgeWidth / 2),
            y: bounds.centerY - 8,
            width: badgeWidth,
            text: label,
            fontSize: 16,
            fontFamily: 'Calibri',
            fontStyle: 'bold',
            fill: '#ffffff',
            align: 'center',
            listening: false,
            id: 'tsel'
        });

        group.add(badge, badgeText);
        group.badge = badge;
        group.badgeText = badgeText;

        return group;
    }

    return {
        PRIMARY_COLOR: PRIMARY_COLOR,
        createHoverHighlight: createHoverHighlight,
        createSelectionHighlight: createSelectionHighlight
    };
});
