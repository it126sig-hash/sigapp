const SiteplanCompositeShape = require('../../public/assets/js/siteplan/composite-shape.js');

const square = [0, 0, 90, 0, 90, 120, 0, 120];
const color = (name) => name;

describe('Siteplan composite shape paint plan', () => {
    test('membagi shape menjadi tiga atau empat baris secara dinamis', () => {
        const three = SiteplanCompositeShape.buildPaintPlan(square, [
            { key: 'a', segments: [{ config_name: 'A', ratio: 1 }] },
            { key: 'b', segments: [{ config_name: 'B', ratio: 1 }] },
            { key: 'c', segments: [{ config_name: 'C', ratio: 1 }] }
        ], 90, color);
        const four = SiteplanCompositeShape.buildPaintPlan(square, [
            { key: 'a', segments: [{ config_name: 'A', ratio: 1 }] },
            { key: 'b', segments: [{ config_name: 'B', ratio: 1 }] },
            { key: 'c', segments: [{ config_name: 'C', ratio: 1 }] },
            { key: 'd', segments: [{ config_name: 'D', ratio: 1 }] }
        ], 90, color);

        expect(three.rows).toHaveLength(3);
        expect(four.rows).toHaveLength(4);
    });

    test('pencairan 30 persen mengisi segmen kiri lebih dulu', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(square, [{
            key: 'keuangan',
            segments: [
                { config_name: 'Cair', ratio: 0.3 },
                { config_name: 'Pengajuan', ratio: 0.7 }
            ]
        }], 90, color);
        const cair = plan.rows[0].segments[0].points;
        const pengajuan = plan.rows[0].segments[1].points;

        expect(cair[0].x).toBeCloseTo(0);
        expect(cair[1].x).toBeCloseTo(27);
        expect(pengajuan[0].x).toBeCloseTo(27);
        expect(pengajuan[1].x).toBeCloseTo(90);
    });

    test('marker berada di tengah baris dan rasio invalid dinormalisasi', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(square, [{
            key: 'produksi',
            segments: [{ config_name: 'Pembangunan', ratio: 5 }],
            markers: [{ config_name: 'Perintah Bangun', position: 0.5 }]
        }], 90, color);

        expect(plan.rows[0].segments[0].ratio).toBe(1);
        expect(plan.rows[0].markers).toHaveLength(1);
        const markerY = plan.rows[0].markers[0].points.map((point) => point.y);
        expect(Math.min(...markerY)).toBeLessThan(60);
        expect(Math.max(...markerY)).toBeGreaterThan(60);
    });

    test('ketebalan marker mengikuti konfigurasi strokeWidth', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(
            square,
            [{
                key: 'produksi',
                segments: [{ config_name: 'Pembangunan', ratio: 1 }],
                markers: [{ config_name: 'Perintah Bangun', position: 0.5 }]
            }],
            90,
            color,
            (name) => name === 'Perintah Bangun' ? 12 : 0
        );

        const markerY = plan.rows[0].markers[0].points.map((point) => point.y);
        expect(Math.max(...markerY) - Math.min(...markerY)).toBeCloseTo(12);
    });

    test('polygon tanpa rotation tetap menghasilkan paint plan valid', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(
            [0, 0, 80, 10, 70, 100, 5, 90],
            [{ key: 'mkdt', segments: [{ config_name: 'Booking', ratio: 1 }] }],
            null,
            color
        );

        expect(plan.rows[0].segments[0].points).toHaveLength(4);
        expect(plan.bounds.minX).toBe(0);
    });

    test('label kavling berada di pusat OBB dan mengikuti rotasi upright', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(
            square,
            [{ key: 'mkdt', segments: [{ config_name: 'Booking', ratio: 1 }] }],
            270,
            color,
            undefined,
            '27'
        );

        expect(plan.label.text).toBe('27');
        expect(plan.label.x).toBeCloseTo(45);
        expect(plan.label.y).toBeCloseTo(60);
        expect(plan.label.angle).toBeCloseTo(0);
        expect(plan.label.fontSize).toBeGreaterThan(0);
    });

    test('label kosong tidak menghasilkan rencana label', () => {
        const plan = SiteplanCompositeShape.buildPaintPlan(
            square,
            [{ key: 'mkdt', segments: [{ config_name: 'Booking', ratio: 1 }] }],
            null,
            color,
            undefined,
            ' '
        );

        expect(plan.label).toBeNull();
    });

    test('facade rotation hanya mengatur layout dan tidak merotasi node Konva', () => {
        class Shape {
            constructor(config) {
                this.config = config;
            }
        }

        const shape = SiteplanCompositeShape.createKonvaShape({ Shape }, {
            points: square,
            facadeRotation: 45,
            visualRows: [{ key: 'mkdt', segments: [{ config_name: 'Booking', ratio: 1 }] }]
        }, color);

        expect(shape.config.rotation).toBeUndefined();
        expect(shape.config.name).toContain('siteplan-kavling');
    });

    test('hitFunc menggambar satu polygon sederhana untuk seluruh kavling', () => {
        class Shape {
            constructor(config) {
                this.config = config;
            }
        }

        const shape = SiteplanCompositeShape.createKonvaShape({ Shape }, {
            points: square,
            visualRows: [{ key: 'mkdt', segments: [{ config_name: 'Booking', ratio: 1 }] }]
        }, color);
        const context = {
            beginPath: jest.fn(),
            moveTo: jest.fn(),
            lineTo: jest.fn(),
            closePath: jest.fn(),
            fillStrokeShape: jest.fn()
        };
        const node = {};

        shape.config.hitFunc(context, node);

        expect(context.beginPath).toHaveBeenCalledTimes(1);
        expect(context.lineTo).toHaveBeenCalledTimes(3);
        expect(context.fillStrokeShape).toHaveBeenCalledWith(node);
    });

    test('marker digambar source-over setelah segmen warna dasar', () => {
        class Shape {
            constructor(config) {
                this.config = config;
            }
        }

        const shape = SiteplanCompositeShape.createKonvaShape({ Shape }, {
            points: square,
            data: { no_kavling: '27' },
            visualRows: [{
                key: 'produksi',
                segments: [{ config_name: 'Pembangunan', ratio: 1 }],
                markers: [{ config_name: 'Perintah Bangun', position: 0.5 }]
            }]
        }, color);
        const modes = [];
        const nativeContext = {
            save: jest.fn(),
            restore: jest.fn(),
            beginPath: jest.fn(),
            moveTo: jest.fn(),
            lineTo: jest.fn(),
            closePath: jest.fn(),
            clip: jest.fn(),
            fill: jest.fn(),
            fillText: jest.fn(),
            strokeText: jest.fn(),
            translate: jest.fn(),
            rotate: jest.fn(),
            stroke: jest.fn(),
            strokeStyle: '#000',
            lineWidth: 0
        };
        let compositeMode = 'multiply';
        Object.defineProperty(nativeContext, 'globalCompositeOperation', {
            get: () => compositeMode,
            set: (value) => {
                compositeMode = value;
                modes.push(value);
            }
        });

        shape.config.sceneFunc({ _context: nativeContext }, {
            stroke: () => '#000',
            strokeWidth: () => 0
        });

        expect(modes).toContain('source-over');
        expect(nativeContext.fill).toHaveBeenCalledTimes(2);
        expect(nativeContext.strokeText).toHaveBeenCalledWith('27', 0, 0);
        expect(nativeContext.fillText).toHaveBeenCalledWith('27', 0, 0);
        expect(nativeContext.fill.mock.invocationCallOrder[1])
            .toBeLessThan(nativeContext.strokeText.mock.invocationCallOrder[0]);
        expect(nativeContext.strokeText.mock.invocationCallOrder[0])
            .toBeLessThan(nativeContext.fillText.mock.invocationCallOrder[0]);
    });
});
