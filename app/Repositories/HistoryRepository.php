<?php

namespace App\Repositories;

use App\Repositories\AuditTrail\HistoryRepository as ModularHistoryRepository;

/**
 * @deprecated Use App\Repositories\AuditTrail\HistoryRepository instead.
 * Zero-downtime shim for legacy references.
 */
class HistoryRepository extends ModularHistoryRepository
{
}
