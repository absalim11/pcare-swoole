<?php
/**
 * Traditional PHP Implementation (Non-Swoole)
 * BPJS P-Care API Client
 *
 * This is a traditional synchronous PHP implementation for comparison
 * with the Swoole async version.
 */

require 'vendor/autoload.php';

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once 'functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// BPJS Credentials from .env
$userpcare = $_ENV['PCARE_USER'];
$passpcare = $_ENV['PCARE_PASS'];
$consid = $_ENV['PCARE_CONS_ID'];
$secretKey = $_ENV['PCARE_SECRET_KEY'];
$userKey = $_ENV['PCARE_USER_KEY'];

$base = $_ENV['PCARE_BASE_URL'];
$endpoint = $_GET['endpoint'] ?? "/dokter/0/100";

// Start timing
$startTime = microtime(true);

try {
    // Computes the timestamp
    date_default_timezone_set('UTC');
    $tstamp = strval(time()-strtotime('1970-01-01 00:00:00'));

    // Computes the signature by hashing the salt with the secret
    $signature = hash_hmac('sha256', $consid . "&" . $tstamp, $secretKey, true);

    // base64 encode...
    $encodedSignature = base64_encode($signature);

    // Headers
    $headers = [
        'X-cons-id: ' . $consid,
        'X-timestamp: ' . $tstamp,
        'X-signature: ' . $encodedSignature,
        'user-key: ' . $userKey,
        'X-authorization: Basic ' . base64_encode("{$userpcare}:{$passpcare}:095"),
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    // Initialize cURL
    $ch = curl_init();

    $fullUrl = $base . $endpoint;

    // Debug output
    error_log("Full URL: " . $fullUrl);

    curl_setopt_array($ch, [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    // Execute request
    $rawBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    // Debug: log response
    error_log("HTTP Code: " . $httpCode);
    error_log("Response: " . $rawBody);

    curl_close($ch);

    if ($rawBody === false) {
        throw new Exception("cURL Error: " . $curlError);
    }

    if ($httpCode != 200) {
        throw new Exception("HTTP Error: " . $httpCode);
    }

    // Decode JSON response
    $result = json_decode($rawBody, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }

    if (!isset($result['metaData']['code']) || $result['metaData']['code'] != 200) {
        throw new Exception($result['metaData']['message'] ?? 'API Error');
    }

    // Decrypt and decompress
    $decode = $result['response'];
    $string1 = stringDecrypt($consid . '' . $secretKey . '' . $tstamp, $decode);
    $string2 = decompress($string1);

    $finalData = json_decode($string2, true);

    // Calculate execution time
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to ms

    // Success response
    echo json_encode([
        'success' => true,
        'data' => $finalData,
        'meta' => [
            'execution_time_ms' => round($executionTime, 2),
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'method' => 'traditional_php_curl'
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Calculate execution time even on error
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000;

    // Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'meta' => [
            'execution_time_ms' => round($executionTime, 2),
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint ?? 'unknown',
            'method' => 'traditional_php_curl'
        ]
    ], JSON_PRETTY_PRINT);
}
