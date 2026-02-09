<?php
/**
 * Simple Benchmark Script for Swoole
 * Menggunakan Apache Bench (ab) untuk testing
 */

// Configuration
$host = 'localhost';
$port = 9501;
$totalRequests = 100;
$concurrentRequests = 10;

echo "=== SIMPLE BENCHMARK TEST (SWOOLE) ===\n";
echo "Target: http://{$host}:{$port}\n";
echo "Total Requests: {$totalRequests}\n";
echo "Concurrent Requests: {$concurrentRequests}\n\n";

// Check if ab (Apache Bench) is installed
exec('which ab', $output, $returnCode);
if ($returnCode !== 0) {
    echo "ERROR: Apache Bench (ab) is not installed.\n";
    echo "Install with: sudo apt-get install apache2-utils\n";
    exit(1);
}

echo "Starting benchmark...\n\n";

// Run Apache Bench
$command = sprintf(
    'ab -n %d -c %d -g swoole_benchmark_gnuplot.tsv http://%s:%d/ 2>&1',
    $totalRequests,
    $concurrentRequests,
    $host,
    $port
);

echo "Command: $command\n\n";
passthru($command);

echo "\n=== BENCHMARK COMPLETE ===\n";
echo "Gnuplot data saved to: swoole_benchmark_gnuplot.tsv\n";
