<?php
$k = '';
$v = '';
foreach (user()->getRoles() as $key => $val) {
    $k = $key;
    $v = $val;
}

?>

<!-- BEGIN: Vendor CSS-->
<link rel="stylesheet" type="text/css"
    href="<?= base_url() ?>app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/extensions/sweetalert2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>app-assets/vendors/css/forms/select/select2.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/richtext.min.css">
<script>
    const rolename = '<?= $v ?>';
    const roleid = <?= $k ?>;
    const has_akses = JSON.parse('<?= json_encode($has_akses) ?>');
    const pph = JSON.parse('<?= json_encode($pph) ?>')
    const ppn = JSON.parse('<?= json_encode($ppn) ?>')

    let dt_proyek = '<?php echo json_encode($data['proyek']) ?>';
    dt_proyek = JSON.parse(dt_proyek);

    const conf = JSON.parse('<?= $conf ?>')

    const li_keu = JSON.parse('<?= $li_keu ?>')
</script>
<style>
    @media screen and (max-width: 1366px) {
        html {
            font-size: 70%;
            /* Mengurangi ukuran font global menjadi 90% */
        }
    }

    /* bug drag kanvas malah select text */
    body {
        -webkit-user-select: none;
        /* Safari */
        -ms-user-select: none;
        /* IE 10 and IE 11 */
        user-select: none;
        /* Standard syntax */
    }

    .canvas {
        /* border: 1px solid black; */
        background-color: #eee;
    }

    #modal-list-rumah-belum-selesai .modal-dialog {
        max-width: min(1040px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal-list-rumah-belum-selesai .modal-content {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
        overflow: hidden;
    }

    #modal-list-rumah-belum-selesai .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: .9rem 1rem;
    }

    #modal-list-rumah-belum-selesai .modal-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0;
    }

    #modal-list-rumah-belum-selesai .modal-body {
        background: #f3f5f7;
        padding: .85rem;
    }

    #modal-list-rumah-belum-selesai .table-responsive {
        background: #fff;
        border: 1px solid #d8dde3;
        border-radius: 8px;
    }

    #modal-list-rumah-belum-selesai .table {
        color: #111827;
        margin-bottom: 0;
        min-width: 920px;
    }

    #modal-list-rumah-belum-selesai thead th {
        background: #f3f4f6;
        border-bottom: 1px solid #d8dde3;
        border-top: 0;
        color: #1f2937;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .02em;
        padding: .8rem .9rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #modal-list-rumah-belum-selesai tbody td {
        border-top: 1px solid #d8dde3;
        font-size: .86rem;
        padding: .85rem .9rem;
        vertical-align: middle;
    }

    #modal-list-rumah-belum-selesai tbody tr:first-child td {
        border-top: 0;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-no {
        color: #111827;
        font-weight: 700;
        width: 42px;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-kavling {
        min-width: 190px;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-kavling-title {
        color: #111827;
        font-size: .78rem;
        font-weight: 800;
        line-height: 1.25;
        text-transform: uppercase;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-kavling-meta {
        color: #111827;
        font-size: .76rem;
        font-weight: 600;
        line-height: 1.25;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-cell {
        min-width: 210px;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-wrap {
        align-items: center;
        display: flex;
        gap: .75rem;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-track {
        background: #dfe5ec;
        border-radius: 999px;
        flex: 1 1 auto;
        height: 7px;
        min-width: 120px;
        overflow: hidden;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-fill {
        background: #f6a76a;
        border-radius: inherit;
        height: 100%;
        transition: width .2s ease;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-fill.is-high {
        background: #2057a3;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-progress-value {
        color: #111827;
        font-size: .86rem;
        font-weight: 800;
        min-width: 44px;
        text-align: right;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-date {
        color: #111827;
        font-weight: 700;
        white-space: nowrap;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-days {
        color: #2057a3;
        display: block;
        font-size: .76rem;
        font-weight: 800;
        line-height: 1.25;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-days.is-overdue {
        color: #dc2626;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-badge {
        background: #e5e7eb;
        border-radius: 5px;
        color: #1f2937;
        display: inline-block;
        font-size: .66rem;
        font-weight: 800;
        line-height: 1;
        max-width: 170px;
        padding: .38rem .5rem;
        text-transform: uppercase;
        white-space: normal;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-badge.is-blue {
        background: #dbeafe;
        color: #164c8b;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-empty {
        color: #6b7280;
        font-weight: 700;
        padding: 1.4rem !important;
        text-align: center;
    }

    #modal-list-rumah-belum-selesai .rumah-belum-action {
        border-radius: 6px;
        height: 32px;
        padding: 0;
        width: 34px;
    }

    @media (max-width: 767.98px) {
        #modal-list-rumah-belum-selesai .modal-dialog {
            max-width: calc(100vw - 18px);
            margin: .6rem auto;
        }

        #modal-list-rumah-belum-selesai .modal-body {
            padding: .65rem;
        }
    }

    .float {
        position: fixed;
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(229, 231, 235, 0.6) !important;
        border-radius: 20px !important;
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.02) !important;
        z-index: 1040;
        padding: 6px 14px !important;
        margin: 0 !important;
        max-width: 95vw;
        width: auto !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        scrollbar-width: none; /* Hide scrollbar Firefox */
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        animation: floatDockEntrance 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) both;
    }

    /* Desktop (min-width: 1201px) - Align Left, Offset by Floating Sidebar */
    @media (min-width: 1201px) {
        .float {
            bottom: 24px !important;
            left: calc(290px + 24px) !important;
            right: auto !important;
            transform: none !important;
        }
        body.menu-collapsed .float {
            left: calc(110px + 24px) !important;
        }
    }

    /* Tablet (min-width: 768px and max-width: 1200px) - Align Left */
    @media (min-width: 768px) and (max-width: 1200px) {
        .float {
            bottom: 24px !important;
            left: 24px !important;
            right: auto !important;
            transform: none !important;
        }
    }

    .float::-webkit-scrollbar {
        display: none; /* Hide scrollbar Chrome/Safari */
    }

    .dark-layout .float {
        background: rgba(40, 48, 70, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.02) !important;
    }

    @keyframes floatDockEntrance {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Style for buttons inside float dock */
    .float .btn,
    .float .btn-icon {
        border-radius: 12px !important;
        font-weight: 600 !important;
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease-in-out !important;
        margin: 2px 1px !important;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.04) !important;
        border: 1px solid transparent !important;
        white-space: nowrap !important;
        width: auto !important; /* Button width is auto, fits content */
    }

    .float .btn:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 4px 8px -2px rgba(91, 79, 207, 0.15) !important;
    }

    .float .btn-primary {
        background: linear-gradient(135deg, #2057a3, #2f74c4) !important;
        color: #fff !important;
    }

    .float .btn-primary:hover {
        background: linear-gradient(135deg, #184272, #2057a3) !important;
    }

    .float .btn-success {
        background: linear-gradient(135deg, #10B981, #059669) !important;
        color: #fff !important;
    }

    .float .btn-success:hover {
        background: linear-gradient(135deg, #0d9668, #047857) !important;
    }

    .float .btn-info {
        background: linear-gradient(135deg, #0EA5E9, #0284C7) !important;
        color: #fff !important;
    }

    .float .btn-info:hover {
        background: linear-gradient(135deg, #0284C7, #0369A1) !important;
    }

    .float .btn-danger {
        background: linear-gradient(135deg, #EF4444, #DC2626) !important;
        color: #fff !important;
    }

    .float .btn-danger:hover {
        background: linear-gradient(135deg, #DC2626, #B91C1C) !important;
    }

    #produksi_menu.produksi-jalan-selecting {
        width: fit-content !important;
        max-width: calc(100vw - 48px) !important;
        overflow-x: visible !important;
        justify-content: flex-start !important;
    }

    #produksi_menu > .hidden,
    #produksi_menu > .d-none {
        display: none !important;
    }

    #produksi_menu.produksi-jalan-selecting #produksi_add_jalan_hint {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        font-size: 0.75rem;
        line-height: 1;
        padding: 0 8px;
    }

    #produksi_menu.produksi-jalan-selecting #produksi_add_jalan_undo {
        display: inline-flex !important;
    }

    .float .btn-outline-warning {
        border: 1.5px solid #F59E0B !important;
        color: #F59E0B !important;
        background: transparent !important;
    }

    .float .btn-outline-warning:hover {
        background: rgba(245, 158, 11, 0.08) !important;
        color: #d97706 !important;
        border-color: #d97706 !important;
    }

    .float .btn-outline-danger {
        border: 1.5px solid #EF4444 !important;
        color: #EF4444 !important;
        background: transparent !important;
    }

    .float .btn-outline-danger:hover {
        background: rgba(239, 68, 68, 0.08) !important;
        color: #DC2626 !important;
        border-color: #DC2626 !important;
    }

    /* Group separator labels for the mobile expanded menu panel */
    .float .menu-group-label {
        display: none;
    }

    /* Styling manual selection switch */
    .float .custom-switch {
        padding-left: 2.25rem !important;
        margin-right: 6px !important;
        display: inline-flex;
        align-items: center;
        height: 32px;
        white-space: nowrap !important;
    }

    .float .custom-control-label {
        font-weight: 600;
        color: #4B5563;
        font-size: 0.75rem;
        cursor: pointer;
        user-select: none;
    }

    .dark-layout .float .custom-control-label {
        color: #C4C6D8;
    }

    /* Mobile styles (max-width: 767px) */
    @media (max-width: 767px) {
        .float {
            bottom: 16px !important;
            right: 16px !important;
            left: auto !important;
            transform: none !important;
            padding: 4px !important;
            border-radius: 24px !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            max-width: 90vw;
            flex-wrap: nowrap !important;
        }

        /* Hide all buttons except the mobile-menu-trigger by default on mobile */
        .float > *:not(.mobile-menu-trigger) {
            display: none !important;
        }

        /* When expanded, show all buttons */
        .float.mobile-expanded {
            border-radius: 16px !important;
            padding: 10px !important;
            display: flex !important;
            flex-direction: column !important; /* Stack vertically */
            align-items: flex-end !important; /* Align button list items to the right */
            width: auto !important;
            max-height: 60vh;
            overflow-y: auto;
            flex-wrap: wrap !important;
        }

        .float.mobile-expanded > * {
            display: inline-flex !important;
            width: auto !important; /* Buttons not full width */
            justify-content: flex-end !important;
            align-self: flex-end !important; /* Force right alignment of button */
            margin: 3px 0 !important;
        }

        .float.mobile-expanded > .d-none {
            display: none !important;
        }

        .float.mobile-expanded .menu-group-label {
            display: block !important;
            width: 100%;
            text-align: right;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #94a3b8;
            margin: 8px 0 2px;
            padding-top: 6px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }

        .float.mobile-expanded .menu-group-label:first-of-type {
            border-top: none;
            margin-top: 0;
            padding-top: 0;
        }

        .float.mobile-expanded .custom-switch {
            display: flex !important;
            justify-content: flex-end !important;
            align-self: flex-end !important;
            width: auto !important;
            margin: 6px 0 !important;
            padding-left: 2rem !important;
        }

        .float.mobile-expanded .mobile-menu-trigger {
            order: -1;
            margin-bottom: 6px !important;
            background: linear-gradient(135deg, #EF4444, #DC2626) !important;
            color: #fff !important;
            align-self: flex-end !important;
        }
    }

    #table-selesai th {
        text-align: center;
        vertical-align: middle;
    }

    .my-float {
        /* margin-top: 22px; */
    }

    .disabled {
        pointer-events: none;
        cursor: default;
    }

    /* div#mkdt,
    div#legal,
    div#lpt {
        height: 50vh;
        overflow: auto;
        background: #fff;
    } */

    .capitalize {
        text-transform: capitalize;
    }

    .tab-pane.active {
        animation: slide-down 0.3s ease-out;
    }

    @keyframes slide-down {
        0% {
            opacity: 0.5;
            transform: translateX(20px);
        }

        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* The heart of the matter */
    .testimonial-group>.row {
        overflow-x: auto;
        white-space: nowrap;
    }

    .testimonial-group>.row>.col-sm-4 {
        display: inline-block;
        float: none;
    }

    /* Decorations */
    .col-sm-4 {
        color: #fff;
        font-size: 48px;
        padding-bottom: 20px;
        padding-top: 18px;
    }

    .col-sm-4:nth-child(3n+1) {
        background: #c69;
    }

    .col-sm-4:nth-child(3n+2) {
        background: #9c6;
    }

    .col-sm-4:nth-child(3n+3) {
        background: #69c;
    }

    /* Menyusun button dengan posisi menempel di kanan tengah */
    .center-right {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translate(0, -50%);
    }

    #menu {
        display: none;
        position: absolute;
        width: 80px;
        background-color: white;
        box-shadow: 0 0 5px grey;
        border-radius: 3px;
    }

    #menu button {
        width: 100%;
        background-color: white;
        border: none;
        margin: 0;
        padding: 10px;
    }

    #menu button:hover {
        background-color: lightgray;
    }

    #div_filter {
        overflow-y: scroll;
    }

    /* untuk modal  */
    #modalEwe .modal-dialog {
        width: 100%;
        height: 50%;
        margin: 0;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
    }

    #modalEwe .modal-content {
        height: 100%;
    }

    #modalEwe .modal.fade .modal-dialog {
        transform: translateY(-100%);
        transition: transform 0.3s ease-out;
    }

    #modalEwe .modal.show .modal-dialog {
        transform: translateY(0);
    }

    .foto-container {
        min-height: 250px;
        height: auto;
    }

    .file-container {
        min-height: 150px;
        height: auto;
    }

    .siteplan-urgent-toggle {
        align-items: center;
        background: #2057a3;
        border: 0;
        border-radius: 8px;
        bottom: 1.25rem;
        box-shadow: 0 10px 26px rgba(32, 87, 163, .24);
        color: #fff;
        cursor: pointer;
        display: flex;
        font-weight: 700;
        gap: .45rem;
        padding: .65rem .85rem;
        position: fixed;
        right: 1.25rem;
        z-index: 1035;
    }

    .siteplan-urgent-toggle .badge {
        background: #ea5455;
        color: #fff;
        margin-left: .15rem;
    }

    .siteplan-urgent-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        bottom: 4.5rem;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .22);
        max-height: calc(100vh - 8rem);
        overflow: hidden;
        position: fixed;
        right: 1.25rem;
        width: min(410px, calc(100vw - 2rem));
        z-index: 1036;
    }

    .siteplan-urgent-header {
        align-items: center;
        border-bottom: 1px solid #edf0f2;
        display: flex;
        justify-content: space-between;
        padding: .85rem 1rem;
    }

    .siteplan-urgent-title {
        color: #111827;
        font-size: .98rem;
        font-weight: 700;
        margin: 0;
    }

    .siteplan-urgent-body {
        background: #f8fafc;
        max-height: calc(100vh - 12rem);
        overflow-y: auto;
        padding: .85rem;
    }

    .siteplan-urgent-section {
        margin-bottom: .85rem;
    }

    .siteplan-urgent-section:last-child {
        margin-bottom: 0;
    }

    .siteplan-urgent-section-title {
        align-items: center;
        color: #4b5563;
        display: flex;
        font-size: .78rem;
        font-weight: 700;
        justify-content: space-between;
        margin-bottom: .45rem;
        text-transform: uppercase;
    }

    .siteplan-urgent-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #2057a3;
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: .5rem;
        padding: .7rem .75rem;
    }

    .siteplan-urgent-item:hover {
        border-color: #c8d7ee;
    }

    .siteplan-urgent-item.is-danger {
        border-left-color: #ea5455;
    }

    .siteplan-urgent-item.is-warning {
        border-left-color: #ff9f43;
    }

    .siteplan-urgent-item.is-info {
        border-left-color: #00cfe8;
    }

    .siteplan-urgent-item-title {
        color: #111827;
        font-size: .88rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: .25rem;
    }

    .siteplan-urgent-item-desc,
    .siteplan-urgent-item-meta,
    .siteplan-urgent-empty {
        color: #6b7280;
        font-size: .78rem;
        line-height: 1.35;
    }

    .siteplan-urgent-empty {
        background: #fff;
        border: 1px dashed #d8dde3;
        border-radius: 8px;
        padding: .85rem;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .siteplan-urgent-toggle {
            bottom: .75rem;
            left: .75rem;
            right: auto;
        }

        .siteplan-urgent-panel {
            bottom: 4rem;
            left: .75rem;
            right: auto;
            width: min(410px, calc(100vw - 5.5rem));
        }
    }

    .input-foto-container {
        width: 350px;
        height: 150px;
        display: flex;
        flex-direction: row;
    }

    .input-foto {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .flatpickr-human-friendly:enabled {
        background-color: #fff !important;
    }

    <?= view('siteplan/partials/modal_detail_styles') ?>

    @media (max-width: 1199.98px) {
        #modal_detail .detail-kavling-layout {
            flex-wrap: wrap;
        }

        #modal_detail .detail-kavling-sidebar,
        #modal_detail .detail-kavling-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal_detail .detail-kavling-sidebar {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #modal_detail .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal_detail .modal-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal_detail .nav-pills {
            flex-direction: row !important;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .25rem;
        }

        #modal_detail .detail-dashboard-grid,
        #modal_detail .detail-card-grid,
        #modal_detail .detail-production-dashboard {
            grid-template-columns: 1fr;
        }

        #modal_detail .card-body {
            padding: .85rem;
        }
    }


    .siteplan-main-card {
        overflow: hidden;
        --siteplan-main-card-height: auto;
        --siteplan-main-content-height: auto;
    }

    .siteplan-main-row {
        align-items: stretch;
    }

    .siteplan-canvas-col,
    #filter-side {
        min-width: 0;
    }

    #stage-parent,
    #konva-holder {
        width: 100%;
    }

    @media (min-width: 768px) {
        .siteplan-main-card {
            min-height: var(--siteplan-main-card-height);
        }

        .siteplan-main-card > .card-body {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .siteplan-main-row {
            flex: 1 1 auto;
            min-height: var(--siteplan-main-content-height);
        }

        .siteplan-canvas-col {
            display: flex;
            flex-direction: column;
        }

        #stage-parent {
            flex: 1 1 auto;
            height: var(--siteplan-main-content-height);
            min-height: var(--siteplan-main-content-height);
        }

        #konva-holder {
            height: var(--siteplan-main-content-height);
            min-height: var(--siteplan-main-content-height);
        }

        #filter-side {
            align-self: stretch;
            height: var(--siteplan-main-content-height);
            max-height: var(--siteplan-main-content-height);
        }
    }

    .input-loading {
        filter: blur(3px);
        transition: filter 0.3s ease;
        /* untuk animasi */

        /* background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loadingShimmer 1.5s infinite;
        color: transparent !important;
        position: relative; */
    }

    .divider-left {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 20px;
    }

    @keyframes loadingShimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .guarantee-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    /* floating label */
    .floating-label {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .floating-label input {
        padding: 1rem 0.75rem 0.25rem;
    }

    .floating-label-select label {
        /* position: relative; */
        top: -0.5rem !important;
        /* left: 0.7rem;
        font-size: 11px;
        color: #464646; */
        background: #fff;
        padding: 0 4px;
    }

    .floating-label label {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        color: #6c757d;
        font-size: 10px;
        pointer-events: none;
        transition: all 0.2s ease;
    }

    .floating-label input:focus+label,
    .floating-label input:not(:placeholder-shown)+label {
        top: -0.6rem;
        left: 0.7rem;
        font-size: 11px;
        color: #464646;
        background: #fff;
        padding: 0 4px;
    }

    /* .floating-label .active label {
        top: -0.6rem;
        left: 0.7rem;
        font-size: 11px;
        color: #464646;
    } */

    .dropzone {
        position: relative;
        border: 2px dashed #ced4da;
        border-radius: .75rem;
        background: #fff;
        transition: .15s border-color, .15s background;
    }

    .dropzone-lg {
        min-height: 90px;
    }

    .dropzone.is-dragover {
        border-color: #007bff;
        background: rgba(0, 123, 255, .05);
    }

    .dz-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .dz-inner {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem;
    }

    .dz-placeholder {
        pointer-events: none;
    }

    .dz-preview {
        display: none;
        /* akan ditampilkan saat ada file */
        height: 100%;
        max-width: 90px;
    }

    .dz-preview .thumb-wrap {
        position: relative;
        display: inline-block;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        overflow: hidden;
        max-width: 100%;
    }

    .dz-preview img {
        display: block;
        max-height: 90px;
        width: auto;
    }

    .dz-remove {
        position: absolute;
        top: .25rem;
        right: .25rem;
        border: 0;
        border-radius: .35rem;
        padding: .25rem .5rem;
    }

    .dz-fileinfo {
        margin-top: .5rem;
        font-size: .9rem;
    }


    /* TOASTER */
    #toast-container {
        top: 1rem;
        right: 1rem;
        z-index: 1080;
    }

    .toast {
        margin-top: 0.5rem;
    }

    /* highlight button */
    .btn-highlight {
        animation: highlightFlash 3s ease-out;
    }

    @keyframes highlightFlash {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 200, 0, 0.8);
        }

        50% {
            box-shadow: 0 0 10px 6px rgba(255, 200, 0, 0.8);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 200, 0, 0);
        }
    }
