(function(root, factory) {
    const api = factory();

    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }

    root.SiteplanExportHelper = api;
})(typeof globalThis !== 'undefined' ? globalThis : this, function() {
    'use strict';

    const MAX_CANVAS_DIMENSION = 7680;
    const MAX_CANVAS_PIXELS = 48000000;

    const QUALITY_PRESETS = {
        standard: { scale: 2, minLongEdge: 2200, maxScale: 3.5, jpegQuality: 0.90, label: 'Standar (2x Vektor)' },
        hd: { scale: 3, minLongEdge: 3200, maxScale: 4.5, jpegQuality: 0.94, label: 'HD (3x Vektor)' },
        ultra: { scale: 4, minLongEdge: 4400, maxScale: 5.5, jpegQuality: 0.96, label: 'Ultra HD (4x Vektor)' }
    };

    const PAPER_SIZES_PT = {
        a4: { width: 595.28, height: 841.89, label: 'A4' },
        a3: { width: 841.89, height: 1190.55, label: 'A3' }
    };

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function sanitizeFilenamePart(value) {
        return String(value || 'Siteplan')
            .replace(/[\\/:*?"<>|]+/g, '-')
            .replace(/\s+/g, ' ')
            .trim() || 'Siteplan';
    }

    function clampCropRect(rect, imageWidth, imageHeight, minSize) {
        const imgW = Math.max(1, Number(imageWidth) || 1);
        const imgH = Math.max(1, Number(imageHeight) || 1);
        const minSide = Math.max(1, Math.min(Number(minSize) || 40, imgW, imgH));

        const rawX = Number(rect && rect.x);
        const rawY = Number(rect && rect.y);
        const rawW = Number(rect && rect.width);
        const rawH = Number(rect && rect.height);

        let w = Number.isFinite(rawW) && rawW > 0 ? clamp(rawW, minSide, imgW) : imgW;
        let h = Number.isFinite(rawH) && rawH > 0 ? clamp(rawH, minSide, imgH) : imgH;
        let x = Number.isFinite(rawX) ? clamp(rawX, 0, Math.max(0, imgW - w)) : 0;
        let y = Number.isFinite(rawY) ? clamp(rawY, 0, Math.max(0, imgH - h)) : 0;

        x = Math.round(x);
        y = Math.round(y);
        w = Math.max(1, Math.min(Math.round(w), imgW - x));
        h = Math.max(1, Math.min(Math.round(h), imgH - y));

        return { x: x, y: y, width: w, height: h };
    }

    function computeEffectivePixelRatio(cropWidth, cropHeight, qualityKey) {
        const preset = QUALITY_PRESETS[qualityKey] || QUALITY_PRESETS.hd;
        const w = Math.max(1, Number(cropWidth) || 1);
        const h = Math.max(1, Number(cropHeight) || 1);
        const longEdge = Math.max(w, h);

        const targetByLongEdge = longEdge < preset.minLongEdge
            ? Math.min(preset.maxScale, preset.minLongEdge / longEdge)
            : preset.scale;
        const desiredRatio = Math.max(preset.scale, targetByLongEdge);

        const maxRatioByDim = Math.min(MAX_CANVAS_DIMENSION / w, MAX_CANVAS_DIMENSION / h);
        const maxRatioByPixels = Math.sqrt(MAX_CANVAS_PIXELS / (w * h));
        const safeCeiling = Math.max(0.25, Math.min(maxRatioByDim, maxRatioByPixels));
        const effectiveRatio = Math.min(desiredRatio, safeCeiling);

        const outputWidth = Math.max(1, Math.round(w * effectiveRatio));
        const outputHeight = Math.max(1, Math.round(h * effectiveRatio));

        return {
            pixelRatio: Number(effectiveRatio.toFixed(3)),
            desiredRatio: Number(desiredRatio.toFixed(3)),
            outputWidth: outputWidth,
            outputHeight: outputHeight,
            capped: effectiveRatio + 0.001 < desiredRatio,
            jpegQuality: preset.jpegQuality
        };
    }

    function computePdfPageLayout(imageWidth, imageHeight, paperSize, orientation) {
        const imgW = Math.max(1, Number(imageWidth) || 1);
        const imgH = Math.max(1, Number(imageHeight) || 1);
        const ratio = imgW / imgH;
        const normalizedPaper = String(paperSize || 'a3').toLowerCase();
        const normalizedOrientation = String(orientation || 'auto').toLowerCase();

        if (normalizedPaper === 'fit') {
            const maxPt = PAPER_SIZES_PT.a3.height;
            const minPt = 420;
            let pageWidthPt;
            let pageHeightPt;

            if (imgW >= imgH) {
                pageWidthPt = clamp(imgW * 0.48, minPt, maxPt);
                pageHeightPt = pageWidthPt / ratio;
            } else {
                pageHeightPt = clamp(imgH * 0.48, minPt, maxPt);
                pageWidthPt = pageHeightPt * ratio;
            }

            return {
                paperSize: 'fit',
                orientation: imgW >= imgH ? 'landscape' : 'portrait',
                pageWidthPt: Number(pageWidthPt.toFixed(2)),
                pageHeightPt: Number(pageHeightPt.toFixed(2)),
                drawXPt: 0,
                drawYPt: 0,
                drawWidthPt: Number(pageWidthPt.toFixed(2)),
                drawHeightPt: Number(pageHeightPt.toFixed(2))
            };
        }

        const paper = PAPER_SIZES_PT[normalizedPaper] || PAPER_SIZES_PT.a3;
        const resolvedOrientation = normalizedOrientation === 'auto'
            ? (imgW >= imgH ? 'landscape' : 'portrait')
            : (normalizedOrientation === 'portrait' ? 'portrait' : 'landscape');

        const pageWidthPt = resolvedOrientation === 'landscape' ? paper.height : paper.width;
        const pageHeightPt = resolvedOrientation === 'landscape' ? paper.width : paper.height;
        const marginPt = 20;
        const availW = Math.max(10, pageWidthPt - marginPt * 2);
        const availH = Math.max(10, pageHeightPt - marginPt * 2);
        const fitScale = Math.min(availW / imgW, availH / imgH);
        const drawWidthPt = imgW * fitScale;
        const drawHeightPt = imgH * fitScale;
        const drawXPt = (pageWidthPt - drawWidthPt) / 2;
        const drawYPt = (pageHeightPt - drawHeightPt) / 2;

        return {
            paperSize: normalizedPaper,
            orientation: resolvedOrientation,
            pageWidthPt: Number(pageWidthPt.toFixed(2)),
            pageHeightPt: Number(pageHeightPt.toFixed(2)),
            drawXPt: Number(drawXPt.toFixed(2)),
            drawYPt: Number(drawYPt.toFixed(2)),
            drawWidthPt: Number(drawWidthPt.toFixed(2)),
            drawHeightPt: Number(drawHeightPt.toFixed(2))
        };
    }

    function encodeAsciiBytes(text) {
        const bytes = new Uint8Array(text.length);
        for (let i = 0; i < text.length; i++) {
            bytes[i] = text.charCodeAt(i) & 0xff;
        }
        return bytes;
    }

    function buildPdfFromJpegBytes(jpegBytes, imageWidth, imageHeight, layout) {
        const imgBytes = jpegBytes instanceof Uint8Array ? jpegBytes : new Uint8Array(jpegBytes || []);
        const pxW = Math.max(1, Math.round(Number(imageWidth) || 1));
        const pxH = Math.max(1, Math.round(Number(imageHeight) || 1));
        const pageLayout = layout || computePdfPageLayout(pxW, pxH, 'a3', 'auto');

        const contentStream =
            'q\n' +
            pageLayout.drawWidthPt.toFixed(2) + ' 0 0 ' + pageLayout.drawHeightPt.toFixed(2) + ' ' +
            pageLayout.drawXPt.toFixed(2) + ' ' + pageLayout.drawYPt.toFixed(2) + ' cm\n' +
            '/Im0 Do\n' +
            'Q\n';

        const chunks = [];
        let offset = 0;
        const offsets = [0];

        function pushBytes(arr) {
            chunks.push(arr);
            offset += arr.length;
        }

        function pushText(str) {
            pushBytes(encodeAsciiBytes(str));
        }

        // PDF 1.4 binary header
        pushBytes(new Uint8Array([0x25, 0x50, 0x44, 0x46, 0x2d, 0x31, 0x2e, 0x34, 0x0a, 0x25, 0xe2, 0xe3, 0xcf, 0xd3, 0x0a]));

        offsets[1] = offset;
        pushText('1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n');

        offsets[2] = offset;
        pushText('2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n');

        offsets[3] = offset;
        pushText(
            '3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' +
            pageLayout.pageWidthPt.toFixed(2) + ' ' + pageLayout.pageHeightPt.toFixed(2) +
            '] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n'
        );

        offsets[4] = offset;
        pushText(
            '4 0 obj\n<< /Type /XObject /Subtype /Image /Width ' + pxW +
            ' /Height ' + pxH +
            ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' + imgBytes.length +
            ' >>\nstream\n'
        );
        pushBytes(imgBytes);
        pushText('\nendstream\nendobj\n');

        offsets[5] = offset;
        pushText(
            '5 0 obj\n<< /Length ' + contentStream.length + ' >>\nstream\n' +
            contentStream +
            'endstream\nendobj\n'
        );

        const xrefOffset = offset;
        pushText('xref\n0 6\n0000000000 65535 f \n');
        for (let i = 1; i <= 5; i++) {
            const padded = String(offsets[i]).padStart(10, '0');
            pushText(padded + ' 00000 n \n');
        }
        pushText('trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n' + xrefOffset + '\n%%EOF\n');

        const result = new Uint8Array(offset);
        let cursor = 0;
        for (let i = 0; i < chunks.length; i++) {
            result.set(chunks[i], cursor);
            cursor += chunks[i].length;
        }
        return result;
    }

    function dataUrlToUint8Array(dataUrl) {
        const parts = String(dataUrl || '').split(',');
        const base64 = parts.length > 1 ? parts[1] : '';
        const binary = typeof atob === 'function'
            ? atob(base64)
            : Buffer.from(base64, 'base64').toString('binary');
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes;
    }

    return {
        MAX_CANVAS_DIMENSION: MAX_CANVAS_DIMENSION,
        MAX_CANVAS_PIXELS: MAX_CANVAS_PIXELS,
        QUALITY_PRESETS: QUALITY_PRESETS,
        PAPER_SIZES_PT: PAPER_SIZES_PT,
        sanitizeFilenamePart: sanitizeFilenamePart,
        clampCropRect: clampCropRect,
        computeEffectivePixelRatio: computeEffectivePixelRatio,
        computePdfPageLayout: computePdfPageLayout,
        buildPdfFromJpegBytes: buildPdfFromJpegBytes,
        dataUrlToUint8Array: dataUrlToUint8Array
    };
});
