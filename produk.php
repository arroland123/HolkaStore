<?php
$page_title = "Katalog Produk - Holka Store";
include 'koneksi.php';
include 'header.php';

// Filter berdasarkan kategori
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Query produk dari database
$query = "SELECT * FROM produk WHERE 1=1";

if (!empty($kategori_filter)) {
    $query .= " AND kategori = '" . mysqli_real_escape_string($conn, $kategori_filter) . "'";
}

if (!empty($search_filter)) {
    $query .= " AND (nama_produk LIKE '%" . mysqli_real_escape_string($conn, $search_filter) . "%' 
                OR deskripsi LIKE '%" . mysqli_real_escape_string($conn, $search_filter) . "%')";
}

$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Hitung total produk untuk filter
$total_query = "SELECT COUNT(*) as total FROM produk WHERE 1=1";
if (!empty($kategori_filter)) {
    $total_query .= " AND kategori = '" . mysqli_real_escape_string($conn, $kategori_filter) . "'";
}
if (!empty($search_filter)) {
    $total_query .= " AND (nama_produk LIKE '%" . mysqli_real_escape_string($conn, $search_filter) . "%' 
                     OR deskripsi LIKE '%" . mysqli_real_escape_string($conn, $search_filter) . "%')";
}
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
$total_produk = $total_data['total'];

// Ambil semua kategori untuk filter
$kategori_query = "SELECT DISTINCT kategori FROM produk ORDER BY kategori";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="content-wrapper">
            <!-- Sidebar Filter -->
            <aside class="sidebar-filter">
                <h2 class="sidebar-title">Katalog Produk Minimalis</h2>
                <p class="sidebar-subtitle">Kategori produk dengan desain minimalis untuk fashion modern</p>

                <div class="filter-section">
                    <h3 class="filter-title">Filter</h3>
                    
                    <!-- Search -->
                    <div class="filter-group">
                        <form method="GET" action="produk.php" id="searchForm">
                            <input type="text" 
                                   class="search-input" 
                                   placeholder="Cari Produk" 
                                   id="searchInput" 
                                   name="search"
                                   value="<?= htmlspecialchars($search_filter) ?>">
                            <?php if (!empty($kategori_filter)): ?>
                            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori_filter) ?>">
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Kategori -->
                    <div class="filter-group">
                        <h4 class="filter-subtitle">
                            <i class="fas fa-chevron-down"></i> Kategori
                        </h4>
                        <div class="filter-options">
                            <label class="checkbox-label">
                                <input type="radio" 
                                       name="category" 
                                       value="" 
                                       <?= empty($kategori_filter) ? 'checked' : '' ?>
                                       onchange="filterByCategory('')">
                                <span>Semua Kategori</span>
                            </label>
                            <?php 
                            mysqli_data_seek($kategori_result, 0);
                            while ($kat = mysqli_fetch_assoc($kategori_result)): 
                            ?>
                            <label class="checkbox-label">
                                <input type="radio" 
                                       name="category" 
                                       value="<?= htmlspecialchars($kat['kategori']) ?>"
                                       <?= $kategori_filter == $kat['kategori'] ? 'checked' : '' ?>
                                       onchange="filterByCategory('<?= htmlspecialchars($kat['kategori']) ?>')">
                                <span><?= htmlspecialchars($kat['kategori']) ?></span>
                            </label>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Harga -->
                    <div class="filter-group">
                        <h4 class="filter-subtitle">
                            <i class="fas fa-chevron-down"></i> Harga
                        </h4>
                        <div class="price-range">
                            <input type="number" class="price-input" id="minPrice" placeholder="Min" value="0">
                            <span>-</span>
                            <input type="number" class="price-input" id="maxPrice" placeholder="Max" value="10000000">
                        </div>
                        <button class="apply-btn" onclick="applyPriceFilter()">Terapkan</button>
                    </div>

                    <!-- Ukuran -->
                    <div class="filter-group">
                        <h4 class="filter-subtitle">
                            <i class="fas fa-chevron-down"></i> Ukuran
                        </h4>
                        <div class="size-options">
                            <button class="size-btn" onclick="filterBySize('S')">S</button>
                            <button class="size-btn" onclick="filterBySize('M')">M</button>
                            <button class="size-btn" onclick="filterBySize('L')">L</button>
                            <button class="size-btn" onclick="filterBySize('XL')">XL</button>
                            <button class="size-btn" onclick="filterBySize('XXL')">XXL</button>
                        </div>
                    </div>

                    <!-- Warna -->
                    <div class="filter-group">
                        <h4 class="filter-subtitle">
                            <i class="fas fa-chevron-down"></i> Warna
                        </h4>
                        <div class="color-options">
                            <button class="color-btn" style="background: #000" onclick="filterByColor('Hitam')"></button>
                            <button class="color-btn" style="background: #fff; border: 1px solid #ddd" onclick="filterByColor('Putih')"></button>
                            <button class="color-btn" style="background: #4169E1" onclick="filterByColor('Biru')"></button>
                            <button class="color-btn" style="background: #228B22" onclick="filterByColor('Hijau')"></button>
                            <button class="color-btn" style="background: #DC143C" onclick="filterByColor('Merah')"></button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Product Grid -->
            <section class="product-section">
                <div class="section-header">
                    <div class="breadcrumb">
                        Menampilkan <?= mysqli_num_rows($result) ?> Dari <?= $total_produk ?> Produk
                        <?php if (!empty($kategori_filter)): ?>
                            <span class="filter-badge">Kategori: <?= htmlspecialchars($kategori_filter) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sort-options">
                        <label>Urutkan:</label>
                        <select class="sort-select" id="sortSelect" onchange="sortProducts(this.value)">
                            <option value="default">Pilih Urutan</option>
                            <option value="price-low">Harga Terendah</option>
                            <option value="price-high">Harga Tertinggi</option>
                            <option value="name">Nama A-Z</option>
                            <option value="newest">Terbaru</option>
                        </select>
                    </div>
                </div>

                <div class="product-grid" id="productGrid">
                    <?php 
                    if (mysqli_num_rows($result) > 0):
                        while ($produk = mysqli_fetch_assoc($result)): 
                            // Hitung diskon jika ada
                            $diskon = 0;
                            $harga_asli = isset($produk['harga_asli']) ? $produk['harga_asli'] : 0;
                            if ($harga_asli > 0 && $harga_asli > $produk['harga']) {
                                $diskon = round((($harga_asli - $produk['harga']) / $harga_asli) * 100);
                            }
                    ?>
                    <div class="product-card" 
                         data-id="<?= $produk['id'] ?>"
                         data-category="<?= htmlspecialchars($produk['kategori']) ?>" 
                         data-price="<?= $produk['harga'] ?>"
                         data-name="<?= htmlspecialchars($produk['nama_produk']) ?>">
                        <div class="product-image">
                            <?php if ($diskon > 0): ?>
                            <span class="badge">Diskon <?= $diskon ?>%</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($produk['gambar']) && file_exists('uploads/' . $produk['gambar'])): ?>
                            <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" 
                                 alt="<?= htmlspecialchars($produk['nama_produk']) ?>">
                            <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="product-overlay">
                                <button class="icon-btn" onclick="addToWishlist(<?= $produk['id'] ?>)" title="Tambah ke Wishlist">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="icon-btn" onclick="addToCart(<?= $produk['id'] ?>)" title="Tambah ke Keranjang">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button class="icon-btn" onclick="quickView(<?= $produk['id'] ?>)" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($produk['nama_produk']) ?></h3>
                            <div class="product-category">
                                <span><?= htmlspecialchars($produk['kategori']) ?></span>
                            </div>
                            <div class="product-price">
                                <span class="current-price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></span>
                                <?php if ($harga_asli > 0 && $harga_asli > $produk['harga']): ?>
                                <span class="old-price">Rp <?= number_format($harga_asli, 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($produk['stok'])): ?>
                            <div class="product-stock">
                                <span class="stock-badge <?= $produk['stok'] > 10 ? 'in-stock' : 'low-stock' ?>">
                                    Stok: <?= $produk['stok'] ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h3>Tidak ada produk ditemukan</h3>
                        <p>Coba ubah filter atau kata kunci pencarian Anda</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination jika diperlukan -->
                <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="pagination">
                    <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<script>
