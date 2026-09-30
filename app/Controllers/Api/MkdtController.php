<?php

namespace App\Controllers\Api;

use App\Services\MkdtHistoryService;
use CodeIgniter\HTTP\ResponseInterface;

class MkdtController extends BaseApiController
{
    public function __construct(
        private readonly MkdtHistoryService $historyService = new MkdtHistoryService()
    ) {}

    public function history(): ResponseInterface
    {
        $rules = [
            'id_kavling' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $idKavling = (int) $this->request->getVar('id_kavling');
        $limit = (int) ($this->request->getVar('history_limit') ?? 10);
        $offset = max(0, (int) ($this->request->getVar('history_offset') ?? 0));

        $result = $this->historyService->getHistory($idKavling, $limit, $offset);
        $result['token'] = csrf_hash();

        return $this->response->setJSON($result);
    }

    public function consumerReplacementHistory(): ResponseInterface
    {
        $rules = [
            'id_mkdt' => 'required|is_natural_no_zero',
            'id_kavling' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $history = $this->historyService->getConsumerReplacementHistory(
            (int) $this->request->getVar('id_mkdt'),
            (int) $this->request->getVar('id_kavling')
        );

        return $this->response->setJSON([
            'success' => true,
            'history' => $history,
            'has_history' => count($history) > 0,
            'token' => csrf_hash(),
        ]);
    }
}
