<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Ambil master opsi dampak self-report untuk checkbox
$stmt = $pdo->query('SELECT id, label FROM impact_options ORDER BY id ASC');
$impactOptions = $stmt->fetchAll();

$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Survey Kebiasaan Bermain Game - Siswa SMK</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">

<div class="max-w-md mx-auto px-4 py-6 sm:max-w-lg">

    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-slate-800">Survey Kebiasaan Bermain Game</h1>
        <p class="text-sm text-slate-500 mt-1">Isi dengan jujur ya, hasilnya bakal dianalisis khusus buat kamu 🎮</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-4">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form action="submit.php" method="POST" id="survey-form" class="bg-white rounded-2xl shadow-sm p-5 space-y-5">

        <!-- Biodata -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
            <input type="text" name="nama" required
                   value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Contoh: Andi Pratama">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
            <input type="text" name="kelas" required
                   value="<?= htmlspecialchars($old['kelas'] ?? '') ?>"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Contoh: XI PPLG 1">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Kelamin</label>
            <div class="flex gap-3">
                <label class="flex-1 flex items-center justify-center gap-2 border border-slate-300 rounded-lg py-2.5 text-sm cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="jenis_kelamin" value="L" required class="accent-indigo-600">
                    Laki-laki
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 border border-slate-300 rounded-lg py-2.5 text-sm cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="jenis_kelamin" value="P" required class="accent-indigo-600">
                    Perempuan
                </label>
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Game favorit -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Game Online Favorit</label>
            <div class="relative">
                <input type="text" id="game-search" autocomplete="off"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Ketik nama game, contoh: Mobile Legends">
                <div id="game-results" class="hidden absolute z-10 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"></div>
            </div>
            <input type="hidden" name="game_id" id="game_id" value="<?= htmlspecialchars($old['game_id'] ?? '') ?>">

            <div id="game-selected" class="hidden mt-2 items-center justify-between bg-indigo-50 text-indigo-700 text-sm rounded-lg px-3 py-2">
                <span id="game-selected-name"></span>
                <button type="button" id="game-clear" class="text-indigo-500 text-xs underline">Ganti</button>
            </div>

            <button type="button" id="toggle-manual" class="text-xs text-indigo-600 underline mt-2">
                Game tidak ada di daftar? Input manual
            </button>

            <div id="manual-wrapper" class="hidden mt-2">
                <input type="text" name="nama_game_manual" id="nama_game_manual"
                       value="<?= htmlspecialchars($old['nama_game_manual'] ?? '') ?>"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="Tulis nama game yang kamu mainkan">
                <p class="text-xs text-slate-400 mt-1">Game baru akan diverifikasi admin dulu sebelum masuk daftar.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Kenapa kamu suka main game itu?</label>
            <textarea name="alasan_suka" rows="3"
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Contoh: seru mainnya bareng teman, grafiknya bagus, dll"><?= htmlspecialchars($old['alasan_suka'] ?? '') ?></textarea>
        </div>

        <hr class="border-slate-100">

        <!-- Dampak self-report -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Apa saja yang kamu rasakan sendiri selama ini? <span class="text-slate-400 font-normal">(boleh pilih lebih dari satu)</span>
            </label>
            <div class="space-y-2">
                <?php foreach ($impactOptions as $opt): ?>
                <label class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                    <input type="checkbox" name="dampak_dirasakan[]" value="<?= (int)$opt['id'] ?>"
                           class="accent-indigo-600"
                           <?= in_array($opt['id'], $old['dampak_dirasakan'] ?? []) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($opt['label']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg py-3 text-sm transition">
            Kirim & Lihat Hasil Analisis
        </button>
    </form>

    <p class="text-center text-xs text-slate-400 mt-4">Data kamu hanya digunakan untuk keperluan analisis internal sekolah.</p>
</div>

<script src="assets/js/survey.js"></script>
</body>
</html>
