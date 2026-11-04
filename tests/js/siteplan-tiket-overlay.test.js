const SiteplanTiketOverlay = require('../../public/assets/js/siteplan/tiket-overlay.js');

class MockNode {
    constructor(attrs) {
        this.attrs = Object.assign({}, attrs);
        this.children = [];
    }

    add(...children) {
        this.children.push(...children);
        return this;
    }

    points(val) {
        if (val === undefined) return this.attrs.points;
        this.attrs.points = val;
        return this;
    }

    opacity(val) {
        if (val === undefined) return this.attrs.opacity;
        this.attrs.opacity = val;
        return this;
    }

    strokeWidth(val) {
        if (val === undefined) return this.attrs.strokeWidth;
        this.attrs.strokeWidth = val;
        return this;
    }

    scale(val) {
        if (val === undefined) return this.attrs.scale || { x: 1, y: 1 };
        this.attrs.scale = val;
        this.attrs.scaleX = val.x;
        this.attrs.scaleY = val.y;
        return this;
    }

    scaleX(val) {
        if (val === undefined) return this.attrs.scaleX;
        this.attrs.scaleX = val;
        return this;
    }

    scaleY(val) {
        if (val === undefined) return this.attrs.scaleY;
        this.attrs.scaleY = val;
        return this;
    }

    visible(val) {
        if (val === undefined) return this.attrs.visible !== false;
        this.attrs.visible = Boolean(val);
        return this;
    }

    getAttr(key) {
        return this.attrs[key];
    }

    setAttr(key, val) {
        this.attrs[key] = val;
        return this;
    }

    destroy() {
        this.destroyed = true;
    }
}

class MockLine extends MockNode {}
class MockShape extends MockNode {}

class MockLayer extends MockNode {
    constructor(attrs) {
        super(attrs);
        this._tiketAnimation = null;
    }

    find(selector) {
        if (selector === '.siteplan-tiket-overlay-shape') {
            return this.children.filter(c => c.attrs.name === 'siteplan-tiket-overlay-shape');
        }
        if (selector === '.siteplan-tiket-badge-shape') {
            return this.children.filter(c => c.attrs.name === 'siteplan-tiket-badge-shape');
        }
        return [];
    }

    destroyChildren() {
        this.children.forEach(c => c.destroy());
        this.children = [];
    }

    batchDraw() {
        this.drawn = true;
    }
}

class MockAnimation {
    constructor(func, layer) {
        this.func = func;
        this.layer = layer;
        this._running = false;
    }

    start() {
        this._running = true;
    }

    stop() {
        this._running = false;
    }

    isRunning() {
        return this._running;
    }

    // Helper for testing animation frame execution
    step(frame) {
        if (typeof this.func === 'function') {
            return this.func(frame || { time: 0, timeDiff: 16 });
        }
    }
}

const Konva = {
    Line: MockLine,
    Shape: MockShape,
    Layer: MockLayer,
    Animation: MockAnimation
};

