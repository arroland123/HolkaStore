<?php
session_start();
include 'koneksi.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Validasi request
if (!isset($_POST['key']) || !isset($_POST['qty'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$cart_key = $_POST['key'];
$new_qty = intval($_POST['qty']);

// Validasi quantity
if ($new_qty < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantity minimal 1'
    ]);
    exit;
}

// Cek apakah cart item exists
if (!isset($_SESSION['cart'][$cart_key])) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan di keranjang'
    ]);
    exit;
}

// Ambil data produk untuk validasi stok
$id_produk = $_SESSION['cart'][$cart_key]['id_produk'];
$size = isset($_SESSION['cart'][$cart_key]['size']) ? $_SESSION['cart'][$cart_key]['size'] : '';
$query = "SELECT stok, stok_detail FROM produk WHERE id_produk = $id_produk";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Produk tidak ditemukan'
    ]);
    exit;
}

$produk = mysqli_fetch_assoc($result);
$stok = isset($produk['stok']) ? intval($produk['stok']) : 99;

if (!empty($produk['stok_detail'])) {
    $stok_detail = json_decode($produk['stok_detail'], true);
    if (is_array($stok_detail) && isset($stok_detail[$size])) {
        $stok = intval($stok_detail[$size]);
    }
}

// Validasi stok
if ($new_qty > $stok) {
    echo json_encode([
        'success' => false,
        'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $stok
    ]);
    exit;
}

// Update quantity
$_SESSION['cart'][$cart_key]['qty'] = $new_qty;

// Hitung ulang semua harga
$items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $key => $item) {
    $item_total = $item['harga'] * $item['qty'];
    $subtotal += $item_total;
    
    $items[] = [
        'key' => $key,
        'subtotal' => $item_total
    ];
}

// Hitung biaya pengiriman
$shipping_cost = $subtotal >= 150000 ? 0 : 15000;
$total = $subtotal + $shipping_cost;

echo json_encode([
    'success' => true,
    'message' => 'Keranjang berhasil diperbarui',
    'items' => $items,
    'summary' => [
        'subtotal' => $subtotal,
        'shipping_cost' => $shipping_cost,
        'total' => $total
    ]
]);
?>
