<?= $this->extend('public/legal_layout') ?>

<?= $this->section('content') ?>
<main id="legal-content">
    <section class="legal-hero">
        <div class="legal-container">
            <span class="legal-eyebrow">Dokumen Legal</span>
            <h1>Ketentuan Layanan</h1>
            <p class="legal-lead">Ketentuan ini mengatur akses dan penggunaan SIGAPP sebagai platform pengelolaan proyek properti.</p>
            <p class="legal-updated">Terakhir diperbarui: 2 September 2026</p>
        </div>
    </section>

    <div class="legal-container legal-content-grid">
        <aside class="legal-toc" aria-label="Daftar isi">
            <p class="legal-toc-title">Daftar isi</p>
            <ol>
                <li><a href="#penerimaan">Penerimaan ketentuan</a></li>
                <li><a href="#layanan">Layanan SIGAPP</a></li>
                <li><a href="#akun">Akun dan akses</a></li>
                <li><a href="#penggunaan">Penggunaan yang diperbolehkan</a></li>
                <li><a href="#data">Data pengguna</a></li>
                <li><a href="#ketersediaan">Ketersediaan layanan</a></li>
                <li><a href="#penghentian">Penghentian akses</a></li>
                <li><a href="#perubahan">Perubahan ketentuan</a></li>
                <li><a href="#kontak">Hubungi kami</a></li>
            </ol>
        </aside>

        <article class="legal-article">
            <div class="legal-intro-note">
                Dengan mengakses atau menggunakan SIGAPP, Anda menyatakan telah membaca, memahami, dan menyetujui Ketentuan Layanan ini.
            </div>

            <section id="penerimaan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">1. Penerimaan Ketentuan</h2>
                </div>
                <p>Penggunaan SIGAPP tunduk pada ketentuan ini dan kebijakan lain yang ditampilkan dalam layanan. Jika Anda menggunakan SIGAPP atas nama organisasi, Anda menyatakan memiliki kewenangan untuk bertindak bagi organisasi tersebut.</p>
            </section>

            <section id="layanan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">2. Layanan SIGAPP</h2>
                </div>
                <p>SIGAPP menyediakan fitur untuk membantu pengelolaan proyek properti, termasuk data kavling, transaksi, keuangan, dokumen, dan aktivitas operasional terkait. Fitur yang tersedia dapat berbeda sesuai paket, peran, atau konfigurasi organisasi.</p>
            </section>

            <section id="akun">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">3. Akun dan Akses</h2>
                </div>
                <p>Anda bertanggung jawab memberikan informasi akun yang benar, menjaga kerahasiaan kredensial, dan memastikan aktivitas melalui akun sesuai kewenangan yang diberikan. Segera laporkan akses yang tidak sah kepada pengelola akun atau tim dukungan SIGAPP.</p>
            </section>

            <section id="penggunaan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">4. Penggunaan yang Diperbolehkan</h2>
                </div>
                <p>Anda wajib menggunakan SIGAPP secara sah dan sesuai tujuan layanan. Anda dilarang mencoba mengakses data tanpa izin, mengganggu keamanan atau kinerja sistem, menyebarkan kode berbahaya, menyalahgunakan identitas pengguna lain, atau menggunakan layanan untuk kegiatan yang melanggar hukum.</p>
            </section>

            <section id="data">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">5. Data Pengguna</h2>
                </div>
                <p>Anda atau organisasi Anda tetap bertanggung jawab atas keakuratan, legalitas, dan hak penggunaan data yang dimasukkan ke SIGAPP. Anda memberikan izin yang diperlukan kepada SIGAPP untuk memproses data tersebut sejauh dibutuhkan untuk menyediakan dan mengamankan layanan.</p>
                <p>Pengelolaan data pribadi dijelaskan lebih lanjut dalam <a href="<?= base_url('privacy-policy') ?>">Kebijakan Privasi SIGAPP</a>.</p>
            </section>

            <section id="ketersediaan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">6. Ketersediaan dan Perubahan Layanan</h2>
                </div>
                <p>Kami berupaya menjaga layanan tetap tersedia dan aman, tetapi tidak menjamin layanan selalu bebas gangguan atau kesalahan. Pemeliharaan, peningkatan sistem, keadaan darurat, atau faktor di luar kendali dapat memengaruhi ketersediaan layanan.</p>
                <p>Fitur dapat ditambah, diubah, atau dihentikan dengan pemberitahuan yang wajar apabila perubahan tersebut berdampak penting bagi pengguna.</p>
            </section>

            <section id="penghentian">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">7. Penghentian Akses</h2>
                </div>
                <p>Akses dapat dibatasi atau dihentikan jika terjadi pelanggaran ketentuan, risiko keamanan, kewajiban hukum, berakhirnya hubungan layanan dengan organisasi, atau permintaan dari pengelola akun yang berwenang.</p>
            </section>

            <section id="perubahan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">8. Perubahan Ketentuan</h2>
                </div>
                <p>Ketentuan ini dapat diperbarui dari waktu ke waktu. Perubahan berlaku sejak tanggal yang dicantumkan pada halaman ini. Penggunaan layanan setelah perubahan berlaku dianggap sebagai penerimaan terhadap ketentuan terbaru.</p>
            </section>

            <section id="kontak">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">9. Hubungi Kami</h2>
                </div>
                <p>Untuk pertanyaan mengenai Ketentuan Layanan, silakan hubungi tim SIGAPP melalui kanal dukungan resmi yang diberikan oleh pengelola akun atau organisasi Anda.</p>
            </section>
        </article>
    </div>
</main>
<?= $this->endSection() ?>
