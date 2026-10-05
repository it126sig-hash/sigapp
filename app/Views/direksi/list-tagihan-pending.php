<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">Daftar Surat Tagihan Menunggu Persetujuan</h4>
            </div>
            <div class="card-body mt-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-pending-tagihan">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nomor Surat</th>
                                <th>Tanggal Invoice</th>
                                <th>Konsumen</th>
                                <th>Kavling</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('page-script') ?>
<script>
    $(document).ready(function() {
        let table = $('#table-pending-tagihan').DataTable({
            ajax: base_url + 'direksi/get_pending_tagihan',
            columns: [
                { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center' },
                { data: 'nomor_surat' },
                { data: 'tanggal_invoice' },
                { data: 'nama_konsumen' },
                { data: null, render: (data) => `${data.nama_proyek || '-'} - ${data.nama_jalan || '-'} No. ${data.no_kavling || '-'}` },
                { 
                    data: 'no_inv',
                    className: 'text-center',
                    render: function(data) {
                        return `<button class="btn btn-sm btn-success btn-sign" data-id="${data}"><i class="fas fa-check-circle"></i> Tanda Tangan</button>
                                <a href="${base_url}keuangan/download_penagihan?id=${data}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Lihat</a>`;
                    }
                }
            ]
        });

        $('#table-pending-tagihan').on('click', '.btn-sign', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Tanda Tangan Surat?',
                text: "Anda akan menyetujui dan menandatangani surat ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28c76f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Tanda Tangan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: base_url + 'direksi/sign_tagihan',
                        type: 'POST',
                        data: {
                            no_inv: id,
                            [csrfName]: csrfHash
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.token) csrfHash = res.token;
                            if (res.success) {
                                Swal.fire('Berhasil!', res.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
