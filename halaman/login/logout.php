<?php
// halaman/login/logout.php — hapus session, cabut token di Supabase, arahkan ke login
auth_logout();

$_SESSION = [];
session_destroy();

// Regenerasi session id baru agar session fixation tidak mungkin
session_start();
session_regenerate_id(true);

redirect('login');
