<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin']);

$id = $_POST['id'] ?? '';
$data = [
    $_POST['kode'] ?? '',
    $_POST['nama'] ?? '',
    $_POST['centroid_akademik'] ?? 0,
    $_POST['centroid_ekstrakurikuler'] ?? 0,
    $_POST['centroid_absensi'] ?? 0,
    $_POST['centroid_tugas'] ?? 0,
];

if ($id) {
    $stmt = $pdo->prepare("UPDATE clusters SET kode=?, nama=?, centroid_akademik=?, centroid_ekstrakurikuler=?, centroid_absensi=?, centroid_tugas=? WHERE id=?");
    $stmt->execute([...$data, $id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO clusters (kode, nama, centroid_akademik, centroid_ekstrakurikuler, centroid_absensi, centroid_tugas) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute($data);
}

header('Location: ../index.php?page=clusters');
exit;
