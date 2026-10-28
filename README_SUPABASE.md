# Setup Autentikasi Supabase (JWT + Google OAuth)

Panduan untuk menyiapkan Supabase Auth, Google Cloud, dan lingkungan lokal
aplikasi **proyek-admin**.

---

## 1. Setup Supabase

### 1.1 Buat project
1. Masuk ke <https://supabase.com>, buat/pilih project Anda.
2. Catat dua nilai dari **Project Settings → API**:
   - **Project URL** → dipakai sebagai `SUPABASE_URL`
   - **anon / publishable key** (bukan `service_role`!) → dipakai sebagai `SUPABASE_KEY`

### 1.2 Aktifkan provider Email
1. Buka **Authentication → Providers → Email**, pastikan status **Enabled**.
2. Untuk pengujian lokal, Anda bisa mematikan konfirmasi email:
   - Matikan **Confirm email** agar registrasi langsung membuat sesi, atau
   - Biarkan aktif; registrasi akan meminta pengguna memverifikasi email dulu
     (aplikasi sudah menangani kedua kasus).

### 1.3 Atur URL Site & Redirect
Buka **Authentication → URL Configuration**:
- **Site URL**: `http://localhost/proyek-admin/`
- **Redirect URLs** — tambahkan:
  ```
  http://localhost/proyek-admin/index.php?hal=login&aksi=callback
  ```
  (Tambahkan juga `http://localhost/proyek-admin/**` bila ingin bebas mengubah aksi.)

> Untuk produksi, ganti dengan domain asli Anda dan pastikan HTTPS.

### 1.4 Kunci JWT (untuk verifikasi tanda tangan)
- Project Supabase baru secara bawaan memakai **kunci asimetris (ES256)**.
  Verifikasi dilakukan otomatis via JWKS:
  `{SUPABASE_URL}/auth/v1/.well-known/jwks.json` (cache 1 jam di `tmp/jwks.json`).
- Project lama yang masih memakai **JWT secret (simetris)**: aplikasi otomatis
  fallback ke `GET /auth/v1/user` untuk memvalidasi token — tidak perlu
  mengubah apa pun. Namun disarankan bermigrasi ke kunci asimetris
  (**Project Settings → JWT Keys → Migrate JWT secret**).

---

## 2. Setup Google Cloud (Login dengan Google)

1. Buka <https://console.cloud.google.com> → buat project (atau pakai yang ada).
2. **APIs & Services → OAuth consent screen**:
   - User type: *External*, isi nama aplikasi + email dukungan.
   - Tambahkan domain `supabase.co` ke **Authorized domains** (jika diminta).
3. **Credentials → Create Credentials → OAuth client ID**:
   - Application type: **Web application**
   - **Authorized redirect URIs** — masukkan URI callback Supabase:
     ```
     https://YOUR_PROJECT_REF.supabase.co/auth/v1/callback
     ```
     (sesuaikan dengan `SUPABASE_URL` Anda; lihat juga kotak bantuan di
     halaman provider Google pada Supabase)
4. Salin **Client ID** dan **Client Secret**, lalu tempel di Supabase:
   **Authentication → Providers → Google → Enabled**, isi Client ID & Secret.

> Provider lain (GitHub, dll.) tinggal konfigurasi di Supabase dan dipanggil
> lewat `auth_url_oauth('github')` — tidak perlu mengubah kode lain.

---

## 3. Konstanta di env.php

Tambahkan dua baris berikut di `env.php` (isi dengan nilai project Anda —
jangan pernah menaruh `service_role` atau secret key):

```php
define('SUPABASE_URL', 'https://YOUR_PROJECT_REF.supabase.co');   // Project URL
define('SUPABASE_KEY', 'sb_publishable_xxx...');                  // anon/publishable key
```

---

## 4. Composer (firebase/php-jwt)

Library verifikasi JWT: `firebase/php-jwt`. Jalankan di folder proyek:

```bash
composer install
```

Ini akan membuat folder `vendor/` dari `composer.json` yang sudah ada.

