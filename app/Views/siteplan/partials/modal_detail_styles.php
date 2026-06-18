    #modal_detail .modal-dialog {
        max-width: min(1440px, calc(100vw - 32px));
        margin: 1rem auto;
    }

    #modal_detail .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    #modal_detail .modal-header {
        align-items: center;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.25rem;
    }

    #modal_detail .modal-title {
        /* color: #111827; */
        font-size: 1.05rem;
        font-weight: 700;
    }

    #modal_detail .modal-body {
        background: #f3f5f7 !important;
        max-height: calc(100vh - 7rem);
        overflow-y: auto;
        padding: 1rem;
    }

    #modal_detail .detail-kavling-layout {
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
        margin: 0;
    }

    #modal_detail .detail-kavling-sidebar,
    #modal_detail .detail-kavling-content {
        padding: 0;
    }

    #modal_detail .detail-kavling-sidebar {
        align-self: flex-start;
        flex: 0 0 320px;
        position: sticky;
        top: 0;
        max-width: 320px;
        z-index: 2;
    }

    #modal_detail .detail-kavling-content {
        flex: 1 1 auto;
        max-width: calc(100% - 336px);
        min-width: 0;
    }

    #modal_detail .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: none;
        margin-bottom: 1rem;
    }

    #modal_detail .card-body {
        padding: 1rem;
    }

    #modal_detail .detail-hero-card {
        background: linear-gradient(145deg, #2056a4 0%, #1f7a8c 100%);
        border: 0;
        color: #fff;
        overflow: hidden;
    }

    #modal_detail .detail-hero-card .card-body {
        background: transparent !important;
    }

    #modal_detail .detail-hero-card .label_alamat {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: .75rem;
    }

    #modal_detail .detail-price-card,
    #modal_detail .detail-meta-card {
        background: rgba(255, 255, 255, 0.94);
        border: 0;
        color: #111827;
        margin-top: .85rem;
    }

    #modal_detail .detail-price-card h5 {
        color: #374151;
        font-size: .85rem;
        margin-bottom: .35rem;
    }

    #modal_detail #label-hargajual h2 {
        font-size: 1.25rem;
        line-height: 1.2;
    }

    #modal_detail .detail-meta-card h6 {
        color: #374151;
        font-size: .78rem;
        line-height: 1.35;
    }

    #modal_detail .detail-consumer-card .card-header,
    #modal_detail .detail-tabs-card .card-body {
        background: #fff;
        border-bottom: 1px solid #edf0f2;
        padding: .85rem 1rem;
    }

    #modal_detail .detail-consumer-card .card-body {
        padding: .85rem 1rem 1rem;
    }

    #modal_detail .detail-consumer-card li {
        border-bottom: 1px solid #edf0f2;
        margin-bottom: 0 !important;
        padding: .55rem 0;
    }

    #modal_detail .detail-consumer-card li:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    #modal_detail .detail-consumer-card b,
    #modal_detail label,
    #modal_detail .info-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 0;
    }

    #modal_detail .detail-consumer-card span {
        color: #111827;
        display: block;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #modal_detail .detail-consumer-toggle {
        font-size: .78rem;
        margin-top: .75rem;
        padding: .45rem .65rem;
    }

    #modal_detail .detail-consumer-toggle .hide-label,
    #modal_detail .detail-consumer-toggle[aria-expanded="true"] .show-label {
        display: none;
    }

    #modal_detail .detail-consumer-toggle[aria-expanded="true"] .hide-label {
        display: inline;
    }

    #modal_detail .detail-consumer-toggle i {
        transition: transform .2s ease;
    }

    #modal_detail .detail-consumer-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    #modal_detail .detail-tabs-card {
        margin-bottom: .75rem;
    }

    #modal_detail .detail-tabs-card .card-body {
        padding: .65rem;
    }

    #modal_detail .nav-pills {
        gap: .4rem;
    }

    #modal_detail .nav-pills .nav-link {
        border-radius: 6px;
        color: #4b5563;
        font-size: .82rem;
        font-weight: 700;
        padding: .55rem .8rem;
        white-space: nowrap;
    }

    #modal_detail .nav-pills .nav-link.active {
        background-color: #2056a4;
        box-shadow: 0 6px 14px rgba(32, 86, 164, .2);
        color: #fff;
    }

    #modal_detail .detail-panel-card {
        margin-bottom: 0;
    }

    #modal_detail .tab-content {
        min-width: 0;
    }

    #modal_detail .tab-pane {
        overflow-x: auto;
    }

    #modal_detail .divider {
        margin: .65rem 0 .85rem;
    }

    #modal_detail .divider-left {
        border-left-color: #2056a4;
        margin-bottom: .85rem;
        padding-left: .75rem;
    }

    #modal_detail .divider .divider-text {
        color: #111827;
        font-size: .86rem;
        font-weight: 700;
    }

    #modal_detail .info-row {
        align-items: flex-start;
        background: #f9fafb;
        border: 1px solid #edf0f2;
        border-radius: 6px;
        margin: 0 0 .45rem;
        padding: .45rem .55rem;
    }

    #modal_detail .info-value {
        color: #111827;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    #modal_detail .form-group {
        margin-bottom: .8rem;
    }

    #modal_detail .form-control {
        background-color: #fff;
        border-color: #d8dde3;
        border-radius: 6px;
        min-height: 36px;
    }

    #modal_detail .form-control:disabled,
    #modal_detail .form-control[readonly] {
        background-color: #f8fafc;
        color: #111827;
        opacity: 1;
    }

    #modal_detail .btn {
        border-radius: 6px;
        white-space: normal;
    }

    #modal_detail table {
        width: 100% !important;
    }

    #modal_detail .detail-dashboard-grid,
    #modal_detail .detail-card-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    #modal_detail .detail-summary-card,
    #modal_detail .detail-metric-card,
    #modal_detail .detail-accordion-card,
    #modal_detail .detail-progress-card,
    #modal_detail .detail-mini-card {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    #modal_detail .detail-summary-card {
        padding: 1rem;
    }

    #modal_detail .detail-summary-card .divider {
        margin-top: 0;
    }

    #modal_detail .detail-card-icon {
        align-items: center;
        background: #205792;
        border-radius: 10px;
        color: #dbeafe;
        display: inline-flex;
        height: 48px;
        justify-content: center;
        width: 48px;
    }

    #modal_detail .detail-metric-card {
        min-height: 250px;
        padding: 1.5rem;
        position: relative;
    }

    #modal_detail .detail-metric-card .detail-percent {
        color: #003b78;
        font-size: 1.25rem;
        font-weight: 700;
        position: absolute;
        right: 1.5rem;
        top: 1.45rem;
    }

    #modal_detail .detail-metric-title {
        color: #344054;
        font-size: .98rem;
        font-weight: 700;
        margin-top: 1.5rem;
    }

    #modal_detail .detail-metric-total {
        color: #020617;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.2;
        margin: .35rem 0 1.6rem;
    }

    #modal_detail .detail-metric-row {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: .85rem;
    }

    #modal_detail .detail-metric-label {
        color: #344054;
        font-size: .96rem;
    }

    #modal_detail .detail-metric-value {
        color: #020617;
        font-weight: 800;
        text-align: right;
    }

    #modal_detail .detail-metric-value.is-paid {
        color: #006b35;
    }

    #modal_detail .detail-metric-value.is-due {
        color: #c40000;
    }

    #modal_detail .detail-progress-track {
        background: #e7eefb;
        border-radius: 999px;
        height: 9px;
        overflow: hidden;
        width: 100%;
    }

    #modal_detail .detail-progress-fill {
        background: #4ade80;
        border-radius: inherit;
        height: 100%;
        transition: width .2s ease;
        width: 0%;
    }

    #modal_detail .detail-progress-fill.is-partial {
        background: #2563eb;
    }

    #modal_detail .detail-progress-fill.is-empty {
        background: transparent;
    }

    #modal_detail .detail-legacy-hidden {
        display: none !important;
    }

    #modal_detail .detail-accordion {
        display: flex;
        flex-direction: column;
        gap: .75rem;
    }

    #modal_detail .detail-accordion-card {
        overflow: hidden;
    }

    #modal_detail .detail-accordion-toggle {
        align-items: center;
        background: #fff;
        border: 0;
        color: #111827;
        display: flex;
        font-weight: 800;
        justify-content: space-between;
        padding: 1rem 1.1rem;
        text-align: left;
        width: 100%;
    }

    #modal_detail .detail-accordion-toggle i {
        color: #2056a4;
        transition: transform .2s ease;
    }

    #modal_detail .detail-accordion-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    #modal_detail .detail-accordion-body {
        border-top: 1px solid #edf0f2;
        padding: 1rem;
    }

    #modal_detail .detail-status-card-header {
        align-items: center;
        display: flex;
        gap: .65rem;
        margin-bottom: 1rem;
    }

    #modal_detail .detail-status-card-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
    }

    #modal_detail .detail-status-card-toggle {
        margin-left: auto;
    }

    #modal_detail .detail-info-row {
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: .9rem;
    }

    #modal_detail .detail-info-col {
        display: flex;
        flex: 1 1 0;
        flex-direction: column;
        gap: .3rem;
        min-width: 0;
    }

    #modal_detail .detail-info-col.text-right {
        align-items: flex-end;
    }

    #modal_detail .detail-info-label {
        color: #6b7280;
        font-size: .76rem;
        font-weight: 700;
    }

    #modal_detail .detail-info-value {
        background-color: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        color: #111827;
        font-size: .92rem;
        font-weight: 700;
        height: auto !important;
        min-height: 0 !important;
        opacity: 1 !important;
        overflow-wrap: anywhere;
        padding: 0 !important;
        width: 100%;
    }

    #modal_detail select.detail-info-value {
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
    }

    #modal_detail .detail-info-col.text-right .detail-info-value {
        text-align: right;
        text-align-last: right;
    }

    #modal_detail .detail-text-primary {
        color: #2056a4 !important;
    }

    #modal_detail .detail-text-danger {
        color: #dc2626 !important;
    }

    #modal_detail .detail-text-warning {
        color: #b7791f !important;
    }

    #modal_detail .detail-highlight-box {
        background: #eaf2fd;
        border-radius: 8px;
        margin-bottom: .9rem;
        padding: .65rem .9rem;
    }

    #modal_detail .detail-highlight-label {
        color: #2056a4;
        display: block;
        font-size: .76rem;
        font-weight: 700;
        margin-bottom: .15rem;
    }

    #modal_detail .detail-highlight-value {
        background-color: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        color: #2056a4 !important;
        font-size: 1.2rem;
        font-weight: 800;
        height: auto !important;
        min-height: 0 !important;
        opacity: 1 !important;
        padding: 0 !important;
        width: 100%;
    }

    #modal_detail .detail-section-divider {
        align-items: center;
        border-top: 1px dashed #e5e7eb;
        display: flex;
        justify-content: space-between;
        margin: 1rem 0 .9rem;
        padding-top: .85rem;
    }

    #modal_detail .detail-section-title {
        color: #111827;
        font-size: .86rem;
        font-weight: 800;
    }

    #modal_detail .detail-section-title-dot::before {
        background: #2056a4;
        border-radius: 50%;
        content: "";
        display: inline-block;
        height: 7px;
        margin-right: .45rem;
        width: 7px;
    }

    #modal_detail .detail-status-badge {
        border-radius: 999px;
        display: inline-block;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .03em;
        padding: .15rem .65rem;
        text-transform: uppercase;
    }

    #modal_detail .detail-status-badge.badge-success {
        background: #dcfce7;
        color: #15803d;
    }

    #modal_detail .detail-status-badge.badge-secondary {
        background: #f1f5f9;
        color: #64748b;
    }

    #modal_detail .detail-status-badge.badge-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    #modal_detail .detail-file-btn {
        align-items: center;
        display: flex;
        font-size: .86rem;
        font-weight: 700;
        justify-content: center;
        margin-top: .25rem;
    }

    #modal_detail .detail-file-btn-success {
        background: #eafaf0;
        border-color: #34d399;
        color: #0f9d58;
    }

    #modal_detail .detail-file-btn-success:hover {
        background: #d6f5e3;
        color: #0f9d58;
    }

    #modal_detail .detail-status-card .custom-switch .custom-control-input:disabled ~ .custom-control-label {
        opacity: 1;
    }

    #modal_detail .detail-status-card .custom-switch .custom-control-input:disabled ~ .custom-control-label::before {
        opacity: .65;
    }

    #modal_detail .detail-status-card .custom-switch .custom-control-input:disabled:checked ~ .custom-control-label::before {
        background-color: #2057a3;
        border-color: #2057a3;
        opacity: 1;
    }

    #modal_detail .detail-price-comparison > .row > [class*="col-md-"] {
        background: #fff;
        border: 1px solid #cfd6e3;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        margin-bottom: 1rem;
        padding: 0 1rem 1rem;
    }

    #modal_detail .detail-price-comparison > .row > [class*="col-md-"] > .divider:first-child {
        background: #f8fafc;
        border: 1px solid #edf0f2;
        border-radius: 8px 8px 0 0;
        margin: 0 -1rem 1rem;
        padding: .75rem 1rem;
    }

    #modal_detail .detail-production-dashboard {
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(220px, 1.4fr) repeat(4, minmax(120px, 1fr));
        margin-bottom: 1rem;
    }

    #modal_detail .detail-progress-card,
    #modal_detail .detail-mini-card {
        padding: 1rem;
    }

    #modal_detail .detail-progress-number {
        color: #003b78;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    #modal_detail .detail-mini-label {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    #modal_detail .detail-mini-value {
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
        margin-top: .35rem;
        overflow-wrap: anywhere;
    }

    #modal_detail .foto-container {
        min-height: 0;
    }

    #modal_detail [id^="dt-list_"],
    #modal_detail #list_rab_dokumen,
    #modal_detail #dt-file_pph42-here,
    #modal_detail #dt-file_ppn-here {
        display: grid !important;
        gap: .85rem;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    }

    #modal_detail .input-foto-container,
    #modal_detail .detail-file-tile {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        display: flex;
        flex-direction: column;
        height: auto;
        min-height: 0;
        overflow: hidden;
        position: relative;
        width: 100%;
    }

    #modal_detail .input-foto,
    #modal_detail .detail-file-preview {
        background: #f3f6fb;
        border-bottom: 1px solid #e5eaf2;
        display: block;
        min-height: 128px;
        position: relative;
        width: 100%;
    }

    #modal_detail .input-foto img,
    #modal_detail .detail-file-preview img,
    #modal_detail .detail-file-preview embed {
        display: block;
        height: 128px;
        object-fit: cover;
        width: 100%;
    }

    #modal_detail .detail-file-icon {
        align-items: center;
        color: #2056a4;
        display: flex;
        font-size: 2rem;
        height: 128px;
        justify-content: center;
        width: 100%;
    }

    #modal_detail .detail-file-body,
    #modal_detail .input-foto-container .detail-file-body {
        padding: .75rem;
    }

    #modal_detail .detail-file-title {
        color: #111827;
        font-size: .9rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: .35rem;
        overflow-wrap: anywhere;
    }

    #modal_detail .detail-file-meta {
        color: #667085;
        font-size: .78rem;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #modal_detail .detail-file-action {
        align-items: center;
        display: flex;
        gap: .45rem;
        justify-content: space-between;
        margin-top: .75rem;
    }

    #modal_detail .detail-file-action .btn,
    #modal_detail .input-foto-container .btn {
        border-radius: 6px;
        font-size: .78rem;
        padding: .35rem .65rem;
    }

    .dark-layout #modal_detail .modal-header,
    .dark-layout #modal_detail .detail-consumer-card .card-header,
    .dark-layout #modal_detail .detail-tabs-card .card-body,
    .dark-layout #modal_detail .card,
    .dark-layout #modal_detail .detail-summary-card,
    .dark-layout #modal_detail .detail-metric-card,
    .dark-layout #modal_detail .detail-accordion-card,
    .dark-layout #modal_detail .detail-progress-card,
    .dark-layout #modal_detail .detail-mini-card,
    .dark-layout #modal_detail .detail-accordion-toggle,
    .dark-layout #modal_detail .input-foto-container,
    .dark-layout #modal_detail .detail-file-tile {
        background: #283046;
        border-color: rgba(255, 255, 255, .08);
    }

    .dark-layout #modal_detail .modal-title,
    .dark-layout #modal_detail .detail-consumer-card span,
    .dark-layout #modal_detail .info-value,
    .dark-layout #modal_detail .divider .divider-text,
    .dark-layout #modal_detail .detail-metric-total,
    .dark-layout #modal_detail .detail-metric-value,
    .dark-layout #modal_detail .detail-accordion-toggle,
    .dark-layout #modal_detail .detail-mini-value,
    .dark-layout #modal_detail .detail-file-title,
    .dark-layout #modal_detail .detail-info-value,
    .dark-layout #modal_detail .detail-status-card-title,
    .dark-layout #modal_detail .detail-section-title {
        color: #f8fafc;
    }

    .dark-layout #modal_detail .detail-highlight-box {
        background: rgba(32, 86, 164, .25);
    }

    .dark-layout #modal_detail .detail-section-divider {
        border-top-color: rgba(255, 255, 255, .08);
    }

    .dark-layout #modal_detail .modal-body,
    .dark-layout #modal_detail .info-row,
    .dark-layout #modal_detail .form-control:disabled,
    .dark-layout #modal_detail .form-control[readonly] {
        background: #1f2937 !important;
    }

    .dark-layout #modal_detail .detail-hero-card {
        background: linear-gradient(145deg, #2056a4 0%, #1f7a8c 100%);
    }

    .dark-layout #modal_detail .detail-price-card,
    .dark-layout #modal_detail .detail-meta-card {
        background: rgba(255, 255, 255, 0.94);
        color: #111827;
    }

    .dark-layout #modal_detail .detail-price-card h5,
    .dark-layout #modal_detail .detail-meta-card h6 {
        color: #374151;
    }

    .produksi-jalan-history-card {
        border: 1px solid #edf0f2;
        border-radius: 8px;
        padding: 1rem;
    }

    .produksi-jalan-timeline {
        display: flex;
        flex-direction: column;
        gap: .9rem;
        margin: 0;
        padding: 0;
    }

    .produksi-jalan-timeline-item {
        border-left: 3px solid #2056a4;
        padding-left: .9rem;
        position: relative;
    }

    .produksi-jalan-timeline-item::before {
        background: #2056a4;
        border: 3px solid #fff;
        border-radius: 999px;
        box-shadow: 0 0 0 2px rgba(32, 86, 164, .18);
        content: "";
        height: 13px;
        left: -8px;
        position: absolute;
        top: .2rem;
        width: 13px;
    }

    .produksi-jalan-timeline-title {
        color: #003b78;
        font-weight: 800;
    }

    .produksi-jalan-timeline-meta {
        color: #6b7280;
        font-size: .78rem;
    }

    .produksi-jalan-timeline-photo {
        height: 72px;
        object-fit: cover;
        width: 96px;
    }

    .dark-layout .produksi-jalan-history-card {
        background: #283046;
        border-color: rgba(255, 255, 255, .08);
    }

    .dark-layout .produksi-jalan-timeline-title {
        color: #f8fafc;
    }