</style>
<!-- END: Vendor CSS-->

<!-- BEGIN: Content-->
<div class="app-content content ">
    <div id="menu_here"></div>

    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">
            <!-- Kick start -->
            <div class="card siteplan-main-card">
                <!-- <div class="card-header">
                        <h4 class="card-title">ASI Ext</h4>
                    </div> -->
                <div class="card-body">
                    <div class="row siteplan-main-row">
                        <div style="overflow: auto" class="hidden">
                            <div style="float: left; margin-right: 10px;">Blur radius: </div>
                            <input id="blurRadius" type="text" onchange="onRadiusChange.apply(this, arguments)"
                                style="float: left; width: 20px; margin-right: 10px;" />
                            <div id="threshold"></div>
                        </div>
                        <div class="col-md-9 siteplan-canvas-col">
                            <div id="stage-parent">
                                <div class="canvas" id="konva-holder"></div>
                            </div>
                            <div id="menu">
                                <div>
                                    <button id="menu-btn-lihat_detail">Detail</button>
                                    <!-- <button id="menu-btn-input">Isi/Ubah</button> -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 d-md-block" style="overflow-y:auto" id="filter-side">
                            <div class="form-group">
                                <select id="pilih-divisi" class="form-control-sm select2">
                                    <option value="0">Pilih Divisi</option>
                                    <!-- <option value="8" class="dropdown-item">Sales & Promotion</option> -->
                                    <option value="7" class="dropdown-item">Produksi</option>
                                    <option value="4" class="dropdown-item">Marketing Data</option>
                                    <option value="5" class="dropdown-item">Legal & Pertanahan</option>
                                    <option value="10" class="dropdown-item">Pajak</option>
                                    <option value="3" class="dropdown-item">Keuangan</option>
                                    <option value="6" class="dropdown-item">Planning</option>
                                    <option value="11" class="dropdown-item">Target</option>
                                    <!-- <option value="9" class="dropdown-item">Management</option> -->
                                </select>
                                <button onclick="load_kavling()" class="btn btn-sm btn-primary col-12 mt-1">
                                    Muat Ulang Kavling
                                </button>
                            </div>

                            <div class="divider divider-left">
                                <div class="divider-text">Filter</div>
                            </div>

                            <div class="form-group">
                                <select id="filter-kategori" name="filter-kategori"
                                    class="select2 select-sm form-control-sm">
                                    <option value="">Semua</option>

                                </select>
                            </div>
                            <div class="form-group">
                                <select id="filter-id_cluster" name="id_cluster"
                                    class="select2 select-sm form-control-sm"></select>
                            </div>
                            <div class="form-group">
                                <select disabled id="filter-id_jalan" name="id_jalan"
                                    class="select-sm form-control-sm select2 "></select>
                            </div>
                            <div class="form-group row">
                                <button class="btn btn-primary col-5 ml-1 mt-1 mb-1 btn-sm "
                                    onclick="filter_option()">Filter Data</button>
                                <button class="btn btn-outline-warning col-5 m-1 btn-sm "
                                    onclick="hapus_filter_option()">Hapus Filter</button>
                            </div>
                            <div id="keterangan-warna-here"></div>
                            <hr>
                            <div class="form-group">
                                <!-- <button onclick="renderText()" class="btn btn-outline-warning  col-12" id="btn-renderText"> Tampilkan Keterangan Warna Di Siteplan</button> -->
                                <button onclick="export_siteplan()" class="btn btn-sm btn-outline-primary  col-12"
                                    id="btn-export-siteplan"> Export Siteplan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Kick start -->
    </div>
