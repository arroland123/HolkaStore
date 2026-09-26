// ==========================================
// CHECKOUT JAVASCRIPT - HOLKA STORE
// ==========================================

// Payment method selection
function selectPayment(radio) {
    // Remove active class from all options
    const options = document.querySelectorAll('.payment-option');
    options.forEach(option => option.classList.remove('active'));
    
    // Add active class to selected option
    radio.closest('.payment-option').classList.add('active');
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('.btn-checkout');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<span>Memproses...</span>';
            
            // Simulate processing (replace with actual form submission)
            setTimeout(() => {
                // Submit form via AJAX or normal submit
                processCheckout();
            }, 1000);
        });
    }
    
    // Auto-format phone number
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
    }
    
    // Auto-format postal code
    const postalInput = document.getElementById('postalCode');
    if (postalInput) {
        postalInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            e.target.value = value;
        });
    }
    
    // Initialize payment options
    const paymentOptions = document.querySelectorAll('.payment-option input[type="radio"]');
    paymentOptions.forEach(option => {
        if (option.checked) {
            option.closest('.payment-option').classList.add('active');
        }
    });
});

// Validate form
function validateForm() {
    const form = document.getElementById('checkoutForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#ff4757';
            
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
        } else {
            field.style.borderColor = '#e0e0e0';
        }
    });
    
    // Validate email format
    const emailField = document.getElementById('email');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            showNotification('Format email tidak valid', 'error');
            emailField.style.borderColor = '#ff4757';
            isValid = false;
            if (!firstInvalidField) firstInvalidField = emailField;
        }
    }
    
    // Validate phone number
    const phoneField = document.getElementById('phone');
    if (phoneField && phoneField.value) {
        const phoneRegex = /^[0-9]{10,13}$/;
        if (!phoneRegex.test(phoneField.value)) {
            showNotification('Nomor telepon harus 10-13 digit', 'error');
            phoneField.style.borderColor = '#ff4757';
            isValid = false;
            if (!firstInvalidField) firstInvalidField = phoneField;
        }
    }
    
    // Validate postal code
    const postalField = document.getElementById('postalCode');
    if (postalField && postalField.value) {
        const postalRegex = /^[0-9]{5}$/;
        if (!postalRegex.test(postalField.value)) {
            showNotification('Kode pos harus 5 digit', 'error');
            postalField.style.borderColor = '#ff4757';
            isValid = false;
            if (!firstInvalidField) firstInvalidField = postalField;
        }
    }
    
    if (!isValid) {
        showNotification('Mohon lengkapi semua field yang wajib diisi', 'error');
        if (firstInvalidField) {
            firstInvalidField.focus();
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    return isValid;
}

// Process checkout
function processCheckout() {
    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);
    
    fetch('process_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to success page
            window.location.href = 'order_success.php?order_id=' + data.order_id;
        } else {
            // Show error
            showNotification(data.message || 'Terjadi kesalahan saat memproses pesanan', 'error');
            
            // Reset button
            const submitBtn = form.querySelector('.btn-checkout');
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<span>Bayar Sekarang</span><i class="fas fa-arrow-right"></i>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
        
        // Reset button
        const submitBtn = form.querySelector('.btn-checkout');
        submitBtn.classList.remove('loading');
        submitBtn.innerHTML = '<span>Bayar Sekarang</span><i class="fas fa-arrow-right"></i>';
    });
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
        max-width: 400px;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Save form data to localStorage (auto-save)
function autoSaveForm() {
    const form = document.getElementById('checkoutForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            const formData = {};
            
            inputs.forEach(field => {
                if (field.name && field.value) {
                    formData[field.name] = field.value;
                }
            });
            
            localStorage.setItem('checkout_form_data', JSON.stringify(formData));
        });
    });
}

// Load saved form data
function loadSavedFormData() {
    const savedData = localStorage.getItem('checkout_form_data');
    
    if (savedData) {
        try {
            const formData = JSON.parse(savedData);
            
            Object.keys(formData).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field && !field.value) {
                    field.value = formData[key];
                }
            });
        } catch (error) {
            console.error('Error loading saved data:', error);
        }
    }
}

// Clear saved form data after successful checkout
function clearSavedFormData() {
    localStorage.removeItem('checkout_form_data');
}

// Initialize auto-save on load
document.addEventListener('DOMContentLoaded', function() {
    loadSavedFormData();
    autoSaveForm();
});

// Prevent accidental page leave
window.addEventListener('beforeunload', function(e) {
    const form = document.getElementById('checkoutForm');
    if (form) {
        const hasContent = Array.from(form.querySelectorAll('input, textarea, select'))
            .some(field => field.value.trim() !== '');
        
        if (hasContent) {
            e.preventDefault();
            e.returnValue = 'Anda memiliki data yang belum disimpan. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    }
});

// Format currency display (if needed)
function formatCurrency(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// Smooth scroll to element
function scrollToElement(element) {
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
}

// Animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Console welcome message
console.log('%cHolka Store 🛍️', 'font-size: 20px; font-weight: bold; color: #4169E1;');
console.log('%cCheckout Page v1.0', 'font-size: 14px; color: #666;');
