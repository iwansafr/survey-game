<?php
// =========================================================
// Kredensial admin (single admin, sesuai kesepakatan awal)
// Password default: admin123  -> WAJIB diganti sebelum deploy!
//
// Cara ganti password:
// 1. Jalankan di terminal: php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
// 2. Salin hasilnya, ganti nilai 'password_hash' di bawah ini
// =========================================================

return [
    'username'      => 'admin',
    'password_hash' => '$2b$12$OsX3.3hA4c0BA0bIRxckL.bp69zeIr8G680vq/JM4/E0TXMgqN/Hq',
];
