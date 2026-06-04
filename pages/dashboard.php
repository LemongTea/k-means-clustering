<?php
require_role(['admin', 'pimpinan']);
$totalSiswa = $pdo->query("SELECT COUNT(*) AS total FROM siswa")->fetch()['total'] ?? 0;
$totalCluster = $pdo->query("SELECT COUNT(*) AS total FROM clusters")->fetch()['total'] ?? 0;
$lastRun = $pdo->query("SELECT * FROM kmeans_runs ORDER BY id DESC LIMIT 1")->fetch();
$clusterSummary = [];
if ($lastRun) {
    $stmt = $pdo->prepare("SELECT c.kode, c.nama, COUNT(a.id) AS jumlah
        FROM clusters c
        LEFT JOIN kmeans_assignments a ON a.cluster_id = c.id AND a.run_id = ?
        WHERE a.iterasi = (SELECT MAX(iterasi) FROM kmeans_assignments WHERE run_id = ?)
        GROUP BY c.id, c.kode, c.nama ORDER BY c.kode");
    $stmt->execute([$lastRun['id'], $lastRun['id']]);
    $clusterSummary = $stmt->fetchAll();
}
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-sm text-slate-500">Total Siswa</p>
        <h3 class="text-3xl font-bold mt-2"><?= e($totalSiswa) ?></h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-sm text-slate-500">Total Cluster</p>
        <h3 class="text-3xl font-bold mt-2"><?= e($totalCluster) ?></h3>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-sm text-slate-500">Proses Terakhir</p>
        <h3 class="text-xl font-bold mt-2"><?= $lastRun ? e($lastRun['status']) : 'Belum Diproses' ?></h3>
        <p class="text-xs text-slate-500 mt-1"><?= $lastRun ? e($lastRun['tanggal_proses']) : '-' ?></p>
    </div>
</div>

<div class="bg-white border rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-bold mb-3">Deskripsi Sistem</h3>
    <p class="text-slate-600 leading-relaxed">
        Sistem ini digunakan untuk mengelompokkan siswa berdasarkan tingkat kemajuan belajar menggunakan metode
        <strong>K-Means Clustering</strong>. Atribut yang digunakan adalah nilai akademik hasil konversi, tingkat kehadiran/absensi hasil konversi, keaktifan/ekstrakurikuler hasil konversi, dan nilai tugas/praktik hasil konversi sesuai BAB IV. Jumlah cluster default mengikuti PDF yaitu K=3.
    </p>
</div>

<?php if ($clusterSummary): ?>
<div class="bg-white border rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-bold mb-4">Ringkasan Hasil Cluster Terakhir</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-slate-50">
                <tr>
                    <th class="border px-3 py-2 text-left">Cluster</th>
                    <th class="border px-3 py-2 text-left">Keterangan</th>
                    <th class="border px-3 py-2 text-right">Jumlah Siswa</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clusterSummary as $row): ?>
                <tr>
                    <td class="border px-3 py-2 font-semibold"><?= e($row['kode']) ?></td>
                    <td class="border px-3 py-2"><?= e($row['nama']) ?></td>
                    <td class="border px-3 py-2 text-right"><?= e($row['jumlah']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
