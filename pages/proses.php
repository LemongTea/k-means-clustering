<?php
require_role(['admin', 'pimpinan']);
$siswaCount = $pdo->query("SELECT COUNT(*) AS total FROM siswa")->fetch()['total'];
$clusterCount = $pdo->query("SELECT COUNT(*) AS total FROM clusters")->fetch()['total'];
$lastRun = $pdo->query("SELECT * FROM kmeans_runs ORDER BY id DESC LIMIT 1")->fetch();
?>
<div class="bg-white border rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-bold mb-3">Proses Perhitungan K-Means</h3>
    <p class="text-slate-600 mb-4">
        Tombol di bawah akan memproses data siswa menggunakan centroid pada menu Cluster. Sistem akan menyimpan detail jarak Euclidean tiap siswa ke semua cluster yang tersedia, sehingga jumlah cluster bisa lebih dari 3.
    </p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="border rounded-lg p-4 bg-slate-50">
            <p class="text-sm text-slate-500">Jumlah Data Siswa</p>
            <p class="text-2xl font-bold"><?= e($siswaCount) ?></p>
        </div>
        <div class="border rounded-lg p-4 bg-slate-50">
            <p class="text-sm text-slate-500">Jumlah Cluster</p>
            <p class="text-2xl font-bold"><?= e($clusterCount) ?></p>
        </div>
        <div class="border rounded-lg p-4 bg-slate-50">
            <p class="text-sm text-slate-500">Proses Terakhir</p>
            <p class="text-lg font-bold"><?= $lastRun ? e($lastRun['status']) : 'Belum ada' ?></p>
        </div>
    </div>

    <form method="post" action="actions/run_kmeans.php" class="flex flex-wrap gap-3 items-center">
        <label class="text-sm font-medium">Maksimal Iterasi</label>
        <input type="number" name="max_iterasi" value="10" min="1" max="50" class="border rounded-lg px-3 py-2 w-32">
        <button class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700" onclick="return confirm('Jalankan proses K-Means?')">Jalankan K-Means</button>
        <a href="actions/reset_results.php" onclick="return confirm('Hapus semua hasil proses?')" class="bg-red-600 text-white px-5 py-2 rounded-lg hover:bg-red-700">Reset Hasil</a>
    </form>
</div>

<div class="bg-white border rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-bold mb-3">Alur Proses</h3>
    <ol class="list-decimal ml-6 space-y-2 text-slate-600">
        <li>Ambil data siswa dari tabel <strong>siswa</strong>.</li>
        <li>Ambil centroid awal dari tabel <strong>clusters</strong>.</li>
        <li>Hitung jarak Euclidean setiap siswa ke seluruh cluster yang tersedia.</li>
        <li>Pilih cluster dengan jarak paling kecil.</li>
        <li>Hitung centroid baru berdasarkan rata-rata anggota cluster.</li>
        <li>Ulangi sampai centroid tidak berubah atau mencapai maksimal iterasi.</li>
    </ol>
</div>
