<?php
require_role(['admin', 'pimpinan']);
$runs = $pdo->query("SELECT * FROM kmeans_runs ORDER BY id DESC")->fetchAll();
$selectedRunId = $_GET['run_id'] ?? ($runs[0]['id'] ?? null);
$selectedIterasi = $_GET['iterasi'] ?? 'all';
$mode = $_GET['mode'] ?? 'jarak';

$iterations = [];
$centroidsByIter = [];
$assignmentsByIter = [];
$distancesByAssignment = [];
$clusterHeaders = [];
$summary = [];
$selectedRun = null;

if (!function_exists('formula_euclidean_html')) {
    function formula_euclidean_html(array $siswa, array $centroid): string {
        $a = (float)$siswa['nilai_akademik'];
        $e = (float)$siswa['nilai_ekstrakurikuler'];
        $ab = (float)$siswa['absensi'];
        $t = (float)$siswa['nilai_tugas'];
        $ca = (float)$centroid['centroid_akademik'];
        $ce = (float)$centroid['centroid_ekstrakurikuler'];
        $cab = (float)$centroid['centroid_absensi'];
        $ct = (float)$centroid['centroid_tugas'];
        $hasil = sqrt(pow($a - $ca, 2) + pow($e - $ce, 2) + pow($ab - $cab, 2) + pow($t - $ct, 2));
        return '√((' . format_number($a, 3) . '−' . format_number($ca, 3) . ')² + (' .
            format_number($e, 3) . '−' . format_number($ce, 3) . ')² + (' .
            format_number($ab, 3) . '−' . format_number($cab, 3) . ')² + (' .
            format_number($t, 3) . '−' . format_number($ct, 3) . ')²) = <strong>' . format_number($hasil) . '</strong>';
    }
}

