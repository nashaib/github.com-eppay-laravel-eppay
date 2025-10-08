{{-- resources/views/payments/show.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Complete Your Payment
            </h1>
            <p class="text-gray-600">
                Scan the QR code with your EpPay mobile app to complete the payment
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Payment Information Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    Payment Details
                </h2>

                <div class="space-y-4">
                    <div class="border-b pb-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Amount</span>
                            <span class="text-2xl font-bold text-gray-900">
                                {{ $paymentData['amount'] ?? 'N/A' }}
                                {{ $paymentData['currency'] ?? '' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Network</span>
                            <span class="font-medium">{{ $paymentData['network'] ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Currency</span>
                            <span class="font-medium">{{ $paymentData['currency'] ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Status</span>
                            <span class="font-medium">
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">
                                    Pending
                                </span>
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment ID</span>
                            <span class="font-mono text-xs">{{ Str::limit($paymentId, 20) }}</span>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-blue-900 mb-2">How to Pay</h3>
                        <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                            <li>Open your EpPay mobile app</li>
                            <li>Scan the QR code on the right</li>
                            <li>Confirm the payment in the app</li>
                            <li>Wait for confirmation (automatic)</li>
                        </ol>
                    </div>

                    <!-- Warning -->
                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <strong>Important:</strong> Do not close this page until the payment is confirmed.
                            The page will automatically detect when payment is received.
                        </p>
                    </div>
                </div>
            </div>

            <!-- QR Code Component -->
            <div>
                <x-eppay-payment-qr
                    :payment-id="$paymentId"
                    :payment-data="$paymentData"
                    :auto-refresh="true"
                    :success-url="route('payment.success', $paymentId)"
                    :cancel-url="route('payment.cancel', $paymentId)"
                />
            </div>
        </div>

        <!-- Footer Help -->
        <div class="mt-8 text-center">
            <p class="text-gray-600 text-sm">
                Need help?
                <a href="mailto:support@eppay.io" class="text-blue-600 hover:underline">
                    Contact Support
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Listen for payment completion event
    document.addEventListener('payment-completed', function(event) {
        console.log('Payment completed!', event.detail);

        // You can add custom logic here
        // For example, show a success message, confetti animation, etc.
    });
</script>
@endpush
