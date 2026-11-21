<style>
/* ==========================================================================
   MODAL TIKET MASALAH - MODERN ARCHITECTURE & DESIGN TOKENS
   Ref: stitch_modern_layout_typography_redesign (SIGAPP QC Standard)
   ========================================================================== */

#modal_tiket_masalah .modal-dialog {
    max-width: 1280px;
    width: 95vw;
    margin: 1.5rem auto;
}

#modal_tiket_masalah .modal-content {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.85);
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* --- SYSTEM TOP BAR (QC Dark Header) --- */
.tm-system-bar {
    background: #0f172a;
    color: #cbd5e1;
    padding: 10px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 500;
    border-bottom: 1px solid #1e293b;
}
.tm-system-badge {
    background: #1e5da6;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    letter-spacing: 0.5px;
}
.tm-server-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
    font-size: 11px;
}
.tm-pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: tmPulse 2s infinite;
}
@keyframes tmPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.tm-btn-close-system {
    color: #94a3b8;
    background: transparent;
    border: none;
    padding: 4px;
    line-height: 1;
    cursor: pointer;
    border-radius: 4px;
    transition: color 0.2s;
}
.tm-btn-close-system:hover {
    color: #ffffff;
}

/* --- SUB-HEADER NAVIGATION --- */
.tm-subheader-nav {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.tm-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s;
    text-decoration: none !important;
}
.tm-btn-back:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.tm-divider-vertical {
    width: 1px;
    height: 20px;
    background: #e2e8f0;
    margin: 0 8px;
}
.tm-tkt-id {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 800;
    font-size: 1.05rem;
    color: #0f172a;
    letter-spacing: -0.3px;
}

/* --- BADGES (PRIORITAS & STATUS) --- */
.tm-badge-urgent {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 800;
    background: #fff1f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
    letter-spacing: 0.5px;
}
.tm-badge-urgent .dot-ping {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #e11d48;
    animation: tmPulseRose 1.5s infinite;
}
@keyframes tmPulseRose {
    0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(225, 29, 72, 0); }
    100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
}

