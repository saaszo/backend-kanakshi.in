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
            color-scheme: dark;
            --bg: #070a13;
            --panel: rgba(17, 25, 40, 0.65);
            --sidebar: #0b0f19;
            --border: rgba(255, 255, 255, 0.08);
            --border-strong: rgba(255, 255, 255, 0.15);
            --text: #e2e8f0;
            --text-soft: #94a3b8;
            --heading: #ffffff;
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --success: #10b981;
            --warning: #f59e0b;
            --purple: #8b5cf6;
            --danger: #ef4444;
            --surface: #111827;
            --shadow-soft: 0 20px 45px rgba(0, 0, 0, 0.35);
            --radius-xl: 24px;
            --radius-lg: 16px;
            --radius-md: 12px;
        }

        * { box-sizing: border-box; }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 99px;
            border: 2px solid var(--bg);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.4);
        }

        body {
            margin: 0;
            font-family: "Inter", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: color 0.18s ease;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.12) 0%, transparent 40%),
                        var(--bg);
            position: relative;
            overflow: hidden;
        }

        .auth-shell::before,
        .auth-shell::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: var(--primary);
            filter: blur(130px);
            opacity: 0.14;
            z-index: 0;
            animation: float 22s infinite alternate ease-in-out;
        }

        .auth-shell::before {
            top: -100px;
            left: -100px;
        }

        .auth-shell::after {
            bottom: -100px;
            right: -100px;
            animation-delay: -11s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(70px, 50px) scale(1.25); }
        }

        .auth-card {
            position: relative;
            z-index: 1;
            width: min(100%, 460px);
            background: rgba(17, 25, 40, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: var(--shadow-soft), 0 0 40px rgba(99, 102, 241, 0.04);
            padding: 38px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .auth-card:hover {
            border-color: rgba(99, 102, 241, 0.25);
            box-shadow: var(--shadow-soft), 0 0 50px rgba(99, 102, 241, 0.08);
        }

        .brand {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #818cf8;
            margin-bottom: 12px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 32px;
            font-weight: 800;
            line-height: 1.1;
            color: var(--heading);
            letter-spacing: -0.02em;
        }

        .lead {
            color: var(--text-soft);
            margin: 0 0 26px;
            line-height: 1.6;
            font-size: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--heading);
            letter-spacing: 0.01em;
        }

        .field {
            margin-bottom: 20px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 13px 15px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            background: rgba(15, 23, 42, 0.6);
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
            box-shadow: 0 10px 24px rgba(99, 102, 241, 0.2);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, background 0.2s ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(99, 102, 241, 0.3);
            color: #fff;
        }

        .button:active {
            transform: translateY(0);
        }

        .button.secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: none;
        }

        .button.secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .button.small {
            width: auto;
            padding: 10px 14px;
            font-size: 13px;
            border-radius: 12px;
        }

        .button.danger {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.2);
            box-shadow: none;
        }

        .button.danger:hover {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 8px 20px rgba(244, 63, 94, 0.25);
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
            color: var(--text);
            text-decoration: none;
        }

        .helper-links a:hover,
        .simple-link:hover {
            color: var(--primary);
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
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .errors {
            background: rgba(244, 63, 94, 0.1);
            border-color: rgba(244, 63, 94, 0.2);
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
            grid-template-columns: 280px minmax(0, 1fr);
            background: var(--bg);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 28px 20px;
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
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .sidebar-logo-text strong {
            display: block;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            color: var(--heading);
            letter-spacing: -0.02em;
        }

        .sidebar-logo-text span {
            display: block;
            margin-top: 2px;
            color: var(--text-soft);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .sidebar-group {
            display: grid;
            gap: 10px;
        }

        .sidebar-label {
            padding: 0 12px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.25);
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
            text-decoration: none;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-link i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
            color: var(--text-soft);
            transition: color 0.2s ease;
        }

        .sidebar-link::before {
            content: "";
            position: absolute;
            left: 0;
            top: 25%;
            height: 50%;
            width: 4px;
            border-radius: 99px;
            background: var(--primary);
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .sidebar-link.active::before {
            opacity: 1;
        }

        .sidebar-link.active,
        .sidebar-link:hover {
            background: rgba(99, 102, 241, 0.08);
            border-color: rgba(99, 102, 241, 0.15);
            color: #ffffff;
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

        .admin-main {
            min-width: 0;
            padding: 34px;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03) 0%, transparent 35%);
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
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .topbar h2,
        .page-head h2 {
            margin: 6px 0 0;
            font-size: 34px;
            line-height: 1.05;
            color: var(--heading);
            font-weight: 800;
            letter-spacing: -0.03em;
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
            gap: 20px;
            margin-top: 24px;
        }

        .stat {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            background: var(--panel);
            backdrop-filter: blur(16px);
            padding: 24px;
            box-shadow: var(--shadow-soft);
            transition: border-color 0.25s ease, transform 0.25s ease;
        }

        .stat:hover {
            border-color: rgba(99, 102, 241, 0.25);
            transform: translateY(-2px);
        }

        .stat::after {
            content: "";
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 0;
            height: 3px;
            border-radius: 999px 999px 0 0;
            background: linear-gradient(90deg, var(--primary), var(--purple));
            opacity: 0.8;
        }

        .stat small {
            display: block;
            color: var(--text-soft);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 700;
            font-size: 11px;
        }

        .stat strong {
            display: block;
            font-size: 34px;
            font-weight: 800;
            line-height: 1.05;
            color: var(--heading);
        }

        .stat p {
            margin: 8px 0 0;
            color: var(--text-soft);
            font-size: 13px;
            line-height: 1.5;
        }

        .section-grid {
            display: grid;
            gap: 20px;
        }

        .split-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: 1.25fr 1fr;
        }

        .panel {
            background: var(--panel);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-soft);
            transition: border-color 0.3s ease;
        }

        .panel:hover {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .panel h3 {
            margin: 0 0 6px;
            font-size: 22px;
            color: var(--heading);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .panel > p {
            margin: 0 0 22px;
            color: var(--text-soft);
            line-height: 1.6;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            gap: 18px;
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
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(8px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            vertical-align: middle;
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-soft);
            font-weight: 800;
            background: rgba(15, 23, 42, 0.5);
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.015);
        }

        .table-input {
            min-width: 110px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.4);
            color: var(--text);
            font-size: 14px;
        }

        .table-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.6);
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

        .admin-row-actions .button {
            min-width: 88px;
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
            background: rgba(15, 23, 42, 0.2);
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
            background: rgba(15, 23, 42, 0.4);
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
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.25);
        }

        .preview-list {
            display: grid;
            gap: 10px;
        }

        .preview-list code {
            display: block;
            padding: 10px 12px;
            background: rgba(15, 23, 42, 0.4);
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .dashboard-table-card {
            background: var(--panel);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .dashboard-table-head {
            padding: 24px 24px 16px;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }

        .dashboard-table-head h3 {
            margin: 0;
            font-size: 22px;
            color: var(--heading);
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .dashboard-empty {
            text-align: center;
            color: var(--text-soft);
            padding: 36px 20px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .metric-card {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
            box-shadow: var(--shadow-soft);
            display: grid;
            gap: 8px;
            transition: border-color 0.25s ease, transform 0.25s ease;
        }

        .metric-card:hover {
            border-color: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .metric-card small {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-soft);
        }

        .metric-card strong {
            font-size: 34px;
            font-weight: 800;
            line-height: 1;
            color: var(--heading);
            letter-spacing: -0.02em;
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

        .admin-product-page {
            grid-template-columns: 1fr;
        }

        .admin-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .admin-toolbar h3 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 800;
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
            background: rgba(15, 23, 42, 0.2);
            backdrop-filter: blur(8px);
        }

        .admin-product-page .admin-product-table-wrap {
            overflow-x: auto;
        }

        .admin-product-page .admin-data-table {
            min-width: 1160px;
        }

        .admin-data-table tbody tr:hover td {
            background: rgba(255, 255, 255, 0.02);
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
            background: rgba(15, 23, 42, 0.4);
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

        .admin-product-page .panel {
            overflow: hidden;
        }

        .admin-product-page .form-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .admin-product-page .media-slot-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .admin-badge {
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

        .admin-badge.primary { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.25); }
        .admin-badge.success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }
        .admin-badge.warning { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.25); }
        .admin-badge.danger { background: rgba(244, 63, 94, 0.15); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.25); }
        .admin-badge.muted { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25); }

        .inventory-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .inventory-count.warning { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.2); }
        .inventory-count.danger { background: rgba(244, 63, 94, 0.15); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.2); }

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
            background: rgba(15, 23, 42, 0.2);
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

            .admin-product-page .form-grid,
            .admin-product-page .media-slot-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .admin-main {
                padding: 20px;
            }

            .topbar,
            .page-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .admin-toolbar,
            .inventory-inline-form {
                flex-direction: column;
                align-items: stretch;
            }

            .admin-product-page .form-grid,
            .admin-product-page .media-slot-grid {
                grid-template-columns: 1fr;
            }

            .admin-row-actions {
                min-width: 220px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .auth-card {
                padding: 28px;
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