</div>
<!-- BEGIN: Vendor JS-->
<script src="<?= base_url() ?>app-assets/vendors/js/vendors.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="<?= base_url() ?>assets/js/magic-wand.min.js"></script>
<script src="<?= base_url() ?>assets/js/konva.min.js"></script>
<script src="<?= base_url() ?>assets/js/jquery.richtext.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/forms/select/select2.full.min.js"></script>
<script src="<?= base_url() ?>app-assets/vendors/js/html2canvas/html2canvas.min.js"></script>
<!-- <script src="<?= base_url() ?>assets/js/scripts.js"></script> -->
<!-- END: Page Vendor JS-->
<script src="<?= base_url() ?>assets/js/siteplan/master.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/master.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/siteplan-detail-modal.js?<?= filemtime(FCPATH . 'assets/js/siteplan-detail-modal.js') ?>"></script>



<!-- Modal to add new record -->
<?php if ($k == 1 || $k == 6): ?>
    <?php echo view('siteplan/planning'); ?>
<?php endif; ?>

<?php if ($k == 1 || $k == 11): ?>
    <?php echo view('siteplan/target'); ?>
<?php endif; ?>

<?php if ($k == 7 || $k == 1): ?>
    <!--#################################### Modal Produksi #########################################-->
    <?php echo view('siteplan/produksi'); ?>
