<style>
    .sticky-button-wrapper {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 12px;
        border-top: 1px solid #ddd;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    #modal-isi_data_konsumen .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal-isi_data_konsumen .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modal-isi_data_konsumen .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modal-isi_data_konsumen .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal-isi_data_konsumen .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal-isi_data_konsumen .modal-body > .row {
        margin-left: -.5rem;
        margin-right: -.5rem;
    }

    #modal-isi_data_konsumen .modal-body > .row > [class*="col-"] {
        padding-left: .5rem;
        padding-right: .5rem;
    }

    #modal-isi_data_konsumen .idk-konsumen-layout {
        align-items: flex-start;
        flex-wrap: nowrap;
        gap: 1rem;
        margin: 0;
    }

    #modal-isi_data_konsumen .idk-konsumen-layout > .idk-sidebar,
    #modal-isi_data_konsumen .idk-konsumen-layout > .idk-main-content {
        padding: 0;
    }

    #modal-isi_data_konsumen .idk-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #modal-isi_data_konsumen .idk-main-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #modal-isi_data_konsumen .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modal-isi_data_konsumen .card-header,
    #modal-isi_data_konsumen #tab-isi-konsumen > .card .card-body {
        background: #fff;
        border-bottom: 1px solid #edf0f2;
        padding: .85rem 1rem;
    }

    #modal-isi_data_konsumen .card-body {
        padding: 1rem;
    }

    #modal-isi_data_konsumen .refund-status-card,
    #modal-batal .refund-status-card {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: .85rem;
    }

    #modal-isi_data_konsumen .refund-status-card .custom-control,
    #modal-batal .refund-status-card .custom-control {
        margin-bottom: .35rem;
    }

    #modal-isi_data_konsumen .refund-status-card .custom-control:last-child,
    #modal-batal .refund-status-card .custom-control:last-child {
        margin-bottom: 0;
    }

    #modal-isi_data_konsumen .refund-status-note,
    #modal-batal .refund-status-note {
        color: #6b7280;
        display: block;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .45rem;
    }

    #modal-isi_data_konsumen .card-title {
        color: #111827;
        font-size: .95rem;
        font-weight: 700;
        margin-bottom: 0;
    }

    #modal-isi_data_konsumen .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #modal-isi_data_konsumen .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #modal-isi_data_konsumen .idk-hero-card {
        border: 0;
    }

    #modal-isi_data_konsumen .idk-sidebar .card-body {
        padding: .9rem 1rem;
    }

    #modal-isi_data_konsumen .idk-sidebar > .card > .col-12.pt-1 {
        padding: .9rem 1rem 0 !important;
    }

    #modal-isi_data_konsumen .idk-sidebar .btn-block {
        padding-left: .75rem;
        padding-right: .75rem;
    }

    #modal-isi_data_konsumen .idk-sidebar #div-hargajual .card {
        background: #fff;
        border-color: #cfd6e3;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
    }

    #modal-isi_data_konsumen .idk-sidebar #btn-print_spptb {
        margin-bottom: 1rem;
    }

    #modal-isi_data_konsumen #idk-diskresi_st {
        margin-left: 0;
        margin-right: 0;
    }

    #modal-isi_data_konsumen #idk-diskresi_st > [class*="col-"] {
        background: #fff !important;
        border: 1px solid #fecaca !important;
        border-left: 4px solid #dc2626 !important;
        border-radius: 8px !important;
        color: #111827 !important;
        flex: 0 0 100%;
        margin-bottom: .75rem;
        max-width: 100%;
        padding: .85rem 1rem;
    }

    #modal-isi_data_konsumen #idk-diskresi_st label,
    #modal-isi_data_konsumen #idk-diskresi_st span {
        color: #991b1b !important;
    }

    #modal-isi_data_konsumen #idk-diskresi_st .form-group {
        margin-bottom: 0;
    }

    #modal-isi_data_konsumen .divider {
        margin: .65rem 0 .85rem;
    }

    #modal-isi_data_konsumen .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modal-isi_data_konsumen .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal-isi_data_konsumen label,
    #modal-isi_data_konsumen .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal-isi_data_konsumen .form-group {
        margin-bottom: .8rem;
    }

    #modal-isi_data_konsumen .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal-isi_data_konsumen .form-control:disabled,
    #modal-isi_data_konsumen .form-control[readonly] {
        background-color: #f8fafc;
        color: #111827;
        opacity: 1;
    }

    #modal-isi_data_konsumen .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #modal-isi_data_konsumen .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modal-isi_data_konsumen .btn-primary:hover,
    #modal-isi_data_konsumen .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modal-isi_data_konsumen .btn-outline-primary {
        border-color: #2057a3 !important;
        color: #2057a3 !important;
    }

    #modal-isi_data_konsumen .btn-outline-primary:hover,
    #modal-isi_data_konsumen .btn-outline-primary:focus {
        background-color: #2057a3 !important;
        color: #fff !important;
    }

    #modal-isi_data_konsumen .nav-tabs {
        border-bottom: 0;
        gap: .4rem;
        margin: 0 !important;
    }

    #modal-isi_data_konsumen .nav-tabs .nav-link {
        border: 0;
        border-radius: 6px;
        color: #4b5563;
        font-size: .82rem;
        font-weight: 700;
        padding: .55rem .8rem;
        white-space: nowrap;
    }

    #modal-isi_data_konsumen .nav-tabs .nav-link.active {
        background-color: #2057a3;
        box-shadow: 0 6px 14px rgba(32, 87, 163, .2);
        color: #fff;
    }

    #modal-isi_data_konsumen .tab-content {
        min-width: 0;
    }

    #modal-isi_data_konsumen .tab-pane {
        overflow-x: auto;
    }

    #modal-isi_data_konsumen .dropzone {
        border: 1px dashed #b7c4d7;
        border-radius: 8px;
        min-height: 150px;
        overflow: hidden;
    }

    #modal-isi_data_konsumen .dz-inner {
        background: #f8fafc;
        min-height: 150px;
    }

    #modal-isi_data_konsumen .dz-placeholder {
        padding: .85rem;
    }

    #modal-isi_data_konsumen .dz-placeholder .h5 {
        color: #111827;
        font-size: .9rem;
        font-weight: 800;
    }

    #modal-isi_data_konsumen .sticky-button-wrapper {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -8px 18px rgba(15, 23, 42, .06);
        border-radius: 8px;
        /* margin: 1rem -1rem -1rem; */
        /* padding: .85rem 1rem; */
    }

    .dark-layout #modal-isi_data_konsumen .modal-header,
    .dark-layout #modal-isi_data_konsumen .card,
    .dark-layout #modal-isi_data_konsumen .card-header,
    .dark-layout #modal-isi_data_konsumen .idk-sidebar #div-hargajual .card,
    .dark-layout #modal-isi_data_konsumen #idk-diskresi_st > [class*="col-"],
    .dark-layout #modal-isi_data_konsumen #tab-isi-konsumen > .card .card-body,
    .dark-layout #modal-isi_data_konsumen .sticky-button-wrapper {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modal-isi_data_konsumen .modal-title,
    .dark-layout #modal-isi_data_konsumen .card-title,
    .dark-layout #modal-isi_data_konsumen .divider .divider-text {
        color: #f8fafc;
    }

    .dark-layout #modal-isi_data_konsumen .modal-body,
    .dark-layout #modal-isi_data_konsumen .form-control:disabled,
    .dark-layout #modal-isi_data_konsumen .form-control[readonly],
    .dark-layout #modal-isi_data_konsumen .dz-inner {
        background: #1f2937 !important;
    }

    @media (max-width: 1199.98px) {
        #modal-isi_data_konsumen .idk-konsumen-layout {
            flex-wrap: wrap;
        }

        #modal-isi_data_konsumen .idk-sidebar,
        #modal-isi_data_konsumen .idk-main-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal-isi_data_konsumen .idk-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #modal-isi_data_konsumen .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal-isi_data_konsumen .modal-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal-isi_data_konsumen .card-body {
            padding: .85rem;
        }

        #modal-isi_data_konsumen .nav-tabs {
            flex-direction: row !important;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .25rem;
        }

        #modal-isi_data_konsumen .nav-tabs .nav-link {
            white-space: nowrap;
        }

        #modal-isi_data_konsumen .sticky-button-wrapper {
            margin: .85rem -.85rem -.85rem;
        }
    }

    /* SIGAPP UI Acuan - Modal Set Harga */
    #modals-set_harga .modal-dialog {
        max-width: min(1120px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modals-set_harga .modal-content {
        background: #fff;
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modals-set_harga .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modals-set_harga .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modals-set_harga .set-harga-project {
        color: #6b7280;
        display: block;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.35;
        margin-top: .2rem;
    }

    #modals-set_harga .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modals-set_harga .set-harga-layout {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
    }

    #modals-set_harga .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modals-set_harga .card:last-child {
        margin-bottom: 0;
    }

    #modals-set_harga .card-body {
        padding: 1rem;
    }

    #modals-set_harga .divider {
        margin: .2rem 0 .85rem;
    }

    #modals-set_harga .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modals-set_harga .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modals-set_harga .divider .divider-text i {
        color: #2057a3;
    }

    #modals-set_harga label,
    #modals-set_harga .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modals-set_harga .form-group {
        margin-bottom: .8rem;
    }

    #modals-set_harga .form-control,
    #modals-set_harga .custom-select {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        color: #111827;
        min-height: 36px;
    }

    #modals-set_harga .form-control:disabled,
    #modals-set_harga .form-control[readonly] {
        background-color: #f8fafc;
        color: #111827;
        opacity: 1;
    }

    #modals-set_harga textarea.form-control {
        min-height: 92px;
        resize: vertical;
    }

    #modals-set_harga .set-harga-note {
        color: #6b7280;
        display: block;
        font-size: .76rem;
        line-height: 1.35;
        margin-top: .45rem;
    }

    #modals-set_harga .set-harga-detail-grid {
        display: grid;
        gap: .8rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    #modals-set_harga .set-harga-detail-grid .form-group {
        margin-bottom: 0;
    }

    #modals-set_harga .set-harga-highlight input {
        color: #2057a3;
        font-weight: 800;
    }

    #modals-set_harga .select2-container {
        width: 100% !important;
    }

    #modals-set_harga .select2-container--default .select2-selection--single {
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modals-set_harga .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 34px;
        padding-left: .75rem;
    }

    #modals-set_harga .select2-container--default .select2-selection--single .select2-selection__arrow {
        min-height: 36px;
    }

    #modals-set_harga .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1rem;
    }

    #modals-set_harga .btn {
        border-radius: 6px;
        font-weight: 700;
    }

    #modals-set_harga .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modals-set_harga .btn-primary:hover,
    #modals-set_harga .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modals-set_harga .btn-outline-primary {
        border-color: #2057a3 !important;
        color: #2057a3 !important;
    }

    #modals-set_harga .btn-outline-primary:hover,
    #modals-set_harga .btn-outline-primary:focus {
        background-color: #2057a3 !important;
        color: #fff !important;
    }

    .dark-layout #modals-set_harga .modal-header,
    .dark-layout #modals-set_harga .modal-footer,
    .dark-layout #modals-set_harga .card {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modals-set_harga .modal-title,
    .dark-layout #modals-set_harga .divider .divider-text {
        color: #f8fafc;
    }

    .dark-layout #modals-set_harga .modal-body,
    .dark-layout #modals-set_harga .form-control:disabled,
    .dark-layout #modals-set_harga .form-control[readonly] {
        background: #1f2937 !important;
    }

    @media (max-width: 991.98px) {
        #modals-set_harga .set-harga-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        #modals-set_harga .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modals-set_harga .modal-body {
            max-height: calc(100vh - 7rem);
            padding: .75rem;
        }

        #modals-set_harga .card-body {
            padding: .85rem;
        }

        #modals-set_harga .set-harga-detail-grid {
            grid-template-columns: 1fr;
        }

        #modals-set_harga .modal-footer {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        #modals-set_harga .modal-footer .btn {
            width: 100%;
        }
    }

    /* SIGAPP UI Acuan - Modal Mkdt Divisi 4 (mengikuti #modal-isi_data_konsumen) */
    #modal_divisi4 .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal_divisi4 .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modal_divisi4 .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modal_divisi4 .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal_divisi4 .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow: hidden;
        padding: 1rem;
    }

    #modal_divisi4 .modal-body > .row {
        margin-left: -.5rem;
        margin-right: -.5rem;
    }

    #modal_divisi4 .modal-body > .row > [class*="col-"] {
        padding-left: .5rem;
        padding-right: .5rem;
    }

    #modal_divisi4 .mkdt-layout {
        align-items: flex-start;
        flex-wrap: nowrap;
        gap: 1rem;
        height: 100%;
        margin: 0;
    }

    #modal_divisi4 .mkdt-layout > .mkdt-sidebar,
    #modal_divisi4 .mkdt-layout > .mkdt-main-content {
        padding: 0;
    }

    #modal_divisi4 .mkdt-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        max-height: calc(100vh - 8rem);
        max-width: 320px;
        overflow-y: auto;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    #modal_divisi4 .mkdt-main-content {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        max-height: calc(100vh - 8rem);
        max-width: calc(100% - 336px);
        min-width: 0;
        overflow: hidden;
    }

    #modal_divisi4 .mkdt-nav-sticky {
        flex: 0 0 auto;
        margin-bottom: 1rem;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    #modal_divisi4 .mkdt-scroll-content {
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    #modal_divisi4 .scroll-section {
        scroll-margin-top: .5rem;
    }

    #modal_divisi4 .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modal_divisi4 .card-body {
        padding: 1rem;
    }

    #modal_divisi4 .mkdt-hero-card {
        border: 0;
    }

    #modal_divisi4 .mkdt-sidebar .card-body {
        padding: .9rem 1rem;
    }

    #modal_divisi4 .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #modal_divisi4 .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #modal_divisi4 .info-row {
        align-items: flex-start;
        background: #f9fafb;
        border: 1px solid #edf0f2;
        border-radius: 6px;
        margin: 0 0 .45rem;
        padding: .45rem .55rem;
    }

    #modal_divisi4 .info-row:last-child {
        margin-bottom: 0;
    }

    #modal_divisi4 .info-label {
        color: #6b7280;
        font-size: .76rem;
        font-weight: 700;
    }

    #modal_divisi4 .info-value {
        color: #111827;
        display: block;
        font-weight: 700;
        min-height: 1.2em;
        overflow-wrap: anywhere;
    }

    #modal_divisi4 .mkdt-kpr-sidebar-card .card-body {
        padding: 1rem;
    }

    #modal_divisi4 .mkdt-kpr-required-note {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 6px;
        color: #9a3412;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.45;
        margin-bottom: .85rem;
        padding: .65rem .75rem;
    }

    #modal_divisi4 .divider {
        margin: .65rem 0 .85rem;
    }

    #modal_divisi4 .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modal_divisi4 .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal_divisi4 .divider .divider-text i {
        color: #2057a3;
    }

    #modal_divisi4 label,
    #modal_divisi4 .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal_divisi4 .form-group {
        margin-bottom: .8rem;
    }

    #modal_divisi4 .form-control,
    #modal_divisi4 .custom-select,
    #modal_divisi4 .select2-container--default .select2-selection--single {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal_divisi4 .form-control:focus,
    #modal_divisi4 .custom-select:focus,
    #modal_divisi4 .select2-container--focus .select2-selection--single {
        border-color: #2057a3;
        box-shadow: 0 0 0 .15rem rgba(32, 87, 163, .12);
    }

    #modal_divisi4 textarea.form-control {
        min-height: 90px;
        resize: vertical;
    }

    #modal_divisi4 .select2-container {
        width: 100% !important;
    }

    #modal_divisi4 .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111827;
        line-height: 34px;
        padding-left: .75rem;
    }

    #modal_divisi4 .select2-container--default .select2-selection--single .select2-selection__arrow {
        min-height: 34px;
    }

    #modal_divisi4 .form-control:disabled,
    #modal_divisi4 .form-control[readonly] {
        background-color: #f8fafc;
        color: #111827;
        opacity: 1;
    }

    #modal_divisi4 .dropzone {
        background: transparent;
        border: 0;
        cursor: pointer;
        min-height: 118px;
        padding: 0;
        width: 100%;
    }

    #modal_divisi4 .dropzone .custom-file-input,
    #modal_divisi4 .dropzone .dz-input {
        cursor: pointer;
        height: 100%;
        inset: 0;
        opacity: 0;
        position: absolute;
        width: 100%;
        z-index: 2;
    }

    #modal_divisi4 .dz-inner {
        align-items: center;
        background: #f8fafc;
        border: 1px dashed #b9c2d0;
        border-radius: 8px;
        color: #4b5563;
        display: flex;
        justify-content: center;
        min-height: 118px;
        padding: .85rem;
        position: relative;
        text-align: center;
        transition: border-color .16s ease, background-color .16s ease;
    }

    #modal_divisi4 .dropzone:hover .dz-inner,
    #modal_divisi4 .dropzone:focus-within .dz-inner {
        background: #eef5ff;
        border-color: #2057a3;
    }

    #modal_divisi4 .dz-placeholder .h5 {
        color: #111827;
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: .2rem;
    }

    #modal_divisi4 .dz-preview {
        width: 100%;
    }

    #modal_divisi4 .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #modal_divisi4 .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modal_divisi4 .btn-primary:hover,
    #modal_divisi4 .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modal_divisi4 .btn-outline-primary {
        border-color: #2057a3 !important;
        color: #2057a3 !important;
    }

    #modal_divisi4 .btn-outline-primary:hover,
    #modal_divisi4 .btn-outline-primary:focus {
        background-color: #2057a3 !important;
        color: #fff !important;
    }

    #modal_divisi4 .mkdt-main-content > .card .card-body {
        background: #fff;
        border-bottom: 1px solid #edf0f2;
        padding: .85rem 1rem;
    }

    #modal_divisi4 .nav-tabs {
        border-bottom: 0;
        gap: .4rem;
        margin: 0 !important;
    }

    #modal_divisi4 .nav-tabs .nav-link {
        border: 0;
        border-radius: 6px;
        color: #4b5563;
        font-size: .82rem;
        font-weight: 700;
        padding: .55rem .8rem;
        white-space: nowrap;
    }

    #modal_divisi4 .nav-tabs .nav-link.active {
        background-color: #2057a3;
        box-shadow: 0 6px 14px rgba(32, 87, 163, .2);
        color: #fff;
    }

    #modal_divisi4 .sticky-button-wrapper {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -8px 18px rgba(15, 23, 42, .06);
        flex: 0 0 auto;
        margin: 1rem 0 0;
        padding: .85rem 1rem;
    }

    #modal_divisi4 .mkdt-main-tabs {
        border-bottom: 1px solid #e5e7eb;
        flex: 0 0 auto;
        margin-bottom: .75rem;
    }

    #modal_divisi4 .mkdt-main-tab-content {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
    }

    #modal_divisi4 .mkdt-main-tab-content > .tab-pane {
        display: none;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
    }

    #modal_divisi4 .mkdt-main-tab-content > .tab-pane.active {
        display: flex;
    }

    #modal_divisi4 .mkdt-history-wrap {
        max-height: calc(100vh - 18rem);
        overflow-y: auto;
        padding-right: .25rem;
    }

    #modal_divisi4 .mkdt-history-item {
        border-left: 3px solid #2057a3;
        margin-bottom: .85rem;
        padding: .75rem .85rem .75rem 1rem;
        background: #f8fafc;
        border-radius: 0 .5rem .5rem 0;
    }

    #modal_divisi4 .mkdt-history-item:last-child {
        margin-bottom: 0;
    }

    #modal_divisi4 .mkdt-history-title {
        color: #2057a3;
        font-size: .92rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    #modal_divisi4 .mkdt-history-meta {
        color: #64748b;
        font-size: .78rem;
        margin-bottom: .35rem;
    }

    #modal_divisi4 .mkdt-history-summary {
        color: #334155;
        font-size: .86rem;
        line-height: 1.45;
        white-space: pre-wrap;
    }

    .dark-layout #modal_divisi4 .mkdt-history-item {
        background: #1f2937;
        border-left-color: #60a5fa;
    }

    .dark-layout #modal_divisi4 .mkdt-history-title {
        color: #93c5fd;
    }

    .dark-layout #modal_divisi4 .mkdt-history-meta,
    .dark-layout #modal_divisi4 .mkdt-history-summary {
        color: #cbd5e1;
    }

    .dark-layout #modal_divisi4 .modal-header,
    .dark-layout #modal_divisi4 .card,
    .dark-layout #modal_divisi4 .mkdt-main-content > .card .card-body,
    .dark-layout #modal_divisi4 .sticky-button-wrapper {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modal_divisi4 .modal-title,
    .dark-layout #modal_divisi4 .divider .divider-text {
        color: #f8fafc;
    }

    .dark-layout #modal_divisi4 .modal-body,
    .dark-layout #modal_divisi4 .form-control:disabled,
    .dark-layout #modal_divisi4 .form-control[readonly],
    .dark-layout #modal_divisi4 .dz-inner {
        background: #1f2937 !important;
    }

    @media (max-width: 1199.98px) {
        #modal_divisi4 .mkdt-layout {
            flex-wrap: wrap;
        }

        #modal_divisi4 .mkdt-sidebar,
        #modal_divisi4 .mkdt-main-content {
            flex: 0 0 100%;
            max-width: 100%;
        }

        #modal_divisi4 .mkdt-sidebar {
            max-height: none;
            overflow-y: visible;
            position: static;
        }

        #modal_divisi4 .mkdt-main-content {
            max-height: calc(100vh - 12rem);
        }
    }

    @media (max-width: 767.98px) {
        #modal_divisi4 .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modal_divisi4 .modal-body {
            max-height: calc(100vh - 5.5rem);
            padding: .75rem;
        }

        #modal_divisi4 .card-body {
            padding: .85rem;
        }

        #modal_divisi4 .mkdt-main-content {
            max-height: calc(100vh - 14rem);
        }

        #modal_divisi4 .nav-tabs {
            flex-direction: row !important;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: .25rem;
        }

        #modal_divisi4 .nav-tabs .nav-link {
            white-space: nowrap;
        }

        #modal_divisi4 .sticky-button-wrapper {
            margin: .85rem 0 0;
            padding: .75rem;
        }
    }

    /* SIGAPP UI Acuan - Modal Turun Pembangunan */
    #modals-turun_pembangunan .modal-dialog {
        max-width: min(980px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modals-turun_pembangunan .modal-content {
        background: #fff;
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modals-turun_pembangunan .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modals-turun_pembangunan .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modals-turun_pembangunan .tp-project {
        color: #6b7280;
        display: block;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1.35;
        margin-top: .2rem;
    }

    #modals-turun_pembangunan .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modals-turun_pembangunan .turun-pembangunan-layout {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
    }

    #modals-turun_pembangunan .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modals-turun_pembangunan .card:last-child {
        margin-bottom: 0;
    }

    #modals-turun_pembangunan .card-body {
        padding: 1rem;
    }

    #modals-turun_pembangunan .divider {
        margin: .2rem 0 .85rem;
    }

    #modals-turun_pembangunan .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modals-turun_pembangunan .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modals-turun_pembangunan .divider .divider-text i {
        color: #2057a3;
    }

    #modals-turun_pembangunan label,
    #modals-turun_pembangunan .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modals-turun_pembangunan .form-group {
        margin-bottom: .8rem;
    }

    #modals-turun_pembangunan .form-control,
    #modals-turun_pembangunan .custom-file-label {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        color: #111827;
        min-height: 36px;
    }

    #modals-turun_pembangunan .form-control:disabled,
    #modals-turun_pembangunan .form-control[readonly] {
        background-color: #f8fafc;
        color: #111827;
        opacity: 1;
    }

    #modals-turun_pembangunan textarea.form-control {
        min-height: 142px;
        resize: vertical;
    }

    #modals-turun_pembangunan .tp-note {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #6b7280;
        display: block;
        font-size: .76rem;
        line-height: 1.45;
        margin-top: .6rem;
        padding: .65rem .75rem;
    }

    #modals-turun_pembangunan .tp-meta-grid {
        display: grid;
        gap: .8rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    #modals-turun_pembangunan .tp-meta-grid .form-group {
        margin-bottom: 0;
    }

    #modals-turun_pembangunan .tp-file-action {
        align-items: center;
        display: flex;
        gap: .65rem;
        margin-top: .75rem;
    }

    #modals-turun_pembangunan .tp-file-action .btn {
        flex: 1 1 auto;
    }

    #modals-turun_pembangunan .btn {
        border-radius: 6px;
        font-weight: 700;
    }

    #modals-turun_pembangunan .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modals-turun_pembangunan .btn-primary:hover,
    #modals-turun_pembangunan .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modals-turun_pembangunan .btn-outline-primary {
        border-color: #2057a3 !important;
        color: #2057a3 !important;
    }

    #modals-turun_pembangunan .btn-outline-primary:hover,
    #modals-turun_pembangunan .btn-outline-primary:focus {
        background-color: #2057a3 !important;
        color: #fff !important;
    }

    #modals-turun_pembangunan .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1rem;
    }

    .dark-layout #modals-turun_pembangunan .modal-header,
    .dark-layout #modals-turun_pembangunan .modal-footer,
    .dark-layout #modals-turun_pembangunan .card {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modals-turun_pembangunan .modal-title,
    .dark-layout #modals-turun_pembangunan .divider .divider-text {
        color: #f8fafc;
    }

    .dark-layout #modals-turun_pembangunan .modal-body,
    .dark-layout #modals-turun_pembangunan .form-control:disabled,
    .dark-layout #modals-turun_pembangunan .form-control[readonly],
    .dark-layout #modals-turun_pembangunan .tp-note {
        background: #1f2937 !important;
    }

    @media (max-width: 991.98px) {
        #modals-turun_pembangunan .turun-pembangunan-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        #modals-turun_pembangunan .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modals-turun_pembangunan .modal-body {
            max-height: calc(100vh - 7rem);
            padding: .75rem;
        }

        #modals-turun_pembangunan .card-body {
            padding: .85rem;
        }

        #modals-turun_pembangunan .tp-meta-grid {
            grid-template-columns: 1fr;
        }

        #modals-turun_pembangunan .tp-file-action {
            align-items: stretch;
            flex-direction: column;
        }

        #modals-turun_pembangunan .modal-footer {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        #modals-turun_pembangunan .modal-footer .btn {
            width: 100%;
        }
    }

    /* SIGAPP UI Acuan - Modal Standing Instruction */
    #modals-si .modal-dialog {
        max-width: min(1180px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modals-si .modal-content {
        background: #fff;
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .18);
        overflow: hidden;
    }

    #modals-si .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 0 !important;
        padding: 1rem 1.25rem;
    }

    #modals-si .modal-title {
        color: #111827;
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modals-si .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 8rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modals-si .si-layout {
        align-items: flex-start;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
    }

    #modals-si .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    #modals-si .card:last-child {
        margin-bottom: 0;
    }

    #modals-si .card-body {
        padding: 1rem;
    }

    #modals-si .si-hero {
        border: 0;
    }

    #modals-si .bg-primary {
        background: linear-gradient(145deg, #2057a3 0%, #1f7a8c 100%) !important;
    }

    #modals-si .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    #modals-si .si-meta-card {
        background: #f8fafc;
        border-color: #edf0f2;
        margin-bottom: 0;
    }

    #modals-si .si-meta-card h6 {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        margin-bottom: .25rem;
    }

    #modals-si .si-meta-card h5 {
        color: #111827;
        font-size: .95rem;
        font-weight: 700;
        margin-bottom: .85rem;
        overflow-wrap: anywhere;
    }

    #modals-si .si-meta-card h5:last-child {
        margin-bottom: 0;
    }

    #modals-si .divider {
        margin: .2rem 0 .85rem;
    }

    #modals-si .divider-left {
        border-left-color: #2057a3;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modals-si .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modals-si label,
    #modals-si .form-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modals-si .form-group {
        margin-bottom: .8rem;
    }

    #modals-si .form-control,
    #modals-si .custom-file-label {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        color: #111827;
        min-height: 36px;
    }

    #modals-si textarea.form-control {
        min-height: 122px;
        resize: vertical;
    }

    #modals-si .si-file-action {
        align-items: center;
        display: flex;
        gap: .65rem;
    }

    #modals-si .si-file-action .btn {
        flex: 1 1 auto;
    }

    #modals-si #si-table {
        font-size: .86rem;
    }

    #modals-si #si-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        vertical-align: middle;
    }

    #modals-si #si-table tbody td {
        border-color: #edf0f2;
        vertical-align: middle;
    }

    #modals-si .btn {
        border-radius: 6px;
        font-weight: 700;
        white-space: normal;
    }

    #modals-si .btn-primary {
        background-color: #2057a3 !important;
        border-color: #2057a3 !important;
    }

    #modals-si .btn-primary:hover,
    #modals-si .btn-primary:focus {
        background-color: #174b8f !important;
        border-color: #174b8f !important;
    }

    #modals-si .btn-outline-primary {
        border-color: #2057a3 !important;
        color: #2057a3 !important;
    }

    #modals-si .btn-outline-primary:hover,
    #modals-si .btn-outline-primary:focus {
        background-color: #2057a3 !important;
        color: #fff !important;
    }

    #modals-si .modal-footer {
        background: #fff;
        border-top: 1px solid #e5e7eb;
        padding: .85rem 1rem;
    }

    .dark-layout #modals-si .modal-header,
    .dark-layout #modals-si .modal-footer,
    .dark-layout #modals-si .card,
    .dark-layout #modals-si .si-meta-card {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, .08) !important;
    }

    .dark-layout #modals-si .modal-title,
    .dark-layout #modals-si .divider .divider-text,
    .dark-layout #modals-si .si-meta-card h5 {
        color: #f8fafc;
    }

    .dark-layout #modals-si .modal-body {
        background: #1f2937 !important;
    }

    @media (max-width: 991.98px) {
        #modals-si .si-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        #modals-si .modal-dialog {
            max-width: calc(100vw - 12px);
            margin: .5rem auto;
        }

        #modals-si .modal-body {
            max-height: calc(100vh - 7rem);
            padding: .75rem;
        }

        #modals-si .card-body {
            padding: .85rem;
        }

        #modals-si .si-file-action {
            align-items: stretch;
            flex-direction: column;
        }

        #modals-si .modal-footer {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        #modals-si .modal-footer .btn {
            width: 100%;
        }
    }
