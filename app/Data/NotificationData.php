<?php

namespace App\Data;

class NotificationData
{
    public function __construct(
        public readonly string $message,
        public readonly int $actorUserId,
        public readonly mixed $idKavling = null,
        public readonly mixed $idKonsumen = null,
        public readonly ?string $type = null,
        public readonly ?int $idProyek = null,
        public readonly ?string $actionUrl = null,
        public readonly ?string $eventType = null
    ) {
    }
}
