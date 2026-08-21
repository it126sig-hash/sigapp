<script>
    const pl_id_proyek = "<?= isset($data['proyek']->id_proyek) ? $data['proyek']->id_proyek : '' ?>";
    const pl_nama_proyek = "<?= isset($data['proyek']->nama_proyek) ? $data['proyek']->nama_proyek : '' ?>";
</script>
<div class="modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
        <form id="fm-add_kavling" class="add-new-record modal-content pt-0">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data</h5>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="form-group hidden">
                    <label class="form-label" for="basic-icon-default-fullname">Proyek</label>
                    <input type="hidden" class="form-control" id="points" readonly name="points" value="" />
                    <input type="hidden" class="form-control id_kavling" readonly name="id_kavling" value="" />
                    <input type="hidden" class="form-control" id="nama_proyek" readonly name="nama_proyek"
                        placeholder="ASI" />
                    <input type="hidden" name="id_proyek" id="id_proyek">
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Jenis</label>
                    <select id="id_jenis" name="id_jenis" class="select2 id_jenis custom-select">
                        <option value=""> - </option>
                        <option value="kavling">Kavling</option>
                        <option value="jalan">Jalan</option>
                        <option value="fasos">Fasos</option>
                        <option value="rth">RTH</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Cluster</label>
                    <select id="id_cluster" name="id_cluster" class="select2 id_cluster custom-select"></select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-post">Jalan</label>
                    <select disabled id="id_jalan" name="id_jalan" class="select id_jalan 2custom-select"></select>
                </div>
                <hr>
                <span>Luas di Lapangan : <br>
                    <span class='t_luas_produksi'></span>
                </span>
                <br>
                <br>
                <span>Luas di Sertifikat : <br>
                    <span class='t_luas_legal'></span>
                </span>
                <hr>
                <div class="form-group">
                    <label class="form-label" for="f_luas">Luas</label>
                    <input type="text" class="form-control" id="f_luas" name="f_luas" value="" placeholder="90" />
                </div>

                <!-- kavling -->
                <div id="div_kavling" class="h">
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-fullname">No Rumah </label>
                        <input type="text" class="form-control" id="no_kavling" name="no_kavling" value=""
                            placeholder="31" />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-post">Tipe</label>
                        <select id="id_tipe" name="id_tipe" class="select2 id_tipe custom-select"></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="basic-icon-default-post">Kategori Kavling</label>
                        <select id="status_tanah" name="status_tanah" class="select2 custom-select"
                            placeholder="standar/kelebihan tanah">
                            <option value="Standar">Standar</option>
                            <option value="Kelebihan Tanah">Kelebihan Tanah</option>
                        </select>
                    </div>
                </div>

                <!-- Fasos -->
                <div id="div_fasos" class="h">
                    <div class="form-group">
                        <label for="f_nama">Nama</label>
                        <input type="text" class="form-control" id="f_nama" name="f_nama" value=""
                            placeholder="FASUM/SOS" />
                    </div>
                </div>

                <!-- luas -->
                <!-- <div class="div_luas" class="h">
                    </div> -->

                <!-- jalan -->
                <div id="div_jalan" class="h">
                    <div class="form-group">
                        <label for="f_planning_keterangan">Keterangan</label>
                        <textarea class="form-control" id="f_planning_keterangan" name="f_planning_keterangan" rows="3"
                            placeholder="Keterangan"></textarea>
                    </div>
                </div>
                <button id="pindah_lokasi_btn" onclick="pindah_kavling()" type="button"
                    class="btn btn-outline-primary btn-block waves-effect">Pindah Lokasi</button>



                <div class="form-group">
                    <label class="form-label" for="basic-icon-default-fullname">*catatan: gunakan titik koma ";" untuk
                        pemisah nomor rumah jika akan input rumah lebih dari 1 kavling sekaligus</label>
                </div>
                <a id="add-form-btn" class="btn btn-primary data-submit mr-1" onclick="add_kavling()"
                    href="javascript:void(0)">Simpan</a>
                <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url() ?>assets/js/siteplan/planning.js?v=<?= filemtime(FCPATH . 'assets/js/siteplan/planning.js') ?>"></script>