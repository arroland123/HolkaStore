<?php
include 'koneksi.php';

// Update Promo
if(isset($_POST['update_promo'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['promo_judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['promo_deskripsi']);
    
    // Cek apakah tabel promo sudah ada isinya
    $cek_promo = mysqli_query($conn, "SELECT * FROM promo LIMIT 1");
    if($cek_promo && mysqli_num_rows($cek_promo) > 0) {
        $promo_data = mysqli_fetch_assoc($cek_promo);
        $gambar_lama = $promo_data['gambar'];
    } else {
        $gambar_lama = 'https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80';
    }

    $gambar_update = $gambar_lama;
    if(!empty($_FILES['promo_gambar']['name'])) {
        $gambar = $_FILES['promo_gambar']['name'];
        $target = "assets/img/" . basename($gambar);
        if(move_uploaded_file($_FILES['promo_gambar']['tmp_name'], $target)) {
            $gambar_update = mysqli_real_escape_string($conn, $gambar);
        }
    }

    if($cek_promo && mysqli_num_rows($cek_promo) > 0) {
        $sql = "UPDATE promo SET judul = '$judul', deskripsi = '$deskripsi', gambar = '$gambar_update'";
    } else {
        $sql = "INSERT INTO promo (judul, deskripsi, gambar) VALUES ('$judul', '$deskripsi', '$gambar_update')";
    }
    
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Promo berhasil diupdate!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate promo: " . mysqli_error($conn) . "');</script>";
    }
}

// Tambah Produk
if(isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['id_kategori'];
    $gambar = $_FILES['gambar']['name'];
    $gambar_db = mysqli_real_escape_string($conn, $gambar);
    
    // Ambil detail stok ukuran dan konversi ke JSON
    $stok_size = isset($_POST['stok_size']) ? $_POST['stok_size'] : [];
    $stok_size_filtered = [];
    foreach ($stok_size as $size => $qty) {
        // Hanya simpan jika input tidak disabled (tidak bernilai kosong/negatif)
        $stok_size_filtered[$size] = max(0, intval($qty));
    }
    $stok_detail_json = json_encode($stok_size_filtered);
    
    $target = "assets/img/" . basename($gambar);
    if(move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
        $sql = "INSERT INTO produk (nama_produk, deskripsi, harga, stok, id_kategori, gambar, stok_detail) 
                VALUES ('$nama', '$deskripsi', '$harga', '$stok', '$kategori', '$gambar_db', '$stok_detail_json')";
        mysqli_query($conn, $sql);
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupload gambar!');</script>";
    }
}

// Hapus Produk
if(isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM produk WHERE id_produk = $id");
    header("Location: admin.php");
    exit;
}

// Hapus Pesanan
if(isset($_GET['hapus_pesanan'])) {
    $id_pesanan = (int)$_GET['hapus_pesanan'];
    mysqli_query($conn, "DELETE FROM orders WHERE id = $id_pesanan");
    $redirect_url = 'admin.php';
    if(isset($_GET['order_page']) && is_numeric($_GET['order_page'])) {
        $redirect_url .= '?order_page=' . (int)$_GET['order_page'];
    }
    header("Location: " . $redirect_url);
    exit;
}

// Logout
if(isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: login.php");
    exit;
}

// AUTO-SYNC LOGIC (Localhost Helper)
// Mencari pesanan pending dalam 1 jam terakhir untuk di-update otomatis
include 'midtrans_config.php';
$pending_orders = mysqli_query($conn, "SELECT order_number FROM orders WHERE payment_status = 'unpaid' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
while($po = mysqli_fetch_assoc($pending_orders)) {
    try {
        $status = \Midtrans\Transaction::status($po['order_number']);
        $ts = $status->transaction_status;
        $ps = 'unpaid';
        $os = 'pending';
        
        if ($ts == 'settlement' || $ts == 'capture') { $ps = 'paid'; $os = 'processing'; }
        else if (in_array($ts, ['deny', 'expire', 'cancel'])) { $ps = 'unpaid'; $os = 'cancelled'; }

        if ($ps == 'paid') {
            mysqli_query($conn, "UPDATE orders SET payment_status = '$ps', status = '$os', midtrans_status = '$ts', midtrans_payment_type = '{$status->payment_type}', midtrans_transaction_id = '{$status->transaction_id}', paid_at = '{$status->transaction_time}' WHERE order_number = '{$po['order_number']}'");
        }
    } catch (Exception $e) { /* skip if error */ }
}

// Query Produk
$result = mysqli_query($conn, "SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id_produk DESC");

// Pagination for Orders
$limit_orders = 10;
$page_orders = isset($_GET['order_page']) && is_numeric($_GET['order_page']) ? (int)$_GET['order_page'] : 1;
if ($page_orders < 1) $page_orders = 1;
$offset_orders = ($page_orders - 1) * $limit_orders;

// Get total orders count
$total_orders_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$total_orders_row = mysqli_fetch_assoc($total_orders_query);
$total_orders = $total_orders_row['total'] ?? 0;
$total_pages_orders = ceil($total_orders / $limit_orders);

// Query Pesanan (Disesuaikan ke tabel orders untuk Midtrans)
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT $limit_orders OFFSET $offset_orders");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard - Holka Store</title>
    <!-- Google Fonts: Manrope -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <!-- Tailwind CSS v3 -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        holka: {
                            primary: '#1e40af',
                            secondary: '#3b82f6',
                            accent: '#f8fafc',
                            success: '#10b981',
                            warning: '#f59e0b',
                            danger: '#ef4444'
                        }
                    },
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        /* Scale up the entire UI */
        html { font-size: 18px; }
        @media (min-width: 1024px) {
            html { font-size: 20px; }
        }
        body { background-color: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="font-sans text-slate-900">

<!-- ===== HEADER ===== -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <div class="logo flex items-center gap-2">
                <i class="fas fa-shopping-bag text-holka-primary text-xl"></i>
                <span class="text-2xl font-extrabold tracking-tight text-holka-primary">Holka Store</span>
            </div>
            <!-- Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-slate-600 hover:text-holka-primary font-semibold transition-colors" href="index.php">
                    <i class="fas fa-home mr-1"></i> Toko
                </a>
                <a class="text-holka-primary border-b-2 border-holka-primary font-bold py-1" href="admin.php">
                    <i class="fas fa-user-lock mr-1"></i> Admin
                </a>
            </nav>
            <!-- Logout -->
            <div class="flex items-center gap-4">
                <a href="admin.php?logout=true"
                   onclick="return confirm('Yakin ingin logout?')"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-full font-bold transition-all flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ===== MAIN ===== -->
<main class="container mx-auto px-4 py-8 lg:px-8">

    <!-- Dashboard Header -->
    <section class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="p-4 bg-white rounded-2xl shadow-sm border border-slate-200">
                <i class="fas fa-cog text-3xl text-holka-primary"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">Pengelola Produk Holka Store</h1>
                <p class="text-slate-500 font-medium">Tampilan Pengelola Produk Holka Store</p>
            </div>
        </div>
        <div class="bg-white px-6 py-3 rounded-xl shadow-sm border border-slate-200 flex items-center gap-4">
            <div class="text-right">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Waktu Server</p>
                <p class="text-sm font-bold text-slate-700"><?= date('d M Y - H:i') ?></p>
            </div>
            <i class="far fa-clock text-holka-secondary text-xl"></i>
        </div>
    </section>

    <div class="flex flex-col gap-8">

        <!-- ===== FORM TAMBAH PRODUK ===== -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center gap-2 bg-white">
                <i class="fas fa-plus-circle text-holka-primary text-xl"></i>
                <h2 class="text-xl font-bold text-slate-800">Tambah Produk Baru</h2>
            </div>
            <div class="p-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <!-- Row: Nama & Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-bold text-slate-600" for="nama">
                                <i class="fas fa-tag mr-1 text-holka-secondary"></i> Nama Produk
                            </label>
                            <input type="text" id="nama" name="nama"
                                   placeholder="Masukkan nama produk"
                                   required
                                   class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-bold text-slate-600" for="kategori">
                                <i class="fas fa-list mr-1 text-holka-secondary"></i> Kategori
                            </label>
                            <select id="kategori" name="id_kategori" required
                                    class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                $kats = mysqli_query($conn, "SELECT * FROM kategori");
                                while($k = mysqli_fetch_assoc($kats)) {
                                    echo "<option value='" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="deskripsi">
                            <i class="fas fa-align-left mr-1 text-holka-secondary"></i> Deskripsi Produk
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="4"
                                  placeholder="Masukkan deskripsi produk" required
                                  class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition resize-none"></textarea>
                    </div>

                    <!-- Row: Harga & Stok -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-bold text-slate-600" for="harga">
                                <i class="fas fa-money-bill-wave mr-1 text-holka-secondary"></i> Harga (Rp)
                            </label>
                            <input type="number" id="harga" name="harga"
                                   placeholder="Masukkan harga" required
                                   class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-bold text-slate-600" for="stok">
                                <i class="fas fa-boxes mr-1 text-holka-secondary"></i> Total Stok (Otomatis)
                            </label>
                            <input type="number" id="stok" name="stok"
                                   placeholder="Total stok otomatis" required readonly
                                   class="border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-500 focus:outline-none transition"/>
                        </div>
                    </div>

                    <!-- Detail Stok per Ukuran -->
                    <div class="border border-slate-100 bg-slate-50/50 rounded-2xl p-5 space-y-4">
                        <h4 class="text-sm font-bold text-slate-700 flex items-center gap-1.5 border-b border-slate-100 pb-2">
                            <i class="fas fa-cubes text-holka-secondary"></i> Stok per Ukuran
                        </h4>
                        
                        <!-- Pilihan Ukuran Baju/Kaos/Jaket/dll (S - XXL) -->
                        <div id="size-baju-group" class="grid grid-cols-5 gap-3">
                            <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $sz): ?>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-slate-500 text-center" for="stok_<?= $sz ?>">Ukuran <?= $sz ?></label>
                                <input type="number" id="stok_<?= $sz ?>" name="stok_size[<?= $sz ?>]" min="0" value="0"
                                       class="size-stock-input text-center border border-slate-200 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pilihan Ukuran Celana (28 - 36) -->
                        <div id="size-celana-group" class="grid grid-cols-3 sm:grid-cols-9 gap-3 hidden">
                            <?php foreach (range(28, 36) as $sz): ?>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-bold text-slate-500 text-center" for="stok_<?= $sz ?>">No. <?= $sz ?></label>
                                <input type="number" id="stok_<?= $sz ?>" name="stok_size[<?= $sz ?>]" min="0" value="0" disabled
                                       class="size-stock-input text-center border border-slate-200 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Gambar -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="gambar">
                            <i class="fas fa-image mr-1 text-holka-secondary"></i> Gambar Produk
                        </label>
                        <input type="file" id="gambar" name="gambar" accept="image/*" required
                               class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary transition file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-holka-primary hover:file:bg-blue-200"/>
                        <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, JPEG (Maks. 2MB) | Recommended: 800x800px</p>
                    </div>

                    <!-- Submit -->
                    <div class="pt-2">
                        <button type="submit" name="tambah"
                                class="bg-holka-primary hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Tambah Produk
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ===== TABEL PRODUK ===== -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-database text-holka-primary"></i> Daftar Produk
                </h2>
                <span class="bg-blue-100 text-holka-primary text-xs font-bold px-3 py-1 rounded-full">
                    <?= mysqli_num_rows($result) ?> Produk
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Gambar</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Nama Produk</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $no = 1;
                        mysqli_data_seek($result, 0);
                        while($row = mysqli_fetch_assoc($result)):
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500 font-bold"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <img src="assets/img/<?= htmlspecialchars($row['gambar']) ?>"
                                     alt="<?= htmlspecialchars($row['nama_produk']) ?>"
                                     class="w-14 h-14 object-cover rounded-xl border border-slate-200"/>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <?= htmlspecialchars($row['nama_produk']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <?= htmlspecialchars(substr($row['deskripsi'], 0, 60)) ?>...
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-50 text-holka-primary text-xs font-bold px-2.5 py-1 rounded-full">
                                    <?= htmlspecialchars($row['nama_kategori'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-700">
                                Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div class="font-bold"><?= $row['stok'] ?> pcs</div>
                                <?php 
                                if (!empty($row['stok_detail'])) {
                                    $sd = json_decode($row['stok_detail'], true);
                                    if ($sd && is_array($sd)) {
                                        $details = [];
                                        foreach ($sd as $sz => $sqty) {
                                            $details[] = "$sz:$sqty";
                                        }
                                        echo '<div class="text-[10px] text-slate-400 mt-1">' . htmlspecialchars(implode(', ', $details)) . '</div>';
                                    }
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="edit_produk.php?id=<?= $row['id_produk'] ?>"
                                       class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-holka-primary text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin.php?hapus=<?= $row['id_produk'] ?>"
                                       onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                       class="inline-flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400 font-medium">
                                <i class="fas fa-box-open text-2xl mb-2 block"></i>
                                Belum ada produk
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== TABEL PESANAN ===== -->
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-holka-primary"></i>
                    Pesanan &amp; Pembayaran Masuk
                </h2>
                <?php
                $pending_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE payment_status = 'unpaid'");
                $pending = mysqli_fetch_assoc($pending_count);
                ?>
                <span class="bg-blue-100 text-holka-primary text-xs font-bold px-3 py-1 rounded-full">
                    <?= $pending['total'] ?? 0 ?> Belum Bayar
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">OrderID / No. Pesanan</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Pelanggan</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Tipe Bayar</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Status Bayar</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if($orders && mysqli_num_rows($orders) > 0):
                            while($order = mysqli_fetch_assoc($orders)):
                                // Badge status pembayaran
                                $pay_status = strtolower($order['payment_status'] ?? 'unpaid');
                                if($pay_status == 'paid') {
                                    $badge = 'bg-green-100 text-green-700';
                                    $label = 'Sudah Bayar';
                                } elseif($pay_status == 'unpaid') {
                                    $badge = 'bg-amber-100 text-amber-700';
                                    $label = 'Pending';
                                } else {
                                    $badge = 'bg-red-100 text-red-700';
                                    $label = ucfirst($pay_status);
                                }
                                
                                // Ikon Tipe Pembayaran (Midtrans)
                                $p_type = strtolower($order['midtrans_payment_type'] ?? '');
                                $p_icon = 'fa-credit-card text-slate-400';
                                if(strpos($p_type, 'va') !== false) $p_icon = 'fa-university text-holka-secondary';
                                if($p_type == 'gopay' || $p_type == 'shopeepay' || $p_type == 'qris') $p_icon = 'fa-qrcode text-holka-secondary';
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700"><?= $order['order_number'] ?></div>
                                <?php if(!empty($order['no_resi'])): ?>
                                    <div class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1" title="Nomor Resi Pengiriman">
                                        <i class="fas fa-truck"></i> Resi: <span class="font-mono"><?= htmlspecialchars($order['no_resi']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-[11px] text-slate-400 italic mt-1 flex items-center gap-1">
                                        <i class="fas fa-truck"></i> Belum ada resi
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900"><?= htmlspecialchars($order['customer_name'] ?? '') ?></div>
                                <div class="text-xs text-slate-500"><?= htmlspecialchars($order['customer_email'] ?? '') ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?= date('d M Y H:i', strtotime($order['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5 text-sm font-medium">
                                    <i class="fas <?= $p_icon ?>"></i> 
                                    <?= !empty($order['midtrans_payment_type']) ? strtoupper($order['midtrans_payment_type']) : strtoupper($order['payment_method']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="<?= $badge ?> text-[10px] font-extrabold px-2.5 py-1 rounded-full uppercase">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editResi(<?= $order['id'] ?>, '<?= htmlspecialchars($order['no_resi'] ?? '') ?>')" 
                                            class="p-2 text-slate-400 hover:text-emerald-500 transition-colors" 
                                            title="Input / Edit Nomor Resi">
                                        <i class="fas fa-shipping-fast"></i>
                                    </button>
                                    <button onclick="syncOrder('<?= $order['order_number'] ?>', this)" 
                                            class="p-2 text-slate-400 hover:text-holka-secondary transition-colors" 
                                            title="Sync Status Handly (Lokal/Dev)">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <a href="view_order.php?id=<?= $order['id'] ?>" class="p-2 text-slate-400 hover:text-holka-primary transition-colors" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="admin.php?hapus_pesanan=<?= $order['id'] ?>&order_page=<?= $page_orders ?>" 
                                       onclick="return confirm('Yakin ingin menghapus pesanan ini?')" 
                                       class="p-2 text-slate-400 hover:text-red-600 transition-colors" 
                                       title="Hapus Pesanan">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-medium">
                                <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                Belum ada pesanan masuk
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages_orders > 1): ?>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-white flex-col sm:flex-row gap-4">
                <div class="text-sm font-medium text-slate-500">
                    Menampilkan <span class="font-bold text-slate-800"><?= min($offset_orders + 1, $total_orders) ?></span>
                    sampai <span class="font-bold text-slate-800"><?= min($offset_orders + $limit_orders, $total_orders) ?></span>
                    dari <span class="font-bold text-slate-800"><?= $total_orders ?></span> pesanan
                </div>
                <div class="flex items-center gap-1">
                    <!-- Previous Button -->
                    <?php if ($page_orders > 1): ?>
                        <a href="admin.php?order_page=<?= $page_orders - 1 ?>" 
                           class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-holka-primary transition-all font-bold">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-100 text-slate-300 cursor-not-allowed font-bold">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $page_orders - 2);
                    $end_page = min($total_pages_orders, $page_orders + 2);

                    if ($start_page > 1) {
                        echo '<a href="admin.php?order_page=1" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-holka-primary transition-all font-bold">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="inline-flex items-center justify-center w-10 h-10 text-slate-400 font-bold">...</span>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++):
                        if ($i == $page_orders):
                    ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-holka-primary text-white font-extrabold shadow-sm">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="admin.php?order_page=<?= $i ?>" 
                           class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-holka-primary transition-all font-bold">
                            <?= $i ?>
                        </a>
                    <?php endif; endfor; ?>

                    <?php
                    if ($end_page < $total_pages_orders) {
                        if ($end_page < $total_pages_orders - 1) {
                            echo '<span class="inline-flex items-center justify-center w-10 h-10 text-slate-400 font-bold">...</span>';
                        }
                        echo '<a href="admin.php?order_page=' . $total_pages_orders . '" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-holka-primary transition-all font-bold">' . $total_pages_orders . '</a>';
                    }
                    ?>

                    <!-- Next Button -->
                    <?php if ($page_orders < $total_pages_orders): ?>
                        <a href="admin.php?order_page=<?= $page_orders + 1 ?>" 
                           class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-holka-primary transition-all font-bold">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-100 text-slate-300 cursor-not-allowed font-bold">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <!-- ===== FORM EDIT PROMO ===== -->
        <?php
        // Fetch current promo
        $query_promo = mysqli_query($conn, "SELECT * FROM promo LIMIT 1");
        $promo = $query_promo && mysqli_num_rows($query_promo) > 0 ? mysqli_fetch_assoc($query_promo) : [];
        ?>
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center gap-2 bg-white">
                <i class="fas fa-bullhorn text-holka-primary text-xl"></i>
                <h2 class="text-xl font-bold text-slate-800">Edit Promo Banner</h2>
            </div>
            <div class="p-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="promo_judul">
                            <i class="fas fa-heading mr-1 text-holka-secondary"></i> Judul Promo
                        </label>
                        <input type="text" id="promo_judul" name="promo_judul"
                               value="<?= htmlspecialchars($promo['judul'] ?? 'Summer Collection 2024') ?>"
                               required
                               class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition"/>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="promo_deskripsi">
                            <i class="fas fa-align-left mr-1 text-holka-secondary"></i> Deskripsi Promo
                        </label>
                        <textarea id="promo_deskripsi" name="promo_deskripsi" rows="3"
                                  required
                                  class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary focus:border-transparent transition resize-none"><?= htmlspecialchars($promo['deskripsi'] ?? 'Dapatkan diskon hingga 50% untuk koleksi musim panas terbaru.') ?></textarea>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-bold text-slate-600" for="promo_gambar">
                            <i class="fas fa-image mr-1 text-holka-secondary"></i> Gambar Promo (Kosongkan jika tidak ingin mengubah)
                        </label>
                        <?php if(!empty($promo['gambar'])): ?>
                            <div class="mb-2">
                                <img src="<?= strpos($promo['gambar'], 'http') === 0 ? $promo['gambar'] : 'assets/img/' . $promo['gambar'] ?>" alt="Current Promo" class="w-32 h-20 object-cover rounded-lg border border-slate-200">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="promo_gambar" name="promo_gambar" accept="image/*"
                               class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-holka-secondary transition file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-holka-primary hover:file:bg-blue-200"/>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="update_promo"
                                class="bg-holka-primary hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fas fa-save"></i> Simpan Perubahan Promo
                        </button>
                    </div>
                </form>
            </div>
        </section>

    </div><!-- end flex col gap -->
</main>

<script>
function syncOrder(orderId, btn) {
    const icon = btn.querySelector('i');
    icon.classList.add('fa-spin');
    btn.disabled = true;

    fetch('sync_status.php?order_id=' + orderId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Berhasil! Status saat ini: ' + data.transaction_status);
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi.');
            console.error(err);
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        });
}

function editResi(orderId, currentResi) {
    const resi = prompt("Masukkan / Edit Nomor Resi:", currentResi);
    if (resi === null) return; // User membatalkan
    
    // Kirim nomor resi ke update_resi.php
    fetch('update_resi.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId + '&no_resi=' + encodeURIComponent(resi)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.email_sent) {
                alert('Nomor resi berhasil diperbarui dan email pemberitahuan telah dikirim ke pembeli!');
            } else if (data.email_error) {
                alert('Nomor resi berhasil diperbarui! Namun, email gagal dikirim ke pembeli. Error: ' + data.email_error);
            } else {
                alert('Nomor resi berhasil diperbarui!');
            }
            window.location.reload();
        } else {
            alert('Gagal memperbarui nomor resi: ' + data.message);
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan koneksi.');
        console.error(err);
    });
}

// Logic untuk menampilkan form stok per ukuran berdasarkan kategori yang dipilih
document.addEventListener('DOMContentLoaded', function() {
    const kategoriSelect = document.getElementById('kategori');
    
    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', toggleSizeFields);
        // Trigger saat load pertama kali
        toggleSizeFields();
    }
    
    // Pasang listener input untuk setiap input stok ukuran agar total terupdate otomatis
    document.querySelectorAll('.size-stock-input').forEach(input => {
        input.addEventListener('input', calculateTotalStok);
        input.addEventListener('change', calculateTotalStok);
    });
});

function toggleSizeFields() {
    const select = document.getElementById('kategori');
    if (!select) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const categoryName = selectedOption ? selectedOption.text.toLowerCase() : '';
    
    // Cek jika kategori adalah Celana / Jeans (atau ID 3)
    const isCelana = categoryName.includes('celana') || categoryName.includes('jeans') || select.value == '3';
    
    const sizeBajuGroup = document.getElementById('size-baju-group');
    const sizeCelanaGroup = document.getElementById('size-celana-group');
    
    if (isCelana) {
        sizeCelanaGroup.classList.remove('hidden');
        sizeBajuGroup.classList.add('hidden');
        enableGroupInputs(sizeCelanaGroup, true);
        enableGroupInputs(sizeBajuGroup, false);
    } else {
        sizeCelanaGroup.classList.add('hidden');
        sizeBajuGroup.classList.remove('hidden');
        enableGroupInputs(sizeCelanaGroup, false);
        enableGroupInputs(sizeBajuGroup, true);
    }
    
    calculateTotalStok();
}

function enableGroupInputs(container, enable) {
    if (!container) return;
    const inputs = container.querySelectorAll('input');
    inputs.forEach(input => {
        input.disabled = !enable;
        if (!enable) {
            input.value = '0';
        }
    });
}

function calculateTotalStok() {
    const sizeBajuGroup = document.getElementById('size-baju-group');
    const sizeCelanaGroup = document.getElementById('size-celana-group');
    if (!sizeBajuGroup || !sizeCelanaGroup) return;
    
    let total = 0;
    const activeGroup = !sizeBajuGroup.classList.contains('hidden') ? sizeBajuGroup : sizeCelanaGroup;
    
    const inputs = activeGroup.querySelectorAll('input');
    inputs.forEach(input => {
        const val = parseInt(input.value) || 0;
        total += val;
    });
    
    const totalStokInput = document.getElementById('stok');
    if (totalStokInput) {
        totalStokInput.value = total;
    }
}
</script>

<!-- ===== FOOTER ===== -->
<footer class="mt-12 py-10 border-t border-slate-200 bg-white">
    <div class="container mx-auto px-4 text-center">
        <div class="flex items-center justify-center gap-2 mb-4">
            <div class="w-6 h-6 bg-slate-200 rounded flex items-center justify-center text-slate-500">
                <i class="fas fa-shopping-bag text-xs"></i>
            </div>
            <span class="font-extrabold text-slate-400 tracking-wider">HOLKA STORE</span>
        </div>
        <p class="text-slate-500 text-sm font-medium">
            &copy; <?= date('Y') ?> Holka Store. All rights reserved.
            <span class="mx-2 text-slate-300">|</span>
            Admin Panel v2.2.0 (Midtrans Enabled)
        </p>
    </div>
</footer>

</body>
</html>