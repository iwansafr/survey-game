document.addEventListener('DOMContentLoaded', function () {
    const searchInput   = document.getElementById('game-search');
    const resultsBox    = document.getElementById('game-results');
    const gameIdInput   = document.getElementById('game_id');
    const selectedBox   = document.getElementById('game-selected');
    const selectedName  = document.getElementById('game-selected-name');
    const clearBtn      = document.getElementById('game-clear');
    const toggleManual  = document.getElementById('toggle-manual');
    const manualWrapper = document.getElementById('manual-wrapper');
    const manualInput   = document.getElementById('nama_game_manual');

    let debounceTimer = null;

    // --- Search AJAX dengan debounce ---
    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);

        if (q.length < 2) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch('search_game.php?q=' + encodeURIComponent(q))
                .then(res => res.json())
                .then(data => renderResults(data))
                .catch(() => {
                    resultsBox.classList.add('hidden');
                });
        }, 300);
    });

    function renderResults(data) {
        resultsBox.innerHTML = '';

        if (!data.length) {
            resultsBox.innerHTML = '<div class="px-3 py-2.5 text-sm text-slate-400">Game tidak ditemukan</div>';
            resultsBox.classList.remove('hidden');
            return;
        }

        data.forEach(game => {
            const item = document.createElement('div');
            item.className = 'px-3 py-2.5 text-sm hover:bg-indigo-50 cursor-pointer flex justify-between items-center';
            item.innerHTML = `<span>${escapeHtml(game.nama_game)}</span><span class="text-xs text-slate-400">${escapeHtml(game.genre ?? '')}</span>`;
            item.addEventListener('click', () => selectGame(game));
            resultsBox.appendChild(item);
        });

        resultsBox.classList.remove('hidden');
    }

    function selectGame(game) {
        gameIdInput.value = game.id;
        selectedName.textContent = game.nama_game + ' (' + game.genre + ')';
        selectedBox.classList.remove('hidden');
        selectedBox.classList.add('flex');

        searchInput.value = '';
        searchInput.classList.add('hidden');
        resultsBox.classList.add('hidden');
        resultsBox.innerHTML = '';

        // Kalau sudah pilih dari daftar, matikan mode manual
        manualWrapper.classList.add('hidden');
        manualInput.value = '';
    }

    clearBtn.addEventListener('click', function () {
        gameIdInput.value = '';
        selectedBox.classList.add('hidden');
        selectedBox.classList.remove('flex');
        searchInput.classList.remove('hidden');
        searchInput.value = '';
        searchInput.focus();
    });

    // --- Toggle input manual ---
    toggleManual.addEventListener('click', function () {
        const isHidden = manualWrapper.classList.contains('hidden');
        manualWrapper.classList.toggle('hidden');

        if (isHidden) {
            // Aktifkan mode manual: kosongkan pilihan dari daftar
            gameIdInput.value = '';
            selectedBox.classList.add('hidden');
            selectedBox.classList.remove('flex');
            searchInput.value = '';
            searchInput.classList.remove('hidden');
            manualInput.focus();
            toggleManual.textContent = 'Batal input manual';
        } else {
            manualInput.value = '';
            toggleManual.textContent = 'Game tidak ada di daftar? Input manual';
        }
    });

    // --- Validasi ringan sebelum submit ---
    document.getElementById('survey-form').addEventListener('submit', function (e) {
        const hasGameId = gameIdInput.value.trim() !== '';
        const hasManual = manualInput.value.trim() !== '';

        if (!hasGameId && !hasManual) {
            e.preventDefault();
            alert('Pilih game dari daftar atau isi nama game secara manual ya.');
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Klik di luar hasil pencarian -> tutup dropdown
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.add('hidden');
        }
    });
});
