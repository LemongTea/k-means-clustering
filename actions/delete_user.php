<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin']);

$id = (int)($_GET['id'] ?? 0);
$currentId = (int)(current_user()['id'] ?? 0);
if ($id > 0 && $id !== $currentId) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
}
header('Location: ../index.php?page=users');
exit;
