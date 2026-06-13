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
        margin: 1rem -1rem -1rem;
        padding: .85rem 1rem;
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
                                    <select id="id_bank" name="id_bank" class="form-control select2"></select>
                                </div>
                                <div class="form-group">
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

<script>
    const MKDT_HISTORY_LIMIT = 10;

    function escapeMkdtHistoryValue(value) {
        return $("<div>").text(value === null || value === undefined ? "" : value).html();
    }

    function resetMkdtHistoryTimeline() {
        $("#mkdt-history-timeline")
            .data("id-kavling", "")
            .data("next-offset", 0)
            .data("history-limit", MKDT_HISTORY_LIMIT)
            .html('<div class="text-muted">Memuat history...</div>');
    }

    function renderMkdtHistoryTimeline(history, meta, append) {
        const $target = $("#mkdt-history-timeline");
        const nextOffset = meta ? (meta.history_next_offset || 0) : 0;
        const limit = meta ? (meta.history_limit || MKDT_HISTORY_LIMIT) : MKDT_HISTORY_LIMIT;
        const hasMore = !!(meta && meta.history_has_more);

        $target
            .data("next-offset", nextOffset)
            .data("history-limit", limit);

        if (!append) {
            $target.html('<div class="mkdt-history-list"></div><div class="mkdt-history-action mt-2"></div>');
        }

        const $list = $target.find(".mkdt-history-list");
        if (!history.length && !append) {
            $list.html('<div class="text-muted">Belum ada history perubahan.</div>');
        }

        $.each(history, function(index, item) {
            $list.append(
                '<div class="mkdt-history-item">' +
                    '<div class="d-flex justify-content-between align-items-start flex-wrap">' +
                        '<div class="mkdt-history-title">' + escapeMkdtHistoryValue(item.action_label || item.action) + '</div>' +
                        '<div class="mkdt-history-meta">' + format_datetime(item.created_at) + '</div>' +
                    '</div>' +
                    '<div class="mkdt-history-meta mb-1">Oleh: ' + escapeMkdtHistoryValue(item.username || '-') + '</div>' +
                    '<div class="mkdt-history-summary">' + escapeMkdtHistoryValue(item.summary || '-') + '</div>' +
                '</div>'
            );
        });

        const $action = $target.find(".mkdt-history-action");
        if (hasMore) {
            $action.html(
                '<button type="button" class="btn btn-outline-primary btn-sm mkdt-history-load-more">Muat lagi</button>'
            );
        } else {
            $action.empty();
        }
    }

    function loadMkdtHistory(idKavling, append) {
        if (!idKavling) {
            resetMkdtHistoryTimeline();
            return;
        }

        const $target = $("#mkdt-history-timeline");
        const offset = append ? ($target.data("next-offset") || 0) : 0;
        const limit = $target.data("history-limit") || MKDT_HISTORY_LIMIT;

        if (!append) {
            $target.data("id-kavling", idKavling).data("next-offset", 0);
            $target.html('<div class="text-muted">Memuat history...</div>');
        } else {
            $target.find(".mkdt-history-action .btn").prop("disabled", true).html('Memuat <i class="fa fa-spinner fa-spin"></i>');
        }

        $.ajax({
            url: base_url + "api/mkdt/history",
            type: "post",
            data: {
                [csrfName]: csrfHash,
                id_kavling: idKavling,
                history_limit: limit,
                history_offset: offset,
            },
            dataType: "json",
            success: function(res) {
                csrfHash = res.token;
                renderMkdtHistoryTimeline(res.history || [], res, append);
            },
            error: function() {
                if (!append) {
                    $target.html('<div class="text-danger">Gagal memuat history.</div>');
                } else {
                    $target.find(".mkdt-history-action .btn").prop("disabled", false).text("Muat lagi");
                }
            },
        });
    }

    $(document).off("click", ".mkdt-history-load-more").on("click", ".mkdt-history-load-more", function() {
        const idKavling = $("#mkdt-history-timeline").data("id-kavling");
        loadMkdtHistory(idKavling, true);
    });

    $(document).off("shown.bs.tab", "#mkdt-tab-history-link").on("shown.bs.tab", "#mkdt-tab-history-link", function() {
        const idKavling = $(".id_kavling").val();
        loadMkdtHistory(idKavling, false);
    });

    document.addEventListener("DOMContentLoaded", function() {
        const mkdtModal = document.getElementById("modal_divisi4");
        if (!mkdtModal) return;

        const scrollArea = document.getElementById("mkdt-main-scroll-area");
        const sections = Array.from(mkdtModal.querySelectorAll(".scroll-section"));
        const navItems = mkdtModal.querySelectorAll(".mkdt-scroll-nav");

        if (!scrollArea || sections.length === 0) return;

        navItems.forEach(item => {
            item.addEventListener("click", function(e) {
                e.preventDefault();
                const targetEl = document.getElementById(this.getAttribute("href").substring(1));
                if (!targetEl) return;
                scrollArea.scrollTo({
                    top: targetEl.offsetTop - scrollArea.offsetTop,
                    behavior: "smooth"
                });
            });
        });

        scrollArea.addEventListener("scroll", function() {
            let current = "";
            const currentPosition = scrollArea.scrollTop;

            sections.forEach(section => {
                const sectionTop = section.offsetTop - scrollArea.offsetTop - 50;
                if (currentPosition >= sectionTop) {
                    current = section.getAttribute("id");
                }
            });

            navItems.forEach(item => {
                item.classList.toggle("active", item.getAttribute("href") === "#" + current);
            });
        });

        $("#modal_divisi4").on("hidden.bs.modal", function() {
            $("#mkdt-tab-form-link").tab("show");
            resetMkdtHistoryTimeline();
        });
    });
</script>

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