### Catatan khusus Avast/SSL di Windows
Jika `composer install` gagal dengan
`SSL certificate problem: unable to get local issuer certificate`, itu karena
Avast (atau antivirus lain) menyisipkan sertifikatnya sendiri saat HTTPS scan.
Solusi: gabungkan **root CA Avast** ke berkas CA yang dipakai PHP:

1. Unduh CA terbaru: <https://curl.se/ca/cacert.pem>
2. Ekstrak root Avast dari cert store Windows (PowerShell):
   ```powershell
   $c = Get-ChildItem Cert:\LocalMachine\Root, Cert:\CurrentUser\Root |
        Where-Object { $_.Subject -like '*Avast*' } | Select-Object -First 1
   "-----BEGIN CERTIFICATE-----`n" +
   [Convert]::ToBase64String($c.RawData, 'InsertLineBreaks') +
   "`n-----END CERTIFICATE-----" | Add-Content D:\laragon\etc\ssl\cacert.pem
   ```
   (sebelumnya salin dulu isi cacert.pem baru ke `D:\laragon\etc\ssl\cacert.pem`)
3. Bila masih gagal, jalankan composer dengan:
   ```powershell
   $env:COMPOSER_CAFILE = 'D:\laragon\etc\ssl\cacert.pem'
   composer install
   ```
   Pastikan `php.ini` berisi `curl.cainfo = D:\laragon\etc\ssl\cacert.pem`.

Alternatif yang lebih bersih: matikan **HTTPS scanning** di pengaturan Avast.

---

## 5. Struktur kode

| Berkas | Isi |
| --- | --- |
| `auth/supabase.php` | Semua fungsi auth: `supabase_request`, `auth_login_email`, `auth_daftar`, `auth_url_oauth($provider)`, `auth_tukar_code`, `auth_verifikasi_jwt`, `auth_refresh`, `auth_logout`, `auth_sesi_valid` (penjaga), `auth_sesi` (claims) |
| `index.php` | Router + penjaga JWT; route `login/{index,daftar,google,callback,logout}` |
| `halaman/login/index.php` | Form email/password + tombol Google + tautan daftar |
| `halaman/login/daftar.php` | Registrasi email/password |
| `halaman/login/google.php` | Membuat PKCE (verifier + challenge S256), redirect ke Supabase |
| `halaman/login/callback.php` | Validasi `state`, tukar `code` → token, masuk dashboard |
| `halaman/login/logout.php` | Logout lokal + panggilan `/auth/v1/logout` |
| `template/header.php` | Email pengguna dari claims JWT di navbar |
| `tmp/jwks.json` | Cache JWKS (otomatis, berlaku 1 jam) |

Token (`access_token`, `refresh_token`) disimpan di `$_SESSION`
(`sb_access_token`, `sb_refresh_token`, `sb_claims`) — **bukan** localStorage.
Cookie session memakai `httponly` + `SameSite=Lax`, dan
`session_regenerate_id(true)` dipanggil setelah login berhasil.

---

## 6. Cara menguji

1. **Login salah password** → pesan galat muncul di kotak login.
2. **Login benar** → diarahkan ke dashboard, email tampil di navbar.
3. **Registrasi** → akun dibuat; bila konfirmasi email aktif, verifikasi dulu.
4. **Tombol "Masuk dengan Google"** → dibawa ke Google, kembali ke dashboard
   (pastikan Redirect URI Google & Redirect URLs Supabase sudah benar).
5. **Tanpa login**: buka `http://localhost/proyek-admin/index.php?hal=buku`
   → harus diarahkan ke halaman login.
6. **Token dipalsukan**: ubah `$_SESSION['sb_access_token']` secara manual
   (mis. via script) → request berikutnya ditolak dan kembali ke login.
7. **Logout** → akses ke halaman terlindung hilang; token juga dicabut di
   sisi Supabase.

---

## 7. Selalu ingat

- Jangan pernah menaruh `service_role` atau secret key di aplikasi web.
- Publishable/anon key boleh dibagikan karena tetap dilindungi RLS.
- Untuk produksi: aktifkan HTTPS, ganti Site URL & Redirect URLs ke domain asli.
