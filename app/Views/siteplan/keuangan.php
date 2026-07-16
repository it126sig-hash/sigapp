<style>
    /* SIGAPP UI Acuan - Modal Dana Jaminan */
    #dana_akad_modal .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #dana_akad_modal .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #dana_akad_modal .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #dana_akad_modal .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #dana_akad_modal .keu-dj-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #dana_akad_modal .keu-dj-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #dana_akad_modal .keu-dj-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #dana_akad_modal .keu-dj-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #dana_akad_modal .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #dana_akad_modal .card-body {
        padding: 1rem;
    }

    #dana_akad_modal .keu-dj-hero {
        border: 0;
    }

    #dana_akad_modal .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #dana_akad_modal .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #dana_akad_modal .keu-dj-meta-card {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        margin-bottom: .75rem;
    }

    #dana_akad_modal .keu-dj-summary-row {
        align-items: center;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        padding: .45rem 0;
    }

    #dana_akad_modal .keu-dj-summary-row:last-child {
        border-bottom: 0;
    }

    #dana_akad_modal .keu-dj-summary-row span {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
    }

    #dana_akad_modal .keu-dj-summary-row strong {
        color: #111827;
        font-size: .9rem;
        text-align: right;
    }

    #dana_akad_modal .divider {
        margin: .65rem 0 .85rem;
    }

    #dana_akad_modal .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #dana_akad_modal .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #dana_akad_modal label,
    #dana_akad_modal .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #dana_akad_modal .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #dana_akad_modal .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #dana_akad_modal .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #dana_akad_modal .btn-primary:hover,
    #dana_akad_modal .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #dana_akad_modal .nav-tabs {
        border-bottom: 1px solid #d8dde3;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    #dana_akad_modal .nav-tabs .nav-link {
        border-radius: 6px 6px 0 0;
        color: #4b5563;
        font-weight: 700;
        white-space: nowrap;
    }

    #dana_akad_modal .nav-tabs .nav-link.active {
        color: #2057a3;
    }

    #dana_akad_modal .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #dana_akad_modal .table tbody td {
        font-size: .84rem;
        vertical-align: middle;
    }

    #dana_akad_modal .keu-dj-empty {
        background: #fff;
        border: 1px dashed #cfd6e3;
        border-radius: 8px;
        color: #6b7280;
        font-weight: 700;
        padding: .9rem;
        text-align: center;
    }

    #dana_akad_modal .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1.25rem;
    }

    .dark-layout #dana_akad_modal .modal-header,
    .dark-layout #dana_akad_modal .card,
    .dark-layout #dana_akad_modal .keu-dj-meta-card,
    .dark-layout #dana_akad_modal .modal-footer {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #dana_akad_modal .modal-title,
    .dark-layout #dana_akad_modal .divider .divider-text,
    .dark-layout #dana_akad_modal .keu-dj-summary-row strong {
        color: #f8fafc;
    }

    .dark-layout #dana_akad_modal .keu-dj-body {
        background: #1f2937 !important;
    }

    @media (max-width: 1199.98px) {
        #dana_akad_modal .keu-dj-layout {
            flex-wrap: wrap;
        }

        #dana_akad_modal .keu-dj-sidebar,
        #dana_akad_modal .keu-dj-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #dana_akad_modal .keu-dj-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #dana_akad_modal .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #dana_akad_modal .keu-dj-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #dana_akad_modal .card-body {
            padding: .85rem;
        }
    }

    /* SIGAPP UI Acuan - Modal Pencairan Bank KPR */
    #bank_kpr_modal .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #bank_kpr_modal .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #bank_kpr_modal .modal-header,
    #bank_kpr_modal .modal-footer {
        background: #fff;
        border-color: #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #bank_kpr_modal .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #bank_kpr_modal .keu-bank-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #bank_kpr_modal .keu-bank-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #bank_kpr_modal .keu-bank-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #bank_kpr_modal .keu-bank-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #bank_kpr_modal .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #bank_kpr_modal .card-body {
        padding: 1rem;
    }

    #bank_kpr_modal .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #bank_kpr_modal .keu-bank-summary-row {
        align-items: center;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        padding: .45rem 0;
    }

    #bank_kpr_modal .keu-bank-summary-row:last-child {
        border-bottom: 0;
    }

    #bank_kpr_modal .keu-bank-summary-row span {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
    }

    #bank_kpr_modal .keu-bank-summary-row strong {
        color: #111827;
        font-size: .9rem;
        text-align: right;
    }

    #bank_kpr_modal .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #bank_kpr_modal .divider .divider-text,
    #bank_kpr_modal label,
    #bank_kpr_modal .form-label {
        color: #111827;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #bank_kpr_modal label,
    #bank_kpr_modal .form-label {
        color: #6b7280;
        font-size: .78rem;
    }

    #bank_kpr_modal .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #bank_kpr_modal .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #bank_kpr_modal .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #bank_kpr_modal .nav-tabs {
        border-bottom: 1px solid #d8dde3;
        flex-wrap: nowrap;
        overflow-x: auto;
    }

    #bank_kpr_modal .nav-tabs .nav-link {
        border-radius: 6px 6px 0 0;
        color: #4b5563;
        font-weight: 700;
        white-space: nowrap;
    }

    #bank_kpr_modal .nav-tabs .nav-link.active {
        color: #2057a3;
    }

    #bank_kpr_modal .table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #bank_kpr_modal .table tbody td {
        font-size: .84rem;
        vertical-align: middle;
    }

    #bank_kpr_modal .keu-bank-empty {
        background: #fff;
        border: 1px dashed #cfd6e3;
        border-radius: 8px;
        color: #6b7280;
        font-weight: 700;
        padding: .9rem;
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        #bank_kpr_modal .keu-bank-layout {
            flex-wrap: wrap;
        }

        #bank_kpr_modal .keu-bank-sidebar,
        #bank_kpr_modal .keu-bank-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #bank_kpr_modal .keu-bank-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #bank_kpr_modal .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #bank_kpr_modal .keu-bank-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #bank_kpr_modal .card-body {
            padding: .85rem;
        }
    }

    /* SIGAPP UI Acuan - Modal Cash Out (mengikuti #modal-isi_data_konsumen) */
    #modal-cashout-keu .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal-cashout-keu .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modal-cashout-keu .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modal-cashout-keu .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal-cashout-keu .keu-co-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal-cashout-keu .keu-co-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        min-width: 0;
    }

    #modal-cashout-keu .keu-co-sidebar {
        align-self: flex-start;
        flex: 0 0 340px;
        max-height: calc(100vh - 8rem);
        max-width: 340px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #modal-cashout-keu .keu-co-content {
        flex: 1 1 auto;
        max-width: calc(100% - 356px);
        min-width: 0;
    }

    #modal-cashout-keu .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modal-cashout-keu .card-body {
        padding: 1rem;
    }

    #modal-cashout-keu .keu-co-hero {
        border: 0;
    }

    #modal-cashout-keu .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #modal-cashout-keu .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #modal-cashout-keu .keu-co-meta-card {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        margin-bottom: 0;
    }

    #modal-cashout-keu .keu-co-meta-card h6,
    #modal-cashout-keu .keu-co-meta-card h5 {
        color: #374151;
        line-height: 1.35;
        margin-bottom: .45rem;
    }

    #modal-cashout-keu .keu-co-meta-card h5:last-child,
    #modal-cashout-keu .keu-co-meta-card h6:last-of-type {
        margin-bottom: 0;
    }

    #modal-cashout-keu .divider {
        margin: .65rem 0 .85rem;
    }

    #modal-cashout-keu .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modal-cashout-keu .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal-cashout-keu .keu-cost-row {
        align-items: flex-start;
        background: #f9fafb;
        border: 1px solid #edf0f2;
        border-radius: 6px;
        display: flex;
        gap: .75rem;
        justify-content: space-between;
        margin-bottom: .45rem;
        padding: .45rem .55rem;
    }

    #modal-cashout-keu .keu-cost-row.is-total {
        background: #eef5ff;
        border-color: #c9ddf5;
    }

    #modal-cashout-keu .keu-cost-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal-cashout-keu .keu-cost-value {
        color: #111827;
        font-weight: 700;
        overflow-wrap: anywhere;
        text-align: right;
    }

    #modal-cashout-keu label,
    #modal-cashout-keu .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal-cashout-keu .form-group {
        margin-bottom: .8rem;
    }

    #modal-cashout-keu .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal-cashout-keu .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #modal-cashout-keu .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modal-cashout-keu .btn-primary:hover,
    #modal-cashout-keu .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modal-cashout-keu #cashout-table {
        margin-bottom: 0;
    }

    #modal-cashout-keu #cashout-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
        font-size: .78rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #modal-cashout-keu #cashout-table tbody td {
        font-size: .84rem;
        vertical-align: middle;
    }

    #modal-cashout-keu .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1.25rem;
    }

    .dark-layout #modal-cashout-keu .modal-header,
    .dark-layout #modal-cashout-keu .card,
    .dark-layout #modal-cashout-keu .keu-co-meta-card,
    .dark-layout #modal-cashout-keu .modal-footer {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modal-cashout-keu .modal-title,
    .dark-layout #modal-cashout-keu .divider .divider-text {
        color: #f8fafc;
    }

    .dark-layout #modal-cashout-keu .keu-co-body {
        background: #1f2937 !important;
    }

    .dark-layout #modal-cashout-keu .keu-cost-row {
        background: rgba(255, 255, 255, .04);
        border-color: rgba(255, 255, 255, .08);
    }

    .dark-layout #modal-cashout-keu .keu-cost-row.is-total {
        background: rgba(32, 87, 163, .2);
        border-color: rgba(32, 87, 163, .35);
    }

    .dark-layout #modal-cashout-keu .keu-cost-value {
        color: #f8fafc;
    }

    @media (max-width: 1199.98px) {
        #modal-cashout-keu .keu-co-layout {
            flex-wrap: wrap;
        }

        #modal-cashout-keu .keu-co-sidebar,
        #modal-cashout-keu .keu-co-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal-cashout-keu .keu-co-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #modal-cashout-keu .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal-cashout-keu .keu-co-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal-cashout-keu .card-body {
            padding: .85rem;
        }
    }