// Filter by category
function filterByCategory(category) {
    const searchParam = '<?= htmlspecialchars($search_filter) ?>';
    let url = 'produk.php';
    
    if (category) {
        url += '?kategori=' + encodeURIComponent(category);
        if (searchParam) {
            url += '&search=' + encodeURIComponent(searchParam);
        }
    } else if (searchParam) {
        url += '?search=' + encodeURIComponent(searchParam);
    }
    
    window.location.href = url;
}

// Search on input
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        document.getElementById('searchForm').submit();
    }, 500);
});

// Price filter
function applyPriceFilter() {
    const minPrice = parseInt(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseInt(document.getElementById('maxPrice').value) || 999999999;
    
    const cards = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const price = parseInt(card.dataset.price);
        if (price >= minPrice && price <= maxPrice) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    updateProductCount(visibleCount);
}

// Sort products
function sortProducts(sortType) {
    const grid = document.getElementById('productGrid');
    const cards = Array.from(document.querySelectorAll('.product-card'));
    
    cards.sort((a, b) => {
        switch(sortType) {
            case 'price-low':
                return parseInt(a.dataset.price) - parseInt(b.dataset.price);
            case 'price-high':
                return parseInt(b.dataset.price) - parseInt(a.dataset.price);
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'newest':
                return parseInt(b.dataset.id) - parseInt(a.dataset.id);
            default:
                return 0;
        }
    });
    
    cards.forEach(card => grid.appendChild(card));
}

// Filter by size
function filterBySize(size) {
    // Toggle active state
    event.target.classList.toggle('active');
    
    // Implement size filter logic
    showNotification('Filter ukuran ' + size + ' diterapkan');
}

// Filter by color
function filterByColor(color) {
    // Toggle active state
    const colorBtns = document.querySelectorAll('.color-btn');
    colorBtns.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    showNotification('Filter warna ' + color + ' diterapkan');
}

// Add to wishlist
function addToWishlist(productId) {
    // AJAX call to add to wishlist
    fetch('add_to_wishlist.php?id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Produk ditambahkan ke wishlist!');
                event.target.closest('.icon-btn').classList.add('liked');
            } else {
                showNotification('Gagal menambahkan ke wishlist');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan');
        });
}

// Add to cart
function addToCart(productId) {
    // AJAX call to add to cart
    fetch('add_to_cart.php?id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Produk ditambahkan ke keranjang!');
            } else {
                showNotification('Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan');
        });
}

// Quick view
function quickView(productId) {
    // Redirect to product detail or open modal
    window.location.href = 'detail_produk.php?id=' + productId;
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: #4169E1;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Update product count
function updateProductCount(count) {
    const breadcrumb = document.querySelector('.breadcrumb');
    if (breadcrumb) {
        const text = breadcrumb.textContent.split('Menampilkan')[1].split('Dari')[1];
        breadcrumb.innerHTML = `Menampilkan ${count} Dari ${text}`;
    }
}
</script>
<?php include 'footer.php'; ?>