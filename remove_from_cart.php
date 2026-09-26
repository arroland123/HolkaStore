<?php
session_start();

// Set header untuk JSON response
header('Content-Type: application/json');

// Validasi request
if (!isset($_POST['key'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$cart_key = $_POST['key'];

// Cek apakah cart item exists
if (!isset($_SESSION['cart'][$cart_key])) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan di keranjang'
    ]);
    exit;
}

// Hapus item dari cart
unset($_SESSION['cart'][$cart_key]);

// Hitung ulang total
$total_items = count($_SESSION['cart']);
$subtotal = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}

$shipping_cost = $subtotal >= 150000 ? 0 : 15000;
$total = $subtotal + $shipping_cost;

echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil dihapus dari keranjang',
    'cart_count' => $total_items,
    'summary' => [
        'subtotal' => $subtotal,
        'shipping_cost' => $shipping_cost,
        'total' => $total
    ]
]);
?>
