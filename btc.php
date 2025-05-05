<?php
if (!defined("WHMCS")) {
    define("WHMCS", true); // Temporary for testing
}
require_once dirname(__FILE__) . '/../../../init.php';

use WHMCS\Database\Capsule;

if (!function_exists('getGatewayVariables')) {
    require_once dirname(__FILE__) . '/../../../init.php';
    require_once dirname(__FILE__) . '/../../../includes/gatewayfunctions.php';
    require_once dirname(__FILE__) . '/../../../includes/invoicefunctions.php';
}

// Add a server-side endpoint to check transaction status
if (isset($_GET['action']) && $_GET['action'] === 'checkTransactionStatus') {
    $walletAddress = $_GET['walletAddress'];
    $expectedAmount = $_GET['expectedAmount'];

    $orderId = $_GET['orderId']; // Optional: Pass order ID if needed

    $isConfirmed = verifyBTCTransactions($orderId, $expectedAmount, $walletAddress);

    header('Content-Type: application/json');
    echo json_encode(['confirmed' => $isConfirmed]);
    exit;
}

function btc_MetaData()
{
    return array(
        'DisplayName' => 'BTC Payment Gateway',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

function btc_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'BTC Payment Gateway',
        ),
        'WalletAddressBTC' => array(
            'FriendlyName' => 'BTC Wallet Address',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your BTC wallet address.',
        ),
        'SalesTaxPercentage' => array(
            'FriendlyName' => 'Sales Tax Percentage',
            'Type' => 'text',
            'Size' => '5',
            'Default' => '5.0',
            'Description' => 'Enter the sales tax percentage.',
        ),
        'BinanceApiKey' => array(
            'FriendlyName' => 'Binance API Key',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your Binance API Key.',
        ),
        'BinanceSecretKey' => array(
            'FriendlyName' => 'Binance Secret Key',
            'Type' => 'password',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your Binance Secret Key.',
        ),
    );
}

function btc_link($params)
{
    $btcWalletAddress = $params['WalletAddressBTC'];
    $invoiceAmountUSD = $params['amount'];

    // Fetch BTC exchange rate (using an alternative API URL)
    $exchangeRate = @file_get_contents('https://blockchain.info/ticker');
    if ($exchangeRate === false) {
        return "<div class='btc-error'>The BTC rate API is currently unreachable. Please try again later.</div>";
    }

    $exchangeRateData = json_decode($exchangeRate, true);
    if (!isset($exchangeRateData['USD']['last']) || $exchangeRateData['USD']['last'] <= 0) {
        return "<div class='btc-error'>Invalid BTC rate. Please try again later.</div>";
    }

    $btcRate = $exchangeRateData['USD']['last'];

    // Fetch the sales tax percentage from admin settings
    $salesTaxPercentage = isset($params['SalesTaxPercentage']) ? (float)$params['SalesTaxPercentage'] : 5.0;

    // Convert USD to BTC and add sales tax
    $btcAmount = ($invoiceAmountUSD / $btcRate) * (1 + $salesTaxPercentage / 100);
    $btcAmountFormatted = number_format($btcAmount, 8);

    // Generate the payment section
    $htmlOutput = '<div class="crypto-payment-container">
        <div class="you-pay-section">
            <label class="section-label">Pay with Bitcoin</label>
            <div class="crypto-amount" onclick="copyToClipboard(this)" style="cursor: pointer; position: relative;">
                <input type="text" value="' . $btcAmountFormatted . '" readonly>
                <span class="crypto-unit">BTC</span>
                <img src="modules/gateways/btc/assets/btc.png" alt="BTC Icon" style="width: 16px; height: 16px; margin-left: 5px;">
                <span class="copy-tooltip" style="position: absolute; top: -25px; right: 0; background: #333; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 12px; display: none;">Copied!</span>
            </div>
        </div>

        <div class="qr-code-section" style="display: flex; justify-content: center; align-items: center;">
            <div id="qrcode" class="qr-code"></div>
        </div>

        <div class="payment-address-section" onclick="copyToClipboard(this)" style="cursor: pointer; position: relative; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
            <label class="section-label">PAYMENT ADDRESS</label>
            <input type="text" value="' . $btcWalletAddress . '" readonly style="width: 100%; background: transparent; font-family: monospace;  border-radius: 4px; border: 1px solid #ddd;">
            <span class="copy-tooltip" style="position: absolute; top: -25px; right: 0; background: #333; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 12px; display: none;">Copied!</span>
        </div>

        <div class="payment-status-section">
            <div class="status-indicator">
                <div class="loading-icon"></div>
                <span class="status-text">Awaiting Payment</span>
            </div>
        </div>

        <div class="expiration-timer-section">
            <span>Price expires in:</span>
            <span class="timer" id="price-expiration-timer">20:00</span>
        </div>

        <div class="btc-payment-footer">
            <p>Ensure you send the exact amount to avoid any delays in processing your payment.</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';

    $htmlOutput .= '<link rel="stylesheet" type="text/css" href="modules/gateways/btc/assets/btc-styles.css">';

    $htmlOutput .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Generate QR Code
            var qrcode = new QRCode(document.getElementById("qrcode"), {
                text: "bitcoin:' . $btcWalletAddress . '?amount=' . $btcAmountFormatted . '",
                width: 200,
                height: 200
            });

            // Ensure status-indicator visibility
            $(".status-indicator").show();

            // Countdown timer for price expiration
            var timerElement = document.getElementById("price-expiration-timer");
            var timeRemaining = 20 * 60; // 20 minutes in seconds

            function updateTimer() {
                var minutes = Math.floor(timeRemaining / 60);
                var seconds = timeRemaining % 60;
                timerElement.textContent = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;

                if (timeRemaining > 0) {
                    timeRemaining--;
                } else {
                    clearInterval(timerInterval);
                    timerElement.textContent = "Expired";
                    location.reload(); // Refresh the page when the timer expires
                }
            }

            var timerInterval = setInterval(updateTimer, 1000);

            // Periodically check transaction status
            var statusText = document.querySelector(".status-text");
            var statusIndicator = document.querySelector(".status-indicator");

            function checkTransactionStatus() {
                $.ajax({
                    url: "modules/gateways/btc/btc.php?action=checkTransactionStatus",
                    method: "GET",
                    data: {
                        walletAddress: "' . $btcWalletAddress . '",
                        expectedAmount: "' . $btcAmountFormatted . '",
                        orderId: "' . $params['invoiceid'] . '"
                    },
                    success: function(response) {
                        if (response.confirmed) {
                            statusText.textContent = "Payment Approved";
                            statusText.style.color = "green";
                            statusIndicator.innerHTML = \'<span style="color: green; font-size: 20px;">✔</span> Payment Approved\';
                            clearInterval(statusCheckInterval);
                            setTimeout(function() {
                                location.reload(); // Refresh the page after marking the order as paid
                            }, 1000); // Delay to ensure the user sees the status change
                        } else {
                            statusText.textContent = "Awaiting Payment";
                            statusText.style.color = "red";
                        }
                    },
                    error: function() {
                        console.error("Failed to check transaction status.");
                    }
                });
            }

            var statusCheckInterval = setInterval(checkTransactionStatus, 10000); // Check every 10 seconds

            // Initialize Bootstrap popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\'popover\']"));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        function copyToClipboard(element) {
            var input = element.querySelector("input");
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(input.value).then(function() {
                var tooltip = element.querySelector(".copy-tooltip");
                tooltip.style.display = "block";
                setTimeout(function() {
                    tooltip.style.display = "none";
                }, 2000);
            });
        }
    </script>';

    return $htmlOutput;
}