</style>
<!-- ################################## Modal Isi Data Konsumen ##########################################-->
<?php /*echo view('siteplan/modal/mkdt-isi_data_konsumen'); */ ?>

<section class="isi_konsumen">
    <div class="modal fade" id="modal-isi_data_konsumen">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <form id="fm-idk_keu" class="add-new-record modal-content pt-0" enctype="multipart/form-data"
                autocomplete="off">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">Ã—</button> -->
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Isi Data Konsumen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body flex-grow-1" style="background-color:#eee; overflow-y: auto;">
                    <div class="row idk-konsumen-layout">
                        <div class="col-md-3 idk-sidebar">
                            <div class="card idk-hero-card">
                                <div class="card-body bg-primary text-light">
                                    <p class="modal-title label_alamat"></p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="col-12 pt-1">
                                    <div class="refresh_fmmkdt_div ">
                                        <button id="refresh-btn-idk_keu" type="button"
                                            class="btn btn-primary btn-block waves-effect">Tambah Konsumen
                                            Baru</button>
                                    </div>
                                    <div class="delete_kons_div" hidden>
                                        <button id="delete-btn-idk_keu" type="button"
                                            class="btn btn-outline-danger btn-block waves-effect"
                                            onclick="delete_kons(false)">Hapus Konsumen</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="divider divider-left">
                                        <div class="divider-text font-weight-bold">Status Kavling</div>
                                    </div>
                                    <div class="form-group floating-label floating-label-select">
                                        <select required class="form-control tab1" id="idk-status_mkdt"
                                            name="dt-status_mkdt">
                                            <option value="">-</option>
                                            <option value="Booking">Booking</option>
                                            <option value="Akad">Akad</option>
                                            <option value="Batal">Batal</option>
                                        </select>
                                        <label for="idk-status_mkdt">Status Kavling</label>
                                    </div>

                                    <div id="idk-show_keterangan_batal" class="hidden">
                                        <div class="form-group">
                                            <label for="keterangan_batal">Keterangan Batal</label>
                                            <textarea class="form-control" id="idk-keterangan_batal"
                                                name="dt-keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Status Refund</label>
                                            <div class="refund-status-card">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input"
                                                        id="idk-perlu_refund_0" name="dt-perlu_refund" value="0" checked>
                                                    <label class="custom-control-label" for="idk-perlu_refund_0">Tidak Perlu Refund</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input"
                                                        id="idk-perlu_refund_1" name="dt-perlu_refund" value="1">
                                                    <label class="custom-control-label" for="idk-perlu_refund_1">Perlu Refund</label>
                                                </div>
                                                <small class="refund-status-note">Status ini akan tampil di list konsumen batal.</small>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div id="div-hargajual">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="divider divider-left">
                                                <div class="divider-text font-weight-bold">Harga Jual
                                                    Pricelist
                                                </div>
                                            </div>
                                            <input type="text" readonly class="form-control num mk-fm"
                                                id="idk-mkdt_hargajual" name="idk-mkdt_hargajual" value="" />
                                            <span>Harga diinput oleh: <span id="idk-mkdt_hargajual_by"
                                                    style="font-weight:bold"></span>
                                                pada: <span id="idk-mkdt_hargajual_tgl"
                                                    style="font-weight:bold"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <a href="" id="btn-print_spptb" target="_blank" class="btn btn-success col-12"><i
                                        class="fa fa-save"></i> Cetak SPPTB</a>
                            </div>
                            <div class="row" id="idk-diskresi_st">
                                <div style=" border: 1px solid red; background-color: red; border-radius: 10px 0px 0px 10px; color: white;"
                                    class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname"
                                            style="color:white">Diskresi harga</label>
                                        <input type="text" readonly class="form-control num" id="idk-diskresi_harga"
                                            name="mkdt_hargajual" value="" />
                                        <span>Diskresi diinput oleh: <span style="font-weight:bold"
                                                id="idk-diskresi_oleh"></span> pada: <span id="idk-diskresi_tgl"
                                                style="font-weight:bold"></span></span>

                                    </div>
                                </div>
                                <div class="col-md-6"
                                    style="border: 1px solid red; background-color: red; border-radius: 0px 10px 10px 0px; color: white;">
                                    <div class="form-group">
                                        <label class="form-label" for="basic-icon-default-fullname"
                                            style="color:white">Memo</label>
                                        <textarea name="idk-diskresi_memo" readonly id="idk-diskresi_memo"
                                            class="form-control" cols="30" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9 idk-main-content" id="tab-isi-konsumen">
                            <div class="card">
                                <div class="card-body pb-0 pt-0">
                                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link locked active" id="idk_data_konsumen-tab"
                                                data-toggle="tab" href="#idk_data_konsumen"
                                                aria-controls="idk_data_konsumen" role="tab" aria-selected="true">1.
                                                Data Konsumen ></a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link locked" id="idk_biaya-tab" data-toggle="tab"
                                                href="#idk_biaya" aria-controls="idk_biaya" role="tab"
                                                aria-selected="true">2. Harga Jual
                                                ></a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link locked" id="idk_tagihan-tab" data-toggle="tab"
                                                href="#idk_tagihan" aria-controls="data_konsumen" role="tab"
                                                aria-selected="true">3. Tagihan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="idk_arsip-tab" data-toggle="tab" href="#idk_arsip"
                                                aria-controls="idk_arsip" role="tab" aria-selected="true">SPPTB
                                                Ditandatangani</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="idk_riwayat-tab" data-toggle="tab"
                                                href="#idk_riwayat" aria-controls="idk_riwayat" role="tab"
                                                aria-selected="true">Riwayat Pindah
                                                Kavling/Ganti Nama</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane show active" id="idk_data_konsumen"
                                    aria-labelledby="idk_data_konsumen-tab" role="tabpanel">
                                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                                    <input type="hidden" class="form-control" id="idk-id_mkdt" name="id_mkdt"
                                        value="" />
                                    <input type="hidden" class="form-control" id="idk-id_konsumen" name="id_konsumen"
                                        value="" />

                                    <input type="hidden" class="form-control" id="idk-harga_akhir"
                                        name="idk-harga_akhir" value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-hargajual" name="idk-hargajual"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-kpr" name="idk-kpr" value=""
                                        readonly />
                                    <input type="hidden" class="form-control" id="idk-uang_muka" name="mkdt-uang_muka"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-bphtb" name="idk-bphtb" value=""
                                        readonly />
                                    <input type="hidden" class="form-control" id="idk-biaya_adm" name="idk-biaya_adm"
                                        value="" readonly />
                                    <input type="hidden" class="form-control" id="idk-biaya_proses"
                                        name="idk-biaya_proses" value="" readonly />

                                    <input type="hidden" class="form-control" id="idk_data_baru" name="mkdt_data_baru"
                                        value="" />

                                    <div class="row align-items-stretch">
                                        <div class="col-sm-12 col-md-12 col-lg-12 text-center">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">File Upload</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4"> <!-- KTP -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">KTP</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-ktp">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="image/*" name="file_ktp" id="file_ktp">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview" id="prev_file_ktp">
                                                                        </div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan gambar
                                                                                ke sini</div>
                                                                            <div class="text-muted">atau klik untuk
                                                                                pilih file (PNG/JPG maks 5 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_ktp-here"
                                                                onclick="window.open(this.href, '_blank'); return false;"
                                                                class="w-100 btn btn-outline-primary">klik
                                                                untuk melihat file</a>
                                                            <button type="button" id="btn-ocr-ktp"
                                                                class="w-100 btn btn-outline-secondary btn-sm mt-1">
                                                                <i class="fa fa-search"></i> OCR KTP
                                                            </button>
                                                        </div>
                                                        <div class="col-md-4"> <!-- NPWP -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">NPWP</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-npwp">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="image/*" name="file_npwp"
                                                                        id="file_npwp">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview" id="prev_file_npwp">
                                                                        </div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan gambar
                                                                                ke sini</div>
                                                                            <div class="text-muted">atau klik (PNG/JPG
                                                                                maks 5 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_npwp-here"
                                                                onclick="window.open(this.href, '_blank'); return false;"
                                                                class=" btn btn-outline-primary w-100">klik untuk
                                                                melihat file</a>
                                                        </div>
                                                        <div class="col-md-4"> <!-- Data Diri (PDF) -->
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Data Diri (PDF)</label>
                                                                <div class="dropzone dropzone-lg custom-file"
                                                                    id="dz-data-diri">
                                                                    <input type="file"
                                                                        class="custom-file-input dz-input"
                                                                        accept="application/pdf" name="file_data_diri"
                                                                        id="file_data_diri">
                                                                    <div class="dz-inner">
                                                                        <div class="dz-preview"
                                                                            id="prev_file_data_diri"></div>
                                                                        <div class="dz-placeholder">
                                                                            <div class="h5 mb-1">Tarik & letakkan PDF ke
                                                                                sini</div>
                                                                            <div class="text-muted">atau klik (PDF maks
                                                                                10 MB)</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <a href="" id="idk-file_data_diri-here"
                                                                class="btn btn-outline-primary w-100"
                                                                onclick="window.open(this.href, '_blank'); return false;">klik
                                                                untuk melihat file</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Data Konsumen</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Data Pribadi
                                                                </div>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="st-mkdt-no_spptb" name="no_spptb" placeholder=" "
                                                                    required>
                                                                <label for="idk-no_spptb">No SPPTB</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_konsumen" required name="nama_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-nama_konsumen">Nama Konsumen</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nik_konsumen" name="nik_konsumen"
                                                                    placeholder=" " required>
                                                                <label for="idk-nik_konsumen">No. KTP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_konsumen" name="alamat_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_konsumen">Alamat Konsumen</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-npwp_konsumen" name="npwp_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-npwp_konsumen">NPWP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-hp_konsumen" name="hp_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-hp_konsumen">No. HP/telp</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-email_konsumen" name="email_konsumen"
                                                                    placeholder=" ">
                                                                <label for="idk-email_konsumen">Email Konsumen</label>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label for="idk-status_konsumen">Status Konsumen</label>
                                                                <select class="form-control" id="idk-status_konsumen"
                                                                    name="status_konsumen">
                                                                    <option value="">-</option>
                                                                    <option value="Umum">Umum</option>
                                                                    <option value="TWP">TWP</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Data Instansi
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_instansi" name="nama_instansi"
                                                                    placeholder=" " required>
                                                                <label for="idk-nama_instansi">Nama Instansi</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_instansi" name="alamat_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_instansi">Alamat Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-tel_instansi" name="tel_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-tel_instansi">No Hpt/telp
                                                                    Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-email_instansi" name="email_instansi"
                                                                    placeholder=" ">
                                                                <label for="idk-email_instansi">Email Instansi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-alamat_surat" name="alamat_surat"
                                                                    placeholder=" ">
                                                                <label for="idk-alamat_surat">Alamat Surat</label>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control" id="idk-pekerjaan"
                                                                    name="pekerjaan">
                                                                    <option value="Karyawan">Karyawan</option>
                                                                    <option value="Wirausaha">Wirausaha</option>
                                                                </select>
                                                                <label for="idk-pekerjaan">Pekerjaan</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" placeholder=" " class="form-control"
                                                                    id="idk-lama_bekerja" name="lama_bekerja">
                                                                <label for="idk-lama_bekerja">Lama Bekerja</label>
                                                            </div>
                                                            <div class="form-group floating-label">

                                                                <input type="text" placeholder=" " class="form-control"
                                                                    id="idk-bidang_pekerjaan" name="bidang_pekerjaan">
                                                                <label for="bidang_pekerjaan">Bidang Pekerjaan</label>
                                                            </div>


                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Status
                                                                    Pernikahan</div>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select class="form-control gn tab1"
                                                                    id="idk-status_pernikahan" name="status_pernikahan">
                                                                    <option value="Belum Kawin">Belum Kawin</option>
                                                                    <option value="Kawin">Kawin</option>
                                                                    <option value="Cerai Mati">Cerai Mati</option>
                                                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                                                </select>
                                                                <label for="idk-status_pernikahan">Status
                                                                    Pernikahan</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nama_pasangan" name="nama_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-nama_pasangan">Nama</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-nik_pasangan" name="nik_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-nik_pasangan">No. KTP</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-hp_pasangan" name="hp_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-hp_pasangan">No. HP/Telp</label>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <label for="idk-status_pekerjaan_pasangan">Status
                                                                    Pekerjaan</label>
                                                                <select required class="form-control"
                                                                    id="idk-status_pekerjaan_pasangan"
                                                                    name="status_pekerjaan_pasangan">
                                                                    <option value="Bekerja">Bekerja</option>
                                                                    <option value="Tidak Bekerja">Tidak Bekerja</option>
                                                                    <option value="Ibu Rumah Tangga">Ibu Rumah Tangga
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control gn tab1"
                                                                    id="idk-instansi_pasangan" name="instansi_pasangan"
                                                                    placeholder=" ">
                                                                <label for="idk-instansi_pasangan">Instansi</label>
                                                            </div>

                                                        </div>

                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Sales & Promo
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control" id="idk-sales"
                                                                    name="sales" placeholder=" ">
                                                                <label for="idk-sales">Sales</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control" id="idk-promo"
                                                                    name="promo" placeholder=" ">
                                                                <label for="idk-promo">Promo/Bonus/Hadiah</label>
                                                            </div>

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">TUNAI/KPR
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control" id="idk-is_kpr"
                                                                    name="is_kpr" onchange="sum_mktotal()">
                                                                    <option value="0">TUNAI/CASH KERAS</option>
                                                                    <option value="2">TUNAI/CASH BERTAHAP</option>
                                                                    <option value="1">KPR</option>
                                                                </select>
                                                                <label for="idk-is_kpr">Tunai/KPR</label>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select required class="form-control"
                                                                    id="idk-is_subsidi" name="is_subsidi">
                                                                    <option value="0">Non-Subsidi</option>
                                                                    <option value="1">Subsidi</option>
                                                                </select>
                                                                <label for="idk-is_subsidi">Subsidi/Non-Subsidi</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control"
                                                                    id="idk-jenis_subsidi" name="jenis_subsidi"
                                                                    placeholder=" ">
                                                                <label for="idk-jenis_subsidi">Jenis Subsidi</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">X Tutup</button>
                                            <a class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('#idk_biaya-tab')">
                                                Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_biaya" aria-labelledby="idk_biaya-tab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">Detail Harga Jual</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">1. Booking
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" required id="idk-booking_tgl"
                                                                    name="dt-booking_tgl"
                                                                    class="form-control flatpickr-human-friendly tab2"
                                                                    placeholder=" " />

                                                                <label for="idk-booking_tgl">Tanggal Booking</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" required
                                                                    class="form-control num tab2" disabled placeholder=" "
                                                                    id="idk-booking_fee" name="dt-booking_fee">
                                                                <label for="idk-booking_fee">Booking Fee</label>
                                                            </div>
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">2. Harga Jual
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control text-right mk-fm flatpickr-human-friendly"
                                                                    id="mk-tgl_harga" name="mk-tgl_harga" value=""
                                                                    readonly placeholder=" " />
                                                                <label class="form-label" for="mk-tgl_harga">Tanggal
                                                                    PriceList</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm hitung-tambah"
                                                                    id="mk-hargajual" name="mk-hargajual" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-hargajual">Harga
                                                                    Jual</label>
                                                            </div>

                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm "
                                                                    id="mk-hargajual_net" name="mk-hargajual_net"
                                                                    value="" placeholder=" " />
                                                                <label class="form-label" for="mk-hargajual_net">Harga
                                                                    Jual
                                                                    Net</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-kpr" name="mk-kpr" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-kpr">KPR(Pengajuan)</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-uang_muka" name="mk-uang_muka" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-uang_muka">Uang
                                                                    Muka</label>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-3">

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">3.
                                                                    Biaya-biaya</div>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-biaya_adm" name="mk-biaya_adm" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-biaya_adm">Biaya
                                                                    Adm</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb" id="mk-ppn"
                                                                    name="mk-ppn" placeholder=" ">
                                                                <label for="mk-ppn">PPN</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb" id="mk-bphtb"
                                                                    name="mk-bphtb" value="" placeholder=" " />
                                                                <label class="form-label" for="mk-bphtb">BPHTB</label>
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <input type="text"
                                                                    class="form-control num mk-fm totalbb"
                                                                    id="mk-biaya_proses" name="mk-biaya_proses" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-biaya_proses">Biaya
                                                                    Proses</label>
                                                            </div>
                                                            <!-- disembunyikan dulu karna masih belum tau bakal kepake atau engga -->
                                                            <div class="hidden">
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-row" name="mk-row" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-row">ROW</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text"
                                                                        class="form-control mk-fm text-right"
                                                                        id="mk-tipe" name="mk-tipe" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-tipe">Tipe</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-lb" name="mk-lb" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-lb">LB</label>
                                                                </div>
                                                                <div class="form-group floating-label">
                                                                    <input type="text" class="form-control num mk-fm"
                                                                        id="mk-lt" name="mk-lt" value=""
                                                                        placeholder=" " />
                                                                    <label class="form-label" for="mk-lt">LT</label>
                                                                </div>

                                                            </div>
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">4. Biaya
                                                                    Tambahan</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Biaya Kelebihan Tanah</label>
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan_tanah"
                                                                    name="mk-harga_penambahan_tanah">
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label for="total_biaya2">Keterangan Penambahan
                                                                    Biaya</label>
                                                                <textarea name="mk-keterangan_harga_penambahan"
                                                                    id="mk-keterangan_harga_penambahan"
                                                                    class="form-control mk-fm" cols="30"
                                                                    rows="2"></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Biaya Kavling
                                                                    Strategis</label>
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan" name="mk-harga_penambahan">
                                                            </div>

                                                        </div>

                                                        <div class="col-md-3">

                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">5. Potongan
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label hidden" id="hjdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-diskon_harga_jual"
                                                                    name="mk-diskon_harga_jual" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-diskon_harga_jual">Diskon Harga
                                                                    Jual</label>
                                                            </div>
                                                            <div class="form-group floating-label" id="umdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-diskon_uang_muka" name="mk-diskon_uang_muka"
                                                                    value="" placeholder=" " />
                                                                <label class="form-label"
                                                                    for="mk-diskon_uang_muka">Diskon</label>
                                                            </div>
                                                            <div class="form-group floating-label" id="sbumdis">
                                                                <input type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_sbum" name="mk-harga_sbum" value=""
                                                                    placeholder=" " />
                                                                <label class="form-label" for="mk-sbum">SBUM</label>
                                                            </div>
                                                            <!-- <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">6. KPR Diseutjui</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">KPR Disetujui</label>
                                                                <input readonly type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_kpr_acc" name="mk-harga_kpr_acc">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_biaya2">Turun KPR</label>
                                                                <input readonly type="text" class="form-control num mk-fm"
                                                                    id="mk-harga_penambahan_um" name="mk-harga_penambahan_um">
                                                            </div> -->
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">6.
                                                                    Catatan/Keterangan</div>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <textarea class="form-control" rows="4" id="idk-rincian"
                                                                    name=" "></textarea>
                                                                <label class="form-label" for="mk-lt">Keterangan</label>
                                                            </div>


                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">7. Total
                                                                </div>
                                                            </div>
                                                            <div class="form-group floating-label floating-label-select">
                                                                <select required class="form-control tab1" id="idk-is_allin"
                                                                    name="idk-is_allin">
                                                                    <option value=0>Tidak</option>
                                                                    <option value=1>Ya</option>
                                                                </select>
                                                                <label for="idk-is_allin">Harga All In</label>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label>Total Uang Muka + Biaya ADM</label>
                                                                <input readonly type="text" class="form-control num tum"
                                                                    id="mk-tum" name="mk-tum">
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label>Total Biaya-Biaya</label>
                                                                <input readonly type="text" class="form-control num tbb"
                                                                    id="mk-tbb" name="mk-tbb">
                                                            </div>
                                                            <div class="form-group floating-label">
                                                                <label>Total Harga Allin</label>
                                                                <input placeholder=" " type="text" required
                                                                    class="form-control num mk-fm" id="mk-harga_allin"
                                                                    name="mk-harga_allin">
                                                            </div>
                                                            <div class="form-group  floating-label">
                                                                <label>Grand Total</label>
                                                                <input readonly type="text" placeholder=" "
                                                                    class="form-control num tgt" id="mk-tgt"
                                                                    name="mk-tgt">
                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>


                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_data_konsumen-tab')"
                                                class="btn btn-secondary mr-1" href="javascript:void(0)"><i
                                                    class="fa fa-arrow-left" aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('#idk_tagihan-tab')">
                                                Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_tagihan" aria-labelledby="idk_tagihan-tab"
                                    role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-4 col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-12 col-lg-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5 class="card-title">Buat Tagihan</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Total Yang
                                                                    Harus Dibayar</div>
                                                            </div>
                                                            <div class="form-group">
                                                                <input readonly type="text" class="form-control num tgt"
                                                                    id="mk-total_tot" name="mk-total_tot">
                                                            </div>
                                                            <div class="form-group" hidden>
                                                                <label for="mk-total_um">Total Uang Muka</label>
                                                                <input readonly type="text" class="form-control num tum"
                                                                    id="mk-total_um" name="mk-total_um">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="total_cicilan">Total Cicilan UM</label>
                                                                <input readonly type="hidden" class="form-control num"
                                                                    id="mk-total_cicilan_um" name="total_cicilan_um">
                                                            </div>
                                                            <input name="id_list_keu" id="id_list_keu"
                                                                class="form-control" type="hidden">
                                                            <input name="id_keuangan" id="id_keuangan"
                                                                class="form-control" type="hidden">
                                                            <div class="divider divider-left">
                                                                <div class="divider-text font-weight-bold">Buat Tagihan
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="form-group floating-label floating-label-select">
                                                                <select class="form-control" required
                                                                    name="berita_acara" id="berita_acara">
                                                                    <option value="Angsuran">Angsuran</option>
                                                                    <option value="Uang Muka">Uang Muka</option>
                                                                    <option value="Biaya Administrasi">Biaya
                                                                        Administrasi</option>
                                                                    <option value="Turun KPR">Turun KPR</option>
                                                                    <option value="Biaya Kavling Strategis">Biaya
                                                                        Kavling Strategis
                                                                    </option>
                                                                    <option value="Biaya Kelebihan Tanah">Biaya
                                                                        Kelebihan Tanah
                                                                    </option>
                                                                    <option value="PPN">PPN</option>
                                                                    <option value="BPHTB">BPHTB</option>
                                                                    <option value="Biaya Proses">Biaya Proses</option>
                                                                </select>
                                                                <label>Untuk Tagihan</label>
                                                                <!-- <input required name="berita_acara" id="berita_acara"
                                                        class="form-control" type="text"> -->
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nominal</label>
                                                                <input name="nominal" id="nominal"
                                                                    onchange="sum_tg(this.value)"
                                                                    class="form-control num tg" type="text">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Tanggal Jatuh Tempo</label>
                                                                <input required name="jatuh_tempo_tgl"
                                                                    id="jatuh_tempo_tgl"
                                                                    class="form-control flatpickr-human-friendly"
                                                                    type="date">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div id="cicilan_belong_here"></div>
                                                            <button id="tambah_list" type="button"
                                                                class="btn btn-outline-primary btn-block waves-effect"
                                                                onclick="tambah_()">+ Tagihan Angsuran</button>
                                                            <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-sm-12 col-md-6 col-lg-6 " hidden>
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <h5 class="card-title">Tagihan Biaya-biaya</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <label for="mk-total_bb">Total Biaya-biaya</label>
                                                                <input readonly type="text" class="form-control num tbb"
                                                                    id="mk-total_bb" name="mk-total_bb">
                                                            </div>
                                                            <!-- <div class="form-group">
                                        <label for="total_cicilan">Total Cicilan UM</label> -->
                                                            <input readonly type="hidden" class="form-control num"
                                                                id="total_cicilan_bb" name="total_cicilan_bb">
                                                            <!-- </div> -->
                                                            <input name="id_list_keu_bb" id="id_list_keu_bb"
                                                                class="form-control" type="hidden">
                                                            <input name="id_keuangan_bb" id="id_keuangan_bb"
                                                                class="form-control" type="hidden">
                                                            <div class="form-group">
                                                                <label>Untuk Tagihan</label>
                                                                <select class="form-control" required
                                                                    name="berita_acara_bb" id="berita_acara_bb">
                                                                    <option value="PPN">PPN</option>
                                                                    <option value="BPHTB">BPHTB</option>
                                                                    <option value="Biaya Proses">Biaya Proses</option>
                                                                </select>

                                                                <!-- <input required name="berita_acara_bb" id="berita_acara_bb"
                                                        class="form-control" type="text"> -->
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Nominal</label>
                                                                <input name="nominal_bb" id="nominal_bb"
                                                                    onchange="sum_tg(this.value, '_bb')"
                                                                    class="form-control num tg" type="text">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Tanggal Jatuh Tempo</label>
                                                                <input required name="jatuh_tempo_tgl_bb"
                                                                    id="jatuh_tempo_tgl_bb"
                                                                    class="form-control flatpickr-human-friendly"
                                                                    type="date">
                                                                <span class="help-block"></span>
                                                            </div>
                                                            <button id="tambah_list_bb" type="button"
                                                                class="btn btn-outline-primary btn-block waves-effect"
                                                                onclick="tambah_('_bb')">+ Tagihan Biaya-biaya</button>
                                                            <!-- <button id="hapus_list" type="button" class="btn btn-outline-danger btn-block waves-effect" onclick="hapus()">+ Hapus List</button> -->
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-8 col-lg-8">

                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title">List Tagihan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table id="list_kendaraan" class="table">
                                                            <thead class="thead-dark">
                                                                <tr>
                                                                    <th>Keterangan</th>
                                                                    <th>Jatuh Tempo</th>
                                                                    <th>Nominal</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="list_cicilan_here">
                                                                <tr>
                                                                    <td colspan="5" class="text-center">Tidak Ada Data
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <!-- <button class="btn btn-sm btn-primary" onclick="addRow()">Tambah Baris</button> -->

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_biaya-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_arsip" aria-labelledby="idk_arsip-tab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>File SPPTB Yang Sudah Ditandatangani</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                accept="application/pdf" name="file_spptb"
                                                                id="idk_file_spptb" onchange="" />
                                                            <label class="custom-file-label" id="label-idk_file_spptb"
                                                                for="idk_file_spptb">Upload SPPTB yang sudah
                                                                ditandatangani</label>
                                                        </div>
                                                        <div id="list-idk_file_spptb">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Lampiran Surat Kuasa SPPTB</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                accept="application/pdf" name="file_surat_kuasa"
                                                                id="idk_file_surat_kuasa" onchange="" />
                                                            <label class="custom-file-label"
                                                                id="label-idk_file_surat_kuasa"
                                                                for="idk_file_surat_kuasa">Upload Lampiran Surat Kuasa
                                                                SPPTB</label>
                                                        </div>
                                                        <div id="list-idk_lampiran">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-head">
                                                    <ul class="nav nav-tabs mb-1 mt-1" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" id="spptb_ttd-tab"
                                                                data-toggle="tab" href="#spptb_ttd"
                                                                aria-controls="spptb_ttd" role="tab"
                                                                aria-selected="true">SPPTB Sudah Ditandatangan</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" id="spptb_riwayat-tab" data-toggle="tab"
                                                                href="#spptb_riwayat" aria-controls="spptb_riwayat"
                                                                role="tab" aria-selected="true">Riwayat Upload SPPTB</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="card-body">
                                                    <div class="tab-content">
                                                        <div class="tab-pane show active" id="spptb_ttd"
                                                            aria-labelledby="spptb_ttd-tab" role="tabpanel">
                                                            <div id="spptb_ttd_file"></div>
                                                        </div>
                                                        <div class="tab-pane" id="spptb_riwayat"
                                                            aria-labelledby="spptb_riwayat-tab" role="tabpanel">
                                                            <div class="table-responsive">
                                                                <table class="table mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>No</th>
                                                                            <th>File</th>
                                                                            <th>Oleh</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="spptb_ttd_file-here"></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_tagihan-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="idk_riwayat" aria-labelledby="idk_riwayat-tab"
                                    role="tabpanel">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">

                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <button class="btn btn-outline-primary" id="btn-ganti_nama"
                                                        onclick="ganti_nama()">Klik Untuk Ganti Nama Konsumen</button>
                                                    <button class="btn btn-outline-warning" id="btn-refresh-ganti_nama"
                                                        onclick="getRiwayatGantinama()">Muat Ulang Diwayat</button>
                                                    <div class="divider">
                                                        <div class="divider-text">Riwayat Ganti Nama </div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>File</th>
                                                                    <th>Oleh</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="riwayat_ganti_nama-here"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="card file-container">
                                                <div class="card-body">
                                                    <button class="btn btn-outline-primary" id="btn-ganti_kavling"
                                                        onclick="ganti_kavling()">Klik Untuk Ganti Kavling</button>
                                                    <button class="btn btn-outline-warning"
                                                        id="btn-refresh-ganti_kavling"
                                                        onclick="getRiwayatGantiKavling()">Muat Ulang Data </button>
                                                    <div class="divider">
                                                        <div class="divider-text">Riwayat Ganti Nama </div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>File</th>
                                                                    <th>Oleh</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="ganti_kavling-here"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="sticky-button-wrapper">
                                        <div>
                                            <button type="reset" class="btn btn-outline-danger mr-1"
                                                data-dismiss="modal">Tutup</button>

                                            <a onclick="btnNext('#idk_tagihan-tab')" class="btn btn-secondary mr-1"
                                                href="javascript:void(0)"><i class="fa fa-arrow-left"
                                                    aria-hidden="true"></i> Sebelumnya</a>
                                            <a class="btn btn-success data-submit mr-1" href="javascript:void(0)"
                                                onclick="btnNext('save')">
                                                Simpan <i class="fa fa-save" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <br>
                </div>
                <!-- <div class="modal-footer">
                    <button type="reset" class="btn btn-outline-danger" data-dismiss="modal">Tutup</button>

                    <a id="prev-form-btn-idk_keu" onclick="btnPrev()" disabled="disabled" class="btn btn-secondary mr-1"
                        href="javascript:void(0)"><i class="fa fa-arrow-left" aria-hidden="true"></i> Sebelumnya</a>
                    <a id="add-form-btn-idk_keu" class="btn btn-primary data-submit mr-1" href="javascript:void(0)"
                        onclick="btnNext('#add-form-btn-idk_keu')">
                        Simpan <i class="fa fa-arrow-right" aria-hidden="true"></i></a>

                </div> -->
        </form>
    </div>
</div>
</section>

<!-- ################################### modal mkdt turun pembangunan ##################################### -->
<div class="modal fade " id="modals-turun_pembangunan">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <form id="fm-turun_pembangunan" class="modal-content pt-0" enctype="multipart/form-data">
            <div class="modal-header">
                <div class="min-w-0">
                    <h5 class="modal-title mb-0">Turun Pembangunan</h5>
                    <span class="tp-project"><i class="fas fa-home mr-1"></i>Proyek: <?= $data['proyek']->nama_proyek ?></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="turun-pembangunan-layout">
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text"><i class="fas fa-map-marker-alt mr-1"></i>Kavling Terpilih</div>
                                </div>
                                <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                                <input type="hidden" class="form-control" id="tp-nama_proyek" readonly name="nama_proyek"
                                    value="<?= $data['proyek']->nama_proyek ?>" />
                                <div class="form-group mb-0">
                                    <label class="form-label" for="tp-kavling">Kavling</label>
                                    <textarea class="form-control" id="tp-kavling" name="tp-kavling" rows="6" readonly
                                        placeholder="Kavling terpilih"></textarea>
                                    <small class="tp-note">
                                        Gunakan titik koma ";" untuk pemisah nomor rumah jika input lebih dari 1 kavling sekaligus.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text"><i class="fas fa-file-signature mr-1"></i>Dokumen Perintah Bangun</div>
                                </div>
                                <div class="tp-meta-grid">
                                    <div class="form-group">
                                        <label for="tp-perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                        <input type="text" readonly="readonly" id="tp-perintah_bangun_tgl"
                                            name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly"
                                            placeholder="-" />
                                    </div>
                                    <div class="form-group">
                                        <label for="tp-perintah_bangun_oleh">Oleh</label>
                                        <input type="text" readonly="readonly" id="tp-perintah_bangun_oleh"
                                            name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                                    </div>
                                </div>

                                <div class="form-group mt-1 mb-0">
                                    <label for="tp-perintah_bangun_file">File Perintah Bangun</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" accept="application/pdf"
                                            name="perintah_bangun_file" id="tp-perintah_bangun_file" />
                                        <label class="custom-file-label" id="label-perintah_bangun_file"
                                            for="tp-perintah_bangun_file">Upload File Perintah Bangun</label>
                                    </div>
                                    <div class="tp-file-action">
                                        <a href="#" target="_blank" id="list-tp-upload_perintah_bangun_file"
                                            class="btn btn-outline-primary">
                                            <i class="fas fa-external-link-alt mr-1"></i>Lihat file tersimpan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                <a id="set-tp-btn" class="btn btn-primary" onclick="set_tp()" href="javascript:void(0)">
                    <i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan
                </a>
            </div>
        </form>
    </div>
</div>
<!-- ################################### modal mkdt set harga ##################################### -->
<div class="modal fade" id="modals-set_harga">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <form id="fm-set_harga" class="add-new-record modal-content pt-0">
            <div class="modal-header">
                <div class="min-w-0">
                    <h5 class="modal-title mb-0">Manajemen Kavling &mdash; Set Harga</h5>
                    <span class="set-harga-project"><i class="fas fa-home mr-1"></i>Proyek: <?= $data['proyek']->nama_proyek ?></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body flex-grow-1">
                <div class="set-harga-layout">
                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text"><i class="fas fa-map-marker-alt mr-1"></i> Kavling Terpilih</div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="sh-kavling">Kavling</label>
                                    <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                                    <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                                    <input type="hidden" class="form-control" id="nama_proyek" readonly name="nama_proyek" value="<?= $data['proyek']->nama_proyek ?>" />
                                    <textarea class="form-control" id="sh-kavling" name="sh-kavling" rows="4" readonly placeholder="Kavling"></textarea>
                                    <small class="set-harga-note">Gunakan titik koma ";" untuk pemisah nomor rumah jika input lebih dari 1 kavling sekaligus.</small>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text"><i class="fas fa-list mr-1"></i> Pilih Pricelist</div>
                                </div>
                                <div class="form-group">
                                    <label for="sh-id">Pricelist</label>
                                    <select class="select2 custom-select sh-fm form-control" id="sh-id" name="sh-id" value=""></select>
                                </div>
                                <div class="form-group mb-0">
                                    <label>File Pricelist</label>
                                    <div>
                                        <a href="javascript:void(0)" target="_blank" id="sh-pricelist_file" rel="noopener noreferrer" class="btn btn-outline-primary btn-block">
                                            <i class="fas fa-file mr-1"></i> Klik untuk melihat file
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text"><i class="fas fa-money-bill-wave mr-1"></i> Detail Pricelist</div>
                                </div>
                                <div class="set-harga-detail-grid">
                                    <div class="form-group">
                                        <label for="sh-row">ROW</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-row" name="sh-row" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-tipe">Tipe</label>
                                        <input type="text" class="form-control sh-fm text-right" id="sh-tipe" name="sh-tipe" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-lb">Luas Bangunan</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-lb" name="sh-lb" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-lt">Luas Tanah</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-lt" name="sh-lt" value="" readonly />
                                    </div>
                                    <div class="form-group set-harga-highlight">
                                        <label for="sh-hargajual">Harga Jual</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-hargajual" name="sh-hargajual" value="" readonly />
                                    </div>
                                    <div class="form-group set-harga-highlight">
                                        <label for="sh-hargajual_net">Harga Jual Net</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-hargajual_net" name="sh-hargajual_net" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-kpr">KPR</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-kpr" name="sh-kpr" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-uang_muka">Uang Muka</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-uang_muka" name="sh-uang_muka" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-biaya_adm">Biaya Adm</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-biaya_adm" name="sh-biaya_adm" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-bphtb">BPHTB</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-bphtb" name="sh-bphtb" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-ppn">PPN</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-ppn" name="sh-ppn" value="" readonly />
                                    </div>
                                    <div class="form-group">
                                        <label for="sh-biaya_proses">Biaya Proses</label>
                                        <input type="text" class="form-control num sh-fm" id="sh-biaya_proses" name="sh-biaya_proses" value="" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancel</button>
                <a id="set-harga-form-btn" class="btn btn-primary" onclick="set_harga()" href="javascript:void(0)"><i class="fas fa-save mr-1"></i> Simpan Terpilih</a>
            </div>
        </form>
    </div>
</div>
<!--#################################### Modal Mkdt #########################################-->
<div class="modal fade" id="modal_divisi4">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-mkdt" enctype="multipart/form-data" class="add-new-record modal-content pt-0" autocomplete="off">

            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabelMkdt">Marketing Data &mdash; Perbaharui Status Kavling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body flex-grow-1">
                <div class="row mkdt-layout">
                    <div class="col-md-3 mkdt-sidebar">
                        <div class="card mkdt-hero-card">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat mb-0"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold">Ringkasan Konsumen</div>
                                </div>
                                <div class="info-row row no-gutters">
                                    <div class="col-5">
                                        <label class="info-label mb-0">No. SPPTB</label>
                                    </div>
                                    <div class="col-7">
                                        <span class="info-value" id="lb-st-no_spptb"></span>
                                    </div>
                                </div>
                                <div class="info-row row no-gutters">
                                    <div class="col-5">
                                        <label class="info-label mb-0">Konsumen</label>
                                    </div>
                                    <div class="col-7">
                                        <span class="info-value" id="lb-st-nama_konsumen"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mkdt-kpr-sidebar-card" id="sd-kpr">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-money-check-alt mr-1"></i> Kredit Pemilikan Rumah (KPR)</div>
                                </div>
                                <div class="mkdt-kpr-required-note">
                                    Jika nilai disetujui lebih kecil dari nilai pengajuan, Turun KPR wajib dibuat tagihan.
                                </div>
                                <div class="form-group">
                                    <label for="harga_kpr">Nilai Pengajuan</label>
                                    <input type="text" id="harga_kpr" name="harga_kpr" class="form-control num" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="acc_harga_kpr">Nilai Disetujui</label>
                                    <input type="text" id="acc_harga_kpr" name="acc_harga_kpr" class="form-control num" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="harga_turun_kpr">Turun KPR (Selisih)</label>
                                    <input type="text" id="harga_turun_kpr" name="harga_turun_kpr" class="form-control num" placeholder="-" readonly />
                                </div>
                                <div class="form-group">
                                    <button type="button" id="btn-add-tagihan-turunkpr" class="btn btn-primary w-100"><i class="fas fa-plus mr-1"></i> Buat Tagihan untuk Turun KPR</button>
                                </div>
                                <div id="mkdt-tagihan_kpr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9 mkdt-main-content">
                    <input type="hidden" class="form-control id_kavling" name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="id_mkdt" name="id_mkdt" value="" />
                    <input type="hidden" class="form-control" id="id_konsumen" name="id_konsumen" value="" />
                    <input type="hidden" class="form-control" id="mkdt_data_baru" name="mkdt_data_baru" value="" />

                    <ul class="nav nav-tabs mkdt-main-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="mkdt-tab-form-link" data-toggle="tab" href="#mkdt-tab-form" role="tab" aria-controls="mkdt-tab-form" aria-selected="true">
                                <i class="fas fa-edit"></i> Form
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="mkdt-tab-history-link" data-toggle="tab" href="#mkdt-tab-history" role="tab" aria-controls="mkdt-tab-history" aria-selected="false">
                                <i class="fas fa-history"></i> History
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mkdt-main-tab-content">
                    <div class="tab-pane fade show active" id="mkdt-tab-form" role="tabpanel" aria-labelledby="mkdt-tab-form-link">

                    <div class="card mkdt-nav-sticky">
                        <div class="card-body pb-0 pt-0">
                            <ul class="nav nav-tabs mb-1 mt-1" id="sidebar-tabs-alur" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link mkdt-scroll-nav active" id="tab-sd-status" href="#sd-status">
                                        <i class="fas fa-flag"></i> Status Kavling
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mkdt-scroll-nav" id="tab-sd-wawancara" href="#sd-wawancara">
                                        <i class="fas fa-comments"></i> Wawancara
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mkdt-scroll-nav" id="tab-sd-pb" href="#sd-pb">
                                        <i class="fas fa-hammer"></i> Perintah Bangun
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mkdt-scroll-nav" id="tab-sd-sp3k" href="#sd-sp3k">
                                        <i class="fas fa-file-contract"></i> SP3K
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link mkdt-scroll-nav" id="tab-sd-akad" href="#sd-akad">
                                        <i class="fas fa-handshake"></i> Akad
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mkdt-scroll-content" id="mkdt-main-scroll-area">
                    <!-- STATUS KAVLING BLOCK -->
                    <div id="sd-status" class="scroll-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-flag mr-1"></i> Status Booking Kavling</div>
                                </div>
                                <div class="form-group">
                                    <label for="status_mkdt">Status Booking</label>
                                    <select required class="form-control" id="status_mkdt" name="status_mkdt">
                                        <option value="">-</option>
                                        <option value="Booking">Booking</option>
                                        <option value="Akad">Akad</option>
                                        <option disabled value="Batal">Batal</option>
                                    </select>
                                </div>
                                <div id="show_keterangan_batal" class="hidden">
                                    <div class="form-group">
                                        <label for="keterangan_batal">Keterangan Batal</label>
                                        <textarea class="form-control" id="keterangan_batal" name="keterangan_batal" rows="3" placeholder="Keterangan"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="booking_tgl">Tanggal Booking</label>
                                    <input type="text" id="booking_tgl" name="booking_tgl" class="form-control flatpickr-human-friendly" placeholder="-" readonly />
                                </div>
                                <div class="form-group">
                                    <label for="booking_fee">Booking Fee</label>
                                    <input type="text" readonly class="form-control num" id="booking_fee" name="booking_fee">
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-clipboard-list mr-1"></i> Status Keterangan</div>
                                </div>
                                <div class="form-group">
                                    <label for="mkdt_keterangan">Keterangan Khusus</label>
                                    <input type="text" id="mkdt_keterangan" name="mkdt_keterangan" class="form-control" placeholder="ACC SP3K/REJECT/WAWANCARA/DLL" />
                                </div>
                                <div class="form-group">
                                    <label for="status_keterangan">Keterangan Status</label>
                                    <textarea class="form-control" id="status_keterangan" name="status_keterangan" rows="3" placeholder="Keterangan detail status kavling"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WAWANCARA BLOCK -->
                    <div id="sd-wawancara" class="scroll-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-comments mr-1"></i> Detail Wawancara</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="wawancara" name="wawancara" value="1" />
                                        <label class="custom-control-label" for="wawancara">Sudah Wawancara</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="id_bank">Bank</label>
                                    <select id="id_bank" name="id_bank" class="form-control select2">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="form-group hidden">
                                    <label for="bank">Keterangan Bank</label>
                                    <input type="text" id="bank" name="bank" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="wawancara_tgl">Tanggal Wawancara</label>
                                    <input type="text" id="wawancara_tgl" name="wawancara_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PERINTAH BANGUN BLOCK -->
                    <div id="sd-pb" class="scroll-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-hammer mr-1"></i> Instruksi Pembangunan</div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="perintah_bangun" name="perintah_bangun" value="1" />
                                        <label class="custom-control-label" for="perintah_bangun">Perintah Bangun</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="perintah_bangun_tgl">Tanggal Perintah Bangun</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_tgl" name="perintah_bangun_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="perintah_bangun_oleh">Oleh</label>
                                    <input type="text" readonly="readonly" id="perintah_bangun_oleh" name="perintah_bangun_oleh" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>File Perintah Bangun (PDF)</label>
                                    <div class="dropzone dropzone-lg custom-file mb-1" id="dz-perintah_bangun_file">
                                        <input type="file" class="custom-file-input dz-input" accept="application/pdf" name="perintah_bangun_file" id="perintah_bangun_file" />
                                        <div class="dz-inner">
                                            <div class="dz-preview" id="prev_perintah_bangun_file"></div>
                                            <div class="dz-placeholder">
                                                <div class="h5 mb-1">Tarik & letakkan PDF ke sini</div>
                                                <div class="text-muted">atau klik untuk pilih file Perintah Bangun</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" target="_blank" id="list-upload_perintah_bangun_file" class="btn btn-outline-primary btn-block"><i class="fas fa-file-pdf"></i> Lihat File</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SP3K BLOCK -->
                    <div id="sd-sp3k" class="scroll-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-file-contract mr-1"></i> Surat Penegasan Persetujuan Penyediaan Kredit</div>
                                </div>
                                <div class="form-group">
                                    <label for="sp3k_no">No SP3K</label>
                                    <input type="text" id="sp3k_no" name="sp3k_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label>File SP3K</label>
                                    <div class="dropzone dropzone-lg custom-file mb-1" id="dz-sp3k_file">
                                        <input type="file" class="custom-file-input dz-input" accept="application/pdf" name="sp3k_file" id="sp3k_file" />
                                        <div class="dz-inner">
                                            <div class="dz-preview" id="prev_sp3k_file"></div>
                                            <div class="dz-placeholder">
                                                <div class="h5 mb-1">Tarik & letakkan PDF ke sini</div>
                                                <div class="text-muted">atau klik untuk pilih file SP3K</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" target="_blank" id="list-upload_sp3k_file" class="btn btn-outline-primary btn-block"><i class="fas fa-file-pdf"></i> Lihat File</a>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="sp3k" name="sp3k" value="1" />
                                        <label class="custom-control-label" for="sp3k">Status Verifikasi SP3K</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="sp3k_tgl">Tanggal Terbit</label>
                                    <input type="text" id="sp3k_tgl" name="sp3k_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="sp3k_tgl_exp">Tanggal Kadaluarsa</label>
                                    <input type="text" id="sp3k_tgl_exp" name="sp3k_tgl_exp" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AKAD BLOCK -->
                    <div id="sd-akad" class="scroll-section">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-handshake mr-1"></i> Persiapan Akad</div>
                                </div>
                                <div class="form-group">
                                    <label for="rencana_akad_tgl">Tanggal Rencana Akad</label>
                                    <input type="text" id="rencana_akad_tgl" name="rencana_akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="notaris">Notaris</label>
                                    <input type="text" id="notaris" name="notaris" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="is_ajb">PPJB/AJB</label>
                                    <select class="form-control" id="is_ajb" name="is_ajb">
                                        <option value=""></option>
                                        <option value="AJB">AJB</option>
                                        <option value="PPJB">PPJB</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="akad" name="akad" value="1" />
                                        <label class="custom-control-label" for="akad">Status Akad Rampung</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="akad_indent" name="akad_indent" value="1" />
                                        <label class="custom-control-label" for="akad_indent">Akad Indent (Bangunan Belum Terbangun)</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="akad_tgl">Tanggal Akad</label>
                                    <input type="text" id="akad_tgl" name="akad_tgl" class="form-control flatpickr-human-friendly" placeholder="-" />
                                </div>
                                <div class="form-group">
                                    <label for="debitur_no">No Debitur</label>
                                    <input type="text" id="debitur_no" name="debitur_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group hidden">
                                    <label for="bast_no">No BAST</label>
                                    <input type="text" id="bast_no" name="bast_no" class="form-control" placeholder="-" />
                                </div>
                                <div class="form-group hidden">
                                    <label>BAST File</label>
                                    <div class="dropzone dropzone-lg custom-file mb-1" id="dz-bast_file">
                                        <input type="file" class="custom-file-input dz-input" accept="application/pdf" name="bast_file" id="bast_file" />
                                        <div class="dz-inner">
                                            <div class="dz-preview" id="prev_bast_file"></div>
                                            <div class="dz-placeholder">
                                                <div class="h5 mb-1">Tarik & letakkan PDF ke sini</div>
                                                <div class="text-muted">atau klik untuk pilih file BAST</div>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" target="_blank" id="list-upload_bast_file" class="btn btn-outline-primary btn-block"><i class="fas fa-file-pdf"></i> Lihat File BAST</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="sticky-button-wrapper">
                        <div class="d-flex flex-wrap justify-content-end" style="gap: .5rem;">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancel</button>
                            <button type="button" id="add-form-btn-mkdt" class="btn btn-primary" onclick="save_mkdt(this)"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                        </div>
                    </div>

                    </div>

                    <div class="tab-pane fade" id="mkdt-tab-history" role="tabpanel" aria-labelledby="mkdt-tab-history-link">
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text font-weight-bold"><i class="fas fa-history mr-1"></i> Riwayat Perubahan Kavling</div>
                                </div>
                                <div id="mkdt-history-timeline" class="mkdt-history-wrap">
                                    <div class="text-muted">Memuat history...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ################################### modal mkdt standing instruction ##################################### -->
<div class="modal fade text-left" id="modals-si" tabindex="-1" role="dialog" aria-labelledby="modals-si-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form id="fm-si" class="modal-content pt-0" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="modals-si-label">Standing Instruction</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="si-layout">
                    <aside class="si-sidebar">
                        <div class="card si-hero">
                            <div class="card-body bg-primary text-light">
                                <p class="modal-title label_alamat mb-0"></p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Standing Instruction Dipilih</div>
                                </div>
                                <div class="card si-meta-card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-file-signature mr-1"></i> Jenis SI</h6>
                                        <h5 id="si-selected-label">-</h5>
                                        <h6><i class="fas fa-calendar mr-1"></i> Tanggal SI</h6>
                                        <h5 id="si-selected-date">-</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <section class="si-content">
                        <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                        <input type="hidden" id="si-current-key" value="">

                        <div class="card">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Form Upload Standing Instruction</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="si-id_list_si">Pilih Standing Instruction</label>
                                            <select id="si-id_list_si" class="form-control"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="si-tanggal_si">Tanggal SI</label>
                                            <input type="text" class="form-control flatpickr-human-friendly" id="si-tanggal_si" placeholder="-">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="si-file">Soft File</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" accept="application/pdf" id="si-file">
                                                <label class="custom-file-label" id="si-file-label" for="si-file">Upload Soft File</label>
                                            </div>
                                        </div>
                                        <div class="si-file-action">
                                            <a href="#" target="_blank" id="si-current-file" class="btn btn-outline-primary hidden">
                                                <i class="fas fa-eye mr-1"></i> Lihat File Tersimpan
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="si-keterangan">Keterangan</label>
                                            <textarea class="form-control" id="si-keterangan" rows="4" placeholder="Keterangan"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="divider divider-left">
                                    <div class="divider-text">Daftar Standing Instruction Tersimpan</div>
                                </div>
                                <div class="table-responsive">
                                    <table id="si-table" class="table table-sm compact mb-0">
                                        <thead>
                                            <tr>
                                                <th width="24%">Jenis SI</th>
                                                <th width="16%">Tanggal SI</th>
                                                <th width="20%">Soft File</th>
                                                <th width="28%">Keterangan</th>
                                                <th width="12%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Data belum dimuat</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-si-simpan" type="button" class="btn btn-primary mr-1" onclick="save_si(); return false;">
                    Simpan
                </button>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/mkdt.js?v=<?= filemtime(FCPATH.'assets/js/siteplan/mkdt.js') ?>"></script>