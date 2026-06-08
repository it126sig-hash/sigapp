<?php

namespace App\Services;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class FileAccessService
{
    private const PRIVATE_DIR = 'protected_uploads';

    private array $sourceRoles = [
        'file_produksi'     => [1, 7, 9],
        'gambar_kerja'      => [1, 6, 7, 9],
        'siteplan_upload'   => [1, 6, 9],
        'file_spptb'        => [1, 3, 4, 9],
        'pencairan_jaminan' => [1, 3, 9],
        'file_hargajual'    => [1, 3, 4, 9],
        'cashout_subkon'    => [1, 3, 7, 9],
        'kavling_perintah_bangun' => [1, 4, 7, 9],
        'mkdt_perintah'     => [1, 4, 7, 9],
        'mkdt_file_spptb'   => [1, 3, 4, 9],
        'mkdt_sp3k'         => [1, 3, 4, 9],
        'mkdt_bast'         => [1, 3, 4, 9],
        'mkdt_surat_batal'  => [1, 3, 4, 5, 8, 9],
        'konsumen_ktp'      => [1, 3, 4, 5, 8, 9],
        'konsumen_npwp'     => [1, 3, 4, 5, 8, 9],
        'konsumen_data'     => [1, 3, 4, 5, 8, 9],
        'si'                => [1, 4, 7, 9],
        'komplain_sales'    => [1, 7, 8, 9],
        'komplain_produksi' => [1, 7, 8, 9],
    ];

    private array $projectAssetRoles = [1, 3, 4, 5, 6, 7, 8, 9, 10];