<script>
    $("#id_bank").select2({
  placeholder: "Pilih Bank",
  allowClear: true,
  ajax: {
    url: base_url + "api/bank",
    dataType: "json",
    delay: 250,
    method: "get",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
      };
    },
    processResults: function (r) {
      // csrfHash = r.token;

      let results = [];
      $.each(r, function (i, v) {
        results.push({
          id: v.id,
          text: `${v.bank}${v.keterangan ? ": (" + v.keterangan + ")" : ""}`,
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});

// $('a[data-toggle="tab"]').on("show.bs.tab", function (e) {
//   const targetId = $(e.target).attr("href"); // ex: #profile
//   if (
//     targetId === "#idk_data_konsumen" ||
//     targetId === "#idk_biaya" ||
//     targetId === "#idk_tagihan"
//   ) {
//     if (!state.status.tab.isClosed) {
//       let isValid = isValidKonsumen(getActiveIndex());

//       if (isValid != undefined && !isValid[getActiveIndex()]) {
//         e.preventDefault(); // mencegah tab berpindah
//         return;
//       }
//     }
//   }
// });

// tab button

const containerIsiKonsumen = $("#tab-isi-konsumen");
let latestIsiDataKonsumenRequestId = 0;

// Array urutan tab
// const tabOrder = ["#idk_data_konsumen", "#idk_biaya", "#idk_tagihan"];

// Ambil index tab aktif
// function getActiveIndex() {
//   const activeId = containerIsiKonsumen.find(".tab-pane.active").attr("id");
//   return tabOrder.findIndex((sel) => sel === "#" + activeId);
// }

// Pindah ke tab ke-i
// function goTo(i) {
//   if (i < 0 || i >= tabOrder.length) return;
//   containerIsiKonsumen.find('a[href="' + tabOrder[i] + '"]').tab("show");
// }

// Update tombol
// function updateButtons(next, prev) {
//   const i = getActiveIndex();
//   const bPrev = $(prev);
//   const bNext = $(next);

//   bPrev.prop("disabled", i === 0);

//   if (i === tabOrder.length - 1) {
//     bNext
//       .html('Simpan <i class="fa fa-save" aria-hidden="true"></i>')
//       .data("action", "save")
//       .removeClass("btn-primary")
//       .addClass("btn-success");
//     return true;
//   } else {
//     bNext
//       .html('Selanjutnya <i class="fa fa-arrow-right" aria-hidden="true"></i>')
//       .data("action", "next")
//       .removeClass("btn-success")
//       .addClass("btn-primary");
//     return false;
//   }
// }

function isValidKonsumen(i) {
  let isValid = true;

  if (i == "#idk_biaya-tab" || i == "#idk_data_konsumen-tab") {
    $("#fm-idk_keu")
      .find("input.tab1[required], select.tab1[required]")
      .each(function () {
        let id = $(this).attr("id");
        let value = $(this).val().trim();

        if (value === "") {
          let labelText = $('label[for="' + id + '"]').text();
          isValid = false;
          showToast(labelText + " harus diisi", "warning");
          $(this).focus();
          this.reportValidity();
          return false; // Stop the $.each loop immediately if an invalid field is found
        }
      });
    return isValid;
  } else if (i == "#idk_tagihan-tab") {
    if ($("#idk-booking_tgl").val() == "") {
      showToast("Tanggal Booking harus diisi", "warning");
      $("#idk-booking_tgl").get(0)._flatpickr.open();
      isValid = false;
    } else if ($("#idk-booking_fee").val() == "") {
      showToast("Booking Fee harus diisi", "warning");
      $("#idk-booking_fee").focus();
      isValid = false;
    }

    return isValid;
  } else if (i == "save") {
    if (parseFloat(removeComma($("#mk-total_tot").val() || 0)) > 0) {
      if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
        showToast(
          "Total tagihan tida sesuai dengan total harus dibayar",
          "danger",
        );
        isValid = false;
      }
    }
    return isValid;
  }
}
// Klik NEXT/SIMPAN
function btnNext(next) {
  let isValid = isValidKonsumen(next);
  if (next === "save" && isValid) {
    Swal.fire({
      title: "Konfirmasi",
      text: "Apakah data sudah benar dan akan disimpan?",
      showDenyButton: true,
      confirmButtonText: "Simpan",
      denyButtonText: `Kembali`,
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        simpan_dt_konsumen_keuangan();
      }
    });
  } else {
    if (isValid) {
      $(next).tab("show");
    } else {
      return;
    }
  }
}
$("a.locked").on("click", function (e) {
  e.preventDefault();
  e.stopPropagation();
});

// ====== Helpers ======
const ui = {
  form: {
    kons: $("#fm-idk_keu"),
  },
  loading: $("#loading"),
  tabs: {
    konsumen: $("#idk_data_konsumen-tab"),
  },
  btn: {
    printSPPTB: $("#btn-print_spptb"),
    addKons: $("#add-form-btn-idk_keu"),
    prevKons: $("#prev-form-btn-idk_keu"),
    delKons: $("#delete-btn-idk_keu"),
  },
  fields: {
    id_kavling: $(".id_kavling"),
    id_mkdt: $("#idk-id_mkdt"),
    hargaAkhirSelect: $("#idk-harga_akhir"),
    rincian: $("#idk-rincian"),
    richText: () => $("#idk-rincian").prev(".richText-editor"),
    // file previews
    ktpHere: $("#idk-file_ktp-here"),
    npwpHere: $("#idk-file_npwp-here"),
    ddHere: $("#idk-file_data_diri-here"),
  },
};

function withLoading(fn) {
  ui.loading.removeClass("hidden");
  return Promise.resolve(fn()).finally(() => ui.loading.addClass("hidden"));
}

function disableForm(disabled) {
  ui.form.kons.find("input:text, select, textarea").prop("disabled", disabled);
}

function setVal(sel, val) {
  $(sel)
    .val(val ?? "")
    .triggerHandler("input");
}
function setDate(dateStr, sel) {
  if (
    dateStr &&
    dateStr !== "0000-00-00" &&
    document.querySelector(sel)?._flatpickr
  ) {
    document.querySelector(sel)._flatpickr.setDate(dateStr);
  }
}
function setRichText(html) {
  ui.fields.richText().trigger("setContent", html ?? "");
  ui.fields.rincian.html(html ?? "");
}

// function updateButtons() {
//   // ganti logika lamamu jika perlu
//   ui.btn.add.prop("disabled", false);
//   ui.btn.prev.prop("disabled", false);
// }

function formatDateSafe(d) {
  return d ? format_date(d) : "-";
}

// ====== Data layer ======
function getTransaksiDetail({ id_mkdt, id_kavling, id_hargajual }) {
  return $.ajax({
    url: base_url + "api/transaksi/ambilsatu",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, id_mkdt, id_kavling, id_hargajual },
  });
}

// ====== Binders ======
function bindKavlingContext(sh) {
  // navigasi/tab & tombol
  ui.tabs.konsumen.tab("show");
  // updateButtons(ui.btn.addKons, ui.btn.prevKons);
  // reset form
  ui.form.kons[0].reset();
  ui.form.kons.find(".num").prop("disabled", false);

  $("#mk-total_bb, #mk-total_um").val(0);
  $("#list_cicilan_here").empty();
  $("#mk-total_cicilan_um, #total_cicilan_bb").val(0).triggerHandler("input");
  $("#id_list_keu, #id_list_keu_bb").val("");
  $("#mk-diskon_harga_jual, #mk-diskon_uang_muka").val(0);
  $("#idk_data_baru").val(1);
  $("#idk-rincian").prev(".richText-editor").trigger("setContent", "");

  // set state dasar
  state.id_kavling = sh.data.id_kavling || sh.id.substr(3);
  state.id_mkdt = sh.data.id_mkdt || null;

  // isi hidden fields
  ui.fields.id_kavling.val(state.id_kavling);
  ui.fields.id_mkdt.val(state.id_mkdt);

  // tombol print
  if (state.id_mkdt == null) {
    ui.btn.printSPPTB
      .attr(
        "onclick",
        `return swal('error', 'Data konsumen harus disimpan terlebih dahulu');`,
      )
      .attr("target", "")
      .prop("href", "#");
  } else {
    ui.btn.printSPPTB
      .attr("onclick", "")
      .prop(
        "href",
        `${base_url}print/spptb?id_mkdt=${state.id_mkdt}&id_kavling=${state.id_kavling}&id_proyek=${dt_proyek.id_proyek}`,
      )
      .attr("target", "_blank");
  }
}

function fillPriceSection(h, dk) {
  if (!h?.hargajual) return;
  // masal: map kunci â†’ #mk-*
  const mkMap = [
    "hargajual",
    "hargajual_net",
    "kpr",
    "uang_muka",
    "biaya_adm",
    "bphtb",
    "ppn",
    "biaya_proses",
    "harga_penambahan",
    "harga_penambahan_tanah",
  ];
  // console.log(h, dk);
  setVal("#mk-diskon_uang_muka", h.diskon_uang_muka);
  mkMap.forEach((k) => setVal(`#mk-${k}`, h[k]));

  setDatePicker(h.tgl_harga, "#mk-tgl_harga");
  setVal("#idk-tgl_harga", formatDateSafe(h.tgl_harga));
  setVal("#idk-harga_kpr", h.kpr);

  setVal("#idk-mkdt_hargajual", h.hargajual);
  $("#idk-mkdt_hargajual_by").text(dk?.username_harga_akhir ?? "-");
  $("#idk-mkdt_hargajual_tgl").text(formatDateSafe(dk?.harga_akhir_tgl));
}

function fillDiskresi(dk) {
  if (dk?.username_diskresi) {
    $("#idk-diskresi_st").removeClass("hidden");
    setVal("#idk-diskresi_harga", dk.diskresi_harga);
    setVal("#idk-diskresi_memo", dk.diskresi_memo);
    $("#idk-diskresi_oleh").text(dk.username_diskresi);
    $("#idk-diskresi_tgl").text(formatDateSafe(dk.diskresi_at));
  } else {
    $("#idk-diskresi_st").addClass("hidden");
    setVal("#idk-diskresi_harga", "-");
    setVal("#idk-diskresi_memo", "-");
    $("#idk-diskresi_oleh").text("-");
    $("#idk-diskresi_tgl").text("-");
  }
}

function fillFiles(v) {
  const ktpUrl = v?.ktp_access_url || v?.ktp_lok;
  const npwpUrl = v?.npwp_access_url || v?.npwp_lok;
  const dataDiriUrl = v?.data_diri_access_url || v?.data_diri_lok;

  renderExistingUploadPreview("file_ktp", ktpUrl, "KTP");
  renderExistingUploadPreview("file_npwp", npwpUrl, "NPWP");
  renderExistingUploadPreview("file_data_diri", dataDiriUrl, "Data Diri", "pdf");

  setImgOrPlaceholder(ui.fields.ktpHere, ktpUrl, not_found);
  setImgOrPlaceholder(ui.fields.npwpHere, npwpUrl, not_found, "90%");
  ui.fields.ddHere
    .html(dataDiriUrl ? "Klik untuk melihat file" : "Tidak ada data")
    .prop("href", resolveFileHref(dataDiriUrl, not_found));
}

function renderExistingUploadPreview(inputId, src, label, type = "image") {
  const input = document.getElementById(inputId);
  if (!input) return;

  const dropzone = input.closest(".dropzone");
  if (!dropzone) return;

  const preview = dropzone.querySelector(".dz-preview");
  const placeholder = dropzone.querySelector(".dz-placeholder");
  if (!preview || !placeholder) return;

  if (!isNotEmpty(src)) {
    preview.innerHTML = "";
    preview.style.display = "none";
    placeholder.style.display = "block";
    return;
  }

  const href = resolveFileHref(src);
  preview.innerHTML =
    type === "pdf"
      ? `<div class="p-2 border rounded bg-light text-center">
          <i class="fa fa-file-pdf fa-3x text-danger"></i>
          <div class="text-truncate">${escapeHtml(label)} sudah diunggah</div>
        </div>`
      : `<img src="${escapeAttribute(href)}"
             class="preview-thumb"
             style="height:100%; object-fit:contain;"
             alt="${escapeAttribute(label)}">
        <div class="text-truncate mb-1">${escapeHtml(label)} sudah diunggah</div>`;
  preview.style.display = "block";
  placeholder.style.display = "none";
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function escapeAttribute(value) {
  return escapeHtml(value).replace(/`/g, "&#096;");
}

let mkdtClipboardBound = false;
let ktpOcrBound = false;
let tesseractLoadPromise = null;

function bindMkdtClipboardUpload() {
  if (mkdtClipboardBound) return;
  mkdtClipboardBound = true;

  document.addEventListener("paste", async function (e) {
    if (!$("#modal-isi_data_konsumen").hasClass("show")) return;

    const pastedFile = getImageFileFromClipboard(e);
    if (!pastedFile) return;

    e.preventDefault();
    const result = await Swal.fire({
      title: "Upload dari Clipboard",
      text: "Pilih tujuan file yang ditempel.",
      icon: "question",
      showDenyButton: true,
      showCancelButton: true,
      confirmButtonText: "KTP",
      denyButtonText: "NPWP",
      cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
      assignFileToInput("file_ktp", pastedFile, "ktp-clipboard");
      runKtpOcrFromInput(true);
    } else if (result.isDenied) {
      assignFileToInput("file_npwp", pastedFile, "npwp-clipboard");
    }
  });
}

function bindKtpOcr() {
  if (ktpOcrBound) return;
  ktpOcrBound = true;

  $("#btn-ocr-ktp").on("click", function () {
    runKtpOcrFromInput(false);
  });
}

function getImageFileFromClipboard(e) {
  const clipboard = e.originalEvent?.clipboardData || e.clipboardData;
  if (!clipboard?.items) return null;

  for (const item of clipboard.items) {
    if (item.kind === "file" && item.type.startsWith("image/")) {
      return item.getAsFile();
    }
  }

  return null;
}

function assignFileToInput(inputId, file, prefix) {
  const input = document.getElementById(inputId);
  if (!input || !file) return;

  const extension = getImageExtension(file.type);
  const uploadFile = new File(
    [file],
    file.name || `${prefix}-${Date.now()}.${extension}`,
    { type: file.type || "image/png" },
  );
  const dataTransfer = new DataTransfer();
  dataTransfer.items.add(uploadFile);
  input.files = dataTransfer.files;
  input.dispatchEvent(new Event("change", { bubbles: true }));
}

function getImageExtension(type) {
  const map = {
    "image/jpeg": "jpg",
    "image/png": "png",
    "image/webp": "webp",
  };
  return map[type] || "png";
}

async function runKtpOcrFromInput(fromClipboard = false) {
  const input = document.getElementById("file_ktp");
  const file = input?.files?.[0];

  if (!file) {
    return Swal.fire("OCR KTP", "Pilih atau paste gambar KTP terlebih dahulu.", "warning");
  }

  if (!file.type.startsWith("image/")) {
    return Swal.fire("OCR KTP", "OCR hanya bisa membaca file gambar.", "warning");
  }

  if (fromClipboard) {
    const confirmOcr = await Swal.fire({
      title: "Jalankan OCR KTP?",
      text: "Sistem akan membaca gambar KTP dan menyiapkan isian form.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, baca KTP",
      cancelButtonText: "Lewati",
    });
    if (!confirmOcr.isConfirmed) return;
  }

  try {
    Swal.fire({
      title: "Membaca KTP",
      html: "Menyiapkan OCR...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const Tesseract = await loadTesseract();
    const result = await Tesseract.recognize(file, "ind+eng", {
      logger: updateOcrProgress,
    });

    Swal.close();
    const parsed = parseKtpOcrText(result?.data?.text || "");
    if (!parsed.nik && !parsed.nama && !parsed.alamat) {
      return Swal.fire(
        "OCR KTP",
        "Teks KTP belum bisa dikenali dengan cukup jelas.",
        "warning",
      );
    }

    await confirmApplyKtpOcr(parsed);
  } catch (err) {
    Swal.close();
    console.error(err);
    Swal.fire("OCR KTP", "Gagal menjalankan OCR KTP.", "error");
  }
}

function loadTesseract() {
  if (window.Tesseract) return Promise.resolve(window.Tesseract);
  if (tesseractLoadPromise) return tesseractLoadPromise;

  tesseractLoadPromise = new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js";
    script.async = true;
    script.onload = () => resolve(window.Tesseract);
    script.onerror = () => reject(new Error("Gagal memuat Tesseract.js"));
    document.head.appendChild(script);
  });

  return tesseractLoadPromise;
}

function updateOcrProgress(message) {
  if (!message?.status) return;

  const progress = Number.isFinite(message.progress)
    ? ` ${Math.round(message.progress * 100)}%`
    : "";
  const container = document.getElementById("swal2-html-container");
  if (container) {
    container.textContent = `${message.status}${progress}`;
  }
}

function parseKtpOcrText(text) {
  const lines = String(text || "")
    .split(/\r?\n/)
    .map((line) => cleanOcrLine(line))
    .filter(Boolean);
  const normalized = lines.join("\n");

  return {
    nik: extractKtpNik(normalized),
    nama: extractKtpLine(lines, /^NAMA\b/i, [/TEMPAT/i, /LAHIR/i, /JENIS/i, /ALAMAT/i]),
    alamat: extractKtpAddress(lines),
  };
}

function cleanOcrLine(line) {
  return String(line || "")
    .replace(/[|]/g, "I")
    .replace(/\s+/g, " ")
    .replace(/^[^\w]+|[^\w]+$/g, "")
    .trim();
}

function extractKtpNik(text) {
  const byLabel = text.match(/(?:NIK|N1K)\s*[:=\-]?\s*([0-9OILSB\s.\-]{12,24})/i);
  const fallback = text.match(/(?:[0-9OILSB][\s.\-]?){16,}/i);
  const raw = byLabel?.[1] || fallback?.[0] || "";
  const nik = raw
    .replace(/[Oo]/g, "0")
    .replace(/[IiLl]/g, "1")
    .replace(/[Ss]/g, "5")
    .replace(/[Bb]/g, "8")
    .replace(/\D/g, "");
  return nik.length >= 16 ? nik.substring(0, 16) : "";
}

function extractKtpLine(lines, labelRegex, stopRegexes = []) {
  for (let i = 0; i < lines.length; i++) {
    if (!labelRegex.test(lines[i])) continue;

    let value = lines[i]
      .replace(labelRegex, "")
      .replace(/^[:=\-\s]+/, "")
      .trim();

    if (!value && lines[i + 1] && !matchesAny(lines[i + 1], stopRegexes)) {
      value = lines[i + 1].trim();
    }

    return cleanKtpValue(value);
  }

  return "";
}

function extractKtpAddress(lines) {
  const stopRegexes = [/AGAMA/i, /STATUS/i, /PEKERJAAN/i, /KEWARGANEGARAAN/i, /BERLAKU/i];
  for (let i = 0; i < lines.length; i++) {
    if (!/^ALAMAT\b/i.test(lines[i])) continue;

    const parts = [];
    const first = lines[i]
      .replace(/^ALAMAT\b/i, "")
      .replace(/^[:=\-\s]+/, "")
      .trim();
    if (first) parts.push(first);

    for (let j = i + 1; j < Math.min(lines.length, i + 6); j++) {
      if (matchesAny(lines[j], stopRegexes)) break;
      if (/^(RT|RW|KEL|DESA|KEC|KAB|KOTA)\b/i.test(lines[j]) || parts.length === 0) {
        parts.push(lines[j]);
      }
    }

    return cleanKtpValue(parts.join(", "));
  }

  return "";
}

function matchesAny(value, regexes) {
  return regexes.some((regex) => regex.test(value));
}

function cleanKtpValue(value) {
  return String(value || "")
    .replace(/\s*[:=]\s*/g, " ")
    .replace(/\s+/g, " ")
    .replace(/^\W+|\W+$/g, "")
    .trim();
}

async function confirmApplyKtpOcr(parsed) {
  const rows = [
    ["NIK", parsed.nik],
    ["Nama", parsed.nama],
    ["Alamat", parsed.alamat],
  ]
    .filter(([, value]) => isNotEmpty(value))
    .map(
      ([label, value]) =>
        `<tr><th class="text-left pr-2">${escapeHtml(label)}</th><td>${escapeHtml(value)}</td></tr>`,
    )
    .join("");

  const result = await Swal.fire({
    title: "Gunakan hasil OCR?",
    html: `<table class="table table-sm mb-0">${rows}</table>`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Isi Form",
    cancelButtonText: "Batal",
  });

  if (!result.isConfirmed) return;

  if (parsed.nik) setVal("#idk-nik_konsumen", parsed.nik);
  if (parsed.nama) setVal("#idk-nama_konsumen", parsed.nama);
  if (parsed.alamat) setVal("#idk-alamat_konsumen", parsed.alamat);
}

$("#idk-is_allin").change(function () {
  is_allin(this);
});

$("#modal-isi_data_konsumen")
  .off("input.mkdtTotal change.mkdtTotal", ".mk-fm, #idk-is_subsidi")
  .on("input.mkdtTotal change.mkdtTotal", ".mk-fm, #idk-is_subsidi", function () {
    if (typeof sum_mktotal === "function") {
      sum_mktotal();
    }
  });

function is_allin(e) {
  if (e.value == "0") {
    harga_Total = $("#mk-tgt").val();
    $("#mk-harga_allin").hide();
  } else {
    harga_Total = $("#mk-harga_allin").val();
    $("#mk-harga_allin").show();
  }
  sum_mktotal();
}

function setIdkPerluRefund(value) {
  const normalized = String(value ?? "0") === "1" ? "1" : "0";
  $(`#modal-isi_data_konsumen input[name="dt-perlu_refund"][value="${normalized}"]`).prop(
    "checked",
    true,
  );
}

function updateIdkBatalSection() {
  const isBatal = $("#idk-status_mkdt").val() === "Batal";
  $("#idk-show_keterangan_batal").toggleClass("hidden", !isBatal);
  if (!isBatal) {
    $("#idk-keterangan_batal").val("");
    setIdkPerluRefund(0);
  }
}

function fillMkdt(v) {
  if (!v) return;

  if (v.status_mkdt === "Batal") {
    disableForm(true);
    $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").removeClass("hidden");
    ui.form.kons.find("#idk-id_konsumen, #idk-id_keuangan0").val("");
    ui.btn.delKons.removeClass("hidden");
  }

  // console.log(v)

  if (v.id_konsumen) $("#idk_data_baru").val(0);

  // basic fields
  setVal("#idk-is_allin", v.is_allin);
  $("#idk-is_allin").change();

  setVal("#mk-harga_allin", v.harga_allin);

  setVal("#idk-status_mkdt", v.status_mkdt);
  setVal("#idk-keterangan_batal", v.keterangan_batal);
  setIdkPerluRefund(v.perlu_refund);
  setDate(v.booking_tgl, "#idk-booking_tgl");
  setVal("#idk-booking_fee", v.booking_fee);

  ui.form.kons.find("#idk-id_konsumen").val(v.id_konsumen ?? "");

  setVal("#st-mkdt-no_spptb", v.no_spptb);
  setVal("#idk-nama_konsumen", v.nama_konsumen);
  setVal("#idk-nik_konsumen", v.nik_konsumen);
  setVal("#idk-alamat_konsumen", v.alamat_konsumen);
  setVal("#idk-npwp_konsumen", v.npwp_konsumen);
  setVal("#idk-hp_konsumen", v.hp_konsumen);
  setVal("#idk-email_konsumen", v.email_konsumen);
  setVal("#idk-status_konsumen", v.status_konsumen);

  setVal("#idk-nama_instansi", v.nama_instansi);
  setVal("#idk-alamat_instansi", v.alamat_instansi);
  setVal("#idk-tel_instansi", v.tel_instansi);
  setVal("#idk-email_instansi", v.email_instansi);
  setVal("#idk-alamat_surat", v.alamat_surat);
  setVal("#idk-pekerjaan", v.pekerjaan);
  setVal("#idk-lama_bekerja", v.lama_bekerja);
  setVal("#idk-bidang_pekerjaan", v.bidang_pekerjaan);

  setVal("#idk-status_pernikahan", v.status_pernikahan);
  setVal("#idk-nama_pasangan", v.nama_pasangan);
  setVal("#idk-nik_pasangan", v.nik_pasangan);
  setVal("#idk-hp_pasangan", v.hp_pasangan);
  setVal("#idk-status_pekerjaan_pasangan", v.status_pekerjaan_pasangan);
  setVal("#idk-instansi_pasangan", v.instansi_pasangan);

  setVal("#idk-sales", v.sales);

  setVal("#idk-is_kpr", v.is_kpr);
  setVal("#idk-is_subsidi", v.is_subsidi);
  setVal("#idk-jenis_subsidi", v.jenis_subsidi);

  setRichText(v.rincian);

  // if (v.keuangan_saved_by) {
  setVal("#mk-hargajual", v.harga_jual);
  setVal("#mk-hargajual_net", v.harga_jual_net);
  setVal("#mk-kpr", v.harga_kpr);
  setVal("#mk-uang_muka", v.harga_uang_muka);
  setVal("#mk-biaya_adm", v.harga_administrasi);
  setVal("#mk-bphtb", v.harga_bphtb);
  setVal("#mk-ppn", v.harga_ppn);
  setVal("#mk-biaya_proses", v.harga_biaya_proses);
  setVal("#mk-harga_sbum", v.harga_sbum);
  setVal("#mk-harga_penambahan", v.harga_penambahan);
  setVal("#mk-harga_penambahan_tanah", v.harga_penambahan_tanah);
  setVal("#mk-diskon_uang_muka", v.harga_diskon_uang_muka);
  // }

  setVal("#idk-promo", v.promo);

  // KPR turun
  // setVal("#mk-harga_kpr_acc", v.harga_kpr_acc);
  const turun_kpr = v.harga_kpr_acc ? v.harga_kpr - v.harga_kpr_acc : 0;
  // setVal("#mk-harga_penambahan_um", turun_kpr);

  // SPPTB file
  const spptbLink = v.file_spptb
    ? `<a href="${
        file_url('mkdt_file_spptb', v.id_mkdt)
      }" target=_blank class="btn btn-outline-primary">Klik untuk melihat File SPPTB Yang Sudah ditandatangan</a>`
    : `Tidak ada data`;
  $("#spptb_ttd_file").html(spptbLink);
}

$("#idk-status_mkdt").change(updateIdkBatalSection);

function fillSpptbList(list) {
  const html =
    list && list.length
      ? list
          .map(
            (val, i) => `
      <tr>
        <td>${i + 1}</td>
        <td><a href="${val.access_url || file_url('file_spptb', val.id)}" target=_blank>Klik untuk melihat file</a></td>
        <td>${val.username}<br>${format_datetime(val.created_at)}</td>
      </tr>`,
          )
          .join("")
      : '<tr><td colspan="3">Tidak ada data</td></tr>';
  $("#spptb_ttd_file-here").html(html);
}

function fillTagihan(tg) {
  state.data_um = {};
  state.data_bb = {};
  if (tg.length == 0) return;

  let a = it; // mengikuti variabel lamamu
  tg.forEach((v) => {
    const id = "lk" + a;
    // if (v.status === "UM") {
    state.data_um[id] = {
      id_list_keu: id,
      id_keuangan: v.id_keuangan,
      berita_acara: v.berita_acara,
      nominal: num_format(v.nominal),
      jatuh_tempo_tgl: v.jatuh_tempo_tgl,
    };
    // } else if (v.status === "BB") {
    //   state.data_bb[id] = {
    //     id_list_keu_bb: id,
    //     id_keuangan_bb: v.id_keuangan,
    //     berita_acara_bb: v.berita_acara,
    //     nominal_bb: num_format(v.nominal),
    //     jatuh_tempo_tgl_bb: v.jatuh_tempo_tgl,
    //   };
    // }
    a++;
  });

  // data_um = state.data_um
  // data_bb = state.data_bb
  // render list tagihan sekali saja
  tambah_ketagihan();
  it = a;
}

async function isi_data_konsumen() {
  const requestId = ++latestIsiDataKonsumenRequestId;
  mkdtUpload();
  // VALIDASI PILIHAN
  if (!editdtt?.[0]) return swal("error", "Tidak ada kavling yang dipilih");
  const sh = editdtt[0];
  if (sh.data.tipe !== "kavling")
    return swal("error", "Tidak ada kavling terpilih");
  if (sh.data2.harga_akhir === "-")
    return swal("error", "Kavling belum dipasarkan (tidak ada harga jual)");

  disableForm(false);
  ui.btn.delKons.addClass("hidden");
  $("#idk-show_keterangan_batal, .refresh_fmmkdt_div").addClass("hidden");
  setIdkPerluRefund(0);

  ui.form.kons.find("#idk-id_konsumen").val("");
  // Siapkan konteks UI & state
  bindKavlingContext(sh);
  state.id_hargajual = sh.data2.id_hargajual;
  setVal("#idk-harga_akhir", state.id_hargajual);

  $("#idk-is_allin").change();

  try {
    await withLoading(async () => {
      const res = await getTransaksiDetail({
        id_mkdt: sh.data.id_mkdt,
        id_kavling: state.id_kavling,
        id_hargajual: state.id_hargajual,
      });
      if (requestId !== latestIsiDataKonsumenRequestId) return;

      // CSRF update
      csrfHash = res.token;

      const v = res.data; // mkdt
      const h = res.hj; // pricelist
      const tg = res.tagihan;
      const dk = res.diskresi;

      state.mkdt = {
        harga_jual: res.hj,
        diskresi: res.diskresi,
      };

      // Diskresi & HJ
      fillDiskresi(dk);
      fillPriceSection(h, dk);

      // File preview
      fillFiles(v);

      // MKDT fields
      fillMkdt(v);

      // SPPTB list
      fillSpptbList(res.list_spptb || []);

      // Tagihan + render
      fillTagihan(tg);

      // Hitung total & label alamat sekali saja
      sum_mktotal();

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      // Buka modal
      $("#modal-isi_data_konsumen").modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal-isi_data_konsumen");
      state.status.tab.isClosed = false;
    });
  } catch (e) {
    console.log(e);
    // Error path konsisten
    return swal("error", e?.statusText || e?.message || "Terjadi kesalahan");
  }
}
function appendCollectionToFormData(fd, collection) {
  if (!collection || typeof collection !== "object") return;
  let i = 0;

  // Izinkan collection berupa Array atau Object keyed
  const items = Array.isArray(collection)
    ? collection
    : Object.values(collection);

  for (const item of items) {
    if (!item || typeof item !== "object") {
      i++;
      continue;
    }
    for (const [key, val] of Object.entries(item)) {
      // Nullish -> string kosong supaya backend nggak terima "undefined"
      fd.append(`${key}[${i}]`, val ?? "");
    }
    i++;
  }
}

function renderNikUsageWarning(rows) {
  const items = (rows || [])
    .map((item) => {
      const kavling = [item.nama_jalan, item.no_kavling ? `No. ${item.no_kavling}` : ""]
        .filter(Boolean)
        .join(" ");
      const lokasi = [
        item.nama_proyek || "Proyek tidak diketahui",
        item.nama_cluster || "",
        kavling || "",
      ]
        .filter(Boolean)
        .join(" - ");

      return `<li class="mb-1">
        <strong>${escapeHtml(item.nama_konsumen || "-")}</strong><br>
        <small>${escapeHtml(lokasi)}</small>
      </li>`;
    })
    .join("");

  return `
    <div class="text-left">
      <p class="mb-1">NIK tersebut sudah digunakan pada kavling/blok/proyek lain:</p>
      <ul class="pl-2 mb-1">${items}</ul>
      <p class="mb-0">Tetap lanjutkan dan simpan konsumen ini?</p>
    </div>
  `;
}

function simpan_dt_konsumen_keuangan(allowDuplicateNik = false) {
  const btnSave = "#add-form-btn-idk_keu";
  // updateButtons(btnSave, "#prev-form-btn-idk_keu");

  if (parseFloat(removeComma($("#mk-total_cicilan_um").val() || 0)) > 0) {
    if ($("#mk-total_tot").val() != $("#mk-total_cicilan_um").val()) {
      return swal(
        "error",
        "Gagal Menyimpan Data",
        "Total tagihan dan total yang harus dibayar tidak sesuai",
      );
    }
  }

  let dt = {};
  dt[csrfName] = csrfHash;
  ui.form.kons.find(":input").each(function () {
    dt[this.name] = this.value;
  });

  let i = 0;
  //cicilan um

  let form = ui.form.kons[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);
  if (allowDuplicateNik) {
    fd.append("allow_duplicate_nik", "1");
  }
  let is_ganti_nama = false;

  if (is_ganti_nama) {
    fd.append("id_mkdt_old", id_mkdt_old);
    fd.append("id_konsumen_old", id_konsumen_old);
    fd.append("is_ganti_nama", is_ganti_nama);
  }

  appendCollectionToFormData(fd, state.data_um);

  $.ajax({
    url: base_url + "api/transaksi/simpan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btnSave, true);
    },
    success: function (r) {
      csrfHash = r.token;
      if (r.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          removeModalListener("#modal-isi_data_konsumen");
          $(".modal").modal("hide");
          simpanBtn(btnSave, false);

          load_kavling();
          hapus_seleksi();
        });
      } else if (r.require_nik_confirmation === true) {
        simpanBtn(btnSave, false);
        Swal.fire({
          icon: "warning",
          title: "NIK sudah digunakan",
          html: renderNikUsageWarning(r.nik_usage),
          showDenyButton: true,
          confirmButtonText: "Ya, tetap simpan",
          denyButtonText: "Batal",
          allowOutsideClick: false,
        }).then(function (result) {
          if (result.isConfirmed) {
            simpan_dt_konsumen_keuangan(true);
          }
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: r.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          simpanBtn(btnSave, false);
        });
      }
    },
    error: function (e) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "Terjadi kesalahan",
        showConfirmButton: true,
        // timer: 1500
      }).then(function () {
        simpanBtn(btnSave, false);
      });
    },
  });
}

