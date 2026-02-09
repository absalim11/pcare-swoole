# PANDUAN LENGKAP PENGGUNAAN SWOOLE P-CARE
## High Performance Async HTTP Request untuk BPJS API

---

## 📋 DAFTAR ISI

1. [Pengenalan](#pengenalan)
2. [Arsitektur System](#arsitektur-system)
3. [Requirements & Installation](#requirements--installation)
4. [Konfigurasi](#konfigurasi)
5. [Cara Penggunaan](#cara-penggunaan)
6. [Security Best Practices](#security-best-practices)
7. [Optimisasi & Tuning](#optimisasi--tuning)
8. [Troubleshooting](#troubleshooting)
9. [Benchmark Testing](#benchmark-testing)
10. [Production Deployment](#production-deployment)

---

## 🎯 PENGENALAN

### Apa itu Swoole P-Care?

Swoole P-Care adalah aplikasi PHP berbasis **OpenSwoole** yang dirancang untuk melakukan HTTP request ke API P-Care BPJS Kesehatan secara **asynchronous** dan **high performance**.

### Keunggulan

✅ **Async & Non-blocking**: Tidak menunggu response sebelum handle request berikutnya
✅ **High Concurrency**: Dapat handle ratusan concurrent requests
✅ **Low Latency**: Response time lebih cepat dibanding traditional PHP
✅ **Memory Efficient**: Menggunakan coroutine, bukan thread/process
✅ **Built-in HTTP Server**: Tidak perlu Nginx/Apache

### Perbandingan Performance

| Metric | Traditional PHP-FPM | Swoole (Async) |
|--------|---------------------|----------------|
| RPS (Requests/sec) | 50-100 | 200-500 |
| Concurrent Handling | 10-50 | 100-1000+ |
| Memory per Request | 5-10 MB | 1-2 MB |
| Response Time | 200-500ms | 50-200ms |

---

## 🏗️ ARSITEKTUR SYSTEM

### Flow Diagram

```
┌─────────────┐
│   Client    │
│  (Browser)  │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────────────────────┐
│   Swoole HTTP Server        │
│   (server.php)              │
│   - Port: 9501              │
│   - Non-blocking            │
│   - Coroutine-based         │
└──────┬──────────────────────┘
       │ Include & Execute
       ▼
┌─────────────────────────────┐
│   Business Logic            │
│   (index.php)               │
│   - Generate signature      │
│   - Build headers           │
│   - Make HTTP request       │
└──────┬──────────────────────┘
       │ HTTPS Request
       ▼
┌─────────────────────────────┐
│   BPJS P-Care API           │
│   (apijkn.bpjs-kesehatan)   │
│   - Encrypted response      │
│   - Compressed data         │
└──────┬──────────────────────┘
       │ Encrypted Response
       ▼
┌─────────────────────────────┐
│   Response Processing       │
│   (functions.php)           │
│   1. Decrypt (AES-256-CBC)  │
│   2. Decompress (LZ-String) │
└──────┬──────────────────────┘
       │ JSON Data
       ▼
┌─────────────────────────────┐
│   Client Response           │
│   (Plain JSON)              │
└─────────────────────────────┘
```

### Komponen Utama

1. **server.php**: Entry point, Swoole HTTP server
2. **index.php**: Business logic, API request handler
3. **functions.php**: Helper functions (decrypt, decompress)
4. **.env**: Configuration & credentials
5. **composer.json**: Dependencies management

---

## 💻 REQUIREMENTS & INSTALLATION

### System Requirements

- **OS**: Linux (Ubuntu 20.04+, Debian 10+, CentOS 7+)
- **PHP**: 8.0 atau lebih baru (recommended: 8.1+)
- **Memory**: Minimum 512MB, Recommended 2GB+
- **CPU**: Minimum 2 cores, Recommended 4+ cores

### PHP Extensions Required

```bash
# Check PHP version
php -v

# Required extensions
php -m | grep -E "json|openssl|mbstring|curl"
```

### Step 1: Install PHP 8.1

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.1-cli php8.1-dev php8.1-mbstring php8.1-curl php8.1-xml

# Verify installation
php -v
```

### Step 2: Install OpenSwoole Extension

```bash
# Install PECL if not installed
sudo apt install -y php-pear

# Install OpenSwoole
sudo pecl install openswoole

# Enable extension
echo "extension=openswoole.so" | sudo tee -a /etc/php/8.1/cli/php.ini

# Verify installation
php --ri openswoole
```

**Expected Output:**
```
openswoole

OpenSwoole => enabled
Author => OpenSwoole Group
Version => 4.x.x
```

### Step 3: Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

### Step 4: Clone & Setup Project

```bash
# Clone repository
git clone <repository-url>
cd swoole-pcare

# Install dependencies
composer install

# Setup environment
cp .env.example .env
nano .env  # Edit dengan credentials Anda
```

---

## ⚙️ KONFIGURASI

### File .env

```env
# ============================================
# SERVER CONFIGURATION
# ============================================
SERVER_PORT=9501

# ============================================
# BPJS P-CARE API CREDENTIALS
# ============================================

# Base URL
# Development: https://apijkn-dev.bpjs-kesehatan.go.id
# Production:  https://apijkn.bpjs-kesehatan.go.id
PCARE_BASE_URL="https://apijkn-dev.bpjs-kesehatan.go.id"

# Consumer ID (dari BPJS)
PCARE_CONS_ID="your_consumer_id"

# Secret Key (dari BPJS)
PCARE_SECRET_KEY="your_secret_key"

# User Key (dari BPJS)
PCARE_USER_KEY="your_user_key"

# P-Care Username
PCARE_USER="your_pcare_username"

# P-Care Password
PCARE_PASS="your_pcare_password"
```

### Mendapatkan Credentials BPJS

1. **Daftar di Portal BPJS**
   - Kunjungi: https://dvlp.bpjs-kesehatan.go.id
   - Registrasi akun developer
   - Ajukan aplikasi baru

2. **Credentials yang Diberikan**
   - `CONS_ID`: Consumer ID aplikasi Anda
   - `SECRET_KEY`: Secret key untuk signature
   - `USER_KEY`: User key untuk autentikasi
   - `USERNAME` & `PASSWORD`: Credentials P-Care

3. **Environment**
   - **Development**: Untuk testing
   - **Production**: Untuk live system

### Konfigurasi Swoole Server (Optional)

Edit `server.php` untuk tuning:

```php
$server->set([
    'worker_num' => 4,              // Jumlah worker (= CPU cores)
    'max_request' => 10000,         // Max request per worker
    'max_conn' => 10000,            // Max concurrent connections
    'task_worker_num' => 4,         // Task worker untuk async tasks
    'dispatch_mode' => 2,           // Dispatch mode (2 = fixed)
    'daemonize' => false,           // Run as daemon (true for production)
    'log_file' => '/tmp/swoole.log', // Log file location
    'log_level' => SWOOLE_LOG_INFO, // Log level
]);
```

---

## 🚀 CARA PENGGUNAAN

### 1. Start Server

```bash
# Start server (foreground)
php server.php

# Output:
# Swoole http server is started at http://0.0.0.0:9501
```

**Server akan berjalan dan mendengarkan di port 9501**

### 2. Test Request

**Terminal baru:**

```bash
# Basic test
curl http://localhost:9501

# Test dengan verbose
curl -v http://localhost:9501

# Test dengan timing
time curl http://localhost:9501

# Save response to file
curl http://localhost:9501 > response.txt
```

### 3. Expected Response

```
Preparing BPJS request...
Creating Swoole HTTP client...
Sending request to BPJS API...
Status Code: 200
Raw Response Body: {"metaData":{"code":"200","message":"OK"},"response":"...encrypted..."}
Decoding JSON response...
Decoded JSON (before decryption):
Array
(
    [metaData] => Array
        (
            [code] => 200
            [message] => OK
        )
    [response] => ...encrypted data...
)
API response successful. Processing data...
Decrypting...
Decryption complete.
ENCODED 1 (Decrypted):
...compressed data...
Decompressing...
Decompression complete.
ENCODED 2/RESULT (Decompressed):
{"list":[...dokter data...]}
Final Decoded JSON:
Array
(
    [list] => Array
        (
            [0] => Array
                (
                    [kdDokter] => 12345
                    [nmDokter] => Dr. Example
                    ...
                )
        )
)
Script finished.
```

### 4. Stop Server

```bash
# Press Ctrl+C in server terminal
^C

# Or kill process
ps aux | grep server.php
kill <PID>
```

### 5. Run as Daemon (Background)

```php
// Edit server.php, add to $server->set():
'daemonize' => true,
'log_file' => '/var/log/swoole-pcare.log',
```

```bash
# Start as daemon
php server.php

# Check if running
ps aux | grep server.php

# Stop daemon
kill $(cat /tmp/swoole.pid)
```

---

## 🔒 SECURITY BEST PRACTICES

### ⚠️ CRITICAL: Protect Credentials

**JANGAN PERNAH commit file .env ke Git!**

```bash
# 1. Add to .gitignore
echo ".env" >> .gitignore

# 2. Remove from git history (if already committed)
git rm --cached .env
git commit -m "Remove .env from repository"
git push

# 3. Verify
git ls-files | grep .env  # Should return nothing
```

### Secure .env File

```bash
# Set proper permissions
chmod 600 .env

# Only owner can read/write
ls -la .env
# Output: -rw------- 1 user user 256 Jan 01 12:00 .env
```

### Use Environment Variables (Production)

```bash
# Instead of .env file, use system environment
export PCARE_CONS_ID="your_cons_id"
export PCARE_SECRET_KEY="your_secret_key"
# ... etc

# Or use systemd environment file
sudo nano /etc/systemd/system/swoole-pcare.service
```

### Firewall Configuration

```bash
# Allow only specific IPs to access port 9501
sudo ufw allow from 192.168.1.0/24 to any port 9501

# Or use reverse proxy (Nginx)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 9501/tcp  # Block direct access
```

### HTTPS/SSL (Production)

```bash
# Use Nginx as reverse proxy with SSL
# /etc/nginx/sites-available/swoole-pcare

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        proxy_pass http://127.0.0.1:9501;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## ⚡ OPTIMISASI & TUNING

### 1. Swoole Configuration

```php
// server.php
$server->set([
    // Worker processes (set to CPU cores)
    'worker_num' => swoole_cpu_num(),

    // Max requests per worker before restart
    'max_request' => 10000,

    // Max concurrent connections
    'max_conn' => 10000,

    // Enable coroutine
    'enable_coroutine' => true,

    // Socket buffer size
    'socket_buffer_size' => 2 * 1024 * 1024, // 2MB

    // Package max length
    'package_max_length' => 4 * 1024 * 1024, // 4MB
]);
```

### 2. PHP Configuration

```bash
# Edit php.ini
sudo nano /etc/php/8.1/cli/php.ini
```

```ini
; Memory limit
memory_limit = 512M

; Max execution time
max_execution_time = 300

; OPcache (for better performance)
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
```

### 3. System Tuning

```bash
# Increase file descriptor limit
sudo nano /etc/security/limits.conf
```

```
* soft nofile 65535
* hard nofile 65535
```

```bash
# Apply immediately
ulimit -n 65535

# Verify
ulimit -n
```

### 4. Connection Pooling (Advanced)

Untuk production, implement connection pooling:

```php
// Create connection pool
$pool = new Swoole\ConnectionPool(function () {
    $client = new Swoole\Coroutine\Http\Client(
        parse_url($_ENV['PCARE_BASE_URL'], PHP_URL_HOST),
        443,
        true
    );
    return $client;
}, 10); // Pool size: 10 connections

// Use from pool
$client = $pool->get();
// ... use client ...
$pool->put($client);
```

### 5. Caching Strategy

Implement caching untuk reduce API calls:

```php
// Simple in-memory cache
$cache = new Swoole\Table(1024);
$cache->column('data', Swoole\Table::TYPE_STRING, 10240);
$cache->column('expire', Swoole\Table::TYPE_INT);
$cache->create();

// Cache key
$cacheKey = md5($endpoint . json_encode($params));

// Check cache
if ($cache->exist($cacheKey)) {
    $row = $cache->get($cacheKey);
    if ($row['expire'] > time()) {
        return json_decode($row['data'], true);
    }
}

// ... fetch from API ...

// Store in cache (TTL: 5 minutes)
$cache->set($cacheKey, [
    'data' => json_encode($result),
    'expire' => time() + 300
]);
```

---

## 🔧 TROUBLESHOOTING

### Issue 1: "Class 'Swoole\Http\Server' not found"

**Cause**: OpenSwoole extension tidak terinstall atau tidak enabled

**Solution**:
```bash
# Check if installed
php --ri openswoole

# If not found, install
sudo pecl install openswoole

# Enable extension
echo "extension=openswoole.so" | sudo tee -a /etc/php/8.1/cli/php.ini

# Restart (if using php-fpm)
sudo systemctl restart php8.1-fpm
```

### Issue 2: "Address already in use"

**Cause**: Port 9501 sudah digunakan

**Solution**:
```bash
# Check what's using the port
sudo lsof -i :9501
sudo netstat -tlnp | grep 9501

# Kill the process
kill <PID>

# Or change port in .env
SERVER_PORT=9502
```

### Issue 3: "Connection refused" saat curl

**Cause**: Server tidak berjalan atau firewall blocking

**Solution**:
```bash
# Check if server running
ps aux | grep server.php

# Check firewall
sudo ufw status
sudo ufw allow 9501/tcp

# Check if listening
netstat -tlnp | grep 9501
```

### Issue 4: "SSL certificate problem"

**Cause**: SSL verification issue dengan BPJS API

**Solution**:
```php
// In index.php, add SSL options
$cli->set([
    'ssl_verify_peer' => false,  // Disable for development only!
    'ssl_allow_self_signed' => true,
]);
```

**⚠️ WARNING**: Jangan disable SSL verification di production!

### Issue 5: Slow Response Time

**Possible Causes**:
1. BPJS API lambat (check dengan curl langsung)
2. Network latency
3. Insufficient worker processes

**Solution**:
```bash
# Test BPJS API directly
time curl -X GET "https://apijkn-dev.bpjs-kesehatan.go.id/pcare-rest/dokter/0/10" \
  -H "X-cons-id: xxx" \
  -H "X-timestamp: xxx" \
  -H "X-signature: xxx"

# Increase workers
# Edit server.php
'worker_num' => 8,  // Increase based on CPU cores
```

### Issue 6: Memory Leak

**Symptoms**: Memory usage terus naik

**Solution**:
```php
// Add max_request to restart workers periodically
$server->set([
    'max_request' => 5000,  // Restart worker after 5000 requests
]);

// Monitor memory
watch -n 1 'ps aux | grep server.php'
```

---

## 📊 BENCHMARK TESTING

Lihat file terpisah: **[BENCHMARK_GUIDE.md](BENCHMARK_GUIDE.md)**

### Quick Start Benchmark

```bash
# 1. Start server
php server.php

# 2. Run benchmark (terminal baru)
./benchmark.sh

# 3. View results
cat benchmark_results/heavy_load.txt
```

---

## 🚢 PRODUCTION DEPLOYMENT

### 1. Systemd Service

Create service file:

```bash
sudo nano /etc/systemd/system/swoole-pcare.service
```

```ini
[Unit]
Description=Swoole P-Care HTTP Server
After=network.target

[Service]
Type=forking
User=www-data
Group=www-data
WorkingDirectory=/var/www/swoole-pcare
ExecStart=/usr/bin/php /var/www/swoole-pcare/server.php
ExecReload=/bin/kill -USR1 $MAINPID
Restart=always
RestartSec=3

# Environment variables
Environment="PCARE_CONS_ID=xxx"
Environment="PCARE_SECRET_KEY=xxx"
Environment="PCARE_USER_KEY=xxx"
Environment="PCARE_USER=xxx"
Environment="PCARE_PASS=xxx"
Environment="PCARE_BASE_URL=https://apijkn.bpjs-kesehatan.go.id"
Environment="SERVER_PORT=9501"

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl daemon-reload
sudo systemctl enable swoole-pcare
sudo systemctl start swoole-pcare

# Check status
sudo systemctl status swoole-pcare

# View logs
sudo journalctl -u swoole-pcare -f
```

### 2. Nginx Reverse Proxy

```bash
sudo nano /etc/nginx/sites-available/swoole-pcare
```

```nginx
upstream swoole_backend {
    server 127.0.0.1:9501;
    keepalive 64;
}

server {
    listen 80;
    server_name api.yourdomain.com;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Logging
    access_log /var/log/nginx/swoole-pcare-access.log;
    error_log /var/log/nginx/swoole-pcare-error.log;

    location / {
        proxy_pass http://swoole_backend;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/swoole-pcare /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3. Monitoring & Logging

**Install monitoring tools:**

```bash
# Install Prometheus Node Exporter
wget https://github.com/prometheus/node_exporter/releases/download/v1.5.0/node_exporter-1.5.0.linux-amd64.tar.gz
tar xvfz node_exporter-1.5.0.linux-amd64.tar.gz
sudo mv node_exporter-1.5.0.linux-amd64/node_exporter /usr/local/bin/
```

**Custom logging:**

```php
// Add to server.php
$server->set([
    'log_file' => '/var/log/swoole-pcare/server.log',
    'log_level' => SWOOLE_LOG_INFO,
    'log_rotation' => SWOOLE_LOG_ROTATION_DAILY,
]);
```

### 4. Backup & Recovery

```bash
# Backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/swoole-pcare"

# Backup code
tar -czf $BACKUP_DIR/code_$DATE.tar.gz /var/www/swoole-pcare

# Backup .env (encrypted)
gpg --encrypt --recipient admin@yourdomain.com \
    /var/www/swoole-pcare/.env > $BACKUP_DIR/env_$DATE.gpg

# Keep only last 7 days
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### 5. Health Check Endpoint

Add health check to `server.php`:

```php
$server->on("request", function (Request $request, Response $response) {
    // Health check endpoint
    if ($request->server['request_uri'] === '/health') {
        $response->header("Content-Type", "application/json");
        $response->end(json_encode([
            'status' => 'healthy',
            'timestamp' => time(),
            'version' => '1.0.0'
        ]));
        return;
    }

    // Normal request handling
    go(function () use ($request, $response) {
        ob_start();
        include 'index.php';
        $content = ob_get_clean();
        $response->header("Content-Type", "text/plain");
        $response->end($content);
    });
});
```

Test:
```bash
curl http://localhost:9501/health
# {"status":"healthy","timestamp":1234567890,"version":"1.0.0"}
```

---

## 📚 REFERENSI

- [OpenSwoole Documentation](https://openswoole.com/docs)
- [BPJS API Documentation](https://dvlp.bpjs-kesehatan.go.id/docs)
- [PHP Best Practices](https://www.php-fig.org/psr/)
- [Nginx Configuration](https://nginx.org/en/docs/)

---

## 📝 CHANGELOG

### Version 1.0.0 (Current)
- Initial release
- Basic GET request support
- Async HTTP client with Swoole
- AES-256-CBC decryption
- LZ-String decompression

### Planned Features
- [ ] POST/PUT/DELETE support
- [ ] Connection pooling
- [ ] Response caching
- [ ] Rate limiting
- [ ] Circuit breaker pattern
- [ ] Metrics & monitoring
- [ ] Admin dashboard

---

## 🤝 KONTRIBUSI

Jika menemukan bug atau ingin menambahkan fitur:

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

---

## 📄 LICENSE

[Specify your license here]

---

## 👨‍💻 AUTHOR

**abworks**
Email: abysalim007@gmail.com

---

**Last Updated**: 2026