<?php endif; ?>

<?php if ($k == 8 || $k == 1): ?>
    <?php echo view('siteplan/sales'); ?>
<?php endif; ?>


<?php if ($k == 5 || $k == 1): ?>
    <?php echo view('siteplan/legal'); ?>
<?php endif; ?>

<?php if ($k == 4 || $k == 1): ?>
    <?php echo view('siteplan/mkdt'); ?>
<?php endif; ?>

<?php if ($k == 9 || $k == 1): ?>
    <?php echo view('siteplan/direksi'); ?>
<?php endif; ?>

<?php if ($k == 3 || $k == 1): ?>
    <?php echo view('siteplan/keuangan'); ?>
<?php endif; ?>

<?php if ($k == 10 || $k == 1): ?>
    <?php echo view('siteplan/pajak'); ?>
<?php endif; ?>
<?php if (in_array($k, [1, 7, 3])): ?>
    <?php echo view('siteplan/cashout_subkon'); ?>
<?php endif; ?>

<?= view('siteplan/partials/modal_detail', isset($data) ? $data : []) ?>

<style>
    #modal-batal .modal-dialog {
        max-width: min(1280px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal-batal .modal-content {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: none;
        overflow: hidden;
    }

    #modal-batal #fm-batal_booking {
        border: 0;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
    }

    #modal-batal .modal-header,
    #modal-batal .modal-footer {
        background: #fff;
        border-color: #e5e7eb;
        margin-bottom: 0 !important;
        padding: .9rem 1rem;
    }

    #modal-batal .modal-title {
        /* color: #111827; */
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal-batal .modal-body {
        background: #f3f5f7;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal-batal .batal-summary {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: .85rem 1rem;
    }

    #modal-batal .batal-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        height: 100%;
        padding: 1rem;
    }

    #modal-batal .divider {
        margin: .2rem 0 .85rem;
    }

    #modal-batal .divider-left {
        border-left-color: #2057a3;
        padding-left: .75rem;
    }

    #modal-batal .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal-batal label,
    #modal-batal .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal-batal .form-control,
    #modal-batal .custom-file-label {
        border-color: #d8dde3;
        border-radius: 6px;
    }

    #modal-batal .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modal-batal .refund-status-card {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: .85rem;
    }

    #modal-batal .refund-status-card .custom-control {
        margin-bottom: .35rem;
    }

    #modal-batal .refund-status-card .custom-control:last-child {
        margin-bottom: 0;
    }

    #modal-batal .refund-status-note {
        color: #6b7280;
        display: block;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .45rem;
    }

    @media (max-width: 767.98px) {
        #modal-batal .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal-batal .modal-body {
            max-height: calc(100vh - 7rem);
            padding: .75rem;
        }

        #modal-batal .batal-panel {
            height: auto;
            margin-bottom: .75rem;
            padding: .85rem;
        }
    }
