let currentUser = null;
let allRiwayatData = [];
let userBookings = [];
let currentPage = 1;
const itemsPerPage = 5;
const hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Fungsi panggil API
async function callAPI(url, data = null) {
    try {
        const options = {
            method: data ? 'POST' : 'GET',
            headers: { 'Content-Type': 'application/json' }
        };
        if (data) options.body = JSON.stringify(data);
        const response = await fetch(url, options);
        return await response.json();
    } catch (e) {
        console.error('API Error:', e);
        return { success: false, message: 'Terjadi kesalahan jaringan atau server.' };
    }
}

// Login Mahasiswa
document.getElementById('loginMahasiswaForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const npm = document.getElementById('npm').value;
    const nama = document.getElementById('namaMahasiswa').value;
    const jurusan = document.getElementById('jurusan').value;
    const jenjang = document.getElementById('jenjang').value;
    const angkatan = document.getElementById('angkatan').value;

    if (!npm || !nama || !jurusan || !jenjang || !angkatan) {
        alert('Harap isi semua data!');
        return;
    }

    const result = await callAPI('api_login_mahasiswa.php', { npm, nama, jurusan, jenjang, angkatan });
    if (result.success) {
        currentUser = { role: 'mahasiswa', npm: npm, nama: result.nama };
        await loadRiwayatFromDB();
        showWelcomeBanner();
        updateHeaderProfile();
        closeLoginModal();
        showPage('dashboard');
    } else {
        alert(result.message);
    }
});

// Login Admin
document.getElementById('loginAdminForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('usernameAdmin').value;
    const password = document.getElementById('passwordAdmin').value;

    const result = await callAPI('api_login_admin.php', { username, password });
    if (result.success) {
        currentUser = { role: 'admin', nama: result.nama };
        await loadRiwayatFromDB();
        showWelcomeBanner();
        updateHeaderProfile();
        closeAdminLoginModal();
        showPage('dashboard');
    } else {
        alert(result.message);
    }
});

// Load riwayat dari database
async function loadRiwayatFromDB() {
    const result = await callAPI('api_get_riwayat.php');
    if (Array.isArray(result)) {
        allRiwayatData = result.map(item => ({
            id: item.id,
            userNpm: item.npm,
            userNama: item.pemesan || currentUser?.nama,
            ruang: item.nama_ruang,
            tanggal: item.tanggal,
            mulai: item.jam_mulai?.substring(0, 5) || '-',
            selesai: item.jam_selesai?.substring(0, 5) || '-',
            keperluan: item.keperluan,
            status: item.status
        }));
    }
    updateUserBookings();
    updateJadwalTetapCount();
}

// Fix #4 - Hitung Jadwal Tetap dari DB secara dinamis
function updateJadwalTetapCount() {
    const totalApproved = allRiwayatData.filter(b => b.status === 'approved').length;
    const el = document.getElementById('totalJadwalTetap');
    if (el) el.innerText = totalApproved;
}

function updateUserBookings() {
    if (!currentUser) return;
    if (currentUser.role === 'admin') {
        userBookings = allRiwayatData;
    } else {
        userBookings = allRiwayatData.filter(b => b.userNpm === currentUser.npm);
    }
    document.getElementById('totalBookingSaya').innerText = userBookings.length;
    const aktifCount = userBookings.filter(b => b.status === 'pending' || b.status === 'approved').length;
    document.getElementById('bookingAktif').innerText = aktifCount;
    updateDashboardTable();
}

