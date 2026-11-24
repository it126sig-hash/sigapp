<?php

namespace App\Services;

use App\Services\AuditTrail\HistoryService as ModularHistoryService;

/**
 * @deprecated Use App\Services\AuditTrail\HistoryService instead.
 * Zero-downtime shim for legacy references.
 */
class HistoryService extends ModularHistoryService
{
}
