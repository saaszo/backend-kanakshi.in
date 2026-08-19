<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kanakshi Executive Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #090d16;
            --sidebar-hover: #131b2e;
            --sidebar-active: #1e293b;
            --sidebar-border: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-text-bright: #f8fafc;
            --main-bg: #f8fafc;
            --surface-bg: #ffffff;
            --border: #e2e8f0;
            --border-dark: #cbd5e1;
            --text-heading: #090d16;
            --text-body: #334155;
            --text-muted: #64748b;
            --primary: #090d16;
            --primary-accent: #2563eb;
            --accent-gold: #c59b27;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0284c7;
        }

        /* 1. Strict Sharp Corners (Border-Radius 0) Everywhere */
        *, *::before, *::after {
            box-sizing: border-box;
            border-radius: 0px !important;
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--main-bg);
            color: var(--text-body);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Main App Grid Shell */
        .dashboard-shell {
            display: flex;
            min-height: 100vh;
            background: var(--main-bg);
        }

        /* Executive Sidebar (Fixed Left) */
        .admin-sidebar {
            width: 270px;
            min-width: 270px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sidebar-border);
            background: #060910;
        }

        .brand-logo-square {
            width: 36px;
            height: 36px;
            background: #ffffff;
            color: #090d16;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #ffffff;
        }

        .brand-badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #94a3b8;
        }

        /* Sidebar Navigation & Accordion Submenus */
        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: var(--sidebar-border);
        }

        .sidebar-section {
            margin-bottom: 4px;
        }

        .sidebar-direct-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--sidebar-text);
            font-size: 13.5px;
            font-weight: 600;
            border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }

        .sidebar-direct-link:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-text-bright);
        }

        .sidebar-direct-link.active {
            background-color: var(--sidebar-active);
            color: #ffffff;
            border-left-color: #ffffff;
            font-weight: 700;
        }

        .sidebar-menu-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 2px;
        }

        .menu-item-parent {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            color: var(--sidebar-text);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border-left: 3px solid transparent;
            user-select: none;
            transition: all 0.15s ease;
        }

        .menu-item-parent:hover {
            background-color: var(--sidebar-hover);
            color: var(--sidebar-text-bright);
        }

        .menu-item-parent.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.04);
            border-left-color: #94a3b8;
        }

        .parent-label {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .parent-meta {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .arrow-icon {
            font-size: 11px;
            transition: transform 0.2s ease;
            color: #64748b;
        }

        .menu-item-parent.open .arrow-icon {
            transform: rotate(180deg);
            color: #ffffff;
        }

        .badge-count {
            background: #2563eb;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
        }

        /* Nested Submenu List */
        .submenu-list {
            display: none;
            flex-direction: column;
            background: #05080f;
            border-left: 1px solid var(--sidebar-border);
            margin-left: 18px;
            padding: 4px 0;
        }

        .submenu-list.expanded {
            display: flex;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 14px 8px 16px;
            color: #94a3b8;
            font-size: 12.5px;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .submenu-link i {
            margin-right: 8px;
            font-size: 13px;
        }

        .submenu-link:hover {
            background-color: var(--sidebar-hover);
            color: #ffffff;
        }

        .submenu-link.active {
            background-color: var(--sidebar-active);
            color: #ffffff;
            font-weight: 700;
            border-left: 2px solid #ffffff;
        }

        .sub-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 1px 5px;
            background: #334155;
            color: #ffffff;
        }

        .sub-badge.warn {
            background: #d97706;
            color: #ffffff;
        }

        /* Sidebar Footer Actions */
        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--sidebar-border);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            background: #060910;
        }

        .footer-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--sidebar-border);
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.15s ease;
            width: 100%;
        }

        .footer-action-btn:hover {
            background: #ffffff;
            color: #090d16;
            border-color: #ffffff;
        }

        .footer-action-btn.logout-btn:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* Workspace Main Content Area */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: var(--main-bg);
        }

        .admin-shell-grid {
            padding: 32px 36px;
            max-width: 1560px;
            margin: 0 auto;
            width: 100%;
        }

        /* Executive Banner */
        .admin-banner {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 24px 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .admin-banner h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-heading);
            margin: 0;
            letter-spacing: -0.02em;
        }

        .admin-banner .brand {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }

        .admin-banner .lead {
            font-size: 13.5px;
            color: var(--text-muted);
            margin: 0;
        }

        /* Metrics / KPI Stat Tiles */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .admin-stat {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .admin-stat small {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
        }

        .admin-stat strong {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }

        .admin-stat span {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Admin Content Card Sections */
        .admin-section {
            background: #ffffff;
            border: 1px solid var(--border);
            padding: 28px;
            margin-bottom: 24px;
        }

        .admin-section h3 {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 6px;
        }

        /* Tables & Lists */
        .table {
            border: 1px solid var(--border);
            margin-bottom: 0;
        }

        .table thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--border);
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 13.5px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fafbfd;
        }

        /* Inputs & Form Controls */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="search"],
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        textarea,
        select,
        .form-control,
        .form-select {
            border: 1px solid var(--border-dark) !important;
            padding: 10px 14px;
            font-size: 13.5px;
            color: var(--text-heading);
            background: #ffffff;
            outline: none;
            box-shadow: none !important;
            width: 100%;
            display: block;
            box-sizing: border-box;
        }

        input:focus,
        textarea:focus,
        select:focus,
        .form-control:focus,
        .form-select:focus {
            border-color: #090d16 !important;
        }

        /* Universal Form Field Containers */
        .admin-field, .field {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            width: 100% !important;
            margin-bottom: 16px !important;
        }

        .admin-field label, .field label {
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #0f172a !important;
            display: block !important;
            margin: 0 0 2px 0 !important;
        }

        .admin-field input, .field input,
        .admin-field select, .field select,
        .admin-field textarea, .field textarea {
            width: 100% !important;
            display: block !important;
            box-sizing: border-box !important;
        }

        .admin-help {
            font-size: 11.5px !important;
            color: #64748b !important;
            margin-top: 3px !important;
            line-height: 1.4 !important;
        }

        .admin-toggle, .checkbox-row {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            margin-bottom: 10px !important;
            cursor: pointer !important;
        }

        .admin-toggle input, .checkbox-row input {
            width: auto !important;
            margin: 0 !important;
        }

        /* Buttons */
        .btn, .button {
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.02em;
            padding: 9px 18px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-primary, .button.primary {
            background: #090d16;
            color: #ffffff;
            border-color: #090d16;
        }

        .btn-primary:hover, .button.primary:hover {
            background: #1e293b;
            color: #ffffff;
            border-color: #1e293b;
        }

        .btn-outline-secondary, .button.secondary {
            background: #ffffff;
            color: var(--text-heading);
            border-color: var(--border-dark);
        }

        .btn-outline-secondary:hover, .button.secondary:hover {
            background: #f1f5f9;
            color: #000000;
            border-color: #000000;
        }

        .btn-success {
            background: #16a34a;
            color: #ffffff;
            border-color: #16a34a;
        }

        .btn-outline-danger {
            border-color: #fca5a5;
            color: #dc2626;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Auth Pages (Login Shell) */
        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #090d16;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border: 1px solid #334155;
            padding: 40px 36px;
        }

        @media (max-width: 991px) {
            .dashboard-shell {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
                min-width: 100%;
                height: auto;
                position: relative;
            }
            .admin-shell-grid {
                padding: 20px 16px;
            }
        }
        /* Global Executive Toast Notifications */
        .admin-toast-stack {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
            max-width: 400px;
            width: calc(100% - 48px);
        }

        .admin-toast-item {
            pointer-events: auto;
            background: #090d16;
            color: #ffffff;
            border: 1px solid #334155;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.35);
            animation: toastSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.2s ease;
        }

        .admin-toast-item.toast-success {
            border-left: 4px solid #16a34a;
        }

        .admin-toast-item.toast-danger {
            border-left: 4px solid #dc2626;
        }

        .admin-toast-item.toast-warning {
            border-left: 4px solid #f59e0b;
        }

        .admin-toast-item.toast-info {
            border-left: 4px solid #2563eb;
        }

        @keyframes toastSlideIn {
            from { transform: translateX(40px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Global Sharp Confirmation Modal */
        .admin-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(9, 13, 22, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .admin-confirm-overlay.is-active {
            display: flex;
        }

        .admin-confirm-box {
            background: #ffffff;
            border: 1px solid #1e293b;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            width: min(460px, 100%);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .admin-confirm-header {
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .admin-confirm-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .admin-confirm-body {
            padding: 24px;
            font-size: 14px;
            color: #334155;
            line-height: 1.6;
        }

        .admin-confirm-footer {
            padding: 16px 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #ffffff;
        }
    </style>
</head>
<body>
    @yield('content')

    <!-- Global Toast Container -->
    <div id="admin-toast-stack" class="admin-toast-stack"></div>

    <!-- Global Confirmation Modal -->
    <div id="admin-confirm-overlay" class="admin-confirm-overlay" aria-hidden="true">
        <div class="admin-confirm-box">
            <div class="admin-confirm-header">
                <h4 id="admin-confirm-title">Confirm Action</h4>
                <button type="button" class="btn btn-sm btn-link p-0 text-dark text-decoration-none" onclick="closeAdminConfirm()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="admin-confirm-body" id="admin-confirm-message">
                Are you sure you want to proceed with this operation?
            </div>
            <div class="admin-confirm-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeAdminConfirm()">Cancel</button>
                <button type="button" class="btn btn-danger" id="admin-confirm-proceed-btn">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Interactive Sidebar Accordion & Global Handlers -->
    <script>
        function toggleSubmenu(parentElem) {
            const group = parentElem.parentElement;
            const submenu = group.querySelector('.submenu-list');
            const isCurrentlyOpen = submenu.classList.contains('expanded');

            document.querySelectorAll('.submenu-list.expanded').forEach(el => {
                if (el !== submenu) {
                    el.classList.remove('expanded');
                    if (el.previousElementSibling) {
                        el.previousElementSibling.classList.remove('open');
                    }
                }
            });

            if (isCurrentlyOpen) {
                submenu.classList.remove('expanded');
                parentElem.classList.remove('open');
            } else {
                submenu.classList.add('expanded');
                parentElem.classList.add('open');
            }
        }

        // Global Toast Dispatcher
        window.showAdminToast = function(message, type = 'success', duration = 3500) {
            const stack = document.getElementById('admin-toast-stack');
            if (!stack) return;

            const toast = document.createElement('div');
            toast.className = `admin-toast-item toast-${type}`;
            
            let icon = 'bi-check-circle-fill text-success';
            if (type === 'danger') icon = 'bi-exclamation-triangle-fill text-danger';
            if (type === 'warning') icon = 'bi-exclamation-circle-fill text-warning';
            if (type === 'info') icon = 'bi-info-circle-fill text-primary';

            toast.innerHTML = `
                <div class="d-flex align-items-center gap-2" style="font-size: 13.5px; font-weight: 600;">
                    <i class="bi ${icon}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn btn-link p-0 text-white text-decoration-none" style="opacity: 0.7;" onclick="this.parentElement.remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;

            stack.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(40px)';
                setTimeout(() => toast.remove(), 200);
            }, duration);
        };

        // Global Confirmation Dialog
        let confirmActionCallback = null;
        window.showAdminConfirm = function({ title = 'Confirm Action', message = 'Are you sure?', confirmText = 'Confirm', confirmClass = 'btn-danger', onConfirm = null }) {
            document.getElementById('admin-confirm-title').textContent = title;
            document.getElementById('admin-confirm-message').textContent = message;
            
            const proceedBtn = document.getElementById('admin-confirm-proceed-btn');
            proceedBtn.textContent = confirmText;
            proceedBtn.className = `btn ${confirmClass}`;

            confirmActionCallback = onConfirm;

            const overlay = document.getElementById('admin-confirm-overlay');
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        window.closeAdminConfirm = function() {
            const overlay = document.getElementById('admin-confirm-overlay');
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            confirmActionCallback = null;
        };

        document.getElementById('admin-confirm-proceed-btn')?.addEventListener('click', () => {
            if (typeof confirmActionCallback === 'function') {
                confirmActionCallback();
            }
            closeAdminConfirm();
        });

        // Intercept data-confirm forms and buttons globally
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.submenu-link.active').forEach(activeLink => {
                const submenu = activeLink.closest('.submenu-list');
                if (submenu) {
                    submenu.classList.add('expanded');
                    if (submenu.previousElementSibling) {
                        submenu.previousElementSibling.classList.add('open', 'active');
                    }
                }
            });

            // Delegate form submit confirmation
            document.addEventListener('submit', (e) => {
                const form = e.target;
                const confirmMsg = form.getAttribute('data-confirm');
                if (confirmMsg && !form.dataset.confirmed) {
                    e.preventDefault();
                    showAdminConfirm({
                        title: form.getAttribute('data-confirm-title') || 'Confirm Action',
                        message: confirmMsg,
                        confirmText: form.getAttribute('data-confirm-btn') || 'Yes, Proceed',
                        confirmClass: form.getAttribute('data-confirm-class') || 'btn-danger',
                        onConfirm: () => {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                }
            });

            // Automatically dispatch session toasts
            @if (session('status'))
                showAdminToast(@json(session('status')), 'success');
            @endif
            @if (session('success'))
                showAdminToast(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                showAdminToast(@json(session('error')), 'danger');
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
