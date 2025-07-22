<?php

namespace App\Controllers;

use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use App\Controllers\Pusher;

class WebSocket extends BaseController {
    public function index() {
        $pusher = new Pusher();
        $wsServer = new WsServer($pusher);
        $httpServer = new HttpServer($wsServer);

        $server = IoServer::factory($httpServer, 8080);
        $server->run();
    }
}