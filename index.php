<?php

require 'vendor/autoload.php';

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once 'functions.php';

use Swoole\Coroutine\Http\Client;

// BPJS Credentials from .env
$userpcare = $_ENV['PCARE_USER'];
$passpcare = $_ENV['PCARE_PASS'];
$consid = $_ENV['PCARE_CONS_ID'];
$secretKey = $_ENV['PCARE_SECRET_KEY'];
$userKey = $_ENV['PCARE_USER_KEY'];

$base = $_ENV['PCARE_BASE_URL'];
$endpoint = "/dokter/0/100";

// For Swoole, we need to extract host and construct full path
$baseUrl = parse_url($base);
$fullPath = ($baseUrl['path'] ?? '') . $endpoint;

echo "Preparing BPJS request...\n";
echo "Host: " . $baseUrl['host'] . "\n";
echo "Full Path: " . $fullPath . "\n";

// Computes the timestamp
date_default_timezone_set('UTC');
$tstamp = strval(time()-strtotime('1970-01-01 00:00:00'));
// Computes the signature by hashing the salt with the secret
$signature = hash_hmac('sha256', $consid."&" . $tstamp, $secretKey, true);
// base64 encode...
$encodedSignature = base64_encode($signature);

// Headers
$headers = [
    'X-cons-id' => $consid,
    'X-timestamp' => $tstamp,
    'X-signature' => $encodedSignature,
    'user-key' => $userKey,
    'X-authorization' => 'Basic ' . base64_encode("{$userpcare}:{$passpcare}:095"),
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
];

echo "Creating Swoole HTTP client...\n";
$cli = new Client($baseUrl['host'], 443, true);
$cli->setHeaders($headers);

echo "Sending request to BPJS API...\n";
$cli->get($fullPath);

echo 'Status Code: ' . $cli->statusCode . PHP_EOL;
$rawBody = $cli->body;
echo 'Raw Response Body: ' . $rawBody . PHP_EOL;

if ($cli->statusCode != 200) {
    echo "Error: Received non-200 status code from BPJS API.\n";
    return;
}

echo "Decoding JSON response...\n";
$result = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON Decode Error: ' . json_last_error_msg() . PHP_EOL;
    return;
}

echo 'Decoded JSON (before decryption):' . PHP_EOL;
print_r($result);

if (isset($result['metaData']['code']) && $result['metaData']['code'] == 200) {
    echo "API response successful. Processing data...\n";
    $decode = $result['response'];

    $string1 = stringDecrypt($consid . '' . $secretKey . '' . $tstamp, $decode);
    echo 'ENCODED 1 (Decrypted):' . PHP_EOL;
    echo $string1 . PHP_EOL;

    $string2 = decompress($string1);
    echo 'ENCODED 2/RESULT (Decompressed):' . PHP_EOL;
    echo $string2 . PHP_EOL;

    echo 'Final Decoded JSON:' . PHP_EOL;
    print_r(json_decode($string2, true));
} else {
    echo 'API Error or Non-200 Status Code in metaData.' . PHP_EOL;
    if (isset($result['metaData']['message'])) {
        echo 'Message: ' . $result['metaData']['message'] . PHP_EOL;
    }
}

$cli->close();
echo "Script finished.\n";

?>