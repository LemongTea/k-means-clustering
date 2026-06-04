<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin']);

$id = $_POST['id'] ?? '';
$nama = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'pimpinan';
$status = $_POST['status'] ?? 'aktif';

if (!in_array($role, ['admin', 'pimpinan'], true)) {
    $role = 'pimpinan';
}
if (!in_array($status, ['aktif', 'nonaktif'], true)) {
    $status = 'aktif';
}

if ($id) {
    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, role=?, status=? WHERE id=?");
        $stmt->execute([$nama, $username, $hash, $role, $status, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=?, status=? WHERE id=?");
        $stmt->execute([$nama, $username, $role, $status, $id]);
    }
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $username, $hash, $role, $status]);
}

header('Location: ../index.php?page=users');
exit;