.tm-badge-medium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    background: #fff7ed;
    color: #ea580c;
    border: 1px solid #fed7aa;
}
.tm-badge-normal {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.tm-badge-low {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.tm-badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
}
.tm-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.tm-status-dibuat { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.tm-status-dibuat .tm-status-dot { background: #94a3b8; }
.tm-status-proses { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.tm-status-proses .tm-status-dot { background: #0284c7; }
.tm-status-selesai { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.tm-status-selesai .tm-status-dot { background: #10b981; }
.tm-status-hold { background: #fefce8; color: #854d0e; border: 1px solid #fef08a; }
.tm-status-hold .tm-status-dot { background: #eab308; }
.tm-status-batal { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.tm-status-batal .tm-status-dot { background: #ef4444; }

/* --- LEFT SIDEBAR (BRAND BLUE GRADIENT HERO) --- */
.tm-hero-sidebar {
    background: linear-gradient(180deg, #164070 0%, #1e5da6 50%, #113257 100%);
    color: #ffffff;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-right: 1px solid #0f2b4c;
    height: 100%;
    min-height: 520px;
}
.tm-sidebar-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #93c5fd;
    margin-bottom: 4px;
    display: block;
}
.tm-sidebar-project-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.3;
    margin-bottom: 0;
}
.tm-sidebar-meta-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 18px;
}
.tm-sidebar-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(30, 93, 166, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #ffffff;
    font-size: 13px;
}
.tm-sidebar-meta-val {
    font-size: 0.88rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.35;
    margin-bottom: 0;
}
.tm-sidebar-meta-sub {
    font-size: 0.75rem;
    color: #bfdbfe;
    font-weight: 400;
    margin-top: 2px;
    display: block;
}

/* Progress Section in Sidebar */
.tm-sidebar-progress-box {
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    margin-top: 24px;
}
.tm-progress-track {
    width: 100%;
    height: 10px;
    background: rgba(11, 32, 56, 0.65);
    border-radius: 9999px;
    padding: 2px;
    border: 1px solid rgba(59, 130, 246, 0.3);
    overflow: hidden;
    margin-top: 8px;
}
.tm-progress-fill {
    background: linear-gradient(90deg, #34d399 0%, #5eead4 100%);
    height: 100%;
    border-radius: 9999px;
    transition: width 0.5s ease;
}

/* --- RIGHT MAIN CONTENT CONTAINER --- */
.tm-main-content-scroll {
    background: #f8fafc;
    padding: 24px;
    overflow-y: auto;
    max-height: calc(88vh - 120px);
}

/* --- CARD 1: DESKRIPSI MASALAH (ROSE BANNER) --- */
.tm-card-rose {
    background: #fdf3f2;
    border: 1px solid #fbd5d3;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    margin-bottom: 24px;
}
.tm-icon-rose-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #ffe4e6;
    border: 1px solid #fecdd3;
    color: #be123c;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    margin-top: 2px;
}
.tm-title-rose {
    font-size: 11px;
    font-weight: 800;
    color: #9f1d1d;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 0;
}
.tm-badge-points {
    font-size: 11px;
    font-weight: 700;
    color: #e11d48;
    background: rgba(254, 205, 211, 0.7);
    padding: 2px 10px;
    border-radius: 9999px;
}
.tm-subtitle-rose {
    font-size: 12px;
    color: #881337;
    opacity: 0.85;
    margin-top: 2px;
    margin-bottom: 12px;
}
.tm-issue-list {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}
.tm-issue-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 13px;
    line-height: 1.55;
    color: #1e293b;
    font-weight: 500;
}
.tm-issue-item:last-child {
    margin-bottom: 0;
}
.tm-issue-num {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(254, 205, 211, 0.85);
    color: #881337;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

/* --- CARD 2: LAMPIRAN & FOTO (WHITE CARD) --- */
.tm-card-white {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    margin-bottom: 24px;
}
.tm-card-header-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 14px;
    margin-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
}
.tm-doc-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 6px 14px;
}
.tm-doc-icon-box {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #fef3c7;
    color: #d97706;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

/* Main Focus Photo Box */
.tm-focus-photo-box {
    background: #0f172a;
    border-radius: 12px;
    height: 360px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.tm-focus-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.4s ease;
}
.tm-focus-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    gap: 6px;
}
.tm-focus-badge .badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #e11d48;
}
.tm-btn-see-full {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.tm-btn-see-full:hover {
    background: #0f172a;
    transform: scale(1.03);
}

/* 5-Column Thumbnail Gallery Grid */
.tm-thumb-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
    margin-top: 10px;
}
.tm-thumb-btn {
    aspect-ratio: 1 / 1;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    padding: 0;
    background: #ffffff;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}
.tm-thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tm-thumb-btn:hover {
    border-color: #1e5da6;
    transform: translateY(-1px);
}
.tm-thumb-btn.active-thumb {
    border: 2px solid #1e5da6;
    box-shadow: 0 0 0 2px rgba(30, 93, 166, 0.25);
}
.tm-thumb-more {
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
}
.tm-thumb-more:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.tm-gallery-tip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 11px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
}

/* --- CARD 3: 3-COLUMNS METADATA GRID --- */
.tm-metadata-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 768px) {
    .tm-metadata-grid {
        grid-template-columns: 1fr;
    }
}
.tm-meta-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.tm-meta-card-label {
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 10px;
    display: block;
}
.tm-avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #1e5da6;
    color: #ffffff;
    font-weight: 800;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tm-user-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    background: #f3e8ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
    margin-right: 4px;
    margin-bottom: 4px;
}

/* --- CARD 4: HISTORY PROGRESS SECTION --- */
.tm-progress-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    margin-bottom: 20px;
}
.tm-progress-accent-bar {
    width: 4px;
    height: 16px;
    background: #1e5da6;
    border-radius: 9999px;
    margin-right: 8px;
}
.tm-empty-progress {
    padding: 30px 16px;
    text-align: center;
}
.tm-empty-icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 18px;
}

/* --- MODERN SLEEK TIMELINE (PROGRESS HISTORY) --- */
.tm-timeline {
    position: relative;
    padding-left: 28px;
    margin-top: 14px;
}
.tm-timeline::before {
    content: '';
    position: absolute;
    top: 14px;
    bottom: 14px;
    left: 13px;
    width: 2px;
    background: #e2e8f0;
    border-radius: 9999px;
}
.tm-timeline-item {
    position: relative;
    margin-bottom: 12px;
}
.tm-timeline-item:last-child {
    margin-bottom: 0;
}
.tm-timeline-node {
    position: absolute;
    left: -28px;
    top: 10px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ffffff;
    border: 2px solid #1e5da6;
    color: #1e5da6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    box-shadow: 0 0 0 3px #ffffff;
    z-index: 2;
}
.tm-timeline-item.is-pinned-item .tm-timeline-node {
    border-color: #f59e0b;
    color: #b45309;
    background: #fffbeb;
}
.tm-timeline-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    transition: all 0.15s ease;
    position: relative;
}
.tm-timeline-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.tm-timeline-card.card-pinned {
    background: #fffdfa;
    border-color: #fde68a;
    border-left: 3px solid #f59e0b;
}

/* Card Header inside Timeline Card */
.tm-timeline-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}
.tm-timeline-user {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
}
.tm-timeline-time {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}
.tm-timeline-text {
    font-size: 13px;
    line-height: 1.5;
    color: #334155;
    margin-bottom: 0;
    word-break: break-word;
}

