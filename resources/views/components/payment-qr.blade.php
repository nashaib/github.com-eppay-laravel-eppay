@php
    $successRedirect = $successUrl ?? '';
    $cancelRedirect = $cancelUrl ?? '';
@endphp

<div class="eppay-payment-qr max-w-md mx-auto p-6 bg-white rounded-lg shadow-lg">
    <!-- Payment Status Banner -->
    <div id="payment-completed-banner" style="display: none;" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-semibold">Payment Completed!</span>
        </div>
    </div>

    <!-- Error Message -->
    <div id="payment-error" style="display: none;" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <span id="payment-error-text"></span>
    </div>

    <!-- Payment Information -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan to Pay</h2>
        <p class="text-gray-600">Scan this QR code with your EpPay mobile app</p>
    </div>

    <!-- QR Code -->
    <div class="flex justify-center mb-6" id="qr-code-container">
        <div class="relative">
            <img
                src="{{ $qrCodeUrl }}"
                alt="Payment QR Code"
                class="w-64 h-64 border-4 border-gray-200 rounded-lg"
            />

            <!-- Loading Overlay -->
            <div id="loading-overlay" style="display: none;" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
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
            id="check-payment-btn"
            onclick="EpPayWidget.checkStatus()"
            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold rounded-lg transition duration-200"
        >
            <span id="btn-text-check">Check Payment Status</span>
            <span id="btn-text-checking" style="display: none;">Checking...</span>
        </button>

        @if($cancelRedirect)
        <a
            href="{{ $cancelRedirect }}"
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
    <div id="auto-check-indicator" class="mt-6 text-center" style="display: {{ $autoRefresh ? 'block' : 'none' }};">
        <div class="flex items-center justify-center text-sm text-gray-500">
            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
            <span>Auto-checking payment status...</span>
        </div>
    </div>
</div>

<script>
(function() {
    window.EpPayWidget = {
        paymentId: '{{ $paymentId }}',
        successUrl: '{{ $successRedirect }}',
        autoRefresh: {{ $autoRefresh ? 'true' : 'false' }},
        pollingInterval: {{ $pollingInterval }},
        timer: null,
        checking: false,

        checkStatus: function() {
            if (this.checking) return;

            this.checking = true;
            this.updateUI('checking');

            fetch('/eppay/verify/' + this.paymentId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === true) {
                        this.onPaymentComplete();
                    } else {
                        this.updateUI('pending');
                    }
                })
                .catch(error => {
                    this.showError('Failed to check payment status');
                    console.error('Payment verification error:', error);
                    this.updateUI('error');
                })
                .finally(() => {
                    this.checking = false;
                });
        },

        onPaymentComplete: function() {
            this.stopPolling();
            this.updateUI('completed');

            if (this.successUrl) {
                setTimeout(() => {
                    window.location.href = this.successUrl;
                }, 1000);
            } else {
                // Dispatch custom event
                const event = new CustomEvent('payment-completed', {
                    detail: { paymentId: this.paymentId }
                });
                document.dispatchEvent(event);
            }
        },

        updateUI: function(state) {
            const btnCheck = document.getElementById('btn-text-check');
            const btnChecking = document.getElementById('btn-text-checking');
            const loadingOverlay = document.getElementById('loading-overlay');
            const completedBanner = document.getElementById('payment-completed-banner');
            const qrContainer = document.getElementById('qr-code-container');

            if (state === 'checking') {
                btnCheck.style.display = 'none';
                btnChecking.style.display = 'inline';
                loadingOverlay.style.display = 'flex';
            } else if (state === 'pending') {
                btnCheck.style.display = 'inline';
                btnChecking.style.display = 'none';
                loadingOverlay.style.display = 'none';
            } else if (state === 'completed') {
                completedBanner.style.display = 'block';
                qrContainer.style.display = 'none';
                document.getElementById('auto-check-indicator').style.display = 'none';
            } else if (state === 'error') {
                btnCheck.style.display = 'inline';
                btnChecking.style.display = 'none';
                loadingOverlay.style.display = 'none';
            }
        },

        showError: function(message) {
            const errorDiv = document.getElementById('payment-error');
            const errorText = document.getElementById('payment-error-text');
            errorText.textContent = message;
            errorDiv.style.display = 'block';

            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        },

        startPolling: function() {
            if (!this.autoRefresh) return;

            this.timer = setInterval(() => {
                this.checkStatus();
            }, this.pollingInterval);
        },

        stopPolling: function() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        init: function() {
            this.startPolling();
        }
    };

    // Auto-start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            EpPayWidget.init();
        });
    } else {
        EpPayWidget.init();
    }
})();
</script>
