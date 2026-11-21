<?php

namespace App\Controllers\Api;

use App\Controllers\Api\MGM\ReferralSettingController as ModularReferralSettingController;

/**
 * Backward compatibility shim for ReferralSettingController.
 * Extends App\Controllers\Api\MGM\ReferralSettingController.
 */
class ReferralSettingController extends ModularReferralSettingController
{
}