$("#status_mkdt").change(function () {
  if ($("#status_mkdt option:selected").val() == "Batal")
    $("#show_keterangan_batal").removeClass("hidden");
  else $("#show_keterangan_batal").addClass("hidden");
});

const mkdtKprState = {
  isLoading: false,
  isPromptOpen: false,
  previousAccValue: "0",
};

function mkdtAmount(selector) {
  return parseFloat(removeComma($(selector).val() || 0)) || 0;
}

function mkdtSetAmount(selector, value) {
  $(selector)
    .val(value || 0)
    .keyup();
}

function mkdtCalculateTurunKpr() {
  const hargaKpr = mkdtAmount("#fm-mkdt #harga_kpr");
  const accHargaKpr = mkdtAmount("#fm-mkdt #acc_harga_kpr");
  const turunKpr = accHargaKpr > 0 && accHargaKpr < hargaKpr ? hargaKpr - accHargaKpr : 0;

  mkdtSetAmount("#fm-mkdt #harga_turun_kpr", turunKpr);

  return { hargaKpr, accHargaKpr, turunKpr };
}

function mkdtRevertKprChange() {
  mkdtKprState.isLoading = true;
  mkdtSetAmount("#fm-mkdt #acc_harga_kpr", mkdtKprState.previousAccValue || 0);
  mkdtSetAmount("#fm-mkdt #harga_turun_kpr", 0);
  mkdtKprState.isLoading = false;
}

