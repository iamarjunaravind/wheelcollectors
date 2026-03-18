// WheelCollectors Main JS

const apiBase = (window.location.hostname.includes('workers.dev') || window.location.hostname.includes('pages.dev') || window.location.hostname.includes('wheelcollectors.in'))
    ? 'https://wheelcollectorsz.vipinlal5901.workers.dev/api'
    : '/api';

// Sync Cart with LocalStorage
function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '{}');
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const cart = getCart();
    const count = Object.values(cart).reduce((a, b) => a + b, 0);
    const countEl = document.getElementById('cart-count');
    if (countEl) countEl.innerText = count;
    
    // Sync mobile counts
    const mobileCountEls = document.querySelectorAll('.mobile-cart-count');
    mobileCountEls.forEach(el => el.innerText = count);
}

function addToCart(event, id, qty = 1) {
    if (event) event.stopPropagation();
    const cart = getCart();
    cart[id] = (cart[id] || 0) + Number(qty);
    saveCart(cart);
    showToast('Item added to cart!');
}

function createProductCard(p, isSlider = false) {
    const isOutOfStock = Number(p.stock) === 0;
    const stockCount = Number(p.stock);
    
    return `
        <div class="product-card ${isSlider ? 'slider-card' : ''} ${isOutOfStock ? 'out-of-stock-card' : ''}" onclick="window.location.href='product-details.html?id=${p.id}'" style="cursor: pointer;">
            <div class="product-image">
                ${p.badge ? `<div class="product-badge">${p.badge}</div>` : ''}
                ${isOutOfStock ? `<div class="product-badge out-of-stock-badge" style="left: auto; right: 15px;">SOLD OUT</div>` : ''}
                <img src="${p.image_url}" alt="${p.name}">
            </div>
            <div class="product-info">
                <h3 style="margin-bottom: 5px; min-height: 2.4em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${p.name}</h3>
                
                <div style="font-size: 0.75rem; margin-bottom: 15px; font-weight: 600; font-family: 'Outfit', sans-serif;">
                    ${isOutOfStock ? 
                        `<span style="color: var(--primary);">SOLD OUT</span>` : 
                        `<span style="color: #666;">
                            Available: ${stockCount} Units
                        </span>`
                    }
                </div>

                <div class="product-actions">
                    <div class="price">₹ ${Number(p.price).toLocaleString()}</div>
                    <button class="btn-cart" onclick="addToCart(event, ${p.id})" ${isOutOfStock ? 'disabled style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;"' : ''}>
                        <i class="fa ${isOutOfStock ? 'fa-ban' : 'fa-shopping-cart'}"></i>
                    </button>
                </div>
            </div>
        </div>`;
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.id = 'cart-toast';
    toast.style.cssText = `position: fixed; bottom: 30px; right: 30px; background: var(--primary); color: white; padding: 15px 30px; border-radius: 8px; z-index: 3000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 15px; animation: slideIn 0.5s ease-out;`;
    toast.innerHTML = `<i class="fa-solid fa-circle-check"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.5s ease-in-out';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Fragment Injection
async function injectFragments() {
    try {
        const headerRes = await fetch('fragments/header.html');
        if (headerRes.ok) {
            const headerHtml = await headerRes.text();
            document.getElementById('header-container').innerHTML = headerHtml;
        }

        const footerRes = await fetch('fragments/footer.html');
        if (footerRes.ok) {
            const footerHtml = await footerRes.text();
            document.getElementById('footer-container').innerHTML = footerHtml;
        }

        updateCartCount();
        checkLoginState();
    } catch (err) { console.error('Error injecting fragments:', err); }
}

function checkLoginState() {
    const token = localStorage.getItem('token');
    const userJson = localStorage.getItem('user');
    if (token && userJson) {
        const user = JSON.parse(userJson);
        const authHtml = `<a href="account.html" style="color: var(--text-dark); font-weight: 500; display: flex; align-items: center; gap: 8px;"><div style="width: 24px; height: 24px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">${user.name.charAt(0).toUpperCase()}</div> ${user.name.split(' ')[0]}'s Account</a>`;
        
        const authLinks = document.getElementById('auth-links');
        if (authLinks) authLinks.innerHTML = authHtml;
        
        const mobileAuthLinks = document.getElementById('mobile-auth-links');
        if (mobileAuthLinks) mobileAuthLinks.innerHTML = authHtml;
    }
}

function toggleMenu() {
    const wrapper = document.querySelector('.humberger-menu-wrapper');
    const overlay = document.querySelector('.humberger-menu-overlay');
    if (wrapper) wrapper.classList.toggle('active');
    if (overlay) overlay.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', () => {
    injectFragments();
});
