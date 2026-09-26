<?php
session_start();

// Set header untuk JSON response
header('Content-Type: application/json');

// Kosongkan cart
$_SESSION['cart'] = [];

echo json_encode([
    'success' => true,
    'message' => 'Keranjang berhasil dikosongkan',
    'cart_count' => 0
]);
?>