    private $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function store(UploadedFile $file, string $logicalDir): string
    {
        $logicalDir = rtrim($this->normalizeLogicalPath($logicalDir), '/') . '/';
        $targetDir = $this->privateRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logicalDir);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName);

        return $logicalDir . $newName;
    }

    public function storeAs(UploadedFile $file, string $logicalDir, string $fileName): string
    {
        $logicalDir = rtrim($this->normalizeLogicalPath($logicalDir), '/') . '/';
        $targetDir = $this->privateRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logicalDir);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $file->move($targetDir, $fileName);

        return $logicalDir . $fileName;
    }

    public function privatePath(string $logicalPath): string
    {
        $logicalPath = $this->normalizeLogicalPath($logicalPath);
        return $this->privateRoot() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath);
    }

    public function existingPath(?string $logicalPath): ?string
    {
        try {
            return $this->findExistingPath($logicalPath);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function accessUrl(string $source, int $id, bool $download = false): string
    {
        $url = site_url('files/' . rawurlencode($source) . '/' . $id);
        return $download ? $url . '?download=1' : $url;
    }

    public function thumbnailUrl(string $source, int $id): string
    {
        return site_url('files/' . rawurlencode($source) . '/' . $id . '/thumbnail');
    }

    public function pathUrl(string $source, string $logicalPath, bool $download = false): string
    {
        $token = $this->encodePathToken($this->normalizeLogicalPath($logicalPath));
        $url = site_url('files/' . rawurlencode($source) . '/path?path=' . rawurlencode($token));
        return $download ? $url . '&download=1' : $url;
    }

    public function resolve(string $source, int $id, bool $thumbnail = false): array
    {
        $file = $this->resolveMetadata($source, $id, $thumbnail);

        return $this->resolveFile($file);
    }

    public function resolvePath(string $source, string $pathToken): array
    {
        $logicalPath = $this->decodePathToken($pathToken);
        $file = $this->fileMeta(
            $logicalPath,
            basename($logicalPath),
            $this->sourceRoles[$source] ?? [],
            (object) ['logical_path' => $logicalPath]
        );

        return $this->resolveFile($file);
    }

    private function resolveFile(array $file): array
    {
        if (!$this->canAccess($file['roles'])) {
            throw new RuntimeException('FORBIDDEN');
        }

        $path = $this->findExistingPath($file['logical_path']);
        if (!$path) {
            throw new RuntimeException('NOT_FOUND');
        }

        $file['absolute_path'] = $path;
        $file['mime_type'] = $this->detectMimeType($path);

        return $file;
    }

    public function normalizeLogicalPath(?string $path): string
    {
        $path = trim((string) $path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^https?://[^/]+/#i', '', $path);
        $path = preg_replace('#^public/#', '', $path);
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..') || preg_match('#^[A-Za-z]:#', $path)) {
            throw new RuntimeException('INVALID_PATH');
        }

        return $path;
    }

    public function migratePublicFile(string $logicalPath, bool $move = false): bool
    {
        $logicalPath = $this->normalizeLogicalPath($logicalPath);
        $source = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath);
        $target = $this->privatePath($logicalPath);

        if (!is_file($source)) {
            return false;
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        return $move ? rename($source, $target) : copy($source, $target);
    }

    public function addAccessUrlsToRows(array $rows, string $source, string $idField = 'id'): array
    {
        foreach ($rows as $row) {
            $id = is_array($row) ? ($row[$idField] ?? null) : ($row->{$idField} ?? null);
            if (!$id) {
                continue;
            }

            if (is_array($row)) {
                $row['access_url'] = $this->accessUrl($source, (int) $id);
                $row['download_url'] = $this->accessUrl($source, (int) $id, true);
                if ($source === 'file_produksi') {
                    $row['thumbnail_url'] = $this->thumbnailUrl($source, (int) $id);
                }
            } else {
                $row->access_url = $this->accessUrl($source, (int) $id);
                $row->download_url = $this->accessUrl($source, (int) $id, true);
                if ($source === 'file_produksi') {
                    $row->thumbnail_url = $this->thumbnailUrl($source, (int) $id);
                }
            }
        }

        return $rows;
    }

    public function pathUrlsFromDelimitedString(?string $paths, string $source): array
    {
        $urls = [];
        foreach (explode(';', (string) $paths) as $path) {
            $path = trim($path);
            if ($path === '') {
                continue;
            }

            try {
                $urls[] = $this->pathUrl($source, $path);
            } catch (RuntimeException) {
                continue;
            }
        }

        return $urls;
    }

    private function resolveMetadata(string $source, int $id, bool $thumbnail): array
    {
        switch ($source) {
            case 'file_upload':
                $row = $this->db->table('file_upload')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                $roles = ((int) $row->id_group === 10) ? [1, 9, 10] : [1, 5, 9];
                return $this->fileMeta($row->lokasi, $row->file_name ?: $row->default_filename, $roles, $row);

            case 'file_produksi':
                $row = $this->db->table('file_produksi')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                $path = rtrim((string) $row->lokasi, '/') . '/' . ($thumbnail ? 'thumbnails/' : '') . $row->file_name;
                return $this->fileMeta($path, $row->file_name, $this->sourceRoles[$source], $row);

            case 'gambar_kerja':
                $row = $this->db->table('gambar_kerja')->where('id_gambar_kerja', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->lokasi, $row->default_filename, $this->sourceRoles[$source], $row);

            case 'siteplan_upload':
                $row = $this->db->table('siteplan_upload')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->location, $row->file_name, $this->sourceRoles[$source], $row);

            case 'proyek_siteplan':
            case 'proyek_logo':
                $field = $source === 'proyek_siteplan' ? 'siteplan' : 'logo';
                $row = $this->db->table('proyek')->select("id_proyek, {$field}")->where('id_proyek', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->{$field}, basename((string) $row->{$field}), $this->projectAssetRoles, $row);

            case 'file_spptb':
                $row = $this->db->table('file_spptb')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->lokasi, basename((string) $row->lokasi), $this->sourceRoles[$source], $row);

            case 'pencairan_jaminan':
                $row = $this->db->table('riwayat_pencairan_jaminan')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->surat_path, basename((string) $row->surat_path), $this->sourceRoles[$source], $row);

            case 'file_hargajual':
                $row = $this->db->table('file_hargajual')->where('id_filehj', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta(rtrim((string) $row->lokasi, '/') . '/' . $row->file_name, $row->default_filename ?: $row->file_name, $this->sourceRoles[$source], $row);

            case 'cashout_subkon':
                $row = $this->db->table('cashout_subkon')->where('id_cashout_subkon', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->file_surat, basename((string) $row->file_surat), $this->sourceRoles[$source], $row);

            case 'kavling_perintah_bangun':
                $row = $this->db->table('kavling')->select('id_kavling, perintah_bangun_file')->where('id_kavling', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->perintah_bangun_file, basename((string) $row->perintah_bangun_file), $this->sourceRoles[$source], $row);

            case 'mkdt_perintah':
            case 'mkdt_file_spptb':
            case 'mkdt_sp3k':
            case 'mkdt_bast':
            case 'mkdt_surat_batal':
                $field = [
                    'mkdt_perintah'   => 'perintah_bangun_file',
                    'mkdt_file_spptb' => 'file_spptb',
                    'mkdt_sp3k'       => 'sp3k_file',
                    'mkdt_bast'       => 'bast_file',
                    'mkdt_surat_batal' => 'surat_batal',
                ][$source];
                $row = $this->db->table('mkdt')->select("id_mkdt, {$field}")->where('id_mkdt', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->{$field}, basename((string) $row->{$field}), $this->sourceRoles[$source], $row);

            case 'konsumen_ktp':
            case 'konsumen_npwp':
            case 'konsumen_data':
                $field = [
                    'konsumen_ktp'  => 'file_ktp',
                    'konsumen_npwp' => 'file_npwp',
                    'konsumen_data' => 'file_data_diri',
                ][$source];
                $row = $this->db->table('konsumen')->select("id_konsumen, {$field}")->where('id_konsumen', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->{$field}, basename((string) $row->{$field}), $this->sourceRoles[$source], $row);

            case 'si':
                $row = $this->db->table('si')->select('id, file')->where('id', $id)->get()->getRow();
                $this->assertRow($row);
                return $this->fileMeta($row->file, basename((string) $row->file), $this->sourceRoles[$source], $row);
        }

        throw new RuntimeException('NOT_FOUND');
    }

    private function fileMeta(?string $logicalPath, ?string $fileName, array $roles, object $record): array
    {
        return [
            'logical_path' => $this->normalizeLogicalPath($logicalPath),
            'file_name'    => $fileName ?: basename((string) $logicalPath),
            'roles'        => $roles,
            'record'       => $record,
        ];
    }

    private function assertRow($row): void
    {
        if (!$row) {
            throw new RuntimeException('NOT_FOUND');
        }
    }

    private function findExistingPath(string $logicalPath): ?string
    {
        $logicalPath = $this->normalizeLogicalPath($logicalPath);
        $candidates = [
            $this->privatePath($logicalPath),
            FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath),
            ROOTPATH . str_replace('/', DIRECTORY_SEPARATOR, $logicalPath),
        ];

        foreach ($candidates as $path) {
            if ($this->isInsideAllowedRoot($path) && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function canAccess(array $allowedRoles): bool
    {
        if (!function_exists('logged_in') || !logged_in()) {
            return false;
        }

        $roles = user() ? array_map('intval', array_keys(user()->getRoles())) : [];
        return count(array_intersect($roles, $allowedRoles)) > 0;
    }

    private function detectMimeType(string $path): string
    {
        $file = new File($path);
        return $file->getMimeType() ?: 'application/octet-stream';
    }

    private function privateRoot(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::PRIVATE_DIR;
    }

    private function isInsideAllowedRoot(string $path): bool
    {
        $fullPath = realpath($path) ?: $path;
        $allowedRoots = [
            realpath($this->privateRoot()) ?: $this->privateRoot(),
            realpath(FCPATH) ?: FCPATH,
            realpath(ROOTPATH . 'writable') ?: ROOTPATH . 'writable',
        ];

        foreach ($allowedRoots as $root) {
            if (str_starts_with($fullPath, rtrim($root, DIRECTORY_SEPARATOR))) {
                return true;
            }
        }

        return false;
    }

    private function encodePathToken(string $logicalPath): string
    {
        return rtrim(strtr(base64_encode($logicalPath), '+/', '-_'), '=');
    }

    private function decodePathToken(string $token): string
    {
        $decoded = base64_decode(strtr($token, '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('INVALID_PATH');
        }

        return $this->normalizeLogicalPath($decoded);
    }
}
