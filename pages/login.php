<?php
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - K-Means Siswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white border rounded-2xl shadow-sm p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-blue-700">Login Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Aplikasi K-Means Kemajuan Belajar Siswa</p>
        </div>

        <?php if ($error === 'invalid'): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Username atau password salah.</div>
        <?php elseif ($error === 'logout'): ?>
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">Berhasil logout.</div>
        <?php endif; ?>

        <form method="post" action="actions/login.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" required autofocus class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white rounded-lg py-2.5 font-semibold hover:bg-blue-700">Masuk</button>
        </form>

        <div class="mt-6 rounded-lg bg-slate-50 border p-4 text-sm text-slate-600">
            <p class="font-semibold mb-2">Akun default:</p>
            <p>Admin: <strong>admin</strong> / <strong>admin123</strong></p>
            <p>Pimpinan: <strong>pimpinan</strong> / <strong>pimpinan123</strong></p>
        </div>
    </div>
</body>
</html>
