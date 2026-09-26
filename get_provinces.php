<?php
header('Content-Type: application/json');
include 'rajaongkir_config.php';

$res = callRajaOngkirAPI('province');
echo json_encode($res);
?>