function updateDashboardTable() {
    const tbody = document.getElementById('dashboardBookingTable');
    if (!tbody) return;
    const recentBookings = [...userBookings].sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal)).slice(0, 5);
    let newHtml = '';
    recentBookings.forEach(booking => {
        let statusClass = '', statusText = '';
        switch (booking.status) {
            case 'pending': statusClass = 'badge-pending'; statusText = 'Pending'; break;
            case 'approved': statusClass = 'badge-approved'; statusText = 'Approved'; break;
            case 'completed': statusClass = 'badge-completed'; statusText = 'Completed'; break;
            case 'rejected': statusClass = 'badge-rejected'; statusText = 'Ditolak'; break;
        }
        newHtml += `<tr>
            <td>${booking.ruang}</td>
            <td>${booking.tanggal}</td>
            <td>${booking.mulai} - ${booking.selesai}</td>
            <td>${booking.keperluan}</td>
            <td><span class="${statusClass}">${statusText}</span></td>
            <td>
                ${booking.status === 'pending' ? `<button class="btn-danger-custom" onclick="cancelBooking(${booking.id})">Batal</button>` : ''}
                ${booking.status === 'approved' ? `<button class="btn-success-custom" onclick="completeBooking(${booking.id})">Selesai</button>` : ''}
            </td>
        </tr>`;
    });
    if (tbody.innerHTML !== newHtml) tbody.innerHTML = newHtml;
}

function showWelcomeBanner() {
    const bannerDiv = document.getElementById('welcomeBanner');
    if (!currentUser) { bannerDiv.innerHTML = ''; return; }
    bannerDiv.innerHTML = `<div class="welcome-banner"><i class="fas fa-smile-wink"></i><div class="welcome-text"><h4>Welcome,</h4><p>${currentUser.nama}</p></div></div>`;
}

function updateHeaderProfile() {
    const profileDiv = document.getElementById('userProfile');
    if (!currentUser) {
        profileDiv.innerHTML = `<button class="user-profile-btn" onclick="showLoginModal()"><i class="fas fa-sign-in-alt"></i><span>Login</span></button>`;
        document.getElementById('logoutMenu').style.display = 'none';
        document.querySelectorAll('.user-menu').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.admin-menu').forEach(el => el.style.display = 'none');
        return;
    }
    const initial = currentUser.nama.charAt(0).toUpperCase();
    profileDiv.innerHTML = `<div class="user-profile"><button class="user-profile-btn" onclick="toggleUserDropdown()"><div class="user-avatar">${initial}</div><span>${currentUser.nama}</span><i class="fas fa-chevron-down"></i></button><div class="user-dropdown" id="userDropdown"><div style="padding:12px 20px"><strong>${currentUser.nama}</strong><br><small>${currentUser.role === 'admin' ? 'Administrator' : 'Mahasiswa'}</small></div><hr><a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div>`;
    document.getElementById('logoutMenu').style.display = 'block';
    if (currentUser.role === 'admin') {
        document.querySelectorAll('.user-menu').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.admin-menu').forEach(el => el.style.display = 'block');
    } else {
        document.querySelectorAll('.user-menu').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.admin-menu').forEach(el => el.style.display = 'none');
    }
}

function toggleUserDropdown() {
    document.getElementById('userDropdown').classList.toggle('show');
}
document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-profile')) document.getElementById('userDropdown')?.classList.remove('show');
});

function showLoginModal() { document.getElementById('loginModal').style.display = 'flex'; }
function closeLoginModal() { document.getElementById('loginModal').style.display = 'none'; document.getElementById('loginMahasiswaForm').reset(); }
function showAdminLoginModal() { document.getElementById('adminLoginModal').style.display = 'flex'; }
function closeAdminLoginModal() { document.getElementById('adminLoginModal').style.display = 'none'; document.getElementById('loginAdminForm').reset(); }

function logout() {
    currentUser = null;
    allRiwayatData = [];
    userBookings = [];
    updateHeaderProfile();
    showWelcomeBanner();
    updateDashboardTable();
    showPage('dashboard');
}

