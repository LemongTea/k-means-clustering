<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$currentPage = $_GET['page'] ?? 'dashboard';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K-Means Kemajuan Belajar Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
<div class="min-h-screen flex">
    <aside class="w-72 bg-white border-r border-slate-200 hidden md:block">
        <div class="p-6 border-b">
            <h1 class="text-xl font-bold text-blue-700">K-Means Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">Clustering kemajuan belajar</p>
            <?php if ($user): ?>
                <div class="mt-4 rounded-lg bg-slate-50 border p-3">
                    <p class="text-sm font-semibold"><?= e($user['nama']) ?></p>
                    <p class="text-xs text-slate-500 capitalize">Role: <?= e($user['role']) ?></p>
                </div>
            <?php endif; ?>
        </div>
        <nav class="p-4 space-y-2">
            <a href="index.php?page=dashboard" class="block px-4 py-3 rounded-lg <?= active_menu('dashboard', $currentPage) ?>">Dashboard</a>
            <?php if (is_admin()): ?>
                <a href="index.php?page=siswa" class="block px-4 py-3 rounded-lg <?= active_menu('siswa', $currentPage) ?>">CRUD Siswa</a>
                <a href="index.php?page=clusters" class="block px-4 py-3 rounded-lg <?= active_menu('clusters', $currentPage) ?>">CRUD Cluster & Centroid</a>
                <a href="index.php?page=users" class="block px-4 py-3 rounded-lg <?= active_menu('users', $currentPage) ?>">CRUD User</a>
            <?php endif; ?>
            <a href="index.php?page=proses" class="block px-4 py-3 rounded-lg <?= active_menu('proses', $currentPage) ?>">Proses K-Means</a>
            <a href="index.php?page=hasil" class="block px-4 py-3 rounded-lg <?= active_menu('hasil', $currentPage) ?>">Hasil Perhitungan</a>
            <a href="index.php?page=laporan" class="block px-4 py-3 rounded-lg <?= active_menu('laporan', $currentPage) ?>">Cetak Laporan</a>
            <a href="actions/logout.php" onclick="return confirm('Logout dari sistem?')" class="block px-4 py-3 rounded-lg text-red-600 hover:bg-red-50">Logout</a>
        </nav>
    </aside>
    <main class="flex-1">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Aplikasi PHP Native K-Means Clustering</h2>
                <p class="text-sm text-slate-500">Nilai Akademik, Kehadiran/Absensi, Keaktifan, dan Tugas</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if (is_admin() || is_pimpinan()): ?>
                    <a href="index.php?page=proses" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Proses Data</a>
                <?php endif; ?>
                <a href="actions/logout.php" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm hover:bg-slate-200">Logout</a>
            </div>
        </header>
        <section class="p-6">
            <?php if (($_GET['error'] ?? '') === 'akses_ditolak'): ?>
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Akses ditolak. Role Anda tidak memiliki izin membuka halaman tersebut.
                </div>
            <?php endif; ?>
