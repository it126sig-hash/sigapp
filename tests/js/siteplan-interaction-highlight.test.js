const SiteplanInteractionHighlight = require('../../public/assets/js/siteplan/interaction-highlight.js');

class MockNode {
    constructor(attrs) {
        this.attrs = Object.assign({}, attrs);
        this.children = [];
    }

    add(...children) {
        this.children.push(...children);
        return this;
    }

    points(value) {
        if (value === undefined) return this.attrs.points;
        this.attrs.points = value;
        return this;
    }

    show() {
        this.attrs.visible = true;
    }

    hide() {
        this.attrs.visible = false;
    }

    isVisible() {
        return this.attrs.visible !== false;
    }
}

class Group extends MockNode {}
class Line extends MockNode {}
class Rect extends MockNode {}
class Text extends MockNode {}

const Konva = { Group, Line, Rect, Text };
const square = [0, 0, 90, 0, 90, 120, 0, 120];

describe('Siteplan interaction highlight', () => {
    test('hover memakai dua outline reusable tanpa menerima event', () => {
        const highlight = SiteplanInteractionHighlight.createHoverHighlight(Konva);

        expect(highlight.attrs.visible).toBe(false);
        expect(highlight.attrs.listening).toBe(false);
        expect(highlight.children).toHaveLength(2);
        expect(highlight.outerLine.attrs.stroke).toBe('#ffffff');
        expect(highlight.innerLine.attrs.stroke).toBe('#111827');
        expect(highlight.outerLine.attrs.strokeScaleEnabled).toBe(false);
        expect(highlight.innerLine.attrs.listening).toBe(false);

        highlight.setHighlightPoints(square);
        highlight.show();

        expect(highlight.outerLine.points()).toEqual(square);
        expect(highlight.innerLine.points()).toEqual(square);
        expect(highlight.isVisible()).toBe(true);
    });

    test('seleksi memakai halo putih, biru SIGAPP, dan badge kontras', () => {
        const selection = SiteplanInteractionHighlight.createSelectionHighlight(Konva, square, 12);

        expect(selection.attrs.id).toBe('siteplan-selection');
        expect(selection.children).toHaveLength(4);
        expect(selection.outerLine.attrs.stroke).toBe('#ffffff');
        expect(selection.outerLine.attrs.strokeWidth).toBe(7);
        expect(selection.innerLine.attrs.stroke).toBe('#2057a3');
        expect(selection.innerLine.attrs.strokeWidth).toBe(3);
        expect(selection.innerLine.attrs.dash).toEqual([8, 4]);
        expect(selection.badge.attrs.fill).toBe('#2057a3');
        expect(selection.badgeText.attrs.fill).toBe('#ffffff');
        expect(selection.badgeText.attrs.text).toBe('12');
        expect(selection.badgeText.attrs.listening).toBe(false);
    });
});
