<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; overflow-x: hidden; font-family: "Segoe UI", sans-serif; background: #f4efe6; color: #1a1814; }
        main { max-width: 36rem; margin: 0 auto; padding: 4rem 1rem; }
        h1 { font-size: 2rem; line-height: 1.2; margin: 0 0 0.75rem; }
        p { line-height: 1.5; }
        a { display: inline-flex; min-height: 44px; align-items: center; margin-top: 1rem; padding: 0 1.25rem; border-radius: 999px; background: #143d33; color: white; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <main>
        <p style="letter-spacing: .08em; text-transform: uppercase; font-size: .75rem;">{{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a href="{{ url('/') }}">Back home</a>
    </main>
</body>
</html>
