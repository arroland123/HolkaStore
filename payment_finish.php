<?php
session_start();
include 'koneksi.php';

// Ambil parameter dari Midtrans redirect
$order_id          = isset($_GET['order_id'])          ? $_GET['order_id']          : '';
$transaction_status = isset($_GET['transaction_status']) ? $_GET['transaction_status'] : '';
$status_code       = isset($_GET['status_code'])       ? $_GET['status_code']       : '';

// Ambil data order dari database
$order = null;
if (!empty($order_id)) {
    $safe_order_id = mysqli_real_escape_string($conn, $order_id);
    $result = mysqli_query($conn, "SELECT * FROM orders WHERE order_number = '$safe_order_id'");
    if ($result && mysqli_num_rows($result) > 0) {
        $order = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - Holka Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
        }
        .icon-circle {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, #00c9a7, #00a0e3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
            box-shadow: 0 0 40px rgba(0,201,167,0.4);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 40px rgba(0,201,167,0.4); }
            50%       { box-shadow: 0 0 60px rgba(0,201,167,0.7); }
        }
        h1 { color: #fff; font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .subtitle { color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-bottom: 30px; }
        .order-info {
            background: rgba(255,255,255,0.05);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 28px;
            text-align: left;
        }
        .order-info-row {
            display: flex; justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-size: 0.9rem;
        }
        .order-info-row:last-child { border-bottom: none; }
        .order-info-row .label { color: rgba(255,255,255,0.5); }
        .order-info-row .value { color: #fff; font-weight: 600; }
        .badge-paid {
            display: inline-block;
            background: linear-gradient(135deg, #00c9a7, #00a0e3);
            color: #fff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: 50px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 6px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #00c9a7, #00a0e3);
            color: #fff;
            border: none;
            box-shadow: 0 8px 24px rgba(0,201,167,0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,201,167,0.4); }
        .btn-secondary {
            background: transparent;
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.08); color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">✓</div>
        <h1>Pembayaran Berhasil!</h1>
        <p class="subtitle">Terima kasih telah berbelanja di Holka Store. Pesanan Anda sedang diproses.</p>

        <?php if ($order): ?>
        <div class="order-info">
            <div class="order-info-row">
                <span class="label">No. Pesanan</span>
                <span class="value"><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="order-info-row">
                <span class="label">Total Pembayaran</span>
                <span class="value">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
            <div class="order-info-row">
                <span class="label">Status Pembayaran</span>
                <span class="value"><span class="badge-paid">Lunas</span></span>
            </div>
            <?php if (!empty($order['no_resi'])): ?>
            <div class="order-info-row">
                <span class="label">No. Resi Pengiriman</span>
                <span class="value" style="color: #00c9a7; font-weight: 700;"><?= htmlspecialchars($order['no_resi']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif (!empty($order_id)): ?>
        <div class="order-info">
            <div class="order-info-row">
                <span class="label">No. Pesanan</span>
                <span class="value"><?= htmlspecialchars($order_id) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-primary">Lanjut Belanja</a>
    </div>
</body>
</html>
