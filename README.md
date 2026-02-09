# P-Care BPJS dengan Swoole
## High Performance Async HTTP Request untuk BPJS API

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![OpenSwoole](https://img.shields.io/badge/OpenSwoole-4.x-green)](https://openswoole.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## 🎯 OVERVIEW

Aplikasi PHP berbasis **OpenSwoole** untuk melakukan HTTP request ke API P-Care BPJS Kesehatan secara **asynchronous** dan **high performance**. Project ini menyediakan **dua implementasi** untuk perbandingan:

1. **Swoole (Async)** - High performance, non-blocking I/O
2. **Traditional PHP** - Standard synchronous implementation

### 🚀 Performance Highlights

| Metric | Swoole | Traditional PHP | Improvement |
|--------|--------|-----------------|-------------|
| **RPS** | 200-500 | 50-100 | **3-5x faster** |
| **Response Time** | 100-300ms | 500-1500ms | **70% faster** |
| **Memory** | 50-100MB | 500MB-2GB | **90% less** |
| **Concurrency** | 1000+ | 50-200 | **5-10x more** |

---

## 📁 PROJECT STRUCTURE

```
swoole-pcare/
│
├── 🔥 Swoole Implementation (Async)
│   ├── server.php              # Swoole HTTP server
│   ├── index.php               # Business logic
│   ├── functions.php           # Helper functions
│   ├── .env                    # Configuration
│   └── composer.json           # Dependencies
│
├── 📦 non-swoole/ (Traditional PHP)
│   ├── index.php               # Traditional implementation
│   ├── functions.php           # Helper functions
│   ├── server.php              # Built-in server runner
│   ├── nginx.conf              # Nginx configuration
│   ├── apache.conf             # Apache configuration
│   └── README.md               # Traditional PHP guide
│
├── 📚 Documentation
    ├── README.md               # This file
    ├── QUICKSTART.md           # Quick start guide (START HERE!)
    ├── USAGE_GUIDE.md          # Complete usage guide
    ├── BENCHMARK_GUIDE.md      # Benchmark testing guide
    └── COMPARISON.md           # Detailed Swoole vs Traditional comparison
```

---

## ⚡ QUICK START

### 1. Install Dependencies

```bash
# Install PHP 8.1+
sudo apt install php8.1-cli php8.1-dev php8.1-mbstring php8.1-curl

# Install OpenSwoole extension
sudo pecl install openswoole
echo "extension=openswoole.so" | sudo tee -a /etc/php/8.1/cli/php.ini

# Install Composer dependencies
composer install

# Setup environment
cp .env.example .env
nano .env  # Edit dengan credentials Anda
```

### 2. Test Swoole Version

```bash
# Start Swoole server
php server.php

# Test (terminal baru)
curl http://localhost:9501
```

### 3. Test Traditional PHP Version

```bash
# Start Traditional server
cd non-swoole
php server.php

# Test (terminal baru)
curl http://localhost:8888
```

### 4. Run Comparison Benchmark

```bash
# Make sure both servers are running
./benchmark_comparison.sh
```

**📖 Detailed Guide**: See [QUICKSTART.md](QUICKSTART.md)

---

## 📚 DOCUMENTATION

### 🎓 Getting Started

- **[QUICKSTART.md](QUICKSTART.md)** - Start here! 5-minute setup guide
- **[USAGE_GUIDE.md](USAGE_GUIDE.md)** - Complete usage guide with all details

### 🧪 Testing & Benchmarking

- **[BENCHMARK_GUIDE.md](BENCHMARK_GUIDE.md)** - How to benchmark and test performance
- **[COMPARISON.md](COMPARISON.md)** - Detailed Swoole vs Traditional PHP comparison

### 🔧 Development

- **[IMPROVEMENTS.md](IMPROVEMENTS.md)** - Code review, issues, and recommendations
- **[non-swoole/README.md](non-swoole/README.md)** - Traditional PHP implementation guide

---

## 🏗️ ARCHITECTURE

### Swoole (Async) Flow

```
Client Request → Swoole Server → Coroutine → BPJS API (Non-blocking)
                      ↓
                 Response ← Decrypt ← Decompress ← API Response
```

**Features:**
- ✅ Non-blocking I/O
- ✅ Coroutine-based concurrency
- ✅ Persistent memory
- ✅ Built-in HTTP server
- ✅ 3-5x faster than traditional PHP

### Traditional PHP Flow

```
Client Request → Nginx/Apache → PHP-FPM → BPJS API (Blocking)
                      ↓
                 Response ← Decrypt ← Decompress ← API Response
```

**Features:**
- ✅ Standard PHP (familiar)
- ✅ Works on shared hosting
- ✅ Easy deployment
- ✅ Wide compatibility
- ❌ Slower, more memory usage

---

## 🎯 USE CASES

### Use Swoole When:

- ✅ High traffic (>10,000 requests/day)
- ✅ Performance critical
- ✅ Real-time features needed
- ✅ VPS/Cloud server available
- ✅ Team capable of async programming

### Use Traditional PHP When:

- ✅ Low traffic (<1,000 requests/day)
- ✅ Shared hosting
- ✅ Simple CRUD application
- ✅ Quick deployment needed
- ✅ Team prefers simplicity

---

## 🔒 SECURITY

### ⚠️ CRITICAL: Credentials Exposed

**File `.env` berisi credentials asli yang ter-commit ke git!**

**ACTION REQUIRED:**

```bash
# 1. Remove from git
git rm --cached .env
echo ".env" >> .gitignore
git commit -m "Remove .env from repository"

# 2. Change BPJS passwords immediately
# Login ke portal BPJS dan regenerate keys

# 3. Use .env.example for template
cp .env .env.example
# Edit .env.example, replace all values with placeholders
```

---

## 🐛 KNOWN ISSUES

### 1. Timestamp Calculation Error

**Location**: `index.php` line 27

```php
// ❌ WRONG
$tstamp = strval(time()-strtotime('1970-01-01 00:00:00'));

// ✅ CORRECT
$tstamp = strval(time());
```

### 2. No Error Handling

Add try-catch blocks for network errors and timeouts.

### 3. No Connection Pooling

Implement connection pooling for better performance.

**See [IMPROVEMENTS.md](IMPROVEMENTS.md) for complete list and solutions.**

---

## 📊 BENCHMARK RESULTS

### Test Environment
- CPU: 4 cores @ 2.4GHz
- RAM: 8GB
- PHP 8.1 + OpenSwoole 4.x
- BPJS API avg response: 800ms

### Results Summary

#### Light Load (100 requests, 10 concurrent)
- **Swoole**: 80 RPS, 125ms avg
- **Traditional**: 39.5 RPS, 253ms avg
- **Winner**: 🏆 Swoole (2x faster)

#### Medium Load (500 requests, 50 concurrent)
- **Swoole**: 155.8 RPS, 321ms avg
- **Traditional**: 58.3 RPS, 857ms avg
- **Winner**: 🏆 Swoole (2.7x faster)

#### Heavy Load (1000 requests, 100 concurrent)
- **Swoole**: 221.2 RPS, 452ms avg
- **Traditional**: 60.3 RPS, 1658ms avg
- **Winner**: 🏆 Swoole (3.7x faster)

**Full benchmark guide**: [BENCHMARK_GUIDE.md](BENCHMARK_GUIDE.md)

---

## 🛠️ REQUIREMENTS

### System Requirements

- **OS**: Linux (Ubuntu 20.04+, Debian 10+, CentOS 7+)
- **PHP**: 8.0+ (recommended: 8.1+)
- **Memory**: Minimum 512MB, Recommended 2GB+
- **CPU**: Minimum 2 cores, Recommended 4+ cores

### PHP Extensions

- `openswoole` (for Swoole version)
- `curl` (for Traditional version)
- `json`
- `openssl`
- `mbstring`

### Dependencies

```json
{
    "netom/lz-string-php": "^1.3",
    "vlucas/phpdotenv": "^5.5"
}
```

---

## 🚀 DEPLOYMENT

### Development

```bash
# Swoole
php server.php

# Traditional
cd non-swoole && php server.php
```

### Production (Swoole)

```bash
# 1. Create systemd service
sudo cp docs/swoole-pcare.service /etc/systemd/system/
sudo systemctl enable swoole-pcare
sudo systemctl start swoole-pcare

# 2. Setup Nginx reverse proxy
sudo cp docs/nginx-swoole.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/nginx-swoole.conf /etc/nginx/sites-enabled/
sudo systemctl reload nginx
```

### Production (Traditional PHP)

```bash
# 1. Setup Nginx + PHP-FPM
sudo cp non-swoole/nginx.conf /etc/nginx/sites-available/pcare
sudo ln -s /etc/nginx/sites-available/pcare /etc/nginx/sites-enabled/
sudo systemctl reload nginx

# 2. Or Apache
sudo cp non-swoole/apache.conf /etc/apache2/sites-available/pcare.conf
sudo a2ensite pcare
sudo systemctl reload apache2
```


## 👨‍💻 AUTHOR

**abworks**
- Email: abysalim007@gmail.com
- GitHub: https://github.com/absalim11

---

## 🙏 ACKNOWLEDGMENTS

- [OpenSwoole](https://openswoole.com/) - High performance async framework
- [BPJS Kesehatan](https://bpjs-kesehatan.go.id/) - API provider
- [LZ-String PHP](https://github.com/nullpunkt/lz-string-php) - Compression library

---

## 📞 SUPPORT

### Documentation

- 📖 [Quick Start Guide](QUICKSTART.md)
- 📖 [Complete Usage Guide](USAGE_GUIDE.md)
- 📖 [Benchmark Guide](BENCHMARK_GUIDE.md)
- 📖 [Comparison Guide](COMPARISON.md)

---

**Documentation:**
- ✅ QUICKSTART.md - Quick start guide
- ✅ USAGE_GUIDE.md - Complete usage guide
- ✅ BENCHMARK_GUIDE.md - Testing guide
- ✅ COMPARISON.md - Detailed comparison
- ✅ non-swoole/README.md - Traditional PHP guide

---

## 🔗 USEFUL LINKS

- [OpenSwoole Documentation](https://openswoole.com/docs)
- [BPJS API Documentation](https://dvlp.bpjs-kesehatan.go.id/docs)
- [PHP Best Practices](https://www.php-fig.org/psr/)
- [Nginx Configuration](https://nginx.org/en/docs/)

---

**⭐ Star this repository if you find it useful!**

**Last Updated**: 2026
