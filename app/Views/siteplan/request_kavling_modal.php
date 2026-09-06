<div class="modal fade" id="modal-request-kavling" tabindex="-1" role="dialog" aria-labelledby="modal-request-kavling-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="modal-request-kavling-label">
                    <i class="fa fa-pencil-square-o mr-1 text-primary"></i> Form Pengajuan Request Kavling / Tipe
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-request-kavling">
                <div class="modal-body">
                    <input type="hidden" name="id_proyek" id="req-id_proyek" value="<?= isset($data['proyek']->id_proyek) ? esc($data['proyek']->id_proyek) : '' ?>">

                    <div class="alert alert-info py-2" role="alert">
                        <i class="fa fa-info-circle mr-1"></i> Form ini digunakan untuk mengajukan penambahan unit kavling baru atau perubahan tipe ke tim <strong>Planning</strong>.
                    </div>

                    <!-- Jenis Request -->
                    <div class="form-group">
                        <label class="font-weight-bold">Jenis Pengajuan <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center">
                            <div class="custom-control custom-radio mr-3">
                                <input type="radio" id="req-jenis-baru" name="jenis_request" value="tambah_baru" class="custom-control-input" checked>
                                <label class="custom-control-label" for="req-jenis-baru">
                                    <i class="fa fa-plus-circle text-success mr-1"></i> Tambah Kavling Baru
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="req-jenis-ubah" name="jenis_request" value="ubah_tipe" class="custom-control-input">
                                <label class="custom-control-label" for="req-jenis-ubah">
                                    <i class="fa fa-exchange text-warning mr-1"></i> Ubah Tipe Kavling Existing
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Kavling Existing (Hanya untuk Ubah Tipe) -->
                    <div class="form-group d-none" id="wrap-req-kavling">
                        <label class="font-weight-bold">Pilih Kavling Existing <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="req-id_kavling" name="id_kavling" style="width: 100%;">
                            <option value="">-- Cari Kavling (Ketik Nomor/Jalan) --</option>
                        </select>
                        <small class="form-text text-muted">Ketik nama jalan atau nomor kavling untuk mencari.</small>
                    </div>

                    <div class="row">
                        <!-- Cluster -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Cluster</label>
                            <select class="form-control select2" id="req-id_cluster" name="id_cluster" style="width: 100%;">
                                <option value="">-- Pilih Cluster (Jika Ada) --</option>
                            </select>
                            <small class="form-text text-muted">Kosongkan jika cluster belum terdaftar di sistem.</small>
                        </div>

                        <!-- Jalan / Blok -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jalan / Blok</label>
                            <select class="form-control select2" id="req-id_jalan" name="id_jalan" style="width: 100%;" disabled>
                                <option value="">-- Pilih Jalan/Blok (Jika Ada) --</option>
                            </select>
                            <small class="form-text text-muted">Pilih cluster terlebih dahulu untuk memilih jalan.</small>
                        </div>
                    </div>

                    <!-- Tipe Kavling -->
                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Rumah / Bangunan</label>
                        <select class="form-control select2" id="req-id_tipe" name="id_tipe" style="width: 100%;">
                            <option value="">-- Pilih Tipe (Jika Ada) --</option>
                        </select>
                        <small class="form-text text-muted">Kosongkan bila tipe baru belum terdaftar di master tipe.</small>
                    </div>

                    <!-- Keterangan -->
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">
                            Keterangan / Request Detail <span class="text-danger" id="req-keterangan-req-star">*</span>
                        </label>
                        <textarea class="form-control" id="req-keterangan" name="keterangan" rows="4" placeholder="Contoh: Tolong buatkan kavling di sebelah Blok A No. 5. Nama jalan baru: Jl. Mawar Indah, Cluster Bougenville. Ukuran kavling 6x12."></textarea>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Wajib diisi jika Cluster dan/atau Jalan belum tersedia di opsi atas untuk ditulis manual.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-request-kavling">
                        <i class="fa fa-paper-plane mr-1"></i> Kirim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