/* Status Change Tag */
.tm-status-change-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 2px 8px;
    border-radius: 6px;
    margin-top: 6px;
}

/* Compact Pin Badges & Action Buttons */
.tm-badge-pinned {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.tm-badge-req-pin {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.tm-badge-newest {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.tm-btn-pin-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1.3;
}
.tm-btn-pin-action:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
}
.tm-btn-pin-action.is-pinned {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}
.tm-btn-pin-action.is-pinned:hover {
    background: #fee2e2;
    color: #b91c1c;
}

/* Compact Photos in Timeline */
.tm-timeline-photos {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}
.tm-timeline-thumb {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
.tm-timeline-thumb:hover {
    transform: scale(1.05);
    border-color: #1e5da6;
}
.tm-timeline-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* --- FOOTER STATUS BAR --- */
.tm-audit-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 14px 24px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
}
.tm-pill-warning {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    background: #fefce8;
    color: #854d0e;
    border: 1px solid #fef08a;
}

/* --- MOBILE SPECIFIC COMPONENTS (Ref: media_1792058887602_ba7be658.png) --- */

/* 1. Mobile Hero Card (Dark Navy Top Card) */
.tm-mobile-hero-card {
    background: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 16px;
    padding: 16px;
    color: #ffffff;
    margin-bottom: 16px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
}
.tm-mobile-hero-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.tm-mobile-hero-badge {
    background: #1e3a8a;
    color: #93c5fd;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 9999px;
    border: 1px solid #2563eb;
    letter-spacing: 0.3px;
}
.tm-mobile-hero-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}
.tm-mobile-meta-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}
.tm-mobile-meta-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(30, 93, 166, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #60a5fa;
    font-size: 11px;
}
.tm-mobile-meta-label {
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #94a3b8;
    margin-bottom: 1px;
}
.tm-mobile-meta-val {
    font-size: 0.84rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.3;
}
.tm-mobile-meta-sub {
    font-size: 0.72rem;
    color: #94a3b8;
}
.tm-mobile-hero-progress {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* 2. Interactive Checklist Items in Rose Card */
.tm-notice-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #fecdd3;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 600;
    color: #e11d48;
    margin-bottom: 12px;
}
.tm-issue-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 6px 0;
    font-size: 0.84rem;
    line-height: 1.45;
    transition: all 0.2s ease;
}
.tm-issue-check {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 1.5px solid #fda4af;
    cursor: pointer;
    margin-top: 2px;
    accent-color: #e11d48;
    flex-shrink: 0;
}
.tm-issue-item.is-checked .tm-issue-text {
    text-decoration: line-through;
    opacity: 0.55;
    color: #881337;
}

/* 3. Lampiran Dokumen Card (Amber/Yellow Theme) */
.tm-card-amber {
    background: #fffbeb;
    border: 1px solid #fef08a;
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    box-shadow: 0 2px 6px rgba(234, 179, 8, 0.05);
}
.tm-doc-icon-amber {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d97706;
    font-size: 15px;
    flex-shrink: 0;
}
.tm-doc-amber-label {
    font-size: 10px;
    font-weight: 800;
    color: #b45309;
    letter-spacing: 0.8px;
    text-transform: uppercase;
}
.tm-doc-amber-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0;
}
.tm-doc-amber-action {
    color: #64748b;
    background: transparent;
    border: none;
    padding: 6px;
    border-radius: 6px;
    cursor: pointer;
    transition: color 0.15s ease;
}
.tm-doc-amber-action:hover {
    color: #0f172a;
    background: rgba(0, 0, 0, 0.04);
}

