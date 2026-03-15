const apiBase = (window.location.hostname.includes('workers.dev') || window.location.hostname.includes('pages.dev') || window.location.hostname.includes('wheelcollectors.in'))
    ? 'https://wheel-collectors-api.vipinlal5901.workers.dev/api'
    : '/api'; // Fallback for local or relative paths

function getAuthHeaders() {
    const token = localStorage.getItem('token');
    return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };
}

function checkAdminAuth() {
    const userJson = localStorage.getItem('user');
    const token = localStorage.getItem('token');
    if (!token || !userJson) {
        window.location.href = '../login.html?redirect=' + encodeURIComponent(window.location.pathname);
        return false;
    }
    const user = JSON.parse(userJson);
    if (user.role !== 'admin') {
        window.location.href = '../index.html';
        return false;
    }
    return true;
}

function adminLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '../login.html';
}

async function injectAdminFragments() {
    try {
        const headerRes = await fetch('fragments/header.html');
        const headerHtml = await headerRes.text();
        document.body.insertAdjacentHTML('afterbegin', headerHtml);
        
        // Mark active nav
        const page = window.location.pathname.split('/').pop().replace('.html', '');
        const navId = 'nav-' + (page || 'index');
        const navLink = document.getElementById(navId);
        if (navLink) navLink.classList.add('active');
        
        // Set welcome message if element exists
        const welcomeEl = document.getElementById('admin-welcome');
        if (welcomeEl) {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            welcomeEl.innerText = `Welcome, ${user.name || 'Admin'}`;
        }
    } catch (err) { console.error('Error injecting admin fragments:', err); }
}

// Initial check
if (checkAdminAuth()) {
    document.addEventListener('DOMContentLoaded', injectAdminFragments);
}
