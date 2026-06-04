<?php
require_role(['admin']);
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clusters WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
$clusters = $pdo->query("SELECT * FROM clusters ORDER BY kode ASC")->fetchAll();
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-lg mb-4"><?= $edit ? 'Edit Cluster' : 'Tambah Cluster' ?></h3>
        <form method="post" action="actions/save_cluster.php" class="space-y-4">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
            <div>
                <label class="block text-sm font-medium mb-1">Kode Cluster</label>
                <input type="text" name="kode" placeholder="C1" required value="<?= e($edit['kode'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama Cluster</label>
                <input type="text" name="nama" placeholder="Penurunan Tinggi" required value="<?= e($edit['nama'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Centroid Akademik</label>
                    <input type="number" step="0.01" name="centroid_akademik" required value="<?= e($edit['centroid_akademik'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Centroid Ekstra</label>
                    <input type="number" step="0.01" name="centroid_ekstrakurikuler" required value="<?= e($edit['centroid_ekstrakurikuler'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Centroid Kehadiran</label>
                    <input type="number" step="0.01" name="centroid_absensi" required value="<?= e($edit['centroid_absensi'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Centroid Tugas/Praktik</label>
                    <input type="number" step="0.01" name="centroid_tugas" required value="<?= e($edit['centroid_tugas'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <button class="w-full bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700">Simpan Cluster</button>
            <?php if ($edit): ?>
                <a href="index.php?page=clusters" class="block text-center text-sm text-slate-500">Batal edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white border rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-lg mb-4">Data Cluster dan Centroid</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-3 py-2">Kode</th>
                        <th class="border px-3 py-2 text-left">Nama</th>
                        <th class="border px-3 py-2">Akademik</th>
                        <th class="border px-3 py-2">Ekstra</th>
                        <th class="border px-3 py-2">Absensi</th>
                        <th class="border px-3 py-2">Tugas</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clusters as $row): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="border px-3 py-2 text-center font-bold"><?= e($row['kode']) ?></td>
                        <td class="border px-3 py-2"><?= e($row['nama']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($row['centroid_akademik'], 2) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($row['centroid_ekstrakurikuler'], 2) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($row['centroid_absensi'], 2) ?></td>
                        <td class="border px-3 py-2 text-center"><?= format_number($row['centroid_tugas'], 2) ?></td>
                        <td class="border px-3 py-2 text-center whitespace-nowrap">
                            <a href="index.php?page=clusters&edit=<?= e($row['id']) ?>" class="text-blue-600 hover:underline">Edit</a>
                            <a href="actions/delete_cluster.php?id=<?= e($row['id']) ?>" onclick="return confirm('Hapus cluster ini?')" class="text-red-600 hover:underline ml-2">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="text-sm text-slate-500 mt-4">Catatan: centroid pada halaman ini menjadi centroid awal saat proses K-Means dijalankan. Setelah proses selesai, centroid akan diperbarui menjadi centroid akhir.</p>
    </div>
</div>
