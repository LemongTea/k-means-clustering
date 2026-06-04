<?php
require_role(['admin']);
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
$siswa = $pdo->query("SELECT * FROM siswa ORDER BY id ASC")->fetchAll();
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl shadow-sm p-6">
        <h3 class="font-bold text-lg mb-4"><?= $edit ? 'Edit Data Siswa' : 'Tambah Data Siswa' ?></h3>
        <form method="post" action="actions/save_siswa.php" class="space-y-4">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Peserta Didik</label>
                <input type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">NISN</label>
                <input type="text" name="nisn" value="<?= e($edit['nisn'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2" placeholder="Boleh kosong">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai Akademik</label>
                <input type="number" step="0.01" name="nilai_akademik" required value="<?= e($edit['nilai_akademik'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Keaktifan / Ekstrakurikuler</label>
                <input type="number" step="0.01" name="nilai_ekstrakurikuler" required value="<?= e($edit['nilai_ekstrakurikuler'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Tingkat Kehadiran / Absensi</label>
                <input type="number" step="0.01" name="absensi" required value="<?= e($edit['absensi'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
                <p class="text-xs text-slate-500 mt-1">Sesuai PDF, nilai ini adalah hasil konversi: 90%-100%=3, 75%-89%=2, <75%=1.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai Tugas / Praktik</label>
                <input type="number" step="0.01" name="nilai_tugas" required value="<?= e($edit['nilai_tugas'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <button class="w-full bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700">Simpan</button>
            <?php if ($edit): ?>
                <a href="index.php?page=siswa" class="block text-center text-sm text-slate-500">Batal edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white border rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg">Data Siswa</h3>
            <span class="text-sm text-slate-500">Total: <?= count($siswa) ?> siswa</span>
        </div>
        <div class="overflow-x-auto max-h-[650px]">
            <table class="w-full text-sm border">
                <thead class="bg-slate-50 sticky top-0">
                    <tr>
                        <th class="border px-3 py-2">No</th>
                        <th class="border px-3 py-2 text-left">Nama Peserta Didik</th>
                        <th class="border px-3 py-2">NISN</th>
                        <th class="border px-3 py-2">Akademik</th>
                        <th class="border px-3 py-2">Ekstra</th>
                        <th class="border px-3 py-2">Absensi</th>
                        <th class="border px-3 py-2">Tugas</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($siswa as $i => $row): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="border px-3 py-2 text-center"><?= $i + 1 ?></td>
                        <td class="border px-3 py-2 font-medium"><?= e($row['nama']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= e($row['nisn'] ?? '') ?></td>
                        <td class="border px-3 py-2 text-center"><?= e($row['nilai_akademik']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= e($row['nilai_ekstrakurikuler']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= e($row['absensi']) ?></td>
                        <td class="border px-3 py-2 text-center"><?= e($row['nilai_tugas']) ?></td>
                        <td class="border px-3 py-2 text-center whitespace-nowrap">
                            <a href="index.php?page=siswa&edit=<?= e($row['id']) ?>" class="text-blue-600 hover:underline">Edit</a>
                            <a href="actions/delete_siswa.php?id=<?= e($row['id']) ?>" onclick="return confirm('Hapus data siswa ini?')" class="text-red-600 hover:underline ml-2">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
