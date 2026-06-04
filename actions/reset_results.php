<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin', 'pimpinan']);
$pdo->exec("DELETE FROM kmeans_assignments");
$pdo->exec("DELETE FROM kmeans_iterations");
$pdo->exec("DELETE FROM kmeans_runs");
header('Location: ../index.php?page=proses');
exit;
