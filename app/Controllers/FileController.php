<?php

namespace App\Controllers;

use App\Libraries\StreamFileResponse;
use App\Services\FileAccessService;
use RuntimeException;

class FileController extends BaseController
{
    protected FileAccessService $fileAccessService;

    public function __construct()
    {
        $this->fileAccessService = new FileAccessService();
    }

    public function show(string $source, int $id)
    {
        return $this->stream($source, $id, false);
    }

    public function thumbnail(string $source, int $id)
    {
        return $this->stream($source, $id, true);
    }

    public function path(string $source)
    {
        try {
            $file = $this->fileAccessService->resolvePath($source, (string) $this->request->getGet('path'));
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        return $this->streamFile($file);
    }

    private function stream(string $source, int $id, bool $thumbnail)
    {
        try {
            $file = $this->fileAccessService->resolve($source, $id, $thumbnail);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'FORBIDDEN') {
                return $this->response->setStatusCode(403)->setBody('Akses file ditolak');
            }

            return $this->response->setStatusCode(404)->setBody('File tidak ditemukan');
        }

        return $this->streamFile($file);
    }

    private function streamFile(array $file)
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

        if ($this->isNotModified($etag, $lastModifiedTime)) {
            return $this->response
                ->setStatusCode(304)
                ->setHeader('Cache-Control', 'private, max-age=86400, must-revalidate')
                ->setHeader('ETag', $etag)
                ->setHeader('Last-Modified', $lastModified);
        }

        return (new StreamFileResponse($absolutePath))
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $this->contentDisposition($disposition, $fileName))
            ->setHeader('Content-Length', (string) $fileSize)
            ->setHeader('Cache-Control', 'private, max-age=86400, must-revalidate')
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
