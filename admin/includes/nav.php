<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    'dashboard.php' => 'Dashboard',
    'data.php'      => 'Data Responden',
    'games.php'     => 'Master Game',
    'rules.php'     => 'Rule Dampak',
];
?>
<div class="bg-white border-b border-slate-200 sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-800">Admin Panel</p>
            <p class="text-xs text-slate-400">Survey Kebiasaan Bermain Game</p>
        </div>
        <a href="logout.php" class="text-xs text-red-500 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50">Keluar</a>
    </div>
    <div class="max-w-5xl mx-auto px-4 flex gap-1 overflow-x-auto pb-2 -mt-1">
        <?php foreach ($navItems as $href => $label): ?>
            <a href="<?= $href ?>"
               class="whitespace-nowrap text-xs font-medium px-3 py-1.5 rounded-full <?= $currentPage === $href ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