async function mkdtRequireTurunKprTagihan() {
  if (mkdtKprState.isLoading || mkdtKprState.isPromptOpen) return;

  const { turunKpr } = mkdtCalculateTurunKpr();
  if (turunKpr <= 0) {
    mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    return;
  }

  mkdtKprState.isPromptOpen = true;
  const { isConfirmed, isDismissed, value } = await loadFormTagihan(turunKpr);
  mkdtKprState.isPromptOpen = false;

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan Turun KPR ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    });
    return;
  }

  if (isDismissed) {
    mkdtRevertKprChange();
    Swal.fire(
      "Dibatalkan",
      "Nilai disetujui dikembalikan ke nilai awal dan Turun KPR direset 0.",
      "info",
    );
  }
}

$("#fm-mkdt #harga_kpr").change(function () {
  mkdtCalculateTurunKpr();
});

$("#fm-mkdt #acc_harga_kpr")
  .on("focusin", function () {
    if (!mkdtKprState.isLoading && !mkdtKprState.isPromptOpen) {
      mkdtKprState.previousAccValue = $(this).val() || "0";
    }
  })
  .on("change", function () {
    mkdtRequireTurunKprTagihan();
  });
//delete tanggal jika toogle di aktifkan
$("#wawancara").change(function () {
  if (!$("#wawancara").prop("checked")) {
    setDatePicker(null, "#wawancara_tgl");
  }
});

