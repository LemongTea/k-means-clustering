<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif' LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'nama' => $user['nama'],
        'username' => $user['username'],
        'role' => $user['role'],
    ];
    header('Location: ../index.php?page=dashboard');
    exit;
}

header('Location: ../index.php?page=login&error=invalid');
exit;
