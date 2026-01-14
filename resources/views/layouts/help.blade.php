<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Hilfe' }}</title>

    <!-- MDI Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            background: #fafafa;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .main-container {
            flex: 1;
            display: flex;
            min-height: 0;
        }

        .sidebar {
            background: #ffffff;
            border-right: 1px solid #ddd;
            padding: 12px;
            overflow-y: auto;
			width: 200px;
			flex: 0 0 200px;
            min-width: 180px;
            max-width: 500px;
        }

        .content {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: #fff;
        }

        .toc-group {
            margin-bottom: 14px;
        }

        .toc-title {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .toc-subtitle {
            margin-top: 4px;
            margin-bottom: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #666;
        }

        .toc-item {
            cursor: pointer;
            padding: 3px 4px;
            border-radius: 3px;
        }

        .toc-item:hover {
            background: #f4f4f4;
        }

        .toc-item-active {
            background: #d6d6d6;
            font-weight: bold;
        }

        h1, h2, h3, p {
            margin: 6px 0;
        }

        /* ====================================
           CONTENT SPACING + CUSTOM HELP TAGS
           ==================================== */

        .content h1 {
            margin-top: 0;
            margin-bottom: 16px;
        }

        .content h2 {
            margin-top: 18px;
            margin-bottom: 10px;
        }

        .content h3 {
            margin-top: 14px;
            margin-bottom: 8px;
        }

        note, info, warning, admin-note {
            display: block;
            padding: 10px 12px;
            border-radius: 4px;
            font-size: 11px;
            margin: 12px 0;
            line-height: 1.4;
        }

        note {
            background: #f7f7f7;
            border-left: 3px solid #999;
        }

        info {
            background: #e7f3ff;
            border-left: 3px solid #4091ff;
        }

        warning {
            background: #fff3cd;
            border-left: 3px solid #d29b00;
        }

		admin-note {
			display: block;
			margin: 10px 0;
			padding: 10px 10px 10px 10px;
			font-size: 12px;
			line-height: 1.35;
			background: #fafafa;
			border: 1px solid #e1e1e1;
			border-left: 3px solid #d0d0d0;
			border-radius: 3px;
		}

		/* Auto-label via ::before */
		admin-note::before {
			content: "ADMIN";
			display: block;
			font-size: 10px;
			font-weight: bold;
			opacity: 0.6;
			text-transform: uppercase;
			margin-bottom: 4px;
			color: #444;
			letter-spacing: 0.3px;
		}

        .footer {
            background: #f1f3f5;
            border-top: 1px solid #d0d5d8;
            padding: 8px 12px;
            text-align: center;
            font-size: 12px;
            color: #333;
        }

        .footer a {
            color: #1D4E8F;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>

    @livewireStyles
</head>
<body>

<div class="main-container">
    {{ $slot }}
</div>

<div class="footer">
    Keine Lösung gefunden? Wir sind erreichbar unter Tel. <a href="tel:456">+41 81 511 75 75</a> und <a href="mailto:info@sitak.ch">info@sitak.ch</a>.
</div>

</body>
</html>