$("#refresh_fmmkdt_btn").click(function () {
  refresh_fmmkdt(false);
  $("#mkdt_data_baru").val(1);
});

function refresh_fmmkdt($st = true) {
  $("#fm-mkdt")[0].reset();
  $("#fm-mkdt input:text, #fm-mkdt select, #fm-mkdt textarea").prop(
    "disabled",
    $st,
  );
  mkdtKprState.isLoading = false;
  mkdtKprState.isPromptOpen = false;
  mkdtKprState.previousAccValue = "0";
  $("#id_konsumen").val("");
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#id_keuangan0").val("");
}

function delete_kons() {
  $(
    "#fm-mkdt #nama_konsumen, #fm-mkdt #alamat_konsumen, #fm-mkdt #nik_konsumen, #fm-mkdt #hp_konsumen, #fm-mkdt #status_konsumen",
  ).val("");
  $("#id_konsumen, #id_mkdt").val("");
  $("#mkdt_data_baru").val(1);
}

function open_mkdt(sh, role, id_kavling) {
  if (sh.data.tipe != "kavling")
    return swal("error", "Tidak ada kavling terpilih", null, true);

  if (!sh.data.id_mkdt)
    return swal("error", "Belum ada data konsumen", null, true);

  if (sh.data2.harga_akhir == "-") {
    return swal(
      "error",
      "Kavling belum dipasarkan",
      "Kavling belum memiliki harga jual",
    );
  }
  $("#lb-st-no_spptb").html("-");
  $("#lb-st-nama_konsumen").html("-");

  // $("#label-file_ktp").html("Upload file KTP");
  // $("#label-file_npwp").html("Upload file KTP");

  // $("#refresh_fmmkdt_div").addClass("hidden");
  // $("#delete_kons_div").addClass("hidden");
  // $("#fm-mkdt .num").prop("disabled", false);

  // $("#cicilan_belong_here").html("");
  it = 0;
  // $("#data_konsumen").tab('show');

  $("#mkdt_data_baru").val(0);

  refresh_fmmkdt(false);

  $("#fm-mkdt .num").val(0);

  $(".id_kavling").val(id_kavling);
  $("#id_mkdt").val(sh.data.id_mkdt);

  $.ajax({
    url: base_url + "api/transaksi/status/ambilsatu",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_mkdt: sh.data.id_mkdt,
      id_hargajual: sh.data2.id_hargajual,
      id_kavling: id_kavling,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (x) {
      $("#loading").addClass("hidden");
      csrfHash = x.token;
      let r = x.data, //data mkdt
        pb = x.perintah_bangun,
        tkpr = x.tagihan;
      mkdtKprState.isLoading = true;

      //load hargajual
      // if (h.hargajual) {
      //   $.each(h, function (k, v) {
      //     $("#mkdt-" + k)
      //       .val(v)
      //       .change()
      //       .keyup();
      //   });
      //   $("#mkdt-tgl_harga").val(format_date(h.tgl_harga));
      //   $("#fm-mkdt #harga_kpr").val(h.kpr).change();
      // }

      //load num
      // if (!r.id_mkdt) {
      //     $(".num").val(0).keyup();
      //     $("#fm-mkdt #harga_jual").val(sh.data2.harga_akhir);
      // }

      //set harga jual dari data kavling
      if (r) {
        if (r.status_mkdt == "Batal") {
          refresh_fmmkdt(true);
          $("#show_keterangan_batal, #refresh_fmmkdt_div").removeClass(
            "hidden",
          );
          $("#delete_kons_div").addClass("hidden");
          $("#delete-btn-idk_keu").addClass("hidden");
        }

        //autoload field ke input
        for (let i in r) {
          if (
            i != "perintah_bangun" &&
            i != "wawancara" &&
            i != "akad" &&
            i != "sp3k" &&
            i != "bast_file" &&
            i != "sp3k_file" &&
            i != "perintah_bangun_file"
          )
            $("#fm-mkdt #" + i).val(r[i]);
        }

        $("#lb-st-no_spptb").html(r.no_spptb);
        $("#lb-st-nama_konsumen").html(r.nama_konsumen);

        $("#fm-mkdt #mkdt_keterangan").val(r.keterangan);
        $("#fm-mkdt #acc_harga_kpr").val(r.harga_kpr_acc).change();
        $("#fm-mkdt #harga_turun_kpr").val(r.harga_penambahan_um).change();

        var newOption = new Option(r.nama_bank, r.id_bank, true, true);
        $("#id_bank").append(newOption).trigger("change");

        if (r.wawancara == 1) $("#wawancara").prop("checked", true);
        if (r.sp3k == 1) $("#sp3k").prop("checked", true);
        if (r.akad == 1) $("#akad").prop("checked", true);

        //set datepicker jika tanggal valid
        setDatePicker(pb.perintah_bangun_tgl, "#fm-mkdt #perintah_bangun_tgl");

        setDatePicker(r.booking_tgl, "#fm-mkdt #booking_tgl");
        setDatePicker(r.wawancara_tgl, "#fm-mkdt #wawancara_tgl");
        setDatePicker(r.sp3k_tgl, "#fm-mkdt #sp3k_tgl");
        setDatePicker(r.sp3k_tgl_exp, "#fm-mkdt #sp3k_tgl_exp");
        setDatePicker(r.rencana_akad_tgl, "#fm-mkdt #rencana_akad_tgl");
        setDatePicker(r.akad_tgl, "#fm-mkdt #akad_tgl");

        // if (r.refund_tgl != "0000-00-00")
        //     document.querySelector("#refund_tgl")._flatpickr.setDate(r.refund_tgl);

        $("#fm-mkdt .num").keyup().change(); //fomrat form number
        $("#status_mkdt").change(); //show/hide keterangan batal

        $("#mkdt_keterangan").val(r.keterangan);

        // $("#file_ktp-here").html("Tidak ada data");
        // src = not_found;

        setBtnHref("#list-upload_sp3k_file", r.sp3k_access_url);
      }

      if (pb.perintah_bangun == 1) {
        $("#perintah_bangun").prop("checked", true);
        $("#fm-mkdt #perintah_bangun_oleh").val(pb.username);
        setBtnHref(
          "#list-upload_perintah_bangun_file",
          pb.perintah_bangun_access_url,
        );
        setDatePicker(pb.perintah_bangun_tgl, "#perintah_bangun_tgl");
      }

      load_tagihankpr(tkpr);
      mkdtKprState.isLoading = false;
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";

      let label_alamat = setLabelAlamat(
        dt_proyek.nama_proyek,
        sh.data.nama_jalan,
        sh.data.no_kavling,
        sh.data2.no_tipe_rumah,
        sh.data2.tipe_rumah,
      );
      $(".label_alamat").html(label_alamat);

      $("#mkdt-tab-form-link").tab("show");
      resetMkdtHistoryTimeline();
      $("#mkdt-history-timeline").data("id-kavling", id_kavling);

      $("#modal_divisi" + role).modal({
        backdrop: "static",
        keyboard: false,
      });
      initModalListener("#modal_divisi" + role);
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}
async function hapus_turunkpr(id_keuangan) {
  const { isConfirmed } = await Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data keuangan ini akan dihapus permanen!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, hapus",
    cancelButtonText: "Batal",
  });

  if (isConfirmed) {
    Swal.fire({
      title: "Menghapus...",
      text: "Tunggu sebentar",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      let response = await fetch(`${base_url}tagihan/hapusturunkpr`, {
        method: "POST", // atau 'DELETE' kalau API pakai method delete
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id_keuangan: id_keuangan }),
      });

      let result = await response.json();

      if (response.ok && result.success) {
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: result.message,
        }).then(() => {
          // refresh table atau halaman
          load_tagihankpr(null);
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: result.message || "Terjadi kesalahan saat menghapus",
        });
      }
    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: error.message,
      });
    }
  }
}
function load_tagihankpr(val) {
  $("#mkdt-tagihan_kpr").html("");

  // console.log(val);

  if (val == null) {
    return;
  }

  let div = `
  <div class="divider divider-left">
      <div class="divider-text font-weight-bold">Tagihan Turun KPR</div>
  </div>
  `;
  if (val.berita_acara == "Turun KPR") {
    div += `
      <div class="form-group">
          <label for="bank">Tanggal Jatuh Tempo Turun KPR</label>
          <input type="text" readonly class="form-control" value='${format_date(
            val.jatuh_tempo_tgl,
          )}' />
              <a href="#" onclick="hapus_turunkpr(${
                val.id_keuangan
              })"class="text-danger"><i class="fa fa-trash"></i>Klik untuk hapus tagihan</a>
      </div>
      `;
  }

  $("#mkdt-tagihan_kpr").html(div);
}

