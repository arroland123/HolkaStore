<?php
session_start();
include 'koneksi.php';
include 'midtrans_config.php';
include 'rajaongkir_config.php';

// Set header JSON agar AJAX di checkout.php bisa membacanya
header('Content-Type: application/json');

// 1. Validasi Keranjang
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja Anda kosong.']);
    exit;
}

// 2. Ambil & Sanitize data dari Form
$full_name   = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
$email       = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$phone       = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
$address     = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
$city        = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
$province    = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
$postal_code = mysqli_real_escape_string($conn, $_POST['postal_code'] ?? '');
$courier     = mysqli_real_escape_string($conn, $_POST['courier'] ?? '');
$notes       = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
$city_id     = (int)($_POST['city_id'] ?? 0);

if (empty($full_name) || empty($phone) || empty($address) || empty($courier) || $city_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mohon lengkapi Nama, HP, Alamat, dan Pilihan Kurir.']);
    exit;
}

// 3. Proses Hitung Total & Validasi Stok
$subtotal = 0;
$total_weight = 0;
$order_items = [];

foreach ($_SESSION['cart'] as $item) {
    $id_produk = (int)$item['id_produk'];
    
    // PERBAIKAN: Hanya menggunakan id_produk agar tidak error 'Unknown column id'
    $res = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk = $id_produk LIMIT 1");
    
    if ($res && mysqli_num_rows($res) > 0) {
        $p = mysqli_fetch_assoc($res);
        $harga = $p['harga'];
        $qty = (int)$item['qty'];
        
        $berat = isset($p['berat']) ? (int)$p['berat'] : 500;
        $total_weight += $berat * $qty;
        
        $item_total = $harga * $qty;
        $subtotal += $item_total;
        
        $order_items[] = [
            'id_produk' => $id_produk,
            'nama_produk' => $p['nama_produk'],
            'harga' => (int)$harga,
            'qty' => $qty,
            'size' => $item['size'] ?? '-',
            'color' => $item['color'] ?? '-',
            'subtotal' => $item_total
        ];
    }
}

// Hitung ongkos kirim real-time via RajaOngkir
$shipping = 0;
$courier_code = strtolower($courier);
if ($courier_code == 'j&t' || $courier_code == 'jnt') {
    $courier_code = 'jnt';
}

$params = [
    'origin' => RAJAONGKIR_ORIGIN_CITY_ID,
    'destination' => $city_id,
    'weight' => $total_weight,
    'courier' => $courier_code
];

$res_ongkir = callRajaOngkirAPI('cost', $params, 'POST');

if ($res_ongkir['success'] && isset($res_ongkir['results'][0]['costs']) && !empty($res_ongkir['results'][0]['costs'])) {
    $costs_list = $res_ongkir['results'][0]['costs'];
    $selected_cost = null;
    $preferred = ['REG', 'REGULER', 'EZ', 'OKE', 'CTC'];
    foreach ($preferred as $pref) {
        foreach ($costs_list as $c) {
            if (strcasecmp($c['service'], $pref) === 0) {
                $selected_cost = $c;
                break 2;
            }
        }
    }
    if (!$selected_cost) {
        // Cari yang termurah
        $selected_cost = $costs_list[0];
        foreach ($costs_list as $c) {
            if ($c['cost'][0]['value'] < $selected_cost['cost'][0]['value']) {
                $selected_cost = $c;
            }
        }
    }
    $shipping = (int)$selected_cost['cost'][0]['value'];
} else {
    // Fallback if API fails (e.g. Starter limit on JNT)
    $shipping = 15000;
}

$tax = 0;
$total_akhir = $subtotal + $shipping;

// 4. Buat Order di Database
$order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

mysqli_begin_transaction($conn);

try {
    // Simpan ke tabel orders
    $sql_order = "INSERT INTO orders (
        order_number, customer_name, customer_email, customer_phone, 
        shipping_address, city, province, postal_code, notes, courier, destination_city_id,
        subtotal, shipping_cost, tax, total, status, payment_status, created_at
    ) VALUES (
        '$order_number', '$full_name', '$email', '$phone', 
        '$address', '$city', '$province', '$postal_code', '$notes', '$courier', $city_id,
        $subtotal, $shipping, $tax, $total_akhir, 'pending', 'unpaid', NOW()
    )";

    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("Gagal simpan order: " . mysqli_error($conn));
    }

    $db_order_id = mysqli_insert_id($conn);

    // Simpan Items
    foreach ($order_items as $oi) {
        $p_name = mysqli_real_escape_string($conn, $oi['nama_produk']);
        $sql_item = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, size, color, subtotal) 
                     VALUES ($db_order_id, {$oi['id_produk']}, '$p_name', {$oi['harga']}, {$oi['qty']}, '{$oi['size']}', '{$oi['color']}', {$oi['subtotal']})";
        mysqli_query($conn, $sql_item);
    }

    // 5. Minta Token ke Midtrans
    $midtrans_data = [
        'order_number' => $order_number,
        'total' => $total_akhir,
        'items' => $order_items,
        'shipping_cost' => $shipping,
        'tax' => $tax,
        'customer_name' => $full_name,
        'customer_email' => $email,
        'customer_phone' => $phone,
        'shipping_address' => $address,
        'city' => $city,
        'postal_code' => $postal_code
    ];

    $snap = createMidtransSnapToken($midtrans_data);

    if ($snap['success']) {
        $token = $snap['snap_token'];
        mysqli_query($conn, "UPDATE orders SET snap_token = '$token' WHERE id = $db_order_id");
        mysqli_commit($conn);
        
        // Hapus cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true, 
            'snap_token' => $token, 
            'order_number' => $order_number
        ]);
    } else {
        throw new Exception("Midtrans Error: " . $snap['message']);
    }

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
