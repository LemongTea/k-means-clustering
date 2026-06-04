<?php
require_role(['admin']);
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, nama, username, role, status FROM users WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
$users = $pdo->query("SELECT id, nama, username, role, status, created_at FROM users ORDER BY id ASC")->fetchAll();
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white border rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4"><?= $edit ? 'Edit User' : 'Tambah User' ?></h3>
        <form method="post" action="actions/save_user.php" class="space-y-4">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
            <div>
                <label class="block text-sm font-medium mb-1">Nama</label>
                <input type="text" name="nama" value="<?= e($edit['nama'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" value="<?= e($edit['username'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password <?= $edit ? '<span class="text-xs text-slate-500">kosongkan jika tidak diubah</span>' : '' ?></label>
                <input type="password" name="password" <?= $edit ? '' : 'required' ?> class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" required class="w-full border rounded-lg px-3 py-2">
                    <option value="admin" <?= (($edit['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="pimpinan" <?= (($edit['role'] ?? '') === 'pimpinan') ? 'selected' : '' ?>>Pimpinan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" required class="w-full border rounded-lg px-3 py-2">
                    <option value="aktif" <?= (($edit['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= (($edit['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <button class="w-full bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700">Simpan</button>
            <?php if ($edit): ?>
                <a href="index.php?page=users" class="block text-center bg-slate-100 rounded-lg py-2 hover:bg-slate-200">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white border rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold mb-4">Data User</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border px-3 py-2 text-left">Nama</th>
                        <th class="border px-3 py-2 text-left">Username</th>
                        <th class="border px-3 py-2 text-left">Role</th>
                        <th class="border px-3 py-2 text-left">Status</th>
                        <th class="border px-3 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td class="border px-3 py-2"><?= e($row['nama']) ?></td>
                        <td class="border px-3 py-2"><?= e($row['username']) ?></td>
                        <td class="border px-3 py-2 capitalize"><?= e($row['role']) ?></td>
                        <td class="border px-3 py-2"><?= e($row['status']) ?></td>
                        <td class="border px-3 py-2 text-center whitespace-nowrap">
                            <a href="index.php?page=users&edit=<?= e($row['id']) ?>" class="text-blue-600 hover:underline">Edit</a>
                            <?php if ((int)$row['id'] !== (int)(current_user()['id'] ?? 0)): ?>
                                <a href="actions/delete_user.php?id=<?= e($row['id']) ?>" onclick="return confirm('Hapus user ini?')" class="text-red-600 hover:underline ml-3">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
