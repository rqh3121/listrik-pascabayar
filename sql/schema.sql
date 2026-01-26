CREATE DATABASE IF NOT EXISTS listrik_db;
USE listrik_db;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username admin, password admin123
-- password_hash di bawah ini contoh, nanti kita isi via script (lebih aman),
-- tapi biar cepat, kita pakai cara "generate lewat PHP" di langkah 4.
-- (Kalau mau langsung insert hash, bilang aja, nanti aku bikinin hash yang valid)

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nomor_kwh VARCHAR(30) UNIQUE NOT NULL,
  alamat TEXT NOT NULL,
  voltase INT NOT NULL,     -- “jumlah voltase yang diambil”
  no_hp VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
