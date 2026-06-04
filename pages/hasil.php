<?php
require_role(['admin', 'pimpinan']);
$runs = $pdo->query("SELECT * FROM kmeans_runs ORDER BY id DESC")->fetchAll();
$selectedRunId = $_GET['run_id'] ?? ($runs[0]['id'] ?? null);
$iterations = [];
$centroidsByIter = [];
$assignmentsByIter = [];
$distancesByAssignment = [];
$clusterHeaders = [];
$summary = [];
$selectedRun = null;

if ($selectedRunId) {
    $stmt = $pdo->prepare("SELECT * FROM kmeans_runs WHERE id = ?");
    $stmt->execute([$selectedRunId]);
    $selectedRun = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT DISTINCT iterasi FROM kmeans_assignments WHERE run_id = ? ORDER BY iterasi ASC");
    $stmt->execute([$selectedRunId]);
    $iterations = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.kode, c.nama
        FROM kmeans_distances d
        JOIN clusters c ON c.id = d.cluster_id
        WHERE d.run_id = ?
        ORDER BY c.kode ASC");
    $stmt->execute([$selectedRunId]);
    $clusterHeaders = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT ki.*, c.kode, c.nama
        FROM kmeans_iterations ki
        JOIN clusters c ON c.id = ki.cluster_id
        WHERE ki.run_id = ?
        ORDER BY ki.iterasi, c.kode");
    $stmt->execute([$selectedRunId]);
    foreach ($stmt->fetchAll() as $row) {
        $centroidsByIter[$row['iterasi']][] = $row;
    }

    $stmt = $pdo->prepare("SELECT a.*, s.nama, s.nilai_akademik, s.nilai_ekstrakurikuler, s.absensi, s.nilai_tugas, c.kode, c.nama AS nama_cluster
        FROM kmeans_assignments a
        JOIN siswa s ON s.id = a.siswa_id
        JOIN clusters c ON c.id = a.cluster_id
        WHERE a.run_id = ?
        ORDER BY a.iterasi ASC, s.id ASC");
    $stmt->execute([$selectedRunId]);
    foreach ($stmt->fetchAll() as $row) {
        $assignmentsByIter[$row['iterasi']][] = $row;
    }

    $stmt = $pdo->prepare("SELECT d.assignment_id, c.kode, d.jarak
        FROM kmeans_distances d
        JOIN clusters c ON c.id = d.cluster_id
        WHERE d.run_id = ?
        ORDER BY c.kode ASC");
    $stmt->execute([$selectedRunId]);
    foreach ($stmt->fetchAll() as $row) {
        $distancesByAssignment[$row['assignment_id']][$row['kode']] = $row['jarak'];
    }

    $stmt = $pdo->prepare("SELECT c.kode, c.nama, COUNT(a.id) AS jumlah
        FROM kmeans_assignments a
        JOIN clusters c ON c.id = a.cluster_id
        WHERE a.run_id = ? AND a.iterasi = (SELECT MAX(iterasi) FROM kmeans_assignments WHERE run_id = ?)
        GROUP BY c.id, c.kode, c.nama ORDER BY c.kode");
    $stmt->execute([$selectedRunId, $selectedRunId]);
    $summary = $stmt->fetchAll();
}
?>
<div class="bg-white border rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold">Hasil Perhitungan K-Means Dinamis</h3>
            <p class="text-sm text-slate-500">Kolom jarak otomatis mengikuti jumlah cluster yang tersedia, tidak dibatasi hanya C1, C2, C3.</p>
            <?php if ($selectedRun): ?>
                <p class="text-sm text-slate-600 mt-2">Status: <strong><?= e($selectedRun['status']) ?></strong> | <?= e($selectedRun['catatan']) ?></p>
            <?php endif; ?>
        </div>
        <div class="flex flex-col md:flex-row gap-2 md:items-center">
            <?php if ($selectedRunId): ?>
                <a href="index.php?page=laporan&run_id=<?= e($selectedRunId) ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 text-center">Cetak Laporan</a>
            <?php endif; ?>
            <form method="get" class="flex gap-2 items-center">
                <input type="hidden" name="page" value="hasil">
                <select name="run_id" class="border rounded-lg px-3 py-2" onchange="this.form.submit()">
                <?php foreach ($runs as $run): ?>
                    <option value="<?= e($run['id']) ?>" <?= $selectedRunId == $run['id'] ? 'selected' : '' ?>>
                        Proses #<?= e($run['id']) ?> - <?= e($run['tanggal_proses']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
</div>

<?php if (!$selectedRunId): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-yellow-800">Belum ada hasil proses. Silakan jalankan proses K-Means terlebih dahulu.</div>
<?php else: ?>
    <?php if ($summary): ?>
    <div class="bg-white border rounded-xl shadow-sm p-6 mb-6">
        <h3 class="font-bold text-lg mb-4">Ringkasan Cluster Akhir</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-3 py-2">Cluster</th>
                        <th class="border px-3 py-2 text-left">Keterangan</th>
                        <th class="border px-3 py-2 text-right">Jumlah Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                    <tr>
                        <td class="border px-3 py-2 text-center font-bold"><?= e($row['kode']) ?></td>
                        <td class="border px-3 py-2"><?= e($row['nama']) ?></td>
                        <td class="border px-3 py-2 text-right"><?= e($row['jumlah']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($iterations as $it): $iter = $it['iterasi']; ?>
    <div class="bg-white border rounded-xl shadow-sm p-6 mb-8">
        <h3 class="font-bold text-lg mb-4">Iterasi <?= e($iter) ?></h3>

        <h4 class="font-semibold mb-2">Centroid yang Digunakan</h4>
        <div class="overflow-x-auto mb-6">
            <table class="w-full text-sm border">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-3 py-2">Cluster</th>
                        <th class="border px-3 py-2">Akademik</th>
                        <th class="border px-3 py-2">Ekstra</th>
                        <th class="border px-3 py-2">Absensi</th>
                        <th class="border px-3 py-2">Tugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($centroidsByIter[$iter] ?? []) as $c): ?>
                    <tr>
                        <td class="border px-3 py-2 text-center font-bold"><?= e($c['kode']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($c['centroid_akademik']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($c['centroid_ekstrakurikuler']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($c['centroid_absensi']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($c['centroid_tugas']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h4 class="font-semibold mb-2">Detail Jarak Setiap Siswa</h4>
        <div class="overflow-x-auto max-h-[650px]">
            <table class="w-full text-xs border">
                <thead class="bg-slate-50 sticky top-0">
                    <tr>
                        <th class="border px-2 py-2">No</th>
                        <th class="border px-2 py-2 text-left">Siswa</th>
                        <th class="border px-2 py-2">Akademik</th>
                        <th class="border px-2 py-2">Ekstra</th>
                        <th class="border px-2 py-2">Absensi</th>
                        <th class="border px-2 py-2">Tugas</th>
                        <?php foreach ($clusterHeaders as $ch): ?>
                            <th class="border px-2 py-2">Jarak <?= e($ch['kode']) ?></th>
                        <?php endforeach; ?>
                        <th class="border px-2 py-2">Jarak Terdekat</th>
                        <th class="border px-2 py-2">Cluster</th>
                        <th class="border px-2 py-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($assignmentsByIter[$iter] ?? []) as $i => $row): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="border px-2 py-2 text-center"><?= $i + 1 ?></td>
                        <td class="border px-2 py-2 font-medium"><?= e($row['nama']) ?></td>
                        <td class="border px-2 py-2 text-center"><?= e($row['nilai_akademik']) ?></td>
                        <td class="border px-2 py-2 text-center"><?= e($row['nilai_ekstrakurikuler']) ?></td>
                        <td class="border px-2 py-2 text-center"><?= e($row['absensi']) ?></td>
                        <td class="border px-2 py-2 text-center"><?= e($row['nilai_tugas']) ?></td>
                        <?php foreach ($clusterHeaders as $ch): ?>
                            <td class="border px-2 py-2 text-center"><?= format_number($distancesByAssignment[$row['id']][$ch['kode']] ?? 0) ?></td>
                        <?php endforeach; ?>
                        <td class="border px-2 py-2 text-center font-semibold"><?= format_number($row['jarak_terdekat']) ?></td>
                        <td class="border px-2 py-2 text-center font-bold"><?= e($row['kode']) ?></td>
                        <td class="border px-2 py-2"><?= e($row['nama_cluster']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
