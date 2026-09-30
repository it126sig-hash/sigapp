<?php

namespace App\Controllers\Api;

use App\Services\Bpb\BpbService;
use RuntimeException;

class BpbController extends BaseApiController
{
    private BpbService $service;

    public function __construct()
    {
        $this->service = new BpbService();
    }

    public function list()
    {
        try {
            $result = $this->service->list($this->request->getPost(), (int) user_id());
            $result['token'] = csrf_hash();
            return $this->respond($result);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function options()
    {
        return $this->success($this->service->options((int) user_id()));
    }

    public function detail(int $id)
    {
        return $this->run(fn() => $this->service->detail($id, (int) user_id()));
    }

    public function draft()
    {
        return $this->run(fn() => $this->service->saveDraft($this->payload(), $this->uploads('attachments'), (int) user_id()), 'Draft BPB disimpan.');
    }

    public function submit()
    {
        return $this->run(fn() => $this->service->submit($this->payload(), $this->uploads('attachments'), (int) user_id(), $this->request->getIPAddress(), (string) $this->request->getUserAgent()), 'BPB berhasil diajukan.');
    }

    public function update($id = null)
    {
        return $this->run(fn() => $this->service->update((int) $id, $this->payload(), $this->uploads('attachments'), (int) user_id(), $this->request->getIPAddress(), (string) $this->request->getUserAgent()), 'BPB berhasil diperbarui dan ditandatangani ulang.');
    }

    public function sign(int $id)
    {
        return $this->run(fn() => $this->service->sign($id, $this->payload(), (int) user_id(), $this->request->getIPAddress(), (string) $this->request->getUserAgent()), 'Tanda tangan berhasil disimpan.');
    }

    public function reject(int $id)
    {
        return $this->run(fn() => $this->service->reject($id, (string) ($this->payload()['reason'] ?? ''), (int) user_id()), 'BPB ditolak.');
    }

    public function cancel(int $id)
    {
        return $this->run(fn() => $this->service->cancel($id, (string) ($this->payload()['reason'] ?? ''), (int) user_id()), 'BPB dibatalkan.');
    }

    public function status(int $id)
    {
        return $this->run(fn() => $this->service->changeStatus($id, $this->payload(), $this->uploads('purchase_proofs'), (int) user_id(), $this->request->getIPAddress(), (string) $this->request->getUserAgent()), 'Status BPB diperbarui.');
    }

    private function run(callable $callback, string $message = 'Success')
    {
        try {
            return $this->success($callback(), $message);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 404 : 422);
        } catch (\Throwable $e) {
            log_message('error', 'BPB API error: {message}', ['message' => $e->getMessage()]);
            return $this->error('Terjadi kesalahan saat memproses BPB.', 500);
        }
    }

    private function payload(): array
    {
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            try {
                $data = $this->request->getJSON(true);
            } catch (\Throwable) {
                $decoded = json_decode((string) $this->request->getBody(), true);
                $data = is_array($decoded) ? $decoded : [];
            }
        } else {
            // Multipart requests are used by every UI mutation because they may include files.
            // Never invoke getJSON() for multipart bodies; CI4 otherwise raises invalidJSON.
            $data = $this->request->getPost();
        }
        $data = is_array($data) ? $data : [];
        foreach (['items', 'keep_file_ids'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (is_array($decoded)) {
                    $data[$field] = $decoded;
                }
            }
        }
        return $data;
    }

    private function uploads(string $name): array
    {
        $files = $this->request->getFileMultiple($name);
        if ($files) {
            return $files;
        }
        $file = $this->request->getFile($name);
        return $file ? [$file] : [];
    }
}