if ($selectedRunId) {
    $stmt = $pdo->prepare("SELECT * FROM kmeans_runs WHERE id = ?");
    $stmt->execute([$selectedRunId]);
    $selectedRun = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT DISTINCT iterasi FROM kmeans_assignments WHERE run_id = ? ORDER BY iterasi ASC");
    $stmt->execute([$selectedRunId]);
    $iterations = $stmt->fetchAll();

    if ($selectedIterasi !== 'all') {
        $iterations = array_values(array_filter($iterations, fn($it) => (string)$it['iterasi'] === (string)$selectedIterasi));
    }

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
        $centroidsByIter[$row['iterasi'] . '_' . $row['kode']] = $row;
    }

    $stmt = $pdo->prepare("SELECT a.*, s.nama, s.nisn, s.nilai_akademik, s.nilai_ekstrakurikuler, s.absensi, s.nilai_tugas, c.kode, c.nama AS nama_cluster
        FROM kmeans_assignments a
        JOIN siswa s ON s.id = a.siswa_id
        JOIN clusters c ON c.id = a.cluster_id
        WHERE a.run_id = ?
        ORDER BY a.iterasi ASC, s.id ASC");
    $stmt->execute([$selectedRunId]);
    foreach ($stmt->fetchAll() as $row) {
        if ($selectedIterasi === 'all' || (string)$row['iterasi'] === (string)$selectedIterasi) {
            $assignmentsByIter[$row['iterasi']][] = $row;
        }
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
<style>
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        aside, main > header, .no-print { display: none !important; }
        body { background: #fff !important; font-size: 11px; }
        main, section { padding: 0 !important; margin: 0 !important; }
        .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        .page-break { page-break-after: always; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        .text-xs { font-size: 10px !important; }
    }
</style>

<div class="bg-white border rounded-xl shadow-sm p-6 mb-6 no-print">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold">Cetak Laporan Perhitungan K-Means</h3>
            <p class="text-sm text-slate-500">Laporan ini menampilkan centroid, jarak Euclidean, hasil cluster, dan bisa dicetak per iterasi.</p>
        </div>
        <button onclick="window.print()" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">Cetak Laporan</button>
    </div>

    <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-5">
        <input type="hidden" name="page" value="laporan">
        <div>
            <label class="block text-sm font-medium mb-1">Pilih Proses</label>
            <select name="run_id" class="w-full border rounded-lg px-3 py-2">
                <?php foreach ($runs as $run): ?>
                    <option value="<?= e($run['id']) ?>" <?= $selectedRunId == $run['id'] ? 'selected' : '' ?>>
                        Proses #<?= e($run['id']) ?> - <?= e($run['tanggal_proses']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Pilih Iterasi</label>
            <select name="iterasi" class="w-full border rounded-lg px-3 py-2">
                <option value="all" <?= $selectedIterasi === 'all' ? 'selected' : '' ?>>Semua Iterasi</option>
                <?php if ($selectedRunId):
                    $allIterStmt = $pdo->prepare("SELECT DISTINCT iterasi FROM kmeans_assignments WHERE run_id = ? ORDER BY iterasi ASC");
                    $allIterStmt->execute([$selectedRunId]);
                    foreach ($allIterStmt->fetchAll() as $itOption): ?>
                        <option value="<?= e($itOption['iterasi']) ?>" <?= (string)$selectedIterasi === (string)$itOption['iterasi'] ? 'selected' : '' ?>>Iterasi <?= e($itOption['iterasi']) ?></option>
                    <?php endforeach; endif; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Mode Laporan</label>
            <select name="mode" class="w-full border rounded-lg px-3 py-2">
                <option value="jarak" <?= $mode === 'jarak' ? 'selected' : '' ?>>Tabel Jarak</option>
                <option value="rumus" <?= $mode === 'rumus' ? 'selected' : '' ?>>Detail Rumus</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">Tampilkan</button>
        </div>
    </form>
</div>

<?php if (!$selectedRunId): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-yellow-800">Belum ada hasil proses. Silakan jalankan proses K-Means terlebih dahulu.</div>
<?php else: ?>
<div class="bg-white border rounded-xl shadow-sm p-6 print-card">
    <div class="text-center mb-6 border-b pb-4">
        <h1 class="text-xl font-bold uppercase">Laporan Perhitungan K-Means Clustering</h1>
        <h2 class="text-base font-semibold">Analisis Kemajuan Belajar Siswa SMK Negeri 4 Kerinci</h2>
        <p class="text-sm text-slate-600 mt-1">Proses #<?= e($selectedRun['id'] ?? '') ?> | Tanggal: <?= e($selectedRun['tanggal_proses'] ?? '') ?> | Status: <?= e($selectedRun['status'] ?? '') ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="font-bold mb-2">Rumus Euclidean Distance</h3>
            <div class="border rounded-lg p-3 text-sm bg-slate-50">
                d = √((x₁-c₁)² + (x₂-c₂)² + (x₃-c₃)² + (x₄-c₄)²)<br>
                x = data siswa, c = centroid cluster.
            </div>
        </div>
        <div>
            <h3 class="font-bold mb-2">Ringkasan Cluster Akhir</h3>
            <table class="w-full text-sm border">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-2 py-1">Cluster</th>
                        <th class="border px-2 py-1 text-left">Keterangan</th>
                        <th class="border px-2 py-1">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                    <tr>
                        <td class="border px-2 py-1 text-center font-bold"><?= e($row['kode']) ?></td>
                        <td class="border px-2 py-1"><?= e($row['nama']) ?></td>
                        <td class="border px-2 py-1 text-center"><?= e($row['jumlah']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php foreach ($iterations as $indexIter => $it): $iter = $it['iterasi']; ?>
        <div class="<?= $indexIter < count($iterations)-1 ? 'page-break' : '' ?> mb-8">
            <h3 class="font-bold text-lg mb-3">Iterasi <?= e($iter) ?></h3>

            <h4 class="font-semibold mb-2">A. Centroid yang Digunakan</h4>
            <table class="w-full text-xs border mb-5">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-2 py-1">Cluster</th>
                        <th class="border px-2 py-1">Keterangan</th>
                        <th class="border px-2 py-1">Nilai Akademik</th>
                        <th class="border px-2 py-1">Ekstrakurikuler</th>
                        <th class="border px-2 py-1">Absensi</th>
                        <th class="border px-2 py-1">Nilai Tugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($centroidsByIter[$iter] ?? []) as $c): ?>
                    <tr>
                        <td class="border px-2 py-1 text-center font-bold"><?= e($c['kode']) ?></td>
                        <td class="border px-2 py-1"><?= e($c['nama']) ?></td>
                        <td class="border px-2 py-1 text-center"><?= format_number($c['centroid_akademik']) ?></td>
                        <td class="border px-2 py-1 text-center"><?= format_number($c['centroid_ekstrakurikuler']) ?></td>
                        <td class="border px-2 py-1 text-center"><?= format_number($c['centroid_absensi']) ?></td>
                        <td class="border px-2 py-1 text-center"><?= format_number($c['centroid_tugas']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($mode === 'rumus'): ?>
                <h4 class="font-semibold mb-2">B. Detail Rumus Perhitungan Jarak</h4>
                <table class="w-full text-xs border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border px-2 py-1">No</th>
                            <th class="border px-2 py-1 text-left">Siswa</th>
                            <th class="border px-2 py-1">Data</th>
                            <?php foreach ($clusterHeaders as $ch): ?>
                                <th class="border px-2 py-1 text-left">Rumus <?= e($ch['kode']) ?></th>
                            <?php endforeach; ?>
                            <th class="border px-2 py-1">Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($assignmentsByIter[$iter] ?? []) as $i => $row): ?>
                        <tr>
                            <td class="border px-2 py-1 text-center"><?= $i + 1 ?></td>
                            <td class="border px-2 py-1"><?= e($row['nama']) ?></td>
                            <td class="border px-2 py-1 text-center">(<?= format_number($row['nilai_akademik']) ?>, <?= format_number($row['nilai_ekstrakurikuler']) ?>, <?= format_number($row['absensi']) ?>, <?= format_number($row['nilai_tugas']) ?>)</td>
                            <?php foreach ($clusterHeaders as $ch): $centroid = $centroidsByIter[$iter . '_' . $ch['kode']] ?? null; ?>
                                <td class="border px-2 py-1 text-left"><?= $centroid ? formula_euclidean_html($row, $centroid) : '-' ?></td>
                            <?php endforeach; ?>
                            <td class="border px-2 py-1 text-center font-bold"><?= e($row['kode']) ?><br><span class="font-normal"><?= format_number($row['jarak_terdekat']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <h4 class="font-semibold mb-2">B. Tabel Jarak dan Hasil Cluster</h4>
                <table class="w-full text-xs border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="border px-2 py-1">No</th>
                            <th class="border px-2 py-1 text-left">Nama Siswa</th>
                            <th class="border px-2 py-1">Akademik</th>
                            <th class="border px-2 py-1">Ekstra</th>
                            <th class="border px-2 py-1">Absensi</th>
                            <th class="border px-2 py-1">Tugas</th>
                            <?php foreach ($clusterHeaders as $ch): ?>
                                <th class="border px-2 py-1">Jarak <?= e($ch['kode']) ?></th>
                            <?php endforeach; ?>
                            <th class="border px-2 py-1">Jarak Min</th>
                            <th class="border px-2 py-1">Cluster</th>
                            <th class="border px-2 py-1 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($assignmentsByIter[$iter] ?? []) as $i => $row): ?>
                        <tr>
                            <td class="border px-2 py-1 text-center"><?= $i + 1 ?></td>
                            <td class="border px-2 py-1"><?= e($row['nama']) ?></td>
                            <td class="border px-2 py-1 text-center"><?= format_number($row['nilai_akademik']) ?></td>
                            <td class="border px-2 py-1 text-center"><?= format_number($row['nilai_ekstrakurikuler']) ?></td>
                            <td class="border px-2 py-1 text-center"><?= format_number($row['absensi']) ?></td>
                            <td class="border px-2 py-1 text-center"><?= format_number($row['nilai_tugas']) ?></td>
                            <?php foreach ($clusterHeaders as $ch): ?>
                                <td class="border px-2 py-1 text-center"><?= format_number($distancesByAssignment[$row['id']][$ch['kode']] ?? 0) ?></td>
                            <?php endforeach; ?>
                            <td class="border px-2 py-1 text-center font-semibold"><?= format_number($row['jarak_terdekat']) ?></td>
                            <td class="border px-2 py-1 text-center font-bold"><?= e($row['kode']) ?></td>
                            <td class="border px-2 py-1"><?= e($row['nama_cluster']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
