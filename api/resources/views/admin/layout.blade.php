<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Little Divinity Admin')</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f8f3ea;
            --panel: #fffdf8;
            --border: rgba(58, 34, 16, 0.12);
            --text: #24170f;
            --muted: #6b5a4d;
            --accent: #d6a443;
            --accent-dark: #b8872d;
            --danger: #b8342d;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #f8f3ea 0%, #f5efe4 100%);
            color: var(--text);
        }
        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .auth-card {
            width: min(100%, 460px);
            background: rgba(255, 253, 248, 0.96);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(56, 35, 16, 0.12);
            padding: 32px;
        }
        .brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--accent-dark);
            margin-bottom: 10px;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 34px;
            line-height: 1.05;
        }
        .lead {
            color: var(--muted);
            margin: 0 0 24px;
            line-height: 1.6;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .field { margin-bottom: 18px; }
        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 15px;
        }
        input:focus {
            outline: none;
            border-color: rgba(214, 164, 67, 0.65);
            box-shadow: 0 0 0 4px rgba(214, 164, 67, 0.14);
        }
        .button {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 15px 18px;
            background: var(--accent);
            color: #2f1d0d;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
        }
        .button:hover { background: #c89331; }
        .button.secondary {
            background: #fff;
            border: 1px solid var(--border);
        }
        .helper-links {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 18px;
            font-size: 14px;
        }
        .helper-links a, .simple-link {
            color: var(--text);
            text-decoration: none;
        }
        .helper-links a:hover, .simple-link:hover { color: var(--accent-dark); }
        .message, .errors {
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .message {
            background: rgba(214, 164, 67, 0.14);
            color: #7b5613;
        }
        .errors {
            background: rgba(184, 52, 45, 0.1);
            color: var(--danger);
        }
        .rule-list {
            margin: 12px 0 0;
            padding-left: 18px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }
        .dashboard-shell {
            min-height: 100vh;
            padding: 24px;
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 24px;
        }
        .sidebar {
            position: sticky;
            top: 24px;
            background: rgba(255, 253, 248, 0.98);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 24px;
            height: fit-content;
            box-shadow: 0 24px 60px rgba(56, 35, 16, 0.08);
        }
        .sidebar-nav {
            display: grid;
            gap: 8px;
            margin-top: 18px;
        }
        .sidebar-link {
            padding: 12px 14px;
            border-radius: 16px;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .sidebar-link.active,
        .sidebar-link:hover {
            background: rgba(214, 164, 67, 0.12);
            border-color: rgba(214, 164, 67, 0.24);
            color: #7b5613;
        }
        .admin-main {
            min-width: 0;
        }
        .dashboard-card {
            background: rgba(255, 253, 248, 0.98);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(56, 35, 16, 0.12);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 24px;
        }
        .topbar h2 {
            margin: 0;
            font-size: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }
        .stat {
            border-radius: 20px;
            border: 1px solid var(--border);
            background: #fff;
            padding: 20px;
        }
        .stat small {
            display: block;
            color: var(--muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }
        .stat strong {
            font-size: 28px;
        }
        .page-head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .page-head h2 {
            margin: 8px 0 0;
            font-size: 34px;
            line-height: 1.05;
        }
        .section-grid {
            display: grid;
            gap: 20px;
        }
        .split-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: 1.2fr 1fr;
        }
        .panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 24px;
        }
        .panel h3 {
            margin: 0 0 6px;
            font-size: 24px;
        }
        .panel > p {
            margin: 0 0 20px;
            color: var(--muted);
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
        textarea, select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 15px;
            font-family: inherit;
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
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-row input {
            width: auto;
        }
        .table-wrap {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            vertical-align: top;
            padding: 14px 12px;
            border-bottom: 1px solid rgba(58, 34, 16, 0.08);
        }
        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
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
        .button.small {
            width: auto;
            padding: 11px 16px;
            font-size: 13px;
        }
        .button.danger {
            background: #fff0ef;
            color: var(--danger);
            border: 1px solid rgba(184, 52, 45, 0.18);
        }
        .muted {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(214, 164, 67, 0.14);
            color: #7b5613;
        }
        .preview-list {
            display: grid;
            gap: 10px;
        }
        .preview-list code {
            display: block;
            padding: 10px 12px;
            background: #f8f3ea;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }
        @media (max-width: 720px) {
            .stats { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .auth-card, .dashboard-card { padding: 24px; }
            h1 { font-size: 28px; }
        }
        @media (max-width: 1100px) {
            .dashboard-shell {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: static;
            }
            .stats,
            .split-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
