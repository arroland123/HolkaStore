<?php
// Payment Notification Handler from Midtrans (Using Official Library)
include 'koneksi.php';
include 'midtrans_config.php';

// Log file untuk debugging
$log_file = 'midtrans_notification.log';

// Log raw notification
$raw_input = file_get_contents('php://input');
file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Raw Notification: " . $raw_input . PHP_EOL, FILE_APPEND);

try {
    // Use Midtrans Notification Library
    $notif = handleMidtransNotification();
    
    if (!$notif) {
        http_response_code(400);
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Failed to parse notification" . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Invalid notification']);
        exit;
    }
    
    // Extract notification data
    $order_id = $notif['order_id'];
    $transaction_id = $notif['transaction_id'];
    $transaction_status = $notif['transaction_status'];
    $fraud_status = isset($notif['fraud_status']) ? $notif['fraud_status'] : '';
    $payment_type = $notif['payment_type'];
    $transaction_time = $notif['transaction_time'];
    
    // Log verified notification
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Valid Notification for order: $order_id" . PHP_EOL, FILE_APPEND);
    
    // Get order from database
    $query = "SELECT * FROM orders WHERE order_number = '" . mysqli_real_escape_string($conn, $order_id) . "'";
    $result_order = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result_order) == 0) {
        http_response_code(404);
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Order not found: $order_id" . PHP_EOL, FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }
    
    $order = mysqli_fetch_assoc($result_order);
    
    // Determine order status based on transaction status
    $order_status = $order['status'];
    $payment_status = $order['payment_status'];
    
    // Map Midtrans status to order status
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'accept') {
            $order_status = 'processing';
            $payment_status = 'paid';
        } else if ($fraud_status == 'challenge') {
            $order_status = 'pending';
            $payment_status = 'unpaid';
        }
    } else if ($transaction_status == 'settlement') {
        $order_status = 'processing';
        $payment_status = 'paid';
    } else if ($transaction_status == 'pending') {
        $order_status = 'pending';
        $payment_status = 'unpaid';
    } else if ($transaction_status == 'deny') {
        $order_status = 'cancelled';
        $payment_status = 'unpaid';
    } else if ($transaction_status == 'expire') {
        $order_status = 'cancelled';
        $payment_status = 'unpaid';
    } else if ($transaction_status == 'cancel') {
        $order_status = 'cancelled';
        $payment_status = 'unpaid';
    }
    
    // Update order
    $update_query = "UPDATE orders SET 
        status = '$order_status',
        payment_status = '$payment_status',
        midtrans_transaction_id = '" . mysqli_real_escape_string($conn, $transaction_id) . "',
        midtrans_status = '" . mysqli_real_escape_string($conn, $transaction_status) . "',
        midtrans_payment_type = '" . mysqli_real_escape_string($conn, $payment_type) . "'";
    
    if ($payment_status == 'paid' && $order['paid_at'] == null) {
        $update_query .= ", paid_at = '" . mysqli_real_escape_string($conn, $transaction_time) . "'";
    }
    
    $update_query .= ", updated_at = NOW() WHERE id = {$order['id']}";
    
    if (mysqli_query($conn, $update_query)) {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Order updated successfully: $order_id - Status: $order_status, Payment: $payment_status" . PHP_EOL, FILE_APPEND);
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Notification processed']);
    } else {
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Database error: " . mysqli_error($conn) . PHP_EOL, FILE_APPEND);
        
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    
} catch (Exception $e) {
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Exception: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
