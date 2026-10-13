<style>
  .swal2-container {
    z-index: 20050 !important;
  }
  .select2-results__option[aria-disabled="true"] .nup-kavling-opt-occupied,
  .nup-kavling-opt-occupied {
    color: #ea5455 !important;
    font-weight: 600;
  }
  .select2-results__option[aria-disabled="true"] {
    background-color: rgba(234, 84, 85, 0.06) !important;
    cursor: not-allowed !important;
  }
  .nup-kavling-opt-available {
    color: #2c3e50;
    font-weight: 600;
  }
  .nup-badge-source {
    font-size: 0.72rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
  }
  #modal-daftar-nup .table th,
  #modal-daftar-nup .table td,
  #nup_tables th,
  #nup_tables td {
    vertical-align: middle;
  }
</style>

<!-- Modal Daftar NUP (Quick Access dari Siteplan & Posisi Konsumen) -->
<div class="modal fade text-left" id="modal-daftar-nup" tabindex="-1" role="dialog" aria-labelledby="modalDaftarNupLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary white">
        <h4 class="modal-title text-white" id="modalDaftarNupLabel">
          <i class="fas fa-list-ol mr-50"></i> Daftar Konsumen NUP (Nomor Urut Pemesanan)
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-1" style="gap: .75rem;">
          <div class="input-group" style="max-width: 380px;">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
            </div>
            <input type="text" id="modal-nup-search" class="form-control" placeholder="Cari nama konsumen, NIK, sales, catatan...">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-primary" id="btn-refresh-modal-nup" onclick="window.SIGAPP_NUP && window.SIGAPP_NUP.reloadAll()" title="Refresh Data NUP">
                <i class="fa fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <div class="d-flex align-items-center" style="gap: .5rem;">
            <?php if (in_groups(['1', '3', '4', '8'])): ?>
              <button type="button" class="btn btn-primary btn-sm" onclick="window.SIGAPP_NUP && window.SIGAPP_NUP.openFormModal()">
                <i class="fa fa-plus mr-25"></i> Input Konsumen NUP
              </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-bordered compact mb-0" id="table-modal-daftar-nup">
            <thead class="thead-light">
              <tr>
                <th style="width: 45px;">No</th>
                <th style="width: 110px;">No NUP</th>
                <th style="width: 170px;">Aksi</th>
                <th>Tgl Booking</th>
                <th>Nama Konsumen & NIK/NPWP</th>
                <th>Kontak & Sales</th>
                <th>Sumber / Eks Kavling</th>
                <th>Booking Fee</th>
                <th>Total Sudah Bayar</th>
                <th>Catatan NUP</th>
                <th>Berkas</th>
              </tr>
            </thead>
            <tbody id="tbody-modal-daftar-nup">
              <tr>
                <td colspan="11" class="text-center text-muted py-2">Memuat data NUP...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Form Input / Edit NUP -->
