<?php
header('Content-Type: application/json');
include 'rajaongkir_config.php';

$destination = isset($_GET['destination_city_id']) ? (int)$_GET['destination_city_id'] : 0;
$weight      = isset($_GET['weight']) ? (int)$_GET['weight'] : 0;
$courier     = isset($_GET['courier']) ? trim($_GET['courier']) : '';

if ($destination <= 0) {
    echo json_encode(['success' => false, 'message' => 'Kota tujuan tidak valid.']);
    exit;
}

if ($weight <= 0) {
    $weight = 500; // default 500 gram jika kosong
}

if (empty($courier)) {
    echo json_encode(['success' => false, 'message' => 'Kurir harus dipilih.']);
    exit;
}

// Konversi nama kurir ke format RajaOngkir
$courier_code = strtolower($courier);
if ($courier_code == 'j&t' || $courier_code == 'jnt') {
    $courier_code = 'jnt';
}

$params = [
    'origin' => RAJAONGKIR_ORIGIN_CITY_ID,
    'destination' => $destination,
    'weight' => $weight,
    'courier' => $courier_code
];

$res = callRajaOngkirAPI('cost', $params, 'POST');

// Fallback jika API gagal (misalnya karena akun Starter tidak mendukung J&T, atau API key salah)
if (!$res['success']) {
    // Jika kurir adalah JNT/J&T dan gagal karena batasan akun Starter,
    // kita sediakan fallback harga tetap (flat rate) Rp 15.000 agar web tidak error dan pembeli tetap bisa checkout.
    if ($courier_code == 'jnt' || strpos(strtolower($res['message']), 'starter') !== false) {
        $mock_cost = 15000; // default flat rate Rp 15.000
        $result_fallback = [
            'success' => true,
            'results' => [
                [
                    'code' => strtoupper($courier_code),
                    'name' => $courier_code == 'jnt' ? 'J&T Express (Fallback)' : 'JNE Express (Fallback)',
                    'costs' => [
                        [
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost' => [
                                [
                                    'value' => $mock_cost,
                                    'etd' => '2-4',
                                    'note' => ''
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        echo json_encode($result_fallback);
        exit;
    }
}

echo json_encode($res);
?>
