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
            --bg: #f3f6fb;
            --panel: #ffffff;
            --sidebar: #ffffff;
            --border: #dde5f0;
            --border-strong: #cfd9e7;
            --text: #23324d;
            --text-soft: #67758f;
            --heading: #24324a;
            --primary: #3a6cc4;
            --primary-dark: #2d57a0;
            --success: #4caf50;
            --warning: #f3a41a;
            --purple: #a44dd8;
            --danger: #d9534f;
            --surface: #f8fbff;
            --shadow-soft: 0 18px 40px rgba(31, 56, 88, 0.08);
            --radius-xl: 22px;
            --radius-lg: 16px;
            --radius-md: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        a {
            color: inherit;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(180deg, #eff4fb 0%, #f8fbff 100%);
        }

        .auth-card {
            width: min(100%, 460px);
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(45, 70, 104, 0.14);
            padding: 34px;
        }

        .brand {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 10px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1.05;
            color: var(--heading);
        }

        .lead {
            color: var(--text-soft);
            margin: 0 0 24px;
            line-height: 1.6;
            font-size: 15px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--heading);
        }

        .field {
            margin-bottom: 18px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: rgba(58, 108, 196, 0.5);
            box-shadow: 0 0 0 4px rgba(58, 108, 196, 0.12);
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
            border-radius: 14px;
            padding: 13px 18px;
            background: var(--primary);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(58, 108, 196, 0.18);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            color: #fff;
        }

        .button.secondary {
            background: #fff;
            color: var(--heading);
            border: 1px solid var(--border-strong);
            box-shadow: none;
        }

        .button.secondary:hover {
            background: #f6f9fd;
            color: var(--heading);
        }

        .button.small {
            width: auto;
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 12px;
        }

        .button.danger {
            background: #fff5f4;
            color: var(--danger);
            border: 1px solid rgba(217, 83, 79, 0.2);
            box-shadow: none;
        }

        .button.danger:hover {
            background: #ffeaea;
            color: #c53f39;
        }

        .helper-links {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 18px;
            font-size: 14px;
        }

        .helper-links a,
        .simple-link {
            color: var(--heading);
            text-decoration: none;
        }

        .helper-links a:hover,
        .simple-link:hover {
            color: var(--primary);
        }

        .message,
        .errors {
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .message {
            background: #edf5ff;
            border-color: #cfe1ff;
            color: #365f9d;
        }

        .errors {
            background: #fff1ef;
            border-color: #ffd7d2;
            color: var(--danger);
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
            background: var(--bg);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 28px 22px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 0;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 8px 18px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo-mark {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary), #6b8df0);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .sidebar-logo-text strong {
            display: block;
            font-size: 28px;
            line-height: 1;
            color: var(--heading);
        }

        .sidebar-logo-text span {
            display: block;
            margin-top: 4px;
            color: var(--text-soft);
            font-size: 13px;
        }

        .sidebar-group {
            display: grid;
            gap: 10px;
        }

        .sidebar-label {
            padding: 0 10px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #8a97ad;
        }

        .sidebar-nav {
            display: grid;
            gap: 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            color: var(--text-soft);
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all 0.18s ease;
        }

        .sidebar-link i {
            font-size: 1rem;
            width: 18px;
            text-align: center;
        }

        .sidebar-link.active,
        .sidebar-link:hover {
            background: #edf4ff;
            border-color: #d7e5ff;
            color: var(--primary);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .admin-main {
            min-width: 0;
            padding: 28px;
        }

        .dashboard-card {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
        }

        .topbar,
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .topbar h2,
        .page-head h2 {
            margin: 6px 0 0;
            font-size: 2rem;
            line-height: 1.05;
            color: var(--heading);
            font-weight: 800;
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .stat {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background: var(--panel);
            padding: 22px;
            box-shadow: var(--shadow-soft);
        }

        .stat::after {
            content: "";
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 16px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--primary), rgba(58, 108, 196, 0.12));
        }

        .stat small {
            display: block;
            color: #7d8aa4;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 700;
        }

        .stat strong {
            display: block;
            font-size: 2rem;
            line-height: 1.05;
            color: var(--heading);
        }

        .stat p {
            margin: 8px 0 0;
            color: var(--text-soft);
            font-size: 14px;
        }

        .section-grid {
            display: grid;
            gap: 20px;
        }

        .split-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: 1.15fr 1fr;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 22px;
            box-shadow: var(--shadow-soft);
        }

        .panel h3 {
            margin: 0 0 6px;
            font-size: 1.6rem;
            color: var(--heading);
            font-weight: 800;
        }

        .panel > p {
            margin: 0 0 20px;
            color: var(--text-soft);
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .form-grid.one {
            grid-template-columns: 1fr;
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

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            vertical-align: top;
            padding: 15px 14px;
            border-bottom: 1px solid #e9eef5;
            font-size: 14px;
        }

        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #7c89a1;
            font-weight: 800;
            background: #f8fbff;
        }

        .table-input {
            min-width: 110px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 14px;
        }

        .admin-product-meta {
            display: grid;
            gap: 6px;
            min-width: 200px;
        }

        .admin-product-meta strong {
            font-size: 15px;
        }

        .admin-product-meta span {
            font-size: 12px;
            color: var(--text-soft);
            overflow-wrap: anywhere;
        }

        .admin-product-flags {
            display: grid;
            gap: 10px;
            min-width: 110px;
        }

        .admin-row-actions {
            min-width: 160px;
        }

        .media-slot-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .media-slot-card {
            display: grid;
            gap: 10px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #f8fbff;
        }

        .media-slot-card strong {
            font-size: 13px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #5f6f8d;
        }

        .admin-upload-preview {
            width: 100%;
            max-width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .admin-upload-preview--small {
            max-width: 72px;
            height: 72px;
        }

        .inline-form {
            display: grid;
            gap: 10px;
        }

        .button-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .muted {
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.6;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #edf4ff;
            color: var(--primary);
        }

        .preview-list {
            display: grid;
            gap: 10px;
        }

        .preview-list code {
            display: block;
            padding: 10px 12px;
            background: #f8fbff;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .dashboard-table-card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .dashboard-table-head {
            padding: 22px 22px 14px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }

        .dashboard-table-head h3 {
            margin: 0;
            font-size: 1.6rem;
            color: var(--heading);
            font-weight: 800;
        }

        .dashboard-empty {
            text-align: center;
            color: var(--text-soft);
            padding: 32px 20px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .metric-card {
            padding: 18px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: var(--shadow-soft);
            display: grid;
            gap: 8px;
        }

        .metric-card small {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #7c89a1;
        }

        .metric-card strong {
            font-size: 2rem;
            line-height: 1;
            color: var(--heading);
        }

        .metric-card span {
            color: var(--text-soft);
            font-size: 13px;
        }

        .metric-card.warning strong { color: var(--warning); }
        .metric-card.danger strong { color: var(--danger); }

        .admin-split-layout {
            grid-template-columns: minmax(0, 1.5fr) minmax(360px, 0.9fr);
            align-items: start;
        }

        .admin-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .admin-toolbar h3 {
            margin: 0 0 6px;
        }

        .admin-toolbar-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .admin-toolbar-filters input,
        .admin-toolbar-filters select {
            min-width: 180px;
            margin: 0;
        }

        .admin-product-table-wrap {
            border: 1px solid var(--border);
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
        }

        .admin-data-table tbody tr:hover {
            background: #f9fbff;
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
            background: #f3f6fb;
            display: grid;
            place-items: center;
            color: var(--text-soft);
        }

        .admin-product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-status-stack {
            display: grid;
            gap: 10px;
            min-width: 120px;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .admin-badge.primary { background: #edf4ff; color: var(--primary); }
        .admin-badge.success { background: #eef9f0; color: #2f8f48; }
        .admin-badge.warning { background: #fff7e8; color: #b67a10; }
        .admin-badge.danger { background: #fff1f0; color: #c53f39; }
        .admin-badge.muted { background: #f1f4f8; color: #7c89a1; }

        .inventory-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            background: #eef9f0;
            color: #2f8f48;
        }

        .inventory-count.warning { background: #fff7e8; color: #b67a10; }
        .inventory-count.danger { background: #fff1f0; color: #c53f39; }

        .inventory-inline-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inventory-inline-form .table-input {
            max-width: 100px;
        }

        .stack-list {
            display: grid;
            gap: 16px;
        }

        .stack-card {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #f8fbff;
        }

        @media (max-width: 1200px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .metrics-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 992px) {
            .dashboard-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                min-height: auto;
                position: static;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .split-grid,
            .form-grid,
            .media-slot-grid,
            .admin-split-layout,
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .admin-main {
                padding: 18px;
            }

            .topbar,
            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-toolbar,
            .inventory-inline-form {
                flex-direction: column;
                align-items: stretch;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .auth-card {
                padding: 26px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
