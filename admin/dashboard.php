<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// --- Statistik ringkas ---
$totalResponden = $pdo->query('SELECT COUNT(*) c FROM responses')->fetch()['c'];
$totalPendingGames = $pdo->query("SELECT COUNT(*) c FROM games WHERE status = 'pending'")->fetch()['c'];
$totalGamesTerdaftar = $pdo->query('SELECT COUNT(*) c FROM games')->fetch()['c'];

// --- Distribusi jenis kelamin ---
$genderStmt = $pdo->query('SELECT jenis_kelamin, COUNT(*) c FROM responses GROUP BY jenis_kelamin');
$genderData = ['L' => 0, 'P' => 0];
foreach ($genderStmt->fetchAll() as $row) {
    $genderData[$row['jenis_kelamin']] = (int) $row['c'];
}

// --- Distribusi kelas ---
$kelasStmt = $pdo->query('SELECT kelas, COUNT(*) c FROM responses GROUP BY kelas ORDER BY c DESC');
$kelasData = $kelasStmt->fetchAll();

// --- Genre game terpopuler ---
$genreStmt = $pdo->query(
    "SELECT COALESCE(g.genre, 'Belum Diverifikasi') AS genre, COUNT(*) c
     FROM responses r
     LEFT JOIN games g ON r.game_id = g.id
     GROUP BY COALESCE(g.genre, 'Belum Diverifikasi')
     ORDER BY c DESC"
);
$genreData = $genreStmt->fetchAll();

// --- Top 5 game favorit ---
$topGamesStmt = $pdo->query(
    "SELECT COALESCE(g.nama_game, r.nama_game_manual) AS nama, COUNT(*) c
     FROM responses r
     LEFT JOIN games g ON r.game_id = g.id
     GROUP BY COALESCE(g.nama_game, r.nama_game_manual)
     ORDER BY c DESC
     LIMIT 5"
);
$topGames = $topGamesStmt->fetchAll();

// --- Dampak yang paling sering dirasakan sendiri ---
$topImpactStmt = $pdo->query(
    "SELECT io.label, COUNT(*) c
     FROM response_impacts ri
     JOIN impact_options io ON ri.impact_option_id = io.id
     GROUP BY io.label
     ORDER BY c DESC
     LIMIT 5"
);
$topImpacts = $topImpactStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="bg-slate-50 min-h-screen pb-10">

<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-5">

    <?php if ($totalPendingGames > 0): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-xl p-3 mb-4 flex items-center justify-between gap-3">
        <span>⚠️ Ada <strong><?= $totalPendingGames ?></strong> game hasil input manual siswa yang belum diverifikasi genre-nya.</span>
        <a href="games.php" class="whitespace-nowrap underline text-xs font-medium">Verifikasi sekarang</a>
    </div>
    <?php endif; ?>

    <!-- Kartu statistik -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <p class="text-xs text-slate-400">Total Responden</p>
            <p class="text-2xl font-bold text-slate-800 mt-1"><?= $totalResponden ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <p class="text-xs text-slate-400">Game Terdaftar</p>
            <p class="text-2xl font-bold text-slate-800 mt-1"><?= $totalGamesTerdaftar ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 col-span-2 sm:col-span-1">
            <p class="text-xs text-slate-400">Menunggu Verifikasi</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?= $totalPendingGames ?></p>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <!-- Chart jenis kelamin -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-800 mb-3">Distribusi Jenis Kelamin</p>
            <canvas id="chartGender" height="200"></canvas>
        </div>

        <!-- Chart genre -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-800 mb-3">Genre Game Terpopuler</p>
            <canvas id="chartGenre" height="200"></canvas>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 mb-4">
        <!-- Chart kelas -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-800 mb-3">Jumlah Responden per Kelas</p>
            <canvas id="chartKelas" height="220"></canvas>
        </div>

        <!-- Top game -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-sm font-semibold text-slate-800 mb-3">Top 5 Game Favorit</p>
            <ul class="space-y-2">
                <?php foreach ($topGames as $i => $g): ?>
                <li class="flex items-center justify-between text-sm">
                    <span class="text-slate-600"><?= ($i + 1) ?>. <?= htmlspecialchars($g['nama']) ?></span>
                    <span class="text-slate-800 font-medium"><?= $g['c'] ?></span>
                </li>
                <?php endforeach; ?>
                <?php if (empty($topGames)): ?>
                <li class="text-sm text-slate-400">Belum ada data.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Top dampak dirasakan -->
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <p class="text-sm font-semibold text-slate-800 mb-3">Dampak yang Paling Sering Dirasakan Siswa</p>
        <ul class="space-y-2">
            <?php foreach ($topImpacts as $imp): ?>
            <li class="flex items-center justify-between text-sm">
                <span class="text-slate-600"><?= htmlspecialchars($imp['label']) ?></span>
                <span class="bg-red-50 text-red-600 font-medium text-xs px-2 py-0.5 rounded-full"><?= $imp['c'] ?> siswa</span>
            </li>
            <?php endforeach; ?>
            <?php if (empty($topImpacts)): ?>
            <li class="text-sm text-slate-400">Belum ada data.</li>
            <?php endif; ?>
        </ul>
    </div>

</div>

<script>
const genderData = <?= json_encode($genderData) ?>;
const genreData  = <?= json_encode($genreData) ?>;
const kelasData  = <?= json_encode($kelasData) ?>;

new Chart(document.getElementById('chartGender'), {
    type: 'doughnut',
    data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
            data: [genderData.L, genderData.P],
            backgroundColor: ['#6366f1', '#f472b6']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('chartGenre'), {
    type: 'bar',
    data: {
        labels: genreData.map(g => g.genre),
        datasets: [{
            label: 'Jumlah Siswa',
            data: genreData.map(g => g.c),
            backgroundColor: '#6366f1',
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

new Chart(document.getElementById('chartKelas'), {
    type: 'bar',
    data: {
        labels: kelasData.map(k => k.kelas),
        datasets: [{
            label: 'Jumlah Siswa',
            data: kelasData.map(k => k.c),
            backgroundColor: '#818cf8',
            borderRadius: 6
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

</body>
</html>