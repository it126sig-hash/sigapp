const SiteplanTargetOverlay = require('../../public/assets/js/siteplan/target-overlay.js');

class MockNode {
    constructor(attrs) {
        this.attrs = Object.assign({}, attrs);
        this.children = [];
    }

    add(...children) {
        this.children.push(...children);
        return this;
    }

    destroy() {
        this.destroyed = true;
    }
}

class MockShape extends MockNode {}

class MockLayer extends MockNode {
    constructor(attrs) {
        super(attrs);
        this.destroyChildrenCalls = 0;
        this.batchDrawCalls = 0;
    }

    destroyChildren() {
        this.destroyChildrenCalls += 1;
        this.children.forEach((child) => child.destroy());
        this.children = [];
    }

    batchDraw() {
        this.batchDrawCalls += 1;
        this.drawn = true;
    }
}

const Konva = {
    Shape: MockShape,
    Layer: MockLayer
};

function createRecordingCanvasContext() {
    const operations = [];
    const ctx = {
        globalCompositeOperation: 'source-over',
        fillStyle: '',
        strokeStyle: '',
        lineWidth: 1,
        lineJoin: 'miter',
        lineCap: 'butt',
        save: jest.fn(() => {
            operations.push({ op: 'save' });
        }),
        restore: jest.fn(() => {
            operations.push({ op: 'restore' });
        }),
        beginPath: jest.fn(() => {
            operations.push({ op: 'beginPath' });
        }),
        moveTo: jest.fn((x, y) => {
            operations.push({ op: 'moveTo', x, y });
        }),
        lineTo: jest.fn((x, y) => {
            operations.push({ op: 'lineTo', x, y });
        }),
        closePath: jest.fn(() => {
            operations.push({ op: 'closePath' });
        }),
        fill: jest.fn(() => {
            operations.push({
                op: 'fill',
                globalCompositeOperation: ctx.globalCompositeOperation,
                fillStyle: ctx.fillStyle,
                lineWidth: ctx.lineWidth,
                lineJoin: ctx.lineJoin,
                lineCap: ctx.lineCap
            });
        }),
        stroke: jest.fn(() => {
            operations.push({
                op: 'stroke',
                globalCompositeOperation: ctx.globalCompositeOperation,
                strokeStyle: ctx.strokeStyle,
                lineWidth: ctx.lineWidth,
                lineJoin: ctx.lineJoin,
                lineCap: ctx.lineCap
            });
        })
    };

    return { ctx, operations };
}

