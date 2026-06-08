<?php
require 'koneksi.php';
$stmt = $pdo->query("SELECT * FROM ruangan");
$ruangan = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["status" => "sukses", "ruangan" => $ruangan]);
?>
