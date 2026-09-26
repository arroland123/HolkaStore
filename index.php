<?php
include 'koneksi.php'; 

// Ambil semua kategori
$query_kat = mysqli_query($conn, "SELECT * FROM kategori");
$all_categories = mysqli_fetch_all($query_kat, MYSQLI_ASSOC);

// Filter produk
$where = "WHERE 1=1";

// Filter kategori
// Filter kategori
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $categories = $_GET['category'];
    
    // Pastikan input adalah array (handle jika manual input string di URL)
    if (!is_array($categories)) {
        $categories = array($categories);
    }
    
    $clean_cats = array();
    foreach($categories as $cat) {
        $clean_cats[] = mysqli_real_escape_string($conn, $cat);
    }
    
    if (!empty($clean_cats)) {
        $cat_ids = "'" . implode("','", $clean_cats) . "'";
        $where .= " AND p.id_kategori IN ($cat_ids)";
    }
}



// Query produk dengan join kategori
$sql = "SELECT p.*, k.nama_kategori 
        FROM produk p 
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
        $where 
        ORDER BY p.id_produk DESC";

$query_produk = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($query_produk, MYSQLI_ASSOC);

// Total produk untuk display
$total_count = count($products);

// Query ulang untuk total semua produk (tanpa filter)
$sql_all = "SELECT COUNT(*) as total FROM produk";
$result_all = mysqli_query($conn, $sql_all);
$row_all = mysqli_fetch_assoc($result_all);
$total_all = $row_all['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk Minimalis - ModernThreads</title>
    <link rel="stylesheet" href="assets/css/style.css?v=2.1">
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
            <div class="content-wrapper">
                <!-- Sidebar Filter -->
                <aside class="sidebar">
                    <h2 class="sidebar-title">Katalog Produk Minimalis</h2>
                    <p class="sidebar-subtitle">Kategori produk dengan desain minimalis untuk fashion modern</p>

                    <div class="filter-section">
                        <h3 class="filter-title">Filter</h3>
                        
                        <form action="" method="GET">


                            <!-- Kategori -->
                            <div class="filter-group">
                                <h4 class="filter-subtitle" onclick="toggleFilter(this)">
                                    <i class="fas fa-chevron-down"></i> Kategori
                                </h4>
                                <div class="filter-content">
                                    <div class="filter-options">
                                        <?php foreach($all_categories as $k): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="category[]" value="<?= $k['id_kategori'] ?>" 
                                                <?= (isset($_GET['category']) && in_array($k['id_kategori'], $_GET['category'])) ? 'checked' : '' ?>>
                                            <span><?= $k['nama_kategori'] ?></span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <div style="margin-top: 15px;">
                                        <button type="submit" class="apply-btn">Terapkan</button>
                                        <a href="index.php" class="reset-link">Reset Filter</a>
                                    </div>
                                </div>
                            </div>





                        </form>
                    </div>
                </aside>

                <!-- Product Grid -->
                <section class="product-section">
                    <div class="section-header">
                        <div class="breadcrumb">
                        </div>

                    </div>

                    <?php if($total_count > 0): ?>
                    <div class="product-grid">
                        <?php foreach ($products as $p): ?>
                        <div class="product-card" onclick="window.location.href='detail_produk.php?id=<?= $p['id_produk'] ?>'" style="cursor: pointer;">
                            <div class="product-image">
                                <?php 
                                    $path = "assets/img/" . $p['gambar'];
                                    if (!empty($p['gambar']) && file_exists($path)) {
                                        // Jika ada gambar, tampilkan gambar
                                        echo '<img src="' . $path . '" alt="' . htmlspecialchars($p['nama_produk']) . '">';
                                    } else {
                                        // Jika tidak ada gambar, tampilkan placeholder
                                        echo '<div class="product-placeholder"><i class="fas fa-tshirt"></i></div>';
                                    }
                                ?>

                            </div>
                            <div class="product-info">
                                <span class="product-category"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                                <h3 class="product-name"><?= htmlspecialchars($p['nama_produk']) ?></h3>
                                <div class="product-rating">
                                </div>
                                <div class="product-price">
                                    <span class="current-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">...</button>
                        <button class="page-btn">12</button>
                        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h3>Produk Tidak Ditemukan</h3>
                        <p>Maaf, tidak ada produk yang sesuai dengan filter Anda</p>
                        <a href="index.php" class="btn-back">Lihat Semua Produk</a>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <!-- Promo Section -->
    <?php
    $query_promo = mysqli_query($conn, "SELECT * FROM promo LIMIT 1");
    if($query_promo && mysqli_num_rows($query_promo) > 0) {
        $promo = mysqli_fetch_assoc($query_promo);
        $promo_judul = htmlspecialchars($promo['judul']);
        $promo_deskripsi = htmlspecialchars($promo['deskripsi']);
        $promo_gambar = strpos($promo['gambar'], 'http') === 0 ? $promo['gambar'] : 'assets/img/' . $promo['gambar'];
    } else {
        $promo_judul = 'Summer Collection 2024';
        $promo_deskripsi = 'Dapatkan diskon hingga 50% untuk koleksi musim panas terbaru.';
        $promo_gambar = 'https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80';
    }
    ?>
    <section class="promo-section">
        <div class="promo-container">
            <div class="promo-content">

                <h2 class="promo-title"><?= $promo_judul ?></h2>
                <p class="promo-desc"><?= $promo_deskripsi ?></p>

            </div>
            <div class="promo-image">
                <img src="<?= htmlspecialchars($promo_gambar) ?>" alt="Promo Fashion">
            </div>
        </div>
    </section>

    <!-- Store Address Section -->
    <section class="store-address-section">
        <div class="container">
            <div class="address-card">
                <div class="address-grid">
                    <div class="address-info-panel">
                        <span class="section-tag">Lokasi Toko</span>
                        <h2 class="address-title">Kunjungi Showroom Kami</h2>
                        <p class="address-desc">Temukan koleksi produk fashion premium kami secara langsung. Kami siap menyambut kehadiran Anda dengan pelayanan terbaik.</p>
                        
                        <div class="contact-details">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Alamat Utama</h4>
                                    <p>Jl. Jend. Gatot Subroto, Nglande Wetan, Ngijo, Kec. Tasikmadu, Kabupaten Karanganyar, Jawa Tengah 57721</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fab fa-shopify"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Shopee Marketplace</h4>
                                    <p><a href="https://shopee.co.id/holka_storee" target="_blank" style="color: #666; text-decoration: none;">holka_storee</a></p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">
                                    <h4>Email Layanan</h4>
                                    <p>holkastore11@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="address-map-panel">
                        <div class="map-container">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.9854754104504!2d110.9349967!3d-7.5765591!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a186446753693%3A0x4b451880b0b5f8d3!2sHolka%20Store!5e0!3m2!1sid!2sid!4v1710000000000!5m2!1sid!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="map-action">
                            <a href="https://maps.app.goo.gl/xfWBHRMv7TzwUyYo8" target="_blank" class="map-btn">
                                <i class="fas fa-directions"></i> Petunjuk Arah di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>