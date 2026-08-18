<style>
/* Sidebar Sticky Layout & Detail Hero Card (SIGAPP Standard) */
#modal_tiket_masalah .detail-kavling-sidebar {
    position: sticky;
    top: 0;
    z-index: 10;
}

#modal_tiket_masalah .detail-hero-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(32, 87, 163, 0.18);
    background: #2057a3;
    color: #ffffff;
    border: none;
}

#modal_tiket_masalah .detail-hero-card .hero-project-title {
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #ffffff;
}

#modal_tiket_masalah .detail-hero-card .hero-meta-item {
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 4px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    color: #e2e8f0;
}

#modal_tiket_masalah .detail-hero-card .hero-meta-item i {
    font-size: 0.85rem;
    margin-top: 3px;
    color: #93c5fd;
    min-width: 14px;
}

#modal_tiket_masalah .detail-hero-card .progress-track {
    background: rgba(255, 255, 255, 0.2);
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
}

#modal_tiket_masalah .detail-hero-card .progress-fill {
    background: #28c76f;
    height: 100%;
    border-radius: 4px;
    transition: width 0.4s ease;
}

/* List Tiket Items (Gambar 1 style) */
.tm-list-card {
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    transition: all 0.2s ease-in-out;
    position: relative;
    overflow: hidden;
}
.tm-list-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}
.tm-list-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 4px;
    background: #cbd5e1;
}
.tm-list-card.prio-urgent::before { background: #ea5455; }
.tm-list-card.prio-medium::before { background: #ff9f43; }
.tm-list-card.prio-normal::before { background: #7367f0; }
.tm-list-card.prio-low::before { background: #b8c2cc; }

.badge-prio {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}
.badge-prio-urgent { background-color: #ea5455; color: #fff; }
.badge-prio-medium { background-color: #ff9f43; color: #fff; }
.badge-prio-normal { background-color: #e2e8f0; color: #475569; }
.badge-prio-low { background-color: #f1f5f9; color: #64748b; }

.badge-status-pill {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-transform: uppercase;
}
.badge-status-pill .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.badge-status-selesai { background-color: #d1e7dd; color: #0f5132; }
.badge-status-selesai .dot { background-color: #198754; }

.badge-status-proses { background-color: #cff4fc; color: #055160; }
.badge-status-proses .dot { background-color: #0dcaf0; }

.badge-status-draft { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
.badge-status-draft .dot { background-color: #ffc107; }
.badge-status-dibuat { background-color: #e2e3e5; color: #41464b; }
.badge-status-dibuat .dot { background-color: #6c757d; }

.badge-status-hold { background-color: #fff3cd; color: #664d03; }
.badge-status-hold .dot { background-color: #ffc107; }

.badge-status-batal { background-color: #f8d7da; color: #842029; }
.badge-status-batal .dot { background-color: #dc3545; }

.badge-meta {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Detail View Sidebar & Layout (Gambar 2 style) */
.tm-sidebar-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px;
}
.tm-detail-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
}
.tm-detail-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.tm-detail-val {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1e293b;
}

.avatar-circle {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #2057a3;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}

.img-thumb-grid {
    width: 72px;
    height: 72px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease;
}
.img-thumb-grid:hover {
    transform: scale(1.05);
}

/* Vertical Timeline (Gambar 2 Timeline) */
.tm-timeline {
    position: relative;
    padding-left: 24px;
}
.tm-timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 10px;
    bottom: 10px;
    width: 2px;
    background: #e2e8f0;
}
.tm-timeline-item {
    position: relative;
    margin-bottom: 24px;
}
.tm-timeline-item:last-child {
    margin-bottom: 0;
}
.tm-timeline-dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #0d9488;
    border: 3px solid #ffffff;
    box-shadow: 0 0 0 2px #0d9488;
}
.tm-timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.tm-timeline-user {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
}
.tm-timeline-time {
    font-size: 0.78rem;
    color: #64748b;
}
.tm-timeline-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.tm-status-change-pill {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
}

/* Drag & Drop Zone & Photo Preview Styling */
.drag-drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.drag-drop-zone:hover, .drag-drop-zone.dragover {
    border-color: #2057a3;
    background: #f0f7ff;
}
.upload-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.upload-preview-item {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.upload-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.upload-preview-item .remove-preview-btn {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
}
</style>
