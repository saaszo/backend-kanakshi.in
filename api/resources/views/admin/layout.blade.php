<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Little Divinity Admin')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --bg-soft: #f8fbff;
            --panel: rgba(255, 255, 255, 0.94);
            --sidebar: #ffffff;
            --sidebar-soft: #eef4ff;
            --border: rgba(15, 23, 42, 0.08);
            --border-strong: rgba(15, 23, 42, 0.15);
            --text: #475569;
            --text-soft: #64748b;
            --heading: #0f172a;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-glow: rgba(37, 99, 235, 0.15);
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --purple: #7c3aed;
            --shadow-soft: 0 18px 45px rgba(15, 23, 42, 0.08);
            --radius-xl: 22px;
            --radius-lg: 16px;
            --radius-md: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.06), transparent 26%),
                linear-gradient(180deg, var(--bg-soft) 0%, var(--bg) 100%);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        h1, h2, h3 {
            color: var(--heading);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .brand {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .lead,
        .muted {
            color: var(--text-soft);
            line-height: 1.6;
            font-size: 14px;
        }

        .message,
        .errors {
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .message {
            background: rgba(5, 150, 105, 0.08);
            border-color: rgba(5, 150, 105, 0.15);
            color: var(--success);
        }

        .errors {
            background: rgba(220, 38, 38, 0.08);
            border-color: rgba(220, 38, 38, 0.15);
            color: var(--danger);
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .auth-card {
            width: min(100%, 480px);
            padding: 34px;
            border-radius: 28px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: var(--shadow-soft);
        }

        .auth-card h1 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.05;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--heading);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--heading);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        textarea {
            min-height: 112px;
            resize: vertical;
        }

        textarea.code {
            min-height: 220px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 13px;
        }

        .button {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.16);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            color: #fff;
        }

        .button.small {
            width: auto;
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 10px;
            box-shadow: none;
        }

        .button.secondary {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--heading);
        }

        .button.secondary:hover {
            background: #eff6ff;
            border-color: rgba(37, 99, 235, 0.18);
            color: var(--primary-dark);
        }

        .button.danger {
            background: rgba(220, 38, 38, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.15);
            color: var(--danger);
        }

        .button.danger:hover {
            background: var(--danger);
            color: #fff;
        }

        .helper-links {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-soft);
        }

        .helper-links a,
        .simple-link {
            color: var(--heading);
        }

        .helper-links a:hover,
        .simple-link:hover {
            color: var(--primary);
        }

        .rule-list {
            margin: 12px 0 0;
            padding-left: 18px;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.6;
        }

        .dashboard-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
        }

        .admin-mobile-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: var(--shadow-soft);
        }

        .admin-mobile-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .admin-mobile-toggle {
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar {
            min-height: 100vh;
            padding: 24px 20px;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
        }

        .sidebar.offcanvas-lg {
            background: var(--sidebar);
        }

        .sidebar-scroll {
            display: flex;
            flex-direction: column;
            gap: 18px;
            min-height: 100%;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 6px 18px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), #60a5fa);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
            flex: 0 0 42px;
        }

        .sidebar-logo-text strong {
            display: block;
            color: var(--heading);
            font-size: 17px;
            line-height: 1.1;
        }

        .sidebar-logo-text span {
            display: block;
            margin-top: 3px;
            color: var(--text-soft);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .sidebar-status {
            display: grid;
            gap: 4px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: linear-gradient(180deg, var(--sidebar-soft) 0%, #ffffff 100%);
        }

        .sidebar-status-label,
        .sidebar-quick-label,
        .sidebar-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .sidebar-status-label,
        .sidebar-quick-label {
            color: var(--primary);
        }

        .sidebar-status strong {
            color: var(--heading);
            font-size: 14px;
        }

        .sidebar-status span:last-child {
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.5;
        }

        .sidebar-group {
            display: grid;
            gap: 10px;
        }

        .sidebar-label {
            padding: 0 12px;
            color: #94a3b8;
        }

        .sidebar-nav {
            display: grid;
            gap: 4px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            color: var(--text-soft);
            border: 1px solid transparent;
            font-weight: 600;
            position: relative;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            width: 18px;
            text-align: center;
            color: var(--text-soft);
            transition: color 0.2s ease;
        }

        .sidebar-link::before {
            content: "";
            position: absolute;
            left: 0;
            top: 22%;
            height: 56%;
            width: 4px;
            border-radius: 999px;
            background: var(--primary);
            opacity: 0;
        }

        .sidebar-link.active::before {
            opacity: 1;
        }

        .sidebar-link.active,
        .sidebar-link:hover {
            background: rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.14);
            color: var(--heading);
            padding-left: 18px;
        }

        .sidebar-link.active i,
        .sidebar-link:hover i {
            color: var(--primary);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .sidebar-quick {
            display: grid;
            gap: 10px;
            margin-bottom: 14px;
        }

        .sidebar-quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sidebar-quick-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #fff;
            color: var(--heading);
            font-size: 12px;
            font-weight: 700;
        }

        .button-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .admin-main {
            min-width: 0;
            padding: 26px;
        }

        .dashboard-card {
            background: transparent;
            border: none;
            padding: 0;
            box-shadow: none;
        }

        .topbar,
        .page-head,
        .admin-toolbar,
        .dashboard-table-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .topbar,
        .page-head {
            margin-bottom: 24px;
        }

        .topbar h2,
        .page-head h2 {
            margin: 6px 0 0;
            font-size: 30px;
            line-height: 1.05;
        }

        .toolbar-actions,
        .admin-toolbar-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .admin-toolbar {
            margin-bottom: 18px;
        }

        .admin-toolbar h3,
        .panel h3,
        .dashboard-table-head h3 {
            margin: 0 0 6px;
            font-size: 22px;
        }

        .section-grid,
        .stack-list,
        .preview-list {
            display: grid;
            gap: 18px;
        }

        .stats,
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat,
        .metric-card {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            background: var(--panel);
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .stat::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 0;
            height: 3px;
            border-radius: 999px 999px 0 0;
            background: linear-gradient(90deg, var(--primary), #60a5fa);
        }

        .stat small,
        .metric-card small {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-soft);
        }

        .stat strong,
        .metric-card strong {
            display: block;
            font-size: 28px;
            line-height: 1;
            color: var(--heading);
        }

        .stat p,
        .metric-card span {
            margin: 8px 0 0;
            color: var(--text-soft);
            font-size: 13px;
        }

        .panel,
        .dashboard-table-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
        }

        .panel {
            padding: 20px;
        }

        .dashboard-table-card {
            overflow: hidden;
        }

        .dashboard-table-head {
            padding: 20px 20px 14px;
        }

        .split-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 18px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-grid.one,
        .admin-product-page {
            grid-template-columns: 1fr;
        }

        .admin-split-layout {
            grid-template-columns: minmax(0, 1.5fr) minmax(360px, 0.9fr);
            align-items: start;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-row.compact {
            gap: 8px;
            font-size: 13px;
        }

        .checkbox-row input {
            width: auto;
            margin: 0;
        }

        .table-wrap,
        .admin-product-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
        }

        .admin-product-page .admin-data-table {
            min-width: 1160px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            vertical-align: middle;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-soft);
            font-weight: 800;
            background: #f8fafc;
        }

        tbody tr:hover td {
            background: #f8fbff;
        }

        .table-input {
            min-width: 110px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--heading);
        }

        .admin-product-line {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 260px;
        }

        .admin-product-thumb {
            width: 58px;
            height: 58px;
            flex: 0 0 58px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #eef2f7;
            display: grid;
            place-items: center;
            color: var(--text-soft);
        }

        .admin-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-product-meta {
            display: grid;
            gap: 6px;
            min-width: 200px;
        }

        .admin-product-meta strong {
            font-size: 15px;
            color: var(--heading);
        }

        .admin-product-meta span {
            font-size: 12px;
            color: var(--text-soft);
            overflow-wrap: anywhere;
        }

        .admin-status-stack,
        .admin-product-flags {
            display: grid;
            gap: 8px;
        }

        .admin-row-actions {
            min-width: 180px;
        }

        .media-slot-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .media-slot-card,
        .stack-card {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #f8fafc;
        }

        .media-slot-card strong {
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-soft);
        }

        .admin-upload-preview {
            width: 100%;
            max-width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #eef2f7;
        }

        .admin-upload-preview--small {
            max-width: 72px;
            height: 72px;
        }

        .preview-list code {
            display: block;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 12px;
            overflow-wrap: anywhere;
            color: var(--heading);
        }

        .pill,
        .admin-badge,
        .inventory-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .pill,
        .admin-badge.primary {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            border: 1px solid rgba(37, 99, 235, 0.14);
        }

        .admin-badge.success,
        .inventory-count {
            background: rgba(5, 150, 105, 0.1);
            color: var(--success);
            border: 1px solid rgba(5, 150, 105, 0.14);
        }

        .admin-badge.warning,
        .inventory-count.warning {
            background: rgba(217, 119, 6, 0.1);
            color: var(--warning);
            border: 1px solid rgba(217, 119, 6, 0.14);
        }

        .admin-badge.danger,
        .inventory-count.danger {
            background: rgba(220, 38, 38, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.14);
        }

        .admin-badge.muted {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.14);
        }

        .inventory-inline-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inventory-inline-form .table-input {
            max-width: 100px;
        }

        .dashboard-empty {
            text-align: center;
            color: var(--text-soft);
            padding: 36px 20px;
        }

        @media (max-width: 1200px) {
            .stats,
            .metrics-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 992px) {
            .dashboard-shell {
                grid-template-columns: 1fr;
            }

            .admin-mobile-bar {
                display: flex;
            }

            .sidebar {
                min-height: auto;
                padding: 0;
                position: static;
                border-right: none;
            }

            .sidebar.offcanvas-lg {
                width: 310px;
                min-height: 100vh;
                border-right: 1px solid var(--border);
                box-shadow: 0 22px 50px rgba(15, 23, 42, 0.12);
            }

            .admin-main {
                padding-top: 18px;
            }

            .split-grid,
            .form-grid,
            .media-slot-grid,
            .admin-split-layout,
            .metrics-grid,
            .stats {
                grid-template-columns: 1fr;
            }

            .admin-product-page .form-grid,
            .admin-product-page .media-slot-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .auth-card {
                padding: 28px;
            }

            .auth-card h1,
            h1 {
                font-size: 28px;
            }

            .admin-main {
                padding: 16px;
            }

            .topbar,
            .page-head,
            .admin-toolbar,
            .dashboard-table-head,
            .inventory-inline-form {
                flex-direction: column;
                align-items: stretch;
            }

            .topbar h2,
            .page-head h2 {
                font-size: 24px;
            }

            .metric-card strong,
            .stat strong {
                font-size: 24px;
            }

            .admin-product-page .form-grid,
            .admin-product-page .media-slot-grid {
                grid-template-columns: 1fr;
            }

            .admin-row-actions {
                min-width: 220px;
            }

            .helper-links {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
