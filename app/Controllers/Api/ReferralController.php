<?php

namespace App\Controllers\Api;

use App\Controllers\Api\MGM\ReferralController as ModularReferralController;

/**
 * Backward compatibility shim for ReferralController.
 * Extends App\Controllers\Api\MGM\ReferralController.
 */
class ReferralController extends ModularReferralController
{
}