/* 4. Action Button Ambil Foto Tambahan */
.tm-btn-add-photo-mobile {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 16px;
    background: #ffffff;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    color: #1e5da6;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 10px;
}
.tm-btn-add-photo-mobile:hover {
    background: #f0f7ff;
    border-color: #1e5da6;
}

/* 5. Mobile Bottom Dual Action Buttons */
.tm-mobile-bottom-actions {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-top: 6px;
}
.tm-btn-mobile-secondary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.tm-btn-mobile-secondary:hover {
    background: #f8fafc;
    color: #0f172a;
}
.tm-btn-mobile-primary {
    flex: 1.3;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 700;
    color: #ffffff;
    background: #0f172a;
    border: 1px solid #0f172a;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
}
.tm-btn-mobile-primary:hover {
    background: #1e293b;
    border-color: #1e293b;
}

/* --- LIST TIKET CARDS --- */
.tm-list-card {
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    background: #ffffff;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    overflow: hidden;
}
.tm-list-card:hover {
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
    transform: translateY(-2px);
}
.tm-list-card.prio-urgent {
    border-left: 4px solid #e11d48 !important;
}
.tm-list-card.prio-tinggi {
    border-left: 4px solid #ea580c !important;
}
.tm-list-card.prio-sedang {
    border-left: 4px solid #f59e0b !important;
}
.tm-list-card.prio-rendah {
    border-left: 4px solid #3b82f6 !important;
}

/* ==========================================================================
   RESPONSIVE MEDIA QUERIES (@media max-width: 767.98px)
   ========================================================================== */
@media (max-width: 767.98px) {
    #modal_tiket_masalah .modal-dialog {
        margin: 0 !important;
        width: 100vw !important;
        max-width: 100% !important;
        height: 100% !important;
        min-height: 100vh !important;
    }
    #modal_tiket_masalah .modal-content {
        border-radius: 0 !important;
        min-height: 100vh !important;
        border: none !important;
    }

    /* Hide desktop hero sidebar when on mobile */
    .tm-hero-sidebar {
        display: none !important;
    }

    /* System bar compact on mobile */
    .tm-system-bar {
        padding: 8px 12px;
        font-size: 11px;
    }

    /* Subheader nav compact (Single Line Guaranteed) */
    .tm-subheader-nav {
        padding: 8px 12px !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 6px !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .tm-subheader-nav::-webkit-scrollbar {
        display: none;
    }
    .tm-btn-back {
        padding: 4px 8px !important;
        font-size: 11px !important;
        border-radius: 6px !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .tm-tkt-id {
        font-size: 0.85rem !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .tm-badge-urgent, .tm-badge-status, .tm-badge-medium, .tm-badge-normal, .tm-badge-low {
        padding: 2px 7px !important;
        font-size: 10px !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    /* Main scroll container full height minus header & footer */
    .tm-main-content-scroll {
        padding: 12px;
        max-height: calc(100vh - 125px);
    }

    /* Thumbnails grid 4-columns on mobile */
    .tm-thumb-grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 8px !important;
    }
    .tm-thumb-btn {
        height: 56px !important;
    }

    /* Metadata grid stacked 2-columns */
    .tm-metadata-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
    }
    .tm-meta-card {
        padding: 10px 12px !important;
    }

    /* Sticky Footer on Mobile */
    .tm-audit-footer {
        position: sticky;
        bottom: 0;
        z-index: 1020;
        background: #ffffff;
        padding: 8px 14px 12px 14px;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    .tm-audit-footer-status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
    }
}

/* Custom Scrollbar for Smooth Navigation */
#modal_tiket_masalah ::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
#modal_tiket_masalah ::-webkit-scrollbar-track {
    background: #f1f5f9;
}
#modal_tiket_masalah ::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
#modal_tiket_masalah ::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
