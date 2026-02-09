# PANDUAN BENCHMARK TESTING
# Swoole P-Care High Performance HTTP Request

## Persiapan Testing

### 1. Install Tools yang Diperlukan

```bash
# Install Apache Bench (untuk load testing)
sudo apt-get install apache2-utils

# Verify installation
ab -V
```

### 2. Pastikan Server Berjalan

```bash
# Terminal 1: Start server
php server.php

# Terminal 2: Verify server is running
curl http://localhost:9501
```

---

## Metode Testing

### A. Testing dengan Bash Script (RECOMMENDED)

Script ini akan menjalankan 5 skenario testing secara otomatis.

```bash
./benchmark.sh
```

**Skenario Testing:**
1. Single Request Test (connectivity check)
2. Light Load: 100 requests, 10 concurrent
3. Medium Load: 500 requests, 50 concurrent
4. Heavy Load: 1000 requests, 100 concurrent
5. Stress Test: 2000 requests, 200 concurrent

**Output:**
- Hasil disimpan di folder `benchmark_results/`
- Summary ditampilkan di terminal
- File `.tsv` untuk visualisasi dengan gnuplot

---

### B. Testing dengan PHP Script (Swoole Native)

Script PHP murni menggunakan Swoole Coroutine untuk testing.

```bash
php benchmark.php
```

**Fitur:**
- Warmup phase (10 requests)
- Concurrent request handling dengan coroutine
- Detailed statistics:
  - Request per second (RPS)
  - Response time (min, max, avg, median, p95, p99)
  - Success/failure rate
  - Memory usage
- Auto-save hasil ke JSON file

**Konfigurasi di `benchmark.php`:**
```php
$config = [
    'host' => 'localhost',
    'port' => 9501,
    'total_requests' => 1000,
    'concurrent_requests' => 100,
    'warmup_requests' => 10,
];
```

---

### C. Testing dengan Apache Bench (Manual)

```bash
# Basic test: 100 requests, 10 concurrent
ab -n 100 -c 10 http://localhost:9501/

# Advanced test dengan output file
ab -n 100 -c 10 -g results.tsv http://localhost:9501/

# Test dengan keepalive
ab -n 100 -c 10 -k http://localhost:9501/
```

**Parameter Penting:**
- `-n`: Total number of requests
- `-c`: Concurrent requests
- `-k`: Enable HTTP KeepAlive
- `-g`: Output results untuk gnuplot
- `-t`: Time limit (seconds)

---

## Metrics yang Diukur

### 1. Throughput Metrics
- **Requests per second (RPS)**: Jumlah request yang bisa dihandle per detik
- **Transfer rate**: Kecepatan transfer data (KB/s)

### 2. Latency Metrics
- **Average response time**: Rata-rata waktu response
- **Median response time**: Waktu response tengah
- **Min/Max response time**: Waktu tercepat/terlambat
- **95th percentile (p95)**: 95% request lebih cepat dari nilai ini
- **99th percentile (p99)**: 99% request lebih cepat dari nilai ini

### 3. Reliability Metrics
- **Success rate**: Persentase request sukses
- **Failed requests**: Jumlah request gagal
- **Error rate**: Persentase error

### 4. Resource Metrics
- **Memory usage**: Penggunaan memory peak
- **CPU usage**: Dapat dimonitor dengan `top` atau `htop`

---

## Target Performance (Expected)

Berdasarkan implementasi Swoole async:

### Scenario 1: Light Load (10 concurrent)
```
Expected:
- RPS: 50-100 req/s
- Avg Response Time: 100-200 ms
- Success Rate: 100%
```

### Scenario 2: Medium Load (50 concurrent)
```
Expected:
- RPS: 100-200 req/s
- Avg Response Time: 200-300 ms
- Success Rate: 99-100%
```

### Scenario 3: Heavy Load (100 concurrent)
```
Expected:
- RPS: 150-300 req/s
- Avg Response Time: 300-500 ms
- Success Rate: 95-100%
```

### Scenario 4: Stress Test (200 concurrent)
```
Expected:
- RPS: 200-400 req/s
- Avg Response Time: 500-1000 ms
- Success Rate: 90-100%
- Some timeouts may occur
```

**NOTE:** Actual performance tergantung pada:
- Response time dari BPJS API
- Network latency
- Hardware specifications
- PHP configuration

---

## Comparison Testing (Swoole vs Traditional PHP)

Untuk membandingkan performa Swoole dengan traditional PHP-FPM:

### 1. Setup Traditional PHP-FPM

```bash
# Install nginx + php-fpm
sudo apt-get install nginx php-fpm

# Configure nginx untuk serve index.php
# Edit /etc/nginx/sites-available/default
```

### 2. Run Comparison Test