</style>

<?= view('keuangan/partials/modal_bayar_tagihan') ?>

<!-- ################################## Modal Tagihan ##########################################-->
<div class="modal fade text-left" id="print_tagihan_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Print Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="col-xl-12 col-md-12 col-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="form_list_inv-tab" data-toggle="tab" href="#form_list_inv"
                            aria-controls="form_list_inv" role="tab" aria-selected="true">List Invoice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " id="form_add_inv-tab" data-toggle="tab" href="#form_add_inv"
                            aria-controls="form_add_inv" role="tab" aria-selected="true">Tambah Invoice</a>
                    </li>
                </ul>
                <div class="tab-content">

                    <div class="tab-pane active" id="form_list_inv" aria-labelledby="form_list_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table mb-0" id="tbl-tagihan">
                                            <thead>
                                                <tr>
                                                    <th>No Invoice</th>
                                                    <th>Tanggal Terbit</th>
                                                    <th>Tanggal Kadaluarsa</th>
                                                    <th>Oleh</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="list_inv-here"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane " id="form_add_inv" aria-labelledby="form_add_inv-tab" role="tabpanel">
                        <div class="card invoice-preview-card">
                            <div class="card-body invoice-padding pb-0">
                                <div
                                    class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">
                                    <div class="col-md-4">
                                        <select class="select2 custom-select " id="kopsurat" name="kopsurat"></select>
                                        <!-- <div class="logo-wrapper" id="pc-logo_perusahaan"></div>
                                        <p class="card-text mb-25" id="pc-alamat_perusahaan">Office 149, 450 South Brand Brooklyn</p>
                                        <p class="card-text mb-25" id="pc-website_perusahaan">San Diego County, CA 91905, USA</p>
                                        <p class="card-text mb-0" id="pc-kontak_perusahaan">+1 (123) 456 7891, +44 (876) 543 2198</p> -->

                                    </div>
                                    <div class="invoice-number-date mt-md-0 mt-4">
                                        <div class="">
                                            <h4 class="invoice-title">No Invoice</h4>
                                            <div class="input-group input-group-merge invoice-edit-input-group">
                                                <input id="no_sruat" name="no_sruat" type="text"
                                                    class="form-control invoice-edit-input" placeholder="53634">
                                            </div>
                                        </div>
                                        <div class="">
                                            <span class="title">Tanggal:</span>
                                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan"
                                                class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                        <div class="">
                                            <span class="title">Tenggat Waktu:</span>
                                            <input type="text" id="pt-tanggal_jatuh_tempo" name="pt-tanggal_jatuh_tempo"
                                                class="form-control flatpickr-human-friendly" placeholder="-">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Header starts -->
                            <!-- <div class="card-body invoice-padding pb-0">
                        <div class="form-group">
                            <label for="no_sruat">No Surat</label>
                            <input type="text" class="form-control" id="no_sruat" name="no_sruat">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_surat_tagihan">Tanggal Surat Tagihan</label>
                            <input type="text" id="tanggal_surat_tagihan" name="tanggal_surat_tagihan" class="form-control flatpickr-human-friendly" placeholder="-" />
                        </div>
                    </div> -->
                            <!-- Header ends -->

                            <hr class="invoice-spacing" />

                            <!-- Address and Contact starts -->
                            <div class="card-body invoice-padding pt-0">
                                <div class="invoice-spacing  row">
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Ditagihkan Ke:</h6>
                                        <h6 class="hidden" id="pt_id_konsumen"></h6>
                                        <h6 class="mb-25" id="pt_detail_konsumen"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                    <div class="col-xl-6 p-0">
                                        <h6 class="mb-2">Perumahan</h6>
                                        <h6 class="hidden" id="pt_id_kavling"></h6>
                                        <h6 class="hidden" id="pt_id_mkdt"></h6>
                                        <h6 class="mb-25" id="pt_detail_kavling"></h6>
                                        <!-- <p class="card-text mb-25" id="pt_hp_konsumen"></p> -->
                                    </div>
                                </div>
                            </div>
                            <!-- Address and Contact ends -->

                            <!-- Product Details starts -->
                            <div class="card-body invoice-padding invoice-product-details">
                                <form class="source-item">
                                    <div data-repeater-list="group-a">
                                        <div class="repeater-wrapper" data-repeater-item>
                                            <div class="row">
                                                <div class="table-responsive">
                                                    <table class="table mb-0" id="tbl-tagihan">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" class="text-nowrap">No</th>
                                                                <th scope="col" class="text-nowrap">Berita Acara</th>
                                                                <th scope="col" class="text-nowrap">Jatuh Tempo</th>
                                                                <!-- <th scope="col" class="text-nowrap">Sudah Dibayar</th> -->
                                                                <th scope="col" class="text-nowrap">Nominal</th>
                                                                <!-- <th scope="col" class="text-nowrap">Masukan Dalam Surat</th> -->
                                                            </tr>
                                                        </thead>
                                                        <tbody id="tb-print-data-tagihan">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Product Details ends -->
                            <hr class="invoice-spacing mt-0" />

                            <div class="card-body invoice-padding py-0">
                                <!-- Invoice Note starts -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-2">
                                            <label for="note" class="form-label font-weight-bold">Syarat &
                                                Ketentuan:</label>
                                            <textarea class="form-control" rows="5"
                                                id="snk"><ol><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda&nbsp;</span></li><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Pembayaran yang sah hanya melalui transfer ke rekening atas nama <br><b>PT. Sanggarindah Karya Sentosa</b> <b>Raya</b> BCA KC Setiabudi - Bandung, Nomor Rekening :<b>2337 887 887</b>&nbsp;</span></li><li>Konfirmasi pembayaran ke bagian keuangan kami dan lampirkan bukti transfer.</li></ol></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- Invoice Note ends -->

                            </div>
                            <div class="modal-footer">
                                <button id="form_add_inv-btn" class="btn btn-primary data-submit mr-1"
                                    onclick="save_inv()" href="javascript:void(0)">Simpan Invoice</button>
                                <button type="reset" class="btn btn-outline-secondary"
                                    data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ################################## Dana Akad ##########################################-->
