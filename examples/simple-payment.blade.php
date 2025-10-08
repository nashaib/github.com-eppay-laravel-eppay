{{-- resources/views/simple-payment.blade.php --}}
{{-- Simple one-page payment example --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EpPay Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <x-eppay-payment-qr
            :payment-id="$paymentId"
            :payment-data="$paymentData"
            :auto-refresh="true"
            success-url="/payment-success"
        />
    </div>
</body>
</html>
