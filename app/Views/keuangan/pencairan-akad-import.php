<script>
  var csrfName = '<?= csrf_token() ?>';
  var csrfHash = '<?= csrf_hash() ?>';
</script>
<style>
  .pa-import-page {
    text-transform: uppercase;
  }

  .pa-import-page .card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: none;
  }

  .pa-import-page label {
    color: #6b7280;
    font-size: .72rem;
    font-weight: 700;
  }

  .pa-import-page .pa-import-steps {
    color: #6b7280;
    font-size: .8rem;
    text-transform: none;
  }

  .pa-import-page .pa-import-steps li {
    margin-bottom: .35rem;
  }

  #pa_import_result {
    font-size: .78rem;
  }

  #pa_import_result table th,
  #pa_import_result table td {
    padding: .4rem .5rem;
    vertical-align: middle;
  }
</style>

<div class="app-content content pa-import-page">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <section>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0"><?= $data['title'] ?></h5>
          </div>
          <div class="card-body">
            <ol class="pa-import-steps">
              <li>Download template CSV (opsional filter proyek) — hanya berisi item retensi/tenor yang sudah punya nominal dan belum cair penuh.</li>
              <li>Isi kolom <strong>no_pengajuan</strong>, <strong>tanggal_cair</strong> (format YYYY-MM-DD), dan opsional <strong>tanggal_pengajuan</strong> / <strong>nominal_cair</strong> / <strong>keterangan</strong>. Jangan ubah kolom lain — baris dengan kolom terkunci yang berubah akan ditolak.</li>
              <li>Baris dengan <strong>no_pengajuan</strong> sama (dalam 1 kavling) digabung jadi satu pengajuan + satu pencairan. Baris dengan <strong>tanggal_cair</strong> kosong akan dilewati.</li>
              <li>Upload CSV yang sudah diisi. Lampiran surat bersifat opsional untuk pengajuan hasil import.</li>
            </ol>

            <a id="pa_download_template" href="<?= base_url('keuangan/hasil-akad/export-template') ?>" class="btn btn-outline-primary btn-sm mb-2">
              <i class="fas fa-file-csv mr-25"></i> Download Template CSV
            </a>

            <form id="pa_import_form" class="border-top pt-2">
              <div class="form-group">
                <label>File CSV yang sudah diisi</label>
                <input type="file" id="pa_csv_file" name="csv_file" accept=".csv" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Lampiran Surat (opsional)</label>
                <input type="file" id="pa_lampiran" name="lampiran_surat" class="form-control">
              </div>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-upload mr-25"></i> Import
              </button>
            </form>

            <div id="pa_import_result" class="mt-2"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script src="<?= base_url() ?>app-assets/vendors/js/extensions/sweetalert2.all.min.js"></script>
<script>
  $(function() {
    $("#pa_download_template").on("click", function(e) {
      const proyek = activeProyekId();
      if (proyek) {
        this.href = base_url + "keuangan/hasil-akad/export-template?id_proyek=" + proyek;
      }
    });

    function renderImportResult(r) {
      if (!r.errors) {
        $("#pa_import_result").html('<div class="alert alert-danger">' + (r.message || "Import gagal") + '</div>');
        return;
      }

      let html = '<div class="alert ' + (r.imported > 0 ? "alert-success" : "alert-warning") + '">' + r.message + '</div>';

      if (r.errors.length > 0) {
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Baris</th><th>ID Item</th><th>Alasan Ditolak</th></tr></thead><tbody>';
        r.errors.forEach(function(err) {
          html += '<tr><td>' + err.baris + '</td><td>' + err.id_item + '</td><td>' + $("<div>").text(err.alasan).html() + '</td></tr>';
        });
        html += '</tbody></table>';
      }

      $("#pa_import_result").html(html);
    }

    $("#pa_import_form").on("submit", function(e) {
      e.preventDefault();

      const fd = new FormData(this);
      fd.append(csrfName, csrfHash);

      $.ajax({
        url: base_url + "keuangan/hasil-akad/import",
        type: "POST",
        data: fd,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(r) {
          if (r.token) csrfHash = r.token;
          renderImportResult(r);
        },
        error: function() {
          $("#pa_import_result").html('<div class="alert alert-danger">Terjadi kesalahan saat mengirim file.</div>');
        }
      });
    });
  });
</script>
