<?php
if (!isset($_GET['btcWalletAddress']) || !isset($_GET['btcAmount'])) {
    die("Invalid request.");
}

$btcWalletAddress = htmlspecialchars($_GET['btcWalletAddress']);
$btcAmount = htmlspecialchars($_GET['btcAmount']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with BTC</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        .qrcode {
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <h1>Pay with BTC</h1>
    <p>Send <strong><?php echo $btcAmount; ?> BTC</strong> to the following address:</p>
    <p><strong><?php echo $btcWalletAddress; ?></strong></p>
    <div id="qrcode" class="qrcode"></div>
    <script>
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $btcWalletAddress; ?>",
            width: 200,
            height: 200
        });
    </script>
</body>
</html>