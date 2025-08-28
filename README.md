# P-Care BPJS dengan Swoole

Ini adalah aplikasi berbasis PHP yang berfungsi sebagai *client* untuk berinteraksi dengan API P-Care dari BPJS Kesehatan. Aplikasi ini dibangun menggunakan ekstensi OpenSwoole untuk menjalankan server HTTP non-blocking dan menangani permintaan secara efisien.

## Alur Kerja Aplikasi

1.  **Menerima Request**: Sebuah server OpenSwoole berjalan dan mendengarkan permintaan HTTP yang masuk.
2.  **Menghubungi API P-Care**: Untuk setiap permintaan yang diterima, server akan menjalankan logika untuk menghubungi endpoint API P-Care BPJS.
3.  **Otentikasi**: Skrip secara otomatis membuat *header* otentikasi yang diperlukan oleh P-Care (`X-cons-id`, `X-timestamp`, `X-signature`, dll.).
4.  **Menerima & Memproses Respons**: Aplikasi menerima respons dari P-Care, yang datanya terenkripsi dan terkompresi.
5.  **Dekripsi & Dekompresi**: Respons tersebut kemudian diproses melalui dua tahap:
    -   **Dekripsi**: Menggunakan `AES-256-CBC` dengan kunci yang dibentuk dari kredensial.
    -   **Dekompresi**: Menggunakan algoritma LZ-String untuk mendapatkan data JSON asli.

---

## Kebutuhan Sistem (Requirements)

-   **PHP**: Versi 7.4 atau lebih baru.
-   **Ekstensi PHP `openswoole`**: **WAJIB**. Ekstensi ini harus diinstal secara manual di lingkungan PHP Anda, misalnya menggunakan PECL.
    ```bash
    pecl install openswoole
    ```
-   **Composer**: Untuk manajemen dependensi PHP.

---

## Cara Penggunaan

#### 1. Instalasi Proyek

-   Clone repositori ini.
-   Salin file `.env.example` menjadi file `.env`.
    ```bash
    cp .env.example .env
    ```
-   Buka file `.env` dan **isi semua variabel** dengan kredensial P-Care dan konfigurasi Anda yang sebenarnya.
    ```dotenv
    # Server Configuration
    SERVER_PORT=9501

    # BPJS P-Care API Credentials
    PCARE_BASE_URL="https://apijkn.bpjs-kesehatan.go.id"
    PCARE_CONS_ID="DENGAN_CONS_ID_ANDA"
    PCARE_SECRET_KEY="DENGAN_SECRET_KEY_ANDA"
    PCARE_USER_KEY="DENGAN_USER_KEY_ANDA"
    PCARE_USER="DENGAN_USER_PCARE_ANDA"
    PCARE_PASS="DENGAN_PASS_PCARE_ANDA"
    ```

#### 2. Instalasi Dependensi

Jalankan perintah berikut untuk menginstal library yang diperlukan (seperti `phpdotenv`).

```bash
composer install
```

#### 3. Menjalankan Server

Untuk memulai server OpenSwoole, jalankan perintah ini dari terminal:

```bash
php server.php
```

Server akan berjalan sesuai dengan port yang Anda atur di file `.env` (default: `9501`). Endpoint API yang dituju di-hardcode di `index.php` sebagai `/pcare-rest/dokter/0/100`.

#### 4. Menguji Aplikasi

Setelah server berjalan, buka terminal lain dan gunakan `curl` untuk mengirim permintaan ke server Anda:

```bash
curl http://localhost:9501
```

Output dari `curl` akan menampilkan log lengkap dari proses komunikasi dengan API P-Care, termasuk hasil akhir data yang telah berhasil didekripsi dan didekompresi.

#### 5. Limitasi Fitur

Untuk saat ini, fungsionalitas proyek hanya mengakomodasi permintaan ke API P-Care yang menggunakan metode **GET**. Fungsionalitas untuk metode lain (seperti POST, PUT, DELETE) belum diimplementasikan.