<div class="modal fade text-left" id="dana_akad_modal" tabindex="-1" role="dialog"
    aria-labelledby="dana_akad_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dana_akad_modal_label">Dana Jaminan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body keu-dj-body">
                <div class="keu-dj-layout">
                    <aside class="keu-dj-sidebar">
                        <div class="card keu-dj-hero">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat" id="label_alamat3"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Info Konsumen</div>
                                </div>
                                <div class="card keu-dj-meta-card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-users"></i> Konsumen</h6>
                                        <h5><strong><span class="label_konsumen" id="label_konsumen">-</span></strong></h5>
                                        <h6><i class="fas fa-check-circle"></i> Status</h6>
                                        <h5 class="mb-0"><strong><span id="da-status_mkdt">-</span></strong></h5>
                                    </div>
                                </div>
                                <div class="divider divider-left">
                                    <div class="divider-text">Ringkasan</div>
                                </div>
                                <div class="card keu-dj-meta-card mb-0">
                                    <div class="card-body">
                                        <div class="keu-dj-summary-row">
                                            <span>KPR Acc</span>
                                            <strong id="da-kpr_acc-label">0</strong>
                                        </div>
                                        <div class="keu-dj-summary-row">
                                            <span>Total Dana Jaminan</span>
                                            <strong id="da-total_dajam-label">0</strong>
                                        </div>
                                        <div class="keu-dj-summary-row">
                                            <span>Hasil Akad</span>
                                            <strong id="da-hasil_akad-label">0</strong>
                                        </div>
                                        <div class="custom-control custom-switch mt-1">
                                            <input type="checkbox" class="custom-control-input cbp" id="da-dajam_selesai"
                                                name="dajam_selesai" value="1" form="fm-dana_akad" />
                                            <label class="custom-control-label" for="da-dajam_selesai">Tandai Selesai</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="keu-dj-content">
                        <div class="card">
                            <div class="card-body pb-0">
                                <ul class="nav nav-tabs mb-1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="keu-dajam-tab" data-toggle="tab"
                                            href="#keu-dajam" aria-controls="keu-dajam" role="tab"
                                            aria-selected="true">Dana Jaminan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="keu-pengajuan-dajam-tab" data-toggle="tab"
                                            href="#keu-pengajuan-dajam" aria-controls="keu-pengajuan-dajam" role="tab"
                                            aria-selected="false">Pengajuan Bank</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="keu-history-dajam-tab" data-toggle="tab"
                                            href="#keu-history-dajam" aria-controls="keu-history-dajam" role="tab"
                                            aria-selected="false">History</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="keu-dajam" aria-labelledby="keu-dajam-tab" role="tabpanel">
                                <form id="fm-dana_akad" autocomplete="off">
                                    <input type="hidden" class="form-control" id="da-id_mkdt" name="id_mkdt" value="" />
                                    <input type="hidden" class="form-control" id="da-id_kavling" name="id_kavling" value="" />
                                    <input type="hidden" id="da-kpr_acc" name="da-kpr_acc" value="0" />
                                    <input type="hidden" id="da-hasil_akad" name="hasil_akad" value="0" />
                                    <input type="hidden" id="da-total_dajam" name="total_dajam" value="0" />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="divider divider-left">
                                                <div class="divider-text">Dana Jaminan dan Pencairan</div>
                                            </div>
                                            <div id="da-jaminan_here"></div>
                                            <button id="add-form-btn-dana_akad" class="btn btn-primary mt-1"
                                                onclick="save_dana_akad(); return false;" href="javascript:void(0)">
                                                Simpan Dana Jaminan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" id="keu-pengajuan-dajam" aria-labelledby="keu-pengajuan-dajam-tab" role="tabpanel">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Tambah Pengajuan Bank</div>
                                        </div>
                                        <form id="form-pencairan" enctype="multipart/form-data" autocomplete="off">
                                            <input type="hidden" id="dajam-pengajuan-id_kavling" name="id_kavling" value="">
                                            <input type="hidden" id="dajam-pengajuan-id_mkdt" name="id_mkdt" value="">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Tanggal Pengajuan</label>
                                                        <input type="date" class="form-control" name="tanggal_pengajuan" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label>Lampiran Surat (PDF)</label>
                                                        <input type="file" class="form-control-file" name="surat" accept="application/pdf">
                                                        <small class="form-text text-muted">Opsional, maksimum 4 MB.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Keterangan Pengajuan</label>
                                                <textarea class="form-control" name="keterangan" rows="3" placeholder="Catatan pengajuan ke bank"></textarea>
                                            </div>
                                            <div class="divider divider-left">
                                                <div class="divider-text">Item yang Diajukan</div>
                                            </div>
                                            <div id="da-pengajuan-item_here"></div>
                                            <button id="btn-saveDanaJaminan" type="submit" class="btn btn-primary mt-1">
                                                Simpan Pengajuan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Riwayat Pengajuan Bank</div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" id="tbl-riwayat">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tanggal Pengajuan</th>
                                                        <th>Item</th>
                                                        <th>Status</th>
                                                        <th>Lampiran</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="keu-history-dajam" aria-labelledby="keu-history-dajam-tab" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">History Dana Jaminan</div>
                                        </div>
                                        <div id="da-history_here"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ################################## Pencairan Bank KPR ##########################################-->
