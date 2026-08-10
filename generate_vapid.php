<?php
require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$vapidKeys = VAPID::createVapidKeys();
echo "VAPID_PUBLIC_KEY=" . $vapidKeys['publicKey'] . PHP_EOL;
echo "VAPID_PRIVATE_KEY=" . $vapidKeys['privateKey'] . PHP_EOL;