function showPage(pageName) {
    document.getElementById('dashboardPage').style.display = 'none';
    document.getElementById('jadwalPage').style.display = 'none';
    document.getElementById('bookingPage').style.display = 'none';
    document.getElementById('riwayatPage').style.display = 'none';
    document.getElementById('adminApprovalPage').style.display = 'none';
    if (pageName === 'dashboard') {
        document.getElementById('dashboardPage').style.display = 'block';
        loadRiwayatFromDB();
    } else if (pageName === 'jadwal') {
        document.getElementById('jadwalPage').style.display = 'block';
        loadJadwal();
    } else if (pageName === 'booking') {
        if (!currentUser) { alert('Silakan login terlebih dahulu!'); showLoginModal(); return; }
        document.getElementById('bookingPage').style.display = 'block';
    } else if (pageName === 'riwayat') {
        if (!currentUser) { alert('Silakan login terlebih dahulu!'); showLoginModal(); return; }
        document.getElementById('riwayatPage').style.display = 'block';
        loadRiwayat();
    } else if (pageName === 'adminApproval') {
        if (!currentUser || currentUser.role !== 'admin') { alert('Akses ditolak!'); return; }
        document.getElementById('adminApprovalPage').style.display = 'block';
        loadAdminApprovals();
    }
}

document.querySelectorAll('.sidebar-menu li').forEach(item => {
    item.addEventListener('click', function () {
        const page = this.getAttribute('data-page');
        if (page === 'logout') { logout(); return; }
        document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
        this.classList.add('active');
        showPage(page);
    });
});

// Load jadwal dari database
async function loadJadwal() {
    const ruang = document.getElementById('ruangFilter').value;
    const tbody = document.getElementById('jadwalBody');
    const jadwal = await callAPI(`api_get_jadwal.php?ruang=${ruang}`);
    let newHtml = '';
    if (jadwal.length === 0) {
        newHtml = '<tr><td colspan="7" style="text-align:center; padding:40px">Belum ada jadwal untuk ruangan ini</td></tr>';
    } else {
        const grouped = {};
        jadwal.forEach(j => {
            const waktu = `${j.jam_mulai?.substring(0, 5) || '-'} - ${j.jam_selesai?.substring(0, 5) || '-'}`;
            if (!grouped[waktu]) grouped[waktu] = {};
            grouped[waktu][j.hari] = j;
        });
        const allTimes = Object.keys(grouped).sort();
        for (let waktu of allTimes) {
            let row = `<tr><td style="font-weight:600; background:#F8F9FC;">${waktu}</td>`;
            for (let hari of hariList) {
                const schedule = grouped[waktu]?.[hari];
                if (schedule) {
                    row += `<td class="schedule-cell"><div class="schedule-fixed"><strong>${schedule.nama_kegiatan}</strong><br><small>${schedule.nama_pengajar}</small></div></td>`;
                } else {
                    row += `<td class="schedule-cell"><span class="text-muted">-</span></td>`;
                }
            }
            row += `</tr>`;
            newHtml += row;
        }
    }
    if (tbody.innerHTML !== newHtml) tbody.innerHTML = newHtml;
}

function filterJadwal() { loadJadwal(); }