$("#add-form-btn-mkdt").click(function (e) {
  e.preventDefault();
});

function save_mkdt(e) {
  const btn = "#add-form-btn-mkdt";
  if (!palid("fm-mkdt #status_mkdt", "", "Status harus diisi")) return;
  if (!palid("fm-mkdt #id_bank", "", "Bank harus diisi")) return;

  if (
    removeComma($("#harga_turun_kpr").val()) > 0 &&
    $("#mkdt-tagihan_kpr").html() == ""
  ) {
    swal(
      "warning",
      "Tagihan untuk turun KPR harus dibuat terlebih dahulu",
      "Karena ada nilai di turun KPR, jadi harus buat tagihannya dulu ya!",
      false,
      hlButton("#btn-add-tagihan-turunkpr"),
    );
    return;
  }

  // if ($("#total_cicilan").val() != $("#total_biaya2").val()) {
  //     Swal.fire({
  //         //position: 'bottom-end',
  //         icon: 'error',
  //         title: "Total Cicilan tidak sesuai dengan total biaya",
  //         showConfirmButton: false,
  //         timer: 1500
  //     });
  //     return false;
  // }

  // var files = $('#file_ktp')[0].files;
  var form = $("#fm-mkdt")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "api/transaksi/status/simpan",
    type: "post",
    // data: $("#fm-mkdt").serialize() + "&" + csrfName + "=" + csrfHash,
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(btn, true);
    },
    success: function (r) {
      csrfHash = r.token;

      if (r.success === true) {
        removeModalListener("#modal_divisi4");
        load_kavling();
        hapus_seleksi();

        swal("success", r.messages);
        $(".modal").modal("hide");
      } else {
        swal("error", r.messages);
      }
      simpanBtn(btn, false);
    },
    error: function (xhr, st, err) {
      simpanBtn(btn, false);
      return swal("error", err);
    },
  });
}

function set_harga() {
  $.ajax({
    url: base_url + "Hargajual/set_harga",
    type: "post",
    data: $("#fm-set_harga").serialize() + "&" + csrfName + "=" + csrfHash, // /converting the form data into array and sending it to server
    dataType: "json",
    beforeSend: function () {
      $("#set-harga-form-btn").html(
        'Menyimpan <i class="fa fa-spinner fa-spin"></i>',
      );
      $("#set-harga-form-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      swal(
        response.success ? "success" : "error",
        response.messages,
        null,
        true,
        () => {
          if (response.success) {
            $("#modals-set_harga").modal("hide");
          }
          $("#set-harga-form-btn").html("Simpan");
          $("#set-harga-form-btn").removeClass("disabled");
        },
      );
      load_kavling();
      hapus_seleksi();
    },
  });
}

function formatDesign(item) {
  var selectionText = item.text.split(";");
  var $returnString = $(
    "<span> <b>" +
      selectionText[0] +
      "</b></br >" +
      selectionText[1] +
      "</br>" +
      selectionText[2] +
      "</span>",
  );
  return $returnString;
}
$("#sh-id").select2({
  placeholder: "Pilih Pricelist",
  allowClear: true,
  templateResult: formatDesign,
  ajax: {
    url: base_url + "hargajual/get",
    dataType: "json",
    delay: 250,
    method: "post",
    data: function (params) {
      return {
        [csrfName]: csrfHash,
        search: params.term,
        id_proyek: dt_proyek.id_proyek,
      };
    },
    processResults: function (r) {
      csrfHash = r.token;

      let results = [];
      $.each(r.data, function (k, v) {
        results.push({
          id: v.id,
          text: `Rp. ${num_format(v.hargajual)} Per ${format_date(
            v.tgl_harga,
          )} (ROW ${v.row}); <b>Tipe:</b> ${v.id_tipe}; <b>Ket:</b> ${
            v.keterangan
          };`,
          row: v.row,
          tipe: v.id_tipe,
          lb: v.lb,
          lt: v.lt,
          hargajual: v.hargajual,
          hargajual_net: v.hargajual_net,
          kpr: v.kpr,
          uang_muka: v.uang_muka,
          bphtb: v.bphtb,
          ppn: v.ppn,
          biaya_adm: v.biaya_adm,
          biaya_proses: v.biaya_proses,
          id_tipe: v.id_tipe,
          lok: file_url('file_hargajual', v.id_filehj),
        });
      });

      return {
        results: results,
      };
    },
    cache: false,
  },
});
// on select cluster
$("#sh-id").on("select2:selecting", function (e) {
  var i = e.params.args.data;
  $.each(i, function (k, v) {
    changeVal("#sh-" + k, v);
  });

  let src = i.lok != "null/null" ? i.lok : not_found;
  setFileHref("#sh-pricelist_file", false, src);
});

$("#sh-id").change(function () {
  if (!this.value) $(".sh-fm").val("");
});

