<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Jadwal Ruang N3 - FKIP Universitas Lampung</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Header dengan Logo dan User Profile -->
    <div class="top-header">
        <div class="logo-area">
            <div class="logo-univ" onclick="showAdminLoginModal()" style="cursor: pointer;">
                <i class="fas fa-university"></i>
            </div>
            <div class="univ-info">
                <h2>UNIVERSITAS LAMPUNG</h2>
                <p>FKIP - Gedung N3</p>
            </div>
        </div>

        <div class="user-profile" id="userProfile">
            <!-- Akan diisi dengan JS saat login -->
        </div>
    </div>

    <div class="app-layout">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li class="active" data-page="dashboard">
                    <a href="#">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li data-page="jadwal">
                    <a href="#">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal Ruang</span>
                    </a>
                </li>
                <li data-page="booking" class="user-menu">
                    <a href="#">
                        <i class="fas fa-bookmark"></i>
                        <span>Booking Ruang</span>
                    </a>
                </li>
                <li data-page="riwayat" class="user-menu">
                    <a href="#">
                        <i class="fas fa-history"></i>
                        <span>Riwayat Booking</span>
                    </a>
                </li>
                <li data-page="adminApproval" class="admin-menu" style="display: none;">
                    <a href="#">
                        <i class="fas fa-check-circle"></i>
                        <span>Persetujuan Booking</span>
                    </a>
                </li>
                <li data-page="logout" id="logoutMenu" style="display: none;">
                    <a href="#">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Konten Utama -->
        <div class="main-content" id="mainContent">
            <!-- Halaman Dashboard -->
            <div id="dashboardPage">
                <div id="welcomeBanner"></div>
                <h2 class="mb-4">Dashboard</h2>

                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-info">
                                <h3>6</h3>
                                <p>Total Ruang</p>
                            </div>
                            <div class="stat-icon blue">
                                <i class="fas fa-door-open"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-info">
                                <h3 id="totalJadwalTetap">-</h3>
                                <p>Jadwal Tetap</p>
                            </div>
                            <div class="stat-icon purple">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-info">
                                <h3 id="totalBookingSaya">0</h3>
                                <p>Total Booking Saya</p>
                            </div>
                            <div class="stat-icon green">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-info">
                                <h3 id="bookingAktif">0</h3>
                                <p>Booking Aktif</p>
                            </div>
                            <div class="stat-icon orange">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-custom">
                    <h5 class="p-3">Booking Terbaru Saya</h5>
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Ruang</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dashboardBookingTable">
                            <!-- Akan diisi JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Halaman Jadwal Ruang - Dengan Format Jam Fleksibel -->
            <div id="jadwalPage" style="display: none;">
                <div class="filter-card">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Pilih Ruangan</label>
                            <select id="ruangFilter" class="form-select">
                                <option value="N3.1">N3.1</option>
                                <option value="N3.2">N3.2</option>
                                <option value="N3.3">N3.3</option>
                                <option value="N3.4">N3.4</option>
                                <option value="N3.5">N3.5</option>
                                <option value="N3.6">N3.6</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Pilih Minggu</label>
                            <select id="mingguFilter" class="form-select">
                                <option>Minggu Ini</option>
                                <option>Minggu Depan</option>
                                <option>2 Minggu Lagi</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button class="btn-primary-custom w-100" onclick="filterJadwal()">Tampilkan</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="width: 100px">Waktu</th>
                                <th>Senin</th>
                                <th>Selasa</th>
                                <th>Rabu</th>
                                <th>Kamis</th>
                                <th>Jumat</th>
                                <th>Sabtu</th>
                            </tr>
                        </thead>
                        <tbody id="jadwalBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Halaman Booking Ruang -->
            <div id="bookingPage" style="display: none;">
                <h2 class="mb-4">Booking Ruang</h2>
                <div class="row">
                    <div class="col-md-7">
                        <div class="filter-card">
                            <form id="bookingForm">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Ruang</label>
                                    <select id="bookingRuang" class="form-select" required>
                                        <option value="">Pilih Ruangan</option>
                                        <option value="N3.1">N3.1</option>
                                        <option value="N3.2">N3.2</option>
                                        <option value="N3.3">N3.3</option>
                                        <option value="N3.4">N3.4</option>
                                        <option value="N3.5">N3.5</option>
                                        <option value="N3.6">N3.6</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" id="bookingTanggal" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Jam Mulai (contoh: 7.30)</label>
                                        <input type="text" id="bookingMulai" class="form-control time-input"
                                            placeholder="Contoh: 7.30 atau 08.00" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Jam Selesai (contoh: 9.30)</label>
                                        <input type="text" id="bookingSelesai" class="form-control time-input"
                                            placeholder="Contoh: 9.30 atau 10.00" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Keperluan</label>
                                    <textarea id="bookingKeperluan" class="form-control" rows="3"
                                        placeholder="Contoh: Basis Data - PTI 24A" required></textarea>
                                </div>
                                <div id="conflictMessage" class="alert alert-danger" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> Ruangan sudah dipesan pada waktu
                                    tersebut!
                                </div>
                                <button type="submit" class="btn-primary-custom w-100" id="submitBooking">Pesan
                                    Ruang</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="filter-card">
                            <h5>Preview Slot Waktu Terisi</h5>
                            <div id="previewSlots" style="max-height: 400px; overflow-y: auto;">
                                <div class="schedule-booking mb-2">7.30 - 9.30: Basis Data - PTI 24A (Sudah dipesan)
                                </div>
                                <div class="schedule-fixed mb-2">10.00 - 12.00: Pemrograman Web - PTI 23A (Jadwal Tetap)
                                </div>
                                <div class="schedule-booking mb-2">13.00 - 15.00: Praktikum Kimia (Sudah dipesan)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Halaman Riwayat Booking -->
            <div id="riwayatPage" style="display: none;">
                <h2 class="mb-4">Riwayat Booking</h2>

                <div class="filter-card">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Filter Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="all">Semua</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Cari</label>
                            <input type="text" id="searchRiwayat" class="form-control"
                                placeholder="Cari ruang atau keperluan...">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="btn-primary-custom w-100" onclick="filterRiwayat()">Cari</button>
                        </div>
                    </div>
                </div>

                <div class="table-custom">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Ruang</th>
                                <th>Tanggal</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="riwayatBody">
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <nav>
                        <ul class="pagination" id="pagination"></ul>
                    </nav>
                </div>
            </div>

            <!-- Halaman Admin Approval -->
            <div id="adminApprovalPage" style="display: none;">
                <h2 class="mb-4">Persetujuan Booking Ruang</h2>

                <div class="table-custom">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pemesan</th>
                                <th>Ruang</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="adminApprovalBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Login Admin -->
    <div id="adminLoginModal" class="modal-custom">
        <div class="modal-content-custom">
            <div style="text-align: center; margin-bottom: 20px;">
                <i class="fas fa-user-shield"
                    style="font-size: 50px; background: linear-gradient(135deg, #001396, #AA00DA); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h3 class="mt-3">Admin Login</h3>
            </div>

            <form id="loginAdminForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" id="usernameAdmin" class="form-control" placeholder="Masukkan Username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="passwordAdmin" class="form-control" placeholder="Masukkan Password"
                        required>
                </div>
                <button type="submit" class="btn-primary-custom w-100">Login sebagai Admin</button>
                <div class="mt-3 text-center">
                    <a href="#" class="text-muted" onclick="closeAdminLoginModal()"
                        style="text-decoration: none; font-size: 14px;">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Login -->
    <div id="loginModal" class="modal-custom">
        <div class="modal-content-custom">
            <div style="text-align: center; margin-bottom: 20px;">
                <i class="fas fa-university"
                    style="font-size: 50px; background: linear-gradient(135deg, #001396, #AA00DA); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h3 class="mt-3">Login Sistem Informasi</h3>
                <p class="text-muted">Jadwal Ruang N3 FKIP Universitas Lampung</p>
            </div>

            <!-- Form Login -->
            <div id="formMahasiswa">
                <form id="loginMahasiswaForm">
                    <div class="mb-3">
                        <label class="form-label">NPM</label>
                        <input type="text" id="npm" class="form-control" placeholder="Masukkan NPM" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" id="namaMahasiswa" class="form-control" placeholder="Masukkan Nama Lengkap"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jurusan</label>
                        <select id="jurusan" class="form-select" required>
                            <option value="">Pilih Jurusan</option>
                            <option value="Pendidikan Bahasa Prancis">Pendidikan Bahasa Prancis</option>
                            <option value="Pendidikan Ekonomi">Pendidikan Ekonomi</option>
                            <option value="Pendidikan Guru Sekolah Dasar">Pendidikan Guru Sekolah Dasar</option>
                            <option value="Pendidikan Kimia">Pendidikan Kimia</option>
                            <option value="Bimbingan Konseling">Bimbingan Konseling</option>
                            <option value="Pendidikan Biologi">Pendidikan Biologi</option>
                            <option value="Pendidikan Sejarah">Pendidikan Sejarah</option>
                            <option value="Pendidikan Matematika">Pendidikan Matematika</option>
                            <option value="Pendidikan Pancasila dan Kewarganegaraan">Pendidikan Pancasila dan
                                Kewarganegaraan</option>
                            <option value="Pendidikan Bahasa dan Sastra Indonesia">Pendidikan Bahasa dan Sastra
                                Indonesia</option>
                            <option value="Pendidikan Jasmani, Kesehatan dan Rekreasi">Pendidikan Jasmani, Kesehatan dan
                                Rekreasi</option>
                            <option value="Pendidikan Seni Tari">Pendidikan Seni Tari</option>
                            <option value="Pendidikan Fisika">Pendidikan Fisika</option>
                            <option value="Pendidikan Geografi">Pendidikan Geografi</option>
                            <option value="Pendidikan Guru Pendidikan Anak Usia Dini">Pendidikan Guru Pendidikan Anak
                                Usia Dini</option>
                            <option value="Pendidikan Teknologi Informasi">Pendidikan Teknologi Informasi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang</label>
                        <select id="jenjang" class="form-select" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Angkatan</label>
                        <select id="angkatan" class="form-select" required>
                            <option value="">Pilih Angkatan</option>
                            <option value="2020">2020</option>
                            <option value="2021">2021</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-custom w-100">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>