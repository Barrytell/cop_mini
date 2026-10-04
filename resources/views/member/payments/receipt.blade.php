<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $payment->tx_ref }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1c1917; font-size: 14px; }
        h1 { font-size: 22px; margin-bottom: 0; }
        p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { text-align: left; padding: 8px 0; border-bottom: 1px solid #e7e5e4; vertical-align: top; }
        th { width: 40%; color: #57534e; font-weight: 600; }
    </style>
</head>
<body>
    <p>{{ $siteName }}</p>
    <h1>Payment receipt</h1>
    <p>This receipt confirms a unit purchase. The USD amount and unit price were stored when the payment was created.</p>
    <table>
        <tr><th>Member</th><td>{{ $payment->user->name }}</td></tr>
        <tr><th>Member number</th><td>{{ $payment->user->member_no }}</td></tr>
        <tr><th>Email</th><td>{{ $payment->user->email }}</td></tr>
        <tr><th>Reference</th><td>{{ $payment->tx_ref }}</td></tr>
        <tr><th>Type</th><td>{{ str_replace('_', ' ', $payment->type->value) }}</td></tr>
        <tr><th>USD amount</th><td>${{ $payment->amount_usd }}</td></tr>
        <tr><th>Amount collected</th><td>{{ $payment->amount_paid }} {{ $payment->currency_paid }}</td></tr>
        <tr><th>Unit price</th><td>${{ $payment->unit_price_snapshot }}</td></tr>
        <tr><th>Units credited</th><td>{{ number_format((int) $payment->units_purchased) }}</td></tr>
        <tr><th>Paid at</th><td>{{ optional($payment->paid_at)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</td></tr>
        <tr><th>Status</th><td>successful</td></tr>
    </table>
</body>
</html>
