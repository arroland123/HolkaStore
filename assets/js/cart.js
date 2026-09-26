// ==========================================
// KERANJANG BELANJA JAVASCRIPT - HOLKA STORE
// ==========================================

// Update quantity item
function updateQuantity(cartKey, change) {
    const qtyInput = document.querySelector(`.qty-input[data-key="${cartKey}"]`);
    if (!qtyInput) return;
    
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;
    
    // Validasi minimum 1
    if (newQty < 1) {
        newQty = 1;
    }
    
    // Validasi maximum (stok)
    const maxQty = parseInt(qtyInput.max);
    if (newQty > maxQty) {
        showNotification('Stok tidak mencukupi', 'warning');
        return;
    }
    
    // Update UI optimistically
    qtyInput.value = newQty;
    
    // Show loading
    showLoading();
    
    // AJAX request to update cart
    fetch('update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `key=${encodeURIComponent(cartKey)}&qty=${newQty}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            // Update all prices
            updateCartDisplay(data);
            showNotification('Keranjang diperbarui', 'success');
        } else {
            // Revert if failed
            qtyInput.value = currentQty;
            showNotification(data.message || 'Gagal mengupdate keranjang', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        qtyInput.value = currentQty;
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Remove item from cart
function removeItem(cartKey) {
    // Konfirmasi
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        return;
    }
    
    showLoading();
    
    fetch('remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `key=${encodeURIComponent(cartKey)}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            // Animate remove
            const cartItem = document.querySelector(`.cart-item[data-key="${cartKey}"]`);
            if (cartItem) {
                cartItem.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    // Reload page to update cart
                    window.location.reload();
                }, 300);
            }
            
            showNotification('Produk dihapus dari keranjang', 'success');
        } else {
            showNotification(data.message || 'Gagal menghapus produk', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Clear entire cart
function clearCart() {
    if (!confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')) {
        return;
    }
    
    showLoading();
    
    fetch('clear_cart.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showNotification('Keranjang dikosongkan', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Gagal mengosongkan keranjang', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Proceed to checkout
function checkout() {
    // Validasi cart tidak kosong
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length === 0) {
        showNotification('Keranjang masih kosong', 'warning');
        return;
    }
    
    // Redirect ke halaman checkout
    window.location.href = 'checkout.php';
}

// Update cart display (prices, totals, etc)
function updateCartDisplay(data) {
    // Update subtotal per item
    if (data.items) {
        data.items.forEach(item => {
            const subtotalElement = document.querySelector(`.cart-item[data-key="${item.key}"] .subtotal-value`);
            if (subtotalElement) {
                subtotalElement.textContent = formatRupiah(item.subtotal);
            }
        });
    }
    
    // Update summary
    if (data.summary) {
        const subtotalElement = document.getElementById('subtotalValue');
        const shippingElement = document.getElementById('shippingValue');
        const totalElement = document.getElementById('totalValue');
        
        if (subtotalElement) {
            subtotalElement.textContent = formatRupiah(data.summary.subtotal);
        }
        
        if (shippingElement) {
            if (data.summary.shipping_cost === 0) {
                shippingElement.textContent = 'Gratis';
                shippingElement.classList.add('free-shipping');
            } else {
                shippingElement.textContent = formatRupiah(data.summary.shipping_cost);
                shippingElement.classList.remove('free-shipping');
            }
        }
        
        if (totalElement) {
            totalElement.textContent = formatRupiah(data.summary.total);
        }
        
    }
}

// Format number to Rupiah
function formatRupiah(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// Show loading overlay
function showLoading() {
    // Remove existing loading if any
    const existingLoading = document.querySelector('.loading-overlay');
    if (existingLoading) {
        existingLoading.remove();
    }
    
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
            <p>Memproses...</p>
        </div>
    `;
    
    document.body.appendChild(loading);
}

// Hide loading overlay
function hideLoading() {
    const loading = document.querySelector('.loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotif = document.querySelector('.notification');
    if (existingNotif) {
        existingNotif.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'notification';
    
    // Set icon and color based on type
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
    notification.style.background = bgColor;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Animation for slide out
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-100%);
        }
    }
`;
document.head.appendChild(style);

// Prevent quantity input manual change
document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.qty-input');
    
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const min = 1;
            const max = parseInt(this.max) || 99;
            let value = parseInt(this.value) || 1;
            
            if (value < min) value = min;
            if (value > max) value = max;
            
            if (value !== parseInt(this.value)) {
                const cartKey = this.dataset.key;
                const change = value - parseInt(this.value);
                updateQuantity(cartKey, change);
            }
        });
    });
    
    // Auto update cart badge in header
    updateCartBadge();
});

// Update cart badge count
function updateCartBadge() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (data.count > 0) {
                if (cartBadge) {
                    cartBadge.textContent = data.count;
                } else {
                    // Create badge if doesn't exist
                    const cartLink = document.querySelector('.nav a[href="cart.php"]');
                    if (cartLink) {
                        const badge = document.createElement('span');
                        badge.className = 'cart-badge';
                        badge.textContent = data.count;
                        cartLink.appendChild(badge);
                    }
                }
            } else {
                if (cartBadge) {
                    cartBadge.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart badge:', error);
        });
}

// Apply coupon code (optional feature)
function applyCoupon() {
    const couponCode = document.getElementById('couponCode')?.value;
    
    if (!couponCode || couponCode.trim() === '') {
        showNotification('Silakan masukkan kode kupon', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('apply_coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `code=${encodeURIComponent(couponCode)}`
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showNotification(data.message || 'Kupon berhasil diterapkan', 'success');
            window.location.reload();
        } else {
            showNotification(data.message || 'Kode kupon tidak valid', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for clear cart
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const clearBtn = document.querySelector('.btn-clear-cart');
        if (clearBtn) {
            clearBtn.click();
        }
    }
    
    // Escape to go back to shopping
    if (e.key === 'Escape') {
        window.location.href = 'index.php';
    }
});

// Console welcome message
console.log('%cHolka Store 🛍️', 'font-size: 20px; font-weight: bold; color: #4169E1;');
console.log('%cKeranjang Belanja v1.0', 'font-size: 14px; color: #666;');
