<?php
// auth/supabase.php — kumpulan fungsi autentikasi Supabase Auth (JWT)
// Semua panggilan HTTP ke Supabase memakai cURL dengan header apikey.

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

// Masa berlaku cache JWKS (detik) dan lokasi file cache
const JWKS_TTL  = 3600;
const JWKS_FILE = __DIR__ . '/../tmp/jwks.json';

/**
 * Pembungkus cURL untuk memanggil API Supabase.
 * $opsi: ['metode' => 'GET|POST', 'body' => array, 'token' => string bearer, 'query' => array]
 * Mengembalikan array: ['ok' => bool, 'status' => int, 'data' => array|null, 'pesan' => string]
 */
function supabase_request(string $metode, string $path, array $opsi = []): array
{
    $url = SUPABASE_URL . $path;
    if (!empty($opsi['query'])) {
        $url .= (str_contains($path, '?') ? '&' : '?') . http_build_query($opsi['query']);
    }

    $header = [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    if (!empty($opsi['token'])) {
        $header[] = 'Authorization: Bearer ' . $opsi['token'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $metode,
        CURLOPT_HTTPHEADER     => $header,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if (!empty($opsi['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opsi['body']));
    }

    $respons = curl_exec($ch);
    $status  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    if ($respons === false) {
        $pesan = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'status' => 0, 'data' => null,
            'pesan' => 'Tidak dapat terhubung ke server Supabase. (' . $pesan . ')'];
    }
    curl_close($ch);

    $data = json_decode($respons, true);
    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'pesan' => ''];
}

/**
 * Menyimpan hasil login/refresh (access_token, refresh_token, claims JWT) ke session.
 * Access token di-decode payload-nya (bukan diverifikasi) karena respons datang
 * langsung dari Supabase lewat HTTPS.
 */
function auth_simpan_sesi(array $data): void
{
    $_SESSION['sb_access_token']  = $data['access_token'] ?? '';
    $_SESSION['sb_refresh_token'] = $data['refresh_token'] ?? '';
    $_SESSION['sb_claims']        = auth_payload_token($_SESSION['sb_access_token']);
}

/** Mengambil claims (payload) dari token tanpa memverifikasi tanda tangan. */
function auth_payload_token(string $token): ?array
{
    $potongan = explode('.', $token);
    if (count($potongan) !== 3) {
        return null;
    }
    $payload = json_decode(base64_decode(strtr($potongan[1], '-_', '+/')), true);
    return is_array($payload) ? $payload : null;
}

/** Mengambil claims pengguna yang tersimpan di session (untuk navbar dsb). */
function auth_sesi(): ?array
{
    return $_SESSION['sb_claims'] ?? null;
}

/**
 * Login email + password: POST /auth/v1/token?grant_type=password
 * Mengembalikan [bool sukses, string pesan].
 */
function auth_login_email(string $email, string $password): array
{
    $hasil = supabase_request('POST', '/auth/v1/token', [
        'query' => ['grant_type' => 'password'],
        'body'  => ['email' => $email, 'password' => $password],
    ]);

    if (!$hasil['ok']) {
        $pesan = 'Email atau password salah.';
        if ($hasil['status'] === 0) {
            $pesan = $hasil['pesan'];
        } elseif (!empty($hasil['data']['msg']) && !str_contains($hasil['data']['msg'], 'Invalid login')) {
            $pesan = $hasil['data']['msg'];
        } elseif ($hasil['status'] === 400 && str_contains($hasil['data']['error_description'] ?? $hasil['data']['error'] ?? '', 'not confirmed')) {
            $pesan = 'Email belum dikonfirmasi. Silakan periksa kotak masuk Anda.';
        }
        return [false, $pesan];
    }

    session_regenerate_id(true);
    auth_simpan_sesi($hasil['data']);
    return [true, ''];
}

/**
 * Registrasi email + password: POST /auth/v1/signup
 * Kalau konfirmasi email aktif di project, Supabase tidak langsung mengembalikan sesi.
 */
function auth_daftar(string $email, string $password): array
{
    $hasil = supabase_request('POST', '/auth/v1/signup', [
        'body' => ['email' => $email, 'password' => $password],
    ]);

    if (!$hasil['ok']) {
        $pesan = $hasil['status'] === 0 ? $hasil['pesan'] : ($hasil['data']['msg'] ?? 'Registrasi gagal.');
        return [false, $pesan];
    }

    // Kalau Supabase langsung mengembalikan sesi (auto-confirm aktif), masuk saja.
    if (!empty($hasil['data']['access_token'])) {
        session_regenerate_id(true);
        auth_simpan_sesi($hasil['data']);
        return [true, ''];
    }

    return [false, 'Registrasi berhasil. Silakan periksa email Anda untuk verifikasi sebelum masuk.'];
}

/**
 * Membuat URL OAuth (mis. Google) dengan alur PKCE di sisi server.
 * code_verifier dan state disimpan di session untuk ditukar di callback.
 * Provider lain cukup dipanggil dengan auth_url_oauth('github').
 */
function auth_url_oauth(string $provider): string
{
    $verifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '='); // 43-128 karakter
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    $state = bin2hex(random_bytes(16));

    $_SESSION['sb_oauth_verifier'] = $verifier;
    $_SESSION['sb_oauth_state']    = $state;

    $redirect_to = BASE_URL . 'index.php?hal=login&aksi=callback';
    return SUPABASE_URL . '/auth/v1/authorize?' . http_build_query([
        'provider'             => $provider,
        'redirect_to'          => $redirect_to,
        'code_challenge'       => $challenge,
        'code_challenge_method' => 's256',
        'state'                => $state,
    ]);
}

/**
 * Menukar authorization code (dari callback OAuth) menjadi token:
 * POST /auth/v1/token?grant_type=pkce dengan auth_code + code_verifier.
 */
function auth_tukar_code(string $code, string $verifier): array
{
    $hasil = supabase_request('POST', '/auth/v1/token', [
        'query' => ['grant_type' => 'pkce'],
        'body'  => ['auth_code' => $code, 'code_verifier' => $verifier],
    ]);

    if (!$hasil['ok']) {
        return [false, $hasil['status'] === 0 ? $hasil['pesan'] : 'Pertukaran kode OAuth gagal. Coba masuk lagi.'];
    }

    session_regenerate_id(true);
    auth_simpan_sesi($hasil['data']);
    return [true, ''];
}

/**
 * Memuat JWKS dari {SUPABASE_URL}/auth/v1/.well-known/jwks.json dengan cache 1 jam.
 * Mengembalikan array kunci (bisa kosong bila project memakai kunci simetris).
 */
function auth_muat_jwks(): array
{
    $cache = null;
    if (is_file(JWKS_FILE)) {
        $cache = json_decode((string) file_get_contents(JWKS_FILE), true);
        if (!empty($cache['waktu']) && time() - (int) $cache['waktu'] < JWKS_TTL && isset($cache['jwks'])) {
            return $cache['jwks'];
        }
    }

    $hasil = supabase_request('GET', '/auth/v1/.well-known/jwks.json');
    if ($hasil['status'] === 0) {
        // Server Supabase tak terjangkau: pakai cache lama bila ada.
        return is_array($cache ?? null) ? ($cache['jwks'] ?? []) : [];
    }

    $jwks = $hasil['data'] ?? [];
    @mkdir(dirname(JWKS_FILE), 0775, true);
    file_put_contents(JWKS_FILE, json_encode(['waktu' => time(), 'jwks' => $jwks]));
    return $jwks;
}

/**
 * Verifikasi access token:
 *  1) Coba verifikasi tanda tangan JWT memakai JWKS (kunci asimetris RS256/ES256).
 *  2) Bila JWKS kosong (project memakai kunci simetris), fallback ke
 *     GET /auth/v1/user — status 200 berarti token valid.
 * Memeriksa claim exp dan iss.
 * Mengembalikan [bool valid, ?array claims, string pesan].
 */
function auth_verifikasi_jwt(string $token): array
{
    $iss_diharapkan = SUPABASE_URL . '/auth/v1';

    // --- Jalur 1: verifikasi dengan JWKS ---
    $jwks = auth_muat_jwks();
    if (!empty($jwks['keys'])) {
        try {
            JWT::$leeway = 10; // toleransi perbedaan jam
            $kunci  = JWK::parseKeySet($jwks);
            $claims = (array) JWT::decode($token, $kunci);
        } catch (Throwable $e) {
            return [false, null, 'Token tidak valid: ' . $e->getMessage()];
        }

        if (empty($claims['exp']) || $claims['exp'] < time()) {
            return [false, $claims, 'Token telah kedaluwarsa.'];
        }
        $iss = rtrim((string) ($claims['iss'] ?? ''), '/');
        if ($iss !== rtrim($iss_diharapkan, '/')) {
            return [false, $claims, 'Issuer token tidak dikenal.'];
        }
        return [true, $claims, ''];
    }

    // --- Jalur 2 (fallback): kunci simetris, validasi lewat endpoint /auth/v1/user ---
    $hasil = supabase_request('GET', '/auth/v1/user', ['token' => $token]);
    if ($hasil['status'] === 0) {
        return [false, null, $hasil['pesan']];
    }
    if (!$hasil['ok']) {
        return [false, null, 'Token tidak valid atau kedaluwarsa.'];
    }

    $pengguna = $hasil['data'] ?? [];
    $claims   = [
        'sub'   => $pengguna['id'] ?? '',
        'email' => $pengguna['email'] ?? '',
        // exp tidak tersedia di endpoint user; refresh tetap ditangani auth_sesi_valid()
        'exp'   => null,
        'iss'   => $iss_diharapkan,
    ];
    return [true, $claims, ''];
}

/**
 * Menukar refresh_token dengan access token baru:
 * POST /auth/v1/token?grant_type=refresh_token
 */
function auth_refresh(): bool
{
    $refresh = $_SESSION['sb_refresh_token'] ?? '';
    if ($refresh === '') {
        return false;
    }

    $hasil = supabase_request('POST', '/auth/v1/token', [
        'query' => ['grant_type' => 'refresh_token'],
        'body'  => ['refresh_token' => $refresh],
    ]);

    if (!$hasil['ok']) {
        return false;
    }

    auth_simpan_sesi($hasil['data']);
    return true;
}

/** Logout di sisi Supabase: POST /auth/v1/logout (mencabut refresh token). */
function auth_logout(): void
{
    $token = $_SESSION['sb_access_token'] ?? '';
    if ($token !== '') {
        supabase_request('POST', '/auth/v1/logout', ['token' => $token]);
    }
}

/**
 * Penjaga sesi untuk tiap request pada halaman terlindungi:
 * memverifikasi JWT; bila kedaluwarsa, coba refresh; bila gagal, bersihkan session.
 * Mengembalikan claims bila sesi valid, atau null bila harus login ulang.
 */
function auth_sesi_valid(): ?array
{
    $token = $_SESSION['sb_access_token'] ?? '';
    if ($token === '') {
        return null;
    }

    [$valid, $claims, $pesan] = auth_verifikasi_jwt($token);
    if ($valid && $claims !== null) {
        $_SESSION['sb_claims'] = $claims;
        return $claims;
    }

    // Token kedaluwarsa/tidak valid: coba tukar refresh_token
    if (auth_refresh()) {
        return $_SESSION['sb_claims'];
    }

    // Gagal juga: hapus seluruh penanda sesi
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    return null;
}