function getBinanceCredentials() {
    $gatewayParams = getGatewayVariables('btc');

    $apiKey = $gatewayParams['BinanceApiKey'];
    $secretKey = $gatewayParams['BinanceSecretKey'];

    return [
        'apiKey' => $apiKey,
        'secretKey' => $secretKey,
    ];
}

function verifyBTCTransactionsTest($orderId, $expectedAmount, $walletAddress) {
    // Simulate a successful transaction
    updateOrderStatus($orderId, 'Paid');
    return true;
}

function verifyBTCTransactions($orderId, $expectedAmount, $walletAddress) {
    $credentials = getBinanceCredentials();
    $apiKey = $credentials['apiKey'];
    $secretKey = $credentials['secretKey'];

    $timestamp = round(microtime(true) * 1000);
    $queryString = "timestamp=$timestamp";
    $signature = hash_hmac('sha256', $queryString, $secretKey);

    $url = "https://api.binance.com/sapi/v1/capital/deposit/hisrec?$queryString&signature=$signature";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-MBX-APIKEY: $apiKey"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $deposits = json_decode($response, true);

    foreach ($deposits as $deposit) {
        if ($deposit['address'] === $walletAddress && $deposit['amount'] == $expectedAmount && $deposit['status'] == 1) {
            // Update WHMCS order as paid
            updateOrderStatus($orderId, 'Paid');
            return true;
        }
    }

    return false;
}

function updateOrderStatus($orderId, $status) {
    // Load WHMCS environment
    require_once dirname(__FILE__) . '/../../../init.php';
    require_once dirname(__FILE__) . '/../../../includes/gatewayfunctions.php';
    require_once dirname(__FILE__) . '/../../../includes/invoicefunctions.php';

    // Get the invoice ID associated with the order
    $invoiceId = Capsule::table('tblorders')->where('id', $orderId)->value('invoiceid');

    if ($invoiceId) {
        // Add payment to the invoice
        addInvoicePayment(
            $invoiceId, // Invoice ID
            '',        // Transaction ID (empty for now)
            0,         // Amount (0 because it's already paid)
            0,         // Fees (0 for no fees)
            'btc'      // Payment gateway module name
        );

        // Mark the invoice as paid
        Capsule::table('tblinvoices')->where('id', $invoiceId)->update(['status' => 'Paid']);
    }
}
