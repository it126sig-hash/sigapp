const SiteplanExportHelper = require('../../public/assets/js/siteplan/export-helper.js');

describe('SiteplanExportHelper', () => {
    test('clampCropRect membatasi koordinat dan ukuran dalam dimensi gambar siteplan', () => {
        const clamped = SiteplanExportHelper.clampCropRect(
            { x: -50, y: 120, width: 5000, height: 400 },
            2000,
            1500,
            60
        );

        expect(clamped).toEqual({
            x: 0,
            y: 120,
            width: 2000,
            height: 400
        });
    });

    test('computeEffectivePixelRatio menaikkan resolusi vektor pada area kecil dan menjaga batas aman memori pada gambar besar', () => {
        const smallViewport = SiteplanExportHelper.computeEffectivePixelRatio(900, 600, 'hd');
        expect(smallViewport.pixelRatio).toBeGreaterThanOrEqual(3);
        expect(smallViewport.outputWidth).toBeGreaterThanOrEqual(2700);
        expect(smallViewport.capped).toBe(false);

        const hugeFullSiteplan = SiteplanExportHelper.computeEffectivePixelRatio(5000, 3535, 'ultra');
        expect(hugeFullSiteplan.outputWidth).toBeLessThanOrEqual(SiteplanExportHelper.MAX_CANVAS_DIMENSION);
        expect(hugeFullSiteplan.outputHeight).toBeLessThanOrEqual(SiteplanExportHelper.MAX_CANVAS_DIMENSION);
        expect(hugeFullSiteplan.outputWidth * hugeFullSiteplan.outputHeight).toBeLessThanOrEqual(
            SiteplanExportHelper.MAX_CANVAS_PIXELS
        );
        expect(hugeFullSiteplan.pixelRatio).toBeGreaterThan(1);
        expect(hugeFullSiteplan.capped).toBe(true);
    });

    test('computePdfPageLayout memilih orientasi otomatis dan menghitung box proporsional', () => {
        const landscapeA3 = SiteplanExportHelper.computePdfPageLayout(3000, 1500, 'a3', 'auto');
        expect(landscapeA3.orientation).toBe('landscape');
        expect(landscapeA3.pageWidthPt).toBeGreaterThan(landscapeA3.pageHeightPt);
        expect(landscapeA3.drawWidthPt / landscapeA3.drawHeightPt).toBeCloseTo(2, 2);

        const portraitA4 = SiteplanExportHelper.computePdfPageLayout(3000, 1500, 'a4', 'portrait');
        expect(portraitA4.orientation).toBe('portrait');
        expect(portraitA4.pageHeightPt).toBeGreaterThan(portraitA4.pageWidthPt);

        const fitLayout = SiteplanExportHelper.computePdfPageLayout(2400, 1600, 'fit', 'auto');
        expect(fitLayout.drawXPt).toBe(0);
        expect(fitLayout.drawYPt).toBe(0);
        expect(fitLayout.pageWidthPt / fitLayout.pageHeightPt).toBeCloseTo(1.5, 2);
    });

    test('buildPdfFromJpegBytes menghasilkan dokumen PDF 1.4 valid dengan xref dan EOF', () => {
        const fakeJpeg = new Uint8Array([0xff, 0xd8, 0xff, 0xe0, 0x00, 0x10, 0xff, 0xd9]);
        const layout = SiteplanExportHelper.computePdfPageLayout(1600, 900, 'a3', 'landscape');
        const pdfBytes = SiteplanExportHelper.buildPdfFromJpegBytes(fakeJpeg, 1600, 900, layout);
        const text = Buffer.from(pdfBytes).toString('latin1');

        expect(text.startsWith('%PDF-1.4\n')).toBe(true);
        expect(text).toContain('/Subtype /Image');
        expect(text).toContain('/Filter /DCTDecode');
        expect(text).toContain('/Width 1600 /Height 900');
        expect(text).toContain('xref\n0 6\n');
        expect(text.trim().endsWith('%%EOF')).toBe(true);

        // Pastikan byte offset di xref menunjuk tepat ke tiap object header
        const xrefMatch = text.match(/xref\n0 6\n0000000000 65535 f \n([\s\S]*?)trailer/);
        expect(xrefMatch).not.toBeNull();
        const entryLines = xrefMatch[1].trim().split('\n');
        expect(entryLines).toHaveLength(5);
        entryLines.forEach((line, idx) => {
            const offset = parseInt(line.slice(0, 10), 10);
            expect(text.slice(offset, offset + 7)).toBe(`${idx + 1} 0 obj`);
        });
    });
});
