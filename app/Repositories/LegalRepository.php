<?php

namespace App\Repositories;

use App\Repositories\Legal\LegalRepository as ModularLegalRepository;

/**
 * @deprecated Use App\Repositories\Legal\LegalRepository instead.
 * Zero-downtime shim for legacy references.
 */
class LegalRepository extends ModularLegalRepository
{
}
