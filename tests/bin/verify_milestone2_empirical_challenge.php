<?php

declare(strict_types=1);

/**
 * Empirical Challenge Verification Script for Milestone 2
 * Author: Challenger M2_1 (teamwork_challenger)
 */

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

$errors = [];
$passes = [];

$assertTest = function (string $description, bool $condition, string $failureDetails = '') use (&$errors, &$passes): void {
    if ($condition) {
        $passes[] = $description;
        echo "  [PASS] {$description}\n";
    } else {
        $errors[] = "{$description}: {$failureDetails}";
        echo "  [FAIL] {$description} -> {$failureDetails}\n";
    }
};

echo "====================================================================\n";
echo " CHALLENGER M2_1 EMPIRICAL AUDIT: FRONTEND UI & MODAL INTEGRATION\n";
echo "====================================================================\n";

// 1. Inspect app/Views/laporan/booking-akad.php
$viewPath = $root . '/app/Views/laporan/booking-akad.php';
$viewContent = file_get_contents($viewPath);

$assertTest(
    'View contains modal_detail_styles embed',
    str_contains($viewContent, "view('siteplan/partials/modal_detail_styles')"),
    'Missing modal_detail_styles view call in booking-akad.php'
);

$assertTest(
    'View loads flatpickr.min.js vendor script',
    str_contains($viewContent, 'flatpickr.min.js'),
    'Missing flatpickr.min.js vendor load in booking-akad.php'
);

$assertTest(
    'View includes siteplan/partials/modal_detail partial view',
    str_contains($viewContent, "view('siteplan/partials/modal_detail'"),
    'Missing modal_detail partial view inclusion in booking-akad.php'
);

$assertTest(
    'View defines window.base_url with trailing slash',
    preg_match("/window\.base_url\s*=\s*'<\?=\s*base_url\(\)\s*\?>\/'/", $viewContent) === 1,
    'window.base_url is missing or lacks trailing slash'
);

$assertTest(
    'View guards loaded global array variable across scopes',
    str_contains($viewContent, 'window.loaded = window.loaded || [];') &&
    str_contains($viewContent, "if (typeof loaded === 'undefined') var loaded = [];"),
    'loaded variable guard missing in booking-akad.php'
);

$assertTest(
    'View guards dt_proyek variable definition',
    str_contains($viewContent, "if (typeof dt_proyek === 'undefined') var dt_proyek"),
    'dt_proyek variable guard missing in booking-akad.php'
);

$assertTest(
    'View guards not_found, editdtt, csrfName, csrfHash',
    str_contains($viewContent, "if (typeof not_found === 'undefined') var not_found") &&
    str_contains($viewContent, "if (typeof editdtt === 'undefined') var editdtt = [];") &&
    str_contains($viewContent, "if (typeof csrfName === 'undefined') var csrfName") &&
    str_contains($viewContent, "if (typeof csrfHash === 'undefined') var csrfHash"),
    'One or more global variable guards missing in booking-akad.php'
);

// Table Header 7 Columns check
$assertTest(
    'Detail table header contains exactly 7 columns',
    substr_count($viewContent, '<th class=') >= 7 &&
    str_contains($viewContent, '<th class="text-center" style="width: 40px;">No</th>') &&
    str_contains($viewContent, '<th class="text-left">Kavling</th>') &&
    str_contains($viewContent, '<th class="text-left">Konsumen</th>') &&
    str_contains($viewContent, '<th class="text-center">Tanggal Booking</th>') &&
    str_contains($viewContent, '<th class="text-left">Status Terakhir</th>') &&
    str_contains($viewContent, '<th class="text-center">Tanggal Status Terakhir</th>') &&
    str_contains($viewContent, '<th class="text-center" style="width: 70px;">Aksi</th>'),
    'Detail table header does not match exact 7 columns or alignment specification'
);

// 2. Inspect public/assets/js/laporan-booking-akad.js
$jsPath = $root . '/public/assets/js/laporan-booking-akad.js';
$jsContent = file_get_contents($jsPath);