describe('SiteplanTiketOverlay', () => {
    describe('Constants', () => {
        test('memiliki konstanta TARGET_FPS = 30 dan FRAME_INTERVAL_MS = 33.33ms', () => {
            expect(SiteplanTiketOverlay.TARGET_FPS).toBe(30);
            expect(SiteplanTiketOverlay.FRAME_INTERVAL_MS).toBeCloseTo(33.333, 2);
        });
    });

    describe('getPriorityColor', () => {
        test('mengembalikan kode warna default untuk setiap tingkat prioritas', () => {
            expect(SiteplanTiketOverlay.getPriorityColor('urgent')).toBe('#ea5455');
            expect(SiteplanTiketOverlay.getPriorityColor('medium')).toBe('#ff9f43');
            expect(SiteplanTiketOverlay.getPriorityColor('normal')).toBe('#00cfe8');
            expect(SiteplanTiketOverlay.getPriorityColor('low')).toBe('#28c76f');
            expect(SiteplanTiketOverlay.getPriorityColor('unknown')).toBe('#ea5455');
            expect(SiteplanTiketOverlay.getPriorityColor(null)).toBe('#ea5455');
        });

        test('mendukung case-insensitive dan variasi nama prioritas', () => {
            expect(SiteplanTiketOverlay.getPriorityColor('URGENT')).toBe('#ea5455');
            expect(SiteplanTiketOverlay.getPriorityColor('  Medium  ')).toBe('#ff9f43');
            expect(SiteplanTiketOverlay.getPriorityColor('Masalah Normal')).toBe('#00cfe8');
            expect(SiteplanTiketOverlay.getPriorityColor('Masalah Low')).toBe('#28c76f');
        });

        test('mengutamakan warna dari conf jika tersedia', () => {
            const conf = {
                'Masalah Urgent': { fill: '#ff0000' },
                'Masalah Medium': { fill: '#ffaa00' },
                'Masalah Normal': { fill: '#00ffff' },
                'Masalah Low': { fill: '#00ff00' }
            };

            expect(SiteplanTiketOverlay.getPriorityColor('urgent', conf)).toBe('#ff0000');
            expect(SiteplanTiketOverlay.getPriorityColor('medium', conf)).toBe('#ffaa00');
            expect(SiteplanTiketOverlay.getPriorityColor('normal', conf)).toBe('#00ffff');
            expect(SiteplanTiketOverlay.getPriorityColor('low', conf)).toBe('#00ff00');
        });
    });

    describe('calculateOpacity', () => {
        const cycle = 2800;

        test('menghasilkan nilai antara 0.0 dan 1.0 (clamped)', () => {
            for (let t = 0; t <= 5600; t += 200) {
                const op = SiteplanTiketOverlay.calculateOpacity(t, 0, cycle);
                expect(op).toBeGreaterThanOrEqual(0);
                expect(op).toBeLessThanOrEqual(1);
            }
        });

        test('pada time 0 dan phase 0 menghasilkan opacity 0.5', () => {
            const op = SiteplanTiketOverlay.calculateOpacity(0, 0, cycle);
            expect(op).toBeCloseTo(0.5, 4);
        });

        test('pada puncak sinus (quarter cycle) menghasilkan opacity 1.0', () => {
            const op = SiteplanTiketOverlay.calculateOpacity(cycle / 4, 0, cycle);
            expect(op).toBeCloseTo(1.0, 4);
        });

        test('pada dasar sinus (three quarters cycle) menghasilkan opacity 0.0', () => {
            const op = SiteplanTiketOverlay.calculateOpacity((3 * cycle) / 4, 0, cycle);
            expect(op).toBeCloseTo(0.0, 4);
        });

        test('phase offset menggeser kurva secara tepat', () => {
            const op1 = SiteplanTiketOverlay.calculateOpacity(0, Math.PI / 2, cycle);
            expect(op1).toBeCloseTo(0.0, 4);

            const op2 = SiteplanTiketOverlay.calculateOpacity(0, -Math.PI / 2, cycle);
            expect(op2).toBeCloseTo(1.0, 4);
        });
    });

    describe('calculateCentroid', () => {
        test('menghitung titik tengah poligon dari array koordinat', () => {
            // Persegi 10x10 dari (0,0) sampai (10,10)
            const points = [0, 0, 10, 0, 10, 10, 0, 10];
            const centroid = SiteplanTiketOverlay.calculateCentroid(points);
            expect(centroid.x).toBe(5);
            expect(centroid.y).toBe(5);
        });

        test('mendukung input format string comma-separated', () => {
            const pointsStr = '10,20,30,20,30,40,10,40';
            const centroid = SiteplanTiketOverlay.calculateCentroid(pointsStr);
            expect(centroid.x).toBe(20);
            expect(centroid.y).toBe(30);
        });

        test('mengembalikan { x: 0, y: 0 } untuk input kosong atau tidak valid', () => {
            expect(SiteplanTiketOverlay.calculateCentroid([])).toEqual({ x: 0, y: 0 });
            expect(SiteplanTiketOverlay.calculateCentroid(null)).toEqual({ x: 0, y: 0 });
            expect(SiteplanTiketOverlay.calculateCentroid('abc')).toEqual({ x: 0, y: 0 });
        });
    });

    describe('createOverlayShape', () => {
        test('membuat Konva.Line dengan border putih kontras dan atribut yang sesuai', () => {
            const shape = SiteplanTiketOverlay.createOverlayShape(Konva, {
                idKavling: 42,
                points: [0, 0, 10, 0, 10, 10, 0, 10],
                prioritas: 'urgent',
                phaseOffset: 1.5
            });

            expect(shape.attrs.id).toBe('tiket-overlay-kav42');
            expect(shape.attrs.name).toBe('siteplan-tiket-overlay-shape');
            expect(shape.attrs.points).toEqual([0, 0, 10, 0, 10, 10, 0, 10]);
            expect(shape.attrs.fill).toBe('#ea5455');
            // Opsi 1: border putih kontras
            expect(shape.attrs.stroke).toBe('#ffffff');
            expect(shape.attrs.strokeWidth).toBe(1.5);
            expect(shape.attrs.opacity).toBe(0);
            expect(shape.attrs.listening).toBe(false);
            expect(shape.attrs.perfectDrawEnabled).toBe(false);
            expect(shape.idKavling).toBe(42);
            expect(shape.prioritas).toBe('urgent');
            expect(shape.phaseOffset).toBe(1.5);
        });

        test('dapat mem-parsing points yang berupa string JSON atau comma-separated', () => {
            const shapeJson = SiteplanTiketOverlay.createOverlayShape(Konva, {
                idKavling: 10,
                points: '1,2,3,4',
                prioritas: 'medium'
            });
            expect(shapeJson.attrs.points).toEqual([1, 2, 3, 4]);
            expect(shapeJson.attrs.fill).toBe('#ff9f43');
            expect(shapeJson.attrs.stroke).toBe('#ffffff');
        });

        test('membuat overlay shape untuk item others dengan targetId others12 dan id tiket-overlay-others12', () => {
            const shape = SiteplanTiketOverlay.createOverlayShape(Konva, {
                refType: 'others',
                id: 12,
                points: [0, 0, 50, 0, 50, 50, 0, 50],
                prioritas: 'medium',
                phaseOffset: 0.8
            });

            expect(shape.attrs.id).toBe('tiket-overlay-others12');
            expect(shape.attrs.refType).toBe('others');
            expect(shape.attrs.refId).toBe(12);
            expect(shape.attrs.targetId).toBe('others12');
            expect(shape.targetId).toBe('others12');
            expect(shape.refType).toBe('others');
            expect(shape.refId).toBe(12);
            expect(shape.attrs.fill).toBe('#ff9f43');
        });
    });

    describe('createBadgeShape', () => {
        test('membuat Konva.Shape di koordinat centroid dengan sceneFunc menggambar pin tanda seru', () => {
            const points = [0, 0, 20, 0, 20, 20, 0, 20];
            const badge = SiteplanTiketOverlay.createBadgeShape(Konva, {
                idKavling: 55,
                points: points,
                prioritas: 'urgent',
                phaseOffset: 0.5
            });

            expect(badge.attrs.id).toBe('tiket-badge-kav55');
            expect(badge.attrs.name).toBe('siteplan-tiket-badge-shape');
            expect(badge.attrs.x).toBe(10);
            expect(badge.attrs.y).toBe(10);
            expect(badge.attrs.listening).toBe(false);
            expect(badge.attrs.perfectDrawEnabled).toBe(false);
            expect(badge.idKavling).toBe(55);
            expect(badge.prioritas).toBe('urgent');
            expect(badge.phaseOffset).toBe(0.5);
            expect(typeof badge.attrs.sceneFunc).toBe('function');

            // Uji pemanggilan sceneFunc dengan mock context
            const mockContext = {
                beginPath: jest.fn(),
                arc: jest.fn(),
                closePath: jest.fn(),
                fill: jest.fn(),
                stroke: jest.fn(),
                fillText: jest.fn()
            };
            badge.attrs.sceneFunc(mockContext, badge);
            expect(mockContext.beginPath).toHaveBeenCalled();
            expect(mockContext.arc).toHaveBeenCalledWith(0, 0, 8, 0, Math.PI * 2, false);
            expect(mockContext.fill).toHaveBeenCalled();
            expect(mockContext.stroke).toHaveBeenCalled();
            expect(mockContext.fillText).toHaveBeenCalledWith('!', 0, 0.5);
        });

        test('membuat badge shape untuk item others dengan targetId others12 dan id tiket-badge-others12', () => {
            const points = [0, 0, 40, 0, 40, 40, 0, 40];
            const badge = SiteplanTiketOverlay.createBadgeShape(Konva, {
                refType: 'others',
                id: 12,
                points: points,
                prioritas: 'urgent',
                phaseOffset: 0.2
            });

            expect(badge.attrs.id).toBe('tiket-badge-others12');
            expect(badge.attrs.refType).toBe('others');
            expect(badge.attrs.refId).toBe(12);
            expect(badge.attrs.targetId).toBe('others12');
            expect(badge.targetId).toBe('others12');
            expect(badge.refType).toBe('others');
            expect(badge.refId).toBe(12);
            expect(badge.attrs.x).toBe(20);
            expect(badge.attrs.y).toBe(20);
        });
    });

    describe('createAnimation', () => {
        test('melakukan frame throttling capping 30 FPS', () => {
            const layer = new MockLayer();
            const anim = SiteplanTiketOverlay.createAnimation(Konva, layer, () => true);

            // Frame 1 pada time: 0 -> harus dijalankan
            const res1 = anim._anim.step({ time: 0 });
            expect(res1).not.toBe(false);

            // Frame 2 pada time: 16ms (< 33.33ms interval) -> harus di-skip (return false)
            const res2 = anim._anim.step({ time: 16 });
            expect(res2).toBe(false);

            // Frame 3 pada time: 35ms (>= 33.33ms interval) -> harus dijalankan
            const res3 = anim._anim.step({ time: 35 });
            expect(res3).not.toBe(false);
        });

        test('memperbarui opacity & pulsing strokeWidth poligon, serta scale badge', () => {
            const layer = new MockLayer();
            const shape1 = SiteplanTiketOverlay.createOverlayShape(Konva, {
                idKavling: 1,
                points: [0, 0, 10, 10],
                prioritas: 'urgent',
                phaseOffset: 0
            });
            const badge1 = SiteplanTiketOverlay.createBadgeShape(Konva, {
                idKavling: 1,
                points: [0, 0, 10, 10],
                prioritas: 'urgent',
                phaseOffset: 0
            });
            layer.add(shape1, badge1);

            const anim = SiteplanTiketOverlay.createAnimation(Konva, layer, () => true);
            anim.start();

            // Puncak gelombang pada cycle / 4 (700ms)
            anim._anim.step({ time: 700 });

            // Opacity puncak sinus = 1.0
            expect(shape1.opacity()).toBeCloseTo(1.0, 4);
            // Opsi 1: strokeWidth berdenyut (1.0 + 2.5 * 1.0 = 3.5px)
            expect(shape1.strokeWidth()).toBeCloseTo(3.5, 4);
            // Opsi 2: badge scale membesar (0.85 + 0.3 * 1.0 = 1.15)
            expect(badge1.scaleX()).toBeCloseTo(1.15, 4);
            expect(badge1.scaleY()).toBeCloseTo(1.15, 4);

            // Lembah gelombang pada 3 * cycle / 4 (2100ms)
            anim._anim.step({ time: 2100 });
            expect(shape1.opacity()).toBeCloseTo(0.0, 4);
            expect(shape1.strokeWidth()).toBeCloseTo(1.0, 4);
            expect(badge1.scaleX()).toBeCloseTo(0.85, 4);

            anim.stop();
        });

        test('menghormati isBaseVisibleCallback untuk poligon dan badge', () => {
            const layer = new MockLayer();
            const shape1 = SiteplanTiketOverlay.createOverlayShape(Konva, {
                idKavling: 1,
                points: [0, 0, 10, 10],
                prioritas: 'urgent',
                phaseOffset: 0
            });
            const badge1 = SiteplanTiketOverlay.createBadgeShape(Konva, {
                idKavling: 1,
                points: [0, 0, 10, 10],
                prioritas: 'urgent',
                phaseOffset: 0
            });
            layer.add(shape1, badge1);

            let isVisible = false;
            const anim = SiteplanTiketOverlay.createAnimation(Konva, layer, () => isVisible);

            anim._anim.step({ time: 100 });
            expect(shape1.visible()).toBe(false);
            expect(badge1.visible()).toBe(false);

            isVisible = true;
            anim._anim.step({ time: 200 });
            expect(shape1.visible()).toBe(true);
            expect(badge1.visible()).toBe(true);
        });

        test('memeriksa visibilitas menggunakan targetId untuk kavling (kavX) dan others (othersX)', () => {
            const layer = new MockLayer();
            const shapeKav = SiteplanTiketOverlay.createOverlayShape(Konva, {
                idKavling: 101,
                points: [0, 0, 10, 10],
                prioritas: 'urgent'
            });
            const shapeOthers = SiteplanTiketOverlay.createOverlayShape(Konva, {
                refType: 'others',
                id: 12,
                points: [20, 20, 30, 30],
                prioritas: 'normal'
            });
            layer.add(shapeKav, shapeOthers);

            const visibleMap = {
                kav101: true,
                others12: false
            };
            const anim = SiteplanTiketOverlay.createAnimation(Konva, layer, (targetId) => Boolean(visibleMap[targetId]));

            anim._anim.step({ time: 100 });
            expect(shapeKav.visible()).toBe(true);
            expect(shapeOthers.visible()).toBe(false);
        });
    });

    describe('buildOverlayShapes', () => {
        test('membuat overlay poligon dan badge tanda seru untuk kavling bermasalah', () => {
            const layer = new MockLayer();
            const kavlingList = [
                { id_kavling: 101, points: [0, 0, 10, 10], active_tiket_id: 11, active_tiket_prioritas: 'urgent' },
                { id_kavling: 102, points: [10, 0, 20, 10], active_tiket_id: 12, active_tiket_prioritas: 'medium' },
                { id_kavling: 103, points: [20, 0, 30, 10], active_tiket_id: null, active_tiket_prioritas: null },
                { id_kavling: 104, points: [30, 0, 40, 10], active_tiket_id: 14, active_tiket_prioritas: 'low' }
            ];

            const shapes = SiteplanTiketOverlay.buildOverlayShapes(Konva, layer, kavlingList, null, () => true);

            // 3 kavling bermasalah x 2 (overlay + badge) = 6 shapes
            expect(shapes).toHaveLength(6);
            expect(layer.children).toHaveLength(6);

            const overlayShapes = layer.find('.siteplan-tiket-overlay-shape');
            const badgeShapes = layer.find('.siteplan-tiket-badge-shape');

            expect(overlayShapes).toHaveLength(3);
            expect(badgeShapes).toHaveLength(3);

            // Phase offset bertahap (index * (2pi / 3))
            expect(overlayShapes[0].phaseOffset).toBeCloseTo(0, 4);
            expect(overlayShapes[1].phaseOffset).toBeCloseTo((2 * Math.PI) / 3, 4);
            expect(overlayShapes[2].phaseOffset).toBeCloseTo((4 * Math.PI) / 3, 4);

            expect(badgeShapes[0].phaseOffset).toBeCloseTo(0, 4);
            expect(badgeShapes[1].phaseOffset).toBeCloseTo((2 * Math.PI) / 3, 4);
            expect(badgeShapes[2].phaseOffset).toBeCloseTo((4 * Math.PI) / 3, 4);

            // Animasi otomatis berjalan
            expect(layer._tiketAnimation).toBeDefined();
            expect(layer._tiketAnimation.isRunning()).toBe(true);
        });

        test('membuat overlay dan badge untuk campuran item kavling dan others', () => {
            const layer = new MockLayer();
            const mixedList = [
                { id_kavling: 101, points: [0, 0, 10, 10], active_tiket_id: 1, active_tiket_prioritas: 'urgent' },
                { id: 12, tipe: 'jalan', points: [20, 0, 40, 10], active_tiket_id: 2, active_tiket_prioritas: 'medium' },
                { id: 13, tipe: 'fasos', points: [40, 0, 60, 10], active_tiket_id: null }, // no active tiket
                { id_kavling: 102, points: [60, 0, 80, 10], active_tiket_id: 3, active_tiket_prioritas: 'low' }
            ];

            const shapes = SiteplanTiketOverlay.buildOverlayShapes(Konva, layer, mixedList, null, () => true);

            // 3 problem items (2 kavling + 1 others) x 2 shapes = 6 shapes
            expect(shapes).toHaveLength(6);
            expect(layer.children).toHaveLength(6);

            const overlayShapes = layer.find('.siteplan-tiket-overlay-shape');
            const badgeShapes = layer.find('.siteplan-tiket-badge-shape');

            expect(overlayShapes).toHaveLength(3);
            expect(badgeShapes).toHaveLength(3);

            // Periksa item kavling pertama
            expect(overlayShapes[0].attrs.id).toBe('tiket-overlay-kav101');
            expect(overlayShapes[0].attrs.targetId).toBe('kav101');
            expect(badgeShapes[0].attrs.id).toBe('tiket-badge-kav101');
            expect(badgeShapes[0].attrs.targetId).toBe('kav101');

            // Periksa item others
            expect(overlayShapes[1].attrs.id).toBe('tiket-overlay-others12');
            expect(overlayShapes[1].attrs.targetId).toBe('others12');
            expect(overlayShapes[1].attrs.refType).toBe('others');
            expect(overlayShapes[1].attrs.refId).toBe(12);
            expect(badgeShapes[1].attrs.id).toBe('tiket-badge-others12');
            expect(badgeShapes[1].attrs.targetId).toBe('others12');
            expect(badgeShapes[1].attrs.refType).toBe('others');
            expect(badgeShapes[1].attrs.refId).toBe(12);

            // Periksa item kavling kedua
            expect(overlayShapes[2].attrs.id).toBe('tiket-overlay-kav102');
            expect(overlayShapes[2].attrs.targetId).toBe('kav102');
            expect(badgeShapes[2].attrs.id).toBe('tiket-badge-kav102');
            expect(badgeShapes[2].attrs.targetId).toBe('kav102');

            expect(layer._tiketAnimation).toBeDefined();
            expect(layer._tiketAnimation.isRunning()).toBe(true);
        });

        test('membersihkan layer dan menghentikan animasi jika tidak ada kavling bermasalah', () => {
            const layer = new MockLayer();
            // Jalankan dulu dengan satu kavling
            SiteplanTiketOverlay.buildOverlayShapes(Konva, layer, [
                { id_kavling: 101, points: [0, 0], active_tiket_id: 1, active_tiket_prioritas: 'urgent' }
            ]);
            // 1 overlay + 1 badge = 2
            expect(layer.children).toHaveLength(2);
            const oldAnim = layer._tiketAnimation;
            expect(oldAnim.isRunning()).toBe(true);

            // Panggil lagi dengan list tanpa kavling bermasalah
            const emptyShapes = SiteplanTiketOverlay.buildOverlayShapes(Konva, layer, []);
            expect(emptyShapes).toHaveLength(0);
            expect(layer.children).toHaveLength(0);
            expect(oldAnim.isRunning()).toBe(false);
        });
    });

    describe('clearOverlay', () => {
        test('menghentikan animasi dan menghapus seluruh child pada layer', () => {
            const layer = new MockLayer();
            SiteplanTiketOverlay.buildOverlayShapes(Konva, layer, [
                { id_kavling: 201, points: [0, 0], active_tiket_id: 1, active_tiket_prioritas: 'urgent' }
            ]);

            const anim = layer._tiketAnimation;
            expect(anim.isRunning()).toBe(true);

            SiteplanTiketOverlay.clearOverlay(layer);
            expect(anim.isRunning()).toBe(false);
            expect(layer.children).toHaveLength(0);
            expect(layer._tiketAnimation).toBeNull();
        });
    });
});
