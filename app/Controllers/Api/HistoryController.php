<?php

namespace App\Controllers\Api;

use App\Controllers\Api\AuditTrail\HistoryController as ModularHistoryController;

/**
 * @deprecated Use App\Controllers\Api\AuditTrail\HistoryController instead.
 * Zero-downtime shim for legacy references.
 */
class HistoryController extends ModularHistoryController
{
}
