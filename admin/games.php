<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$genreList = ['FPS/Battle Royale', 'MOBA', 'RPG', 'Simulation/Sandbox', 'Sports/Racing', 'Puzzle/Casual', 'Strategy', 'Fighting', 'Lainnya'];

$message = '';
$messageType = 'success';

// --- Tambah game baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $nama  = trim($_POST['nama_game'] ?? '');
    $genre = trim($_POST['genre'] ?? '');

    if ($nama === '' || $genre === '') {
        $message = 'Nama game dan genre wajib diisi.';
        $messageType = 'error';
    } else {
        $check = $pdo->prepare('SELECT id FROM games WHERE nama_game = :nama');
        $check->execute(['nama' => $nama]);
        if ($check->fetch()) {
            $message = 'Game dengan nama itu sudah ada.';
            $messageType = 'error';
        } else {
            $insert = $pdo->prepare('INSERT INTO games (nama_game, genre, status) VALUES (:nama, :genre, "verified")');
            $insert->execute(['nama' => $nama, 'genre' => $genre]);
            $message = 'Game baru berhasil ditambahkan.';
        }
    }
}

// --- Update / verifikasi genre game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id    = (int) ($_POST['id'] ?? 0);
    $genre = trim($_POST['genre'] ?? '');

    if ($id && $genre !== '') {
        $update = $pdo->prepare('UPDATE games SET genre = :genre, status = "verified" WHERE id = :id');
        $update->execute(['genre' => $genre, 'id' => $id]);
        $message = 'Genre game berhasil diperbarui.';
    }
}

// --- Hapus game ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        $delete = $pdo->prepare('DELETE FROM games WHERE id = :id');
        $delete->execute(['id' => $id]);
        $message = 'Game berhasil dihapus. Responden yang pernah memilih game ini otomatis jadi belum diverifikasi.';
    }
}

$games = $pdo->query('SELECT * FROM games ORDER BY (status = "pending") DESC, nama_game ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Master Data Game</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen pb-10">

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-5">

    <h1 class="text-lg font-bold text-slate-800 mb-4">Master Data Game</h1>

    <?php if ($message): ?>
    <div class="<?= $messageType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> border text-sm rounded-lg p-3 mb-4">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Form tambah game -->
    <form method="POST" class="bg-white rounded-2xl shadow-sm p-4 mb-5 grid sm:grid-cols-4 gap-3">
        <input type="hidden" name="action" value="add">
        <input type="text" name="nama_game" required placeholder="Nama game baru"
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="genre" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Pilih Genre</option>
            <?php foreach ($genreList as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg px-4 py-2">
            + Tambah Game
        </button>
    </form>

    <!-- List game -->
    <div class="space-y-3">
        <?php foreach ($games as $g): ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between <?= $g['status'] === 'pending' ? 'ring-2 ring-amber-200' : '' ?>">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($g['nama_game']) ?></p>
                <?php if ($g['status'] === 'pending'): ?>
                    <span class="inline-block mt-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Menunggu verifikasi</span>
                <?php else: ?>
                    <span class="inline-block mt-1 text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full"><?= htmlspecialchars($g['genre']) ?></span>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                    <select name="genre" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($genreList as $opt): ?>
                            <option value="<?= htmlspecialchars($opt) ?>" <?= $g['genre'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg px-3 py-1.5">Simpan</button>
                </form>

                <form method="POST" onsubmit="return confirm('Hapus game ini? Responden yang pernah pilih game ini akan jadi belum diverifikasi.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                    <button type="submit" class="text-xs font-medium text-red-500 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50">Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($games)): ?>
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center text-sm text-slate-400">Belum ada data game.</div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
