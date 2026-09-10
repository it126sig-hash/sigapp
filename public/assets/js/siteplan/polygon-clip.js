/**
 * Polygon Clipping Utility (Sutherland-Hodgman Algorithm)
 * + Oriented Bounding Box (OBB) via PCA untuk deteksi orientasi otomatis
 * Digunakan untuk memotong polygon kavling menjadi beberapa bagian.
 */

const PolygonClip = (function() {

    // Helper: Convert [x1, y1, x2, y2, ...] to [{x: x1, y: y1}, ...]
    function toPointsObj(flatArray) {
        const points = [];
        for (let i = 0; i < flatArray.length; i += 2) {
            points.push({ x: flatArray[i], y: flatArray[i + 1] });
        }
        return points;
    }

    // Helper: Convert [{x, y}, ...] to [x1, y1, x2, y2, ...]
    function toFlatArray(pointsObj) {
        const flat = [];
        for (let i = 0; i < pointsObj.length; i++) {
            flat.push(pointsObj[i].x, pointsObj[i].y);
        }
        return flat;
    }

    // Get axis-aligned bounding box of a polygon
    function getBounds(pointsObj) {
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        for (let i = 0; i < pointsObj.length; i++) {
            if (pointsObj[i].x < minX) minX = pointsObj[i].x;
            if (pointsObj[i].x > maxX) maxX = pointsObj[i].x;
            if (pointsObj[i].y < minY) minY = pointsObj[i].y;
            if (pointsObj[i].y > maxY) maxY = pointsObj[i].y;
        }
        return { minX, minY, maxX, maxY };
    }

    // Hitung centroid (titik pusat) polygon
    function getCentroid(points) {
        let sx = 0, sy = 0;
        for (let i = 0; i < points.length; i++) {
            sx += points[i].x;
            sy += points[i].y;
        }
        return { x: sx / points.length, y: sy / points.length };
    }

    // Rotasi array of {x,y} terhadap titik pusat (center) sebesar angle (radian)
    function rotatePoints(points, angle, center) {
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        return points.map(p => {
            const dx = p.x - center.x;
            const dy = p.y - center.y;
            return {
                x: center.x + dx * cos - dy * sin,
                y: center.y + dx * sin + dy * cos
            };
        });
    }

    // Deteksi orientasi polygon menggunakan PCA (Principal Component Analysis)
    // Mengembalikan sudut (radian) sumbu utama polygon
    function getOrientation(points) {
        const c = getCentroid(points);
        let cov_xx = 0, cov_xy = 0, cov_yy = 0;

        for (let i = 0; i < points.length; i++) {
            const dx = points[i].x - c.x;
            const dy = points[i].y - c.y;
            cov_xx += dx * dx;
            cov_xy += dx * dy;
            cov_yy += dy * dy;
        }

        // Sudut eigenvector utama dari covariance matrix 2x2
        const angle = 0.5 * Math.atan2(2 * cov_xy, cov_xx - cov_yy);

        // Pastikan setelah rotasi, sumbu panjang selalu vertikal (portrait)
        // agar pemotongan horizontal selalu memotong sisi pendek
        const rotated = rotatePoints(points, -angle, c);
        const bounds = getBounds(rotated);
        const width = bounds.maxX - bounds.minX;
        const height = bounds.maxY - bounds.minY;

        if (width > height) {
            return angle + Math.PI / 2;
        }
        return angle;
    }

    // Sutherland-Hodgman Polygon Clipping
    function clipPolygon(subjectPolygon, clipPoly) {
        let outputList = subjectPolygon;

        for (let edge = 0; edge < clipPoly.length; edge++) {
            const edgeStart = clipPoly[edge];
            const edgeEnd = clipPoly[(edge + 1) % clipPoly.length];

            const inputList = outputList;
            outputList = [];

            if (inputList.length === 0) break;

            let S = inputList[inputList.length - 1];

            for (let i = 0; i < inputList.length; i++) {
                const E = inputList[i];

                const isInside = (p) => {
                    return (edgeEnd.x - edgeStart.x) * (p.y - edgeStart.y) - (edgeEnd.y - edgeStart.y) * (p.x - edgeStart.x) >= 0;
                };

                const computeIntersection = (p1, p2, es, ee) => {
                    const num1 = (p1.x * p2.y - p1.y * p2.x);
                    const num2 = (es.x * ee.y - es.y * ee.x);
                    const den = (p1.x - p2.x) * (es.y - ee.y) - (p1.y - p2.y) * (es.x - ee.x);

                    if (den === 0) return p1;

                    const x = (num1 * (es.x - ee.x) - (p1.x - p2.x) * num2) / den;
                    const y = (num1 * (es.y - ee.y) - (p1.y - p2.y) * num2) / den;
                    return { x, y };
                };

                if (isInside(E)) {
                    if (!isInside(S)) {
                        outputList.push(computeIntersection(S, E, edgeStart, edgeEnd));
                    }
                    outputList.push(E);
                } else if (isInside(S)) {
                    outputList.push(computeIntersection(S, E, edgeStart, edgeEnd));
                }
                S = E;
            }
        }
        return outputList;
    }

    /**
     * Membagi polygon menjadi beberapa area berdasarkan layout grid (AABB).
     * Digunakan secara internal setelah polygon sudah di-rotasi ke posisi tegak.
     */
    function splitKavlingShape(flatPoints, layout = [1, 1, 1]) {
        const poly = toPointsObj(flatPoints);
        const bounds = getBounds(poly);

        const rows = layout.length;
        const rowHeight = (bounds.maxY - bounds.minY) / rows;
        const results = [];

        for (let r = 0; r < rows; r++) {
            const cols = layout[r];
            const colWidth = (bounds.maxX - bounds.minX) / cols;

            const rectMinY = bounds.minY + r * rowHeight;
            const rectMaxY = bounds.minY + (r + 1) * rowHeight;

            for (let c = 0; c < cols; c++) {
                const rectMinX = bounds.minX + c * colWidth;
                const rectMaxX = bounds.minX + (c + 1) * colWidth;

                const clipRect = [
                    { x: rectMinX, y: rectMinY },
                    { x: rectMaxX, y: rectMinY },
                    { x: rectMaxX, y: rectMaxY },
                    { x: rectMinX, y: rectMaxY }
                ];

                const clipped = clipPolygon(poly, clipRect);
                if (clipped.length > 2) {
                    results.push(toFlatArray(clipped));
                } else {
                    results.push([]);
                }
            }
        }

        return results;
    }

    /**
     * Algoritma Monotone Chain Convex Hull
     */
    function convexHull(points) {
        if (points.length <= 3) return points.slice();
        
        points.sort((a, b) => a.x !== b.x ? a.x - b.x : a.y - b.y);

        const cross = (o, a, b) => (a.x - o.x) * (b.y - o.y) - (a.y - o.y) * (b.x - o.x);

        const lower = [];
        for (let i = 0; i < points.length; i++) {
            while (lower.length >= 2 && cross(lower[lower.length - 2], lower[lower.length - 1], points[i]) <= 0) {
                lower.pop();
            }
            lower.push(points[i]);
        }

        const upper = [];
        for (let i = points.length - 1; i >= 0; i--) {
            while (upper.length >= 2 && cross(upper[upper.length - 2], upper[upper.length - 1], points[i]) <= 0) {
                upper.pop();
            }
            upper.push(points[i]);
        }

        upper.pop();
        lower.pop();
        return lower.concat(upper);
    }

    /**
     * Compute Minimum Area Bounding Rectangle (MABR)
     * Mengembalikan 4 titik dari persegi panjang terkecil dan sudut kemiringannya.
     */
    function computeMABR(flatPoints) {
        const points = toPointsObj(flatPoints);
        const hull = convexHull(points);
        const centroid = getCentroid(points);
        
        let minArea = Infinity, bestRect = null, bestAngle = 0;
        
        for (let i = 0; i < hull.length; i++) {
            const edgeFrom = hull[i];
            const edgeTo = hull[(i + 1) % hull.length];
            const angle = Math.atan2(edgeTo.y - edgeFrom.y, edgeTo.x - edgeFrom.x);
            
            const rotated = rotatePoints(hull, -angle, centroid);
            const bounds = getBounds(rotated);
            const area = (bounds.maxX - bounds.minX) * (bounds.maxY - bounds.minY);
            
            if (area < minArea) {
                minArea = area;
                bestAngle = angle;
                bestRect = bounds;
            }
        }
        
        // Buat 4 titik rect
        const rectPoints = [
            { x: bestRect.minX, y: bestRect.minY },
            { x: bestRect.maxX, y: bestRect.minY },
            { x: bestRect.maxX, y: bestRect.maxY },
            { x: bestRect.minX, y: bestRect.maxY }
        ];
        
        // Rotasi balik ke posisi asli
        const restored = rotatePoints(rectPoints, bestAngle, centroid);
        
        return { 
            points: toFlatArray(restored), 
            angle: bestAngle * 180 / Math.PI // Dalam derajat
        };
    }

    /**
     * Membagi polygon dengan deteksi orientasi otomatis (OBB) atau explicit rotation.
     *
     * @param {Array} flatPoints - Array koordinat [x1, y1, x2, y2, ...]
     * @param {Array} layout - Jumlah kolom per baris. Contoh: [1, 1, 1] = 3 baris masing-masing 1 kolom.
     * @param {Number|null} rotationDeg - Derajat rotasi manual dari database. Null = auto detect.
     * @returns {Array} Array of flat polygon arrays yang sudah di-clip.
     */
    function splitKavlingShapeOBB(flatPoints, layout = [1, 1, 1], rotationDeg = null) {
        const poly = toPointsObj(flatPoints);
        const centroid = getCentroid(poly);
        
        let angle;
        if (rotationDeg !== null && rotationDeg !== undefined && rotationDeg !== '') {
            let arrowAngle = parseFloat(rotationDeg) * Math.PI / 180;
            // The arrow points to the facade. We want to align the facade to the BOTTOM (+Y axis, which is PI/2 or 90 deg).
            // So we subtract PI/2 from the arrow angle to get the rotation offset needed to make the facade face down.
            angle = arrowAngle - Math.PI / 2;
            // User rotation explicit, do not force portrait.
        } else {
            angle = getOrientation(poly);
        }

        // 2. Rotasi semua titik ke posisi tegak (counter-rotate)
        const rotated = rotatePoints(poly, -angle, centroid);
        const rotatedFlat = toFlatArray(rotated);

        // 3. Potong dengan grid AABB (sekarang sudah tegak)
        const clippedSections = splitKavlingShape(rotatedFlat, layout);

        // 4. Rotasi balik setiap potongan ke posisi asli
        return clippedSections.map(section => {
            if (section.length === 0) return [];
            const sectionPoly = toPointsObj(section);
            const restored = rotatePoints(sectionPoly, angle, centroid);
            return toFlatArray(restored);
        });
    }

    return {
        splitKavlingShape: splitKavlingShape,
        splitKavlingShapeOBB: splitKavlingShapeOBB,
        clipPolygon: clipPolygon,
        getBounds: getBounds,
        getCentroid: getCentroid,
        getOrientation: getOrientation,
        rotatePoints: rotatePoints,
        toPointsObj: toPointsObj,
        toFlatArray: toFlatArray,
        convexHull: convexHull,
        computeMABR: computeMABR
    };

})();
