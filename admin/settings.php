<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['adminid']; // or get the user ID from the admin session
    $walletAddressERC20 = $_POST['wallet_address_erc20'];
    $walletAddressTRC20 = $_POST['wallet_address_trc20'];
    $walletAddressPolygon = $_POST['wallet_address_polygon'];
    $walletAddressSolana = $_POST['wallet_address_solana'];

    $pdo = Capsule::connection()->getPdo();
    $stmt = $pdo->prepare("REPLACE INTO user_settings (user_id, wallet_address_erc20, wallet_address_trc20, wallet_address_polygon, wallet_address_solana) VALUES (:user_id, :wallet_address_erc20, :wallet_address_trc20, :wallet_address_polygon, :wallet_address_solana)");
    $stmt->execute([
        'user_id' => $userId,
        'wallet_address_erc20' => $walletAddressERC20,
        'wallet_address_trc20' => $walletAddressTRC20,
        'wallet_address_polygon' => $walletAddressPolygon,
        'wallet_address_solana' => $walletAddressSolana
    ]);

    echo '<p>Settings saved successfully!</p>';
}

?>

<form method="POST">
    <label for="wallet_address_erc20">USDT Wallet Address (ERC-20):</label>
    <input type="text" id="wallet_address_erc20" name="wallet_address_erc20" required>

    <label for="wallet_address_trc20">USDT Wallet Address (TRC-20):</label>
    <input type="text" id="wallet_address_trc20" name="wallet_address_trc20" required>

    <label for="wallet_address_polygon">USDC Wallet Address (Polygon):</label>
    <input type="text" id="wallet_address_polygon" name="wallet_address_polygon" required>

    <label for="wallet_address_solana">USDC Wallet Address (Solana):</label>
    <input type="text" id="wallet_address_solana" name="wallet_address_solana" required>

    <input type="submit" value="Save Settings">
</form>