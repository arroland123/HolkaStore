<?php
session_start();
include 'koneksi.php';

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ambil data cart dari session
$cart_items = $_SESSION['cart'];
$total_items = count($cart_items);

// Hitung total harga
$subtotal = 0;
$cart_details = [];

foreach ($cart_items as $key => $item) {
    // Ambil detail produk dari database
    $id_produk = $item['id_produk'];
    $query = "SELECT * FROM produk WHERE id_produk = $id_produk";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $produk = mysqli_fetch_assoc($result);
        
        $cart_details[] = [
            'key' => $key,
            'id_produk' => $item['id_produk'],
            'nama_produk' => $produk['nama_produk'],
            'harga' => $produk['harga'],
            'gambar' => $produk['gambar'],
            'size' => $item['size'],
            'color' => $item['color'],
            'qty' => $item['qty'],
            'stok' => $produk['stok'] ?? 99,
            'subtotal' => $produk['harga'] * $item['qty']
        ];
        
        $subtotal += $produk['harga'] * $item['qty'];
    }
}

// Biaya pengiriman (gratis jika > 150000)
$shipping_cost = $subtotal >= 150000 ? 0 : 15000;
$total = $subtotal + $shipping_cost;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Holka Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Holka Store</span>
                </div>
                <nav class="nav">
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input-header" placeholder="Cari Produk..." name="search">
                        <button type="button" class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    <a href="index.php"><i class="fas fa-home"></i></a>
                    <a href="cart.php" class="active">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($total_items > 0): ?>
                        <span class="cart-badge"><?= $total_items ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="login.php"><i class="fas fa-user"></i></a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Keranjang Belanja</h1>
                <?php if ($total_items > 0): ?>
                <p class="page-subtitle"><?= $total_items ?> produk dalam keranjang Anda</p>
                <?php endif; ?>
            </div>

            <?php if ($total_items > 0): ?>
            <div class="cart-wrapper">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php foreach ($cart_details as $item): ?>
                    <div class="cart-item" data-key="<?= $item['key'] ?>">
                        <div class="item-image">
                            <?php 
                            $img_path = "assets/img/" . $item['gambar'];
                            if (!empty($item['gambar']) && file_exists($img_path)): 
                            ?>
                            <img src="<?= $img_path ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                            <?php else: ?>
                            <div class="image-placeholder">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-details">
                            <div class="item-header">
                                <div class="item-info">
                                    <h3 class="item-name"><?= htmlspecialchars($item['nama_produk']) ?></h3>
                                    <div class="item-variants">
                                        <span>Ukuran: <strong><?= htmlspecialchars($item['size']) ?></strong></span>
                                        <?php if ($item['color'] != 'Default'): ?>
                                        <span class="separator">•</span>
                                        <span>Warna: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="btn-remove" onclick="removeItem('<?= $item['key'] ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            
                            <div class="item-footer">
                                <div class="item-price">
                                    <span class="price-label">Harga:</span>
                                    <span class="price-value">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                </div>
                                
                                <div class="quantity-control">
                                    <button class="qty-btn" onclick="updateQuantity('<?= $item['key'] ?>', -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="qty-input" 
                                           value="<?= $item['qty'] ?>" 
                                           min="1" 
                                           max="<?= $item['stok'] ?>"
                                           data-key="<?= $item['key'] ?>"
                                           readonly>
                                    <button class="qty-btn" onclick="updateQuantity('<?= $item['key'] ?>', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="item-subtotal">
                                    <span class="subtotal-label">Subtotal:</span>
                                    <span class="subtotal-value">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="summary-card">
                        <h2 class="summary-title">Ringkasan Pesanan</h2>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal (<?= $total_items ?> produk)</span>
                                <span class="summary-value" id="subtotalValue">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span class="summary-label">Biaya Pengiriman</span>
                                <span class="summary-value <?= $shipping_cost == 0 ? 'free-shipping' : '' ?>" id="shippingValue">
                                    <?= $shipping_cost == 0 ? 'Gratis' : 'Rp ' . number_format($shipping_cost, 0, ',', '.') ?>
                                </span>
                            </div>
                            

                        </div>
                        
                        <div class="summary-total">
                            <span class="total-label">Total Harga</span>
                            <span class="total-value" id="totalValue">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <button class="btn-checkout" onclick="checkout()">
                            <span>Lanjutkan ke Pembayaran</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>

                    </div>
                </div>
            </div>

            <!-- Continue Shopping -->
            <div class="cart-actions">
                <a href="index.php" class="btn-continue-shopping">
                    <i class="fas fa-arrow-left"></i>
                    <span>Lanjut Belanja</span>
                </a>
                
                <button class="btn-clear-cart" onclick="clearCart()">
                    <i class="fas fa-trash"></i>
                    <span>Kosongkan Keranjang</span>
                </button>
            </div>

            <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 class="empty-cart-title">Keranjang Belanja Kosong</h2>
                <p class="empty-cart-text">Anda belum menambahkan produk apapun ke keranjang</p>
                <a href="index.php" class="btn-shop-now">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Mulai Belanja</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Holka Store</span>
                    </div>
                    <p>Redefining modern luxury dengan fokus pada bahan berkelanjutan dan kerajinan abadi.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Belanja</h4>
                    <ul>
                        <li><a href="#">Pria</a></li>
                        <li><a href="#">Wanita</a></li>
                        <li><a href="#">Aksesoris</a></li>
                        <li><a href="#">Produk Baru</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#">Panduan Ukuran</a></li>
                        <li><a href="#">Kebijakan Pengiriman</a></li>
                        <li><a href="#">Retur & Tukar</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Newsletter</h4>
                    <p>Dapatkan akses awal dan penawaran eksklusif.</p>
                    <div class="newsletter">
                        <input type="email" placeholder="Email Anda">
                        <button>Daftar</button>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2024 Holka Store. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/cart.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchValue = this.value;
                if (searchValue.trim()) {
                    window.location.href = 'index.php?search=' + encodeURIComponent(searchValue);
                }
            }
        });

        document.querySelector('.search-btn')?.addEventListener('click', function() {
            const searchValue = document.getElementById('searchInput').value;
            if (searchValue.trim()) {
                window.location.href = 'index.php?search=' + encodeURIComponent(searchValue);
            }
        });
    </script>
</body>
</html>
