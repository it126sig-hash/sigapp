<?php

namespace App\Controllers;

use App\Controllers\Api\Mkdt\PromoController as ApiPromoController;
use App\Controllers\Web\Mkdt\PromoController as WebPromoController;
use App\Services\Mkdt\PromoService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Legacy shim. Use App\Controllers\Web\Mkdt\PromoController or App\Controllers\Api\Mkdt\PromoController.
 */
class Promo extends BaseController
{
    protected WebPromoController $webController;
    protected ApiPromoController $apiController;
    protected PromoService $promoService;

    public function __construct()
    {
        $this->promoService  = new PromoService();
        $this->webController = new WebPromoController($this->promoService);
        $this->apiController = new ApiPromoController($this->promoService);
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->webController->initController($request, $response, $logger);
        $this->apiController->initController($request, $response, $logger);
    }

    public function index()
    {
        return $this->webController->index();
    }

    public function getDataTable()
    {
        return $this->apiController->getDataTable();
    }

    public function getAll()
    {
        return $this->apiController->getAll();
    }

    public function getOne($id = null)
    {
        return $this->apiController->getOne($id);
    }

    public function add()
    {
        return $this->apiController->add();
    }

    public function edit($id = null)
    {
        return $this->apiController->edit($id);
    }

    public function remove($id = null)
    {
        return $this->apiController->remove($id);
    }

    /**
     * @deprecated Use App\Repositories\Mkdt\PromoRepository query builder filtering instead.
     */
    public function if_where($var, $column, $condition, $query)
    {
        $searchValue = (string) ($var['search']['value'] ?? '');
        $x           = 0;
        foreach ($column as $i) {
            if ($x === 0) {
                $query->like($i, $searchValue);
            } else {
                $query->orLike($i, $searchValue);
            }
            if (!empty($condition)) {
                $query->where($condition);
            }
            $x++;
        }

        return $query;
    }

    /**
     * @deprecated Use App\Services\Mkdt\PromoService::statusBadge().
     */
    public function is_active($id, $texts, $textf)
    {
        return $this->promoService->statusBadge($id, (string) $texts, (string) $textf);
    }

    /**
     * @deprecated Use App\Services\Mkdt\PromoService::formatDate().
     */
    public function format_tgl($tgl)
    {
        return $this->promoService->formatDate($tgl !== null ? (string) $tgl : null);
    }
}
