<?php
include 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query order detail
$query = "SELECT * FROM orders WHERE id = $id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "Pesanan tidak ditemukan.";
    exit;
}

$order = mysqli_fetch_assoc($result);

// Query items
$items_query = "SELECT * FROM order_items WHERE order_id = $id";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order['order_number'] ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-slate-50 p-6 md:p-12">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="admin.php" class="text-blue-600 font-bold flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <span class="bg-blue-100 text-blue-700 px-4 py-1.5 rounded-full font-bold text-sm uppercase">
                <?= $order['order_number'] ?>
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Order Header -->
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-800 mb-4">Informasi Pelanggan</h1>
                        <div class="space-y-2">
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider">Nama</p>
                            <p class="text-slate-800 font-medium"><?= htmlspecialchars($order['customer_name']) ?></p>
                            
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mt-4">Kontak</p>
                            <p class="text-slate-800"><?= htmlspecialchars($order['customer_email']) ?> / <?= htmlspecialchars($order['customer_phone']) ?></p>
                            
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mt-4">Alamat Pengiriman</p>
                            <p class="text-slate-800 leading-relaxed">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?><br>
                                <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['province']) ?> <?= htmlspecialchars($order['postal_code']) ?>
                            </p>
                            
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mt-4">Kurir Pilihan</p>
                            <p class="text-slate-800 font-medium"><?= htmlspecialchars($order['courier'] ?? '-') ?></p>
                            
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-wider mt-4">Nomor Resi</p>
                            <p class="text-slate-800 font-medium"><?= !empty($order['no_resi']) ? htmlspecialchars($order['no_resi']) : '<span class="text-slate-400 italic">Belum diinput</span>' ?></p>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-800 mb-4">Status Pembayaran</h1>
                        <div class="bg-white p-6 rounded-xl border border-slate-200 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold text-xs uppercase">Metode</span>
                                <span class="font-bold text-blue-600"><?= strtoupper($order['midtrans_payment_type'] ?? $order['payment_method']) ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold text-xs uppercase">Status</span>
                                <?php
                                $status = strtolower($order['payment_status']);
                                $badge = $status == 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                                ?>
                                <span class="<?= $badge ?> px-3 py-1 rounded-full text-xs font-bold uppercase">
                                    <?= $status == 'paid' ? 'LUNAS' : 'PENDING' ?>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold text-xs uppercase">Midtrans ID</span>
                                <span class="text-xs text-slate-400 font-mono"><?= $order['midtrans_transaction_id'] ?? '-' ?></span>
                            </div>
                            <?php if($order['paid_at']): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-bold text-xs uppercase">Waktu Bayar</span>
                                <span class="text-sm font-medium text-slate-700"><?= date('d M Y H:i', strtotime($order['paid_at'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-blue-600"></i> Detail Item
                </h2>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Produk</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Harga</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center">Qty</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Size: <?= $item['size'] ?> | Color: <?= $item['color'] ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 text-slate-600 font-bold text-center"><?= $item['quantity'] ?></td>
                                <td class="px-6 py-4 text-slate-800 font-bold text-right">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="bg-slate-50/50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-slate-500 font-bold text-sm uppercase">Subtotal</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-800">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-slate-500 font-bold text-sm uppercase">Biaya Kirim</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-800">Rp <?= number_format($order['shipping_cost'], 0, ',', '.') ?></td>
                            </tr>
                            <?php if ($order['tax'] > 0): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-slate-500 font-bold text-sm uppercase">Pajak (11%)</td>
                                <td class="px-6 py-3 text-right font-bold text-slate-800">Rp <?= number_format($order['tax'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="bg-blue-600 text-white">
                                <td colspan="3" class="px-6 py-4 text-right font-extrabold text-lg uppercase">Total Harga</td>
                                <td class="px-6 py-4 text-right font-extrabold text-lg">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
