-- ===== DATABASE =====
DROP DATABASE IF EXISTS sipinlab;
CREATE DATABASE sipinlab;
USE sipinlab;

-- ===== TABEL PENGGUNA =====
CREATE TABLE pengguna (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  sandi VARCHAR(255) NOT NULL,
  peran ENUM('admin','laboran','peminjam') DEFAULT 'peminjam',
  prodi VARCHAR(80),
  nim VARCHAR(30),
  diblokir BOOLEAN DEFAULT 0
);

-- ===== TABEL KATEGORI ALAT =====
CREATE TABLE kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL UNIQUE
);

-- ===== TABEL LOKASI =====
CREATE TABLE lokasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  ruang VARCHAR(50),
  rak VARCHAR(50)
);

-- ===== TABEL ALAT =====
CREATE TABLE alat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_unik VARCHAR(50) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  kategori_id INT NOT NULL,
  lokasi_id INT NOT NULL,
  kondisi_enum ENUM('baru','baik','cukup','rusak') DEFAULT 'baik',
  jumlah_total INT DEFAULT 0,
  jumlah_tersedia INT DEFAULT 0,
  minimum_alert INT DEFAULT 1,
  catatan TEXT,
  CONSTRAINT fk_alat_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id),
  CONSTRAINT fk_alat_lokasi   FOREIGN KEY (lokasi_id)   REFERENCES lokasi(id)
);

-- ===== USER DEFAULT (password: admin123 / peminjam123) =====
-- Hash admin123
INSERT INTO pengguna(nama,email,sandi,peran) VALUES
('Admin Lab',  'admin@lab.test',  '$2y$10$vdTAgC29RR7D9tEzyL1Cj.Mf/5oMhtk0D4bRQ1CmxHKT0QkkS4BGq', 'admin'),
('Admin Lab 2','admin2@lab.test', '$2y$10$vdTAgC29RR7D9tEzyL1Cj.Mf/5oMhtk0D4bRQ1CmxHKT0QkkS4BGq', 'admin');

-- Hash peminjam123
INSERT INTO pengguna(nama,email,sandi,peran) VALUES
('Peminjam Satu','peminjam@lab.test','$2y$10$e2vY6IuLqk3JpC8m9MfTJu1z8B3c8X5f0vHf1n7a0uR2iV.3vG0pW','peminjam');

-- ===== TABEL PEMINJAMAN =====
CREATE TABLE peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengguna_id INT NOT NULL,
  tanggal_pinjam DATE NOT NULL,
  tanggal_kembali_rencana DATE,
  tanggal_kembali DATE,
  status ENUM('Menunggu','Disetujui','Ditolak','Selesai') DEFAULT 'Menunggu',
  denda INT DEFAULT 0,
  keterangan TEXT,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_peminjaman_pengguna FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
);

-- ===== TABEL DETAIL PEMINJAMAN =====
CREATE TABLE detail_peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  peminjaman_id INT NOT NULL,
  alat_id INT NOT NULL,
  jumlah INT NOT NULL,
  CONSTRAINT fk_detail_peminjaman FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
  CONSTRAINT fk_detail_alat        FOREIGN KEY (alat_id)        REFERENCES alat(id)
);

-- ===== CONTOH QUERY LIST UNTUK HALAMAN ADMIN (ada hari_telat & daftar_alat) =====
SELECT
  p.id,
  p.pengguna_id,
  p.tanggal_pinjam,
  p.tanggal_kembali_rencana,
  p.tanggal_kembali,
  p.status,
  COALESCE(p.denda,0) AS denda,
  g.nama AS nama_peminjam,
  GREATEST(DATEDIFF(CURDATE(), p.tanggal_kembali_rencana), 0) AS hari_telat,
  d.daftar_alat
FROM peminjaman p
JOIN pengguna g ON g.id = p.pengguna_id
LEFT JOIN (
  SELECT
    dp.peminjaman_id,
    GROUP_CONCAT(CONCAT(a.nama,' (',dp.jumlah,')') ORDER BY a.nama SEPARATOR ', ') AS daftar_alat
  FROM detail_peminjaman dp
  JOIN alat a ON a.id = dp.alat_id
  GROUP BY dp.peminjaman_id
) d ON d.peminjaman_id = p.id
ORDER BY p.id DESC;

-- ===== CEK STRUKTUR =====
DESCRIBE pengguna;
DESCRIBE kategori;
DESCRIBE lokasi;
DESCRIBE alat;
DESCRIBE peminjaman;
DESCRIBE detail_peminjaman;
