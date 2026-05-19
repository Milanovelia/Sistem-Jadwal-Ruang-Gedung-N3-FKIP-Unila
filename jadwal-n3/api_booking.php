<?php
// api_booking.php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $ruang = $data['ruang'] ?? '';
    $tanggal = $data['tanggal'] ?? '';
    $jam_mulai = $data['jam_mulai'] ?? '';
    $jam_selesai = $data['jam_selesai'] ?? '';
    $keperluan = $data['keperluan'] ?? '';
    $npm = $data['npm'] ?? '';
    $checkOnly = $data['checkOnly'] ?? false;

    // Normalisasi format jam (dari "7.30" menjadi "07:30:00")
    function normalizeTime($timeStr) {
        $timeStr = str_replace('.', ':', $timeStr);
        $timeParts = explode(':', $timeStr);
        if (count($timeParts) >= 2) {
            $h = str_pad((int)$timeParts[0], 2, '0', STR_PAD_LEFT);
            $m = str_pad((int)$timeParts[1], 2, '0', STR_PAD_LEFT);
            return "$h:$m:00";
        }
        return '';
    }
    
    $jam_mulai = normalizeTime($jam_mulai);
    $jam_selesai = normalizeTime($jam_selesai);
    
    if (empty($jam_mulai) || empty($jam_selesai)) {
        echo json_encode(["success" => false, "message" => "Format jam tidak valid. Harap gunakan format HH:MM (contoh 07:30)", "conflict" => true]);
        exit;
    }

    // Get ruang id
    $rStmt = $pdo->prepare("SELECT id FROM ruangan WHERE nama_ruang = ?");
    $rStmt->execute([$ruang]);
    $r = $rStmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) {
        echo json_encode(["success" => false, "message" => "Ruangan tidak valid.", "conflict" => true]);
        exit;
    }
    $ruangan_id = $r['id'];

    // Cek konflik
    $conflict = false;
    $qBooking = $pdo->prepare("SELECT * FROM booking_ruangan WHERE ruangan_id = ? AND tanggal = ? AND status IN ('pending', 'approved', 'completed') AND ((jam_mulai < ? AND jam_selesai > ?))");
    $qBooking->execute([$ruangan_id, $tanggal, $jam_selesai, $jam_mulai]);
    if ($qBooking->rowCount() > 0) {
        $conflict = true;
    }

    if ($checkOnly) {
        echo json_encode(["conflict" => $conflict]);
        exit;
    }

    if ($conflict) {
        echo json_encode(["success" => false, "message" => "Jadwal bentrok dengan kegiatan lain!"]);
    } else {
        if (!$npm) {
            echo json_encode(["success" => false, "message" => "Gagal: User belum login dengan benar."]);
            exit;
        }

        $uStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $uStmt->execute([$npm]);
        $u = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $user_id = $u['id'];
            $insert = $pdo->prepare("INSERT INTO booking_ruangan (user_id, ruangan_id, tanggal, jam_mulai, jam_selesai, keperluan, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            if ($insert->execute([$user_id, $ruangan_id, $tanggal, $jam_mulai, $jam_selesai, $keperluan])) {
                echo json_encode(["success" => true, "message" => "Booking berhasil diajukan! Menunggu persetujuan admin."]);
            } else {
                echo json_encode(["success" => false, "message" => "Gagal booking"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "User tidak ditemukan."]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "conflict" => true]);
}
?>