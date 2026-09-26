<?php
header('Content-Type: application/json');
include 'rajaongkir_config.php';

$province_id = isset($_GET['province_id']) ? (int)$_GET['province_id'] : 0;

if ($province_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Provinsi tidak valid.']);
    exit;
}

$res = callRajaOngkirAPI('city', ['province' => $province_id]);
echo json_encode($res);
?>
