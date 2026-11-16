# Deklarasi & Evaluasi Penggunaan Generative AI

## 1. Persentase Penggunaan AI
- **Total Estimasi Keterlibatan AI (Tahap 1):** `60%` (Pemahaman, pengujian manual Postman/Browser, dan penyesuaian: `40%`)
- **Rincian per Bagian:**
  - Backend (PHP 8.4 Native, MariaDB, JWT): `60%`
  - Frontend (HTML, Pure CSS, Native JS `fetch`): `75%`
  - Pengujian & Dokumentasi: `30%`

## 2. Contoh Prompt Kunci yang Digunakan
1. **Topik:** *Memecah Pengerjaan Bertahap dari Dasar (Tahap 1 / CPK 2)*
   - **Prompt:** `"Saya ingin membangun proyek aplikasi pesan ini sedikit demi sedikit mulai dari CPK 2 (register, login JWT, hapus akun, dan CRUD kontak) agar paham setiap bagiannya sebelum lanjut ke validasi lanjutan dan optimasi."`
   - **Tujuan:** Menyusun arsitektur bertahap dari fondasi autentikasi dan manajemen kontak.
2. **Topik:** *Penanganan Routing Subfolder di Laragon & Error JSON*
   - **Prompt:** `"Bagaimana cara agar Router PHP native tetap mencocokkan route /api/... saat proyek dijalankan di subfolder Laragon, serta mengembalikan error exception dalam format JSON?"`
   - **Tujuan:** Memastikan seluruh response API konsisten berbentuk JSON dan berjalan di environment lokal Laragon.

## 3. Evaluasi & Solusi atas Kode yang Dihasilkan AI
| Fitur / Bagian | Masalah / Kendala yang Ditemui | Solusi / Perbaikan yang Dilakukan |
| :--- | :--- | :--- |
| Routing Subfolder (`Router.php`) | `REQUEST_URI` mengandung prefix `/Big/Chat-App-PHP-HTML-Javascript/backend` sehingga route `/api/...` mengembalikan `404` | Memotong `dirname($_SERVER['SCRIPT_NAME'])` di `Router::dispatch()` sebelum mencocokkan pola route |
| Secret Key JWT (`Auth.php`) | `php-jwt` v7.2.1 melempar `Provided key is too short` jika secret kurang dari 32 byte | Memperpanjang default secret >= 32 karakter dan menormalisasi secret pendek dengan `hash('sha256', ..., true)` |
| Sinkronisasi Skema Database | Saat uji di Postman muncul `Field 'id' doesn't have a default value` karena tabel `users` lama masih tersimpan | Melakukan `DROP DATABASE` lalu mengimport ulang `schema.sql` agar struktur tabel sesuai |
