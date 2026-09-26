<?php
session_start();
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Belum Selesai - Holka Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 480px; width: 100%;
            text-align: center;
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
        }
        .icon-circle {
            width: 90px; height: 90px;
            background: linear-gradient(135deg, #f7971e, #ffd200);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
            box-shadow: 0 0 40px rgba(247,151,30,0.4);
        }
        h1 { color: #fff; font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
        .subtitle { color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-bottom: 30px; }
        .info-box {
            background: rgba(247,151,30,0.1);
            border: 1px solid rgba(247,151,30,0.3);
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 28px;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-block; padding: 14px 32px;
            border-radius: 50px; font-family: 'Outfit', sans-serif;
            font-size: 0.95rem; font-weight: 600;
            text-decoration: none; cursor: pointer;
            transition: all 0.3s ease; margin: 6px;
        }
        .btn-warning {
            background: linear-gradient(135deg, #f7971e, #ffd200);
            color: #1a1a2e; border: none;
            box-shadow: 0 8px 24px rgba(247,151,30,0.3);
        }
        .btn-warning:hover { transform: translateY(-2px); }
        .btn-secondary {
            background: transparent; color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.08); color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">⏳</div>
        <h1>Pembayaran Tertunda</h1>
        <p class="subtitle">Anda belum menyelesaikan pembayaran. Pesanan masih tersimpan.</p>

        <?php if (!empty($order_id)): ?>
        <div class="info-box">
            Pesanan <strong><?= htmlspecialchars($order_id) ?></strong> masih berstatus <strong>pending</strong>.<br>
            Selesaikan pembayaran sebelum pesanan Anda kedaluwarsa.
        </div>
        <?php endif; ?>

        <a href="checkout.php" class="btn btn-warning">Selesaikan Pembayaran</a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
    </div>
</body>
</html>
