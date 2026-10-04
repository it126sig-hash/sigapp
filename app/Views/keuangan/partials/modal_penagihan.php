<!-- ################################## Modal Penagihan ##########################################-->
<style>
  .signature-canvas-wrap {
      border: 1px solid #ced4da;
      border-radius: .25rem;
      background-color: #f8f9fa;
      position: relative;
      width: 100%;
  }
  .signature-canvas-wrap canvas {
      width: 100%;
      height: 200px;
      cursor: crosshair;
  }
  .signature-clear-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      z-index: 10;
  }
</style>
<div class="modal fade text-left" id="modal_penagihan" tabindex="-1" role="dialog" aria-labelledby="modalPenagihanLabel"
    aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document" style="max-height: 100vh;">
        <div class="modal-content" style="max-height: 95vh;">
            <div class="modal-header d-flex flex-column align-items-start pb-0">
                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                    <h5 class="modal-title" id="modalPenagihanLabel">Penagihan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- Tabs as part of fixed header -->
                <ul class="nav nav-tabs mb-0 w-100 border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab_riwayat_tagihan-tab" data-toggle="tab" href="#tab_riwayat_tagihan"
                            aria-controls="tab_riwayat_tagihan" role="tab" aria-selected="true">Riwayat Tagihan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab_buat_tagihan-tab" data-toggle="tab" href="#tab_buat_tagihan"
                            aria-controls="tab_buat_tagihan" role="tab" aria-selected="false">Buat Tagihan</a>
                    </li>
                </ul>
            </div>

            <!-- Scrollable Body -->
            <div class="modal-body p-0" style="max-height: calc(100vh - 160px); overflow-y: auto;">
                <div class="tab-content">
                    <!-- Tab Riwayat Tagihan -->
                    <div class="tab-pane active px-1 py-1" id="tab_riwayat_tagihan" aria-labelledby="tab_riwayat_tagihan-tab" role="tabpanel">
                        <div class="card invoice-preview-card mb-0 shadow-none border-0">
                            <div class="card-body p-1">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="tbl-riwayat-tagihan">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No Invoice</th>
                                                <th>Tanggal Terbit</th>
                                                <th>Jatuh Tempo</th>
                                                <th>Status</th>
                                                <th>Tgl Ubah Status</th>
                                                <th>Keterangan</th>
                                                <th>Dibuat Oleh</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="list_riwayat_tagihan-here">
                                            <tr><td colspan="8" class="text-center">Memuat riwayat...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Buat Tagihan -->
                    <div class="tab-pane px-1 py-1" id="tab_buat_tagihan" aria-labelledby="tab_buat_tagihan-tab" role="tabpanel">
                        <form id="form-buat-tagihan">
                            <input type="hidden" id="tagihan_id_mkdt" name="id_mkdt">
                            <input type="hidden" id="tagihan_id_kavling" name="id_kavling">
                            <input type="hidden" id="tagihan_id_konsumen" name="id_konsumen">

                            <div class="card invoice-preview-card mb-0 shadow-none border-0">
                                <div class="card-body p-1">
                                    <div class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">
                                        <div class="col-md-5 pl-0">
                                            <div class="form-group">
                                                <div class="d-flex justify-content-between align-items-center mb-50">
                                                    <label for="tagihan_kopsurat" class="mb-0 font-weight-bold">Kop Surat <span class="text-danger">*</span></label>
                                                    <button type="button" class="btn btn-outline-primary btn-sm py-25 px-50" id="btn-modal-kelola-kopsurat" title="Kelola Kop Surat">
                                                        <i class="fas fa-cog"></i> Kelola Kop
                                                    </button>
                                                </div>
                                                <select class="custom-select w-100" id="tagihan_kopsurat" name="id_kopsurat" required>
                                                    <option value="">-- Pilih Kop Surat --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="invoice-number-date mt-md-0 mt-4 col-md-7 pr-0 text-right">
                                            <div class="d-flex align-items-center justify-content-end mb-1">
                                                <span class="title mr-1">No Invoice:</span>
                                                <input type="text" id="tagihan_no_inv" name="no_inv" class="form-control invoice-edit-input" placeholder="Auto Generate" disabled style="width: 200px;">
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end mb-1">
                                                <span class="title mr-1">Tanggal:</span>
                                                <input type="date" id="tagihan_tanggal" name="tanggal_invoice" class="form-control" required style="width: 200px;">
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="title mr-1">Jatuh Tempo:</span>
                                                <input type="date" id="tagihan_jatuh_tempo" name="tanggal_jatuh_tempo" class="form-control" required style="width: 200px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="invoice-spacing m-0" />

                                <div class="card-body p-1 pt-2">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <h6 class="mb-1 text-muted text-uppercase">Ditagihkan Ke:</h6>
                                            <h6 class="mb-25 font-weight-bold text-dark" id="tagihan_detail_konsumen"></h6>
                                        </div>
                                        <div class="col-xl-6 text-right">
                                            <h6 class="mb-1 text-muted text-uppercase">Perumahan:</h6>
                                            <h6 class="mb-25 font-weight-bold text-dark" id="tagihan_detail_kavling"></h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body p-1 invoice-product-details">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" id="tbl-tagihan-items">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th scope="col" width="5%" class="text-center">No</th>
                                                    <th scope="col">Berita Acara</th>
                                                    <th scope="col" width="20%">Jatuh Tempo</th>
                                                    <th scope="col" width="20%" class="text-right">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tb-tagihan-items-here">
                                                <!-- Populated by JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <hr class="invoice-spacing m-0" />

                                <div class="card-body p-1">
                                    <div class="row">
                                        <!-- Syarat Ketentuan -->
                                        <div class="col-lg-6 mb-2">
                                            <div class="form-group mb-0">
                                                <label for="tagihan_snk" class="form-label font-weight-bold">Syarat & Ketentuan:</label>
                                                <!-- We'll attach rich text editor to this textarea -->
                                                <textarea class="form-control" id="tagihan_snk" name="terms" required><ol><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari denda&nbsp;</span></li><li><span style="font-size: 1rem; letter-spacing: 0.01rem;">Pembayaran yang sah hanya melalui transfer ke rekening atas nama <br><b>PT. Sanggarindah Karya Sentosa</b> <b>Raya</b> BCA KC Setiabudi - Bandung, Nomor Rekening :<b>2337 887 887</b>&nbsp;</span></li><li>Konfirmasi pembayaran ke bagian keuangan kami dan lampirkan bukti transfer.</li></ol></textarea>
                                            </div>
                                        </div>
                                        
                                        <!-- Tanda Tangan -->
                                        <div class="col-lg-6 mb-2">
                                            <h6 class="mb-1 font-weight-bold text-uppercase">Tanda Tangan Pembuat Tagihan</h6>
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label>Metode Tanda Tangan</label>
                                                    <select class="form-control" id="tagihan-sign-method" name="sign_method">
                                                        <option value="canvas">Gambar Sekarang</option>
                                                        <option value="profile">TTD Profil Saya</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>Password Akun <span class="text-danger">*</span></label>
                                                    <input type="password" autocomplete="current-password" class="form-control" id="tagihan-password" name="password" required placeholder="Masukkan password">
                                                </div>
                                            </div>
                                            <div class="signature-canvas-wrap" id="tagihan-canvas-wrap">
                                                <canvas id="tagihan-canvas"></canvas>
                                                <button type="button" class="btn btn-sm btn-outline-secondary signature-clear-btn" id="btn-clear-tagihan-canvas">Hapus</button>
                                                <input type="hidden" name="ttd_img" id="tagihan_ttd_img">
                                            </div>
                                            <div id="tagihan-profile-preview-wrap" class="d-none mt-50">
                                                <div class="border rounded p-1 text-center bg-light" style="min-height: 120px;">
                                                    <div id="tagihan-profile-preview-content">
                                                        <i class="fas fa-spinner fa-spin mr-50"></i> Memeriksa tanda tangan profil...
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
            </div>
            
            <!-- Sticky Footer -->
            <div class="modal-footer p-1" id="footer-penagihan">
                <!-- Only visible when 'Buat Tagihan' tab is active -->
                <div id="footer-action-buat-tagihan" class="d-none w-100 text-right">
                    <button type="submit" form="form-buat-tagihan" class="btn btn-primary" id="btn-simpan-tagihan">Buat Invoice Tagihan</button>
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                </div>
                <!-- Only visible when 'Riwayat Tagihan' tab is active -->
                <div id="footer-action-riwayat-tagihan" class="w-100 text-right">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Ubah Status Tagihan -->
