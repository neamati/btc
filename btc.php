<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
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
    );
}

function btc_link($params)
{
    $btcWalletAddress = $params['WalletAddressBTC'];
    $invoiceAmountUSD = $params['amount'];

    // Fetch BTC exchange rate (using a placeholder API URL, replace with a real one)
    $exchangeRate = file_get_contents('https://api.coindesk.com/v1/bpi/currentprice/USD.json');
    $exchangeRateData = json_decode($exchangeRate, true);
    $btcRate = $exchangeRateData['bpi']['USD']['rate_float'];

    // Convert USD to BTC and add 5% fee
    $btcAmount = ($invoiceAmountUSD / $btcRate) * 1.05;
    $btcAmountFormatted = number_format($btcAmount, 8);

    // Generate the "Pay with BTC" button
    $htmlOutput = '<form action="modules/gateways/btc/payment.php" method="GET" target="_blank">
        <input type="hidden" name="btcWalletAddress" value="' . $btcWalletAddress . '">
        <input type="hidden" name="btcAmount" value="' . $btcAmountFormatted . '">
        <button type="submit" class="btn btn-primary">Pay with BTC</button>
    </form>';

    return $htmlOutput;
}
