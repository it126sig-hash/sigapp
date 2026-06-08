<?php

namespace App\Controllers;

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
        $download = (bool) $this->request->getGet('download');
        $disposition = $download ? 'attachment' : 'inline';
        $fileName = str_replace('"', '', (string) $file['file_name']);

        return $this->response
            ->setHeader('Content-Type', $file['mime_type'])
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $fileName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody(file_get_contents($file['absolute_path']));
    }
}