$assertTest(
    'JavaScript defines 7 categories with 6 drilldowns and 1 conversion metric',
    str_contains($jsContent, 'key: "booking"') &&
    str_contains($jsContent, 'key: "wawancara"') &&
    str_contains($jsContent, 'key: "sp3k"') &&
    str_contains($jsContent, 'key: "batal"') &&
    str_contains($jsContent, 'key: "akad_indent"') &&
    str_contains($jsContent, 'key: "akad_ajb"') &&
    str_contains($jsContent, 'key: "konversi"'),
    'JavaScript categories missing one or more required status keys'
);

$assertTest(
    'JavaScript header renderer specifies colspan="7" on year headers',
    str_contains($jsContent, '<th colspan="7" class="text-center booking-akad-year-heading'),
    'Header renderer missing colspan="7" for 7-column layout'
);

$assertTest(
    'JavaScript detail modal uses order: [[3, "asc"]] (Tanggal Booking ASC)',
    str_contains($jsContent, 'order: [[3, "asc"]]'),
    'Default sort is not set to column index 3 (Tanggal Booking ASC)'
);

$assertTest(
    'JavaScript action button emits 4 data attributes: id-kavling, id-mkdt, nama-jalan, no-kavling',
    str_contains($jsContent, 'data-id-kavling="') &&
    str_contains($jsContent, 'data-id-mkdt="') &&
    str_contains($jsContent, 'data-nama-jalan="') &&
    str_contains($jsContent, 'data-no-kavling="'),
    'Action button renderer missing required data attributes'
);

$assertTest(
    'JavaScript action click handler coerces nama-jalan and no-kavling to string',
    (str_contains($jsContent, 'String(button.data("nama-jalan") || "")') || str_contains($jsContent, "String(button.data('nama-jalan') || '')")) &&
    (str_contains($jsContent, 'String(button.data("no-kavling") || "")') || str_contains($jsContent, "String(button.data('no-kavling') || '')")),
    'Action click handler lacks safe string coercion'
);

$assertTest(
    'JavaScript action click handler initializes window.editdtt with full sh structure',
    str_contains($jsContent, 'window.editdtt = [sh]') &&
    (str_contains($jsContent, 'id: "kav" + idKavling') || str_contains($jsContent, "id: 'kav' + idKavling")) &&
    (str_contains($jsContent, 'tipe: "kavling"') || str_contains($jsContent, "tipe: 'kavling'")),
    'Action click handler fails to prepare sh structure for detail_kavling'
);

// 3. Inspect public/assets/css/laporan-booking-akad.css
$cssPath = $root . '/public/assets/css/laporan-booking-akad.css';
$cssContent = file_get_contents($cssPath);

$assertTest(
    'CSS defines min-width 1200px (single year) and 2200px (comparison)',
    str_contains($cssContent, 'min-width: 1200px;') &&
    str_contains($cssContent, 'min-width: 2200px;'),
    'CSS min-width does not support 7-column responsive widths'
);

$assertTest(
    'CSS defines semantic badge classes for all statuses',
    str_contains($cssContent, '.booking-akad-badge-booking') &&
    str_contains($cssContent, '.booking-akad-badge-wawancara') &&
    str_contains($cssContent, '.booking-akad-badge-sp3k') &&
    str_contains($cssContent, '.booking-akad-badge-batal') &&
    str_contains($cssContent, '.booking-akad-badge-akad-indent') &&
    str_contains($cssContent, '.booking-akad-badge-akad-ajb'),
    'CSS missing one or more semantic badge styles'
);

$assertTest(
    'CSS contains explicit !important text-alignment rules for R4',
    str_contains($cssContent, 'text-align: left !important;') &&
    str_contains($cssContent, 'text-align: right !important;') &&
    str_contains($cssContent, 'text-align: center !important;'),
    'CSS missing explicit alignment override classes'
);

// Summary
echo "--------------------------------------------------------------------\n";
echo "Total Verified Checks : " . (count($passes) + count($errors)) . "\n";
echo "Passed                : " . count($passes) . "\n";
echo "Failed                : " . count($errors) . "\n";

if (!empty($errors)) {
    echo "Failures:\n";
    foreach ($errors as $e) {
        echo " - {$e}\n";
    }
    exit(1);
}

echo "All empirical challenge checks PASSED perfectly!\n";
echo "====================================================================\n";
