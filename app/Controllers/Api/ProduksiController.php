<?php

namespace App\Controllers\Api;

use App\Models\GambarkerjaModel;
use App\Models\ChecklistWorkModel;
use App\Models\KavlingModel;
use App\Models\KomplainModel;
use App\Libraries\Mpdf_lib;
use App\Services\FileAccessService;
use App\Services\FinanceLedgerService;
use App\Services\KonsumenService;
use App\Services\ProduksiService;
use App\Services\ProduksiFileService;
use App\Repositories\ProduksiRepository;
use CodeIgniter\HTTP\ResponseInterface;

class ProduksiController extends BaseApiController
{
    protected ProduksiRepository  $repo;
    protected KonsumenService     $konsumenService;
    protected ProduksiService     $produksiService;
    protected ProduksiFileService $fileService;
    protected FileAccessService   $fileAccessService;
    protected FinanceLedgerService $ledgerService;
    protected GambarkerjaModel    $gambarkerjaModel;
    protected KavlingModel        $kavlingModel;
    protected ChecklistWorkModel  $cwModel;
    protected KomplainModel       $komplainModel;
    protected Mpdf_lib            $mpdf;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->repo              = new ProduksiRepository();
        $this->konsumenService   = new KonsumenService();
        $this->produksiService   = new ProduksiService();
        $this->fileService       = new ProduksiFileService();
        $this->fileAccessService = new FileAccessService();
        $this->ledgerService     = new FinanceLedgerService();
        $this->gambarkerjaModel  = new GambarkerjaModel();
        $this->kavlingModel      = new KavlingModel();
        $this->cwModel           = new ChecklistWorkModel();
        $this->komplainModel     = new KomplainModel();
        $this->mpdf              = new Mpdf_lib();
        $this->validation        = \Config\Services::validation();
        $this->db                = \Config\Database::connect();
    }

    public function get_data_by_id(): ResponseInterface
    {
        $idKavling  = (int) $this->request->getVar('id_kavling');
        $idProduksi = $this->request->getVar('id_produksi');

        $files = [];
        if ($idKavling) {
            $files = $this->repo->getFilesByKavling($idKavling);
            $files = $this->fileAccessService->addAccessUrlsToRows($files, 'file_produksi');
        }

        if ($idProduksi) {
            $r = $this->repo->getByIdWithUsers((int) $idProduksi);

            $r->token  = csrf_hash();
            $r->cl     = $this->cwModel
                ->select('checklist_work.*, username')
                ->where('id_kavling', $idKavling)
                ->join('users', 'checklist_work.produksi_cek = users.id')
                ->find();
            $r->files  = $files;
        } else {
            $r = ['token' => csrf_hash(), 'files' => $files];
        }

        return $this->response->setJSON($r);
    }

    public function history(): ResponseInterface
    {
        $idKavling = (int) $this->request->getVar('id_kavling');
        $limit = (int) ($this->request->getVar('history_limit') ?? 10);
        $limit = max(1, min(50, $limit));
        $offset = max(0, (int) ($this->request->getVar('history_offset') ?? 0));

        $total = $this->repo->countProduksiChangeHistory($idKavling);
        $history = $this->repo->getProduksiChangeHistory($idKavling, $limit, $offset);

        foreach ($history as $item) {
            $item->old_data = json_decode($item->old_data ?? '{}', true) ?: [];
            $item->new_data = json_decode($item->new_data ?? '{}', true) ?: [];
            $item->files = json_decode($item->files ?? '[]', true) ?: [];
        }

        return $this->response->setJSON([
            'token'               => csrf_hash(),
            'history'             => $history,
            'history_total'       => $total,
            'history_limit'       => $limit,
            'history_offset'      => $offset,
            'history_next_offset' => $offset + count($history),
            'history_has_more'    => ($offset + count($history)) < $total,
        ]);
    }

    public function getBayarProduksiListItem(): ResponseInterface
    {
        $search = trim((string) $this->request->getVar('search'));

        return $this->response->setJSON([
            'token'     => csrf_hash(),
            'list_item' => $this->repo->getBayarItemList($search),
        ]);
    }

    public function getBayarProduksi(): ResponseInterface
    {
        $idKavling = (int) $this->request->getVar('id_kavling');

        if ($idKavling <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'token'               => csrf_hash(),
            'id_kavling'          => $idKavling,
            'konsumen'            => $this->konsumenService->getByIDKavling($idKavling),
            'riwayat_bayar'       => $this->repo->getRiwayatBayarByKavling($idKavling),
            'list_bayar_produksi' => $this->repo->getBayarList($idKavling),
        ]);
    }

    public function saveBayarProduksi(): ResponseInterface
    {
        $idKavling       = (int) $this->request->getVar('id_kavling');
        $idItemProduksi  = (int) $this->request->getVar('bp-untuk_pembayaran');
        $tanggalBayar    = trim((string) $this->request->getVar('bp-tanggal_bayar'));
        $nominal         = str_replace(',', '', (string) $this->request->getVar('bp-nominal'));
        $keterangan      = (string) $this->request->getVar('bp-keterangan');

        if ($idKavling <= 0 || $idItemProduksi <= 0 || $tanggalBayar === '' || $nominal === '' || (float) $nominal <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak lengkap',
            ]);
        }

        try {
            $this->db->transBegin();

            $idBayarProduksi = $this->repo->insertBayarSingle([
                'id_kavling'       => $idKavling,
                'id_item_produksi' => $idItemProduksi,
                'nominal'          => $nominal,
                'keterangan'       => $keterangan,
                'tanggal_bayar'    => $tanggalBayar,
                'add_by'           => user_id(),
            ]);

            if (!$idBayarProduksi) {
                throw new \RuntimeException('data gagal disimpan');
            }

            $this->ledgerService->recordExpenseFromBayarProduksi((int) $idBayarProduksi, user_id());

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('transaksi gagal');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data gagal disimpan',
            ]);
        }

        return $this->response->setJSON([
            'token'      => csrf_hash(),
            'id_kavling' => $idKavling,
            'success'    => true,
            'messages'   => 'data berhasil disimpan',
        ]);
    }

    public function deleteBayarProduksi(): ResponseInterface
    {
        $id = (int) $this->request->getVar('id');

        if ($id <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data tidak ditemukan',
            ]);
        }

        try {
            $this->db->transBegin();

            $idKavling = $this->repo->deleteBayar($id);

            if ($idKavling === null) {
                throw new \RuntimeException('data gagal dihapus');
            }

            $this->ledgerService->voidByBayarProduksi($id, user_id());

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('transaksi gagal');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'data gagal dihapus',
            ]);
        }

        return $this->response->setJSON([
            'token'      => csrf_hash(),
            'id_kavling' => $idKavling,
            'success'    => true,
            'messages'   => 'data berhasil dihapus',
        ]);
    }

    public function get_data_komplain_by_id(): ResponseInterface
    {
        $r = ['token' => csrf_hash()];

        if ($this->request->getVar('id_kavling')) {
            $r['komplain'] = $this->kavlingModel
                ->select('
                    status_komplain, kavling.id_kavling, komplain.*,
                    s.username as username_komplain_oleh,
                    p.username as username_ditangani_oleh,
                    ss.username as username_selesai_oleh_sales,
                    sp.username as username_selesai_oleh_produksi,
                    lu.username as username_last_update
                ')
                ->join('komplain', 'komplain.id_komplain = kavling.id_komplain')
                ->join('users as s', 's.id = komplain.komplain_oleh', 'left')
                ->join('users as p', 'p.id = komplain.ditangani_oleh', 'left')
                ->join('users as ss', 'ss.id = komplain.selesai_oleh_sales', 'left')
                ->join('users as sp', 'sp.id = komplain.selesai_oleh_produksi', 'left')
                ->join('users as lu', 'lu.id = komplain.edit_by', 'left')
                ->where('id_kavling', $this->request->getVar('id_kavling'))
                ->first();
            if ($r['komplain']) {
                $r['komplain']->upload_komplain_sales_urls = $this->fileAccessService->pathUrlsFromDelimitedString($r['komplain']->upload_komplain_sales, 'komplain_sales');
                $r['komplain']->upload_komplain_produksi_urls = $this->fileAccessService->pathUrlsFromDelimitedString($r['komplain']->upload_komplain_produksi, 'komplain_produksi');
            }
        }

        return $this->response->setJSON($r);
    }

    public function save_komplain_produksi(): ResponseInterface
    {
        $response          = ['token' => csrf_hash()];
        $terimaKomplain    = $this->request->getVar('terima_komplain');
        $isSelesaiProduksi = $this->request->getVar('is_selesai_produksi');
        $idKavling         = $this->request->getVar('id_kavling');

        if ($terimaKomplain != 1) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => true, 'messages' => '']);
        }

        $idKomplain = (int) $this->request->getVar('id_komplain');

        // Verify id_komplain belongs to this kavling (prevent IDOR)
        $kavlingOwner = $this->kavlingModel
            ->where('id_kavling', (int) $idKavling)
            ->where('id_komplain', $idKomplain)
            ->first();

        if (!$kavlingOwner) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => 'Data komplain tidak ditemukan']);
        }

        $f = [
            'keterangan_ditangani' => $this->request->getVar('keterangan_ditangani'),
            'id_komplain'          => $idKomplain,
            'edit_by'              => user_id(),
        ];

        if ($isSelesaiProduksi != 1) {
            $this->validation->setRules(['keterangan_ditangani' => [
                'label'  => 'Keterangan',
                'rules'  => 'required|max_length[255]',
                'errors' => ['required' => 'Keterangan harus diisi'],
            ]]);

            if (!$this->validation->run($f)) {
                return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => $this->validation->listErrors()]);
            }

            $f['ditangani_oleh'] = user_id();
            $f['ditangani_tgl']  = date('Y-m-d');

            $db = \Config\Database::connect();
            $db->transStart();
            $this->komplainModel->update($f['id_komplain'], $f);
            $this->kavlingModel->update($idKavling, ['status_komplain' => 2]);
            $db->transComplete();

            $response['success']  = $db->transStatus() !== false;
            $response['messages'] = $response['success'] ? 'Successfully updated' : 'Terjadi kesalahan';

            return $this->response->setJSON($response);
        }

        $f['selesai_keterangan_produksi'] = $this->request->getVar('selesai_keterangan_produksi');
        $f['is_selesai_produksi']         = 1;

        $this->validation->setRules([
            'selesai_keterangan_produksi' => [
                'label'  => 'Keterangan',
                'rules'  => 'required|max_length[255]',
                'errors' => ['required' => 'Keterangan harus diisi'],
            ],
            'upload_komplain_produksi' => [
                'label' => 'File',
                'rules' => 'uploaded[upload_komplain_produksi]'
                    . '|mime_in[upload_komplain_produksi,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                    . '|max_size[upload_komplain_produksi,12000]'
                    . '|max_dims[upload_komplain_produksi,6000,6000]',
            ],
        ]);

        if (!$this->validation->run($f)) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => $this->validation->listErrors()]);
        }

        $f['upload_komplain_produksi'] = '';
        $lok = 'uploads/komplain_produksi/' . date('Ymd') . '/';
        foreach ($this->request->getFileMultiple('upload_komplain_produksi') as $img) {
            $name = $img->getRandomName();
            $f['upload_komplain_produksi'] .= $lok . $name . ';';
            $this->fileAccessService->storeAs($img, $lok, $name);
        }

        $f['selesai_oleh_produksi'] = user_id();
        $f['selesai_tgl_produksi']  = date('Y-m-d');

        $db = \Config\Database::connect();
        $db->transStart();
        $this->komplainModel->update($f['id_komplain'], $f);
        $this->kavlingModel->update($idKavling, ['status_komplain' => 3]);
        $db->transComplete();

        $response['success']  = $db->transStatus() !== false;
        $response['messages'] = $response['success'] ? 'Successfully updated' : 'Terjadi kesalahan';

        return $this->response->setJSON($response);
    }

    public function save(): ResponseInterface
    {
        $result = $this->produksiService->save(
            (array) $this->request->getVar(),
            (array) $this->request->getFiles(),
            user_id()
        );

        $result['token'] = csrf_hash();
        return $this->response->setJSON($result);
    }

    public function saveSLf(): ResponseInterface
    {
        $idKavling = implode(',', $this->request->getVar('id_kavling'));

        $data = [
            'no_slf'                       => $this->request->getVar('no_slf'),
            'tanggal_slf'                  => $this->request->getVar('tanggal_slf'),
            'penanggungjawab'              => $this->request->getVar('penanggungjawab'),
            'id_proyek'                    => $this->request->getVar('id_proyek'),
            'fungsi_utama'                 => $this->request->getVar('fungsi_utama'),
            'fungsi_tambahan'              => $this->request->getVar('fungsi_tambahan'),
            'jenis_bangunan'               => $this->request->getVar('jenis_bangunan'),
            'nama_bangunan'                => $this->request->getVar('nama_bangunan'),
            'nomor_pendaftaran_bangunan'   => $this->request->getVar('nomor_pendaftaran_bangunan'),
            'penerbitan_slf_no'            => $this->request->getVar('penerbitan_slf_no'),
            'penerbitan_slf_tgl'           => $this->request->getVar('penerbitan_slf_tgl'),
            'perpanjangan_slf_ke'          => $this->request->getVar('perpanjangan_slf_ke'),
            'persyaratan_administrasi'     => $this->request->getVar('persyaratan_administrasi'),
            'persyaratan_fungsi_bangunan'  => $this->request->getVar('persyaratan_fungsi_bangunan'),
            'persyaratan_peruntukan'       => $this->request->getVar('persyaratan_peruntukan'),
            'persyaratan_tata_bangunan'    => $this->request->getVar('persyaratan_tata_bangunan'),
            'persyaratan_kelaikan'         => $this->request->getVar('persyaratan_kelaikan'),
            'id_kavling'                   => $idKavling,
            'created_at'                   => date('Y-m-d H:i:s'),
            'add_by'                       => user_id(),
        ];

        $success = $this->repo->insertSlf($data);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Berhasil menyimpan data' : 'Gagal menyimpan data',
        ]);
    }

    public function getSlf(): ResponseInterface
    {
        return $this->response->setJSON(['data' => $this->repo->getSlfList()]);
    }

    public function hapusSLF(): ResponseInterface
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => 'ID SLF tidak ditemukan']);
        }

        $success = $this->repo->deleteSlfById((int) $id);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Data SLF berhasil dihapus' : 'Gagal menghapus data SLF',
        ]);
    }

    public function getSLFPDF($id)
    {
        if (!$id) return false;

        $listSlf = $this->repo->getSlfById((int) $id);
        if (!$listSlf) return false;

        $idKavlingList = explode(',', $listSlf->id_kavling);

        $data = [
            'list_slf' => $listSlf,
            'kavling'  => $this->repo->getSlfKavlingData($idKavlingList),
            'mpdf'     => $this->mpdf,
        ];

        $filename = date('y-m-d-H-i-s') . '- SLF';
        $html     = [view('pdf/slf', $data), view('pdf/slf_page3', $data)];

        $this->mpdf->generate($html, $filename, '');
        exit();
    }

    public function getKavling(): ResponseInterface
    {
        $idProyek = (int) $this->request->getVar('id_proyek');
        $search   = (string) ($this->request->getVar('search') ?? '');

        return $this->response->setJSON([
            'token' => csrf_hash(),
            'data'  => $this->repo->getKavlingByProyek($idProyek, $search),
        ]);
    }

    public function hapus_foto(): ResponseInterface
    {
        $id   = (int) $this->request->getPost('id');
        $file = $this->repo->getFileById($id);

        if (!$file) {
            return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => 'File tidak ditemukan']);
        }

        if ($this->fileService->moveFotoToTrash($file)) {
            $deleted = $this->repo->deleteFileById($id);
            if ($deleted && $this->repo->hasProduksiChangeHistoryTable()) {
                $this->repo->insertProduksiChangeHistory([
                    'id_kavling'  => (int) $file->id_kavling,
                    'id_produksi' => null,
                    'action'      => 'delete_file',
                    'summary'     => 'File/foto produksi dihapus',
                    'old_data'    => null,
                    'new_data'    => null,
                    'files'       => json_encode([[
                        'kategori'        => $file->kategori ?? null,
                        'file_name'       => $file->file_name ?? null,
                        'file_keterangan' => $file->file_keterangan ?? null,
                    ]]),
                    'add_by'      => user_id(),
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => $deleted,
                'messages' => $deleted ? 'File berhasil dipindahkan ke folder trash' : 'Terjadi kesalahan saat menghapus data file dari database',
            ]);
        }

        return $this->response->setJSON(['token' => csrf_hash(), 'success' => false, 'messages' => 'Terjadi kesalahan saat memindahkan file ke folder trash']);
    }

    public function get_gambarkerja(): ResponseInterface
    {
        $id = (int) $this->request->getVar('id_gambar_kerja');
        $r  = $this->gambarkerjaModel->where('id_gambar_kerja', $id)->first();

        if (!$r) {
            return $this->response->setStatusCode(404)->setJSON(['messages' => 'File tidak ditemukan']);
        }

        return $this->response->setJSON(['lokasi' => $this->fileAccessService->accessUrl('gambar_kerja', $id, true)]);
    }

    public function add_jalan(): ResponseInterface
    {
        $roleIds = array_map('intval', array_keys(user()->getRoles()));

        if (!in_array(1, $roleIds, true) && !in_array(7, $roleIds, true)) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Kamu tidak memiliki akses untuk menambahkan jalan produksi',
            ]);
        }

        $idJalan = (int) $this->request->getPost('id_jalan');
        $points  = trim((string) $this->request->getPost('points'));
        $pointList = array_filter(array_map('trim', explode(',', $points)), static function ($point) {
            return $point !== '';
        });

        if ($idJalan <= 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Blok/Jalan harus diisi',
            ]);
        }

        if (count($pointList) < 6 || count($pointList) % 2 !== 0) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Seleksi manual minimal 3 titik',
            ]);
        }

        $progres = $this->request->getPost('f_progres_jalan');
        $progres = $progres === null || $progres === '' ? 0 : max(0, min(100, (int) $progres));

        $now = date('Y-m-d H:i:s');
        $fields = [
            'id_jalan'             => $idJalan,
            'tipe'                 => 'jalan',
            'scope'                => 'produksi',
            'points'               => implode(',', $pointList),
            'progres'              => $progres,
            'produksi_luas'        => $this->request->getPost('f_produksi_luas'),
            'produksi_keterangan'  => $this->request->getPost('f_produksi_keterangan'),
            'produksi_add_by'      => user_id(),
            'produksi_created_at'  => $now,
            'produksi_edit_by'     => user_id(),
            'produksi_updated_at'  => $now,
        ];

        $success = $this->repo->createOther($fields);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Jalan produksi berhasil ditambahkan' : 'Jalan produksi gagal ditambahkan',
        ]);
    }

    public function edit_others(): ResponseInterface
    {
        $id    = (int) $this->request->getPost('id_kavling');
        $other = $this->repo->getOtherById($id);

        if (!$other) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Data jalan tidak ditemukan',
            ]);
        }

        $progres = $this->request->getPost('f_progres_jalan');
        $progres = $progres === null || $progres === '' ? 0 : max(0, min(100, (int) $progres));
        $now     = date('Y-m-d H:i:s');
        $foto    = '';
        $isJalan = ($other->tipe ?? '') === 'jalan';

        if ($isJalan && !$this->repo->hasJalanProgressHistoryTable()) {
            return $this->response->setJSON([
                'token'    => csrf_hash(),
                'success'  => false,
                'messages' => 'Tabel history progres jalan belum tersedia. Jalankan migration terlebih dahulu.',
            ]);
        }

        if ($isJalan) {
            $storedFoto = $this->storeJalanProgressPhotos();
            if ($storedFoto['success'] === false) {
                return $this->response->setJSON([
                    'token'    => csrf_hash(),
                    'success'  => false,
                    'messages' => $storedFoto['messages'],
                ]);
            }
            $foto = implode(';', $storedFoto['paths']);
            if ($foto !== '') {
                $foto .= ';';
            }
        }

        $fields = [
            'progres'             => $progres,
            'produksi_luas'       => $this->request->getPost('f_produksi_luas'),
            'produksi_keterangan' => $this->request->getPost('f_produksi_keterangan'),
            'produksi_edit_by'    => user_id(),
            'produksi_updated_at' => $now,
        ];

        $db = \Config\Database::connect();
        $db->transStart();
        $this->repo->updateOthers($id, $fields);

        if ($isJalan) {
            $this->repo->insertJalanProgressHistory([
                'id_others'       => $id,
                'progres'         => $progres,
                'produksi_luas'   => $fields['produksi_luas'],
                'keterangan'      => $fields['produksi_keterangan'],
                'foto'            => $foto,
                'add_by'          => user_id(),
                'created_at'      => $now,
            ]);
        }

        $db->transComplete();
        $success = $db->transStatus() !== false;

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Data berhasil diperbaharui' : 'Data gagal diperbaharui!',
        ]);
    }

    private function storeJalanProgressPhotos(): array
    {
        $files = $this->request->getFileMultiple('produksi_jalan_foto') ?: [];
        $paths = [];
        $allowedMime = ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp'];

        foreach ($files as $img) {
            if (!$img || $img->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (!$img->isValid()) {
                return ['success' => false, 'messages' => 'Foto kondisi jalan tidak valid', 'paths' => []];
            }

            if (!in_array($img->getMimeType(), $allowedMime, true)) {
                return ['success' => false, 'messages' => 'Foto kondisi jalan harus berupa gambar', 'paths' => []];
            }

            if ($img->getSize() > 12000 * 1024) {
                return ['success' => false, 'messages' => 'Ukuran foto kondisi jalan maksimal 12MB', 'paths' => []];
            }

            $imageSize = @getimagesize($img->getTempName());
            if ($imageSize && ($imageSize[0] > 6000 || $imageSize[1] > 6000)) {
                return ['success' => false, 'messages' => 'Dimensi foto kondisi jalan maksimal 6000x6000 px', 'paths' => []];
            }

            $paths[] = $this->fileAccessService->storeAs(
                $img,
                'uploads/produksi_jalan_progress/' . date('Ymd') . '/',
                $img->getRandomName()
            );
        }

        return ['success' => true, 'messages' => '', 'paths' => $paths];
    }
}
