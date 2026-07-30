function siteplanActiveProyekName() {
    if (typeof dt_proyek === 'object' && dt_proyek && dt_proyek.nama_proyek) {
        return dt_proyek.nama_proyek;
    }
    if (window.SIGAPP && window.SIGAPP.activeProyekName) {
        return window.SIGAPP.activeProyekName;
    }
    return '';
}

function lihat_detail() {
        if (editdtt.length == 0) {
            Swal.fire({

                icon: 'error',
                title: "Terjadi Kesalahan.",
                text: "Tidak ada kavling yang dipilih",
                showConfirmButton: false,
            });
            return;
        }
        last_update("#last_update_legal, #last_update_mkdt, #last_update_keuangan, #last_update_prod")
        var sh = editdtt[0],
            id_kavling = sh.id.substr(3);

        // $("#fm-detail")[0].reset();
        $('#fm-detail input,#fm-detail select').val('');
        $("#tb-data-tagihan-detail").html("");

        if (sh.data.tipe == 'kavling') {
            return detail_kavling(sh, id_kavling)
        } else {
            return detail_others(sh)
        }
    }

    function detail_others(sh) {
        // alert(sh.data.tipe)
        $("#f_detail_progres_jalan").val(0)
        $(".t_luas_planning, .t_keterangan_planning, .t_luas_legal, .t_keterangan_legal, .t_luas_produksi, .t_keterangan_produksi, .r_progres").html("-")
        $("#detail_produksi_jalan_history_wrap").addClass("d-none")
        resetProduksiJalanHistoryTimeline("#detail_produksi_jalan_history")
        $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: editdtt[0].id.substr(6),
                history_limit: PRODUKSI_JALAN_HISTORY_LIMIT,
                history_offset: 0
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;

                if (r.data) {
                    let d = r.data[0],
                        progres = (d.progres) ? d.progres : 0;
                    // $(".id_kavling").val(d.id)
                    // $(".t_luas_legal, .t_luas_produksi").html("-")

                    if (d.planning_luas) {
                        $(".t_luas_planning").html(d.planning_luas + "  m&sup2  (" + d.planning_edit + ": " + format_datetime(d.planning_updated_at) + ")")
                        $(".t_keterangan_planning").html(d.planning_keterangan)
                    }

                    if (d.legal_luas) {
                        $(".t_luas_legal").html(d.legal_luas + "  m&sup2  (" + d.legal_edit + ": " + format_datetime(d.legal_updated_at) + ")")
                        $(".t_keterangan_legal").html(d.legal_keterangan)
                    }

                    if (d.produksi_luas) {
                        $(".t_luas_produksi").html(d.produksi_luas + "  m&sup2  (" + d.produksi_edit + ": " + format_datetime(d.produksi_updated_at) + ")")
                        $(".t_keterangan_produksi").html(d.produksi_keterangan)
                    }
                    $("#f_detail_progres_jalan").val(progres)
                    $(".r_progres").html(progres)
                    $("#detail_produksi_jalan_history_wrap").toggleClass("d-none", d.tipe !== "jalan")
                    renderProduksiJalanHistoryTimeline("#detail_produksi_jalan_history", r.history || [], r, false)

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



        $(".label_alamat").html("<b>" + dt_proyek.nama_proyek + "</b>" + "<br/> <span class='capitalize'>" + sh.data.tipe + "<span>: " + sh.data.nama_jalan + "");
        $('#modal_othersdetail').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    const PRODUKSI_JALAN_HISTORY_LIMIT = 5;

    function escapeProduksiJalanHistoryValue(value) {
        return $("<div>").text(value === null || value === undefined ? "" : value).html();
    }

    function resetProduksiJalanHistoryTimeline(targetSelector) {
        const $target = $(targetSelector);
        $target
            .data("id-others", "")
            .data("next-offset", 0)
            .data("history-limit", PRODUKSI_JALAN_HISTORY_LIMIT)
            .html('<div class="text-muted">Memuat history...</div>');
    }

    function renderProduksiJalanHistoryTimeline(targetSelector, history, meta, append) {
        const $target = $(targetSelector);
        const idOthers = meta && meta.data && meta.data[0] ? meta.data[0].id : $target.data("id-others");
        const nextOffset = meta ? (meta.history_next_offset || 0) : 0;
        const limit = meta ? (meta.history_limit || PRODUKSI_JALAN_HISTORY_LIMIT) : PRODUKSI_JALAN_HISTORY_LIMIT;
        const hasMore = !!(meta && meta.history_has_more);

        $target
            .data("id-others", idOthers || "")
            .data("next-offset", nextOffset)
            .data("history-limit", limit);

        if (!append) {
            $target.html('<div class="produksi-jalan-timeline"></div><div class="produksi-jalan-history-action mt-1"></div>');
        }

        const $timeline = $target.find(".produksi-jalan-timeline");
        if (!history.length && !append) {
            $timeline.html('<div class="text-muted">Belum ada history progres.</div>');
        }

        $.each(history, function(index, item) {
            let photos = "";
            $.each(item.foto_urls || [], function(fotoIndex, url) {
                photos +=
                    '<a href="' + url + '" target="_blank" class="mr-1 mb-1 d-inline-block">' +
                    '<img src="' + url + '" class="img-thumbnail produksi-jalan-timeline-photo" />' +
                    '</a>';
            });

            $timeline.append(
                '<div class="produksi-jalan-timeline-item">' +
                    '<div class="d-flex justify-content-between align-items-start flex-wrap">' +
                        '<div class="produksi-jalan-timeline-title">Progres ' +
                            escapeProduksiJalanHistoryValue(item.progres) + '%</div>' +
                        '<div class="produksi-jalan-timeline-meta">' + format_datetime(item.created_at) + '</div>' +
                    '</div>' +
                    '<div class="produksi-jalan-timeline-meta mb-1">Oleh: ' +
                        escapeProduksiJalanHistoryValue(item.username || '-') + '</div>' +
                    '<div>' + escapeProduksiJalanHistoryValue(item.keterangan || '-') + '</div>' +
                    (photos ? '<div class="mt-1">' + photos + '</div>' : '') +
                '</div>'
            );
        });

        const $action = $target.find(".produksi-jalan-history-action");
        if (hasMore) {
            $action.html(
                '<button type="button" class="btn btn-outline-primary btn-sm produksi-jalan-load-more" data-target="' +
                    escapeProduksiJalanHistoryValue(targetSelector) + '">Muat lagi</button>'
            );
        } else {
            $action.empty();
        }
    }

    $(document).off("click", ".produksi-jalan-load-more").on("click", ".produksi-jalan-load-more", function() {
        loadProduksiJalanHistoryMore($(this).data("target"));
    });

    function loadProduksiJalanHistoryMore(targetSelector) {
        const $target = $(targetSelector);
        const idOthers = $target.data("id-others");
        if (!idOthers) return;

        const $button = $target.find(".produksi-jalan-history-action .btn");
        $button.prop("disabled", true).html('Memuat <i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: base_url + 'siteplan/get_others',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: idOthers,
                history_limit: $target.data("history-limit") || PRODUKSI_JALAN_HISTORY_LIMIT,
                history_offset: $target.data("next-offset") || 0
            },
            dataType: 'json',
            success: function(r) {
                csrfHash = r.token;
                renderProduksiJalanHistoryTimeline(targetSelector, r.history || [], r, true);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan saat memuat history',
                    showConfirmButton: false,
                    timer: 1500
                });
                $button.prop("disabled", false).html("Muat lagi");
            }
        });
    }
    const isSubsidi = ['Non-Subsidi', 'Subsidi'],
        isKPR = ['Tunai/Cash Keras', 'KPR', 'Tunai/Bertahap']

    let dmkdt,
        dpl,
        dpr,
        dlg,
        dlc,
        dbprod,
        dr;

    function detail_kavling(sh, id_kavling) {
        prepareDetailModalRedesign();
        $(".id_sikumbang").html("Memuat data sikumbang...");
        let src = not_found
        $("#fm-detail input, #fm-detail select, #fm-detail textarea").prop("disabled", true)
        $('#dtt-summary-tab').tab('show');
        $("#detailConsumerMore").collapse('hide');
        $(".detail-consumer-toggle").attr('aria-expanded', 'false');
        $("#detailHargaJualPricelist").collapse('hide');
        $(".detail-price-toggle").attr('aria-expanded', 'false');

        $([
            "#dt-pl_hargajual", "#dt-pl_harga_diskon_hargajual", "#dt-pl_hargajual_net", "#dt-pl_kpr",
            "#dt-pl_uang_muka", "#dt-pl_harga_diskon_uang_muka", "#dt-pl_biaya_adm", "#dt-pl_ppn",
            "#dt-pl_bphtb", "#dt-pl_biaya_proses",
            "#dt-hargajual", "#dt-harga_diskon_hargajual", "#dt-hargajual_net", "#dt-kpr", "#dt-uang_muka",
            "#dt-harga_diskon_uang_muka", "#dt-biaya_adm", "#dt-ppn", "#dt-bphtb", "#dt-biaya_proses",
            "#dt-st_harga_kpr_acc", "#dt-st_harga_penambahan_um", "#dt-st_harga_penambahan",
            "#dt-st_harga_penambahan_tanah",
        ].join(",")).text('-')
        $("#dt-pl_keterangan").html('-')

        $("#dt-promo").text('-')
        $("#dt-is_kpr").text('-')
        $("#dt-is_subsidi").text('-')
        $("#dt-no_spptb").text('-')
        $("#dt-nama_konsumen").text('-')
        $("#dt-alamat_konsumen").text('-')
        $("#dt-nik_konsumen").text('-')
        $("#dt-npwp_konsumen").text('-')
        $("#dt-hp_konsumen").text('-')
        $("#dt-email_konsumen").text('-')
        $("#dt-sales").text('-')
        loadTipeDetail(null)

        let categories = [
            "rab_dokumen",
            "prod_foto_konstruksi",
            "prod_foto_exterior",
            "prod_foto_interior",
            "jalan_foto",
            "jalan_foto_update",
            "listrik_pln_foto",
            "listrik_disediakan_dokumen",
            "air_komunal",
            "air_tanah",
            "air_pdam",
        ];

        categories.forEach((cat) => {
            $("#dt-list_" + cat).html("");
            $("#dt-label_" + cat).html("Upload file/Foto");
        });

        $(".files-here").prop('src', src)

        $.ajax({
            url: base_url + 'siteplan/get/detail',
            type: 'post',
            data: {
                [csrfName]: csrfHash,
                id_kavling: id_kavling,
                id_legal: sh.data.id_legal,
                id_produksi: sh.data.id_produksi,
                id_keuangan: sh.data.id_keuangan,
                id_mkdt: sh.data.id_mkdt,
                id_hargajual: sh.data2.id_hargajual
            },
            dataType: 'json',
            beforeSend: function() {
                $("#loading").removeClass("hidden");
            },
            success: function(r) {
                $("#loading").addClass("hidden");

                csrfHash = r.token;
                dr = r

                loadSummary(dr)
                loadPL(r.pricelist)
                loadKavling(r)
                loadTipeDetail(r.tipe_detail)
                loadMKDT(r.mkdt)
                loadLegal(r.legal)
                loadTagihan(r)
                loadProduksi(r.produksi, r.files)
                loadBuktiBayarPajak(r)
                loadCashOut(r.cashout || [])
                loadFinanceFlow(r.finance_flow || {})
                loadBayarProduksi(r.bayar_produksi || [])
                loaded['sm'] = true

                /************************ load bayar produksi  ***************************/

                let lAlamat = setLabelAlamat(siteplanActiveProyekName(), sh.data.nama_jalan, sh.data.no_kavling, sh.data2.no_tipe_rumah, sh.data2.tipe_rumah)

                $(".label_alamat").html(lAlamat);
                $("#label-hargajual").html(`
                    <h5 class="text-primary mb-0"><strong>Rp. ${sh.data2.harga_akhir}</strong></h5>
                    <small class="text-muted">(${format_date(sh.data2.harga_akhir_tgl)} - ${sh.data2.harga_akhir_oleh})</small>`);

                $("#modal_detail").modal('show');
            },
            error: function(xhr, st, err) {
                $("#loading").addClass("hidden")
                return swal("error", err);
            },
        });



        let no_kavling_pad = (parseInt(sh.data.no_kavling, 10) < 10 ? '0' : '') + sh.data.no_kavling;
        let id_kavling_sikumbang = dt_proyek.id_perumahan_sikumbang + sh.data.nama_jalan.replace(/\s+/g, '').toUpperCase() + no_kavling_pad;


        fetch('https://sikumbang.tapera.go.id/ajax/trilogi/' + id_kavling_sikumbang)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Fetched data from SIKUMBANG:', data);

                let tpengembang, tbank, tmbr, txt
                if (data.error === true) {
                    txt = "ID TIdak Ditemukan/Sikumbang Sedang tidak bisa diakses."
                } else {
                    tpengembang = (data.data.pengembang === true) ? '<span class="badge badge-success">Ready Stock</span>' : '';
                    tbank = (data.data.bank === true) ? '<span class="badge badge-success">Trilogy Bank</span>' : '';
                    tmbr = (data.data.mbr === true) ? '<span class="badge badge-success">Trilogy MBR</span>' : '';

                    txt = `<b>${id_kavling_sikumbang}</b> <br>${tpengembang} ${tbank} ${tmbr}`;
                }
                // You can handle/use the fetched data as needed here
                $(".id_sikumbang").html(txt);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    $('#modal_detail').on('hidden.bs.modal', function() {
        // alert()
        loaded = [];
    });


    let modalKeuanganChart = null;
    let modalHasilAkadChart = null;

    function loadSummary(r) {
        //load data konsumen
        if (r.mkdt) {
            let mkdt = r.mkdt
            setText("#dt-promo", mkdt.promo)
            setText("#dt-is_kpr", isKPR[mkdt.is_kpr], true, "success")
            setText("#dt-is_subsidi", isSubsidi[mkdt.is_subsidi], true, "success")
            setText("#dt-no_spptb", mkdt.no_spptb)
            setText("#dt-nama_konsumen", mkdt.nama_konsumen)
            setText("#dt-alamat_konsumen", mkdt.alamat_konsumen)
            setText("#dt-nik_konsumen", mkdt.nik_konsumen)
            setText("#dt-npwp_konsumen", mkdt.npwp)
            setText("#dt-hp_konsumen", mkdt.hp_konsumen)
            setText("#dt-email_konsumen", mkdt.email_konsumen)
            setText("#dt-sales", mkdt.sales)

            // console.log()

            setStatusMkdtBadge(mkdt.status_mkdt)
            setText("#s-st_bank", mkdt.bank)
            setText("#s-notaris", mkdt.notaris)
            setText("#s-st_sp3k_tgl", format_date(mkdt.sp3k_tgl))
            setText("#s-st_sp3k_tgl_exp", format_date(mkdt.sp3k_tgl_exp))
            setText("#s-akad_tgl", format_date(mkdt.akad_tgl))
        } else {
            setText("#dt-promo", '-')
            setText("#dt-is_kpr", '-')
            setText("#dt-is_subsidi", '-')
            setText("#dt-no_spptb", '-')
            setText("#dt-nama_konsumen", '-')
            setText("#dt-alamat_konsumen", '-')
            setText("#dt-nik_konsumen", '-')
            setText("#dt-npwp_konsumen", '-')
            setText("#dt-hp_konsumen", '-')
            setText("#dt-email_konsumen", '-')
            setText("#dt-sales", '-')

            // console.log()

            setStatusMkdtBadge('-')
            setText("#s-st_bank", '-')
            setText("#s-notaris", '-')
            setText("#s-st_sp3k_tgl", '-')
            setText("#s-st_sp3k_tgl_exp", '-')
            setText("#s-akad_tgl", '-')
        }

        if (r.kavling) {
            setText("#s-perintah_bangun_tgl", format_date(r.kavling.perintah_bangun_tgl))
        } else {
            setText("#s-perintah_bangun_tgl", "-")
        }

        //keuangan
        const tg = hitungTagihan(r)
        setText("#s-persentase_bayar_tagihan_um", tg.ldp)
        setText("#s-persentase_bayar_tagihan_um_ll", tg.ldp_semua)
        setText("#s-persentase_bayar_tagihan_bb", tg.ldp_bb)

        if (r.produksi) {
            let pr = r.produksi
            let p_bangunan = parseInt(pr.progres_bangunan) || 0;
            setText("#s-progress_bangunan_text", p_bangunan + '%')
            $("#s-progress_bangunan_bar").css("width", p_bangunan + "%").attr("aria-valuenow", p_bangunan)
                .toggleClass("is-empty", p_bangunan <= 0)
                .toggleClass("is-partial", p_bangunan > 0 && p_bangunan < 100);

            setText("#s-tanggal_pembangunan", format_date(pr.tanggal_pembangunan))
            setText("#s-tanggal_selesai_pembangunan", format_date(pr.tanggal_selesai_pembangunan))

            setAmenityChip("#s-st_saluran", pr.st_saluran)
            setAmenityChip("#s-st_air", pr.st_air)
            setAmenityChip("#s-st_jalan", pr.st_jalan)
            setAmenityChip("#s-slo", pr.slo)
            setAmenityChip("#s-lpa", pr.lpa)
        } else {
            setText("#s-progress_bangunan_text", "-")
            $("#s-progress_bangunan_bar").css("width", "0%").attr("aria-valuenow", 0)
                .addClass("is-empty").removeClass("is-partial");

            setText("#s-tanggal_pembangunan", "-")
            setText("#s-tanggal_selesai_pembangunan", "-")

            setAmenityChip("#s-st_saluran", false)
            setAmenityChip("#s-st_air", false)
            setAmenityChip("#s-st_jalan", false)
            setAmenityChip("#s-slo", false)
            setAmenityChip("#s-lpa", false)
        }

        if (r.si) {
            let si = ''
            $.each(r.si, function(i, v) {
                si += `
                <div class="custom-control custom-checkbox mb-1">
                    <input type="checkbox" class="custom-control-input" id="s-si-${i}" disabled ${v.id_kavling ? 'checked' : ''}>
                    <label class="custom-control-label" for="s-si-${i}">${v.nama}
                        <small class="text-muted d-block">${v.tanggal_si ? format_date(v.tanggal_si) : '-'}</small>
                    </label>
                </div>
                `
            });
            applyLoadingEffect("#s-si")
            setTimeout(() => {
                setText("#s-si", si)
                removeLoadingEffect("#s-si");
            }, 500);
        }

        const financeFlowTypeTag = {
            'cashout_subkon_allocation': { label: 'Subkon', cls: 'badge-warning' },
            'bayar_produksi': { label: 'Produksi', cls: 'badge-secondary' },
            'pajak_pph42': { label: 'PPh', cls: 'badge-danger' },
            'pajak_ppn': { label: 'PPN', cls: 'badge-danger' },
            'pencairan_akad_payment_detail': { label: 'Retensi Akad', cls: 'badge-success' },
            'dana_jaminan': { label: 'Dana Jaminan', cls: 'badge-success' },
            'bank_kpr_disbursement': { label: 'Retensi Bank', cls: 'badge-success' },
        };

        if (r.finance_flow) {
            let cashout = ''
            const rows = Array.isArray(r.finance_flow.expense_rows) ? r.finance_flow.expense_rows : []

            if (rows.length == 0) {
                cashout += `<div class="detail-mini-card text-center text-muted" style="font-size:.82rem;">
                    Belum ada riwayat pembayaran yang tercatat
                </div>`

            } else {
                // list ini mengikuti data & urutan yang sama dengan tabel Cashout di tab Keuangan, tanpa kolom nominal
                let items = ''
                $.each(rows, function(i, v) {
                    const tanggal = v.tanggal_transaksi || v.tanggal_bayar
                    const label = v.label || v.item
                    const tag = financeFlowTypeTag[v.source_type]
                    items += `
                <div class="detail-cashout-timeline-item">
                    <div class="detail-info-row">
                        <div class="detail-info-col">
                            <span class="detail-info-label">${label} ${tag ? `<span class="detail-status-badge ${tag.cls}">${tag.label}</span>` : ''}</span>
                            <span class="detail-info-value">${tanggal ? format_date(tanggal) : '-'}</span>
                        </div>
                    </div>
                    ${v.keterangan ? `<div class="text-muted" style="font-size:.76rem; margin-top:-.35rem; margin-bottom:.5rem;">${v.keterangan}</div>` : ''}
                </div>`
                });
                cashout = `<div class="detail-cashout-timeline">${items}</div>`
            }


            applyLoadingEffect("#s-co")
            setTimeout(() => {
                setText("#s-co", cashout)
                removeLoadingEffect("#s-co");
            }, 500);
        }

        if (r.hutang_subkon) {
            let hutang = ''
            const rows = r.hutang_subkon

            if (rows.length == 0) {
                hutang += `<div class="detail-mini-card text-center text-muted" style="font-size:.82rem;">
                    Tidak ada hutang subkon
                </div>`
            } else {
                $.each(rows, function(i, v) {
                    hutang += `
                <div class="detail-info-row">
                    <div class="detail-info-col">
                        <span class="detail-info-label">${v.nomor_surat ?? '-'}</span>
                        <span class="detail-info-value">${v.tanggal_jatuh_tempo ? format_date(v.tanggal_jatuh_tempo) : '-'}</span>
                    </div>
                    <div class="detail-info-col text-right">
                        <span class="detail-info-value">${detailRupiah(v.nominal)}</span>
                    </div>
                </div>
                ${v.keterangan ? `<div class="text-muted" style="font-size:.76rem; margin-top:-.35rem; margin-bottom:.5rem;">${v.keterangan}</div>` : ''}`
                });
            }

            applyLoadingEffect("#s-hutang-subkon")
            setTimeout(() => {
                setText("#s-hutang-subkon", hutang)
                removeLoadingEffect("#s-hutang-subkon");
            }, 500);
        }

        // --- Render Keuangan Chart ---
        if (modalKeuanganChart) {
            modalKeuanganChart.destroy();
        }

        let ctx = document.getElementById('keuanganChart');
        if (ctx) {
            ctx = ctx.getContext('2d');

            let totalBayar = parseFloat(tg.sb_semua) || 0;
            let sisaTagihan = parseFloat(tg.sisa_semua) || 0;

            setText("#s-total_dibayar", "Rp " + num_format(totalBayar));
            setText("#s-sisa_tagihan", "Rp " + num_format(sisaTagihan));

            if (totalBayar === 0 && sisaTagihan === 0) {
                $("#keuanganChart-empty").show();
                $("#keuanganChart").hide();
            } else {
                $("#keuanganChart-empty").hide();
                $("#keuanganChart").show();

                let isLunas = sisaTagihan <= 0 && totalBayar > 0;
                let percentLabel = tg.ldp_semua || '0%';

                modalKeuanganChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Telah Dibayar', 'Sisa Tagihan'],
                        datasets: [{
                            data: [totalBayar, sisaTagihan],
                            backgroundColor: ['#28c76f', '#ea5455'], // Success green and Danger red
                            borderWidth: 0
                        }]
                    },
                    plugins: [{
                        id: 'detailCenterText',
                        afterDraw(chart) {
                            const { ctx, chartArea: { top, bottom, left, right } } = chart;
                            const cx = (left + right) / 2, cy = (top + bottom) / 2;
                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.font = '700 20px sans-serif';
                            ctx.fillStyle = '#020617';
                            ctx.fillText(percentLabel, cx, cy - 9);
                            ctx.font = '700 11px sans-serif';
                            ctx.fillStyle = isLunas ? '#28c76f' : '#ea5455';
                            ctx.fillText(isLunas ? 'LUNAS' : 'BELUM LUNAS', cx, cy + 11);
                            ctx.restore();
                        }
                    }],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.raw !== null) {
                                            label += 'Rp ' + num_format(context.raw);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // --- Render Retensi & Hasil Akad ---
        renderRetensiHasilAkad(r.pencairan_akad);
    }

    function renderRetensiHasilAkadItems(items) {
        const retensiItems = (Array.isArray(items) ? items : []).filter(v => v.jenis === 'retensi' && parseFloat(v.nominal || 0) > 0);

        if (retensiItems.length === 0) {
            return `<div class="detail-mini-card text-center text-muted" style="font-size:.82rem;">
                Belum ada rencana retensi
            </div>`;
        }

        const rows = retensiItems.map(function(v) {
            const nominal = parseFloat(v.nominal || 0);
            const sudahCair = parseFloat(v.sudah_cair || 0);
            let status = '-';
            let badgeClass = 'badge-secondary';
            if (v.is_locked) {
                if (sudahCair >= nominal - 0.01) {
                    status = 'Sudah cair';
                    badgeClass = 'badge-success';
                } else {
                    status = 'Diajukan pencairan';
                    badgeClass = 'badge-warning';
                }
            }

            return `
                <div class="detail-cashout-timeline-item">
                    <div class="detail-info-row">
                        <div class="detail-info-col">
                            <span class="detail-info-label">${detailEscapeHtml(v.nama_jaminan || 'Retensi')}</span>
                            <span class="detail-info-value">${detailRupiah(nominal)}</span>
                        </div>
                        <div class="detail-info-col text-right">
                            <span class="detail-status-badge ${badgeClass}">${status}</span>
                        </div>
                    </div>
                </div>`;
        }).join('');

        return `<div class="detail-cashout-timeline">${rows}</div>`;
    }

    function renderRetensiHasilAkad(pa) {
        if (modalHasilAkadChart) {
            modalHasilAkadChart.destroy();
            modalHasilAkadChart = null;
        }

        const items = pa && Array.isArray(pa.items) ? pa.items : [];
        const plan = pa && pa.plan ? pa.plan : null;
        const totalHasilAkad = plan ? parseFloat(plan.total_hasil_akad || 0) : 0;

        applyLoadingEffect("#s-pa-retensi")
        setTimeout(() => {
            setText("#s-pa-retensi", renderRetensiHasilAkadItems(items))
            removeLoadingEffect("#s-pa-retensi");
        }, 500);

        let ctx = document.getElementById('hasilAkadChart');
        if (!ctx) {
            return;
        }
        ctx = ctx.getContext('2d');

        if (!plan || totalHasilAkad <= 0) {
            setText("#s-pa_total_hasil_akad", '-');
            setText("#s-pa_total_cair", '-');
            $("#hasilAkadChart-empty").show();
            $("#hasilAkadChart").hide();
            return;
        }

        const totalCair = items
            .filter(v => v.jenis === 'tenor')
            .reduce((sum, v) => sum + (parseFloat(v.sudah_cair || 0)), 0);
        const sisa = Math.max(0, totalHasilAkad - totalCair);

        setText("#s-pa_total_hasil_akad", detailRupiah(totalHasilAkad));
        setText("#s-pa_total_cair", detailRupiah(totalCair));

        $("#hasilAkadChart-empty").hide();
        $("#hasilAkadChart").show();

        const percentLabel = Math.round((totalCair / totalHasilAkad) * 100) + '%';
        const isLunas = sisa <= 0.01 && totalCair > 0;

        modalHasilAkadChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sudah Cair', 'Belum Cair'],
                datasets: [{
                    data: [totalCair, sisa],
                    backgroundColor: ['#28c76f', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            plugins: [{
                id: 'hasilAkadCenterText',
                afterDraw(chart) {
                    const { ctx, chartArea: { top, bottom, left, right } } = chart;
                    const cx = (left + right) / 2, cy = (top + bottom) / 2;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '700 20px sans-serif';
                    ctx.fillStyle = '#020617';
                    ctx.fillText(percentLabel, cx, cy - 9);
                    ctx.font = '700 11px sans-serif';
                    ctx.fillStyle = isLunas ? '#28c76f' : '#b45309';
                    ctx.fillText(isLunas ? 'CAIR' : 'BELUM CAIR', cx, cy + 11);
                    ctx.restore();
                }
            }],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    label += 'Rp ' + num_format(context.raw);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }


    function isSudah(e) {
        if (e)
            return `<i class="fa fa-solid fa-check"></i> Sudah`
        return `-`
    }

    function setStatusMkdtBadge(status) {
        $("#s-status_mkdt").text(status || '-')
            .removeClass('badge-success badge-secondary badge-danger')
            .addClass(status === 'Akad' ? 'badge-success' : (status === 'Batal' ? 'badge-danger' : 'badge-secondary'));
    }

    function setAmenityChip(id, val) {
        const ok = !!val;
        $(id).text(ok ? 'Sudah' : 'Belum')
            .removeClass('badge-success badge-secondary')
            .addClass(ok ? 'badge-success' : 'badge-secondary');
    }

    function last_update(id, username = null, date = null) {
        let u = '-',
            t = '-'
        if (username) {
            u = username
            t = format_datetime(date)
        }
        $(id).html(`Terakhir diubah oleh: <b>${u}</b>, pada: <b>${t}</b> `);
    }

    let detailModalRedesignPrepared = false;

    function detailMoneyValue(value) {
        if (value === null || value === undefined || value === '') return 0;
        if (typeof value === 'number') return value;
        return Number(String(value).replace(/[^0-9.-]/g, '')) || 0;
    }

    function detailRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(detailMoneyValue(value));
    }

    function detailDocFilename(url) {
        if (!url) return 'Belum diunggah';
        const clean = String(url).split('?')[0];
        const name = decodeURIComponent(clean.split('/').pop() || '');
        return name || 'Belum diunggah';
    }

    function detailEscapeHtml(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    function detailTextValue(value) {
        return value === null || value === undefined || value === '' ? '-' : value;
    }

    function detailPercentValue(value) {
        const n = Number(String(value || '0').replace('%', '')) || 0;
        return Math.max(0, Math.min(100, n));
    }

    function renderTipeDetailFile(selector, label, accessUrl, downloadUrl) {
        const safeLabel = detailEscapeHtml(label);

        if (!accessUrl) {
            $(selector).html(`
                <div class="detail-spec-file-preview">
                    <div class="detail-spec-file-empty">Belum ada file</div>
                </div>
                <div class="detail-spec-file-body">
                    <div class="detail-mini-label">${safeLabel}</div>
                    <div class="detail-mini-value">-</div>
                </div>
            `);
            return;
        }

        const previewUrl = detailEscapeHtml(accessUrl);
        const actionUrl = detailEscapeHtml(downloadUrl || accessUrl);
        $(selector).html(`
            <a class="detail-spec-file-preview" href="${actionUrl}" target="_blank" rel="noopener">
                <img src="${previewUrl}" alt="${safeLabel}">
            </a>
            <div class="detail-spec-file-body">
                <div class="detail-mini-label">${safeLabel}</div>
                <a class="btn btn-outline-primary btn-sm detail-file-btn" href="${actionUrl}" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt mr-50"></i> Lihat / Unduh
                </a>
            </div>
        `);
    }

    function loadTipeDetail(tipe) {
        const data = tipe || {};
        const tipeLabel = [
            detailTextValue(data.no_tipe_rumah),
            detailTextValue(data.tipe_rumah)
        ].filter((item) => item !== '-').join(' / ') || '-';

        $("#dt-spesifikasi-tipe").text(tipeLabel);
        $("#dt-spesifikasi-lb").text(data.lb ? `${data.lb} m2` : '-');
        $("#dt-spesifikasi-lt").text(data.lt ? `${data.lt} m2` : '-');
        $("#dt-spesifikasi-kamar-tidur").text(detailTextValue(data.jumlah_kamar_tidur));
        $("#dt-spesifikasi-kamar-mandi").text(detailTextValue(data.jumlah_kamar_mandi));
        $("#dt-spesifikasi-atap").text(detailTextValue(data.spesifikasi_teknis_atap));
        $("#dt-spesifikasi-dinding").text(detailTextValue(data.spesifikasi_teknis_dinding));
        $("#dt-spesifikasi-lantai").text(detailTextValue(data.spesifikasi_teknis_lantai));
        $("#dt-spesifikasi-pondasi").text(detailTextValue(data.spesifikasi_teknis_pondasi));

        renderTipeDetailFile(
            "#dt-spesifikasi-gambar-tipe",
            "Gambar Ilustrasi",
            data.gambar_tipe_access_url,
            data.gambar_tipe_download_url
        );
        renderTipeDetailFile(
            "#dt-spesifikasi-gambar-denah",
            "Denah Arsitektural",
            data.gambar_denah_access_url,
            data.gambar_denah_download_url
        );
    }

    function detailAccordionItem(parentId, itemId, title, isOpen = false) {
        return `
            <div class="detail-accordion-card">
                <button class="detail-accordion-toggle" type="button" data-toggle="collapse"
                    data-target="#${itemId}" aria-expanded="${isOpen ? 'true' : 'false'}"
                    aria-controls="${itemId}">
                    <span>${title}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div id="${itemId}" class="collapse ${isOpen ? 'show' : ''}" data-parent="#${parentId}">
                    <div class="detail-accordion-body" id="${itemId}-body"></div>
                </div>
            </div>`;
    }

    function detailTagihanSummaryCard() {
        return `
            <div class="detail-tagihan-card" data-tagihan-card="semua">
                <input type="hidden" id="dt-total_tagihan_semua" name="dt-total_tagihan_semua">
                <input type="hidden" id="dt-sudah_bayar_semua" name="dt-sudah_bayar_semua">
                <div class="detail-tagihan-grid">
                    <div class="detail-tagihan-col-total">
                        <div class="detail-card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        <div>
                            <div class="detail-tagihan-label">Total Tagihan</div>
                            <div class="detail-tagihan-total" id="dt-total_tagihan_semua_text">Rp 0</div>
                        </div>
                    </div>
                    <div class="detail-tagihan-col-status">
                        <div class="detail-tagihan-side">
                            <div class="detail-tagihan-side-block">
                                <div class="detail-tagihan-side-label">Sudah Dibayar</div>
                                <div class="detail-tagihan-side-value" id="dt-sudah_bayar_semua_text">Rp 0</div>
                            </div>
                            <div class="detail-tagihan-side-block">
                                <div class="detail-tagihan-side-label">Status</div>
                                <span class="detail-status-badge badge-secondary" id="dt-tagihan-status-badge">Belum Lunas</span>
                            </div>
                        </div>
                        <div class="detail-tagihan-progress-wrap">
                            <div class="detail-progress-track">
                                <div class="detail-progress-fill is-empty" id="detail-tagihan-semua-bar"></div>
                            </div>
                            <div class="detail-tagihan-progress-labels">
                                <span>0%</span>
                                <span id="dt-tagihan-percent-text">0% Paid</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function renderTagihanSummaryCard(total, paid) {
        const percent = total > 0 ? Math.max(0, Math.min(100, (paid / total) * 100)) : 0;
        const isLunas = total > 0 && paid >= total;

        $('#dt-total_tagihan_semua_text').text(detailRupiah(total));
        $('#dt-sudah_bayar_semua_text').text(detailRupiah(paid));
        $('#dt-tagihan-percent-text').text(`${Math.round(percent)}% Paid`);
        $('#detail-tagihan-semua-bar')
            .css('width', `${percent}%`)
            .toggleClass('is-empty', percent <= 0)
            .toggleClass('is-partial', percent > 0 && percent < 100);
        $('#dt-tagihan-status-badge')
            .text(isLunas ? 'Lunas' : 'Belum Lunas')
            .toggleClass('badge-success', isLunas)
            .toggleClass('badge-secondary', !isLunas);
    }

    function prepareDetailModalRedesign() {
        if (detailModalRedesignPrepared || !$('#modal_detail').length) return;
        detailModalRedesignPrepared = true;

        $('#dtt-hj-tab').text('Keuangan');
        $('#dt-stdetail-tab').text('Status & Akad');
        $('#dt-legal-tab').text('Legal & Pajak');
        $('#dt-tagihan-tab, #dt-cashout-tab, #dt-pajak-tab').closest('.nav-item').addClass('detail-legacy-hidden');

        const $summaryRow = $('#dtt-summary > .row');
        $summaryRow.addClass('detail-dashboard-grid').removeClass('row');
        $summaryRow.children('[class*="col-md-"]').each(function(i) {
            $(this).removeClass('col-md-4').addClass('detail-summary-card');
            if (!$(this).find('.detail-card-icon').length) {
                const icons = ['fas fa-clipboard-check', 'fas fa-wallet', 'fas fa-hard-hat'];
                $(this).prepend(`<div class="detail-card-icon mb-1"><i class="${icons[i] || 'fas fa-info'}"></i></div>`);
            }
        });

        buildDetailFinancePanel();
        buildDetailLegalPanel();
        buildDetailProductionPanel();
        bindDetailModalRedesignEvents();
    }

    function buildDetailFinancePanel() {
        const $finance = $('#dtt-hj');
        if ($('#detailFinanceAccordion').length) return;

        const $hargaContent = $finance.children().detach();

        $('#dt-tagihan').empty().append(`
            <small id="last_update_keuangan" class="text-muted d-block mb-1"></small>
            <div id="detail-tagihan-cards">
                ${detailTagihanSummaryCard()}
            </div>
        `);

        const $tagihanContent = $('#dt-tagihan').children().detach();
        $('#dt-fm-prod-bayar_produksi').empty();

        $finance.append(`
            <div class="detail-accordion" id="detailFinanceAccordion">
                ${detailAccordionItem('detailFinanceAccordion', 'detail-finance-tagihan', 'Tagihan', true)}
                ${detailAccordionItem('detailFinanceAccordion', 'detail-finance-flow', 'Cash In & Cash Out')}
                ${detailAccordionItem('detailFinanceAccordion', 'detail-finance-harga', 'Harga Jual')}
            </div>
        `);

        $('#detail-finance-harga-body').addClass('detail-price-comparison').append($hargaContent);
        $('#detail-finance-tagihan-body').append($tagihanContent);
        $('#detail-finance-flow-body').append(`
            <div class="detail-card-grid mb-1">
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Cash In</div>
                    <div class="detail-mini-value detail-text-primary" id="dt-finance-income-total">Rp 0</div>
                    <small class="text-muted" id="dt-finance-income-count">0 transaksi</small>
                </div>
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Cash Out</div>
                    <div class="detail-mini-value detail-text-danger" id="dt-finance-expense-total">Rp 0</div>
                    <small class="text-muted" id="dt-finance-expense-count">0 transaksi</small>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-1 mb-md-0">
                    <div class="divider divider-left">
                        <div class="divider-text">Cash In</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="dt-finance-income-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Label</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="divider divider-left">
                        <div class="divider-text">Cash Out</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="dt-finance-expense-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Item</th>
                                    <th class="text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `);
    }

    function buildDetailLegalPanel() {
        const $legal = $('#dt-legal');
        if ($('#detailLegalAccordion').length) return;

        const $lastUpdate = $('#last_update_legal').detach();
        const groups = {
            sertifikat: [
                $('#dt-legal-sertifikat').children().detach(),
                $('#dt-legal-pbb').children().detach()
            ],
            pajak: [
                $('#dt-legal-bphtb').children().detach(),
                $('#dt-legal-pph').children().detach(),
                $('#dt-pajak').children().detach()
            ],
            akad: [
                $('#dt-legal-pbg').children().detach(),
                $('#dt-legal-ajb').children().detach()
            ]
        };

        $legal.empty().append($lastUpdate).append(`
            <div class="detail-accordion mt-1" id="detailLegalAccordion">
                ${detailAccordionItem('detailLegalAccordion', 'detail-legal-sertifikat', 'Sertipikat & PBB', true)}
                ${detailAccordionItem('detailLegalAccordion', 'detail-legal-pajak', 'BPHTB, PPH & Bukti Bayar')}
                ${detailAccordionItem('detailLegalAccordion', 'detail-legal-akad', 'PBG & AJB/PPJB')}
            </div>
        `);

        groups.sertifikat.forEach($content => $('#detail-legal-sertifikat-body').append($content));
        groups.pajak.forEach($content => $('#detail-legal-pajak-body').append($content));
        groups.akad.forEach($content => $('#detail-legal-akad-body').append($content));
    }

    function buildDetailProductionPanel() {
        const $produksi = $('#dt-produksi');
        if ($('#detailProductionAccordion').length) return;

        const $lastUpdate = $('#last_update_produksi').detach();
        const sections = {
            progress: $('#dt-fm-prod-progress').children().detach(),
            dokumentasi: $('#dt-fm-prod-dokumentasi').children().detach(),
            jalan: $('#dt-fm-prod-jalan').children().detach(),
            listrik: $('#dt-fm-prod-listrik').children().detach(),
            air: $('#dt-fm-prod-air').children().detach()
        };

        $produksi.empty().append($lastUpdate).append(`
            <div class="detail-production-dashboard mt-1">
                <div class="detail-progress-card">
                    <div class="detail-mini-label">Progres Bangunan</div>
                    <div class="detail-progress-number"><span id="dt-produksi-progress-summary">0</span>%</div>
                    <div class="detail-progress-track mt-1">
                        <div class="detail-progress-fill is-empty" id="dt-produksi-progress-bar"></div>
                    </div>
                </div>
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Tanggal Bangun</div>
                    <div class="detail-mini-value" id="dt-produksi-tanggal-bangun">-</div>
                </div>
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Tanggal Selesai</div>
                    <div class="detail-mini-value" id="dt-produksi-tanggal-selesai">-</div>
                </div>
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Listrik</div>
                    <div class="detail-mini-value" id="dt-produksi-listrik-summary">-</div>
                </div>
                <div class="detail-mini-card">
                    <div class="detail-mini-label">Air</div>
                    <div class="detail-mini-value" id="dt-produksi-air-summary">-</div>
                </div>
            </div>
            <div class="detail-accordion" id="detailProductionAccordion">
                ${detailAccordionItem('detailProductionAccordion', 'detail-produksi-progress', 'Progres & Jadwal', true)}
                ${detailAccordionItem('detailProductionAccordion', 'detail-produksi-dokumentasi', 'Dokumentasi')}
                ${detailAccordionItem('detailProductionAccordion', 'detail-produksi-jalan', 'Jalan')}
                ${detailAccordionItem('detailProductionAccordion', 'detail-produksi-listrik', 'Listrik')}
                ${detailAccordionItem('detailProductionAccordion', 'detail-produksi-air', 'Air')}
            </div>
        `);

        $('#detail-produksi-progress-body').append(sections.progress);
        $('#detail-produksi-dokumentasi-body').append(sections.dokumentasi);
        $('#detail-produksi-jalan-body').append(sections.jalan);
        $('#detail-produksi-listrik-body').append(sections.listrik);
        $('#detail-produksi-air-body').append(sections.air);
    }

    function bindDetailModalRedesignEvents() {
        $(document)
            .off('change.detailRedesign', '#dt-listrik_jenis')
            .on('change.detailRedesign', '#dt-listrik_jenis', function() {
                if (this.value == "PLN") {
                    $("#dt-listrik-pln-input-form").removeClass("hidden");
                    $("#listrik_disediakan").addClass("hidden");
                } else {
                    $("#dt-listrik-pln-input-form").addClass("hidden");
                    $("#listrik_disediakan").removeClass("hidden");
                }
            });

        $(document)
            .off('change.detailRedesign', '#dt-air_jenis')
            .on('change.detailRedesign', '#dt-air_jenis', function() {
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
    }

    function loadPL(pl) {
        //load harga pricelist
        if (pl) {
            $.each(pl, function(i, v) {
                if (i === 'tgl_harga' || i === 'keterangan') return;
                applyLoadingEffect("#dt-pl_" + i, v)
                setTimeout(() => {
                    $("#dt-pl_" + i).text(num_format(v))
                    removeLoadingEffect("#dt-pl_" + i, v);
                }, 500);

            });
            setDatePicker(pl.tgl_harga, "#dt-pl_tgl_harga")
            $("#dt-pl_keterangan").html(pl.keterangan || '-')
        }

    }

    // buka gambar embed di catatan pricelist dalam lightbox
    $(document).on('click', '#dt-pl_keterangan img', function() {
        $('#modal_image_lightbox_img').attr('src', $(this).attr('src'));
        $('#modal_image_lightbox').modal('show');
    });

    function loadKavling(r) {
        if (r.kavling) {
            setDatePicker(r.kavling.perintah_bangun_tgl, '#dt-st_perintah_bangun_tgl')
            changeVal("#dt-st_perintah_bangun_oleh", r.kavling.username)
            src = not_found
            if (r.kavling.perintah_bangun_access_url) {
                src = r.kavling.perintah_bangun_access_url
            }

            $("#dt-pph_ntpn").val(r.kavling.pph42_ntpn)
            $("#dt-pph_nominal_bayar").val(r.kavling.pph42_nilai).keyup()
            setDatePicker(r.kavling.pph42_tgl_bayar, "#dt-pph_tgl_bayar")

        }
        $("#dt-st_list-upload_perintah_bangun_file").prop('href', resolveFileHref(src))
    }

    function updateLegalBadge(id, value, positiveValues, positiveLabel, negativeLabel) {
        const $badge = $(id);
        if (positiveValues.includes(String(value))) {
            $badge.text(positiveLabel).removeClass('badge-secondary badge-danger').addClass('badge-success');
        } else {
            $badge.text(negativeLabel).removeClass('badge-success badge-danger').addClass('badge-secondary');
        }
    }

    function updateSp3kStatusBadge(sp3k) {
        const $badge = $('#dt-sp3k-status-badge');
        if (sp3k && sp3k != "0") {
            $badge.text('Disetujui').removeClass('badge-secondary badge-danger').addClass('badge-success');
        } else {
            $badge.text('Belum SP3K').removeClass('badge-success badge-danger').addClass('badge-secondary');
        }
    }

    function updateSp3kExpiryStyle(expDate) {
        const $el = $('#dt-st_sp3k_tgl_exp');
        if (!expDate || expDate === '0000-00-00') {
            $el.removeClass('detail-text-danger');
            return;
        }
        $el.toggleClass('detail-text-danger', new Date(expDate) < new Date());
    }

    function loadMKDT(mkdt) {
        src = not_found

        $("#dt-st_list-upload_sp3k_file").prop('href', resolveFileHref(src))

        $("#dt-st_list-upload_bast_file").prop('href', resolveFileHref(src))

        $("#dt-btn-bl_here").prop('href', resolveFileHref(src))
        $(".dt-cl-bl_here").prop('src', resolveFileHref(src))

        $("#dt-btn-npwp_here").prop('href', resolveFileHref(src))
        $(".dt-cl-npwp_here").prop('src', resolveFileHref(src))

        $("#dt-btn-ktp_here").prop('href', resolveFileHref(src))
        $(".dt-cl-ktp_here").prop('src', resolveFileHref(src))

        if (mkdt) {
            //load price list dari keuangan
            setDatePicker(mkdt.tgl_harga, "#dt-tgl_harga")
            $("#dt-hargajual").text(num_format(mkdt.harga_jual))
            $("#dt-harga_diskon_hargajual").text(num_format(mkdt.harga_diskon_hargajual))
            $("#dt-hargajual_net").text(num_format(mkdt.harga_jual_net))
            $("#dt-kpr").text(num_format(mkdt.harga_kpr))
            $("#dt-uang_muka").text(num_format(mkdt.harga_uang_muka))
            $("#dt-harga_diskon_uang_muka").text(num_format(mkdt.harga_diskon_uang_muka))
            $("#dt-biaya_adm").text(num_format(mkdt.harga_administrasi))
            $("#dt-bphtb").text(num_format(mkdt.harga_bphtb))
            $("#dt-ppn").text(num_format(mkdt.harga_ppn))
            $("#dt-biaya_proses").text(num_format(mkdt.harga_biaya_proses))
            changeVal("#dt-row", mkdt.row)
            changeVal("#dt-tipe", mkdt.tipe)
            changeVal("#dt-lt", mkdt.lb)
            changeVal("#dt-lb", mkdt.lt)
            changeVal("#dt-is_ajb", mkdt.is_ajb)
            changeVal("#dt-notaris", mkdt.notaris)



            //kpr disetujui
            $("#dt-st_harga_kpr_acc").text(num_format(mkdt.harga_kpr_acc))
            $("#dt-st_harga_penambahan_um").text(num_format(mkdt.harga_penambahan_um))
            $("#dt-st_harga_penambahan").text(num_format(mkdt.harga_penambahan))
            $("#dt-st_harga_penambahan_tanah").text(num_format(mkdt.harga_penambahan_tanah))
            changeVal("#dt-st_keterangan_harga_penambahan", mkdt.keterangan_harga_penambahan)

            //status
            changeVal('#dt-status_mkdt', mkdt.status_mkdt)
            setDatePicker(mkdt.booking_tgl, '#dt-st_booking_tgl')
            changeVal('#dt-st_bank', mkdt.bank)

            changeVal('#dt-st_booking_fee', mkdt.booking_fee)

            $("#dt-st_wawancara").prop('checked', mkdt.wawancara)
            setDatePicker(mkdt.wawancara_tgl, '#dt-st_wawancara_tgl')

            changeVal('#dt-st_harga_kpr', mkdt.harga_kpr)
            changeVal('#dt-st_acc_harga_kpr', mkdt.harga_kpr_acc)
            changeVal('#dt-st_harga_turun_kpr', parseFloat(mkdt.harga_kpr) - parseFloat(mkdt.harga_kpr_acc))
            changeVal('#dt-st_sp3k_no', mkdt.sp3k_no)
            setDatePicker(mkdt.sp3k_tgl, "#dt-st_sp3k_tgl")
            setDatePicker(mkdt.sp3k_tgl_exp, "#dt-st_sp3k_tgl_exp")

            $("#dt-sp3k").prop('checked', mkdt.sp3k == "0" || !mkdt.sp3k ? 0 : 1)
            updateSp3kStatusBadge(mkdt.sp3k)
            updateSp3kExpiryStyle(mkdt.sp3k_tgl_exp)

            src = not_found

            if (mkdt.sp3k_access_url) {
                src = mkdt.sp3k_access_url
            }
            $("#dt-st_list-upload_sp3k_file").prop('href', resolveFileHref(src))

            src = not_found
            if (mkdt.ktp_access_url) {
                src = mkdt.ktp_access_url
            }
            $("#dt-btn-ktp_here").prop('href', resolveFileHref(src))
            $(".dt-cl-ktp_here").prop('src', resolveFileHref(src))
            setText('#dt-doc-ktp_name', detailDocFilename(mkdt.ktp_access_url))

            src = not_found
            if (mkdt.npwp_access_url) {
                src = mkdt.npwp_access_url
            }
            $("#dt-btn-npwp_here").prop('href', resolveFileHref(src))
            $(".dt-cl-npwp_here").prop('src', resolveFileHref(src))
            setText('#dt-doc-npwp_name', detailDocFilename(mkdt.npwp_access_url))

            src = not_found
            if (mkdt.data_diri_access_url) {
                src = mkdt.data_diri_access_url
            }
            $("#dt-btn-bl_here").prop('href', resolveFileHref(src))
            $(".dt-cl-bl_here").prop('src', resolveFileHref(src))
            setText('#dt-doc-bl_name', detailDocFilename(mkdt.data_diri_access_url))





            setDatePicker(mkdt.rencana_akad_tgl, '#dt-st_rencana_akad_tgl')
            $("#dt-st_akad").prop('checked', mkdt.akad == "0" ? 0 : 1)
            setDatePicker(mkdt.akad_tgl, '#dt-st_akad_tgl')

            changeVal("#dt-st_debitur_no", mkdt.debitur_no)
            changeVal("#dt-st_bast_no", mkdt.bast_no)

            src = not_found
            if (mkdt.bast_access_url) {
                src = mkdt.bast_access_url
            }
            $("#dt-st_list-upload_bast_file").prop('href', resolveFileHref(src))

            // $("#last_update_legal").html(`Terakhir dipudate oleh: ${lg.uadd_by}, pada: ${format_datetime(lg.created_at)} `);
            // if(lg.uedit_by){
            //     $("#last_update_legal").html(`Terakhir dipudate oleh: -${lg.uedit_by}, pada: ${format_datetime(lg.updated_at)} `);
            // }
        }
    }


    function loadLegal(lg) {
        /************************ load table legal ***************************/
        if (lg) {
            for (let i in lg) {
                $("#dt-" + i).val(lg[i]).change();
            }

            if (lg.data) {
                $("#dt-sertifikat_balik_nama").val(lg.data.nama_konsumen ? lg.data.nama_konsumen : '')
                $("#dt-pbb_balik_nama").val(lg.data.nama_konsumen ? lg.data.nama_konsumen : '')
                $("#dt-bphtb_nominal_disetujui").val(lg.data.harga_bphtb ? lg.data.harga_bphtb : '').change().keyup()
            }

            setDatePicker(lg.sertifikat_split_tanggal_terbit, "#dt-sertifikat_split_tanggal_terbit")
            setDatePicker(lg.sertifikat_split_tanggal_berakhir, "#dt-sertifikat_split_tanggal_berakhir")
            setDatePicker(lg.sertifikat_split_tanggal_surat_ukur, "#dt-sertifikat_split_tanggal_surat_ukur")
            setDatePicker(lg.sertifikat_balik_nama_tgl_pengiriman, "#dt-sertifikat_balik_nama_tgl_pengiriman")
            setDatePicker(lg.pbb_pecah_tanggal_bayar, "#dt-pbb_pecah_tanggal_bayar")

            setDatePicker(lg.bphtb_tanggal_verifikasi, "#dt-bphtb_tanggal_verifikasi")
            setDatePicker(lg.bphtb_jatuh_tempo, "#dt-bphtb_jatuh_tempo")
            setDatePicker(lg.bphtb_perpanjang_jatuh_tempo, "#dt-bphtb_perpanjang_jatuh_tempo")
            setDatePicker(lg.bphtb_tanggal_pembayaran, "#dt-bphtb_tanggal_pembayaran")
            setDatePicker(lg.bphtb_tanggal_validasi, "#dt-bphtb_tanggal_validasi")

            setDatePicker(lg.pph_tgl_permohonan, "#dt-pph_tgl_permohonan")
            setDatePicker(lg.pph_tanggal_validasi, "#dt-pph_tanggal_validasi")
            setDatePicker(lg.pph_tgl_bayar, "#dt-pph_tgl_bayar")
            setDatePicker(lg.ajb_tanggal, "#dt-ajb_tanggal")
            setDatePicker(lg.ajb_tanggal_dikirim, "#dt-ajb_tanggal_dikirim")
            setDatePicker(lg.ppjb_tanggal, "#dt-ppjb_tanggal")

            setDatePicker(lg.pbb_balik_nama_tgl_pengiriman, "#dt-pbb_balik_nama_tgl_pengiriman")
            setDatePicker(lg.pbb_tgl_pembetulan, "#dt-pbb_tgl_pembetulan")
            setDatePicker(lg.pbg_tanggal_kirim, "#dt-pbg_tanggal_kirim")
            setDatePicker(lg.pph_tgl_selesai, "#dt-pph_tgl_selesai")

            $("#dt-legal_keterangan").val(lg.keterangan);

            updateLegalBadge('#dt-sertifikat_is_split-badge', lg.sertifikat_is_split, ['1'], 'Split', 'Tidak Split');
            updateLegalBadge('#dt-sertifikat_is_balik_nama-badge', lg.sertifikat_is_balik_nama, ['Sudah'], 'Sudah', 'Belum');
            updateLegalBadge('#dt-pbb_is_balik_nama-badge', lg.pbb_is_balik_nama, ['Sudah'], 'Sudah', 'Belum');
            updateLegalBadge('#dt-pbb_is_pembetulan-badge', lg.pbb_is_pembetulan, ['Iya'], 'Ada', 'Tidak Ada');
            updateLegalBadge('#dt-pbg_is_revisi-badge', lg.pbg_is_revisi, ['Ya'], 'Revisi', 'Tidak Revisi');

            const $pbgBadge = $('#dt-pbg_status-badge');
            const pbgStatus = lg.pbg_status || '';
            $pbgBadge.text(pbgStatus || '-')
                .removeClass('badge-success badge-secondary badge-danger')
                .addClass(pbgStatus === 'Selesai' ? 'badge-success' : pbgStatus === 'Terjadi Masalah' ? 'badge-danger' : 'badge-secondary');

            last_update("#last_update_legal", lg.uadd_by, lg.created_at)
            if (lg.uedit_by) {
                last_update("#last_update_legal", lg.uedit_by, lg.updated_at)
            }

        }

        /************************ end of table legal ***************************/
    }

    function hitungTagihan(r) {
        const hitungMetricTagihan = (total, bayar) => {
            total = parseFloat(total) || 0;
            bayar = parseFloat(bayar) || 0;

            const sisa = Math.max(total - bayar, 0);
            const persen = total > 0 ? Math.min((bayar / total) * 100, 100) : (bayar > 0 ? 100 : 0);

            return {
                total,
                bayar,
                sisa,
                persen: bayar > 0 ? ~~persen + "%" : "0%"
            };
        };

        let um = hitungMetricTagihan(r.total_um, r.sb_um);
        let um_ll = hitungMetricTagihan(r.total_um_ll, r.sb_um_ll);
        let bb = hitungMetricTagihan(r.total_bb, r.sb_bb);

        let total_um = um.total
        let sb_um = um.bayar
        let sisa_um = um.sisa,
            ldp = um.persen;

        let total_um_ll = um_ll.total,
            sb_um_ll = um_ll.bayar
        let sisa_um_ll = um_ll.sisa,
            ldp_ll = um_ll.persen;

        let total_bb = bb.total
        let sb_bb = bb.bayar
        let sisa_bb = bb.sisa,
            ldp_bb = bb.persen;

        let total_semua = parseFloat(r.total_tagihan_semua) || 0,
            sb_semua = parseFloat(r.sudah_bayar_semua) || 0,
            tagihan_semua = hitungMetricTagihan(total_semua, sb_semua),
            sisa_semua = tagihan_semua.sisa,
            ldp_semua = tagihan_semua.persen;
        // console.log(total_semua, sb_semua)
        return {
            sisa_um: sisa_um,
            ldp: ldp,
            total_um: total_um,
            sb_um: sb_um,
            sisa_um_ll: sisa_um_ll,
            ldp_ll: ldp_ll,
            sb_um_ll: sb_um_ll,
            total_um_ll: total_um_ll,
            total_bb: total_bb,
            sb_bb: sb_bb,
            sisa_bb: sisa_bb,
            ldp_bb: ldp_bb,
            total_semua: total_semua,
            sb_semua: sb_semua,
            sisa_semua: sisa_semua,
            ldp_semua: ldp_semua

        }
    }

    function loadTagihan(r) {
        /************************ load table tagihan ***************************/

        let tagihan = hitungTagihan(r);

        changeVal("#dt-total_tagihan_semua", tagihan.total_semua)
        changeVal("#dt-sudah_bayar_semua", tagihan.sb_semua)
        renderTagihanSummaryCard(tagihan.total_semua, tagihan.sb_semua)

        if (r.ku)
            $("#last_update_keuangan").html("Terakhir diupdate oleh: " + r.ku.username + " pada: " + format_datetime(r.ku.created_at));
        /************************ end of load table tagihan ***************************/
    }

    function loadProduksi(pr, files) {
        /************************ load produksi ***************************/
        if (pr) {
            $("#dt-st_0").prop('checked', pr.st_0)
            $("#dt-st_25").prop('checked', pr.st_25)
            $("#dt-st_50").prop('checked', pr.st_50)
            $("#dt-st_75").prop('checked', pr.st_75)
            $("#dt-st_100").prop('checked', pr.st_100)
            $("#dt-st_saluran").prop('checked', pr.st_saluran)
            $("#dt-st_jalan").prop('checked', pr.st_jalan)
            $("#dt-st_air").prop('checked', pr.st_air)
            $("#dt-bp").prop('checked', pr.bp)
            $("#dt-lpa").prop('checked', pr.lpa)
            $("#dt-slo").prop('checked', pr.slo)
            $("#dt-sumurbor").prop('checked', pr.sumurbor)

            setDatePicker(pr.lpa_tanggal, "#dt-lpa_tanggal")

            setDatePicker(pr.tanggal_pembangunan, '#dt-tanggal_pembangunan')
            setDatePicker(pr.tanggal_rencana_selesai_pembangunan, '#dt-tanggal_rencana_selesai_pembangunan')
            setDatePicker(pr.tanggal_selesai_pembangunan, '#dt-tanggal_selesai_pembangunan')

            $("#dt-progres_bangunan").val(pr.progres_bangunan)
            setText("#dt-t_progres_bangunan", pr.progres_bangunan)
            const progress = detailPercentValue(pr.progres_bangunan)
            $("#dt-produksi-progress-summary").text(progress)
            $("#dt-produksi-progress-bar")
                .css('width', `${progress}%`)
                .toggleClass('is-empty', progress <= 0)
                .toggleClass('is-partial', progress > 0 && progress < 100)

            $("#dt-produksi_keterangan").val(pr.keterangan)

            changeVal("#dt-air_jenis", pr.air_jenis);
            changeVal("#dt-listrik_jenis", pr.listrik_jenis);

            changeVal("#dt-listrik_pln", pr.listrik_pln);
            changeVal("#dt-listrik_disediakan_no", pr.listrik_disediakan_no);
            changeVal("#dt-listrik_disediakan_tanggal", pr.listrik_disediakan_tanggal);
            changeVal("#dt-air_deskripsi_unit", pr.air_deskripsi_unit);
            changeVal("#dt-air_pdam_no", pr.air_pdam_no);
            $("#dt-produksi-tanggal-bangun").text(pr.tanggal_pembangunan ? format_date(pr.tanggal_pembangunan) : '-')
            $("#dt-produksi-tanggal-selesai").text(pr.tanggal_selesai_pembangunan ? format_date(pr.tanggal_selesai_pembangunan) : '-')
            $("#dt-produksi-listrik-summary").text(isSudah(pr.st_jalan).replace(/<[^>]*>/g, ''))
            $("#dt-produksi-air-summary").text(isSudah(pr.st_air).replace(/<[^>]*>/g, ''))


            changeVal("#dt-sumurbor_keterangan", pr.sumurbor_keterangan);
            setDatePicker(pr.sumurbor_tanggal, '#dt-sumurbor_tanggal')
            $("#dt-last_update-sumurbor").html(
                `Diubah pada: ${pr.sumurbor_updated ? format_datetime(pr.sumurbor_updated) : '-'},
                    oleh: ${pr.sumurbor_oleh_u ? pr.sumurbor_oleh_u : '-'}`
            )

        } else {
            $("#dt-st_0").prop('checked', false)
            $("#dt-st_25").prop('checked', false)
            $("#dt-st_50").prop('checked', false)
            $("#dt-st_75").prop('checked', false)
            $("#dt-st_100").prop('checked', false)
            $("#dt-st_saluran").prop('checked', false)
            $("#dt-st_jalan").prop('checked', false)
            $("#dt-st_air").prop('checked', false)
            $("#dt-bp").prop('checked', false)
            $("#dt-lpa").prop('checked', false)
            $("#dt-slo").prop('checked', false)
            $("#dt-sumurbor").prop('checked', false)

            setDatePicker(null, "#dt-lpa_tanggal")

            setDatePicker(null, '#dt-tanggal_pembangunan')
            setDatePicker(null, '#dt-tanggal_rencana_selesai_pembangunan')
            setDatePicker(null, '#dt-tanggal_selesai_pembangunan')

            $("#dt-progres_bangunan").val(null)
            setText("#dt-t_progres_bangunan", null)
            $("#dt-produksi-progress-summary").text('0')
            $("#dt-produksi-progress-bar").css('width', '0%').addClass('is-empty').removeClass('is-partial')

            $("#dt-produksi_keterangan").val(null)

            changeVal("#dt-air_jenis", null);
            changeVal("#dt-listrik_jenis", null);

            changeVal("#dt-listrik_pln", null);
            changeVal("#dt-listrik_disediakan_no", null);
            changeVal("#dt-listrik_disediakan_tanggal", null);
            changeVal("#dt-air_deskripsi_unit", null);
            changeVal("#dt-air_pdam_no", null);
            $("#dt-produksi-tanggal-bangun").text('-')
            $("#dt-produksi-tanggal-selesai").text('-')
            $("#dt-produksi-listrik-summary").text('-')
            $("#dt-produksi-air-summary").text('-')

            changeVal("#dt-sumurbor_keterangan", null);
            setDatePicker(null, '#dt-sumurbor_tanggal')
            $("#dt-last_update-sumurbor").html(null)
        }
        // console.log(files)
        if (files)
            showFoto(files, 'dt-', "false");


        /************************ end of produksi ***************************/
    }
    /************************ load bukti bayar pajak  ***************************/
    function loadBuktiBayarPajak(r) {

        let dv = ''
        $.each(r.file_pph, function(i, v) {
            const href = v.access_url || file_url('file_upload', v.id);
            dv += `
                <div class="detail-file-tile">
                    <a class="detail-file-preview" href="${href}" target="_blank">
                        <embed src="${href}" class="files-here dt-cl-ktp_here">
                    </a>
                    <div class="detail-file-body">
                        <div class="detail-file-title">Bukti pembayaran ${v.default_filename}</div>
                        <div class="detail-file-meta">${v.keterangan || '-'}</div>
                        <div class="detail-file-meta">Diunggah ${format_datetime(v.upload_at)} (${v.uupload_by || '-'})</div>
                        <div class="detail-file-action">
                            <a href="${href}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Lihat
                            </a>
                        </div>
                    </div>
                </div>
                 `;
        });
        $("#dt-file_pph42-here").html(dv)

        dv = '';
        $.each(r.file_ppn, function(i, v) {
            const href = v.access_url || file_url('file_upload', v.id);
            dv += `
                <div class="detail-file-tile">
                    <a class="detail-file-preview" href="${href}" target="_blank">
                        <embed src="${href}" class="files-here dt-cl-ktp_here">
                    </a>
                    <div class="detail-file-body">
                        <div class="detail-file-title">Bukti pembayaran ${v.default_filename}</div>
                        <div class="detail-file-meta">${v.keterangan || '-'}</div>
                        <div class="detail-file-meta">Diunggah ${format_datetime(v.upload_at)} (${v.uupload_by || '-'})</div>
                        <div class="detail-file-action">
                            <a href="${href}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Lihat
                            </a>
                        </div>
                    </div>
                </div>
                 `;
        });
        $("#dt-file_ppn-here").html(dv)

    }

    function loadCashOut(d) {
        let total = 0;
        const rows = Array.isArray(d) ? d : [];
        $("#dt-cashout-table tbody").html("");

        if (rows.length == 0) {
            $("#dt-cashout-table tbody").html(`<tr><td colspan='4' class='text-center'>Data tidak ditemukan</td></tr>`)
        }

        $.each(rows, function(i, val) {
            total += detailMoneyValue(val.nominal);
            let row = `
        <tr>
            <td>${val.item}</td>
            <td>${format_date(val.tanggal_bayar) ?? "-"}</td>
            <td>${num_format(val.nominal) ?? "0"}</td>
            <td>${val.keterangan ?? "-"}</td>
        </tr>`;
            $("#dt-cashout-table tbody").append(row);
        });

        $("#dt-cashout-summary-total").text(detailRupiah(total));
        $("#dt-cashout-summary-count").text(`${rows.length} item`);
        $("#dt-cashout-summary-status").text(rows.length > 0 ? 'Ada pembayaran' : 'Belum ada data');
    }

    function loadFinanceFlow(data) {
        const incomeRows = Array.isArray(data.income_rows) ? data.income_rows : [];
        const expenseRows = Array.isArray(data.expense_rows) ? data.expense_rows : [];
        const incomeTotal = detailMoneyValue(data.income_total);
        const expenseTotal = detailMoneyValue(data.expense_total);

        $("#dt-finance-income-total").text(detailRupiah(incomeTotal));
        $("#dt-finance-expense-total").text(detailRupiah(expenseTotal));
        $("#dt-finance-income-count").text(`${incomeRows.length} transaksi`);
        $("#dt-finance-expense-count").text(`${expenseRows.length} transaksi`);

        renderFinanceFlowTable("#dt-finance-income-table tbody", incomeRows, 'income');
        renderFinanceFlowTable("#dt-finance-expense-table tbody", expenseRows, 'expense');
    }

    function renderFinanceFlowTable(target, rows, type) {
        const $tbody = $(target);
        $tbody.empty();

        if (!rows.length) {
            $tbody.html(`<tr><td colspan="3" class="text-center">Data tidak ditemukan</td></tr>`);
            return;
        }

        rows.slice(0, 10).forEach(function(row) {
            const date = row.tanggal_transaksi || row.tanggal_bayar;
            const label = row.label || row.item;
            $tbody.append(`
                <tr>
                    <td>${date ? format_date(date) : '-'}</td>
                    <td>${detailEscapeHtml(label)}</td>
                    <td class="text-right">${detailRupiah(row.nominal)}</td>
                </tr>
            `);
        });
    }

    function loadBayarProduksi(bprod) {
        const hasData = Array.isArray(bprod) && bprod.length > 0;
        $('#detail-cashout-produksi-wrap').toggleClass('d-none', !hasData);

        const $tbody = $('#dt-div-bayar_produksi-here');
        $tbody.empty();
        if (!hasData) return;

        bprod.forEach(function(v) {
            $tbody.append(`
                <tr>
                    <td>${detailEscapeHtml(v.item)}</td>
                    <td>${v.tanggal_bayar ? format_date(v.tanggal_bayar) : '-'}</td>
                    <td class="text-right">${detailRupiah(v.nominal)}</td>
                    <td>${detailEscapeHtml(v.keterangan)}</td>
                </tr>
            `);
        });
    }
