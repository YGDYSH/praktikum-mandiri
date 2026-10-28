<?php
// halaman/login/google.php — memulai alur OAuth Google via Supabase (PKCE di sisi server)
$redirect = auth_url_oauth('google');
header('Location: ' . $redirect);
exit;
