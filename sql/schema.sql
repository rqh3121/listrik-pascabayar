CREATE DATABASE IF NOT EXISTS listrik_db;
USE listrik_db;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username admin, password admin123

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nomor_kwh VARCHAR(30) UNIQUE NOT NULL,
  alamat TEXT NOT NULL,
  voltase INT NOT NULL,     -- “jumlah voltase yang diambil”
  no_hp VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status enum('BELUM LUNAS', 'LUNAS') NOT NULL DEFAULT,
  daya_va int(11) NOT NULL,
  tarif_per_kwh decimal(10,2) NOT NULL,
  kwh 	int(11) NOT NULL,
  tagihan_bulanan int(11) NOT NULL
    );
