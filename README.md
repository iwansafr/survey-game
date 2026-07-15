# Survey Dampak Game - Siswa SMK

Website survey kebiasaan bermain game untuk siswa SMK, dengan hasil analisis
otomatis (rule-based) tentang dampak negatif & saran, plus dashboard admin.

## Struktur Folder

```
survey-game/
├── admin/
│   ├── includes/
│   │   ├── auth_check.php     -> guard login, di-require di tiap halaman admin
│   │   └── nav.php            -> navigasi bersama
│   ├── login.php               -> login admin
│   ├── logout.php              -> logout admin
│   ├── dashboard.php           -> statistik & chart
│   ├── data.php                -> tabel semua responden + filter
│   ├── games.php                -> CRUD master game + verifikasi game pending
│   └── rules.php                -> CRUD rule dampak & saran per genre
├── assets/
│   └── js/
│       └── survey.js           -> search AJAX game & validasi form
├── config/
│   ├── db.php                  -> koneksi database (WAJIB disesuaikan)
│   └── admin.php               -> kredensial admin (WAJIB diganti)
├── database/
│   └── schema.sql               -> struktur tabel + seed data
├── index.php                    -> form survey siswa
├── search_game.php              -> endpoint AJAX pencarian game
├── submit.php                   -> proses simpan jawaban survey
└── result.php                   -> halaman hasil analisis untuk siswa
```

## Cara Instalasi

1. **Buat database**
   Import `database/schema.sql` ke MySQL/MariaDB (via phpMyAdmin, adminer,
   atau `mysql -u root -p < database/schema.sql`). Ini akan otomatis membuat
   database `survey_game` beserta seed data game & rule dampak.

2. **Konfigurasi koneksi database**
   Edit `config/db.php`, sesuaikan `$db_host`, `$db_name`, `$db_user`, `$db_pass`
   dengan kredensial MySQL di server/hosting Anda.

3. **Ganti password admin (WAJIB sebelum dipakai beneran)**
   Login default:
   - Username: `admin`
   - Password: `admin123`

   Untuk ganti password, jalankan di terminal:
   ```
   php -r "echo password_hash('password_baru_anda', PASSWORD_DEFAULT);"
   ```
   Salin hasilnya, lalu ganti nilai `password_hash` di `config/admin.php`.

4. **Upload ke server**
   Upload seluruh folder `survey-game/` ke public_html (atau subdomain) di
   aaPanel/server Anda. Pastikan PHP >= 8.0 dan ekstensi PDO MySQL aktif.

5. **Akses**
   - Form survey siswa: `https://domain-anda.com/`
   - Login admin: `https://domain-anda.com/admin/login.php`

## Alur Aplikasi

**Siswa:**
1. Isi form di `index.php` (biodata, cari game favorit via search box,
   atau input manual kalau game belum ada di daftar)
2. Data disimpan lewat `submit.php`
3. Diarahkan ke `result.php` -> lihat hasil analisis: tingkat kewaspadaan,
   dampak yang dirasakan sendiri, potensi dampak negatif dari genre game,
   dan saran mengatasinya

**Admin:**
1. Login di `admin/login.php`
2. `dashboard.php` -> lihat statistik & chart (distribusi gender, genre
   terpopuler, jumlah per kelas, top game, top dampak yang dirasakan siswa)
3. `data.php` -> lihat & filter semua jawaban responden per kelas/genre,
   bisa cek hasil analisis tiap siswa
4. `games.php` -> tambah game baru, atau verifikasi genre untuk game yang
   diinput manual oleh siswa (status "pending")
5. `rules.php` -> edit kalimat dampak negatif & saran per genre, atau
   tambah rule untuk genre baru

## Catatan Teknis

- Genre game otomatis mengikuti data game yang dipilih siswa (bukan input
  manual genre), sehingga konsisten.
- Kalau siswa input nama game yang belum ada di database, game tersebut
  otomatis masuk ke tabel `games` dengan `status = pending` dan sementara
  memakai rule fallback genre "Lainnya", sampai admin memverifikasi
  genre aslinya di `admin/games.php`.
- Hasil analisis bersifat **rule-based** (bukan AI generatif sungguhan),
  disusun dari kombinasi rule genre + jawaban self-report siswa, supaya
  tanpa biaya API dan hasilnya konsisten.
# survey-game
