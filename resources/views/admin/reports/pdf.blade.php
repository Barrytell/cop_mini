<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report</title>
    <style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#222} h1{font-size:20px} table{width:100%;border-collapse:collapse;margin-top:16px} td,th{border:1px solid #ddd;padding:8px;text-align:left}</style>
</head>
<body>
    <h1>{{ $siteName }} report</h1>
    <p>{{ $from }} to {{ $to }}</p>
    <table>
        <thead><tr><th>Metric</th><th>Value</th></tr></thead>
        <tbody>
            @foreach ($summary as $key => $value)
                <tr><td>{{ str_replace('_', ' ', $key) }}</td><td>{{ $value }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>