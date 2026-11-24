<?php

namespace App\Services;

use App\Services\MGM\ReferralService as ModularReferralService;

/**
 * Backward compatibility shim for ReferralService.
 * Delegates all service operations to the modular class App\Services\MGM\ReferralService.
 * Ensures zero-downtime for external callers like TransaksiService, NupService, and MgmRepairBonuses.
 */
class ReferralService extends ModularReferralService
{
}
