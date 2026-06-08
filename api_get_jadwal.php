<?php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    $ruang = $_GET['ruang'] ?? 'Semua';

    // Auto-complete expired bookings
    $pdo->exec("UPDATE booking_ruangan SET status = 'completed' WHERE status = 'approved' AND (tanggal < CURDATE() OR (tanggal = CURDATE() AND jam_selesai <= CURTIME()))");

    // Get approved and completed bookings
    $sql = "
        SELECT 
            br.jam_mulai, 
            br.jam_selesai, 
            br.keperluan as nama_kegiatan, 
            u.nama_lengkap as nama_pengajar,
            r.nama_ruang,
            br.tanggal
        FROM booking_ruangan br
        JOIN ruangan r ON br.ruangan_id = r.id
        JOIN users u ON br.user_id = u.id
        WHERE br.status IN ('approved', 'completed')
    ";

    $params = [];
    if ($ruang !== 'Semua' && $ruang !== '') {
        $sql .= " AND r.nama_ruang = ?";
        $params[] = $ruang;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map tanggal to hari
    $hariMap = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];

    $data = [];
    foreach ($bookings as $b) {
        $dayName = date('l', strtotime($b['tanggal']));
        $b['hari'] = $hariMap[$dayName] ?? '';
        $data[] = $b;
    }

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
