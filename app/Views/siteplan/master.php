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
    let sv_url,
        sv_fm,
        sv_btn,
        sv_par,
        wr_pembangunan = [],
        list_jatuhtempo = [],
        filter = {
            id_cluster: '',
            id_jalan: ''
        },
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            Legal: null,
            Pajak: null,
            Target: null,
            'Lain-lain': null
        };

    const not_found = "/images/not_found.png"


    // var browser = require("webextension-polyfill");

    // const  nama_perusahaan,
    //             alamat_perusahaan,
    //             kota_perusahaan,
    //             tel_perushaan,


    const rolename = '<?= $v ?>';
    const roleid = <?= $k ?>;
    const has_akses = JSON.parse('<?= json_encode($has_akses) ?>');
    const pph = JSON.parse('<?= json_encode($pph) ?>')
    const ppn = JSON.parse('<?= json_encode($ppn) ?>')

    let dt_proyek = '<?php echo json_encode($data['proyek']) ?>';
    dt_proyek = JSON.parse(dt_proyek);

    let c_date = new Date();
    let c_date_m = (c_date.getMonth() + 1 > 10) ? c_date.getMonth() + 1 : "0" + (c_date.getMonth() + 1);
    let today_date = c_date.getFullYear() + '-' + c_date_m + '-' + c_date.getDate();

    //convert date to num
    function treatAsUTC(date) {
        let result = new Date(date);
        result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
        return result;
    }

    //cari selisih hari
    function daysBetween(startDate, endDate) {
        let millisecondsPerDay = 24 * 60 * 60 * 1000;
        return (treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay;
    }
    // palidasi manual
    function palid(id, val, msg) {

        if ($("#" + id).val() == val) {
            Swal.fire({
                //
                icon: 'error',
                title: msg,
                showConfirmButton: false,
                //timer: 1500
            });
            return false;
        }
        return true;

    }
    Date.prototype.toDateInputValue = (function() {
        var local = new Date(this);
        local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
        return local.toJSON().slice(0, 10);
    });

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
<script>
    let data_um = [],
        data_bb = []


    //sewwtalert2 fix error cant type after open modal
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

    const state = {
        id_kavling: null,
        id_hargajual: null,
        id_mkdt: null,
        data_um: {},
        data_bb: {},
        sisa_cicilan: 0,
        sudah_bayar: 0,
        total_cicilan: 0,
        status: {
            tab: {
                isClosed: false
            }
        }

    };


    //carousel
    $('.carousel').carousel('pause')

    //datepicker
    var fp = flatpickr(".flatpickr-human-friendly", {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
        }),
        sp,
        dtt = [], //data point from selection
        batchdtt = [], // multiple data point from selection
        kav, //kavling poly
        imageInfo,
        bml_old = 0, //batch mask old
        batchMask = [], //for multiple selection
        dtt_first = [], //for multiple selection
        sceneWidth = screen.width,
        sceneHeight = Math.min(window.innerHeight, window.innerHeight * 0.7);
    //declare stage
    var stage = new Konva.Stage({
        height: sceneHeight,
        width: sceneWidth,
        container: "konva-holder",
        draggable: true
    });

    //declare layer
    var siteplan = new Konva.Layer(), //siteplan layer
        masked = new Konva.Layer(), //selection layer
        // datal = new Konva.Layer(), //kavling shape layer
        group = new Konva.Group({ //group for tooltip
            visible: false,
        }),
        shape_ket = new Konva.Image({
            x: 10,
            y: 10,
            draggable: true,
            stroke: 'red',
            scaleX: 2,
            scaleY: 2,
        }),

        manual_selection = new Konva.Group(),

        maskedGroup = new Konva.Group(), //group for selection line and number
        tooltip = new Konva.Text({ //tooltip text on hovering at kavling
            text: '',
            fontFamily: 'Calibri',
            fontSize: 12,
            padding: 5,
            textFill: 'white',
            fill: 'black',
            text: 'vertical align',
            alpha: 0.75,
        }),
        tooltipbg = new Konva.Rect({ //tooltip bg on hovering at kavling
            width: 270,
            height: 57,
            stroke: "black",
            strokeWidth: 1,
            fill: "#f2ff7d",
        })

    Konva.hitOnDragEnabled = true; //for zoom on touchscreen

    group.add(tooltipbg, tooltip) //grouping tooltip bg & text
    siteplan.add(group);
    masked.add(shape_ket);
    masked.add(group);

    var siteplanImageReady = false,
        siteplanStageReady = false,
        siteplanCanvasInitialized = false;

    function syncSiteplanMainHeight(konva_h) {
        const $card = $('.siteplan-main-card');
        const $cardBody = $card.children('.card-body').first();
        const fallbackHeight = Math.ceil(Number(konva_h) || sceneHeight || 0);

        if (!$card.length) {
            return fallbackHeight;
        }

        if ($(window).width() < 768) {
            $card.css({
                '--siteplan-main-card-height': 'auto',
                '--siteplan-main-content-height': 'auto'
            });
            $('#filter-side').css('height', 'auto');
            return fallbackHeight;
        }

        const paddingY = (parseFloat($cardBody.css('padding-top')) || 0) +
            (parseFloat($cardBody.css('padding-bottom')) || 0);
        const cardRect = $card[0].getBoundingClientRect();
        const viewportGap = 16;
        const availableCardHeight = Math.max(0, window.innerHeight - cardRect.top - viewportGap);
        const availableContentHeight = Math.max(0, availableCardHeight - paddingY);
        const contentHeight = Math.ceil(Math.max(fallbackHeight, availableContentHeight));

        $card.css({
            '--siteplan-main-card-height': Math.ceil(contentHeight + paddingY) + 'px',
            '--siteplan-main-content-height': contentHeight + 'px'
        });
        $('#filter-side').css('height', '');

        return contentHeight;
    }

    function tryInitSiteplanCanvas() {
        if (!siteplanImageReady || !siteplanStageReady || siteplanCanvasInitialized) {
            return;
        }

        siteplanCanvasInitialized = true;
        initSiteplanCanvas();
    }

    // siteplan img object :
    var imageObj = new Image();
    imageObj.onload = function() {

        sp = new Konva.Image({
            x: 0,
            y: 0,
            image: imageObj,
            width: imageObj.width,
            height: imageObj.height,
            globalCompositeOperation: 'overlay'
        });

        // add image to the layer
        siteplan.add(sp);
        siteplanImageReady = true;
        tryInitSiteplanCanvas();
    };


    //siteplan src
    imageObj.src = dt_proyek.siteplan_access_url || file_url('proyek_siteplan', dt_proyek.id_proyek);

    //deklarasi kanvas untuk kavling
    function initSiteplanCanvas() {
        colorThreshold = 15;
        blurRadius = 1;
        simplifyTolerant = 0;
        simplifyCount = 30;
        hatchLength = 4;
        hatchOffset = 0;

        imageInfo = null;
        cacheInd = null;
        mask = null;
        oldMask = null;
        downPoint = null;
        allowDraw = false;
        addMode = false;
        currentThreshold = colorThreshold;

        showThreshold();

        //imginfo
        var img = imageObj;
        var cvs = masked;
        cvs.width = img.width;
        cvs.height = img.height;
        imageInfo = {
            width: img.width,
            height: img.height,
            context: cvs.getContext("2d", {
                willReadFrequently: true
            })._context
        };
        mask = null;

        var tempCtx = document.createElement("canvas").getContext("2d", {
            willReadFrequently: true
        });
        tempCtx.canvas.width = imageInfo.width;
        tempCtx.canvas.height = imageInfo.height;
        tempCtx.drawImage(img, 0, 0);
        imageInfo.data = tempCtx.getImageData(0, 0, imageInfo.width, imageInfo.height);

        //load kavling dari database
        load_kavling(roleid == 1 || roleid == 7);
        loadSiteplanUrgentPanel({
            force: false
        });

        // $("#pilih-divisi").select2("val", roleid)
        // change_div();
        load_menu();


        // scaling layer to fit stage
        let konva_w = parseFloat($("#konva-holder").width())
        let konva_h = parseFloat($("#konva-holder").height())
        let l
        if (konva_w > konva_h)
            l = parseFloat($("#konva-holder").width()) / imageObj.width;
        else
            l = parseFloat($("#konva-holder").height()) / imageObj.height;

        new Konva.Tween({
            node: stage,
            duration: 0.5,
            scaleX: l,
            scaleY: l,
            x: 0,
            y: 0,
            easing: Konva.Easings.EaseInOut,
        }).play();

        // stage.scale({
        //     x: l,
        //     y: l
        // });

        group.scale({
            x: 1 / l,
            y: 1 / l
        })

        stage.height(syncSiteplanMainHeight(konva_h));

    }

    var line_ms = new Konva.Line({
        points: [0, 0],
        stroke: "red",
        strokeWidth: 2,
        dash: [5, 5],
        opacity: 1,
        closed: !0,
        id: "line_sel"
    });

    //refresh kavling setelah ganti divisi
    $("#pilih-divisi").change(function() {
        change_div()
    });

    function isManualSelectionActive() {
        return $("#tambah_jalan").prop("checked") || $("#produksi_tambah_jalan").prop("checked");
    }

    function change_div() {
        $("#tambah_jalan").prop("checked", 0)
        $("#produksi_tambah_jalan").prop("checked", 0)
        hapus_seleksi(); //hapus seleksi kavling

        //tampilkan menu sesuai divisi jika login sebagai admin
        if (roleid == 1) {
            let va = $("#pilih-divisi option:selected").val();
            $(".div_menu").addClass("hidden");

            if (va != 0) {
                const $selectedMenu = $('.div_menu[data-siteplan-role="' + va + '"]');
                if ($selectedMenu.length) {
                    $selectedMenu.removeClass("hidden");
                } else {
                    $('.div_menu[data-siteplan-role="0"]').removeClass("hidden");
                }
            }
        }

        //load ulang kavling
        load_kavling();
    }

    function load_menu() {
        // let va = $("#pilih-divisi option:selected").val();
        $.ajax({
            url: base_url + 'home/getMenuBtn',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                // id_role: roleid
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");
                const menu = r.menu;
                $("#menu_here").html(menu);
                if (roleid == 7 && has_akses.proyek == false) {
                    $(".btn-prod").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $(".btn-prod").show()
                }
                if (roleid == 5 && has_akses.legal == false) {
                    $("#edit_kavling_batch").hide()
                    swal('warning', 'Peringatan!', "Kamu tidak bisa melakukan perubahan untuk proyek ini")
                } else {
                    $("#edit_kavling_batch").show()
                }
            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        })
    }

    function registerMobileBottomNav(config) {
        window.SIGAPPMobileBottomNavQueue = window.SIGAPPMobileBottomNavQueue || [];
        if (window.SIGAPPMobileBottomNav && typeof window.SIGAPPMobileBottomNav.register === 'function') {
            window.SIGAPPMobileBottomNav.register(config);
        } else {
            window.SIGAPPMobileBottomNavQueue.push(config);
        }
    }

    function registerSiteplanMobileBottomNav() {
        registerMobileBottomNav({
            filter: {
                sourceSelector: '#filter-side'
            },
            actions: [{
                sourceSelector: '#menu_here'
            }],
            showBack: true,
            showMenu: true
        });
    }

    registerSiteplanMobileBottomNav();

    function buat_nominatif() {

    }

    var stroke, fill, strokeWidth, dashed;
    let filterwarnahitung = {};

    function set_fill2(e) { //test set fill dengan config dari db
        // console.log(conf[e])
        if (!conf[e])
            e = "Warna Tidak Ditemukan"
        set_fill(conf[e].fill, conf[e].stroke, conf[e].strokeWidth, conf[e].dashed)
        return e;
    }

    const legalStatusOrder = [
        'Belum Isi Data',
        'Data Legal Masuk',
        'Sertipikat Induk Ada',
        'Split Sertipikat Diproses',
        'Sertipikat Split Terbit',
        'PBB Pecah / NOP Terbit',
        'PBG/IMB Terbit',
        'PPJB Dibuat',
        'PPH Dibayar / Validasi',
        'BPHTB Dibayar / Validasi',
        'AJB Dibuat',
        'Balik Nama Sertipikat',
        'Balik Nama PBB',
        'Selesai Legal'
    ];

    const pajakStatusOrder = [
        'Belum Input Pajak',
        'PPh4(2) Belum Bayar',
        'PPh4(2) Dibayar',
        'PPN Belum Bayar',
        'PPN Dibayar',
        'Faktur Pajak Terbit',
        'Selesai Pajak'
    ];

    function get_kategori_color(kategori) {
        if (conf[kategori]) return conf[kategori].fill;
        if (kategori == 'Belum Target') return '#f8fafc';
        if (String(kategori).indexOf('Target ') === 0) {
            const colors = ['#f59e0b', '#14b8a6', '#3b82f6', '#a855f7', '#ef4444', '#22c55e'];
            const year = parseInt(String(kategori).replace('Target ', ''), 10);
            return colors[Math.abs(year || 0) % colors.length];
        }
        return '#d1d5db';
    }

    function legalHasValue(value) {
        return value !== null &&
            value !== undefined &&
            value !== '' &&
            value !== 'null' &&
            value !== '0000-00-00';
    }

    function legalIsYes(value) {
        return value === 1 || value === '1' || value === true || value === 'Ya' || value === 'Iya' || value === 'Sudah';
    }

    function getLegalStatus(row) {
        if (!row.id_legal) {
            return {
                fill: 'Belum Isi Data',
                tipe: 'Legal'
            };
        }

        const sertifikatBalikNama = legalIsYes(row.sertifikat_is_balik_nama);
        const pbbBalikNama = legalIsYes(row.pbb_is_balik_nama);
        const sertifikatDikirim = legalHasValue(row.sertifikat_balik_nama_tgl_pengiriman) || legalHasValue(row.sertifikat_balik_nama_ke);
        const pbbDikirim = legalHasValue(row.pbb_balik_nama_tgl_pengiriman) || legalHasValue(row.pbb_balik_nama_ke);

        let status = 'Data Legal Masuk';

        if (legalHasValue(row.sertifikat_split_no_hgb_induk)) {
            status = 'Sertipikat Induk Ada';
        }

        if (legalIsYes(row.sertifikat_is_split) && !legalHasValue(row.sertifikat_split_no_hgb)) {
            status = 'Split Sertipikat Diproses';
        }

        if (legalHasValue(row.sertifikat_split_no_hgb) || legalHasValue(row.sertifikat_split_nib) || legalHasValue(row.sertifikat_split_tanggal_terbit)) {
            status = 'Sertipikat Split Terbit';
        }

        if (legalHasValue(row.pbb_pecah_nop)) {
            status = 'PBB Pecah / NOP Terbit';
        }

        if (row.pbg_status === 'Selesai' || legalHasValue(row.pbg_no) || legalHasValue(row.pbg_tanggal_terbit)) {
            status = 'PBG/IMB Terbit';
        }

        if (legalHasValue(row.ppjb_no) || legalHasValue(row.ppjb_tanggal)) {
            status = 'PPJB Dibuat';
        }

        if (legalHasValue(row.pph_tgl_bayar) || legalHasValue(row.pph_ntpn) || legalHasValue(row.pph_tanggal_validasi) || legalHasValue(row.pph_tgl_selesai) || legalHasValue(row.pph_no_sket)) {
            status = 'PPH Dibayar / Validasi';
        }

        if (legalHasValue(row.bphtb_tanggal_pembayaran) || legalHasValue(row.bphtb_tanggal_validasi)) {
            status = 'BPHTB Dibayar / Validasi';
        }

        if (legalHasValue(row.ajb_no) || legalHasValue(row.ajb_tanggal)) {
            status = 'AJB Dibuat';
        }

        if (sertifikatBalikNama) {
            status = 'Balik Nama Sertipikat';
        }

        if (pbbBalikNama) {
            status = 'Balik Nama PBB';
        }

        if (sertifikatBalikNama && pbbBalikNama && (sertifikatDikirim || pbbDikirim)) {
            status = 'Selesai Legal';
        }

        return {
            fill: status,
            tipe: 'Legal'
        };
    }

    function pajakNumber(value) {
        if (!legalHasValue(value)) return 0;

        const parsed = parseFloat(String(value).replace(/[^\d.-]/g, ''));
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function getPajakStatus(row) {
        if (row.status_mkdt == 'Batal' || row.is_batal == 1) {
            return {
                fill: 'Batal',
                tipe: 'Status'
            };
        }

        if (!row.id_pajak) {
            return {
                fill: 'Belum Input Pajak',
                tipe: 'Pajak'
            };
        }

        const pphComplete = legalHasValue(row.pph42_tgl_bayar) || legalHasValue(row.pph42_ntpn);
        if (!pphComplete) {
            return {
                fill: 'PPh4(2) Belum Bayar',
                tipe: 'Pajak'
            };
        }

        const ppnRequired = pajakNumber(row.ppn_nilai) > 0;
        if (!ppnRequired) {
            return {
                fill: 'Selesai Pajak',
                tipe: 'Pajak'
            };
        }

        const ppnComplete = legalHasValue(row.ppn_tgl_bayar) || legalHasValue(row.ppn_ntpn);
        const fakturTerbit = legalHasValue(row.ppn_no_faktur);

        if (fakturTerbit && !ppnComplete) {
            return {
                fill: 'Faktur Pajak Terbit',
                tipe: 'Pajak'
            };
        }

        if (!ppnComplete) {
            return {
                fill: 'PPN Belum Bayar',
                tipe: 'Pajak'
            };
        }

        if (!fakturTerbit) {
            return {
                fill: 'PPN Dibayar',
                tipe: 'Pajak'
            };
        }

        return {
            fill: 'Selesai Pajak',
            tipe: 'Pajak'
        };
    }

    function hitung_kavling(fill) {
        let e = fill.fill
        let p = filterwarnahitung[e] ? filterwarnahitung[e] : 0;
        filterwarnahitung[e] = p + 1;

    }

    function set_keterangan_warna() {
        $("#keterangan-warna-here").html(" ")

        //filter
        $("#filter-kategori option").remove()
        $("#filter-kategori").append(`<option value="">Semua</option>`);
        let div = "",
            kv
        // console.log(filterwarna)
        $.each(filterwarna, function(i, v) {
            if (v) {
                div += `
                <div class="divider">
                    <div class="divider-text">${i} ${i == 'Subsidi' || i == 'Komersil' ? 'Dipasarkan' : ''}</div>
                </div>`;
                const statusOrder = i === 'Legal' ? legalStatusOrder : (i === 'Pajak' ? pajakStatusOrder : null);
                const sortedKeys = statusOrder ?
                    statusOrder.filter(key => Object.prototype.hasOwnProperty.call(v, key)).concat(Object.keys(v).filter(key => !statusOrder.includes(key)).sort()) :
                    Object.keys(v).sort();

                // Step 2: Create a new object with sorted keys
                const sortedObj = {};
                sortedKeys.forEach(key => {
                    sortedObj[key] = v[key];
                });

                $.each(sortedObj, function(x, y) {
                    //untuk tambah option di filter
                    $("#filter-kategori").append(`<option value="${x}">${x}</option>`);
                    kv = (x == "Def") ? "Data yang bisa diolah" : x;

                    div += `<div class="form-group row">
                                <div class="btn col-2 ml-1" style="background-color:${y}"></div>
                                <div class="col-9"> ${kv} (${filterwarnahitung[x]})</div>
                            </div>`;
                })
            }
        })
        $("#keterangan-warna-here").html(div)
    }
    //load shape kavling
    function load_kavling(refresh = false) {
        hapus_seleksi();
        filterwarna = {
            Status: null,
            Subsidi: null,
            Komersil: null,
            Legal: null,
            Pajak: null,
            Target: null,
            'Lain-lain': null
        };
        filterwarnahitung = {};

        const fields = [ //untuk pengecekan status kavling yang sudah done
            'nama_jalan',
            'no_kavling',
            'status_mkdt',
            'is_lunas',
            'progres_bangunan',
            'sertifikat_split_no_hgb_induk',
            'sertifikat_split_no_hgb',
            'sertifikat_is_balik_nama',
            'pbb_pecah_nop',
            'pbb_is_balik_nama',
            'pbg_no',
            'ajb_no',
            'pph_tgl_bayar',
            'bphtb_tanggal_pembayaran'
        ];
        const isValidDate = (val) => {
            return typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val) && !isNaN(new Date(val).getTime()) && val !== "0000-00-00";
        };

        siteplan.find('Line').forEach(line => line.destroy());

        let va = $("#pilih-divisi option:selected").val();
        wr_pembangunan = [];
        list_jatuhtempo = [];
        $.ajax({
            url: base_url + 'siteplan/get/all',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_cluster: filter.id_cluster,
                id_jalan: filter.id_jalan,
                id_role: va
            },
            dataType: 'json',
            beforeSend: () => $("#loading").removeClass("hidden"),
            success: function(result) {
                $("#loading").addClass("hidden");
                csrfHash = result.token;
                stroke = fill = strokeWidth = dashed = "";

                let r = result['data'],
                    targetKavling = result.target_kavling || {},
                    data2,
                    hit,
                    tp_rumah,
                    subsidi;



                for (var p = 0; p < r.length; p++) {
                    tp_rumah = r[p].tipe_rumah
                    no_tp_rumah = r[p].no_tipe_rumah

                    subsidi = (r[p].is_subsidi == 1) ? "Subsidi" : "Komersil"

                    //set default shape color
                    // set_fill("#fff67a", "#000000", 0, null)
                    hit = {
                        fill: set_fill2("Def"),
                        tipe: 'Lain-lain'
                    }

                    //add list to belum selesai bangun
                    if (r[p].tanggal_rencana_selesai_pembangunan != null) {
                        if (r[p].tanggal_selesai_pembangunan == null) {
                            if (daysBetween(today_date, r[p].tanggal_rencana_selesai_pembangunan) < 3) {
                                wr_pembangunan.push({
                                    id_kavling: r[p].id_kavling,
                                    id_produksi: r[p].id_produksi,
                                    id_mkdt: r[p].id_mkdt,
                                    id_keuangan: r[p].id_keuangan,
                                    id_tipe: r[p].id_tipe,
                                    id_gambar_kerja: r[p].id_gambar_kerja,
                                    progres: r[p].progres_bangunan ? r[p].progres_bangunan : 0,
                                    nama_jalan: r[p].nama_jalan,
                                    no_kavling: r[p].no_kavling,
                                    no_tipe_rumah: r[p].no_tipe_rumah,
                                    tipe: r[p].tipe_rumah,
                                    keterangan: r[p].keterangan_produksi,
                                    tanggal_pembangunan: r[p].tanggal_pembangunan,
                                    tanggal_rencana_selesai_pembangunan: r[p].tanggal_rencana_selesai_pembangunan,
                                    tanggal_selesai_pembangunan: r[p].tanggal_selesai_pembangunan
                                })
                            }
                        }
                    }

                    //add to list jatuh tempo
                    const today = new Date();
                    const sevenDaysLater = new Date();
                    sevenDaysLater.setDate(today.getDate() + 7);


                    if (r[p].jatuh_tempo_tgl) {
                        const jatuhTempo = new Date(r[p].jatuh_tempo_tgl);

                        if (jatuhTempo <= today && jatuhTempo <= sevenDaysLater) {
                            list_jatuhtempo.push(r[p].id_kavling)
                        }
                    }


                    // if (r[p].tanggal_selesai_pembangunan != '0000-00-00' || r[p].tanggal_selesai_pembangunan != '' || r[p].tanggal_selesai_pembangunan != null) {

                    // }

                    if (r[p].harga_akhir) {
                        tp_rumah = r[p].tipe_rumah
                        no_tp_rumah = r[p].no_tipe_rumah
                        hit = set_fill2(r[p].tipe_rumah)
                        hit = {
                            fill: hit,
                            tipe: subsidi
                        }
                        // if (r[p].is_subsidi == 1)
                        //     hit = set_fill2("Subsidi")
                        // else
                        //     hit = set_fill2("Non-Subsidi")
                    }


                    if (r[p].status_mkdt) {
                        hit = set_fill2(r[p].status_mkdt)
                        if (hit == "Akad" || hit == "Booking") {
                            hit = hit + " " + subsidi
                        }
                    } else {
                        if (r[p].progres_bangunan == "100") {
                            // jika sudah selesai
                            hit = set_fill2("Ready Stock")
                        }
                    }


                    if (va == 3) { //keuangan
                        if (r[p].is_lunas == 0 || r[p].is_lunas == null || r[p].is_lunas == "undefined") {
                            if (r[p].jatuh_tempo_tgl != null && r[p].jatuh_tempo_tgl != "0000-00-00") {
                                if (daysBetween(today_date, r[p].jatuh_tempo_tgl) < 7)
                                    hit = set_fill2('Jatuh Tempo')
                            }

                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        } else if (r[p].is_lunas == 1) {
                            hit = set_fill2('Lunas')
                            if (r[p].status_mkdt == 'Akad') {
                                if (r[p].dajam_selesai == 0)
                                    hit = set_fill2('Dajam Belum Cair')
                                else
                                    hit = set_fill2('Akad')
                            }
                        }

                        if (r[p].is_batal == 1)
                            hit = set_fill2("Batal")
                    } else if (va == 4) { //mkdt
                        if (r[p].perintah_bangun == 1)
                            hit = set_fill2("Perintah Bangun")

                        //status kavling
                        if (r[p].status_mkdt == "Batal") { //jika batal
                            if (r[p].is_batal == 1)
                                hit = set_fill2("Batal")
                            else
                                hit = set_fill2(r[p].status_mkdt)
                        } else if (r[p].status_mkdt == "Akad") { //jika akad
                            hit = set_fill2('Akad')
                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        } else if (r[p].status_mkdt == "Booking") {
                            //jika booking
                            if (r[p].booking_tgl != null && r[p].booking_tgl != "0000-00-00") {
                                // if (r[p].is_kpr == 1)
                                //     hit = set_fill2('KPR')
                                // else if (r[p].is_kpr == 0)
                                //     hit = set_fill2('Tunai')
                                // if (r[p].mkdt_is_subsidi == 1) {
                                //     hit = set_fill2('Subsidi')
                                // } else if (r[p].mkdt_is_subsidi == 0) {
                                //     hit = set_fill2('Non-Subsidi')
                                // }
                            }
                            //jika turun sp3k
                            if (r[p].sp3k_tgl != null && r[p].sp3k_tgl != "0000-00-00") {
                                hit = set_fill2('SP3K')
                            }
                            if (r[p].is_sudah_isi_tagihan != 1) {
                                hit = set_fill2('SPPTB Belum Lengkap')
                            }
                        }
                    } else if (va == 5) { //legal
                        hit = getLegalStatus(r[p]);

                        // if (
                        //     r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" &&
                        //     r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" &&
                        //     r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" &&
                        //     r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" &&
                        //     r[p].sertifikat_no_hgb != null &&
                        //     r[p].sertifikat_no_split != null &&
                        //     r[p].imb_no_induk != null &&
                        //     r[p].imb_no_split != null &&
                        //     r[p].nop_pbb != null &&
                        //     r[p].pph != null
                        // )
                        //     hit = set_fill2("Sudah Diisi")
                        // else {
                        //     if (
                        //         r[p].sertifikat_tgl != null && r[p].sertifikat_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00" ||
                        //         r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00" ||
                        //         r[p].imb_tgl != null && r[p].imb_tgl != "0000-00-00" ||
                        //         r[p].bphtb_tgl != null && r[p].bphtb_tgl != "0000-00-00" ||
                        //         r[p].sertifikat_no_hgb != null ||
                        //         r[p].sertifikat_no_split != null ||
                        //         r[p].imb_no_induk != null ||
                        //         r[p].imb_no_split != null ||
                        //         r[p].nop_pbb != null ||
                        //         r[p].pph != null
                        //     )
                        //         hit = set_fill2("Sebagian Diisi")
                        // }
                        // if (r[p].sertifikat_masa_berlaku != null && r[p].sertifikat_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].sertifikat_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                        // if (r[p].bphtb_masa_berlaku != null && r[p].bphtb_masa_berlaku != "0000-00-00") {
                        //     if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 30)
                        //         hit = set_fill2("h-30 Kadaluarsa") //warna merah
                        //     else if (daysBetween(today_date, r[p].bphtb_masa_berlaku) < 60)
                        //         hit = set_fill2("h-60 Kadaluarsa") // warna orange
                        // }

                    } else if (va == 7) { //produksi
                        // if (r[p].status_mkdt)
                        //     hit = set_fill2(r[p].status_mkdt)
                        if (r[p].status_mkdt == "Akad") {
                            hit = set_fill2("Akad " + subsidi)
                            if (r[p].progres_bangunan == null)
                                hit = set_fill2("Pembangunan") // warna merah

                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")
                            // if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                            //     hit = set_fill2("Pembangunan") // warna merah
                            // }else if (parseInt(r[p].progres_bangunan) == 100) {
                            //     // jika sudah selesai
                            //     hit = set_fill2("Akad "+ subsidi)
                            // }
                        } else if (r[p].status_mkdt == "Booking") {
                            hit = set_fill2("Booking " + subsidi)

                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")

                            // if (r[p].perintah_bangun == 1)
                            //     hit = set_fill2("Perintah Bangun")

                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Bangunan 100%")
                            }
                        } else {
                            if (r[p].perintah_bangun == 1)
                                hit = set_fill2("Perintah Bangun")
                            if (parseInt(r[p].progres_bangunan) > 0 && parseInt(r[p].progres_bangunan) < 100) {
                                hit = set_fill2("Pembangunan") // warna merah
                            } else if (parseInt(r[p].progres_bangunan) == 100) {
                                // jika sudah selesai
                                hit = set_fill2("Ready Stock") // warna merah
                            }
                        }

                        //jika ada komplain (dari sales)
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain")

                    } else if (va == 8) { //sales
                        //jika bangunan sudah 100%
                        if (r[p].progres_bangunan == "100") {
                            hit = set_fill2("Pembangunan Selesai") // warna biru
                            if (r[p].id_mkdt == null)
                                hit = set_fill2("Ready Stock") // warna biru
                        }

                        //jika sudah akad
                        if (r[p].status_mkdt == "Akad")
                            hit = set_fill2("Akad") //warna ungu
                        //jika ada komplain
                        if (r[p].status_komplain == 1 || r[p].status_komplain == 2 || r[p].status_komplain == 3)
                            hit = set_fill2("Komplain") //warna merah
                        // jika sudah dicek
                        if (r[p].is_checked == 1)
                            hit = set_fill2("Sudah dicek") // warna orange
                        // jika sudah dicek
                        if (r[p].is_serah_terima == 1)
                            hit = set_fill2("Serah Terima") // warna hijau

                    } else if (va == 9) {
                        if (r[p].harga_akhir) {
                            tp_rumah = r[p].tipe_rumah
                            no_tp_rumah = r[p].no_tipe_rumah
                            hit = set_fill2(r[p].tipe_rumah)

                            //ubah var hit ke object
                            hit = {
                                fill: hit,
                                tipe: subsidi
                            }

                        }
                    } else if (va == 10) { //pajak
                        hit = getPajakStatus(r[p]);
                    }


                    // }



                    let targetInfo = null;
                    if (va == 11) {
                        targetInfo = targetKavling[r[p].id_kavling] || null;
                        if (targetInfo) {
                            hit = {
                                fill: 'Target ' + targetInfo.tahun_target,
                                tipe: 'Target'
                            }
                        } else {
                            hit = {
                                fill: 'Belum Target',
                                tipe: 'Target'
                            }
                        }
                    }

                    const fieldChecks = {
                        'sertifikat_is_balik_nama': (val) => val === 'Sudah',
                        'pbb_is_balik_nama':        (val) => val === 'Sudah',
                        'progres_bangunan':         (val) => parseInt(val) === 100,
                        'is_lunas':                 (val) => val == 1,
                    };
                    const isComplete = fields.every(field => {
                        const val = r[p][field];

                        if (fieldChecks[field]) {
                            return fieldChecks[field](val);
                        }

                        if (field.includes('tgl') || field.includes('tanggal')) {
                            return isValidDate(val);
                        }

                        return val !== null && val !== '';
                    });

                    if (va != 11 && va != 5 && va != 10 && isComplete) {
                        hit = set_fill2("Selesai");
                    }


                    //harga jual
                    let id_hargajual = r[p].harga_akhir;
                    r[p].harga_akhir = (r[p].hargajual) ? num_format(r[p].hargajual) + " (Per " + format_date(r[p].tgl_harga) + ")" : '-';



                    if (typeof hit !== 'object') {
                        hit = {
                            fill: hit,
                            tipe: 'Status'
                        }
                    }

                    // return;

                    //set untuk filter warna
                    filterwarna[hit.tipe] = {
                        ...filterwarna[hit.tipe],
                        [hit.fill]: get_kategori_color(hit.fill)
                    }


                    // console.log(hit.fill, conf[hit.fill].fill);

                    hitung_kavling(hit)
                    //data di tiap kavling harus disesuaikan dengan divisi yang dipilih
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        // lineCap: 'round',
                        // lineJoin: 'round',
                        // stroke: stroke,
                        fill: get_kategori_color(hit.fill),
                        // strokeWidth: strokeWidth,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        kategori: hit.fill,
                        data: {
                            nama_jalan: r[p].nama_jalan,
                            no_kavling: r[p].no_kavling,
                            id_produksi: r[p].id_produksi,
                            id_legal: r[p].id_legal,
                            id_keuangan: r[p].id_keuangan,
                            id_sales: r[p].id_sales,
                            id_planning: r[p].id_planning,
                            id_mkdt: r[p].id_mkdt,
                            id_umum: r[p].id_umum,
                            id_direksi: r[p].id_direksi,
                            tipe: 'kavling',
                            status_tanah: r[p].status_tanah,
                            luas_tanah: r[p].luas_tanah,
                            is_batal: r[p].is_batal,
                            // total_biaya: ktotal_biaya,
                            // sudah_bayar: ksudah_bayar
                        },
                        data2: {
                            id_hargajual: id_hargajual,
                            status_mkdt: r[p].status_mkdt,
                            id_tipe: r[p].id_tipe,
                            tipe_rumah: tp_rumah,
                            no_tipe_rumah: no_tp_rumah,
                            id_gambar_kerja: r[p].id_gambar_kerja,
                            harga_akhir: r[p].harga_akhir,
                            harga_akhir_tgl: r[p].harga_akhir_tgl,
                            harga_akhir_oleh: r[p].harga_akhir_oleh_username,
                            id_serah_terima: r[p].id_serah_terima,
                            id_komplain: r[p].id_komplain,
                            target: targetInfo,
                        },
                        id: 'kav' + r[p].id_kavling
                    });
                    siteplan.add(kav);
                }
                set_keterangan_warna()
                cek_tanggal_pembangunan(refresh)
                handlePendingSiteplanUrgentAction()
            },
            error: function(xhr, st, err) {
                $("#loading").addClass("hidden")
                return swal("error", err);
            },
        });

        //load jalan fasos rth
        $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek,
                id_role: va
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(result) {
                stroke = ""
                fill = ""
                strokeWidth = ""
                dashed = ""

                var r = result.data

                for (var p = 0; p < r.length; p++) {
                    if (r[p].tipe == "jalan")
                        set_fill("#ccc", "#000", "0", null) // warna abu
                    else if (r[p].tipe == "fasos")
                        set_fill("#9000ff", "#000", "0", null) // warna ungu
                    else if (r[p].tipe == "rth")
                        set_fill("#0f0", "#000", "0", null) // warna merah
                    kav = new Konva.Line({
                        points: JSON.parse("[" + r[p].points + "]"),
                        fill: fill,
                        dash: dashed,
                        opacity: 1,
                        closed: true,
                        globalCompositeOperation: 'multiply',
                        data: {
                            tipe: r[p].tipe,
                            nama_jalan: r[p].nama_jalan,
                        },
                        data2: {},
                        id: 'others' + r[p].id
                    });
                    siteplan.add(kav);
                }
            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                return;
            }
        });
        group.hide();
    }

    //zoom
    var scaleBy = 1.1;
    stage.on('wheel', (e) => {
        // stop default scrolling
        menuNode.style.display = 'none';
        e.evt.preventDefault();

        var oldScale = stage.scaleX();
        var pointer = stage.getPointerPosition();

        var mousePointTo = {
            x: (pointer.x - stage.x()) / oldScale,
            y: (pointer.y - stage.y()) / oldScale,
        };

        // how to scale? Zoom in? Or zoom out?
        let direction = e.evt.deltaY > 0 ? -1 : 1;

        // when we zoom on trackpad, e.evt.ctrlKey is true
        // in that case lets revert direction
        if (e.evt.ctrlKey) {
            direction = -direction;
        }

        var newScale = direction > 0 ? oldScale * scaleBy : oldScale / scaleBy;

        stage.scale({
            x: newScale,
            y: newScale
        });

        group.scale({
            x: 1 / newScale,
            y: 1 / newScale
        })
        var newPos = {
            x: pointer.x - mousePointTo.x * newScale,
            y: pointer.y - mousePointTo.y * newScale,
        };
        stage.position(newPos);
    });

    //clear selction
    var idss, idsb, idst, idstb, ajal, seljal;

    function hapus_seleksi() {
        line_ms.points([0, 0])

        bml_old = 0;
        dtt_first = [];

        idss = stage.find('#sel')[0]; //find selection line
        idst = stage.find('#tsel')[0]; //find selection text


        // ajal = stage.find('#ajal')[0];
        // if(ajal) ajal.destroy();

        //remove point select jalan
        seljal = stage.find('#seljal');
        for (let p = 0; p <= seljal.length; p++) {
            if (seljal[p])
                seljal[p].destroy();
        }
        dtt = [];



        if (idss)
            idss.destroy(); //destroy shape
        if (idst)
            idst.destroy(); //destroy shape

        hapus_seleksi_batch() //destroy multiple selection
    }
    //destroy multiple selection
    function hapus_seleksi_batch() {
        idsb = stage.find('#sel'); //find selection line
        idstb = stage.find('#tsel'); //find selection text

        for (let p = 0; p <= idsb.length; p++) {
            if (idsb[p])
                idsb[p].destroy();

            if (idstb[p])
                idstb[p].destroy();
        }

        batchMask = [];
        batchdtt = [];
        editdtt = [];
        siteplan.draw();
    }

    var editdtt = [];

    //event klik kavling
    masked.on('dblclick', function(e) {
        if (!addMode) {
            if (e.evt.button === 0 && e.target.attrs.id) {
                //open detail modal
                lihat_detail();
            }
        }
    })

    //hide tooltip on tap at siteplan
    siteplan.on('tap', function() {
        group.hide();
    })

    //panggil tooltip saat di tap di ponsel
    siteplan.on('tap', function(e) {
        var data = e.target.attrs;

        //posisi tooltip
        var mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });

        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir +
                ""
            );
            group.moveToTop();
            group.show(); //show tooltip
        }
    })

    siteplan.on('click tap', function(e) {
        var k = e.target, //get shape
            sh = k.attrs, //get attribut shape
            role = $('#pilih-divisi option:selected').val(),
            id_kavling = ''

        if (!sh.id) return false;
        id_kavling = sh.id.substr(3)

        if (!isManualSelectionActive()) {
            addMode = e.evt.ctrlKey;

            //jika hak akses = planning dan pilihan data yang ditampilkan = planning
            //admin, pro
            if ([1, 3, 4, 6, 7, 11].includes(roleid)) {
                if (addMode) {
                    if (sh.data.tipe != "kavling") {
                        return swal('error', 'Terjadi kesalahan', 'Multiple Selection hanya untuk data kavling ')
                    }

                    editdtt.push(sh)
                    drawBorderEdit(sh)
                } else {
                    hapus_seleksi();
                    editdtt.push(sh)
                    drawBorderEdit(sh)
                }
            } else {
                hapus_seleksi();
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
        }
    })

    function refresh_manual_selection_points() {
        dtt = [];
        var sj = stage.find("#seljal");

        for (let u = 0; u < sj.length; u++) {
            dtt.push(Math.trunc(sj[u].attrs.x), Math.trunc(sj[u].attrs.y))
        }

        line_ms.points(dtt)
        masked.batchDraw()
        return dtt;
    }

    function undo_manual_selection() {
        var sj = stage.find("#seljal");

        if (!sj.length) {
            return swal('error', 'Terjadi kesalahan', 'Belum ada titik seleksi manual')
        }

        sj[sj.length - 1].destroy();
        refresh_manual_selection_points();
    }

    $(document).on('keydown', function(e) {
        var tagName = e.target && e.target.tagName ? e.target.tagName.toLowerCase() : '';

        if (!isManualSelectionActive() || ['input', 'textarea', 'select'].includes(tagName)) {
            return;
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
            e.preventDefault();
            undo_manual_selection();
        }
    });

    stage.on('click tap', function(e) {
        if (isManualSelectionActive()) {
            var pos = this.getRelativePointerPosition();

            var dot = new Konva.Circle({
                x: pos.x,
                y: pos.y,
                fill: 'red',
                radius: 5,
                id: "seljal",
                draggable: true
            })
            manual_selection.add(dot);
            refresh_manual_selection_points();
        }
    })
    masked.add(line_ms)
    masked.add(manual_selection)

    manual_selection.on('dragend', function(e) {
        refresh_manual_selection_points();
    })

    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })
    //even mouse move data kavling
    var data, mousePos, persentase;
    siteplan.on('mousemove', function(e) {
        data = e.target.attrs;
        // console.log(data);

        //posisi tooltip
        mousePos = stage.getRelativePointerPosition();
        group.position({
            x: mousePos.x + 20,
            y: mousePos.y + 5,
        });
        //text tooltip
        if (data.data) {
            if (!data.data.nama_jalan || !data.data.no_kavling)
                return;
            tooltip.text(
                data.data.nama_jalan +
                " No. " + data.data.no_kavling + "\n" +
                data.data2.no_tipe_rumah + "\n" +
                data.data2.tipe_rumah + " ( " + data.data.luas_tanah + " / " + data.data.status_tanah + ") \n" +
                "HJ: Rp. " + data.data2.harga_akhir
            );
            // }
            group.moveToTop();
            group.show(); //show tooltip

        }

    })

    //highligh kavling
    siteplan.on('mouseover', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 4);
        sh.setAttr("stroke", "black");
    })

    //hide tooltip
    siteplan.on('mouseout', function(e) {
        var sh = e.target;
        sh.setAttr("strokeWidth", 0);
        group.hide();
    })

    function getDistance(p1, p2) {
        return Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
    }

    function getCenter(p1, p2) {
        return {
            x: (p1.x + p2.x) / 2,
            y: (p1.y + p2.y) / 2,
        };
    }

    var lastCenter = null;
    var lastDist = 0;
    stage.on('touchmove', function(e) {
        e.evt.preventDefault();
        var touch1 = e.evt.touches[0];
        var touch2 = e.evt.touches[1];

        if (touch1 && touch2) {
            // if the stage was under Konva's drag&drop
            // we need to stop it, and implement our own pan logic with two pointers
            if (stage.isDragging()) {
                stage.stopDrag();
            }

            var p1 = {
                x: touch1.clientX,
                y: touch1.clientY,
            };
            var p2 = {
                x: touch2.clientX,
                y: touch2.clientY,
            };

            if (!lastCenter) {
                lastCenter = getCenter(p1, p2);
                return;
            }
            var newCenter = getCenter(p1, p2);

            var dist = getDistance(p1, p2);

            if (!lastDist) {
                lastDist = dist;
            }

            // local coordinates of center point
            var pointTo = {
                x: (newCenter.x - stage.x()) / stage.scaleX(),
                y: (newCenter.y - stage.y()) / stage.scaleX(),
            };

            var scale = stage.scaleX() * (dist / lastDist);

            stage.scaleX(scale);
            stage.scaleY(scale);

            // calculate new position of the stage
            var dx = newCenter.x - lastCenter.x;
            var dy = newCenter.y - lastCenter.y;

            var newPos = {
                x: newCenter.x - pointTo.x * scale + dx,
                y: newCenter.y - pointTo.y * scale + dy,
            };

            group.scale({
                x: 1 / scale,
                y: 1 / scale
            })

            stage.position(newPos);

            lastDist = dist;
            lastCenter = newCenter;
        }
    });

    stage.on('touchend', function() {
        lastDist = 0;
        lastCenter = null;
    });
