<?php require_once 'admin_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Vault</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --premium-bg: #0f172a;
            --premium-surface: #1e293b;
            --premium-primary: #6366f1;
            --premium-secondary: #818cf8;
            --premium-accent: #f43f5e;
            --premium-text: #f8fafc;
            --premium-text-muted: #94a3b8;
            --premium-glass: rgba(30, 41, 59, 0.7);
            --premium-border: rgba(255, 255, 255, 0.1);
            --premium-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--premium-bg);
            color: var(--premium-text);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Glassmorphism */
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-right: 1px solid var(--premium-border);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .admin-sidebar .logo {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 50px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .admin-sidebar .logo i {
            background: linear-gradient(135deg, var(--premium-primary), var(--premium-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .admin-nav a {
            color: var(--premium-text-muted);
            text-decoration: none;
            padding: 14px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .admin-nav a i {
            font-size: 1.1rem;
            transition: transform 0.3s;
        }

        .admin-nav a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-nav a:hover i {
            transform: translateX(3px);
        }

        .admin-nav a.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 100%);
            color: var(--premium-primary);
            border-left: 3px solid var(--premium-primary);
            border-radius: 0 12px 12px 0;
            padding-left: 15px;
        }
        
        .admin-nav a.active i {
            color: var(--premium-primary);
            filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.5));
        }

        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            max-width: calc(100% - 280px);
        }

        /* Glass Header */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: var(--premium-glass);
            backdrop-filter: blur(12px);
            padding: 20px 35px;
            border-radius: 20px;
            border: 1px solid var(--premium-border);
            box-shadow: var(--premium-shadow);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--premium-surface);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--premium-border);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.5);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 15px;
        }

        .admin-card {
            background: var(--premium-surface);
            border-radius: 24px;
            border: 1px solid var(--premium-border);
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: var(--premium-shadow);
        }

        .admin-card-header {
            padding: 25px 35px;
            border-bottom: 1px solid var(--premium-border);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .admin-table th {
            text-align: left;
            padding: 20px 35px;
            background: rgba(15, 23, 42, 0.5);
            color: var(--premium-text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            border-bottom: 1px solid var(--premium-border);
        }

        .admin-table td {
            padding: 20px 35px;
            border-bottom: 1px solid var(--premium-border);
            vertical-align: middle;
            color: #e2e8f0;
        }

        .admin-table tr:last-child td { border-bottom: none; }
        .admin-table tr:hover td { background: rgba(255, 255, 255, 0.02); }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--premium-primary), var(--premium-secondary));
            color: white;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -5px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .badge-pending { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-completed { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-cancelled { background: rgba(244, 63, 94, 0.1); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.2); }
        
        .bg-blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .bg-green { background: linear-gradient(135deg, #10b981, #34d399); }
        .bg-purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
        .bg-orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        
        /* Robust Delete Button Styles */
        .delete-btn-robust.confirming {
            background: #ef4444 !important;
            color: white !important;
            padding: 6px 15px !important;
            border-radius: 6px !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            animation: pulse-red 1.5s infinite;
        }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
    <script>
        // Scroll Preservation Logic
        window.addEventListener('scroll', () => {
            sessionStorage.setItem('admin_scroll_pos', window.scrollY);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const isEditing = urlParams.has('edit');
            const isSuccess = urlParams.get('status') === 'success';

            if (isEditing || isSuccess) {
                sessionStorage.removeItem('admin_scroll_pos');
                window.scrollTo(0, 0);
            } else {
                const scrollPos = sessionStorage.getItem('admin_scroll_pos');
                if (scrollPos) {
                    window.scrollTo({
                        top: parseInt(scrollPos),
                        behavior: 'instant'
                    });
                }
            }
        });

        // Robust Delete Button Styles
        function handleDeleteRobust(btn) {
            // Save position explicitly before submission
            sessionStorage.setItem('admin_scroll_pos', window.scrollY);
            
            if (!btn.classList.contains('confirming')) {
                btn.classList.add('confirming');
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> CONFIRM?';
                
                setTimeout(() => {
                    if (btn.classList.contains('confirming')) {
                        resetDeleteBtn(btn);
                    }
                }, 3000);
                
                return false;
            } else {
                const form = btn.closest('form');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = btn.name;
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
        
        function resetDeleteBtn(btn) {
            btn.classList.remove('confirming');
            btn.innerHTML = btn.dataset.originalHtml || '<i class="fas fa-trash"></i>';
        }

        // Intercept all form submissions to save scroll
        document.addEventListener('submit', () => {
            sessionStorage.setItem('admin_scroll_pos', window.scrollY);
        });
    </script>
</head>
<body>
    <div class="admin-sidebar">
        <a href="index.php" class="logo">
            The Vault Admin
        </a>
        <nav class="admin-nav">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i> Products
            </a>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="../index.php"><i class="fas fa-eye"></i> View Site</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>
    </div>
    <div class="admin-main">
        <header class="admin-header">
            <h2 style="margin: 0; font-size: 1.25rem;">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>
            <div style="color: #64748b; font-size: 0.875rem;">
                <i class="far fa-calendar"></i> <?= date('F j, Y') ?>
            </div>
        </header>
