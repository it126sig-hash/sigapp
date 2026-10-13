(function (window, $) {
  'use strict';

  if (!$) return;

  function getActiveProyekId() {
    if (typeof window.activeProyekId === 'function') {
      const id = Number(window.activeProyekId());
      if (id > 0) return id;
    }
    if (window.dt_proyek && Number(window.dt_proyek.id_proyek) > 0) {
      return Number(window.dt_proyek.id_proyek);
    }
    if (window.SIGAPP && Number(window.SIGAPP.activeProyekId) > 0) {
      return Number(window.SIGAPP.activeProyekId);
    }
    return 0;
  }

  function canManageMkdt() {
    const r = Number((typeof roleid !== 'undefined' ? roleid : window.roleid) || 0);
    return r === 1 || r === 4;
  }

  function canInputNup() {
    const r = Number((typeof roleid !== 'undefined' ? roleid : window.roleid) || 0);
    return [1, 3, 4, 8].includes(r);
  }

  function escHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatRupiah(val) {
    const n = Number(String(val ?? 0).replace(/[^\d.-]/g, '')) || 0;
    if (typeof window.num_format === 'function') {
      return 'Rp ' + window.num_format(n);
    }
    return 'Rp ' + n.toLocaleString('id-ID');
  }

  function formatNumberInput(val) {
    const clean = String(val ?? '').replace(/[^\d]/g, '');
    if (!clean) return '';
    return Number(clean).toLocaleString('en-US');
  }

  function formatDateDisplay(val) {
    if (!val || val === '0000-00-00') return '-';
    if (typeof window.format_date === 'function') {
      try {
        return window.format_date(val);
      } catch (e) {}
    }
    return escHtml(val);
  }

  function formatDateTimeDisplay(val) {
    if (!val) return '-';
    if (typeof window.format_datetime === 'function') {
      try {
        return window.format_datetime(val);
      } catch (e) {}
    }
    return escHtml(val);
  }

  function resolveActiveSelection() {
    let sh = (typeof editdtt !== 'undefined' && Array.isArray(editdtt) && editdtt[0])
      ? editdtt[0]
      : ((window.editdtt && Array.isArray(window.editdtt) && window.editdtt[0]) ? window.editdtt[0] : null);

    let idKavling = 0;
    if (sh && sh.data && Number(sh.data.id_kavling || 0) > 0) {
      idKavling = Number(sh.data.id_kavling);
    } else if (sh && sh.id && String(sh.id).toLowerCase().indexOf('kav') === 0) {
      idKavling = Number(String(sh.id).replace(/^kav/i, '') || 0);
    }
    if (!idKavling) {
      const domKav = Number(
        $('#modal-isi_data_konsumen.show .id_kavling').first().val() ||
        $('#modal_divisi4.show .id_kavling').first().val() ||
        $('.id_kavling').first().val() ||
        0
      );
      if (domKav > 0) idKavling = domKav;
    }

    let idMkdt = 0;
    if (sh && sh.data && Number(sh.data.id_mkdt || 0) > 0) {
      idMkdt = Number(sh.data.id_mkdt);
    }
    if (!idMkdt) {
      const domMkdt = Number(
        ($('#modal-isi_data_konsumen').hasClass('show') && $('#idk-id_mkdt').val()) ||
        ($('#modal_divisi4').hasClass('show') && $('#id_mkdt').val()) ||
        $('#idk-id_mkdt').val() ||
        $('#id_mkdt').val() ||
        $('.id_mkdt').first().val() ||
        0
      );
      if (domMkdt > 0) idMkdt = domMkdt;
    }

    return { sh: sh, idKavling: idKavling, idMkdt: idMkdt };
  }

  function forceCloseModal(selector) {
    const $m = $(selector);
    if (!$m.length) return;
    if (typeof window.removeModalListener === 'function') {
      window.removeModalListener(selector);
    }
    $m.off('hide.bs.modal');
    $m.data('isConfirmedClose', true);
    $m.modal('hide');
  }

  const NUP = {
    pendingTransferContext: null,
    cachedList: [],
    selectedKavlingData: null,

    init: function () {
      const self = this;

      $(document).on('input', '#nup-booking_fee', function () {
        if (this.readOnly) return;
        const formatted = formatNumberInput($(this).val());
        $(this).val(formatted);
      });

      $(document).on('change', '#form-nup .custom-file-input, #form-tarik-nup .custom-file-input, #pindah-kavling-file', function () {
        const fileName = this.files && this.files[0] ? this.files[0].name : 'Pilih file...';
        $(this).siblings('.custom-file-label').text(fileName);
      });

      $(document).on('change', '#filter-kavling-hargajual-nup', function () {
        const curr = Number($('#pilih-kavling-from_id_kavling').val() || 0);
        const last = Number($('#pilih-kavling-last_id_kavling').val() || 0);
        self.initKavlingSelect2(curr, last);
      });

      let searchTimer = null;
      $(document).on('input', '#modal-nup-search, #tab-nup-search', function () {
        const val = $(this).val();
        $('#modal-nup-search, #tab-nup-search').not(this).val(val);
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
          self.loadList(val);
        }, 250);
      });

      $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#list_nup') {
          self.loadList($('#tab-nup-search').val() || '');
        }
      });

      $('#modal-isi_data_konsumen').on('hidden.bs.modal', function () {
        self.clearPendingTransferBanner();
      });

      if ($('#tbody-tab-daftar-nup').length || $('#badge-nup-count').length) {
        self.loadList('');
      }
    },

    reloadAll: function () {
      const q = $('#modal-nup-search').val() || $('#tab-nup-search').val() || '';
      this.loadList(q);
      if ($.fn.DataTable && $.fn.DataTable.isDataTable('#data_tables')) {
        try {
          $('#data_tables').DataTable().ajax.reload(null, false);
        } catch (e) {}
      }
      if (typeof window.load_kavling === 'function') {
        try {
          window.load_kavling();
        } catch (e) {}
      }
    },

    openListModal: function () {
      $('#modal-daftar-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
      this.loadList($('#modal-nup-search').val() || '');
    },

    loadTabNupList: function () {
      this.loadList($('#tab-nup-search').val() || '');
    },

    loadList: function (searchQuery) {
      const self = this;
      const idProyek = getActiveProyekId();
      if (!idProyek) return;

      const $tbModal = $('#tbody-modal-daftar-nup');
      const $tbTab = $('#tbody-tab-daftar-nup');

      const loadingRow = '<tr><td colspan="11" class="text-center py-2"><i class="fa fa-spinner fa-spin mr-50"></i> Memuat daftar NUP...</td></tr>';
      if ($tbModal.length) $tbModal.html(loadingRow);
      if ($tbTab.length) $tbTab.html(loadingRow);

      $.ajax({
        url: base_url + 'api/nup/list',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_proyek: idProyek,
          search: searchQuery || ''
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          const rows = (res && Array.isArray(res.data)) ? res.data : [];
          self.cachedList = rows;

          $('#badge-nup-count, .badge-nup-count').text(rows.length);

          const html = self.renderListRows(rows);
          if ($tbModal.length) $tbModal.html(html);
          if ($tbTab.length) $tbTab.html(html);
        },
        error: function () {
          const errRow = '<tr><td colspan="11" class="text-center text-danger py-2">Gagal memuat data NUP.</td></tr>';
          if ($tbModal.length) $tbModal.html(errRow);
          if ($tbTab.length) $tbTab.html(errRow);
        }
      });
    },

    renderListRows: function (rows) {
      if (!rows || rows.length === 0) {
        return '<tr><td colspan="11" class="text-center text-muted py-2">Belum ada konsumen dengan status NUP pada proyek ini.</td></tr>';
      }

      const isMkdt = canManageMkdt();
      const canEdit = canInputNup();

      return rows.map(function (r, idx) {
        const isTarikan = String(r.nup_source || '').toUpperCase() === 'TARIKAN';
        const sourceBadge = isTarikan
          ? '<span class="badge badge-light-warning nup-badge-source"><i class="fas fa-undo-alt mr-25"></i> Tarikan Kavling</span>' +
            (r.last_kavling_label ? '<div class="small text-muted mt-25">Eks: <strong>' + escHtml(r.last_kavling_label) + '</strong></div>' : '')
          : '<span class="badge badge-light-primary nup-badge-source"><i class="fas fa-star mr-25"></i> NUP Baru</span>';

        const verifiedBadge = Number(r.booking_verified) === 1
          ? '<div class="small text-success"><i class="fa fa-check-circle"></i> Terverifikasi</div>'
          : '<div class="small text-warning"><i class="fa fa-clock"></i> Menunggu Verifikasi</div>';

        const files = [];
        if (r.file_ktp_url) {
          files.push('<a href="' + escHtml(r.file_ktp_url) + '" target="_blank" class="badge badge-light-info mr-25 mb-25"><i class="fa fa-id-card"></i> KTP</a>');
        }
        if (r.file_npwp_url) {
          files.push('<a href="' + escHtml(r.file_npwp_url) + '" target="_blank" class="badge badge-light-info mr-25 mb-25"><i class="fa fa-file-alt"></i> NPWP</a>');
        }
        if (r.file_nup_url) {
          files.push('<a href="' + escHtml(r.file_nup_url) + '" target="_blank" class="badge badge-light-primary mr-25 mb-25"><i class="fa fa-paperclip"></i> Bukti NUP</a>');
        }

        const actions = [];
        if (isMkdt) {
          actions.push(
            '<button type="button" class="btn btn-success btn-sm mb-25 btn-block" onclick="window.SIGAPP_NUP.openActivateNupModal(' + Number(r.id_mkdt) + ')" title="Pilih Kavling & Aktifkan menjadi Konsumen Aktif">' +
              '<i class="fas fa-check-circle mr-25"></i> Pilih Kavling / Aktifkan' +
            '</button>'
          );
        }
        if (canEdit) {
          actions.push(
            '<button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="window.SIGAPP_NUP.openFormModal(' + Number(r.id_mkdt) + ')" title="Lihat / Edit Data NUP">' +
              '<i class="fas fa-edit mr-25"></i> Detail / Edit' +
            '</button>'
          );
        }

        const noNupDisplay = escHtml(r.no_nup || '-');
        const noNupBadge = r.no_nup
          ? '<span class="badge badge-light-primary font-weight-bold">' + noNupDisplay + '</span>'
          : '<span class="text-muted small">-</span>';

        return (
          '<tr>' +
            '<td class="text-center">' + (idx + 1) + '</td>' +
            '<td class="text-center">' + noNupBadge + '</td>' +
            '<td>' + (actions.join('') || '-') + '</td>' +
            '<td>' + formatDateDisplay(r.booking_tgl) + '<div class="small text-muted">Oleh: ' + escHtml(r.diinput_oleh || '-') + '</div></td>' +
            '<td>' +
              '<div class="font-weight-bold">' + escHtml(r.nama_konsumen || '-') + '</div>' +
              (r.nik ? '<div class="small text-muted">NIK: ' + escHtml(r.nik) + '</div>' : '') +
              (r.npwp ? '<div class="small text-muted">NPWP: ' + escHtml(r.npwp) + '</div>' : '') +
            '</td>' +
            '<td>' +
              (r.hp_konsumen ? '<div><i class="fa fa-phone-alt text-muted mr-25"></i>' + escHtml(r.hp_konsumen) + '</div>' : '-') +
              (r.sales ? '<div class="small text-primary font-weight-bold">Sales: ' + escHtml(r.sales) + '</div>' : '') +
            '</td>' +
            '<td>' + sourceBadge + '</td>' +
            '<td><div class="font-weight-bold">' + formatRupiah(r.booking_fee) + '</div>' + verifiedBadge + '</td>' +
            '<td><div class="font-weight-bold text-success">' + formatRupiah(r.total_sudah_bayar) + '</div>' +
              (Number(r.total_angsuran_masuk) > 0 ? '<div class="small text-muted">Termasuk angsuran: ' + formatRupiah(r.total_angsuran_masuk) + '</div>' : '') +
            '</td>' +
            '<td style="max-width:240px; white-space:normal;">' + escHtml(r.catatan_nup || '-') + '</td>' +
            '<td>' + (files.length ? files.join(' ') : '<span class="text-muted small">-</span>') + '</td>' +
          '</tr>'
        );
      }).join('');
    },

    resetForm: function () {
      const form = document.getElementById('form-nup');
      if (form) form.reset();

      $('#nup-id_mkdt').val('');
      $('#nup-id_konsumen').val('');
      $('#nup-no_nup').val('');
      $('#nup-id_proyek').val(getActiveProyekId());
      $('#nup-booking_tgl').val(new Date().toISOString().slice(0, 10)).prop('readonly', false).removeClass('bg-light');
      $('#nup-booking_fee').val('').prop('readonly', false).removeClass('bg-light');
      $('#nup-booking-lock-hint').hide().text('');
      $('#nup-preview-file_ktp, #nup-preview-file_npwp, #nup-preview-file_data_diri, #nup-preview-file_nup').empty();
      $('#form-nup .custom-file-label').text('Pilih file...');
      $('#nup-form-history-card').hide();
      $('#nup-form-history-tbody').empty();
    },

    openFormModal: function (idMkdt) {
      const self = this;
      self.resetForm();

      if (!idMkdt) {
        $('#modal-form-nup-title').text('Input Konsumen NUP Baru');
        $('#modal-form-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
        return;
      }

      $('#modal-form-nup-title').text('Edit / Detail Konsumen NUP');
      $.ajax({
        url: base_url + 'api/nup/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          if (!res || !res.success || !res.data) {
            return Swal.fire('Gagal', (res && res.messages) || 'Data NUP tidak ditemukan.', 'error');
          }

          const d = res.data;
          $('#nup-id_mkdt').val(d.id_mkdt || '');
          $('#nup-no_nup').val(d.no_nup || '');
          $('#nup-id_konsumen').val(d.id_konsumen || '');
          $('#nup-id_proyek').val(d.id_proyek || getActiveProyekId());
          $('#nup-booking_tgl').val(d.booking_tgl || '');
          $('#nup-booking_fee').val(formatNumberInput(Math.round(Number(d.booking_fee || 0))));
          $('#nup-catatan_nup').val(d.catatan_nup || d.keterangan || '');

          const locked = d.booking_state && d.booking_state.is_verified;
          $('#nup-booking_tgl, #nup-booking_fee').prop('readonly', Boolean(locked)).toggleClass('bg-light', Boolean(locked));
          if (locked) {
            $('#nup-booking-lock-hint').text('Booking fee sudah diverifikasi Keuangan dan terkunci.').show();
          }

          $('#nup-nama_konsumen').val(d.nama_konsumen || '');
          $('#nup-nik_konsumen').val(d.nik || '');
          $('#nup-npwp_konsumen').val(d.npwp || '');
          $('#nup-hp_konsumen').val(d.hp_konsumen || '');
          $('#nup-email_konsumen').val(d.email_konsumen || '');
          $('#nup-sales').val(d.sales || '');
          $('#nup-alamat_konsumen').val(d.alamat_konsumen || '');
          $('#nup-alamat_surat').val(d.alamat_surat || '');
          $('#nup-pekerjaan').val(d.pekerjaan || 'Pegawai');
          $('#nup-nama_instansi').val(d.nama_instansi || '');
          $('#nup-bidang_pekerjaan').val(d.bidang_pekerjaan || '');
          $('#nup-lama_bekerja').val(d.lama_bekerja || '');
          $('#nup-tel_instansi').val(d.tel_instansi || '');
          $('#nup-email_instansi').val(d.email_instansi || '');
          $('#nup-alamat_instansi').val(d.alamat_instansi || '');
          $('#nup-status_pernikahan').val(d.status_pernikahan || 'Belum Menikah');
          $('#nup-nama_pasangan').val(d.nama_pasangan || '');
          $('#nup-nik_pasangan').val(d.nik_pasangan || '');
          $('#nup-hp_pasangan').val(d.hp_pasangan || '');
          $('#nup-status_pekerjaan_pasangan').val(d.status_pekerjaan_pasangan || 'Tidak Bekerja');
          $('#nup-instansi_pasangan').val(d.instansi_pasangan || '');
          $('#nup-no_spptb').val(d.no_spptb || '');

          if (d.file_ktp_url) {
            $('#nup-preview-file_ktp').html('<a href="' + escHtml(d.file_ktp_url) + '" target="_blank" class="btn btn-outline-info btn-sm"><i class="fa fa-eye"></i> Lihat File KTP Tersimpan</a>');
          }
          if (d.file_npwp_url) {
            $('#nup-preview-file_npwp').html('<a href="' + escHtml(d.file_npwp_url) + '" target="_blank" class="btn btn-outline-info btn-sm"><i class="fa fa-eye"></i> Lihat File NPWP Tersimpan</a>');
          }
          if (d.file_data_diri_url) {
            $('#nup-preview-file_data_diri').html('<a href="' + escHtml(d.file_data_diri_url) + '" target="_blank" class="btn btn-outline-info btn-sm"><i class="fa fa-eye"></i> Lihat File Data Diri Tersimpan</a>');
          }
          if (d.file_nup_url) {
            $('#nup-preview-file_nup').html('<a href="' + escHtml(d.file_nup_url) + '" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa fa-eye"></i> Lihat Lampiran NUP Tersimpan</a>');
          }

          if (Array.isArray(d.kavling_history) && d.kavling_history.length > 0) {
            $('#nup-form-history-card').show();
            $('#nup-form-history-tbody').html(
              d.kavling_history.map(function (h, i) {
                const fileLink = h.file_lampiran_url
                  ? '<div class="mt-25"><a href="' + escHtml(h.file_lampiran_url) + '" target="_blank" class="badge badge-light-primary"><i class="fa fa-paperclip"></i> Lihat Lampiran</a></div>'
                  : '';
                return (
                  '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td><span class="badge badge-light-info">' + escHtml(h.action_label || h.action_type) + '</span></td>' +
                    '<td>' + escHtml(h.from_kavling_label || '-') + '</td>' +
                    '<td>' + escHtml(h.to_kavling_label || '-') + '</td>' +
                    '<td>' + formatRupiah(h.nominal_sudah_bayar_snapshot || 0) + '</td>' +
                    '<td>' + escHtml(h.catatan || '-') + fileLink + '</td>' +
                    '<td>' + escHtml(h.actor_name || '-') + '<div class="small text-muted">' + formatDateTimeDisplay(h.created_at) + '</div></td>' +
                  '</tr>'
                );
              }).join('')
            );
          }

          $('#modal-form-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
        },
        error: function () {
          Swal.fire('Error', 'Gagal mengambil detail konsumen NUP.', 'error');
        }
      });
    },

    submitForm: function () {
      const self = this;
      const formEl = document.getElementById('form-nup');
      if (!formEl) return;

      const noNup = $.trim($('#nup-no_nup').val());
      const nama = $.trim($('#nup-nama_konsumen').val());
      const tgl = $.trim($('#nup-booking_tgl').val());
      const fee = Number(String($('#nup-booking_fee').val() || '').replace(/[^\d.-]/g, '')) || 0;
      const catatan = $.trim($('#nup-catatan_nup').val());

      if (!noNup) {
        return Swal.fire('Validasi', 'Nomor Urut Pemesanan (No NUP) wajib diisi manual.', 'warning');
      }
      if (!nama) {
        return Swal.fire('Validasi', 'Nama konsumen wajib diisi.', 'warning');
      }
      if (!tgl) {
        return Swal.fire('Validasi', 'Tanggal booking wajib diisi.', 'warning');
      }
      if (fee <= 0) {
        return Swal.fire('Validasi', 'Nominal Booking Fee wajib diisi lebih dari Rp 0.', 'warning');
      }
      if (!catatan) {
        return Swal.fire('Validasi', 'Catatan NUP wajib diisi.', 'warning');
      }

      $('#nup-id_proyek').val(getActiveProyekId());

      const fd = new FormData(formEl);
      fd.append(csrfName, csrfHash);

      const $btn = $('#btn-submit-form-nup');
      const oldHtml = $btn.html();
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-50"></i> Menyimpan...');

      $.ajax({
        url: base_url + 'api/nup/save',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          $btn.prop('disabled', false).html(oldHtml);

          if (!res || !res.success) {
            return Swal.fire('Gagal', (res && res.messages) || 'Gagal menyimpan data NUP.', 'error');
          }

          $('#modal-form-nup').modal('hide');
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: res.messages || 'Data NUP berhasil disimpan.',
            timer: 1800,
            showConfirmButton: false
          });
          self.reloadAll();
        },
        error: function (xhr) {
          $btn.prop('disabled', false).html(oldHtml);
          const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
          if (json && json.token) csrfHash = json.token;
          Swal.fire('Gagal', (json && json.messages) || 'Terjadi kesalahan saat menyimpan data NUP.', 'error');
        }
      });
    },

    openPullToNupModal: function () {
      const sel = resolveActiveSelection();
      const idKavling = sel.idKavling;
      const idMkdt = sel.idMkdt;
      const sh = sel.sh;

      if (!idKavling || !idMkdt) {
        return Swal.fire('Perhatian', 'Kavling ini belum memiliki transaksi konsumen aktif untuk ditarik ke NUP.', 'warning');
      }

      $('#tarik-nup-id_mkdt').val(idMkdt);
      $('#tarik-nup-id_kavling').val(idKavling);
      $('#tarik-nup-no_nup').val('');
      $('#tarik-nup-catatan').val('');
      $('#tarik-nup-file').val('');
      $('#form-tarik-nup .custom-file-label').text('Pilih file pendukung...');

      const kavLabel = (sh && sh.data && (sh.data.nama_jalan || sh.data.no_kavling))
        ? $.trim((sh.data.nama_jalan || '') + ' No. ' + (sh.data.no_kavling || '-'))
        : ('Kavling #' + idKavling);
      $('#tarik-nup-info-kavling').text(kavLabel);
      $('#tarik-nup-info-konsumen').text($('#idk-nama_konsumen').val() || $('#st-mkdt-nama_konsumen').text() || 'Memuat...');
      $('#tarik-nup-info-bayar').text('Memuat...');

      $.ajax({
        url: base_url + 'api/nup/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          if (res && res.data) {
            $('#tarik-nup-info-konsumen').text(res.data.nama_konsumen || '-');
            $('#tarik-nup-info-bayar').text(formatRupiah(res.data.total_sudah_bayar || res.data.booking_fee || 0));
            if (res.data.no_nup) {
              $('#tarik-nup-no_nup').val(res.data.no_nup);
            }
          }
        }
      });

      $('#modal-tarik-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
    },

    submitPullToNup: function () {
      const self = this;
      const idMkdt = Number($('#tarik-nup-id_mkdt').val() || 0);
      const idKavling = Number($('#tarik-nup-id_kavling').val() || 0);
      const noNup = $.trim($('#tarik-nup-no_nup').val());
      const catatan = $.trim($('#tarik-nup-catatan').val());

      if (!noNup) {
        return Swal.fire('Validasi', 'Nomor Urut Pemesanan (No NUP) wajib diisi manual.', 'warning');
      }
      if (!catatan) {
        return Swal.fire('Validasi', 'Catatan / alasan penarikan konsumen ke NUP wajib diisi.', 'warning');
      }

      Swal.fire({
        title: 'Tarik Konsumen ke NUP?',
        html: 'Status kavling akan menjadi <strong>Kosong</strong> di Siteplan, dan konsumen beserta seluruh pembayaran yang sudah masuk akan tersimpan di <strong>Daftar NUP</strong>.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tarik ke NUP',
        cancelButtonText: 'Batal'
      }).then(function (confirmRes) {
        if (!confirmRes.isConfirmed && !confirmRes.value) return;

        const formEl = document.getElementById('form-tarik-nup');
        const fd = new FormData(formEl);
        fd.append(csrfName, csrfHash);

        const $btn = $('#btn-submit-tarik-nup');
        const oldHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-50"></i> Memproses...');

        $.ajax({
          url: base_url + 'api/nup/pull',
          type: 'POST',
          data: fd,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (res) {
            if (res && res.token) csrfHash = res.token;
            $btn.prop('disabled', false).html(oldHtml);

            if (!res || !res.success) {
              return Swal.fire('Gagal', (res && res.messages) || 'Gagal menarik konsumen ke NUP.', 'error');
            }

            forceCloseModal('#modal-tarik-nup');
            forceCloseModal('#modal-isi_data_konsumen');
            forceCloseModal('#modal_divisi4');

            Swal.fire({
              icon: 'success',
              title: 'Berhasil Ditarik ke NUP',
              text: res.messages || 'Konsumen telah dipindahkan ke daftar NUP dan kavling telah dikosongkan.',
              timer: 2200,
              showConfirmButton: false
            });

            self.reloadAll();
          },
          error: function (xhr) {
            $btn.prop('disabled', false).html(oldHtml);
            const json = xhr && xhr.responseJSON ? xhr.responseJSON : null;
            if (json && json.token) csrfHash = json.token;
            Swal.fire('Gagal', (json && json.messages) || 'Terjadi kesalahan saat menarik konsumen ke NUP.', 'error');
          }
        });
      });
    },

    initKavlingSelect2: function (currentIdKavling, lastIdKavling) {
      const self = this;
      const $sel = $('#select-target-kavling-nup');
      if ($sel.hasClass('select2-hidden-accessible')) {
        $sel.select2('destroy');
      }
      $sel.empty().val(null);
      self.selectedKavlingData = null;

      $sel.select2({
        dropdownParent: $('#modal-pilih-kavling-nup'),
        placeholder: 'Ketik Nama Jalan / Blok atau Nomor Kavling...',
        allowClear: true,
        width: '100%',
        ajax: {
          url: base_url + 'api/nup/kavling-options',
          type: 'POST',
          dataType: 'json',
          delay: 200,
          data: function (params) {
            return {
              [csrfName]: csrfHash,
              id_proyek: getActiveProyekId(),
              search: params.term || '',
              current_id_kavling: currentIdKavling || 0,
              last_id_kavling: lastIdKavling || 0,
              filter_hargajual: $('#filter-kavling-hargajual-nup').val() || 'sudah'
            };
          },
          processResults: function (res) {
            if (res && res.token) csrfHash = res.token;
            const list = (res && Array.isArray(res.data)) ? res.data : [];
            return {
              results: list.map(function (item) {
                const label = $.trim((item.nama_jalan || '') + ' No. ' + (item.no_kavling || '-'));
                return $.extend({}, item, {
                  id: item.id_kavling,
                  text: label,
                  disabled: Boolean(item.is_occupied)
                });
              })
            };
          },
          cache: false
        },
        templateResult: function (item) {
          if (item.loading) return item.text;
          const label = escHtml($.trim((item.nama_jalan || '') + ' No. ' + (item.no_kavling || '-')));
          const tipe = item.tipe_rumah ? ' (' + escHtml(item.tipe_rumah) + ')' : '';

          if (item.is_current) {
            return $(
              '<div class="nup-kavling-opt-occupied">' +
                '<i class="fas fa-ban mr-50"></i>' + label + tipe +
                ' <span class="badge badge-danger ml-50">Kavling Saat Ini</span>' +
              '</div>'
            );
          }

          if (item.is_occupied) {
            const occInfo = item.active_konsumen
              ? 'Terisi: ' + escHtml(item.active_konsumen) + (item.status_mkdt ? ' [' + escHtml(item.status_mkdt) + ']' : '')
              : 'Sudah Terisi Konsumen Aktif';
            return $(
              '<div class="nup-kavling-opt-occupied">' +
                '<i class="fas fa-lock mr-50"></i>' + label + tipe +
                ' <span class="badge badge-light-danger ml-50">' + occInfo + '</span>' +
              '</div>'
            );
          }

          const priceBadge = item.has_hargajual
            ? ' <span class="badge badge-light-info ml-50" title="Harga Jual Tersedia">' + formatRupiah(item.nominal_hargajual || 0) + '</span>'
            : ' <span class="badge badge-light-secondary ml-50" title="Belum diset harga jual">Belum Ada Harga Jual</span>';

          const lastBadge = item.is_last_kavling
            ? ' <span class="badge badge-light-warning ml-50"><i class="fa fa-history"></i> Kavling Sebelumnya (Tersedia)</span>'
            : ' <span class="badge badge-light-success ml-50">Kosong / Tersedia</span>';

          return $(
            '<div class="nup-kavling-opt-available">' +
              '<i class="fas fa-check-circle text-success mr-50"></i>' + label + tipe + lastBadge + priceBadge +
            '</div>'
          );
        },
        templateSelection: function (item) {
          if (!item || !item.id) return item.text || '';
          const label = $.trim((item.nama_jalan || '') + ' No. ' + (item.no_kavling || '-'));
          const tipe = item.tipe_rumah ? ' (' + item.tipe_rumah + ')' : '';
          const price = item.has_hargajual ? ' [' + formatRupiah(item.nominal_hargajual || 0) + ']' : (item.nominal_hargajual ? '' : ' [Belum Ada Harga Jual]');
          return label + tipe + price;
        }
      });

      $sel.off('select2:select').on('select2:select', function (e) {
        self.selectedKavlingData = e.params.data || null;
      });
      $sel.off('select2:clear').on('select2:clear', function () {
        self.selectedKavlingData = null;
      });
    },

    openActivateNupModal: function (idMkdt) {
      const self = this;
      if (!canManageMkdt()) {
        return Swal.fire('Akses Ditolak', 'Aktivasi NUP ke kavling hanya dapat dilakukan oleh MKDT atau Administrator.', 'warning');
      }

      $('#pilih-kavling-mode').val('AKTIFKAN_NUP');
      $('#pilih-kavling-id_mkdt').val(idMkdt);
      $('#pilih-kavling-from_id_kavling').val('');
      $('#pilih-kavling-last_id_kavling').val('');
      $('#pilih-kavling-modal-title').text('Aktifkan NUP — Pilih Kavling Tujuan');
      $('#pindah-kavling-file').val('');
      $('#pindah-kavling-file').siblings('.custom-file-label').text('Pilih file pendukung (opsional)...');
      $('#pindah-kavling-catatan').val('');

      $.ajax({
        url: base_url + 'api/nup/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          if (!res || !res.success || !res.data) {
            return Swal.fire('Gagal', 'Data NUP tidak ditemukan.', 'error');
          }

          const d = res.data;
          const lastIdKav = Number(d.last_id_kavling || 0);
          $('#pilih-kavling-last_id_kavling').val(lastIdKav || '');
          $('#pilih-kavling-info-konsumen').text(d.nama_konsumen || '-');
          $('#pilih-kavling-info-asal').text(
            d.last_kavling_label
              ? 'NUP (Eks ' + d.last_kavling_label + ')'
              : 'NUP Baru (Belum Pernah Pilih Kavling)'
          );
          $('#pilih-kavling-info-bayar').text(formatRupiah(d.total_sudah_bayar || d.booking_fee || 0));
          $('#pindah-kavling-catatan').val(d.catatan_nup || '');

          self.initKavlingSelect2(0, lastIdKav);
          $('#modal-pilih-kavling-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
        }
      });
    },

    openPindahKavlingModal: function () {
      const self = this;
      if (!canManageMkdt()) {
        return Swal.fire('Akses Ditolak', 'Pindah Kavling hanya dapat dilakukan oleh MKDT atau Administrator.', 'warning');
      }

      const sel = resolveActiveSelection();
      const idKavling = sel.idKavling;
      const idMkdt = sel.idMkdt;
      const sh = sel.sh;

      if (!idKavling || !idMkdt) {
        return Swal.fire('Perhatian', 'Kavling ini belum memiliki konsumen aktif untuk dipindahkan.', 'warning');
      }

      const kavLabel = (sh && sh.data && (sh.data.nama_jalan || sh.data.no_kavling))
        ? $.trim((sh.data.nama_jalan || '') + ' No. ' + (sh.data.no_kavling || '-'))
        : ('Kavling #' + idKavling);

      $('#pilih-kavling-mode').val('PINDAH_KAVLING');
      $('#pilih-kavling-id_mkdt').val(idMkdt);
      $('#pilih-kavling-from_id_kavling').val(idKavling);
      $('#pilih-kavling-last_id_kavling').val(idKavling);
      $('#pilih-kavling-modal-title').text('Pindah Kavling Konsumen Aktif');
      $('#pilih-kavling-info-konsumen').text($('#idk-nama_konsumen').val() || $('#st-mkdt-nama_konsumen').text() || 'Memuat...');
      $('#pilih-kavling-info-asal').text(kavLabel);
      $('#pilih-kavling-info-bayar').text('Memuat...');
      $('#pindah-kavling-file').val('');
      $('#pindah-kavling-file').siblings('.custom-file-label').text('Pilih file surat pindah...');
      $('#pindah-kavling-catatan').val('');

      $.ajax({
        url: base_url + 'api/nup/detail',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          if (res && res.data) {
            $('#pilih-kavling-info-konsumen').text(res.data.nama_konsumen || '-');
            $('#pilih-kavling-info-bayar').text(formatRupiah(res.data.total_sudah_bayar || res.data.booking_fee || 0));
          }
        }
      });

      self.initKavlingSelect2(idKavling, idKavling);
      $('#modal-pilih-kavling-nup').modal({ backdrop: 'static', keyboard: false }).modal('show');
    },

    proceedToIsiDataKonsumen: function () {
      const self = this;
      const selected = self.selectedKavlingData;
      if (!selected || !selected.id_kavling) {
        return Swal.fire('Validasi', 'Silakan pilih kavling tujuan yang masih kosong terlebih dahulu.', 'warning');
      }
      if (selected.is_occupied) {
        return Swal.fire('Tidak Dapat Dipilih', 'Kavling tersebut sudah terisi oleh konsumen aktif lain.', 'error');
      }
      if (!selected.has_hargajual || selected.harga_akhir === '-' || !selected.id_hargajual || selected.id_hargajual === '-') {
        return Swal.fire('Belum Ada Harga Jual', 'Kavling ini belum dipasarkan / belum memiliki harga jual. Silakan set harga jual kavling terlebih dahulu atau pilih kavling lain yang sudah memiliki harga jual.', 'warning');
      }

      const mode = $('#pilih-kavling-mode').val() || 'AKTIFKAN_NUP';
      let idMkdt = Number($('#pilih-kavling-id_mkdt').val() || 0);
      if (!idMkdt) {
        const sel = resolveActiveSelection();
        idMkdt = sel.idMkdt;
      }
      const fromIdKavling = Number($('#pilih-kavling-from_id_kavling').val() || 0);
      const catatan = $.trim($('#pindah-kavling-catatan').val());
      const fileInput = document.getElementById('pindah-kavling-file');
      const suratFile = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

      const toLabel = $.trim((selected.nama_jalan || '') + ' No. ' + (selected.no_kavling || '-'));
      const fromLabel = $('#pilih-kavling-info-asal').text() || '-';

      self.pendingTransferContext = {
        mode: mode,
        id_mkdt: idMkdt,
        from_id_kavling: fromIdKavling,
        to_id_kavling: Number(selected.id_kavling),
        from_kavling_label: fromLabel,
        to_kavling_label: toLabel,
        catatan_pindah_kavling: catatan,
        file_surat_pindah: suratFile
      };

      const targetShape = {
        id: 'kav' + selected.id_kavling,
        data: {
          tipe: 'kavling',
          id_kavling: String(selected.id_kavling),
          id_mkdt: String(idMkdt),
          id_keuangan: selected.id_keuangan || null,
          id_legal: selected.id_legal || null,
          id_produksi: selected.id_produksi || null,
          nama_jalan: selected.nama_jalan || '',
          no_kavling: selected.no_kavling || ''
        },
        data2: {
          harga_akhir: selected.harga_akhir || selected.id_hargajual || '-',
          id_hargajual: selected.id_hargajual || selected.harga_akhir || '-',
          id_komplain: selected.id_komplain || null,
          no_tipe_rumah: selected.no_tipe_rumah || '',
          tipe_rumah: selected.tipe_rumah || '',
          harga_akhir_tgl: selected.harga_akhir_tgl || '',
          harga_akhir_oleh: selected.harga_akhir_oleh || ''
        }
      };

      forceCloseModal('#modal-pilih-kavling-nup');
      forceCloseModal('#modal-daftar-nup');
      forceCloseModal('#modal_divisi4');

      window.editdtt = [targetShape];
      if (typeof editdtt !== 'undefined') {
        editdtt = [targetShape];
      }
      $('.id_kavling').val(selected.id_kavling);
      $('.id_mkdt').val(idMkdt);
      $('#id_mkdt').val(idMkdt);
      $('#idk-id_mkdt').val(idMkdt);

      if (typeof window.isi_data_konsumen === 'function') {
        window.isi_data_konsumen();
        setTimeout(function () {
          self.renderPendingTransferBanner();
        }, 350);
      } else {
        Swal.fire('Error', 'Fungsi form Isi Data Konsumen tidak ditemukan pada halaman ini.', 'error');
      }
    },

    renderPendingTransferBanner: function () {
      const ctx = this.pendingTransferContext;
      this.clearPendingTransferBanner();
      if (!ctx) return;

      const isNup = ctx.mode === 'AKTIFKAN_NUP';
      const badgeClass = isNup ? 'alert-success' : 'alert-info';
      const title = isNup
        ? '<i class="fas fa-user-check mr-50"></i> Mode Aktivasi NUP ke Kavling: <strong>' + escHtml(ctx.to_kavling_label) + '</strong>'
        : '<i class="fas fa-exchange-alt mr-50"></i> Mode Pindah Kavling: <strong>' + escHtml(ctx.from_kavling_label) + ' &rarr; ' + escHtml(ctx.to_kavling_label) + '</strong>';

      const fileInfo = ctx.file_surat_pindah
        ? '<div class="small mt-25"><i class="fa fa-paperclip"></i> Lampiran Surat Pindah: <strong>' + escHtml(ctx.file_surat_pindah.name) + '</strong></div>'
        : '';

      const bannerHtml =
        '<div id="idk-transfer-banner" class="alert ' + badgeClass + ' p-1 mb-1">' +
          '<div>' + title + '</div>' +
          '<div class="small mt-25">Seluruh data konsumen, riwayat pembayaran, dan rincian sebelumnya telah terisi otomatis. Silakan periksa kembali harga & jadwal tagihan pada kavling tujuan lalu klik <strong>Simpan</strong> di langkah terakhir.</div>' +
          (ctx.catatan_pindah_kavling ? '<div class="small mt-25">Catatan: <em>' + escHtml(ctx.catatan_pindah_kavling) + '</em></div>' : '') +
          fileInfo +
          '<input type="hidden" name="from_id_kavling" value="' + Number(ctx.from_id_kavling || 0) + '">' +
          '<input type="hidden" name="catatan_pindah_kavling" value="' + escHtml(ctx.catatan_pindah_kavling || '') + '">' +
        '</div>';

      $('#modal-isi_data_konsumen .modal-body').first().prepend(bannerHtml);
      $('#modal-isi_data_konsumen .label_alamat').text(ctx.to_kavling_label);
    },

    clearPendingTransferBanner: function () {
      $('#idk-transfer-banner').remove();
    },

    appendPendingTransferToFormData: function (formData) {
      const ctx = this.pendingTransferContext;
      if (!ctx) return;
      if (ctx.from_id_kavling) {
        formData.set('from_id_kavling', String(ctx.from_id_kavling));
      }
      if (ctx.catatan_pindah_kavling) {
        formData.set('catatan_pindah_kavling', String(ctx.catatan_pindah_kavling));
      }
      if (ctx.file_surat_pindah) {
        formData.set('file_surat_pindah', ctx.file_surat_pindah);
      }
    },

    renderKavlingHistoryTable: function (historyRows) {
      const $tbody = $('#ganti_kavling-here');
      if (!$tbody.length) return;

      if (!Array.isArray(historyRows) || historyRows.length === 0) {
        $tbody.html('<tr><td colspan="3" class="text-center text-muted">Belum ada riwayat perpindahan kavling / NUP</td></tr>');
        return;
      }

      const html = historyRows.map(function (h, idx) {
        const fileBtn = h.file_lampiran_url
          ? '<div class="mt-25"><a href="' + escHtml(h.file_lampiran_url) + '" target="_blank" class="btn btn-outline-primary btn-sm py-25 px-50"><i class="fa fa-file-alt mr-25"></i> Lihat Surat / Lampiran</a></div>'
          : '';

        return (
          '<tr>' +
            '<td>' + (idx + 1) + '</td>' +
            '<td>' +
              '<div class="font-weight-bold text-primary">' + escHtml(h.action_label || h.action_type) + '</div>' +
              '<div class="small">Dari: <strong>' + escHtml(h.from_kavling_label || '-') + '</strong> &rarr; Ke: <strong>' + escHtml(h.to_kavling_label || '-') + '</strong></div>' +
              '<div class="small text-success">Sudah Bayar Tersimpan: ' + formatRupiah(h.nominal_sudah_bayar_snapshot || 0) + '</div>' +
              (h.catatan ? '<div class="small text-muted">Catatan: ' + escHtml(h.catatan) + '</div>' : '') +
              fileBtn +
            '</td>' +
            '<td>' +
              '<div class="font-weight-bold">' + escHtml(h.actor_name || '-') + '</div>' +
              '<div class="small text-muted">' + formatDateTimeDisplay(h.created_at) + '</div>' +
            '</td>' +
          '</tr>'
        );
      }).join('');

      $tbody.html(html);
    },

    loadKavlingHistoryForCurrentSelection: function () {
      const self = this;
      const sel = resolveActiveSelection();
      const idKavling = sel.idKavling;
      const idMkdt = sel.idMkdt;

      if (!idKavling && !idMkdt) {
        self.renderKavlingHistoryTable([]);
        return;
      }

      $.ajax({
        url: base_url + 'api/nup/kavling-history',
        type: 'POST',
        dataType: 'json',
        data: {
          [csrfName]: csrfHash,
          id_mkdt: idMkdt,
          id_kavling: idKavling
        },
        success: function (res) {
          if (res && res.token) csrfHash = res.token;
          self.renderKavlingHistoryTable((res && res.history) ? res.history : []);
        }
      });
    }
  };

  window.SIGAPP_NUP = NUP;

  window.openPindahKavlingModal = function () {
    NUP.openPindahKavlingModal();
  };

  window.openTarikKeNupModal = function () {
    NUP.openPullToNupModal();
  };

  window.ganti_kavling = function () {
    NUP.openPindahKavlingModal();
  };

  window.getRiwayatGantiKavling = function () {
    NUP.loadKavlingHistoryForCurrentSelection();
  };

  $(function () {
    NUP.init();
  });
})(window, window.jQuery);
