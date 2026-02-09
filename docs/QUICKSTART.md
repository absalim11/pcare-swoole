# QUICK START GUIDE
## Swoole vs Traditional PHP Comparison

---

## 🚀 QUICK START (5 Minutes)

### Step 1: Test Swoole Version

```bash
# Terminal 1: Start Swoole server
cd /home/abworks/Documents/Riset/swoole-pcare
php server.php

# Terminal 2: Test
curl http://localhost:9501
```

### Step 2: Test Traditional PHP Version

```bash
# Terminal 1: Start Traditional server
cd /home/abworks/Documents/Riset/swoole-pcare/non-swoole
php server.php

# Terminal 2: Test
curl http://localhost:8888
```

### Step 3: Run Individual Benchmarks

```bash
# Make sure both servers are running, then:

# Benchmark Swoole (100 requests, 10 concurrent)
php benchmark/benchmark_swoole.php

# Benchmark Non-Swoole (100 requests, 10 concurrent)
php benchmark/benchmark_non_swoole.php
```

**Note:** Reduced to 100 requests/10 concurrent to conserve BPJS API limits.

---

## 📁 PROJECT STRUCTURE

```
swoole-pcare/
│
├── 📂 benchmark/              # Benchmark scripts & results
│   ├── benchmark_swoole.php    # Swoole benchmark (port 9501)
│   └── benchmark_non_swoole.php # Non-Swoole benchmark (port 8888)
│
├── 📂 documentations/         # Documentation files
│   ├── QUICKSTART.md           # This file
│   ├── BENCHMARK_GUIDE.md      # Benchmark guide
│   ├── COMPARISON.md           # Swoole vs Traditional comparison
│   ├── IMPROVEMENTS.md         # Feature improvements
│   ├── TROUBLESHOOTING_401.md  # Error troubleshooting
│   └── USAGE_GUIDE.md          # Detailed usage guide
│
├── 📂 non-swoole/             # Traditional PHP Implementation (Sync)
│   ├── server.php              # Built-in PHP server (port 8888)
│   ├── index.php               # Business logic
│   ├── functions.php           # Helper functions
│   ├── nginx.conf              # Nginx configuration
│   └── apache.conf             # Apache configuration
│
├── 📂 vendor/                 # Composer dependencies
│
├── server.php                 # Swoole HTTP server (port 9501)
├── index.php                  # Swoole business logic
├── functions.php              # Helper functions
├── .env                       # Environment configuration
├── composer.json              # Composer configuration
└── README.md                  # Main project README
│   ├── .env                    # Configuration
│   └── composer.json           # Dependencies
│
├── 📂 non-swoole/ (Traditional PHP)
│   ├── index.php               # Traditional implementation
│   ├── functions.php           # Helper functions
│   ├── server.php              # Built-in server runner
│   ├── nginx.conf              # Nginx config
│   ├── apache.conf             # Apache config
│   └── README.md               # Traditional PHP guide
│
├── 📂 Documentation
│   ├── README.md               # Main documentation
│   ├── USAGE_GUIDE.md          # Complete usage guide
│   ├── BENCHMARK_GUIDE.md      # Benchmark testing guide
│   ├── IMPROVEMENTS.md         # Issues & recommendations
│   ├── COMPARISON.md           # Detailed comparison
│   └── QUICKSTART.md           # This file
│
└── 📂 Benchmark Scripts
    ├── benchmark.sh            # Swoole benchmark
    ├── benchmark.php           # PHP native benchmark
    ├── benchmark_simple.php    # Simple benchmark
    └── benchmark_comparison.sh # Comparison benchmark
```

---

## 🎯 WHAT'S BEEN CREATED

### 1. Traditional PHP Implementation ✅

**Location**: `non-swoole/`

**Features**:
- ✅ Standard PHP with cURL
- ✅ Same functionality as Swoole version
- ✅ Works with Nginx/Apache/Built-in server
- ✅ Easy deployment
- ✅ Shared hosting compatible

**Files**:
- `index.php` - Main application
- `functions.php` - Decrypt & decompress helpers
- `server.php` - Built-in server runner
- `nginx.conf` - Nginx configuration
- `apache.conf` - Apache configuration
- `composer.json` - Dependencies

### 2. Comparison Benchmark Script ✅

**Location**: `benchmark_comparison.sh`

**Features**:
- ✅ Automated testing
- ✅ Tests both implementations
- ✅ 3 load scenarios (light, medium, heavy)
- ✅ Generates comparison report
- ✅ Saves detailed results

**Usage**:
```bash
./benchmark_comparison.sh
```

### 3. Comprehensive Documentation ✅

**Files Created**:

1. **USAGE_GUIDE.md** (Main Guide)
   - Complete installation guide
   - Configuration details
   - Security best practices
   - Optimization tips
   - Troubleshooting
   - Production deployment

