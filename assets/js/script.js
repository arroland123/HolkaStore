// Data produk (untuk filter dan sort)
let allProducts = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeProducts();
    setupEventListeners();
});

// Initialize products array
function initializeProducts() {
    const productCards = document.querySelectorAll('.product-card');
    allProducts = Array.from(productCards).map(card => ({
        element: card,
        name: card.querySelector('.product-name').textContent,
        price: parseInt(card.dataset.price),
        category: card.dataset.category,
        discount: card.querySelector('.badge') ? 
            parseInt(card.querySelector('.badge').textContent.match(/\d+/)[0]) : 0
    }));
}

// Setup event listeners
function setupEventListeners() {
    // Search
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }

    // Category filters
    const categoryCheckboxes = document.querySelectorAll('input[name="category"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleFilter);
    });

    // Size buttons
    const sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
        });
    });

    // Color buttons
    const colorButtons = document.querySelectorAll('.color-btn');
    colorButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            colorButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Rating checkboxes
    const ratingCheckboxes = document.querySelectorAll('input[name="rating"]');
    ratingCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleFilter);
    });



    // Pagination
    const pageButtons = document.querySelectorAll('.page-btn');
    pageButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                pageButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    // Product card hover effects
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        const iconBtns = card.querySelectorAll('.icon-btn');
        iconBtns.forEach((btn, index) => {
            btn.style.transitionDelay = `${index * 0.1}s`;
        });
    });

    // Filter subtitle toggle
    const filterSubtitles = document.querySelectorAll('.filter-subtitle');
    filterSubtitles.forEach(subtitle => {
        subtitle.addEventListener('click', function() {
            const filterOptions = this.nextElementSibling;
            if (filterOptions) {
                filterOptions.style.display = 
                    filterOptions.style.display === 'none' ? 'flex' : 'none';
                
                const icon = this.querySelector('i');
                if (icon) {
                    icon.style.transform = 
                        filterOptions.style.display === 'none' ? 
                        'rotate(-90deg)' : 'rotate(0deg)';
                }
            }
        });
    });

    // Product card click (go to detail)
    productCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (!e.target.closest('.icon-btn')) {
                // Simulate going to product detail
                console.log('Going to product detail...');
                // window.location.href = 'product-detail.php?id=' + productId;
            }
        });
    });
}

// Handle search
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    allProducts.forEach(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm);
        product.element.style.display = matchesSearch ? 'block' : 'none';
    });

    updateProductCount();
}

// Handle filter
function handleFilter() {
    const selectedCategories = Array.from(
        document.querySelectorAll('input[name="category"]:checked')
    ).map(cb => cb.value);

    const minPrice = parseInt(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseInt(document.getElementById('maxPrice').value) || 999999999;

    const selectedRatings = Array.from(
        document.querySelectorAll('input[name="rating"]:checked')
    ).map(cb => parseInt(cb.value));

    allProducts.forEach(product => {
        const matchesCategory = selectedCategories.length === 0 || 
            selectedCategories.includes(product.category);
        
        const matchesPrice = product.price >= minPrice && product.price <= maxPrice;
        
        const matchesRating = selectedRatings.length === 0 || 
            selectedRatings.some(rating => rating <= 5); // Simplified

        const shouldShow = matchesCategory && matchesPrice && matchesRating;
        product.element.style.display = shouldShow ? 'block' : 'none';
    });

    updateProductCount();
}

// Apply price filter
function applyFilter() {
    handleFilter();
}



// Update product count
function updateProductCount() {
    const visibleProducts = allProducts.filter(
        p => p.element.style.display !== 'none'
    ).length;
    
    const breadcrumb = document.querySelector('.breadcrumb');
    if (breadcrumb) {
        breadcrumb.textContent = 
            `Menampilkan ${visibleProducts} Dari ${allProducts.length} Produk`;
    }
}

// Toggle filter
function toggleFilter(element) {
    element.classList.toggle('collapsed');
    const content = element.nextElementSibling;
    if (content) {
        content.classList.toggle('hidden');
    }
}

// Product card interactions
document.addEventListener('click', function(e) {
    // Add to cart
    if (e.target.closest('.icon-btn .fa-shopping-cart')) {
        e.stopPropagation();
        const productCard = e.target.closest('.product-card');
        const productName = productCard.querySelector('.product-name').textContent;
        showNotification(`${productName} ditambahkan ke keranjang!`);
    }

    // Add to wishlist
    if (e.target.closest('.icon-btn .fa-heart')) {
        e.stopPropagation();
        const btn = e.target.closest('.icon-btn');
        btn.classList.toggle('liked');
        const icon = btn.querySelector('i');
        
        if (btn.classList.contains('liked')) {
            icon.style.color = '#FF4757';
            showNotification('Ditambahkan ke wishlist!');
        } else {
            icon.style.color = '';
            showNotification('Dihapus dari wishlist!');
        }
    }

    // Quick view
    if (e.target.closest('.icon-btn .fa-eye')) {
        e.stopPropagation();
        const productCard = e.target.closest('.product-card');
        const productName = productCard.querySelector('.product-name').textContent;
        showNotification(`Menampilkan detail ${productName}`);
        // Implement modal here
    }
});

// Show notification
function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
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

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    .icon-btn .fa-heart {
        transition: color 0.3s;
    }

    .icon-btn.liked .fa-heart {
        color: #FF4757 !important;
    }
`;
document.head.appendChild(style);

// Smooth scroll
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

// Newsletter form
const newsletterForm = document.querySelector('.newsletter');
if (newsletterForm) {
    const input = newsletterForm.querySelector('input');
    const button = newsletterForm.querySelector('button');
    
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const email = input.value.trim();
        
        if (email && email.includes('@')) {
            showNotification('Terima kasih telah berlangganan newsletter!');
            input.value = '';
        } else {
            showNotification('Mohon masukkan email yang valid!');
        }
    });
}

// Lazy loading for images (if you add real images later)
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// Mobile menu toggle (for responsive)
function createMobileMenu() {
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && !document.querySelector('.mobile-filter-btn')) {
            const btn = document.createElement('button');
            btn.className = 'mobile-filter-btn';
            btn.innerHTML = '<i class="fas fa-filter"></i> Filter';
            btn.style.cssText = `
                display: block;
                width: 100%;
                max-width: 200px;
                padding: 12px;
                background: #4169E1;
                color: white;
                border: none;
                border-radius: 25px;
                font-size: 15px;
                font-weight: 600;
                margin: 0 auto 20px auto;
                cursor: pointer;
                box-shadow: 0 4px 10px rgba(65, 105, 225, 0.2);
            `;
            
            sidebar.parentNode.insertBefore(btn, sidebar);
            sidebar.style.display = 'none';
            
            btn.addEventListener('click', function() {
                sidebar.style.display = 
                    sidebar.style.display === 'none' ? 'block' : 'none';
            });
        }
    }
}

window.addEventListener('resize', createMobileMenu);
createMobileMenu();

// Promo animation
const promoObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.querySelector('.promo-container').classList.add('visible');
        }
    });
}, { threshold: 0.3 });

const promoSection = document.querySelector('.promo-section');
if (promoSection) {
    promoObserver.observe(promoSection);
}