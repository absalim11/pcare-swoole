<?php
/**
 * Built-in PHP Server Runner
 * Alternative untuk testing tanpa Nginx/Apache
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$host = '0.0.0.0';
$port = 8888;

echo "======================================\n";
echo "Traditional PHP Built-in Server\n";
echo "======================================\n";
echo "Server: http://{$host}:{$port}\n";
echo "Document Root: " . __DIR__ . "\n";
echo "Press Ctrl+C to stop\n";
echo "======================================\n\n";

// Change to current directory
chdir(__DIR__);

// Start built-in server
$command = sprintf(
    'php -S %s:%d -t %s',
    $host,
    $port,
    __DIR__
);

passthru($command);
