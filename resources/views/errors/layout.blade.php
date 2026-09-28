<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'خطا | SilaGallery')</title>
    <style>
        :root {
            color-scheme: light;
            --cream: #f5f1ea;
            --white: #fffdf9;
            --ink: #201f1c;
            --stone: #77736b;
            --accent: #a66f43;
            --border: rgba(32,31,28,.10);
            --danger: #a24e4e;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: Arial, "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(166,111,67,.10), transparent 34%),
                radial-gradient(circle at bottom left, rgba(32,31,28,.06), transparent 34%),
                var(--cream);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card {
            width: min(720px, 100%);
            padding: clamp(28px, 6vw, 64px);
            border: 1px solid var(--border);
            border-radius: 32px;
            background: rgba(255,253,249,.92);
            box-shadow: 0 30px 90px rgba(32,31,28,.10);
            text-align: center;
            backdrop-filter: blur(16px);
        }
        .brand { font-size: 12px; letter-spacing: .22em; font-weight: 700; opacity: .48; text-transform: uppercase; }
        .code { margin-top: 28px; font-size: clamp(64px, 13vw, 120px); line-height: .9; font-weight: 900; letter-spacing: -.06em; color: var(--accent); }
        .danger { color: var(--danger); }
        h1 { margin: 24px 0 0; font-size: clamp(24px, 4vw, 38px); line-height: 1.3; }
        p { margin: 14px auto 0; max-width: 560px; color: var(--stone); line-height: 2; font-size: 14px; }
        .actions { margin-top: 32px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 48px; padding: 0 20px; border-radius: 16px;
            border: 1px solid var(--border); font-size: 13px; font-weight: 700;
        }
        .primary { border-color: var(--ink); background: var(--ink); color: #fff; }
        .secondary { background: #fff; }
        .hint { margin-top: 22px; font-size: 11px; color: rgba(32,31,28,.42); }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>