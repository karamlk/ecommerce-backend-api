<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Order Confirmed ✅</h2>
    <p>Thank you for your order.</p>
    <table style="border-collapse: collapse; width: 100%; max-width: 400px;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Order ID</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{{ $orderId }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Total</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">${{ $total }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Status</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ ucfirst($status) }}</td>
        </tr>
    </table>
    <p style="margin-top: 20px; color: #666;">
        We'll notify you when your order is on its way.
    </p>
</body>
</html>