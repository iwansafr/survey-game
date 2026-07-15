<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, langsung ke dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$adminConfig = require __DIR__ . '/../config/admin.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $adminConfig['username'] && password_verify($password, $adminConfig['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center">

<div class="max-w-sm mx-auto px-4 w-full">
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-slate-800">Login Admin</h1>
        <p class="text-sm text-slate-500 mt-1">Survey Kebiasaan Bermain Game</p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-2xl shadow-sm p-5 space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
            <input type="text" name="username" required autofocus
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-2.5 text-sm transition">
            Masuk
        </button>
    </form>
</div>

</body>
</html>
