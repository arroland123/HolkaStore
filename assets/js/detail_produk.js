// ==========================================
// DETAIL PRODUK JAVASCRIPT - HOLKA STORE
// ==========================================

// Fungsi untuk mengubah gambar utama saat thumbnail diklik
function changeImage(imgSrc) {
    const mainImg = document.getElementById('mainImg');
    if (mainImg) {
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = imgSrc;
            mainImg.style.opacity = '1';
        }, 200);
    }
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => thumb.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// Fungsi untuk memilih ukuran
function selectSize(element, size) {
    // Cek apakah ukuran disabled
    if (element.classList.contains('disabled')) {
        showNotification('Ukuran ' + size + ' tidak tersedia', 'warning');
        return;
    }
    
    // Remove active dari semua ukuran
    const sizes = document.querySelectorAll('.size-option');
    sizes.forEach(s => s.classList.remove('active'));
    
    // Tambah active ke ukuran yang dipilih
    element.classList.add('active');
    
    // Update stock UI based on window.productStockDetail
    if (window.productStockDetail && window.productStockDetail[size] !== undefined) {
        const stockQty = window.productStockDetail[size];
        
        // Update max input
        const qtyInput = document.getElementById('quantity');
        if (qtyInput) {
            qtyInput.max = stockQty;
            if (parseInt(qtyInput.value) > stockQty) {
                qtyInput.value = stockQty;
            }
        }
        
        // Update stock text display
        const stockInfo = document.querySelector('.stock-info strong');
        if (stockInfo) {
            stockInfo.textContent = stockQty;
        }
    }
}

// Fungsi untuk memilih warna
function selectColor(element) {
    // Remove active dari semua warna
    const colors = document.querySelectorAll('.color-option');
    colors.forEach(c => c.classList.remove('active'));
    
    // Tambah active ke warna yang dipilih
    element.classList.add('active');
}

// Fungsi untuk increase quantity
function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        const max = parseInt(qtyInput.max) || 99;
        const current = parseInt(qtyInput.value) || 1;
        
        if (current < max) {
            qtyInput.value = current + 1;
        } else {
            showNotification('Stok maksimal tercapai', 'warning');
        }
    }
}

// Fungsi untuk decrease quantity
function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        const current = parseInt(qtyInput.value) || 1;
        
        if (current > 1) {
            qtyInput.value = current - 1;
        }
    }
}

// Fungsi untuk tambah ke wishlist
function addToWishlist(productId) {
    const btn = document.querySelector('.favorite-btn i');
    
    if (btn) {
        if (btn.classList.contains('far')) {
            // Tambah ke wishlist
            btn.classList.remove('far');
            btn.classList.add('fas');
            showNotification('Produk ditambahkan ke wishlist!', 'success');
            
            // AJAX call untuk save ke database (opsional)
            saveToWishlist(productId);
        } else {
            // Hapus dari wishlist
            btn.classList.remove('fas');
            btn.classList.add('far');
            showNotification('Produk dihapus dari wishlist!', 'info');
            
            // AJAX call untuk remove dari database (opsional)
            removeFromWishlist(productId);
        }
    }
}

// Fungsi untuk save wishlist ke database
function saveToWishlist(productId) {
    fetch('add_to_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to add to wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Fungsi untuk remove wishlist dari database
function removeFromWishlist(productId) {
    fetch('remove_from_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to remove from wishlist');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Fungsi untuk tambah ke keranjang
function addToCart(productId) {
    const qty = document.getElementById('quantity')?.value || 1;
    const sizeElement = document.querySelector('.size-option.active');
    const colorElement = document.querySelector('.color-option.active');
    
    // Validasi ukuran harus dipilih
    if (!sizeElement) {
        showNotification('Silakan pilih ukuran terlebih dahulu', 'warning');
        return;
    }
    
    const size = sizeElement.textContent.trim();
    const color = colorElement?.getAttribute('title') || 'Default';
    
    // Show loading
    showLoading();
    
    // AJAX call untuk tambah ke keranjang
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${productId}&qty=${qty}&size=${size}&color=${encodeURIComponent(color)}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
            // Update cart count di header (jika ada)
            updateCartCount();
        } else {
            showNotification(data.message || 'Gagal menambahkan ke keranjang', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('Terjadi kesalahan, silakan coba lagi', 'error');
    });
}

// Fungsi untuk beli sekarang
function buyNow(productId) {
    const qty = document.getElementById('quantity')?.value || 1;
    const sizeElement = document.querySelector('.size-option.active');
    const colorElement = document.querySelector('.color-option.active');
    
    // Validasi ukuran harus dipilih
    if (!sizeElement) {
        showNotification('Silakan pilih ukuran terlebih dahulu', 'warning');
        return;
    }
    
    const size = sizeElement.textContent.trim();
    const color = colorElement?.getAttribute('title') || 'Default';
    
    // Redirect langsung ke halaman checkout
    window.location.href = `checkout.php?id=${productId}&qty=${qty}&size=${size}&color=${encodeURIComponent(color)}`;
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Hapus notifikasi sebelumnya jika ada
    const existingNotif = document.querySelector('.notification');
    if (existingNotif) {
        existingNotif.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'notification';
    
    // Set icon berdasarkan type
    let icon = '';
    let bgColor = '';
    
    switch(type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            bgColor = '#4169E1';
            break;
        case 'error':
            icon = '<i class="fas fa-times-circle"></i>';
            bgColor = '#ff4757';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            bgColor = '#f57c00';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
            bgColor = '#4169E1';
    }
    
    notification.innerHTML = `${icon} <span>${message}</span>`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove setelah 3 detik
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Fungsi untuk menampilkan loading
function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
            <p>Memproses...</p>
        </div>
    `;
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 20000;
    `;
    
    const spinner = loading.querySelector('.loading-spinner');
    spinner.style.cssText = `
        background: white;
        padding: 30px 50px;
        border-radius: 12px;
        text-align: center;
        color: #333;
    `;
    
    spinner.querySelector('i').style.cssText = `
        font-size: 40px;
        color: #4169E1;
        margin-bottom: 15px;
    `;
    
    spinner.querySelector('p').style.cssText = `
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    `;
    
    document.body.appendChild(loading);
}

// Fungsi untuk menyembunyikan loading
function hideLoading() {
    const loading = document.querySelector('.loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Fungsi untuk update cart count (opsional)
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge && data.count) {
                cartBadge.textContent = data.count;
                cartBadge.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Image zoom on hover (opsional)
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.main-image');
    
    if (mainImage) {
        mainImage.addEventListener('mousemove', function(e) {
            const img = this.querySelector('img');
            if (img) {
                const rect = this.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                img.style.transformOrigin = `${x}% ${y}%`;
            }
        });
        
        mainImage.addEventListener('mouseleave', function() {
            const img = this.querySelector('img');
            if (img) {
                img.style.transformOrigin = 'center center';
            }
        });
    }
    
    // Smooth scroll untuk anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Prevent quantity input dari nilai tidak valid
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('quantity');
    
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            const min = parseInt(this.min) || 1;
            const max = parseInt(this.max) || 99;
            let value = parseInt(this.value) || 1;
            
            if (value < min) value = min;
            if (value > max) value = max;
            
            this.value = value;
        });
    }
});