function loadRiwayat() {
    const statusFilter = document.getElementById('statusFilter').value;
    const searchTerm = document.getElementById('searchRiwayat').value.toLowerCase();
    let filtered = [...userBookings];
    if (statusFilter !== 'all') filtered = filtered.filter(r => r.status === statusFilter);
    if (searchTerm) filtered = filtered.filter(r => r.ruang.toLowerCase().includes(searchTerm) || r.keperluan.toLowerCase().includes(searchTerm));
    const totalPages = Math.ceil(filtered.length / itemsPerPage);
    const start = (currentPage - 1) * itemsPerPage;
    const pageData = filtered.slice(start, start + itemsPerPage);
    const tbody = document.getElementById('riwayatBody');
    let newHtml = '';
    pageData.forEach((item, index) => {
        let statusClass = '', statusText = '';
        switch (item.status) {
            case 'pending': statusClass = 'badge-pending'; statusText = 'Pending'; break;
            case 'approved': statusClass = 'badge-approved'; statusText = 'Approved'; break;
            case 'completed': statusClass = 'badge-completed'; statusText = 'Completed'; break;
            case 'rejected': statusClass = 'badge-rejected'; statusText = 'Ditolak'; break;
        }
        newHtml += `<tr>
            <td>${start + index + 1}</td>
            <td>${item.ruang}</td>
            <td>${item.tanggal}</td>
            <td>${item.mulai}</td>
            <td>${item.selesai}</td>
            <td>${item.keperluan}</td>
            <td><span class="${statusClass}">${statusText}</span></td>
            <td>
                ${item.status === 'pending' ? `<button class="btn-danger-custom" onclick="cancelBooking(${item.id})">Batal</button>` : ''}
                ${item.status === 'approved' ? `<button class="btn-success-custom" onclick="completeBooking(${item.id})">Selesai</button>` : ''}
            </td>
        </tr>`;
    });
    if (tbody.innerHTML !== newHtml) tbody.innerHTML = newHtml;
    let paginationHtml = '';
    for (let i = 1; i <= totalPages; i++) {
        paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`;
    }
    document.getElementById('pagination').innerHTML = paginationHtml;
}

function filterRiwayat() { currentPage = 1; loadRiwayat(); }
function changePage(page) { currentPage = page; loadRiwayat(); }

// Cek konflik ke server
document.getElementById('bookingRuang').addEventListener('change', checkConflict);
document.getElementById('bookingTanggal').addEventListener('change', checkConflict);
document.getElementById('bookingMulai').addEventListener('input', checkConflict);
document.getElementById('bookingSelesai').addEventListener('input', checkConflict);

let conflictTimeout = null;
async function checkConflict() {
    const ruang = document.getElementById('bookingRuang').value;
    const tanggal = document.getElementById('bookingTanggal').value;
    const mulai = document.getElementById('bookingMulai').value;
    const selesai = document.getElementById('bookingSelesai').value;
    const messageDiv = document.getElementById('conflictMessage');
    const submitBtn = document.getElementById('submitBooking');
    
    // Pastikan user selesai mengetik minimal 4 karakter (misal: 7.30 atau 07:30)
    if (ruang && tanggal && mulai && mulai.length >= 4 && selesai && selesai.length >= 4) {
        clearTimeout(conflictTimeout);
        conflictTimeout = setTimeout(async () => {
            const result = await callAPI('api_booking.php', { ruang, tanggal, jam_mulai: mulai, jam_selesai: selesai, checkOnly: true });
            if (result.conflict) {
                messageDiv.style.display = 'block';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + (result.message || 'Ruangan sudah dipesan pada waktu tersebut atau format salah!');
                submitBtn.disabled = true;
            } else {
                messageDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }, 500); // Tunggu 500ms setelah mengetik
    } else {
        // Jangan disable tombol jika input belum lengkap, biarkan alert validasi form yang bertindak
        messageDiv.style.display = 'none';
        submitBtn.disabled = false;
    }
}

// Submit booking
document.getElementById('bookingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentUser) { alert('Silakan login terlebih dahulu!'); showLoginModal(); return; }
    const ruang = document.getElementById('bookingRuang').value;
    const tanggal = document.getElementById('bookingTanggal').value;
    const mulai = document.getElementById('bookingMulai').value;
    const selesai = document.getElementById('bookingSelesai').value;
    const keperluan = document.getElementById('bookingKeperluan').value;
    if (!mulai || !selesai) { alert('Harap isi jam mulai dan jam selesai!'); return; }
    const result = await callAPI('api_booking.php', { ruang, tanggal, jam_mulai: mulai, jam_selesai: selesai, keperluan, npm: currentUser.npm });
    if (result.success) {
        alert(result.message);
        await loadRiwayatFromDB();
        e.target.reset();
        document.getElementById('conflictMessage').style.display = 'none';
        document.getElementById('bookingTanggal').value = new Date().toISOString().split('T')[0];
    } else {
        alert(result.message);
    }
});

// Fix #3 - Cancel booking dengan refresh data yang benar
async function cancelBooking(id) {
    if (confirm('Batalkan booking ini?')) {
        const result = await callAPI('api_cancel.php', { id });
        if (result.success) {
            alert('Booking berhasil dibatalkan!');
            await loadRiwayatFromDB(); // refresh data dari DB dulu
            loadRiwayat();             // baru render ulang tabel
            updateDashboardTable();    // update dashboard juga
        } else {
            alert('Gagal membatalkan: ' + result.message);
        }
    }
}

async function completeBooking(id) {
    if (confirm('Konfirmasi selesai untuk booking ini?')) {
        const result = await callAPI('api_complete.php', { id });
        if (result.success) {
            alert('Booking selesai');
            await loadRiwayatFromDB();
            loadRiwayat();
        } else {
            alert('Gagal menyelesaikan: ' + result.message);
        }
    }
}

// Fix #1 & #2 - loadAdminApprovals selalu fetch data segar dari DB
async function loadAdminApprovals() {
    const tbody = document.getElementById('adminApprovalBody');
    if (!tbody) return;

    // Selalu ambil data terbaru langsung dari DB (bukan dari cache allRiwayatData)
    tbody.innerHTML = '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';
    const result = await callAPI('api_get_riwayat.php');
    if (Array.isArray(result)) {
        allRiwayatData = result.map(item => ({
            id: item.id,
            userNpm: item.npm,
            userNama: item.pemesan || '-',
            ruang: item.nama_ruang,
            tanggal: item.tanggal,
            mulai: item.jam_mulai?.substring(0, 5) || '-',
            selesai: item.jam_selesai?.substring(0, 5) || '-',
            keperluan: item.keperluan,
            status: item.status
        }));
        updateUserBookings();
        updateJadwalTetapCount();
    }

    const pendingBookings = allRiwayatData.filter(b => b.status === 'pending');
    let newHtml = '';
    if (pendingBookings.length === 0) {
        newHtml = '<tr><td colspan="8" class="text-center">Tidak ada booking yang perlu disetujui</td></tr>';
    } else {
        pendingBookings.forEach((item, index) => {
            newHtml += `<tr>
                <td>${index + 1}</td>
                <td>${item.userNama}</td>
                <td>${item.ruang}</td>
                <td>${item.tanggal}</td>
                <td>${item.mulai} - ${item.selesai}</td>
                <td>${item.keperluan}</td>
                <td><span class="badge-pending">Pending</span></td>
                <td>
                    <button class="btn-success-custom mb-1 w-100" onclick="approveBooking(${item.id})">Terima</button>
                    <button class="btn-danger-custom w-100" onclick="rejectBooking(${item.id})">Tolak</button>
                </td>
            </tr>`;
        });
    }
    tbody.innerHTML = newHtml;
}

// Fix #2 - approveBooking: await loadAdminApprovals (yang sudah fetch fresh dari DB)
async function approveBooking(id) {
    if (confirm('Terima booking ini?')) {
        const result = await callAPI('api_approve.php', { id });
        if (result.success) {
            alert('Booking disetujui!');
            await loadAdminApprovals(); // loadAdminApprovals sudah fetch DB sendiri
        } else {
            alert('Gagal menyetujui: ' + result.message);
        }
    }
}

// Fix #1 - rejectBooking: await loadAdminApprovals (yang sudah fetch fresh dari DB)
async function rejectBooking(id) {
    if (confirm('Tolak booking ini?')) {
        const result = await callAPI('api_reject.php', { id });
        if (result.success) {
            alert('Booking ditolak!');
            await loadAdminApprovals(); // loadAdminApprovals sudah fetch DB sendiri
        } else {
            alert('Gagal menolak: ' + result.message);
        }
    }
}

// Set default tanggal hari ini
const today = new Date().toISOString().split('T')[0];
if (document.getElementById('bookingTanggal')) document.getElementById('bookingTanggal').value = today;

// Initial load
loadJadwal();
updateHeaderProfile();
showWelcomeBanner();