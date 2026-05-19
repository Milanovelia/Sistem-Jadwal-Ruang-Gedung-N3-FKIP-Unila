<?php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    // Auto-complete expired bookings
    $pdo->exec("UPDATE booking_ruangan SET status = 'completed' WHERE status = 'approved' AND (tanggal < CURDATE() OR (tanggal = CURDATE() AND jam_selesai <= CURTIME()))");

    $stmt = $pdo->query("
        SELECT br.*, r.nama_ruang, u.nama_lengkap as pemesan, u.username as npm
        FROM booking_ruangan br
        JOIN ruangan r ON br.ruangan_id = r.id
        JOIN users u ON br.user_id = u.id
        ORDER BY br.tanggal DESC, br.jam_mulai DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
