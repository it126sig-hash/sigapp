<?php
require 'public/index.php';
\ = new App\Services\WebPushService();
\ = \->sendToSubscriptions((new App\Models\PushSubscriptionModel())->activeForUser(1), 'Test', 'Body', '/');
var_dump(\);
