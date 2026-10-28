# proyek-admin — Cara Menjalankan

1. Salin folder `proyek-admin/` ke `htdocs` (XAMPP) atau `www` (Laragon/WAMP).
2. Unduh AdminLTE 4 (https://github.com/ColorlibHQ/AdminLTE/releases), lalu salin:
   - `dist/css/adminlte.css`  -> `assets/css/adminlte.css`
   - `dist/js/adminlte.js`    -> `assets/js/adminlte.js`
   - `dist/assets/img/*`      -> `assets/img/`
3. Buka phpMyAdmin, impor `sql/database.sql`.
4. Atur autentikasi Supabase: ikuti panduan `README_SUPABASE.md`
   (setup project, provider Google, dan konstanta `SUPABASE_*` di `env.php`),
   lalu daftar/masuk melalui halaman login aplikasi.
5. Sesuaikan `BASE_URL` dan kredensial DB di `env.php`.
6. Buka http://localhost/proyek-admin/