<div class="modal fade text-left" id="bank_kpr_modal" tabindex="-1" role="dialog"
    aria-labelledby="bank_kpr_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bank_kpr_modal_label">Pencairan Bank KPR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body keu-bank-body">
                <div class="keu-bank-layout">
                    <aside class="keu-bank-sidebar">
                        <div class="card">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat" id="bank-kpr-label-alamat"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Info Akad</div>
                                </div>
                                <h6><i class="fas fa-users"></i> Konsumen</h6>
                                <h5><strong><span id="bank-kpr-label-konsumen">-</span></strong></h5>
                                <h6><i class="fas fa-check-circle"></i> Status</h6>
                                <h5><strong><span id="bank-kpr-status-mkdt">-</span></strong></h5>
                                <h6><i class="fas fa-university"></i> Bank Akad</h6>
                                <h5 class="mb-0"><strong><span id="bank-kpr-bank-akad">-</span></strong></h5>
                            </div>
                        </div>
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Ringkasan</div>
                                </div>
                                <div class="keu-bank-summary-row">
                                    <span>KPR Acc</span>
                                    <strong id="bank-kpr-plafon-label">0</strong>
                                </div>
                                <div class="keu-bank-summary-row">
                                    <span>Total Cair</span>
                                    <strong id="bank-kpr-total-cair-label">0</strong>
                                </div>
                                <div class="keu-bank-summary-row">
                                    <span>Retensi Tercatat</span>
                                    <strong id="bank-kpr-retensi-label">0</strong>
                                </div>
                                <div class="keu-bank-summary-row">
                                    <span>Sisa Plafon</span>
                                    <strong id="bank-kpr-sisa-label">0</strong>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="keu-bank-content">
                        <div class="card">
                            <div class="card-body pb-0">
                                <ul class="nav nav-tabs mb-1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="bank-kpr-form-tab" data-toggle="tab"
                                            href="#bank-kpr-form-pane" aria-controls="bank-kpr-form-pane" role="tab"
                                            aria-selected="true">Form Pencairan</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="bank-kpr-history-tab" data-toggle="tab"
                                            href="#bank-kpr-history-pane" aria-controls="bank-kpr-history-pane" role="tab"
                                            aria-selected="false">Riwayat Pencairan</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="bank-kpr-form-pane" aria-labelledby="bank-kpr-form-tab" role="tabpanel">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Realisasi Uang dari Bank</div>
                                        </div>
                                        <form id="form-bank-kpr" enctype="multipart/form-data" autocomplete="off">
                                            <input type="hidden" id="bank-kpr-id" name="id" value="">
                                            <input type="hidden" id="bank-kpr-id-mkdt" name="id_mkdt" value="">
                                            <input type="hidden" id="bank-kpr-id-kavling" name="id_kavling" value="">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Bank Pencair</label>
                                                        <select class="form-control" id="bank-kpr-id-bank" name="id_bank"></select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Nominal Plafon/ACC</label>
                                                        <input type="text" class="form-control num" id="bank-kpr-nominal-plafon" name="nominal_plafon" value="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control" id="bank-kpr-status" name="status">
                                                            <option value="draft">Draft</option>
                                                            <option value="cair">Cair</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Nominal Cair</label>
                                                        <input type="text" class="form-control num" id="bank-kpr-nominal-cair" name="nominal_cair" value="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Nominal Retensi</label>
                                                        <input type="text" class="form-control num" id="bank-kpr-nominal-retensi" name="nominal_retensi" value="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Tanggal Cair</label>
                                                        <input type="date" class="form-control" id="bank-kpr-tanggal-cair" name="tanggal_cair">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Rekening Tujuan</label>
                                                        <input type="text" class="form-control" id="bank-kpr-rekening" name="rekening_tujuan" placeholder="Nama bank / nomor rekening tujuan">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>No Referensi</label>
                                                        <input type="text" class="form-control" id="bank-kpr-referensi" name="no_referensi" placeholder="No referensi transfer">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-md-0">
                                                        <label>Bukti Transfer</label>
                                                        <input type="file" class="form-control-file" id="bank-kpr-file" name="file_bukti" accept="application/pdf,image/jpeg,image/png">
                                                        <small class="form-text text-muted">PDF/JPG/PNG, maksimum 4 MB.</small>
                                                        <div id="bank-kpr-current-file" class="mt-50"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-0">
                                                        <label>Keterangan</label>
                                                        <textarea class="form-control" id="bank-kpr-keterangan" name="keterangan" rows="3" placeholder="Catatan pencairan"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-1">
                                                <button id="btn-save-bank-kpr" type="submit" class="btn btn-primary">
                                                    Simpan Pencairan
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary ml-50" onclick="resetBankKprForm()">
                                                    Reset Form
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="bank-kpr-history-pane" aria-labelledby="bank-kpr-history-tab" role="tabpanel">
                                <div class="card mb-0">
                                    <div class="card-body">
                                        <div class="divider divider-left">
                                            <div class="divider-text">Riwayat Pencairan Bank</div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" id="tbl-bank-kpr">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Status</th>
                                                        <th>Bank</th>
                                                        <th>Tanggal</th>
                                                        <th class="text-right">Cair</th>
                                                        <th class="text-right">Retensi</th>
                                                        <th>Bukti</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= view('keuangan/partials/modal_pencairan_akad') ?>

