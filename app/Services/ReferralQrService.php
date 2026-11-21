<?php

namespace App\Services;

use App\Services\MGM\ReferralQrService as ModularReferralQrService;

/**
 * Backward compatibility shim for ReferralQrService.
 * Delegates all operations to the modular class App\Services\MGM\ReferralQrService.
 */
class ReferralQrService extends ModularReferralQrService
{
}
