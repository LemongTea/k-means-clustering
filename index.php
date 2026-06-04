<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$page = $_GET['page'] ?? (is_logged_in() ? 'dashboard' : 'login');
$allowed = ['login', 'dashboard', 'siswa', 'clusters', 'users', 'proses', 'hasil', 'laporan'];

if (!in_array($page, $allowed, true)) {
    $page = is_logged_in() ? 'dashboard' : 'login';
}

if ($page === 'login') {
    if (is_logged_in()) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    require_once __DIR__ . '/pages/login.php';
    exit;
}

require_login();
if (!can_access_page($page)) {
    header('Location: index.php?page=dashboard&error=akses_ditolak');
    exit;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . "/pages/{$page}.php";
require_once __DIR__ . '/includes/footer.php';
