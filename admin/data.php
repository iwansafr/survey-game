<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// --- Ambil opsi filter ---
$kelasOptions = $pdo->query('SELECT DISTINCT kelas FROM responses ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
$genreOptions = $pdo->query('SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre ASC')->fetchAll(PDO::FETCH_COLUMN);

// --- Ambil filter dari GET ---
$filterKelas = trim($_GET['kelas'] ?? '');
$filterGenre = trim($_GET['genre'] ?? '');
$filterQ     = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if ($filterKelas !== '') {
    $where[] = 'r.kelas = :kelas';
    $params['kelas'] = $filterKelas;
}

if ($filterGenre !== '') {
    if ($filterGenre === '__belum__') {
        $where[] = 'g.genre IS NULL';
    } else {
        $where[] = 'g.genre = :genre';
        $params['genre'] = $filterGenre;
    }
}

if ($filterQ !== '') {
    $where[] = 'r.nama LIKE :q';
    $params['q'] = '%' . $filterQ . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT r.*, g.nama_game, g.genre, g.status AS game_status
        FROM responses r
        LEFT JOIN games g ON r.game_id = g.id
        {$whereSql}
        ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$responses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Responden</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen pb-10">

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-5">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-bold text-slate-800">Data Responden</h1>
        <span class="text-xs text-slate-400"><?= count($responses) ?> hasil</span>
    </div>

    <!-- Filter -->
    <form method="GET" class="bg-white rounded-2xl shadow-sm p-4 mb-4 grid sm:grid-cols-4 gap-3">
        <input type="text" name="q" value="<?= htmlspecialchars($filterQ) ?>" placeholder="Cari nama siswa..."
               class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">

        <select name="kelas" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Kelas</option>
            <?php foreach ($kelasOptions as $k): ?>
                <option value="<?= htmlspecialchars($k) ?>" <?= $filterKelas === $k ? 'selected' : '' ?>><?= htmlspecialchars($k) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="genre" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Genre</option>
            <?php foreach ($genreOptions as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>" <?= $filterGenre === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
            <option value="__belum__" <?= $filterGenre === '__belum__' ? 'selected' : '' ?>>Belum Diverifikasi</option>
        </select>

        <div class="sm:col-span-4 flex gap-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg px-4 py-2">Terapkan Filter</button>
            <a href="data.php" class="text-sm text-slate-500 rounded-lg px-4 py-2 border border-slate-200">Reset</a>
        </div>
    </form>

    <!-- List responden -->
    <div class="space-y-3">
        <?php foreach ($responses as $r): ?>
        <?php
            $gameName = $r['nama_game'] ?? $r['nama_game_manual'];
            $isPending = ($r['game_status'] === 'pending' || $r['genre'] === null);
        ?>
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($r['nama']) ?></p>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= htmlspecialchars($r['kelas']) ?> ·
                    <?= $r['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?> ·
                    <?= date('d M Y', strtotime($r['created_at'])) ?>
                </p>
                <p class="text-xs text-slate-600 mt-1 truncate">
                    🎮 <?= htmlspecialchars($gameName) ?>
                    <?php if ($isPending): ?>
                        <span class="ml-1 text-amber-600">(belum diverifikasi)</span>
                    <?php else: ?>
                        <span class="ml-1 text-slate-400">(<?= htmlspecialchars($r['genre']) ?>)</span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="../result.php?id=<?= $r['id'] ?>" target="_blank"
               class="whitespace-nowrap text-xs font-medium text-indigo-600 border border-indigo-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50">
                Lihat Hasil
            </a>
        </div>
        <?php endforeach; ?>

        <?php if (empty($responses)): ?>
        <div class="bg-white rounded-2xl shadow-sm p-8 text-center text-sm text-slate-400">
            Tidak ada data yang cocok dengan filter.
        </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