function open_set_turun_pembangunan() {
  $("#list-tp-upload_perintah_bangun_file").prop("href", resolveFileHref(not_found));
  $("#label-perintah_bangun_file").html("Upload File Perintah Bangun");
  if (editdtt.length == 0) {
    return swal("error", "Tidak ada kavling terpilih");
  }
  $("#fm-turun_pembangunan")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }
  $.ajax({
    url: base_url + "siteplan/get_turun_pembangunan",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      let r = res.data,
        id_kavling = "",
        no = "";

      $(".select2").not("#pilih-divisi").val(null).trigger("change");

      if (r.length > 0) {
        r.forEach((v) => {
          id_kavling += v.id_kavling + ";";
          no += `${v.nama_jalan} No. ${v.no_kavling} \n`;
        });

        $(".id_kavling").val(id_kavling);
        $("#tp-kavling").val(no);

        $("#tp-perintah_bangun_oleh").val(r[0].username);

        $("#list-tp-upload_perintah_bangun_file").prop(
          "href",
          resolveFileHref(r[0].perintah_bangun_access_url, not_found),
        );

        setDatePicker(r[0].perintah_bangun_tgl, "#tp-perintah_bangun_tgl");
      }

      $("#loading").addClass("hidden");
      $("#modals-turun_pembangunan").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function set_tp() {
  if ($("#tp-perintah_bangun_tgl").val() == "") {
    return Swal.fire({
      icon: "error",
      title: "Tanggal Perintah Bangun harus diisi",
      showConfirmButton: false,
    });
  }
  let form = $("#fm-turun_pembangunan")[0];
  let fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  $.ajax({
    url: base_url + "siteplan/set_turun_pembangunan",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      $("#set-tp-btn").html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan');
      $("#set-tp-btn").addClass("disabled");
    },
    success: function (response) {
      csrfHash = response.token;
      if (response.success === true) {
        Swal.fire({
          //position: 'bottom-end',
          icon: "success",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#modals-turun_pembangunan").modal("hide");
          $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
          $("#set-tp-btn").removeClass("disabled");
        });
      } else {
        Swal.fire({
          //position: 'bottom-end',
          icon: "error",
          title: response.messages,
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
          $("#set-tp-btn").removeClass("disabled");
        });
      }
      load_kavling();
      hapus_seleksi();
    },
    error: function (err) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan saat menginput data",
        showConfirmButton: false,
      }).then(function () {
        $("#set-tp-btn").html('<i class="fa fa-save mr-1" aria-hidden="true"></i>Simpan');
        $("#set-tp-btn").removeClass("disabled");
      });
    },
  });
}

function setFileHref(id, remove = true, url = null) {
  if (remove) {
    $(id).removeAttr("target");
    $(id).prop("href", "javascript:void(0)");
  } else {
    $(id).prop("href", resolveFileHref(url));
    $(id).prop("target", "_blank");
  }
}

function open_set_harga() {
  if (editdtt.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  setFileHref("#sh-pricelist_file");

  $("#fm-set_harga")[0].reset();

  let data = [];

  for (let a = 0; a < editdtt.length; a++) {
    data.push(editdtt[a].id.substr(3));
  }

  $.ajax({
    url: base_url + "siteplan/get_harga_kavling",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: data,
    },
    dataType: "json",
    beforeSend: function () {
      $("#loading").removeClass("hidden");
    },
    success: function (res) {
      csrfHash = res.token;
      $("#loading").addClass("hidden");
      let r = res.data,
        id_kavling = "",
        src,
        no = "";

      $("#sh-id").val(null).trigger("change");

      if (r.length > 0) {
        for (let a = 0; a < r.length; a++) {
          id_kavling += r[a].id_kavling + ";";
          no += `${r[a].nama_jalan} No. ${r[a].no_kavling} - ${r[a].tipe_rumah}\n`;

          if (r[a].harga_akhir) {
            $("#sh-id")
              .append(
                $("<option selected></option>")
                  .attr("value", r[a].harga_akhir)
                  .text(
                    `Rp. ${num_format(r[a].hargajual)} (${
                      r[a].tipe_rumah
                    }) ROW ${r[a].row}: per ${format_date(r[a].tgl_harga)}`,
                  ),
              )
              .trigger("change");

            changeVal("#sh-row", r[a].row);
            changeVal("#sh-tipe", r[a].tipe_rumah);
            changeVal("#sh-lb", r[a].hj_lb);
            changeVal("#sh-lt", r[a].hj_lt);
            changeVal("#sh-hargajual", r[a].hargajual);
            changeVal("#sh-hargajual_net", r[a].hargajual_net);
            changeVal("#sh-kpr", r[a].kpr);
            changeVal("#sh-uang_muka", r[a].uang_muka);
            changeVal("#sh-ppn", r[a].ppn);
            changeVal("#sh-bphtb", r[a].bphtb);
            changeVal("#sh-biaya_adm", r[a].biaya_adm);
            changeVal("#sh-biaya_proses", r[a].biaya_proses);

            src = not_found;
            if (r[a].file_name) {
              src = file_url('file_hargajual', r[a].id_filehj);
            }
            setFileHref("#sh-pricelist_file", false, src);
          } else {
            setFileHref("#sh-pricelist_file");
          }
        }

        $(".id_kavling").val(id_kavling);
        $("#sh-kavling").val(no);
        // $("#fm-set_harga #id_tipe").val(id_tipe);
        // $("#fm-set_harga #harga").val(harga).keyup();
      }

      $("#modals-set_harga").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      $("#loading").addClass("hidden");
      return swal("error", err);
    },
  });
}

let siDataCache = [];

function getSiRecordKey(item) {
  return item && item.id ? item.id : "n" + item.id_list_si_ori;
}

function isSiSaved(item) {
  return !!(
    item &&
    (item.id || item.tanggal_si || item.keterangan || item.file || item.access_url)
  );
}

function getSelectedSiItem() {
  const selectedId = $("#si-id_list_si").val();
  return siDataCache.find((item) => String(item.id_list_si_ori) === String(selectedId));
}

function initSiDatePicker(value = "") {
  const input = document.querySelector("#si-tanggal_si");
  if (!input) return;

  if (input._flatpickr) {
    input._flatpickr.setDate(value || null, false);
    return;
  }

  flatpickr(input, {
    altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d",
    defaultDate: value || null,
  });
}

function setSiDynamicFields(item) {
  const key = item ? getSiRecordKey(item) : "";
  $("#si-current-key").val(key);
  if (key) {
    $("#si-tanggal_si").attr("name", `id-si[${key}][tanggal_si]`);
    $("#si-keterangan").attr("name", `id-si[${key}][keterangan]`);
    $("#si-file").attr("name", `id-si-file-${key}`);
  } else {
    $("#si-tanggal_si, #si-keterangan, #si-file").removeAttr("name");
  }
}

function resetSiInput() {
  $("#si-id_list_si").val("");
  $("#si-keterangan").val("");
  $("#si-file").val("");
  $("#si-file-label").text("Upload Soft File");
  $("#si-current-file").addClass("hidden").attr("href", "#");
  $("#si-selected-label").text("-");
  $("#si-selected-date").text("-");
  setSiDynamicFields(null);
  initSiDatePicker("");
}

function fillSiInput(item) {
  if (!item) {
    resetSiInput();
    return;
  }

  setSiDynamicFields(item);
  $("#si-id_list_si").val(item.id_list_si_ori);
  $("#si-keterangan").val(item.keterangan || "");
  $("#si-file").val("");
  $("#si-file-label").text("Upload Soft File");
  initSiDatePicker(item.tanggal_si || "");
  $("#si-selected-label").text(item.nama || "-");
  $("#si-selected-date").text(item.tanggal_si ? format_date(item.tanggal_si) : "-");

  if (item.access_url) {
    $("#si-current-file")
      .removeClass("hidden")
      .attr("href", item.access_url);
  } else {
    $("#si-current-file").addClass("hidden").attr("href", "#");
  }
}

function renderSiOptions(data) {
  let options = '<option value="">Pilih Standing Instruction</option>';
  $.each(data, function (_, item) {
    const savedLabel = isSiSaved(item) ? " (sudah tersimpan)" : "";
    options += `<option value="${escapeAttribute(item.id_list_si_ori)}">${escapeHtml(item.nama || "-")}${savedLabel}</option>`;
  });
  $("#si-id_list_si").html(options);
}

function renderSiTable(data) {
  const rows = data.filter(isSiSaved);
  const tbody = $("#si-table tbody");
  tbody.empty();

  if (!rows.length) {
    tbody.html("<tr><td colspan='5' class='text-center text-muted'>Data tidak ditemukan</td></tr>");
    return;
  }

  $.each(rows, function (_, item) {
    const fileButton = item.access_url
      ? `<a href="${escapeAttribute(item.access_url)}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye mr-1"></i> Lihat</a>`
      : '<span class="text-muted">-</span>';
    const row = `
      <tr>
        <td>${escapeHtml(item.nama || "-")}</td>
        <td>${item.tanggal_si ? format_date(item.tanggal_si) : "-"}</td>
        <td>${fileButton}</td>
        <td>${escapeHtml(item.keterangan || "-")}</td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-primary si-edit-btn" data-id="${escapeAttribute(item.id_list_si_ori)}">
            <i class="fas fa-edit mr-1"></i> Pilih
          </button>
        </td>
      </tr>`;
    tbody.append(row);
  });
}

function load_si_data(id_kavling, keepSelection = false) {
  const selectedId = keepSelection ? $("#si-id_list_si").val() : "";

  $.ajax({
    url: base_url + "mkdt/getsi",
    type: "post",
    data: {
      [csrfName]: csrfHash,
      id_kavling: id_kavling,
    },
    dataType: "json",
    success: function (res) {
      csrfHash = res.token;
      siDataCache = res.data || [];
      renderSiOptions(siDataCache);
      renderSiTable(siDataCache);
      if (selectedId) {
        $("#si-id_list_si").val(selectedId);
      }

      const selected = keepSelection ? getSelectedSiItem() : null;
      if (selected) {
        fillSiInput(selected);
      } else {
        resetSiInput();
      }

      $("#modals-si").modal({
        backdrop: "static",
        keyboard: false,
      });
    },
    error: function (xhr, st, err) {
      return swal("error", err);
    },
  });
}

