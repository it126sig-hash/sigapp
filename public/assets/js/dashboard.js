/**
 * dashboard.js
 * Logika Frontend & UI Modul Home / Dashboard Role-Aware SIGAPP
 *
 * Standar: sigapp-ui-acuan (Vanilla/jQuery Module, Role-Aware Cards & Panels, Clickable Alerts)
 */

(function($) {
    'use strict';

    // Konfigurasi dari backend
    const config = window.DASHBOARD_CONFIG || {};
    const baseUrl = config.baseUrl || (typeof base_url !== 'undefined' ? base_url : '');
    const csrfName = config.csrfName || (typeof window.csrfName !== 'undefined' ? window.csrfName : 'csrf_test_name');
    let csrfHash = config.csrfHash || (typeof window.csrfHash !== 'undefined' ? window.csrfHash : '');
    const roleContext = config.roleContext || {
        can_view_finance: false,
        can_switch_department: false,
        default_tab: 'operasional',
        visible_tabs: ['operasional']
    };

    // Helper Tanggal & Periode
    function getFirstDate(m = 0) {
        const today = new Date();
        let year = today.getFullYear();
        let month = (today.getMonth() + 1) - m;
        if (month <= 0) {
            month = 12 + month;
            year = year - 1;
        }
        return `${year}-${month.toString().padStart(2, '0')}-01`;
    }

    function getLastDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth() + 1;
        const lastDay = new Date(year, month, 0).getDate();
        return `${year}-${month.toString().padStart(2, '0')}-${lastDay.toString().padStart(2, '0')}`;
    }

    // Helper Format
    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function formatRupiah(value) {
        return 'Rp ' + formatNumber(Math.round(Number(value || 0)));
    }

    function formatPercent(value) {
        return Number(value || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 1
        });
    }

    function formatDatetimeIndo(datetimeString) {
        if (!datetimeString) return '-';
        const str = String(datetimeString).replace('T', ' ').trim();
        const parts = str.split(' ');
        const dateParts = (parts[0] || '').split('-');
        if (dateParts.length !== 3) return str;

        const year = dateParts[0];
        const monthIndex = parseInt(dateParts[1], 10) - 1;
        const day = parseInt(dateParts[2], 10).toString().padStart(2, '0');

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        const monthName = monthNames[monthIndex] || dateParts[1];

        let timeStr = '';
        if (parts[1]) {
            const timeParts = parts[1].split(':');
            if (timeParts.length >= 2) {
                timeStr = `, ${timeParts[0].padStart(2, '0')}:${timeParts[1].padStart(2, '0')}`;
            }
        }

        return `${day} ${monthName} ${year}${timeStr}`;
    }

    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    function refreshFeathers() {
        if (window.feather) {
            window.feather.replace({ width: 14, height: 14 });
        }
    }

    // State Periode
    let sdate = getFirstDate(0);
    let edate = getLastDate();
    let thn = new Date().getFullYear().toString();
    let aktStart = 0;
    let aktIsLoading = false;

    // Cache Chart Kavling
    let bookingKavlingByMonth = Array.from({ length: 12 }, () => []);
    let akadKavlingByMonth = Array.from({ length: 12 }, () => []);

    // --------------------------------------------------------------------------
    // 1. Render Project Stock & Target Strip
    // --------------------------------------------------------------------------
    function renderProjectStockAndTarget(summary, target) {
        summary = summary || {};
        target = target || {};

        $('#stock-val-total').text(formatNumber(summary.total_kavling));
        $('#stock-val-tersedia').text(formatNumber(summary.kavling_tersedia));
        $('#stock-val-booking').text(formatNumber(summary.kavling_booking));
        $('#stock-val-akad').text(formatNumber(summary.kavling_akad));
        $('#stock-val-lunas').text(formatNumber(summary.kavling_lunas));
        $('#stock-val-batal').text(formatNumber(summary.kavling_batal));

        // Target Tahunan
        const targetTahun = target.tahun || thn;
        const targetKavling = Number(target.target_kavling || 0);
        const realBooking = Number(target.realisasi_booking || 0);
        const realAkad = Number(target.realisasi_akad || 0);
        const persenBooking = Number(target.persen_booking || 0);
        const persenAkad = Number(target.persen_akad || 0);

        $('#target-tahun-label').text(targetTahun);
        $('#target-total-kavling').text(formatNumber(targetKavling));

        $('#target-real-booking').text(formatNumber(realBooking));
        $('#target-pct-booking').text(formatPercent(persenBooking) + '%');
        $('#target-bar-booking').css('width', Math.min(100, Math.max(0, persenBooking)) + '%');

        $('#target-real-akad').text(formatNumber(realAkad));
        $('#target-pct-akad').text(formatPercent(persenAkad) + '%');
        $('#target-bar-akad').css('width', Math.min(100, Math.max(0, persenAkad)) + '%');
    }

    // --------------------------------------------------------------------------
    // 2. Render Action Queue (Tugas Saya & Menunggu Persetujuan)
    // --------------------------------------------------------------------------
    function renderActionQueue(actionQueue) {
        const container = $('#dashboard-action-queue-section');
        const listContainer = $('#dashboard-action-queue-list');

        if (!actionQueue || actionQueue.length === 0) {
            container.hide();
            listContainer.empty();
            return;
        }

        let html = '';
        $.each(actionQueue, function(i, item) {
            const severity = ['danger', 'warning', 'info'].includes(item.severity) ? item.severity : 'info';
            const url = item.url || 'javascript:void(0);';

            html += `
                <a href="${escapeHtml(url)}" class="action-queue-card severity-${severity}">
                    <div class="action-queue-head">
                        <span class="action-queue-title">${escapeHtml(item.title)}</span>
                        <span class="action-queue-badge">${formatNumber(item.count)}</span>
                    </div>
                    <div class="action-queue-desc">${escapeHtml(item.description)}</div>
                    <div class="action-queue-foot">
                        <span>Buka sekarang</span>
                        <i data-feather="arrow-right"></i>
                    </div>
                </a>
            `;
        });

        listContainer.html(html);
        container.show();
        refreshFeathers();
    }

    // --------------------------------------------------------------------------
    // 3. Render Role-Aware Primary KPI Cards
    // --------------------------------------------------------------------------
    function renderRoleKpiCards(r) {
        const summary = r.summary || {};
        const finance = r.finance || {};
        const production = r.production || {};
        const departments = r.departments || {};
        const grid = $('#dashboard-primary-kpi-grid');

        const canViewFinance = Boolean(roleContext.can_view_finance && r.finance !== null);
        const dept = roleContext.default_tab || 'operasional';

        const prodOngoing = Number(production.pembangunan_berjalan || 0);
        const prodTelat = Number(r.pembangunan_telat || production.bangunan_telat || 0);
        const prodSelesai = Number(r.pembangunan_selesai || production.bangunan_selesai || 0);

        let html = '';

        if (canViewFinance) {
            // Mode Eksekutif / Keuangan: Tampilkan Hasil Akad & Tagihan Keuangan
            const ha = r.hasil_akad || {};
            const overdueCount = Number(finance.tagihan_lewat_tempo || 0);

            html = `
                <!-- Card 1: Hasil Akad -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}keuangan/hasil-akad/list'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Perhitungan Hasil Akad</div>
                        <div class="kpi-icon-badge green"><i data-feather="dollar-sign"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value" style="font-size:1.5rem">${formatRupiah(ha.total_hasil_akad)}</div>
                    </div>
                    <div class="kpi-stats-row mt-1" style="flex-direction: column; gap: 0.4rem;">
                        <div class="d-flex justify-content-between w-100">
                            <span>Pengajuan:</span>
                            <strong>${formatRupiah(ha.pengajuan_pencairan)}</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah Cair:</span>
                            <strong class="text-success">${formatRupiah(ha.sudah_cair)}</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Sisa Cair:</span>
                            <strong class="text-danger">${formatRupiah(ha.sisa_cair)}</strong>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Sales Periode -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Sales Periode</div>
                        <div class="kpi-icon-badge amber"><i data-feather="tag"></i></div>
                    </div>
                    <div class="sales-kpi-grid">
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Booking</span>
                            <span class="sales-kpi-val text-primary" id="st_booking">${formatNumber(r.booking)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Wawancara</span>
                            <span class="sales-kpi-val text-info" id="st_wawancara">${formatNumber(r.wawancara || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">SP3K</span>
                            <span class="sales-kpi-val text-warning" id="st_sp3k">${formatNumber(r.sp3k || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Batal</span>
                            <span class="sales-kpi-val text-danger" id="st_batal">${formatNumber(r.batal || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Akad Indent</span>
                            <span class="sales-kpi-val text-secondary" id="st_akad_indent">${formatNumber(r.akad_indent || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Akad AJB</span>
                            <span class="sales-kpi-val text-success" id="st_akad_ajb">${formatNumber(r.akad_ajb || 0)}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Keuangan Tagihan -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}tagihan/list'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Keuangan</div>
                        <div class="kpi-icon-badge slate"><i data-feather="credit-card"></i></div>
                    </div>
                    <div class="kpi-card-caption" style="margin-top:.4rem">Total Belum Dibayar</div>
                    <div class="kpi-value-row">
                        <div class="kpi-value" style="font-size:1.5rem">${formatRupiah(finance.nominal_belum_bayar)}</div>
                    </div>
                    <div class="kpi-alert-pill ${overdueCount === 0 ? 'ok' : ''}">
                        <i data-feather="${overdueCount === 0 ? 'check-circle' : 'alert-circle'}"></i>
                        <span>${formatNumber(overdueCount)} overdue</span>
                    </div>
                    <div class="kpi-card-caption">Masuk periode: <strong>${formatRupiah(finance.pembayaran_masuk)}</strong></div>
                </div>

                <!-- Card 4: Produksi Lapangan -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Produksi Lapangan</div>
                        <div class="kpi-icon-badge blue"><i data-feather="tool"></i></div>
                    </div>
                    <div class="prod-kpi-grid">
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot blue"></span>Sedang Dibangun</div>
                            <div class="prod-kpi-val text-primary" id="dash_prod_active">${formatNumber(prodOngoing)}</div>
                        </div>
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot red"></span>Terlambat</div>
                            <div class="prod-kpi-val text-danger" id="st_telat">${formatNumber(prodTelat)}</div>
                        </div>
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot green"></span>100% Selesai</div>
                            <div class="prod-kpi-val text-success" id="st_100persen">${formatNumber(prodSelesai)}</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (dept === 'produksi') {
            // Mode Produksi / Lapangan (Non-Finance)
            const prodDept = departments.produksi || {};

            html = `
                <!-- Card 1: Pembangunan Berjalan -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Pembangunan Berjalan</div>
                        <div class="kpi-icon-badge blue"><i data-feather="home"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value">${formatNumber(production.pembangunan_berjalan)}</div>
                        <div class="kpi-unit">unit</div>
                    </div>
                    <div class="kpi-progress">
                        <div class="kpi-progress-bar blue" style="width: ${Math.min(100, Number(production.progres_rata_rata || 0))}%;"></div>
                    </div>
                    <div class="kpi-stats-row">
                        <div>Progres rata-rata: <strong>${formatPercent(production.progres_rata_rata)}%</strong></div>
                    </div>
                    <div class="kpi-card-caption">Total diproses dalam produksi: <strong>${formatNumber(production.total_produksi)}</strong> unit</div>
                </div>

                <!-- Card 2: Menunggu Bangun -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Menunggu Bangun (SPK)</div>
                        <div class="kpi-icon-badge amber"><i data-feather="clock"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value">${formatNumber(prodDept.menunggu_bangun)}</div>
                        <div class="kpi-unit">kavling</div>
                    </div>
                    <div class="kpi-card-caption">Sudah ada perintah bangun namun belum masuk progres lapangan</div>
                </div>

                <!-- Card 3: Selesai 100% & Ketepatan Waktu -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Selesai 100% & Keterlambatan</div>
                        <div class="kpi-icon-badge green"><i data-feather="check-circle"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-success">${formatNumber(production.bangunan_selesai)}</div>
                        <div class="kpi-unit">selesai</div>
                    </div>
                    <div class="kpi-stats-row">
                        <div><span class="kpi-dot red"></span><strong>${formatNumber(production.bangunan_telat)}</strong>unit terlambat</div>
                    </div>
                    <div class="kpi-card-caption">Kavling progres 100%: <strong>${formatNumber(prodDept.progres_100)}</strong></div>
                </div>

                <!-- Card 4: Utilitas & Komplain -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Utilitas & Komplain</div>
                        <div class="kpi-icon-badge teal"><i data-feather="zap"></i></div>
                    </div>
                    <div class="kpi-stats-row mt-1" style="flex-direction: column; gap: 0.4rem;">
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah SLF:</span>
                            <strong>${formatNumber(prodDept.sudah_slf)} unit</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah PLN:</span>
                            <strong>${formatNumber(prodDept.sudah_listrik)} unit</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah PDAM/Air:</span>
                            <strong>${formatNumber(prodDept.sudah_air)} unit</strong>
                        </div>
                    </div>
                    <div class="kpi-card-caption">Komplain aktif: <strong class="text-danger">${formatNumber(production.komplain_aktif)}</strong></div>
                </div>
            `;
        } else if (dept === 'legal') {
            // Mode Legalitas & Sertifikat (Non-Finance)
            const legalDept = departments.legal || {};

            html = `
                <!-- Card 1: Unit Terjual -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Unit Membutuhkan Legalitas</div>
                        <div class="kpi-icon-badge blue"><i data-feather="file-text"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value">${formatNumber(legalDept.total_unit_terjual)}</div>
                        <div class="kpi-unit">terjual</div>
                    </div>
                    <div class="kpi-card-caption">Belum diproses legal: <strong class="text-danger">${formatNumber(legalDept.belum_ada_legal)}</strong> unit</div>
                </div>

                <!-- Card 2: Split HGB -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Sertifikat Split HGB</div>
                        <div class="kpi-icon-badge green"><i data-feather="layers"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-success">${formatNumber(legalDept.sudah_split_hgb)}</div>
                        <div class="kpi-unit">sudah split</div>
                    </div>
                    <div class="kpi-card-caption">Balik nama: <strong>${formatNumber(legalDept.sudah_balik_nama)}</strong> &middot; AJB: <strong>${formatNumber(legalDept.sudah_ajb)}</strong></div>
                </div>

                <!-- Card 3: PBG & PBB Pecah -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">PBG & PBB Pecah</div>
                        <div class="kpi-icon-badge teal"><i data-feather="shield"></i></div>
                    </div>
                    <div class="kpi-stats-row mt-1" style="flex-direction: column; gap: 0.4rem;">
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah Terbit PBG:</span>
                            <strong class="text-success">${formatNumber(legalDept.sudah_pbg)} unit</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Sudah Pecah PBB:</span>
                            <strong class="text-info">${formatNumber(legalDept.sudah_pbb_pecah)} unit</strong>
                        </div>
                    </div>
                </div>

                <!-- Card 4: BPHTB Monitoring -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Status Pajak BPHTB</div>
                        <div class="kpi-icon-badge amber"><i data-feather="alert-triangle"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-warning">${formatNumber(legalDept.bphtb_jatuh_tempo)}</div>
                        <div class="kpi-unit">jatuh tempo</div>
                    </div>
                    <div class="kpi-card-caption">BPHTB Tervalidasi: <strong class="text-success">${formatNumber(legalDept.bphtb_valid)}</strong></div>
                </div>
            `;
        } else if (dept === 'planning') {
            // Mode Planning & Master Data (Non-Finance)
            const planDept = departments.planning || {};
            const req = planDept.kavling_request || {};
            const infra = planDept.infrastruktur || {};

            html = `
                <!-- Card 1: Total Kavling Proyek -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}siteplan/view'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Total Kavling Siteplan</div>
                        <div class="kpi-icon-badge blue"><i data-feather="map"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value">${formatNumber(summary.total_kavling)}</div>
                        <div class="kpi-unit">kavling</div>
                    </div>
                    <div class="kpi-stats-row">
                        <div>Tersedia: <strong class="text-success">${formatNumber(summary.kavling_tersedia)}</strong></div>
                        <div>Terjual: <strong>${formatNumber(Number(summary.kavling_booking || 0) + Number(summary.kavling_akad || 0))}</strong></div>
                    </div>
                </div>

                <!-- Card 2: Permohonan Kavling -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}siteplan/view'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Permohonan Kavling</div>
                        <div class="kpi-icon-badge amber"><i data-feather="edit-3"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-warning">${formatNumber(req.pending_count)}</div>
                        <div class="kpi-unit">pending</div>
                    </div>
                    <div class="kpi-stats-row">
                        <div>Disetujui: <strong class="text-success">${formatNumber(req.approved_count)}</strong></div>
                        <div>Ditolak: <strong class="text-danger">${formatNumber(req.rejected_count)}</strong></div>
                    </div>
                </div>

                <!-- Card 3: Pemetaan Koordinat -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}siteplan/view'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Pemetaan Koordinat</div>
                        <div class="kpi-icon-badge green"><i data-feather="compass"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value">${formatNumber(infra.kavling_terpetakan)}</div>
                        <div class="kpi-unit">terpetakan</div>
                    </div>
                    <div class="kpi-card-caption">Belum dipetakan: <strong class="${infra.kavling_belum_terpetakan > 0 ? 'text-danger' : 'text-success'}">${formatNumber(infra.kavling_belum_terpetakan)}</strong></div>
                </div>

                <!-- Card 4: Master Data Infrastruktur -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}siteplan/view'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Infrastruktur Master</div>
                        <div class="kpi-icon-badge teal"><i data-feather="grid"></i></div>
                    </div>
                    <div class="kpi-stats-row mt-1" style="flex-direction: column; gap: 0.4rem;">
                        <div class="d-flex justify-content-between w-100">
                            <span>Total Cluster:</span>
                            <strong>${formatNumber(infra.total_cluster)}</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Total Ruas Jalan:</span>
                            <strong>${formatNumber(infra.total_jalan)}</strong>
                        </div>
                        <div class="d-flex justify-content-between w-100">
                            <span>Tipe Bangunan:</span>
                            <strong>${formatNumber(infra.total_tipe)}</strong>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // Mode Operasional Umum (Non-Finance)
            html = `
                <!-- Card 1: Sales Periode -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Sales Periode</div>
                        <div class="kpi-icon-badge amber"><i data-feather="tag"></i></div>
                    </div>
                    <div class="sales-kpi-grid">
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Booking</span>
                            <span class="sales-kpi-val text-primary" id="st_booking">${formatNumber(r.booking)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Wawancara</span>
                            <span class="sales-kpi-val text-info" id="st_wawancara">${formatNumber(r.wawancara || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">SP3K</span>
                            <span class="sales-kpi-val text-warning" id="st_sp3k">${formatNumber(r.sp3k || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Batal</span>
                            <span class="sales-kpi-val text-danger" id="st_batal">${formatNumber(r.batal || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Akad Indent</span>
                            <span class="sales-kpi-val text-secondary" id="st_akad_indent">${formatNumber(r.akad_indent || 0)}</span>
                        </div>
                        <div class="sales-kpi-item">
                            <span class="sales-kpi-label">Akad AJB</span>
                            <span class="sales-kpi-val text-success" id="st_akad_ajb">${formatNumber(r.akad_ajb || 0)}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Produksi Lapangan -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Produksi Lapangan</div>
                        <div class="kpi-icon-badge blue"><i data-feather="tool"></i></div>
                    </div>
                    <div class="prod-kpi-grid">
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot blue"></span>Sedang Dibangun</div>
                            <div class="prod-kpi-val text-primary" id="dash_prod_active">${formatNumber(prodOngoing)}</div>
                        </div>
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot red"></span>Terlambat</div>
                            <div class="prod-kpi-val text-danger" id="st_telat">${formatNumber(prodTelat)}</div>
                        </div>
                        <div class="prod-kpi-item">
                            <div class="prod-kpi-label"><span class="kpi-dot green"></span>100% Selesai</div>
                            <div class="prod-kpi-val text-success" id="st_100persen">${formatNumber(prodSelesai)}</div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Stok Kavling -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Ketersediaan Unit</div>
                        <div class="kpi-icon-badge green"><i data-feather="home"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-success">${formatNumber(summary.kavling_tersedia)}</div>
                        <div class="kpi-unit">tersedia</div>
                    </div>
                    <div class="kpi-card-caption">Total kavling proyek: <strong>${formatNumber(summary.total_kavling)}</strong></div>
                </div>

                <!-- Card 4: Bangunan Selesai 100% -->
                <div class="dashboard-kpi-card cursor-pointer" onclick="window.location.href='${baseUrl}list-kavling'">
                    <div class="kpi-card-head">
                        <div class="kpi-label">Bangunan Selesai 100%</div>
                        <div class="kpi-icon-badge teal"><i data-feather="check-circle"></i></div>
                    </div>
                    <div class="kpi-value-row">
                        <div class="kpi-value text-info">${formatNumber(production.bangunan_selesai)}</div>
                        <div class="kpi-unit">unit</div>
                    </div>
                    <div class="kpi-card-caption">Komplain aktif: <strong class="text-danger">${formatNumber(production.komplain_aktif)}</strong></div>
                </div>
            `;
        }

        grid.html(html);
        refreshFeathers();
    }

    // --------------------------------------------------------------------------
    // 4. Render Actionable Alerts (Clickable Links)
    // --------------------------------------------------------------------------
    function renderDashboardAlerts(alerts) {
        const container = $('#dashboard-alerts');

        if (!alerts || !alerts.length) {
            container.html(`
                <div class="dashboard-alert-item">
                    <div class="dashboard-alert-head">
                        <span class="dashboard-alert-label">Tidak ada alert mendesak</span>
                        <span class="dashboard-alert-value text-success">0</span>
                    </div>
                    <div class="dashboard-alert-desc">Seluruh operasional dan administrasi berjalan lancar</div>
                </div>
            `);
            return;
        }

        let html = '';
        $.each(alerts, function(i, item) {
            const type = ['danger', 'warning', 'info'].includes(item.type) ? item.type : 'info';
            const url = item.url || 'javascript:void(0);';

            html += `
                <a href="${escapeHtml(url)}" target="_self" class="dashboard-alert-item dashboard-alert-${type}">
                    <div class="dashboard-alert-head">
                        <span class="dashboard-alert-label">${escapeHtml(item.label)}</span>
                        <div class="d-flex align-items-center">
                            <span class="dashboard-alert-value">${formatNumber(item.value)}</span>
                            <i data-feather="chevron-right" class="dashboard-alert-chevron"></i>
                        </div>
                    </div>
                    <div class="dashboard-alert-desc">${escapeHtml(item.description)}</div>
                </a>
            `;
        });

        container.html(html);
        refreshFeathers();
    }

    // --------------------------------------------------------------------------
    // 5. Render Department Workspace Panels
    // --------------------------------------------------------------------------
    function renderDepartmentPanels(departments, finance, production) {
        departments = departments || {};
        finance = finance || {};
        production = production || {};

        // 5.1 MKDT & Sales Panel
        const mkdt = departments.mkdt_sales || {};
        $('#mkdt-funnel-nup').text(formatNumber(mkdt.nup_aktif));
        $('#mkdt-funnel-booking').text(formatNumber(mkdt.booking_aktif));
        $('#mkdt-funnel-wawancara').text(formatNumber(mkdt.sudah_wawancara));
        $('#mkdt-funnel-sp3k').text(formatNumber(mkdt.sudah_sp3k));
        $('#mkdt-funnel-spk').text(formatNumber(mkdt.sudah_perintah_bangun));
        $('#mkdt-funnel-akad').text(formatNumber(mkdt.sudah_akad));

        $('#mkdt-count-subsidi').text(formatNumber(mkdt.subsidi_count));
        $('#mkdt-count-komersil').text(formatNumber(mkdt.komersil_count));
        $('#mkdt-count-kpr').text(formatNumber(mkdt.kpr_count));
        $('#mkdt-count-tunai').text(formatNumber(mkdt.tunai_count));

        // 5.2 Produksi Panel
        const prod = departments.produksi || {};
        $('#prod-stage-0').text(formatNumber(prod.progres_0));
        $('#prod-stage-25').text(formatNumber(prod.progres_25));
        $('#prod-stage-50').text(formatNumber(prod.progres_50));
        $('#prod-stage-75').text(formatNumber(prod.progres_75));
        $('#prod-stage-100').text(formatNumber(prod.progres_100));

        $('#prod-val-menunggu').text(formatNumber(prod.menunggu_bangun));
        $('#prod-val-telat').text(formatNumber(production.bangunan_telat));

        $('#prod-util-slf').text(formatNumber(prod.sudah_slf));
        $('#prod-util-listrik').text(formatNumber(prod.sudah_listrik));
        $('#prod-util-air').text(formatNumber(prod.sudah_air));

        // 5.3 Legal Panel
        const legal = departments.legal || {};
        $('#legal-terjual').text(formatNumber(legal.total_unit_terjual));
        $('#legal-belum').text(formatNumber(legal.belum_ada_legal));
        $('#legal-split').text(formatNumber(legal.sudah_split_hgb));
        $('#legal-baliknama').text(formatNumber(legal.sudah_balik_nama));
        $('#legal-pbg').text(formatNumber(legal.sudah_pbg));
        $('#legal-pbb').text(formatNumber(legal.sudah_pbb_pecah));
        $('#legal-ajb').text(formatNumber(legal.sudah_ajb));
        $('#legal-bphtb-valid').text(formatNumber(legal.bphtb_valid));
        $('#legal-bphtb-tempo').text(formatNumber(legal.bphtb_jatuh_tempo));

        // 5.4 Planning Panel
        const planning = departments.planning || {};
        const req = planning.kavling_request || {};
        const infra = planning.infrastruktur || {};

        $('#plan-req-pending').text(formatNumber(req.pending_count));
        $('#plan-req-approved').text(formatNumber(req.approved_count));
        $('#plan-req-rejected').text(formatNumber(req.rejected_count));
        $('#plan-req-total').text(formatNumber(req.total_request));

        $('#plan-infra-cluster').text(formatNumber(infra.total_cluster));
        $('#plan-infra-jalan').text(formatNumber(infra.total_jalan));
        $('#plan-infra-tipe').text(formatNumber(infra.total_tipe));
        $('#plan-infra-terpetakan').text(formatNumber(infra.kavling_terpetakan));
        $('#plan-infra-belum').text(formatNumber(infra.kavling_belum_terpetakan));

        // 5.5 Keuangan Panel (jika ada)
        if (roleContext.can_view_finance && finance) {
            $('#fin-nominal-unpaid').text(formatRupiah(finance.nominal_belum_bayar));
            $('#fin-tagihan-unpaid').text(formatNumber(finance.tagihan_belum_bayar));
            $('#fin-tagihan-due').text(formatNumber(finance.tagihan_jatuh_tempo));
            $('#fin-tagihan-overdue').text(formatNumber(finance.tagihan_lewat_tempo));
            $('#fin-payment-in').text(formatRupiah(finance.pembayaran_masuk));
            $('#fin-cashout-total').text(formatRupiah(finance.cashout_total));
            $('#fin-cashout-subkon-due').text(formatNumber(finance.cashout_subkon_jatuh_tempo));
            $('#fin-piutang-akad').text(formatRupiah(finance.piutang_pencairan_akad));
        }
    }

    // --------------------------------------------------------------------------
    // 6. Render Riwayat Tiket Masalah
    // --------------------------------------------------------------------------
    function renderDashboardTiketMasalah(tickets) {
        const body = $('#dashboard-tiket-masalah-body');

        if (!tickets || tickets.length === 0) {
            body.html('<tr><td colspan="5" class="text-center text-muted py-2">Belum ada tiket masalah pada proyek ini.</td></tr>');
            return;
        }

        let html = '';
        $.each(tickets, function(i, item) {
            let badgeClass = 'badge-light-secondary';
            let labelPrioritas = item.prioritas || '-';
            if (item.prioritas === 'urgent') {
                badgeClass = 'badge-light-danger';
                labelPrioritas = 'Urgent';
            } else if (item.prioritas === 'medium') {
                badgeClass = 'badge-light-warning';
                labelPrioritas = 'Medium';
            } else if (item.prioritas === 'normal') {
                badgeClass = 'badge-light-primary';
                labelPrioritas = 'Normal';
            }

            let statusClass = 'badge-light-secondary';
            let labelStatus = item.status || '-';
            if (item.status === 'selesai') {
                statusClass = 'badge-light-success';
                labelStatus = 'Selesai';
            } else if (item.status === 'dalam_proses') {
                statusClass = 'badge-light-info';
                labelStatus = 'Dalam Proses';
            } else if (item.status === 'dibuat') {
                statusClass = 'badge-light-primary';
                labelStatus = 'Dibuat';
            } else if (item.status === 'batal') {
                statusClass = 'badge-light-danger';
                labelStatus = 'Batal';
            }

            const waktu = item.updated_at ? item.updated_at : item.created_at;
            const plainKet = $('<div>').html(item.keterangan || '').text().trim();

            html += `
                <tr>
                    <td>
                        <div class="tiket-waktu-cell">
                            <span class="tiket-waktu-title">${escapeHtml(formatDatetimeIndo(waktu))}</span>
                            <span class="tiket-lokasi-sub"><i data-feather="map-pin"></i> ${escapeHtml(item.lokasi || '-')}</span>
                        </div>
                    </td>
                    <td>
                        <div class="tiket-keterangan-cell" data-id="${item.id}" data-ref-type="${escapeHtml(item.ref_type || 'kavling')}" data-ref-id="${escapeHtml(item.ref_id || 0)}" title="${escapeHtml(plainKet)}">
                            ${item.keterangan || '-'}
                        </div>
                    </td>
                    <td><span class="badge ${badgeClass}">${escapeHtml(labelPrioritas)}</span></td>
                    <td><span class="badge ${statusClass}">${escapeHtml(labelStatus)}</span></td>
                    <td class="text-center">
                        <div class="d-inline-flex" style="gap: 0.35rem;">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary btn-view-tiket" data-id="${item.id}" data-ref-type="${escapeHtml(item.ref_type || 'kavling')}" data-ref-id="${escapeHtml(item.ref_id || 0)}" title="Lihat Detail Tiket">
                                <i data-feather="eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-view-lokasi" data-id-proyek="${escapeHtml(item.id_proyek || '')}" data-id-tiket="${item.id}" data-ref-type="${escapeHtml(item.ref_type || '')}" data-ref-id="${escapeHtml(item.ref_id || '')}" data-cluster-id="${escapeHtml(item.target_cluster_id || '')}" title="Buka Lokasi di Siteplan">
                                <i data-feather="map"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        body.html(html);

        // Event Handling Tiket Masalah
        body.off('click', '.tiket-keterangan-cell, .btn-view-tiket').on('click', '.tiket-keterangan-cell, .btn-view-tiket', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const refType = $(this).data('ref-type') || 'kavling';
            const refId = $(this).data('ref-id') || 0;
            if (typeof window.tm_open_detail === 'function') {
                window.tm_open_detail(id, refType, refId);
            } else {
                console.warn('window.tm_open_detail tidak tersedia');
            }
        });

        body.off('click', '.btn-view-lokasi').on('click', '.btn-view-lokasi', function(e) {
            e.preventDefault();
            const idProyek = $(this).data('id-proyek') || (typeof window.activeProyekId === 'function' ? window.activeProyekId() : '');
            const idTiket = $(this).data('id-tiket');
            const refType = $(this).data('ref-type') || '';
            const refId = $(this).data('ref-id') || '';
            const clusterId = $(this).data('cluster-id') || '';

            const params = new URLSearchParams({
                id_proyek: idProyek,
                show_tiket: idTiket,
                ref_type: refType,
                ref_id: refId,
                target_cluster_id: clusterId
            });

            const targetUrl = baseUrl + 'siteplan/view?' + params.toString();
            window.open(targetUrl, '_blank');
        });

        refreshFeathers();
    }

    // --------------------------------------------------------------------------
    // 7. Chart Modal Handler
    // --------------------------------------------------------------------------
    function showChartKavlingModal(seriesLabel, monthLabel, list) {
        $('#modal-chart-kavling-title').text(seriesLabel + ' — ' + monthLabel);
        if (list && list.length) {
            $('#modal-chart-kavling-list').html(
                list.map(k => `<li class="kavling-pill-item">${escapeHtml(k)}</li>`).join('')
            );
        } else {
            $('#modal-chart-kavling-list').html('<li class="text-muted">Tidak ada unit kavling pada periode ini.</li>');
        }
        $('#modal-chart-kavling').modal('show');
    }

    // --------------------------------------------------------------------------
    // 8. Main AJAX: Load Dashboard
    // --------------------------------------------------------------------------
    function load_dashboard(statistik = false, aktivitas = false, chart = false) {
        const idProyek = typeof window.activeProyekId === 'function' ? window.activeProyekId() : null;

        if (!idProyek) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Belum ada proyek aktif. Pilih proyek dari navbar.', 'Perhatian!', {
                    timeOut: 3000,
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-bottom-right',
                });
            }
            return;
        }

        $.ajax({
            type: 'POST',
            url: baseUrl + 'get-dashboard',
            data: {
                [csrfName]: csrfHash,
                id_proyek: idProyek,
                statistik: statistik,
                aktivitas: aktivitas,
                chart: chart,
                sdate: sdate,
                edate: edate,
                tahun: thn
            },
            dataType: 'json',
            beforeSend: function() {
                if (statistik) {
                    $('#stock-val-total, #stock-val-tersedia, #stock-val-booking, #stock-val-akad, #stock-val-lunas, #stock-val-batal').text('-');
                    $('#dashboard-tiket-masalah-body').html('<tr><td colspan="5" class="text-center">Memuat data...</td></tr>');
                }
                if (aktivitas) {
                    $('#aktivitas-here').html('<div class="activity-empty">Memuat aktivitas...</div>');
                }
            },
            success: function(r) {
                if (r.csrfHash) csrfHash = r.csrfHash;

                if (statistik) {
                    renderProjectStockAndTarget(r.summary, r.target);
                    renderActionQueue(r.action_queue || []);
                    renderRoleKpiCards(r);
                    renderDashboardAlerts(r.alerts || []);
                    renderDepartmentPanels(r.departments, r.finance, r.production);

                    if (r.tiket_masalah) {
                        renderDashboardTiketMasalah(r.tiket_masalah.recent_tickets);
                    }
                }

                if (aktivitas) {
                    const ac = r.aktivitas || [];
                    let actHtml = '';
                    $.each(ac, function(i, v) {
                        if (typeof window.renderActivityItem === 'function') {
                            actHtml += window.renderActivityItem(v);
                        } else {
                            actHtml += `<div class="p-1 border-bottom"><small class="text-muted">${escapeHtml(v.created_at || '')}</small><p class="mb-0">${escapeHtml(v.keterangan || v.deskripsi || '')}</p></div>`;
                        }
                    });

                    if (!actHtml) {
                        actHtml = '<div class="activity-empty">Belum ada aktivitas pada proyek ini.</div>';
                    }
                    $('#aktivitas-here').html(actHtml);
                    aktStart = ac.length;
                }

                if (chart && window.myLineChart) {
                    const booking = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    const akad = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    bookingKavlingByMonth = Array.from({ length: 12 }, () => []);
                    akadKavlingByMonth = Array.from({ length: 12 }, () => []);

                    $.each(r.cbooking || [], function(i, v) {
                        const m = parseInt(v.bulan, 10) - 1;
                        if (m >= 0 && m < 12) {
                            booking[m] = Number(v.jumlah || 0);
                            bookingKavlingByMonth[m] = v.kavling || [];
                        }
                    });

                    $.each(r.cakad || [], function(i, v) {
                        const m = parseInt(v.bulan, 10) - 1;
                        if (m >= 0 && m < 12) {
                            akad[m] = Number(v.jumlah || 0);
                            akadKavlingByMonth[m] = v.kavling || [];
                        }
                    });

                    window.myLineChart.data.datasets[0].data = booking;
                    window.myLineChart.data.datasets[1].data = akad;
                    window.myLineChart.update();
                }
            },
            error: function(xhr, status, err) {
                console.error('Error load_dashboard:', err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Galat saat mengambil data dashboard dari server.', 'Terjadi Kesalahan!', {
                        timeOut: 3000,
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });
                }
            }
        });
    }

    // --------------------------------------------------------------------------
    // 9. Infinite Scroll Aktivitas
    // --------------------------------------------------------------------------
    function load_aktivitas() {
        const idProyek = typeof window.activeProyekId === 'function' ? window.activeProyekId() : null;
        if (!idProyek || aktIsLoading) return;

        aktIsLoading = true;
        $.ajax({
            url: baseUrl + 'loadaktivitas',
            method: 'GET',
            data: {
                [csrfName]: csrfHash,
                offset: aktStart,
                id_proyek: idProyek
            },
            success: function(r) {
                const ac = r.aktivitas || [];
                let actHtml = '';
                $.each(ac, function(i, v) {
                    if (typeof window.renderActivityItem === 'function') {
                        actHtml += window.renderActivityItem(v);
                    } else {
                        actHtml += `<div class="p-1 border-bottom"><small class="text-muted">${escapeHtml(v.created_at || '')}</small><p class="mb-0">${escapeHtml(v.keterangan || v.deskripsi || '')}</p></div>`;
                    }
                });

                $('#aktivitas-here').append(actHtml);
                aktStart += ac.length;
                aktIsLoading = false;
            },
            error: function() {
                aktIsLoading = false;
            }
        });
    }

    // --------------------------------------------------------------------------
    // 10. Chart.js Initialization
    // --------------------------------------------------------------------------
    function initChart() {
        const ctxEl = document.getElementById('myLineChart');
        if (!ctxEl) return;

        const chartData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Booking',
                    backgroundColor: '#d97706',
                    borderColor: '#d97706',
                    data: [],
                    fill: false,
                    tension: 0.2
                },
                {
                    label: 'Akad',
                    backgroundColor: '#2057a3',
                    borderColor: '#2057a3',
                    data: [],
                    fill: false,
                    tension: 0.2
                }
            ]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            onClick: function(evt, elements) {
                if (!elements.length) return;
                const el = elements[0];
                const seriesLabel = el.datasetIndex === 0 ? 'Booking' : 'Akad';
                const monthName = chartData.labels[el.index];
                const list = (el.datasetIndex === 0 ? bookingKavlingByMonth : akadKavlingByMonth)[el.index] || [];
                showChartKavlingModal(seriesLabel, monthName, list);
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterBody: function(items) {
                            if (!items.length) return [];
                            const it = items[0];
                            const list = (it.datasetIndex === 0 ? bookingKavlingByMonth : akadKavlingByMonth)[it.dataIndex] || [];
                            if (!list.length) return [];
                            const preview = list.slice(0, 8);
                            const lines = preview.map(k => '• ' + k);
                            if (list.length > preview.length) lines.push(`+${list.length - preview.length} lainnya`);
                            return lines;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'category',
                    title: { display: true, text: 'Bulan' }
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Unit' }
                }
            }
        };

        window.myLineChart = new Chart(ctxEl.getContext('2d'), {
            type: 'line',
            data: chartData,
            options: chartOptions
        });
    }

    // --------------------------------------------------------------------------
    // 11. Event Handlers & Document Ready
    // --------------------------------------------------------------------------
    $(document).ready(function() {
        // Init Chart
        initChart();

        // Check active project
        if (typeof window.activeProyekId === 'function' && window.activeProyekId()) {
            load_dashboard(true, true, true);
        }

        // Project label di header jika ada
        if (window.SIGAPP && window.SIGAPP.activeProyekName) {
            $('#dashboard-active-proyek').text('Proyek: ' + window.SIGAPP.activeProyekName);
        }

        // Filter Pills Event
        $('#filter-bulan').on('change', function() {
            $('#filter-statistik-pills .btn').removeClass('active');
            $(this).addClass('active');

            let val = $(this).val();
            if (!val) {
                const now = new Date();
                val = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}`;
                $(this).val(val);
            }
            const parts = val.split('-');
            const year = parts[0];
            const month = parts[1];

            sdate = `${year}-${month}-01`;
            const lastDay = new Date(year, month, 0).getDate();
            edate = `${year}-${month}-${lastDay.toString().padStart(2, '0')}`;
            thn = year;

            load_dashboard(true, false, false);
        });

        $('#filter-6bulan').on('click', function() {
            $('#filter-statistik-pills .btn').removeClass('active');
            $(this).addClass('active');

            const y = $(this).attr('data-val');
            $('#chart-judul').text('Trend Sales Tahun ' + y);
            thn = y;
            sdate = `${y}-01-01`;
            edate = `${y}-12-31`;

            load_dashboard(true, false, true);
        });

        $('#filter-1tahun').on('click', function() {
            $('#filter-statistik-pills .btn').removeClass('active');
            $(this).addClass('active');

            const y = $(this).attr('data-val');
            $('#chart-judul').text('Trend Sales Tahun ' + y);
            thn = y;
            sdate = `${y}-01-01`;
            edate = `${y}-12-31`;

            load_dashboard(true, false, true);
        });

        // Chart dropdown filter
        $('#filter-chart-tahun-sekarang').on('click', function() {
            const curY = new Date().getFullYear().toString();
            $('#chart-judul').text('Trend Sales Tahun ' + curY);
            thn = curY;
            load_dashboard(false, false, true);
        });

        $('#filter-chart-tahun-lalu').on('click', function() {
            const prevY = (new Date().getFullYear() - 1).toString();
            $('#chart-judul').text('Trend Sales Tahun ' + prevY);
            thn = prevY;
            load_dashboard(false, false, true);
        });

        // View all activity trigger
        $('#dashboard-view-all-activity').on('click', function() {
            if (typeof window.openNotificationCenter === 'function') {
                window.openNotificationCenter();
            } else if ($('#list-notif').length) {
                $('#list-notif').modal('show');
            }
        });

        // Infinite scroll aktivitas
        $('#aktivitas-body').on('scroll', function() {
            if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 10) {
                load_aktivitas();
            }
        });

        // Tab Switcher Role Context: Aktifkan default tab
        const defaultTab = roleContext.default_tab || 'operasional';
        const defaultTabBtn = $(`#tab-btn-${defaultTab}`);
        if (defaultTabBtn.length) {
            defaultTabBtn.tab('show');
        } else {
            $('#dashboard-dept-tabs a.nav-link:first').tab('show');
        }

        refreshFeathers();
    });

})(jQuery);
