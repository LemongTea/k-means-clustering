<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin']);

$id = $_POST['id'] ?? '';
$nama = $_POST['nama'] ?? '';
$nisn = $_POST['nisn'] ?? '';
$akademik = $_POST['nilai_akademik'] ?? 0;
$ekstra = $_POST['nilai_ekstrakurikuler'] ?? 0;
$absensi = $_POST['absensi'] ?? 0;
$tugas = $_POST['nilai_tugas'] ?? 0;

if ($id) {
    $stmt = $pdo->prepare("UPDATE siswa SET nama=?, nisn=?, nilai_akademik=?, nilai_ekstrakurikuler=?, absensi=?, nilai_tugas=? WHERE id=?");
    $stmt->execute([$nama, $nisn, $akademik, $ekstra, $absensi, $tugas, $id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO siswa (nama, nisn, nilai_akademik, nilai_ekstrakurikuler, absensi, nilai_tugas) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $nisn, $akademik, $ekstra, $absensi, $tugas]);
}

header('Location: ../index.php?page=siswa');
exit;
