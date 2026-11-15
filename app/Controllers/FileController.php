<?php

namespace App\Controllers;

use App\Libraries\StreamFileResponse;
use App\Services\FileAccessService;
use App\Services\ImageThumbnailService;
use App\Services\PhotoGpsMetadataService;
use CodeIgniter\Files\File;
use RuntimeException;

class FileController extends BaseController
{
    protected FileAccessService $fileAccessService;
    protected ImageThumbnailService $thumbnailService;
    protected PhotoGpsMetadataService $photoGpsMetadataService;

    public function __construct()
    {
        $this->fileAccessService = new FileAccessService();
        $this->thumbnailService = new ImageThumbnailService();
        $this->photoGpsMetadataService = new PhotoGpsMetadataService();
    }

    public function show(string $source, int $id)
    {
        $signedUserId = $this->resolveSignedUserId($source, $id, null);
        return $this->stream($source, $id, false, $signedUserId);
    }

    public function thumbnail(string $source, int $id)
    {
        try {
            $signedUserId = $this->resolveSignedUserId($source, $id, 'thumb');
            try {
                $file = $this->fileAccessService->resolve($source, $id, true, $signedUserId);
            } catch (RuntimeException $e) {
                if ($e->getMessage() === 'FORBIDDEN') {
                    throw $e;
                }
                $file = $this->fileAccessService->resolve($source, $id, false, $signedUserId);
            }
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        return $this->streamThumbnail($file);
    }

    public function path(string $source)
    {
        try {
            $pathToken = (string) $this->request->getGet('path');
            $signedUserId = $this->resolveSignedUserId($source, $pathToken, null);
            $file = $this->fileAccessService->resolvePath($source, $pathToken, $signedUserId);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        return $this->streamFile($file);
    }

    private function shouldEmbedGps(string $source): bool
    {
        return $source === 'file_produksi'
            && (string) $this->request->getGet('download') === '1'
            && (string) $this->request->getGet('gps') === '1';
    }

    public function pathThumbnail(string $source)
    {
        try {
            $pathToken = (string) $this->request->getGet('path');
            $signedUserId = $this->resolveSignedUserId($source, $pathToken, 'thumb');
            $file = $this->fileAccessService->resolvePath($source, $pathToken, $signedUserId);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        return $this->streamThumbnail($file);
    }

    private function resolveSignedUserId(string $source, string|int $identifier, ?string $variant = null): ?int
    {
        $expires = (int) $this->request->getGet('expires');
        $uid = (int) $this->request->getGet('uid');
        $sig = trim((string) $this->request->getGet('sig'));

        if ($expires > 0 || $uid > 0 || $sig !== '') {
            if ($expires <= 0 || $uid <= 0 || $sig === '') {
                throw new RuntimeException('FORBIDDEN');
            }
            if ($this->fileAccessService->verifySignature($source, $identifier, $variant, $expires, $uid, $sig)) {
                return $uid;
            }
            throw new RuntimeException('FORBIDDEN');
        }

        return null;
    }

    private function stream(string $source, int $id, bool $thumbnail, ?int $signedUserId = null)
    {
        try {
            $file = $this->fileAccessService->resolve($source, $id, $thumbnail, $signedUserId);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        if ($this->shouldEmbedGps($source)) {
            try {
                $prepared = $this->photoGpsMetadataService->prepareDownload($file);
                if ($prepared !== null) {
                    $file = $prepared;
                    $temporaryPath = (string) ($file['temporary_path'] ?? '');
                    register_shutdown_function(function () use ($temporaryPath) {
                        $this->photoGpsMetadataService->cleanup($temporaryPath);
                    });
                }
            } catch (\Throwable $e) {
                log_message('error', 'Gagal menyiapkan metadata GPS foto produksi: ' . $e->getMessage());
            }
        }

        $isVersionedSiteplan = !$thumbnail
            && $source === 'proyek_siteplan'
            && trim((string) $this->request->getGet('v')) !== '';

        return $this->streamFile($file, $isVersionedSiteplan);
    }

    private function streamThumbnail(array $file)
    {
        $absolutePath = (string) ($file['absolute_path'] ?? '');
        $mimeType = (string) ($file['mime_type'] ?? '');

        if ($absolutePath !== '' && is_file($absolutePath) && str_starts_with($mimeType, 'image/')) {
            $w = (int) $this->request->getGet('w');
            $h = (int) $this->request->getGet('h');
            $width = $w > 0 ? max(16, min(1000, $w)) : 100;
            $height = $h > 0 ? max(16, min(1000, $h)) : $width;

            $thumbPath = $this->thumbnailService->getThumbnail($absolutePath, $width, $height, 'center');
            if ($thumbPath && is_file($thumbPath)) {
                $file['absolute_path'] = $thumbPath;
                $file['mime_type'] = (new File($thumbPath))->getMimeType() ?: $mimeType;
            }
        }

        return $this->streamFile($file);
    }

    private function streamFile(array $file, bool $immutableVersioned = false)
    {
        $absolutePath = (string) ($file['absolute_path'] ?? '');
        if ($absolutePath === '' || !is_file($absolutePath) || !is_readable($absolutePath)) {
            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        $download = (bool) $this->request->getGet('download');
        $disposition = $download ? 'attachment' : 'inline';
        $fileName = $this->sanitizeFileName((string) ($file['file_name'] ?? basename($absolutePath)));
        $mimeType = (string) ($file['mime_type'] ?? 'application/octet-stream');
        $fileSize = (int) (filesize($absolutePath) ?: 0);
        $lastModifiedTime = (int) (filemtime($absolutePath) ?: time());
        $lastModified = gmdate('D, d M Y H:i:s', $lastModifiedTime) . ' GMT';
        $etag = '"' . sha1($absolutePath . '|' . $fileSize . '|' . $lastModifiedTime) . '"';

        // Berkas ber-hash ?v= pada proyek_siteplan bersifat immutable selama 30 hari.
        $maxAge = $immutableVersioned ? 2592000 : 86400;
        $cacheControl = $immutableVersioned
            ? "private, max-age={$maxAge}, immutable"
            : "private, max-age={$maxAge}, must-revalidate";
        $expiresHeader = gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT';

        if (function_exists('header_remove')) {
            @header_remove('Cache-Control');
            @header_remove('Expires');
            @header_remove('Pragma');
        }

        if ($this->isNotModified($etag, $lastModifiedTime)) {
            return $this->response
                ->setStatusCode(304)
                ->removeHeader('Cache-Control')
                ->removeHeader('Expires')
                ->removeHeader('Pragma')
                ->setHeader('Cache-Control', $cacheControl)
                ->setHeader('Expires', $expiresHeader)
                ->setHeader('Pragma', 'private')
                ->setHeader('ETag', $etag)
                ->setHeader('Last-Modified', $lastModified);
        }

        return (new StreamFileResponse($absolutePath))
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $this->contentDisposition($disposition, $fileName))
            ->setHeader('Content-Length', (string) $fileSize)
            ->removeHeader('Cache-Control')
            ->removeHeader('Expires')
            ->removeHeader('Pragma')
            ->setHeader('Cache-Control', $cacheControl)
            ->setHeader('Expires', $expiresHeader)
            ->setHeader('Pragma', 'private')
            ->setHeader('ETag', $etag)
            ->setHeader('Last-Modified', $lastModified)
            ->setHeader('X-Content-Type-Options', 'nosniff');
    }

    private function isNotModified(string $etag, int $lastModifiedTime): bool
    {
        $ifNoneMatch = trim($this->request->getHeaderLine('If-None-Match'));
        if ($ifNoneMatch !== '') {
            $clientEtags = array_map('trim', explode(',', $ifNoneMatch));
            if (in_array($etag, $clientEtags, true) || in_array('*', $clientEtags, true)) {
                return true;
            }
        }

        $ifModifiedSince = trim($this->request->getHeaderLine('If-Modified-Since'));
        if ($ifModifiedSince === '') {
            return false;
        }

        $clientTime = strtotime($ifModifiedSince);

        return $clientTime !== false && $clientTime >= $lastModifiedTime;
    }

    private function contentDisposition(string $disposition, string $fileName): string
    {
        $asciiName = str_replace(['"', '\\'], '', $fileName);

        return $disposition . '; filename="' . $asciiName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName);
    }

    private function sanitizeFileName(string $fileName): string
    {
        $fileName = str_replace(["\r", "\n"], '', $fileName);

        return $fileName !== '' ? $fileName : 'file';
    }
}
