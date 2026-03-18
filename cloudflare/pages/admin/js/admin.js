const apiBase = (window.location.hostname.includes('workers.dev') || window.location.hostname.includes('pages.dev') || window.location.hostname.includes('wheelcollectors.in'))
    ? 'https://wheelcollectorsz.vipinlal5901.workers.dev/api'
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

async function handleDeleteRobust(btn, id, type) {
    if (!btn.classList.contains('confirming')) {
        btn.classList.add('confirming');
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> CONFIRM?';
        
        setTimeout(() => {
            if (btn.classList.contains('confirming')) {
                resetDeleteBtn(btn);
            }
        }, 3000);
        return;
    }

    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        const url = type === 'category' ? `${apiBase}/admin/categories/${id}` : `${apiBase}/admin/products/${id}`;
        const res = await fetch(url, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });

        if (res.ok) {
            if (type === 'category') typeof loadCategories === 'function' && loadCategories();
            else typeof loadProducts === 'function' && loadProducts();
        } else {
            if (res.status === 401) {
                alert('Session expired. Please login again.');
                adminLogout();
                return;
            }
            const result = await res.json();
            alert('Error: ' + (result.error || 'Failed to delete'));
            resetDeleteBtn(btn);
        }
    } catch (err) {
        console.error('Delete error:', err);
        alert('Connection error');
        resetDeleteBtn(btn);
    } finally {
        btn.disabled = false;
    }
}

function resetDeleteBtn(btn) {
    btn.classList.remove('confirming');
    btn.innerHTML = btn.dataset.originalHtml || '<i class="fas fa-trash"></i>';
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