<div class="modal fade text-left" id="modal-form-nup" tabindex="-1" role="dialog" aria-labelledby="modalFormNupLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary white">
        <h4 class="modal-title text-white" id="modalFormNupLabel">
          <i class="fas fa-user-plus mr-50"></i> <span id="modal-form-nup-title">Input Konsumen NUP Baru</span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-nup" enctype="multipart/form-data" onsubmit="return false;">
          <input type="hidden" name="id_mkdt" id="nup-id_mkdt" value="">
          <input type="hidden" name="id_konsumen" id="nup-id_konsumen" value="">
          <input type="hidden" name="id_proyek" id="nup-id_proyek" value="">

          <div class="alert alert-primary p-1 mb-1">
            <div class="d-flex align-items-center">
              <i class="fas fa-info-circle font-medium-3 mr-75"></i>
              <div>
                <strong>Status NUP (Nomor Urut Pemesanan):</strong> Konsumen sudah membayar <em>Booking Fee</em> pada proyek aktif namun belum memilih kavling spesifik. Pembayaran Booking Fee akan langsung tercatat di keuangan proyek.
              </div>
            </div>
          </div>

          <!-- Bagian 1: Data Booking NUP & Catatan -->
          <div class="card border mb-1">
            <div class="card-header bg-light py-75">
              <h5 class="card-title mb-0"><i class="fas fa-receipt text-primary mr-50"></i> 1. Data Booking Fee & Catatan NUP (Wajib)</h5>
            </div>
            <div class="card-body pt-1 pb-50">
              <div class="row">
                <div class="col-md-3 mb-1">
                  <label class="font-weight-bold" for="nup-no_nup">No NUP <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nup-no_nup" name="no_nup" placeholder="Contoh: NUP-001, NUP-01" required>
                  <small class="text-muted">Diisi manual</small>
                </div>
                <div class="col-md-3 mb-1">
                  <label class="font-weight-bold" for="nup-booking_tgl">Tanggal Booking <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="nup-booking_tgl" name="booking_tgl" required value="<?= date('Y-m-d') ?>">
                  <small class="text-muted" id="nup-booking-lock-hint" style="display:none;"></small>
                </div>
                <div class="col-md-3 mb-1">
                  <label class="font-weight-bold" for="nup-booking_fee">Nominal Booking Fee (Rp) <span class="text-danger">*</span></label>
                  <input type="text" class="form-control numeral-mask" id="nup-booking_fee" name="booking_fee" placeholder="Contoh: 1,000,000" required>
                </div>
                <div class="col-md-3 mb-1">
                  <label class="font-weight-bold" for="nup-file_nup">Lampiran Berkas NUP</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="nup-file_nup" name="file_nup" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="custom-file-label" for="nup-file_nup">Pilih file...</label>
                  </div>
                  <div id="nup-preview-file_nup" class="mt-25"></div>
                </div>
                <div class="col-12 mb-1">
                  <label class="font-weight-bold" for="nup-catatan_nup">Catatan NUP <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="nup-catatan_nup" name="catatan_nup" rows="2" placeholder="Masukkan catatan pemesanan NUP, preferensi blok/tipe rumah, atau keterangan booking..." required></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Bagian 2: Data Identitas Konsumen & Lampiran KTP/NPWP -->
          <div class="card border mb-1">
            <div class="card-header bg-light py-75">
              <h5 class="card-title mb-0"><i class="fas fa-id-card text-primary mr-50"></i> 2. Identitas Konsumen & Dokumen (KTP / NPWP)</h5>
            </div>
            <div class="card-body pt-1 pb-50">
              <div class="row">
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-nama_konsumen">Nama Lengkap Konsumen <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="nup-nama_konsumen" name="nama_konsumen" placeholder="Nama sesuai KTP" required>
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-nik_konsumen">NIK KTP</label>
                  <input type="text" class="form-control" id="nup-nik_konsumen" name="nik_konsumen" placeholder="16 digit NIK" maxlength="20">
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-npwp_konsumen">NPWP</label>
                  <input type="text" class="form-control" id="nup-npwp_konsumen" name="npwp_konsumen" placeholder="Nomor NPWP" maxlength="25">
                </div>

                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-hp_konsumen">No. HP / WhatsApp</label>
                  <input type="text" class="form-control" id="nup-hp_konsumen" name="hp_konsumen" placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-email_konsumen">Email Konsumen</label>
                  <input type="email" class="form-control" id="nup-email_konsumen" name="email_konsumen" placeholder="nama@email.com">
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-sales">Nama Sales / Marketing</label>
                  <input type="text" class="form-control" id="nup-sales" name="sales" placeholder="Nama Sales">
                </div>

                <div class="col-md-6 mb-1">
                  <label class="font-weight-bold" for="nup-alamat_konsumen">Alamat Sesuai KTP</label>
                  <textarea class="form-control" id="nup-alamat_konsumen" name="alamat_konsumen" rows="2" placeholder="Alamat lengkap sesuai KTP"></textarea>
                </div>
                <div class="col-md-6 mb-1">
                  <label class="font-weight-bold" for="nup-alamat_surat">Alamat Surat Menyurat / Domisili</label>
                  <textarea class="form-control" id="nup-alamat_surat" name="alamat_surat" rows="2" placeholder="Alamat domisili saat ini"></textarea>
                </div>

                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-file_ktp">Lampiran File KTP</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="nup-file_ktp" name="file_ktp" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="custom-file-label" for="nup-file_ktp">Pilih file KTP...</label>
                  </div>
                  <div id="nup-preview-file_ktp" class="mt-25"></div>
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-file_npwp">Lampiran File NPWP</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="nup-file_npwp" name="file_npwp" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="custom-file-label" for="nup-file_npwp">Pilih file NPWP...</label>
                  </div>
                  <div id="nup-preview-file_npwp" class="mt-25"></div>
                </div>
                <div class="col-md-4 mb-1">
                  <label class="font-weight-bold" for="nup-file_data_diri">Lampiran Data Diri Lainnya</label>
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="nup-file_data_diri" name="file_data_diri" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="custom-file-label" for="nup-file_data_diri">Pilih file...</label>
                  </div>
                  <div id="nup-preview-file_data_diri" class="mt-25"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bagian 3: Pekerjaan & Data Pasangan -->
          <div class="card border mb-1">
            <div class="card-header bg-light py-75 d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#nup-collapse-extra" style="cursor:pointer;">
              <h5 class="card-title mb-0"><i class="fas fa-briefcase text-primary mr-50"></i> 3. Data Pekerjaan, Instansi & Pasangan (Klik untuk buka/tutup)</h5>
              <i class="fas fa-chevron-down"></i>
            </div>
            <div id="nup-collapse-extra" class="collapse show">
              <div class="card-body pt-1 pb-50">
                <div class="row">
                  <div class="col-md-3 mb-1">
                    <label for="nup-pekerjaan">Pekerjaan</label>
                    <select class="form-control" id="nup-pekerjaan" name="pekerjaan">
                      <option value="Pegawai">Pegawai</option>
                      <option value="Wiraswasta">Wiraswasta</option>
                      <option value="Profesional">Profesional</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-nama_instansi">Nama Instansi / Perusahaan</label>
                    <input type="text" class="form-control" id="nup-nama_instansi" name="nama_instansi">
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-bidang_pekerjaan">Bidang Pekerjaan</label>
                    <input type="text" class="form-control" id="nup-bidang_pekerjaan" name="bidang_pekerjaan">
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-lama_bekerja">Lama Bekerja</label>
                    <input type="text" class="form-control" id="nup-lama_bekerja" name="lama_bekerja">
                  </div>

                  <div class="col-md-4 mb-1">
                    <label for="nup-tel_instansi">No. Telp Instansi</label>
                    <input type="text" class="form-control" id="nup-tel_instansi" name="tel_instansi">
                  </div>
                  <div class="col-md-4 mb-1">
                    <label for="nup-email_instansi">Email Instansi</label>
                    <input type="email" class="form-control" id="nup-email_instansi" name="email_instansi">
                  </div>
                  <div class="col-md-4 mb-1">
                    <label for="nup-alamat_instansi">Alamat Instansi</label>
                    <input type="text" class="form-control" id="nup-alamat_instansi" name="alamat_instansi">
                  </div>

                  <div class="col-md-3 mb-1">
                    <label for="nup-status_pernikahan">Status Pernikahan</label>
                    <select class="form-control" id="nup-status_pernikahan" name="status_pernikahan">
                      <option value="Belum Menikah">Belum Menikah</option>
                      <option value="Menikah">Menikah</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-nama_pasangan">Nama Pasangan</label>
                    <input type="text" class="form-control" id="nup-nama_pasangan" name="nama_pasangan">
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-nik_pasangan">NIK Pasangan</label>
                    <input type="text" class="form-control" id="nup-nik_pasangan" name="nik_pasangan">
                  </div>
                  <div class="col-md-3 mb-1">
                    <label for="nup-hp_pasangan">No. HP Pasangan</label>
                    <input type="text" class="form-control" id="nup-hp_pasangan" name="hp_pasangan">
                  </div>

                  <div class="col-md-4 mb-1">
                    <label for="nup-status_pekerjaan_pasangan">Pekerjaan Pasangan</label>
                    <select class="form-control" id="nup-status_pekerjaan_pasangan" name="status_pekerjaan_pasangan">
                      <option value="Tidak Bekerja">Tidak Bekerja</option>
                      <option value="Pegawai">Pegawai</option>
                      <option value="Wiraswasta">Wiraswasta</option>
                      <option value="Profesional">Profesional</option>
                    </select>
                  </div>
                  <div class="col-md-4 mb-1">
                    <label for="nup-instansi_pasangan">Instansi Pasangan</label>
                    <input type="text" class="form-control" id="nup-instansi_pasangan" name="instansi_pasangan">
                  </div>
                  <div class="col-md-4 mb-1">
                    <label for="nup-no_spptb">No. SPPTB (Jika sudah ada)</label>
                    <input type="text" class="form-control" id="nup-no_spptb" name="no_spptb">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bagian 4: Riwayat Kavling pada NUP ini -->
          <div class="card border mb-0" id="nup-form-history-card" style="display:none;">
            <div class="card-header bg-light py-75">
              <h5 class="card-title mb-0"><i class="fas fa-history text-primary mr-50"></i> Riwayat Kavling & Perpindahan</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>No</th>
                      <th>Aksi</th>
                      <th>Dari Kavling</th>
                      <th>Ke Kavling</th>
                      <th>Sudah Bayar</th>
                      <th>Catatan & Berkas</th>
                      <th>Waktu / Oleh</th>
                    </tr>
                  </thead>
                  <tbody id="nup-form-history-tbody"></tbody>
                </table>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form-nup" onclick="window.SIGAPP_NUP && window.SIGAPP_NUP.submitForm()">
          <i class="fas fa-save mr-50"></i> Simpan Data NUP
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tarik Konsumen Aktif ke NUP -->
<div class="modal fade text-left" id="modal-tarik-nup" tabindex="-1" role="dialog" aria-labelledby="modalTarikNupLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning white">
        <h4 class="modal-title text-white" id="modalTarikNupLabel">
          <i class="fas fa-undo-alt mr-50"></i> Penarikan Konsumen Aktif ke Daftar NUP
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-tarik-nup" enctype="multipart/form-data" onsubmit="return false;">
          <input type="hidden" name="id_mkdt" id="tarik-nup-id_mkdt" value="">
          <input type="hidden" name="id_kavling" id="tarik-nup-id_kavling" value="">

          <div class="alert alert-warning p-1 mb-1">
            <div class="font-weight-bold mb-25"><i class="fas fa-exclamation-triangle mr-50"></i> Konsekuensi Penarikan ke NUP:</div>
            <ul class="mb-0 pl-1">
              <li>Status konsumen dipindahkan ke <strong>Daftar NUP</strong> pada proyek ini.</li>
              <li>Seluruh <strong>data konsumen, rincian harga, dan nominal yang sudah dibayar tetap tersimpan utuh</strong>.</li>
              <li>Status kavling saat ini di Siteplan akan menjadi <strong>Kosong (Tanpa Konsumen)</strong> dan siap dipilih konsumen lain.</li>
              <li>Riwayat kavling yang pernah dipilih konsumen ini akan tercatat otomatis.</li>
            </ul>
          </div>

          <div class="card bg-light border mb-1">
            <div class="card-body py-75 px-1">
              <div class="row">
                <div class="col-md-4">
                  <small class="text-muted d-block">Nama Konsumen</small>
                  <strong id="tarik-nup-info-konsumen">-</strong>
                </div>
                <div class="col-md-4">
                  <small class="text-muted d-block">Kavling Saat Ini</small>
                  <strong id="tarik-nup-info-kavling" class="text-primary">-</strong>
                </div>
                <div class="col-md-4">
                  <small class="text-muted d-block">Total Nominal Sudah Masuk</small>
                  <strong id="tarik-nup-info-bayar" class="text-success">Rp 0</strong>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="font-weight-bold" for="tarik-nup-no_nup">No NUP <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="tarik-nup-no_nup" name="no_nup" placeholder="Contoh: NUP-001, NUP-01" required>
            <small class="text-muted">Diisi manual</small>
          </div>

          <div class="form-group">
            <label class="font-weight-bold" for="tarik-nup-catatan">Catatan / Alasan Penarikan ke NUP <span class="text-danger">*</span></label>
            <textarea class="form-control" id="tarik-nup-catatan" name="catatan_nup" rows="3" placeholder="Jelaskan kendala teknis / alasan konsumen ditarik kembali ke NUP..." required></textarea>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold" for="tarik-nup-file">Lampiran Dokumen / Bukti Pendukung (Opsional)</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="tarik-nup-file" name="file_nup" accept=".pdf,.jpg,.jpeg,.png">
              <label class="custom-file-label" for="tarik-nup-file">Pilih file pendukung...</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-warning" id="btn-submit-tarik-nup" onclick="window.SIGAPP_NUP && window.SIGAPP_NUP.submitPullToNup()">
          <i class="fas fa-undo-alt mr-50"></i> Tarik ke NUP & Kosongkan Kavling
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Pilih Kavling Tujuan (Digunakan untuk Aktifkan NUP & Pindah Kavling) -->
<div class="modal fade text-left" id="modal-pilih-kavling-nup" tabindex="-1" role="dialog" aria-labelledby="modalPilihKavlingNupLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info white">
        <h4 class="modal-title text-white" id="modalPilihKavlingNupLabel">
          <i class="fas fa-exchange-alt mr-50"></i> <span id="pilih-kavling-modal-title">Pilih Kavling Tujuan</span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pilih-kavling-mode" value="AKTIFKAN_NUP">
        <input type="hidden" id="pilih-kavling-id_mkdt" value="">
        <input type="hidden" id="pilih-kavling-from_id_kavling" value="">
        <input type="hidden" id="pilih-kavling-last_id_kavling" value="">

        <div class="alert alert-info p-1 mb-1" id="pilih-kavling-alert-desc">
          Pilih kavling tujuan yang masih kosong. Kavling yang sudah memiliki konsumen aktif ditampilkan <strong>berwarna merah dan tidak dapat dipilih</strong>. Setelah memilih kavling, form <strong>Isi Data Konsumen</strong> akan terbuka otomatis dengan seluruh data sebelumnya sudah terisi.
        </div>

        <div class="card bg-light border mb-1">
          <div class="card-body py-75 px-1">
            <div class="row">
              <div class="col-md-4">
                <small class="text-muted d-block">Nama Konsumen</small>
                <strong id="pilih-kavling-info-konsumen">-</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Asal / Kavling Sebelumnya</small>
                <strong id="pilih-kavling-info-asal">-</strong>
              </div>
              <div class="col-md-4">
                <small class="text-muted d-block">Nominal Pembayaran Tersimpan</small>
                <strong id="pilih-kavling-info-bayar" class="text-success">Rp 0</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-5">
            <label class="font-weight-bold" for="filter-kavling-hargajual-nup">Filter Status Harga Jual</label>
            <select id="filter-kavling-hargajual-nup" class="form-control">
              <option value="sudah" selected>Sudah Diset Harga Jual (Siap Dipasarkan)</option>
              <option value="semua">Semua Kavling (Sudah &amp; Belum Ada Harga)</option>
              <option value="belum">Belum Diset Harga Jual</option>
            </select>
          </div>
          <div class="form-group col-md-7">
            <label class="font-weight-bold" for="select-target-kavling-nup">Pilih Kavling Tujuan (Nama Jalan &amp; No. Kavling) <span class="text-danger">*</span></label>
            <select id="select-target-kavling-nup" class="form-control" style="width: 100%;"></select>
          </div>
        </div>
        <div class="mb-1" style="margin-top: -0.5rem;">
          <small class="text-muted d-block">
            <span class="text-danger font-weight-bold"><i class="fa fa-circle"></i> Merah (Disabled):</span> Sudah ada konsumen aktif &nbsp;|&nbsp;
            <span class="text-success font-weight-bold"><i class="fa fa-circle"></i> Tersedia:</span> Kosong &amp; dapat dipilih &nbsp;|&nbsp;
            <span class="text-primary font-weight-bold"><i class="fa fa-tag"></i> Harga Jual:</span> Wajib diset untuk membuka Form Isi Data Konsumen
          </small>
        </div>

        <div class="form-group" id="group-pindah-kavling-surat">
          <label class="font-weight-bold" for="pindah-kavling-file">Lampiran Surat Pindah Kavling / Dokumen Pendukung</label>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="pindah-kavling-file" accept=".pdf,.jpg,.jpeg,.png">
            <label class="custom-file-label" for="pindah-kavling-file">Pilih file surat pindah...</label>
          </div>
          <small class="text-muted">File surat pindah akan otomatis disertakan saat Anda menyimpan Form Isi Data Konsumen.</small>
        </div>

        <div class="form-group mb-0">
          <label class="font-weight-bold" for="pindah-kavling-catatan">Catatan Pindah Kavling / Aktivasi NUP</label>
          <textarea class="form-control" id="pindah-kavling-catatan" rows="2" placeholder="Masukkan catatan perpindahan atau aktivasi kavling..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-proceed-pilih-kavling" onclick="window.SIGAPP_NUP && window.SIGAPP_NUP.proceedToIsiDataKonsumen()">
          <i class="fas fa-arrow-right mr-50"></i> Lanjutkan ke Form Isi Data Konsumen
        </button>
      </div>
    </div>
  </div>
</div>
