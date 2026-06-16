<?php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $npm = $data['npm'] ?? '';
    $nama = $data['nama'] ?? '';
    $jurusan = $data['jurusan'] ?? '';
    $jenjang = $data['jenjang'] ?? '';
    $angkatan = $data['angkatan'] ?? '';

    if ($npm) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role='mahasiswa'");
        $stmt->execute([$npm]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(["success" => true, "nama" => $user['nama_lengkap']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, jurusan, jenjang, angkatan) VALUES (?, '123456', ?, 'mahasiswa', ?, ?, ?)");
            if ($insert->execute([$npm, $nama, $jurusan, $jenjang, $angkatan])) {
                echo json_encode(["success" => true, "nama" => $nama]);
            } else {
                echo json_encode(["success" => false, "message" => "Gagal register."]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "NPM kosong."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
