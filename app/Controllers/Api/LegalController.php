<?php

namespace App\Controllers\Api;

use App\Controllers\Api\Legal\LegalController as ModularLegalController;

/**
 * @deprecated Use App\Controllers\Api\Legal\LegalController instead.
 * Zero-downtime shim for legacy references.
 */
class LegalController extends ModularLegalController
{
}
