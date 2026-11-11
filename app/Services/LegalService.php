<?php

namespace App\Services;

use App\Services\Legal\LegalService as ModularLegalService;

/**
 * @deprecated Use App\Services\Legal\LegalService instead.
 * Zero-downtime shim for legacy references.
 */
class LegalService extends ModularLegalService
{
}
