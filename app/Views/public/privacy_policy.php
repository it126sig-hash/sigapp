<?= $this->extend('public/legal_layout') ?>

<?= $this->section('content') ?>
<main id="legal-content">
    <section class="legal-hero">
        <div class="legal-container">
            <span class="legal-eyebrow">Dokumen Legal</span>
            <h1>Kebijakan Privasi</h1>
            <p class="legal-lead">Kami menghargai kepercayaan Anda. Halaman ini menjelaskan bagaimana SIGAPP mengelola dan melindungi data saat Anda menggunakan layanan kami.</p>
            <p class="legal-updated">Terakhir diperbarui: 2 September 2026</p>
        </div>
    </section>

    <div class="legal-container legal-content-grid">
        <aside class="legal-toc" aria-label="Daftar isi">
            <p class="legal-toc-title">Daftar isi</p>
            <ol>
                <li><a href="#informasi">Informasi yang dikumpulkan</a></li>
                <li><a href="#penggunaan">Penggunaan informasi</a></li>
                <li><a href="#penyimpanan">Penyimpanan dan keamanan</a></li>
                <li><a href="#pembagian">Pembagian data</a></li>
                <li><a href="#hak">Hak pengguna</a></li>
                <li><a href="#perubahan">Perubahan kebijakan</a></li>
                <li><a href="#kontak">Hubungi kami</a></li>
            </ol>
        </aside>

        <article class="legal-article">
            <div class="legal-intro-note">
                Kebijakan ini berlaku untuk penggunaan platform SIGAPP, termasuk situs web dan fitur pendukung yang terhubung dengan layanan SIGAPP.
            </div>

            <section id="informasi">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">1. Informasi yang Kami Kumpulkan</h2>
                </div>
                <p>Kami dapat mengumpulkan informasi yang Anda berikan saat membuat atau menggunakan akun, seperti nama, alamat email, nomor telepon, informasi profil, serta data pekerjaan yang dimasukkan ke dalam platform.</p>
                <p>Kami juga dapat mencatat informasi teknis secara otomatis, seperti alamat IP, jenis perangkat dan peramban, waktu akses, serta aktivitas penggunaan yang diperlukan untuk menjaga keamanan dan kinerja layanan.</p>
            </section>

            <section id="penggunaan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">2. Penggunaan Informasi</h2>
                </div>
                <p>Informasi digunakan untuk menyediakan fitur SIGAPP, mengelola akses akun, memproses aktivitas pengguna, memberikan dukungan, meningkatkan kualitas layanan, dan menjaga keamanan sistem.</p>
                <p>Kami dapat menggunakan informasi kontak untuk mengirim pemberitahuan layanan, perubahan penting, atau informasi administratif yang berkaitan dengan akun Anda.</p>
            </section>

            <section id="penyimpanan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">3. Penyimpanan dan Keamanan Data</h2>
                </div>
                <p>Kami menerapkan langkah teknis dan organisatoris yang wajar untuk melindungi data dari akses, perubahan, pengungkapan, atau kehilangan yang tidak sah. Data disimpan selama masih diperlukan untuk menyediakan layanan, memenuhi kewajiban hukum, dan menyelesaikan sengketa.</p>
                <p>Tidak ada sistem elektronik yang sepenuhnya bebas risiko. Karena itu, pengguna juga bertanggung jawab menjaga kerahasiaan kredensial akun dan segera melaporkan dugaan penyalahgunaan.</p>
            </section>

            <section id="pembagian">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">4. Pembagian Data</h2>
                </div>
                <p>Kami tidak menjual data pribadi pengguna. Data hanya dapat dibagikan kepada penyedia layanan yang membantu operasional SIGAPP, atas instruksi organisasi pemilik akun, untuk memenuhi kewajiban hukum, atau untuk melindungi keamanan dan hak pihak terkait.</p>
                <p>Penyedia layanan yang menerima data diwajibkan menggunakannya hanya untuk tujuan yang telah ditentukan dan menerapkan perlindungan yang sesuai.</p>
            </section>

            <section id="hak">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">5. Hak Pengguna</h2>
                </div>
                <p>Sesuai ketentuan yang berlaku dan kewenangan organisasi pengelola akun, Anda dapat meminta akses, pembaruan, koreksi, atau penghapusan data pribadi. Permintaan tertentu mungkin memerlukan verifikasi identitas dan dapat dibatasi oleh kewajiban penyimpanan data.</p>
            </section>

            <section id="perubahan">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">6. Perubahan Kebijakan</h2>
                </div>
                <p>Kebijakan Privasi ini dapat diperbarui untuk menyesuaikan perubahan layanan, praktik keamanan, atau peraturan. Tanggal pembaruan terbaru akan ditampilkan pada bagian atas halaman ini.</p>
            </section>

            <section id="kontak">
                <div class="divider divider-left legal-section-heading">
                    <h2 class="divider-text">7. Hubungi Kami</h2>
                </div>
                <p>Jika Anda memiliki pertanyaan atau permintaan terkait privasi, silakan hubungi tim SIGAPP melalui kanal dukungan resmi yang diberikan oleh pengelola akun atau organisasi Anda.</p>
            </section>
        </article>
    </div>
</main>
<?= $this->endSection() ?>
