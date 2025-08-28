<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require 'vendor/autoload.php';

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$server = new Server("0.0.0.0", $_ENV['SERVER_PORT'] ?? 9501);

$server->on("start", function (Server $server) {
    echo "Swoole http server is started at http://{$server->host}:{$server->port}\n";
});


$server->on("request", function (Request $request, Response $response) {
    echo "Request received in server.php\n";
    
    // Run the index.php logic in a new coroutine
    go(function () use ($request, $response) {
        ob_start();
        include 'index.php';
        $content = ob_get_clean();
        $response->header("Content-Type", "text/plain");
        $response->end($content);
        echo "Response sent from server.php\n";
    });
});

$server->start();