function isi_si() {
  let sh = editdtt;

  if (sh.length == 0)
    return swal("error", "Tidak ada kavling terpilih", null, true);

  sh = sh[0];

  let id_kavling = sh.id.substr(3);
  let nama_proyek = dt_proyek?.nama_proyek ?? sh.data.nama_proyek;
  let label_alamat = setLabelAlamat(
    nama_proyek,
    sh.data.nama_jalan,
    sh.data.no_kavling,
    sh.data2.no_tipe_rumah,
    sh.data2.tipe_rumah,
  );

  $(".id_kavling").val(id_kavling);
  $("#modals-si .label_alamat").html(label_alamat);
  $("#fm-si")[0].reset();
  $("#si-table tbody").html("<tr><td colspan='5' class='text-center text-muted'>Memuat data...</td></tr>");
  load_si_data(id_kavling);
}

$("#si-id_list_si").on("change", function () {
  fillSiInput(getSelectedSiItem());
});

$("#si-file").on("change", function () {
  const fileName = this.files && this.files[0] ? this.files[0].name : "Upload Soft File";
  $("#si-file-label").text(fileName);
});

$(document).on("click", ".si-edit-btn", function () {
  const itemId = $(this).data("id");
  $("#si-id_list_si").val(itemId);
  fillSiInput(getSelectedSiItem());
});

function save_si() {
  const selected = getSelectedSiItem();

  if (!selected) {
    return swal("error", "Pilih Standing Instruction terlebih dahulu");
  }

  if ($("#si-tanggal_si").val() == "") {
    return swal("error", "Tanggal SI harus diisi");
  }

  if (!selected.access_url && !selected.file && $("#si-file").val() == "") {
    return swal("error", "Soft file Standing Instruction harus diupload");
  }

  var form = $("#fm-si")[0];
  var fd = new FormData(form);
  fd.append(csrfName, csrfHash);

  let sbtn = "#btn-si-simpan";

  $.ajax({
    url: base_url + "mkdt/saveSI",
    type: "post",
    contentType: false,
    processData: false,
    data: fd,
    dataType: "json",
    beforeSend: function () {
      simpanBtn(sbtn, true);
    },
    success: function (r) {
      csrfHash = r.token;
      simpanBtn(sbtn, false);
      if (r.success === true) {
        swal("success", r.messages);
        load_si_data($("#fm-si .id_kavling").val(), true);
        load_kavling();
      } else {
        swal("error", "Terjadi kesalahan", r.messages);
      }
    },
    error: function (r) {
      Swal.fire({
        //position: 'bottom-end',
        icon: "error",
        title: "terjadi kesalahan",
        showConfirmButton: false,
        // timer: 1500
      });
      simpanBtn(sbtn, false);
    },
  });
}

$("#fm-mkdt #sp3k_tgl").change(function () {
  if (!this.value) return;
  document
    .querySelector("#fm-mkdt #sp3k_tgl_exp")
    ._flatpickr.setDate(new Date(this.value).fp_incr(88));
});

//untuk tambah konsumen baru ketika batal
$("#refresh-btn-idk_keu").click(function () {
  $("#fm-idk_keu .num").prop("disabled", false);
  $("#fm-idk_keu")[0].reset();

  // refresh_fmmkdt(false);
  $("#fm-idk_keu input:text, #fm-idk_keu select, #fm-idk_keu textarea").prop(
    "disabled",
    false,
  );
  ui.form.kons.find("#idk-id_konsumen").val("");
  $("#idk_data_baru").val(1);
  $("#idk-show_keterangan_batal").addClass("hidden");
  setIdkPerluRefund(0);

  if (state.mkdt.harga_jual && state.mkdt.diskresi) {
    fillPriceSection(state.mkdt.harga_jual, state.mkdt.diskresi);
  }
  state.data_um = {};
  tambah_ketagihan();
  sum_mktotal();
});

function mkdtUpload() {
  const inputs = [
    { id: "file_ktp" },
    { id: "file_npwp" },
    { id: "file_data_diri" },
    { id: "perintah_bangun_file" },
    { id: "sp3k_file" },
    { id: "bast_file" },
  ];

  inputs.forEach((item) => {
    const input = document.getElementById(item.id);
    if (!input || input.dataset.dropzoneLoaded === "1") return;

    load_dropzone(item.id);
    input.dataset.dropzoneLoaded = "1";
  });

  bindMkdtClipboardUpload();
  bindKtpOcr();
}

function postTurunKPR(val) {
  // console.log(val)
  const { berita_acara, nominal, tgl, id_mkdt } = val;
  $.ajax({
    url: base_url + "tagihan/turunkpr",
    type: "POST",
    dataType: "json",
    data: { [csrfName]: csrfHash, berita_acara, nominal, tgl, id_mkdt },
    beforeSend: () => {},
    success: () => {},
    error: () => {},
  });
}
async function loadFormTagihan(nominal_kpr) {
  const { isConfirmed, isDismissed, value } = await Swal.fire({
    title: "Tambah Ke Tagihan",
    html: `
      <div class="swal2-content mt-1" style="text-align:left">
        <div class="alert alert-warning py-1 px-2 mb-1" style="font-size:.82rem; line-height:1.4">
          Turun KPR wajib dibuat tagihan sebelum perubahan nilai disetujui bisa disimpan.
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="Turun KPR" readonly id="fkpr-berita_acara" placeholder=" " required>
          <label for="fkpr-berita_acara">Untuk Tagihan</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control" value="${nominal_kpr}" readonly id="fkpr-nominal" placeholder=" " required>
          <label for="fkpr-nominal">Nominal</label>
        </div>
        <div class="form-group floating-label">
          <input type="text" class="form-control fp-jatuhtempo" id="fkpr-jatuh_tempo_tgl" placeholder=" " required>
          <label for="fkpr-jatuh_tempo_tgl">Jatuh Tempo</label>
        </div>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: "Simpan Tagihan",
    cancelButtonText: "Batal",
    allowOutsideClick: false,
    allowEscapeKey: false,
    showLoaderOnConfirm: true,

    didOpen: () => {
      // const popup = Swal.getPopup();
      const el = document.querySelector(".fp-jatuhtempo");
      if (el && el._flatpickr) el._flatpickr.destroy();
      flatpickr(el, {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        defaultDate: new Date(),
        allowInput: true,
        // appendTo: popup, // penting biar z-index aman
      });
    },

    preConfirm: async () => {
      const p = Swal.getPopup();
      const berita_acara = p.querySelector("#fkpr-berita_acara").value.trim();
      const nominalStr = p.querySelector("#fkpr-nominal").value.trim();
      const tgl = p.querySelector("#fkpr-jatuh_tempo_tgl").value.trim();
      const id_mkdt = document.querySelector("#id_mkdt").value.trim();
      const id_kavling = document.querySelector(".id_kavling").value.trim();
      const id_konsumen = document.querySelector("#id_konsumen").value.trim();
      const harga_kpr = document.querySelector("#harga_kpr").value.trim();
      const acc_harga_kpr = document
        .querySelector("#acc_harga_kpr")
        .value.trim();

      const nominal = Number(nominalStr.replace(/[^\d.-]/g, "")) || 0;

      if (!berita_acara)
        return Swal.showValidationMessage("Untuk Tagihan wajib diisi");
      if (nominal <= 0)
        return Swal.showValidationMessage("Nominal tidak boleh 0");
      if (!tgl)
        return Swal.showValidationMessage(
          "Tanggal jatuh tempo tidak boleh kosong",
        );

      // ---- POST ke server ----
      try {
        // (opsional) Abort kalau kelamaan
        const ac = new AbortController();
        const timeout = setTimeout(() => ac.abort(), 20000); // 20 detik

        const res = await fetch(`${base_url}tagihan/turunkpr`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            // sertakan CSRF kalau CI4 mengaktifkan:
            // "X-CSRF-TOKEN": window.CSRF_TOKEN
          },
          body: JSON.stringify({
            berita_acara,
            nominal,
            jatuh_tempo: tgl,
            id_mkdt,
            id_kavling,
            id_konsumen,
            harga_kpr,
            acc_harga_kpr,
          }),
          signal: ac.signal,
        });
        clearTimeout(timeout);

        // Tangani error HTTP
        if (!res.ok) {
          const text = await res.text().catch(() => "");
          throw new Error(text || `Gagal menyimpan (HTTP ${res.status})`);
        }

        const data = await res.json().catch(() => ({}));
        // Jika API kamu kirim {success:false, message:"..."}
        if (data && data.success === false) {
          throw new Error(data.messages || "Gagal menyimpan");
        }
        // Return untuk diteruskan ke .then(...) sebagai `value`
        return data;
      } catch (err) {
        // Tetap di popup + tampilkan pesan error di bawah tombol
        return Swal.showValidationMessage(err.message || "Gagal menyimpan");
      }
    },
  });

  return { isConfirmed, isDismissed, value };
}
let btnTunruKpr = "#btn-add-tagihan-turunkpr";
$(btnTunruKpr).click(async function (e) {
  e.preventDefault();
  let nominal_kpr = removeComma($("#harga_turun_kpr").val());

  if (nominal_kpr == 0) {
    return swal(
      "error",
      "Terjadi Kesalahan",
      "Tidak bisa menambahkan ke tagihan jika nominal Turun KPR 0!",
    );
  }
  const { isConfirmed, isDismissed, value } = await loadFormTagihan(nominal_kpr);

  if (isConfirmed) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Tagihan ditambahkan.",
    }).then(() => {
      load_tagihankpr(value.data);
      mkdtKprState.previousAccValue = $("#fm-mkdt #acc_harga_kpr").val() || "0";
    });
  } else if (isDismissed) {
    Swal.fire("Dibatalkan", "Aksi dibatalkan.", "info");
  }
});

</script>
