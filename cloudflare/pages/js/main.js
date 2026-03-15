// Wheel Collectors Main JS

const apiBase = (window.location.hostname.includes('workers.dev') || window.location.hostname.includes('pages.dev'))
    ? `https://wheel-collectors-api.${window.location.hostname.split('.')[window.location.hostname.split('.').length - 3]}.workers.dev/api`
    : 'https://wheel-collectors-api.arjunaravinda.workers.dev/api';

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
}

function addToCart(event, id, qty = 1) {
    if (event) event.stopPropagation();
    const cart = getCart();
    cart[id] = (cart[id] || 0) + Number(qty);
    saveCart(cart);
    showToast('Item added to cart!');
}

function createProductCard(p, isSlider = false) {
    return `
        <div class="product-card ${isSlider ? 'slider-card' : ''}" onclick="window.location.href='product-details.html?id=${p.id}'" style="cursor: pointer;">
            <div class="product-image">
                ${p.badge ? `<div class="product-badge">${p.badge}</div>` : ''}
                <img src="${p.image_url}" alt="${p.name}">
            </div>
            <div class="product-info">
                <h3>${p.name}</h3>
                <div class="product-actions">
                    <div class="price">₹ ${Number(p.price).toLocaleString()}</div>
                    <button class="btn-cart" onclick="addToCart(event, ${p.id})">
                        <i class="fa fa-shopping-cart"></i>
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
        const headerHtml = await headerRes.text();
        document.getElementById('header-container').innerHTML = headerHtml;
        updateCartCount();
        checkLoginState();
    } catch (err) { console.error('Error injecting fragments:', err); }
}

function checkLoginState() {
    const token = localStorage.getItem('token');
    if (token) {
        // Simple client-side check, actual verification happens at API level
        // Replace login link with account/logout
        const authLinks = document.getElementById('auth-links');
        if (authLinks) {
            authLinks.innerHTML = `<a href="logout.html" style="color: var(--text-dark); font-weight: 500;"><i class="fa-solid fa-user"></i> Logout</a>`;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    injectFragments();
});