</style>

<!-- END: Content-->
<div class="modal fade text-left" id="modal-batal" tabindex="-1" role="dialog" aria-labelledby="modal-batal"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Batal Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="fm-batal_booking" enctype="multipart/form-data" class="add-new-record modal-content pt-0">
                <div class="modal-body">
                    <div class="batal-summary">
                        <p class="modal-title label_alamat mb-0" id="label-batal_booking"></p>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="batal-panel">
                            <div class="divider divider-left">
                                <div class="divider-text">Data Konsumen</div>
                            </div>
                            <div class="form-group">
                                <label>No SPPTB</label>
                                <input disabled type="text" class="form-control" id="batal-no_spptb"
                                    name="batal-no_spptb">
                            </div>
                            <div class="form-group">
                                <label>Nama Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-nama_konsumen" required
                                    name="batal-nama_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Alamat Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-alamat_konsumen"
                                    name="batal-alamat_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NIK</label>
                                <input disabled type="text" class="form-control" id="batal-nik_konsumen"
                                    name="batal-nik_konsumen">
                            </div>
                            <div class="form-group">
                                <label>NPWP</label>
                                <input disabled type="text" class="form-control" id="batal-npwp_konsumen"
                                    name="batal-npwp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Kontak Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-hp_konsumen"
                                    name="batal-hp_konsumen">
                            </div>
                            <div class="form-group">
                                <label>Email Konsumen</label>
                                <input disabled type="text" class="form-control" id="batal-email_konsumen"
                                    name="batal-email_konsumen">
                            </div>
                            <div class="form-group hidden">
                                <label>Status Konsumen</label>
                                <select class="form-control" id="batal-status_konsumen" name="batal-status_konsumen">
                                    <option value="">-</option>
                                    <option value="Umum">Umum</option>
                                    <option value="TWP">TWP</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sales</label>
                                <input disabled type="text" class="form-control" id="batal-sales" required
                                    name="batal-sales">
                            </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="batal-panel">
                            <input readonly type="hidden" class="form-control" id="batal-id_konsumen"
                                name="batal-id_konsumen">
                            <input readonly type="hidden" class="form-control" id="batal-id_mkdt" name="batal-id_mkdt">
                            <input readonly type="hidden" class="form-control" id="batal-id_kavling"
                                name="batal-id_kavling">
                            <div class="divider divider-left">
                                <div class="divider-text">TUNAI/KPR</div>
                            </div>
                            <div class="form-group">
                                <label>Tunai/KPR</label>
                                <select disabled class="form-control" id="batal-is_kpr" name="batal-is_kpr">
                                    <option value="">-</option>
                                    <option value="0">TUNAI/CASH KERAS</option>
                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                    <option value="1">KPR</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Subsidi/Non-Subsidi</label>
                                <select disabled class="form-control" id="batal-is_subsidi" name="batal-is_subsidi">
                                    <option value="">-</option>
                                    <option value="0">Non-Subsidi</option>
                                    <option value="1">Subsidi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">ACC KPR</label>
                                <input readonly type="text" class="form-control num" id="batal-harga_kpr_acc"
                                    name="batal-harga_kpr_acc">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Turun KPR</label>
                                <input readonly type="text" class="form-control num" id="batal-harga_penambahan_um"
                                    name="batal-harga_penambahan_um">
                            </div>
                            <div class="divider divider-left">
                                <div class="divider-text">Penambahan Biaya</div>
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Penambahan Biaya</label>
                                <input disabled type="text" class="form-control num totalbb" id="batal-harga_penambahan"
                                    name="batal-harga_penambahan">
                            </div>
                            <div class="form-group">
                                <label for="total_biaya2">Keterangan Penambahan Biaya</label>
                                <textarea disabled name="batal-keterangan_penambahan_biaya"
                                    id="batal-keterangan_penambahan_biaya" class="form-control batal-fm" cols="30"
                                    rows="2"></textarea>
                            </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="batal-panel">
                            <div class="divider divider-left">
                                <div class="divider-text">Total Uang Muka</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-total_biaya_um">Total Uang Muka</label>
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_um"
                                    name="batal-total_biaya_um">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_um">Sudah Bayar Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_um"
                                    name="batal-sudah_bayar_um">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Uang Muka</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_um"
                                    name="batal-sisa_tagihan_um">
                            </div>
                            <!-- <div class="form-group">
                                <label for="batal-persentase_bayar_tagihan_um">Persentase</label>
                                <input disabled type="text" class="form-control" style="text-align:right" readonly id="batal-persentase_bayar_tagihan_um" name="batal-persentase_bayar_tagihan_um">
                            </div> -->
                            <div class="divider divider-left">
                                <div class="divider-text">Total Biaya-biaya</div>
                            </div>
                            <div class="form-group">
                                <label for="batal-total_biaya_bb">Total Biaya-biaya</label>
                                <input readonly type="text" class="form-control num" id="batal-total_biaya_bb"
                                    name="batal-total_biaya_bb">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label for="batal-sudah_bayar_bb">Sudah Bayar Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sudah_bayar_bb"
                                    name="batal-sudah_bayar_bb">
                            </div>
                            <div class="form-group">
                                <label for="batal-sisa_tagihan_um">Sisa Tagihan Biaya-biaya</label>
                                <input disabled type="text" class="form-control num" readonly id="batal-sisa_tagihan_bb"
                                    name="batal-sisa_tagihan_bb">
                            </div>
                            <!-- <div class="form-group">
                                <label for="batal-persentase_bayar_tagihan_bb">Persentase</label>
                                <input disabled type="text" class="form-control" style="text-align:right" readonly id="batal-persentase_bayar_tagihan_bb" name="batal-persentase_bayar_tagihan_bb">
                            </div> -->
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3 col-lg-3">
                            <div class="batal-panel">
                            <div class="divider divider-left">
                                <div class="divider-text">Batal</div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan_batal">Keterangan Batal</label>
                                <textarea class="form-control" id="batal-keterangan_batal" name="batal-keterangan_batal"
                                    rows="3" placeholder="Keterangan"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Surat Batal</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" accept="application/pdf"
                                        name="file_surat_batal" id="file_surat_batal" />
                                    <label class="custom-file-label" id="label-file_surat_batal"
                                        for="label-file_surat_batal">Upload Surat Batal</label>
                                    <a href="" target=_blank id="list-file_surat_batal">klik untuk melihat surat
                                        batal</a>
                                </div>
                            </div>
                            <small id="last_update-batal_mkdt" class="text-muted"></small>
                            <div class="divider divider-left">
                                <div class="divider-text">Pengembalian Dana ke Konsumen</div>
                            </div>
                            <div class="form-group">
                                <label>Status Refund</label>
                                <div class="refund-status-card">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="batal-perlu_refund_0"
                                            name="batal-perlu_refund" value="0" checked>
                                        <label class="custom-control-label" for="batal-perlu_refund_0">Tidak Perlu Refund</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="batal-perlu_refund_1"
                                            name="batal-perlu_refund" value="1">
                                        <label class="custom-control-label" for="batal-perlu_refund_1">Perlu Refund</label>
                                    </div>
                                    <small class="refund-status-note">Status ini akan tampil di list konsumen batal.</small>
                                </div>
                            </div>
                            <small id="last_update-batal_keuangan" class="text-muted"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btn-simpan_batal_mkdt" class="btn btn-primary" onclick="simpan_batal()">Simpan</button>
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!--#################################### Modal Filter/Setting #########################################-->
<div class="modal modal-slide-in fade" id="modal-setting-filter">
    <div class="modal-dialog sidebar-sm">
        <div class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Filter</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <!-- <div id="modal-filter"></div> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-list-rumah-belum-selesai" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title">Kavling Belum Selesai di Bangun</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="table-responsive">
                    <table class="table" id="table-selesai">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kavling</th>
                                <th>Progress</th>
                                <th>Pembangunan</th>
                                <th>Rencana Selesai</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-rumah-belum-selesai-here"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-list-jatuh-tempo">
    <div class="modal-dialog modal-lg">
        <div class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h1 class="modal-title" id="exampleModalLabel">Konsumen Jatuh Tempo</h1>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="table-responsive">
                    <table class="table" id="table-selesai">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Konsumen</th>
                                <th>Tagihan</th>
                                <th></th>
                            </tr>

                        </thead>
                        <tbody id="list-jatuh-tempo-here"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalEwe" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Filter & Keterangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-filter"></div>
            </div>
        </div>
    </div>
