<?php

namespace App\Repositories;

use App\Repositories\MGM\ReferralRepository as ModularReferralRepository;

/**
 * Backward compatibility shim for ReferralRepository.
 * Delegates all repository operations to the modular class App\Repositories\MGM\ReferralRepository.
 */
class ReferralRepository extends ModularReferralRepository
{
}
