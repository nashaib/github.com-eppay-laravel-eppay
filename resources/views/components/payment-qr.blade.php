<div
    x-data="{
        paymentId: '{{ $paymentId }}',
        successUrl: {{ $successUrl ? "'" . $successUrl . "'" : 'null' }},
        status: 'pending',
        checking: false,
        error: null,
        pollingInterval: {{ $pollingInterval }},
        autoRefresh: {{ $autoRefresh ? 'true' : 'false' }},
        timer: null,

        async checkPaymentStatus() {
            if (this.checking || this.status === 'completed') return;

            this.checking = true;
            this.error = null;

            try {
                const response = await fetch('/eppay/verify/' + this.paymentId);
                const data = await response.json();

                // EpPay API returns {"status": true} when payment is completed
                if (data.status === true) {
                    this.status = 'completed';
                    this.stopPolling();

                    if (this.successUrl) {
                        window.location.href = this.successUrl;
                    } else {
                        this.$dispatch('payment-completed', { paymentId: this.paymentId, data: data });
                    }
                } else if (data.status === false) {
                    // Payment not yet completed, keep checking
                    this.status = 'pending';
                }
            } catch (err) {
                this.error = 'Failed to check payment status';
                console.error('Payment verification error:', err);
            } finally {
                this.checking = false;
            }
        },

        startPolling() {
            if (!this.autoRefresh) return;

            this.timer = setInterval(() => {
                this.checkPaymentStatus();
            }, this.pollingInterval);
        },

        stopPolling() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        init() {
            this.startPolling();

            // Cleanup on component destroy
            this.$watch('status', value => {
                if (value === 'completed' || value === 'failed' || value === 'expired') {
                    this.stopPolling();
                }
            });
        }
    }"
    x-init="init()"
    class="eppay-payment-qr max-w-md mx-auto p-6 bg-white rounded-lg shadow-lg"
>
    <!-- Payment Status Banner -->
    <div x-show="status === 'completed'" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-semibold">Payment Completed!</span>
        </div>
    </div>

    <div x-show="status === 'failed'" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span class="font-semibold">Payment Failed</span>
        </div>
    </div>

    <div x-show="status === 'expired'" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Payment Expired</span>
        </div>
    </div>

    <!-- Error Message -->
    <div x-show="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <span x-text="error"></span>
    </div>

    <!-- Payment Information -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan to Pay</h2>
        <p class="text-gray-600">Scan this QR code with your EpPay mobile app</p>
    </div>

    <!-- QR Code -->
    <div class="flex justify-center mb-6" x-show="status === 'pending'">
        <div class="relative">
            <img
                src="{{ $qrCodeUrl }}"
                alt="Payment QR Code"
                class="w-64 h-64 border-4 border-gray-200 rounded-lg"
            />

            <!-- Loading Overlay -->
            <div
                x-show="checking"
                class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"
            >
                <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Payment Details -->
    @if(!empty($paymentData))
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="space-y-2 text-sm">
            @if(isset($paymentData['amount']))
            <div class="flex justify-between">
                <span class="text-gray-600">Amount:</span>
                <span class="font-semibold">{{ $paymentData['amount'] ?? 'N/A' }}</span>
            </div>
            @endif

            @if(isset($paymentData['currency']))
            <div class="flex justify-between">
                <span class="text-gray-600">Currency:</span>
                <span class="font-semibold">{{ $paymentData['currency'] ?? 'N/A' }}</span>
            </div>
            @endif

            @if(isset($paymentData['network']))
            <div class="flex justify-between">
                <span class="text-gray-600">Network:</span>
                <span class="font-semibold">{{ $paymentData['network'] ?? 'N/A' }}</span>
            </div>
            @endif

            <div class="flex justify-between">
                <span class="text-gray-600">Payment ID:</span>
                <span class="font-mono text-xs">{{ Str::limit($paymentId, 20) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="space-y-3">
        <button
            @click="checkPaymentStatus()"
            :disabled="checking || status === 'completed'"
            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition duration-200"
        >
            <span x-show="!checking">Check Payment Status</span>
            <span x-show="checking">Checking...</span>
        </button>

        @if($cancelUrl)
        <a
            href="{{ $cancelUrl }}"
            class="block w-full py-3 px-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg text-center transition duration-200"
        >
            Cancel Payment
        </a>
        @endif

        <a
            href="{{ $paymentUrl }}"
            target="_blank"
            class="block w-full py-3 px-4 border-2 border-blue-600 text-blue-600 hover:bg-blue-50 font-semibold rounded-lg text-center transition duration-200"
        >
            Open Payment Page
        </a>
    </div>

    <!-- Status Indicator -->
    <div class="mt-6 text-center" x-show="status === 'pending' && autoRefresh">
        <div class="flex items-center justify-center text-sm text-gray-500">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
            <span>Auto-checking payment status...</span>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endonce
