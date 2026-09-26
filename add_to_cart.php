<?php
session_start();
include 'koneksi.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil dan bersihkan data input
$id_produk = isset($_POST['id']) ? intval($_POST['id']) : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
$size = isset($_POST['size']) ? trim($_POST['size']) : '';
$color = isset($_POST['color']) ? trim($_POST['color']) : 'Default';

// Validasi input dasar
if ($id_produk <= 0 || $qty <= 0 || empty($size)) {
    echo json_encode(['success' => false, 'message' => 'Data produk tidak lengkap']);
    exit;
}

// Cek stok di database
$query = "SELECT stok, nama_produk, harga, stok_detail FROM produk WHERE id_produk = $id_produk";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
    exit;
}

$produk = mysqli_fetch_assoc($result);
$stok_tersedia = $produk['stok'];

// Gunakan stok spesifik ukuran jika tersedia
if (!empty($produk['stok_detail'])) {
    $stok_detail = json_decode($produk['stok_detail'], true);
    if (is_array($stok_detail) && isset($stok_detail[$size])) {
        $stok_tersedia = $stok_detail[$size];
    }
}

// Buat unique key untuk kombinasi produk (ID + Size + Color)
// Menggunakan MD5 atau string concat sederhana agar unik per varian
$cart_key = md5($id_produk . $size . $color);

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Cek jika produk sudah ada di cart
if (isset($_SESSION['cart'][$cart_key])) {
    $current_qty = $_SESSION['cart'][$cart_key]['qty'];
    $new_qty = $current_qty + $qty;
    
    // Validasi stok akumulatif
    if ($new_qty > $stok_tersedia) {
        echo json_encode([
            'success' => false, 
            'message' => 'Stok tidak mencukupi (Tersedia: ' . $stok_tersedia . ', di Keranjang: ' . $current_qty . ')'
        ]);
        exit;
    }
    
    // Update quantity
    $_SESSION['cart'][$cart_key]['qty'] = $new_qty;
} else {
    // Validasi stok awal
    if ($qty > $stok_tersedia) {
        echo json_encode([
            'success' => false, 
            'message' => 'Stok tidak mencukupi. Tersedia: ' . $stok_tersedia
        ]);
        exit;
    }
    
    // Tambah item baru
    $_SESSION['cart'][$cart_key] = [
        'id_produk' => $id_produk,
        'nama_produk' => $produk['nama_produk'], // Simpan nama untuk referensi cepat
        'harga' => $produk['harga'],             // Simpan harga (opsional, sebaiknya ambil dari DB live saat checkout)
        'qty' => $qty,
        'size' => $size,
        'color' => $color
    ];
}

// Hitung total item di cart untuk badge
$total_items = count($_SESSION['cart']);

echo json_encode([
    'success' => true,
    'message' => 'Produk berhasil ditambahkan ke keranjang',
    'cart_count' => $total_items
]);
?>
