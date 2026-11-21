<?php

namespace App\Controllers;

use App\Controllers\Web\Legal\LegalController as ModularLegalController;

/**
 * @deprecated Use App\Controllers\Web\Legal\LegalController instead.
 * Zero-downtime shim for legacy references and auto-routes.
 */
class Legal extends ModularLegalController
{
}