2. **BENCHMARK_GUIDE.md** (Testing Guide)
   - Benchmark methodology
   - Testing tools
   - Metrics explanation
   - Expected results
   - Comparison testing

3. **COMPARISON.md** (Detailed Comparison)
   - Architecture differences
   - Performance benchmarks
   - Cost comparison
   - Use case recommendations
   - Decision matrix

4. **non-swoole/README.md** (Traditional PHP Guide)
   - Setup instructions
   - Deployment options
   - Comparison with Swoole
   - When to use what

---

## 📊 EXPECTED RESULTS

### Performance Comparison

| Load Level | Swoole RPS | Traditional RPS | Improvement |
|------------|-----------|-----------------|-------------|
| Light (10 concurrent) | 80-100 | 40-60 | **2x faster** |
| Medium (50 concurrent) | 150-200 | 50-80 | **3x faster** |
| Heavy (100 concurrent) | 200-300 | 50-100 | **4x faster** |

### Memory Usage

| Implementation | Memory Usage |
|----------------|--------------|
| Swoole | 50-100 MB (total) |
| Traditional | 500MB-2GB (total) |
| **Savings** | **90% less memory** |

---

## 🔧 SETUP OPTIONS

### Option 1: Built-in PHP Server (Easiest)

**Traditional PHP**:
```bash
cd non-swoole
php server.php
# Access: http://localhost:8888
```

**Swoole**:
```bash
php server.php
# Access: http://localhost:9501
```

**Pros**: No installation needed
**Cons**: Not for production

### Option 2: Nginx + PHP-FPM (Recommended)

**Setup**:
```bash
# Install
sudo apt install nginx php8.1-fpm

# Configure
sudo cp non-swoole/nginx.conf /etc/nginx/sites-available/pcare-traditional
sudo ln -s /etc/nginx/sites-available/pcare-traditional /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# Access: http://localhost:8888
```

**Pros**: Production-ready, fast
**Cons**: Requires server access

### Option 3: Apache + mod_php

**Setup**:
```bash
# Install
sudo apt install apache2 libapache2-mod-php8.1

# Configure
sudo cp non-swoole/apache.conf /etc/apache2/sites-available/pcare-traditional.conf
sudo a2ensite pcare-traditional
sudo systemctl restart apache2

# Access: http://localhost:8888
```

**Pros**: Widely supported
**Cons**: Slower than Nginx

---

## 🧪 TESTING WORKFLOW

### 1. Basic Functionality Test

```bash
# Test Swoole
curl http://localhost:9501

# Test Traditional
curl http://localhost:8888

# Both should return BPJS data
```

### 2. Performance Test

```bash
# Quick test with Apache Bench
ab -n 100 -c 10 http://localhost:9501/  # Swoole
ab -n 100 -c 10 http://localhost:8888/  # Traditional
```

### 3. Full Comparison

```bash
# Run automated comparison
./benchmark_comparison.sh

# View results
cat comparison_results/comparison_report.txt
```

---

## 🎓 LEARNING PATH

### For Beginners

1. **Start with Traditional PHP**
   - Read: `non-swoole/README.md`
   - Run: Built-in server
   - Test: Basic curl requests

2. **Understand the Code**
   - Review: `non-swoole/index.php`
   - Learn: How BPJS API works
   - Experiment: Change endpoints

3. **Try Swoole**
   - Read: `USAGE_GUIDE.md`
   - Install: OpenSwoole extension
   - Run: Swoole server
   - Compare: Response times

### For Advanced Users

1. **Performance Analysis**
   - Read: `BENCHMARK_GUIDE.md`
   - Run: All benchmark tests
   - Analyze: Results and bottlenecks

2. **Production Deployment**
   - Read: `USAGE_GUIDE.md` (Production section)
   - Setup: Systemd service
   - Configure: Nginx reverse proxy
   - Monitor: Logs and metrics

---

## ⚠️ IMPORTANT NOTES

### Performance

1. **BPJS API Response Time**
   - Average: 800-1500ms
   - Your app performance depends on this
   - Consider caching for frequently accessed data

2. **Concurrent Limits**
   - Traditional PHP: Limited by PHP-FPM workers
   - Swoole: Can handle 1000+ concurrent
   - Test your limits before production

---

## 📚 DOCUMENTATION INDEX

| Document | Purpose | Audience |
|----------|---------|----------|
| **README.md** | Project overview | Everyone |
| **QUICKSTART.md** | Quick start guide | Beginners |
| **USAGE_GUIDE.md** | Complete usage guide | Developers |
| **BENCHMARK_GUIDE.md** | Testing guide | DevOps |
| **COMPARISON.md** | Detailed comparison | Decision makers |
| **non-swoole/README.md** | Traditional PHP guide | PHP developers |

---
