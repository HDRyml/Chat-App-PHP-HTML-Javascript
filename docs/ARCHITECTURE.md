# Dokumentasi Keputusan Arsitektur (Bertahap)

## 1. Pendekatan Pengerjaan Bertahap (Tahap 1 — Dasar)
Proyek dibangun secara bertahap mulai dari fondasi **Kriteria 2** (manajemen akun dan CRUD kontak) dengan pemisahan tegas antara folder `frontend/` (HTML, pure CSS, native JS) dan `backend/` (PHP 8.4 native + MariaDB).

## 2. Kendala & Keputusan Teknis Sejauh Ini
1. **Subfolder Routing pada Laragon:**
   - **Kendala:** Saat diakses melalui `http://localhost/Big/Chat-App-PHP-HTML-Javascript/backend/...`, `REQUEST_URI` menyertakan path folder proyek.
   - **Keputusan:** `Router::dispatch()` memotong base directory dari `$_SERVER['SCRIPT_NAME']` sehingga API tetap portable baik di root domain maupun di subfolder.
2. **Autentikasi Stateless dengan JWT (`php-jwt` 7.2.1):**
   - **Keputusan:** Memakai Bearer Token JWT (`HS256`) agar frontend dan backend terpisah bersih tanpa bergantung pada PHP session cookie, sekaligus tetap mematuhi aturan library yang diizinkan (`php-jwt`).
3. **Skema `users` & `contacts` Tahap Awal:**
   - **Keputusan:** Di Tahap 1 tabel `users` dan `contacts` dibuat sederhana dengan relasi `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE` agar penghapusan akun otomatis membersihkan data kontaknya. *(Pada tahap lanjutan akan ditingkatkan ke Generated Unique ID + Pessimistic Locking).*
