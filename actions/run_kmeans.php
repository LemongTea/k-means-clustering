<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_role_action(['admin', 'pimpinan']);
require_once __DIR__ . '/../includes/functions.php';

$maxIterasi = max(1, min(50, (int)($_POST['max_iterasi'] ?? 10)));
$siswa = $pdo->query("SELECT * FROM siswa ORDER BY id ASC")->fetchAll();
$clusters = $pdo->query("SELECT * FROM clusters ORDER BY kode ASC")->fetchAll();

if (count($siswa) === 0) {
    die('Data siswa belum ada.');
}

if (count($clusters) < 2) {
    die('Jumlah cluster minimal 2. Silakan tambah cluster terlebih dahulu.');
}

if (count($clusters) > count($siswa)) {
    die('Jumlah cluster tidak boleh lebih banyak dari jumlah siswa.');
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO kmeans_runs (tanggal_proses, status, max_iterasi, catatan) VALUES (NOW(), ?, ?, ?)");
    $stmt->execute(['DIPROSES', $maxIterasi, 'Proses K-Means Clustering dinamis berdasarkan jumlah cluster pada tabel clusters']);
    $runId = $pdo->lastInsertId();

    $currentCentroids = [];
    foreach ($clusters as $c) {
        $currentCentroids[$c['id']] = [
            'id' => (int)$c['id'],
            'kode' => $c['kode'],
            'centroid_akademik' => (float)$c['centroid_akademik'],
            'centroid_ekstrakurikuler' => (float)$c['centroid_ekstrakurikuler'],
            'centroid_absensi' => (float)$c['centroid_absensi'],
            'centroid_tugas' => (float)$c['centroid_tugas'],
        ];
    }

    $previousAssignments = [];
    $stopMessage = '';

    for ($iterasi = 1; $iterasi <= $maxIterasi; $iterasi++) {
        foreach ($currentCentroids as $centroid) {
            $stmt = $pdo->prepare("INSERT INTO kmeans_iterations
                (run_id, iterasi, cluster_id, centroid_akademik, centroid_ekstrakurikuler, centroid_absensi, centroid_tugas)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $runId,
                $iterasi,
                $centroid['id'],
                $centroid['centroid_akademik'],
                $centroid['centroid_ekstrakurikuler'],
                $centroid['centroid_absensi'],
                $centroid['centroid_tugas']
            ]);
        }

        $assignments = [];
        $members = [];
        foreach ($currentCentroids as $clusterId => $centroid) {
            $members[$clusterId] = [];
        }

        foreach ($siswa as $row) {
            $distances = [];
            foreach ($currentCentroids as $clusterId => $centroid) {
                $distances[$clusterId] = euclidean_distance($row, $centroid);
            }

            asort($distances);
            $nearestClusterId = (int)array_key_first($distances);
            $nearestDistance = (float)reset($distances);
            $assignments[$row['id']] = $nearestClusterId;
            $members[$nearestClusterId][] = $row;

            $stmt = $pdo->prepare("INSERT INTO kmeans_assignments
                (run_id, iterasi, siswa_id, cluster_id, jarak_terdekat)
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $runId,
                $iterasi,
                $row['id'],
                $nearestClusterId,
                $nearestDistance
            ]);
            $assignmentId = $pdo->lastInsertId();

            foreach ($currentCentroids as $clusterId => $centroid) {
                $stmt = $pdo->prepare("INSERT INTO kmeans_distances
                    (assignment_id, run_id, iterasi, siswa_id, cluster_id, jarak)
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $assignmentId,
                    $runId,
                    $iterasi,
                    $row['id'],
                    $clusterId,
                    $distances[$clusterId]
                ]);
            }
        }

        $newCentroids = [];
        foreach ($currentCentroids as $clusterId => $centroid) {
            $rows = $members[$clusterId];
            if (count($rows) === 0) {
                // Jika sebuah cluster kosong, centroid dipertahankan agar proses tetap berjalan.
                $newCentroids[$clusterId] = $centroid;
                continue;
            }

            $count = count($rows);
            $newCentroids[$clusterId] = [
                'id' => $centroid['id'],
                'kode' => $centroid['kode'],
                'centroid_akademik' => array_sum(array_column($rows, 'nilai_akademik')) / $count,
                'centroid_ekstrakurikuler' => array_sum(array_column($rows, 'nilai_ekstrakurikuler')) / $count,
                'centroid_absensi' => array_sum(array_column($rows, 'absensi')) / $count,
                'centroid_tugas' => array_sum(array_column($rows, 'nilai_tugas')) / $count,
            ];
        }

        $centroidStable = true;
        foreach ($currentCentroids as $clusterId => $centroid) {
            foreach (['centroid_akademik', 'centroid_ekstrakurikuler', 'centroid_absensi', 'centroid_tugas'] as $key) {
                if (abs($centroid[$key] - $newCentroids[$clusterId][$key]) > 0.0001) {
                    $centroidStable = false;
                    break 2;
                }
            }
        }

        $assignmentStable = ($previousAssignments === $assignments);
        $currentCentroids = $newCentroids;

        if ($centroidStable || $assignmentStable) {
            $stopMessage = "Konvergen pada iterasi ke-$iterasi";
            break;
        }

        $previousAssignments = $assignments;
    }

    foreach ($currentCentroids as $clusterId => $centroid) {
        $stmt = $pdo->prepare("UPDATE clusters SET centroid_akademik=?, centroid_ekstrakurikuler=?, centroid_absensi=?, centroid_tugas=? WHERE id=?");
        $stmt->execute([
            $centroid['centroid_akademik'],
            $centroid['centroid_ekstrakurikuler'],
            $centroid['centroid_absensi'],
            $centroid['centroid_tugas'],
            $clusterId
        ]);
    }

    if ($stopMessage === '') {
        $stopMessage = "Berhenti karena mencapai maksimal iterasi $maxIterasi";
    }

    $stmt = $pdo->prepare("UPDATE kmeans_runs SET status=?, catatan=? WHERE id=?");
    $stmt->execute(['SELESAI', $stopMessage, $runId]);

    $pdo->commit();
    header("Location: ../index.php?page=hasil&run_id=$runId");
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    die('Proses gagal: ' . $e->getMessage());
}
