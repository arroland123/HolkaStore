<?php
session_start();
include 'koneksi.php';

// Redirect jika cart kosong
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: cart.php');
    exit;
}

// Ambil data cart
$cart_items = $_SESSION['cart'];
$cart_details = [];
$subtotal = 0;
$total_weight = 0;

foreach ($cart_items as $key => $item) {
    $id_produk = $item['id_produk'];
    $query = "SELECT * FROM produk WHERE id_produk = $id_produk";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $produk = mysqli_fetch_assoc($result);
        
        $berat = isset($produk['berat']) ? (int)$produk['berat'] : 500;
        $total_weight += $berat * $item['qty'];
        
        $cart_details[] = [
            'key' => $key,
            'id_produk' => $item['id_produk'],
            'nama_produk' => $produk['nama_produk'],
            'harga' => $produk['harga'],
            'gambar' => $produk['gambar'],
            'size' => $item['size'],
            'color' => $item['color'],
            'qty' => $item['qty'],
            'subtotal' => $produk['harga'] * $item['qty']
        ];
        
        $subtotal += $produk['harga'] * $item['qty'];
    }
}

// Hitung biaya (default awal sebelum pilih kota & kurir)
$shipping_cost = 0; // kita set 0 default awal, biar dihitung real-time saat pilih kurir
$tax = 0;
$total = $subtotal + $shipping_cost;

