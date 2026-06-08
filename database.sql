-- Membuat Database
CREATE DATABASE IF NOT EXISTS db_ruang_n3;
USE db_ruang_n3;

-- ==========================================
-- 1. Tabel Users (Mahasiswa & Admin)
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL, -- NPM untuk mahasiswa, 'admin' untuk admin
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'mahasiswa') DEFAULT 'mahasiswa',
    jurusan VARCHAR(100) DEFAULT NULL,
    jenjang ENUM('S1', 'S2', 'S3') DEFAULT NULL,
    angkatan YEAR DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Data Dummy Users
INSERT IGNORE INTO users (username, password, nama_lengkap, role, jurusan, jenjang, angkatan) VALUES
('admin', 'admin123', 'Administrator', 'admin', NULL, NULL, NULL),
('2413025051', 'password123', 'Mila Novelia', 'mahasiswa', 'Pendidikan Teknologi Informasi', 'S1', 2024);

-- ==========================================
-- 2. Tabel Ruangan
-- ==========================================
CREATE TABLE IF NOT EXISTS ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruang VARCHAR(20) UNIQUE NOT NULL,
    kapasitas INT DEFAULT 40,
    status_aktif BOOLEAN DEFAULT TRUE
);

-- Insert Data Dummy Ruangan
INSERT IGNORE INTO ruangan (nama_ruang, kapasitas) VALUES
('N3.1', 40),
('N3.2', 40),
('N3.3', 40),
('N3.4', 40),
('N3.5', 40),
('N3.6', 40);

-- ==========================================
-- 3. Tabel Booking Ruangan (Sekaligus sebagai Jadwal)
-- ==========================================
CREATE TABLE IF NOT EXISTS booking_ruangan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ruangan_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
    alasan_penolakan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
);

-- Insert Data Dummy Booking Ruangan untuk Simulasi Jadwal Tetap
-- Kita buat beberapa data approved agar muncul di jadwal
INSERT IGNORE INTO booking_ruangan (user_id, ruangan_id, tanggal, jam_mulai, jam_selesai, keperluan, status) VALUES
(1, 1, CURDATE() + INTERVAL (1 - DAYOFWEEK(CURDATE()) + 1) DAY, '07:30:00', '09:30:00', 'Pemrograman Web - PTI 23A', 'approved'),
(1, 2, CURDATE() + INTERVAL (1 - DAYOFWEEK(CURDATE()) + 1) DAY, '08:30:00', '10:30:00', 'Aljabar Linear - MAT 23A', 'approved'),
(1, 3, CURDATE() + INTERVAL (1 - DAYOFWEEK(CURDATE()) + 1) DAY, '07:30:00', '10:00:00', 'Sistem Operasi - PTI 23A', 'approved'),
(1, 4, CURDATE() + INTERVAL (2 - DAYOFWEEK(CURDATE()) + 1) DAY, '10:00:00', '12:00:00', 'Metode Numerik - MAT 23', 'approved');
