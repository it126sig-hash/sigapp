<?php

namespace App\Controllers\Api;

use App\Controllers\Api\MGM\ReferralQrController as ModularReferralQrController;

/**
 * Backward compatibility shim for ReferralQrController.
 * Extends App\Controllers\Api\MGM\ReferralQrController.
 */
class ReferralQrController extends ModularReferralQrController
{
}
