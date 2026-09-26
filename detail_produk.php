<?php
$page_title = "Detail Produk - Holka Store";
include 'koneksi.php';

// Ambil ID produk dari URL
$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mengambil detail produk
$query = "SELECT p.*, k.nama_kategori 
          FROM produk p 
          LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
          WHERE p.id_produk = $id_produk";

$result = mysqli_query($conn, $query);

// Cek apakah produk ditemukan
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}

$produk = mysqli_fetch_assoc($result);

// Hitung diskon jika ada
$diskon = 0;
$harga_asli = isset($produk['harga_asli']) && $produk['harga_asli'] > 0 ? $produk['harga_asli'] : 0;
if ($harga_asli > 0 && $harga_asli > $produk['harga']) {
    $diskon = round((($harga_asli - $produk['harga']) / $harga_asli) * 100);
}

// Query produk terkait (dari kategori yang sama)
$query_related = "SELECT p.*, k.nama_kategori 
                  FROM produk p 
                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                  WHERE p.id_kategori = {$produk['id_kategori']} 
                  AND p.id_produk != $id_produk 
                  ORDER BY RAND() 
                  LIMIT 4";
$result_related = mysqli_query($conn, $query_related);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk['nama_produk']) ?> - Holka Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/detail_produk.css?v=1.2">
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
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                    <a href="login.php"><i class="fas fa-user"></i></a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span class="separator">/</span>
                <a href="index.php?category=<?= $produk['id_kategori'] ?>"><?= htmlspecialchars($produk['nama_kategori']) ?></a>
                <span class="separator">/</span>
                <span class="current"><?= htmlspecialchars($produk['nama_produk']) ?></span>
            </nav>

            <!-- Product Detail Section -->
            <div class="product-detail-wrapper">
                <!-- Left: Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <?php if ($diskon > 0): ?>
                        <span class="discount-badge">-<?= $diskon ?>%</span>
                        <?php endif; ?>
                        
                        <?php 
                        $gambar_path = "assets/img/" . $produk['gambar'];
                        if (!empty($produk['gambar']) && file_exists($gambar_path)): 
                        ?>
                        <img id="mainImg" src="<?= $gambar_path ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        <?php else: ?>
                        <div class="image-placeholder">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <?php endif; ?>
                        

                    </div>
                    

                </div>

                <!-- Right: Product Information -->
                <div class="product-info-detail">
                    <div class="product-badge">
                        <i class="fas fa-shield-alt"></i>
                        Produk Original
                    </div>
                    
                    <h1 class="product-title"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
                    

                    
                    <div class="product-price-section">
                        <div class="current-price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                        <?php if ($harga_asli > 0 && $harga_asli > $produk['harga']): ?>
                        <div class="old-price">Rp <?= number_format($harga_asli, 0, ',', '.') ?></div>
                        <div class="discount-percent"><?= $diskon ?>% OFF</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <p><?= nl2br(htmlspecialchars($produk['deskripsi'] ?? 'Produk berkualitas tinggi dengan bahan premium dan desain modern yang cocok untuk berbagai acara.')) ?></p>
                    </div>
                    
                    <!-- Size Selection -->
                    <div class="selection-group">
                        <label class="selection-label">Pilih Ukuran:</label>
                        <div class="size-options">
                            <?php
                            $stok_detail = [];
                            if (!empty($produk['stok_detail'])) {
                                $stok_detail = json_decode($produk['stok_detail'], true);
                            }
                            
                            // Jika data stok detail kosong (produk lama/dummy), buat default berdasarkan kategori
                            if (empty($stok_detail)) {
                                $is_celana = (strpos(strtolower($produk['nama_kategori'] ?? ''), 'celana') !== false || strpos(strtolower($produk['nama_kategori'] ?? ''), 'jeans') !== false || ($produk['id_kategori'] == 3));
                                $available_sizes = $is_celana ? ['28', '29', '30', '31', '32', '33', '34', '35', '36'] : ['S', 'M', 'L', 'XL', 'XXL'];
                                foreach ($available_sizes as $sz) {
                                    $stok_detail[$sz] = ($produk['stok'] > 0) ? ceil($produk['stok'] / count($available_sizes)) : 0;
                                }
                            }
                            
                            // Tentukan tombol active pertama kali untuk ukuran yang memiliki stok > 0
                            $active_set = false;
                            $initial_stock = 0;
                            foreach ($stok_detail as $size => $qty):
                                $isDisabled = ($qty <= 0);
                                $activeClass = "";
                                if (!$isDisabled && !$active_set) {
                                    $activeClass = "active";
                                    $initial_stock = $qty;
                                    $active_set = true;
                                }
                                $disabledAttr = $isDisabled ? "disabled" : "";
                            ?>
                            <button class="size-option <?= $activeClass ?> <?= $isDisabled ? 'disabled' : '' ?>" 
                                    <?= $disabledAttr ?> 
                                    onclick="selectSize(this, '<?= htmlspecialchars($size) ?>')">
                                <?= htmlspecialchars($size) ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <script>
                        window.productStockDetail = <?= json_encode($stok_detail) ?>;
                    </script>
                    
                    <!-- Color Selection -->

                    
                    <!-- Quantity Selection -->
                    <div class="selection-group">
                        <label class="selection-label">Jumlah:</label>
                        <div class="quantity-selector">
                            <button class="qty-btn" onclick="decreaseQty()"><i class="fas fa-minus"></i></button>
                            <input type="number" id="quantity" value="1" min="1" max="<?= $initial_stock > 0 ? $initial_stock : ($produk['stok'] ?? 99) ?>" readonly>
                            <button class="qty-btn" onclick="increaseQty()"><i class="fas fa-plus"></i></button>
                        </div>
                        <span class="stock-info">Stok: <strong><?= $initial_stock > 0 ? $initial_stock : 0 ?></strong></span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn-add-cart" onclick="addToCart(<?= $id_produk ?>)">
                            <i class="fas fa-shopping-cart"></i>
                            Tambah ke Keranjang
                        </button>
                        <button class="btn-buy-now" onclick="buyNow(<?= $id_produk ?>)">
                            Beli Sekarang
                        </button>
                    </div>

                </div>
            </div>

            <!-- Related Products -->
            <?php if (mysqli_num_rows($result_related) > 0): ?>
            <section class="related-products">
                <h2 class="section-title">Produk Terkait</h2>
                <div class="product-grid">
                    <?php while ($related = mysqli_fetch_assoc($result_related)): ?>
                    <div class="product-card" onclick="window.location.href='detail_produk.php?id=<?= $related['id_produk'] ?>'">
                        <div class="product-image">
                            <?php 
                            $related_img = "assets/img/" . $related['gambar'];
                            if (!empty($related['gambar']) && file_exists($related_img)): 
                            ?>
                            <img src="<?= $related_img ?>" alt="<?= htmlspecialchars($related['nama_produk']) ?>">
                            <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?= htmlspecialchars($related['nama_kategori']) ?></span>
                            <h3 class="product-name"><?= htmlspecialchars($related['nama_produk']) ?></h3>
                            <div class="product-price">
                                <span class="current-price">Rp <?= number_format($related['harga'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/detail_produk.js"></script>
</body>
</html>
