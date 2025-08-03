DapurPintar API 🍳
Ini adalah backend API untuk aplikasi DapurPintar, sebuah aplikasi web cerdas yang menyarankan resep masakan berdasarkan bahan yang dimiliki pengguna. Dibangun dengan Laravel, API ini menyediakan endpoint yang aman untuk autentikasi pengguna, manajemen pantry, dan algoritma pencarian resep yang canggih.

✨ Fitur Utama
Autentikasi Aman: Sistem registrasi, login, dan logout berbasis token menggunakan Laravel Sanctum.

Manajemen Pantry Pribadi: Endpoint RESTful dengan fungsionalitas CRUD (Create, Read, Update, Delete) penuh untuk setiap pantry pengguna.

Algoritma Pencarian Cerdas: Endpoint khusus yang membandingkan bahan milik pengguna dengan ribuan resep dari API eksternal, lalu mengurutkannya berdasarkan persentase kecocokan.

Detail Resep: Endpoint untuk mengambil detail lengkap resep, termasuk bahan dan instruksi memasak.

🛠️ Teknologi yang Digunakan
Framework: Laravel 10

Bahasa: PHP 8+

Database: SQLite (untuk pengembangan)

Autentikasi API: Laravel Sanctum

HTTP Client: Laravel HTTP Client

🚀 Instalasi & Setup Lokal
Clone repositori ini:

git clone https://github.com/NAMA_ANDA/dapurpintar-api-v2.git
cd dapurpintar-api-v2

Install dependensi Composer:

composer install

Buat file .env:

cp .env.example .env

Generate application key:

php artisan key:generate

Setup database SQLite:

Pastikan file database/database.sqlite sudah ada (jika belum, buat file kosong).

Ubah .env Anda: DB_CONNECTION=sqlite

Jalankan migrasi database:

php artisan migrate

Jalankan server development:

php artisan serve

API sekarang berjalan di http://127.0.0.1:8000.

Endpoints API
Semua endpoint berada di bawah prefix /api.

POST /v1/register - Registrasi pengguna baru.

POST /v1/login - Login pengguna.

GET /v1/pantry-items - Mendapatkan semua item di pantry pengguna (memerlukan autentikasi).

POST /v1/pantry-items - Menambah item baru ke pantry (memerlukan autentikasi).

DELETE /v1/pantry-items/{id} - Menghapus item dari pantry (memerlukan autentikasi).

GET /v1/find-recipes - Menjalankan algoritma pencarian resep (memerlukan autentikasi).

GET /v1/recipes/{id} - Mendapatkan detail resep (memerlukan autentikasi).