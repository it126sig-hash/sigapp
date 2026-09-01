<!-- Modal Action Bonus -->
<div class="modal fade" id="modalBonusAction" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBonusActionTitle">Aksi Bonus Referral</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="action_id_bonus">
                <input type="hidden" id="action_type">
                
                <div id="form-konfirmasi" class="d-none">
                    <p>Konfirmasi kelayakan bonus ini. Anda bisa mengubah nominal default jika diperlukan.</p>
                    <div class="form-group">
                        <label>Nominal Bonus</label>
                        <input type="text" class="form-control number-format" id="action_nominal_bonus">
                    </div>
                </div>

                <div id="form-bayar-promosi" class="d-none">
                    <p>Catat pembayaran bonus dari kas Promosi.</p>
                    <div class="form-group">
                        <label>Bukti Bayar (Image/PDF)</label>
                        <input type="file" class="form-control-file" id="action_bukti_bayar">
                    </div>
                </div>

                <div id="form-keterangan" class="d-none">
                    <div class="form-group">
                        <label>Keterangan Tambahan / Alasan Batal</label>
                        <textarea class="form-control" id="action_keterangan" rows="3"></textarea>
                    </div>
                </div>

                <div id="form-submit-keuangan" class="d-none">
                    <p>Ajukan pencairan bonus ini ke departemen Keuangan.</p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-save-action">Simpan</button>
            </div>
        </div>
    </div>
</div>
