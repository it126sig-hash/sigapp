<?php

namespace App\Controllers\Web\Siteplan;

use App\Controllers\BaseController;
use App\Exceptions\ServiceException;
use App\Services\Siteplan\SiteplanService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class SiteplanController extends BaseController
{
    protected SiteplanService $siteplanService;

    public function __construct(?SiteplanService $siteplanService = null)
    {
        $this->siteplanService = $siteplanService ?? new SiteplanService();
    }

    public function index(): string|RedirectResponse
    {
        $userId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;
        $state  = $this->siteplanService->getIndexState($userId);

        if (! empty($state['redirect_to_view'])) {
            return redirect()->to(base_url('siteplan/view'));
        }

        return view('template', [
            'content' => 'siteplan/pilih_proyek',
            'data'    => [
                'proyek' => $state['proyek'] ?? [],
            ],
        ]);
    }

    public function view_siteplan(?int $idProyek = null): string|RedirectResponse
    {
        $requestedIdProyek = ($idProyek !== null && $idProyek > 0)
            ? $idProyek
            : (int) ($this->request->getGet('id_proyek') ?? 0);

        $userId = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;

        try {
            $viewData = $this->siteplanService->getViewSiteplanData(
                $requestedIdProyek > 0 ? $requestedIdProyek : null,
                $userId
            );
        } catch (ServiceException $e) {
            return $this->handleServiceException($e);
        }

        return view('template', [
            'content' => 'siteplan/master',
            'data'    => $viewData,
        ]);
    }

    public function produksi_mobile(): string|RedirectResponse
    {
        $requestedIdProyek = (int) ($this->request->getGet('id_proyek') ?? 0);
        $initialIdKavling  = (int) ($this->request->getGet('id_kavling') ?? 0);
        $userId            = function_exists('user_id') ? (int) (user_id() ?? 0) : 0;

        try {
            $viewData = $this->siteplanService->getProduksiMobileData(
                $requestedIdProyek > 0 ? $requestedIdProyek : null,
                $userId,
                $initialIdKavling
            );
        } catch (ServiceException $e) {
            return $this->handleServiceException($e);
        }

        return view('template', [
            'content' => 'siteplan/produksi_mobile',
            'data'    => $viewData,
        ]);
    }

    private function handleServiceException(ServiceException $e): RedirectResponse
    {
        $errors = $e->getErrors();
        if (! empty($errors['redirect'])) {
            return redirect()->to(base_url((string) $errors['redirect']))->with('error', $e->getMessage());
        }

        if ($e->getStatusCode() === 404) {
            throw PageNotFoundException::forPageNotFound($e->getMessage());
        }

        return redirect()->to(base_url('siteplan'))->with('error', $e->getMessage());
    }
}