</script>
<script src="<?= base_url() ?>assets/js/siteplan-detail-modal.js?<?= filemtime(FCPATH . 'assets/js/siteplan-detail-modal.js') ?>"></script>
<script>
    function isi_data() {
        if (editdtt.length == 0)
            return swal('error', 'Terjad Kesalahan', 'Tidak ada kavling yang dipilih')

        //bug isi data with addmode
        if (editdtt.length > 1) {
            swal('error', 'Terjad Kesalahan', 'Tidak bisa merubah data lebih dari 1 kavling')
            hapus_seleksi();
            return;
        }

        let role,
            sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        //jika admin login
        if (roleid == 1)
            role = $('#pilih-divisi option:selected').val()
        else
            role = roleid

        if (role == 7) { //produksi
            open_produksi(sh, role, id_kavling)
        } else if (role == 5) { //legal
            open_legal(sh, role, id_kavling)
        } else if (role == 4) { //mkdt
            open_mkdt(sh, role, id_kavling)
        } else if (role == 3) { //keunagan
            if (!sh.data.id_mkdt) {
                return swal('error', 'Terjad Kesalahan', `Belum ada data konsumen di kavling ${sh.data.nama_jalan}, No. ${sh.data.no_kavling}`)
            }
            open_keuangan(sh, role, id_kavling)
        } else if (role == 6) { //planning
            if (!addMode) {
                hapus_seleksi();
                open_planning(sh, role, id_kavling)
            } else {
                editdtt.push(sh)
                drawBorderEdit(sh)
            }
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 8) { //sales promotion
            $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");
            $('#modal_divisi' + role).modal({
                backdrop: 'static',
                keyboard: false
            });
        } else if (role == 10) { //pajak
            open_pajak(sh, role, id_kavling);
        }
    }

    <?php if ($k == 1 || $k == 6): ?><?php endif; ?>
    <?php if ($k == 1 || $k == 3): ?> <?php endif; ?>
    /********************************* mkdt *******************************************/
    $("#fm-mkdt .num").change(function() {
        // total()
    })
    //tidak dipakai
    function total(id_form = "") {
        var harga_jual = removeComma(($(id_form + " #harga_jual").val() == '') ? 0 : $(id_form + " #harga_jual").val()),
            harga_diskon = removeComma(($(id_form + " #harga_diskon").val() == '') ? 0 : $(id_form + " #harga_diskon").val()),
            harga_penambahan = removeComma(($(id_form + " #harga_penambahan").val() == '') ? 0 : $(id_form + " #harga_penambahan").val()),
            harga_administrasi = removeComma(($(id_form + " #harga_administrasi").val() == '') ? 0 : $(id_form + " #harga_administrasi").val()),
            harga_ppn = removeComma(($(id_form + " #harga_ppn").val() == '') ? 0 : $(id_form + " #harga_ppn").val()),
            harga_bphtb = removeComma(($(id_form + " #harga_bphtb").val() == '') ? 0 : $(id_form + " #harga_bphtb").val()),
            harga_biaya_proses = removeComma(($(id_form + " #harga_biaya_proses").val() == '') ? 0 : $(id_form + " #harga_biaya_proses").val()),
            harga_kpr = removeComma(($(id_form + " #harga_kpr").val() == '') ? 0 : $(id_form + " #harga_kpr").val()),
            total_biaya = 0;

        total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

        $(id_form + " #total_biaya").val(total_biaya).keyup();

        // console.log(total_biaya)

        $("#total_biaya2").val(total_biaya).keyup();

    }

    function lihat_total() {
        var harga_jual = removeComma(($("#detail_harga_jual").val() == '') ? 0 : $("#detail_harga_jual").val()),
            harga_diskon = removeComma(($("#detail_harga_diskon").val() == '') ? 0 : $("#detail_harga_diskon").val()),
            harga_penambahan = removeComma(($("#detail_harga_penambahan").val() == '') ? 0 : $("#detail_harga_penambahan").val()),
            harga_administrasi = removeComma(($("#detail_harga_administrasi").val() == '') ? 0 : $("#detail_harga_administrasi").val()),
            harga_ppn = removeComma(($("#detail_harga_ppn").val() == '') ? 0 : $("#detail_harga_ppn").val()),
            harga_bphtb = removeComma(($("#detail_harga_bphtb").val() == '') ? 0 : $("#detail_harga_bphtb").val()),
            harga_biaya_proses = removeComma(($("#detail_harga_biaya_proses").val() == '') ? 0 : $("#detail_harga_biaya_proses").val()),
            harga_kpr = removeComma(($("#detail_harga_kpr").val() == '') ? 0 : $("#detail_harga_kpr").val()),
            total_biaya = 0;

        total_biaya = (harga_jual - harga_kpr) - harga_diskon + harga_penambahan + harga_ppn + harga_bphtb + harga_biaya_proses;

        $("#detail_total_biaya").val(total_biaya).keyup();

    }
    //sum tagihan

    function sum_tg(e = 0, bb = '') {
        e = parseFloat(removeComma(e))

        let total_keu = parseFloat(removeComma($("#mk-total_tot").val()) || 0)
        let cicilan_keu = parseFloat(removeComma($("#mk-total_cicilan_um").val()) || 0)

        if (cicilan_keu + e > total_keu)
            $("#nominal").val(total_keu - cicilan_keu).keyup()
    }

    var it = 0;
    /***************** list tagihan ****************/
    function tambah_(e = '') {
        let a = (e == '_bb') ? e : '_um'
        if ($("#mk-total_cicilan_um").val() == $("#mk-total_tot").val()) {
            swal('error', "Tidak bisa menambahkan tagihan", "Total tagihan tidak bisa melebeihi total harus dibayar", false);
            return false;
        } else {
            if (!$("#berita_acara" + e).val() || !$("#nominal" + e).val() || !$("#jatuh_tempo_tgl" + e).val()) {
                swal('error', "Nominal dan jatuh tempo tidak boleh kosong", null, false);
                return false;
            }
            Swal.fire({
                title: 'Simpan data?',
                text: "Pastikan data sudah terisi dengan benar!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya!',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: !1
            }).then(function(t) {
                if (t.value) {
                    tambah(e)
                }
            })
        }
    }

    function tambah(e = '') {
        let i = 'lk' + it

        if (state.data_um[$("#id_list_keu" + e).val()])
            i = $("#id_list_keu" + e).val()

        state.data_um[i] = ({
            id_list_keu: i,
            id_keuangan: $("#id_keuangan").val(),
            berita_acara: $("#berita_acara").val(),
            nominal: $("#nominal").val(),
            jatuh_tempo_tgl: $("#jatuh_tempo_tgl").val(),
        })

        tambah_ketagihan(e)

        fp = flatpickr("#jatuh_tempo_tgl", {
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
        })

        var d = new Date(
            $("#jatuh_tempo_tgl").val()
        ).fp_incr(30);

        fp.setDate(d);

        it += 1;
    }

    function removeFromTable(x, y = null) {
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data tidak bisa dipulihkan!",
            type: 'danger',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya!',
            confirmButtonClass: 'btn btn-primary',
            cancelButtonClass: 'btn btn-danger ml-1',
            buttonsStyling: !1
        }).then(function(t) {
            if (t.value) {
                $.ajax({
                    url: base_url + 'Keuangan/isSudahBayar/' + editdtt[0].data.id_mkdt,
                    type: 'get',
                    dataType: 'json',
                    success: function(r) {
                        csrfHash = r.token;

                        if (r.success === false) {
                            return swal('error', r.messages)
                        }

                        if (y == '_bb') delete state.data_bb[x];
                        else delete state.data_um[x];
                        tambah_ketagihan();
                    },
                    error: function() {
                        return swal('error', 'Terjadi kesalahan')
                    }
                });

            }
        })

    }

    function editFromTable(x) {
        var d = state.data_um[x]

        $("#id_list_keu").val(x);
        $("#berita_acara").val(d.berita_acara);
        $("#nominal").val(d.nominal).keyup();
        $("#jatuh_tempo_tgl").val(d.jatuh_tempo_tgl);
        $("#tambah_list").html("Simpan Perubahan")
    }


    function rowHTML({
        title,
        date,
        amount,
        key,
        suffix = ''
    }) {
        return `
    <tr data-key="${key}" data-suffix="${suffix}">
      <td>${title}</td>
      <td>${format_date(date)}</td>
      <td>${num_format(amount)}</td>
      <td>
        <div class="btn-group">
          <button type="button" class="btn btn-outline-danger waves-effect btn-sm js-remove">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </td>
    </tr>`;
    }

    function sectionHTML({
        rows,
        label,
        suffix = ''
    }) {
        let total = 0;
        const body = rows.map(r => {
            total += Number(removeComma(r.amount));
            return rowHTML({
                ...r,
                suffix
            });
        }).join('');
        const foot = `
                    <tr class="table-secondary">
                        <td colspan="2">Total Tagihan </td>
                        <td>${num_format(total)}</td>
                        <td></td>
                    </tr>`;
        return {
            html: body + foot,
            total
        };
    }

    function tambah_ketagihan() {
        const umRows = Object.keys(state.data_um || {}).map(k => ({
            key: k,
            title: state.data_um[k].berita_acara,
            date: state.data_um[k].jatuh_tempo_tgl,
            amount: state.data_um[k].nominal
        }));

        // const bbRows = Object.keys(state.data_bb || {}).map(k => ({
        //     key: k,
        //     title: state.data_bb[k].berita_acara_bb,
        //     date: state.data_bb[k].jatuh_tempo_tgl_bb,
        //     amount: state.data_bb[k].nominal_bb
        // }));

        const um = sectionHTML({
            rows: umRows,
            label: 'Tagihan Uang Muka',
            suffix: ''
        });

        // const bb = sectionHTML({
        //     rows: bbRows,
        //     label: 'Tagihan Biaya Biaya',
        //     suffix: '_bb'
        // });

        // 1x write ke DOM
        $("#list_cicilan_here").html(um.html);

        // update total & UI state
        $("#mk-total_cicilan_um").val(um.total).trigger('change');
        // $("#total_cicilan_bb").val(bb.total).trigger('change');
        $("#id_list_keu").val('');
        $("#id_list_keu_bb").val('');
        $("#nominal, #nominal_bb").trigger('change');
        // $("#tambah_list").text("+ Cicilan UM");
        // $("#tambah_list_bb").text("+ Cicilan BB");
    }

    // Event delegation untuk remove
    $(document).on('click', '#list_cicilan_here .js-remove', function() {
        const $tr = $(this).closest('tr');
        const key = $tr.data('key');
        const suffix = $tr.data('suffix');
        removeFromTable(String(key), String(suffix || ''));
    });

    $("#pilih-divisi").select2()
    $("#filter-kategori").select2()
    $("#filter-kategori").change(function() {
        filterKategori(this.value)
    })

    function filterKategori(kat) {
        siteplan.find('Line').forEach(function(i, v) {
            if (kat == "") {
                return i.visible(true)
            }
            if (i.attrs.kategori == kat) {
                i.visible(true)
            } else {
                i.visible(false)
            }
        })
    }
