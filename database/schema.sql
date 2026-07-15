-- =========================================================
-- Schema Database: Survey Dampak Game untuk Siswa SMK
-- =========================================================

CREATE DATABASE IF NOT EXISTS survey_game
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE survey_game;

-- ---------------------------------------------------------
-- 1. Master data game (nama game -> genre)
--    status: 'verified' = sudah punya genre pasti
--            'pending'  = hasil input manual siswa, belum diverifikasi admin
-- ---------------------------------------------------------
CREATE TABLE games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_game VARCHAR(150) NOT NULL,
    genre VARCHAR(50) NULL,
    status ENUM('verified', 'pending') NOT NULL DEFAULT 'verified',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nama_game (nama_game)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. Rule dampak negatif & saran, per GENRE (bukan per game)
--    Admin bisa CRUD lewat admin/rules.php
--    dampak_negatif & saran disimpan multi-baris, dipisah "\n"
--    (1 baris = 1 poin), supaya gampang ditampilkan sebagai list.
-- ---------------------------------------------------------
CREATE TABLE game_impact_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    genre VARCHAR(50) NOT NULL,
    dampak_negatif TEXT NOT NULL,
    saran TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_genre (genre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. Master opsi dampak self-report (checkbox di form survey)
-- ---------------------------------------------------------
CREATE TABLE impact_options (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. Jawaban survey siswa
--    game_id NULL jika siswa input manual & belum match ke master data
-- ---------------------------------------------------------
CREATE TABLE responses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    game_id INT UNSIGNED NULL,
    nama_game_manual VARCHAR(150) NULL,
    alasan_suka TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_responses_game
        FOREIGN KEY (game_id) REFERENCES games(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. Pivot: dampak yang dirasakan sendiri oleh siswa (self-report)
-- ---------------------------------------------------------
CREATE TABLE response_impacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id INT UNSIGNED NOT NULL,
    impact_option_id INT UNSIGNED NOT NULL,
    CONSTRAINT fk_ri_response
        FOREIGN KEY (response_id) REFERENCES responses(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_ri_impact_option
        FOREIGN KEY (impact_option_id) REFERENCES impact_options(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- SEED DATA
-- =========================================================

-- Genre yang dipakai: FPS/Battle Royale, MOBA, RPG,
-- Simulation/Sandbox, Sports/Racing, Puzzle/Casual, Fighting, Strategy

INSERT INTO games (nama_game, genre, status) VALUES
('Mobile Legends: Bang Bang', 'MOBA', 'verified'),
('Arena of Valor (AOV)', 'MOBA', 'verified'),
('PUBG Mobile', 'FPS/Battle Royale', 'verified'),
('Free Fire', 'FPS/Battle Royale', 'verified'),
('Call of Duty Mobile', 'FPS/Battle Royale', 'verified'),
('Valorant', 'FPS/Battle Royale', 'verified'),
('Genshin Impact', 'RPG', 'verified'),
('Honkai Star Rail', 'RPG', 'verified'),
('Roblox', 'Simulation/Sandbox', 'verified'),
('Minecraft', 'Simulation/Sandbox', 'verified'),
('Growtopia', 'Simulation/Sandbox', 'verified'),
('EA Sports FC Mobile', 'Sports/Racing', 'verified'),
('eFootball', 'Sports/Racing', 'verified'),
('Asphalt 9: Legends', 'Sports/Racing', 'verified'),
('Clash of Clans', 'Strategy', 'verified'),
('Clash Royale', 'Strategy', 'verified'),
('Stumble Guys', 'Puzzle/Casual', 'verified'),
('Among Us', 'Puzzle/Casual', 'verified'),
('Tekken Mobile', 'Fighting', 'verified'),
('Mortal Kombat', 'Fighting', 'verified');

INSERT INTO game_impact_rules (genre, dampak_negatif, saran) VALUES
('FPS/Battle Royale',
 'Cenderung memicu emosi/agresivitas saat bermain\nDurasi main bisa tidak terasa lama karena ritme permainan cepat\nBerisiko menurunkan fokus belajar karena adrenalin tinggi',
 'Batasi sesi bermain maksimal 1-2 jam per hari\nAmbil jeda 10 menit tiap 30 menit bermain\nLakukan aktivitas fisik ringan sesudahnya untuk menetralkan emosi'),
('MOBA',
 'Rentan menimbulkan tekanan sosial (tuntutan menang dari tim)\nBisa memicu stres/emosi saat kalah\nWaktu bermain sering lebih lama dari rencana awal karena 1 match cukup panjang',
 'Tentukan jumlah match maksimal per hari sebelum mulai main\nHindari bermain saat lelah/mengantuk agar tidak mudah emosi\nAjak teman diskusi santai, bukan hanya lewat game'),
('RPG',
 'Alur cerita & progres karakter yang panjang mendorong main berjam-jam\nBerpotensi mengalihkan fokus dari tugas sekolah karena ingin menyelesaikan misi\nAda dorongan belanja item digital (gacha/top up)',
 'Buat jadwal bermain tetap, misal hanya di akhir pekan\nGunakan fitur pengingat waktu (timer) saat bermain\nDiskusikan dengan orang tua soal batas pengeluaran top up'),
('Simulation/Sandbox',
 'Waktu bermain sulit dibatasi karena sifatnya open-ended/tanpa akhir jelas\nBisa mengurangi interaksi sosial langsung dengan teman sebaya\nRisiko paparan konten dari pemain lain yang tidak selalu sesuai usia',
 'Sepakati waktu mulai dan berhenti main sejak awal\nImbangi dengan kegiatan sosial nyata seperti futsal atau nongkrong\nGunakan mode/server yang sesuai usia dan diawasi'),
('Sports/Racing',
 'Kompetisi online dapat memicu frustrasi berlebihan saat kalah\nMendorong pembelian item/kartu pemain secara berulang\nBisa menjadi pelarian dari olahraga fisik yang sesungguhnya',
 'Jadikan game ini pelengkap, bukan pengganti olahraga fisik asli\nTetapkan budget top up bulanan dan patuhi\nMain santai tanpa target harus menang terus'),
('Puzzle/Casual',
 'Terlihat ringan sehingga sering dimainkan tanpa sadar waktu (mindless scrolling/playing)\nSesi bermain singkat tapi berulang-ulang sepanjang hari\nBisa mengganggu konsentrasi saat jam belajar',
 'Gunakan game ini hanya sebagai jeda singkat, misal 5-10 menit saat istirahat\nMatikan notifikasi game saat jam belajar\nGanti kebiasaan buka game saat bosan dengan aktivitas lain'),
('Strategy',
 'Sistem timer/energi mendorong siswa membuka game berkali-kali sepanjang hari\nMemicu keinginan top up demi mempercepat progres\nBisa menyita perhatian meski durasi tiap sesi singkat',
 'Cukup cek game di waktu-waktu tertentu saja, misal pagi dan malam\nHindari tergoda tawaran top up dengan menonaktifkan metode pembayaran otomatis\nFokuskan energi kompetitif ke kegiatan sekolah atau organisasi'),
('Fighting',
 'Bisa memicu emosi/agresivitas tinggi terutama saat kalah beruntun\nSesi latihan combo bisa memakan waktu lama tanpa terasa\nBerpotensi memancing konflik saat bermain multiplayer',
 'Berhenti sejenak jika mulai merasa emosi/frustrasi\nBatasi sesi latihan dengan timer\nJadikan ajang kompetisi sehat, hindari perdebatan dengan pemain lain'),
('Lainnya',
 'Data dampak untuk game ini sedang menunggu verifikasi admin',
 'Sementara waktu, terapkan kebiasaan bermain game yang sehat secara umum: batasi waktu, jaga interaksi sosial, dan utamakan tugas sekolah');

INSERT INTO impact_options (label) VALUES
('Sulit tidur karena main sampai larut malam'),
('Nilai atau tugas sekolah jadi terganggu'),
('Sering emosi/marah saat kalah bermain'),
('Mengeluarkan uang untuk top up secara berlebihan'),
('Kurang bersosialisasi langsung dengan teman/keluarga'),
('Mata lelah, pusing, atau sakit kepala setelah main'),
('Malas berolahraga/kurang gerak fisik'),
('Sulit berhenti bermain meski sudah berniat berhenti');
