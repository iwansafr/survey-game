<?php
session_start();
require_once __DIR__ . '/config/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit((string) $id)) {
    header('Location: index.php');
    exit;
}

// Ambil data response + info game
$stmt = $pdo->prepare(
    'SELECT r.*, g.nama_game, g.genre, g.status AS game_status
     FROM responses r
     LEFT JOIN games g ON r.game_id = g.id
     WHERE r.id = :id'
);
$stmt->execute(['id' => $id]);
$response = $stmt->fetch();

if (!$response) {
    header('Location: index.php');
    exit;
}

$gameName  = $response['nama_game'] ?? $response['nama_game_manual'];
$genre     = $response['genre'];
$isPending = ($response['game_status'] === 'pending' || $genre === null);

// Kalau game masih pending/belum ada genre, pakai rule fallback "Lainnya"
$ruleGenre = $isPending ? 'Lainnya' : $genre;

$ruleStmt = $pdo->prepare('SELECT dampak_negatif, saran FROM game_impact_rules WHERE genre = :genre');
$ruleStmt->execute(['genre' => $ruleGenre]);
$rule = $ruleStmt->fetch();

$dampakList = $rule ? array_filter(array_map('trim', explode("\n", $rule['dampak_negatif']))) : [];
$saranList  = $rule ? array_filter(array_map('trim', explode("\n", $rule['saran']))) : [];

// Dampak yang dirasakan sendiri oleh siswa (self-report)
$impactStmt = $pdo->prepare(
    'SELECT io.label
     FROM response_impacts ri
     JOIN impact_options io ON ri.impact_option_id = io.id
     WHERE ri.response_id = :id'
);
$impactStmt->execute(['id' => $id]);
$selfImpacts = array_column($impactStmt->fetchAll(), 'label');
$impactCount = count($selfImpacts);

// Tingkat kewaspadaan sederhana berdasarkan jumlah dampak yang dirasakan sendiri
if ($impactCount <= 1) {
    $level = ['label' => 'Ringan', 'badge' => 'bg-green-100 text-green-700', 'bar' => 'bg-green-500', 'width' => '30%'];
} elseif ($impactCount <= 3) {
    $level = ['label' => 'Perlu Diperhatikan', 'badge' => 'bg-yellow-100 text-yellow-700', 'bar' => 'bg-yellow-500', 'width' => '65%'];
} else {
    $level = ['label' => 'Perlu Perhatian Serius', 'badge' => 'bg-red-100 text-red-700', 'bar' => 'bg-red-500', 'width' => '90%'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hasil Analisis - <?= htmlspecialchars($response['nama']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="max-w-md mx-auto px-4 py-6 sm:max-w-lg">

    <div class="text-center mb-5">
        <div class="inline-flex items-center gap-1.5 bg-indigo-100 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full mb-2">
            ✨ Hasil Analisis AI
        </div>
        <h1 class="text-xl font-bold text-slate-800">Halo, <?= htmlspecialchars($response['nama']) ?>!</h1>
        <p class="text-sm text-slate-500 mt-1">Ini analisis kebiasaan bermain game kamu berdasarkan jawaban yang tadi diisi.</p>
    </div>

    <!-- Info game -->
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-xs text-slate-400 mb-1">Game favorit kamu</p>
        <p class="text-lg font-semibold text-slate-800"><?= htmlspecialchars($gameName) ?></p>
        <?php if (!$isPending): ?>
            <span class="inline-block mt-1 text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">Genre: <?= htmlspecialchars($genre) ?></span>
        <?php else: ?>
            <span class="inline-block mt-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Genre sedang diverifikasi admin</span>
        <?php endif; ?>
    </div>

    <!-- Tingkat kewaspadaan -->
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-slate-700">Tingkat Kewaspadaan</p>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= $level['badge'] ?>"><?= $level['label'] ?></span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div class="h-2 rounded-full <?= $level['bar'] ?>" style="width: <?= $level['width'] ?>"></div>
        </div>
        <p class="text-xs text-slate-400 mt-2">Berdasarkan jumlah hal yang kamu rasakan sendiri dari kebiasaan bermain game.</p>
    </div>

    <?php if (!empty($selfImpacts)): ?>
    <!-- Dampak yang dirasakan sendiri -->
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-sm font-semibold text-slate-800 mb-3">🧍 Yang kamu rasakan sendiri</p>
        <ul class="space-y-2">
            <?php foreach ($selfImpacts as $item): ?>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="text-red-400 mt-0.5">●</span>
                <span><?= htmlspecialchars($item) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Dampak negatif potensial dari genre game -->
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-sm font-semibold text-slate-800 mb-3">⚠️ Potensi Dampak Negatif</p>
        <?php if (!empty($dampakList)): ?>
        <ul class="space-y-2">
            <?php foreach ($dampakList as $item): ?>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="text-amber-400 mt-0.5">●</span>
                <span><?= htmlspecialchars($item) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-sm text-slate-400">Belum ada data untuk genre ini.</p>
        <?php endif; ?>
    </div>

    <!-- Saran mengatasi -->
    <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
        <p class="text-sm font-semibold text-slate-800 mb-3">💡 Saran Buat Kamu</p>
        <?php if (!empty($saranList)): ?>
        <ul class="space-y-2">
            <?php foreach ($saranList as $item): ?>
            <li class="flex items-start gap-2 text-sm text-slate-600">
                <span class="text-emerald-500 mt-0.5">●</span>
                <span><?= htmlspecialchars($item) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-sm text-slate-400">Belum ada saran untuk genre ini.</p>
        <?php endif; ?>
    </div>

    <?php if ($isPending): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 mb-4">
        Game yang kamu isi masih menunggu verifikasi admin, jadi analisis di atas masih bersifat umum. Nanti kalau sudah diverifikasi, hasilnya bisa lebih spesifik.
    </div>
    <?php endif; ?>

    <a href="index.php" class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-3 text-sm transition">
        Kembali ke Beranda
    </a>
</div>

</body>
</html>
