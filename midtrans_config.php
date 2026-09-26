<?php
/**
 * MIDTRANS CONFIGURATION - CLEAN & STABLE VERSION
 */

// Load Library secara manual untuk menghindari error loader
$base_dir = dirname(__FILE__) . '/midtrans-php-master/Midtrans/';

require_once $base_dir . 'Config.php';
require_once $base_dir . 'ApiRequestor.php';
require_once $base_dir . 'SnapApiRequestor.php';
require_once $base_dir . 'Snap.php';
require_once $base_dir . 'Notification.php';
require_once $base_dir . 'Transaction.php';
require_once $base_dir . 'Sanitizer.php';

// Konfigurasi Key
\Midtrans\Config::$serverKey = 'Mid-server-LnE3sYiEfxT2JVZMfAsWaLHt';
\Midtrans\Config::$clientKey = 'Mid-client-dqTFTSR0lLax5mvX';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// PERBAIKAN FATAL: Gunakan array index yang aman untuk CURLOPT_HTTPHEADER (10023)
// Kita set manual agar tidak menyebabkan 'Undefined array key 10023'
\Midtrans\Config::$curlOptions = [
    CURLOPT_SSL_VERIFYPEER => false,
    10023 => [] // Ini adalah nilai dari CURLOPT_HTTPHEADER
];

/**
 * Fungsi untuk Membuat Snap Token
 */
function createMidtransSnapToken($order) {
    $transaction = [
        'transaction_details' => [
            'order_id' => $order['order_number'],
            'gross_amount' => (int) $order['total']
        ],
        'item_details' => array_merge(
            array_map(function($item) {
                return [
                    'id' => 'P-'.$item['id_produk'],
                    'price' => (int)$item['harga'],
                    'quantity' => (int)$item['qty'],
                    'name' => substr($item['nama_produk'], 0, 50)
                ];
            }, $order['items']),
            $order['shipping_cost'] > 0 ? [[
                'id' => 'SHIP', 'price' => (int)$order['shipping_cost'], 'quantity' => 1, 'name' => 'Ongkir'
            ]] : [],
            $order['tax'] > 0 ? [[
                'id' => 'TAX', 'price' => (int)$order['tax'], 'quantity' => 1, 'name' => 'Pajak'
            ]] : []
        ),
        'customer_details' => [
            'first_name' => $order['customer_name'],
            'email' => $order['customer_email'],
            'phone' => $order['customer_phone']
        ]
    ];

    try {
        return ['success' => true, 'snap_token' => \Midtrans\Snap::getSnapToken($transaction)];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