<!-- ################################## isi_cashout ##########################################-->
<div class="modal fade text-left" id="modal-cashout-keu" tabindex="-1" role="dialog"
    aria-labelledby="modal-cashout-keu-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-cashout-keu" class="add-new-record modal-content pt-0" autocomplete="off" style="height: 95vh;">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-cashout-keu-label">Form Isi Cash Out</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1 keu-co-body">
                <div class="keu-co-layout">
                    <aside class="keu-co-sidebar">
                        <div class="card keu-co-hero">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Info Konsumen</div>
                                </div>
                                <div class="card keu-co-meta-card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-users"></i> Konsumen</h6>
                                        <h5><strong><span id="fm-co-label_konsumen">-</span></strong></h5>
                                        <h6><i class="fas fa-calendar"></i> Tanggal Booking</h6>
                                        <h5 class="mb-0"><strong><span id="fm-co-label_tgl">-</span> (Rp. <span id="fm-co-label_bookingfee">0</span>)</strong></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Harga & Detail Biaya MKDT</div>
                                </div>
                                <div id="fm-co-biaya-mkdt">
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga Jual</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_jual">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga Jual Net</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_jual_net">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Harga KPR</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_kpr">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">KPR ACC</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_kpr_acc">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Uang Muka</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_uang_muka">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Diskon UM</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_diskon_uang_muka">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">SBUM</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_sbum">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total">
                                        <span class="keu-cost-label">Total UM</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="total_um">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Administrasi</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_administrasi">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">BPHTB</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_bphtb">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Proses</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_biaya_proses">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">PPN</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_ppn">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Turun KPR</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_penambahan_um">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Kavling Strategis</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_penambahan">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row">
                                        <span class="keu-cost-label">Biaya Kelebihan Tanah</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="harga_penambahan_tanah">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total">
                                        <span class="keu-cost-label">Total Biaya Lain</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="total_biaya_lain">Rp. 0</span>
                                    </div>
                                    <div class="keu-cost-row is-total mb-0">
                                        <span class="keu-cost-label">Total Tercatat</span>
                                        <span class="keu-cost-value" data-cashout-biaya-mkdt="total_tercatat">Rp. 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <section class="keu-co-content">
                        <input type="hidden" class="form-control" id="cashout-id_kavling" name="id_kavling">

                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Form Cash Out</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="co-untuk_pembayaran">Untuk Pembayaran</label>
                                            <select name="co-untuk_pembayaran" id="co-untuk_pembayaran"
                                                class="form-control form-select"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="co-tanggal_bayar">Tanggal Pembayaran</label>
                                            <input type="text" id="co-tanggal_bayar" name="co-tanggal_bayar"
                                                class="form-control flatpickr-human-friendly" placeholder="-" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group">
                                            <label for="co-nominal">Nominal Pembayaran</label>
                                            <input type="text" class="form-control num" id="co-nominal"
                                                name="co-nominal">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="form-group mb-0">
                                            <label for="co-keterangan">Keterangan Pembayaran</label>
                                            <textarea class="form-control" id="co-keterangan" name="co-keterangan"
                                                rows="3" placeholder="Keterangan"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Riwayat Pembayaran Cash Out</div>
                                </div>
                                <div class="table-responsive">
                                    <table id="cashout-table" class="datatables-basic table table-sm compact mb-0">
                                        <thead>
                                            <tr>
                                                <th width=""></th>
                                                <th width="20%">Item</th>
                                                <th width="20%">Tanggal Pembayaran</th>
                                                <th width="25%">Nominal</th>
                                                <th width="35%">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div id="div-cashout-here" class="row">
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button id="add-form-btn-cashout" class="btn btn-primary data-submit mr-1"
                    onclick="save_cashout(); return false;" href="javascript:void(0)">Simpan</button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/keuangan.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/keuangan.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/tagihan-bayar-modal.js?v=<?= filemtime(FCPATH.'assets/js/tagihan-bayar-modal.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/pencairan-akad-modal.js?v=<?= filemtime(FCPATH . 'assets/js/pencairan-akad-modal.js') ?>"></script>
