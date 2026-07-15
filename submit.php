<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// --- Ambil & bersihkan input ---
$nama            = trim($_POST['nama'] ?? '');
$kelas           = trim($_POST['kelas'] ?? '');
$jenis_kelamin   = trim($_POST['jenis_kelamin'] ?? '');
$game_id         = trim($_POST['game_id'] ?? '');
$nama_game_manual = trim($_POST['nama_game_manual'] ?? '');
$alasan_suka     = trim($_POST['alasan_suka'] ?? '');
$dampak_dirasakan = array_map('intval', $_POST['dampak_dirasakan'] ?? []);

// --- Validasi server-side ---
$errors = [];

if ($nama === '') {
    $errors[] = 'Nama wajib diisi.';
}
if ($kelas === '') {
    $errors[] = 'Kelas wajib diisi.';
}
if (!in_array($jenis_kelamin, ['L', 'P'], true)) {
    $errors[] = 'Jenis kelamin wajib dipilih.';
}
if ($game_id === '' && $nama_game_manual === '') {
    $errors[] = 'Pilih game dari daftar atau isi nama game secara manual.';
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old'] = [
        'nama' => $nama,
        'kelas' => $kelas,
        'game_id' => $game_id,
        'nama_game_manual' => $nama_game_manual,
        'alasan_suka' => $alasan_suka,
        'dampak_dirasakan' => $dampak_dirasakan,
    ];
    header('Location: index.php');
    exit;
}

// --- Simpan ke database ---
try {
    $pdo->beginTransaction();

    if ($game_id !== '') {
        // Pastikan game_id valid
        $check = $pdo->prepare('SELECT id FROM games WHERE id = :id');
        $check->execute(['id' => $game_id]);
        if (!$check->fetch()) {
            $game_id = ''; // fallback ke manual kalau ternyata id tidak valid
        }
    }

    if ($game_id === '' && $nama_game_manual !== '') {
        // Cek apakah nama game manual sudah pernah diinput sebelumnya (hindari duplikat)
        $existing = $pdo->prepare('SELECT id FROM games WHERE nama_game = :nama');
        $existing->execute(['nama' => $nama_game_manual]);
        $found = $existing->fetch();

        if ($found) {
            $game_id = $found['id'];
        } else {
            $insertGame = $pdo->prepare(
                'INSERT INTO games (nama_game, genre, status) VALUES (:nama, NULL, "pending")'
            );
            $insertGame->execute(['nama' => $nama_game_manual]);
            $game_id = $pdo->lastInsertId();
        }
    }

    $insertResponse = $pdo->prepare(
        'INSERT INTO responses (nama, kelas, jenis_kelamin, game_id, nama_game_manual, alasan_suka)
         VALUES (:nama, :kelas, :jk, :game_id, :manual, :alasan)'
    );
    $insertResponse->execute([
        'nama'   => $nama,
        'kelas'  => $kelas,
        'jk'     => $jenis_kelamin,
        'game_id' => $game_id !== '' ? $game_id : null,
        'manual' => $nama_game_manual !== '' ? $nama_game_manual : null,
        'alasan' => $alasan_suka !== '' ? $alasan_suka : null,
    ]);

    $responseId = $pdo->lastInsertId();

    if (!empty($dampak_dirasakan)) {
        $insertImpact = $pdo->prepare(
            'INSERT INTO response_impacts (response_id, impact_option_id) VALUES (:rid, :iid)'
        );
        foreach ($dampak_dirasakan as $impactId) {
            $insertImpact->execute(['rid' => $responseId, 'iid' => $impactId]);
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['form_errors'] = ['Terjadi kesalahan saat menyimpan data. Coba lagi ya.'];
    header('Location: index.php');
    exit;
}

header('Location: result.php?id=' . $responseId);
exit;
