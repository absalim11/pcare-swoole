# TRADITIONAL PHP IMPLEMENTATION (NON-SWOOLE)
## Perbandingan dengan Swoole Async

---

## 📋 DAFTAR ISI

1. [Pengenalan](#pengenalan)
2. [Perbedaan Arsitektur](#perbedaan-arsitektur)
3. [Setup & Installation](#setup--installation)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Benchmark Comparison](#benchmark-comparison)
6. [Kelebihan & Kekurangan](#kelebihan--kekurangan)

---

## 🎯 PENGENALAN

Direktori `non-swoole/` berisi implementasi **Traditional PHP** untuk BPJS P-Care API sebagai **pembanding** dengan versi Swoole async.

### Tujuan

- Membandingkan performance Swoole vs Traditional PHP
- Memahami perbedaan arsitektur async vs synchronous
- Memberikan opsi deployment untuk environment yang tidak support Swoole

### Apa yang Berbeda?

| Aspect | Swoole | Traditional PHP |
|--------|--------|-----------------|
| Execution | Async/Non-blocking | Synchronous/Blocking |
| Server | Built-in HTTP Server | Nginx/Apache + PHP-FPM |
| Concurrency | Coroutine-based | Process/Thread-based |
| Memory | Persistent | Per-request |
| Startup | Manual (php server.php) | Auto (web server) |

---

## 🏗️ PERBEDAAN ARSITEKTUR

### Traditional PHP Flow

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────────────────────┐
│   Web Server (Nginx/Apache) │
│   - Port: 80/8888           │
│   - Handles static files    │
└──────┬──────────────────────┘
       │ FastCGI Protocol
       ▼
┌─────────────────────────────┐
│   PHP-FPM Worker Pool       │
│   - Process per request     │
│   - Synchronous execution   │
│   - Memory allocated        │
└──────┬──────────────────────┘
       │ Execute PHP
       ▼
┌─────────────────────────────┐
│   index.php                 │
│   - Load libraries          │
│   - Process request         │
│   - cURL to BPJS API        │
│   - BLOCKING until response │
└──────┬──────────────────────┘
       │ Response
       ▼
┌─────────────────────────────┐
│   Client Response           │
│   - Worker freed            │
│   - Memory released         │
└─────────────────────────────┘
```

### Swoole Flow (Async)

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────────────────────┐
│   Swoole HTTP Server        │
│   - Built-in server         │
│   - Persistent workers      │
│   - Coroutine pool          │
└──────┬──────────────────────┘
       │ Create Coroutine
       ▼
┌─────────────────────────────┐
│   Coroutine Context         │
│   - NON-BLOCKING            │
│   - Can handle others       │
│   - Minimal memory          │
└──────┬──────────────────────┘
       │ Async HTTP Request
       ▼
┌─────────────────────────────┐
│   Client Response           │
│   - Worker still active     │
│   - Memory persisted        │
└─────────────────────────────┘
```

### Key Differences

**Traditional PHP:**
- ✅ Familiar untuk semua PHP developers
- ✅ Mudah deployment (shared hosting support)
- ✅ Banyak dokumentasi
- ❌ Blocking I/O (menunggu response)
- ❌ Process overhead per request
- ❌ Bootstrap overhead (load classes setiap request)

**Swoole:**
- ✅ Non-blocking I/O (async)
- ✅ Persistent memory
- ✅ High concurrency
- ❌ Requires extension installation
- ❌ Different programming paradigm
- ❌ Less compatible dengan shared hosting

---

## 💻 SETUP & INSTALLATION

### Option 1: Built-in PHP Server (Simplest)

**No installation needed!** Langsung pakai built-in server.

```bash
cd non-swoole
php server.php
```

Server akan jalan di: `http://localhost:8888`

### Option 2: Nginx + PHP-FPM (Recommended for Production)

#### Install Nginx dan PHP-FPM

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-cli php8.1-curl php8.1-mbstring

# Start PHP-FPM
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm

# Verify
sudo systemctl status php8.1-fpm
```

#### Configure Nginx

```bash
# Copy nginx config
sudo cp non-swoole/nginx.conf /etc/nginx/sites-available/pcare-traditional

# Edit path jika perlu
sudo nano /etc/nginx/sites-available/pcare-traditional

# Enable site
sudo ln -s /etc/nginx/sites-available/pcare-traditional /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

Server akan jalan di: `http://localhost:8888`

### Option 3: Apache + mod_php

#### Install Apache dan PHP

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y apache2 libapache2-mod-php8.1 php8.1-cli php8.1-curl php8.1-mbstring

# Enable PHP module
sudo a2enmod php8.1

# Configure Apache port (if needed)
sudo nano /etc/apache2/ports.conf
# Add: Listen 8888
```

#### Configure Apache

```bash
# Copy apache config
sudo cp non-swoole/apache.conf /etc/apache2/sites-available/pcare-traditional.conf

# Edit path jika perlu
sudo nano /etc/apache2/sites-available/pcare-traditional.conf

# Enable site
sudo a2ensite pcare-traditional

# Test configuration
sudo apache2ctl configtest

# Restart apache
sudo systemctl restart apache2
```

Server akan jalan di: `http://localhost:8888`

---

## 🚀 CARA PENGGUNAAN

### 1. Setup Environment

```bash
# Non-swoole folder sudah memiliki symlink ke:
# - vendor/ (shared dengan parent directory)
# - .env (shared dengan parent directory)

# Verify symlinks
cd non-swoole
ls -la vendor .env
```

### 2. Start Server

#### Option A: Built-in Server
```bash
cd non-swoole
php server.php

# Output:
# ======================================
# Traditional PHP Built-in Server
# ======================================
# Server: http://0.0.0.0:8888
# Document Root: /path/to/non-swoole
# Press Ctrl+C to stop
# ======================================
```

#### Option B: Nginx/Apache
```bash
# Already running as service
sudo systemctl status nginx
# or
sudo systemctl status apache2
```

### 3. Test Request

```bash
# Basic test
curl http://localhost:8888

# Test dengan endpoint parameter
curl http://localhost:8888?endpoint=/pcare-rest/dokter/0/100

# Pretty print JSON
curl -s http://localhost:8888 | jq .

# Save to file
curl http://localhost:8888 > response.json
```

### 4. Expected Response

```json
{
    "success": true,
    "data": {
        "list": [
            {
                "kdDokter": "12345",
                "nmDokter": "Dr. Example",
                ...
            }
        ]
    },
    "meta": {
        "execution_time_ms": 1245.67,
        "timestamp": "2024-01-01 12:00:00",
        "endpoint": "/pcare-rest/dokter/0/100",
        "method": "traditional_php_curl"
    }
}
```

---

## 📊 BENCHMARK COMPARISON

### Running Comparison Benchmark

```bash
# Terminal 1: Start Swoole server
cd /path/to/swoole-pcare
php server.php

# Terminal 2: Start Traditional PHP server
cd /path/to/swoole-pcare/non-swoole
php server.php

# Terminal 3: Run comparison benchmark
cd /path/to/swoole-pcare
./benchmark_comparison.sh
```

### Script akan menjalankan:

1. **Light Load**: 100 requests, 10 concurrent
2. **Medium Load**: 500 requests, 50 concurrent
3. **Heavy Load**: 1000 requests, 100 concurrent

### Expected Results

#### Light Load (100 req, 10 concurrent)

| Metric | Swoole | Traditional PHP | Winner |
|--------|--------|-----------------|--------|
| RPS | 80-100 | 40-60 | 🏆 Swoole (1.5-2x) |
| Avg Time | 100-150ms | 150-250ms | 🏆 Swoole |
| Memory | 50MB | 200MB | 🏆 Swoole |

#### Medium Load (500 req, 50 concurrent)

| Metric | Swoole | Traditional PHP | Winner |
|--------|--------|-----------------|--------|
| RPS | 150-200 | 50-80 | 🏆 Swoole (2-3x) |
| Avg Time | 200-300ms | 500-800ms | 🏆 Swoole |
| Memory | 50MB | 400MB | 🏆 Swoole |

#### Heavy Load (1000 req, 100 concurrent)

| Metric | Swoole | Traditional PHP | Winner |
|--------|--------|-----------------|--------|
| RPS | 200-300 | 50-100 | 🏆 Swoole (3-4x) |
| Avg Time | 300-500ms | 800-1500ms | 🏆 Swoole |
| Memory | 60MB | 600MB+ | 🏆 Swoole |
| Errors | 0-1% | 5-10% | 🏆 Swoole |

### Real-World Comparison

**Scenario: 1000 concurrent users**

**Traditional PHP (PHP-FPM):**
```
- Needs: 1000 PHP-FPM workers
- Memory: ~1000 x 50MB = 50GB RAM
- Response: Slow due to context switching
- Result: Server will crash or timeout
```

**Swoole:**
```
- Needs: 4-8 workers (CPU cores)
- Memory: ~60MB total
- Response: Fast with coroutines
- Result: Handles smoothly
```

---

## ⚖️ KELEBIHAN & KEKURANGAN

### Traditional PHP

#### ✅ Kelebihan

1. **Familiar & Simple**
   - Standard PHP programming
   - No learning curve
   - Easy to debug

2. **Wide Compatibility**
   - Shared hosting support
   - All servers support (Nginx, Apache, LiteSpeed)
   - No special extensions needed

3. **Mature Ecosystem**
   - Banyak dokumentasi
   - Large community
   - Established best practices

4. **Isolation**
   - Request isolated (error di 1 request tidak affect lainnya)
   - No state management issues
   - Fresh start setiap request

5. **Easy Deployment**
   - Upload via FTP
   - No daemon management
   - Auto-restart by web server

#### ❌ Kekurangan

1. **Poor Performance**
   - Blocking I/O
   - Bootstrap overhead setiap request
   - Context switching overhead

2. **High Resource Usage**
   - Memory per request
   - Process overhead
   - Not scalable

3. **Limited Concurrency**
   - Terbatas oleh jumlah workers
   - Cannot handle spike traffic
   - Timeout under load

4. **No Long-lived Connections**
   - WebSocket not native
   - Database connection pool limited
   - No persistent state

---

### Swoole

#### ✅ Kelebihan

1. **High Performance**
   - Non-blocking I/O
   - Persistent memory
   - Coroutine-based concurrency

2. **Low Resource Usage**
   - Single worker handles many requests
   - Memory efficient
   - Highly scalable

3. **Modern Features**
   - WebSocket support
   - HTTP/2 support
   - Built-in async MySQL/Redis

4. **Long-lived State**
   - Connection pooling
   - Persistent cache
   - Stateful applications

#### ❌ Kekurangan

1. **Requires Extension**
   - Manual installation
   - Not available on shared hosting
   - Compilation needed

2. **Different Paradigm**
   - Learning curve
   - Cannot use global variables
   - Memory leak risks if not careful

3. **Debugging Challenges**
   - Hard to debug async code
   - Error affects whole server
   - Requires careful error handling

4. **Deployment Complexity**
   - Daemon management
   - Process monitoring
   - Manual restart on code changes

---

## 🎯 KAPAN PAKAI MANA?

### Gunakan Traditional PHP jika:

- ✅ Deployment di shared hosting
- ✅ Low to medium traffic (<1000 req/day)
- ✅ Simple CRUD application
- ✅ Team tidak familiar dengan async programming
- ✅ Budget terbatas (cheap hosting)

### Gunakan Swoole jika:

- ✅ High traffic (>10,000 req/day)
- ✅ Real-time features needed
- ✅ Performance critical
- ✅ VPS/Dedicated server available
- ✅ Team capable of async programming

---

## 🔧 SWITCHING BETWEEN IMPLEMENTATIONS

### From Traditional to Swoole

**Benefits:**
- 2-5x performance improvement
- Better resource utilization
- Can handle more concurrent users

**Migration Steps:**
1. Setup VPS/Cloud server
2. Install OpenSwoole extension
3. Adapt code for persistent state
4. Test thoroughly
5. Deploy with systemd

### From Swoole to Traditional

**When needed:**
- Downgrading to shared hosting
- Team skills limitation
- Simpler deployment

**Migration Steps:**
1. Copy `non-swoole/` implementation
2. Setup Nginx/Apache
3. Configure PHP-FPM
4. Deploy

---

## 📁 FILE STRUCTURE

```
non-swoole/
├── index.php           # Main application logic
├── functions.php       # Helper functions (decrypt, decompress)
├── server.php          # Built-in server runner
├── composer.json       # Dependencies
├── nginx.conf          # Nginx configuration
├── apache.conf         # Apache configuration
├── vendor -> ../vendor # Symlink to parent vendor
└── .env -> ../.env     # Symlink to parent .env
```

---

## 📚 REFERENCES

- [PHP Built-in Server](https://www.php.net/manual/en/features.commandline.webserver.php)
- [Nginx + PHP-FPM](https://www.nginx.com/resources/wiki/start/topics/examples/phpfcgi/)
- [Apache + mod_php](https://httpd.apache.org/docs/2.4/mod/mod_php.html)
- [cURL Documentation](https://www.php.net/manual/en/book.curl.php)

---

## 🤝 SUPPORT

Untuk pertanyaan atau issue, silakan buka issue di repository atau hubungi:

**Author**: abworks
**Email**: abysalim007@gmail.com

---

**Last Updated**: 2024