```bash
# Test Swoole (async)
ab -n 100 -c 10 http://localhost:9501/ > swoole_result.txt

# Test PHP-FPM (traditional)
ab -n 100 -c 10 http://localhost/index.php > phpfpm_result.txt

# Compare results
diff swoole_result.txt phpfpm_result.txt
```

**Expected Difference:**
- Swoole: 2-5x faster throughput
- Swoole: 50-70% lower response time
- Swoole: Better handling concurrent requests
- Swoole: Lower memory per request

---

## Monitoring During Test

### Terminal 1: Server logs
```bash
php server.php
```

### Terminal 2: System resources
```bash
# Monitor real-time
htop

# Or with watch
watch -n 1 'ps aux | grep php'
```

### Terminal 3: Network monitoring
```bash
# Monitor connections
watch -n 1 'netstat -an | grep 9501 | wc -l'

# Or with ss
watch -n 1 'ss -tan | grep 9501'
```

---

## Troubleshooting

### Issue: Too many open files
```bash
# Increase file descriptor limit
ulimit -n 65535

# Or permanent (add to /etc/security/limits.conf)
* soft nofile 65535
* hard nofile 65535
```

### Issue: Connection refused
```bash
# Check if server is running
ps aux | grep server.php

# Check port
netstat -tlnp | grep 9501

# Check firewall
sudo ufw status
```

### Issue: Slow performance
```bash
# Check Swoole configuration in server.php
# Increase worker_num if needed
$server->set([
    'worker_num' => 4,  // Set to CPU cores
    'max_request' => 10000,
]);
```

---

## Advanced Testing

### 1. Test dengan wrk (Modern HTTP Benchmark Tool)

```bash
# Install wrk
sudo apt-get install wrk

# Basic test
wrk -t4 -c100 -d30s http://localhost:9501

# With Lua script for complex scenarios
wrk -t4 -c100 -d30s -s script.lua http://localhost:9501
```

### 2. Test dengan Siege

```bash
# Install siege
sudo apt-get install siege

# Run test
siege -c 100 -r 10 http://localhost:9501
```

### 3. Test dengan hey (Go-based)

```bash
# Install hey
go install github.com/rakyll/hey@latest

# Run test
hey -n 1000 -c 100 http://localhost:9501
```

---

## Generating Reports

### 1. Gnuplot Visualization

```bash
# Generate graph from .tsv data
gnuplot << EOF
set terminal png size 1024,768
set output "benchmark_graph.png"
set title "Response Time Distribution"
set xlabel "Request Number"
set ylabel "Response Time (ms)"
plot "benchmark_results/heavy_load.tsv" using 9 with lines title "Response Time"
EOF
```

### 2. JSON Report Analysis

```bash
# After running benchmark.php
php -r "
\$data = json_decode(file_get_contents('benchmark_results_*.json'), true);
echo 'RPS: ' . \$data['results']['requests_per_second'] . PHP_EOL;
echo 'Avg Time: ' . \$data['results']['avg_response_time'] . ' ms' . PHP_EOL;
"
```

---

## Best Practices

1. **Always run warmup** before actual testing
2. **Test gradually** (start with low load, increase gradually)
3. **Monitor server resources** during testing
4. **Run multiple iterations** and average the results
5. **Test during different times** (peak hours vs off-hours)
6. **Document environment** (hardware, network, etc.)
7. **Keep consistent conditions** between tests
8. **Save all results** for historical comparison

---

## Sample Benchmark Report Template

```
=== SWOOLE P-CARE BENCHMARK REPORT ===
Date: 2024-XX-XX
Environment: Development/Production
Server: PHP 8.1 + OpenSwoole 4.x
Hardware: 4 CPU cores, 8GB RAM

Test Results:
--------------
Total Requests: 1000
Concurrent: 100
Duration: 10.5 seconds

Performance:
- RPS: 95.2 req/s
- Avg Response Time: 1050 ms
- Median Response Time: 980 ms
- 95th Percentile: 1500 ms
- Min/Max: 850ms / 2100ms

Success Rate: 98.5% (985/1000)
Failed Requests: 15
Memory Peak: 45.2 MB

Conclusion:
- System can handle 100 concurrent requests
- Average BPJS API response time: ~1 second
- No connection errors under load
- Ready for production deployment
```

---

## Next Steps After Benchmark

1. **Analyze bottlenecks** dari hasil testing
2. **Tune Swoole configuration** untuk optimal performance
3. **Implement connection pooling** untuk database (jika ada)
4. **Add caching layer** untuk response BPJS
5. **Setup monitoring** (Prometheus, Grafana)
6. **Implement rate limiting** untuk production
7. **Add circuit breaker** untuk BPJS API failures

---
