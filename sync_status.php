<?php
/**
 * SYNC PAYMENT STATUS FROM MIDTRANS
 * Digunakan untuk mengupdate status secara manual di localhost
 */
include 'koneksi.php';
include 'midtrans_config.php';

header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid']);
    exit;
}

try {
    // 1. Ambil status dari Midtrans API
    $status = \Midtrans\Transaction::status($order_id);
    
    $transaction_status = $status->transaction_status;
    $payment_type = $status->payment_type;
    $transaction_id = $status->transaction_id;
    $fraud_status = isset($status->fraud_status) ? $status->fraud_status : '';
    $transaction_time = $status->transaction_time;

    // 2. Mapping Status
    $order_status = 'pending';
    $pay_status = 'unpaid';

    if ($transaction_status == 'capture') {
        if ($fraud_status == 'accept') { $order_status = 'processing'; $pay_status = 'paid'; }
    } else if ($transaction_status == 'settlement') {
        $order_status = 'processing'; $pay_status = 'paid';
    } else if ($transaction_status == 'pending') {
        $order_status = 'pending'; $pay_status = 'unpaid';
    } else if (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
        $order_status = 'cancelled'; $pay_status = 'unpaid';
    }

    // 3. Update Database
    $query = "UPDATE orders SET 
                status = '$order_status', 
                payment_status = '$pay_status',
                midtrans_status = '$transaction_status',
                midtrans_payment_type = '$payment_type',
                midtrans_transaction_id = '$transaction_id'";
    
    if ($pay_status == 'paid') {
        $query .= ", paid_at = '$transaction_time'";
    }
    
    $query .= " WHERE order_number = '$order_id'";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Status berhasil disinkronkan!',
            'transaction_status' => $transaction_status,
            'payment_status' => $pay_status
        ]);
    } else {
        throw new Exception("Gagal update database: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
