<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$message = '';
$messageType = 'success';

// --- Update rule ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id = (int) ($_POST['id'] ?? 0);
    $dampak = trim($_POST['dampak_negatif'] ?? '');
    $saran  = trim($_POST['saran'] ?? '');

    if ($id && $dampak !== '' && $saran !== '') {
        $update = $pdo->prepare('UPDATE game_impact_rules SET dampak_negatif = :dampak, saran = :saran WHERE id = :id');
        $update->execute(['dampak' => $dampak, 'saran' => $saran, 'id' => $id]);
        $message = 'Rule berhasil diperbarui.';
    } else {
        $message = 'Dampak negatif dan saran wajib diisi.';
        $messageType = 'error';
    }
}

// --- Tambah rule genre baru ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $genre  = trim($_POST['genre'] ?? '');
    $dampak = trim($_POST['dampak_negatif'] ?? '');
    $saran  = trim($_POST['saran'] ?? '');

    if ($genre === '' || $dampak === '' || $saran === '') {
        $message = 'Semua kolom wajib diisi.';
        $messageType = 'error';
    } else {
        $check = $pdo->prepare('SELECT id FROM game_impact_rules WHERE genre = :genre');
        $check->execute(['genre' => $genre]);
        if ($check->fetch()) {
            $message = 'Genre itu sudah punya rule. Silakan edit yang sudah ada.';
            $messageType = 'error';
        } else {
            $insert = $pdo->prepare('INSERT INTO game_impact_rules (genre, dampak_negatif, saran) VALUES (:genre, :dampak, :saran)');
            $insert->execute(['genre' => $genre, 'dampak' => $dampak, 'saran' => $saran]);
            $message = 'Rule genre baru berhasil ditambahkan.';
        }
    }
}

$rules = $pdo->query('SELECT * FROM game_impact_rules ORDER BY (genre = "Lainnya") ASC, genre ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rule Dampak & Saran</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen pb-10">

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-5">

    <h1 class="text-lg font-bold text-slate-800 mb-1">Rule Dampak & Saran per Genre</h1>
    <p class="text-xs text-slate-400 mb-4">Setiap poin dampak/saran ditulis di baris baru. Poin ini yang akan ditampilkan ke siswa di halaman hasil analisis.</p>

    <?php if ($message): ?>
    <div class="<?= $messageType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700' ?> border text-sm rounded-lg p-3 mb-4">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- List rule yang sudah ada -->
    <div class="space-y-4 mb-6">
        <?php foreach ($rules as $rule): ?>
        <details class="bg-white rounded-2xl shadow-sm p-4" <?= ($_GET['open'] ?? '') === (string) $rule['id'] ? 'open' : '' ?>>
            <summary class="text-sm font-semibold text-slate-800 cursor-pointer">
                <?= htmlspecialchars($rule['genre']) ?>
            </summary>

            <form method="POST" class="mt-3 space-y-3">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $rule['id'] ?>">

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Dampak Negatif (1 baris = 1 poin)</label>
                    <textarea name="dampak_negatif" rows="4"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($rule['dampak_negatif']) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Saran Mengatasi (1 baris = 1 poin)</label>
                    <textarea name="saran" rows="4"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($rule['saran']) ?></textarea>
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg px-4 py-2">
                    Simpan Perubahan
                </button>
            </form>
        </details>
        <?php endforeach; ?>
    </div>

    <!-- Tambah genre rule baru -->
    <div class="bg-white rounded-2xl shadow-sm p-4">
        <p class="text-sm font-semibold text-slate-800 mb-3">+ Tambah Rule Genre Baru</p>
        <form method="POST" class="space-y-3">
            <input type="hidden" name="action" value="add">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Nama Genre</label>
                <input type="text" name="genre" required placeholder="Contoh: Music/Rhythm"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Dampak Negatif (1 baris = 1 poin)</label>
                <textarea name="dampak_negatif" rows="3" required
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Saran Mengatasi (1 baris = 1 poin)</label>
                <textarea name="saran" rows="3" required
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg px-4 py-2">
                Tambah Rule
            </button>
        </form>
    </div>

</div>

</body>
</html>
