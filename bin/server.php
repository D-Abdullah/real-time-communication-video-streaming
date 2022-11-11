<?php

use Core\Classes\Chat;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once '../core/classes/DB.php';
require_once '../core/classes/User.php';
require_once '../core/classes/chat.php';

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8000
);

$server->run();