</script>

<script>
    // stage.add(siteplan, masked, datal);
    stage.add(siteplan, masked);
    stage.draw();
    siteplanStageReady = true;
    tryInitSiteplanCanvas();

    function downloadURI(uri, name, callback) {
        var link = document.createElement('a');
        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        delete link;

        callback()
    }

    function export_siteplan() {
        $('#btn-export-siteplan').prop("disabled", true);
        $('#btn-export-siteplan').html('Export<i class="fa fa-spinner fa-spin"></i>');

        const stageh = stage.height()
        const stagew = stage.width()
        const stages = stage.scale()
        const stagep = stage.position()

        stage.height(imageObj.height)
        stage.width(imageObj.width)

        stage.position({
            x: 0,
            y: 0
        })

        var dataURL = stage.scale({
            x: 1,
            y: 1
        }).toDataURL({
            pixelRatio: 1
        });

        const nama_file = "Siteplan " + dt_proyek.nama_proyek + " Per " + format_date(today_date) + ".png";

        downloadURI(dataURL, nama_file, function() {
            stage.height(stageh)
            stage.width(stagew)
            stage.scale(stages)
            stage.position(stagep)
            $('#btn-export-siteplan').prop("disabled", false);
            $('#btn-export-siteplan').html('Export');
        })
    }

    //context menu


    //autofit
    function fitStageIntoParentContainer() {
        var container = document.querySelector('#stage-parent');
        if (!container || !container.offsetWidth) {
            return;
        }

        // now we need to fit stage into parent container
        var containerWidth = container.offsetWidth;

        // but we also make the full scene visible
        // so we need to scale all objects on canvas
        var scale = containerWidth / sceneWidth;



        stage.width(sceneWidth * scale);
        stage.height(syncSiteplanMainHeight(sceneHeight));
        new Konva.Tween({
            node: stage,
            duration: 0.5,
            scaleX: scale,
            scaleY: scale,
            easing: Konva.Easings.EaseInOut,
        }).play();
        // stage.scale({
        //     x: scale,
        //     y: scale
        // });
    }

    fitStageIntoParentContainer();

    let siteplanResizeTimer = null;
    $(window).on('resize', function() {
        clearTimeout(siteplanResizeTimer);
        siteplanResizeTimer = setTimeout(function() {
            fitStageIntoParentContainer();
        }, 150);
    });

    function open_setting() {
        // $("#modal-setting-filter").modal()
        $("#modalEwe").modal()
    }

    function filter_option() {
        filter.id_cluster = $("#filter-id_cluster").val()
        filter.id_jalan = $("#filter-id_jalan").val()
        load_kavling()
    }

    function hapus_filter_option() {
        $('#filter-id_cluster').val(null).trigger('change');
        filter_option()
    }

    //select2 cluster
    $("#filter-id_cluster").select2({
        placeholder: "Pilih Cluster",
        allowClear: true,
        ajax: {
            url: base_url + "/cluster/getAll",
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function(params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term,
                    id_proyek: dt_proyek.id_proyek
                };
            },
            processResults: function(r) {
                csrfHash = r.token

                let results = [];
                $.each(r.data, function(index, item) {
                    results.push({
                        id: item[0],
                        text: item[3]
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })
    // on select cluster
    $("#filter-id_cluster").on("change", function(e) {
        $('#filter-id_jalan').val(null).trigger('change');
        if (this.value)
            $("#filter-id_jalan").prop("disabled", false)
        else
            $("#filter-id_jalan").prop("disabled", true)
    });
    $("#filter-id_jalan").select2({
        placeholder: "Pilih Blok",
        allowClear: true,
        ajax: {
            url: base_url + "/jalan/getAll",
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function(params) {
                return {
                    [csrfName]: csrfHash,
                    search: params.term,
                    id_cluster: $("#filter-id_cluster").val(),
                    id_proyek: dt_proyek.id_proyek
                };
            },
            processResults: function(r) {
                csrfHash = r.token

                let results = [];
                $.each(r.data, function(index, item) {
                    results.push({
                        id: item[0],
                        text: item[3]
                    });
                });

                return {
                    results: results
                };
            },
            cache: true
        },
    })

    //remove bug arrow select2
    $(".select2-selection__arrow").css("pointer-events", "none")


    //context menu
    let currentShape;
    document.getElementById('menu-btn-lihat_detail').addEventListener('click', () => {
        if (currentShape.target.attrs.id) {
            //open detail modal
            lihat_detail();
        }
    });
    var menuNode = document.getElementById('menu');
    window.addEventListener('click', () => {
        // hide menu
        menuNode.style.display = 'none';
    });
    stage.on('contextmenu', function(e) {
        // prevent default behavior
        e.evt.preventDefault();

        if (e.target === stage) {
            // if we are on empty place of the stage we will do nothing
            return;
        }
        currentShape = e;
        // show menu
        menuNode.style.display = 'initial';
        var containerRect = stage.container().getBoundingClientRect();
        menuNode.style.top = stage.getPointerPosition().y + 4 + 'px';
        menuNode.style.left = stage.getPointerPosition().x + 20 + 'px';
    });

    function renderText() {

        $("#btn-renderText").prop("disabled", true);
        $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan <i class="fa fa-spinner fa-spin"></i>');
        // convert DOM into image
        html2canvas(document.querySelector("#keterangan-warna-here"))
            .then((canvas) => {
                // show it inside Konva.Image
                shape_ket.image(canvas);
                $("#btn-renderText").prop("disabled", false);
                $("#btn-renderText").html('Tampilkan Keterangan Warna Di Siteplan');
            });
    }
    $("#btn-simpan_batal_mkdt").click(function(e) {
        e.preventDefault()
    })

    function setBatalPerluRefund(value) {
        const normalized = String(value ?? "0") === "1" ? "1" : "0";
        $(`#modal-batal input[name="batal-perlu_refund"][value="${normalized}"]`).prop("checked", true);
    }

    function simpan_batal() {
        let btn = "#btn-simpan_batal_mkdt"

        if (!palid("batal-keterangan_batal", "", "Keterangan Batal harus diisi"))
            return;


        var form = $('#fm-batal_booking')[0];
        var fd = new FormData(form);
        fd.append(csrfName, csrfHash);

        $.ajax({
            url: base_url + 'mkdt/simpan_batal',
            type: 'post',
            contentType: false,
            processData: false,
            data: fd,
            dataType: 'json',
            beforeSend: function() {
                $(btn).prop("disabled", true);
                $(btn).html('Menyimpan <i class="fa fa-spinner fa-spin"></i>');
            },
            success: function(r) {
                csrfHash = r.token;

                if (r.success === true) {
                    Swal.fire({
                        //
                        icon: 'success',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $('.modal').modal('hide');
                        $(btn).html('Simpan');
                        $(btn).prop("disabled", false);
                    })
                } else {
                    Swal.fire({
                        //
                        icon: 'error',
                        title: r.messages,
                        showConfirmButton: false,
                        //timer: 1500
                    }).then(function() {
                        $(btn).html('Simpan');
                        $(btn).prop("disabled", false);
                    })
                }
                load_kavling();
                hapus_seleksi();
            },
            error: function() {
                Swal.fire({

                    icon: 'error',
                    title: "Terjadi kesalahan",
                    showConfirmButton: false,
                    //timer: 1500
                })
                $(btn).html('Simpan');
                $(btn).prop("disabled", false);
                return;
            }
        });
    }

    function ajukan_batal() {
        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                //timer: 1500
            })
            return;
        }
        $("#fm-batal_booking")[0].reset();
        setBatalPerluRefund(0);
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })
                setBatalPerluRefund(mkdt.perlu_refund);

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal_access_url != null) {
                    src = mkdt.surat_batal_access_url
                }

                $("#list-file_surat_batal").prop("href", resolveFileHref(src))

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }

    function rumahBelumSelesaiEscape(value) {
        return String(value ?? '-')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function rumahBelumSelesaiDate(value) {
        if (!value || value === '0000-00-00') {
            return '-';
        }

        return format_date(value) || '-';
    }

    function rumahBelumSelesaiProgress(value) {
        const progress = parseFloat(value);
        if (!Number.isFinite(progress)) {
            return 0;
        }

        return Math.max(0, Math.min(100, Math.round(progress)));
    }

    function rumahBelumSelesaiShape(item) {
        return {
            id: 'kav' + item.id_kavling,
            data: {
                id_produksi: item.id_produksi || null,
                id_mkdt: item.id_mkdt || null,
                id_keuangan: item.id_keuangan || null,
                nama_proyek: dt_proyek.nama_proyek,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-',
                tipe: 'kavling'
            },
            data2: {
                id_tipe: item.id_tipe || '-',
                no_tipe_rumah: item.no_tipe_rumah || '-',
                tipe_rumah: item.tipe || '-',
                id_gambar_kerja: item.id_gambar_kerja || null
            }
        };
    }

    function openRumahBelumSelesaiProgress(idKavling) {
        const item = wr_pembangunan.find((row) => String(row.id_kavling) === String(idKavling));
        const sh = findSiteplanKavlingAttrs(idKavling) || (item ? rumahBelumSelesaiShape(item) : null);

        if (!sh || typeof open_fproduksi !== 'function') {
            return swal('error', 'Terjadi Kesalahan', 'Data kavling produksi tidak ditemukan');
        }

        if (typeof editdtt !== 'undefined') {
            editdtt = [sh];
        }

        const openProgressModal = function() {
            open_fproduksi(sh, 7, idKavling);
            if (typeof focusProduksiProgressForm === 'function') {
                setTimeout(focusProduksiProgressForm, 250);
            }
        };

        const $modal = $("#modal-list-rumah-belum-selesai");
        if ($modal.hasClass('show')) {
            $modal.one('hidden.bs.modal', openProgressModal);
            $modal.modal('hide');
        } else {
            openProgressModal();
        }
    }

    function cek_tanggal_pembangunan(x = false) {
        let arr = `<tr><td colspan="7" class="rumah-belum-empty">Tidak ada Data</td></tr>`;
        if (wr_pembangunan.length > 0) {
            let n = 1;
            arr = ''
            wr_pembangunan.forEach(i => {
                const progress = rumahBelumSelesaiProgress(i.progres);
                const daysLeft = Math.round(daysBetween(today_date, i.tanggal_rencana_selesai_pembangunan));
                const isOverdue = daysLeft < 0;
                const kavlingTitle = `${i.nama_jalan || '-'} No. ${i.no_kavling || '-'}`;
                const tipeLabel = `${i.no_tipe_rumah || '-'} / ${i.tipe || '-'}`;
                const keterangan = i.keterangan || '-';
                const progressClass = progress >= 75 ? 'is-high' : '';
                const badgeClass = progress >= 75 ? 'is-blue' : '';

                arr += `
                    <tr>
                        <td class="rumah-belum-no">${n++}</td>
                        <td class="rumah-belum-kavling">
                            <div class="rumah-belum-kavling-title">${rumahBelumSelesaiEscape(kavlingTitle)}</div>
                            <div class="rumah-belum-kavling-meta">Area: ${rumahBelumSelesaiEscape(tipeLabel)}</div>
                        </td>
                        <td class="rumah-belum-progress-cell">
                            <div class="rumah-belum-progress-wrap">
                                <div class="rumah-belum-progress-track">
                                    <div class="rumah-belum-progress-fill ${progressClass}" style="width:${progress}%"></div>
                                </div>
                                <span class="rumah-belum-progress-value">${progress}%</span>
                            </div>
                        </td>
                        <td><span class="rumah-belum-date">${rumahBelumSelesaiEscape(rumahBelumSelesaiDate(i.tanggal_pembangunan))}</span></td>
                        <td>
                            <span class="rumah-belum-date">${rumahBelumSelesaiEscape(rumahBelumSelesaiDate(i.tanggal_rencana_selesai_pembangunan))}</span>
                            <span class="rumah-belum-days ${isOverdue ? 'is-overdue' : ''}">${daysLeft} hari</span>
                        </td>
                        <td><span class="rumah-belum-badge ${badgeClass}">${rumahBelumSelesaiEscape(keterangan)}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary rumah-belum-action" onclick="openRumahBelumSelesaiProgress('${rumahBelumSelesaiEscape(i.id_kavling)}')" title="Ubah data progress">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </td>
                    </tr>
                `
            });
        }
        $("#list-rumah-belum-selesai-here").html(arr)
        if (x == true)
            $("#modal-list-rumah-belum-selesai").modal();
    }

    function formatNomorHP(nomor) {
        // Hapus semua karakter non-digit
        let cleaned = nomor.replace(/\D/g, '');

        // Jika sudah diawali dengan 62, tambahkan tanda +
        if (cleaned.startsWith('+')) {
            return '' + cleaned.slice(1);
        }

        // Jika diawali 0, ubah jadi +62
        if (cleaned.startsWith('0')) {
            return '62' + cleaned.slice(1);
        }

        // Jika sudah diawali dengan 8, asumsikan masih nomor lokal
        if (cleaned.startsWith('8')) {
            return '62' + cleaned;
        }

        // Jika sudah diawali +62 dan hanya simbol + yang dihapus
        return cleaned;
    }

    let siteplanUrgentItems = {};
    let pendingSiteplanUrgentActionConsumed = false;
    const siteplanUrgentSectionOrder = [
        'tagihan_overdue',
        'tagihan_due',
        'cashout_subkon',
        'sp3k_expire',
        'rencana_akad',
        'pembangunan_telat',
        'perubahan_kavling'
    ];

    function siteplanUrgentEscape(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    function toggleSiteplanUrgentPanel(force) {
        const panel = $("#siteplan-urgent-panel");
        if (force === true) {
            panel.removeClass("hidden");
            return;
        }
        if (force === false) {
            panel.addClass("hidden");
            return;
        }
        panel.toggleClass("hidden");
    }

    function loadSiteplanUrgentPanel(options = {}) {
        if (!dt_proyek || !dt_proyek.id_proyek) {
            return;
        }

        $.ajax({
            type: "post",
            url: base_url + "siteplan/urgent/summary",
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: "json",
            beforeSend: function() {
                $("#siteplan-urgent-toggle").removeClass("hidden");
                $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Memuat hal urgent...</div>');
            },
            success: function(r) {
                if (r.token) {
                    csrfHash = r.token;
                    $('input[name="' + csrfName + '"]').val(csrfHash);
                }

                if (!r.success) {
                    $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Gagal memuat hal urgent.</div>');
                    return;
                }

                renderSiteplanUrgentPanel(r.summary || {}, options);
            },
            error: function() {
                $("#siteplan-urgent-toggle").removeClass("hidden");
                $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Gagal memuat hal urgent.</div>');
            }
        });
    }

    function renderSiteplanUrgentPanel(summary, options = {}) {
        const sections = summary.sections || {};
        const total = parseInt(summary.total || 0, 10);
        let html = '';
        siteplanUrgentItems = {};

        $("#siteplan-urgent-badge").text(total);
        $("#siteplan-urgent-toggle").removeClass("hidden");

        if (total <= 0) {
            $("#siteplan-urgent-body").html('<div class="siteplan-urgent-empty">Belum ada hal urgent untuk proyek ini.</div>');
            if (options.force) {
                toggleSiteplanUrgentPanel(true);
            }
            return;
        }

        $.each(siteplanUrgentSectionOrder, function(_, sectionKey) {
            const section = sections[sectionKey] || {};
            const items = section.items || [];
            if (!items.length) {
                return;
            }

            html += `
                <div class="siteplan-urgent-section">
                    <div class="siteplan-urgent-section-title">
                        <span>${siteplanUrgentEscape(section.label || sectionKey)}</span>
                        <span class="badge badge-light-primary">${items.length}</span>
                    </div>
            `;

            $.each(items, function(i, item) {
                const key = sectionKey + '-' + i;
                const severity = ['danger', 'warning', 'info', 'primary'].includes(item.severity) ? item.severity : 'primary';
                siteplanUrgentItems[key] = item;
                html += `
                    <div class="siteplan-urgent-item is-${severity}" onclick="openSiteplanUrgentItem('${key}')">
                        <div class="siteplan-urgent-item-title">${siteplanUrgentEscape(item.title)}</div>
                        <div class="siteplan-urgent-item-desc">${siteplanUrgentEscape(item.description)}</div>
                        <div class="siteplan-urgent-item-meta">${siteplanUrgentEscape(item.meta)}</div>
                    </div>
                `;
            });

            html += '</div>';
        });

        $("#siteplan-urgent-body").html(html);
        if (options.autoOpen) {
            toggleSiteplanUrgentPanel(true);
        }
    }

    function findSiteplanKavlingAttrs(id_kavling) {
        if (!id_kavling || typeof siteplan === 'undefined') {
            return null;
        }

        try {
            const node = typeof siteplan.findOne === 'function' ?
                siteplan.findOne('#kav' + id_kavling) :
                (siteplan.find('#kav' + id_kavling)[0] || null);
            return node && node.attrs ? node.attrs : null;
        } catch (error) {
            return null;
        }
    }

    function buildMinimalSiteplanShape(item) {
        return {
            id: 'kav' + item.id_kavling,
            data: {
                id_mkdt: item.id_mkdt || null,
                id_keuangan: item.id_keuangan || null,
                nama_proyek: item.nama_proyek || dt_proyek.nama_proyek,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-'
            },
            data2: {
                id_tipe: item.id_tipe || '-',
                no_tipe_rumah: item.id_tipe || '-',
                tipe_rumah: item.id_tipe || '-'
            }
        };
    }

    function openSiteplanUrgentItem(key) {
        const item = siteplanUrgentItems[key];
        if (!item) {
            return;
        }

        toggleSiteplanUrgentPanel(false);

        if (item.type === 'tagihan') {
            return openSiteplanKeuanganFromUrgent(item);
        }

        if (item.type === 'cashout_subkon') {
            return openSiteplanCashoutSubkonFromUrgent(item);
        }

        if (item.id_notif && typeof handleNotificationClick === 'function') {
            return handleNotificationClick(item.id_notif, item.id_kavling, item.type);
        }

        return openSiteplanKavlingFromNotification(item.id_kavling);
    }

    function openSiteplanKeuanganFromUrgent(item) {
        const sh = findSiteplanKavlingAttrs(item.id_kavling) || buildMinimalSiteplanShape(item);
        if (!sh.data || !sh.data.id_mkdt) {
            return openSiteplanKavlingFromNotification(item.id_kavling);
        }

        if (typeof open_keuangan === 'function') {
            return open_keuangan(sh, 3, item.id_kavling);
        }

        return openSiteplanKavlingFromNotification(item.id_kavling);
    }

    function openSiteplanCashoutSubkonFromUrgent(item) {
        if (typeof openCOSubkon !== 'function') {
            return openSiteplanKavlingFromNotification(item.id_kavling);
        }

        return openCOSubkon({
            id_proyek: dt_proyek.id_proyek,
            id_cashout_subkon: item.id_cashout_subkon,
            id_cashout_subkon_detail: item.id_cashout_subkon_detail,
            id_kavlings: [String(item.id_kavling)],
            selected_kavlings: [{
                id_kavling: item.id_kavling,
                nama_jalan: item.nama_jalan || '-',
                no_kavling: item.no_kavling || '-'
            }]
        });
    }

    function openSiteplanKavlingFromNotification(id_kavling) {
        const sh = findSiteplanKavlingAttrs(id_kavling);
        if (sh && typeof detail_kavling === 'function') {
            hapus_seleksi();
            editdtt.push(sh);
            drawBorderEdit(sh);
            return detail_kavling(sh, id_kavling);
        }

        return swal('warning', 'Data kavling belum siap', 'Silakan buka detail kavling langsung dari siteplan.');
    }

    function getPendingSiteplanUrgentAction() {
        const params = new URLSearchParams(window.location.search || '');
        const target = params.get('urgent_action');
        if (!target) {
            return null;
        }

        return {
            type: target,
            action_target: target,
            id_proyek: parseInt(params.get('id_proyek') || dt_proyek.id_proyek || 0, 10),
            id_kavling: parseInt(params.get('id_kavling') || 0, 10),
            id_mkdt: parseInt(params.get('id_mkdt') || 0, 10),
            id_keuangan: parseInt(params.get('id_keuangan') || 0, 10),
            id_cashout_subkon: parseInt(params.get('id_cashout_subkon') || 0, 10),
            id_cashout_subkon_detail: parseInt(params.get('id_cashout_subkon_detail') || 0, 10),
            nama_proyek: dt_proyek.nama_proyek
        };
    }

    function handlePendingSiteplanUrgentAction() {
        if (pendingSiteplanUrgentActionConsumed) {
            return;
        }

        const item = getPendingSiteplanUrgentAction();
        if (!item) {
            return;
        }

        pendingSiteplanUrgentActionConsumed = true;
        setTimeout(function() {
            if (item.action_target === 'tagihan' || item.type === 'tagihan') {
                openSiteplanKeuanganFromUrgent(item);
                return;
            }

            if (item.action_target === 'cashout_subkon' || item.type === 'cashout_subkon') {
                openSiteplanCashoutSubkonFromUrgent(item);
                return;
            }

            openSiteplanKavlingFromNotification(item.id_kavling);
        }, 300);
    }

    function openSiteplanKeuanganFromNotification(id_kavling) {
        const sh = findSiteplanKavlingAttrs(id_kavling);
        if (sh && sh.data && sh.data.id_mkdt && typeof open_keuangan === 'function') {
            hapus_seleksi();
            editdtt.push(sh);
            drawBorderEdit(sh);
            return open_keuangan(sh, 3, id_kavling);
        }

        return openSiteplanKavlingFromNotification(id_kavling);
    }

    function cek_jatuh_tempo(x = false) {
        let arr = `<tr><td colspan='6'> Tidak ada Data</td></tr>`;

        $.ajax({
            type: "post",
            url: base_url + 'tagihan/jatuhtempo',
            data: {
                [csrfName]: csrfHash,
                id_proyek: dt_proyek.id_proyek
            },
            dataType: "json",
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");
                if (r.length > 0) {
                    arr = ''
                    let n = 1
                    var groupedData = {};

                    $.each(r, function(i, v) {
                        if (!groupedData[v.id_mkdt]) {
                            // Jika id_mkdt belum ada, buat objek baru
                            groupedData[v.id_mkdt] = {
                                nama_konsumen: v.nama_konsumen,
                                no_kavling: v.no_kavling,
                                nama_cluster: v.nama_cluster,
                                nama_jalan: v.nama_jalan,
                                nama_proyek: v.nama_proyek,
                                id_tipe: v.id_tipe,
                                tagihan_list: [] // Tempat menampung banyak tagihan
                            };
                        }
                        // Masukkan tagihan ke dalam list
                        groupedData[v.id_mkdt].tagihan_list.push({
                            berita: v.berita_acara,
                            nominal: v.nominal,
                            tgl: v.jatuh_tempo_tgl
                        });
                    });
                    var html = "";
                    var no = 1;

                    $.each(groupedData, function(id, item) {
                        var tagihanHtml = "";

                        // Build tampilan list tagihan di dalam satu kolom
                        $.each(item.tagihan_list, function(idx, tg) {
                            // Format nominal ke rupiah sederhana
                            let formattedNominal = new Intl.NumberFormat('id-ID').format(tg.nominal);

                            tagihanHtml += `
                                <div style="border-bottom: 1px solid #eee; margin-bottom: 5px; padding-bottom: 5px;">
                                    <strong>${tg.berita}</strong>: Rp ${formattedNominal} <br>
                                    <small class="text-muted">Tempo: ${tg.tgl}</small>
                                </div>`;
                        });
                        let sh = {
                            data: {
                                id_mkdt: id,
                                nama_proyek: item.nama_proyek,
                                nama_jalan: item.nama_jalan,
                                no_kavling: item.no_kavling
                            },
                            data2: {
                                no_tipe_rumah: item.id_tipe,
                                tipe_rumah: item.id_tipe
                            }
                        };
                        let shString = JSON.stringify(sh).replace(/"/g, '&quot;');
                        var btn = `<button class="btn btn-outline-primary btn-sm"
                                    onclick="if(confirm('Apakah Anda yakin ingin melakukan pembayaran?')) { $('.modal').modal('hide'); open_keuangan(${shString}, 3, 0); }">
                                    <i class="fas fa-receipt"></i> Bayar
                                </button>`

                        html += `
                            <tr>
                                <td class="text-center">${no++}</td>
                                <td>
                                    <strong>${item.nama_konsumen}</strong><br>
                                    <small>${item.nama_jalan} No. ${item.no_kavling}: Tipe ${item.id_tipe}</small>
                                </td>
                                <td>${tagihanHtml}</td>
                                <td>${btn}</td>
                            </tr>`;
                    });

                    // 3. Masukkan ke dalam tbody
                    $("#list-jatuh-tempo-here").html(html);
                }
                // $("#list-jatuh-tempo-here").html(arr)
                $("#modal-list-jatuh-tempo").modal();

            },
            error: function() {
                $("#loading").addClass("hidden");

            }
        });

    }
    $(".mk-fm, #idk-is_subsidi").change(function() {
        sum_mktotal()
    })


    function hitung_total(isForm = false, mkdt = []) {
        let totalum = 0,
            totalbb = 0,
            pengurangan = 0,
            hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0), //
            diskon_hj = parseFloat(removeComma($("#mk-diskon_harga_jual").val()) || 0),
            hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
            kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
            um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
            diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
            badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
            ppn = parseFloat(removeComma($("#mk-ppn").val()) || 0),
            bphtb = parseFloat(removeComma($("#mk-bphtb").val()) || 0),
            bproses = parseFloat(removeComma($("#mk-biaya_proses").val()) || 0),
            sbum = parseFloat(removeComma($("#mk-harga_sbum").val()) || 0),

            hj_real = 0,
            persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1, //persentase kpr
            penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
            penambahan_biaya_tanah = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()) || 0),
            is_allin = $("#idk-is_allin").val(),
            harga_allin = parseFloat(removeComma($("#mk-harga_allin").val() || 0))
        if (isForm) {
            if (mkdt.length == 0)
                return showToast('tidak ada data tersedia', 'warning')

            um = parseFloat(mkdt.harga_uang_muka || 0)
            diskon_um = parseFloat(mkdt.harga_diskon_uang_muka || 0)
            badm = parseFloat(mkdt.harga_administrasi || 0)
            ppn = parseFloat(mkdt.harga_ppn || 0)
            bphtb = parseFloat(mkdt.harga_bphtb || 0)
            bproses = parseFloat(mkdt.harga_biaya_proses || 0)
            sbum = parseFloat(mkdt.harga_sbum || 0)
            penambahan_biaya = parseFloat(mkdt.harga_penambahan || 0)
            penambahan_biaya_tanah = parseFloat(mkdt.harga_penambahan_tanah || 0)
            is_allin = parseFloat(mkdt.is_allin || 0)
            harga_allin = parseFloat(mkdt.harga_allin || 0)
        }

        pengurangan = diskon_um + sbum

        totalum = um + badm + penambahan_biaya + penambahan_biaya_tanah
        totalbb = ppn + bphtb + bproses

        let tottot = totalum + totalbb - pengurangan;

        let grandtotal = tottot;
        if (is_allin == "1")
            grandtotal = harga_allin

        return {
            'total_keseluruhan': tottot,
            'harus_dibayar': grandtotal
        }
    }

    function sum_mktotal() {
        let hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0)
        // let totalum = 0,
        //     totalbb = 0,
        //     pengurangan = 0,
        //     hj = parseFloat(removeComma($("#mk-hargajual").val()) || 0), //
        //     diskon_hj = parseFloat(removeComma($("#mk-diskon_harga_jual").val()) || 0),
        //     hj_net = parseFloat(removeComma($("#mk-hargajual_net").val()) || 0),
        //     kpr = parseFloat(removeComma($("#mk-kpr").val()) || 0),
        //     um = parseFloat(removeComma($("#mk-uang_muka").val()) || 0),
        //     diskon_um = parseFloat(removeComma($("#mk-diskon_uang_muka").val()) || 0),
        //     badm = parseFloat(removeComma($("#mk-biaya_adm").val()) || 0),
        //     ppn = parseFloat(removeComma($("#mk-ppn").val()) || 0),
        //     bphtb = parseFloat(removeComma($("#mk-bphtb").val()) || 0),
        //     bproses = parseFloat(removeComma($("#mk-biaya_proses").val()) || 0),
        //     sbum = parseFloat(removeComma($("#mk-harga_sbum").val()) || 0),

        //     hj_real = 0,
        //     persentase_kpr = ($("#idk-is_subsidi").val() == 1) ? 0.05 : 0.1, //persentase kpr
        //     penambahan_biaya = parseFloat(removeComma($("#mk-harga_penambahan").val()) || 0),
        //     penambahan_biaya_tanah = parseFloat(removeComma($("#mk-harga_penambahan_tanah").val()) || 0),
        //     is_allin = $("#idk-is_allin").val(),
        //     harga_allin = parseFloat(removeComma($("#mk-harga_allin").val() || 0))
        // penambahan_biaya_um = parseFloat(removeComma($("#mk-harga_penambahan_um").val()) || 0); //turun kpr, tapi todak ada di isi data kosumen

        // hj_net = hj - diskon_hj
        // um = hj_net - kpr

        // kpr = hj - (hj * persentase_kpr)

        let tot = hitung_total()

        $("#mk-hargajual_net").val(hj_net).keyup()
        // $("#mk-kpr").val(kpr).keyup()

        // pengurangan = diskon_um + sbum

        // totalum = um + badm + penambahan_biaya + penambahan_biaya_tanah
        // totalbb = ppn + bphtb + bproses

        // let tottot = totalum + totalbb - pengurangan;

        // let grandtotal = tottot;
        // if (is_allin == "1")
        //     grandtotal = harga_allin

        $("#mk-tgt").val(tot.total_keseluruhan).keyup(); //grand total keseluruhan
        $("#mk-total_tot").val(tot.harus_dibayar).keyup(); //total yang harus dibayar konsumen

        // alert(totalbb)
        // $(".tum").val(totalum).keyup();
        // $(".tbb").val(totalbb).keyup();
    }

    function terima_batal() {

        let sh = editdtt[0],
            id_kavling = sh.id.substr(3);
        if (sh.data.tipe != "kavling") {
            Swal.fire({
                //
                icon: 'error',
                title: "Tidak ada kavling terpilih ",
                showConfirmButton: true,
                // //timer: 1500
            })
            return;
        }
        if (!sh.data.id_mkdt) {
            Swal.fire({
                //
                icon: 'error',
                title: "Belum ada data konsumen di kavling: <br>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling,
                showConfirmButton: false,
                // ////timer: 1500
            })
            return;
        }
        // if (sh.data.is_batal == '0') {
        //     Swal.fire({
        //         //
        //         icon: 'error',
        //         title: "Belum ada data surat batal dari MKDT",
        //         showConfirmButton: false,
        //         // //timer: 1500
        //     })
        //     return;
        // }
        $("#fm-batal_booking")[0].reset();
        setBatalPerluRefund(0);
        $("#last_update-batal_mkdt").html("Dibatalkan oleh: -  Pada: -")

        $("#batal-id_kavling").val(id_kavling);
        $("#batal-id_mkdt").val(sh.data.id_mkdt);

        $(".label_alamat").html(dt_proyek.nama_proyek + "<br/>" + sh.data.nama_jalan + ", No." + sh.data.no_kavling + "<br/>" + sh.data2.no_tipe_rumah + " (" + sh.data2.tipe_rumah + ")<br/>");

        $.ajax({
            url: base_url + 'mkdt/batal_mkdt',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual,
                id_kavling: id_kavling
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(x) {
                let mkdt = x.data,
                    sb = x.sudah_bayar,
                    tb = x.total_biaya
                $.each(mkdt, function(k, v) {
                    $("#batal-" + k).val(v).change().keyup()
                })
                setBatalPerluRefund(mkdt.perlu_refund);

                let src = not_found
                //load ktp npwp
                if (mkdt.surat_batal_access_url != null) {
                    src = mkdt.surat_batal_access_url
                }

                $("#list-file_surat_batal").prop("href", resolveFileHref(src))

                $("#last_update-batal_mkdt").html("Dibatalkan oleh: " + mkdt.mkdt_batal_oleh_u + " Pada: " + format_datetime(mkdt.mkdt_batal_tgl))

                $("#batal-total_biaya_um").val(tb.uang_muka).keyup()
                $("#batal-total_biaya_bb").val(tb.biaya_biaya).keyup()

                $("#batal-sudah_bayar_um").val(sb.uang_muka).keyup()
                $("#batal-sudah_bayar_bb").val(sb.biaya_biaya).keyup()

                $("#batal-sisa_tagihan_um").val(tb.uang_muka - sb.biaya_biaya).keyup()
                $("#batal-sisa_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()

                // $("#batal-persentase_bayar_tagihan_bb").val(tb.biaya_biaya - sb.biaya_biaya).keyup()
                // $("#batal-persentase_bayar_tagihan_um").val(tb.biaya_biaya - sb.biaya_biaya).keyup()


                $('#modal-batal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loading").addClass("hidden");
            },
            error: function(e) {
                Swal.fire({
                    //
                    icon: 'error',
                    title: "Token tidak valid, muat ulang halaman",
                    showConfirmButton: true,
                    // //timer: 1500
                }).then(function() {
                    location.reload();
                })
            }
        });
    }



    /*************************** cashout subkon ***************************/




    /*************************** End of cashout subkon ***************************/

    $(document).keydown(function(event) {
        if (event.key === 'Escape') {
            hapus_seleksi()
        }
    });


    $("#dt-listrik_jenis").change(function() {
        if (this.value == "PLN") {
            $("#dt-listrik-pln-input-form").removeClass("hidden");
            $("#dt-listrik_disediakan").addClass("hidden");
        } else {
            $("#dt-listrik-pln-input-form").addClass("hidden");
            $("#dt-listrik_disediakan").removeClass("hidden");
        }
    });
    $("#dt-air_jenis").change(function() {
        if (this.value == "Air Tanah") {
            $("#dt-air_tanah-input_form").removeClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else if (this.value == "Komunal Warga") {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").removeClass("hidden");
            $("#dt-air_pdam-input_form").addClass("hidden");
        } else {
            $("#dt-air_tanah-input_form").addClass("hidden");
            $("#dt-air_komunal-input_form").addClass("hidden");
            $("#dt-air_pdam-input_form").removeClass("hidden");
        }
    });
</script>



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
