<?php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? '';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE booking_ruangan SET status = 'approved' WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(["success" => true, "message" => "Booking disetujui"]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal mengeksekusi query"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ID tidak valid"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
