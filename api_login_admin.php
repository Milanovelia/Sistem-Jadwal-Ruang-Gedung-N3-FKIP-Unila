<?php
header('Content-Type: application/json');
error_reporting(0);
require 'koneksi.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role='admin'");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(["success" => true, "nama" => $user['nama_lengkap']]);
        } else {
            echo json_encode(["success" => false, "message" => "Username atau password salah!"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Data tidak lengkap."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