describe('SiteplanTargetOverlay', () => {
    describe('Constants & UMD Export', () => {
        test('mengekspor konstanta default sesuai spesifikasi dan terdaftar di globalThis', () => {
            expect(SiteplanTargetOverlay.DEFAULT_STROKE_COLOR).toBe('#000000');
            expect(SiteplanTargetOverlay.DEFAULT_BORDER_WIDTH).toBe(2.5);
            expect(SiteplanTargetOverlay.DEFAULT_GAP_BRIDGE).toBe(2);
            expect(SiteplanTargetOverlay.MIN_GAP_BRIDGE).toBe(0);
            expect(SiteplanTargetOverlay.MAX_GAP_BRIDGE).toBe(12);
            expect(globalThis.SiteplanTargetOverlay).toBe(SiteplanTargetOverlay);
        });
    });

    describe('normalizeGapBridge', () => {
        test('mengembalikan DEFAULT_GAP_BRIDGE saat undefined, null, NaN, atau string invalid', () => {
            expect(SiteplanTargetOverlay.normalizeGapBridge(undefined)).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge(null)).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge(NaN)).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge(Infinity)).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge(-Infinity)).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge('abc')).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge('')).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge('   ')).toBe(2);
            expect(SiteplanTargetOverlay.normalizeGapBridge(true)).toBe(2);
        });

        test('melakukan clamp ke rentang [0, 12]', () => {
            expect(SiteplanTargetOverlay.normalizeGapBridge(-5)).toBe(0);
            expect(SiteplanTargetOverlay.normalizeGapBridge(-0)).toBe(0);
            expect(SiteplanTargetOverlay.normalizeGapBridge(0)).toBe(0);
            expect(SiteplanTargetOverlay.normalizeGapBridge('0')).toBe(0);
            expect(SiteplanTargetOverlay.normalizeGapBridge(12)).toBe(12);
            expect(SiteplanTargetOverlay.normalizeGapBridge(15.4)).toBe(12);
            expect(SiteplanTargetOverlay.normalizeGapBridge('99')).toBe(12);
        });

        test('membulatkan nilai ke 1 angka desimal', () => {
            expect(SiteplanTargetOverlay.normalizeGapBridge(2.34)).toBe(2.3);
            expect(SiteplanTargetOverlay.normalizeGapBridge(2.35)).toBe(2.4);
            expect(SiteplanTargetOverlay.normalizeGapBridge('4.67')).toBe(4.7);
        });
    });

    describe('parsePoints', () => {
        test('menormalisasi array flat dan nested [[x, y], ...]', () => {
            expect(SiteplanTargetOverlay.parsePoints([0, 0, 10, 0, 10, 10])).toEqual([0, 0, 10, 0, 10, 10]);
            expect(SiteplanTargetOverlay.parsePoints([[0, 0], [10, 0], [10, 10], [0, 10]])).toEqual([0, 0, 10, 0, 10, 10, 0, 10]);
            expect(SiteplanTargetOverlay.parsePoints([['1', '2'], ['3', '4'], ['5', '6']])).toEqual([1, 2, 3, 4, 5, 6]);
        });

        test('menormalisasi string CSV dan string JSON', () => {
            expect(SiteplanTargetOverlay.parsePoints('10,20,30,20,30,40')).toEqual([10, 20, 30, 20, 30, 40]);
            expect(SiteplanTargetOverlay.parsePoints('[10, 20, 30, 20, 30, 40]')).toEqual([10, 20, 30, 20, 30, 40]);
            expect(SiteplanTargetOverlay.parsePoints('[[0,0],[20,0],[20,20]]')).toEqual([0, 0, 20, 0, 20, 20]);
            expect(SiteplanTargetOverlay.parsePoints('10, 20, 30, 40, 50, 60,')).toEqual([10, 20, 30, 40, 50, 60]);
        });

        test('mengabaikan elemen koordinat ganjil terakhir dan mengembalikan [] untuk input kosong/invalid', () => {
            expect(SiteplanTargetOverlay.parsePoints([1, 2, 3, 4, 5])).toEqual([1, 2, 3, 4]);
            expect(SiteplanTargetOverlay.parsePoints('10,20,30')).toEqual([10, 20]);
            expect(SiteplanTargetOverlay.parsePoints('')).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints('   ')).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints('abc')).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints('abc,def')).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints(null)).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints(undefined)).toEqual([]);
            expect(SiteplanTargetOverlay.parsePoints({})).toEqual([]);
        });
    });

    describe('computeBounds & getSelfRect', () => {
        test('menghitung bounding box gabungan dan memperluas dengan padding di semua sisi', () => {
            const polygons = [
                [10, 20, 30, 20, 30, 40, 10, 40],
                [50, 10, 80, 10, 80, 60, 50, 60],
                [0, 0, 5, 5] // < 6 titik, diabaikan
            ];

            expect(SiteplanTargetOverlay.computeBounds(polygons)).toEqual({
                x: 10,
                y: 10,
                width: 70,
                height: 50
            });

            expect(SiteplanTargetOverlay.computeBounds(polygons, 4.5)).toEqual({
                x: 5.5,
                y: 5.5,
                width: 79,
                height: 59
            });

            expect(SiteplanTargetOverlay.computeBounds(polygons, -5)).toEqual({
                x: 10,
                y: 10,
                width: 70,
                height: 50
            });
        });

        test('mengembalikan bounding box nol untuk array kosong atau tidak ada poligon valid', () => {
            expect(SiteplanTargetOverlay.computeBounds([])).toEqual({ x: 0, y: 0, width: 0, height: 0 });
            expect(SiteplanTargetOverlay.computeBounds(null, 5)).toEqual({ x: 0, y: 0, width: 0, height: 0 });
            expect(SiteplanTargetOverlay.computeBounds([[0, 0, 10, 10]], 5)).toEqual({ x: 0, y: 0, width: 0, height: 0 });
        });

        test('shape.getSelfRect() menggunakan padding borderWidth + gapBridge', () => {
            const shape = SiteplanTargetOverlay.createOverlayShape(
                Konva,
                [[10, 20, 30, 20, 30, 50, 10, 50]],
                { borderWidth: 3, gapBridge: 4 }
            );

            // padding = 3 + 4 = 7 -> x: 10-7=3, y: 20-7=13, width: 20+14=34, height: 30+14=44
            expect(shape.getSelfRect()).toEqual({
                x: 3,
                y: 13,
                width: 34,
                height: 44
            });
        });
    });

    describe('tracePolygonPath', () => {
        test('memanggil beginPath, moveTo, lineTo, dan closePath untuk poligon valid', () => {
            const { ctx, operations } = createRecordingCanvasContext();
            SiteplanTargetOverlay.tracePolygonPath(ctx, [0, 0, 20, 0, 20, 10, 0, 10]);

            expect(operations).toEqual([
                { op: 'beginPath' },
                { op: 'moveTo', x: 0, y: 0 },
                { op: 'lineTo', x: 20, y: 0 },
                { op: 'lineTo', x: 20, y: 10 },
                { op: 'lineTo', x: 0, y: 10 },
                { op: 'closePath' }
            ]);
        });

        test('aman terhadap context null atau koordinat kurang dari 6', () => {
            const { ctx } = createRecordingCanvasContext();
            expect(() => SiteplanTargetOverlay.tracePolygonPath(null, [0, 0, 10, 0, 10, 10])).not.toThrow();
            SiteplanTargetOverlay.tracePolygonPath(ctx, [0, 0, 10, 10]);
            expect(ctx.beginPath).not.toHaveBeenCalled();
        });
    });

    describe('createOverlayShape', () => {
        test('membuat Konva.Shape dengan atribut standar dan menjalankan sceneFunc 2-pass saat gapBridge > 0', () => {
            const polygons = [
                [0, 0, 20, 0, 20, 20, 0, 20],
                [22, 0, 42, 0, 42, 20, 22, 20]
            ];

            const shape = SiteplanTargetOverlay.createOverlayShape(Konva, polygons, {
                strokeColor: '#123456',
                borderWidth: 3,
                gapBridge: 2.5
            });

            expect(shape.attrs.id).toBe('siteplan-target-border-overlay');
            expect(shape.attrs.name).toBe('siteplan-target-border-shape');
            expect(shape.attrs.listening).toBe(false);
            expect(shape.attrs.perfectDrawEnabled).toBe(false);
            expect(typeof shape.attrs.sceneFunc).toBe('function');

            const { ctx, operations } = createRecordingCanvasContext();
            // Simulasikan wrapper Konva.SceneContext yang memiliki _context
            shape.attrs.sceneFunc({ _context: ctx });

            expect(ctx.save).toHaveBeenCalledTimes(1);
            expect(ctx.restore).toHaveBeenCalledTimes(1);
            expect(operations[0]).toEqual({ op: 'save' });
            expect(operations[operations.length - 1]).toEqual({ op: 'restore' });

            const pass1Fills = operations.filter((item) => item.op === 'fill' && item.globalCompositeOperation === 'source-over');
            const pass1Strokes = operations.filter((item) => item.op === 'stroke' && item.globalCompositeOperation === 'source-over');
            expect(pass1Fills).toHaveLength(2);
            expect(pass1Strokes).toHaveLength(2);
            pass1Strokes.forEach((entry) => {
                expect(entry.strokeStyle).toBe('#123456');
                expect(entry.lineWidth).toBe(11); // 2 * (3 + 2.5)
                expect(entry.lineJoin).toBe('round');
                expect(entry.lineCap).toBe('round');
            });

            const pass2Fills = operations.filter((item) => item.op === 'fill' && item.globalCompositeOperation === 'destination-out');
            const pass2Strokes = operations.filter((item) => item.op === 'stroke' && item.globalCompositeOperation === 'destination-out');
            expect(pass2Fills).toHaveLength(2);
            expect(pass2Strokes).toHaveLength(2);
            pass2Strokes.forEach((entry) => {
                expect(entry.strokeStyle).toBe('#000000');
                expect(entry.lineWidth).toBe(5); // 2 * 2.5
                expect(entry.lineJoin).toBe('round');
                expect(entry.lineCap).toBe('round');
            });
        });

        test('pada Pass 2 saat gapBridge === 0 hanya memanggil fill tanpa stroke', () => {
            const polygons = [
                [0, 0, 20, 0, 20, 20, 0, 20],
                [30, 0, 50, 0, 50, 20, 30, 20]
            ];

            const shape = SiteplanTargetOverlay.createOverlayShape(Konva, polygons, {
                borderWidth: 2.5,
                gapBridge: 0
            });

            const { ctx, operations } = createRecordingCanvasContext();
            shape.attrs.sceneFunc(ctx);

            expect(ctx.save).toHaveBeenCalledTimes(1);
            expect(ctx.restore).toHaveBeenCalledTimes(1);

            const pass1Fills = operations.filter((item) => item.op === 'fill' && item.globalCompositeOperation === 'source-over');
            const pass1Strokes = operations.filter((item) => item.op === 'stroke' && item.globalCompositeOperation === 'source-over');
            expect(pass1Fills).toHaveLength(2);
            expect(pass1Strokes).toHaveLength(2);
            expect(pass1Strokes[0].lineWidth).toBe(5); // 2 * (2.5 + 0)

            const pass2Fills = operations.filter((item) => item.op === 'fill' && item.globalCompositeOperation === 'destination-out');
            const pass2Strokes = operations.filter((item) => item.op === 'stroke' && item.globalCompositeOperation === 'destination-out');
            expect(pass2Fills).toHaveLength(2);
            expect(pass2Strokes).toHaveLength(0);
        });

        test('tidak memanggil save/restore ketika tidak ada poligon valid', () => {
            const shape = SiteplanTargetOverlay.createOverlayShape(Konva, [[0, 0, 10, 10]]);
            const { ctx } = createRecordingCanvasContext();
            shape.attrs.sceneFunc({ _context: ctx });

            expect(ctx.save).not.toHaveBeenCalled();
            expect(ctx.restore).not.toHaveBeenCalled();
            expect(shape.getSelfRect()).toEqual({ x: 0, y: 0, width: 0, height: 0 });
        });
    });

    describe('buildOverlayShapes', () => {
        test('memfilter item dengan isBaseVisibleCallback dan membangun 1 shape gabungan pada layer', () => {
            const layer = new MockLayer();
            const existing = new MockShape({ id: 'stale' });
            layer.add(existing);

            const items = [
                { id_kavling: 101, points: [0, 0, 20, 0, 20, 20, 0, 20] },
                { idKavling: 102, points: '25,0,45,0,45,20,25,20' },
                { id_kavling: 103, points: [50, 0, 70, 0, 70, 20, 50, 20] },
                { id_kavling: 104, points: [0, 0, 10, 10] } // < 6 titik
            ];

            const visibleMap = {
                kav101: true,
                kav102: false,
                kav103: true,
                kav104: true
            };
            const isVisibleCb = jest.fn((targetId) => Boolean(visibleMap[targetId]));

            const shape = SiteplanTargetOverlay.buildOverlayShapes(
                Konva,
                layer,
                items,
                isVisibleCb,
                { borderWidth: 3, gapBridge: 2 }
            );

            expect(existing.destroyed).toBe(true);
            expect(shape).not.toBeNull();
            expect(layer.children).toHaveLength(1);
            expect(layer.children[0]).toBe(shape);
            expect(layer.batchDrawCalls).toBe(2); // clearOverlay + buildOverlayShapes
            expect(isVisibleCb).toHaveBeenCalledWith('kav101');
            expect(isVisibleCb).toHaveBeenCalledWith('kav102');
            expect(isVisibleCb).toHaveBeenCalledWith('kav103');

            // Bounds hanya dari kav101 (0..20) dan kav103 (50..70) dengan padding 3 + 2 = 5
            expect(shape.getSelfRect()).toEqual({
                x: -5,
                y: -5,
                width: 80,
                height: 30
            });
        });

        test('membersihkan layer dan mengembalikan null saat item kosong atau semua ter-hide', () => {
            const layer = new MockLayer();
            const items = [
                { id_kavling: 101, points: [0, 0, 20, 0, 20, 20, 0, 20] }
            ];

            SiteplanTargetOverlay.buildOverlayShapes(Konva, layer, items, () => true);
            expect(layer.children).toHaveLength(1);

            const hiddenResult = SiteplanTargetOverlay.buildOverlayShapes(Konva, layer, items, () => false);
            expect(hiddenResult).toBeNull();
            expect(layer.children).toHaveLength(0);
            expect(layer.drawn).toBe(true);

            SiteplanTargetOverlay.buildOverlayShapes(Konva, layer, items);
            expect(layer.children).toHaveLength(1);

            const emptyResult = SiteplanTargetOverlay.buildOverlayShapes(Konva, layer, []);
            expect(emptyResult).toBeNull();
            expect(layer.children).toHaveLength(0);
        });

        test('mengembalikan null jika Konva atau layer tidak tersedia', () => {
            expect(SiteplanTargetOverlay.buildOverlayShapes(null, new MockLayer(), [])).toBeNull();
            expect(SiteplanTargetOverlay.buildOverlayShapes(Konva, null, [])).toBeNull();
        });
    });

    describe('clearOverlay', () => {
        test('memanggil destroyChildren dan batchDraw pada layer dan aman saat layer null', () => {
            const layer = new MockLayer();
            const child = new MockShape({ id: 'test' });
            layer.add(child);

            SiteplanTargetOverlay.clearOverlay(layer);

            expect(child.destroyed).toBe(true);
            expect(layer.children).toHaveLength(0);
            expect(layer.destroyChildrenCalls).toBe(1);
            expect(layer.batchDrawCalls).toBe(1);
            expect(() => SiteplanTargetOverlay.clearOverlay(null)).not.toThrow();
        });
    });
});