// Simpan total ke session untuk proses order
$_SESSION['checkout_total'] = $total;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Holka Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Midtrans Snap JS (Sandbox) -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="Mid-client-dqTFTSR0lLax5mvX"></script>
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
                    <a href="index.php">Beranda</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Pembayaran</h1>
                <p class="page-subtitle">Selesaikan pesanan Anda dengan aman</p>
            </div>

            <!-- Checkout Steps -->
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span>Keranjang</span>
                </div>
                <div class="step-line"></div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span>Pengiriman</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span>Pembayaran</span>
                </div>
            </div>

            <form id="checkoutForm">
                <div class="checkout-wrapper">
                    <!-- Left Column: Forms -->
                    <div class="checkout-forms">
                        <!-- Shipping Information -->
                        <section class="form-section">
                            <div class="section-header">
                                <i class="fas fa-shipping-fast"></i>
                                <h2>Informasi Pengiriman</h2>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="fullName">Nama Lengkap <span class="required">*</span></label>
                                    <input type="text" 
                                           id="fullName" 
                                           name="full_name" 
                                           placeholder="Masukkan nama lengkap Anda"
                                           required>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="address">Alamat Lengkap <span class="required">*</span></label>
                                    <textarea id="address" 
                                              name="address" 
                                              rows="3" 
                                              placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan"
                                              required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="province">Provinsi <span class="required">*</span></label>
                                    <select id="province" name="province_id" required>
                                        <option value="">Memuat Provinsi...</option>
                                    </select>
                                    <input type="hidden" id="provinceName" name="province">
                                </div>

                                <div class="form-group">
                                    <label for="city">Kota / Kabupaten <span class="required">*</span></label>
                                    <select id="city" name="city_id" required disabled>
                                        <option value="">Pilih Provinsi Dahulu</option>
                                    </select>
                                    <input type="hidden" id="cityName" name="city">
                                </div>
                                
                                <div class="form-group">
                                    <label for="postalCode">Kode Pos <span class="required">*</span></label>
                                    <input type="text" 
                                           id="postalCode" 
                                           name="postal_code" 
                                           placeholder="12345"
                                           pattern="[0-9]{5}"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Nomor Telepon <span class="required">*</span></label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           placeholder="08123456789"
                                           pattern="[0-9]{10,13}"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="courier">Pilihan Kurir <span class="required">*</span></label>
                                    <select id="courier" name="courier" required>
                                        <option value="">Pilih Kurir</option>
                                        <option value="JNE">JNE</option>
                                        <option value="JNT">JNT</option>
                                    </select>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           placeholder="email@example.com">
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="notes">Catatan Pesanan (Opsional)</label>
                                    <textarea id="notes" 
                                              name="notes" 
                                              rows="2" 
                                              placeholder="Catatan untuk penjual atau kurir"></textarea>
                                </div>
                            </div>
                        </section>

                        <!-- Payment Method -->
                        <section class="form-section">
                            <div class="section-header">
                                <i class="fas fa-credit-card"></i>
                                <h2>Metode Pembayaran</h2>
                            </div>
                            
                            <div class="payment-methods">
                                <!-- Consolidated Midtrans Payment Option -->
                                <label class="payment-option active">
                                    <input type="radio" 
                                           name="payment_method" 
                                           value="midtrans"
                                           checked
                                           onchange="selectPayment(this)">
                                    <div class="payment-icon">
                                        <i class="fas fa-shield-alt text-holka-secondary"></i>
                                    </div>
                                    <div class="payment-info">
                                        <strong>Pembayaran Instan & Otomatis</strong>
                                        <span>Mendukung QRIS, GoPay, ShopeePay, Transfer Bank (Virtual Account), dll.</span>
                                    </div>
                                    <div class="payment-radio"></div>
                                </label>
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="order-summary-sidebar">
                        <div class="summary-card">
                            <h2 class="summary-title">Ringkasan Pesanan</h2>
                            
                            <!-- Order Items -->
                            <div class="summary-items">
                                <?php foreach ($cart_details as $item): ?>
                                <div class="summary-item">
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
                                        <h4><?= htmlspecialchars($item['nama_produk']) ?></h4>
                                        <p class="item-variant"><?= $item['qty'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                        <p class="item-meta">Size: <?= $item['size'] ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Price Breakdown -->
                            <div class="price-breakdown">
                                <div class="price-row">
                                    <span>Subtotal (<?= count($cart_details) ?> produk)</span>
                                    <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Biaya Pengiriman</span>
                                    <span class="<?= $shipping_cost == 0 ? 'free-shipping' : '' ?>">
                                        <?= $shipping_cost == 0 ? 'Gratis' : 'Rp ' . number_format($shipping_cost, 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Total -->
                            <div class="total-price">
                                <span>Total Harga</span>
                                <span class="total-amount">Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="button" id="payBtn" class="btn-checkout" onclick="handleCheckout()">
                                <span id="payBtnText">Bayar Sekarang</span>
                                <i class="fas fa-arrow-right" id="payBtnIcon"></i>
                            </button>

                            <!-- Loading State (hidden) -->
                            <div id="payLoading" style="display:none;text-align:center;padding:14px;color:rgba(255,255,255,0.6);font-size:0.9rem;">
                                <i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Menyiapkan pembayaran...
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2024 Holka Store. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/checkout.js"></script>
    <script>
    // ==========================================
    // RajaOngkir Integration
    // ==========================================
    const totalWeight = <?= (int)$total_weight ?>;
    const subtotalPrice = <?= (int)$subtotal ?>;

    document.addEventListener("DOMContentLoaded", function() {
        loadProvinces();
        
        const provinceSelect = document.getElementById('province');
        const citySelect = document.getElementById('city');
        const courierSelect = document.getElementById('courier');
        
        provinceSelect.addEventListener('change', function() {
            const provinceId = this.value;
            const provinceName = this.options[this.selectedIndex].text;
            document.getElementById('provinceName').value = provinceId ? provinceName : '';
            
            citySelect.innerHTML = '<option value="">Memuat Kota...</option>';
            citySelect.disabled = true;
            
            updateShippingCostDisplay(0);
            
            if (provinceId) {
                fetch(`get_cities.php?province_id=${provinceId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let html = '<option value="">Pilih Kota / Kabupaten</option>';
                        data.results.forEach(city => {
                            html += `<option value="${city.city_id}">${city.type} ${city.city_name}</option>`;
                        });
                        citySelect.innerHTML = html;
                        citySelect.disabled = false;
                    } else {
                        citySelect.innerHTML = '<option value="">Gagal memuat kota</option>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    citySelect.innerHTML = '<option value="">Error memuat kota</option>';
                });
            } else {
                citySelect.innerHTML = '<option value="">Pilih Provinsi Dahulu</option>';
            }
        });
        
        citySelect.addEventListener('change', function() {
            const cityName = this.options[this.selectedIndex].text;
            document.getElementById('cityName').value = this.value ? cityName : '';
            calculateShipping();
        });
        
        courierSelect.addEventListener('change', function() {
            calculateShipping();
        });
    });

    function loadProvinces() {
        const provinceSelect = document.getElementById('province');
        fetch('get_provinces.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '<option value="">Pilih Provinsi</option>';
                data.results.forEach(prov => {
                    html += `<option value="${prov.province_id}">${prov.province}</option>`;
                });
                provinceSelect.innerHTML = html;
            } else {
                provinceSelect.innerHTML = '<option value="">Gagal memuat provinsi</option>';
            }
        })
        .catch(err => {
            console.error(err);
            provinceSelect.innerHTML = '<option value="">Error memuat provinsi</option>';
        });
    }

    function calculateShipping() {
        const cityId = document.getElementById('city').value;
        const courier = document.getElementById('courier').value;
        
        if (!cityId || !courier) {
            updateShippingCostDisplay(0);
            return;
        }
        
        // Tampilkan status loading pada baris pengiriman
        const rows = document.querySelectorAll('.price-row');
        let shippingValEl = null;
        rows.forEach(row => {
            if (row.textContent.includes('Biaya Pengiriman')) {
                shippingValEl = row.querySelector('span:last-child');
            }
        });
        
        const prevShippingHTML = shippingValEl ? shippingValEl.innerHTML : '';
        if (shippingValEl) {
            shippingValEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
        document.getElementById('payBtn').disabled = true;
        
        fetch(`get_shipping_cost.php?destination_city_id=${cityId}&courier=${courier}&weight=${totalWeight}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('payBtn').disabled = false;
            if (data.success && data.results && data.results[0] && data.results[0].costs && data.results[0].costs.length > 0) {
                const costs = data.results[0].costs;
                let selected = null;
                const preferred = ['REG', 'REGULER', 'EZ', 'OKE', 'CTC'];
                for (let pref of preferred) {
                    const found = costs.find(c => c.service.toUpperCase() === pref);
                    if (found) {
                        selected = found;
                        break;
                    }
                }
                if (!selected) {
                    // Jika tidak ada service pilihan, cari yang termurah
                    selected = costs.reduce((cheapest, current) => {
                        return current.cost[0].value < cheapest.cost[0].value ? current : cheapest;
                    }, costs[0]);
                }
                const costVal = selected.cost[0].value;
                updateShippingCostDisplay(costVal);
            } else {
                alert(data.message || 'Gagal menghitung ongkos kirim.');
                if (shippingValEl) shippingValEl.innerHTML = prevShippingHTML;
                updateShippingCostDisplay(0);
            }
        })
        .catch(err => {
            document.getElementById('payBtn').disabled = false;
            console.error(err);
            if (shippingValEl) shippingValEl.innerHTML = prevShippingHTML;
            updateShippingCostDisplay(0);
        });
    }

    function updateShippingCostDisplay(cost) {
        const rows = document.querySelectorAll('.price-row');
        rows.forEach(row => {
            if (row.textContent.includes('Biaya Pengiriman')) {
                const spanVal = row.querySelector('span:last-child');
                if (cost > 0) {
                    spanVal.className = '';
                    spanVal.textContent = 'Rp ' + cost.toLocaleString('id-ID');
                } else {
                    spanVal.className = 'free-shipping';
                    spanVal.textContent = 'Gratis';
                }
            }
        });
        
        const total = subtotalPrice + cost;
        document.querySelector('.total-amount').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    // ==========================================
    // Midtrans Snap Integration
    // ==========================================
    function handleCheckout() {
        const form = document.getElementById('checkoutForm');

        // Validasi form HTML5
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Tampilkan loading
        document.getElementById('payBtn').disabled  = true;
        document.getElementById('payBtnText').textContent = 'Memproses...';
        document.getElementById('payBtnIcon').className = 'fas fa-spinner fa-spin';

        // Kirim data form ke process_order.php via AJAX
        const formData = new FormData(form);

        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Bukan JSON:', text);
                throw new Error('Server Error: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            if (data.success && data.snap_token) {
                snap.pay(data.snap_token, {
                    onSuccess: function(result) { window.location.href = 'payment_finish.php?order_id=' + data.order_number; },
                    onPending: function(result) { window.location.href = 'payment_unfinish.php?order_id=' + data.order_number; },
                    onError: function(result) { window.location.href = 'payment_error.php?order_id=' + data.order_number; },
                    onClose: function() { resetPayBtn(); showCheckoutNotif('Pilih metode pembayaran untuk lanjut.', 'warning'); }
                });
            } else {
                resetPayBtn();
                showCheckoutNotif(data.message || 'Gagal memproses pesanan.', 'error');
            }
        })
        .catch(err => {
            resetPayBtn();
            showCheckoutNotif('Error: ' + err.message, 'error');
            console.error(err);
        });
    }

    function resetPayBtn() {
        const btn = document.getElementById('payBtn');
        btn.disabled = false;
        document.getElementById('payBtnText').textContent = 'Bayar Sekarang';
        document.getElementById('payBtnIcon').className = 'fas fa-arrow-right';
    }

    function showCheckoutNotif(msg, type) {
        // Hapus notif lama jika ada
        const old = document.getElementById('checkoutNotif');
        if (old) old.remove();

        const colors = {
            error:   'rgba(239,68,68,0.15)',
            warning: 'rgba(247,151,30,0.15)',
            success: 'rgba(0,201,167,0.15)'
        };
        const borders = {
            error:   'rgba(239,68,68,0.4)',
            warning: 'rgba(247,151,30,0.4)',
            success: 'rgba(0,201,167,0.4)'
        };

        const notif = document.createElement('div');
        notif.id = 'checkoutNotif';
        notif.style.cssText = `
            margin-top: 14px;
            padding: 13px 16px;
            border-radius: 10px;
            background: ${colors[type] || colors.error};
            border: 1px solid ${borders[type] || borders.error};
            color: rgba(255,255,255,0.9);
            font-size: 0.88rem;
            line-height: 1.5;
        `;
        notif.textContent = msg;

        document.getElementById('payBtn').insertAdjacentElement('afterend', notif);
        notif.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    </script>
</body>
</html>
