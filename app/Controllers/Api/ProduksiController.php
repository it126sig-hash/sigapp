<?php

namespace App\Controllers\Api;

use App\Models\GambarkerjaModel;
use App\Models\ChecklistWorkModel;
use App\Models\KavlingModel;
use App\Models\KomplainModel;
use App\Libraries\Mpdf_lib;
use App\Services\FileAccessService;
use App\Services\ProduksiService;
use App\Services\ProduksiFileService;
use App\Repositories\ProduksiRepository;
use CodeIgniter\HTTP\ResponseInterface;

class ProduksiController extends BaseApiController
{
    protected ProduksiRepository  $repo;
    protected ProduksiService     $produksiService;
    protected ProduksiFileService $fileService;
    protected FileAccessService   $fileAccessService;
    protected GambarkerjaModel    $gambarkerjaModel;
    protected KavlingModel        $kavlingModel;
    protected ChecklistWorkModel  $cwModel;
    protected KomplainModel       $komplainModel;
    protected Mpdf_lib            $mpdf;
    protected $validation;

    public function __construct()
    {
        $this->repo              = new ProduksiRepository();
        $this->produksiService   = new ProduksiService();
        $this->fileService       = new ProduksiFileService();
        $this->fileAccessService = new FileAccessService();
        $this->gambarkerjaModel  = new GambarkerjaModel();
        $this->kavlingModel      = new KavlingModel();
        $this->cwModel           = new ChecklistWorkModel();
        $this->komplainModel     = new KomplainModel();
        $this->mpdf              = new Mpdf_lib();
        $this->validation        = \Config\Services::validation();
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

    public function getBayarProduksi(): ResponseInterface
    {
        $idKavling = (int) $this->request->getVar('id_kavling');

        return $this->response->setJSON([
            'token'               => csrf_hash(),
            'id_kavling'          => $idKavling,
            'list_bayar_produksi' => $this->repo->getBayarList($idKavling),
        ]);
    }

    public function saveBayarProduksi(): ResponseInterface
    {
        $items     = $this->request->getVar('id-bayar_produksi');
        $idKavling = $this->request->getVar('id_kavling');

        $this->repo->upsertBayarItems($idKavling, $items, user_id());

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => true,
            'messages' => 'Data berhasil diperbaharui',
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

    public function edit_others(): ResponseInterface
    {
        $id     = (int) $this->request->getPost('id_kavling');
        $fields = [
            'progres'             => $this->request->getPost('f_progres_jalan'),
            'produksi_luas'       => $this->request->getPost('f_produksi_luas'),
            'produksi_keterangan' => $this->request->getPost('f_produksi_keterangan'),
            'produksi_edit_by'    => user_id(),
            'produksi_updated_at' => date('Y-m-d H:i:s'),
        ];

        $success = $this->repo->updateOthers($id, $fields);

        return $this->response->setJSON([
            'token'    => csrf_hash(),
            'success'  => $success,
            'messages' => $success ? 'Data berhasil diperbaharui' : 'Data gagal diperbaharui!',
        ]);
    }
}