</div>
<div class="fade modal text-left" id="modal_othersdetail" aria-labelledby="modal_othersdetail" role="dialog"
    aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Detail</h5><button class="close" type="button"
                    data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
            </div>
            <form class="modal-content pt-0 add-new-record" id="fm-fotherproduksi">
                <div class="modal-body">
                    <p class="modal-title label_alamat" id="label_fothersproduksi"></p><br><span><strong>Luas di
                            Siteplan :</strong><br><span
                            class="t_luas_planning"></span></span><br><span><strong>Keterangan Planning
                            :</strong><br><span class="t_keterangan_planning"></span></span><br><br><span><strong>Luas
                            di Sertifikat :</strong><br><span
                            class="t_luas_legal"></span></span><br><span><strong>Keterangan Legal :</strong><br><span
                            class="t_keterangan_legal"></span></span><br><br><span><strong>Luas di Lapangan
                            :</strong><br><span class="t_luas_produksi"></span></span><br><span><strong>Keterangan
                            Produksi :</strong><br><span class="t_keterangan_produksi"></span></span>
                    <hr>
                    <div class="form-group"><label for="f_progres_jalan">Progres</label> <input
                            name="f_detail_progres_jalan" class="form-control-range" id="f_detail_progres_jalan"
                            disabled type="range" max="100" min="0" oninput='$(".r_progres").html($(this).val())'
                            step="5"> <span class="r_progres"></span><span>%</span></div>
                    <div id="detail_produksi_jalan_history_wrap" class="produksi-jalan-history-card mt-2 d-none">
                        <div class="divider divider-left mb-1">
                            <div class="divider-text font-weight-bold">History Progres</div>
                        </div>
                        <div id="detail_produksi_jalan_history"></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="reset"
                        data-dismiss="modal">Tutup</button></div>
            </form>
        </div>
    </div>
</div>
