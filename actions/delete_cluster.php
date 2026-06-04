<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin']);
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM clusters WHERE id=?");
    $stmt->execute([$id]);
}
header('Location: ../index.php?page=clusters');
exit;