<div class="modal fade" id="modal_ubah_status_tagihan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <form class="modal-content" id="form-ubah-status-tagihan">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Status Tagihan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="no_inv" id="us_no_inv">
                <div class="form-group">
                    <label>Status Tagihan</label>
                    <select class="form-control" name="status_tagihan" id="us_status_tagihan" required>
                        <option value="dibuat">Dibuat</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="respon">Respon</option>
                        <option value="tidak respon">Tidak Respon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Ubah Status</label>
                    <input type="date" class="form-control" name="tanggal_ubah_status" id="us_tanggal_ubah_status" required>
                </div>
                <div class="form-group mb-0">
                    <label>Keterangan</label>
                    <textarea class="form-control" name="keterangan_status" id="us_keterangan_status" rows="3" placeholder="Keterangan tambahan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan Status</button>
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kelola Kop Surat -->
<div class="modal fade" id="modal_kelola_kopsurat" tabindex="-1" role="dialog" aria-labelledby="modalKelolaKopLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalKelolaKopLabel"><i class="fas fa-file-invoice text-primary mr-50"></i> Kelola Kop Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-2" style="max-height: calc(100vh - 180px); overflow-y: auto;">
                <!-- Action Bar -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted small">Kelola latar belakang dan format ukuran kop surat</span>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-kopsurat">
                        <i class="fas fa-plus mr-25"></i> Tambah Kop Surat
                    </button>
                </div>

                <!-- Form Card (Hidden by default, shown when Tambah / Edit clicked) -->
                <div class="card border shadow-none mb-2 d-none" id="card-form-kopsurat">
                    <div class="card-header bg-light py-75">
                        <h6 class="card-title font-weight-bold mb-0" id="card-form-kopsurat-title">Tambah Kop Surat</h6>
                    </div>
                    <form id="form-kopsurat" enctype="multipart/form-data" class="card-body p-2">
                        <input type="hidden" name="id" id="kop_id">
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="kop_nama" class="font-weight-bold">Nama Kop Surat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kop_nama" name="nama" placeholder="Contoh: SIG, MSU, PT. SKR" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="kop_ukuran" class="font-weight-bold">Ukuran KOP <span class="text-danger">*</span></label>
                                <select class="form-control custom-select" id="kop_ukuran" name="ukuran" required>
                                    <option value="A4" selected>A4 (21 x 29.7 cm)</option>
                                    <option value="F4">F4 / Folio (21.5 x 33 cm)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="kop_w" class="font-weight-bold">Lebar (W) <small class="text-muted font-italic">(Otomatis)</small></label>
                                <input type="text" class="form-control bg-light" id="kop_w" name="w" value="21cm" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="kop_h" class="font-weight-bold">Tinggi (H) <small class="text-muted font-italic">(Otomatis)</small></label>
                                <input type="text" class="form-control bg-light" id="kop_h" name="h" value="29.7cm" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="kop_file" class="font-weight-bold">Background Kop Surat <span class="text-danger" id="kop-file-req">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="kop_file" name="file_kop" accept="image/png, image/jpeg, image/jpg">
                                <label class="custom-file-label" for="kop_file" id="kop_file_label">Pilih gambar background (PNG / JPG max 2MB)...</label>
                            </div>
                            <small class="text-muted d-block mt-25">Disarankan menggunakan gambar proporsional sesuai ukuran A4/F4.</small>
                            <div class="mt-1 d-none" id="kop_preview_wrap">
                                <img id="kop_preview_img" src="" alt="Preview Background" style="max-height: 140px; border: 1px dashed #ced4da; border-radius: 4px; padding: 4px;">
                            </div>
                        </div>

                        <!-- Collapse Pengaturan Margin (Opsional) -->
                        <div class="mb-1">
                            <a class="text-primary font-weight-bold" data-toggle="collapse" href="#collapseMarginKop" role="button" aria-expanded="false">
                                <i class="fas fa-sliders-h mr-25"></i> Pengaturan Margin & Posisi Dokumen (Opsional)
                            </a>
                        </div>
                        <div class="collapse" id="collapseMarginKop">
                            <div class="bg-light p-1 rounded mb-2 border">
                                <div class="row">
                                    <div class="col-md-3 col-6 form-group mb-1">
                                        <label class="small mb-25">Posisi Top (mt)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="mt" id="kop_mt" value="0">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-1">
                                        <label class="small mb-25">Posisi Bottom (mb)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="mb" id="kop_mb" value="0">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-1">
                                        <label class="small mb-25">Posisi Left (ml)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="ml" id="kop_ml" value="0">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-1">
                                        <label class="small mb-25">Posisi Right (mr)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="mr" id="kop_mr" value="0">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 col-6 form-group mb-0">
                                        <label class="small mb-25">Padding Top (pmt)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="pmt" id="kop_pmt" value="30">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-0">
                                        <label class="small mb-25">Padding Bottom (pmb)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="pmb" id="kop_pmb" value="25">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-0">
                                        <label class="small mb-25">Padding Left (pml)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="pml" id="kop_pml" value="15">
                                    </div>
                                    <div class="col-md-3 col-6 form-group mb-0">
                                        <label class="small mb-25">Padding Right (pmr)</label>
                                        <input type="number" step="any" class="form-control form-control-sm" name="pmr" id="kop_pmr" value="15">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-batal-form-kopsurat">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btn-simpan-form-kopsurat">
                                <i class="fas fa-save mr-25"></i> Simpan Kop Surat
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table List Kop Surat -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="tbl-kopsurat-list">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%" class="text-center">Preview</th>
                                <th>Nama Kop</th>
                                <th width="15%" class="text-center">Ukuran</th>
                                <th width="20%" class="text-center">Dimensi (W x H)</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-kopsurat-list">
                            <tr><td colspan="6" class="text-center py-2">Memuat daftar kop surat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>